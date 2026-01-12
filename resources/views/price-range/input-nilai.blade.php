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
                    style="border-radius: 12px; background: linear-gradient(135deg, var(--bps-blue), #007bbd);">
                    <div class="card-body p-4 text-white d-flex flex-column justify-content-center">
                        <div class="d-flex align-items-center mb-2">
                            <div class="bg-white bg-opacity-25 rounded-circle p-2 me-3">
                                <i class="fas fa-info-circle fa-lg"></i>
                            </div>
                            <h6 class="mb-0 fw-bold">Konfigurasi Batas Selisih</h6>
                        </div>
                        <p class="small mb-0 opacity-75">Batas selisih harga kini diatur per komoditas. Lihat kolom <b>Batas
                                Selisih</b> untuk detail setiap komoditas.</p>
                    </div>
                </div>
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
                                <th rowspan="2" style="width: 100px;">BATAS SELISIH</th>
                                <th colspan="3" class="bg-blue-light text-blue">MASTER NILAI
                                    ({{ substr($activeYear->tahun, -2) }})</th>

                                @foreach($displayRevisions as $rev)
                                    <th colspan="3" class="bg-orange-light text-orange">{{ strtoupper($rev->label) }}</th>
                                @endforeach
                            </tr>
                            <tr>
                                <th style="width: 120px;" class="bg-blue-light text-blue small">
                                    MIN_{{ substr($activeYear->tahun, -2) }}</th>
                                <th style="width: 120px;" class="bg-blue-light text-blue small">
                                    MAX_{{ substr($activeYear->tahun, -2) }}</th>
                                <th style="width: 200px;" class="bg-blue-light text-blue small">ALASAN</th>

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
                                        $colspan = 6 + ($displayRevisions->count() * 3);
                                    @endphp
                                    <td colspan="{{ $colspan }}" class="ps-4 fw-bold text-muted small text-uppercase py-2">
                                        <i class="fas fa-folder-open me-1"></i> {{ $category->nama_kategori }}
                                    </td>
                                </tr>
                                @foreach($category->komoditas as $komo)
                                    @php
                                        $master = $masterNilai->get($komo->id);
                                        // Master is editable ONLY if there are NO revisions
                                        $isMasterEditable = empty($latestRevisionId);
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

                                        <!-- Master Inputs -->
                                        @if($isMasterEditable)
                                            <td class="p-1">
                                                <input type="number" name="master[{{ $komo->id }}][min]"
                                                    class="form-control form-control-sm border-0 bg-blue-faded text-center"
                                                    placeholder="Min" value="{{ $master->min_nilai ?? '' }}" step="0.01"
                                                    data-batas="{{ $komo->batas_selisih_harga ?? 0 }}">
                                            </td>
                                            <td class="p-1">
                                                <input type="number" name="master[{{ $komo->id }}][max]"
                                                    class="form-control form-control-sm border-0 bg-blue-faded text-center"
                                                    placeholder="Max" value="{{ $master->max_nilai ?? '' }}" step="0.01"
                                                    data-batas="{{ $komo->batas_selisih_harga ?? 0 }}">
                                            </td>
                                            <td class="p-1">
                                                <textarea name="master[{{ $komo->id }}][alasan]" rows="1"
                                                    class="form-control form-control-sm border-0 bg-blue-faded {{ ($master->max_nilai ?? 0) - ($master->min_nilai ?? 0) > ($komo->batas_selisih_harga ?? 0) ? '' : 'd-none' }}"
                                                    placeholder="Berikan alasan"
                                                    data-batas="{{ $komo->batas_selisih_harga ?? 0 }}">{{ $master->alasan ?? '' }}</textarea>
                                            </td>
                                        @else
                                            <td class="p-1 text-center align-middle bg-light text-muted">
                                                {{ $master->min_nilai ?? '-' }}
                                            </td>
                                            <td class="p-1 text-center align-middle bg-light text-muted">
                                                {{ $master->max_nilai ?? '-' }}
                                            </td>
                                            <td class="p-1 text-center align-middle bg-light text-muted small fst-italic">
                                                {{ $master->alasan ?? '-' }}
                                            </td>
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
                                                    // Note: You might want to fetch reason if needed, but for "Price Range" view style, usually just numbers.
                                                    // Let's stick to numbers for Read-Only as per request "view readonly but values same as Price Range".
                                                }
                                            @endphp
                                            
                                            @if($isRevEditable)
                                                <td class="p-1">
                                                    <input type="number" name="revision[{{ $rev->id }}][{{ $komo->id }}][min]"
                                                        class="form-control form-control-sm border-0 bg-orange-faded text-center"
                                                        placeholder="Edit Min" value="{{ $valMin }}" step="0.01"
                                                        data-batas="{{ $komo->batas_selisih_harga ?? 0 }}">
                                                </td>
                                                <td class="p-1">
                                                    <input type="number" name="revision[{{ $rev->id }}][{{ $komo->id }}][max]"
                                                        class="form-control form-control-sm border-0 bg-orange-faded text-center"
                                                        placeholder="Edit Max" value="{{ $valMax }}" step="0.01"
                                                        data-batas="{{ $komo->batas_selisih_harga ?? 0 }}">
                                                </td>
                                                <td class="p-1">
                                                    <textarea name="revision[{{ $rev->id }}][{{ $komo->id }}][alasan]" rows="1"
                                                        class="form-control form-control-sm border-0 bg-orange-faded {{ ((float)$valMax - (float)$valMin) > ($komo->batas_selisih_harga ?? 0) && $valMax !== '' && $valMin !== '' ? '' : 'd-none' }}"
                                                        placeholder="Berikan alasan"
                                                        data-batas="{{ $komo->batas_selisih_harga ?? 0 }}">{{ $valAlasan }}</textarea>
                                                </td>
                                            @else
                                                <td class="p-1 text-center align-middle bg-light text-muted">
                                                    {{ $valMin }}
                                                </td>
                                                <td class="p-1 text-center align-middle bg-light text-muted">
                                                    {{ $valMax }}
                                                </td>
                                                <td class="p-1 text-center align-middle bg-light">
                                                    <!-- Placeholder for reason in read-only view, currently empty/dash -->
                                                    -
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
            // Batas selisih is handled per-input via data-batas attribute now

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

                const minVal = minInput.value !== '' ? parseFloat(minInput.value) : null;
                const maxVal = maxInput.value !== '' ? parseFloat(maxInput.value) : null;
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

                // Check Max < Min
                if (minVal !== null && maxVal !== null && maxVal < minVal) {
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
                    alasanInput.classList.add('d-none');
                }

                return !hasError;
            }

            // Real-time validation on input
            form.addEventListener('input', function (e) {
                if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') {
                    validateTrio(e.target);
                }
            });

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
                        text: 'Terdapat kesalahan: Nilai Min/Max tidak lengkap, MAX < MIN, atau Alasan belum diisi.',
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
                }
            });
        });
    </script>
@endpush