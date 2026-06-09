<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RhTahun;
use App\Models\Kabupaten;
use App\Models\KategoriKomoditas;

use App\Models\RhPerubahanHeader;
use App\Models\RhPerubahanDetail;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Traits\HandlesRhStates;

class PriceRangeController extends Controller
{
    use HandlesRhStates;
    public function index(Request $request)
    {
        $years = RhTahun::orderBy('tahun', 'desc')->get();

        $kabupatens = Kabupaten::withoutIndonesia()->orderBy('kode_kab', 'asc')->where('kode_kab', '!=', '6100')->get();
        $allKomoditas = \App\Models\Komoditas::orderBy('order_number', 'asc')->get(); // Needed for full mapping

        $selectedYearId = $request->year_id ?? ($years->where('is_active', true)->first()->id ?? $years->first()->id ?? null);
        $selectedKabupatenId = $request->kabupaten_id ?? ($kabupatens->first()->id ?? null);
        $thresholdPercent = $request->threshold ? ($request->threshold / 100) : null;
        $selectedCategoryIds = $request->category_ids ?? [];
        $selectedCommodityIds = $request->commodity_ids ?? [];
        $selectedAnalysisKabupatenIds = $request->analysis_kabupaten_ids ?? [];

        $activeYear = $years->where('id', $selectedYearId)->first();

        // if ($activeYear == null) {
        //     return back()->with('error', 'Tahun RH tidak ditemukan');
        // }
        $categories = KategoriKomoditas::with([
            'komoditas' => function ($query) {
                $query->orderBy('order_number', 'asc');
            }
        ])->get();

        // Fetch commodities filtered by categories for the dropdown
        $availableKomoditas = \App\Models\Komoditas::query()
            ->when(!empty($selectedCategoryIds), function ($q) use ($selectedCategoryIds) {
                return $q->whereIn('kategori_id', $selectedCategoryIds);
            })
            ->orderBy('order_number', 'asc')
            ->get();

        // 1. Fetch Current View Data (Specific Kabupaten)
        $masterNilai = collect();
        $prevYearValues = [];
        if ($selectedYearId && $selectedKabupatenId) {

            // 1. Base = Final of Prev Year
            // 2. Overlay = Explicit Master of Current Year

            $prevYearNum = $activeYear->tahun - 1;
            $prevYearNum = $activeYear->tahun - 1;
            $baseState = $this->getFinalStateForYear($prevYearNum, $selectedKabupatenId);
            $prevYearValues = $baseState;

            // 3. Fetch Actual Master Records for Current Year
            // Using PerubahanDetail with header_id NULL
            $actualMaster = RhPerubahanDetail::where('rh_tahun_id', $selectedYearId)
                ->where('kabupaten_id', $selectedKabupatenId)
                ->whereNull('rh_perubahan_header_id')
                ->select('*', 'min_edit as min_nilai', 'max_edit as max_nilai')
                ->get()
                ->keyBy('komoditas_id');

            // 4. Merge
            $finalMasterData = [];

            //$allKomoditasIds = \App\Models\Komoditas::orderBy('order_number', 'asc')->pluck('id')->toArray();
            $allKomoditasIds = $allKomoditas->pluck('id')->toArray();

            foreach ($allKomoditasIds as $komId) {
                $m = $actualMaster->get($komId);
                $b = $baseState[$komId] ?? null;

                $val = new \stdClass();
                $val->min_nilai = null;
                $val->max_nilai = null;
                $val->alasan = null;

                // If explicit master exists, prefer it
                if ($m && ($m->min_nilai !== null || $m->max_nilai !== null)) {
                    $val->min_nilai = $m->min_nilai;
                    $val->max_nilai = $m->max_nilai;
                    $val->alasan = $m->alasan;
                    $val->verification_status = $m->verification_status;
                }
                // Else use base (prev year final)
                elseif ($b) {
                    $val->min_nilai = $b['min'];
                    $val->max_nilai = $b['max'];
                    $val->alasan = $b['alasan'];
                }

                $finalMasterData[$komId] = $val;
            }

            $masterNilai = collect($finalMasterData);
        }

        $revisions = collect();
        $revisionDetails = collect();
        if ($selectedYearId) {
            $revisions = RhPerubahanHeader::where('rh_tahun_id', $selectedYearId)
                ->orderBy('tanggal_perubahan', 'asc')
                ->get();

            if ($selectedKabupatenId) {
                $revisionDetails = RhPerubahanDetail::whereIn('rh_perubahan_header_id', $revisions->pluck('id'))
                    ->where('kabupaten_id', $selectedKabupatenId)
                    ->get()
                    ->groupBy('rh_perubahan_header_id');

                $revisionDetails = $revisionDetails->map(function ($items) {
                    return $items->keyBy('komoditas_id');
                });
            }
        }

        // 3. Rejected Summary (For Alert Box)
        $rejectedSummary = [];
        if ($selectedYearId) {
            $rejectedRaw = RhPerubahanDetail::where('rh_tahun_id', $selectedYearId)
                ->where('verification_status', 'rejected')
                ->select(\Illuminate\Support\Facades\DB::raw('count(*) as total'), 'kabupaten_id')
                ->groupBy('kabupaten_id')
                ->get();

            foreach ($rejectedRaw as $item) {
                $kab = $kabupatens->firstWhere('id', $item->kabupaten_id);
                if ($kab) {
                    $rejectedSummary[] = [
                        'kode_kab' => $kab->kode_kab,
                        'nama_kab' => $kab->nama_kabupaten,
                        'total' => $item->total
                    ];
                }
            }
        }

        // 2. Outlier Analysis (Global Context for Selected Year)
        $outliers = [];
        if ($selectedYearId && $thresholdPercent !== null) {
            $analysisRevisions = RhPerubahanHeader::where('rh_tahun_id', $selectedYearId)->orderBy('tanggal_perubahan', 'asc')->get();
            $outliers = $this->calculateOutliers($selectedYearId, $kabupatens, $allKomoditas, $analysisRevisions, $thresholdPercent, $selectedCategoryIds, $selectedAnalysisKabupatenIds, $selectedCommodityIds);
        }

        $idMaxRHPerubahan = null;
        if (auth()->check() == true) {

            $isActiveYear = RhTahun::where('is_active', 1)->first();
            if ($isActiveYear) {
                $cekPerubahanMax = RhPerubahanHeader::where('rh_tahun_id', $isActiveYear->id)->get();
                $idMaxRHPerubahan = $cekPerubahanMax->max('id');
            }

        }

        return view('price-range.index', compact(
            'years',
            'idMaxRHPerubahan',
            'kabupatens',
            'selectedYearId',
            'selectedKabupatenId',
            'activeYear',
            'categories',
            'masterNilai',
            'prevYearValues',
            'revisions',
            'revisionDetails',
            'outliers',
            'rejectedSummary',
            'thresholdPercent',
            'selectedCategoryIds',
            'availableKomoditas',
            'selectedCategoryIds',
            'availableKomoditas',
            'selectedAnalysisKabupatenIds',
            'selectedCommodityIds'
        ));
    }

