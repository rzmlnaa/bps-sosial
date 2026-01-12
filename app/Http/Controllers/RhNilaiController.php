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

        // Determine revisions to display
        $displayRevisions = collect();

        if ($selectedRevisionId === 'all') {
            $displayRevisions = $revisions;
        } elseif ($selectedRevisionId) {
            // Find current selection index
            $currentIndex = $revisions->search(function ($item) use ($selectedRevisionId) {
                return $item->id == $selectedRevisionId;
            });

            if ($currentIndex !== false) {
                // If there is a previous revision, add it
                if ($currentIndex > 0) {
                    $displayRevisions->push($revisions[$currentIndex - 1]);
                }
                // Add the currently selected revision
                $displayRevisions->push($revisions[$currentIndex]);
            }
        }

        // Fetch revision values for displayed revisions
        $revisionNilai = collect(); // Unused but kept for safety if view references it anywhere else
        $allRevisionNilai = collect();

        if ($displayRevisions->isNotEmpty()) {
            $allRevisionNilai = RhPerubahanDetail::whereIn('rh_perubahan_header_id', $displayRevisions->pluck('id'))
                ->where('kabupaten_id', $selectedKabupatenId)
                ->get()
                ->groupBy('rh_perubahan_header_id');

            // Map each revision to its komoditas_id for easy access
            $allRevisionNilai = $allRevisionNilai->map(function ($items) {
                return $items->keyBy('komoditas_id');
            });
        }

        return view('price-range.input-nilai', compact(
            'activeYear',
            'kabupatens',
            'selectedKabupatenId',
            'revisions', // Keep full list for filter dropdown
            'displayRevisions', // New list for table columns
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
        $userId = Auth::id() ?? 1;

        // Collect all Komoditas IDs to fetch their specific limits
        $komoditasIds = [];
        if ($request->has('master')) {
            $komoditasIds = array_keys($request->master);
        }
        if ($request->has('revision')) {
            foreach ($request->revision as $revData) {
                $komoditasIds = array_merge($komoditasIds, array_keys($revData));
            }
        }
        $komoditasIds = array_unique($komoditasIds);

        // Map Komoditas ID -> Batas Selisih Harga
        $komoditasLimits = \App\Models\Komoditas::whereIn('id', $komoditasIds)
            ->pluck('batas_selisih_harga', 'id');

        // Validation: Check if Max < Min and Alasan if Selisih > Batas
        if ($request->has('master')) {
            foreach ($request->master as $komoditasId => $vals) {
                if ($vals['min'] !== null && $vals['max'] !== null && $vals['min'] !== '' && $vals['max'] !== '') {
                    $min = (float) $vals['min'];
                    $max = (float) $vals['max'];
                    $batas = $komoditasLimits[$komoditasId] ?? 0; // Default to 0 if not set

                    if ($max < $min) {
                        return back()->with('error', 'Gagal menyimpan: Nilai MAX tidak boleh lebih kecil dari nilai MIN pada Master Nilai.')->withInput();
                    }
                    if (($max - $min) > $batas && empty($vals['alasan'])) {
                        return back()->with('error', 'Gagal menyimpan: Alasan wajib diisi jika selisih harga melebihi batas (' . number_format($batas, 0, ',', '.') . ') pada Master Nilai.')->withInput();
                    }
                }
            }
        }

        if ($request->has('revision')) {
            foreach ($request->revision as $revHeaderId => $komoditasData) {
                foreach ($komoditasData as $komoditasId => $vals) {
                    if ($vals['min'] !== null && $vals['max'] !== null && $vals['min'] !== '' && $vals['max'] !== '') {
                        $min = (float) $vals['min'];
                        $max = (float) $vals['max'];
                        $batas = $komoditasLimits[$komoditasId] ?? 0;

                        if ($max < $min) {
                            return back()->with('error', 'Gagal menyimpan: Nilai MAX tidak boleh lebih kecil dari nilai MIN pada data perubahan.')->withInput();
                        }
                        if (($max - $min) > $batas && empty($vals['alasan'])) {
                            return back()->with('error', 'Gagal menyimpan: Alasan wajib diisi jika selisih harga melebihi batas (' . number_format($batas, 0, ',', '.') . ') pada data perubahan.')->withInput();
                        }
                    }
                }
            }
        }

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
                        'alasan' => $vals['alasan'] ?? null,
                        'user_id_add' => $userId,
                    ]
                );
            }
        }

        // Save Revision Values
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
                            'alasan' => $vals['alasan'] ?? null,
                            'user_id_add' => $userId,
                        ]
                    );
                }
            }
        }

        return back()->with('success', 'Data rentang harga berhasil disimpan.');
    }
}
