@extends('layouts.admin')

@section('title', 'Detail Fenomena — ' . Str::limit($fenomena->judul, 50))

@push('styles')
    <style>
        /* ─── CSS Variables ─────────────────────────────── */
        :root {
            --df-primary: #f58220;
            --df-primary-lt: #fff4eb;
            --df-secondary: #64748b;
            --df-success: #16a34a;
            --df-success-lt: #f0fdf4;
            --df-danger: #dc2626;
            --df-danger-lt: #fef2f2;
            --df-warning: #d97706;
            --df-warning-lt: #fffbeb;
            --df-border: #e2e8f0;
            --df-surface: #f8fafc;
            --df-text: #0f172a;
            --df-muted: #64748b;
            --df-radius: 14px;
            --df-shadow: 0 1px 4px rgba(0, 0, 0, .06), 0 4px 16px rgba(0, 0, 0, .06);
        }

        .df-page {
            font-family: 'Inter', sans-serif;
            color: var(--df-text);
        }

        /* ─── Back button ───────────────────────────────── */
        .df-back-btn {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            padding: .45rem 1rem;
            border-radius: 9px;
            font-size: .85rem;
            font-weight: 500;
            border: 1px solid var(--df-border);
            background: #fff;
            color: var(--df-secondary);
            text-decoration: none;
            transition: all .2s;
            margin-bottom: 1.5rem;
        }

        .df-back-btn:hover {
            background: var(--df-surface);
            color: var(--df-text);
            border-color: #cbd5e1;
        }

        /* ─── Meta header strip ─────────────────────────── */
        .df-meta-strip {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: .5rem .75rem;
            margin-bottom: 1.25rem;
        }

        /* ─── Source / Status badges ────────────────────── */
        .badge-source {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            padding: .3rem .8rem;
            border-radius: 8px;
            font-size: .8rem;
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

        .badge-status-pill {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            padding: .3rem .85rem;
            border-radius: 50px;
            font-size: .8rem;
            font-weight: 600;
            line-height: 1;
        }

        .badge-status-pill.menunggu {
            background: var(--df-warning-lt);
            color: var(--df-warning);
            border: 1px solid #fcd34d;
        }

        .badge-status-pill.diverifikasi {
            background: var(--df-success-lt);
            color: var(--df-success);
            border: 1px solid #86efac;
        }

        .badge-status-pill.ditolak {
            background: var(--df-danger-lt);
            color: var(--df-danger);
            border: 1px solid #fca5a5;
        }

        /* ─── Main card ─────────────────────────────────── */
        .df-card {
            background: #fff;
            border: 1px solid var(--df-border);
            border-radius: var(--df-radius);
            box-shadow: var(--df-shadow);
            padding: 2rem 2.25rem;
            margin-bottom: 1.25rem;
            position: relative;
            overflow: hidden;
        }

        .df-card::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background: var(--df-primary);
            border-radius: 4px 0 0 4px;
        }

        .df-judul {
            font-size: 1.45rem;
            font-weight: 800;
            line-height: 1.3;
            color: var(--df-text);
            margin-bottom: .75rem;
        }

        .df-penjelasan {
            font-size: .95rem;
            color: #334155;
            line-height: 1.75;
            margin-bottom: 0;
        }

        /* ─── Info grid ─────────────────────────────────── */
        .df-info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            margin-bottom: 1.25rem;
        }

        @media (max-width: 640px) {
            .df-info-grid {
                grid-template-columns: 1fr;
            }

            .df-card {
                padding: 1.25rem 1rem;
            }

            .df-judul {
                font-size: 1.15rem;
            }
        }

        .df-info-box {
            background: var(--df-surface);
            border: 1px solid var(--df-border);
            border-radius: 10px;
            padding: 1rem 1.1rem;
        }

        .df-info-label {
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .6px;
            color: var(--df-muted);
            margin-bottom: .4rem;
            display: flex;
            align-items: center;
            gap: .35rem;
        }

        .df-info-value {
            font-size: .92rem;
            font-weight: 600;
            color: var(--df-text);
            word-break: break-word;
        }

        .df-info-value a {
            color: var(--df-primary);
            text-decoration: none;
            word-break: break-all;
        }

        .df-info-value a:hover {
            text-decoration: underline;
        }

        /* ─── Tag lists ─────────────────────────────────── */
        .tag-list {
            display: flex;
            flex-wrap: wrap;
            gap: .4rem;
            margin-top: .35rem;
        }

        .tag-item {
            display: inline-flex;
            align-items: center;
            gap: .3rem;
            padding: .22rem .65rem;
            border-radius: 6px;
            font-size: .8rem;
            font-weight: 600;
            background: #f1f5f9;
            color: #475569;
            border: 1px solid var(--df-border);
        }

        .tag-item.indikator-utama {
            background: var(--df-primary-lt);
            color: var(--df-primary);
            border-color: #fed7aa;
        }

        .tag-item.indikator-dampak {
            background: #eff6ff;
            color: #1d4ed8;
            border-color: #bfdbfe;
        }

        /* ─── Verifier / Creator section ────────────────── */
        .df-people-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            margin-bottom: 1.25rem;
        }

        @media (max-width: 640px) {
            .df-people-grid {
                grid-template-columns: 1fr;
            }
        }

        .df-person-card {
            display: flex;
            align-items: center;
            gap: .75rem;
            padding: 1rem 1.1rem;
            background: #fff;
            border: 1px solid var(--df-border);
            border-radius: 10px;
            box-shadow: var(--df-shadow);
        }

        .df-person-card.verified-card {
            background: var(--df-success-lt);
            border-color: #86efac;
        }

        .df-person-card.rejected-card {
            background: var(--df-danger-lt);
            border-color: #fca5a5;
        }

        .df-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .82rem;
            font-weight: 700;
            color: #fff;
            flex-shrink: 0;
        }

        .df-avatar.creator {
            background: linear-gradient(135deg, #667eea, #764ba2);
        }

        .df-avatar.verifier-ok {
            background: linear-gradient(135deg, #16a34a, #15803d);
        }

        .df-avatar.verifier-no {
            background: linear-gradient(135deg, #dc2626, #b91c1c);
        }

        .df-person-label {
            font-size: .7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .5px;
            color: var(--df-muted);
            margin-bottom: .1rem;
        }

        .df-person-name {
            font-size: .9rem;
            font-weight: 700;
            color: var(--df-text);
        }

        .df-person-time {
            font-size: .78rem;
            color: var(--df-muted);
        }

        /* ─── Action bar ────────────────────────────────── */
        .df-action-bar {
            display: flex;
            gap: .65rem;
            flex-wrap: wrap;
            padding-top: 1rem;
            border-top: 1px solid #f1f5f9;
        }

        .df-btn {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            height: 38px;
            padding: 0 1.15rem;
            border-radius: 9px;
            font-size: .85rem;
            font-weight: 600;
            text-decoration: none;
            transition: all .18s;
            cursor: pointer;
            border: none;
        }

        .df-btn-primary {
            background: var(--df-primary);
            color: #fff;
            box-shadow: 0 2px 10px rgba(245, 130, 32, .3);
        }

        .df-btn-primary:hover {
            background: #e0700f;
            color: #fff;
        }

        .df-btn-outline {
            background: #fff;
            color: var(--df-secondary);
            border: 1px solid var(--df-border);
        }

        .df-btn-outline:hover {
            background: var(--df-surface);
            color: var(--df-text);
        }
    </style>
@endpush

@section('content')
    @php
        /* ── Source styling ── */
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

        /* ── Status ── */
        $status = $fenomena->status_verifikasi;
        [$statusLabel, $statusClass, $statusIcon] = match ($status) {
            'Y' => ['Diverifikasi', 'diverifikasi', 'fas fa-check-circle'],
            'T' => ['Ditolak', 'ditolak', 'fas fa-times-circle'],
            default => ['Menunggu Verifikasi', 'menunggu', 'fas fa-hourglass-half'],
        };

        /* ── People ── */
        $creatorName = $fenomena->creator->name ?? 'Sistem';
        $creatorInit = collect(explode(' ', $creatorName))->take(2)->map(fn($w) => strtoupper(substr($w, 0, 1)))->implode('');
        $verifierName = $fenomena->verifier->name ?? null;
        $verifierInit = $verifierName
            ? collect(explode(' ', $verifierName))->take(2)->map(fn($w) => strtoupper(substr($w, 0, 1)))->implode('')
            : null;

        /* ── Back URL ── */
        $backUrl = match ($from) {
            'verification' => route('fenomena.verification.index', ['tab' => request('tab', 'riwayat')]),
            'dashboard' => route('dashboard'),
            default => route('fenomena.index'),
        };
        $backLabel = match ($from) {
            'verification' => request('tab') === 'pending' ? 'Kembali ke Antrean' : 'Kembali ke Riwayat',
            'dashboard' => 'Kembali ke Dashboard',
            default => 'Kembali ke Daftar Fenomena',
        };
    @endphp

    <div class="df-page fade-in-up" style="padding: 1.5rem 0;">

        {{-- Back button --}}
        <a href="{{ $backUrl }}" class="df-back-btn">
            <i class="fas fa-arrow-left"></i> {{ $backLabel }}
        </a>

        {{-- ══ Meta strip ══ --}}
        <div class="df-meta-strip">
            <span class="badge-source {{ $sumberClass }}">{{ $sumberEmoji }} {{ $sumberNama }}</span>
            <span class="badge-status-pill {{ $statusClass }}">
                <i class="{{ $statusIcon }}"></i> {{ $statusLabel }}
            </span>
            @foreach($fenomena->jenisFenomenas as $jenis)
                <span
                    style="display:inline-flex;align-items:center;gap:.3rem;padding:.28rem .7rem;border-radius:6px;font-size:.78rem;font-weight:600;background:#faf5ff;color:#7c3aed;border:1px solid #ddd6fe;">
                    🏷️ {{ $jenis->nama }}
                </span>
            @endforeach
        </div>

        {{-- ══ Main Content Card ══ --}}
        <div class="df-card">
            <h1 class="df-judul">{{ $fenomena->judul }}</h1>
            <p class="df-penjelasan">{{ $fenomena->penjelasan }}</p>
        </div>

        {{-- ══ Info Grid ══ --}}
        <div class="df-info-grid">

            {{-- Tanggal Berita --}}
            <div class="df-info-box">
                <div class="df-info-label"><i class="fas fa-calendar-alt"></i> Tanggal Berita</div>
                <div class="df-info-value">
                    {{ \Carbon\Carbon::parse($fenomena->tanggal_berita)->translatedFormat('d F Y') }}
                </div>
            </div>

            {{-- Sumber Berita --}}
            <div class="df-info-box">
                <div class="df-info-label"><i class="fas fa-newspaper"></i> Sumber Berita</div>
                <div class="df-info-value">{{ $sumberNama }}</div>
            </div>

            {{-- Link Berita --}}
            @if($fenomena->link_berita)
                <div class="df-info-box" style="grid-column: 1 / -1;">
                    <div class="df-info-label"><i class="fas fa-link"></i> Link Berita</div>
                    <div class="df-info-value">
                        <a href="{{ $fenomena->link_berita }}" target="_blank" rel="noopener">
                            <i class="fas fa-external-link-alt" style="font-size:.8rem;"></i>
                            {{ $fenomena->link_berita }}
                        </a>
                    </div>
                </div>
            @endif

            {{-- Sektor Usaha --}}
            <div class="df-info-box">
                <div class="df-info-label"><i class="fas fa-building"></i> Sektor Usaha</div>
                <div class="tag-list">
                    @forelse($fenomena->sektors as $sek)
                        <span class="tag-item">🏢 [{{ $sek->kode }}] {{ $sek->nama }}</span>
                    @empty
                        <span style="font-size:.85rem;color:var(--df-muted);">Tidak ada</span>
                    @endforelse
                </div>
            </div>

            {{-- Indikator --}}
            <div class="df-info-box">
                <div class="df-info-label"><i class="fas fa-chart-bar"></i> Indikator</div>
                <div class="tag-list">
                    @forelse($fenomena->indikators as $ind)
                        @php
                            $indClass = $ind->kelompok === 'utama' ? 'indikator-utama' : 'indikator-dampak';
                            $indLabel = $ind->kelompok === 'utama' ? '🎯' : '📊';
                            $arahPivot = $ind->pivot->arah ?? null;
                            $arahLabel = match ($arahPivot) {
                                'naik' => '↑ Naik',
                                'turun' => '↓ Turun',
                                'tetap' => '→ Tetap',
                                default => null,
                            };
                        @endphp
                        <span class="tag-item {{ $indClass }}">
                            {{ $indLabel }} [{{ $ind->kode }}] {{ $ind->nama }}
                            @if($arahLabel)
                                <span style="font-weight:700;margin-left:.3rem;">· {{ $arahLabel }}</span>
                            @endif
                        </span>
                    @empty
                        <span style="font-size:.85rem;color:var(--df-muted);">Tidak ada</span>
                    @endforelse
                </div>
            </div>

        </div>

        {{-- ══ People: Penginput & Verifikator ══ --}}
        <div class="df-people-grid">

            {{-- Creator --}}
            <div class="df-person-card">
                <div class="df-avatar creator">{{ $creatorInit }}</div>
                <div>
                    <div class="df-person-label">👤 Diinput oleh</div>
                    <div class="df-person-name">{{ $creatorName }}</div>
                    <div class="df-person-time">
                        {{ $fenomena->created_at->translatedFormat('d F Y, H:i') }}
                        <span style="color:#94a3b8"> · {{ $fenomena->created_at->diffForHumans() }}</span>
                    </div>
                </div>
            </div>

            {{-- Verifier --}}
            @if($verifierName)
                @php $isVerified = $status === 'Y'; @endphp
                <div class="df-person-card {{ $isVerified ? 'verified-card' : 'rejected-card' }}">
                    <div class="df-avatar {{ $isVerified ? 'verifier-ok' : 'verifier-no' }}">{{ $verifierInit }}</div>
                    <div>
                        <div class="df-person-label">{{ $isVerified ? '✅ Diverifikasi oleh' : '❌ Ditolak oleh' }}</div>
                        <div class="df-person-name">{{ $verifierName }}</div>
                        @if($fenomena->verified_at)
                            <div class="df-person-time">
                                {{ \Carbon\Carbon::parse($fenomena->verified_at)->translatedFormat('d F Y, H:i') }}
                                <span style="color:#94a3b8"> ·
                                    {{ \Carbon\Carbon::parse($fenomena->verified_at)->diffForHumans() }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            @else
                <div class="df-person-card" style="opacity:.6;">
                    <div class="df-avatar" style="background:#cbd5e1;">?</div>
                    <div>
                        <div class="df-person-label">⏳ Status Verifikasi</div>
                        <div class="df-person-name">Menunggu verifikasi</div>
                        <div class="df-person-time">Belum diproses</div>
                    </div>
                </div>
            @endif

        </div>

        {{-- ══ Action Bar ══ --}}
        <div class="df-action-bar">
            <a href="{{ $backUrl }}" class="df-btn df-btn-outline">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>

            {{-- Tombol Verifikasi hanya tampil jika dari verification dan masih pending --}}
            @if($from === 'verification' && $status === 'P')
                <a href="{{ route('fenomena.verification.show', $fenomena->id) }}" class="df-btn df-btn-primary">
                    <i class="fas fa-check-circle"></i> Lanjutkan Verifikasi
                </a>
            @endif

            {{-- Link Berita --}}
            @if($fenomena->link_berita)
                <a href="{{ $fenomena->link_berita }}" target="_blank" rel="noopener" class="df-btn df-btn-outline">
                    <i class="fas fa-external-link-alt"></i> Buka Sumber Berita
                </a>
            @endif
        </div>

    </div>
@endsection