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
        return view('seruti.index');
    }

    public function create()
    {
        $coicops = Coicop::orderBy('kode', 'asc')->get();
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
        ]);

        try {
            DB::beginTransaction();

            // Option: Clear existing or Update/Upsert
            // For "Master", usually safer to Upsert or Check existence. 
            // Given the requirement "paste excel", let's assuming adding/updating.

            foreach ($request->coicops as $row) {
                // Determine is_total based on keyword if not provided
                $isTotal = false;
                if (stripos($row['nama'], 'Total') !== false || stripos($row['nama'], 'Subtotal') !== false) {
                    $isTotal = true;
                }

                Coicop::updateOrCreate(
                    ['kode' => $row['kode']],
                    [
                        'nama' => $row['nama'],
                        'seruti' => $row['seruti'] ?? '',
                        'is_total' => $isTotal
                    ]
                );
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Data Master Kelompok berhasil disimpan!']);

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
}
