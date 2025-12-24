<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/poverty/input', function () {
    return view('poverty.input');
})->name('poverty.input');
