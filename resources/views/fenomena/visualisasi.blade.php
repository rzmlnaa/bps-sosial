@extends('layouts.admin')

@section('title', 'Visualisasi Fenomena')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4 mt-1 fade-in-up">
        <div>
            <h1 class="h3 mb-0 fw-bold" style="color: var(--bps-orange);">Visualisasi Statistik Fenomena</h1>
            <p class="text-muted small mb-0">Tren dan analisis data fenomena sosial ekonomi yang terverifikasi</p>
        </div>
        <div class="d-flex align-items-center gap-3">
            <form action="{{ route('fenomena.visualisasi') }}" method="GET"
                class="d-flex align-items-center gap-2 bg-white p-2 rounded-3 shadow-sm border">
                <i class="fas fa-filter text-muted small"></i>
                <label for="tahun" class="text-nowrap mb-0 small fw-bold text-muted">Tahun:</label>
                <select name="tahun" id="tahun" class="form-select form-select-sm border-0 fw-bold"
                    onchange="this.form.submit()" style="width: 100px; cursor: pointer;">
                    <option value="">Semua</option>
                    @foreach($availableTahun as $t)
                        <option value="{{ $t }}" @selected($tahun == $t)>{{ $t }}</option>
                    @endforeach
                </select>
            </form>

        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4 fade-in-up">
        <!-- Total -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 overflow-hidden"
                style="border-left: 4px solid var(--bps-blue) !important;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-xs font-weight-bold text-uppercase mb-1" style="color: var(--bps-blue);">
                                Total Fenomena</div>
                            <div class="h4 mb-0 font-weight-bold text-dark">
                                {{ $totalVerified + $totalPending + $totalRejected }}
                            </div>
                        </div>
                        <div class="bg-blue-faded p-3 rounded-circle">
                            <i class="fas fa-database"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Verified -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid var(--bps-green) !important;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-xs font-weight-bold text-uppercase mb-1" style="color: var(--bps-green);">
                                Terverifikasi</div>
                            <div class="h4 mb-0 font-weight-bold text-dark">{{ $totalVerified }}</div>
                        </div>
                        <div class="bg-green-faded p-3 rounded-circle">
                            <i class="fas fa-check-double"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Pending -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid var(--bps-orange) !important;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-xs font-weight-bold text-uppercase mb-1" style="color: var(--bps-orange);">
                                Pending</div>
                            <div class="h4 mb-0 font-weight-bold text-dark">{{ $totalPending }}</div>
                        </div>
                        <div class="bg-orange-faded p-3 rounded-circle">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Rejected -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #dc3545 !important;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-xs font-weight-bold text-uppercase mb-1" style="color: #dc3545;">
                                Ditolak</div>
                            <div class="h4 mb-0 font-weight-bold text-dark">{{ $totalRejected }}</div>
                        </div>
                        <div class="bg-danger bg-opacity-10 p-3 rounded-circle">
                            <i class="fas fa-times-circle text-danger"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <!-- Monthly Trend -->
        <div class="col-xl-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3 d-flex align-items-center">
                    <i class="fas fa-chart-line text-primary me-2"></i>
                    <h6 class="m-0 font-weight-bold text-dark">Tren Fenomena Bulanan ({{ $tahun ?? 'Semua Tahun' }})
                    </h6>
                </div>
                <div class="card-body">
                    <div style="height: 300px;">
                        <canvas id="monthlyTrendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <!-- Examination Status -->
        <div class="col-xl-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3 d-flex align-items-center">
                    <i class="fas fa-tasks text-primary me-2"></i>
                    <h6 class="m-0 font-weight-bold text-dark">Status Pemeriksaan</h6>
                </div>
                <div class="card-body d-flex flex-column justify-content-center">
                    <div style="height: 220px;">
                        <canvas id="examinationChart"></canvas>
                    </div>
                    <div class="mt-4">
                        <div class="d-flex justify-content-between align-items-center mb-2 small">
                            <span><i class="fas fa-circle me-1" style="color: var(--bps-green);"></i> Terverifikasi</span>
                            <span class="fw-bold">{{ $totalVerified }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2 small">
                            <span><i class="fas fa-circle me-1" style="color: var(--bps-blue);"></i> Pending</span>
                            <span class="fw-bold">{{ $totalPending }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center small">
                            <span><i class="fas fa-circle me-1" style="color: #dc3545;"></i> Ditolak</span>
                            <span class="fw-bold">{{ $totalRejected }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <!-- Sektor Usaha -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-building text-info me-2"></i>
                        <h6 class="m-0 font-weight-bold text-dark">Top 10 Lapangan Usaha</h6>
                    </div>
                    <span class="badge bg-light text-dark fw-normal">Verified Only</span>
                </div>
                <div class="card-body">
                    <div style="height: 350px;">
                        <canvas id="sektorChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <!-- Indikator Utama -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-chart-pie text-primary me-2"></i>
                        <h6 class="m-0 font-weight-bold text-dark">Top 10 Indikator Utama</h6>
                    </div>
                    <span class="badge bg-light text-dark fw-normal">Verified Only</span>
                </div>
                <div class="card-body">
                    <div style="height: 350px;">
                        <canvas id="indikatorUtamaChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <!-- Indikator Dampak -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-bullseye text-secondary me-2"></i>
                        <h6 class="m-0 font-weight-bold text-dark">Top 10 Indikator Dampak</h6>
                    </div>
                    <span class="badge bg-light text-dark fw-normal">Verified Only</span>
                </div>
                <div class="card-body">
                    <div style="height: 350px;">
                        <canvas id="indikatorDampakChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <!-- Sumber Berita -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3 d-flex align-items-center">
                    <i class="fas fa-newspaper text-warning me-2"></i>
                    <h6 class="m-0 font-weight-bold text-dark">Rekap Sumber Fenomena</h6>
                </div>
                <div class="card-body">
                    <div style="height: 350px;">
                        <canvas id="sumberChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Jenis Fenomena -->
        <div class="col-lg-12">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3 d-flex align-items-center">
                    <i class="fas fa-tags text-success me-2"></i>
                    <h6 class="m-0 font-weight-bold text-dark">Rekap Jenis Fenomena</h6>
                </div>
                <div class="card-body">
                    <div style="height: 300px;">
                        <canvas id="jenisChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <style>
        .text-navy {
            color: #1a237e;
        }

        .text-xs {
            font-size: .75rem;
        }

        .bg-opacity-10 {
            --bs-bg-opacity: 0.1;
        }

        .card {
            transition: transform 0.2s ease;
        }

        .card:hover {
            transform: translateY(-3px);
        }
    </style>

@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Global Config
            Chart.defaults.font.family = "'Inter', sans-serif";
            Chart.defaults.color = '#858796';

            // Helper function for gradient
            function getGradient(ctx, color) {
                let gradient = ctx.createLinearGradient(0, 0, 0, 400);
                gradient.addColorStop(0, color);
                gradient.addColorStop(1, 'rgba(255,255,255,0)');
                return gradient;
            }

            // 1. Monthly Trend Chart
            const ctxTrend = document.getElementById("monthlyTrendChart").getContext('2d');
            new Chart(ctxTrend, {
                type: 'line',
                data: {
                    labels: @json($bulanLabels),
                    datasets: [{
                        label: "Jumlah Fenomena",
                        tension: 0.4,
                        fill: true,
                        backgroundColor: getGradient(ctxTrend, 'rgba(0, 147, 221, 0.2)'),
                        borderColor: "#0093dd",
                        borderWidth: 3,
                        pointRadius: 4,
                        pointBackgroundColor: "#0093dd",
                        pointBorderColor: "#fff",
                        pointHoverRadius: 6,
                        data: @json(array_values($bulanCounts)),
                    }],
                },
                options: {
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false }, tooltip: { mode: 'index', intersect: false } },
                    scales: {
                        y: { beginAtZero: true, ticks: { stepSize: 1, precision: 0 }, grid: { borderDash: [2, 2] } },
                        x: { grid: { display: false } }
                    }
                }
            });

            // 2. Examination Status (Donut)
            new Chart(document.getElementById("examinationChart"), {
                type: 'doughnut',
                data: {
                    labels: ["Terverifikasi", "Pending", "Ditolak"],
                    datasets: [{
                        data: [{{ $totalVerified }}, {{ $totalPending }}, {{ $totalRejected }}],
                        backgroundColor: ['#7ab800', '#0093dd', '#dc3545'],
                        hoverOffset: 10,
                        borderWidth: 0
                    }],
                },
                options: {
                    maintainAspectRatio: false,
                    cutout: '75%',
                    plugins: { legend: { display: false } }
                }
            });

            // 3. Sektor Chart (Horizontal Bar)
            new Chart(document.getElementById("sektorChart"), {
                type: 'bar',
                data: {
                    labels: @json($rekapSektor->pluck('nama')->map(fn($n) => \Illuminate\Support\Str::limit($n, 25))),
                    datasets: [{
                        label: "Total Fenomena",
                        backgroundColor: "#36b9cc",
                        borderRadius: 5,
                        data: @json($rekapSektor->pluck('fenomenas_count')),
                    }],
                },
                options: {
                    indexAxis: 'y',
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { x: { beginAtZero: true, grid: { display: false } }, y: { grid: { display: false } } }
                }
            });

            // 4. Indikator Utama Chart
            new Chart(document.getElementById("indikatorUtamaChart"), {
                type: 'bar',
                data: {
                    labels: @json($rekapIndikatorUtama->pluck('nama')->map(fn($n) => \Illuminate\Support\Str::limit($n, 25))),
                    datasets: [{
                        label: "Total Fenomena",
                        backgroundColor: "#0093dd",
                        borderRadius: 5,
                        data: @json($rekapIndikatorUtama->pluck('fenomenas_count')),
                    }],
                },
                options: {
                    indexAxis: 'y',
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { x: { beginAtZero: true, grid: { display: false } }, y: { grid: { display: false } } }
                }
            });

            // 4b. Indikator Dampak Chart
            new Chart(document.getElementById("indikatorDampakChart"), {
                type: 'bar',
                data: {
                    labels: @json($rekapIndikatorDampak->pluck('nama')->map(fn($n) => \Illuminate\Support\Str::limit($n, 25))),
                    datasets: [{
                        label: "Total Fenomena",
                        backgroundColor: "#6c757d",
                        borderRadius: 5,
                        data: @json($rekapIndikatorDampak->pluck('fenomenas_count')),
                    }],
                },
                options: {
                    indexAxis: 'y',
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { x: { beginAtZero: true, grid: { display: false } }, y: { grid: { display: false } } }
                }
            });

            // 5. Sumber Chart
            new Chart(document.getElementById("sumberChart"), {
                type: 'bar',
                data: {
                    labels: @json($rekapSumber->pluck('nama')),
                    datasets: [{
                        label: "Total",
                        backgroundColor: ["#0093dd", "#7ab800", "#f58220", "#36b9cc", "#6610f2"],
                        borderRadius: 8,
                        data: @json($rekapSumber->pluck('fenomenas_count')),
                    }],
                },
                options: {
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true }, x: { grid: { display: false } } }
                }
            });

            // 6. Jenis Chart
            new Chart(document.getElementById("jenisChart"), {
                type: 'bar',
                data: {
                    labels: @json($rekapJenis->pluck('nama')->map(fn($n) => \Illuminate\Support\Str::limit($n, 15))),
                    datasets: [{
                        label: "Total",
                        backgroundColor: "#7ab800",
                        borderRadius: 8,
                        data: @json($rekapJenis->pluck('fenomenas_count')),
                    }],
                },
                options: {
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true }, x: { grid: { display: false } } }
                }
            });
        });
    </script>
@endpush