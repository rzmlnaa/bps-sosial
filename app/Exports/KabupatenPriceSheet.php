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
use App\Models\RhMasterNilai;
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
        $masterNilai = RhMasterNilai::where('rh_tahun_id', $this->yearId)
            ->where('kabupaten_id', $kabupaten->id)
            ->get()
            ->keyBy('komoditas_id');

        $revisions = RhPerubahanHeader::where('rh_tahun_id', $this->yearId)
            ->orderBy('tanggal_perubahan', 'asc')
            ->get();

        $revisionDetails = RhPerubahanDetail::whereIn('rh_perubahan_header_id', $revisions->pluck('id'))
            ->where('kabupaten_id', $kabupaten->id)
            ->get()
            ->groupBy('rh_perubahan_header_id');

        $revisionDetails = $revisionDetails->map(function ($items) {
            return $items->keyBy('komoditas_id');
        });

        return view('exports.price-range-sheet', [
            'year' => $year,
            'kabupaten' => $kabupaten,
            'categories' => $categories,
            'masterNilai' => $masterNilai,
            'revisions' => $revisions,
            'revisionDetails' => $revisionDetails
        ]);
    }

    public function title(): string
    {
        $title = "{$this->kabupaten->kode_kab} - {$this->kabupaten->nama_kabupaten}";
        return substr($title, 0, 31); // Excel sheet limit is 31 characters
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]], // Row 1 bold
            2 => ['font' => ['bold' => true]], // Row 2 bold
        ];
    }
}
