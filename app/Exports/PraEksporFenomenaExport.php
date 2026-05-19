<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use App\Models\Kabupaten;

class PraEksporFenomenaExport implements FromView, WithTitle, WithColumnWidths, WithStyles
{
    protected $kabupaten;
    protected $tahun;
    protected $bulan;
    protected $groupedData;
    protected $unmatchedData;
    protected $baseYear;

    public function __construct($kabupaten, $tahun, $bulan, $groupedData, $unmatchedData)
    {
        $this->kabupaten = $kabupaten;
        $this->tahun = $tahun;
        $this->bulan = $bulan;
        $this->groupedData = $groupedData;
        $this->unmatchedData = $unmatchedData;
        $this->baseYear = $tahun === 'all' ? date('Y') : (int) $tahun;
    }

    public function view(): View
    {
        return view('pra-ekspor.excel', [
            'kabupaten' => $this->kabupaten,
            'tahun' => $this->tahun,
            'bulan' => $this->bulan,
            'groupedData' => $this->groupedData,
            'unmatchedData' => $this->unmatchedData,
            'baseYear' => $this->baseYear,
        ]);
    }

    public function title(): string
    {
        $sheetName = $this->kabupaten->kode_kab . ' - ' . $this->kabupaten->nama_kabupaten;
        return substr($sheetName, 0, 31);
    }

    public function columnWidths(): array
    {
        return [
            'A' => 6,
            'B' => 45,
            'C' => 12,
            'D' => 12,
            'E' => 12,
            'F' => 65,
            'G' => 65,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            'A:G' => [
                'alignment' => [
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP,
                ],
            ],
            'B' => [
                'alignment' => [
                    'wrapText' => true,
                ],
            ],
            'F' => [
                'alignment' => [
                    'wrapText' => true,
                ],
            ],
            'G' => [
                'alignment' => [
                    'wrapText' => true,
                ],
            ],
        ];
    }
}
