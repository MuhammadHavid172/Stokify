<footer id="kontak" class="bg-gray-900 text-gray-300 border-t border-gray-800 py-12 lg:py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-10 lg:gap-14 pb-12 border-b border-gray-800">
            
            <!-- Kolom Brand & Deskripsi -->
            <div class="md:col-span-5 space-y-4">
                <a href="#beranda" class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-[#166534] flex items-center justify-center text-white text-xs font-bold shadow-xs">
                        S
                    </div>
                    <span class="text-xl font-black text-white tracking-tight">Stokify</span>
                </a>
                <p class="text-xs text-gray-400 leading-relaxed max-w-sm">
                    Platform sistem informasi manajemen kios multi-tenant modern. Transaksi kasir kilat, stok otomatis, dan laporan pembukuan usaha siap cetak.
                </p>
                <div class="pt-1 flex items-center gap-2 text-xs text-gray-400">
                    <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    <span>Email Resmi:</span>
                    <a href="mailto:stokify.app27@gmail.com" class="text-white hover:text-emerald-300 hover:underline font-semibold underline-offset-4 transition">
                        stokify.app27@gmail.com
                    </a>
                </div>
            </div>

            <!-- Kolom Navigasi -->
            <div class="md:col-span-3 space-y-3">
                <h4 class="text-xs font-bold uppercase tracking-wider text-white">Navigasi Halaman</h4>
                <ul class="space-y-2 text-xs text-gray-400">
                    <li><a href="#beranda" class="hover:text-white transition">Beranda</a></li>
                    <li><a href="#fitur" class="hover:text-white transition">Fitur Unggulan</a></li>
                    <li><a href="#cara-kerja" class="hover:text-white transition">Cara Kerja</a></li>
                </ul>
            </div>

            <!-- Kolom Pintu Masuk / Akses -->
            <div class="md:col-span-4 space-y-3">
                <h4 class="text-xs font-bold uppercase tracking-wider text-white">Pintu Masuk Kios</h4>
                <p class="text-xs text-gray-400">
                    Admin dan kasir login melalui satu pintu sistem terpadu Stokify.
                </p>
                <div class="flex items-center gap-2.5 pt-1">
                    <a href="{{ route('login') }}" class="px-4 py-2 text-xs font-bold text-gray-200 bg-gray-800 hover:bg-gray-700 border border-gray-700 rounded-lg transition shadow-xs">
                        Masuk
                    </a>
                    <a href="{{ route('register') }}" class="px-4 py-2 text-xs font-bold text-white bg-[#166534] hover:bg-[#14532D] rounded-lg transition shadow-xs">
                        Daftar Kios
                    </a>
                </div>
            </div>

        </div>

        <!-- Baris Bawah: Copyright & Info Tambahan -->
        <div class="pt-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-gray-400">
            <p>© 2026 Stokify. Dibuat sebagai proyek Sistem Informasi Manajemen.</p>
            <div class="flex items-center gap-4 text-xs text-gray-400">
                <span>Multi-Tenant Architecture</span>
                <span>&bull;</span>
                <span>Data 100% Terisolasi</span>
            </div>
        </div>
    </div>
</footer>
