@extends('layouts.admin')

@section('title', 'Data Seruti')

@section('content')
    <div class="fade-in-up">
        <!-- Header -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
            <div>
                <h2 class="fw-bold mb-1" style="color: var(--bps-orange);">SERUTI</h2>
                <p class="text-muted mb-0">Survei Ekonomi Rumah Tangga Triwulanan</p>
            </div>
            @if (auth()->check())
                <div class="mt-3 mt-md-0">
                    @if (auth()->user()->can('access-admin') || (auth()->user()->status == 'active' && auth()->user()->kabupaten->kode_kab == '6100'))
                        <a href="{{ route('seruti.create') }}" class="btn btn-success text-white shadow-sm"
                            style="border: none; border-radius: 8px;">
                            <i class="fas fa-plus me-2"></i>Input Data
                        </a>
                    @endif
                </div>
            @endif
        </div>

        <!-- Filters Section -->
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; background: #fff;">
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-12 mb-2">
                        <label class="small text-uppercase fw-bold text-muted ls-1">Filter Data</label>
                        <p class="small text-muted mb-0">Pilih <span class="fw-bold text-dark">Kabupaten</span> untuk
                            melihat rincian Sub Kelompok,
                            atau pilih <span class="fw-bold text-dark">Sub Kelompok</span> untuk membandingkan antar
                            Kabupaten.</p>
                    </div>

                    <!-- Year -->
                    <div class="col-md-2">
                        <label class="form-label small fw-bold">Tahun</label>
                        <select id="filterYear" class="form-select form-select-sm">
                            <option value="" disabled selected>Pilih Tahun</option>
                            <option value="all">Semua Tahun</option>
                            @foreach($years as $y)
                                <option value="{{ $y }}">{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Quarter (Multi) -->
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Triwulan</label>
                        <div class="d-flex gap-2 align-items-center mt-1">
                            <div class="form-check">
                                <input class="form-check-input filter-quarter" type="checkbox" value="1" id="q1" checked>
                                <label class="form-check-label small" for="q1">Q1</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input filter-quarter" type="checkbox" value="2" id="q2" checked>
                                <label class="form-check-label small" for="q2">Q2</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input filter-quarter" type="checkbox" value="3" id="q3" checked>
                                <label class="form-check-label small" for="q3">Q3</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input filter-quarter" type="checkbox" value="4" id="q4" checked>
                                <label class="form-check-label small" for="q4">Q4</label>
                            </div>
                        </div>
                    </div>

                    <!-- Regency -->
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Kabupaten/Kota</label>
                        <select id="filterKabupaten" class="form-select form-select-sm">
                            <option value="">-- Semua / Pilih Salah Satu --</option>
                            @foreach($kabupatens as $kab)
                                <option value="{{ $kab->id }}">[{{ $kab->kode_kab }}] {{ $kab->nama_kabupaten }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Sub Group -->
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Sub Kelompok (COICOP)</label>
                        <select id="filterCoicop" class="form-select form-select-sm">
                            <option value="">-- Semua / Pilih Salah Satu --</option>
                            @foreach($coicops as $c)
                                <option value="{{ $c->id }}">[{{ $c->kode }}] {{ Str::limit($c->nama, 30) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-1 d-flex align-items-end">
                        <button id="resetBtn" class="btn btn-sm btn-outline-secondary w-100">Reset</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chart Section -->
        <div class="card border-0 shadow-sm" style="border-radius: 12px; min-height: 450px;">
            <div class="card-body p-4 position-relative">

                <!-- Loading Overlay -->
                <div id="loadingOverlay"
                    class="position-absolute top-0 start-0 w-100 h-100 bg-white d-flex flex-column justify-content-center align-items-center"
                    style="z-index: 10; border-radius: 12px; display: none;">
                    <div class="spinner-border text-primary mb-3" role="status"></div>
                    <span class="text-muted small fw-bold">Memuat Data...</span>
                </div>

                <!-- Empty State -->
                <div id="emptyState"
                    class="h-100 d-flex flex-column justify-content-center align-items-center text-center py-5">
                    <div class="bg-light rounded-circle p-4 mb-3">
                        <i class="fas fa-chart-pie fa-3x text-secondary opacity-50"></i>
                    </div>
                    <h5 class="fw-bold text-dark">Data Visualisasi</h5>
                    <p class="text-muted mb-0 max-w-md">Silakan pilih filter (Tahun & Wilayah/Kelompok) di atas untuk
                        menampilkan grafik.</p>
                </div>

                <!-- Chart Container -->
                <div id="chartContainer" style="display: none;">
                    <!-- Chart Section (Full Width) -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="fw-bold mb-0 text-dark" id="chartTitle">Judul Grafik</h5>
                            </div>
                            <div id="mainChart"></div>
                        </div>
                    </div>

                    <!-- Anomaly Insight Section (Full Width Below Chart) -->
                    <div class="card border-0 bg-light shadow-sm" style="border-radius: 12px;">
                        <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="fw-bold text-dark mb-0">
                                    <i class="fas fa-microscope me-2 text-primary"></i> Analisis Anomali (>25%)
                                </h5>
                                <span class="badge bg-primary rounded-pill px-3 py-2" id="anomalyCount"
                                    style="font-size: 0.85rem;">0</span>
                            </div>
                            <p class="text-muted small mt-2 mb-0">Perbandingan data terhadap rata-rata provinsi pada periode
                                yang sama.</p>
                        </div>
                        <div class="card-body p-4">
                            <div id="anomalyList" class="row g-3">
                                <!-- JS will populate this with responsive grid cards -->
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <!-- ApexCharts CDN -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {

            // Elements
            const filterYear = document.getElementById('filterYear');
            const filterQuarters = document.querySelectorAll('.filter-quarter');
            const filterKabupaten = document.getElementById('filterKabupaten');
            const filterCoicop = document.getElementById('filterCoicop');
            const resetBtn = document.getElementById('resetBtn');
            const loadingOverlay = document.getElementById('loadingOverlay');
            const emptyState = document.getElementById('emptyState');
            const chartContainer = document.getElementById('chartContainer');
            const chartTitle = document.getElementById('chartTitle');

            let chart = null;

            function generateTWColors(series) {
                return series.map((s, i) => {
                    const hue = Math.round((360 / series.length) * i);
                    return `hsl(${hue}, 70%, 50%)`;
                });
            }

            function twColorByName(name) {
                if (name.includes('Tw 1')) return '#dc3545';
                if (name.includes('Tw 2')) return '#198754';
                if (name.includes('Tw 3')) return '#0d6efd';
                if (name.includes('Tw 4')) return '#6f42c1';
                return '#adb5bd';
            }


            function initChart() {
                chart = new ApexCharts(
                    document.querySelector("#mainChart"),
                    {
                        chart: {
                            type: 'bar',
                            height: 500,
                            fontFamily: 'inherit',
                            toolbar: {
                                show: true,
                                tools: {
                                    download: true,
                                    selection: false,
                                    zoom: true,
                                    zoomin: false,
                                    zoomout: false,
                                    pan: false,
                                    reset: false
                                }
                            },
                            animations: { enabled: true }
                        },
                        series: [],
                        xaxis: {
                            categories: []
                        },
                        plotOptions: {
                            bar: {
                                horizontal: false,
                                columnWidth: '70%',
                                borderRadius: 4
                            }
                        },
                        dataLabels: { enabled: false },
                        stroke: { show: true, width: 2, colors: ['transparent'] },
                        yaxis: {
                            title: { text: 'Rupiah' },
                            labels: {
                                formatter: (val) =>
                                    val >= 1000000
                                        ? (val / 1000000).toFixed(1) + ' jt'
                                        : val >= 1000
                                            ? (val / 1000).toFixed(0) + ' rb'
                                            : val
                            }
                        },
                        tooltip: {
                            shared: true,
                            intersect: false,
                            y: {
                                formatter: (val) => "Rp " + val.toLocaleString('id-ID')
                            }
                        },
                        colors: ['#0d6efd', '#fd7e14', '#d63384', '#ffc107', '#ffcd39'],
                        grid: { borderColor: '#f3f4f6' },
                        legend: { position: 'top' }
                    }
                );

                chart.render();
            }


            // Optional UX: Mutually exclusive highlight
            filterKabupaten.addEventListener('change', () => {
                const val = filterKabupaten.value;
                //console.log('Kabupaten changed:', val);
                if (val !== "") {
                    filterCoicop.value = ""; // Clear COICOP
                }
                fetchData();
            });

            filterCoicop.addEventListener('change', () => {
                const val = filterCoicop.value;
                //console.log('COICOP changed:', val);
                if (val !== "") {
                    filterKabupaten.value = ""; // Clear Kabupaten
                }
                fetchData();
            });

            filterYear.addEventListener('change', () => fetchData());
            filterQuarters.forEach(q => q.addEventListener('change', () => fetchData()));

            resetBtn.addEventListener('click', () => {
                if (filterYear.options.length > 1) filterYear.selectedIndex = 1;
                filterKabupaten.value = "";
                filterCoicop.value = "";
                filterQuarters.forEach(q => q.checked = true);
                fetchData();
            });

            // Initial Setup
            if (filterYear.options.length > 1 && !filterYear.value) {
                filterYear.selectedIndex = 1;
            }
            // Auto-select Kabupaten with data if available
            const defaultKabId = @json($defaultKabupatenId ?? null);

            if (filterKabupaten.options.length > 1 && !filterKabupaten.value && !filterCoicop.value) {
                if (defaultKabId && filterKabupaten.querySelector(`option[value="${defaultKabId}"]`)) {
                    filterKabupaten.value = defaultKabId;
                } else {
                    filterKabupaten.selectedIndex = 1;
                }
            }

            initChart();
            fetchData();


            /**
             * Centralized UI State Manager
             * States: 'loading', 'empty', 'chart'
             */
            function updateUIState(state, message = '') {
                //console.log('UI State Change:', state, message ? '(' + message + ')' : '');

                // Hide All Initially
                loadingOverlay.style.setProperty('display', 'none', 'important');
                emptyState.style.setProperty('display', 'none', 'important');
                chartContainer.style.setProperty('display', 'none', 'important');

                if (state === 'loading') {
                    loadingOverlay.style.setProperty('display', 'flex', 'important');
                } else if (state === 'empty') {
                    emptyState.style.setProperty('display', 'flex', 'important');
                    emptyState.querySelector('p').innerText = message;
                } else if (state === 'chart') {
                    chartContainer.style.setProperty('display', 'block', 'important');
                }
            }

            function fetchData() {
                const year = filterYear.value;
                const kabupatenId = filterKabupaten.value;
                const coicopId = filterCoicop.value;
                const quarters = Array.from(filterQuarters).filter(c => c.checked).map(c => c.value);

                if (!year) return;

                updateUIState('loading');

                const params = new URLSearchParams({
                    year: year,
                    quarters: quarters.join(','),
                    kabupaten_id: kabupatenId,
                    coicop_id: coicopId
                });

                fetch(`{{ route('seruti.chart-data') }}` + `?${params.toString()}`)
                    .then(response => {
                        if (!response.ok) throw new Error('HTTP ' + response.status);
                        return response.json();
                    })
                    .then(data => {
                        if (data.empty || !data.series || data.series.length === 0) {
                            updateUIState('empty', data.mode === 'initial' ? "Silakan pilih filter." : "Tidak ada data ditemukan untuk filter ini.");
                        } else {
                            renderChart(data);
                        }
                    })
                    .catch(err => {
                        console.error('Fetch Error:', err);
                        updateUIState('empty', "Terjadi kesalahan: " + err.message);
                    });
            }

            function renderChart(data) {
                updateUIState('chart');
                chartTitle.innerText = data.title;
                const colors = generateTWColors(data.series);

                //console.log('Render chart with:', data.categories);
                const formattedCategories = data.categories.map(item => {
                    const match = item.match(/^\[(\d+)\]\s*(\S+)/);
                    return match ? `[${match[1]}] ${match[2]}` : item;
                });
                chart.updateOptions({
                    colors: colors,
                    plotOptions: {
                        bar: {
                            horizontal: false,
                            columnWidth: '65%',
                            borderRadius: 4
                        }
                    },
                    xaxis: {
                        categories: formattedCategories,
                        labels: {
                            rotate: -45,
                            style: { fontSize: '11px' }
                        }
                    },
                    legend: {
                        position: 'top'
                    }
                }, false, true);

                chart.updateSeries(data.series, true);

                // Populate Anomalies
                const anomalyList = document.getElementById('anomalyList');
                const anomalyCount = document.getElementById('anomalyCount');
                anomalyList.innerHTML = '';

                console.log(data.anomalies);


                if (!data.anomalies || data.anomalies.length === 0) {
                    if (anomalyCount) anomalyCount.innerText = '0';
                    anomalyList.innerHTML = `
                                                                                <div class="col-12 text-center py-5">
                                                                                    <i class="fas fa-check-circle fa-3x text-success opacity-50 mb-3"></i>
                                                                                    <h6 class="fw-bold text-dark">Data Normal</h6>
                                                                                    <p class="text-muted small mb-0">Tidak ditemukan anomali signifikan (>25%) pada dataset ini.</p>
                                                                                </div>
                                                                            `;
                } else {
                    if (anomalyCount) anomalyCount.innerText = data.anomalies.length;
                    data.anomalies.forEach(ano => {
                        const isHigh = ano.type === 'Tinggi';
                        const badgeClass = isHigh ? 'anomaly-badge-high' : 'anomaly-badge-low';
                        const iconClass = isHigh ? 'fa-arrow-up' : 'fa-arrow-down';
                        const borderClass = isHigh ? 'border-danger' : 'border-warning';

                        const col = document.createElement('div');
                        col.className = 'col-md-6 col-xl-4';
                        col.innerHTML = `
                                                                                    <div class="card h-100 border-0 shadow-sm anomaly-card border-start border-4 ${borderClass}">
                                                                                        <div class="card-body p-3">
                                                                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                                                                <span class="${badgeClass}">
                                                                                                    <i class="fas ${iconClass} me-1"></i> ${ano.type} (${ano.deviation}%)
                                                                                                </span>
                                                                                                <span class="text-muted fw-bold" style="font-size: 0.75rem;">${ano.period}</span>
                                                                                            </div>

                                                                                            <h6 class="fw-bold text-dark mb-1 text-truncate" title="${ano.item}">[${ano.code}] ${ano.item}</h6>
                                                                                            <p class="text-muted mb-3" style="font-size: 0.75rem;">
                                                                                                <i class="fas fa-map-marker-alt me-1"></i> ${ano.location}
                                                                                            </p>

                                                                                            <div class="d-flex flex-column bg-light rounded p-2">
                                                                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                                                                    <span class="text-muted extra-small">Nilai Aktual:</span>
                                                                                                    <span class="fw-bold text-dark">Rp ${ano.value.toLocaleString('id-ID')}</span>
                                                                                                </div>
                                                                                                <div class="d-flex justify-content-between align-items-center">
                                                                                                    <span class="text-muted extra-small">Prov. Avg:</span>
                                                                                                    <span class="text-secondary small">Rp ${ano.reference.toLocaleString('id-ID')}</span>
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                `;
                        anomalyList.appendChild(col);
                    });
                }
            }

            //console.log('Chart width:', document.querySelector('#mainChart').offsetWidth);


        });
    </script>
    <style>
        .ls-1 {
            letter-spacing: 1px;
        }

        .max-w-md {
            max-width: 400px;
        }

        .bg-danger-subtle {
            background-color: #fee2e2 !important;
        }

        .bg-warning-subtle {
            background-color: #fef3c7 !important;
        }

        .anomaly-badge-high {
            background-color: #fee2e2;
            color: #b91c1c;
            padding: 4px 10px;
            border-radius: 6px;
            font-weight: 700;
            font-size: 0.75rem;
            display: inline-block;
        }

        .anomaly-badge-low {
            background-color: #fef3c7;
            color: #92400e;
            padding: 4px 10px;
            border-radius: 6px;
            font-weight: 700;
            font-size: 0.75rem;
            display: inline-block;
        }

        .anomaly-card {
            transition: transform 0.2s, box-shadow 0.2s;
            border-radius: 10px !important;
        }

        .anomaly-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1) !important;
        }

        .extra-small {
            font-size: 0.65rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
    </style>
@endpush