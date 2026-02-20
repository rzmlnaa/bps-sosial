@extends('layouts.admin')

@section('title', 'Fenomena')

@section('content')
    <div class="fade-in-up">
        <!-- Header -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
            <div>
                <h2 class="fw-bold mb-1" style="color: var(--bps-orange);">FENOMENA</h2>
                <p class="text-muted mb-0">Fenomena Sosial Ekonomi & Kejadian Penting</p>
            </div>
            
            <div class="mt-3 mt-md-0">
                <a href="{{ route('fenomena.create') }}" class="btn btn-success text-white shadow-sm"
                    style="border: none; border-radius: 8px;">
                    <i class="fas fa-plus me-2"></i>Input Fenomena
                </a>
            </div>
        </div>

        <!-- Filters Section (Sample) -->
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; background: #fff;">
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-12 mb-2">
                        <label class="small text-uppercase fw-bold text-muted ls-1">Filter Data</label>
                        <p class="small text-muted mb-0">Filter fenomena berdasarkan tahun atau bulan kejadian.</p>
                    </div>

                    <!-- Year -->
                    <div class="col-md-2">
                        <label class="form-label small fw-bold">Tahun</label>
                        <select id="filterYear" class="form-select form-select-sm">
                            <option value="">Semua Tahun</option>
                            <option value="2024" selected>2024</option>
                            <option value="2023">2023</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty State (Since no DB yet) -->
        <div class="card border-0 shadow-sm" style="border-radius: 12px; min-height: 400px;">
            <div class="card-body p-5 text-center d-flex flex-column justify-content-center align-items-center">
                <div class="bg-light rounded-circle p-4 mb-3">
                    <i class="fas fa-newspaper fa-3x text-secondary opacity-50"></i>
                </div>
                <h5 class="fw-bold text-dark">Daftar Fenomena</h5>
                <p class="text-muted mb-0 max-w-md">Belum ada data fenomena yang tersimpan.</p>
                <p class="text-muted small">Silakan klik tombol <strong>Input Fenomena</strong> untuk menambahkan data baru.</p>
            </div>
        </div>
    </div>
@endsection