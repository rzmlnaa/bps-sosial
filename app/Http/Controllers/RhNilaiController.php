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

        $kabupatens = Kabupaten::orderBy('kode_kab', 'asc')->get();
        $selectedKabupatenId = $request->kabupaten_id ?? ($kabupatens->first()->id ?? null);

        // 1. Fetch ALL Revisions for Calculation (Ascending)
        $allRevisions = RhPerubahanHeader::where('rh_tahun_id', $activeYear->id)
            ->orderBy('tanggal_perubahan', 'asc')
            ->get();
        // Latest revision ID is the last one in the time series
        $latestRevisionId = $allRevisions->last()->id ?? null;

        // 2. Fetch Master Values
        $masterNilai = RhMasterNilai::where('rh_tahun_id', $activeYear->id)
            ->where('kabupaten_id', $selectedKabupatenId)
            ->get()
            ->keyBy('komoditas_id');

        // 3. Fetch ALL Revision Details for this Year/Kabupaten to compute state
        $allDetailsRaw = RhPerubahanDetail::whereIn('rh_perubahan_header_id', $allRevisions->pluck('id'))
            ->where('kabupaten_id', $selectedKabupatenId)
            ->get()
            ->groupBy('rh_perubahan_header_id');

        // 4. Compute Effective State for EACH revision in order
        $effectiveValues = collect(); // [rev_id => [kom_id => ['min' => val, 'max' => val]]]
        $currentState = [];

        // Initialize state with Master
        foreach ($masterNilai as $komId => $val) {
            $currentState[$komId] = [
                'min' => $val->min_nilai,
                'max' => $val->max_nilai,
                'alasan' => $val->alasan
            ];
        }

        foreach ($allRevisions as $rev) {
            $details = $allDetailsRaw->get($rev->id);
            if ($details) {
                foreach ($details as $dt) {
                    // Update state if value is explicitly set (edit)
                    // If null, it means no change -> keep previous state
                    // Logic: The DB stores raw edit. 
                    if ($dt->min_edit !== null) {
                        $currentState[$dt->komoditas_id]['min'] = $dt->min_edit;
                    }
                    if ($dt->max_edit !== null) {
                        $currentState[$dt->komoditas_id]['max'] = $dt->max_edit;
                    }
                    if ($dt->alasan !== null) {
                        $currentState[$dt->komoditas_id]['alasan'] = $dt->alasan;
                    } elseif ($dt->min_edit !== null || $dt->max_edit !== null) {
                        $currentState[$dt->komoditas_id]['alasan'] = null;
                    }
                }
            }
            // Snapshot state for this revision
            $effectiveValues->put($rev->id, $currentState);
        }

        // 5. Determine Revisions to Display (View Logic)
        $displayRevisions = collect();
        $selectedRevisionId = $request->revision_id;

        if ($selectedRevisionId === 'all') {
            $displayRevisions = $allRevisions;
        } elseif ($selectedRevisionId) {
            $currentIndex = $allRevisions->search(function ($item) use ($selectedRevisionId) {
                return $item->id == $selectedRevisionId;
            });

            if ($currentIndex !== false) {
                if ($currentIndex > 0) {
                    $displayRevisions->push($allRevisions[$currentIndex - 1]);
                }
                $displayRevisions->push($allRevisions[$currentIndex]);
            }
        } else {
            // Default: Show latest if exists (optional, or show none/master only)
            // Existing logic seemed to default to none if no ID provided?
            // Let's keep existing behavior: if no params, revisions empty. 
            // Wait, existing behavior showed empty revisions if none selected.
        }

        // 6. Map Raw Input Values (for Edit Fields) - Only needed for Displayed Revisions
        $allRevisionNilai = collect();
        if ($displayRevisions->isNotEmpty()) {
            foreach ($displayRevisions as $rev) {
                // We use the raw details fetched earlier
                $raw = $allDetailsRaw->get($rev->id);
                if ($raw) {
                    $allRevisionNilai->put($rev->id, $raw->keyBy('komoditas_id'));
                }
            }
        }

        $categories = KategoriKomoditas::with(['komoditas'])->get();
        // Rename $allRevisions to $revisions to match view variable expectation
        $revisions = $allRevisions;

        return view('price-range.input-nilai', compact(
            'activeYear',
            'kabupatens',
            'selectedKabupatenId',
            'revisions', // Keep original list for dropdown (which is $allRevisions basically)
            'displayRevisions',
            'selectedRevisionId',
            'categories',
            'masterNilai',
            'effectiveValues', // Calculated State
            'allRevisionNilai', // Raw Values for inputs
            'latestRevisionId' // To determine readonly status
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

        // Function to remove thousands separators
        $cleanNumber = function ($val) {
            return str_replace('.', '', $val);
        };

        // Clean Master Input
        if ($request->has('master')) {
            $master = $request->master;
            foreach ($master as $key => $vals) {
                if (isset($vals['min']))
                    $master[$key]['min'] = $cleanNumber($vals['min']);
                if (isset($vals['max']))
                    $master[$key]['max'] = $cleanNumber($vals['max']);
            }
            $request->merge(['master' => $master]);
        }

        // Clean Revision Input
        if ($request->has('revision')) {
            $revision = $request->revision;
            foreach ($revision as $revId => $items) {
                foreach ($items as $komId => $vals) {
                    if (isset($vals['min']))
                        $revision[$revId][$komId]['min'] = $cleanNumber($vals['min']);
                    if (isset($vals['max']))
                        $revision[$revId][$komId]['max'] = $cleanNumber($vals['max']);
                }
            }
            $request->merge(['revision' => $revision]);
        }

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
