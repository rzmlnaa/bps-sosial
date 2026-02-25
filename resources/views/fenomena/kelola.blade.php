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
                <a href="{{ route('fenomena.index') }}" class="btn btn-outline-secondary shadow-sm"
                    style="border-radius: 8px;">
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
                <button class="nav-link {{ $activeTab == 'lap-usaha' ? 'active' : 'bg-warning-subtle text-dark' }} rounded-pill px-4" id="pills-lap-usaha-tab" data-bs-toggle="pill"
                    data-bs-target="#pills-lap-usaha" type="button" role="tab" style="{{ $activeTab == 'lap-usaha' ? 'background-color: var(--bps-blue);' : '' }}">
                    <i class="fas fa-industry me-2"></i>Kode Lap Usaha
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $activeTab == 'indikator' ? 'active' : 'bg-warning-subtle text-dark' }} rounded-pill px-4" id="pills-indikator-tab"
                    data-bs-toggle="pill" data-bs-target="#pills-indikator" type="button" role="tab" style="{{ $activeTab == 'indikator' ? 'background-color: var(--bps-blue);' : '' }}">
                    <i class="fas fa-chart-line me-2"></i>Kode Indikator
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-pill px-4 bg-warning-subtle text-dark" id="pills-jenis-tab"
                    data-bs-toggle="pill" data-bs-target="#pills-jenis" type="button" role="tab">
                    <i class="fas fa-list me-2"></i>Jenis Fenomena
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-pill px-4 bg-warning-subtle text-dark" id="pills-sumber-tab"
                    data-bs-toggle="pill" data-bs-target="#pills-sumber" type="button" role="tab">
                    <i class="fas fa-newspaper me-2"></i>Sumber Berita
                </button>
            </li>
        </ul>

        <div class="tab-content" id="pills-tabContent">
            <!-- Tab 1: Kode Lap Usaha -->
            <div class="tab-pane fade {{ $activeTab == 'lap-usaha' ? 'show active' : '' }}" id="pills-lap-usaha" role="tabpanel">
                <div class="card border-0 shadow-sm" style="border-radius: 12px; background: #fff;">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="fw-bold mb-0">Manajemen Kode Lapangan Usaha</h5>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSektorUsahaModal"
                                style="border-radius: 8px;">
                                <i class="fas fa-plus me-1"></i> Tambah Data
                            </button>
                        </div>

                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
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

                        <div class="table-responsive">
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
                                                            <small class="ms-4 text-secondary">{{ $item->created_at->format('d/m/Y H:i') }}</small>
                                                        </div>
                                                    @endif
                                                    
                                                    @if($item->updated_at && $item->updated_at != $item->created_at)
                                                        <div class="mt-1 border-top pt-1">
                                                            <i class="fas fa-user-edit me-1 text-primary" title="User Edit"></i>
                                                            {{ $item->userUpdate ? $item->userUpdate->name : 'Sistem' }}
                                                            <br>
                                                            <small class="ms-4 text-secondary">{{ $item->updated_at->format('d/m/Y H:i') }}</small>
                                                        </div>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <button class="btn btn-sm btn-warning mb-1" data-bs-toggle="modal"
                                                    data-bs-target="#editSektorUsahaModal{{ $item->id }}" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <!-- Delete form can be added if needed, sticking to input/edit based on requirements -->
                                            </td>
                                        </tr>

                                        <!-- Modal Edit -->
                                        <div class="modal fade" id="editSektorUsahaModal{{ $item->id }}" tabindex="-1"
                                            aria-labelledby="editSektorUsahaModalLabel{{ $item->id }}" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form action="{{ route('sektor-usaha.update', $item->id) }}" method="POST">
                                                        @csrf
                                                        @method('PUT')
                                                        <div class="modal-header">
                                                            <h5 class="modal-title"
                                                                id="editSektorUsahaModalLabel{{ $item->id }}">Edit Kode Lapangan
                                                                Usaha</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                                aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body text-start">
                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold">Kode (A-Z)</label>
                                                                <input type="text" class="form-control text-uppercase"
                                                                    name="kode" value="{{ $item->kode }}" required
                                                                    pattern="[A-Za-z]+"
                                                                    title="Hanya huruf alfabet yang diperbolehkan"
                                                                    oninput="this.value = this.value.toUpperCase().replace(/[^A-Z]/g, '')">
                                                                <small class="text-muted">Contoh: A, B, ABC (Hanya huruf kapital
                                                                    beruntun)</small>
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
                                                            <button type="submit" class="btn btn-primary">Simpan
                                                                Perubahan</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">Belum ada data kode lapangan
                                                usaha.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 2: Kode Indikator -->
            <div class="tab-pane fade {{ $activeTab == 'indikator' ? 'show active' : '' }}" id="pills-indikator" role="tabpanel">
                <div class="card border-0 shadow-sm" style="border-radius: 12px; background: #fff;">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="fw-bold mb-0">Manajemen Kode Indikator</h5>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addIndikatorModal" style="border-radius: 8px;">
                                <i class="fas fa-plus me-1"></i> Tambah Data
                            </button>
                        </div>

                        <div class="table-responsive">
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
                                                <span class="badge {{ $item->kelompok == 'Utama' ? 'bg-primary' : 'bg-secondary' }}">
                                                    {{ $item->kelompok }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <div class="form-check form-switch d-flex justify-content-center">
                                                    <input class="form-check-input toggle-indikator" type="checkbox" role="switch" 
                                                        data-id="{{ $item->id }}" 
                                                        {{ $item->is_active ? 'checked' : '' }}
                                                        style="cursor: pointer; transform: scale(1.2);">
                                                </div>
                                            </td>
                                            <td class="text-start" style="font-size: 0.85rem;">
                                                <div class="d-flex flex-column text-muted">
                                                    @if($item->created_at)
                                                        <div>
                                                            <i class="fas fa-user-plus me-1 text-success" title="User Add"></i> 
                                                            {{ $item->userAdd ? $item->userAdd->name : 'Sistem' }}
                                                            <br>
                                                            <small class="ms-4 text-secondary">{{ $item->created_at->format('d/m/Y H:i') }}</small>
                                                        </div>
                                                    @endif
                                                    
                                                    @if($item->updated_at && $item->updated_at != $item->created_at)
                                                        <div class="mt-1 border-top pt-1">
                                                            <i class="fas fa-user-edit me-1 text-primary" title="User Edit"></i>
                                                            {{ $item->userUpdate ? $item->userUpdate->name : 'Sistem' }}
                                                            <br>
                                                            <small class="ms-4 text-secondary">{{ $item->updated_at->format('d/m/Y H:i') }}</small>
                                                        </div>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <button class="btn btn-sm btn-warning mb-1" data-bs-toggle="modal" data-bs-target="#editIndikatorModal{{ $item->id }}" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        
                                        <!-- Modal Edit Indikator -->
                                        <div class="modal fade" id="editIndikatorModal{{ $item->id }}" tabindex="-1" aria-labelledby="editIndikatorModalLabel{{ $item->id }}" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form action="{{ route('indikator.update', $item->id) }}" method="POST">
                                                        @csrf
                                                        @method('PUT')
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="editIndikatorModalLabel{{ $item->id }}">Edit Kode Indikator</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body text-start">
                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold">Kode (Angka)</label>
                                                                <input type="text" class="form-control" name="kode" value="{{ $item->kode }}" required pattern="[0-9]+" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                                                <small class="text-muted">Contoh: 01, 02 (Hanya angka)</small>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold">Nama Indikator</label>
                                                                <input type="text" class="form-control" name="nama" value="{{ $item->nama }}" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold">Kelompok</label>
                                                                <select name="kelompok" class="form-select" required>
                                                                    <option value="Utama" {{ $item->kelompok == 'Utama' ? 'selected' : '' }}>Utama</option>
                                                                    <option value="Dampak" {{ $item->kelompok == 'Dampak' ? 'selected' : '' }}>Dampak</option>
                                                                </select>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold d-block">Status Aktif</label>
                                                                <div class="form-check form-switch">
                                                                    <input class="form-check-input" type="checkbox" role="switch" name="is_active" id="flexSwitchCheckChecked{{ $item->id }}" {{ $item->is_active ? 'checked' : '' }} value="1">
                                                                    <label class="form-check-label" for="flexSwitchCheckChecked{{ $item->id }}">Ceklis untuk menyalakan indikator</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">Belum ada data kode indikator.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 3: Jenis Fenomena -->
            <div class="tab-pane fade" id="pills-jenis" role="tabpanel">
                <div class="card border-0 shadow-sm" style="border-radius: 12px; background: #fff;">
                    <div class="card-body p-5 text-center">
                        <i class="fas fa-list fa-3x text-secondary opacity-50 mb-3"></i>
                        <h5 class="fw-bold">Manajemen Jenis Fenomena</h5>
                        <p class="text-muted">Fitur ini sedang dalam pengembangan.</p>
                    </div>
                </div>
            </div>

            <!-- Tab 4: Sumber Berita -->
            <div class="tab-pane fade" id="pills-sumber" role="tabpanel">
                <div class="card border-0 shadow-sm" style="border-radius: 12px; background: #fff;">
                    <div class="card-body p-5 text-center">
                        <i class="fas fa-newspaper fa-3x text-secondary opacity-50 mb-3"></i>
                        <h5 class="fw-bold">Manajemen Sumber Berita</h5>
                        <p class="text-muted">Fitur ini sedang dalam pengembangan.</p>
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
    <div class="modal fade" id="addIndikatorModal" tabindex="-1" aria-labelledby="addIndikatorModalLabel" aria-hidden="true">
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
                            <input type="text" class="form-control" name="kode" required pattern="[0-9]+" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
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
                                <option value="Utama">Utama</option>
                                <option value="Dampak">Dampak</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold d-block">Status Aktif</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" name="is_active" id="flexSwitchCheckAdd" checked value="1">
                                <label class="form-check-label" for="flexSwitchCheckAdd">Ceklis untuk menyalakan indikator</label>
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
            document.querySelectorAll('.toggle-indikator').forEach(function(toggle) {
                toggle.addEventListener('change', function() {
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
    </script>
@endsection