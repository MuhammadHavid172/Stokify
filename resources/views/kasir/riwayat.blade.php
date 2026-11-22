<!DOCTYPE html>
<html lang="id" class="h-full bg-[#F6F7F5]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Riwayat Shift Kasir - {{ $user->name }} - Stokify</title>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|poppins:500,600,700,800&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-[#F6F7F5] text-gray-800 antialiased font-sans flex flex-col selection:bg-[#DCFCE7] selection:text-[#166534]"
      x-data="{
          modalVoidOpen: false,
          voidPenjualanId: null,
          voidNomor: '',
          voidTotal: '',
          voidAlasan: '',
          voidSubmitting: false,
          openVoidModal(id, nomor, total) {
              this.voidPenjualanId = id;
              this.voidNomor = nomor;
              this.voidTotal = total;
              this.voidAlasan = '';
              this.modalVoidOpen = true;
          }
      }">

    <!-- Topbar Kasir -->
    <header class="bg-white border-b border-gray-200 px-4 py-3 sticky top-0 z-30 shadow-xs">
        <div class="max-w-6xl mx-auto flex items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <a href="{{ route('kasir.index') }}" 
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white hover:bg-gray-50 border border-gray-200 text-xs font-semibold text-gray-700 transition">
                    <svg class="w-3.5 h-3.5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    <span>Kembali ke Kasir</span>
                </a>
                <span class="text-sm font-bold text-gray-900">Riwayat Transaksi Shift Kasir</span>
            </div>

            <div class="flex items-center gap-3">
                <x-notification-lonceng role="kasir" />
                @if ($shift)
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-gray-50 border border-gray-200 text-xs">
                        <span class="w-2 h-2 rounded-full {{ $shift->status === 'berjalan' ? 'bg-emerald-500 animate-pulse' : 'bg-gray-400' }}"></span>
                        <span class="text-gray-600">Shift #{{ $shift->id }} ({{ ucfirst($shift->status) }})</span>
                    </div>
                @endif
                <span class="text-xs text-gray-500 hidden sm:inline">Kasir: <strong class="text-gray-900 font-semibold">{{ $user->name }}</strong></span>
            </div>
        </div>
    </header>

    <!-- Konten Utama -->
    <main class="flex-1 max-w-6xl w-full mx-auto p-4 sm:p-6 space-y-6 overflow-y-auto">
        
        <!-- Flash Alert Sukses / Error -->
        @if (session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-2.5">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span class="font-medium">{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if ($errors->any())
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs space-y-1 shadow-xs">
                @foreach ($errors->all() as $error)
                    <p>&bull; {{ $error }}</p>
                @endforeach
            </div>
        @endif

        <!-- Header Info & Ringkasan Metrik Shift Ini -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-2 border-b border-gray-200/60">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight text-balance">Riwayat Transaksi Shift Anda</h1>
                <p class="text-xs text-gray-500 mt-0.5">
                    @if ($shift)
                        Shift dibuka sejak {{ \Carbon\Carbon::parse($shift->waktu_buka)->timezone('Asia/Jayapura')->format('d M Y, H:i') }} WIT &bull; Durasi: {{ $shift->durasi }}
                    @else
                        Belum ada riwayat shift yang tercatat.
                    @endif
                </p>
            </div>
            @if ($shift && $shift->status === 'berjalan')
                <a href="{{ route('kasir.shift.tutup') }}" 
                   class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg bg-amber-50 hover:bg-amber-100 border border-amber-200 text-xs font-semibold text-amber-800 transition shrink-0">
                    <svg class="w-4 h-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                    </svg>
                    <span>Tutup Shift Kasir</span>
                </a>
            @endif
        </div>

        <!-- 3 Kartu Ringkasan Penjualan Kasir Shift Ini (Hanya nota selesai) -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-stretch">
            <!-- Omzet Kasir -->
            <div class="p-5 rounded-xl bg-white border border-gray-200 flex items-center justify-between shadow-sm">
                <div>
                    <span class="text-xs font-semibold text-gray-500">Penjualan Tunai Selesai</span>
                    <h3 class="text-2xl font-bold text-gray-900 mt-1 tabular-nums">Rp {{ number_format($totalOmzetShift, 0, ',', '.') }}</h3>
                </div>
                <div class="w-10 h-10 rounded-lg bg-[#DCFCE7] text-[#166534] flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>

            <!-- Total Transaksi -->
            <div class="p-5 rounded-xl bg-white border border-gray-200 flex items-center justify-between shadow-sm">
                <div>
                    <span class="text-xs font-semibold text-gray-500">Transaksi Selesai</span>
                    <h3 class="text-2xl font-bold text-gray-900 mt-1 tabular-nums">{{ number_format($totalTransaksiShift) }} Transaksi</h3>
                </div>
                <div class="w-10 h-10 rounded-lg bg-[#DCFCE7] text-[#166534] flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                </div>
            </div>

            <!-- Total Barang Terjual -->
            <div class="p-5 rounded-xl bg-white border border-gray-200 flex items-center justify-between shadow-sm">
                <div>
                    <span class="text-xs font-semibold text-gray-500">Total Barang Terjual</span>
                    <h3 class="text-2xl font-bold text-gray-900 mt-1 tabular-nums">{{ number_format($totalItemShift) }} Barang</h3>
                </div>
                <div class="w-10 h-10 rounded-lg bg-[#DCFCE7] text-[#166534] flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Tabel Transaksi Shift -->
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <div class="p-4 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold text-gray-900">Daftar Struk Transaksi Pada Shift Ini</h2>
                    <span class="text-[11px] text-gray-500">Nota yang dibatalkan tidak masuk dalam perhitungan uang kasir.</span>
                </div>
                <span class="text-xs text-gray-500">{{ $penjualanList->count() }} nota tercatat</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50 text-gray-500 uppercase font-semibold text-[11px] tracking-wider">
                            <th class="py-3 px-4">No. Nota</th>
                            <th class="py-3 px-4">Jam</th>
                            <th class="py-3 px-4">Jumlah Barang</th>
                            <th class="py-3 px-4 text-right">Total Belanja</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($penjualanList as $p)
                            @php $isBatal = $p->status === 'dibatalkan'; @endphp
                            <tr class="hover:bg-gray-50/80 transition {{ $isBatal ? 'opacity-70 bg-gray-50/50' : '' }}">
                                <td class="py-3 px-4 font-mono font-bold text-gray-900">
                                    <span class="{{ $isBatal ? 'line-through text-gray-400' : '' }}">{{ $p->nomor }}</span>
                                    @if ($isBatal)
                                        <span class="text-[10px] text-rose-600 block font-normal mt-0.5">Alasan: {{ $p->alasan_batal }}</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-gray-500">
                                    {{ $p->waktu_transaksi->format('H:i') }} WIT
                                </td>
                                <td class="py-3 px-4 text-gray-700">
                                    <span class="{{ $isBatal ? 'line-through text-gray-400' : '' }}">{{ $p->detail->sum('jumlah') }} barang</span>
                                    <span class="text-[10px] text-gray-400 block">({{ $p->detail->count() }} jenis barang)</span>
                                </td>
                                <td class="py-3 px-4 text-right font-bold tabular-nums {{ $isBatal ? 'line-through text-gray-400' : 'text-gray-900' }}">
                                    Rp {{ number_format($p->total, 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-4 text-center">
                                    @if ($isBatal)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                            Dibatalkan
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-[#DCFCE7] text-[#166534]">
                                            Selesai
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <div class="inline-flex items-center justify-end gap-1.5">
                                        <!-- Tombol Struk -->
                                        <a href="{{ route('kasir.penjualan.struk', $p->id) }}" 
                                           target="_blank"
                                           class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-white hover:bg-gray-50 border border-gray-200 text-xs font-semibold text-gray-700 transition">
                                            <svg class="w-3.5 h-3.5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <polyline points="6 9 6 2 18 2 18 9"></polyline>
                                                <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                                                <rect x="6" y="14" width="12" height="8"></rect>
                                            </svg>
                                            <span>Struk</span>
                                        </a>

                                        <!-- Tombol Batalkan Nota (Hanya jika shift masih berjalan dan nota belum batal) -->
                                        @if (! $isBatal && $shift && $shift->status === 'berjalan')
                                            <button type="button"
                                                    @click="openVoidModal({{ $p->id }}, '{{ $p->nomor }}', 'Rp {{ number_format($p->total, 0, ',', '.') }}')"
                                                    class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 border border-rose-200 text-xs font-semibold text-rose-700 transition">
                                                <svg class="w-3.5 h-3.5 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <circle cx="12" cy="12" r="10"/>
                                                    <line x1="15" y1="9" x2="9" y2="15"/>
                                                    <line x1="9" y1="9" x2="15" y2="15"/>
                                                </svg>
                                                <span>Batalkan Nota</span>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-gray-400">
                                    <svg class="w-10 h-10 mx-auto text-gray-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <rect x="2" y="3" width="20" height="14" rx="2" ry="2"/>
                                        <line x1="8" y1="21" x2="16" y2="21"/>
                                        <line x1="12" y1="17" x2="12" y2="21"/>
                                    </svg>
                                    <p class="font-medium text-xs text-gray-600">Belum ada transaksi penjualan pada shift ini.</p>
                                    <a href="{{ route('kasir.index') }}" class="text-[#166534] font-semibold text-xs mt-2 inline-block hover:underline">
                                        Mulai Layani Transaksi di Kasir &rarr;
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </main>

    <!-- MODAL BATALKAN TRANSAKSI (BATALKAN NOTA) -->
    <div x-show="modalVoidOpen" 
         x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-xs flex items-center justify-center p-4"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">

        <div @click.away="if (!voidSubmitting) modalVoidOpen = false" 
             class="bg-white border border-gray-200 rounded-2xl max-w-md w-full p-6 shadow-xl space-y-4">

            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-rose-50 border border-rose-200 text-rose-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="8" x2="12" y2="12"/>
                        <line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-900">Batalkan Nota Transaksi</h3>
                    <p class="text-xs text-gray-500">Stok barang akan otomatis dikembalikan ke sistem kios.</p>
                </div>
            </div>

            <div class="p-3 rounded-xl bg-gray-50 border border-gray-200 text-xs space-y-1">
                <div class="flex justify-between">
                    <span class="text-gray-500">Nomor Nota:</span>
                    <strong class="text-gray-900 font-mono" x-text="voidNomor"></strong>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Total Transaksi:</span>
                    <strong class="text-gray-900 font-mono tabular-nums" x-text="voidTotal"></strong>
                </div>
            </div>

            <p class="text-xs text-gray-500 leading-relaxed teks-penjelasan">
                Aturan Pembatalan: Nota hanya boleh dibatalkan selama shift kasir masih berjalan. Setelah shift ditutup, seluruh transaksi terkunci demi keakuratan uang yang sudah direkonsiliasi.
            </p>

            <form :action="'{{ url('/kasir/penjualan') }}/' + voidPenjualanId + '/batal'" 
                  method="POST" 
                  @submit="voidSubmitting = true" 
                  class="space-y-4">
                @csrf

                <div>
                    <label for="alasan_void" class="block text-xs font-semibold text-gray-700 mb-1.5">
                        Alasan Pembatalan <span class="text-rose-500">*</span>
                    </label>
                    <textarea id="alasan_void"
                              name="alasan"
                              x-model="voidAlasan"
                              rows="3"
                              required
                              placeholder="Tuliskan alasan pembatalan transaksi nota ini (min. 5 karakter)..."
                              class="w-full px-3.5 py-2.5 bg-white border border-gray-200 rounded-xl text-xs text-gray-900 placeholder-gray-400 focus:outline-none focus:border-rose-500 focus:ring-1 focus:ring-rose-500 transition leading-relaxed"></textarea>
                    <span class="text-[10px] text-gray-400 mt-1 block" x-show="voidAlasan.length < 5 && voidAlasan.length > 0">
                        Minimal 5 karakter (saat ini: <span x-text="voidAlasan.length"></span> karakter).
                    </span>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2 border-t border-gray-100">
                    <button type="button" 
                            @click="modalVoidOpen = false" 
                            :disabled="voidSubmitting"
                            class="px-4 py-2.5 rounded-lg border border-gray-200 bg-white hover:bg-gray-50 text-xs font-semibold text-gray-700 transition">
                        Batal
                    </button>
                    <button type="submit" 
                            :disabled="voidSubmitting || voidAlasan.trim().length < 5"
                            class="px-4 py-2.5 rounded-lg bg-rose-600 hover:bg-rose-700 text-white font-semibold text-xs shadow-xs transition disabled:opacity-50 disabled:cursor-not-allowed">
                        <span x-text="voidSubmitting ? 'Membatalkan...' : 'Ya, Batalkan Nota'"></span>
                    </button>
                </div>
            </form>

        </div>
    </div>

</body>
</html>
