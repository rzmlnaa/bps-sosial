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
                                    $totalUnits = 0;
                                    $filledUnits = 0;
                                    $totalMandatory = 0;
                                    $filledMandatory = 0;
                                    
                                    $mandatoryBuktiCount = count($mandatoryBuktiIds);
                                    
                                    // 1. Kegiatan
                                    foreach($kegiatans as $keg) {
                                        $unitsInThisKeg = (2 + $mandatoryBuktiCount);
                                        $totalUnits += $unitsInThisKeg;
                                        if($keg->is_wajib) $totalMandatory += $unitsInThisKeg;

                                        $prog = $p->progresses->where('kegiatan_id', $keg->id)->first();
                                        if ($prog) {
                                            $filledInThisKeg = 0;
                                            if ($prog->target_tanggal) $filledInThisKeg++;
                                            if ($prog->realisasi_tanggal) $filledInThisKeg++;
                                            foreach($mandatoryBuktiIds as $mid) {
                                                $bukti = $prog->buktis->where('jenis_bukti_id', $mid)->first();
                                                if ($bukti && !empty($bukti->link_file)) $filledInThisKeg++;
                                            }
                                            
                                            $filledUnits += $filledInThisKeg;
                                            if($keg->is_wajib) $filledMandatory += $filledInThisKeg;
                                        }
                                    }

                                    // 2. Output
                                    $totalUnits += count($mandatoryOutputIds);
                                    $totalMandatory += count($mandatoryOutputIds);
                                    foreach($mandatoryOutputIds as $oid) {
                                        $out = $p->outputs->where('jenis_output_id', $oid)->first();
                                        if ($out && !empty($out->link)) {
                                            $filledUnits++;
                                            $filledMandatory++;
                                        }
                                    }

                                    // 3. Bukti Dukung
                                    $totalUnits += count($mandatoryDukungIds);
                                    $totalMandatory += count($mandatoryDukungIds);
                                    foreach($mandatoryDukungIds as $did) {
                                        $duk = $p->buktiDukungs->where('jenis_bukti_id', $did)->first();
                                        if ($duk && !empty($duk->link_file)) {
                                            $filledUnits++;
                                            $filledMandatory++;
                                        }
                                    }

                                    $percent = $totalUnits > 0 ? round(($filledUnits / $totalUnits) * 100) : 0;
                                    $mandatoryPercent = $totalMandatory > 0 ? round(($filledMandatory / $totalMandatory) * 100) : 0;
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
                                        {{-- Progress Wajib --}}
                                        <div class="mb-2">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <span class="small fw-bold text-navy">Progress Wajib</span>
                                                <span class="fw-bold small {{ $mandatoryPercent == 100 ? 'text-success' : 'text-orange' }}">{{ $mandatoryPercent }}%</span>
                                            </div>
                                            <div class="progress" style="height: 6px; border-radius: 3px;">
                                                <div class="progress-bar {{ $mandatoryPercent == 100 ? 'bg-success' : 'bg-orange' }}" role="progressbar" 
                                                    style="width: {{ $mandatoryPercent }}%" 
                                                    aria-valuenow="{{ $mandatoryPercent }}" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </div>

                                        {{-- Total Progress --}}
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <div class="flex-grow-1">
                                                <div class="progress" style="height: 4px; border-radius: 2px; background-color: #e9ecef;">
                                                    <div class="progress-bar bg-secondary opacity-50" role="progressbar" 
                                                        style="width: {{ $percent }}%" 
                                                        aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100"></div>
                                                </div>
                                            </div>
                                            <span class="text-muted" style="font-size: 0.7rem;">Total: {{ $percent }}%</span>
                                        </div>

                                        <div class="mt-2 d-flex flex-wrap gap-1">
                                            @foreach($kegiatans as $keg)
                                                @php
                                                    $prog = $p->progresses->where('kegiatan_id', $keg->id)->first();
                                                    $statusClass = 'bg-light text-muted';
                                                    if($prog) {
                                                        if($prog->status == 'draf') $statusClass = 'bg-info text-white';
                                                        elseif($prog->status == 'menunggu_verifikasi') $statusClass = 'bg-warning text-dark';
                                                        elseif($prog->status == 'disetujui') $statusClass = 'bg-success text-white';
                                                        elseif($prog->status == 'ditolak') $statusClass = 'bg-danger text-white';
                                                    }
                                                @endphp
                                                <span class="badge {{ $statusClass }} {{ $keg->is_wajib ? 'border border-secondary' : '' }}" 
                                                      style="font-size: 0.6rem; opacity: 0.8; {{ $keg->is_wajib ? 'position: relative;' : '' }}" 
                                                      title="{{ $keg->nama_kegiatan }} ({{ $keg->is_wajib ? 'Wajib' : 'Opsional' }}): {{ $prog ? ($prog->status == 'draf' ? 'Draf' : ($prog->status == 'menunggu_verifikasi' ? 'Menunggu Verifikasi' : ($prog->status == 'disetujui' ? 'Terverifikasi' : 'Ditolak'))) : 'Belum' }}">
                                                    {{ $loop->iteration }}
                                                    @if($keg->is_wajib)
                                                        <span style="position: absolute; top: -5px; right: -2px; color: red; font-size: 0.5rem;">*</span>
                                                    @endif
                                                </span>
                                            @endforeach
                                        </div>

                                        {{-- Output Indicators --}}
                                        <div class="mt-1 d-flex flex-wrap gap-1 align-items-center">
                                            <span class="small text-muted me-1" style="font-size: 0.6rem;">Out:</span>
                                            @foreach($allJenisOutputs as $jo)
                                                @php
                                                    $out = $p->outputs->where('jenis_output_id', $jo->id)->first();
                                                    $statusClass = 'bg-light text-muted';
                                                    if($out) {
                                                        if($out->status == 'draf') $statusClass = 'bg-info text-white';
                                                        elseif($out->status == 'menunggu_verifikasi') $statusClass = 'bg-warning text-dark';
                                                        elseif($out->status == 'disetujui') $statusClass = 'bg-success text-white';
                                                        elseif($out->status == 'ditolak') $statusClass = 'bg-danger text-white';
                                                    }
                                                @endphp
                                                <span class="badge {{ $statusClass }} {{ $jo->is_wajib ? 'border border-primary' : '' }}" 
                                                      style="font-size: 0.55rem; opacity: 0.8; {{ $jo->is_wajib ? 'position: relative;' : '' }}" 
                                                      title="{{ $jo->nama_output }} ({{ $jo->is_wajib ? 'Wajib' : 'Opsional' }}): {{ $out ? ($out->status == 'draf' ? 'Draf' : ($out->status == 'menunggu_verifikasi' ? 'Menunggu Verifikasi' : ($out->status == 'disetujui' ? 'Terverifikasi' : 'Ditolak'))) : 'Belum' }}">
                                                    O{{ $loop->iteration }}
                                                    @if($jo->is_wajib)
                                                        <span style="position: absolute; top: -5px; right: -2px; color: red; font-size: 0.5rem;">*</span>
                                                    @endif
                                                </span>
                                            @endforeach
                                        </div>

                                        {{-- Bukti Lainnya Indicators --}}
                                        <div class="mt-1 d-flex flex-wrap gap-1 align-items-center">
                                            <span class="small text-muted me-1" style="font-size: 0.6rem;">Bukti:</span>
                                            @foreach($allJenisDukungs as $jd)
                                                @php
                                                    $duk = $p->buktiDukungs->where('jenis_bukti_id', $jd->id)->first();
                                                    $statusClass = 'bg-light text-muted';
                                                    if($duk) {
                                                        if($duk->status == 'draf') $statusClass = 'bg-info text-white';
                                                        elseif($duk->status == 'menunggu_verifikasi') $statusClass = 'bg-warning text-dark';
                                                        elseif($duk->status == 'disetujui') $statusClass = 'bg-success text-white';
                                                        elseif($duk->status == 'ditolak') $statusClass = 'bg-danger text-white';
                                                    }
                                                @endphp
                                                <span class="badge {{ $statusClass }} {{ $jd->is_wajib ? 'border border-info' : '' }}" 
                                                      style="font-size: 0.55rem; opacity: 0.8; {{ $jd->is_wajib ? 'position: relative;' : '' }}" 
                                                      title="{{ $jd->nama_bukti }} ({{ $jd->is_wajib ? 'Wajib' : 'Opsional' }}): {{ $duk ? ($duk->status == 'draf' ? 'Draf' : ($duk->status == 'menunggu_verifikasi' ? 'Menunggu Verifikasi' : ($duk->status == 'disetujui' ? 'Terverifikasi' : 'Ditolak'))) : 'Belum' }}">
                                                    B{{ $loop->iteration }}
                                                    @if($jd->is_wajib)
                                                        <span style="position: absolute; top: -5px; right: -2px; color: red; font-size: 0.5rem;">*</span>
                                                    @endif
                                                </span>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-end">
                                        @php
                                            $isPending = $p->progresses->where('status', 'menunggu_verifikasi')->isNotEmpty() || 
                                                        $p->outputs->where('status', 'menunggu_verifikasi')->isNotEmpty() || 
                                                        $p->buktiDukungs->where('status', 'menunggu_verifikasi')->isNotEmpty();
                                        @endphp

                                        @if($isPending)
                                            @if($isProvinsi)
                                                <a href="{{ route('desa-cantik.progress.detail', $p->id) }}" class="btn btn-sm btn-warning rounded-pill px-3">
                                                    <i class="fas fa-check-circle me-1"></i> Verifikasi Progress
                                                </a>
                                            @else
                                                <a href="{{ route('desa-cantik.progress.detail', $p->id) }}" class="btn btn-sm btn-info rounded-pill px-3">
                                                    <i class="fas fa-eye me-1"></i> Lihat Progress
                                                </a>
                                            @endif
                                        @else
                                            <a href="{{ route('desa-cantik.progress.detail', $p->id) }}" class="btn btn-sm btn-outline-orange rounded-pill px-3">
                                                <i class="fas fa-edit me-1"></i> Update Progress
                                            </a>
                                        @endif
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
                <span class="me-3"><span class="badge bg-info text-white me-1">1</span> Draf</span>
                <span class="me-3"><span class="badge bg-warning text-dark me-1">1</span> Menunggu Verifikasi</span>
                <span class="me-3"><span class="badge bg-success text-white me-1">1</span> Terverifikasi</span>
                <span class="me-3"><span class="badge bg-danger text-white me-1">1</span> Ditolak / Perbaikan</span>
                <span class="me-3"><span class="badge bg-light text-muted border border-secondary me-1" style="position: relative;">1<span style="position: absolute; top: -5px; right: -2px; color: red; font-size: 0.5rem;">*</span></span> Wajib</span>
                
                <div class="mt-2 pt-2 border-top">
                    <span class="fw-bold me-2 text-navy">Keterangan Label:</span>
                    <span class="me-3"><span class="badge bg-secondary text-white me-1">1</span> Kegiatan</span>
                    <span class="me-3"><span class="badge bg-secondary text-white me-1">O1</span> Output</span>
                    <span class="me-3"><span class="badge bg-secondary text-white me-1">B1</span> Bukti Lainnya</span>
                </div>
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
        .bg-orange {
            background-color: var(--bps-orange) !important;
        }
        .text-orange {
            color: var(--bps-orange) !important;
        }
    </style>
@endpush
