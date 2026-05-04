<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DescanPeriode;
use App\Models\DescanKegiatan;
use App\Models\DescanPeserta;
use App\Models\DescanProgressDesa;
use App\Models\DescanJenisBuktiKegiatan;
use App\Models\DescanJenisOutput;
use App\Models\DescanJenisBuktiDukung;
use App\Models\Kabupaten;
use App\Models\Kecamatan;
use App\Models\Desa;
use Illuminate\Support\Facades\Auth;

class DescanController extends Controller
{
    public function index()
    {
        return view('desa_cantik.index');
    }

    public function kelola()
    {
        // Mendapatkan semua periode tahun
        $periodes = DescanPeriode::withCount('pesertas')->orderBy('tahun', 'desc')->paginate(10, ['*'], 'periode_page');
        // Mendapatkan semua kegiatan
        $kegiatans = DescanKegiatan::orderBy('urutan', 'asc')->paginate(20, ['*'], 'kegiatan_page');

        // Mendapatkan Master Data Tambahan
        $jenisBuktiKegiatans = DescanJenisBuktiKegiatan::paginate(10, ['*'], 'jbk_page');
        $jenisOutputs = DescanJenisOutput::paginate(10, ['*'], 'output_page');
        $jenisBuktiDukungs = DescanJenisBuktiDukung::paginate(10, ['*'], 'jbd_page');

        return view('desa_cantik.kelola', compact('periodes', 'kegiatans', 'jenisBuktiKegiatans', 'jenisOutputs', 'jenisBuktiDukungs'));
    }

    public function storePeriode(Request $request)
    {
        $request->validate([
            'tahun' => 'required|digits:4|unique:descan_periode,tahun'
        ]);

        DescanPeriode::create([
            'tahun' => $request->tahun,
            'is_active' => false // default false as requested
        ]);

        // Tetap di tab periode
        return redirect()->route('desa-cantik.kelola', ['tab' => 'periode'])->with('success', 'Periode tahun berhasil ditambahkan.');
    }

    public function togglePeriodeActive($id)
    {
        $periode = DescanPeriode::findOrFail($id);
        $newStatus = !$periode->is_active;

        if ($newStatus) {
            // Jika mau mengaktifkan, matikan yang lain dulu
            DescanPeriode::where('id', '!=', $id)->update(['is_active' => false]);
            $msg = 'Periode ' . $periode->tahun . ' telah diatur sebagai periode aktif.';
        } else {
            $msg = 'Periode ' . $periode->tahun . ' telah dinonaktifkan.';
        }

        $periode->is_active = $newStatus;
        $periode->save();

        return redirect()->route('desa-cantik.kelola', ['tab' => 'periode'])->with('success', $msg);
    }

    public function destroyPeriode($id)
    {
        $periode = DescanPeriode::findOrFail($id);

        $hasPeserta = DescanPeserta::where('periode_id', $id)->exists();

        if ($hasPeserta) {
            return redirect()->route('desa-cantik.kelola', ['tab' => 'periode'])->with('error', 'Periode tidak dapat dihapus karena sudah memiliki relasi peserta desa.');
        }

        $periode->delete();
        return redirect()->route('desa-cantik.kelola', ['tab' => 'periode'])->with('success', 'Periode berhasil dihapus.');
    }



    public function storeKegiatan(Request $request)
    {
        $request->validate([
            'nama_kegiatan' => 'required|string|max:255|unique:descan_kegiatan,nama_kegiatan'
        ], [
            'nama_kegiatan.unique' => 'Kegiatan dengan nama tersebut sudah ada.'
        ]);

        $maxUrutan = DescanKegiatan::max('urutan') ?? 0;

        DescanKegiatan::create([
            'nama_kegiatan' => $request->nama_kegiatan,
            'urutan' => $maxUrutan + 1,
            'is_active' => true,
            'is_wajib' => $request->has('is_wajib')
        ]);

        return redirect()->route('desa-cantik.kelola', ['tab' => 'kegiatan'])->with('success', 'Kegiatan berhasil ditambahkan.');
    }

