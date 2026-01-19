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
        $kabupatens = Kabupaten::orderBy('kode_kab', 'asc')->get();

        foreach ($kabupatens as $kab) {
            // The instruction implies that KabupatenPriceSheet should accept the Kabupaten model
            // and handle the title formatting internally.
            // The provided "Code Edit" snippet for `title()` method seems to be intended for `KabupatenPriceSheet`.
            // This file (PriceRangeExport) already passes the $kab model to the sheet constructor.
            $sheets[] = new KabupatenPriceSheet($this->yearId, $kab);
        }

        return $sheets;
    }
}
