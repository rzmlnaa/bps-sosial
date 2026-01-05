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
    $kabupatens = Kabupaten::all();
    $variabels = VariabelKemiskinan::all();
    $selectedTahun = $request->get('tahun', 'all');

    // Attempt to find a representative variable (Rupiah/GK related)
    if ($selectedTahun == 'all' || $selectedTahun == null) {
        $mainVar = $variabels->first();
    } else {
        $mainVar = VariabelKemiskinan::
            where('tahun', $selectedTahun)

            // ->orWhere('nama_variabel', 'like', '%Garis%')
            // ->orWhere('nama_variabel', 'like', '%Rupiah%')
            ->latest()->first()
            //?? $variabels->first()
        ;
    }

    $kabupatenData = [];
    foreach ($kabupatens as $kab) {
        // Calculate average value across all percentiles for this kabupaten and variabel
        // $avgValue = \App\Models\NilaiKemiskinan::where('kabupaten_id', $kab->id)
        //     ->where('variabel_kemiskinan_id', $mainVar->id ?? 0)
        //     ->avg('nilai') ?? 0;
        $ceknilai = \App\Models\NilaiKemiskinan::where('kabupaten_id', $kab->id)->first();

        // Fetch specific variables for the table (optional display)
        // $varCount = VariabelKemiskinan::where('nama_variabel', 'like', '%Jumlah%')->latest()->first();
        // $varGK = VariabelKemiskinan::where('nama_variabel', 'like', '%GK%')->orWhere('nama_variabel', 'like', '%Garis%')->latest()->first();
        // $count = $varCount ? \App\Models\NilaiKemiskinan::where('kabupaten_id', $kab->id)
        //     ->where('variabel_kemiskinan_id', $varCount->id)
        //     ->where('persentil', 1)

        //     ->value('nilai') : 0;

        // $gk = $varGK ? \App\Models\NilaiKemiskinan::where('kabupaten_id', $kab->id)
        //     ->where('variabel_kemiskinan_id', $varGK->id)
        //     ->where('persentil', 1)
        //     ->value('nilai') : 0;

        // if ($avgValue > 0) {
        $kabupatenData[] = [
            'id' => $kab->id,
            'name' => $kab->nama_kabupaten,
            'nilai' => $ceknilai
            // 'avg_nilai' => floatval($avgValue),
            // 'count' => floatval($count),
            // 'nilai'=>
            // 'gk' => floatval($gk)
        ];
        //}

    }
    // $kabupatenData = array_filter($kabupatenData, function ($item) {
    //     return $item['avg_nilai'] != 0;
    // });
    $kabupatenData = array_filter($kabupatenData, function ($item) {
        return !is_null($item['nilai']);
    });
    //dd($kabupatenData);



    // Calculate Provincial Aggregates
    $provAvg = count($kabupatenData) > 0 ? (array_sum(array_column($kabupatenData, 'avg_nilai')) / count($kabupatenData)) : 0;
    $provCount = array_sum(array_column($kabupatenData, 'count'));
    $provGK = count($kabupatenData) > 0 ? (array_sum(array_column($kabupatenData, 'gk')) / count($kabupatenData)) : 0;

    // Determine Label for Latest Data
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

    return view('poverty.index', compact('kabupatens', 'variabels', 'kabupatenData', 'mainVar', 'provAvg', 'provCount', 'provGK', 'latestLabel'));
})->name('poverty');

Route::get('/poverty/input', function () {
    $kabupatens = Kabupaten::with(['userAdd', 'userUpdate'])->get();
    $variabels = VariabelKemiskinan::with('userAdd')->get();
    return view('poverty.input', compact('kabupatens', 'variabels'));
})->name('poverty.input');

Route::post('/kabupaten', [KabupatenController::class, 'store'])->name('kabupaten.store');
Route::put('/kabupaten/{id}', [KabupatenController::class, 'update'])->name('kabupaten.update');
Route::delete('/kabupaten/{id}', [KabupatenController::class, 'destroy'])->name('kabupaten.destroy');

Route::post('/variabel', [VariabelController::class, 'store'])->name('variabel.store');
Route::delete('/variabel/{id}', [VariabelController::class, 'destroy'])->name('variabel.destroy');

Route::post('/poverty-data', [PovertyDataController::class, 'store'])->name('poverty-data.store');
Route::delete('/poverty-data/clear', [PovertyDataController::class, 'clearData'])->name('poverty-data.clear');
Route::get('/poverty-data/get-data/{kabupaten_id}', [PovertyDataController::class, 'getData']);
Route::get('/poverty-data/get-raw/{kabupaten_id}/{variabel_id}', [PovertyDataController::class, 'getRawData']);
Route::get('/poverty-data/export/{kabupaten_id}', [PovertyDataController::class, 'exportToCSV'])->name('poverty-data.export');

Route::get('/price-range', function () {
    return view('price-range.index');
})->name('price-range.index');

Route::get('/price-range/input', function () {
    $kategori = KategoriKomoditas::with(['userAdd', 'userUpdate'])->get();
    return view('price-range.input', compact('kategori'));
})->name('price-range.input');

Route::post('/kategori-komoditas', [KategoriKomoditasController::class, 'store'])->name('kategori-komoditas.store');
Route::put('/kategori-komoditas/{id}', [KategoriKomoditasController::class, 'update'])->name('kategori-komoditas.update');
Route::delete('/kategori-komoditas/{id}', [KategoriKomoditasController::class, 'destroy'])->name('kategori-komoditas.destroy');

Route::post('/komoditas', [KomoditasController::class, 'store'])->name('komoditas.store');
Route::delete('/komoditas/clear', [KomoditasController::class, 'clearData'])->name('komoditas.clear');
Route::delete('/komoditas/{id}', [KomoditasController::class, 'destroy'])->name('komoditas.destroy');
Route::get('/komoditas/get-by-category/{kategori_id}', [KomoditasController::class, 'getByCategory']);

Route::post('/logout', function () {
    Auth::logout();
    return redirect('/dashboard');
})->name('logout');
