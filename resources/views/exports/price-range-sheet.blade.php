<table>
    <thead>
        <tr>
            <!-- Row 1: Top Headers -->
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
        @foreach($categories as $category)
            <tr>
                <td style="font-weight: bold; border: 1px solid #000000; background-color: #f2f2f2;">
                    {{ $category->nama_kategori }}</td>
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
                @endphp
                <tr>
                    <td style="border: 1px solid #000000;">{{ $komo->nama_komoditas }}</td>
                    <td style="border: 1px solid #000000;">{{ $komo->satuan ?? 'Kg' }}</td>

                    <!-- Master Data -->
                    <td style="border: 1px solid #000000;">{{ $master ? $master->min_nilai : '-' }}</td>
                    <td style="border: 1px solid #000000;">{{ $master ? $master->max_nilai : '-' }}</td>
                    <td style="border: 1px solid #000000;">{{ $master ? $master->alasan : '-' }}</td>

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
                            } else {
                                // Even if carry forward, we might want to show the value? 
                                // The previous table showed just the number.
                                // If no edit, it implies carry forward if we want to show the 'State at this revision'.
                                // Standard logic: Show current state. 
                                // But if 'MIN_EDIT' usually implies 'The Edited Value'. 
                                // However, user said "jika nilai min ... sama ... jangan label kuning". This implies we ARE showing it.
                                // So we display $currentMin (state).
                                // Wait, the column name is MIN_EDIT. 
                                // If I just show $currentMin, it's the state.
                                // If I compare $currentMin (now) vs $currentMin (before), it is same.
                                // So checks out.
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

                            // Alasan Logic
                            if ($revData && $revData->alasan !== null) {
                                $currentAlasan = $revData->alasan;
                            }
                        @endphp

                        <td style="border: 1px solid #000000; {{ $styleMin }}">{{ $currentMin ?? '-' }}</td>
                        <td style="border: 1px solid #000000; {{ $styleMax }}">{{ $currentMax ?? '-' }}</td>
                        <td style="border: 1px solid #000000;">{{ $currentAlasan ?? '-' }}</td>
                    @endforeach
                </tr>
            @endforeach
        @endforeach
    </tbody>
</table>