<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Fenomena;
use Illuminate\Support\Facades\DB;

class FenomenaContributorController extends Controller
{
    public function index()
    {
        // Fetch users who have contributed phenomena, with their counts
        // $contributors = User::whereHas('fenomenas', function ($query) {
        //     $query->where('status_verifikasi', 'Y');
        // })
        //     ->withCount([
        //         'fenomenas' => function ($query) {
        //             $query->where('status_verifikasi', 'Y');
        //         }
        //     ])
        //     ->orderBy('fenomenas_count', 'desc')
        //     ->get();

        // // Calculate some stats
        // $totalContributors = $contributors->count();
        // $totalContributions = Fenomena::where('status_verifikasi', 'Y')->count();

        //return view('fenomena.contributor', compact('contributors', 'totalContributors', 'totalContributions'));

        return view('fenomena.contributor');
    }
}
