@extends('layouts.admin')

@section('title', 'Pra Ekspor Fenomena')

@push('styles')
    <style>
        /* Reusing consistent styles from fenomena */
        :root {
            --fi-primary: #f58220;
            --fi-primary-lt: #fff4eb;
            --fi-success: #16a34a;
            --fi-success-lt: #f0fdf4;
            --fi-border: #e2e8f0;
            --fi-surface: #f8fafc;
            --fi-text: #0f172a;
            --fi-muted: #64748b;
            --fi-radius: 14px;
            --fi-shadow: 0 1px 4px rgba(0, 0, 0, .05), 0 4px 16px rgba(0, 0, 0, .06);
        }

        .fi-page {
            font-family: 'Inter', sans-serif;
            color: var(--fi-text);
        }

        .fi-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .fi-title {
            font-size: 1.55rem;
            font-weight: 800;
            color: var(--fi-primary);
            margin: 0;
        }

        .fi-subtitle {
            font-size: .875rem;
            color: var(--fi-muted);
            margin: .25rem 0 0;
        }

        .fi-filter-bar {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: .55rem;
            padding: .9rem 1.1rem;
            background: #fff;
            border: 1px solid var(--fi-border);
            border-radius: 12px;
            box-shadow: var(--fi-shadow);
            margin-bottom: 1.25rem;
        }

        .fi-select {
            height: 38px;
            padding: 0 .8rem;
            border: 1px solid var(--fi-border);
            border-radius: 9px;
            font-size: .82rem;
            color: var(--fi-text);
            background: #fff;
            outline: none;
            cursor: pointer;
            transition: border-color .18s;
            flex: 1;
            min-width: 150px;
        }

        .fi-select:focus {
            border-color: var(--fi-primary);
        }

        .fi-btn-submit {
            height: 38px;
            padding: 0 1rem;
            border-radius: 9px;
            font-size: .83rem;
            font-weight: 600;
            border: none;
            cursor: pointer;
            background: var(--fi-primary);
            color: #fff;
            display: inline-flex;
            align-items: center;
            gap: .4rem;
        }

        .fi-empty {
            text-align: center;
            padding: 4rem 2rem;
            background: #fff;
            border: 1px solid var(--fi-border);
            border-radius: var(--fi-radius);
            box-shadow: var(--fi-shadow);
        }

        .fi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .fi-card {
            background: #fff;
            border: 1px solid var(--fi-border);
            border-radius: var(--fi-radius);
            box-shadow: var(--fi-shadow);
            padding: 1.25rem 1.35rem;
            display: flex;
            flex-direction: column;
            gap: .75rem;
            position: relative;
            overflow: hidden;
            transition: box-shadow .2s, transform .2s;
        }

        .fi-card:hover {
            box-shadow: 0 4px 20px rgba(0, 0, 0, .1);
            transform: translateY(-2px);
        }

        .fi-card-title {
            font-size: .97rem;
            font-weight: 700;
            color: var(--fi-text);
            line-height: 1.4;
            margin: 0;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .fi-card-preview {
            font-size: .83rem;
            color: #475569;
            line-height: 1.6;
            margin: 0;
            overflow: hidden;
        }

        /* Toggle Switch Styling */
        .form-switch .form-check-input {
            width: 3em;
            height: 1.5em;
            cursor: pointer;
            margin-top: 0;
        }

        .form-check-input:checked {
            background-color: var(--fi-success);
            border-color: var(--fi-success);
        }

        .selection-toggle-wrap {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: .75rem;
            margin-top: auto;
            border-top: 1px solid var(--fi-border);
        }

        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
        }

        /* ─── Pagination ────────────────────────────────── */
        .fi-pagination {
            display: flex;
            justify-content: center;
            gap: .35rem;
            flex-wrap: wrap;
            margin-top: 1.5rem;
        }

        .fi-pagination .page-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 36px;
            height: 36px;
            padding: 0 .6rem;
            border-radius: 9px;
            font-size: .82rem;
            font-weight: 500;
            border: 1px solid var(--fi-border);
            background: #fff;
            color: var(--fi-muted);
            text-decoration: none;
            transition: all .18s;
        }

        .fi-pagination .page-link:hover {
            background: var(--fi-surface);
            color: var(--fi-text);
            border-color: #cbd5e1;
        }

        .fi-pagination .page-link.active {
            background: var(--fi-primary);
            border-color: var(--fi-primary);
            color: #fff;
            box-shadow: 0 2px 8px rgba(245, 130, 32, .3);
        }

        .fi-pagination .page-link.disabled {
            opacity: .45;
            pointer-events: none;
        }
    </style>
