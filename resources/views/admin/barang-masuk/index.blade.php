<x-layouts.admin title="Daftar Barang Masuk">
    <div class="space-y-6">
        
        <!-- Sub-Navigasi Tab: Barang Masuk & Supplier -->
        <div class="flex items-center gap-2 border-b border-gray-200 pb-3">
            <a href="{{ route('admin.barang-masuk.index') }}" 
               class="px-4 py-2 rounded-lg text-xs font-bold transition {{ request()->routeIs('admin.barang-masuk.*') ? 'bg-[#166534] text-white shadow-xs' : 'bg-white text-gray-600 hover:text-gray-900 border border-gray-200 hover:bg-gray-50' }}">
                Riwayat Pasokan Masuk
            </a>
            <a href="{{ route('admin.supplier.index') }}" 
               class="px-4 py-2 rounded-lg text-xs font-bold transition {{ request()->routeIs('admin.supplier.*') ? 'bg-[#166534] text-white shadow-xs' : 'bg-white text-gray-600 hover:text-gray-900 border border-gray-200 hover:bg-gray-50' }}">
                Data Supplier
            </a>
        </div>

        <!-- Header Halaman -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-slate-100">
            <div>
                <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 mb-1">
                    <a href="{{ route('admin.dashboard') }}" class="hover:text-slate-800 transition">Dashboard</a>
                    <span>/</span>
                    <span class="text-slate-800">Barang Masuk</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Barang Masuk</h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">
                    Catat restok barang dagangan dari supplier atau pembelian grosir di pasar.
                </p>
            </div>

            <div class="flex items-center gap-2.5 shrink-0">
                <a href="{{ route('admin.supplier.index') }}" 
                   class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs shadow-xs transition">
                    <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    <span>Data Supplier</span>
                </a>

                <a href="{{ route('admin.barang-masuk.create') }}" 
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-[#166534] to-[#14532D] hover:from-[#14532D] hover:to-[#14532D] text-white font-bold text-xs shadow-md shadow-[#166534]/20 transition transform hover:-translate-y-0.5 active:translate-y-0">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <line x1="12" y1="5" x2="12" y2="19" />
                        <line x1="5" y1="12" x2="19" y2="12" />
                    </svg>
                    <span>Catat Barang Masuk</span>
                </a>
            </div>
        </div>

        <!-- Filter & Search Bar Panel -->
        <div class="bg-slate-50/80 border border-slate-200/80 rounded-2xl p-4 sm:p-5 shadow-xs">
            <form method="GET" action="{{ route('admin.barang-masuk.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                <!-- 1. Pencarian Nomor / Catatan -->
                <div class="lg:col-span-1 relative">
                    <input type="text" 
                           name="q" 
                           value="{{ $search }}" 
                           placeholder="Cari nomor nota..." 
                           class="w-full rounded-xl border border-slate-200 bg-white pl-9 pr-3 py-2 text-xs text-slate-800 placeholder-slate-400 focus:border-[#166534] focus:ring-1 focus:ring-[#166534] transition" />
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8" />
                            <line x1="21" y1="21" x2="16.65" y2="16.65" />
                        </svg>
                    </div>
                </div>

                <!-- 2. Filter Supplier -->
                <div>
                    <select name="supplier_id" 
                            onchange="this.form.submit()"
                            class="w-full rounded-xl border border-slate-200 bg-white pl-3.5 pr-9 py-2 text-xs text-slate-700 focus:border-[#166534] focus:ring-1 focus:ring-[#166534] transition cursor-pointer">
                        <option value="">Semua Supplier</option>
                        @foreach ($supplierList as $sup)
                            <option value="{{ $sup->id }}" {{ (string) $supplierId === (string) $sup->id ? 'selected' : '' }}>
                                {{ $sup->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- 3. Filter Tanggal Mulai & Selesai -->
                <div class="grid grid-cols-2 gap-1.5">
                    <input type="date" 
                           name="tanggal_mulai" 
                           value="{{ $tanggalMulai }}" 
                           placeholder="Mulai" 
                           onchange="this.form.submit()"
                           class="w-full rounded-xl border border-slate-200 bg-white px-2 py-1.5 text-xs text-slate-700 focus:border-[#166534] focus:ring-1 focus:ring-[#166534]" />
                    <input type="date" 
                           name="tanggal_selesai" 
                           value="{{ $tanggalSelesai }}" 
                           placeholder="Selesai" 
                           onchange="this.form.submit()"
                           class="w-full rounded-xl border border-slate-200 bg-white px-2 py-1.5 text-xs text-slate-700 focus:border-[#166534] focus:ring-1 focus:ring-[#166534]" />
                </div>

                <!-- 4. Filter Status -->
                <div>
                    <select name="status" 
                            onchange="this.form.submit()"
                            class="w-full rounded-xl border border-slate-200 bg-white pl-3.5 pr-9 py-2 text-xs text-slate-700 focus:border-[#166534] focus:ring-1 focus:ring-[#166534] transition cursor-pointer">
                        <option value="">Semua Status</option>
                        <option value="selesai" {{ $status === 'selesai' ? 'selected' : '' }}>Selesai</option>
                        <option value="dibatalkan" {{ $status === 'dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
                    </select>
                </div>

                <!-- 5. Reset Filter -->
                <div class="flex items-center justify-between sm:justify-end gap-2">
                    <span class="text-xs font-semibold text-slate-500">
                        Total: <strong class="text-slate-800">{{ $barangMasukList->total() }}</strong> nota
                    </span>

                    @if ($search || $supplierId || $status || $tanggalMulai || $tanggalSelesai)
                        <a href="{{ route('admin.barang-masuk.index') }}" 
                           class="text-xs text-slate-500 hover:text-slate-800 underline transition shrink-0">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Tabel / Daftar Barang Masuk -->
        @if ($barangMasukList->count() > 0)
            <div class="bg-white border border-slate-200/80 rounded-2xl shadow-xs overflow-hidden">
                <!-- Tampilan Desktop & Tablet -->
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-600">
                        <thead class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200/80">
                            <tr>
                                <th class="px-5 py-3.5">Nomor Nota</th>
                                <th class="px-5 py-3.5">Tanggal</th>
                                <th class="px-5 py-3.5">Supplier</th>
                                <th class="px-5 py-3.5 text-center">Jumlah Barang</th>
                                <th class="px-5 py-3.5 text-right">Total Belanja</th>
                                <th class="px-5 py-3.5 text-center">Status</th>
                                <th class="px-5 py-3.5">Dicatat Oleh</th>
                                <th class="px-5 py-3.5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($barangMasukList as $bm)
                                <tr class="hover:bg-slate-50/80 transition {{ $bm->status === 'dibatalkan' ? 'bg-rose-50/30 opacity-75' : '' }}">
                                    <!-- Nomor Nota -->
                                    <td class="px-5 py-3.5 font-mono font-bold text-slate-900">
                                        <a href="{{ route('admin.barang-masuk.show', $bm) }}" class="hover:text-[#166534] transition">
                                            {{ $bm->nomor }}
                                        </a>
                                    </td>

                                    <!-- Tanggal -->
                                    <td class="px-5 py-3.5 text-slate-600">
                                        {{ $bm->tanggal->translatedFormat('d M Y') }}
                                    </td>

                                    <!-- Supplier -->
                                    <td class="px-5 py-3.5 font-medium text-slate-700">
                                        {{ $bm->supplier->nama ?? 'Beli Langsung / Pasar' }}
                                    </td>

                                    <!-- Jumlah Barang -->
                                    <td class="px-5 py-3.5 text-center font-bold text-slate-800">
                                        {{ $bm->detail_count }} barang
                                    </td>

                                    <!-- Total Belanja -->
                                    <td class="px-5 py-3.5 text-right font-black text-slate-900">
                                        {{ rupiah($bm->total) }}
                                    </td>

                                    <!-- Status Badge -->
                                    <td class="px-5 py-3.5 text-center">
                                        @if ($bm->status === 'selesai')
                                            <x-badge variant="green" size="sm">Selesai</x-badge>
                                        @else
                                            <x-badge variant="red" size="sm">Dibatalkan</x-badge>
                                        @endif
                                    </td>

                                    <!-- Dicatat Oleh -->
                                    <td class="px-5 py-3.5 text-slate-500">
                                        {{ $bm->user->name ?? 'Admin' }}
                                    </td>

                                    <!-- Aksi -->
                                    <td class="px-5 py-3.5 text-right">
                                        <a href="{{ route('admin.barang-masuk.show', $bm) }}" 
                                           class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border border-slate-200 text-xs font-semibold text-slate-700 hover:bg-slate-100 transition">
                                            <span>Detail</span>
                                            <span class="text-slate-400">&rarr;</span>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Tampilan Mobile (375px): Kartu Bertumpuk -->
                <div class="md:hidden divide-y divide-slate-100">
                    @foreach ($barangMasukList as $bm)
                        <div class="p-4 space-y-3 {{ $bm->status === 'dibatalkan' ? 'bg-rose-50/30 opacity-80' : '' }}">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <span class="text-[10px] font-mono font-bold text-slate-400 bg-slate-100 px-2 py-0.5 rounded">
                                        {{ $bm->nomor }}
                                    </span>
                                    <a href="{{ route('admin.barang-masuk.show', $bm) }}" class="text-sm font-bold text-slate-900 block mt-1 hover:text-[#166534]">
                                        {{ $bm->supplier->nama ?? 'Beli Langsung / Pasar' }}
                                    </a>
                                    <p class="text-[11px] text-slate-500">
                                        {{ $bm->tanggal->translatedFormat('d M Y') }} • {{ $bm->detail_count }} item barang
                                    </p>
                                </div>
                                @if ($bm->status === 'selesai')
                                    <x-badge variant="green" size="sm">Selesai</x-badge>
                                @else
                                    <x-badge variant="red" size="sm">Dibatalkan</x-badge>
                                @endif
                            </div>

                            <div class="flex items-center justify-between text-xs bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                                <div>
                                    <span class="text-[10px] text-slate-400 block">Total Barang Masuk</span>
                                    <span class="font-black text-slate-900 text-sm">{{ rupiah($bm->total) }}</span>
                                </div>
                                <a href="{{ route('admin.barang-masuk.show', $bm) }}" 
                                   class="px-3 py-1.5 rounded-lg bg-white border border-slate-200 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                                    Lihat Detail &rarr;
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                @if ($barangMasukList->hasPages())
                    <div class="px-5 py-4 border-t border-slate-100 bg-slate-50/50">
                        {{ $barangMasukList->links() }}
                    </div>
                @endif
            </div>
        @else
            <x-empty-state 
                title="Belum Ada Transaksi Barang Masuk" 
                description="{{ $search || $supplierId || $status || $tanggalMulai || $tanggalSelesai ? 'Tidak ditemukan transaksi barang masuk yang sesuai dengan kriteria filter Anda.' : 'Catat transaksi barang masuk pertama Anda untuk menambah persediaan stok barang di kios.' }}"
                buttonText="{{ $search || $supplierId || $status || $tanggalMulai || $tanggalSelesai ? 'Reset Filter' : 'Catat Barang Masuk' }}"
                buttonAction="{{ $search || $supplierId || $status || $tanggalMulai || $tanggalSelesai ? route('admin.barang-masuk.index') : route('admin.barang-masuk.create') }}"
            />
        @endif

    </div>
</x-layouts.admin>
