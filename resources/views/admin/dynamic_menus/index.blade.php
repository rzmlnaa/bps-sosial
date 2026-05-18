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
                                <th>Link / Konten</th>
                                <th>Status</th>
                                <th>Dibuat Oleh</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                            @php
                                $parents = $menus->whereNull('parent_id');
                            @endphp
                            @forelse($parents as $parent)
                                <tbody class="sortable-tbody" data-id="{{ $parent->id }}">
                                <!-- Parent Row -->
                                <tr class="bg-white parent-row">
                                    <td class="order-number">
                                        <i class="fas fa-grip-vertical text-muted me-2 drag-handle-parent" style="cursor: grab;" title="Geser untuk mengubah urutan"></i>
                                        <span class="number-display">{{ $parent->order_number }}</span>
                                    </td>
                                    <td class="fw-bold">
                                        {{ $parent->name }}
                                        <br><small class="text-muted fw-normal">{{ $parent->slug }}</small>
                                    </td>
                                    <td>
                                        @php
                                            $icon = 'fas fa-link';
                                            $color = 'secondary';
                                            $typeLabel = ucfirst($parent->type ?? 'External');
                                            if ($parent->type === 'spreadsheet') { $icon = 'fas fa-file-excel'; $color = 'success'; }
                                            elseif ($parent->type === 'youtube') { $icon = 'fab fa-youtube'; $color = 'danger'; }
                                            elseif ($parent->type === 'drive') { $icon = 'fab fa-google-drive'; $color = 'primary'; }
                                            elseif ($parent->type === 'external') { $icon = 'fas fa-external-link-alt'; $color = 'secondary'; }
                                            elseif ($parent->type === 'main_menu') { $icon = 'fas fa-bars'; $color = 'secondary'; }

                                            $hasChildren = $menus->where('parent_id', $parent->id)->count() > 0;
                                        @endphp

                                        @if($hasChildren)
                                            <span class="badge bg-info bg-opacity-10 text-info px-3 py-2 rounded-pill">
                                                <i class="fas fa-folder me-1"></i> Parent (Dropdown)
                                            </span>
                                        @else
                                            <span class="badge bg-{{ $color }} bg-opacity-10 text-{{ $color }} px-3 py-2 rounded-pill">
                                                <i class="{{ $icon }} me-1"></i> {{ $typeLabel }}
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($hasChildren)
                                            <span class="text-muted fst-italic">Menu Utama (Dropdown)</span>
                                        @else
                                            @if($parent->url)
                                                <div class="text-truncate d-inline-block" style="max-width: 200px;" title="{{ $parent->url }}">
                                                    <a href="{{ $parent->url }}" target="_blank" class="text-decoration-none">
                                                        <i class="fas fa-external-link-alt fa-xs me-1"></i>{{ $parent->url }}
                                                    </a>
                                                </div>
                                                @if($parent->type === 'spreadsheet')
                                                    @php $meta = $parent->meta; @endphp
                                                    <br><small class="text-muted">
                                                        Mode: {{ ucfirst($meta['sheet_mode'] ?? 'all') }}
                                                        {{ ($meta['sheet_mode'] ?? '') === 'single' ? '| GID: ' . ($meta['gid'] ?? '-') : '' }}
                                                    </small>
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
                                    <tr class="bg-light bg-opacity-50 child-row" data-child-id="{{ $child->id }}">
                                        <td class="ps-4">
                                            <i class="fas fa-grip-vertical text-muted me-2 drag-handle-child" style="cursor: grab;" title="Geser untuk mengubah urutan sub-menu"></i>
                                            <span class="number-display-child">{{ $child->order_number }}</span>
                                        </td>
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
                                            @php
                                                $icon = 'fas fa-link';
                                                $color = 'secondary';
                                                $typeLabel = ucfirst($child->type ?? 'External');
                                                if ($child->type === 'spreadsheet') { $icon = 'fas fa-file-excel'; $color = 'success'; }
                                                elseif ($child->type === 'youtube') { $icon = 'fab fa-youtube'; $color = 'danger'; }
                                                elseif ($child->type === 'drive') { $icon = 'fab fa-google-drive'; $color = 'primary'; }
                                            @endphp
                                            <span class="badge bg-{{ $color }} bg-opacity-10 text-{{ $color }} px-3 py-2 rounded-pill">
                                                <i class="{{ $icon }} me-1"></i> {{ $typeLabel }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($child->url)
                                                <div class="text-truncate d-inline-block" style="max-width: 200px;" title="{{ $child->url }}">
                                                    <a href="{{ $child->url }}" target="_blank" class="text-decoration-none">
                                                        <i class="fas fa-external-link-alt fa-xs me-1"></i>{{ $child->url }}
                                                    </a>
                                                </div>
                                                @if($child->type === 'spreadsheet')
                                                    @php $meta = $child->meta; @endphp
                                                    <br><small class="text-muted">
                                                        Mode: {{ ucfirst($meta['sheet_mode'] ?? 'all') }}
                                                        {{ ($meta['sheet_mode'] ?? '') === 'single' ? '| GID: ' . ($meta['gid'] ?? '-') : '' }}
                                                    </small>
                                                @endif
                                            @else
                                                <span class="badge bg-danger bg-opacity-10 text-danger px-3 py-2 rounded-pill">Belum di set</span>
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
                                </tbody>
                            @empty
                                <tbody>
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">Belum ada menu dinamis.</td>
                                </tr>
                                </tbody>
                            @endforelse
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
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

    document.addEventListener('DOMContentLoaded', function () {
        var el = document.querySelector('.table');
        if (el) {
            Sortable.create(el, {
                animation: 150,
                handle: '.drag-handle-parent',
                draggable: '.sortable-tbody',
                onEnd: function (evt) {
                    var order = [];
                    var orderNumber = 1;
                    
                    document.querySelectorAll('.sortable-tbody').forEach(function(tbody) {
                        var id = tbody.getAttribute('data-id');
                        if (id) {
                            order.push({
                                id: id,
                                order_number: orderNumber
                            });
                            tbody.querySelector('.number-display').innerText = orderNumber;
                            orderNumber++;
                        }
                    });

                    if (order.length > 0) {
                        fetch('/admin/dynamic-menus/reorder', {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ order: order })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    toast: true,
                                    position: 'top-end',
                                    icon: 'success',
                                    title: 'Urutan menu berhasil diperbarui',
                                    showConfirmButton: false,
                                    timer: 3000
                                });
                            } else {
                                Swal.fire('Error!', 'Gagal memperbarui urutan.', 'error');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire('Error!', 'Terjadi kesalahan sistem.', 'error');
                        });
                    }
                }
            });
        }

        // Initialize SortableJS for each parent's sub-menus
        document.querySelectorAll('.sortable-tbody').forEach(function(tbody) {
            Sortable.create(tbody, {
                animation: 150,
                handle: '.drag-handle-child',
                draggable: '.child-row',
                filter: '.parent-row',
                onEnd: function (evt) {
                    var order = [];
                    var orderNumber = 1;
                    
                    tbody.querySelectorAll('.child-row').forEach(function(row) {
                        var id = row.getAttribute('data-child-id');
                        if (id) {
                            order.push({
                                id: id,
                                order_number: orderNumber
                            });
                            row.querySelector('.number-display-child').innerText = orderNumber;
                            orderNumber++;
                        }
                    });

                    if (order.length > 0) {
                        fetch('/admin/dynamic-menus/reorder', {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ order: order })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    toast: true,
                                    position: 'top-end',
                                    icon: 'success',
                                    title: 'Urutan sub-menu berhasil diperbarui',
                                    showConfirmButton: false,
                                    timer: 3000
                                });
                            } else {
                                Swal.fire('Error!', 'Gagal memperbarui urutan sub-menu.', 'error');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire('Error!', 'Terjadi kesalahan sistem.', 'error');
                        });
                    }
                }
            });
        });
    });
</script>
@endpush