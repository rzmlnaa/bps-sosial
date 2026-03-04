@extends('layouts.admin')

@section('title', 'Verifikasi Rentang Harga')

@push('styles')
    <style>
        .stat-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 1.25rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .05);
            transition: all 0.3s ease;
            height: 100%;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, .08);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            flex-shrink: 0;
        }

        .stat-icon.warning {
            background: #fffbeb;
            color: #d97706;
        }

        .stat-icon.success {
            background: #f0fdf4;
            color: #16a34a;
        }

        .stat-icon.danger {
            background: #fef2f2;
            color: #dc2626;
        }

        .stat-info {
            flex: 1;
            min-width: 0;
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            line-height: 1.2;
            color: #1e293b;
        }

        .stat-label {
            font-size: .85rem;
            color: #64748b;
            font-weight: 500;
        }
    </style>
@endpush

@section('content')
    <div class="fade-in-up">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
            <div>
                <h2 class="fw-bold mb-1" style="color: var(--bps-orange);">Verifikasi Rentang Harga</h2>
                <p class="text-muted mb-0">Daftar Kabupaten/Kota dengan data rentang harga yang perlu diverifikasi.</p>
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
                                <th class="px-4 py-3 border-0 text-center">Jumlah Rejected</th>
                                <th class="px-4 py-3 border-0 text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($kabupatens as $kab)
                                <tr>
                                    <td class="px-4 fw-medium text-muted">{{ $kab->kode_kab }}</td>
                                    <td class="px-4 fw-bold text-dark">{{ $kab->nama_kabupaten }}</td>
                                    <td class="px-4 text-center">
                                        @if($kab->pending_count > 0)
                                            <span class="badge bg-warning text-dark rounded-pill px-3">
                                                {{ $kab->pending_count }} Item
                                            </span>
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                    </td>
                                    <td class="px-4 text-center">
                                        @if($kab->rejected_count > 0)
                                            <span class="badge bg-danger text-white rounded-pill px-3">
                                                {{ $kab->rejected_count }} Item
                                            </span>
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
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
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <img src="https://cdn.dribbble.com/userupload/22333996/file/original-6ac4030147adbe5d9381c4600c79eccb.gif"
                                            alt=""
                                            style="display:block; margin:0 auto; width:100%; max-width:500px; mix-blend-mode: multiply; filter: brightness(1.05) contrast(1.1);"
                                            background="transparent">
                                        <div class="d-flex flex-column align-items-center">
                                            <!-- <i class="fas fa-check-circle text-success fa-3x mb-3"></i> -->
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