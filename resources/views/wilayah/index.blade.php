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
            <div class="col-12 col-md-6">
                <div class="card border-0 shadow-sm rounded-4 bps-card h-100">
                    <div class="card-body p-4 d-flex align-items-center">
                        <div class="stats-icon bg-orange-light text-bps-orange me-3">
                            <i class="fas fa-map"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1 text-sm">Total Kecamatan</h6>
                            <h3 class="mb-0 fw-bold">{{ $totalKecamatan }}</h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="card border-0 shadow-sm rounded-4 bps-card h-100">
                    <div class="card-body p-4 d-flex align-items-center">
                        <div class="stats-icon bg-orange-light text-bps-orange me-3">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1 text-sm">Total Desa</h6>
                            <h3 class="mb-0 fw-bold">{{ $totalDesa }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4">
            <div
                class="card-header bg-white border-bottom py-3 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <h5 class="mb-0 fw-bold" style="color: var(--bps-orange);">Daftar Wilayah (Desa/Kelurahan)</h5>
                <div class="d-flex flex-column flex-md-row gap-2">
                    <form action="{{ route('wilayah.index') }}" method="GET" class="d-flex">
                        <div class="input-group input-group-sm">
                            <input type="text" name="search" class="form-control" placeholder="Cari Desa atau Kecamatan..."
                                value="{{ $search }}">
                            <button class="btn btn-outline-secondary" type="submit"><i class="fas fa-search"></i></button>
                        </div>
                    </form>
                    <button type="button" class="btn btn-orange btn-sm px-4 rounded-pill" data-bs-toggle="modal"
                        data-bs-target="#modalTambahWilayah">
                        <i class="fas fa-plus me-1"></i> Tambah Wilayah
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
                                <th class="py-3 text-secondary">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $globalNo = 1;
                                // Predefined soft colors for group headers
                                $groupColors = ['#f8fafc', '#f0fdf4', '#fefce8', '#fff7ed', '#faf5ff', '#fdf2f8'];
                                $colorIndex = 0;
                            @endphp
                            
                            @forelse($kecamatans as $kecamatan)
                                @php
                                    $bgColor = $groupColors[$colorIndex % count($groupColors)];
                                    $colorIndex++;

                                    $kecCreator = $kecamatan->creator->name ?? 'Sistem';
                                    $kecCreatedAt = $kecamatan->created_at ? \Carbon\Carbon::parse($kecamatan->created_at)->format('d-m-Y') : '-';
                                    $kecTooltip = "Dibuat oleh: {$kecCreator} pada {$kecCreatedAt}";
                                    if ($kecamatan->updated_by) {
                                        $kecUpdater = $kecamatan->updater->name ?? 'Sistem';
                                        $kecUpdatedAt = $kecamatan->updated_at ? \Carbon\Carbon::parse($kecamatan->updated_at)->format('d-m-Y') : '-';
                                        $kecTooltip .= " | Update oleh: {$kecUpdater} pada {$kecUpdatedAt}";
                                    }
                                @endphp

                                <!-- Group Header -->
                                <tr class="kecamatan-group-header" data-bs-toggle="collapse" data-bs-target=".desa-group-{{ $kecamatan->id }}" aria-expanded="true" style="cursor: pointer; background-color: {{ $bgColor }}; border-left: 4px solid var(--bps-orange);">
                                    <td colspan="5" class="py-3 px-4">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="d-flex align-items-center gap-3">
                                                <i class="fas fa-chevron-down text-secondary transition-transform toggle-icon"></i>
                                                <div>
                                                    <span class="fw-bold text-dark fs-6 mb-0 d-block" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $kecTooltip }}">
                                                        Kecamatan {{ $kecamatan->nama_kecamatan }}
                                                    </span>
                                                    @if($isProvinsi)
                                                        <small class="text-muted"><i class="fas fa-map-marker-alt me-1"></i>{{ $kecamatan->kabupaten->nama_kabupaten ?? '-' }}</small>
                                                    @endif
                                                </div>
                                                <span class="badge bg-white text-dark border ms-2 shadow-sm rounded-pill px-3">{{ $kecamatan->desas->count() }} Desa</span>
                                            </div>
                                            <div class="text-end">
                                                <span class="badge bg-light text-secondary border">Kode: {{ $kecamatan->kode_kecamatan }}</span>
                                                <button type="button" class="btn btn-sm btn-outline-orange ms-2"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editKecamatanModal{{ $kecamatan->id }}"
                                                    title="Edit Kecamatan" onclick="event.stopPropagation();">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger btn-delete ms-1"
                                                    data-url="{{ route('wilayah.destroy-kecamatan', $kecamatan->id) }}" data-type="Kecamatan"
                                                    data-name="{{ $kecamatan->nama_kecamatan }}" title="Hapus Kecamatan" onclick="event.stopPropagation();">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                @forelse($kecamatan->desas as $desa)
                                @php
                                    $desaCreator = $desa->creator->name ?? 'Sistem';
                                    $desaCreatedAt = $desa->created_at ? \Carbon\Carbon::parse($desa->created_at)->format('d-m-Y') : '-';
                                    $desaTooltip = "Dibuat oleh: {$desaCreator} pada {$desaCreatedAt}";
                                    if ($desa->updated_by) {
                                        $desaUpdater = $desa->updater->name ?? 'Sistem';
                                        $desaUpdatedAt = $desa->updated_at ? \Carbon\Carbon::parse($desa->updated_at)->format('d-m-Y') : '-';
                                        $desaTooltip .= " | Update oleh: {$desaUpdater} pada {$desaUpdatedAt}";
                                    }
                                @endphp
                                <tr class="collapse show desa-group-{{ $kecamatan->id }} desa-row hover-effect">
                                    <td class="px-4 py-3 text-muted border-start border-3 border-transparent ps-4">{{ $globalNo++ }}</td>
                                    <td class="py-3">
                                        <div class="d-flex flex-column">
                                            <span class="text-muted small"><i class="fas fa-level-up-alt fa-rotate-90 text-light me-2"></i>{{ $kecamatan->nama_kecamatan }}</span>
                                        </div>
                                    </td>
                                    <td class="py-3">
                                        <span class="badge bg-orange text-dark border border-orange px-3 py-2 rounded-pill fw-bold" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $desaTooltip }}" style="cursor: help;">
                                            <i class="fas fa-hashtag me-1 opacity-75"></i> {{ $desa->kode_desa }}
                                        </span>
                                    </td>
                                    <td class="py-3 fw-semibold text-dark">
                                        <span data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $desaTooltip }}" style="cursor: help;">
                                            {{ $desa->nama_desa }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="btn-group shadow-sm rounded">
                                            <button type="button" class="btn btn-sm btn-light border" data-bs-toggle="modal"
                                                data-bs-target="#editDesaModal{{ $desa->id }}" title="Edit Desa">
                                                <i class="fas fa-edit text-orange"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-light border text-danger btn-delete"
                                                data-url="{{ route('wilayah.destroy-desa', $desa->id) }}" data-type="Desa"
                                                data-name="{{ $desa->nama_desa }}" title="Hapus Desa">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Modal Edit Desa (Inside Loop) -->
                                <div class="modal fade" id="editDesaModal{{ $desa->id }}" tabindex="-1" aria-hidden="true">
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
                                                        <label class="form-label fw-medium">Kecamatan <span
                                                                class="text-danger">*</span></label>
                                                        <select name="kecamatan_id" class="form-select select2-edit-kecamatan"
                                                            required>
                                                            <option value="{{ $desa->kecamatan_id }}" selected>
                                                                [{{ $desa->kecamatan->kode_kecamatan }}]
                                                                {{ $desa->kecamatan->nama_kecamatan }} @if($isProvinsi)
                                                                    ([{{ $desa->kecamatan->kabupaten->kode_kab }}]
                                                                {{ $desa->kecamatan->kabupaten->nama_kabupaten }}) @endif
                                                            </option>
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
                                                    <button type="submit" class="btn btn-orange rounded-pill px-4">Simpan
                                                        Perubahan</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                @empty
                                <tr class="collapse show desa-group-{{ $kecamatan->id }}">
                                    <td colspan="5" class="text-center py-4 text-muted small border-start border-3 border-transparent ps-4" style="background-color: #fafafa;">
                                        <i class="fas fa-info-circle me-1"></i> Belum ada desa di kecamatan ini.
                                    </td>
                                </tr>
                                @endforelse

                                <!-- Modal Edit Kecamatan (Inside Kecamatan Loop) -->
                                <div class="modal fade" id="editKecamatanModal{{ $kecamatan->id }}" tabindex="-1"
                                    aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title fw-bold">Edit Kecamatan</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <form action="{{ route('wilayah.update-kecamatan', $kecamatan->id) }}"
                                                method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label fw-medium">Kabupaten <span
                                                                class="text-danger">*</span></label>
                                                        <select name="kabupaten_id" class="form-select select2-edit-kabupaten"
                                                            required>
                                                            @foreach($kabupatens as $kab)
                                                                <option value="{{ $kab->id }}" {{ $kecamatan->kabupaten_id == $kab->id ? 'selected' : '' }}>
                                                                    [{{ $kab->kode_kab }}] {{ $kab->nama_kabupaten }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-medium">Kode Kecamatan <span
                                                                class="text-danger">*</span></label>
                                                        <input type="text" name="kode_kecamatan" class="form-control"
                                                            value="{{ $kecamatan->kode_kecamatan }}" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-medium">Nama Kecamatan <span
                                                                class="text-danger">*</span></label>
                                                        <input type="text" name="nama_kecamatan" class="form-control"
                                                            value="{{ $kecamatan->nama_kecamatan }}" required>
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
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <div class="mb-3"><i class="fas fa-map-marked-alt text-light fa-3x"></i></div>
                                        Tidak ditemukan data wilayah yang sesuai.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4 px-4 pb-4 d-flex justify-content-between align-items-center">
                    <div class="text-muted small">
                        Menampilkan {{ $kecamatans->firstItem() ?? 0 }} sampai {{ $kecamatans->lastItem() ?? 0 }} dari
                        {{ $kecamatans->total() }} kecamatan
                    </div>
                    <div>
                        {{ $kecamatans->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Wilayah (Unified) -->
    <div class="modal fade" id="modalTambahWilayah" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Tambah Wilayah Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('wilayah.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-medium">Kabupaten <span class="text-danger">*</span></label>
                            <select name="kabupaten_id" id="kabupaten_id_unified" class="form-select select2" required>
                                @if(count($kabupatens) > 1)
                                    <option value="">Pilih Kabupaten</option>
                                @endif
                                @foreach($kabupatens as $kab)
                                    <option value="{{ $kab->id }}">[{{ $kab->kode_kab }}] {{ $kab->nama_kabupaten }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label fw-medium mb-0">Kecamatan <span
                                        class="text-danger">*</span></label>
                                <div class="form-check form-switch small">
                                    <input class="form-check-input" type="checkbox" id="toggle_new_kecamatan"
                                        name="is_new_kecamatan" value="1">
                                    <label class="form-check-label" for="toggle_new_kecamatan">Kecamatan Baru?</label>
                                </div>
                            </div>

                            <div id="wrapper_select_kecamatan">
                                <select name="kecamatan_id" id="kecamatan_id_unified" class="form-select select2" required>
                                </select>
                            </div>

                            <div id="wrapper_new_kecamatan" style="display: none;">
                                <div class="row g-2">
                                    <div class="col-4">
                                        <input type="text" name="new_kode_kecamatan" class="form-control"
                                            placeholder="Kode">
                                    </div>
                                    <div class="col-8">
                                        <input type="text" name="new_nama_kecamatan" class="form-control"
                                            placeholder="Nama Kecamatan Baru">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4 text-muted">
                        <h6 class="fw-bold mb-3"><i class="fas fa-home me-1"></i> Data Desa</h6>

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
                    <div class="modal-footer pb-2 border-0">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-orange rounded-pill px-4">Simpan Wilayah</button>
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
        </style>
    @endpush

    @push('scripts')
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script>
            $(document).ready(function () {
                // Initialize Select2 for Unified Modal
                $('#kabupaten_id_unified').select2({
                    dropdownParent: $('#modalTambahWilayah'),
                    width: '100%',
                    placeholder: 'Pilih Kabupaten'
                });

                $('#kabupaten_id_unified').on('change', function() {
                    $('#kecamatan_id_unified').val(null).trigger('change');
                });

                $('#kecamatan_id_unified').select2({
                    dropdownParent: $('#modalTambahWilayah'),
                    width: '100%',
                    placeholder: 'Ketik untuk mencari Kecamatan...',
                    allowClear: true,
                    ajax: {
                        url: '/wilayah/search-kecamatan',
                        dataType: 'json',
                        delay: 250,
                        data: function (params) {
                            return { 
                                q: params.term,
                                kabupaten_id: $('#kabupaten_id_unified').val()
                            };
                        },
                        processResults: function (data) {
                            return { results: data.results };
                        },
                        cache: true
                    }
                });

                // Handle Toggle New Kecamatan
                $('#toggle_new_kecamatan').on('change', function () {
                    if ($(this).is(':checked')) {
                        $('#wrapper_select_kecamatan').hide();
                        $('#wrapper_new_kecamatan').show();
                        $('#kecamatan_id_unified').prop('required', false);
                        $('input[name="new_kode_kecamatan"]').prop('required', true);
                        $('input[name="new_nama_kecamatan"]').prop('required', true);
                    } else {
                        $('#wrapper_select_kecamatan').show();
                        $('#wrapper_new_kecamatan').hide();
                        $('#kecamatan_id_unified').prop('required', true);
                        $('input[name="new_kode_kecamatan"]').prop('required', false);
                        $('input[name="new_nama_kecamatan"]').prop('required', false);
                    }
                });

                $('.select2-edit-kabupaten').each(function () {
                    $(this).select2({
                        dropdownParent: $(this).closest('.modal'),
                        width: '100%',
                        placeholder: 'Pilih Kabupaten'
                    });
                });

                $('.select2-edit-kecamatan').each(function () {
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