<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Kabupaten;
use App\Models\DynamicMenu;

class AdminController extends Controller
{
    public function index()
    {
        $stats = [
            'users_count' => User::where('role', '!=', 'admin')->count(),
            'admins_count' => User::where('role', 'admin')->count(),
            'kabupatens_count' => Kabupaten::withoutIndonesia()->count(),
            'menus_count' => DynamicMenu::where('is_active', true)
                ->whereNotIn('type', ['logo', 'panduan_pengguna', 'video_panduan'])
                ->whereNotIn('id', function ($query) {
                    $query->select('parent_id')
                        ->from('dynamic_menus')
                        ->whereNotNull('parent_id');
                })
                ->where(function ($query) {
                    $query->where(function ($q) {
                        $q->whereNotNull('url')->where('url', '!=', '');
                    })
                        ->orWhere(function ($q) {
                            $q->whereNotNull('embed_url')->where('embed_url', '!=', '');
                        })
                        ->orWhere(function ($q) {
                            $q->whereJsonLength('meta', '>', 0);
                        });
                })->count(),
        ];

        // Eager loading to avoid N+1
        $latestUsers = User::with('kabupaten')
            ->where('role', '!=', 'admin')
            ->latest()
            ->take(5)
            ->get();

        $latestAdmins = User::where('role', 'admin')
            ->latest()
            ->take(5)
            ->get();

        $kabupatens = Kabupaten::withoutIndonesia()->with(['userAdd', 'userUpdate'])
            ->orderBy('kode_kab', 'asc')
            ->take(10)
            ->get();

        $latestMenus = DynamicMenu::with(['parent', 'creator'])
            ->latest()
            ->take(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'latestUsers', 'latestAdmins', 'kabupatens', 'latestMenus'));
    }

    public function users(Request $request)
    {
        $query = User::where('role', '!=', 'admin')->where('kabupaten_id', '!=', null);

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

        $users = $query->latest()->paginate(10)->withQueryString();

        $kabupatens = \App\Models\Kabupaten::withoutIndonesia()->orderBy('kode_kab', 'asc')->get();
        $userTidakFinalPofile = User::where('role', '!=', 'admin')->where('kabupaten_id', null)->latest()->get();



        return view('admin.users', compact('users', 'kabupatens', 'userTidakFinalPofile'));
    }

    public function approve($id)
    {

        $user = User::findOrFail($id);
        if ($user->otp_code != null) {
            return redirect()->back()->with('error', 'Akun pengguna belum melakukan verifikasi nomor WhatsApp.');
        }
        if ($user->kabupaten_id == null) {
            return redirect()->back()->with('error', 'Akun pengguna tidak memiliki kabupaten.');
        }
        $user->update(['status' => 'active']);

        return redirect()->back()->with('success', 'Akun pengguna berhasil disetujui.');
    }

    public function reject($id)
    {
        $user = User::findOrFail($id);
        if ($user->otp_code != null) {
            return redirect()->back()->with('error', 'Akun pengguna belum melakukan verifikasi nomor WhatsApp.');
        }
        if ($user->kabupaten_id == null) {
            return redirect()->back()->with('error', 'Akun pengguna tidak memiliki kabupaten.');
        }
        $user->update([
            'status' => 'rejected',
            'no_hp_verified_at' => null,
        ]);

        return redirect()->back()->with('success', 'Akun pengguna berhasil ditolak.');
    }

    public function makePending($id)
    {
        $user = User::findOrFail($id);
        $user->update(['status' => 'pending']);

        return redirect()->back()->with('success', 'Status akun diubah menjadi Pending.');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);

        $hasDependencies = \App\Models\RhTahun::where('user_id_add', $id)->exists()
            || \App\Models\RhPerubahanDetail::where('user_id_add', $id)->orWhere('verified_by', $id)->exists()
            || \App\Models\Kabupaten::where('user_id_add', $id)->orWhere('user_id_update', $id)->exists()
            || \App\Models\KategoriKomoditas::where('user_id_add', $id)->orWhere('user_id_update', $id)->exists()
            || \App\Models\VariabelKemiskinan::where('user_id_add', $id)->exists()
            || \App\Models\RhPerubahanHeader::where('user_id_add', $id)->exists()
            || \App\Models\Komoditas::where('user_id_add', $id)->orWhere('user_id_update', $id)->exists();


        if ($hasDependencies) {
            return redirect()->back()->with('error', 'Gagal menghapus! User ini masih digunakan di referensi data lain.');
        }

        $user->delete();

        return redirect()->back()->with('success', 'User berhasil dihapus.');
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
        $kabupatens = \App\Models\Kabupaten::with(['userAdd', 'userUpdate'])
            ->orderByRaw('LENGTH(kode_kab) ASC')
            ->orderBy('kode_kab', 'asc')
            ->get();
        return view('admin.kabupaten', compact('kabupatens'));
    }
}
