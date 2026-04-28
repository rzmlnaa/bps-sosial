@extends('layouts.admin')

@section('title', 'Detail Progress ' . $peserta->desa->nama_desa)

@section('content')
    <div class="mt-2 fade-in-up pb-5">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
            <div>
                <a href="{{ route('desa-cantik.progress') }}" class="btn btn-light btn-sm mb-2 rounded-pill">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
                <h2 class="fw-bold mb-1" style="color: var(--bps-orange);">
                    {{ $peserta->desa->nama_desa }}
                </h2>
                <p class="text-muted mb-0">
                    Kec. {{ $peserta->kecamatan->nama_kecamatan }}, {{ $peserta->kabupaten->nama_kabupaten }} | Periode {{ $peserta->periode->tahun }}
                </p>
            </div>
            <div class="mt-3 mt-md-0">
                @php
                    $completedCount = $progresses->where('status', 2)->count();
                    $totalKegiatan = $kegiatans->count();
                    $percent = $totalKegiatan > 0 ? round(($completedCount / $totalKegiatan) * 100) : 0;
                @endphp
                <div class="card border-0 shadow-sm rounded-4 p-2 px-3 bg-white d-flex flex-row align-items-center gap-3">
                    <div class="text-end">
                        <small class="text-muted d-block">Overall Progress</small>
                        <span class="fw-bold fs-4" style="color: var(--bps-orange);">{{ $percent }}%</span>
                    </div>
                    <div style="width: 60px;">
                        <svg viewBox="0 0 36 36" class="circular-chart orange">
                            <path class="circle-bg" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                            <path class="circle" stroke-dasharray="{{ $percent }}, 100" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="timeline">
                    @foreach($kegiatans as $keg)
                        @php
                            $prog = $progresses->get($keg->id);
                            $isFirst = $loop->first;
                            $prevKeg = $loop->first ? null : $kegiatans[$loop->index - 1];
                            $prevProg = $prevKeg ? $progresses->get($prevKeg->id) : null;
                            $isLocked = !$isFirst && (!$prevProg || $prevProg->status != 2);
                            
                            $statusLabel = 'Belum Dimulai';
                            $statusColor = 'secondary';
                            if($prog) {
                                if($prog->status == 1) { $statusLabel = 'Menunggu Verifikasi'; $statusColor = 'warning'; }
                                elseif($prog->status == 2) { $statusLabel = 'Terverifikasi'; $statusColor = 'success'; }
                                elseif($prog->status == 3) { $statusLabel = 'Perbaikan'; $statusColor = 'danger'; }
                            }
                        @endphp
                        
                        <div class="timeline-item pb-5">
                            <div class="timeline-marker {{ $prog && $prog->status == 2 ? 'bg-success' : ($isLocked ? 'bg-light border' : 'bg-orange-light text-bps-orange') }}">
                                @if($prog && $prog->status == 2)
                                    <i class="fas fa-check"></i>
                                @else
                                    {{ $loop->iteration }}
                                @endif
                            </div>
                            
                            <div class="timeline-content">
                                <div class="card border-0 shadow-sm rounded-4 {{ $isLocked ? 'opacity-75' : '' }}">
                                    <div class="card-header bg-white border-bottom-0 pt-3 px-4 d-flex justify-content-between align-items-center">
                                        <h5 class="fw-bold mb-0 {{ $isLocked ? 'text-muted' : 'text-navy' }}">
                                            {{ $keg->nama_kegiatan }}
                                        </h5>
                                        <span class="badge bg-{{ $statusColor }} rounded-pill">{{ $statusLabel }}</span>
                                    </div>
                                    
                                    <div class="card-body px-4 pb-4">
                                        @if($isLocked)
                                            <div class="alert alert-light border-0 small mb-0 py-2">
                                                <i class="fas fa-lock me-1"></i> Selesaikan kegiatan <strong>{{ $prevKeg->nama_kegiatan }}</strong> terlebih dahulu.
                                            </div>
                                        @else
                                            @if(!$prog || $prog->status == 3)
                                                <form action="{{ route('desa-cantik.progress.store', $peserta->id) }}" method="POST" class="mt-3">
                                                    @csrf
                                                    <input type="hidden" name="kegiatan_id" value="{{ $keg->id }}">
                                                    
                                                    <div class="row g-3">
                                                        <div class="col-md-6">
                                                            <label class="form-label small fw-bold">Target Tanggal</label>
                                                            <input type="date" name="target_tanggal" id="target_{{ $keg->id }}" class="form-control" value="{{ $prog ? $prog->target_tanggal : '' }}" onchange="updateMinDate({{ $keg->id }})">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label small fw-bold">Realisasi Tanggal</label>
                                                            <input type="date" name="realisasi_tanggal" id="realisasi_{{ $keg->id }}" class="form-control" value="{{ $prog ? $prog->realisasi_tanggal : '' }}" min="{{ $prog ? $prog->target_tanggal : '' }}">
                                                        </div>
                                                        <div class="col-12">
                                                            <label class="form-label small fw-bold">Bukti Kegiatan (Link/Teks)</label>
                                                            <div id="bukti-container-{{ $keg->id }}">
                                                                <div class="d-flex gap-2 mb-2">
                                                                    <select name="jenis_bukti_id[]" class="form-select w-50">
                                                                        <option value="">Pilih Jenis Bukti</option>
                                                                        @foreach($jenisBukti as $jb)
                                                                            <option value="{{ $jb->id }}">{{ $jb->nama_bukti }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                    <input type="text" name="bukti_link[]" class="form-control" placeholder="Link folder atau deskripsi bukti...">
                                                                </div>
                                                            </div>
                                                            <button type="button" class="btn btn-sm btn-link text-decoration-none p-0 text-bps-orange" onclick="addFileField({{ $keg->id }})">
                                                                <i class="fas fa-plus-circle me-1"></i> Tambah Bukti Lainnya
                                                            </button>
                                                        </div>
                                                        <div class="col-12">
                                                            <button type="submit" class="btn btn-orange rounded-pill px-4">
                                                                <i class="fas fa-paper-plane me-1"></i> Kirim Progress
                                                            </button>
                                                        </div>
                                                    </div>
                                                </form>
                                            @else
                                                {{-- View mode --}}
                                                <div class="row mt-3 g-3">
                                                    <div class="col-md-6">
                                                        <small class="text-muted d-block">Target</small>
                                                        <div class="fw-bold">{{ $prog->target_tanggal ? date('d M Y', strtotime($prog->target_tanggal)) : '-' }}</div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <small class="text-muted d-block">Realisasi</small>
                                                        <div class="fw-bold">{{ $prog->realisasi_tanggal ? date('d M Y', strtotime($prog->realisasi_tanggal)) : '-' }}</div>
                                                    </div>
                                                    <div class="col-12">
                                                        <small class="text-muted d-block">Bukti Kegiatan</small>
                                                        <div class="d-flex flex-wrap gap-2 mt-1">
                                                            @forelse($prog->buktis as $bukti)
                                                                @php
                                                                    $isUrl = filter_var($bukti->link_file, FILTER_VALIDATE_URL);
                                                                @endphp
                                                                @if($isUrl)
                                                                    <a href="{{ $bukti->link_file }}" target="_blank" class="btn btn-sm btn-light border rounded-pill">
                                                                        <i class="fas fa-link me-1"></i> {{ $bukti->jenisBukti->nama_bukti }}
                                                                    </a>
                                                                @else
                                                                    <span class="badge bg-light text-dark border p-2 rounded-pill fw-normal">
                                                                        <i class="fas fa-file-alt me-1 text-muted"></i> {{ $bukti->jenisBukti->nama_bukti }}: {{ $bukti->link_file }}
                                                                    </span>
                                                                @endif
                                                            @empty
                                                                <span class="text-muted small italic">Tidak ada bukti.</span>
                                                            @endforelse
                                                        </div>
                                                    </div>
                                                </div>

                                                @if($isProvinsi && $prog->status == 1)
                                                    <div class="mt-4 pt-3 border-top">
                                                        <div class="d-flex gap-2">
                                                            <form action="{{ route('desa-cantik.progress.verify', [$peserta->id, $prog->id]) }}" method="POST">
                                                                @csrf
                                                                <input type="hidden" name="action" value="approve">
                                                                <button type="submit" class="btn btn-success btn-sm rounded-pill px-3">
                                                                    <i class="fas fa-check-circle me-1"></i> Setujui
                                                                </button>
                                                            </form>
                                                            <form action="{{ route('desa-cantik.progress.verify', [$peserta->id, $prog->id]) }}" method="POST">
                                                                @csrf
                                                                <input type="hidden" name="action" value="reject">
                                                                <button type="submit" class="btn btn-danger btn-sm rounded-pill px-3">
                                                                    <i class="fas fa-times-circle me-1"></i> Tolak / Perbaikan
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                @endif
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 sticky-top" style="top: 2rem;">
                    <div class="card-header bg-white border-0 pt-4 px-4">
                        <h5 class="fw-bold mb-0">Rincian Desa</h5>
                    </div>
                    <div class="card-body px-4 pb-4">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-3">
                                <small class="text-muted d-block">Kode Desa</small>
                                <span class="fw-bold">{{ $peserta->desa->kode_desa }}</span>
                            </li>
                            <li class="mb-3">
                                <small class="text-muted d-block">Didaftarkan Oleh</small>
                                <span class="fw-bold">{{ $peserta->creator->name ?? '-' }}</span>
                                <small class="text-muted">({{ $peserta->created_at->format('d/m/Y') }})</small>
                            </li>
                        </ul>
                        
                        <div class="alert alert-warning border-0 small mt-4">
                            <i class="fas fa-info-circle me-1"></i>
                            Progress harus diisi secara berurutan. Admin Provinsi akan melakukan verifikasi pada setiap tahapan kegiatan.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@push('scripts')
    <script>
        function addFileField(kegId) {
            const container = document.getElementById(`bukti-container-${kegId}`);
            const firstRow = container.querySelector('.d-flex');
            const newRow = firstRow.cloneNode(true);
            
            // Clear values
            newRow.querySelector('select').value = '';
            newRow.querySelector('input[type="text"]').value = '';
            
            container.appendChild(newRow);
        }

        function updateMinDate(kegId) {
            const targetVal = document.getElementById(`target_${kegId}`).value;
            const realisasiInput = document.getElementById(`realisasi_${kegId}`);
            realisasiInput.min = targetVal;
            
            // If current realisasi is before new target, clear it
            if (realisasiInput.value && realisasiInput.value < targetVal) {
                realisasiInput.value = '';
            }
        }
    </script>
@endpush
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
            color: #fff;
        }
        
        /* Timeline Styles */
        .timeline {
            position: relative;
            padding-left: 2.5rem;
        }
        .timeline::before {
            content: '';
            position: absolute;
            left: 0.9rem;
            top: 0;
            height: 100%;
            width: 2px;
            background: #eee;
        }
        .timeline-item {
            position: relative;
        }
        .timeline-marker {
            position: absolute;
            left: -2.5rem;
            width: 2rem;
            height: 2rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            font-weight: bold;
            z-index: 1;
        }
        .bg-orange-light {
            background-color: rgba(243, 112, 33, 0.1);
        }
        .text-bps-orange {
            color: var(--bps-orange);
        }
        
        /* Circular Chart */
        .circular-chart {
            display: block;
            margin: 0 auto;
            max-width: 100%;
            max-height: 250px;
        }
        .circle-bg {
            fill: none;
            stroke: #eee;
            stroke-width: 3.8;
        }
        .circle {
            fill: none;
            stroke-width: 2.8;
            stroke-linecap: round;
            animation: progress 1s ease-out forwards;
        }
        @keyframes progress {
            0% { stroke-dasharray: 0 100; }
        }
        .circular-chart.orange .circle {
            stroke: var(--bps-orange);
        }
        .text-navy {
            color: var(--primary-navy, #1e293b);
        }
    </style>
@endpush
