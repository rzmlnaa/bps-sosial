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
    $kabupatens = Kabupaten::with('userAdd')->get();
    $variabels = VariabelKemiskinan::all();
    return view('poverty.input', compact('kabupatens', 'variabels'));
})->name('poverty.input');

use App\Http\Controllers\VariabelController;

Route::post('/kabupaten', [KabupatenController::class, 'store'])->name('kabupaten.store');
Route::put('/kabupaten/{id}', [KabupatenController::class, 'update'])->name('kabupaten.update');

Route::post('/variabel', [VariabelController::class, 'store'])->name('variabel.store');
Route::delete('/variabel/{id}', [VariabelController::class, 'destroy'])->name('variabel.destroy');
