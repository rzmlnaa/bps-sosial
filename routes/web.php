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
use App\Http\Controllers\SerutiController;
use App\Http\Controllers\FenomenaController;
use App\Http\Controllers\FenomenaVerificationController;
use App\Http\Controllers\DynamicMenuController;
use App\Http\Controllers\FrontendMenuController;
use App\Http\Controllers\SektorUsahaController;
use App\Http\Controllers\IndikatorController;
use App\Http\Controllers\MyTeamController;



// --- Authentication Routes (Public/Guest) ---

Route::get('/login', function () {
    if (Auth::check()) {
        return redirect('/dashboard');
    }
    return view('auth.login');
})->name('login');

Route::get('auth/google', [LoginController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('auth/google/callback', [LoginController::class, 'handleGoogleCallback']);

Route::get('/developer', function () {
    return redirect()->away('https://kostapp.reservasiaja.com/portofolio');
});

Route::middleware(['auth'])->group(function () {
    // Admin Routes
    Route::middleware(['can:access-admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\AdminController::class, 'index'])->name('dashboard');
        Route::get('/users', [\App\Http\Controllers\AdminController::class, 'users'])->name('users');
        Route::delete('/users/{id}', [\App\Http\Controllers\AdminController::class, 'destroy'])->name('users.destroy');
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

        // Dynamic Menus Admin
        Route::resource('dynamic-menus', DynamicMenuController::class)->except(['show']);
    });

    Route::post('/logout', function (Request $request) {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');

    })->name('logout');
});


// --- Profile Completion (Auth Required, Self-Allowed by Middleware) ---

Route::middleware(['auth', 'check.status'])->group(function () {
    Route::get('/complete-profile', [ProfileCompletionController::class, 'show'])->name('complete-profile');
    Route::post('/complete-profile', [ProfileCompletionController::class, 'update'])->name('complete-profile.update');
    Route::post('/complete-profile/verify-otp', [ProfileCompletionController::class, 'verifyOtp'])->name('complete-profile.verify-otp');
    Route::post('/complete-profile/resend-otp', [ProfileCompletionController::class, 'resendOtp'])->name('complete-profile.resend-otp');
    Route::post('/complete-profile/reset-number', [ProfileCompletionController::class, 'resetNumber'])->name('complete-profile.reset-number');


    // RH Price Range Values
    Route::get('/price-range/input-nilai', [RhNilaiController::class, 'index'])->name('rh-nilai.index');
    Route::post('/price-range/input-nilai/save', [RhNilaiController::class, 'save'])->name('rh-nilai.save');

    // Fenomena Values
    Route::get('/fenomena/input', [FenomenaController::class, 'create'])->name('fenomena.create');
    Route::get('/fenomena/check-uniqueness', [FenomenaController::class, 'checkUniqueness'])->name('fenomena.check-uniqueness');
    Route::post('/fenomena', [FenomenaController::class, 'store'])->name('fenomena.store');

    // My Team Route
    Route::get('/my-team', [MyTeamController::class, 'index'])->name('my-team.index');
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
            $request->merge(['tahun' => 'all']);
            //$request->merge(['tahun' => $latestYear]);
        }
        $selectedTahun = $request->get('tahun');

        if ($selectedTahun == 'all' || $selectedTahun == null) {
            $mainVar = $variabels->first();
        } else {
            $mainVar = VariabelKemiskinan::where('tahun', $selectedTahun)->latest()->first();
        }
        $nilaiKemiskinan = \App\Models\NilaiKemiskinan::all()->keyBy('kabupaten_id');

        // $kabupatenData = [];
        // foreach ($kabupatens as $kab) {
        //     $ceknilai = \App\Models\NilaiKemiskinan::where('kabupaten_id', $kab->id)->first();
        //     $kabupatenData[] = [
        //         'id' => $kab->id,
        //         'kode_kab' => $kab->kode_kab,
        //         'name' => $kab->nama_kabupaten,
        //         'nilai' => $ceknilai
        //     ];
        // }
        // $kabupatenData = array_filter($kabupatenData, function ($item) {
        //     return !is_null($item['nilai']);
        // });

        $kabupatenData = $kabupatens->map(function ($kab) use ($nilaiKemiskinan) {
            $nilai = $nilaiKemiskinan->get($kab->id);

            if (!$nilai) {
                return null;
            }

            return [
                'id' => $kab->id,
                'kode_kab' => $kab->kode_kab,
                'name' => $kab->nama_kabupaten,
                'nilai' => $nilai
            ];
        })->filter()->values();

        //dd($kabupatenData);

        //dd($kabupatens, $kabupatenData);

        // $provAvg = count($kabupatenData) > 0 ? (array_sum(array_column($kabupatenData, 'avg_nilai')) / count($kabupatenData)) : 0;
        // $provCount = array_sum(array_column($kabupatenData, 'count'));
        // $provGK = count($kabupatenData) > 0 ? (array_sum(array_column($kabupatenData, 'gk')) / count($kabupatenData)) : 0;

        $provAvg = $kabupatenData->whereNotNull('nilai')->avg(fn($item) => $item['nilai']->avg_nilai);
        $provCount = $kabupatenData->sum(fn($item) => $item['nilai']->count);
        $provGK = $kabupatenData->avg(fn($item) => $item['nilai']->gk);

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


        return view('poverty.index', compact('kabupatens', 'variabels', 'kabupatenData', 'mainVar', 'provAvg', 'provCount', 'provGK', 'latestLabel', 'selectedTahun', 'availableYears', 'bulanNama'));
    })->name('poverty');

    Route::get('/poverty-data/get-data/{kabupaten_id}', [PovertyDataController::class, 'getData']);

    Route::get('/price-range', [PriceRangeController::class, 'index'])->name('price-range.index');
    Route::get('/price-range/export', [PriceRangeController::class, 'export'])->name('price-range.export');

    // SERUTI (Public Read)
    Route::get('/seruti', [SerutiController::class, 'index'])->name('seruti.index');
    Route::get('/seruti/chart-data', [SerutiController::class, 'getChartData'])->name('seruti.chart-data');

    // Dynamic Menu Frontend
    Route::get('/menu/{slug}', [FrontendMenuController::class, 'show'])->name('dynamic-menu.show');

    // Menu Fenomena
    Route::get('/fenomena', [FenomenaController::class, 'index'])->name('fenomena.index');
    Route::get('/fenomena-visualisasi', [FenomenaController::class, 'visualisasi'])->name('fenomena.visualisasi');
    Route::get('/fenomena-contributor', [\App\Http\Controllers\FenomenaContributorController::class, 'index'])->name('fenomena.contributor');


});



