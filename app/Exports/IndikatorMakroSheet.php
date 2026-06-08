<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use App\Models\IndikatorMakro;
use App\Models\IndikatorDimensi;
use App\Models\NilaiIndikatorMakro;

class IndikatorMakroSheet implements FromView, WithTitle, ShouldAutoSize, WithStyles
{
    protected $bidang;
    protected $sheetIndex;
    protected $selectedIndikatorIds;
    protected $periodes;
    protected $kabupatens;

    public function __construct($bidang, $sheetIndex, $selectedIndikatorIds, $periodes, $kabupatens)
    {
        $this->bidang = $bidang;
        $this->sheetIndex = $sheetIndex;
        $this->selectedIndikatorIds = $selectedIndikatorIds;
        $this->periodes = $periodes;
        $this->kabupatens = $kabupatens;
    }

    public function view(): View
    {
        // Fetch selected indicators belonging to this Bidang, eager loading dimensions with their dimensi names
        $indicators = IndikatorMakro::where('indikator_bidang_id', $this->bidang->id)
            ->whereIn('id', $this->selectedIndikatorIds)
            ->with([
                'indikatorDimensis' => function ($q) {
                    $q->with(['dimensi'])
                        ->orderBy('urutan', 'asc');
                }
            ])
            ->orderBy('urutan', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();

        // Collect all dimension IDs across all selected indicators in this sheet
        $allDimensionIds = $indicators->flatMap(function ($indicator) {
            return $indicator->indikatorDimensis->pluck('id');
        })->toArray();

        // Fetch all values for all dimensions in a single query to prevent N+1 query issue
        $valuesMap = [];
        if (!empty($allDimensionIds)) {
            $rawValues = NilaiIndikatorMakro::whereIn('indikator_dimensi_id', $allDimensionIds)
                ->whereIn('periode_indikator_id', $this->periodes->pluck('id'))
                ->whereIn('kabupaten_id', $this->kabupatens->pluck('id'))
                ->get();
            foreach ($rawValues as $v) {
                $valuesMap[$v->indikator_dimensi_id][$v->kabupaten_id][$v->periode_indikator_id] = $v->nilai;
            }
        }

        $indicatorData = [];
        foreach ($indicators as $indicator) {
            $dimensions = $indicator->indikatorDimensis;
            $isNoneOnly = ($dimensions->count() === 1 && strtolower(trim($dimensions->first()->dimensi->nama_dimensi ?? '')) === 'none');

            // Map values in-memory from valuesMap
            $values = [];
            foreach ($dimensions as $d) {
                foreach ($this->kabupatens as $kab) {
                    foreach ($this->periodes as $p) {
                        if (isset($valuesMap[$d->id][$kab->id][$p->id])) {
                            $values[$kab->id][$p->id][$d->id] = $valuesMap[$d->id][$kab->id][$p->id];
                        }
                    }
                }
            }

            $indicatorData[] = [
                'indicator' => $indicator,
                'dimensions' => $dimensions,
                'isNoneOnly' => $isNoneOnly,
                'values' => $values
            ];
        }

        return view('exports.indikator-makro-sheet', [
            'bidang' => $this->bidang,
            'sheetIndex' => $this->sheetIndex,
            'periodes' => $this->periodes,
            'kabupatens' => $this->kabupatens,
            'indicatorData' => $indicatorData
        ]);
    }

    private function getRomanNumeral(int $integer): string
    {
        $table = [
            'M' => 1000, 'CM' => 900, 'D' => 500, 'CD' => 400,
            'C' => 100, 'XC' => 90, 'L' => 50, 'XL' => 40,
            'X' => 10, 'IX' => 9, 'V' => 5, 'IV' => 4, 'I' => 1
        ];
        $return = '';
        while ($integer > 0) {
            foreach ($table as $rom => $arb) {
                if ($integer >= $arb) {
                    $integer -= $arb;
                    $return .= $rom;
                    break;
                }
            }
        }
        return $return;
    }

    public function title(): string
    {
        $roman = $this->getRomanNumeral($this->sheetIndex);
        $title = $roman . '. ' . $this->bidang->nama_bidang;
        $title = str_replace(['*', ':', '?', '/', '\\', '[', ']'], '', $title);
        $title = str_replace('"', '', $title);
        return strtoupper(substr($title, 0, 30));
    }

    public function styles(Worksheet $sheet)
    {
        return [];
    }
}
