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
use App\Models\DescanOutputDesa;
use App\Models\DescanBuktiDukungDesa;
use App\Models\DescanPenilaian;
use Illuminate\Support\Facades\Auth;
use App\Exports\DescanProgressExport;
use Maatwebsite\Excel\Facades\Excel;

class DescanController extends Controller
{
    public function index(Request $request)
    {
        $periodes = DescanPeriode::orderBy('tahun', 'desc')->get();
        $selectedPeriodeId = $request->get('periode_id');

        if ($selectedPeriodeId === null) {
            $currentYear = date('Y');
            $selectedPeriode = $periodes->where('tahun', $currentYear)->first();

            if (!$selectedPeriode) {
                $selectedPeriode = $periodes->where('is_active', true)->first();
            }

            $selectedPeriodeId = $selectedPeriode ? $selectedPeriode->id : ($periodes->first() ? $periodes->first()->id : null);
        }

        $queryPeserta = DescanPeserta::when($selectedPeriodeId && $selectedPeriodeId !== 'all', function ($q) use ($selectedPeriodeId) {
            $q->where('periode_id', $selectedPeriodeId);
        });

        $totalPeserta = $queryPeserta->count();

        $kabupatens = Kabupaten::where('kode_kab', '!=', '6100')->orderBy('kode_kab')->get();

        // Optimasi: Ambil jumlah peserta per kabupaten dalam 1 query (GROUP BY)
        $pesertaCounts = DescanPeserta::selectRaw('kabupaten_id, count(*) as count')
            ->when($selectedPeriodeId && $selectedPeriodeId !== 'all', function ($q) use ($selectedPeriodeId) {
                $q->where('periode_id', $selectedPeriodeId);
            })
            ->groupBy('kabupaten_id')
            ->pluck('count', 'kabupaten_id');

        $rekapKabupaten = collect();
        foreach ($kabupatens as $kab) {
            $nama = str_replace(['KABUPATEN ', 'KOTA '], '', strtoupper($kab->nama_kabupaten));
            $rekapKabupaten->push([
                'nama' => '[' . $kab->kode_kab . '] ' . $nama,
                'pesertas_count' => $pesertaCounts->get($kab->id, 0)
            ]);
        }

        // Optimasi: Ambil rekap status dalam 1 query (GROUP BY status)
        $statusCounts = DescanProgressDesa::whereHas('peserta', function ($q) use ($selectedPeriodeId) {
            if ($selectedPeriodeId && $selectedPeriodeId !== 'all') {
                $q->where('periode_id', $selectedPeriodeId);
            }
        })
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $totalDisetujui = $statusCounts->get('disetujui', 0);
        $totalMenunggu = $statusCounts->get('menunggu_verifikasi', 0);
        $totalDitolak = $statusCounts->get('ditolak', 0);
        $totalDraf = $statusCounts->get('draf', 0);

        $trendPeriode = DescanPeriode::orderBy('tahun', 'asc')
            ->withCount('pesertas')
            ->get();

        return view('desa_cantik.index', compact(
            'periodes',
            'selectedPeriodeId',
            'totalPeserta',
            'rekapKabupaten',
            'totalDisetujui',
            'totalMenunggu',
            'totalDitolak',
            'totalDraf',
            'trendPeriode'
        ));
    }

