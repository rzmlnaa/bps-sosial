<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
        return view('seruti.input', compact('coicops'));
    }

    public function store(Request $request)
    {
        // Placeholder for future consumption data store
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
}