    private function calculateOutliers($yearId, $kabupatens, $allKomoditas, $revisions, $thresholdPercent, $selectedCategoryIds = [], $selectedAnalysisKabupatenIds = [], $selectedCommodityIds = [])
    {
        $activeYear = RhTahun::find($yearId);
        if (!$activeYear) {
            return [];
        }

        $results = [];
        // Filter commodities by selected categories/specific IDs if provided
        if (!empty($selectedCategoryIds)) {
            $allKomoditas = $allKomoditas->whereIn('kategori_id', $selectedCategoryIds);
        }

        // Filter by specific commodities if provided
        if (!empty($selectedCommodityIds)) {
            $allKomoditas = $allKomoditas->whereIn('id', $selectedCommodityIds);
        }
        // Initial State: Load all Master Data (Carrying over from previous year)
        // Optimization: Use getBulkStates to get all kabupatens' final states of the previous year
        $dataState = $this->getBulkStates($activeYear->tahun - 1, $kabupatens->pluck('id')->toArray());

        // Ensure every kabupaten exists in dataState even if no history
        foreach ($kabupatens as $kab) {
            if (!isset($dataState[$kab->id])) {
                $dataState[$kab->id] = [];
            }
        }

        // Overlay current year's Master Data (explicit edits for this year)
        $masterData = RhPerubahanDetail::where('rh_tahun_id', $yearId)
            ->whereNull('rh_perubahan_header_id')
            ->get();

        foreach ($masterData as $md) {
            if ($md->min_edit !== null || $md->max_edit !== null) {
                $dataState[$md->kabupaten_id][$md->komoditas_id] = [
                    'min' => $md->min_edit,
                    'max' => $md->max_edit
                ];
            }
        }

        // --- Analyze Master Period ---
        $results['Master Data'] = $this->analyzeSnapshot($dataState, $kabupatens, $allKomoditas, $thresholdPercent, $selectedAnalysisKabupatenIds);

        // --- Analyze Each Revision ---
        foreach ($revisions as $rev) {
            // Update state with revision details
            $details = RhPerubahanDetail::where('rh_perubahan_header_id', $rev->id)->get();

            foreach ($details as $dt) {

                if (!isset($dataState[$dt->kabupaten_id]))
                    $dataState[$dt->kabupaten_id] = [];

                // Update Min
                if ($dt->min_edit !== null) {
                    $dataState[$dt->kabupaten_id][$dt->komoditas_id]['min'] = $dt->min_edit;
                }
                // Update Max
                if ($dt->max_edit !== null) {
                    $dataState[$dt->kabupaten_id][$dt->komoditas_id]['max'] = $dt->max_edit;
                }
            }

            $results[strtoupper($rev->label)] = $this->analyzeSnapshot($dataState, $kabupatens, $allKomoditas, $thresholdPercent, $selectedAnalysisKabupatenIds);
        }

        return $results;
    }

