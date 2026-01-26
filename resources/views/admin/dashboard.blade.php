@extends('layouts.admin')

@section('title', 'Dashboard Admin')

@section('content')
    <div class="container-fluid py-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-body text-center p-5">
                        <div class="mb-4">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/2/28/Lambang_Badan_Pusat_Statistik_%28BPS%29_Indonesia.svg/960px-Lambang_Badan_Pusat_Statistik_%28BPS%29_Indonesia.svg.png"
                                alt="Logo BPS" style="height: 80px;">
                        </div>

                        <h2 class="fw-bold text-navy mb-3">Selamat Datang, {{ Auth::user()->name }}</h2>
                        <p class="text-muted mb-0">Anda login sebagai Administrator.</p>
                        <p class="text-muted">Silakan gunakan menu di sebelah kiri untuk mengelola sistem.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection