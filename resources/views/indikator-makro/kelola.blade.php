@extends('layouts.admin')

@section('title', 'Kelola Indikator Makro')

@push('styles')
    <style>
        .bps-card {
            border-radius: 1rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }

        .btn-orange {
            background-color: #f58220;
            color: white;
            border: none;
        }

        .btn-orange:hover {
            background-color: #e57210;
            color: white;
        }

        .btn-outline-orange {
            color: #f58220;
            border-color: #f58220;
        }

        .btn-outline-orange:hover {
            background-color: #f58220;
            color: white;
        }

        .nav-tabs .nav-link {
            color: #495057;
            background-color: #f8f9fa;
        }

        .nav-tabs .nav-link.active {
            font-weight: 600;
            color: #f58220;
            background-color: #fff;
            border-bottom-color: #fff;
        }

        :root {
            --bps-orange: #f58220;
        }
    </style>
@endpush

@section('content')
    <div class="mt-3">

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 fade-in-up">
            <div>
                <h2 class="fw-bold mb-1" style="color: var(--bps-orange);">Kelola Master Indikator</h2>
                <p class="text-muted mb-0">Pengaturan Periode, Bidang, Makro, Dimensi, dan Indikator Dimensi</p>
            </div>
            <div class="mt-2 mt-md-0">
                @if(request('from') === 'input-nilai')
                    <a href="{{ route('indikator-makro.input-nilai', ['periode_indikator_id' => request('periode_indikator_id'), 'indikator_makro_id' => request('indikator_makro_id')]) }}"
                        class="btn btn-outline-secondary rounded-pill px-4">
                        <i class="fas fa-arrow-left me-2"></i>Kembali
                    </a>
                @else
                    <a href="{{ route('indikator-makro.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                        <i class="fas fa-arrow-left me-2"></i>Kembali
                    </a>
                @endif
            </div>
        </div>

        {{-- Status Legend/Info --}}
        <div class="card border-0 shadow-sm mb-4"
            style="border-radius: 12px; background-color: #fff8f3; border-left: 5px solid #f58220 !important;">
            <div class="card-body p-3">
                <div class="d-flex align-items-start">
                    <i class="fas fa-info-circle me-3 fs-5 mt-1" style="color: #f58220;"></i>
                    <div>
                        <h6 class="fw-bold mb-2 text-dark">Panduan Status Aktif / Non-aktif:</h6>
                        <div class="row g-2 text-secondary small">
                            <div class="col-md-4">
                                <span class="badge bg-danger me-1">Non-aktif Periode</span> Mengunci (disable) seluruh
                                pengisian nilai tabel indikator makro pada tahun bersangkutan.
                            </div>
                            <div class="col-md-4">
                                <span class="badge bg-danger me-1">Non-aktif Indikator/Dimensi</span> Mengunci (disable)
                                isian input nilai indikator makro atau dimensi spesifik tersebut.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show rounded-4 shadow-sm" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <span class="fw-bold">Gagal menyimpan data:</span>
                <ul class="mb-0 mt-1 ps-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @php $activeTab = request()->query('tab') ?? session('tab', 'periode'); @endphp
        <ul class="nav nav-tabs fw-medium border-bottom-0 mb-4 scrollable-tabs" id="kelolaTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $activeTab == 'periode' ? 'active' : '' }} px-4 py-3 rounded-top-3 border"
                    id="periode-tab" data-bs-toggle="tab" data-bs-target="#periode" type="button" role="tab">
                    <i class="fas fa-calendar-alt me-2"></i>Periode Indikator
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $activeTab == 'bidang' ? 'active' : '' }} px-4 py-3 rounded-top-3 border ms-2"
                    id="bidang-tab" data-bs-toggle="tab" data-bs-target="#bidang" type="button" role="tab">
                    <i class="fas fa-layer-group me-2"></i>Indikator Bidang
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $activeTab == 'makro' ? 'active' : '' }} px-4 py-3 rounded-top-3 border ms-2"
                    id="makro-tab" data-bs-toggle="tab" data-bs-target="#makro" type="button" role="tab">
                    <i class="fas fa-chart-line me-2"></i>Indikator Makro
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $activeTab == 'dimensi' ? 'active' : '' }} px-4 py-3 rounded-top-3 border ms-2"
                    id="dimensi-tab" data-bs-toggle="tab" data-bs-target="#dimensi" type="button" role="tab">
                    <i class="fas fa-cube me-2"></i>Dimensi
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button
                    class="nav-link {{ $activeTab == 'indikator-dimensi' ? 'active' : '' }} px-4 py-3 rounded-top-3 border ms-2"
                    id="indikator-dimensi-tab" data-bs-toggle="tab" data-bs-target="#indikator-dimensi" type="button"
                    role="tab">
                    <i class="fas fa-link me-2"></i>Indikator Dimensi
                </button>
            </li>
        </ul>

        <div class="tab-content" id="kelolaTabContent">

            {{-- ═══════════════════ TAB PERIODE ═══════════════════ --}}
            <div class="tab-pane fade {{ $activeTab == 'periode' ? 'show active' : '' }}" id="periode" role="tabpanel">
                <div class="card border-0 shadow-sm rounded-4 bps-card mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="mb-0 fw-bold" style="color: var(--bps-orange);">
                            <i class="fas fa-calendar-alt me-2"></i>Tambah & Kelola Periode Indikator
                        </h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('indikator-makro.periode.store') }}" method="POST" class="mb-4">
                            @csrf
                            <div class="row align-items-end">
                                <div class="col-md-7 mb-3">
                                    <label class="form-label fw-bold small">Tahun <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" name="tahun" required
                                        placeholder="Contoh: {{ date('Y') }}" min="2000" max="2100">
                                </div>
                                <div class="col-md-2 mb-3">
                                    <div class="form-check form-switch pt-2">
                                        <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                            id="isActivePeriode" checked>
                                        <label class="form-check-label fw-bold small" for="isActivePeriode">Aktif?</label>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <button class="btn btn-orange rounded-pill w-100" type="submit">Tambah Periode</button>
                                </div>
                            </div>
                        </form>
                        <hr class="my-4">
                        <h6 class="fw-bold text-muted small mb-3">Daftar Periode:</h6>
                        @if($periodeIndikators->count() > 0)
                                            <!-- Desktop Table View -->
                                            <div class="table-responsive d-none d-md-block">
                                                <table class="table table-hover align-middle table-bordered">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th width="5%" class="text-center">No</th>
                                                            <th>Tahun</th>
                                                            <th width="15%" class="text-center">Status</th>
                                                            <th width="25%" class="text-center">Informasi</th>
                                                            <th width="15%" class="text-center">Aksi</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($periodeIndikators as $idx => $item)
                                                            <tr>
                                                                <td class="text-center">
                                                                    {{ ($periodeIndikators->currentPage() - 1) * $periodeIndikators->perPage() + $idx + 1 }}
                                                                </td>
                                                                <td class="fw-bold">{{ $item->tahun }}</td>
                                                                <td class="text-center">
                                                                    <div class="form-check form-switch d-flex justify-content-center">
                                                                        <input class="form-check-input toggle-periode-status" type="checkbox"
                                                                            role="switch" data-id="{{ $item->id }}" {{ $item->is_active ? 'checked' : '' }} style="cursor: pointer; transform: scale(1.2);">
                                                                    </div>
                                                                </td>
                                                                <td class="text-start" style="font-size: 0.85rem;">
                                                                    <div class="d-flex flex-column text-muted">
                                                                        @if($item->created_at)
                                                                            <div>
                                                                                <i class="fas fa-clock me-1 text-success" title="Dibuat"></i>
                                                                                Dibuat: {{ $item->created_at->format('d/m/Y H:i') }}
                                                                            </div>
                                                                        @endif
                                                                        @if($item->updated_at && $item->updated_at != $item->created_at)
                                                                            <div class="mt-1 border-top pt-1">
                                                                                <i class="fas fa-clock me-1 text-primary" title="Diperbarui"></i>
                                                                                Diperbarui: {{ $item->updated_at->format('d/m/Y H:i') }}
                                                                            </div>
                                                                        @endif
                                                                        <div class="mt-1 @if($item->created_at) border-top pt-1 @endif text-info">
                                                                            <i class="fas fa-link me-1"></i>Digunakan: <span
                                                                                class="fw-bold">{{ $item->nilai_indikator_makros_count }}</span>
                                                                            data nilai
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                                <td class="text-center">
                                                                    <button type="button" class="btn btn-sm btn-warning mb-1" data-bs-toggle="modal"
                                                                        data-bs-target="#modalEditPeriode{{ $item->id }}" title="Edit">
                                                                        <i class="fas fa-edit"></i>
                                                                    </button>
                                                                    @if($item->nilai_indikator_makros_count > 0)
                                                                        <button type="button" class="btn btn-sm btn-danger mb-1" disabled
                                                                            title="Sedang digunakan oleh data nilai"><i
                                                                                class="fas fa-trash"></i></button>
                                                                    @else
                                                                        <form action="{{ route('indikator-makro.periode.destroy', $item->id) }}"
                                                                            method="POST" class="d-inline delete-form">
                                                                            @csrf @method('DELETE')
                                                                            <button type="submit" class="btn btn-sm btn-danger mb-1"><i
                                                                                    class="fas fa-trash"></i></button>
                                                                        </form>
                                                                    @endif
                                                                </td>
                                                            </tr>

                                                            {{-- Edit Modal --}}
                                                            <div class="modal fade" id="modalEditPeriode{{ $item->id }}" tabindex="-1"
                                                                aria-hidden="true" style="text-align: left;">
                                                                <div class="modal-dialog modal-dialog-centered">
                                                                    <div class="modal-content rounded-4 border-0">
                                                                        <div class="modal-header">
                                                                            <h5 class="modal-title fw-bold">Edit Periode</h5>
                                                                            <button type="button" class="btn-close"
                                                                                data-bs-dismiss="modal"></button>
                                                                        </div>
                                                                        <form action="{{ route('indikator-makro.periode.update', $item->id) }}"
                                                                            method="POST">
                                                                            @csrf @method('PUT')
                                                                            <div class="modal-body">
                                                                                <div class="mb-3">
                                                                                    <label class="form-label fw-medium">Tahun <span
                                                                                            class="text-danger">*</span></label>
                                                                                    <input type="number" name="tahun" class="form-control"
                                                                                        value="{{ $item->tahun }}" required min="2000" max="2100">
                                                                                </div>

                                                                            </div>
                                                                            <div class="modal-footer pb-2 border-0">
                                                                                <button type="button" class="btn btn-light rounded-pill px-4"
                                                                                    data-bs-dismiss="modal">Batal</button>
                                                                                <button type="submit"
                                                                                    class="btn btn-orange rounded-pill px-4">Simpan
                                                                                    Perubahan</button>
                                                                            </div>
                                                                        </form>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>

                                            <!-- Mobile Card View -->
                                            <div class="d-md-none">
                                                @foreach($periodeIndikators as $idx => $item)
                                                    <div class="card mb-3 border shadow-sm" style="border-radius: 12px;">
                                                        <div class="card-body p-3">
                                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                                <span class="badge bg-light text-dark border p-2 fw-bold"
                                                                    style="font-size: 0.9rem;">
                                                                    Tahun {{ $item->tahun }}
                                                                </span>
                                                                <div class="form-check form-switch p-0 m-0">
                                                                    <input class="form-check-input toggle-periode-status m-0" type="checkbox"
                                                                        role="switch" data-id="{{ $item->id }}" {{ $item->is_active ? 'checked' : '' }} style="cursor: pointer; width: 3.5em; height: 1.75em;">
                                                                </div>
                                                            </div>

                                                            <div class="d-flex justify-content-between align-items-center border-top pt-2 mt-2">
                                                                <div class="text-muted" style="font-size: 0.75rem;">
                                                                    <div class="text-info"><i class="fas fa-link me-1"></i>Digunakan: <span
                                                                            class="fw-bold">{{ $item->nilai_indikator_makros_count }}</span> data
                                                                        nilai</div>
                                                                    @if($item->created_at)
                                                                        <div class="mt-1"><i class="fas fa-clock me-1"></i>
                                                                            {{ $item->created_at->format('d/m/y H:i') }}</div>
                                                                    @endif
                                                                </div>
                                                                <div class="d-flex gap-2">
                                                                    <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal"
                                                                        data-bs-target="#modalEditPeriode{{ $item->id }}">
                                                                        <i class="fas fa-edit"></i>
                                                                    </button>
                                                                    @if($item->nilai_indikator_makros_count > 0)
                                                                        <button type="button" class="btn btn-danger btn-sm" disabled
                                                                            title="Sedang digunakan oleh data nilai"><i
                                                                                class="fas fa-trash"></i></button>
                                                                    @else
                                                                        <form action="{{ route('indikator-makro.periode.destroy', $item->id) }}"
                                                                            method="POST" class="d-inline delete-form">
                                                                            @csrf @method('DELETE')
                                                                            <button type="submit" class="btn btn-danger btn-sm"><i
                                                                                    class="fas fa-trash"></i></button>
                                                                        </form>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>

                                            <div class="mt-3">
                                                {{ $periodeIndikators->appends([
                                'tab' => 'periode',
                                'from' => request('from'),
                                'periode_indikator_id' => request('periode_indikator_id'),
                                'indikator_makro_id' => request('indikator_makro_id')
                            ])->links('pagination::bootstrap-5') }}
                                            </div>
                        @else
                            <div class="text-center text-muted p-3 bg-light rounded">
                                <small>Belum ada daftar periode.</small>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- ═══════════════════ TAB BIDANG ═══════════════════ --}}
            <div class="tab-pane fade {{ $activeTab == 'bidang' ? 'show active' : '' }}" id="bidang" role="tabpanel">
                <div class="card border-0 shadow-sm rounded-4 bps-card mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="mb-0 fw-bold" style="color: var(--bps-orange);">
                            <i class="fas fa-layer-group me-2"></i>Tambah & Kelola Indikator Bidang
                        </h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('indikator-makro.bidang.store') }}" method="POST" class="mb-4">
                            @csrf
                            <div class="row align-items-end">
                                <div class="col-md-9 mb-3">
                                    <label class="form-label fw-bold small">Nama Bidang <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="nama_bidang" required
                                        placeholder="Contoh: Ekonomi">
                                </div>

                                <div class="col-md-3 mb-3">
                                    <button class="btn btn-orange rounded-pill w-100" type="submit">Tambah Bidang</button>
                                </div>
                            </div>
                        </form>
                        <hr class="my-4">
                        <div class="row align-items-center mb-3">
                            <div class="col-md-6">
                                <h6 class="fw-bold text-muted small mb-0">Daftar Bidang (Geser untuk mengatur urutan):</h6>
                            </div>
                            <div class="col-md-6 mt-2 mt-md-0">
                                <form action="{{ route('indikator-makro.kelola') }}" method="GET">
                                    <input type="hidden" name="tab" value="bidang">
                                    @if(request('from'))
                                        <input type="hidden" name="from" value="{{ request('from') }}">
                                    @endif
                                    @if(request('periode_indikator_id'))
                                        <input type="hidden" name="periode_indikator_id"
                                            value="{{ request('periode_indikator_id') }}">
                                    @endif
                                    @if(request('indikator_makro_id'))
                                        <input type="hidden" name="indikator_makro_id"
                                            value="{{ request('indikator_makro_id') }}">
                                    @endif
                                    <div class="input-group shadow-sm" style="border-radius: 20px; overflow: hidden;">
                                        <span class="input-group-text bg-white border-end-0 text-muted"><i
                                                class="fas fa-search"></i></span>
                                        <input type="text" name="search_bidang" id="search-bidang-input"
                                            class="form-control border-start-0" placeholder="Cari nama bidang..."
                                            value="{{ $searchBidang ?? '' }}" style="font-size: 0.85rem;">
                                        @if($searchBidang)
                                            <a href="{{ route('indikator-makro.kelola', ['tab' => 'bidang', 'from' => request('from'), 'periode_indikator_id' => request('periode_indikator_id'), 'indikator_makro_id' => request('indikator_makro_id')]) }}"
                                                class="btn btn-outline-secondary d-flex align-items-center justify-content-center"
                                                title="Bersihkan Pencarian"><i class="fas fa-times"></i></a>
                                        @endif
                                    </div>
                                </form>
                            </div>
                        </div>
                        @if($indikatorBidangs->count() > 0)
                                            <!-- Desktop Table View -->
                                            <div class="table-responsive d-none d-md-block">
                                                <table class="table table-hover align-middle table-bordered" id="tableBidang">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th style="width: 50px;"></th>
                                                            <th style="width: 80px;" class="text-center">Urutan</th>
                                                            <th>Nama Bidang</th>

                                                            <th class="text-center" width="25%">Informasi</th>
                                                            <th class="text-center" width="15%">Aksi</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="sortable-bidang">
                                                        @foreach($indikatorBidangs as $idx => $item)
                                                            <tr data-id="{{ $item->id }}">
                                                                <td class="text-center" style="cursor: grab;">
                                                                    <i class="fas fa-grip-vertical text-muted"></i>
                                                                </td>
                                                                <td class="text-center sortable-urutan fw-bold">
                                                                    {{ ($indikatorBidangs->currentPage() - 1) * $indikatorBidangs->perPage() + $idx + 1 }}
                                                                </td>
                                                                <td class="fw-bold">{{ $item->nama_bidang }}</td>

                                                                <td class="text-start" style="font-size: 0.85rem;">
                                                                    <div class="d-flex flex-column text-muted">
                                                                        @if($item->created_at)
                                                                            <div>
                                                                                <i class="fas fa-user-plus me-1 text-success" title="User Add"></i>
                                                                                {{ $item->creator ? $item->creator->name : 'Sistem' }}
                                                                                <br>
                                                                                <small
                                                                                    class="ms-4 text-secondary">{{ $item->created_at->format('d/m/Y H:i') }}</small>
                                                                            </div>
                                                                        @endif

                                                                        @if($item->updated_at && $item->updated_at != $item->created_at)
                                                                            <div class="mt-1 border-top pt-1">
                                                                                <i class="fas fa-user-edit me-1 text-primary" title="User Edit"></i>
                                                                                {{ $item->updater ? $item->updater->name : 'Sistem' }}
                                                                                <br>
                                                                                <small
                                                                                    class="ms-4 text-secondary">{{ $item->updated_at->format('d/m/Y H:i') }}</small>
                                                                            </div>
                                                                        @endif
                                                                        <div class="mt-1 border-top pt-1 text-info">
                                                                            <i class="fas fa-link me-1"></i>
                                                                            Digunakan: <span
                                                                                class="fw-bold">{{ $item->indikator_makros_count }}</span> indikator
                                                                            makro
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                                <td class="text-center">
                                                                    <button type="button" class="btn btn-sm btn-warning mb-1" data-bs-toggle="modal"
                                                                        data-bs-target="#modalEditBidang{{ $item->id }}" title="Edit">
                                                                        <i class="fas fa-edit"></i>
                                                                    </button>
                                                                    @if($item->indikator_makros_count > 0)
                                                                        <button type="button" class="btn btn-sm btn-danger mb-1" disabled
                                                                            title="Sedang digunakan oleh data makro"><i
                                                                                class="fas fa-trash"></i></button>
                                                                    @else
                                                                        <form action="{{ route('indikator-makro.bidang.destroy', $item->id) }}"
                                                                            method="POST" class="d-inline delete-form">
                                                                            @csrf @method('DELETE')
                                                                            <button type="submit" class="btn btn-sm btn-danger mb-1"><i
                                                                                    class="fas fa-trash"></i></button>
                                                                        </form>
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>

                                            <!-- Mobile Card View -->
                                            <div class="d-md-none" id="sortable-bidang-mobile">
                                                @foreach($indikatorBidangs as $idx => $item)
                                                    <div class="card mb-3 border shadow-sm" style="border-radius: 12px;" data-id="{{ $item->id }}">
                                                        <div class="card-body p-3">
                                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                                <div class="d-flex align-items-center">
                                                                    <i class="fas fa-grip-vertical text-muted me-2 grip-handle"
                                                                        style="cursor: grab;"></i>
                                                                    <span class="badge bg-light text-dark border p-2 fw-bold badge-sequence"
                                                                        style="font-size: 0.9rem;">
                                                                        #{{ ($indikatorBidangs->currentPage() - 1) * $indikatorBidangs->perPage() + $idx + 1 }}
                                                                    </span>
                                                                </div>
                                                                <div class="d-flex gap-2 align-items-center">
                                                                    <div class="form-check form-switch p-0 m-0">
                                                                        <input class="form-check-input toggle-bidang-status m-0" type="checkbox"
                                                                            role="switch" data-id="{{ $item->id }}" {{ $item->is_active ? 'checked' : '' }} style="cursor: pointer; width: 3.5em; height: 1.75em;">
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <h6 class="fw-bold mb-3">{{ $item->nama_bidang }}</h6>

                                                            <div class="text-muted border-top pt-2 mt-2" style="font-size: 0.75rem;">
                                                                <div class="d-flex justify-content-between">
                                                                    <span><i class="fas fa-user-plus me-1 text-success"></i>
                                                                        {{ $item->creator ? str($item->creator->name)->words(2, '') : 'Sistem' }}</span>
                                                                    <span>{{ $item->created_at ? $item->created_at->format('d/m/y H:i') : '-' }}</span>
                                                                </div>
                                                                @if($item->updater && $item->updater != $item->creator)
                                                                    <div class="d-flex justify-content-between mt-1">
                                                                        <span><i class="fas fa-user-edit me-1 text-primary"></i>
                                                                            {{ $item->updater ? str($item->updater->name)->words(2, '') : 'Sistem' }}</span>
                                                                        <span>{{ $item->updated_at->format('d/m/y H:i') }}</span>
                                                                    </div>
                                                                @endif
                                                                <div class="text-info mt-1 border-top pt-1">
                                                                    <i class="fas fa-link me-1"></i>Digunakan: <span
                                                                        class="fw-bold">{{ $item->indikator_makros_count }}</span> indikator makro
                                                                </div>
                                                            </div>

                                                            <div class="d-flex justify-content-end gap-2 mt-2 border-top pt-2">
                                                                <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal"
                                                                    data-bs-target="#modalEditBidang{{ $item->id }}">
                                                                    <i class="fas fa-edit"></i> Edit
                                                                </button>
                                                                @if($item->indikator_makros_count > 0)
                                                                    <button type="button" class="btn btn-danger btn-sm" disabled
                                                                        title="Sedang digunakan oleh data makro"><i class="fas fa-trash"></i>
                                                                        Hapus</button>
                                                                @else
                                                                    <form action="{{ route('indikator-makro.bidang.destroy', $item->id) }}"
                                                                        method="POST" class="d-inline delete-form">
                                                                        @csrf @method('DELETE')
                                                                        <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i>
                                                                            Hapus</button>
                                                                    </form>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>

                                            <!-- Edit Modals -->
                                            @foreach($indikatorBidangs as $item)
                                                <div class="modal fade" id="modalEditBidang{{ $item->id }}" tabindex="-1" aria-hidden="true"
                                                    style="text-align: left;">
                                                    <div class="modal-dialog modal-dialog-centered">
                                                        <div class="modal-content rounded-4 border-0">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title fw-bold">Edit Bidang</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <form action="{{ route('indikator-makro.bidang.update', $item->id) }}" method="POST">
                                                                @csrf @method('PUT')
                                                                <div class="modal-body">
                                                                    <div class="mb-3">
                                                                        <label class="form-label fw-medium">Nama Bidang <span
                                                                                class="text-danger">*</span></label>
                                                                        <input type="text" name="nama_bidang" class="form-control"
                                                                            value="{{ $item->nama_bidang }}" required>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label class="form-label fw-medium">Urutan <span
                                                                                class="text-danger">*</span></label>
                                                                        <input type="number" name="urutan" class="form-control"
                                                                            value="{{ $item->urutan }}" required min="1">
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer pb-2 border-0">
                                                                    <button type="button" class="btn btn-light rounded-pill px-4"
                                                                        data-bs-dismiss="modal">Batal</button>
                                                                    <button type="submit" class="btn btn-orange rounded-pill px-4">Simpan
                                                                        Perubahan</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach

                                            <div class="mt-3">
                                                {{ $indikatorBidangs->appends([
                                'tab' => 'bidang',
                                'search_bidang' => $searchBidang,
                                'from' => request('from'),
                                'periode_indikator_id' => request('periode_indikator_id'),
                                'indikator_makro_id' => request('indikator_makro_id')
                            ])->links('pagination::bootstrap-5') }}
                                            </div>
                        @else
                            <div class="text-center text-muted p-3 bg-light rounded">
                                <small>Belum ada data bidang.</small>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- ═══════════════════ TAB MAKRO ═══════════════════ --}}
            <div class="tab-pane fade {{ $activeTab == 'makro' ? 'show active' : '' }}" id="makro" role="tabpanel">
                <div class="card border-0 shadow-sm rounded-4 bps-card mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="mb-0 fw-bold" style="color: var(--bps-orange);">
                            <i class="fas fa-chart-line me-2"></i>Tambah & Kelola Indikator Makro
                        </h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('indikator-makro.makro.store') }}" method="POST" class="mb-4">
                            @csrf
                            <div class="row align-items-end">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-bold small">Bidang <span
                                            class="text-danger">*</span></label>
                                    <select name="indikator_bidang_id" class="form-select"
                                        onchange="filterByBidang(this.value)" required>
                                        <option value="">-- Pilih Bidang --</option>
                                        @foreach($allIndikatorBidangs as $bidang)
                                            <option value="{{ $bidang->id }}" {{ $selectedFilterBidangId == $bidang->id ? 'selected' : '' }}>
                                                {{ $bidang->nama_bidang }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-bold small">Nama Indikator <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="nama_indikator" required
                                        placeholder="Contoh: Pertumbuhan Ekonomi" {{ !$selectedFilterBidangId ? 'disabled' : '' }}>
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label class="form-label fw-bold small">Satuan</label>
                                    <input type="text" class="form-control" name="satuan" placeholder="Contoh: %" {{ !$selectedFilterBidangId ? 'disabled' : '' }}>
                                </div>
                                <div class="col-md-2 mb-3">
                                    <div class="form-check pt-2">
                                        <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                            id="isActiveMakro" checked {{ !$selectedFilterBidangId ? 'disabled' : '' }}>
                                        <label class="form-check-label fw-bold small" for="isActiveMakro">Aktif?</label>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-9 mb-3">
                                    <label class="form-label fw-bold small">Deskripsi <span
                                            class="text-muted">(opsional)</span></label>
                                    <textarea class="form-control" name="deskripsi" rows="2"
                                        placeholder="Deskripsi singkat indikator..." {{ !$selectedFilterBidangId ? 'disabled' : '' }}></textarea>
                                </div>
                                <div class="col-md-3 mb-3 d-flex align-items-end">
                                    <button class="btn btn-orange rounded-pill w-100" type="submit" {{ !$selectedFilterBidangId ? 'disabled' : '' }}>Tambah Makro</button>
                                </div>
                            </div>
                        </form>
                        <hr class="my-4">
                        @if($selectedFilterBidangId)
                            <div class="row align-items-center mb-3">
                                <div class="col-md-6">
                                    <h6 class="fw-bold text-muted small mb-0">Daftar Indikator Makro Bidang
                                        {{ $selectedFilterBidang->nama_bidang ?? '' }} (Geser untuk mengatur urutan):
                                    </h6>
                                </div>
                                <div class="col-md-6 mt-2 mt-md-0">
                                    <form action="{{ route('indikator-makro.kelola') }}" method="GET">
                                        <input type="hidden" name="tab" value="makro">
                                        <input type="hidden" name="filter_bidang_id" value="{{ $selectedFilterBidangId }}">
                                        @if(request('from'))
                                            <input type="hidden" name="from" value="{{ request('from') }}">
                                        @endif
                                        @if(request('periode_indikator_id'))
                                            <input type="hidden" name="periode_indikator_id"
                                                value="{{ request('periode_indikator_id') }}">
                                        @endif
                                        @if(request('indikator_makro_id'))
                                            <input type="hidden" name="indikator_makro_id"
                                                value="{{ request('indikator_makro_id') }}">
                                        @endif
                                        <div class="input-group shadow-sm" style="border-radius: 20px; overflow: hidden;">
                                            <span class="input-group-text bg-white border-end-0 text-muted"><i
                                                    class="fas fa-search"></i></span>
                                            <input type="text" name="search_makro" id="search-makro-input"
                                                class="form-control border-start-0" placeholder="Cari nama indikator makro..."
                                                value="{{ $searchMakro ?? '' }}" style="font-size: 0.85rem;">
                                            @if($searchMakro)
                                                <a href="{{ route('indikator-makro.kelola', ['tab' => 'makro', 'filter_bidang_id' => $selectedFilterBidangId, 'from' => request('from'), 'periode_indikator_id' => request('periode_indikator_id'), 'indikator_makro_id' => request('indikator_makro_id')]) }}"
                                                    class="btn btn-outline-secondary d-flex align-items-center justify-content-center"
                                                    title="Bersihkan Pencarian"><i class="fas fa-times"></i></a>
                                            @endif
                                        </div>
                                    </form>
                                </div>
                            </div>
                            @if($indikatorMakros->count() > 0)
                                            <!-- Desktop Table View -->
                                            <div class="table-responsive d-none d-md-block">
                                                <table class="table table-hover align-middle table-bordered">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th style="width: 50px;"></th>
                                                            <th style="width: 80px;" class="text-center">Urutan</th>
                                                            <th>Nama Indikator</th>
                                                            <th>Satuan</th>
                                                            <th>Deskripsi</th>
                                                            <th class="text-center" width="15%">Status</th>
                                                            <th class="text-center" width="25%">Informasi</th>
                                                            <th class="text-center" width="15%">Aksi</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="sortable-makro">
                                                        @foreach($indikatorMakros as $idx => $item)
                                                            <tr data-id="{{ $item->id }}">
                                                                <td class="text-center" style="cursor: grab;">
                                                                    <i class="fas fa-grip-vertical text-muted"></i>
                                                                </td>
                                                                <td class="text-center sortable-urutan fw-bold">{{ $item->urutan }}</td>
                                                                <td class="fw-bold">{{ $item->nama_indikator }}</td>
                                                                <td>{{ $item->satuan ?? '-' }}</td>
                                                                <td>{{ $item->deskripsi ?? '-' }}</td>
                                                                <td class="text-center">
                                                                    <div class="form-check form-switch d-flex justify-content-center">
                                                                        <input class="form-check-input toggle-makro-status" type="checkbox"
                                                                            role="switch" data-id="{{ $item->id }}" {{ $item->is_active ? 'checked' : '' }} style="cursor: pointer; transform: scale(1.2);">
                                                                    </div>
                                                                </td>
                                                                <td class="text-start" style="font-size: 0.85rem;">
                                                                    <div class="d-flex flex-column text-muted">
                                                                        @if($item->created_at)
                                                                            <div>
                                                                                <i class="fas fa-user-plus me-1 text-success" title="User Add"></i>
                                                                                {{ $item->creator ? $item->creator->name : 'Sistem' }}
                                                                                <br>
                                                                                <small
                                                                                    class="ms-4 text-secondary">{{ $item->created_at->format('d/m/Y H:i') }}</small>
                                                                            </div>
                                                                        @endif

                                                                        @if($item->updated_at && $item->updated_at != $item->created_at)
                                                                            <div class="mt-1 border-top pt-1">
                                                                                <i class="fas fa-user-edit me-1 text-primary" title="User Edit"></i>
                                                                                {{ $item->updater ? $item->updater->name : 'Sistem' }}
                                                                                <br>
                                                                                <small
                                                                                    class="ms-4 text-secondary">{{ $item->updated_at->format('d/m/Y H:i') }}</small>
                                                                            </div>
                                                                        @endif
                                                                        <div class="mt-1 border-top pt-1 text-info">
                                                                            <i class="fas fa-link me-1"></i>
                                                                            Digunakan: <span
                                                                                class="fw-bold">{{ $item->indikator_dimensis_count }}</span>
                                                                            indikator dimensi
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                                <td class="text-center">
                                                                    <button type="button" class="btn btn-sm btn-warning mb-1" data-bs-toggle="modal"
                                                                        data-bs-target="#modalEditMakro{{ $item->id }}" title="Edit">
                                                                        <i class="fas fa-edit"></i>
                                                                    </button>
                                                                    @if($item->indikator_dimensis_count > 0)
                                                                        <button type="button" class="btn btn-sm btn-danger mb-1" disabled
                                                                            title="Sedang digunakan oleh data relasi dimensi"><i
                                                                                class="fas fa-trash"></i></button>
                                                                    @else
                                                                        <form action="{{ route('indikator-makro.makro.destroy', $item->id) }}"
                                                                            method="POST" class="d-inline delete-form">
                                                                            @csrf @method('DELETE')
                                                                            <button type="submit" class="btn btn-sm btn-danger mb-1"><i
                                                                                    class="fas fa-trash"></i></button>
                                                                        </form>
                                                                    @endif
                                                                    <a href="{{ route('indikator-makro.input-nilai', ['indikator_makro_id' => $item->id, 'from' => 'kelola', 'filter_bidang_id' => $selectedFilterBidangId]) }}" class="btn btn-sm btn-info mb-1" title="Input Nilai Indikator Makro">
                                                                        <i class="fas fa-keyboard"></i> Input
                                                                    </a>
                                                                    <a href="{{ route('indikator-makro.kelola', ['tab' => 'indikator-dimensi', 'filter_makro_id' => $item->id, 'filter_bidang_id' => $selectedFilterBidangId]) }}" class="btn btn-sm btn-primary mb-1" title="Atur Indikator Dimensi">
                                                                        <i class="fas fa-cogs"></i> Atur Dimensi
                                                                    </a>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>

                                            <!-- Mobile Card View -->
                                            <div class="d-md-none" id="sortable-makro-mobile">
                                                @foreach($indikatorMakros as $idx => $item)
                                                    <div class="card mb-3 border shadow-sm" style="border-radius: 12px;" data-id="{{ $item->id }}">
                                                        <div class="card-body p-3">
                                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                                <div>
                                                                    <i class="fas fa-grip-vertical text-muted me-2 grip-handle"
                                                                        style="cursor: grab;"></i>
                                                                    <span class="badge bg-light text-dark border p-2 fw-bold badge-sequence"
                                                                        style="font-size: 0.9rem;">
                                                                        #{{ $item->urutan }}
                                                                    </span>
                                                                    @if($item->satuan)
                                                                        <span class="badge bg-secondary text-white">
                                                                            {{ $item->satuan }}
                                                                        </span>
                                                                    @endif
                                                                </div>
                                                                <div class="d-flex gap-2 align-items-center">
                                                                    <div class="form-check form-switch p-0 m-0">
                                                                        <input class="form-check-input toggle-makro-status m-0" type="checkbox"
                                                                            role="switch" data-id="{{ $item->id }}" {{ $item->is_active ? 'checked' : '' }} style="cursor: pointer; width: 3.5em; height: 1.75em;">
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <h6 class="fw-bold mb-2">{{ $item->nama_indikator }}</h6>
                                                            @if($item->deskripsi)
                                                                <p class="text-secondary small mb-2">{{ $item->deskripsi }}</p>
                                                            @endif

                                                            <div class="text-muted border-top pt-2 mt-2" style="font-size: 0.75rem;">
                                                                <div class="d-flex justify-content-between">
                                                                    <span><i class="fas fa-user-plus me-1 text-success"></i>
                                                                        {{ $item->creator ? str($item->creator->name)->words(2, '') : 'Sistem' }}</span>
                                                                    <span>{{ $item->created_at ? $item->created_at->format('d/m/y H:i') : '-' }}</span>
                                                                </div>
                                                                @if($item->updater && $item->updater != $item->creator)
                                                                    <div class="d-flex justify-content-between mt-1">
                                                                        <span><i class="fas fa-user-edit me-1 text-primary"></i>
                                                                            {{ $item->updater ? str($item->updater->name)->words(2, '') : 'Sistem' }}</span>
                                                                        <span>{{ $item->updated_at->format('d/m/y H:i') }}</span>
                                                                    </div>
                                                                @endif
                                                                <div class="text-info mt-1 border-top pt-1">
                                                                    <i class="fas fa-link me-1"></i>Digunakan: <span
                                                                        class="fw-bold">{{ $item->indikator_dimensis_count }}</span> indikator
                                                                    dimensi
                                                                </div>
                                                            </div>

                                                            <div class="d-flex justify-content-end gap-2 mt-2 border-top pt-2">
                                                                <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal"
                                                                    data-bs-target="#modalEditMakro{{ $item->id }}">
                                                                    <i class="fas fa-edit"></i> Edit
                                                                </button>
                                                                @if($item->indikator_dimensis_count > 0)
                                                                    <button type="button" class="btn btn-danger btn-sm" disabled
                                                                        title="Sedang digunakan oleh data relasi dimensi"><i class="fas fa-trash"></i>
                                                                        Hapus</button>
                                                                @else
                                                                    <form action="{{ route('indikator-makro.makro.destroy', $item->id) }}" method="POST"
                                                                        class="d-inline delete-form">
                                                                        @csrf @method('DELETE')
                                                                        <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i>
                                                                            Hapus</button>
                                                                    </form>
                                                                @endif
                                                                <a href="{{ route('indikator-makro.input-nilai', ['indikator_makro_id' => $item->id, 'from' => 'kelola', 'filter_bidang_id' => $selectedFilterBidangId]) }}" class="btn btn-info btn-sm">
                                                                    <i class="fas fa-keyboard"></i> Input
                                                                </a>
                                                                <a href="{{ route('indikator-makro.kelola', ['tab' => 'indikator-dimensi', 'filter_makro_id' => $item->id, 'filter_bidang_id' => $selectedFilterBidangId]) }}" class="btn btn-primary btn-sm">
                                                                    <i class="fas fa-cogs"></i> Atur Dimensi
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>

                                            <!-- Edit Modals -->
                                            @foreach($indikatorMakros as $item)
                                                <div class="modal fade" id="modalEditMakro{{ $item->id }}" tabindex="-1" aria-hidden="true"
                                                    style="text-align: left;">
                                                    <div class="modal-dialog modal-dialog-centered">
                                                        <div class="modal-content rounded-4 border-0">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title fw-bold">Edit Indikator Makro</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <form action="{{ route('indikator-makro.makro.update', $item->id) }}" method="POST">
                                                                @csrf @method('PUT')
                                                                <div class="modal-body">
                                                                    <div class="mb-3">
                                                                        <label class="form-label fw-medium">Bidang <span
                                                                                class="text-danger">*</span></label>
                                                                        <select name="indikator_bidang_id" class="form-select" required>
                                                                            <option value="">-- Pilih Bidang --</option>
                                                                            @foreach($allIndikatorBidangs as $bidang)
                                                                                <option value="{{ $bidang->id }}" {{ $item->indikator_bidang_id == $bidang->id ? 'selected' : '' }}>
                                                                                    {{ $bidang->nama_bidang }}
                                                                                </option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label class="form-label fw-medium">Nama Indikator <span
                                                                                class="text-danger">*</span></label>
                                                                        <input type="text" name="nama_indikator" class="form-control"
                                                                            value="{{ $item->nama_indikator }}" required>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label class="form-label fw-medium">Satuan</label>
                                                                        <input type="text" name="satuan" class="form-control"
                                                                            value="{{ $item->satuan }}">
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label class="form-label fw-medium">Deskripsi</label>
                                                                        <textarea name="deskripsi" class="form-control"
                                                                            rows="2">{{ $item->deskripsi }}</textarea>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label class="form-label fw-medium">Urutan <span
                                                                                class="text-danger">*</span></label>
                                                                        <input type="number" name="urutan" class="form-control"
                                                                            value="{{ $item->urutan }}" required min="1">
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <div class="form-check">
                                                                            <input class="form-check-input" type="checkbox" name="is_active"
                                                                                value="1" id="editIsActiveMakro{{ $item->id }}" {{ $item->is_active ? 'checked' : '' }}>
                                                                            <label class="form-check-label fw-medium"
                                                                                for="editIsActiveMakro{{ $item->id }}">Aktif</label>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer pb-2 border-0">
                                                                    <button type="button" class="btn btn-light rounded-pill px-4"
                                                                        data-bs-dismiss="modal">Batal</button>
                                                                    <button type="submit" class="btn btn-orange rounded-pill px-4">Simpan
                                                                        Perubahan</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach

                                            <div class="mt-3">
                                                {{ $indikatorMakros->appends([
                                    'tab' => 'makro',
                                    'search_makro' => request('search_makro'),
                                    'indikator_bidang_id' => request('indikator_bidang_id')
                                ])->links('pagination::bootstrap-5') }}
                                            </div>
                            @else
                                <div class="text-center text-muted p-3 bg-light rounded">
                                    <small>Belum ada data indikator makro untuk bidang yang dipilih.</small>
                                </div>
                            @endif
                        @else
                            <div class="text-center text-muted p-4 bg-light rounded-3 border">
                                <i class="fas fa-info-circle fa-2x mb-2 text-warning text-opacity-75"></i>
                                <p class="mb-0 fw-medium">Silakan pilih Bidang terlebih dahulu pada dropdown di atas untuk
                                    menampilkan daftar Indikator Makro.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- ═══════════════════ TAB DIMENSI ═══════════════════ --}}
            <div class="tab-pane fade {{ $activeTab == 'dimensi' ? 'show active' : '' }}" id="dimensi" role="tabpanel">
                <div class="card border-0 shadow-sm rounded-4 bps-card mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="mb-0 fw-bold" style="color: var(--bps-orange);">
                            <i class="fas fa-cube me-2"></i>Tambah & Kelola Dimensi
                        </h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('indikator-makro.dimensi.store') }}" method="POST" class="mb-4">
                            @csrf
                            <div class="row align-items-end">
                                <div class="col-md-9 mb-3">
                                    <label class="form-label fw-bold small">Nama Dimensi <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="nama_dimensi" required
                                        placeholder="Contoh: SD/SMP/SMA">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <button class="btn btn-orange rounded-pill w-100" type="submit">Tambah Dimensi</button>
                                </div>
                            </div>
                        </form>
                        <hr class="my-4">
                        <div class="row align-items-center mb-3">
                            <div class="col-md-6">
                                <h6 class="fw-bold text-muted small mb-0">Daftar Dimensi:</h6>
                            </div>
                            <div class="col-md-6 mt-2 mt-md-0">
                                <form action="{{ route('indikator-makro.kelola') }}" method="GET">
                                    <input type="hidden" name="tab" value="dimensi">
                                    @if(request('from'))
                                        <input type="hidden" name="from" value="{{ request('from') }}">
                                    @endif
                                    @if(request('periode_indikator_id'))
                                        <input type="hidden" name="periode_indikator_id"
                                            value="{{ request('periode_indikator_id') }}">
                                    @endif
                                    @if(request('indikator_makro_id'))
                                        <input type="hidden" name="indikator_makro_id"
                                            value="{{ request('indikator_makro_id') }}">
                                    @endif
                                    <div class="input-group shadow-sm" style="border-radius: 20px; overflow: hidden;">
                                        <span class="input-group-text bg-white border-end-0 text-muted"><i
                                                class="fas fa-search"></i></span>
                                        <input type="text" name="search_dimensi" id="search-dimensi-input"
                                            class="form-control border-start-0" placeholder="Cari nama dimensi..."
                                            value="{{ $searchDimensi ?? '' }}" style="font-size: 0.85rem;">
                                        @if($searchDimensi)
                                            <a href="{{ route('indikator-makro.kelola', ['tab' => 'dimensi', 'from' => request('from'), 'periode_indikator_id' => request('periode_indikator_id'), 'indikator_makro_id' => request('indikator_makro_id')]) }}"
                                                class="btn btn-outline-secondary d-flex align-items-center justify-content-center"
                                                title="Bersihkan Pencarian"><i class="fas fa-times"></i></a>
                                        @endif
                                    </div>
                                </form>
                            </div>
                        </div>
                        @if($dimensis->count() > 0)
                                            <!-- Desktop Table View -->
                                            <div class="table-responsive d-none d-md-block">
                                                <table class="table table-hover align-middle table-bordered">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th width="10%" class="text-center">No</th>
                                                            <th>Nama Dimensi</th>
                                                            <th class="text-center" width="25%">Informasi</th>
                                                            <th class="text-center" width="15%">Aksi</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($dimensis as $idx => $item)
                                                            <tr>
                                                                <td class="text-center">
                                                                    {{ ($dimensis->currentPage() - 1) * $dimensis->perPage() + $idx + 1 }}
                                                                </td>
                                                                <td class="fw-bold">{{ $item->nama_dimensi }}</td>
                                                                <td class="text-start" style="font-size: 0.85rem;">
                                                                    <div class="d-flex flex-column text-muted">
                                                                        @if($item->created_at)
                                                                            <div>
                                                                                <i class="fas fa-user-plus me-1 text-success" title="User Add"></i>
                                                                                {{ $item->creator ? $item->creator->name : 'Sistem' }}
                                                                                <br>
                                                                                <small
                                                                                    class="ms-4 text-secondary">{{ $item->created_at->format('d/m/Y H:i') }}</small>
                                                                            </div>
                                                                        @endif

                                                                        @if($item->updated_at && $item->updated_at != $item->created_at)
                                                                            <div class="mt-1 border-top pt-1">
                                                                                <i class="fas fa-user-edit me-1 text-primary" title="User Edit"></i>
                                                                                {{ $item->updater ? $item->updater->name : 'Sistem' }}
                                                                                <br>
                                                                                <small
                                                                                    class="ms-4 text-secondary">{{ $item->updated_at->format('d/m/Y H:i') }}</small>
                                                                            </div>
                                                                        @endif
                                                                        <div class="mt-1 border-top pt-1 text-info">
                                                                            <i class="fas fa-link me-1"></i>
                                                                            Digunakan: <span
                                                                                class="fw-bold">{{ $item->indikator_dimensis_count }}</span>
                                                                            indikator dimensi
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                                <td class="text-center">
                                                                    @if(strtolower(trim($item->nama_dimensi)) === 'none')
                                                                        <span class="badge bg-secondary px-2 py-1"
                                                                            title="Dimensi sistem, tidak dapat diubah">
                                                                            <i class="fas fa-lock me-1"></i>Terkunci
                                                                        </span>
                                                                    @else
                                                                        <button type="button" class="btn btn-sm btn-warning mb-1" data-bs-toggle="modal"
                                                                            data-bs-target="#modalEditDimensi{{ $item->id }}" title="Edit">
                                                                            <i class="fas fa-edit"></i>
                                                                        </button>
                                                                        @if($item->indikator_dimensis_count > 0)
                                                                            <button type="button" class="btn btn-sm btn-danger mb-1" disabled
                                                                                title="Sedang digunakan oleh data relasi indikator"><i
                                                                                    class="fas fa-trash"></i></button>
                                                                        @else
                                                                            <form action="{{ route('indikator-makro.dimensi.destroy', $item->id) }}"
                                                                                method="POST" class="d-inline delete-form">
                                                                                @csrf @method('DELETE')
                                                                                <button type="submit" class="btn btn-sm btn-danger mb-1"><i
                                                                                        class="fas fa-trash"></i></button>
                                                                            </form>
                                                                        @endif
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>

                                            <!-- Mobile Card View -->
                                            <div class="d-md-none">
                                                @foreach($dimensis as $idx => $item)
                                                    <div class="card mb-3 border shadow-sm" style="border-radius: 12px;">
                                                        <div class="card-body p-3">
                                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                                <span class="badge bg-light text-dark border p-2 fw-bold"
                                                                    style="font-size: 0.9rem;">
                                                                    #{{ ($dimensis->currentPage() - 1) * $dimensis->perPage() + $idx + 1 }}
                                                                </span>
                                                            </div>

                                                            <h6 class="fw-bold mb-3">{{ $item->nama_dimensi }}</h6>

                                                            <div class="text-muted border-top pt-2 mt-2" style="font-size: 0.75rem;">
                                                                <div class="d-flex justify-content-between">
                                                                    <span><i class="fas fa-user-plus me-1 text-success"></i>
                                                                        {{ $item->creator ? str($item->creator->name)->words(2, '') : 'Sistem' }}</span>
                                                                    <span>{{ $item->created_at ? $item->created_at->format('d/m/y H:i') : '-' }}</span>
                                                                </div>
                                                                @if($item->updater && $item->updater != $item->creator)
                                                                    <div class="d-flex justify-content-between mt-1">
                                                                        <span><i class="fas fa-user-edit me-1 text-primary"></i>
                                                                            {{ $item->updater ? str($item->updater->name)->words(2, '') : 'Sistem' }}</span>
                                                                        <span>{{ $item->updated_at->format('d/m/y H:i') }}</span>
                                                                    </div>
                                                                @endif
                                                                <div class="text-info mt-1 border-top pt-1">
                                                                    <i class="fas fa-link me-1"></i>Digunakan: <span
                                                                        class="fw-bold">{{ $item->indikator_dimensis_count }}</span> indikator
                                                                    dimensi
                                                                </div>
                                                            </div>

                                                            <div class="d-flex justify-content-end gap-2 mt-2 border-top pt-2">
                                                                @if(strtolower(trim($item->nama_dimensi)) === 'none')
                                                                    <span class="badge bg-secondary px-3 py-2"
                                                                        title="Dimensi sistem, tidak dapat diubah">
                                                                        <i class="fas fa-lock me-1"></i>Terkunci
                                                                    </span>
                                                                @else
                                                                    <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal"
                                                                        data-bs-target="#modalEditDimensi{{ $item->id }}">
                                                                        <i class="fas fa-edit"></i> Edit
                                                                    </button>
                                                                    @if($item->indikator_dimensis_count > 0)
                                                                        <button type="button" class="btn btn-danger btn-sm" disabled
                                                                            title="Sedang digunakan oleh data relasi indikator"><i class="fas fa-trash"></i>
                                                                            Hapus</button>
                                                                    @else
                                                                        <form action="{{ route('indikator-makro.dimensi.destroy', $item->id) }}"
                                                                            method="POST" class="d-inline delete-form">
                                                                            @csrf @method('DELETE')
                                                                            <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i>
                                                                                Hapus</button>
                                                                        </form>
                                                                    @endif
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>

                                            <!-- Edit Modals -->
                                            @foreach($dimensis as $item)
                                                <div class="modal fade" id="modalEditDimensi{{ $item->id }}" tabindex="-1" aria-hidden="true"
                                                    style="text-align: left;">
                                                    <div class="modal-dialog modal-dialog-centered">
                                                        <div class="modal-content rounded-4 border-0">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title fw-bold">Edit Dimensi</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <form action="{{ route('indikator-makro.dimensi.update', $item->id) }}" method="POST">
                                                                @csrf @method('PUT')
                                                                <div class="modal-body">
                                                                    <div class="mb-3">
                                                                        <label class="form-label fw-medium">Nama Dimensi <span
                                                                                class="text-danger">*</span></label>
                                                                        <input type="text" name="nama_dimensi" class="form-control"
                                                                            value="{{ $item->nama_dimensi }}" required>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer pb-2 border-0">
                                                                    <button type="button" class="btn btn-light rounded-pill px-4"
                                                                        data-bs-dismiss="modal">Batal</button>
                                                                    <button type="submit" class="btn btn-orange rounded-pill px-4">Simpan
                                                                        Perubahan</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach

                                            <div class="mt-3">
                                                {{ $dimensis->appends([
                                'tab' => 'dimensi',
                                'search_dimensi' => $searchDimensi,
                                'from' => request('from'),
                                'periode_indikator_id' => request('periode_indikator_id'),
                                'indikator_makro_id' => request('indikator_makro_id')
                            ])->links('pagination::bootstrap-5') }}
                                            </div>
                        @else
                            <div class="text-center text-muted p-3 bg-light rounded">
                                <small>Belum ada data dimensi.</small>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- ═══════════════════ TAB INDIKATOR DIMENSI ═══════════════════ --}}
            <div class="tab-pane fade {{ $activeTab == 'indikator-dimensi' ? 'show active' : '' }}" id="indikator-dimensi"
                role="tabpanel">
                <div class="card border-0 shadow-sm rounded-4 bps-card mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="mb-0 fw-bold" style="color: var(--bps-orange);">
                            <i class="fas fa-link me-2"></i>Tambah & Kelola Indikator Dimensi
                        </h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('indikator-makro.indikator-dimensi.store') }}" method="POST" class="mb-4">
                            @csrf
                            <div class="row align-items-end">
                                <div class="col-md-5 mb-3">
                                    <label class="form-label fw-bold small">Indikator Makro <span
                                            class="text-danger">*</span></label>
                                    <div class="position-relative" id="indikator-search-wrapper">
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0">
                                                <i class="fas fa-search text-muted"></i>
                                            </span>
                                            <input type="text" id="indikator-search-input"
                                                class="form-control border-start-0 ps-1"
                                                placeholder="Cari Indikator Makro..."
                                                value="{{ $selectedMakro ? $selectedMakro->nama_indikator : '' }}"
                                                autocomplete="off" required>
                                            @if($selectedMakro)
                                                <button class="btn btn-outline-secondary border-start-0" type="button"
                                                    id="btn-clear-search" title="Bersihkan Pilihan">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            @endif
                                        </div>
                                        <input type="hidden" name="indikator_makro_id" id="indikator-search-id"
                                            value="{{ $selectedMakroId ?? '' }}">
                                        <div id="indikator-search-results" class="dropdown-menu w-100 shadow border-0 py-1"
                                            style="max-height: 250px; overflow-y: auto; display: none; position: absolute; z-index: 1050; top: 100%;">
                                            <!-- AJAX results will be loaded here -->
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-bold small">Dimensi <span
                                            class="text-danger">*</span></label>
                                    <select name="dimensi_id" class="form-select" required>
                                        <option value="">-- Pilih Dimensi --</option>
                                        @foreach($allDimensis as $dimensi)
                                            <option value="{{ $dimensi->id }}">{{ $dimensi->nama_dimensi }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <button class="btn btn-orange rounded-pill w-100" type="submit">Tambah Relasi</button>
                                </div>
                            </div>
                        </form>



                        <hr class="my-4">

                        @if($selectedMakroId)
                            <div class="row align-items-center mb-3">
                                <div class="col-md-6">
                                    <h6 class="fw-bold text-muted small mb-0">Daftar Indikator Dimensi:
                                        {{ $selectedMakro->nama_indikator ?? '-' }}
                                    </h6>
                                </div>
                                <div class="col-md-6 mt-2 mt-md-0">
                                    <form action="{{ route('indikator-makro.kelola') }}" method="GET">
                                        <input type="hidden" name="tab" value="indikator-dimensi">
                                        <input type="hidden" name="filter_makro_id" value="{{ $selectedMakroId }}">
                                        @if(request('from'))
                                            <input type="hidden" name="from" value="{{ request('from') }}">
                                        @endif
                                        @if(request('periode_indikator_id'))
                                            <input type="hidden" name="periode_indikator_id"
                                                value="{{ request('periode_indikator_id') }}">
                                        @endif
                                        @if(request('indikator_makro_id'))
                                            <input type="hidden" name="indikator_makro_id"
                                                value="{{ request('indikator_makro_id') }}">
                                        @endif
                                        <div class="input-group shadow-sm" style="border-radius: 20px; overflow: hidden;">
                                            <span class="input-group-text bg-white border-end-0 text-muted"><i
                                                    class="fas fa-search"></i></span>
                                            <input type="text" name="search_ind_dimensi" id="search-ind-dimensi-input"
                                                class="form-control border-start-0" placeholder="Cari nama dimensi..."
                                                value="{{ $searchIndDimensi ?? '' }}" style="font-size: 0.85rem;">
                                            @if($searchIndDimensi)
                                                <a href="{{ route('indikator-makro.kelola', ['tab' => 'indikator-dimensi', 'filter_makro_id' => $selectedMakroId, 'from' => request('from'), 'periode_indikator_id' => request('periode_indikator_id'), 'indikator_makro_id' => request('indikator_makro_id')]) }}"
                                                    class="btn btn-outline-secondary d-flex align-items-center justify-content-center"
                                                    title="Bersihkan Pencarian"><i class="fas fa-times"></i></a>
                                            @endif
                                        </div>
                                    </form>
                                </div>
                            </div>
                            @if($indikatorDimensis->count() > 0)
                                            <!-- Desktop Table View -->
                                            <div class="table-responsive d-none d-md-block">
                                                <table class="table table-hover align-middle table-bordered">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th style="width: 50px;"></th>
                                                            <th style="width: 80px;" class="text-center">Urutan</th>
                                                            <th>Dimensi</th>
                                                            <th class="text-center" style="width: 100px;">Status</th>
                                                            <th class="text-center" width="25%">Informasi</th>
                                                            <th class="text-center" width="15%">Aksi</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="sortable-ind-dimensi">
                                                        @foreach($indikatorDimensis as $idx => $item)
                                                            <tr data-id="{{ $item->id }}">
                                                                <td class="text-center" style="cursor: grab;">
                                                                    <i class="fas fa-grip-vertical text-muted"></i>
                                                                </td>
                                                                <td class="text-center sortable-urutan fw-bold">
                                                                    {{ ($indikatorDimensis->currentPage() - 1) * $indikatorDimensis->perPage() + $idx + 1 }}
                                                                </td>
                                                                <td class="fw-bold">{{ $item->dimensi->nama_dimensi ?? '-' }}</td>
                                                                <td class="text-center">
                                                                    <div class="form-check form-switch d-flex justify-content-center">
                                                                        <input class="form-check-input toggle-ind-dim-status" type="checkbox"
                                                                            role="switch" data-id="{{ $item->id }}" {{ $item->is_active ? 'checked' : '' }} style="cursor: pointer; transform: scale(1.2);">
                                                                    </div>
                                                                </td>
                                                                <td class="text-start" style="font-size: 0.85rem;">
                                                                    <div class="d-flex flex-column text-muted">
                                                                        @if($item->created_at)
                                                                            <div>
                                                                                <i class="fas fa-user-plus me-1 text-success" title="User Add"></i>
                                                                                {{ $item->creator ? $item->creator->name : 'Sistem' }}
                                                                                <br>
                                                                                <small
                                                                                    class="ms-4 text-secondary">{{ $item->created_at->format('d/m/Y H:i') }}</small>
                                                                            </div>
                                                                        @endif

                                                                        @if($item->updated_at && $item->updated_at != $item->created_at)
                                                                            <div class="mt-1 border-top pt-1">
                                                                                <i class="fas fa-user-edit me-1 text-primary" title="User Edit"></i>
                                                                                {{ $item->updater ? $item->updater->name : 'Sistem' }}
                                                                                <br>
                                                                                <small
                                                                                    class="ms-4 text-secondary">{{ $item->updated_at->format('d/m/Y H:i') }}</small>
                                                                            </div>
                                                                        @endif
                                                                        <div class="mt-1 border-top pt-1 text-info">
                                                                            <i class="fas fa-link me-1"></i>
                                                                            Digunakan: <span
                                                                                class="fw-bold">{{ $item->nilai_indikator_makros_count }}</span>
                                                                            data nilai
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                                <td class="text-center">
                                                                    <button type="button" class="btn btn-sm btn-warning mb-1" data-bs-toggle="modal"
                                                                        data-bs-target="#modalEditIndDim{{ $item->id }}" title="Edit">
                                                                        <i class="fas fa-edit"></i>
                                                                    </button>
                                                                    @if($item->nilai_indikator_makros_count > 0)
                                                                        <button type="button" class="btn btn-sm btn-danger mb-1" disabled
                                                                            title="Sedang digunakan oleh data nilai"><i
                                                                                class="fas fa-trash"></i></button>
                                                                    @else
                                                                        <form
                                                                            action="{{ route('indikator-makro.indikator-dimensi.destroy', $item->id) }}"
                                                                            method="POST" class="d-inline delete-form">
                                                                            @csrf @method('DELETE')
                                                                            <button type="submit" class="btn btn-sm btn-danger mb-1"><i
                                                                                    class="fas fa-trash"></i></button>
                                                                        </form>
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>

                                            <!-- Mobile Card View -->
                                            <div class="d-md-none" id="sortable-ind-dimensi-mobile">
                                                @foreach($indikatorDimensis as $idx => $item)
                                                    <div class="card mb-3 border shadow-sm" style="border-radius: 12px;" data-id="{{ $item->id }}">
                                                        <div class="card-body p-3">
                                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                                <div>
                                                                    <i class="fas fa-grip-vertical text-muted me-2 grip-handle"
                                                                        style="cursor: grab;"></i>
                                                                    <span class="badge bg-light text-dark border p-2 fw-bold badge-sequence"
                                                                        style="font-size: 0.9rem;">
                                                                        #{{ ($indikatorDimensis->currentPage() - 1) * $indikatorDimensis->perPage() + $idx + 1 }}
                                                                    </span>
                                                                    <span class="badge bg-orange text-white">
                                                                        {{ $item->dimensi->nama_dimensi ?? '-' }}
                                                                    </span>
                                                                </div>
                                                                <div class="d-flex gap-2 align-items-center">
                                                                    <div class="form-check form-switch p-0 m-0">
                                                                        <input class="form-check-input toggle-ind-dim-status m-0" type="checkbox"
                                                                            role="switch" data-id="{{ $item->id }}" {{ $item->is_active ? 'checked' : '' }} style="cursor: pointer; width: 3.5em; height: 1.75em;">
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="text-muted border-top pt-2 mt-2" style="font-size: 0.75rem;">
                                                                <div class="d-flex justify-content-between">
                                                                    <span><i class="fas fa-user-plus me-1 text-success"></i>
                                                                        {{ $item->creator ? str($item->creator->name)->words(2, '') : 'Sistem' }}</span>
                                                                    <span>{{ $item->created_at ? $item->created_at->format('d/m/y H:i') : '-' }}</span>
                                                                </div>
                                                                @if($item->updater && $item->updater != $item->creator)
                                                                    <div class="d-flex justify-content-between mt-1">
                                                                        <span><i class="fas fa-user-edit me-1 text-primary"></i>
                                                                            {{ $item->updater ? str($item->updater->name)->words(2, '') : 'Sistem' }}</span>
                                                                        <span>{{ $item->updated_at->format('d/m/y H:i') }}</span>
                                                                    </div>
                                                                @endif
                                                                <div class="text-info mt-1 border-top pt-1">
                                                                    <i class="fas fa-link me-1"></i>Digunakan: <span
                                                                        class="fw-bold">{{ $item->nilai_indikator_makros_count }}</span> data nilai
                                                                </div>
                                                                <div class="d-flex justify-content-end gap-2 mt-2 border-top pt-2">
                                                                    <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal"
                                                                        data-bs-target="#modalEditIndDim{{ $item->id }}" title="Edit">
                                                                        <i class="fas fa-edit"></i> Edit
                                                                    </button>
                                                                    @if($item->nilai_indikator_makros_count > 0)
                                                                        <button type="button" class="btn btn-danger btn-sm" disabled
                                                                            title="Sedang digunakan oleh data nilai"><i class="fas fa-trash"></i>
                                                                            Hapus</button>
                                                                    @else
                                                                        <form
                                                                            action="{{ route('indikator-makro.indikator-dimensi.destroy', $item->id) }}"
                                                                            method="POST" class="d-inline delete-form">
                                                                            @csrf @method('DELETE')
                                                                            <button type="submit" class="btn btn-danger btn-sm"><i
                                                                                    class="fas fa-trash"></i>
                                                                                Hapus</button>
                                                                        </form>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>

                                            <!-- Edit Modals -->
                                            @foreach($indikatorDimensis as $item)
                                                <div class="modal fade" id="modalEditIndDim{{ $item->id }}" tabindex="-1" aria-hidden="true"
                                                    style="text-align: left;">
                                                    <div class="modal-dialog modal-dialog-centered">
                                                        <div class="modal-content rounded-4 border-0">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title fw-bold">Edit Urutan Indikator Dimensi</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <form action="{{ route('indikator-makro.indikator-dimensi.update', $item->id) }}"
                                                                method="POST">
                                                                @csrf @method('PUT')
                                                                <input type="hidden" name="indikator_makro_id"
                                                                    value="{{ $item->indikator_makro_id }}">
                                                                <input type="hidden" name="dimensi_id" value="{{ $item->dimensi_id }}">
                                                                <div class="modal-body">
                                                                    <div class="mb-3">
                                                                        <label class="form-label fw-medium">Dimensi</label>
                                                                        <input type="text" class="form-control bg-light"
                                                                            value="{{ $item->dimensi->nama_dimensi ?? '-' }}" readonly>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label class="form-label fw-medium">Urutan <span
                                                                                class="text-danger">*</span></label>
                                                                        <input type="number" name="urutan" class="form-control"
                                                                            value="{{ $item->urutan }}" min="1" required>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer pb-2 border-0">
                                                                    <button type="button" class="btn btn-light rounded-pill px-4"
                                                                        data-bs-dismiss="modal">Batal</button>
                                                                    <button type="submit" class="btn btn-orange rounded-pill px-4">Simpan
                                                                        Perubahan</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach

                                            <div class="mt-3">
                                                {{ $indikatorDimensis->appends([
                                    'tab' => 'indikator-dimensi',
                                    'filter_makro_id' => $selectedMakroId,
                                    'search_ind_dimensi' => $searchIndDimensi,
                                    'from' => request('from'),
                                    'periode_indikator_id' => request('periode_indikator_id'),
                                    'indikator_makro_id' => request('indikator_makro_id')
                                ])->links('pagination::bootstrap-5') }}
                                            </div>
                            @else
                                <div class="text-center text-muted p-3 bg-light rounded">
                                    <small>Belum ada relasi indikator dimensi untuk indikator makro yang dipilih.</small>
                                </div>
                            @endif
                        @else
                            <div class="text-center text-muted p-4 bg-light rounded-3 border">
                                <i class="fas fa-info-circle fa-2x mb-2 text-warning text-opacity-75"></i>
                                <p class="mb-0 fw-medium">Silakan pilih Indikator Makro terlebih dahulu pada dropdown di atas
                                    untuk menampilkan daftar Indikator Dimensi.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // 1. Tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            });

            // Helper function to update both desktop table rows and mobile card sequence numbers
            const updateSequenceNumbers = (desktopElId, mobileElId, startIdx, numberSelector) => {
                const desktopEl = document.getElementById(desktopElId);
                if (desktopEl) {
                    desktopEl.querySelectorAll('.sortable-urutan').forEach(function (cell, i) {
                        cell.textContent = startIdx + i + 1;
                    });
                }
                const mobileEl = document.getElementById(mobileElId);
                if (mobileEl) {
                    mobileEl.querySelectorAll(numberSelector).forEach(function (el, i) {
                        el.textContent = '#' + (startIdx + i + 1);
                    });
                }
            };

            // Drag & Drop helper for both desktop and mobile containers
            const initBidirectionalSortable = (desktopElId, mobileElId, routeUrl, startIdx, numberSelector) => {
                const desktopEl = document.getElementById(desktopElId);
                if (desktopEl) {
                    new Sortable(desktopEl, {
                        animation: 150,
                        handle: '.fa-grip-vertical',
                        ghostClass: 'table-warning',
                        onEnd: function () {
                            const order = [];
                            desktopEl.querySelectorAll('tr[data-id]').forEach(function (row) {
                                order.push(row.getAttribute('data-id'));
                            });

                            // Synchronize mobile cards order in DOM before fetching
                            const mobileEl = document.getElementById(mobileElId);
                            if (mobileEl) {
                                order.forEach(id => {
                                    const card = mobileEl.querySelector(`.card[data-id="${id}"]`);
                                    if (card) mobileEl.appendChild(card);
                                });
                            }

                            fetch(routeUrl, {
                                method: 'PATCH',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({ order: order, start_idx: startIdx })
                            }).then(function (response) {
                                if (response.ok) {
                                    updateSequenceNumbers(desktopElId, mobileElId, startIdx, numberSelector);
                                }
                            });
                        }
                    });
                }

                const mobileEl = document.getElementById(mobileElId);
                if (mobileEl) {
                    new Sortable(mobileEl, {
                        animation: 150,
                        handle: '.grip-handle',
                        ghostClass: 'bg-warning-subtle',
                        onEnd: function () {
                            const order = [];
                            mobileEl.querySelectorAll('.card[data-id]').forEach(function (card) {
                                order.push(card.getAttribute('data-id'));
                            });

                            // Synchronize desktop table rows order in DOM before fetching
                            if (desktopEl) {
                                order.forEach(id => {
                                    const row = desktopEl.querySelector(`tr[data-id="${id}"]`);
                                    if (row) desktopEl.appendChild(row);
                                });
                            }

                            fetch(routeUrl, {
                                method: 'PATCH',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({ order: order, start_idx: startIdx })
                            }).then(function (response) {
                                if (response.ok) {
                                    updateSequenceNumbers(desktopElId, mobileElId, startIdx, numberSelector);
                                }
                            });
                        }
                    });
                }
            };

            // 2. Drag & Drop Indikator Makro
            const startIdxMakro = {{ ($indikatorMakros->currentPage() - 1) * $indikatorMakros->perPage() }};
            initBidirectionalSortable('sortable-makro', 'sortable-makro-mobile', '/indikator-makro/makro/reorder', startIdxMakro, '.badge-sequence');

            // 3. Drag & Drop Indikator Bidang
            const startIdxBidang = {{ ($indikatorBidangs->currentPage() - 1) * $indikatorBidangs->perPage() }};
            initBidirectionalSortable('sortable-bidang', 'sortable-bidang-mobile', '/indikator-makro/bidang/reorder', startIdxBidang, '.badge-sequence');

            // 3b. Drag & Drop Indikator Dimensi
            const startIdxIndDimensi = {{ ($indikatorDimensis->currentPage() - 1) * $indikatorDimensis->perPage() }};
            initBidirectionalSortable('sortable-ind-dimensi', 'sortable-ind-dimensi-mobile', '/indikator-makro/indikator-dimensi/reorder', startIdxIndDimensi, '.badge-sequence');

            // 4. Toggle Periode Status Switch
            document.querySelectorAll('.toggle-periode-status').forEach(function (toggle) {
                toggle.addEventListener('change', function () {
                    const id = this.getAttribute('data-id');
                    const url = '/indikator-makro/periode/' + id + '/toggle';

                    fetch(url, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    }).then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    toast: true,
                                    position: 'top-end',
                                    icon: 'success',
                                    title: 'Status periode diperbarui',
                                    showConfirmButton: false,
                                    timer: 1500
                                });
                            }
                        });
                });
            });

            // 5. Toggle Bidang Status Switch
            document.querySelectorAll('.toggle-bidang-status').forEach(function (toggle) {
                toggle.addEventListener('change', function () {
                    const id = this.getAttribute('data-id');
                    const url = '/indikator-makro/bidang/' + id + '/toggle';

                    fetch(url, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    }).then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    toast: true,
                                    position: 'top-end',
                                    icon: 'success',
                                    title: 'Status bidang diperbarui',
                                    showConfirmButton: false,
                                    timer: 1500
                                });
                            }
                        });
                });
            });

            // 5b. Toggle Makro Status Switch
            document.querySelectorAll('.toggle-makro-status').forEach(function (toggle) {
                toggle.addEventListener('change', function () {
                    const id = this.getAttribute('data-id');
                    const url = '/indikator-makro/makro/' + id + '/toggle';

                    fetch(url, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    }).then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    toast: true,
                                    position: 'top-end',
                                    icon: 'success',
                                    title: 'Status indikator makro diperbarui',
                                    showConfirmButton: false,
                                    timer: 1500
                                });
                            }
                        });
                });
            });

            // 5c. Toggle Indikator Dimensi Status Switch
            document.querySelectorAll('.toggle-ind-dim-status').forEach(function (toggle) {
                toggle.addEventListener('change', function () {
                    const id = this.getAttribute('data-id');
                    const url = '/indikator-makro/indikator-dimensi/' + id + '/toggle';

                    fetch(url, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    }).then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    toast: true,
                                    position: 'top-end',
                                    icon: 'success',
                                    title: 'Status dimensi diperbarui',
                                    showConfirmButton: false,
                                    timer: 1500
                                });
                            }
                        });
                });
            });

            window.filterByMakro = function (val) {
                const url = new URL(window.location.href);
                url.searchParams.set('tab', 'indikator-dimensi');
                if (val) {
                    url.searchParams.set('filter_makro_id', val);
                } else {
                    url.searchParams.delete('filter_makro_id');
                }
                url.searchParams.delete('ind_dimensi_page');
                window.location.href = url.toString();
            };

            window.filterByBidang = function (val) {
                const url = new URL(window.location.href);
                url.searchParams.set('tab', 'makro');
                if (val) {
                    url.searchParams.set('filter_bidang_id', val);
                } else {
                    url.searchParams.delete('filter_bidang_id');
                }
                url.searchParams.delete('makro_page');
                window.location.href = url.toString();
            };

            // 7. Live AJAX Search Autocomplete for Indikator Makro Selection
            const searchInput = document.getElementById('indikator-search-input');
            const searchId = document.getElementById('indikator-search-id');
            const searchResults = document.getElementById('indikator-search-results');
            const btnClearSearch = document.getElementById('btn-clear-search');
            let lastSelectedName = '{{ $selectedMakro ? $selectedMakro->nama_indikator : "" }}';
            let lastSelectedId = '{{ $selectedMakroId ?? "" }}';
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
                                    filterByMakro(item.id);
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
                        // Revert back if no suggestion chosen
                        if (!searchId.value) {
                            searchInput.value = lastSelectedName;
                            searchId.value = lastSelectedId;
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
                    filterByMakro('');
                });
            }

            // 6. SweetAlert2 Delete Confirmation
            document.querySelectorAll('.delete-form').forEach(function (form) {
                form.addEventListener('submit', function (e) {
                    e.preventDefault();

                    Swal.fire({
                        title: 'Apakah Anda yakin?',
                        text: "Data yang dihapus tidak dapat dikembalikan!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#f58220', // BPS Orange
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Ya, hapus!',
                        cancelButtonText: 'Batal',
                        customClass: {
                            popup: 'rounded-4 border-0 shadow-sm'
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });

            // 7. Active Tab Tracker (history.replaceState)
            const tabButtons = document.querySelectorAll('#kelolaTab button[data-bs-toggle="tab"]');
            tabButtons.forEach(function (button) {
                button.addEventListener('shown.bs.tab', function (event) {
                    const targetId = event.target.getAttribute('data-bs-target').replace('#', '');
                    const url = new URL(window.location.href);
                    url.searchParams.set('tab', targetId);
                    window.history.replaceState(null, '', url.toString());
                });
            });

            // 8. Blur-to-submit for search inputs (submit form when user clicks outside / loses focus)
            const blurSubmitSearchIds = [
                'search-bidang-input',
                'search-makro-input',
                'search-dimensi-input',
                'search-ind-dimensi-input'
            ];
            blurSubmitSearchIds.forEach(function (inputId) {
                const el = document.getElementById(inputId);
                if (!el) return;
                el.addEventListener('blur', function () {
                    const form = el.closest('form');
                    if (!form) return;
                    // Only submit if value changed from original
                    if (el.value !== el.defaultValue) {
                        form.submit();
                    }
                });
            });
        });
    </script>
@endpush