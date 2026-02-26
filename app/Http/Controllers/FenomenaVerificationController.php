<?php

namespace App\Http\Controllers;

use App\Models\Fenomena;
use App\Models\Indikator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class FenomenaVerificationController extends Controller
{
    public function index()
    {
        $fenomenas = Fenomena::with(['creator', 'sumberBerita', 'sektors', 'indikators'])
            ->where('status_verifikasi', 'P')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('fenomena.verification.index', compact('fenomenas'));
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
