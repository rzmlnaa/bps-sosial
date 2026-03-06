<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SumberBerita;
use Illuminate\Support\Facades\Auth;

class SumberBeritaController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255|unique:sumber_beritas,nama',
        ], [
            'nama.required' => 'Nama sumber berita wajib diisi.',
            'nama.unique' => 'Nama sumber berita sudah ada.',
        ]);

        SumberBerita::create([
            'nama' => $request->nama,
            'is_online' => $request->has('is_online'),
            'user_id_add' => Auth::id(),
            'user_id_update' => Auth::id(),
        ]);

        return redirect()->back()->with('success', 'Data Sumber Berita berhasil ditambahkan.')->with('active_tab', 'sumber');
    }

    public function update(Request $request, $id)
    {
        $sumber = SumberBerita::findOrFail($id);

        $request->validate([
            'nama' => 'required|string|max:255|unique:sumber_beritas,nama,' . $sumber->id,
        ], [
            'nama.required' => 'Nama sumber berita wajib diisi.',
            'nama.unique' => 'Nama sumber berita sudah ada.',
        ]);

        $sumber->update([
            'nama' => $request->nama,
            'is_online' => $request->has('is_online'),
            'user_id_update' => Auth::id(),
        ]);

        return redirect()->back()->with('success', 'Data Sumber Berita berhasil diperbarui.')->with('active_tab', 'sumber');
    }

    public function destroy($id)
    {
        $sumber = SumberBerita::findOrFail($id);

        if ($sumber->fenomenas()->exists()) {
            return redirect()->back()
                ->with('error', 'Tidak dapat menghapus Sumber Berita ini karena sudah digunakan dalam data fenomena.')
                ->with('active_tab', 'sumber');
        }

        $sumber->delete();

        return redirect()->back()->with('success', 'Data Sumber Berita berhasil dihapus.')->with('active_tab', 'sumber');
    }
}
