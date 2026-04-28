@extends('layouts.admin')

@section('title', 'Progress Kegiatan Desa Cantik')

@section('content')
    <div class="mt-2 fade-in-up">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
            <div>
                <h2 class="fw-bold mb-1" style="color: var(--bps-orange);">Progress Kegiatan Desa Cantik</h2>
                <p class="text-muted mb-0">Pantau dan update progress kegiatan desa peserta</p>
            </div>
        </div>

        {{-- Filter --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-3">
                <form action="{{ route('desa-cantik.progress') }}" method="GET" class="row g-3 align-items-center">
                    <div class="col-md-4">
                        <select name="periode_id" class="form-select border-0 bg-light rounded-pill" onchange="this.form.submit()">
                            <option value="">-- Semua Periode --</option>
                            @foreach ($periodes as $p)
                                <option value="{{ $p->id }}" {{ request('periode_id') == $p->id ? 'selected' : '' }}>
                                    Tahun {{ $p->tahun }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-auto ms-auto">
                        <button type="submit" class="btn btn-orange rounded-pill px-4">
                            <i class="fas fa-filter me-1"></i> Filter
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Grid Peserta --}}
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="px-4 py-3" width="5%">No</th>
                                <th class="py-3">Desa / Kecamatan</th>
                                @if($isProvinsi)
                                    <th class="py-3">Kabupaten</th>
                                @endif
                                <th class="py-3">Tahun</th>
                                <th class="py-3 text-center">Status Progress</th>
                                <th class="px-4 py-3 text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pesertas as $index => $p)
                                @php
                                    $completedCount = $p->progresses->where('status', 2)->count();
                                    $totalKegiatan = $kegiatans->count();
                                    $percent = $totalKegiatan > 0 ? round(($completedCount / $totalKegiatan) * 100) : 0;
                                @endphp
                                <tr>
                                    <td class="px-4 py-3">{{ $pesertas->firstItem() + $index }}</td>
                                    <td class="py-3">
                                        <div class="fw-bold text-navy">{{ $p->desa->nama_desa }}</div>
                                        <small class="text-muted">{{ $p->kecamatan->nama_kecamatan }}</small>
                                    </td>
                                    @if($isProvinsi)
                                        <td class="py-3">
                                            <span class="badge bg-light text-dark border">{{ $p->kabupaten->nama_kabupaten }}</span>
                                        </td>
                                    @endif
                                    <td class="py-3">{{ $p->periode->tahun }}</td>
                                    <td class="py-3" style="min-width: 250px;">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="flex-grow-1">
                                                <div class="progress" style="height: 8px; border-radius: 4px;">
                                                    <div class="progress-bar bg-success" role="progressbar" 
                                                        style="width: {{ $percent }}%" 
                                                        aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100"></div>
                                                </div>
                                            </div>
                                            <span class="fw-bold small text-navy">{{ $percent }}%</span>
                                        </div>
                                        <div class="mt-2 d-flex flex-wrap gap-1">
                                            @foreach($kegiatans as $keg)
                                                @php
                                                    $prog = $p->progresses->where('kegiatan_id', $keg->id)->first();
                                                    $statusClass = 'bg-light text-muted';
                                                    if($prog) {
                                                        if($prog->status == 1) $statusClass = 'bg-warning text-dark';
                                                        elseif($prog->status == 2) $statusClass = 'bg-success text-white';
                                                        elseif($prog->status == 3) $statusClass = 'bg-danger text-white';
                                                    }
                                                @endphp
                                                <span class="badge {{ $statusClass }}" style="font-size: 0.6rem; opacity: 0.8;" 
                                                      title="{{ $keg->nama_kegiatan }}: {{ $prog ? ($prog->status == 1 ? 'Menunggu Verifikasi' : ($prog->status == 2 ? 'Terverifikasi' : 'Ditolak')) : 'Belum' }}">
                                                    {{ $loop->iteration }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-end">
                                        <a href="{{ route('desa-cantik.progress.detail', $p->id) }}" class="btn btn-sm btn-outline-orange rounded-pill px-3">
                                            <i class="fas fa-edit me-1"></i> Update Progress
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $isProvinsi ? 6 : 5 }}" class="text-center py-5 text-muted">
                                        <i class="fas fa-tasks fa-3x mb-3 opacity-20"></i>
                                        <p class="mb-0">Belum ada data peserta desa.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-4">
                    {{ $pesertas->links() }}
                </div>
            </div>
        </div>

        {{-- Legend --}}
        <div class="mt-4 card border-0 shadow-sm rounded-4 bg-light">
            <div class="card-body p-3 small">
                <span class="fw-bold me-2 text-navy">Keterangan Status Kegiatan:</span>
                <span class="me-3"><span class="badge bg-light text-muted border me-1">1</span> Belum</span>
                <span class="me-3"><span class="badge bg-warning text-dark me-1">1</span> Menunggu Verifikasi</span>
                <span class="me-3"><span class="badge bg-success text-white me-1">1</span> Terverifikasi</span>
                <span><span class="badge bg-danger text-white me-1">1</span> Ditolak / Perbaikan</span>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .btn-orange {
            background-color: var(--bps-orange);
            border-color: var(--bps-orange);
            color: #fff;
        }
        .btn-orange:hover {
            background-color: #e6661a;
            border-color: #e6661a;
            color: #fff;
        }
        .btn-outline-orange {
            color: var(--bps-orange);
            border-color: var(--bps-orange);
        }
        .btn-outline-orange:hover {
            background-color: var(--bps-orange);
            color: #fff;
        }
    </style>
@endpush
