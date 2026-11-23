@props([
    'title' => 'Dashboard Admin',
])
<!DOCTYPE html>
<html lang="id" class="h-full bg-[#F6F7F5]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="turbo-cache-control" content="no-cache">

    <title>{{ $title ?? 'Dashboard Admin' }} - Stokify</title>

    <!-- Favicon Stokify -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="alternate icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    <!-- Fonts: Inter & Poppins -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|poppins:500,600,700,800&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body x-data="{ sidebarOpen: false }" class="min-h-full bg-[#F6F7F5] text-gray-900 antialiased font-sans">
    
    <!-- Outer Container -->
    <div class="min-h-screen flex flex-col lg:flex-row bg-[#F6F7F5]">
        
        <!-- Mobile Header Bar -->
        <header class="lg:hidden bg-white border-b border-gray-200 text-gray-900 px-4 py-3 flex items-center justify-between sticky top-0 z-40 shadow-xs">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-[#166534] text-white flex items-center justify-center font-black text-sm">
                    S
                </div>
                <div>
                    <span class="text-sm font-black tracking-tight text-[#166534] uppercase font-sans">STOKIFY</span>
                    <span class="text-[11px] text-gray-500 font-medium block -mt-1 truncate max-w-[150px]">
                        {{ auth()->user()->kios->nama_kios ?? 'Kios' }}
                    </span>
                </div>
            </div>

            <div class="flex items-center gap-1.5">
                <x-notification-lonceng role="admin" />
                <button @click="sidebarOpen = true" type="button" class="p-2 rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-100 transition" aria-label="Buka Menu Navigasi">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <line x1="3" y1="12" x2="21" y2="12" />
                        <line x1="3" y1="6" x2="21" y2="6" />
                        <line x1="3" y1="18" x2="21" y2="18" />
                    </svg>
                </button>
            </div>
        </header>

        <!-- Sidebar Backdrop (Mobile) -->
        <div x-show="sidebarOpen" 
             x-cloak
             @click="sidebarOpen = false" 
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-gray-900/50 backdrop-blur-xs z-50 lg:hidden transition-opacity"></div>

        <!-- Sidebar Navigation (Latar Hijau Tua Mewah Stokify) -->
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
               class="fixed inset-y-0 left-0 z-50 w-64 bg-[#14532D] text-white flex flex-col justify-between transition-transform duration-200 ease-in-out lg:sticky lg:top-0 lg:h-screen shrink-0 border-r border-[#166534] shadow-xl lg:shadow-none">
            
            <div class="p-4 flex-1 overflow-y-auto">
                <!-- Profil Kios & Nama Pemilik -->
                <div class="bg-white/10 backdrop-blur-xs border border-white/15 rounded-2xl p-3 flex items-center justify-between shadow-xs mb-4">
                    <div class="flex items-center gap-3 overflow-hidden">
                        <div class="w-10 h-10 rounded-xl bg-white text-[#14532D] font-black text-xs flex items-center justify-center shrink-0 shadow-xs">
                            {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                        </div>
                        <div class="overflow-hidden">
                            <p class="text-xs font-bold text-white truncate leading-tight">{{ auth()->user()->name }}</p>
                            <div class="flex items-center gap-1.5 mt-0.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 shrink-0"></span>
                                <p class="text-[11px] text-emerald-200 font-medium truncate" title="{{ auth()->user()->kios->nama_kios ?? 'Kios' }}">
                                    {{ auth()->user()->kios->nama_kios ?? 'Kios' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Close button in mobile -->
                    <button @click="sidebarOpen = false" class="lg:hidden p-1.5 rounded-lg text-emerald-200 hover:text-white hover:bg-white/10 transition" aria-label="Tutup Menu">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Kelompok Menu Navigasi Ramping (8 Menu Inti) -->
                <nav class="mt-4 space-y-4">
                    
                    <!-- KELOMPOK OPERASIONAL -->
                    <div class="space-y-1">
                        <p class="text-[10px] font-bold tracking-wider text-emerald-300/80 uppercase px-3 py-1">OPERASIONAL</p>

                        <!-- 1. Dashboard -->
                        <a href="{{ route('admin.dashboard') }}" 
                           data-turbo-preload
                           @click="sidebarOpen = false"
                           class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs transition-all duration-150 {{ request()->routeIs('admin.dashboard') ? 'bg-white text-[#14532D] font-bold shadow-md shadow-black/20' : 'text-emerald-100/90 hover:bg-white/10 hover:text-white font-medium' }}">
                            <svg class="w-4 h-4 {{ request()->routeIs('admin.dashboard') ? 'text-[#14532D]' : 'text-emerald-300/80' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="3" width="7" height="7" rx="1.5" />
                                <rect x="14" y="3" width="7" height="7" rx="1.5" />
                                <rect x="14" y="14" width="7" height="7" rx="1.5" />
                                <rect x="3" y="14" width="7" height="7" rx="1.5" />
                            </svg>
                            <span>Dashboard</span>
                        </a>

                        <!-- 2. Kasir POS -->
                        <a href="{{ route('kasir.index') }}" 
                           data-turbo-preload
                           @click="sidebarOpen = false"
                           class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs transition-all duration-150 text-emerald-100/90 hover:bg-white/10 hover:text-white font-medium">
                            <svg class="w-4 h-4 text-emerald-300/80" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                            <span>Kasir POS</span>
                        </a>

                        <!-- 3. Penjualan & Shift -->
                        <a href="{{ route('admin.penjualan.index') }}" 
                           data-turbo-preload
                           @click="sidebarOpen = false"
                           class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs transition-all duration-150 {{ request()->routeIs('admin.penjualan.*') || request()->routeIs('admin.shift.*') ? 'bg-white text-[#14532D] font-bold shadow-md shadow-black/20' : 'text-emerald-100/90 hover:bg-white/10 hover:text-white font-medium' }}">
                            <svg class="w-4 h-4 {{ request()->routeIs('admin.penjualan.*') || request()->routeIs('admin.shift.*') ? 'text-[#14532D]' : 'text-emerald-300/80' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                            </svg>
                            <span>Penjualan & Shift</span>
                        </a>
                    </div>

                    <!-- KELOMPOK INVENTORI -->
                    <div class="space-y-1 pt-2">
                        <p class="text-[10px] font-bold tracking-wider text-emerald-300/80 uppercase px-3 py-1">INVENTORI</p>

                        <!-- 4. Data Barang & Kategori -->
                        <a href="{{ route('admin.barang.index') }}" 
                           data-turbo-preload
                           @click="sidebarOpen = false"
                           class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs transition-all duration-150 {{ request()->routeIs('admin.barang.*') || request()->routeIs('admin.kategori.*') ? 'bg-white text-[#14532D] font-bold shadow-md shadow-black/20' : 'text-emerald-100/90 hover:bg-white/10 hover:text-white font-medium' }}">
                            <svg class="w-4 h-4 {{ request()->routeIs('admin.barang.*') || request()->routeIs('admin.kategori.*') ? 'text-[#14532D]' : 'text-emerald-300/80' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                            <span>Data Barang</span>
                        </a>

                        <!-- 5. Barang Masuk & Supplier -->
                        <a href="{{ route('admin.barang-masuk.index') }}" 
                           data-turbo-preload
                           @click="sidebarOpen = false"
                           class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs transition-all duration-150 {{ request()->routeIs('admin.barang-masuk.*') || request()->routeIs('admin.supplier.*') ? 'bg-white text-[#14532D] font-bold shadow-md shadow-black/20' : 'text-emerald-100/90 hover:bg-white/10 hover:text-white font-medium' }}">
                            <svg class="w-4 h-4 {{ request()->routeIs('admin.barang-masuk.*') || request()->routeIs('admin.supplier.*') ? 'text-[#14532D]' : 'text-emerald-300/80' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                            </svg>
                            <span>Barang Masuk</span>
                        </a>

                        <!-- 6. Penyesuaian Stok -->
                        <a href="{{ route('admin.penyesuaian.index') }}" 
                           data-turbo-preload
                           @click="sidebarOpen = false"
                           class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs transition-all duration-150 {{ request()->routeIs('admin.penyesuaian.*') ? 'bg-white text-[#14532D] font-bold shadow-md shadow-black/20' : 'text-emerald-100/90 hover:bg-white/10 hover:text-white font-medium' }}">
                            <svg class="w-4 h-4 {{ request()->routeIs('admin.penyesuaian.*') ? 'text-[#14532D]' : 'text-emerald-300/80' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            <span>Penyesuaian Stok</span>
                        </a>
                    </div>

                    <!-- KELOMPOK LAPORAN -->
                    <div class="space-y-1 pt-2">
                        <p class="text-[10px] font-bold tracking-wider text-emerald-300/80 uppercase px-3 py-1">LAPORAN</p>

                        <!-- 7. Laporan & Analisis -->
                        <a href="{{ route('admin.laporan.index') }}" 
                           data-turbo-preload
                           @click="sidebarOpen = false"
                           class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs transition-all duration-150 {{ request()->routeIs('admin.laporan.*') || request()->routeIs('admin.analisis.*') ? 'bg-white text-[#14532D] font-bold shadow-md shadow-black/20' : 'text-emerald-100/90 hover:bg-white/10 hover:text-white font-medium' }}">
                            <svg class="w-4 h-4 {{ request()->routeIs('admin.laporan.*') || request()->routeIs('admin.analisis.*') ? 'text-[#14532D]' : 'text-emerald-300/80' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            <span>Laporan & Analisis</span>
                        </a>
                    </div>

                    <!-- KELOMPOK PENGATURAN -->
                    <div class="space-y-1 pt-2">
                        <p class="text-[10px] font-bold tracking-wider text-emerald-300/80 uppercase px-3 py-1">PENGATURAN</p>

                        <!-- 8. Pengaturan Kios (Profil, Kasir, Log) -->
                        <a href="{{ route('admin.kios.index') }}" 
                           data-turbo-preload
                           @click="sidebarOpen = false"
                           class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs transition-all duration-150 {{ request()->routeIs('admin.kios.*') || request()->routeIs('admin.kasir.*') || request()->routeIs('admin.log.*') ? 'bg-white text-[#14532D] font-bold shadow-md shadow-black/20' : 'text-emerald-100/90 hover:bg-white/10 hover:text-white font-medium' }}">
                            <svg class="w-4 h-4 {{ request()->routeIs('admin.kios.*') || request()->routeIs('admin.kasir.*') || request()->routeIs('admin.log.*') ? 'text-[#14532D]' : 'text-emerald-300/80' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <circle cx="12" cy="12" r="3" />
                            </svg>
                            <span>Pengaturan Kios</span>
                        </a>

                        <!-- 9. Optimasi Kecepatan Sistem -->
                        <a href="{{ route('admin.optimasi') }}" 
                           @click="sidebarOpen = false"
                           title="Segarkan cache dan optimalkan kecepatan website"
                           class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs transition-all duration-150 text-emerald-100/90 hover:bg-white/10 hover:text-white font-medium">
                            <svg class="w-4 h-4 text-emerald-300/80" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            <span>Optimalkan Sistem</span>
                        </a>
                    </div>

                </nav>
            </div>

            <!-- Footer Sidebar: Logo & Tombol Logout -->
            <div class="p-4 border-t border-white/10 flex items-center justify-between bg-[#0F3E22]/70 shrink-0">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
                    <span class="text-xs font-black tracking-wider text-white uppercase font-sans">
                        STOKIFY
                    </span>
                </div>

                <form method="POST" action="{{ route('logout') }}" data-turbo="false">
                    @csrf
                    <button type="submit" title="Keluar dari sesi" class="p-1.5 rounded-lg text-emerald-300 hover:text-rose-300 hover:bg-white/10 transition">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </button>
                </form>
            </div>

        </aside>

        <!-- Main Content Shell -->
        <div class="flex-1 min-w-0 flex flex-col bg-[#F6F7F5]">
            <!-- Topbar Bersih -->
            <header class="bg-white border-b border-gray-200 px-4 sm:px-6 lg:px-8 py-3 flex items-center justify-between sticky top-0 z-30 shadow-xs">
                <div class="flex items-center gap-2 text-xs text-gray-500">
                    <a href="{{ route('admin.dashboard') }}" class="hover:text-gray-900 transition">Dashboard</a>
                    <span>/</span>
                    <span class="text-gray-900 font-semibold truncate">{{ $title ?? 'Halaman' }}</span>
                </div>

                <div class="flex items-center gap-2 sm:gap-3">
                    <!-- Notifikasi Lonceng Stok Menipis -->
                    <x-notification-lonceng role="admin" />

                    <!-- User Profile Dropdown / Badge -->
                    <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                        <button type="button" 
                                @click="open = !open" 
                                class="flex items-center gap-2 p-1 pl-2.5 rounded-lg bg-gray-50 hover:bg-gray-100 border border-gray-200 transition">
                            <span class="text-xs font-semibold text-gray-700 hidden sm:block">{{ auth()->user()->name }}</span>
                            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-[#DCFCE7] text-[#166534] uppercase">Admin</span>
                        </button>

                        <div x-show="open" 
                             x-cloak 
                             x-transition 
                             class="absolute right-0 mt-2 w-48 bg-white border border-gray-200 rounded-xl shadow-lg py-1 z-50">
                            <div class="px-4 py-2 border-b border-gray-100">
                                <p class="text-xs font-bold text-gray-900 truncate">{{ auth()->user()->name }}</p>
                                <p class="text-[11px] text-gray-500 truncate">{{ auth()->user()->kios->nama_kios ?? 'Kios' }}</p>
                            </div>
                            <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 px-4 py-2 text-xs text-gray-700 hover:bg-gray-50 font-medium transition">
                                <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                <span>Profil Akun</span>
                            </a>
                            <a href="{{ route('admin.optimasi') }}" class="flex items-center gap-2 px-4 py-2 text-xs text-emerald-700 hover:bg-emerald-50 font-medium transition">
                                <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                                <span>Optimalkan Sistem</span>
                            </a>
                            <form method="POST" action="{{ route('logout') }}" data-turbo="false">
                                @csrf
                                <button type="submit" class="w-full text-left flex items-center gap-2 px-4 py-2 text-xs text-rose-600 hover:bg-rose-50 font-medium transition">
                                    <svg class="w-4 h-4 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                    </svg>
                                    <span>Keluar</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main Page Content Container (max-w-7xl, mx-auto, px-4 sm:px-6 lg:px-8 py-6) -->
            <main class="flex-1 py-6 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto w-full">
                <x-flash-message />
                {{ $slot ?? '' }}
                @yield('content')
            </main>
        </div>

    </div>

    @stack('scripts')
</body>
</html>
