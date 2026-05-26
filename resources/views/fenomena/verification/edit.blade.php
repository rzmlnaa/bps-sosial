@extends('layouts.admin')

@section('title', 'Edit Verifikasi Fenomena')

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        /* ══════════════════════════════════════════════
                                                   DESIGN TOKENS
                                                ══════════════════════════════════════════════ */
        :root {
            --dvf-primary: #f58220;
            --dvf-primary-lt: #eff6ff;
            --dvf-primary-ring: rgba(37, 99, 235, .18);
            --dvf-success: #16a34a;
            --dvf-success-lt: #f0fdf4;
            --dvf-success-ring: rgba(22, 163, 74, .18);
            --dvf-danger: #dc2626;
            --dvf-danger-lt: #fef2f2;
            --dvf-danger-ring: rgba(220, 38, 38, .18);
            --dvf-warning: #d97706;
            --dvf-warning-lt: #fffbeb;
            --dvf-neutral: #64748b;
            --dvf-neutral-lt: #f1f5f9;
            --dvf-border: #e2e8f0;
            --dvf-surface: #f8fafc;
            --dvf-bg: transparent;
            --dvf-text: #0f172a;
            --dvf-muted: #64748b;
            --dvf-radius: 16px;
            --dvf-radius-sm: 10px;
            --dvf-shadow: 0 1px 3px rgba(0, 0, 0, .06), 0 4px 16px rgba(0, 0, 0, .05);
            --dvf-shadow-hover: 0 6px 20px rgba(37, 99, 235, .12), 0 2px 6px rgba(0, 0, 0, .06);
            --sidebar-w: 260px;
        }

        /* ══════════════════════════════════════════════
                                                   PAGE WRAPPER
                                                ══════════════════════════════════════════════ */
        .dvf-page {
            font-family: 'Inter', sans-serif;
            background: transparent;
            min-height: 100vh;
            padding: 1.75rem 1rem 2rem;
            color: var(--dvf-text);
        }

        .dvf-wrap {
            max-width: 1080px;
            margin: 0 auto;
        }

        /* ══════════════════════════════════════════════
                                                   HEADER
                                                ══════════════════════════════════════════════ */
        .dvf-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.75rem;
        }

        .dvf-back-btn {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            height: 40px;
            padding: 0 1rem;
            border-radius: 10px;
            border: 1px solid var(--dvf-border);
            background: #fff;
            color: var(--dvf-neutral);
            font-size: .85rem;
            font-weight: 500;
            text-decoration: none;
            transition: all .2s;
            white-space: nowrap;
            box-shadow: var(--dvf-shadow);
            flex-shrink: 0;
        }

        .dvf-back-btn:hover {
            background: var(--dvf-primary-lt);
            border-color: #bfdbfe;
            color: var(--dvf-primary);
        }

        .dvf-header-text h1 {
            font-size: 1.55rem;
            font-weight: 700;
            letter-spacing: -.4px;
            color: var(--dvf-text);
            margin: 0;
            line-height: 1.2;
        }

        .dvf-header-text p {
            font-size: .875rem;
            color: var(--dvf-muted);
            margin: .2rem 0 0;
        }

        /* ══════════════════════════════════════════════
                                                   CARDS — BASE
                                                ══════════════════════════════════════════════ */
        .dvf-card {
            background: #fff;
            border: 1px solid var(--dvf-border);
            border-radius: var(--dvf-radius);
            box-shadow: var(--dvf-shadow);
            margin-bottom: 1.25rem;
            overflow: hidden;
        }

        .dvf-card-header {
            padding: 1.1rem 1.5rem;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            align-items: center;
            gap: .65rem;
        }

        .dvf-card-header-icon {
            width: 34px;
            height: 34px;
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .9rem;
            flex-shrink: 0;
        }

        .dvf-card-header-icon.blue {
            background: var(--dvf-primary-lt);
            color: var(--dvf-primary);
        }

        .dvf-card-header-icon.green {
            background: var(--dvf-success-lt);
            color: var(--dvf-success);
        }

        .dvf-card-header-icon.orange {
            background: var(--dvf-warning-lt);
            color: var(--dvf-warning);
        }

        .dvf-card-header-icon.purple {
            background: #f5f3ff;
            color: #7c3aed;
        }

        .dvf-card-header-text h2 {
            font-size: 1rem;
            font-weight: 700;
            margin: 0;
            color: var(--dvf-text);
        }

        .dvf-card-header-text p {
            font-size: .8rem;
            color: var(--dvf-muted);
            margin: 0;
        }

        .dvf-card-body {
            padding: 1.5rem;
        }

        /* ========================
         * FORM CONTROLS & SELECT2 OVERRIDES
         * ======================== */
        .form-label-custom {
            font-size: 0.82rem;
            font-weight: 700;
            letter-spacing: .5px;
            text-transform: uppercase;
            color: var(--dvf-muted);
            margin-bottom: 0.45rem;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .form-control-custom,
        .form-select-custom {
            border: 1.5px solid #d1d5db;
            border-radius: 8px;
            padding: 0.5rem 0.875rem;
            font-size: 0.875rem;
            color: #1e293b;
            background: #fff;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
            width: 100%;
            height: auto;
            min-height: 40px;
        }

        .form-control-custom:focus,
        .form-select-custom:focus {
            border-color: var(--dvf-primary);
            box-shadow: 0 0 0 3px rgba(245, 130, 32, 0.12);
            outline: none;
        }

        .select2-container--default .select2-selection--single {
            border: 1.5px solid #d1d5db !important;
            border-radius: 8px !important;
            height: 40px !important;
            display: flex;
            align-items: center;
            background: #fff !important;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .select2-container--default .select2-selection--single:focus-within,
        .select2-container--default.select2-container--open .select2-selection--single {
            border-color: var(--dvf-primary) !important;
            box-shadow: 0 0 0 3px rgba(245, 130, 32, 0.12) !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 38px !important;
            padding-left: 12px !important;
            font-size: 0.875rem;
            color: #1e293b;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 38px !important;
        }

        .select2-container--default .select2-selection--multiple {
            border: 1.5px solid #d1d5db !important;
            border-radius: 8px !important;
            min-height: 40px !important;
            background: #fff !important;
            padding: 4px 6px !important;
        }

        .select2-container--default.select2-container--open .select2-selection--multiple,
        .select2-container--default .select2-selection--multiple:focus-within {
            border-color: var(--dvf-primary) !important;
            box-shadow: 0 0 0 3px rgba(245, 130, 32, 0.12) !important;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background: var(--dvf-primary) !important;
            border: none !important;
            color: #fff !important;
            border-radius: 6px !important;
            padding: 2px 10px !important;
            font-size: 0.78rem !important;
            font-weight: 500 !important;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            color: rgba(255, 255, 255, 0.75) !important;
            margin-right: 5px !important;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
            color: #fff !important;
        }

        .select2-dropdown {
            border: 1.5px solid var(--dvf-primary) !important;
            border-radius: 10px !important;
            overflow: hidden;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1) !important;
            z-index: 9999;
        }

        .select2-results__option--highlighted {
            background-color: var(--dvf-primary) !important;
        }

        /* ══════════════════════════════════════════════
                                                   STATUS VERIFIKASI — SEGMENTED CONTROL
                                                ══════════════════════════════════════════════ */
        .dvf-status-segment {
            display: inline-flex;
            background: var(--dvf-surface);
            border: 1px solid var(--dvf-border);
            border-radius: 50px;
            padding: 4px;
            gap: 4px;
        }

        .dvf-status-segment input[type="radio"] {
            display: none;
        }

        .dvf-status-segment label {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            padding: .55rem 1.35rem;
            border-radius: 50px;
            font-size: .875rem;
            font-weight: 600;
            color: var(--dvf-muted);
            cursor: pointer;
            transition: all .22s;
            white-space: nowrap;
            user-select: none;
        }

        .dvf-status-segment label:hover {
            color: var(--dvf-text);
            background: rgba(0, 0, 0, .04);
        }

        /* Active — Setujui */
        #status_y:checked~#label_y,
        .dvf-status-segment input[id="status_y"]:checked+label {
            background: var(--dvf-success-lt);
            color: var(--dvf-success);
            box-shadow: 0 2px 8px var(--dvf-success-ring);
        }

        /* Active — Tolak */
        #status_t:checked~#label_t,
        .dvf-status-segment input[id="status_t"]:checked+label {
            background: var(--dvf-danger-lt);
            color: var(--dvf-danger);
            box-shadow: 0 2px 8px var(--dvf-danger-ring);
        }

        /* ══════════════════════════════════════════════
                                                   DIRECTION PILLS — INDIKATOR UTAMA
                                                ══════════════════════════════════════════════ */
        .dvf-dir-card {
            background: var(--dvf-surface);
            border: 1.5px solid var(--dvf-border);
            border-radius: var(--dvf-radius-sm);
            padding: 1rem 1.25rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
            transition: border-color .2s, box-shadow .2s;
        }

        .dvf-dir-card.has-selection {
            border-color: var(--dvf-primary);
            box-shadow: 0 0 0 3px var(--dvf-primary-ring);
        }

        .dvf-dir-card-label {
            display: flex;
            align-items: center;
            gap: .5rem;
            min-width: 140px;
            flex: 1;
        }

        .dvf-dir-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: var(--dvf-primary-lt);
            color: var(--dvf-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .88rem;
            flex-shrink: 0;
        }

        .dvf-dir-name {
            font-size: .875rem;
            font-weight: 700;
            color: var(--dvf-text);
            line-height: 1.3;
        }

        .dvf-dir-kode {
            font-size: .75rem;
            color: var(--dvf-muted);
        }

        .dvf-pill-group {
            display: flex;
            gap: .5rem;
            flex-wrap: wrap;
        }

        .dvf-pill-group input[type="radio"] {
            display: none;
        }

        .dvf-pill-group label {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            padding: .45rem 1.1rem;
            border-radius: 50px;
            font-size: .82rem;
            font-weight: 600;
            cursor: pointer;
            border: 1.5px solid var(--dvf-border);
            background: #fff;
            color: var(--dvf-muted);
            transition: all .2s;
            user-select: none;
            white-space: nowrap;
        }

        .dvf-pill-group label:hover {
            background: var(--dvf-surface);
            color: var(--dvf-text);
            border-color: #94a3b8;
        }

        /* Naik */
        .dvf-pill-group input[data-dir="naik"]:checked+label {
            background: var(--dvf-success-lt);
            color: var(--dvf-success);
            border-color: #86efac;
            box-shadow: 0 2px 8px var(--dvf-success-ring);
        }

        /* Turun */
        .dvf-pill-group input[data-dir="turun"]:checked+label {
            background: var(--dvf-danger-lt);
            color: var(--dvf-danger);
            border-color: #fca5a5;
            box-shadow: 0 2px 8px var(--dvf-danger-ring);
        }

        /* Tetap */
        .dvf-pill-group input[data-dir="tetap"]:checked+label {
            background: #f1f5f9;
            color: #334155;
            border-color: #94a3b8;
        }

        /* ══════════════════════════════════════════════
                                                   IMPACT CARDS GRID
                                                ══════════════════════════════════════════════ */
        .dvf-impact-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
        }

        @media (max-width: 992px) {
            .dvf-impact-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 576px) {
            .dvf-impact-grid {
                grid-template-columns: 1fr;
            }
        }

        .dvf-impact-item {
            background: var(--dvf-surface);
            border: 1.5px solid var(--dvf-border);
            border-radius: var(--dvf-radius-sm);
            padding: 1rem 1.1rem;
            transition: border-color .2s, box-shadow .2s;
        }

        .dvf-impact-item.has-selection {
            border-color: var(--dvf-primary);
            box-shadow: 0 0 0 3px var(--dvf-primary-ring);
        }

        .dvf-impact-item-header {
            display: flex;
            align-items: flex-start;
            gap: .6rem;
            margin-bottom: .85rem;
        }

        .dvf-impact-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: var(--dvf-primary-lt);
            color: var(--dvf-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .8rem;
            flex-shrink: 0;
            margin-top: 1px;
        }

        .dvf-impact-name {
            font-size: .835rem;
            font-weight: 700;
            color: var(--dvf-text);
            line-height: 1.35;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        /* Impact pill group — 2×2 compact */
        .dvf-impact-pills {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: .35rem;
        }

        .dvf-impact-pills input[type="radio"] {
            display: none;
        }

        .dvf-impact-pills label {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .3rem;
            padding: .38rem .5rem;
            border-radius: 8px;
            font-size: .76rem;
            font-weight: 600;
            cursor: pointer;
            border: 1.5px solid var(--dvf-border);
            background: #fff;
            color: var(--dvf-muted);
            text-align: center;
            transition: all .18s;
            user-select: none;
        }

        .dvf-impact-pills label:hover {
            background: var(--dvf-surface);
            color: var(--dvf-text);
            border-color: #94a3b8;
        }

        /* na — spans full width */
        .dvf-impact-pills label.na-label {
            grid-column: 1 / -1;
            font-size: .72rem;
            color: #94a3b8;
            border-style: dashed;
            border-color: #cbd5e1;
        }

        .dvf-impact-pills label.na-label:hover {
            background: var(--dvf-neutral-lt);
            color: var(--dvf-muted);
        }

        .dvf-impact-pills input[data-dir="naik"]:checked+label {
            background: var(--dvf-success-lt);
            color: var(--dvf-success);
            border-color: #86efac;
        }

        .dvf-impact-pills input[data-dir="turun"]:checked+label {
            background: var(--dvf-danger-lt);
            color: var(--dvf-danger);
            border-color: #fca5a5;
        }

        .dvf-impact-pills input[data-dir="tetap"]:checked+label {
            background: #f1f5f9;
            color: #334155;
            border-color: #94a3b8;
        }

        .dvf-impact-pills input[data-dir="na"]:checked+label {
            background: #f8fafc;
            color: #64748b;
            border-color: #94a3b8;
            border-style: solid;
        }

        /* ══════════════════════════════════════════════
                                                   ACTION BAR
                                                ══════════════════════════════════════════════ */
        .dvf-sticky-bar {
            background: #fff;
            border: 1px solid rgba(0, 0, 0, .07);
            border-radius: var(--dvf-radius);
            box-shadow: 0 2px 12px rgba(0, 0, 0, .06);
            display: flex;
            align-items: center;
            margin-top: 0.5rem;
        }

        .dvf-bar-inner {
            width: 100%;
            padding: 1rem 2.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }

        .dvf-bar-summary {
            font-size: .82rem;
            color: var(--dvf-muted);
            display: flex;
            align-items: center;
            gap: .5rem;
            flex-wrap: wrap;
        }

        .dvf-bar-summary strong {
            color: var(--dvf-text);
            font-weight: 600;
        }

        .dvf-summary-chip {
            display: inline-flex;
            align-items: center;
            gap: .3rem;
            padding: .22rem .7rem;
            border-radius: 50px;
            font-size: .76rem;
            font-weight: 600;
            background: var(--dvf-primary-lt);
            color: var(--dvf-primary);
            border: 1px solid #bfdbfe;
            transition: all .2s;
        }

        .dvf-summary-chip.neutral {
            background: #f1f5f9;
            color: var(--dvf-muted);
            border-color: #e2e8f0;
        }

        .dvf-summary-chip.success {
            background: var(--dvf-success-lt);
            color: var(--dvf-success);
            border-color: #86efac;
        }

        .dvf-summary-chip.danger {
            background: var(--dvf-danger-lt);
            color: var(--dvf-danger);
            border-color: #fca5a5;
        }

        .dvf-bar-actions {
            display: flex;
            align-items: center;
            gap: .6rem;
            flex-shrink: 0;
        }

        .dvf-btn-cancel {
            height: 42px;
            padding: 0 1.4rem;
            border-radius: 10px;
            font-size: .875rem;
            font-weight: 600;
            border: 1.5px solid var(--dvf-border);
            background: #fff;
            color: var(--dvf-muted);
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            transition: all .2s;
        }

        .dvf-btn-cancel:hover {
            background: var(--dvf-surface);
            color: var(--dvf-text);
            border-color: #94a3b8;
        }

        .dvf-btn-save {
            height: 42px;
            padding: 0 1.6rem;
            border-radius: 10px;
            font-size: .875rem;
            font-weight: 700;
            border: none;
            background: var(--dvf-primary);
            color: #fff;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            box-shadow: 0 4px 14px rgba(37, 99, 235, .35);
            transition: all .2s;
        }

        .dvf-btn-save:hover {
            background: #1d4ed8;
            box-shadow: 0 6px 20px rgba(37, 99, 235, .42);
            transform: translateY(-1px);
        }

        /* Section reveal animation */
        .dvf-section-reveal {
            overflow: hidden;
            transition: max-height .35s ease, opacity .35s ease;
        }

        /* Helper */
        @media (max-width: 576px) {
            .dvf-status-segment {
                flex-direction: column;
                border-radius: var(--dvf-radius-sm);
            }

            .dvf-status-segment label {
                padding: .55rem 1rem;
            }

            .dvf-bar-summary {
                display: none;
            }
        }

        /* ══════════════════════════════════════════════
           EDITABLE SHOW-LIKE LAYOUT SYSTEM
        ══════════════════════════════════════════════ */
        .dvf-info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            margin-bottom: 1.25rem;
        }

        @media (max-width: 640px) {
            .dvf-info-grid {
                grid-template-columns: 1fr;
            }
        }

        .dvf-info-box {
            background: var(--dvf-surface);
            border: 1px solid var(--dvf-border);
            border-radius: 10px;
            padding: 1rem 1.1rem;
        }

        .dvf-info-label {
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .6px;
            color: var(--dvf-muted);
            margin-bottom: .4rem;
            display: flex;
            align-items: center;
            gap: .35rem;
        }

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
            border: 1px solid var(--dvf-border);
            border-radius: 10px;
            box-shadow: var(--dvf-shadow);
        }

        .df-person-card.verified-card {
            background: var(--dvf-success-lt);
            border-color: #86efac;
        }

        .df-person-card.rejected-card {
            background: var(--dvf-danger-lt);
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
            color: var(--dvf-muted);
            margin-bottom: .1rem;
        }

        .df-person-name {
            font-size: .9rem;
            font-weight: 700;
            color: var(--dvf-text);
        }

        .df-person-time {
            font-size: .78rem;
            color: var(--dvf-muted);
        }
    </style>
