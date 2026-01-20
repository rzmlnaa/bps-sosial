<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use App\Models\RhTahun;
use App\Models\Kabupaten;
use App\Models\KategoriKomoditas;

use App\Models\RhPerubahanHeader;
use App\Models\RhPerubahanDetail;

class KabupatenPriceSheet implements FromView, WithTitle, ShouldAutoSize, WithStyles
{
    protected $yearId;
    protected $kabupaten;

    public function __construct($yearId, $kabupaten)
    {
        $this->yearId = $yearId;
        $this->kabupaten = $kabupaten;
    }

    public function view(): View
    {
        $year = RhTahun::findOrFail($this->yearId);
        $kabupaten = $this->kabupaten;

        $categories = KategoriKomoditas::with(['komoditas'])->get();

        // --- 1. Calculate Effective Master State (Recursive) ---
        // A. Get Final State of PREVIOUS Year
        $prevYearNum = $year->tahun - 1;
        $baseState = $this->getFinalStateForYear($prevYearNum, $kabupaten->id);
        $prevYearValues = $baseState; // Baseline for highlighting

        // B. Fetch Actual Master Records for Current Year
        $actualMaster = RhPerubahanDetail::where('rh_tahun_id', $this->yearId)
            ->where('kabupaten_id', $kabupaten->id)
            ->whereNull('rh_perubahan_header_id')
            ->select('*', 'min_edit as min_nilai', 'max_edit as max_nilai')
            ->get()
            ->keyBy('komoditas_id');

        // C. Merge (Effective Master = Explicit Master OR Previous Final)
        $finalMasterData = [];
        $allKomoditasIds = \App\Models\Komoditas::pluck('id')->toArray();

        // Helper to check if master has data
        foreach ($allKomoditasIds as $komId) {
            $m = $actualMaster->get($komId);
            $b = $baseState[$komId] ?? null;

            $val = new \stdClass();
            $val->min_nilai = null;
            $val->max_nilai = null;
            $val->alasan = null;

            if ($m && ($m->min_nilai !== null || $m->max_nilai !== null)) {
                $val->min_nilai = $m->min_nilai;
                $val->max_nilai = $m->max_nilai;
                $val->alasan = $m->alasan;
            } elseif ($b) {
                $val->min_nilai = $b['min'];
                $val->max_nilai = $b['max'];
                $val->alasan = $b['alasan'];
            }
            $finalMasterData[$komId] = $val;
        }
        $masterNilai = collect($finalMasterData);

        // --- 2. Fetch Revisions ---
        $revisions = RhPerubahanHeader::where('rh_tahun_id', $this->yearId)
            ->orderBy('tanggal_perubahan', 'asc')
            ->get();

        $revisionDetails = collect();
        if ($revisions->isNotEmpty()) {
            $revisionDetails = RhPerubahanDetail::whereIn('rh_perubahan_header_id', $revisions->pluck('id'))
                ->where('kabupaten_id', $kabupaten->id)
                ->get()
                ->groupBy('rh_perubahan_header_id');

            $revisionDetails = $revisionDetails->map(function ($items) {
                return $items->keyBy('komoditas_id');
            });
        }

        return view('exports.price-range-sheet', [
            'year' => $year,
            'kabupaten' => $kabupaten,
            'categories' => $categories,
            'masterNilai' => $masterNilai,
            'prevYearValues' => $prevYearValues,
            'revisions' => $revisions,
            'revisionDetails' => $revisionDetails
        ]);
    }

    public function title(): string
    {
        $title = "{$this->kabupaten->kode_kab} - {$this->kabupaten->nama_kabupaten}";
        return substr($title, 0, 31);
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
            2 => ['font' => ['bold' => true]],
        ];
    }

    private function getFinalStateForYear($year, $kabupatenId)
    {
        if ($year < 2020)
            return [];

        $baseState = $this->getFinalStateForYear($year - 1, $kabupatenId);

        $rhTahun = RhTahun::where('tahun', $year)->first();
        if (!$rhTahun)
            return $baseState;

        $masterNilai = RhPerubahanDetail::where('rh_tahun_id', $rhTahun->id)
            ->whereNull('rh_perubahan_header_id')
            ->where('kabupaten_id', $kabupatenId)
            ->get();

        foreach ($masterNilai as $m) {
            // RhPerubahanDetail uses min_edit/max_edit
            if ($m->min_edit !== null || $m->max_edit !== null) {
                $baseState[$m->komoditas_id] = [
                    'min' => $m->min_edit,
                    'max' => $m->max_edit,
                    'alasan' => $m->alasan,
                ];
            }
        }

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
                        elseif ($det->min_edit !== null || $det->max_edit !== null) {
                            $baseState[$komId]['alasan'] = null;
                        }
                    }
                }
            }
        }
        return $baseState;
    }
}
