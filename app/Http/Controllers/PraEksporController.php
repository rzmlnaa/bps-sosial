<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PraEksporController extends Controller
{
    public function index(Request $request)
    {
        // Get all kabupatens for the dropdown
        $kabupatens = \App\Models\Kabupaten::orderBy('kode_kab', 'asc')->get();
        $indikators = \App\Models\Indikator::where('kelompok', 'utama')->orderBy('nama', 'asc')->get();

        $selectedKabupatenId = $request->input('kabupaten_id');
        $selectedIndikatorId = $request->input('indikator_id');
        $tahun = $request->input('tahun', date('Y'));
        $bulan = $request->input('bulan', date('n'));

        $fenomenas = null;
        $selectedIds = [];

        if ($selectedKabupatenId) {
            $kabupaten = \App\Models\Kabupaten::find($selectedKabupatenId);
            if ($kabupaten) {
                // Extract keyword: remove 'kab.' 'kota' 'kabupaten' and trim
                $kataKunci = str_ireplace(['kab.', 'kabupaten', 'kota', 'provinsi'], '', strtolower($kabupaten->nama_kabupaten));
                $kataKunci = trim($kataKunci);

                $allKabupatens = \App\Models\Kabupaten::pluck('nama_kabupaten', 'id')->toArray();
                $otherKeywords = [];
                foreach ($allKabupatens as $id => $nama) {
                    if ($id == $selectedKabupatenId)
                        continue;
                    $kw = trim(str_ireplace(['kab.', 'kabupaten', 'kota', 'provinsi'], '', strtolower($nama)));
                    if (!empty($kw) && $kw !== $kataKunci) {
                        $otherKeywords[] = $kw;
                    }
                }

                // Efficient Eager Loading 
                $query = \App\Models\Fenomena::with(['sumberBerita', 'creator.kabupaten', 'indikators'])
                    ->where(function ($q) use ($kataKunci) {
                        $q->where('judul', 'LIKE', '%' . $kataKunci . '%')
                            ->orWhere('penjelasan', 'LIKE', '%' . $kataKunci . '%');
                    });

                $searchField = "LOWER(CONCAT(IFNULL(judul, ''), ' ', IFNULL(penjelasan, '')))";
                $targetLen = strlen($kataKunci);

                if ($targetLen > 0) {
                    foreach (array_unique($otherKeywords) as $otherKw) {
                        $otherLen = strlen($otherKw);
                        if ($otherLen > 0) {
                            $query->whereRaw("((LENGTH($searchField) - LENGTH(REPLACE($searchField, ?, ''))) / ?) <= ((LENGTH($searchField) - LENGTH(REPLACE($searchField, ?, ''))) / ?)", [
                                $otherKw,
                                $otherLen,
                                $kataKunci,
                                $targetLen
                            ]);
                        }
                    }

                    $query->orderByRaw("((LENGTH($searchField) - LENGTH(REPLACE($searchField, ?, ''))) / ?) DESC", [
                        $kataKunci,
                        $targetLen
                    ]);
                }

                if ($tahun !== 'all') {
                    $query->where('tahun', $tahun);
                }

                if ($bulan !== 'all') {
                    $query->where('bulan', $bulan);
                }

                if ($selectedIndikatorId) {
                    $query->whereHas('indikators', function ($qInd) use ($selectedIndikatorId) {
                        $qInd->where('indikators.kode', $selectedIndikatorId)
                            ->orWhere('indikators.id', $selectedIndikatorId);
                    });
                }

                $fenomenas = $query->latest('tanggal_berita')->paginate(50);

                // Append query params to pagination
                $fenomenas->appends($request->all());

                // Get currently selected items for this target
                $selectionQuery = \App\Models\PraEksporFenomenaSelection::where('kabupaten_id', $selectedKabupatenId);

                if ($tahun !== 'all') {
                    $selectionQuery->where('tahun', $tahun);
                }

                if ($bulan !== 'all') {
                    $selectionQuery->where('bulan', $bulan);
                }

                $selectedIds = $selectionQuery->pluck('fenomena_id')->toArray();
            }
        }

        return view('pra-ekspor.index', compact('kabupatens', 'indikators', 'fenomenas', 'selectedKabupatenId', 'selectedIndikatorId', 'tahun', 'bulan', 'selectedIds'));
    }

    public function toggleSelection(Request $request)
    {
        $request->validate([
            'kabupaten_id' => 'required|exists:tb_kabupaten,id',
            'fenomena_id' => 'required|exists:fenomenas,id',
            'tahun' => 'required',
            'bulan' => 'required',
            'is_selected' => 'required|boolean'
        ]);

        $tahunToSave = $request->tahun;
        $bulanToSave = $request->bulan;

        if ($tahunToSave === 'all' || $bulanToSave === 'all') {
            $fen = \App\Models\Fenomena::find($request->fenomena_id);
            if ($fen) {
                $carbon = \Carbon\Carbon::parse($fen->tanggal_berita);
                $tahunToSave = $tahunToSave === 'all' ? $carbon->year : $tahunToSave;
                $bulanToSave = $bulanToSave === 'all' ? $carbon->month : $bulanToSave;
            }
        }

        if ($request->is_selected) {
            // Add to selection
            \App\Models\PraEksporFenomenaSelection::updateOrCreate([
                'kabupaten_id' => $request->kabupaten_id,
                'fenomena_id' => $request->fenomena_id,
            ], [
                'tahun' => $tahunToSave,
                'bulan' => $bulanToSave,
                'user_id' => \Illuminate\Support\Facades\Auth::id(),
                'status' => 'draft'
            ]);
            return response()->json(['success' => true, 'message' => 'Fenomena selected']);
        } else {
            // Remove from selection
            \App\Models\PraEksporFenomenaSelection::where([
                'kabupaten_id' => $request->kabupaten_id,
                'fenomena_id' => $request->fenomena_id,
            ])->delete();
            return response()->json(['success' => true, 'message' => 'Fenomena unselected']);
        }
    }

    public function preview(Request $request)
    {
        $kabupaten_id = $request->input('kabupaten_id');
        $tahun = $request->input('tahun', date('Y'));
        $bulan = $request->input('bulan', date('n'));

        if (!$kabupaten_id) {
            return redirect()->route('pra-ekspor.index')->with('error', 'Pilih kabupaten terlebih dahulu.');
        }

        $kabupaten = \App\Models\Kabupaten::findOrFail($kabupaten_id);

        $selectionsQuery = \App\Models\PraEksporFenomenaSelection::with(['fenomena.indikators'])
            ->where('kabupaten_id', $kabupaten_id);

        if ($tahun !== 'all') {
            $selectionsQuery->where('tahun', $tahun);
        }

        if ($bulan !== 'all') {
            $selectionsQuery->where('bulan', $bulan);
        }

        $selections = $selectionsQuery->get();

        $groupedData = [];
        $unmatchedData = [];

        foreach ($selections as $sel) {
            $fen = $sel->fenomena;
            if (!$fen)
                continue;

            if ($fen->indikators->isEmpty()) {
                $unmatchedData[$fen->id] = $fen;
                continue;
            }

            foreach ($fen->indikators as $ind) {
                $id = $ind->id;
                if (!isset($groupedData[$id])) {
                    $groupedData[$id] = [
                        'indikator' => $ind,
                        'naik' => [],
                        'turun' => [],
                    ];
                }

                $arah = strtolower($ind->pivot->arah ?? '');
                if ($arah === 'naik') {
                    $groupedData[$id]['naik'][$fen->id] = $fen;
                } elseif ($arah === 'turun') {
                    $groupedData[$id]['turun'][$fen->id] = $fen;
                }
            }
        }

        // Urutkan Indikator sesuai abjad
        usort($groupedData, function ($a, $b) {
            return strcmp($a['indikator']->nama, $b['indikator']->nama);
        });

        return view('pra-ekspor.preview', compact('kabupaten', 'tahun', 'bulan', 'groupedData', 'unmatchedData'));
    }

    public function exportExcel(Request $request)
    {
        $kabupaten_id = $request->input('kabupaten_id');
        $tahun = $request->input('tahun', date('Y'));
        $bulan = $request->input('bulan', date('n'));

        if (!$kabupaten_id) {
            return redirect()->route('pra-ekspor.index')->with('error', 'Pilih kabupaten terlebih dahulu.');
        }

        $kabupaten = \App\Models\Kabupaten::findOrFail($kabupaten_id);

        $selectionsQuery = \App\Models\PraEksporFenomenaSelection::with(['fenomena.indikators'])
            ->where('kabupaten_id', $kabupaten_id);

        if ($tahun !== 'all') {
            $selectionsQuery->where('tahun', $tahun);
        }

        if ($bulan !== 'all') {
            $selectionsQuery->where('bulan', $bulan);
        }

        $selections = $selectionsQuery->get();

        $groupedData = [];
        $unmatchedData = [];

        foreach ($selections as $sel) {
            $fen = $sel->fenomena;
            if (!$fen)
                continue;

            if ($fen->indikators->isEmpty()) {
                $unmatchedData[$fen->id] = $fen;
                continue;
            }

            foreach ($fen->indikators as $ind) {
                $id = $ind->id;
                if (!isset($groupedData[$id])) {
                    $groupedData[$id] = [
                        'indikator' => $ind,
                        'naik' => [],
                        'turun' => [],
                    ];
                }

                $arah = strtolower($ind->pivot->arah ?? '');
                if ($arah === 'naik') {
                    $groupedData[$id]['naik'][$fen->id] = $fen;
                } elseif ($arah === 'turun') {
                    $groupedData[$id]['turun'][$fen->id] = $fen;
                }
            }
        }

        $filters = $request->input('filters', []);

        if (!empty($filters)) {
            $filteredGroupedData = [];
            foreach ($groupedData as $id => $row) {
                $kelompok = strtolower($row['indikator']->kelompok ?? 'lainnya');
                if (in_array($kelompok, $filters)) {
                    $filteredGroupedData[$id] = $row;
                }
            }
            $groupedData = $filteredGroupedData;

            if (!in_array('lainnya', $filters)) {
                $unmatchedData = [];
            }
        }

        usort($groupedData, function ($a, $b) {
            return strcmp($a['indikator']->nama, $b['indikator']->nama);
        });

        $filename = "Rekap_Fenomena_" . \Str::slug($kabupaten->nama_kabupaten) . "_" . ($tahun === 'all' ? 'Semua_Tahun' : $tahun) . ".xlsx";

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\PraEksporFenomenaExport($kabupaten, $tahun, $bulan, $groupedData, $unmatchedData),
            $filename
        );
    }

    public function previewSemua(Request $request)
    {
        $tahun = $request->input('tahun', date('Y'));
        $bulan = $request->input('bulan', date('n'));

        $selectionsQuery = \App\Models\PraEksporFenomenaSelection::with(['fenomena.indikators', 'fenomena.creator.kabupaten']);

        if ($tahun !== 'all') {
            $selectionsQuery->where('tahun', $tahun);
        }

        if ($bulan !== 'all') {
            $selectionsQuery->where('bulan', $bulan);
        }

        $selections = $selectionsQuery->get();
        $kabupatens = \App\Models\Kabupaten::orderBy('kode_kab', 'asc')->get();

        $dataPerKabupaten = [];
        foreach ($kabupatens as $kab) {
            $dataPerKabupaten[$kab->id] = [
                'kabupaten' => $kab,
                'groupedData' => [],
                'unmatchedData' => []
            ];
        }

        foreach ($selections as $sel) {
            $fen = $sel->fenomena;
            if (!$fen)
                continue;

            $kab_id = $sel->kabupaten_id;
            if (!isset($dataPerKabupaten[$kab_id]))
                continue;

            if ($fen->indikators->isEmpty()) {
                $dataPerKabupaten[$kab_id]['unmatchedData'][$fen->id] = $fen;
                continue;
            }

            foreach ($fen->indikators as $ind) {
                $ind_id = $ind->id;
                if (!isset($dataPerKabupaten[$kab_id]['groupedData'][$ind_id])) {
                    $dataPerKabupaten[$kab_id]['groupedData'][$ind_id] = [
                        'indikator' => $ind,
                        'naik' => [],
                        'turun' => [],
                    ];
                }

                $arah = strtolower($ind->pivot->arah ?? '');
                if ($arah === 'naik') {
                    $dataPerKabupaten[$kab_id]['groupedData'][$ind_id]['naik'][$fen->id] = $fen;
                } elseif ($arah === 'turun') {
                    $dataPerKabupaten[$kab_id]['groupedData'][$ind_id]['turun'][$fen->id] = $fen;
                }
            }
        }

        foreach ($dataPerKabupaten as $kab_id => &$data) {
            usort($data['groupedData'], function ($a, $b) {
                return strcmp($a['indikator']->nama, $b['indikator']->nama);
            });
        }
        unset($data);

        return view('pra-ekspor.preview-semua', compact('tahun', 'bulan', 'dataPerKabupaten', 'kabupatens'));
    }

    public function exportExcelSemua(Request $request)
    {
        $tahun = $request->input('tahun', date('Y'));
        $bulan = $request->input('bulan', date('n'));

        $selectionsQuery = \App\Models\PraEksporFenomenaSelection::with(['fenomena.indikators', 'fenomena.creator.kabupaten']);

        if ($tahun !== 'all') {
            $selectionsQuery->where('tahun', $tahun);
        }

        if ($bulan !== 'all') {
            $selectionsQuery->where('bulan', $bulan);
        }

        $selections = $selectionsQuery->get();
        $kabupatens = \App\Models\Kabupaten::orderBy('kode_kab', 'asc')->get();

        $dataPerKabupaten = [];
        foreach ($kabupatens as $kab) {
            $dataPerKabupaten[$kab->id] = [
                'kabupaten' => $kab,
                'groupedData' => [],
                'unmatchedData' => []
            ];
        }

        foreach ($selections as $sel) {
            $fen = $sel->fenomena;
            if (!$fen)
                continue;

            $kab_id = $sel->kabupaten_id;
            if (!isset($dataPerKabupaten[$kab_id]))
                continue;

            if ($fen->indikators->isEmpty()) {
                $dataPerKabupaten[$kab_id]['unmatchedData'][$fen->id] = $fen;
                continue;
            }

            foreach ($fen->indikators as $ind) {
                $ind_id = $ind->id;
                if (!isset($dataPerKabupaten[$kab_id]['groupedData'][$ind_id])) {
                    $dataPerKabupaten[$kab_id]['groupedData'][$ind_id] = [
                        'indikator' => $ind,
                        'naik' => [],
                        'turun' => [],
                    ];
                }

                $arah = strtolower($ind->pivot->arah ?? '');
                if ($arah === 'naik') {
                    $dataPerKabupaten[$kab_id]['groupedData'][$ind_id]['naik'][$fen->id] = $fen;
                } elseif ($arah === 'turun') {
                    $dataPerKabupaten[$kab_id]['groupedData'][$ind_id]['turun'][$fen->id] = $fen;
                }
            }
        }

        $filters = $request->input('filters', []);

        foreach ($dataPerKabupaten as $kab_id => &$data) {
            if (!empty($filters)) {
                $filteredGroupedData = [];
                foreach ($data['groupedData'] as $id => $row) {
                    $kelompok = strtolower($row['indikator']->kelompok ?? 'lainnya');
                    if (in_array($kelompok, $filters)) {
                        $filteredGroupedData[$id] = $row;
                    }
                }
                $data['groupedData'] = $filteredGroupedData;

                if (!in_array('lainnya', $filters)) {
                    $data['unmatchedData'] = [];
                }
            }

            usort($data['groupedData'], function ($a, $b) {
                return strcmp($a['indikator']->nama, $b['indikator']->nama);
            });
        }
        unset($data);

        $filename = "Rekap_Fenomena_Semua_Wilayah_" . ($tahun === 'all' ? 'Semua_Tahun' : $tahun) . ".xlsx";

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\PraEksporFenomenaSemuaExport($tahun, $bulan, $dataPerKabupaten),
            $filename
        );
    }
}
