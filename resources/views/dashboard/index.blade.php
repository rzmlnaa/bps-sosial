@extends('layouts.admin')

@section('title', 'Dashboard Ringkasan')

@section('content')
    <!-- <style>
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
                                                                                                                                                                                                                                                                                            Dikembangkan oleh <a href="/developer" target="_blank" style="text-decoration: none;">Peserta Magang</a> –
                                                                                                                                                                                                                                                                                            Program
                                                                                                                                                                                                                                                                                            MagangHUB Kemnaker
                                                                                                                                                                                                                                                                                        </p>

                                                                                                                                                                                                                                                                                    </div>
                                                                                                                                                                                                                                                                                </div> -->


    <div class="container-fluid px-4 py-4 fade-in-up">
        <!-- Dashboard Header -->
        <div class="row align-items-center mb-5 ">
            <div class="col-md-8 col-12 mb-3 mb-md-0">
                <h1 class="fw-bold text-navy mb-1" style="color: var(--bps-orange);">Dashboard SISOKA</h1>
                <p class="text-dark lead mb-0">Monitoring dan Analisis Sosial Ekonomi Regional</p>
            </div>
            <div class="col-md-4 col-12 text-md-end">
                <div class="d-inline-flex align-items-center bg-white shadow-sm p-2 rounded-pill px-4">
                    <div class="rounded-circle bg-success me-2"
                        style="width: 10px; height: 10px; animation: pulse 2s infinite;"></div>
                    <span id="realtime-clock" class="small fw-bold text-navy">{{ now()->format('d M Y, H:i:s') }}</span>
                </div>
            </div>
        </div>


        <!-- Welcome Section -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="welcome-card p-4 p-md-5 shadow-sm border-0 position-relative overflow-hidden"
                    style="border-radius: 1.5rem; background: linear-gradient(135deg, #1e293b 0%, #334155 100%);">
                    <div class="position-absolute top-0 end-0 p-4" style="opacity: 0.25;">
                        <i class="fas fa-chart-line fa-10x text-white"></i>
                    </div>
                    <div class="row align-items-center position-relative" style="z-index: 1;">
                        <div class="col-lg-12 text-white">
                            <h2 class="fw-bold mb-1">Selamat Datang di SISOKA</h2>
                            <p class="mb-4 opacity-75 fw-medium">Sistem Informasi Sosial Kalbar</p>

                            <p class="lead mb-4 opacity-75" style="max-width: 850px; line-height: 1.6; font-size: 1.1rem;">
                                SISOKA adalah platform pemantauan indikator sosial ekonomi di Kalimantan Barat yang
                                menyajikan data harga komoditas dan fenomena pembangunan secara real-time untuk mendukung
                                analisis yang lebih akurat.
                            </p>

                            <div class="d-flex align-items-center text-white-50 small">
                                <i class="fas fa-mouse-pointer me-2"></i>
                                <span>Gunakan menu di samping untuk mulai eksplorasi data.</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <!-- Stats Grid (KPIs with Deltas) -->
        <div class="row g-4 mb-5">
            <!-- Perubahan Harga -->
            <div class="col-xl-6 col-md-6">
                <div class="stats-card h-100 border-0 shadow-sm hover-up transition-all"
                    style="border-radius: 1.5rem; background: #ffffff;">
                    <div class="p-4">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                            <div class="rounded-circle bg-warning-faded p-2 text-warning d-flex align-items-center justify-content-center"
                                style="width: 45px; height: 45px;">
                                <i class="fas fa-coins fa-lg"></i>
                            </div>
                            <span class="badge rounded-pill bg-warning-faded text-warning py-1 px-2"
                                style="font-size: 0.7rem;">
                                <i class="fas fa-clock-rotate-left me-1"></i> Aktual
                            </span>
                        </div>
                        <div>
                            <h3 class="fw-bold text-navy mb-1 counter-value"
                                data-target="{{ $stats['price_range_count'] }}">0</h3>
                            <p class="text-muted small mb-1 text-uppercase letter-spacing-1 fw-bold">Perubahan Harga</p>
                            <div class="text-warning fw-bold" style="font-size: 0.7rem;">
                                <i class="fas fa-arrows-left-right me-1"></i> Fluktuasi tinggi minggu ini
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dynamic Menu -->
            <div class="col-xl-6 col-md-6">
                <div class="stats-card h-100 border-0 shadow-sm hover-up transition-all"
                    style="border-radius: 1.5rem; background: #ffffff;">
                    <div class="p-4">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                            <div class="rounded-circle bg-info-faded p-2 text-info d-flex align-items-center justify-content-center"
                                style="width: 45px; height: 45px;">
                                <i class="fas fa-gears fa-lg"></i>
                            </div>
                            <span class="badge rounded-pill bg-info-faded text-info py-1 px-2" style="font-size: 0.7rem;">
                                <i class="fas fa-check-circle me-1"></i> Aktif
                            </span>
                        </div>
                        <div>
                            <h3 class="fw-bold text-navy mb-1 counter-value" data-target="{{ $stats['menu_count'] }}">0</h3>
                            <p class="text-muted small mb-1 text-uppercase letter-spacing-1 fw-bold">Menu Aktif</p>
                            <div class="text-info fw-bold" style="font-size: 0.7rem;">
                                <i class="fas fa-shield-check me-1"></i> Sistem berjalan normal
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <!-- Latest Online Users -->
        <div class="row g-4 mb-5">
            <div class="col-12">
                <div class="card border-0 shadow-sm p-4" style="border-radius: 1.5rem; background: #ffffff;">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <div>
                            <h5 class="fw-bold text-navy mb-0">Aktifitas Pengguna</h5>
                            <p class="text-muted small mb-0">{{ count($latestOnlineUsers) }} Pengguna yang terakhir online
                            </p>
                        </div>
                    </div>

                    <div class="row g-4">
                        @foreach($latestOnlineUsers as $user)
                            @php
                                $names = explode(' ', $user->name);
                                $initials = count($names) >= 2
                                    ? strtoupper(substr($names[0], 0, 1) . substr($names[count($names) - 1], 0, 1))
                                    : strtoupper(substr($names[0], 0, 1));

                                $colorClasses = ['bg-color-1', 'bg-color-2', 'bg-color-3', 'bg-color-4', 'bg-color-5', 'bg-color-6'];
                                $randomColor = $colorClasses[$user->id % count($colorClasses)];
                            @endphp
                            <div class="col-xl-2 col-lg-4 col-md-6">
                                <div class="text-center p-3 h-100 hover-up transition-all rounded-4 border border-light">
                                    <div class="avatar-circle mx-auto {{ $randomColor }} mb-3"
                                        style="width: 55px; height: 55px; font-size: 1.1rem; border: 3px solid #fff;">
                                        {{ $initials }}
                                    </div>
                                    <h6 class="fw-bold text-navy mb-1 text-truncate" title="{{ $user->name }}">
                                        {{ $user->name }}
                                    </h6>
                                    <p class="text-muted text-xs mb-2">{{ $user->kabupaten->nama_kabupaten ?? '-' }}</p>
                                    <span class="last-seen"
                                        style="font-size: 0.65rem; padding: 0.3rem 0.6rem; background: #F1F5F9; border-radius: 99px; display: inline-flex; align-items: center; gap: 0.3rem;">
                                        <i class="fas fa-clock text-muted"></i>
                                        {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never' }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>


        <div class="row g-4 mb-5">
            <!-- Commodity Trends -->
            <div class="col-lg-12">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 1.5rem; overflow: hidden;">
                    <div class="card-header bg-white border-0 p-4 pb-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="fw-bold text-navy mb-0">Tren Harga Komoditas Utama</h5>
                                <p class="text-muted small mb-0 mt-1">Top 5 Komoditas yang paling aktif perubahannya</p>
                            </div>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-light border-0 shadow-sm rounded-pill px-3 dropdown-toggle"
                                    type="button" data-bs-toggle="dropdown">
                                    @if($selectedYearId === 'all')
                                        Semua Tahun
                                    @else
                                        {{ $allYears->firstWhere('id', $selectedYearId)->tahun ?? 'Pilih Tahun' }}
                                    @endif
                                </button>
                                <ul class="dropdown-menu border-0 shadow">
                                    <li>
                                        <a class="dropdown-item {{ $selectedYearId === 'all' ? 'active' : '' }}"
                                            href="{{ route('dashboard', ['year_id' => 'all']) }}">Semua Tahun</a>
                                    </li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    @foreach($allYears as $year)
                                        <li>
                                            <a class="dropdown-item {{ $selectedYearId == $year->id ? 'active' : '' }}"
                                                href="{{ route('dashboard', ['year_id' => $year->id]) }}">{{ $year->tahun }}</a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div style="height: 400px;">
                            <canvas id="priceChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Content Sections -->
        <div class="row g-4 mb-5">

            <!-- Price Range Glance -->
            <div class="col-lg-12">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 1.25rem; overflow: hidden;">
                    <div class="card-header bg-white border-0 p-4 pb-0 d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="fw-bold text-navy mb-0">
                                <i class="fas fa-tag me-2 text-warning opacity-75"></i> Perubahan Harga Terbaru
                            </h5>
                            <p class="text-muted small mb-0 mt-1">Monitoring fluktuasi harga komoditas wilayah</p>
                        </div>
                        <a href="{{ route('price-range.index') }}" class="btn btn-sm btn-light rounded-pill px-3">Lihat
                            Index</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light text-muted small text-uppercase fw-bold">
                                    <tr>
                                        <th class="ps-4">Komoditas & Wilayah</th>
                                        <th>Alasan Perubahan</th>
                                        <th class="text-end pe-4">Rentang Harga Baru</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($latestPriceChanges as $pc)
                                        <tr>
                                            <td class="ps-4">
                                                <div class="fw-bold text-navy text-sm">
                                                    {{ $pc->komoditas->nama_komoditas ?? '-' }}
                                                </div>
                                                <div class="text-xs text-muted">
                                                    <i class="fas fa-location-dot me-1"></i>
                                                    {{ $pc->kabupaten->nama_kabupaten ?? '-' }}
                                                </div>
                                                <small class="badge bg-light text-dark border-0 mt-1"
                                                    style="font-size: 0.65rem;">
                                                    {{ $pc->revisionHeader->label ?? 'Master' }}
                                                </small>
                                            </td>
                                            <td>
                                                <div class="small fw-medium text-muted">
                                                    {{ \Illuminate\Support\Str::limit($pc->alasan ?? '-', 50) }}
                                                </div>
                                            </td>
                                            <td class="text-end pe-4">
                                                <div class="d-flex flex-column align-items-end">
                                                    <span class="fw-bold text-navy text-sm">Rp
                                                        {{ number_format($pc->min_edit, 0, ',', '.') }}</span>
                                                    <span class="text-muted text-xs">s/d Rp
                                                        {{ number_format($pc->max_edit, 0, ',', '.') }}</span>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center py-4 text-muted small">Belum ada data perubahan
                                                harga terbaru</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lower Sections: SERUTI & Fenomena -->
        <div class="row g-4">

            <!-- Fenomena Terbaru -->
            <div class="col-lg-12">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 1.25rem; overflow: hidden;">
                    <div class="card-header bg-white border-0 p-4 pb-0 d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="fw-bold text-navy mb-0">
                                <i class="fas fa-newspaper me-2 text-info opacity-75"></i> Fenomena Terbaru
                            </h5>
                            <p class="text-muted small mb-0 mt-1">Berita, Fenomena Sosial Ekonomi</p>
                        </div>
                        <a href="{{ route('fenomena.index') }}" class="btn btn-sm btn-light rounded-pill px-3">Lihat
                            Fenonena</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light text-muted small text-uppercase fw-bold">
                                    <tr>
                                        <th class="ps-4">Judul Fenomena</th>
                                        <th class="text-center">Sumber</th>
                                        <th class="text-end pe-4">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($latestFenomena as $f)
                                        <tr>
                                            <td class="ps-4">
                                                @if($f->status_verifikasi === 'Y')
                                                    <a href="{{ route('fenomena.show', ['id' => $f->id, 'from' => 'dashboard']) }}"
                                                        class="text-decoration-none group">
                                                        <div class="fw-bold text-navy text-sm text-truncate transition-all"
                                                            style="max-width: 250px;" title="{{ $f->judul }}">
                                                            <i class="fas fa-external-link-alt small opacity-0 btn-link-icon transition-all"
                                                                style="font-size: 0.6rem;"></i> {{ $f->judul }}
                                                        </div>
                                                    </a>
                                                @else
                                                    <div class="fw-bold text-navy text-sm text-truncate opacity-75"
                                                        style="max-width: 250px;" title="{{ $f->judul }}">
                                                        {{ $f->judul }}
                                                    </div>
                                                @endif
                                                <small class="text-muted text-xs">
                                                    <i class="fas fa-calendar-day me-1"></i>
                                                    {{ \Carbon\Carbon::parse($f->tanggal_berita)->format('d M Y') }}
                                                </small>
                                            </td>
                                            <td class="text-center">
                                                <span class="small text-muted">{{ $f->sumberBerita->nama ?? '-' }}</span>
                                            </td>
                                            <td class="text-end pe-4">
                                                @php
                                                    $statusLabel = ['Y' => 'Verified', 'P' => 'Pending', 'T' => 'Rejected'][$f->status_verifikasi] ?? $f->status_verifikasi;
                                                    $statusClass = ['Y' => 'bg-success-faded text-success', 'P' => 'bg-warning-faded text-warning', 'T' => 'bg-danger-faded text-danger'][$f->status_verifikasi] ?? 'bg-light text-muted';
                                                @endphp
                                                <span class="badge rounded-pill {{ $statusClass }} small">
                                                    {{ $statusLabel }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center py-4 text-muted small">Belum ada fenomena
                                                tercatat</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        :root {
            --primary-faded: rgba(37, 99, 235, 0.1);
            --success-faded: rgba(34, 197, 94, 0.1);
            --danger-faded: rgba(239, 68, 68, 0.1);
            --warning-faded: rgba(245, 158, 11, 0.1);
        }

        .text-navy {
            color: #1e293b;
        }

        .text-xs {
            font-size: 0.75rem;
        }

        .text-sm {
            font-size: 0.875rem;
        }

        .letter-spacing-1 {
            letter-spacing: 0.1em;
        }

        .hover-up:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05) !important;
        }

        .transition-all {
            transition: all 0.3s ease;
        }

        .fade-in {
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes pulse {
            0% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7);
            }

            70% {
                transform: scale(1);
                box-shadow: 0 0 0 6px rgba(34, 197, 94, 0);
            }

            100% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(34, 197, 94, 0);
            }
        }

        .bg-blue-100 {
            background-color: #dbeafe;
        }

        .bg-danger-100 {
            background-color: #fee2e2;
        }

        .bg-success-100 {
            background-color: #dcfce7;
        }

        .bg-warning-100 {
            background-color: #fef3c7;
        }

        .bg-indigo-100 {
            background-color: #e0e7ff;
        }

        .group:hover .text-navy {
            color: #2563eb !important;
        }

        .group:hover .btn-link-icon {
            opacity: 1 !important;
            transform: translateX(2px);
        }

        .avatar-circle {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            font-weight: 700;
            color: white;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            text-transform: uppercase;
        }

        .bg-color-1 {
            background: linear-gradient(135deg, #FF6B6B, #EE5253);
        }

        .bg-color-2 {
            background: linear-gradient(135deg, #4834D4, #686DE0);
        }

        .bg-color-3 {
            background: linear-gradient(135deg, #20BF6B, #26DE81);
        }

        .bg-color-4 {
            background: linear-gradient(135deg, #F0932B, #FFBE76);
        }

        .bg-color-5 {
            background: linear-gradient(135deg, #A55EEA, #D1D8E0);
        }

        .bg-color-6 {
            background: linear-gradient(135deg, #2bcbba, #0fb9b1);
        }
    </style>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const colors = ['#0ea5e9', '#f59e0b', '#ef4444', '#10b981', '#6366f1'];


            // 3. Price Chart (Multi-line)
            const priceCtx = document.getElementById('priceChart').getContext('2d');
            new Chart(priceCtx, {
                type: 'line',
                data: {
                    labels: {!! json_encode($priceLabels) !!},
                    datasets: [
                        @foreach($priceTrend as $index => $trend)
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    {
                                label: '{{ $trend["name"] }}',
                                data: {!! json_encode($trend["data"]) !!},
                                borderColor: colors[{{ $index }} % colors.length],
                                borderWidth: 4,
                                tension: 0.3,
                                pointRadius: 5,
                                pointHoverRadius: 8,
                                fill: false
                            },
                        @endforeach
                                                                                                                                                                                                                                        ]
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
                            backgroundColor: '#1e293b',
                            padding: 15,
                            callbacks: {
                                label: function (context) {
                                    return context.dataset.label + ': Rp ' + new Intl.NumberFormat('id-ID').format(context.parsed.y);
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            grid: { color: 'rgba(0,0,0,0.05)', drawBorder: false },
                            ticks: {
                                font: { size: 11 },
                                callback: function (value) {
                                    return 'Rp ' + new Intl.NumberFormat('id-ID', { notation: 'compact' }).format(value);
                                }
                            }
                        },
                        x: { grid: { drawBorder: false }, ticks: { font: { size: 11 } } }
                    }
                }
            });
        });

        // 4. Counter Animation
        document.addEventListener('DOMContentLoaded', function () {
            const counters = document.querySelectorAll('.counter-value');
            const duration = 2000; // Total animation time in ms (2 seconds)

            counters.forEach(counter => {
                const target = +counter.getAttribute('data-target');
                if (target === 0) return;

                let startTimestamp = null;
                const step = (timestamp) => {
                    if (!startTimestamp) startTimestamp = timestamp;
                    const progress = Math.min((timestamp - startTimestamp) / duration, 1);
                    const currentCount = Math.floor(progress * target);

                    counter.innerText = currentCount.toLocaleString('id-ID');

                    if (progress < 1) {
                        window.requestAnimationFrame(step);
                    } else {
                        counter.innerText = target.toLocaleString('id-ID');
                    }
                };
                window.requestAnimationFrame(step);
            });
        });

        // Real-time Clock
        function updateClock() {
            const now = new Date();
            const d = String(now.getDate()).padStart(2, '0');
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            const m = months[now.getMonth()];
            const y = now.getFullYear();
            const h = String(now.getHours()).padStart(2, '0');
            const i = String(now.getMinutes()).padStart(2, '0');
            const s = String(now.getSeconds()).padStart(2, '0');

            const clockElement = document.getElementById('realtime-clock');
            if (clockElement) {
                clockElement.innerText = `${d} ${m} ${y}, ${h}:${i}:${s}`;
            }
        }
        setInterval(updateClock, 1000);
    </script>
@endpush