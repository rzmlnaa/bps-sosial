@extends('layouts.admin')

@section('title', 'Input Data Seruti')

@section('content')
    @php
        $activeTab = request()->query('tab', 'input');
    @endphp
    <div class="fade-in-up">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-1" style="color: var(--bps-orange);">Input Data Seruti</h2>
                <p class="text-muted mb-0">Kelola master data coicop dan input data konsumsi</p>
            </div>
            <a href="{{ route('seruti.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Kembali
            </a>
        </div>

        <!-- Tabs/Navigation -->
        <div class="card border-0 shadow-sm mb-4 " style="border-radius: 12px; overflow: hidden;">
            <div class="card-header bg-white p-2">
                <ul class="nav nav-pills card-header-pills" id="serutiTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ $activeTab == 'master' ? 'active' : '' }} fw-bold" id="master-tab"
                            data-bs-toggle="tab" data-bs-target="#master" type="button" role="tab" aria-controls="master"
                            aria-selected="{{ $activeTab == 'master' ? 'true' : 'false' }}" style="border-radius: 8px;">
                            <i class="fas fa-tags me-2"></i>Master Kelompok
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ $activeTab == 'input' ? 'active' : '' }} fw-bold" id="input-tab"
                            data-bs-toggle="tab" data-bs-target="#input" type="button" role="tab" aria-controls="input"
                            aria-selected="{{ $activeTab == 'input' ? 'true' : 'false' }}" style="border-radius: 8px;">
                            <i class="fas fa-edit me-2"></i>Input Data
                        </button>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Tab Content -->
        <div class="tab-content" id="serutiTabContent">

            <!-- Tab 1: Master Kelompok -->
            <div class="tab-pane fade {{ $activeTab == 'master' ? 'show active' : '' }}" id="master" role="tabpanel"
                aria-labelledby="master-tab">
                <div class="row g-4">
                    <!-- Left: Input Area -->
                    <div class="col-lg-12">
                        <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                            <div
                                class="card-header bg-white py-3 border-bottom-0 d-flex justify-content-between align-items-center">
                                <h6 class="fw-bold mb-0 text-uppercase text-secondary">Input Master Coicop</h6>
                                <div class="btn-group" role="group" aria-label="Input Mode">
                                    <input type="radio" class="btn-check" name="inputMode" id="modePaste" autocomplete="off"
                                        checked>
                                    <label class="btn btn-outline-primary btn-sm" for="modePaste"><i
                                            class="fas fa-paste me-1"></i> Paste Excel</label>

                                    <input type="radio" class="btn-check" name="inputMode" id="modeManual"
                                        autocomplete="off">
                                    <label class="btn btn-outline-primary btn-sm" for="modeManual"><i
                                            class="fas fa-keyboard me-1"></i> Manual</label>
                                </div>
                            </div>
                            <div class="card-body p-4 pt-0">

                                <!-- Paste Section -->
                                <div id="pasteSection">
                                    <p class="text-muted small">
                                        Salin data dari Excel dengan kolom (Kode | COICOP | SERUTI) lalu tempel di bawah
                                        ini.
                                    </p>
                                    <div class="form-group mb-3">
                                        <textarea id="pasteArea" class="form-control" rows="8"
                                            placeholder="Paste data Excel di sini..."
                                            style="font-family: monospace; font-size: 0.9rem;"></textarea>
                                    </div>
                                    <div class="d-flex justify-content-end gap-2">
                                        <button class="btn btn-secondary" id="clearBtn">Clear</button>
                                        <button class="btn btn-primary" id="previewBtn">Simpan Data</button>
                                    </div>
                                </div>

                                <!-- Manual Section -->
                                <div id="manualSection" class="d-none">
                                    <p class="text-muted small">Masukkan data kelompok secara manual.</p>
                                    <div id="manualRowsContainer">
                                        <!-- Dynamic Rows -->
                                    </div>
                                    <div class="mt-3 d-flex justify-content-between">
                                        <button class="btn btn-outline-primary btn-sm" id="addManualRowBtn">
                                            <i class="fas fa-plus me-1"></i> Tambah Baris
                                        </button>
                                        <button class="btn btn-primary" id="previewManualBtn">Simpan Data</button>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- Preview & Existing Data -->
                    <div class="col-lg-12">
                        <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                            <div class="card-header bg-white py-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="fw-bold mb-0">Data Kelompok COICOP</h5>
                                    <button class="btn btn-success d-none" id="saveBtn">
                                        <i class="fas fa-save me-2"></i>Simpan Perubahan
                                    </button>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0" id="coicopTable">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="ps-4" style="width: 100px;">Kode</th>
                                            <th>Nama COICOP</th>
                                            <th>SERUTI</th>
                                            <th>Operator</th>
                                            <th class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="coicopTableBody">
                                        @forelse($coicops as $c)
                                            <tr>
                                                <td class="ps-4 fw-bold">{{ $c->kode }}</td>
                                                <td>{{ $c->nama }}</td>
                                                <td><span class="badge bg-info text-dark">{{ $c->seruti }}</span></td>
                                                <td class="small">
                                                    @if($c->userAdd)
                                                        <div class="text-muted mb-1" title="Dibuat oleh">
                                                            <i class="fas fa-user-plus me-1 text-success"></i>
                                                            {{ $c->userAdd->name }}
                                                            <span
                                                                class="text-xs opacity-75 ms-1">({{ $c->created_at->format('d/m/y') }})</span>
                                                        </div>
                                                    @endif
                                                    @if($c->userUpdate)
                                                        <div class="text-muted" title="Diupdate oleh">
                                                            <i class="fas fa-user-edit me-1 text-warning"></i>
                                                            {{ $c->userUpdate->name }}
                                                            <span
                                                                class="text-xs opacity-75 ms-1">({{ $c->updated_at->format('d/m/y') }})</span>
                                                        </div>
                                                    @endif
                                                    @if(!$c->userAdd && !$c->userUpdate)
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    <button class="btn btn-sm btn-icon btn-outline-warning edit-coicop-btn"
                                                        data-id="{{ $c->id }}" data-kode="{{ $c->kode }}"
                                                        data-nama="{{ $c->nama }}" data-seruti="{{ $c->seruti }}">
                                                        <i class="fas fa-pencil-alt"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-icon btn-outline-danger delete-coicop-btn"
                                                        data-id="{{ $c->id }}" data-obj="{{ $c->kode }} - {{ $c->nama }}">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center py-4 text-muted">Belum ada data. Silakan
                                                    input di atas.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 2: Input Data -->
            <div class="tab-pane fade {{ $activeTab == 'input' ? 'show active' : '' }}" id="input" role="tabpanel"
                aria-labelledby="input-tab">
                <div class="row g-4">

                    <!-- LEFT COLUMN: FILTER -->
                    <div class="col-lg-4 col-xl-3">
                        <div class="card border-0 shadow-sm sticky-top"
                            style="top: 20px; z-index: 100; border-radius: 12px;">
                            <div class="card-header bg-white py-3 border-bottom-0">
                                <h6 class="fw-bold mb-0 text-uppercase text-secondary" style="letter-spacing: 0.5px;">1.
                                    Filter Data</h6>
                            </div>
                            <div class="card-body pt-0">
                                <div class="mb-3">
                                    <label class="form-label fw-bold small">Tahun</label>
                                    <input type="number" id="inputYear" class="form-control" placeholder="YYYY"
                                        value="{{ date('Y') }}">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold small">Triwulan</label>
                                    <select id="inputQuarter" class="form-select">
                                        <option value="" selected disabled>Pilih Triwulan</option>
                                        <option value="1">Triwulan 1</option>
                                        <option value="2">Triwulan 2</option>
                                        <option value="3">Triwulan 3</option>
                                        <option value="4">Triwulan 4</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold small">Kabupaten/Kota</label>
                                    <select id="inputKabupaten" class="form-select" size="10" style="height: 250px;">
                                        @foreach($kabupatens as $kab)
                                            <option value="{{ $kab->id }}">[{{ $kab->kode_kab }}] {{ $kab->nama_kabupaten }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted fst-italic mt-1 d-block">Pilih salah satu wilayah.</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- RIGHT COLUMN: CONTENT -->
                    <div class="col-lg-8 col-xl-9">
                        <div class="row g-4">
                            <!-- PASTE AREA -->
                            <div class="col-12">
                                <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                                    <div class="card-header bg-white py-3 border-bottom-0">
                                        <h6 class="fw-bold mb-0 text-uppercase text-secondary"
                                            style="letter-spacing: 0.5px;">2. Paste Data</h6>
                                    </div>
                                    <div class="card-body pt-0">
                                        <p class="text-muted small mb-2">
                                            Salin kolom nilai (tanpa header) dari Excel dan tempel di sini.
                                            Pastikan urutan baris sesuai dengan Master Kelompok (disortir berdasarkan Kode).
                                        </p>
                                        <div class="d-flex gap-3">
                                            <div class="flex-grow-1">
                                                <textarea id="pasteConsumption" class="form-control" rows="5"
                                                    placeholder="Paste nilai data di sini..."
                                                    style="font-family: monospace; font-size: 0.9rem; white-space: pre;"></textarea>
                                            </div>
                                            <div class="d-flex flex-column gap-2 justify-content-start">
                                                <button class="btn btn-primary" id="actionSaveBtn">
                                                    <i class="fas fa-save me-1"></i> Simpan Data
                                                </button>
                                                <button class="btn btn-outline-secondary" id="clearConsumptionBtn">
                                                    <i class="fas fa-trash me-1"></i> Clear
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- PREVIEW / EXISTING DATA TABLE -->
                            <div class="col-12">
                                <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                                    <div
                                        class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                                        <h5 class="fw-bold mb-0" id="tableHeaderTitle">Data Tersimpan - [Pilih Wilayah]</h5>
                                        <button class="btn btn-outline-dark d-none" id="printBtn">
                                            <i class="fas fa-print"></i>
                                        </button>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0" style="font-size: 0.9rem;">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th class="ps-4 zone-data" style="width: 80px;">Kode</th>
                                                    <th class="zone-data">COICOP / Komoditas</th>
                                                    <th class="zone-data zone-divider">SERUTI</th>
                                                    <th class="text-end zone-action" style="width: 150px;">Nilai (Rp)</th>
                                                    <th class="text-center zone-action" style="width: 240px;">Operator</th>
                                                    <th class="text-center zone-action" style="width: 80px;">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody id="consumptionTableBody">
                                                <tr>
                                                    <td colspan="6" class="text-center py-5 text-muted">
                                                        <i class="fas fa-filter fa-3x mb-3 text-secondary opacity-50"></i>
                                                        <p class="mb-0">Silakan pilih Tahun, Triwulan, dan Wilayah terlebih
                                                            dahulu.</p>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const masterCoicops = {!! $coicops->toJson() !!};
            let parsedConsumption = [];

            // --- MASTER TAB LOGIC ---
            const pasteArea = document.getElementById('pasteArea');
            const previewBtn = document.getElementById('previewBtn');
            const clearBtn = document.getElementById('clearBtn');
            const saveBtn = document.getElementById('saveBtn'); // Note: This button is hidden in new design, integrated into Preview
            const tableBody = document.getElementById('coicopTableBody');

            // Toggle Logic
            const modePaste = document.getElementById('modePaste');
            const modeManual = document.getElementById('modeManual');
            const pasteSection = document.getElementById('pasteSection');
            const manualSection = document.getElementById('manualSection');

            if (modePaste && modeManual) {
                modePaste.addEventListener('change', () => {
                    if (modePaste.checked) {
                        pasteSection.classList.remove('d-none');
                        manualSection.classList.add('d-none');
                    }
                });
                modeManual.addEventListener('change', () => {
                    if (modeManual.checked) {
                        manualSection.classList.remove('d-none');
                        pasteSection.classList.add('d-none');
                        if (manualRowsContainer.children.length === 0) addRow(); // Init one row
                    }
                });
            }

            // Manual Input Logic
            const manualRowsContainer = document.getElementById('manualRowsContainer');
            const addManualRowBtn = document.getElementById('addManualRowBtn');
            const previewManualBtn = document.getElementById('previewManualBtn');

            function createRowHtml() {
                return `
                                                                                <div class="row g-2 mb-2 manual-row align-items-end">
                                                                                    <div class="col-md-2">
                                                                                        <label class="form-label small text-muted mb-1">Kode</label>
                                                                                        <input type="text" class="form-control form-control-sm input-kode" placeholder="ex: 01">
                                                                                    </div>
                                                                                    <div class="col-md-5">
                                                                                        <label class="form-label small text-muted mb-1">Nama COICOP</label>
                                                                                        <input type="text" class="form-control form-control-sm input-nama" placeholder="Nama Komoditas">
                                                                                    </div>
                                                                                    <div class="col-md-4">
                                                                                        <label class="form-label small text-muted mb-1">Seruti</label>
                                                                                        <input type="text" class="form-control form-control-sm input-seruti" placeholder="Kelompok Seruti">
                                                                                    </div>
                                                                                    <div class="col-md-1">
                                                                                         <button class="btn btn-outline-danger btn-sm w-100 remove-row-btn" tabindex="-1"><i class="fas fa-times"></i></button>
                                                                                    </div>
                                                                                                                                                      </div>
                                                                            `;
            }

            function addRow(data = null) {
                const div = document.createElement('div');
                div.innerHTML = createRowHtml();
                const newRow = div.firstElementChild;
                manualRowsContainer.appendChild(newRow);

                if (data) {
                    newRow.querySelector('.input-kode').value = data.kode || '';
                    newRow.querySelector('.input-nama').value = data.nama || '';
                    newRow.querySelector('.input-seruti').value = data.seruti || '';
                }

                newRow.querySelector('.remove-row-btn').addEventListener('click', function () {
                    newRow.remove();
                });
            }

            if (addManualRowBtn) {
                addManualRowBtn.addEventListener('click', () => addRow());
            }

            // Edit Logic
            document.querySelectorAll('.edit-coicop-btn').forEach(btn => {
                btn.addEventListener('click', function () {
                    const id = this.dataset.id;
                    const kode = this.dataset.kode;
                    const nama = this.dataset.nama;
                    const seruti = this.dataset.seruti;

                    Swal.fire({
                        title: 'Edit Kelompok COICOP',
                        html: `
                                                                                        <div class="text-start">
                                                                                            <div class="mb-3">
                                                                                                <label class="form-label small">Kode</label>
                                                                                                <input id="editKode" class="form-control" value="${kode}" readonly>
                                                                                            </div>
                                                                                            <div class="mb-3">
                                                                                                <label class="form-label small">Nama</label>
                                                                                                <input id="editNama" class="form-control" value="${nama}">
                                                                                            </div>
                                                                                            <div class="mb-3">
                                                                                                <label class="form-label small">Seruti</label>
                                                                                                <input id="editSeruti" class="form-control" value="${seruti}">
                                                                                            </div>
                                                                                        </div>
                                                                                    `,
                        showCancelButton: true,
                        confirmButtonText: 'Simpan',
                        preConfirm: () => {
                            return {
                                id: id, // Include ID for Update Logic
                                kode: document.getElementById('editKode').value,
                                nama: document.getElementById('editNama').value,
                                seruti: document.getElementById('editSeruti').value
                            }
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Reuse store-coicop endpoint
                            saveCoicopData([result.value]);
                        }
                    });
                });
            });

            // Delete Logic
            document.querySelectorAll('.delete-coicop-btn').forEach(btn => {
                btn.addEventListener('click', function () {
                    const id = this.dataset.id;
                    const label = this.dataset.obj;

                    Swal.fire({
                        title: 'Hapus Komoditas?',
                        text: `Anda akan menghapus "${label}". Data tidak dapat dikembalikan. Pastikan data tidak memiliki nilai konsumsi tersimpan.`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Ya, Hapus!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            fetch(`/seruti/coicop/${id}`, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                }
                            })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        Swal.fire('Terhapus!', data.message, 'success').then(() => {
                                            window.location.href = "{{ route('seruti.create', ['tab' => 'master']) }}";
                                        });
                                    } else {
                                        Swal.fire('Gagal', data.message, 'error');
                                    }
                                })
                                .catch(err => {
                                    Swal.fire('Error', 'Terjadi kesalahan sistem.', 'error');
                                });
                        }
                    });
                });
            });

            // Save/Processing Logic
            let parsedData = [];

            if (clearBtn) {
                clearBtn.addEventListener('click', () => {
                    pasteArea.value = '';
                });
            }

            function getManualData() {
                const rows = document.querySelectorAll('.manual-row');
                const data = [];
                rows.forEach(row => {
                    const kode = row.querySelector('.input-kode').value.trim();
                    const nama = row.querySelector('.input-nama').value.trim();
                    const seruti = row.querySelector('.input-seruti').value.trim();
                    if (kode && nama) {
                        data.push({ kode, nama, seruti });
                    }
                });
                return data;
            }

            function saveCoicopData(data) {
                if (data.length === 0) {
                    Swal.fire('Error', 'Tidak ada data valid.', 'warning');
                    return;
                }

                Swal.fire({
                    title: 'Simpan Data?',
                    text: `Akan menyimpan ${data.length} item data.`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Simpan',
                    showLoaderOnConfirm: true,
                    preConfirm: () => {
                        return fetch('/seruti/store-coicop', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ coicops: data })
                        })
                            .then(response => response.json())
                            .catch(error => Swal.showValidationMessage(`Request failed: ${error}`));
                    }
                }).then((result) => {
                    if (result.isConfirmed && result.value.success) {
                        Swal.fire('Berhasil!', result.value.message, 'success').then(() => {
                            window.location.href = "{{ route('seruti.create', ['tab' => 'master']) }}";
                        });
                    } else if (result.value && !result.value.success) {
                        Swal.fire('Gagal', result.value.message, 'error');
                    }
                });
            }

            if (previewBtn) {
                previewBtn.addEventListener('click', () => {
                    const rawText = pasteArea.value.trim();
                    if (!rawText) {
                        Swal.fire('Info', 'Silakan paste data terlebih dahulu', 'info');
                        return;
                    }

                    const rows = rawText.split('\n');
                    const newRows = [];

                    rows.forEach((row, index) => {
                        const cols = row.split('\t');
                        if (cols.length >= 2) {
                            const kode = cols[0].trim();
                            const nama = cols[1].trim();
                            const seruti = cols[2] ? cols[2].trim() : '';

                            if (kode && nama) {
                                newRows.push({ kode, nama, seruti });
                            }
                        }
                    });

                    if (newRows.length === 0) {
                        Swal.fire('Error', 'Format data tidak dikenali.', 'error');
                        return;
                    }

                    saveCoicopData(newRows);
                });
            }

            if (previewManualBtn) {
                previewManualBtn.addEventListener('click', () => {
                    const data = getManualData();
                    saveCoicopData(data);
                });
            }

            function renderPreview(data) {
                // Deprecated as we save immediately now
            }

            if (saveBtn) {
                saveBtn.addEventListener('click', function () {
                    if (parsedData.length === 0) return;

                    Swal.fire({
                        title: 'Simpan Data?',
                        text: "Data akan disimpan ke database (Update/Insert based on Kode).",
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Ya, Simpan!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            fetch('/seruti/store-coicop', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({ coicops: parsedData })
                            })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        Swal.fire('Berhasil!', data.message, 'success').then(() => {
                                            window.location.href = "{{ route('seruti.create', ['tab' => 'master']) }}";
                                        });
                                    } else {
                                        Swal.fire('Gagal', data.message, 'error');
                                    }
                                })
                                .catch(err => {
                                    console.error(err);
                                    Swal.fire('Error', 'Terjadi kesalahan sistem.', 'error');
                                });
                        }
                    });
                });
            }

            // --- INPUT CONSUMPTION LOGIC ---
            const pasteConsumption = document.getElementById('pasteConsumption');
            const actionSaveBtn = document.getElementById('actionSaveBtn');
            const clearConsumptionBtn = document.getElementById('clearConsumptionBtn');
            const printBtn = document.getElementById('printBtn');
            const consumptionTableBody = document.getElementById('consumptionTableBody');
            const tableHeaderTitle = document.getElementById('tableHeaderTitle');

            // Input Elements
            const inputYear = document.getElementById('inputYear');
            const inputQuarter = document.getElementById('inputQuarter');
            const inputKabupaten = document.getElementById('inputKabupaten');

            function updatePreviewHeader() {
                const y = inputYear ? inputYear.value : '';
                const q = inputQuarter && inputQuarter.value ? inputQuarter.options[inputQuarter.selectedIndex].text : '';

                let kName = '[Pilih Wilayah]';
                let kId = '';

                if (inputKabupaten && inputKabupaten.value) {
                    kName = inputKabupaten.options[inputKabupaten.selectedIndex].text;
                    kId = inputKabupaten.value;
                }

                if (tableHeaderTitle) {
                    let title = 'Data Tersimpan';
                    if (y) title += ` ${y}`;
                    if (q) title += ` - ${q}`;
                    title += ` - ${kName}`;
                    tableHeaderTitle.innerText = title;
                }

                if (y && y.length === 4 && inputQuarter.value && kId) {
                    fetchExistingData(y, inputQuarter.value, kId);
                } else {
                    consumptionTableBody.innerHTML = `
                                                                <tr>
                                                                    <td colspan="6" class="text-center py-5 text-muted">
                                                                        <i class="fas fa-filter fa-3x mb-3 text-secondary opacity-50"></i>
                                                                        <p class="mb-0">Silakan pilih Tahun, Triwulan, dan Wilayah terlebih dahulu.</p>
                                                                    </td>
                                                                </tr>
                                                            `;
                    pasteConsumption.value = '';
                    if (printBtn) printBtn.classList.add('d-none');
                }
            }

            function fetchExistingData(year, quarter, kabupatenId) {
                // Show loading state
                consumptionTableBody.innerHTML = '<tr><td colspan="6" class="text-center py-4"><div class="spinner-border text-primary" role="status"></div><div class="mt-2 text-muted">Memuat data...</div></td></tr>';
                pasteConsumption.value = 'Memuat data...';
                if (printBtn) printBtn.classList.add('d-none');

                const url = `/seruti/get-data?year=${year}&quarter=${quarter}&kabupaten_id=${kabupatenId}`;

                fetch(url)
                    .then(res => res.json())
                    .then(response => {
                        if (response.success) {
                            // Map response to include state
                            parsedConsumption = response.data.map(item => ({
                                ...item,
                                state: (item.value !== null && item.value !== '') ? 'saved' : 'empty'
                            }));

                            renderConsumptionTable(parsedConsumption);
                            populateTextArea(parsedConsumption);
                            if (printBtn) printBtn.classList.remove('d-none');
                        } else {
                            Swal.fire('Error', response.message, 'error');
                            consumptionTableBody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-danger">Gagal memuat data.</td></tr>';
                            pasteConsumption.value = '';
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        consumptionTableBody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-danger">Gagal menghubungi server.</td></tr>';
                        pasteConsumption.value = '';
                    });
            }

            function renderConsumptionTable(data) {
                let html = '';

                data.forEach(item => {
                    const hasValue = item.value !== null && item.value !== '' && !isNaN(item.value);
                    const valDisplay = hasValue ? parseFloat(item.value).toLocaleString('id-ID', { minimumFractionDigits: 2 }) : '-';
                    let rowClass = '';
                    if (item.state === 'ready') {
                        rowClass = 'table-info'; // Highlight changed rows
                    }
                    let operatorHtml = '<span class="text-muted opacity-50"> — </span>';

                    if (item.operator_add || item.operator_update) {
                        let addPart = '';
                        if (item.operator_add) {
                            addPart = `
                                                    <div class="mb-1">
                                                        <i class="fas fa-plus-circle text-success me-1"></i>
                                                        <strong>${item.operator_add}</strong>
                                                        <span class="text-muted">${item.date_add || ''}</span>
                                                    </div>`;
                        }

                        let updatePart = '';
                        if (item.operator_update) {
                            updatePart = `
                                                <div>
                                                    <i class="fas fa-edit text-warning me-1"></i>
                                                    <strong>${item.operator_update}</strong>
                                                    <span class="text-muted">${item.date_update || ''}</span>
                                                </div>`;
                        }
                        if (addPart || updatePart) {
                            operatorHtml = `
                                                    <div class="d-flex flex-column text-start" style="font-size: 0.75rem;">
                                                        ${addPart}
                                                        ${updatePart}
                                                    </div>
                                                `;
                        }
                    }

                    html += `
                                                                <tr class="${rowClass}">
                                                                    <td class="ps-4 fw-bold align-middle zone-data text-primary">${item.kode}</td>
                                                                    <td class="align-middle zone-data scrollable-cell">${item.nama}</td>
                                                                    <td class="align-middle zone-data zone-divider text-center">
                                                                        <span class="badge bg-white text-dark border-secondary-subtle" style="font-size: 0.7rem;">${item.seruti || '-'}</span>
                                                                    </td>
                                                                    <td class="text-end fw-bold align-middle zone-action ${hasValue ? 'text-dark' : 'text-muted'}" style="font-size: 1rem;">${valDisplay}</td>
                                                                    <td class="align-middle zone-action">${operatorHtml}</td>
                                                                    <td class="text-center zone-action">
                                                                        <button class="btn btn-sm btn-icon btn-outline-warning edit-consumption-btn"
                                                                            data-kode="${item.kode}" data-nama="${item.nama}" data-value="${item.value || ''}">
                                                                            <i class="fas fa-pencil-alt"></i>
                                                                        </button>
                                                                    </td>
                                                                </tr>
                                                            `;
                });

                if (data.length === 0) {
                    html = '<tr><td colspan="6" class="text-center py-4 text-muted">Master data kosong.</td></tr>';
                }

                consumptionTableBody.innerHTML = html;
                attachEditConsumptionListeners(); // Attach listeners after rendering
            }

            function populateTextArea(data) {
                const lines = [];
                let hasValues = false;

                data.forEach(item => {
                    if (item.value !== null && item.value !== '' && !isNaN(item.value)) {
                        lines.push(item.value);
                        hasValues = true;
                    } else {
                        lines.push("");
                    }
                });

                if (hasValues) {
                    pasteConsumption.value = lines.join('\n');
                } else {
                    pasteConsumption.value = '';
                    pasteConsumption.placeholder = "Belum ada data nilai. Paste data dari Excel di sini...";
                }
            }

            if (inputYear) inputYear.addEventListener('input', updatePreviewHeader);
            if (inputQuarter) inputQuarter.addEventListener('change', updatePreviewHeader);
            if (inputKabupaten) inputKabupaten.addEventListener('change', updatePreviewHeader);

            // Trigger once on load
            updatePreviewHeader();

            if (clearConsumptionBtn) {
                clearConsumptionBtn.addEventListener('click', () => {
                    const year = inputYear.value;
                    const quarter = inputQuarter.value;
                    const kabupatenId = inputKabupaten.value;

                    if (!year || !quarter || !kabupatenId) {
                        // If no filter selected, just clear the textarea
                        pasteConsumption.value = '';
                        pasteConsumption.focus();
                        return;
                    }

                    const hasSavedData = parsedConsumption.some(item => item.state === 'saved');
                    if (!hasSavedData) {
                        Swal.fire('Info', 'Tidak ada data nilai tersimpan yang bisa dihapus untuk filter ini.', 'info');
                        pasteConsumption.value = '';
                        return;
                    }

                    Swal.fire({
                        title: 'Hapus Data Nilai?',
                        text: "Seluruh data nilai konsumsi yang tersimpan untuk wilayah dan periode ini akan dihapus permanen.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Ya, Hapus!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            fetch('/seruti/clear-consumption', {
                                method: 'DELETE',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({
                                    year: year,
                                    quarter: quarter,
                                    kabupaten_id: kabupatenId
                                })
                            })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        Swal.fire('Terhapus!', data.message, 'success').then(() => {
                                            fetchExistingData(year, quarter, kabupatenId);
                                        });
                                    } else {
                                        Swal.fire('Gagal', data.message, 'error');
                                    }
                                })
                                .catch(err => {
                                    console.error(err);
                                    Swal.fire('Error', 'Terjadi kesalahan sistem.', 'error');
                                });
                        }
                    });
                });
            }

            if (printBtn) {
                printBtn.addEventListener('click', () => {
                    window.print();
                });
            }

            function attachEditConsumptionListeners() {
                document.querySelectorAll('.edit-consumption-btn').forEach(btn => {
                    btn.addEventListener('click', function () {
                        const kode = this.dataset.kode;
                        const nama = this.dataset.nama;
                        const oldValue = this.dataset.value;
                        const year = inputYear.value;
                        const quarter = inputQuarter.value;
                        const kabupatenId = inputKabupaten.value;

                        Swal.fire({
                            title: 'Edit Nilai Konsumsi',
                            html: `
                                                                        <div class="text-start mb-2">
                                                                            <small class="text-muted d-block mb-1">[${kode}] ${nama}</small>
                                                                            <label class="form-label fw-bold small">Konsumsi per Kapita (Rp)</label>
                                                                            <input type="number" id="editConsValue" class="form-control" value="${oldValue}" placeholder="Masukkan nilai...">
                                                                        </div>
                                                                    `,
                            showCancelButton: true,
                            confirmButtonText: 'Simpan',
                            cancelButtonText: 'Batal',
                            preConfirm: () => {
                                const newValue = document.getElementById('editConsValue').value;
                                if (newValue === '') {
                                    Swal.showValidationMessage('Nilai tidak boleh kosong');
                                    return false;
                                }
                                return newValue;
                            }
                        }).then((result) => {
                            if (result.isConfirmed) {
                                const newValue = result.value;

                                fetch('/seruti', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                    },
                                    body: JSON.stringify({
                                        year: year,
                                        quarter: quarter,
                                        kabupaten_id: kabupatenId,
                                        data: [{ kode: kode, value: newValue }]
                                    })
                                })
                                    .then(response => response.json())
                                    .then(data => {
                                        if (data.success) {
                                            Swal.fire('Berhasil!', 'Nilai berhasil diperbarui.', 'success').then(() => {
                                                fetchExistingData(year, quarter, kabupatenId);
                                            });
                                        } else {
                                            Swal.fire('Gagal', data.message, 'error');
                                        }
                                    })
                                    .catch(err => {
                                        console.error(err);
                                        Swal.fire('Error', 'Terjadi kesalahan sistem.', 'error');
                                    });
                            }
                        });
                    });
                });
            }

            if (actionSaveBtn) {
                actionSaveBtn.addEventListener('click', () => {
                    const year = document.getElementById('inputYear').value;
                    const quarter = document.getElementById('inputQuarter').value;
                    const kabupatenId = document.getElementById('inputKabupaten').value;

                    if (!year || !quarter || !kabupatenId) {
                        Swal.fire('Peringatan', 'Harap pilih Tahun, Triwulan, dan Kabupaten terlebih dahulu.', 'warning');
                        return;
                    }

                    const rawText = pasteConsumption.value.trim();
                    if (!rawText) {
                        Swal.fire('Info', 'Silakan paste data excel/nilai terlebih dahulu pada kolom input.', 'info');
                        return;
                    }

                    const rawVal = pasteConsumption.value;
                    const rows = rawVal.split('\n');

                    if (rows.length > masterCoicops.length) {
                        // Warning handled implicitly
                    }

                    // Reset parsedConsumption based on Master
                    const newParsed = JSON.parse(JSON.stringify(masterCoicops)).map(item => ({
                        ...item,
                        value: null,
                        state: 'empty'
                    }));

                    let validCount = 0;

                    masterCoicops.forEach((coicop, index) => {
                        if (index < rows.length) {
                            let valueStr = rows[index].trim();
                            if (valueStr !== '' && valueStr !== '-') {
                                let valueClean = valueStr.replace(/\./g, '').replace(/,/g, '.');
                                let value = parseFloat(valueClean);

                                if (!isNaN(value)) {
                                    newParsed[index].value = value;
                                    newParsed[index].state = 'ready';
                                    validCount++;
                                }
                            }
                        }
                    });

                    parsedConsumption = newParsed;
                    // Skip local preview rendering as requested by user
                    // renderConsumptionTable(parsedConsumption);

                    if (validCount === 0) {
                        Swal.fire('Info', 'Tidak ada nilai valid yang ditemukan.', 'info');
                        return;
                    }

                    // Proceed to Save
                    const dataToSave = parsedConsumption.filter(item => item.state === 'ready').map(item => ({
                        kode: item.kode,
                        value: item.value
                    }));

                    Swal.fire({
                        title: 'Simpan Data?',
                        text: `Anda akan menyimpan ${validCount} data untuk ${year} Q${quarter}.`,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Simpan',
                        showLoaderOnConfirm: true,
                        preConfirm: () => {
                            return fetch('/seruti', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({
                                    year: year,
                                    quarter: quarter,
                                    kabupaten_id: kabupatenId,
                                    data: dataToSave
                                })
                            })
                                .then(response => {
                                    if (!response.ok) {
                                        throw new Error(response.statusText)
                                    }
                                    return response.json()
                                })
                                .catch(error => {
                                    Swal.showValidationMessage(`Request failed: ${error}`);
                                })
                        },
                        allowOutsideClick: () => !Swal.isLoading()
                    }).then((result) => {
                        if (result.isConfirmed) {
                            if (result.value.success) {
                                Swal.fire('Berhasil!', result.value.message, 'success').then(() => {
                                    fetchExistingData(year, quarter, kabupatenId);
                                });
                            } else {
                                Swal.fire('Gagal', result.value.message || 'Error occurred', 'error');
                            }
                        }
                    });
                });
            }

        });
    </script>
    <style>
        .nav-pills .nav-link.active {
            background-color: var(--bps-blue);
        }

        .nav-pills .nav-link {
            color: #64748b;
        }

        /* Zoning Table Styles */
        .zone-data {
            background-color: rgba(248, 249, 250, 0.7) !important;
            color: #475569;
        }

        .zone-action {
            background-color: #ffffff !important;
        }

        .zone-divider {
            border-right: 1.5px dashed #e2e8f0 !important;
        }

        /* Row Hover Highlight - Unified for both zones */
        .table-hover tbody tr:hover td.zone-data,
        .table-hover tbody tr:hover td.zone-action {
            background-color: #f1f5f9 !important;
            transition: background-color 0.2s ease;
        }

        .scrollable-cell {
            max-width: 300px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .scrollable-cell:hover {
            white-space: normal;
            word-break: break-word;
        }
    </style>
@endpush