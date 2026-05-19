<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\NilaiKemiskinan;
use App\Models\ConsumptionValue;
use App\Models\RhPerubahanDetail;
use App\Models\DynamicMenu;
use App\Models\RhTahun;
use App\Models\VariabelKemiskinan;
use App\Models\Komoditas;
use App\Models\Kabupaten;
use App\Models\Kecamatan;
use App\Models\Desa;

class DashboardController extends Controller
{
    public function index()
    {

        $now = now();
        $lastMonth = now()->subMonth();

        $stats = [
            'price_range_count' => RhPerubahanDetail::count(),
            'menu_count' => DynamicMenu::where('is_active', true)
                ->whereNotIn('type', ['logo', 'panduan_pengguna'])
                ->whereNotIn('id', function ($query) {
                    $query->select('parent_id')
                        ->from('dynamic_menus')
                        ->whereNotNull('parent_id');
                })
                ->where(function ($query) {
                    $query->where(function ($q) {
                        $q->whereNotNull('url')->where('url', '!=', '');
                    })
                        ->orWhere(function ($q) {
                            $q->whereNotNull('embed_url')->where('embed_url', '!=', '');
                        })
                        ->orWhere(function ($q) {
                            $q->whereJsonLength('meta', '>', 0);
                        });
                })->count(),
            'kabupaten_count' => Kabupaten::where('kode_kab', '!=', '6100')->count(),
            'kecamatan_count' => Kecamatan::count(),
            'desa_count' => Desa::count(),
        ];


        $deltas = [
            'price' => $this->calculateDelta(RhPerubahanDetail::class, $now, $lastMonth),
        ];


        $activeRhTahun = RhTahun::where('is_active', true)->first();
        $allYears = RhTahun::orderBy('tahun', 'desc')->get();

        $selectedYearId = request('year_id');
        if ($selectedYearId === null) {

            $defaultYear = $allYears->firstWhere('tahun', 2026) ?? $activeRhTahun;
            $selectedYearId = $defaultYear->id ?? 'all';
        }

        $latestPriceChanges = RhPerubahanDetail::with(['revisionHeader', 'komoditas', 'kabupaten'])
            ->whereNotNull('min_edit')
            ->orderBy('updated_at', 'desc')
            ->take(5)
            ->get();



        $sampleKomoditas = Komoditas::whereHas('rhPerubahanDetails')
            ->withCount('rhPerubahanDetails')
            ->orderBy('rh_perubahan_details_count', 'desc')
            ->take(5)
            ->get();

        $priceTrend = [];
        $priceLabels = [];

        $revisionsQuery = \App\Models\RhPerubahanHeader::with('rhTahun')
            ->orderBy('tanggal_perubahan', 'desc');

        if ($selectedYearId !== 'all') {
            $revisionsQuery->where('rh_tahun_id', $selectedYearId);
        }

        $revisions = $revisionsQuery->take(12)
            ->get()
            ->reverse();


        $yearsData = [];


        if ($selectedYearId !== 'all') {
            $selYear = $allYears->firstWhere('id', $selectedYearId);
            if ($selYear) {
                $yearsData[$selYear->id] = [
                    'id' => $selYear->id,
                    'year' => $selYear->tahun ?? '-',
                    'revisions' => []
                ];
            }
        }

        foreach ($revisions as $rev) {
            $yId = $rev->rh_tahun_id;
            if (!isset($yearsData[$yId])) {
                $yearsData[$yId] = [
                    'id' => $yId,
                    'year' => $rev->rhTahun->tahun ?? '-',
                    'revisions' => []
                ];
            }
            $yearsData[$yId]['revisions'][] = $rev;
        }

        uasort($yearsData, fn($a, $b) => $a['year'] <=> $b['year']);

        if (!empty($yearsData)) {

            foreach ($yearsData as $yData) {
                $priceLabels[] = 'Master ' . $yData['year'];
                foreach ($yData['revisions'] as $rev) {
                    $priceLabels[] = $rev->label;
                }
            }

            foreach ($sampleKomoditas as $kom) {
                $series = [];

                foreach ($yearsData as $yData) {

                    $masterPrice = RhPerubahanDetail::whereNull('rh_perubahan_header_id')
                        ->where('rh_tahun_id', $yData['id'])
                        ->where('komoditas_id', $kom->id)
                        ->selectRaw('AVG((min_edit + max_edit) / 2) as avg_price')
                        ->first()
                        ->avg_price;
                    $series[] = (float) ($masterPrice ?? 0);


                    foreach ($yData['revisions'] as $rev) {
                        $avgPrice = RhPerubahanDetail::where('rh_perubahan_header_id', $rev->id)
                            ->where('komoditas_id', $kom->id)
                            ->selectRaw('AVG((min_edit + max_edit) / 2) as avg_price')
                            ->first()
                            ->avg_price;

                        $series[] = (float) ($avgPrice ?? 0);
                    }
                }

                $priceTrend[] = [
                    'name' => $kom->nama_komoditas,
                    'data' => $series
                ];
            }
        }


        $latestFenomena = \App\Models\Fenomena::with(['creator', 'sumberBerita'])
            ->latest()
            ->take(5)
            ->get();

        $latestOnlineUsers = User::with('kabupaten')
            ->whereNotNull('last_login_at')
            ->whereNotNull('no_hp')
            ->where('status', 'active')
            ->where('role', 'user')
            ->orderBy('last_login_at', 'desc')
            ->take(6)
            ->get();

        return view('dashboard.index', compact(
            'stats',
            'deltas',
            'activeRhTahun',
            'allYears',
            'selectedYearId',
            'latestPriceChanges',
            'priceTrend',
            'priceLabels',
            'latestFenomena',
            'latestOnlineUsers'
        ));
    }

    private function calculateDelta($model, $current, $previous)
    {
        $thisMonth = $model::whereMonth('created_at', $current->month)->whereYear('created_at', $current->year)->count();
        $lastMonth = $model::whereMonth('created_at', $previous->month)->whereYear('created_at', $previous->year)->count();

        $percentage = 0;
        if ($lastMonth == 0) {
            $percentage = $thisMonth > 0 ? 100 : 0;
        } else {
            $percentage = round((($thisMonth - $lastMonth) / $lastMonth) * 100, 1);
        }

        return [
            'this_month' => $thisMonth,
            'last_month' => $lastMonth,
            'percentage' => $percentage
        ];
    }
}
