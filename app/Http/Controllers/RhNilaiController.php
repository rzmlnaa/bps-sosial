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

        $revisions = RhPerubahanHeader::where('rh_tahun_id', $activeYear->id)->orderBy('tanggal_perubahan', 'asc')->get();
        $selectedRevisionId = $request->revision_id;

        $categories = KategoriKomoditas::with(['komoditas'])->get();

        // Fetch existing master values
        $masterNilai = RhMasterNilai::where('rh_tahun_id', $activeYear->id)
            ->where('kabupaten_id', $selectedKabupatenId)
            ->get()
            ->keyBy('komoditas_id');

        // Fetch revision values
        $revisionNilai = collect(); // For single revision selection
        $allRevisionNilai = collect(); // For 'all' revisions selection

        if ($selectedRevisionId === 'all') {
            $allRevisionNilai = RhPerubahanDetail::whereIn('rh_perubahan_header_id', $revisions->pluck('id'))
                ->where('kabupaten_id', $selectedKabupatenId)
                ->get()
                ->groupBy('rh_perubahan_header_id');

            // Map each revision to its komoditas_id for easy access
            $allRevisionNilai = $allRevisionNilai->map(function ($items) {
                return $items->keyBy('komoditas_id');
            });
        } elseif ($selectedRevisionId) {
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
            'revisionNilai',
            'allRevisionNilai'
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

        // Save Revision Values
        if ($revisionId === 'all') {
            if ($request->has('revision')) {
                foreach ($request->revision as $revHeaderId => $komoditasData) {
                    foreach ($komoditasData as $komoditasId => $vals) {
                        RhPerubahanDetail::updateOrCreate(
                            [
                                'rh_perubahan_header_id' => $revHeaderId,
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
            }
        } elseif ($revisionId) {
            if ($request->has('revision')) {
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
        }

        return back()->with('success', 'Data rentang harga berhasil disimpan.');
    }
}
