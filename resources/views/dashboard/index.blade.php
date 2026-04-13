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
            <div class="col-md-8">
                <h1 class="fw-bold text-navy mb-1" style="color: var(--bps-orange);">Analytics
                    Hub</h1>
                <p class="text-dark lead mb-0">Monitoring dan Analisis Sosial Ekonomi Regional</p>
            </div>
            <div class="col-md-4 text-md-end">
                <div class="d-inline-flex align-items-center bg-white shadow-sm p-2 rounded-pill px-4">
                    <div class="rounded-circle bg-success me-2"
                        style="width: 10px; height: 10px; animation: pulse 2s infinite;"></div>
                    <span class="small fw-bold text-navy">{{ now()->format('d M Y, H:i') }}</span>
                </div>
            </div>
        </div>


        <!-- Stats Grid (KPIs with Deltas) -->
        <div class="row g-4 mb-5">
            <!-- Kemiskinan -->
            <div class="col-xl-3 col-md-6">
                <div class="stats-card h-100 border-0 shadow-sm hover-up transition-all"
                    style="border-radius: 1.5rem; background: #ffffff;">
                    <div class="p-4">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                            <div class="rounded-circle bg-danger-faded p-2 text-danger d-flex align-items-center justify-content-center"
                                style="width: 45px; height: 45px;">
                                <i class="fas fa-users fa-lg"></i>
                            </div>
                            @if($deltas['poverty']['last_month'] > 0)
                                <span
                                    class="badge rounded-pill {{ $deltas['poverty']['percentage'] >= 0 ? 'bg-danger-faded text-danger' : 'bg-success-faded text-success' }} py-1 px-2"
                                    style="font-size: 0.7rem;">
                                    <i
                                        class="fas fa-{{ $deltas['poverty']['percentage'] >= 0 ? 'arrow-up' : 'arrow-down' }} me-1"></i>
                                    {{ abs($deltas['poverty']['percentage']) }}% <span class="fw-normal">dari bulan lalu</span>
                                </span>
                            @else
                                <span class="badge rounded-pill bg-info-faded text-info py-1 px-2" style="font-size: 0.7rem;">
                                    <i class="fas fa-sparkles me-1"></i> Baru Bulan Ini
                                </span>
                            @endif
                        </div>
                        <div>
                            <h3 class="fw-bold text-navy mb-1 counter-value"
                                data-target="{{ $stats['poverty_data_count'] }}">0</h3>
                            <p class="text-muted small mb-1 text-uppercase letter-spacing-1 fw-bold">Data Kemiskinan</p>
                            <div class="text-{{ $deltas['poverty']['last_month'] > 0 ? ($deltas['poverty']['percentage'] >= 0 ? 'danger' : 'success') : 'info' }} fw-bold"
                                style="font-size: 0.7rem;">
                                @if($deltas['poverty']['last_month'] > 0)
                                    {{ $deltas['poverty']['percentage'] >= 0 ? '↑' : '↓' }}
                                    {{ $deltas['poverty']['percentage'] >= 0 ? 'naik' : 'turun' }} dari
                                    {{ $deltas['poverty']['last_month'] }} bulan lalu
                                @else
                                    <i class="fas fa-info-circle me-1"></i> Mulai pencatatan baru bulan ini
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SERUTI -->
            <div class="col-xl-3 col-md-6">
                <div class="stats-card h-100 border-0 shadow-sm hover-up transition-all"
                    style="border-radius: 1.5rem; background: #ffffff;">
                    <div class="p-4">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                            <div class="rounded-circle bg-success-faded p-2 text-success d-flex align-items-center justify-content-center"
                                style="width: 45px; height: 45px;">
                                <i class="fas fa-house-chimney-user fa-lg"></i>
                            </div>
                            @if($deltas['seruti']['last_month'] > 0)
                                <span
                                    class="badge rounded-pill {{ $deltas['seruti']['percentage'] >= 0 ? 'bg-success-faded text-success' : 'bg-danger-faded text-danger' }} py-1 px-2"
                                    style="font-size: 0.7rem;">
                                    <i
                                        class="fas fa-{{ $deltas['seruti']['percentage'] >= 0 ? 'arrow-up' : 'arrow-down' }} me-1"></i>
                                    {{ abs($deltas['seruti']['percentage']) }}% <span class="fw-normal">dari minggu lalu</span>
                                </span>
                            @else
                                <span class="badge rounded-pill bg-info-faded text-info py-1 px-2" style="font-size: 0.7rem;">
                                    <i class="fas fa-sparkles me-1"></i> Data Perdana
                                </span>
                            @endif
                        </div>
                        <div>
                            <h3 class="fw-bold text-navy mb-1 counter-value"
                                data-target="{{ $stats['seruti_data_count'] }}">0</h3>
                            <p class="text-muted small mb-1 text-uppercase letter-spacing-1 fw-bold">Data SERUTI</p>
                            <div class="text-{{ $deltas['seruti']['last_month'] > 0 ? ($deltas['seruti']['percentage'] >= 0 ? 'success' : 'danger') : 'info' }} fw-bold"
                                style="font-size: 0.7rem;">
                                @if($deltas['seruti']['last_month'] > 0)
                                    {{ $deltas['seruti']['percentage'] >= 0 ? '↑' : '↓' }}
                                    {{ $deltas['seruti']['percentage'] >= 0 ? 'naik' : 'turun' }} dari
                                    {{ $deltas['seruti']['last_month'] }} minggu lalu
                                @else
                                    <i class="fas fa-info-circle me-1"></i> Baru tersedia minggu ini
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Perubahan Harga -->
            <div class="col-xl-3 col-md-6">
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
            <div class="col-xl-3 col-md-6">
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

        <!-- Main Analytics Section (Charts) -->
        <div class="row g-4 mb-5">
            <!-- Poverty Trend (Left) -->
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 1.5rem; overflow: hidden;">
                    <div class="card-header bg-white border-0 p-4 pb-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="fw-bold text-navy mb-0">Tren Rata-rata Kemiskinan</h5>
                                <p class="text-muted small mb-0 mt-1">Pergerakan angka kemiskinan dari waktu ke waktu</p>
                            </div>
                            <span class="badge bg-danger-faded text-danger rounded-pill px-3">MoM
                                {{ $deltas['poverty']['percentage'] >= 0 ? '+' : '' }}{{ $deltas['poverty']['percentage'] }}%</span>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div style="height: 350px;">
                            <canvas id="povertyChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Regional Distribution (Right) - NEW BAR CHART -->
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 1.5rem; overflow: hidden;">
                    <div class="card-header bg-white border-0 p-4 pb-0">
                        <h5 class="fw-bold text-navy mb-0">Distribusi Kemiskinan Regional</h5>
                        <p class="text-muted small mb-0 mt-1">Perbandingan tingkat kemiskinan antar kabupaten/kota</p>
                    </div>
                    <div class="card-body p-4">
                        <div style="height: 350px;">
                            <canvas id="regionalBarChart"></canvas>
                        </div>
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
                                    {{ $activeRhTahun->tahun ?? 'All Years' }}
                                </button>
                                <ul class="dropdown-menu border-0 shadow">
                                    <li><a class="dropdown-item" href="#">Semua Revisi</a></li>
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
            <!-- Kemiskinan & Indikator -->
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 1.25rem; overflow: hidden;">
                    <div class="card-header bg-white border-0 p-4 pb-0 d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="fw-bold text-navy mb-0">
                                <i class="fas fa-database me-2 text-danger opacity-75"></i> Update Kemiskinan
                            </h5>
                            <p class="text-muted small mb-0 mt-1">Rincian data terbaru berdasarkan variabel</p>
                        </div>
                        <a href="{{ route('poverty') }}" class="btn btn-sm btn-light rounded-pill px-3">Detail Data</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light text-muted small text-uppercase fw-bold">
                                    <tr>
                                        <th class="ps-4">Kabupaten/Kota</th>
                                        <th class="text-center">Variabel</th>
                                        <th class="text-center">Rata-rata Nilai</th>
                                        <th class="text-end pe-4">Tahun</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($latestPovertyData as $pd)
                                        <tr>
                                            <td class="ps-4">
                                                <div class="fw-bold text-navy text-sm">
                                                    {{ $pd->kabupaten_nama }}
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <span
                                                    class="badge bg-danger-faded text-danger rounded-pill px-3">{{ $pd->variabel_nama }}</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="small fw-semibold text-navy">Rp
                                                    {{ number_format($pd->avg_nilai, 0, ',', '.') }}</span>
                                            </td>
                                            <td class="text-end pe-4">
                                                <span class="text-muted small">{{ $pd->tahun }}</span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-4 text-muted small">Belum ada data kemiskinan
                                                terbaru</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Price Range Glance -->
            <div class="col-lg-7">
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
            <!-- SERUTI Terbaru -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 1.25rem; overflow: hidden;">
                    <div class="card-header bg-white border-0 p-4 pb-0 d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="fw-bold text-navy mb-0">
                                <i class="fas fa-house-chimney-user me-2 text-success opacity-75"></i> Update SERUTI
                            </h5>
                            <p class="text-muted small mb-0 mt-1">Aktivitas survei konsumsi & pengeluaran</p>
                        </div>
                        <a href="{{ route('seruti.index') }}" class="btn btn-sm btn-light rounded-pill px-3">Detail</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light text-muted small text-uppercase fw-bold">
                                    <tr>
                                        <th class="ps-4">Kabupaten & COICOP</th>
                                        <th class="text-center">Periode</th>
                                        <th class="text-end pe-4">Nilai Konsumsi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($latestSeruti as $s)
                                        <tr>
                                            <td class="ps-4">
                                                <div class="fw-bold text-navy text-sm">
                                                    {{ $s->kabupaten->nama_kabupaten ?? '-' }}
                                                </div>
                                                <small class="text-muted text-xs">
                                                    {{ $s->total_items }} Indikator COICOP
                                                </small>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-success-faded text-success rounded-pill px-2">
                                                    @if($s->period)
                                                        Q{{ $s->period->quarter }} {{ $s->period->year }}
                                                    @else
                                                        -
                                                    @endif
                                                </span>
                                            </td>
                                            <td class="text-end pe-4">
                                                <div class="d-flex flex-column align-items-end">
                                                    <span class="fw-bold text-navy text-sm">
                                                        {{ number_format($s->avg_value, 2, ',', '.') }}
                                                    </span>
                                                    <small class="text-muted text-xs" style="font-size: 0.65rem;">Rata-rata
                                                        Nilai</small>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center py-4 text-muted small">Belum ada data SERUTI
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Fenomena Terbaru -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 1.25rem; overflow: hidden;">
                    <div class="card-header bg-white border-0 p-4 pb-0 d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="fw-bold text-navy mb-0">
                                <i class="fas fa-newspaper me-2 text-info opacity-75"></i> Fenomena Terbaru
                            </h5>
                            <p class="text-muted small mb-0 mt-1">Berita, Fenomena Sosial Ekonomi</p>
                        </div>
                        <a href="{{ route('fenomena.index') }}" class="btn btn-sm btn-light rounded-pill px-3">Kelola</a>
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
    </style>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const colors = ['#0ea5e9', '#f59e0b', '#ef4444', '#10b981', '#6366f1'];

            // 1. Poverty Chart (Area Chart)
            const povertyCtx = document.getElementById('povertyChart').getContext('2d');
            const povertyGradient = povertyCtx.createLinearGradient(0, 0, 0, 300);
            povertyGradient.addColorStop(0, 'rgba(239, 68, 68, 0.2)');
            povertyGradient.addColorStop(1, 'rgba(239, 68, 68, 0)');

            new Chart(povertyCtx, {
                type: 'line',
                data: {
                    labels: {!! json_encode($povertyTrend->pluck('label')) !!},
                    datasets: [{
                        label: 'Rata-rata Nilai',
                        data: {!! json_encode($povertyTrend->pluck('avg')) !!},
                        borderColor: '#ef4444',
                        borderWidth: 4,
                        backgroundColor: povertyGradient,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 4,
                        pointBackgroundColor: '#ef4444'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                            backgroundColor: '#1e293b',
                            padding: 12,
                            titleFont: { size: 14, weight: 'bold' },
                            bodyFont: { size: 13 }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: false,
                            grid: { color: 'rgba(0,0,0,0.05)', drawBorder: false },
                            ticks: { font: { size: 11 } }
                        },
                        x: {
                            grid: { display: false, drawBorder: false },
                            ticks: { font: { size: 11 } }
                        }
                    }
                }
            });

            // 2. Regional Bar Chart (NEW)
            const regionalCtx = document.getElementById('regionalBarChart').getContext('2d');
            new Chart(regionalCtx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($regionalPoverty->pluck('name')) !!},
                    datasets: [{
                        label: 'Nilai Kemiskinan',
                        data: {!! json_encode($regionalPoverty->pluck('value')) !!},
                        backgroundColor: 'rgba(14, 165, 233, 0.8)',
                        borderRadius: 8,
                        borderSkipped: false,
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: { backgroundColor: '#1e293b' }
                    },
                    scales: {
                        x: { grid: { display: false }, ticks: { font: { size: 10 } } },
                        y: { grid: { display: false }, ticks: { font: { size: 10 } } }
                    }
                }
            });

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
    </script>
@endpush