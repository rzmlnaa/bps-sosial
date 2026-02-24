@extends('layouts.admin')

@section('title', 'Master Menu')

@section('content')

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
        <div>
            <h2 class="fw-bold mb-1" style="color: var(--bps-orange);">Master Menu</h2>
            <p class="text-muted mb-0">Master Menu Dinamis</p>
        </div>

       <a href="{{ route('admin.dynamic-menus.create') }}" class="btn btn-primary bg-navy border-0 rounded-pill px-4">
            <i class="fas fa-plus me-2"></i>Tambah Menu
        </a>
    </div>
    <div>
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Urutan</th>
                                <th>Nama Menu</th>
                                <th>Tipe</th>
                                <th>Spreadsheet ID</th>
                                <th>Status</th>
                                <th>Dibuat Oleh</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $parents = $menus->whereNull('parent_id');
                            @endphp
                            @forelse($parents as $parent)
                                <!-- Parent Row -->
                                <tr>
                                    <td>{{ $parent->order_number }}</td>
                                    <td class="fw-bold">
                                        {{ $parent->name }}
                                        <br><small class="text-muted fw-normal">{{ $parent->slug }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">Dropdown (Parent)</span>
                                    </td>
                                    <td>
                                        @php
                                            $hasChildren = $menus->where('parent_id', $parent->id)->count() > 0;
                                        @endphp
                                        @if($hasChildren)
                                            <span class="text-muted fst-italic">Tidak digunakan (Menu Utama)</span>
                                        @else
                                            @if($parent->spreadsheet_id)
                                                @if($parent->sheet_mode === 'all')
                                                    <span class="text-truncate d-inline-block" style="max-width: 150px;">{{ $parent->spreadsheet_id }}</span>
                                                    <br><small class="text-muted">Mode: All Sheets</small>
                                                @else
                                                    <span class="text-truncate d-inline-block" style="max-width: 150px;">{{ $parent->spreadsheet_id }}</span>
                                                    <br><small class="text-muted">Mode: {{ ucfirst($parent->sheet_mode ?? 'single') }} | GID: {{ $parent->gid ?? '-' }}</small>
                                                @endif
                                            @else
                                                <span class="badge bg-danger bg-opacity-10 text-danger px-3 py-2 rounded-pill">Belum di set</span>
                                            @endif
                                        @endif
                                    </td>
                                    <td>
                                        @if($parent->is_active)
                                            <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill">Aktif</span>
                                        @else
                                            <span class="badge bg-danger bg-opacity-10 text-danger px-3 py-2 rounded-pill">Tidak Aktif</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                                        <i class="fas fa-user-edit me-1 text-primary"></i>
                                                        {{ $parent->creator->name ?? 'Admin' }}
                                                        <br>
                                                        <small class="text-muted fw-normal" style="font-size: 0.75rem;">
                                                            {{ $parent->created_at->format('d/m/Y H:i') }}
                                                        </small>
                                                    </span>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <a href="{{ route('admin.dynamic-menus.edit', $parent->id) }}" class="btn btn-sm btn-outline-primary rounded-pill">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <form action="{{ route('admin.dynamic-menus.destroy', $parent->id) }}" method="POST" id="delete-form-{{ $parent->id }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill" onclick="confirmDelete({{ $parent->id }})">
                                                    <i class="fas fa-trash"></i> Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Children Rows -->
                                @foreach($menus->where('parent_id', $parent->id) as $child)
                                    <tr class="bg-light bg-opacity-50">
                                        <td>{{ $child->order_number }}</td>
                                        <td style="padding-left: 30px;">
                                            <div class="d-flex align-items-start">
                                                <span class="text-muted me-2">&#x21B3;</span>
                                                <div>
                                                    {{ $child->name }}
                                                    <br><small class="text-muted">{{ $child->slug }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-info text-dark">Submenu (Child)</span>
                                        </td>
                                        <td>
                                            @if($child->sheet_mode === 'all')
                                                @if($child->spreadsheet_id)
                                                    <span class="text-truncate d-inline-block" style="max-width: 150px;">{{ $child->spreadsheet_id }}</span>
                                                    <br><small class="text-muted">Mode: All Sheets</small>
                                                @else
                                                    <span class="text-muted">Mode: All Sheets</span>
                                                @endif
                                            @else
                                                @if($child->spreadsheet_id)
                                                    <span class="text-truncate d-inline-block" style="max-width: 150px;">{{ $child->spreadsheet_id }}</span>
                                                    <br><small class="text-muted">Mode: {{ ucfirst($child->sheet_mode ?? 'single') }} | GID: {{ $child->gid ?? '-' }}</small>
                                                @else
                                                    <span class="badge bg-danger bg-opacity-10 text-danger px-3 py-2 rounded-pill">Belum di set</span>
                                                @endif
                                            @endif
                                        </td>
                                        <td>
                                            @if($child->is_active)
                                                <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill">Aktif</span>
                                            @else
                                                <span class="badge bg-danger bg-opacity-10 text-danger px-3 py-2 rounded-pill">Tidak Aktif</span>
                                            @endif
                                        </td>
                                        <td> <span class="badge bg-light text-dark border">
                                                        <i class="fas fa-user-edit me-1 text-primary"></i>
                                                        {{ $child->creator->name ?? 'Admin' }}
                                                        <br>
                                                        <small class="text-muted fw-normal" style="font-size: 0.75rem;">
                                                            {{ $child->created_at->format('d/m/Y H:i') }}
                                                        </small>
                                                    </span></td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <a href="{{ route('admin.dynamic-menus.edit', $child->id) }}" class="btn btn-sm btn-outline-primary rounded-pill">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                                <form action="{{ route('admin.dynamic-menus.destroy', $child->id) }}" method="POST" id="delete-form-{{ $child->id }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="btn btn-sm btn-outline-danger rounded-pill" onclick="confirmDelete({{ $child->id }})">
                                                        <i class="fas fa-trash"></i> Hapus
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">Belum ada menu dinamis.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function confirmDelete(id) {
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Menu yang dihapus tidak dapat dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + id).submit();
            }
        })
    }
</script>
@endpush