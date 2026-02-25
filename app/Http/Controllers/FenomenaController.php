<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FenomenaController extends Controller
{
    public function index()
    {
        return view('fenomena.index');
    }

    public function create()
    {
        return view('fenomena.input');
    }

    public function kelola()
    {
        $sektorUsahas = \App\Models\SektorUsaha::with(['userAdd', 'userUpdate'])->orderBy('kode', 'asc')->get();
        $indikators = \App\Models\Indikator::with(['userAdd', 'userUpdate'])->orderBy('kode', 'asc')->get();
        return view('fenomena.kelola', compact('sektorUsahas', 'indikators'));
    }

}
