<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\KabupatenController;
use App\Models\Kabupaten;
use App\Models\VariabelKemiskinan;
use App\Http\Controllers\VariabelController;
use App\Http\Controllers\PovertyDataController;

Route::get('/', function () {
    return redirect('/dashboard');
});


Route::get('/dashboard', function () {
    return view('dashboard.index');
})->name('dashboard');

Route::get('/layouts', function () {
    return view('layouts.admin');
})->name('layouts');

Route::get('/poverty', function () {
    $kabupatens = Kabupaten::all();
    $variabels = VariabelKemiskinan::all();

    // Attempt to find specific types of variables for the dashboard
    $varPercentage = VariabelKemiskinan::where('nama_variabel', 'like', '%Persentase%')->latest()->first() ?? $variabels->first();
    $varCount = VariabelKemiskinan::where('nama_variabel', 'like', '%Jumlah%')->latest()->first();
    $varGK = VariabelKemiskinan::where('nama_variabel', 'like', '%GK%')->orWhere('nama_variabel', 'like', '%Garis%')->latest()->first();

    $kabupatenData = [];
    foreach ($kabupatens as $kab) {
        $percent = \App\Models\NilaiKemiskinan::where('kabupaten_id', $kab->id)
            ->where('variabel_kemiskinan_id', $varPercentage->id ?? 0)
            ->where('persentil', 1) // Just take first percentile as a representative value for now
            ->value('nilai') ?? 0;

        $count = $varCount ? \App\Models\NilaiKemiskinan::where('kabupaten_id', $kab->id)
            ->where('variabel_kemiskinan_id', $varCount->id)
            ->where('persentil', 1)
            ->value('nilai') : 0;

        $gk = $varGK ? \App\Models\NilaiKemiskinan::where('kabupaten_id', $kab->id)
            ->where('variabel_kemiskinan_id', $varGK->id)
            ->where('persentil', 1)
            ->value('nilai') : 0;

        $kabupatenData[] = [
            'id' => $kab->id,
            'name' => $kab->nama_kabupaten,
            'percent' => $percent,
            'count' => $count,
            'gk' => $gk
        ];
    }

    return view('poverty.index', compact('kabupatens', 'variabels', 'kabupatenData', 'varPercentage'));
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
Route::get('/poverty-data/get-data/{kabupaten_id}', [PovertyDataController::class, 'getData']);
Route::get('/poverty-data/get-raw/{kabupaten_id}/{variabel_id}', [PovertyDataController::class, 'getRawData']);
Route::get('/poverty-data/export/{kabupaten_id}', [PovertyDataController::class, 'exportToCSV'])->name('poverty-data.export');