    public function export(Request $request)
    {
        $periodeId = $request->get('periode_id');
        if (!$periodeId) {
            $activePeriode = DescanPeriode::where('is_active', true)->first();
            $periodeId = $activePeriode ? $activePeriode->id : DescanPeriode::max('id');
        }

        $periode = DescanPeriode::find($periodeId);
        $fileName = 'Progres_Desa_Cantik_' . ($periode ? $periode->tahun : 'All') . '.xlsx';

        return Excel::download(new DescanProgressExport($periodeId), $fileName);
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
        $kabupatens = $isProvinsi ? Kabupaten::where('kode_kab', '!=', '6100')->orderByRaw('LENGTH(kode_kab) ASC')->orderBy('kode_kab', 'asc')->get() : collect();
        $kecamatans = !$isProvinsi ? Kecamatan::where('kabupaten_id', $kabupaten?->id)->orderByRaw('LENGTH(kode_kecamatan) ASC')->orderBy('kode_kecamatan', 'asc')->get() : collect();
        $myKabupatenId = $isProvinsi ? null : $kabupaten?->id;

        // Query peserta
        $query = DescanPeserta::with(['desa', 'kecamatan', 'creator', 'kabupaten', 'periode'])
            ->select('descan_peserta.*')
            ->join('tb_kabupaten', 'descan_peserta.kabupaten_id', '=', 'tb_kabupaten.id')
            ->join('tb_kecamatan', 'descan_peserta.kecamatan_id', '=', 'tb_kecamatan.id')
            ->join('tb_desa', 'descan_peserta.desa_id', '=', 'tb_desa.id')
            ->orderByRaw('LENGTH(tb_kabupaten.kode_kab) ASC')
            ->orderBy('tb_kabupaten.kode_kab', 'asc')
            ->orderByRaw('LENGTH(tb_kecamatan.kode_kecamatan) ASC')
            ->orderBy('tb_kecamatan.kode_kecamatan', 'asc')
            ->orderByRaw('LENGTH(tb_desa.kode_desa) ASC')
            ->orderBy('tb_desa.kode_desa', 'asc');

        // Filter oleh kabupaten jika bukan provinsi
        if (!$isProvinsi) {
            $query->where('descan_peserta.kabupaten_id', $myKabupatenId);
        } else {
            if ($request->filled('kabupaten_id')) {
                $query->where('descan_peserta.kabupaten_id', $request->kabupaten_id);
            }
        }

        // Filter periode
        if ($request->filled('filter_periode')) {
            $query->where('periode_id', $request->filter_periode);
        }

        $pesertas = $query->paginate(20)->withQueryString();

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

        // Cek apakah sudah ada data terkait (progres atau penilaian)
        $hasProgress = DescanProgressDesa::where('peserta_id', $id)->exists();
        $hasPenilaian = DescanPenilaian::where('peserta_id', $id)->exists();

        if ($hasProgress || $hasPenilaian) {
            return back()->with('error', 'Peserta tidak dapat dihapus karena sudah memiliki data progres atau penilaian.');
        }

        $peserta->delete();
        return back()->with('success', 'Peserta desa berhasil dihapus.');
    }

    // =========================================================
    // PENILAIAN DESA
    // =========================================================

    public function penilaian(Request $request)
    {
        $user = Auth::user();
        $kabupaten = $user->kabupaten;
        $isProvinsi = $kabupaten && $kabupaten->kode_kab == '6100';

        $periodes = DescanPeriode::orderBy('tahun', 'desc')->get();
        $myKabupatenId = $isProvinsi ? null : $kabupaten?->id;
        $kabupatens = $isProvinsi ? Kabupaten::where('kode_kab', '!=', '6100')->orderByRaw('LENGTH(kode_kab) ASC')->orderBy('kode_kab', 'asc')->get() : collect();

        // Query peserta along with penilaian relation
        $query = DescanPeserta::with(['desa', 'kecamatan', 'kabupaten', 'periode', 'penilaian'])
            ->select('descan_peserta.*')
            ->join('tb_kabupaten', 'descan_peserta.kabupaten_id', '=', 'tb_kabupaten.id')
            ->join('tb_kecamatan', 'descan_peserta.kecamatan_id', '=', 'tb_kecamatan.id')
            ->join('tb_desa', 'descan_peserta.desa_id', '=', 'tb_desa.id')
            ->orderByRaw('LENGTH(tb_kabupaten.kode_kab) ASC')
            ->orderBy('tb_kabupaten.kode_kab', 'asc')
            ->orderByRaw('LENGTH(tb_kecamatan.kode_kecamatan) ASC')
            ->orderBy('tb_kecamatan.kode_kecamatan', 'asc')
            ->orderByRaw('LENGTH(tb_desa.kode_desa) ASC')
            ->orderBy('tb_desa.kode_desa', 'asc');

        if (!$isProvinsi) {
            $query->where('descan_peserta.kabupaten_id', $myKabupatenId);
        } else {
            if ($request->filled('kabupaten_id')) {
                $query->where('descan_peserta.kabupaten_id', $request->kabupaten_id);
            }
        }

        if ($request->filled('filter_periode')) {
            $query->where('periode_id', $request->filter_periode);
        }

        $pesertas = $query->paginate(20)->withQueryString();

        return view('desa_cantik.penilaian', compact(
            'periodes',
            'kabupatens',
            'myKabupatenId',
            'isProvinsi',
            'pesertas'
        ));
    }

