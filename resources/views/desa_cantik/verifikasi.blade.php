@extends('layouts.admin')

@section('title', 'Verifikasi Progress Desa Cantik')

@section('content')
    <div class="mt-2 fade-in-up">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
            <div>
                <h2 class="fw-bold mb-1" style="color: var(--bps-orange);">Verifikasi Progress</h2>
                <p class="text-muted mb-0">Daftar pengajuan progress kegiatan yang butuh verifikasi Provinsi</p>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-navy">
                            <tr>
                                <th class="px-4 py-3" width="5%">No</th>
                                <th class="py-3">Desa / Kecamatan</th>
                                <th class="py-3">Kabupaten</th>
                                <th class="py-3">Kegiatan</th>
                                <th class="py-3">Tgl Realisasi</th>
                                <th class="py-3">Bukti</th>
                                <th class="px-4 py-3 text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pendingVerifications as $index => $item)
                                <tr>
                                    <td class="px-4 py-3 text-muted">{{ $pendingVerifications->firstItem() + $index }}</td>
                                    <td class="py-3">
                                        <div class="fw-bold text-navy">{{ $item->peserta->desa->nama_desa }}</div>
                                        <small class="text-muted">{{ $item->peserta->kecamatan->nama_kecamatan }}</small>
                                    </td>
                                    <td class="py-3">
                                        <span
                                            class="badge bg-light text-dark border">{{ $item->peserta->kabupaten->nama_kabupaten }}</span>
                                    </td>
                                    <td class="py-3">
                                        <div class="fw-bold">{{ $item->kegiatan->nama_kegiatan }}</div>
                                        <small class="text-muted">Periode {{ $item->peserta->periode->tahun }}</small>
                                    </td>
                                    <td class="py-3 align-middle">
                                        {{ $item->realisasi_tanggal ? date('d/m/Y', strtotime($item->realisasi_tanggal)) : '-' }}
                                    </td>
                                    <td class="py-3">
                                        <div class="d-flex flex-wrap gap-1">
                                            @forelse($item->buktis as $bukti)
                                                @php $isUrl = filter_var($bukti->link_file, FILTER_VALIDATE_URL); @endphp
                                                @if($isUrl)
                                                    <a href="{{ $bukti->link_file }}" target="_blank"
                                                        class="btn btn-xs btn-outline-info rounded-pill py-0 px-2"
                                                        style="font-size: 0.7rem;">
                                                        <i class="fas fa-link"></i> {{ $bukti->jenisBukti->nama_bukti }}
                                                    </a>
                                                @else
                                                    <span class="badge bg-light text-dark border py-1" style="font-size: 0.65rem;"
                                                        title="{{ $bukti->link_file }}">
                                                        <i class="fas fa-file-alt text-muted"></i> {{ $bukti->jenisBukti->nama_bukti }}
                                                    </span>
                                                @endif
                                            @empty
                                                <span class="text-muted small">-</span>
                                            @endforelse
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-end">
                                        <div class="d-flex justify-content-end gap-2">
                                            <form id="verify-form-{{ $item->id }}" action="{{ route('desa-cantik.progress.verify', [$item->peserta_id, $item->id]) }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="action" id="action-{{ $item->id }}" value="approve">
                                                <input type="hidden" name="alasan_penolakan" id="alasan-{{ $item->id }}" value="">
                                                <button type="submit" class="btn btn-sm btn-success rounded-pill px-3">
                                                    <i class="fas fa-check me-1"></i> Approve
                                                </button>
                                                <button type="button" onclick="rejectProgress({{ $item->id }})" class="btn btn-sm btn-outline-danger rounded-pill px-3">
                                                    <i class="fas fa-times me-1"></i> Reject
                                                </button>
                                            </form>
                                            <a href="{{ route('desa-cantik.progress.detail', $item->peserta_id) }}"
                                                class="btn btn-sm btn-light rounded-pill px-2" title="Lihat Detail">
                                                <i class="fas fa-external-link-alt"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="fas fa-coffee fa-3x mb-3 opacity-20"></i>
                                        <p class="mb-0 fw-bold">Semua beres! Tidak ada antrian verifikasi.</p>
                                        <p class="small text-muted">Ayo istirahat sejenak.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-4">
                    {{ $pendingVerifications->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <script>
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
                    document.getElementById('action-' + progId).value = 'reject';
                    document.getElementById('alasan-' + progId).value = result.value;
                    document.getElementById('verify-form-' + progId).submit();
                }
            });
        }
    </script>
    <style>
        .btn-xs {
            padding: 0.1rem 0.4rem;
            font-size: 0.75rem;
        }

        .text-navy {
            color: #1e293b;
        }
    </style>
@endpush