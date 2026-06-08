<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
</head>

<body>
    @if(count($indicatorData) > 0)
        @foreach($indicatorData as $data)
            @php
                $indicator = $data['indicator'];
                $dimensions = $data['dimensions'];
                $isNoneOnly = $data['isNoneOnly'];
                $values = $data['values'];
                $colspan = $isNoneOnly ? ($periodes->count() + 2) : ($periodes->count() * $dimensions->count() + 2);
                $titleColspan = max($colspan, 25);
                
                $years = $periodes->pluck('tahun')->toArray();
                if (empty($years)) {
                    $yearsRange = '';
                } else if (count($years) === 1) {
                    $yearsRange = $years[0];
                } else if (($years[count($years) - 1] - $years[0]) === (count($years) - 1)) {
                    $yearsRange = $years[0] . '-' . $years[count($years) - 1];
                } else {
                    $yearsRange = implode(', ', $years);
                }
            @endphp
            <table>
                <thead>
                    <tr>
                        <th colspan="{{ $titleColspan }}" style="font-weight: bold; font-size: 14pt; text-align: left; word-wrap: break-word;">
                            TABEL {{ $sheetIndex }}.{{ $loop->iteration }} {{ strtoupper($indicator->nama_indikator) }}, {{ $yearsRange }}
                        </th>
                    </tr>
                    <tr>
                        <th colspan="{{ $titleColspan }}" style="font-style: italic; font-size: 11pt; text-align: left;">
                            SATUAN: {{ strtoupper($indicator->satuan ?? '-') }}
                        </th>
                    </tr>
                    <tr></tr>

                    @if($isNoneOnly)
                        <tr>
                            <th style="font-weight: bold; border: 1px solid #000000; text-align: center; background-color: #f2f2f2;"
                                rowspan="2">NO</th>
                            <th style="font-weight: bold; border: 1px solid #000000; text-align: center; background-color: #f2f2f2;"
                                rowspan="2">KABUPATEN/KOTA/PROVINSI</th>
                            <th style="font-weight: bold; border: 1px solid #000000; text-align: center; background-color: #f2f2f2;"
                                colspan="{{ $periodes->count() }}">TAHUN / PERIODE</th>
                        </tr>
                        <tr>
                            @foreach($periodes as $p)
                                <th
                                    style="font-weight: bold; border: 1px solid #000000; text-align: center; background-color: #f2f2f2;">
                                    {{ $p->tahun }}</th>
                            @endforeach
                        </tr>
                        <tr>
                            <th
                                style="font-weight: bold; border: 1px solid #000000; text-align: center; background-color: #f2f2f2;">
                                (1)</th>
                            <th
                                style="font-weight: bold; border: 1px solid #000000; text-align: center; background-color: #f2f2f2;">
                                (2)</th>
                            @foreach($periodes as $idx => $p)
                                <th
                                    style="font-weight: bold; border: 1px solid #000000; text-align: center; background-color: #f2f2f2;">
                                    ({{ $idx + 3 }})</th>
                            @endforeach
                        </tr>
                    @else
                        <tr>
                            <th style="font-weight: bold; border: 1px solid #000000; text-align: center; background-color: #f2f2f2;"
                                rowspan="3">NO</th>
                            <th style="font-weight: bold; border: 1px solid #000000; text-align: center; background-color: #f2f2f2;"
                                rowspan="3">KABUPATEN/KOTA/PROVINSI</th>
                            <th style="font-weight: bold; border: 1px solid #000000; text-align: center; background-color: #f2f2f2;"
                                colspan="{{ $periodes->count() * $dimensions->count() }}">TAHUN / PERIODE</th>
                        </tr>
                        <tr>
                            @foreach($periodes as $p)
                                <th style="font-weight: bold; border: 1px solid #000000; text-align: center; background-color: #f2f2f2;"
                                    colspan="{{ $dimensions->count() }}">{{ $p->tahun }}</th>
                            @endforeach
                        </tr>
                        <tr>
                            @foreach($periodes as $p)
                                @foreach($dimensions as $indDim)
                                    <th
                                        style="font-weight: bold; border: 1px solid #000000; text-align: center; background-color: #f2f2f2;">
                                        {{ strtoupper($indDim->dimensi->nama_dimensi ?? '-') }}</th>
                                @endforeach
                            @endforeach
                        </tr>
                        <tr>
                            <th
                                style="font-weight: bold; border: 1px solid #000000; text-align: center; background-color: #f2f2f2;">
                                (1)</th>
                            <th
                                style="font-weight: bold; border: 1px solid #000000; text-align: center; background-color: #f2f2f2;">
                                (2)</th>
                            @for($i = 0; $i < $periodes->count() * $dimensions->count(); $i++)
                                <th
                                    style="font-weight: bold; border: 1px solid #000000; text-align: center; background-color: #f2f2f2;">
                                    ({{ $i + 3 }})</th>
                            @endfor
                        </tr>
                    @endif
                </thead>
                <tbody>
                    @foreach($kabupatens as $idx => $kab)
                        @php
                            $isProvince = $kab->kode_kab === '6100';
                            $isIndonesia = ($kab->kode_kab === '1' || strtolower($kab->nama_kabupaten) === 'indonesia');
                            $rowStyle = ($isProvince || $isIndonesia) ? 'font-weight: bold; background-color: #f9f9f9;' : '';
                        @endphp
                        <tr>
                            <td style="border: 1px solid #000000; text-align: center; {{ $rowStyle }}">
                                {{ $idx + 1 }}
                            </td>
                            <td style="border: 1px solid #000000; {{ $rowStyle }}">
                                @if($isIndonesia)
                                    INDONESIA
                                @elseif($isProvince)
                                    [{{ $kab->kode_kab }}] PROVINSI KALIMANTAN BARAT
                                @else
                                    [{{ $kab->kode_kab }}] {{ strtoupper($kab->nama_kabupaten) }}
                                @endif
                            </td>
                            @foreach($periodes as $p)
                                @foreach($dimensions as $indDim)
                                    @php
                                        $val = $values[$kab->id][$p->id][$indDim->id] ?? null;
                                        $valFormatted = is_numeric($val) ? (float) $val : '-';
                                        if ($valFormatted === '-') {
                                            $valFormatted = '';
                                        }
                                    @endphp
                                    <td style="border: 1px solid #000000; text-align: right; {{ $rowStyle }}">
                                        {{ $valFormatted }}
                                    </td>
                                @endforeach
                            @endforeach
                        </tr>
                    @endforeach
                    <!-- Spacer rows inside the table body -->
                    <tr>
                        <td colspan="{{ $colspan }}"></td>
                    </tr>
                    <tr>
                        <td colspan="{{ $colspan }}"></td>
                    </tr>
                    <tr>
                        <td colspan="{{ $colspan }}"></td>
                    </tr>
                </tbody>
            </table>
        @endforeach
    @else
        <table>
            <thead>
                <tr>
                    <th style="font-weight: bold; font-size: 14pt; text-align: left;">
                        BIDANG: {{ strtoupper($bidang->nama_bidang) }}
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="font-style: italic; color: #64748b;">
                        TIDAK ADA DATA INDIKATOR TERPILIH UNTUK BIDANG INI.
                    </td>
                </tr>
            </tbody>
        </table>
    @endif
</body>

</html>