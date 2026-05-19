<table>
    <thead>
        <tr>
            <!-- Row 1: Top Headers -->
            <th style="font-weight: bold; text-align: center;"></th> <!-- URUT_BAR -->
            <th style="font-weight: bold; text-align: left;"></th> <!-- NAMA -->
            <th style="font-weight: bold; text-align: center;"></th> <!-- SATUAN -->
            <th style="font-weight: bold; text-align: center;"></th> <!-- MIN MASTER -->
            <th style="font-weight: bold; text-align: center;"></th> <!-- MAX MASTER -->
            <th style="font-weight: bold; text-align: center;"></th> <!-- ALASAN MASTER -->

            @foreach($revisions as $rev)
                <th colspan="3" style="font-weight: bold; text-align: center; border: 1px solid #000000;">
                    Perubahan {{ $rev->label }}
                </th>
            @endforeach
        </tr>
        <tr>
            <!-- Row 2: Sub Headers -->
            <th style="font-weight: bold; border: 1px solid #000000;">URUT_BAR</th>
            <th style="font-weight: bold; border: 1px solid #000000;">NAMA</th>
            <th style="font-weight: bold; border: 1px solid #000000;">SATUAN</th>
            <th style="font-weight: bold; border: 1px solid #000000;">MIN_{{ substr($year->tahun, -2) }}</th>
            <th style="font-weight: bold; border: 1px solid #000000;">MAX_{{ substr($year->tahun, -2) }}</th>
            <th style="font-weight: bold; border: 1px solid #000000;">ALASAN</th>

            @foreach($revisions as $rev)
                <th style="font-weight: bold; border: 1px solid #000000;">MIN_EDIT</th>
                <th style="font-weight: bold; border: 1px solid #000000;">MAX_EDIT</th>
                <th style="font-weight: bold; border: 1px solid #000000;">ALASAN_EDIT</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @php $no = 1; @endphp
        @foreach($categories as $category)
            <tr>
                <td style="border: 1px solid #000000; text-align: center; background-color: #f2f2f2;">{{ $no++ }}</td>
                <td style="font-weight: bold; border: 1px solid #000000; background-color: #f2f2f2;">
                    {{ $category->nama_kategori }}
                </td>
                <td style="border: 1px solid #000000; background-color: #f2f2f2;"></td>
                <td style="border: 1px solid #000000; background-color: #f2f2f2;"></td>
                <td style="border: 1px solid #000000; background-color: #f2f2f2;"></td>
                <td style="border: 1px solid #000000; background-color: #f2f2f2;"></td>
                @foreach($revisions as $rev)
                    <td style="border: 1px solid #000000; background-color: #f2f2f2;"></td>
                    <td style="border: 1px solid #000000; background-color: #f2f2f2;"></td>
                    <td style="border: 1px solid #000000; background-color: #f2f2f2;"></td>
                @endforeach
            </tr>

            @foreach($category->komoditas as $komo)
                @php
                    $master = $masterNilai->get($komo->id);
                    $currentMin = $master ? $master->min_nilai : null;
                    $currentMax = $master ? $master->max_nilai : null;
                    $currentAlasan = $master ? $master->alasan : null;

                    // Comparison Logic for Master
                    $limit = $komo->batas_selisih_harga ?? 0;
                    $masterDiff = ($currentMax !== null && $currentMin !== null) ? abs($currentMax - $currentMin) : 0;

                    // User request: Clear reason if within range ONLY for the last state
                    // We remove it from here to allow carry-forward to revisions
                    // if ($masterDiff <= $limit) {
                    //     $currentAlasan = null;
                    // }
                    $prevData = isset($prevYearValues) ? ($prevYearValues[$komo->id] ?? null) : null;
                    $prevMin = $prevData['min'] ?? null;
                    $prevMax = $prevData['max'] ?? null;

                    $styleMinMaster = "";
                    $styleMaxMaster = "";

                    if ($master && $master->min_nilai !== null && $prevMin !== null && $master->min_nilai != $prevMin) {
                        $styleMinMaster = "background-color: #FFFF00;";
                    }
                    if ($master && $master->max_nilai !== null && $prevMax !== null && $master->max_nilai != $prevMax) {
                        $styleMaxMaster = "background-color: #FFFF00;";
                    }
                @endphp
                <tr>
                    <td style="border: 1px solid #000000; text-align: center;">{{ $no++ }}</td>
                    <td style="border: 1px solid #000000;">{{ $komo->nama_komoditas }}</td>
                    <td style="border: 1px solid #000000;">{{ $komo->satuan ?? 'Kg' }}</td>

                    <!-- Master Data -->
                    <td style="border: 1px solid #000000; {{ $styleMinMaster }}">{{ $currentMin ?? '-' }}</td>
                    <td style="border: 1px solid #000000; {{ $styleMaxMaster }}">{{ $currentMax ?? '-' }}</td>
                    @php
                        $mAlasanStyle = "";
                        $mHasRevisions = $revisions->count() > 0;
                        if ($masterDiff > $limit && empty($master->alasan) && !$mHasRevisions) {
                            $mAlasanStyle = "background-color: #FFCCCC;"; // Red background
                        }
                    @endphp
                    <td style="border: 1px solid #000000; {{ $mAlasanStyle }}">{{ ($master ? $master->alasan : null) ?? '-' }}
                    </td>

                    <!-- Revisions -->
                    @foreach($revisions as $rev)
                        @php
                            $revData = isset($revisionDetails[$rev->id]) ? $revisionDetails[$rev->id]->get($komo->id) : null;

                            $newMin = null;
                            $styleMin = "";

                            // Min Logic
                            if ($revData && $revData->min_edit !== null) {
                                // If explicit edit exists
                                $newMin = $revData->min_edit;
                                // Check if different from PREVIOUS value
                                if ($currentMin != $newMin) {
                                    $styleMin = "background-color: #FFFF00;"; // Yellow
                                }
                                $currentMin = $newMin; // Update carry forward
                            }

                            $newMax = null;
                            $styleMax = "";

                            // Max Logic
                            if ($revData && $revData->max_edit !== null) {
                                $newMax = $revData->max_edit;
                                if ($currentMax != $newMax) {
                                    $styleMax = "background-color: #FFFF00;";
                                }
                                $currentMax = $newMax;
                            }

                            // Alasan Carry Forward & Reset Logic
                            if ($revData) {
                                if ($revData->alasan !== null) {
                                    $currentAlasan = $revData->alasan;
                                } elseif ($revData->min_edit !== null || $revData->max_edit !== null) {
                                    // If value updated but reason not provided => reset reason
                                    $currentAlasan = null;
                                }
                            }

                            // Additional check: If now within range after edit, reset reason ONLY for the last revision
                            $revDiff = ($currentMax !== null && $currentMin !== null) ? abs($currentMax - $currentMin) : 0;
                            if ($revDiff <= $limit && $loop->last) {
                                $currentAlasan = null;
                            }

                            $styleAlasan = "";
                            if ($revDiff > $limit && empty($currentAlasan) && $loop->last) {
                                $styleAlasan = "background-color: #FFCCCC;"; // Red background
                            }
                        @endphp

                        <td style="border: 1px solid #000000; {{ $styleMin }}">{{ $currentMin ?? '-' }}</td>
                        <td style="border: 1px solid #000000; {{ $styleMax }}">{{ $currentMax ?? '-' }}</td>
                        <td style="border: 1px solid #000000; {{ $styleAlasan }}">{{ $currentAlasan ?? '-' }}</td>
                    @endforeach
                </tr>
            @endforeach
        @endforeach
    </tbody>
</table>