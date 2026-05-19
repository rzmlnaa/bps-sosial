@extends('layouts.admin')

@section('title', 'Tabel Hasil Ekspor - Semua Wilayah')

@push('styles')
    <style>
        .export-wrapper {
            background: #fff;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, .06);
            margin-top: 1rem;
        }

        .export-table {
            width: 100%;
            border-collapse: collapse;
            font-family: 'Inter', sans-serif;
        }

        .export-table th,
        .export-table td {
            border: 1px solid #1e293b;
            padding: 10px 14px;
            font-size: 0.85rem;
            vertical-align: top;
        }

        .export-table thead th {
            background: #dbeafe;
            text-align: center;
            font-weight: 700;
            color: #0f172a;
            vertical-align: middle;
        }

        .cell-numbering th {
            font-weight: normal;
            font-size: 0.75rem;
            background: #e0e7ff;
            padding: 4px;
        }

        .export-table ul {
            margin: 0;
            padding-left: 1.25rem;
        }

        .export-table li {
            margin-bottom: 0.5rem;
        }

        .export-table li:last-child {
            margin-bottom: 0;
        }

        @media print {
            body * {
                visibility: hidden;
            }

            .export-wrapper,
            .export-wrapper * {
                visibility: visible;
            }

            .export-wrapper {
                position: relative !important;
                left: 0;
                top: 0;
                padding: 0;
                box-shadow: none;
                margin-top: 0;
                margin-bottom: 2rem;
                page-break-after: always;
            }

            .export-wrapper:last-child {
                page-break-after: auto;
            }

            .btn-hide-print {
                display: none !important;
            }
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid py-4 fade-in-up">
        @php
            $displayTahunText = $tahun === 'all' ? 'Semua Tahun' : $tahun;
            $displayBulanText = $bulan === 'all' ? 'Semua Bulan' : $bulan;
            $baseYear = $tahun === 'all' ? date('Y') : (int) $tahun;
        @endphp
        <div class="d-flex justify-content-between align-items-center mb-4 btn-hide-print">
            <div>
                <h4 class="fw-bold mb-1" style="color: var(--fi-primary);">Tabel Rekap Ekspor Fenomena</h4>
                <div class="text-muted" style="font-size: 0.9rem;">
                    Wilayah: <strong>Semua Wilayah</strong> | Periode: Bulan {{ $displayBulanText }}
                    Tahun {{ $displayTahunText }}
                </div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('pra-ekspor.index', ['tahun' => $tahun, 'bulan' => $bulan]) }}"
                    class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali ke Kurasi
                </a>
                <a href="{{ route('pra-ekspor.export-excel-semua', ['tahun' => $tahun, 'bulan' => $bulan]) }}"
                    onclick="confirmExport(event, this, '{{ $displayTahunText }}')" class="btn btn-success fw-bold"
                    style="background: #10b981; border: none;">
                    <i class="fas fa-file-excel me-2"></i> Export Excel
                </a>
            </div>
        </div>

        <div class="d-flex flex-wrap gap-3 mb-4 btn-hide-print">
            <div class="p-3 rounded" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                <div class="d-flex align-items-center gap-3">
                    <span class="fw-bold" style="font-size: 0.9rem; color: #475569;"><i class="fas fa-filter me-1"></i>
                        Filter Tampilan Indikator:</span>
                    <div class="form-check form-switch custom-switch mb-0">
                        <input class="form-check-input filter-kelompok" type="checkbox" value="utama" id="filterUtama"
                            style="cursor: pointer;">
                        <label class="form-check-label" for="filterUtama" style="cursor: pointer;">Utama</label>
                    </div>
                    <div class="form-check form-switch custom-switch mb-0">
                        <input class="form-check-input filter-kelompok" type="checkbox" value="dampak" id="filterDampak"
                            style="cursor: pointer;">
                        <label class="form-check-label" for="filterDampak" style="cursor: pointer;">Dampak</label>
                    </div>
                </div>
                <div class="text-muted mt-1" style="font-size: 0.75rem;">*Jika tidak ada yang dicentang, semua indikator
                    akan ditampilkan.</div>
            </div>

            <div class="p-3 rounded" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                <div class="d-flex align-items-center gap-3">
                    <span class="fw-bold" style="font-size: 0.9rem; color: #475569;"><i
                            class="fas fa-map-marker-alt me-1"></i> Penampil Wilayah:</span>
                    <select class="form-select form-select-sm" id="filterKabupatenSelect"
                        style="width: auto; min-width: 250px; font-size: 0.85rem;">
                        <option value="all">Tampilkan Semua Kabupaten/Kota</option>
                        @foreach($kabupatens as $kab)
                            <option value="{{ $kab->id }}">{{ $kab->nama_kabupaten }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="text-muted mt-1" style="font-size: 0.75rem;">*Pilih kabupaten untuk melihat pratinjau spesifik.
                    (Hasil eksport Excel tetap memuat semua sheets)</div>
            </div>
        </div>

        @foreach($dataPerKabupaten as $kab_id => $data)
            @php
                $kabupaten = $data['kabupaten'];
                $groupedData = $data['groupedData'];
                $unmatchedData = $data['unmatchedData'];
            @endphp
            <div class="export-wrapper kabupaten-wrapper" data-kabupaten-id="{{ $kab_id }}">
                <h5 class="text-center fw-bold mb-4">REKAPITULASI FENOMENA PENDUKUNG ANGKA
                    KEMISKINAN<br>{{ strtoupper($kabupaten->nama_kabupaten) }} TAHUN {{ strtoupper($displayTahunText) }}</h5>

                <div class="table-responsive">
                    <table class="export-table">
                        <thead>
                            <tr>
                                <th rowspan="2" style="width: 3%">No</th>
                                <th rowspan="2" style="width: 20%">Indikator</th>
                                <th colspan="3">Nilai Indikator</th>
                                <th colspan="2">Penjelasan Fenomena Pendukung Angka Kemiskinan<br>(Secara Kualitatif atau
                                    Kuantitatif, kondisi September {{ $baseYear }} dibandingkan Maret {{ $baseYear }})</th>
                            </tr>
                            <tr>
                                <th style="width: 7%">{{ $baseYear - 2 }}</th>
                                <th style="width: 7%">{{ $baseYear - 1 }}</th>
                                <th style="width: 7%">{{ $baseYear }}</th>
                                <th style="width: 28%">NAIK</th>
                                <th style="width: 28%">TURUN</th>
                            </tr>
                            <tr class="cell-numbering">
                                <th>(1)</th>
                                <th>(2)</th>
                                <th>(3)</th>
                                <th>(4)</th>
                                <th>(5)</th>
                                <th>(6)</th>
                                <th>(7)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $no = 1; @endphp
                            @forelse($groupedData as $row)
                                <tr class="indikator-row"
                                    data-kelompok="{{ strtolower($row['indikator']->kelompok ?? 'lainnya') }}">
                                    <td class="text-center row-number">{{ $no++ }}</td>
                                    <td class="fw-bold">{{ $row['indikator']->nama }}</td>
                                    <td></td> {{-- 2023 Placeholder --}}
                                    <td></td> {{-- 2024 Placeholder --}}
                                    <td></td> {{-- 2025 Placeholder --}}

                                    {{-- Fenomena NAIK --}}
                                    <td>
                                        @if(count($row['naik']) > 0)
                                            <ul>
                                                @foreach($row['naik'] as $fen)
                                                    <li>
                                                        {{ rtrim($fen->penjelasan, '.') }}.
                                                        @if($fen->link_berita)
                                                            <a href="{{ $fen->link_berita }}" target="_blank"
                                                                class="text-primary text-decoration-none ms-1 btn-hide-print"
                                                                style="font-size: 0.8rem;">{{ $fen->link_berita }}</a>
                                                        @endif
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <div class="text-center text-muted">-</div>
                                        @endif
                                    </td>

                                    {{-- Fenomena TURUN --}}
                                    <td>
                                        @if(count($row['turun']) > 0)
                                            <ul>
                                                @foreach($row['turun'] as $fen)
                                                    <li>
                                                        {{ rtrim($fen->penjelasan, '.') }}.
                                                        @if($fen->link_berita)
                                                            <a href="{{ $fen->link_berita }}" target="_blank"
                                                                class="text-primary text-decoration-none ms-1 btn-hide-print"
                                                                style="font-size: 0.8rem;">{{ $fen->link_berita }}</a>
                                                        @endif
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <div class="text-center text-muted">-</div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5 indikator-row" data-kelompok="lainnya">
                                        <i class="fas fa-folder-open text-muted fs-3 mb-3 d-block"></i>
                                        Belum ada fenomena yang ditandai/dipilih untuk diekspor pada wilayah/filter ini.
                                    </td>
                                </tr>
                            @endforelse

                            {{-- Data Tanpa Indikator (Fallback) --}}
                            @if(count($unmatchedData) > 0)
                                <tr class="indikator-row" data-kelompok="lainnya">
                                    <td class="text-center row-number">{{ $no++ }}</td>
                                    <td class="fst-italic text-muted">Lainnya (Tanpa Indikator)</td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td colspan="2">
                                        <ul>
                                            @foreach($unmatchedData as $fen)
                                                <li>
                                                    {{ rtrim($fen->penjelasan, '.') }}.
                                                    @if($fen->link_berita)
                                                        <a href="{{ $fen->link_berita }}" target="_blank"
                                                            class="text-primary text-decoration-none ms-1 btn-hide-print"
                                                            style="font-size: 0.8rem;">{{ $fen->link_berita }}</a>
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ul>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach

        <div class="mt-4 text-muted btn-hide-print" style="font-size: 0.8rem; padding-left: 1rem;">
            * Kolom Nilai Indikator dikosongkan untuk dapat diisi nilai absolut manual pada file Excel terkait.<br>
            * Tabel-tabel di atas dapat diekspor langsung ke Microsoft Excel dengan klik tombol Export Excel di atas.<br>
            * File Excel akan berisi banyak sheets untuk setiap Kabupaten/Kota.
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function confirmExport(e, btn, year) {
            e.preventDefault();

            // Collect checked filters
            const checkboxes = document.querySelectorAll('.filter-kelompok');
            const checkedValues = Array.from(checkboxes)
                .filter(cb => cb.checked)
                .map(cb => cb.value.toLowerCase());

            let urlObj = new URL(btn.href);
            if (checkedValues.length > 0) {
                checkedValues.forEach(val => {
                    urlObj.searchParams.append('filters[]', val);
                });
            }

            const url = urlObj.pathname + urlObj.search;
            const originalContent = btn.innerHTML;

            Swal.fire({
                title: `Apakah yakin export Rekap Fenomena Semua Wilayah Tahun ${year}?`,
                html: 'Ketika proses mengekspor data. Mohon jangan meninggalkan halaman sampai proses selesai hingga terlihat "<i class="fas fa-check me-2"></i>Berhasil!". File yang diunduh akan memiliki sheet untuk masing-masing wilayah.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Export',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    btn.classList.add('disabled');
                    btn.style.pointerEvents = 'none';
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Sedang mengunduh..';

                    let timerInterval;
                    let secondsLeft = 5;

                    Swal.fire({
                        title: 'Menyiapkan Data Excel...',
                        html: `Mohon tunggu sejenak (<b>${secondsLeft}</b> detik)...<br><small class="text-muted">Sedang memproses banyak sheet Excel.</small>`,
                        allowOutsideClick: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                            timerInterval = setInterval(() => {
                                secondsLeft--;
                                if (secondsLeft > 0) {
                                    Swal.getHtmlContainer().querySelector('b').textContent = secondsLeft;
                                } else {
                                    Swal.getHtmlContainer().innerHTML = 'Hampir selesai, sedang mengemas file multi-sheet...<br><small class="text-muted">Sedang memproses data Excel.</small>';
                                }
                            }, 1000);
                        },
                        willClose: () => {
                            clearInterval(timerInterval);
                        }
                    });

                    fetch(url)
                        .then(response => {
                            if (!response.ok) throw new Error('Network response was not ok');
                            let filename = `Rekap_Fenomena_Semua_Wilayah_${year}.xlsx`;
                            const disposition = response.headers.get('Content-Disposition');
                            if (disposition && disposition.indexOf('attachment') !== -1) {
                                const filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
                                const matches = filenameRegex.exec(disposition);
                                if (matches != null && matches[1]) {
                                    filename = matches[1].replace(/['"]/g, '');
                                }
                            }
                            return response.blob().then(blob => ({ blob, filename }));
                        })
                        .then(({ blob, filename }) => {
                            const downloadUrl = window.URL.createObjectURL(blob);
                            const a = document.createElement('a');
                            a.style.display = 'none';
                            a.href = downloadUrl;
                            a.download = filename;
                            document.body.appendChild(a);
                            a.click();
                            window.URL.revokeObjectURL(downloadUrl);

                            // Success State
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: 'Data excel multi-sheet berhasil diexport.',
                                showConfirmButton: false,
                                timer: 2500,
                                timerProgressBar: true
                            });

                            btn.innerHTML = '<i class="fas fa-check me-2"></i> Berhasil!';

                            setTimeout(() => {
                                btn.classList.remove('disabled');
                                btn.style.pointerEvents = 'auto';
                                btn.innerHTML = originalContent;
                            }, 2500);
                        })
                        .catch(error => {
                            console.error('Download failed:', error);
                            btn.classList.remove('disabled');
                            btn.style.pointerEvents = 'auto';
                            btn.innerHTML = originalContent;
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: 'Gagal mengunduh file. Silakan coba lagi nanti.'
                            });
                        })
                        .finally(() => {
                            clearInterval(timerInterval);
                        });
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function () {
            const checkboxes = document.querySelectorAll('.filter-kelompok');
            const kabupatenSelect = document.getElementById('filterKabupatenSelect');
            const wrappers = document.querySelectorAll('.kabupaten-wrapper');

            function applyFilter() {
                const checkedValues = Array.from(checkboxes)
                    .filter(cb => cb.checked)
                    .map(cb => cb.value.toLowerCase());

                const selectedKabupaten = kabupatenSelect.value;

                wrappers.forEach(wrapper => {
                    // Kabupaten filtering
                    if (selectedKabupaten !== 'all' && wrapper.dataset.kabupatenId !== selectedKabupaten) {
                        wrapper.style.display = 'none';
                        return;
                    } else {
                        wrapper.style.display = '';
                    }

                    // Indikator filtering within visible wrapper
                    let currentNo = 1;
                    const rows = wrapper.querySelectorAll('.indikator-row');

                    rows.forEach(row => {
                        const kelompok = (row.dataset.kelompok || '').toLowerCase();

                        if (checkedValues.length === 0) {
                            row.style.display = '';
                            const noEl = row.querySelector('.row-number');
                            if (noEl) noEl.textContent = currentNo++;
                        } else {
                            if (checkedValues.includes(kelompok)) {
                                row.style.display = '';
                                const noEl = row.querySelector('.row-number');
                                if (noEl) noEl.textContent = currentNo++;
                            } else {
                                row.style.display = 'none';
                            }
                        }
                    });
                });
            }

            checkboxes.forEach(cb => cb.addEventListener('change', applyFilter));
            kabupatenSelect.addEventListener('change', applyFilter);
        });
    </script>
@endpush