<!DOCTYPE html>
<html lang="id" class="h-full bg-[#F6F7F5]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Mesin Kasir POS - {{ $kios->nama_kios }} - Stokify</title>

    <!-- Favicon Stokify -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="alternate icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    <!-- Fonts: Inter & Poppins -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|poppins:500,600,700,800&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
@if (! $shift)
<body class="h-full bg-[#F6F7F5] text-gray-800 antialiased font-sans flex flex-col selection:bg-[#DCFCE7] selection:text-[#166534]">

    <!-- TOPBAR KASIR (Layar Buka Shift) -->
    <header class="bg-white border-b border-gray-200 px-4 py-3 sticky top-0 z-30 shadow-xs">
        <div class="max-w-7xl mx-auto flex items-center justify-between gap-3">
            <div class="flex items-center gap-3 min-w-0">
                @if ($user->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" 
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-gray-50 hover:bg-gray-100 border border-gray-200 text-xs font-semibold text-gray-700 transition shrink-0"
                       title="Kembali ke Dashboard Admin">
                        <svg class="w-3.5 h-3.5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <line x1="19" y1="12" x2="5" y2="12"></line>
                            <polyline points="12 19 5 12 12 5"></polyline>
                        </svg>
                        <span class="hidden sm:inline">Dashboard</span>
                    </a>
                    <div class="h-6 w-px bg-gray-200 hidden sm:block shrink-0"></div>
                @endif

                <div class="w-9 h-9 rounded-lg bg-[#DCFCE7] text-[#166534] flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <rect x="2" y="3" width="20" height="14" rx="2" ry="2"/>
                        <line x1="8" y1="21" x2="16" y2="21"/>
                        <line x1="12" y1="17" x2="12" y2="21"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold tracking-wider text-gray-900 uppercase">STOKIFY POS</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#DCFCE7] text-[#166534]">
                            {{ $kios->nama_kios }}
                        </span>
                    </div>
                    <p class="text-[11px] text-gray-500 truncate">
                        Kasir: <strong class="text-gray-900 font-semibold">{{ $user->name }}</strong>
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                <x-notification-lonceng role="kasir" />
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" 
                            title="Keluar dari sesi"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-gray-50 hover:bg-rose-50 border border-gray-200 hover:border-rose-200 text-xs font-semibold text-gray-600 hover:text-rose-700 transition">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                            <polyline points="16 17 21 12 16 7" />
                            <line x1="21" y1="12" x2="9" y2="12" />
                        </svg>
                        <span class="hidden sm:inline">Keluar</span>
                    </button>
                </form>
            </div>
        </div>
    </header>

    <!-- FORM BUKA SHIFT -->
    <main class="flex-1 flex items-center justify-center p-4">
        <div class="w-full max-w-md bg-white border border-gray-200 rounded-2xl p-6 sm:p-8 shadow-sm"
             x-data="{
                 modalAwalRaw: '{{ old('modal_awal', '0') }}',
                 submitting: false,
                 get formattedModal() {
                     let clean = (this.modalAwalRaw + '').replace(/\D/g, '');
                     if (!clean) return '0';
                     return new Intl.NumberFormat('id-ID').format(clean);
                 },
                 onInput(e) {
                     let clean = e.target.value.replace(/\D/g, '');
                     this.modalAwalRaw = clean || '0';
                 },
                 setNominal(val) {
                     this.modalAwalRaw = val + '';
                 }
             }">

            <div class="text-center mb-6">
                <div class="w-14 h-14 rounded-2xl bg-[#DCFCE7] text-[#166534] mx-auto flex items-center justify-center mb-3">
                    <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <rect x="2" y="4" width="20" height="16" rx="2"/>
                        <path d="M7 15h0M2 10h20"/>
                    </svg>
                </div>
                <h1 class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight">Buka Shift Kasir</h1>
                <p class="text-xs text-gray-500 mt-1">
                    Silakan masukkan modal awal kas di laci sebelum memulai melayani penjualan.
                </p>
            </div>

            <!-- Pesan Error Validasi -->
            @if ($errors->any())
                <div class="mb-5 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs space-y-1">
                    @foreach ($errors->all() as $err)
                        <p class="flex items-center gap-1.5">
                            <span>&bull;</span>
                            <span>{{ $err }}</span>
                        </p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('kasir.shift.buka') }}" @submit="submitting = true" class="space-y-4">
                @csrf

                <div>
                    <label for="modal_display" class="block text-xs font-bold text-gray-700 mb-1.5">
                        Modal Awal di Laci Kasir (Rp) <span class="text-rose-500">*</span>
                    </label>

                    <div class="relative bg-gray-50 border border-gray-200 rounded-xl p-3.5 focus-within:border-[#166534] focus-within:ring-1 focus-within:ring-[#166534] focus-within:bg-white transition">
                        <span class="text-xs font-bold text-gray-400 block mb-1">Nominal Tunai:</span>
                        <div class="flex items-baseline gap-1.5">
                            <span class="text-xl font-bold text-gray-400">Rp</span>
                            <input type="text"
                                   id="modal_display"
                                   autocomplete="off"
                                   autofocus
                                   x-bind:value="formattedModal"
                                   @input="onInput($event)"
                                   placeholder="0"
                                   class="w-full bg-transparent border-0 p-0 text-2xl font-bold text-gray-900 focus:ring-0 focus:outline-none tabular-nums" />
                        </div>
                    </div>

                    <!-- Input Tersembunyi (Dikirim ke Controller) -->
                    <input type="hidden" name="modal_awal" :value="modalAwalRaw" />

                    <p class="text-[11px] text-gray-400 mt-1.5">
                        Ketik 0 jika shift dimulai tanpa modal kembalian.
                    </p>
                </div>

                <!-- Tombol Cepat Nominal -->
                <div>
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block mb-2">Pilihan Cepat:</span>
                    <div class="grid grid-cols-3 gap-2">
                        <button type="button" @click="setNominal(0)" class="py-2 px-2 rounded-lg bg-gray-50 hover:bg-gray-100 border border-gray-200 text-xs font-semibold text-gray-700 transition">
                            Rp 0
                        </button>
                        <button type="button" @click="setNominal(50000)" class="py-2 px-2 rounded-lg bg-gray-50 hover:bg-gray-100 border border-gray-200 text-xs font-semibold text-gray-700 transition">
                            Rp 50 rb
                        </button>
                        <button type="button" @click="setNominal(100000)" class="py-2 px-2 rounded-lg bg-gray-50 hover:bg-gray-100 border border-gray-200 text-xs font-semibold text-gray-700 transition">
                            Rp 100 rb
                        </button>
                        <button type="button" @click="setNominal(200000)" class="py-2 px-2 rounded-lg bg-gray-50 hover:bg-gray-100 border border-gray-200 text-xs font-semibold text-gray-700 transition">
                            Rp 200 rb
                        </button>
                        <button type="button" @click="setNominal(300000)" class="py-2 px-2 rounded-lg bg-gray-50 hover:bg-gray-100 border border-gray-200 text-xs font-semibold text-gray-700 transition">
                            Rp 300 rb
                        </button>
                        <button type="button" @click="setNominal(500000)" class="py-2 px-2 rounded-lg bg-gray-50 hover:bg-gray-100 border border-gray-200 text-xs font-semibold text-gray-700 transition">
                            Rp 500 rb
                        </button>
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit"
                            :disabled="submitting"
                            class="w-full py-3 px-6 rounded-lg bg-[#166534] hover:bg-[#14532D] text-white font-bold text-sm tracking-wide shadow-sm transition disabled:opacity-50 flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        <span x-text="submitting ? 'Membuka Shift...' : 'Mulai Shift Kasir'"></span>
                    </button>
                </div>
            </form>
        </div>
    </main>
