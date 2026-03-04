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
    protected $year;
    protected $categories;
    protected $prevYearValues;
    protected $masterDetails;
    protected $revisions;
    protected $revisionDetails;

    public function __construct(
        $yearId,
        $kabupaten,
        $year = null,
        $categories = null,
        $prevYearValues = null,
        $masterDetails = null,
        $revisions = null,
        $revisionDetails = null
    ) {
        $this->yearId = $yearId;
        $this->kabupaten = $kabupaten;
        $this->year = $year;
        $this->categories = $categories;
        $this->prevYearValues = $prevYearValues;
        $this->masterDetails = $masterDetails;
        $this->revisions = $revisions;
        $this->revisionDetails = $revisionDetails;
    }

    public function view(): View
    {
        $year = $this->year ?? RhTahun::findOrFail($this->yearId);
        $kabupaten = $this->kabupaten;

        $categories = $this->categories ?? KategoriKomoditas::with([
            'komoditas' => function ($query) {
                $query->orderBy('order_number', 'asc');
            }
        ])->get();

        // --- 1. Calculate Effective Master State ---
        // A. Baseline (Prev Year Final)
        $prevYearValues = $this->prevYearValues;
        if ($prevYearValues === null) {
            // Fallback for safety
            $prevYearNum = $year->tahun - 1;
            $prevYearValues = $this->getFinalStateForYear($prevYearNum, $kabupaten->id);
        }

        // B. Fetch Actual Master Records for Current Year
        $actualMaster = $this->masterDetails;
        if ($actualMaster === null) {
            $actualMaster = RhPerubahanDetail::where('rh_tahun_id', $this->yearId)
                ->where('kabupaten_id', $kabupaten->id)
                ->whereNull('rh_perubahan_header_id')
                ->get();
        }
        $actualMaster = collect($actualMaster)->keyBy('komoditas_id');

        // C. Merge (Effective Master = Explicit Master OR Previous Final)
        $finalMasterData = [];
        $allKomoditasIds = \App\Models\Komoditas::orderBy('order_number', 'asc')->pluck('id')->toArray();

        foreach ($allKomoditasIds as $komId) {
            $m = $actualMaster->get($komId);
            $b = $prevYearValues[$komId] ?? null;

            $val = new \stdClass();
            $val->min_nilai = null;
            $val->max_nilai = null;
            $val->alasan = null;

            if ($m && ($m->min_edit !== null || $m->max_edit !== null)) {
                $val->min_nilai = $m->min_edit;
                $val->max_nilai = $m->max_edit;
                $val->alasan = $m->alasan;
            } elseif ($b) {
                $val->min_nilai = $b['min'] ?? null;
                $val->max_nilai = $b['max'] ?? null;
                $val->alasan = $b['alasan'] ?? null;
            }
            $finalMasterData[$komId] = $val;
        }
        $masterNilai = collect($finalMasterData);

        // --- 2. Fetch Revisions ---
        $revisions = $this->revisions ?? RhPerubahanHeader::where('rh_tahun_id', $this->yearId)
            ->orderBy('tanggal_perubahan', 'asc')
            ->get();

        $revisionDetails = $this->revisionDetails;
        if ($revisionDetails === null && $revisions->isNotEmpty()) {
            $revisionDetails = RhPerubahanDetail::whereIn('rh_perubahan_header_id', $revisions->pluck('id'))
                ->where('kabupaten_id', $kabupaten->id)
                ->get()
                ->groupBy('rh_perubahan_header_id');
        }

        if ($revisionDetails instanceof \Illuminate\Support\Collection) {
            $revisionDetails = $revisionDetails->map(function ($items) {
                return collect($items)->keyBy('komoditas_id');
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
        return [];
    }
}
