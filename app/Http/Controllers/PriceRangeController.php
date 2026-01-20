<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RhTahun;
use App\Models\Kabupaten;
use App\Models\KategoriKomoditas;
use App\Models\RhMasterNilai;
use App\Models\RhPerubahanHeader;
use App\Models\RhPerubahanDetail;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PriceRangeController extends Controller
{
    public function index(Request $request)
    {
        $years = RhTahun::orderBy('tahun', 'desc')->get();
        $kabupatens = Kabupaten::orderBy('kode_kab', 'asc')->get();
        $allKomoditas = \App\Models\Komoditas::all(); // Needed for full mapping

        $selectedYearId = $request->year_id ?? ($years->where('is_active', true)->first()->id ?? $years->first()->id ?? null);
        $selectedKabupatenId = $request->kabupaten_id ?? ($kabupatens->first()->id ?? null);

        $activeYear = $years->where('id', $selectedYearId)->first();

        if ($activeYear == null) {
            return back()->with('error', 'Tahun RH tidak ditemukan');
        }
        $categories = KategoriKomoditas::with(['komoditas'])->get();

        // 1. Fetch Current View Data (Specific Kabupaten)
        $masterNilai = collect();
        $prevYearValues = [];
        if ($selectedYearId && $selectedKabupatenId) {

            // A. Get Final State of PREVIOUS Year (Recursive Fallback)
            // Even though we are mostly looking at "Current Master", the logic is:
            // "Master Nilai (current year)" should show carried-over values if explicit Master is empty.
            // So we actually want getFinalStateForYear(CURRENT_YEAR, ...) BUT WITHOUT revisions of current year?
            // No, strictly speacking "Master Nilai" column usually represents the STARTING point of the year.
            // Which is: (Final of Prev Year) + (Explicit Master Edits of Current Year).
            // Revisions of Current Year are separate columns.

            // So: 
            // 1. Base = Final of Prev Year
            // 2. Overlay = Explicit Master of Current Year

            $prevYearNum = $activeYear->tahun - 1;
            $prevYearNum = $activeYear->tahun - 1;
            $baseState = $this->getFinalStateForYear($prevYearNum, $selectedKabupatenId);
            $prevYearValues = $baseState;

            // 3. Fetch Actual Master Records for Current Year
            $actualMaster = RhMasterNilai::where('rh_tahun_id', $selectedYearId)
                ->where('kabupaten_id', $selectedKabupatenId)
                ->get()
                ->keyBy('komoditas_id');

            // 4. Merge
            $finalMasterData = [];

            // We need to iterate all possible commodities to ensure we show full list state
            $allKomoditasIds = \App\Models\Komoditas::pluck('id')->toArray();

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

        // 2. Outlier Analysis (Global Context for Selected Year)
        $outliers = [];
        if ($activeYear) {
            $outliers = $this->calculateOutliers($selectedYearId, $kabupatens, $allKomoditas, $revisions);
        }

        return view('price-range.index', compact(
            'years',
            'kabupatens',
            'selectedYearId',
            'selectedKabupatenId',
            'activeYear',
            'categories',
            'masterNilai',
            'prevYearValues',
            'revisions',
            'revisionDetails',
            'outliers'
        ));
    }

    private function calculateOutliers($yearId, $kabupatens, $allKomoditas, $revisions)
    {
        $results = [];
        $thresholdPercent = 0.25; // 25% deviation

        // Initial State: Load all Master Data
        $dataState = []; // [kab_id][kom_id] => ['min' => val, 'max' => val]

        $masterData = RhMasterNilai::where('rh_tahun_id', $yearId)->get();

        foreach ($masterData as $md) {
            $dataState[$md->kabupaten_id][$md->komoditas_id] = [
                'min' => $md->min_nilai,
                'max' => $md->max_nilai
            ];
        }

        // --- Analyze Master Period ---
        $results['Master Data'] = $this->analyzeSnapshot($dataState, $kabupatens, $allKomoditas, $thresholdPercent);

        // --- Analyze Each Revision ---
        foreach ($revisions as $rev) {
            // Update state with revision details
            $details = RhPerubahanDetail::where('rh_perubahan_header_id', $rev->id)->get();

            foreach ($details as $dt) {
                // If value is edited (not null), update state
                // Note: Logic in view assumes if null, carry forward.
                // But in DB, a record exists only if touched? 
                // Wait, if a record exists in DB with null, it means explicitly set to null? 
                // Usually edit forms submit values. Assuming non-null replaces.

                if (!isset($dataState[$dt->kabupaten_id]))
                    $dataState[$dt->kabupaten_id] = [];

                // Update Min
                if ($dt->min_edit !== null) {
                    $dataState[$dt->kabupaten_id][$dt->komoditas_id]['min'] = $dt->min_edit;
                }
                // If record exists but val is null, we assume it keeps previous value (Carry Forward), so DO NOTHING.

                // Update Max
                if ($dt->max_edit !== null) {
                    $dataState[$dt->kabupaten_id][$dt->komoditas_id]['max'] = $dt->max_edit;
                }
            }

            $results[strtoupper($rev->label)] = $this->analyzeSnapshot($dataState, $kabupatens, $allKomoditas, $thresholdPercent);
        }

        return $results;
    }

    private function analyzeSnapshot($dataState, $kabupatens, $allKomoditas, $threshold)
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
                    if ($p < $avgMin * (1 - $threshold)) {
                        $itemOutliers['below'][] = [
                            'kode_kab' => $kabupatens->find($kid)->kode_kab,
                            'kab' => $kabupatens->find($kid)->nama_kabupaten,
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
                    if ($p > $avgMax * (1 + $threshold)) {
                        $itemOutliers['above'][] = [
                            'kode_kab' => $kabupatens->find($kid)->kode_kab,
                            'kab' => $kabupatens->find($kid)->nama_kabupaten,
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

    private function getFinalStateForYear($year, $kabupatenId)
    {
        if ($year < 2020)
            return []; // Safety break

        // Always start with previous year's state (Recursive)
        $baseState = $this->getFinalStateForYear($year - 1, $kabupatenId);

        $rhTahun = RhTahun::where('tahun', $year)->first();

        // If current year not defined in DB, return previous data
        if (!$rhTahun)
            return $baseState;

        // 1. Overlay Master Values
        $masterNilai = RhMasterNilai::where('rh_tahun_id', $rhTahun->id)
            ->where('kabupaten_id', $kabupatenId)
            ->get();

        foreach ($masterNilai as $m) {
            if ($m->min_nilai !== null || $m->max_nilai !== null) {
                $baseState[$m->komoditas_id] = [
                    'min' => $m->min_nilai,
                    'max' => $m->max_nilai,
                    'alasan' => $m->alasan,
                ];
            }
        }

        // 2. Apply Revisions for this year
        $revisions = RhPerubahanHeader::where('rh_tahun_id', $rhTahun->id)
            ->orderBy('tanggal_perubahan', 'asc')
            ->get();

        if ($revisions->isNotEmpty()) {
            $details = RhPerubahanDetail::whereIn('rh_perubahan_header_id', $revisions->pluck('id'))
                ->where('kabupaten_id', $kabupatenId)
                ->get()
                ->groupBy('rh_perubahan_header_id');

            foreach ($revisions as $rev) {
                if (isset($details[$rev->id])) {
                    foreach ($details[$rev->id] as $det) {
                        $komId = $det->komoditas_id;
                        if (!isset($baseState[$komId])) {
                            $baseState[$komId] = ['min' => null, 'max' => null, 'alasan' => null];
                        }
                        if ($det->min_edit !== null)
                            $baseState[$komId]['min'] = $det->min_edit;
                        if ($det->max_edit !== null)
                            $baseState[$komId]['max'] = $det->max_edit;
                        if ($det->alasan !== null)
                            $baseState[$komId]['alasan'] = $det->alasan;
                        elseif ($det->min_edit !== null || $det->max_edit !== null)
                            $baseState[$komId]['alasan'] = null;
                    }
                }
            }
        }

        return $baseState;
    }
}
