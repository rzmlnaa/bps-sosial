@extends('layouts.admin')

@section('title', 'Login')

@section('content')
    <div class="d-flex justify-content-center align-items-center" style="min-height: calc(100vh - 180px);">

        <div class="text-center" style="max-width: 720px;">

            @if(session('error'))
                <div class="bg-red-50 text-red-600 p-3 rounded-lg mb-4 text-sm">
                    {{ session('error') }}
                </div>
            @endif

            <dotlottie-player src="{{ asset('statistik-animasi.json') }}" background="transparent" speed="1"
                style="display:block; margin:0 auto; width:100%; max-width:500px;" loop autoplay>
            </dotlottie-player>


            <h2 class="fw-bold mb-2 text-center">
                Sistem Informasi Statistik Sosial
            </h2>
            <p class="text-secondary text-center mx-auto mb-2" style="max-width:520px;">
                Menyediakan informasi statistik sosial untuk BPS Kabupaten/Kota se-Kalimantan Barat.
            </p>

            <p class="text-muted text-center small mb-4">
                Masuk menggunakan akun Google untuk mengakses fitur.
            </p>


            <!-- <button class="btn btn-google-login mb-2" data-bs-toggle="modal" data-bs-target="#loginModal">
                                                    <i class="fas fa-sign-in-alt"></i>
                                                    <span>Login</span>
                                                </button> -->

            <a href="{{ url('/auth/google') }}" class="btn btn-google-login py-3 mb-3">
                <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/c/c1/Google_%22G%22_logo.svg/960px-Google_%22G%22_logo.svg.png"
                    alt="Google Logo">
                <span>Login with Google</span>
            </a>

        </div>

    </div>




@endsection