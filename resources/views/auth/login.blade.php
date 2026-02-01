@extends('layouts.admin')

@section('title', 'Login')

@section('content')
    <div class="d-flex justify-content-center align-items-center" style="min-height: calc(100vh - 180px);">

        <div class="text-center" style="max-width: 720px;">



            <dotlottie-player src="{{ asset('statistik-animasi.json') }}" background="transparent" speed="1"
                style="display:block; margin:0 auto; width:100%; max-width:500px;" loop autoplay>
            </dotlottie-player>


            <h2 class="fw-bold mb-2">
                Sistem Informasi Statistika Sosial
            </h2>


            <p class="text-muted mb-4">
                Badan Pusat Statistik Provinsi Kalimantan Barat
            </p>


            <p class="text-secondary mb-5">
                Sistem ini menyajikan informasi statistika sosial yang dapat diakses oleh BPS Kabupaten/Kota di Provinsi
                Kalimantan Barat. <br>
                Untuk mengakses fitur lanjutan, silakan login menggunakan akun Google.
            </p>



        </div>

    </div>




@endsection