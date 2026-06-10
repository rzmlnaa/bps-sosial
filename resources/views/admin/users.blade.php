@extends('layouts.admin')

@section('title', 'Akun Pengguna')

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
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
        <div>
            <h2 class="fw-bold mb-1" style="color: var(--bps-orange);">Akun Pengguna</h2>
            <p class="text-muted mb-0">Manajemen Akun Pengguna Kab/Kot</p>
        </div>
    </div>


    <!-- Filters -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3">
            <form action="{{ route('admin.users') }}" method="GET" class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Cari Nama / Email..."
                        value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="kabupaten_id" class="form-select">
                        <option value="">-- Semua Kabupaten --</option>
                        @foreach($kabupatens as $kab)
                            <option value="{{ $kab->id }}" {{ request('kabupaten_id') == $kab->id ? 'selected' : '' }}>
                                [{{ $kab->kode_kab }}] {{ $kab->nama_kabupaten }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">-- Semua Status --</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>
                <div class="col-md-2 d-grid">
                    <button type="submit" class="btn btn-primary bg-navy border-0">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3">
        @forelse($users as $user)
            <div class="col-12">
                <div class="card h-100 shadow-sm border-0 rounded-4 hover-elevate">
                    <div class="card-body p-4">
                        <div class="row align-items-center gy-3">
                            <!-- Info Kiri -->
                            <div class="col-md-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="bg-light rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                        style="width: 50px; height: 50px;">
                                        <i class="fas fa-user text-navy fa-lg"></i>
                                    </div>
                                    <div>
                                        <h5 class="fw-bold text-dark mb-0">{{ $user->name }}</h5>
                                        <small class="text-muted d-block mt-1">
                                            <i class="fas fa-phone-alt me-1 fa-xs"></i>
                                            {{ $user->no_hp ?? '-' }}
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <!-- Info Tengah -->
                            <div class="col-md-5">
                                <div class="d-flex flex-column gap-2 border-start ps-md-4 border-light">
                                    <div class="d-flex align-items-center text-muted">
                                        <i class="fas fa-envelope me-2 fa-fw"></i>
                                        {{ $user->email }}
                                    </div>
                                    <div class="d-flex align-items-center text-muted">
                                        <i class="fas fa-building me-2 fa-fw"></i>
                                        @if($user->kabupaten)
                                            [{{ $user->kabupaten->kode_kab }}] {{ $user->kabupaten->nama_kabupaten }}
                                        @else
                                            {{ $user->team ?? '-' }}
                                        @endif
                                    </div>
                                    <div class="mt-1">
                                        @if($user->status === 'active')
                                            <span
                                                class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill">Active</span>
                                        @elseif($user->status === 'pending')
                                            <span
                                                class="badge bg-warning bg-opacity-10 text-warning px-3 py-2 rounded-pill">Pending</span>
                                            @if($user->otp_code != null)
                                                <span
                                                    class="badge bg-danger bg-opacity-10 text-danger px-3 py-2 rounded-pill ms-1">Verif
                                                    WA</span>
                                            @endif
                                        @elseif($user->status === 'rejected')
                                            <span
                                                class="badge bg-danger bg-opacity-10 text-danger px-3 py-2 rounded-pill">Rejected</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Info Kanan & Aksi -->
                            <div class="col-md-3">
                                <div
                                    class="d-flex flex-column align-items-md-end justify-content-center h-100 ps-md-4 border-start border-light">
                                    <div class="text-muted small mb-3 text-end">
                                        Login: <span
                                            class="fw-medium text-dark">{{ $user->last_login_at?->format('d M Y H:i') ?? '-' }}</span>
                                    </div>
                                    <div class="d-flex gap-2">
                                        @if($user->status === 'pending' && $user->otp_code == null)
                                            <form action="{{ route('admin.approve', $user->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success rounded-pill px-3"
                                                    title="Terima">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                            <form action="{{ route('admin.reject', $user->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-danger rounded-pill px-3" title="Tolak">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </form>
                                        @elseif($user->status === 'active')
                                            <form action="{{ route('admin.make-pending', $user->id) }}" method="POST"
                                                class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-warning rounded-pill px-3 text-white"
                                                    title="Jadikan Pending">
                                                    <i class="fas fa-clock"></i>
                                                </button>
                                            </form>
                                        @endif

                                        <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST"
                                            class="d-inline"
                                            onsubmit="return confirm('Apakah Anda yakin ingin menghapus pengguna ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="btn btn-sm btn-outline-danger rounded-circle p-0 d-flex align-items-center justify-content-center"
                                                style="width: 32px; height: 32px;" title="Hapus Pengguna">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
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
                        Tidak ada data pengguna yang ditemukan.
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination for Users -->
    <div class="mt-4 d-flex justify-content-end">
        {{ $users->links('pagination::bootstrap-5') }}
    </div>

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4 gap-3 mt-5">
        <h2 class="fw-bold text-navy mb-0">Profil Pengguna Belum Diselesaikan</h2>
    </div>
    <div class="row g-3">
        @forelse($userTidakFinalPofile as $user)
            <div class="col-12">
                <div class="card h-100 shadow-sm border-0 rounded-4 hover-elevate">
                    <div class="card-body p-4">
                        <div class="row align-items-center gy-3">
                            <!-- Info Kiri -->
                            <div class="col-md-5">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="bg-light rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                        style="width: 50px; height: 50px;">
                                        <i class="fas fa-user-clock text-warning fa-lg"></i>
                                    </div>
                                    <div>
                                        <h5 class="fw-bold text-dark mb-0">{{ $user->name }}</h5>
                                        <small class="text-muted d-block mt-1">
                                            <i class="fas fa-phone-alt me-1 fa-xs"></i>
                                            {{ $user->no_hp ?? '-' }}
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <!-- Info Tengah -->
                            <div class="col-md-4">
                                <div class="d-flex flex-column gap-1 border-start ps-md-4 border-light">
                                    <div class="d-flex align-items-center text-muted">
                                        <i class="fas fa-envelope me-2 fa-fw"></i>
                                        {{ $user->email }}
                                    </div>
                                    <div class="mt-1">
                                        <span
                                            class="badge bg-secondary bg-opacity-10 text-secondary px-3 py-2 rounded-pill">Belum
                                            Selesai</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Aksi -->
                            <div class="col-md-3">
                                <div
                                    class="d-flex flex-column align-items-md-end justify-content-center h-100 ps-md-4 border-start border-light">
                                    <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="d-inline"
                                        onsubmit="return confirm('Apakah Anda yakin ingin menghapus pengguna ini? Tindakan ini tidak dapat dibatalkan.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-4 py-2"
                                            title="Hapus Pengguna">
                                            <i class="fas fa-trash-alt me-2"></i> Hapus
                                        </button>
                                    </form>
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
                        Tidak ada data pengguna yang ditemukan.
                    </div>
                </div>
            </div>
        @endforelse
    </div>

@endsection