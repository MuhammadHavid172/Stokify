<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Stokify - Sistem Kasir & Manajemen Stok Kios</title>
    <meta name="description" content="Stokify adalah sistem informasi manajemen kios multi-tenant. Kasir cepat, stok otomatis real-time, dan laporan siap cetak dalam satu aplikasi untuk kios Anda.">

    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="Stokify - Sistem Kasir & Manajemen Stok Kios">
    <meta property="og:description" content="Sistem informasi manajemen kios multi-tenant: kasir cepat, stok otomatis real-time, dan laporan siap cetak dalam satu aplikasi.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:image" content="{{ asset('favicon.svg') }}">

    <!-- Favicon Stokify -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="alternate icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    <!-- Fonts: Inter & Poppins -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|poppins:500,600,700,800&display=swap" rel="stylesheet" />

    <!-- Scripts and Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-[#F6F7F5] text-gray-900 selection:bg-[#DCFCE7] selection:text-[#166534]">
    <!-- Navbar -->
    <x-landing.navbar />

    <!-- Main Content -->
    <main>
        {{ $slot }}
    </main>

    <!-- Footer -->
    <x-landing.footer />
</body>
</html>
