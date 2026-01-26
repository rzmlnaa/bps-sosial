<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Kabupaten;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ProfileCompletionController extends Controller
{
    public function show()
    {
        $user = Auth::user();

        // // If profile is already complete, redirect to dashboard
        // if (!empty($user->no_hp) && !empty($user->kabupaten_id)) {
        //     return redirect('/dashboard');
        // }
        if ($user->status === 'active') {
            return redirect('/dashboard');
        }
        $kabupatens = Kabupaten::orderBy('nama_kabupaten', 'asc')->get();
        return view('auth.complete-profile', compact('user', 'kabupatens'));
    }

    public function update(Request $request)
    {
        $rawNoHp = $request->no_hp;

        // Hapus semua selain angka
        $noHp = preg_replace('/[^0-9]/', '', $rawNoHp);

        // Jika diawali 0 → ganti 62
        if (str_starts_with($noHp, '0')) {
            $noHp = '62' . substr($noHp, 1);
        }
        $request->merge([
            'no_hp' => $noHp
        ]);
        $request->validate([
            'no_hp' => [
                'required',
                'numeric',
                Rule::unique('users', 'no_hp')->ignore(auth()->id()),
            ],
            'kabupaten_id' => 'required|exists:tb_kabupaten,id',
            'team' => 'required|string',
        ], [
            'no_hp.required' => 'Nomor WhatsApp wajib diisi.',
            'no_hp.numeric' => 'Nomor WhatsApp hanya boleh berisi angka.',
            'no_hp.unique' => 'Nomor WhatsApp sudah digunakan oleh pengguna lain.',
            'kabupaten_id.required' => 'Silakan pilih Kabupaten/Kota.',
            'team.required' => 'Silakan pilih Unit Kerja.',
        ]);

        $user = Auth::user();

        /** @var \App\Models\User $user */

        $user->update([
            'no_hp' => $request->no_hp,
            'kabupaten_id' => $request->kabupaten_id,
            'team' => $request->team,
            'status' => 'pending',
            'role' => 'user'
        ]);

        return redirect('/complete-profile')->with('success', 'Profil berhasil disimpan. Harap tunggu verifikasi admin.');
    }
}
