@extends('layouts.admin')

@section('title', 'Input Nilai RH Kabupaten - BPS Kalbar')

@section('content')
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
                <button type="submit" form="form-save-nilai" class="btn text-white fw-bold shadow-sm"
                    style="background-color: var(--bps-blue);">
                    <i class="fas fa-save me-1"></i> Simpan Data
                </button>
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

        <!-- Filters -->
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
            <div class="card-body p-4">
                <form action="{{ route('rh-nilai.index') }}" method="GET" class="row g-3 align-items-end" id="filter-form">
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
                        <label class="form-label small fw-bold text-muted text-uppercase">Pilih Kabupaten</label>
                        <select name="kabupaten_id" class="form-select border-0 bg-light shadow-none"
                            onchange="this.form.submit()">
                            @foreach($kabupatens as $kab)
                                <option value="{{ $kab->id }}" {{ $selectedKabupatenId == $kab->id ? 'selected' : '' }}>
                                    {{ $kab->nama_kabupaten }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-muted text-uppercase">Pilih Header Perubahan
                            (Optional)</label>
                        <select name="revision_id" class="form-select border-0 bg-light shadow-none"
                            onchange="this.form.submit()">
                            <option value="">-- Master Nilai (Input Utama) --</option>
                            @if($revisions->count() > 0)
                                <option value="all" {{ $selectedRevisionId == 'all' ? 'selected' : '' }}>-- Semua Perubahan --
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

        <!-- Data Table -->
        <div class="card border-0 shadow-sm overflow-hidden" style="border-radius: 12px;">
            <form action="{{ route('rh-nilai.save') }}" method="POST" id="form-save-nilai">
                @csrf
                <input type="hidden" name="rh_tahun_id" value="{{ $activeYear->id }}">
                <input type="hidden" name="kabupaten_id" value="{{ $selectedKabupatenId }}">
                <input type="hidden" name="revision_id" value="{{ $selectedRevisionId }}">

                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0">
                        <thead class="bg-light text-center align-middle">
                            <tr>
                                <th rowspan="2" class="ps-4" style="min-width: 250px;">NAMA</th>
                                <th rowspan="2" style="width: 100px;">SATUAN</th>
                                <th colspan="2" class="bg-blue-light text-blue">MASTER NILAI
                                    ({{ substr($activeYear->tahun, -2) }})</th>

                                @if($selectedRevisionId === 'all')
                                    @foreach($revisions as $rev)
                                        <th colspan="2" class="bg-orange-light text-orange">{{ strtoupper($rev->label) }}</th>
                                    @endforeach
                                @elseif($selectedRevisionId)
                                    @php
                                        $revHeader = $revisions->find($selectedRevisionId);
                                    @endphp
                                    <th colspan="2" class="bg-orange-light text-orange">{{ strtoupper($revHeader->label) }}</th>
                                @endif
                            </tr>
                            <tr>
                                <th style="width: 150px;" class="bg-blue-light text-blue small">
                                    MIN_{{ substr($activeYear->tahun, -2) }}</th>
                                <th style="width: 150px;" class="bg-blue-light text-blue small">
                                    MAX_{{ substr($activeYear->tahun, -2) }}</th>

                                @if($selectedRevisionId === 'all')
                                    @foreach($revisions as $rev)
                                        <th style="width: 150px;" class="bg-orange-light text-orange small">MIN_EDIT</th>
                                        <th style="width: 150px;" class="bg-orange-light text-orange small">MAX_EDIT</th>
                                    @endforeach
                                @elseif($selectedRevisionId)
                                    <th style="width: 150px;" class="bg-orange-light text-orange small">MIN_EDIT</th>
                                    <th style="width: 150px;" class="bg-orange-light text-orange small">MAX_EDIT</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($categories as $category)
                                <tr class="bg-light">
                                    @php
                                        $colspan = 4;
                                        if ($selectedRevisionId === 'all') {
                                            $colspan = 4 + ($revisions->count() * 2);
                                        } elseif ($selectedRevisionId) {
                                            $colspan = 6;
                                        }
                                    @endphp
                                    <td colspan="{{ $colspan }}" class="ps-4 fw-bold text-muted small text-uppercase py-2">
                                        <i class="fas fa-folder-open me-1"></i> {{ $category->nama_kategori }}
                                    </td>
                                </tr>
                                @foreach($category->komoditas as $komo)
                                    @php
                                        $master = $masterNilai->get($komo->id);
                                    @endphp
                                    <tr>
                                        <td class="ps-4">{{ $komo->nama_komoditas }}</td>
                                        <td class="text-center"><span
                                                class="badge bg-light text-dark border fw-normal">{{ $komo->satuan ?? 'Kg' }}</span>
                                        </td>

                                        <!-- Master Inputs -->
                                        <td class="p-1">
                                            <input type="number" name="master[{{ $komo->id }}][min]"
                                                class="form-control form-control-sm border-0 bg-blue-faded text-center"
                                                placeholder="Min" value="{{ $master->min_nilai ?? '' }}" step="0.01">
                                        </td>
                                        <td class="p-1">
                                            <input type="number" name="master[{{ $komo->id }}][max]"
                                                class="form-control form-control-sm border-0 bg-blue-faded text-center"
                                                placeholder="Max" value="{{ $master->max_nilai ?? '' }}" step="0.01">
                                        </td>

                                        <!-- Revision Inputs -->
                                        @if($selectedRevisionId === 'all')
                                            @foreach($revisions as $rev)
                                                @php
                                                    $revData = $allRevisionNilai->get($rev->id)?->get($komo->id);
                                                @endphp
                                                <td class="p-1">
                                                    <input type="number" name="revision[{{ $rev->id }}][{{ $komo->id }}][min]"
                                                        class="form-control form-control-sm border-0 bg-orange-faded text-center"
                                                        placeholder="Edit Min" value="{{ $revData->min_edit ?? '' }}" step="0.01">
                                                </td>
                                                <td class="p-1">
                                                    <input type="number" name="revision[{{ $rev->id }}][{{ $komo->id }}][max]"
                                                        class="form-control form-control-sm border-0 bg-orange-faded text-center"
                                                        placeholder="Edit Max" value="{{ $revData->max_edit ?? '' }}" step="0.01">
                                                </td>
                                            @endforeach
                                        @elseif($selectedRevisionId)
                                            @php
                                                $revData = $revisionNilai->get($komo->id);
                                            @endphp
                                            <td class="p-1">
                                                <input type="number" name="revision[{{ $komo->id }}][min]"
                                                    class="form-control form-control-sm border-0 bg-orange-faded text-center"
                                                    placeholder="Edit Min" value="{{ $revData->min_edit ?? '' }}" step="0.01">
                                            </td>
                                            <td class="p-1">
                                                <input type="number" name="revision[{{ $komo->id }}][max]"
                                                    class="form-control form-control-sm border-0 bg-orange-faded text-center"
                                                    placeholder="Edit Max" value="{{ $revData->max_edit ?? '' }}" step="0.01">
                                            </td>
                                        @endif
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
    </style>
@endsection