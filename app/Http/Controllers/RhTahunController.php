<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RhTahun;
use App\Models\RhPerubahanHeader;
use Illuminate\Support\Facades\Auth;

class RhTahunController extends Controller
{
    public function storeTahun(Request $request)
    {
        $request->validate([
            'tahun' => 'required|numeric|unique:tb_rh_tahun,tahun',
            'batas_selisih_harga' => 'required|numeric|min:0',
        ]);

        RhTahun::create([
            'tahun' => $request->tahun,
            'is_active' => false,
            'batas_selisih_harga' => $request->batas_selisih_harga,
            'user_id_add' => Auth::id() ?? 1,
        ]);

        return back()->with('success', 'Tahun RH berhasil ditambahkan.')->with('active_tab', 'pills-rh-settings-tab');
    }

    public function toggleActive($id)
    {
        $tahun = RhTahun::findOrFail($id);

        // Deactivate all others
        RhTahun::where('id', '!=', $id)->update(['is_active' => false]);

        $tahun->is_active = !$tahun->is_active;
        $tahun->save();

        return back()->with('success', 'Status aktif tahun berhasil diubah.')->with('active_tab', 'pills-rh-settings-tab');
    }

    public function destroyTahun($id)
    {
        $tahun = RhTahun::findOrFail($id);

        if ($tahun->perubahanHeaders()->count() > 0) {
            return back()->with('error', 'Tahun tidak bisa dihapus karena memiliki data perubahan.')->with('active_tab', 'pills-rh-settings-tab');
        }

        $tahun->delete();
        return back()->with('success', 'Tahun RH berhasil dihapus.')->with('active_tab', 'pills-rh-settings-tab');
    }

    public function updateTahun(Request $request, $id)
    {
        $request->validate([
            'tahun' => 'required|numeric|unique:tb_rh_tahun,tahun,' . $id,
            'batas_selisih_harga' => 'required|numeric|min:0',
        ]);

        $tahun = RhTahun::findOrFail($id);
        $tahun->update([
            'tahun' => $request->tahun,
            'batas_selisih_harga' => $request->batas_selisih_harga,
        ]);

        return back()->with('success', 'Tahun RH berhasil diperbarui.')->with('active_tab', 'pills-rh-settings-tab');
    }

    public function storePerubahan(Request $request)
    {
        $request->validate([
            'rh_tahun_id' => 'required|exists:tb_rh_tahun,id',
            'tanggal_perubahan' => 'required|date',
        ]);

        // Auto generate label: Perubahan RH 3 Maret 2025
        $date = \Carbon\Carbon::parse($request->tanggal_perubahan);
        $label = 'Perubahan RH ' . $date->translatedFormat('j F Y');

        RhPerubahanHeader::create([
            'rh_tahun_id' => $request->rh_tahun_id,
            'tanggal_perubahan' => $request->tanggal_perubahan,
            'label' => $label,
            'user_id_add' => Auth::id() ?? 1,
        ]);

        return back()->with('success', 'Header perubahan berhasil ditambahkan.')->with('active_tab', 'pills-rh-settings-tab');
    }

    public function updatePerubahan(Request $request, $id)
    {
        $request->validate([
            'tanggal_perubahan' => 'required|date',
        ]);

        $perubahan = RhPerubahanHeader::findOrFail($id);

        // Auto generate label: Perubahan RH 3 Maret 2025
        $date = \Carbon\Carbon::parse($request->tanggal_perubahan);
        $label = 'Perubahan RH ' . $date->translatedFormat('j F Y');

        $perubahan->update([
            'tanggal_perubahan' => $request->tanggal_perubahan,
            'label' => $label,
        ]);

        return back()->with('success', 'Header perubahan berhasil diperbarui.')->with('active_tab', 'pills-rh-settings-tab');
    }

    public function destroyPerubahan($id)
    {
        $perubahan = RhPerubahanHeader::findOrFail($id);
        $perubahan->delete();

        return back()->with('success', 'Header perubahan berhasil dihapus.')->with('active_tab', 'pills-rh-settings-tab');
    }
}
