@extends('layouts.admin')

@section('title', 'Manajemen Admin')

@section('content')
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold text-navy mb-0">Manajemen Admin</h2>
            <button type="button" class="btn btn-primary bg-navy border-0 rounded-pill px-4" data-bs-toggle="modal"
                data-bs-target="#addAdminModal">
                <i class="fas fa-plus me-2"></i>Tambah Admin
            </button>
        </div>

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="px-4 py-3 border-0">Nama</th>
                                <th class="px-4 py-3 border-0">Email</th>
                                <th class="px-4 py-3 border-0">No. HP</th>
                                <th class="px-4 py-3 border-0">Status</th>
                                <th class="px-4 py-3 border-0">Login Terakhir</th>
                                <th class="px-4 py-3 border-0">Bergabung</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($admins as $admin)
                                <tr>
                                    <td class="px-4 py-3">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="bg-light rounded-circle d-flex align-items-center justify-content-center"
                                                style="width: 40px; height: 40px;">
                                                <i class="fas fa-user-shield text-navy"></i>
                                            </div>
                                            <div class="fw-bold text-dark">{{ $admin->name }}</div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-muted">{{ $admin->email }}</td>
                                    <td class="px-4 py-3 text-muted">{{ $admin->no_hp ?? '-' }}</td>
                                    <td class="px-4 py-3">
                                        <span
                                            class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill">Active</span>
                                    </td>
                                    <td class="px-4 py-3 text-muted">
                                        {{ $admin->last_login_at?->format('d M Y H:i') ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-muted">
                                        {{ $admin->created_at?->format('d M Y') ?? '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <div class="mb-2"><i class="fas fa-users-slash fa-3x opacity-25"></i></div>
                                        Belum ada data admin lain.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
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