<?php

namespace App\Exports;

use App\Models\DescanPeserta;
use App\Models\DescanKegiatan;
use App\Models\DescanJenisOutput;
use App\Models\DescanJenisBuktiDukung;
use App\Models\DescanJenisBuktiKegiatan;
use App\Models\DescanPeriode;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DescanProgressExport implements FromView, ShouldAutoSize, WithStyles
{
    protected $periodeId;

    public function __construct($periodeId)
    {
        $this->periodeId = $periodeId;
    }

    public function view(): View
    {
        $periode = DescanPeriode::find($this->periodeId);
        
        $pesertas = DescanPeserta::with([
            'kabupaten',
            'kecamatan',
            'desa',
            'progresses' => function($q) {
                $q->where('status', 'disetujui')->with('buktis.jenisBukti');
            },
            'outputs' => function($q) {
                $q->where('status', 'disetujui');
            },
            'buktiDukungs' => function($q) {
                $q->where('status', 'disetujui')->with('jenisBukti');
            },
            'penilaian'
        ])
        ->where('periode_id', $this->periodeId)
        ->join('tb_kabupaten', 'descan_peserta.kabupaten_id', '=', 'tb_kabupaten.id')
        ->join('tb_kecamatan', 'descan_peserta.kecamatan_id', '=', 'tb_kecamatan.id')
        ->orderBy('tb_kabupaten.kode_kab')
        ->orderBy('tb_kecamatan.nama_kecamatan')
        ->select('descan_peserta.*')
        ->get();

        $kegiatans = DescanKegiatan::where('is_active', true)->orderBy('urutan', 'asc')->get();
        $jenisOutputs = DescanJenisOutput::orderBy('id', 'asc')->get();
        $jenisDukungs = DescanJenisBuktiDukung::orderBy('id', 'asc')->get();
        $topBuktiKegiatan = DescanJenisBuktiKegiatan::orderBy('id', 'asc')->pluck('nama_bukti')->take(3)->toArray();

        return view('exports.descan_progress', [
            'groupedPesertas' => $pesertas->groupBy('kabupaten_id'),
            'kegiatans' => $kegiatans,
            'jenisOutputs' => $jenisOutputs,
            'jenisDukungs' => $jenisDukungs,
            'periode' => $periode,
            'topBuktiKegiatan' => $topBuktiKegiatan
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        $sheet->getStyle('A1:' . $highestColumn . '2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A1:' . $highestColumn . '2')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        
        // Wrap text and middle align for all data cells
        $sheet->getStyle('A1:' . $highestColumn . $highestRow)->getAlignment()->setWrapText(true);
        $sheet->getStyle('A1:' . $highestColumn . $highestRow)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

        return [
            1 => ['font' => ['bold' => true]],
            2 => ['font' => ['bold' => true]],
        ];
    }
}