    public function toggleKegiatanActive($id)
    {
        $keg = DescanKegiatan::findOrFail($id);
        $keg->is_active = !$keg->is_active;
        $keg->save();

        return redirect()->route('desa-cantik.kelola', ['tab' => 'kegiatan'])->with('success', 'Status kegiatan berhasil diubah.');
    }

    public function updateKegiatan(Request $request, $id)
    {
        $request->validate([
            'nama_kegiatan' => 'required|string|max:255|unique:descan_kegiatan,nama_kegiatan,' . $id
        ], [
            'nama_kegiatan.unique' => 'Kegiatan dengan nama tersebut sudah ada.'
        ]);

        $keg = DescanKegiatan::findOrFail($id);
        $keg->update([
            'nama_kegiatan' => $request->nama_kegiatan,
            'is_wajib' => $request->has('is_wajib')
        ]);

        return redirect()->route('desa-cantik.kelola', ['tab' => 'kegiatan'])->with('success', 'Kegiatan berhasil diperbarui.');
    }

    public function destroyKegiatan($id)
    {
        $keg = DescanKegiatan::findOrFail($id);

        $hasProgress = DescanProgressDesa::where('kegiatan_id', $id)->exists();
        if ($hasProgress) {
            return redirect()->route('desa-cantik.kelola', ['tab' => 'kegiatan'])->with('error', 'Kegiatan tidak dapat dihapus karena sudah digunakan pada Progress Desa.');
        }

        $keg->delete();
        return redirect()->route('desa-cantik.kelola', ['tab' => 'kegiatan'])->with('success', 'Kegiatan berhasil dihapus.');
    }

    public function reorderKegiatan(Request $request)
    {
        $request->validate([
            'orders' => 'required|array',
            'orders.*.id' => 'required|exists:descan_kegiatan,id',
            'orders.*.urutan' => 'required|integer'
        ]);

        foreach ($request->orders as $order) {
            DescanKegiatan::where('id', $order['id'])->update(['urutan' => $order['urutan']]);
        }

        return response()->json(['success' => true, 'message' => 'Urutan kegiatan berhasil diperbarui.']);
    }

    // --- Jenis Bukti Kegiatan ---
    public function storeJenisBuktiKegiatan(Request $request)
    {
        $request->validate(['nama_bukti' => 'required|string|max:255|unique:descan_jenis_bukti_kegiatan,nama_bukti']);
        DescanJenisBuktiKegiatan::create([
            'nama_bukti' => $request->nama_bukti,
            'is_wajib' => $request->has('is_wajib')
        ]);
        return redirect()->route('desa-cantik.kelola', ['tab' => 'jbk'])->with('success', 'Jenis Bukti Kegiatan berhasil ditambahkan.');
    }

    public function updateJenisBuktiKegiatan(Request $request, $id)
    {
        $request->validate(['nama_bukti' => 'required|string|max:255|unique:descan_jenis_bukti_kegiatan,nama_bukti,' . $id]);
        DescanJenisBuktiKegiatan::findOrFail($id)->update([
            'nama_bukti' => $request->nama_bukti,
            'is_wajib' => $request->has('is_wajib')
        ]);
        return redirect()->route('desa-cantik.kelola', ['tab' => 'jbk'])->with('success', 'Jenis Bukti Kegiatan berhasil diperbarui.');
    }

    public function destroyJenisBuktiKegiatan($id)
    {
        try {
            DescanJenisBuktiKegiatan::findOrFail($id)->delete();
            return redirect()->route('desa-cantik.kelola', ['tab' => 'jbk'])->with('success', 'Jenis Bukti Kegiatan berhasil dihapus.');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->route('desa-cantik.kelola', ['tab' => 'jbk'])->with('error', 'Data gagal dihapus karena sudah digunakan di rekam jejak progress desa.');
        }
    }

    // --- Jenis Output ---
    public function storeJenisOutput(Request $request)
    {
        $request->validate(['nama_output' => 'required|string|max:255|unique:descan_jenis_output,nama_output']);
        DescanJenisOutput::create(['nama_output' => $request->nama_output, 'is_wajib' => $request->has('is_wajib')]);
        return redirect()->route('desa-cantik.kelola', ['tab' => 'output'])->with('success', 'Jenis Output berhasil ditambahkan.');
    }

