<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-900">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Struk Belanja {{ $penjualan->nomor }} - {{ $kios->nama_kios }}</title>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

    <!-- Google Fonts: Roboto Mono / Courier -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=roboto-mono:400,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style id="dynamic-page-size">
        @media print {
            @page {
                size: 58mm auto;
                margin: 0;
            }
        }
    </style>
    <style>
        /* CSS Khusus Mode Cetak (Print / Kertas Thermal) */
        @media print {
            html, body {
                background: white !important;
                color: black !important;
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                display: flex !important;
                justify-content: center !important;
                align-items: flex-start !important;
            }
            .no-print {
                display: none !important;
            }
            .struk-container {
                box-shadow: none !important;
                border: none !important;
                border-radius: 0 !important;
                padding: 3mm 2mm !important;
                margin: 0 auto !important;
                color: black !important;
            }
            .struk-58mm {
                width: 58mm !important;
                max-width: 58mm !important;
            }
            .struk-80mm {
                width: 80mm !important;
                max-width: 80mm !important;
            }
            .border-dashed {
                border-color: #000000 !important;
            }
        }

        /* Tampilan di Layar Monitor */
        .struk-58mm {
            width: 100%;
            max-width: 320px;
        }
        .struk-80mm {
            width: 100%;
            max-width: 440px;
        }
    </style>
