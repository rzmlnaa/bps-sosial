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
            <form action="{{ route('poverty') }}" method="GET" class="mt-3 mt-md-0 d-flex gap-2">

                <select name="tahun" class="form-select border-0 shadow-sm" style="min-width: 150px;">
                    <option value="all" {{ request('tahun') == 'all' ? 'selected' : '' }}>
                        Semua Tahun
                    </option>

                    @for ($year = 2022; $year <= now()->year; $year++)
                        <option value="{{ $year }}" {{ request('tahun') == $year ? 'selected' : '' }}>
                            Tahun {{ $year }}
                        </option>
                    @endfor
                </select>

                <button type="submit" class="btn btn-primary text-white"
                    style="background-color: var(--bps-blue); border: none;">
                    <i class="fas fa-filter"></i> Filter
                </button>

                <a href="{{ route('poverty.input') }}" class="btn btn-success text-white"
                    style="background-color: var(--bps-green); border: none;">
                    <i class="fas fa-plus"></i> Input Data
                </a>

            </form>

        </div>



        <!-- Interactive Map/Detailed Stats Grid -->
        <div class="row g-4 mb-4">
            <!-- Highlights -->
            <!-- <div class="col-lg-4">
                                                <div class="stats-card h-100 bg-orange-faded border-0">
                                                    <h5 class="fw-bold text-dark mb-3">Provinsi Kalimantan Barat</h5>
                                                    <div class="d-flex align-items-end mb-2">
                                                        <h1 class="fw-bold mb-0 text-orange" style="font-size: 2.5rem;">
                                                            Rp {{ number_format($provAvg, 0, ',', '.') }}
                                                        </h1>
                                                        <span class="mb-2 ms-2 fw-medium text-muted">{{ $latestLabel }}</span>
                                                    </div>
                                                    <p class="text-muted small">Rata-rata nilai (Rp) dari seluruh Kabupaten/Kota di Kalimantan Barat.</p>
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
                                            </div> -->

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
                            <div class="d-flex align-items-center gap-2">
                                <div class="btn-group p-1 bg-light" role="group" style="border-radius: 10px;">
                                    <button type="button" class="btn btn-sm border-0 chart-type-btn active" data-type="line"
                                        title="Grafik Garis" style="border-radius: 8px; transition: all 0.2s;">
                                        <i class="fas fa-chart-line"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm border-0 chart-type-btn" data-type="bar"
                                        title="Grafik Balok" style="border-radius: 8px; transition: all 0.2s;">
                                        <i class="fas fa-chart-bar"></i>
                                    </button>
                                </div>
                                <select class="form-select w-auto border-0 bg-light fw-bold" id="regencySelectorChart"
                                    style="border-radius: 8px;">
                                    <option value="all" class="text-primary font-bold" selected>Semua Wilayah</option>
                                    @foreach($kabupatens as $kab)
                                        <option value="{{ $kab->id }}">{{ $kab->nama_kabupaten }}</option>
                                    @endforeach
                                </select>
                            </div>
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
                <h5 class="fw-bold mb-0">Data Rinci Menurut Wilayah
                    ({{ request('tahun', 'all') == 'all' ? 'Semua Tahun' : request('tahun') }})</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 border-0">Wilayah</th>
                            <!-- <th class="text-center border-0">Rata-rata (Rp)</th> -->
                            <th class="text-center border-0">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">

                        @forelse($kabupatenData as $region)
                            <tr>
                                <td class="ps-4 fw-medium">{{ $region['name'] }}</td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-primary border-0 btn-detail-region"
                                        data-id="{{ $region['id'] }}" data-name="{{ $region['name'] }}">
                                        <i class="fas fa-eye"></i> Detail
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="text-center text-muted py-4">
                                    <i class="fas fa-database me-2"></i>
                                    Belum ada data pada tahun
                                    <strong>
                                        {{ request('tahun', 'all') == 'all' ? 'Semua Tahun' : request('tahun') }}
                                    </strong>
                                </td>
                            </tr>
                        @endforelse
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


        // --- 2. Interactive Line Chart (Dynamic) ---
        const ctxLine = document.getElementById('povertyLineChart').getContext('2d');
        const chartCanvas = document.getElementById('povertyLineChart');
        const chartPlaceholder = document.getElementById('chartPlaceholder');
        const chartLoading = document.getElementById('chartLoading');
        let povertyLineChart;
        let currentChartType = 'line';


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

            fetch(`/poverty-data/get-data/${kabId}?tahun={{ request('tahun', 'all') }}`)
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
                        type: currentChartType,
                        data: {
                            labels: persentils.map(p => `P${p}`),
                            datasets: datasets.map(ds => {
                                if (currentChartType === 'bar') {
                                    return {
                                        ...ds,
                                        backgroundColor: ds.borderColor,
                                        borderWidth: 1
                                    };
                                }
                                return ds;
                            })
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

        // Handle Chart Type Switch
        document.querySelectorAll('.chart-type-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                document.querySelectorAll('.chart-type-btn').forEach(b => b.classList.remove('active', 'btn-primary'));
                this.classList.add('active', 'btn-primary');
                currentChartType = this.dataset.type;

                // Re-trigger regency selector to refresh chart
                document.getElementById('regencySelectorChart').dispatchEvent(new Event('change'));
            });
        });

        // Trigger chart load for "Semua Wilayah" on page load
        document.addEventListener('DOMContentLoaded', function () {
            const selector = document.getElementById('regencySelectorChart');
            if (selector) {
                selector.dispatchEvent(new Event('change'));
            }
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

                fetch(`/poverty-data/get-data/${kabId}?tahun={{ request('tahun', 'all') }}`)
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

@push('styles')
    <style>
        .chart-type-btn.active {
            background-color: var(--bps-blue) !important;
            color: white !important;
        }

        .chart-type-btn:hover:not(.active) {
            background-color: #f1f5f9;
        }
    </style>
@endpush