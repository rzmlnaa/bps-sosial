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
                    Kec. {{ $peserta->kecamatan->nama_kecamatan }}, {{ $peserta->kabupaten->nama_kabupaten }} | Periode
                    {{ $peserta->periode->tahun }}
                </p>
            </div>
        </div>
    </div>

    @php
        $year = $peserta->periode->tahun;
        $minYear = $year . '-01-01';
        $maxYear = $year . '-12-31';

        $hasPending = $peserta->progresses->where('status', 'menunggu_verifikasi')->isNotEmpty() ||
            $peserta->outputs->where('status', 'menunggu_verifikasi')->isNotEmpty() ||
            $peserta->buktiDukungs->where('status', 'menunggu_verifikasi')->isNotEmpty();

        // Individual items handle their own lock status now to allow "focusing on new drafts"
        $isReadonly = false; 
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
                            if ($prog) {
                                if ($prog->status == 'draf') {
                                    $statusLabel = 'Draf';
                                    $statusColor = 'info';
                                } elseif ($prog->status == 'menunggu_verifikasi') {
                                    $statusLabel = 'Menunggu Verifikasi';
                                    $statusColor = 'warning';
                                } elseif ($prog->status == 'disetujui') {
                                    $statusLabel = 'Terverifikasi';
                                    $statusColor = 'success';
                                } elseif ($prog->status == 'ditolak') {
                                    $statusLabel = 'Ditolak';
                                    $statusColor = 'danger';
                                }
                            }
                        @endphp

                        <div class="timeline-item pb-5">
                            <div
                                class="timeline-marker {{ $prog && $prog->status == 'disetujui' ? 'bg-success' : ($isLocked ? 'bg-light border' : 'bg-orange-light text-bps-orange') }}">
                                @if($prog && $prog->status == 'disetujui')
                                    <i class="fas fa-check"></i>
                                @else
                                    {{ $loop->iteration }}
                                @endif
                            </div>

                            <div class="timeline-content">
                                <div class="card border-0 shadow-sm rounded-4 {{ $isLocked ? 'opacity-75' : '' }}">
                                    <div
                                        class="card-header bg-white border-bottom-0 pt-3 px-4 d-flex justify-content-between align-items-center">
                                        <h5 class="fw-bold mb-0 {{ $isLocked ? 'text-muted' : 'text-navy' }}">
                                            {{ $keg->nama_kegiatan }}
                                            @if($keg->is_wajib)
                                                <span class="text-danger" title="Wajib diisi">*</span>
                                            @endif
                                        </h5>
                                        <span class="badge bg-{{ $statusColor }} rounded-pill">{{ $statusLabel }}</span>
                                    </div>

                                    <div class="card-body px-4 pb-4">
                                        @if($prog && $prog->status == 'ditolak' && $prog->alasan_penolakan)
                                            <div class="alert alert-danger border-0 rounded-4 mt-3 mb-2 small">
                                                <i class="fas fa-exclamation-circle me-1"></i>
                                                <strong>Alasan Penolakan:</strong> {{ $prog->alasan_penolakan }}
                                            </div>
                                        @endif

                                        @if(!$prog || $prog->status == 'draf' || $prog->status == 'ditolak')
                                            <div class="mt-3">
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label small fw-bold">Target Tanggal
                                                            @if($keg->is_wajib)<span class="text-danger">*</span>@endif</label>
                                                        <input type="date" name="progress[{{ $keg->id }}][target_tanggal]"
                                                            id="target_{{ $keg->id }}" class="form-control"
                                                            value="{{ $prog ? $prog->target_tanggal : '' }}" min="{{ $minYear }}"
                                                            max="{{ $maxYear }}"
                                                            onchange="updateMinDate({{ $keg->id }}); updateNextActivityMinDate({{ $keg->id }});"
                                                            {{ $isReadonly ? 'readonly' : '' }}>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small fw-bold">
                                                            Realisasi Tanggal
                                                            <span class="text-danger mandatory-star-{{ $keg->id }}"
                                                                data-is-keg-wajib="{{ $keg->is_wajib ? 'true' : 'false' }}"
                                                                style="{{ ($keg->is_wajib || ($prog && $prog->target_tanggal)) ? '' : 'display: none;' }}">*</span>
                                                        </label>
                                                        <input type="date" name="progress[{{ $keg->id }}][realisasi_tanggal]"
                                                            id="realisasi_{{ $keg->id }}" class="form-control"
                                                            value="{{ $prog ? $prog->realisasi_tanggal : '' }}"
                                                            min="{{ $prog && $prog->target_tanggal ? $prog->target_tanggal : $minYear }}"
                                                            max="{{ $maxYear }}"
                                                            onchange="updateNextActivityMinDate({{ $keg->id }}); checkMandatoryFilled()"
                                                            {{ $isReadonly ? 'readonly' : '' }}>
                                                    </div>
                                                    <div class="col-12">
                                                        <label class="form-label small fw-bold mb-2">Bukti Kegiatan</label>
                                                        <div id="bukti-container-{{ $keg->id }}">
                                                            {{-- Bukti Mandatory (Tampil sebagai input tetap) --}}
                                                            @foreach($jenisBuktiMandatory as $mb)
                                                                @php
                                                                    $existingMb = $prog ? $prog->buktis->where('jenis_bukti_id', $mb->id)->first() : null;
                                                                @endphp
                                                                <div
                                                                    class="mb-3 p-3 bg-light rounded-4 border-start border-4 border-orange">
                                                                    <label class="small fw-bold d-block mb-1 text-navy">
                                                                        {{ $mb->nama_bukti }}
                                                                        <span class="text-danger mandatory-star-{{ $keg->id }}"
                                                                            data-is-keg-wajib="{{ $keg->is_wajib ? 'true' : 'false' }}"
                                                                            style="{{ ($keg->is_wajib || ($prog && $prog->target_tanggal)) ? '' : 'display: none;' }}">*</span>
                                                                    </label>
                                                                    <input type="hidden" name="bukti[{{ $keg->id }}][jenis_id][]"
                                                                        value="{{ $mb->id }}">
                                                                    <div class="input-group">
                                                                        <input type="text" name="bukti[{{ $keg->id }}][link][]"
                                                                            class="form-control mandatory-bukti-input-{{ $keg->id }}"
                                                                            value="{{ $existingMb ? $existingMb->link_file : '' }}"
                                                                            data-placeholder="Masukkan link folder atau deskripsi bukti {{ $mb->nama_bukti }}..."
                                                                            placeholder="Masukkan link folder atau deskripsi bukti {{ $mb->nama_bukti }}..."
                                                                            oninput="checkMandatoryFilled()" {{ $isReadonly ? 'readonly' : '' }}>
                                                                        @if($existingMb && filter_var($existingMb->link_file, FILTER_VALIDATE_URL))
                                                                            <a href="{{ $existingMb->link_file }}" target="_blank"
                                                                                class="btn btn-outline-orange">
                                                                                <i class="fas fa-external-link-alt"></i>
                                                                            </a>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            @endforeach

                                                            {{-- Bukti Optional yang sudah ada --}}
                                                            @if($prog)
                                                                @foreach($prog->buktis->whereIn('jenis_bukti_id', $jenisBuktiOptional->pluck('id')) as $ob)
                                                                    <div class="d-flex gap-2 mb-2">
                                                                        <select name="bukti[{{ $keg->id }}][jenis_id][]"
                                                                            class="form-select w-50" {{ $isReadonly ? 'disabled' : '' }}>
                                                                            <option value="">Pilih Jenis Bukti...</option>
                                                                            @foreach($jenisBuktiOptional as $jb)
                                                                                <option value="{{ $jb->id }}" {{ $ob->jenis_bukti_id == $jb->id ? 'selected' : '' }}>
                                                                                    {{ $jb->nama_bukti }}
                                                                                </option>
                                                                            @endforeach
                                                                        </select>
                                                                        <div class="input-group flex-grow-1">
                                                                            <input type="text" name="bukti[{{ $keg->id }}][link][]"
                                                                                class="form-control" value="{{ $ob->link_file }}"
                                                                                data-placeholder="Link folder atau deskripsi bukti..."
                                                                                placeholder="Link folder atau deskripsi bukti..." {{ $isReadonly ? 'readonly' : '' }}>
                                                                            @if(filter_var($ob->link_file, FILTER_VALIDATE_URL))
                                                                                <a href="{{ $ob->link_file }}" target="_blank"
                                                                                    class="btn btn-outline-orange">
                                                                                    <i class="fas fa-external-link-alt"></i>
                                                                                </a>
                                                                            @endif
                                                                        </div>
                                                                        @php 
                                                                            $isItemLocked = ($isReadonly || ($prog && in_array($prog->status, ['disetujui', 'menunggu_verifikasi'])));
                                                                        @endphp
                                                                        @if(!$isItemLocked)
                                                                            <button type="button" class="btn btn-sm btn-outline-danger border-0"
                                                                                onclick="this.parentElement.remove()"><i
                                                                                    class="fas fa-trash"></i></button>
                                                                        @endif
                                                                    </div>
                                                                @endforeach
                                                            @endif

                                                            @if(!$isReadonly)
                                                                {{-- Input baru untuk Bukti Optional --}}
                                                                <div class="d-flex gap-2 mb-2">
                                                                    <select name="bukti[{{ $keg->id }}][jenis_id][]"
                                                                        class="form-select w-50">
                                                                        <option value="">Pilih Jenis Bukti...</option>
                                                                        @foreach($jenisBuktiOptional as $jb)
                                                                            <option value="{{ $jb->id }}">{{ $jb->nama_bukti }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                    <input type="text" name="bukti[{{ $keg->id }}][link][]"
                                                                        class="form-control"
                                                                        data-placeholder="Link folder atau deskripsi bukti..."
                                                                        placeholder="Link folder atau deskripsi bukti...">
                                                                </div>
                                                            @endif
                                                        </div>

                                                        @if(!$isReadonly)
                                                            <button type="button" id="btn-add-bukti-{{ $keg->id }}"
                                                                class="btn btn-sm btn-link text-decoration-none p-0 text-bps-orange mt-1"
                                                                onclick="addFileField({{ $keg->id }})">
                                                                <i class="fas fa-plus-circle me-1"></i> Tambah Bukti Kegiatan
                                                            </button>
                                                        @endif

                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            {{-- View mode --}}
                                            <input type="hidden" id="target_{{ $keg->id }}" value="{{ $prog->target_tanggal }}">
                                            <input type="hidden" id="realisasi_{{ $keg->id }}"
                                                value="{{ $prog->realisasi_tanggal }}">

                                            <div class="row mt-3 g-3">
                                                <div class="col-md-6">
                                                    <small class="text-muted d-block">Target</small>
                                                    <div class="fw-bold">
                                                        {{ $prog->target_tanggal ? date('d M Y', strtotime($prog->target_tanggal)) : '-' }}
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <small class="text-muted d-block">Realisasi</small>
                                                    <div class="fw-bold">
                                                        {{ $prog->realisasi_tanggal ? date('d M Y', strtotime($prog->realisasi_tanggal)) : '-' }}
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <small class="text-muted d-block">Bukti Kegiatan</small>
                                                    <div class="d-flex flex-wrap gap-2 mt-1">
                                                        @forelse($prog->buktis as $bukti)
                                                            @php
                                                                $isUrl = filter_var($bukti->link_file, FILTER_VALIDATE_URL);
                                                            @endphp
                                                            @if($isUrl)
                                                                <a href="{{ $bukti->link_file }}" target="_blank"
                                                                    class="btn btn-sm btn-light border rounded-pill">
                                                                    <i class="fas fa-link me-1"></i> {{ $bukti->jenisBukti->nama_bukti }}
                                                                </a>
                                                            @else
                                                                <span class="badge bg-light text-dark border p-2 rounded-pill fw-normal">
                                                                    <i class="fas fa-file-alt me-1 text-muted"></i>
                                                                    {{ $bukti->jenisBukti->nama_bukti }}: {{ $bukti->link_file }}
                                                                </span>
                                                            @endif
                                                        @empty
                                                            <span class="text-muted small italic">Tidak ada bukti.</span>
                                                        @endforelse
                                                    </div>
                                                </div>
                                            </div>



                                            @if($isProvinsi && $prog->status == 'menunggu_verifikasi')
                                                <div class="mt-4 pt-3 border-top">
                                                    <div class="d-flex gap-2">
                                                        <div id="verify-actions-{{ $prog->id }}" class="d-flex gap-2">
                                                            <button type="button" onclick="confirmApproveProgress({{ $prog->id }})"
                                                                class="btn btn-success btn-sm rounded-pill px-3">
                                                                <i class="fas fa-check-circle me-1"></i> Setujui
                                                            </button>
                                                            <button type="button" onclick="rejectProgress({{ $prog->id }})"
                                                                class="btn btn-danger btn-sm rounded-pill px-3 ms-1">
                                                                <i class="fas fa-times-circle me-1"></i> Tolak / Perbaikan
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        @endif

                                        @if($prog)
                                            <div class="mt-3 pt-2 border-top border-light opacity-75"
                                                style="font-size: 0.7rem; border-top-style: dashed !important;">
                                                <div class="d-flex flex-wrap justify-content-between align-items-center">
                                                    <div class="text-muted">
                                                        <i class="fas fa-user-plus me-1"></i> Input oleh:
                                                        <strong><span data-bs-toggle="tooltip" title="{{ $prog->creator->kabupaten->nama_kabupaten ?? 'Pusat/Provinsi' }}">{{ $prog->creator->name ?? '-' }}</span></strong> pada
                                                        {{ $prog->created_at->format('d/m/Y H:i') }}
                                                    </div>
                                                    @if($prog->updated_by && $prog->updated_at != $prog->created_at)
                                                        <div class="text-muted">
                                                            <i class="fas fa-user-edit me-1"></i> Update terakhir:
                                                            <strong><span data-bs-toggle="tooltip" title="{{ $prog->updater->kabupaten->nama_kabupaten ?? 'Pusat/Provinsi' }}">{{ $prog->updater->name ?? '-' }}</span></strong> pada
                                                            {{ $prog->updated_at->format('d/m/Y H:i') }}
                                                        </div>
                                                    @endif
                                                </div>
                                                @if(($prog->status == 'disetujui' || $prog->status == 'ditolak') && $prog->verified_by)
                                                    <div
                                                        class="{{ $prog->status == 'disetujui' ? 'text-success' : 'text-danger' }} mt-1">
                                                        <i
                                                            class="fas {{ $prog->status == 'disetujui' ? 'fa-user-check' : 'fa-user-times' }} me-1"></i>
                                                        {{ $prog->status == 'disetujui' ? 'Diverifikasi' : 'Ditolak' }} oleh:
                                                        <strong><span data-bs-toggle="tooltip" title="{{ $prog->verifier->kabupaten->nama_kabupaten ?? 'Pusat/Provinsi' }}">{{ $prog->verifier->name ?? '-' }}</span></strong> pada
                                                        {{ $prog->verified_at->format('d/m/Y H:i') }}
                                                    </div>
                                                @endif
                                            </div>
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
                        <h5 class="fw-bold mb-0 text-navy"><i class="fas fa-file-export me-2"></i>Output (Link bukti dukung)
                        </h5>
                    </div>
                    <div class="card-body px-4 pb-4">
                        <div id="output-container">
                            {{-- Mandatory Output --}}
                            @foreach($jenisOutputMandatory as $jo)
                                @php $out = $peserta->outputs->where('jenis_output_id', $jo->id)->first(); @endphp
                                <div class="mb-3 p-3 bg-light rounded-4 border-start border-4 border-primary">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <label class="small fw-bold text-navy mb-0">{{ $jo->nama_output }} <span
                                                class="text-danger">*</span></label>
                                        @if($out && $out->status)
                                            @php
                                                $sLabel = 'Draf';
                                                $sColor = 'info';
                                                if ($out->status == 'menunggu_verifikasi') {
                                                    $sLabel = 'Menunggu Verifikasi';
                                                    $sColor = 'warning';
                                                } elseif ($out->status == 'disetujui') {
                                                    $sLabel = 'Terverifikasi';
                                                    $sColor = 'success';
                                                } elseif ($out->status == 'ditolak') {
                                                    $sLabel = 'Ditolak';
                                                    $sColor = 'danger';
                                                }
                                            @endphp
                                            <span class="badge bg-{{ $sColor }} rounded-pill"
                                                style="font-size: 0.65rem;">{{ $sLabel }}</span>
                                        @endif
                                    </div>
                                    <input type="hidden" name="output_jenis_id[]" value="{{ $jo->id }}" {{ ($out && in_array($out->status, ['disetujui', 'menunggu_verifikasi'])) ? 'data-locked="true"' : '' }} {{ ($out && in_array($out->status, ['disetujui', 'menunggu_verifikasi'])) ? 'disabled' : '' }}>
                                    <div class="input-group">
                                        <input type="text" name="output_link[]" class="form-control mandatory-output-input"
                                            value="{{ $out ? $out->link : '' }}" placeholder="Link {{ $jo->nama_output }}..."
                                            oninput="checkMandatoryFilled()" {{ ($isReadonly || ($out && in_array($out->status, ['disetujui', 'menunggu_verifikasi']))) ? 'disabled' : '' }} {{ ($out && in_array($out->status, ['disetujui', 'menunggu_verifikasi'])) ? 'data-locked="true"' : '' }}>
                                        @if($out && filter_var($out->link, FILTER_VALIDATE_URL))
                                            <a href="{{ $out->link }}" target="_blank" class="btn btn-outline-primary">
                                                <i class="fas fa-external-link-alt"></i>
                                            </a>
                                        @endif
                                    </div>
                                    @if($out)
                                        <div class="mt-1 d-flex flex-wrap justify-content-between align-items-center opacity-75"
                                            style="font-size: 0.65rem;">
                                            <div class="text-muted">
                                                <i class="fas fa-user-plus me-1"></i> Input: <span data-bs-toggle="tooltip" title="{{ $out->creator->kabupaten->nama_kabupaten ?? 'Pusat/Provinsi' }}">{{ $out->creator->name ?? '-' }}</span>
                                                ({{ $out->created_at->format('d/m/Y H:i') }})
                                            </div>
                                            @if($out->updated_by && $out->updated_at != $out->created_at)
                                                <div class="text-muted">
                                                    <i class="fas fa-user-edit me-1"></i> Update: <span data-bs-toggle="tooltip" title="{{ $out->updater->kabupaten->nama_kabupaten ?? 'Pusat/Provinsi' }}">{{ $out->updater->name ?? '-' }}</span>
                                                    ({{ $out->updated_at->format('d/m/Y H:i') }})
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                    @if($out && $out->status == 'ditolak' && $out->alasan_penolakan)
                                        <small class="text-danger mt-1 d-block" style="font-size: 0.75rem;">
                                            <i class="fas fa-exclamation-circle me-1"></i><strong>Alasan:</strong>
                                            {{ $out->alasan_penolakan }}
                                        </small>
                                    @endif

                                    @if($isProvinsi && $out && $out->status == 'menunggu_verifikasi')
                                        <div class="mt-2 pt-2 border-top">
                                            <div id="verify-output-actions-{{ $out->id }}" class="d-flex gap-2">
                                                <button type="button" onclick="confirmApproveOutput({{ $out->id }})"
                                                    class="btn btn-success btn-sm rounded-pill px-3">
                                                    <i class="fas fa-check-circle me-1"></i> Terima
                                                </button>
                                                <button type="button" onclick="rejectOutput({{ $out->id }})"
                                                    class="btn btn-danger btn-sm rounded-pill px-3">
                                                    <i class="fas fa-times-circle me-1"></i> Tolak / Perbaikan
                                                </button>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endforeach

                            <label class="small fw-bold d-block mb-2 text-muted mt-4">Output Lainnya (Opsional)</label>
                            {{-- Existing Optional Output --}}
                            @foreach($peserta->outputs->whereIn('jenis_output_id', $jenisOutputOptional->pluck('id')) as $out)
                                <div class="mb-3">
                                    @if($out && $out->status)
                                        <div class="d-flex justify-content-end mb-1">
                                            @php
                                                $sLabel = 'Draf';
                                                $sColor = 'info';
                                                if ($out->status == 'menunggu_verifikasi') {
                                                    $sLabel = 'Menunggu Verifikasi';
                                                    $sColor = 'warning';
                                                } elseif ($out->status == 'disetujui') {
                                                    $sLabel = 'Terverifikasi';
                                                    $sColor = 'success';
                                                } elseif ($out->status == 'ditolak') {
                                                    $sLabel = 'Ditolak';
                                                    $sColor = 'danger';
                                                }
                                            @endphp
                                            <span class="badge bg-{{ $sColor }} rounded-pill"
                                                style="font-size: 0.65rem;">{{ $sLabel }}</span>
                                        </div>
                                    @endif
                                    <div class="d-flex gap-2 mb-1 align-items-center">
                                        <select name="output_jenis_id[]" class="form-select w-50" {{ ($isReadonly || ($out && in_array($out->status, ['disetujui', 'menunggu_verifikasi']))) ? 'disabled' : '' }}
                                            {{ ($out && in_array($out->status, ['disetujui', 'menunggu_verifikasi'])) ? 'data-locked="true"' : '' }}>
                                            <option value="">Pilih Jenis Output...</option>
                                            @foreach($jenisOutputOptional as $jo)
                                                <option value="{{ $jo->id }}" {{ $out->jenis_output_id == $jo->id ? 'selected' : '' }}>{{ $jo->nama_output }}</option>
                                            @endforeach
                                        </select>
                                        <div class="input-group flex-grow-1">
                                            <input type="text" name="output_link[]" class="form-control"
                                                value="{{ $out->link }}" placeholder="Link output..." {{ ($isReadonly || ($out && in_array($out->status, ['disetujui', 'menunggu_verifikasi']))) ? 'disabled' : '' }} {{ ($out && in_array($out->status, ['disetujui', 'menunggu_verifikasi'])) ? 'data-locked="true"' : '' }}>
                                            @if(filter_var($out->link, FILTER_VALIDATE_URL))
                                                <a href="{{ $out->link }}" target="_blank" class="btn btn-outline-primary">
                                                    <i class="fas fa-external-link-alt"></i>
                                                </a>
                                            @endif
                                        </div>
                                        @if($out)
                                            <div class="mt-1 d-flex flex-wrap justify-content-between align-items-center opacity-75 w-100"
                                                style="font-size: 0.65rem;">
                                                <div class="text-muted">
                                                    <i class="fas fa-user-plus me-1"></i> Input: <span data-bs-toggle="tooltip" title="{{ $out->creator->kabupaten->nama_kabupaten ?? 'Pusat/Provinsi' }}">{{ $out->creator->name ?? '-' }}</span>
                                                    ({{ $out->created_at->format('d/m/Y H:i') }})
                                                </div>
                                                @if($out->updated_by && $out->updated_at != $out->created_at)
                                                    <div class="text-muted">
                                                        <i class="fas fa-user-edit me-1"></i> Update: <span data-bs-toggle="tooltip" title="{{ $out->updater->kabupaten->nama_kabupaten ?? 'Pusat/Provinsi' }}">{{ $out->updater->name ?? '-' }}</span>
                                                        ({{ $out->updated_at->format('d/m/Y H:i') }})
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                        @php 
                                            $isItemLocked = ($isReadonly || ($out && in_array($out->status, ['disetujui', 'menunggu_verifikasi'])));
                                        @endphp
                                        @if(!$isItemLocked)
                                            <button type="button" class="btn btn-sm btn-link text-danger p-0"
                                                onclick="this.closest('.mb-3').remove()"><i class="fas fa-trash"></i></button>
                                        @endif
                                    </div>
                                    @if($out && $out->status == 'ditolak' && $out->alasan_penolakan)
                                        <small class="text-danger d-block" style="font-size: 0.75rem; margin-left: 2px;">
                                            <i class="fas fa-exclamation-circle me-1"></i><strong>Alasan:</strong>
                                            {{ $out->alasan_penolakan }}
                                        </small>
                                    @endif

                                    @if($isProvinsi && $out && $out->status == 'menunggu_verifikasi')
                                        <div class="mt-2 pt-2 border-top">
                                            <div id="verify-output-actions-{{ $out->id }}" class="d-flex gap-2">
                                                <button type="button" onclick="confirmApproveOutput({{ $out->id }})"
                                                    class="btn btn-success btn-sm rounded-pill px-3">
                                                    <i class="fas fa-check-circle me-1"></i> Terima
                                                </button>
                                                <button type="button" onclick="rejectOutput({{ $out->id }})"
                                                    class="btn btn-danger btn-sm rounded-pill px-3">
                                                    <i class="fas fa-times-circle me-1"></i> Tolak / Perbaikan
                                                </button>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endforeach

                            @if(!$isReadonly)
                                {{-- Always show one empty optional row --}}
                                <div class="mb-3 generic-row">
                                    <div class="d-flex gap-2 mb-1 align-items-center">
                                        <select name="output_jenis_id[]" class="form-select w-50">
                                            <option value="">Pilih Jenis Output...</option>
                                            @foreach($jenisOutputOptional as $jo)
                                                <option value="{{ $jo->id }}">{{ $jo->nama_output }}</option>
                                            @endforeach
                                        </select>
                                        <input type="text" name="output_link[]" class="form-control"
                                            placeholder="Link output...">
                                    </div>
                                </div>
                            @endif
                        </div>
                        @if(!$isReadonly)
                            <button type="button" class="btn btn-sm btn-link text-decoration-none p-0 text-bps-orange mt-1"
                                onclick="addGenericField('output-container')">
                                <i class="fas fa-plus-circle me-1"></i> Tambah Output Lainnya
                            </button>
                        @endif
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
                                        <label class="small fw-bold text-navy mb-0">{{ $jd->nama_bukti }} <span
                                                class="text-danger">*</span></label>
                                        @if($duk && $duk->status)
                                            @php
                                                $sLabel = 'Draf';
                                                $sColor = 'info';
                                                if ($duk->status == 'menunggu_verifikasi') {
                                                    $sLabel = 'Menunggu Verifikasi';
                                                    $sColor = 'warning';
                                                } elseif ($duk->status == 'disetujui') {
                                                    $sLabel = 'Terverifikasi';
                                                    $sColor = 'success';
                                                } elseif ($duk->status == 'ditolak') {
                                                    $sLabel = 'Ditolak';
                                                    $sColor = 'danger';
                                                }
                                            @endphp
                                            <span class="badge bg-{{ $sColor }} rounded-pill"
                                                style="font-size: 0.65rem;">{{ $sLabel }}</span>
                                        @endif
                                    </div>
                                    <input type="hidden" name="dukung_jenis_id[]" value="{{ $jd->id }}" {{ ($duk && in_array($duk->status, ['disetujui', 'menunggu_verifikasi'])) ? 'data-locked="true"' : '' }} {{ ($duk && in_array($duk->status, ['disetujui', 'menunggu_verifikasi'])) ? 'disabled' : '' }}>
                                    <div class="input-group">
                                        <input type="text" name="dukung_link[]" class="form-control mandatory-dukung-input"
                                            value="{{ $duk ? $duk->link_file : '' }}"
                                            placeholder="Link {{ $jd->nama_bukti }}..." oninput="checkMandatoryFilled()" {{ ($isReadonly || ($duk && in_array($duk->status, ['disetujui', 'menunggu_verifikasi']))) ? 'disabled' : '' }} {{ ($duk && in_array($duk->status, ['disetujui', 'menunggu_verifikasi'])) ? 'data-locked="true"' : '' }}>
                                        @if($duk && filter_var($duk->link_file, FILTER_VALIDATE_URL))
                                            <a href="{{ $duk->link_file }}" target="_blank" class="btn btn-outline-info">
                                                <i class="fas fa-external-link-alt"></i>
                                            </a>
                                        @endif
                                    </div>
                                    @if($duk)
                                        <div class="mt-1 opacity-75" style="font-size: 0.65rem;">
                                            <div class="text-muted">
                                                <i class="fas fa-user-plus me-1"></i> Input oleh: <span data-bs-toggle="tooltip" title="{{ $duk->creator->kabupaten->nama_kabupaten ?? 'Pusat/Provinsi' }}">{{ $duk->creator->name ?? '-' }}</span>
                                                ({{ $duk->created_at->format('d/m/Y H:i') }})
                                            </div>
                                        </div>
                                    @endif
                                    @if($duk && $duk->status == 'ditolak' && $duk->alasan_penolakan)
                                        <small class="text-danger mt-1 d-block" style="font-size: 0.75rem;">
                                            <i class="fas fa-exclamation-circle me-1"></i><strong>Alasan:</strong>
                                            {{ $duk->alasan_penolakan }}
                                        </small>
                                    @endif

                                    @if($isProvinsi && $duk && $duk->status == 'menunggu_verifikasi')
                                        <div class="mt-2 pt-2 border-top">
                                            <div id="verify-dukung-actions-{{ $duk->id }}" class="d-flex gap-2">
                                                <button type="button" onclick="confirmApproveDukung({{ $duk->id }})"
                                                    class="btn btn-success btn-sm rounded-pill px-3">
                                                    <i class="fas fa-check-circle me-1"></i> Terima
                                                </button>
                                                <button type="button" onclick="rejectDukung({{ $duk->id }})"
                                                    class="btn btn-danger btn-sm rounded-pill px-3">
                                                    <i class="fas fa-times-circle me-1"></i> Tolak / Perbaikan
                                                </button>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endforeach

                            <label class="small fw-bold d-block mb-2 text-muted mt-4">Bukti Lainnya (Opsional)</label>
                            {{-- Existing Optional Dukung --}}
                            @foreach($peserta->buktiDukungs->whereIn('jenis_bukti_id', $jenisDukungOptional->pluck('id')) as $duk)
                                <div class="mb-3">
                                    @if($duk && $duk->status)
                                        <div class="d-flex justify-content-end mb-1">
                                            @php
                                                $sLabel = 'Draf';
                                                $sColor = 'info';
                                                if ($duk->status == 'menunggu_verifikasi') {
                                                    $sLabel = 'Menunggu Verifikasi';
                                                    $sColor = 'warning';
                                                } elseif ($duk->status == 'disetujui') {
                                                    $sLabel = 'Terverifikasi';
                                                    $sColor = 'success';
                                                } elseif ($duk->status == 'ditolak') {
                                                    $sLabel = 'Ditolak';
                                                    $sColor = 'danger';
                                                }
                                            @endphp
                                            <span class="badge bg-{{ $sColor }} rounded-pill"
                                                style="font-size: 0.65rem;">{{ $sLabel }}</span>
                                        </div>
                                    @endif
                                    <div class="d-flex gap-2 mb-1 align-items-center">
                                        <select name="dukung_jenis_id[]" class="form-select w-50" {{ ($isReadonly || ($duk && in_array($duk->status, ['disetujui', 'menunggu_verifikasi']))) ? 'disabled' : '' }}
                                            {{ ($duk && in_array($duk->status, ['disetujui', 'menunggu_verifikasi'])) ? 'data-locked="true"' : '' }}>
                                            <option value="">Pilih Jenis Bukti...</option>
                                            @foreach($jenisDukungOptional as $jd)
                                                <option value="{{ $jd->id }}" {{ $duk->jenis_bukti_id == $jd->id ? 'selected' : '' }}>
                                                    {{ $jd->nama_bukti }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="input-group flex-grow-1">
                                            <input type="text" name="dukung_link[]" class="form-control"
                                                value="{{ $duk->link_file }}" placeholder="Link bukti..." {{ ($isReadonly || ($duk && in_array($duk->status, ['disetujui', 'menunggu_verifikasi']))) ? 'disabled' : '' }} {{ ($duk && in_array($duk->status, ['disetujui', 'menunggu_verifikasi'])) ? 'data-locked="true"' : '' }}>
                                            @if(filter_var($duk->link_file, FILTER_VALIDATE_URL))
                                                <a href="{{ $duk->link_file }}" target="_blank" class="btn btn-outline-info">
                                                    <i class="fas fa-external-link-alt"></i>
                                                </a>
                                            @endif
                                        </div>
                                        @if($duk)
                                            <div class="mt-1 opacity-75 w-100" style="font-size: 0.65rem;">
                                                <div class="text-muted">
                                                    <i class="fas fa-user-plus me-1"></i> Input oleh:
                                                    <span data-bs-toggle="tooltip" title="{{ $duk->creator->kabupaten->nama_kabupaten ?? 'Pusat/Provinsi' }}">{{ $duk->creator->name ?? '-' }}</span> ({{ $duk->created_at->format('d/m/Y H:i') }})
                                                </div>
                                            </div>
                                        @endif
                                        @php 
                                            $isItemLocked = ($isReadonly || ($duk && in_array($duk->status, ['disetujui', 'menunggu_verifikasi'])));
                                        @endphp
                                        @if(!$isItemLocked)
                                            <button type="button" class="btn btn-sm btn-link text-danger p-0"
                                                onclick="this.closest('.mb-3').remove()"><i class="fas fa-trash"></i></button>
                                        @endif
                                    </div>
                                    @if($duk && $duk->status == 'ditolak' && $duk->alasan_penolakan)
                                        <small class="text-danger d-block" style="font-size: 0.75rem; margin-left: 2px;">
                                            <i class="fas fa-exclamation-circle me-1"></i><strong>Alasan:</strong>
                                            {{ $duk->alasan_penolakan }}
                                        </small>
                                    @endif

                                    @if($isProvinsi && $duk && $duk->status == 'menunggu_verifikasi')
                                        <div class="mt-2 pt-2 border-top">
                                            <div id="verify-dukung-actions-{{ $duk->id }}" class="d-flex gap-2">
                                                <button type="button" onclick="confirmApproveDukung({{ $duk->id }})"
                                                    class="btn btn-success btn-sm rounded-pill px-3">
                                                    <i class="fas fa-check-circle me-1"></i> Terima
                                                </button>
                                                <button type="button" onclick="rejectDukung({{ $duk->id }})"
                                                    class="btn btn-danger btn-sm rounded-pill px-3">
                                                    <i class="fas fa-times-circle me-1"></i> Tolak / Perbaikan
                                                </button>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endforeach

                            @if(!$isReadonly)
                                {{-- Always show one empty optional row --}}
                                <div class="mb-3 generic-row">
                                    <div class="d-flex gap-2 mb-1 align-items-center">
                                        <select name="dukung_jenis_id[]" class="form-select w-50">
                                            <option value="">Pilih Jenis Bukti...</option>
                                            @foreach($jenisDukungOptional as $jd)
                                                <option value="{{ $jd->id }}">{{ $jd->nama_bukti }}</option>
                                            @endforeach
                                        </select>
                                        <input type="text" name="dukung_link[]" class="form-control"
                                            placeholder="Link bukti...">
                                    </div>
                                </div>
                            @endif
                        </div>
                        @if(!$isReadonly)
                            <button type="button" class="btn btn-sm btn-link text-decoration-none p-0 text-bps-orange mt-1"
                                onclick="addGenericField('dukung-container')">
                                <i class="fas fa-plus-circle me-1"></i> Tambah Bukti Lainnya
                            </button>
                        @endif
                    </div>
                </div>
            </div> {{-- End Col-lg-8 --}}
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 mb-3 sticky-top" style="top: 2rem;">
                    <div class="card-header bg-white border-0 pt-4 px-4">
                        @php
                            $totalUnits = 0;
                            $filledUnits = 0;
                            $totalMandatory = 0;
                            $filledMandatory = 0;

                            // 1. Progress Kegiatan
                            $mandatoryBuktiCount = $jenisBuktiMandatory->count();
                            $mandatoryBuktiIdsArr = $jenisBuktiMandatory->pluck('id')->toArray();

                            foreach ($kegiatans as $keg) {
                                $unitsInThisKeg = (2 + $mandatoryBuktiCount);
                                $totalUnits += $unitsInThisKeg;
                                if ($keg->is_wajib)
                                    $totalMandatory += $unitsInThisKeg;

                                $prog = $progresses->get($keg->id);
                                if ($prog) {
                                    $filledInThisKeg = 0;
                                    if ($prog->target_tanggal)
                                        $filledInThisKeg++;
                                    if ($prog->realisasi_tanggal)
                                        $filledInThisKeg++;
                                    foreach ($mandatoryBuktiIdsArr as $mid) {
                                        $bukti = $prog->buktis->where('jenis_bukti_id', $mid)->first();
                                        if ($bukti && !empty($bukti->link_file))
                                            $filledInThisKeg++;
                                    }

                                    $filledUnits += $filledInThisKeg;
                                    if ($keg->is_wajib)
                                        $filledMandatory += $filledInThisKeg;
                                }
                            }

                            // 2. Output
                            $mandatoryOutputIdsArr = $jenisOutputMandatory->pluck('id')->toArray();
                            $totalUnits += count($mandatoryOutputIdsArr);
                            $totalMandatory += count($mandatoryOutputIdsArr);
                            foreach ($mandatoryOutputIdsArr as $oid) {
                                $out = $peserta->outputs->where('jenis_output_id', $oid)->first();
                                if ($out && !empty($out->link)) {
                                    $filledUnits++;
                                    $filledMandatory++;
                                }
                            }

                            // 3. Bukti Dukung (Lainnya)
                            $mandatoryDukungIdsArr = $jenisDukungMandatory->pluck('id')->toArray();
                            $totalUnits += count($mandatoryDukungIdsArr);
                            $totalMandatory += count($mandatoryDukungIdsArr);
                            foreach ($mandatoryDukungIdsArr as $did) {
                                $duk = $peserta->buktiDukungs->where('jenis_bukti_id', $did)->first();
                                if ($duk && !empty($duk->link_file)) {
                                    $filledUnits++;
                                    $filledMandatory++;
                                }
                            }

                            $percent = $totalUnits > 0 ? round(($filledUnits / $totalUnits) * 100) : 0;
                            $mandatoryPercent = $totalMandatory > 0 ? round(($filledMandatory / $totalMandatory) * 100) : 0;
                         @endphp

                        <div class="bg-light rounded-4 p-3 mb-4">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <div>
                                    <small class="text-muted d-block">Progress Wajib</small>
                                    <span
                                        class="fw-bold fs-4 {{ $mandatoryPercent == 100 ? 'text-success' : 'text-bps-orange' }}">{{ $mandatoryPercent }}%</span>
                                </div>
                                <div style="width: 45px;">
                                    <svg viewBox="0 0 36 36"
                                        class="circular-chart {{ $mandatoryPercent == 100 ? 'success' : 'orange' }}">
                                        <path class="circle-bg"
                                            d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                                        <path class="circle" stroke-dasharray="{{ $mandatoryPercent }}, 100"
                                            d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                                    </svg>
                                </div>
                            </div>
                            <div class="progress" style="height: 4px; border-radius: 2px; background-color: #dee2e6;">
                                <div class="progress-bar bg-secondary opacity-50" role="progressbar"
                                    style="width: {{ $percent }}%" aria-valuenow="{{ $percent }}" aria-valuemin="0"
                                    aria-valuemax="100"></div>
                            </div>
                            <div class="d-flex justify-content-between mt-1">
                                <small class="text-muted" style="font-size: 0.7rem;">Overall Progress</small>
                                <small class="text-muted" style="font-size: 0.7rem;">{{ $percent }}%</small>
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
                            <h6 class="fw-bold small text-navy mb-2"><i class="fas fa-info-circle me-1"></i> Informasi
                                Pengisian</h6>
                            <div class="small text-muted">
                                <div class="d-flex gap-2 mb-2">
                                    <span class="text-danger fw-bold">*</span>
                                    <span>Field dengan tanda bintang merah bersifat <strong>wajib</strong>. Seluruh field
                                        wajib harus dilengkapi sebelum dapat mengajukan verifikasi.</span>
                                </div>
                                <div class="d-flex gap-2 mb-2">
                                    <i class="fas fa-check-circle mt-1 text-success"></i>
                                    <span><strong>Progress Wajib</strong> dihitung dari kelengkapan komponen yang bersifat
                                        mandatory (wajib).</span>
                                </div>
                                <div class="d-flex gap-2">
                                    <i class="fas fa-chart-line mt-1 opacity-50"></i>
                                    <span><strong>Overall Progress</strong> dihitung berdasarkan seluruh komponen baik yang
                                        wajib maupun opsional.</span>
                                </div>
                            </div>
                        </div>

                        <!-- <div class="alert alert-info border-0 small mt-4">
                                                                    <i class="fas fa-info-circle me-1"></i>
                                                                    Admin Provinsi akan melakukan verifikasi pada setiap tahapan kegiatan yang telah diajukan.
                                                                </div> -->

                        @php
                            $hasPending = $peserta->progresses->where('status', 'menunggu_verifikasi')->isNotEmpty() ||
                                $peserta->outputs->where('status', 'menunggu_verifikasi')->isNotEmpty() ||
                                $peserta->buktiDukungs->where('status', 'menunggu_verifikasi')->isNotEmpty();
                        @endphp

                        <div class="d-grid gap-2 mt-4">
                            @if($hasPending && $isProvinsi)
                                <div class="d-grid gap-2">
                                    <button type="button" onclick="confirmVerifyAll('approve')"
                                        class="btn btn-success rounded-pill">
                                        <i class="fas fa-check-double me-1"></i> Terima Semua Progress
                                    </button>
                                    <button type="button" onclick="confirmVerifyAll('reject')"
                                        class="btn btn-danger rounded-pill">
                                        <i class="fas fa-times-circle me-1"></i> Tolak Semua Progress
                                    </button>
                                </div>
                            @else
                                @if($hasPending && !$isProvinsi)
                                    <div class="alert alert-info border-0 text-center rounded-4 py-2 mb-3 small">
                                        <i class="fas fa-info-circle me-1"></i> Beberapa isian sedang diverifikasi. Anda tetap dapat mengisi draf baru lainnya.
                                    </div>
                                @endif
                                <button type="submit" onclick="document.getElementById('action_type').value='draf'"
                                    class="btn btn-light rounded-pill">
                                    <i class="fas fa-save me-1"></i> Simpan Draf
                                </button>
                                <button type="button" id="btn-submit-verifikasi" onclick="confirmSubmit()"
                                    class="btn btn-orange rounded-pill">
                                    <i class="fas fa-paper-plane me-1"></i> Ajukan Verifikasi
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    {{-- Global Verification Form (Hidden) --}}
    <form id="global-verify-form" action="" method="POST" style="display:none;">
        @csrf
        <input type="hidden" name="action" id="global-verify-action" value="">
        <input type="hidden" name="alasan_penolakan" id="global-verify-alasan" value="">
    </form>
    </div>
    @push('scripts')
        <script>
            function updateSelectOptions(container) {
                const selects = container.querySelectorAll('select');
                if (selects.length === 0) return;

                // Get all selected values
                const selectedValues = Array.from(selects).map(s => s.value).filter(v => v !== '');

                selects.forEach(select => {
                    const options = select.querySelectorAll('option');
                    options.forEach(option => {
                        if (option.value === '') return;
                        if (selectedValues.includes(option.value) && select.value !== option.value) {
                            option.disabled = true;
                            option.style.display = 'none';
                        } else {
                            option.disabled = false;
                            option.style.display = '';
                        }
                    });
                });

                // Check if we can add more rows
                const validOptionsCount = Array.from(selects[0].options).filter(o => o.value !== '').length;
                let btnAdd;
                if (container.id.startsWith('bukti-container-')) {
                    const kegId = container.id.split('-')[2];
                    btnAdd = document.getElementById(`btn-add-bukti-${kegId}`);
                } else {
                    btnAdd = container.parentElement.querySelector('button[onclick^="addGenericField"]');
                }

                if (btnAdd) {
                    if (selects.length >= validOptionsCount) {
                        btnAdd.style.display = 'none';
                    } else {
                        btnAdd.style.display = '';
                    }
                }
            }

            document.addEventListener('change', function (e) {
                if (e.target.tagName === 'SELECT') {
                    const container = e.target.closest('#output-container, #dukung-container, [id^="bukti-container-"]');
                    if (container) {
                        updateSelectOptions(container);
                    }
                }
            });

            document.addEventListener('click', function (e) {
                const btn = e.target.closest('button');
                if (btn && btn.querySelector('.fa-trash')) {
                    const container = btn.closest('#output-container, #dukung-container, [id^="bukti-container-"]');
                    if (container) {
                        setTimeout(() => updateSelectOptions(container), 50);
                    }
                }
            });

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
                if (select) {
                    select.value = '';
                    select.disabled = false;
                    select.removeAttribute('data-locked');
                }
                if (input) {
                    input.value = '';
                    input.disabled = false;
                    input.readOnly = false;
                    input.removeAttribute('data-locked');
                    input.style.backgroundColor = '#fff';
                    input.style.cursor = 'text';
                }

                // Tambahkan tombol hapus jika belum ada
                if (!newRow.querySelector('.fa-trash')) {
                    const btnTrash = document.createElement('button');
                    btnTrash.type = 'button';
                    btnTrash.className = 'btn btn-sm btn-link text-danger p-0';
                    btnTrash.innerHTML = '<i class="fas fa-trash"></i>';
                    btnTrash.onclick = function () { this.parentElement.remove(); updateSelectOptions(container); };
                    newRow.appendChild(btnTrash);
                } else {
                    // Pastikan fungsi hapus bekerja pada baris baru
                    const btn = newRow.querySelector('button');
                    if (btn) {
                        btn.onclick = function () { this.parentElement.remove(); updateSelectOptions(container); };
                    }
                }

                container.appendChild(newRow);
                updateSelectOptions(container);
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
                if (select) {
                    select.value = '';
                    select.disabled = false;
                    select.removeAttribute('data-locked');
                }
                if (input) {
                    input.value = '';
                    input.disabled = false;
                    input.readOnly = false;
                    input.removeAttribute('data-locked');
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
                    btnTrash.onclick = function () { this.closest('.mb-3').remove(); updateSelectOptions(container); };
                    newRow.querySelector('.d-flex').appendChild(btnTrash);
                } else {
                    const btn = newRow.querySelector('.fa-trash').parentElement;
                    if (btn) {
                        btn.onclick = function () { this.closest('.mb-3').remove(); updateSelectOptions(container); };
                    }
                }

                container.appendChild(newRow);
                updateSelectOptions(container);
            }

            function updateMinDate(kegId) {
                const targetInput = document.getElementById(`target_${kegId}`);
                if (!targetInput) return;

                const targetVal = targetInput.value;
                const realisasiInput = document.getElementById(`realisasi_${kegId}`);
                const container = document.getElementById(`bukti-container-${kegId}`);
                const btnAdd = document.getElementById(`btn-add-bukti-${kegId}`);

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
                        btnAdd.disabled = isNoDate;
                        btnAdd.style.opacity = isNoDate ? '0.5' : '1';
                        btnAdd.style.cursor = isNoDate ? 'not-allowed' : 'pointer';
                        // Also remove any display styling so it returns to its natural layout
                        btnAdd.style.display = '';
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
                            input.disabled = !enabled || input.hasAttribute('data-locked');
                        } else {
                            input.disabled = !enabled || input.hasAttribute('data-locked');
                            input.style.backgroundColor = (!enabled || input.hasAttribute('data-locked')) ? '#f8f9fa' : '#fff';
                            input.style.cursor = (!enabled || input.hasAttribute('data-locked')) ? 'not-allowed' : 'text';

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

            document.addEventListener('DOMContentLoaded', function () {
                @foreach($kegiatans as $keg)
                    updateMinDate({{ $keg->id }});
                    updateNextActivityMinDate({{ $keg->id }});
                @endforeach
                checkMandatoryFilled();

                // Initialize options and limits for all containers
                const containers = document.querySelectorAll('#output-container, #dukung-container, [id^="bukti-container-"]');
                containers.forEach(c => updateSelectOptions(c));
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

            function confirmApproveProgress(progId) {
                Swal.fire({
                    title: 'Setujui Progress?',
                    text: "Apakah Anda yakin ingin menyetujui progress kegiatan ini?",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#198754',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, Setujui',
                    cancelButtonText: 'Batal',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        const form = document.getElementById('global-verify-form');
                        form.action = "{{ url('/desa-cantik/progress') }}/" + "{{ $peserta->id }}" + "/verify/" + progId;
                        document.getElementById('global-verify-action').value = 'approve';
                        document.getElementById('global-verify-alasan').value = '';
                        form.submit();
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
                        const form = document.getElementById('global-verify-form');
                        form.action = "{{ url('/desa-cantik/progress') }}/" + "{{ $peserta->id }}" + "/verify/" + progId;
                        document.getElementById('global-verify-action').value = 'reject';
                        document.getElementById('global-verify-alasan').value = result.value;
                        form.submit();
                    }
                });
            }

            function confirmApproveOutput(outputId) {
                Swal.fire({
                    title: 'Setujui Output?',
                    text: "Apakah Anda yakin ingin menyetujui output ini?",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#198754',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, Setujui',
                    cancelButtonText: 'Batal',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        const form = document.getElementById('global-verify-form');
                        form.action = "{{ url('/desa-cantik/progress') }}/" + "{{ $peserta->id }}" + "/verify-output/" + outputId;
                        document.getElementById('global-verify-action').value = 'approve';
                        document.getElementById('global-verify-alasan').value = '';
                        form.submit();
                    }
                });
            }

            function rejectOutput(outputId) {
                Swal.fire({
                    title: 'Tolak Output?',
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
                        const form = document.getElementById('global-verify-form');
                        form.action = "{{ url('/desa-cantik/progress') }}/" + "{{ $peserta->id }}" + "/verify-output/" + outputId;
                        document.getElementById('global-verify-action').value = 'reject';
                        document.getElementById('global-verify-alasan').value = result.value;
                        form.submit();
                    }
                });
            }

            function confirmApproveDukung(dukungId) {
                Swal.fire({
                    title: 'Setujui Bukti Dukung?',
                    text: "Apakah Anda yakin ingin menyetujui bukti dukung ini?",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#198754',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, Setujui',
                    cancelButtonText: 'Batal',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        const form = document.getElementById('global-verify-form');
                        form.action = "{{ url('/desa-cantik/progress') }}/" + "{{ $peserta->id }}" + "/verify-dukung/" + dukungId;
                        document.getElementById('global-verify-action').value = 'approve';
                        document.getElementById('global-verify-alasan').value = '';
                        form.submit();
                    }
                });
            }

            function rejectDukung(dukungId) {
                Swal.fire({
                    title: 'Tolak Bukti Dukung?',
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
                        const form = document.getElementById('global-verify-form');
                        form.action = "{{ url('/desa-cantik/progress') }}/" + "{{ $peserta->id }}" + "/verify-dukung/" + dukungId;
                        document.getElementById('global-verify-action').value = 'reject';
                        document.getElementById('global-verify-alasan').value = result.value;
                        form.submit();
                    }
                });
            }

            function confirmVerifyAll(action) {
                if (action === 'approve') {
                    Swal.fire({
                        title: 'Terima Semua Progress?',
                        text: "Apakah Anda yakin ingin menyetujui semua komponen progress yang sedang diajukan?",
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#198754',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Ya, Terima Semua',
                        cancelButtonText: 'Batal',
                        reverseButtons: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            const form = document.getElementById('global-verify-form');
                            form.action = "{{ url('/desa-cantik/progress') }}/" + "{{ $peserta->id }}" + "/verify-all";
                            document.getElementById('global-verify-action').value = 'approve';
                            document.getElementById('global-verify-alasan').value = '';
                            form.submit();
                        }
                    });
                } else {
                    Swal.fire({
                        title: 'Tolak Semua Progress?',
                        text: "Berikan alasan penolakan untuk seluruh komponen progress yang diajukan.",
                        icon: 'warning',
                        input: 'textarea',
                        inputPlaceholder: 'Masukkan alasan penolakan di sini...',
                        showCancelButton: true,
                        confirmButtonColor: '#dc3545',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Ya, Tolak Semua',
                        cancelButtonText: 'Batal',
                        reverseButtons: true,
                        inputValidator: (value) => {
                            if (!value) {
                                return 'Alasan penolakan wajib diisi!'
                            }
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            const form = document.getElementById('global-verify-form');
                            form.action = "{{ url('/desa-cantik/progress') }}/" + "{{ $peserta->id }}" + "/verify-all";
                            document.getElementById('global-verify-action').value = 'reject';
                            document.getElementById('global-verify-alasan').value = result.value;
                            form.submit();
                        }
                    });
                }
            }
            // Initialize Bootstrap Tooltips
            document.addEventListener('DOMContentLoaded', function () {
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
                var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl)
                })
            });
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
            0% {
                stroke-dasharray: 0 100;
            }
        }

        .circular-chart.orange .circle {
            stroke: var(--bps-orange);
        }

        .circular-chart.success .circle {
            stroke: #28a745;
        }

        .text-bps-orange {
            color: var(--bps-orange);
        }
    </style>
@endpush