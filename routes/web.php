<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\KabupatenController;
use App\Models\Kabupaten;
use App\Models\VariabelKemiskinan;
use App\Http\Controllers\VariabelController;
use App\Http\Controllers\PovertyDataController;
use Illuminate\Http\Request;
use App\Models\NilaiKemiskinan;
use App\Http\Controllers\KategoriKomoditasController;
use App\Http\Controllers\KomoditasController;
use App\Models\KategoriKomoditas;
use App\Http\Controllers\RhNilaiController;
use App\Http\Controllers\RhTahunController;
use App\Http\Controllers\PriceRangeController;
use App\Http\Controllers\VerificationController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ProfileCompletionController;
use Illuminate\Support\Facades\Auth;


// --- Authentication Routes (Public/Guest) ---

Route::get('/login', function () {
    if (Auth::check()) {
        return redirect('/dashboard');
    }
    return view('auth.login');
})->name('login');

Route::get('auth/google', [LoginController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('auth/google/callback', [LoginController::class, 'handleGoogleCallback']);

Route::middleware(['auth'])->group(function () {
    // Admin Routes
    Route::middleware(['can:access-admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\AdminController::class, 'index'])->name('dashboard');
        Route::get('/users', [\App\Http\Controllers\AdminController::class, 'users'])->name('users');
        Route::post('/approve/{id}', [\App\Http\Controllers\AdminController::class, 'approve'])->name('approve');
        Route::post('/reject/{id}', [\App\Http\Controllers\AdminController::class, 'reject'])->name('reject');
        Route::post('/make-pending/{id}', [\App\Http\Controllers\AdminController::class, 'makePending'])->name('make-pending');

        // Admin Management
        Route::get('/admins', [\App\Http\Controllers\AdminController::class, 'admins'])->name('admins');
        Route::post('/admins', [\App\Http\Controllers\AdminController::class, 'storeAdmin'])->name('admins.store');

        // Master Wilayah
        Route::get('/kabupaten', [\App\Http\Controllers\AdminController::class, 'kabupatens'])->name('kabupatens');
        Route::post('/kabupaten', [KabupatenController::class, 'store'])->name('kabupaten.store');
        Route::put('/kabupaten/{id}', [KabupatenController::class, 'update'])->name('kabupaten.update');
        Route::delete('/kabupaten/{id}', [KabupatenController::class, 'destroy'])->name('kabupaten.destroy');
    });

    Route::post('/logout', function () {
        Auth::logout();
        return redirect('/dashboard');
    })->name('logout');
});


// --- Profile Completion (Auth Required, Self-Allowed by Middleware) ---

Route::middleware(['auth', 'check.status'])->group(function () {
    Route::get('/complete-profile', [ProfileCompletionController::class, 'show'])->name('complete-profile');
    Route::post('/complete-profile', [ProfileCompletionController::class, 'update'])->name('complete-profile.update');
});


// --- Application Routes (Applied CheckUserStatus) ---
// These routes will check if a logged-in user is 'pending' and redirect them if so.
// Guests (not logged in) will bypass the check and can access public pages if meant to be public.

Route::middleware(['check.status'])->group(function () {

    Route::get('/', function () {
        return redirect('/dashboard');
    });

    Route::get('/dashboard', function () {
        return view('dashboard.index');
    })->name('dashboard');

    Route::get('/layouts', function () {
        return view('layouts.admin');
    })->name('layouts');

    Route::get('/poverty', function (Request $request) {

        $kabupatens = Kabupaten::orderBy('kode_kab', 'asc')->get();
        $variabels = VariabelKemiskinan::all();
        $availableYears = VariabelKemiskinan::distinct()->orderBy('tahun', 'desc')->pluck('tahun');
        $latestYear = VariabelKemiskinan::max('tahun') ?? date('Y');
        if (!$request->has('tahun')) {
            $request->merge(['tahun' => $latestYear]);
        }
        $selectedTahun = $request->get('tahun');

        if ($selectedTahun == 'all' || $selectedTahun == null) {
            $mainVar = $variabels->first();
        } else {
            $mainVar = VariabelKemiskinan::where('tahun', $selectedTahun)->latest()->first();
        }

        $kabupatenData = [];
        foreach ($kabupatens as $kab) {
            $ceknilai = \App\Models\NilaiKemiskinan::where('kabupaten_id', $kab->id)->first();
            $kabupatenData[] = [
                'id' => $kab->id,
                'kode_kab' => $kab->kode_kab,
                'name' => $kab->nama_kabupaten,
                'nilai' => $ceknilai
            ];
        }
        $kabupatenData = array_filter($kabupatenData, function ($item) {
            return !is_null($item['nilai']);
        });

        $provAvg = count($kabupatenData) > 0 ? (array_sum(array_column($kabupatenData, 'avg_nilai')) / count($kabupatenData)) : 0;
        $provCount = array_sum(array_column($kabupatenData, 'count'));
        $provGK = count($kabupatenData) > 0 ? (array_sum(array_column($kabupatenData, 'gk')) / count($kabupatenData)) : 0;

        $bulanNama = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember'
        ];
        $latestLabel = $mainVar ? (($mainVar->bulan ? $bulanNama[$mainVar->bulan] . ' ' : '') . ($mainVar->tahun ?? '')) : '';


        return view('poverty.index', compact('kabupatens', 'variabels', 'kabupatenData', 'mainVar', 'provAvg', 'provCount', 'provGK', 'latestLabel', 'selectedTahun', 'availableYears'));
    })->name('poverty');

    Route::get('/poverty/input', function () {
        // Strict Access: Only Province User (6100)
        if (auth()->check() == false) {
            return redirect('/poverty')->with('error', 'Silahkan login terlebih dahulu.');
        }
        $user = Auth::user();
        if (!$user->kabupaten || $user->kabupaten->kode_kab != '6100') {
            return redirect()->back()->with('error', 'Akses Ditolak: Hanya BPS Provinsi (6100) yang dapat mengakses halaman ini.');
        }

        $kabupatens = Kabupaten::with(['userAdd', 'userUpdate'])->orderBy('kode_kab', 'asc')->get();
        $variabels = VariabelKemiskinan::with('userAdd')->get();
        return view('poverty.input', compact('kabupatens', 'variabels'));
    })->name('poverty.input');

    Route::post('/variabel', [VariabelController::class, 'store'])->name('variabel.store');
    Route::delete('/variabel/{id}', [VariabelController::class, 'destroy'])->name('variabel.destroy');

    Route::post('/poverty-data', [PovertyDataController::class, 'store'])->name('poverty-data.store');
    Route::delete('/poverty-data/clear', [PovertyDataController::class, 'clearData'])->name('poverty-data.clear');
    Route::get('/poverty-data/get-data/{kabupaten_id}', [PovertyDataController::class, 'getData']);
    Route::get('/poverty-data/get-raw/{kabupaten_id}/{variabel_id}', [PovertyDataController::class, 'getRawData']);
    Route::get('/poverty-data/export/{kabupaten_id}', [PovertyDataController::class, 'exportToCSV'])->name('poverty-data.export');

    Route::get('/price-range', [PriceRangeController::class, 'index'])->name('price-range.index');
    Route::get('/price-range/export', [PriceRangeController::class, 'export'])->name('price-range.export');

    Route::get('/price-range/input', function () {
        if (!auth()->check()) {
            return redirect()->back()->with('error', 'Anda harus login terlebih dahulu.');
        }
        $user = auth()->user();
        if (!$user->kabupaten || $user->kabupaten->kode_kab != '6100') {
            return redirect()->back()->with('error', 'Akses Ditolak: Hanya BPS Provinsi (6100) yang dapat menginput data.');
        }
        $kategori = KategoriKomoditas::with(['userAdd', 'userUpdate'])->withCount('komoditas')->get();
        $rhTahun = \App\Models\RhTahun::with([
            'perubahanHeaders' => function ($query) {
                $query->orderBy('tanggal_perubahan', 'asc')->withCount([
                    'details as details_with_values_count' => function ($q) {
                        $q->where(function ($sub) {
                            $sub->whereNotNull('min_edit')->orWhereNotNull('max_edit');
                        });
                    }
                ]);
            },
            'perubahanHeaders.userAdd',
            'userAdd'
        ])->withCount([
                    'perubahanDetails as perubahan_details_with_values_count' => function ($q) {
                        $q->where(function ($sub) {
                            $sub->whereNotNull('min_edit')->orWhereNotNull('max_edit');
                        });
                    }
                ])->orderBy('tahun', 'desc')->get();
        return view('price-range/input', compact('kategori', 'rhTahun'));
    })->name('price-range.input');

    Route::post('/kategori-komoditas', [KategoriKomoditasController::class, 'store'])->name('kategori-komoditas.store');
    Route::put('/kategori-komoditas/{id}', [KategoriKomoditasController::class, 'update'])->name('kategori-komoditas.update');
    Route::delete('/kategori-komoditas/{id}', [KategoriKomoditasController::class, 'destroy'])->name('kategori-komoditas.destroy');

    Route::post('/komoditas', [KomoditasController::class, 'store'])->name('komoditas.store');
    Route::put('/komoditas/{id}', [KomoditasController::class, 'update'])->name('komoditas.update');
    Route::delete('/komoditas/clear', [KomoditasController::class, 'clearData'])->name('komoditas.clear');
    Route::delete('/komoditas/{id}', [KomoditasController::class, 'destroy'])->name('komoditas.destroy');
    Route::get('/komoditas/get-by-category/{kategori_id}', [KomoditasController::class, 'getByCategory']);

    // RH Year & Revision Management
    Route::post('/rh-tahun', [RhTahunController::class, 'storeTahun'])->name('rh-tahun.store');
    Route::patch('/rh-tahun/{id}/toggle-active', [RhTahunController::class, 'toggleActive'])->name('rh-tahun.toggle-active');
    Route::put('/rh-tahun/{id}', [RhTahunController::class, 'updateTahun'])->name('rh-tahun.update');
    Route::delete('/rh-tahun/{id}', [RhTahunController::class, 'destroyTahun'])->name('rh-tahun.destroy');
    Route::post('/rh-perubahan', [RhTahunController::class, 'storePerubahan'])->name('rh-perubahan.store');
    Route::put('/rh-perubahan/{id}', [RhTahunController::class, 'updatePerubahan'])->name('rh-perubahan.update');
    Route::delete('/rh-perubahan/{id}', [RhTahunController::class, 'destroyPerubahan'])->name('rh-perubahan.destroy');

    // RH Price Range Values
    Route::get('/price-range/input-nilai', [RhNilaiController::class, 'index'])->name('rh-nilai.index');
    Route::post('/price-range/input-nilai/save', [RhNilaiController::class, 'save'])->name('rh-nilai.save');

    // RH Verification
    Route::get('/verification', [VerificationController::class, 'index'])->name('verification.index');
    Route::get('/verification/{kabupatenId}', [VerificationController::class, 'show'])->name('verification.show');
    Route::post('/verification/{kabupatenId}', [VerificationController::class, 'store'])->name('verification.store');
});