    public function updatePenilaian(Request $request, $peserta_id)
    {
        $user = Auth::user();
        $isProvinsi = $user->kabupaten && $user->kabupaten->kode_kab == '6100';

        $peserta = DescanPeserta::findOrFail($peserta_id);

        if (!$isProvinsi && $peserta->kabupaten_id != $user->kabupaten_id) {
            return back()->with('error', 'Anda tidak memiliki akses ke peserta ini.');
        }

        $request->validate([
            'catatan' => 'nullable|string'
        ]);

        $penilaian = \App\Models\DescanPenilaian::firstOrNew(['peserta_id' => $peserta_id]);

        if (!$isProvinsi) {
            $penilaian->penilaian_mandiri_desa = $request->has('penilaian_mandiri_desa');
            $penilaian->penilaian_mandiri_kab = $request->has('penilaian_mandiri_kab');
        } else {
            $penilaian->verifikasi_provinsi = $request->has('verifikasi_provinsi');
            $penilaian->catatan = $request->input('catatan');
        }

        // Jika semua 0/false dan catatan kosong, hapus barisnya (jika ada)
        if (
            !$penilaian->penilaian_mandiri_desa &&
            !$penilaian->penilaian_mandiri_kab &&
            !$penilaian->verifikasi_provinsi &&
            empty($penilaian->catatan)
        ) {
            if ($penilaian->exists) {
                $penilaian->delete();
            }
        } else {
            $penilaian->save();
        }

        return back()->with('success', 'Penilaian berhasil diperbarui.');
    }

    // =========================================================
    // PROGRESS KEGIATAN
    // =========================================================

