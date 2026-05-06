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
                <form action="{{ route('desa-cantik.progress') }}" method="GET" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted mb-1">Periode</label>
                        <select name="periode_id" class="form-select border-0 bg-light" onchange="this.form.submit()">
                            <option value="">Semua Periode</option>
                            @foreach ($periodes as $p)
                                <option value="{{ $p->id }}" {{ request('periode_id') == $p->id ? 'selected' : '' }}>
                                    Tahun {{ $p->tahun }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if($isProvinsi)
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-muted mb-1">Kabupaten</label>
                            <select name="kabupaten_id" id="selectKabupaten"
                                class="form-select border-0 bg-light select2-kabupaten" onchange="this.form.submit()">
                                <option value="">Semua Kabupaten</option>
                                @foreach($kabupatens as $kab)
                                    <option value="{{ $kab->id }}" {{ request('kabupaten_id') == $kab->id ? 'selected' : '' }}>
                                        {{ $kab->nama_kabupaten }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @else
                        <input type="hidden" name="kabupaten_id" id="selectKabupaten" value="{{ $myKabupatenId }}">
                    @endif

                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted mb-1">Kecamatan</label>
                        <select name="kecamatan_id" id="selectKecamatan"
                            class="form-select border-0 bg-light select2-kecamatan" onchange="this.form.submit()">
                            @if($selectedKecamatan)
                                <option value="{{ $selectedKecamatan->id }}" selected>{{ $selectedKecamatan->nama_kecamatan }}
                                </option>
                            @else
                                <option value="">Semua Kecamatan</option>
                            @endif
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted mb-1">Desa</label>
                        <select name="desa_id" id="selectDesa" class="form-select border-0 bg-light select2-desa"
                            onchange="this.form.submit()">
                            @if($selectedDesa)
                                <option value="{{ $selectedDesa->id }}" selected>{{ $selectedDesa->nama_desa }}</option>
                            @else
                                <option value="">Semua Desa</option>
                            @endif
                        </select>
                    </div>

                    <div class="col-12 mt-3 d-flex flex-column flex-md-row justify-content-between gap-3">
                        <div class="d-flex gap-2 align-items-center flex-wrap">
                            @if($countAction > 0)
                                <button type="submit" name="action_needed" value="1"
                                    class="btn {{ request('action_needed') == '1' ? 'btn-danger' : 'btn-outline-danger' }} rounded-pill px-4 fw-bold">
                                    <i class="fas fa-exclamation-circle me-1"></i>
                                    {{ $countAction }} {{ $isProvinsi ? 'Perlu Diverifikasi' : 'Perlu Perbaikan' }}
                                </button>
                            @else
                                <span class="badge bg-light text-success border border-success p-2 rounded-pill px-3">
                                    <i class="fas fa-check-circle me-1"></i> {{ $isProvinsi ? 'Semua data sudah diverifikasi' : 'Tidak ada data yang perlu diperbaiki' }}
                                </span>
                            @endif

                            @if($isProvinsi && $countPerbaikan > 0)
                                <button type="submit" name="action_needed" value="perbaikan"
                                    class="btn {{ request('action_needed') == 'perbaikan' ? 'btn-warning text-dark' : 'btn-outline-warning text-dark' }} rounded-pill px-4 fw-bold">
                                    <i class="fas fa-clock me-1"></i>
                                    {{ $countPerbaikan }} Menunggu Perbaikan
                                </button>
                            @endif

                            @if($countDraf > 0)
                                <button type="submit" name="action_needed" value="draf"
                                    class="btn {{ request('action_needed') == 'draf' ? 'btn-info text-white' : 'btn-outline-info' }} rounded-pill px-4 fw-bold">
                                    <i class="fas fa-pencil-alt me-1"></i>
                                    {{ $countDraf }} Masih Draf
                                </button>
                            @endif
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('desa-cantik.progress') }}" class="btn btn-light rounded-pill px-4">Reset</a>
                            <button type="submit" class="btn btn-orange rounded-pill px-4">
                                <i class="fas fa-filter me-1"></i> Filter
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Grid Peserta --}}
        {{-- Card List --}}
        <div class="row g-4">
            @forelse($pesertas as $index => $p)
                @php
                    // Sort kegiatans specific to this peserta based on progress lock
                    $sortedKegiatans = $kegiatans->sortBy(function ($keg) use ($p) {
                        $prog = $p->progresses->where('kegiatan_id', $keg->id)->first();
                        $hasProgress = $prog ? 0 : 1; // 0 comes before 1 (DESC logic)
                        $urutan = $prog && $prog->urutan !== null ? $prog->urutan : $keg->urutan;
                        return sprintf('%01d-%05d-%05d', $hasProgress, $urutan, $keg->id);
                    })->values();

                    $totalUnits = 0;
                    $filledUnits = 0;
                    $totalMandatory = 0;
                    $filledMandatory = 0;

                    $mandatoryBuktiCount = count($mandatoryBuktiIds);

                    // 1. Kegiatan
                    foreach ($sortedKegiatans as $keg) {
                        $unitsInThisKeg = (2 + $mandatoryBuktiCount);
                        $totalUnits += $unitsInThisKeg;
                        if ($keg->is_wajib)
                            $totalMandatory += $unitsInThisKeg;

                        $prog = $p->progresses->where('kegiatan_id', $keg->id)->first();
                        if ($prog) {
                            $filledInThisKeg = 0;
                            if ($prog->target_tanggal)
                                $filledInThisKeg++;
                            if ($prog->realisasi_tanggal)
                                $filledInThisKeg++;
                            foreach ($mandatoryBuktiIds as $mid) {
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
                    $totalUnits += count($mandatoryOutputIds);
                    $totalMandatory += count($mandatoryOutputIds);
                    foreach ($mandatoryOutputIds as $oid) {
                        $out = $p->outputs->where('jenis_output_id', $oid)->first();
                        if ($out && !empty($out->link)) {
                            $filledUnits++;
                            $filledMandatory++;
                        }
                    }

                    // 3. Bukti Dukung
                    $totalUnits += count($mandatoryDukungIds);
                    $totalMandatory += count($mandatoryDukungIds);
                    foreach ($mandatoryDukungIds as $did) {
                        $duk = $p->buktiDukungs->where('jenis_bukti_id', $did)->first();
                        if ($duk && !empty($duk->link_file)) {
                            $filledUnits++;
                            $filledMandatory++;
                        }
                    }

                    $percent = $totalUnits > 0 ? round(($filledUnits / $totalUnits) * 100) : 0;
                    $mandatoryPercent = $totalMandatory > 0 ? round(($filledMandatory / $totalMandatory) * 100) : 0;
                @endphp
                <div class="col-12 col-md-6 col-xl-4">
                    <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden card-hover position-relative">
                        <div class="card-body p-4">
                            {{-- Header: Desa & Info --}}
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <span class="badge bg-light text-dark border">{{ $p->periode->tahun }}</span>
                                        @if($isProvinsi)
                                            <span
                                                class="badge bg-soft-orange text-orange">{{ $p->kabupaten->nama_kabupaten }}</span>
                                        @endif
                                    </div>
                                    <h5 class="fw-bold text-navy mb-1">{{ $p->desa->nama_desa }}</h5>
                                    <p class="text-muted small mb-0"><i class="fas fa-map-marker-alt me-1"></i> Kec.
                                        {{ $p->kecamatan->nama_kecamatan }}</p>
                                </div>
                                <div class="ms-2">
                                    <div class="rounded-circle bg-light d-flex align-items-center justify-content-center"
                                        style="width: 35px; height: 35px;">
                                        <span class="fw-bold text-muted small">#{{ $pesertas->firstItem() + $index }}</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Progress Bars --}}
                            <div class="mb-4 p-3 bg-light rounded-4">
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="small fw-bold text-navy">Progress Wajib</span>
                                        <span
                                            class="fw-bold small {{ $mandatoryPercent == 100 ? 'text-success' : 'text-orange' }}">{{ $mandatoryPercent }}%</span>
                                    </div>
                                    <div class="progress" style="height: 8px; border-radius: 4px; background-color: #e9ecef;">
                                        <div class="progress-bar {{ $mandatoryPercent == 100 ? 'bg-success' : 'bg-orange' }}"
                                            role="progressbar" style="width: {{ $mandatoryPercent }}%"
                                            aria-valuenow="{{ $mandatoryPercent }}" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>

                                <div>
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="small text-muted" style="font-size: 0.75rem;">Total Keseluruhan</span>
                                        <span class="text-muted small" style="font-size: 0.75rem;">{{ $percent }}%</span>
                                    </div>
                                    <div class="progress" style="height: 4px; border-radius: 2px; background-color: #dee2e6;">
                                        <div class="progress-bar bg-secondary opacity-50" role="progressbar"
                                            style="width: {{ $percent }}%" aria-valuenow="{{ $percent }}" aria-valuemin="0"
                                            aria-valuemax="100"></div>
                                    </div>
                                </div>
                            </div>

                            {{-- Badges Section --}}
                            <div class="mb-0">
                                <div class="d-flex align-items-center mb-2">
                                    <span class="small fw-bold text-navy me-2" style="font-size: 0.7rem;">KEGIATAN</span>
                                    <div class="flex-grow-1 border-bottom opacity-10"></div>
                                </div>
                                <div class="d-flex flex-wrap gap-1 mb-3">
                                    @foreach($sortedKegiatans as $keg)
                                        @php
                                            $prog = $p->progresses->where('kegiatan_id', $keg->id)->first();
                                            $statusClass = 'bg-light text-muted';
                                            if ($prog) {
                                                if ($prog->status == 'draf')
                                                    $statusClass = 'bg-info text-white';
                                                elseif ($prog->status == 'menunggu_verifikasi')
                                                    $statusClass = 'bg-warning text-dark';
                                                elseif ($prog->status == 'disetujui')
                                                    $statusClass = 'bg-success text-white';
                                                elseif ($prog->status == 'ditolak')
                                                    $statusClass = 'bg-danger text-white';
                                            }
                                        @endphp
                                        <span class="badge {{ $statusClass }} {{ $keg->is_wajib ? 'border border-secondary' : '' }}"
                                            style="width: 26px; height: 26px; display: flex; align-items: center; justify-content: center; font-size: 0.65rem; padding: 0; {{ $keg->is_wajib ? 'position: relative;' : '' }}"
                                            title="{{ $keg->nama_kegiatan }} ({{ $keg->is_wajib ? 'Wajib' : 'Opsional' }}): {{ $prog ? ($prog->status == 'draf' ? 'Draf' : ($prog->status == 'menunggu_verifikasi' ? 'Menunggu Verifikasi' : ($prog->status == 'disetujui' ? 'Terverifikasi' : 'Ditolak'))) : 'Belum' }}">
                                            {{ $loop->iteration }}
                                            @if($keg->is_wajib)
                                                <span
                                                    style="position: absolute; top: -4px; right: -2px; color: red; font-size: 0.5rem;">*</span>
                                            @endif
                                        </span>
                                    @endforeach
                                </div>

                                <div class="row g-3">
                                    <div class="col-6">
                                        <div class="small fw-bold text-navy mb-2" style="font-size: 0.7rem;">OUTPUT</div>
                                        <div class="d-flex flex-wrap gap-1">
                                            @foreach($allJenisOutputs as $jo)
                                                                            @php
                                                                                $out = $p->outputs->where('jenis_output_id', $jo->id)->first();
                                                                                $statusClass = 'bg-light text-muted';
                                                                                if ($out) {
                                                                                    if ($out->status == 'draf')
                                                                                        $statusClass = 'bg-info text-white';
                                                                                    elseif ($out->status == 'menunggu_verifikasi')
                                                                                        $statusClass = 'bg-warning text-dark';
                                                                                    elseif ($out->status == 'disetujui')
                                                                                        $statusClass = 'bg-success text-white';
                                                                                    elseif ($out->status == 'ditolak')
                                                                                        $statusClass = 'bg-danger text-white';
                                                                                }
                                                                            @endphp
                                                 <span
                                                                                class="badge {{ $statusClass }} {{ $jo->is_wajib ? 'border border-primary' : '' }}"
                                                                                style="font-size: 0.55rem; padding: 0.25rem 0.4rem; {{ $jo->is_wajib ? 'position: relative;' : '' }}"
                                                                                title="{{ $jo->nama_output }} ({{ $jo->is_wajib ? 'Wajib' : 'Opsional' }})">
                                                                                O{{ $loop->iteration }}
                                                                                @if($jo->is_wajib)
                                                                                    <span
                                                                                        style="position: absolute; top: -4px; right: -2px; color: red; font-size: 0.5rem;">*</span>
                                                                                @endif
                                                                            </span>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="small fw-bold text-navy mb-2" style="font-size: 0.7rem;">BUKTI LAINNYA</div>
                                        <div class="d-flex flex-wrap gap-1">
                                            @foreach($allJenisDukungs as $jd)
                                                                            @php
                                                                                $duk = $p->buktiDukungs->where('jenis_bukti_id', $jd->id)->first();
                                                                                $statusClass = 'bg-light text-muted';
                                                                                if ($duk) {
                                                                                    if ($duk->status == 'draf')
                                                                                        $statusClass = 'bg-info text-white';
                                                                                    elseif ($duk->status == 'menunggu_verifikasi')
                                                                                        $statusClass = 'bg-warning text-dark';
                                                                                    elseif ($duk->status == 'disetujui')
                                                                                        $statusClass = 'bg-success text-white';
                                                                                    elseif ($duk->status == 'ditolak')
                                                                                        $statusClass = 'bg-danger text-white';
                                                                                }
                                                                            @endphp
                                                <span
                                                                                class="badge {{ $statusClass }} {{ $jd->is_wajib ? 'border border-info' : '' }}"
                                                                                style="font-size: 0.55rem; padding: 0.25rem 0.4rem; {{ $jd->is_wajib ? 'position: relative;' : '' }}"
                                                                                title="{{ $jd->nama_bukti }} ({{ $jd->is_wajib ? 'Wajib' : 'Opsional' }})">
                                                                                B{{ $loop->iteration }}
                                                                                @if($jd->is_wajib)
                                                                                    <span
                                                                                        style="position: absolute; top: -4px; right: -2px; color: red; font-size: 0.5rem;">*</span>
                                                                                @endif
                                                                            </span>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Card Footer: Action Button --}}
                        <div class="card-footer bg-white border-0 p-4 pt-0">
                            @php
                                $isPending = $p->progresses->where('status', 'menunggu_verifikasi')->isNotEmpty() ||
                                    $p->outputs->where('status', 'menunggu_verifikasi')->isNotEmpty() ||
                                    $p->buktiDukungs->where('status', 'menunggu_verifikasi')->isNotEmpty();
                            @endphp

                            @if($isPending)
                                @if($isProvinsi)
                                    <a href="{{ route('desa-cantik.progress.detail', $p->id) }}"
                                        class="btn btn-warning rounded-pill w-100 py-2 fw-bold shadow-sm">
                                        <i class="fas fa-check-circle me-1"></i> Verifikasi Progress
                                    </a>
                                @else
                                    <a href="{{ route('desa-cantik.progress.detail', $p->id) }}"
                                        class="btn btn-info rounded-pill w-100 py-2 fw-bold text-white shadow-sm">
                                        <i class="fas fa-eye me-1"></i> Lihat Progress
                                    </a>
                                @endif
                            @else
                                <a href="{{ route('desa-cantik.progress.detail', $p->id) }}"
                                    class="btn btn-outline-orange rounded-pill w-100 py-2 fw-bold shadow-sm">
                                    <i class="fas fa-edit me-1"></i> Update Progress
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="card border-0 shadow-sm rounded-4 p-5 text-center text-muted">
                        <i class="fas fa-tasks fa-3x mb-3 opacity-20"></i>
                        <p class="mb-0 fs-5">Belum ada data peserta desa.</p>
                    </div>
                </div>
            @endforelse
        </div>

        <div class="mt-4">
            {{ $pesertas->links('pagination::bootstrap-5') }}
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
                <span class="me-3"><span class="badge bg-light text-muted border border-secondary me-1"
                        style="position: relative;">1<span
                            style="position: absolute; top: -5px; right: -2px; color: red; font-size: 0.5rem;">*</span></span>
                    Wajib</span>

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
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<style>
    .select2-container--default .select2-selection--single {
        height: 38px;
        border: 0;
        background-color: #f8f9fa;
        border-radius: 0.375rem;
        padding: 4px 12px;
        display: flex;
        align-items: center;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px;
        right: 10px;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 28px;
        padding-left: 0;
    }

    .select2-dropdown {
        border: 1px solid #e9ecef;
        border-radius: 0.5rem;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }

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

    .bg-soft-orange {
        background-color: rgba(253, 126, 20, 0.1) !important;
    }

    .card-hover {
        transition: all 0.3s ease;
    }

    .card-hover:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.12) !important;
    }

    .text-navy {
        color: #0a2558;
    }

    .opacity-10 {
        opacity: 0.1;
    }
