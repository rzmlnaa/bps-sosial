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
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" required
                            value="{{ old('name', $dynamicMenu->name) }}">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <input type="hidden" name="slug" required value="{{ old('slug', $dynamicMenu->slug) }}">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label class="form-label fw-medium">Parent Menu</label>
                        <select name="parent_id" id="parentSelect" class="form-select">
                            <option value="">-- Jadikan Menu Utama (Dropdown) --</option>
                            @foreach($parents as $parent)
                                <option value="{{ $parent->id }}" {{ old('parent_id', $dynamicMenu->parent_id) == $parent->id ? 'selected' : '' }}>{{ $parent->name }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted">Pilih parent jika ini adalah submenu.</small>
                    </div>
                </div>

                <hr class="my-4">
                <h5 class="fw-bold mb-3">Pengaturan Konten</h5>

                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label class="form-label fw-medium">Tipe Konten <span class="text-danger"
                                id="typeRequiredStar">*</span></label>
                        <select name="type" class="form-select" id="typeSelect">
                            <option value="">Pilih Tipe Konten</option>
                            <option value="spreadsheet" {{ old('type', $dynamicMenu->type) == 'spreadsheet' ? 'selected' : '' }}>Google Spreadsheet</option>
                            <option value="youtube" {{ old('type', $dynamicMenu->type) == 'youtube' ? 'selected' : '' }}>
                                YouTube Video </option>
                            <option value="drive" {{ old('type', $dynamicMenu->type) == 'drive' ? 'selected' : '' }}>
                                Google
                                Drive (View/Embed) </option>
                            <option value="external" {{ old('type', $dynamicMenu->type) == 'external' ? 'selected' : '' }}>
                                Link Eksternal Lainnya</option>
                        </select>
                    </div>
                </div>

                <div class="row" id="urlSection" style="display: none;">
                    <div class="col-md-12 mb-3">
                        <label class="form-label fw-medium" id="urlLabel">Link / URL <span class="text-danger"
                                id="urlRequiredStar">*</span></label>
                        <input type="text" name="url" id="urlInput" class="form-control @error('url') is-invalid @enderror"
                            placeholder="Paste link di sini..." value="{{ old('url', $dynamicMenu->url) }}">
                        @error('url')
                            <div class="invalid-feedback" id="urlError">{{ $message }}</div>
                        @enderror
                        <div class="invalid-feedback d-none" id="youtubeError">Format URL YouTube tidak valid. Harap
                            masukkan link youtube.com atau youtu.be yang benar.</div>
                        <div class="invalid-feedback d-none" id="spreadsheetError">Format URL Spreadsheet tidak valid. Harap
                            masukkan link docs.google.com/spreadsheets yang benar.</div>
                        <div class="invalid-feedback d-none" id="driveError">Format URL Google Drive tidak valid. Harap
                            masukkan link drive.google.com yang benar.</div>
                        <small class="text-muted" id="urlHint">Paste link lengkap (URL) dari sumber konten.</small>
                    </div>
                </div>

                <div id="spreadsheetExtra" style="display: none;">
                    @php
                        $meta = is_array($dynamicMenu->meta) ? $dynamicMenu->meta : json_decode($dynamicMenu->meta ?? '[]', true);
                    @endphp
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-medium">Sheet Mode</label>
                            <select name="sheet_mode" class="form-select" id="modeSelect">
                                <option value="all" {{ old('sheet_mode', $meta['sheet_mode'] ?? 'all') == 'all' ? 'selected' : '' }}>Semua Sheet (Tabs)</option>
                                <option value="single" {{ old('sheet_mode', $meta['sheet_mode'] ?? '') == 'single' ? 'selected' : '' }}>Satu Sheet Saja (Butuh GID)</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-medium">GID (Khusus mode Single)</label>
                            <input type="text" name="gid" id="gidInput" class="form-control" placeholder="Contoh: 0"
                                value="{{ old('gid', $meta['gid'] ?? '') }}">
                        </div>
                    </div>
                </div>

                <div id="externalLinksSection" style="display: none;" class="mb-3">
                    <label class="form-label fw-medium">Daftar Link (Opsional)</label>
                    <div id="linksContainer">
                        @php
                            $links = old('links', $meta['links'] ?? []);
                        @endphp
                        @foreach($links as $index => $link)
                            <div class="row mb-2 link-row">
                                <div class="col-md-5">
                                    <input type="text" name="links[{{ $index }}][name]" class="form-control"
                                        placeholder="Nama Link" value="{{ $link['name'] ?? '' }}">
                                </div>
                                <div class="col-md-5">
                                    <input type="text" name="links[{{ $index }}][url]" class="form-control"
                                        placeholder="URL Link" value="{{ $link['url'] ?? '' }}">
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-outline-danger w-100 remove-link-btn">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <button type="button" class="btn btn-outline-primary btn-sm rounded-pill mt-2" id="addLinkBtn">
                        <i class="fas fa-plus me-1"></i> Tambah Link
                    </button>
                </div>

                <div class="row" id="embedUrlSection">
                    <div class="col-md-12 mb-3">
                        <label class="form-label fw-medium">Embed URL (Opsional)</label>
                        <input type="text" name="embed_url" id="embedUrlInput" class="form-control"
                            placeholder="Akan terisi otomatis jika dikosongkan"
                            value="{{ old('embed_url', $dynamicMenu->embed_url) }}">
                        <small class="text-muted">Gunakan jika Anda ingin menentukan sendiri URL untuk iframe.</small>
                    </div>
                </div>

                <div class="mb-4 form-check form-switch mt-3">
                    <input class="form-check-input" type="checkbox" role="switch" id="isActive" name="is_active" {{ old('is_active', $dynamicMenu->is_active) ? 'checked' : '' }}>
                    <label class="form-check-label ms-2 fw-medium" for="isActive">Aktifkan Menu Ini</label>
                </div>

                <hr class="my-4">
                <h5 class="fw-bold mb-3">Live Preview Konten</h5>
                <div class="mb-4">
                    <div class="card bg-light border-0 rounded-4 overflow-hidden" style="height: 450px; display: none;"
                        id="previewContainer">
                        <div class="card-body p-0 h-100">
                            <iframe id="previewIframe" src="" width="100%" height="100%" style="border:0;"
                                title="Preview"></iframe>
                        </div>
                    </div>
                    <div class="text-center p-5 text-muted bg-light rounded-4 border"
                        style="border-style: dashed !important;" id="noPreviewMessage">
                        <i class="fas fa-eye fa-3x mb-3 opacity-50" id="previewIcon"></i>
                        <p class="mb-0" id="previewText">Isi URL untuk melihat preview</p>
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <button type="submit" id="btnDinamisCreate"
                        class="btn btn-primary bg-navy py-2 px-5 rounded-pill border-0">
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

            nameInput.addEventListener('input', function () {
                let slug = nameInput.value
                    .toLowerCase()
                    .replace(/[^\w\s-]/g, '') // Remove invalid chars
                    .replace(/[\s_-]+/g, '-') // Swap whitespace, underscores, dashes with a single dash
                    .replace(/^-+|-+$/g, ''); // Trim dashes from start and end

                slugInput.value = slug;
            });

            // Type and URL handling
            const typeSelect = document.getElementById('typeSelect');
            const urlInput = document.getElementById('urlInput');
            const urlHint = document.getElementById('urlHint');
            const spreadsheetExtra = document.getElementById('spreadsheetExtra');
            const gidInput = document.getElementById('gidInput');
            const modeSelect = document.getElementById('modeSelect');
            const embedUrlInput = document.getElementById('embedUrlInput');

            const previewContainer = document.getElementById('previewContainer');
            const previewIframe = document.getElementById('previewIframe');
            const noPreviewMessage = document.getElementById('noPreviewMessage');
            const previewIcon = document.getElementById('previewIcon');
            const previewText = document.getElementById('previewText');

            const submitBtn = document.getElementById('btnDinamisCreate');

            function isValidYoutubeUrl(url) {
                if (!url) return true;
                const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=)([^#\&\?]*).*/;
                const match = url.match(regExp);
                return (match && match[2].length == 11);
            }

            function isValidSpreadsheetUrl(url) {
                if (!url) return true;
                return url.includes('docs.google.com/spreadsheets');
            }

            function isValidDriveUrl(url) {
                if (!url) return true;
                return url.includes('drive.google.com');
            }

            const parentSelect = document.getElementById('parentSelect');

            function updateUI(event) {
                const type = typeSelect.value;
                const parentId = parentSelect.value;
                const urlSection = document.getElementById('urlSection');

                // Conditional Required Logic
                const typeRequiredStar = document.getElementById('typeRequiredStar');
                const urlRequiredStar = document.getElementById('urlRequiredStar');

                if (parentId === "") {
                    // It's a Top Level Menu (Dropdown), Content Type is NOT required
                    typeSelect.required = false;
                    urlInput.required = false;
                    typeRequiredStar.style.display = 'none';
                    urlRequiredStar.style.display = 'none';

                    // NEW: Automatically reset values when switched to Menu Utama
                    if (event && event.target === parentSelect) {
                        typeSelect.value = "";
                        urlInput.value = "";
                    }
                } else {
                    // It's a Submenu, Content Type IS required
                    typeSelect.required = true;
                    // For external types, the main URL is optional if there are links in the list
                    urlInput.required = type === 'external' ? false : true;
                    typeRequiredStar.style.display = 'inline';
                    urlRequiredStar.style.display = type === 'external' ? 'none' : 'inline';
                }

                urlSection.style.display = (type && type !== 'external') ? 'block' : 'none';
                spreadsheetExtra.style.display = type === 'spreadsheet' ? 'block' : 'none';

                const embedUrlSection = document.getElementById('embedUrlSection');
                if (embedUrlSection) {
                    embedUrlSection.style.display = (type && type !== 'external') ? 'block' : 'none';
                }

                if (type === 'external') {
                    urlInput.value = '';
                    const embedInput = document.getElementById('embedUrlInput');
                    if (embedInput) embedInput.value = '';
                }

                const externalLinksSection = document.getElementById('externalLinksSection');
                if (externalLinksSection) {
                    externalLinksSection.style.display = type === 'external' ? 'block' : 'none';
                }

                if (type === 'spreadsheet') {
                    urlHint.innerHTML = 'Paste link Google Spreadsheet lengkap. ID dan GID akan diekstrak otomatis.';
                } else if (type === 'youtube') {
                    urlHint.innerHTML = 'Paste link video YouTube (misal: https://www.youtube.com/watch?v=...)';
                } else if (type === 'drive') {
                    urlHint.innerHTML = 'Paste link "Share" dari Google Drive atau link folder.';
                } else {
                    urlHint.innerHTML = 'Paste link URL eksternal lainnya.';
                }
                validateForm();
                updatePreview();
            }

            // External Links Handling
            const addLinkBtn = document.getElementById('addLinkBtn');
            const linksContainer = document.getElementById('linksContainer');
            let linkIndex = document.querySelectorAll('.link-row').length;

            if (addLinkBtn) {
                addLinkBtn.addEventListener('click', function () {
                    const row = document.createElement('div');
                    row.className = 'row mb-2 link-row';
                    row.innerHTML = `
                                            <div class="col-md-5">
                                                <input type="text" name="links[${linkIndex}][name]" class="form-control" placeholder="Nama Link">
                                            </div>
                                            <div class="col-md-5">
                                                <input type="text" name="links[${linkIndex}][url]" class="form-control" placeholder="URL Link">
                                            </div>
                                            <div class="col-md-2">
                                                <button type="button" class="btn btn-outline-danger w-100 remove-link-btn">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        `;
                    linksContainer.appendChild(row);
                    linkIndex++;
                });

                linksContainer.addEventListener('click', function (e) {
                    if (e.target.classList.contains('remove-link-btn') || e.target.closest('.remove-link-btn')) {
                        const row = e.target.closest('.link-row');
                        row.remove();
                    }
                });
            }

            function validateForm() {
                const type = typeSelect.value;
                const url = urlInput.value.trim();
                let isValid = true;
                const youtubeError = document.getElementById('youtubeError');
                const spreadsheetError = document.getElementById('spreadsheetError');
                const driveError = document.getElementById('driveError');
                const urlError = document.getElementById('urlError');

                // Reset states
                urlInput.classList.remove('is-invalid');
                if (youtubeError) youtubeError.classList.add('d-none');
                if (spreadsheetError) spreadsheetError.classList.add('d-none');
                if (driveError) driveError.classList.add('d-none');
                if (urlError) urlError.classList.remove('d-none');

                if (url === '') {
                    // For external types, it's valid if there's at least one link in the list
                    if (type === 'external' && document.querySelectorAll('.link-row').length > 0) {
                        isValid = true;
                    } else {
                        isValid = false;
                    }
                } else {
                    if (type === 'youtube') {
                        if (!isValidYoutubeUrl(url)) {
                            urlInput.classList.add('is-invalid');
                            if (youtubeError) youtubeError.classList.remove('d-none');
                            if (urlError) urlError.classList.add('d-none');
                            isValid = false;
                        }
                    } else if (type === 'spreadsheet') {
                        if (!isValidSpreadsheetUrl(url)) {
                            urlInput.classList.add('is-invalid');
                            if (spreadsheetError) spreadsheetError.classList.remove('d-none');
                            if (urlError) urlError.classList.add('d-none');
                            isValid = false;
                        }
                    } else if (type === 'drive') {
                        if (!isValidDriveUrl(url)) {
                            urlInput.classList.add('is-invalid');
                            if (driveError) driveError.classList.remove('d-none');
                            if (urlError) urlError.classList.add('d-none');
                            isValid = false;
                        }
                    }
                }

                // submitBtn.disabled = !isValid; 
                const hasError = urlInput.classList.contains('is-invalid');
                if (hasError) {
                    submitBtn.disabled = true;
                } else {
                    submitBtn.disabled = false;
                }
            }

            function extractSpreadsheetInfo(url) {
                if (typeSelect.value !== 'spreadsheet') return;

                const gidMatch = url.match(/[#&?]gid=([0-9]+)/);
                if (gidMatch && gidMatch[1]) {
                    gidInput.value = gidMatch[1];
                    modeSelect.value = 'single';
                }
            }

            function getEmbedUrl(type, url, gid, mode) {
                if (!url) return '';

                if (type === 'youtube') {
                    const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=)([^#\&\?]*).*/;
                    const match = url.match(regExp);
                    if (match && match[2].length == 11) {
                        return "https://www.youtube.com/embed/" + match[2];
                    }
                } else if (type === 'spreadsheet') {
                    const idMatch = url.match(/\/d\/([a-zA-Z0-9-_]+)/);
                    if (idMatch && idMatch[1]) {
                        let embed = `https://docs.google.com/spreadsheets/d/${idMatch[1]}/htmlembed`;
                        let params = [];
                        if (mode === 'single' && gid !== '') {
                            params.push(`gid=${gid}`);
                            params.push('single=true');
                        } else {
                            params.push('widget=true');
                            params.push('headers=false');
                        }
                        return embed + (params.length ? '?' + params.join('&') : '');
                    }
                } else if (type === 'drive') {
                    // Google Drive Folders
                    const folderMatch = url.match(/drive\.google\.com\/drive\/folders\/([a-zA-Z0-9-_]+)/);
                    if (folderMatch && folderMatch[1]) {
                        return "https://drive.google.com/embeddedfolderview?id=" + folderMatch[1] + "#list";
                    }

                    // Google Drive Files
                    const fileMatch = url.match(/drive\.google\.com\/file\/d\/([a-zA-Z0-9-_]+)/);
                    if (fileMatch && fileMatch[1]) {
                        return "https://drive.google.com/file/d/" + fileMatch[1] + "/preview";
                    }

                    if (url.includes('view?usp=sharing')) return url.replace('view?usp=sharing', 'preview');
                    if (url.includes('/view')) return url.replace('/view', '/preview');
                }
                return url;
            }

            function updatePreview() {
                const type = typeSelect.value;
                const url = urlInput.value.trim();
                const gid = gidInput.value.trim();
                const mode = modeSelect.value;
                const manualEmbed = embedUrlInput.value.trim();

                let finalEmbedUrl = manualEmbed || getEmbedUrl(type, url, gid, mode);

                // Ensure Google Drive links are always in embed/preview format
                if (finalEmbedUrl.includes('drive.google.com')) {
                    const folderMatch = finalEmbedUrl.match(/drive\.google\.com\/drive\/folders\/([a-zA-Z0-9-_]+)/);
                    if (folderMatch && folderMatch[1]) {
                        finalEmbedUrl = "https://drive.google.com/embeddedfolderview?id=" + folderMatch[1] + "#list";
                    } else {
                        const fileMatch = finalEmbedUrl.match(/drive\.google\.com\/file\/d\/([a-zA-Z0-9-_]+)/);
                        if (fileMatch && fileMatch[1]) {
                            finalEmbedUrl = "https://drive.google.com/file/d/" + fileMatch[1] + "/preview";
                        } else if (finalEmbedUrl.includes('view?usp=sharing')) {
                            finalEmbedUrl = finalEmbedUrl.replace('view?usp=sharing', 'preview');
                        } else if (finalEmbedUrl.includes('/view')) {
                            finalEmbedUrl = finalEmbedUrl.replace('/view', '/preview');
                        }
                    }
                }

                if (finalEmbedUrl && (type !== 'external' || manualEmbed)) {
                    previewIframe.src = finalEmbedUrl;
                    previewContainer.style.display = 'block';
                    noPreviewMessage.style.display = 'none';
                } else {
                    previewIframe.src = '';
                    previewContainer.style.display = 'none';
                    noPreviewMessage.style.display = 'block';

                    // Update helper text if external
                    if (type === 'external' && url && !manualEmbed) {
                        previewIcon.className = 'fas fa-external-link-alt fa-3x mb-3 opacity-50';
                        previewText.innerText = 'Link eksternal akan dibuka di tab baru oleh user.';
                    } else {
                        previewIcon.className = 'fas fa-eye fa-3x mb-3 opacity-50';
                        previewText.innerText = 'Isi URL untuk melihat preview';
                    }
                }
            }

            typeSelect.addEventListener('change', updateUI);
            parentSelect.addEventListener('change', updateUI);
            urlInput.addEventListener('input', function () {
                extractSpreadsheetInfo(this.value);
                validateForm();
                updatePreview();
            });
            gidInput.addEventListener('input', updatePreview);
            modeSelect.addEventListener('change', updatePreview);
            embedUrlInput.addEventListener('input', updatePreview);

            // Initial UI state
            updateUI();
        });
    </script>
@endpush