<x-layouts.admin title="Detail Shift Kasir #{{ $shift->id }}">
    <div class="space-y-6"
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
             },
             modalTutupPaksaOpen: false,
             tutupUangFisik: '',
             tutupCatatan: '',
             tutupSubmitting: false,
             openTutupPaksaModal() {
                 this.tutupUangFisik = '';
                 this.tutupCatatan = 'Kasir lupa menutup shift';
                 this.modalTutupPaksaOpen = true;
             }
         }">

        <!-- Navigasi Balik & Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-gray-200">
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.shift.index') }}" 
                   class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-white hover:bg-gray-50 border border-gray-300 text-xs font-semibold text-gray-700 shadow-sm transition">
                    <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    <span>Kembali ke Daftar Shift</span>
                </a>
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight text-balance">Detail Shift #{{ $shift->id }}</h1>
                    <p class="text-xs text-gray-500 text-pretty">Kasir: <strong class="text-gray-900">{{ $shift->user->name }}</strong> &bull; {{ $shift->durasi }}</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                @if ($shift->status === 'berjalan' && (int)$shift->user_id !== (int)auth()->id())
                    <button type="button" 
                            @click="openTutupPaksaModal()"
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-amber-50 hover:bg-amber-100 border border-amber-200 text-xs font-semibold text-amber-800 transition shadow-sm">
                        <svg class="w-4 h-4 text-amber-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                        </svg>
                        <span>Tutup Paksa Shift</span>
                    </button>
                @endif
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

        <!-- Card Ringkasan Shift -->
        <div class="bg-white border border-gray-200 rounded-xl p-5 sm:p-6 shadow-sm space-y-5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-gray-200 gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl {{ $shift->status === 'berjalan' ? 'bg-emerald-50 border-emerald-200 text-emerald-700' : 'bg-gray-100 border-gray-200 text-gray-700' }} border flex items-center justify-center font-bold text-lg font-mono">
                        #{{ $shift->id }}
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-base font-semibold text-gray-900">Shift Kasir: {{ $shift->user->name }}</h2>
                            @if ($shift->status === 'berjalan')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-600 animate-pulse"></span>
                                    Sedang Berjalan
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-700 border border-gray-200">
                                    Sudah Ditutup
                                </span>
                            @endif
                        </div>
                        <p class="text-xs text-gray-500 mt-0.5">
                            Dibuka: {{ \Carbon\Carbon::parse($shift->waktu_buka)->timezone('Asia/Jayapura')->format('d M Y, H:i') }} WIT
                            @if ($shift->waktu_tutup)
                                &bull; Ditutup: {{ \Carbon\Carbon::parse($shift->waktu_tutup)->timezone('Asia/Jayapura')->format('d M Y, H:i') }} WIT
                            @endif
                        </p>
                    </div>
                </div>

                @if ($shift->ditutup_paksa)
                    <div class="p-2.5 rounded-lg bg-amber-50 border border-amber-200 text-xs text-amber-900">
                        <strong class="font-semibold text-amber-900">Ditutup Paksa:</strong>
                        <span class="block text-[11px] text-amber-700">Oleh: {{ $shift->ditutupOlehUser?->name ?? 'Admin' }}</span>
                    </div>
                @endif
            </div>

            <!-- Grid 4 Kartu Metrik Keuangan Shift -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                <div class="p-4 rounded-lg bg-gray-50 border border-gray-200">
                    <span class="text-xs font-medium text-gray-500 block">Modal Awal</span>
                    <strong class="text-base sm:text-lg font-mono font-bold text-gray-900 mt-1 block tabular-nums">
                        Rp {{ number_format($shift->modal_awal, 0, ',', '.') }}
                    </strong>
                </div>

                <div class="p-4 rounded-lg bg-gray-50 border border-gray-200">
                    <span class="text-xs font-medium text-gray-500 block">Penjualan Tunai (Selesai)</span>
                    <strong class="text-base sm:text-lg font-mono font-bold text-[#166534] mt-1 block tabular-nums">
                        @if ($shift->status === 'ditutup')
                            Rp {{ number_format($shift->total_penjualan_tunai, 0, ',', '.') }}
                        @else
                            Rp {{ number_format($shift->penjualan->where('status', 'selesai')->sum('total'), 0, ',', '.') }}
                        @endif
                    </strong>
                </div>

                <div class="p-4 rounded-lg bg-gray-50 border border-gray-200">
                    <span class="text-xs font-medium text-gray-500 block">
                        Uang Seharusnya @if ($shift->status === 'berjalan') <span class="text-amber-600">(sementara)</span> @endif
                    </span>
                    <strong class="text-base sm:text-lg font-mono font-bold text-gray-900 mt-1 block tabular-nums">
                        @if ($shift->status === 'ditutup')
                            Rp {{ number_format($shift->uang_seharusnya, 0, ',', '.') }}
                        @else
                            Rp {{ number_format($shift->uang_seharusnya_live, 0, ',', '.') }}
                        @endif
                    </strong>
                </div>

                <div class="p-4 rounded-lg bg-gray-50 border border-gray-200">
                    <span class="text-xs font-medium text-gray-500 block">Uang Fisik & Selisih</span>
                    @if ($shift->status === 'ditutup')
                        <strong class="text-base sm:text-lg font-mono font-bold text-gray-900 mt-1 block tabular-nums">
                            Rp {{ number_format($shift->uang_fisik, 0, ',', '.') }}
                        </strong>
                        <div class="mt-1">
                            @if ($shift->selisih == 0)
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    Sesuai (Rp 0)
                                </span>
                            @elseif ($shift->selisih < 0)
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[11px] font-semibold bg-red-50 text-red-700 border border-red-200">
                                    Kurang Rp {{ number_format(abs($shift->selisih), 0, ',', '.') }}
                                </span>
                            @else
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[11px] font-semibold bg-amber-50 text-amber-800 border border-amber-200">
                                    Lebih Rp {{ number_format($shift->selisih, 0, ',', '.') }}
                                </span>
                            @endif
                        </div>
                    @else
                        <span class="text-gray-400 text-xs block mt-2">Menunggu penutupan shift</span>
                    @endif
                </div>
            </div>

            <!-- Catatan Shift -->
            @if ($shift->catatan)
                <div class="p-3.5 rounded-lg bg-gray-50 border border-gray-200 text-xs">
                    <span class="text-gray-500 text-[11px] block font-semibold mb-1 uppercase tracking-wider">Catatan Kasir / Penutup:</span>
                    <p class="text-gray-700 italic">"{{ $shift->catatan }}"</p>
                </div>
            @endif
        </div>

        <!-- Tabel Daftar Nota Pada Shift Ini -->
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <div class="p-4 sm:p-5 border-b border-gray-200 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-semibold text-gray-900">Transaksi Pada Shift Ini</h2>
                    <p class="text-xs text-gray-500">Nota hanya dapat dibatalkan selama shift masih berstatus berjalan.</p>
                </div>
                <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-gray-100 text-gray-600">
                    {{ $shift->penjualan->count() }} Nota
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50 text-gray-600 font-semibold">
                            <th class="py-3 px-4">No. Nota</th>
                            <th class="py-3 px-4">Waktu</th>
                            <th class="py-3 px-4">Kasir</th>
                            <th class="py-3 px-4 text-right">Total</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($shift->penjualan as $p)
                            @php $isBatal = $p->status === 'dibatalkan'; @endphp
                            <tr class="hover:bg-gray-50/50 transition {{ $isBatal ? 'opacity-75 bg-red-50/20' : '' }}">
                                <td class="py-3 px-4 font-mono font-semibold text-gray-900">
                                    <span class="{{ $isBatal ? 'line-through text-gray-400' : '' }}">{{ $p->nomor }}</span>
                                    @if ($isBatal)
                                        <div class="text-[11px] text-red-600 font-normal mt-0.5">
                                            Dibatalkan oleh {{ $p->dibatalkanOleh?->name ?? 'Petugas' }}: "{{ $p->alasan_batal }}"
                                        </div>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-gray-500">
                                    {{ $p->waktu_transaksi->format('H:i') }} WIT
                                </td>
                                <td class="py-3 px-4 text-gray-700">
                                    {{ $p->user?->name ?? '-' }}
                                </td>
                                <td class="py-3 px-4 text-right font-mono font-semibold {{ $isBatal ? 'line-through text-gray-400' : 'text-gray-900' }} tabular-nums">
                                    Rp {{ number_format($p->total, 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-4 text-center">
                                    @if ($isBatal)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-red-50 text-red-700 border border-red-200">
                                            Dibatalkan
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            Selesai
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <div class="inline-flex items-center gap-1.5">
                                        <a href="{{ route('admin.penjualan.show', $p->id) }}" 
                                           class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-white hover:bg-gray-50 border border-gray-300 text-xs font-semibold text-gray-700 shadow-sm transition">
                                            <span>Rincian</span>
                                        </a>

                                        @if (! $isBatal && $shift->status === 'berjalan')
                                            <button type="button" 
                                                    @click="openVoidModal({{ $p->id }}, '{{ $p->nomor }}', 'Rp {{ number_format($p->total, 0, ',', '.') }}')"
                                                    class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-red-50 hover:bg-red-100 border border-red-200 text-xs font-semibold text-red-700 transition">
                                                <span>Batalkan</span>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-gray-500">
                                    Belum ada nota transaksi yang dicatat pada shift ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- MODAL BATALKAN NOTA (ADMIN) -->
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
                        <strong class="text-gray-900 font-mono" x-text="voidNomor"></strong>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Total Transaksi:</span>
                        <strong class="text-gray-900 font-semibold tabular-nums" x-text="voidTotal"></strong>
                    </div>
                </div>

                <form :action="'{{ url('/admin/penjualan') }}/' + voidPenjualanId + '/batal'" 
                      method="POST" 
                      @submit="voidSubmitting = true" 
                      class="space-y-4">
                    @csrf

                    <div>
                        <label for="alasan_void_admin" class="block text-xs font-semibold text-gray-700 mb-1.5">
                            Alasan Pembatalan <span class="text-red-500">*</span>
                        </label>
                        <textarea id="alasan_void_admin"
                                  name="alasan"
                                  x-model="voidAlasan"
                                  rows="3"
                                  required
                                  placeholder="Alasan pembatalan nota (min. 5 karakter)..."
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

        <!-- MODAL TUTUP PAKSA SHIFT (ADMIN) -->
        <div x-show="modalTutupPaksaOpen" 
             x-cloak 
             class="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-sm flex items-center justify-center p-4"
             x-transition>

            <div @click.away="if (!tutupSubmitting) modalTutupPaksaOpen = false" 
                 class="bg-white border border-gray-200 rounded-2xl max-w-md w-full p-6 shadow-xl space-y-4">

                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-50 border border-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-gray-900">Tutup Paksa Shift Kasir</h3>
                        <p class="text-xs text-gray-500">Tutup shift kasir secara sepihak oleh admin.</p>
                    </div>
                </div>

                <form action="{{ route('admin.shift.tutup_paksa', $shift->id) }}" 
                      method="POST" 
                      @submit="tutupSubmitting = true" 
                      class="space-y-4">
                    @csrf

                    <div>
                        <label for="uang_fisik_paksa" class="block text-xs font-semibold text-gray-700 mb-1.5">
                            Uang Fisik Dihitung (Rp) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" 
                               id="uang_fisik_paksa" 
                               name="uang_fisik" 
                               x-model="tutupUangFisik"
                               required
                               min="0"
                               placeholder="0"
                               class="w-full px-3.5 py-2.5 bg-white border border-gray-300 rounded-lg text-sm font-semibold text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#166534] focus:border-[#166534] transition tabular-nums" />
                    </div>

                    <div>
                        <label for="catatan_paksa" class="block text-xs font-semibold text-gray-700 mb-1.5">
                            Alasan Tutup Paksa <span class="text-red-500">*</span>
                        </label>
                        <textarea id="catatan_paksa"
                                  name="catatan"
                                  x-model="tutupCatatan"
                                  rows="3"
                                  required
                                  placeholder="Tuliskan alasan penutupan paksa shift ini (min. 5 karakter)..."
                                  class="w-full px-3.5 py-2.5 bg-white border border-gray-300 rounded-lg text-xs text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#166534] focus:border-[#166534] transition leading-relaxed"></textarea>
                    </div>

                    <div class="flex items-center gap-3 pt-2">
                        <button type="button" 
                                @click="modalTutupPaksaOpen = false" 
                                :disabled="tutupSubmitting"
                                class="w-1/2 py-2.5 px-4 rounded-lg bg-white hover:bg-gray-50 border border-gray-300 text-xs font-semibold text-gray-700 text-center transition">
                            Batal
                        </button>
                        <button type="submit" 
                                :disabled="tutupSubmitting || tutupCatatan.trim().length < 5 || tutupUangFisik === ''"
                                class="w-1/2 py-2.5 px-4 rounded-lg bg-amber-600 hover:bg-amber-700 text-white font-semibold text-xs shadow-sm transition disabled:opacity-50">
                            <span x-text="tutupSubmitting ? 'Memproses...' : 'Tutup Paksa Shift'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-layouts.admin>