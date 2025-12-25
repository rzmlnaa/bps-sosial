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
                        <h1 class="fw-bold mb-0 text-orange" style="font-size: 3.5rem;">
                            {{ number_format($provPercent, 2, ',', '.') }}%
                        </h1>
                        <span class="mb-2 ms-2 fw-medium text-muted">{{ $latestLabel }}</span>
                    </div>
                    <p class="text-muted small">Angka rata-rata gabungan dari seluruh Kabupaten/Kota di Kalimantan Barat.
                    </p>
                    <hr style="border-color: rgba(0,0,0,0.1);">
                    <div class="d-flex justify-content-between">
                        <div>
                            <small class="text-muted d-block">Garis Kemiskinan</small>
                            <span class="fw-bold">Rp {{ number_format($provGK, 0, ',', '.') }}</span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Penduduk Miskin</small>
                            <span class="fw-bold">{{ number_format($provCount, 2, ',', '.') }} Ribu</span>
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
                                <option value="all" class="text-primary font-bold">Semua Wilayah</option>
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
                            $povertyTableData = $kabupatenData;
                        @endphp
                        @foreach($povertyTableData as $region)
                            <tr>
                                <td class="ps-4 fw-medium">{{ $region['name'] }}</td>
                                <td class="text-center">
                                    <span
                                        class="badge bg-green-faded text-green rounded-pill px-3">{{ $region['percent'] }}%</span>
                                </td>
                                <td class="text-center">{{ number_format($region['count'], 2, ',', '.') }}</td>
                                <td class="text-center">Rp {{ number_format($region['gk'], 0, ',', '.') }}</td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-primary border-0 btn-detail-region"
                                        data-id="{{ $region['id'] }}" data-name="{{ $region['name'] }}">
                                        <i class="fas fa-eye"></i> Detail
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Detail Modal -->
    <div class="modal fade" id="modalDetailRegion" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
                <div class="modal-header border-0 pb-0">
                    <div>
                        <h4 class="fw-bold mb-0" id="detailModalTitle">Detail Kemiskinan</h4>
                        <p class="text-muted small mb-0">Analisis data per persentil berdasarkan variabel yang tersedia</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-4">
                        <div class="col-lg-7">
                            <div class="card border-0 bg-light-faded h-100" style="border-radius: 12px;">
                                <div class="card-body p-3">
                                    <h6 class="fw-bold mb-3"><i class="fas fa-chart-line me-2 text-primary"></i>Grafik
                                        Perbandingan</h6>
                                    <div style="height: 350px;">
                                        <canvas id="modalPovertyChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-5">
                            <div class="card border-0 bg-light-faded h-100" style="border-radius: 12px;">
                                <div class="card-body p-3">
                                    <h6 class="fw-bold mb-3"><i class="fas fa-table me-2 text-primary"></i>Data Tabel</h6>
                                    <div class="table-responsive" style="max-height: 350px;">
                                        <table class="table table-sm table-hover text-center" id="modalDetailTable"
                                            style="font-size: 0.8rem;">
                                            <thead class="bg-white sticky-top">
                                                <tr id="modalTableHeader">
                                                    <th>P</th>
                                                </tr>
                                            </thead>
                                            <tbody id="modalTableBody">
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
        // --- 1. Main Comparison Bar Chart ---
        const ctxBar = document.getElementById('povertyComparisonChart').getContext('2d');

        // Data Setup from PHP
        const kabData = @json($kabupatenData);
        const regionLabels = kabData.map(d => d.name);
        if (regionLabels.length === 0) regionLabels.push('Belum ada data');

        const povertyData = kabData.map(d => d.percent);
        if (povertyData.length === 0) povertyData.push(0);

        const barColors = povertyData.map((val) => {
            if (val > 8) return '#f58220';
            return '#0093dd';
        });

        new Chart(ctxBar, {
            type: 'bar',
            data: {
                labels: regionLabels,
                datasets: [{
                    label: '{{ $varPercentage->nama_variabel ?? "Persentase" }} (%)',
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
                    const { data, variabels, kabupatens } = result;

                    chartLoading.classList.add('d-none');
                    chartCanvas.style.display = 'block';

                    const persentils = Object.keys(data).sort((a, b) => a - b);
                    let datasets = [];

                    if (kabId === 'all') {
                        // For All Regions, create a line for EACH (Kabupaten, Variabel) pair
                        kabupatens.forEach((kab, kIndex) => {
                            variabels.forEach((v, vIndex) => {
                                const lineData = persentils.map(p => {
                                    const record = data[p].find(r =>
                                        r.variabel_kemiskinan_id == v.id &&
                                        r.kabupaten_id == kab.id
                                    );
                                    return record ? record.nilai : null;
                                });

                                // Check if this line has any data points before adding
                                if (lineData.some(val => val !== null)) {
                                    datasets.push({
                                        label: `${kab.nama_kabupaten} - ${v.nama_variabel} ${v.tahun}`,
                                        data: lineData,
                                        borderColor: colorPalette[datasets.length % colorPalette.length],
                                        backgroundColor: 'transparent',
                                        tension: 0.3,
                                        borderWidth: 2,
                                        pointRadius: 2,
                                        pointHoverRadius: 4,
                                        spanGaps: true
                                    });
                                }
                            });
                        });
                    } else {
                        // Original logic for single region
                        datasets = variabels.map((v, index) => {
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
                                spanGaps: true
                            };
                        });
                    }

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
                                        label: function (context) {
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
                                        callback: function (value) {
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

        // --- 3. Detail Modal Functionality ---
        const modalDetail = new bootstrap.Modal(document.getElementById('modalDetailRegion'));
        const modalChartCtx = document.getElementById('modalPovertyChart').getContext('2d');
        let modalChart;

        document.querySelectorAll('.btn-detail-region').forEach(button => {
            button.addEventListener('click', function () {
                const kabId = this.dataset.id;
                const kabName = this.dataset.name;

                document.getElementById('detailModalTitle').innerText = `Detail Kemiskinan: ${kabName}`;

                // Clear previous data
                document.getElementById('modalTableBody').innerHTML = '<tr><td colspan="5" class="py-4">Loading...</td></tr>';
                if (modalChart) modalChart.destroy();

                modalDetail.show();

                fetch(`/poverty-data/get-data/${kabId}`)
                    .then(response => response.json())
                    .then(result => {
                        const { data, variabels } = result;
                        const persentils = Object.keys(data).sort((a, b) => a - b);

                        // Update Table Header
                        let header = '<th>P</th>';
                        variabels.forEach(v => {
                            header += `<th title="${v.nama_variabel} ${v.tahun}">${v.nama_variabel.substring(0, 3)} ${v.tahun.toString().substring(2)}</th>`;
                        });
                        document.getElementById('modalTableHeader').innerHTML = header;

                        // Update Table Body
                        let body = '';
                        persentils.forEach(p => {
                            body += `<tr><td class="fw-bold bg-light">${p}</td>`;
                            variabels.forEach(v => {
                                const record = data[p].find(r => r.variabel_kemiskinan_id == v.id);
                                body += `<td>${record ? record.nilai.toLocaleString('id-ID') : '-'}</td>`;
                            });
                            body += '</tr>';
                        });
                        document.getElementById('modalTableBody').innerHTML = body;

                        // Update Chart
                        const datasets = variabels.map((v, index) => {
                            const lineData = persentils.map(p => {
                                const record = data[p].find(r => r.variabel_kemiskinan_id == v.id);
                                return record ? record.nilai : null;
                            });

                            return {
                                label: `${v.nama_variabel} ${v.tahun}`,
                                data: lineData,
                                borderColor: colorPalette[index % colorPalette.length],
                                tension: 0.3,
                                borderWidth: 2,
                                pointRadius: 2
                            };
                        });

                        modalChart = new Chart(modalChartCtx, {
                            type: 'line',
                            data: {
                                labels: persentils.map(p => `P${p}`),
                                datasets: datasets
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 10 } } }
                                },
                                scales: {
                                    y: { ticks: { font: { size: 8 } } },
                                    x: { ticks: { font: { size: 8 } } }
                                }
                            }
                        });
                    });
            });
        });
    </script>
@endpush