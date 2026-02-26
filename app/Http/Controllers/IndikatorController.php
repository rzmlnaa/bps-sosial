<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Indikator;
use Illuminate\Support\Facades\Auth;

class IndikatorController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'kode' => ['required', 'string', 'regex:/^[0-9]+$/', 'unique:indikators,kode'],
            'nama' => 'required|string|max:255',
            'kelompok' => 'required|in:utama,dampak',
        ], [
            'kode.regex' => 'Kode indikator hanya boleh berisi angka (contoh: 01, 02).',
            'kode.unique' => 'Kode indikator sudah ada di database.',
            'kelompok.in' => 'Kelompok indikator tidak valid.',
        ]);

        Indikator::create([
            'kode' => $request->kode,
            'nama' => $request->nama,
            'kelompok' => $request->kelompok,
            'is_active' => $request->has('is_active'),
            'user_id_add' => Auth::id(),
            'user_id_update' => Auth::id(),
        ]);

        return redirect()->back()->with('success', 'Data Kode Indikator berhasil ditambahkan.')->with('active_tab', 'indikator');
    }

    public function update(Request $request, $id)
    {
        $indikator = Indikator::findOrFail($id);

        $request->validate([
            'kode' => ['required', 'string', 'regex:/^[0-9]+$/', 'unique:indikators,kode,' . $indikator->id],
            'nama' => 'required|string|max:255',
            'kelompok' => 'required|in:utama,dampak',
        ], [
            'kode.regex' => 'Kode indikator hanya boleh berisi angka (contoh: 01, 02).',
            'kode.unique' => 'Kode indikator sudah ada di database.',
            'kelompok.in' => 'Kelompok indikator tidak valid.',
        ]);

        $indikator->update([
            'kode' => $request->kode,
            'nama' => $request->nama,
            'kelompok' => $request->kelompok,
            'is_active' => $request->has('is_active'),
            'user_id_update' => Auth::id(),
        ]);

        return redirect()->back()->with('success', 'Data Kode Indikator berhasil diperbarui.')->with('active_tab', 'indikator');
    }

    public function toggleActive($id)
    {
        $indikator = Indikator::findOrFail($id);
        $indikator->is_active = !$indikator->is_active;
        $indikator->user_id_update = Auth::id();
        $indikator->save();

        return response()->json([
            'success' => true,
            'message' => 'Status Indikator berhasil diubah.',
            'is_active' => $indikator->is_active
        ]);
    }

    public function destroy($id)
    {
        $indikator = Indikator::findOrFail($id);
        $indikator->delete();

        return redirect()->back()->with('success', 'Data Kode Indikator berhasil dihapus.')->with('active_tab', 'indikator');
    }
}