@endpush

@section('content')
    <div class="fi-page" style="padding: 1.5rem 0;">

        <div class="fi-header">
            <div>
                <h1 class="fi-title">PRA EKSPOR FENOMENA</h1>
                <p class="fi-subtitle">Pilih wilayah untuk memfilter dan memilih fenomena yang akan diekspor</p>
            </div>
        </div>

        {{-- Filter Form --}}
        <form method="GET" action="{{ route('pra-ekspor.index') }}" class="fi-filter-bar" id="filter-form">
            <select class="fi-select" name="kabupaten_id" required>
                <option value="">-- Pilih Wilayah Target (Kabupaten) --</option>
                @foreach($kabupatens as $kab)
                    <option value="{{ $kab->id }}" @selected($selectedKabupatenId == $kab->id)>
                        {{ $kab->kode_kab }} - {{ $kab->nama_kabupaten }}
                    </option>
                @endforeach
            </select>

            <select class="fi-select" name="indikator_id" style="min-width: 180px;">
                <option value="">-- Semua Indikator --</option>
                @foreach($indikators as $ind)
                    <option value="{{ $ind->id }}" @selected($selectedIndikatorId == $ind->id)>
                        {{ $ind->nama }}
                    </option>
                @endforeach
            </select>

            <select class="fi-select" name="tahun" style="max-width: 140px;">
                <option value="all" @selected($tahun == 'all')>-- Semua Tahun --</option>
                @php $currentYear = date('Y'); @endphp
                @for($y = $currentYear; $y >= $currentYear - 3; $y--)
                    <option value="{{ $y }}" @selected($tahun == $y)>{{ $y }}</option>
                @endfor
            </select>

            <select class="fi-select" name="bulan" style="max-width: 160px;">
                <option value="all" @selected($bulan == 'all')>-- Semua Bulan --</option>
                @foreach([1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'] as $num => $nama)
                    <option value="{{ $num }}" @selected($bulan == $num)>{{ $nama }}</option>
                @endforeach
            </select>

            <button type="submit" class="fi-btn-submit">
                <i class="fas fa-filter"></i> Terapkan Filter
            </button>

            @if($selectedKabupatenId)
                <a href="{{ route('pra-ekspor.preview', ['kabupaten_id' => $selectedKabupatenId, 'tahun' => $tahun, 'bulan' => $bulan]) }}"
                    id="btn-preview-sasaran" class="fi-btn-submit"
                    style="background: #10b981; text-decoration: none; margin-left: auto;">
                    <i class="fas fa-table"></i> Lihat Tabel Ekspor
                </a>
            @endif
            <a href="{{ route('pra-ekspor.preview-semua', ['tahun' => $tahun, 'bulan' => $bulan]) }}" id="btn-preview-semua"
                class="fi-btn-submit"
                style="background: #0284c7; text-decoration: none; {{ !$selectedKabupatenId ? 'margin-left: auto;' : '' }}">
                <i class="fas fa-globe"></i> Tabel Semua Wilayah
            </a>
        </form>

        {{-- Results & Grid --}}
        @if(!$selectedKabupatenId)
            <div class="fi-empty">
                <div style="font-size: 2rem; color: var(--fi-muted); margin-bottom: 1rem;"><i class="fas fa-map-marked-alt"></i>
                </div>
                <h5 class="fw-bold mb-2">Pilih Wilayah Terlebih Dahulu</h5>
                <p class="text-muted mb-0">Silakan pilih Kabupaten pada filter di atas untuk melihat daftar fenomena yang
                    terdeteksi.</p>
            </div>
        @elseif(isset($fenomenas) && $fenomenas->isEmpty())
            <div class="fi-empty">
                <div style="font-size: 2rem; color: var(--fi-muted); margin-bottom: 1rem;"><i class="fas fa-search-minus"></i>
                </div>
                <h5 class="fw-bold mb-2">Tidak Ada Fenomena yang Cocok</h5>
                <p class="text-muted mb-0">Tidak ditemukan fenomena dengan kata kunci wilayah tersebut pada tahun dan bulan yang
                    dipilih.</p>
            </div>
        @elseif(isset($fenomenas))

            <div style="margin-bottom: 1rem; color: var(--fi-muted); font-size: 0.9rem;">
                Ditemukan <strong>{{ $fenomenas->total() }}</strong> fenomena. Silakan gunakan <i>toggle switch</i> untuk
                memilih berita.
            </div>

            <div class="fi-grid">
                @foreach($fenomenas as $fen)
                    <div class="fi-card">
                        <h2 class="fi-card-title">{{ $fen->judul }}</h2>
                        <p class="fi-card-preview">
                            {{ str($fen->penjelasan)->limit(140) }}
                            @if(strlen($fen->penjelasan) > 140)
                                <a href="#"
                                    style="font-size: 0.8rem; text-decoration: none; font-weight: 500; color: var(--fi-primary);"
                                    data-bs-toggle="modal" data-bs-target="#modalFenomena{{ $fen->id }}">Lihat selengkapnya</a>
                            @endif
                        </p>

                        <div style="font-size: 0.75rem; color: var(--fi-muted); display: flex; gap: 10px; flex-wrap: wrap;">
                            <span><i class="fas fa-calendar-alt"></i>
                                {{ \Carbon\Carbon::parse($fen->tanggal_berita)->translatedFormat('d F Y') }}</span>
                            <span><i class="fas fa-user"></i> {{ $fen->creator->name ?? 'Unknown' }}
                                ({{ $fen->creator->kabupaten->nama_kabupaten ?? '-' }})</span>
                            @if($selectedIndikatorId)
                                @php
                                    $matchedInd = $fen->indikators->firstWhere('id', $selectedIndikatorId) ?? $fen->indikators->firstWhere('kode', $selectedIndikatorId);
                                @endphp
                                @if($matchedInd && $matchedInd->pivot && $matchedInd->pivot->arah)
                                    <span class="fw-bold"
                                        style="color: {{ strtolower($matchedInd->pivot->arah) == 'naik' ? 'var(--fi-success)' : '#dc2626' }}; background: {{ strtolower($matchedInd->pivot->arah) == 'naik' ? 'var(--fi-success-lt)' : '#fef2f2' }}; padding: 2px 6px; border-radius: 4px;">
                                        <i class="fas fa-arrow-{{ strtolower($matchedInd->pivot->arah) == 'naik' ? 'up' : 'down' }}"></i>
                                        {{ strtoupper($matchedInd->pivot->arah) }} - {{ str($matchedInd->nama)->limit(25) }}
                                    </span>
                                @endif
                            @else
                                @php
                                    $utamaInd = $fen->indikators->where('kelompok', 'utama')->first();
                                @endphp
                                @if($utamaInd && $utamaInd->pivot && $utamaInd->pivot->arah)
                                    <span class="fw-bold"
                                        style="color: {{ strtolower($utamaInd->pivot->arah) == 'naik' ? 'var(--fi-success)' : '#dc2626' }}; background: {{ strtolower($utamaInd->pivot->arah) == 'naik' ? 'var(--fi-success-lt)' : '#fef2f2' }}; padding: 2px 6px; border-radius: 4px;">
                                        <i class="fas fa-arrow-{{ strtolower($utamaInd->pivot->arah) == 'naik' ? 'up' : 'down' }}"></i>
                                        {{ strtoupper($utamaInd->pivot->arah) }} - {{ str($utamaInd->nama)->limit(25) }}
                                    </span>
                                @elseif($fen->indikators->count() > 0)
                                    <span style="color: var(--fi-muted);"><i class="fas fa-tags"></i> {{ $fen->indikators->count() }}
                                        Indikator terkait</span>
                                @endif
                            @endif
                        </div>

                        <div class="selection-toggle-wrap">
                            <span class="fw-bold" style="font-size: 0.85rem; color: var(--fi-text);">
                                Tambahkan ke Ekspor
                            </span>
                            <div class="form-check form-switch custom-switch">
                                <input class="form-check-input toggle-selection" type="checkbox" role="switch"
                                    id="toggle-{{ $fen->id }}" data-id="{{ $fen->id }}" {{ in_array($fen->id, $selectedIds) ? 'checked' : '' }}>
                                <label class="form-check-label d-none" for="toggle-{{ $fen->id }}">Toggle</label>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Modals for Long Description --}}
            @foreach($fenomenas as $fen)
                @if(strlen($fen->penjelasan) > 140)
                    <div class="modal fade" id="modalFenomena{{ $fen->id }}" tabindex="-1"
                        aria-labelledby="modalFenomenaLabel{{ $fen->id }}" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
                            <div class="modal-content"
                                style="border-radius: 12px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                                <div class="modal-header"
                                    style="border-bottom: 1px solid #e2e8f0; background: #f8fafc; border-radius: 12px 12px 0 0;">
                                    <h5 class="modal-title fw-bold" id="modalFenomenaLabel{{ $fen->id }}"
                                        style="color: var(--fi-primary); font-size: 1.1rem;">
                                        <i class="fas fa-align-left me-2"></i>Detail Penjelasan Fenomena
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body" style="color: #334155; font-size: 0.95rem; line-height: 1.7;">
                                    <h6 class="fw-bold mb-3" style="color: #0f172a;">{{ $fen->judul }}</h6>
                                    <div style="white-space: pre-wrap; text-align: justify;">{{ $fen->penjelasan }}</div>
                                </div>
                                <div class="modal-footer"
                                    style="border-top: 1px solid #e2e8f0; background: #f8fafc; border-radius: 0 0 12px 12px;">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                                        style="border-radius: 8px; font-weight: 500;">Tutup</button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach

            {{-- Pagination --}}
            @if($fenomenas->hasPages())
                <div class="fi-pagination">
                    @if($fenomenas->onFirstPage())
                        <span class="page-link disabled"><i class="fas fa-chevron-left"></i></span>
                    @else
                        <a class="page-link" href="{{ $fenomenas->previousPageUrl() }}"><i class="fas fa-chevron-left"></i></a>
                    @endif

                    @foreach($fenomenas->getUrlRange(max(1, $fenomenas->currentPage() - 2), min($fenomenas->lastPage(), $fenomenas->currentPage() + 2)) as $page => $url)
                        @if($page == $fenomenas->currentPage())
                            <span class="page-link active">{{ $page }}</span>
                        @else
                            <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                        @endif
                    @endforeach

                    @if($fenomenas->hasMorePages())
                        <a class="page-link" href="{{ $fenomenas->nextPageUrl() }}"><i class="fas fa-chevron-right"></i></a>
                    @else
                        <span class="page-link disabled"><i class="fas fa-chevron-right"></i></span>
                    @endif
                </div>
            @endif

        @endif
    </div>

    <!-- Toast Container for Notifications -->
    <div class="toast-container"></div>

