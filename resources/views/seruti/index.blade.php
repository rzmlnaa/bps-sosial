@extends('layouts.admin')

@section('title', 'Data Seruti')

@section('content')
    <div class="fade-in-up">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
            <div>
                <h2 class="fw-bold mb-1" style="color: var(--bps-orange);">Data Seruti</h2>
                <p class="text-muted mb-0">Halaman Pengolahan Data Seruti</p>
            </div>

            <div class="mt-3 mt-md-0">
                @if (auth()->check() && auth()->user()->can('access-admin') || (auth()->user()->status == 'active' && auth()->user()->kabupaten->kode_kab == '6100'))
                    <a href="{{ route('seruti.create') }}" class="btn btn-success text-white"
                        style="background-color: var(--bps-green); border: none;">
                        <i class="fas fa-plus me-2"></i>input data
                    </a>
                @endif
            </div>
        </div>

        <div class="card border-0 shadow-sm" style="border-radius: 12px;">
            <div class="card-body p-5 text-center text-muted">
                <i class="fas fa-chart-bar fa-3x mb-3 text-secondary"></i>
                <h5>Belum ada data yang ditampilkan</h5>
                <p>Silakan input data terlebih dahulu.</p>
            </div>
        </div>
    </div>
@endsection