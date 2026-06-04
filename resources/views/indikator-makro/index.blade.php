@extends('layouts.admin')

@section('title', 'Indikator Makro')

@push('styles')
    <style>
        :root {
            --fi-primary: #f58220;
            --fi-primary-lt: #fff4eb;
            --fi-border: #e2e8f0;
            --fi-surface: #f8fafc;
            --fi-text: #0f172a;
            --fi-muted: #64748b;
        }

        .fi-page {
            font-family: 'Inter', sans-serif;
            color: var(--fi-text);
        }

        .fi-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .fi-title {
            font-size: 1.55rem;
            font-weight: 800;
            color: var(--fi-primary);
            margin: 0;
        }

        .fi-subtitle {
            font-size: .875rem;
            color: var(--fi-muted);
            margin: .25rem 0 0;
        }

        .fi-btn-action {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            height: 38px;
            padding: 0 1rem;
            border-radius: 9px;
            font-size: .83rem;
            font-weight: 600;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all .18s;
            background: var(--fi-primary);
            color: #fff;
            box-shadow: 0 2px 8px rgba(245, 130, 32, .3);
        }

        .fi-btn-action:hover {
            background: #E9861A;
            color: #fff;
        }

        .fi-empty {
            text-align: center;
            padding: 4rem 2rem;
            background: #fff;
            border: 1px solid var(--fi-border);
            border-radius: 14px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .05);
        }

        .fi-empty-icon {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            margin: 0 auto 1.25rem;
            background: var(--fi-surface);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            color: var(--fi-muted);
        }
    </style>
@endpush

@section('content')
    <div class="fi-page" style="padding: 1.5rem 0;">

        {{-- ══ HEADER ══ --}}
        <div class="fi-header">
            <div>
                <h1 class="fi-title">INDIKATOR MAKRO</h1>
                <p class="fi-subtitle">Informasi Indikator Makro Sosial Ekonomi</p>
            </div>
            @auth
                @if(auth()->user()->status === 'active' && auth()->user()->kabupaten->kode_kab === '6100')
                    <div class="fi-actions">
                        <a href="{{ route('indikator-makro.kelola') }}" class="fi-btn-action">
                            <i class="fas fa-cog fa-spin"></i> Kelola Indikator
                        </a>
                    </div>
                @endif
            @endauth
        </div>

        <div class="fi-empty">
            <div class="fi-empty-icon"><i class="fas fa-chart-area"></i></div>
            <h5 class="fw-bold mb-2">Modul Indikator Makro</h5>
            <p class="text-muted mb-0">
                Fitur ini sedang dalam pengembangan. Silakan gunakan tombol <strong>Kelola Indikator</strong> di atas untuk
                mengatur master data.
            </p>
        </div>

    </div>
@endsection