@extends('layouts.admin')

@section('title', 'Rentang Harga - BPS Kalbar')

@section('content')
    <div class="fade-in-up">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
            <div>
                <h2 class="fw-bold mb-1" style="color: var(--bps-orange);">Rentang Harga</h2>
                <p class="text-muted mb-0">Visualisasi data rentang harga komoditas di Kalimantan Barat</p>
            </div>
            <div class="mt-3 mt-md-0 d-flex gap-2">
                <a href="{{ route('rh-nilai.index') }}" class="btn fw-bold shadow-sm"
                    style="background-color: #fff; color: var(--bps-orange); border: 1px solid var(--bps-orange);">
                    <i class="fas fa-edit me-1"></i> Input Nilai RH Kabupaten
                </a>
                <a href="{{ route('price-range.input') }}" class="btn text-white fw-bold shadow-sm"
                    style="background-color: var(--bps-blue);">
                    <i class="fas fa-plus-circle me-1"></i> Input Komoditas
                </a>
            </div>
        </div>

        <!-- Filters & Export -->
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
            <div class="card-body p-4">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3">
                    <form action="{{ route('price-range.index') }}" method="GET" class="row g-3 flex-grow-1 align-items-end" id="filter-form">
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted text-uppercase">Pilih Tahun</label>
                            <select name="year_id" class="form-select border-0 bg-light shadow-none" onchange="this.form.submit()">
                                @foreach($years as $yr)
                                    <option value="{{ $yr->id }}" {{ $selectedYearId == $yr->id ? 'selected' : '' }}>
                                        {{ $yr->tahun }} {{ $yr->is_active ? '(Aktif)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted text-uppercase">Pilih Kabupaten</label>
                            <select name="kabupaten_id" class="form-select border-0 bg-light shadow-none" onchange="this.form.submit()">
                                @foreach($kabupatens as $kab)
                                    <option value="{{ $kab->id }}" {{ $selectedKabupatenId == $kab->id ? 'selected' : '' }}>
                                        {{ $kab->nama_kabupaten }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </form>
                    
                    @if($selectedYearId && $selectedKabupatenId)
                        <div class="ms-md-auto">
                            <a href="{{ route('price-range.export', ['year_id' => $selectedYearId, 'kabupaten_id' => $selectedKabupatenId]) }}" 
                               class="btn btn-success fw-bold px-4 shadow-sm h-100 d-flex align-items-center">
                                <i class="fas fa-file-excel me-2"></i> Export Excel
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        @if($activeYear && $selectedKabupatenId)
            <!-- Info Batas Selisih & Legend -->
            <div class="alert alert-info border-0 shadow-sm mb-4" role="alert" style="background-color: rgba(13, 202, 240, 0.1); color: #055160;">
                <div class="d-flex align-items-start">
                    <div class="me-3 mt-1">
                        <i class="fas fa-info-circle fs-4"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h5 class="alert-heading fw-bold mb-3" style="font-size: 1rem;">Informasi & Keterangan</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <strong class="d-block mb-2 text-uppercase small opacity-75">Batas Selisih Harga</strong>
                                <div class="d-flex align-items-end mb-1">
                                    <span class="fs-4 fw-bold me-2" style="line-height: 1;">Rp {{ number_format($activeYear->batas_selisih_harga ?? 0, 0, ',', '.') }}</span>
                                </div>
                                <p class="mb-0 small" style="opacity: 0.85;">
                                    Jika selisih harga (MAX - MIN) melebihi batas ini, maka kolom <b>Alasan</b> wajib diisi.
                                </p>
                            </div>
                            <div class="col-md-6 position-relative">
                                <div class="d-none d-md-block position-absolute start-0 top-0 bottom-0 border-start border-info opacity-25" style="width: 1px;"></div>
                                <div class="ps-md-4">
                                    <strong class="d-block mb-2 text-uppercase small opacity-75">Legenda Warna</strong>
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="rounded me-2 bg-warning-light border border-warning" style="width: 18px; height: 18px; flex-shrink: 0;"></div>
                                        <span class="small" style="line-height: 1.2;">
                                            <b>Kuning:</b> Ada perubahan data (Input/Edit) dari periode sebelumnya.
                                        </span>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded me-2 bg-danger-light border border-danger" style="width: 18px; height: 18px; flex-shrink: 0;"></div>
                                        <span class="small" style="line-height: 1.2;">
                                            <b>Merah:</b> Selisih harga melebihi batas wajar (Perlu Perhatian).
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Analisis Insight Section -->
            @if(!empty($outliers))
                <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                    <div class="card-header bg-white py-3">
                        <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-search-dollar me-2 text-primary"></i>Analisis Harga Antar Kabupaten</h5>
                        <small class="text-muted">Menampilkan kabupaten dengan harga yang menyimpang jauh (>25%) dari rata-rata kabupaten.</small>
                    </div>
                    <div class="card-body">
                        <ul class="nav nav-tabs nav-fill mb-3" id="insightTabs" role="tablist">
                            @foreach($outliers as $periodName => $data)
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link {{ $loop->first ? 'active' : '' }} fw-bold" 
                                            id="tab-{{ Str::slug($periodName) }}" data-bs-toggle="tab" 
                                            data-bs-target="#content-{{ Str::slug($periodName) }}" type="button" role="tab">
                                        {{ $periodName }}
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                        <div class="tab-content" id="insightTabsContent">
                            @foreach($outliers as $periodName => $commodities)
                                <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" 
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
                                                                {{ $komoditasName }} <span class="badge bg-secondary ms-1 fw-normal">{{ $details['unit'] ?? '' }}</span>
                                                            </strong>
                                                            <div class="d-flex justify-content-between small text-muted border-bottom pb-2 mb-2">
                                                                <span>Avg Min: <b>{{ number_format($details['avg_min'] ?? 0, 0, ',', '.') }}</b></span>
                                                                <span>Avg Max: <b>{{ number_format($details['avg_max'] ?? 0, 0, ',', '.') }}</b></span>
                                                            </div>
                                                            
                                                            <div class="row small">
                                                                <!-- Below Average -->
                                                                <div class="col-6 border-end">
                                                                    <span class="text-success fw-bold d-block mb-1"><i class="fas fa-arrow-down me-1"></i>Jauh Di Bawah Rata2</span>
                                                                    @forelse($details['below'] as $item)
                                                                        <div class="mb-1">
                                                                            <span class="fw-bold">{{ $item['kab'] }}</span>
                                                                            <br>
                                                                            <span class="text-muted">Rp {{ number_format($item['val'], 0, ',', '.') }}</span>
                                                                            <span class="badge bg-success bg-opacity-10 text-success ms-1">-{{ $item['diff'] }}</span>
                                                                        </div>
                                                                    @empty
                                                                        <span class="text-muted fst-italic">-</span>
                                                                    @endforelse
                                                                </div>
                                                                
                                                                <!-- Above Average -->
                                                                <div class="col-6 ps-3">
                                                                    <span class="text-danger fw-bold d-block mb-1"><i class="fas fa-arrow-up me-1"></i>Jauh Di Atas Rata2</span>
                                                                    @forelse($details['above'] as $item)
                                                                        <div class="mb-1">
                                                                            <span class="fw-bold">{{ $item['kab'] }}</span>
                                                                            <br>
                                                                            <span class="text-muted">Rp {{ number_format($item['val'], 0, ',', '.') }}</span>
                                                                            <span class="badge bg-danger bg-opacity-10 text-danger ms-1">+{{ $item['diff'] }}</span>
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
            @endif

            <!-- Data Table -->
            <div class="card border-0 shadow-sm overflow-hidden" style="border-radius: 12px;">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0">
                        <thead class="bg-light text-center align-middle">
                            <tr>
                                <th rowspan="2" class="ps-4" style="min-width: 200px;">KOMODITAS</th>
                                <th rowspan="2" style="width: 80px;">SATUAN</th>
                                <th colspan="3" class="bg-blue-light text-blue">MASTER NILAI ({{ substr($activeYear->tahun, -2) }})</th>
                                
                                @foreach($revisions as $rev)
                                    <th colspan="3" class="bg-orange-light text-orange">{{ strtoupper($rev->label) }}</th>
                                @endforeach
                            </tr>
                            <tr>
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
                                        $colspan = 5 + ($revisions->count() * 3);
                                    @endphp
                                    <td colspan="{{ $colspan }}" class="ps-4 fw-bold text-muted small text-uppercase py-2">
                                        <i class="fas fa-folder-open me-1"></i> {{ $category->nama_kategori }}
                                    </td>
                                </tr>
                                @foreach($category->komoditas as $komo)
                                    @php
                                        $master = $masterNilai->get($komo->id);
                                    @endphp
                                    
                                    <tr>
                                        <td class="ps-4">{{ $komo->nama_komoditas }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-light text-dark border fw-normal">{{ $komo->satuan ?? 'Kg' }}</span>
                                        </td>
                                        
                                        <!-- Master Data -->
                                        @php
                                            $masterDiff = ($master && $master->max_nilai !== null && $master->min_nilai !== null) 
                                                ? ($master->max_nilai - $master->min_nilai) 
                                                : 0;
                                            $isMasterExceeded = $masterDiff > ($activeYear->batas_selisih_harga ?? 0);
                                        @endphp
                                        <td class="text-center bg-blue-faded">
                                            {{ $master && $master->min_nilai !== null ? number_format($master->min_nilai, 0, ',', '.') : '-' }}
                                        </td>
                                        <td class="text-center bg-blue-faded">
                                            {{ $master && $master->max_nilai !== null ? number_format($master->max_nilai, 0, ',', '.') : '-' }}
                                        </td>
                                        <td class="small {{ $isMasterExceeded ? 'bg-danger-light fw-bold text-dark' : 'bg-blue-faded text-muted' }}"
                                            title="{{ $isMasterExceeded ? 'Selisih harga melebihi batas (Rp ' . number_format($masterDiff, 0, ',', '.') . ')' : '' }}">
                                            {{ $master->alasan ?? '-' }}
                                        </td>

                                        @php
                                            // Initialize tracking for carry-forward logic
                                            $currentMin = $master ? $master->min_nilai : null;
                                            $currentMax = $master ? $master->max_nilai : null;
                                            $currentAlasan = $master ? $master->alasan : null;
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
                                                    $currentMin = $revData->min_edit;
                                                    $isMinEdit = true;
                                                }
                                                
                                                // Max Carry Forward
                                                if ($revData && $revData->max_edit !== null) {
                                                    $currentMax = $revData->max_edit;
                                                    $isMaxEdit = true;
                                                }

                                                // Alasan Carry Forward
                                                if ($revData && $revData->alasan !== null) {
                                                    $currentAlasan = $revData->alasan;
                                                    $isAlasanEdit = true;
                                                }

                                                // Check Difference Limit
                                                $currentDiff = ($currentMax !== null && $currentMin !== null) ? ($currentMax - $currentMin) : 0;
                                                $isExceeded = $currentDiff > ($activeYear->batas_selisih_harga ?? 0);
                                            @endphp
                                            <td class="text-center {{ $isMinEdit ? 'bg-warning-light fw-bold text-dark' : 'bg-orange-faded' }}">
                                                {{ $currentMin !== null ? number_format($currentMin, 0, ',', '.') : '-' }}
                                            </td>
                                            <td class="text-center {{ $isMaxEdit ? 'bg-warning-light fw-bold text-dark' : 'bg-orange-faded' }}">
                                                {{ $currentMax !== null ? number_format($currentMax, 0, ',', '.') : '-' }}
                                            </td>
                                            <td class="small {{ $isExceeded ? 'bg-danger-light fw-bold text-dark' : ($isAlasanEdit ? 'bg-warning-light fw-bold text-dark' : 'bg-orange-faded text-muted') }}"
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
        .bg-blue-light { background-color: rgba(0, 147, 221, 0.05); }
        .bg-blue-faded { background-color: rgba(0, 147, 221, 0.02); }
        .text-blue { color: var(--bps-blue); }
        
        .bg-orange-light { background-color: rgba(255, 140, 0, 0.05); }
        .bg-orange-faded { background-color: rgba(255, 140, 0, 0.02); }
        .bg-warning-light { background-color: rgba(255, 193, 7, 0.15) !important; }
        .bg-danger-light { background-color: rgba(220, 53, 69, 0.15) !important; }
        .text-orange { color: var(--bps-orange); }

        table th {
            font-weight: 700;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
        }

        .table-bordered > :not(caption) > * > * {
            border-width: 1px;
            border-color: #f1f5f9;
        }
    </style>
@endsection