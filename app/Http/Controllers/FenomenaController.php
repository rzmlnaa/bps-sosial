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
        return view('fenomena.kelola');
    }

}