</style>
@push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function () {
            const urlKecamatan = '/desa-cantik/ajax/kecamatan';
            const urlDesa = '/desa-cantik/ajax/desa';

            function getKabupatenId() { return $('#selectKabupaten').val(); }

            if ($('.select2-kabupaten').length) {
                $('.select2-kabupaten').select2({
                    placeholder: "Semua Kabupaten"
                });

                $('#selectKabupaten').on('change', function () {
                    $('#selectKecamatan').val(null).trigger('change');
                    $('#selectDesa').val(null).trigger('change');
                });
            }

            $('#selectKecamatan').select2({
                placeholder: 'Semua Kecamatan',
                allowClear: true,
                ajax: {
                    url: urlKecamatan,
                    dataType: 'json',
                    delay: 250,
                    data: params => ({
                        search: params.term || '',
                        limit: 10,
                        kabupaten_id: getKabupatenId(),
                    }),
                    processResults: data => ({
                        results: data.map(k => ({ id: k.id, text: k.nama_kecamatan }))
                    }),
                    cache: true
                }
            }).on('change', function () {
                $('#selectDesa').val(null).trigger('change');
            });

            $('#selectDesa').select2({
                placeholder: 'Semua Desa',
                allowClear: true,
                ajax: {
                    url: urlDesa,
                    dataType: 'json',
                    delay: 250,
                    data: params => ({
                        search: params.term || '',
                        limit: 10,
                        kecamatan_id: $('#selectKecamatan').val(),
                        is_filter: 1
                    }),
                    processResults: data => ({
                        results: data.map(d => ({
                            id: d.id,
                            text: d.text
                        }))
                    }),
                    cache: true
                }
            });
        });
    </script>
@endpush