@extends('layouts.admin')

@section('title', 'Input Data Seruti')

@section('content')
    <div class="fade-in-up">
        <div class="mb-4">
            <h2 class="fw-bold mb-1" style="color: var(--bps-orange);">Input Data Seruti</h2>
            <p class="text-muted mb-0">Form input data konsumsi Seruti</p>
        </div>

        <div class="card border-0 shadow-sm" style="border-radius: 12px;">
            <div class="card-body">
                <p>Form input akan ditampilkan di sini.</p>
                <a href="{{ route('seruti.index') }}" class="btn btn-secondary">Kembali</a>
            </div>
        </div>
    </div>
@endsection