    public function updateJenisOutput(Request $request, $id)
    {
        $request->validate(['nama_output' => 'required|string|max:255|unique:descan_jenis_output,nama_output,' . $id]);
        DescanJenisOutput::findOrFail($id)->update(['nama_output' => $request->nama_output, 'is_wajib' => $request->has('is_wajib')]);
        return redirect()->route('desa-cantik.kelola', ['tab' => 'output'])->with('success', 'Jenis Output berhasil diperbarui.');
    }

    public function destroyJenisOutput($id)
    {
        try {
            DescanJenisOutput::findOrFail($id)->delete();
            return redirect()->route('desa-cantik.kelola', ['tab' => 'output'])->with('success', 'Jenis Output berhasil dihapus.');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->route('desa-cantik.kelola', ['tab' => 'output'])->with('error', 'Data gagal dihapus karena sudah digunakan di rekam jejak progress desa.');
        }
    }

    // --- Jenis Bukti Dukung ---
    public function storeJenisBuktiDukung(Request $request)
    {
        $request->validate(['nama_bukti' => 'required|string|max:255|unique:descan_jenis_bukti_dukung,nama_bukti']);
        DescanJenisBuktiDukung::create(['nama_bukti' => $request->nama_bukti, 'is_wajib' => $request->has('is_wajib')]);
        return redirect()->route('desa-cantik.kelola', ['tab' => 'jbd'])->with('success', 'Jenis Bukti Dukung berhasil ditambahkan.');
    }

    public function updateJenisBuktiDukung(Request $request, $id)
    {
        $request->validate(['nama_bukti' => 'required|string|max:255|unique:descan_jenis_bukti_dukung,nama_bukti,' . $id]);
        DescanJenisBuktiDukung::findOrFail($id)->update(['nama_bukti' => $request->nama_bukti, 'is_wajib' => $request->has('is_wajib')]);
        return redirect()->route('desa-cantik.kelola', ['tab' => 'jbd'])->with('success', 'Jenis Bukti Dukung berhasil diperbarui.');
    }

    public function destroyJenisBuktiDukung($id)
    {
        try {
            DescanJenisBuktiDukung::findOrFail($id)->delete();
            return redirect()->route('desa-cantik.kelola', ['tab' => 'jbd'])->with('success', 'Jenis Bukti Dukung berhasil dihapus.');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->route('desa-cantik.kelola', ['tab' => 'jbd'])->with('error', 'Data gagal dihapus karena sudah digunakan di rekam jejak progress desa.');
        }
    }

    // =========================================================
    // PESERTA DESA
    // =========================================================

    public function peserta(Request $request)
    {
        $user = Auth::user();
        $kabupaten = $user->kabupaten;
        $isProvinsi = $kabupaten && $kabupaten->kode_kab == '6100';

        $periodes = DescanPeriode::orderBy('tahun', 'desc')->get();

        // Data untuk form
        $kabupatens = $isProvinsi ? Kabupaten::where('kode_kab', '!=', '6100')->orderBy('nama_kabupaten')->get() : collect();
        $kecamatans = !$isProvinsi ? Kecamatan::where('kabupaten_id', $kabupaten?->id)->orderBy('nama_kecamatan')->get() : collect();
        $myKabupatenId = $isProvinsi ? null : $kabupaten?->id;

        // Query peserta
        $query = DescanPeserta::with(['desa', 'kecamatan', 'creator', 'kabupaten', 'periode'])
            ->orderBy('created_at', 'desc');

        // Filter oleh kabupaten jika bukan provinsi
        if (!$isProvinsi) {
            $query->where('kabupaten_id', $myKabupatenId);
        }

        // Filter periode
        if ($request->filled('filter_periode')) {
            $query->where('periode_id', $request->filter_periode);
        }

        $pesertas = $query->paginate(20);

        return view('desa_cantik.peserta', compact(
            'periodes',
            'kabupatens',
            'kecamatans',
            'myKabupatenId',
            'isProvinsi',
            'pesertas'
        ));
    }

