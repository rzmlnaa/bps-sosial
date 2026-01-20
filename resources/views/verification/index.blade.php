@extends('layouts.admin')

@section('title', 'Verifikasi Rentang Harga')

@section('content')
    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col-12">
                <h1 class="h3 mb-2 text-gray-800">Verifikasi Rentang Harga</h1>
                <p class="text-muted">Daftar Kabupaten/Kota dengan data rentang harga yang perlu diverifikasi.</p>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="px-4 py-3 border-0">Kode</th>
                                <th class="px-4 py-3 border-0">Kabupaten/Kota</th>
                                <th class="px-4 py-3 border-0 text-center">Jumlah Pending</th>
                                <th class="px-4 py-3 border-0 text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($kabupatens as $kab)
                                <tr>
                                    <td class="px-4 fw-medium text-muted">{{ $kab->kode_kab }}</td>
                                    <td class="px-4 fw-bold text-dark">{{ $kab->nama_kabupaten }}</td>
                                    <td class="px-4 text-center">
                                        <span class="badge bg-warning text-dark rounded-pill px-3">
                                            {{ $kab->pending_count }} Item
                                        </span>
                                    </td>
                                    <td class="px-4 text-end">
                                        <a href="{{ route('verification.show', $kab->id) }}"
                                            class="btn btn-primary btn-sm rounded-pill px-4">
                                            <i class="fas fa-search me-1"></i> Periksa
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-5 text-muted">
                                        <div class="d-flex flex-column align-items-center">
                                            <i class="fas fa-check-circle text-success fa-3x mb-3"></i>
                                            <h5 class="fw-medium">Semua Bersih!</h5>
                                            <p class="mb-0">Tidak ada data yang perlu verifikasi saat ini.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection