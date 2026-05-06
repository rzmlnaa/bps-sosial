@extends('layouts.admin')

@section('title', 'Visualisasi Desa Cantik')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4 mt-1 fade-in-up">
        <div>
            <h1 class="h3 mb-0 fw-bold" style="color: var(--bps-orange);">Visualisasi Desa Cantik</h1>
            <p class="text-muted small mb-0">Statistik dan Monitoring Program Desa Cantik di Kalimantan Barat</p>
        </div>
        <div class="d-flex align-items-center gap-3">
            <form action="{{ route('desa-cantik.index') }}" method="GET"
                class="d-flex align-items-center gap-2 bg-white p-2 rounded-3 shadow-sm border">
                <i class="fas fa-filter text-muted small"></i>
                <label for="periode_id" class="text-nowrap mb-0 small fw-bold text-muted">Periode:</label>
                <select name="periode_id" id="periode_id" class="form-select form-select-sm border-0 fw-bold"
                    onchange="this.form.submit()" style="cursor: pointer;">

                    @foreach($periodes as $p)
                        <option value="{{ $p->id }}" @selected($selectedPeriodeId == $p->id)>{{ $p->tahun }}
                            @if($p->is_active) (Aktif) @endif
                        </option>
                    @endforeach
                </select>
            </form>
            @if(auth()->check() && auth()->user()->kabupaten && auth()->user()->kabupaten->kode_kab == '6100')
                <a href="{{ route('desa-cantik.kelola') }}" class="btn btn-sm text-white"
                    style="background-color: var(--bps-orange);">
                    <i class="fas fa-cog me-1 fa-spin"></i> Kelola
                </a>
            @endif
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4 fade-in-up">
        <!-- Total Peserta -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 overflow-hidden"
                style="border-left: 4px solid var(--bps-blue) !important;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-xs font-weight-bold text-uppercase mb-1" style="color: var(--bps-blue);">
                                Total Peserta</div>
                            <div class="h4 mb-0 font-weight-bold text-dark">
                                {{ $totalPeserta }}
                            </div>
                        </div>
                        <div class="p-3 rounded-circle" style="background-color: rgba(0, 147, 221, 0.1);">
                            <i class="fas fa-users" style="color: var(--bps-blue);"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Disetujui -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid var(--bps-green) !important;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-xs font-weight-bold text-uppercase mb-1" style="color: var(--bps-green);">
                                Progress Disetujui</div>
                            <div class="h4 mb-0 font-weight-bold text-dark">{{ $totalDisetujui }}</div>
                        </div>
                        <div class="p-3 rounded-circle" style="background-color: rgba(122, 184, 0, 0.1);">
                            <i class="fas fa-check-double" style="color: var(--bps-green);"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Menunggu -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid var(--bps-orange) !important;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-xs font-weight-bold text-uppercase mb-1" style="color: var(--bps-orange);">
                                Menunggu Verifikasi</div>
                            <div class="h4 mb-0 font-weight-bold text-dark">{{ $totalMenunggu }}</div>
                        </div>
                        <div class="p-3 rounded-circle" style="background-color: rgba(245, 130, 32, 0.1);">
                            <i class="fas fa-clock fa-spin" style="color: var(--bps-orange);"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Ditolak -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #dc3545 !important;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-xs font-weight-bold text-uppercase mb-1" style="color: #dc3545;">
                                Perlu Perbaikan</div>
                            <div class="h4 mb-0 font-weight-bold text-dark">{{ $totalDitolak }}</div>
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
        <!-- Kabupaten Bar Chart -->
        <div class="col-xl-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3 d-flex align-items-center">
                    <i class="fas fa-map-marked-alt text-primary me-2"></i>
                    <h6 class="m-0 font-weight-bold text-dark">Sebaran Peserta Desa Cantik Berdasarkan Kabupaten/Kota
                    </h6>
                </div>
                <div class="card-body">
                    <div style="height: 350px;">
                        <canvas id="kabupatenChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status Progress -->
        <div class="col-xl-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3 d-flex align-items-center">
                    <i class="fas fa-tasks text-primary me-2"></i>
                    <h6 class="m-0 font-weight-bold text-dark">Status Progress Kegiatan</h6>
                </div>
                <div class="card-body d-flex flex-column justify-content-center">
                    <div style="height: 220px;">
                        <canvas id="statusChart"></canvas>
                    </div>
                    <div class="mt-4">
                        <div class="d-flex justify-content-between align-items-center mb-2 small">
                            <span><i class="fas fa-circle me-1" style="color: var(--bps-green);"></i> Disetujui</span>
                            <span class="fw-bold">{{ $totalDisetujui }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2 small">
                            <span><i class="fas fa-circle me-1" style="color: var(--bps-orange);"></i> Menunggu
                                Verifikasi</span>
                            <span class="fw-bold">{{ $totalMenunggu }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2 small">
                            <span><i class="fas fa-circle me-1" style="color: #dc3545;"></i> Perlu Perbaikan
                                (Ditolak)</span>
                            <span class="fw-bold">{{ $totalDitolak }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center small">
                            <span><i class="fas fa-circle me-1" style="color: #6c757d;"></i> Draf</span>
                            <span class="fw-bold">{{ $totalDraf }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <!-- Trend Periode Chart -->
        <div class="col-xl-12">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3 d-flex align-items-center">
                    <i class="fas fa-chart-line text-success me-2"></i>
                    <h6 class="m-0 font-weight-bold text-dark">Tren Partisipasi Peserta dari Tahun ke Tahun</h6>
                </div>
                <div class="card-body">
                    <div style="height: 300px;">
                        <canvas id="trendChart"></canvas>
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

            // 1. Kabupaten Bar Chart
            new Chart(document.getElementById("kabupatenChart"), {
                type: 'bar',
                data: {
                    labels: @json($rekapKabupaten->pluck('nama')),
                    datasets: [{
                        label: "Total Peserta",
                        backgroundColor: "#0093dd",
                        borderRadius: 5,
                        data: @json($rekapKabupaten->pluck('pesertas_count')),
                    }],
                },
                options: {
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, ticks: { stepSize: 1, precision: 0 } },
                        x: { grid: { display: false } }
                    }
                }
            });

            // 2. Status Chart (Donut)
            new Chart(document.getElementById("statusChart"), {
                type: 'doughnut',
                data: {
                    labels: ["Disetujui", "Menunggu Verifikasi", "Ditolak", "Draf"],
                    datasets: [{
                        data: [{{ $totalDisetujui }}, {{ $totalMenunggu }}, {{ $totalDitolak }}, {{ $totalDraf }}],
                        backgroundColor: ['#7ab800', '#f58220', '#dc3545', '#6c757d'],
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

            // 3. Trend Periode Line Chart
            function getGradient(ctx, color) {
                let gradient = ctx.createLinearGradient(0, 0, 0, 400);
                gradient.addColorStop(0, color);
                gradient.addColorStop(1, 'rgba(255,255,255,0)');
                return gradient;
            }

            const ctxTrend = document.getElementById("trendChart").getContext('2d');
            new Chart(ctxTrend, {
                type: 'line',
                data: {
                    labels: @json($trendPeriode->pluck('tahun')),
                    datasets: [{
                        label: "Jumlah Peserta",
                        tension: 0.4,
                        fill: true,
                        backgroundColor: getGradient(ctxTrend, 'rgba(122, 184, 0, 0.2)'),
                        borderColor: "#7ab800",
                        borderWidth: 3,
                        pointRadius: 4,
                        pointBackgroundColor: "#7ab800",
                        pointBorderColor: "#fff",
                        pointHoverRadius: 6,
                        data: @json($trendPeriode->pluck('pesertas_count')),
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

        });
    </script>
@endpush