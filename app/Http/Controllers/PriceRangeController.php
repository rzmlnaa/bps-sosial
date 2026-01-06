<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RhTahun;
use App\Models\Kabupaten;
use App\Models\KategoriKomoditas;
use App\Models\RhMasterNilai;
use App\Models\RhPerubahanHeader;
use App\Models\RhPerubahanDetail;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PriceRangeController extends Controller
{
    public function index(Request $request)
    {
        $years = RhTahun::orderBy('tahun', 'desc')->get();
        $kabupatens = Kabupaten::all();

        $selectedYearId = $request->year_id ?? ($years->where('is_active', true)->first()->id ?? $years->first()->id ?? null);
        $selectedKabupatenId = $request->kabupaten_id ?? ($kabupatens->first()->id ?? null);

        $activeYear = $years->where('id', $selectedYearId)->first();

        $categories = KategoriKomoditas::with(['komoditas'])->get();

        // Fetch Master Values
        $masterNilai = collect();
        if ($selectedYearId && $selectedKabupatenId) {
            $masterNilai = RhMasterNilai::where('rh_tahun_id', $selectedYearId)
                ->where('kabupaten_id', $selectedKabupatenId)
                ->get()
                ->keyBy('komoditas_id');
        }

        // Fetch Revisions for the selected year
        $revisions = collect();
        $revisionDetails = collect();
        if ($selectedYearId && $selectedKabupatenId) {
            $revisions = RhPerubahanHeader::where('rh_tahun_id', $selectedYearId)
                ->orderBy('tanggal_perubahan', 'asc')
                ->get();

            $revisionDetails = RhPerubahanDetail::whereIn('rh_perubahan_header_id', $revisions->pluck('id'))
                ->where('kabupaten_id', $selectedKabupatenId)
                ->get()
                ->groupBy('rh_perubahan_header_id');

            // Map to komoditas_id for each revision
            $revisionDetails = $revisionDetails->map(function ($items) {
                return $items->keyBy('komoditas_id');
            });
        }

        return view('price-range.index', compact(
            'years',
            'kabupatens',
            'selectedYearId',
            'selectedKabupatenId',
            'activeYear',
            'categories',
            'masterNilai',
            'revisions',
            'revisionDetails'
        ));
    }

    public function export(Request $request)
    {
        $yearId = $request->year_id;
        $kabupatenId = $request->kabupaten_id;

        $year = RhTahun::findOrFail($yearId);
        $kabupaten = Kabupaten::findOrFail($kabupatenId);

        $categories = KategoriKomoditas::with(['komoditas'])->get();
        $masterNilai = RhMasterNilai::where('rh_tahun_id', $yearId)
            ->where('kabupaten_id', $kabupatenId)
            ->get()
            ->keyBy('komoditas_id');

        $revisions = RhPerubahanHeader::where('rh_tahun_id', $yearId)
            ->orderBy('tanggal_perubahan', 'asc')
            ->get();

        $revisionDetails = RhPerubahanDetail::whereIn('rh_perubahan_header_id', $revisions->pluck('id'))
            ->where('kabupaten_id', $kabupatenId)
            ->get()
            ->groupBy('rh_perubahan_header_id');

        $revisionDetails = $revisionDetails->map(function ($items) {
            return $items->keyBy('komoditas_id');
        });

        $fileName = "Rentang_Harga_{$kabupaten->nama_kabupaten}_{$year->tahun}.csv";

        $response = new StreamedResponse(function () use ($categories, $masterNilai, $revisions, $revisionDetails, $year) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM for Excel
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Header row 1
            $header1 = ['KATEGORI', 'KOMODITAS', 'SATUAN', 'MIN MASTER', 'MAX MASTER', 'ALASAN MASTER'];
            foreach ($revisions as $rev) {
                $header1[] = "MIN " . strtoupper($rev->label);
                $header1[] = "MAX " . strtoupper($rev->label);
                $header1[] = "ALASAN " . strtoupper($rev->label);
            }
            fputcsv($handle, $header1);

            foreach ($categories as $category) {
                foreach ($category->komoditas as $komo) {
                    $master = $masterNilai->get($komo->id);
                    $row = [
                        $category->nama_kategori,
                        $komo->nama_komoditas,
                        $komo->satuan ?? 'Kg',
                        $master ? $master->min_nilai : '-',
                        $master ? $master->max_nilai : '-',
                        $master ? $master->alasan : '-',
                    ];

                    foreach ($revisions as $rev) {
                        $revData = isset($revisionDetails[$rev->id]) ? $revisionDetails[$rev->id]->get($komo->id) : null;
                        $row[] = $revData ? $revData->min_edit : '-';
                        $row[] = $revData ? $revData->max_edit : '-';
                        $row[] = $revData ? $revData->alasan : '-';
                    }
                    fputcsv($handle, $row);
                }
            }
            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $fileName . '"');

        return $response;
    }
}
