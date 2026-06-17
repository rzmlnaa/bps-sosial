<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Models\Kabupaten;
use App\Models\RhTahun;
use App\Models\KategoriKomoditas;
use App\Models\RhPerubahanHeader;
use App\Models\RhPerubahanDetail;
use App\Traits\HandlesRhStates;

class PriceRangeExport implements WithMultipleSheets
{
    use HandlesRhStates;
    protected $yearId;

    public function __construct($yearId)
    {
        $this->yearId = $yearId;
    }

    public function sheets(): array
    {
        $year = RhTahun::findOrFail($this->yearId);
        $kabupatens = Kabupaten::withoutIndonesia()->orderBy('kode_kab', 'asc')->where('kode_kab', '!=', '6100')->get();
        $kabIds = $kabupatens->pluck('id')->toArray();

        // 1. Bulk fetch categories and commodities
        $categories = KategoriKomoditas::with([
            'komoditas' => function ($query) {
                $query->orderBy('order_number', 'asc');
            }
        ])->get();

        // 2. Bulk fetch historical states (Min, Max, Alasan up to Prev Year)
        $historicalStates = $this->getBulkStates($year->tahun - 1, $kabIds);

        // 3. Bulk fetch Current Year Master Edits (header_id IS NULL)
        $currentMasterDetails = RhPerubahanDetail::where('rh_tahun_id', $this->yearId)
            ->whereNull('rh_perubahan_header_id')
            ->whereIn('kabupaten_id', $kabIds)
            ->get()
            ->groupBy('kabupaten_id');

        // 4. Bulk fetch Revisions
        $revisions = RhPerubahanHeader::where('rh_tahun_id', $this->yearId)
            ->orderBy('tanggal_perubahan', 'asc')
            ->get();

        $revisionDetails = collect();
        if ($revisions->isNotEmpty()) {
            $revisionDetails = RhPerubahanDetail::whereIn('rh_perubahan_header_id', $revisions->pluck('id'))
                ->whereIn('kabupaten_id', $kabIds)
                ->get()
                ->groupBy(['kabupaten_id', 'rh_perubahan_header_id']);
        }

        $sheets = [];
        foreach ($kabupatens as $kab) {
            $kabId = $kab->id;

            // Prepare data for this specific kabupaten
            $prevYearStates = $historicalStates[$kabId] ?? [];
            $masterDetails = $currentMasterDetails->get($kabId) ?? collect();
            $revDetailsForKab = $revisionDetails->get($kabId) ?? collect();

            $sheets[] = new KabupatenPriceSheet(
                $this->yearId,
                $kab,
                $year,
                $categories,
                $prevYearStates,
                $masterDetails,
                $revisions,
                $revDetailsForKab
            );
        }

        return $sheets;
    }
}
