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

    public function users(Request $request)
    {
        $query = User::where('role', '!=', 'admin');

        // Filter by Name
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Filter by Kabupaten
        if ($request->filled('kabupaten_id')) {
            $query->where('kabupaten_id', $request->kabupaten_id);
        }

        // Filter by Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $users = $query->latest()->get();
        $kabupatens = \App\Models\Kabupaten::orderBy('kode_kab', 'asc')->get();

        return view('admin.users', compact('users', 'kabupatens'));
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

    public function makePending($id)
    {
        $user = User::findOrFail($id);
        $user->update(['status' => 'pending']);

        return redirect()->back()->with('success', 'Status akun diubah menjadi Pending.');
    }

    public function admins()
    {
        $admins = User::where('role', 'admin')->latest()->get();
        return view('admin.admins', compact('admins'));
    }

    public function storeAdmin(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'no_hp' => [
                'required',
                'numeric',
                'unique:users,no_hp',
                function ($attribute, $value, $fail) {
                    if (!str_starts_with($value, '62')) {
                        $fail('Nomor telepon harus diawali dengan 62.');
                    }
                },
            ],
            // Adding explicit password field or default could use 'nullable' + logic
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'no_hp' => $request->no_hp,
            'password' => bcrypt(str()->random(16)),
            'role' => 'admin',
            'status' => 'active',
            'team' => 'Admin Team',
        ]);

        return redirect()->back()->with('success', 'Admin baru berhasil ditambahkan.');
    }

    public function kabupatens()
    {
        $kabupatens = \App\Models\Kabupaten::with(['userAdd', 'userUpdate'])->orderBy('kode_kab', 'asc')->get();
        return view('admin.kabupaten', compact('kabupatens'));
    }
}
