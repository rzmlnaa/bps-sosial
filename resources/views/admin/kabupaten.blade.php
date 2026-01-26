@extends('layouts.admin')

@section('title', 'Master Wilayah')

@section('content')
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold text-navy mb-0">Master Wilayah</h2>
        </div>

        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white py-3 border-bottom-0">
                        <h5 class="fw-bold mb-0 text-navy">Tambah Wilayah</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.kabupaten.store') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label text-muted small fw-bold text-uppercase">Kode Kabupaten</label>
                                <input type="text" name="kode_kab" class="form-control" placeholder="Contoh: 6101" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted small fw-bold text-uppercase">Nama
                                    Kabupaten/Kota</label>
                                <input type="text" name="nama_kabupaten" class="form-control"
                                    placeholder="Contoh: Kab. Sambas" required>
                            </div>
                            <button type="submit" class="btn btn-primary bg-navy w-100 fw-medium border-0 rounded-3">
                                <i class="fas fa-plus me-1"></i> Simpan
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white py-3 border-bottom-0">
                        <h5 class="fw-bold mb-0 text-navy">Daftar Wilayah</h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4 border-0">No</th>
                                    <th class="border-0">Kode</th>
                                    <th class="border-0">Nama Kabupaten</th>
                                    <th class="border-0">Info</th>
                                    <th class="text-center border-0">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="border-top-0">
                                @forelse($kabupatens as $index => $kab)
                                    <tr>
                                        <td class="ps-4 text-muted">{{ $index + 1 }}</td>
                                        <td class="fw-bold">{{ $kab->kode_kab }}</td>
                                        <td class="fw-medium">{{ $kab->nama_kabupaten }}</td>
                                        <td>
                                            <div class="d-flex flex-column gap-1">
                                                <small class="text-muted" style="font-size: 0.75rem;">
                                                    <i class="fas fa-user-plus me-1"></i> {{ $kab->userAdd->name ?? 'Admin' }}
                                                </small>
                                                @if($kab->userUpdate)
                                                    <small class="text-muted" style="font-size: 0.75rem;">
                                                        <i class="fas fa-user-edit me-1"></i> {{ $kab->userUpdate->name }}
                                                    </small>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <button
                                                class="btn btn-sm btn-outline-warning border-0 btn-edit-kabupaten rounded-circle"
                                                data-id="{{ $kab->id }}" data-nama="{{ $kab->nama_kabupaten }}"
                                                data-kode="{{ $kab->kode_kab }}">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form action="{{ route('admin.kabupaten.destroy', $kab->id) }}" method="POST"
                                                class="d-inline form-delete">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button"
                                                    class="btn btn-sm btn-outline-danger border-0 btn-delete rounded-circle">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">Belum ada data wilayah.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Edit Kabupaten -->
    <div class="modal fade" id="modalEditKabupaten" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow rounded-4">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold text-navy">Edit Nama Kabupaten</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formEditKabupaten" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold text-uppercase">Kode Kabupaten</label>
                            <input type="text" name="kode_kab" id="edit_kode_kab" class="form-control"
                                placeholder="Contoh: 6101" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold text-uppercase">Nama Kabupaten/Kota</label>
                            <input type="text" name="nama_kabupaten" id="edit_nama_kabupaten" class="form-control"
                                placeholder="Contoh: Kab. Sambas" required>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary bg-navy border-0 rounded-3">
                                <i class="fas fa-save me-1"></i> Simpan Perubahan
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                // Edit Kabupaten Modal Logic
                const editButtons = document.querySelectorAll('.btn-edit-kabupaten');
                const modalEdit = new bootstrap.Modal(document.getElementById('modalEditKabupaten'));
                const formEdit = document.getElementById('formEditKabupaten');

                editButtons.forEach(button => {
                    button.addEventListener('click', function () {
                        const id = this.getAttribute('data-id');
                        const nama = this.getAttribute('data-nama');
                        const kode = this.getAttribute('data-kode');

                        document.getElementById('edit_nama_kabupaten').value = nama;
                        document.getElementById('edit_kode_kab').value = kode;
                        formEdit.action = `/admin/kabupaten/${id}`;

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
@endsection