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
        $topContributors = User::withCount([
            'fenomenas' => function ($query) {
                $query->where('status_verifikasi', 'Y');
            }
        ])
            ->withMax([
                'fenomenas' => function ($query) {
                    $query->where('status_verifikasi', 'Y');
                }
            ], 'created_at')
            ->having('fenomenas_count', '>', 0)
            ->orderBy('fenomenas_count', 'desc')
            ->orderBy('fenomenas_max_created_at', 'asc')
            ->take(10)
            ->get();

        $stats = Fenomena::where('status_verifikasi', 'Y')
            ->selectRaw('
        COUNT(*) as total_verified,
        COUNT(DISTINCT created_by) as total_contributors,
        MAX(created_at) as last_update
    ')
            ->first();
        $totalVerified = $stats->total_verified;
        $totalContributors = $stats->total_contributors;
        $lastUpdate = $stats->last_update;

        return view('fenomena.contributor', compact('topContributors', 'totalVerified', 'totalContributors', 'lastUpdate'));
    }
}
