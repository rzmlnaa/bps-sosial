@extends('layouts.admin')

@section('title', 'Kontributor Fenomena')

@push('styles')
    <style>
        :root {
            --lb-gold: #fbbf24;
                --lb-silver: #94a3b8;
                --lb-bronze: #b45309;
                --lb-primary: #1d4ed8;
                --lb-bg: #f8fafc;
                --lb-card-bg: #ffffff;
                --lb-radius: 24px;
            }

            .leaderboard-container {
                max-width: 1000px;
                margin: 0 auto;
                padding: 1rem 0;
                font-family: 'Inter', sans-serif;
            }

            /* Stats Cards */
            .stats-overview {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 1.25rem;
                margin-bottom: 3rem;
            }

            .stat-box {
                background: #fff;
                padding: 1.5rem;
                border-radius: 20px;
                border: 1px solid #e2e8f0;
                display: flex;
                align-items: center;
                gap: 1.25rem;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.03);
                transition: transform 0.2s ease;
            }

            .stat-box:hover {
                transform: translateY(-3px);
            }

            .stat-box i {
                width: 52px;
                height: 52px;
                background: #eff6ff;
                border-radius: 14px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 1.5rem;
                color: var(--lb-primary);
            }

            .stat-info h6 {
                margin: 0;
                font-size: 0.9rem;
                color: #64748b;
                font-weight: 600;
            }

            .stat-info h4 {
                margin: 0;
                font-size: 1.5rem;
                font-weight: 800;
                color: #0f172a;
            }

            .lb-header {
                text-align: center;
                margin-bottom: 4rem;
            }

            .lb-title {
                font-size: 2.75rem;
                font-weight: 900;
                color: #0f172a;
                letter-spacing: -0.03em;
                margin-bottom: 0.75rem;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 1rem;
            }

            .lb-subtitle {
                color: #64748b;
                font-size: 1.15rem;
                font-weight: 500;
            }

            /* Top 3 Cards Desktop */
            .top-three-grid {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 2rem;
                margin-bottom: 6rem;
                align-items: flex-end;
                padding-top: 2rem;
            }

            .top-card {
                background: var(--lb-card-bg);
                border-radius: var(--lb-radius);
                padding: 2.5rem 1.5rem;
                text-align: center;
                position: relative;
                transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
                box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.04);
                border: 1px solid #f1f5f9;
            }

            .top-card:hover {
                transform: translateY(-12px);
                box-shadow: 0 30px 60px -12px rgba(0, 0, 0, 0.12);
            }

            /* Rank 1 Special Emphasis */
            .top-card.rank-1 {
                padding-top: 5rem;
                padding-bottom: 3.5rem;
                transform: scale(1.1);
                border: 2px solid #fef3c7;
                background: linear-gradient(180deg, #fffcf0 0%, #ffffff 100%);
                box-shadow: 0 25px 50px -12px rgba(251, 191, 36, 0.2);
                z-index: 10;
            }

            .top-card.rank-1:hover {
                transform: scale(1.15) translateY(-12px);
                box-shadow: 0 35px 70px -15px rgba(251, 191, 36, 0.3);
            }

            .rank-badge-floated {
                position: absolute;
                top: -28px;
                left: 50%;
                transform: translateX(-50%);
                width: 56px;
                height: 56px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 1.5rem;
                font-weight: 900;
                color: #fff;
                box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
                border: 4px solid #fff;
            }

            .rank-1 .rank-badge-floated { background: linear-gradient(135deg, #fbbf24, #f59e0b); font-size: 1.75rem; }
            .rank-2 .rank-badge-floated { background: linear-gradient(135deg, #94a3b8, #64748b); }
            .rank-3 .rank-badge-floated { background: linear-gradient(135deg, #b45309, #78350f); }

            .top-avatar {
                width: 80px;
                height: 80px;
                margin: 0 auto 1.75rem;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 2rem;
                font-weight: 800;
                color: #475569;
                background: #f1f5f9;
                box-shadow: inset 0 2px 4px rgba(0,0,0,0.06);
                border: 3px solid #fff;
            }

            .rank-1 .top-avatar { 
                width: 100px; height: 100px; font-size: 2.5rem; 
                background: #fbbf24; color: #fff;
                box-shadow: 0 0 20px rgba(251, 191, 36, 0.3);
            }

            .top-name {
                font-weight: 800;
                font-size: 1.35rem;
                color: #0f172a;
                margin-bottom: 0.75rem;
                display: block;
            }

            .top-count {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                padding: 0.6rem 1.25rem;
                background: #eff6ff;
                border-radius: 50px;
                gap: 0.5rem;
                font-weight: 800;
                color: var(--lb-primary);
                font-size: 1.2rem;
            }

            /* Leaderboard List */
            .lb-card {
                background: #fff;
                border-radius: var(--lb-radius);
                overflow: hidden;
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
                border: 1px solid #f1f5f9;
            }

            .lb-item {
                display: flex;
                align-items: center;
                padding: 1.5rem 2.5rem;
                border-bottom: 1px solid #f8fafc;
                transition: all 0.3s ease;
            }

            .lb-item:hover {
                background: #fbfcfe;
                padding-left: 3rem;
            }

            .lb-rank-box {
                width: 60px;
                display: flex;
                justify-content: center;
                margin-right: 1.5rem;
            }

            .rank-circle {
                width: 44px;
                height: 44px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-weight: 800;
                font-size: 1.05rem;
                background: #f1f5f9;
                color: #64748b;
                border: 2px solid transparent;
            }

            .rank-top-1 { background: #fef3c7; color: #b45309; font-size: 1.5rem; border-color: #fbbf24; }
            .rank-top-2 { background: #f1f5f9; color: #475569; font-size: 1.5rem; border-color: #94a3b8; }
            .rank-top-3 { background: #fff7ed; color: #9a3412; font-size: 1.5rem; border-color: #b45309; }

            .lb-user-details {
                flex-grow: 1;
                display: flex;
                align-items: center;
                gap: 1.5rem;
            }

            .lb-avatar-small {
                width: 52px;
                height: 52px;
                border-radius: 14px;
                background: #f8fafc;
                display: flex;
                align-items: center;
                justify-content: center;
                font-weight: 700;
                font-size: 1.1rem;
                color: #475569;
                border: 1px solid #e2e8f0;
            }

            .lb-info h5 {
                margin: 0;
                font-size: 1.15rem;
                font-weight: 700;
                color: #1e293b;
                margin-bottom: 0.2rem;
            }

            .lb-info p {
                margin: 0;
                font-size: 0.9rem;
                color: #64748b;
                font-weight: 500;
            }

            .lb-stats-box {
                text-align: right;
                min-width: 200px;
            }

            .lb-count-text {
                display: block;
                font-weight: 800;
                color: var(--lb-primary);
                font-size: 1.2rem;
                margin-bottom: 0.6rem;
            }

            /* Progress Bar */
            .lb-progress-container {
                width: 100%;
                max-width: 180px;
                margin-left: auto;
            }

            .progress {
                height: 10px;
                border-radius: 10px;
                background-color: #f1f5f9;
                overflow: hidden;
                border: 1px solid #e2e8f0;
            }

            .progress-bar {
                border-radius: 10px;
                background: linear-gradient(90deg, #3b82f6, #1d4ed8);
                box-shadow: 0 2px 4px rgba(29, 78, 216, 0.2);
            }

            .update-footer {
                text-align: center;
                margin-top: 4rem;
                padding: 2rem;
                color: #94a3b8;
                font-size: 0.95rem;
                border-top: 1px dashed #cbd5e1;
                font-weight: 500;
            }

            @media (max-width: 768px) {
                .stats-overview {
                    grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
                    gap: 0.75rem;
                }
                .stat-box {
                    padding: 1rem;
                    gap: 0.75rem;
                    border-radius: 14px;
                }
                .stat-box i {
                    width: 42px;
                    height: 42px;
                    font-size: 1.2rem;
                    border-radius: 10px;
                    flex-shrink: 0;
                }
                .stat-info h6 {
                    font-size: 0.78rem;
                }
                .stat-info h4 {
                    font-size: 1.2rem;
                }
                .top-three-grid { display: none; }
                .lb-title { font-size: 2rem; }
                .lb-item { padding: 1.5rem 1.25rem; }
                .lb-item:hover { padding-left: 1.25rem; }
                .lb-stats-box { min-width: auto; }
                .lb-progress-container { display: none; }
                .lb-rank-box { margin-right: 1rem; width: 44px; }
                .lb-info h5 { font-size: 1.05rem; }
                .lb-count-text { font-size: 1.05rem; }
            }
        </style>
@endpush

@section('content')
    <div class="mt-2 fade-in-up">
        <!-- Statistics Bar -->
        <div class="stats-overview">
            <div class="stat-box ">
                <i class="fas fa-chart-line"></i>
                <div class="stat-info">
                    <h6>Total Fenomena</h6>
                    <h4>{{ number_format($totalVerified) }}</h4>
                </div>
            </div>
            <div class="stat-box">
                <i class="fas fa-users"></i>
                <div class="stat-info">
                    <h6>Kontributor</h6>
                    <h4>{{ number_format($totalContributors) }}</h4>
                </div>
            </div>
            <div class="stat-box">
                <i class="fas fa-medal"></i>
                <div class="stat-info">
                    <h6>Top Kontribusi</h6>
                    <h4>{{ $topContributors->first()?->fenomenas_count ?? 0 }}</h4>
                </div>
            </div>
        </div>

        <div class="lb-header">
            <h1 class="lb-title">
                🏆 Top 10 Kontributor
            </h1>
            <p class="lb-subtitle">Apresiasi bagi insan BPS yang paling aktif mendokumentasikan fenomena terverifikasi</p>
        </div>

        @if($topContributors->isEmpty())
            <div class="text-center py-5 lb-card shadow-sm">
                <div class="py-5">
                    <img src="https://cdn-icons-png.flaticon.com/512/7486/7486744.png" width="140" class="mb-4 opacity-50">
                    <h5 class="text-muted fw-bold">Belum ada kontribusi data bulan ini.</h5>
                    <p class="text-muted">Ayo jadi yang pertama berkontribusi!</p>
                </div>
            </div>
        @else
            @php 
                            $maxFenomena = $topContributors->first()->fenomenas_count;
                $top3 = $topContributors->take(3);
            @endphp

            <!-- Desktop Podium (Top 3) -->
            <div class="top-three-grid d-none d-md-grid">
                {{-- Rank 2 --}}
                @if($top3->has(1))
                    <div class="top-card rank-2">
                        <div class="rank-badge-floated">2</div>
                        <div class="top-avatar">{{ substr($top3[1]->name, 0, 1) }}</div>
                        <h4 class="top-name">{{ $top3[1]->name }}</h4>
                        <div class="top-count">
                            <i class="fas fa-fire"></i>
                            {{ $top3[1]->fenomenas_count }} Fenomena
                        </div>
                    </div>
                @endif

                {{-- Rank 1 --}}
                @if($top3->has(0))
                    <div class="top-card rank-1">
                        <div class="rank-badge-floated"><i class="fas fa-crown"></i></div>
                        <div class="top-avatar">{{ substr($top3[0]->name, 0, 1) }}</div>
                        <h4 class="top-name">{{ $top3[0]->name }}</h4>
                        <div class="top-count">
                            <i class="fas fa-fire"></i>
                            {{ $top3[0]->fenomenas_count }} Fenomena
                        </div>
                    </div>
                @endif

                {{-- Rank 3 --}}
                @if($top3->has(2))
                    <div class="top-card rank-3">
                        <div class="rank-badge-floated">3</div>
                        <div class="top-avatar">{{ substr($top3[2]->name, 0, 1) }}</div>
                        <h4 class="top-name">{{ $top3[2]->name }}</h4>
                        <div class="top-count">
                            <i class="fas fa-fire"></i>
                            {{ $top3[2]->fenomenas_count }} Fenomena
                        </div>
                    </div>
                @endif
            </div>

            <!-- Leaderboard List -->
            <div class="lb-card">
                @foreach($topContributors as $index => $user)
                    <div class="lb-item">
                        <div class="lb-rank-box">
                            <div class="rank-circle {{ $index == 0 ? 'rank-top-1' : ($index == 1 ? 'rank-top-2' : ($index == 2 ? 'rank-top-3' : '')) }}">
                                @if($index == 0) 🥇
                                @elseif($index == 1) 🥈
                                @elseif($index == 2) 🥉
                                @else {{ $index + 1 }}
                                @endif
                            </div>
                        </div>
                        <div class="lb-user-details">
                            <div class="lb-avatar-small">
                                {{ substr($user->name, 0, 1) }}
                            </div>
                            <div class="lb-info">
                                <h5>{{ $user->name }}</h5>
                                <p><i class="fas fa-map-marker-alt me-1"></i> {{ $user->kabupaten->nama_kabupaten ?? 'Tim Provinsi' }}</p>
                            </div>
                        </div>
                        <div class="lb-stats-box">
                            <span class="lb-count-text">
                                🔥 {{ $user->fenomenas_count }} Fenomena
                            </span>
                            <div class="lb-progress-container d-none d-md-block">
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" 
                                         style="width: {{ ($user->fenomenas_count / $maxFenomena) * 100 }}%" 
                                         aria-valuenow="{{ ($user->fenomenas_count / $maxFenomena) * 100 }}" 
                                         aria-valuemin="0" aria-valuemax="100">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if($lastUpdate)
                <div class="update-footer">
                    <i class="fas fa-clock me-1"></i>
                    Pembaruan terakhir: {{ \Carbon\Carbon::parse($lastUpdate)->translatedFormat('d F Y, H:i') }} WIB
                </div>
            @endif
        @endif
    </div>
@endsection