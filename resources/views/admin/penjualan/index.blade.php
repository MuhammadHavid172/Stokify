<x-layouts.admin title="Riwayat Penjualan">
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

        <!-- Header Halaman & Aksi Kasir -->
        <x-page-header 
            title="Riwayat Penjualan" 
            description="Daftar seluruh transaksi penjualan kasir kios {{ auth()->user()->kios->nama_kios }}.">
            <a href="{{ route('kasir.index') }}" 
               class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-[#166534] hover:bg-[#14532D] text-white text-xs font-semibold shadow-xs transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Buka Layar Kasir</span>
            </a>
        </x-page-header>

        <!-- 3 Kartu Ringkasan Metrik (Sesuai Filter & Status Selesai) -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-stretch">
            <!-- Total Omzet -->
            <div class="bg-white border border-gray-200 rounded-xl p-5 flex flex-col justify-between hover:border-gray-300 transition shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-gray-500">Total Omzet Penjualan</span>
                    <div class="w-8 h-8 rounded-lg bg-[#DCFCE7] text-[#166534] flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-3">
                    <h3 class="text-2xl font-bold text-gray-900 tracking-tight tabular-nums">
                        Rp {{ number_format($totalOmzet, 0, ',', '.') }}
                    </h3>
                </div>
                <div class="mt-3 pt-2.5 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500">
                    <span>Nilai bruto transaksi selesai</span>
                    <span class="text-[11px] text-[#166534] font-semibold">Omzet</span>
                </div>
            </div>

            <!-- Total Transaksi -->
            <div class="bg-white border border-gray-200 rounded-xl p-5 flex flex-col justify-between hover:border-gray-300 transition shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-gray-500">Jumlah Transaksi Selesai</span>
                    <div class="w-8 h-8 rounded-lg bg-[#DCFCE7] text-[#166534] flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-3">
                    <h3 class="text-2xl font-bold text-gray-900 tracking-tight tabular-nums">
                        {{ number_format($totalTransaksi) }} Transaksi
                    </h3>
                </div>
                <div class="mt-3 pt-2.5 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500">
                    <span>Sesuai rentang filter aktif</span>
                    <span class="text-[11px] text-[#166534] font-semibold">Terselesaikan</span>
                </div>
            </div>

            <!-- Total Laba Kotor -->
            <div class="bg-white border border-gray-200 rounded-xl p-5 flex flex-col justify-between hover:border-gray-300 transition shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-gray-500">Laba Kotor</span>
                    <div class="w-8 h-8 rounded-lg bg-[#DCFCE7] text-[#166534] flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-3">
                    <h3 class="text-2xl font-bold text-gray-900 tracking-tight tabular-nums">
                        Rp {{ number_format($totalLabaKotor, 0, ',', '.') }}
                    </h3>
                </div>
                <div class="mt-3 pt-2.5 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500">
                    <span>Omzet dikurangi HPP modal</span>
                    <span class="text-[11px] text-[#166534] font-semibold">Margin Kotor</span>
                </div>
            </div>
        </div>

        <!-- FORM FILTER (Tanggal, Kasir, Status, Nomor) -->
        <div class="bg-white border border-gray-200 rounded-xl p-4 sm:p-5 shadow-sm">
            <form method="GET" action="{{ route('admin.penjualan.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3 items-end">
                
                <!-- Cari Nomor Nota -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Nomor Nota</label>
                    <input type="text" 
                           name="nomor" 
                           value="{{ $searchNomor }}" 
                           placeholder="Cari nomor nota..." 
                           class="w-full bg-white border border-gray-200 rounded-lg px-3 py-2 text-xs text-gray-900 placeholder-gray-400 focus:outline-none focus:border-[#166534] focus:ring-1 focus:ring-[#166534]" />
                </div>

                <!-- Tanggal Mulai -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Tanggal Mulai</label>
                    <input type="date" 
                           name="tanggal_mulai" 
                           value="{{ $tanggalMulai }}" 
                           class="w-full bg-white border border-gray-200 rounded-lg px-3 py-2 text-xs text-gray-900 focus:outline-none focus:border-[#166534] focus:ring-1 focus:ring-[#166534]" />
                </div>

                <!-- Tanggal Selesai -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Tanggal Selesai</label>
                    <input type="date" 
                           name="tanggal_selesai" 
                           value="{{ $tanggalSelesai }}" 
                           class="w-full bg-white border border-gray-200 rounded-lg px-3 py-2 text-xs text-gray-900 focus:outline-none focus:border-[#166534] focus:ring-1 focus:ring-[#166534]" />
                </div>

                <!-- Pilih Kasir -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Petugas Kasir</label>
                    <select name="kasir_id" class="w-full bg-white border border-gray-200 rounded-lg pl-3.5 pr-9 py-2 text-xs text-gray-900 focus:outline-none focus:border-[#166534] focus:ring-1 focus:ring-[#166534] cursor-pointer">
                        <option value="">Semua Kasir</option>
                        @foreach ($kasirOptions as $k)
                            <option value="{{ $k->id }}" {{ (string)$kasirId === (string)$k->id ? 'selected' : '' }}>
                                {{ $k->name }} ({{ $k->role }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Filter Status (Kolom Penuh Mandiri Agar Tidak Terpotong) -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Status</label>
                    <select name="status" class="w-full bg-white border border-gray-200 rounded-lg pl-3.5 pr-9 py-2 text-xs text-gray-900 focus:outline-none focus:border-[#166534] focus:ring-1 focus:ring-[#166534] cursor-pointer">
                        <option value="">Semua Status</option>
                        <option value="selesai" {{ $status === 'selesai' ? 'selected' : '' }}>Selesai</option>
                        <option value="dibatalkan" {{ $status === 'dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
                    </select>
                </div>

                <!-- Tombol Aksi -->
                <div class="flex items-center gap-2">
                    <button type="submit" 
                            class="flex-1 py-2 px-3.5 bg-[#166534] hover:bg-[#14532D] text-white rounded-lg text-xs font-semibold shadow-xs transition text-center">
                        Terapkan
                    </button>
                    @if ($searchNomor || $kasirId || $status || $tanggalMulai || $tanggalSelesai)
                        <a href="{{ route('admin.penjualan.index') }}" 
                           title="Atur Ulang Filter"
                           class="py-2 px-3 bg-white hover:bg-gray-50 text-gray-600 hover:text-gray-900 rounded-lg border border-gray-200 text-xs font-semibold transition text-center shrink-0">
                            Reset
                        </a>
                    @endif
                </div>

            </form>
        </div>

            </form>
        </div>

        <!-- TABEL DAFTAR PENJUALAN -->
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <div class="p-4 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold text-gray-900">Daftar Transaksi Penjualan</h2>
                    <p class="text-xs text-gray-500">Menampilkan 15 transaksi per halaman</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50 text-gray-500 uppercase font-semibold text-[11px] tracking-wider">
                            <th class="py-3 px-4">No. Nota</th>
                            <th class="py-3 px-4">Waktu Transaksi</th>
                            <th class="py-3 px-4">Kasir</th>
                            <th class="py-3 px-4">Jumlah Barang</th>
                            <th class="py-3 px-4 text-right">Total Belanja</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($penjualanList as $p)
                            <tr class="hover:bg-gray-50/80 transition">
                                <td class="py-3.5 px-4 font-mono font-bold text-gray-900">
                                    {{ $p->nomor }}
                                </td>
                                <td class="py-3.5 px-4 text-gray-700">
                                    <div>{{ $p->waktu_transaksi->format('d/m/Y') }}</div>
                                    <div class="text-[10px] text-gray-400 font-mono">{{ $p->waktu_transaksi->format('H:i') }} WIT</div>
                                </td>
                                <td class="py-3.5 px-4 text-gray-700">
                                    <div class="font-semibold text-gray-900">{{ $p->user?->name ?? 'Kasir' }}</div>
                                    <div class="text-[10px] text-gray-400">@<span>{{ $p->user?->username ?? '-' }}</span></div>
                                </td>
                                <td class="py-3.5 px-4 text-gray-700">
                                    {{ $p->detail->sum('jumlah') }} barang
                                    <span class="text-[10px] text-gray-400 block">({{ $p->detail->count() }} jenis barang)</span>
                                </td>
                                <td class="py-3.5 px-4 text-right font-bold tabular-nums text-gray-900">
                                    Rp {{ number_format($p->total, 0, ',', '.') }}
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    @if ($p->status === 'selesai')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-[#DCFCE7] text-[#166534]">
                                            <span class="w-1.5 h-1.5 rounded-full bg-[#166534]"></span>
                                            <span>Selesai</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                            <span>Dibatalkan</span>
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <a href="{{ route('admin.penjualan.show', $p->id) }}" 
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white hover:bg-gray-50 border border-gray-200 text-xs font-semibold text-gray-700 transition">
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
                                <td colspan="7" class="py-12 text-center text-gray-400">
                                    <svg class="w-10 h-10 mx-auto text-gray-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <rect x="2" y="3" width="20" height="14" rx="2" ry="2"/>
                                        <line x1="8" y1="21" x2="16" y2="21"/>
                                        <line x1="12" y1="17" x2="12" y2="21"/>
                                    </svg>
                                    <p class="font-medium text-xs text-gray-600">Belum ada transaksi penjualan yang cocok dengan filter.</p>
                                    <a href="{{ route('kasir.index') }}" class="text-[#166534] font-semibold text-xs mt-2 inline-block hover:underline">
                                        Mulai Penjualan Baru di Layar Kasir &rarr;
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginasi -->
            @if ($penjualanList->hasPages())
                <div class="p-4 border-t border-gray-100 bg-gray-50/50">
                    {{ $penjualanList->links() }}
                </div>
            @endif
        </div>

    </div>
</x-layouts.admin>
