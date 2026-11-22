@props(['maxWidth' => 'sm:max-w-md'])

<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Stokify') }} - Sistem Kasir & Manajemen Stok Kios</title>

        <!-- Favicon Stokify -->
        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
        <link rel="alternate icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

        <!-- Fonts: Inter & Poppins -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|poppins:500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased text-gray-900 min-h-screen relative bg-gray-900 overflow-x-hidden">
        <!-- Fullscreen Background Image with clean subtle overlay -->
        <div class="fixed inset-0 z-0 overflow-hidden pointer-events-none">
            <img src="{{ asset('images/auth-bg.jpg') }}" 
                 alt="Background Kios Stokify" 
                 class="w-full h-full object-cover object-center filter brightness-50 contrast-105" />
            <div class="absolute inset-0 bg-black/40 backdrop-blur-[2px]"></div>
        </div>

        <div class="relative z-10 min-h-screen flex flex-col justify-center items-center py-6 sm:py-10 px-4 sm:px-6">
            <!-- Brand Logo -->
            <div class="mb-4">
                <a href="/" class="flex items-center gap-2 px-4 py-2 rounded-xl bg-white/95 backdrop-blur-md shadow-md border border-gray-200 transition hover:scale-105">
                    <div class="w-8 h-8 rounded-lg bg-[#166534] text-white flex items-center justify-center font-black text-sm">
                        S
                    </div>
                    <div class="flex flex-col text-left">
                        <span class="text-base font-black tracking-tight text-gray-900 uppercase font-sans leading-tight">
                            STOKIFY
                        </span>
                        <span class="text-[9px] uppercase tracking-wider font-semibold text-gray-500">Manajemen Kios</span>
                    </div>
                </a>
            </div>

            <!-- Card Container: White card, rounded-2xl, border, shadow-lg -->
            <div class="w-full {{ $maxWidth }} bg-white border border-gray-200 shadow-xl rounded-2xl p-6 sm:p-8 text-gray-900 transition-all duration-200">
                {{ $slot }}
            </div>

            <!-- Single Back to Home Link -->
            <div class="mt-4 text-center">
                <a href="/" class="hover:text-white text-gray-300 transition inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-black/40 hover:bg-black/60 backdrop-blur-md border border-white/20 text-xs">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    <span>Kembali ke Beranda</span>
                </a>
            </div>
        </div>
    </body>
</html>
