<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Models\IndikatorBidang;
use App\Models\PeriodeIndikator;
use App\Models\Kabupaten;

class IndikatorMakroExport implements WithMultipleSheets
{
    protected $bidangIds;
    protected $indikatorIds;
    protected $tahunNames;
    protected $kabupatenIds;

    public function __construct($bidangIds, $indikatorIds, $tahunNames, $kabupatenIds)
    {
        $this->bidangIds = $bidangIds;
        $this->indikatorIds = $indikatorIds;
        $this->tahunNames = $tahunNames;
        $this->kabupatenIds = $kabupatenIds;
    }

    public function sheets(): array
    {
        $sheets = [];

        // Fetch periodes
        $periodes = PeriodeIndikator::whereIn('tahun', $this->tahunNames)
            ->orderBy('tahun', 'asc')
            ->get();

        // Fetch kabupatens
        $kabupatens = Kabupaten::whereIn('id', $this->kabupatenIds)
            ->orderByRaw("
                CASE 
                    WHEN kode_kab = '6100' THEN 1 
                    WHEN kode_kab = '1' OR LOWER(nama_kabupaten) = 'indonesia' THEN 2 
                    ELSE 0 
                END ASC, 
                kode_kab ASC
            ")->get();

        // Fetch selected bidangs that have selected indicators
        $bidangs = IndikatorBidang::whereIn('id', $this->bidangIds)
            ->whereHas('indikatorMakros', function ($q) {
                $q->whereIn('id', $this->indikatorIds);
            })
            ->orderBy('urutan', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();

        foreach ($bidangs as $idx => $bidang) {
            $sheets[] = new IndikatorMakroSheet($bidang, $idx + 1, $this->indikatorIds, $periodes, $kabupatens);
        }

        return $sheets;
    }
}
