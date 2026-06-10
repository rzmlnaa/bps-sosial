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

        $search = $request->input('search');
        $filterKabupaten = $request->input('kabupaten_id');

        $query = Kecamatan::with([
            'kabupaten',
            'creator',
            'updater',
            'desas' => function ($q) {
                $q->with(['creator', 'updater'])
                    ->orderByRaw('LENGTH(kode_desa) ASC')
                    ->orderBy('kode_desa', 'asc');
            }
        ])
            ->select('tb_kecamatan.*')
            ->join('tb_kabupaten', 'tb_kecamatan.kabupaten_id', '=', 'tb_kabupaten.id')
            ->orderByRaw('LENGTH(tb_kabupaten.kode_kab) ASC')
            ->orderBy('tb_kabupaten.kode_kab', 'asc')
            ->orderByRaw('LENGTH(tb_kecamatan.kode_kecamatan) ASC')
            ->orderBy('tb_kecamatan.kode_kecamatan', 'asc');

        if (!$isProvinsi) {
            $query->where('tb_kecamatan.kabupaten_id', $user->kabupaten_id);
        } elseif ($filterKabupaten) {
            $query->where('tb_kecamatan.kabupaten_id', $filterKabupaten);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_kecamatan', 'like', "%{$search}%")
                    ->orWhere('kode_kecamatan', 'like', "%{$search}%")
                    ->orWhereHas('desas', function ($sq) use ($search) {
                        $sq->where('nama_desa', 'like', "%{$search}%")
                            ->orWhere('kode_desa', 'like', "%{$search}%");
                    });
            });
        }

        $kecamatans = $query->paginate(10)->withQueryString();

        if ($isProvinsi) {
            $kabupatens = Kabupaten::withoutIndonesia()->where('kode_kab', '!=', '6100')->orderBy('kode_kab', 'asc')->get();
        } else {
            $kabupatens = Kabupaten::withoutIndonesia()->where('id', $user->kabupaten_id)->where('kode_kab', '!=', '6100')->get();
        }

        // Hitung total untuk statistik
        $totalKecamatan = Kecamatan::when(!$isProvinsi, function ($q) use ($user) {
            return $q->where('kabupaten_id', $user->kabupaten_id);
        })->when($isProvinsi && $filterKabupaten, function ($q) use ($filterKabupaten) {
            return $q->where('kabupaten_id', $filterKabupaten);
        })->count();

        $totalDesa = Desa::when(!$isProvinsi, function ($q) use ($user) {
            return $q->whereHas('kecamatan', function ($sq) use ($user) {
                $sq->where('kabupaten_id', $user->kabupaten_id);
            });
        })->when($isProvinsi && $filterKabupaten, function ($q) use ($filterKabupaten) {
            return $q->whereHas('kecamatan', function ($sq) use ($filterKabupaten) {
                $sq->where('kabupaten_id', $filterKabupaten);
            });
        })->count();

        return view('wilayah.index', compact('kecamatans', 'kabupatens', 'user', 'isProvinsi', 'search', 'filterKabupaten', 'totalKecamatan', 'totalDesa'));
    }

    public function searchKecamatanAjax(Request $request)
    {
        $user = Auth::user();
        $isProvinsi = $user->role === 'admin' || (isset($user->kabupaten) && $user->kabupaten->kode_kab == '6100');

        $search = $request->input('q');

        $query = Kecamatan::with(['kabupaten']);

        if (!$isProvinsi) {
            $query->where('kabupaten_id', $user->kabupaten_id);
        } elseif ($request->filled('kabupaten_id')) {
            $query->where('kabupaten_id', $request->input('kabupaten_id'));
        }

        $query->orderByRaw('LENGTH(kode_kecamatan) ASC')
            ->orderBy('kode_kecamatan', 'asc');

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

    public function store(Request $request)
    {
        $user = Auth::user();
        $isProvinsi = $user->role === 'admin' || (isset($user->kabupaten) && $user->kabupaten->kode_kab == '6100');

        $request->validate([
            'kabupaten_id' => 'required|exists:tb_kabupaten,id',
            'is_new_kecamatan' => 'nullable|string',
            'kecamatan_id' => 'required_without:is_new_kecamatan|nullable',
            'new_kode_kecamatan' => 'required_if:is_new_kecamatan,1|nullable',
            'new_nama_kecamatan' => 'required_if:is_new_kecamatan,1|nullable',
            'kode_desa' => 'required',
            'nama_desa' => 'required',
        ]);

        if ($request->is_new_kecamatan == '1') {
            $existsKecamatan = Kecamatan::where('kabupaten_id', $request->kabupaten_id)
                ->where('kode_kecamatan', $request->new_kode_kecamatan)
                ->exists();
            if ($existsKecamatan) {
                return back()->withInput()->withErrors(['new_kode_kecamatan' => 'Kode Kecamatan sudah digunakan di kabupaten ini.']);
            }
        } else {
            $existsDesa = Desa::where('kecamatan_id', $request->kecamatan_id)
                ->where('kode_desa', $request->kode_desa)
                ->exists();
            if ($existsDesa) {
                return back()->withInput()->withErrors(['kode_desa' => 'Kode Desa sudah digunakan di kecamatan ini.']);
            }
        }

        if (!$isProvinsi && $request->kabupaten_id != $user->kabupaten_id) {
            return back()->with('error', 'Akses ditolak: Anda tidak dapat memanipulasi data untuk kabupaten lain.');
        }

        $kabupatenTarget = Kabupaten::withoutIndonesia()->find($request->kabupaten_id);
        if ($kabupatenTarget && $kabupatenTarget->kode_kab == '6100') {
            return back()->with('error', 'Tidak dapat menambahkan wilayah untuk level Provinsi.');
        }

        try {
            \DB::beginTransaction();

            $kecamatanId = $request->kecamatan_id;

            // Jika kecamatan baru
            if ($request->is_new_kecamatan == '1') {
                $kecamatan = Kecamatan::create([
                    'kabupaten_id' => $request->kabupaten_id,
                    'kode_kecamatan' => $request->new_kode_kecamatan,
                    'nama_kecamatan' => $request->new_nama_kecamatan,
                    'created_by' => Auth::id()
                ]);
                $kecamatanId = $kecamatan->id;
            }

            // Tambah Desa
            Desa::create([
                'kecamatan_id' => $kecamatanId,
                'kode_desa' => $request->kode_desa,
                'nama_desa' => $request->nama_desa,
                'created_by' => Auth::id()
            ]);

            \DB::commit();
            return redirect()->back()->with('success', 'Data wilayah berhasil ditambahkan.');
        } catch (\Exception $e) {
            \DB::rollBack();
            return back()->with('error', 'Gagal menambahkan data: ' . $e->getMessage());
        }
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
            'kode_kecamatan' => 'required',
            'nama_kecamatan' => 'required'
        ]);

        $existsKecamatan = Kecamatan::where('kabupaten_id', $request->kabupaten_id)
            ->where('kode_kecamatan', $request->kode_kecamatan)
            ->exists();
        if ($existsKecamatan) {
            return back()->withInput()->withErrors(['kode_kecamatan' => 'Kode Kecamatan sudah digunakan di kabupaten ini.']);
        }

        $kabupatenTarget = Kabupaten::withoutIndonesia()->find($request->kabupaten_id);
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
            'kode_kecamatan' => 'required',
            'nama_kecamatan' => 'required'
        ]);

        $existsKecamatan = Kecamatan::where('kabupaten_id', $request->kabupaten_id)
            ->where('kode_kecamatan', $request->kode_kecamatan)
            ->where('id', '!=', $id)
            ->exists();
        if ($existsKecamatan) {
            return back()->withInput()->withErrors(['kode_kecamatan' => 'Kode Kecamatan sudah digunakan di kabupaten ini.']);
        }

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
            'kode_desa' => 'required',
            'nama_desa' => 'required'
        ]);

        $kecamatan = Kecamatan::findOrFail($request->kecamatan_id);
        $user = Auth::user();
        $isProvinsi = $user->role === 'admin' || (isset($user->kabupaten) && $user->kabupaten->kode_kab == '6100');

        if (!$isProvinsi && $kecamatan->kabupaten_id != $user->kabupaten_id) {
            return redirect()->back()->with('error', 'Akses ditolak: Anda tidak dapat memasukkan desa pada kecamatan milik kabupaten lain.');
        }

        $existsDesa = Desa::where('kecamatan_id', $request->kecamatan_id)
            ->where('kode_desa', $request->kode_desa)
            ->exists();
        if ($existsDesa) {
            return back()->withInput()->withErrors(['kode_desa' => 'Kode Desa sudah digunakan di kecamatan ini.']);
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
            'kode_desa' => 'required',
            'nama_desa' => 'required'
        ]);

        $kecamatanTarget = \App\Models\Kecamatan::findOrFail($request->kecamatan_id);
        if (!$isProvinsi && $kecamatanTarget->kabupaten_id != $user->kabupaten_id) {
            return redirect()->back()->with('error', 'Akses ditolak: Anda tidak dapat mengubah desa ke kecamatan milik kabupaten lain.');
        }

        $existsDesa = Desa::where('kecamatan_id', $request->kecamatan_id)
            ->where('kode_desa', $request->kode_desa)
            ->where('id', '!=', $id)
            ->exists();
        if ($existsDesa) {
            return back()->withInput()->withErrors(['kode_desa' => 'Kode Desa sudah digunakan di kecamatan ini.']);
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

        $peserta = \App\Models\DescanPeserta::where('desa_id', $id)->first();
        if ($peserta) {
            $hasProgress = \App\Models\DescanProgressDesa::where('peserta_id', $peserta->id)->exists();
            if ($hasProgress) {
                return redirect()->back()->with('error', 'Desa tidak dapat dihapus karena sudah memiliki data progress Desa Cantik.');
            }
        }

        $desa->delete();
        return redirect()->back()->with(['success' => 'Desa berhasil dihapus', 'tab' => 'desa']);
    }
}
