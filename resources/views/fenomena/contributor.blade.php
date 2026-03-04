@extends('layouts.admin')

@section('title', 'Kontributor Fenomena')

@push('styles')
    <style>
        :root {
            --fc-primary: #f58220;
            --fc-secondary: #0f172a;
            --fc-muted: #64748b;
            --fc-border: #e2e8f0;
            --fc-surface: #f8fafc;
            --fc-radius: 16px;
            --fc-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        }

        .fc-container {
            font-family: 'Inter', sans-serif;
            padding: 1.5rem 0;
        }

        .fc-header {
            margin-bottom: 2rem;
        }

        .fc-title {
            font-size: 1.75rem;
            font-weight: 800;
            color: var(--fc-primary);
            margin-bottom: 0.5rem;
        }

        .fc-subtitle {
            color: var(--fc-muted);
            font-size: 0.95rem;
        }

        .fc-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2.5rem;
        }

        .fc-stat-card {
            background: #fff;
            padding: 1.5rem;
            border-radius: var(--fc-radius);
            border: 1px solid var(--fc-border);
            box-shadow: var(--fc-shadow);
            display: flex;
            align-items: center;
            gap: 1.25rem;
        }

        .fc-stat-icon {
            width: 54px;
            height: 54px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            background: #fff4eb;
            color: var(--fc-primary);
        }

        .fc-stat-info h4 {
            font-size: 1.5rem;
            font-weight: 800;
            margin: 0;
            color: var(--fc-secondary);
        }

        .fc-stat-info p {
            font-size: 0.85rem;
            color: var(--fc-muted);
            margin: 0;
            font-weight: 500;
        }

        .fc-card {
            background: #fff;
            border-radius: var(--fc-radius);
            border: 1px solid var(--fc-border);
            box-shadow: var(--fc-shadow);
            overflow: hidden;
        }

        .fc-card-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--fc-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .fc-card-title {
            font-weight: 700;
            font-size: 1.1rem;
            color: var(--fc-secondary);
            margin: 0;
        }

        .table-responsive {
            margin: 0;
        }

        .fc-table {
            width: 100%;
            margin-bottom: 0;
        }

        .fc-table th {
            background: var(--fc-surface);
            padding: 1rem 1.5rem;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--fc-muted);
            border-bottom: 1px solid var(--fc-border);
        }

        .fc-table td {
            padding: 1.25rem 1.5rem;
            vertical-align: middle;
            border-bottom: 1px solid var(--fc-border);
        }

        .fc-user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .fc-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--fc-primary), #E9861A);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 700;
            font-size: 0.9rem;
        }

        .fc-username {
            font-weight: 600;
            color: var(--fc-secondary);
            font-size: 0.95rem;
            margin: 0;
        }

        .fc-userteam {
            font-size: 0.75rem;
            color: var(--fc-muted);
            margin: 0;
        }

        .fc-count-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.4rem 0.8rem;
            background: #eff6ff;
            color: #1d4ed8;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 700;
            gap: 0.4rem;
        }

        .fc-rank {
            font-weight: 800;
            color: var(--fc-muted);
            font-size: 1.1rem;
            width: 30px;
        }

        .rank-1 {
            color: #f59e0b;
        }

        .rank-2 {
            color: #94a3b8;
        }

        .rank-3 {
            color: #b45309;
        }

        @media (max-width: 768px) {
            .fc-stats-grid {
                grid-template-columns: 1fr;
            }

            .fc-title {
                font-size: 1.5rem;
            }
        }
    </style>
@endpush

@section('content')
    <style>
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
                Dikembangkan oleh <a href="https://kostapp.reservasiaja.com/portofolio" target="_blank"
                    style="text-decoration: none;">Peserta Magang</a> –
                Program
                MagangHUB Kemnaker
            </p>

        </div>
    </div>
@endsection