    public function progress(Request $request)
    {

        $user = Auth::user();
        $isProvinsi = $user->kabupaten && $user->kabupaten->kode_kab == '6100';
        $periodes = DescanPeriode::orderBy('tahun', 'desc')->get();
        // Hanya ambil kegiatan yang active
        $kegiatans = DescanKegiatan::where('is_active', true)->orderBy('urutan', 'asc')->get();

        $myKabupatenId = $isProvinsi ? null : $user->kabupaten_id;
        $kabupatens = $isProvinsi ? Kabupaten::where('kode_kab', '!=', '6100')->orderByRaw('LENGTH(kode_kab) ASC')->orderBy('kode_kab', 'asc')->get() : collect();

        $query = DescanPeserta::with(['desa', 'kecamatan', 'kabupaten', 'periode', 'progresses.buktis', 'outputs', 'buktiDukungs'])
            ->select('descan_peserta.*')
            ->join('tb_kabupaten', 'descan_peserta.kabupaten_id', '=', 'tb_kabupaten.id')
            ->join('tb_kecamatan', 'descan_peserta.kecamatan_id', '=', 'tb_kecamatan.id')
            ->join('tb_desa', 'descan_peserta.desa_id', '=', 'tb_desa.id')
            ->orderByRaw('LENGTH(tb_kabupaten.kode_kab) ASC')
            ->orderBy('tb_kabupaten.kode_kab', 'asc')
            ->orderByRaw('LENGTH(tb_kecamatan.kode_kecamatan) ASC')
            ->orderBy('tb_kecamatan.kode_kecamatan', 'asc')
            ->orderByRaw('LENGTH(tb_desa.kode_desa) ASC')
            ->orderBy('tb_desa.kode_desa', 'asc');

        if (!$isProvinsi) {
            $query->where('descan_peserta.kabupaten_id', $user->kabupaten_id);
        } else {
            if ($request->filled('kabupaten_id')) {
                $query->where('descan_peserta.kabupaten_id', $request->kabupaten_id);
            }
        }

        if ($request->filled('periode_id')) {
            $query->where('descan_peserta.periode_id', $request->periode_id);
        }

        if ($request->filled('kecamatan_id')) {
            $query->where('descan_peserta.kecamatan_id', $request->kecamatan_id);
        }

        if ($request->filled('desa_id')) {
            $query->where('descan_peserta.desa_id', $request->desa_id);
        }

        // Keep selected values for select2
        $selectedKecamatan = $request->filled('kecamatan_id') ? Kecamatan::find($request->kecamatan_id) : null;
        $selectedDesa = $request->filled('desa_id') ? Desa::find($request->desa_id) : null;

        // Count action needed (menunggu_verifikasi for Provinsi, ditolak for Kabupaten)
        $actionCondition = function ($q) use ($isProvinsi) {
            $status = $isProvinsi ? 'menunggu_verifikasi' : 'ditolak';
            $q->whereHas('progresses', fn($sq) => $sq->where('status', $status))
                ->orWhereHas('outputs', fn($sq) => $sq->where('status', $status))
                ->orWhereHas('buktiDukungs', fn($sq) => $sq->where('status', $status));
        };

        $countQuery = clone $query;
        $countAction = $countQuery->where($actionCondition)->count();

        // Count perbaikan for Provinsi
        $countPerbaikan = 0;
        if ($isProvinsi) {
            $perbaikanCondition = function ($q) {
                $q->whereHas('progresses', fn($sq) => $sq->where('status', 'ditolak'))
                    ->orWhereHas('outputs', fn($sq) => $sq->where('status', 'ditolak'))
                    ->orWhereHas('buktiDukungs', fn($sq) => $sq->where('status', 'ditolak'));
            };
            $countQueryPerbaikan = clone $query;
            $countPerbaikan = $countQueryPerbaikan->where($perbaikanCondition)->count();

            if ($request->input('action_needed') == 'perbaikan') {
                $query->where($perbaikanCondition);
            }
        }

        // Count draf
        $drafCondition = function ($q) {
            $q->whereHas('progresses', fn($sq) => $sq->where('status', 'draf'))
                ->orWhereHas('outputs', fn($sq) => $sq->where('status', 'draf'))
                ->orWhereHas('buktiDukungs', fn($sq) => $sq->where('status', 'draf'));
        };
        $countQueryDraf = clone $query;
        $countDraf = $countQueryDraf->where($drafCondition)->count();

        if ($request->input('action_needed') == 'draf') {
            $query->where($drafCondition);
        }

        if ($request->input('action_needed') == '1') {
            $query->where($actionCondition);
        }

        $pesertas = $query->paginate(10)->withQueryString();
        $mandatoryBuktiIds = DescanJenisBuktiKegiatan::where('is_wajib', true)->pluck('id')->toArray();
        $mandatoryOutputIds = DescanJenisOutput::where('is_wajib', true)->pluck('id')->toArray();
        $mandatoryDukungIds = DescanJenisBuktiDukung::where('is_wajib', true)->pluck('id')->toArray();

        // Get all types for indicators
        $allJenisOutputs = DescanJenisOutput::orderBy('is_wajib', 'desc')->orderBy('nama_output')->get();
        $allJenisDukungs = DescanJenisBuktiDukung::orderBy('is_wajib', 'desc')->orderBy('nama_bukti')->get();

        return view('desa_cantik.progress', compact(
            'pesertas',
            'periodes',
            'kegiatans',
            'isProvinsi',
            'mandatoryBuktiIds',
            'mandatoryOutputIds',
            'mandatoryDukungIds',
            'allJenisOutputs',
            'allJenisDukungs',
            'kabupatens',
            'myKabupatenId',
            'selectedKecamatan',
            'selectedDesa',
            'countAction',
            'countPerbaikan',
            'countDraf'
        ));
    }

