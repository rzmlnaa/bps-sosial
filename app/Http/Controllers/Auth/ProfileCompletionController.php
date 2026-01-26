<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Kabupaten;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileCompletionController extends Controller
{
    public function show()
    {
        $user = Auth::user();

        // If profile is already complete, redirect to dashboard
        if (!empty($user->no_hp) && !empty($user->kabupaten_id)) {
            return redirect('/dashboard');
        }

        $kabupatens = Kabupaten::orderBy('nama_kabupaten', 'asc')->get();
        return view('auth.complete-profile', compact('user', 'kabupatens'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'no_hp' => 'required|string|max:20',
            'kabupaten_id' => 'required|exists:tb_kabupaten,id',
        ]);

        $user = Auth::user();

        // Explicitly assert $user is a User model instance to satisfy static analysis if needed
        /** @var \App\Models\User $user */

        $user->update([
            'no_hp' => $request->no_hp,
            'kabupaten_id' => $request->kabupaten_id,
            // Status remains pending until approved by admin, or we can set it here if auto-approval logic existed
            // 'status' => 'pending' // It should already be pending from creation or default
        ]);

        return redirect('/dashboard')->with('success', 'Profile updated. Please wait for admin approval if required.');
    }
}
