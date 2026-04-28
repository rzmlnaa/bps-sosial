@extends('layouts.admin')

@section('title', 'Desa Cantik')

@section('content')
    <div class="mt-2 fade-in-up">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-1" style="color: var(--bps-orange);">Desa Cantik</h2>
                <p class="text-muted mb-0">Halaman Utama Desa Cantik</p>
            </div>

            {{-- Tombol hanya muncul jika level provinsi (kode_kab == '6100') --}}
            @if(auth()->check() && auth()->user()->kabupaten && auth()->user()->kabupaten->kode_kab == '6100')
                <a href="{{ route('desa-cantik.kelola') }}" class="btn btn-primary">
                    <i class="fas fa-cog me-1 fa-spin"></i> Kelola Descan
                </a>
            @endif
        </div>

        <div class="card border-0 shadow-sm rounded-4 bps-card">
            <div class="card-body p-5 text-center">
                <div class="text-muted mb-3">
                    <i class="fas fa-seedling fa-4x text-success"></i>
                </div>
                <h5 class="fw-bold" style="color: var(--bps-orange);">Belum Ada Informasi</h5>
                <p class="text-muted">Halaman ini masih kosong atau konten sedang dalam persiapan.</p>
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
        </style>
    @endpush
@endsection