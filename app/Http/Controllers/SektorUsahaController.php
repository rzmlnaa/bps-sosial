<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SektorUsaha;
use Illuminate\Support\Facades\Auth;

class SektorUsahaController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'kode' => ['required', 'string', 'regex:/^[A-Z]+$/', 'unique:sektor_usahas,kode'],
            'nama' => 'required|string|max:255',
        ], [
            'kode.regex' => 'Kode lapangan usaha hanya boleh mengandung huruf A-Z (huruf besar).',
            'kode.unique' => 'Kode lapangan usaha sudah ada di database.',
        ]);

        SektorUsaha::create([
            'kode' => strtoupper($request->kode),
            'nama' => $request->nama,
            'user_id_add' => Auth::id(),
            'user_id_update' => Auth::id(),
        ]);

        return redirect()->back()->with('success', 'Data Kode Lapangan Usaha berhasil ditambahkan.')->with('active_tab', 'lap-usaha');
    }

    public function update(Request $request, $id)
    {
        $sektorUsaha = SektorUsaha::findOrFail($id);

        $request->validate([
            'kode' => ['required', 'string', 'regex:/^[A-Z]+$/', 'unique:sektor_usahas,kode,' . $sektorUsaha->id],
            'nama' => 'required|string|max:255',
        ], [
            'kode.regex' => 'Kode lapangan usaha hanya boleh mengandung huruf A-Z (huruf besar).',
            'kode.unique' => 'Kode lapangan usaha sudah ada di database.',
        ]);

        $sektorUsaha->update([
            'kode' => strtoupper($request->kode),
            'nama' => $request->nama,
            'user_id_update' => Auth::id(),
        ]);

        return redirect()->back()->with('success', 'Data Kode Lapangan Usaha berhasil diperbarui.')->with('active_tab', 'lap-usaha');
    }

    public function destroy($id)
    {
        $sektorUsaha = SektorUsaha::findOrFail($id);
        $sektorUsaha->delete();

        return redirect()->back()->with('success', 'Data Kode Lapangan Usaha berhasil dihapus.')->with('active_tab', 'lap-usaha');
    }
}
