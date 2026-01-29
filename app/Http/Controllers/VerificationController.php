<?php

namespace App\Http\Controllers;

use App\Models\Kabupaten;
use App\Models\RhMasterNilai;
use App\Models\RhPerubahanDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Helpers\WhatsAppHelper;

class VerificationController extends Controller
{
    public function index()
    {
        if (auth()->check() == false) {
            return redirect('/price-range')->with('error', 'Silahkan login terlebih dahulu.');
        }
        // Strict Access: Only Province User (6100)
        $user = Auth::user();
        if (!$user->kabupaten || $user->kabupaten->kode_kab != '6100') {
            return redirect()->back()->with('error', 'Akses Ditolak: Hanya BPS Provinsi (6100) yang dapat mengakses halaman verifikasi.');
        }

        // Get Kabupaten IDs that have pending/rejected verifications

        // Pending
        $pendingMaster = RhPerubahanDetail::where('verification_status', 'pending')
            ->whereNull('rh_perubahan_header_id')
            ->select('kabupaten_id', DB::raw('count(*) as count'))
            ->groupBy('kabupaten_id')
            ->get();

        $pendingDetail = RhPerubahanDetail::where('verification_status', 'pending')
            ->whereNotNull('rh_perubahan_header_id')
            ->select('kabupaten_id', DB::raw('count(*) as count'))
            ->groupBy('kabupaten_id')
            ->get();

        // Rejected
        $rejectedMaster = RhPerubahanDetail::where('verification_status', 'rejected')
            ->whereNull('rh_perubahan_header_id')
            ->select('kabupaten_id', DB::raw('count(*) as count'))
            ->groupBy('kabupaten_id')
            ->get();

        $rejectedDetail = RhPerubahanDetail::where('verification_status', 'rejected')
            ->whereNotNull('rh_perubahan_header_id')
            ->select('kabupaten_id', DB::raw('count(*) as count'))
            ->groupBy('kabupaten_id')
            ->get();

        $kabupatenStats = [];

        // Helper to init stat array
        $initStat = function ($id) use (&$kabupatenStats) {
            if (!isset($kabupatenStats[$id])) {
                $kabupatenStats[$id] = ['pending' => 0, 'rejected' => 0];
            }
        };

        foreach ($pendingMaster as $item) {
            $initStat($item->kabupaten_id);
            $kabupatenStats[$item->kabupaten_id]['pending'] += $item->count;
        }

        foreach ($pendingDetail as $item) {
            $initStat($item->kabupaten_id);
            $kabupatenStats[$item->kabupaten_id]['pending'] += $item->count;
        }

        foreach ($rejectedMaster as $item) {
            $initStat($item->kabupaten_id);
            $kabupatenStats[$item->kabupaten_id]['rejected'] += $item->count;
        }

        foreach ($rejectedDetail as $item) {
            $initStat($item->kabupaten_id);
            $kabupatenStats[$item->kabupaten_id]['rejected'] += $item->count;
        }

        $kabupatens = Kabupaten::whereIn('id', array_keys($kabupatenStats))->get();

        foreach ($kabupatens as $kab) {
            $kab->pending_count = $kabupatenStats[$kab->id]['pending'];
            $kab->rejected_count = $kabupatenStats[$kab->id]['rejected'];
        }

        return view('verification.index', compact('kabupatens'));
    }

    public function show($kabupatenId)
    {
        if (auth()->check() == false) {
            return redirect('/price-range')->with('error', 'Silahkan login terlebih dahulu.');
        }
        // Strict Access: Only Province User (6100)
        $user = Auth::user();
        if (!$user->kabupaten || $user->kabupaten->kode_kab != '6100') {
            return redirect()->back()->with('error', 'Akses Ditolak: Hanya BPS Provinsi (6100) yang dapat mengakses detail verifikasi.');
        }

        $kabupaten = Kabupaten::findOrFail($kabupatenId);

        // Map min_edit/max_edit to min_nilai/max_nilai via select alias for consistent view usage if needed,
        // OR update the VIEW to use min_edit/max_edit for master too.
        // Let's modify the controller to just pass the objects. The unified table uses min_edit/max_edit.
        // The View 'verification.show' likely uses min_nilai/max_nilai for master.
        // We can use Select Raw or just update view. Updating view is better for long term. 
        // We will update the view later. For now, fetch as is.

        $pendingMaster = RhPerubahanDetail::where('kabupaten_id', $kabupatenId)
            ->whereNull('rh_perubahan_header_id')
            ->where('verification_status', 'pending')
            ->with(['komoditas', 'rhTahun', 'userAdd'])
            ->get();

        $pendingDetail = RhPerubahanDetail::where('kabupaten_id', $kabupatenId)
            ->whereNotNull('rh_perubahan_header_id')
            ->where('verification_status', 'pending')
            ->with(['komoditas', 'revisionHeader', 'userAdd']) // Header might not have rhTahun loaded if not requested, but revisionHeader relation exists
            ->get();

        // Pre-load rhTahun for pendingDetail via revisionHeader
        // Wait, revisionHeader model has rhTahun. 
        // Fetch Rejected Items
        $rejectedMaster = RhPerubahanDetail::where('kabupaten_id', $kabupatenId)
            ->whereNull('rh_perubahan_header_id')
            ->where('verification_status', 'rejected')
            ->with(['komoditas', 'rhTahun', 'userAdd'])
            ->orderBy('verified_at', 'desc')
            ->get();

        $rejectedDetail = RhPerubahanDetail::where('kabupaten_id', $kabupatenId)
            ->whereNotNull('rh_perubahan_header_id')
            ->where('verification_status', 'rejected')
            ->with(['komoditas', 'revisionHeader', 'userAdd'])
            ->orderBy('verified_at', 'desc')
            ->get();

        $pendingDetail->load('revisionHeader.rhTahun');
        $rejectedDetail->load('revisionHeader.rhTahun');

        return view('verification.show', compact('kabupaten', 'pendingMaster', 'pendingDetail', 'rejectedMaster', 'rejectedDetail'));
    }

