<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kecamatan;
use App\Models\Desa;
use App\Models\Kabupaten;
use Illuminate\Support\Facades\Auth;

class WilayahController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $isProvinsi = $user->role === 'admin' || (isset($user->kabupaten) && $user->kabupaten->kode_kab == '6100');

        $searchKecamatan = $request->input('search_kecamatan');
        $searchDesa = $request->input('search_desa');

        $kQuery = Kecamatan::with(['kabupaten', 'creator.kabupaten', 'updater.kabupaten']);
        $dQuery = Desa::with(['kecamatan.kabupaten', 'creator.kabupaten', 'updater.kabupaten']);

        if (!$isProvinsi) {
            $kQuery->where('kabupaten_id', $user->kabupaten_id);
            $dQuery->whereHas('kecamatan', function ($q) use ($user) {
                $q->where('kabupaten_id', $user->kabupaten_id);
            });
        }

        if ($searchKecamatan) {
            $kQuery->where(function ($q) use ($searchKecamatan) {
                $q->where('nama_kecamatan', 'like', "%{$searchKecamatan}%")
                    ->orWhere('kode_kecamatan', 'like', "%{$searchKecamatan}%");
            });
        }

        if ($searchDesa) {
            $dQuery->where(function ($q) use ($searchDesa) {
                $q->where('nama_desa', 'like', "%{$searchDesa}%")
                    ->orWhere('kode_desa', 'like', "%{$searchDesa}%");
            });
        }

        $kecamatans = $kQuery->paginate(10, ['*'], 'kecamatan_page');
        $desas = $dQuery->paginate(10, ['*'], 'desa_page');

        if ($isProvinsi) {
            $kabupatens = Kabupaten::where('kode_kab', '!=', '6100')->get();
        } else {
            $kabupatens = Kabupaten::where('id', $user->kabupaten_id)->where('kode_kab', '!=', '6100')->get();
        }

        return view('wilayah.index', compact('kecamatans', 'desas', 'kabupatens', 'user', 'isProvinsi', 'searchKecamatan', 'searchDesa'));
    }

    public function searchKecamatanAjax(Request $request)
    {
        $user = Auth::user();
        $isProvinsi = $user->role === 'admin' || (isset($user->kabupaten) && $user->kabupaten->kode_kab == '6100');

        $search = $request->input('q');

        $query = Kecamatan::with(['kabupaten']);

        if (!$isProvinsi) {
            $query->where('kabupaten_id', $user->kabupaten_id);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_kecamatan', 'like', "%{$search}%")
                    ->orWhere('kode_kecamatan', 'like', "%{$search}%");
            });
            $kecamatans = $query->limit(20)->get();
        } else {
            $kecamatans = $query->limit(4)->get();
        }

        $formatted = $kecamatans->map(function ($kec) use ($isProvinsi) {
            $text = "[$kec->kode_kecamatan] $kec->nama_kecamatan";
            if ($isProvinsi) {
                $text .= " ([" . ($kec->kabupaten->kode_kab ?? '') . "] " . ($kec->kabupaten->nama_kabupaten ?? '') . ")";
            }
            return ['id' => $kec->id, 'text' => $text];
        });

        return response()->json(['results' => $formatted]);
    }

    public function storeKecamatan(Request $request)
    {
        $user = Auth::user();
        $isProvinsi = $user->role === 'admin' || (isset($user->kabupaten) && $user->kabupaten->kode_kab == '6100');

        if (!$isProvinsi && $request->kabupaten_id != $user->kabupaten_id) {
            return redirect()->back()->with('error', 'Akses ditolak: Anda tidak dapat memanipulasi data untuk kabupaten lain.');
        }

        $request->validate([
            'kabupaten_id' => 'required',
            'kode_kecamatan' => 'required|unique:tb_kecamatan,kode_kecamatan',
            'nama_kecamatan' => 'required'
        ]);

        $kabupatenTarget = Kabupaten::find($request->kabupaten_id);
        if ($kabupatenTarget && $kabupatenTarget->kode_kab == '6100') {
            return redirect()->back()->with('error', 'Tidak dapat menambahkan kecamatan untuk level Provinsi (6100).');
        }

        Kecamatan::create([
            'kabupaten_id' => $request->kabupaten_id,
            'kode_kecamatan' => $request->kode_kecamatan,
            'nama_kecamatan' => $request->nama_kecamatan,
            'created_by' => Auth::id()
        ]);

        return redirect()->back()->with(['success' => 'Kecamatan berhasil ditambahkan', 'tab' => 'kecamatan']);
    }

    public function updateKecamatan(Request $request, $id)
    {
        $kecamatan = Kecamatan::findOrFail($id);
        $user = Auth::user();
        $isProvinsi = $user->role === 'admin' || (isset($user->kabupaten) && $user->kabupaten->kode_kab == '6100');

        if (!$isProvinsi && $kecamatan->kabupaten_id != $user->kabupaten_id) {
            return redirect()->back()->with('error', 'Akses ditolak: Anda tidak dapat mengubah data milik kabupaten lain.');
        }

        if (!$isProvinsi && $request->kabupaten_id != $user->kabupaten_id) {
            return redirect()->back()->with('error', 'Akses ditolak: Anda tidak dapat mengubah data ke kabupaten lain.');
        }

        $request->validate([
            'kabupaten_id' => 'required',
            'kode_kecamatan' => 'required|unique:tb_kecamatan,kode_kecamatan,' . $id,
            'nama_kecamatan' => 'required'
        ]);

        $kecamatan->update([
            'kabupaten_id' => $request->kabupaten_id,
            'kode_kecamatan' => $request->kode_kecamatan,
            'nama_kecamatan' => $request->nama_kecamatan,
            'updated_by' => Auth::id()
        ]);

        return redirect()->back()->with(['success' => 'Kecamatan berhasil diperbarui', 'tab' => 'kecamatan']);
    }

    public function destroyKecamatan($id)
    {
        $kecamatan = Kecamatan::findOrFail($id);
        $user = Auth::user();
        $isProvinsi = $user->role === 'admin' || (isset($user->kabupaten) && $user->kabupaten->kode_kab == '6100');

        if (!$isProvinsi && $kecamatan->kabupaten_id != $user->kabupaten_id) {
            return redirect()->back()->with('error', 'Akses ditolak: Anda tidak dapat menghapus data milik kabupaten lain.');
        }

        if ($kecamatan->desas()->count() > 0) {
            return redirect()->back()->with('error', 'Kecamatan tidak dapat dihapus karena masih memiliki desa. Hapus desa terlebih dahulu.');
        }
        $kecamatan->delete();
        return redirect()->back()->with(['success' => 'Kecamatan berhasil dihapus', 'tab' => 'kecamatan']);
    }

    public function storeDesa(Request $request)
    {
        $request->validate([
            'kecamatan_id' => 'required',
            'kode_desa' => 'required|unique:tb_desa,kode_desa',
            'nama_desa' => 'required'
        ]);

        $kecamatan = Kecamatan::findOrFail($request->kecamatan_id);
        $user = Auth::user();
        $isProvinsi = $user->role === 'admin' || (isset($user->kabupaten) && $user->kabupaten->kode_kab == '6100');

        if (!$isProvinsi && $kecamatan->kabupaten_id != $user->kabupaten_id) {
            return redirect()->back()->with('error', 'Akses ditolak: Anda tidak dapat memasukkan desa pada kecamatan milik kabupaten lain.');
        }

        Desa::create([
            'kecamatan_id' => $request->kecamatan_id,
            'kode_desa' => $request->kode_desa,
            'nama_desa' => $request->nama_desa,
            'created_by' => Auth::id()
        ]);

        return redirect()->back()->with(['success' => 'Desa berhasil ditambahkan', 'tab' => 'desa']);
    }

    public function updateDesa(Request $request, $id)
    {
        $desa = Desa::with('kecamatan')->findOrFail($id);
        $user = Auth::user();
        $isProvinsi = $user->role === 'admin' || (isset($user->kabupaten) && $user->kabupaten->kode_kab == '6100');

        if (!$isProvinsi && $desa->kecamatan->kabupaten_id != $user->kabupaten_id) {
            return redirect()->back()->with('error', 'Akses ditolak: Anda tidak dapat mengubah data desa milik kabupaten lain.');
        }

        $request->validate([
            'kecamatan_id' => 'required',
            'kode_desa' => 'required|unique:tb_desa,kode_desa,' . $id,
            'nama_desa' => 'required'
        ]);

        $kecamatanTarget = \App\Models\Kecamatan::findOrFail($request->kecamatan_id);
        if (!$isProvinsi && $kecamatanTarget->kabupaten_id != $user->kabupaten_id) {
            return redirect()->back()->with('error', 'Akses ditolak: Anda tidak dapat mengubah desa ke kecamatan milik kabupaten lain.');
        }

        $desa->update([
            'kecamatan_id' => $request->kecamatan_id,
            'kode_desa' => $request->kode_desa,
            'nama_desa' => $request->nama_desa,
            'updated_by' => Auth::id()
        ]);

        return redirect()->back()->with(['success' => 'Desa berhasil diperbarui', 'tab' => 'desa']);
    }

    public function destroyDesa($id)
    {
        $desa = Desa::with('kecamatan')->findOrFail($id);
        $user = Auth::user();
        $isProvinsi = $user->role === 'admin' || (isset($user->kabupaten) && $user->kabupaten->kode_kab == '6100');

        if (!$isProvinsi && $desa->kecamatan->kabupaten_id != $user->kabupaten_id) {
            return redirect()->back()->with('error', 'Akses ditolak: Anda tidak dapat menghapus data desa milik kabupaten lain.');
        }

        $desa->delete();
        return redirect()->back()->with(['success' => 'Desa berhasil dihapus', 'tab' => 'desa']);
    }
}
