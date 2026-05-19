@extends('layouts.admin')

@section('title', 'My Team - ' . ($kabupatenName ?? 'BPS'))

@push('styles')
    <style>
        :root {
            --primary-orange: #FF9900;
            --primary-navy: #003366;
            --soft-gray: #F8FAFC;
            --border-color: #E2E8F0;
            --text-main: #1E293B;
            --text-muted: #64748B;
            --card-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05);
            --hover-shadow: 0 10px 25px -5px rgba(255, 153, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
        }

        .team-container {
            padding: 0.5rem;
        }

        /* Header Style */
        .page-header {
            margin-bottom: 1rem;
        }

        .page-header h1 {
            font-size: 2rem;
            font-weight: 800;
            color: var(--primary-orange);
            margin-bottom: 0.25rem;
            letter-spacing: -0.025em;
        }

        .page-header p {
            font-size: 0.95rem;
            color: var(--text-muted);
            margin-bottom: 0;
        }

        /* Stat Cards */
        .stat-card {
            background: white;
            border-radius: 20px;
            padding: 1.5rem;
            box-shadow: var(--card-shadow);
            border: 1px solid var(--border-color);
            position: relative;
            overflow: hidden;
            height: 100%;
            transition: transform 0.3s ease;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-orange), #FFB84D);
        }

        .stat-card .icon-box {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            margin-bottom: 1rem;
            background: rgba(255, 153, 0, 0.1);
            color: var(--primary-orange);
        }

        .stat-card .label {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.25rem;
        }

        .stat-card .value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-navy);
        }

        /* Member Cards Grid */
        .member-grid {
            display: grid;
            gap: 1.5rem;
            grid-template-columns: 1fr;
        }

        @media (min-width: 768px) {
            .member-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (min-width: 1200px) {
            .member-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        .member-card {
            background: white;
            border-radius: 24px;
            padding: 2rem;
            box-shadow: var(--card-shadow);
            border: 1px solid var(--border-color);
            text-align: center;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .member-card:hover {
            transform: translateY(-10px);
            shadow: var(--hover-shadow);
            border-color: rgba(255, 153, 0, 0.2);
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
            margin-bottom: 1.5rem;
            box-shadow: 0 8px 15px -3px rgba(0, 0, 0, 0.1);
            text-transform: uppercase;
            border: 4px solid white;
        }

        .member-info h3 {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--primary-navy);
            margin-bottom: 0.25rem;
        }

        .member-info .role {
            font-size: 0.9rem;
            color: var(--primary-orange);
            font-weight: 600;
            margin-bottom: 1.5rem;
            display: block;
        }

        .member-details {
            width: 100%;
            text-align: left;
            border-top: 1px dashed var(--border-color);
            padding-top: 1.25rem;
        }

        .detail-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 0.75rem;
            font-size: 0.85rem;
            color: var(--text-main);
        }

        .detail-item i {
            width: 20px;
            color: var(--text-muted);
            font-size: 1rem;
        }

        .last-seen {
            font-size: 0.75rem;
            color: var(--text-muted);
            background: #F1F5F9;
            padding: 0.4rem 0.8rem;
            border-radius: 99px;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
        }

        /* Empty State */
        .empty-team {
            padding: 4rem 2rem;
            text-align: center;
            background: white;
            border-radius: 24px;
            border: 2px dashed var(--border-color);
        }

        .empty-team i {
            font-size: 4rem;
            color: #CBD5E1;
            margin-bottom: 1.5rem;
        }

        /* Color Palettes */
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
@endpush

@section('content')
    <div class="mt-2 fade-in-up">
        <!-- Page Header -->
        <div class="page-header">
            <h1>Anggota Tim</h1>
            <p>Unit Kerja Statistik Sosial &mdash; Memantau kolaborasi anggota di wilayah Anda</p>
        </div>

        <!-- Quick Stats -->
        <div class="row g-4 mb-5">
            <div class="col-md-6 col-lg-4">
                <div class="stat-card">
                    <div class="icon-box">
                        <i class="fas fa-map-location-dot"></i>
                    </div>
                    <div class="label">Kabupaten/Kota</div>
                    <div class="value">{{ $kabupatenName }}</div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="stat-card">
                    <div class="icon-box">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="label">Total Anggota</div>
                    <div class="value">{{ $totalMembers }}</div>
                </div>
            </div>
        </div>

        <!-- Members Grid -->
        <div class="member-grid">
            @forelse($teamMembers as $member)
                @php
                    // Avatar Logic
                    $names = explode(' ', $member->name);
                    $initials = '';
                    if (count($names) >= 2) {
                        $initials = strtoupper(substr($names[0], 0, 1) . substr($names[count($names) - 1], 0, 1));
                    } else {
                        $initials = strtoupper(substr($names[0], 0, 1));
                    }

                    $colorClasses = ['bg-color-1', 'bg-color-2', 'bg-color-3', 'bg-color-4', 'bg-color-5', 'bg-color-6'];
                    $randomColor = $colorClasses[$member->id % count($colorClasses)];
                @endphp

                <div class="member-card">
                    <div class="avatar-circle {{ $randomColor }}">
                        {{ $initials }}
                    </div>

                    <div class="member-info">
                        <h3>{{ $member->name }}</h3>
                        <span class="role">Statistik Sosial</span>
                    </div>

                    <div class="member-details">
                        <div class="detail-item">
                            <i class="fas fa-envelope"></i>
                            <span class="text-truncate" title="{{ $member->email }}">{{ $member->email }}</span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-phone"></i>
                            <span>{{ $member->no_hp ?? '-' }}</span>
                        </div>
                        <div class="mt-3">
                            <span class="last-seen">
                                <i class="fas fa-clock"></i>
                                Last login: {{ $member->last_login_at ? $member->last_login_at->diffForHumans() : 'Never' }}
                            </span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="empty-team">
                        <i class="fas fa-users-slash"></i>
                        <h3 class="fw-bold text-navy">No Team Members</h3>
                        <p class="text-muted">Belum ada anggota tim pada wilayah ini.</p>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
@endsection