<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RhTahun;
use App\Models\RhPerubahanHeader;
use App\Models\RhPerubahanDetail;
use Illuminate\Support\Facades\Auth;

class RhTahunController extends Controller
{
    public function storeTahun(Request $request)
    {
        $request->validate([
            'tahun' => 'required|numeric|unique:tb_rh_tahun,tahun',
        ]);

        // Prevent adding year smaller than existing minimum year
        $minExistingYear = RhTahun::min('tahun');
        if ($minExistingYear && $request->tahun < $minExistingYear) {
            return back()->with('error', "Gagal menambah tahun. Tahun tidak boleh lebih kecil dari tahun paling awal yang sudah ada ($minExistingYear).")->with('active_tab', 'pills-rh-settings-tab');
        }

        // Prevent adding year if there are pending or rejected verifications in ANY year
        // Use global check because new year usually carries over from previous, which must be stable.
        $hasPending = RhPerubahanDetail::whereIn('verification_status', ['pending', 'rejected'])->exists();
        if ($hasPending) {
            return redirect('/verification')->with('error', "Gagal menambah tahun. Masih ada data perubahan dengan status verifikasi Pending atau Rejected.")->with('active_tab', 'pills-rh-settings-tab');
        }

        RhTahun::create([
            'tahun' => $request->tahun,
            'is_active' => false,
            'user_id_add' => Auth::id() ?? 1,
        ]);

        return back()->with('success', 'Tahun RH berhasil ditambahkan.')->with('active_tab', 'pills-rh-settings-tab');
    }

    public function toggleActive($id)
    {
        $hasPending = RhPerubahanDetail::whereIn('verification_status', ['pending', 'rejected'])->exists();
        if ($hasPending) {
            return redirect('/verification')->with('error', "Gagal mengubah status tahun. Masih ada data perubahan dengan status verifikasi Pending atau Rejected.")->with('active_tab', 'pills-rh-settings-tab');
        }
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

        $hasValues = $tahun->perubahanDetails()
            ->where(function ($q) {
                $q->whereNotNull('min_edit')
                    ->orWhereNotNull('max_edit');
            })
            ->exists();

        if ($tahun->perubahanHeaders()->count() > 0 || $hasValues) {
            return back()->with('error', 'Tahun tidak bisa dihapus karena memiliki data perubahan atau nilai min/max.')->with('active_tab', 'pills-rh-settings-tab');
        }

        // Delete associated details (empty ones) before deleting year
        $tahun->perubahanDetails()->delete();
        $tahun->delete();
        return back()->with('success', 'Tahun RH berhasil dihapus.')->with('active_tab', 'pills-rh-settings-tab');
    }

    public function updateTahun(Request $request, $id)
    {
        $request->validate([
            'tahun' => 'required|numeric|unique:tb_rh_tahun,tahun,' . $id,
        ]);

        $tahun = RhTahun::findOrFail($id);

        $hasValues = $tahun->perubahanDetails()
            ->where(function ($q) {
                $q->whereNotNull('min_edit')
                    ->orWhereNotNull('max_edit');
            })
            ->exists();

        if ($tahun->perubahanHeaders()->count() > 0 || $hasValues) {
            return back()->with('error', 'Tahun tidak bisa diedit karena memiliki data perubahan atau nilai min/max.')->with('active_tab', 'pills-rh-settings-tab');
        }
        $tahun->update([
            'tahun' => $request->tahun,
        ]);

        return back()->with('success', 'Tahun RH berhasil diperbarui.')->with('active_tab', 'pills-rh-settings-tab');
    }

    public function storePerubahan(Request $request)
    {
        $request->validate([
            'rh_tahun_id' => 'required|exists:tb_rh_tahun,id',
            'tanggal_perubahan' => 'required|date',
        ]);

        $rhTahun = RhTahun::findOrFail($request->rh_tahun_id);

        // Ensure date is in the same year
        $year = \Carbon\Carbon::parse($request->tanggal_perubahan)->year;
        if ($year != $rhTahun->tahun) {
            return back()->with('error', "Tanggal harus berada pada tahun {$rhTahun->tahun}.")->with('active_tab', 'pills-rh-settings-tab');
        }

        // Prevent adding revision header if there are pending or rejected verifications in THIS year
        $hasPending = RhPerubahanDetail::where('rh_tahun_id', $rhTahun->id)
            ->whereIn('verification_status', ['pending', 'rejected'])
            ->exists();

        if ($hasPending) {
            return redirect('/verification')->with('error', "Gagal menambah header perubahan. Masih ada data perubahan di tahun ini dengan status verifikasi Pending atau Rejected.")->with('active_tab', 'pills-rh-settings-tab');
        }

        // Prevent duplicate or earlier date
        $latestRevision = RhPerubahanHeader::where('rh_tahun_id', $rhTahun->id)
            ->orderBy('tanggal_perubahan', 'desc')
            ->first();

        if ($latestRevision && $request->tanggal_perubahan <= $latestRevision->tanggal_perubahan) {
            $formattedDate = \Carbon\Carbon::parse($latestRevision->tanggal_perubahan)->translatedFormat('j F Y');
            return back()->with('error', "Tanggal perubahan harus setelah tanggal terakhir ({$formattedDate}).")->with('active_tab', 'pills-rh-settings-tab');
        }

        // Auto generate label: Perubahan RH 3 Maret 2025
        $date = \Carbon\Carbon::parse($request->tanggal_perubahan);
        $label = 'Perubahan RH ' . $date->translatedFormat('j F Y');

        RhPerubahanHeader::create([
            'rh_tahun_id' => $rhTahun->id,
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

        $hasValues = $perubahan->details()
            ->where(function ($q) {
                $q->whereNotNull('min_edit')
                    ->orWhereNotNull('max_edit');
            })
            ->exists();

        if ($hasValues) {
            return back()->with('error', 'Header perubahan tidak bisa diedit karena sudah memiliki rincian nilai (min/max).')->with('active_tab', 'pills-rh-settings-tab');
        }

        // Prevent duplicate date in the same year
        $exists = RhPerubahanHeader::where('rh_tahun_id', $perubahan->rh_tahun_id)
            ->where('tanggal_perubahan', $request->tanggal_perubahan)
            ->where('id', '!=', $id)
            ->exists();

        if ($exists) {
            return back()->with('error', 'Tanggal perubahan sudah ada untuk tahun ini.')->with('active_tab', 'pills-rh-settings-tab');
        }

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

        $hasValues = $perubahan->details()
            ->where(function ($q) {
                $q->whereNotNull('min_edit')
                    ->orWhereNotNull('max_edit');
            })
            ->exists();

        if ($hasValues) {
            return back()->with('error', 'Header perubahan tidak bisa dihapus karena sudah memiliki rincian nilai (min/max).')->with('active_tab', 'pills-rh-settings-tab');
        }

        // Delete associated details (which are all nulls/empty at this point)
        $perubahan->details()->delete();
        $perubahan->delete();

        return back()->with('success', 'Header perubahan berhasil dihapus.')->with('active_tab', 'pills-rh-settings-tab');
    }
}
