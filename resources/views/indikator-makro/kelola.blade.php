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
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
            <div>
                <a href="{{ route('indikator-makro.index') }}" class="btn btn-light btn-sm mb-2 rounded-pill">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
                <h2 class="fw-bold mb-1" style="color: var(--bps-orange);">Kelola Master Indikator</h2>
                <p class="text-muted mb-0">Pengaturan Periode, Bidang, Makro, Dimensi, dan Indikator Dimensi</p>
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

        @php $activeTab = session('tab', 'periode'); @endphp
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
                            <div class="table-responsive table-scrollable">
                                <table class="table table-hover align-middle">
                                    <thead class="bg-light">
                                        <tr>
                                            <th width="10%">No</th>
                                            <th>Tahun</th>
                                            <th class="text-center">Status</th>
                                            <th class="text-center">Data Digunakan</th>
                                            <th class="text-end" width="20%">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($periodeIndikators as $idx => $item)
                                            <tr>
                                                <td>{{ ($periodeIndikators->currentPage() - 1) * $periodeIndikators->perPage() + $idx + 1 }}</td>
                                                <td class="fw-bold">{{ $item->tahun }}</td>
                                                <td class="text-center">
                                                    <div class="form-check form-switch d-inline-block">
                                                        <input class="form-check-input toggle-periode-status" type="checkbox"
                                                            data-id="{{ $item->id }}" {{ $item->is_active ? 'checked' : '' }}>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-info rounded-pill">{{ $item->nilai_indikator_makros_count }} kali</span>
                                                </td>
                                                <td class="text-end">
                                                    <button type="button" class="btn btn-sm btn-outline-orange mb-1 rounded-pill"
                                                        data-bs-toggle="modal" data-bs-target="#modalEditPeriode{{ $item->id }}">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    @if($item->nilai_indikator_makros_count > 0)
                                                        <button type="button" class="btn btn-sm btn-outline-danger mb-1 rounded-pill" disabled title="Sedang digunakan oleh data nilai"><i class="fas fa-trash"></i></button>
                                                    @else
                                                        <form action="{{ route('indikator-makro.periode.destroy', $item->id) }}"
                                                            method="POST" class="d-inline delete-form">
                                                            @csrf @method('DELETE')
                                                            <button type="submit"
                                                                class="btn btn-sm btn-outline-danger mb-1 rounded-pill"><i
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
                            <div class="mt-3">
                                {{ $periodeIndikators->appends(['tab' => 'periode'])->links('pagination::bootstrap-5') }}
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
                                <div class="col-md-7 mb-3">
                                    <label class="form-label fw-bold small">Nama Bidang <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="nama_bidang" required
                                        placeholder="Contoh: Ekonomi">
                                </div>
                                <div class="col-md-2 mb-3">
                                    <div class="form-check form-switch pt-2">
                                        <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                            id="isActiveBidang" checked>
                                        <label class="form-check-label fw-bold small" for="isActiveBidang">Aktif?</label>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <button class="btn btn-orange rounded-pill w-100" type="submit">Tambah Bidang</button>
                                </div>
                            </div>
                        </form>
                        <hr class="my-4">
                        <h6 class="fw-bold text-muted small mb-3">Daftar Bidang (Geser untuk mengatur urutan):</h6>
                        @if($indikatorBidangs->count() > 0)
                            <div class="table-responsive table-scrollable">
                                <table class="table table-hover align-middle">
                                    <thead class="bg-light">
                                        <tr>
                                            <th style="width: 50px;"></th>
                                            <th style="width: 80px;" class="text-center">Urutan</th>
                                            <th>Nama Bidang</th>
                                            <th class="text-center">Status</th>
                                            <th class="text-center">Data Digunakan</th>
                                            <th class="text-center">Info User</th>
                                            <th class="text-end" width="15%">Aksi</th>
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
                                                <td class="fw-bold">
                                                    {{ $item->nama_bidang }}
                                                </td>
                                                <td class="text-center">
                                                    <div class="form-check form-switch d-inline-block">
                                                        <input class="form-check-input toggle-bidang-status" type="checkbox"
                                                            data-id="{{ $item->id }}" {{ $item->is_active ? 'checked' : '' }}>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-info rounded-pill">{{ $item->indikator_makros_count }} kali</span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="me-2" style="font-size: 1.1rem; cursor: pointer;">
                                                        <i class="fas fa-user-plus text-primary" data-bs-toggle="tooltip"
                                                            data-bs-placement="top"
                                                            title="Dibuat oleh: {{ $item->creator->name ?? 'System' }} pada {{ $item->created_at ? $item->created_at->format('d/m/Y H:i') : '-' }}"></i>
                                                    </span>
                                                    @if($item->updater)
                                                    <span style="font-size: 1.1rem; cursor: pointer;">
                                                        <i class="fas fa-user-edit text-success" data-bs-toggle="tooltip"
                                                            data-bs-placement="top"
                                                            title="Diperbarui oleh: {{ $item->updater->name }} pada {{ $item->updated_at ? $item->updated_at->format('d/m/Y H:i') : '-' }}"></i>
                                                    </span>
                                                    @endif
                                                </td>
                                                <td class="text-end">
                                                    <button type="button" class="btn btn-sm btn-outline-orange mb-1 rounded-pill"
                                                        data-bs-toggle="modal" data-bs-target="#modalEditBidang{{ $item->id }}">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    @if($item->indikator_makros_count > 0)
                                                        <button type="button" class="btn btn-sm btn-outline-danger mb-1 rounded-pill" disabled title="Sedang digunakan oleh data makro"><i class="fas fa-trash"></i></button>
                                                    @else
                                                        <form action="{{ route('indikator-makro.bidang.destroy', $item->id) }}"
                                                            method="POST" class="d-inline delete-form">
                                                            @csrf @method('DELETE')
                                                            <button type="submit"
                                                                class="btn btn-sm btn-outline-danger mb-1 rounded-pill"><i
                                                                    class="fas fa-trash"></i></button>
                                                        </form>
                                                    @endif
                                                </td>
                                            </tr>

                                            {{-- Edit Modal --}}
                                            <div class="modal fade" id="modalEditBidang{{ $item->id }}" tabindex="-1"
                                                aria-hidden="true" style="text-align: left;">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content rounded-4 border-0">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title fw-bold">Edit Bidang</h5>
                                                            <button type="button" class="btn-close"
                                                                data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <form action="{{ route('indikator-makro.bidang.update', $item->id) }}"
                                                            method="POST">
                                                            @csrf @method('PUT')
                                                            <div class="modal-body">
                                                                <div class="mb-3">
                                                                    <label class="form-label fw-medium">Nama Bidang <span
                                                                            class="text-danger">*</span></label>
                                                                    <input type="text" name="nama_bidang" class="form-control"
                                                                        value="{{ $item->nama_bidang }}" required>
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
                            <div class="mt-3">
                                {{ $indikatorBidangs->appends(['tab' => 'bidang'])->links('pagination::bootstrap-5') }}
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
                                    <select name="indikator_bidang_id" class="form-select" required>
                                        <option value="">-- Pilih Bidang --</option>
                                        @foreach($allIndikatorBidangs as $bidang)
                                            <option value="{{ $bidang->id }}">{{ $bidang->nama_bidang }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-bold small">Nama Indikator <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="nama_indikator" required
                                        placeholder="Contoh: Pertumbuhan Ekonomi">
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label class="form-label fw-bold small">Satuan</label>
                                    <input type="text" class="form-control" name="satuan" placeholder="Contoh: %">
                                </div>
                                <div class="col-md-2 mb-3">
                                    <div class="form-check pt-2">
                                        <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                            id="isActiveMakro" checked>
                                        <label class="form-check-label fw-bold small" for="isActiveMakro">Aktif?</label>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-9 mb-3">
                                    <label class="form-label fw-bold small">Deskripsi <span
                                            class="text-muted">(opsional)</span></label>
                                    <textarea class="form-control" name="deskripsi" rows="2"
                                        placeholder="Deskripsi singkat indikator..."></textarea>
                                </div>
                                <div class="col-md-3 mb-3 d-flex align-items-end">
                                    <button class="btn btn-orange rounded-pill w-100" type="submit">Tambah Makro</button>
                                </div>
                            </div>
                        </form>
                        <hr class="my-4">
                        <h6 class="fw-bold text-muted small mb-3">Daftar Indikator Makro (Geser untuk mengatur urutan):</h6>
                        @if($indikatorMakros->count() > 0)
                            <div class="table-responsive table-scrollable">
                                <table class="table table-hover align-middle">
                                    <thead class="bg-light">
                                        <tr>
                                            <th style="width: 50px;"></th>
                                            <th style="width: 80px;" class="text-center">Urutan</th>
                                            <th>Nama Indikator</th>
                                            <th>Bidang</th>
                                            <th>Satuan</th>
                                            <th class="text-center">Status</th>
                                            <th class="text-center">Data Digunakan</th>
                                            <th class="text-center">Info User</th>
                                            <th class="text-end" width="15%">Aksi</th>
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
                                                <td>{{ $item->bidang->nama_bidang ?? '-' }}</td>
                                                <td>{{ $item->satuan ?? '-' }}</td>
                                                <td class="text-center">
                                                    @if($item->is_active)
                                                        <span class="badge bg-success rounded-pill">Aktif</span>
                                                    @else
                                                        <span class="badge bg-secondary rounded-pill">Non-Aktif</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-info rounded-pill">{{ $item->indikator_dimensis_count }} kali</span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="me-2" style="font-size: 1.1rem; cursor: pointer;">
                                                        <i class="fas fa-user-plus text-primary" data-bs-toggle="tooltip"
                                                            data-bs-placement="top"
                                                            title="Dibuat oleh: {{ $item->creator->name ?? 'System' }} pada {{ $item->created_at ? $item->created_at->format('d/m/Y H:i') : '-' }}"></i>
                                                    </span>
                                                    @if($item->updater)
                                                    <span style="font-size: 1.1rem; cursor: pointer;">
                                                        <i class="fas fa-user-edit text-success" data-bs-toggle="tooltip"
                                                            data-bs-placement="top"
                                                            title="Diperbarui oleh: {{ $item->updater->name }} pada {{ $item->updated_at ? $item->updated_at->format('d/m/Y H:i') : '-' }}"></i>
                                                    </span>
                                                    @endif
                                                </td>
                                                <td class="text-end">
                                                    <button type="button" class="btn btn-sm btn-outline-orange mb-1 rounded-pill"
                                                        data-bs-toggle="modal" data-bs-target="#modalEditMakro{{ $item->id }}">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    @if($item->indikator_dimensis_count > 0)
                                                        <button type="button" class="btn btn-sm btn-outline-danger mb-1 rounded-pill" disabled title="Sedang digunakan oleh data relasi dimensi"><i class="fas fa-trash"></i></button>
                                                    @else
                                                        <form action="{{ route('indikator-makro.makro.destroy', $item->id) }}"
                                                            method="POST" class="d-inline delete-form">
                                                            @csrf @method('DELETE')
                                                            <button type="submit"
                                                                class="btn btn-sm btn-outline-danger mb-1 rounded-pill"><i
                                                                    class="fas fa-trash"></i></button>
                                                        </form>
                                                    @endif
                                                </td>
                                            </tr>

                                            {{-- Edit Modal --}}
                                            <div class="modal fade" id="modalEditMakro{{ $item->id }}" tabindex="-1"
                                                aria-hidden="true" style="text-align: left;">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content rounded-4 border-0">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title fw-bold">Edit Indikator Makro</h5>
                                                            <button type="button" class="btn-close"
                                                                data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <form action="{{ route('indikator-makro.makro.update', $item->id) }}"
                                                            method="POST">
                                                            @csrf @method('PUT')
                                                            <div class="modal-body">
                                                                <div class="mb-3">
                                                                    <label class="form-label fw-medium">Bidang <span
                                                                            class="text-danger">*</span></label>
                                                                    <select name="indikator_bidang_id" class="form-select" required>
                                                                        <option value="">-- Pilih Bidang --</option>
                                                                        @foreach($allIndikatorBidangs as $bidang)
                                                                            <option value="{{ $bidang->id }}" {{ $item->indikator_bidang_id == $bidang->id ? 'selected' : '' }}>{{ $bidang->nama_bidang }}</option>
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
                                                                    <div class="form-check">
                                                                        <input class="form-check-input" type="checkbox"
                                                                            name="is_active" value="1"
                                                                            id="editIsActiveMakro{{ $item->id }}" {{ $item->is_active ? 'checked' : '' }}>
                                                                        <label class="form-check-label fw-medium"
                                                                            for="editIsActiveMakro{{ $item->id }}">Aktif</label>
                                                                    </div>
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
                            <div class="mt-3">
                                {{ $indikatorMakros->appends(['tab' => 'makro'])->links('pagination::bootstrap-5') }}
                            </div>
                        @else
                            <div class="text-center text-muted p-3 bg-light rounded">
                                <small>Belum ada data indikator makro.</small>
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
                                        placeholder="Contoh: Persentase">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <button class="btn btn-orange rounded-pill w-100" type="submit">Tambah Dimensi</button>
                                </div>
                            </div>
                        </form>
                        <hr class="my-4">
                        <h6 class="fw-bold text-muted small mb-3">Daftar Dimensi:</h6>
                        @if($dimensis->count() > 0)
                            <div class="table-responsive table-scrollable">
                                <table class="table table-hover align-middle">
                                    <thead class="bg-light">
                                        <tr>
                                            <th width="10%">No</th>
                                            <th>Nama Dimensi</th>
                                            <th class="text-center">Data Digunakan</th>
                                            <th class="text-center">Info User</th>
                                            <th class="text-end" width="15%">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($dimensis as $idx => $item)
                                            <tr>
                                                <td>{{ ($dimensis->currentPage() - 1) * $dimensis->perPage() + $idx + 1 }}</td>
                                                <td class="fw-bold">{{ $item->nama_dimensi }}</td>
                                                <td class="text-center">
                                                    <span class="badge bg-info rounded-pill">{{ $item->indikator_dimensis_count }} kali</span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="me-2" style="font-size: 1.1rem; cursor: pointer;">
                                                        <i class="fas fa-user-plus text-primary" data-bs-toggle="tooltip"
                                                            data-bs-placement="top"
                                                            title="Dibuat oleh: {{ $item->creator->name ?? 'System' }} pada {{ $item->created_at ? $item->created_at->format('d/m/Y H:i') : '-' }}"></i>
                                                    </span>
                                                    @if($item->updater)
                                                    <span style="font-size: 1.1rem; cursor: pointer;">
                                                        <i class="fas fa-user-edit text-success" data-bs-toggle="tooltip"
                                                            data-bs-placement="top"
                                                            title="Diperbarui oleh: {{ $item->updater->name }} pada {{ $item->updated_at ? $item->updated_at->format('d/m/Y H:i') : '-' }}"></i>
                                                    </span>
                                                    @endif
                                                </td>
                                                <td class="text-end">
                                                    <button type="button" class="btn btn-sm btn-outline-orange mb-1 rounded-pill"
                                                        data-bs-toggle="modal" data-bs-target="#modalEditDimensi{{ $item->id }}">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    @if($item->indikator_dimensis_count > 0)
                                                        <button type="button" class="btn btn-sm btn-outline-danger mb-1 rounded-pill" disabled title="Sedang digunakan oleh data relasi indikator"><i class="fas fa-trash"></i></button>
                                                    @else
                                                        <form action="{{ route('indikator-makro.dimensi.destroy', $item->id) }}"
                                                            method="POST" class="d-inline delete-form">
                                                            @csrf @method('DELETE')
                                                            <button type="submit"
                                                                class="btn btn-sm btn-outline-danger mb-1 rounded-pill"><i
                                                                    class="fas fa-trash"></i></button>
                                                        </form>
                                                    @endif
                                                </td>
                                            </tr>

                                            {{-- Edit Modal --}}
                                            <div class="modal fade" id="modalEditDimensi{{ $item->id }}" tabindex="-1"
                                                aria-hidden="true" style="text-align: left;">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content rounded-4 border-0">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title fw-bold">Edit Dimensi</h5>
                                                            <button type="button" class="btn-close"
                                                                data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <form action="{{ route('indikator-makro.dimensi.update', $item->id) }}"
                                                            method="POST">
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
                            <div class="mt-3">
                                {{ $dimensis->appends(['tab' => 'dimensi'])->links('pagination::bootstrap-5') }}
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
                                    <select name="indikator_makro_id" class="form-select" required>
                                        <option value="">-- Pilih Indikator --</option>
                                        @foreach($allIndikatorMakros as $makro)
                                            <option value="{{ $makro->id }}">{{ $makro->nama_indikator }}</option>
                                        @endforeach
                                    </select>
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

                        @if($errors->has('indikator_dimensi'))
                            <div class="alert alert-danger alert-dismissible fade show rounded-3 py-2" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                {{ $errors->first('indikator_dimensi') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <hr class="my-4">
                        <h6 class="fw-bold text-muted small mb-3">Daftar Indikator Dimensi:</h6>
                        @if($indikatorDimensis->count() > 0)
                            <div class="table-responsive table-scrollable">
                                <table class="table table-hover align-middle">
                                    <thead class="bg-light">
                                        <tr>
                                            <th style="width: 50px;"></th>
                                            <th style="width: 80px;" class="text-center">Urutan</th>
                                            <th>Indikator Makro</th>
                                            <th>Dimensi</th>
                                            <th class="text-center">Data Digunakan</th>
                                            <th class="text-center">Info User</th>
                                            <th class="text-end" width="15%">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="sortable-ind-dimensi">
                                        @foreach($indikatorDimensis as $idx => $item)
                                            <tr data-id="{{ $item->id }}">
                                                <td class="text-center" style="cursor: grab;">
                                                    <i class="fas fa-grip-vertical text-muted"></i>
                                                </td>
                                                <td class="text-center sortable-urutan fw-bold">{{ ($indikatorDimensis->currentPage() - 1) * $indikatorDimensis->perPage() + $idx + 1 }}</td>
                                                <td class="fw-bold">{{ $item->indikatorMakro->nama_indikator ?? '-' }}</td>
                                                <td class="fw-bold">{{ $item->dimensi->nama_dimensi ?? '-' }}</td>
                                                <td class="text-center">
                                                    <span class="badge bg-info rounded-pill">{{ $item->nilai_indikator_makros_count }} kali</span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="me-2" style="font-size: 1.1rem; cursor: pointer;">
                                                        <i class="fas fa-user-plus text-primary" data-bs-toggle="tooltip"
                                                            data-bs-placement="top"
                                                            title="Dibuat oleh: {{ $item->creator->name ?? 'System' }} pada {{ $item->created_at ? $item->created_at->format('d/m/Y H:i') : '-' }}"></i>
                                                    </span>
                                                    @if($item->updater)
                                                    <span style="font-size: 1.1rem; cursor: pointer;">
                                                        <i class="fas fa-user-edit text-success" data-bs-toggle="tooltip"
                                                            data-bs-placement="top"
                                                            title="Diperbarui oleh: {{ $item->updater->name }} pada {{ $item->updated_at ? $item->updated_at->format('d/m/Y H:i') : '-' }}"></i>
                                                    </span>
                                                    @endif
                                                </td>
                                                <td class="text-end">
                                                    @if($item->nilai_indikator_makros_count > 0)
                                                        <button type="button" class="btn btn-sm btn-outline-danger mb-1 rounded-pill" disabled title="Sedang digunakan oleh data nilai"><i class="fas fa-trash"></i></button>
                                                    @else
                                                        <form
                                                            action="{{ route('indikator-makro.indikator-dimensi.destroy', $item->id) }}"
                                                            method="POST" class="d-inline delete-form">
                                                            @csrf @method('DELETE')
                                                            <button type="submit"
                                                                class="btn btn-sm btn-outline-danger mb-1 rounded-pill"><i
                                                                    class="fas fa-trash"></i></button>
                                                        </form>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-3">
                                {{ $indikatorDimensis->appends(['tab' => 'indikator-dimensi'])->links('pagination::bootstrap-5') }}
                            </div>
                        @else
                            <div class="text-center text-muted p-3 bg-light rounded">
                                <small>Belum ada relasi indikator dimensi.</small>
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

            // 2. Drag & Drop Indikator Makro
            const sortableMakroEl = document.getElementById('sortable-makro');
            if (sortableMakroEl) {
                new Sortable(sortableMakroEl, {
                    animation: 150,
                    handle: '.fa-grip-vertical',
                    ghostClass: 'table-warning',
                    onEnd: function () {
                        const order = [];
                        sortableMakroEl.querySelectorAll('tr[data-id]').forEach(function (row) {
                            order.push(row.getAttribute('data-id'));
                        });

                        fetch('{{ route("indikator-makro.makro.reorder") }}', {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ order: order })
                        }).then(function (response) {
                            if (response.ok) {
                                sortableMakroEl.querySelectorAll('.sortable-urutan').forEach(function (cell, i) {
                                    cell.textContent = i + 1;
                                });
                            }
                        });
                    }
                });
            }

            // 3. Drag & Drop Indikator Bidang
            const sortableBidangEl = document.getElementById('sortable-bidang');
            if (sortableBidangEl) {
                new Sortable(sortableBidangEl, {
                    animation: 150,
                    handle: '.fa-grip-vertical',
                    ghostClass: 'table-warning',
                    onEnd: function () {
                        const order = [];
                        sortableBidangEl.querySelectorAll('tr[data-id]').forEach(function (row) {
                            order.push(row.getAttribute('data-id'));
                        });

                        fetch('{{ route("indikator-makro.bidang.reorder") }}', {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ order: order })
                        }).then(function (response) {
                            if (response.ok) {
                                sortableBidangEl.querySelectorAll('.sortable-urutan').forEach(function (cell, i) {
                                    cell.textContent = i + 1;
                                });
                            }
                        });
                    }
                });
            }

            // 3b. Drag & Drop Indikator Dimensi
            const sortableIndDimensiEl = document.getElementById('sortable-ind-dimensi');
            if (sortableIndDimensiEl) {
                new Sortable(sortableIndDimensiEl, {
                    animation: 150,
                    handle: '.fa-grip-vertical',
                    ghostClass: 'table-warning',
                    onEnd: function () {
                        const order = [];
                        sortableIndDimensiEl.querySelectorAll('tr[data-id]').forEach(function (row) {
                            order.push(row.getAttribute('data-id'));
                        });

                        fetch('{{ route("indikator-makro.indikator-dimensi.reorder") }}', {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ order: order })
                        }).then(function (response) {
                            if (response.ok) {
                                sortableIndDimensiEl.querySelectorAll('.sortable-urutan').forEach(function (cell, i) {
                                    cell.textContent = i + 1;
                                });
                            }
                        });
                    }
                });
            }

            // 4. Toggle Periode Status Switch
            document.querySelectorAll('.toggle-periode-status').forEach(function (toggle) {
                toggle.addEventListener('change', function () {
                    const id = this.getAttribute('data-id');
                    const url = '{{ route("indikator-makro.periode.toggle", ":id") }}'.replace(':id', id);

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
                    const url = '{{ route("indikator-makro.bidang.toggle", ":id") }}'.replace(':id', id);

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
        });
    </script>
@endpush