@endpush

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
        <div>
            <h2 class="fw-bold mb-1" style="color: var(--bps-orange);">Edit Verifikasi Fenomena</h2>
            <p class="text-muted mb-0">Ubah keputusan verifikasi dan arah dampak indikator terkait</p>
        </div>

        <div class="mt-3 mt-md-0">
            <a href="{{ route('fenomena.verification.index', ['tab' => 'riwayat']) }}" class="btn btn-outline-secondary shadow-sm"
                style="border-radius: 8px;">
                <i class="fas fa-arrow-left me-2"></i>Kembali
            </a>
        </div>
    </div>

    {{-- ════════════ FORM ════════════ --}}
    <form id="dvf-form" action="{{ route('fenomena.verification.update', $fenomena->id) }}" method="POST">
        @csrf
        @method('PUT')

        {{-- ────────────────────────────────────────────
        CARD 1 — TITLE & EXPLANATION CARD (EDITABLE)
        ──────────────────────────────────────────── --}}
        <div class="dvf-card mb-4" style="border-left: 5px solid #f97316; border-radius: var(--dvf-radius);">
            <div class="dvf-card-body" style="padding: 2rem;">
                {{-- Judul --}}
                <div class="mb-4">
                    <label for="judul" class="dvf-info-label">
                        <i class="fas fa-heading"></i> Judul Fenomena <span class="text-danger">*</span>
                    </label>
                    <input type="text" class="form-control form-control-custom" id="judul" name="judul"
                        placeholder="Masukkan Judul Fenomena..." value="{{ old('judul', $fenomena->judul) }}" required
                        style="font-size: 1.35rem; font-weight: 800; color: var(--dvf-text); line-height: 1.35; padding: 0.75rem 1rem; border-radius: 8px;">
                </div>

                {{-- Penjelasan --}}
                <div>
                    <label for="penjelasan" class="dvf-info-label">
                        <i class="fas fa-align-left"></i> Isi / Penjelasan Fenomena <span class="text-danger">*</span>
                    </label>
                    <textarea class="form-control form-control-custom textarea-penjelasan" id="penjelasan" name="penjelasan"
                        rows="5" placeholder="Tuliskan penjelasan lengkap mengenai fenomena..." required 
                        style="min-height: 120px; line-height: 1.7; font-size: 0.95rem; color: #334155; padding: 0.75rem 1rem; border-radius: 8px; resize: vertical; text-align: justify;">{{ old('penjelasan', $fenomena->penjelasan) }}</textarea>
                </div>
            </div>
        </div>

        {{-- ────────────────────────────────────────────
        CARD 2 — INFO GRID (EDITABLE SHOW-LIKE CARDS)
        ──────────────────────────────────────────── --}}
        <div class="dvf-info-grid">

            {{-- Tanggal Berita --}}
            <div class="dvf-info-box">
                <label for="tanggal_berita" class="dvf-info-label">
                    <i class="fas fa-calendar-alt"></i> Tanggal Berita <span class="text-danger">*</span>
                </label>
                <div class="mt-1">
                    <input type="date" class="form-control form-control-custom" id="tanggal_berita" name="tanggal_berita"
                        value="{{ old('tanggal_berita', $fenomena->tanggal_berita) }}" required
                        style="font-weight: 600; font-size: 0.92rem; color: var(--dvf-text); padding: 0.6rem 0.8rem; border-radius: 8px;">
                </div>
            </div>

            {{-- Sumber Berita --}}
            <div class="dvf-info-box">
                <label for="sumber_berita_id" class="dvf-info-label">
                    <i class="fas fa-newspaper"></i> Sumber Berita <span class="text-danger">*</span>
                </label>
                <div class="mt-1">
                    <select class="form-select-custom select2" id="sumber_berita_id" name="sumber_berita_id" required>
                        <option value="">Pilih Sumber</option>
                        @foreach($sumberBeritas as $sumber)
                            <option value="{{ $sumber->id }}" data-online="{{ $sumber->is_online ? '1' : '0' }}"
                                @selected(old('sumber_berita_id', $fenomena->sumber_berita_id) == $sumber->id)>
                                {{ $sumber->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Link Berita --}}
            <div class="dvf-info-box" style="grid-column: 1 / -1;">
                <label for="link_berita" class="dvf-info-label">
                    <i class="fas fa-link"></i> Link Berita <span id="link-required-indicator" class="text-muted" style="font-size:0.75rem;">(Opsional)</span>
                </label>
                <div class="mt-1">
                    <div class="input-group">
                        <input type="url" class="form-control form-control-custom" id="link_berita" name="link_berita"
                            placeholder="https://..." value="{{ old('link_berita', $fenomena->link_berita) }}"
                            style="font-weight: 500; font-size: 0.92rem; padding: 0.6rem 0.8rem; border-top-left-radius: 8px; border-bottom-left-radius: 8px; border-top-right-radius: 0; border-bottom-right-radius: 0;">
                        <button type="button" class="btn" id="btn-visit-link" 
                            style="border: 1px solid var(--dvf-border); border-left: none; border-top-right-radius: 8px; border-bottom-right-radius: 8px; border-top-left-radius: 0; border-bottom-left-radius: 0; display: flex; align-items: center; justify-content: center; width: 48px; background: #fff;"
                            title="Kunjungi Link Berita">
                            <i class="fas fa-external-link-alt" style="color: var(--dvf-primary);"></i>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Sektor Usaha --}}
            <div class="dvf-info-box">
                <label for="sektor_usaha_id" class="dvf-info-label">
                    <i class="fas fa-building"></i> Sektor Usaha <span class="text-danger">*</span>
                </label>
                <div class="mt-1">
                    <select class="form-select-custom select2" id="sektor_usaha_id" name="sektor_usaha_id" required>
                        <option value="">Pilih Lapangan Usaha</option>
                        @foreach($sektorUsahas as $sektor)
                            <option value="{{ $sektor->id }}"
                                @selected(old('sektor_usaha_id', $fenomena->sektors->first()->id ?? null) == $sektor->id)>
                                [{{ $sektor->kode }}] {{ $sektor->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Indikator Utama --}}
            <div class="dvf-info-box">
                <label for="indikator_id" class="dvf-info-label">
                    <i class="fas fa-star"></i> Indikator Utama <span class="text-danger">*</span>
                </label>
                <div class="mt-1">
                    @php
                        $utama = $fenomena->indikators->where('kelompok', 'utama')->first();
                        $selectedUtamaId = old('indikator_id', $utama->id ?? null);
                    @endphp
                    <select class="form-select-custom select2" id="indikator_id" name="indikator_id" required>
                        <option value="">Pilih Indikator</option>
                        @foreach($utamaIndikators as $indikator)
                            <option value="{{ $indikator->id }}" @selected($selectedUtamaId == $indikator->id)>
                                [{{ $indikator->kode }}] {{ $indikator->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Jenis Fenomena --}}
            <div class="dvf-info-box" style="grid-column: 1 / -1;">
                <label for="jenis_fenomena_ids" class="dvf-info-label">
                    <i class="fas fa-tag"></i> Jenis Fenomena <span class="text-danger">*</span> <span class="text-muted" style="font-size:0.75rem;">(Boleh >1)</span>
                </label>
                <div class="mt-1">
                    @php
                        $selectedJenisIds = old('jenis_fenomena_ids', $fenomena->jenisFenomenas->pluck('id')->toArray());
                    @endphp
                    <select class="form-select-custom select2" id="jenis_fenomena_ids" name="jenis_fenomena_ids[]" multiple="multiple" required>
                        @foreach($jenisFenomenas as $jenis)
                            <option value="{{ $jenis->id }}" @selected(in_array($jenis->id, $selectedJenisIds))>
                                {{ $jenis->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

        </div>

        {{-- ────────────────────────────────────────────
        CARD 3 — PEOPLE (PENGINPUT & STATUS SAAT INI)
        ──────────────────────────────────────────── --}}
        <div class="df-people-grid mb-4">
            {{-- Creator --}}
            <div class="df-person-card">
                @php
                    $creatorName = $fenomena->creator->name ?? 'Sistem';
                    $creatorInit = collect(explode(' ', $creatorName))->take(2)->map(fn($w) => strtoupper(substr($w, 0, 1)))->implode('');
                @endphp
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

            {{-- Status Verifikasi Saat Ini --}}
            @php
                $status = $fenomena->status_verifikasi;
                [$statusLabel, $statusClass, $statusIcon] = match ($status) {
                    'Y' => ['Diverifikasi', 'verified-card', 'fas fa-check-circle'],
                    'T' => ['Ditolak', 'rejected-card', 'fas fa-times-circle'],
                    default => ['Menunggu Verifikasi', '', 'fas fa-hourglass-half'],
                };
            @endphp
            <div class="df-person-card {{ $statusClass }}">
                @if($status === 'Y')
                    <div class="df-avatar verifier-ok"><i class="fas fa-check" style="font-size: 0.95rem;"></i></div>
                @elseif($status === 'T')
                    <div class="df-avatar verifier-no"><i class="fas fa-times" style="font-size: 0.95rem;"></i></div>
                @else
                    <div class="df-avatar" style="background:#cbd5e1;">?</div>
                @endif
                <div>
                    <div class="df-person-label">⏳ Status Verifikasi Saat Ini</div>
                    <div class="df-person-name">{{ $statusLabel }}</div>
                    <div class="df-person-time">
                        @if($fenomena->verified_by && $fenomena->verifier)
                            Oleh: {{ $fenomena->verifier->name }}
                        @else
                            Belum diproses
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- ────────────────────────────────────────────
        CARD 2 — STATUS VERIFIKASI
        ──────────────────────────────────────────── --}}
        <div class="dvf-card">
            <div class="dvf-card-header">
                <div class="dvf-card-header-icon green">
                    <i class="fas fa-clipboard-check"></i>
                </div>
                <div class="dvf-card-header-text">
                    <h2>Status Verifikasi</h2>
                    <p>Pilih keputusan verifikasi untuk fenomena ini</p>
                </div>
            </div>
            <div class="dvf-card-body">
                <div class="dvf-status-segment" id="status-segment">
                    <input type="radio" name="status_verifikasi" id="status_y" value="Y" required @checked(old('status_verifikasi', $fenomena->status_verifikasi) === 'Y')>
                    <label for="status_y" id="label_y">
                        <i class="fas fa-check-circle"></i>
                        Verifikasi (Setujui)
                    </label>
                    <input type="radio" name="status_verifikasi" id="status_t" value="T" @checked(old('status_verifikasi', $fenomena->status_verifikasi) === 'T')>
                    <label for="status_t" id="label_t">
                        <i class="fas fa-times-circle"></i>
                        Tolak Data
                    </label>
                </div>
                <p class="text-muted small mt-3 mb-0">
                    <i class="fas fa-info-circle me-1"></i>
                    Jika memilih <strong>Setujui</strong>, Anda perlu menentukan arah indikator dan dampak terkait
                    di bawah.
                </p>
            </div>
        </div>

        {{-- ────────────────────────────────────────────
        CARD 3 — ARAH INDIKATOR UTAMA
        (hanya muncul saat Setujui dipilih)
        ──────────────────────────────────────────── --}}
        @php
            $arahUtamaVal = old('arah_utama', $utama ? $utama->pivot->arah ?? null : null);
        @endphp
        <div class="dvf-section-reveal" id="section-arah" style="max-height:0;opacity:0;">
            <div class="dvf-card">
                <div class="dvf-card-header">
                    <div class="dvf-card-header-icon orange">
                        <i class="fas fa-compass"></i>
                    </div>
                    <div class="dvf-card-header-text">
                        <h2>Tentukan Arah Indikator Utama</h2>
                        <p>Pilih arah pergerakan indikator utama berdasarkan fenomena ini</p>
                    </div>
                </div>
                <div class="dvf-card-body">
                    <div class="dvf-dir-card" id="dir-card-utama">
                        <div class="dvf-dir-card-label">
                            <div class="dvf-dir-icon">
                                <i class="fas fa-star"></i>
                            </div>
                            <div>
                                <div class="dvf-dir-name" id="selected-utama-indicator-name">{{ $utama ? $utama->nama : 'Indikator Utama' }}</div>
                                <div class="dvf-dir-kode" id="selected-utama-indicator-kode">Kode: {{ $utama ? $utama->kode : '-' }}</div>
                            </div>
                        </div>
                        <div class="dvf-pill-group" id="primary-pill-group">
                            <input type="radio" name="arah_utama" id="arah_naik" value="naik" data-dir="naik" @checked($arahUtamaVal === 'naik')>
                            <label for="arah_naik">
                                <i class="fas fa-arrow-trend-up"></i> Naik
                            </label>
                            <input type="radio" name="arah_utama" id="arah_turun" value="turun" data-dir="turun" @checked($arahUtamaVal === 'turun')>
                            <label for="arah_turun">
                                <i class="fas fa-arrow-trend-down"></i> Turun
                            </label>
                            <input type="radio" name="arah_utama" id="arah_tetap" value="tetap" data-dir="tetap" @checked($arahUtamaVal === 'tetap')>
                            <label for="arah_tetap">
                                <i class="fas fa-minus"></i> Tetap
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ────────────────────────────────────────────
            CARD 4 — DAMPAK TERKAIT
            ──────────────────────────────────────────── --}}
            <div class="dvf-card">
                <div class="dvf-card-header">
                    <div class="dvf-card-header-icon purple">
                        <i class="fas fa-diagram-project"></i>
                    </div>
                    <div class="dvf-card-header-text">
                        <h2>Tentukan Dampak Terkait</h2>
                        <p>Pilih arah dampak untuk setiap indikator yang terpengaruh</p>
                    </div>
                </div>
                <div class="dvf-card-body">
                    @if(count($impactIndikators) > 0)
                        <div class="dvf-impact-grid" id="impact-grid">
                            @foreach($impactIndikators as $impact)
                                @php
                                    $impactPivot = $fenomena->indikators->where('id', $impact->id)->first()->pivot ?? null;
                                    $impactArahVal = old("impact_directions.{$impact->id}", $impactPivot ? $impactPivot->arah : null);
                                @endphp
                                <div class="dvf-impact-item" id="impact-item-{{ $impact->id }}">
                                    <div class="dvf-impact-item-header">
                                        <div class="dvf-impact-icon">
                                            <i class="fas fa-chart-line"></i>
                                        </div>
                                        <div class="dvf-impact-name" title="{{ $impact->nama }}">
                                            {{ $impact->nama }}
                                        </div>
                                    </div>
                                    <div class="dvf-impact-pills">
                                        <input type="radio" name="impact_directions[{{ $impact->id }}]"
                                            id="imp_{{ $impact->id }}_naik" value="naik" data-dir="naik"
                                            data-impact="{{ $impact->id }}" @checked($impactArahVal === 'naik')>
                                        <label for="imp_{{ $impact->id }}_naik">
                                            <i class="fas fa-arrow-up" style="font-size:.7rem;"></i> Naik
                                        </label>

                                        <input type="radio" name="impact_directions[{{ $impact->id }}]"
                                            id="imp_{{ $impact->id }}_turun" value="turun" data-dir="turun"
                                            data-impact="{{ $impact->id }}" @checked($impactArahVal === 'turun')>
                                        <label for="imp_{{ $impact->id }}_turun">
                                            <i class="fas fa-arrow-down" style="font-size:.7rem;"></i> Turun
                                        </label>

                                        <input type="radio" name="impact_directions[{{ $impact->id }}]"
                                            id="imp_{{ $impact->id }}_tetap" value="tetap" data-dir="tetap"
                                            data-impact="{{ $impact->id }}" @checked($impactArahVal === 'tetap')>
                                        <label for="imp_{{ $impact->id }}_tetap">
                                            <i class="fas fa-minus" style="font-size:.7rem;"></i> Tetap
                                        </label>

                                        <input type="radio" name="impact_directions[{{ $impact->id }}]"
                                            id="imp_{{ $impact->id }}_na" value="" data-dir="na" data-impact="{{ $impact->id }}"
                                            @checked(empty($impactArahVal))>
                                        <label for="imp_{{ $impact->id }}_na" class="na-label">
                                            <i class="fas fa-ban" style="font-size:.7rem;"></i> Tidak Ada (N/A)
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-circle-info text-muted" style="font-size:2rem;"></i>
                            <p class="text-muted mt-2 mb-0">Tidak ada indikator dampak yang perlu ditentukan.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>{{-- /section-arah --}}

    </form>{{-- /form --}}

    {{-- ════════════ ACTION BAR ════════════ --}}
    <div class="dvf-sticky-bar">
        <div class="dvf-bar-inner">
            {{-- Summary --}}
            <div class="dvf-bar-summary">
                <i class="fas fa-circle-info text-muted"></i>
                <span>Status:&nbsp;<span class="dvf-summary-chip neutral" id="sum-status">Belum dipilih</span></span>
                <span id="sum-arah-wrap" style="display:none;">
                    &bull; Arah:&nbsp;<span class="dvf-summary-chip neutral" id="sum-arah">–</span>
                </span>
                <span id="sum-impact-wrap" style="display:none;">
                    &bull; Dampak dipilih:&nbsp;<span class="dvf-summary-chip" id="sum-impact">0</span>
                </span>
            </div>

            {{-- Actions --}}
            <div class="dvf-bar-actions">
                <a href="{{ route('fenomena.verification.index', ['tab' => 'riwayat']) }}" class="dvf-btn-cancel">
                    <i class="fas fa-xmark"></i> Batal
                </a>
                <button type="submit" form="dvf-form" class="dvf-btn-save" id="btn-save">
                    <i class="fas fa-floppy-disk"></i> Simpan Hasil Verifikasi
                </button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize Select2
            $('#sumber_berita_id, #sektor_usaha_id, #indikator_id').select2({
                theme: 'default',
                width: '100%',
                placeholder: 'Pilih opsi...'
            });

            $('#jenis_fenomena_ids').select2({
                theme: 'default',
                width: '100%',
                placeholder: 'Pilih jenis fenomena...',
                closeOnSelect: false
            });

            // Update main indicator preview in Card 3 when dropdown changes
            $('#indikator_id').on('change', function() {
                const text = $(this).find('option:selected').text().trim();
                const matched = text.match(/\[(.*?)\]\s*(.*)/);
                if (matched) {
                    $('#selected-utama-indicator-name').text(matched[2]);
                    $('#selected-utama-indicator-kode').text('Kode: ' + matched[1]);
                } else {
                    $('#selected-utama-indicator-name').text(text || 'Indikator Utama');
                    $('#selected-utama-indicator-kode').text('Kode: -');
                }
            });

            // Dynamic required for link_berita
            $('#sumber_berita_id').on('change', function () {
                const isOnline = $(this).find('option:selected').data('online') == '1';
                const linkInput = $('#link_berita');
                const indicator = $('#link-required-indicator');

                if (isOnline) {
                    linkInput.attr('required', true);
                    indicator.html('<span class="text-danger">*</span> <span style="color:#ef4444; font-size:0.75rem;">(Wajib untuk online)</span>');
                } else {
                    linkInput.removeAttr('required');
                    indicator.html('<span class="text-muted">(Opsional)</span>');
                }
            }).trigger('change');
        });

        document.addEventListener('DOMContentLoaded', function () {

            /* ── refs ── */
            const sectionArah = document.getElementById('section-arah');
            const statusInputs = document.querySelectorAll('input[name="status_verifikasi"]');
            const arahInputs = document.querySelectorAll('input[name="arah_utama"]');
            const dirCardUtama = document.getElementById('dir-card-utama');

            /* Summary refs */
            const sumStatus = document.getElementById('sum-status');
            const sumArahWrap = document.getElementById('sum-arah-wrap');
            const sumArah = document.getElementById('sum-arah');
            const sumImpactWrap = document.getElementById('sum-impact-wrap');
            const sumImpact = document.getElementById('sum-impact');

            /* ── Section reveal helpers ── */
            function showSection() {
                sectionArah.style.maxHeight = sectionArah.scrollHeight + 2000 + 'px';
                sectionArah.style.opacity = '1';
            }

            function hideSection() {
                sectionArah.style.maxHeight = '0';
                sectionArah.style.opacity = '0';
            }

            /* ── Status change ── */
            function onStatusChange() {
                const val = document.querySelector('input[name="status_verifikasi"]:checked')?.value;

                if (val === 'Y') {
                    showSection();
                    document.querySelectorAll('input[name="arah_utama"]').forEach(r => r.required = true);
                    sumStatus.className = 'dvf-summary-chip success';
                    sumStatus.innerHTML = '<i class="fas fa-check-circle"></i> Disetujui';
                    sumArahWrap.style.display = '';
                    sumImpactWrap.style.display = '';
                } else if (val === 'T') {
                    hideSection();
                    document.querySelectorAll('input[name="arah_utama"]').forEach(r => r.required = false);
                    sumStatus.className = 'dvf-summary-chip danger';
                    sumStatus.innerHTML = '<i class="fas fa-times-circle"></i> Ditolak';
                    sumArahWrap.style.display = 'none';
                    sumImpactWrap.style.display = 'none';
                } else {
                    hideSection();
                    sumStatus.className = 'dvf-summary-chip neutral';
                    sumStatus.textContent = 'Belum dipilih';
                    sumArahWrap.style.display = 'none';
                    sumImpactWrap.style.display = 'none';
                }
            }

            statusInputs.forEach(r => r.addEventListener('change', onStatusChange));
            onStatusChange(); // init

            /* ── Arah utama highlight ── */
            function updateArahUtamaHighlight(checkedInput) {
                if (!checkedInput) return;
                const dir = checkedInput.value;
                const icons = { naik: '↑', turun: '↓', tetap: '—' };
                const cls = { naik: 'success', turun: 'danger', tetap: 'neutral' };

                if (dir) {
                    dirCardUtama.classList.add('has-selection');
                    sumArah.className = 'dvf-summary-chip ' + (cls[dir] || 'neutral');
                    sumArah.textContent = icons[dir] + ' ' + dir.charAt(0).toUpperCase() + dir.slice(1);
                } else {
                    dirCardUtama.classList.remove('has-selection');
                }
            }

            arahInputs.forEach(r => {
                r.addEventListener('change', function () {
                    updateArahUtamaHighlight(this);
                });
            });

            // Initial trigger for checked arah utama
            const checkedArahUtama = document.querySelector('input[name="arah_utama"]:checked');
            if (checkedArahUtama) {
                updateArahUtamaHighlight(checkedArahUtama);
            }

            /* ── Impact card highlight & counter ── */
            function updateImpactCounter() {
                const selected = document.querySelectorAll('input[name^="impact_directions"]:checked:not([value=""])');
                sumImpact.textContent = selected.length;
            }

            function updateImpactHighlight(inputEl) {
                const impactId = inputEl.dataset.impact;
                const item = document.getElementById('impact-item-' + impactId);
                if (item) {
                    if (inputEl.value !== '') {
                        item.classList.add('has-selection');
                    } else {
                        item.classList.remove('has-selection');
                    }
                }
            }

            document.querySelectorAll('input[name^="impact_directions"]').forEach(r => {
                r.addEventListener('change', function () {
                    updateImpactHighlight(this);
                    updateImpactCounter();
                });
            });

            // Initial highlight triggers for impact directions
            document.querySelectorAll('input[name^="impact_directions"]:checked').forEach(r => {
                updateImpactHighlight(r);
            });
            updateImpactCounter();

            /* ── Form submit guard ── */
            document.getElementById('dvf-form').addEventListener('submit', function (e) {
                const status = document.querySelector('input[name="status_verifikasi"]:checked')?.value;
                if (!status) {
                    e.preventDefault();
                    alert('Silakan pilih status verifikasi terlebih dahulu (Setujui atau Tolak).');
                    return;
                }
                if (status === 'Y') {
                    const arah = document.querySelector('input[name="arah_utama"]:checked')?.value;
                    if (!arah) {
                        e.preventDefault();
                        alert('Silakan tentukan arah indikator utama terlebih dahulu.');
                        return;
                    }
                }
            });

            // Visit link handler
            $('#btn-visit-link').on('click', function() {
                const url = $('#link_berita').val().trim();
                if (url) {
                    let targetUrl = url;
                    if (!/^https?:\/\//i.test(url)) {
                        targetUrl = 'http://' + url;
                    }
                    window.open(targetUrl, '_blank');
                } else {
                    alert('Silakan masukkan link berita terlebih dahulu.');
                }
            });

            /* Recalculate section height on window resize */
            window.addEventListener('resize', () => {
                const val = document.querySelector('input[name="status_verifikasi"]:checked')?.value;
                if (val === 'Y') showSection();
            });
        });
    </script>
@endpush
