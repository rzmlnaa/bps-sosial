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

        <form action="{{ route('desa-cantik.progress.store', $peserta->id) }}" method="POST" id="form-progress">
            <input type="hidden" name="action_type" id="action_type" value="draft">
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
                                if($prog->status == 'draft') { $statusLabel = 'Draf'; $statusColor = 'info'; }
                                elseif($prog->status == 'menunggu_verifikasi') { $statusLabel = 'Menunggu Verifikasi'; $statusColor = 'warning'; }
                                elseif($prog->status == 'disetujui') { $statusLabel = 'Terverifikasi'; $statusColor = 'success'; }
                                elseif($prog->status == 'ditolak') { $statusLabel = 'Perbaikan'; $statusColor = 'danger'; }
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
                                        @if(!$prog || $prog->status == 'draft' || $prog->status == 'ditolak')
                                            <div class="mt-3">
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label small fw-bold">Target Tanggal @if($keg->is_wajib)<span class="text-danger">*</span>@endif</label>
                                                        <input type="date" name="progress[{{ $keg->id }}][target_tanggal]" id="target_{{ $keg->id }}" class="form-control" value="{{ $prog ? $prog->target_tanggal : '' }}" onchange="updateMinDate({{ $keg->id }})">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small fw-bold">Realisasi Tanggal @if($keg->is_wajib)<span class="text-danger">*</span>@endif</label>
                                                        <input type="date" name="progress[{{ $keg->id }}][realisasi_tanggal]" id="realisasi_{{ $keg->id }}" class="form-control" value="{{ $prog ? $prog->realisasi_tanggal : '' }}" min="{{ $prog ? $prog->target_tanggal : '' }}">
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
                                                                    <label class="small fw-bold d-block mb-1 text-navy">{{ $mb->nama_bukti }} <span class="text-danger">*</span></label>
                                                                    <input type="hidden" name="bukti[{{ $keg->id }}][jenis_id][]" value="{{ $mb->id }}">
                                                                     <input type="text" name="bukti[{{ $keg->id }}][link][]" class="form-control" 
                                                                         value="{{ $existingMb ? $existingMb->link_file : '' }}" 
                                                                         data-placeholder="Masukkan link folder atau deskripsi bukti {{ $mb->nama_bukti }}..."
                                                                         placeholder="Masukkan link folder atau deskripsi bukti {{ $mb->nama_bukti }}...">
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

                                                @if($isProvinsi && $prog->status == 'menunggu_verifikasi')
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
                                        <label class="small fw-bold d-block mb-1 text-navy">{{ $jo->nama_output }} <span class="text-danger">*</span></label>
                                        <input type="hidden" name="output_jenis_id[]" value="{{ $jo->id }}">
                                        <input type="text" name="output_link[]" class="form-control" value="{{ $out ? $out->link : '' }}" placeholder="Link {{ $jo->nama_output }}...">
                                    </div>
                                @endforeach
                                
                                <label class="small fw-bold d-block mb-2 text-muted mt-4">Output Lainnya (Opsional)</label>
                                {{-- Existing Optional Output --}}
                                @foreach($peserta->outputs->whereIn('jenis_output_id', $jenisOutputOptional->pluck('id')) as $out)
                                    <div class="d-flex gap-2 mb-2 align-items-center">
                                        <select name="output_jenis_id[]" class="form-select w-50">
                                            <option value="">Pilih Jenis Output...</option>
                                            @foreach($jenisOutputOptional as $jo)
                                                <option value="{{ $jo->id }}" {{ $out->jenis_output_id == $jo->id ? 'selected' : '' }}>{{ $jo->nama_output }}</option>
                                            @endforeach
                                        </select>
                                        <input type="text" name="output_link[]" class="form-control" value="{{ $out->link }}" placeholder="Link output...">
                                    </div>
                                @endforeach

                                {{-- Always show one empty optional row --}}
                                <div class="d-flex gap-2 mb-2 align-items-center">
                                    <select name="output_jenis_id[]" class="form-select w-50">
                                        <option value="">Pilih Jenis Output...</option>
                                        @foreach($jenisOutputOptional as $jo)
                                            <option value="{{ $jo->id }}">{{ $jo->nama_output }}</option>
                                        @endforeach
                                    </select>
                                     <input type="text" name="output_link[]" class="form-control" placeholder="Link output...">
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
                                        <label class="small fw-bold d-block mb-1 text-navy">{{ $jd->nama_bukti }} <span class="text-danger">*</span></label>
                                        <input type="hidden" name="dukung_jenis_id[]" value="{{ $jd->id }}">
                                        <input type="text" name="dukung_link[]" class="form-control" value="{{ $duk ? $duk->link_file : '' }}" placeholder="Link {{ $jd->nama_bukti }}...">
                                    </div>
                                @endforeach

                                <label class="small fw-bold d-block mb-2 text-muted mt-4">Bukti Lainnya (Opsional)</label>
                                {{-- Existing Optional Dukung --}}
                                @foreach($peserta->buktiDukungs->whereIn('jenis_bukti_id', $jenisDukungOptional->pluck('id')) as $duk)
                                    <div class="d-flex gap-2 mb-2 align-items-center">
                                        <select name="dukung_jenis_id[]" class="form-select w-50">
                                            <option value="">Pilih Jenis Bukti...</option>
                                            @foreach($jenisDukungOptional as $jd)
                                                <option value="{{ $jd->id }}" {{ $duk->jenis_bukti_id == $jd->id ? 'selected' : '' }}>{{ $jd->nama_bukti }}</option>
                                            @endforeach
                                        </select>
                                        <input type="text" name="dukung_link[]" class="form-control" value="{{ $duk->link_file }}" placeholder="Link bukti...">
                                    </div>
                                @endforeach

                                {{-- Always show one empty optional row --}}
                                <div class="d-flex gap-2 mb-2 align-items-center">
                                    <select name="dukung_jenis_id[]" class="form-select w-50">
                                        <option value="">Pilih Jenis Bukti...</option>
                                        @foreach($jenisDukungOptional as $jd)
                                            <option value="{{ $jd->id }}">{{ $jd->nama_bukti }}</option>
                                        @endforeach
                                    </select>
                                     <input type="text" name="dukung_link[]" class="form-control" placeholder="Link bukti...">
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
                            
                            <div class="alert alert-info border-0 small mt-4">
                                <i class="fas fa-info-circle me-1"></i>
                                Admin Provinsi akan melakukan verifikasi pada setiap tahapan kegiatan yang telah diajukan.
                            </div>

                            <div class="d-grid gap-2 mt-4">
                                <button type="submit" onclick="document.getElementById('action_type').value='draft'" class="btn btn-light rounded-pill">
                                    <i class="fas fa-save me-1"></i> Simpan Draf
                                </button>
                                <button type="button" onclick="confirmSubmit()" class="btn btn-orange rounded-pill">
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
            
            const optionalRows = container.querySelectorAll('.d-flex');
            if (optionalRows.length === 0) return;
            
            const lastRow = optionalRows[optionalRows.length - 1];
            const newRow = lastRow.cloneNode(true);
            
            const select = newRow.querySelector('select');
            const input = newRow.querySelector('input[type="text"]');
            if (select) select.value = '';
            if (input) input.value = '';
            
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

        function updateMinDate(kegId) {
            const targetInput = document.getElementById(`target_${kegId}`);
            if (!targetInput) return;

            const targetVal = targetInput.value;
            const realisasiInput = document.getElementById(`realisasi_${kegId}`);
            const container = document.getElementById(`bukti-container-${kegId}`);
            const btnAdd = container.nextElementSibling; // Tombol "Tambah Baris Bukti"

            if (realisasiInput) {
                realisasiInput.min = targetVal;
                if (!targetVal) {
                    realisasiInput.value = '';
                } else if (realisasiInput.value && realisasiInput.value < targetVal) {
                    realisasiInput.value = '';
                }
            }

            // Toggle Bukti fields
            const isNoDate = !targetVal;
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
        }

        document.addEventListener('DOMContentLoaded', function() {
            @foreach($kegiatans as $keg)
                updateMinDate({{ $keg->id }});
            @endforeach
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
