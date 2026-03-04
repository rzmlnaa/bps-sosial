<?php

namespace App\Traits;

use App\Models\RhTahun;
use App\Models\RhPerubahanHeader;
use App\Models\RhPerubahanDetail;

trait HandlesRhStates
{
    /**
     * Build states for multiple kabupatens across years iteratively.
     * Prevents N+1 by bulk fetching details per year.
     */
    public function getBulkStates($targetYear, $kabupatenIds = [])
    {
        $years = RhTahun::where('tahun', '<=', $targetYear)
            ->where('tahun', '>=', 2020)
            ->orderBy('tahun', 'asc')
            ->get();

        $states = [];
        foreach ($kabupatenIds as $id) {
            $states[$id] = [];
        }

        foreach ($years as $yr) {
            // 1. Overlay Master Values
            $masterNilai = RhPerubahanDetail::where('rh_tahun_id', $yr->id)
                ->whereNull('rh_perubahan_header_id')
                ->when(!empty($kabupatenIds), function ($q) use ($kabupatenIds) {
                    return $q->whereIn('kabupaten_id', $kabupatenIds);
                })
                ->get();

            foreach ($masterNilai as $m) {
                if ($m->min_edit !== null || $m->max_edit !== null) {
                    $states[$m->kabupaten_id][$m->komoditas_id] = [
                        'min' => $m->min_edit,
                        'max' => $m->max_edit,
                        'alasan' => $m->alasan,
                    ];
                }
            }

            // 2. Apply Revisions for this year
            $revisions = RhPerubahanHeader::where('rh_tahun_id', $yr->id)
                ->orderBy('tanggal_perubahan', 'asc')
                ->get();

            if ($revisions->isNotEmpty()) {
                $details = RhPerubahanDetail::whereIn('rh_perubahan_header_id', $revisions->pluck('id'))
                    ->when(!empty($kabupatenIds), function ($q) use ($kabupatenIds) {
                        return $q->whereIn('kabupaten_id', $kabupatenIds);
                    })
                    ->get()
                    ->groupBy(['rh_perubahan_header_id', 'kabupaten_id']);

                foreach ($revisions as $rev) {
                    if (isset($details[$rev->id])) {
                        foreach ($details[$rev->id] as $kabId => $kabDetails) {
                            foreach ($kabDetails as $det) {
                                $komId = $det->komoditas_id;
                                if (!isset($states[$kabId][$komId])) {
                                    $states[$kabId][$komId] = ['min' => null, 'max' => null, 'alasan' => null];
                                }
                                if ($det->min_edit !== null)
                                    $states[$kabId][$komId]['min'] = $det->min_edit;
                                if ($det->max_edit !== null)
                                    $states[$kabId][$komId]['max'] = $det->max_edit;

                                if ($det->alasan !== null) {
                                    $states[$kabId][$komId]['alasan'] = $det->alasan;
                                } elseif ($det->min_edit !== null || $det->max_edit !== null) {
                                    $states[$kabId][$komId]['alasan'] = null;
                                }
                            }
                        }
                    }
                }
            }
        }

        return $states;
    }

    /**
     * Helper to get final state for a single kabupaten by wrapping getBulkStates.
     */
    public function getFinalStateForYear($year, $kabupatenId)
    {
        $states = $this->getBulkStates($year, [$kabupatenId]);
        return $states[$kabupatenId] ?? [];
    }
}
