<table style="border: 1px solid #000; border-collapse: collapse;">
    <thead>
        <tr>
            <th colspan="7" style="text-align: center; font-weight: bold; font-size: 14pt;">
                REKAPITULASI FENOMENA PENDUKUNG ANGKA KEMISKINAN<br>
                {{ strtoupper($kabupaten->nama_kabupaten) }} TAHUN
                {{ strtoupper($tahun === 'all' ? 'Semua Tahun' : $tahun) }}
            </th>
        </tr>
        <tr></tr>
        <tr>
            <th rowspan="2" style="border: 1px solid #000; text-align: center; font-weight: bold; width: 5px;">No</th>
            <th rowspan="2" style="border: 1px solid #000; text-align: center; font-weight: bold; width: 30px;">
                Indikator</th>
            <th colspan="3" style="border: 1px solid #000; text-align: center; font-weight: bold;">Nilai Indikator</th>
            <th colspan="2" style="border: 1px solid #000; text-align: center; font-weight: bold;">
                Penjelasan Fenomena Pendukung Angka Kemiskinan<br>
                (Secara Kualitatif atau Kuantitatif, kondisi September {{ $baseYear }} dibandingkan Maret
                {{ $baseYear }})
            </th>
        </tr>
        <tr>
            <th style="border: 1px solid #000; text-align: center; font-weight: bold;">{{ $baseYear - 2 }}</th>
            <th style="border: 1px solid #000; text-align: center; font-weight: bold;">{{ $baseYear - 1 }}</th>
            <th style="border: 1px solid #000; text-align: center; font-weight: bold;">{{ $baseYear }}</th>
            <th style="border: 1px solid #000; text-align: center; font-weight: bold; width: 50px;">NAIK</th>
            <th style="border: 1px solid #000; text-align: center; font-weight: bold; width: 50px;">TURUN</th>
        </tr>
        <tr>
            <th style="border: 1px solid #000; text-align: center;">(1)</th>
            <th style="border: 1px solid #000; text-align: center;">(2)</th>
            <th style="border: 1px solid #000; text-align: center;">(3)</th>
            <th style="border: 1px solid #000; text-align: center;">(4)</th>
            <th style="border: 1px solid #000; text-align: center;">(5)</th>
            <th style="border: 1px solid #000; text-align: center;">(6)</th>
            <th style="border: 1px solid #000; text-align: center;">(7)</th>
        </tr>
    </thead>
    <tbody>
        @php $no = 1; @endphp
        @forelse($groupedData as $row)
            <tr>
                <td style="border: 1px solid #000; text-align: center; vertical-align: top;">{{ $no++ }}</td>
                <td style="border: 1px solid #000; font-weight: bold; vertical-align: top;">{{ $row['indikator']->nama }}
                </td>
                <td style="border: 1px solid #000;"></td>
                <td style="border: 1px solid #000;"></td>
                <td style="border: 1px solid #000;"></td>
                <td style="border: 1px solid #000; vertical-align: top;">
                    @if(count($row['naik']) > 0)
                        @foreach($row['naik'] as $fen)
                            • {{ rtrim($fen->penjelasan, '.') }}.
                            @if($fen->link_berita)
                                ({{ $fen->link_berita }})
                            @endif
                            <br><br>
                        @endforeach
                    @else
                        -
                    @endif
                </td>
                <td style="border: 1px solid #000; vertical-align: top;">
                    @if(count($row['turun']) > 0)
                        @foreach($row['turun'] as $fen)
                            • {{ rtrim($fen->penjelasan, '.') }}.
                            @if($fen->link_berita)
                                ({{ $fen->link_berita }})
                            @endif
                            <br><br>
                        @endforeach
                    @else
                        -
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" style="border: 1px solid #000; text-align: center;">Belum ada fenomena yang
                    ditandai/dipilih.</td>
            </tr>
        @endforelse

        {{-- Data Tanpa Indikator (Fallback) --}}
        @if(count($unmatchedData) > 0)
            <tr>
                <td style="border: 1px solid #000; text-align: center; vertical-align: top;">{{ $no++ }}</td>
                <td style="border: 1px solid #000; font-style: italic; vertical-align: top;">Lainnya (Tanpa Indikator)</td>
                <td style="border: 1px solid #000;"></td>
                <td style="border: 1px solid #000;"></td>
                <td style="border: 1px solid #000;"></td>
                <td colspan="2" style="border: 1px solid #000; vertical-align: top;">
                    @foreach($unmatchedData as $fen)
                        • {{ rtrim($fen->penjelasan, '.') }}.
                        @if($fen->link_berita)
                            ({{ $fen->link_berita }})
                        @endif
                        <br><br>
                    @endforeach
                </td>
            </tr>
        @endif
    </tbody>
</table>