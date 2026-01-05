@extends('layouts.admin')

@section('title', 'Rentang Harga - BPS Kalbar')

@section('content')
    <div class="fade-in-up">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
            <div>
                <h2 class="fw-bold mb-1" style="color: var(--bps-orange);">Rentang Harga</h2>
                <p class="text-muted mb-0">Kelola master kategori dan data rentang harga komoditas</p>
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
                <button class="nav-link rounded-pill px-4" id="pills-harga-tab" data-bs-toggle="pill"
                    data-bs-target="#pills-harga" type="button" role="tab" disabled>
                    <i class="fas fa-money-bill-wave me-2"></i>Data Harga (Coming Soon)
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
                                        <label class="form-label text-muted small fw-bold text-uppercase">Nama
                                            Kategori</label>
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
                                                    <form action="{{ route('kategori-komoditas.destroy', $item->id) }}"
                                                        method="POST" class="d-inline form-delete">
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
                                                <td colspan="5" class="text-center py-5 text-muted">
                                                    <i class="fas fa-folder-open mb-2 d-block" style="font-size: 2rem;"></i>
                                                    Belum ada data kategori.
                                                </td>
                                            </tr>
                                        @endforelse
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
        });
    </script>
@endpush