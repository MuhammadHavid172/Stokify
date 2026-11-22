<x-layouts.admin title="Detail Penjualan {{ $penjualan->nomor }}">
    <div class="space-y-6"
         x-data="{
             modalVoidOpen: false,
             voidAlasan: '',
             voidSubmitting: false
         }">

        <!-- Header Nota & Aksi -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-gray-200">
            <div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.penjualan.index') }}" class="text-xs text-gray-500 hover:text-[#166534] transition">
                        &larr; Riwayat Penjualan
                    </a>
                    <span class="text-xs text-gray-400">•</span>
                    <span class="text-xs font-mono text-gray-700">{{ $penjualan->nomor }}</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 tracking-tight mt-1 text-balance">
                    Nota {{ $penjualan->nomor }}
                </h1>
                <p class="text-xs text-gray-500 mt-0.5 text-pretty">
                    Transaksi dilayani pada {{ $penjualan->waktu_transaksi->translatedFormat('d F Y H:i') }} WIT oleh {{ $penjualan->user?->name ?? 'Kasir' }}.
                    @if ($penjualan->shift)
                        &bull; <a href="{{ route('admin.shift.show', $penjualan->shift_id) }}" class="underline text-[#166534]">Shift #{{ $penjualan->shift_id }} ({{ ucfirst($penjualan->shift->status) }})</a>
                    @endif
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('kasir.penjualan.struk', $penjualan->id) }}" 
                   target="_blank"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-white hover:bg-gray-50 border border-gray-300 text-xs font-semibold text-gray-700 shadow-sm transition">
                    <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <polyline points="6 9 6 2 18 2 18 9"></polyline>
                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                        <rect x="6" y="14" width="12" height="8"></rect>
                    </svg>
                    <span>Cetak Struk Kasir</span>
                </a>

                @if ($penjualan->status === 'selesai')
                    @if ($penjualan->shift && $penjualan->shift->status === 'berjalan')
                        <button type="button" 
                                @click="modalVoidOpen = true"
                                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-red-50 hover:bg-red-100 border border-red-200 text-xs font-semibold text-red-700 transition shadow-sm">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <circle cx="12" cy="12" r="10"/>
                                <line x1="15" y1="9" x2="9" y2="15"/>
                                <line x1="9" y1="9" x2="15" y2="15"/>
                            </svg>
                            <span>Batalkan Nota</span>
                        </button>
                    @else
                        <span class="inline-flex items-center px-3 py-2 rounded-lg bg-gray-100 border border-gray-200 text-xs text-gray-500" title="Shift transaksi ini sudah ditutup sehingga transaksi terkunci.">
                            Shift sudah ditutup
                        </span>
                    @endif
                @endif

                <a href="{{ route('admin.penjualan.index') }}" 
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-white hover:bg-gray-50 border border-gray-300 text-xs font-semibold text-gray-700 shadow-sm transition">
                    Kembali
                </a>
            </div>
        </div>

        <!-- Flash Alert Sukses / Error -->
        @if (session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center gap-2.5 shadow-sm">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs space-y-1 shadow-sm">
                @foreach ($errors->all() as $error)
                    <p>&bull; {{ $error }}</p>
                @endforeach
            </div>
        @endif

        <!-- Alert Jika Nota Dibatalkan -->
        @if ($penjualan->status === 'dibatalkan')
            <div class="p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-xs flex items-start gap-3 shadow-sm">
                <div class="w-8 h-8 rounded-lg bg-red-100 text-red-600 flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="15" y1="9" x2="9" y2="15"/>
                        <line x1="9" y1="9" x2="15" y2="15"/>
                    </svg>
                </div>
                <div>
                    <h4 class="font-bold text-red-900 text-sm">Nota Ini Telah Dibatalkan</h4>
                    <p class="text-xs mt-0.5 text-red-700">
                        Dibatalkan oleh <strong class="text-red-900">{{ $penjualan->dibatalkanOleh?->name ?? 'Petugas' }}</strong> 
                        pada {{ $penjualan->dibatalkan_pada?->translatedFormat('d F Y, H:i') }} WIT.
                    </p>
                    <p class="text-xs italic mt-1 text-red-600">
                        Alasan Pembatalan: "{{ $penjualan->alasan_batal }}"
                    </p>
                    <span class="inline-block mt-2 text-[11px] bg-red-100 text-red-700 px-2 py-0.5 rounded font-medium">
                        Stok seluruh barang pada nota ini telah dikembalikan ke sistem secara otomatis.
                    </span>
                </div>
            </div>
        @endif

        <!-- 4 Kartu Ringkasan Finansial Nota -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Total Belanja (Omzet) -->
            <div class="bg-white border border-gray-200 rounded-xl p-5 flex flex-col justify-between shadow-sm">
                <span class="text-xs font-medium text-gray-500">Total Transaksi (Omzet)</span>
                <h3 class="text-2xl font-bold {{ $penjualan->status === 'dibatalkan' ? 'line-through text-gray-400' : 'text-gray-900' }} tracking-tight mt-2 tabular-nums">
                    Rp {{ number_format($penjualan->total, 0, ',', '.') }}
                </h3>
                <span class="text-[11px] text-gray-500 mt-2 pt-2 border-t border-gray-100 tabular-nums">
                    Bayar: Rp {{ number_format($penjualan->bayar, 0, ',', '.') }} &bull; Kembali: Rp {{ number_format($penjualan->kembalian, 0, ',', '.') }}
                </span>
            </div>

            <!-- Total Modal HPP (Snapshot) -->
            <div class="bg-white border border-gray-200 rounded-xl p-5 flex flex-col justify-between shadow-sm">
                <span class="text-xs font-medium text-gray-500">Total Modal (HPP)</span>
                <h3 class="text-2xl font-bold {{ $penjualan->status === 'dibatalkan' ? 'line-through text-gray-400' : 'text-gray-900' }} tracking-tight mt-2 tabular-nums">
                    Rp {{ number_format($penjualan->total_modal, 0, ',', '.') }}
                </h3>
                <span class="text-[11px] text-gray-500 mt-2 pt-2 border-t border-gray-100">
                    Harga beli modal snapshot saat transaksi
                </span>
            </div>

            <!-- Laba Kotor (Gross Profit) -->
            <div class="bg-white border border-gray-200 rounded-xl p-5 flex flex-col justify-between shadow-sm">
                <span class="text-xs font-medium text-gray-500">Laba Kotor</span>
                <h3 class="text-2xl font-bold {{ $penjualan->status === 'dibatalkan' ? 'line-through text-gray-400' : 'text-[#166534]' }} tracking-tight mt-2 tabular-nums">
                    Rp {{ number_format($penjualan->laba_kotor, 0, ',', '.') }}
                </h3>
                <span class="text-[11px] text-gray-500 mt-2 pt-2 border-t border-gray-100">
                    Margin: {{ $penjualan->total > 0 ? round(($penjualan->laba_kotor / $penjualan->total) * 100, 1) : 0 }}%
                </span>
            </div>

            <!-- Status & Kasir -->
            <div class="bg-white border border-gray-200 rounded-xl p-5 flex flex-col justify-between shadow-sm">
                <span class="text-xs font-medium text-gray-500">Status Nota</span>
                <div class="mt-2 flex items-center gap-2">
                    @if ($penjualan->status === 'selesai')
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                            <span>Selesai</span>
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-red-50 text-red-700 border border-red-200">
                            <span>Dibatalkan</span>
                        </span>
                    @endif
                </div>
                <span class="text-[11px] text-gray-500 mt-2 pt-2 border-t border-gray-100">
                    Kasir: <strong class="text-gray-900">{{ $penjualan->user?->name ?? '-' }}</strong>
                </span>
            </div>
        </div>

        <!-- Tabel Rincian Barang yang Dijual -->
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <div class="p-4 sm:p-5 border-b border-gray-200 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-semibold text-gray-900">Rincian Barang Terjual</h2>
                    <p class="text-xs text-gray-500">{{ $penjualan->detail->count() }} barang &bull; {{ $penjualan->detail->sum('jumlah') }} unit</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50 text-gray-600 font-semibold">
                            <th class="py-3 px-4">Barang</th>
                            <th class="py-3 px-4 text-center">Jumlah</th>
                            <th class="py-3 px-4 text-right">Harga Jual</th>
                            <th class="py-3 px-4 text-right">Subtotal Jual</th>
                            <th class="py-3 px-4 text-right">Modal Satuan (HPP)</th>
                            <th class="py-3 px-4 text-right">Subtotal Modal</th>
                            <th class="py-3 px-4 text-right">Laba Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($penjualan->detail as $d)
                            <tr class="hover:bg-gray-50/50 transition">
                                <td class="py-3.5 px-4">
                                    <div class="font-medium text-gray-900">{{ $d->nama_barang }}</div>
                                    <div class="text-[11px] text-gray-400 font-mono">{{ $d->kode_barang }}</div>
                                </td>
                                <td class="py-3.5 px-4 text-center text-gray-700">
                                    {{ $d->jumlah }} {{ $d->satuan }}
                                </td>
                                <td class="py-3.5 px-4 text-right text-gray-700 tabular-nums">
                                    Rp {{ number_format($d->harga_jual, 0, ',', '.') }}
                                </td>
                                <td class="py-3.5 px-4 text-right font-semibold text-gray-900 tabular-nums">
                                    Rp {{ number_format($d->subtotal, 0, ',', '.') }}
                                </td>
                                <td class="py-3.5 px-4 text-right text-gray-500 tabular-nums">
                                    Rp {{ number_format($d->harga_modal, 0, ',', '.') }}
                                </td>
                                <td class="py-3.5 px-4 text-right text-gray-700 tabular-nums">
                                    Rp {{ number_format($d->subtotal_modal, 0, ',', '.') }}
                                </td>
                                <td class="py-3.5 px-4 text-right font-semibold text-[#166534] tabular-nums">
                                    Rp {{ number_format($d->subtotal - $d->subtotal_modal, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="border-t border-gray-200 bg-gray-50 font-semibold text-gray-900">
                        <tr>
                            <td class="py-3.5 px-4 text-xs">Total Keseluruhan</td>
                            <td class="py-3.5 px-4 text-center tabular-nums">{{ $penjualan->detail->sum('jumlah') }}</td>
                            <td class="py-3.5 px-4 text-right">—</td>
                            <td class="py-3.5 px-4 text-right text-sm font-bold tabular-nums">Rp {{ number_format($penjualan->total, 0, ',', '.') }}</td>
                            <td class="py-3.5 px-4 text-right">—</td>
                            <td class="py-3.5 px-4 text-right text-sm font-bold text-gray-700 tabular-nums">Rp {{ number_format($penjualan->total_modal, 0, ',', '.') }}</td>
                            <td class="py-3.5 px-4 text-right text-sm font-bold text-[#166534] tabular-nums">Rp {{ number_format($penjualan->laba_kotor, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Catatan Kebijakan Integritas Transaksi -->
        <div class="p-4 rounded-xl bg-emerald-50/50 border border-emerald-100 flex items-start gap-3 text-xs text-gray-600">
            <svg class="w-5 h-5 text-[#166534] shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="16" x2="12" y2="12"/>
                <line x1="12" y1="8" x2="12.01" y2="8"/>
            </svg>
            <div>
                <p class="font-semibold text-gray-900">Aturan Pembatalan Nota</p>
                <p class="mt-0.5 teks-penjelasan">
                    Nota hanya dapat dibatalkan selama shift kasir terkait masih berstatus <strong>Berjalan</strong>. Setelah shift ditutup dan direkonsiliasi, seluruh data transaksi terkunci secara permanen demi integritas laporan keuangan.
                </p>
            </div>
        </div>

        <!-- MODAL BATALKAN NOTA OLEH ADMIN -->
        <div x-show="modalVoidOpen" 
             x-cloak 
             class="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-sm flex items-center justify-center p-4"
             x-transition>

            <div @click.away="if (!voidSubmitting) modalVoidOpen = false" 
                 class="bg-white border border-gray-200 rounded-2xl max-w-md w-full p-6 shadow-xl space-y-4">

                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-red-50 border border-red-100 text-red-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="12" y1="8" x2="12" y2="12"/>
                            <line x1="12" y1="16" x2="12.01" y2="16"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-gray-900">Batalkan Nota Penjualan</h3>
                        <p class="text-xs text-gray-500">Stok barang akan otomatis dikembalikan ke sistem.</p>
                    </div>
                </div>

                <div class="p-3.5 rounded-lg bg-gray-50 border border-gray-200 text-xs space-y-1.5">
                    <div class="flex justify-between">
                        <span class="text-gray-500">No. Nota:</span>
                        <strong class="text-gray-900 font-mono">{{ $penjualan->nomor }}</strong>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Total Transaksi:</span>
                        <strong class="text-gray-900 font-semibold tabular-nums">Rp {{ number_format($penjualan->total, 0, ',', '.') }}</strong>
                    </div>
                </div>

                <form action="{{ route('admin.penjualan.batal', $penjualan->id) }}" 
                      method="POST" 
                      @submit="voidSubmitting = true" 
                      class="space-y-4">
                    @csrf

                    <div>
                        <label for="alasan_void" class="block text-xs font-semibold text-gray-700 mb-1.5">
                            Alasan Pembatalan <span class="text-red-500">*</span>
                        </label>
                        <textarea id="alasan_void"
                                  name="alasan"
                                  x-model="voidAlasan"
                                  rows="3"
                                  required
                                  placeholder="Alasan pembatalan nota (minimal 5 karakter)..."
                                  class="w-full px-3.5 py-2.5 bg-white border border-gray-300 rounded-lg text-xs text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 transition leading-relaxed"></textarea>
                    </div>

                    <div class="flex items-center gap-3 pt-2">
                        <button type="button" 
                                @click="modalVoidOpen = false" 
                                :disabled="voidSubmitting"
                                class="w-1/2 py-2.5 px-4 rounded-lg bg-white hover:bg-gray-50 border border-gray-300 text-xs font-semibold text-gray-700 text-center transition">
                            Batal
                        </button>
                        <button type="submit" 
                                :disabled="voidSubmitting || voidAlasan.trim().length < 5"
                                class="w-1/2 py-2.5 px-4 rounded-lg bg-red-600 hover:bg-red-700 text-white font-semibold text-xs shadow-sm transition disabled:opacity-50">
                            <span x-text="voidSubmitting ? 'Membatalkan...' : 'Ya, Batalkan Nota'"></span>
                        </button>
                    </div>
                </form>

            </div>
        </div>

    </div>
</x-layouts.admin>