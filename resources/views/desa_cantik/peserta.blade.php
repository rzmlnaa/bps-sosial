@extends('layouts.admin')

@section('title', 'Peserta Desa Cantik')

@section('content')
    <div class="mt-3 fade-in-up">
        {{-- Header --}}
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
            <div>

                <h2 class="fw-bold mb-1" style="color: var(--bps-orange);">Peserta Desa Cantik</h2>
                <p class="text-muted mb-0">Daftarkan dan kelola desa peserta per periode</p>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-3" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show rounded-3" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row g-4">
            {{-- FORM TAMBAH PESERTA --}}
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 bps-card h-100">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="mb-0 fw-bold" style="color: var(--bps-orange);">
                            <i class="fas fa-plus-circle me-2"></i>Tambah Peserta Desa
                        </h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('desa-cantik.peserta.store') }}" method="POST" id="formTambahPeserta">
                            @csrf

                            {{-- Pilih Periode --}}
                            <div class="mb-3">
                                <label class="form-label fw-bold small">Periode <span class="text-danger">*</span></label>
                                <select class="form-select" name="periode_id" id="selectPeriode" required>
                                    <option value="">-- Pilih Periode --</option>
                                    @foreach($periodes as $p)
                                        <option value="{{ $p->id }}" data-tahun="{{ $p->tahun }}"
                                            data-active="{{ $p->is_active ? 1 : 0 }}">
                                            {{ $p->tahun }} {{ !$p->is_active ? '(Tidak Aktif)' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Pilih Kabupaten (HANYA untuk kode_kab = 6100) --}}
                            @if($isProvinsi)
                                <div class="mb-3" id="wrapKabupaten">
                                    <label class="form-label fw-bold small">Kabupaten <span class="text-danger">*</span></label>
                                    <select class="form-select" name="kabupaten_id" id="selectKabupaten">
                                        <option value="">-- Pilih Kabupaten --</option>
                                        @foreach($kabupatens as $kab)
                                            <option value="{{ $kab->id }}" data-kode="{{ $kab->kode_kab }}">
                                                {{ $kab->nama_kabupaten }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @else
                                {{-- Hidden field untuk user non-provinsi --}}
                                <input type="hidden" name="kabupaten_id" value="{{ $myKabupatenId }}">
                            @endif

                            {{-- Pilih Kecamatan (Select2 AJAX) --}}
                            <div class="mb-3">
                                <label class="form-label fw-bold small">Kecamatan <span class="text-danger">*</span></label>
                                <select name="kecamatan_id" id="selectKecamatan" class="form-select select2-kecamatan"
                                    required style="width:100%">
                                    <option value="">
                                        {{ $isProvinsi ? '-- Pilih Kabupaten dulu --' : '-- Ketik untuk cari kecamatan --' }}
                                    </option>
                                </select>
                            </div>

                            {{-- Pilih Desa (Select2 AJAX) --}}
                            <div class="mb-3">
                                <label class="form-label fw-bold small">Desa <span class="text-danger">*</span></label>
                                <select name="desa_id" id="selectDesa" class="form-select select2-desa" required
                                    style="width:100%">
                                    <option value="">-- Pilih Kecamatan dulu --</option>
                                </select>
                            </div>
 
                            {{-- Warning for inactive period --}}
                            <div id="inactivePeriodAlert" class="mb-3 d-none">
                                <div class="alert alert-warning py-2 rounded-3 mb-0 small">
                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                    Periode ini tidak aktif. Pendaftaran hanya bisa dilakukan pada periode aktif.
                                </div>
                            </div>

                            <button type="submit" class="btn btn-orange rounded-pill w-100 fw-bold" id="btnSubmit">
                                <i class="fas fa-plus me-1"></i> Daftarkan Desa
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- TABEL DAFTAR PESERTA --}}
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 bps-card">
                    <div class="card-header bg-white border-bottom py-3">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <h6 class="mb-0 fw-bold" style="color: var(--bps-orange);">
                                <i class="fas fa-list me-2"></i>Daftar Peserta Terdaftar
                            </h6>
                            {{-- Filter Periode --}}
                            <form method="GET" action="{{ route('desa-cantik.peserta') }}"
                                class="d-flex gap-2 align-items-center">
                                <select name="filter_periode" class="form-select form-select-sm" style="min-width:130px;"
                                    onchange="this.form.submit()">
                                    <option value="">Semua Periode</option>
                                    @foreach($periodes as $p)
                                        <option value="{{ $p->id }}" {{ request('filter_periode') == $p->id ? 'selected' : '' }}>
                                            {{ $p->tahun }}
                                        </option>
                                    @endforeach
                                </select>
                            </form>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        @if($pesertas->count() > 0)
                            <div class="table-responsive table-scrollable-lg">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="ps-3" width="5%">No</th>
                                            <th>Desa</th>
                                            <th>Kecamatan</th>
                                            @if($isProvinsi)
                                                <th>Kabupaten</th>
                                            @endif
                                            <th class="text-center">Periode</th>
                                            <th class="text-center">Didaftarkan Oleh</th>

                                            <th class="text-end pe-3" width="10%">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($pesertas as $index => $peserta)
                                            <tr>
                                                <td class="ps-3">
                                                    {{ ($pesertas->currentPage() - 1) * $pesertas->perPage() + $index + 1 }}
                                                </td>
                                                <td class="fw-bold">{{ $peserta->desa->nama_desa ?? '-' }}</td>
                                                <td>{{ $peserta->kecamatan->nama_kecamatan ?? '-' }}</td>
                                                @if($isProvinsi)
                                                    <td>
                                                        <span class="badge rounded-pill"
                                                            style="background-color: #fff3e0; color: var(--bps-orange); border: 1px solid var(--bps-orange);">
                                                            {{ $peserta->kabupaten->nama_kabupaten ?? '-' }}
                                                        </span>
                                                    </td>
                                                @endif
                                                <td class="text-center">
                                                    <span
                                                        class="badge bg-primary rounded-pill">{{ $peserta->periode->tahun ?? '-' }}</span>
                                                </td>
                                                <td class="text-center">
                                                    <span
                                                        class="badge bg-secondary rounded-pill">{{ $peserta->creator->name ?? '-' }}</span>
                                                </td>
                                                <td class="text-end pe-3">
                                                    @if($peserta->periode->is_active)
                                                        <button type="button" class="btn btn-sm btn-outline-danger btn-delete"
                                                            data-url="{{ route('desa-cantik.peserta.destroy', $peserta->id) }}"
                                                            data-type="Peserta Desa" data-name="{{ $peserta->desa->nama_desa ?? '' }}">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    @else
                                                        <button type="button" class="btn btn-sm btn-outline-secondary" disabled
                                                            title="Periode tidak aktif">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="p-3">
                                {{ $pesertas->appends(request()->query())->links('pagination::bootstrap-5') }}
                            </div>
                        @else
                            <div class="text-center text-muted py-5">
                                <i class="fas fa-inbox fa-3x mb-3 opacity-25"></i>
                                <p class="mb-0">Belum ada desa yang terdaftar sebagai peserta.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Global delete form --}}
    <form id="globalDeleteForm" method="POST" action="" style="display:none;">
        @csrf
        @method('DELETE')
    </form>

    @push('styles')
        <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
        <style>
            .btn-orange {
                background-color: var(--bps-orange);
                border-color: var(--bps-orange);
                color: #fff;
            }

            .btn-orange:hover,
            .btn-orange:focus {
                background-color: #e6661a;
                border-color: #e6661a;
                color: #fff;
            }

            .btn-orange:disabled {
                background-color: #f8b97c;
                border-color: #f8b97c;
                color: #fff;
            }

            .table-scrollable-lg {
                max-height: 520px;
                overflow-y: auto;
            }

            .table-scrollable-lg thead th {
                position: sticky;
                top: 0;
                z-index: 10;
                background-color: #f8fafc;
            }

            .tiny {
                font-size: 0.7rem;
            }

            /* Select2 Styling */
            .select2-container--default .select2-selection--single {
                height: 38px;
                border: 1px solid #dee2e6;
                border-radius: 0.375rem;
                padding: 4px 8px;
                font-size: 0.9rem;
            }

            .select2-container--default .select2-selection--single .select2-selection__arrow {
                height: 36px;
            }

            .select2-container--default .select2-selection--single .select2-selection__rendered {
                line-height: 28px;
                color: #212529;
            }

            .select2-container--default .select2-results__option--disabled {
                color: #aaa;
                font-style: italic;
                background: #f8f9fa;
            }

            .select2-dropdown {
                border-radius: 0.375rem;
                border-color: #dee2e6;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            }

            .select2-container--open .select2-selection--single {
                border-color: #f7921e !important;
                box-shadow: 0 0 0 0.2rem rgba(247, 146, 30, 0.2);
            }

            .select2-results__option--highlighted {
                background-color: var(--bps-orange) !important;
            }
        </style>
    @endpush

    @push('scripts')
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.all.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {

                const isProvinsi = {{ $isProvinsi ? 'true' : 'false' }};
                const myKabupatenId = {{ $myKabupatenId ?? 'null' }};

                const urlKecamatan = '/desa-cantik/ajax/kecamatan';
                const urlDesa = '/desa-cantik/ajax/desa';


                const selectPeriode = document.getElementById('selectPeriode');
                const selectKabupaten = document.getElementById('selectKabupaten');
                const inactiveAlert = document.getElementById('inactivePeriodAlert');

                function getPeriodeId() { return $(selectPeriode).val() || ''; }
                function getKabupatenId() { return $(selectKabupaten).val() || myKabupatenId; }

                // ========================
                // SELECT2: KECAMATAN
                // ========================
                // ========================
                // SELECT2: KECAMATAN
                // ========================
                function initKecamatan() {
                    const $sel = $('#selectKecamatan');

                    const kabId = getKabupatenId();
                    const shouldDisable = isProvinsi && !kabId;

                    if ($sel.data('select2')) $sel.select2('destroy');

                    // Clear existing options to ensure placeholder updates correctly
                    $sel.empty().append('<option value=""></option>');

                    $sel.select2({
                        placeholder: shouldDisable ? '-- Pilih Kabupaten dulu --' : '-- Ketik atau pilih kecamatan --',
                        allowClear: true,
                        minimumInputLength: 0,
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
                            cache: true,
                        }
                    }).prop('disabled', shouldDisable);

                    // Re-bind change event using namespaced event to avoid duplication
                    $sel.off('change.descan').on('change.descan', function () {
                        resetDesa();
                        const kecId = $(this).val();
                        if (kecId && getPeriodeId()) {
                            initDesa();
                        }
                    });
                }

                // ========================
                // SELECT2: DESA
                // ========================
                function initDesa() {
                    const $sel = $('#selectDesa');
                    const kecId = $('#selectKecamatan').val();
                    const perId = getPeriodeId();

                    if ($sel.data('select2')) $sel.select2('destroy');

                    if (!kecId || !perId) {
                        $sel.prop('disabled', true).html('<option value="">-- Pilih Kecamatan dulu --</option>');
                        return;
                    }

                    $sel.select2({
                        placeholder: '-- Ketik atau pilih desa --',
                        allowClear: true,
                        minimumInputLength: 0,
                        ajax: {
                            url: urlDesa,
                            dataType: 'json',
                            delay: 250,
                            data: params => ({
                                search: params.term || '',
                                limit: 10,
                                kecamatan_id: kecId,
                                periode_id: perId,
                            }),
                            processResults: data => ({
                                results: data.map(d => ({
                                    id: d.id,
                                    text: d.text,
                                    disabled: d.disabled,
                                    previous_tahun: d.previous_tahun
                                }))
                            }),
                            cache: true,
                        }
                    }).prop('disabled', false);

                    $sel.off('select2:select.descan').on('select2:select.descan', function (e) {
                        var data = e.params.data;
                        if (data.previous_tahun) {
                            Swal.fire({
                                title: 'Perhatian!',
                                html: `Desa <strong>${data.text}</strong> sudah pernah menjadi peserta Desa Cantik pada periode <strong>${data.previous_tahun}</strong>.<br><br>Apakah Anda yakin ingin mendaftarkannya kembali di periode ini?`,
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonColor: '#f7921e',
                                cancelButtonColor: '#6c757d',
                                confirmButtonText: 'Ya, Lanjutkan',
                                cancelButtonText: 'Batal'
                            }).then((result) => {
                                if (!result.isConfirmed) {
                                    $sel.val(null).trigger('change');
                                }
                            });
                        }
                    });
                }

                function resetDesa() {
                    const $sel = $('#selectDesa');
                    if ($sel.data('select2')) $sel.select2('destroy');
                    $sel.val(null).html('<option value="">-- Pilih Kecamatan dulu --</option>').prop('disabled', true);
                }

                // Init awal
                initKecamatan();



                // ========================
                // EVENT: PERIODE
                // ========================
                $(selectPeriode).on('change select2:select', function () {
                    const val = $(this).val();
                    const isActive = $(this).find(':selected').data('active') == 1;

                    $('#selectKecamatan').val(null).trigger('change');
                    resetDesa();
                    initKecamatan();

                    // Lock form if period is inactive
                    const btn = document.getElementById('btnSubmit');
                    if (val && !isActive) {
                        inactiveAlert.classList.remove('d-none');
                        btn.disabled = true;
                    } else {
                        inactiveAlert.classList.add('d-none');
                        btn.disabled = false;
                    }
                });

                // ========================
                // EVENT: KABUPATEN (provinsi saja)
                // ========================
                if (isProvinsi && selectKabupaten) {
                    $(selectKabupaten).on('change select2:select', function () {
                        $('#selectKecamatan').val(null).trigger('change');
                        resetDesa();
                        initKecamatan();
                    });
                }



                // ========================
                // SWEET ALERT DELETE
                // ========================
                document.querySelectorAll('.btn-delete').forEach(button => {
                    button.addEventListener('click', function () {
                        const url = this.getAttribute('data-url');
                        const type = this.getAttribute('data-type');
                        const name = this.getAttribute('data-name');
                        Swal.fire({
                            title: `Hapus ${type}?`,
                            html: `Desa <strong>${name}</strong> akan dihapus dari daftar peserta.<br><small class="text-muted">Data progress terkait mungkin ikut terdampak.</small>`,
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#dc3545',
                            cancelButtonColor: '#6c757d',
                            confirmButtonText: 'Ya, Hapus!',
                            cancelButtonText: 'Batal'
                        }).then(result => {
                            if (result.isConfirmed) {
                                const form = document.getElementById('globalDeleteForm');
                                form.action = url;
                                form.submit();
                            }
                        });
                    });
                });
            });
        </script>
    @endpush
@endsection