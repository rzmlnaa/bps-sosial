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

        // if (auth()->check() == false) {
        //     return redirect('/price-range')->with('error', 'Silahkan login terlebih dahulu.');
        // }

        $user = Auth::user();
        // if (!$user->kabupaten || $user->kabupaten->kode_kab === '6100') {
        //     return redirect()->back()->with('error', 'Akses Ditolak: Hanya BPS Kabupaten/Kota yang dapat mengakses input nilai RH Kabupaten.');
        // }

        // dd($user->kabupaten->id, $request->query('kabupaten_id'));

        if ($user->kabupaten->kode_kab !== '6100') {
            if ($user->kabupaten->id != $request->query('kabupaten_id')) {
                return redirect('/price-range/input-nilai?rh_tahun_id=' . $request->query('rh_tahun_id') . '&kabupaten_id=' . $user->kabupaten->id . '&revision_id=' . $request->query('revision_id'))->with('error', 'Anda tidak dapat mengakses data ini.');
            }
        }



        $activeYear = RhTahun::where('is_active', true)->first();

        if (!$activeYear) {
            return back()->with('error', 'Tidak dapat input nilai RH Kabupaten, karena Admin Provinsi belum mengaktifkan tahun RH.');
        }

        $kabupatens = Kabupaten::orderBy('kode_kab', 'asc')->get();
        if ($user->kabupaten->kode_kab == '6100') {
            $kabupatens = $kabupatens->where('kode_kab', '!=', '6100');
        }
        $selectedKabupatenId = $request->kabupaten_id ?? ($kabupatens->first()->id ?? null);

        // 1. Fetch ALL Revisions for Calculation (Ascending)
        $allRevisions = RhPerubahanHeader::where('rh_tahun_id', $activeYear->id)
            ->orderBy('tanggal_perubahan', 'asc')
            ->get();
        // Latest revision ID is the last one in the time series
        $latestRevisionId = $allRevisions->last()->id ?? null;

        // 2. Fetch Master Values (Now from PerubahanDetail where header is null)
        // We select min_edit as min_nilai to keep compatibility with view or we just update view?
        // Let's select aliases to make it compatible with existing View logic for Master
        $masterNilai = RhPerubahanDetail::where('rh_tahun_id', $activeYear->id)
            ->where('kabupaten_id', $selectedKabupatenId)
            ->whereNull('rh_perubahan_header_id')
            ->select('*', 'min_edit as min_nilai', 'max_edit as max_nilai')
            ->get()
            ->keyBy('komoditas_id');

        // 3. Fetch ALL Revision Details for this Year/Kabupaten (where header is NOT null)
        $allDetailsRaw = RhPerubahanDetail::whereIn('rh_perubahan_header_id', $allRevisions->pluck('id'))
            ->where('kabupaten_id', $selectedKabupatenId)
            ->get()
            ->groupBy('rh_perubahan_header_id');

        // 4. Compute Effective State 
        $effectiveValues = collect();
        $currentState = [];

        // --- Fetch Previous Year's Final State ---
        $prevYear = RhTahun::where('tahun', $activeYear->tahun - 1)->first();
        $prevYearFinal = [];
        $prevYearLabel = null;

        if ($prevYear) {
            $prevYearFinal = $this->getFinalStateForYear($prevYear->tahun, $selectedKabupatenId);
            $prevRevisionsCount = RhPerubahanHeader::where('rh_tahun_id', $prevYear->id)->count();

            if ($prevRevisionsCount > 0) {
                $lastRev = RhPerubahanHeader::where('rh_tahun_id', $prevYear->id)->orderBy('tanggal_perubahan', 'desc')->first();
                $prevYearLabel = "Akhir " . $prevYear->tahun . " (" . \Carbon\Carbon::parse($lastRev->tanggal_perubahan)->translatedFormat('d M') . ")";
            } else {
                $prevYearLabel = "Master " . $prevYear->tahun;
            }
        }
        // ----------------------------------------------------

        $allKomoditasIds = \App\Models\Komoditas::pluck('id')->toArray();

        foreach ($allKomoditasIds as $komId) {
            $mVal = $masterNilai->get($komId);
            $pVal = $prevYearFinal[$komId] ?? null;

            // Check if Master has explicit minimal valid data
            // Note: mVal is now RhPerubahanDetail, so we check min_edit/max_edit (aliased as min_nilai/max_nilai)
            $hasMasterData = $mVal && ($mVal->min_nilai !== null || $mVal->max_nilai !== null);

            if ($hasMasterData) {
                $currentState[$komId] = [
                    'min' => $mVal->min_nilai,
                    'max' => $mVal->max_nilai,
                    'alasan' => $mVal->alasan
                ];
            } elseif ($pVal) {
                $currentState[$komId] = [
                    'min' => $pVal['min'],
                    'max' => $pVal['max'],
                    'alasan' => $pVal['alasan']
                ];
            }
        }
        $initialState = $currentState;

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

                    // Add verification details
                    $currentState[$dt->komoditas_id]['verification_status'] = $dt->verification_status;
                    $currentState[$dt->komoditas_id]['rejection_reason'] = $dt->rejection_reason;
                }
            }
            // Snapshot state for this revision
            $effectiveValues->put($rev->id, $currentState);
        }

        // 5. Viewing Logic
        $displayRevisions = collect();
        $selectedRevisionId = $request->query('revision_id');

        // Default to latest revision if not specified
        if (!$request->has('revision_id') && $allRevisions->isNotEmpty()) {
            $selectedRevisionId = (string) $allRevisions->last()->id;
        }

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
        }

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

        $categories = KategoriKomoditas::with([
            'komoditas' => function ($query) {
                $query->orderBy('order_number', 'asc');
            }
        ])->get();
        // Rename $allRevisions to $revisions to match view variable expectation
        $revisions = $allRevisions;



        // 6. Gather Rejected Items for Summary Alert
        $rejectedSummary = collect();

        // Master Rejections
        foreach ($masterNilai as $item) {
            if ($item->verification_status === 'rejected') {
                $rejectedSummary->push((object) [
                    'id' => $item->id,
                    'source' => 'Master Nilai',
                    'komoditas_id' => $item->komoditas_id,
                    'komoditas_nama' => $item->komoditas->nama_komoditas ?? '-',
                    'min' => $item->min_edit,
                    'max' => $item->max_edit,
                    'reason' => $item->rejection_reason,
                    'verified_at' => $item->verified_at
                ]);
            }
        }

        // Revision Rejections
        foreach ($allDetailsRaw as $headerId => $details) {
            $header = $allRevisions->where('id', $headerId)->first();
            $label = $header ? $header->label : 'Perubahan';

            foreach ($details as $item) {
                if ($item->verification_status === 'rejected') {
                    $rejectedSummary->push((object) [
                        'id' => $item->id,
                        'source' => $label,
                        'komoditas_id' => $item->komoditas_id,
                        'komoditas_nama' => $item->komoditas->nama_komoditas ?? '-',
                        'min' => $item->min_edit,
                        'max' => $item->max_edit,
                        'reason' => $item->rejection_reason,
                        'verified_at' => $item->verified_at
                    ]);
                }
            }
        }

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
            'latestRevisionId', // To determine readonly status
            'initialState',
            'prevYearFinal',
            'prevYearLabel',
            'rejectedSummary'
        ));
    }

    public function save(Request $request)
    {
        // Strict Access: Only Province User (6100)
        // if (auth()->check() == false) {
        //     return redirect('/price-range')->with('error', 'Silahkan login terlebih dahulu.');
        // }
        $user = Auth::user();
        // if (!$user->kabupaten || $user->kabupaten->kode_kab === '6100') {
        //     return redirect()->back()->with('error', 'Akses Ditolak: Hanya BPS Kabupaten/Kota yang dapat mengakses input nilai RH Kabupaten.');
        // }

        if ($user->kabupaten->kode_kab !== '6100') {
            if ($user->kabupaten->id != $request->input('kabupaten_id')) {
                return redirect('/price-range/input-nilai?rh_tahun_id=' . $request->input('rh_tahun_id') . '&kabupaten_id=' . $user->kabupaten->id . '&revision_id=' . $request->input('revision_id'))->with('error', 'Anda tidak dapat mengakses data ini.');
            }
        }

        $activeYear = RhTahun::where('id', $request->input('rh_tahun_id'))->first();
        if ($activeYear->is_active == false) {
            return redirect('/price-range')->with('error', 'Admin telah menonaktifkan tahun RH ' . $activeYear->tahun . '. Silakan gunakan tahun yang aktif.');
        }

        $request->validate([
            'rh_tahun_id' => 'required|exists:tb_rh_tahun,id',
            'kabupaten_id' => 'required|exists:tb_kabupaten,id',
        ]);

        $kabupatenId = $request->kabupaten_id;
        $tahunId = $request->rh_tahun_id;
        $userId = Auth::id() ?? 1;

        $cleanNumber = function ($val) {
            return str_replace('.', '', $val);
        };

        // Clean Inputs
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

        $komoditasIds = [];
        if ($request->has('master'))
            $komoditasIds = array_keys($request->master);
        if ($request->has('revision')) {
            foreach ($request->revision as $revData) {
                $komoditasIds = array_merge($komoditasIds, array_keys($revData));
            }
        }
        $komoditasIds = array_unique($komoditasIds);
        $komoditasLimits = \App\Models\Komoditas::whereIn('id', $komoditasIds)->pluck('batas_selisih_harga', 'id');

        // Validation
        if ($request->has('master')) {
            foreach ($request->master as $komoditasId => $vals) {
                if ($vals['min'] !== null && $vals['max'] !== null && $vals['min'] !== '' && $vals['max'] !== '') {
                    $min = (float) $vals['min'];
                    $max = (float) $vals['max'];
                    $batas = $komoditasLimits[$komoditasId] ?? 0;
                    if ($max <= $min)
                        return back()->with('error', 'Master Nilai: Max <= Min')->withInput();
                    if (($max - $min) > $batas && empty($vals['alasan']))
                        return back()->with('error', 'Master Nilai: Alasan wajib (Batas: ' . number_format($batas, 0, ',', '.') . ')')->withInput();
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
                        if ($max <= $min)
                            return back()->with('error', 'Perubahan: Max <= Min')->withInput();
                        if (($max - $min) > $batas && empty($vals['alasan']))
                            return back()->with('error', 'Perubahan: Alasan wajib (Batas: ' . number_format($batas, 0, ',', '.') . ')')->withInput();
                    }
                }
            }
        }

        // Save Master (Header = NULL)
        if ($request->has('master')) {
            foreach ($request->master as $komoditasId => $vals) {
                $min = isset($vals['min']) && $vals['min'] !== '' ? (float) $vals['min'] : null;
                $max = isset($vals['max']) && $vals['max'] !== '' ? (float) $vals['max'] : null;
                $batas = $komoditasLimits[$komoditasId] ?? 0;

                $status = 'approved';
                // Trigger verification only if 'alasan' is provided
                if (!empty($vals['alasan'])) {
                    $status = 'pending';
                }

                $cekPerubahanDetail = RhPerubahanDetail::whereNotNull('alasan')->whereIn('verification_status', ['approved', 'rejected'])->where('kabupaten_id', $kabupatenId)->where('komoditas_id', $komoditasId)->where('rh_tahun_id', $tahunId)->first();

                if ($cekPerubahanDetail != null) {
                    if ($cekPerubahanDetail->min_edit == $min && $cekPerubahanDetail->max_edit == $max && $cekPerubahanDetail->alasan === $vals['alasan']) {
                        continue;
                    }
                }
                if ($min == null && $max == null) {
                    // Hapus record jika sudah ada, untuk mengosongkan tanpa menyimpan nilai null
                    RhPerubahanDetail::where([
                        'rh_tahun_id' => $tahunId,
                        'rh_perubahan_header_id' => null, // MASTER
                        'kabupaten_id' => $kabupatenId,
                        'komoditas_id' => $komoditasId,
                    ])->delete();
                    continue; // Skip updateOrCreate agar tidak insert null
                }

                RhPerubahanDetail::updateOrCreate(
                    [
                        'rh_tahun_id' => $tahunId,
                        'rh_perubahan_header_id' => null, // MASTER
                        'kabupaten_id' => $kabupatenId,
                        'komoditas_id' => $komoditasId,
                    ],
                    [
                        'min_edit' => $min,
                        'max_edit' => $max,
                        'alasan' => $vals['alasan'] ?? null,
                        'user_id_add' => $userId,
                        'verification_status' => $status,
                    ]
                );
            }
        }

        // Save Revision (Header != NULL)
        if ($request->has('revision')) {
            foreach ($request->revision as $revHeaderId => $komoditasData) {
                foreach ($komoditasData as $komoditasId => $vals) {
                    $min = isset($vals['min']) && $vals['min'] !== '' ? (float) $vals['min'] : null;
                    $max = isset($vals['max']) && $vals['max'] !== '' ? (float) $vals['max'] : null;
                    $batas = $komoditasLimits[$komoditasId] ?? 0;

                    $status = 'approved';
                    // Trigger verification only if 'alasan' is provided
                    if (!empty($vals['alasan'])) {
                        $status = 'pending';
                    }

                    // For revisions, rh_tahun_id is derived, but we should store it too as per new schema
                    $revHeaderExists = RhPerubahanHeader::where('id', $revHeaderId)->exists();
                    if (!$revHeaderExists) {
                        return back()->with('error', 'Data Header Perubahan (ID: ' . $revHeaderId . ') tidak ditemukan. Mohon refresh halaman dan coba lagi.')->withInput();
                    }

                    $cekPerubahanDetail = RhPerubahanDetail::whereNotNull('alasan')->whereIn('verification_status', ['approved', 'rejected'])->where('rh_perubahan_header_id', $revHeaderId)->where('kabupaten_id', $kabupatenId)->where('komoditas_id', $komoditasId)->where('rh_tahun_id', $tahunId)->first();

                    if ($cekPerubahanDetail != null) {
                        if ($cekPerubahanDetail->min_edit == $min && $cekPerubahanDetail->max_edit == $max && $cekPerubahanDetail->alasan === $vals['alasan']) {
                            continue;
                        }
                    }
                    // dd($request);
                    if ($min == null && $max == null) {
                        // Hapus record jika sudah ada, untuk mengosongkan tanpa menyimpan nilai null
                        RhPerubahanDetail::where([
                            'rh_perubahan_header_id' => $revHeaderId,
                            'kabupaten_id' => $kabupatenId,
                            'komoditas_id' => $komoditasId,
                        ])->delete();
                        continue; // Skip updateOrCreate agar tidak insert null
                    }

                    RhPerubahanDetail::updateOrCreate(
                        [
                            'rh_perubahan_header_id' => $revHeaderId,
                            'kabupaten_id' => $kabupatenId,
                            'komoditas_id' => $komoditasId,
                        ],
                        [
                            'rh_tahun_id' => $tahunId, // ADDED
                            'min_edit' => $min,
                            'max_edit' => $max,
                            'alasan' => $vals['alasan'] ?? null,
                            'user_id_add' => $userId,
                            'verification_status' => $status,
                        ]
                    );
                }
            }
        }

        $previousUrl = url()->previous();
        if (!str_contains($previousUrl, '#table')) {
            $previousUrl .= '#table';
        }
        return redirect($previousUrl)->with('success', 'Data rentang harga berhasil disimpan.');
    }


    private function getFinalStateForYear($year, $kabupatenId)
    {
        if ($year < 2020)
            return [];

        $baseState = $this->getFinalStateForYear($year - 1, $kabupatenId);
        $rhTahun = RhTahun::where('tahun', $year)->first();

        if (!$rhTahun)
            return $baseState;

        // 1. Overlay Master Values (Header IS NULL)
        $masterNilai = RhPerubahanDetail::where('rh_tahun_id', $rhTahun->id)
            ->whereNull('rh_perubahan_header_id')
            ->where('kabupaten_id', $kabupatenId)
            ->get();

        foreach ($masterNilai as $m) {
            // Note: master use min_edit/max_edit columns in DB now
            if ($m->min_edit !== null || $m->max_edit !== null) {
                $baseState[$m->komoditas_id] = [
                    'min' => $m->min_edit,
                    'max' => $m->max_edit,
                    'alasan' => $m->alasan,
                ];
            }
        }

        // 2. Apply Revisions
        $revisions = RhPerubahanHeader::where('rh_tahun_id', $rhTahun->id)
            ->orderBy('tanggal_perubahan', 'asc')
            ->get();

        if ($revisions->isNotEmpty()) {
            $details = RhPerubahanDetail::whereIn('rh_perubahan_header_id', $revisions->pluck('id'))
                ->where('kabupaten_id', $kabupatenId)
                ->get()
                ->groupBy('rh_perubahan_header_id');

            foreach ($revisions as $rev) {
                if (isset($details[$rev->id])) {
                    foreach ($details[$rev->id] as $det) {
                        $komId = $det->komoditas_id;
                        if (!isset($baseState[$komId]))
                            $baseState[$komId] = ['min' => null, 'max' => null, 'alasan' => null];

                        if ($det->min_edit !== null)
                            $baseState[$komId]['min'] = $det->min_edit;
                        if ($det->max_edit !== null)
                            $baseState[$komId]['max'] = $det->max_edit;

                        if ($det->alasan !== null) {
                            $baseState[$komId]['alasan'] = $det->alasan;
                        } elseif ($det->min_edit !== null || $det->max_edit !== null) {
                            // If value changed but reason not provided (and not required/set), we clear reason? 
                            // Or keep old? existing logic was clearing it if values set.
                            $baseState[$komId]['alasan'] = null;
                        }
                    }
                }
            }
        }
        return $baseState;
    }
}
