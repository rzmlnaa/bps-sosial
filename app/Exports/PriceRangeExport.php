<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Models\Kabupaten;

class PriceRangeExport implements WithMultipleSheets
{
    protected $yearId;

    public function __construct($yearId)
    {
        $this->yearId = $yearId;
    }

    public function sheets(): array
    {
        $sheets = [];
        $kabupatens = Kabupaten::all();

        foreach ($kabupatens as $kab) {
            $sheets[] = new KabupatenPriceSheet($this->yearId, $kab->id);
        }

        return $sheets;
    }
}
