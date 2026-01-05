@extends('layouts.admin')

@section('title', 'Rentang Harga - BPS Kalbar')

@section('content')
    <div class="fade-in-up">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
            <div>
                <h2 class="fw-bold mb-1" style="color: var(--bps-orange);">Rentang Harga</h2>
                <p class="text-muted mb-0">Visualisasi data rentang harga komoditas di Kalimantan Barat</p>
            </div>
            <div class="mt-3 mt-md-0">
                <a href="{{ route('price-range.input') }}" class="btn text-white fw-bold shadow-sm"
                    style="background-color: var(--bps-blue);">
                    <i class="fas fa-plus-circle me-1"></i> Input Komoditas
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card border-0 shadow-sm p-5 text-center" style="border-radius: 12px;">
                    <i class="fas fa-chart-line mb-3 text-muted" style="font-size: 4rem;"></i>
                    <h4 class="fw-bold">Visualisasi Data Rentang Harga</h4>
                    <p class="text-muted">Data visualisasi akan muncul di sini setelah data komoditas diinput.</p>
                </div>
            </div>
        </div>
    </div>
@endsection