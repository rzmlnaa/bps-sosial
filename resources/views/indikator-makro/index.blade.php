@extends('layouts.admin')

@section('title', 'Indikator Makro')

@push('styles')
    <style>
        :root {
            --fi-primary: #f58220;
            --fi-primary-lt: #fff4eb;
            --fi-border: #e2e8f0;
            --fi-surface: #f8fafc;
            --fi-text: #0f172a;
            --fi-muted: #64748b;
        }

        .fi-page {
            font-family: 'Inter', sans-serif;
            color: var(--fi-text);
        }

        .fi-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .fi-title {
            font-size: 1.55rem;
            font-weight: 800;
            color: var(--fi-primary);
            margin: 0;
        }

        .fi-subtitle {
            font-size: .875rem;
            color: var(--fi-muted);
            margin: .25rem 0 0;
        }

        .fi-btn-action {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            height: 38px;
            padding: 0 1rem;
            border-radius: 9px;
            font-size: .83rem;
            font-weight: 600;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all .18s;
            background: var(--fi-primary);
            color: #fff;
            box-shadow: 0 2px 8px rgba(245, 130, 32, .3);
        }

        .fi-btn-action:hover {
            background: #E9861A;
            color: #fff;
        }

        .card-custom {
            border: 1px solid var(--fi-border);
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .05);
            background: #fff;
        }

        .form-select-custom {
            background-color: var(--fi-surface);
            border: 1px solid var(--fi-border);
            border-radius: 8px;
            font-size: 0.9rem;
            color: var(--fi-text);
            padding: 0.5rem 1rem;
            transition: all 0.2s;
        }

        .form-select-custom:focus {
            border-color: var(--fi-primary);
            box-shadow: 0 0 0 3px rgba(245, 130, 32, 0.15);
            outline: none;
        }

        /* BPS Table Style */
        .table-bps-container {
            background: #fff;
            border-radius: 8px;
            padding: 1.5rem;
            border: 1px solid var(--fi-border);
        }

        .table-bps-scroll {
            max-height: 70vh;
            overflow-y: auto;
            border: 1px solid var(--fi-border);
            border-radius: 8px;
            position: relative;
        }

        .table-bps {
            border-collapse: collapse;
            width: 100%;
            border-top: 3px double #000;
            border-bottom: 3px double #000;
        }

        .table-bps th {
            border: 1px solid #bbb;
            text-align: center;
            vertical-align: middle;
            font-weight: 700;
            font-size: 0.85rem;
            padding: 10px 8px;
            background-color: #fafafa;
        }

        .table-bps td {
            border: 1px solid #ccc;
            padding: 8px 12px;
            font-size: 0.9rem;
            vertical-align: middle;
        }

        /* None only styles (2 header rows + 1 numbering row) */
        .table-bps.table-none-only thead tr:nth-child(1) th {
            position: sticky;
            top: 0;
            z-index: 1022;
            background-color: #fafafa !important;
            box-shadow: inset 0 -1px 0 #bbb;
        }
        .table-bps.table-none-only thead tr:nth-child(2) th {
            position: sticky;
            top: 41px;
            z-index: 1022;
            background-color: #fafafa !important;
            box-shadow: inset 0 -1px 0 #bbb;
        }
        .table-bps.table-none-only thead tr.col-num th {
            position: sticky;
            top: 80px;
            z-index: 1022;
            background-color: #fafafa !important;
            box-shadow: inset 0 -1.5px 0 #000;
        }

        /* Multi dim styles (3 header rows + 1 numbering row) */
        .table-bps.table-multi-dim thead tr:nth-child(1) th {
            position: sticky;
            top: 0;
            z-index: 1022;
            background-color: #fafafa !important;
            box-shadow: inset 0 -1px 0 #bbb;
        }
        .table-bps.table-multi-dim thead tr:nth-child(2) th {
            position: sticky;
            top: 41px;
            z-index: 1022;
            background-color: #fafafa !important;
            box-shadow: inset 0 -1px 0 #bbb;
        }
        .table-bps.table-multi-dim thead tr:nth-child(3) th {
            position: sticky;
            top: 82px;
            z-index: 1022;
            background-color: #fafafa !important;
            box-shadow: inset 0 -1px 0 #bbb;
        }
        .table-bps.table-multi-dim thead tr.col-num th {
            position: sticky;
            top: 121px;
            z-index: 1022;
            background-color: #fafafa !important;
            box-shadow: inset 0 -1.5px 0 #000;
        }

        /* Sticky Column (Kabupaten/Kota/Provinsi) */
        .table-bps tbody tr {
            background-color: #fff;
        }
        
        .table-bps .sticky-col {
            position: sticky;
            left: 0;
            z-index: 1020;
            background-color: inherit;
            box-shadow: 2px 0 5px rgba(0,0,0,0.05);
            border-right: 2px solid #aaa !important;
        }
        
        .table-bps thead th.sticky-col {
            z-index: 1024 !important;
            background-color: #fafafa !important;
            box-shadow: inset 0 -1px 0 #bbb, 2px 0 5px rgba(0,0,0,0.05) !important;
            border-right: 2px solid #aaa !important;
        }

        .table-bps thead tr.col-num th.sticky-col {
            box-shadow: inset 0 -1.5px 0 #000, 2px 0 5px rgba(0,0,0,0.05) !important;
        }

        .region-badge {
            cursor: pointer;
            transition: all 0.2s;
            user-select: none;
        }

        .region-badge:hover {
            opacity: 0.85;
            transform: scale(1.02);
        }
    </style>
