<?php

namespace App\Http\Controllers;

use App\Models\VariabelKemiskinan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VariabelController extends Controller
{
    public function store(Request $request)
    {
        if (auth()->check() == false) {
            return redirect('/poverty')->with('error', 'Silahkan login terlebih dahulu.');
        }
        $user = Auth::user();
        if (!$user->kabupaten || $user->kabupaten->kode_kab != '6100') {
            return back()->with('error', 'Akses Ditolak: Hanya BPS Provinsi (6100) yang dapat menambah variabel.')->with('active_tab', 'pills-rh-settings-tab');
        }
        $request->validate([
            'nama_variabel' => 'required|string|max:100',
            'bulan' => 'nullable|integer|between:1,12',
            'tahun' => 'required|integer|between:2000,2099',
        ], [
            'nama_variabel.required' => 'Nama Variabel wajib diisi.',
            'tahun.required' => 'Tahun wajib diisi.',
            'tahun.between' => 'Tahun harus antara 2000 - 2099.',
        ]);

        // Check for double input (nama, bulan, tahun)
        $exists = VariabelKemiskinan::where('nama_variabel', $request->nama_variabel)
            ->where('bulan', $request->bulan)
            ->where('tahun', $request->tahun)
            ->exists();

        if ($exists) {
            return redirect()->back()->with('error', 'Variabel dengan bulan dan tahun tersebut sudah ada!');
        }

        VariabelKemiskinan::create([
            'nama_variabel' => $request->nama_variabel,
            'bulan' => $request->bulan,
            'tahun' => $request->tahun,
            'user_id_add' => Auth::id() ?? 1,
        ]);

        return redirect()->back()->with('success', 'Data Variabel berhasil ditambahkan!');
    }

    public function destroy($id)
    {
        if (auth()->check() == false) {
            return redirect('/poverty')->with('error', 'Silahkan login terlebih dahulu.');
        }
        $user = Auth::user();
        if (!$user->kabupaten || $user->kabupaten->kode_kab != '6100') {
            return back()->with('error', 'Akses Ditolak: Hanya BPS Provinsi (6100) yang dapat menghapus tahun.')->with('active_tab', 'pills-rh-settings-tab');
        }
        $variabel = VariabelKemiskinan::withCount('nilaiKemiskinan')->findOrFail($id);

        if ($variabel->nilai_kemiskinan_count > 0) {
            return redirect()->back()->with('error', 'Variabel tidak dapat dihapus karena sudah memiliki data nilai kemiskinan!');
        }

        $variabel->delete();

        return redirect()->back()->with('success', 'Data Variabel berhasil dihapus!');
    }
}
