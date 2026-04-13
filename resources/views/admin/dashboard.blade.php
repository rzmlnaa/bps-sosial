@extends('layouts.admin')

@section('title', 'Dashboard Admin')

@section('content')
    <div class="container-fluid py-4">
        <div class="row g-4 mb-4">
            <!-- Stats Cards -->
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="bg-primary bg-opacity-10 p-3 rounded-3">
                                <i class="fas fa-users-cog fa-2x text-primary"></i>
                            </div>
                        </div>
                        <h6 class="text-muted fw-medium mb-1">Total Pengguna</h6>
                        <h2 class="fw-bold mb-0 counter-value" data-target="{{ $stats['users_count'] }}">0</h2>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="bg-info bg-opacity-10 p-3 rounded-3">
                                <i class="fas fa-user-shield fa-2x text-info"></i>
                            </div>
                        </div>
                        <h6 class="text-muted fw-medium mb-1">Total Admin</h6>
                        <h2 class="fw-bold mb-0 counter-value" data-target="{{ $stats['admins_count'] }}">0</h2>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="bg-success bg-opacity-10 p-3 rounded-3">
                                <i class="fas fa-map-marked-alt fa-2x text-success"></i>
                            </div>
                        </div>
                        <h6 class="text-muted fw-medium mb-1">Master Wilayah</h6>
                        <h2 class="fw-bold mb-0 counter-value" data-target="{{ $stats['kabupatens_count'] }}">0</h2>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="bg-warning bg-opacity-10 p-3 rounded-3">
                                <i class="fas fa-list-alt fa-2x text-warning"></i>
                            </div>
                        </div>
                        <h6 class="text-muted fw-medium mb-1">Menu Dinamis</h6>
                        <h2 class="fw-bold mb-0 counter-value" data-target="{{ $stats['menus_count'] }}">0</h2>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Latest Users -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-transparent border-0 pt-4 px-4">
                        <div class="d-flex align-items-center justify-content-between">
                            <h5 class="fw-bold mb-0">Pengguna Terbaru</h5>
                            <a href="{{ route('admin.users') }}" class="btn btn-sm btn-light rounded-pill px-3">Semua</a>
                        </div>
                    </div>
                    <div class="card-body px-0 px-md-4">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="border-0 rounded-start">Nama</th>
                                        <th class="border-0">Kabupaten</th>
                                        <th class="border-0 rounded-end text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($latestUsers as $user)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="bg-light rounded-circle p-2 me-2">
                                                        <i class="fas fa-user text-muted small"></i>
                                                    </div>
                                                    <div>
                                                        <span class="d-block fw-semibold">{{ $user->name }}</span>
                                                        <small class="text-muted">{{ $user->email }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ $user->kabupaten->nama_kabupaten ?? '-' }}</td>
                                            <td class="text-center">
                                                @if($user->status === 'active')
                                                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3">Aktif</span>
                                                @elseif($user->status === 'pending')
                                                    <span class="badge bg-warning bg-opacity-10 text-warning rounded-pill px-3">Pending</span>
                                                @else
                                                    <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3">Tolak</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center py-4 text-muted">Belum ada data</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Latest Admins -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-transparent border-0 pt-4 px-4">
                        <div class="d-flex align-items-center justify-content-between">
                            <h5 class="fw-bold mb-0">Admin Terbaru</h5>
                            <a href="{{ route('admin.admins') }}" class="btn btn-sm btn-light rounded-pill px-3">Semua</a>
                        </div>
                    </div>
                    <div class="card-body px-0 px-md-4">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="border-0 rounded-start">Nama</th>
                                        <th class="border-0 rounded-end">Email</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($latestAdmins as $admin)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="bg-info bg-opacity-10 rounded-circle p-2 me-2">
                                                        <i class="fas fa-user-shield text-info small"></i>
                                                    </div>
                                                    <span class="fw-semibold">{{ $admin->name }}</span>
                                                </div>
                                            </td>
                                            <td>{{ $admin->email }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="text-center py-4 text-muted">Belum ada data</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Master Wilayah Summary -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-transparent border-0 pt-4 px-4">
                        <div class="d-flex align-items-center justify-content-between">
                            <h5 class="fw-bold mb-0">Wilayah (Kabupaten/Kota)</h5>
                            <a href="{{ route('admin.kabupatens') }}" class="btn btn-sm btn-light rounded-pill px-3">Semua</a>
                        </div>
                    </div>
                    <div class="card-body px-0 px-md-4">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="border-0 rounded-start">Kode</th>
                                        <th class="border-0">Nama Kabupaten</th>
                                        <th class="border-0 rounded-end">Oleh</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($kabupatens as $kab)
                                        <tr>
                                            <td><span class="badge bg-light text-dark fw-bold px-3">{{ $kab->kode_kab }}</span></td>
                                            <td>{{ $kab->nama_kabupaten }}</td>
                                            <td>
                                                <small class="text-muted d-block">{{ $kab->userAdd->name ?? '-' }}</small>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center py-4 text-muted">Belum ada data</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Latest Menus -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-transparent border-0 pt-4 px-4">
                        <div class="d-flex align-items-center justify-content-between">
                            <h5 class="fw-bold mb-0">Menu Dinamis Terbaru</h5>
                            <a href="{{ route('admin.dynamic-menus.index') }}" class="btn btn-sm btn-light rounded-pill px-3">Semua</a>
                        </div>
                    </div>
                    <div class="card-body px-0 px-md-4">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="border-0 rounded-start">Nama Menu</th>
                                        <th class="border-0">Tipe</th>
                                        <th class="border-0 rounded-end">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($latestMenus as $menu)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="bg-warning bg-opacity-10 rounded-circle p-2 me-2">
                                                        <i class="fas fa-file-alt text-warning small"></i>
                                                    </div>
                                                    <span class="fw-semibold">{{ $menu->name }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="text-capitalize small bg-gray-100 px-2 py-1 rounded">{{ $menu->type }}</span>
                                            </td>
                                            <td>
                                                @if($menu->is_active)
                                                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3">Aktif</span>
                                                @else
                                                    <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-3">Non-aktif</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center py-4 text-muted">Belum ada data</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
         document.addEventListener('DOMContentLoaded', function () {
            const counters = document.querySelectorAll('.counter-value');
            const duration = 1000; // Total animation time in ms (2 seconds)

            counters.forEach(counter => {
                const target = +counter.getAttribute('data-target');
                if (target === 0) return;

                let startTimestamp = null;
                const step = (timestamp) => {
                    if (!startTimestamp) startTimestamp = timestamp;
                    const progress = Math.min((timestamp - startTimestamp) / duration, 1);
                    const currentCount = Math.floor(progress * target);

                    counter.innerText = currentCount.toLocaleString('id-ID');

                    if (progress < 1) {
                        window.requestAnimationFrame(step);
                    } else {
                        counter.innerText = target.toLocaleString('id-ID');
                    }
                };
                window.requestAnimationFrame(step);
            });
        });
    </script>
@endsection