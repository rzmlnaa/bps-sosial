@extends('layouts.admin')

@section('title', 'Input Nilai Indikator Makro')

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

        /* Official BPS Table Style */
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
            padding: 6px 12px;
            font-size: 0.9rem;
            vertical-align: middle;
        }

        .table-bps tr.col-num th {
            font-weight: normal;
            font-style: italic;
            font-size: 0.75rem;
            background-color: #fafafa;
            border-bottom: 1.5px solid #000;
            padding: 3px;
        }

        /* Sticky headers for scrollable container */
        .table-bps thead tr:nth-child(1) th {
            position: sticky;
            top: 0;
            z-index: 1022;
            background-color: #fafafa !important;
            box-shadow: inset 0 -1px 0 #bbb;
        }

        .table-bps thead tr:nth-child(2) th {
            position: sticky;
            top: 41px;
            /* Height of the first header row */
            z-index: 1022;
            background-color: #fafafa !important;
            box-shadow: inset 0 -1px 0 #bbb;
        }

        .table-bps:not(.table-none-only) thead tr.col-num th {
            position: sticky;
            top: 80px;
            /* Height of first + second header rows */
            z-index: 1022;
            background-color: #fafafa !important;
            box-shadow: inset 0 -1.5px 0 #000;
        }

        .table-bps.table-none-only thead tr.col-num th {
            position: sticky;
            top: 41px;
            z-index: 1022;
            background-color: #fafafa !important;
            box-shadow: inset 0 -1.5px 0 #000;
        }

        .bps-input-cell {
            width: 100%;
            border: 1px solid var(--fi-border);
            border-radius: 6px;
            background-color: var(--fi-surface);
            text-align: right;
            padding: 6px 12px;
            font-size: 0.9rem;
            font-family: inherit;
            transition: all 0.2s;
        }

        .bps-input-cell:focus {
            background-color: #fff;
            border-color: var(--fi-primary);
            box-shadow: 0 0 0 3px rgba(245, 130, 32, 0.15);
            outline: none;
        }

        .bps-input-cell::placeholder {
            color: #bbb;
            text-align: right;
        }

        .btn-primary-custom {
            background-color: var(--fi-primary);
            border-color: var(--fi-primary);
            color: #fff;
            font-weight: 600;
            padding: 0.5rem 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(245, 130, 32, .3);
            transition: all 0.2s;
        }

        .btn-primary-custom:hover {
            background-color: #E9861A;
            color: #fff;
        }

        .table-title-bps {
            font-family: 'Inter', sans-serif;
            font-weight: 700;
            font-size: 1.1rem;
            color: #000;
            margin-bottom: 0.25rem;
        }

        .table-unit-bps {
            font-family: 'Inter', sans-serif;
            font-size: 0.9rem;
            color: #333;
            margin-bottom: 1rem;
            font-style: italic;
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

        .table-bps:not(.table-none-only) thead tr.col-num th.sticky-col {
            box-shadow: inset 0 -1.5px 0 #000, 2px 0 5px rgba(0,0,0,0.05) !important;
        }

        .table-bps.table-none-only thead tr.col-num th.sticky-col {
            box-shadow: inset 0 -1.5px 0 #000, 2px 0 5px rgba(0,0,0,0.05) !important;
        }

        /* Select2 Styling Matching Bootstrap bg-light */
        .select2-container--default .select2-selection--single {
            background-color: #f8f9fa !important; /* bg-light */
            border: 0 !important; /* border-0 */
            border-radius: 0 8px 8px 0 !important;
            height: 38px !important;
            padding: 0.25rem 0.5rem;
            box-shadow: none !important; /* shadow-none */
            outline: none !important;
        }
        .select2-container--default.select2-container--focus .select2-selection--single,
        .select2-container--default.select2-container--open .select2-selection--single {
            background-color: #f8f9fa !important;
            border: 0 !important;
            box-shadow: none !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: var(--fi-text) !important;
            line-height: 28px !important;
            font-size: 0.9rem !important;
            padding-left: 0.2rem !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px !important;
            right: 8px !important;
        }
        /* Fix Select2 inside Input Group */
        .input-group > .select2-container--default {
            flex: 1 1 auto;
            width: 1% !important;
        }
    </style>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush

@section('content')
    <div class="fi-page mt-2">

        {{-- HEADER --}}
        <div class="fi-header fade-in-up">
            <div>
                <h1 class="fi-title">INPUT NILAI INDIKATOR MAKRO</h1>
                <p class="fi-subtitle">Input nilai indikator makro menggunakan format tabel grid BPS</p>
            </div>
            <div>
                <a href="{{ route('indikator-makro.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                    <i class="fas fa-arrow-left me-2"></i>Kembali
                </a>
            </div>
        </div>

        {{-- ALERTS --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4 rounded-3" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-check-circle me-2 fs-5 text-success"></i>
                    <div>
                        {{ session('success') }}
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- FILTERS CARD --}}
        <div class="card card-custom border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <form action="{{ route('indikator-makro.input-nilai') }}" method="GET" class="row g-3 align-items-end"
                    id="filter-form">
                    <div class="col-md-6">
                        <label class="form-label text-muted small fw-bold text-uppercase">Tahun Periode</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-0"><i
                                    class="fas fa-calendar-alt text-muted"></i></span>
                            <select name="periode_indikator_id"
                                class="form-select form-select-custom border-0 shadow-none bg-light"
                                onchange="this.form.submit()">
                                @foreach($periodeIndikators as $periode)
                                    <option value="{{ $periode->id }}" {{ $selectedPeriodeId == $periode->id ? 'selected' : '' }}>
                                        {{ $periode->tahun }} {{ $periode->is_active ? '(Aktif)' : '(Non-aktif)' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label text-muted small fw-bold text-uppercase d-flex align-items-center gap-2">
                            Indikator Makro
                            @if($selectedIndikator)
                                <span class="badge bg-secondary opacity-75 fw-normal text-capitalize" style="text-transform: none;"><i class="fas fa-tag me-1"></i> Bidang: {{ $selectedIndikator->bidang->nama_bidang ?? 'Tanpa Bidang' }}</span>
                            @endif
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-0"><i
                                    class="fas fa-chart-line text-muted"></i></span>
                            <select name="indikator_makro_id"
                                class="form-select form-select-custom border-0 shadow-none bg-light select2-makro"
                                onchange="this.form.submit()">
                                @foreach($indikatorMakros as $makro)
                                    <option value="{{ $makro->id }}" {{ $selectedIndikatorMakroId == $makro->id ? 'selected' : '' }}>
                                        {{ $makro->nama_indikator }} {{ $makro->is_active ? '' : '(Non-aktif oleh Admin)' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- BPS MATRIX TABLE --}}
        <div class="table-bps-container shadow-sm" id="tabel">
            @if($selectedPeriode && !$selectedPeriode->is_active)
                <div class="alert alert-danger border-0 shadow-sm mb-4 rounded-3 d-flex align-items-center">
                    <i class="fas fa-exclamation-triangle me-3 fs-4 text-danger"></i>
                    <div>
                        <strong>Periode Dinonaktifkan:</strong> Tahun Periode {{ $selectedPeriode->tahun }} telah dinonaktifkan oleh Admin. Anda tidak dapat menginput atau mengubah nilai pada tabel di bawah ini.
                    </div>
                </div>
            @elseif($selectedIndikator && !$selectedIndikator->is_active)
                <div class="alert alert-danger border-0 shadow-sm mb-4 rounded-3 d-flex align-items-center">
                    <i class="fas fa-exclamation-triangle me-3 fs-4 text-danger"></i>
                    <div>
                        <strong>Indikator Dinonaktifkan:</strong> Indikator makro ini telah dinonaktifkan oleh Admin. Anda tidak dapat menginput atau mengubah nilai pada tabel di bawah ini.
                    </div>
                </div>
            @endif

            <form action="{{ route('indikator-makro.store-nilai') }}" method="POST" id="nilai-form">
                @csrf
                <input type="hidden" name="periode_indikator_id" value="{{ $selectedPeriodeId }}">
                <input type="hidden" name="indikator_makro_id" value="{{ $selectedIndikatorMakroId }}">

                {{-- Table Meta/Header Title --}}
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h4 class="table-title-bps mb-1">
                            Tabel {{ $selectedIndikator ? $selectedIndikator->nama_indikator : 'Indikator Makro' }} Tahun {{ $selectedPeriode ? $selectedPeriode->tahun : '' }}
                        </h4>
                        
                    </div>
                   
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        
                        <div class="table-unit-bps mb-0">
                            Satuan: {{ $selectedIndikator->satuan ?? '-' }}
                        </div>
                    </div>
                    @if($indikatorDimensis->isNotEmpty())
                        <div class="d-flex gap-2">
                            @if($selectedPeriode && $selectedPeriode->is_active && $selectedIndikator && $selectedIndikator->is_active)
                                <button type="button" class="btn btn-outline-success rounded-pill px-3" data-bs-toggle="collapse" data-bs-target="#collapsePasteExcel">
                                    <i class="fas fa-file-excel me-1"></i> Paste Excel
                                </button>
                                <button type="submit" class="btn btn-primary-custom">
                                    <i class="fas fa-save me-1"></i> Simpan
                                </button>
                            @else
                                <span class="badge bg-danger p-2 px-3 fs-7 d-flex align-items-center rounded-pill">
                                    <i class="fas fa-ban me-1"></i> Dinonaktifkan oleh Admin
                                </span>
                            @endif
                        </div>
                    @else
                    <a href="/indikator-makro/kelola?tab=indikator-dimensi&filter_makro_id={{ $selectedIndikatorMakroId }}&from=input-nilai&periode_indikator_id={{ $selectedPeriodeId }}&indikator_makro_id={{ $selectedIndikatorMakroId }}" class="btn btn-outline-warning rounded-pill px-3">
                        <i class="fas fa-cog me-1 fa-spin"></i> Atur Dimensi
                    </a>
                    @endif
                </div>
 
                {{-- Copy Paste Excel Section --}}
                @if($indikatorDimensis->isNotEmpty())
                    <div class="collapse mb-3" id="collapsePasteExcel">
                        <div class="card card-body bg-light border-0 rounded-3">
                            <h6 class="fw-bold mb-2 text-success"><i class="fas fa-file-excel me-1"></i> Paste Excel Data Matrix</h6>
                            <p class="text-muted small mb-2">
                                Copy blok data angka dari Excel (sesuai urutan baris kabupaten dan kolom dimensi di bawah), lalu paste di kotak di bawah ini dan klik tombol untuk mengisi tabel otomatis. Nilai kosong atau tanda (-) akan otomatis dikosongkan.
                            </p>

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-2">Pilih Kolom Dimensi untuk Diisi:</label>
                                <div class="d-flex flex-wrap gap-2 p-2 bg-white border rounded-3">
                                    @foreach($indikatorDimensis as $indDim)
                                        <div class="form-check form-check-inline mb-0 py-1">
                                            <input class="form-check-input check-paste-dimensi" type="checkbox" 
                                                id="chk-paste-{{ $indDim->id }}" 
                                                value="{{ $indDim->id }}" 
                                                {{ $indDim->is_active ? 'checked' : 'disabled' }}>
                                            <label class="form-check-label small {{ !$indDim->is_active ? 'text-decoration-line-through text-muted' : '' }}" 
                                                for="chk-paste-{{ $indDim->id }}">
                                                {{ $indDim->dimensi->nama_dimensi ?? '-' }}
                                                @if(!$indDim->is_active)
                                                    <span class="text-danger small">(Non-aktif)</span>
                                                @endif
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <textarea id="excel-paste-area" class="form-control mb-3" rows="5" placeholder="Paste data Excel disini... (Contoh: 115.08	82.08	98.96)"></textarea>
                            <div class="text-end">
                                <button type="button" id="btn-parse-excel" class="btn btn-sm btn-success rounded-pill px-3">
                                    <i class="fas fa-check me-1"></i> Terapkan Nilai ke Tabel
                                </button>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="table-bps-scroll">
                    @php
                        $dimColCount = $indikatorDimensis->count();
                        $colspan = $dimColCount > 0 ? $dimColCount : 1;
                        $isNoneOnly = ($dimColCount === 1 && strtolower(trim($indikatorDimensis->first()->dimensi->nama_dimensi ?? '')) === 'none');
                    @endphp
                    <table class="table-bps align-middle {{ $isNoneOnly ? 'table-none-only' : '' }}">
                        <thead>
                            @if($isNoneOnly)
                                <tr>
                                    <th class="sticky-col">Kabupaten/Kota/Provinsi</th>
                                    <th style="min-width: 150px;">
                                        {{ $selectedPeriode ? $selectedPeriode->tahun : '-' }}
                                        @if($indikatorDimensis->isNotEmpty() && !$indikatorDimensis->first()->is_active)
                                            <br><span class="badge bg-danger p-1 fs-8 text-uppercase" style="font-size: 0.65rem;">Non-aktif oleh Admin</span>
                                        @endif
                                    </th>
                                </tr>
                                <tr class="col-num">
                                    <th class="sticky-col">(1)</th>
                                    <th>(2)</th>
                                </tr>
                            @else
                                <tr>
                                    <th rowspan="2" class="sticky-col">Kabupaten/Kota/Provinsi</th>
                                    <th colspan="{{ $colspan }}">{{ $selectedPeriode ? $selectedPeriode->tahun : '-' }}</th>
                                </tr>
                                <tr>
                                    @forelse($indikatorDimensis as $indDim)
                                        <th style="min-width: 120px;">
                                            {{ $indDim->dimensi->nama_dimensi ?? '-' }}
                                            @if(!$indDim->is_active)
                                                <br><span class="badge bg-danger p-1 fs-8 text-uppercase" style="font-size: 0.65rem;">Non-aktif oleh Admin</span>
                                            @endif
                                        </th>
                                    @empty
                                        <th>Dimensi Belum Diatur</th>
                                    @endforelse
                                </tr>
                                {{-- Column Numbering Row --}}
                                <tr class="col-num">
                                    <th class="sticky-col">(1)</th>
                                    @if($dimColCount > 0)
                                        @for($i = 0; $i < $dimColCount; $i++)
                                            <th>({{ $i + 2 }})</th>
                                        @endfor
                                    @else
                                        <th>(2)</th>
                                    @endif
                                </tr>
                            @endif
                        </thead>
                        <tbody>
                            @forelse($kabupatens as $idx => $kab)
                                @php
                                    $isProvince = $kab->kode_kab === '6100';
                                    $isIndonesia = ($kab->kode_kab === '1' || strtolower($kab->nama_kabupaten) === 'indonesia');
                                    $rowStyle = ($isProvince || $isIndonesia) ? 'font-weight: bold; background-color: #f9f9f9;' : '';
                                @endphp
                                <tr style="{{ $rowStyle }}" data-kode-kab="{{ $kab->kode_kab }}" data-nama-kab="{{ strtolower($kab->nama_kabupaten) }}">
                                    <td class="sticky-col">
                                        @if($isIndonesia)
                                            <strong>INDONESIA</strong>
                                        @elseif($isProvince)
                                            <strong>[{{ $kab->kode_kab }}] Provinsi {{ $kab->nama_kabupaten }}</strong>
                                        @else
                                            [{{ $kab->kode_kab }}] {{ $kab->nama_kabupaten }}
                                        @endif
                                    </td>
                                    @forelse($indikatorDimensis as $indDim)
                                        @php
                                            $val = $existingValues[$kab->id][$indDim->id] ?? '';
                                            if (is_numeric($val)) {
                                                $floatVal = (float)$val;
                                                if (floor($floatVal) == $floatVal) {
                                                    $val = number_format($floatVal, 0, '.', ',');
                                                } else {
                                                    $strVal = (string)$floatVal;
                                                    $dotPos = strpos($strVal, '.');
                                                    $decimals = 2;
                                                    if ($dotPos !== false) {
                                                        $decimals = strlen($strVal) - $dotPos - 1;
                                                    }
                                                    $val = number_format($floatVal, $decimals, '.', ',');
                                                }
                                            }
                                            $isDisabled = !$selectedPeriode->is_active || !$selectedIndikator->is_active || !$indDim->is_active;
                                        @endphp
                                        <td class="p-1">
                                            <input type="text" inputmode="decimal" name="nilai[{{ $kab->id }}][{{ $indDim->id }}]"
                                                class="bps-input-cell fw-medium" placeholder="{{ $isDisabled ? 'Non-aktif' : '-' }}" value="{{ $val }}"
                                                {{ $isDisabled ? 'disabled' : '' }}
                                                data-dimensi-id="{{ $indDim->id }}"
                                                style="{{ $isDisabled ? 'background-color: #e2e8f0; color: #64748b; cursor: not-allowed;' : '' }}">
                                        </td>
                                    @empty
                                        <td class="text-center text-muted small italic">-</td>
                                    @endforelse
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $colspan + 2 }}" class="text-center py-5 text-muted">
                                        Belum ada data wilayah kabupaten.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Simpan Semua button is at the top right of the card header --}}
            </form>
        </div>

    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const table = document.querySelector('.table-bps');
    if (!table) return;

    let isDirty = false;

    // Monitor typing / manual changes
    table.addEventListener('input', function (e) {
        if (e.target.classList.contains('bps-input-cell')) {
            isDirty = true;
        }
    });

    // Reset isDirty flag when form is submitted for saving
    const nilaiForm = document.getElementById('nilai-form');
    if (nilaiForm) {
        nilaiForm.addEventListener('submit', function () {
            isDirty = false;
        });
    }

    // Intercept filter dropdown form submissions
    const filterForm = document.getElementById('filter-form');
    if (filterForm) {
        filterForm.addEventListener('submit', function (e) {
            if (isDirty) {
                e.preventDefault();
                Swal.fire({
                    title: 'Data Belum Disimpan!',
                    text: 'Ada perubahan data yang belum disimpan. Apakah Anda yakin ingin mengubah filter dan membuang perubahan?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#f58220',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Ya, Ubah Filter',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        isDirty = false;
                        filterForm.submit();
                    }
                });
            }
        });
    }

    // Intercept browser reload / tab close / back buttons (standard browser prompts)
    window.addEventListener('beforeunload', function (e) {
        if (isDirty) {
            e.preventDefault();
            e.returnValue = ''; // Trigger browser built-in warning popup
        }
    });

    // Intercept clicks on local menu/navigation links
    document.addEventListener('click', function (e) {
        const anchor = e.target.closest('a');
        if (anchor && isDirty) {
            const href = anchor.getAttribute('href');
            // Ignore blank targets, hash links, javascript actions, etc.
            if (!href || href.startsWith('#') || href.startsWith('javascript:') || anchor.getAttribute('target') === '_blank') {
                return;
            }

            e.preventDefault();
            Swal.fire({
                title: 'Data Belum Disimpan!',
                text: 'Ada perubahan data yang belum disimpan. Apakah Anda yakin ingin meninggalkan halaman?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#f58220',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Tinggalkan',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    isDirty = false;
                    window.location.href = anchor.href;
                }
            });
        }
    });

    function normalizeName(name) {
        if (!name) return '';
        return name.toLowerCase()
            .replace(/^(kabupaten|kab|kota|provinsi|prov)\.?\s+/ig, '')
            .replace(/[^a-z0-9]/g, '')
            .trim();
    }

    function cleanNumberValue(valText) {
        let cleanVal = valText.trim();
        if (cleanVal === '-' || cleanVal === '—' || cleanVal === '') {
            return '';
        }
        
        // Strip percentage, Rp currency symbol, spaces, and other non-numeric chars except commas, dots, and minus sign
        cleanVal = cleanVal.replace(/[^0-9,\.\-]/g, '');
        return cleanVal;
    }

    function findRowByKab(identifier, trs) {
        const cleanId = identifier.trim().toLowerCase();
        if (!cleanId) return null;

        // Try exact match on code (extract digits)
        const codeMatch = cleanId.match(/\b\d{1,4}\b/);
        const code = codeMatch ? codeMatch[0] : null;

        for (let row of trs) {
            const rowKode = row.getAttribute('data-kode-kab');
            const rowNama = row.getAttribute('data-nama-kab'); // pre-lowercased
            
            if (code && rowKode === code) {
                return row;
            }
            if (cleanId === rowKode) {
                return row;
            }
            
            const normRowNama = normalizeName(rowNama);
            const normPasted = normalizeName(cleanId);
            if (normRowNama && normPasted && (normPasted.includes(normRowNama) || normRowNama.includes(normPasted))) {
                return row;
            }
        }
        return null;
    }

    function processExcelData(pastedText, startElement = null) {
        const rows = pastedText.split(/\r?\n/).map(r => r.trim()).filter(r => r !== '');
        if (rows.length === 0) return 0;

        const allDimIds = Array.from(document.querySelectorAll('.check-paste-dimensi')).map(el => el.value);
        const checkedDimIds = Array.from(document.querySelectorAll('.check-paste-dimensi:checked')).map(el => el.value);
        if (checkedDimIds.length === 0) return 0;

        const allTrs = Array.from(table.querySelectorAll('tbody tr'));
        
        const firstRowCells = rows[0].split('\t');
        const isSmartMatch = findRowByKab(firstRowCells[0], allTrs) !== null;

        let count = 0;

        if (isSmartMatch) {
            // Smart Matching Mode
            rows.forEach(rowText => {
                const cells = rowText.split('\t');
                if (cells.length < 2) return;
                
                const targetTr = findRowByKab(cells[0], allTrs);
                if (!targetTr) return;

                const valCells = cells.slice(1);
                
                // If cells count matches total dimensions, map 1-to-1 to allDimIds
                if (valCells.length === allDimIds.length) {
                    allDimIds.forEach((dimId, idx) => {
                        if (checkedDimIds.includes(dimId)) {
                            const input = targetTr.querySelector(`input.bps-input-cell[data-dimensi-id="${dimId}"]`);
                            if (input && !input.disabled) {
                                input.value = cleanNumberValue(valCells[idx]);
                                count++;
                            }
                        }
                    });
                } else {
                    // Otherwise, map 1-to-1 to the checked dimensions in order
                    checkedDimIds.forEach((dimId, idx) => {
                        if (idx < valCells.length) {
                            const input = targetTr.querySelector(`input.bps-input-cell[data-dimensi-id="${dimId}"]`);
                            if (input && !input.disabled) {
                                input.value = cleanNumberValue(valCells[idx]);
                                count++;
                            }
                        }
                    });
                }
            });
        } else {
            // Grid-based Mode
            let startRowIdx = 0;
            let startColIdx = 0;

            if (startElement) {
                const activeTd = startElement.closest('td');
                const activeTr = activeTd.closest('tr');
                startRowIdx = allTrs.indexOf(activeTr);
                
                const activeDimId = startElement.getAttribute('data-dimensi-id');
                startColIdx = allDimIds.indexOf(activeDimId);
                if (startColIdx === -1) {
                    startColIdx = 0;
                }
            }

            rows.forEach((rowText, rowOffset) => {
                const cells = rowText.split('\t');
                const targetTr = allTrs[startRowIdx + rowOffset];
                if (!targetTr) return;

                // If cells count matches total dimensions and pasted via textarea (no startElement)
                if (cells.length === allDimIds.length && !startElement) {
                    cells.forEach((valText, colOffset) => {
                        const dimId = allDimIds[colOffset];
                        if (checkedDimIds.includes(dimId)) {
                            const input = targetTr.querySelector(`input.bps-input-cell[data-dimensi-id="${dimId}"]`);
                            if (input && !input.disabled) {
                                input.value = cleanNumberValue(valText);
                                count++;
                            }
                        }
                    });
                } else if (startElement) {
                    // If started from a specific cell, map relative to allDimIds
                    cells.forEach((valText, colOffset) => {
                        const targetColIdx = startColIdx + colOffset;
                        if (targetColIdx >= allDimIds.length) return;

                        const dimId = allDimIds[targetColIdx];
                        if (checkedDimIds.includes(dimId)) {
                            const input = targetTr.querySelector(`input.bps-input-cell[data-dimensi-id="${dimId}"]`);
                            if (input && !input.disabled) {
                                input.value = cleanNumberValue(valText);
                                count++;
                            }
                        }
                    });
                } else {
                    // Fallback: map to checked dimensions in order
                    cells.forEach((valText, colOffset) => {
                        if (colOffset < checkedDimIds.length) {
                            const dimId = checkedDimIds[colOffset];
                            const input = targetTr.querySelector(`input.bps-input-cell[data-dimensi-id="${dimId}"]`);
                            if (input && !input.disabled) {
                                input.value = cleanNumberValue(valText);
                                count++;
                            }
                        }
                    });
                }
            });
        }

        if (count > 0) {
            isDirty = true;
        }

        return count;
    }

    // 1. Direct Copy-Paste into Input Cells
    table.addEventListener('paste', function (e) {
        const activeInput = document.activeElement;
        if (!activeInput || !activeInput.classList.contains('bps-input-cell')) return;

        const clipboardData = e.clipboardData || window.clipboardData;
        if (!clipboardData) return;

        const pastedText = clipboardData.getData('Text');
        if (!pastedText) return;

        e.preventDefault();

        const count = processExcelData(pastedText, activeInput);

        if (count > 0) {
            Swal.fire({
                icon: 'success',
                title: 'Data Ditempel!',
                text: `${count} sel nilai berhasil diisi dari data Excel.`,
                timer: 1500,
                showConfirmButton: false
            });
        }
    });

    // 2. Parse button from Textarea
    const btnParse = document.getElementById('btn-parse-excel');
    const textarea = document.getElementById('excel-paste-area');

    if (btnParse && textarea) {
        btnParse.addEventListener('click', function () {
            const checkedDimIds = Array.from(document.querySelectorAll('.check-paste-dimensi:checked')).map(el => el.value);
            if (checkedDimIds.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Kolom Kosong',
                    text: 'Silakan pilih minimal satu kolom dimensi untuk diisi.'
                });
                return;
            }

            const pastedText = textarea.value;
            if (!pastedText.trim()) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Kosong',
                    text: 'Silakan paste data dari Excel terlebih dahulu.'
                });
                return;
            }

            const count = processExcelData(pastedText, null);

            // Close the collapse area
            const collapseEl = document.getElementById('collapsePasteExcel');
            const bsCollapse = bootstrap.Collapse.getInstance(collapseEl) || new bootstrap.Collapse(collapseEl);
            bsCollapse.hide();

            // Clear textarea
            textarea.value = '';

            if (count > 0) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: `${count} sel nilai berhasil diisi dari data Excel.`,
                    timer: 2000,
                    showConfirmButton: false
                });
            } else {
                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan',
                    text: 'Tidak ada data yang berhasil diisi. Periksa kembali kecocokan nama Kabupaten/Kota.'
                });
            }
        });
    }
});
</script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        if ($('.select2-makro').length) {
            $('.select2-makro').select2({
                width: '100%',
                placeholder: "Pilih Indikator Makro..."
            });
            // Ensure select2 change triggers form submission correctly
            $('.select2-makro').on('select2:select', function (e) {
                // If form is dirty, let the generic interceptor handle it
                // We dispatch a submit event to the form
                const form = $(this).closest('form')[0];
                if (form) {
                    const event = new Event('submit', { cancelable: true, bubbles: true });
                    form.dispatchEvent(event);
                    if (!event.defaultPrevented) {
                        form.submit();
                    }
                }
            });
        }
    });
</script>
@endpush