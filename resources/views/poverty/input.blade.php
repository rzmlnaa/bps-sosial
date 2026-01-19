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
                                        <label class="form-label text-muted small fw-bold text-uppercase">Kode
                                            Kabupaten</label>
                                        <input type="text" name="kode_kab" class="form-control" placeholder="Contoh: 6101"
                                            required>
                                    </div>
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
                                            <th class="border-0">Kode Kabupaten</th>
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
                                                <td class="fw-bold">{{ $kab->kode_kab }}</td>
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
                                                        data-id="{{ $kab->id }}" data-nama="{{ $kab->nama_kabupaten }}"
                                                        data-kode="{{ $kab->kode_kab }}">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <form action="{{ route('kabupaten.destroy', $kab->id) }}" method="POST"
                                                        class="d-inline form-delete">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button"
                                                            class="btn btn-sm btn-outline-danger border-0 btn-delete">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center py-4 text-muted">Belum ada data wilayah.</td>
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
                                        <input type="text" name="nama_variabel" class="form-control"
                                            placeholder="Contoh: GKS" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label text-muted small fw-bold text-uppercase">Bulan
                                            (Opsional)</label>
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
                                        <input type="number" name="tahun" class="form-control" placeholder="2024" min="2000"
                                            max="2099" required>
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
                                                    @php
                                                        $bulanNama = [
                                                            1 => 'Januari',
                                                            2 => 'Februari',
                                                            3 => 'Maret',
                                                            4 => 'April',
                                                            5 => 'Mei',
                                                            6 => 'Juni',
                                                            7 => 'Juli',
                                                            8 => 'Agustus',
                                                            9 => 'September',
                                                            10 => 'Oktober',
                                                            11 => 'November',
                                                            12 => 'Desember'
                                                        ];
                                                    @endphp
                                                    {{ $bulanNama[$v->bulan] ?? '-' }}
                                                </td>
                                                <td><span class="badge bg-blue-faded text-blue">{{ $v->tahun }}</span></td>
                                                <td>
                                                    <span class="badge bg-light text-dark border">
                                                        {{ $v->userAdd->name ?? 'Admin' }} pada
                                                        {{ $v->created_at->format('d/m/Y H:i') }}
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <form action="{{ route('variabel.destroy', $v->id) }}" method="POST"
                                                        class="d-inline form-delete">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button"
                                                            class="btn btn-sm btn-outline-danger border-0 btn-delete">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center py-4 text-muted">Belum ada data variabel.
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

            <!-- Tab 3: Input Data -->
            <div class="tab-pane fade" id="pills-input" role="tabpanel">
                <div class="row g-4">
                    <!-- Left Column: Input Form -->
                    <div class="col-lg-4">
                        <form action="{{ route('poverty-data.store') }}" method="POST">
                            @csrf
                            <!-- Filters -->
                            <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                                <div class="card-body p-4">
                                    <h6 class="fw-bold mb-3 text-uppercase text-muted"
                                        style="font-size: 0.8rem; letter-spacing: 0.5px;">1. Filter Data</h6>
                                    <div class="mb-3">
                                        <label class="form-label small fw-medium">Kabupaten/Kota</label>
                                        <select id="select_kabupaten_filter" name="kabupaten_id"
                                            class="form-select bg-light border-0" required>
                                            <option selected disabled value="">-- Pilih Wilayah --</option>
                                            @foreach($kabupatens as $kab)
                                                <option value="{{ $kab->id }}" {{ session('last_kabupaten_id') == $kab->id ? 'selected' : '' }}>[{{ $kab->kode_kab }}] {{ $kab->nama_kabupaten }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-0">
                                        <label class="form-label small fw-medium">Variabel</label>
                                        <select id="select_variabel_filter" name="variabel_id"
                                            class="form-select bg-light border-0" required>
                                            <option selected disabled value="">-- Pilih Variabel --</option>
                                            @foreach($variabels as $var)
                                                @php
                                                    $bulanNama = [
                                                        1 => 'Jan',
                                                        2 => 'Feb',
                                                        3 => 'Mar',
                                                        4 => 'Apr',
                                                        5 => 'Mei',
                                                        6 => 'Jun',
                                                        7 => 'Jul',
                                                        8 => 'Agu',
                                                        9 => 'Sep',
                                                        10 => 'Okt',
                                                        11 => 'Nov',
                                                        12 => 'Des'
                                                    ];
                                                    $bln = $var->bulan ? '(' . $bulanNama[$var->bulan] . ')' : '';
                                                @endphp
                                                <option value="{{ $var->id }}" {{ session('last_variabel_id') == $var->id ? 'selected' : '' }}>{{ $var->nama_variabel }} {{ $bln }} {{ $var->tahun }}
                                                </option>
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
                                        <textarea id="textarea_data_poverty" name="raw_data"
                                            class="form-control fw-mono border-0 bg-light" rows="15"
                                            placeholder="Paste data dari Excel/SPSS disini...&#10;Contoh:&#10;547005,00&#10;547005,00&#10;..."
                                            style="font-family: 'Courier New', monospace; font-size: 1rem; resize: none;"
                                            required></textarea>
                                    </div>
                                </div>
                                <div class="card-footer bg-white border-top-0 py-3">
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary flex-grow-1 fw-bold py-2 shadow-sm"
                                            style="background-color: var(--bps-orange); border: none;">
                                            <i class="fas fa-save me-2"></i>Simpan
                                        </button>
                                        <button type="button" id="btn_clear_data"
                                            class="btn btn-outline-danger fw-bold py-2 shadow-sm" style="display: none;">
                                            <i class="fas fa-eraser me-2"></i>Kosongkan
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
                                <h5 class="fw-bold mb-0" id="table_title">Data Wilayah</h5>
                                <a href="#" id="btn_export" class="btn btn-sm btn-outline-secondary" style="display: none;">
                                    <i class="fas fa-download me-1"></i> Export
                                </a>
                            </div>
                            <div class="table-responsive h-100">
                                <table class="table table-hover table-striped mb-0 text-center" style="font-size: 0.85rem;"
                                    id="table_poverty_data">
                                    <thead class="bg-light sticky-top" style="z-index: 1;">
                                        <tr class="fw-bold text-secondary" id="table_header_data">
                                            <th class="py-3" style="width: 60px;">Persentil</th>
                                            <th class="py-3">Pilih Wilayah Terlebih Dahulu</th>
                                        </tr>
                                    </thead>
                                    <tbody class="border-top-0" id="table_body_data">
                                        <tr>
                                            <td colspan="2" class="text-center py-5 text-muted">
                                                <i class="fas fa-info-circle me-1"></i> Silakan pilih wilayah pada filter
                                                untuk menampilkan data.
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

    <!-- Modal Edit Kabupaten -->
    <div class="modal fade" id="modalEditKabupaten" tabindex="-1" aria-labelledby="modalEditKabupatenLabel"
        aria-hidden="true">
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
                            <label class="form-label text-muted small fw-bold text-uppercase">Kode Kabupaten</label>
                            <input type="text" name="kode_kab" id="edit_kode_kab" class="form-control"
                                placeholder="Contoh: 6101" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold text-uppercase">Nama Kabupaten/Kota</label>
                            <input type="text" name="nama_kabupaten" id="edit_nama_kabupaten" class="form-control"
                                placeholder="Contoh: Kab. Sambas" required>
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

            // 2.5 Auto-switch to Input tab if redirecting after save
            @if(session('last_kabupaten_id'))
                const inputTab = document.querySelector('#pills-input-tab');
                if (inputTab) {
                    const tab = new bootstrap.Tab(inputTab);
                    tab.show();
                    localStorage.setItem(storageKey, '#pills-input');
                }
            @endif

                            // 3. Edit Kabupaten Modal Logic
                            const editButtons = document.querySelectorAll('.btn-edit-kabupaten');
            const modalEdit = new bootstrap.Modal(document.getElementById('modalEditKabupaten'));
            const formEdit = document.getElementById('formEditKabupaten');
            const inputEditNama = document.getElementById('edit_nama_kabupaten');

            editButtons.forEach(button => {
                button.addEventListener('click', function () {
                    const id = this.getAttribute('data-id');
                    const nama = this.getAttribute('data-nama');
                    const kode = this.getAttribute('data-kode');

                    document.getElementById('edit_nama_kabupaten').value = nama;
                    document.getElementById('edit_kode_kab').value = kode;
                    formEdit.action = `/kabupaten/${id}`;

                    modalEdit.show();
                });
            });

            // 4. Delete Confirmation Logic (Event Delegation)
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

            // 5. Dynamic Data Loading Logic
            const selectKabupaten = document.getElementById('select_kabupaten_filter');
            const selectVariabel = document.getElementById('select_variabel_filter');
            const textareaData = document.getElementById('textarea_data_poverty');
            const btnExport = document.getElementById('btn_export');
            const tableTitle = document.getElementById('table_title');
            const tableHeader = document.getElementById('table_header_data');
            const tableBody = document.getElementById('table_body_data');

            // Function to format number to IDR style
            const formatIDR = (num) => {
                return new Intl.NumberFormat('id-ID', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }).format(num);
            };

            // Loading state for table
            const showLoading = () => {
                tableBody.innerHTML = `<tr><td colspan="${tableHeader.children.length}" class="text-center py-5"><div class="spinner-border text-primary" role="status"></div><p class="mt-2 text-muted">Memuat data...</p></td></tr>`;
            };

            // When Kabupaten is selected
            selectKabupaten.addEventListener('change', function () {
                const kabId = this.value;
                const kabName = this.options[this.selectedIndex].text;
                tableTitle.innerText = `Data Tersimpan - ${kabName}`;

                // Show and update export button
                btnExport.style.display = 'inline-block';
                btnExport.href = `/poverty-data/export/${kabId}`;

                showLoading();

                fetch(`/poverty-data/get-data/${kabId}`)
                    .then(response => response.json())
                    .then(result => {
                        const { data, variabels } = result;

                        // Update Header
                        let headerHtml = `<th class="py-3" style="width: 60px;">Persentil</th>`;
                        variabels.forEach(v => {
                            headerHtml += `<th class="py-3">${v.nama_variabel} ${v.tahun}</th>`;
                        });
                        tableHeader.innerHTML = headerHtml;

                        // Update Body
                        let bodyHtml = '';
                        const persentils = Object.keys(data).sort((a, b) => a - b);

                        if (persentils.length === 0) {
                            bodyHtml = `<tr><td colspan="${variabels.length + 1}" class="text-center py-5 text-muted">Belum ada data nilai untuk wilayah ini.</td></tr>`;
                        } else {
                            persentils.forEach(p => {
                                bodyHtml += `<tr><td class="fw-bold text-muted bg-light">${p}</td>`;
                                variabels.forEach(v => {
                                    const record = data[p].find(r => r.variabel_kemiskinan_id == v.id);
                                    bodyHtml += `<td>${record ? formatIDR(record.nilai) : '-'}</td>`;
                                });
                                bodyHtml += `</tr>`;
                            });
                        }
                        tableBody.innerHTML = bodyHtml;

                        // If Variabel is already selected, refresh textarea too
                        if (selectVariabel.value) {
                            selectVariabel.dispatchEvent(new Event('change'));
                        }
                        checkClearButtonVisibility();
                    });
            });

            // When Variabel is selected
            selectVariabel.addEventListener('change', function () {
                checkClearButtonVisibility();
                const kabId = selectKabupaten.value;
                const varId = this.value;

                if (!kabId) return;

                textareaData.placeholder = "Memuat data...";
                textareaData.value = "";

                fetch(`/poverty-data/get-raw/${kabId}/${varId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.length > 0) {
                            textareaData.value = data.join('\n');
                            textareaData.placeholder = "Data ditemukan. Silakan edit dan simpan kembali.";
                        } else {
                            textareaData.value = "";
                            textareaData.placeholder = "Belum ada data. Paste data dari Excel/SPSS disini...";
                        }
                    });
            });

            // 6. Trigger initial load if last session exists
            if (selectKabupaten.value) {
                selectKabupaten.dispatchEvent(new Event('change'));
            }

            // 7. Clear Data Button Logic
            const btnClearData = document.getElementById('btn_clear_data');

            function checkClearButtonVisibility() {
                if (selectKabupaten.value && selectVariabel.value) {
                    btnClearData.style.display = 'block';
                } else {
                    btnClearData.style.display = 'none';
                }
            }

            btnClearData.addEventListener('click', function () {
                const kabName = selectKabupaten.options[selectKabupaten.selectedIndex].text;
                const varName = selectVariabel.options[selectVariabel.selectedIndex].text;

                Swal.fire({
                    title: 'Kosongkan Data?',
                    html: `Apakah Anda yakin ingin menghapus <b>seluruh data</b> untuk:<br><br><b>${kabName}</b><br><b>${varName}</b>`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, Kosongkan!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Create a temporary form to submit DELETE request
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = '{{ route('poverty-data.clear') }}';

                        const csrfToken = document.createElement('input');
                        csrfToken.type = 'hidden';
                        csrfToken.name = '_token';
                        csrfToken.value = '{{ csrf_token() }}';

                        const methodField = document.createElement('input');
                        methodField.type = 'hidden';
                        methodField.name = '_method';
                        methodField.value = 'DELETE';

                        const kabField = document.createElement('input');
                        kabField.type = 'hidden';
                        kabField.name = 'kabupaten_id';
                        kabField.value = selectKabupaten.value;

                        const varField = document.createElement('input');
                        varField.type = 'hidden';
                        varField.name = 'variabel_id';
                        varField.value = selectVariabel.value;

                        form.appendChild(csrfToken);
                        form.appendChild(methodField);
                        form.appendChild(kabField);
                        form.appendChild(varField);
                        document.body.appendChild(form);
                        form.submit();
                    }
                });
            });
        });
    </script>
@endpush