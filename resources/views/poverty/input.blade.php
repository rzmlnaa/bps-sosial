@extends('layouts.admin')

@section('title', 'Input Data Kemiskinan - BPS Kalbar')

@section('content')
    <div class="fade-in-up">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
            <div>
                <h2 class="fw-bold mb-1" style="color: var(--bps-orange);">Input Data Kemiskinan</h2>
                <p class="text-muted mb-0">Kelola master data wilayah, variabel, dan input data kemiskinan</p>
            </div>
            <div class="mt-3 mt-md-0">
                <a href="{{ route('poverty') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Kembali
                </a>
            </div>
        </div>

        <!-- Tabs Navigation -->
        <ul class="nav nav-pills mb-4" id="pills-tab" role="tablist">
            <li class="nav-item" role="presentation">

                <button class="nav-link active rounded-pill px-4" id="pills-wilayah-tab" data-bs-toggle="pill"
                    data-bs-target="#pills-wilayah" type="link" role="tab">
                    <i class="fas fa-map-marker-alt me-2"></i>Master Wilayah
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-pill px-4" id="pills-variabel-tab" data-bs-toggle="pill"
                    data-bs-target="#pills-variabel" type="button" role="tab">
                    <i class="fas fa-tags me-2"></i>Master Variabel
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-pill px-4" id="pills-input-tab" data-bs-toggle="pill"
                    data-bs-target="#pills-input" type="button" role="tab">
                    <i class="fas fa-edit me-2"></i>Input Data
                </button>
            </li>
        </ul>

        <div class="tab-content" id="pills-tabContent">

            <!-- Tab 1: Master Wilayah -->
            <div class="tab-pane fade show active" id="pills-wilayah" role="tabpanel">
                <div class="row">
                    <div class="col-md-4 mb-4">
                        <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                            <div class="card-header bg-white py-3 border-bottom-0">
                                <h5 class="fw-bold mb-0">Tambah Wilayah</h5>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('kabupaten.store') }}" method="POST">
                                    @csrf
                                    <div class="mb-3">
                                        <label class="form-label text-muted small fw-bold text-uppercase">Nama
                                            Kabupaten/Kota</label>
                                        <input type="text" name="nama_kabupaten" class="form-control"
                                            placeholder="Contoh: Kab. Sambas" required>
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
                                <h5 class="fw-bold mb-0">Daftar Wilayah</h5>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="ps-4 border-0">No</th>
                                            <th class="border-0">Nama Kabupaten</th>
                                            <th class="border-0">Dibuat Oleh</th>
                                            <th class="border-0">Di Update Oleh</th>
                                            <th class="text-center border-0">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="border-top-0">
                                        @forelse($kabupatens as $index => $kab)
                                            <tr>
                                                <td class="ps-4 text-muted">{{ $index + 1 }}</td>
                                                <td class="fw-medium">{{ $kab->nama_kabupaten }}</td>
                                                <td>
                                                    <span class="badge bg-light text-dark border">
                                                        {{ $kab->userAdd->name ?? 'Admin' }} pada
                                                        {{ $kab->created_at->format('d/m/Y H:i') }}
                                                    </span>
                                                </td>
                                                <td>
                                                    @if($kab->userUpdate)
                                                        <span class="badge bg-light text-dark border">
                                                            {{ $kab->userUpdate->name }} pada
                                                            {{ $kab->updated_at->format('d/m/Y H:i') }}
                                                        </span>
                                                    @else
                                                        <span class="text-muted small">-</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    <button class="btn btn-sm btn-outline-warning border-0 btn-edit-kabupaten"
                                                        data-id="{{ $kab->id }}" data-nama="{{ $kab->nama_kabupaten }}">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center py-4 text-muted">Belum ada data wilayah.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 2: Master Variabel -->
            <div class="tab-pane fade" id="pills-variabel" role="tabpanel">
                <div class="row">
                    <div class="col-md-4 mb-4">
                        <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                            <div class="card-header bg-white py-3 border-bottom-0">
                                <h5 class="fw-bold mb-0">Tambah Variabel</h5>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('variabel.store') }}" method="POST">
                                    @csrf
                                    <div class="mb-3">
                                        <label class="form-label text-muted small fw-bold text-uppercase">Nama
                                            Variabel</label>
                                        <input type="text" name="nama_variabel" class="form-control" placeholder="Contoh: GKS" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label text-muted small fw-bold text-uppercase">Bulan (Opsional)</label>
                                        <select name="bulan" class="form-select">
                                            <option value="">-- Tanpa Bulan --</option>
                                            <option value="1">Januari</option>
                                            <option value="2">Februari</option>
                                            <option value="3">Maret</option>
                                            <option value="4">April</option>
                                            <option value="5">Mei</option>
                                            <option value="6">Juni</option>
                                            <option value="7">Juli</option>
                                            <option value="8">Agustus</option>
                                            <option value="9">September</option>
                                            <option value="10">Oktober</option>
                                            <option value="11">November</option>
                                            <option value="12">Desember</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label text-muted small fw-bold text-uppercase">Tahun</label>
                                        <input type="number" name="tahun" class="form-control" placeholder="2024" min="2000" max="2099" required>
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
                                <h5 class="fw-bold mb-0">Daftar Variabel & Tahun</h5>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="ps-4 border-0">No</th>
                                            <th class="border-0">Nama Variabel</th>
                                            <th class="border-0">Bulan</th>
                                            <th class="border-0">Tahun</th>
                                            <th class="border-0">Dibuat Oleh</th>
                                            <th class="text-center border-0">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="border-top-0">
                                        @forelse($variabels as $index => $v)
                                            <tr>
                                                <td class="ps-4 text-muted">{{ $index + 1 }}</td>
                                                <td class="fw-medium">{{ $v->nama_variabel }}</td>
                                                <td>
                                                    @if($v->bulan == 3) Maret @elseif($v->bulan == 9) September @else - @endif
                                                </td>
                                                <td><span class="badge bg-blue-faded text-blue">{{ $v->tahun }}</span></td>
                                                <td>
                                                    <span class="badge bg-light text-dark border">
                                                        {{ $v->userAdd->name ?? 'Admin' }} pada {{ $v->created_at->format('d/m/Y H:i') }}
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <form action="{{ route('variabel.destroy', $v->id) }}" method="POST" class="d-inline form-delete">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button" class="btn btn-sm btn-outline-danger border-0 btn-delete">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center py-4 text-muted">Belum ada data variabel.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 3: Input Data -->
            <div class="tab-pane fade" id="pills-input" role="tabpanel">
                <div class="row g-4">
                    <!-- Left Column: Input Form -->
                    <div class="col-lg-4">
                        <!-- Filters -->
                        <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                            <div class="card-body p-4">
                                <h6 class="fw-bold mb-3 text-uppercase text-muted"
                                    style="font-size: 0.8rem; letter-spacing: 0.5px;">1. Filter Data</h6>
                                <div class="mb-3">
                                    <label class="form-label small fw-medium">Kabupaten/Kota</label>
                                    <select class="form-select bg-light border-0">
                                        <option selected disabled>-- Pilih Wilayah --</option>
                                        @foreach($kabupatens as $kab)
                                            <option value="{{ $kab->id }}">{{ $kab->nama_kabupaten }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-0">
                                    <label class="form-label small fw-medium">Variabel</label>
                                    <select class="form-select bg-light border-0">
                                        <option selected disabled>-- Pilih Variabel --</option>
                                        @foreach($variabels as $var)
                                            <option value="{{ $var->id }}">{{ $var->nama_variabel }} {{ $var->tahun }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Input Area -->
                        <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                            <div class="card-header bg-white py-3 border-bottom-0">
                                <h6 class="fw-bold mb-0 text-uppercase text-muted"
                                    style="font-size: 0.8rem; letter-spacing: 0.5px;">2. Paste Data</h6>
                            </div>
                            <div class="card-body p-0">
                                <div class="p-3">
                                    <textarea class="form-control fw-mono border-0 bg-light" rows="15"
                                        placeholder="Paste data dari Excel/SPSS disini...&#10;Contoh:&#10;547005,00&#10;547005,00&#10;..."
                                        style="font-family: 'Courier New', monospace; font-size: 1rem; resize: none;"></textarea>
                                </div>
                            </div>
                            <div class="card-footer bg-white border-top-0 py-3">
                                <button class="btn btn-primary w-100 fw-bold py-2 shadow-sm"
                                    style="background-color: var(--bps-orange); border: none;">
                                    <i class="fas fa-save me-2"></i>Simpan Data
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Data Table -->
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
                            <div
                                class="card-header bg-white py-3 border-bottom-0 d-flex justify-content-between align-items-center">
                                <h5 class="fw-bold mb-0">Data Tersimpan - Kab. Sambas</h5>
                                <button class="btn btn-sm btn-outline-secondary"><i class="fas fa-download me-1"></i>
                                    Export</button>
                            </div>
                            <div class="table-responsive h-100">
                                <table class="table table-hover table-striped mb-0 text-center" style="font-size: 0.85rem;">
                                    <thead class="bg-light sticky-top" style="z-index: 1;">
                                        <tr class="fw-bold text-secondary">
                                            <th class="py-3" style="width: 60px;">Persentil</th>
                                            <th class="py-3">2022 Asli</th>
                                            <th class="py-3">2022 Rilis</th>
                                            <th class="py-3">GK 2022</th>
                                            <th class="py-3">2023 Asli</th>
                                            <th class="py-3">GKS 2023</th>
                                            <th class="py-3">2024 Asli</th>
                                            <th class="py-3">GKS 2024</th>
                                            <th class="py-3">2025 Asli</th>
                                            <th class="py-3">GKS 2025</th>
                                        </tr>
                                    </thead>
                                    <tbody class="border-top-0">
                                        @for ($i = 1; $i <= 20; $i++)
                                            <tr>
                                                <td class="fw-bold text-muted bg-light">{{ $i }}</td>
                                                <td>{{ number_format(rand(400000, 900000), 2, ',', '.') }}</td>
                                                <td>{{ number_format(rand(300000, 800000), 2, ',', '.') }}</td>
                                                <td>{{ number_format(472079, 2, ',', '.') }}</td>
                                                <td>{{ number_format(rand(400000, 900000), 2, ',', '.') }}</td>
                                                <td>{{ number_format(497618, 2, ',', '.') }}</td>
                                                <td>{{ number_format(rand(500000, 950000), 2, ',', '.') }}</td>
                                                <td>{{ number_format(511611, 2, ',', '.') }}</td>
                                                <td>{{ number_format(rand(600000, 990000), 2, ',', '.') }}</td>
                                                <td>{{ number_format(528217, 2, ',', '.') }}</td>
                                            </tr>
                                        @endfor
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Edit Kabupaten -->
    <div class="modal fade" id="modalEditKabupaten" tabindex="-1" aria-labelledby="modalEditKabupatenLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom-0">
                    <h5 class="modal-title fw-bold" id="modalEditKabupatenLabel">Edit Nama Kabupaten</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formEditKabupaten" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold text-uppercase">Nama Kabupaten/Kota</label>
                            <input type="text" name="nama_kabupaten" id="edit_nama_kabupaten" class="form-control" placeholder="Contoh: Kab. Sambas" required>
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
        /* Custom Styles specific to this page */
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

        .bg-blue-faded {
            background-color: rgba(0, 147, 221, 0.1);
        }

        .text-blue {
            color: var(--bps-blue);
        }

        .fw-mono {
            font-family: 'Courier New', Courier, monospace;
        }

        /* Input focus effect for the data table */
        .table input:focus {
            background-color: #f8fafc;
            box-shadow: inset 0 0 0 2px var(--bps-blue) !important;
        }
    </style>
@endsection

@push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // Unique key for this page to avoid conflicts
            const storageKey = 'activeTab_poverty_input';

            // 1. Check if there is a saved tab
            const activeTab = localStorage.getItem(storageKey);
            if (activeTab) {
                const tabToTrigger = document.querySelector(`button[data-bs-target="${activeTab}"]`);
                if (tabToTrigger) {
                    const tab = new bootstrap.Tab(tabToTrigger);
                    tab.show();
                }
            }

            // 2. Save tab to localStorage on click
            const tabLinks = document.querySelectorAll('button[data-bs-toggle="pill"]');
            tabLinks.forEach(function (tabLink) {
                tabLink.addEventListener('shown.bs.tab', function (event) {
                    const target = event.target.getAttribute("data-bs-target");
                    localStorage.setItem(storageKey, target);
                });
            });

            // 3. Edit Kabupaten Modal Logic
            const editButtons = document.querySelectorAll('.btn-edit-kabupaten');
            const modalEdit = new bootstrap.Modal(document.getElementById('modalEditKabupaten'));
            const formEdit = document.getElementById('formEditKabupaten');
            const inputEditNama = document.getElementById('edit_nama_kabupaten');

            editButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const nama = this.getAttribute('data-nama');
                    
                    inputEditNama.value = nama;
                    formEdit.action = `/kabupaten/${id}`;
                    
                    modalEdit.show();
                });
            });

            // 4. Delete Confirmation Logic
            const deleteButtons = document.querySelectorAll('.btn-delete');
            deleteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const form = this.closest('.form-delete');
                    
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
                });
            });
        });
    </script>
@endpush