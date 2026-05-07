<table>
    <thead>
        <tr>
            <th rowspan="2" style="background-color: #f2f2f2; border: 1px solid #000; text-align: center; vertical-align: middle;">Kabupaten/Kota</th>
            <th rowspan="2" style="background-color: #f2f2f2; border: 1px solid #000; text-align: center; vertical-align: middle;">Kecamatan</th>
            <th rowspan="2" style="background-color: #f2f2f2; border: 1px solid #000; text-align: center; vertical-align: middle;">Desa</th>
            
            @foreach($kegiatans as $keg)
                <th colspan="3" style="background-color: #f2f2f2; border: 1px solid #000; text-align: center;">{{ $keg->nama_kegiatan }}</th>
            @endforeach

            <th colspan="{{ $jenisOutputs->count() }}" style="background-color: #f2f2f2; border: 1px solid #000; text-align: center;">Output (Link bukti dukung)</th>
            
            <th rowspan="2" style="background-color: #f2f2f2; border: 1px solid #000; text-align: center; vertical-align: middle;">Bukti Dukung lainnya (SK Agen Statistik Desa, Laporan Akhir Desa Cantik {{ $periode?->tahun }} - link bukti dukung)</th>

            <th rowspan="2" style="background-color: #f2f2f2; border: 1px solid #000; text-align: center; vertical-align: middle;">Penilaian mandiri desa (sudah/belum)</th>
            <th rowspan="2" style="background-color: #f2f2f2; border: 1px solid #000; text-align: center; vertical-align: middle;">Penilaian mandiri kab kota (sudah/belum)</th>
            <th rowspan="2" style="background-color: #f2f2f2; border: 1px solid #000; text-align: center; vertical-align: middle;">Verifikasi provinsi (sudah/belum)</th>
        </tr>
        <tr>
            @foreach($kegiatans as $keg)
                <th style="background-color: #f2f2f2; border: 1px solid #000; text-align: center;">Target</th>
                <th style="background-color: #f2f2f2; border: 1px solid #000; text-align: center;">Realisasi</th>
                <th style="background-color: #f2f2f2; border: 1px solid #000; text-align: center;">Link Bukti dukung ({{ implode(', ', $topBuktiKegiatan) }}, dsb)</th>
            @endforeach

            @foreach($jenisOutputs as $jo)
                <th style="background-color: #f2f2f2; border: 1px solid #000; text-align: center;">{{ $jo->nama_output }}</th>
            @endforeach

            {{-- Bukti Dukung column handled by rowspan in row above --}}
        </tr>
    </thead>
    <tbody>
        @foreach($groupedPesertas as $pesertasInKab)
            @foreach($pesertasInKab as $index => $peserta)
                <tr>
                    @if($index == 0)
                        <td rowspan="{{ count($pesertasInKab) }}" style="border: 1px solid #000; text-align: center; vertical-align: middle;">
                            {{ str_replace(['KABUPATEN ', 'KOTA '], '', strtoupper($peserta->kabupaten?->nama_kabupaten)) }}
                        </td>
                    @endif
                    <td style="border: 1px solid #000;">{{ $peserta->kecamatan?->nama_kecamatan }}</td>
                    <td style="border: 1px solid #000;">{{ $peserta->desa?->nama_desa }}</td>

                    @foreach($kegiatans as $keg)
                        @php
                            $prog = $peserta->progresses->where('kegiatan_id', $keg->id)->first();
                        @endphp
                        <td style="border: 1px solid #000; text-align: center;">{{ $prog ? $prog->target_tanggal : '' }}</td>
                        <td style="border: 1px solid #000; text-align: center;">{{ $prog ? $prog->realisasi_tanggal : '' }}</td>
                        <td style="border: 1px solid #000; vertical-align: top;">
                            @if($prog)
                                @foreach($prog->buktis as $idx => $b)
                                    {{ ($idx + 1) . '. ' . ($b->jenisBukti?->nama_bukti ?? 'Bukti') . ': ' . $b->link_file }}<br>
                                @endforeach
                            @endif
                        </td>
                    @endforeach

                    @foreach($jenisOutputs as $jo)
                        @php
                            $out = $peserta->outputs->where('jenis_output_id', $jo->id)->first();
                        @endphp
                        <td style="border: 1px solid #000;">{{ $out ? $out->link : '' }}</td>
                    @endforeach

                    <td style="border: 1px solid #000; vertical-align: top;">
                        @foreach($peserta->buktiDukungs as $idx => $duk)
                            {{ ($idx + 1) . '. ' . ($duk->jenisBukti?->nama_bukti ?? 'Bukti') . ': ' . $duk->link_file }}<br>
                        @endforeach
                    </td>

                    <td style="border: 1px solid #000; text-align: center;">{{ $peserta->penilaian?->penilaian_mandiri_desa ? 'Sudah' : 'Belum' }}</td>
                    <td style="border: 1px solid #000; text-align: center;">{{ $peserta->penilaian?->penilaian_mandiri_kab ? 'Sudah' : 'Belum' }}</td>
                    <td style="border: 1px solid #000; text-align: center;">{{ $peserta->penilaian?->verifikasi_provinsi ? 'Sudah' : 'Belum' }}</td>
                </tr>
            @endforeach
        @endforeach
    </tbody>
</table>
