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
                                        <td class="text-center bg-blue-faded">
                                            {{ $master && $master->min_nilai !== null ? number_format($master->min_nilai, 0, ',', '.') : '-' }}
                                        </td>
                                        <td class="text-center bg-blue-faded">
                                            {{ $master && $master->max_nilai !== null ? number_format($master->max_nilai, 0, ',', '.') : '-' }}
                                        </td>
                                        <td class="small bg-blue-faded text-muted">
                                            {{ $master->alasan ?? '-' }}
                                        </td>

                                        <!-- Revision Data -->
                                        @foreach($revisions as $rev)
                                            @php
                                                $revData = isset($revisionDetails[$rev->id]) ? $revisionDetails[$rev->id]->get($komo->id) : null;
                                            @endphp
                                            <td class="text-center bg-orange-faded">
                                                {{ $revData && $revData->min_edit !== null ? number_format($revData->min_edit, 0, ',', '.') : '-' }}
                                            </td>
                                            <td class="text-center bg-orange-faded">
                                                {{ $revData && $revData->max_edit !== null ? number_format($revData->max_edit, 0, ',', '.') : '-' }}
                                            </td>
                                            <td class="small bg-orange-faded text-muted">
                                                {{ $revData->alasan ?? '-' }}
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