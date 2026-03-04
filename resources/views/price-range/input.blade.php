@extends('layouts.admin')

@section('title', 'Input Rentang Harga - BPS Kalbar')

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
    <style>
        .ts-control {
            border: none !important;
            background-color: #f8f9fa !important;
            padding: 0.5rem 0.75rem !important;
            border-radius: 8px !important;
        }

        .ts-wrapper.single .ts-control {
            background-image: none !important;
        }
    </style>
@endpush

@section('content')
    <div class="fade-in-up">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
            <div>
                <h2 class="fw-bold mb-1" style="color: var(--bps-orange);">Kelola Komoditas</h2>
                <p class="text-muted mb-0">Kelola master kategori dan data rentang harga komoditas</p>
            </div>
            <div class="mt-3 mt-md-0">
                <a href="{{ route('price-range.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Kembali
                </a>
            </div>
        </div>

        <!-- Tabs Navigation -->
        <ul class="nav nav-pills mb-4" id="pills-tab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active rounded-pill px-4" id="pills-kategori-tab" data-bs-toggle="pill"
                    data-bs-target="#pills-kategori" type="button" role="tab">
                    <i class="fas fa-tags me-2"></i>Master Kategori
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-pill px-4" id="pills-input-tab" data-bs-toggle="pill"
                    data-bs-target="#pills-input" type="button" role="tab">
                    <i class="fas fa-edit me-2"></i>Input Nama Komoditas
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-pill px-4" id="pills-rh-settings-tab" data-bs-toggle="pill"
                    data-bs-target="#pills-rh-settings" type="button" role="tab">
                    <i class="fas fa-calendar-alt me-2"></i>Pengaturan RH
                </button>
            </li>
        </ul>

        <div class="tab-content" id="pills-tabContent">
            <!-- Tab 1: Master Kategori -->
            <div class="tab-pane fade show active" id="pills-kategori" role="tabpanel">
                <div class="row">
                    <div class="col-md-4 mb-4">
                        <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                            <div class="card-header bg-white py-3 border-bottom-0">
                                <h5 class="fw-bold mb-0">Tambah Kategori</h5>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('kategori-komoditas.store') }}" method="POST">
                                    @csrf
                                    <div class="mb-3">
                                        <label class="form-label text-muted small fw-bold text-uppercase">Nama
                                            Kategori</label>
                                        <input type="text" name="nama_kategori" class="form-control"
                                            placeholder="Contoh: PADI-PADIAN" required>
                                    </div>
                                    <button type="submit" class="btn text-white w-100 fw-medium"
                                        style="background-color: var(--bps-blue);">
                                        <i class="fas fa-plus me-1"></i> Simpan
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                            <div class="card-header bg-white py-3 border-bottom-0">
                                <h5 class="fw-bold mb-0">Daftar Kategori Komoditas</h5>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="ps-4 border-0" style="width: 60px;">No</th>
                                            <th class="border-0">Nama Kategori</th>
                                            <th class="border-0">Dibuat Oleh</th>
                                            <th class="border-0">Di Update Oleh</th>
                                            <th class="text-center border-0">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="border-top-0">
                                        @forelse($kategori as $index => $item)
                                            <tr>
                                                <td class="ps-4 text-muted">{{ $index + 1 }}</td>
                                                <td class="fw-medium text-uppercase">
                                                    {{ $item->nama_kategori }}
                                                    @if($item->komoditas_count > 0)
                                                        <span class="badge rounded-pill bg-blue-faded text-blue border ms-1"
                                                            style="font-size: 0.65rem;">
                                                            {{ $item->komoditas_count }} Komoditas
                                                        </span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge bg-light text-dark border">
                                                        <i class="fas fa-user-edit me-1 text-primary"></i>
                                                        {{ $item->userAdd->name ?? 'Admin' }}
                                                        <br>
                                                        <small class="text-muted fw-normal" style="font-size: 0.75rem;">
                                                            {{ $item->created_at->format('d/m/Y H:i') }}
                                                        </small>
                                                    </span>
                                                </td>
                                                <td>
                                                    @if($item->userUpdate)
                                                        <span class="badge bg-light text-dark border">
                                                            <i class="fas fa-user-check me-1 text-success"></i>
                                                            {{ $item->userUpdate->name }}
                                                            <br>
                                                            <small class="text-muted fw-normal" style="font-size: 0.75rem;">
                                                                {{ $item->updated_at->format('d/m/Y H:i') }}
                                                            </small>
                                                        </span>
                                                    @else
                                                        <span class="text-muted small">Belum pernah diupdate</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    <button class="btn btn-sm btn-outline-warning border-0 btn-edit-kategori"
                                                        data-bs-toggle="modal" data-bs-target="#modalEditKategori"
                                                        data-id="{{ $item->id }}" data-nama="{{ $item->nama_kategori }}">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    @if($item->komoditas_count == 0)
                                                        <form action="{{ route('kategori-komoditas.destroy', $item->id) }}"
                                                            method="POST" class="d-inline form-delete">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="button"
                                                                class="btn btn-sm btn-outline-danger border-0 btn-delete">
                                                                <i class="fas fa-trash-alt"></i>
                                                            </button>
                                                        </form>
                                                    @else
                                                        <button type="button"
                                                            class="btn btn-sm btn-link text-muted border-0 opacity-50"
                                                            title="Kategori tidak dapat dihapus karena masih memiliki data komoditas"
                                                            disabled>
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center py-5 text-muted"> Belum ada data kategori.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 2: Master Komoditas (Turunan) -->
            <div class="tab-pane fade" id="pills-input" role="tabpanel">
                <div class="row g-4">
                    <!-- Left Column: Input Form -->
                    <div class="col-lg-4">
                        <form action="{{ route('komoditas.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="active_tab" id="active_tab_input" value="pills-input">
                            <input type="hidden" name="input_mode" id="input_mode_input" value="mode-paste">
                            <!-- Filters -->
                            <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="fw-bold mb-0 text-uppercase text-muted"
                                            style="font-size: 0.8rem; letter-spacing: 0.5px;">1. Pilih Kategori</h6>
                                        @php
                                            $activeRh = $rhTahun->where('is_active', true)->first();
                                        @endphp
                                        @if($activeRh)
                                            <span class="badge bg-success-faded text-success border small">
                                                <i class="fas fa-calendar-check me-1"></i> Tahun Aktif: {{ $activeRh->tahun }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="mb-0">
                                        <label class="form-label small fw-medium">Kategori Utama</label>
                                        <select id="select_kategori_filter" name="kategori_id"
                                            class="form-select bg-light border-0" required>
                                            <option selected disabled value="">-- Pilih Kategori --</option>
                                            @foreach($kategori as $kat)
                                                <option value="{{ $kat->id }}" {{ session('last_kategori_id') == $kat->id ? 'selected' : '' }}>{{ $kat->nama_kategori }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Input Area -->
                            <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                                <div
                                    class="card-header bg-white py-2 border-bottom-0 d-flex justify-content-between align-items-center">
                                    <h6 class="fw-bold mb-0 text-uppercase text-muted"
                                        style="font-size: 0.75rem; letter-spacing: 0.5px;">2. Nama Komoditas & Satuan</h6>
                                    <div class="nav nav-pills small" id="inputModeTab" role="tablist"
                                        style="background: #f8f9fa; padding: 2px; border-radius: 8px;">
                                        <button class="nav-link active py-1 px-3 border-0" id="mode-paste-tab"
                                            data-bs-toggle="pill" data-bs-target="#mode-paste" type="button" role="tab"
                                            style="font-size: 0.7rem;">
                                            <i class="fas fa-file-excel me-1"></i> Paste Excel
                                        </button>
                                        <button class="nav-link py-1 px-3 border-0" id="mode-manual-tab"
                                            data-bs-toggle="pill" data-bs-target="#mode-manual" type="button" role="tab"
                                            style="font-size: 0.7rem;">
                                            <i class="fas fa-keyboard me-1"></i> Manual
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body p-0">
                                    <div class="tab-content" id="inputModeTabContent">
                                        <!-- Paste Mode -->
                                        <div class="tab-pane fade show active" id="mode-paste" role="tabpanel">
                                            <div class="p-3">
                                                <textarea id="textarea_komoditas" name="raw_data"
                                                    class="form-control fw-mono border-0 bg-light" rows="15"
                                                    placeholder="Paste dari Excel (Nama [tab] Satuan [tab] Batas Selisih)...&#10;Contoh:&#10;Beras lokal	Kg	5000&#10;Jagung basah	Kg	2000"
                                                    style="font-family: 'Inter', sans-serif; font-size: 0.85rem; resize: none;"></textarea>
                                            </div>
                                        </div>
                                        <!-- Manual Mode -->
                                        <div class="tab-pane fade" id="mode-manual" role="tabpanel">
                                            <div class="p-3">
                                                <div style="max-height: 400px; overflow-y: auto;">
                                                    <table class="table table-sm table-borderless align-middle mb-0">
                                                        <thead>
                                                            <tr class="text-muted small">
                                                                <th style="width: 45%;">Nama</th>
                                                                <th style="width: 25%;">Satuan</th>
                                                                <th style="width: 25%;">Batas Selisih</th>
                                                                <th style="width: 5%;" class="text-center"></th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="manual_input_body">
                                                            <!-- Initial Row -->
                                                            <tr class="manual-row">
                                                                <td><input type="text"
                                                                        class="form-control form-control-sm bg-light border-0 manual-name"
                                                                        placeholder="Nama Komoditas"></td>
                                                                <td><input type="text"
                                                                        class="form-control form-control-sm bg-light border-0 manual-unit"
                                                                        placeholder="Satuan (e.g. Kg)"></td>
                                                                <td><input type="number"
                                                                        class="form-control form-control-sm bg-light border-0 manual-batas"
                                                                        placeholder="Batas Selisih (Rp)"></td>
                                                                <td class="text-center"><button type="button"
                                                                        class="btn btn-sm btn-link text-danger btn-remove-row p-0"><i
                                                                            class="fas fa-times"></i></button></td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <button type="button" id="btn_add_manual_row"
                                                    class="btn btn-sm btn-link text-decoration-none mt-2 p-0">
                                                    <i class="fas fa-plus-circle me-1"></i>Tambah Baris
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer bg-white border-top-0 py-3">
                                    <div class="d-flex gap-2">
                                        <button type="submit" id="btn_submit_komoditas"
                                            class="btn btn-primary flex-grow-1 fw-bold py-2 shadow-sm"
                                            style="background-color: var(--bps-orange); border: none;">
                                            <i class="fas fa-save me-2"></i>Simpan Data
                                        </button>
                                        <button type="button" id="btn_clear_komoditas"
                                            class="btn btn-outline-danger fw-bold py-2 shadow-sm" style="display: none;">
                                            <i class="fas fa-trash me-2"></i>Hapus Semua
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Right Column: Data Table -->
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
                            <div
                                class="card-header bg-white py-3 border-bottom-0 d-flex justify-content-between align-items-center">
                                <h5 class="fw-bold mb-0" id="komoditas_table_title">Daftar Komoditas</h5>
                            </div>
                            <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light sticky-top" style="z-index: 1;">
                                        <tr>
                                            <th class="ps-4 border-0" style="width: 60px;">No</th>
                                            <th class="border-0">Nama Komoditas</th>
                                            <th class="border-0 text-center">Satuan</th>
                                            <th class="border-0 text-center">Batas Selisih</th>
                                            <th class="border-0">Admin</th>
                                            <th class="text-center border-0">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="border-top-0" id="table_body_komoditas">
                                        <tr>
                                            <td colspan="5" class="text-center py-5 text-muted">
                                                <i class="fas fa-info-circle me-1"></i> Silakan pilih kategori untuk
                                                menampilkan daftar komoditas.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 3: Pengaturan RH (Admin Utama) -->
            <div class="tab-pane fade" id="pills-rh-settings" role="tabpanel">
                <div class="row g-4">
                    <!-- Left Column: Settings Form -->
                    <div class="col-lg-4">


                        <!-- Add Year -->
                        <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                            <div class="card-header bg-white py-3 border-bottom-0">
                                <h6 class="fw-bold mb-0 text-uppercase text-muted" style="font-size: 0.75rem;">Tambah Tahun
                                    RH (MASTER)</h6>
                            </div>
                            <div class="card-body pt-0">
                                <form action="{{ route('rh-tahun.store') }}" method="POST">
                                    @csrf
                                    <div class="mb-3">
                                        <label class="form-label small fw-medium">Tahun</label>
                                        <input type="number" name="tahun" class="form-control" placeholder="Contoh: 2025"
                                            required min="2000" max="2099">
                                    </div>
                                    <button type="submit" class="btn text-white w-100 fw-medium"
                                        style="background-color: var(--bps-blue);">
                                        <i class="fas fa-plus me-1"></i> Tambah Tahun
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Add Revision Header -->
                        <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                            <div class="card-header bg-white py-3 border-bottom-0">
                                <h6 class="fw-bold mb-0 text-uppercase text-muted" style="font-size: 0.75rem;">Tambah Header
                                    Perubahan</h6>
                            </div>
                            <div class="card-body pt-0">
                                <form action="{{ route('rh-perubahan.store') }}" method="POST">
                                    @csrf
                                    <div class="mb-3">
                                        <label class="form-label small fw-medium">Pilih Tahun RH</label>
                                        <select name="rh_tahun_id" id="select_rh_tahun_perubahan" class="form-select"
                                            placeholder="Cari atau pilih tahun..." required>
                                            <option value="">-- Pilih Tahun --</option>
                                            @foreach($rhTahun as $t)
                                                <option value="{{ $t->id }}" data-tahun="{{ $t->tahun }}">{{ $t->tahun }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-medium">Tanggal Perubahan</label>
                                        <input type="date" name="tanggal_perubahan" id="input-tanggal-header"
                                            class="form-control" required readonly
                                            style="background-color: #f8f9fa; pointer-events: none;">
                                        <small class="text-muted" style="font-size: 0.7rem;" id="date_hint_header">Pilih
                                            tahun
                                            terlebih dahulu.</small>
                                    </div>
                                    <button type="submit" class="btn text-white w-100 fw-medium"
                                        style="background-color: var(--bps-orange);">
                                        <i class="fas fa-save me-1"></i> Simpan Header
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Settings Data -->
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                            <div class="card-header bg-white py-3 border-bottom-0">
                                <h5 class="fw-bold mb-0">Daftar Tahun & Perubahan</h5>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="ps-4 border-0" style="width: 120px;">Tahun</th>

                                            <th class="border-0">Status</th>
                                            <th class="border-0">
                                                Daftar Perubahan (Header)
                                                <button type="button"
                                                    class="btn btn-xs btn-link p-0 ms-1 text-muted btn-sort-perubahan"
                                                    title="Urutkan Tanggal">
                                                    <i class="fas fa-sort-amount-down"></i>
                                                </button>
                                            </th>
                                            <th class="text-center border-0">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="border-top-0">
                                        @forelse($rhTahun as $tahun)
                                            <tr>
                                                <td class="ps-4">
                                                    <span class="fs-5 fw-bold text-dark">{{ $tahun->tahun }}</span>
                                                    <br>
                                                    <span class="text-muted" style="font-size: 0.7rem;">
                                                        <i class="fas fa-user-edit me-1"></i>
                                                        {{ $tahun->userAdd->name ?? 'Admin' }}
                                                    </span>
                                                </td>

                                                <td>
                                                    <form action="{{ route('rh-tahun.toggle-active', $tahun->id) }}"
                                                        method="POST" class="d-inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        @if($tahun->is_active)
                                                            <button type="submit" class="btn btn-sm btn-success rounded-pill px-3">
                                                                <i class="fas fa-check-circle me-1"></i> Aktif
                                                            </button>
                                                        @else
                                                            <button type="submit"
                                                                class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                                                                Set Aktif
                                                            </button>
                                                        @endif
                                                    </form>
                                                </td>
                                                <td>
                                                    @if($tahun->perubahanHeaders->count() > 0)
                                                        <ul class="list-unstyled mb-0 small perubahan-list">
                                                            @foreach($tahun->perubahanHeaders->sortByDesc('tanggal_perubahan') as $rev)
                                                                <li class="mb-2 d-flex justify-content-between align-items-center bg-light p-2 rounded border-start border-4 border-orange perubahan-item"
                                                                    data-date="{{ $rev->tanggal_perubahan }}">
                                                                    <div>
                                                                        @php
                                                                            $revDate = \Carbon\Carbon::parse($rev->tanggal_perubahan);
                                                                        @endphp
                                                                        <span class="fw-bold text-dark">Perubahan RH
                                                                            {{ $revDate->translatedFormat('j F Y') }}</span>
                                                                        <br>
                                                                        <span class="text-muted" style="font-size: 0.75rem;">
                                                                            <i class="fas fa-user-edit me-1"></i>
                                                                            {{ $rev->userAdd->name ?? 'Admin' }}
                                                                        </span>
                                                                    </div>
                                                                    <div class="d-flex gap-1">
                                                                        <button type="button"
                                                                            class="btn btn-sm text-warning border-0 btn-edit-rh-perubahan"
                                                                            {{ $rev->details_with_values_count > 0 ? 'disabled' : '' }}
                                                                            data-bs-toggle="modal"
                                                                            data-bs-target="#modalEditRhPerubahan"
                                                                            data-id="{{ $rev->id }}"
                                                                            data-tanggal="{{ $rev->tanggal_perubahan }}"
                                                                            data-tahun="{{ $tahun->tahun }}">
                                                                            <i class="fas fa-edit"></i>
                                                                        </button>
                                                                        <form action="{{ route('rh-perubahan.destroy', $rev->id) }}"
                                                                            method="POST" class="form-delete">
                                                                            @csrf
                                                                            @method('DELETE')
                                                                            <button type="button"
                                                                                class="btn btn-sm text-danger border-0 btn-delete" {{ $rev->details_with_values_count > 0 ? 'disabled' : '' }}
                                                                                title="{{ $rev->details_with_values_count > 0 ? 'Hapus semua rincian nilai dulu' : 'Hapus Header' }}">
                                                                                <i class="fas fa-times"></i>
                                                                            </button>
                                                                        </form>
                                                                    </div>
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    @else
                                                        <span class="text-muted italic small">Belum ada header perubahan</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    <div class="d-flex justify-content-center gap-1">
                                                        <button type="button"
                                                            class="btn btn-sm btn-outline-warning border-0 btn-edit-rh-tahun" {{ ($tahun->perubahanHeaders->count() > 0 || $tahun->perubahan_details_with_values_count > 0) ? 'disabled' : '' }} data-bs-toggle="modal" data-bs-target="#modalEditRhTahun"
                                                            data-id="{{ $tahun->id }}" data-tahun="{{ $tahun->tahun }}"
                                                            data-selisih="{{ (int) $tahun->batas_selisih_harga }}">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <form action="{{ route('rh-tahun.destroy', $tahun->id) }}" method="POST"
                                                            class="form-delete">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="button"
                                                                class="btn btn-sm btn-outline-danger border-0 btn-delete" {{ ($tahun->perubahanHeaders->count() > 0 || $tahun->perubahan_details_with_values_count > 0) ? 'disabled' : '' }}
                                                                title="{{ ($tahun->perubahanHeaders->count() > 0 || $tahun->perubahan_details_with_values_count > 0) ? 'Hapus semua header/detail perubahan dulu' : 'Hapus Tahun' }}">
                                                                <i class="fas fa-trash-alt"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center py-5 text-muted">Belum ada data tahun RH.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Modal Edit Kategori -->
    <div class="modal fade" id="modalEditKategori" tabindex="-1" aria-labelledby="modalEditKategoriLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom-0">
                    <h5 class="modal-title fw-bold" id="modalEditKategoriLabel">Edit Nama Kategori</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formEditKategori" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold text-uppercase">Nama Kategori</label>
                            <input type="text" name="nama_kategori" id="edit_nama_kategori" class="form-control"
                                placeholder="Contoh: PADI-PADIAN" required>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0">
                        <button type="button" class="btn btn-light fw-medium" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn text-white fw-medium" style="background-color: var(--bps-orange);">
                            <i class="fas fa-save me-1"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Edit Rh Tahun -->
    <div class="modal fade" id="modalEditRhTahun" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom-0">
                    <h5 class="modal-title fw-bold">Edit Tahun</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formEditRhTahun" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small fw-medium">Tahun</label>
                            <input type="number" name="tahun" id="edit_rh_tahun" class="form-control" required min="2000"
                                max="2099">
                        </div>
                    </div>
                    <div class="modal-footer border-top-0">
                        <button type="button" class="btn btn-light fw-medium" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn text-white fw-medium" style="background-color: var(--bps-orange);">
                            <i class="fas fa-save me-1"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Edit Rh Perubahan -->
    <div class="modal fade" id="modalEditRhPerubahan" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom-0">
                    <h5 class="modal-title fw-bold">Edit Tanggal Perubahan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formEditRhPerubahan" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small fw-medium">Tanggal Perubahan</label>
                            <input type="date" name="tanggal_perubahan" id="edit_rh_perubahan_tanggal" class="form-control"
                                required>
                            <small class="text-primary small" id="edit_rh_perubahan_hint"></small>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0">
                        <button type="button" class="btn btn-light fw-medium" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn text-white fw-medium" style="background-color: var(--bps-orange);">
                            <i class="fas fa-save me-1"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <style>
        .nav-pills .nav-link {
            color: #64748b;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .nav-pills .nav-link.active {
            background-color: var(--bps-blue);
            color: white;
            box-shadow: 0 4px 6px -1px rgba(0, 147, 221, 0.3);
        }

        .badge {
            text-align: left;
            line-height: 1.4;
            padding: 0.5rem 0.75rem;
        }

        .bg-blue-faded {
            background-color: rgba(0, 147, 221, 0.1);
        }

        .bg-success-faded {
            background-color: rgba(40, 167, 69, 0.1);
        }

        .text-blue {
            color: var(--bps-blue);
        }

        #inputModeTab .nav-link {
            color: #64748b;
            border-radius: 6px;
        }

        #inputModeTab .nav-link.active {
            background: white;
            color: var(--bps-blue);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .manual-row:hover .btn-remove-row {
            opacity: 1;
        }

        .btn-remove-row {
            opacity: 0.4;
            transition: opacity 0.2s;
        }
    </style>

    <!-- Modal Edit Kategori -->
    <div class="modal fade" id="modalEditKategori" tabindex="-1" aria-labelledby="modalEditKategoriLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom-0">
                    <h5 class="modal-title fw-bold" id="modalEditKategoriLabel">Edit Nama Kategori</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formEditKategori" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold text-uppercase">Nama Kategori</label>
                            <input type="text" name="nama_kategori" id="edit_nama_kategori" class="form-control"
                                placeholder="Contoh: PADI-PADIAN" required>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0">
                        <button type="button" class="btn btn-light fw-medium" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn text-white fw-medium" style="background-color: var(--bps-orange);">
                            <i class="fas fa-save me-1"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Edit Rh Tahun -->
    <div class="modal fade" id="modalEditRhTahun" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom-0">
                    <h5 class="modal-title fw-bold">Edit Tahun</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formEditRhTahun" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small fw-medium">Tahun</label>
                            <input type="number" name="tahun" id="edit_rh_tahun" class="form-control" required min="2000"
                                max="2099">
                        </div>
                    </div>
                    <div class="modal-footer border-top-0">
                        <button type="button" class="btn btn-light fw-medium" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn text-white fw-medium" style="background-color: var(--bps-orange);">
                            <i class="fas fa-save me-1"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Edit Rh Perubahan -->
    <div class="modal fade" id="modalEditRhPerubahan" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom-0">
                    <h5 class="modal-title fw-bold">Edit Tanggal Perubahan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formEditRhPerubahan" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small fw-medium">Tanggal Perubahan</label>
                            <input type="date" name="tanggal_perubahan" id="edit_rh_perubahan_tanggal" class="form-control"
                                required>
                            <small class="text-primary small" id="edit_rh_perubahan_hint"></small>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0">
                        <button type="button" class="btn btn-light fw-medium" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn text-white fw-medium" style="background-color: var(--bps-orange);">
                            <i class="fas fa-save me-1"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Edit Komoditas -->
    <div class="modal fade" id="modalEditKomoditas" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom-0">
                    <h5 class="modal-title fw-bold">Edit Komoditas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formEditKomoditas" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small fw-medium">Nama Komoditas</label>
                            <input type="text" name="nama_komoditas" id="edit_komoditas_nama" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-medium">Satuan</label>
                            <input type="text" name="satuan" id="edit_komoditas_satuan" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-medium">Batas Selisih Harga (Rp)</label>
                            <input type="number" name="batas_selisih_harga" id="edit_komoditas_batas" class="form-control">
                            <small class="text-muted" style="font-size: 0.7rem;">Kosongkan jika ingin mengikuti default
                                Kategori/Tahun (Jika ada).</small>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0">
                        <button type="button" class="btn btn-light fw-medium" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn text-white fw-medium" style="background-color: var(--bps-orange);">
                            <i class="fas fa-save me-1"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Store latest dates for each RH year to restrict new header dat       es
        window.latestRhDates = {
            @foreach($rhTahun as $t)
                "{{ $t->id }}": "{{ $t->perubahanHeaders->last()->tanggal_perubahan ?? '' }}",
            @endforeach
                                    };

        document.addEventListener("DOMContentLoaded", function () {
            // Edit Kategori Modal Populating
            const modalEditKategoriEl = document.getElementById('modalEditKategori');
            if (modalEditKategoriEl) {
                modalEditKategoriEl.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget;
                    const id = button.getAttribute('data-id');
                    const nama = button.getAttribute('data-nama');

                    this.querySelector('#edit_nama_kategori').value = nama;
                    this.querySelector('#formEditKategori').action = `/kategori-komoditas/${id}`;
                });
            }

            // Delete Confirmation Logic
            document.addEventListener('click', function (event) {
                if (event.target.closest('.btn-delete')) {
                    const button = event.target.closest('.btn-delete');
                    const form = button.closest('.form-delete');

                    Swal.fire({
                        title: 'Apakah Anda yakin?',
                        text: "Data yang dihapus tidak dapat dikembalikan!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Ya, hapus!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                }
            });

            // Master Komoditas Logic
            const selectKategori = document.getElementById('select_kategori_filter');
            const tableBodyKomoditas = document.getElementById('table_body_komoditas');
            const komoditasTableTitle = document.getElementById('komoditas_table_title');
            const btnClearKomoditas = document.getElementById('btn_clear_komoditas');
            const textareaKomoditas = document.getElementById('textarea_komoditas');
            const manualInputBody = document.getElementById('manual_input_body');
            const btnAddManualRow = document.getElementById('btn_add_manual_row');

            // Function to add a manual row
            // Function to add a manual row
            function addManualRow(name = '', unit = '', batas = '') {
                const tr = document.createElement('tr');
                tr.className = 'manual-row';
                tr.innerHTML = `
                                                                                                                    <td><input type="text" class="form-control form-control-sm bg-light border-0 manual-name" placeholder="Nama Komoditas" value="${name}"></td>
                                                                                                                    <td><input type="text" class="form-control form-control-sm bg-light border-0 manual-unit" placeholder="Satuan (e.g. Kg)" value="${unit}"></td>
                                                                                                                    <td><input type="number" class="form-control form-control-sm bg-light border-0 manual-batas" placeholder="Batas Selisih (Rp)" value="${batas}"></td>
                                                                                                                    <td class="text-center"><button type="button" class="btn btn-sm btn-link text-danger btn-remove-row p-0"><i class="fas fa-times"></i></button></td>
                                                                                                                `;
                manualInputBody.appendChild(tr);
            }

            // Sync Manual -> Textarea
            function syncManualToTextarea() {
                const rows = document.querySelectorAll('.manual-row');
                const data = Array.from(rows).map(row => {
                    const name = row.querySelector('.manual-name').value.trim();
                    const unit = row.querySelector('.manual-unit').value.trim();
                    const batas = row.querySelector('.manual-batas').value.trim();
                    // Join with tabs: Name \t Unit \t Batas
                    return name ? `${name}\t${unit || 'Kg'}\t${batas}` : null;
                }).filter(Boolean);
                textareaKomoditas.value = data.join('\n');
            }

            // Sync Textarea -> Manual
            function syncTextareaToManual() {
                const lines = textareaKomoditas.value.trim().split('\n');
                manualInputBody.innerHTML = '';
                if (lines.length === 1 && lines[0] === '') {
                    addManualRow();
                    return;
                }
                lines.forEach(line => {
                    const parts = line.split('\t');
                    addManualRow(parts[0]?.trim() || '', parts[1]?.trim() || 'Kg', parts[2]?.trim() || '');
                });
            }

            // Tab Switching Logic
            document.querySelectorAll('#pills-tab button').forEach(button => {
                button.addEventListener('shown.bs.tab', function (event) {
                    document.getElementById('active_tab_input').value = event.target.id;
                });
            });

            document.querySelectorAll('#inputModeTab button').forEach(button => {
                button.addEventListener('shown.bs.tab', function (event) {
                    const modeId = event.target.id.replace('-tab', '');
                    document.getElementById('input_mode_input').value = modeId;
                    if (modeId === 'mode-manual') {
                        syncTextareaToManual();
                    } else {
                        syncManualToTextarea();
                    }
                });
            });

            // Restore State from Session
            @if(session('active_tab'))
                const activeTab = document.getElementById('{{ session('active_tab') }}');
                if (activeTab) {
                    bootstrap.Tab.getInstance(activeTab)?.show() || new bootstrap.Tab(activeTab).show();
                }
            @endif

                @if(session('input_mode'))
                    const inputModeTab = document.getElementById('{{ session('input_mode') }}-tab');
                    if (inputModeTab) {
                        bootstrap.Tab.getInstance(inputModeTab)?.show() || new bootstrap.Tab(inputModeTab).show();
                        document.getElementById('input_mode_input').value = '{{ session('input_mode') }}';
                    }
                @endif

            // Global click handler for remove row
            document.addEventListener('click', function (e) {
                if (e.target.closest('.btn-remove-row')) {
                    const row = e.target.closest('tr');
                    if (manualInputBody.children.length > 1) {
                        row.remove();
                    } else {
                        // Clear inputs if only one row left
                        row.querySelectorAll('input').forEach(input => input.value = '');
                    }
                }
            });

            if (btnAddManualRow) {
                btnAddManualRow.addEventListener('click', () => addManualRow());
            }

            // Ensure synchronization before submit
            const komoditasForm = textareaKomoditas.closest('form');
            komoditasForm.addEventListener('submit', function (e) {
                const activeTab = document.querySelector('#inputModeTab .nav-link.active').id;
                if (activeTab === 'mode-manual-tab') {
                    syncManualToTextarea();
                }

                if (textareaKomoditas.value.trim() === '') {
                    e.preventDefault();
                    Swal.fire('Peringatan', 'Silakan masukkan minimal satu komoditas.', 'warning');
                }
            });

            if (selectKategori) {
                selectKategori.addEventListener('change', function () {
                    const kategoriId = this.value;
                    const kategoriName = this.options[this.selectedIndex].text;
                    komoditasTableTitle.innerText = `Daftar Komoditas - ${kategoriName}`;

                    // Show Loading
                    tableBodyKomoditas.innerHTML = `<tr><td colspan="6" class="text-center py-5"><div class="spinner-border text-primary" role="status"></div></td></tr>`;

                    fetch(`/komoditas/get-by-category/${kategoriId}`)
                        .then(response => response.json())
                        .then(data => {
                            let html = '';
                            if (data.length === 0) {
                                html = `<tr><td colspan="6" class="text-center py-5 text-muted">Belum ada data komoditas untuk kategori ini.</td></tr>`;
                                btnClearKomoditas.style.display = 'none';
                                textareaKomoditas.value = '';

                                // Reset manual rows to one empty row
                                manualInputBody.innerHTML = '';
                                addManualRow();
                            } else {
                                // Clear manual rows
                                manualInputBody.innerHTML = '';

                                data.forEach((item, index) => {
                                    // Add to table
                                    html += `
                                                                                                                                        <tr>
                                                                                                                                            <td class="ps-4 text-muted">${index + 1}</td>
                                                                                                                                            <td class="fw-medium">${item.nama_komoditas}</td>
                                                                                                                                            <td class="text-center"><span class="badge bg-blue-faded text-blue border">${item.satuan || '-'}</span></td>
                                                                                                                                            <td class="text-center">${item.batas_selisih_harga ? new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(item.batas_selisih_harga) : '-'}</td>
                                                                                                                                            <td>
                                                                                                                                                <div class="mb-1">
                                                                                                                                                    <span class="badge bg-light text-dark border w-100 text-start" style="padding: 0.4rem 0.6rem;">
                                                                                                                                                        <i class="fas fa-user-edit me-1 text-primary"></i>
                                                                                                                                                        ${item.user_add?.name || 'Admin'}
                                                                                                                                                        <br>
                                                                                                                                                        <small class="text-muted fw-normal" style="font-size: 0.75rem;">
                                                                                                                                                            ${new Date(item.created_at).toLocaleString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' })}
                                                                                                                                                        </small>
                                                                                                                                                    </span>
                                                                                                                                                </div>
                                                                                                                                                ${item.user_update ? `
                                                                                                                                                <div class="mt-1">
                                                                                                                                                    <span class="badge bg-light text-dark border w-100 text-start" style="padding: 0.4rem 0.6rem;">
                                                                                                                                                        <i class="fas fa-user-check me-1 text-success"></i>
                                                                                                                                                        ${item.user_update.name}
                                                                                                                                                        <br>
                                                                                                                                                        <small class="text-muted fw-normal" style="font-size: 0.75rem;">
                                                                                                                                                            ${new Date(item.updated_at).toLocaleString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' })}
                                                                                                                                                        </small>
                                                                                                                                                    </span>
                                                                                                                                                </div>
                                                                                                                                                ` : ''}
                                                                                                                                            </td>
                                                                                                                                            <td class="text-center">
                                                                                                                                                <button class="btn btn-sm btn-outline-warning border-0 btn-edit-komoditas"
                                                                                                                                                    data-bs-toggle="modal" data-bs-target="#modalEditKomoditas"
                                                                                                                                                    data-id="${item.id}"
                                                                                                                                                    data-nama="${item.nama_komoditas}"
                                                                                                                                                    data-satuan="${item.satuan}"
                                                                                                                                                    data-batas="${item.batas_selisih_harga || ''}">
                                                                                                                                                    <i class="fas fa-edit"></i>
                                                                                                                                                </button>
                                                                                                                                                <form action="/komoditas/${item.id}" method="POST" class="form-delete d-inline">
                                                                                                                                                    @csrf
                                                                                                                                                    @method('DELETE')
                                                                                                                                                    <input type="hidden" name="active_tab" value="pills-input-tab">
                                                                                                                                                    <input type="hidden" name="input_mode" value="${document.getElementById('input_mode_input').value}">
                                                                                                                                                    <button type="button" class="btn btn-sm btn-outline-danger border-0 btn-delete">
                                                                                                                                                        <i class="fas fa-trash-alt"></i>
                                                                                                                                                    </button>
                                                                                                                                                </form>
                                                                                                                                            </td>
                                                                                                                                        </tr>
                                                                                                                                    `;

                                    // Add to manual input
                                    addManualRow(item.nama_komoditas, item.satuan || 'Kg', item.batas_selisih_harga || '');
                                });
                                btnClearKomoditas.style.display = 'block';
                                // Also fill textarea
                                textareaKomoditas.value = data.map(i => `${i.nama_komoditas}\t${i.satuan || 'Kg'}\t${i.batas_selisih_harga || ''}`).join('\n');
                            }
                            tableBodyKomoditas.innerHTML = html;
                        });
                });

                // Trigger if already selected
                if (selectKategori.value) {
                    selectKategori.dispatchEvent(new Event('change'));
                }
            }

            if (btnClearKomoditas) {
                btnClearKomoditas.addEventListener('click', function () {
                    const kategoriName = selectKategori.options[selectKategori.selectedIndex].text;
                    Swal.fire({
                        title: 'Kosongkan Data?',
                        html: `Hapus semua komoditas dalam kategori <b>${kategoriName}</b>?`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        confirmButtonText: 'Ya, hapus semua!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            const form = document.createElement('form');
                            form.method = 'POST';
                            form.action = "{{ route('komoditas.clear') }}";

                            const csrf = document.createElement('input');
                            csrf.type = 'hidden';
                            csrf.name = '_token';
                            csrf.value = "{{ csrf_token() }}";

                            const method = document.createElement('input');
                            method.type = 'hidden';
                            method.name = '_method';
                            method.value = "DELETE";

                            const katId = document.createElement('input');
                            katId.type = 'hidden';
                            katId.name = 'kategori_id';
                            katId.value = selectKategori.value;

                            const activeTab = document.createElement('input');
                            activeTab.type = 'hidden';
                            activeTab.name = 'active_tab';
                            activeTab.value = 'pills-input-tab';

                            const inputMode = document.createElement('input');
                            inputMode.type = 'hidden';
                            inputMode.name = 'input_mode';
                            inputMode.value = document.getElementById('input_mode_input').value;

                            form.appendChild(csrf);
                            form.appendChild(method);
                            form.appendChild(katId);
                            form.appendChild(activeTab);
                            form.appendChild(inputMode);
                            document.body.appendChild(form);
                            form.submit();
                        }
                    });
                });
            }



            // Edit Rh Tahun Modal Populating
            const modalEditRhTahunEl = document.getElementById('modalEditRhTahun');
            if (modalEditRhTahunEl) {
                modalEditRhTahunEl.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget;
                    const id = button.getAttribute('data-id');
                    const tahun = button.getAttribute('data-tahun');
                    const selisih = button.getAttribute('data-selisih');

                    this.querySelector('#edit_rh_tahun').value = tahun;


                    let url = "{{ route('rh-tahun.update', ':id') }}";
                    this.querySelector('#formEditRhTahun').action = url.replace(':id', id);
                });
            }

            // Edit Rh Perubahan Modal Populating
            const modalEditRhPerubahanEl = document.getElementById('modalEditRhPerubahan');
            if (modalEditRhPerubahanEl) {
                modalEditRhPerubahanEl.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget;
                    const id = button.getAttribute('data-id');
                    const tanggal = button.getAttribute('data-tanggal');
                    const tahun = button.getAttribute('data-tahun');

                    this.querySelector('#edit_rh_perubahan_tanggal').value = tanggal;
                    this.querySelector('#edit_rh_perubahan_tanggal').min = `${tahun}-01-01`;
                    this.querySelector('#edit_rh_perubahan_tanggal').max = `${tahun}-12-31`;
                    this.querySelector('#edit_rh_perubahan_hint').innerText = `Pilih tanggal di tahun ${tahun}`;

                    let url = "{{ route('rh-perubahan.update', ':id') }}";
                    this.querySelector('#formEditRhPerubahan').action = url.replace(':id', id);
                });
            }

            // Edit Komoditas Modal Populating
            const modalEditKomoditasEl = document.getElementById('modalEditKomoditas');
            if (modalEditKomoditasEl) {
                modalEditKomoditasEl.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget;
                    const id = button.getAttribute('data-id');
                    const nama = button.getAttribute('data-nama');
                    const satuan = button.getAttribute('data-satuan');
                    const batas = button.getAttribute('data-batas');

                    this.querySelector('#edit_komoditas_nama').value = nama;
                    this.querySelector('#edit_komoditas_satuan').value = satuan;
                    this.querySelector('#edit_komoditas_batas').value = batas;

                    let url = "{{ route('komoditas.update', ':id') }}";
                    this.querySelector('#formEditKomoditas').action = url.replace(':id', id);
                });
            }

            // --- Fitur Sort Perubahan ---
            const btnSortPerubahan = document.querySelector('.btn-sort-perubahan');
            if (btnSortPerubahan) {
                let sortDesc = true; // Default DESC from backend
                btnSortPerubahan.addEventListener('click', function () {
                    sortDesc = !sortDesc;
                    const icon = this.querySelector('i');
                    icon.className = sortDesc ? 'fas fa-sort-amount-down' : 'fas fa-sort-amount-up';

                    document.querySelectorAll('.perubahan-list').forEach(list => {
                        const items = Array.from(list.querySelectorAll('.perubahan-item'));
                        items.sort((a, b) => {
                            const dateA = new Date(a.getAttribute('data-date'));
                            const dateB = new Date(b.getAttribute('data-date'));
                            return sortDesc ? dateB - dateA : dateA - dateB;
                        });

                        // Clear and Re-append
                        list.innerHTML = '';
                        items.forEach(item => list.appendChild(item));
                    });

                    // Show a small toast or notification if needed (optional)
                    console.log('Sorted revisions:', sortDesc ? 'Descending' : 'Ascending');
                });
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const dateInput = document.getElementById('input-tanggal-header');
            const dateHint = document.getElementById('date_hint_header');

            const tsRhTahun = new TomSelect("#select_rh_tahun_perubahan", {
                create: false,
                sortField: {
                    field: "text",
                    direction: "desc"
                },
                onChange: function (value) {
                    if (value) {
                        const optionHeader = this.options[value];
                        const year = optionHeader.tahun || optionHeader['data-tahun'] || (this.getItem(value) ? this.getItem(value).innerHTML.trim() : '');

                        dateInput.readOnly = false;
                        dateInput.style.pointerEvents = 'auto';
                        dateInput.style.backgroundColor = 'white';

                        dateInput.max = `${year}-12-31`;

                        const latestDate = window.latestRhDates[value];
                        if (latestDate) {
                            // Date restriction: must be AFTER the latest existing date
                            const d = new Date(latestDate);
                            d.setDate(d.getDate() + 1);
                            const minDate = d.toISOString().split('T')[0];
                            dateInput.min = minDate;
                            dateInput.value = minDate;
                            dateHint.innerHTML = `Pilih tanggal <b>setelah ${new Date(latestDate).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })}</b>`;
                        } else {
                            dateInput.min = `${year}-01-01`;
                            dateInput.value = `${year}-01-01`;
                            dateHint.innerText = `Pilih tanggal di tahun ${year}`;
                        }
                        dateHint.classList.remove('text-muted');
                        dateHint.classList.add('text-primary', 'fw-bold');
                    } else {
                        dateInput.value = '';
                        dateInput.min = '';
                        dateInput.max = '';
                        dateInput.readOnly = true;
                        dateInput.style.pointerEvents = 'none';
                        dateInput.style.backgroundColor = '#f8f9fa';

                        dateHint.innerText = 'Pilih tahun terlebih dahulu.';
                        dateHint.classList.remove('text-primary', 'fw-bold');
                        dateHint.classList.add('text-muted');
                    }
                }
            });

            // Initial trigger
            if (tsRhTahun.getValue()) {
                tsRhTahun.settings.onChange.call(tsRhTahun, tsRhTahun.getValue());
            }
        });
    </script>
@endpush