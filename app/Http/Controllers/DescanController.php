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
            ->where('status', 1) // Menunggu Verifikasi
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

        $query = DescanPeserta::with(['desa', 'kecamatan', 'kabupaten', 'periode', 'progresses.kegiatan'])
            ->orderBy('created_at', 'desc');

        if (!$isProvinsi) {
            $query->where('kabupaten_id', $user->kabupaten_id);
        }

        if ($request->filled('periode_id')) {
            $query->where('periode_id', $request->periode_id);
        }

        $pesertas = $query->paginate(20);

        return view('desa_cantik.progress', compact('pesertas', 'periodes', 'kegiatans', 'isProvinsi'));
    }

    public function progressDetail($peserta_id)
    {
        $peserta = DescanPeserta::with(['desa', 'kecamatan', 'kabupaten', 'periode'])->findOrFail($peserta_id);
        // Hanya ambil kegiatan yang active
        $kegiatans = DescanKegiatan::where('is_active', true)->orderBy('urutan', 'asc')->get();
        $progresses = DescanProgressDesa::with(['buktis.jenisBukti', 'verifier'])
            ->where('peserta_id', $peserta_id)
            ->get()
            ->keyBy('kegiatan_id');

        $isProvinsi = Auth::user()->kabupaten && Auth::user()->kabupaten->kode_kab == '6100';
        $jenisBukti = DescanJenisBuktiKegiatan::orderBy('nama_bukti')->get();

        return view('desa_cantik.progress_detail', compact('peserta', 'kegiatans', 'progresses', 'isProvinsi', 'jenisBukti'));
    }

    public function storeProgress(Request $request, $peserta_id)
    {
        $request->validate([
            'kegiatan_id' => 'required|exists:descan_kegiatan,id',
            'target_tanggal' => 'nullable|date',
            'realisasi_tanggal' => 'nullable|date|after_or_equal:target_tanggal',
            'bukti_link.*' => 'nullable|string',
            'jenis_bukti_id.*' => 'nullable|exists:descan_jenis_bukti_kegiatan,id'
        ], [
            'realisasi_tanggal.after_or_equal' => 'Tanggal realisasi tidak boleh lebih kecil dari tanggal target.'
        ]);

        $peserta = DescanPeserta::findOrFail($peserta_id);
        $kegiatan = DescanKegiatan::findOrFail($request->kegiatan_id);

        if (!$kegiatan->is_active) {
            return back()->with('error', 'Kegiatan ini tidak aktif.');
        }

        // Cek Urutan: Tidak bisa lompat
        if ($kegiatan->urutan > 1) {
            $prevKegiatan = DescanKegiatan::where('urutan', $kegiatan->urutan - 1)->first();
            if ($prevKegiatan) {
                $prevProgress = DescanProgressDesa::where('peserta_id', $peserta_id)
                    ->where('kegiatan_id', $prevKegiatan->id)
                    ->first();

                if (!$prevProgress || $prevProgress->status != 2) {
                    return back()->with('error', 'Kegiatan sebelumnya (' . $prevKegiatan->nama_kegiatan . ') harus diselesaikan dan diverifikasi terlebih dahulu.');
                }
            }
        }

        $progress = DescanProgressDesa::updateOrCreate(
            ['peserta_id' => $peserta_id, 'kegiatan_id' => $request->kegiatan_id],
            [
                'target_tanggal' => $request->target_tanggal,
                'realisasi_tanggal' => $request->realisasi_tanggal,
                'status' => 1, // Menunggu Verifikasi
                'updated_by' => Auth::id()
            ]
        );

        // Handle Bukti Links
        if ($request->has('bukti_link')) {
            foreach ($request->bukti_link as $index => $link) {
                if (!empty($link) && isset($request->jenis_bukti_id[$index])) {
                    \App\Models\DescanBuktiKegiatan::create([
                        'progress_desa_id' => $progress->id,
                        'jenis_bukti_id' => $request->jenis_bukti_id[$index],
                        'link_file' => $link, // Menyimpan teks/link
                        'created_by' => Auth::id()
                    ]);
                }
            }
        }

        return back()->with('success', 'Progress berhasil disimpan dan diajukan untuk verifikasi.');
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
                'status' => 2, // Terverifikasi
                'verified_by' => Auth::id(),
                'verified_at' => now()
            ]);
            $msg = 'Progress berhasil diverifikasi.';
        } else {
            $progress->update([
                'status' => 3, // Ditolak
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
