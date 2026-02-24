@extends('layouts.admin')

@section('title', 'Kelola Fenomena')

@section('content')
    <div class="fade-in-up">
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
        <ul class="nav nav-pills mb-4 gap-2" id="pills-tab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active rounded-pill px-4" id="pills-lap-usaha-tab" data-bs-toggle="pill"
                    data-bs-target="#pills-lap-usaha" type="button" role="tab" style="background-color: var(--bps-blue);">
                    <i class="fas fa-industry me-2"></i>Kode Lap Usaha
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-pill px-4 bg-warning-subtle text-dark" id="pills-indikator-tab"
                    data-bs-toggle="pill" data-bs-target="#pills-indikator" type="button" role="tab">
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
            <div class="tab-pane fade show active" id="pills-lap-usaha" role="tabpanel">
                <div class="card border-0 shadow-sm" style="border-radius: 12px; background: #fff;">
                    <div class="card-body p-5 text-center">
                        <i class="fas fa-industry fa-3x text-secondary opacity-50 mb-3"></i>
                        <h5 class="fw-bold">Manajemen Kode Lapangan Usaha</h5>
                        <p class="text-muted">Fitur ini sedang dalam pengembangan.</p>
                    </div>
                </div>
            </div>

            <!-- Tab 2: Kode Indikator -->
            <div class="tab-pane fade" id="pills-indikator" role="tabpanel">
                <div class="card border-0 shadow-sm" style="border-radius: 12px; background: #fff;">
                    <div class="card-body p-5 text-center">
                        <i class="fas fa-chart-line fa-3x text-secondary opacity-50 mb-3"></i>
                        <h5 class="fw-bold">Manajemen Kode Indikator</h5>
                        <p class="text-muted">Fitur ini sedang dalam pengembangan.</p>
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
        });
    </script>
@endsection