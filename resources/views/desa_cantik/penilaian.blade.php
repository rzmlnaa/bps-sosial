@extends('layouts.admin')

@section('title', 'Penilaian Desa Cantik')

@section('content')
    <div class="mt-3 fade-in-up">
        {{-- Header --}}
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
            <div>
                <h2 class="fw-bold mb-1" style="color: var(--bps-orange);">Penilaian Desa Cantik</h2>
                <p class="text-muted mb-0">Kelola penilaian mandiri dan verifikasi provinsi</p>
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
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4 bps-card">
                    <div class="card-header bg-white border-bottom py-3">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <h6 class="mb-0 fw-bold" style="color: var(--bps-orange);">
                                <i class="fas fa-star me-2"></i>Daftar Penilaian Peserta
                            </h6>
                            {{-- Filter --}}
                            <form method="GET" action="{{ route('desa-cantik.penilaian') }}"
                                class="d-flex gap-2 align-items-center">
                                @if($isProvinsi)
                                    <select name="kabupaten_id" class="form-select form-select-sm" onchange="this.form.submit()">
                                        <option value="">Semua Kabupaten</option>
                                        @foreach($kabupatens as $kab)
                                            <option value="{{ $kab->id }}" {{ request('kabupaten_id') == $kab->id ? 'selected' : '' }}>
                                                [{{ $kab->kode_kab }}] {{ $kab->nama_kabupaten }}
                                            </option>
                                        @endforeach
                                    </select>
                                @endif
                                <select name="filter_periode" class="form-select form-select-sm" style="min-width:130px;"
                                    onchange="this.form.submit()">
                                    <option value="all" {{ $filterPeriode === 'all' ? 'selected' : '' }}>Semua Periode</option>
                                    @foreach($periodes as $p)
                                        <option value="{{ $p->id }}" {{ $filterPeriode == $p->id ? 'selected' : '' }}>
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
                                <table class="table table-hover align-middle mb-0 text-center">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="ps-3 text-start" width="5%">No</th>
                                            <th class="text-start">Desa</th>
                                            <th class="text-start">Kecamatan / Kabupaten</th>
                                            <th>Penilaian Mandiri<br><small class="text-muted">(Desa)</small></th>
                                            <th>Penilaian Mandiri<br><small class="text-muted">(Kab/Kota)</small></th>
                                            <th>Verifikasi<br><small class="text-muted">(Provinsi)</small></th>
                                            <th>Catatan</th>
                                            <th class="pe-3">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($pesertas as $index => $peserta)
                                            @php $penilaian = $peserta->penilaian; @endphp
                                            <tr>
                                                <td class="ps-3 text-start">
                                                    {{ ($pesertas->currentPage() - 1) * $pesertas->perPage() + $index + 1 }}
                                                </td>
                                                <td class="text-start">
                                                    <div class="fw-bold fs-6 text-dark">
                                                        <span class="badge bg-light text-dark border me-1" style="font-size: 0.7rem;">{{ $peserta->desa->kode_desa ?? '-' }}</span>
                                                        {{ $peserta->desa->nama_desa ?? '-' }}
                                                    </div>
                                                    <div class="text-muted small" style="font-size: 0.75rem;">
                                                        <i class="fas fa-calendar-alt me-1 opacity-50"></i>{{ $peserta->periode->tahun ?? '-' }}
                                                    </div>
                                                </td>
                                                <td class="text-start">
                                                    <div class="small fw-medium text-secondary">
                                                        <span class="text-muted me-1">[{{ $peserta->kecamatan->kode_kecamatan ?? '-' }}]</span>
                                                        {{ $peserta->kecamatan->nama_kecamatan ?? '-' }}
                                                    </div>
                                                    <div class="mt-1">
                                                        <span class="badge rounded-pill fw-normal" style="font-size: 0.65rem; background-color: #f8f9fa; color: #6c757d; border: 1px solid #e9ecef;">
                                                            [{{ $peserta->kabupaten->kode_kab ?? '-' }}] {{ $peserta->kabupaten->nama_kabupaten ?? '-' }}
                                                        </span>
                                                    </div>
                                                </td>
                                                <td>
                                                    @if($penilaian && $penilaian->penilaian_mandiri_desa)
                                                        <i class="fas fa-check-circle text-success fs-5" data-bs-toggle="tooltip" title="Sudah dinilai oleh Desa"></i>
                                                    @else
                                                        <i class="fas fa-times-circle text-muted fs-5 opacity-25" data-bs-toggle="tooltip" title="Belum dinilai oleh Desa"></i>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($penilaian && $penilaian->penilaian_mandiri_kab)
                                                        <i class="fas fa-check-circle text-success fs-5" data-bs-toggle="tooltip" title="Sudah dinilai oleh Kab/Kota"></i>
                                                    @else
                                                        <i class="fas fa-times-circle text-muted fs-5 opacity-25" data-bs-toggle="tooltip" title="Belum dinilai oleh Kab/Kota"></i>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($penilaian && $penilaian->verifikasi_provinsi)
                                                        <i class="fas fa-check-circle text-success fs-5" data-bs-toggle="tooltip" title="Sudah diverifikasi Provinsi"></i>
                                                    @else
                                                        <i class="fas fa-times-circle text-muted fs-5 opacity-25" data-bs-toggle="tooltip" title="Belum diverifikasi Provinsi"></i>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($penilaian && $penilaian->catatan)
                                                        <button type="button" class="btn btn-sm btn-info text-white rounded-pill px-3 py-1" style="font-size: 0.7rem;" onclick="Swal.fire({title:'Catatan', text:'{{ addslashes($penilaian->catatan) }}', icon:'info', confirmButtonColor:'var(--bps-blue)'})">
                                                            <i class="fas fa-comment-dots me-1"></i> Lihat
                                                        </button>
                                                    @else
                                                        <span class="text-muted small italic opacity-50" style="font-size: 0.7rem;">Belum ada</span>
                                                    @endif
                                                </td>
                                                <td class="pe-3">
                                                    <div class="d-flex justify-content-center gap-1">
                                                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill btn-edit-penilaian"
                                                            style="font-size: 0.75rem;"
                                                            data-id="{{ $peserta->id }}"
                                                            data-desa="{{ $penilaian ? $penilaian->penilaian_mandiri_desa : 0 }}"
                                                            data-kab="{{ $penilaian ? $penilaian->penilaian_mandiri_kab : 0 }}"
                                                            data-prov="{{ $penilaian ? $penilaian->verifikasi_provinsi : 0 }}"
                                                            data-catatan="{{ $penilaian ? $penilaian->catatan : '' }}">
                                                            <i class="fas fa-edit me-1"></i> Edit
                                                        </button>
                                                    </div>
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

    {{-- Modal Edit Penilaian --}}
    <div class="modal fade" id="modalEditPenilaian" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow rounded-4">
                <div class="modal-header border-bottom-0">
                    <h5 class="modal-title fw-bold" style="color: var(--bps-orange);">
                        <i class="fas fa-edit me-2"></i>Update Penilaian
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formPenilaian" method="POST">
                    @csrf
                    <div class="modal-body py-0">
                        <div class="alert alert-info py-2 small mb-4">
                            <i class="fas fa-info-circle me-1"></i> Sesuaikan penilaian berdasarkan kewenangan role Anda.
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" role="switch" id="pm_desa" name="penilaian_mandiri_desa" value="1" {{ !$isProvinsi ? '' : 'disabled' }}>
                            <label class="form-check-label fw-bold" for="pm_desa">Penilaian Mandiri (Desa)</label>
                        </div>
                        
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" role="switch" id="pm_kab" name="penilaian_mandiri_kab" value="1" {{ !$isProvinsi ? '' : 'disabled' }}>
                            <label class="form-check-label fw-bold" for="pm_kab">Penilaian Mandiri (Kabupaten/Kota)</label>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" role="switch" id="v_prov" name="verifikasi_provinsi" value="1" {{ $isProvinsi ? '' : 'disabled' }}>
                            <label class="form-check-label fw-bold" for="v_prov">Verifikasi (Provinsi)</label>
                        </div>

                        <div class="mb-3 mt-4">
                            <label class="form-label fw-bold small">Catatan (Hanya Provinsi)</label>
                            <textarea class="form-control" name="catatan" id="catatan_input" rows="3" {{ $isProvinsi ? '' : 'readonly' }}></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 pt-0">
                        <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-orange rounded-pill px-4 text-white" style="background-color: var(--bps-orange);">
                            <i class="fas fa-save me-1"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('styles')
        <style>
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
            .form-switch .form-check-input {
                width: 2.5em;
                height: 1.25em;
                cursor: pointer;
            }
            .form-switch .form-check-input:checked {
                background-color: var(--bps-green) !important;
                border-color: var(--bps-green) !important;
            }
        </style>
    @endpush

    @push('scripts')
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.all.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const modal = new bootstrap.Modal(document.getElementById('modalEditPenilaian'));
                const form = document.getElementById('formPenilaian');

                // Initialize Tooltips
                const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });

                document.querySelectorAll('.btn-edit-penilaian').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const id = this.getAttribute('data-id');
                        form.action = `/desa-cantik/penilaian/${id}`;

                        document.getElementById('pm_desa').checked = this.getAttribute('data-desa') == "1";
                        document.getElementById('pm_kab').checked = this.getAttribute('data-kab') == "1";
                        document.getElementById('v_prov').checked = this.getAttribute('data-prov') == "1";
                        document.getElementById('catatan_input').value = this.getAttribute('data-catatan') || '';

                        modal.show();
                    });
                });
            });
        </script>
    @endpush
@endsection
