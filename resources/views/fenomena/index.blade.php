@extends('layouts.admin')

@section('title', 'Fenomena Terverifikasi')

@push('styles')
    <style>
        /* ─── Variables ─────────────────────────────────── */
        :root {
            --fi-primary:    #f58220;
            --fi-primary-lt: #fff4eb;
            --fi-success:    #16a34a;
            --fi-success-lt: #f0fdf4;
            --fi-border:     #e2e8f0;
            --fi-surface:    #f8fafc;
            --fi-text:       #0f172a;
            --fi-muted:      #64748b;
            --fi-radius:     14px;
            --fi-shadow:     0 1px 4px rgba(0,0,0,.05), 0 4px 16px rgba(0,0,0,.06);
        }

        .fi-page { font-family: 'Inter', sans-serif; color: var(--fi-text); }

        /* ─── Header ────────────────────────────────────── */
        .fi-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .fi-title  { font-size: 1.55rem; font-weight: 800; color: var(--fi-primary); margin: 0; }
        .fi-subtitle { font-size: .875rem; color: var(--fi-muted); margin: .25rem 0 0; }
        .fi-actions { display: flex; gap: .5rem; flex-wrap: wrap; }

        .fi-btn-action {
            display: inline-flex; align-items: center; gap: .4rem;
            height: 38px; padding: 0 1rem; border-radius: 9px;
            font-size: .83rem; font-weight: 600; text-decoration: none; border: none; cursor: pointer;
            transition: all .18s;
        }
        .fi-btn-orange { background: var(--fi-primary); color: #fff; box-shadow: 0 2px 8px rgba(245,130,32,.3); }
        .fi-btn-orange:hover { background: #E9861A; color: #fff; }
        .fi-btn-blue   { background: #1C8BC3; color: #fff; box-shadow: 0 2px 8px rgba(30,64,175,.25); }
        .fi-btn-blue:hover { background: #6DBB2E; color: #fff; }

        /* ─── Stat strip ────────────────────────────────── */
        .fi-stat-strip {
            display: flex;
            gap: .75rem;
            margin-bottom: 1.25rem;
            flex-wrap: wrap;
        }
        .fi-stat {
            display: flex; align-items: center; gap: .65rem;
            padding: .75rem 1.1rem;
            background: #fff;
            border: 1px solid var(--fi-border);
            border-radius: 12px;
            box-shadow: var(--fi-shadow);
            min-width: 160px;
        }
        .fi-stat-icon {
            width: 38px; height: 38px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: .95rem;
            background: var(--fi-success-lt); color: var(--fi-success);
        }
        .fi-stat-value { font-size: 1.35rem; font-weight: 800; color: var(--fi-text); line-height: 1; }
        .fi-stat-label { font-size: .75rem; color: var(--fi-muted); margin-top: .15rem; }

        /* ─── Filter Bar ────────────────────────────────── */
        .fi-filter-bar {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: .55rem;
            padding: .9rem 1.1rem;
            background: #fff;
            border: 1px solid var(--fi-border);
            border-radius: 12px;
            box-shadow: var(--fi-shadow);
            margin-bottom: 1.25rem;
        }
        .fi-search-wrap {
            position: relative; flex: 1; min-width: 200px;
        }
        .fi-search-wrap i {
            position: absolute; left: .8rem; top: 50%; transform: translateY(-50%);
            color: var(--fi-muted); font-size: .85rem; pointer-events: none;
        }
        .fi-search-wrap input {
            width: 100%; height: 38px; padding: 0 .85rem 0 2.2rem;
            border: 1px solid var(--fi-border); border-radius: 9px;
            font-size: .875rem; color: var(--fi-text); outline: none;
            transition: border-color .18s;
        }
        .fi-search-wrap input:focus { border-color: var(--fi-primary); }

        .fi-select {
            height: 38px; padding: 0 .8rem;
            border: 1px solid var(--fi-border); border-radius: 9px;
            font-size: .82rem; color: var(--fi-text); background: #fff;
            outline: none; cursor: pointer;
            transition: border-color .18s;
        }
        .fi-select:focus { border-color: var(--fi-primary); }

        .fi-btn-submit {
            height: 38px; padding: 0 1rem; border-radius: 9px;
            font-size: .83rem; font-weight: 600; border: none; cursor: pointer;
            background: var(--fi-primary); color: #fff;
            display: inline-flex; align-items: center; gap: .4rem;
            transition: background .18s;
        }
        .fi-btn-submit:hover { background: #E9861A; }

        .fi-btn-reset {
            height: 38px; padding: 0 .9rem; border-radius: 9px;
            font-size: .83rem; font-weight: 600;
            border: 1px solid var(--fi-border); background: #fff;
            color: var(--fi-muted); text-decoration: none;
            display: inline-flex; align-items: center; gap: .35rem;
            transition: all .18s;
        }
        .fi-btn-reset:hover { background: var(--fi-surface); color: var(--fi-text); }

        /* ─── Results bar ───────────────────────────────── */
        .fi-results-bar {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: .75rem; flex-wrap: wrap; gap: .4rem;
        }
        .fi-results-count { font-size: .85rem; color: var(--fi-muted); font-weight: 500; }
        .fi-results-count strong { color: var(--fi-text); }

        /* ─── Card Grid ─────────────────────────────────── */
        .fi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        /* ─── Fenomena Card ─────────────────────────────── */
        .fi-card {
            background: #fff;
            border: 1px solid var(--fi-border);
            border-radius: var(--fi-radius);
            box-shadow: var(--fi-shadow);
            padding: 1.25rem 1.35rem;
            display: flex; flex-direction: column; gap: .75rem;
            position: relative; overflow: hidden;
            transition: box-shadow .2s, transform .2s;
        }
        .fi-card::before {
            content: '';
            position: absolute; left: 0; top: 0; bottom: 0; width: 4px;
            background: var(--fi-success);
            border-radius: 4px 0 0 4px;
        }
        .fi-card:hover {
            box-shadow: 0 4px 20px rgba(0,0,0,.1);
            transform: translateY(-2px);
        }

        /* Card top row */
        .fi-card-top { display: flex; align-items: center; justify-content: space-between; gap: .5rem; }

        .badge-source {
            display: inline-flex; align-items: center; gap: .3rem;
            padding: .25rem .7rem; border-radius: 7px; font-size: .76rem; font-weight: 600;
        }
        .badge-source.berita-online { background:#eff6ff; color:#1d4ed8; border:1px solid #bfdbfe; }
        .badge-source.koran         { background:#faf5ff; color:#7c3aed; border:1px solid #ddd6fe; }
        .badge-source.media-sosial  { background:#f0fdf4; color:#15803d; border:1px solid #bbf7d0; }
        .badge-source.laporan       { background:#fff7ed; color:#c2410c; border:1px solid #fed7aa; }

        .badge-verified {
            display: inline-flex; align-items: center; gap: .3rem;
            padding: .22rem .65rem; border-radius: 50px; font-size: .73rem; font-weight: 700;
            background: var(--fi-success-lt); color: var(--fi-success); border: 1px solid #86efac;
        }

        /* Title & preview */
        .fi-card-title {
            font-size: .97rem; font-weight: 700; color: var(--fi-text);
            line-height: 1.4; margin: 0;
            display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
        }
        .fi-card-preview {
            font-size: .83rem; color: #475569; line-height: 1.6; margin: 0;
            display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;
        }

        /* Meta row */
        .fi-card-meta { display: flex; flex-wrap: wrap; gap: .35rem; }
        .fi-meta-item { display: inline-flex; align-items: center; gap: .28rem; font-size: .77rem; color: var(--fi-muted); }
        .fi-tag {
            display: inline-flex; align-items: center; gap: .25rem;
            padding: .18rem .55rem; border-radius: 5px; font-size: .74rem; font-weight: 600;
            background: var(--fi-surface); color: #475569; border: 1px solid var(--fi-border);
        }

        /* Card footer */
        .fi-card-footer { display: flex; align-items: center; justify-content: space-between; gap: .5rem; margin-top: auto; }

        .fi-verifier { display: flex; align-items: center; gap: .5rem; }
        .fi-verifier-avatar {
            width: 28px; height: 28px; border-radius: 50%;
            background: linear-gradient(135deg, #16a34a, #15803d);
            display: flex; align-items: center; justify-content: center;
            font-size: .68rem; font-weight: 700; color: #fff; flex-shrink: 0;
        }
        .fi-verifier-name  { font-size: .8rem; font-weight: 600; color: var(--fi-text); line-height: 1.2; }
        .fi-verifier-label { font-size: .71rem; color: var(--fi-muted); }

        .fi-btn-detail {
            display: inline-flex; align-items: center; gap: .35rem;
            height: 32px; padding: 0 .85rem; border-radius: 8px;
            font-size: .8rem; font-weight: 600; text-decoration: none;
            background: var(--fi-primary-lt); color: var(--fi-primary);
            border: 1px solid #fed7aa;
            transition: all .18s; white-space: nowrap;
        }
        .fi-btn-detail:hover { background: var(--fi-primary); color: #fff; border-color: var(--fi-primary); }

        /* ─── Empty State ───────────────────────────────── */
        .fi-empty {
            text-align: center; padding: 4rem 2rem;
            background: #fff; border: 1px solid var(--fi-border);
            border-radius: var(--fi-radius); box-shadow: var(--fi-shadow);
        }
        .fi-empty-icon {
            width: 72px; height: 72px; border-radius: 50%; margin: 0 auto 1.25rem;
            background: var(--fi-surface);
            display: flex; align-items: center; justify-content: center;
            font-size: 1.75rem; color: var(--fi-muted);
        }

        /* ─── Pagination ────────────────────────────────── */
        .fi-pagination { display: flex; justify-content: center; gap: .35rem; flex-wrap: wrap; margin-top: 1.5rem; }
        .fi-pagination .page-link {
            display: inline-flex; align-items: center; justify-content: center;
            min-width: 36px; height: 36px; padding: 0 .6rem; border-radius: 9px;
            font-size: .82rem; font-weight: 500;
            border: 1px solid var(--fi-border); background: #fff; color: var(--fi-muted);
            text-decoration: none; transition: all .18s;
        }
        .fi-pagination .page-link:hover { background: var(--fi-surface); color: var(--fi-text); border-color: #cbd5e1; }
        .fi-pagination .page-link.active { background: var(--fi-primary); border-color: var(--fi-primary); color: #fff; box-shadow: 0 2px 8px rgba(245,130,32,.3); }
        .fi-pagination .page-link.disabled { opacity: .45; pointer-events: none; }

        @media (max-width: 768px) {
            .fi-grid { grid-template-columns: 1fr; }
            .fi-title { font-size: 1.25rem; }
        }
    </style>
@endpush

@section('content')
    <div class="fi-page fade-in-up" style="padding: 1.5rem 0;">

        {{-- ══ HEADER ══ --}}
        <div class="fi-header">
            <div>
                <h1 class="fi-title">INFORMASI FENOMENA</h1>
                <p class="fi-subtitle">Fenomena sosial ekonomi yang telah diverifikasi</p>
            </div>
            @auth
                @if(auth()->user()->status === 'active')
                    <div class="fi-actions">
                        @if(auth()->user()->kabupaten->kode_kab === '6100')
                            <a href="{{ route('fenomena.kelola') }}" class="fi-btn-action fi-btn-blue">
                                <i class="fas fa-cog fa-spin"></i> Kelola
                            </a>
                        @endif
                        <a href="{{ route('fenomena.create') }}" class="fi-btn-action fi-btn-orange">
                            <i class="fas fa-plus"></i> Input Fenomena
                        </a>
                    </div>
                @endif
            @endauth
        </div>

        {{-- ══ STAT STRIP ══ --}}
        <div class="fi-stat-strip">
            <div class="fi-stat">
                <div class="fi-stat-icon"><i class="fas fa-check-double"></i></div>
                <div>
                    <div class="fi-stat-value">{{ $totalVerified }}</div>
                    <div class="fi-stat-label">Total Terverifikasi</div>
                </div>
            </div>
            @auth
                <a href="{{ route('fenomena.index', ['creator' => request('creator') === 'me' ? '' : 'me']) }}"
                   class="fi-stat text-decoration-none"
                   style="{{ request('creator') === 'me' ? 'background:var(--fi-primary-lt);border-color:#fed7aa;cursor:pointer;' : 'cursor:pointer;' }}">
                    <div class="fi-stat-icon" style="background:{{ request('creator') === 'me' ? 'var(--fi-primary-lt)' : '#eff6ff' }};color:{{ request('creator') === 'me' ? 'var(--fi-primary)' : '#1d4ed8' }};">
                        <i class="fas fa-user"></i>
                    </div>
                    <div>
                        <div class="fi-stat-value" style="color:{{ request('creator') === 'me' ? 'var(--fi-primary)' : 'var(--fi-text)' }};">{{ $myCount }}</div>
                        <div class="fi-stat-label">
                            Input Anda
                            @if(request('creator') === 'me')
                                &nbsp;<span style="font-size:.68rem;color:var(--fi-primary);font-weight:700;">● Aktif</span>
                            @endif
                        </div>
                    </div>
                </a>
            @endauth
            @if(request()->hasAny(['search','sektor','indikator','sumber','tahun','bulan','creator']))
                <div class="fi-stat" style="background:var(--fi-primary-lt);border-color:#fed7aa;">
                    <div class="fi-stat-icon" style="background:var(--fi-primary-lt);color:var(--fi-primary);">
                        <i class="fas fa-filter"></i>
                    </div>
                    <div>
                        <div class="fi-stat-value" style="color:var(--fi-primary);">{{ $totalFiltered }}</div>
                        <div class="fi-stat-label">Hasil Filter Aktif</div>
                    </div>
                </div>
            @endif
        </div>

        {{-- ══ FILTER BAR ══ --}}
        <form method="GET" action="{{ route('fenomena.index') }}" class="fi-filter-bar" id="fi-filter-form">

            {{-- Search --}}
            <div class="fi-search-wrap">
                <i class="fas fa-search"></i>
                <input type="text" name="search" placeholder="Cari judul atau isi fenomena..."
                       autocomplete="off" value="{{ request('search') }}" id="fi-search">
            </div>

            {{-- Sektor --}}
            <select class="fi-select" name="sektor" id="fi-sektor" style="min-width:135px;">
                <option value="">🏢 Semua Sektor</option>
                @foreach($sektors as $sek)
                    <option value="{{ $sek->kode }}" @selected(request('sektor') == $sek->kode)>
                        [{{ $sek->kode }}] {{ $sek->nama }}
                    </option>
                @endforeach
            </select>

            {{-- Indikator --}}
            <select class="fi-select" name="indikator" id="fi-indikator" style="min-width:145px;">
                <option value="">📊 Semua Indikator</option>
                @foreach($indikators as $ind)
                    <option value="{{ $ind->kode }}" @selected(request('indikator') == $ind->kode)>
                        {{ $ind->nama }}
                    </option>
                @endforeach
            </select>

            {{-- Sumber --}}
            <select class="fi-select" name="sumber" id="fi-sumber" style="min-width:145px;">
                <option value="">📰 Semua Sumber</option>
                @foreach($sumberBeritas as $sb)
                    <option value="{{ $sb->id }}" @selected(request('sumber') == $sb->id)>
                        {{ $sb->nama }}
                    </option>
                @endforeach
            </select>

            {{-- Tahun --}}
            <select class="fi-select" name="tahun" id="fi-tahun" style="min-width:110px;">
                <option value="">📅 Semua Tahun</option>
                @foreach($availableTahun as $thn)
                    <option value="{{ $thn }}" @selected(request('tahun') == $thn)>{{ $thn }}</option>
                @endforeach
            </select>

            {{-- Bulan --}}
            <select class="fi-select" name="bulan" id="fi-bulan" style="min-width:120px;">
                <option value="">🗓️ Semua Bulan</option>
                @foreach([
                        1 => 'Januari',
                        2 => 'Februari',
                        3 => 'Maret',
                        4 => 'April',
                        5 => 'Mei',
                        6 => 'Juni',
                        7 => 'Juli',
                        8 => 'Agustus',
                        9 => 'September',
                        10 => 'Oktober',
                        11 => 'November',
                        12 => 'Desember'
                    ] as $num => $nama)
                        <option value="{{ $num }}" @selected(request('bulan') == $num)>{{ $nama }}</option>
                @endforeach
            </select>

            {{-- ── Filter Penginput (hanya jika login) ────── --}}
            @auth
                <select class="fi-select" name="creator" id="fi-creator" style="min-width:170px;"
                        title="Filter berdasarkan penginput">
                    <option value="">👥 Semua Orang</option>
                    <option value="me" @selected(request('creator') === 'me')>
                        👤 Diinput oleh Anda
                    </option>
                </select>
            @endauth

            <button type="submit" class="fi-btn-submit">
                <i class="fas fa-search"></i> Cari
            </button>
            <a href="{{ route('fenomena.index') }}" class="fi-btn-reset">
                <i class="fas fa-undo-alt"></i> Reset
            </a>
        </form>

        {{-- ══ RESULTS BAR ══ --}}
        <div class="fi-results-bar">
            <div class="fi-results-count">
                Menampilkan <strong>{{ $fenomenas->firstItem() ?? 0 }}–{{ $fenomenas->lastItem() ?? 0 }}</strong>
                dari <strong>{{ $totalFiltered }}</strong> fenomena terverifikasi
                @if(request()->hasAny(['search','sektor','indikator','sumber','tahun','bulan','creator']))
                    <span style="color:#d97706;font-size:.78rem;margin-left:.4rem;">
                        <i class="fas fa-filter"></i> Filter aktif
                    </span>
                @endif
            </div>
            @if($fenomenas->lastPage() > 1)
                <div style="font-size:.8rem;color:var(--fi-muted);">
                    Halaman {{ $fenomenas->currentPage() }} / {{ $fenomenas->lastPage() }}
                </div>
            @endif
        </div>

        {{-- ══ SESSION ALERT ══ --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-3 mb-3" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- ══ CARD GRID ══ --}}
        @if($fenomenas->isEmpty())
            <div class="fi-empty">
                <div class="fi-empty-icon"><i class="fas fa-newspaper"></i></div>
                <h5 class="fw-bold mb-2">Belum Ada Fenomena Terverifikasi</h5>
                <p class="text-muted mb-0">
                    @if(request()->hasAny(['search', 'sektor', 'indikator', 'sumber', 'tahun', 'bulan']))
                        Tidak ada data yang cocok dengan filter yang dipilih.
                        <a href="{{ route('fenomena.index') }}">Reset filter</a>
                    @else
                        Belum ada fenomena yang telah diverifikasi.
                    @endif
                </p>
            </div>
        @else
            <div class="fi-grid">
                @foreach($fenomenas as $fenomena)
                    @php
                        $sumberNama = $fenomena->sumberBerita->nama ?? 'Tidak diketahui';
                        $sumberLower = strtolower($sumberNama);
                        if (str_contains($sumberLower, 'koran') || str_contains($sumberLower, 'cetak'))
                            $sumberClass = 'koran';
                        elseif (str_contains($sumberLower, 'sosial'))
                            $sumberClass = 'media-sosial';
                        elseif (str_contains($sumberLower, 'laporan'))
                            $sumberClass = 'laporan';
                        else
                            $sumberClass = 'berita-online';
                        $sumberEmoji = match ($sumberClass) {
                            'koran' => '🗞️',
                            'media-sosial' => '📱',
                            'laporan' => '📊',
                            default => '📰',
                        };
                        $verifierName = $fenomena->verifier->name ?? 'Sistem';
                        $verifierInit = collect(explode(' ', $verifierName))->take(2)->map(fn($w) => strtoupper(substr($w, 0, 1)))->implode('');
                        $utama = $fenomena->indikators->where('kelompok', 'utama')->first();
                    @endphp

                    <div class="fi-card">

                        {{-- Top row --}}
                        <div class="fi-card-top">
                            <span class="badge-source {{ $sumberClass }}">{{ $sumberEmoji }} {{ $sumberNama }}</span>
                            <span class="badge-verified"><i class="fas fa-check-circle"></i> Terverifikasi</span>
                        </div>

                        {{-- Title --}}
                        <h2 class="fi-card-title">{{ $fenomena->judul }}</h2>

                        {{-- Preview --}}
                        <p class="fi-card-preview">{{ $fenomena->penjelasan }}</p>

                        {{-- Meta --}}
                        <div class="fi-card-meta">
                            <span class="fi-meta-item">
                                <i class="fas fa-calendar-alt"></i>
                                {{ \Carbon\Carbon::parse($fenomena->tanggal_berita)->translatedFormat('d F Y') }}
                            </span>
                            @foreach($fenomena->sektors->take(2) as $sek)
                                <span class="fi-tag">🏢 [{{ $sek->kode }}] {{ Str::limit($sek->nama, 18) }}</span>
                            @endforeach
                            @if($fenomena->sektors->count() > 2)
                                <span class="fi-tag">+{{ $fenomena->sektors->count() - 2 }}</span>
                            @endif
                            @if($utama)
                                <span class="fi-tag" style="background:var(--fi-primary-lt);color:var(--fi-primary);border-color:#fed7aa;">
                                    🎯 {{ Str::limit($utama->nama, 22) }}
                                </span>
                            @endif
                            @if($fenomena->link_berita)
                                <a href="{{ $fenomena->link_berita }}" target="_blank" rel="noopener"
                                   class="fi-meta-item" style="color:var(--fi-primary); text-decoration:none;">
                                    <i class="fas fa-external-link-alt"></i> Buka Link
                                </a>
                            @endif
                        </div>

                        {{-- Footer: Verifier + Detail button --}}
                        <div class="fi-card-footer">
                            <div class="fi-verifier">
                                <div class="fi-verifier-avatar">{{ $verifierInit }}</div>
                                <div>
                                    <div class="fi-verifier-label">✅ Diverifikasi oleh</div>
                                    <div class="fi-verifier-name">{{ $verifierName }}</div>
                                </div>
                            </div>
                            <a href="{{ route('fenomena.show', [$fenomena->id, 'from' => 'fenomena']) }}"
                               class="fi-btn-detail">
                                <i class="fas fa-eye"></i> Detail
                            </a>
                        </div>

                    </div>
                @endforeach
            </div>

            {{-- ══ PAGINATION ══ --}}
            @if($fenomenas->hasPages())
                <div class="fi-pagination">
                    @if($fenomenas->onFirstPage())
                        <span class="page-link disabled"><i class="fas fa-chevron-left"></i></span>
                    @else
                        <a class="page-link" href="{{ $fenomenas->previousPageUrl() }}"><i class="fas fa-chevron-left"></i></a>
                    @endif

                    @foreach($fenomenas->getUrlRange(max(1, $fenomenas->currentPage() - 2), min($fenomenas->lastPage(), $fenomenas->currentPage() + 2)) as $page => $url)
                        @if($page == $fenomenas->currentPage())
                            <span class="page-link active">{{ $page }}</span>
                        @else
                            <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                        @endif
                    @endforeach

                    @if($fenomenas->hasMorePages())
                        <a class="page-link" href="{{ $fenomenas->nextPageUrl() }}"><i class="fas fa-chevron-right"></i></a>
                    @else
                        <span class="page-link disabled"><i class="fas fa-chevron-right"></i></span>
                    @endif
                </div>
            @endif
        @endif

    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form    = document.getElementById('fi-filter-form');
            const selects = form.querySelectorAll('select');
            selects.forEach(sel => sel.addEventListener('change', () => form.submit()));

            document.getElementById('fi-search').addEventListener('keydown', function (e) {
                if (e.key === 'Enter') { e.preventDefault(); form.submit(); }
            });
        });
    </script>
@endpush