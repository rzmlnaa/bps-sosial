@extends('layouts.admin')

@section('title', 'Kelola Wilayah')

@section('content')
    <div class="mt-4">

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 fade-in-up">
            <div>
                <h2 class="fw-bold mb-1" style="color: var(--bps-orange);">Kelola Wilayah</h2>
                <p class="text-muted mb-0">Mengelola Data Wilayah Kecamatan - Desa </p>
            </div>
        </div>

        <!-- Stats summary -->
        <div class="row g-4 mb-4 fade-in-up">
            <div class="col-12 col-md-4">
                <div class="card border-0 shadow-sm rounded-4 bps-card h-100">
                    <div class="card-body p-4 d-flex align-items-center">
                        <div class="stats-icon bg-orange-light text-bps-orange me-3">
                            <i class="fas fa-map"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1 text-sm">Total Kecamatan</h6>
                            <h3 class="mb-0 fw-bold">{{ $kecamatans->total() }}</h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="card border-0 shadow-sm rounded-4 bps-card h-100">
                    <div class="card-body p-4 d-flex align-items-center">
                        <div class="stats-icon bg-orange-light text-bps-orange me-3">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1 text-sm">Total Desa</h6>
                            <h3 class="mb-0 fw-bold">{{ $desas->total() }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @php $activeTab = session('tab', request('tab', 'kecamatan')); @endphp
        <ul class="nav nav-tabs fw-medium border-bottom-0 mb-4 fade-in-up" id="wilayahTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $activeTab == 'kecamatan' ? 'active' : '' }} px-4 py-3 rounded-top-3 border" id="kecamatan-tab" data-bs-toggle="tab"
                    data-bs-target="#kecamatan" type="button" role="tab" aria-controls="kecamatan" aria-selected="true">
                    <i class="fas fa-map me-2"></i>Kecamatan
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $activeTab == 'desa' ? 'active' : '' }} px-4 py-3 rounded-top-3 border ms-2" id="desa-tab" data-bs-toggle="tab"
                    data-bs-target="#desa" type="button" role="tab" aria-controls="desa" aria-selected="false">
                    <i class="fas fa-map-marker-alt me-2"></i>Desa
                </button>
            </li>
        </ul>

        <div class="tab-content" id="wilayahTabContent">
            <!-- Tab Kecamatan -->
            <div class="tab-pane fade {{ $activeTab == 'kecamatan' ? 'show active' : '' }}" id="kecamatan" role="tabpanel" aria-labelledby="kecamatan-tab">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white border-bottom py-3 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                        <h5 class="mb-0 fw-bold" style="color: var(--bps-orange);">Daftar Kecamatan</h5>
                        <div class="d-flex flex-column flex-md-row gap-2">
                            <form action="{{ route('wilayah.index') }}" method="GET" class="d-flex">
                                <input type="hidden" name="tab" value="kecamatan">
                                @if(request('desa_page'))
                                    <input type="hidden" name="desa_page" value="{{ request('desa_page') }}">
                                @endif
                                @if(request('search_desa'))
                                    <input type="hidden" name="search_desa" value="{{ request('search_desa') }}">
                                @endif
                                <div class="input-group input-group-sm">
                                    <input type="text" name="search_kecamatan" class="form-control" placeholder="Cari kecamatan..." value="{{ request('search_kecamatan') }}">
                                    <button class="btn btn-outline-secondary" type="submit"><i class="fas fa-search"></i></button>
                                </div>
                            </form>
                            <button type="button" class="btn btn-orange btn-sm px-3 rounded-pill" data-bs-toggle="modal"
                                data-bs-target="#modalTambahKecamatan">
                                <i class="fas fa-plus me-1"></i> Tambah Kecamatan
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="px-4 py-3 text-secondary" width="5%">No</th>
                                        @if($isProvinsi)
                                            <th class="py-3 text-secondary">Kabupaten</th>
                                        @endif
                                        <th class="py-3 text-secondary">Kode Kecamatan</th>
                                        <th class="py-3 text-secondary">Nama Kecamatan</th>
                                        <th class="py-3 text-secondary">Dibuat Oleh</th>
                                        <th class="py-3 text-secondary">Diubah Oleh</th>
                                        <th class="px-4 py-3 text-end text-secondary" width="15%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($kecamatans as $index => $kec)
                                        <tr>
                                            <td class="px-4 py-3">{{ $index + 1 }}</td>
                                            @if($isProvinsi)
                                                <td class="py-3 fw-medium">[{{ $kec->kabupaten->kode_kab }}] {{ $kec->kabupaten->nama_kabupaten }}</td>
                                            @endif
                                            <td class="py-3"><span
                                                    class="badge bg-light text-dark border">{{ $kec->kode_kecamatan }}</span>
                                            </td>
                                            <td class="py-3 fw-medium">{{ $kec->nama_kecamatan }}</td>
                                            <td class="py-3">
                                                @if($kec->creator)
                                                    <div class="fw-medium text-dark" data-bs-toggle="tooltip"
                                                        title="{{ $kec->creator->kabupaten->nama_kabupaten ?? '-' }}">
                                                        {{ $kec->creator->name }}</div>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td class="py-3">
                                                @if($kec->updater)
                                                    <div class="fw-medium text-dark" data-bs-toggle="tooltip"
                                                        title="{{ $kec->updater->kabupaten->nama_kabupaten ?? '-' }}">
                                                        {{ $kec->updater->name }}</div>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-end">
                                                <div class="btn-group">
                                                    <button type="button" class="btn btn-sm btn-outline-orange"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#editKecamatanModal{{ $kec->id }}">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-danger btn-delete"
                                                        data-url="{{ route('wilayah.destroy-kecamatan', $kec->id) }}"
                                                        data-type="Kecamatan" data-name="{{ $kec->nama_kecamatan }}">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>

                                        <!-- Modal Edit Kecamatan -->
                                        <div class="modal fade" id="editKecamatanModal{{ $kec->id }}" tabindex="-1"
                                            aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title fw-bold">Edit Kecamatan</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                            aria-label="Close"></button>
                                                    </div>
                                                    <form action="{{ route('wilayah.update-kecamatan', $kec->id) }}"
                                                        method="POST">
                                                        @csrf
                                                        @method('PUT')
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label class="form-label fw-medium">Kabupaten <span class="text-danger">*</span></label>
                                                                <select name="kabupaten_id" class="form-select select2-edit-kabupaten" required>
                                                                    @foreach($kabupatens as $kab)
                                                                        <option value="{{ $kab->id }}" {{ $kec->kabupaten_id == $kab->id ? 'selected' : '' }}>[{{ $kab->kode_kab }}] {{ $kab->nama_kabupaten }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label fw-medium">Kode Kecamatan <span
                                                                        class="text-danger">*</span></label>
                                                                <input type="text" name="kode_kecamatan" class="form-control"
                                                                    value="{{ $kec->kode_kecamatan }}" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label fw-medium">Nama Kecamatan <span
                                                                        class="text-danger">*</span></label>
                                                                <input type="text" name="nama_kecamatan" class="form-control"
                                                                    value="{{ $kec->nama_kecamatan }}" required>
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
                                    @empty
                                        <tr>
                                            <td colspan="{{ $isProvinsi ? 7 : 6 }}" class="text-center py-5 text-muted">
                                                <div class="mb-3"><i class="fas fa-map text-light fa-3x"></i></div>
                                                Belum ada data kecamatan.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3 px-3">
                            {{ $kecamatans->appends(['tab' => 'kecamatan', 'desa_page' => request('desa_page'), 'search_kecamatan' => request('search_kecamatan'), 'search_desa' => request('search_desa')])->links('pagination::bootstrap-5') }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab Desa -->
            <div class="tab-pane fade {{ $activeTab == 'desa' ? 'show active' : '' }}" id="desa" role="tabpanel" aria-labelledby="desa-tab">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white border-bottom py-3 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                        <h5 class="mb-0 fw-bold" style="color: var(--bps-orange);">Daftar Desa</h5>
                        <div class="d-flex flex-column flex-md-row gap-2">
                            <form action="{{ route('wilayah.index') }}" method="GET" class="d-flex">
                                <input type="hidden" name="tab" value="desa">
                                @if(request('kecamatan_page'))
                                    <input type="hidden" name="kecamatan_page" value="{{ request('kecamatan_page') }}">
                                @endif
                                @if(request('search_kecamatan'))
                                    <input type="hidden" name="search_kecamatan" value="{{ request('search_kecamatan') }}">
                                @endif
                                <div class="input-group input-group-sm">
                                    <input type="text" name="search_desa" class="form-control" placeholder="Cari desa..." value="{{ request('search_desa') }}">
                                    <button class="btn btn-outline-secondary" type="submit"><i class="fas fa-search"></i></button>
                                </div>
                            </form>
                            <button type="button" class="btn btn-orange btn-sm px-3 rounded-pill" data-bs-toggle="modal"
                                data-bs-target="#modalTambahDesa">
                                <i class="fas fa-plus me-1"></i> Tambah Desa
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="px-4 py-3 text-secondary" width="5%">No</th>
                                        <th class="py-3 text-secondary">Kecamatan</th>
                                        <th class="py-3 text-secondary">Kode Desa</th>
                                        <th class="py-3 text-secondary">Nama Desa</th>
                                        <th class="py-3 text-secondary">Dibuat Oleh</th>
                                        <th class="py-3 text-secondary">Diubah Oleh</th>
                                        <th class="px-4 py-3 text-end text-secondary" width="15%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($desas as $index => $desa)
                                        <tr>
                                            <td class="px-4 py-3">{{ $index + 1 }}</td>
                                            <td class="py-3 flex-column">
                                                <div class="fw-medium">[{{ $desa->kecamatan->kode_kecamatan }}] {{ $desa->kecamatan->nama_kecamatan }}</div>
                                                @if($isProvinsi)
                                                    <small
                                                        class="text-muted">[{{ $desa->kecamatan->kabupaten->kode_kab }}] {{ $desa->kecamatan->kabupaten->nama_kabupaten }}</small>
                                                @endif
                                            </td>
                                            <td class="py-3"><span
                                                    class="badge bg-light text-dark border">{{ $desa->kode_desa }}</span></td>
                                            <td class="py-3 fw-medium">{{ $desa->nama_desa }}</td>
                                            <td class="py-3">
                                                @if($desa->creator)
                                                    <div class="fw-medium text-dark" data-bs-toggle="tooltip" title="{{ $desa->creator->kabupaten->nama_kabupaten ?? '-' }}">{{ $desa->creator->name }}</div>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td class="py-3">
                                                @if($desa->updater)
                                                    <div class="fw-medium text-dark" data-bs-toggle="tooltip" title="{{ $desa->updater->kabupaten->nama_kabupaten ?? '-' }}">{{ $desa->updater->name }}</div>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-end">
                                                <div class="btn-group">
                                                    <button type="button" class="btn btn-sm btn-outline-orange"
                                                        data-bs-toggle="modal" data-bs-target="#editDesaModal{{ $desa->id }}">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-danger btn-delete"
                                                        data-url="{{ route('wilayah.destroy-desa', $desa->id) }}"
                                                        data-type="Desa" data-name="{{ $desa->nama_desa }}">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>

                                        <!-- Modal Edit Desa -->
                                        <div class="modal fade" id="editDesaModal{{ $desa->id }}" tabindex="-1"
                                            aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title fw-bold">Edit Desa</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                            aria-label="Close"></button>
                                                    </div>
                                                    <form action="{{ route('wilayah.update-desa', $desa->id) }}" method="POST">
                                                        @csrf
                                                        @method('PUT')
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label class="form-label fw-medium">Kecamatan <span class="text-danger">*</span></label>
                                                                <select name="kecamatan_id" class="form-select select2-edit-kecamatan" required>
                                                                    <option value="{{ $desa->kecamatan_id }}" selected>[{{ $desa->kecamatan->kode_kecamatan }}] {{ $desa->kecamatan->nama_kecamatan }} @if($isProvinsi) ([{{ $desa->kecamatan->kabupaten->kode_kab }}] {{ $desa->kecamatan->kabupaten->nama_kabupaten }}) @endif</option>
                                                                </select>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label fw-medium">Kode Desa <span
                                                                        class="text-danger">*</span></label>
                                                                <input type="text" name="kode_desa" class="form-control"
                                                                    value="{{ $desa->kode_desa }}" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label fw-medium">Nama Desa <span
                                                                        class="text-danger">*</span></label>
                                                                <input type="text" name="nama_desa" class="form-control"
                                                                    value="{{ $desa->nama_desa }}" required>
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
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-5 text-muted">
                                                <div class="mb-3"><i class="fas fa-map-marker-alt text-light fa-3x"></i></div>
                                                Belum ada data desa.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3 px-3">
                            {{ $desas->appends(['tab' => 'desa', 'kecamatan_page' => request('kecamatan_page'), 'search_kecamatan' => request('search_kecamatan'), 'search_desa' => request('search_desa')])->links('pagination::bootstrap-5') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Kecamatan -->
    <div class="modal fade" id="modalTambahKecamatan" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold">Tambah Kecamatan Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('wilayah.store-kecamatan') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-medium">Kabupaten <span class="text-danger">*</span></label>
                            <select name="kabupaten_id" id="kabupaten_id" class="form-select select2" required>
                                @if(count($kabupatens) > 1)
                                    <option value="">Pilih Kabupaten</option>
                                @endif
                                @foreach($kabupatens as $kab)
                                    <option value="{{ $kab->id }}">[{{ $kab->kode_kab }}] {{ $kab->nama_kabupaten }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-medium">Kode Kecamatan <span class="text-danger">*</span></label>
                            <input type="text" name="kode_kecamatan" class="form-control" required
                                placeholder="Contoh: 010">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-medium">Nama Kecamatan <span class="text-danger">*</span></label>
                            <input type="text" name="nama_kecamatan" class="form-control" required
                                placeholder="Contoh: Pontianak Selatan">
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 pt-0">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-orange rounded-pill px-4">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Desa -->
    <div class="modal fade" id="modalTambahDesa" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold">Tambah Desa Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('wilayah.store-desa') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-medium">Kecamatan <span class="text-danger">*</span></label>
                            <select name="kecamatan_id" id="kecamatan_id" class="form-select select2" required>
                            </select>
                            @if($kecamatans->total() == 0)
                                <div class="form-text text-danger mt-1"><i class="fas fa-info-circle me-1"></i>Silahkan
                                    tambahkan kecamatan terlebih dahulu.</div>
                            @endif
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-medium">Kode Desa <span class="text-danger">*</span></label>
                            <input type="text" name="kode_desa" class="form-control" required placeholder="Contoh: 001">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-medium">Nama Desa <span class="text-danger">*</span></label>
                            <input type="text" name="nama_desa" class="form-control" required
                                placeholder="Contoh: Kota Baru">
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 pt-0">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-orange rounded-pill px-4" {{ $kecamatans->total() == 0 ? 'disabled' : '' }}>Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Global Form untuk SweetAlert Delete -->
    <form id="globalDeleteForm" method="POST" action="" style="display: none;">
        @csrf
        @method('DELETE')
    </form>

    @push('styles')
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <style>
            .select2-container--default .select2-selection--single {
                height: 38px;
                border: 1px solid #dee2e6;
                border-radius: 6px;
                display: flex;
                align-items: center;
            }

            .select2-container--default .select2-selection--single .select2-selection__arrow {
                height: 36px;
                right: 8px;
            }

            .select2-container--default .select2-selection--single:focus-within {
                border-color: var(--bps-orange);
                box-shadow: 0 0 0 0.25rem rgba(243, 112, 33, 0.25);
            }

            .select2-results__option--highlighted {
                background-color: var(--bps-orange) !important;
            }

            .nav-tabs .nav-link {
                color: #64748b;
                background-color: #f8fafc;
                border-color: #e2e8f0;
            }

            .nav-tabs .nav-link.active {
                color: var(--bps-orange);
                font-weight: 600;
                background-color: #fff;
                border-bottom-color: transparent !important;
            }

            .btn-orange {
                background-color: var(--bps-orange);
                border-color: var(--bps-orange);
                color: #fff;
            }

            .btn-orange:hover,
            .btn-orange:focus {
                background-color: #e6661a;
                border-color: #e6661a;
                color: #fff;
            }

            .btn-outline-orange {
                color: var(--bps-orange);
                border-color: var(--bps-orange);
                background-color: transparent;
            }

            .btn-outline-orange:hover,
            .btn-outline-orange:focus {
                background-color: var(--bps-orange);
                color: #fff;
            }

            .text-orange {
                color: var(--bps-orange) !important;
            }
        </style>
    @endpush
    @push('scripts')
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script>
            $(document).ready(function () {
                $('#kabupaten_id').select2({
                    dropdownParent: $('#modalTambahKecamatan'),
                    width: '100%',
                    placeholder: 'Pilih Kabupaten'
                });

                $('#kecamatan_id').select2({
                    dropdownParent: $('#modalTambahDesa'),
                    width: '100%',
                    placeholder: 'Ketik untuk mencari Kecamatan...',
                    allowClear: true,
                    ajax: {
                        url: '/wilayah/search-kecamatan',
                        dataType: 'json',
                        delay: 250,
                        data: function (params) {
                            return { q: params.term };
                        },
                        processResults: function (data) {
                            return { results: data.results };
                        },
                        cache: true
                    }
                });

                $('.select2-edit-kabupaten').each(function() {
                    $(this).select2({
                        dropdownParent: $(this).closest('.modal'),
                        width: '100%',
                        placeholder: 'Pilih Kabupaten'
                    });
                });

                $('.select2-edit-kecamatan').each(function() {
                    $(this).select2({
                        dropdownParent: $(this).closest('.modal'),
                        width: '100%',
                        placeholder: 'Ketik untuk mencari Kecamatan...',
                        allowClear: true,
                        ajax: {
                            url: '/wilayah/search-kecamatan',
                            dataType: 'json',
                            delay: 250,
                            data: function (params) {
                                return { q: params.term };
                            },
                            processResults: function (data) {
                                return { results: data.results };
                            },
                            cache: true
                        }
                    });
                });

                // Initialize tooltips
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
                var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl)
                });
            });

            document.addEventListener('DOMContentLoaded', function () {
                const deleteButtons = document.querySelectorAll('.btn-delete');
                deleteButtons.forEach(button => {
                    button.addEventListener('click', function () {
                        const url = this.getAttribute('data-url');
                        const type = this.getAttribute('data-type');
                        const name = this.getAttribute('data-name');

                        Swal.fire({
                            title: `Hapus ${type}?`,
                            text: `Anda yakin ingin menghapus ${name}? Tindakan ini tidak dapat dibatalkan.`,
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#d33',
                            cancelButtonColor: '#6c757d',
                            confirmButtonText: 'Ya, Hapus',
                            cancelButtonText: 'Batal'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                const form = document.getElementById('globalDeleteForm');
                                form.action = url;
                                form.submit();
                            }
                        });
                    });
                });
            });
        </script>
    @endpush
@endsection