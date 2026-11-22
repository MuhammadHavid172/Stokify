<x-layouts.admin title="Shift Kasir">
    <div class="space-y-6">

        <!-- Sub-Navigasi Tab: Penjualan & Shift -->
        <div class="flex items-center gap-2 border-b border-gray-200 pb-3">
            <a href="{{ route('admin.penjualan.index') }}" 
               class="px-4 py-2 rounded-lg text-xs font-bold transition {{ request()->routeIs('admin.penjualan.*') ? 'bg-[#166534] text-white shadow-xs' : 'bg-white text-gray-600 hover:text-gray-900 border border-gray-200 hover:bg-gray-50' }}">
                Riwayat Penjualan
            </a>
            <a href="{{ route('admin.shift.index') }}" 
               class="px-4 py-2 rounded-lg text-xs font-bold transition {{ request()->routeIs('admin.shift.*') ? 'bg-[#166534] text-white shadow-xs' : 'bg-white text-gray-600 hover:text-gray-900 border border-gray-200 hover:bg-gray-50' }}">
                Rekap Shift Kasir
            </a>
        </div>

        <!-- Header Halaman -->
        <x-page-header 
            title="Shift Kasir" 
            description="Monitoring dan rekonsiliasi kas shift laci kasir kios {{ auth()->user()->kios->nama_kios }}.">
            <a href="{{ route('kasir.index') }}" 
               class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg bg-[#166534] hover:bg-[#14532D] text-white text-xs font-semibold shadow-sm transition">
                <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <rect x="2" y="3" width="20" height="14" rx="2"/>
                    <line x1="8" y1="21" x2="16" y2="21"/>
                    <line x1="12" y1="17" x2="12" y2="21"/>
                </svg>
                <span>Buka Layar Kasir</span>
            </a>
        </x-page-header>

        <!-- Peringatan Kuning Jika Ada Shift > 16 Jam -->
        @if ($shiftLebih16JamCount > 0)
            <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-xs flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 shadow-sm">
                <div class="flex items-start sm:items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center shrink-0 mt-0.5 sm:mt-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div>
                        <strong class="font-semibold text-amber-900">Peringatan Shift Berjalan Lebih dari 16 Jam:</strong>
                        <p class="text-xs text-amber-700 mt-0.5">
                            Terdapat {{ $shiftLebih16JamCount }} shift kasir yang belum ditutup lebih dari 16 jam. Periksa kemungkinan kasir lupa menutup shift atau lakukan tutup paksa jika diperlukan.
                        </p>
                    </div>
                </div>
                <a href="{{ route('admin.shift.index', ['status' => 'berjalan']) }}" 
                   class="px-3 py-1.5 rounded-lg bg-amber-200 hover:bg-amber-300 text-amber-900 text-xs font-semibold shrink-0 transition self-start sm:self-auto">
                    Lihat Shift Aktif
                </a>
            </div>
        @endif

        <!-- Filter Bar -->
        <div class="bg-white border border-gray-200 rounded-xl p-4 sm:p-5 shadow-sm">
            <form method="GET" action="{{ route('admin.shift.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3.5 items-end">
                
                <!-- Filter Tanggal Mulai -->
                <div>
                    <label for="tanggal_mulai" class="block text-xs font-medium text-gray-700 mb-1.5">
                        Mulai Tanggal
                    </label>
                    <input type="date" 
                           id="tanggal_mulai" 
                           name="tanggal_mulai" 
                           value="{{ $tanggalMulai }}"
                           class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#166534] focus:border-[#166534] transition" />
                </div>

                <!-- Filter Tanggal Selesai -->
                <div>
                    <label for="tanggal_selesai" class="block text-xs font-medium text-gray-700 mb-1.5">
                        Sampai Tanggal
                    </label>
                    <input type="date" 
                           id="tanggal_selesai" 
                           name="tanggal_selesai" 
                           value="{{ $tanggalSelesai }}"
                           class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#166534] focus:border-[#166534] transition" />
                </div>

                <!-- Filter Kasir -->
                <div>
                    <label for="kasir_id" class="block text-xs font-medium text-gray-700 mb-1.5">
                        Petugas Kasir
                    </label>
                    <select id="kasir_id" 
                            name="kasir_id" 
                            class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#166534] focus:border-[#166534] transition">
                        <option value="">Semua Petugas</option>
                        @foreach ($kasirOptions as $kasir)
                            <option value="{{ $kasir->id }}" {{ (string)$kasirId === (string)$kasir->id ? 'selected' : '' }}>
                                {{ $kasir->name }} ({{ ucfirst($kasir->role) }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Filter Status -->
                <div>
                    <label for="status" class="block text-xs font-medium text-gray-700 mb-1.5">
                        Status Shift
                    </label>
                    <select id="status" 
                            name="status" 
                            class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#166534] focus:border-[#166534] transition">
                        <option value="">Semua Status</option>
                        <option value="berjalan" {{ $status === 'berjalan' ? 'selected' : '' }}>Berjalan</option>
                        <option value="ditutup" {{ $status === 'ditutup' ? 'selected' : '' }}>Ditutup</option>
                    </select>
                </div>

                <!-- Checkbox & Tombol Aksi -->
                <div class="flex items-center gap-2">
                    <div class="flex items-center gap-2 mr-2">
                        <input type="checkbox" 
                               id="hanya_selisih" 
                               name="hanya_selisih" 
                               value="1" 
                               {{ $hanyaSelisih ? 'checked' : '' }}
                               class="rounded border-gray-300 text-[#166534] focus:ring-[#166534] w-4 h-4">
                        <label for="hanya_selisih" class="text-xs text-gray-700 font-medium select-none cursor-pointer">
                            Ada Selisih
                        </label>
                    </div>

                    <button type="submit" 
                            class="flex-1 py-2 px-3 rounded-lg bg-[#166534] hover:bg-[#14532D] text-xs font-semibold text-white transition text-center shadow-sm">
                        Filter
                    </button>
                    @if (request()->hasAny(['tanggal_mulai', 'tanggal_selesai', 'kasir_id', 'status', 'hanya_selisih']))
                        <a href="{{ route('admin.shift.index') }}" 
                           class="py-2 px-3 rounded-lg bg-white hover:bg-gray-50 border border-gray-300 text-xs font-semibold text-gray-700 transition text-center"
                           title="Atur Ulang Filter">
                            &times;
                        </a>
                    @endif
                </div>

            </form>
        </div>

        <!-- Tabel Daftar Shift -->
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <div class="p-4 sm:p-5 border-b border-gray-200 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-semibold text-gray-900">Riwayat Rekonsiliasi Shift</h2>
                    <p class="text-xs text-gray-500">Menampilkan daftar shift kasir dan pencatatan kas.</p>
                </div>
                <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-gray-100 text-gray-600">
                    {{ $shiftList->total() }} Shift
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50 text-gray-600 font-semibold">
                            <th class="py-3 px-4">Kasir</th>
                            <th class="py-3 px-4">Waktu Buka / Tutup</th>
                            <th class="py-3 px-4">Durasi</th>
                            <th class="py-3 px-4 text-right">Modal Awal</th>
                            <th class="py-3 px-4 text-right">Penjualan Tunai</th>
                            <th class="py-3 px-4 text-right">Uang Seharusnya</th>
                            <th class="py-3 px-4 text-right">Uang Fisik</th>
                            <th class="py-3 px-4 text-center">Selisih</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($shiftList as $s)
                            <tr class="hover:bg-gray-50/50 transition">
                                <td class="py-3.5 px-4">
                                    <strong class="text-gray-900 font-semibold">{{ $s->user?->name ?? 'Kasir' }}</strong>
                                    <span class="text-[11px] text-gray-400 block font-mono">Shift #{{ $s->id }}</span>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="text-gray-900">
                                        {{ \Carbon\Carbon::parse($s->waktu_buka)->timezone('Asia/Jayapura')->format('d M Y, H:i') }}
                                    </div>
                                    <div class="text-[11px] text-gray-500">
                                        @if ($s->waktu_tutup)
                                            s/d {{ \Carbon\Carbon::parse($s->waktu_tutup)->timezone('Asia/Jayapura')->format('H:i') }} WIT
                                        @else
                                            <span class="text-emerald-700 font-medium">Masih aktif</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 text-gray-500">
                                    {{ $s->durasi }}
                                </td>
                                <td class="py-3.5 px-4 text-right font-mono text-gray-900 tabular-nums">
                                    Rp {{ number_format($s->modal_awal, 0, ',', '.') }}
                                </td>
                                <td class="py-3.5 px-4 text-right font-mono text-[#166534] font-medium tabular-nums">
                                    @if ($s->status === 'ditutup')
                                        Rp {{ number_format($s->total_penjualan_tunai, 0, ',', '.') }}
                                    @else
                                        Rp {{ number_format($s->penjualan()->where('status', 'selesai')->sum('total'), 0, ',', '.') }}
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right font-mono text-gray-900 tabular-nums">
                                    @if ($s->status === 'ditutup')
                                        Rp {{ number_format($s->uang_seharusnya, 0, ',', '.') }}
                                    @else
                                        <span class="text-amber-700">Rp {{ number_format($s->uang_seharusnya_live, 0, ',', '.') }}</span>
                                        <span class="text-[10px] text-amber-600 block">(sementara)</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right font-mono text-gray-900 tabular-nums">
                                    @if ($s->status === 'ditutup')
                                        Rp {{ number_format($s->uang_fisik, 0, ',', '.') }}
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-center tabular-nums">
                                    @if ($s->status === 'ditutup')
                                        @if ($s->selisih == 0)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                Sesuai (Rp 0)
                                            </span>
                                        @elseif ($s->selisih < 0)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-red-50 text-red-700 border border-red-200">
                                                - Rp {{ number_format(abs($s->selisih), 0, ',', '.') }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-amber-50 text-amber-800 border border-amber-200">
                                                + Rp {{ number_format($s->selisih, 0, ',', '.') }}
                                            </span>
                                        @endif
                                    @else
                                        <span class="text-gray-400 text-[11px]">-</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    @if ($s->status === 'berjalan')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-600 animate-pulse"></span>
                                            Berjalan
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-700 border border-gray-200">
                                            Ditutup
                                        </span>
                                        @if ($s->ditutup_paksa)
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-amber-100 text-amber-800 ml-1">
                                                Paksa
                                            </span>
                                        @endif
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <a href="{{ route('admin.shift.show', $s->id) }}" 
                                       class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-white hover:bg-gray-50 border border-gray-300 text-xs font-semibold text-gray-700 shadow-sm transition">
                                        <svg class="w-3.5 h-3.5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        <span>Detail</span>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="py-12 text-center text-gray-500">
                                    <svg class="w-10 h-10 mx-auto text-gray-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <rect x="2" y="3" width="20" height="14" rx="2"/>
                                        <line x1="8" y1="21" x2="16" y2="21"/>
                                        <line x1="12" y1="17" x2="12" y2="21"/>
                                    </svg>
                                    <p class="font-medium text-xs">Tidak ditemukan riwayat shift kasir pada rentang filter ini.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginasi -->
            @if ($shiftList->hasPages())
                <div class="p-4 border-t border-gray-200">
                    {{ $shiftList->links() }}
                </div>
            @endif
        </div>

    </div>
</x-layouts.admin>