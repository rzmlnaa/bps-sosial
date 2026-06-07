@extends('layouts.admin')

@section('title', $menu->name)

@section('content')

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
        <div>
            <h2 class="fw-bold mb-1" style="color: var(--bps-orange);">
                {{ $menu->name }}
            </h2>
        </div>

        @if($menu->type !== 'external' && ($menu->url || $menu->embed_url))
            <div class="d-flex gap-2 mt-3 mt-md-0">
                <!-- Tombol Buka -->
                <a href="{{ $menu->url ?: $menu->embed_url }}" target="_blank"
                    class="btn btn-primary rounded-pill px-4 d-flex align-items-center">
                    <i class="fas fa-up-right-from-square me-2"></i>
                    <span>Buka</span>
                </a>
                <!-- Tombol Bagikan -->
                <button class="btn btn-outline-primary rounded-pill px-4 d-flex align-items-center copy-link-btn"
                    data-url="{{ $menu->url ?: $menu->embed_url }}">
                    <i class="fas fa-share-alt me-2"></i>
                    <span>Bagikan</span>
                </button>
            </div>
        @endif
    </div>
    <div class="fade-in-up ">

        <div class="card shadow-sm border-0 rounded-4 flex-grow-1 overflow-hidden" style="min-height: calc(100vh - 180px);">
            <div class="card-body p-0 d-flex flex-column">
                @if($menu->type === 'external')
                    <div class="d-flex flex-grow-1 justify-content-center align-items-center text-muted p-5"
                        style="min-height: 50vh;">
                        <div class="text-center w-100" style="max-width: 800px;">
                            <i class="fas fa-external-link-alt fa-4x mb-3 opacity-50" style="color: var(--bps-orange);"></i>
                            <h4 class="fw-bold mb-4">Link Eksternal</h4>

                            @php
                                $links = is_array($menu->meta) ? ($menu->meta['links'] ?? []) : (json_decode($menu->meta ?? '[]', true)['links'] ?? []);
                            @endphp

                            @if(!empty($links))
                                <div class="row g-3 justify-content-center">
                                    @foreach($links as $link)
                                        @if(!empty($link['url']))
                                            <div class="col-md-6 col-lg-4">
                                                <div class="d-flex flex-column h-100">
                                                    <a href="{{ $link['url'] }}" target="_blank"
                                                        class="btn btn-outline-primary bg-navy-hover w-100 rounded-4 p-3 border-2 d-flex flex-column align-items-center justify-content-center transition-all shadow-sm flex-grow-1"
                                                        style="min-height: 120px;">
                                                        <i class="fas fa-link mb-2 fa-2x"></i>
                                                        <span
                                                            class="fw-bold text-wrap text-center">{{ $link['name'] ?: 'Buka Link' }}</span>
                                                    </a>
                                                    <button class="btn btn-light border shadow-sm rounded-pill mt-2 copy-link-btn"
                                                        data-url="{{ $link['url'] }}">
                                                        <i class="fas fa-share-alt me-2"></i>Bagikan Link
                                                    </button>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach

                                    @if($menu->url)
                                        <div class="col-md-6 col-lg-4">
                                            <div class="d-flex flex-column h-100">
                                                <a href="{{ $menu->url }}" target="_blank"
                                                    class="btn btn-primary bg-navy w-100 rounded-4 p-3 border-0 d-flex flex-column align-items-center justify-content-center transition-all shadow-sm flex-grow-1"
                                                    style="min-height: 120px;">
                                                    <i class="fas fa-external-link-alt mb-2 fa-2x"></i>
                                                    <span class="fw-bold text-wrap text-center">Buka {{ $menu->name }}</span>
                                                </a>
                                                <button class="btn btn-light border shadow-sm rounded-pill mt-2 copy-link-btn"
                                                    data-url="{{ $menu->url }}">
                                                    <i class="fas fa-share-alt me-2"></i>Bagikan Link
                                                </button>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @elseif($menu->url)
                                <p>Klik tombol di bawah untuk membuka halaman di tab baru.</p>
                                <div class="d-flex flex-column flex-sm-row justify-content-center gap-2 align-items-center">
                                    <a href="{{ $menu->url }}" target="_blank"
                                        class="btn btn-primary bg-navy rounded-pill px-5 py-2 border-0 shadow-sm">
                                        <i class="fas fa-external-link-alt me-2"></i>Buka {{ $menu->name }}
                                    </a>
                                    <button class="btn btn-outline-secondary rounded-pill px-4 py-2 shadow-sm copy-link-btn"
                                        data-url="{{ $menu->url }}">
                                        <i class="fas fa-share-alt me-2"></i>Bagikan Link
                                    </button>
                                </div>
                            @else
                                <p>Belum ada link yang tersedia.</p>
                            @endif
                        </div>
                    </div>
                @elseif($menu->url || $menu->embed_url)
                    <div id="previewContainer" class="flex-grow-1 w-100"
                        style="display: none; min-height: calc(100vh - 180px);">
                        <iframe id="previewIframe" src="" width="100%" height="100%"
                            style="border:0; flex-grow: 1; min-height: calc(100vh - 180px);" title="{{ $menu->name }}"></iframe>
                    </div>
                    <div class="d-flex flex-grow-1 justify-content-center align-items-center text-muted p-5"
                        style="min-height: 50vh;" id="loadingPreview">
                        <div class="text-center">
                            <div class="spinner-border text-primary mb-3" role="status"
                                style="color: var(--bps-orange) !important;">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <h5>Memuat Konten...</h5>
                        </div>
                    </div>
                @else
                    <div class="d-flex flex-grow-1 justify-content-center align-items-center text-muted p-5"
                        style="min-height: 50vh;">
                        <div class="text-center">
                            <i class="fas fa-info-circle fa-4x mb-3 opacity-25"></i>
                            <h5>Konten Belum Tersedia</h5>
                            <p>Administrator belum menyetel konten untuk menu ini.</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .bg-navy-hover:hover {
            background-color: #1a365d !important;
            color: white !important;
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1) !important;
        }

        .transition-all {
            transition: all 0.3s ease;
        }

        .bg-navy {
            background-color: #1a365d !important;
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Data from Backend
            const menuType = @json($menu->type);
            const menuUrl = @json($menu->url);
            const menuEmbedUrl = @json($menu->embed_url);
            const menuMeta = @json($menu->meta);

            const previewContainer = document.getElementById('previewContainer');
            const previewIframe = document.getElementById('previewIframe');
            const loadingPreview = document.getElementById('loadingPreview');

            function getEmbedUrl(type, url, meta) {
                if (!url && !menuEmbedUrl) return '';

                if (type === 'youtube') {
                    const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=)([^#\&\?]*).*/;
                    const match = url ? url.match(regExp) : null;
                    if (match && match[2].length == 11) {
                        return "https://www.youtube.com/embed/" + match[2];
                    }
                } else if (type === 'spreadsheet') {
                    const idMatch = url ? url.match(/\/d\/([a-zA-Z0-9-_]+)/) : null;
                    if (idMatch && idMatch[1]) {
                        const gid = meta ? (meta.gid || '') : '';
                        const mode = meta ? (meta.sheet_mode || '') : '';
                        const allowEdit = meta ? (meta.allow_edit === true || meta.allow_edit === '1' || meta.allow_edit === 1 || meta.allow_edit === 'true') : false;

                        if (allowEdit) {
                            let embed = `https://docs.google.com/spreadsheets/d/${idMatch[1]}/edit`;
                            let params = [];
                            if (mode === 'single' && gid !== '') {
                                params.push(`gid=${gid}`);
                            }
                            params.push('widget=true');
                            params.push('headers=false');
                            return embed + (params.length ? '?' + params.join('&') : '');
                        } else {
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
                    }
                } else if (type === 'drive') {
                    // Google Drive Folders
                    const folderMatch = url ? url.match(/drive\.google\.com\/drive\/folders\/([a-zA-Z0-9-_]+)/) : null;
                    if (folderMatch && folderMatch[1]) {
                        return "https://drive.google.com/embeddedfolderview?id=" + folderMatch[1] + "#list";
                    }

                    // Google Drive Files
                    const fileMatch = url ? url.match(/drive\.google\.com\/file\/d\/([a-zA-Z0-9-_]+)/) : null;
                    if (fileMatch && fileMatch[1]) {
                        return "https://drive.google.com/file/d/" + fileMatch[1] + "/preview";
                    }

                    if (url && url.includes('view?usp=sharing')) return url.replace('view?usp=sharing', 'preview');
                    if (url && url.includes('/view')) return url.replace('/view', '/preview');
                }
                return url;
            }

            function initPreview() {
                let finalEmbedUrl = menuEmbedUrl || getEmbedUrl(menuType, menuUrl, menuMeta);

                // Ensure Google Drive links are always in embed/preview format
                if (finalEmbedUrl && finalEmbedUrl.includes('drive.google.com')) {
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

                if (finalEmbedUrl && menuType !== 'external') {
                    previewIframe.src = finalEmbedUrl;

                    previewIframe.onload = function () {
                        if (loadingPreview) {
                            loadingPreview.classList.add('d-none');
                            loadingPreview.classList.remove('d-flex');
                        }
                        previewContainer.style.display = 'flex';
                        previewContainer.classList.add('flex-column');
                    };
                } else {
                    if (loadingPreview) {
                        loadingPreview.classList.add('d-none');
                        loadingPreview.classList.remove('d-flex');
                    }
                }
            }

            initPreview();

            document.querySelectorAll('.copy-link-btn').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const url = this.getAttribute('data-url');

                    if (!url) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Link tidak tersedia untuk dibagikan.',
                        });
                        return;
                    }

                    navigator.clipboard.writeText(url).then(function () {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: 'Link konten berhasil disalin ke clipboard',
                            showConfirmButton: false,
                            timer: 2000,
                            timerProgressBar: true
                        });
                    }).catch(function (err) {
                        console.error('Could not copy text: ', err);
                        // Fallback for older browsers or non-HTTPS
                        const textArea = document.createElement("textarea");
                        textArea.value = url;
                        document.body.appendChild(textArea);
                        textArea.select();
                        try {
                            document.execCommand('copy');
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: 'Link konten berhasil disalin ke clipboard',
                                showConfirmButton: false,
                                timer: 2000,
                                timerProgressBar: true
                            });
                        } catch (err) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: 'Gagal menyalin link.',
                            });
                        }
                        document.body.removeChild(textArea);
                    });
                });
            });
        });
    </script>
@endpush