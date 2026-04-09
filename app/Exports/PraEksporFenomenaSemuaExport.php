<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class PraEksporFenomenaSemuaExport implements WithMultipleSheets
{
    use Exportable;

    protected $tahun;
    protected $bulan;
    protected $dataPerKabupaten;

    public function __construct($tahun, $bulan, $dataPerKabupaten)
    {
        $this->tahun = $tahun;
        $this->bulan = $bulan;
        $this->dataPerKabupaten = $dataPerKabupaten;
    }

    public function sheets(): array
    {
        $sheets = [];

        foreach ($this->dataPerKabupaten as $kab_id => $data) {
            $sheets[] = new PraEksporFenomenaExport(
                $data['kabupaten'],
                $this->tahun,
                $this->bulan,
                $data['groupedData'],
                $data['unmatchedData']
            );
        }

        return $sheets;
    }
}
