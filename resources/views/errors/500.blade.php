<!DOCTYPE html>
<html lang="id" class="h-full bg-[#F6F7F5]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>500 - Terjadi Kesalahan Server | Stokify</title>
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|poppins:500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-[#F6F7F5] text-gray-900 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-white rounded-2xl border border-gray-200 p-8 sm:p-10 text-center shadow-lg space-y-5">
        <div class="w-16 h-16 rounded-xl bg-rose-50 text-rose-600 border border-rose-200 mx-auto flex items-center justify-center">
            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
        </div>

        <div>
            <span class="text-[11px] font-bold uppercase tracking-wider text-rose-800 bg-rose-50 px-3 py-1 rounded-full border border-rose-200">
                Galat 500: Server Internal
            </span>
            <h1 class="text-2xl font-bold text-gray-900 mt-3">
                Terjadi Kesalahan Server
            </h1>
            <p class="text-xs sm:text-sm text-gray-500 mt-2 leading-relaxed">
                Mohon maaf, terjadi kendala saat memproses permintaan Anda. Tim teknis kami telah mencatat peristiwa ini. Silakan coba beberapa saat lagi.
            </p>
        </div>

        <div class="pt-2">
            @auth
                @if (auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center justify-center w-full min-h-[44px] py-2.5 px-6 rounded-lg bg-[#166534] hover:bg-[#14532D] text-white font-semibold text-xs tracking-wider shadow-sm transition">
                        Kembali ke Dashboard Admin
                    </a>
                @else
                    <a href="{{ route('kasir.index') }}" class="inline-flex items-center justify-center w-full min-h-[44px] py-2.5 px-6 rounded-lg bg-[#166534] hover:bg-[#14532D] text-white font-semibold text-xs tracking-wider shadow-sm transition">
                        Kembali ke Layar Kasir
                    </a>
                @endif
            @else
                <a href="{{ url('/') }}" class="inline-flex items-center justify-center w-full min-h-[44px] py-2.5 px-6 rounded-lg bg-[#166534] hover:bg-[#14532D] text-white font-semibold text-xs tracking-wider shadow-sm transition">
                    Kembali ke Beranda
                </a>
            @endauth
        </div>
    </div>
</body>
</html>
