<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kabupaten;
use App\Models\VariabelKemiskinan;
use App\Models\NilaiKemiskinan;

class PovertyViewController extends Controller
{
    public function index(Request $request)
    {
        $kabupatens = Kabupaten::withoutIndonesia()->orderBy('kode_kab', 'asc')->get();
        $variabels = VariabelKemiskinan::all();
        $availableYears = VariabelKemiskinan::distinct()->orderBy('tahun', 'desc')->pluck('tahun');
        $latestYear = VariabelKemiskinan::max('tahun') ?? date('Y');
        
        if (!$request->has('tahun')) {
            $request->merge(['tahun' => 'all']);
        }
        $selectedTahun = $request->get('tahun');

        if ($selectedTahun == 'all' || $selectedTahun == null) {
            $mainVar = $variabels->first();
        } else {
            $mainVar = VariabelKemiskinan::where('tahun', $selectedTahun)->latest()->first();
        }
        $nilaiKemiskinan = NilaiKemiskinan::all()->keyBy('kabupaten_id');

        $kabupatenData = $kabupatens->map(function ($kab) use ($nilaiKemiskinan) {
            $nilai = $nilaiKemiskinan->get($kab->id);

            if (!$nilai) {
                return null;
            }

            return [
                'id' => $kab->id,
                'kode_kab' => $kab->kode_kab,
                'name' => $kab->nama_kabupaten,
                'nilai' => $nilai
            ];
        })->filter()->values();

        $provAvg = $kabupatenData->whereNotNull('nilai')->avg(fn($item) => $item['nilai']->avg_nilai);
        $provCount = $kabupatenData->sum(fn($item) => $item['nilai']->count);
        $provGK = $kabupatenData->avg(fn($item) => $item['nilai']->gk);

        $bulanNama = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember'
        ];
        $latestLabel = $mainVar ? (($mainVar->bulan ? $bulanNama[$mainVar->bulan] . ' ' : '') . ($mainVar->tahun ?? '')) : '';

        return view('poverty.index', compact('kabupatens', 'variabels', 'kabupatenData', 'mainVar', 'provAvg', 'provCount', 'provGK', 'latestLabel', 'selectedTahun', 'availableYears', 'bulanNama'));
    }

    public function input()
    {
        $kabupatens = Kabupaten::withoutIndonesia()->with(['userAdd', 'userUpdate'])->orderBy('kode_kab', 'asc')->get();
        $variabels = VariabelKemiskinan::with('userAdd')->orderBy('tahun', 'desc')->orderBy('bulan', 'desc')->get();
        
        return view('poverty.input', compact('kabupatens', 'variabels'));
    }
}
