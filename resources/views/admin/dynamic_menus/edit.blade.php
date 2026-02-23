@extends('layouts.admin')

@section('title', 'Edit Menu')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
        <div>
            <h2 class="fw-bold mb-1" style="color: var(--bps-orange);">Edit Menu: {{ $dynamicMenu->name }}</h2>
            <p class="text-muted mb-0">Edit Menu</p>
        </div>

        <a href="{{ route('admin.dynamic-menus.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>

    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-4">
            <form action="{{ route('admin.dynamic-menus.update', $dynamicMenu->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label class="form-label fw-medium">Nama Menu <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required
                            value="{{ old('name', $dynamicMenu->name) }}">
                        <input type="hidden" name="slug" required value="{{ old('slug', $dynamicMenu->slug) }}">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-medium">Parent Menu</label>
                        <select name="parent_id" class="form-select">
                            <option value="">-- Jadikan Menu Utama (Dropdown) --</option>
                            @foreach($parents as $parent)
                                <option value="{{ $parent->id }}" {{ old('parent_id', $dynamicMenu->parent_id) == $parent->id ? 'selected' : '' }}>{{ $parent->name }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted">Pilih parent jika ini adalah submenu. Jika kosong, akan menjadi menu
                            utama.</small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-medium">Urutan (Order Number) <span class="text-danger">*</span></label>
                        <input type="number" name="order_number" class="form-control" required min="0"
                            value="{{ old('order_number', $dynamicMenu->order_number) }}">
                        <small class="text-muted">Angka lebih kecil tampil lebih atas.</small>
                    </div>
                </div>

                <hr class="my-4">
                <h5 class="fw-bold mb-3">Pengaturan Spreadsheet</h5>

                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label class="form-label fw-medium text-primary">Atau Paste Link Google Spreadsheet
                            Lengkap</label>
                        <input type="text" id="spreadsheet_url" class="form-control border-primary"
                            placeholder="Contoh: https://docs.google.com/spreadsheets/d/1k6.../edit#gid=123">
                        <small class="text-primary">System akan otomatis mengisi ID dan GID di bawah jika Anda mempaste
                            link baru.</small>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label class="form-label fw-medium">Google Spreadsheet ID</label>
                        <input type="text" name="spreadsheet_id" class="form-control bg-light"
                            value="{{ old('spreadsheet_id', $dynamicMenu->spreadsheet_id) }}">
                        <small class="text-muted">Ambil ID dari URL Google Spreadsheet.</small>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-medium">Sheet Mode</label>
                        <select name="sheet_mode" class="form-select">
                            <option value="">-- Pilih Mode --</option>
                            <option value="single" {{ old('sheet_mode', $dynamicMenu->sheet_mode) == 'single' ? 'selected' : '' }}>Satu Sheet Saja (Butuh GID)</option>
                            <option value="all" {{ old('sheet_mode', $dynamicMenu->sheet_mode) == 'all' ? 'selected' : '' }}>
                                Semua Sheet (Tampil Tabs Bawah)</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-medium">GID (Khusus mode Single)</label>
                        <input type="text" name="gid" class="form-control bg-light"
                            value="{{ old('gid', $dynamicMenu->gid) }}">
                        <small class="text-muted">Kosongkan jika mode 'all'.</small>
                    </div>
                </div>

                <div class="mb-4 form-check form-switch mt-3">
                    <input class="form-check-input" type="checkbox" role="switch" id="isActive" name="is_active" {{ old('is_active', $dynamicMenu->is_active) ? 'checked' : '' }}>
                    <label class="form-check-label ms-2 fw-medium" for="isActive">Aktifkan Menu Ini</label>
                </div>

                <hr class="my-4">
                <h5 class="fw-bold mb-3">Live Preview Google Spreadsheet</h5>
                <div class="mb-4">
                    <div class="card bg-light border-0 rounded-4 overflow-hidden" style="height: 400px; display: none;"
                        id="previewContainer">
                        <div class="card-body p-0 h-100">
                            <iframe id="previewIframe" src="" width="100%" height="100%" style="border:0;"
                                title="Preview"></iframe>
                        </div>
                    </div>
                    <div class="text-center p-5 text-muted bg-light rounded-4 border"
                        style="border-style: dashed !important;" id="noPreviewMessage">
                        <i class="fas fa-file-excel fa-3x mb-3 opacity-50"></i>
                        <p class="mb-0">Isi ID Spreadsheet untuk melihat preview</p>
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <button type="submit" class="btn btn-primary bg-navy py-2 px-5 rounded-pill border-0">
                        <i class="fas fa-save me-2"></i>Perbarui Menu
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const nameInput = document.querySelector('input[name="name"]');
            const slugInput = document.querySelector('input[name="slug"]');
            let initialSlug = slugInput.value;

            nameInput.addEventListener('input', function () {
                let slug = nameInput.value
                    .toLowerCase()
                    .replace(/[^\w\s-]/g, '') // Remove invalid chars
                    .replace(/[\s_-]+/g, '-') // Swap whitespace, underscores, dashes with a single dash
                    .replace(/^-+|-+$/g, ''); // Trim dashes from start and end

                slugInput.value = slug;
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const urlInput = document.getElementById('spreadsheet_url');
            const idInput = document.querySelector('input[name="spreadsheet_id"]');
            const gidInput = document.querySelector('input[name="gid"]');
            const modeSelect = document.querySelector('select[name="sheet_mode"]');

            urlInput.addEventListener('input', function () {
                const url = this.value;

                // Extract Spreadsheet ID
                const idMatch = url.match(/\/d\/(.*?)(?:\/|$)/);
                if (idMatch && idMatch[1]) {
                    idInput.value = idMatch[1];
                } else if (url === '') {
                    // don't clear on empty, maybe they just deleted the url input
                }

                // Extract GID
                const gidMatch = url.match(/[#&?]gid=([0-9]+)/);
                if (gidMatch && gidMatch[1]) {
                    gidInput.value = gidMatch[1];
                    modeSelect.value = 'single';
                } else if (url !== '') {  // Only change to 'all' if they actually pasted something but it has no GID
                    gidInput.value = '';
                    modeSelect.value = 'all';
                }
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const idInput = document.querySelector('input[name="spreadsheet_id"]');
            const gidInput = document.querySelector('input[name="gid"]');
            const modeSelect = document.querySelector('select[name="sheet_mode"]');
            const urlInput = document.getElementById('spreadsheet_url');

            const previewContainer = document.getElementById('previewContainer');
            const previewIframe = document.getElementById('previewIframe');
            const noPreviewMessage = document.getElementById('noPreviewMessage');

            function updatePreview() {
                const id = idInput.value.trim();
                const gid = gidInput.value.trim();
                const mode = modeSelect.value;

                if (id) {
                    let embedUrl = `https://docs.google.com/spreadsheets/d/${id}/htmlembed`;
                    let queryParams = [];

                    if (mode === 'single' && gid !== '') {
                        queryParams.push(`gid=${gid}`);
                        queryParams.push('single=true');
                    } else {
                        queryParams.push('widget=true');
                        queryParams.push('headers=false');
                    }

                    if (queryParams.length > 0) {
                        embedUrl += '?' + queryParams.join('&');
                    }

                    previewIframe.src = embedUrl;
                    previewContainer.style.display = 'block';
                    noPreviewMessage.style.display = 'none';
                } else {
                    previewIframe.src = '';
                    previewContainer.style.display = 'none';
                    noPreviewMessage.style.display = 'block';
                }
            }

            idInput.addEventListener('input', updatePreview);
            gidInput.addEventListener('input', updatePreview);
            modeSelect.addEventListener('change', updatePreview);

            if (urlInput) {
                urlInput.addEventListener('input', function () {
                    // Use setTimeout to ensure the previous event listener updates the inputs first
                    setTimeout(updatePreview, 50);
                });
            }

            // Initial check in case it's an edit page with existing values
            updatePreview();
        });
    </script>
@endpush