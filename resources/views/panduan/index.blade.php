@extends('layouts.admin')

@section('title', 'Panduan Pengguna SISOKA')

@section('content')
    @php
        $panduanLink = \App\Models\DynamicMenu::where('type', 'panduan_pengguna')->first();
        $rawUrl = $panduanLink ? $panduanLink->url : '';

        $videoLink = \App\Models\DynamicMenu::where('type', 'video_panduan')->first();
        $videoEmbedUrl = $videoLink ? $videoLink->embed_url : '';
        $videoRawUrl = $videoLink ? $videoLink->url : '';

        $hasPdf = !empty($rawUrl);
        $hasVideo = !empty($videoEmbedUrl);
    @endphp

    <div class="container-fluid mt-3 animate-fade-in">
        <!-- Header -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
            <div>
                <h1 class="fw-bold mb-1" style="color: var(--bps-orange);">Panduan Pengguna</h1>
                <p class="text-dark lead mb-0 small-description">Petunjuk penggunaan sistem informasi SISOKA</p>
            </div>
            @if($hasPdf)
                <div class="mt-3 mt-md-0">
                    <a href="" id="externalLinkBtn" target="_blank"
                        class="btn btn-primary bg-navy border-0 rounded-pill px-4 shadow-sm">
                        <i class="fas fa-external-link-alt me-2"></i> Buka PDF di Tab Baru
                    </a>
                </div>
            @endif
        </div>

        @if($hasPdf || $hasVideo)
            <div class="row g-4">
                @if($hasPdf)
                    <!-- PDF Viewer Column -->
                    <div class="{{ $hasVideo ? 'col-lg-7' : 'col-12' }}">
                        <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100"
                            style="min-height: calc(100vh - 250px);">
                            <div class="card-body p-0 d-flex flex-column">
                                <div id="previewContainer" class="flex-grow-1 w-100"
                                    style="display: none; min-height: calc(100vh - 250px);">
                                    <iframe id="previewIframe" src="" width="100%" height="100%"
                                        style="border:0; flex-grow: 1; min-height: calc(100vh - 250px);"
                                        title="Panduan Pengguna PDF"></iframe>
                                </div>

                                <!-- Loading Indicator -->
                                <div id="loadingPreview" class="d-flex flex-grow-1 justify-content-center align-items-center p-5"
                                    style="min-height: 50vh;">
                                    <div class="text-center">
                                        <div class="spinner-border text-primary mb-3" role="status"
                                            style="color: var(--bps-orange) !important; width: 3rem; height: 3rem;">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                        <h5 class="fw-bold">Memuat Panduan PDF...</h5>
                                        <p class="text-muted small">Pastikan koneksi internet Anda stabil</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                @if($hasVideo)
                    <!-- YouTube Video Column -->
                    <div class="{{ $hasPdf ? 'col-lg-5' : 'col-lg-8 mx-auto col-md-10' }}">
                        <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 bg-white">
                            <div class="card-body p-4 d-flex flex-column justify-content-between h-100">
                                <div>
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="bg-danger bg-opacity-10 text-danger rounded-circle p-2 me-3 d-flex align-items-center justify-content-center"
                                            style="width: 40px; height: 40px;">
                                            <i class="fab fa-youtube fa-lg"></i>
                                        </div>
                                        <h5 class="fw-bold text-navy mb-0">Video Panduan Pengguna</h5>
                                    </div>

                                    <div class="ratio ratio-16x9 w-100 rounded-3 overflow-hidden shadow-sm mb-4">
                                        <iframe src="{{ $videoEmbedUrl }}" title="Video Panduan Pengguna" frameborder="0"
                                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                            allowfullscreen></iframe>
                                    </div>

                                    <h4 class="fw-bold text-navy mb-3">Panduan Interaktif SISOKA</h4>
                                    <p class="text-muted" style="line-height: 1.6;">
                                        Saksikan video panduan ini untuk memahami cara mengoperasikan sistem SISOKA secara praktis,
                                        mulai dari visualisasi data kemiskinan, ekspor data, hingga verifikasi data.
                                    </p>

                                    <div class="d-flex align-items-start gap-3 mt-4 bg-light p-3 rounded-3">
                                        <i class="fas fa-lightbulb text-warning fa-lg mt-1"></i>
                                        <div>
                                            <h6 class="fw-bold mb-1 text-navy">Tips Penggunaan</h6>
                                            <p class="text-muted small mb-0">Klik tombol fullscreen pada pojok kanan bawah video
                                                untuk memperbesar tampilan video.</p>
                                        </div>
                                    </div>
                                </div>

                                @if($videoRawUrl)
                                    <div class="mt-4 pt-3 border-top text-end">
                                        <a href="{{ $videoRawUrl }}" target="_blank"
                                            class="btn btn-outline-danger rounded-pill px-4 btn-sm">
                                            <i class="fab fa-youtube me-2"></i>Tonton di YouTube
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @else
            <!-- Empty State -->
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-0 d-flex flex-column">
                    <div class="d-flex flex-grow-1 flex-column justify-content-center align-items-center p-5"
                        style="min-height: 50vh;">
                        <div class="text-center my-5 py-5">
                            <div class="bg-warning bg-opacity-10 text-warning rounded-circle p-4 mb-4 d-inline-flex"
                                style="width: 80px; height: 80px; align-items: center; justify-content: center;">
                                <i class="fas fa-exclamation-triangle fa-2x"></i>
                            </div>
                            <h4 class="fw-bold text-navy mb-2">Panduan Pengguna Belum Tersedia</h4>
                            <p class="text-muted mb-0">Administrator belum menyetel konten panduan ini.</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Footer Help Section -->
        <div class="row mt-5 mb-4">
            <div class="col-lg-12">
                <div class="card border-0 shadow-sm"
                    style="border-radius: 1.25rem; background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);">
                    <div class="card-body p-4">
                        <div class="row align-items-center">
                            <div class="col-md-1 text-center mb-3 mb-md-0">
                                <div class="bg-primary-faded p-3 rounded-circle d-inline-flex align-items-center justify-content-center"
                                    style="width: 60px; height: 60px; background: rgba(37, 99, 235, 0.1);">
                                    <i class="fas fa-question-circle fa-2x text-primary"></i>
                                </div>
                            </div>
                            <div class="col-md-11">
                                <h5 class="fw-bold text-navy mb-1">Masih butuh bantuan?</h5>
                                <p class="text-muted mb-0 small">Jika Anda mengalami kendala teknis atau menemukan bug yang
                                    tidak dijelaskan dalam panduan, silakan hubungi tim IT / Administrator BPS Provinsi
                                    Kalimantan Barat.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            .bg-primary-faded {
                background-color: rgba(37, 99, 235, 0.1);
            }

            .animate-fade-in {
                animation: fadeInUp 0.6s ease-out;
            }

            @keyframes fadeInUp {
                from {
                    opacity: 0;
                    transform: translateY(20px);
                }

                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            @media (max-width: 768px) {

                #previewContainer,
                #previewIframe {
                    min-height: 500px !important;
                }
            }
        </style>
    @endpush

    @if($hasPdf)
        @push('scripts')
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const rawUrl = "{{ $rawUrl }}";

                    const previewContainer = document.getElementById('previewContainer');
                    const previewIframe = document.getElementById('previewIframe');
                    const loadingPreview = document.getElementById('loadingPreview');
                    const externalLinkBtn = document.getElementById('externalLinkBtn');

                    if (externalLinkBtn) {
                        externalLinkBtn.href = rawUrl;
                    }

                    function getEmbedUrl(url) {
                        if (!url) return '';

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

                        return url;
                    }

                    function initPreview() {
                        const finalEmbedUrl = getEmbedUrl(rawUrl);

                        if (finalEmbedUrl && previewIframe) {
                            previewIframe.src = finalEmbedUrl;

                            // Deteksi jika iframe berhasil dimuat
                            previewIframe.onload = function () {
                                if (loadingPreview) {
                                    loadingPreview.classList.add('d-none');
                                    loadingPreview.classList.remove('d-flex');
                                }
                                if (previewContainer) {
                                    previewContainer.style.display = 'block';
                                }
                            };

                            // FAILSAFE: Jika setelah 7 detik masih loading (kemungkinan diblokir browser)
                            setTimeout(() => {
                                if (loadingPreview && !loadingPreview.classList.contains('d-none')) {
                                    loadingPreview.innerHTML = `
                                                    <div class="text-center p-5">
                                                        <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                                                        <h5 class="fw-bold">Preview Tidak Dapat Dimuat</h5>
                                                        <p class="text-muted">Browser Anda memblokir tampilan PDF secara langsung atau koneksi lambat.</p>
                                                        <a href="${rawUrl}" target="_blank" class="btn btn-primary rounded-pill px-4 mt-2">
                                                            <i class="fas fa-external-link-alt me-2"></i> Klik Untuk Membuka Panduan
                                                        </a>
                                                    </div>
                                                `;
                                }
                            }, 7000);
                        }
                    }

                    initPreview();
                });
            </script>
        @endpush
    @endif
@endsection