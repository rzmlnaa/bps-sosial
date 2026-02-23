@extends('layouts.admin')

@section('title', 'Verifikasi - ' . $kabupaten->nama_kabupaten)

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
        <div>
            <h2 class="fw-bold mb-1" style="color: var(--bps-orange);">Verifikasi {{ $kabupaten->nama_kabupaten }}</h2>
            <p class="text-muted mb-0">Tinjau data yang melebihi batas selisih harga.</p>
        </div>
        <div class="mt-3 mt-md-0">
            <a href="{{ route('verification.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Kembali
            </a>
        </div>
    </div>



    <form action="{{ route('verification.store', $kabupaten->id) }}" method="POST">
        @csrf
        
        <!-- Pending Master Data -->
        @if($pendingMaster->count() > 0)
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-bold text-navy"><i class="fas fa-database me-2"></i>Master Nilai Awal</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Komoditas</th> 
                                    <th>Tahun</th>
                                    <th>Nilai Diajukan</th>
                                    <th>Alasan Inputter</th>
                                    <th style="min-width: 300px;">Keputusan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pendingMaster as $item)
                                    <input type="hidden" name="verifications[{{ $item->id }}][type]" value="master">
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-bold">{{ $item->komoditas->nama_komoditas }}</div>
                                            <div class="small text-muted">{{ $item->komoditas->satuan }}</div>
                                        </td>
                                        <td>{{ $item->rhTahun->tahun }}</td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="text-danger small">Min: {{ number_format($item->min_edit, 0, ',', '.') }}</span>
                                                <span class="text-success small">Max: {{ number_format($item->max_edit, 0, ',', '.') }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="p-2 bg-light rounded small fst-italic text-muted">
                                                "{{ $item->alasan ?? '-' }}"
                                            </div>
                                            <div class="small mt-1 text-muted">By: {{ $item->userAdd->name ?? 'Unknown' }}</div>
                                        </td>
                                        <td class="py-3">
                                            <div class="d-flex flex-column gap-2">
                                                <div class="btn-group w-100" role="group">
                                                    <input type="radio" class="btn-check" name="verifications[{{ $item->id }}][status]" id="master_approve_{{ $item->id }}" value="approved" autocomplete="off" onchange="toggleReason({{ $item->id }}, 'master')">
                                                    <label class="btn btn-outline-success" for="master_approve_{{ $item->id }}">
                                                        <i class="fas fa-check me-1"></i> Setuju
                                                    </label>
                                                
                                                    <input type="radio" class="btn-check" name="verifications[{{ $item->id }}][status]" id="master_reject_{{ $item->id }}" value="rejected" autocomplete="off" onchange="toggleReason({{ $item->id }}, 'master')">
                                                    <label class="btn btn-outline-danger" for="master_reject_{{ $item->id }}">
                                                        <i class="fas fa-times me-1"></i> Tolak
                                                    </label>
                                                </div>
                                                <div id="reason_container_master_{{ $item->id }}" style="display: none;">
                                                    <textarea name="verifications[{{ $item->id }}][reason]" class="form-control form-control-sm" placeholder="Tulis alasan penolakan..." rows="2"></textarea>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        <!-- Pending Detail Data -->
        @if($pendingDetail->count() > 0)
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-bold text-navy"><i class="fas fa-edit me-2"></i>Perubahan Harga</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Komoditas</th>
                                    <th>Header Perubahan</th>
                                    <th>Nilai Diajukan</th>
                                    <th>Alasan Inputter</th>
                                    <th style="min-width: 300px;">Keputusan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pendingDetail as $item)
                                    <input type="hidden" name="verifications[{{ $item->id }}][type]" value="detail">
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-bold">{{ $item->komoditas->nama_komoditas }}</div>
                                            <div class="small text-muted">{{ $item->komoditas->satuan }}</div>
                                        </td>
                                        <td>
                                            <div class="fw-bold">{{ $item->revisionHeader->label }}</div>
                                            <div class="small text-muted">{{ \Carbon\Carbon::parse($item->revisionHeader->tanggal_perubahan)->format('d M Y') }}</div>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="text-danger small">Min: {{ number_format($item->min_edit, 0, ',', '.') }}</span>
                                                <span class="text-success small">Max: {{ number_format($item->max_edit, 0, ',', '.') }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="p-2 bg-light rounded small fst-italic text-muted">
                                                "{{ $item->alasan ?? '-' }}"
                                            </div>
                                            <div class="small mt-1 text-muted">By: {{ $item->userAdd->name ?? 'Unknown' }}</div>
                                        </td>
                                        <td class="py-3">
                                            <div class="d-flex flex-column gap-2">
                                                <div class="btn-group w-100" role="group">
                                                    <input type="radio" class="btn-check" name="verifications[{{ $item->id }}][status]" id="detail_approve_{{ $item->id }}" value="approved" autocomplete="off" onchange="toggleReason({{ $item->id }}, 'detail')">
                                                    <label class="btn btn-outline-success" for="detail_approve_{{ $item->id }}">
                                                        <i class="fas fa-check me-1"></i> Setuju
                                                    </label>
                                                
                                                    <input type="radio" class="btn-check" name="verifications[{{ $item->id }}][status]" id="detail_reject_{{ $item->id }}" value="rejected" autocomplete="off" onchange="toggleReason({{ $item->id }}, 'detail')">
                                                    <label class="btn btn-outline-danger" for="detail_reject_{{ $item->id }}">
                                                        <i class="fas fa-times me-1"></i> Tolak
                                                    </label>
                                                </div>
                                                <div id="reason_container_detail_{{ $item->id }}" style="display: none;">
                                                    <textarea name="verifications[{{ $item->id }}][reason]" class="form-control form-control-sm" placeholder="Tulis alasan penolakan..." rows="2"></textarea>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif



        @if($pendingMaster->count() == 0 && $pendingDetail->count() == 0)
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i> Semua data pending untuk kabupaten ini sudah diverifikasi.
            </div>
        
        @else
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body">
                    <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold">
                        <i class="fas fa-paper-plane me-2"></i> Kirim Hasil Verifikasi ke Kabkot
                    </button>
                </div>
            </div>
        @endif
    </form>
    
    <!-- Rejected Items History -->
    
    @if($rejectedMaster->count() > 0 || $rejectedDetail->count() > 0)
        <div class="mt-5">
            <h5 class="fw-bold mb-3 text-secondary"><i class="fas fa-history me-2"></i>Riwayat Penolakan (Perlu Revisi Kabkot)</h5>
            
            @if($rejectedMaster->count() > 0)
                <div class="card border-0 shadow-sm rounded-4 mb-3">
                    <div class="card-header bg-danger bg-opacity-10 border-bottom py-3">
                        <h6 class="mb-0 fw-bold text-danger">Master Nilai Awal - Ditolak</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4">Komoditas</th>
                                        <th>Tahun</th>
                                        <th>Nilai Diajukan</th>
                                        <th>Alasan Penolakan</th>
                                        <th>Ditolak Oleh</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($rejectedMaster as $item)
                                        <tr>
                                            <td class="ps-4">
                                                <div class="fw-bold">{{ $item->komoditas->nama_komoditas }}</div>
                                            </td>
                                            <td>{{ $item->rhTahun->tahun }}</td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <span class="text-secondary small">Min: {{ number_format($item->min_edit, 0, ',', '.') }}</span>
                                                    <span class="text-secondary small">Max: {{ number_format($item->max_edit, 0, ',', '.') }}</span>
                                                    <span class="small text-muted fst-italic">"{{ $item->alasan ?? '-' }}"</span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="text-danger fw-bold small">{{ $item->rejection_reason }}</div>
                                            </td>
                                            <td class="small text-muted">
                                                {{ \Carbon\Carbon::parse($item->verified_at)->format('d M H:i') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            @if($rejectedDetail->count() > 0)
                <div class="card border-0 shadow-sm rounded-4 mb-3">
                    <div class="card-header bg-danger bg-opacity-10 border-bottom py-3">
                        <h6 class="mb-0 fw-bold text-danger">Perubahan Harga - Ditolak</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4">Komoditas</th>
                                        <th>Header Perubahan</th>
                                        <th>Nilai Diajukan</th>
                                        <th>Alasan Penolakan</th>
                                        <th>Ditolak Oleh</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($rejectedDetail as $item)
                                        <tr>
                                            <td class="ps-4">
                                                <div class="fw-bold">{{ $item->komoditas->nama_komoditas }}</div>
                                            </td>
                                            <td>
                                                <div class="fw-bold">{{ $item->revisionHeader->label }}</div>
                                                <div class="small text-muted">{{ \Carbon\Carbon::parse($item->revisionHeader->tanggal_perubahan)->format('d M Y') }}</div>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <span class="text-secondary small">Min: {{ number_format($item->min_edit, 0, ',', '.') }}</span>
                                                    <span class="text-secondary small">Max: {{ number_format($item->max_edit, 0, ',', '.') }}</span>
                                                    <span class="small text-muted fst-italic">"{{ $item->alasan ?? '-' }}"</span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="text-danger fw-bold small">{{ $item->rejection_reason }}</div>
                                            </td>
                                            <td class="small text-muted">
                                                {{ \App\Models\User::find($item->verified_by)->name ?? '-' }} - {{ \Carbon\Carbon::parse($item->verified_at)->format('d M H:i') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @endif


<script>
    function toggleReason(id, type) {
        const rejectRadio = document.getElementById(type + '_reject_' + id);
        const container = document.getElementById('reason_container_' + type + '_' + id);
        const textarea = container.querySelector('textarea');

        if (rejectRadio.checked) {
            container.style.display = 'block';
            textarea.setAttribute('required', 'required');
            textarea.focus();
        } else {
            container.style.display = 'none';
            textarea.removeAttribute('required');
            textarea.value = ''; // Clean up if they switch back to approve
        }
    }
</script>
@endsection
