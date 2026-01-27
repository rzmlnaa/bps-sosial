@extends('layouts.admin')

@section('title', 'Akun Pengguna')

@section('content')
    <div class="container-fluid py-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4 gap-3">
            <h2 class="fw-bold text-navy mb-0">Akun Pengguna</h2>
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
                                    {{ $kab->nama_kabupaten }}
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

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="px-4 py-3 border-0">Nama Lengkap</th>
                                <th class="px-4 py-3 border-0">Email</th>
                                <th class="px-4 py-3 border-0">Tim / Asal</th>
                                <th class="px-4 py-3 border-0">Status</th>
                                <th class="px-4 py-3 border-0 text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                                <tr>
                                    <td class="px-4 py-3">
                                        <div class="fw-bold text-dark">{{ $user->name }}</div>
                                        <small class="text-muted">{{ $user->no_hp ?? '-' }}</small>
                                    </td>
                                    <td class="px-4 py-3 text-muted">{{ $user->email }}</td>
                                    <td class="px-4 py-3">
                                        @if($user->kabupaten)
                                            {{ $user->kabupaten->nama_kabupaten }}
                                        @else
                                            {{ $user->team ?? '-' }}
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        @if($user->status === 'active')
                                            <span
                                                class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill">Active</span>
                                        @elseif($user->status === 'pending')
                                            <span
                                                class="badge bg-warning bg-opacity-10 text-warning px-3 py-2 rounded-pill">Pending</span>
                                            @if($user->otp_code != null)
                                                <br><span
                                                    class="badge bg-danger mt-2 bg-opacity-10 text-danger px-3 py-2 rounded-pill">Sedang
                                                    Verifikasi WA</span>
                                            @endif
                                        @elseif($user->status === 'rejected')
                                            <span
                                                class="badge bg-danger bg-opacity-10 text-danger px-3 py-2 rounded-pill">Rejected</span>
                                            @if($user->otp_code != null)
                                                <br><span
                                                    class="badge bg-danger mt-2 bg-opacity-10 text-danger px-3 py-2 rounded-pill">Sedang
                                                    Verifikasi WA</span>
                                            @endif
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-end">

                                        @if($user->status === 'pending' && $user->otp_code == null)
                                            <form action="{{ route('admin.approve', $user->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success rounded-pill px-3 me-1"
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
                                                    <i class="fas fa-clock me-1"></i> Pending-kan
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <div class="mb-2"><i class="fas fa-users-slash fa-3x opacity-25"></i></div>
                                        Tidak ada data pengguna yang ditemukan.
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