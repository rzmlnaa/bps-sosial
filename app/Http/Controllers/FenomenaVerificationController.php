<?php

namespace App\Http\Controllers;

use App\Models\Fenomena;
use App\Models\Indikator;
use App\Models\SektorUsaha;
use App\Models\SumberBerita;
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
        $fenomena = Fenomena::with(['creator', 'sumberBerita', 'sektors', 'indikators', 'jenisFenomenas'])
            ->findOrFail($id);

        $impactIndikators = Indikator::where('kelompok', 'dampak')
            ->where('is_active', true)
            ->orderBy('kode', 'asc')
            ->get();

        return view('fenomena.verification.form', compact('fenomena', 'impactIndikators'));
    }

    public function store(Request $request, $id)
    {
        $request->validate([
            'status_verifikasi' => 'required|in:Y,T',
            'arah_utama' => 'required_if:status_verifikasi,Y|in:naik,turun,tetap',
            'impact_directions' => 'array',
        ]);

        $fenomena = Fenomena::findOrFail($id);

        DB::transaction(function () use ($request, $fenomena) {
            $status = $request->status_verifikasi;

            $fenomena->update([
                'status_verifikasi' => $status,
                'verified_by' => Auth::id(),
                'verified_at' => now(),
            ]);

            if ($status === 'Y') {
                // Update direction for the primary (utama) indicator
                $utamaIndikator = $fenomena->indikators()->where('kelompok', 'utama')->first();
                if ($utamaIndikator) {
                    $fenomena->indikators()->updateExistingPivot($utamaIndikator->id, [
                        'arah' => $request->arah_utama,
                        'ditetapkan_oleh' => Auth::id(),
                        'ditetapkan_at' => now(),
                    ]);
                }

                // Sync impact indicators
                if ($request->has('impact_directions')) {
                    $impactsToSync = [];
                    foreach ($request->impact_directions as $indikatorId => $arah) {
                        if ($arah) {
                            $impactsToSync[$indikatorId] = [
                                'arah' => $arah,
                                'ditetapkan_oleh' => Auth::id(),
                                'ditetapkan_at' => now(),
                            ];
                        }
                    }

                    // We only want to attach impacts, but pivot table might already have the 'utama' one.
                    // To avoid removing the 'utama' one, we use syncWithoutDetaching or manually manage it.
                    // Since we want to update impacts if they exist or add new ones, syncWithoutDetaching is good.
                    if (!empty($impactsToSync)) {
                        $fenomena->indikators()->syncWithoutDetaching($impactsToSync);
                    }
                }
            }
        });

        return redirect()->route('fenomena.verification.index')
            ->with('success', 'Verifikasi fenomena berhasil disimpan.');
    }
}