Route::middleware(['auth', 'check.status', 'only.province'])->group(function () {

    Route::get('/poverty/input', function () {
        $kabupatens = Kabupaten::with(['userAdd', 'userUpdate'])->orderBy('kode_kab', 'asc')->get();
        $variabels = VariabelKemiskinan::with('userAdd')->orderBy('tahun', 'desc')->orderBy('bulan', 'desc')->get();
        return view('poverty.input', compact('kabupatens', 'variabels'));
    })->name('poverty.input');

    Route::post('/variabel', [VariabelController::class, 'store'])->name('variabel.store');
    Route::delete('/variabel/{id}', [VariabelController::class, 'destroy'])->name('variabel.destroy');

    Route::post('/poverty-data', [PovertyDataController::class, 'store'])->name('poverty-data.store');
    Route::delete('/poverty-data/clear', [PovertyDataController::class, 'clearData'])->name('poverty-data.clear');
    // Route::get('/poverty-data/get-data/{kabupaten_id}', [PovertyDataController::class, 'getData']);
    Route::get('/poverty-data/get-raw/{kabupaten_id}/{variabel_id}', [PovertyDataController::class, 'getRawData']);
    Route::get('/poverty-data/export/{kabupaten_id}', [PovertyDataController::class, 'exportToCSV'])->name('poverty-data.export');

    Route::post('/kategori-komoditas', [KategoriKomoditasController::class, 'store'])->name('kategori-komoditas.store');
    Route::put('/kategori-komoditas/{id}', [KategoriKomoditasController::class, 'update'])->name('kategori-komoditas.update');
    Route::delete('/kategori-komoditas/{id}', [KategoriKomoditasController::class, 'destroy'])->name('kategori-komoditas.destroy');

    Route::post('/komoditas', [KomoditasController::class, 'store'])->name('komoditas.store');
    Route::put('/komoditas/{id}', [KomoditasController::class, 'update'])->name('komoditas.update');
    Route::delete('/komoditas/clear', [KomoditasController::class, 'clearData'])->name('komoditas.clear');
    Route::delete('/komoditas/{id}', [KomoditasController::class, 'destroy'])->name('komoditas.destroy');
    Route::get('/komoditas/get-by-category/{kategori_id}', [KomoditasController::class, 'getByCategory']);
    Route::patch('/komoditas/reorder', [KomoditasController::class, 'reorder'])->name('komoditas.reorder');

    // RH Year & Revision Management
    Route::post('/rh-tahun', [RhTahunController::class, 'storeTahun'])->name('rh-tahun.store');
    Route::patch('/rh-tahun/{id}/toggle-active', [RhTahunController::class, 'toggleActive'])->name('rh-tahun.toggle-active');
    Route::put('/rh-tahun/{id}', [RhTahunController::class, 'updateTahun'])->name('rh-tahun.update');
    Route::delete('/rh-tahun/{id}', [RhTahunController::class, 'destroyTahun'])->name('rh-tahun.destroy');
    Route::post('/rh-perubahan', [RhTahunController::class, 'storePerubahan'])->name('rh-perubahan.store');
    Route::put('/rh-perubahan/{id}', [RhTahunController::class, 'updatePerubahan'])->name('rh-perubahan.update');
    Route::delete('/rh-perubahan/{id}', [RhTahunController::class, 'destroyPerubahan'])->name('rh-perubahan.destroy');

    // Input Komoditas
    Route::get('/price-range/input', function () {
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


    //RH Verification
    Route::get('/verification', [VerificationController::class, 'index'])->name('verification.index');
    Route::get('/verification/{kabupatenId}', [VerificationController::class, 'show'])->name('verification.show');
    Route::post('/verification/{kabupatenId}', [VerificationController::class, 'store'])->name('verification.store');

    // SERUTI (Input Restricted)
    Route::get('/seruti/input', [SerutiController::class, 'create'])->name('seruti.create');
    Route::get('/seruti/get-data', [SerutiController::class, 'getData'])->name('seruti.get-data');
    Route::post('/seruti', [SerutiController::class, 'store'])->name('seruti.store');


    Route::post('/seruti/store-coicop', [SerutiController::class, 'storeCoicop'])->name('seruti.store-coicop');
    Route::delete('/seruti/coicop/{id}', [SerutiController::class, 'destroyCoicop'])->name('seruti.destroy-coicop');
    Route::delete('/seruti/clear-consumption', [SerutiController::class, 'destroyConsumption'])->name('seruti.destroy-consumption');


    // Kelola Fenomena
    Route::get('/fenomena/kelola', [FenomenaController::class, 'kelola'])->name('fenomena.kelola');
    // Detail Fenomena (setelah /kelola agar route statis tidak tertangkap oleh {id})
    //Route::get('/fenomena/{id}', [FenomenaController::class, 'show'])->name('fenomena.show');

    // Sektor Usaha
    Route::post('/sektor-usaha', [SektorUsahaController::class, 'store'])->name('sektor-usaha.store');
    Route::put('/sektor-usaha/{id}', [SektorUsahaController::class, 'update'])->name('sektor-usaha.update');
    Route::delete('/sektor-usaha/{id}', [SektorUsahaController::class, 'destroy'])->name('sektor-usaha.destroy');

    // Kode Indikator
    Route::post('/indikator', [IndikatorController::class, 'store'])->name('indikator.store');
    Route::put('/indikator/{id}', [IndikatorController::class, 'update'])->name('indikator.update');
    Route::patch('/indikator/{id}/toggle-active', [IndikatorController::class, 'toggleActive'])->name('indikator.toggle-active');
    Route::delete('/indikator/{id}', [IndikatorController::class, 'destroy'])->name('indikator.destroy');

    // Jenis Fenomena
    Route::post('/jenis-fenomena', [\App\Http\Controllers\JenisFenomenaController::class, 'store'])->name('jenis-fenomena.store');
    Route::put('/jenis-fenomena/{id}', [\App\Http\Controllers\JenisFenomenaController::class, 'update'])->name('jenis-fenomena.update');
    Route::delete('/jenis-fenomena/{id}', [\App\Http\Controllers\JenisFenomenaController::class, 'destroy'])->name('jenis-fenomena.destroy');

    // Sumber Berita
    Route::post('/sumber-berita', [\App\Http\Controllers\SumberBeritaController::class, 'store'])->name('sumber-berita.store');
    Route::put('/sumber-berita/{id}', [\App\Http\Controllers\SumberBeritaController::class, 'update'])->name('sumber-berita.update');
    Route::delete('/sumber-berita/{id}', [\App\Http\Controllers\SumberBeritaController::class, 'destroy'])->name('sumber-berita.destroy');

    // Fenomena Verification
    Route::get('/verification-fenomena', [FenomenaVerificationController::class, 'index'])->name('fenomena.verification.index');
    Route::get('/verification-fenomena/{id}', [FenomenaVerificationController::class, 'show'])->name('fenomena.verification.show');
    Route::post('/verification-fenomena/{id}', [FenomenaVerificationController::class, 'store'])->name('fenomena.verification.store');

    // Pra Ekspor
    Route::get('/pra-ekspor', [\App\Http\Controllers\PraEksporController::class, 'index'])->name('pra-ekspor.index');
    Route::post('/pra-ekspor/toggle', [\App\Http\Controllers\PraEksporController::class, 'toggleSelection'])->name('pra-ekspor.toggle');
    Route::get('/pra-ekspor/preview', [\App\Http\Controllers\PraEksporController::class, 'preview'])->name('pra-ekspor.preview');
    Route::get('/pra-ekspor/export-excel', [\App\Http\Controllers\PraEksporController::class, 'exportExcel'])->name('pra-ekspor.export-excel');
});

// Detail Fenomena (Diletakkan di luar kelompok agar semua user bisa akses, 
// tapi di bawah route spesifik agar tidak terangkap oleh {id})
Route::middleware(['check.status'])->group(function () {
    Route::get('/fenomena/{id}', [FenomenaController::class, 'show'])->name('fenomena.show');
});