@endpush

@section('content')
    <div class="fi-page mt-2">

        {{-- ══ HEADER ══ --}}
        <div class="fi-header fade-in-up">
            <div>
                <h1 class="fi-title">INDIKATOR MAKRO</h1>
                <p class="fi-subtitle">Visualisasi Tren & Tabel Indikator Makro Sosial Ekonomi</p>
            </div>
            @auth
                @if(auth()->user()->status === 'active' && auth()->user()->kabupaten->kode_kab === '6100')
                    <div class="fi-actions">
                        <a href="{{ route('indikator-makro.kelola') }}" class="fi-btn-action">
                            <i class="fas fa-cog"></i> Kelola Indikator
                        </a>
                        <a href="{{ route('indikator-makro.input-nilai') }}" class="fi-btn-action" style="background: #2ecc71; box-shadow: 0 2px 8px rgba(46, 204, 113, .3); margin-left: 0.5rem;">
                            <i class="fas fa-edit"></i> Input Nilai Indikator
                        </a>
                    </div>
                @endif
            @endauth
        </div>

        {{-- ══ FILTERS CARD ══ --}}
        <div class="card card-custom border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <form action="{{ route('indikator-makro.index') }}" method="GET" class="row g-3" id="filter-form">
                    {{-- Live Autocomplete Search for Indikator Makro --}}
                    <div class="col-md-12 mb-2">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label text-muted small fw-bold text-uppercase mb-0">Pilih Indikator Makro</label>
                            <button type="button" class="btn btn-sm btn-outline-warning rounded-pill px-3 py-1" 
                                id="btn-open-katalog" data-bs-toggle="modal" data-bs-target="#katalogModal" style="font-size: 0.75rem; font-weight: 600;">
                                <i class="fas fa-list me-1"></i> Lihat Katalog Indikator
                            </button>
                        </div>
                        <div class="position-relative" id="indikator-search-wrapper">
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="fas fa-search text-muted"></i>
                                </span>
                                <input type="text" id="indikator-search-input"
                                    class="form-control border-start-0 ps-1 bg-light"
                                    placeholder="Cari Indikator Makro..."
                                    value="{{ $selectedIndikator ? $selectedIndikator->nama_indikator : '' }}"
                                    autocomplete="off" required>
                                <button class="btn btn-outline-secondary border-start-0" type="button"
                                    id="btn-clear-search" title="Bersihkan Pilihan" style="{{ $selectedIndikator ? '' : 'display: none;' }}">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            <input type="hidden" name="indikator_makro_id" id="indikator-search-id"
                                value="{{ $selectedIndikator ? $selectedIndikator->id : '' }}">
                            <div id="indikator-search-results" class="dropdown-menu w-100 shadow border-0 py-1"
                                style="max-height: 250px; overflow-y: auto; display: none; position: absolute; z-index: 1050; top: 100%;">
                            </div>
                        </div>
                    </div>

                    @if($selectedIndikator)
                        {{-- Filter Tahun --}}
                        <div class="col-md-12 mb-2">
                            <label class="form-label text-muted small fw-bold text-uppercase">Filter Tahun / Periode (Tabel & Grafik)</label>
                            <div class="d-flex flex-wrap gap-3 p-2 bg-light rounded border px-3">
                                @foreach($allPeriodes as $p)
                                    <div class="form-check">
                                        <input class="form-check-input year-filter-checkbox" type="checkbox" name="tahun[]" value="{{ $p->tahun }}" id="year_{{ $p->tahun }}" {{ in_array($p->tahun, $selectedYears) ? 'checked' : '' }} onchange="this.form.submit()">
                                        <label class="form-check-label small fw-semibold" for="year_{{ $p->tahun }}">{{ $p->tahun }}</label>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Filter Dimensi / Klasifikasi untuk Tabel (Only if not none-only) --}}
                        @if(!$isNoneOnly)
                            <div class="col-md-12 mb-2">
                                <label class="form-label text-muted small fw-bold text-uppercase">Filter Dimensi / Klasifikasi (Tabel)</label>
                                <div class="d-flex flex-wrap gap-3 p-2 bg-light rounded border px-3">
                                    @foreach($indikatorDimensis as $indDim)
                                        <div class="form-check">
                                            <input class="form-check-input dimensi-filter-checkbox" type="checkbox" name="indikator_dimensi_ids[]" value="{{ $indDim->id }}" id="dim_{{ $indDim->id }}" {{ in_array($indDim->id, $selectedDimensiIds) ? 'checked' : '' }} onchange="this.form.submit()">
                                            <label class="form-check-label small fw-semibold" for="dim_{{ $indDim->id }}">
                                                {{ $indDim->dimensi->nama_dimensi ?? '-' }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <hr class="my-3 text-secondary opacity-25">

                        {{-- Grafik Mode Selector & Filters --}}
                        <div class="col-md-12">
                            <h6 class="fw-bold small text-muted text-uppercase mb-2"><i class="fas fa-chart-bar me-1 text-primary"></i> Pengaturan Filter Grafik</h6>
                        </div>

                        @if(!$isNoneOnly)
                            <div class="col-md-12 mb-2">
                                <label class="form-label text-muted small fw-bold text-uppercase">Mode Analisis Grafik</label>
                                <div class="d-flex gap-4 flex-wrap">
                                    <div class="form-check">
                                        <input class="form-check-input chart-mode-radio" type="radio" name="chart_mode" id="mode_compare_regions" value="compare_regions" checked>
                                        <label class="form-check-label small fw-bold text-dark" style="cursor: pointer;" for="mode_compare_regions">Bandingkan Wilayah (Pilih 1 Dimensi, Sumbu X: Wilayah)</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input chart-mode-radio" type="radio" name="chart_mode" id="mode_compare_dimensions" value="compare_dimensions">
                                        <label class="form-check-label small fw-bold text-dark" style="cursor: pointer;" for="mode_compare_dimensions">Bandingkan Dimensi (Pilih 1 Wilayah, Sumbu X: Dimensi)</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input chart-mode-radio" type="radio" name="chart_mode" id="mode_compare_all" value="compare_all">
                                        <label class="form-check-label small fw-bold text-dark" style="cursor: pointer;" for="mode_compare_all">Bandingkan Wilayah & Dimensi (Sumbu X: Wilayah, Datasets: Dimensi)</label>
                                    </div>
                                </div>
                            </div>

                            {{-- Single Dimensi Selector (Shown when mode is compare_regions) --}}
                            <div class="col-md-12 mb-2" id="single-dimensi-container">
                                <label class="form-label text-muted small fw-bold text-uppercase">Pilih 1 Dimensi Grafik</label>
                                <select id="chart-single-dimensi" class="form-select bg-light border-0 py-2" style="font-size: 0.9rem;">
                                    @foreach($indikatorDimensis as $indDim)
                                        <option value="{{ $indDim->id }}">{{ $indDim->dimensi->nama_dimensi ?? '-' }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Multi Dimensi Selector (Shown when mode is compare_dimensions) --}}
                            <div class="col-md-12 mb-2 d-none" id="multi-dimensi-container">
                                <label class="form-label text-muted small fw-bold text-uppercase">Pilih Dimensi Grafik (Bisa Banyak)</label>
                                <div class="d-flex flex-wrap gap-3 p-2 bg-light rounded border px-3">
                                    @foreach($indikatorDimensis as $indDim)
                                        <div class="form-check">
                                            <input class="form-check-input chart-dim-checkbox" type="checkbox" value="{{ $indDim->id }}" id="chk_chart_dim_{{ $indDim->id }}" checked>
                                            <label class="form-check-label small fw-semibold" for="chk_chart_dim_{{ $indDim->id }}">
                                                {{ $indDim->dimensi->nama_dimensi ?? '-' }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Multi Wilayah Selector (Shown when mode is compare_regions) --}}
                        <div class="col-md-12 mb-2" id="multi-region-container">
                            <div class="d-flex justify-content-between align-items-center mb-1 flex-wrap gap-2">
                                <label class="form-label text-muted small fw-bold text-uppercase mb-0">Pilih Wilayah Grafik (Bisa Banyak)</label>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-xs btn-outline-primary py-0 px-2" id="btn-chart-select-all" style="font-size: 0.75rem;">Pilih Semua</button>
                                    <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-2" id="btn-chart-deselect-all" style="font-size: 0.75rem;">Hapus Semua</button>
                                </div>
                            </div>
                            <div class="d-flex flex-wrap gap-3 p-2 bg-light rounded border px-3">
                                @foreach($kabupatens as $kab)
                                    @php
                                        $kabChart = collect($chartData)->first(fn($item) => $item['id'] === $kab->id);
                                    @endphp
                                    @if($kabChart)
                                        <div class="form-check">
                                            <input class="form-check-input region-chart-checkbox" type="checkbox" value="{{ $kab->id }}" id="chk_region_{{ $kab->id }}" {{ ($kab->kode_kab === '6100' || $kab->kode_kab === '1') ? 'checked' : '' }}>
                                            <label class="form-check-label small fw-semibold" for="chk_region_{{ $kab->id }}">
                                                {{ $kab->kode_kab === '6100' ? 'Provinsi Kalbar' : ($kab->kode_kab === '1' ? 'Indonesia' : $kab->nama_kabupaten) }}
                                            </label>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>

                        {{-- Single Wilayah Selector (Shown when mode is compare_dimensions) --}}
                        <div class="col-md-12 mb-2 d-none" id="single-region-container">
                            <label class="form-label text-muted small fw-bold text-uppercase">Pilih 1 Wilayah Grafik</label>
                            <select id="chart-single-region" class="form-select bg-light border-0 py-2" style="font-size: 0.9rem;">
                                @foreach($kabupatens as $kab)
                                    @php
                                        $kabChart = collect($chartData)->first(fn($item) => $item['id'] === $kab->id);
                                    @endphp
                                    @if($kabChart)
                                        <option value="{{ $kab->id }}" {{ $kab->kode_kab === '6100' ? 'selected' : '' }}>
                                            {{ $kab->kode_kab === '6100' ? 'Provinsi Kalimantan Barat' : ($kab->kode_kab === '1' ? 'Indonesia' : $kab->nama_kabupaten) }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                    @endif
                </form>
            </div>
        </div>

        @if($selectedIndikator)
            {{-- ══ DASHBOARD LAYOUT ══ --}}
            <div class="row g-4">
                {{-- 📈 CHART CARD --}}
                <div class="col-12">
                    <div class="card card-custom border-0 shadow-sm p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h5 class="fw-bold mb-1" style="color: var(--fi-primary)">
                                    <i class="fas fa-chart-line me-1"></i> Grafik Tren Nilai Indikator
                                </h5>
                                <p class="text-muted small mb-0">
                                    {{ $selectedIndikator->nama_indikator }}
                                    @if(!empty($selectedYears))
                                        Tahun {{ count($selectedYears) === 1 ? $selectedYears[0] : min($selectedYears) . ' - ' . max($selectedYears) }}
                                    @endif
                                </p>
                            </div>
                        </div>

                        {{-- Chart canvas wrapper --}}
                        <div style="position: relative; height: 600px;" class="mb-2">
                            <canvas id="makroChart"></canvas>
                        </div>
                    </div>
                </div>

                {{-- 📊 COMPARATIVE BPS TABLE CARD --}}
                <div class="col-12">
                    <div class="table-bps-container shadow-sm mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h4 class="fw-bold mb-1 text-dark" style="font-size: 1.1rem;">
                                    Tabel {{ $selectedIndikator->nama_indikator }}
                                    @if(!empty($selectedYears))
                                        Tahun {{ count($selectedYears) === 1 ? $selectedYears[0] : min($selectedYears) . ' - ' . max($selectedYears) }}
                                    @endif
                                </h4>
                                <p class="text-muted small mb-0">
                                    Satuan: {{ $selectedIndikator->satuan ?? '-' }}
                                </p>
                            </div>
                        </div>

                        <div class="table-bps-scroll">
                            <table class="table-bps align-middle {{ $isNoneOnly ? 'table-none-only' : 'table-multi-dim' }}">
                                <thead>
                                    @if($isNoneOnly)
                                        <tr>
                                            <th class="sticky-col" rowspan="2">Kabupaten/Kota/Provinsi</th>
                                            <th colspan="{{ $periodes->count() }}">Tahun / Periode</th>
                                        </tr>
                                        <tr>
                                            @foreach($periodes as $p)
                                                <th style="min-width: 95px;">{{ $p->tahun }}</th>
                                            @endforeach
                                        </tr>
                                        <tr class="col-num">
                                            <th class="sticky-col">(1)</th>
                                            @foreach($periodes as $idx => $p)
                                                <th>({{ $idx + 2 }})</th>
                                            @endforeach
                                        </tr>
                                    @else
                                        <tr>
                                            <th class="sticky-col" rowspan="3">Kabupaten/Kota/Provinsi</th>
                                            <th colspan="{{ $periodes->count() * $selectedDimensis->count() }}">Tahun / Periode</th>
                                        </tr>
                                        <tr>
                                            @foreach($periodes as $p)
                                                <th colspan="{{ $selectedDimensis->count() }}">{{ $p->tahun }}</th>
                                            @endforeach
                                        </tr>
                                        <tr>
                                            @foreach($periodes as $p)
                                                @foreach($selectedDimensis as $indDim)
                                                    <th style="min-width: 95px;">{{ $indDim->dimensi->nama_dimensi ?? '-' }}</th>
                                                @endforeach
                                            @endforeach
                                        </tr>
                                        <tr class="col-num">
                                            <th class="sticky-col">(1)</th>
                                            @for($i = 0; $i < $periodes->count() * $selectedDimensis->count(); $i++)
                                                <th>({{ $i + 2 }})</th>
                                            @endfor
                                        </tr>
                                    @endif
                                </thead>
                                <tbody>
                                    @forelse($kabupatens as $kab)
                                        @php
                                            $isProvince = $kab->kode_kab === '6100';
                                            $isIndonesia = ($kab->kode_kab === '1' || strtolower($kab->nama_kabupaten) === 'indonesia');
                                            $rowStyle = ($isProvince || $isIndonesia) ? 'font-weight: bold; background-color: #f9f9f9;' : '';
                                        @endphp
                                        <tr style="{{ $rowStyle }}">
                                            <td class="sticky-col">
                                                @if($isIndonesia)
                                                    <strong>INDONESIA</strong>
                                                @elseif($isProvince)
                                                    <strong>[{{ $kab->kode_kab }}] Provinsi Kalimantan Barat</strong>
                                                @else
                                                    [{{ $kab->kode_kab }}] {{ $kab->nama_kabupaten }}
                                                @endif
                                            </td>
                                            @foreach($periodes as $p)
                                                @foreach($selectedDimensis as $indDim)
                                                    @php
                                                        $val = $values[$kab->id][$p->id][$indDim->id] ?? null;
                                                        if (is_numeric($val)) {
                                                            $floatVal = (float)$val;
                                                            if (floor($floatVal) == $floatVal) {
                                                                $valFormatted = number_format($floatVal, 0, '.', ',');
                                                            } else {
                                                                $strVal = (string)$floatVal;
                                                                $dotPos = strpos($strVal, '.');
                                                                $decimals = 2;
                                                                if ($dotPos !== false) {
                                                                    $decimals = strlen($strVal) - $dotPos - 1;
                                                                }
                                                                $valFormatted = number_format($floatVal, $decimals, '.', ',');
                                                            }
                                                        } else {
                                                            $valFormatted = '-';
                                                        }
                                                    @endphp
                                                    <td class="text-end fw-medium">
                                                        {{ $valFormatted }}
                                                    </td>
                                                @endforeach
                                            @endforeach
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="{{ ($periodes->count() * $selectedDimensis->count()) + 1 }}" class="text-center py-4 text-muted">
                                                Belum ada data wilayah tersedia.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @else
            {{-- Empty State --}}
            <div class="card card-custom border-0 shadow-sm py-5 px-4 text-center">
                <div class="mb-3"><i class="fas fa-chart-area fa-3x text-muted"></i></div>
                <h5 class="fw-bold">Belum Ada Indikator Aktif</h5>
                <p class="text-muted small">Silakan tambahkan atau aktifkan Indikator Makro terlebih dahulu di halaman kelola.</p>
            </div>
        @endif

        {{-- ══ MODAL KATALOG INDIKATOR ══ --}}
        <div class="modal fade" id="katalogModal" tabindex="-1" aria-labelledby="katalogModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content border-0 shadow" style="border-radius: 16px;">
                    <div class="modal-header border-bottom-0 pb-0">
                        <div>
                            <h5 class="modal-title fw-bold text-dark" id="katalogModalLabel" style="color: var(--fi-primary) !important;">
                                <i class="fas fa-list-ul me-2"></i> Katalog Indikator Makro
                            </h5>
                            <p class="text-muted small mb-0">Silakan pilih indikator makro berdasarkan bidang di bawah ini</p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4 pt-3">
                        <div id="katalog-loading" class="text-center py-5">
                            <div class="spinner-border text-warning" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="text-muted small mt-2">Memuat Katalog Indikator...</p>
                        </div>
                        <div id="katalog-content" class="accordion" style="display: none;">
                            {{-- Accordion dynamically populated via AJAX --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // ════════ 1. LIVE SEARCH AUTOCOMPLETE FOR INDIKATOR MAKRO ════════
    const searchInput = document.getElementById('indikator-search-input');
    const searchId = document.getElementById('indikator-search-id');
    const searchResults = document.getElementById('indikator-search-results');
    const btnClearSearch = document.getElementById('btn-clear-search');
    let lastSelectedName = '{{ $selectedIndikator ? $selectedIndikator->nama_indikator : "" }}';
    let lastSelectedId = '{{ $selectedIndikator ? $selectedIndikator->id : "" }}';
    let debounceTimer;

    const performSearch = (query) => {
        fetch('/indikator-makro/search?q=' + encodeURIComponent(query))
            .then(response => response.json())
            .then(data => {
                searchResults.innerHTML = '';
                if (data.length > 0) {
                    data.forEach(item => {
                        const btn = document.createElement('button');
                        btn.type = 'button';
                        btn.className = 'dropdown-item d-flex justify-content-between align-items-center py-2';
                        btn.innerHTML = `<span>${item.nama_indikator}</span>`;
                        btn.addEventListener('click', function () {
                            searchInput.value = item.nama_indikator;
                            searchId.value = item.id;
                            searchResults.style.display = 'none';
                            lastSelectedName = item.nama_indikator;
                            lastSelectedId = item.id;
                            btnClearSearch.style.display = 'inline-block';
                            
                            // Redirect to load selected indicator
                            const url = new URL(window.location.href);
                            url.searchParams.set('indikator_makro_id', item.id);
                            url.searchParams.delete('indikator_dimensi_ids[]');
                            url.searchParams.delete('tahun[]');
                            window.location.href = url.toString();
                        });
                        searchResults.appendChild(btn);
                    });
                    searchResults.style.display = 'block';
                } else {
                    searchResults.innerHTML = '<div class="dropdown-item text-muted text-center py-2">Tidak ada indikator yang cocok</div>';
                    searchResults.style.display = 'block';
                }
            });
    };

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            searchId.value = ''; // clear ID on input to force choosing from suggestion
            const query = this.value;
            if (query.trim() === '') {
                btnClearSearch.style.display = 'none';
            } else {
                btnClearSearch.style.display = 'inline-block';
            }
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                performSearch(query);
            }, 300);
        });

        searchInput.addEventListener('focus', function () {
            performSearch(this.value);
        });

        document.addEventListener('click', function (e) {
            const wrapper = document.getElementById('indikator-search-wrapper');
            if (wrapper && !wrapper.contains(e.target)) {
                searchResults.style.display = 'none';
                if (!searchId.value) {
                    searchInput.value = lastSelectedName;
                    searchId.value = lastSelectedId;
                    if (lastSelectedName) {
                        btnClearSearch.style.display = 'inline-block';
                    }
                }
            }
        });
    }

    if (btnClearSearch) {
        btnClearSearch.addEventListener('click', function () {
            searchInput.value = '';
            searchId.value = '';
            lastSelectedName = '';
            lastSelectedId = '';
            btnClearSearch.style.display = 'none';
            // Do NOT reload the page, just let user search again
        });
    }

    // ════════ 2. CHART GENERATION & REGION INTERACTIVITY ════════
    @if($selectedIndikator && $periodes->isNotEmpty())
        const periodes = {!! json_encode($periodes->values()) !!};
        const selectedDimensis = {!! json_encode($selectedDimensis->values()) !!};
        const kabupatens = {!! json_encode($kabupatens) !!};
        const values = {!! json_encode($values) !!};
        const isNoneOnly = {{ $isNoneOnly ? 'true' : 'false' }};

        // Beautiful curated BPS palette colors
        const colors = [
            '#0093dd', // BPS Blue
            '#f58220', // BPS Orange
            '#2ecc71', // Green
            '#9b59b6', // Purple
            '#e74c3c', // Red
            '#1abc9c', // Teal
            '#f1c40f', // Yellow
            '#34495e', // Dark Gray
            '#e67e22', // Dark Orange
            '#2980b9', // Dark Blue
            '#27ae60', // Dark Green
            '#8e44ad', // Dark Purple
            '#c0392b'  // Dark Red
        ];

        // Initialize Chart.js
        const ctx = document.getElementById('makroChart').getContext('2d');
        const chart = new Chart(ctx, {
            type: 'bar', // Grouped bar chart comparing regions side-by-side
            data: {
                labels: [],
                datasets: []
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 20,
                            font: { family: 'Inter', size: 11, weight: 'bold' }
                        }
                    },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        padding: 12,
                        cornerRadius: 8,
                        callbacks: {
                            label: function (context) {
                                return context.dataset.label + ': ' + new Intl.NumberFormat('id-ID').format(context.parsed.y);
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        grid: { color: 'rgba(0, 0, 0, 0.05)' },
                        ticks: {
                            font: { family: 'Inter', size: 11 },
                            callback: function(value) {
                                return new Intl.NumberFormat('id-ID').format(value);
                            }
                        }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { family: 'Inter', size: 11, weight: 'bold' } }
                    }
                }
            }
        });

        // ════════ 3. CHART INTERACTION & MODE HANDLERS ════════
        const regionCheckboxes = document.querySelectorAll('.region-chart-checkbox');
        const chartModeRadios = document.querySelectorAll('.chart-mode-radio');
        const multiDimContainer = document.getElementById('multi-dimensi-container');
        const singleDimContainer = document.getElementById('single-dimensi-container');
        const multiRegionContainer = document.getElementById('multi-region-container');
        const singleRegionContainer = document.getElementById('single-region-container');
        const chartSingleDim = document.getElementById('chart-single-dimensi');
        const chartSingleRegion = document.getElementById('chart-single-region');
        const chartDimCheckboxes = document.querySelectorAll('.chart-dim-checkbox');

        const updateChartData = () => {
            const chartModeInput = document.querySelector('input[name="chart_mode"]:checked');
            const chartMode = chartModeInput ? chartModeInput.value : 'compare_regions';

            if (chartMode === 'compare_regions' || isNoneOnly) {
                // Sumbu X = Wilayah, Datasets = Tahun
                const activeRegionIds = Array.from(regionCheckboxes)
                    .filter(chk => chk.checked)
                    .map(chk => parseInt(chk.value));

                const activeKabupatens = kabupatens.filter(kab => activeRegionIds.includes(kab.id));

                // 1. Update X-axis Labels (Regions)
                chart.data.labels = activeKabupatens.map(kab => {
                    const name = kab.kode_kab === '6100' ? 'Provinsi Kalbar' : (kab.kode_kab === '1' ? 'Indonesia' : kab.nama_kabupaten);
                    if (kab.kode_kab === '1') {
                        return name;
                    }
                    return `[${kab.kode_kab}] ${name}`;
                });

                // 2. Rebuild Datasets: One dataset per Year for the selected single Dimension
                const singleDimId = chartSingleDim ? parseInt(chartSingleDim.value) : (selectedDimensis[0]?.id || null);

                const newDatasets = [];
                let colorIndex = 0;

                periodes.forEach(p => {
                    const color = colors[colorIndex % colors.length];
                    colorIndex++;

                    const labelName = p.tahun;
                    const dataPoints = activeKabupatens.map(kab => {
                        const val = values[kab.id]?.[p.id]?.[singleDimId] ?? null;
                        return val !== null ? parseFloat(val) : null;
                    });

                    newDatasets.push({
                        label: labelName,
                        data: dataPoints,
                        backgroundColor: color,
                        borderColor: color,
                        borderWidth: 1,
                        borderRadius: 4
                    });
                });

                chart.data.datasets = newDatasets;
            } else if (chartMode === 'compare_dimensions') {
                // Sumbu X = Dimensi, Datasets = Tahun
                const singleRegionId = chartSingleRegion ? parseInt(chartSingleRegion.value) : kabupatens[0]?.id;

                const activeDimIds = Array.from(chartDimCheckboxes)
                    .filter(chk => chk.checked)
                    .map(chk => parseInt(chk.value));
                const activeDimensis = selectedDimensis.filter(d => activeDimIds.includes(d.id));

                // 1. Update X-axis Labels (Dimensions)
                chart.data.labels = activeDimensis.map(d => d.dimensi?.nama_dimensi ?? '-');

                // 2. Rebuild Datasets: One dataset per Year for the selected single Region
                const newDatasets = [];
                let colorIndex = 0;

                periodes.forEach(p => {
                    const color = colors[colorIndex % colors.length];
                    colorIndex++;

                    const labelName = p.tahun;
                    const dataPoints = activeDimensis.map(d => {
                        const val = values[singleRegionId]?.[p.id]?.[d.id] ?? null;
                        return val !== null ? parseFloat(val) : null;
                    });

                    newDatasets.push({
                        label: labelName,
                        data: dataPoints,
                        backgroundColor: color,
                        borderColor: color,
                        borderWidth: 1,
                        borderRadius: 4
                    });
                });

                chart.data.datasets = newDatasets;
            } else if (chartMode === 'compare_all') {
                // Sumbu X = Wilayah, Datasets = Dimensi (Tahun)
                const activeRegionIds = Array.from(regionCheckboxes)
                    .filter(chk => chk.checked)
                    .map(chk => parseInt(chk.value));

                const activeKabupatens = kabupatens.filter(kab => activeRegionIds.includes(kab.id));

                // 1. Update X-axis Labels (Regions)
                chart.data.labels = activeKabupatens.map(kab => {
                    const name = kab.kode_kab === '6100' ? 'Provinsi Kalbar' : (kab.kode_kab === '1' ? 'Indonesia' : kab.nama_kabupaten);
                    if (kab.kode_kab === '1') {
                        return name;
                    }
                    return `[${kab.kode_kab}] ${name}`;
                });

                // 2. Rebuild Datasets: One dataset per Dimensi (Tahun)
                const activeDimIds = Array.from(chartDimCheckboxes)
                    .filter(chk => chk.checked)
                    .map(chk => parseInt(chk.value));
                const activeDimensis = selectedDimensis.filter(d => activeDimIds.includes(d.id));

                const newDatasets = [];
                let colorIndex = 0;

                activeDimensis.forEach(d => {
                    periodes.forEach(p => {
                        const color = colors[colorIndex % colors.length];
                        colorIndex++;

                        const labelName = periodes.length > 1 
                            ? `${d.dimensi?.nama_dimensi ?? '-'} (${p.tahun})`
                            : (d.dimensi?.nama_dimensi ?? '-');

                        const dataPoints = activeKabupatens.map(kab => {
                            const val = values[kab.id]?.[p.id]?.[d.id] ?? null;
                            return val !== null ? parseFloat(val) : null;
                        });

                        newDatasets.push({
                            label: labelName,
                            data: dataPoints,
                            backgroundColor: color,
                            borderColor: color,
                            borderWidth: 1,
                            borderRadius: 4
                        });
                    });
                });

                chart.data.datasets = newDatasets;
            }
            chart.update();
        };

        // Event listeners
        if (chartModeRadios.length > 0) {
            chartModeRadios.forEach(radio => {
                radio.addEventListener('change', function () {
                    if (this.value === 'compare_regions') {
                        if (multiDimContainer) multiDimContainer.classList.add('d-none');
                        if (singleRegionContainer) singleRegionContainer.classList.add('d-none');
                        if (singleDimContainer) singleDimContainer.classList.remove('d-none');
                        if (multiRegionContainer) multiRegionContainer.classList.remove('d-none');
                    } else if (this.value === 'compare_dimensions') {
                        if (singleDimContainer) singleDimContainer.classList.add('d-none');
                        if (multiRegionContainer) multiRegionContainer.classList.add('d-none');
                        if (multiDimContainer) multiDimContainer.classList.remove('d-none');
                        if (singleRegionContainer) singleRegionContainer.classList.remove('d-none');
                    } else if (this.value === 'compare_all') {
                        if (singleDimContainer) singleDimContainer.classList.add('d-none');
                        if (singleRegionContainer) singleRegionContainer.classList.add('d-none');
                        if (multiDimContainer) multiDimContainer.classList.remove('d-none');
                        if (multiRegionContainer) multiRegionContainer.classList.remove('d-none');
                    }
                    updateChartData();
                });
            });
        }

        if (chartSingleDim) {
            chartSingleDim.addEventListener('change', updateChartData);
        }

        if (chartSingleRegion) {
            chartSingleRegion.addEventListener('change', updateChartData);
        }

        regionCheckboxes.forEach(chk => {
            chk.addEventListener('change', updateChartData);
        });

        chartDimCheckboxes.forEach(chk => {
            chk.addEventListener('change', updateChartData);
        });

        // Select All regions button
        const btnSelectAll = document.getElementById('btn-chart-select-all');
        if (btnSelectAll) {
            btnSelectAll.addEventListener('click', function () {
                regionCheckboxes.forEach(chk => {
                    chk.checked = true;
                });
                updateChartData();
            });
        }

        // Deselect All regions button
        const btnDeselectAll = document.getElementById('btn-chart-deselect-all');
        if (btnDeselectAll) {
            btnDeselectAll.addEventListener('click', function () {
                regionCheckboxes.forEach(chk => {
                    chk.checked = false;
                });
                updateChartData();
            });
        }

        // Run initial configuration
        updateChartData();
    @endif

    // ════════ 1b. KATALOG INDIKATOR ON-DEMAND LOAD ════════
    const katalogModal = document.getElementById('katalogModal');
    let isKatalogLoaded = false;

    if (katalogModal) {
        katalogModal.addEventListener('show.bs.modal', function () {
            if (isKatalogLoaded) return; // Cache: do not query database if already loaded once

            const loading = document.getElementById('katalog-loading');
            const content = document.getElementById('katalog-content');

            loading.style.display = 'block';
            content.style.display = 'none';

            fetch('/indikator-makro/katalog')
                .then(response => response.json())
                .then(data => {
                    content.innerHTML = '';
                    if (data.length > 0) {
                        data.forEach((bidang, index) => {
                            const isFirst = index === 0;
                            const accordionItem = document.createElement('div');
                            accordionItem.className = 'accordion-item border shadow-sm mb-3';
                            accordionItem.style.borderRadius = '12px';
                            accordionItem.style.overflow = 'hidden';

                            // Filter macro indicators for this bidang
                            const indicators = bidang.indikator_makros || [];
                            
                            let indicatorsListHtml = '';
                            if (indicators.length > 0) {
                                indicators.forEach(item => {
                                    indicatorsListHtml += `
                                        <a href="?indikator_makro_id=${item.id}" class="list-group-item list-group-item-action border-0 py-2 d-flex align-items-center justify-content-between text-decoration-none">
                                            <span class="small fw-semibold text-dark"><i class="fas fa-file-alt text-muted me-2"></i>${item.nama_indikator}</span>
                                            <i class="fas fa-chevron-right text-muted small"></i>
                                        </a>
                                    `;
                                });
                            } else {
                                indicatorsListHtml = '<div class="list-group-item border-0 text-muted small py-2">Belum ada indikator makro di bidang ini.</div>';
                            }

                            accordionItem.innerHTML = `
                                <h2 class="accordion-header" id="heading_bidang_${bidang.id}">
                                    <button class="accordion-button fw-bold text-dark ${isFirst ? '' : 'collapsed'}" type="button" data-bs-toggle="collapse" 
                                        data-bs-target="#collapse_bidang_${bidang.id}" aria-expanded="${isFirst ? 'true' : 'false'}" aria-controls="collapse_bidang_${bidang.id}"
                                        style="background-color: var(--fi-surface); font-size: 0.9rem;">
                                        <i class="fas fa-folder text-warning me-2"></i>${bidang.nama_bidang}
                                        <span class="badge bg-secondary ms-2 small" style="font-size: 0.75rem;">${indicators.length}</span>
                                    </button>
                                </h2>
                                <div id="collapse_bidang_${bidang.id}" class="accordion-collapse collapse ${isFirst ? 'show' : ''}" aria-labelledby="heading_bidang_${bidang.id}" data-bs-parent="#katalog-content">
                                    <div class="accordion-body p-0">
                                        <div class="list-group list-group-flush">
                                            ${indicatorsListHtml}
                                        </div>
                                    </div>
                                </div>
                            `;
                            content.appendChild(accordionItem);
                        });
                        loading.style.display = 'none';
                        content.style.display = 'block';
                        isKatalogLoaded = true; // Set loaded cache to true
                    } else {
                        content.innerHTML = '<div class="text-center py-4 text-muted small">Belum ada bidang atau indikator terdaftar.</div>';
                        loading.style.display = 'none';
                        content.style.display = 'block';
                    }
                })
                .catch(err => {
                    content.innerHTML = '<div class="text-center py-4 text-danger small">Gagal memuat katalog. Silakan coba lagi.</div>';
                    loading.style.display = 'none';
                    content.style.display = 'block';
                });
        });
    }
});
</script>
@endpush