    public function storePeserta(Request $request)
    {
        $user = Auth::user();
        $kabupaten = $user->kabupaten;
        $isProvinsi = $kabupaten && $kabupaten->kode_kab == '6100';

        $request->validate([
            'periode_id' => 'required|exists:descan_periode,id',
            'kabupaten_id' => 'required|exists:tb_kabupaten,id',
            'kecamatan_id' => 'required|exists:tb_kecamatan,id',
            'desa_id' => 'required|exists:tb_desa,id',
        ]);

        // Paksa kabupaten_id sesuai user jika bukan provinsi
        $kabupatenId = $isProvinsi ? $request->kabupaten_id : $kabupaten->id;

        // Cek apakah periode aktif
        $periode = DescanPeriode::findOrFail($request->periode_id);
        if (!$periode->is_active) {
            return back()->with('error', 'Periode ini tidak aktif. Pendaftaran peserta hanya dapat dilakukan pada periode aktif.');
        }

        // Cek duplikat
        $sudahAda = DescanPeserta::where('periode_id', $request->periode_id)
            ->where('desa_id', $request->desa_id)
            ->exists();
        if ($sudahAda) {
            return back()->with('error', 'Desa ini sudah terdaftar sebagai peserta pada periode yang dipilih.');
        }

        DescanPeserta::create([
            'periode_id' => $request->periode_id,
            'kabupaten_id' => $kabupatenId,
            'kecamatan_id' => $request->kecamatan_id,
            'desa_id' => $request->desa_id,
            'created_by' => $user->id,
        ]);

        return back()->with('success', 'Desa berhasil didaftarkan sebagai peserta.');
    }

    public function destroyPeserta($id)
    {
        $peserta = DescanPeserta::with('periode')->findOrFail($id);

        if (!$peserta->periode->is_active) {
            return back()->with('error', 'Peserta tidak dapat dihapus karena periode ini tidak aktif.');
        }

        // Cek apakah sudah ada progress
        $hasProgress = DescanProgressDesa::where('peserta_id', $id)->exists();
        if ($hasProgress) {
            return back()->with('error', 'Peserta tidak dapat dihapus because sudah memiliki data progress.');
        }

        $peserta->delete();
        return back()->with('success', 'Peserta desa berhasil dihapus.');
    }

    // =========================================================
    // PROGRESS KEGIATAN
    // =========================================================

    public function verifikasi(Request $request)
    {
        $user = Auth::user();
        $isProvinsi = $user->kabupaten && $user->kabupaten->kode_kab == '6100';

        if (!$isProvinsi) {
            return redirect()->route('desa-cantik.index')->with('error', 'Akses ditolak.');
        }

        $query = DescanProgressDesa::with(['peserta.desa', 'peserta.kecamatan', 'peserta.kabupaten', 'kegiatan', 'buktis.jenisBukti'])
            ->where('status', 'menunggu_verifikasi')
            ->orderBy('updated_at', 'asc');

        $pendingVerifications = $query->paginate(20);

        return view('desa_cantik.verifikasi', compact('pendingVerifications'));
    }

    public function progress(Request $request)
    {
        $user = Auth::user();
        $isProvinsi = $user->kabupaten && $user->kabupaten->kode_kab == '6100';
        $periodes = DescanPeriode::orderBy('tahun', 'desc')->get();
        // Hanya ambil kegiatan yang active
        $kegiatans = DescanKegiatan::where('is_active', true)->orderBy('urutan', 'asc')->get();

        $query = DescanPeserta::with(['desa', 'kecamatan', 'kabupaten', 'periode', 'progresses.buktis', 'outputs', 'buktiDukungs'])
            ->orderBy('created_at', 'desc');

        if (!$isProvinsi) {
            $query->where('kabupaten_id', $user->kabupaten_id);
        }

        if ($request->filled('periode_id')) {
            $query->where('periode_id', $request->periode_id);
        }

        $pesertas = $query->paginate(20);
        $mandatoryBuktiIds = DescanJenisBuktiKegiatan::where('is_wajib', true)->pluck('id')->toArray();
        $mandatoryOutputIds = DescanJenisOutput::where('is_wajib', true)->pluck('id')->toArray();
        $mandatoryDukungIds = DescanJenisBuktiDukung::where('is_wajib', true)->pluck('id')->toArray();

        return view('desa_cantik.progress', compact(
            'pesertas',
            'periodes',
            'kegiatans',
            'isProvinsi',
            'mandatoryBuktiIds',
            'mandatoryOutputIds',
            'mandatoryDukungIds'
        ));
    }