    public function progressDetail($peserta_id)
    {
        $peserta = DescanPeserta::with([
            'desa',
            'kecamatan',
            'kabupaten',
            'periode',
            'outputs.creator.kabupaten',
            'outputs.updater.kabupaten',
            'buktiDukungs.creator.kabupaten'
        ])->findOrFail($peserta_id);
        $kegiatans = DescanKegiatan::where('is_active', true)
            ->leftJoin('descan_progress_desa', function ($join) use ($peserta_id) {
                $join->on('descan_kegiatan.id', '=', 'descan_progress_desa.kegiatan_id')
                    ->where('descan_progress_desa.peserta_id', '=', $peserta_id);
            })
            ->select('descan_kegiatan.*', \DB::raw('COALESCE(descan_progress_desa.urutan, descan_kegiatan.urutan) as urutan'))
            ->orderByRaw('(descan_progress_desa.id IS NOT NULL) DESC, COALESCE(descan_progress_desa.urutan, descan_kegiatan.urutan) ASC, descan_kegiatan.id ASC')
            ->get();

        $progresses = DescanProgressDesa::with(['buktis.jenisBukti', 'verifier.kabupaten', 'creator.kabupaten', 'updater.kabupaten'])
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
        $allKegiatans = DescanKegiatan::where('is_active', true)
            ->leftJoin('descan_progress_desa', function ($join) use ($peserta_id) {
                $join->on('descan_kegiatan.id', '=', 'descan_progress_desa.kegiatan_id')
                    ->where('descan_progress_desa.peserta_id', '=', $peserta_id);
            })
            ->select('descan_kegiatan.*', \DB::raw('COALESCE(descan_progress_desa.urutan, descan_kegiatan.urutan) as urutan'))
            ->orderByRaw('(descan_progress_desa.id IS NOT NULL) DESC, COALESCE(descan_progress_desa.urutan, descan_kegiatan.urutan) ASC, descan_kegiatan.id ASC')
            ->get();
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
                $dbProg = \App\Models\DescanProgressDesa::with('buktis')->where('peserta_id', $peserta_id)->where('kegiatan_id', $keg->id)->first();

                if (!$data && $dbProg) {
                    $data = [
                        'target_tanggal' => $dbProg->target_tanggal,
                        'realisasi_tanggal' => $dbProg->realisasi_tanggal
                    ];
                }

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


                        if (!$linkFound && $dbProg) {
                            $dbBukti = $dbProg->buktis->where('jenis_bukti_id', $mb->id)->first();
                            if ($dbBukti && !empty($dbBukti->link_file)) {
                                $linkFound = true;
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
                    $dbOutput = \App\Models\DescanOutputDesa::where('peserta_id', $peserta_id)
                        ->where('jenis_output_id', $jo->id)
                        ->where(function ($q) {
                            $q->where('status', 'disetujui')
                                ->orWhere('status', 'menunggu_verifikasi')
                                ->orWhereNotNull('link');
                        })
                        ->first();
                    if ($dbOutput && !empty($dbOutput->link)) {
                        $linkFound = true;
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
                    $dbDukung = \App\Models\DescanBuktiDukungDesa::where('peserta_id', $peserta_id)
                        ->where('jenis_bukti_id', $jd->id)
                        ->where(function ($q) {
                            $q->where('status', 'disetujui')
                                ->orWhere('status', 'menunggu_verifikasi')
                                ->orWhereNotNull('link_file');
                        })
                        ->first();
                    if ($dbDukung && !empty($dbDukung->link_file)) {
                        $linkFound = true;
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

                    $existingProg = DescanProgressDesa::where('peserta_id', $peserta_id)->where('kegiatan_id', $keg_id)->first();
                    $progress = DescanProgressDesa::updateOrCreate(
                        ['peserta_id' => $peserta_id, 'kegiatan_id' => $keg_id],
                        [
                            'urutan' => $existingProg ? $existingProg->urutan : $urutan, // Simpan urutan saat ini ke progress (lock order)
                            'target_tanggal' => $data['target_tanggal'],
                            'realisasi_tanggal' => $data['realisasi_tanggal'],
                            'status' => $status,
                            'updated_by' => Auth::id(),
                            'created_by' => $existingProg ? $existingProg->created_by : Auth::id()
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
            // Kecuali yang statusnya sudah disetujui atau menunggu verifikasi (karena di UI di-disable jadi tidak terkirim)
            \App\Models\DescanOutputDesa::where('peserta_id', $peserta_id)
                ->whereNotIn('id', $existingOutputIds)
                ->whereNotIn('status', ['disetujui', 'menunggu_verifikasi'])
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
            // Hapus yang tidak ada di form (untuk opsional)
            // Kecuali yang statusnya sudah disetujui atau menunggu verifikasi
            \App\Models\DescanBuktiDukungDesa::where('peserta_id', $peserta_id)
                ->whereNotIn('id', $existingDukungIds)
                ->whereNotIn('status', ['disetujui', 'menunggu_verifikasi'])
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

        if ($progress->status != 'menunggu_verifikasi') {
            return back()->with('error', 'Komponen ini sudah pernah diverifikasi atau tidak dalam status menunggu verifikasi.');
        }

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
                'verified_by' => Auth::id(),
                'verified_at' => now(),
                'alasan_penolakan' => $request->alasan_penolakan
            ]);
            $msg = 'Progress ditolak.';
        }

        return back()->with('success', $msg);
    }

    public function verifyOutput(Request $request, $peserta_id, $output_id)
    {
        $user = Auth::user();
        $isProvinsi = $user->kabupaten && $user->kabupaten->kode_kab == '6100';

        if (!$isProvinsi) {
            return back()->with('error', 'Hanya admin provinsi yang dapat melakukan verifikasi.');
        }

        $output = \App\Models\DescanOutputDesa::findOrFail($output_id);

        if ($output->status != 'menunggu_verifikasi') {
            return back()->with('error', 'Output ini sudah pernah diverifikasi atau tidak dalam status menunggu verifikasi.');
        }

        $request->validate([
            'action' => 'required|in:approve,reject',
            'alasan_penolakan' => 'nullable|string'
        ]);

        if ($request->action == 'approve') {
            $output->update([
                'status' => 'disetujui',
                'alasan_penolakan' => null,
                'updated_by' => Auth::id()
            ]);
            $msg = 'Output berhasil diverifikasi.';
        } else {
            $output->update([
                'status' => 'ditolak',
                'alasan_penolakan' => $request->alasan_penolakan,
                'updated_by' => Auth::id()
            ]);
            $msg = 'Output ditolak.';
        }

        return back()->with('success', $msg);
    }

    public function verifyDukung(Request $request, $peserta_id, $dukung_id)
    {
        $user = Auth::user();
        $isProvinsi = $user->kabupaten && $user->kabupaten->kode_kab == '6100';

        if (!$isProvinsi) {
            return back()->with('error', 'Hanya admin provinsi yang dapat melakukan verifikasi.');
        }

        $dukung = \App\Models\DescanBuktiDukungDesa::findOrFail($dukung_id);

        if ($dukung->status != 'menunggu_verifikasi') {
            return back()->with('error', 'Bukti dukung ini sudah pernah diverifikasi atau tidak dalam status menunggu verifikasi.');
        }

        $request->validate([
            'action' => 'required|in:approve,reject',
            'alasan_penolakan' => 'nullable|string'
        ]);

        if ($request->action == 'approve') {
            $dukung->update([
                'status' => 'disetujui',
                'alasan_penolakan' => null
            ]);
            $msg = 'Bukti dukung berhasil diverifikasi.';
        } else {
            $dukung->update([
                'status' => 'ditolak',
                'alasan_penolakan' => $request->alasan_penolakan
            ]);
            $msg = 'Bukti dukung ditolak.';
        }

        return back()->with('success', $msg);
    }

    public function verifyAllProgress(Request $request, $peserta_id)
    {
        $user = Auth::user();
        $isProvinsi = $user->kabupaten && $user->kabupaten->kode_kab == '6100';

        if (!$isProvinsi) {
            return back()->with('error', 'Hanya admin provinsi yang dapat melakukan verifikasi.');
        }

        $request->validate([
            'action' => 'required|in:approve,reject',
            'alasan_penolakan' => 'required_if:action,reject'
        ]);

        // Check if there's anything to verify
        $countKeg = DescanProgressDesa::where('peserta_id', $peserta_id)->where('status', 'menunggu_verifikasi')->count();
        $countOut = \App\Models\DescanOutputDesa::where('peserta_id', $peserta_id)->where('status', 'menunggu_verifikasi')->count();
        $countDuk = \App\Models\DescanBuktiDukungDesa::where('peserta_id', $peserta_id)->where('status', 'menunggu_verifikasi')->count();

        if ($countKeg == 0 && $countOut == 0 && $countDuk == 0) {
            return back()->with('info', 'Tidak ada komponen yang dalam status menunggu verifikasi saat ini.');
        }

        $status = ($request->action == 'approve') ? 'disetujui' : 'ditolak';

        $updateData = [
            'status' => $status,
        ];

        if ($request->action == 'approve') {
            $updateData['alasan_penolakan'] = null;
        } else {
            $updateData['alasan_penolakan'] = $request->alasan_penolakan;
        }

        // 1. Update Kegiatan (has verified_by and verified_at)
        $progUpdate = $updateData;
        $progUpdate['verified_by'] = Auth::id();
        $progUpdate['verified_at'] = now();

        DescanProgressDesa::where('peserta_id', $peserta_id)
            ->where('status', 'menunggu_verifikasi')
            ->update($progUpdate);

        // 2. Update Output
        DescanOutputDesa::where('peserta_id', $peserta_id)
            ->where('status', 'menunggu_verifikasi')
            ->update($updateData);

        // 3. Update Bukti Dukung
        DescanBuktiDukungDesa::where('peserta_id', $peserta_id)
            ->where('status', 'menunggu_verifikasi')
            ->update($updateData);

        $msg = $request->action == 'approve' ? 'Seluruh progress berhasil disetujui.' : 'Seluruh progress berhasil ditolak.';
        return back()->with('success', $msg);
    }

    // =========================================================
    // AJAX ENDPOINTS (Dropdown bertingkat)
    // =========================================================

    public function ajaxKecamatan(Request $request)
    {
        $kabupatenId = $request->kabupaten_id;
        $search = $request->get('search', '');
        $limit = (int) $request->get('limit', 10);

        $kecamatans = Kecamatan::where('kabupaten_id', $kabupatenId)
            ->when($search, function ($q) use ($search) {
                $q->where('nama_kecamatan', 'like', "%{$search}%")
                    ->orWhere('kode_kecamatan', 'like', "%{$search}%");
            })
            ->orderByRaw('LENGTH(kode_kecamatan) ASC')
            ->orderBy('kode_kecamatan', 'asc')
            ->limit($limit)
            ->get(['id', 'nama_kecamatan', 'kode_kecamatan']);

        $result = $kecamatans->map(function ($k) {
            return [
                'id' => $k->id,
                'text' => "[{$k->kode_kecamatan}] {$k->nama_kecamatan}",
                'nama_kecamatan' => $k->nama_kecamatan
            ];
        });

        return response()->json($result);
    }

    public function ajaxDesa(Request $request)
    {
        $kecamatanId = $request->kecamatan_id;
        $periodeId = $request->periode_id;
        $search = $request->get('search', '');
        $limit = (int) $request->get('limit', 10);

        $desas = Desa::where('kecamatan_id', $kecamatanId)
            ->when($search, function ($q) use ($search) {
                $q->where('nama_desa', 'like', "%{$search}%")
                    ->orWhere('kode_desa', 'like', "%{$search}%");
            })
            ->orderByRaw('LENGTH(kode_desa) ASC')
            ->orderBy('kode_desa', 'asc')
            ->limit($limit)
            ->get(['id', 'nama_desa', 'kode_desa']);

        // Tandai desa yang sudah terdaftar di periode ini
        $terdaftar = DescanPeserta::where('periode_id', $periodeId)
            ->pluck('desa_id')
            ->toArray();

        // Cari riwayat keikutsertaan di periode sebelumnya
        $previousPesertas = DescanPeserta::with('periode')
            ->whereIn('desa_id', $desas->pluck('id'))
            ->where('periode_id', '!=', $periodeId)
            ->get()
            ->groupBy('desa_id');

        $result = $desas->map(function ($d) use ($terdaftar, $previousPesertas, $request) {
            $codePrefix = "[{$d->kode_desa}] ";

            if ($request->get('is_filter') == '1') {
                return [
                    'id' => $d->id,
                    'text' => $codePrefix . $d->nama_desa,
                    'disabled' => false
                ];
            }

            $terdaftarFlag = in_array($d->id, $terdaftar);
            $previous = $previousPesertas->get($d->id);
            $previousTahun = $previous ? $previous->map(fn($p) => $p->periode->tahun ?? '')->filter()->sort()->join(', ') : '';

            $isDisabled = $terdaftarFlag || !empty($previousTahun);

            $text = $codePrefix . $d->nama_desa;
            if ($terdaftarFlag) {
                $text .= ' ✓ terdaftar di periode ini';
            } elseif (!empty($previousTahun)) {
                $text .= ' (Pernah ikut: ' . $previousTahun . ')';
            }

            return [
                'id' => $d->id,
                'nama_desa' => $d->nama_desa,
                'sudah_terdaftar' => $terdaftarFlag,
                'text' => $text,
                'disabled' => $isDisabled,
                'previous_tahun' => $previousTahun,
            ];
        });

        return response()->json($result);
    }


}
