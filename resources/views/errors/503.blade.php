<!DOCTYPE html>
<html lang="id" class="h-full bg-[#F6F7F5]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>503 - Pemeliharaan Sistem | Stokify</title>
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|poppins:500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-[#F6F7F5] text-gray-900 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-white rounded-2xl border border-gray-200 p-8 sm:p-10 text-center shadow-lg space-y-5">
        <div class="w-16 h-16 rounded-xl bg-[#DCFCE7] text-[#166534] border border-[#86EFAC] mx-auto flex items-center justify-center">
            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
            </svg>
        </div>

        <div>
            <span class="text-[11px] font-bold uppercase tracking-wider text-[#166534] bg-[#DCFCE7] px-3 py-1 rounded-full border border-[#86EFAC]">
                Pemeliharaan Sistem
            </span>
            <h1 class="text-2xl font-bold text-gray-900 mt-3">
                Layanan Sedang Ditingkatkan
            </h1>
            <p class="text-xs sm:text-sm text-gray-500 mt-2 leading-relaxed">
                Stokify sedang melakukan pemeliharaan rutin atau pembaruan sistem untuk performa yang lebih optimal. Kami akan segera kembali beroperasi normal.
            </p>
        </div>

        <div class="pt-2">
            <button type="button" 
                    onclick="window.location.reload()" 
                    class="inline-flex items-center justify-center w-full min-h-[44px] py-2.5 px-6 rounded-lg bg-[#166534] hover:bg-[#14532D] text-white font-semibold text-xs tracking-wider shadow-sm transition">
                Muat Ulang Halaman
            </button>
        </div>
    </div>
</body>
</html>
