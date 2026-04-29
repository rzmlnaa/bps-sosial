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
            'pesertas', 'periodes', 'kegiatans', 'isProvinsi', 
            'mandatoryBuktiIds', 'mandatoryOutputIds', 'mandatoryDukungIds'
        ));
    }

    public function progressDetail($peserta_id)
    {
        $peserta = DescanPeserta::with(['desa', 'kecamatan', 'kabupaten', 'periode', 'outputs', 'buktiDukungs'])->findOrFail($peserta_id);
        // Hanya ambil kegiatan yang active
        $kegiatans = DescanKegiatan::where('is_active', true)->orderBy('urutan', 'asc')->get();
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
            'peserta', 'kegiatans', 'progresses', 'isProvinsi', 
            'jenisBuktiMandatory', 'jenisBuktiOptional',
            'jenisOutputMandatory', 'jenisOutputOptional',
            'jenisDukungMandatory', 'jenisDukungOptional'
        ));
    }

    public function storeProgress(Request $request, $peserta_id)
    {
        $request->validate([
            'action_type' => 'required|in:draft,submit',
            'progress' => 'nullable|array',
            'progress.*.target_tanggal' => 'nullable|date',
            'progress.*.realisasi_tanggal' => 'nullable|date|after_or_equal:progress.*.target_tanggal',
        ], [
            'progress.*.realisasi_tanggal.after_or_equal' => 'Tanggal realisasi tidak boleh lebih kecil dari tanggal target.'
        ]);

        $peserta = DescanPeserta::findOrFail($peserta_id);
        $allKegiatans = DescanKegiatan::where('is_active', true)->get();
        $status = ($request->action_type == 'submit') ? 'menunggu_verifikasi' : 'draft';

        // Jika Submit, validasi kegiatan wajib dan bukti wajib
        if ($status == 'menunggu_verifikasi') {
            $wajibKegiatans = $allKegiatans->where('is_wajib', true);
            foreach ($wajibKegiatans as $wk) {
                $data = $request->input("progress.{$wk->id}");
                if (empty($data['target_tanggal']) || empty($data['realisasi_tanggal'])) {
                    return back()->with('error', 'Kegiatan wajib "' . $wk->nama_kegiatan . '" harus diisi tanggal target dan realisasinya sebelum diajukan.');
                }
            }

            // Validasi Bukti Wajib untuk setiap kegiatan yang sedang diproses
            if ($request->has('progress')) {
                $jenisBuktiMandatory = DescanJenisBuktiKegiatan::where('is_wajib', true)->get();
                foreach ($request->progress as $keg_id => $data) {
                    if (!empty($data['target_tanggal']) || !empty($data['realisasi_tanggal'])) {
                        foreach ($jenisBuktiMandatory as $mb) {
                            $linkFound = false;
                            if ($request->has("bukti.{$keg_id}")) {
                                foreach ($request->bukti[$keg_id]['jenis_id'] as $idx => $jid) {
                                    if ($jid == $mb->id && !empty($request->bukti[$keg_id]['link'][$idx])) {
                                        $linkFound = true;
                                        break;
                                    }
                                }
                            }
                            
                            if (!$linkFound) {
                                $keg = $allKegiatans->where('id', $keg_id)->first();
                                return back()->with('error', 'Bukti wajib "' . $mb->nama_bukti . '" pada kegiatan "' . $keg->nama_kegiatan . '" harus diisi.');
                            }
                        }
                    }
                }
            }
        }

        // Simpan data progress
        if ($request->has('progress')) {
            foreach ($request->progress as $keg_id => $data) {
                if (!empty($data['target_tanggal']) || !empty($data['realisasi_tanggal'])) {
                    $progress = DescanProgressDesa::updateOrCreate(
                        ['peserta_id' => $peserta_id, 'kegiatan_id' => $keg_id],
                        [
                            'target_tanggal' => $data['target_tanggal'],
                            'realisasi_tanggal' => $data['realisasi_tanggal'],
                            'status' => $status,
                            'updated_by' => Auth::id()
                        ]
                    );

                    // Sync Bukti Links: Hapus yang lama, simpan yang baru dari form
                    if ($request->has("bukti.{$keg_id}")) {
                        // Optional: Jika ingin lebih aman, hanya hapus yang ada di form
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
                }
            }
        }

        // Simpan data Output (Global untuk desa di periode ini)
        if ($request->has('output_jenis_id')) {
            \App\Models\DescanOutputDesa::where('peserta_id', $peserta_id)->delete();
            foreach ($request->output_jenis_id as $idx => $j_id) {
                $link = $request->output_link[$idx] ?? null;
                if (!empty($j_id) && !empty($link)) {
                    \App\Models\DescanOutputDesa::create([
                        'peserta_id' => $peserta_id,
                        'jenis_output_id' => $j_id,
                        'link' => $link,
                        'updated_by' => Auth::id(),
                        'created_by' => Auth::id()
                    ]);
                }
            }
        }

        // Simpan data Bukti Dukung / Lainnya (Global untuk desa di periode ini)
        if ($request->has('dukung_jenis_id')) {
            \App\Models\DescanBuktiDukungDesa::where('peserta_id', $peserta_id)->delete();
            foreach ($request->dukung_jenis_id as $idx => $j_id) {
                $link = $request->dukung_link[$idx] ?? null;
                if (!empty($j_id) && !empty($link)) {
                    \App\Models\DescanBuktiDukungDesa::create([
                        'peserta_id' => $peserta_id,
                        'jenis_bukti_id' => $j_id,
                        'link_file' => $link,
                        'created_by' => Auth::id()
                    ]);
                }
            }
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
            'action' => 'required|in:approve,reject'
        ]);

        if ($request->action == 'approve') {
            $progress->update([
                'status' => 'disetujui',
                'verified_by' => Auth::id(),
                'verified_at' => now()
            ]);
            $msg = 'Progress berhasil diverifikasi.';
        } else {
            $progress->update([
                'status' => 'ditolak',
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
