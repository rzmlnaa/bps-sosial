@extends('layouts.admin')

@section('title', 'Desa Cantik')

@section('content')
    <div class="mt-2 fade-in-up">
        <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
            <div>
                <h2 class="fw-bold mb-1" style="color: var(--bps-orange);">Visualisasi Desa Cantik</h2>
                <p class="text-muted mb-0">Halaman Visualisasi dan Monitoring Program Desa Cantik</p>
            </div>

            @if(auth()->check() && auth()->user()->kabupaten && auth()->user()->kabupaten->kode_kab == '6100')
                <a href="{{ route('desa-cantik.kelola') }}" class="btn btn-orange rounded-pill px-4 shadow-sm">
                    <i class="fas fa-cog me-2 fa-spin"></i> Kelola Desa Cantik
                </a>
            @endif
        </div>

        {{-- Visualization Content Area --}}
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4 bps-card">
                    <div class="card-body p-5 text-center">
                        <div class="mb-4">
                            <i class="fas fa-chart-line fa-4x" style="color: #eee;"></i>
                        </div>
                        <h4 class="fw-bold text-muted">Akan Datang</h4>
                        <p class="text-muted mx-auto" style="max-width: 500px;">Halaman ini akan menampilkan peta dan grafik
                            visualisasi progress program Desa Cantik di Kalimantan Barat.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            .btn-orange {
                background-color: var(--bps-orange);
                border-color: var(--bps-orange);
                color: #fff;
            }

            .btn-orange:hover,
            .btn-orange:focus {
                background-color: #e6661a;
                border-color: #e6661a;
                color: #fff;
            }

            .menu-card {
                transition: transform 0.2s, box-shadow 0.2s;
                cursor: pointer;
            }

            .menu-card:hover {
                transform: translateY(-4px);
                box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12) !important;
            }

            .menu-icon-wrap {
                width: 60px;
                height: 60px;
                border-radius: 50%;
                background: #fff3e0;
                display: flex;
                align-items: center;
                justify-content: center;
                margin: 0 auto;
            }
        </style>
    @endpush
@endsection