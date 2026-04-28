@extends('layouts.admin')

@section('title', 'Kelola Desa Cantik')

@section('content')
    <div class="mt-3">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 fade-in-up">
            <div>
                <a href="{{ route('desa-cantik.index') }}" class="btn btn-light btn-sm mb-2 rounded-pill">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
                <h2 class="fw-bold mb-1" style="color: var(--bps-orange);">Kelola Descan</h2>
                <p class="text-muted mb-0">Pengaturan Periode, Kuota, dan Kegiatan Desa Cantik</p>
            </div>
        </div>

        @php $activeTab = session('tab', request('tab', 'periode')); @endphp
        <ul class="nav nav-tabs fw-medium border-bottom-0 mb-4 fade-in-up scrollable-tabs" id="kelolaTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $activeTab == 'periode' ? 'active' : '' }} px-4 py-3 rounded-top-3 border"
                    id="periode-tab" data-bs-toggle="tab" data-bs-target="#periode" type="button" role="tab"
                    aria-controls="periode" aria-selected="true">
                    <i class="fas fa-calendar-alt me-2"></i>Periode Tahun
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $activeTab == 'kuota' ? 'active' : '' }} px-4 py-3 rounded-top-3 border ms-2"
                    id="kuota-tab" data-bs-toggle="tab" data-bs-target="#kuota" type="button" role="tab"
                    aria-controls="kuota" aria-selected="false">
                    <i class="fas fa-chart-pie me-2"></i>Pengaturan Kuota
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $activeTab == 'kegiatan' ? 'active' : '' }} px-4 py-3 rounded-top-3 border ms-2"
                    id="kegiatan-tab" data-bs-toggle="tab" data-bs-target="#kegiatan" type="button" role="tab"
                    aria-controls="kegiatan" aria-selected="false">
                    <i class="fas fa-list-check me-2"></i>Pengaturan Kegiatan
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $activeTab == 'jbk' ? 'active' : '' }} px-4 py-3 rounded-top-3 border ms-2"
                    id="jbk-tab" data-bs-toggle="tab" data-bs-target="#jbk" type="button" role="tab" aria-controls="jbk"
                    aria-selected="false">
                    <i class="fas fa-file-invoice me-2"></i>Jenis Bukti Kegiatan
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $activeTab == 'output' ? 'active' : '' }} px-4 py-3 rounded-top-3 border ms-2"
                    id="output-tab" data-bs-toggle="tab" data-bs-target="#output" type="button" role="tab"
                    aria-controls="output" aria-selected="false">
                    <i class="fas fa-box-open me-2"></i>Jenis Output
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $activeTab == 'jbd' ? 'active' : '' }} px-4 py-3 rounded-top-3 border ms-2"
                    id="jbd-tab" data-bs-toggle="tab" data-bs-target="#jbd" type="button" role="tab" aria-controls="jbd"
                    aria-selected="false">
                    <i class="fas fa-file-signature me-2"></i>Jenis Bukti Dukung
                </button>
            </li>
        </ul>

        <div class="tab-content" id="kelolaTabContent">
            <!-- Tab Periode -->
            <div class="tab-pane fade {{ $activeTab == 'periode' ? 'show active' : '' }}" id="periode" role="tabpanel"
                aria-labelledby="periode-tab">
                <div class="card border-0 shadow-sm rounded-4 bps-card mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="mb-0 fw-bold" style="color: var(--bps-orange);"><i
                                class="fas fa-calendar-alt me-2"></i>Tambah & Kelola Periode Tahun</h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('desa-cantik.periode.store') }}" method="POST" class="mb-4">
                            @csrf
                            <div class="input-group mb-3" style="max-width: 400px;">
                                <input type="number" class="form-control" name="tahun" placeholder="Contoh: {{ date('Y') }}"
                                    required min="2000" max="2100">
                                <button class="btn btn-orange" type="submit">Tambah Periode</button>
                            </div>
                        </form>

                        @if($periodes->count() > 0)
                            <h6 class="fw-bold text-muted small">Periode Terdaftar:</h6>
                            <div class="table-responsive table-scrollable">
                                <table class="table table-hover align-middle">
                                    <thead class="bg-light">
                                        <tr>
                                            <th width="10%">No</th>
                                            <th>Tahun</th>
                                            <th class="text-center">Status</th>
                                            <th>Jumlah Peserta (Desa)</th>
                                            <th class="text-end" width="15%">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($periodes as $index => $p)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td class="fw-bold">{{ $p->tahun }}</td>
                                                <td class="text-center">
                                                    @if($p->is_active)
                                                        <span class="badge bg-success rounded-pill">Aktif</span>
                                                    @else
                                                        <span class="badge bg-secondary rounded-pill">Non-Aktif</span>
                                                    @endif
                                                </td>
                                                <td>{{ $p->pesertas_count ?? 0 }} Desa</td>
                                                <td class="text-end">
                                                    <form action="{{ route('desa-cantik.periode.toggle-active', $p->id) }}"
                                                        method="POST" class="d-inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        @if($p->is_active)
                                                            <button type="submit"
                                                                class="btn btn-xs btn-outline-warning rounded-pill px-3 py-1 mb-1"
                                                                style="font-size: 0.75rem; font-weight: 600;">
                                                                Nonaktifkan
                                                            </button>
                                                        @else
                                                            <button type="submit"
                                                                class="btn btn-xs btn-outline-success rounded-pill px-3 py-1 mb-1"
                                                                style="font-size: 0.75rem; font-weight: 600;">
                                                                Set Aktif
                                                            </button>
                                                        @endif
                                                    </form>
                                                    <button type="button" class="btn btn-sm btn-outline-danger btn-delete mb-1"
                                                        data-url="{{ route('desa-cantik.periode.destroy', $p->id) }}"
                                                        data-type="Periode Tahun" data-name="{{ $p->tahun }}">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-3">
                                {{ $periodes->appends(['tab' => 'periode'])->links('pagination::bootstrap-5') }}
                            </div>
                        @else
                            <div class="text-center text-muted p-3 bg-light rounded">
                                <small>Belum ada daftar periode terpilih.</small>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Tab Kuota -->
            <div class="tab-pane fade {{ $activeTab == 'kuota' ? 'show active' : '' }}" id="kuota" role="tabpanel"
                aria-labelledby="kuota-tab">
                <div class="card border-0 shadow-sm rounded-4 bps-card ">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="mb-0 fw-bold" style="color: var(--bps-orange);"><i
                                class="fas fa-chart-pie me-2"></i>Pengaturan Alokasi Kuota</h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('desa-cantik.kuota.store') }}" method="POST" class="mb-4">
                            @csrf
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-bold small">Pilih Periode</label>
                                    <select class="form-select" name="periode_id" required>
                                        <option value="">-- Pilih Periode --</option>
                                        @foreach($periodes as $p)
                                            <option value="{{ $p->id }}">{{ $p->tahun }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-bold small">Max Kecamatan</label>
                                    <input type="number" class="form-control" name="max_kecamatan" required min="1">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-bold small">Max Desa</label>
                                    <input type="number" class="form-control" name="max_desa" required min="1">
                                </div>
                            </div>
                            <button class="btn btn-orange rounded-pill w-100" type="submit">Tambah / Update Kuota
                                Baru</button>
                        </form>

                        <hr class="my-4">

                        @if($kuotas->count() > 0)
                            <h6 class="fw-bold text-muted small">Daftar Kuota Tersimpan:</h6>
                            <div class="table-responsive table-scrollable">
                                <table class="table table-hover align-middle">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Tahun/Periode</th>
                                            <th class="text-center">Max Kec</th>
                                            <th class="text-center">Max Desa</th>
                                            <th>Dibuat Oleh</th>
                                            <th>Diubah Oleh</th>
                                            <th class="text-end" width="15%">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($kuotas as $k)
                                            <tr>
                                                <td class="fw-bold">{{ $k->periode->tahun ?? '-' }}</td>
                                                <td class="text-center">{{ $k->max_kecamatan }}</td>
                                                <td class="text-center">{{ $k->max_desa }}</td>
                                                <td>{{ $k->creator->name ?? '-' }}</td>
                                                <td>{{ $k->updater->name ?? '-' }}</td>
                                                <td class="text-end">
                                                    <button type="button" class="btn btn-sm btn-outline-orange"
                                                        data-bs-toggle="modal" data-bs-target="#editKuotaModal{{ $k->id }}">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-danger btn-delete"
                                                        data-url="{{ route('desa-cantik.kuota.destroy', $k->id) }}"
                                                        data-type="Pengaturan Kuota"
                                                        data-name="Periode {{ $k->periode->tahun ?? '' }}">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>

                                            <!-- Modal Edit Kuota -->
                                            <div class="modal fade" id="editKuotaModal{{ $k->id }}" tabindex="-1" aria-hidden="true"
                                                style="text-align: left;">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title fw-bold">Edit Kuota Periode
                                                                {{ $k->periode->tahun ?? '' }}
                                                            </h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                                aria-label="Close"></button>
                                                        </div>
                                                        <form action="{{ route('desa-cantik.kuota.update', $k->id) }}"
                                                            method="POST">
                                                            @csrf
                                                            @method('PUT')
                                                            <div class="modal-body">
                                                                <div class="mb-3">
                                                                    <label class="form-label fw-medium">Max Kecamatan <span
                                                                            class="text-danger">*</span></label>
                                                                    <input type="number" name="max_kecamatan" class="form-control"
                                                                        value="{{ $k->max_kecamatan }}" required min="1">
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label fw-medium">Max Desa <span
                                                                            class="text-danger">*</span></label>
                                                                    <input type="number" name="max_desa" class="form-control"
                                                                        value="{{ $k->max_desa }}" required min="1">
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
                                {{ $kuotas->appends(['tab' => 'kuota'])->links('pagination::bootstrap-5') }}
                            </div>
                        @else
                            <div class="text-center text-muted p-3 bg-light rounded">
                                <small>Belum ada kuota didaftarkan.</small>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Tab Kegiatan -->
            <div class="tab-pane fade {{ $activeTab == 'kegiatan' ? 'show active' : '' }}" id="kegiatan" role="tabpanel"
                aria-labelledby="kegiatan-tab">
                <div class="card border-0 shadow-sm rounded-4 bps-card ">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="mb-0 fw-bold" style="color: var(--bps-orange);"><i
                                class="fas fa-list-check me-2"></i>Pengaturan Kegiatan</h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('desa-cantik.kegiatan.store') }}" method="POST" class="mb-4">
                            @csrf
                            <div class="row align-items-end">
                                <div class="col-md-10 mb-3">
                                    <label class="form-label fw-bold small">Nama Kegiatan Baru</label>
                                    <input type="text" class="form-control" name="nama_kegiatan" required
                                        placeholder="Contoh: Bukti Sosialisasi">
                                </div>
                                <div class="col-md-2 mb-3">
                                    <button class="btn btn-orange rounded-pill w-100" type="submit">Tambah</button>
                                </div>
                            </div>
                        </form>

                        <hr class="my-4">

                        <h6 class="fw-bold text-muted small mb-3">Daftar Kegiatan (Geser untuk mengatur urutannya):</h6>
                        @if($kegiatans->count() > 0)
                            <div class="table-responsive table-scrollable">
                                <table class="table table-hover align-middle">
                                    <thead class="bg-light">
                                        <tr>
                                            <th style="width: 50px;"></th>
                                            <th style="width: 80px;" class="text-center">Urutan</th>
                                            <th>Nama Kegiatan</th>
                                            <th class="text-center">Status</th>
                                            <th class="text-end" width="15%">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="sortable-kegiatan">
                                        @foreach($kegiatans as $index => $keg)
                                            <tr data-id="{{ $keg->id }}">
                                                <td class="text-center" style="cursor: grab;">
                                                    <i class="fas fa-grip-vertical text-muted"></i>
                                                </td>
                                                <td class="text-center sortable-urutan fw-bold">{{ $keg->urutan }}</td>
                                                <td>{{ $keg->nama_kegiatan }}</td>
                                                <td class="text-center">
                                                    @if($keg->is_active)
                                                        <span class="badge bg-success rounded-pill">Aktif</span>
                                                    @else
                                                        <span class="badge bg-secondary rounded-pill">Non-Aktif</span>
                                                    @endif
                                                </td>
                                                <td class="text-end">
                                                    <form action="{{ route('desa-cantik.kegiatan.toggle-active', $keg->id) }}"
                                                        method="POST" class="d-inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit"
                                                            class="btn btn-sm {{ $keg->is_active ? 'btn-outline-warning' : 'btn-outline-success' }} mb-1"
                                                            title="{{ $keg->is_active ? 'Non-aktifkan' : 'Aktifkan' }}">
                                                            <i class="fas fa-{{ $keg->is_active ? 'times' : 'check' }}"></i>
                                                        </button>
                                                    </form>
                                                    <button type="button" class="btn btn-sm btn-outline-orange mb-1"
                                                        data-bs-toggle="modal" data-bs-target="#editKegiatanModal{{ $keg->id }}">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-danger btn-delete mb-1"
                                                        data-url="{{ route('desa-cantik.kegiatan.destroy', $keg->id) }}"
                                                        data-type="Kegiatan" data-name="{{ $keg->nama_kegiatan }}">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>

                                            <!-- Modal Edit Kegiatan -->
                                            <div class="modal fade" id="editKegiatanModal{{ $keg->id }}" tabindex="-1"
                                                aria-hidden="true" style="text-align: left;">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title fw-bold">Edit Kegiatan</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                                aria-label="Close"></button>
                                                        </div>
                                                        <form action="{{ route('desa-cantik.kegiatan.update', $keg->id) }}"
                                                            method="POST">
                                                            @csrf
                                                            @method('PUT')
                                                            <div class="modal-body">
                                                                <div class="mb-3">
                                                                    <label class="form-label fw-medium">Nama Kegiatan <span
                                                                            class="text-danger">*</span></label>
                                                                    <input type="text" name="nama_kegiatan" class="form-control"
                                                                        value="{{ $keg->nama_kegiatan }}" required>
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
                                {{ $kegiatans->appends(['tab' => 'kegiatan'])->links('pagination::bootstrap-5') }}
                            </div>
                        @else
                            <div class="text-center text-muted p-3 bg-light rounded">
                                <small>Belum ada kegiatan didaftarkan.</small>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Tab Jenis Bukti Kegiatan -->
            <div class="tab-pane fade {{ $activeTab == 'jbk' ? 'show active' : '' }}" id="jbk" role="tabpanel"
                aria-labelledby="jbk-tab">
                <div class="card border-0 shadow-sm rounded-4 bps-card ">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="mb-0 fw-bold" style="color: var(--bps-orange);"><i
                                class="fas fa-file-invoice me-2"></i>Pengaturan Jenis Bukti Kegiatan</h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('desa-cantik.jenis-bukti-kegiatan.store') }}" method="POST" class="mb-4">
                            @csrf
                            <div class="row align-items-end">
                                <div class="col-md-7 mb-3">
                                    <label class="form-label fw-bold small">Nama Bukti</label>
                                    <input type="text" class="form-control" name="nama_bukti" required
                                        placeholder="Contoh: Laporan Kegiatan">
                                </div>
                                <div class="col-md-2 mb-3">
                                    <div class="form-check pt-2">
                                        <input class="form-check-input" type="checkbox" name="is_wajib" value="1"
                                            id="isWajibJbk">
                                        <label class="form-check-label fw-bold small" for="isWajibJbk">
                                            Wajib?
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <button class="btn btn-orange rounded-pill w-100" type="submit">Tambah</button>
                                </div>
                            </div>
                        </form>

                        <hr class="my-4">

                        @if($jenisBuktiKegiatans->count() > 0)
                            <div class="table-responsive table-scrollable">
                                <table class="table table-hover align-middle">
                                    <thead class="bg-light">
                                        <tr>
                                            <th width="10%">No</th>
                                            <th>Nama Bukti</th>
                                            <th class="text-center">Label</th>
                                            <th class="text-end" width="15%">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($jenisBuktiKegiatans as $index => $item)
                                            <tr>
                                                <td>{{ ($jenisBuktiKegiatans->currentPage() - 1) * $jenisBuktiKegiatans->perPage() + $index + 1 }}
                                                </td>
                                                <td>{{ $item->nama_bukti }}</td>
                                                <td class="text-center">
                                                    @if($item->is_wajib)
                                                        <span class="badge bg-danger rounded-pill">Wajib</span>
                                                    @else
                                                        <span class="badge bg-secondary rounded-pill">Opsional</span>
                                                    @endif
                                                </td>
                                                <td class="text-end">
                                                    <button type="button" class="btn btn-sm btn-outline-orange"
                                                        data-bs-toggle="modal" data-bs-target="#editJbkModal{{ $item->id }}">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-danger btn-delete"
                                                        data-url="{{ route('desa-cantik.jenis-bukti-kegiatan.destroy', $item->id) }}"
                                                        data-type="Jenis Bukti Kegiatan" data-name="{{ $item->nama_bukti }}">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>

                                            <!-- Modal Edit -->
                                            <div class="modal fade" id="editJbkModal{{ $item->id }}" tabindex="-1"
                                                aria-hidden="true" style="text-align: left;">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title fw-bold">Edit Jenis Bukti Kegiatan</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                                aria-label="Close"></button>
                                                        </div>
                                                        <form
                                                            action="{{ route('desa-cantik.jenis-bukti-kegiatan.update', $item->id) }}"
                                                            method="POST">
                                                            @csrf
                                                            @method('PUT')
                                                            <div class="modal-body">
                                                                <div class="mb-3">
                                                                    <label class="form-label fw-medium">Nama Bukti <span
                                                                            class="text-danger">*</span></label>
                                                                    <input type="text" name="nama_bukti" class="form-control"
                                                                        value="{{ $item->nama_bukti }}" required>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <div class="form-check">
                                                                        <input class="form-check-input" type="checkbox"
                                                                            name="is_wajib" value="1"
                                                                            id="editIsWajibJbk{{ $item->id }}" {{ $item->is_wajib ? 'checked' : '' }}>
                                                                        <label class="form-check-label fw-medium"
                                                                            for="editIsWajibJbk{{ $item->id }}">
                                                                            Jadikan Wajib
                                                                        </label>
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
                                {{ $jenisBuktiKegiatans->appends(['tab' => 'jbk'])->links('pagination::bootstrap-5') }}
                            </div>
                        @else
                            <div class="text-center text-muted p-3 bg-light rounded">
                                <small>Belum ada jenis bukti kegiatan didaftarkan.</small>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Tab Jenis Output -->
            <div class="tab-pane fade {{ $activeTab == 'output' ? 'show active' : '' }}" id="output" role="tabpanel"
                aria-labelledby="output-tab">
                <div class="card border-0 shadow-sm rounded-4 bps-card ">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="mb-0 fw-bold" style="color: var(--bps-orange);"><i
                                class="fas fa-box-open me-2"></i>Pengaturan Jenis Output</h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('desa-cantik.jenis-output.store') }}" method="POST" class="mb-4">
                            @csrf
                            <div class="row align-items-end">
                                <div class="col-md-7 mb-3">
                                    <label class="form-label fw-bold small">Nama Output <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="nama_output" required
                                        placeholder="Contoh: Dokumen Publikasi">
                                </div>
                                <div class="col-md-2 mb-3">
                                    <div class="form-check pt-2">
                                        <input class="form-check-input" type="checkbox" name="is_wajib" value="1"
                                            id="isWajibOutput">
                                        <label class="form-check-label fw-bold small" for="isWajibOutput">
                                            Wajib?
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <button class="btn btn-orange rounded-pill w-100" type="submit">Tambah</button>
                                </div>
                            </div>
                        </form>

                        <hr class="my-4">

                        @if($jenisOutputs->count() > 0)
                            <div class="table-responsive table-scrollable">
                                <table class="table table-hover align-middle">
                                    <thead class="bg-light">
                                        <tr>
                                            <th width="10%">No</th>
                                            <th>Nama Output</th>
                                            <th class="text-center">Label</th>
                                            <th class="text-end" width="15%">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($jenisOutputs as $index => $item)
                                            <tr>
                                                <td>{{ ($jenisOutputs->currentPage() - 1) * $jenisOutputs->perPage() + $index + 1 }}
                                                </td>
                                                <td>{{ $item->nama_output }}</td>
                                                <td class="text-center">
                                                    @if($item->is_wajib)
                                                        <span class="badge bg-danger rounded-pill">Wajib</span>
                                                    @else
                                                        <span class="badge bg-secondary rounded-pill">Opsional</span>
                                                    @endif
                                                </td>
                                                <td class="text-end">
                                                    <button type="button" class="btn btn-sm btn-outline-orange"
                                                        data-bs-toggle="modal" data-bs-target="#editOutputModal{{ $item->id }}">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-danger btn-delete"
                                                        data-url="{{ route('desa-cantik.jenis-output.destroy', $item->id) }}"
                                                        data-type="Jenis Output" data-name="{{ $item->nama_output }}">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>

                                            <!-- Modal Edit -->
                                            <div class="modal fade" id="editOutputModal{{ $item->id }}" tabindex="-1"
                                                aria-hidden="true" style="text-align: left;">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title fw-bold">Edit Jenis Output</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                                aria-label="Close"></button>
                                                        </div>
                                                        <form action="{{ route('desa-cantik.jenis-output.update', $item->id) }}"
                                                            method="POST">
                                                            @csrf
                                                            @method('PUT')
                                                            <div class="modal-body">
                                                                <div class="mb-3">
                                                                    <label class="form-label fw-medium">Nama Output <span
                                                                            class="text-danger">*</span></label>
                                                                    <input type="text" name="nama_output" class="form-control"
                                                                        value="{{ $item->nama_output }}" required>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <div class="form-check">
                                                                        <input class="form-check-input" type="checkbox"
                                                                            name="is_wajib" value="1"
                                                                            id="editIsWajibOutput{{ $item->id }}" {{ $item->is_wajib ? 'checked' : '' }}>
                                                                        <label class="form-check-label fw-medium"
                                                                            for="editIsWajibOutput{{ $item->id }}">
                                                                            Jadikan Wajib
                                                                        </label>
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
                                {{ $jenisOutputs->appends(['tab' => 'output'])->links('pagination::bootstrap-5') }}
                            </div>
                        @else
                            <div class="text-center text-muted p-3 bg-light rounded">
                                <small>Belum ada jenis output didaftarkan.</small>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Tab Jenis Bukti Dukung -->
            <div class="tab-pane fade {{ $activeTab == 'jbd' ? 'show active' : '' }}" id="jbd" role="tabpanel"
                aria-labelledby="jbd-tab">
                <div class="card border-0 shadow-sm rounded-4 bps-card ">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="mb-0 fw-bold" style="color: var(--bps-orange);"><i
                                class="fas fa-file-signature me-2"></i>Pengaturan Jenis Bukti Dukung</h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('desa-cantik.jenis-bukti-dukung.store') }}" method="POST" class="mb-4">
                            @csrf
                            <div class="row align-items-end">
                                <div class="col-md-7 mb-3">
                                    <label class="form-label fw-bold small">Nama Bukti <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="nama_bukti" required
                                        placeholder="Contoh: SK Kades">
                                </div>
                                <div class="col-md-2 mb-3">
                                    <div class="form-check pt-2">
                                        <input class="form-check-input" type="checkbox" name="is_wajib" value="1"
                                            id="isWajibJbd">
                                        <label class="form-check-label fw-bold small" for="isWajibJbd">
                                            Wajib?
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <button class="btn btn-orange rounded-pill w-100" type="submit">Tambah</button>
                                </div>
                            </div>
                        </form>

                        <hr class="my-4">

                        @if($jenisBuktiDukungs->count() > 0)
                            <div class="table-responsive table-scrollable">
                                <table class="table table-hover align-middle">
                                    <thead class="bg-light">
                                        <tr>
                                            <th width="10%">No</th>
                                            <th>Nama Bukti</th>
                                            <th class="text-center">Label</th>
                                            <th class="text-end" width="15%">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($jenisBuktiDukungs as $index => $item)
                                            <tr>
                                                <td>{{ ($jenisBuktiDukungs->currentPage() - 1) * $jenisBuktiDukungs->perPage() + $index + 1 }}
                                                </td>
                                                <td>{{ $item->nama_bukti }}</td>
                                                <td class="text-center">
                                                    @if($item->is_wajib)
                                                        <span class="badge bg-danger rounded-pill">Wajib</span>
                                                    @else
                                                        <span class="badge bg-secondary rounded-pill">Opsional</span>
                                                    @endif
                                                </td>
                                                <td class="text-end">
                                                    <button type="button" class="btn btn-sm btn-outline-orange"
                                                        data-bs-toggle="modal" data-bs-target="#editJbdModal{{ $item->id }}">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-danger btn-delete"
                                                        data-url="{{ route('desa-cantik.jenis-bukti-dukung.destroy', $item->id) }}"
                                                        data-type="Jenis Bukti Dukung" data-name="{{ $item->nama_bukti }}">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>

                                            <!-- Modal Edit -->
                                            <div class="modal fade" id="editJbdModal{{ $item->id }}" tabindex="-1"
                                                aria-hidden="true" style="text-align: left;">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title fw-bold">Edit Jenis Bukti Dukung</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                                aria-label="Close"></button>
                                                        </div>
                                                        <form
                                                            action="{{ route('desa-cantik.jenis-bukti-dukung.update', $item->id) }}"
                                                            method="POST">
                                                            @csrf
                                                            @method('PUT')
                                                            <div class="modal-body">
                                                                <div class="mb-3">
                                                                    <label class="form-label fw-medium">Nama Bukti <span
                                                                            class="text-danger">*</span></label>
                                                                    <input type="text" name="nama_bukti" class="form-control"
                                                                        value="{{ $item->nama_bukti }}" required>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <div class="form-check">
                                                                        <input class="form-check-input" type="checkbox"
                                                                            name="is_wajib" value="1"
                                                                            id="editIsWajibJbd{{ $item->id }}" {{ $item->is_wajib ? 'checked' : '' }}>
                                                                        <label class="form-check-label fw-medium"
                                                                            for="editIsWajibJbd{{ $item->id }}">
                                                                            Jadikan Wajib
                                                                        </label>
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
                                {{ $jenisBuktiDukungs->appends(['tab' => 'jbd'])->links('pagination::bootstrap-5') }}
                            </div>
                        @else
                            <div class="text-center text-muted p-3 bg-light rounded">
                                <small>Belum ada jenis bukti dukung didaftarkan.</small>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Global Form untuk SweetAlert Delete -->
    <form id="globalDeleteForm" method="POST" action="" style="display: none;">
        @csrf
        @method('DELETE')
    </form>

    @push('styles')
        <style>
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

            .scrollable-tabs {
                flex-wrap: nowrap;
                overflow-x: auto;
                overflow-y: hidden;
                white-space: nowrap;
                -webkit-overflow-scrolling: touch;
            }

            .scrollable-tabs::-webkit-scrollbar {
                height: 4px;
            }

            .scrollable-tabs::-webkit-scrollbar-thumb {
                background-color: #cbd5e1;
                border-radius: 4px;
            }

            #sortable-kegiatan tr.sortable-ghost {
                opacity: 0.4;
                background-color: #f8fafc;
            }

            .table-scrollable {
                max-height: 400px;
                overflow-y: auto;
            }

            .table-scrollable thead th {
                position: sticky;
                top: 0;
                z-index: 10;
                background-color: #f8fafc;
                /* match bg-light */
            }
        </style>
        <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.min.css" rel="stylesheet">
    @endpush

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.all.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // SweetAlert Delete Logic
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

                // SortableJS Logic
                const sortableList = document.getElementById('sortable-kegiatan');
                if (sortableList) {
                    new Sortable(sortableList, {
                        animation: 150,
                        ghostClass: 'sortable-ghost',
                        handle: 'td:first-child', // Only draggable from the grip icon
                        onEnd: function (evt) {
                            // Collect rows
                            const rows = sortableList.querySelectorAll('tr');
                            let orderData = [];
                            let startIndex = {{ ($kegiatans->currentPage() - 1) * $kegiatans->perPage() }};

                            rows.forEach((row, index) => {
                                let newUrutan = startIndex + index + 1;
                                row.querySelector('.sortable-urutan').textContent = newUrutan;
                                orderData.push({
                                    id: row.getAttribute('data-id'),
                                    urutan: newUrutan
                                });
                            });

                            // Send AJAX request
                            fetch('/desa-cantik/kelola/kegiatan/reorder', {
                                                method: 'PATCH',
                                                headers: {
                                                    'Content-Type': 'application/json',
                                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                                    'Accept': 'application/json'
                                                },
                                                body: JSON.stringify({ orders: orderData })
                                            })
                                                .then(response => response.json())
                                                .then(data => {
                                                    if (data.success) {
                                                        // Optional check success
                                                        /* Swal.fire({
                                                            icon: 'success',
                                                            title: 'Berhasil',
                                                            text: 'Urutan berhasil disimpan.',
                                                            timer: 1500,
                                                            showConfirmButton: false
                                                        }); */
                                                    }
                                                })
                                                .catch(error => {
                                                    console.error('Error reordering:', error);
                                                    Swal.fire('Error', 'Gagal menyimpan urutan baru', 'error');
                                                });
                                        }
                                    });
                                }
                            });
                        </script>
    @endpush
@endsection