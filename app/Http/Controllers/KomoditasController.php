<?php

namespace App\Http\Controllers;

use App\Models\Komoditas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KomoditasController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'kategori_id' => 'required|exists:tb_kategori_komoditas,id',
            'raw_data' => 'required|string',
        ]);

        $kategori_id = $request->kategori_id;
        // Split by line
        $lines = explode("\n", str_replace("\r", "", $request->raw_data));

        $count = 0;
        $processedNames = [];
        foreach ($lines as $line) {
            $parts = explode("\t", trim($line));

            $name = trim($parts[0] ?? '');
            $satuan = trim($parts[1] ?? 'Kg');

            if (!empty($name)) {
                $komoditas = Komoditas::where('kategori_id', $kategori_id)
                    ->where('nama_komoditas', $name)
                    ->first();

                if ($komoditas) {
                    $komoditas->update([
                        'satuan' => $satuan,
                        'user_id_update' => Auth::id() ?? 1,
                    ]);
                } else {
                    Komoditas::create([
                        'kategori_id' => $kategori_id,
                        'nama_komoditas' => $name,
                        'satuan' => $satuan,
                        'user_id_add' => Auth::id() ?? 1,
                        'user_id_update' => Auth::id() ?? 1,
                    ]);
                }

                $processedNames[] = $name;
                $count++;
            }
        }

        // Delete commodities that were in this category but are no longer in the provided list
        if (!empty($processedNames)) {
            Komoditas::where('kategori_id', $kategori_id)
                ->whereNotIn('nama_komoditas', $processedNames)
                ->delete();
        }

        return redirect()->back()->with('success', $count . ' Komoditas berhasil disimpan.');
    }

    public function destroy($id)
    {
        $komoditas = Komoditas::findOrFail($id);
        $komoditas->delete();
        return redirect()->back()->with('success', 'Komoditas berhasil dihapus.');
    }

    public function getByCategory($kategori_id)
    {
        $data = Komoditas::where('kategori_id', $kategori_id)
            ->with(['userAdd', 'userUpdate'])
            ->get();

        return response()->json($data);
    }

    public function clearData(Request $request)
    {
        $request->validate([
            'kategori_id' => 'required|exists:tb_kategori_komoditas,id',
        ]);

        Komoditas::where('kategori_id', $request->kategori_id)->delete();

        return redirect()->back()->with('success', 'Data komoditas untuk kategori tersebut telah dikosongkan.');
    }
}
