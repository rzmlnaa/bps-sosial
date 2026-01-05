<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\KategoriKomoditas;
use Illuminate\Support\Facades\Auth;

class KategoriKomoditasController extends Controller
{
    public function index()
    {
        // This will be handled in a general input page for price range
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_kategori' => 'required|string|max:100|unique:tb_kategori_komoditas,nama_kategori',
        ]);

        KategoriKomoditas::create([
            'nama_kategori' => strtoupper($request->nama_kategori),
            'user_id_add' => Auth::id() ?? 1,
        ]);

        return redirect()->back()->with('success', 'Kategori Berhasil Ditambahkan');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_kategori' => 'required|string|max:100|unique:tb_kategori_komoditas,nama_kategori,' . $id,
        ]);

        $kategori = KategoriKomoditas::findOrFail($id);
        $kategori->update([
            'nama_kategori' => strtoupper($request->nama_kategori),
            'user_id_update' => Auth::id() ?? 1,
        ]);

        return redirect()->back()->with('success', 'Kategori Berhasil Diperbarui');
    }

    public function destroy($id)
    {
        $kategori = KategoriKomoditas::findOrFail($id);

        // Check if category has commodities
        if ($kategori->komoditas()->count() > 0) {
            return redirect()->back()->with('error', 'Kategori tidak dapat dihapus karena masih memiliki data komoditas. Silakan hapus semua komoditas dalam kategori ini terlebih dahulu.');
        }

        $kategori->delete();

        return redirect()->back()->with('success', 'Kategori Berhasil Dihapus');
    }
}
