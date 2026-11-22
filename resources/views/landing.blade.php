<x-landing-layout>

    <!-- HERO SECTION -->
    <section id="beranda" class="relative overflow-hidden bg-[#F6F7F5] min-h-[600px] lg:min-h-[660px] flex items-center pt-8 pb-16 lg:pt-12 lg:pb-20">
        
        <!-- Foto Kanan Full-Bleed Tanpa Bingkai dengan Gradasi Menyatu -->
        <div class="hidden lg:block absolute inset-y-0 right-0 w-[50%] xl:w-[52%] h-full pointer-events-none z-0 overflow-hidden">
            @if (file_exists(public_path('images/kios-hero.jpg')))
                <img src="{{ asset('images/kios-hero.jpg') }}?v={{ filemtime(public_path('images/kios-hero.jpg')) }}" 
                     alt="Interior Kios Modern" 
                     class="w-full h-full object-cover object-center"
                     loading="lazy"
                     width="800"
                     height="600">
            @endif
            
            <div class="absolute inset-y-0 left-0 w-36 xl:w-56 bg-gradient-to-r from-[#F6F7F5] via-[#F6F7F5]/85 to-transparent"></div>
            <div class="absolute inset-x-0 top-0 h-16 bg-gradient-to-b from-[#F6F7F5] to-transparent"></div>
            <div class="absolute inset-x-0 bottom-0 h-16 bg-gradient-to-t from-[#F6F7F5] to-transparent"></div>
        </div>

        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
                
                <!-- Sisi Kiri: Editorial Headline, Subtitle, CTA Buttons -->
                <div class="lg:col-span-7 xl:col-span-6 space-y-6">
                    
                    <!-- Tag Pill Atas (Gaya Forest) -->
                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-[#166534]/10 border border-[#166534]/30 text-xs font-semibold text-[#166534] w-fit shadow-2xs">
                        <span class="w-2 h-2 rounded-full bg-[#166534]"></span>
                        <span class="tracking-wide uppercase text-[11px] font-bold">SISTEM INFORMASI MANAJEMEN KIOS MULTI-TENANT</span>
                    </div>

                    <!-- Main Headline Sesuai Spesifikasi -->
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black text-[#111827] tracking-tight leading-[1.12]">
                        Kelola Kios Lebih Rapi, <br>
                        <span class="text-[#166534]">Stok Selalu Akurat</span>
                    </h1>

                    <!-- Deskripsi Pengantar Resmi -->
                    <p class="text-base sm:text-lg text-[#14532D] max-w-xl leading-relaxed">
                        Kasir cepat, stok otomatis, dan laporan siap cetak dalam satu aplikasi untuk kios Anda.
                    </p>

                    <!-- Tombol Aksi Hero (Daftar Kios Gratis & Lihat Fitur) -->
                    <div class="pt-1 flex flex-col sm:flex-row items-stretch sm:items-center gap-3 max-w-md">
                        <a href="{{ route('register') }}" 
                           class="min-h-[44px] px-8 py-3.5 bg-[#166534] hover:bg-[#14532D] text-[#DCFCE7] text-xs font-bold rounded-full shadow-sm hover:shadow transition transform hover:-translate-y-0.5 flex items-center justify-center gap-2 text-center border border-[#166534]">
                            <span>Daftar Kios Gratis</span>
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                                <polyline points="12 5 19 12 12 19"></polyline>
                            </svg>
                        </a>
                        <a href="#fitur" 
                           class="min-h-[44px] px-6 py-3.5 bg-white hover:bg-[#DCFCE7]/40 border border-[#166534]/30 text-[#166534] hover:text-[#111827] text-xs font-bold rounded-full transition flex items-center justify-center text-center shadow-2xs">
                            Lihat Fitur
                        </a>
                    </div>

                    <!-- Keterangan Jujur Target Pengguna -->
                    <div class="flex items-center gap-2.5 pt-1 text-xs text-[#4B5563]">
                        <svg class="w-4 h-4 text-[#166534]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        <span>Dirancang khusus untuk kios sembako, kelontong, dan toko mandiri.</span>
                    </div>

                    <!-- Foto Versi Mobile -->
                    <div class="block lg:hidden pt-4">
                        <div class="w-full h-60 sm:h-72 rounded-3xl overflow-hidden relative shadow-md">
                            @if (file_exists(public_path('images/kios-hero.jpg')))
                                <img src="{{ asset('images/kios-hero.jpg') }}?v={{ filemtime(public_path('images/kios-hero.jpg')) }}" 
                                     alt="Interior Kios Modern" 
                                     class="w-full h-full object-cover"
                                     loading="lazy"
                                     width="600"
                                     height="360">
                            @endif
                            <div class="absolute inset-0 bg-gradient-to-t from-black/30 via-transparent to-transparent"></div>
                        </div>
                    </div>

                </div>

                <!-- Spacer Kanan Desktop -->
                <div class="hidden lg:flex lg:col-span-5 xl:col-span-6 items-center justify-center min-h-[380px]"></div>

            </div>

        </div>
    </section>

    <!-- SECTION 2: KEUNGGULAN UTAMA (OVERLAPPING SECTION / STACKING CARDS) -->
    <section id="tentang" class="py-16 sm:py-24 bg-white border-y border-[#E5E7EB]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-14 items-start">
                
                <!-- Sisi Kiri: Tag, Editorial Headline, Teks Penjelasan, 3 Poin, & CTA (Sticky Menempel saat Scroll) -->
                <div class="lg:col-span-5 space-y-6 lg:sticky lg:top-28 self-start">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#166534]/10 text-xs font-bold text-[#166534] uppercase tracking-wider">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#166534]"></span>
                        SOLUSI PRAKTIS OPERASIONAL
                    </div>

                    <h2 class="text-3xl sm:text-4xl lg:text-5xl font-black text-[#111827] tracking-tight leading-[1.15]">
                        Mengapa Memilih Stokify?
                    </h2>

                    <p class="teks-penjelasan text-sm text-[#4B5563] leading-relaxed">
                        Pengelolaan warung kelontong sering terkendala catatan manual yang tercecer, salah hitung uang kembalian, selisih laci kasir saat pergantian jam kerja, serta kehabisan stok barang tanpa disadari. Stokify menghadirkan sistem kasir terpadu yang dirancang khusus untuk ritel mandiri.
                    </p>

                    <div class="space-y-3.5 pt-1">
                        <div class="flex items-start gap-3">
                            <div class="w-6 h-6 rounded-full bg-[#166534]/15 text-[#166534] flex items-center justify-center font-bold text-xs mt-0.5 flex-shrink-0">✓</div>
                            <div>
                                <h4 class="text-sm font-bold text-[#111827]">Kasir Cepat</h4>
                                <p class="text-xs text-[#4B5563]">Input belanjaan pembeli dan kembalian terhitung otomatis.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <div class="w-6 h-6 rounded-full bg-[#166534]/15 text-[#166534] flex items-center justify-center font-bold text-xs mt-0.5 flex-shrink-0">✓</div>
                            <div>
                                <h4 class="text-sm font-bold text-[#111827]">Stok Otomatis</h4>
                                <p class="text-xs text-[#4B5563]">Stok terpotong saat terjual dan bertambah saat barang masuk.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <div class="w-6 h-6 rounded-full bg-[#166534]/15 text-[#166534] flex items-center justify-center font-bold text-xs mt-0.5 flex-shrink-0">✓</div>
                            <div>
                                <h4 class="text-sm font-bold text-[#111827]">Data Kios Aman</h4>
                                <p class="text-xs text-[#4B5563]">Setiap kios memiliki ruang data sendiri yang terpisah.</p>
                            </div>
                        </div>
                    </div>

                    <div class="pt-2 flex flex-wrap items-center gap-3">
                        <a href="{{ route('register') }}" 
                           class="min-h-[44px] px-7 py-3 bg-[#166534] hover:bg-[#14532D] text-white text-xs font-bold rounded-full shadow-xs transition inline-flex items-center gap-2">
                            <span>Daftar Kios Sekarang</span>
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                                <polyline points="12 5 19 12 12 19"></polyline>
                            </svg>
                        </a>
                        <a href="#fitur" 
                           class="min-h-[44px] px-5 py-3 text-[#166534] hover:text-[#111827] text-xs font-bold rounded-full hover:bg-[#DCFCE7]/30 transition inline-flex items-center">
                            Lihat Fitur Lengkap
                        </a>
                    </div>
                </div>

                <!-- Sisi Kanan: 3 Kartu yang Menumpuk Bertahap (Overlapping Section / Stacking Cards) -->
                <div class="lg:col-span-7 space-y-8 sm:space-y-12 pb-10">
                    
                    <!-- KARTU 1: Real-time (Kasir Cepat) -->
                    <div class="sticky top-24 sm:top-28 z-10 bg-white rounded-3xl border border-gray-200/90 shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden">
                        <div class="grid grid-cols-1 sm:grid-cols-12 min-h-[320px] sm:min-h-[340px]">
                            <div class="sm:col-span-7 p-6 sm:p-8 flex flex-col justify-between space-y-4">
                                <div>
                                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#166534]/10 text-xs font-bold text-[#166534] mb-3">
                                        <span>⚡</span>
                                        <span>Real-time</span>
                                    </div>
                                    <h3 class="text-xl sm:text-2xl font-black text-gray-900 tracking-tight">Kasir Cepat</h3>
                                    <p class="text-xs sm:text-sm text-gray-600 leading-relaxed mt-2">
                                        Input belanjaan pembeli dan kembalian terhitung otomatis. Dilengkapi dukungan barcode scanner instan serta cetak struk belanja thermal 58mm/80mm tanpa jeda.
                                    </p>
                                </div>
                                <div class="pt-2 border-t border-gray-100 flex items-center justify-between text-xs font-bold text-[#166534]">
                                    <span class="flex items-center gap-1.5">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#166534]"></span>
                                        Transaksi Kilat
                                    </span>
                                    <span class="text-gray-400 font-normal">Layar POS Kasir</span>
                                </div>
                            </div>
                            <div class="sm:col-span-5 relative min-h-[180px] sm:min-h-full overflow-hidden bg-gray-100">
                                <img src="{{ asset('images/landing/kios-kayu.jpg') }}" 
                                     alt="Kasir Cepat Stokify" 
                                     class="absolute inset-0 w-full h-full object-cover object-center"
                                     loading="lazy">
                                <div class="absolute inset-0 bg-gradient-to-t sm:bg-gradient-to-r from-white/95 sm:from-white/90 via-transparent to-transparent"></div>
                            </div>
                        </div>
                    </div>

                    <!-- KARTU 2: 100% Terpisah (Stok Otomatis) -->
                    <div class="sticky top-32 sm:top-36 z-20 bg-white rounded-3xl border border-[#166534]/30 shadow-xl hover:shadow-2xl transition-all duration-300 overflow-hidden">
                        <div class="grid grid-cols-1 sm:grid-cols-12 min-h-[320px] sm:min-h-[340px]">
                            <div class="sm:col-span-7 p-6 sm:p-8 flex flex-col justify-between space-y-4">
                                <div>
                                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#166534] text-white text-xs font-bold mb-3 shadow-xs">
                                        <span>📦</span>
                                        <span>100% Terpisah</span>
                                    </div>
                                    <h3 class="text-xl sm:text-2xl font-black text-gray-900 tracking-tight">Stok Otomatis</h3>
                                    <p class="text-xs sm:text-sm text-gray-600 leading-relaxed mt-2">
                                        Stok terpotong saat terjual dan bertambah saat barang masuk. Dilengkapi peringatan visual stok menipis otomatis serta perlindungan saldo stok agar tidak minus.
                                    </p>
                                </div>
                                <div class="pt-2 border-t border-gray-100 flex items-center justify-between text-xs font-bold text-[#166534]">
                                    <span class="flex items-center gap-1.5">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#166534]"></span>
                                        Inventori Real-time
                                    </span>
                                    <span class="text-gray-400 font-normal">Multi-Tenant Terisolasi</span>
                                </div>
                            </div>
                            <div class="sm:col-span-5 relative min-h-[180px] sm:min-h-full overflow-hidden bg-gray-100">
                                <img src="{{ asset('images/landing/kios-rak.jpg') }}" 
                                     alt="Stok Otomatis Stokify" 
                                     class="absolute inset-0 w-full h-full object-cover object-center"
                                     loading="lazy">
                                <div class="absolute inset-0 bg-gradient-to-t sm:bg-gradient-to-r from-white/95 sm:from-white/90 via-transparent to-transparent"></div>
                            </div>
                        </div>
                    </div>

                    <!-- KARTU 3: Laporan PDF (Data Kios Aman) -->
                    <div class="sticky top-40 sm:top-44 z-30 bg-gradient-to-br from-[#14532D] to-[#0F3E22] text-white rounded-3xl border border-[#166534] shadow-2xl hover:shadow-3xl transition-all duration-300 overflow-hidden">
                        <div class="grid grid-cols-1 sm:grid-cols-12 min-h-[320px] sm:min-h-[340px]">
                            <div class="sm:col-span-7 p-6 sm:p-8 flex flex-col justify-between space-y-4">
                                <div>
                                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/20 backdrop-blur-xs text-white text-xs font-bold mb-3 border border-white/20">
                                        <span>🔒</span>
                                        <span>Laporan PDF</span>
                                    </div>
                                    <h3 class="text-xl sm:text-2xl font-black text-white tracking-tight">Data Kios Aman</h3>
                                    <p class="text-xs sm:text-sm text-emerald-100/90 leading-relaxed mt-2">
                                        Setiap kios memiliki ruang data sendiri yang terpisah. Nikmati rekonsiliasi kasir presisi, analisis barang terlaris, dan unduhan dokumen PDF resmi bertanggal kapan saja.
                                    </p>
                                </div>
                                <div class="pt-2 border-t border-white/15 flex items-center justify-between text-xs font-bold text-emerald-300">
                                    <span class="flex items-center gap-1.5">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                        Dokumen Siap Cetak
                                    </span>
                                    <span class="text-emerald-200/70 font-normal">Keuangan & Audit Mutasi</span>
                                </div>
                            </div>
                            <div class="sm:col-span-5 relative min-h-[180px] sm:min-h-full overflow-hidden">
                                <img src="{{ asset('images/landing/kios-lorong.jpg') }}" 
                                     alt="Laporan PDF Kios Stokify" 
                                     class="absolute inset-0 w-full h-full object-cover object-center"
                                     loading="lazy">
                                <div class="absolute inset-0 bg-gradient-to-t sm:bg-gradient-to-r from-[#14532D]/95 sm:from-[#14532D]/90 via-transparent to-transparent"></div>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </section>

    <!-- STRIP KATEGORI (GAYA TRUSTED BY PADA GAMBAR 1) -->
    <div class="py-8 bg-[#F6F7F5] border-b border-[#E5E7EB]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-gray-600">
                    <span class="w-2 h-2 rounded-full bg-[#166534]"></span>
                    <span>COCOK UNTUK SEMUA JENIS KIOS & TOKO KECIL:</span>
                </div>
                <div class="flex flex-wrap items-center justify-center gap-2 sm:gap-3 text-xs font-semibold text-gray-700">
                    <span class="px-3 py-1.5 rounded-full bg-white border border-gray-200 shadow-2xs">Kios Kelontong / Sembako</span>
                    <span class="px-3 py-1.5 rounded-full bg-white border border-gray-200 shadow-2xs">Warung dan Toko Harian</span>
                    <span class="px-3 py-1.5 rounded-full bg-white border border-gray-200 shadow-2xs">Toko Buah & Sayur</span>
                    <span class="px-3 py-1.5 rounded-full bg-white border border-gray-200 shadow-2xs">Toko dengan Banyak Kasir</span>
                    <span class="px-3 py-1.5 rounded-full bg-white border border-gray-200 shadow-2xs">Minimarket Mandiri</span>
                </div>
            </div>
        </div>
    </div>

    <!-- SECTION 3: INITIATIVES & FITUR UTAMA (SATU KESATUAN 6 FITUR URUT 1-6) -->
    <section id="fitur" class="py-16 sm:py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
            
            <!-- Header Seksi & Kotak Aksi Hijau Mint Berdampingan -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
                
                <!-- Sisi Kiri: Tag, Editorial Headline, & Teks Penjelasan -->
                <div class="lg:col-span-7 space-y-4">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#166534]/10 text-xs font-bold text-[#166534] uppercase tracking-wider">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#166534]"></span>
                        KAPABILITAS SISTEM LENGKAP
                    </div>
                    <h2 class="text-3xl sm:text-4xl font-black text-[#111827] tracking-tight leading-tight">
                        Teknologi Sederhana. <br>
                        Dampak Nyata untuk Kios Anda.
                    </h2>
                    <p class="teks-penjelasan text-sm text-[#4B5563] leading-relaxed max-w-2xl">
                        Stokify mengintegrasikan seluruh kebutuhan kasir dan inventori dalam 6 fitur utama yang tersusun rapi, mudah digunakan oleh kasir, dan memberikan kendali penuh bagi pemilik usaha.
                    </p>
                </div>

                <!-- Sisi Kanan: Kotak Aksi Hijau Mint (#DAF1DE Sesuai Gambar 1) -->
                <div class="lg:col-span-5">
                    <div class="bg-[#DAF1DE] border border-[#166534]/25 rounded-3xl p-6 sm:p-7 space-y-4 shadow-sm">
                        <div class="space-y-1.5">
                            <h3 class="text-lg font-black text-[#051F20] tracking-tight">
                                Siap Merapikan Kios Anda?
                            </h3>
                            <p class="text-xs text-[#14532D] leading-relaxed">
                                Daftarkan usaha Anda sekarang tanpa biaya pendaftaran awal. Langsung gunakan di browser perangkat laptop, tablet, atau smartphone.
                            </p>
                        </div>

                        <div class="pt-1">
                            <a href="{{ route('register') }}" 
                               class="w-full min-h-[44px] py-3.5 px-6 bg-[#166534] hover:bg-[#14532D] text-white text-xs font-bold rounded-2xl shadow-sm transition flex items-center justify-center gap-2">
                                <span>Daftar Kios Sekarang</span>
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                    <polyline points="12 5 19 12 12 19"></polyline>
                                </svg>
                            </a>
                        </div>

                        <div class="pt-1 border-t border-[#166534]/15 flex items-center justify-between text-[11px] text-[#14532D] font-medium">
                            <span class="flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#166534]"></span>
                                Multi-Tenant Terisolasi
                            </span>
                            <span>Data 100% Aman</span>
                        </div>
                    </div>
                </div>

            </div>

            <!-- SATU GRID UTUH 6 FITUR (URUT 1 SAMPAI 6 TANPA TERPISAH) -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                
                <!-- 1. Transaksi Kasir -->
                <div class="bg-white rounded-3xl border border-[#E5E7EB] overflow-hidden shadow-2xs hover:shadow-md transition-shadow flex flex-col justify-between">
                    <div>
                        <div class="h-44 w-full overflow-hidden relative">
                            <img src="{{ asset('images/landing/kios-kayu.jpg') }}" 
                                 alt="Transaksi Kasir" 
                                 class="w-full h-full object-cover hover:scale-105 transition-transform duration-500"
                                 loading="lazy">
                            <span class="absolute top-3 left-3 px-2.5 py-1 rounded-full bg-white/90 backdrop-blur-sm text-[10px] font-bold text-[#166534] uppercase tracking-wider">
                                POS Kasir
                            </span>
                        </div>
                        <div class="p-5 space-y-2">
                            <h3 class="text-base font-bold text-[#111827]">1. Transaksi Kasir</h3>
                            <p class="text-xs text-[#4B5563] leading-relaxed">
                                Layar kasir cepat dengan pencarian instan nama atau kode barcode, hitung kembalian otomatis, dan cetak struk belanja thermal.
                            </p>
                        </div>
                    </div>
                    <div class="p-5 pt-0">
                        <a href="#cara-kerja" class="text-xs font-bold text-[#166534] hover:text-[#14532D] inline-flex items-center gap-1 group">
                            <span>Pelajari Alur</span>
                            <span class="group-hover:translate-x-1 transition-transform">→</span>
                        </a>
                    </div>
                </div>

                <!-- 2. Buka/Tutup Shift -->
                <div class="bg-white rounded-3xl border border-[#E5E7EB] overflow-hidden shadow-2xs hover:shadow-md transition-shadow flex flex-col justify-between">
                    <div>
                        <div class="h-44 w-full overflow-hidden relative">
                            <img src="{{ asset('images/landing/kios-lorong.jpg') }}" 
                                 alt="Buka/Tutup Shift Kasir" 
                                 class="w-full h-full object-cover hover:scale-105 transition-transform duration-500"
                                 loading="lazy">
                            <span class="absolute top-3 left-3 px-2.5 py-1 rounded-full bg-white/90 backdrop-blur-sm text-[10px] font-bold text-[#166534] uppercase tracking-wider">
                                Shift Kasir
                            </span>
                        </div>
                        <div class="p-5 space-y-2">
                            <h3 class="text-base font-bold text-[#111827]">2. Buka/Tutup Shift</h3>
                            <p class="text-xs text-[#4B5563] leading-relaxed">
                                Buka dan tutup shift kasir dengan hitung buta uang fisik di laci kasir untuk merekonsiliasi selisih uang secara akurat.
                            </p>
                        </div>
                    </div>
                    <div class="p-5 pt-0">
                        <a href="#cara-kerja" class="text-xs font-bold text-[#166534] hover:text-[#14532D] inline-flex items-center gap-1 group">
                            <span>Pelajari Alur</span>
                            <span class="group-hover:translate-x-1 transition-transform">→</span>
                        </a>
                    </div>
                </div>

                <!-- 3. Peringatan Stok Menipis -->
                <div class="bg-white rounded-3xl border border-[#E5E7EB] overflow-hidden shadow-2xs hover:shadow-md transition-shadow flex flex-col justify-between">
                    <div>
                        <div class="h-44 w-full overflow-hidden relative">
                            <img src="{{ asset('images/landing/kios-rak.jpg') }}" 
                                 alt="Peringatan Stok Menipis" 
                                 class="w-full h-full object-cover hover:scale-105 transition-transform duration-500"
                                 loading="lazy">
                            <span class="absolute top-3 left-3 px-2.5 py-1 rounded-full bg-white/90 backdrop-blur-sm text-[10px] font-bold text-[#166534] uppercase tracking-wider">
                                Inventori
                            </span>
                        </div>
                        <div class="p-5 space-y-2">
                            <h3 class="text-base font-bold text-[#111827]">3. Peringatan Stok Menipis</h3>
                            <p class="text-xs text-[#4B5563] leading-relaxed">
                                Notifikasi visual barang yang menipis atau berada di bawah batas minimum agar Anda tidak kehabisan stok barang dagangan.
                            </p>
                        </div>
                    </div>
                    <div class="p-5 pt-0">
                        <a href="#cara-kerja" class="text-xs font-bold text-[#166534] hover:text-[#14532D] inline-flex items-center gap-1 group">
                            <span>Pelajari Alur</span>
                            <span class="group-hover:translate-x-1 transition-transform">→</span>
                        </a>
                    </div>
                </div>

                <!-- 4. Laporan PDF -->
                <div class="bg-white rounded-3xl border border-[#E5E7EB] overflow-hidden shadow-2xs hover:shadow-md transition-shadow flex flex-col justify-between">
                    <div>
                        <div class="h-44 w-full overflow-hidden relative">
                            <img src="{{ asset('images/landing/kios-lorong.jpg') }}" 
                                 alt="Laporan PDF Resmi" 
                                 class="w-full h-full object-cover hover:scale-105 transition-transform duration-500"
                                 loading="lazy">
                            <span class="absolute top-3 left-3 px-2.5 py-1 rounded-full bg-white/90 backdrop-blur-sm text-[10px] font-bold text-[#166534] uppercase tracking-wider">
                                Dokumen PDF
                            </span>
                        </div>
                        <div class="p-5 space-y-2">
                            <h3 class="text-base font-bold text-[#111827]">4. Laporan PDF</h3>
                            <p class="text-xs text-[#4B5563] leading-relaxed">
                                Unduh laporan resmi bertanggal: Laporan Keuangan (Omzet, HPP, Laba Kotor), Audit Mutasi Stok, dan Status Stok Fisik.
                            </p>
                        </div>
                    </div>
                    <div class="p-5 pt-0">
                        <a href="#cara-kerja" class="text-xs font-bold text-[#166534] hover:text-[#14532D] inline-flex items-center gap-1 group">
                            <span>Pelajari Alur</span>
                            <span class="group-hover:translate-x-1 transition-transform">→</span>
                        </a>
                    </div>
                </div>

                <!-- 5. Barang Terlaris -->
                <div class="bg-white rounded-3xl border border-[#E5E7EB] overflow-hidden shadow-2xs hover:shadow-md transition-shadow flex flex-col justify-between">
                    <div>
                        <div class="h-44 w-full overflow-hidden relative">
                            <img src="{{ asset('images/landing/kios-peti.jpg') }}" 
                                 alt="Barang Terlaris Stokify" 
                                 class="w-full h-full object-cover hover:scale-105 transition-transform duration-500"
                                 loading="lazy">
                            <span class="absolute top-3 left-3 px-2.5 py-1 rounded-full bg-white/90 backdrop-blur-sm text-[10px] font-bold text-[#166534] uppercase tracking-wider">
                                Analisis
                            </span>
                        </div>
                        <div class="p-5 space-y-2">
                            <h3 class="text-base font-bold text-[#111827]">5. Barang Terlaris</h3>
                            <p class="text-xs text-[#4B5563] leading-relaxed">
                                Klasifikasi barang Fast/Slow moving berbasis Pareto serta perhitungan Titik Pesan Ulang (ROP) untuk belanja pasokan tepat waktu.
                            </p>
                        </div>
                    </div>
                    <div class="p-5 pt-0">
                        <a href="#cara-kerja" class="text-xs font-bold text-[#166534] hover:text-[#14532D] inline-flex items-center gap-1 group">
                            <span>Pelajari Alur</span>
                            <span class="group-hover:translate-x-1 transition-transform">→</span>
                        </a>
                    </div>
                </div>

                <!-- 6. Kelola Akun Kasir -->
                <div class="bg-white rounded-3xl border border-[#E5E7EB] overflow-hidden shadow-2xs hover:shadow-md transition-shadow flex flex-col justify-between">
                    <div>
                        <div class="h-44 w-full overflow-hidden relative">
                            <img src="{{ asset('images/landing/kios-kayu.jpg') }}" 
                                 alt="Kelola Akun Kasir" 
                                 class="w-full h-full object-cover hover:scale-105 transition-transform duration-500"
                                 loading="lazy">
                            <span class="absolute top-3 left-3 px-2.5 py-1 rounded-full bg-white/90 backdrop-blur-sm text-[10px] font-bold text-[#166534] uppercase tracking-wider">
                                Hak Akses
                            </span>
                        </div>
                        <div class="p-5 space-y-2">
                            <h3 class="text-base font-bold text-[#111827]">6. Kelola Akun Kasir</h3>
                            <p class="text-xs text-[#4B5563] leading-relaxed">
                                Kelola akun petugas kasir mandiri, status aktif/nonaktif, dan reset password dengan kontrol hak akses admin yang ketat.
                            </p>
                        </div>
                    </div>
                    <div class="p-5 pt-0">
                        <a href="#cara-kerja" class="text-xs font-bold text-[#166534] hover:text-[#14532D] inline-flex items-center gap-1 group">
                            <span>Pelajari Alur</span>
                            <span class="group-hover:translate-x-1 transition-transform">→</span>
                        </a>
                    </div>
                </div>

            </div>

        </div>
    </section>

    <!-- SECTION 4: BATASAN SISTEM (DISEMBUNYIKAN SESUAI PERMINTAAN USER) -->
    <section class="hidden" aria-hidden="true">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            <div class="text-center space-y-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-[#166534] bg-[#166534]/10 px-3 py-1 rounded-full">
                    TRANSPARANSI SISTEM
                </span>
                <h2 class="text-2xl sm:text-3xl font-black text-[#111827] tracking-tight">
                    Batasan Sistem Stokify
                </h2>
                <p class="teks-penjelasan text-xs sm:text-sm text-[#4B5563] max-w-xl mx-auto">
                    Demi menjaga keandalan dan fokus pada kebutuhan kios mandiri, sistem dirancang dengan batasan-batasan berikut:
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <div class="p-5 rounded-2xl bg-white border border-[#E5E7EB] space-y-2 shadow-2xs">
                    <div class="flex items-center gap-2 text-rose-600 font-bold text-xs uppercase">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <span>Pembayaran Tunai Saja</span>
                    </div>
                    <p class="text-xs text-[#4B5563] leading-relaxed">
                        Saat ini transaksi kasir hanya melayani pembayaran uang tunai fisik. Sistem belum terintegrasi dengan mesin EDC non-tunai, transfer bank, atau QRIS dinamis.
                    </p>
                </div>

                <div class="p-5 rounded-2xl bg-white border border-[#E5E7EB] space-y-2 shadow-2xs">
                    <div class="flex items-center gap-2 text-rose-600 font-bold text-xs uppercase">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <span>Tanpa Kasbon / Piutang</span>
                    </div>
                    <p class="text-xs text-[#4B5563] leading-relaxed">
                        Seluruh transaksi kasir wajib lunas pada saat nota diselesaikan. Sistem tidak menyediakan fitur hutang pelanggan (kasbon/piutang usaha).
                    </p>
                </div>

                <div class="p-5 rounded-2xl bg-white border border-[#E5E7EB] space-y-2 shadow-2xs">
                    <div class="flex items-center gap-2 text-rose-600 font-bold text-xs uppercase">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <span>Satu Lokasi per Kios</span>
                    </div>
                    <p class="text-xs text-[#4B5563] leading-relaxed">
                        Satu akun kios terdaftar mengelola 1 lokasi operasional kios fisik mandiri. Belum mendukung transfer stok antar-cabang banyak gudang.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- SECTION 5: UNTUK SIAPA (DISEMBUNYIKAN SESUAI PERMINTAAN USER) -->
    <section id="untuk-siapa" class="hidden" aria-hidden="true">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
            <div class="text-center max-w-2xl mx-auto space-y-3">
                <span class="inline-block px-3 py-1 rounded-full bg-[#E5E7EB]/70 text-xs font-bold text-[#374151] uppercase tracking-wider">
                    TARGET PENGGUNA
                </span>
                <h2 class="text-3xl sm:text-4xl font-black text-[#111827] tracking-tight">
                    Untuk Kios dan Toko Kecil
                </h2>
                <p class="text-sm text-[#4B5563]">
                    Dirancang spesifik untuk model usaha ritel mandiri dengan perputaran barang harian yang cepat.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white border border-[#E5E7EB] rounded-3xl p-7 space-y-3 shadow-2xs">
                    <h3 class="text-lg font-bold text-[#111827]">Kios Kelontong / Sembako</h3>
                    <p class="teks-penjelasan text-xs text-[#4B5563] leading-relaxed">
                        Pantau persediaan beras, gula, minyak goreng, dan sembako secara real-time tanpa takut kehabisan barang dagangan utama.
                    </p>
                </div>

                <div class="bg-white border border-[#E5E7EB] rounded-3xl p-7 space-y-3 shadow-2xs">
                    <h3 class="text-lg font-bold text-[#111827]">Warung dan Toko Harian</h3>
                    <p class="teks-penjelasan text-xs text-[#4B5563] leading-relaxed">
                        Transaksi cepat di jam ramai tanpa antre berlama-lama dengan dukungan pencarian barang kilat dan cetak struk kasir.
                    </p>
                </div>

                <div class="bg-white border border-[#E5E7EB] rounded-3xl p-7 space-y-3 shadow-2xs">
                    <h3 class="text-lg font-bold text-[#111827]">Toko dengan Banyak Kasir</h3>
                    <p class="teks-penjelasan text-xs text-[#4B5563] leading-relaxed">
                        Setiap kasir memiliki shift terpisah dengan rekonsiliasi kas laci mandiri untuk mencegah kecurangan dan salah hitung.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- SECTION 6: CARA KERJA (3 LANGKAH) -->
    <section id="cara-kerja" class="py-16 sm:py-20 bg-[#F6F7F5] border-t border-[#E5E7EB]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
            
            <div class="text-center max-w-2xl mx-auto space-y-3">
                <span class="inline-block px-3 py-1 rounded-full bg-[#E5E7EB]/70 text-xs font-bold text-[#374151] uppercase tracking-wider">
                    ALUR PENGGUNAAN
                </span>
                <h2 class="text-3xl sm:text-4xl font-black text-[#111827] tracking-tight">
                    Mulai dalam 3 Langkah
                </h2>
                <p class="text-sm text-[#4B5563]">
                    Tidak memerlukan keahlian IT khusus. Kios Anda siap beroperasi dalam hitungan menit.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Langkah 1 -->
                <div class="bg-white border border-[#E5E7EB] rounded-3xl p-7 flex flex-col justify-between hover:border-[#166534] transition shadow-2xs">
                    <div>
                        <span class="text-4xl font-black text-[#166534]/30 block mb-4">01</span>
                        <h3 class="text-lg font-bold text-[#111827] mb-2">Daftarkan Kios Anda</h3>
                        <p class="teks-penjelasan text-xs text-[#4B5563] leading-relaxed">
                            Masukkan nama kios, alamat, dan email pemilik usaha. Ruang database kios Anda langsung diinisialisasi secara otomatis.
                        </p>
                    </div>
                    <div class="mt-6 pt-4 border-t border-[#E5E7EB] flex items-center text-xs font-bold text-[#166534]">
                        <span>Waktu setup: ~1 menit</span>
                    </div>
                </div>

                <!-- Langkah 2 -->
                <div class="bg-white border border-[#E5E7EB] rounded-3xl p-7 flex flex-col justify-between hover:border-[#166534] transition shadow-2xs">
                    <div>
                        <span class="text-4xl font-black text-[#166534]/30 block mb-4">02</span>
                        <h3 class="text-lg font-bold text-[#111827] mb-2">Isi Barang dan Tambah Kasir</h3>
                        <p class="teks-penjelasan text-xs text-[#4B5563] leading-relaxed">
                            Tambahkan data master sembako beserta stok awal, catat pasokan barang masuk, dan buat akun untuk petugas kasir Anda.
                        </p>
                    </div>
                    <div class="mt-6 pt-4 border-t border-[#E5E7EB] flex items-center text-xs font-bold text-[#166534]">
                        <span>Dukungan barcode scanner</span>
                    </div>
                </div>

                <!-- Langkah 3 -->
                <div class="bg-white border border-[#E5E7EB] rounded-3xl p-7 flex flex-col justify-between hover:border-[#166534] transition shadow-2xs">
                    <div>
                        <span class="text-4xl font-black text-[#166534]/30 block mb-4">03</span>
                        <h3 class="text-lg font-bold text-[#111827] mb-2">Mulai Berjualan</h3>
                        <p class="teks-penjelasan text-xs text-[#4B5563] leading-relaxed">
                            Kasir membuka shift, memproses transaksi pembeli, cetak struk, dan menutup shift dengan rekonsiliasi laci kasir akurat.
                        </p>
                    </div>
                    <div class="mt-6 pt-4 border-t border-[#E5E7EB] flex items-center text-xs font-bold text-[#166534]">
                        <span>Laporan PDF siap cetak</span>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <!-- SECTION 7: CALL TO ACTION BOTTOM -->
    <section class="py-16 bg-[#166534] text-white border-t border-[#14532D]">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center space-y-6">
            <h2 class="text-3xl sm:text-4xl font-black tracking-tight text-white">
                Siap Merapikan Kios Anda?
            </h2>
            <p class="text-xs sm:text-sm text-[#DCFCE7]/90 max-w-xl mx-auto leading-relaxed">
                Daftarkan usaha Anda sekarang tanpa biaya pendaftaran awal. Langsung gunakan di browser perangkat laptop, tablet, atau smartphone.
            </p>
            <div class="pt-2 flex flex-col sm:flex-row items-center justify-center gap-3">
                <a href="{{ route('register') }}" 
                   class="min-h-[44px] px-8 py-3.5 bg-[#DCFCE7] hover:bg-white text-[#111827] text-xs font-bold rounded-full shadow-lg transition transform hover:-translate-y-0.5 flex items-center justify-center gap-2">
                    <span>Daftar Kios Sekarang</span>
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                        <polyline points="12 5 19 12 12 19"></polyline>
                    </svg>
                </a>
                <a href="{{ route('login') }}" 
                   class="min-h-[44px] px-6 py-3.5 bg-[#14532D] hover:bg-[#166534] border border-[#DCFCE7]/30 text-[#DCFCE7] text-xs font-bold rounded-full transition flex items-center justify-center">
                    Masuk ke Kios Terdaftar
                </a>
            </div>

            <!-- Email Kontak Bantuan Resmi -->
            <div class="pt-4 flex items-center justify-center gap-2 text-xs text-[#DCFCE7]/80">
                <svg class="w-4 h-4 text-[#86EFAC]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                <span>Pertanyaan & Bantuan Layanan:</span>
                <a href="mailto:stokify.app27@gmail.com" class="font-bold text-white hover:underline">
                    stokify.app27@gmail.com
                </a>
            </div>
        </div>
    </section>

</x-landing-layout>
