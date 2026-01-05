@extends('layouts.admin')

@section('title', 'Input Rentang Harga - BPS Kalbar')

@section('content')
    <div class="fade-in-up">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
            <div>
                <h2 class="fw-bold mb-1" style="color: var(--bps-orange);">Input Data Komoditas</h2>
                <p class="text-muted mb-0">Kelola master kategori dan data rentang harga komoditas</p>
            </div>
            <div class="mt-3 mt-md-0">
                <a href="{{ route('price-range.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Kembali
                </a>
            </div>
        </div>

        <!-- Tabs Navigation -->
        <ul class="nav nav-pills mb-4" id="pills-tab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active rounded-pill px-4" id="pills-kategori-tab" data-bs-toggle="pill"
                    data-bs-target="#pills-kategori" type="button" role="tab">
                    <i class="fas fa-tags me-2"></i>Master Kategori
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-pill px-4" id="pills-input-tab" data-bs-toggle="pill"
                    data-bs-target="#pills-input" type="button" role="tab">
                    <i class="fas fa-edit me-2"></i>Input Harga
                </button>
            </li>
        </ul>

        <div class="tab-content" id="pills-tabContent">
            <!-- Tab 1: Master Kategori -->
            <div class="tab-pane fade show active" id="pills-kategori" role="tabpanel">
                <div class="row">
                    <div class="col-md-4 mb-4">
                        <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                            <div class="card-header bg-white py-3 border-bottom-0">
                                <h5 class="fw-bold mb-0">Tambah Kategori</h5>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('kategori-komoditas.store') }}" method="POST">
                                    @csrf
                                    <div class="mb-3">
                                        <label class="form-label text-muted small fw-bold text-uppercase">Nama Kategori</label>
                                        <input type="text" name="nama_kategori" class="form-control"
                                            placeholder="Contoh: PADI-PADIAN" required>
                                    </div>
                                    <button type="submit" class="btn text-white w-100 fw-medium"
                                        style="background-color: var(--bps-blue);">
                                        <i class="fas fa-plus me-1"></i> Simpan
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                            <div class="card-header bg-white py-3 border-bottom-0">
                                <h5 class="fw-bold mb-0">Daftar Kategori Komoditas</h5>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="ps-4 border-0" style="width: 60px;">No</th>
                                            <th class="border-0">Nama Kategori</th>
                                            <th class="border-0">Dibuat Oleh</th>
                                            <th class="border-0">Di Update Oleh</th>
                                            <th class="text-center border-0">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="border-top-0">
                                        @forelse($kategori as $index => $item)
                                            <tr>
                                                <td class="ps-4 text-muted">{{ $index + 1 }}</td>
                                                <td class="fw-medium text-uppercase">{{ $item->nama_kategori }}</td>
                                                <td>
                                                    <span class="badge bg-light text-dark border">
                                                        <i class="fas fa-user-edit me-1 text-primary"></i>
                                                        {{ $item->userAdd->name ?? 'Admin' }}
                                                        <br>
                                                        <small class="text-muted fw-normal" style="font-size: 0.75rem;">
                                                            {{ $item->created_at->format('d/m/Y H:i') }}
                                                        </small>
                                                    </span>
                                                </td>
                                                <td>
                                                    @if($item->userUpdate)
                                                        <span class="badge bg-light text-dark border">
                                                            <i class="fas fa-user-check me-1 text-success"></i>
                                                            {{ $item->userUpdate->name }}
                                                            <br>
                                                            <small class="text-muted fw-normal" style="font-size: 0.75rem;">
                                                                {{ $item->updated_at->format('d/m/Y H:i') }}
                                                            </small>
                                                        </span>
                                                    @else
                                                        <span class="text-muted small">Belum pernah diupdate</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    <button class="btn btn-sm btn-outline-warning border-0 btn-edit-kategori"
                                                        data-id="{{ $item->id }}" data-nama="{{ $item->nama_kategori }}">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <form action="{{ route('kategori-komoditas.destroy', $item->id) }}" method="POST"
                                                        class="d-inline form-delete">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button"
                                                            class="btn btn-sm btn-outline-danger border-0 btn-delete">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center py-5 text-muted"> Belum ada data kategori.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 2: Master Komoditas (Turunan) -->
            <div class="tab-pane fade" id="pills-input" role="tabpanel">
                <div class="row g-4">
                    <!-- Left Column: Input Form -->
                    <div class="col-lg-4">
                        <form action="{{ route('komoditas.store') }}" method="POST">
                            @csrf
                            <!-- Filters -->
                            <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                                <div class="card-body p-4">
                                    <h6 class="fw-bold mb-3 text-uppercase text-muted"
                                        style="font-size: 0.8rem; letter-spacing: 0.5px;">1. Pilih Kategori</h6>
                                    <div class="mb-0">
                                        <label class="form-label small fw-medium">Kategori Utama</label>
                                        <select id="select_kategori_filter" name="kategori_id"
                                            class="form-select bg-light border-0" required>
                                            <option selected disabled value="">-- Pilih Kategori --</option>
                                            @foreach($kategori as $kat)
                                                <option value="{{ $kat->id }}" {{ session('last_kategori_id') == $kat->id ? 'selected' : '' }}>{{ $kat->nama_kategori }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Input Area -->
                            <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                                <div class="card-header bg-white py-3 border-bottom-0">
                                    <h6 class="fw-bold mb-0 text-uppercase text-muted"
                                        style="font-size: 0.8rem; letter-spacing: 0.5px;">2. Paste Nama Komoditas & Satuan</h6>
                                </div>
                                <div class="card-body p-0">
                                    <div class="p-3">
                                        <textarea id="textarea_komoditas" name="raw_data"
                                            class="form-control fw-mono border-0 bg-light" rows="15"
                                            placeholder="Paste dari Excel (Nama [tab] Satuan)...&#10;Contoh:&#10;Beras lokal	Kg&#10;Jagung basah	Kg&#10;Tepung terigu	Kg"
                                            style="font-family: 'Inter', sans-serif; font-size: 0.9rem; resize: none;"
                                            required></textarea>
                                    </div>
                                </div>
                                <div class="card-footer bg-white border-top-0 py-3">
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary flex-grow-1 fw-bold py-2 shadow-sm"
                                            style="background-color: var(--bps-orange); border: none;">
                                            <i class="fas fa-save me-2"></i>Simpan Data
                                        </button>
                                        <button type="button" id="btn_clear_komoditas"
                                            class="btn btn-outline-danger fw-bold py-2 shadow-sm" style="display: none;">
                                            <i class="fas fa-trash me-2"></i>Hapus Semua
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Right Column: Data Table -->
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
                            <div
                                class="card-header bg-white py-3 border-bottom-0 d-flex justify-content-between align-items-center">
                                <h5 class="fw-bold mb-0" id="komoditas_table_title">Daftar Komoditas</h5>
                            </div>
                            <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light sticky-top" style="z-index: 1;">
                                        <tr>
                                            <th class="ps-4 border-0" style="width: 60px;">No</th>
                                            <th class="border-0">Nama Komoditas</th>
                                            <th class="border-0 text-center">Satuan</th>
                                            <th class="border-0">Admin</th>
                                            <th class="text-center border-0">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="border-top-0" id="table_body_komoditas">
                                        <tr>
                                            <td colspan="5" class="text-center py-5 text-muted">
                                                <i class="fas fa-info-circle me-1"></i> Silakan pilih kategori untuk menampilkan daftar komoditas.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Edit Kategori -->
    <div class="modal fade" id="modalEditKategori" tabindex="-1" aria-labelledby="modalEditKategoriLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom-0">
                    <h5 class="modal-title fw-bold" id="modalEditKategoriLabel">Edit Nama Kategori</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formEditKategori" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold text-uppercase">Nama Kategori</label>
                            <input type="text" name="nama_kategori" id="edit_nama_kategori" class="form-control"
                                placeholder="Contoh: PADI-PADIAN" required>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0">
                        <button type="button" class="btn btn-light fw-medium" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn text-white fw-medium" style="background-color: var(--bps-orange);">
                            <i class="fas fa-save me-1"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        .nav-pills .nav-link {
            color: #64748b;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .nav-pills .nav-link.active {
            background-color: var(--bps-blue);
            color: white;
            box-shadow: 0 4px 6px -1px rgba(0, 147, 221, 0.3);
        }

        .badge {
            text-align: left;
            line-height: 1.4;
            padding: 0.5rem 0.75rem;
        }

        .bg-blue-faded {
            background-color: rgba(0, 147, 221, 0.1);
        }

        .text-blue {
            color: var(--bps-blue);
        }
    </style>
@endsection

@push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // Edit Kategori Modal Logic
            const editButtons = document.querySelectorAll('.btn-edit-kategori');
            const modalEdit = new bootstrap.Modal(document.getElementById('modalEditKategori'));
            const formEdit = document.getElementById('formEditKategori');
            const inputEditNama = document.getElementById('edit_nama_kategori');

            editButtons.forEach(button => {
                button.addEventListener('click', function () {
                    const id = this.getAttribute('data-id');
                    const nama = this.getAttribute('data-nama');

                    inputEditNama.value = nama;
                    formEdit.action = `/kategori-komoditas/${id}`;

                    modalEdit.show();
                });
            });

            // Delete Confirmation Logic
            document.addEventListener('click', function (event) {
                if (event.target.closest('.btn-delete')) {
                    const button = event.target.closest('.btn-delete');
                    const form = button.closest('.form-delete');

                    Swal.fire({
                        title: 'Apakah Anda yakin?',
                        text: "Data yang dihapus tidak dapat dikembalikan!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Ya, hapus!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                }
            });

            // Master Komoditas Logic
            const selectKategori = document.getElementById('select_kategori_filter');
            const tableBodyKomoditas = document.getElementById('table_body_komoditas');
            const komoditasTableTitle = document.getElementById('komoditas_table_title');
            const btnClearKomoditas = document.getElementById('btn_clear_komoditas');
            const textareaKomoditas = document.getElementById('textarea_komoditas');

            if (selectKategori) {
                selectKategori.addEventListener('change', function() {
                    const kategoriId = this.value;
                    const kategoriName = this.options[this.selectedIndex].text;
                    komoditasTableTitle.innerText = `Daftar Komoditas - ${kategoriName}`;
                    
                    // Show Loading
                    tableBodyKomoditas.innerHTML = `<tr><td colspan="5" class="text-center py-5"><div class="spinner-border text-primary" role="status"></div></td></tr>`;
                    
                    fetch(`/komoditas/get-by-category/${kategoriId}`)
                        .then(response => response.json())
                        .then(data => {
                            let html = '';
                            if (data.length === 0) {
                                html = `<tr><td colspan="5" class="text-center py-5 text-muted">Belum ada data komoditas untuk kategori ini.</td></tr>`;
                                btnClearKomoditas.style.display = 'none';
                                textareaKomoditas.value = '';
                            } else {
                                data.forEach((item, index) => {
                                    html += `
                                        <tr>
                                            <td class="ps-4 text-muted">${index + 1}</td>
                                            <td class="fw-medium">${item.nama_komoditas}</td>
                                            <td class="text-center"><span class="badge bg-blue-faded text-blue border">${item.satuan || '-'}</span></td>
                                            <td>
                                                <small class="text-muted d-block">${item.user_add?.name || 'Admin'}</small>
                                                <small class="text-xs text-muted" style="font-size: 0.7rem;">${new Date(item.created_at).toLocaleString('id-ID')}</small>
                                            </td>
                                            <td class="text-center">
                                                <form action="/komoditas/${item.id}" method="POST" class="form-delete d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="btn btn-sm btn-outline-danger border-0 btn-delete">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    `;
                                });
                                btnClearKomoditas.style.display = 'block';
                                // Also fill textarea for easy editing (per line: Name [tab] Satuan)
                                textareaKomoditas.value = data.map(i => `${i.nama_komoditas}\t${i.satuan}`).join('\n');
                            }
                            tableBodyKomoditas.innerHTML = html;
                        });
                });

                // Trigger if already selected
                if (selectKategori.value) {
                    selectKategori.dispatchEvent(new Event('change'));
                }
            }

            if (btnClearKomoditas) {
                btnClearKomoditas.addEventListener('click', function() {
                    const kategoriName = selectKategori.options[selectKategori.selectedIndex].text;
                    Swal.fire({
                        title: 'Kosongkan Data?',
                        html: `Hapus semua komoditas dalam kategori <b>${kategoriName}</b>?`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        confirmButtonText: 'Ya, hapus semua!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            const form = document.createElement('form');
                            form.method = 'POST';
                            form.action = "{{ route('komoditas.clear') }}";
                            
                            const csrf = document.createElement('input');
                            csrf.type = 'hidden';
                            csrf.name = '_token';
                            csrf.value = "{{ csrf_token() }}";
                            
                            const method = document.createElement('input');
                            method.type = 'hidden';
                            method.name = '_method';
                            method.value = "DELETE";
                            
                            const katId = document.createElement('input');
                            katId.type = 'hidden';
                            katId.name = 'kategori_id';
                            katId.value = selectKategori.value;
                            
                            form.appendChild(csrf);
                            form.appendChild(method);
                            form.appendChild(katId);
                            document.body.appendChild(form);
                            form.submit();
                        }
                    });
                });
            }
        });
    </script>
@endpush
