<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MyTeamController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Ambil semua user dengan role = 'user' dan kabupaten_id yang sama dengan user yang sedang login dan sudah sampai tahap verifikasi no hp
        $teamMembers = User::where('role', 'user')
            ->where('kabupaten_id', $user->kabupaten_id)
            ->whereNotNull('no_hp_verified_at')
            ->orderBy('last_login_at', 'desc')
            ->where('status', 'active')
            ->get();

        $totalMembers = $teamMembers->count();
        $kabupatenName = $user->kabupaten->nama_kabupaten ?? 'Tidak Diketahui';

        return view('my-team.index', compact('teamMembers', 'totalMembers', 'kabupatenName'));
    }
}