    public function store(Request $request, $kabupatenId)
    {
        if (auth()->check() == false) {
            return redirect('/price-range')->with('error', 'Silahkan login terlebih dahulu.');
        }
        // Strict Access: Only Province User (6100)
        $user = Auth::user();
        if (!$user->kabupaten || $user->kabupaten->kode_kab != '6100') {
            return redirect()->back()->with('error', 'Akses Ditolak: Hanya BPS Provinsi (6100) yang dapat menyimpan hasil verifikasi.');
        }

        $data = $request->input('verifications', []);
        $rejectedIds = [];

        DB::transaction(function () use ($data, &$rejectedIds) {
            foreach ($data as $id => $item) {
                // We assume $id is the RhPerubahanDetail id
                $cekstatus = RhPerubahanDetail::where('id', $id)->where('verification_status', 'pending')->lockForUpdate()->first();
                if (!$cekstatus) {
                    continue;
                }
                $status = $item['status'] ?? 'pending';
                $reason = $item['reason'] ?? null;

                if ($status === 'pending') {
                    continue;
                }
                if ($status === 'rejected' && empty($reason)) {
                    continue; // Skip if rejected but no reason provided, or handle as error? existing logic skips.
                }

                $cekstatus->update([
                    'verification_status' => $status,
                    'rejection_reason' => ($status === 'rejected') ? $reason : null,
                    'verified_at' => now(),
                    'verified_by' => Auth::id(),
                ]);

                if ($status === 'rejected') {
                    $rejectedIds[] = $id;
                }
            }
        });

        // $pesan = '';
        // if (count($rejectedIds) > 0) {
        //     $kabupaten = Kabupaten::find($kabupatenId);
        //     $namaKabupaten = $kabupaten ? $kabupaten->nama_kabupaten : '-';

        //     // Fetch rejected details for the message
        //     $rejectedItems = RhPerubahanDetail::whereIn('id', $rejectedIds)
        //         ->with(['komoditas', 'rhTahun', 'revisionHeader', 'userAdd'])
        //         ->get();

        //     $team = $rejectedItems->first()->userAdd->team ?? '-';
        //     $pesan = "Kepada Yth. Bapak/Ibu Tim " . $team . " Perwakilan " . $namaKabupaten . ",\n\n";
        //     $pesan .= "Berikut kami sampaikan rincian data Rentang Harga yang *DITOLAK* pada proses verifikasi:\n\n";

        //     $groupedItems = $rejectedItems->groupBy(function ($item) {
        //         if ($item->rh_perubahan_header_id) {
        //             $tanggal = $item->revisionHeader->tanggal_perubahan ?? null;
        //             if ($tanggal) {
        //                 $date = \Carbon\Carbon::parse($tanggal);
        //                 $months = [
        //                     1 => 'Januari',
        //                     2 => 'Februari',
        //                     3 => 'Maret',
        //                     4 => 'April',
        //                     5 => 'Mei',
        //                     6 => 'Juni',
        //                     7 => 'Juli',
        //                     8 => 'Agustus',
        //                     9 => 'September',
        //                     10 => 'Oktober',
        //                     11 => 'November',
        //                     12 => 'Desember'
        //                 ];
        //                 $monthName = $months[$date->month];
        //                 return '*PERUBAHAN ' . $date->day . ' ' . $monthName . ' ' . $date->year . '*';
        //             }
        //             return '*PERUBAHAN*';
        //         } else {
        //             return '*MASTER ' . ($item->rhTahun->tahun ?? '') . '*';
        //         }
        //     });

        //     foreach ($groupedItems as $header => $items) {
        //         $pesan .= $header . "\n";
        //         $counter = 1;
        //         foreach ($items as $item) {
        //             $komoditas = $item->komoditas ? $item->komoditas->nama_komoditas : 'Komoditas Tidak Dikenal';
        //             $tahun = $item->rhTahun->tahun ?? '-';
        //             $alasan = $item->rejection_reason ?? '-';

        //             $pesan .= $counter . ". " . $komoditas . " (" . $tahun . ")\n";
        //             $pesan .= "   Alasan: " . $alasan . "\n";
        //             $counter++;
        //         }
        //         $pesan .= "\n";
        //     }

        //     $pesan .= "\nSilahkan melakukan revisi atau perubahan pada link website berikut.\n" . url('/price-range/input-nilai');
        //     $pesan .= "\n\n*Salam,*";
        //     $pesan .= "\n*Tim Sosial BPS Provinsi Kalbar*";

        //     // Send WhatsApp to the user who added the data
        //     $userIds = $rejectedItems->pluck('user_id_add')->unique();
        //     $users = \App\Models\User::whereIn('id', $userIds)->get();

        //     foreach ($users as $user) {
        //         if ($user->no_hp) {
        //             WhatsAppHelper::kirimPesanWhatsApp($user->no_hp, $pesan);
        //         }
        //     }
        // }

        return redirect()->route('verification.index')
            ->with('success', 'Verifikasi berhasil disimpan.');
    }
}
