<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Sistem Informasi BPS')</title>

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
    <link rel="icon"
        href="{{ $logoUrl }}"
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

    <!-- TAILWIND (CDN for this specific page if needed as complete-profile used tailwind classes) -->
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6;
            /* Tailwind gray-100 */
        }
    </style>

    @stack('styles')
</head>

<body class="bg-gray-50">

    <main>
        @yield('content')
    </main>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>

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

    @stack('scripts')
</body>

</html>