    private function analyzeSnapshot($dataState, $kabupatens, $allKomoditas, $threshold, $selectedAnalysisKabupatenIds = [])
    {
        $snapshotOutliers = [];

        foreach ($allKomoditas as $komo) {
            $pricesMin = [];
            $pricesMax = [];
            $kabMap = []; // Store references

            foreach ($kabupatens as $kab) {
                if (isset($dataState[$kab->id][$komo->id])) {
                    $val = $dataState[$kab->id][$komo->id];
                    if (isset($val['min']))
                        $pricesMin[$kab->id] = $val['min'];
                    if (isset($val['max']))
                        $pricesMax[$kab->id] = $val['max'];
                }
            }

            if (empty($pricesMin) && empty($pricesMax))
                continue;

            // Calculate Stats
            $avgMin = count($pricesMin) > 0 ? array_sum($pricesMin) / count($pricesMin) : 0;
            $avgMax = count($pricesMax) > 0 ? array_sum($pricesMax) / count($pricesMax) : 0;

            $itemOutliers = ['below' => [], 'above' => []];
            $found = false;

            // Check Min Outliers (Low)
            if ($avgMin > 0) {
                foreach ($pricesMin as $kid => $p) {
                    // Filter: Only include if no filter selected OR if this kab is in selected list
                    if (!empty($selectedAnalysisKabupatenIds) && !in_array($kid, $selectedAnalysisKabupatenIds)) {
                        continue;
                    }

                    if ($p < $avgMin * (1 - $threshold)) {
                        $kab = $kabupatens->firstWhere('id', $kid);
                        $itemOutliers['below'][] = [
                            'kode_kab' => $kab->kode_kab ?? '??',
                            'kab' => $kab->nama_kabupaten ?? 'Unknown',
                            'type' => 'MIN',
                            'val' => $p,
                            'avg' => $avgMin,
                            'diff' => round((($avgMin - $p) / $avgMin) * 100) . '%'
                        ];
                        $found = true;
                    }
                }
            }

            // Check Max Outliers (High)
            if ($avgMax > 0) {
                foreach ($pricesMax as $kid => $p) {
                    // Filter: Only include if no filter selected OR if this kab is in selected list
                    if (!empty($selectedAnalysisKabupatenIds) && !in_array($kid, $selectedAnalysisKabupatenIds)) {
                        continue;
                    }

                    if ($p > $avgMax * (1 + $threshold)) {
                        $kab = $kabupatens->firstWhere('id', $kid);
                        $itemOutliers['above'][] = [
                            'kode_kab' => $kab->kode_kab ?? '??',
                            'kab' => $kab->nama_kabupaten ?? 'Unknown',
                            'type' => 'MAX',
                            'val' => $p,
                            'avg' => $avgMax,
                            'diff' => round((($p - $avgMax) / $avgMax) * 100) . '%'
                        ];
                        $found = true;
                    }
                }
            }

            if ($found) {
                $snapshotOutliers[$komo->nama_komoditas] = $itemOutliers;
                $snapshotOutliers[$komo->nama_komoditas]['unit'] = $komo->satuan;
                $snapshotOutliers[$komo->nama_komoditas]['avg_min'] = $avgMin;
                $snapshotOutliers[$komo->nama_komoditas]['avg_max'] = $avgMax;
            }
        }

        return $snapshotOutliers;
    }

    public function export(Request $request)
    {

        $yearId = $request->year_id;
        $year = RhTahun::findOrFail($yearId);

        $fileName = "RH_{$year->tahun}.xlsx";

        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\PriceRangeExport($yearId), $fileName);
    }
}
