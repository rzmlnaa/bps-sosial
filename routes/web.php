<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\KabupatenController;

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
    return view('poverty.index');
})->name('poverty');

use App\Models\Kabupaten;
use App\Models\VariabelKemiskinan;

Route::get('/poverty/input', function () {
    $kabupatens = Kabupaten::with(['userAdd', 'userUpdate'])->get();
    $variabels = VariabelKemiskinan::with('userAdd')->get();
    return view('poverty.input', compact('kabupatens', 'variabels'));
})->name('poverty.input');

use App\Http\Controllers\VariabelController;

use App\Http\Controllers\PovertyDataController;

Route::post('/kabupaten', [KabupatenController::class, 'store'])->name('kabupaten.store');
Route::put('/kabupaten/{id}', [KabupatenController::class, 'update'])->name('kabupaten.update');
Route::delete('/kabupaten/{id}', [KabupatenController::class, 'destroy'])->name('kabupaten.destroy');

Route::post('/variabel', [VariabelController::class, 'store'])->name('variabel.store');
Route::delete('/variabel/{id}', [VariabelController::class, 'destroy'])->name('variabel.destroy');

Route::post('/poverty-data', [PovertyDataController::class, 'store'])->name('poverty-data.store');
Route::get('/poverty-data/get-data/{kabupaten_id}', [PovertyDataController::class, 'getData']);
Route::get('/poverty-data/get-raw/{kabupaten_id}/{variabel_id}', [PovertyDataController::class, 'getRawData']);
Route::get('/poverty-data/export/{kabupaten_id}', [PovertyDataController::class, 'exportToCSV'])->name('poverty-data.export');