@endsection

@push('scripts')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Update preview button links dynamically based on filter selects
            const btnPreviewSasaran = document.getElementById('btn-preview-sasaran');
            const btnPreviewSemua = document.getElementById('btn-preview-semua');
            const selectKabupaten = document.querySelector('select[name="kabupaten_id"]');
            const selectTahun = document.querySelector('select[name="tahun"]');
            const selectBulan = document.querySelector('select[name="bulan"]');

            function updatePreviewLinks() {
                const tahun = selectTahun ? selectTahun.value : 'all';
                const bulan = selectBulan ? selectBulan.value : 'all';
                const kabupatenId = selectKabupaten ? selectKabupaten.value : '';

                if (btnPreviewSasaran && kabupatenId) {
                    const url = new URL(btnPreviewSasaran.href);
                    url.searchParams.set('kabupaten_id', kabupatenId);
                    url.searchParams.set('tahun', tahun);
                    url.searchParams.set('bulan', bulan);
                    btnPreviewSasaran.href = url.toString();
                }

                if (btnPreviewSemua) {
                    const url = new URL(btnPreviewSemua.href);
                    url.searchParams.set('tahun', tahun);
                    url.searchParams.set('bulan', bulan);
                    btnPreviewSemua.href = url.toString();
                }
            }

            if (selectTahun) selectTahun.addEventListener('change', updatePreviewLinks);
            if (selectBulan) selectBulan.addEventListener('change', updatePreviewLinks);
            if (selectKabupaten) selectKabupaten.addEventListener('change', updatePreviewLinks);

            const toggleSwitches = document.querySelectorAll('.toggle-selection');
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            const kabupatenId = '{{ $selectedKabupatenId ?? '' }}';
            const tahunStr = '{{ $tahun ?? date('Y') }}';
            const bulanStr = '{{ $bulan ?? date('n') }}';

            toggleSwitches.forEach(toggle => {
                toggle.addEventListener('change', function () {
                    const fenomenaId = this.dataset.id;
                    const isSelected = this.checked ? 1 : 0;
                    const oldState = !this.checked;

                    // Disable briefly to prevent double clicks
                    this.disabled = true;

                    fetch('/pra-ekspor/toggle', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            kabupaten_id: kabupatenId,
                            fenomena_id: fenomenaId,
                            tahun: tahunStr,
                            bulan: bulanStr,
                            is_selected: isSelected
                        })
                    })
                        .then(response => response.json())
                        .then(data => {
                            this.disabled = false;
                            if (data.success) {
                                showToast('Berhasil', isSelected ? 'Berita ditambahkan ke Pra Ekspor' : 'Berita dihapus dari Pra Ekspor', 'success');
                            } else {
                                // Revert on server error
                                this.checked = oldState;
                                showToast('Gagal', 'Terjadi kesalahan sistem', 'danger');
                            }
                        })
                        .catch(error => {
                            this.disabled = false;
                            this.checked = oldState; // revert visually
                            showToast('Error', 'Gagal menghubungi server', 'danger');
                        });
                });
            });

            function showToast(title, message, type = 'success') {
                const toastContainer = document.querySelector('.toast-container');
                const bgClass = type === 'success' ? 'bg-success' : 'bg-danger';

                const toastHTML = `
                                                            <div class="toast align-items-center text-white ${bgClass} border-0 show" role="alert" aria-live="assertive" aria-atomic="true">
                                                                <div class="d-flex">
                                                                    <div class="toast-body">
                                                                        <strong>${title}:</strong> ${message}
                                                                    </div>
                                                                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                                                                </div>
                                                            </div>
                                                        `;

                toastContainer.insertAdjacentHTML('beforeend', toastHTML);
                const newToast = toastContainer.lastElementChild;

                setTimeout(() => {
                    newToast.classList.remove('show');
                    setTimeout(() => newToast.remove(), 300);
                }, 3000);
            }
        });
    </script>
@endpush