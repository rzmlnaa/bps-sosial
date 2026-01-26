<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class AdminController extends Controller
{
    public function index()
    {
        return view('admin.dashboard');
    }

    public function verification()
    {
        $pendingUsers = User::where('status', 'pending')->where('role', '!=', 'admin')->get();
        return view('admin.verification', compact('pendingUsers'));
    }

    public function approve($id)
    {
        $user = User::findOrFail($id);
        $user->update(['status' => 'active']);

        return redirect()->back()->with('success', 'Akun pengguna berhasil disetujui.');
    }

    public function reject($id)
    {
        $user = User::findOrFail($id);
        $user->update(['status' => 'rejected']);

        return redirect()->back()->with('success', 'Akun pengguna berhasil ditolak.');
    }
}
