<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JenisFenomena;
use Illuminate\Support\Facades\Auth;

class JenisFenomenaController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255|unique:jenis_fenomenas,nama',
        ], [
            'nama.required' => 'Nama jenis fenomena wajib diisi.',
            'nama.unique' => 'Nama jenis fenomena sudah ada.',
        ]);

        JenisFenomena::create([
            'nama' => $request->nama,
            'user_id_add' => Auth::id(),
            'user_id_update' => Auth::id(),
        ]);

        return redirect()->back()->with('success', 'Data Jenis Fenomena berhasil ditambahkan.')->with('active_tab', 'jenis');
    }

    public function update(Request $request, $id)
    {
        $jenis = JenisFenomena::findOrFail($id);

        $request->validate([
            'nama' => 'required|string|max:255|unique:jenis_fenomenas,nama,' . $jenis->id,
        ], [
            'nama.required' => 'Nama jenis fenomena wajib diisi.',
            'nama.unique' => 'Nama jenis fenomena sudah ada.',
        ]);

        $jenis->update([
            'nama' => $request->nama,
            'user_id_update' => Auth::id(),
        ]);

        return redirect()->back()->with('success', 'Data Jenis Fenomena berhasil diperbarui.')->with('active_tab', 'jenis');
    }

    public function destroy($id)
    {
        $jenis = JenisFenomena::findOrFail($id);
        $jenis->delete();

        return redirect()->back()->with('success', 'Data Jenis Fenomena berhasil dihapus.')->with('active_tab', 'jenis');
    }
}