    public function progressDetail($peserta_id)
    {
        $peserta = DescanPeserta::with(['desa', 'kecamatan', 'kabupaten', 'periode', 'outputs', 'buktiDukungs'])->findOrFail($peserta_id);
        $kegiatans = DescanKegiatan::where('is_active', true)
            ->leftJoin('descan_progress_desa', function($join) use ($peserta_id) {
                $join->on('descan_kegiatan.id', '=', 'descan_progress_desa.kegiatan_id')
                     ->where('descan_progress_desa.peserta_id', '=', $peserta_id);
            })
            ->select('descan_kegiatan.*')
            ->orderByRaw('COALESCE(descan_progress_desa.urutan, descan_kegiatan.urutan) ASC, descan_kegiatan.id ASC')
            ->get();

        $progresses = DescanProgressDesa::with(['buktis.jenisBukti', 'verifier'])
            ->where('peserta_id', $peserta_id)
            ->get()
            ->keyBy('kegiatan_id');

        $isProvinsi = Auth::user()->kabupaten && Auth::user()->kabupaten->kode_kab == '6100';

        // Jenis Bukti Kegiatan
        $jenisBuktiMandatory = DescanJenisBuktiKegiatan::where('is_wajib', true)->orderBy('nama_bukti')->get();
        $jenisBuktiOptional = DescanJenisBuktiKegiatan::where('is_wajib', false)->orderBy('nama_bukti')->get();

        // Jenis Output
        $jenisOutputMandatory = DescanJenisOutput::where('is_wajib', true)->orderBy('nama_output')->get();
        $jenisOutputOptional = DescanJenisOutput::where('is_wajib', false)->orderBy('nama_output')->get();

        // Jenis Bukti Dukung (Lainnya)
        $jenisDukungMandatory = DescanJenisBuktiDukung::where('is_wajib', true)->orderBy('nama_bukti')->get();
        $jenisDukungOptional = DescanJenisBuktiDukung::where('is_wajib', false)->orderBy('nama_bukti')->get();

        return view('desa_cantik.progress_detail', compact(
            'peserta',
            'kegiatans',
            'progresses',
            'isProvinsi',
            'jenisBuktiMandatory',
            'jenisBuktiOptional',
            'jenisOutputMandatory',
            'jenisOutputOptional',
            'jenisDukungMandatory',
            'jenisDukungOptional'
        ));
    }

