@extends('layouts.admin')

@section('title', 'Rentang Harga - BPS Kalbar')

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
    <style>
        .ts-control {
            border-radius: 8px !important;
            padding: 8px 12px !important;
            background-color: #f8f9fa !important;
            border: 1px solid #dee2e6 !important;
        }

        .ts-dropdown .active {
            background-color: var(--bps-blue) !important;
            color: #fff !important;
        }
    /* Custom Scrollbar for Analysis Area */
            .analysis-scroll-area {
                max-height: 600px;
                overflow-y: auto;
                overflow-x: hidden;
                padding-right: 5px;
            }

            .analysis-scroll-area::-webkit-scrollbar {
                width: 6px;
            }

            .analysis-scroll-area::-webkit-scrollbar-track {
                background: #f1f1f1;
                border-radius: 10px;
            }

            .analysis-scroll-area::-webkit-scrollbar-thumb {
                background: #ccc;
                border-radius: 10px;
            }

            .analysis-scroll-area::-webkit-scrollbar-thumb:hover {
                background: var(--bps-blue);
            }
        </style>
@endpush
@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // Initialize Tom Select for Category Filter
            new TomSelect('#categoryFilter', {
                plugins: ['remove_button'],
                maxOptions: null,
                placeholder: 'Cari kategori...',
                render: {
                    no_results: function (data, escape) {
                        return '<div class="no-results">Kategori "' + escape(data.input) + '" tidak ditemukan</div>';
                    },
                }
            });

            // Initialize Tom Select for Kabupaten Filter (Analysis)
            new TomSelect('#kabupatenFilter', {
                plugins: ['remove_button'],
                maxOptions: null,
                placeholder: 'Cari kabupaten...',
                render: {
                    no_results: function (data, escape) {
                        return '<div class="no-results">Kabupaten "' + escape(data.input) + '" tidak ditemukan</div>';
                    },
                }
            });

            // Initialize Tom Select for Commodity Filter (Analysis)
            new TomSelect('#commodityFilter', {
                plugins: ['remove_button'],
                maxOptions: null,
                placeholder: 'Cari komoditas...',
                render: {
                    no_results: function (data, escape) {
                        return '<div class="no-results">Komoditas "' + escape(data.input) + '" tidak ditemukan</div>';
                    },
                }
            });
        });
    </script>
@endpush

