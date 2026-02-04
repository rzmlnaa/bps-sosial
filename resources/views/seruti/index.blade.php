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
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="fw-bold mb-0 text-dark" id="chartTitle">Judul Grafik</h5>
                        <!-- Legend Info or Tooltip hint -->
                    </div>
                    <div id="mainChart"></div>
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
                console.log('Kabupaten changed:', val);
                if (val !== "") {
                    filterCoicop.value = ""; // Clear COICOP
                }
                fetchData();
            });

            filterCoicop.addEventListener('change', () => {
                const val = filterCoicop.value;
                console.log('COICOP changed:', val);
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
                console.log('UI State Change:', state, message ? '(' + message + ')' : '');

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

                fetch(`{{ route('seruti.chart-data') }}?${params.toString()}`)
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
                //const colors = data.series.map(s => twColorByName(s.name));

                console.log('Render chart with:', data);

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
                        categories: data.categories,
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
            }

            console.log('Chart width:', document.querySelector('#mainChart').offsetWidth);


        });
    </script>
    <style>
        .ls-1 {
            letter-spacing: 1px;
        }

        .max-w-md {
            max-width: 400px;
        }

        .form-select-sm,
        .form-control-sm {
            border-radius: 6px;
            font-size: 0.85rem;
        }
    </style>
@endpush