<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SerutiController extends Controller
{
    public function index()
    {
        return view('seruti.index');
    }

    public function create()
    {
        return view('seruti.input');
    }

    public function store(Request $request)
    {
        // To be implemented
    }
}
