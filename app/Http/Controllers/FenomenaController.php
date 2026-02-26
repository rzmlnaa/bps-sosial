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
        $indikators = \App\Models\Indikator::with(['userAdd', 'userUpdate'])
            ->orderByRaw("CASE WHEN kelompok = 'utama' THEN 1 ELSE 2 END")
            ->orderBy('kode', 'asc')
            ->get();
        $jenisFenomenas = \App\Models\JenisFenomena::with(['userAdd', 'userUpdate'])->orderBy('nama', 'asc')->get();
        $sumberBeritas = \App\Models\SumberBerita::with(['userAdd', 'userUpdate'])->orderBy('nama', 'asc')->get();
        return view('fenomena.kelola', compact('sektorUsahas', 'indikators', 'jenisFenomenas', 'sumberBeritas'));
    }

}
