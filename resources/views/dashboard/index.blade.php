@extends('layouts.admin')

@section('title', 'Dashboard Kemiskinan Kalimantan Barat')

@section('content')
    @php
        $statusPengembangan = true;
    @endphp
    @if ($statusPengembangan)
        <style>
            .dev-wrapper {
                min-height: calc(100vh - 120px);
                /* sesuaikan tinggi navbar/header */
                display: flex;
                align-items: center;
                justify-content: center;
            }
        </style>

        <div class="dev-wrapper">
            <div class="dev-box text-center">

                <img src="https://i.pinimg.com/originals/48/e3/03/48e303bf57f8ad627c73a0e0e30f5f33.gif" width="300"
                    class="mb-1">

                <h4 class="fw-bold text-warning mb-2">
                    🚧 Fitur Dalam Pengembangan
                </h4>

                <p class="text-muted mb-0">
                    Halaman ini masih dalam tahap pengembangan. <br>
                    Beberapa fitur mungkin belum berjalan secara optimal.
                    <br><br>
                    © BPS Provinsi Kalimantan Barat
                    <br>
                    Dikembangkan oleh <a href="https://kostapp.reservasiaja.com/portofolio">Peserta Magang</a> –
                    Program
                    MagangHUB Kemnaker
                </p>

            </div>
        </div>

    @else
        <div class="fade-in-up">
            <!-- Header -->
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
                <div>
                    <h2 class="fw-bold text-navy mb-1" style="color: var(--primary-navy);">Dashboard Kemiskinan</h2>
                    <p class="text-muted mb-0">Gambaran umum indikator kemiskinan di Provinsi Kalimantan Barat</p>
                </div>
                <div class="mt-3 mt-md-0">
                    <select class="form-select border-0 shadow-sm" style="min-width: 150px;">
                        <option value="2024" selected>Tahun 2024</option>
                        <option value="2023">Tahun 2023</option>
                        <option value="2022">Tahun 2022</option>
                    </select>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="row g-4 mb-4">
                <!-- Card 1: Persentase -->
                <div class="col-md-6 col-lg-3">
                    <div class="stats-card delay-100">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="card-icon bg-navy-faded">
                                <i class="fas fa-percent"></i>
                            </div>
                            <div class="trend-badge trend-down">
                                <i class="fas fa-arrow-down"></i> 0.25%
                            </div>
                        </div>
                        <div class="stat-value text-navy">6.71%</div>
                        <div class="stat-label">Persentase Penduduk Miskin</div>
                    </div>
                </div>

                <!-- Card 2: Jumlah -->
                <div class="col-md-6 col-lg-3">
                    <div class="stats-card delay-200">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="card-icon bg-teal-faded">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="trend-badge trend-down">
                                <i class="fas fa-arrow-down"></i> 1.2k
                            </div>
                        </div>
                        <div class="stat-value" style="color: var(--primary-teal);">354.21k</div>
                        <div class="stat-label">Jumlah Penduduk Miskin</div>
                    </div>
                </div>

                <!-- Card 3: Garis Kemiskinan -->
                <div class="col-md-6 col-lg-3">
                    <div class="stats-card delay-300">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="card-icon bg-green-faded">
                                <i class="fas fa-money-bill-wave"></i>
                            </div>
                            <div class="trend-badge trend-up">
                                <i class="fas fa-arrow-up"></i> 5.4%
                            </div>
                        </div>
                        <div class="stat-value" style="color: var(--soft-green);">Rp 586.2k</div>
                        <div class="stat-label">Garis Kemiskinan (GK)</div>
                    </div>
                </div>

                <!-- Card 4: Indeks Kedalaman -->
                <div class="col-md-6 col-lg-3">
                    <div class="stats-card delay-300">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="card-icon" style="background-color: rgba(99, 102, 241, 0.1); color: #6366f1;">
                                <i class="fas fa-chart-area"></i>
                            </div>
                            <div class="trend-badge trend-down">
                                <i class="fas fa-arrow-down"></i> 0.02
                            </div>
                        </div>
                        <div class="stat-value" style="color: #6366f1;">0.95</div>
                        <div class="stat-label">Indeks Kedalaman (P1)</div>
                    </div>
                </div>
            </div>

            <!-- Main Chart Section -->
            <div class="row mb-4">
                <div class="col-lg-8">
                    <div class="chart-container h-100">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="fw-bold mb-0">Tren Kemiskinan Kalimantan Barat</h5>
                            <button class="btn btn-sm btn-outline-light text-muted border">
                                <i class="fas fa-download me-1"></i> Export
                            </button>
                        </div>
                        <!-- <canvas id="povertyTrendChart" height="300"></canvas> -->
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="chart-container h-100">
                        <h5 class="fw-bold mb-4">Komposisi Menurut Wilayah</h5>
                        <canvas id="regionPieChart" height="250"></canvas>
                        <div class="mt-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-sm text-muted">Perkotaan</span>
                                <span class="fw-bold">42%</span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar" role="progressbar"
                                    style="width: 42%; background-color: var(--primary-navy);" aria-valuenow="42"
                                    aria-valuemin="0" aria-valuemax="100"></div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-2 mt-3">
                                <span class="text-sm text-muted">Perdesaan</span>
                                <span class="fw-bold">58%</span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar" role="progressbar"
                                    style="width: 58%; background-color: var(--primary-teal);" aria-valuenow="58"
                                    aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Section -->
            <div class="row g-4">
                <!-- Fenomena Highlight -->
                <div class="col-lg-6">
                    <div class="stats-card">
                        <h5 class="fw-bold mb-3">Fenomena Terbaru</h5>
                        <div class="d-flex gap-3 mb-3 pb-3 border-bottom">
                            <div class="flex-shrink-0">
                                <div class="bg-blue-100 rounded p-2 text-primary">
                                    <i class="fas fa-info-circle"></i>
                                </div>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1">Kenaikan Harga Beras</h6>
                                <p class="text-muted text-sm mb-0">Memberikan dampak signifikan terhadap garis kemiskinan pada
                                    periode Maret 2024.</p>
                            </div>
                        </div>
                        <div class="d-flex gap-3">
                            <div class="flex-shrink-0">
                                <div class="bg-green-100 rounded p-2 text-success">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1">Panen Raya Hortikultura</h6>
                                <p class="text-muted text-sm mb-0">Meningkatkan pendapatan petani di wilayah Kabupaten Sambas
                                    dan sekitarnya.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Commodity Stats -->
                <div class="col-lg-6">
                    <div class="stats-card">
                        <h5 class="fw-bold mb-3">5 Komoditas Teratas (Kontribusi GK)</h5>
                        <div class="table-responsive">
                            <table class="table table-borderless table-sm mb-0">
                                <thead class="text-muted text-uppercase text-xs">
                                    <tr>
                                        <th>Komoditas</th>
                                        <th class="text-end">Kontribusi (%)</th>
                                        <th class="text-end">Harga Rata-rata</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="fw-medium">Beras</td>
                                        <td class="text-end text-navy fw-bold">18.4%</td>
                                        <td class="text-end small">Rp 14.500</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-medium">Rokok Kretek</td>
                                        <td class="text-end text-navy fw-bold">11.2%</td>
                                        <td class="text-end small">Rp 23.000</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-medium">Daging Ayam Ras</td>
                                        <td class="text-end text-navy fw-bold">4.8%</td>
                                        <td class="text-end small">Rp 38.000</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-medium">Telur Ayam Ras</td>
                                        <td class="text-end text-navy fw-bold">3.5%</td>
                                        <td class="text-end small">Rp 28.000</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-medium">Gula Pasir</td>
                                        <td class="text-end text-navy fw-bold">2.1%</td>
                                        <td class="text-end small">Rp 16.500</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@push('scripts')
    <script>
        // Poverty Trend Chart
        const ctx = document.getElementById('povertyTrendChart').getContext('2d');

        // Gradient for line chart
        const gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(26, 54, 93, 0.2)');
        gradient.addColorStop(1, 'rgba(26, 54, 93, 0.0)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['2019', '2020', '2021', '2022', '2023', '2024'],
                datasets: [{
                    label: 'Persentase Penduduk Miskin (%)',
                    data: [7.2, 7.15, 7.3, 6.9, 6.71, 6.55], // Dummy data
                    borderColor: '#1a365d',
                    backgroundColor: gradient,
                    borderWidth: 3,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#1a365d',
                    pointBorderWidth: 2,
                    pointRadius: 6,
                    pointHoverRadius: 8,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(255, 255, 255, 0.9)',
                        titleColor: '#1e293b',
                        bodyColor: '#1e293b',
                        borderColor: '#e2e8f0',
                        borderWidth: 1,
                        padding: 12,
                        displayColors: false,
                        callbacks: {
                            label: function (context) {
                                return context.parsed.y + '%';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: false,
                        grid: {
                            borderDash: [5, 5],
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index',
                },
            }
        });

        // Region Pie Chart
        const ctxPie = document.getElementById('regionPieChart').getContext('2d');
        new Chart(ctxPie, {
            type: 'doughnut',
            data: {
                labels: ['Perkotaan', 'Perdesaan'],
                datasets: [{
                    data: [42, 58],
                    backgroundColor: [
                        '#1a365d', // Navy
                        '#0d9488'  // Teal
                    ],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '75%',
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    </script>
@endpush