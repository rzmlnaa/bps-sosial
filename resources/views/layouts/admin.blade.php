<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard Kemiskinan Kalbar')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="icon"
        href="https://upload.wikimedia.org/wikipedia/commons/2/28/Lambang_Badan_Pusat_Statistik_%28BPS%29_Indonesia.svg"
        type="image/x-icon">
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="{{ asset('css/dashboard.css') }}" rel="stylesheet">

    <style>
        .logo-icon {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            /* PENTING */
            border-radius: 6px;
            /* opsional */
            background: #fff;
            /* opsional */
        }

        .logo-icon img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            /* AGAR LOGO TIDAK TERPOTONG */
        }
    </style>

    @stack('styles')
</head>

<body>

    <!-- Mobile Navbar -->
    <div class="mobile-nav d-lg-none">
        <div class="d-flex align-items-center gap-2">
            <div class="logo-icon">
                <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/2/28/Lambang_Badan_Pusat_Statistik_%28BPS%29_Indonesia.svg/960px-Lambang_Badan_Pusat_Statistik_%28BPS%29_Indonesia.svg.png"
                    alt="Logo BPS">
            </div>
            <span class="fw-bold text-navy">BPS Kalbar</span>
        </div>
        <button class="btn btn-link text-dark" id="sidebarToggle">
            <i class="fas fa-bars fa-lg"></i>
        </button>
    </div>

    <!-- Sidebar -->
    <nav class="sidebar client-sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="logo-icon">
                <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/2/28/Lambang_Badan_Pusat_Statistik_%28BPS%29_Indonesia.svg/960px-Lambang_Badan_Pusat_Statistik_%28BPS%29_Indonesia.svg.png"
                    alt="Logo BPS">
            </div>
            <div>
                <h5 class="mb-0 fw-bold" style="font-size: 1rem; color: var(--primary-navy);">Badan Pusat Statistik</h5>
                @auth
                    <small class="text-muted"
                        style="font-size: 0.7rem;">{{ Auth::user()->kabupaten->nama_kabupaten ?? '-' }}</small>
                @endauth
            </div>
        </div>
        @php
            $h = \Carbon\Carbon::now('Asia/Jakarta')->hour;
            $greeting = 'Selamat Pagi';
            if ($h >= 11 && $h < 15)
                $greeting = 'Selamat Siang';
            elseif ($h >= 15 && $h < 19)
                $greeting = 'Selamat Sore';
            elseif ($h >= 19)
                $greeting = 'Selamat Malam';
        @endphp
        <div class="px-4 mb-3">
            <span class="fw-bold d-block" style="color: var(--primary-navy); font-size: 0.9rem;">
                {{ $greeting }}@auth, {{ Auth::user()->name }}@endauth 👋
            </span>
        </div>
        <div class="py-3">


            @if(Auth::check() && Auth::user()->role === 'admin')
                <h6 class="px-4 text-xs font-weight-bold text-muted text-uppercase mb-2"
                    style="font-size: 0.75rem; letter-spacing: 0.05em;">Menu Admin</h6>

                <a href="{{ route('admin.dashboard') }}"
                    class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>

                <a href="{{ route('admin.users') }}"
                    class="nav-link {{ request()->routeIs('admin.users') ? 'active' : '' }}">
                    <i class="fas fa-users-cog"></i>
                    <span>Akun Pengguna</span>
                </a>

                <a href="{{ route('admin.admins') }}"
                    class="nav-link {{ request()->routeIs('admin.admins') ? 'active' : '' }}">
                    <i class="fas fa-user-shield"></i>
                    <span>Akun Admin</span>
                </a>

                <a href="{{ route('admin.kabupatens') }}"
                    class="nav-link {{ request()->routeIs('admin.kabupatens') ? 'active' : '' }}">
                    <i class="fas fa-map-marked-alt"></i>
                    <span>Master Wilayah</span>
                </a>
            @else
                <h6 class="px-4 text-xs font-weight-bold text-muted text-uppercase mb-2"
                    style="font-size: 0.75rem; letter-spacing: 0.05em;">Menu Utama</h6>

                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>


                <a href="{{ route('poverty') }}"
                    class="nav-link {{ request()->routeIs('poverty') || request()->routeIs('poverty.input') ? 'active' : '' }}">
                    <i class="fas fa-chart-bar"></i>
                    <span>Kemiskinan</span>
                </a>



                <a href="#submenu1" class="nav-link" data-bs-toggle="collapse" aria-expanded="false">
                    <i class="fas fa-layer-group"></i>
                    <div class="d-flex justify-content-between align-items-center w-100">
                        <span>Sub Kelompok</span>
                        <i class="fas fa-chevron-down ms-auto" style="font-size: 0.7rem; width: auto;"></i>
                    </div>
                </a>
                <div class="collapse" id="submenu1">
                    <ul class="nav flex-column ps-4 border-start ms-3 py-1">
                        <li class="nav-item">
                            <a href="#" class="nav-link d-flex align-items-center gap-2 py-2 text-sm text-muted">
                                <i class="fas fa-circle" style="font-size: 4px;"></i>
                                <span>Makanan</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#" class="nav-link d-flex align-items-center gap-2 py-2 text-sm text-muted">
                                <i class="fas fa-circle" style="font-size: 4px;"></i>
                                <span>Non-Makanan</span>
                            </a>
                        </li>
                    </ul>
                </div>

                <a href="#" class="nav-link">
                    <i class="fas fa-shopping-basket"></i>
                    <span>Komoditas</span>
                </a>




                <a href="#submenu2" id="menu-rentang-harga"
                    class="nav-link {{ request()->is('price-range*') || request()->is('verification*') ? 'active' : '' }}"
                    data-bs-toggle="collapse" aria-expanded="true">
                    <i class="fas fa-tags"></i>
                    <div class="d-flex justify-content-between align-items-center w-100">
                        <span>Rentang Harga</span>
                        <i class="fas fa-chevron-down ms-auto" style="font-size: 0.7rem;"></i>
                    </div>
                </a>

                <div class="collapse {{ request()->is('price-range*') || request()->is('verification*') ? 'show' : '' }}"
                    id="submenu2">
                    <ul class="nav flex-column ps-4 border-start ms-3 py-1">
                        <li class="nav-item">
                            <a href="{{ route('price-range.index') }}"
                                class="nav-link {{ request()->is('price-range*') ? 'active' : '' }}">
                                <i class="fas fa-table"></i>
                                <span>Visualisasi RH</span>
                            </a>
                        </li>
                        @if (auth()->check() == true)
                            @if(auth()->user()->status == 'active' && auth()->user()->kabupaten->kode_kab == '6100')
                                <li class="nav-item">
                                    <a href="{{ route('verification.index') }}"
                                        class="nav-link {{ request()->is('verification*') ? 'active' : '' }}">
                                        <i class="fas fa-clipboard-check"></i>
                                        <span>Verifikasi Harga</span>
                                    </a>
                                </li>
                            @endif
                        @endif

                    </ul>
                </div>





                <a href="#" class="nav-link">
                    <i class="fas fa-search-dollar"></i>
                    <span>Fenomena</span>
                </a>
            @endif

        </div>

        <div class="mt-auto p-4 border-top">
            @auth
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-gray-200 rounded-circle d-flex align-items-center justify-content-center"
                            style="width: 36px; height: 36px; background: #e2e8f0;">
                            <i class="fas fa-user text-muted"></i>
                        </div>
                        <div>
                            <p class="mb-0 fw-medium text-sm">{{ Auth::user()->name }}</p>
                            @if(Auth::user()->role !== 'admin')
                                <small class="text-muted"
                                    style="font-size: 0.75rem;">{{ Auth::user()->team ?? 'Tim Sosial' }}</small>
                            @else
                                <small class="text-muted" style="font-size: 0.75rem;">Admin</small>
                            @endif
                        </div>
                    </div>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" onclick="setTimeout(() => window.location.href = '/login', 50)"
                            class="btn btn-sm btn-outline-danger border-0">
                            <i class="fas fa-sign-out-alt"></i>
                        </button>
                    </form>
                </div>
            @else
                <button class="btn btn-google-login" data-bs-toggle="modal" data-bs-target="#loginModal">
                    <i class="fas fa-sign-in-alt"></i>
                    <span>Login</span>
                </button>
            @endauth
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content d-flex flex-column min-vh-100">
        <div class="flex-grow-1">
            @yield('content')
        </div>

        <footer class="mt-auto pt-4 border-top text-center text-muted pb-4">
            <small class="d-block mb-1">&copy; {{ date('Y') }} Badan Pusat Statistik Provinsi Kalimantan Barat. All
                rights reserved.</small>
            <small>Jika terdapat pertanyaan atau error - bug pada sistem, harap hubungi Developer dengan <a
                    href="https://wa.me/6289529406362" target="_blank" class="text-decoration-none fw-bold"
                    style="color: var(--bps-orange);">klik disini</a></small>
        </footer>
    </main>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/@dotlottie/player-component@latest/dist/dotlottie-player.mjs" type="module"></script>

    @if(session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: '{{ session('success') }}',
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true
            });
        </script>
    @endif

    @if(session('error'))
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: '{{ session('error') }}',
            });
        </script>
    @endif

    @if($errors->any())
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Validasi Gagal',
                text: '{{ $errors->first() }}',
            });
        </script>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Sidebar Toggle for Mobile
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.getElementById('sidebar');

            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function () {
                    sidebar.classList.toggle('active');
                });
            }

            // Close sidebar when clicking outside on mobile
            document.addEventListener('click', function (event) {
                const isClickInside = sidebar.contains(event.target) || sidebarToggle.contains(event.target);

                if (!isClickInside && sidebar.classList.contains('active') && window.innerWidth < 992) {
                    sidebar.classList.remove('active');
                }
            });
        });
    </script>

    <!-- Login Modal -->
    <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-body text-center">
                    <div class="mb-4">
                        <div class="logo-icon mx-auto mb-3" style="width: 64px; height: 64px;">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/2/28/Lambang_Badan_Pusat_Statistik_%28BPS%29_Indonesia.svg/960px-Lambang_Badan_Pusat_Statistik_%28BPS%29_Indonesia.svg.png"
                                alt="Logo BPS">
                        </div>
                        <h4 class="fw-bold text-navy">Selamat Datang</h4>
                        <p class="text-muted">Silakan login menggunakan akun Google untuk mengakses fitur sistem.</p>
                    </div>

                    <div class="login-divider"></div>

                    <a href="{{ url('/auth/google') }}" class="btn btn-google-login py-3">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/c/c1/Google_%22G%22_logo.svg/960px-Google_%22G%22_logo.svg.png"
                            alt="Google Logo">
                        <span>Login with Google</span>
                    </a>




                </div>
            </div>
        </div>
    </div>

    @stack('scripts')
</body>

</html>