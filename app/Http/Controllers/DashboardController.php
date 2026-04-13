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

class DashboardController extends Controller
{
    public function index()
    {
        // 1. KPI Stats with Deltas (Monthly Comparison)
        $now = now();
        $lastMonth = now()->subMonth();

        $stats = [
            'poverty_data_count' => NilaiKemiskinan::count(),
            'seruti_data_count' => ConsumptionValue::count(),
            'price_range_count' => RhPerubahanDetail::count(),
            'menu_count' => DynamicMenu::count(),
        ];

        // Delta calculation (Current month entries vs Previous month)
        $deltas = [
            'poverty' => $this->calculateDelta(NilaiKemiskinan::class, $now, $lastMonth),
            'seruti' => $this->calculateDelta(ConsumptionValue::class, $now, $lastMonth),
            'price' => $this->calculateDelta(RhPerubahanDetail::class, $now, $lastMonth),
        ];

        // ... existing code ...
        // 3. Regional Poverty Distribution (For Bar Chart)
        $latestVar = VariabelKemiskinan::latest()->first();
        // ... (rest of the index method)





        // 3. Regional Poverty Distribution (For Bar Chart)
        $latestVar = VariabelKemiskinan::latest()->first();
        $regionalPoverty = [];
        if ($latestVar) {
            $regionalPoverty = \App\Models\Kabupaten::with([
                'nilaiKemiskinan' => function ($q) use ($latestVar) {
                    $q->where('variabel_kemiskinan_id', $latestVar->id);
                }
            ])
                ->get()
                ->map(function ($kab) {
                    return [
                        'name' => $kab->nama_kabupaten,
                        'value' => $kab->nilaiKemiskinan->avg('nilai') ?? 0
                    ];
                })
                ->where('value', '>', 0)
                ->values();
        }

        // 4. Existing Content Data
        $latestPovertyData = NilaiKemiskinan::with(['kabupaten', 'variabelKemiskinan'])
            ->select('kabupaten_id', 'variabel_kemiskinan_id', \Illuminate\Support\Facades\DB::raw('MAX(created_at) as last_update'))
            ->groupBy('kabupaten_id', 'variabel_kemiskinan_id')
            ->orderBy('last_update', 'desc')
            ->take(5)
            ->get()
            ->map(function ($group) {
                $stats = NilaiKemiskinan::where('kabupaten_id', $group->kabupaten_id)
                    ->where('variabel_kemiskinan_id', $group->variabel_kemiskinan_id)
                    ->selectRaw('AVG(nilai) as avg_nilai')
                    ->first();
                return (object) [
                    'kabupaten_nama' => $group->kabupaten->nama_kabupaten ?? '-',
                    'variabel_nama' => $group->variabelKemiskinan->nama_variabel ?? '-',
                    'tahun' => $group->variabelKemiskinan->tahun ?? '-',
                    'avg_nilai' => $stats->avg_nilai ?? 0,
                ];
            });

        $activeRhTahun = RhTahun::where('is_active', true)->first();
        $latestPriceChanges = RhPerubahanDetail::with(['revisionHeader', 'komoditas', 'kabupaten'])
            ->whereNotNull('min_edit')
            ->orderBy('updated_at', 'desc')
            ->take(5)
            ->get();

        // Poverty Trend (thicker line data)
        $povertyTrend = VariabelKemiskinan::with(['nilaiKemiskinan'])
            ->orderBy('tahun', 'asc')
            ->orderBy('bulan', 'asc')
            ->take(12)
            ->get()
            ->map(function ($v) {
                return [
                    'label' => ($v->bulan ? $v->bulan . '/' : '') . $v->tahun,
                    'avg' => $v->nilaiKemiskinan->avg('nilai') ?? 0
                ];
            });

        // Commodity Trend (More Contrast)
        $sampleKomoditas = Komoditas::whereHas('rhPerubahanDetails')
            ->withCount('rhPerubahanDetails')
            ->orderBy('rh_perubahan_details_count', 'desc')
            ->take(5)
            ->get();

        $priceTrend = [];
        $priceLabels = [];
        if ($activeRhTahun) {
            $revisions = \App\Models\RhPerubahanHeader::where('rh_tahun_id', $activeRhTahun->id)
                ->orderBy('tanggal_perubahan', 'asc')
                ->get();
            $priceLabels = $revisions->pluck('label')->toArray();

            foreach ($sampleKomoditas as $kom) {
                $series = [];
                foreach ($revisions as $rev) {
                    $avgPrice = RhPerubahanDetail::where('rh_perubahan_header_id', $rev->id)
                        ->where('komoditas_id', $kom->id)
                        ->selectRaw('AVG((min_edit + max_edit) / 2) as avg_price')
                        ->first()
                        ->avg_price;
                    $series[] = $avgPrice ?? 0;
                }
                $priceTrend[] = [
                    'name' => $kom->nama_komoditas,
                    'data' => $series
                ];
            }
        }

        $latestSeruti = \App\Models\ConsumptionValue::with(['kabupaten', 'period'])
            ->select('kabupaten_id', 'period_id', \Illuminate\Support\Facades\DB::raw('MAX(updated_at) as last_update'), \Illuminate\Support\Facades\DB::raw('AVG(value) as avg_value'), \Illuminate\Support\Facades\DB::raw('COUNT(*) as total_items'))
            ->groupBy('kabupaten_id', 'period_id')
            ->orderBy('last_update', 'desc')
            ->take(5)
            ->get();

        $latestFenomena = \App\Models\Fenomena::with(['creator', 'sumberBerita'])
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard.index', compact(
            'stats',
            'deltas',

            'regionalPoverty',
            'latestPovertyData',
            'latestPriceChanges',
            'povertyTrend',
            'priceTrend',
            'priceLabels',
            'latestSeruti',
            'latestFenomena'
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
