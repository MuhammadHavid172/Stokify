<nav x-data="{ mobileOpen: false }" class="sticky top-0 z-50 bg-white/95 backdrop-blur-md border-b border-gray-200 transition-all duration-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-20 gap-4">
            
            <!-- Brand / Logo -->
            <a href="#beranda" class="flex items-center gap-2.5 flex-shrink-0 group">
                <div class="w-9 h-9 rounded-lg bg-[#166534] flex items-center justify-center text-white shadow-sm group-hover:scale-105 transition-transform font-black text-sm">
                    S
                </div>
                <div class="flex items-baseline">
                    <span class="text-xl font-black tracking-tight text-gray-900 font-sans">
                        Stokify
                    </span>
                    <span class="w-1.5 h-1.5 rounded-full bg-[#166534] ml-1"></span>
                </div>
            </a>

            <!-- Desktop Menu Links (Center) -->
            <div class="hidden lg:flex items-center space-x-1 xl:space-x-2">
                <a href="#beranda" class="px-3.5 py-2 text-xs font-semibold text-gray-700 hover:text-[#166534] rounded-lg hover:bg-[#DCFCE7]/50 transition">
                    Beranda
                </a>
                <a href="#tentang" class="px-3.5 py-2 text-xs font-semibold text-gray-700 hover:text-[#166534] rounded-lg hover:bg-[#DCFCE7]/50 transition">
                    Tentang Stokify
                </a>
                <a href="#fitur" class="px-3.5 py-2 text-xs font-semibold text-gray-700 hover:text-[#166534] rounded-lg hover:bg-[#DCFCE7]/50 transition">
                    Fitur Unggulan
                </a>
                <a href="#cara-kerja" class="px-3.5 py-2 text-xs font-semibold text-gray-700 hover:text-[#166534] rounded-lg hover:bg-[#DCFCE7]/50 transition">
                    Cara Kerja
                </a>
                <a href="#kontak" class="px-3.5 py-2 text-xs font-semibold text-gray-700 hover:text-[#166534] rounded-lg hover:bg-[#DCFCE7]/50 transition">
                    Kontak
                </a>
            </div>

            <!-- Right Controls: Auth Action Buttons -->
            <div class="hidden md:flex items-center gap-3">
                <a href="{{ route('login') }}" class="px-4 py-2 text-xs font-bold text-gray-700 hover:text-[#166534] transition">
                    Masuk
                </a>
                <a href="{{ route('register') }}" class="px-4 py-2.5 text-xs font-bold text-white bg-[#166534] hover:bg-[#14532D] rounded-lg shadow-sm hover:shadow transition flex items-center gap-1.5">
                    <span>Daftar Kios</span>
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                        <polyline points="12 5 19 12 12 19"></polyline>
                    </svg>
                </a>
            </div>

            <!-- Hamburger Button (Mobile) -->
            <div class="lg:hidden flex items-center">
                <button @click="mobileOpen = !mobileOpen" 
                        type="button" 
                        class="p-2 rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-100 transition focus:outline-none"
                        aria-label="Toggle navigation menu">
                    <svg x-show="!mobileOpen" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <line x1="4" y1="6" x2="20" y2="6"></line>
                        <line x1="4" y1="12" x2="20" y2="12"></line>
                        <line x1="4" y1="18" x2="20" y2="18"></line>
                    </svg>
                    <svg x-show="mobileOpen" x-cloak class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Drawer Menu -->
    <div x-show="mobileOpen" 
         x-cloak 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-2"
         class="lg:hidden bg-white border-b border-gray-200 px-4 pt-2 pb-6 space-y-3 shadow-lg">
        <div class="space-y-1">
            <a href="#beranda" @click="mobileOpen = false" class="block px-3 py-2 rounded-lg text-sm font-semibold text-gray-800 hover:bg-[#DCFCE7]/60 hover:text-[#166534]">Beranda</a>
            <a href="#tentang" @click="mobileOpen = false" class="block px-3 py-2 rounded-lg text-sm font-semibold text-gray-800 hover:bg-[#DCFCE7]/60 hover:text-[#166534]">Tentang Stokify</a>
            <a href="#fitur" @click="mobileOpen = false" class="block px-3 py-2 rounded-lg text-sm font-semibold text-gray-800 hover:bg-[#DCFCE7]/60 hover:text-[#166534]">Fitur Unggulan</a>
            <a href="#cara-kerja" @click="mobileOpen = false" class="block px-3 py-2 rounded-lg text-sm font-semibold text-gray-800 hover:bg-[#DCFCE7]/60 hover:text-[#166534]">Cara Kerja</a>
            <a href="#kontak" @click="mobileOpen = false" class="block px-3 py-2 rounded-lg text-sm font-semibold text-gray-800 hover:bg-[#DCFCE7]/60 hover:text-[#166534]">Kontak</a>
        </div>
        <div class="pt-3 border-t border-gray-100 flex flex-col gap-2">
            <a href="{{ route('login') }}" class="w-full text-center py-2.5 px-4 rounded-lg border border-gray-300 text-xs font-bold text-gray-700 hover:bg-gray-50">
                Masuk ke Akun
            </a>
            <a href="{{ route('register') }}" class="w-full text-center py-2.5 px-4 rounded-lg bg-[#166534] text-xs font-bold text-white hover:bg-[#14532D] shadow-sm">
                Daftar Kios Baru
            </a>
        </div>
    </div>
</nav>
