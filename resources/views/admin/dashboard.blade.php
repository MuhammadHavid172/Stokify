<x-layouts.admin title="Dashboard">
    <div class="space-y-6">
        
        <!-- Header Halaman Dashboard -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 tracking-tight">Dashboard</h1>
                <p class="text-xs sm:text-sm text-gray-500 mt-0.5">
                    Ringkasan performa penjualan, ketersediaan stok, dan kasir kios {{ $kios->nama_kios }}.
                </p>
            </div>

            <div class="flex items-center gap-2.5 flex-wrap">
                <a href="{{ route('admin.laporan.keuangan', ['jenis' => 'bulanan']) }}" 
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-white hover:bg-gray-50 border border-gray-300 text-xs font-semibold text-gray-700 shadow-xs transition">
                    <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>Laporan Bulan Ini</span>
                </a>

                <a href="{{ route('kasir.index') }}" 
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-[#166534] hover:bg-[#14532D] text-white text-xs font-semibold shadow-xs transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                    <span>Buka Layar Kasir</span>
                </a>
            </div>
        </div>

        <!-- Peringatan Shift Berjalan > 16 Jam (Tahap 8) -->
        @if ($shiftLebih16JamCount > 0)
            <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-xs">
                <div class="flex items-center gap-2.5">
                    <svg class="w-5 h-5 text-amber-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <span>Terdapat <strong>{{ $shiftLebih16JamCount }} shift kasir</strong> yang sedang berjalan lebih dari 16 jam. Periksa kemungkinan kasir lupa menutup shift.</span>
                </div>
                <a href="{{ route('admin.shift.index', ['status' => 'berjalan']) }}" class="text-xs font-bold text-amber-800 hover:text-amber-950 underline shrink-0">
                    Lihat Shift &rarr;
                </a>
            </div>
        @endif

        <!-- 4 KARTU STATISTIK UTAMA (DATA NYATA HANYA) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            
            <!-- Kartu 1: Penjualan Hari Ini (Hero Card Hijau Gradasi Mewah) -->
            <div class="relative overflow-hidden bg-gradient-to-br from-[#166534] to-[#14532D] text-white rounded-xl p-5 shadow-md shadow-[#166534]/20 flex flex-col justify-between border border-[#166534] transition hover:shadow-lg hover:shadow-[#166534]/30">
                <!-- Aksen Cahaya Halus di Latar Belakang -->
                <div class="absolute -right-6 -top-6 w-24 h-24 rounded-full bg-white/10 blur-xl pointer-events-none"></div>

                <div class="relative z-10">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-emerald-200 tracking-wide uppercase">Penjualan Hari Ini</span>
                        <div class="w-8 h-8 rounded-lg bg-white/20 backdrop-blur-xs text-white border border-white/20 flex items-center justify-center shadow-xs">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-2.5">
                        <h3 class="text-2xl font-black text-white tracking-tight tabular-nums drop-shadow-xs">
                            Rp {{ number_format($omzetHariIni, 0, ',', '.') }}
                        </h3>
                    </div>
                </div>
                <div class="relative z-10 mt-3 pt-2.5 border-t border-white/20 flex items-center justify-between text-xs text-emerald-100">
                    <span class="text-emerald-200/90 font-medium">Transaksi Hari Ini</span>
                    <span class="text-[11px] font-bold px-2 py-0.5 rounded-md bg-white/20 text-white border border-white/20 backdrop-blur-xs">{{ $totalTransaksiHariIni }} Transaksi</span>
                </div>
            </div>

            <!-- Kartu 2: Laba Kotor Hari Ini -->
            <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm flex flex-col justify-between hover:border-gray-300 transition">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-gray-500">Laba Kotor Hari Ini</span>
                        <div class="w-8 h-8 rounded-lg bg-[#DCFCE7] text-[#166534] flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-2.5">
                        <h3 class="text-2xl font-bold text-gray-900 tracking-tight tabular-nums">
                            Rp {{ number_format($labaBersih, 0, ',', '.') }}
                        </h3>
                    </div>
                </div>
                <div class="mt-3 pt-2.5 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500">
                    <span>Omzet dikurangi HPP</span>
                    <span class="text-[11px] text-gray-500">Hari ini</span>
                </div>
            </div>

            <!-- Kartu 3: Nilai Aset Stok -->
            <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm flex flex-col justify-between hover:border-gray-300 transition">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-gray-500">Nilai Aset Persediaan</span>
                        <div class="w-8 h-8 rounded-lg bg-[#DCFCE7] text-[#166534] flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-2.5">
                        <h3 class="text-2xl font-bold text-gray-900 tracking-tight tabular-nums">
                            Rp {{ number_format($nilaiAsetStok, 0, ',', '.') }}
                        </h3>
                    </div>
                </div>
                <div class="mt-3 pt-2.5 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500">
                    <span>{{ $totalBarangCount }} jenis barang aktif</span>
                    <a href="{{ route('admin.barang.index') }}" class="text-[11px] font-semibold text-[#166534] hover:underline">Lihat &rarr;</a>
                </div>
            </div>

            <!-- Kartu 4: Rekomendasi Restok (Tahap 10) -->
            <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm flex flex-col justify-between hover:border-gray-300 transition">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-gray-500">Perlu Restok (30 Hari)</span>
                        <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-700 border border-amber-200 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="mt-2.5">
                        <h3 class="text-2xl font-bold text-gray-900 tracking-tight tabular-nums">
                            {{ $jumlahPerluDikulak }} <span class="text-sm font-semibold text-gray-500">barang</span>
                        </h3>
                    </div>
                </div>
                <div class="mt-3 pt-2.5 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500">
                    <span>Berdasarkan kecepatan jual</span>
                    <a href="{{ route('admin.analisis.rekomendasi') }}" class="text-[11px] font-semibold text-amber-700 hover:underline">Detail &rarr;</a>
                </div>
            </div>

        </div>

        <!-- ==========================================
             BARIS 1 (GRID SIMETRIS ATAS):
             KIRI: GRAFIK PENJUALAN 7 HARI (8 KOLOM)
             KANAN: DAFTAR KASIR KIOS DENGAN SCROLL INTERNAL (4 KOLOM)
             Kedua kartu menggunakan items-stretch & h-full agar tingginya 100% rata
             ========================================== -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch">
            
            <!-- 1. GRAFIK BATANG ASLI: PENJUALAN 7 HARI TERAKHIR (8 KOLOM) -->
            <div class="lg:col-span-8 bg-white border border-gray-200 rounded-xl p-5 shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                        <div>
                            <h2 class="text-sm font-bold text-gray-900">Penjualan 7 Hari Terakhir</h2>
                            <p class="text-xs text-gray-500 mt-0.5">Total omzet riil: Rp {{ number_format($totalOmzet7Hari, 0, ',', '.') }}</p>
                        </div>
                        <span class="text-xs font-semibold px-2.5 py-1 rounded-md bg-[#DCFCE7] text-[#166534]">
                            Zona Asia/Jayapura
                        </span>
                    </div>

                    @if ($hasPenjualan7Hari)
                        <div class="mt-6 flex items-end justify-between gap-2 sm:gap-4 h-48 pt-4 px-2">
                            @foreach ($penjualan7Hari as $p)
                                @php
                                    $pct = $maxOmzet7Hari > 0 ? max(8, round(($p['omzet'] / $maxOmzet7Hari) * 100)) : 8;
                                @endphp
                                <div class="flex-1 flex flex-col items-center gap-2 group relative">
                                    <!-- Tooltip hover -->
                                    <div class="opacity-0 group-hover:opacity-100 transition absolute -top-12 z-20 bg-gray-900 text-white text-[11px] rounded-md px-2 py-1 shadow-md whitespace-nowrap pointer-events-none">
                                        Rp {{ number_format($p['omzet'], 0, ',', '.') }} ({{ $p['count'] }} nota)
                                    </div>
                                    
                                    <div class="w-full max-w-[48px] bg-gray-100 rounded-t-lg relative flex items-end justify-center overflow-hidden" style="height: 140px;">
                                        <div class="w-full bg-[#166534] hover:bg-[#14532D] transition-all rounded-t-lg" style="height: {{ $pct }}%;"></div>
                                    </div>
                                    <div class="text-center">
                                        <p class="text-[11px] font-bold text-gray-700">{{ $p['short_label'] }}</p>
                                        <p class="text-[9px] text-gray-400">{{ $p['full_label'] }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="py-8">
                            <x-empty-state 
                                title="Belum Ada Transaksi 7 Hari Terakhir" 
                                description="Data penjualan 7 hari terakhir akan otomatis muncul dalam grafik batang ini setelah kasir memproses transaksi nota." />
                        </div>
                    @endif
                </div>
            </div>

            <!-- 2. KASIR KIOS (4 KOLOM) - TINGGI SELARAS & SCROLL INTERNAL JIKA KASIR BANYAK -->
            <div class="lg:col-span-4 bg-white border border-gray-200 rounded-xl p-5 shadow-sm flex flex-col justify-between">
                <div class="flex-1 flex flex-col min-h-0">
                    <div class="flex items-center justify-between pb-3 border-b border-gray-100 shrink-0">
                        <div>
                            <h2 class="text-sm font-bold text-gray-900">Kasir Kios</h2>
                            <p class="text-[11px] text-gray-500 mt-0.5">
                                Jumlah Kasir Aktif: <strong class="text-gray-900">{{ $kasirAktifCount }}</strong>
                            </p>
                        </div>

                        <a href="{{ route('admin.kasir.create') }}" 
                           class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-[#166534] hover:bg-[#14532D] text-white text-xs font-semibold shadow-xs transition" 
                           title="Daftarkan Kasir Baru">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <line x1="12" y1="5" x2="12" y2="19" />
                                <line x1="5" y1="12" x2="19" y2="12" />
                            </svg>
                            <span>Daftarkan Kasir</span>
                        </a>
                    </div>

                    <!-- Scrollable Container untuk Kasir dengan batas tinggi presisi & scrollbar halus -->
                    <div class="space-y-2 mt-3.5 overflow-y-auto pr-1.5 custom-scrollbar max-h-[175px] flex-1">
                        @forelse ($kasirList as $k)
                            <div class="p-3 rounded-lg border border-gray-100 bg-gray-50 flex items-center justify-between">
                                <div class="flex items-center gap-2.5 overflow-hidden">
                                    <div class="w-8 h-8 rounded-lg bg-white border border-gray-200 text-gray-700 font-bold text-xs flex items-center justify-center shrink-0">
                                        {{ strtoupper(substr($k->name, 0, 2)) }}
                                    </div>
                                    <div class="overflow-hidden">
                                        <p class="text-xs font-bold text-gray-900 truncate leading-tight">{{ $k->name }}</p>
                                        <p class="text-[10px] text-gray-500 truncate mt-0.5">@<span>{{ $k->username }}</span></p>
                                    </div>
                                </div>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $k->is_aktif ? 'bg-[#DCFCE7] text-[#166534]' : 'bg-gray-200 text-gray-600' }}">
                                    {{ $k->is_aktif ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </div>
                        @empty
                            <div class="text-center py-4 text-gray-500">
                                <p class="text-xs">Belum ada kasir terdaftar.</p>
                                <a href="{{ route('admin.kasir.create') }}" class="text-xs text-[#166534] font-bold mt-1 inline-flex items-center gap-1 hover:underline">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <line x1="12" y1="5" x2="12" y2="19" />
                                        <line x1="5" y1="12" x2="19" y2="12" />
                                    </svg>
                                    <span>Daftarkan Kasir</span>
                                </a>
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="mt-4 pt-3 border-t border-gray-100 shrink-0">
                    <a href="{{ route('admin.kasir.index') }}" class="block text-center text-xs font-semibold text-gray-600 hover:text-[#166534] transition">
                        Kelola Seluruh Kasir &rarr;
                    </a>
                </div>
            </div>

        </div>

        <!-- ==========================================
             BARIS 2 (GRID SIMETRIS BAWAH):
             KIRI: PERINGATAN STOK MENIPIS & BARANG TERLARIS (8 KOLOM)
             KANAN: SHIFT KASIR BERJALAN & REKONSILIASI KAS (4 KOLOM)
             Kedua bagian dimulai pada garis horizontal yang sama persis
             ========================================== -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch">
            
            <!-- KOLOM KIRI (8 KOLOM): 2 KARTU BERDAMPINGAN -->
            <div class="lg:col-span-8 grid grid-cols-1 md:grid-cols-2 gap-6 items-stretch">
                
                <!-- KARTU A: PERINGATAN STOK MENIPIS -->
                <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                            <div class="flex items-center gap-2">
                                <h2 class="text-sm font-bold text-gray-900">Peringatan Stok Menipis</h2>
                                @if ($barangMenipisCount > 0)
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-rose-100 text-rose-800">
                                        {{ $barangMenipisCount }} Kritis
                                    </span>
                                @else
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-[#DCFCE7] text-[#166534]">
                                        100% Aman
                                    </span>
                                @endif
                            </div>
                            <a href="{{ route('admin.barang.index', ['filter_stok' => 'menipis']) }}" class="text-xs font-semibold text-[#166534] hover:underline">
                                Lihat Semua &rarr;
                            </a>
                        </div>

                        @if ($barangMenipisCount > 0)
                            <div class="divide-y divide-gray-100 mt-2">
                                @foreach ($barangMenipisList as $b)
                                    <div class="py-3 flex items-center justify-between gap-3">
                                        <div class="min-w-0">
                                            <p class="text-xs font-bold text-gray-900 truncate">{{ $b->nama }}</p>
                                            <p class="text-[11px] text-gray-500 font-mono">{{ $b->kode }} &bull; {{ $b->kategori?->nama ?? 'Tanpa Kategori' }}</p>
                                        </div>
                                        <div class="flex items-center gap-2 shrink-0">
                                            <div class="text-right">
                                                <span class="text-xs font-bold text-rose-600">{{ $b->stok }} {{ $b->satuan }}</span>
                                                <p class="text-[10px] text-gray-400">Min: {{ $b->stok_minimum }}</p>
                                            </div>
                                            <a href="{{ route('admin.barang-masuk.create') }}" class="p-1.5 rounded-lg border border-gray-200 hover:bg-gray-50 text-gray-600" title="Restok Barang">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                                </svg>
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="py-4 space-y-3">
                                <div class="p-3.5 rounded-xl bg-[#DCFCE7]/40 border border-[#86EFAC]/60 flex items-start gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-[#DCFCE7] text-[#166534] flex items-center justify-center shrink-0">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-[#166534]">Seluruh Stok Aman</p>
                                        <p class="text-[11px] text-gray-600 mt-0.5 leading-relaxed">
                                            Tidak ada barang yang berada di bawah batas stok minimum saat ini.
                                        </p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-2 text-center">
                                    <div class="p-2.5 rounded-lg bg-gray-50 border border-gray-100">
                                        <p class="text-[10px] text-gray-500 font-semibold uppercase">Total Barang</p>
                                        <p class="text-sm font-bold text-gray-900 mt-0.5">{{ $totalBarangCount }}</p>
                                    </div>
                                    <div class="p-2.5 rounded-lg bg-gray-50 border border-gray-100">
                                        <p class="text-[10px] text-gray-500 font-semibold uppercase">Nilai Aset</p>
                                        <p class="text-sm font-bold text-gray-900 mt-0.5 tabular-nums">Rp {{ number_format($nilaiAsetStok, 0, ',', '.') }}</p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="pt-3 border-t border-gray-100 flex items-center justify-between text-xs">
                        <a href="{{ route('admin.barang-masuk.create') }}" class="text-[11px] font-semibold text-[#166534] hover:underline inline-flex items-center gap-1">
                            <span>+ Catat Barang Masuk</span>
                        </a>
                        <a href="{{ route('admin.penyesuaian.create') }}" class="text-[11px] font-semibold text-gray-600 hover:text-gray-900 hover:underline">
                            Penyesuaian Stok
                        </a>
                    </div>
                </div>

                <!-- KARTU B: BARANG TERLARIS 30 HARI TERAKHIR -->
                <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                            <div>
                                <h2 class="text-sm font-bold text-gray-900">Barang Terlaris 30 Hari Terakhir</h2>
                                <p class="text-[11px] text-gray-500 mt-0.5">Volume penjualan tertinggi</p>
                            </div>
                            <a href="{{ route('admin.analisis.index') }}" class="text-xs font-semibold text-[#166534] hover:underline">
                                Analisis &rarr;
                            </a>
                        </div>

                        @if (!empty($barangTerlaris30Hari) && count($barangTerlaris30Hari) > 0)
                            <div class="divide-y divide-gray-100 mt-2">
                                @foreach ($barangTerlaris30Hari as $idx => $t)
                                    <div class="py-2.5 flex items-center justify-between gap-2">
                                        <div class="flex items-center gap-2.5 min-w-0">
                                            <span class="w-5 h-5 rounded bg-gray-100 text-gray-700 font-bold text-[11px] flex items-center justify-center shrink-0">
                                                {{ $idx + 1 }}
                                            </span>
                                            <div class="min-w-0">
                                                <p class="text-xs font-bold text-gray-900 truncate">{{ $t['nama'] }}</p>
                                                <p class="text-[10px] text-gray-500 font-mono">{{ $t['kode'] }} &bull; {{ $t['kategori_nama'] ?? ($t['kategori'] ?? '-') }}</p>
                                            </div>
                                        </div>
                                        <div class="text-right shrink-0">
                                            <p class="text-xs font-bold text-gray-900 tabular-nums">Rp {{ number_format($t['omzet'], 0, ',', '.') }}</p>
                                            <p class="text-[10px] text-gray-500">{{ $t['terjual_netto'] ?? ($t['terjual'] ?? 0) }} terjual</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="py-6 text-center">
                                <div class="w-8 h-8 rounded-lg bg-gray-100 text-gray-400 mx-auto flex items-center justify-center mb-2">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                    </svg>
                                </div>
                                <p class="text-xs font-bold text-gray-800">Belum Ada Data Penjualan</p>
                                <p class="text-[11px] text-gray-500 mt-0.5">Peringkat terisi otomatis setelah nota selesai.</p>
                            </div>
                        @endif
                    </div>

                    <div class="pt-3 border-t border-gray-100 flex items-center justify-between text-xs">
                        <span class="text-gray-500 text-[11px]">Perlu Restok: <strong class="text-gray-800">{{ $jumlahPerluDikulak }} barang</strong></span>
                        <a href="{{ route('admin.analisis.rekomendasi') }}" class="text-[11px] font-semibold text-[#166534] hover:underline">
                            Rekomendasi &rarr;
                        </a>
                    </div>
                </div>

            </div>

            <!-- KOLOM KANAN (4 KOLOM): SHIFT BERJALAN & REKONSILIASI KAS -->
            <div class="lg:col-span-4 bg-white border border-gray-200 rounded-xl p-5 shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                        <h2 class="text-sm font-bold text-gray-900">Shift Kasir Berjalan</h2>
                        <span class="text-xs font-bold px-2 py-0.5 rounded-full {{ $shiftBerjalanCount > 0 ? 'bg-[#DCFCE7] text-[#166534]' : 'bg-gray-100 text-gray-600' }}">
                            {{ $shiftBerjalanCount }} Aktif
                        </span>
                    </div>

                    <div class="mt-3.5 space-y-3">
                        @if ($shiftBerjalanCount > 0)
                            <div class="p-3 rounded-lg bg-[#DCFCE7]/40 border border-[#86EFAC]/60 text-xs text-gray-800 space-y-1">
                                <p class="font-bold text-[#166534]">Shift aktif saat ini:</p>
                                <p class="text-gray-700">{{ implode(', ', $shiftBerjalanNames) }}</p>
                            </div>
                        @else
                            <p class="text-xs text-gray-500 py-1">Tidak ada shift kasir yang sedang berjalan.</p>
                        @endif

                        <div class="pt-2 border-t border-gray-100 flex items-center justify-between text-xs">
                            <span class="text-gray-500">Selisih Kas 7 Hari:</span>
                            <span class="font-bold tabular-nums {{ $selisihKas7Hari < 0 ? 'text-rose-600' : ($selisihKas7Hari > 0 ? 'text-blue-600' : 'text-gray-800') }}">
                                {{ $selisihKas7Hari > 0 ? '+' : '' }}Rp {{ number_format($selisihKas7Hari, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-t border-gray-100">
                    <a href="{{ route('admin.shift.index') }}" class="block text-center text-xs font-semibold text-gray-600 hover:text-[#166534] transition">
                        Riwayat & Rekonsiliasi Shift &rarr;
                    </a>
                </div>
            </div>

        </div>

    </div>
</x-layouts.admin>
