<?php

namespace App\Http\Controllers;

use App\Models\Kabupaten;
use App\Models\RhMasterNilai;
use App\Models\RhPerubahanDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class VerificationController extends Controller
{
    public function index()
    {
        // Get Kabupaten IDs that have pending verifications
        // Master: data where rh_perubahan_header_id IS NULL
        $pendingMaster = RhPerubahanDetail::where('verification_status', 'pending')
            ->whereNull('rh_perubahan_header_id')
            ->select('kabupaten_id', DB::raw('count(*) as count'))
            ->groupBy('kabupaten_id')
            ->get();

        // Detail: data where rh_perubahan_header_id IS NOT NULL
        $pendingDetail = RhPerubahanDetail::where('verification_status', 'pending')
            ->whereNotNull('rh_perubahan_header_id')
            ->select('kabupaten_id', DB::raw('count(*) as count'))
            ->groupBy('kabupaten_id')
            ->get();

        $kabupatenStats = [];

        foreach ($pendingMaster as $item) {
            if (!isset($kabupatenStats[$item->kabupaten_id])) {
                $kabupatenStats[$item->kabupaten_id] = 0;
            }
            $kabupatenStats[$item->kabupaten_id] += $item->count;
        }

        foreach ($pendingDetail as $item) {
            if (!isset($kabupatenStats[$item->kabupaten_id])) {
                $kabupatenStats[$item->kabupaten_id] = 0;
            }
            $kabupatenStats[$item->kabupaten_id] += $item->count;
        }

        $kabupatens = Kabupaten::whereIn('id', array_keys($kabupatenStats))->get();

        foreach ($kabupatens as $kab) {
            $kab->pending_count = $kabupatenStats[$kab->id];
        }

        return view('verification.index', compact('kabupatens'));
    }

    public function show($kabupatenId)
    {
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
        $data = $request->input('verifications', []);

        DB::transaction(function () use ($data) {
            foreach ($data as $id => $item) {
                $status = $item['status'] ?? 'pending';
                $reason = $item['reason'] ?? null;
                // Type is less relevant now for table selection but might be useful for logic if needed.
                // We just update RhPerubahanDetail by ID.

                if ($status === 'pending') {
                    continue;
                }

                $updateData = [
                    'verification_status' => $status,
                    'rejection_reason' => ($status === 'rejected') ? $reason : null,
                    'verified_at' => now(),
                    'verified_by' => Auth::id(),
                ];

                RhPerubahanDetail::where('id', $id)->update($updateData);
            }
        });

        return redirect()->route('verification.index')->with('success', 'Verifikasi berhasil disimpan.');
    }
}