    public function storeProgress(Request $request, $peserta_id)
    {
        $request->validate([
            'action_type' => 'required|in:draf,submit',
            'progress' => 'nullable|array',
            'progress.*.target_tanggal' => 'nullable|date',
            'progress.*.realisasi_tanggal' => 'nullable|date|after_or_equal:progress.*.target_tanggal',
        ], [
            'progress.*.realisasi_tanggal.after_or_equal' => 'Tanggal realisasi tidak boleh lebih kecil dari tanggal target.'
        ]);

        $peserta = DescanPeserta::with('periode')->findOrFail($peserta_id);
        $year = $peserta->periode->tahun;
        $allKegiatans = DescanKegiatan::where('is_active', true)->orderBy('urutan', 'asc')->get();
        $status = ($request->action_type == 'submit') ? 'menunggu_verifikasi' : 'draf';

        // 1. Validasi Batasan Tahun & Urutan Tanggal antar kegiatan
        if ($request->has('progress')) {
            $lastTarget = null;
            $lastRealisasi = null;
            $lastKegiatanNama = "";

            foreach ($allKegiatans as $keg) {
                $data = $request->input("progress.{$keg->id}");
                if (!$data) {
                    // Coba ambil dari DB untuk keperluan validasi urutan
                    $dbProg = \App\Models\DescanProgressDesa::where('peserta_id', $peserta_id)->where('kegiatan_id', $keg->id)->first();
                    $targetVal = $dbProg ? $dbProg->target_tanggal : null;
                    $realisasiVal = $dbProg ? $dbProg->realisasi_tanggal : null;
                } else {
                    $targetVal = $data['target_tanggal'] ?? null;
                    $realisasiVal = $data['realisasi_tanggal'] ?? null;
                }

                if ($targetVal) {
                    // Cek batasan tahun
                    if (date('Y', strtotime($targetVal)) != $year) {
                        return back()->with('error', "Tanggal target untuk kegiatan '{$keg->nama_kegiatan}' harus berada di tahun {$year}.");
                    }

                    // Cek urutan kegiatan: harus berurutan
                    if ($lastKegiatanNama && !$lastTarget) {
                        return back()->with('error', "Kegiatan '{$keg->nama_kegiatan}' tidak dapat diisi sebelum kegiatan '{$lastKegiatanNama}' ditentukan tanggal targetnya.");
                    }

                    // Cek urutan tanggal: min date adalah realisasi sebelumnya, jika tidak ada pakai target sebelumnya
                    $effectiveMin = $lastRealisasi ?: $lastTarget;
                    if ($effectiveMin && $targetVal < $effectiveMin) {
                        $msgMin = $lastRealisasi ? "tanggal realisasi" : "tanggal target";
                        return back()->with('error', "Tanggal target untuk kegiatan '{$keg->nama_kegiatan}' tidak boleh lebih kecil dari {$msgMin} kegiatan '{$lastKegiatanNama}'.");
                    }
                }

                if ($realisasiVal) {
                    // Cek batasan tahun
                    if (date('Y', strtotime($realisasiVal)) != $year) {
                        return back()->with('error', "Tanggal realisasi untuk kegiatan '{$keg->nama_kegiatan}' harus berada di tahun {$year}.");
                    }
                    // Validasi internal: realisasi >= target (sudah divalidasi di validate() sebenarnya, tapi di sini untuk sequence)
                    $lastRealisasi = $realisasiVal;
                }
                
                $lastTarget = $targetVal;
                $lastKegiatanNama = $keg->nama_kegiatan;
            }
        }

        // Jika Submit, validasi kegiatan wajib dan bukti wajib
        // 2. Validasi Submit: Pastikan semua isian wajib pada kegiatan yang dimulai telah lengkap
        if ($status == 'menunggu_verifikasi') {
            $jenisBuktiMandatory = \App\Models\DescanJenisBuktiKegiatan::where('is_wajib', true)->get();
            foreach ($allKegiatans as $keg) {
                $data = $request->input("progress.{$keg->id}");
                $hasTarget = !empty($data['target_tanggal']);

                // Jika kegiatan wajib, atau kegiatan opsional yang sudah mulai diisi targetnya
                if ($keg->is_wajib || $hasTarget) {
                    if (empty($data['target_tanggal']) || empty($data['realisasi_tanggal'])) {
                        return back()->with('error', "Kegiatan '{$keg->nama_kegiatan}' harus diisi tanggal target dan realisasinya sebelum diajukan.");
                    }

                    foreach ($jenisBuktiMandatory as $mb) {
                        $linkFound = false;
                        if ($request->has("bukti.{$keg->id}")) {
                            foreach ($request->bukti[$keg->id]['jenis_id'] as $idx => $jid) {
                                if ($jid == $mb->id && !empty($request->bukti[$keg->id]['link'][$idx])) {
                                    $linkFound = true;
                                    break;
                                }
                            }
                        }

                        if (!$linkFound) {
                            return back()->with('error', "Bukti wajib '{$mb->nama_bukti}' pada kegiatan '{$keg->nama_kegiatan}' harus diisi.");
                        }
                    }
                }
            }

            // 3. Validasi Output Wajib
            $jenisOutputMandatory = \App\Models\DescanJenisOutput::where('is_wajib', true)->get();
            foreach ($jenisOutputMandatory as $jo) {
                $linkFound = false;
                if ($request->has('output_jenis_id')) {
                    foreach ($request->output_jenis_id as $idx => $jid) {
                        if ($jid == $jo->id && !empty($request->output_link[$idx])) {
                            $linkFound = true;
                            break;
                        }
                    }
                }
                if (!$linkFound) {
                    return back()->with('error', "Output wajib '{$jo->nama_output}' harus diisi sebelum diajukan.");
                }
            }

            // 4. Validasi Bukti Dukung (Lainnya) Wajib
            $jenisDukungMandatory = \App\Models\DescanJenisBuktiDukung::where('is_wajib', true)->get();
            foreach ($jenisDukungMandatory as $jd) {
                $linkFound = false;
                if ($request->has('dukung_jenis_id')) {
                    foreach ($request->dukung_jenis_id as $idx => $jid) {
                        if ($jid == $jd->id && !empty($request->dukung_link[$idx])) {
                            $linkFound = true;
                            break;
                        }
                    }
                }
                if (!$linkFound) {
                    return back()->with('error', "Bukti Lainnya yang bersifat wajib ('{$jd->nama_bukti}') harus diisi sebelum diajukan.");
                }
            }
        }

        // Simpan data progress
        if ($request->has('progress')) {
            // Map urutan dari master kegiatan
            $kegiatanUrutan = $allKegiatans->pluck('urutan', 'id');

            foreach ($request->progress as $keg_id => $data) {
                if (!empty($data['target_tanggal'])) {
                    // Cari urutan kegiatan ini dari master
                    $urutan = $kegiatanUrutan[$keg_id] ?? 0;

                    $progress = DescanProgressDesa::updateOrCreate(
                        ['peserta_id' => $peserta_id, 'kegiatan_id' => $keg_id],
                        [
                            'urutan' => $urutan, // Simpan urutan saat ini ke progress (lock order)
                            'target_tanggal' => $data['target_tanggal'],
                            'realisasi_tanggal' => $data['realisasi_tanggal'],
                            'status' => $status,
                            'updated_by' => Auth::id()
                        ]
                    );

                    // Sync Bukti Links: Hapus yang lama, simpan yang baru dari form
                    if ($request->has("bukti.{$keg_id}")) {
                        \App\Models\DescanBuktiKegiatan::where('progress_desa_id', $progress->id)->delete();

                        foreach ($request->bukti[$keg_id]['link'] as $idx => $link) {
                            $jenisId = $request->bukti[$keg_id]['jenis_id'][$idx];
                            if (!empty($link) && !empty($jenisId)) {
                                \App\Models\DescanBuktiKegiatan::create([
                                    'progress_desa_id' => $progress->id,
                                    'jenis_bukti_id' => $jenisId,
                                    'link_file' => $link,
                                    'created_by' => Auth::id()
                                ]);
                            }
                        }
                    }
                } else {
                    // Jika target_tanggal kosong, hapus record progress jika ada (untuk mereset ke Belum Dimulai)
                    $existingProgress = DescanProgressDesa::where('peserta_id', $peserta_id)
                        ->where('kegiatan_id', $keg_id)
                        ->first();

                    if ($existingProgress) {
                        // Hapus bukti kegiatan terlebih dahulu (karena tidak ada cascade delete di migration)
                        \App\Models\DescanBuktiKegiatan::where('progress_desa_id', $existingProgress->id)->delete();
                        $existingProgress->delete();
                    }
                }
            }
        }

        // Simpan data Output
        if ($request->has('output_jenis_id')) {
            $existingOutputIds = [];
            foreach ($request->output_jenis_id as $idx => $j_id) {
                $link = $request->output_link[$idx] ?? null;
                if (!empty($j_id) && !empty($link)) {
                    $item = \App\Models\DescanOutputDesa::where('peserta_id', $peserta_id)
                        ->where('jenis_output_id', $j_id)
                        ->first();

                    $newStatus = $status;
                    // Jika link sama dan sudah disetujui, pertahankan status
                    if ($item && $item->link == $link && $item->status == 'disetujui') {
                        $newStatus = 'disetujui';
                    }

                    $output = \App\Models\DescanOutputDesa::updateOrCreate(
                        ['peserta_id' => $peserta_id, 'jenis_output_id' => $j_id],
                        [
                            'link' => $link,
                            'status' => $newStatus,
                            'updated_by' => Auth::id(),
                            'created_by' => $item ? $item->created_by : Auth::id()
                        ]
                    );
                    $existingOutputIds[] = $output->id;
                }
            }
            // Hapus yang tidak ada di form (untuk opsional)
            \App\Models\DescanOutputDesa::where('peserta_id', $peserta_id)
                ->whereNotIn('id', $existingOutputIds)
                ->delete();
        }

        // Simpan data Bukti Dukung
        if ($request->has('dukung_jenis_id')) {
            $existingDukungIds = [];
            foreach ($request->dukung_jenis_id as $idx => $j_id) {
                $link = $request->dukung_link[$idx] ?? null;
                if (!empty($j_id) && !empty($link)) {
                    $item = \App\Models\DescanBuktiDukungDesa::where('peserta_id', $peserta_id)
                        ->where('jenis_bukti_id', $j_id)
                        ->first();

                    $newStatus = $status;
                    if ($item && $item->link_file == $link && $item->status == 'disetujui') {
                        $newStatus = 'disetujui';
                    }

                    $dukung = \App\Models\DescanBuktiDukungDesa::updateOrCreate(
                        ['peserta_id' => $peserta_id, 'jenis_bukti_id' => $j_id],
                        [
                            'link_file' => $link,
                            'status' => $newStatus,
                            'created_by' => $item ? $item->created_by : Auth::id()
                        ]
                    );
                    $existingDukungIds[] = $dukung->id;
                }
            }
            \App\Models\DescanBuktiDukungDesa::where('peserta_id', $peserta_id)
                ->whereNotIn('id', $existingDukungIds)
                ->delete();
        }

        $msg = ($status == 'menunggu_verifikasi') ? 'Progress berhasil diajukan untuk verifikasi.' : 'Progress draf berhasil disimpan.';
        return back()->with('success', $msg);
    }

