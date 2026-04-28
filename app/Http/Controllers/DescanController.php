<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DescanPeriode;
use App\Models\DescanKuota;
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
        // Mendapatkan kuota
        $kuotas = DescanKuota::with(['periode', 'creator', 'updater'])->paginate(10, ['*'], 'kuota_page');

        // Mendapatkan Master Data Tambahan
        $jenisBuktiKegiatans = DescanJenisBuktiKegiatan::paginate(10, ['*'], 'jbk_page');
        $jenisOutputs = DescanJenisOutput::paginate(10, ['*'], 'output_page');
        $jenisBuktiDukungs = DescanJenisBuktiDukung::paginate(10, ['*'], 'jbd_page');

        return view('desa_cantik.kelola', compact('periodes', 'kegiatans', 'kuotas', 'jenisBuktiKegiatans', 'jenisOutputs', 'jenisBuktiDukungs'));
    }

    public function storePeriode(Request $request)
    {
        $request->validate([
            'tahun' => 'required|digits:4|unique:descan_periode,tahun'
        ]);

        DescanPeriode::create([
            'tahun' => $request->tahun
        ]);

        // Tetap di tab periode
        return redirect()->route('desa-cantik.kelola', ['tab' => 'periode'])->with('success', 'Periode tahun berhasil ditambahkan.');
    }

    public function destroyPeriode($id)
    {
        $periode = DescanPeriode::findOrFail($id);

        $hasPeserta = DescanPeserta::where('periode_id', $id)->exists();
        $hasKuota = DescanKuota::where('periode_id', $id)->exists();

        if ($hasPeserta || $hasKuota) {
            return redirect()->route('desa-cantik.kelola', ['tab' => 'periode'])->with('error', 'Periode tidak dapat dihapus karena sudah memiliki relasi pengaturan kuota atau peserta desa.');
        }

        $periode->delete();
        return redirect()->route('desa-cantik.kelola', ['tab' => 'periode'])->with('success', 'Periode berhasil dihapus.');
    }

    public function storeKuota(Request $request)
    {
        $request->validate([
            'periode_id' => 'required|exists:descan_periode,id',
            'max_kecamatan' => 'required|integer|min:1',
            'max_desa' => 'required|integer|min:1'
        ]);

        $kuota = DescanKuota::where('periode_id', $request->periode_id)->first();
        if ($kuota) {
            $kuota->update([
                'max_kecamatan' => $request->max_kecamatan,
                'max_desa' => $request->max_desa,
                'updated_by' => Auth::id()
            ]);
        } else {
            DescanKuota::create([
                'periode_id' => $request->periode_id,
                'max_kecamatan' => $request->max_kecamatan,
                'max_desa' => $request->max_desa,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id()
            ]);
        }

        return redirect()->route('desa-cantik.kelola', ['tab' => 'kuota'])->with('success', 'Kuota berhasil disimpan.');
    }

    public function updateKuota(Request $request, $id)
    {
        $request->validate([
            'max_kecamatan' => 'required|integer|min:1',
            'max_desa' => 'required|integer|min:1'
        ]);

        $kuota = DescanKuota::findOrFail($id);
        $kuota->update([
            'max_kecamatan' => $request->max_kecamatan,
            'max_desa' => $request->max_desa,
            'updated_by' => Auth::id()
        ]);

        return redirect()->route('desa-cantik.kelola', ['tab' => 'kuota'])->with('success', 'Kuota berhasil diperbarui.');
    }

    public function destroyKuota($id)
    {
        $kuota = DescanKuota::findOrFail($id);

        $hasPeserta = DescanPeserta::where('periode_id', $kuota->periode_id)->exists();
        if ($hasPeserta) {
            return redirect()->route('desa-cantik.kelola', ['tab' => 'kuota'])->with('error', 'Pengaturan Kuota tidak dapat dihapus karena sudah ada peserta yang terdaftar pada periode ini.');
        }

        $kuota->delete();
        return redirect()->route('desa-cantik.kelola', ['tab' => 'kuota'])->with('success', 'Pengaturan Kuota berhasil dihapus.');
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
            'urutan' => $maxUrutan + 1
        ]);

        return redirect()->route('desa-cantik.kelola', ['tab' => 'kegiatan'])->with('success', 'Kegiatan berhasil ditambahkan.');
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
            'nama_kegiatan' => $request->nama_kegiatan
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
        DescanJenisBuktiKegiatan::create(['nama_bukti' => $request->nama_bukti]);
        return redirect()->route('desa-cantik.kelola', ['tab' => 'jbk'])->with('success', 'Jenis Bukti Kegiatan berhasil ditambahkan.');
    }

    public function updateJenisBuktiKegiatan(Request $request, $id)
    {
        $request->validate(['nama_bukti' => 'required|string|max:255|unique:descan_jenis_bukti_kegiatan,nama_bukti,' . $id]);
        DescanJenisBuktiKegiatan::findOrFail($id)->update(['nama_bukti' => $request->nama_bukti]);
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

        // Cek duplikat
        $sudahAda = DescanPeserta::where('periode_id', $request->periode_id)
            ->where('desa_id', $request->desa_id)
            ->exists();
        if ($sudahAda) {
            return back()->with('error', 'Desa ini sudah terdaftar sebagai peserta pada periode yang dipilih.');
        }

        // Ambil kuota
        $kuota = DescanKuota::where('periode_id', $request->periode_id)->first();
        if (!$kuota) {
            return back()->with('error', 'Periode ini belum memiliki pengaturan kuota. Hubungi admin provinsi.');
        }

        // Hitung peserta existing pada periode ini (dari kabupaten yang sama jika bukan provinsi)
        $queryExisting = DescanPeserta::where('periode_id', $request->periode_id);
        if (!$isProvinsi) {
            $queryExisting->where('kabupaten_id', $kabupatenId);
        }
        $existingPesertas = $queryExisting->get();

        // Validasi max_desa
        if ($existingPesertas->count() >= $kuota->max_desa) {
            return back()->with('error', "Kuota desa sudah penuh. Maksimal {$kuota->max_desa} desa untuk periode ini.");
        }

        // Validasi max_kecamatan unik
        $kecUnik = $existingPesertas->pluck('kecamatan_id')->unique();
        $kecBaru = (int) $request->kecamatan_id;
        // Kecamatan baru dan belum ada di existing
        if (!$kecUnik->contains($kecBaru)) {
            if ($kecUnik->count() >= $kuota->max_kecamatan) {
                return back()->with('error', "Kuota kecamatan unik sudah penuh. Maksimal {$kuota->max_kecamatan} kecamatan untuk periode ini.");
            }
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
        $peserta = DescanPeserta::findOrFail($id);

        // Cek apakah sudah ada progress
        $hasProgress = DescanProgressDesa::where('peserta_id', $id)->exists();
        if ($hasProgress) {
            return back()->with('error', 'Peserta tidak dapat dihapus karena sudah memiliki data progress.');
        }

        $peserta->delete();
        return back()->with('success', 'Peserta desa berhasil dihapus.');
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

    public function ajaxKuotaInfo(Request $request)
    {
        $periodeId = $request->periode_id;
        $kuota = DescanKuota::where('periode_id', $periodeId)->first();

        if (!$kuota) {
            return response()->json(['has_kuota' => false]);
        }

        // Hitung jumlah peserta & kecamatan unik yang sudah ada
        $pesertas = DescanPeserta::where('periode_id', $periodeId)->get();
        $jumlahDesa = $pesertas->count();
        $jumlahKec = $pesertas->pluck('kecamatan_id')->unique()->count();

        return response()->json([
            'has_kuota' => true,
            'max_desa' => $kuota->max_desa,
            'max_kecamatan' => $kuota->max_kecamatan,
            'terpakai_desa' => $jumlahDesa,
            'terpakai_kec' => $jumlahKec,
            'sisa_desa' => $kuota->max_desa - $jumlahDesa,
            'sisa_kecamatan' => $kuota->max_kecamatan - $jumlahKec,
        ]);
    }
}
