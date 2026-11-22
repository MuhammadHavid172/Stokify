<x-layouts.admin title="Detail Nota Barang Masuk">
    <div class="space-y-6" x-data="{ batalModalOpen: false }">
        
        <!-- Header Halaman -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-slate-100">
            <div>
                <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 mb-1">
                    <a href="{{ route('admin.dashboard') }}" class="hover:text-slate-800 transition">Dashboard</a>
                    <span>/</span>
                    <a href="{{ route('admin.barang-masuk.index') }}" class="hover:text-slate-800 transition">Barang Masuk</a>
                    <span>/</span>
                    <span class="text-slate-800">{{ $barangMasuk->nomor }}</span>
                </div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight font-mono">
                        {{ $barangMasuk->nomor }}
                    </h1>
                    @if ($barangMasuk->status === 'selesai')
                        <x-badge variant="green" size="md">Selesai</x-badge>
                    @else
                        <x-badge variant="red" size="md">Dibatalkan</x-badge>
                    @endif
                </div>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">
                    Tanggal: <strong class="text-slate-800">{{ $barangMasuk->tanggal->translatedFormat('d F Y') }}</strong> • Dicatat oleh: <strong class="text-slate-800">{{ $barangMasuk->user->name ?? 'Admin' }}</strong>
                </p>
            </div>

            <div class="flex items-center gap-2.5 shrink-0">
                <a href="{{ route('admin.barang-masuk.index') }}" 
                   class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs shadow-xs transition">
                    <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    <span>Daftar Barang Masuk</span>
                </a>

                @if ($barangMasuk->status === 'selesai')
                    <button type="button" 
                            @click="batalModalOpen = true" 
                            class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl border border-rose-200 bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold text-xs shadow-xs transition">
                        <svg class="w-4 h-4 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                        </svg>
                        <span>Batalkan Nota</span>
                    </button>
                @endif
            </div>
        </div>

        <!-- Banner Keterangan Jika Dibatalkan -->
        @if ($barangMasuk->status === 'dibatalkan')
            <div class="p-4 sm:p-5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-900 text-xs sm:text-sm">
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-xl bg-rose-600 text-white flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <line x1="18" y1="6" x2="6" y2="18" />
                            <line x1="6" y1="6" x2="18" y2="18" />
                        </svg>
                    </div>
                    <div>
                        <p class="font-bold text-rose-800">Nota Barang Masuk Ini Telah Dibatalkan</p>
                        <p class="mt-0.5 text-rose-700 text-xs">
                            Dibatalkan oleh <strong>{{ $barangMasuk->pembatal->name ?? 'Admin' }}</strong> pada <strong>{{ $barangMasuk->dibatalkan_pada ? $barangMasuk->dibatalkan_pada->translatedFormat('d M Y, H:i') : '—' }}</strong>.
                        </p>
                        <p class="mt-1.5 text-xs text-rose-800 bg-white/70 p-2 rounded-lg border border-rose-200/80">
                            <strong>Alasan Pembatalan:</strong> {{ $barangMasuk->alasan_batal }}
                        </p>
                    </div>
                </div>
            </div>
        @else
            <!-- Notice UI: Nota Tidak Dapat Diedit -->
            <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200/80 text-[11px] text-slate-500 flex items-center gap-2">
                <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10" />
                    <line x1="12" y1="16" x2="12" y2="12" />
                    <line x1="12" y1="8" x2="12.01" y2="8" />
                </svg>
                <span>
                    <strong>Catatan:</strong> Nota barang masuk tidak dapat diubah setelah disimpan demi menjaga integritas data mutasi stok. Jika terdapat kesalahan input, silakan batalkan nota ini lalu catat kembali nota yang baru.
                </span>
            </div>
        @endif

        <!-- Card Rincian Header -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <!-- Supplier -->
            <div class="p-4 sm:p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
                <span class="text-xs font-semibold text-slate-500 block">Pemasok / Supplier</span>
                <span class="text-base sm:text-lg font-bold text-slate-900 block mt-1">
                    {{ $barangMasuk->supplier->nama ?? 'Beli Langsung / Pasar Bebas' }}
                </span>
                @if ($barangMasuk->supplier && $barangMasuk->supplier->no_telepon)
                    <span class="text-[11px] text-slate-400 block mt-0.5 font-mono">
                        Telp/WA: {{ $barangMasuk->supplier->no_telepon }}
                    </span>
                @endif
            </div>

            <!-- Total Belanja -->
            <div class="p-4 sm:p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
                <span class="text-xs font-semibold text-slate-500 block">Total Nilai Barang Masuk</span>
                <span class="text-2xl sm:text-3xl font-black text-slate-900 block mt-1">
                    {{ rupiah($barangMasuk->total) }}
                </span>
                <span class="text-[11px] text-slate-400 block mt-0.5">
                    {{ $barangMasuk->detail->count() }} jenis barang masuk
                </span>
            </div>

            <!-- Catatan Nota -->
            <div class="p-4 sm:p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
                <span class="text-xs font-semibold text-slate-500 block">Catatan Nota</span>
                <p class="text-xs sm:text-sm text-slate-700 mt-1 italic">
                    {{ $barangMasuk->catatan ?: 'Tidak ada catatan tambahan.' }}
                </p>
            </div>
        </div>

        <!-- Tabel Detail Barang Masuk -->
        <div class="bg-white border border-slate-200/80 rounded-3xl shadow-xs overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Rincian Barang yang Masuk</h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Daftar barang, jumlah restok, harga beli per unit, dan snapshot modal sebelum barang masuk.
                    </p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200/80">
                        <tr>
                            <th class="px-5 py-3.5 w-12 text-center">No</th>
                            <th class="px-5 py-3.5">Barang Dagangan</th>
                            <th class="px-5 py-3.5 text-center">Stok Sebelum</th>
                            <th class="px-5 py-3.5 text-center">Jumlah Masuk</th>
                            <th class="px-5 py-3.5 text-right">Harga Beli Satuan</th>
                            <th class="px-5 py-3.5 text-right">Modal Sebelum</th>
                            <th class="px-5 py-3.5 text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($barangMasuk->detail as $index => $item)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="px-5 py-3.5 text-center text-slate-400 font-mono">
                                    {{ $index + 1 }}
                                </td>

                                <td class="px-5 py-3.5 font-bold text-slate-900">
                                    <a href="{{ route('admin.barang.show', $item->barang) }}" class="hover:text-[#166534] transition block">
                                        {{ $item->barang->nama }}
                                    </a>
                                    <span class="text-[10px] text-slate-400 font-mono font-normal">
                                        {{ $item->barang->kode }} • Satuan: {{ $item->barang->satuan }}
                                    </span>
                                </td>

                                <td class="px-5 py-3.5 text-center font-medium text-slate-500">
                                    {{ $item->stok_sebelum }} {{ $item->barang->satuan }}
                                </td>

                                <td class="px-5 py-3.5 text-center font-black text-emerald-600 text-sm">
                                    +{{ $item->jumlah }} {{ $item->barang->satuan }}
                                </td>

                                <td class="px-5 py-3.5 text-right font-medium text-slate-800">
                                    {{ rupiah($item->harga_beli) }}
                                </td>

                                <td class="px-5 py-3.5 text-right text-slate-400">
                                    {{ rupiah($item->harga_modal_sebelum) }}
                                </td>

                                <td class="px-5 py-3.5 text-right font-black text-slate-900 text-sm">
                                    {{ rupiah($item->subtotal) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-slate-50 font-bold border-t border-slate-200">
                        <tr>
                            <td colspan="6" class="px-5 py-4 text-right text-xs uppercase tracking-wider text-slate-700">
                                Total Keseluruhan:
                            </td>
                            <td class="px-5 py-4 text-right text-base sm:text-lg font-black text-slate-900">
                                {{ rupiah($barangMasuk->total) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Modal Konfirmasi Pembatalan Nota -->
        @if ($barangMasuk->status === 'selesai')
            <div x-show="batalModalOpen" 
                 x-cloak 
                 class="fixed inset-0 z-50 overflow-y-auto" 
                 role="dialog" 
                 aria-modal="true">
                <div class="fixed inset-0 bg-black/60 backdrop-blur-xs transition-opacity" @click="batalModalOpen = false"></div>
                <div class="flex min-h-full items-center justify-center p-4">
                    <div x-show="batalModalOpen" 
                         x-transition 
                         class="relative w-full max-w-md rounded-3xl bg-white p-6 sm:p-7 shadow-2xl border border-slate-200">
                        <div class="flex items-start gap-4">
                            <div class="w-11 h-11 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center shrink-0 border border-rose-200/60">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-slate-900">Batalkan Nota Barang Masuk?</h3>
                                <p class="mt-1 text-xs text-slate-600 leading-relaxed">
                                    Membatalkan nota <strong>{{ $barangMasuk->nomor }}</strong> akan menarik kembali seluruh stok barang yang telah masuk melalui mutasi penyesuaian.
                                </p>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('admin.barang-masuk.batal', $barangMasuk) }}" class="mt-4 space-y-4">
                            @csrf
                            <div>
                                <label for="alasan" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                    Alasan Pembatalan <span class="text-rose-500">* (Minimal 5 karakter)</span>
                                </label>
                                <textarea id="alasan" 
                                          name="alasan" 
                                          rows="3" 
                                          required 
                                          placeholder="Tuliskan alasan pembatalan penerimaan barang ini..." 
                                          class="w-full rounded-xl border border-slate-200 px-3.5 py-2 text-xs sm:text-sm text-slate-800 focus:border-rose-500 focus:ring-1 focus:ring-rose-500 transition"></textarea>
                                <p class="text-[11px] text-slate-400 mt-1">
                                    Pembatalan akan ditolak secara utuh jika stok barang di kios sudah tidak mencukupi (misal telah terjual).
                                </p>
                            </div>

                            <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100">
                                <button type="button" 
                                        @click="batalModalOpen = false" 
                                        class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition">
                                    Batal
                                </button>
                                <button type="submit" 
                                        class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-xs font-bold text-white shadow-sm transition">
                                    Ya, Batalkan Nota
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif

    </div>
</x-layouts.admin>
