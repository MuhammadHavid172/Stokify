<!DOCTYPE html>
<html lang="id" class="h-full bg-[#F6F7F5]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>419 - Sesi Habis | Stokify</title>
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|poppins:500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-[#F6F7F5] text-gray-900 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-white rounded-2xl border border-gray-200 p-8 sm:p-10 text-center shadow-lg space-y-5">
        <div class="w-16 h-16 rounded-xl bg-blue-50 text-blue-600 border border-blue-200 mx-auto flex items-center justify-center">
            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>

        <div>
            <span class="text-[11px] font-bold uppercase tracking-wider text-blue-800 bg-blue-50 px-3 py-1 rounded-full border border-blue-200">
                Galat 419: Sesi Kedaluwarsa
            </span>
            <h1 class="text-2xl font-bold text-gray-900 mt-3">
                Sesi Habis, Muat Ulang Halaman
            </h1>
            <p class="text-xs sm:text-sm text-gray-500 mt-2 leading-relaxed">
                Masa aktif sesi atau token keamanan Anda telah berakhir karena tidak ada aktivitas dalam waktu lama. Silakan masuk kembali untuk melanjutkan.
            </p>
        </div>

        <!-- Catatan Khusus untuk Kasir -->
        <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 text-left flex items-start gap-3">
            <svg class="w-5 h-5 text-[#166534] shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div class="space-y-0.5 text-xs">
                <p class="font-bold text-gray-900">Informasi untuk Kasir:</p>
                <p class="text-gray-600 leading-relaxed">
                    Keranjang belanja transaksi Anda tetap aman tersimpan di perangkat ini dan tidak hilang setelah Anda masuk (login) kembali.
                </p>
            </div>
        </div>

        <div class="pt-2">
            <a href="{{ route('login') }}" class="inline-flex items-center justify-center w-full min-h-[44px] py-2.5 px-6 rounded-lg bg-[#166534] hover:bg-[#14532D] text-white font-semibold text-xs tracking-wider shadow-sm transition">
                Masuk Kembali ke Akun
            </a>
        </div>
    </div>
</body>
</html>
