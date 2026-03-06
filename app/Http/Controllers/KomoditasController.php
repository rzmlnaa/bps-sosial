<?php

namespace App\Http\Controllers;

use App\Models\Komoditas;
use App\Models\RhPerubahanDetail;
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
            // Check for 3rd column (Batas Selisih Harga) if exists
            $batas = isset($parts[2]) && is_numeric(trim($parts[2])) ? (float) trim($parts[2]) : null;

            if (!empty($name)) {
                $komoditas = Komoditas::where('kategori_id', $kategori_id)
                    ->where('nama_komoditas', $name)
                    ->first();

                if ($komoditas) {
                    $komoditas->update([
                        'satuan' => $satuan,
                        'batas_selisih_harga' => $batas,
                        'user_id_update' => Auth::id() ?? 1,
                    ]);
                } else {
                    $newKomoditas = Komoditas::create([
                        'kategori_id' => $kategori_id,
                        'nama_komoditas' => $name,
                        'satuan' => $satuan,
                        'batas_selisih_harga' => $batas,
                        'user_id_add' => Auth::id() ?? 1,
                        'user_id_update' => Auth::id() ?? 1,
                    ]);
                    $newKomoditas->update(['order_number' => $newKomoditas->id]);
                }

                $processedNames[] = $name;
                $count++;
            }
        }

        // Delete commodities that were in this category but are no longer in the provided list
        // PROTECTION: Only delete if not in use in tb_rh_perubahan_detail with min_edit/max_edit not null
        if (!empty($processedNames)) {
            $toDelete = Komoditas::where('kategori_id', $kategori_id)
                ->whereNotIn('nama_komoditas', $processedNames)
                ->get();

            foreach ($toDelete as $item) {
                /** @var \App\Models\Komoditas $item */
                $isInUse = RhPerubahanDetail::where('komoditas_id', $item->id)
                    ->where(function ($query) {
                        $query->whereNotNull('min_edit')
                            ->orWhereNotNull('max_edit');
                    })->exists();

                if (!$isInUse) {
                    $item->delete();
                }
            }
        }

        // Save state to session
        session([
            'last_kategori_id' => $kategori_id,
            'active_tab' => $request->active_tab ?? 'pills-input-tab',
            'input_mode' => $request->input_mode ?? 'mode-paste'
        ]);

        return redirect()->back()->with('success', $count . ' Komoditas berhasil disimpan.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_komoditas' => 'required|string',
            'satuan' => 'nullable|string',
            'batas_selisih_harga' => 'nullable|numeric|min:0',
        ]);

        $komoditas = Komoditas::findOrFail($id);

        $komoditas->update([
            'nama_komoditas' => $request->nama_komoditas,
            'satuan' => $request->satuan,
            'batas_selisih_harga' => $request->batas_selisih_harga,
            'user_id_update' => Auth::id() ?? 1,
        ]);

        // Save state to session to keep tab open
        session([
            'last_kategori_id' => $komoditas->kategori_id,
            'active_tab' => 'pills-input-tab',
            'input_mode' => 'mode-paste' // or whatever default
        ]);

        return redirect()->back()->with('success', 'Komoditas berhasil diperbarui.');
    }

    public function destroy(Request $request, $id)
    {
        $komoditas = Komoditas::findOrFail($id);

        // Check if in use
        $isInUse = RhPerubahanDetail::where('komoditas_id', $id)
            ->where(function ($query) {
                $query->whereNotNull('min_edit')
                    ->orWhereNotNull('max_edit');
            })->exists();

        if ($isInUse) {
            return redirect()->back()->with('error', 'Komoditas ' . $komoditas->nama_komoditas . ' tidak dapat dihapus karena sudah memiliki data rincian perubahan harga.');
        }

        $kategori_id = $komoditas->kategori_id;
        $komoditas->delete();

        // Save state to session
        session([
            'last_kategori_id' => $kategori_id,
            'active_tab' => $request->active_tab ?? 'pills-input-tab',
            'input_mode' => $request->input_mode ?? 'mode-paste'
        ]);

        return redirect()->back()->with('success', 'Komoditas berhasil dihapus.');
    }

    public function getByCategory($kategori_id)
    {
        $data = Komoditas::where('kategori_id', $kategori_id)
            ->with(['userAdd', 'userUpdate'])
            ->orderBy('order_number', 'asc')
            ->get();

        return response()->json($data);
    }

    public function reorder(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:tb_komoditas,id',
        ]);

        foreach ($request->ids as $index => $id) {
            Komoditas::where('id', $id)->update(['order_number' => $index + 1]);
        }

        return response()->json(['success' => true]);
    }

    public function clearData(Request $request)
    {
        $request->validate([
            'kategori_id' => 'required|exists:tb_kategori_komoditas,id',
        ]);

        $komoditasList = Komoditas::where('kategori_id', $request->kategori_id)->get();
        $deletedCount = 0;
        $skippedCount = 0;

        foreach ($komoditasList as $item) {
            /** @var \App\Models\Komoditas $item */
            $isInUse = RhPerubahanDetail::where('komoditas_id', $item->id)
                ->where(function ($query) {
                    $query->whereNotNull('min_edit')
                        ->orWhereNotNull('max_edit');
                })->exists();

            if (!$isInUse) {
                $item->delete();
                $deletedCount++;
            } else {
                $skippedCount++;
            }
        }

        // Save state to session
        session([
            'last_kategori_id' => $request->kategori_id,
            'active_tab' => $request->active_tab ?? 'pills-input-tab',
            'input_mode' => $request->input_mode ?? 'mode-paste'
        ]);

        if ($skippedCount > 0) {
            return redirect()->back()->with('warning', $deletedCount . ' komoditas dihapus. ' . $skippedCount . ' komoditas dilewati karena sudah memiliki data rincian perubahan harga.');
        }

        return redirect()->back()->with('success', 'Data komoditas untuk kategori tersebut telah dikosongkan.');
    }
}
