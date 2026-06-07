<?php

namespace App\Http\Controllers;

use App\Models\Fenomena;
use App\Models\Indikator;
use App\Models\SektorUsaha;
use App\Models\SumberBerita;
use App\Models\JenisFenomena;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class FenomenaVerificationController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'pending'); // 'pending' | 'riwayat'

        // ── Stat counts (global, tidak terpengaruh filter) ────────
        $totalMenunggu = Fenomena::where('status_verifikasi', 'P')->count();
        $totalDiverifikasi = Fenomena::where('status_verifikasi', 'Y')->count();
        $totalDitolak = Fenomena::where('status_verifikasi', 'T')->count();

        $sektors = SektorUsaha::orderBy('kode')->get();
        $indikators = Indikator::where('is_active', true)->orderBy('kode')->get();
        $sumberBeritas = SumberBerita::orderBy('nama')->get();

        if ($tab === 'riwayat') {
            // ── Tab Riwayat: fenomena yang sudah Y atau T ──────────
            $riwayatQuery = Fenomena::with(['creator', 'sumberBerita', 'sektors', 'indikators', 'verifier'])
                ->whereIn('status_verifikasi', ['Y', 'T']);

            if ($search = $request->search) {
                $riwayatQuery->where(function ($q) use ($search) {
                    $q->where('judul', 'like', "%{$search}%")
                        ->orWhere('penjelasan', 'like', "%{$search}%");
                });
            }
            if ($sektor = $request->sektor) {
                $riwayatQuery->whereHas('sektors', fn($q) => $q->where('kode', $sektor));
            }
            if ($indikator = $request->indikator) {
                $riwayatQuery->whereHas('indikators', fn($q) => $q->where('kode', $indikator));
            }
            if ($sumber = $request->sumber) {
                $riwayatQuery->where('sumber_berita_id', $sumber);
            }
            // filter status riwayat: Y | T | (kosong = keduanya)
            if ($statusFilter = $request->status_riwayat) {
                $riwayatQuery->where('status_verifikasi', $statusFilter);
            }

            $riwayatQuery->orderBy('verified_at', 'desc');
            $riwayat = $riwayatQuery->paginate(10)->withQueryString();
            $totalFiltered = $riwayat->total();

            return view('fenomena.verification.index', compact(
                'tab',
                'riwayat',
                'sektors',
                'indikators',
                'sumberBeritas',
                'totalFiltered',
                'totalMenunggu',
                'totalDiverifikasi',
                'totalDitolak'
            ));
        }

        // ── Tab Pending (default) ──────────────────────────────────
        $query = Fenomena::with(['creator', 'sumberBerita', 'sektors', 'indikators'])
            ->where('status_verifikasi', 'P');

        if ($search = $request->search) {
            $query->where(function ($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%")
                    ->orWhere('penjelasan', 'like', "%{$search}%");
            });
        }
        if ($sektor = $request->sektor) {
            $query->whereHas('sektors', fn($q) => $q->where('kode', $sektor));
        }
        if ($indikator = $request->indikator) {
            $query->whereHas('indikators', fn($q) => $q->where('kode', $indikator));
        }
        if ($sumber = $request->sumber) {
            $query->where('sumber_berita_id', $sumber);
        }
        $dateCol = $request->date_type === 'created_at' ? 'created_at' : 'tanggal_berita';
        if ($dateFrom = $request->date_from) {
            $query->whereDate($dateCol, '>=', $dateFrom);
        }
        if ($dateTo = $request->date_to) {
            $query->whereDate($dateCol, '<=', $dateTo);
        }

        $query->orderBy('created_at', 'desc');
        $fenomenas = $query->paginate(10)->withQueryString();
        $totalFiltered = $fenomenas->total();

        return view('fenomena.verification.index', compact(
            'tab',
            'fenomenas',
            'sektors',
            'indikators',
            'sumberBeritas',
            'totalFiltered',
            'totalMenunggu',
            'totalDiverifikasi',
            'totalDitolak'
        ));
    }

    public function show($id)
    {
        $fenomena = Fenomena::where('status_verifikasi', 'P')
            ->with(['creator', 'sumberBerita', 'sektors', 'indikators', 'jenisFenomenas'])
            ->find($id);

        if (!$fenomena) {
            return redirect('/verification-fenomena')
                ->with('error', 'Data fenomena ini sudah diverifikasi atau tidak ditemukan.');
        }

        $sektorUsahas = SektorUsaha::orderBy('kode', 'asc')->get();
        $sumberBeritas = SumberBerita::orderBy('nama', 'asc')->get();
        $jenisFenomenas = JenisFenomena::orderBy('nama', 'asc')->get();
        $utamaIndikators = Indikator::where('kelompok', 'utama')
            ->where('is_active', true)
            ->orderBy('kode', 'asc')
            ->get();

        $impactIndikators = Indikator::where('kelompok', 'dampak')
            ->where('is_active', true)
            ->orderBy('kode', 'asc')
            ->get();

        return view('fenomena.verification.form', compact(
            'fenomena',
            'sektorUsahas',
            'sumberBeritas',
            'jenisFenomenas',
            'utamaIndikators',
            'impactIndikators'
        ));
    }

    public function store(Request $request, $id)
    {
        $rules = [
            // Edit Fenomena Details
            'judul' => 'required|string|max:255|unique:fenomenas,judul,' . $id,
            'tanggal_berita' => 'required|date',
            'sumber_berita_id' => 'required|exists:sumber_beritas,id',
            'penjelasan' => 'required|string',
            'sektor_usaha_id' => 'required|exists:sektor_usahas,id',
            'jenis_fenomena_ids' => 'required|array',
            'jenis_fenomena_ids.*' => 'exists:jenis_fenomenas,id',
            'indikator_id' => 'required|exists:indikators,id,is_active,1',

            // Verification Details
            'status_verifikasi' => 'required|in:Y,T',
            'arah_utama' => 'required_if:status_verifikasi,Y|in:naik,turun,tetap',
            'impact_directions' => 'array',
        ];

        // Conditional validation for link_berita
        $sumberBerita = SumberBerita::find($request->sumber_berita_id);
        if ($sumberBerita && $sumberBerita->is_online) {
            $rules['link_berita'] = 'required|url|unique:fenomenas,link_berita,' . $id;
        } else {
            $rules['link_berita'] = 'nullable|url|unique:fenomenas,link_berita,' . $id;
        }

        $request->validate($rules, [
            'judul.required' => 'Judul fenomena wajib diisi.',
            'judul.unique' => 'Judul fenomena sudah pernah dimasukkan.',
            'tanggal_berita.required' => 'Tanggal berita wajib diisi.',
            'sumber_berita_id.required' => 'Sumber berita wajib dipilih.',
            'penjelasan.required' => 'Penjelasan fenomena wajib diisi.',
            'sektor_usaha_id.required' => 'Lapangan usaha wajib dipilih.',
            'jenis_fenomena_ids.required' => 'Jenis fenomena wajib dipilih.',
            'indikator_id.required' => 'Indikator utama wajib dipilih.',
            'link_berita.required' => 'Link berita wajib diisi untuk sumber berita online.',
            'link_berita.url' => 'Format link berita tidak valid.',
            'link_berita.unique' => 'Link berita sudah pernah digunakan.',
        ]);

        $fenomena = Fenomena::where('status_verifikasi', 'P')->find($id);

        if (!$fenomena) {
            return redirect('/verification-fenomena')
                ->with('error', 'Data fenomena ini sudah diverifikasi atau tidak ditemukan.');
        }

        DB::transaction(function () use ($request, $fenomena) {
            $status = $request->status_verifikasi;

            $fenomena->update([
                'judul' => $request->judul,
                'tanggal_berita' => $request->tanggal_berita,
                'sumber_berita_id' => $request->sumber_berita_id,
                'link_berita' => $request->link_berita,
                'penjelasan' => $request->penjelasan,
                'status_verifikasi' => $status,
                'verified_by' => Auth::id(),
                'verified_at' => now(),
            ]);

            // Sync Sektor
            $fenomena->sektors()->sync([$request->sektor_usaha_id]);

            // Sync Jenis Fenomena
            $fenomena->jenisFenomenas()->sync($request->jenis_fenomena_ids);

            $syncData = [];

            // Primary indicator (kelompok = 'utama')
            $primaryId = $request->indikator_id;
            if ($primaryId) {
                if ($status === 'Y') {
                    $syncData[$primaryId] = [
                        'arah' => $request->arah_utama,
                        'ditetapkan_oleh' => Auth::id(),
                        'ditetapkan_at' => now(),
                    ];
                } else {
                    $syncData[$primaryId] = [
                        'arah' => null,
                        'ditetapkan_oleh' => null,
                        'ditetapkan_at' => null,
                    ];
                }
            }

            // Impact indicators (only if status === 'Y')
            if ($status === 'Y' && $request->has('impact_directions')) {
                $activeImpactIds = Indikator::where('is_active', true)
                    ->whereIn('id', array_keys($request->impact_directions))
                    ->pluck('id')->toArray();

                foreach ($request->impact_directions as $indikatorId => $arah) {
                    if (in_array($indikatorId, $activeImpactIds) && $arah) {
                        $syncData[$indikatorId] = [
                            'arah' => $arah,
                            'ditetapkan_oleh' => Auth::id(),
                            'ditetapkan_at' => now(),
                        ];
                    }
                }
            }

            $fenomena->indikators()->sync($syncData);
        });

        return redirect('/verification-fenomena')
            ->with('success', 'Verifikasi fenomena berhasil disimpan.');
    }

    public function edit($id)
    {
        $fenomena = Fenomena::with(['creator', 'sumberBerita', 'sektors', 'indikators', 'jenisFenomenas'])
            ->find($id);

        if (!$fenomena) {
            return redirect('/verification-fenomena')
                ->with('error', 'Data fenomena tidak ditemukan.');
        }

        $sektorUsahas = SektorUsaha::orderBy('kode', 'asc')->get();
        $sumberBeritas = SumberBerita::orderBy('nama', 'asc')->get();
        $jenisFenomenas = JenisFenomena::orderBy('nama', 'asc')->get();
        $utamaIndikators = Indikator::where('kelompok', 'utama')
            ->where('is_active', true)
            ->orderBy('kode', 'asc')
            ->get();

        $impactIndikators = Indikator::where('kelompok', 'dampak')
            ->where('is_active', true)
            ->orderBy('kode', 'asc')
            ->get();

        return view('fenomena.verification.edit', compact(
            'fenomena',
            'sektorUsahas',
            'sumberBeritas',
            'jenisFenomenas',
            'utamaIndikators',
            'impactIndikators'
        ));
    }

    public function update(Request $request, $id)
    {
        $rules = [
            // Edit Fenomena Details
            'judul' => 'required|string|max:255|unique:fenomenas,judul,' . $id,
            'tanggal_berita' => 'required|date',
            'sumber_berita_id' => 'required|exists:sumber_beritas,id',
            'penjelasan' => 'required|string',
            'sektor_usaha_id' => 'required|exists:sektor_usahas,id',
            'jenis_fenomena_ids' => 'required|array',
            'jenis_fenomena_ids.*' => 'exists:jenis_fenomenas,id',
            'indikator_id' => 'required|exists:indikators,id,is_active,1',

            // Verification Details
            'status_verifikasi' => 'required|in:Y,T',
            'arah_utama' => 'required_if:status_verifikasi,Y|in:naik,turun,tetap',
            'impact_directions' => 'array',
        ];

        // Conditional validation for link_berita
        $sumberBerita = SumberBerita::find($request->sumber_berita_id);
        if ($sumberBerita && $sumberBerita->is_online) {
            $rules['link_berita'] = 'required|url|unique:fenomenas,link_berita,' . $id;
        } else {
            $rules['link_berita'] = 'nullable|url|unique:fenomenas,link_berita,' . $id;
        }

        $request->validate($rules, [
            'judul.required' => 'Judul fenomena wajib diisi.',
            'judul.unique' => 'Judul fenomena sudah pernah dimasukkan.',
            'tanggal_berita.required' => 'Tanggal berita wajib diisi.',
            'sumber_berita_id.required' => 'Sumber berita wajib dipilih.',
            'penjelasan.required' => 'Penjelasan fenomena wajib diisi.',
            'sektor_usaha_id.required' => 'Lapangan usaha wajib dipilih.',
            'jenis_fenomena_ids.required' => 'Jenis fenomena wajib dipilih.',
            'indikator_id.required' => 'Indikator utama wajib dipilih.',
            'link_berita.required' => 'Link berita wajib diisi untuk sumber berita online.',
            'link_berita.url' => 'Format link berita tidak valid.',
            'link_berita.unique' => 'Link berita sudah pernah digunakan.',
        ]);

        $fenomena = Fenomena::find($id);

        if (!$fenomena) {
            return redirect('/verification-fenomena')
                ->with('error', 'Data fenomena tidak ditemukan.');
        }

        DB::transaction(function () use ($request, $fenomena) {
            $status = $request->status_verifikasi;

            $fenomena->update([
                'judul' => $request->judul,
                'tanggal_berita' => $request->tanggal_berita,
                'sumber_berita_id' => $request->sumber_berita_id,
                'link_berita' => $request->link_berita,
                'penjelasan' => $request->penjelasan,
                'status_verifikasi' => $status,
                'verified_by' => Auth::id(),
                'verified_at' => now(),
            ]);

            // Sync Sektor
            $fenomena->sektors()->sync([$request->sektor_usaha_id]);

            // Sync Jenis Fenomena
            $fenomena->jenisFenomenas()->sync($request->jenis_fenomena_ids);

            $syncData = [];

            // Primary indicator (kelompok = 'utama')
            $primaryId = $request->indikator_id;
            if ($primaryId) {
                if ($status === 'Y') {
                    $syncData[$primaryId] = [
                        'arah' => $request->arah_utama,
                        'ditetapkan_oleh' => Auth::id(),
                        'ditetapkan_at' => now(),
                    ];
                } else {
                    $syncData[$primaryId] = [
                        'arah' => null,
                        'ditetapkan_oleh' => null,
                        'ditetapkan_at' => null,
                    ];
                }
            }

            // Impact indicators (only if status === 'Y')
            if ($status === 'Y' && $request->has('impact_directions')) {
                $activeImpactIds = Indikator::where('is_active', true)
                    ->whereIn('id', array_keys($request->impact_directions))
                    ->pluck('id')->toArray();

                foreach ($request->impact_directions as $indikatorId => $arah) {
                    if (in_array($indikatorId, $activeImpactIds) && $arah) {
                        $syncData[$indikatorId] = [
                            'arah' => $arah,
                            'ditetapkan_oleh' => Auth::id(),
                            'ditetapkan_at' => now(),
                        ];
                    }
                }
            }

            $fenomena->indikators()->sync($syncData);
        });

        return redirect('/verification-fenomena?tab=riwayat')
            ->with('success', 'Verifikasi fenomena berhasil diubah.');
    }
}
