<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PeriodeIndikator;
use App\Models\IndikatorBidang;
use App\Models\IndikatorMakro;
use App\Models\Dimensi;
use App\Models\IndikatorDimensi;
use App\Models\Kabupaten;
use App\Models\NilaiIndikatorMakro;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class IndikatorMakroController extends Controller
{
    public function index(Request $request)
    {
        // 1. Fetch the selected IndikatorMakro (from query param or default to first active)
        $selectedId = $request->query('indikator_makro_id');
        $selectedIndikator = null;
        if ($selectedId) {
            $selectedIndikator = IndikatorMakro::select(['id', 'nama_indikator', 'satuan', 'is_active'])->find($selectedId);
        }
        if (!$selectedIndikator) {
            $selectedIndikator = IndikatorMakro::select(['id', 'nama_indikator', 'satuan', 'is_active'])
                ->orderBy('urutan', 'asc')
                ->orderBy('created_at', 'desc')
                ->first();
        }

        $allPeriodes = PeriodeIndikator::select(['id', 'tahun', 'is_active'])
            ->orderBy('tahun', 'asc')
            ->get();
        // Years filter (default to latest year only to optimize performance)
        $latestYear = $allPeriodes->isNotEmpty() ? $allPeriodes->max('tahun') : null;
        $defaultYears = $latestYear ? [$latestYear] : [];
        $selectedYears = $request->query('tahun', $defaultYears);
        if (!is_array($selectedYears)) {
            $selectedYears = [$selectedYears];
        }
        $periodes = $allPeriodes->filter(function ($p) use ($selectedYears) {
            return in_array($p->tahun, $selectedYears);
        });

        $indikatorDimensis = collect();
        $selectedDimensiIds = $request->query('indikator_dimensi_ids', []);

        if ($selectedIndikator) {
            $indikatorDimensis = IndikatorDimensi::where('indikator_makro_id', $selectedIndikator->id)
                ->with(['dimensi:id,nama_dimensi'])
                ->orderBy('urutan', 'asc')
                ->get(['id', 'indikator_makro_id', 'dimensi_id', 'is_active', 'urutan']);

            $isNoneOnly = ($indikatorDimensis->count() === 1 && strtolower(trim($indikatorDimensis->first()->dimensi->nama_dimensi ?? '')) === 'none');

            if ($isNoneOnly) {
                $selectedDimensiIds = [$indikatorDimensis->first()->id];
            } else {
                if (empty($selectedDimensiIds)) {
                    $selectedDimensiIds = $indikatorDimensis->pluck('id')->toArray();
                } else if (!is_array($selectedDimensiIds)) {
                    $selectedDimensiIds = [$selectedDimensiIds];
                }
            }
        } else {
            $isNoneOnly = false;
        }

        // Fetch regions (order by BPS standard)
        $kabupatens = Kabupaten::select(['id', 'kode_kab', 'nama_kabupaten'])
            ->orderByRaw("
                CASE 
                    WHEN kode_kab = '6100' THEN 1 
                    WHEN kode_kab = '1' OR LOWER(nama_kabupaten) = 'indonesia' THEN 2 
                    ELSE 0 
                END ASC, 
                kode_kab ASC
            ")->get();

        // Fetch values: $values[kabupaten_id][periode_id][indikator_dimensi_id] = nilai
        $values = [];
        if (!empty($selectedDimensiIds)) {
            $rawValues = NilaiIndikatorMakro::select(['id', 'kabupaten_id', 'periode_indikator_id', 'indikator_dimensi_id', 'nilai'])
                ->whereIn('indikator_dimensi_id', $selectedDimensiIds)
                ->whereIn('periode_indikator_id', $periodes->pluck('id'))
                ->get();
            foreach ($rawValues as $v) {
                $values[$v->kabupaten_id][$v->periode_indikator_id][$v->indikator_dimensi_id] = $v->nilai;
            }
        }

        // Selected dimensions records
        $selectedDimensis = $indikatorDimensis->whereIn('id', $selectedDimensiIds);

        // Prepare chart data: datasets for combination of Region and Dimension
        $chartData = [];
        foreach ($kabupatens as $kab) {
            foreach ($selectedDimensis as $indDim) {
                $dataPoints = [];
                foreach ($periodes as $p) {
                    $val = $values[$kab->id][$p->id][$indDim->id] ?? null;
                    $dataPoints[] = ($val !== null) ? (float) $val : null;
                }

                $hasData = collect($dataPoints)->filter(fn($v) => $v !== null)->isNotEmpty();
                if ($hasData) {
                    $dimName = !$isNoneOnly ? ' - ' . ($indDim->dimensi->nama_dimensi ?? '') : '';
                    $chartData[] = [
                        'id' => $kab->id,
                        'indikator_dimensi_id' => $indDim->id,
                        'kode_kab' => $kab->kode_kab,
                        'region_name' => $kab->kode_kab === '6100' ? 'Provinsi Kalimantan Barat' : ($kab->kode_kab === '1' ? 'Indonesia' : $kab->nama_kabupaten),
                        'name' => ($kab->kode_kab === '6100' ? 'Provinsi Kalimantan Barat' : ($kab->kode_kab === '1' ? 'Indonesia' : $kab->nama_kabupaten)) . $dimName,
                        'data' => $dataPoints,
                    ];
                }
            }
        }

        return view('indikator-makro.index', compact(
            'selectedIndikator',
            'allPeriodes',
            'selectedYears',
            'periodes',
            'indikatorDimensis',
            'selectedDimensiIds',
            'selectedDimensis',
            'isNoneOnly',
            'kabupatens',
            'values',
            'chartData'
        ));
    }

    public function export(Request $request)
    {
        $request->validate([
            'bidang_ids' => 'required|array',
            'indikator_makro_ids' => 'required|array',
            'tahuns' => 'required|array',
            'kabupatens' => 'required|array',
        ]);

        $bidangIds = $request->bidang_ids;
        $indikatorIds = $request->indikator_makro_ids;
        $tahunNames = $request->tahuns;
        $kabupatenIds = $request->kabupatens;

        $export = new \App\Exports\IndikatorMakroExport($bidangIds, $indikatorIds, $tahunNames, $kabupatenIds);

        $fileName = "Indikator_Makro_" . date('Ymd_His') . ".xlsx";
        return \Maatwebsite\Excel\Facades\Excel::download($export, $fileName);
    }

    public function inputNilai(Request $request)
    {
        // 1. Fetch all PeriodeIndikator (ordered by tahun desc)
        $periodeIndikators = PeriodeIndikator::orderBy('tahun', 'desc')->get();

        // 2. Fetch all IndikatorMakro (ordered by urutan asc, then created_at desc)
        $indikatorMakros = IndikatorMakro::orderBy('urutan', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();

        // 3. Fetch all Kabupaten (ordered by BPS standard: kab/kota first, then province 6100, then Indonesia at the absolute bottom)
        $kabupatens = Kabupaten::orderByRaw("
            CASE 
                WHEN kode_kab = '6100' THEN 1 
                WHEN kode_kab = '1' OR LOWER(nama_kabupaten) = 'indonesia' THEN 2 
                ELSE 0 
            END ASC, 
            kode_kab ASC
        ")->get();

        // Determine selected Periode
        // Determine selected Periode
        $selectedPeriodeId = $request->query('periode_indikator_id') ?? ($periodeIndikators->where('is_active', true)->first()->id ?? $periodeIndikators->first()->id ?? null);
        $selectedPeriode = PeriodeIndikator::find($selectedPeriodeId);

        // Determine selected Indikator Makro
        $selectedIndikatorMakroId = $request->query('indikator_makro_id') ?? ($indikatorMakros->first()->id ?? null);

        $selectedIndikator = IndikatorMakro::find($selectedIndikatorMakroId);

        // 4. Fetch IndikatorDimensi for this specific macro indicator
        $indikatorDimensis = collect();
        if ($selectedIndikatorMakroId) {
            $indikatorDimensis = IndikatorDimensi::where('indikator_makro_id', $selectedIndikatorMakroId)
                ->with(['dimensi'])
                ->orderBy('urutan', 'asc')
                ->orderBy('created_at', 'desc')
                ->get();
        }

        // 5. Fetch existing values in nilai_indikator_makros for the selected Periode and the dimensions of this indicator
        $existingValues = [];
        if ($selectedPeriodeId && $indikatorDimensis->isNotEmpty()) {
            $existingValues = NilaiIndikatorMakro::where('periode_indikator_id', $selectedPeriodeId)
                ->whereIn('indikator_dimensi_id', $indikatorDimensis->pluck('id'))
                ->get()
                ->groupBy('kabupaten_id')
                ->map(function ($items) {
                    return $items->pluck('nilai', 'indikator_dimensi_id');
                })
                ->toArray();
        }

        return view('indikator-makro.input-nilai', compact(
            'periodeIndikators',
            'indikatorMakros',
            'kabupatens',
            'selectedPeriodeId',
            'selectedIndikatorMakroId',
            'selectedPeriode',
            'selectedIndikator',
            'indikatorDimensis',
            'existingValues'
        ));
    }

    public function storeNilai(Request $request)
    {
        $request->validate([
            'periode_indikator_id' => 'required|exists:periode_indikators,id',
            'indikator_makro_id' => 'required|exists:indikator_makros,id',
            'nilai' => 'nullable|array',
        ]);

        $periodeId = $request->periode_indikator_id;
        $indikatorMakroId = $request->indikator_makro_id;
        $userId = auth()->id();

        if ($request->has('nilai')) {
            foreach ($request->nilai as $kabupatenId => $dimensiValues) {
                // Ensure kabupaten exists
                if (!Kabupaten::where('id', $kabupatenId)->exists()) {
                    continue;
                }

                foreach ($dimensiValues as $indikatorDimensiId => $nilaiRaw) {
                    // Ensure IndikatorDimensi exists and belongs to this indicator
                    $indDim = IndikatorDimensi::where('id', $indikatorDimensiId)
                        ->where('indikator_makro_id', $indikatorMakroId)
                        ->first();
                    if (!$indDim) {
                        continue;
                    }

                    if ($nilaiRaw === null || $nilaiRaw === '') {
                        NilaiIndikatorMakro::where([
                            'periode_indikator_id' => $periodeId,
                            'kabupaten_id' => $kabupatenId,
                            'indikator_dimensi_id' => $indikatorDimensiId,
                        ])->delete();
                    } else {
                        $nilaiClean = $this->cleanFloatValue($nilaiRaw);

                        $record = NilaiIndikatorMakro::firstOrNew([
                            'periode_indikator_id' => $periodeId,
                            'kabupaten_id' => $kabupatenId,
                            'indikator_dimensi_id' => $indikatorDimensiId,
                        ]);

                        if (!$record->exists) {
                            $record->created_by = $userId;
                        }
                        $record->nilai = $nilaiClean;
                        $record->updated_by = $userId;
                        $record->save();
                    }
                }
            }
        }

        $previousUrl = url()->previous();
        if (!str_contains($previousUrl, '#tabel')) {
            $previousUrl = preg_replace('/#.*$/', '', $previousUrl) . '#tabel';
        }
        return redirect($previousUrl)->with('success', 'Nilai Indikator Makro berhasil disimpan.');
    }

    public function searchMakro(Request $request)
    {
        $search = $request->query('q', '');
        $limit = $search === '' ? 3 : 20;
        $makros = IndikatorMakro::where('nama_indikator', 'like', '%' . $search . '%')
            ->orderBy('nama_indikator', 'asc')
            ->limit($limit)
            ->get(['id', 'nama_indikator']);

        return response()->json($makros);
    }

    public function katalog()
    {
        $bidangs = IndikatorBidang::whereHas('indikatorMakros')
            ->with([
                'indikatorMakros' => function ($query) {
                    $query->select(['id', 'indikator_bidang_id', 'nama_indikator', 'is_active'])
                        ->orderBy('urutan', 'asc')
                        ->orderBy('created_at', 'desc');
                }
            ])
            ->orderBy('urutan', 'asc')
            ->get(['id', 'nama_bidang']);

        return response()->json($bidangs);
    }

    public function kelola(Request $request)
    {
        $periodeIndikators = PeriodeIndikator::withCount('nilaiIndikatorMakros')
            ->orderBy('tahun', 'desc')
            ->paginate(10, ['*'], 'periode_page');

        // Bidang Search
        $searchBidang = $request->query('search_bidang');
        $queryBidang = IndikatorBidang::with(['creator', 'updater'])
            ->withCount('indikatorMakros');
        if ($searchBidang) {
            $queryBidang->where('nama_bidang', 'like', '%' . $searchBidang . '%');
        }
        $indikatorBidangs = $queryBidang->orderBy('urutan', 'asc')
            ->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'bidang_page');

        // Filter Indikator Makro by selected Bidang
        $selectedFilterBidangId = $request->query('filter_bidang_id');
        $selectedFilterBidang = null;

        // Makro Search
        $searchMakro = $request->query('search_makro');
        if ($selectedFilterBidangId) {
            $selectedFilterBidang = IndikatorBidang::find($selectedFilterBidangId);
            $queryMakro = IndikatorMakro::with(['bidang', 'creator', 'updater'])
                ->withCount('indikatorDimensis')
                ->where('indikator_bidang_id', $selectedFilterBidangId);
            if ($searchMakro) {
                $queryMakro->where('nama_indikator', 'like', '%' . $searchMakro . '%');
            }
            $indikatorMakros = $queryMakro->orderBy('urutan', 'asc')
                ->orderBy('created_at', 'desc')
                ->paginate(10, ['*'], 'makro_page');
        } else {
            $indikatorMakros = IndikatorMakro::whereNull('id')
                ->paginate(10, ['*'], 'makro_page');
        }

        // Dimensi Search
        $searchDimensi = $request->query('search_dimensi');
        $queryDimensi = Dimensi::with(['creator', 'updater'])
            ->withCount('indikatorDimensis');
        if ($searchDimensi) {
            $queryDimensi->where('nama_dimensi', 'like', '%' . $searchDimensi . '%');
        }
        $dimensis = $queryDimensi->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'dimensi_page');

        // Indikator Dimensi Search
        $searchIndDimensi = $request->query('search_ind_dimensi');
        $selectedMakroId = $request->query('filter_makro_id');
        $selectedMakro = null;

        if ($selectedMakroId) {
            $selectedMakro = IndikatorMakro::find($selectedMakroId);
            $queryIndDimensi = IndikatorDimensi::where('indikator_makro_id', $selectedMakroId)
                ->with(['indikatorMakro', 'dimensi', 'creator', 'updater'])
                ->withCount('nilaiIndikatorMakros');
            if ($searchIndDimensi) {
                $queryIndDimensi->whereHas('dimensi', function ($q) use ($searchIndDimensi) {
                    $q->where('nama_dimensi', 'like', '%' . $searchIndDimensi . '%');
                });
            }
            $indikatorDimensis = $queryIndDimensi->orderBy('urutan', 'asc')
                ->orderBy('created_at', 'desc')
                ->paginate(10, ['*'], 'ind_dimensi_page');
        } else {
            $indikatorDimensis = IndikatorDimensi::whereNull('id')
                ->paginate(10, ['*'], 'ind_dimensi_page');
        }

        $allIndikatorBidangs = IndikatorBidang::orderBy('id', 'asc')->get();
        $allDimensis = Dimensi::all();

        return view('indikator-makro.kelola', compact(
            'periodeIndikators',
            'indikatorBidangs',
            'indikatorMakros',
            'dimensis',
            'indikatorDimensis',
            'allIndikatorBidangs',
            'allDimensis',
            'selectedMakroId',
            'selectedMakro',
            'selectedFilterBidangId',
            'selectedFilterBidang',
            'searchBidang',
            'searchMakro',
            'searchDimensi',
            'searchIndDimensi'
        ));
    }

    // --- Periode Indikator CRUD ---
    public function storePeriode(Request $request)
    {
        $request->validate(['tahun' => 'required|integer|unique:periode_indikators,tahun']);
        $data = $request->all();
        $data['is_active'] = $request->has('is_active');
        PeriodeIndikator::create($data);
        return back()->with('success', 'Periode Indikator berhasil ditambahkan')->with('tab', 'periode');
    }

    public function updatePeriode(Request $request, $id)
    {
        $request->validate(['tahun' => 'required|integer|unique:periode_indikators,tahun,' . $id]);
        $data = $request->all();
        $data['is_active'] = $request->has('is_active');
        PeriodeIndikator::findOrFail($id)->update($data);
        return back()->with('success', 'Periode Indikator berhasil diperbarui')->with('tab', 'periode');
    }

    public function togglePeriodeActive($id)
    {
        $periode = PeriodeIndikator::findOrFail($id);
        $periode->is_active = !$periode->is_active;
        $periode->save();

        return response()->json([
            'success' => true,
            'is_active' => $periode->is_active
        ]);
    }

    public function destroyPeriode($id)
    {
        $count = \App\Models\NilaiIndikatorMakro::where('periode_indikator_id', $id)->count();
        if ($count > 0) {
            return back()->withErrors(['error' => "Periode Indikator tidak dapat dihapus karena sedang digunakan oleh {$count} data nilai."])->with('tab', 'periode');
        }
        PeriodeIndikator::findOrFail($id)->delete();
        return back()->with('success', 'Periode Indikator berhasil dihapus')->with('tab', 'periode');
    }

    // --- Indikator Bidang CRUD ---
    public function storeBidang(Request $request)
    {
        $request->validate([
            'nama_bidang' => 'required|string|max:100',
        ]);

        // Cek duplikat nama bidang via slug 
        $slug = \Illuminate\Support\Str::slug($request->nama_bidang);
        $exists = IndikatorBidang::get()->contains(function ($item) use ($slug) {
            return \Illuminate\Support\Str::slug($item->nama_bidang) === $slug;
        });
        if ($exists) {
            return back()->withErrors(['nama_bidang' => 'Nama bidang sudah terdaftar.'])->withInput()->with('tab', 'bidang');
        }

        $data = [
            'nama_bidang' => $request->nama_bidang,
            'created_by' => auth()->id(),
        ];

        $maxUrutan = IndikatorBidang::max('urutan') ?? 0;
        $data['urutan'] = $maxUrutan + 1;

        IndikatorBidang::create($data);
        return back()->with('success', 'Indikator Bidang berhasil ditambahkan')->with('tab', 'bidang');
    }

    public function updateBidang(Request $request, $id)
    {
        $request->validate([
            'nama_bidang' => 'required|string|max:100',
            'urutan' => 'required|integer|min:1',
        ]);

        // Cek duplikat nama bidang via slug, kecuali record yang sedang diedit
        $slug = \Illuminate\Support\Str::slug($request->nama_bidang);
        $exists = IndikatorBidang::where('id', '!=', $id)->get()->contains(function ($item) use ($slug) {
            return \Illuminate\Support\Str::slug($item->nama_bidang) === $slug;
        });
        if ($exists) {
            return back()->withErrors(['nama_bidang' => 'Nama bidang sudah terdaftar.'])->withInput()->with('tab', 'bidang');
        }

        $bidang = IndikatorBidang::findOrFail($id);
        $oldUrutan = $bidang->urutan;
        $maxUrutan = IndikatorBidang::max('urutan') ?? 1;
        $newUrutan = max(1, min($maxUrutan, (int) $request->urutan));

        if ($newUrutan !== $oldUrutan) {
            if ($newUrutan > $oldUrutan) {
                IndikatorBidang::where('id', '!=', $id)
                    ->whereBetween('urutan', [$oldUrutan + 1, $newUrutan])
                    ->decrement('urutan');
            } else {
                IndikatorBidang::where('id', '!=', $id)
                    ->whereBetween('urutan', [$newUrutan, $oldUrutan - 1])
                    ->increment('urutan');
            }
            $bidang->urutan = $newUrutan;
        }

        $bidang->nama_bidang = $request->nama_bidang;
        $bidang->updated_by = auth()->id();
        $bidang->save();

        return back()->with('success', 'Indikator Bidang berhasil diperbarui')->with('tab', 'bidang');
    }

    public function reorderBidang(Request $request)
    {
        $request->validate(['order' => 'required|array']);
        $startIdx = $request->input('start_idx', 0);
        foreach ($request->order as $index => $id) {
            IndikatorBidang::where('id', $id)->update([
                'urutan' => $startIdx + $index + 1,
                'updated_by' => auth()->id()
            ]);
        }
        return response()->json(['success' => true]);
    }

    public function destroyBidang($id)
    {
        $count = \App\Models\IndikatorMakro::where('indikator_bidang_id', $id)->count();
        if ($count > 0) {
            return back()->withErrors(['error' => "Indikator Bidang tidak dapat dihapus karena sedang digunakan oleh {$count} data indikator makro."])->with('tab', 'bidang');
        }
        IndikatorBidang::findOrFail($id)->delete();
        return back()->with('success', 'Indikator Bidang berhasil dihapus')->with('tab', 'bidang');
    }


    // --- Indikator Makro CRUD ---
    public function storeMakro(Request $request)
    {
        $request->validate([
            'indikator_bidang_id' => 'required|exists:indikator_bidangs,id',
            'nama_indikator' => 'required|string|max:255',
            'satuan' => 'nullable|string|max:100',
            'deskripsi' => 'nullable|string',
        ]);

        $slug = \Illuminate\Support\Str::slug($request->nama_indikator);
        $exists = IndikatorMakro::where('indikator_bidang_id', $request->indikator_bidang_id)
            ->get()
            ->contains(function ($item) use ($slug) {
                return \Illuminate\Support\Str::slug($item->nama_indikator) === $slug;
            });
        if ($exists) {
            return back()->withErrors(['nama_indikator' => 'Nama indikator makro sudah terdaftar di bidang yang sama.'])->withInput()->with('tab', 'makro');
        }

        $data = $request->all();
        $data['created_by'] = auth()->id();
        $data['is_active'] = $request->has('is_active');

        // Scoped by bidang
        $maxUrutan = IndikatorMakro::where('indikator_bidang_id', $request->indikator_bidang_id)->max('urutan') ?? 0;
        $data['urutan'] = $maxUrutan + 1;

        IndikatorMakro::create($data);
        return back()->with('success', 'Indikator Makro berhasil ditambahkan')->with('tab', 'makro');
    }

    public function updateMakro(Request $request, $id)
    {
        $request->validate([
            'indikator_bidang_id' => 'required|exists:indikator_bidangs,id',
            'nama_indikator' => 'required|string|max:255',
            'satuan' => 'nullable|string|max:100',
            'deskripsi' => 'nullable|string',
            'urutan' => 'required|integer|min:1',
        ]);

        $slug = \Illuminate\Support\Str::slug($request->nama_indikator);
        $exists = IndikatorMakro::where('indikator_bidang_id', $request->indikator_bidang_id)
            ->where('id', '!=', $id)
            ->get()
            ->contains(function ($item) use ($slug) {
                return \Illuminate\Support\Str::slug($item->nama_indikator) === $slug;
            });
        if ($exists) {
            return back()->withErrors(['nama_indikator' => 'Nama indikator makro sudah terdaftar di bidang yang sama.'])->withInput()->with('tab', 'makro');
        }

        $makro = IndikatorMakro::findOrFail($id);
        $oldBidangId = $makro->indikator_bidang_id;
        $newBidangId = (int) $request->indikator_bidang_id;

        $makro->nama_indikator = $request->nama_indikator;
        $makro->satuan = $request->satuan;
        $makro->deskripsi = $request->deskripsi;
        $makro->is_active = $request->has('is_active');
        $makro->updated_by = auth()->id();

        if ($oldBidangId === $newBidangId) {
            // Urutan shifting within same bidang
            $oldUrutan = $makro->urutan;
            $maxUrutan = IndikatorMakro::where('indikator_bidang_id', $newBidangId)->max('urutan') ?? 1;
            $newUrutan = max(1, min($maxUrutan, (int) $request->urutan));

            if ($newUrutan !== $oldUrutan) {
                if ($newUrutan > $oldUrutan) {
                    IndikatorMakro::where('indikator_bidang_id', $newBidangId)
                        ->where('id', '!=', $id)
                        ->whereBetween('urutan', [$oldUrutan + 1, $newUrutan])
                        ->decrement('urutan');
                } else {
                    IndikatorMakro::where('indikator_bidang_id', $newBidangId)
                        ->where('id', '!=', $id)
                        ->whereBetween('urutan', [$newUrutan, $oldUrutan - 1])
                        ->increment('urutan');
                }
                $makro->urutan = $newUrutan;
            }
            $makro->save();
        } else {
            // Bidang changed!
            // Move to the new bidang at the specified urutan (or end of new bidang), then shift items in new bidang
            $maxUrutanNewBidang = IndikatorMakro::where('indikator_bidang_id', $newBidangId)->max('urutan') ?? 0;
            $targetUrutan = max(1, min($maxUrutanNewBidang + 1, (int) $request->urutan));

            // Shift new bidang items up to make room
            IndikatorMakro::where('indikator_bidang_id', $newBidangId)
                ->where('urutan', '>=', $targetUrutan)
                ->increment('urutan');

            $makro->indikator_bidang_id = $newBidangId;
            $makro->urutan = $targetUrutan;
            $makro->save();

            // Resequence the old bidang
            $this->resequenceMakro($oldBidangId);
        }

        return back()->with('success', 'Indikator Makro berhasil diperbarui')->with('tab', 'makro');
    }

    public function reorderMakro(Request $request)
    {
        $request->validate(['order' => 'required|array']);
        $startIdx = $request->input('start_idx', 0);
        foreach ($request->order as $index => $id) {
            IndikatorMakro::where('id', $id)->update([
                'urutan' => $startIdx + $index + 1,
                'updated_by' => auth()->id()
            ]);
        }
        return response()->json(['success' => true]);
    }

    public function toggleMakroActive($id)
    {
        $makro = IndikatorMakro::findOrFail($id);
        $makro->is_active = !$makro->is_active;
        $makro->updated_by = auth()->id();
        $makro->save();

        return response()->json([
            'success' => true,
            'is_active' => $makro->is_active
        ]);
    }

    public function destroyMakro($id)
    {
        $makro = IndikatorMakro::findOrFail($id);
        $count = \App\Models\IndikatorDimensi::where('indikator_makro_id', $id)->count();
        if ($count > 0) {
            return back()->withErrors(['error' => "Indikator Makro tidak dapat dihapus karena sedang digunakan oleh {$count} relasi dimensi."])->with('tab', 'makro');
        }
        $bidangId = $makro->indikator_bidang_id;
        $makro->delete();

        // Resequence remaining makros in the bidang
        $this->resequenceMakro($bidangId);

        return back()->with('success', 'Indikator Makro berhasil dihapus')->with('tab', 'makro');
    }

    // --- Dimensi CRUD ---
    public function storeDimensi(Request $request)
    {
        $request->validate(['nama_dimensi' => 'required|string|max:255']);



        $slug = \Illuminate\Support\Str::slug($request->nama_dimensi);
        $exists = Dimensi::get()->contains(function ($item) use ($slug) {
            return \Illuminate\Support\Str::slug($item->nama_dimensi) === $slug;
        });
        if ($exists) {
            return back()->withErrors(['nama_dimensi' => 'Nama dimensi sudah terdaftar.'])->withInput()->with('tab', 'dimensi');
        }

        $data = $request->all();
        $data['created_by'] = auth()->id();

        Dimensi::create($data);
        return back()->with('success', 'Dimensi berhasil ditambahkan')->with('tab', 'dimensi');
    }

    public function updateDimensi(Request $request, $id)
    {
        $request->validate(['nama_dimensi' => 'required|string|max:255']);

        // Cegah edit dimensi 'None'
        $dimensi = Dimensi::findOrFail($id);
        if (strtolower(trim($dimensi->nama_dimensi)) === 'none') {
            return back()->withErrors(['nama_dimensi' => 'Dimensi "None" tidak dapat diedit.'])->with('tab', 'dimensi');
        }


        $slug = \Illuminate\Support\Str::slug($request->nama_dimensi);
        $exists = Dimensi::where('id', '!=', $id)->get()->contains(function ($item) use ($slug) {
            return \Illuminate\Support\Str::slug($item->nama_dimensi) === $slug;
        });
        if ($exists) {
            return back()->withErrors(['nama_dimensi' => 'Nama dimensi sudah terdaftar.'])->withInput()->with('tab', 'dimensi');
        }

        $data = $request->all();
        $data['updated_by'] = auth()->id();

        $dimensi->update($data);
        return back()->with('success', 'Dimensi berhasil diperbarui')->with('tab', 'dimensi');
    }

    public function destroyDimensi($id)
    {
        $dimensi = Dimensi::findOrFail($id);

        // Cegah penghapusan dimensi 'None'
        if (strtolower(trim($dimensi->nama_dimensi)) === 'none') {
            return back()->withErrors(['error' => 'Dimensi "None" tidak dapat dihapus.'])->with('tab', 'dimensi');
        }

        $count = \App\Models\IndikatorDimensi::where('dimensi_id', $id)->count();
        if ($count > 0) {
            return back()->withErrors(['error' => "Dimensi tidak dapat dihapus karena sedang digunakan oleh {$count} relasi indikator."])->with('tab', 'dimensi');
        }
        $dimensi->delete();
        return back()->with('success', 'Dimensi berhasil dihapus')->with('tab', 'dimensi');
    }

    // --- Indikator Dimensi CRUD ---
    public function storeIndikatorDimensi(Request $request)
    {
        $request->validate([
            'indikator_makro_id' => 'required|exists:indikator_makros,id',
            'dimensi_id' => 'required|exists:dimensis,id',
        ]);

        // Cegah kombinasi indikator + dimensi yang sudah ada
        $alreadyExists = IndikatorDimensi::where('indikator_makro_id', $request->indikator_makro_id)
            ->where('dimensi_id', $request->dimensi_id)
            ->exists();
        if ($alreadyExists) {
            return back()
                ->withErrors(['indikator_dimensi' => 'Kombinasi Indikator Makro dan Dimensi ini sudah terdaftar.'])
                ->with('tab', 'indikator-dimensi');
        }

        // Validasi dimensi "none"
        $dimensi = Dimensi::findOrFail($request->dimensi_id);
        $isNoneDimensi = strtolower(trim($dimensi->nama_dimensi)) === 'none';

        if ($isNoneDimensi) {
            $hasExistingRelations = IndikatorDimensi::where('indikator_makro_id', $request->indikator_makro_id)->exists();
            if ($hasExistingRelations) {
                return back()
                    ->withErrors(['indikator_dimensi' => 'Indikator ini sudah memiliki relasi dimensi lain, tidak dapat memilih dimensi "none".'])
                    ->with('tab', 'indikator-dimensi');
            }
        } else {
            $hasNoneRelation = IndikatorDimensi::where('indikator_makro_id', $request->indikator_makro_id)
                ->whereHas('dimensi', function ($q) {
                    $q->where(DB::raw('LOWER(nama_dimensi)'), 'none');
                })
                ->exists();
            if ($hasNoneRelation) {
                return back()
                    ->withErrors(['indikator_dimensi' => 'Indikator ini sudah menggunakan dimensi "none". Hapus dimensi "none" terlebih dahulu untuk menambahkan dimensi lain.'])
                    ->with('tab', 'indikator-dimensi');
            }
        }

        $data = $request->all();
        $data['created_by'] = auth()->id();
        $data['is_active'] = true;

        $maxUrutan = IndikatorDimensi::where('indikator_makro_id', $request->indikator_makro_id)->max('urutan') ?? 0;
        $data['urutan'] = $maxUrutan + 1;

        IndikatorDimensi::create($data);
        return back()->with('success', 'Indikator Dimensi berhasil ditambahkan')->with('tab', 'indikator-dimensi');
    }

    public function updateIndikatorDimensi(Request $request, $id)
    {
        $request->validate([
            'indikator_makro_id' => 'required|exists:indikator_makros,id',
            'dimensi_id' => 'required|exists:dimensis,id',
            'urutan' => 'required|integer|min:1',
        ]);

        // Cegah kombinasi indikator + dimensi yang sudah ada (kecuali record saat ini)
        $alreadyExists = IndikatorDimensi::where('indikator_makro_id', $request->indikator_makro_id)
            ->where('dimensi_id', $request->dimensi_id)
            ->where('id', '!=', $id)
            ->exists();
        if ($alreadyExists) {
            return back()
                ->withErrors(['indikator_dimensi' => 'Kombinasi Indikator Makro dan Dimensi ini sudah terdaftar.'])
                ->with('tab', 'indikator-dimensi');
        }

        // Validasi dimensi "none" untuk update
        $dimensi = Dimensi::findOrFail($request->dimensi_id);
        $isNoneDimensi = strtolower(trim($dimensi->nama_dimensi)) === 'none';

        if ($isNoneDimensi) {
            $hasExistingRelations = IndikatorDimensi::where('indikator_makro_id', $request->indikator_makro_id)
                ->where('id', '!=', $id)
                ->exists();
            if ($hasExistingRelations) {
                return back()
                    ->withErrors(['indikator_dimensi' => 'Indikator ini sudah memiliki relasi dimensi lain, tidak dapat memilih dimensi "none".'])
                    ->with('tab', 'indikator-dimensi');
            }
        } else {
            $hasNoneRelation = IndikatorDimensi::where('indikator_makro_id', $request->indikator_makro_id)
                ->where('id', '!=', $id)
                ->whereHas('dimensi', function ($q) {
                    $q->where(DB::raw('LOWER(nama_dimensi)'), 'none');
                })
                ->exists();
            if ($hasNoneRelation) {
                return back()
                    ->withErrors(['indikator_dimensi' => 'Indikator ini sudah menggunakan dimensi "none". Hapus dimensi "none" terlebih dahulu untuk menambahkan dimensi lain.'])
                    ->with('tab', 'indikator-dimensi');
            }
        }

        $indDim = IndikatorDimensi::findOrFail($id);
        $oldMakroId = $indDim->indikator_makro_id;
        $newMakroId = (int) $request->indikator_makro_id;

        $indDim->dimensi_id = $request->dimensi_id;
        $indDim->updated_by = auth()->id();

        if ($oldMakroId === $newMakroId) {
            // Urutan shifting within same makro
            $oldUrutan = $indDim->urutan;
            $maxUrutan = IndikatorDimensi::where('indikator_makro_id', $newMakroId)->max('urutan') ?? 1;
            $newUrutan = max(1, min($maxUrutan, (int) $request->urutan));

            if ($newUrutan !== $oldUrutan) {
                if ($newUrutan > $oldUrutan) {
                    IndikatorDimensi::where('indikator_makro_id', $newMakroId)
                        ->where('id', '!=', $id)
                        ->whereBetween('urutan', [$oldUrutan + 1, $newUrutan])
                        ->decrement('urutan');
                } else {
                    IndikatorDimensi::where('indikator_makro_id', $newMakroId)
                        ->where('id', '!=', $id)
                        ->whereBetween('urutan', [$newUrutan, $oldUrutan - 1])
                        ->increment('urutan');
                }
                $indDim->urutan = $newUrutan;
            }
            $indDim->save();
        } else {
            // Makro changed!
            // Move to the new makro at the specified urutan (or end of new makro), then shift items in new makro
            $maxUrutanNewMakro = IndikatorDimensi::where('indikator_makro_id', $newMakroId)->max('urutan') ?? 0;
            $targetUrutan = max(1, min($maxUrutanNewMakro + 1, (int) $request->urutan));

            // Shift new makro items up to make room
            IndikatorDimensi::where('indikator_makro_id', $newMakroId)
                ->where('urutan', '>=', $targetUrutan)
                ->increment('urutan');

            $indDim->indikator_makro_id = $newMakroId;
            $indDim->urutan = $targetUrutan;
            $indDim->save();

            // Resequence the old makro
            $this->resequenceIndikatorDimensi($oldMakroId);
        }

        return back()->with('success', 'Indikator Dimensi berhasil diperbarui')->with('tab', 'indikator-dimensi');
    }

    public function reorderIndikatorDimensi(Request $request)
    {
        $request->validate(['order' => 'required|array']);
        $startIdx = $request->input('start_idx', 0);
        foreach ($request->order as $index => $id) {
            IndikatorDimensi::where('id', $id)->update([
                'urutan' => $startIdx + $index + 1,
                'updated_by' => auth()->id()
            ]);
        }
        return response()->json(['success' => true]);
    }

    public function toggleIndikatorDimensiActive($id)
    {
        $indDim = IndikatorDimensi::findOrFail($id);
        $indDim->is_active = !$indDim->is_active;
        $indDim->updated_by = auth()->id();
        $indDim->save();

        return response()->json([
            'success' => true,
            'is_active' => $indDim->is_active
        ]);
    }

    public function destroyIndikatorDimensi($id)
    {
        $count = \App\Models\NilaiIndikatorMakro::where('indikator_dimensi_id', $id)->count();
        if ($count > 0) {
            return back()->withErrors(['error' => "Indikator Dimensi tidak dapat dihapus karena sedang digunakan oleh {$count} data nilai."])->with('tab', 'indikator-dimensi');
        }
        $indDim = IndikatorDimensi::findOrFail($id);
        $indikatorMakroId = $indDim->indikator_makro_id;
        $indDim->delete();
        $this->resequenceIndikatorDimensi($indikatorMakroId);
        return back()->with('success', 'Indikator Dimensi berhasil dihapus')->with('tab', 'indikator-dimensi');
    }

    private function resequenceMakro($bidangId)
    {
        $makros = IndikatorMakro::where('indikator_bidang_id', $bidangId)
            ->orderBy('urutan', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();

        foreach ($makros as $index => $makro) {
            $makro->update(['urutan' => $index + 1]);
        }
    }

    private function resequenceIndikatorDimensi($indikatorMakroId)
    {
        $indDims = IndikatorDimensi::where('indikator_makro_id', $indikatorMakroId)
            ->orderBy('urutan', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();

        foreach ($indDims as $index => $indDim) {
            $indDim->update(['urutan' => $index + 1]);
        }
    }

    private function cleanFloatValue($val)
    {
        if ($val === null || $val === '') {
            return null;
        }

        $val = trim($val);

        // Remove Rp currency symbol, space, percentage, etc.
        $val = preg_replace('/[^0-9,\.\-]/', '', $val);

        // Standardize based on existence of both separators
        if (strpos($val, '.') !== false && strpos($val, ',') !== false) {
            if (strpos($val, '.') < strpos($val, ',')) {
                // dot is thousands, comma is decimal (Indonesian format: 54.748.977,00)
                $val = str_replace('.', '', $val);
                $val = str_replace(',', '.', $val);
            } else {
                // comma is thousands, dot is decimal (US format: 54,748,977.00)
                $val = str_replace(',', '', $val);
            }
        } else {
            // Only one type of separator or none
            if (strpos($val, ',') !== false) {
                // Comma could be decimal or thousands
                $parts = explode(',', $val);
                if (count($parts) === 2 && strlen($parts[1]) !== 3) {
                    // Decimal: e.g. 12,34
                    $val = str_replace(',', '.', $val);
                } else {
                    // Thousands: e.g. 12,345 or multiple commas
                    $val = str_replace(',', '', $val);
                }
            } elseif (strpos($val, '.') !== false) {
                // Dot could be decimal or thousands
                $parts = explode('.', $val);
                if (count($parts) === 2 && strlen($parts[1]) !== 3) {
                    // Decimal: e.g. 12.34
                    // Keep dot
                } else {
                    // Thousands: e.g. 12.345 or multiple dots
                    $val = str_replace('.', '', $val);
                }
            }
        }

        return (float) $val;
    }
}
