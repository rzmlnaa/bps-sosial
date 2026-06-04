<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard Kemiskinan Kalbar')</title>

    @php
        $logoMenu = \App\Models\DynamicMenu::where('type', 'logo')->first();
        $logoUrl = 'https://upload.wikimedia.org/wikipedia/commons/2/28/Lambang_Badan_Pusat_Statistik_%28BPS%29_Indonesia.svg';
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

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="icon" href="{{ $logoUrl }}" type="image/x-icon">
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
                <img src="{{ $logoUrl }}" alt="Logo BPS"
                    onerror="this.onerror=null; this.src='https://upload.wikimedia.org/wikipedia/commons/2/28/Lambang_Badan_Pusat_Statistik_%28BPS%29_Indonesia.svg';">
            </div>
            <span class="fw-bold text-navy">Sistem Informasi Sosial Kalbar</span>
        </div>
        <button class="btn btn-link text-dark" id="sidebarToggle">
            <i class="fas fa-bars fa-lg"></i>
        </button>
    </div>

    <!-- Sidebar -->
    <nav class="sidebar client-sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="logo-icon">
                <img src="{{ $logoUrl }}" alt="Logo BPS"
                    onerror="this.onerror=null; this.src='https://upload.wikimedia.org/wikipedia/commons/2/28/Lambang_Badan_Pusat_Statistik_%28BPS%29_Indonesia.svg';">
            </div>
            <div>
                <h5 class="mb-0 fw-bold" style="font-size: 1rem; color: var(--primary-navy);">SISOKA</h5>
                <small class="text-muted" style="font-size: 0.7rem;">BPS Prov. Kalimantan Barat</small>
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
            <small class="text-muted d-block">

                @auth
                    <small class="text-muted" style="font-size: 0.7rem;">Team BPS
                        {{ Auth::user()->kabupaten->nama_kabupaten ?? '-' }}</small>
                @endauth
            </small>
        </div>
        <div class="py-3">


            @if(Auth::check() && Auth::user()->role === 'admin')
                <h6 class="px-4 text-xs font-weight-bold text-muted text-uppercase mb-2"
                    style="font-size: 0.75rem; letter-spacing: 0.05em;">Menu Admin</h6>

                <a href="{{ route('admin.dashboard') }}"
                    class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="fa fa-dashboard"></i>
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

                <a href="{{ route('admin.dynamic-menus.index') }}"
                    class="nav-link {{ request()->routeIs('admin.dynamic-menus.*') ? 'active' : '' }}">
                    <i class="fas fa-list-alt"></i>
                    <span>Menu Dinamis</span>
                </a>
            @else
                <h6 class="px-4 text-xs font-weight-bold text-muted text-uppercase mb-2"
                    style="font-size: 0.75rem; letter-spacing: 0.05em;">Menu Utama</h6>

                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="fas fa-dashboard"></i>
                    <span>Dashboard</span>
                </a>

                @if(auth()->check() && auth()->user()->role === 'user')
                    <a href="{{ route('my-team.index') }}"
                        class="nav-link {{ request()->routeIs('my-team.index') ? 'active' : '' }}">
                        <i class="fas fa-users"></i>
                        <span>Anggota Tim</span>
                    </a>
                @endif

                @if(auth()->check() && auth()->user()->kabupaten->kode_kab == '6100')
                    <a href="{{ route('wilayah.index') }}"
                        class="nav-link {{ request()->routeIs('wilayah.*') ? 'active' : '' }}">
                        <i class="fas fa-map-marked-alt"></i>
                        <span>Kelola Wilayah</span>
                    </a>
                @endif

                <a href="{{ route('poverty') }}"
                    class="nav-link {{ request()->routeIs('poverty') || request()->routeIs('poverty.input') ? 'active' : '' }}">
                    <i class="fas fa-chart-bar"></i>
                    <span>Kemiskinan</span>
                </a>

                <a href="{{ route('seruti.index') }}" class="nav-link {{ request()->routeIs('seruti.*') ? 'active' : '' }}">
                    <i class="fas fa-chart-pie"></i>
                    <span>Seruti</span>
                </a>

                <a href="#submenuDescan" id="menu-descan"
                    class="nav-link {{ request()->routeIs('desa-cantik.*') ? 'active' : '' }}" data-bs-toggle="collapse"
                    aria-expanded="true">
                    <i class="fas fa-seedling"></i>
                    <div class="d-flex justify-content-between align-items-center w-100">
                        <span>Desa Cantik</span>
                        <i class="fas fa-chevron-down ms-auto" style="font-size: 0.7rem;"></i>
                    </div>
                </a>

                <div class="collapse {{ request()->routeIs('desa-cantik.*') ? 'show' : '' }}" id="submenuDescan">
                    <ul class="nav flex-column ps-4 border-start ms-3 py-1">
                        <li class="nav-item">
                            <a href="{{ route('desa-cantik.index') }}"
                                class="nav-link {{ request()->routeIs('desa-cantik.kelola') || request()->routeIs('desa-cantik.index') ? 'active' : '' }}">
                                <i class="fas fa-chart-line"></i>
                                <span>Visualisasi</span>
                            </a>
                        </li>
                        @if (auth()->check())


                            <li class="nav-item">
                                <a href="{{ route('desa-cantik.peserta') }}"
                                    class="nav-link {{ request()->routeIs('desa-cantik.peserta') ? 'active' : '' }}">
                                    <i class="fas fa-users"></i>
                                    <span>Peserta Desa</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('desa-cantik.progress') }}"
                                    class="nav-link {{ request()->routeIs('desa-cantik.progress.*') || request()->routeIs('desa-cantik.progress') ? 'active' : '' }}">
                                    <i class="fas fa-tasks"></i>
                                    <span>Progress Kegiatan</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('desa-cantik.penilaian') }}"
                                    class="nav-link {{ request()->routeIs('desa-cantik.penilaian.*') || request()->routeIs('desa-cantik.penilaian') ? 'active' : '' }}">
                                    <i class="fas fa-star"></i>
                                    <span>Penilaian</span>
                                </a>
                            </li>
                        @endif

                    </ul>
                </div>


                <a href="#submenu2" id="menu-rentang-harga"
                    class="nav-link {{ request()->is('price-range*') || request()->routeIs('price-range.*') || request()->routeIs('verification.*') ? 'active' : '' }}"
                    data-bs-toggle="collapse" aria-expanded="true">
                    <i class="fas fa-tags"></i>
                    <div class="d-flex justify-content-between align-items-center w-100">
                        <span>Rentang Harga</span>
                        <i class="fas fa-chevron-down ms-auto" style="font-size: 0.7rem;"></i>
                    </div>
                </a>

                <div class="collapse {{ request()->is('price-range*') || request()->routeIs('price-range.*') || request()->routeIs('verification.*') ? 'show' : '' }}"
                    id="submenu2">
                    <ul class="nav flex-column ps-4 border-start ms-3 py-1">
                        <li class="nav-item">
                            <a href="{{ route('price-range.index') }}"
                                class="nav-link {{ request()->is('price-range*') ? 'active' : '' }}">
                                <i class="fas fa-table"></i>
                                <span>Visualisasi</span>
                            </a>
                        </li>
                        @if (auth()->check() == true)
                            @if(auth()->user()->status == 'active' && auth()->user()->kabupaten->kode_kab == '6100')
                                <li class="nav-item">
                                    <a href="{{ route('verification.index') }}"
                                        class="nav-link {{ request()->routeIs('verification.*') ? 'active' : '' }}">
                                        <i class="fas fa-clipboard-check"></i>
                                        <span>Verifikasi</span>
                                    </a>
                                </li>
                            @endif
                        @endif

                    </ul>
                </div>


                <a href="#submenu3" id="menu-fenomena"
                    class="nav-link {{request()->is('verification-fenomena*') || request()->is('fenomena*') || request()->routeIs('pra-ekspor.*') ? 'active' : '' }}"
                    data-bs-toggle="collapse" aria-expanded="true">
                    <i class="fas fa-newspaper"></i>
                    <div class="d-flex justify-content-between align-items-center w-100">
                        <span>Fenomena</span>
                        <i class="fas fa-chevron-down ms-auto" style="font-size: 0.7rem;"></i>
                    </div>
                </a>

                <div class="collapse {{ request()->routeIs('fenomena.*') || request()->routeIs('fenomena.verification.*') || request()->routeIs('pra-ekspor.*') ? 'show' : '' }}"
                    id="submenu3">
                    <ul class="nav flex-column ps-4 border-start ms-3 py-1">
                        <li class="nav-item">
                            <a href="{{ route('fenomena.visualisasi') }}"
                                class="nav-link {{ request()->routeIs('fenomena.visualisasi') ? 'active' : '' }}">
                                <i class="fas fa-chart-line"></i>
                                <span>Visualisasi</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('fenomena.index') }}"
                                class="nav-link {{ request()->has('creator') || request()->has('search') || request()->routeIs('fenomena.kelola') || request()->routeIs('fenomena.create') || request()->routeIs('fenomena.index') && !request()->has('creator') || (request()->routeIs('fenomena.show') && request()->query('from') != 'verification') ? 'active' : '' }}">
                                <i class="far fa-newspaper"></i>
                                <span>Informasi</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('fenomena.contributor') }}"
                                class="nav-link {{ request()->routeIs('fenomena.contributor') ? 'active' : '' }}">
                                <i class="fas fa-trophy"></i>
                                <span>Kontributor</span>
                            </a>
                        </li>

                        @if (auth()->check() == true)
                            @if(auth()->user()->status == 'active' && auth()->user()->kabupaten->kode_kab == '6100')
                                <li class="nav-item">
                                    <a href="{{ route('fenomena.verification.index') }}"
                                        class="nav-link {{ request()->routeIs('fenomena.verification.*') || (request()->routeIs('fenomena.show') && request()->query('from') == 'verification') ? 'active' : '' }}">
                                        <i class="fas fa-clipboard-check"></i>
                                        <span>Verifikasi</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('pra-ekspor.index') }}"
                                        class="nav-link {{ request()->routeIs('pra-ekspor.*') ? 'active' : '' }}">
                                        <i class="fas fa-file-export"></i>
                                        <span>Pra Ekspor</span>
                                    </a>
                                </li>
                            @endif
                        @endif
                    </ul>
                </div>

                <a href="{{ route('indikator-makro.index') }}"
                    class="nav-link {{ request()->routeIs('indikator-makro.*') ? 'active' : '' }}">
                    <i class="fas fa-chart-area"></i>
                    <span>Indikator Makro</span>
                </a>




                @php
                    $dynamicMenus = \App\Models\DynamicMenu::whereNull('parent_id')
                        ->whereNotIn('type', ['panduan_pengguna', 'logo', 'video_panduan'])
                        ->where('is_active', true)
                        ->with([
                            'children' => function ($q) {
                                $q->where('is_active', true)->orderBy('order_number');
                            }
                        ])
                        ->orderBy('order_number')
                        ->get();

                    if (!auth()->check()) {
                        // Filter out spreadsheets with allow_edit enabled for guest users
                        $dynamicMenus = $dynamicMenus->filter(function ($menu) {
                            $meta = is_array($menu->meta) ? $menu->meta : json_decode($menu->meta ?? '[]', true);
                            if ($menu->type === 'spreadsheet' && !empty($meta['allow_edit'])) {
                                return false;
                            }
                            return true;
                        });

                        foreach ($dynamicMenus as $menu) {
                            $menu->setRelation('children', $menu->children->filter(function ($child) {
                                $childMeta = is_array($child->meta) ? $child->meta : json_decode($child->meta ?? '[]', true);
                                if ($child->type === 'spreadsheet' && !empty($childMeta['allow_edit'])) {
                                    return false;
                                }
                                return true;
                            }));
                        }
                    }
                @endphp

                @foreach($dynamicMenus as $menu)
                    @if($menu->children->count() > 0)
                        @php
                            $childPatterns = $menu->children->pluck('slug')->map(function ($slug) {
                                return 'menu/' . $slug;
                            })->toArray();
                            $isDropdownActive = request()->is($childPatterns);
                        @endphp
                        <a href="#dynamicSubmenu{{ $menu->id }}" id="menu-dynamic-{{ $menu->id }}"
                            class="nav-link {{ $isDropdownActive ? 'active' : '' }}" data-bs-toggle="collapse"
                            aria-expanded="{{ $isDropdownActive ? 'true' : 'false' }}">
                            <i class="fas fa-folder"></i>
                            <div class="d-flex justify-content-between align-items-center w-100">
                                <span>{{ $menu->name }}</span>
                                <i class="fas fa-chevron-down ms-auto" style="font-size: 0.7rem;"></i>
                            </div>
                        </a>
                        <div class="collapse {{ $isDropdownActive ? 'show' : '' }}" id="dynamicSubmenu{{ $menu->id }}">
                            <ul class="nav flex-column ps-4 border-start ms-3 py-1">

                                @foreach($menu->children as $child)
                                    <li class="nav-item">
                                        <a href="{{ route('dynamic-menu.show', $child->slug) }}"
                                            class="nav-link {{ request()->is('menu/' . $child->slug) ? 'active' : '' }}">
                                            <i class="fas fa-file-alt"></i>
                                            <span>{{ $child->name }}</span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @else
                        <a href="{{ route('dynamic-menu.show', $menu->slug) }}"
                            class="nav-link {{ request()->is('menu/' . $menu->slug) ? 'active' : '' }}">
                            <i class="fas fa-file-alt"></i>
                            <span>{{ $menu->name }}</span>
                        </a>
                    @endif
                @endforeach

                <h6 class="px-4 text-xs font-weight-bold text-muted text-uppercase mt-4 mb-2"
                    style="font-size: 0.75rem; letter-spacing: 0.05em;">Bantuan</h6>
                <a href="{{ route('panduan') }}" class="nav-link {{ request()->routeIs('panduan') ? 'active' : '' }}">
                    <i class="fas fa-book-open"></i>
                    <span>Panduan Pengguna</span>
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
                <button class="btn btn-google-login" onclick="window.location.href = '/login'">
                    <i class="fas fa-sign-in-alt"></i>
                    <span>Login</span>
                </button>
            @endauth
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content d-flex flex-column min-vh-100">
        <div class="flex-grow-1 mb-2">
            @yield('content')
        </div>

        <footer class="mt-auto pt-4 border-top text-center text-muted pb-0">
            @php
                $startYear = 2026; // tahun pertama web di deploy
                $currentYear = date('Y');
            @endphp

            <small class="d-block mb-1">
                &copy; {{ $startYear == $currentYear ? $startYear : $startYear . ' - ' . $currentYear }}
                Badan Pusat Statistik Provinsi Kalimantan Barat. All rights reserved.
            </small>
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
                text: @json(session('success')),
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true
            });
        </script>
    @endif

    @if(session('warning'))
        <script>
            Swal.fire({
                icon: 'warning',
                title: 'Perhatian!',
                html: @json(session('warning')),
            });
        </script>
    @endif

    @if(session('error'))
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: @json(session('error')),
            });
        </script>
    @endif

    @if($errors->any())
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Validasi Gagal',
                text: @json($errors->first()),
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