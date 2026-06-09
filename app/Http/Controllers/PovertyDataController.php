<?php

namespace App\Http\Controllers;

use App\Models\NilaiKemiskinan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PovertyDataController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'kabupaten_id' => 'required|exists:tb_kabupaten,id',
            'variabel_id' => 'required|exists:tb_variabel_kemiskinan,id',
            'raw_data' => 'required|string',
        ], [
            'kabupaten_id.required' => 'Wilayah wajib dipilih.',
            'variabel_id.required' => 'Variabel wajib dipilih.',
            'raw_data.required' => 'Data nilai wajib diisi (paste dari excel).',
        ]);


        // Parse data from textarea
        $lines = preg_split('/\r\n|\r|\n/', $request->raw_data);
        $values = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '')
                continue;

            // HANYA jika ada titik, hapus titik (pemisah ribuan)
            if (str_contains($line, '.')) {
                $line = str_replace('.', '', $line);
            }

            // Handle decimal format (replace comma with dot)
            $value = str_replace(',', '.', $line);
            if (is_numeric($value)) {
                $values[] = floatval($value);
            }
        }
        $countValues = count($values);

        if ($countValues > 20) {
            return redirect()->back()->with('error', 'Data tidak boleh lebih dari 20.');
        }
        //dd($values, $request->raw_data);
        if (empty($values)) {
            return redirect()->back()->with('error', 'Tidak ada data valid yang ditemukan.');
        }
        try {
            DB::beginTransaction();

            // Delete existing data for this selection if any (to support "edit" by re-pasting)
            NilaiKemiskinan::where('kabupaten_id', $request->kabupaten_id)
                ->where('variabel_kemiskinan_id', $request->variabel_id)
                ->delete();

            foreach ($values as $index => $nilai) {
                NilaiKemiskinan::create([
                    'kabupaten_id' => $request->kabupaten_id,
                    'variabel_kemiskinan_id' => $request->variabel_id,
                    'persentil' => $index + 1,
                    'nilai' => $nilai,
                ]);
            }
            DB::commit();

            return redirect()->back()->with([
                'success' => $countValues . ' data berhasil disimpan!',
                'last_kabupaten_id' => $request->kabupaten_id,
                'last_variabel_id' => $request->variabel_id,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage());
        }
    }

    public function getData(Request $request, $kabupaten_id)
    {

        $tahun = $request->get('tahun', 'all');

        $query = NilaiKemiskinan::with(['variabelKemiskinan', 'kabupaten']);

        if ($kabupaten_id !== 'all') {
            $query->where('kabupaten_id', $kabupaten_id);
        }

        if ($tahun !== 'all') {
            $query->whereHas('variabelKemiskinan', function ($q) use ($tahun) {
                $q->where('tahun', $tahun);
            });
        }

        $data = $query->get()->groupBy('persentil');

        $variabelsQuery = \App\Models\VariabelKemiskinan::query();
        if ($tahun !== 'all') {
            $variabelsQuery->where('tahun', $tahun);
        }
        $variabels = $variabelsQuery->get();
        $kabupatens = \App\Models\Kabupaten::withoutIndonesia()->orderBy('kode_kab', 'asc')->get();

        return response()->json([
            'data' => $data,
            'variabels' => $variabels,
            'kabupatens' => $kabupatens
        ]);
    }

    public function getRawData($kabupaten_id, $variabel_id)
    {

        $data = NilaiKemiskinan::where('kabupaten_id', $kabupaten_id)
            ->where('variabel_kemiskinan_id', $variabel_id)
            ->orderBy('persentil')
            ->pluck('nilai');

        return response()->json($data);
    }

    public function exportToCSV($kabupaten_id)
    {
        $kabupaten = \App\Models\Kabupaten::withoutIndonesia()->findOrFail($kabupaten_id);
        $variabels = \App\Models\VariabelKemiskinan::all();
        $data = NilaiKemiskinan::where('kabupaten_id', $kabupaten_id)
            ->get()
            ->groupBy('persentil');

        $filename = "Data_Kemiskinan_" . str_replace(' ', '_', $kabupaten->nama_kabupaten) . "_" . date('Ymd_His') . ".csv";

        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $columns = ['Persentil'];
        foreach ($variabels as $v) {
            $columns[] = $v->nama_variabel . " " . $v->tahun;
        }

        $callback = function () use ($data, $columns, $variabels) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            $persentils = $data->keys()->sort();
            foreach ($persentils as $p) {
                $row = [$p];
                foreach ($variabels as $v) {
                    $record = $data[$p]->where('variabel_kemiskinan_id', $v->id)->first();
                    $row[] = $record ? $record->nilai : '';
                }
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
    public function clearData(Request $request)
    {
        $request->validate([
            'kabupaten_id' => 'required|exists:tb_kabupaten,id',
            'variabel_id' => 'required|exists:tb_variabel_kemiskinan,id',
        ]);

        try {
            NilaiKemiskinan::where('kabupaten_id', $request->kabupaten_id)
                ->where('variabel_kemiskinan_id', $request->variabel_id)
                ->delete();

            return redirect()->back()->with([
                'success' => 'Data untuk wilayah dan variabel terpilih berhasil dikosongkan.',
                'last_kabupaten_id' => $request->kabupaten_id,
                'last_variabel_id' => $request->variabel_id,
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat mengosongkan data: ' . $e->getMessage());
        }
    }
}
