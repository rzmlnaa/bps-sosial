@extends('layouts.admin')

@section('title', 'Manajemen Admin')

@section('content')
    <style>
        .hover-elevate {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .hover-elevate:hover {
            transform: translateY(-4px);
            box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.08) !important;
        }
    </style>
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold text-navy mb-0">Manajemen Admin</h2>
            <button type="button" class="btn btn-primary bg-navy border-0 rounded-pill px-4" data-bs-toggle="modal"
                data-bs-target="#addAdminModal">
                <i class="fas fa-plus me-2"></i>Tambah Admin
            </button>
        </div>


        <div class="row g-3">
            @forelse($admins as $admin)
                <div class="col-12">
                    <div class="card h-100 shadow-sm border-0 rounded-4 hover-elevate">
                        <div class="card-body p-4">
                            <div class="row align-items-center gy-3">
                                <!-- Info Kiri -->
                                <div class="col-md-4">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="bg-light rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                            style="width: 50px; height: 50px;">
                                            <i class="fas fa-user-shield text-navy fa-lg"></i>
                                        </div>
                                        <div>
                                            <h5 class="fw-bold text-dark mb-0">{{ $admin->name }}</h5>
                                            <small class="text-muted d-block mt-1">
                                                <i class="fas fa-phone-alt me-1 fa-xs"></i>
                                                {{ $admin->no_hp ?? '-' }}
                                            </small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Info Tengah -->
                                <div class="col-md-5">
                                    <div class="d-flex flex-column gap-2 border-start ps-md-4 border-light">
                                        <div class="d-flex align-items-center text-muted">
                                            <i class="fas fa-envelope me-2 fa-fw"></i>
                                            {{ $admin->email }}
                                        </div>
                                        <div class="mt-1">
                                            <span
                                                class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill">Active</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Info Kanan -->
                                <div class="col-md-3">
                                    <div
                                        class="d-flex flex-column align-items-md-end justify-content-center h-100 ps-md-4 border-start border-light">
                                        <div class="text-muted small mb-1 text-end">
                                            Login: <span
                                                class="fw-medium text-dark">{{ $admin->last_login_at?->format('d M Y H:i') ?? '-' }}</span>
                                        </div>
                                        <div class="text-muted small text-end">
                                            Bergabung: {{ $admin->created_at?->format('d M Y') ?? '-' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-body p-5 text-center text-muted">
                            <div class="mb-3"><i class="fas fa-users-slash fa-3x opacity-25"></i></div>
                            Belum ada data admin lain.
                        </div>
                    </div>
                </div>
            @endforelse
        </div>

    </div>

    <!-- Modal Tambah Admin -->
    <div class="modal fade" id="addAdminModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold text-navy">Tambah Admin Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form action="{{ route('admin.admins.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-medium">Nama Lengkap</label>
                            <input type="text" name="name" class="form-control" required placeholder="Nama Admin">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-medium">Email</label>
                            <input type="email" name="email" class="form-control" required placeholder="admin@example.com">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-medium">Nomor HP</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="fas fa-phone text-muted"></i>
                                </span>
                                <input type="text" name="no_hp" class="form-control border-start-0 ps-0" required
                                    placeholder="628..." oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                            </div>
                            <small class="text-muted d-block mt-1" style="font-size: 0.8rem;">
                                *Wajib diawali <strong>62</strong> (Contoh: 628123456789)
                            </small>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary bg-navy border-0 py-2 rounded-3">
                                <i class="fas fa-save me-2"></i> Simpan Admin
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection