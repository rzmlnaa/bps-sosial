@extends('layouts.admin')

@section('title', 'Kelola Fenomena')

@section('content')
    <div class="">
        <!-- Header -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
            <div>
                <h2 class="fw-bold mb-1" style="color: var(--bps-orange);">Kelola Fenomena</h2>
                <p class="text-muted mb-0">Kelola master data untuk fenomena</p>
            </div>

            <div class="mt-3 mt-md-0">
                <a href="{{ url('/fenomena') }}" class="btn btn-outline-secondary shadow-sm" style="border-radius: 8px;">
                    <i class="fas fa-arrow-left me-2"></i>Kembali
                </a>
            </div>
        </div>

        <!-- Tabs Navigation -->
        @php
            $activeTab = session('active_tab', 'lap-usaha');
        @endphp
        <ul class="nav nav-pills mb-4 gap-2" id="pills-tab" role="tablist">
            <li class="nav-item" role="presentation">
                <button
                    class="nav-link {{ $activeTab == 'lap-usaha' ? 'active' : 'bg-warning-subtle text-dark' }} rounded-pill px-4"
                    id="pills-lap-usaha-tab" data-bs-toggle="pill" data-bs-target="#pills-lap-usaha" type="button"
                    role="tab" style="{{ $activeTab == 'lap-usaha' ? 'background-color: var(--bps-blue);' : '' }}">
                    <i class="fas fa-industry me-2"></i>Kode Lap Usaha
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button
                    class="nav-link {{ $activeTab == 'indikator' ? 'active' : 'bg-warning-subtle text-dark' }} rounded-pill px-4"
                    id="pills-indikator-tab" data-bs-toggle="pill" data-bs-target="#pills-indikator" type="button"
                    role="tab" style="{{ $activeTab == 'indikator' ? 'background-color: var(--bps-blue);' : '' }}">
                    <i class="fas fa-chart-line me-2"></i>Kode Indikator
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button
                    class="nav-link {{ $activeTab == 'jenis' ? 'active' : 'bg-warning-subtle text-dark' }} rounded-pill px-4"
                    id="pills-jenis-tab" data-bs-toggle="pill" data-bs-target="#pills-jenis" type="button" role="tab"
                    style="{{ $activeTab == 'jenis' ? 'background-color: var(--bps-blue);' : '' }}">
                    <i class="fas fa-list me-2"></i>Jenis Fenomena
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button
                    class="nav-link {{ $activeTab == 'sumber' ? 'active' : 'bg-warning-subtle text-dark' }} rounded-pill px-4"
                    id="pills-sumber-tab" data-bs-toggle="pill" data-bs-target="#pills-sumber" type="button" role="tab"
                    style="{{ $activeTab == 'sumber' ? 'background-color: var(--bps-blue);' : '' }}">
                    <i class="fas fa-newspaper me-2"></i>Sumber Berita
                </button>
            </li>
        </ul>

        <div class="tab-content" id="pills-tabContent">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            <!-- Tab 1: Kode Lap Usaha -->
            <div class="tab-pane fade {{ $activeTab == 'lap-usaha' ? 'show active' : '' }}" id="pills-lap-usaha"
                role="tabpanel">
                <div class="card border-0 shadow-sm" style="border-radius: 12px; background: #fff;">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="fw-bold mb-0">Manajemen Kode Lapangan Usaha</h5>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSektorUsahaModal"
                                style="border-radius: 8px;">
                                <i class="fas fa-plus me-1"></i> Tambah Data
                            </button>
                        </div>
                        <!-- Desktop Table View -->
                        <div class="table-responsive d-none d-md-block">
                            <table class="table table-hover align-middle table-bordered" id="tableSektorUsaha">
                                <thead class="table-light">
                                    <tr>
                                        <th width="5%" class="text-center">No</th>
                                        <th width="20%">Kode</th>
                                        <th>Nama Lapangan Usaha</th>
                                        <th width="20%" class="text-center">Informasi</th>
                                        <th width="10%" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($sektorUsahas as $key => $item)
                                        <tr>
                                            <td class="text-center">{{ $key + 1 }}</td>
                                            <td class="fw-bold">{{ $item->kode }}</td>
                                            <td>{{ $item->nama }}</td>
                                            <td class="text-start" style="font-size: 0.85rem;">
                                                <div class="d-flex flex-column text-muted">
                                                    @if($item->created_at)
                                                        <div>
                                                            <i class="fas fa-user-plus me-1 text-success" title="User Add"></i>
                                                            {{ $item->userAdd ? $item->userAdd->name : 'Sistem' }}
                                                            <br>
                                                            <small
                                                                class="ms-4 text-secondary">{{ $item->created_at->format('d/m/Y H:i') }}</small>
                                                        </div>
                                                    @endif

                                                    @if($item->updated_at && $item->updated_at != $item->created_at)
                                                        <div class="mt-1 border-top pt-1">
                                                            <i class="fas fa-user-edit me-1 text-primary" title="User Edit"></i>
                                                            {{ $item->userUpdate ? $item->userUpdate->name : 'Sistem' }}
                                                            <br>
                                                            <small
                                                                class="ms-4 text-secondary">{{ $item->updated_at->format('d/m/Y H:i') }}</small>
                                                        </div>
                                                    @endif
                                                    <div class="mt-1 border-top pt-1 text-info">
                                                        <i class="fas fa-link me-1"></i>
                                                        Digunakan: <span class="fw-bold">{{ $item->fenomenas_count }}</span>
                                                        data fenomena
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <button class="btn btn-sm btn-warning mb-1" data-bs-toggle="modal"
                                                    data-bs-target="#editSektorUsahaModal{{ $item->id }}" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-danger mb-1"
                                                    onclick="confirmDelete('{{ url('/sektor-usaha/' . $item->id) }}', '{{ $item->nama }}')"
                                                    title="{{ $item->fenomenas_count > 0 ? 'Tidak dapat dihapus karena sudah digunakan' : 'Hapus' }}"
                                                    {{ $item->fenomenas_count > 0 ? 'disabled' : '' }}>
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>

                                        <!-- Modal Edit included in the loop logic remains same -->
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">Belum ada data kode lapangan
                                                usaha.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Mobile Card View -->
                        <div class="d-md-none">
                            @forelse($sektorUsahas as $key => $item)
                                <div class="card mb-3 border shadow-sm" style="border-radius: 12px;">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <span class="badge bg-light text-dark border p-2 fw-bold"
                                                style="font-size: 0.9rem;">
                                                {{ $item->kode }}
                                            </span>
                                            <div class="d-flex gap-2">
                                                <button class="btn btn-warning btn-sm" data-bs-toggle="modal"
                                                    data-bs-target="#editSektorUsahaModal{{ $item->id }}">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-danger btn-sm"
                                                    onclick="confirmDelete('{{ url('/sektor-usaha/' . $item->id) }}', '{{ $item->nama }}')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <h6 class="fw-bold mb-2">{{ $item->nama }}</h6>
                                        <div class="text-muted border-top pt-2 mt-2" style="font-size: 0.75rem;">
                                            <div class="d-flex justify-content-between">
                                                <span><i class="fas fa-user-plus me-1 text-success"></i>
                                                    {{ $item->userAdd ? str($item->userAdd->name)->words(2, '') : 'Sistem' }}</span>
                                                <span>{{ $item->created_at ? $item->created_at->format('d/m/y H:i') : '-' }}</span>
                                            </div>
                                            @if($item->updated_at && $item->updated_at != $item->created_at)
                                                <div class="d-flex justify-content-between mt-1">
                                                    <span><i class="fas fa-user-edit me-1 text-primary"></i>
                                                        {{ $item->userUpdate ? str($item->userUpdate->name)->words(2, '') : 'Sistem' }}</span>
                                                    <span>{{ $item->updated_at->format('d/m/y H:i') }}</span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- Modals need to be outside rows but inside the tab-pane or loop -->
                                <!-- Re-using same modals as desktop -->
                            @empty
                                <div class="text-center py-4 text-muted border rounded">
                                    Belum ada data kode lapangan usaha.
                                </div>
                            @endforelse
                        </div>

                        <!-- Re-include Edit Modals logic so they are available for both views -->
                        @foreach($sektorUsahas as $item)
                            <!-- Modal Edit -->
                            <div class="modal fade" id="editSektorUsahaModal{{ $item->id }}" tabindex="-1"
                                aria-labelledby="editSektorUsahaModalLabel{{ $item->id }}" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="{{ route('sektor-usaha.update', $item->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="editSektorUsahaModalLabel{{ $item->id }}">Edit Kode
                                                    Lapangan Usaha</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body text-start">
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Kode (A-Z)</label>
                                                    <input type="text" class="form-control text-uppercase" name="kode"
                                                        value="{{ $item->kode }}" required pattern="[A-Za-z]+"
                                                        title="Hanya huruf alfabet yang diperbolehkan"
                                                        oninput="this.value = this.value.toUpperCase().replace(/[^A-Z]/g, '')">
                                                    <small class="text-muted">Contoh: A, B, ABC</small>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Nama Lapangan Usaha</label>
                                                    <input type="text" class="form-control" name="nama"
                                                        value="{{ $item->nama }}" required>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary"
                                                    data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Tab 2: Kode Indikator -->
            <div class="tab-pane fade {{ $activeTab == 'indikator' ? 'show active' : '' }}" id="pills-indikator"
                role="tabpanel">
                <div class="card border-0 shadow-sm" style="border-radius: 12px; background: #fff;">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="fw-bold mb-0">Manajemen Kode Indikator</h5>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addIndikatorModal"
                                style="border-radius: 8px;">
                                <i class="fas fa-plus me-1"></i> Tambah Data
                            </button>
                        </div>

                        <!-- Desktop Table View -->
                        <div class="table-responsive d-none d-md-block">
                            <table class="table table-hover align-middle table-bordered" id="tableIndikator">
                                <thead class="table-light">
                                    <tr>
                                        <th width="5%" class="text-center">No</th>
                                        <th width="15%">Kode</th>
                                        <th>Nama Indikator</th>
                                        <th width="15%">Kelompok</th>
                                        <th width="10%" class="text-center">Status</th>
                                        <th width="20%" class="text-center">Informasi</th>
                                        <th width="10%" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($indikators as $key => $item)
                                        <tr>
                                            <td class="text-center">{{ $key + 1 }}</td>
                                            <td class="fw-bold">{{ $item->kode }}</td>
                                            <td>{{ $item->nama }}</td>
                                            <td>
                                                <span
                                                    class="badge {{ strtolower($item->kelompok) == 'utama' ? 'bg-primary' : 'bg-secondary' }}">
                                                    {{ ucfirst($item->kelompok) }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <div class="form-check form-switch d-flex justify-content-center">
                                                    <input class="form-check-input toggle-indikator" type="checkbox"
                                                        role="switch" data-id="{{ $item->id }}" {{ $item->is_active ? 'checked' : '' }} style="cursor: pointer; transform: scale(1.2);">
                                                </div>
                                            </td>
                                            <td class="text-start" style="font-size: 0.85rem;">
                                                <div class="d-flex flex-column text-muted">
                                                    @if($item->created_at)
                                                        <div>
                                                            <i class="fas fa-user-plus me-1 text-success" title="User Add"></i>
                                                            {{ $item->userAdd ? $item->userAdd->name : 'Sistem' }}
                                                            <br>
                                                            <small
                                                                class="ms-4 text-secondary">{{ $item->created_at->format('d/m/Y H:i') }}</small>
                                                        </div>
                                                    @endif

                                                    @if($item->updated_at && $item->updated_at != $item->created_at)
                                                        <div class="mt-1 border-top pt-1">
                                                            <i class="fas fa-user-edit me-1 text-primary" title="User Edit"></i>
                                                            {{ $item->userUpdate ? $item->userUpdate->name : 'Sistem' }}
                                                            <br>
                                                            <small
                                                                class="ms-4 text-secondary">{{ $item->updated_at->format('d/m/Y H:i') }}</small>
                                                        </div>
                                                    @endif
                                                    <div class="mt-1 border-top pt-1 text-info">
                                                        <i class="fas fa-link me-1"></i>
                                                        Digunakan: <span class="fw-bold">{{ $item->fenomenas_count }}</span>
                                                        data fenomena
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <button class="btn btn-sm btn-warning mb-1" data-bs-toggle="modal"
                                                    data-bs-target="#editIndikatorModal{{ $item->id }}" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-danger mb-1"
                                                    onclick="confirmDelete('{{ url('/indikator/' . $item->id) }}', '{{ $item->nama }}')"
                                                    title="{{ $item->fenomenas_count > 0 ? 'Tidak dapat dihapus karena sudah digunakan' : 'Hapus' }}"
                                                    {{ $item->fenomenas_count > 0 ? 'disabled' : '' }}>
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">Belum ada data kode indikator.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Mobile Card View -->
                        <div class="d-md-none">
                            @forelse($indikators as $key => $item)
                                <div class="card mb-3 border shadow-sm" style="border-radius: 12px;">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="badge bg-light text-dark border p-2 fw-bold"
                                                    style="font-size: 0.9rem;">
                                                    #{{ $item->kode }}
                                                </span>
                                                <span
                                                    class="badge {{ strtolower($item->kelompok) == 'utama' ? 'bg-primary' : 'bg-secondary' }}">
                                                    {{ ucfirst($item->kelompok) }}
                                                </span>
                                            </div>
                                            <div class="form-check form-switch p-0 m-0">
                                                <input class="form-check-input toggle-indikator m-0" type="checkbox"
                                                    role="switch" data-id="{{ $item->id }}" {{ $item->is_active ? 'checked' : '' }} style="cursor: pointer; width: 3.5em; height: 1.75em;">
                                            </div>
                                        </div>

                                        <h6 class="fw-bold mb-3">{{ $item->nama }}</h6>

                                        <div class="d-flex justify-content-between align-items-center border-top pt-2">
                                            <div class="text-muted" style="font-size: 0.75rem;">
                                                <div><i class="fas fa-user-plus me-1 text-success"></i>
                                                    {{ $item->userAdd ? str($item->userAdd->name)->words(2, '') : 'Sistem' }}
                                                </div>
                                                <div class="mt-1"><i class="fas fa-clock me-1"></i>
                                                    {{ $item->created_at ? $item->created_at->format('d/m/y H:i') : '-' }}</div>
                                            </div>
                                            <div class="d-flex gap-2">
                                                <button class="btn btn-warning btn-sm" data-bs-toggle="modal"
                                                    data-bs-target="#editIndikatorModal{{ $item->id }}">
                                                    <i class="fas fa-edit"></i> Edit
                                                </button>
                                                <button type="button" class="btn btn-danger btn-sm"
                                                    onclick="confirmDelete('{{ url('/indikator/' . $item->id) }}', '{{ $item->nama }}')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-4 text-muted border rounded">
                                    Belum ada data kode indikator.
                                </div>
                            @endforelse
                        </div>

                        <!-- Re-include Edit Modals logic outside loop -->
                        @foreach($indikators as $item)
                            <!-- Modal Edit Indikator -->
                            <div class="modal fade" id="editIndikatorModal{{ $item->id }}" tabindex="-1"
                                aria-labelledby="editIndikatorModalLabel{{ $item->id }}" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="{{ route('indikator.update', $item->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="editIndikatorModalLabel{{ $item->id }}">Edit Kode
                                                    Indikator</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body text-start">
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Kode (Angka)</label>
                                                    <input type="text" class="form-control" name="kode"
                                                        value="{{ $item->kode }}" required pattern="[0-9]+"
                                                        oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                                    <small class="text-muted">Contoh: 01, 02</small>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Nama Indikator</label>
                                                    <input type="text" class="form-control" name="nama"
                                                        value="{{ $item->nama }}" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Kelompok</label>
                                                    <select name="kelompok" class="form-select" required>
                                                        <option value="utama" {{ strtolower($item->kelompok) == 'utama' ? 'selected' : '' }}>
                                                            Utama</option>
                                                        <option value="dampak" {{ strtolower($item->kelompok) == 'dampak' ? 'selected' : '' }}>Dampak</option>
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold d-block">Status Aktif</label>
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" role="switch"
                                                            name="is_active" id="flexSwitchCheckChecked{{ $item->id }}" {{ $item->is_active ? 'checked' : '' }} value="1">
                                                        <label class="form-check-label"
                                                            for="flexSwitchCheckChecked{{ $item->id }}">Ceklis untuk menyalakan
                                                            indikator</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary"
                                                    data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Tab 3: Jenis Fenomena -->
            <div class="tab-pane fade {{ $activeTab == 'jenis' ? 'show active' : '' }}" id="pills-jenis" role="tabpanel">
                <div class="card border-0 shadow-sm" style="border-radius: 12px; background: #fff;">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="fw-bold mb-0">Manajemen Jenis Fenomena</h5>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addJenisModal"
                                style="border-radius: 8px;">
                                <i class="fas fa-plus me-1"></i> Tambah Data
                            </button>
                        </div>

                        <!-- Desktop Table View -->
                        <div class="table-responsive d-none d-md-block">
                            <table class="table table-hover align-middle table-bordered" id="tableJenis">
                                <thead class="table-light">
                                    <tr>
                                        <th width="5%" class="text-center">No</th>
                                        <th>Nama Jenis Fenomena</th>
                                        <th width="25%" class="text-center">Informasi</th>
                                        <th width="10%" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($jenisFenomenas as $key => $item)
                                        <tr>
                                            <td class="text-center">{{ $key + 1 }}</td>
                                            <td>{{ $item->nama }}</td>
                                            <td class="text-start" style="font-size: 0.85rem;">
                                                <div class="d-flex flex-column text-muted">
                                                    @if($item->created_at)
                                                        <div>
                                                            <i class="fas fa-user-plus me-1 text-success" title="User Add"></i>
                                                            {{ $item->userAdd ? $item->userAdd->name : 'Sistem' }}
                                                            <br>
                                                            <small
                                                                class="ms-4 text-secondary">{{ $item->created_at->format('d/m/Y H:i') }}</small>
                                                        </div>
                                                    @endif

                                                    @if($item->updated_at && $item->updated_at != $item->created_at)
                                                        <div class="mt-1 border-top pt-1">
                                                            <i class="fas fa-user-edit me-1 text-primary" title="User Edit"></i>
                                                            {{ $item->userUpdate ? $item->userUpdate->name : 'Sistem' }}
                                                            <br>
                                                            <small
                                                                class="ms-4 text-secondary">{{ $item->updated_at->format('d/m/Y H:i') }}</small>
                                                        </div>
                                                    @endif
                                                    <div class="mt-1 border-top pt-1 text-info">
                                                        <i class="fas fa-link me-1"></i>
                                                        Digunakan: <span class="fw-bold">{{ $item->fenomenas_count }}</span>
                                                        data fenomena
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <button class="btn btn-sm btn-warning mb-1" data-bs-toggle="modal"
                                                    data-bs-target="#editJenisModal{{ $item->id }}" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-danger mb-1"
                                                    onclick="confirmDelete('{{ url('/jenis-fenomena/' . $item->id) }}', '{{ $item->nama }}')"
                                                    title="{{ $item->fenomenas_count > 0 ? 'Tidak dapat dihapus karena sudah digunakan' : 'Hapus' }}"
                                                    {{ $item->fenomenas_count > 0 ? 'disabled' : '' }}>
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">Belum ada data jenis fenomena.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Mobile Card View -->
                        <div class="d-md-none">
                            @forelse($jenisFenomenas as $key => $item)
                                <div class="card mb-3 border shadow-sm" style="border-radius: 12px;">
                                    <div class="card-body p-3">
                                        <h6 class="fw-bold mb-3">{{ $item->nama }}</h6>
                                        <div class="d-flex justify-content-between align-items-center border-top pt-2">
                                            <div class="text-muted" style="font-size: 0.75rem;">
                                                <div><i class="fas fa-user-plus me-1 text-success"></i>
                                                    {{ $item->userAdd ? str($item->userAdd->name)->words(2, '') : 'Sistem' }}
                                                </div>
                                                <div class="mt-1"><i class="fas fa-clock me-1"></i>
                                                    {{ $item->created_at ? $item->created_at->format('d/m/y H:i') : '-' }}</div>
                                            </div>
                                            <div class="d-flex gap-2">
                                                <button class="btn btn-warning btn-sm" data-bs-toggle="modal"
                                                    data-bs-target="#editJenisModal{{ $item->id }}">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-danger btn-sm"
                                                    onclick="confirmDelete('{{ url('/jenis-fenomena/' . $item->id) }}', '{{ $item->nama }}')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-4 text-muted border rounded">
                                    Belum ada data jenis fenomena.
                                </div>
                            @endforelse
                        </div>

                        <!-- Modals for Edit and Delete -->
                        @foreach($jenisFenomenas as $item)
                            <!-- Modal Edit Jenis -->
                            <div class="modal fade" id="editJenisModal{{ $item->id }}" tabindex="-1"
                                aria-labelledby="editJenisModalLabel{{ $item->id }}" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="{{ route('jenis-fenomena.update', $item->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="editJenisModalLabel{{ $item->id }}">Edit Jenis
                                                    Fenomena</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body text-start">
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Nama Jenis Fenomena</label>
                                                    <input type="text" class="form-control" name="nama"
                                                        value="{{ $item->nama }}" required>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary"
                                                    data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Tab 4: Sumber Berita -->
            <div class="tab-pane fade {{ $activeTab == 'sumber' ? 'show active' : '' }}" id="pills-sumber" role="tabpanel">
                <div class="card border-0 shadow-sm" style="border-radius: 12px; background: #fff;">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="fw-bold mb-0">Manajemen Sumber Berita</h5>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSumberModal"
                                style="border-radius: 8px;">
                                <i class="fas fa-plus me-1"></i> Tambah Data
                            </button>
                        </div>

                        <!-- Desktop Table View -->
                        <div class="table-responsive d-none d-md-block">
                            <table class="table table-hover align-middle table-bordered" id="tableSumber">
                                <thead class="table-light">
                                    <tr>
                                        <th width="5%" class="text-center">No</th>
                                        <th>Nama Sumber Berita</th>
                                        <th width="10%" class="text-center">Tipe</th>
                                        <th width="25%" class="text-center">Informasi</th>
                                        <th width="10%" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($sumberBeritas as $key => $item)
                                        <tr>
                                            <td class="text-center">{{ $key + 1 }}</td>
                                            <td>{{ $item->nama }}</td>
                                            <td class="text-center">
                                                @if($item->is_online)
                                                    <span class="badge bg-info text-dark">Online</span>
                                                @else
                                                    <span class="badge bg-secondary">Offline</span>
                                                @endif
                                            </td>
                                            <td class="text-start" style="font-size: 0.85rem;">
                                                <div class="d-flex flex-column text-muted">
                                                    @if($item->created_at)
                                                        <div>
                                                            <i class="fas fa-user-plus me-1 text-success" title="User Add"></i>
                                                            {{ $item->userAdd ? $item->userAdd->name : 'Sistem' }}
                                                            <br>
                                                            <small
                                                                class="ms-4 text-secondary">{{ $item->created_at->format('d/m/Y H:i') }}</small>
                                                        </div>
                                                    @endif

                                                    @if($item->updated_at && $item->updated_at != $item->created_at)
                                                        <div class="mt-1 border-top pt-1">
                                                            <i class="fas fa-user-edit me-1 text-primary" title="User Edit"></i>
                                                            {{ $item->userUpdate ? $item->userUpdate->name : 'Sistem' }}
                                                            <br>
                                                            <small
                                                                class="ms-4 text-secondary">{{ $item->updated_at->format('d/m/Y H:i') }}</small>
                                                        </div>
                                                    @endif
                                                    <div class="mt-1 border-top pt-1 text-info">
                                                        <i class="fas fa-link me-1"></i>
                                                        Digunakan: <span class="fw-bold">{{ $item->fenomenas_count }}</span>
                                                        data fenomena
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <button class="btn btn-sm btn-warning mb-1" data-bs-toggle="modal"
                                                    data-bs-target="#editSumberModal{{ $item->id }}" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-danger mb-1"
                                                    onclick="confirmDelete('{{ url('/sumber-berita/' . $item->id) }}', '{{ $item->nama }}')"
                                                    title="{{ $item->fenomenas_count > 0 ? 'Tidak dapat dihapus karena sudah digunakan' : 'Hapus' }}"
                                                    {{ $item->fenomenas_count > 0 ? 'disabled' : '' }}>
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">Belum ada data sumber berita.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Mobile Card View -->
                        <div class="d-md-none">
                            @forelse($sumberBeritas as $key => $item)
                                <div class="card mb-3 border shadow-sm" style="border-radius: 12px;">
                                    <div class="card-body p-3">
                                        <h6 class="fw-bold mb-3">{{ $item->nama }}</h6>
                                        <div class="d-flex justify-content-between align-items-center border-top pt-2">
                                            <div class="text-muted" style="font-size: 0.75rem;">
                                                <div><i class="fas fa-user-plus me-1 text-success"></i>
                                                    {{ $item->userAdd ? str($item->userAdd->name)->words(2, '') : 'Sistem' }}
                                                </div>
                                                <div class="mt-1"><i class="fas fa-clock me-1"></i>
                                                    {{ $item->created_at ? $item->created_at->format('d/m/y H:i') : '-' }}</div>
                                            </div>
                                            <div class="d-flex gap-2">
                                                <button class="btn btn-warning btn-sm" data-bs-toggle="modal"
                                                    data-bs-target="#editSumberModal{{ $item->id }}">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-danger btn-sm"
                                                    onclick="confirmDelete('{{ url('/sumber-berita/' . $item->id) }}', '{{ $item->nama }}')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-4 text-muted border rounded">
                                    Belum ada data sumber berita.
                                </div>
                            @endforelse
                        </div>

                        <!-- Modals for Edit and Delete -->
                        @foreach($sumberBeritas as $item)
                            <!-- Modal Edit Sumber -->
                            <div class="modal fade" id="editSumberModal{{ $item->id }}" tabindex="-1"
                                aria-labelledby="editSumberModalLabel{{ $item->id }}" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="{{ route('sumber-berita.update', $item->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="editSumberModalLabel{{ $item->id }}">Edit Sumber
                                                    Berita</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body text-start">
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Nama Sumber Berita</label>
                                                    <input type="text" class="form-control" name="nama"
                                                        value="{{ $item->nama }}" required>
                                                </div>
                                                <div class="mb-3">
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" role="switch"
                                                            name="is_online" id="isOnlineEdit{{ $item->id }}" {{ $item->is_online ? 'checked' : '' }} value="1">
                                                        <label class="form-check-label fw-bold"
                                                            for="isOnlineEdit{{ $item->id }}">Sumber Online (Wajibkan
                                                            Link)</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary"
                                                    data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Sektor Usaha -->
    <div class="modal fade" id="addSektorUsahaModal" tabindex="-1" aria-labelledby="addSektorUsahaModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('sektor-usaha.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="addSektorUsahaModalLabel">Tambah Kode Lapangan Usaha</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Kode (A-Z)</label>
                            <input type="text" class="form-control text-uppercase" name="kode" required pattern="[A-Za-z]+"
                                title="Hanya huruf alfabet yang diperbolehkan"
                                oninput="this.value = this.value.toUpperCase().replace(/[^A-Z]/g, '')">
                            <small class="text-muted">Contoh: A, B, ABC (Hanya huruf kapital beruntun)</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nama Lapangan Usaha</label>
                            <input type="text" class="form-control" name="nama" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Indikator -->
    <div class="modal fade" id="addIndikatorModal" tabindex="-1" aria-labelledby="addIndikatorModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('indikator.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="addIndikatorModalLabel">Tambah Kode Indikator</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Kode (Angka)</label>
                            <input type="text" class="form-control" name="kode" required pattern="[0-9]+"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                            <small class="text-muted">Contoh: 01, 02 (Hanya angka)</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nama Indikator</label>
                            <input type="text" class="form-control" name="nama" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Kelompok</label>
                            <select name="kelompok" class="form-select" required>
                                <option value="" disabled selected>Pilih Kelompok...</option>
                                <option value="utama">Utama</option>
                                <option value="dampak">Dampak</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold d-block">Status Aktif</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" name="is_active"
                                    id="flexSwitchCheckAdd" checked value="1">
                                <label class="form-check-label" for="flexSwitchCheckAdd">Ceklis untuk menyalakan
                                    indikator</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Jenis Fenomena -->
    <div class="modal fade" id="addJenisModal" tabindex="-1" aria-labelledby="addJenisModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('jenis-fenomena.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="addJenisModalLabel">Tambah Jenis Fenomena</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nama Jenis Fenomena</label>
                            <input type="text" class="form-control" name="nama" required
                                placeholder="Masukkan nama jenis fenomena...">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Sumber Berita -->
    <div class="modal fade" id="addSumberModal" tabindex="-1" aria-labelledby="addSumberModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('sumber-berita.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="addSumberModalLabel">Tambah Sumber Berita</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nama Sumber Berita</label>
                            <input type="text" class="form-control" name="nama" required
                                placeholder="Masukkan nama sumber berita...">
                        </div>
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" name="is_online"
                                    id="isOnlineAdd" value="1">
                                <label class="form-check-label fw-bold" for="isOnlineAdd">Sumber Online (Wajibkan
                                    Link)</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        .nav-pills .nav-link {
            font-weight: 500;
            transition: all 0.3s ease;
            border: 1px solid transparent;
        }

        .nav-pills .nav-link:not(.active) {
            background-color: #fffaf0 !important;
            /* light orange/yellow background similar to screenshot */
            color: #6c757d !important;
        }

        .nav-pills .nav-link.active {
            background-color: var(--bps-blue) !important;
            color: white !important;
            box-shadow: 0 4px 6px -1px rgba(0, 147, 221, 0.3);
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Setup CSRF Token for fetch requests
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            var triggerTabList = [].slice.call(document.querySelectorAll('#pills-tab button'))
            triggerTabList.forEach(function (triggerEl) {
                triggerEl.addEventListener('click', function (event) {
                    event.preventDefault()

                    // Reset all tabs to inactive style
                    document.querySelectorAll('.nav-pills .nav-link').forEach(btn => {
                        btn.style.backgroundColor = '';
                        btn.classList.add('bg-warning-subtle', 'text-dark');
                    });

                    // Set active tab style
                    this.classList.remove('bg-warning-subtle', 'text-dark');
                    this.style.backgroundColor = 'var(--bps-blue)';

                    var tab = new bootstrap.Tab(this)
                    tab.show()
                })
            })

            // Indikator Toggle Logic
            document.querySelectorAll('.toggle-indikator').forEach(function (toggle) {
                toggle.addEventListener('change', function () {
                    const id = this.getAttribute('data-id');
                    const isChecked = this.checked;

                    fetch(`/indikator/${id}/toggle-active`, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken || '{{ csrf_token() }}'
                        }
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Optional: Add toast notification here
                                console.log(data.message);
                            } else {
                                // Revert toggle if failed
                                this.checked = !isChecked;
                                alert('Gagal mengubah status indikator.');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            this.checked = !isChecked;
                            alert('Terjadi kesalahan pada server.');
                        });
                });
            });
        });

        function confirmDelete(url, itemName) {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: `Anda akan menghapus "${itemName}". Tindakan ini tidak dapat dibatalkan!`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.getElementById('global-delete-form');
                    form.action = url;
                    form.submit();
                }
            });
        }
    </script>

    <form id="global-delete-form" method="POST" style="display:none;">
        @csrf
        @method('DELETE')
    </form>
@endsection