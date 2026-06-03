@extends('layouts.admin')

@section('title', 'Login')

@section('content')
    <div class="d-flex justify-content-center align-items-center mt-5" style="min-height: calc(100vh - 180px);">

        <div class="text-center" style="max-width: 720px;">

            @if(session('error'))
                <div class="bg-red-50 text-red-600 p-3 rounded-lg mb-4 text-sm">
                    {{ session('error') }}
                </div>
            @endif

            @php
                $logoMenu = \App\Models\DynamicMenu::where('type', 'logo')->first();
                $logoUrl = 'https://blog.educationnest.com/wp-content/uploads/2023/04/c99172c17b83d3c620b997858351b2a5.gif';
                if ($logoMenu && $logoMenu->url) {
                    if (preg_match('/drive\.google\.com\/file\/d\/([a-zA-Z0-9-_]+)/', $logoMenu->url, $matches)) {
                        $logoUrl = 'https://lh3.googleusercontent.com/d/' . $matches[1];
                    } elseif (preg_match('/drive\.google\.com\/open\?id=([a-zA-Z0-9-_]+)/', $logoMenu->url, $matches)) {
                        $logoUrl = 'https://lh3.googleusercontent.com/d/' . $matches[1];
                    } else {
                        $logoUrl = $logoMenu->url;
                    }
                }
            @endphp

            <img src="{{ $logoUrl }}" alt="Logo Sisoka"
                style="display:block; margin:0 auto; width:100%; max-width:700px; max-height:350px; object-fit:contain; mix-blend-mode: multiply; filter: brightness(1.05) contrast(1.1); fade-in-up"
                onerror="this.onerror=null; this.src='https://blog.educationnest.com/wp-content/uploads/2023/04/c99172c17b83d3c620b997858351b2a5.gif';"
                background="transparent">

            <h2 class="fw-bold mb-2 text-center">
                Sistem Informasi Sosial Kalbar
            </h2>
            <p class="text-secondary text-center mx-auto mb-2" style="max-width:520px;">
                Menyediakan informasi statistik sosial ekonomi untuk Badan Pusat Statistik Kabupaten/Kota se-Kalimantan
                Barat.
            </p>

            <p class="text-muted text-center small mb-4">
                Masuk menggunakan akun Google untuk mengakses fitur.
            </p>

            <a href="{{ url('/auth/google') }}" class="btn btn-google-login py-3 mb-3">
                <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/c/c1/Google_%22G%22_logo.svg/960px-Google_%22G%22_logo.svg.png"
                    alt="Google Logo">
                <span>Login with Google</span>
            </a>

        </div>

    </div>




@endsection