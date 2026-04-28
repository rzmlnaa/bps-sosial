<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DescanPeriode;
use App\Models\DescanKuota;
use App\Models\DescanKegiatan;
use App\Models\DescanPeserta;
use App\Models\DescanProgressDesa;
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

        return view('desa_cantik.kelola', compact('periodes', 'kegiatans', 'kuotas'));
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
}
