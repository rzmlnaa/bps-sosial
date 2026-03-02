@extends('layouts.admin')

@section('title', $menu->name)

@section('content')

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
        <div>
            <h2 class="fw-bold mb-1" style="color: var(--bps-orange);">{{ $menu->name }}</h2>
            @if($title)
                <p class="text-muted mb-0">{{ $title }}</p>
            @endif
        </div>
        <div class="mt-3 mt-md-0">
            <button class="btn btn-outline-primary rounded-pill px-4 d-flex align-items-center" id="copyLinkBtn">
                <i class="fas fa-share-alt me-2"></i> <span>Bagikan</span>
            </button>
        </div>
    </div>
    <div class="fade-in-up ">

        <div class="card shadow-sm border-0 rounded-4 flex-grow-1 overflow-hidden" style="min-height: 70vh;">
            <div class="card-body p-0 d-flex flex-column">
                @if($menu->type === 'external' && $menu->url)
                    <div class="d-flex flex-grow-1 justify-content-center align-items-center text-muted p-5"
                        style="min-height: 50vh;">
                        <div class="text-center">
                            <i class="fas fa-external-link-alt fa-4x mb-3 opacity-50" style="color: var(--bps-orange);"></i>
                            <h4 class="fw-bold">Link Eksternal</h4>
                            <p>Klik tombol di bawah untuk membuka halaman di tab baru.</p>
                            <a href="{{ $menu->url }}" target="_blank"
                                class="btn btn-primary bg-navy rounded-pill px-5 py-2 border-0">
                                <i class="fas fa-external-link-alt me-2"></i>Buka {{ $menu->name }}
                            </a>
                        </div>
                    </div>
                @elseif($menu->embed_url)
                    <iframe src="{{ $menu->safe_embed_url }}" width="100%" height="100%" style="border:0; flex-grow: 1;"
                        title="{{ $menu->name }}"></iframe>
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

@push('scripts')
    <script>
        document.getElementById('copyLinkBtn').addEventListener('click', function () {
            const url = window.location.href;
            navigator.clipboard.writeText(url).then(function () {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'Link berhasil disalin ke clipboard',
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
                        text: 'Link berhasil disalin ke clipboard',
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
    </script>
@endpush