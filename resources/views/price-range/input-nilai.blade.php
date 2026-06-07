@extends('layouts.admin')

@section('title', 'Input Nilai RH Kabupaten - BPS Kalbar')

@section('content')
    <style>
        .table-responsive {
            max-height: 80vh;
            /* Limit height to enable scrolling within container if needed, or stick to viewport */
            overflow-y: auto;
        }

        thead {
            position: sticky;
            top: 0;
            z-index: 1020;
            background-color: #f8fafc;
            /* Match bg-light */
        }

        thead tr:nth-child(1) th {
            position: sticky;
            top: 0;
            z-index: 1021;
            background-color: #f8fafc !important;
            box-shadow: inset 0 -1px 0 #f1f5f9;
        }

        thead tr:nth-child(2) th {
            position: sticky;
            top: 45px;
            /* Adjust based on Row 1 height */
            z-index: 1021;
            background-color: #f8fafc !important;
            box-shadow: inset 0 -1px 0 #f1f5f9;
        }

        /* Ensure category rows also look good if they are sticky (optional, but requested behavior usually implies headers) */
        .bg-light.category-header {
            position: sticky;
            top: 90px;
            /* Adjust based on Row 1 + Row 2 height */
            z-index: 1010;
        }
    </style>
    <div class="fade-in-up">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
            <div>
                <h2 class="fw-bold mb-1" style="color: var(--bps-orange);">Input Nilai RH Kabupaten</h2>
                <p class="text-muted mb-0">Input dan kelola rentang harga komoditas per kabupaten</p>
            </div>
            <div class="mt-3 mt-md-0 d-flex gap-2">
                <a href="{{ route('price-range.index') }}" class="btn btn-outline-secondary fw-bold shadow-sm">
                    <i class="fas fa-arrow-left me-1"></i> Kembali
                </a>
            </div>

        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                <i class="fas fa-exclamation-triangle me-1"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(isset($rejectedSummary) && $rejectedSummary->count() > 0)
            <div class="alert alert-danger border-danger-custom shadow-sm mb-4" role="alert" style="background-color: #fff5f5;">
                <div class="d-flex align-items-center mb-2">
                    <i class="fas fa-exclamation-circle text-danger fs-4 me-2"></i>
                    <div>
                        <h5 class="alert-heading fw-bold mb-0 text-danger">Perhatian: Terdapat Data Ditolak!</h5>
                        <p class="mb-0 small text-muted">Beberapa data yang Anda ajukan telah ditolak oleh Verifikator. Mohon
                            perbaiki data berikut:</p>
                    </div>
                </div>
                <hr class="text-danger opacity-25 my-2">
                <div class="table-responsive">
                    <table class="table table-sm table-borderless mb-0 small">
                        <thead class="text-secondary border-bottom">
                            <tr>
                                <th>Sumber Data</th>
                                <th>Komoditas</th>
                                <th>Nilai Diajukan</th>
                                <th>Alasan Penolakan</th>
                                <th>Waktu</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rejectedSummary as $reject)
                                <tr>
                                    <td class="fw-bold text-dark">{{ $reject->source }}</td>
                                    <td>{{ $reject->komoditas_nama }}</td>
                                    <td>
                                        <span class="text-danger">Min: {{ number_format($reject->min, 0, ',', '.') }}</span><br>
                                        <span class="text-danger">Max: {{ number_format($reject->max, 0, ',', '.') }}</span>
                                    </td>
                                    <td class="fw-bold text-danger">"{{ $reject->reason }}"</td>
                                    <td class="text-muted">{{ \Carbon\Carbon::parse($reject->verified_at)->format('d M H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- Filters & Info -->
        <div class="row g-3 mb-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                    <div class="card-body p-4">
                        <form action="{{ route('rh-nilai.index') }}" method="GET" class="row g-3 align-items-end"
                            id="filter-form">
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted text-uppercase">Tahun RH Aktif</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-0"><i
                                            class="fas fa-calendar-alt text-muted"></i></span>
                                    <input type="text" class="form-control bg-light border-0 fw-bold"
                                        value="{{ $activeYear->tahun }}" readonly>
                                    <input type="hidden" name="rh_tahun_id" value="{{ $activeYear->id }}">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted text-uppercase">Kabupaten/Kota</label>
                                <!-- <select name="kabupaten_id" class="form-select border-0 bg-light shadow-none"
                                                                                                                                                        onchange="this.form.submit()">
                                                                                                                                                        @foreach($kabupatens as $kab)
                                                                                                                                                            <option value="{{ $kab->id }}" {{ $selectedKabupatenId == $kab->id ? 'selected' : '' }}>
                                                                                                                                                                [{{ $kab->kode_kab }}] {{ $kab->nama_kabupaten }}
                                                                                                                                                            </option>
                                                                                                                                                        @endforeach
                                                                                                                                                    </select> -->


                                @if (auth()->user()->kabupaten->kode_kab == '6100')
                                    <select name="kabupaten_id" class="form-select border-0 bg-light shadow-none"
                                        onchange="this.form.submit()">
                                        @foreach($kabupatens as $kab)

                                            <option value="{{ $kab->id }}" {{ $selectedKabupatenId == $kab->id ? 'selected' : '' }}>
                                                [{{ $kab->kode_kab }}] {{ $kab->nama_kabupaten }}
                                            </option>
                                        @endforeach
                                    </select>

                                @else
                                    <select name="kabupaten_id" class="form-select border-0 bg-light shadow-none"
                                        onchange="this.form.submit()">
                                        <option value="{{ auth()->user()->kabupaten->id }}">
                                            [{{ auth()->user()->kabupaten->kode_kab }}]
                                            {{ auth()->user()->kabupaten->nama_kabupaten }}
                                        </option>
                                    </select>
                                @endif



                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted text-uppercase">Pilih Header Perubahan
                                    (Optional)</label>
                                <select name="revision_id" class="form-select border-0 bg-light shadow-none"
                                    onchange="this.form.submit()">
                                    <option value="">-- Master Nilai (Input Utama) --</option>
                                    @if($revisions->count() > 0)
                                        <option value="all" {{ $selectedRevisionId == 'all' ? 'selected' : '' }}>-- Semua
                                            Perubahan --
                                        </option>
                                    @endif
                                    @foreach($revisions as $rev)
                                        <option value="{{ $rev->id }}" {{ $selectedRevisionId == $rev->id ? 'selected' : '' }}>
                                            {{ $rev->label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm h-100"
                    style="border-radius: 12px; background: linear-gradient(135deg, var(--bps-orange), #007bbd);">
                    <div class="card-body p-4 text-white d-flex flex-column justify-content-center">
                        <div class="d-flex align-items-center mb-2">
                            <div class="bg-white bg-opacity-25 rounded-circle p-2 me-3">
                                <i class="fas fa-info-circle fa-lg"></i>
                            </div>
                            <h6 class="mb-0 fw-bold">Panduan Admin Kabupaten/Kota</h6>
                        </div>
                        <p class="small mb-0 opacity-75">Jika tidak terdapat perubahan RH, kolom tidak perlu diisi. Sistem
                            akan menampilkan nilai RH terakhir pada periode atau tahun sebelumnya</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Table -->
        <div class="text-end mb-2">
            <button type="submit" form="form-save-nilai" class="btn text-white fw-bold shadow-sm"
                style="background-color: var(--bps-blue);">
                <i class="fas fa-save me-1"></i> Simpan Data
            </button>
        </div>

        <div class="card border-0 shadow-sm overflow-hidden" style="border-radius: 12px;" id="table">
            <form action="{{ route('rh-nilai.save') }}" method="POST" id="form-save-nilai">
                @csrf
                <input type="hidden" name="rh_tahun_id" value="{{ $activeYear->id }}">
                <input type="hidden" name="kabupaten_id" value="{{ $selectedKabupatenId }}">
                <input type="hidden" name="revision_id" value="{{ $selectedRevisionId }}">

                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0">
                        <thead class="bg-light text-center align-middle">
                            @php
                                $latestRevisionId = $latestRevisionId ?? null; // Ensure variable exists
                                $isMasterEditable = empty($latestRevisionId);

                                $showMaster = ($selectedRevisionId === 'all' || !$selectedRevisionId || $displayRevisions->count() < 2);
                                $showPrev = !empty($prevYearFinal) && (
                                    $selectedRevisionId === 'all' ||
                                    !$selectedRevisionId
                                );
                            @endphp
                            <tr>
                                <th rowspan="2" class="ps-4" style="min-width: 250px;">NAMA</th>
                                <th rowspan="2" style="width: 100px;">SATUAN</th>
                                <th rowspan="2" style="width: 100px;">BATAS SELISIH</th>
                                @if($showPrev)
                                    <th colspan="3" class="bg-secondary bg-opacity-10 text-secondary border-secondary">
                                        {{ strtoupper($prevYearLabel) }}
                                    </th>
                                @endif
                                @if($showMaster)
                                    <th colspan="3" class="bg-blue-light text-blue">MASTER NILAI
                                        ({{ substr($activeYear->tahun, -2) }})</th>
                                @endif

                                @foreach($displayRevisions as $rev)
                                    <th colspan="3" class="bg-orange-light text-orange">{{ strtoupper($rev->label) }}</th>
                                @endforeach
                            </tr>
                            <tr>
                                @if($showPrev)
                                    <th style="width: 120px;"
                                        class="bg-secondary bg-opacity-10 text-secondary small border-secondary">MIN</th>
                                    <th style="width: 120px;"
                                        class="bg-secondary bg-opacity-10 text-secondary small border-secondary">MAX</th>
                                    <th style="width: 200px;"
                                        class="bg-secondary bg-opacity-10 text-secondary small border-secondary">ALASAN</th>
                                @endif
                                @if($showMaster)
                                    <th style="width: 120px;" class="bg-blue-light text-blue small">
                                        MIN_{{ substr($activeYear->tahun, -2) }}</th>
                                    <th style="width: 120px;" class="bg-blue-light text-blue small">
                                        MAX_{{ substr($activeYear->tahun, -2) }}</th>
                                    <th style="width: 200px;" class="bg-blue-light text-blue small">ALASAN</th>
                                @endif

                                @foreach($displayRevisions as $rev)
                                    <th style="width: 120px;" class="bg-orange-light text-orange small">MIN_EDIT</th>
                                    <th style="width: 120px;" class="bg-orange-light text-orange small">MAX_EDIT</th>
                                    <th style="width: 200px;" class="bg-orange-light text-orange small">ALASAN</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($categories as $category)
                                <tr class="bg-light">
                                    @php
                                        // 3 Fixed columns + (3 if Prev Shown) + (3 if Master shown) + (3 per Revision)
                                        $colspan = 3 + ($showPrev ? 3 : 0) + ($showMaster ? 3 : 0) + ($displayRevisions->count() * 3);
                                    @endphp
                                    <td colspan="{{ $colspan }}"
                                        class="ps-4 fw-bold text-muted small text-uppercase py-2 category-header">
                                        <i class="fas fa-folder-open me-1"></i> {{ $category->nama_kategori }}
                                    </td>
                                </tr>
                                @foreach($category->komoditas as $komo)
                                    @php
                                        $master = $masterNilai->get($komo->id);

                                        // Check if Master has explicit values (Min OR Max is set)
                                        $hasMasterData = $master && (($master->min_nilai ?? null) !== null || ($master->max_nilai ?? null) !== null);

                                        if ($hasMasterData) {
                                            // Use explicit Master values (even if reason is null, do NOT fallback)
                                            $mMin = $master->min_nilai;
                                            $mMax = $master->max_nilai;
                                            $mAlasan = $master->alasan;
                                        } else {
                                            // Master is empty
                                            $mMin = null;
                                            $mMax = null;
                                            $mAlasan = null;

                                            // If Read-Only mode (Revisions exist), Fallback to Prev Year used as Baseline
                                            if (!$isMasterEditable) {
                                                $prevData = $prevYearFinal[$komo->id] ?? [];
                                                $mMin = $prevData['min'] ?? null;
                                                $mMax = $prevData['max'] ?? null;
                                                $mAlasan = $prevData['alasan'] ?? null;
                                            }
                                        }
                                    @endphp
                                    <tr>
                                        <td class="ps-4">{{ $komo->nama_komoditas }}</td>
                                        <td class="text-center"><span
                                                class="badge bg-light text-dark border fw-normal">{{ $komo->satuan ?? 'Kg' }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-light text-dark border fw-normal">
                                                {{ $komo->batas_selisih_harga ? number_format($komo->batas_selisih_harga, 0, ',', '.') : '-' }}
                                            </span>
                                        </td>

                                        <!-- Prev Year Values -->
                                        @if($showPrev)
                                            @php
                                                $prevData = $prevYearFinal[$komo->id] ?? [];
                                            @endphp
                                            <td class="p-1 text-center align-middle bg-secondary bg-opacity-10 text-secondary">
                                                {{ isset($prevData['min']) ? number_format($prevData['min'], 0, ',', '.') : '-' }}
                                            </td>
                                            <td class="p-1 text-center align-middle bg-secondary bg-opacity-10 text-secondary">
                                                {{ isset($prevData['max']) ? number_format($prevData['max'], 0, ',', '.') : '-' }}
                                            </td>
                                            <td
                                                class="p-1 text-center align-middle bg-secondary bg-opacity-10 text-secondary small fst-italic">
                                                {{ $prevData['alasan'] ?? '-' }}
                                            </td>
                                        @endif

                                        <!-- Master Inputs -->
                                        @if($showMaster)
                                            @if($isMasterEditable)
                                                @php
                                                    $mStatus = $master->verification_status ?? 'pending';
                                                    $mIsRejected = $mStatus === 'rejected';
                                                    $mReason = $master->rejection_reason ?? '';
                                                @endphp
                                                <td class="p-1 {{ $mIsRejected ? 'bg-danger-faded' : '' }}">
                                                    @php
                                                        $mMinVal = isset($master->min_nilai) ? number_format($master->min_nilai, 0, ',', '.') : '';
                                                        $mMaxVal = isset($master->max_nilai) ? number_format($master->max_nilai, 0, ',', '.') : '';

                                                        $mShowLimitWarning = false;
                                                        // Fallback logic for empty Master check Prev Year
                                                        if (!$hasMasterData && !empty($prevYearFinal[$komo->id])) {
                                                            $pData = $prevYearFinal[$komo->id];
                                                            $pDiff = ($pData['max'] ?? 0) - ($pData['min'] ?? 0);
                                                            $pHasAlasan = !empty(trim($pData['alasan'] ?? ''));
                                                            $limit = $komo->batas_selisih_harga ?? 0;
                                                            if ($pDiff > $limit && !$pHasAlasan) {
                                                                $mMinVal = number_format($pData['min'], 0, ',', '.');
                                                                $mMaxVal = number_format($pData['max'], 0, ',', '.');
                                                                $mShowLimitWarning = true;
                                                            }
                                                        }
                                                    @endphp
                                                    <input type="text" name="master[{{ $komo->id }}][min]"
                                                        class="form-control form-control-sm border-0 bg-blue-faded text-center format-ribuan {{ $mIsRejected ? 'text-danger fw-bold' : '' }}"
                                                        placeholder="Min" value="{{ $mMinVal }}"
                                                        data-batas="{{ $komo->batas_selisih_harga ?? 0 }}">
                                                </td>
                                                <td class="p-1 {{ $mIsRejected ? 'bg-danger-faded' : '' }}">
                                                    <input type="text" name="master[{{ $komo->id }}][max]"
                                                        class="form-control form-control-sm border-0 bg-blue-faded text-center format-ribuan {{ $mIsRejected ? 'text-danger fw-bold' : '' }}"
                                                        placeholder="Max" value="{{ $mMaxVal }}"
                                                        data-batas="{{ $komo->batas_selisih_harga ?? 0 }}">
                                                </td>
                                                <td class="p-1 {{ $mIsRejected ? 'bg-danger-faded' : '' }}">
                                                    @if($mIsRejected)
                                                        <div class="text-danger x-small fw-bold mb-1"
                                                            style="font-size: 0.7rem; line-height: 1.1;">
                                                            <i class="fas fa-times-circle me-1"></i>Ditolak: {{ $mReason }}
                                                        </div>
                                                    @endif
                                                    <textarea name="master[{{ $komo->id }}][alasan]" rows="1"
                                                        class="form-control form-control-sm border-0 bg-blue-faded {{ ($master->max_nilai ?? 0) - ($master->min_nilai ?? 0) > ($komo->batas_selisih_harga ?? 0) || $mShowLimitWarning ? '' : 'd-none' }}"
                                                        placeholder="Berikan alasan"
                                                        data-batas="{{ $komo->batas_selisih_harga ?? 0 }}">{{ $master->alasan ?? '' }}</textarea>
                                                    @if($mShowLimitWarning)
                                                        <div class="text-danger mt-1" style="font-size: 0.65rem; line-height: 1.1;">
                                                            <i class="fas fa-info-circle"></i> Harga ini merupakan turunan dari periode
                                                            sebelumnya yang melewati batas selisih harga terbaru. Anda wajib mengubah harga atau
                                                            mengisi Alasan.
                                                        </div>
                                                    @endif
                                                </td>
                                            @else
                                                <td class="p-1 text-center align-middle bg-light text-muted">
                                                    {{ isset($mMin) ? number_format($mMin, 0, ',', '.') : '-' }}
                                                </td>
                                                <td class="p-1 text-center align-middle bg-light text-muted">
                                                    {{ isset($mMax) ? number_format($mMax, 0, ',', '.') : '-' }}
                                                </td>
                                                <td class="p-1 text-center align-middle bg-light text-muted small fst-italic">
                                                    {{ $mAlasan ?? '-' }}
                                                </td>
                                            @endif
                                        @endif

                                        <!-- Revision Inputs -->
                                        @foreach($displayRevisions as $rev)
                                            @php
                                                // Check if this is the Latest Revision
                                                $isRevEditable = ($rev->id == $latestRevisionId);

                                                // For Editable: Use RAW values (from allRevisionNilai)
                                                // For Read-Only: Use EFFECTIVE values (from effectiveValues)

                                                if ($isRevEditable) {
                                                    $rawRevData = $allRevisionNilai->get($rev->id)?->get($komo->id);
                                                    $valMin = $rawRevData->min_edit ?? '';
                                                    $valMax = $rawRevData->max_edit ?? '';
                                                    $valAlasan = $rawRevData->alasan ?? '';
                                                } else {
                                                    $effData = $effectiveValues->get($rev->id)[$komo->id] ?? null;
                                                    $valMin = $effData['min'] ?? '-';
                                                    $valMax = $effData['max'] ?? '-';
                                                    $valAlasan = '-'; // We don't track historical reasons strictly in effective array, usually shown if relevant or just skip for readonly summary.
                                                }
                                            @endphp

                                            @if($isRevEditable)
                                                @php
                                                    $revStatus = $rawRevData->verification_status ?? 'pending';
                                                    $revIsRejected = $revStatus === 'rejected';
                                                    $revReason = $rawRevData->rejection_reason ?? '';
                                                @endphp
                                                <td class="p-1 {{ $revIsRejected ? 'bg-danger-faded' : '' }}">
                                                    @php
                                                        $displayMin = is_numeric($valMin) ? number_format($valMin, 0, ',', '.') : $valMin;
                                                        $displayMax = is_numeric($valMax) ? number_format($valMax, 0, ',', '.') : $valMax;

                                                        $rShowLimitWarning = false;
                                                        // Fallback logic for empty Revision check Previous State
                                                        if (($valMin === '' || $valMin === null) && ($valMax === '' || $valMax === null)) {
                                                            // Find previous state
                                                            $revIndex = $revisions->search(fn($r) => $r->id == $rev->id);
                                                            $prevState = null;
                                                            if ($revIndex > 0) {
                                                                $prevId = $revisions[$revIndex - 1]->id;
                                                                $prevState = $effectiveValues->get($prevId)[$komo->id] ?? null;
                                                            } else {
                                                                $prevState = $initialState[$komo->id] ?? null;
                                                            }

                                                            if ($prevState) {
                                                                $pDiff = ($prevState['max'] ?? 0) - ($prevState['min'] ?? 0);
                                                                $pHasAlasan = !empty(trim($prevState['alasan'] ?? ''));
                                                                $limit = $komo->batas_selisih_harga ?? 0;
                                                                if ($pDiff > $limit && !$pHasAlasan) {
                                                                    $displayMin = number_format($prevState['min'], 0, ',', '.');
                                                                    $displayMax = number_format($prevState['max'], 0, ',', '.');
                                                                    $rShowLimitWarning = true;
                                                                }
                                                            }
                                                        }
                                                    @endphp
                                                    <input type="text" name="revision[{{ $rev->id }}][{{ $komo->id }}][min]"
                                                        class="form-control form-control-sm border-0 bg-orange-faded text-center format-ribuan {{ $revIsRejected ? 'text-danger fw-bold' : '' }}"
                                                        placeholder="Edit Min" value="{{ $displayMin }}"
                                                        data-batas="{{ $komo->batas_selisih_harga ?? 0 }}">
                                                </td>
                                                <td class="p-1 {{ $revIsRejected ? 'bg-danger-faded' : '' }}">
                                                    <input type="text" name="revision[{{ $rev->id }}][{{ $komo->id }}][max]"
                                                        class="form-control form-control-sm border-0 bg-orange-faded text-center format-ribuan {{ $revIsRejected ? 'text-danger fw-bold' : '' }}"
                                                        placeholder="Edit Max" value="{{ $displayMax }}"
                                                        data-batas="{{ $komo->batas_selisih_harga ?? 0 }}">
                                                </td>
                                                <td class="p-1 {{ $revIsRejected ? 'bg-danger-faded' : '' }}">
                                                    @if($revIsRejected)
                                                        <div class="text-danger x-small fw-bold mb-1"
                                                            style="font-size: 0.7rem; line-height: 1.1;">
                                                            <i class="fas fa-times-circle me-1"></i>Ditolak: {{ $revReason }}
                                                        </div>
                                                    @endif
                                                    <textarea name="revision[{{ $rev->id }}][{{ $komo->id }}][alasan]" rows="1"
                                                        class="form-control form-control-sm border-0 bg-orange-faded {{ (((float) $valMax - (float) $valMin) > ($komo->batas_selisih_harga ?? 0) && $valMax !== '' && $valMin !== '') || $rShowLimitWarning ? '' : 'd-none' }}"
                                                        placeholder="Berikan alasan"
                                                        data-batas="{{ $komo->batas_selisih_harga ?? 0 }}">{{ $valAlasan }}</textarea>
                                                    @if($rShowLimitWarning)
                                                        <div class="text-danger mt-1" style="font-size: 0.65rem; line-height: 1.1;">
                                                            <i class="fas fa-info-circle"></i> Harga ini merupakan turunan dari periode
                                                            sebelumnya yang melewati batas selisih harga terbaru. Anda wajib mengubah harga atau
                                                            mengisi Alasan.
                                                        </div>
                                                    @endif
                                                </td>
                                            @else
                                                <td class="p-1 text-center align-middle bg-light text-muted">
                                                    {{ is_numeric($valMin) ? number_format($valMin, 0, ',', '.') : '-' }}
                                                </td>
                                                <td class="p-1 text-center align-middle bg-light text-muted">
                                                    {{ is_numeric($valMax) ? number_format($valMax, 0, ',', '.') : '-' }}
                                                </td>
                                                <td class="p-1 text-center align-middle bg-light text-muted small fst-italic">
                                                    {{ $effData['alasan'] ?? '-' }}
                                                </td>
                                            @endif
                                        @endforeach
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </form>
        </div>
    </div>

    <style>
        .bg-blue-light {
            background-color: rgba(0, 147, 221, 0.05);
        }

        .bg-blue-faded {
            background-color: rgba(0, 147, 221, 0.03);
        }

        .text-blue {
            color: var(--bps-blue);
        }

        .bg-orange-light {
            background-color: rgba(255, 140, 0, 0.05);
        }

        .bg-orange-faded {
            background-color: rgba(255, 140, 0, 0.03);
        }

        .text-orange {
            color: var(--bps-orange);
        }

        .bg-danger-faded {
            background-color: rgba(220, 53, 69, 0.1);
        }

        .border-danger-custom {
            border: 1px solid #dc3545 !important;
        }

        .form-control:focus {
            background-color: #fff !important;
            box-shadow: none;
            outline: 1px solid var(--bps-blue);
        }

        .form-control-sm {
            height: 38px;
            font-size: 0.9rem;
        }

        table th {
            font-weight: 700;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
        }

        .table-bordered> :not(caption)>*>* {
            border-width: 1px;
            border-color: #f1f5f9;
        }

        .is-invalid-custom {
            border: 2px solid #dc3545 !important;
            background-color: #fff5f5 !important;
        }
    </style>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('form-save-nilai');
            let isDirty = false;

            // Format Ribuan Function
            function formatRibuan(input) {
                let value = input.value.replace(/[^0-9]/g, '');
                if (value) {
                    value = parseInt(value, 10).toLocaleString('id-ID');
                }
                input.value = value;
            }

            // Apply formatter listener and track dirtiness
            form.addEventListener('input', function (e) {
                isDirty = true;
                if (e.target.classList.contains('format-ribuan')) {
                    formatRibuan(e.target);
                    validateTrio(e.target);
                } else if (e.target.tagName === 'TEXTAREA') {
                    validateTrio(e.target);
                }
            });

            // Intercept filter dropdown form submissions
            const filterForm = document.getElementById('filter-form');
            if (filterForm) {
                filterForm.addEventListener('submit', function (e) {
                    if (isDirty) {
                        e.preventDefault();
                        Swal.fire({
                            title: 'Data Belum Disimpan!',
                            text: 'Ada perubahan data yang belum disimpan. Apakah Anda yakin ingin mengubah filter dan membuang perubahan?',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#f58220',
                            cancelButtonColor: '#64748b',
                            confirmButtonText: 'Ya, Ubah Filter',
                            cancelButtonText: 'Batal'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                isDirty = false;
                                filterForm.submit();
                            }
                        });
                    }
                });
            }

            // Intercept browser reload / tab close / back buttons (standard browser prompts)
            window.addEventListener('beforeunload', function (e) {
                if (isDirty) {
                    e.preventDefault();
                    e.returnValue = ''; // Trigger browser built-in warning popup
                }
            });

            // Intercept clicks on local menu/navigation links
            document.addEventListener('click', function (e) {
                const anchor = e.target.closest('a');
                if (anchor && isDirty) {
                    const href = anchor.getAttribute('href');
                    // Ignore blank targets, hash links, javascript actions, etc.
                    if (!href || href.startsWith('#') || href.startsWith('javascript:') || anchor.getAttribute('target') === '_blank') {
                        return;
                    }

                    e.preventDefault();
                    Swal.fire({
                        title: 'Data Belum Disimpan!',
                        text: 'Ada perubahan data yang belum disimpan. Apakah Anda yakin ingin meninggalkan halaman?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#f58220',
                        cancelButtonColor: '#64748b',
                        confirmButtonText: 'Ya, Tinggalkan',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            isDirty = false;
                            window.location.href = anchor.href;
                        }
                    });
                }
            });

            // Helper to get raw number value
            function getRawValue(input) {
                if (!input) return null;
                const val = input.value.replace(/\./g, ''); // Remove dots
                return val !== '' ? parseFloat(val) : null;
            }

            // Function to validate a trio of min/max/alasan inputs and toggle visibility
            function validateTrio(input) {
                const name = input.getAttribute('name');
                let minName, maxName, alasanName;

                if (name.includes('[min]')) {
                    minName = name;
                    maxName = name.replace('[min]', '[max]');
                    alasanName = name.replace('[min]', '[alasan]');
                } else if (name.includes('[max]')) {
                    maxName = name;
                    minName = name.replace('[max]', '[min]');
                    alasanName = name.replace('[max]', '[alasan]');
                } else if (name.includes('[alasan]')) {
                    alasanName = name;
                    minName = name.replace('[alasan]', '[min]');
                    maxName = name.replace('[alasan]', '[max]');
                } else {
                    return;
                }

                const minInput = form.querySelector(`input[name="${CSS.escape(minName)}"]`);
                const maxInput = form.querySelector(`input[name="${CSS.escape(maxName)}"]`);
                const alasanInput = form.querySelector(`input[name="${CSS.escape(alasanName)}"], textarea[name="${CSS.escape(alasanName)}"]`);

                if (!minInput || !maxInput || !alasanInput) return;

                const minVal = getRawValue(minInput);
                const maxVal = getRawValue(maxInput);
                const alasanVal = alasanInput.value.trim();

                let hasError = false;

                // Reset
                minInput.classList.remove('is-invalid-custom');
                maxInput.classList.remove('is-invalid-custom');
                alasanInput.classList.remove('is-invalid-custom');

                // Check completeness
                if (minVal !== null && maxVal === null) {
                    maxInput.classList.add('is-invalid-custom');
                    hasError = true;
                } else if (maxVal !== null && minVal === null) {
                    minInput.classList.add('is-invalid-custom');
                    hasError = true;
                }

                // Check Max <= Min
                if (minVal !== null && maxVal !== null && maxVal <= minVal) {
                    minInput.classList.add('is-invalid-custom');
                    maxInput.classList.add('is-invalid-custom');
                    hasError = true;
                }

                // Toggle visibility and check Batas Selisih
                // Get specific limit from data-batas attribute (fallback to 0)
                const specificBatas = parseFloat(alasanInput.getAttribute('data-batas')) || 0;

                if (minVal !== null && maxVal !== null && (maxVal - minVal) > specificBatas) {
                    alasanInput.classList.remove('d-none');
                    if (alasanVal === '') {
                        alasanInput.classList.add('is-invalid-custom');
                        hasError = true;
                    }
                } else {
                    // Jika ada tulisan di alasan, lalu nilai min max nya tidak melewai batas selisih, kosongkan isian alasan nya
                    if (alasanInput.value !== '') {
                        alasanInput.value = '';
                    }
                    alasanInput.classList.add('d-none');
                }

                return !hasError;
            }

            // Real-time validation on input - handled in 'input' listener above

            // Validation on form submission
            form.addEventListener('submit', function (e) {
                const allMinInputs = form.querySelectorAll('input[name*="[min]"]');
                const allMaxInputs = form.querySelectorAll('input[name*="[max]"]'); // Get max inputs too in case only max is filled
                let hasError = false;

                // Validate based on min inputs
                allMinInputs.forEach(input => {
                    if (!validateTrio(input)) {
                        hasError = true;
                    }
                });

                if (hasError) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Input Tidak Valid',
                        text: 'Terdapat kesalahan: Nilai Min/Max tidak lengkap, MAX <= MIN (tidak boleh sama), atau Alasan belum diisi.',
                        confirmButtonColor: '#0093dd'
                    });

                    // Scroll to first error
                    const firstError = form.querySelector('.is-invalid-custom');
                    if (firstError) {
                        firstError.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                        firstError.focus();
                    }
                } else {
                    isDirty = false;
                }
            });
        });
    </script>
@endpush