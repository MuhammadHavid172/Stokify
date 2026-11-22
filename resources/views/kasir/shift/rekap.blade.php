<!DOCTYPE html>
<html lang="id" class="h-full bg-[#F6F7F5]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Rekap Shift Kasir #{{ $shift->id }} - {{ $user->kios->nama_kios }} - Stokify</title>

    <!-- Favicon Stokify -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

    <!-- Fonts: Inter & Poppins -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|poppins:500,600,700,800&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        @media print {
            body {
                background: white !important;
                color: black !important;
            }
            .no-print {
                display: none !important;
            }
            .print-only {
                display: block !important;
            }
            .rekap-card {
                border: none !important;
                background: white !important;
                box-shadow: none !important;
                color: black !important;
                padding: 0 !important;
                max-width: 100% !important;
            }
        }
    </style>
</head>
<body class="min-h-full bg-[#F6F7F5] text-gray-800 antialiased font-sans flex flex-col selection:bg-[#DCFCE7] selection:text-[#166534]">

    <!-- Topbar Kasir Sederhana (No Print) -->
    <header class="no-print bg-white border-b border-gray-200 px-4 py-3 sticky top-0 z-30 shadow-xs">
        <div class="max-w-4xl mx-auto flex items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-[#DCFCE7] text-[#166534] flex items-center justify-center shrink-0 shadow-xs">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-sm font-bold text-gray-900">Rekapitulasi Shift Kasir</h1>
                    <p class="text-[11px] text-gray-500 font-medium">{{ $user->kios->nama_kios }} &bull; Shift #{{ $shift->id }}</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button type="button" 
                        onclick="window.print()" 
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white hover:bg-gray-50 border border-gray-200 text-xs font-semibold text-gray-700 transition cursor-pointer shadow-2xs">
                    <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <polyline points="6 9 6 2 18 2 18 9"/>
                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
                        <rect x="6" y="14" width="12" height="8"/>
                    </svg>
                    <span>Cetak Rekap</span>
                </button>
                <a href="{{ route('kasir.index') }}" 
                   class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-xl bg-[#166534] hover:bg-[#14532D] text-xs font-bold text-white transition shadow-xs">
                    <span>Selesai</span>
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <polyline points="9 18 15 12 9 6"/>
                    </svg>
                </a>
            </div>
        </div>
    </header>

    <!-- Konten Rekap Shift -->
    <main class="flex-1 max-w-xl w-full mx-auto p-4 sm:p-6 my-auto">
        <div class="rekap-card bg-white border border-gray-200 rounded-3xl p-6 sm:p-8 shadow-sm space-y-6">

            <!-- Header Tiket Rekap -->
            <div class="text-center pb-5 border-b border-gray-100">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-[#DCFCE7] text-[#166534] mb-2.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#166534]"></span>
                    <span>Shift Selesai &bull; Rekonsiliasi Kasir</span>
                </span>
                <h2 class="text-2xl font-black text-gray-900 uppercase tracking-tight">{{ $user->kios->nama_kios }}</h2>
                <div class="flex items-center justify-center gap-2 mt-1.5 text-xs text-gray-500">
                    <span>Petugas Kasir:</span>
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-gray-100 font-bold text-gray-900">
                        {{ $shift->user->name }}
                    </span>
                </div>
                @if ($shift->ditutup_paksa)
                    <div class="mt-2.5 inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-semibold bg-amber-50 text-amber-800 border border-amber-200">
                        <svg class="w-4 h-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <span>Ditutup paksa oleh Admin ({{ $shift->ditutupOlehUser?->name ?? 'Admin' }})</span>
                    </div>
                @endif
            </div>

            <!-- Detail Waktu & Durasi Shift -->
            <div class="grid grid-cols-3 gap-2 py-3.5 px-4 rounded-2xl bg-gray-50 border border-gray-100 text-xs">
                <div class="text-center sm:text-left">
                    <span class="text-gray-500 text-[11px] block">Waktu Buka</span>
                    <strong class="text-gray-900 font-bold font-mono text-xs">{{ \Carbon\Carbon::parse($shift->waktu_buka)->timezone('Asia/Jayapura')->format('d M, H:i') }} WIT</strong>
                </div>
                <div class="text-center">
                    <span class="text-gray-500 text-[11px] block">Waktu Tutup</span>
                    <strong class="text-gray-900 font-bold font-mono text-xs">{{ \Carbon\Carbon::parse($shift->waktu_tutup)->timezone('Asia/Jayapura')->format('d M, H:i') }} WIT</strong>
                </div>
                <div class="text-center sm:text-right">
                    <span class="text-gray-500 text-[11px] block">Durasi Kerja</span>
                    <strong class="text-[#166534] font-bold text-xs">{{ $shift->durasi }}</strong>
                </div>
            </div>

            <!-- BUKU REKONSILIASI KAS (Struktur Terpadu & Rapi) -->
            <div class="rounded-2xl border border-gray-200/90 bg-gray-50/40 p-4 sm:p-5 space-y-3">
                <div class="flex items-center justify-between pb-2 border-b border-gray-200/80">
                    <h3 class="text-xs font-bold text-gray-700 uppercase tracking-wider">Perhitungan Uang Kas Laci</h3>
                    <span class="text-[10px] text-gray-500 font-medium">Rekonsiliasi Fisik</span>
                </div>

                <!-- 1. Modal Awal -->
                <div class="flex items-center justify-between py-1 text-xs">
                    <span class="text-gray-600 font-medium">Modal Awal Laci</span>
                    <span class="font-mono font-bold text-gray-900 text-sm tabular-nums">Rp {{ number_format($shift->modal_awal, 0, ',', '.') }}</span>
                </div>

                <!-- 2. Total Penjualan Tunai -->
                <div class="flex items-center justify-between py-1 text-xs">
                    <span class="text-gray-600 font-medium">Total Penjualan Tunai</span>
                    <span class="font-mono font-bold text-[#166534] text-sm tabular-nums">+ Rp {{ number_format($shift->total_penjualan_tunai, 0, ',', '.') }}</span>
                </div>

                <div class="border-t border-dashed border-gray-300 my-1"></div>

                <!-- 3. Total Seharusnya -->
                <div class="flex items-center justify-between py-1 text-xs">
                    <div>
                        <span class="text-gray-900 font-bold block">Uang Seharusnya di Laci</span>
                        <span class="text-[10px] text-gray-500 font-normal">Modal Awal + Penjualan Tunai</span>
                    </div>
                    <span class="font-mono font-black text-gray-900 text-base tabular-nums">Rp {{ number_format($shift->uang_seharusnya, 0, ',', '.') }}</span>
                </div>

                <!-- 4. Uang Fisik Hasil Hitung -->
                <div class="flex items-center justify-between py-2 px-3 rounded-xl bg-white border border-gray-200 shadow-2xs text-xs">
                    <div>
                        <span class="text-gray-900 font-bold block">Uang Fisik Dihitung Kasir</span>
                        <span class="text-[10px] text-gray-500 font-normal">Hasil hitungan uang tunai saat tutup</span>
                    </div>
                    <span class="font-mono font-black text-gray-900 text-base tabular-nums">Rp {{ number_format($shift->uang_fisik, 0, ',', '.') }}</span>
                </div>
            </div>

            <!-- KARTU HASIL REKONSILIASI & STATUS SELISIH -->
            <div class="p-4 rounded-2xl border flex items-center justify-between transition-all {{ $shift->selisih == 0 ? 'bg-[#DCFCE7] border-[#86EFAC] text-[#166534]' : ($shift->selisih < 0 ? 'bg-rose-50 border-rose-200 text-rose-800' : 'bg-amber-50 border-amber-200 text-amber-900') }}">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center font-black text-lg shadow-xs shrink-0 {{ $shift->selisih == 0 ? 'bg-[#166534] text-white' : ($shift->selisih < 0 ? 'bg-rose-600 text-white' : 'bg-amber-600 text-white') }}">
                        @if ($shift->selisih == 0)
                            ✓
                        @elseif ($shift->selisih < 0)
                            −
                        @else
                            +
                        @endif
                    </div>
                    <div>
                        <span class="text-[10px] block uppercase font-bold tracking-wider opacity-75">Hasil Rekonsiliasi</span>
                        <strong class="text-sm font-black">
                            @if ($shift->selisih == 0)
                                Sesuai / Pas (Tidak Ada Selisih)
                            @elseif ($shift->selisih < 0)
                                Uang Fisik Kurang
                            @else
                                Uang Fisik Lebih
                            @endif
                        </strong>
                    </div>
                </div>
                <div class="text-right">
                    <span class="tabular-nums text-lg font-black font-mono block">
                        @if ($shift->selisih == 0)
                            Rp 0
                        @elseif ($shift->selisih < 0)
                            - Rp {{ number_format(abs($shift->selisih), 0, ',', '.') }}
                        @else
                            + Rp {{ number_format($shift->selisih, 0, ',', '.') }}
                        @endif
                    </span>
                    <span class="text-[10px] opacity-75 block">
                        @if ($shift->selisih == 0)
                            Kas Akurat
                        @elseif ($shift->selisih < 0)
                            Defisit Kas
                        @else
                            Surplus Kas
                        @endif
                    </span>
                </div>
            </div>

            <!-- Ringkasan Transaksi & Batal (2 Kolom Rapi) -->
            <div class="grid grid-cols-2 gap-3 text-xs">
                <div class="p-3.5 rounded-2xl bg-gray-50 border border-gray-100 flex items-center justify-between">
                    <div>
                        <span class="text-gray-500 block text-[11px] font-medium">Transaksi Selesai</span>
                        <strong class="text-gray-900 text-base font-black font-mono tabular-nums">{{ $shift->jumlah_transaksi ?? 0 }}</strong> 
                        <span class="text-[11px] text-gray-500">nota</span>
                    </div>
                    <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold text-xs shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                </div>

                <div class="p-3.5 rounded-2xl bg-gray-50 border border-gray-100 flex items-center justify-between">
                    <div>
                        <span class="text-gray-500 block text-[11px] font-medium">Nota Dibatalkan</span>
                        <strong class="{{ ($shift->jumlah_nota_batal ?? 0) > 0 ? 'text-rose-600' : 'text-gray-900' }} text-base font-black font-mono tabular-nums">{{ $shift->jumlah_nota_batal ?? 0 }}</strong> 
                        <span class="text-[11px] text-gray-500">nota</span>
                    </div>
                    <div class="w-8 h-8 rounded-xl {{ ($shift->jumlah_nota_batal ?? 0) > 0 ? 'bg-rose-100 text-rose-800' : 'bg-gray-200 text-gray-500' }} flex items-center justify-center font-bold text-xs shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Catatan Pertanggungjawaban Kasir -->
            @if ($shift->catatan)
                <div class="p-3.5 rounded-2xl bg-amber-50/50 border border-amber-200/70 text-xs">
                    <div class="flex items-center gap-1.5 text-amber-800 text-[11px] font-bold uppercase tracking-wider mb-1">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                        </svg>
                        <span>Catatan Kasir:</span>
                    </div>
                    <p class="text-gray-800 italic pl-5 leading-relaxed teks-penjelasan">"{{ $shift->catatan }}"</p>
                </div>
            @endif

            <!-- Tombol Aksi di Bawah (No Print) -->
            <div class="no-print pt-2 flex flex-col sm:flex-row items-center gap-3">
                <button type="button" 
                        onclick="window.print()" 
                        class="w-full sm:w-1/2 py-2.5 px-4 rounded-xl bg-white hover:bg-gray-50 border border-gray-200 text-xs font-semibold text-gray-700 transition flex items-center justify-center gap-2 cursor-pointer shadow-2xs">
                    <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <polyline points="6 9 6 2 18 2 18 9"/>
                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
                        <rect x="6" y="14" width="12" height="8"/>
                    </svg>
                    <span>Cetak Lembar Rekap</span>
                </button>
                <a href="{{ route('kasir.index') }}" 
                   class="w-full sm:w-1/2 py-2.5 px-6 rounded-xl bg-[#166534] hover:bg-[#14532D] text-white font-bold text-xs text-center shadow-xs transition">
                    Kembali ke Layar Kasir
                </a>
            </div>

        </div>
    </main>

</body>
</html>