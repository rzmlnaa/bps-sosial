<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Period;
use App\Models\ConsumptionValue;
use App\Models\Kabupaten;
use App\Models\Coicop;
use Illuminate\Support\Facades\DB;

class SerutiController extends Controller
{
    public function index()
    {
        $years = Period::distinct()->orderBy('year', 'desc')->pluck('year');
        $kabupatens = Kabupaten::orderBy('kode_kab', 'asc')->get();
        $coicops = Coicop::orderBy('kode', 'asc')->get();

        // Get first Kabupaten that has data
        $defaultKabupatenId = ConsumptionValue::select('kabupaten_id')
            ->distinct()
            ->first()
                ?->kabupaten_id;

        return view('seruti.index', compact('years', 'kabupatens', 'coicops', 'defaultKabupatenId'));
    }

    public function create()
    {
        $coicops = Coicop::with(['userAdd', 'userUpdate'])->orderBy('kode', 'asc')->get();
        $kabupatens = Kabupaten::orderBy('kode_kab', 'asc')->get();
        return view('seruti.input', compact('coicops', 'kabupatens'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'year' => 'required|numeric',
            'quarter' => 'required|numeric|min:1|max:4',
            'kabupaten_id' => 'required|exists:tb_kabupaten,id',
            'data' => 'required|array',
            'data.*.kode' => 'required',
            'data.*.value' => 'required|numeric',
        ]);

        try {
            DB::beginTransaction();

            $period = Period::firstOrCreate(
                ['year' => $request->year, 'quarter' => $request->quarter]
            );

            $count = 0;
            foreach ($request->data as $row) {
                $coicop = Coicop::where('kode', $row['kode'])->first();
                if ($coicop) {
                    ConsumptionValue::updateOrCreate(
                        [
                            'period_id' => $period->id,
                            'coicop_id' => $coicop->id,
                            'kabupaten_id' => $request->kabupaten_id
                        ],
                        [
                            'value' => $row['value']
                        ]
                    );
                    $count++;
                }
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => "$count data konsumsi berhasil disimpan untuk Tahun {$request->year} Triwulan {$request->quarter}!"]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function storeCoicop(Request $request)
    {
        $request->validate([
            'coicops' => 'required|array',
            'coicops.*.kode' => 'required',
            'coicops.*.nama' => 'required',
            'coicops.*.seruti' => 'nullable',
            'coicops.*.id' => 'nullable',
        ]);

        try {
            DB::beginTransaction();

            $savedCount = 0;
            $skippedCount = 0;

            foreach ($request->coicops as $row) {
                // Determine is_total based on keyword if not provided
                $isTotal = false;
                if (stripos($row['nama'], 'Total') !== false || stripos($row['nama'], 'Subtotal') !== false) {
                    $isTotal = true;
                }

                if (isset($row['id']) && $row['id']) {
                    // --- UPDATE EXISTING (Edit Mode) ---
                    $existing = Coicop::find($row['id']);
                    if ($existing) {
                        // Check if new code conflicts with ANOTHER record
                        $conflict = Coicop::where('kode', $row['kode'])
                            ->where('id', '!=', $row['id'])
                            ->exists();

                        if ($conflict) {
                            throw new \Exception("Gagal Edit: Kode '{$row['kode']}' sudah digunakan oleh komoditas lain.");
                        }

                        $existing->update([
                            'kode' => $row['kode'],
                            'nama' => $row['nama'],
                            'seruti' => $row['seruti'] ?? '',
                            'is_total' => $isTotal,
                            'user_id_update' => auth()->id()
                        ]);
                        $savedCount++;
                    }
                } else {
                    // --- CREATE NEW (Paste/Manual Mode) ---
                    // "Jangan memasukan data yang sama atau sudah ada"
                    $exists = Coicop::where('kode', $row['kode'])->exists();

                    if ($exists) {
                        $skippedCount++;
                        continue; // SKIP DUPLICATE
                    }

                    Coicop::create([
                        'kode' => $row['kode'],
                        'nama' => $row['nama'],
                        'seruti' => $row['seruti'] ?? '',
                        'is_total' => $isTotal,
                        'user_id_add' => auth()->id()
                    ]);
                    $savedCount++;
                }
            }

            DB::commit();

            $message = "Simpan berhasil! {$savedCount} data diproses.";
            if ($skippedCount > 0) {
                $message .= " ({$skippedCount} data diabaikan karena Kode sudah ada)";
            }

            return response()->json(['success' => true, 'message' => $message]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }
    public function getData(Request $request)
    {
        $request->validate([
            'year' => 'required|numeric',
            'quarter' => 'required|numeric|min:1|max:4',
            'kabupaten_id' => 'required|exists:tb_kabupaten,id',
        ]);

        try {
            // 1. Get Master Data
            $coicops = Coicop::orderBy('kode', 'asc')->get();

            // 2. Get Existing Values (if any)
            $period = Period::where('year', $request->year)
                ->where('quarter', $request->quarter)
                ->first();

            $values = [];
            if ($period) {
                $values = ConsumptionValue::where('period_id', $period->id)
                    ->where('kabupaten_id', $request->kabupaten_id)
                    ->get()
                    ->keyBy('coicop_id'); // Key by coicop ID for easy lookup
            }

            // 3. Merge Data
            $data = $coicops->map(function ($item) use ($values) {
                $val = isset($values[$item->id]) ? $values[$item->id]->value : null;
                return [
                    'kode' => $item->kode,
                    'nama' => $item->nama,
                    'seruti' => $item->seruti,
                    'value' => $val
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error fetching data: ' . $e->getMessage()], 500);
        }
    }

    public function getChartData(Request $request)
    {
        $yearInfo = $request->get('year');
        $quartersStr = $request->get('quarters');
        $quarters = $quartersStr ? explode(',', $quartersStr) : [1, 2, 3, 4];

        $kabupatenId = $request->get('kabupaten_id');
        $coicopId = $request->get('coicop_id');

        // Explicitly check for empty strings or 'null' strings from frontend
        if ($kabupatenId === 'null' || $kabupatenId === '')
            $kabupatenId = null;
        if ($coicopId === 'null' || $coicopId === '')
            $coicopId = null;

        if (!$yearInfo || (!$kabupatenId && !$coicopId)) {
            return response()->json(['empty' => true]);
        }

        $query = ConsumptionValue::with(['period', 'coicop', 'kabupaten']);

        // Handle Year Filter
        if ($yearInfo !== 'all') {
            $query->whereHas('period', function ($q) use ($yearInfo) {
                $q->where('year', $yearInfo);
            });
        }

        // Handle Quarter Filter
        $query->whereHas('period', function ($q) use ($quarters) {
            $q->whereIn('quarter', $quarters);
        });

        // Determine Mode: 
        // 1. If Coicop is selected (and not Kabupaten), we are comparing Kabupatens for that Coicop.
        // 2. If Kabupaten is selected (and not Coicop), we are comparing Coicops for that Kabupaten.
        // If both are present, we prioritize the one that makes more sense (usually regency mode if a specific kab is picked).

        $mode = 'regency'; // Default: X-Axis are COICOPs
        if ($coicopId && !$kabupatenId) {
            $mode = 'subgroup'; // X-Axis are Kabupatens
        }

        if ($kabupatenId) {
            $query->where('kabupaten_id', $kabupatenId);
        }

        if ($coicopId) {
            $query->where('coicop_id', $coicopId);
        }

        $rawData = $query->get();

        if ($rawData->isEmpty()) {
            return response()->json(['empty' => true, 'mode' => $mode, 'debug' => ['params' => $request->all()]]);
        }

        $series = [];
        $categories = [];

        // --- New Logic for Chronological Series ---
        // Series = "Tw [Quarter] [Year]". Categories = Coicops or Kabupatens.

        // 1. Determine all unique periods (Year + Quarter) present in the data, sorted chronologically
        $availablePeriods = $rawData->map(function ($item) {
            return [
                'year' => $item->period->year,
                'quarter' => $item->period->quarter,
                'key' => $item->period->year . '-' . $item->period->quarter,
                'label' => "Tw {$item->period->quarter} {$item->period->year}"
            ];
        })->unique('key')->values()->sort(function ($a, $b) {
            if ($a['year'] != $b['year'])
                return $a['year'] <=> $b['year'];
            return $a['quarter'] <=> $b['quarter'];
        })->values();

        foreach ($availablePeriods as $p) {
            $series[$p['key']] = [
                'name' => $p['label'],
                'data' => []
            ];
        }

        if ($mode === 'regency') {
            // X-Axis: Coicops
            $grouped = $rawData->groupBy('coicop_id');
            // Filter out items where value is 0 if needed, but for grouped bar usually keep all in categories
            $sortedCoicops = $rawData->unique('coicop_id')->sortBy(fn($item) => $item->coicop->kode);

            foreach ($sortedCoicops as $item) {
                $categories[] = "[{$item->coicop->kode}] {$item->coicop->nama}";
                foreach ($availablePeriods as $p) {
                    $val = isset($grouped[$item->coicop_id])
                        ? $grouped[$item->coicop_id]->first(fn($v) => $v->period->year == $p['year'] && $v->period->quarter == $p['quarter'])
                        : null;
                    $series[$p['key']]['data'][] = $val ? (float) $val->value : 0;
                }
            }
            $kabName = $rawData->first()->kabupaten->nama_kabupaten;
            $title = "Konsumsi per Kapita - {$kabName}";
            if ($yearInfo !== 'all')
                $title .= " ({$yearInfo})";
        } else {
            // X-Axis: Kabupatens
            $grouped = $rawData->groupBy('kabupaten_id');
            $sortedKabs = $rawData->unique('kabupaten_id')->sortBy(fn($item) => $item->kabupaten->kode_kab);

            foreach ($sortedKabs as $item) {
                $categories[] = "[{$item->kabupaten->kode_kab}] {$item->kabupaten->nama_kabupaten}";
                foreach ($availablePeriods as $p) {
                    $val = isset($grouped[$item->kabupaten_id])
                        ? $grouped[$item->kabupaten_id]->first(fn($v) => $v->period->year == $p['year'] && $v->period->quarter == $p['quarter'])
                        : null;
                    $series[$p['key']]['data'][] = $val ? (float) $val->value : 0;
                }
            }
            $coicopName = $rawData->first()->coicop->nama;
            $title = "Perbandingan {$coicopName}";
            if ($yearInfo !== 'all')
                $title .= " ({$yearInfo})";
        }

        if (empty($categories) || empty($series)) {
            return response()->json(['empty' => true, 'debug' => ['count' => $rawData->count(), 'mode' => $mode]]);
        }

        return response()->json([
            'categories' => $categories,
            'series' => array_values($series),
            'title' => $title,
            'mode' => $mode,
            'debug' => ['count' => $rawData->count()]
        ]);
    }

    public function destroyCoicop($id)
    {
        try {
            $coicop = Coicop::find($id);

            if (!$coicop) {
                return response()->json(['success' => false, 'message' => 'Data komoditas tidak ditemukan.'], 404);
            }

            // Check if ANY value exists in consumption_values table for this coicop.
            $hasUsage = ConsumptionValue::where('coicop_id', $id)->exists();

            if ($hasUsage) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kelompok ini sudah memiliki data nilai yang tersimpan.'
                ], 400);
            }

            $coicop->delete();

            return response()->json(['success' => true, 'message' => 'Data komoditas berhasil dihapus!']);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function destroyConsumption(Request $request)
    {
        $request->validate([
            'year' => 'required|numeric',
            'quarter' => 'required|numeric|min:1|max:4',
            'kabupaten_id' => 'required|exists:tb_kabupaten,id',
        ]);

        try {
            $period = Period::where('year', $request->year)
                ->where('quarter', $request->quarter)
                ->first();

            if (!$period) {
                return response()->json(['success' => false, 'message' => 'Data untuk periode tersebut belum ada.']);
            }

            ConsumptionValue::where('period_id', $period->id)
                ->where('kabupaten_id', $request->kabupaten_id)
                ->delete();

            return response()->json(['success' => true, 'message' => 'Seluruh data nilai konsumsi untuk wilayah dan periode terpilih telah dihapus!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }
}
