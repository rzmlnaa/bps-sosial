@extends('layouts.admin')

@section('title', 'Input Data Fenomena')

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        /* ========================
                                 * PAGE VARIABLES
                                 * ======================== */
        :root {
            --form-primary: #f58220;
            --form-primary-dark: #0078b8;
            --form-primary-soft: #e8f5fc;
            --form-primary-border: #b3ddf2;
            --form-success: #198754;
            --form-card-radius: 14px;
            --form-input-radius: 8px;
            --form-card-shadow: 0 1px 4px rgba(0, 0, 0, 0.06), 0 2px 12px rgba(0, 0, 0, 0.04);
            --form-border: #e2e8f0;
            --form-bg: #f1f5f9;
            --form-section-header-bg: #f8fafc;
        }

        /* ========================
                                 * PAGE HEADER
                                 * ======================== */
        .page-header-input {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 1.75rem;
        }

        .page-header-input .page-title-block h2 {
            font-size: 1.6rem;
            font-weight: 700;
            color: var(--form-primary);
            margin-bottom: 2px;
            line-height: 1.2;
        }

        .page-header-input .page-title-block .subtitle {
            font-size: 0.875rem;
            color: #64748b;
            font-weight: 400;
        }

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 18px;
            border: 1.5px solid #cbd5e1;
            border-radius: 9px;
            color: #475569;
            font-weight: 500;
            font-size: 0.875rem;
            background: #fff;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .btn-back:hover {
            background: var(--form-primary-soft);
            border-color: var(--form-primary-border);
            color: var(--form-primary);
        }

        /* ========================
                                 * FORM CARDS
                                 * ======================== */
        .form-card {
            background: #fff;
            border-radius: var(--form-card-radius);
            box-shadow: var(--form-card-shadow);
            border: 1px solid var(--form-border);
            margin-bottom: 1.5rem;
            overflow: hidden;
        }

        .form-card-header {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 1.1rem 1.5rem;
            background: var(--form-section-header-bg);
            border-bottom: 1px solid var(--form-border);
        }

        .form-card-header .card-icon-wrap {
            width: 36px;
            height: 36px;
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--form-primary-soft);
            color: var(--form-primary);
            font-size: 0.95rem;
            flex-shrink: 0;
        }

        .form-card-header .card-title {
            font-size: 0.925rem;
            font-weight: 600;
            color: #1e293b;
            margin: 0;
        }

        .form-card-header .card-subtitle {
            font-size: 0.78rem;
            color: #94a3b8;
            margin: 2px 0 0;
        }

        .form-card-body {
            padding: 1.5rem;
        }

        /* ========================
                                 * AUTO FETCH SECTION
                                 * ======================== */
        .auto-fetch-section {
            background: linear-gradient(135deg, #eaf6ff 0%, #f0faff 100%);
            border: 1.5px dashed var(--form-primary-border);
            border-radius: 11px;
            padding: 1.25rem 1.4rem;
            margin-bottom: 1.5rem;
        }

        .auto-fetch-label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--form-primary-dark);
            margin-bottom: 0.65rem;
        }

        .auto-fetch-label .badge-feature {
            background: var(--form-primary);
            color: #fff;
            font-size: 0.68rem;
            padding: 2px 8px;
            border-radius: 20px;
            font-weight: 600;
            letter-spacing: 0.03em;
        }

        .auto-fetch-section textarea {
            border: 1.5px solid #c5e3f5;
            border-radius: 9px;
            background: rgba(255, 255, 255, 0.85);
            resize: vertical;
            font-size: 0.875rem;
            line-height: 1.6;
        }

        .auto-fetch-section textarea:focus {
            border-color: var(--form-primary);
            box-shadow: 0 0 0 3px rgba(0, 147, 221, 0.12);
        }

        .btn-fetch {
            background: var(--form-primary);
            color: #fff;
            border: none;
            border-radius: 9px;
            padding: 9px 20px;
            font-size: 0.875rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
            box-shadow: 0 2px 8px rgba(0, 147, 221, 0.25);
        }

        .btn-fetch:hover {
            background: var(--form-primary-dark);
            transform: translateY(-1px);
            box-shadow: 0 4px 14px rgba(0, 147, 221, 0.35);
            color: #fff;
        }

        .btn-fetch:active {
            transform: scale(0.98);
        }

        .auto-fetch-hint {
            font-size: 0.78rem;
            color: #64748b;
            margin-top: 0.5rem;
        }

        /* ========================
                                 * FORM CONTROLS
                                 * ======================== */
        .form-label-custom {
            font-size: 0.85rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.45rem;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .required-star {
            color: #ef4444;
            font-size: 0.85rem;
        }

        .form-control-custom,
        .form-select-custom {
            border: 1.5px solid #d1d5db;
            border-radius: var(--form-input-radius);
            padding: 0.5rem 0.875rem;
            font-size: 0.875rem;
            color: #1e293b;
            background: #fff;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
            width: 100%;
            height: auto;
            min-height: 40px;
        }

        .form-control-custom:focus,
        .form-select-custom:focus {
            border-color: var(--form-primary);
            box-shadow: 0 0 0 3px rgba(0, 147, 221, 0.12);
            outline: none;
        }

        .form-control-custom.is-invalid {
            border-color: #ef4444;
        }

        .form-control-custom.is-valid {
            border-color: #22c55e;
        }

        .form-hint {
            font-size: 0.76rem;
            color: #94a3b8;
            margin-top: 0.3rem;
        }

        /* ========================
                                 * DATE ROW COMPACT
                                 * ======================== */
        .date-compact-row {
            display: flex;
            gap: 12px;
            align-items: flex-end;
        }

        .date-compact-row .date-field {
            flex: 1;
        }

        .date-compact-row .date-field-wide {
            flex: 2;
        }

        /* ========================
                                 * TEXTAREA PENJELASAN
                                 * ======================== */
        .textarea-penjelasan {
            min-height: 150px;
            line-height: 1.8;
            resize: vertical;
        }

        /* ========================
                                 * SELECT2 OVERRIDES
                                 * ======================== */
        .select2-container--default .select2-selection--single {
            border: 1.5px solid #d1d5db !important;
            border-radius: var(--form-input-radius) !important;
            height: 40px !important;
            display: flex;
            align-items: center;
            background: #fff !important;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .select2-container--default .select2-selection--single:focus-within,
        .select2-container--default.select2-container--open .select2-selection--single {
            border-color: var(--form-primary) !important;
            box-shadow: 0 0 0 3px rgba(0, 147, 221, 0.12) !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 38px !important;
            padding-left: 12px !important;
            font-size: 0.875rem;
            color: #1e293b;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 38px !important;
        }

        .select2-container--default .select2-selection--multiple {
            border: 1.5px solid #d1d5db !important;
            border-radius: var(--form-input-radius) !important;
            min-height: 40px !important;
            background: #fff !important;
            padding: 4px 6px !important;
        }

        .select2-container--default.select2-container--open .select2-selection--multiple,
        .select2-container--default .select2-selection--multiple:focus-within {
            border-color: var(--form-primary) !important;
            box-shadow: 0 0 0 3px rgba(0, 147, 221, 0.12) !important;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background: var(--form-primary) !important;
            border: none !important;
            color: #fff !important;
            border-radius: 6px !important;
            padding: 2px 10px !important;
            font-size: 0.78rem !important;
            font-weight: 500 !important;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            color: rgba(255, 255, 255, 0.75) !important;
            margin-right: 5px !important;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
            color: #fff !important;
        }

        .select2-dropdown {
            border: 1.5px solid var(--form-primary-border) !important;
            border-radius: 10px !important;
            overflow: hidden;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1) !important;
        }

        .select2-results__option--highlighted {
            background-color: var(--form-primary) !important;
        }

        /* ========================
                             * ACTION BAR
                             * ======================== */
        .sticky-action-bar {
            margin-top: 0.5rem;
            padding: 1rem 0 0.5rem;
            border-top: 1.5px solid #e2e8f0;
        }

        .sticky-action-bar .action-inner {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 12px;
        }

        .btn-batal {
            padding: 9px 22px;
            border: 1.5px solid #cbd5e1;
            border-radius: 9px;
            background: #fff;
            color: #475569;
            font-weight: 600;
            font-size: 0.875rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            transition: all 0.2s ease;
        }

        .btn-batal:hover {
            background: #f1f5f9;
            border-color: #94a3b8;
            color: #334155;
        }

        .btn-simpan {
            padding: 9px 24px;
            border-radius: 9px;
            background: var(--form-primary);
            color: #fff;
            font-weight: 600;
            font-size: 0.875rem;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
            box-shadow: 0 2px 10px rgba(0, 147, 221, 0.28);
            cursor: pointer;
        }

        .btn-simpan:hover {
            background: var(--form-primary-dark);
            transform: translateY(-1px);
            box-shadow: 0 4px 16px rgba(0, 147, 221, 0.38);
            color: #fff;
        }

        .btn-simpan:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        /* ========================
                                 * VALIDATION FEEDBACK
                                 * ======================== */
        .field-invalid-msg {
            font-size: 0.76rem;
            color: #ef4444;
            margin-top: 4px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        /* ========================
                                 * RESPONSIVE
                                 * ======================== */
        @media (max-width: 767px) {
            .date-compact-row {
                flex-direction: column;
                gap: 10px;
            }

            .page-header-input .page-title-block h2 {
                font-size: 1.3rem;
            }

            .form-card-body {
                padding: 1.1rem 1rem;
            }
        }
    </style>
@endpush

@section('content')

    {{-- ======================== PAGE HEADER ======================== --}}
    <div class="page-header-input fade-in-up">
        <div class="page-title-block">
            <h2>
                <i class="fas fa-file-alt me-2" style="font-size:1.3rem; opacity:0.85;"></i>
                Input Data Fenomena
            </h2>
            <div class="subtitle">
                <i class="fas fa-circle" style="font-size:5px; vertical-align:middle; color:#0093dd;"></i>
                Fenomena Sosial Ekonomi &amp; Kejadian Penting
            </div>
        </div>
        <a href="{{ route('fenomena.index') }}" class="btn-back">
            <i class="fas fa-arrow-left"></i>
            Kembali
        </a>
    </div>

    <form action="{{ route('fenomena.store') }}" method="POST" id="form-fenomena">
        @csrf

        {{-- ======================== CARD 1: SUMBER BERITA ======================== --}}
        <div class="form-card fade-in-up delay-100">
            <div class="form-card-header">
                <div class="card-icon-wrap">
                    <i class="fas fa-newspaper"></i>
                </div>
                <div>
                    <div class="card-title">Sumber Berita</div>
                    <div class="card-subtitle">Tempelkan berita atau masukkan link sumber data</div>
                </div>
            </div>
            <div class="form-card-body">

                {{-- Auto Fetch --}}
                <div class="auto-fetch-section">
                    <div class="auto-fetch-label">
                        <i class="fas fa-magic"></i>
                        Ambil Otomatis dari Clipboard
                        <span class="badge-feature">FITUR MUDAH</span>
                    </div>
                    <textarea id="textarea-clipboard" class="form-control form-control-custom" rows="3"
                        placeholder="Copy seluruh isi berita mulai dari Judul hingga Paragraf yang Anda rasa cukup, lalu klik 'Ambil Otomatis'..."></textarea>
                    <div class="d-flex align-items-center justify-content-between mt-2 flex-wrap gap-2">
                        <div class="auto-fetch-hint">
                            <i class="fas fa-info-circle me-1"></i>
                            Copy isi berita lengkap, tempel di sini atau langsung dari clipboard, lalu klik tombol.
                        </div>
                        <button class="btn-fetch" type="button" onclick="ambilBerita()" id="btn-fetch">
                            <i class="fas fa-magic"></i>
                            Ambil Otomatis
                        </button>
                    </div>
                </div>

                {{-- Link & Sumber --}}
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="link" class="form-label-custom">
                            Link Berita
                            <span id="link-required-indicator" class="text-muted"
                                style="font-size:0.78rem; font-weight:400;">(Opsional)</span>
                        </label>
                        <input type="url" class="form-control form-control-custom" id="link" name="link_berita"
                            placeholder="https://contoh.com/artikel/...">
                        <div class="form-hint">URL sumber berita online</div>
                    </div>
                    <div class="col-md-6">
                        <label for="sumber_berita_id" class="form-label-custom">
                            Sumber Berita <span class="required-star">*</span>
                        </label>
                        <select class="form-select-custom select2" id="sumber_berita_id" name="sumber_berita_id" required>
                            <option value="">Pilih Sumber Berita</option>
                            @foreach($sumberBeritas as $sumber)
                                <option value="{{ $sumber->id }}" data-online="{{ $sumber->is_online ? '1' : '0' }}">
                                    {{ $sumber->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

            </div>
        </div>

        {{-- ======================== CARD 2: INFORMASI WAKTU ======================== --}}
        <div class="form-card fade-in-up delay-200">
            <div class="form-card-header">
                <div class="card-icon-wrap">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div>
                    <div class="card-title">Informasi Waktu</div>
                    <div class="card-subtitle">Tanggal terbit atau kejadian fenomena</div>
                </div>
            </div>
            <div class="form-card-body">
                <label class="form-label-custom">
                    Tanggal Berita / Fenomena <span class="required-star">*</span>
                </label>
                <div class="date-compact-row">
                    <div class="date-field">
                        <input type="number" class="form-control form-control-custom text-center" id="tanggal"
                            name="tanggal" min="1" max="31" placeholder="DD" required>
                        <div class="form-hint text-center">Tanggal</div>
                    </div>
                    <div class="date-field-wide">
                        <select class="form-select-custom select2-no-search" id="bulan" name="bulan" required>
                            <option value="">— Bulan —</option>
                            @foreach(range(1, 12) as $m)
                                <option value="{{ $m }}">{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                            @endforeach
                        </select>
                        <div class="form-hint">Bulan</div>
                    </div>
                    <div class="date-field">
                        <input type="number" class="form-control form-control-custom text-center" id="tahun" name="tahun"
                            min="2000" max="{{ date('Y') }}" value="{{ date('Y') }}" required>
                        <div class="form-hint text-center">Tahun</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ======================== CARD 3: INFORMASI FENOMENA ======================== --}}
        <div class="form-card fade-in-up delay-200">
            <div class="form-card-header">
                <div class="card-icon-wrap">
                    <i class="fas fa-layer-group"></i>
                </div>
                <div>
                    <div class="card-title">Informasi Fenomena</div>
                    <div class="card-subtitle">Judul, klasifikasi, dan jenis fenomena</div>
                </div>
            </div>
            <div class="form-card-body">

                {{-- Judul --}}
                <div class="mb-4">
                    <label for="judul" class="form-label-custom">
                        Judul Fenomena <span class="required-star">*</span>
                    </label>
                    <input type="text" class="form-control form-control-custom" id="judul" name="judul"
                        placeholder="Contoh: Kenaikan Harga BBM Berdampak pada Inflasi..." required>
                    <div class="form-hint">Judul singkat dan deskriptif sesuai fenomena</div>
                </div>

                {{-- Kode LU & Indikator --}}
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="sektor_usaha_id" class="form-label-custom">
                            Kode Lapangan Usaha <span class="required-star">*</span>
                        </label>
                        <select class="form-select-custom select2" id="sektor_usaha_id" name="sektor_usaha_id" required>
                            <option value="">Pilih Lapangan Usaha</option>
                            @foreach($sektorUsahas as $sektor)
                                <option value="{{ $sektor->id }}">[{{ $sektor->kode }}] {{ $sektor->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="indikator_id" class="form-label-custom">
                            Kode Indikator <span class="required-star">*</span>
                        </label>
                        <select class="form-select-custom select2" id="indikator_id" name="indikator_id" required>
                            <option value="">Pilih Indikator</option>
                            @foreach($indikators as $indikator)
                                <option value="{{ $indikator->id }}">[{{ $indikator->kode }}] {{ $indikator->nama }}
                                    ({{ ucfirst($indikator->kelompok) }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Jenis Fenomena Multi --}}
                <div>
                    <label for="jenis_fenomena_ids" class="form-label-custom">
                        Jenis Fenomena <span class="required-star">*</span>
                        <span class="text-muted" style="font-size:0.76rem; font-weight:400;">(boleh lebih dari 1)</span>
                    </label>
                    <select class="form-select-custom select2" id="jenis_fenomena_ids" name="jenis_fenomena_ids[]"
                        multiple="multiple" required>
                        @foreach($jenisFenomenas as $jenis)
                            <option value="{{ $jenis->id }}">{{ $jenis->nama }}</option>
                        @endforeach
                    </select>
                    <div class="form-hint">Pilih satu atau lebih jenis yang relevan</div>
                </div>

            </div>
        </div>

        {{-- ======================== CARD 4: PENJELASAN FENOMENA ======================== --}}
        <div class="form-card fade-in-up delay-300">
            <div class="form-card-header">
                <div class="card-icon-wrap">
                    <i class="fas fa-align-left"></i>
                </div>
                <div>
                    <div class="card-title">Penjelasan Fenomena</div>
                    <div class="card-subtitle">Uraian naratif tentang fenomena yang terjadi</div>
                </div>
            </div>
            <div class="form-card-body">
                <label for="penjelasan" class="form-label-custom">
                    Uraian / Penjelasan <span class="required-star">*</span>
                </label>
                <textarea class="form-control form-control-custom textarea-penjelasan" id="penjelasan" name="penjelasan"
                    rows="6"
                    placeholder="Tuliskan penjelasan lengkap mengenai fenomena ini, termasuk dampak, penyebab, dan konteks kejadian..."
                    required></textarea>
                <div class="form-hint">Gunakan bahasa yang jelas dan faktual. Minimal 2–3 paragraf dianjurkan.</div>
            </div>
        </div>

        {{-- ======================== STICKY ACTION BAR ======================== --}}
        <div class="sticky-action-bar">
            <div class="action-inner">
                <a href="{{ route('fenomena.index') }}" class="btn-batal">
                    <i class="fas fa-times"></i>
                    Batal
                </a>
                <button type="submit" class="btn-simpan" id="btn-simpan">
                    <i class="fas fa-save"></i>
                    Simpan Data Fenomena
                </button>
            </div>
        </div>

    </form>

@endsection

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function () {

            // ── Initialize Select2 for single selects ──
            $('#sumber_berita_id, #sektor_usaha_id, #indikator_id').select2({
                theme: 'default',
                width: '100%',
                placeholder: function () { return $(this).data('placeholder'); },
                allowClear: true,
            });

            // ── Bulan without search ──
            $('#bulan').select2({
                theme: 'default',
                width: '100%',
                minimumResultsForSearch: Infinity,
            });

            // ── Multiple select (Jenis Fenomena) ──
            $('#jenis_fenomena_ids').select2({
                theme: 'default',
                width: '100%',
                placeholder: 'Pilih jenis fenomena...',
                closeOnSelect: false,
            });

            // ── Dynamic required for Link Berita ──
            $('#sumber_berita_id').on('change', function () {
                const isOnline = $(this).find('option:selected').data('online') == '1';
                const linkInput = $('#link');
                const indicator = $('#link-required-indicator');

                if (isOnline) {
                    linkInput.attr('required', true);
                    indicator.html('<span class="required-star">*</span> <span style="color:#ef4444; font-size:0.78rem;">(Wajib untuk sumber online)</span>');
                } else {
                    linkInput.removeAttr('required');
                    indicator.html('<span class="text-muted">(Opsional)</span>');
                }
            }).trigger('change');

            // ── AJAX Uniqueness Check ──
            let timeout = null;
            const fieldStatus = { judul: true, link_berita: true, url_valid: true };

            function updateButtonState() {
                const disabled = !fieldStatus.judul || !fieldStatus.link_berita || !fieldStatus.url_valid;
                $('#btn-simpan').prop('disabled', disabled);
            }

            // ── URL Format Validator ──
            function isValidUrl(value) {
                if (!value) return true; // kosong = opsional, skip
                try {
                    const url = new URL(value);
                    return url.protocol === 'http:' || url.protocol === 'https:';
                } catch (_) {
                    return false;
                }
            }

            function showFieldError(element, message) {
                element.addClass('is-invalid').removeClass('is-valid');
                element.siblings('.field-invalid-msg').remove();
                element.after(`<div class="field-invalid-msg"><i class="fas fa-exclamation-circle"></i> ${message}</div>`);
            }

            function clearFieldError(element, valid) {
                element.siblings('.field-invalid-msg').remove();
                if (valid) {
                    element.addClass('is-valid').removeClass('is-invalid');
                } else {
                    element.removeClass('is-valid is-invalid');
                }
            }

            function checkUniqueness(field, value, element) {
                if (value.length < 3) {
                    clearFieldError(element, false);
                    fieldStatus[field] = true;
                    updateButtonState();
                    return;
                }

                $.ajax({
                    url: "/fenomena/check-uniqueness",
                    type: "GET",
                    data: { field, value },
                    success: function (response) {
                        if (response.exists) {
                            showFieldError(element, response.message);
                            fieldStatus[field] = false;
                        } else {
                            clearFieldError(element, true);
                            fieldStatus[field] = true;
                        }
                        updateButtonState();
                    },
                    error: function () {
                        fieldStatus[field] = true;
                        updateButtonState();
                    }
                });
            }

            // ── Judul: AJAX uniqueness ──
            $('#judul').on('input', function () {
                const element = $(this);
                clearTimeout(timeout);
                timeout = setTimeout(function () {
                    checkUniqueness('judul', element.val(), element);
                }, 500);
            });

            // ── Link Berita: validasi URL dulu, baru AJAX uniqueness ──
            $('#link').on('input', function () {
                const element = $(this);
                const value = element.val().trim();

                // Kosong → reset semua
                if (!value) {
                    clearFieldError(element, false);
                    fieldStatus.url_valid = true;
                    fieldStatus.link_berita = true;
                    updateButtonState();
                    return;
                }

                // Cek format URL
                if (!isValidUrl(value)) {
                    showFieldError(element, 'Format tidak valid. Gunakan URL lengkap, contoh: https://kompas.com/...');
                    fieldStatus.url_valid = false;
                    fieldStatus.link_berita = true; // reset AJAX status
                    updateButtonState();
                    return;
                }

                // URL valid → lanjut AJAX uniqueness
                fieldStatus.url_valid = true;
                clearTimeout(timeout);
                timeout = setTimeout(function () {
                    checkUniqueness('link_berita', value, element);
                }, 500);
            });
        });

        // ── Ambil Otomatis ──
        async function ambilBerita() {
            const btn = document.getElementById("btn-fetch");
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Membaca...';
            btn.disabled = true;

            try {
                // Try clipboard textarea first, then system clipboard
                let text = document.getElementById('textarea-clipboard').value.trim();
                if (!text) text = await navigator.clipboard.readText();

                if (!text || text.length < 50)
                    throw new Error("Konten terlalu pendek atau kosong");

                // ── LINK ──
                const linkMatch = text.match(/https?:\/\/[^\s]+/g);
                if (linkMatch) document.getElementById("link").value = linkMatch[0];

                // ── TANGGAL ──
                const bulanMap = {
                    januari: 1, februari: 2, maret: 3, april: 4, mei: 5, juni: 6,
                    juli: 7, agustus: 8, september: 9, oktober: 10, november: 11, desember: 12
                };
                const dateMatch = text.match(/(\d{1,2})\s(Januari|Februari|Maret|April|Mei|Juni|Juli|Agustus|September|Oktober|November|Desember)\s(\d{4})/i);
                if (dateMatch) {
                    document.getElementById("tanggal").value = dateMatch[1];
                    // Trigger select2 untuk bulan
                    $('#bulan').val(bulanMap[dateMatch[2].toLowerCase()]).trigger('change');
                    document.getElementById("tahun").value = dateMatch[3];
                }

                // ── JUDUL ──
                let judul = text.split(/kompas\.com|detikcom|tribunnews|tempo\.co/i)[0];
                judul = judul.split('\n')[0].trim();
                document.getElementById("judul").value = judul;

                // ── ISI BERITA ──
                let startIndex = text.search(/[A-Z][A-Z\s]+ \([A-Z]+\) -/);
                let isi = startIndex !== -1 ? text.substring(startIndex) : text;
                isi = isi.replace(/^.*?\) -\s*/, '');
                let paragraf = isi.split('\n')
                    .map(l => l.trim())
                    .filter(l => l.length > 100 && !l.includes('ANTARA') && !l.includes('waktu baca') && !l.match(/^\w+\s\d{1,2},/));
                isi = paragraf.slice(0, 3).join(' ').replace(/\s+/g, ' ').trim();
                document.getElementById("penjelasan").value = isi;

                // Trigger uniqueness check
                $('#judul, #link').trigger('input');

                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'Form otomatis terisi dari berita',
                    timer: 1800,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end',
                });

            } catch (e) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Gagal Membaca',
                    text: 'Copy seluruh isi berita dulu (CTRL+A → CTRL+C), atau tempel teks di kolom di atas.',
                    confirmButtonColor: '#0093dd',
                });
            }

            btn.innerHTML = '<i class="fas fa-magic"></i> Ambil Otomatis';
            btn.disabled = false;
        }
    </script>
@endpush