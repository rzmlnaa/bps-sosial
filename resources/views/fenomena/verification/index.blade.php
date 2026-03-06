@extends('layouts.admin')

@section('title', 'Verifikasi Fenomena')

@push('styles')
    <style>
        /* ─── CSS Variables ─────────────────────────── */
        :root {
            --vf-primary: #f58220;
            --vf-primary-lt: #eff6ff;
            --vf-secondary: #64748b;
            --vf-success: #16a34a;
            --vf-success-lt: #f0fdf4;
            --vf-danger: #dc2626;
            --vf-danger-lt: #fef2f2;
            --vf-warning: #d97706;
            --vf-warning-lt: #fffbeb;
            --vf-border: #e2e8f0;
            --vf-surface: #f8fafc;
            --vf-text: #0f172a;
            --vf-muted: #64748b;
            --vf-radius: 14px;
            --vf-shadow: 0 1px 3px rgba(0, 0, 0, .06), 0 4px 16px rgba(0, 0, 0, .06);
            --vf-shadow-hover: 0 4px 12px rgba(37, 99, 235, .12), 0 8px 32px rgba(0, 0, 0, .08);
        }

        /* ─── Page wrapper ──────────────────────────── */
        .vf-page {
            font-family: 'Inter', sans-serif;
            color: var(--vf-text);
        }

        /* ─── Page Header ───────────────────────────── */
        .vf-header {
            margin-bottom: 1.75rem;
        }

        .vf-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--vf-text);
            letter-spacing: -.4px;
            margin: 0;
            line-height: 1.2;
        }

        .vf-subtitle {
            font-size: .9rem;
            color: var(--vf-muted);
            margin: .25rem 0 0;
        }

        /* ─── Stat Cards ────────────────────────────── */
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        @media (max-width: 992px) {
            .stat-grid {
                grid-template-columns: 1fr;
            }
        }

        .stat-card {
            background: #fff;
            border: 1px solid var(--vf-border);
            border-radius: var(--vf-radius);
            padding: 1.25rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            box-shadow: var(--vf-shadow);
            transition: transform .2s, box-shadow .2s;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--vf-shadow-hover);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            flex-shrink: 0;
        }

        .stat-icon.warning {
            background: var(--vf-warning-lt);
            color: var(--vf-warning);
        }

        .stat-icon.success {
            background: var(--vf-success-lt);
            color: var(--vf-success);
        }

        .stat-icon.danger {
            background: var(--vf-danger-lt);
            color: var(--vf-danger);
        }

        .stat-info {
            flex: 1;
            min-width: 0;
        }

        .stat-value {
            font-size: 1.75rem;
            font-weight: 700;
            line-height: 1;
            color: var(--vf-text);
        }

        .stat-label {
            font-size: .8rem;
            color: var(--vf-muted);
            margin-top: .2rem;
            font-weight: 500;
        }

        /* ─── Filter Bar ────────────────────────────── */
        .vf-filter-bar {
            background: #fff;
            border: 1px solid var(--vf-border);
            border-radius: var(--vf-radius);
            padding: 1rem 1.25rem;
            display: flex;
            flex-wrap: wrap;
            gap: .75rem;
            align-items: center;
            box-shadow: var(--vf-shadow);
            margin-bottom: 1.5rem;
        }

        .vf-filter-bar .search-wrap {
            position: relative;
            flex: 1 1 220px;
            min-width: 180px;
        }

        .vf-filter-bar .search-wrap i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--vf-muted);
            font-size: .85rem;
            pointer-events: none;
        }

        .vf-filter-bar .search-wrap input {
            padding-left: 2.25rem;
            height: 40px;
            border-radius: 10px;
            font-size: .875rem;
            border: 1px solid var(--vf-border);
            background: var(--vf-surface);
            transition: border-color .2s, box-shadow .2s;
            width: 100%;
        }

        .vf-filter-bar .search-wrap input:focus {
            outline: none;
            border-color: var(--vf-primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, .1);
            background: #fff;
        }

        .vf-select {
            height: 40px;
            padding: 0 .75rem;
            border-radius: 10px;
            font-size: .875rem;
            border: 1px solid var(--vf-border);
            background: var(--vf-surface);
            color: var(--vf-text);
            cursor: pointer;
            transition: border-color .2s, box-shadow .2s;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%2364748b' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 10px center;
            padding-right: 30px;
        }

        .vf-select:focus {
            outline: none;
            border-color: var(--vf-primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, .1);
            background-color: #fff;
        }

        .vf-date-input {
            height: 40px;
            padding: 0 .75rem;
            border-radius: 10px;
            font-size: .875rem;
            border: 1px solid var(--vf-border);
            background: var(--vf-surface);
            color: var(--vf-text);
            transition: border-color .2s, box-shadow .2s;
            max-width: 160px;
        }

        .vf-date-input:focus {
            outline: none;
            border-color: var(--vf-primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, .1);
            background: #fff;
        }

        .btn-reset {
            height: 40px;
            padding: 0 1rem;
            border-radius: 10px;
            font-size: .875rem;
            font-weight: 500;
            border: 1px solid var(--vf-border);
            background: #fff;
            color: var(--vf-muted);
            cursor: pointer;
            transition: all .2s;
            display: flex;
            align-items: center;
            gap: .4rem;
            white-space: nowrap;
        }

        .btn-reset:hover {
            background: var(--vf-surface);
            color: var(--vf-danger);
            border-color: var(--vf-danger);
        }

        /* ─── Fenomena Card List ─────────────────────── */
        .fenomena-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .fenomena-card {
            background: #fff;
            border: 1px solid var(--vf-border);
            border-radius: var(--vf-radius);
            padding: 1.25rem 1.5rem;
            box-shadow: var(--vf-shadow);
            transition: transform .2s, box-shadow .2s, border-color .2s;
            position: relative;
            overflow: hidden;
        }

        .fenomena-card::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            border-radius: 4px 0 0 4px;
            background: var(--vf-primary);
            opacity: 0;
            transition: opacity .2s;
        }

        .fenomena-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--vf-shadow-hover);
            border-color: #c7d7f0;
        }

        .fenomena-card:hover::before {
            opacity: 1;
        }

        /* ─── Card Top Row ─────── */
        .card-top-row {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: .5rem;
            margin-bottom: .75rem;
        }

        /* ─── Source Badge ─────── */
        .badge-source {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            padding: .3rem .75rem;
            border-radius: 8px;
            font-size: .78rem;
            font-weight: 600;
            line-height: 1;
        }

        .badge-source.berita-online {
            background: #eff6ff;
            color: #1d4ed8;
            border: 1px solid #bfdbfe;
        }

        .badge-source.koran {
            background: #faf5ff;
            color: #7c3aed;
            border: 1px solid #ddd6fe;
        }

        .badge-source.media-sosial {
            background: #f0fdf4;
            color: #15803d;
            border: 1px solid #bbf7d0;
        }

        .badge-source.laporan {
            background: #fff7ed;
            color: #c2410c;
            border: 1px solid #fed7aa;
        }

        /* ─── Status Badge ─────── */
        .badge-status {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            padding: .3rem .8rem;
            border-radius: 50px;
            font-size: .78rem;
            font-weight: 600;
            line-height: 1;
        }

        .badge-status.menunggu {
            background: var(--vf-warning-lt);
            color: var(--vf-warning);
            border: 1px solid #fcd34d;
        }

        .badge-status.menunggu::before {
            content: '';
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: var(--vf-warning);
            display: inline-block;
            animation: pulse-dot 1.5s ease-in-out infinite;
        }

        @keyframes pulse-dot {

            0%,
            100% {
                opacity: 1;
                transform: scale(1);
            }

            50% {
                opacity: .5;
                transform: scale(.75);
            }
        }

        /* ─── Card Title & Preview ─────── */
        .fenomena-title {
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--vf-text);
            margin: 0 0 .4rem;
            line-height: 1.35;
        }

        .fenomena-preview {
            font-size: .875rem;
            color: var(--vf-muted);
            line-height: 1.6;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            margin-bottom: .75rem;
        }

        /* ─── Meta Info Row ─────── */
        .meta-row {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: .5rem .75rem;
            margin-bottom: .85rem;
        }

        .meta-item {
            display: inline-flex;
            align-items: center;
            gap: .3rem;
            font-size: .8rem;
            color: var(--vf-muted);
        }

        .meta-item i {
            font-size: .75rem;
            color: #94a3b8;
        }

        .badge-sektor {
            display: inline-flex;
            align-items: center;
            padding: .2rem .6rem;
            border-radius: 6px;
            font-size: .78rem;
            font-weight: 600;
            background: #f1f5f9;
            color: #475569;
            border: 1px solid var(--vf-border);
        }

        .badge-indikator {
            display: inline-flex;
            align-items: center;
            padding: .2rem .6rem;
            border-radius: 6px;
            font-size: .78rem;
            font-weight: 600;
            background: var(--vf-primary-lt);
            color: var(--vf-primary);
            border: 1px solid #bfdbfe;
        }

        /* ─── Card Footer Actions ─────── */
        .card-footer-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-top: .85rem;
            border-top: 1px solid #f1f5f9;
            gap: .75rem;
        }

        .creator-info {
            display: flex;
            align-items: center;
            gap: .5rem;
        }

        .creator-avatar {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .7rem;
            font-weight: 700;
            color: #fff;
            flex-shrink: 0;
        }

        .creator-name {
            font-size: .8rem;
            font-weight: 600;
            color: var(--vf-text);
            line-height: 1.2;
        }

        .creator-time {
            font-size: .74rem;
            color: var(--vf-muted);
            line-height: 1.2;
        }

        .action-group {
            display: flex;
            gap: .5rem;
        }

        .btn-detail {
            height: 34px;
            padding: 0 1rem;
            border-radius: 8px;
            font-size: .82rem;
            font-weight: 500;
            border: 1px solid var(--vf-border);
            background: #fff;
            color: var(--vf-secondary);
            cursor: pointer;
            transition: all .2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: .35rem;
        }

        .btn-detail:hover {
            background: var(--vf-surface);
            color: var(--vf-text);
            border-color: #cbd5e1;
        }

        .btn-verify {
            height: 34px;
            padding: 0 1.1rem;
            border-radius: 8px;
            font-size: .82rem;
            font-weight: 600;
            border: none;
            background: var(--vf-primary);
            color: #fff;
            cursor: pointer;
            transition: all .2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            box-shadow: 0 2px 8px rgba(37, 99, 235, .25);
        }

        .btn-verify:hover {
            background: #1d4ed8;
            color: #fff;
            box-shadow: 0 4px 14px rgba(37, 99, 235, .35);
            transform: translateY(-1px);
        }

        /* ─── Empty State ─────── */
        .empty-state {
            background: #fff;
            border: 1px solid var(--vf-border);
            border-radius: var(--vf-radius);
            box-shadow: var(--vf-shadow);
            padding: 4rem 2rem;
            text-align: center;
        }

        .empty-state-icon {
            font-size: 3rem;
            color: #cbd5e1;
            margin-bottom: 1rem;
        }

        .empty-state h5 {
            font-weight: 700;
            color: var(--vf-text);
            margin-bottom: .5rem;
        }

        .empty-state p {
            color: var(--vf-muted);
            font-size: .9rem;
        }

        /* ─── Results count ──────────────────────────── */
        .results-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: .75rem;
        }

        .results-count {
            font-size: .85rem;
            color: var(--vf-muted);
            font-weight: 500;
        }

        .results-count strong {
            color: var(--vf-text);
        }

        /* ─── Pagination ─────────────────────────────── */
        .vf-pagination {
            display: flex;
            just ify-content: center;
            align-items: center;
            gap: .35rem;
            margin-top: 1.75rem;
            flex -wrap: wrap;
        }

        .vf-pagination .page-link {
            display: inline-flex;
            alig n-items: center;
            justify-content: center;
            min-width: 36px;
            height: 36px;
            padd ing: 0 .65rem;
            border-radius: 9px;
            font-size: .82rem;
            font-weight: 500;
            bord er: 1px solid var(--vf-border);
            background: #fff;
            color: var(--vf-secondary);
            text-decoration: none;
            tran sition: all .18s;
            curs or: pointer;
        }

        .vf- pagination .page-link:hover {
            back ground: var(--vf-surface);
            border-color: #cbd5e1;
            color: var(--vf-text);
        }

        .vf-pagi nation .page-link.active {
            back ground: var(--vf-primary);
            border-color: var(--vf-primary);
            color: #fff;
            box-shadow: 0 2px 8px rgba(245, 130, 32, .3);
            cursor: default;
        }

        .vf-pagination .page-link.disabled {
            opacity: .45;
            cursor: not-allowed;
            pointer-events: none;
        }

        .vf-pagination-info {
            text-align: center;
            font-size: .8rem;
            color: var(--vf-muted);
            margin-top: .5rem;
        }

        /* ─── Responsive tweaks ──────────────────────────── */
        @media (max-width: 768px) {
            .vf-title {
                font-size: 1.35rem;
            }

            .stat-grid {
                grid-template-columns: 1fr;
                gap: .6rem;
            }

            .stat-card {
                padding: 1rem;
            }

            .stat-value {
                font-size: 1.35rem;
            }

            .fenomena-card {
                padding: 1rem;
            }

            .card-footer-row {
                flex-direction: column;
                align-items: flex-start;
            }

            .action-group {
                width: 100%;
            }

            .btn-detail,
            .btn-verify {
                flex: 1;
                justify-content: center;
            }

            .vf-tab-btn {
                padding: .5rem .75rem;
                font-size: .8rem;
            }

            .vf-tab-btn .tab-count {
                min-width: 18px;
                height: 18px;
                font-size: .65rem;
            }
        }

        /* ─── Tab Switcher ───────────────────────────── */
        .vf-tabs {
            display: flex;
            gap: .5rem;
            margin-bottom: 1.5rem;
            border-bottom: 2px solid var(--vf-border);
            padding-bottom: 0;
            overflow-x: auto;
            flex-wrap: nowrap;
            /* Hide scrollbar for Chrome, Safari and Opera */
            scrollbar-width: none;
            /* Firefox */
            -ms-overflow-style: none;
            /* IE and Edge */
        }

        .vf-tabs::-webkit-scrollbar {
            display: none;
        }

        .vf-tab-btn {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            padding: .6rem 1.25rem;
            border-radius: 10px 10px 0 0;
            font-size: .875rem;
            font-weight: 600;
            border: 1px solid transparent;
            border-bottom: none;
            background: transparent;
            color: var(--vf-muted);
            text-decoration: none;
            cursor: pointer;
            transition: all .2s;
            margin-bottom: -2px;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .vf-tab-btn:hover {
            background: var(--vf-surface);
            color: var(--vf-text);
        }

        .vf-tab-btn.active {
            background: #fff;
            color: var(--vf-primary);
            border-color: var(--vf-border);
            border-bottom-color: #fff;
        }

        .vf-tab-btn .tab-count {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 22px;
            height: 20px;
            border-radius: 50px;
            font-size: .72rem;
            font-weight: 700;
            padding: 0 6px;
            background: var(--vf-surface);
            color: var(--vf-muted);
        }

        .vf-tab-btn.active .tab-count {
            background: var(--vf-primary);
            color: #fff;
        }

        /* ─── Riwayat card extras ──────────────────────── */
        .badge-status.diverifikasi {
            background: var(--vf-success-lt);
            color: var(--vf-success);
            border: 1px solid #86efac;
        }

        .badge-status.ditolak {
            background: var(--vf-danger-lt);
            color: var(--vf-danger);
            border: 1px solid #fca5a5;
        }

        .verified-by-row {
            display: flex;
            align-items: center;
            gap: .65rem;
            padding: .75rem 1rem;
            background: var(--vf-surface);
            border-radius: 10px;
            margin-top: .85rem;
            border: 1px solid var(--vf-border);
        }

        .verified-by-row.rejected {
            background: var(--vf-danger-lt);
            border-color: #fca5a5;
        }

        .verified-avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .78rem;
            font-weight: 700;
            color: #fff;
            flex-shrink: 0;
        }

        .verified-avatar.success {
            background: linear-gradient(135deg, #16a34a, #15803d);
        }

        .verified-avatar.danger {
            background: linear-gradient(135deg, #dc2626, #b91c1c);
        }

        .verified-meta {
            flex: 1;
            min-width: 0;
        }

        .verified-label {
            font-size: .72rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .5px;
            color: var(--vf-muted);
            margin-bottom: .1rem;
        }

        .verified-name {
            font-size: .85rem;
            font-weight: 700;
            color: var(--vf-text);
        }

        .verified-time {
            font-size: .78rem;
            color: var(--vf-muted);
        }
    </style>
@endpush

@section('content')
    <div class="vf-page fade-in-up">

        {{-- ════════════════════ HEADER ════════════════════ --}}

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
            <div>
                <h2 class="fw-bold mb-1" style="color: var(--bps-orange);">VERIFIKASI FENOMENA</h2>
                <p class="text-muted mb-0">Daftar fenomena yang menunggu verifikasi dari berbagai sumber berita &amp;
                    laporan</p>
            </div>


        </div>


        {{-- ════════════════════ STAT CARDS ════════════════════ --}}
        <div class="stat-grid">
            {{-- Total Menunggu --}}
            <div class="stat-card">
                <div class="stat-icon warning">
                    <i class="fas fa-hourglass-half"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-value" id="stat-menunggu">{{ $totalMenunggu }}</div>
                    <div class="stat-label">Total Menunggu</div>
                </div>
            </div>

            {{-- Total Diverifikasi --}}
            <div class="stat-card">
                <div class="stat-icon success">
                    <i class="fas fa-check-double"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-value" id="stat-verified">{{ $totalDiverifikasi }}</div>
                    <div class="stat-label">Total Diverifikasi</div>
                </div>
            </div>

            {{-- Total Ditolak --}}
            <div class="stat-card">
                <div class="stat-icon danger">
                    <i class="fas fa-times-circle"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-value" id="stat-rejected">{{ $totalDitolak }}</div>
                    <div class="stat-label">Total Ditolak</div>
                </div>
            </div>
        </div>

        {{-- ════════════════════ TAB SWITCHER ════════════════════ --}}
        <div class="vf-tabs">
            <a href="{{ route('fenomena.verification.index', array_merge(request()->except(['tab', 'page']), ['tab' => 'pending'])) }}"
                class="vf-tab-btn {{ $tab === 'pending' ? 'active' : '' }}">
                <i class="fas fa-hourglass-half"></i>
                Menunggu Verifikasi
                <span class="tab-count">{{ $totalMenunggu }}</span>
            </a>
            <a href="{{ route('fenomena.verification.index', array_merge(request()->except(['tab', 'page']), ['tab' => 'riwayat'])) }}"
                class="vf-tab-btn {{ $tab === 'riwayat' ? 'active' : '' }}">
                <i class="fas fa-history"></i>
                Riwayat Verifikasi
                <span class="tab-count">{{ $totalDiverifikasi + $totalDitolak }}</span>
            </a>
        </div>

        @if($tab === 'pending')

            {{-- ════════════════════ FILTER BAR ════════════════════ --}}
            <form method="GET" action="{{ route('fenomena.verification.index') }}" id="filter-form" class="vf-filter-bar">
                <input type="hidden" name="tab" value="pending">

                {{-- Search --}}
                <div class="search-wrap">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search" id="filter-search" placeholder="Cari judul fenomena..." autocomplete="off"
                        value="{{ request('search') }}">
                </div>

                {{-- Sektor --}}
                <select class="vf-select" name="sektor" id="filter-sektor" style="min-width:140px;">
                    <option value="">🏢 Semua Sektor</option>
                    @foreach($sektors ?? [] as $sektor)
                        <option value="{{ $sektor->kode }}" @selected(request('sektor') == $sektor->kode)>
                            {{ $sektor->nama }}
                        </option>
                    @endforeach
                </select>

                {{-- Indikator --}}
                <select class="vf-select" name="indikator" id="filter-indikator" style="min-width:150px;">
                    <option value="">📊 Semua Indikator</option>
                    @foreach($indikators ?? [] as $ind)
                        <option value="{{ $ind->kode }}" @selected(request('indikator') == $ind->kode)>
                            {{ $ind->nama }}
                        </option>
                    @endforeach
                </select>

                {{-- Sumber Berita --}}
                <select class="vf-select" name="sumber" id="filter-sumber" style="min-width:160px;">
                    <option value="">📰 Semua Sumber</option>
                    @foreach($sumberBeritas ?? [] as $sb)
                        <option value="{{ $sb->id }}" @selected(request('sumber') == $sb->id)>
                            📰 {{ $sb->nama }}
                        </option>
                    @endforeach
                </select>

                {{-- Tipe Tanggal + Rentang --}}
                <div style="display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;">
                    <select class="vf-select" name="date_type" id="filter-date-type" title="Tipe tanggal"
                        style="min-width:155px;">
                        <option value="tanggal_berita" @selected(request('date_type', 'tanggal_berita') === 'tanggal_berita')>🗓️
                            Tgl. Berita</option>
                        <option value="created_at" @selected(request('date_type') === 'created_at')>🗓️ Tgl. Input</option>
                    </select>
                    <input type="date" class="vf-date-input" name="date_from" id="filter-date-from" title="Dari tanggal"
                        value="{{ request('date_from') }}">
                    <span style="font-size:.8rem;color:var(--vf-muted);">–</span>
                    <input type="date" class="vf-date-input" name="date_to" id="filter-date-to" title="Sampai tanggal"
                        value="{{ request('date_to') }}">
                </div>

                {{-- Tombol Cari + Reset --}}
                <button type="submit" class="btn-verify" style="height:40px;padding:0 1.1rem;">
                    <i class="fas fa-search"></i> Cari
                </button>
                <a href="{{ route('fenomena.verification.index') }}" class="btn-reset" style="height:40px;">
                    <i class="fas fa-undo-alt"></i> Reset
                </a>

            </form>

            {{-- ════════════════════ RESULTS BAR ════════════════════ --}}
            <div class="results-bar">
                <div class="results-count">
                    Menampilkan
                    <strong>{{ $fenomenas->firstItem() ?? 0 }}–{{ $fenomenas->lastItem() ?? 0 }}</strong>
                    dari <strong>{{ $totalFiltered }}</strong> fenomena
                    @if(request()->hasAny(['search', 'sektor', 'indikator', 'sumber', 'date_from', 'date_to']))
                        <span style="color:var(--vf-warning);font-size:.78rem;margin-left:.4rem;">
                            <i class="fas fa-filter"></i> Filter aktif
                        </span>
                    @endif
                </div>
                <div style="font-size:.8rem;color:var(--vf-muted);">
                    Halaman {{ $fenomenas->currentPage() }} / {{ $fenomenas->lastPage() }}
                </div>
            </div>

            {{-- ════════════════════ FENOMENA CARD LIST ════════════════════ --}}
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show rounded-3 mb-3" role="alert">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="fenomena-list" id="fenomena-list">
                @forelse($fenomenas as $fenomena)
                    @php
                        $sumberNama = $fenomena->sumberBerita->nama ?? 'Tidak diketahui';
                        $sumberLower = strtolower($sumberNama);
                        if (str_contains($sumberLower, 'koran') || str_contains($sumberLower, 'cetak'))
                            $sumberClass = 'koran';
                        elseif (str_contains($sumberLower, 'sosial') || str_contains($sumberLower, 'media sosial'))
                            $sumberClass = 'media-sosial';
                        elseif (str_contains($sumberLower, 'laporan') || str_contains($sumberLower, 'internal'))
                            $sumberClass = 'laporan';
                        else
                            $sumberClass = 'berita-online';

                        $sumberEmoji = match ($sumberClass) {
                            'koran' => '🗞️',
                            'media-sosial' => '📱',
                            'laporan' => '📊',
                            default => '📰',
                        };

                        $utama = $fenomena->indikators->where('kelompok', 'utama')->first();

                        // Build creator initials
                        $creatorName = $fenomena->creator->name ?? 'System';
                        $initials = collect(explode(' ', $creatorName))->take(2)->map(fn($w) => strtoupper(substr($w, 0, 1)))->implode('');
                    @endphp

                    <div class="fenomena-card" data-search="{{ strtolower($fenomena->judul . ' ' . $fenomena->penjelasan) }}"
                        data-sumber="{{ $fenomena->sumber_berita_id }}"
                        data-sektor="{{ $fenomena->sektors->pluck('kode')->implode(',') }}"
                        data-indikator="{{ $fenomena->indikators->pluck('kode')->implode(',') }}"
                        data-tanggal-berita="{{ $fenomena->tanggal_berita }}"
                        data-created-at="{{ $fenomena->created_at->toDateString() }}">

                        {{-- Top Row: Source Badge | Status Badge --}}
                        <div class="card-top-row">
                            <span class="badge-source {{ $sumberClass }}">
                                {{ $sumberEmoji }} {{ $sumberNama }}
                            </span>
                            <span class="badge-status menunggu">
                                Menunggu Verifikasi
                            </span>
                        </div>

                        {{-- Title --}}
                        <h2 class="fenomena-title">{{ $fenomena->judul }}</h2>

                        {{-- Preview --}}
                        <p class="fenomena-preview">{{ $fenomena->penjelasan }}</p>

                        {{-- Meta Info --}}
                        <div class="meta-row">
                            {{-- Tanggal Berita --}}
                            <span class="meta-item">
                                <i class="fas fa-calendar-alt"></i>
                                {{ \Carbon\Carbon::parse($fenomena->tanggal_berita)->translatedFormat('d F Y') }}
                            </span>

                            {{-- Sektor --}}
                            @foreach($fenomena->sektors->take(2) as $sektor)
                                <span class="badge-sektor">
                                    🏢 [{{ $sektor->kode }}] {{ Str::limit($sektor->nama, 20) }}
                                </span>
                            @endforeach
                            @if($fenomena->sektors->count() > 2)
                                <span class="badge-sektor">+{{ $fenomena->sektors->count() - 2 }} lagi</span>
                            @endif

                            {{-- Indikator Utama --}}
                            @if($utama)
                                <span class="badge-indikator">
                                    📊 {{ Str::limit($utama->nama, 24) }}
                                </span>
                            @endif

                            {{-- Link Berita --}}
                            @if($fenomena->link_berita)
                                <a href="{{ $fenomena->link_berita }}" target="_blank" class="meta-item"
                                    style="color: var(--vf-primary); text-decoration: none;">
                                    <i class="fas fa-external-link-alt"></i>
                                    Buka Link
                                </a>
                            @endif
                        </div>

                        {{-- Footer: Creator + Actions --}}
                        <div class="card-footer-row">
                            {{-- Creator --}}
                            <div class="creator-info">
                                <div class="creator-avatar">{{ $initials }}</div>
                                <div>
                                    <div class="creator-name">👤 {{ $creatorName }}</div>
                                    <div class="creator-time">{{ $fenomena->created_at->diffForHumans() }}</div>
                                </div>
                            </div>

                            {{-- Action Buttons --}}
                            <div class="action-group">
                                <a href="{{ route('fenomena.show', [$fenomena->id, 'from' => 'verification']) }}"
                                    class="btn-detail">
                                    <i class="fas fa-eye"></i> Detail
                                </a>
                                <a href="{{ route('fenomena.verification.show', $fenomena->id) }}" class="btn-verify">
                                    <i class="fas fa-check-circle"></i> Verifikasi
                                </a>
                            </div>
                        </div>

                    </div>
                @empty
                    {{-- Empty State --}}
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="fas fa-check-double"></i>
                        </div>
                        <h5>Semua Fenomena Terverifikasi!</h5>
                        <p>Tidak ada fenomena yang menunggu verifikasi saat ini.<br>Sistem sudah bersih dan up-to-date.</p>
                    </div>
                @endforelse
            </div>

            {{-- ════════════════════ PAGINATION (pending) ════════════════════ --}}
            @if($fenomenas->hasPages())
                <div class="vf-pagination">
                    {{-- Prev --}}
                    @if($fenomenas->onFirstPage())
                        <span class="page-link disabled"><i class="fas fa-chevron-left"></i></span>
                    @else
                        <a class="page-link" href="{{ $fenomenas->previousPageUrl() }}"><i class="fas fa-chevron-left"></i></a>
                    @endif

                    {{-- Page Numbers --}}
                    @foreach($fenomenas->getUrlRange(max(1, $fenomenas->currentPage() - 2), min($fenomenas->lastPage(), $fenomenas->currentPage() + 2)) as $page => $url)
                        @if($page == $fenomenas->currentPage())
                            <span class="page-link active">{{ $page }}</span>
                        @else
                            <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                        @endif
                    @endforeach

                    {{-- Next --}}
                    @if($fenomenas->hasMorePages())
                        <a class="page-link" href="{{ $fenomenas->nextPageUrl() }}"><i class="fas fa-chevron-right"></i></a>
                    @else
                        <span class="page-link disabled"><i class="fas fa-chevron-right"></i></span>
                    @endif
                </div>
            @endif

        @else
            {{-- ═══════════════════ TAB RIWAYAT ═══════════════════ --}}

            {{-- Filter ringkas riwayat --}}
            <form method="GET" action="{{ route('fenomena.verification.index') }}" id="filter-form" class="vf-filter-bar">
                <input type="hidden" name="tab" value="riwayat">

                {{-- Search --}}
                <div class="search-wrap">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search" id="filter-search" placeholder="Cari judul fenomena..." autocomplete="off"
                        value="{{ request('search') }}">
                </div>

                {{-- Filter status: Y / T / semua --}}
                <select class="vf-select" name="status_riwayat" id="filter-status-riwayat" style="min-width:165px;">
                    <option value="">&#x1F4CB; Semua Status</option>
                    <option value="Y" @selected(request('status_riwayat') === 'Y')>&#x2705; Diverifikasi</option>
                    <option value="T" @selected(request('status_riwayat') === 'T')>&#x274C; Ditolak</option>
                </select>

                {{-- Sumber --}}
                <select class="vf-select" name="sumber" id="filter-sumber" style="min-width:155px;">
                    <option value="">&#x1F4F0; Semua Sumber</option>
                    @foreach($sumberBeritas ?? [] as $sb)
                        <option value="{{ $sb->id }}" @selected(request('sumber') == $sb->id)>
                            &#x1F4F0; {{ $sb->nama }}
                        </option>
                    @endforeach
                </select>

                {{-- Sektor --}}
                <select class="vf-select" name="sektor" id="filter-sektor" style="min-width:140px;">
                    <option value="">&#x1F3E2; Semua Sektor</option>
                    @foreach($sektors ?? [] as $sektor)
                        <option value="{{ $sektor->kode }}" @selected(request('sektor') == $sektor->kode)>
                            {{ $sektor->nama }}
                        </option>
                    @endforeach
                </select>

                <button type="submit" class="btn-verify" style="height:40px;padding:0 1.1rem;">
                    <i class="fas fa-search"></i> Cari
                </button>
                <a href="{{ route('fenomena.verification.index', ['tab' => 'riwayat']) }}" class="btn-reset"
                    style="height:40px;">
                    <i class="fas fa-undo-alt"></i> Reset
                </a>
            </form>

            {{-- Results bar riwayat --}}
            <div class="results-bar">
                <div class="results-count">
                    Menampilkan <strong>{{ $riwayat->firstItem() ?? 0 }}&ndash;{{ $riwayat->lastItem() ?? 0 }}</strong>
                    dari <strong>{{ $totalFiltered }}</strong> riwayat
                </div>
                <div style="font-size:.8rem;color:var(--vf-muted);">
                    Halaman {{ $riwayat->currentPage() }} / {{ $riwayat->lastPage() }}
                </div>
            </div>

            {{-- Riwayat Card List --}}
            <div class="fenomena-list">
                @forelse($riwayat as $item)
                    @php
                        $isVerified = $item->status_verifikasi === 'Y';
                        $sumberNamaR = $item->sumberBerita->nama ?? 'Tidak diketahui';
                        $sumberLowerR = strtolower($sumberNamaR);
                        if (str_contains($sumberLowerR, 'koran') || str_contains($sumberLowerR, 'cetak'))
                            $sumberClassR = 'koran';
                        elseif (str_contains($sumberLowerR, 'sosial'))
                            $sumberClassR = 'media-sosial';
                        elseif (str_contains($sumberLowerR, 'laporan'))
                            $sumberClassR = 'laporan';
                        else
                            $sumberClassR = 'berita-online';
                        $sumberEmojiR = match ($sumberClassR) {
                            'koran' => '&#x1F5DE;&#xFE0F;',
                            'media-sosial' => '&#x1F4F1;',
                            'laporan' => '&#x1F4CA;',
                            default => '&#x1F4F0;',
                        };
                        $verifierName = $item->verifier->name ?? 'Sistem';

                        $verifierInit = collect(explode(' ', $verifierName))->take(2)->map(fn($w) => strtoupper(substr($w, 0, 1)))->implode('');
                        $creatorNameR = $item->creator->name ?? 'System';
                        $creatorInitR = collect(explode(' ', $creatorNameR))->take(2)->map(fn($w) => strtoupper(substr($w, 0, 1)))->implode('');
                    @endphp

                    <div class="fenomena-card">

                        {{-- Top Row --}}
                        <div class="card-top-row">
                            <span class="badge-source {{ $sumberClassR }}">
                                {!! $sumberEmojiR !!} {{ $sumberNamaR }}
                            </span>
                            @if($isVerified)
                                <span class="badge-status diverifikasi">
                                    <i class="fas fa-check-circle"></i> Diverifikasi
                                </span>
                            @else
                                <span class="badge-status ditolak">
                                    <i class="fas fa-times-circle"></i> Ditolak
                                </span>
                            @endif
                        </div>

                        {{-- Title --}}
                        <h2 class="fenomena-title">{{ $item->judul }}</h2>

                        {{-- Preview --}}
                        <p class="fenomena-preview">{{ $item->penjelasan }}</p>

                        {{-- Meta --}}
                        <div class="meta-row">
                            <span class="meta-item">
                                <i class="fas fa-calendar-alt"></i>
                                {{ \Carbon\Carbon::parse($item->tanggal_berita)->translatedFormat('d F Y') }}
                            </span>
                            @foreach($item->sektors->take(2) as $sek)
                                <span class="badge-sektor">&#x1F3E2; [{{ $sek->kode }}] {{ Str::limit($sek->nama, 20) }}</span>
                            @endforeach
                            @if($item->sektors->count() > 2)
                                <span class="badge-sektor">+{{ $item->sektors->count() - 2 }} lagi</span>
                            @endif
                        </div>

                        {{-- Verifier Info --}}
                        <div class="verified-by-row {{ $isVerified ? '' : 'rejected' }}">
                            <div class="verified-avatar {{ $isVerified ? 'success' : 'danger' }}">{{ $verifierInit }}</div>
                            <div class="verified-meta">
                                <div class="verified-label">{{ $isVerified ? 'Diverifikasi oleh' : 'Ditolak oleh' }}</div>
                                <div class="verified-name">{{ $verifierName }}</div>
                            </div>
                            <div style="text-align:right;">
                                <div class="verified-time">
                                    <i class="fas fa-clock" style="margin-right:.25rem;"></i>
                                    @if($item->verified_at)
                                        {{ \Carbon\Carbon::parse($item->verified_at)->translatedFormat('d F Y, H:i') }}
                                    @else
                                        &mdash;
                                    @endif
                                </div>
                                <div class="verified-time" style="margin-top:.15rem;color:#94a3b8;">
                                    @if($item->verified_at)
                                        {{ \Carbon\Carbon::parse($item->verified_at)->diffForHumans() }}
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Footer: Creator --}}
                        <div class="card-footer-row">
                            <div class="creator-info">
                                <div class="creator-avatar">{{ $creatorInitR }}</div>
                                <div>
                                    <div class="creator-name">&#x1F464; {{ $creatorNameR }}</div>
                                    <div class="creator-time">Diinput {{ $item->created_at->diffForHumans() }}</div>
                                </div>
                            </div>
                            <div class="action-group">
                                <a href="{{ route('fenomena.show', [$item->id, 'from' => 'verification']) }}" class="btn-detail">
                                    <i class="fas fa-eye"></i> Detail
                                </a>
                            </div>
                        </div>

                    </div>
                @empty
                    <div class="empty-state">
                        <div class="empty-state-icon"><i class="fas fa-history"></i></div>
                        <h5>Belum Ada Riwayat</h5>
                        <p>Belum ada fenomena yang sudah diverifikasi atau ditolak.</p>
                    </div>
                @endforelse
            </div>

            {{-- Pagination riwayat --}}
            @if($riwayat->hasPages())
                <div class="vf-pagination">
                    @if($riwayat->onFirstPage())
                        <span class="page-link disabled"><i class="fas fa-chevron-left"></i></span>
                    @else
                        <a class="page-link" href="{{ $riwayat->previousPageUrl() }}"><i class="fas fa-chevron-left"></i></a>
                    @endif
                    @foreach($riwayat->getUrlRange(max(1, $riwayat->currentPage() - 2), min($riwayat->lastPage(), $riwayat->currentPage() + 2)) as $page => $url)
                        @if($page == $riwayat->currentPage())
                            <span class="page-link active">{{ $page }}</span>
                        @else
                            <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                        @endif
                    @endforeach
                    @if($riwayat->hasMorePages())
                        <a class="page-link" href="{{ $riwayat->nextPageUrl() }}"><i class="fas fa-chevron-right"></i></a>
                    @else
                        <span class="page-link disabled"><i class="fas fa-chevron-right"></i></span>
                    @endif
                </div>
            @endif

        @endif {{-- end tab --}}

    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('filter-form');
            const selects = form.querySelectorAll('select');

            // Auto-submit on dropdown change (except date type — user may want to set dates first)
            selects.forEach(sel => {
                sel.addEventListener('change', () => form.submit());
            });

            // Submit on Enter in search box
            document.getElementById('filter-search').addEventListener('keydown', function (e) {
                if (e.key === 'Enter') { e.preventDefault(); form.submit(); }
            });
        });
    </script>
@endpush