</body>
</html>
@else
<body class="h-full bg-[#F6F7F5] text-gray-800 antialiased font-sans flex flex-col selection:bg-[#DCFCE7] selection:text-[#166534]"
      x-data="kasirPos()"
      x-init="initPos()"
      @keydown.window.f2.prevent="focusSearch()"
      @keydown.window.f9.prevent="focusBayar()">

    <!-- TOPBAR KASIR (Tema Terang Seragam) -->
    <header class="bg-white border-b border-gray-200 px-4 py-3 sticky top-0 z-30 shadow-xs">
        <div class="max-w-7xl mx-auto flex items-center justify-between gap-3">
            
            <!-- Info Kios & Logo -->
            <div class="flex items-center gap-3 min-w-0">
                @if ($user->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" 
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-gray-50 hover:bg-gray-100 border border-gray-200 text-xs font-semibold text-gray-700 transition shrink-0"
                       title="Kembali ke Dashboard Admin">
                        <svg class="w-3.5 h-3.5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <line x1="19" y1="12" x2="5" y2="12"></line>
                            <polyline points="12 19 5 12 12 5"></polyline>
                        </svg>
                        <span class="hidden sm:inline">Dashboard</span>
                    </a>
                    <div class="h-6 w-px bg-gray-200 hidden sm:block shrink-0"></div>
                @endif

                <div class="w-9 h-9 rounded-lg bg-[#DCFCE7] text-[#166534] flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <rect x="2" y="3" width="20" height="14" rx="2" ry="2"/>
                        <line x1="8" y1="21" x2="16" y2="21"/>
                        <line x1="12" y1="17" x2="12" y2="21"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold tracking-wider text-gray-900 uppercase">STOKIFY POS</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#DCFCE7] text-[#166534]">
                            {{ $kios->nama_kios }}
                        </span>
                    </div>
                    <p class="text-[11px] text-gray-500 truncate">
                        Kasir: <strong class="text-gray-900 font-semibold">{{ $user->name }}</strong>
                    </p>
                </div>
            </div>

            <!-- Navigasi Kasir & Aksi (Disederhanakan & Rapi) -->
            <div class="flex items-center gap-2 sm:gap-2.5 shrink-0">
                <!-- Notifikasi Lonceng Stok Menipis -->
                <x-notification-lonceng role="kasir" />

                <!-- Indikator Shift Berjalan & Akses Riwayat -->
                <a href="{{ route('kasir.riwayat') }}" 
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-gray-50 hover:bg-gray-100 border border-gray-200 text-xs text-gray-700 transition"
                   title="Lihat Riwayat Shift Kasir">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span class="text-[11px]">Shift: <strong>{{ \Carbon\Carbon::parse($shift->waktu_buka)->timezone('Asia/Jayapura')->format('H:i') }}</strong></span>
                </a>

                <!-- Tombol Tutup Shift -->
                <a href="{{ route('kasir.shift.tutup') }}" 
                   @click="if (cart && cart.length > 0) { if (!confirm('Keranjang belanja masih memiliki ' + cart.length + ' barang yang belum disimpan. Yakin ingin menutup shift?')) { $event.preventDefault(); } }"
                   class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg bg-amber-50 hover:bg-amber-100 border border-amber-200 text-xs font-semibold text-amber-800 transition shadow-xs">
                    <svg class="w-3.5 h-3.5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                    </svg>
                    <span>Tutup Shift</span>
                </a>

                <!-- Menu Pengguna Ringkas (Dropdown) -->
                <div class="relative" x-data="{ userMenuOpen: false }" @click.outside="userMenuOpen = false">
                    <button type="button" 
                            @click="userMenuOpen = !userMenuOpen" 
                            class="flex items-center gap-1 p-1.5 rounded-lg hover:bg-gray-100 border border-gray-200 text-gray-700 transition"
                            title="Menu Kasir">
                        <div class="w-6 h-6 rounded-md bg-[#DCFCE7] text-[#166534] font-bold text-xs flex items-center justify-center">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                        <svg class="w-3.5 h-3.5 text-gray-400 transition" :class="{'rotate-180': userMenuOpen}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <div x-show="userMenuOpen" 
                         x-cloak 
                         x-transition 
                         class="absolute right-0 mt-2 w-48 bg-white border border-gray-200 rounded-xl shadow-lg py-1.5 z-50">
                        <div class="px-3.5 py-2 border-b border-gray-100">
                            <p class="text-xs font-bold text-gray-900 truncate">{{ $user->name }}</p>
                            <p class="text-[10px] text-gray-500 capitalize">{{ $user->role }} &bull; {{ $kios->nama_kios }}</p>
                        </div>

                        <a href="{{ route('kasir.riwayat') }}" class="flex items-center gap-2 px-3.5 py-2 text-xs text-gray-700 hover:bg-gray-50 transition">
                            <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <circle cx="12" cy="12" r="10" />
                                <polyline points="12 6 12 12 16 14" />
                            </svg>
                            <span>Riwayat Shift</span>
                        </a>

                        @if ($user->isAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 px-3.5 py-2 text-xs text-gray-700 hover:bg-gray-50 transition">
                                <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <rect x="3" y="3" width="7" height="7" rx="1.5" />
                                    <rect x="14" y="3" width="7" height="7" rx="1.5" />
                                    <rect x="14" y="14" width="7" height="7" rx="1.5" />
                                    <rect x="3" y="14" width="7" height="7" rx="1.5" />
                                </svg>
                                <span>Dashboard Admin</span>
                            </a>
                        @endif

                        <div class="border-t border-gray-100 my-1"></div>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full flex items-center gap-2 px-3.5 py-2 text-xs text-rose-600 hover:bg-rose-50 transition">
                                <svg class="w-4 h-4 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                                    <polyline points="16 17 21 12 16 7" />
                                    <line x1="21" y1="12" x2="9" y2="12" />
                                </svg>
                                <span>Keluar</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </header>

    <!-- AREA KASIR UTAMA (2 KOLOM DESKTOP, 1 KOLOM MOBILE) -->
    <main class="flex-1 max-w-7xl w-full mx-auto p-3 sm:p-5 flex flex-col min-h-0 overflow-hidden">
        
        <!-- Notifikasi Error Alert Server -->
        <div x-show="errorMessage" 
             x-cloak 
             x-transition 
             class="mb-3 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2.5">
                <svg class="w-4 h-4 text-rose-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10" />
                    <line x1="12" y1="8" x2="12" y2="12" />
                    <line x1="12" y1="16" x2="12.01" y2="16" />
                </svg>
                <span x-text="errorMessage" class="font-medium"></span>
            </div>
            <button @click="errorMessage = ''" class="text-rose-500 hover:text-rose-800 font-bold text-sm px-2">&times;</button>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 flex-1 min-h-0">
            
            <!-- ============================================================== -->
            <!-- KOLOM KIRI (lg:col-span-7): PENCARIAN & KATALOG BARANG         -->
            <!-- ============================================================== -->
            <div class="lg:col-span-7 flex flex-col min-h-0 bg-white border border-gray-200 rounded-xl p-4 shadow-sm overflow-hidden">
                
                <!-- Kolom Pencarian Cepat Barcode / Nama -->
                <div class="relative mb-3">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8" />
                            <line x1="21" y1="21" x2="16.65" y2="16.65" />
                        </svg>
                    </div>
                    <input type="text"
                           x-ref="searchInput"
                           x-model="searchQuery"
                           @input.debounce.250ms="cariProduk()"
                           @keydown.enter.prevent="handleEnterSearch()"
                           placeholder="Cari nama barang atau scan kode barcode... (F2)"
                           autocomplete="off"
                           class="w-full bg-gray-50 border border-gray-200 rounded-lg pl-10 pr-24 py-2.5 text-xs sm:text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:bg-white focus:border-[#166534] focus:ring-1 focus:ring-[#166534] transition" />
                    
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center gap-1.5 text-[10px] text-gray-400">
                        <span class="bg-gray-100 text-gray-600 px-2 py-0.5 rounded border border-gray-200 font-mono">F2</span>
                        <span x-show="loadingSearch" class="animate-spin text-[#166534]">&#9696;</span>
                    </div>
                </div>

                <!-- Petunjuk Pemindai Barcode -->
                <div class="flex items-center justify-between text-[11px] text-gray-500 mb-2 px-1">
                    <span>Klik barang atau scan barcode untuk menambahkan ke keranjang</span>
                    <span x-text="`${itemsList.length} barang ditemukan`"></span>
                </div>

                <!-- Grid Kartu Barang (Touch Friendly & Responsif) -->
                <div class="flex-1 overflow-y-auto pr-1 space-y-2 min-h-[260px] sm:min-h-0">
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                        <template x-for="item in itemsList" :key="item.id">
                            <button type="button"
                                    @click="tambahKeKeranjang(item)"
                                    :disabled="item.stok <= 0"
                                    :class="item.stok <= 0 ? 'opacity-40 cursor-not-allowed bg-gray-50 border-gray-200' : 'hover:border-[#166534] hover:shadow-xs active:scale-[0.98] bg-white border-gray-200'"
                                    class="text-left p-3 rounded-xl border transition flex flex-col justify-between group h-28 relative overflow-hidden">
                                
                                <div>
                                    <div class="flex items-start justify-between gap-1">
                                        <span class="text-xs font-bold text-gray-900 line-clamp-2 leading-tight group-hover:text-[#166534]" x-text="item.nama"></span>
                                    </div>
                                    <span class="text-[10px] text-gray-400 font-mono block mt-1" x-text="item.kode"></span>
                                </div>

                                <div class="mt-2 pt-1.5 border-t border-gray-100 flex items-center justify-between">
                                    <span class="text-xs font-bold text-[#166534] tabular-nums" x-text="formatRupiah(item.harga_jual)"></span>
                                    <template x-if="item.stok <= 0">
                                        <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-rose-100 text-rose-700">Habis</span>
                                    </template>
                                    <template x-if="item.stok > 0 && item.stok <= 5">
                                        <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-amber-100 text-amber-800" x-text="`Sisa ${item.stok}`"></span>
                                    </template>
                                    <template x-if="item.stok > 5">
                                        <span class="text-[10px] text-gray-400" x-text="`Stok ${item.stok}`"></span>
                                    </template>
                                </div>
                            </button>
                        </template>
                    </div>

                    <!-- Empty State Barang -->
                    <div x-show="itemsList.length === 0 && !loadingSearch" class="py-12 text-center text-gray-400">
                        <svg class="w-10 h-10 mx-auto text-gray-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <rect x="2" y="3" width="20" height="14" rx="2" ry="2"/>
                            <line x1="8" y1="21" x2="16" y2="21"/>
                            <line x1="12" y1="17" x2="12" y2="21"/>
                        </svg>
                        <p class="text-xs font-medium text-gray-600">Tidak ada barang yang cocok.</p>
                        <p class="text-[11px] text-gray-400 mt-0.5">Coba gunakan kata kunci atau kode barang lain.</p>
                    </div>
                </div>

            </div>

            <!-- ============================================================== -->
            <!-- KOLOM KANAN (lg:col-span-5): KERANJANG & PEMBAYARAN TUNAI      -->
            <!-- ============================================================== -->
            <div class="lg:col-span-5 flex flex-col min-h-0 bg-white border border-gray-200 rounded-xl p-4 shadow-sm overflow-hidden"
                 :class="{ 'fixed inset-x-0 bottom-0 top-16 z-40 bg-white p-4 lg:static lg:inset-auto': mobileCartOpen }">
                
                <!-- Header Keranjang -->
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <div class="flex items-center gap-2">
                        <div class="w-6 h-6 rounded-lg bg-[#DCFCE7] text-[#166534] flex items-center justify-center text-xs font-bold">
                            <span x-text="totalJumlahItem"></span>
                        </div>
                        <h2 class="text-sm font-bold text-gray-900 tracking-tight">Keranjang Belanja</h2>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button"
                                @click="kosongkanKeranjang()"
                                x-show="cart.length > 0"
                                class="text-[11px] text-rose-600 hover:text-rose-800 hover:underline transition">
                            Kosongkan
                        </button>
                        
                        <!-- Mobile Close Drawer Button -->
                        <button type="button" 
                                @click="mobileCartOpen = false" 
                                class="lg:hidden text-gray-500 hover:text-gray-900 p-1">
                            &times; Tutup
                        </button>
                    </div>
                </div>

                <!-- Daftar Rincian Keranjang (Scrollable) -->
                <div class="flex-1 overflow-y-auto py-2 space-y-2 min-h-[160px] max-h-[220px] sm:max-h-none">
                    <template x-for="(c, idx) in cart" :key="c.id">
                        <div class="p-2.5 rounded-lg bg-gray-50 border border-gray-100 flex items-center justify-between gap-2">
                            <div class="min-w-0 flex-1">
                                <h4 class="text-xs font-bold text-gray-900 truncate" x-text="c.nama"></h4>
                                <div class="flex items-center gap-2 text-[10px] text-gray-500 mt-0.5">
                                    <span x-text="formatRupiah(c.harga_jual)"></span>
                                    <span>&times;</span>
                                    <span class="text-gray-700 font-semibold" x-text="`${c.jumlah} ${c.satuan}`"></span>
                                    <span>=</span>
                                    <strong class="text-gray-900 tabular-nums" x-text="formatRupiah(c.jumlah * c.harga_jual)"></strong>
                                </div>
                            </div>

                            <!-- Kontrol Kuantitas Kapsul Bersih (Minus, Input Angka, Plus, Hapus) -->
                            <div class="flex items-center gap-1.5 shrink-0">
                                <div class="inline-flex items-center rounded-lg border border-gray-200 bg-white overflow-hidden shadow-2xs">
                                    <button type="button" 
                                            @click="kurangiItem(idx)"
                                            class="w-7 h-7 flex items-center justify-center text-gray-600 hover:text-gray-900 hover:bg-gray-100 active:bg-gray-200 font-bold text-sm transition"
                                            title="Kurangi 1">
                                        &minus;
                                    </button>
                                    
                                    <input type="number" 
                                           min="1" 
                                           :max="c.stok_tersedia"
                                           x-model.number="c.jumlah"
                                           @change="validasiJumlah(idx)"
                                           @blur="validasiJumlah(idx)"
                                           class="no-spinner w-11 h-7 text-center p-0 text-xs font-bold text-gray-900 border-x border-y-0 border-gray-200 focus:ring-0 focus:border-[#166534] focus:bg-emerald-50/30 select-all" 
                                           title="Ketik jumlah langsung" />

                                    <button type="button" 
                                            @click="tambahItem(idx)"
                                            :disabled="c.jumlah >= c.stok_tersedia"
                                            :class="c.jumlah >= c.stok_tersedia ? 'opacity-30 cursor-not-allowed text-gray-300' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100 active:bg-gray-200'"
                                            class="w-7 h-7 flex items-center justify-center font-bold text-sm transition"
                                            title="Tambah 1">
                                        &#43;
                                    </button>
                                </div>

                                <button type="button" 
                                        @click="hapusItem(idx)"
                                        class="p-1.5 text-gray-400 hover:text-rose-600 hover:bg-rose-50 rounded-md transition"
                                        title="Hapus dari keranjang">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </template>

                    <!-- Empty State Keranjang -->
                    <div x-show="cart.length === 0" class="py-8 text-center text-gray-400">
                        <svg class="w-8 h-8 mx-auto text-gray-300 mb-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <p class="text-xs">Keranjang masih kosong.</p>
                        <p class="text-[10px] text-gray-400">Pilih barang dari sisi kiri.</p>
                    </div>
                </div>

                <!-- Bagian Total, Pembayaran Tunai & Kembalian -->
                <div class="pt-3 border-t border-gray-100 space-y-3">
                    
                    <!-- Display Total Belanja -->
                    <div class="p-3 rounded-xl bg-gray-50 border border-gray-200 flex items-center justify-between">
                        <span class="text-xs font-semibold text-gray-500">Total Tagihan</span>
                        <span class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight tabular-nums" x-text="formatRupiah(totalBelanja)"></span>
                    </div>

                    <!-- Input Uang Diterima & Tombol Cepat -->
                    <div>
                        <div class="flex items-center justify-between text-xs mb-1">
                            <label class="font-bold text-gray-700">Uang Diterima (F9)</label>
                            <span class="text-[10px] text-gray-400 font-mono">Tekan F9 untuk fokus</span>
                        </div>

                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-xs font-bold text-gray-400">Rp</span>
                            <input type="text"
                                   x-ref="cashInput"
                                   x-model="uangDiterimaDisplay"
                                   @input="handleUangInput($event)"
                                   @keydown.enter.prevent="submitBayar()"
                                   placeholder="0"
                                   autocomplete="off"
                                   class="w-full bg-white border border-gray-200 rounded-lg pl-9 pr-3 py-2 text-base font-bold text-gray-900 focus:outline-none focus:border-[#166534] focus:ring-1 focus:ring-[#166534] tabular-nums" />
                        </div>

                        <!-- Tombol Cepat Pecahan Uang -->
                        <div class="flex flex-wrap gap-1.5 mt-2">
                            <button type="button" 
                                    @click="setUangPas()"
                                    class="px-2.5 py-1 rounded-lg bg-[#DCFCE7] hover:bg-[#bbf7d0] text-[10px] font-bold text-[#166534] border border-[#86EFAC]/60 transition">
                                Uang Pas
                            </button>
                            <button type="button" 
                                    @click="tambahUangNominal(5000)"
                                    class="px-2 py-1 rounded-lg bg-gray-100 hover:bg-gray-200 text-[10px] font-medium text-gray-700 border border-gray-200 transition">
                                +5k
                            </button>
                            <button type="button" 
                                    @click="tambahUangNominal(10000)"
                                    class="px-2 py-1 rounded-lg bg-gray-100 hover:bg-gray-200 text-[10px] font-medium text-gray-700 border border-gray-200 transition">
                                +10k
                            </button>
                            <button type="button" 
                                    @click="tambahUangNominal(20000)"
                                    class="px-2 py-1 rounded-lg bg-gray-100 hover:bg-gray-200 text-[10px] font-medium text-gray-700 border border-gray-200 transition">
                                +20k
                            </button>
                            <button type="button" 
                                    @click="tambahUangNominal(50000)"
                                    class="px-2 py-1 rounded-lg bg-gray-100 hover:bg-gray-200 text-[10px] font-medium text-gray-700 border border-gray-200 transition">
                                +50k
                            </button>
                            <button type="button" 
                                    @click="tambahUangNominal(100000)"
                                    class="px-2 py-1 rounded-lg bg-gray-100 hover:bg-gray-200 text-[10px] font-medium text-gray-700 border border-gray-200 transition">
                                +100k
                            </button>
                        </div>
                    </div>

                    <!-- Baris Kembalian / Status Kurang -->
                    <div class="p-3 rounded-xl border flex items-center justify-between"
                         :class="uangDiterima < totalBelanja && totalBelanja > 0 ? 'bg-rose-50 border-rose-200 text-rose-700' : 'bg-[#DCFCE7] border-[#86EFAC]/60 text-[#166534]'">
                        <span class="text-xs font-bold" x-text="uangDiterima < totalBelanja && totalBelanja > 0 ? 'Kurang Pembayaran' : 'Kembalian'"></span>
                        <span class="text-base font-bold tracking-tight tabular-nums" 
                              x-text="uangDiterima < totalBelanja && totalBelanja > 0 ? formatRupiah(totalBelanja - uangDiterima) : formatRupiah(kembalian)">
                        </span>
                    </div>

                    <!-- Tombol Bayar Tunai -->
                    <button type="button"
                            @click="submitBayar()"
                            :disabled="isSubmitting || cart.length === 0 || uangDiterima < totalBelanja || totalBelanja <= 0"
                            :class="(isSubmitting || cart.length === 0 || uangDiterima < totalBelanja || totalBelanja <= 0) ? 'opacity-40 cursor-not-allowed bg-gray-200 text-gray-500' : 'bg-[#166534] hover:bg-[#14532D] text-white shadow-sm'"
                            class="w-full py-3 rounded-lg font-bold text-sm flex items-center justify-center gap-2 transition">
                        <template x-if="isSubmitting">
                            <span class="inline-flex items-center gap-2">
                                <span class="animate-spin text-white">&#9696;</span>
                                <span>Menyimpan Transaksi...</span>
                            </span>
                        </template>
                        <template x-if="!isSubmitting">
                            <span class="inline-flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                                <span>Bayar Tunai (Enter)</span>
                            </span>
                        </template>
                    </button>

                </div>

            </div>

        </div>
    </main>

    <!-- MOBILE STICKY BOTTOM BAR (Tampil hanya di layar kecil < lg) -->
    <div class="lg:hidden bg-white border-t border-gray-200 p-3 sticky bottom-0 z-20 flex items-center justify-between shadow-lg">
        <div>
            <span class="text-[10px] text-gray-500 block">Total (<span x-text="totalJumlahItem"></span> item):</span>
            <span class="text-base font-bold text-gray-900 tabular-nums" x-text="formatRupiah(totalBelanja)"></span>
        </div>
        <button type="button"
                @click="mobileCartOpen = !mobileCartOpen"
                class="px-4 py-2 rounded-lg bg-[#166534] hover:bg-[#14532D] text-white font-bold text-xs flex items-center gap-2 shadow-sm">
            <span x-text="mobileCartOpen ? 'Tutup Keranjang' : 'Buka Keranjang'"></span>
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>
    </div>

    <!-- MODAL TRANSAKSI BERHASIL (Cetak Struk & Transaksi Baru) -->
    <div x-show="showSuccessModal" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs">
        
        <div @click.outside="transaksiBaru()" 
             class="w-full max-w-md bg-white border border-gray-200 rounded-2xl p-6 sm:p-8 shadow-xl text-center space-y-5 animate-in fade-in zoom-in duration-200">
            
            <!-- Icon Checkmark -->
            <div class="w-14 h-14 rounded-full bg-[#DCFCE7] text-[#166534] flex items-center justify-center mx-auto shadow-xs">
                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
            </div>

            <div>
                <span class="text-[11px] font-bold text-[#166534] uppercase tracking-wider">Transaksi Selesai</span>
                <h3 class="text-xl font-bold text-gray-900 mt-1" x-text="lastSuccessData?.nomor"></h3>
                <p class="text-xs text-gray-500 mt-0.5" x-text="lastSuccessData?.waktu_transaksi"></p>
            </div>

            <!-- Ringkasan Angka Besar (Total, Bayar, Kembalian) -->
            <div class="p-4 rounded-xl bg-gray-50 border border-gray-200 space-y-2.5 text-left text-xs">
                <div class="flex items-center justify-between text-gray-600">
                    <span>Total Belanja</span>
                    <strong class="text-gray-900 text-sm tabular-nums" x-text="formatRupiah(lastSuccessData?.total ?? 0)"></strong>
                </div>
                <div class="flex items-center justify-between text-gray-600">
                    <span>Uang Diterima</span>
                    <span class="text-gray-900 tabular-nums" x-text="formatRupiah(lastSuccessData?.bayar ?? 0)"></span>
                </div>
                <div class="pt-2 border-t border-gray-200 flex items-center justify-between">
                    <span class="font-bold text-gray-700">Kembalian</span>
                    <strong class="text-xl font-bold text-[#166534] tabular-nums" x-text="formatRupiah(lastSuccessData?.kembalian ?? 0)"></strong>
                </div>
            </div>

            <!-- Tombol Cetak Struk & Transaksi Baru -->
            <div class="grid grid-cols-2 gap-3 pt-2">
                <a :href="lastSuccessData?.struk_url" 
                   target="_blank"
                   class="inline-flex items-center justify-center gap-1.5 py-2.5 rounded-lg bg-white hover:bg-gray-50 border border-gray-200 text-xs font-semibold text-gray-700 transition">
                    <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <polyline points="6 9 6 2 18 2 18 9"></polyline>
                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                        <rect x="6" y="14" width="12" height="8"></rect>
                    </svg>
                    <span>Cetak Struk</span>
                </a>

                <button type="button" 
                        @click="transaksiBaru()"
                        class="py-2.5 rounded-lg bg-[#166534] hover:bg-[#14532D] text-white text-xs font-bold transition shadow-xs">
                    Transaksi Baru &rarr;
                </button>
            </div>

        </div>
    </div>

    <!-- SCRIPT ALPINE POS LOGIC -->
    <script>
        function kasirPos() {
            return {
                searchQuery: '',
                itemsList: [],
                loadingSearch: false,
                cart: [],
                uangDiterima: 0,
                uangDiterimaDisplay: '',
                isSubmitting: false,
                errorMessage: '',
                mobileCartOpen: false,
                showSuccessModal: false,
                lastSuccessData: null,
                transactionToken: '',
                storageKey: 'stokify_cart_{{ $user->id }}',

                initPos() {
                    this.ensureToken();
                    this.loadCartFromStorage();
                    this.cariProduk();
                    this.$nextTick(() => this.focusSearch());
                },

                ensureToken() {
                    if (!this.transactionToken) {
                        this.transactionToken = (typeof crypto !== 'undefined' && crypto.randomUUID) 
                            ? crypto.randomUUID() 
                            : 'token_' + Math.random().toString(36).substring(2) + Date.now();
                    }
                },

                loadCartFromStorage() {
                    try {
                        const saved = localStorage.getItem(this.storageKey);
                        if (saved) {
                            this.cart = JSON.parse(saved);
                        }
                    } catch (e) {
                        this.cart = [];
                    }
                },

                saveCartToStorage() {
                    try {
                        localStorage.setItem(this.storageKey, JSON.stringify(this.cart));
                    } catch (e) {}
                },

                focusSearch() {
                    this.$refs.searchInput?.focus();
                },

                focusBayar() {
                    this.$refs.cashInput?.focus();
                },

                async cariProduk() {
                    this.loadingSearch = true;
                    try {
                        const res = await fetch(`{{ route('kasir.barang.cari') }}?q=${encodeURIComponent(this.searchQuery)}`, {
                            headers: { 'Accept': 'application/json' }
                        });
                        if (res.ok) {
                            this.itemsList = await res.json();
                        }
                    } catch (err) {
                        console.error(err);
                    } finally {
                        this.loadingSearch = false;
                    }
                },

                handleEnterSearch() {
                    const query = this.searchQuery.trim().toLowerCase();
                    if (!query) return;

                    // Cari kecocokan persis pada kode barang terlebih dahulu (scanner barcode)
                    const exactCodeMatch = this.itemsList.find(i => i.kode.toLowerCase() === query);
                    if (exactCodeMatch && exactCodeMatch.stok > 0) {
                        this.tambahKeKeranjang(exactCodeMatch);
                        this.searchQuery = '';
                        this.cariProduk();
                        return;
                    }

                    // Jika ada 1 hasil yang tersedia
                    if (this.itemsList.length === 1 && this.itemsList[0].stok > 0) {
                        this.tambahKeKeranjang(this.itemsList[0]);
                        this.searchQuery = '';
                        this.cariProduk();
                    }
                },

                tambahKeKeranjang(item) {
                    if (item.stok <= 0) return;
                    this.errorMessage = '';

                    const existingIdx = this.cart.findIndex(c => c.id === item.id);
                    if (existingIdx > -1) {
                        if (this.cart[existingIdx].jumlah < item.stok) {
                            this.cart[existingIdx].jumlah++;
                        } else {
                            this.errorMessage = `Stok maksimal untuk ${item.nama} adalah ${item.stok}.`;
                        }
                    } else {
                        this.cart.push({
                            id: item.id,
                            kode: item.kode,
                            nama: item.nama,
                            satuan: item.satuan,
                            harga_jual: item.harga_jual,
                            stok_tersedia: item.stok,
                            jumlah: 1
                        });
                    }

                    this.saveCartToStorage();
                    this.ensureToken();
                },

                tambahItem(idx) {
                    if (this.cart[idx].jumlah < this.cart[idx].stok_tersedia) {
                        this.cart[idx].jumlah++;
                        this.saveCartToStorage();
                    }
                },

                kurangiItem(idx) {
                    if (this.cart[idx].jumlah > 1) {
                        this.cart[idx].jumlah--;
                    } else {
                        this.cart.splice(idx, 1);
                    }
                    this.saveCartToStorage();
                },

                validasiJumlah(idx) {
                    const item = this.cart[idx];
                    if (!item) return;
                    if (!item.jumlah || isNaN(item.jumlah) || item.jumlah < 1) {
                        item.jumlah = 1;
                    } else if (item.jumlah > item.stok_tersedia) {
                        item.jumlah = item.stok_tersedia;
                        this.errorMessage = `Stok maksimal untuk ${item.nama} adalah ${item.stok_tersedia}.`;
                    }
                    this.saveCartToStorage();
                },

                hapusItem(idx) {
                    this.cart.splice(idx, 1);
                    this.saveCartToStorage();
                },

                kosongkanKeranjang() {
                    this.cart = [];
                    this.saveCartToStorage();
                    this.errorMessage = '';
                },

                get totalBelanja() {
                    return this.cart.reduce((sum, item) => sum + (item.jumlah * item.harga_jual), 0);
                },

                get totalJumlahItem() {
                    return this.cart.reduce((sum, item) => sum + item.jumlah, 0);
                },

                get kembalian() {
                    return Math.max(0, this.uangDiterima - this.totalBelanja);
                },

                handleUangInput(e) {
                    const clean = e.target.value.replace(/\D/g, '');
                    this.uangDiterima = clean ? parseInt(clean, 10) : 0;
                    this.uangDiterimaDisplay = this.uangDiterima > 0 ? this.uangDiterima.toLocaleString('id-ID') : '';
                },

                setUangPas() {
                    this.uangDiterima = this.totalBelanja;
                    this.uangDiterimaDisplay = this.uangDiterima > 0 ? this.uangDiterima.toLocaleString('id-ID') : '';
                },

                tambahUangNominal(val) {
                    this.uangDiterima += val;
                    this.uangDiterimaDisplay = this.uangDiterima.toLocaleString('id-ID');
                },

                formatRupiah(num) {
                    return 'Rp ' + Number(num || 0).toLocaleString('id-ID');
                },

                async submitBayar() {
                    if (this.isSubmitting) return;
                    if (this.cart.length === 0) {
                        this.errorMessage = 'Keranjang belanja masih kosong.';
                        return;
                    }
                    if (this.totalBelanja <= 0) {
                        this.errorMessage = 'Total belanja tidak boleh 0.';
                        return;
                    }
                    if (this.uangDiterima < this.totalBelanja) {
                        this.errorMessage = 'Nominal uang yang diterima kurang dari total belanja.';
                        return;
                    }

                    this.isSubmitting = true;
                    this.errorMessage = '';

                    const payload = {
                        token: this.transactionToken,
                        bayar: this.uangDiterima,
                        items: this.cart.map(c => ({
                            barang_id: c.id,
                            jumlah: c.jumlah
                        }))
                    };

                    try {
                        const tokenCsrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                        const res = await fetch("{{ route('kasir.penjualan.store') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': tokenCsrf
                            },
                            body: JSON.stringify(payload)
                        });

                        const data = await res.json();

                        if (!res.ok) {
                            // Tangani pesan error dari server (misal stok kurang)
                            let msg = data.message || 'Terjadi kesalahan saat memproses transaksi.';
                            if (data.errors) {
                                const errVals = Object.values(data.errors).flat();
                                if (errVals.length > 0) msg = errVals[0];
                            }
                            this.errorMessage = msg;
                            // Muat ulang katalog stok terkini tanpa mengosongkan keranjang
                            this.cariProduk();
                        } else {
                            // Sukses!
                            this.lastSuccessData = data.penjualan;
                            this.showSuccessModal = true;
                            window.dispatchEvent(new CustomEvent('notif-refresh'));
                            // Kosongkan keranjang & hapus storage
                            this.cart = [];
                            this.saveCartToStorage();
                            this.uangDiterima = 0;
                            this.uangDiterimaDisplay = '';
                            this.transactionToken = ''; // Reset token baru
                            this.ensureToken();
                            // Refresh daftar barang untuk update stok
                            this.cariProduk();
                        }
                    } catch (err) {
                        console.error(err);
                        this.errorMessage = 'Gagal menghubungi server. Periksa koneksi Anda.';
                    } finally {
                        this.isSubmitting = false;
                    }
                },

                transaksiBaru() {
                    this.showSuccessModal = false;
                    this.lastSuccessData = null;
                    this.ensureToken();
                    this.focusSearch();
                }
            }
        }
    </script>
</body>
</html>
@endif
