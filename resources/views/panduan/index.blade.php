@extends('layouts.admin')

@section('title', 'Panduan Pengguna SISOKA')

@section('content')
    @php
        $panduanLink = \App\Models\DynamicMenu::where('type', 'panduan_pengguna')->first();
        $rawUrl = $panduanLink ? $panduanLink->url : '';
    @endphp

    <div class="container-fluid mt-3">
        <!-- Header -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-5">
            <div>
                <h1 class="fw-bold mb-1" style="color: var(--bps-orange);">Panduan Pengguna</h1>
                <p class="text-dark lead mb-0">Petunjuk penggunaan sistem informasi SISOKA</p>
            </div>
            @if($rawUrl)
                <div class="mt-3 mt-md-0">
                    <a href="" id="externalLinkBtn" target="_blank" class="btn btn-primary rounded-pill px-4 shadow-sm">
                        <i class="fas fa-external-link-alt me-2"></i> Buka di Tab Baru
                    </a>
                </div>
            @endif
        </div>

        <!-- PDF Viewer Section -->
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4 flex-grow-1 overflow-hidden"
                    style="min-height: calc(100vh - 250px);">
                    <div class="card-body p-0 d-flex flex-column">
                        @if($rawUrl)
                            <div id="previewContainer" class="flex-grow-1 w-100"
                                style="display: none; min-height: calc(100vh - 250px);">
                                <iframe id="previewIframe" src="" width="100%" height="100%"
                                    style="border:0; flex-grow: 1; min-height: calc(100vh - 250px);"
                                    title="Panduan Pengguna"></iframe>
                            </div>

                            <!-- Loading Indicator -->
                            <div id="loadingPreview" class="d-flex flex-grow-1 justify-content-center align-items-center p-5"
                                style="min-height: 50vh;">
                                <div class="text-center">
                                    <div class="spinner-border text-primary mb-3" role="status"
                                        style="color: var(--bps-orange) !important; width: 3rem; height: 3rem;">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <h5 class="fw-bold">Memuat Panduan...</h5>
                                    <p class="text-muted small">Pastikan koneksi internet Anda stabil</p>
                                </div>
                            </div>
                        @else
                            <!-- Empty State -->
                            <div class="d-flex flex-grow-1 flex-column justify-content-center align-items-center p-5" style="min-height: 50vh;">
                                <div class="text-center my-5 py-5">
                                    <div class="bg-warning bg-opacity-10 text-warning rounded-circle p-4 mb-4 d-inline-flex" style="width: 80px; height: 80px; align-items: center; justify-content: center;">
                                        <i class="fas fa-exclamation-triangle fa-2x"></i>
                                    </div>
                                    <h4 class="fw-bold text-navy mb-2">Panduan Pengguna Belum Tersedia</h4>
                                    <p class="text-muted mb-0">Administrator belum menyetel konten panduan ini.</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

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
                            <div class="col-md-8">
                                <h5 class="fw-bold text-navy mb-1">Masih butuh bantuan?</h5>
                                <p class="text-muted mb-0 small">Jika Anda mengalami kendala teknis atau menemukan bug yang
                                    tidak dijelaskan dalam panduan, jangan ragu untuk menghubungi tim pengembang.</p>
                            </div>
                            <div class="col-md-3 text-md-end mt-3 mt-md-0">
                                <a href="/developer" target="_blank" class="btn btn-outline-primary rounded-pill px-4">
                                    <i class="fas fa-headset me-2"></i> Hubungi Developer
                                </a>
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

            .fade-in-up {
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

    @if($rawUrl)
        @push('scripts')
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const rawUrl = "{{ $rawUrl }}";

                const previewContainer = document.getElementById('previewContainer');
                const previewIframe = document.getElementById('previewIframe');
                const loadingPreview = document.getElementById('loadingPreview');
                const externalLinkBtn = document.getElementById('externalLinkBtn');

                externalLinkBtn.href = rawUrl;

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

                    if (finalEmbedUrl) {
                        previewIframe.src = finalEmbedUrl;

                        // Deteksi jika iframe berhasil dimuat
                        previewIframe.onload = function () {
                            loadingPreview.classList.add('d-none');
                            loadingPreview.classList.remove('d-flex');
                            previewContainer.style.display = 'block';
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