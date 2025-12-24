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
                                <form>
                                    <div class="mb-3">
                                        <label class="form-label text-muted small fw-bold text-uppercase">Nama
                                            Kabupaten/Kota</label>
                                        <input type="text" class="form-control" placeholder="Contoh: Kab. Sambas">
                                    </div>
                                    <button type="button" class="btn text-white w-100 fw-medium"
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
                                            <th class="text-center border-0">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="border-top-0">
                                        @php
                                            $kabupatens = [
                                                ['name' => 'Kab. Sambas', 'admin' => 'Admin BPS'],
                                                ['name' => 'Kab. Mempawah', 'admin' => 'Admin BPS'],
                                                ['name' => 'Kab. Sanggau', 'admin' => 'Super Admin'],
                                                ['name' => 'Kab. Ketapang', 'admin' => 'Admin BPS'],
                                            ];
                                        @endphp
                                        @foreach($kabupatens as $index => $kab)
                                            <tr>
                                                <td class="ps-4 text-muted">{{ $index + 1 }}</td>
                                                <td class="fw-medium">{{ $kab['name'] }}</td>
                                                <td>
                                                    <span class="badge bg-light text-dark border">{{ $kab['admin'] }}</span>
                                                </td>
                                                <td class="text-center">
                                                    <button class="btn btn-sm btn-outline-warning border-0"><i
                                                            class="fas fa-edit"></i></button>
                                                </td>
                                            </tr>
                                        @endforeach
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
                                <form>
                                    <div class="mb-3">
                                        <label class="form-label text-muted small fw-bold text-uppercase">Nama
                                            Variabel</label>
                                        <input type="text" class="form-control" placeholder="Contoh: GKS">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label text-muted small fw-bold text-uppercase">Tahun</label>
                                        <input type="number" class="form-control" placeholder="2024" min="2000" max="2099">
                                    </div>
                                    <button type="button" class="btn text-white w-100 fw-medium"
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
                                            <th class="border-0">Tahun</th>
                                            <th class="border-0">Dibuat Oleh</th>
                                            <th class="text-center border-0">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="border-top-0">
                                        @php
                                            $vars = [
                                                ['name' => 'GKS', 'year' => '2024', 'admin' => 'Admin BPS'],
                                                ['name' => 'Asli', 'year' => '2024', 'admin' => 'Admin BPS'],
                                                ['name' => 'Rilis', 'year' => '2024', 'admin' => 'Super Admin'],
                                                ['name' => 'GK', 'year' => '2023', 'admin' => 'Admin BPS'],
                                            ];
                                        @endphp
                                        @foreach($vars as $index => $v)
                                            <tr>
                                                <td class="ps-4 text-muted">{{ $index + 1 }}</td>
                                                <td class="fw-medium">{{ $v['name'] }}</td>
                                                <td><span class="badge bg-blue-faded text-blue">{{ $v['year'] }}</span></td>
                                                <td>
                                                    <span class="badge bg-light text-dark border">{{ $v['admin'] }}</span>
                                                </td>
                                                <td class="text-center">
                                                    <button class="btn btn-sm btn-outline-danger border-0"><i
                                                            class="fas fa-trash-alt"></i></button>
                                                </td>
                                            </tr>
                                        @endforeach
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
                                            <option>{{ $kab['name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-0">
                                    <label class="form-label small fw-medium">Variabel</label>
                                    <select class="form-select bg-light border-0">
                                        <option selected disabled>-- Pilih Variabel --</option>
                                        <option>GK 2024</option>
                                        <option>GKS 2024</option>
                                        <option>Rilis 2024</option>
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
        });
    </script>
@endpush