    public function verifyProgress(Request $request, $peserta_id, $progress_id)
    {
        $user = Auth::user();
        $isProvinsi = $user->kabupaten && $user->kabupaten->kode_kab == '6100';

        if (!$isProvinsi) {
            return back()->with('error', 'Hanya admin provinsi yang dapat melakukan verifikasi.');
        }

        $progress = DescanProgressDesa::findOrFail($progress_id);

        $request->validate([
            'action' => 'required|in:approve,reject',
            'alasan_penolakan' => 'nullable|string'
        ]);

        if ($request->action == 'approve') {
            $progress->update([
                'status' => 'disetujui',
                'verified_by' => Auth::id(),
                'verified_at' => now(),
                'alasan_penolakan' => null
            ]);
            $msg = 'Progress berhasil diverifikasi.';
        } else {
            $progress->update([
                'status' => 'ditolak',
                'alasan_penolakan' => $request->alasan_penolakan
            ]);
            $msg = 'Progress ditolak.';
        }

        return back()->with('success', $msg);
    }

    // =========================================================
    // AJAX ENDPOINTS (Dropdown bertingkat)
    // =========================================================

    public function ajaxKecamatan(Request $request)
    {
        $kabupatenId = $request->kabupaten_id;
        $search = $request->get('search', '');
        $limit = (int) $request->get('limit', 4);

        $kecamatans = Kecamatan::where('kabupaten_id', $kabupatenId)
            ->when($search, fn($q) => $q->where('nama_kecamatan', 'like', "%{$search}%"))
            ->orderBy('nama_kecamatan')
            ->limit($limit)
            ->get(['id', 'nama_kecamatan']);

        return response()->json($kecamatans);
    }

    public function ajaxDesa(Request $request)
    {
        $kecamatanId = $request->kecamatan_id;
        $periodeId = $request->periode_id;
        $search = $request->get('search', '');
        $limit = (int) $request->get('limit', 4);

        $desas = Desa::where('kecamatan_id', $kecamatanId)
            ->when($search, fn($q) => $q->where('nama_desa', 'like', "%{$search}%"))
            ->orderBy('nama_desa')
            ->limit($limit)
            ->get(['id', 'nama_desa']);

        // Tandai desa yang sudah terdaftar di periode ini
        $terdaftar = DescanPeserta::where('periode_id', $periodeId)
            ->pluck('desa_id')
            ->toArray();

        $result = $desas->map(function ($d) use ($terdaftar) {
            $terdaftarFlag = in_array($d->id, $terdaftar);
            return [
                'id' => $d->id,
                'nama_desa' => $d->nama_desa,
                'sudah_terdaftar' => $terdaftarFlag,
                'text' => $terdaftarFlag ? $d->nama_desa . ' ✓ terdaftar' : $d->nama_desa,
                'disabled' => $terdaftarFlag,
            ];
        });

        return response()->json($result);
    }


}
