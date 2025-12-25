@extends('layouts.admin')

@section('title', 'Data Kemiskinan - BPS Kalbar')

@section('content')
    <div class="fade-in-up">
        <!-- Header -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
            <div>
                <h2 class="fw-bold mb-1" style="color: var(--bps-orange);">Data Kemiskinan Wilayah</h2>
                <p class="text-muted mb-0">Perbandingan tingkat kemiskinan Kalimantan Barat Menurut Kabupaten/Kota</p>
            </div>
            <div class="mt-3 mt-md-0 d-flex gap-2">
                <select class="form-select border-0 shadow-sm" style="min-width: 150px;">
                    <option value="2024" selected>Tahun 2024</option>
                    <option value="2023">Tahun 2023</option>
                    <option value="2022">Tahun 2022</option>
                    <option value="2021">Tahun 2021</option>
                    <option value="2020">Tahun 2020</option>
                </select>
                <button class="btn btn-primary text-white" style="background-color: var(--bps-blue); border: none;">
                    <i class="fas fa-filter"></i> Filter
                </button>
                <a href="{{ route('poverty.input') }}" class="btn btn-success text-white"
                    style="background-color: var(--bps-green); border: none;">
                    <i class="fas fa-plus"></i> Input Data
                </a>
            </div>
        </div>

        <!-- Main Comparison Chart -->
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-4">Persentase Penduduk Miskin menurut Kabupaten/Kota</h5>
                <div style="height: 400px; position: relative;">
                    <canvas id="povertyComparisonChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Interactive Map/Detailed Stats Grid -->
        <div class="row g-4 mb-4">
            <!-- Highlights -->
            <div class="col-lg-4">
                <div class="stats-card h-100 bg-orange-faded border-0">
                    <h5 class="fw-bold text-dark mb-3">Provinsi Kalimantan Barat</h5>
                    <div class="d-flex align-items-end mb-2">
                        <h1 class="fw-bold mb-0 text-orange" style="font-size: 3.5rem;">6.71%</h1>
                        <span class="mb-2 ms-2 fw-medium text-muted">Maret 2024</span>
                    </div>
                    <p class="text-muted small">Mengalami penurunan sebesar 0.25 persen poin dibandingkan Maret 2023.</p>
                    <hr style="border-color: rgba(0,0,0,0.1);">
                    <div class="d-flex justify-content-between">
                        <div>
                            <small class="text-muted d-block">Garis Kemiskinan</small>
                            <span class="fw-bold">Rp 652.812</span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Penduduk Miskin</small>
                            <span class="fw-bold">354.21 Ribu</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Trend Chart by Regency -->
            <div class="col-lg-12">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h5 class="fw-bold mb-1">Grafik Nilai Kemiskinan per Persentil</h5>
                                <p class="text-muted small mb-0">Sumbu Y: Rupiah | Sumbu X: Persentil | Garis: Variabel &
                                    Tahun</p>
                            </div>
                            <select class="form-select w-auto border-0 bg-light fw-bold" id="regencySelectorChart"
                                style="border-radius: 8px;">
                                <option value="" disabled selected>-- Pilih Wilayah --</option>
                                @foreach($kabupatens as $kab)
                                    <option value="{{ $kab->id }}">{{ $kab->nama_kabupaten }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div style="height: 450px; width: 100%;">
                            <canvas id="povertyLineChart"></canvas>
                        </div>
                        <div id="chartLoading" class="text-center py-5 d-none">
                            <div class="spinner-border text-primary" role="status"></div>
                            <p class="mt-2 text-muted">Memuat data grafik...</p>
                        </div>
                        <div id="chartPlaceholder" class="text-center py-5">
                            <i class="fas fa-chart-line fa-4x text-light mb-3"></i>
                            <h6 class="text-muted">Silakan pilih wilayah untuk menampilkan grafik perbandingan</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Table -->
        <div class="card border-0 shadow-sm" style="border-radius: 12px;">
            <div class="card-header bg-white py-3 border-bottom-0">
                <h5 class="fw-bold mb-0">Data Rinci Menurut Wilayah (2024)</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 border-0">Wilayah</th>
                            <th class="text-center border-0">Persentase (%)</th>
                            <th class="text-center border-0">Jumlah (Ribu Jiwa)</th>
                            <th class="text-center border-0">Garis Kemiskinan (Rp)</th>
                            <th class="text-center border-0">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        @php
                            $regions = [
                                ['name' => 'Kab. Sambas', 'percent' => 7.55, 'count' => 45.2, 'gk' => 550000],
                                ['name' => 'Kab. Mempawah', 'percent' => 5.80, 'count' => 15.3, 'gk' => 560000],
                                ['name' => 'Kab. Sanggau', 'percent' => 4.20, 'count' => 18.1, 'gk' => 570000],
                                ['name' => 'Kab. Ketapang', 'percent' => 9.10, 'count' => 48.5, 'gk' => 540000],
                                ['name' => 'Kab. Sintang', 'percent' => 8.80, 'count' => 38.2, 'gk' => 590000],
                                ['name' => 'Kab. Kapuas Hulu', 'percent' => 6.20, 'count' => 16.5, 'gk' => 580000],
                                ['name' => 'Kab. Bengkayang', 'percent' => 6.90, 'count' => 19.8, 'gk' => 530000],
                                ['name' => 'Kab. Landak', 'percent' => 10.5, 'count' => 42.1, 'gk' => 520000],
                                ['name' => 'Kab. Sekadau', 'percent' => 5.90, 'count' => 12.4, 'gk' => 550000],
                                ['name' => 'Kab. Melawi', 'percent' => 11.2, 'count' => 25.6, 'gk' => 560000],
                                ['name' => 'Kab. Kayong Utara', 'percent' => 9.50, 'count' => 11.2, 'gk' => 545000],
                                ['name' => 'Kab. Kubu Raya', 'percent' => 4.50, 'count' => 28.5, 'gk' => 585000],
                                ['name' => 'Kota Pontianak', 'percent' => 4.25, 'count' => 29.1, 'gk' => 650000],
                                ['name' => 'Kota Singkawang', 'percent' => 4.80, 'count' => 11.5, 'gk' => 610000],
                            ];
                        @endphp
                        @foreach($regions as $region)
                            <tr>
                                <td class="ps-4 fw-medium">{{ $region['name'] }}</td>
                                <td class="text-center">
                                    <span
                                        class="badge bg-green-faded text-green rounded-pill px-3">{{ $region['percent'] }}%</span>
                                </td>
                                <td class="text-center">{{ $region['count'] }}</td>
                                <td class="text-center">Rp {{ number_format($region['gk'], 0, ',', '.') }}</td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-link text-muted"><i class="fas fa-eye"></i> Detail</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // --- 1. Main Comparison Bar Chart ---
        const ctxBar = document.getElementById('povertyComparisonChart').getContext('2d');

        // Data Setup
        const regionLabels = ['Sambas', 'Mempawah', 'Sanggau', 'Ketapang', 'Sintang', 'Kapuas Hulu', 'Bengkayang', 'Landak', 'Sekadau', 'Melawi', 'Kayong Utara', 'Kubu Raya', 'Pontianak', 'Singkawang', 'PROVINSI'];
        const povertyData = [7.55, 5.80, 4.20, 9.10, 8.80, 6.20, 6.90, 10.5, 5.90, 11.2, 9.50, 4.50, 4.25, 4.80, 6.71];

        // Create colors array (Orange for higher poverty, Blue for lower/average, Green for Prov)
        const barColors = povertyData.map((val, i) => {
            if (i === 14) return '#7ab800'; // Green for Province
            if (val > 8) return '#f58220'; // Orange for high poverty
            return '#0093dd'; // Blue for others
        });

        new Chart(ctxBar, {
            type: 'bar',
            data: {
                labels: regionLabels,
                datasets: [{
                    label: 'Persentase Penduduk Miskin (%)',
                    data: povertyData,
                    backgroundColor: barColors,
                    borderRadius: 4,
                    barThickness: 'flex',
                    maxBarThickness: 35
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(255, 255, 255, 0.95)',
                        titleColor: '#1e293b',
                        bodyColor: '#1e293b',
                        borderColor: '#e2e8f0',
                        borderWidth: 1,
                        callbacks: {
                            label: function (context) { return context.parsed.y + '%'; }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(0,0,0,0.05)' }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { autoSkip: false, maxRotation: 45, minRotation: 45 }
                    }
                }
            }
        });

        // --- 2. Interactive Line Chart (Dynamic) ---
        const ctxLine = document.getElementById('povertyLineChart').getContext('2d');
        const chartCanvas = document.getElementById('povertyLineChart');
        const chartPlaceholder = document.getElementById('chartPlaceholder');
        const chartLoading = document.getElementById('chartLoading');
        let povertyLineChart;

        // Colors for line chart
        const colorPalette = [
            '#f58220', // Orange
            '#0093dd', // Blue
            '#7ab800', // Green
            '#6366f1', // Indigo
            '#ec4899', // Pink
            '#f43f5e', // Rose
            '#8b5cf6', // Violet
            '#06b6d4', // Cyan
        ];

        // Hide chart initially
        chartCanvas.style.display = 'none';

        document.getElementById('regencySelectorChart').addEventListener('change', function (e) {
            const kabId = e.target.value;
            
            // UI State
            chartPlaceholder.classList.add('d-none');
            chartLoading.classList.remove('d-none');
            chartCanvas.style.display = 'none';

            fetch(`/poverty-data/get-data/${kabId}`)
                .then(response => response.json())
                .then(result => {
                    const { data, variabels } = result;
                    
                    chartLoading.classList.add('d-none');
                    chartCanvas.style.display = 'block';

                    const persentils = Object.keys(data).sort((a, b) => a - b);
                    
                    const datasets = variabels.map((v, index) => {
                        const lineData = persentils.map(p => {
                            const record = data[p].find(r => r.variabel_kemiskinan_id == v.id);
                            return record ? record.nilai : null;
                        });

                        return {
                            label: `${v.nama_variabel} ${v.tahun}`,
                            data: lineData,
                            borderColor: colorPalette[index % colorPalette.length],
                            backgroundColor: 'transparent',
                            tension: 0.3,
                            borderWidth: 3,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            spanGaps: true // In case some percentiles are missing
                        };
                    });

                    if (povertyLineChart) {
                        povertyLineChart.destroy();
                    }

                    povertyLineChart = new Chart(ctxLine, {
                        type: 'line',
                        data: {
                            labels: persentils.map(p => `P${p}`),
                            datasets: datasets
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        usePointStyle: true,
                                        padding: 20,
                                        font: { size: 12, weight: 'bold' }
                                    }
                                },
                                tooltip: {
                                    mode: 'index',
                                    intersect: false,
                                    callbacks: {
                                        label: function(context) {
                                            let label = context.dataset.label || '';
                                            if (label) { label += ': '; }
                                            if (context.parsed.y !== null) {
                                                label += new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(context.parsed.y);
                                            }
                                            return label;
                                        }
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: false,
                                    grid: { borderDash: [5, 5], color: 'rgba(0,0,0,0.05)' },
                                    ticks: {
                                        callback: function(value) {
                                            return 'Rp ' + value.toLocaleString('id-ID');
                                        }
                                    },
                                    title: { display: true, text: 'Nilai (Rupiah)', font: { weight: 'bold' } }
                                },
                                x: {
                                    grid: { display: false },
                                    title: { display: true, text: 'Persentil', font: { weight: 'bold' } }
                                }
                            }
                        }
                    });
                });
        });
    </script>
@endpush