@extends('layouts.admin')

@section('title', 'Verifikasi Akun')

@section('content')
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold text-navy mb-0">Verifikasi Akun Pengguna</h2>
        </div>

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="px-4 py-3 border-0">Nama Lengkap</th>
                                <th class="px-4 py-3 border-0">Email</th>
                                <th class="px-4 py-3 border-0">No. HP</th>
                                <th class="px-4 py-3 border-0">Tim / Asal</th>
                                <th class="px-4 py-3 border-0">Status</th>
                                <th class="px-4 py-3 border-0 text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pendingUsers as $user)
                                <tr>
                                    <td class="px-4 py-3">
                                        <div class="fw-bold text-dark">{{ $user->name }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-muted">{{ $user->email }}</td>
                                    <td class="px-4 py-3 text-muted">{{ $user->no_hp ?? '-' }}</td>
                                    <td class="px-4 py-3">
                                        @if($user->kabupaten)
                                            {{ $user->kabupaten->nama_kabupaten }}
                                        @else
                                            {{ $user->team ?? '-' }}
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="badge bg-warning text-dark bg-opacity-25 px-3 py-2 rounded-pill">
                                            {{ ucfirst($user->status) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-end">
                                        <form action="{{ route('admin.approve', $user->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success rounded-pill px-3 me-1">
                                                <i class="fas fa-check me-1"></i> Terima
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.reject', $user->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-danger rounded-pill px-3">
                                                <i class="fas fa-times me-1"></i> Tolak
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <div class="mb-2"><i class="fas fa-user-check fa-3x opacity-25"></i></div>
                                        Tidak ada pengguna yang menunggu verifikasi.
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