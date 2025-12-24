<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard Kemiskinan Kalbar')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
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
                <h5 class="mb-0 fw-bold" style="font-size: 1rem; color: var(--primary-navy);">BPS Kalbar</h5>
                <small class="text-muted" style="font-size: 0.7rem;">Tim Sosial</small>
            </div>
        </div>

        <div class="py-3">
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

            <a href="#" class="nav-link">
                <i class="fas fa-tags"></i>
                <span>Rentang Harga</span>
            </a>

            <a href="#" class="nav-link">
                <i class="fas fa-search-dollar"></i>
                <span>Fenomena</span>
            </a>
        </div>

        <div class="mt-auto p-4 border-top">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-gray-200 rounded-circle d-flex align-items-center justify-content-center"
                    style="width: 36px; height: 36px; background: #e2e8f0;">
                    <i class="fas fa-user text-muted"></i>
                </div>
                <div>
                    <p class="mb-0 fw-medium text-sm">Admin BPS</p>
                    <small class="text-muted" style="font-size: 0.75rem;">Administrator</small>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        @yield('content')

        <footer class="mt-5 pt-4 border-top text-center text-muted pb-4">
            <small>&copy; {{ date('Y') }} Badan Pusat Statistik Provinsi Kalimantan Barat. All rights reserved.</small>
        </footer>
    </main>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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

    @stack('scripts')
</body>

</html>