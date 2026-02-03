@extends('layouts.admin')

@section('title', 'Input Data Seruti')

@section('content')
    <div class="fade-in-up">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-1" style="color: var(--bps-orange);">Input Data Seruti</h2>
                <p class="text-muted mb-0">Kelola master data coicop dan input data konsumsi</p>
            </div>
            <a href="{{ route('seruti.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Kembali
            </a>
        </div>

        <!-- Tabs/Navigation -->
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; overflow: hidden;">
            <div class="card-header bg-white p-2">
                <ul class="nav nav-pills card-header-pills" id="serutiTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-bold" id="master-tab" data-bs-toggle="tab" data-bs-target="#master" type="button" role="tab" aria-controls="master" aria-selected="true" style="border-radius: 8px;">
                            <i class="fas fa-tags me-2"></i>Master Kelompok
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold" id="input-tab" data-bs-toggle="tab" data-bs-target="#input" type="button" role="tab" aria-controls="input" aria-selected="false" style="border-radius: 8px;">
                            <i class="fas fa-edit me-2"></i>Input Data
                        </button>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Tab Content -->
        <div class="tab-content" id="serutiTabContent">

            <!-- Tab 1: Master Kelompok -->
            <div class="tab-pane fade show active" id="master" role="tabpanel" aria-labelledby="master-tab">
                <div class="row g-4">
                    <!-- Left: Paste Area -->
                    <div class="col-lg-12">
                         <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                            <div class="card-body p-4">
                                <h5 class="fw-bold mb-3">Copy-Paste dari Excel</h5>
                                <p class="text-muted small">
                                    Salin data dari Excel dengan kolom (Kode | COICOP | SERUTI) lalu tempel di bawah ini.
                                </p>
                                <div class="form-group mb-3">
                                    <textarea id="pasteArea" class="form-control" rows="8" placeholder="Paste data Excel di sini..." style="font-family: monospace; font-size: 0.9rem;"></textarea>
                                </div>
                                <div class="d-flex justify-content-end gap-2">
                                    <button class="btn btn-secondary" id="clearBtn">Clear</button>
                                    <button class="btn btn-primary" id="previewBtn">Preview Data</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Preview & Existing Data -->
                    <div class="col-lg-12">
                         <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                             <div class="card-header bg-white py-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="fw-bold mb-0">Data Kelompok COICOP</h5>
                                    <button class="btn btn-success d-none" id="saveBtn">
                                        <i class="fas fa-save me-2"></i>Simpan Perubahan
                                    </button>
                                </div>
                             </div>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0" id="coicopTable">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="ps-4" style="width: 100px;">Kode</th>
                                            <th>Nama COICOP</th>
                                            <th>SERUTI</th>
                                            <th class="text-center">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody id="coicopTableBody">
                                        @forelse($coicops as $c)
                                            <tr>
                                                <td class="ps-4 fw-bold">{{ $c->kode }}</td>
                                                <td>{{ $c->nama }}</td>
                                                <td><span class="badge bg-info text-dark">{{ $c->seruti }}</span></td>
                                                <td class="text-center"><span class="badge bg-secondary">Tersimpan</span></td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center py-4 text-muted">Belum ada data. Silakan input di atas.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                         </div>
                    </div>
                </div>
            </div>

            <!-- Tab 2: Input Data (Placeholder) -->
            <div class="tab-pane fade" id="input" role="tabpanel" aria-labelledby="input-tab">
                <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                    <div class="card-body p-5 text-center">
                        <i class="fas fa-tools fa-3x text-muted mb-3"></i>
                        <h5>Fitur Input Data Konsumsi</h5>
                        <p class="text-muted">Akan segera tersedia.</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const pasteArea = document.getElementById('pasteArea');
            const previewBtn = document.getElementById('previewBtn');
            const clearBtn = document.getElementById('clearBtn');
            const saveBtn = document.getElementById('saveBtn');
            const tableBody = document.getElementById('coicopTableBody');
            let parsedData = [];

            clearBtn.addEventListener('click', () => {
                 pasteArea.value = '';
                 parsedData = [];
                 saveBtn.classList.add('d-none');
                 // Optionally reload page to reset table, but simple clear is ok
            });

            previewBtn.addEventListener('click', () => {
                const rawText = pasteArea.value.trim();
                if (!rawText) {
                    Swal.fire('Info', 'Silakan paste data terlebih dahulu', 'info');
                    return;
                }

                const rows = rawText.split('\n');
                const newRows = [];

                rows.forEach((row, index) => {
                    // Split by tab or multiple spaces (basic heuristic)
                    // Excel copy usually uses tabs (\t)
                    const cols = row.split('\t');

                    // Expect at least 2 columns: Kode, Nama. Seruti is optional?
                    // Based on image: Kode | COICOP | SERUTI (3 cols)
                    if (cols.length >= 2) {
                        const kode = cols[0].trim();
                        const nama = cols[1].trim();
                        const seruti = cols[2] ? cols[2].trim() : '';

                        if (kode && nama) {
                             newRows.push({ kode, nama, seruti });
                        }
                    }
                });

                if (newRows.length === 0) {
                     Swal.fire('Error', 'Format data tidak dikenali. Pastikan copy-paste dari Excel dengan benar.', 'error');
                     return;
                }

                parsedData = newRows;
                renderPreview(newRows);
                saveBtn.classList.remove('d-none');
                Swal.fire('Sukses', `${newRows.length} baris data dikenali. Klik Simpan untuk memproses.`, 'success');
            });

            function renderPreview(data) {
                let html = '';
                data.forEach(item => {
                    html += `
                        <tr class="table-warning">
                            <td class="ps-4 fw-bold">${item.kode}</td>
                            <td>${item.nama}</td>
                            <td>${item.seruti}</td>
                            <td class="text-center"><span class="badge bg-warning text-dark">Preview (Belum Disimpan)</span></td>
                        </tr>
                    `;
                });
                // Prepend to existing or replace? Replace for clear preview
                tableBody.innerHTML = html;
            }

            saveBtn.addEventListener('click', function() {
                if (parsedData.length === 0) return;

                Swal.fire({
                    title: 'Simpan Data?',
                    text: "Data akan disimpan ke database (Update/Insert based on Kode).",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, Simpan!'
                }).then((result) => {
                    if (result.isConfirmed) {
                         // Send to server
                         fetch('{{ route("seruti.store-coicop") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ coicops: parsedData })
                         })
                         .then(response => response.json())
                         .then(data => {
                            if (data.success) {
                                Swal.fire('Berhasil!', data.message, 'success').then(() => {
                                    window.location.reload();
                                });
                            } else {
                                Swal.fire('Gagal', data.message, 'error');
                            }
                         })
                         .catch(err => {
                            console.error(err);
                            Swal.fire('Error', 'Terjadi kesalahan sistem.', 'error');
                         });
                    }
                });
            });
        });
    </script>
    <style>
        .nav-pills .nav-link.active {
            background-color: var(--bps-blue);
        }
        .nav-pills .nav-link {
            color: #64748b;
        }
    </style>
@endpush