@push('scripts')
    <script>
        function confirmExport(e, btn, year) {
            e.preventDefault();
            const url = new URL(btn.href).pathname + new URL(btn.href).search;
            const originalContent = btn.innerHTML;

            Swal.fire({
                title: `Apakah yakin export RH Tahun ${year} ini?`,
                html: 'Ketika proses mengekspor data. Mohon jangan meninggalkan halaman sampai proses selesai hingga terlihat "<i class="fas fa-check me-2"></i>Berhasil!"',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Export',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Disable button and show loading state
                    btn.classList.add('disabled');
                    btn.style.pointerEvents = 'none';
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Sedang mengunduh..';

                    let timerInterval;
                    let secondsLeft = 30;

                    Swal.fire({
                        title: 'Menyiapkan Data...',
                        html: `Mohon tunggu sejenak (<b>${secondsLeft}</b> detik)...<br><small class="text-muted">Sedang memproses ribuan baris data.</small>`,
                        allowOutsideClick: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                            timerInterval = setInterval(() => {
                                secondsLeft--;
                                if (secondsLeft > 0) {
                                    Swal.getHtmlContainer().querySelector('b').textContent = secondsLeft;
                                } else {
                                    Swal.getHtmlContainer().innerHTML = 'Hampir selesai, sedang mengemas file...<br><small class="text-muted">Sedang memproses ribuan baris data.</small>';
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
                            let filename = `RH_${year}.xlsx`;
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
                                text: 'Data berhasil diexport.',
                                showConfirmButton: false,
                                timer: 2000,
                                timerProgressBar: true

                            });

                            btn.innerHTML = '<i class="fas fa-check me-2"></i> Berhasil!';

                            setTimeout(() => {
                                btn.classList.remove('disabled');
                                btn.style.pointerEvents = 'auto';
                                btn.innerHTML = originalContent;
                            }, 2000);
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
    </script>
@endpush

@section('content')
    <div class="fade-in-up">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
            <div>
                <h2 class="fw-bold mb-1" style="color: var(--bps-orange);">Rentang Harga</h2>
                <p class="text-muted mb-0">Visualisasi data rentang harga komoditas di Kalimantan Barat</p>
            </div>
            <div class="mt-3 mt-md-0 d-flex gap-2">
                @if (auth()->check() == true)
                    @if (auth()->user()->status == 'active' && auth()->user()->kabupaten->kode_kab != '6100')

                        <!-- <a href="{{ route('rh-nilai.index') }}" class="btn fw-bold shadow-sm"
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    style="background-color: #fff; color: var(--bps-orange); border: 1px solid var(--bps-orange);">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <i class="fas fa-edit me-1"></i> Input Nilai RH Kabupaten
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                </a> -->
                        <a href="/price-range/input-nilai?rh_tahun_id=&kabupaten_id={{ auth()->user()->kabupaten->id }}&revision_id={{ $idMaxRHPerubahan }}"
                            class="btn fw-bold shadow-sm"
                            style="background-color: #fff; color: var(--bps-orange); border: 1px solid var(--bps-orange);">
                            <i class="fas fa-edit me-1"></i> Input Nilai RH Kabupaten
                        </a>
                    @endif

                    @if (auth()->user()->status == 'active' && auth()->user()->kabupaten->kode_kab == '6100')

                        <a href="/price-range/input-nilai" class="btn fw-bold shadow-sm"
                            style="background-color: #fff; color: var(--bps-orange); border: 1px solid var(--bps-orange);">
                            <i class="fas fa-edit me-1"></i> Input Nilai RH Kabupaten
                        </a>
                        <a href="{{ route('price-range.input') }}" class="btn text-white fw-bold shadow-sm"
                            style="background-color: var(--bps-blue);">
                            <i class="fas fa-cog me-1 fa-spin"></i> Kelola Komoditas
                        </a>
                    @endif
                @endif
            </div>
        </div>
        @if ($selectedYearId && $selectedKabupatenId)
            <!-- Analisis Insight Section -->
            <div class="card border-0 shadow-sm mb-4" id="analysis-section" style="border-radius: 12px;">
                <div class="card-header bg-white py-3">
                    <div class="row align-items-center">
                        <div class="col-md-8 col-12 mb-2 mb-md-0">
                            <h5 class="fw-bold mb-0 text-dark">
                                <i class="fas fa-search-dollar me-2 text-primary"></i>Analisis Harga Antar Kabupaten
                                {{ $activeYear->tahun }}
                            </h5>
                        </div>
                        <div class="col-md-4 col-12 text-md-end">
                            <div class="d-inline-flex align-items-center gap-2">
                                <label class="small fw-bold text-muted mb-0">BATAS</label>
                                <div class="input-group input-group-sm" style="max-width: 120px;">
                                    <input type="number" name="threshold" id="thresholdInput"
                                        class="form-control text-center fw-bold" value="{{ request('threshold') }}"
                                        form="filter-form" oninput="validateThresholdInput(this)"
                                        onchange="submitThreshold(this)">
                                    <span class="input-group-text fw-bold">%</span>
                                </div>
                            </div>
                            <input type="hidden" name="active_tab" id="activeTabInput"
                                value="{{ request('active_tab', array_key_first($outliers ?? [])) }}" form="filter-form">
                            <div class="mt-1">
                                <small id="thresholdError" class="text-danger {{ request('threshold') ? 'd-none' : '' }}"
                                    style="font-size: 0.75rem;">
                                    Masukan angka 1-100
                                </small>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 row g-2">
                        <div class="col-md-4">
                            <label class="small fw-bold text-muted text-uppercase mb-2 d-block">Filter Kategori
                                (Analisis)</label>
                            <select name="category_ids[]" id="categoryFilter" class="form-select form-select-sm" multiple
                                form="filter-form" placeholder="Pilih satu atau lebih kategori..."
                                onchange="this.form.submit()">
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ in_array($cat->id, $selectedCategoryIds) ? 'selected' : '' }}>
                                        {{ $cat->nama_kategori }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="small fw-bold text-muted text-uppercase mb-2 d-block">PILIH KOMODITAS
                                (ANALISIS)</label>
                            <select name="commodity_ids[]" id="commodityFilter" class="form-select form-select-sm" multiple
                                form="filter-form" placeholder="Cari komoditas..." onchange="this.form.submit()">
                                @foreach($availableKomoditas as $kom)
                                    <option value="{{ $kom->id }}" {{ in_array($kom->id, $selectedCommodityIds) ? 'selected' : '' }}>
                                        {{ $kom->nama_komoditas }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="small fw-bold text-muted text-uppercase mb-2 d-block">PILIH KABUPATEN
                                (ANALISIS)</label>
                            <select name="analysis_kabupaten_ids[]" id="kabupatenFilter" class="form-select form-select-sm"
                                multiple form="filter-form" placeholder="Cari kabupaten..." onchange="this.form.submit()">
                                @foreach($kabupatens as $kab)
                                    <option value="{{ $kab->id }}" {{ in_array($kab->id, $selectedAnalysisKabupatenIds) ? 'selected' : '' }}>
                                        {{ $kab->nama_kabupaten }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <small class="text-muted">Menampilkan kabupaten dengan harga yang menyimpang jauh
                        ({{ $thresholdPercent ? ($thresholdPercent * 100) . '%' : '>...%' }}) dari
                        rata-rata kabupaten.</small>
                </div>
                <div id="analysis-content" class="{{ $thresholdPercent === null ? 'd-none' : '' }}">
                    <div class="card-body">
                        <ul class="nav nav-tabs flex-nowrap overflow-auto mb-3 pb-1" id="insightTabs" role="tablist"
                            style="white-space: nowrap;">
                            @php
                                $activeTab = request('active_tab', array_key_first($outliers ?? []));
                            @endphp
                            @foreach($outliers as $periodName => $data)
                                <li class="nav-item flex-shrink-0" role="presentation">
                                    <button class="nav-link {{ $activeTab == $periodName ? 'active' : '' }} fw-bold"
                                        id="tab-{{ Str::slug($periodName) }}" data-bs-toggle="tab"
                                        data-bs-target="#content-{{ Str::slug($periodName) }}" type="button" role="tab"
                                        onclick="setActiveTab('{{ $periodName }}')">
                                        {{ $periodName }} ({{ count($data) }} Ditemukan)
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                        <div class="tab-content analysis-scroll-area" id="insightTabsContent">
                            @foreach($outliers as $periodName => $commodities)

                                <div class="tab-pane fade {{ $activeTab == $periodName ? 'show active' : '' }}"
                                    id="content-{{ Str::slug($periodName) }}" role="tabpanel">

                                    @if(empty($commodities))
                                        <div class="text-center py-4">
                                            <p class="text-muted mb-0">Tidak ditemukan anomali harga signifikan pada periode ini.</p>
                                        </div>
                                    @else
                                        <div class="row g-3">
                                            @foreach($commodities as $komoditasName => $details)
                                                @if($komoditasName !== 'unit')
                                                    <div class="col-md-6">
                                                        <div class="card h-100 border bg-light">
                                                            <div class="card-body py-2 px-3">
                                                                <strong class="d-block mb-1 text-primary">
                                                                    {{ $komoditasName }} <span
                                                                        class="badge bg-secondary ms-1 fw-normal">{{ $details['unit'] ?? '' }}</span>
                                                                </strong>
                                                                <div
                                                                    class="d-flex justify-content-between small text-muted border-bottom pb-2 mb-2">
                                                                    <span>Avg Min:
                                                                        <b>{{ number_format($details['avg_min'] ?? 0, 0, ',', '.') }}</b></span>
                                                                    <span>Avg Max:
                                                                        <b>{{ number_format($details['avg_max'] ?? 0, 0, ',', '.') }}</b></span>
                                                                </div>

                                                                <div class="row small">
                                                                    <!-- Below Average -->
                                                                    <div class="col-6 border-end">
                                                                        <span class="text-success fw-bold d-block mb-1"><i
                                                                                class="fas fa-arrow-down me-1"></i>Jauh Di Bawah Rata2</span>
                                                                        @forelse($details['below'] as $item)
                                                                            <div class="mb-1">
                                                                                <span class="fw-bold">[{{ $item['kode_kab'] }}]
                                                                                    {{ $item['kab'] }}</span>
                                                                                <br>
                                                                                <span class="text-muted">Rp
                                                                                    {{ number_format($item['val'], 0, ',', '.') }}</span>
                                                                                <span
                                                                                    class="badge bg-success bg-opacity-10 text-success ms-1">-{{ $item['diff'] }}</span>
                                                                            </div>
                                                                        @empty
                                                                            <span class="text-muted fst-italic">-</span>
                                                                        @endforelse
                                                                    </div>

                                                                    <!-- Above Average -->
                                                                    <div class="col-6 ps-3">
                                                                        <span class="text-danger fw-bold d-block mb-1"><i
                                                                                class="fas fa-arrow-up me-1"></i>Jauh Di Atas Rata2</span>
                                                                        @forelse($details['above'] as $item)
                                                                            <div class="mb-1">
                                                                                <span class="fw-bold">[{{ $item['kode_kab'] }}]
                                                                                    {{ $item['kab'] }}</span>
                                                                                <br>
                                                                                <span class="text-muted">Rp
                                                                                    {{ number_format($item['val'], 0, ',', '.') }}</span>
                                                                                <span
                                                                                    class="badge bg-danger bg-opacity-10 text-danger ms-1">+{{ $item['diff'] }}</span>
                                                                            </div>
                                                                        @empty
                                                                            <span class="text-muted fst-italic">-</span>
                                                                        @endforelse
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="card-body py-5 text-center {{ $thresholdPercent === null ? '' : 'd-none' }}"
                    id="analysis-empty-state">
                    <i class="fas fa-percentage mb-3 text-muted" style="font-size: 3rem; opacity: 0.3;"></i>
                    <h4 class="fw-bold text-dark mb-2">Mohon inputan angka persen</h4>
                    <p class="text-muted mb-0">Masukkan angka 1-100 untuk melihat analisis anomali.</p>
                </div>
            </div>
            <!-- Info Batas Selisih & Legend -->
            <div class="alert alert-info border-0 shadow-sm mb-4" role="alert"
                style="background-color: rgba(13, 202, 240, 0.1); color: #055160;">
                <div class="d-flex align-items-start">
                    <div class="me-3 mt-1">
                        <i class="fas fa-info-circle fs-4"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h5 class="alert-heading fw-bold mb-3" style="font-size: 1rem;">Informasi & Keterangan</h5>
                        <div class="row g-3">
                            <div class="col-12">
                                <strong class="d-block mb-2 text-uppercase small opacity-75">Legenda Warna</strong>
                                <div class="d-flex flex-wrap gap-3">
                                    <div class="d-flex align-items-center">
                                        <div class="rounded me-2 bg-warning-light border border-warning"
                                            style="width: 18px; height: 18px; flex-shrink: 0;"></div>
                                        <span class="small" style="line-height: 1.2;">
                                            <b>Kuning:</b> Ada perubahan data (Input/Edit) dari periode sebelumnya.
                                        </span>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded me-2 bg-success-light border border-success"
                                            style="width: 18px; height: 18px; flex-shrink: 0;"></div>
                                        <span class="small" style="line-height: 1.2;">
                                            <b>Hijau:</b> Selisih harga telah disetujui (Verified).
                                        </span>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded me-2 bg-danger-light border border-danger"
                                            style="width: 18px; height: 18px; flex-shrink: 0;"></div>
                                        <span class="small" style="line-height: 1.2;">
                                            <b>Merah:</b> Selisih harga melebihi batas wajar, telah diverifikasi namun ditolak
                                            dan menunggu perbaikan dari admin kabupaten/kota.
                                        </span>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded me-2 bg-secondary-light border border-secondary"
                                            style="width: 18px; height: 18px; flex-shrink: 0;"></div>
                                        <span class="small" style="line-height: 1.2;">
                                            <b>Abu-abu:</b> Data dalam status pending (Menunggu verifikasi).
                                        </span>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded me-2 bg-white border"
                                            style="width: 18px; height: 18px; flex-shrink: 0;"></div>
                                        <span class="small" style="line-height: 1.2;">
                                            <b>Putih:</b> Tidak ada perubahan sama dengan periode sebelumnya atau tidak melebihi
                                            batas selisih.
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        <!-- Filters & Export -->
        <!-- Rejected Summary Alert -->
        @if(!empty($rejectedSummary))
            <div class="alert alert-danger border-0 shadow-sm mb-4" role="alert" style="background-color: #fff5f5;">
                <div class="d-flex align-items-center mb-2">
                    <i class="fas fa-exclamation-triangle text-danger fs-4 me-2"></i>
                    <div>
                        <h5 class="alert-heading fw-bold mb-0 text-danger">Perhatian: Terdapat Data Ditolak</h5>
                        <p class="mb-0 small text-muted">Berikut adalah daftar kabupaten/kota yang memiliki data ditolak dan
                            perlu perbaikan:</p>
                    </div>
                </div>
                <hr class="text-danger opacity-25 my-2">
                <div class="row g-2">
                    @foreach($rejectedSummary as $summary)
                        <div class="col-md-4 col-sm-6">
                            <div
                                class="d-flex justify-content-between align-items-center bg-white p-2 rounded border border-danger border-opacity-25">
                                <span class="fw-bold text-dark small">[{{ $summary['kode_kab'] }}] {{ $summary['nama_kab'] }}</span>
                                <span class="badge bg-danger rounded-pill">{{ $summary['total'] }} Item</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="card border-0 shadow-sm mb-1" id="filter-section" style="border-radius: 12px;">
            <div class="card-body p-4">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3">
                    <form action="{{ route('price-range.index') }}" method="GET" class="row g-3 flex-grow-1 align-items-end"
                        id="filter-form">
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted text-uppercase">Pilih Tahun</label>
                            <select name="year_id" class="form-select border-0 bg-light shadow-none"
                                onchange="this.form.submit()">
                                @foreach($years as $yr)
                                    <option value="{{ $yr->id }}" {{ $selectedYearId == $yr->id ? 'selected' : '' }}>
                                        {{ $yr->tahun }} {{ $yr->is_active ? '(Aktif)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted text-uppercase">Pilih Kabupaten</label>
                            <select name="kabupaten_id" class="form-select border-0 bg-light shadow-none"
                                onchange="this.form.submit()">

                                @foreach($kabupatens as $kab)
                                    <option value="{{ $kab->id }}" {{ $selectedKabupatenId == $kab->id ? 'selected' : '' }}>
                                        [{{ $kab->kode_kab }}] {{ $kab->nama_kabupaten }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @if(isset($activeYear) && $years->contains('tahun', $activeYear->tahun - 1))
                            <div class="col-md-4 d-flex align-items-center mb-1">
                                <div class="form-check form-switch cursor-pointer">
                                    <input class="form-check-input my-cursor-pointer" type="checkbox" role="switch"
                                        id="togglePrevYear">
                                    <label class="form-check-label small fw-bold text-muted text-uppercase cursor-pointer"
                                        for="togglePrevYear">TAMPILKAN
                                        {{ isset($activeYear) ? 'akhir ' . ($activeYear->tahun - 1) : 'TAHUN SEBELUMNYA' }}</label>
                                </div>
                            </div>
                        @endif
                    </form>

                    @if($selectedYearId && $selectedKabupatenId)
                        <div class="ms-md-auto">
                            <a href="{{ route('price-range.export', ['year_id' => $selectedYearId, 'kabupaten_id' => $selectedKabupatenId]) }}"
                                onclick="confirmExport(event, this, '{{ $activeYear->tahun }}')"
                                class="btn btn-success fw-bold px-4 shadow-sm h-100 d-flex align-items-center">
                                <i class="fas fa-file-excel me-2"></i> Export Excel
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        @if($activeYear && $selectedKabupatenId)

            <!-- Data Table -->
            <div id="commodity-table" class="card border-0 shadow-sm overflow-hidden" style="border-radius: 12px;">
                <div class="table-responsive" id="mainTableContainer"
                    style="max-height: var(--table-height, 80vh); overflow-y: auto;">
                    <table class="table table-bordered align-middle mb-0">
                        <thead class="bg-light text-center align-middle sticky-header">
                            <tr>
                                <th rowspan="2" class="ps-4 sticky-col-1">KOMODITAS</th>
                                <th rowspan="2" class="sticky-col-2">SATUAN</th>
                                <th rowspan="2" class="sticky-col-3">BATAS HARGA</th>
                                <th colspan="3" class="bg-light text-muted prev-year-col d-none">AKHIR
                                    {{ $activeYear->tahun - 1 }}
                                </th>
                                <th colspan="3" class="bg-blue-light text-blue">MASTER NILAI
                                    ({{ substr($activeYear->tahun, -2) }})</th>

                                @foreach($revisions as $rev)
                                    <th colspan="3" class="bg-orange-light text-orange">{{ strtoupper($rev->label) }}</th>
                                @endforeach
                            </tr>
                            <tr>
                                <th style="width: 100px;" class="bg-light text-muted small prev-year-col d-none">MIN</th>
                                <th style="width: 100px;" class="bg-light text-muted small prev-year-col d-none">MAX</th>
                                <th style="width: 180px;" class="bg-light text-muted small prev-year-col d-none">ALASAN</th>
                                <th style="width: 100px;" class="bg-blue-light text-blue small">MIN</th>
                                <th style="width: 100px;" class="bg-blue-light text-blue small">MAX</th>
                                <th style="width: 180px;" class="bg-blue-light text-blue small">ALASAN</th>

                                @foreach($revisions as $rev)
                                    <th style="width: 100px;" class="bg-orange-light text-orange small">MIN</th>
                                    <th style="width: 100px;" class="bg-orange-light text-orange small">MAX</th>
                                    <th style="width: 180px;" class="bg-orange-light text-orange small">ALASAN</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($categories as $category)
                                <tr class="bg-light">
                                    @php
                                        $colspan = 6 + ($revisions->count() * 3);
                                    @endphp
                                    <td colspan="{{ $colspan }}"
                                        class="ps-4 fw-bold text-muted small text-uppercase py-2 category-header-cell sticky-col-category">
                                        <div class="sticky-category-name">
                                            <i class="fas fa-folder-open me-1"></i> {{ $category->nama_kategori }}
                                        </div>
                                    </td>
                                </tr>
                                @foreach($category->komoditas as $komo)
                                    @php
                                        $master = $masterNilai->get($komo->id);
                                    @endphp

                                    <tr>
                                        <td class="ps-4 sticky-col-1 bg-white">{{ $komo->nama_komoditas }}</td>
                                        <td class="text-center sticky-col-2 bg-white">
                                            <span class="badge bg-light text-dark border fw-normal">{{ $komo->satuan ?? 'Kg' }}</span>
                                        </td>
                                        <td class="text-center sticky-col-3 bg-white">
                                            {{ number_format($komo->batas_selisih_harga ?? 0, 0, ',', '.') }}
                                        </td>

                                        <!-- Master Data -->
                                        @php
                                            $masterDiff = ($master && $master->max_nilai !== null && $master->min_nilai !== null)
                                                ? ($master->max_nilai - $master->min_nilai)
                                                : 0;
                                            $limit = $komo->batas_selisih_harga ?? 0;
                                            // $isMasterExceeded moved below to check inheritance

                                            // Previous Year Data extraction
                                            $prevData = isset($prevYearValues) ? ($prevYearValues[$komo->id] ?? null) : null;
                                            $prevMin = $prevData['min'] ?? null;
                                            $prevMax = $prevData['max'] ?? null;
                                            $prevAlasan = $prevData['alasan'] ?? '-';

                                            // Highlight Logic: If Master differs from Prev Year
                                            $isMinChanged = $master && $master->min_nilai !== null && $prevMin !== null && $master->min_nilai != $prevMin;
                                            $isMaxChanged = $master && $master->max_nilai !== null && $prevMax !== null && $master->max_nilai != $prevMax;

                                            // Fix: If values are inherited (same as prev), do not flag as exceeded (Red)
                                            $isInherited = ($prevMin !== null && $master && $master->min_nilai == $prevMin)
                                                && ($prevMax !== null && $master && $master->max_nilai == $prevMax);

                                            // Highlight red ONLY if it's the final state (no revisions) AND missing reason
                                            $isMasterExceeded = ($masterDiff > $limit) && empty($master->alasan) && ($revisions->count() == 0);
                                        @endphp

                                        <!-- Previous Year Columns (Hidden by Default) -->
                                        <td class="text-center bg-light text-muted prev-year-col d-none">
                                            {{ $prevMin !== null ? number_format($prevMin, 0, ',', '.') : '-' }}
                                        </td>
                                        <td class="text-center bg-light text-muted prev-year-col d-none">
                                            {{ $prevMax !== null ? number_format($prevMax, 0, ',', '.') : '-' }}
                                        </td>
                                        <td class="small bg-light text-muted prev-year-col d-none">
                                            {{ $prevAlasan }}
                                        </td>

                                        <!-- Actual Master Columns -->
                                        <td
                                            class="text-center {{ $isMinChanged ? 'bg-warning-light fw-bold text-dark' : 'bg-blue-faded' }}">
                                            {{ $master && $master->min_nilai !== null ? number_format($master->min_nilai, 0, ',', '.') : '-' }}
                                        </td>
                                        <td
                                            class="text-center {{ $isMaxChanged ? 'bg-warning-light fw-bold text-dark' : 'bg-blue-faded' }}">
                                            {{ $master && $master->max_nilai !== null ? number_format($master->max_nilai, 0, ',', '.') : '-' }}
                                        </td>
                                        @php
                                            $mIsApproved = ($master->verification_status ?? '') === 'approved'
                                                && ($master->min_nilai ?? null) !== null
                                                && ($master->max_nilai ?? null) !== null
                                                && !empty($master->alasan);

                                            $mIsPending = ($master->verification_status ?? '') === 'pending'
                                                && ($master->min_nilai ?? null) !== null
                                                && ($master->max_nilai ?? null) !== null
                                                && !empty($master->alasan);
                                        @endphp
                                        <td class="small {{ $mIsApproved ? 'bg-success-light fw-bold text-dark' : ($mIsPending ? 'bg-secondary-light fw-bold text-dark' : ($isMasterExceeded ? 'bg-danger-light fw-bold text-dark' : 'bg-blue-faded text-muted')) }}"
                                            title="{{ $isMasterExceeded ? 'Selisih harga melebihi batas (Rp ' . number_format($masterDiff, 0, ',', '.') . ')' : '' }}">
                                            {{ $master->alasan ?? '-' }}
                                        </td>

                                        @php
                                            // Initialize tracking for carry-forward logic
                                            $currentMin = $master ? $master->min_nilai : null;
                                            $currentMax = $master ? $master->max_nilai : null;
                                            $currentAlasan = $master ? $master->alasan : null;

                                            // REMOVED: Apply within-range reset logic for Master
                                            // We keep it so it can be carried forward to revisions
                                            // only the final state (last revision) will be subject to auto-reset
                                        @endphp

                                        <!-- Revision Data -->
                                        @foreach($revisions as $rev)
                                            @php
                                                $revData = isset($revisionDetails[$rev->id]) ? $revisionDetails[$rev->id]->get($komo->id) : null;

                                                $isMinEdit = false;
                                                $isMaxEdit = false;
                                                $isAlasanEdit = false;

                                                // Min Carry Forward
                                                if ($revData && $revData->min_edit !== null) {
                                                    if ($currentMin != $revData->min_edit) {
                                                        $isMinEdit = true;
                                                    }
                                                    $currentMin = $revData->min_edit;
                                                }

                                                // Max Carry Forward
                                                if ($revData && $revData->max_edit !== null) {
                                                    if ($currentMax != $revData->max_edit) {
                                                        $isMaxEdit = true;
                                                    }
                                                    $currentMax = $revData->max_edit;
                                                }

                                                // Alasan Carry Forward & Reset Logic
                                                if ($revData) {
                                                    if ($revData->alasan !== null) {
                                                        $currentAlasan = $revData->alasan;
                                                        $isAlasanEdit = true;
                                                    } elseif ($revData->min_edit !== null || $revData->max_edit !== null) {
                                                        // If value updated but reason not provided => reset reason
                                                        $currentAlasan = null;
                                                    }
                                                }

                                                // Check Difference Limit
                                                $currentDiff = ($currentMax !== null && $currentMin !== null) ? ($currentMax - $currentMin) : 0;
                                                $limit = $komo->batas_selisih_harga ?? 0;
                                                $isDiffExceeded = $currentDiff > $limit;

                                                // Additional check: If now within range after edit, reset reason ONLY for the last revision
                                                if ($currentDiff <= $limit && $loop->last) {
                                                    $currentAlasan = null;
                                                }

                                                // Check if there was an edit in this revision
                                                $hasEdit = ($revData && ($revData->min_edit !== null || $revData->max_edit !== null));

                                                // Highlight red ONLY if it's the final state (last revision) AND missing reason
                                                $isExceeded = $isDiffExceeded && empty($currentAlasan) && $loop->last;

                                                // Approved Logic: Must be approved AND have Min, Max, Alasan in THIS revision
                                                $revIsApproved = ($revData->verification_status ?? '') === 'approved'
                                                    && ($revData->min_edit ?? null) !== null
                                                    && ($revData->max_edit ?? null) !== null
                                                    && !empty($revData->alasan);

                                                // Pending Logic: Must be pending AND have Min, Max, Alasan in THIS revision
                                                $revIsPending = ($revData->verification_status ?? '') === 'pending'
                                                    && ($revData->min_edit ?? null) !== null
                                                    && ($revData->max_edit ?? null) !== null
                                                    && !empty($revData->alasan);
                                            @endphp
                                            <td
                                                class="text-center {{ $isMinEdit ? 'bg-warning-light fw-bold text-dark' : 'bg-orange-faded' }}">
                                                {{ $currentMin !== null ? number_format($currentMin, 0, ',', '.') : '-' }}
                                            </td>
                                            <td
                                                class="text-center {{ $isMaxEdit ? 'bg-warning-light fw-bold text-dark' : 'bg-orange-faded' }}">
                                                {{ $currentMax !== null ? number_format($currentMax, 0, ',', '.') : '-' }}
                                            </td>
                                            <td class="small {{ $revIsApproved ? 'bg-success-light fw-bold text-dark' : ($revIsPending ? 'bg-secondary-light fw-bold text-dark' : ($isExceeded ? 'bg-danger-light fw-bold text-dark' : ($isAlasanEdit ? 'bg-warning-light fw-bold text-dark' : 'bg-orange-faded text-muted'))) }}"
                                                title="{{ $isExceeded ? 'Selisih harga melebihi batas (Rp ' . number_format($currentDiff, 0, ',', '.') . ')' : '' }}">
                                                {{ $currentAlasan ?? '-' }}
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="card border-0 shadow-sm p-5 text-center" style="border-radius: 12px;">
                <i class="fas fa-chart-line mb-3 text-muted" style="font-size: 4rem;"></i>
                <h4 class="fw-bold">Visualisasi Data Rentang Harga</h4>
                <p class="text-muted">Data visualisasi akan muncul di sini setelah tahun dan kabupaten dipilih.</p>
            </div>
        @endif
    </div>

    <style>
        .bg-blue-light {
            background-color: rgba(0, 147, 221, 0.05);
        }

        .bg-blue-faded {
            background-color: rgba(0, 147, 221, 0.02);
        }

        .text-blue {
            color: var(--bps-blue);
        }

        .bg-orange-light {
            background-color: rgba(255, 140, 0, 0.05);
        }

        .bg-orange-faded {
            background-color: rgba(255, 140, 0, 0.02);
        }

        .bg-warning-light {
            background-color: rgba(255, 193, 7, 0.15) !important;
        }

        .bg-success-light {
            background-color: rgba(25, 135, 84, 0.15) !important;
        }

        .bg-danger-light {
            background-color: rgba(220, 53, 69, 0.15) !important;
        }

        .bg-secondary-light {
            background-color: rgba(108, 117, 125, 0.15) !important;
        }

        .text-orange {
            color: var(--bps-orange);
        }

        table th {
            font-weight: 700;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
        }

        .table-bordered> :not(caption)>*>* {
            border-width: 1px;
            border-color: #f1f5f9;
        }

        @media (min-width: 992px) {
            .sticky-header {
                position: sticky;
                top: 0;
                z-index: 4;
            }

            /* Sticky Columns for Rentang Harga Table */
            .sticky-col-1 {
                position: sticky !important;
                left: 0;
                z-index: 2;
                min-width: 250px;
                max-width: 250px;
                /* use box-shadow to simulate right border without breaking sticky layout */
                box-shadow: inset -1px 0 0 #f1f5f9;
            }

            .sticky-col-2 {
                position: sticky !important;
                left: 250px;
                z-index: 2;
                min-width: 100px;
                max-width: 100px;
                box-shadow: inset -1px 0 0 #f1f5f9;
            }

            .sticky-col-3 {
                position: sticky !important;
                left: 350px;
                z-index: 2;
                min-width: 130px;
                max-width: 130px;
                border-right: 2px solid #dee2e6 !important;
            }

            .sticky-col-category {
                position: sticky !important;
                left: 0;
                z-index: 3;
                /* Lower than main headers */
                background-color: #f8f9fa !important;
            }

            .sticky-category-name {
                position: sticky;
                left: 1.5rem;
                /* Match ps-4 padding-start */
                display: inline-block;
                white-space: nowrap;
            }

            /* Ensure Table Headers stay above scrolling content */
            thead .sticky-col-1,
            thead .sticky-col-2,
            thead .sticky-col-3 {
                z-index: 5 !important;
                background-color: #f8f9fa !important;
            }

            /* Apply solid background to td so they hide scrolling behind them */
            tbody .sticky-col-1,
            tbody .sticky-col-2,
            tbody .sticky-col-3 {
                background-color: #fff !important;
            }

            /* Fix the row coloring overlapping */
            table tbody tr:hover .sticky-col-1,
            table tbody tr:hover .sticky-col-2,
            table tbody tr:hover .sticky-col-3 {
                background-color: #f8f9fa !important;
            }
        }

        /* Custom Adjustments for Full Height Mode */
        @media (min-width: 992px) {
            .main-content {
                height: 100vh;
            }

            .sticky-header {
                position: sticky;
                top: 0;
                z-index: 4;
            }
        }
    </style>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const urlParams = new URLSearchParams(window.location.search);
            const threshold = urlParams.get('threshold');
            const hasThresholdValue = threshold !== null && threshold !== "";
            const hasYear = urlParams.has('year_id');
            const hasKab = urlParams.has('kabupaten_id');
            const activeTab = urlParams.get('active_tab');

            if (hasThresholdValue || activeTab) {
                var analysisElement = document.getElementById('analysis-section');
                if (analysisElement) {
                    setTimeout(function () {
                        analysisElement.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }, 500);
                }
            } else if (hasYear || hasKab) {
                var filterSection = document.getElementById('filter-section');
                if (filterSection) {
                    setTimeout(function () {
                        filterSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }, 500);
                }
            }
        });

        document.addEventListener("DOMContentLoaded", function () {

            const togglePrev = document.getElementById('togglePrevYear');
            if (togglePrev) {
                togglePrev.addEventListener('change', function () {
                    const cols = document.querySelectorAll('.prev-year-col');
                    cols.forEach(col => {
                        if (this.checked) {
                            col.classList.remove('d-none');
                        } else {
                            col.classList.add('d-none');
                        }
                    });



                    const catRows = document.querySelectorAll('.category-header-cell');
                    catRows.forEach(td => {
                        let current = parseInt(td.getAttribute('colspan'));
                        if (this.checked) {
                            td.setAttribute('colspan', current + 3);
                        } else {
                            td.setAttribute('colspan', current - 3);
                        }
                    });
                });
            }
        });

        function validateThresholdInput(input) {
            let val = input.value;
            const errorElement = document.getElementById('thresholdError');
            const contentElement = document.getElementById('analysis-content');
            const emptyStateElement = document.getElementById('analysis-empty-state');

            if (val === "" || val === null) {
                errorElement.classList.remove('d-none');
                if (contentElement) contentElement.classList.add('d-none');
                if (emptyStateElement) emptyStateElement.classList.remove('d-none');
                return;
            }

            errorElement.classList.add('d-none');
            if (contentElement) contentElement.classList.remove('d-none');
            if (emptyStateElement) emptyStateElement.classList.add('d-none');

            // Restrict 1-100
            if (val < 1) input.value = 1;
            if (val > 100) input.value = 100;
        }

        function submitThreshold(input) {
            const errorElement = document.getElementById('thresholdError');
            if (input.value === "" || input.value === null) {
                errorElement.classList.remove('d-none');
                input.focus();
                return;
            }
            errorElement.classList.add('d-none');
            input.form.submit();
        }

        function setActiveTab(tabName) {
            const input = document.getElementById('activeTabInput');
            if (input) {
                input.value = tabName;
            }
        }

        // Dynamic Height Calculation for Table
        let isAdjusting = false;
        function adjustTableHeight() {
            if (isAdjusting) return;

            const tableContainer = document.getElementById('mainTableContainer');
            if (!tableContainer) return;

            const mainContent = document.querySelector('.main-content');
            if (!mainContent) return;

            if (window.innerWidth < 992) {
                tableContainer.style.removeProperty('--table-height');
                document.body.style.overflow = 'auto';
                mainContent.style.overflow = 'auto';
                return;
            }

            isAdjusting = true;

            // Temporary allow scrolling to measure natural positions
            const prevOverflow = mainContent.style.overflow;
            mainContent.style.overflow = 'auto';

            const windowHeight = window.innerHeight;
            const footer = document.querySelector('footer');
            const footerHeight = footer ? footer.offsetHeight : 60;

            // Get position relative to main-content
            const rect = tableContainer.getBoundingClientRect();
            const scrollTop = mainContent.scrollTop;
            const absoluteTop = rect.top + scrollTop;

            // Available space from natural position to bottom of screen
            // We want the height to be (Window - AbsoluteTop - Footer - Padding)
            const availableHeight = windowHeight - absoluteTop - footerHeight - 30;

            if (availableHeight > 250) {
                tableContainer.style.setProperty('--table-height', availableHeight + 'px');
                document.body.style.overflow = 'hidden';
                mainContent.style.overflow = 'hidden';
                // Reset scroll to top to ensure the dashboard fits perfectly
                mainContent.scrollTop = 0;
            } else {
                tableContainer.style.removeProperty('--table-height');
                document.body.style.overflow = 'auto';
                mainContent.style.overflow = 'auto';
            }

            isAdjusting = false;
        }

        window.addEventListener('load', adjustTableHeight);
        window.addEventListener('resize', adjustTableHeight);

        // Also trigger after tab changes
        document.querySelectorAll('[data-bs-toggle="tab"]').forEach(tab => {
            tab.addEventListener('shown.bs.tab', adjustTableHeight);
        });

        // Trigger when analysis content/filters might change layout
        const observer = new MutationObserver(adjustTableHeight);
        const analysisSection = document.getElementById('analysis-section');
        const filterSection = document.getElementById('filter-section');
        if (analysisSection) observer.observe(analysisSection, { attributes: true, childList: true });
        if (filterSection) observer.observe(filterSection, { attributes: true, childList: true });
    </script>
@endsection