</head>
<body x-data="{
        ukuran: localStorage.getItem('stokify_paper_size') || '58mm',
        pilihUkuran(u) {
            this.ukuran = u;
            localStorage.setItem('stokify_paper_size', u);
            let el = document.getElementById('dynamic-page-size');
            if (el) {
                el.innerHTML = `@media print { @page { size: ${u} auto; margin: 0; } }`;
            }
        }
      }" 
      x-init="pilihUkuran(ukuran)"
      class="min-h-full bg-[#14532D] text-slate-800 antialiased flex flex-col items-center py-6 px-3">

    <!-- Tombol Aksi Layar (Hidden Saat Print) -->
    <div class="no-print w-full max-w-md mb-5 flex flex-wrap items-center justify-between gap-2 bg-[#0F3E22]/90 p-2.5 rounded-2xl border border-[#166534] shadow-xl backdrop-blur-sm">
        <div class="flex items-center gap-1.5">
            <a href="{{ route('kasir.index') }}" 
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-[#14532D] hover:bg-[#166534] border border-[#166534] text-xs font-semibold text-[#DCFCE7] transition">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span>Kasir</span>
            </a>

            <a href="{{ route('kasir.riwayat') }}" 
               class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl bg-[#14532D] hover:bg-[#166534] border border-[#166534] text-xs font-semibold text-emerald-200 hover:text-white transition">
                <span>Riwayat</span>
            </a>
        </div>

        <!-- Pilihan Ukuran Kertas Thermal -->
        <div class="flex items-center gap-1 bg-[#14532D] p-1 rounded-xl border border-[#166534]">
            <span class="text-[10px] font-bold text-emerald-300 px-1.5 uppercase tracking-wider">Kertas:</span>
            <button type="button" 
                    @click="pilihUkuran('58mm')" 
                    :class="ukuran === '58mm' ? 'bg-[#DCFCE7] text-[#14532D] font-bold shadow-xs' : 'text-emerald-200 hover:text-white'"
                    class="px-2 py-0.5 rounded-lg text-xs transition cursor-pointer">
                58mm
            </button>
            <button type="button" 
                    @click="pilihUkuran('80mm')" 
                    :class="ukuran === '80mm' ? 'bg-[#DCFCE7] text-[#14532D] font-bold shadow-xs' : 'text-emerald-200 hover:text-white'"
                    class="px-2 py-0.5 rounded-lg text-xs transition cursor-pointer">
                80mm
            </button>
        </div>

        <button type="button" 
                onclick="window.print()" 
                class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-[#DCFCE7] hover:bg-white text-[#14532D] text-xs font-black shadow-md hover:shadow-lg transition cursor-pointer">
            <svg class="w-3.5 h-3.5 text-[#14532D]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <polyline points="6 9 6 2 18 2 18 9"></polyline>
                <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                <rect x="6" y="14" width="12" height="8"></rect>
            </svg>
            <span>Cetak (Print)</span>
        </button>
    </div>

    <!-- KERTAS STRUK THERMAL (58mm / 80mm Compatible) -->
    <div class="struk-container bg-white p-5 rounded-2xl shadow-2xl border border-slate-300 font-mono text-xs leading-tight text-black transition-all"
         :class="ukuran === '58mm' ? 'struk-58mm' : 'struk-80mm'">
        
        <!-- Header Toko / Kios -->
        <div class="text-center pb-2">
            <h1 class="text-sm font-bold uppercase tracking-wider">{{ $kios->nama_kios }}</h1>
            @if ($kios->alamat)
                <p class="text-[11px] text-slate-600 mt-0.5 leading-snug">{{ $kios->alamat }}</p>
            @endif
            @if ($kios->no_telepon)
                <p class="text-[11px] text-slate-600">Telp: {{ $kios->no_telepon }}</p>
            @endif
        </div>

        <div class="border-b border-dashed border-slate-400 my-2"></div>

        <!-- Meta Nota & Kasir -->
        <div class="text-[11px] space-y-0.5">
            <div class="flex justify-between">
                <span>No. Nota:</span>
                <span class="font-bold">{{ $penjualan->nomor }}</span>
            </div>
            <div class="flex justify-between">
                <span>Waktu:</span>
                <span>{{ $penjualan->waktu_transaksi->format('d/m/Y H:i') }}</span>
            </div>
            <div class="flex justify-between">
                <span>Kasir:</span>
                <span>{{ $penjualan->user?->name ?? 'Kasir' }}</span>
            </div>
        </div>

        @if ($penjualan->status === 'dibatalkan')
            <div class="my-2 p-2 border-2 border-dashed border-red-600 bg-red-50 text-red-700 text-center rounded">
                <div class="text-sm font-black tracking-widest uppercase">*** DIBATALKAN ***</div>
                <div class="text-[10px] mt-0.5">
                    Oleh: {{ $penjualan->dibatalkanOleh?->name ?? 'Petugas' }} &bull; {{ $penjualan->dibatalkan_pada?->format('d/m/Y H:i') }}
                </div>
                <div class="text-[10px] italic mt-0.5">
                    Alasan: "{{ $penjualan->alasan_batal }}"
                </div>
            </div>
        @endif

        <div class="border-b border-dashed border-slate-400 my-2"></div>

        <!-- Daftar Rincian Belanja -->
        <div class="space-y-2 text-[11px]">
            @foreach ($penjualan->detail as $item)
                <div>
                    <div class="font-bold text-slate-900">{{ $item->nama_barang }}</div>
                    <div class="flex justify-between text-slate-600">
                        <span>{{ $item->jumlah }} {{ $item->satuan }} &times; {{ number_format($item->harga_jual, 0, ',', '.') }}</span>
                        <span class="font-semibold text-slate-900">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="border-b border-dashed border-slate-400 my-2"></div>

        <!-- Rincian Total, Bayar, Kembalian -->
        <div class="space-y-1 text-xs">
            <div class="flex justify-between font-bold text-sm">
                <span>TOTAL:</span>
                <span>Rp {{ number_format($penjualan->total, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between text-[11px]">
                <span>Metode:</span>
                <span class="font-bold uppercase">{{ $penjualan->metode_bayar === 'qris' ? 'QRIS' : 'TUNAI' }}</span>
            </div>
            <div class="flex justify-between text-[11px]">
                <span>{{ $penjualan->metode_bayar === 'qris' ? 'Nominal QRIS:' : 'Tunai:' }}</span>
                <span>Rp {{ number_format($penjualan->bayar, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between text-[11px] font-semibold">
                <span>Kembalian:</span>
                <span>Rp {{ number_format($penjualan->kembalian, 0, ',', '.') }}</span>
            </div>
        </div>

        <div class="border-b border-dashed border-slate-400 my-2"></div>

        <!-- Footer Ucapan Struk -->
        <div class="text-center text-[10px] text-slate-600 space-y-1 pt-1">
            <p class="font-bold">Terima kasih atas kunjungan Anda!</p>
            <p>Barang yang sudah dibeli tidak dapat ditukar/dikembalikan.</p>
        </div>

    </div>

</body>
</html>