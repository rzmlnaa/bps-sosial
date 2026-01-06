<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RhTahun;
use App\Models\RhPerubahanHeader;
use App\Models\Kabupaten;
use App\Models\KategoriKomoditas;
use App\Models\RhMasterNilai;
use App\Models\RhPerubahanDetail;
use Illuminate\Support\Facades\Auth;

class RhNilaiController extends Controller
{
    public function index(Request $request)
    {
        $activeYear = RhTahun::where('is_active', true)->first();

        if (!$activeYear) {
            return redirect()->route('price-range.input')->with('error', 'Silakan aktifkan salah satu tahun RH terlebih dahulu.');
        }

        $kabupatens = Kabupaten::all();
        $selectedKabupatenId = $request->kabupaten_id ?? ($kabupatens->first()->id ?? null);

        $revisions = RhPerubahanHeader::where('rh_tahun_id', $activeYear->id)->orderBy('tanggal_perubahan', 'desc')->get();
        $selectedRevisionId = $request->revision_id;

        $categories = KategoriKomoditas::with(['komoditas'])->get();

        // Fetch existing master values
        $masterNilai = RhMasterNilai::where('rh_tahun_id', $activeYear->id)
            ->where('kabupaten_id', $selectedKabupatenId)
            ->get()
            ->keyBy('komoditas_id');

        // Fetch existing revision values if revision selected
        $revisionNilai = collect();
        if ($selectedRevisionId) {
            $revisionNilai = RhPerubahanDetail::where('rh_perubahan_header_id', $selectedRevisionId)
                ->where('kabupaten_id', $selectedKabupatenId)
                ->get()
                ->keyBy('komoditas_id');
        }

        return view('price-range.input-nilai', compact(
            'activeYear',
            'kabupatens',
            'selectedKabupatenId',
            'revisions',
            'selectedRevisionId',
            'categories',
            'masterNilai',
            'revisionNilai'
        ));
    }

    public function save(Request $request)
    {
        $request->validate([
            'rh_tahun_id' => 'required|exists:tb_rh_tahun,id',
            'kabupaten_id' => 'required|exists:tb_kabupaten,id',
        ]);

        $kabupatenId = $request->kabupaten_id;
        $tahunId = $request->rh_tahun_id;
        $revisionId = $request->revision_id;
        $userId = Auth::id() ?? 1;

        // Save Master Values
        if ($request->has('master')) {
            foreach ($request->master as $komoditasId => $vals) {
                // Only save if at least one value is filled OR if it already exists (to update to null)
                // Actually updateOrCreate handles nulls fine.
                RhMasterNilai::updateOrCreate(
                    [
                        'rh_tahun_id' => $tahunId,
                        'kabupaten_id' => $kabupatenId,
                        'komoditas_id' => $komoditasId,
                    ],
                    [
                        'min_nilai' => $vals['min'] !== null && $vals['min'] !== '' ? $vals['min'] : null,
                        'max_nilai' => $vals['max'] !== null && $vals['max'] !== '' ? $vals['max'] : null,
                        'user_id_add' => $userId,
                    ]
                );
            }
        }

        // Save Revision Values if revision header is selected
        if ($revisionId && $request->has('revision')) {
            foreach ($request->revision as $komoditasId => $vals) {
                RhPerubahanDetail::updateOrCreate(
                    [
                        'rh_perubahan_header_id' => $revisionId,
                        'kabupaten_id' => $kabupatenId,
                        'komoditas_id' => $komoditasId,
                    ],
                    [
                        'min_edit' => $vals['min'] !== null && $vals['min'] !== '' ? $vals['min'] : null,
                        'max_edit' => $vals['max'] !== null && $vals['max'] !== '' ? $vals['max'] : null,
                        'user_id_add' => $userId,
                    ]
                );
            }
        }

        return back()->with('success', 'Data rentang harga berhasil disimpan.');
    }
}
