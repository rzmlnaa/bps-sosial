<?php

namespace App\Http\Controllers;

use App\Models\Kabupaten;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KabupatenController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'nama_kabupaten' => 'required|string|max:100|unique:tb_kabupaten,nama_kabupaten',
        ], [
            'nama_kabupaten.required' => 'Nama Kabupaten wajib diisi.',
            'nama_kabupaten.unique' => 'Nama Kabupaten sudah ada dalam data.',
        ]);

        Kabupaten::create([
            'nama_kabupaten' => $request->nama_kabupaten,
            'user_id_add' => Auth::id() ?? 1,
        ]);

        return redirect()->back()->with('success', 'Data Kabupaten berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_kabupaten' => 'required|string|max:100|unique:tb_kabupaten,nama_kabupaten,' . $id,
        ], [
            'nama_kabupaten.required' => 'Nama Kabupaten wajib diisi.',
            'nama_kabupaten.unique' => 'Nama Kabupaten sudah ada dalam data.',
        ]);

        $kabupaten = Kabupaten::findOrFail($id);
        $kabupaten->update([
            'nama_kabupaten' => $request->nama_kabupaten,
            'user_id_update' => Auth::id() ?? 1,
        ]);

        return redirect()->back()->with('success', 'Data Kabupaten berhasil diperbarui!');
    }
}
