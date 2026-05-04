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
            </div>
        </div>

        @php
            $year = $peserta->periode->tahun;
            $minYear = $year . '-01-01';
            $maxYear = $year . '-12-31';
        @endphp
        <form action="{{ route('desa-cantik.progress.store', $peserta->id) }}" method="POST" id="form-progress">
            <input type="hidden" name="action_type" id="action_type" value="draf">
            @csrf
            <div class="row">
                <div class="col-lg-8">

                    <div class="timeline">
                    @foreach($kegiatans as $keg)
                        @php
                            $prog = $progresses->get($keg->id);
                            $isLocked = false; // Always unlocked now
                            
                            $statusLabel = 'Belum Dimulai';
                            $statusColor = 'secondary';
                            if($prog) {
                                if($prog->status == 'draf') { $statusLabel = 'Draf'; $statusColor = 'info'; }
                                elseif($prog->status == 'menunggu_verifikasi') { $statusLabel = 'Menunggu Verifikasi'; $statusColor = 'warning'; }
                                elseif($prog->status == 'disetujui') { $statusLabel = 'Terverifikasi'; $statusColor = 'success'; }
                                elseif($prog->status == 'ditolak') { $statusLabel = 'Ditolak'; $statusColor = 'danger'; }
                            }
                        @endphp
                        
                        <div class="timeline-item pb-5">
                            <div class="timeline-marker {{ $prog && $prog->status == 'disetujui' ? 'bg-success' : ($isLocked ? 'bg-light border' : 'bg-orange-light text-bps-orange') }}">
                                @if($prog && $prog->status == 'disetujui')
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
                                            @if($keg->is_wajib)
                                                <span class="text-danger" title="Wajib diisi">*</span>
                                            @endif
                                        </h5>
                                        <span class="badge bg-{{ $statusColor }} rounded-pill">{{ $statusLabel }}</span>
                                    </div>
                                    
                                    <div class="card-body px-4 pb-4">
                                        @if(!$prog || $prog->status == 'draf' || $prog->status == 'ditolak')
                                            <div class="mt-3">
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label small fw-bold">Target Tanggal @if($keg->is_wajib)<span class="text-danger">*</span>@endif</label>
                                                        <input type="date" name="progress[{{ $keg->id }}][target_tanggal]" id="target_{{ $keg->id }}" 
                                                               class="form-control" value="{{ $prog ? $prog->target_tanggal : '' }}" 
                                                               min="{{ $minYear }}" max="{{ $maxYear }}"
                                                               onchange="updateMinDate({{ $keg->id }}); updateNextActivityMinDate({{ $keg->id }});">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small fw-bold">
                                                            Realisasi Tanggal 
                                                            <span class="text-danger mandatory-star-{{ $keg->id }}" 
                                                                  data-is-keg-wajib="{{ $keg->is_wajib ? 'true' : 'false' }}"
                                                                  style="{{ ($keg->is_wajib || ($prog && $prog->target_tanggal)) ? '' : 'display: none;' }}">*</span>
                                                        </label>
                                                        <input type="date" name="progress[{{ $keg->id }}][realisasi_tanggal]" id="realisasi_{{ $keg->id }}" 
                                                               class="form-control" value="{{ $prog ? $prog->realisasi_tanggal : '' }}" 
                                                               min="{{ $prog && $prog->target_tanggal ? $prog->target_tanggal : $minYear }}" max="{{ $maxYear }}"
                                                               onchange="updateNextActivityMinDate({{ $keg->id }}); checkMandatoryFilled()">
                                                    </div>
                                                    <div class="col-12">
                                                        <label class="form-label small fw-bold mb-2">Bukti Kegiatan</label>
                                                        <div id="bukti-container-{{ $keg->id }}">
                                                            {{-- Bukti Mandatory (Tampil sebagai input tetap) --}}
                                                            @foreach($jenisBuktiMandatory as $mb)
                                                                @php
                                                                    $existingMb = $prog ? $prog->buktis->where('jenis_bukti_id', $mb->id)->first() : null;
                                                                @endphp
                                                                 <div class="mb-3 p-3 bg-light rounded-4 border-start border-4 border-orange">
                                                                    <label class="small fw-bold d-block mb-1 text-navy">
                                                                        {{ $mb->nama_bukti }} 
                                                                        <span class="text-danger mandatory-star-{{ $keg->id }}" 
                                                                              data-is-keg-wajib="{{ $keg->is_wajib ? 'true' : 'false' }}"
                                                                              style="{{ ($keg->is_wajib || ($prog && $prog->target_tanggal)) ? '' : 'display: none;' }}">*</span>
                                                                    </label>
                                                                    <input type="hidden" name="bukti[{{ $keg->id }}][jenis_id][]" value="{{ $mb->id }}">
                                                                     <input type="text" name="bukti[{{ $keg->id }}][link][]" class="form-control mandatory-bukti-input-{{ $keg->id }}" 
                                                                         value="{{ $existingMb ? $existingMb->link_file : '' }}" 
                                                                         data-placeholder="Masukkan link folder atau deskripsi bukti {{ $mb->nama_bukti }}..."
                                                                         placeholder="Masukkan link folder atau deskripsi bukti {{ $mb->nama_bukti }}..."
                                                                         oninput="checkMandatoryFilled()">
                                                                </div>
                                                            @endforeach

                                                            {{-- Bukti Optional yang sudah ada --}}
                                                            @if($prog)
                                                                @foreach($prog->buktis->whereIn('jenis_bukti_id', $jenisBuktiOptional->pluck('id')) as $ob)
                                                                    <div class="d-flex gap-2 mb-2">
                                                                        <select name="bukti[{{ $keg->id }}][jenis_id][]" class="form-select w-50">
                                                                            @foreach($jenisBuktiOptional as $jb)
                                                                                <option value="{{ $jb->id }}" {{ $ob->jenis_bukti_id == $jb->id ? 'selected' : '' }}>
                                                                                    {{ $jb->nama_bukti }}
                                                                                </option>
                                                                            @endforeach
                                                                        </select>
                                                                        <input type="text" name="bukti[{{ $keg->id }}][link][]" class="form-control" value="{{ $ob->link_file }}" data-placeholder="Link folder atau deskripsi bukti...">
                                                                        <button type="button" class="btn btn-sm btn-outline-danger border-0" onclick="this.parentElement.remove()"><i class="fas fa-trash"></i></button>
                                                                    </div>
                                                                @endforeach
                                                            @endif

                                                            {{-- Input baru untuk Bukti Optional --}}
                                                            <div class="d-flex gap-2 mb-2">
                                                                <select name="bukti[{{ $keg->id }}][jenis_id][]" class="form-select w-50">
                                                                    <option value="">+ Tambah Bukti Lainnya</option>
                                                                    @foreach($jenisBuktiOptional as $jb)
                                                                        <option value="{{ $jb->id }}">{{ $jb->nama_bukti }}</option>
                                                                    @endforeach
                                                                </select>
                                                                 <input type="text" name="bukti[{{ $keg->id }}][link][]" class="form-control" 
                                                                     data-placeholder="Link folder atau deskripsi bukti..."
                                                                     placeholder="Link folder atau deskripsi bukti...">
                                                            </div>
                                                        </div>
                                                        <button type="button" class="btn btn-sm btn-link text-decoration-none p-0 text-bps-orange mt-1" onclick="addFileField({{ $keg->id }})">
                                                            <i class="fas fa-plus-circle me-1"></i> Tambah Baris Bukti
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
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

                                                @if($prog->status == 'ditolak' && $prog->alasan_penolakan)
                                                    <div class="alert alert-danger border-0 rounded-4 mt-3 small mb-0">
                                                        <i class="fas fa-exclamation-circle me-1"></i>
                                                        <strong>Alasan Penolakan:</strong> {{ $prog->alasan_penolakan }}
                                                    </div>
                                                @endif

                                                @if($isProvinsi && $prog->status == 'menunggu_verifikasi')
                                                    <div class="mt-4 pt-3 border-top">
                                                        <div class="d-flex gap-2">
                                                            <form id="verify-form-{{ $prog->id }}" action="{{ route('desa-cantik.progress.verify', [$peserta->id, $prog->id]) }}" method="POST">
                                                                @csrf
                                                                <input type="hidden" name="action" id="action-{{ $prog->id }}" value="approve">
                                                                <input type="hidden" name="alasan_penolakan" id="alasan-{{ $prog->id }}" value="">
                                                                <button type="submit" class="btn btn-success btn-sm rounded-pill px-3">
                                                                    <i class="fas fa-check-circle me-1"></i> Setujui
                                                                </button>
                                                                <button type="button" onclick="rejectProgress({{ $prog->id }})" class="btn btn-danger btn-sm rounded-pill px-3 ms-1">
                                                                    <i class="fas fa-times-circle me-1"></i> Tolak / Perbaikan
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                @endif
                                            @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    </div> {{-- End Timeline --}}

                    {{-- Section: Output --}}
                    <div class="card border-0 shadow-sm rounded-4 mt-4 mb-4">
                        <div class="card-header bg-white border-bottom-0 pt-4 px-4">
                            <h5 class="fw-bold mb-0 text-navy"><i class="fas fa-file-export me-2"></i>Output (Link bukti dukung)</h5>
                        </div>
                        <div class="card-body px-4 pb-4">
                            <div id="output-container">
                                {{-- Mandatory Output --}}
                                @foreach($jenisOutputMandatory as $jo)
                                    @php $out = $peserta->outputs->where('jenis_output_id', $jo->id)->first(); @endphp
                                    <div class="mb-3 p-3 bg-light rounded-4 border-start border-4 border-primary">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <label class="small fw-bold text-navy mb-0">{{ $jo->nama_output }} <span class="text-danger">*</span></label>
                                            @if($out && $out->status)
                                                @php
                                                    $sLabel = 'Draf'; $sColor = 'info';
                                                    if($out->status == 'menunggu_verifikasi') { $sLabel = 'Menunggu Verifikasi'; $sColor = 'warning'; }
                                                    elseif($out->status == 'disetujui') { $sLabel = 'Terverifikasi'; $sColor = 'success'; }
                                                    elseif($out->status == 'ditolak') { $sLabel = 'Ditolak'; $sColor = 'danger'; }
                                                @endphp
                                                <span class="badge bg-{{ $sColor }} rounded-pill" style="font-size: 0.65rem;">{{ $sLabel }}</span>
                                            @endif
                                        </div>
                                        <input type="hidden" name="output_jenis_id[]" value="{{ $jo->id }}">
                                        <input type="text" name="output_link[]" class="form-control mandatory-output-input" 
                                               value="{{ $out ? $out->link : '' }}" placeholder="Link {{ $jo->nama_output }}..."
                                               oninput="checkMandatoryFilled()">
                                        @if($out && $out->status == 'ditolak' && $out->alasan_penolakan)
                                            <small class="text-danger mt-1 d-block" style="font-size: 0.75rem;">
                                                <i class="fas fa-exclamation-circle me-1"></i><strong>Alasan:</strong> {{ $out->alasan_penolakan }}
                                            </small>
                                        @endif
                                    </div>
                                @endforeach
                                
                                <label class="small fw-bold d-block mb-2 text-muted mt-4">Output Lainnya (Opsional)</label>
                                {{-- Existing Optional Output --}}
                                @foreach($peserta->outputs->whereIn('jenis_output_id', $jenisOutputOptional->pluck('id')) as $out)
                                    <div class="mb-3">
                                        <div class="d-flex gap-2 mb-1 align-items-center">
                                            <select name="output_jenis_id[]" class="form-select w-50">
                                                <option value="">Pilih Jenis Output...</option>
                                                @foreach($jenisOutputOptional as $jo)
                                                    <option value="{{ $jo->id }}" {{ $out->jenis_output_id == $jo->id ? 'selected' : '' }}>{{ $jo->nama_output }}</option>
                                                @endforeach
                                            </select>
                                            <input type="text" name="output_link[]" class="form-control" value="{{ $out->link }}" placeholder="Link output...">
                                            @if($out && $out->status)
                                                @php
                                                    $sLabel = 'Draf'; $sColor = 'info';
                                                    if($out->status == 'menunggu_verifikasi') { $sLabel = 'Menunggu Verifikasi'; $sColor = 'warning'; }
                                                    elseif($out->status == 'disetujui') { $sLabel = 'Terverifikasi'; $sColor = 'success'; }
                                                    elseif($out->status == 'ditolak') { $sLabel = 'Ditolak'; $sColor = 'danger'; }
                                                @endphp
                                                <span class="badge bg-{{ $sColor }} rounded-pill" style="font-size: 0.65rem;">{{ $sLabel }}</span>
                                            @endif
                                            <button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="this.closest('.mb-3').remove()"><i class="fas fa-trash"></i></button>
                                        </div>
                                        @if($out && $out->status == 'ditolak' && $out->alasan_penolakan)
                                            <small class="text-danger d-block" style="font-size: 0.75rem; margin-left: 2px;">
                                                <i class="fas fa-exclamation-circle me-1"></i><strong>Alasan:</strong> {{ $out->alasan_penolakan }}
                                            </small>
                                        @endif
                                    </div>
                                @endforeach

                                {{-- Always show one empty optional row --}}
                                <div class="mb-3 generic-row">
                                    <div class="d-flex gap-2 mb-1 align-items-center">
                                        <select name="output_jenis_id[]" class="form-select w-50">
                                            <option value="">Pilih Jenis Output...</option>
                                            @foreach($jenisOutputOptional as $jo)
                                                <option value="{{ $jo->id }}">{{ $jo->nama_output }}</option>
                                            @endforeach
                                        </select>
                                         <input type="text" name="output_link[]" class="form-control" placeholder="Link output...">
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-link text-decoration-none p-0 text-bps-orange mt-1" onclick="addGenericField('output-container')">
                                <i class="fas fa-plus-circle me-1"></i> Tambah Output Lainnya
                            </button>
                        </div>
                    </div>

                    {{-- Section: Bukti Lainnya --}}
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-header bg-white border-bottom-0 pt-4 px-4">
                            <h5 class="fw-bold mb-0 text-navy"><i class="fas fa-folder-plus me-2"></i>Bukti Lainnya</h5>
                        </div>
                        <div class="card-body px-4 pb-4">
                            <div id="dukung-container">
                                {{-- Mandatory Dukung --}}
                                @foreach($jenisDukungMandatory as $jd)
                                    @php $duk = $peserta->buktiDukungs->where('jenis_bukti_id', $jd->id)->first(); @endphp
                                    <div class="mb-3 p-3 bg-light rounded-4 border-start border-4 border-info">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <label class="small fw-bold text-navy mb-0">{{ $jd->nama_bukti }} <span class="text-danger">*</span></label>
                                            @if($duk && $duk->status)
                                                @php
                                                    $sLabel = 'Draf'; $sColor = 'info';
                                                    if($duk->status == 'menunggu_verifikasi') { $sLabel = 'Menunggu Verifikasi'; $sColor = 'warning'; }
                                                    elseif($duk->status == 'disetujui') { $sLabel = 'Terverifikasi'; $sColor = 'success'; }
                                                    elseif($duk->status == 'ditolak') { $sLabel = 'Ditolak'; $sColor = 'danger'; }
                                                @endphp
                                                <span class="badge bg-{{ $sColor }} rounded-pill" style="font-size: 0.65rem;">{{ $sLabel }}</span>
                                            @endif
                                        </div>
                                        <input type="hidden" name="dukung_jenis_id[]" value="{{ $jd->id }}">
                                        <input type="text" name="dukung_link[]" class="form-control mandatory-dukung-input" 
                                               value="{{ $duk ? $duk->link_file : '' }}" placeholder="Link {{ $jd->nama_bukti }}..."
                                               oninput="checkMandatoryFilled()">
                                        @if($duk && $duk->status == 'ditolak' && $duk->alasan_penolakan)
                                            <small class="text-danger mt-1 d-block" style="font-size: 0.75rem;">
                                                <i class="fas fa-exclamation-circle me-1"></i><strong>Alasan:</strong> {{ $duk->alasan_penolakan }}
                                            </small>
                                        @endif
                                    </div>
                                @endforeach

                                <label class="small fw-bold d-block mb-2 text-muted mt-4">Bukti Lainnya (Opsional)</label>
                                {{-- Existing Optional Dukung --}}
                                @foreach($peserta->buktiDukungs->whereIn('jenis_bukti_id', $jenisDukungOptional->pluck('id')) as $duk)
                                    <div class="mb-3">
                                        <div class="d-flex gap-2 mb-1 align-items-center">
                                            <select name="dukung_jenis_id[]" class="form-select w-50">
                                                <option value="">Pilih Jenis Bukti...</option>
                                                @foreach($jenisDukungOptional as $jd)
                                                    <option value="{{ $jd->id }}" {{ $duk->jenis_bukti_id == $jd->id ? 'selected' : '' }}>{{ $jd->nama_bukti }}</option>
                                                @endforeach
                                            </select>
                                            <input type="text" name="dukung_link[]" class="form-control" value="{{ $duk->link_file }}" placeholder="Link bukti...">
                                            @if($duk && $duk->status)
                                                @php
                                                    $sLabel = 'Draf'; $sColor = 'info';
                                                    if($duk->status == 'menunggu_verifikasi') { $sLabel = 'Menunggu Verifikasi'; $sColor = 'warning'; }
                                                    elseif($duk->status == 'disetujui') { $sLabel = 'Terverifikasi'; $sColor = 'success'; }
                                                    elseif($duk->status == 'ditolak') { $sLabel = 'Ditolak'; $sColor = 'danger'; }
                                                @endphp
                                                <span class="badge bg-{{ $sColor }} rounded-pill" style="font-size: 0.65rem;">{{ $sLabel }}</span>
                                            @endif
                                            <button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="this.closest('.mb-3').remove()"><i class="fas fa-trash"></i></button>
                                        </div>
                                        @if($duk && $duk->status == 'ditolak' && $duk->alasan_penolakan)
                                            <small class="text-danger d-block" style="font-size: 0.75rem; margin-left: 2px;">
                                                <i class="fas fa-exclamation-circle me-1"></i><strong>Alasan:</strong> {{ $duk->alasan_penolakan }}
                                            </small>
                                        @endif
                                    </div>
                                @endforeach

                                {{-- Always show one empty optional row --}}
                                <div class="mb-3 generic-row">
                                    <div class="d-flex gap-2 mb-1 align-items-center">
                                        <select name="dukung_jenis_id[]" class="form-select w-50">
                                            <option value="">Pilih Jenis Bukti...</option>
                                            @foreach($jenisDukungOptional as $jd)
                                                <option value="{{ $jd->id }}">{{ $jd->nama_bukti }}</option>
                                            @endforeach
                                        </select>
                                         <input type="text" name="dukung_link[]" class="form-control" placeholder="Link bukti...">
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-link text-decoration-none p-0 text-bps-orange mt-1" onclick="addGenericField('dukung-container')">
                                <i class="fas fa-plus-circle me-1"></i> Tambah Bukti Lainnya
                            </button>
                        </div>
                    </div>
                </div> {{-- End Col-lg-8 --}}
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm rounded-4 mb-3 sticky-top" style="top: 2rem;">
                        <div class="card-header bg-white border-0 pt-4 px-4">
                            @php
                                $totalUnits = 0;
                                $filledUnits = 0;
                                
                                // 1. Progress Kegiatan
                                $mandatoryBuktiCount = $jenisBuktiMandatory->count();
                                $mandatoryBuktiIds = $jenisBuktiMandatory->pluck('id')->toArray();
                                
                                foreach($kegiatans as $keg) {
                                    $totalUnits += (2 + $mandatoryBuktiCount);
                                    $prog = $progresses->get($keg->id);
                                    if ($prog) {
                                        if ($prog->target_tanggal) $filledUnits++;
                                        if ($prog->realisasi_tanggal) $filledUnits++;
                                        foreach($mandatoryBuktiIds as $mid) {
                                            $bukti = $prog->buktis->where('jenis_bukti_id', $mid)->first();
                                            if ($bukti && !empty($bukti->link_file)) $filledUnits++;
                                        }
                                    }
                                }

                                // 2. Output
                                $mandatoryOutputIds = $jenisOutputMandatory->pluck('id')->toArray();
                                $totalUnits += count($mandatoryOutputIds);
                                foreach($mandatoryOutputIds as $oid) {
                                    $out = $peserta->outputs->where('jenis_output_id', $oid)->first();
                                    if ($out && !empty($out->link)) $filledUnits++;
                                }

                                // 3. Bukti Dukung (Lainnya)
                                $mandatoryDukungIds = $jenisDukungMandatory->pluck('id')->toArray();
                                $totalUnits += count($mandatoryDukungIds);
                                foreach($mandatoryDukungIds as $did) {
                                    $duk = $peserta->buktiDukungs->where('jenis_bukti_id', $did)->first();
                                    if ($duk && !empty($duk->link_file)) $filledUnits++;
                                }

                                $percent = $totalUnits > 0 ? round(($filledUnits / $totalUnits) * 100) : 0;
                            @endphp

                            <div class="bg-light rounded-4 p-3 mb-4 d-flex align-items-center justify-content-between">
                                <div>
                                    <small class="text-muted d-block">Overall Progress</small>
                                    <span class="fw-bold fs-4" style="color: var(--bps-orange);">{{ $percent }}%</span>
                                </div>
                                <div style="width: 50px;">
                                    <svg viewBox="0 0 36 36" class="circular-chart orange">
                                        <path class="circle-bg" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                                        <path class="circle" stroke-dasharray="{{ $percent }}, 100" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                                    </svg>
                                </div>
                            </div>

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

                            <div class="mt-4 pt-3 border-top">
                                <h6 class="fw-bold small text-navy mb-2"><i class="fas fa-info-circle me-1"></i> Informasi Pengisian</h6>
                                <div class="small text-muted">
                                    <div class="d-flex gap-2 mb-2">
                                        <span class="text-danger fw-bold">*</span>
                                        <span>Field dengan tanda bintang merah bersifat <strong>wajib</strong>. Seluruh field wajib harus dilengkapi sebelum dapat mengajukan verifikasi.</span>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <i class="fas fa-chart-line mt-1" style="color: var(--bps-orange);"></i>
                                        <span><strong>Overall Progress</strong> dihitung berdasarkan persentase kelengkapan isian pada seluruh komponen yang ada.</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="alert alert-info border-0 small mt-4">
                                <i class="fas fa-info-circle me-1"></i>
                                Admin Provinsi akan melakukan verifikasi pada setiap tahapan kegiatan yang telah diajukan.
                            </div>

                            <div class="d-grid gap-2 mt-4">
                                <button type="submit" onclick="document.getElementById('action_type').value='draf'" class="btn btn-light rounded-pill">
                                    <i class="fas fa-save me-1"></i> Simpan Draf
                                </button>
                                <button type="button" id="btn-submit-verifikasi" onclick="confirmSubmit()" class="btn btn-orange rounded-pill">
                                    <i class="fas fa-paper-plane me-1"></i> Ajukan Verifikasi
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@push('scripts')
    <script>
        function addFileField(kegId) {
            const container = document.getElementById(`bukti-container-${kegId}`);
            if (!container) return;
            
            const optionalRows = container.querySelectorAll('.d-flex');
            if (optionalRows.length === 0) return;
            
            const lastRow = optionalRows[optionalRows.length - 1];
            const newRow = lastRow.cloneNode(true);
            
            // Clear values
            const select = newRow.querySelector('select');
            const input = newRow.querySelector('input[type="text"]');
            if (select) select.value = '';
            if (input) {
                input.value = '';
                input.readOnly = false;
                input.style.backgroundColor = '#fff';
                input.style.cursor = 'text';
            }
            
            // Tambahkan tombol hapus jika belum ada
            if (!newRow.querySelector('.fa-trash')) {
                const btnTrash = document.createElement('button');
                btnTrash.type = 'button';
                btnTrash.className = 'btn btn-sm btn-link text-danger p-0';
                btnTrash.innerHTML = '<i class="fas fa-trash"></i>';
                btnTrash.onclick = function() { this.parentElement.remove(); };
                newRow.appendChild(btnTrash);
            } else {
                // Pastikan fungsi hapus bekerja pada baris baru
                const btn = newRow.querySelector('button');
                if (btn) {
                    btn.onclick = function() { this.parentElement.remove(); };
                }
            }

            container.appendChild(newRow);
        }

        function addGenericField(containerId) {
            const container = document.getElementById(containerId);
            if (!container) return;
            
            const rows = container.querySelectorAll('.mb-3');
            if (rows.length === 0) return;
            
            const lastRow = rows[rows.length - 1];
            const newRow = lastRow.cloneNode(true);
            
            // Clear values and remove badges/reasons
            const select = newRow.querySelector('select');
            const input = newRow.querySelector('input[type="text"]');
            if (select) select.value = '';
            if (input) {
                input.value = '';
                input.readOnly = false;
                input.style.backgroundColor = '#fff';
                input.style.cursor = 'text';
                input.placeholder = input.getAttribute('data-placeholder') || input.placeholder;
            }
            
            const badge = newRow.querySelector('.badge');
            if (badge) badge.remove();
            
            const reason = newRow.querySelector('small.text-danger');
            if (reason) reason.remove();
            
            // Tambahkan tombol hapus jika belum ada
            if (!newRow.querySelector('.fa-trash')) {
                const btnTrash = document.createElement('button');
                btnTrash.type = 'button';
                btnTrash.className = 'btn btn-sm btn-link text-danger p-0';
                btnTrash.innerHTML = '<i class="fas fa-trash"></i>';
                btnTrash.onclick = function() { this.closest('.mb-3').remove(); };
                newRow.querySelector('.d-flex').appendChild(btnTrash);
            } else {
                const btn = newRow.querySelector('.fa-trash').parentElement;
                if (btn) {
                    btn.onclick = function() { this.closest('.mb-3').remove(); };
                }
            }
            
            container.appendChild(newRow);
        }

        function updateMinDate(kegId) {
            const targetInput = document.getElementById(`target_${kegId}`);
            if (!targetInput) return;

            const targetVal = targetInput.value;
            const realisasiInput = document.getElementById(`realisasi_${kegId}`);
            const container = document.getElementById(`bukti-container-${kegId}`);
            const btnAdd = container.nextElementSibling; // Tombol "Tambah Baris Bukti"

            const isNoDate = !targetVal;
            const minDate = targetVal || '{{ $minYear }}';

            // Toggle mandatory stars for this activity if it's optional
            const stars = document.querySelectorAll(`.mandatory-star-${kegId}`);
            stars.forEach(star => {
                const isKegWajib = star.getAttribute('data-is-keg-wajib') === 'true';
                if (!isKegWajib) {
                    star.style.display = isNoDate ? 'none' : 'inline';
                }
            });

            if (realisasiInput) {
                realisasiInput.min = minDate;
                realisasiInput.readOnly = isNoDate;
                realisasiInput.style.backgroundColor = isNoDate ? '#f8f9fa' : '#fff';
                realisasiInput.style.cursor = isNoDate ? 'not-allowed' : 'text';

                if (isNoDate) {
                    realisasiInput.value = '';
                } else if (realisasiInput.value && realisasiInput.value < targetVal) {
                    realisasiInput.value = '';
                }
            }

            // Toggle Bukti fields
            if (container) {
                const inputs = container.querySelectorAll('input[type="text"], select');
                inputs.forEach(el => {
                    if (el.tagName === 'SELECT') {
                        el.disabled = isNoDate;
                        if (isNoDate) el.value = '';
                    } else {
                        el.readOnly = isNoDate;
                        el.style.backgroundColor = isNoDate ? '#f8f9fa' : '#fff';
                        el.style.cursor = isNoDate ? 'not-allowed' : 'text';
                        if (isNoDate) {
                            el.value = '';
                            el.placeholder = "Isi tanggal target terlebih dahulu...";
                        } else {
                            el.placeholder = el.getAttribute('data-placeholder') || el.placeholder;
                        }
                    }
                });

                if (btnAdd && btnAdd.classList.contains('btn-link')) {
                    btnAdd.style.display = isNoDate ? 'none' : 'block';
                }
            }
            checkMandatoryFilled();
        }

        const allKegiatanIds = @json($kegiatans->pluck('id'));

        function updateNextActivityMinDate(kegId) {
            const currentIndex = allKegiatanIds.indexOf(parseInt(kegId));
            if (currentIndex === -1 || currentIndex === allKegiatanIds.length - 1) return;

            const nextKegId = allKegiatanIds[currentIndex + 1];
            const nextTarget = document.getElementById(`target_${nextKegId}`);
            
            if (nextTarget) {
                const currentTarget = document.getElementById(`target_${kegId}`);
                const currentRealisasi = document.getElementById(`realisasi_${kegId}`);
                
                const targetVal = currentTarget ? currentTarget.value : null;
                const realisasiVal = currentRealisasi ? currentRealisasi.value : null;

                if (!targetVal) {
                    // Jika target kegiatan sebelumnya kosong, matikan input target kegiatan ini
                    nextTarget.disabled = true;
                    nextTarget.value = '';
                    nextTarget.style.backgroundColor = '#f8f9fa';
                    nextTarget.style.cursor = 'not-allowed';
                    // Trigger cascade clear untuk kegiatan setelahnya lagi
                    updateMinDate(nextKegId);
                    updateNextActivityMinDate(nextKegId);
                } else {
                    nextTarget.disabled = false;
                    nextTarget.style.backgroundColor = '#fff';
                    nextTarget.style.cursor = 'text';

                    // Prioritas: Realisasi (jika ada), jika tidak ada pakai Target
                    const effectiveMin = realisasiVal || targetVal;
                    nextTarget.min = effectiveMin;

                    if (nextTarget.value && nextTarget.value < effectiveMin) {
                        nextTarget.value = '';
                        updateMinDate(nextKegId);
                        updateNextActivityMinDate(nextKegId);
                    }
                }
            }
        }

        const mandatoryKegiatanIds = @json($kegiatans->where('is_wajib', true)->pluck('id'));

        function checkMandatoryFilled() {
            let activityFilled = true;

            // 1. Check ALL activities (Mandatory + Optional that have target filled)
            allKegiatanIds.forEach(id => {
                const target = document.getElementById(`target_${id}`);
                const realisasi = document.getElementById(`realisasi_${id}`);
                const isMandatory = mandatoryKegiatanIds.includes(parseInt(id));

                if (isMandatory) {
                    // Mandatory activity MUST have target and realisasi
                    if (!target || !target.value) activityFilled = false;
                    if (!realisasi || !realisasi.value) activityFilled = false;
                } else {
                    // Optional activity MUST have realisasi IF target is filled
                    if (target && target.value && (!realisasi || !realisasi.value)) {
                        activityFilled = false;
                    }
                }

                // If target is filled (mandatory or optional), MUST check its mandatory bukti
                if (target && target.value) {
                    const mandatoryBuktis = document.querySelectorAll(`.mandatory-bukti-input-${id}`);
                    mandatoryBuktis.forEach(input => {
                        if (!input.value.trim()) activityFilled = false;
                    });
                }
            });

            toggleAdditionalSections(activityFilled);

            let allMandatoryFilled = activityFilled;

            if (activityFilled) {
                // 2. Check Mandatory Output & Bukti Lainnya
                const mandatoryOutputs = document.querySelectorAll('.mandatory-output-input');
                mandatoryOutputs.forEach(input => {
                    if (!input.value.trim()) allMandatoryFilled = false;
                });

                const mandatoryDukungs = document.querySelectorAll('.mandatory-dukung-input');
                mandatoryDukungs.forEach(input => {
                    if (!input.value.trim()) allMandatoryFilled = false;
                });
            }

            const btnSubmit = document.getElementById('btn-submit-verifikasi');
            if (btnSubmit) {
                btnSubmit.disabled = !allMandatoryFilled;
                btnSubmit.style.opacity = !allMandatoryFilled ? '0.5' : '1';
                btnSubmit.title = !allMandatoryFilled ? 'Lengkapi semua inputan wajib terlebih dahulu' : 'Ajukan Verifikasi';
            }
        }

        function toggleAdditionalSections(enabled) {
            const sections = ['output-container', 'dukung-container'];
            sections.forEach(id => {
                const container = document.getElementById(id);
                if (!container) return;

                const inputs = container.querySelectorAll('input, select');
                inputs.forEach(input => {
                    if (input.tagName === 'SELECT') {
                        input.disabled = !enabled;
                    } else {
                        input.readOnly = !enabled;
                        input.style.backgroundColor = !enabled ? '#f8f9fa' : '#fff';
                        input.style.cursor = !enabled ? 'not-allowed' : 'text';
                        
                        if (!enabled) {
                            input.placeholder = "Selesaikan kegiatan & bukti wajib terlebih dahulu...";
                        } else {
                            // Restore original placeholder if needed or just use default
                            input.placeholder = input.getAttribute('data-placeholder') || input.placeholder;
                        }
                    }
                });

                const btnAdd = container.nextElementSibling;
                if (btnAdd && btnAdd.tagName === 'BUTTON') {
                    btnAdd.disabled = !enabled;
                    btnAdd.style.opacity = !enabled ? '0.5' : '1';
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            @foreach($kegiatans as $keg)
                updateMinDate({{ $keg->id }});
                updateNextActivityMinDate({{ $keg->id }});
            @endforeach
            checkMandatoryFilled();
        });

        function confirmSubmit() {
            Swal.fire({
                title: 'Ajukan Verifikasi?',
                text: "Pastikan semua data mandatory telah terisi dengan benar. Data yang sudah diajukan tidak dapat diubah sampai diverifikasi/ditolak oleh admin provinsi.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: 'var(--bps-orange)',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Ajukan!',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('action_type').value = 'submit';
                    document.getElementById('form-progress').submit();
                }
            });
        }

        function rejectProgress(progId) {
            Swal.fire({
                title: 'Tolak Progress?',
                text: "Berikan alasan penolakan agar user dapat melakukan perbaikan.",
                icon: 'warning',
                input: 'textarea',
                inputPlaceholder: 'Masukkan alasan penolakan di sini...',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Tolak',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                inputValidator: (value) => {
                    if (!value) {
                        return 'Alasan penolakan wajib diisi!'
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('action-' + progId).value = 'reject';
                    document.getElementById('alasan-' + progId).value = result.value;
                    document.getElementById('verify-form-' + progId).submit();
                }
            });
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
