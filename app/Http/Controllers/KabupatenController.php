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
            'kode_kab' => 'required|digits_between:1,10|unique:tb_kabupaten,kode_kab',
            'nama_kabupaten' => [
                'required',
                'string',
                'max:100',
                'unique:tb_kabupaten,nama_kabupaten',
                'regex:/^[A-Za-z\s]+$/'
            ],
        ], [
            'kode_kab.required' => 'Kode Kabupaten wajib diisi.',
            'kode_kab.digits_between' => 'Kode Kabupaten hanya boleh berisi angka.',
            'kode_kab.unique' => 'Kode Kabupaten sudah ada dalam data.',

            'nama_kabupaten.required' => 'Nama Kabupaten wajib diisi.',
            'nama_kabupaten.regex' => 'Nama Kabupaten hanya boleh berisi huruf.',
            'nama_kabupaten.unique' => 'Nama Kabupaten sudah ada dalam data.',
        ]);


        Kabupaten::create([
            'kode_kab' => $request->kode_kab,
            'nama_kabupaten' => $request->nama_kabupaten,
            'user_id_add' => Auth::id() ?? 1,
        ]);

        return redirect()->back()->with('success', 'Data Kabupaten berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'kode_kab' => 'required|string|max:10|unique:tb_kabupaten,kode_kab,' . $id,
            'nama_kabupaten' => 'required|string|max:100|unique:tb_kabupaten,nama_kabupaten,' . $id,
        ], [
            'kode_kab.required' => 'Kode Kabupaten wajib diisi.',
            'kode_kab.unique' => 'Kode Kabupaten sudah ada dalam data.',
            'nama_kabupaten.required' => 'Nama Kabupaten wajib diisi.',
            'nama_kabupaten.unique' => 'Nama Kabupaten sudah ada dalam data.',
        ]);

        $kabupaten = Kabupaten::findOrFail($id);
        $kabupaten->update([
            'kode_kab' => $request->kode_kab,
            'nama_kabupaten' => $request->nama_kabupaten,
            'user_id_update' => Auth::id() ?? 1,
        ]);

        return redirect()->back()->with('success', 'Data Kabupaten berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $kabupaten = Kabupaten::withCount('nilaiKemiskinan')->findOrFail($id);

        if ($kabupaten->nilai_kemiskinan_count > 0) {
            return redirect()->back()->with('error', 'Kabupaten tidak dapat dihapus karena sudah memiliki data nilai kemiskinan!');
        }

        $kabupaten->delete();

        return redirect()->back()->with('success', 'Data Kabupaten berhasil dihapus!');
    }
}
