<x-layouts.admin title="Daftar Penyesuaian Stok">
    <div class="space-y-6">
        
        <!-- Header Halaman -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-slate-100">
            <div>
                <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 mb-1">
                    <a href="{{ route('admin.dashboard') }}" class="hover:text-slate-800 transition">Dashboard</a>
                    <span>/</span>
                    <span class="text-slate-800">Penyesuaian Stok</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Penyesuaian Stok</h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">
                    Catat barang rusak, hilang, kedaluwarsa, koreksi stok, dan selisih stok opname berkala.
                </p>
            </div>

            <div class="flex items-center gap-2.5 shrink-0">
                <a href="{{ route('admin.penyesuaian.opname') }}" 
                   class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs shadow-xs transition">
                    <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                    <span>Stok Opname</span>
                </a>

                <a href="{{ route('admin.penyesuaian.create') }}" 
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-[#166534] to-[#14532D] hover:from-[#14532D] hover:to-[#14532D] text-white font-bold text-xs shadow-md shadow-[#166534]/20 transition transform hover:-translate-y-0.5 active:translate-y-0">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <line x1="12" y1="5" x2="12" y2="19" />
                        <line x1="5" y1="12" x2="19" y2="12" />
                    </svg>
                    <span>Catat Penyesuaian</span>
                </a>
            </div>
        </div>

        <!-- Filter & Search Bar Panel -->
        <div class="bg-slate-50/80 border border-slate-200/80 rounded-2xl p-4 sm:p-5 shadow-xs">
            <form method="GET" action="{{ route('admin.penyesuaian.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                <!-- 1. Pencarian Nomor / Catatan -->
                <div class="lg:col-span-1 relative">
                    <input type="text" 
                           name="q" 
                           value="{{ request('q') }}" 
                           placeholder="Cari nomor nota..." 
                           class="w-full rounded-xl border border-slate-200 bg-white pl-9 pr-3 py-2 text-xs text-slate-800 placeholder-slate-400 focus:border-[#166534] focus:ring-1 focus:ring-[#166534] transition" />
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8" />
                            <line x1="21" y1="21" x2="16.65" y2="16.65" />
                        </svg>
                    </div>
                </div>

                <!-- 2. Filter Mode -->
                <div>
                    <select name="mode" 
                            onchange="this.form.submit()"
                            class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs text-slate-700 focus:border-[#166534] focus:ring-1 focus:ring-[#166534] transition">
                        <option value="">Semua Mode</option>
                        <option value="manual" {{ request('mode') === 'manual' ? 'selected' : '' }}>Manual (Rusak/Hilang/Koreksi)</option>
                        <option value="opname" {{ request('mode') === 'opname' ? 'selected' : '' }}>Stok Opname</option>
                    </select>
                </div>

                <!-- 3. Tanggal Mulai -->
                <div>
                    <input type="date" 
                           name="tanggal_mulai" 
                           value="{{ request('tanggal_mulai') }}" 
                           onchange="this.form.submit()"
                           class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs text-slate-700 focus:border-[#166534] focus:ring-1 focus:ring-[#166534] transition" />
                </div>

                <!-- 4. Tanggal Selesai -->
                <div>
                    <input type="date" 
                           name="tanggal_selesai" 
                           value="{{ request('tanggal_selesai') }}" 
                           onchange="this.form.submit()"
                           class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs text-slate-700 focus:border-[#166534] focus:ring-1 focus:ring-[#166534] transition" />
                </div>

                <!-- 5. Tombol Filter & Reset -->
                <div class="flex items-center gap-2">
                    <button type="submit" 
                            class="flex-1 py-2 px-3 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs shadow-xs transition">
                        Filter
                    </button>
                    @if (request()->hasAny(['q', 'mode', 'tanggal_mulai', 'tanggal_selesai']))
                        <a href="{{ route('admin.penyesuaian.index') }}" 
                           class="py-2 px-3 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 font-semibold text-xs transition">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Tabel Riwayat Penyesuaian Stok (Desktop) -->
        <div class="hidden md:block bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            @if ($penyesuaianList->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="border-b border-slate-100 bg-slate-50/60 font-bold text-slate-500 uppercase tracking-wider text-[11px]">
                                <th class="px-5 py-3.5">Nomor Nota</th>
                                <th class="px-5 py-3.5">Tanggal</th>
                                <th class="px-5 py-3.5 text-center">Mode</th>
                                <th class="px-5 py-3.5 text-center">Barang</th>
                                <th class="px-5 py-3.5 text-right">Total Kerugian</th>
                                <th class="px-5 py-3.5">Dicatat Oleh</th>
                                <th class="px-5 py-3.5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            @foreach ($penyesuaianList as $item)
                                <tr class="hover:bg-slate-50/80 transition">
                                    <!-- Nomor Nota -->
                                    <td class="px-5 py-3.5">
                                        <a href="{{ route('admin.penyesuaian.show', $item) }}" 
                                           class="font-mono font-bold text-slate-900 hover:text-[#166534] transition">
                                            {{ $item->nomor }}
                                        </a>
                                        @if ($item->catatan)
                                            <p class="text-[10px] text-slate-400 truncate max-w-xs mt-0.5">{{ $item->catatan }}</p>
                                        @endif
                                    </td>

                                    <!-- Tanggal -->
                                    <td class="px-5 py-3.5 text-slate-600">
                                        {{ $item->tanggal->translatedFormat('d M Y') }}
                                    </td>

                                    <!-- Mode -->
                                    <td class="px-5 py-3.5 text-center">
                                        @if ($item->mode === 'opname')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                                Stok Opname
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                                Manual
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Jumlah Barang -->
                                    <td class="px-5 py-3.5 text-center font-bold text-slate-700">
                                        {{ $item->detail->count() }} barang
                                    </td>

                                    <!-- Total Kerugian -->
                                    <td class="px-5 py-3.5 text-right font-bold {{ $item->total_kerugian > 0 ? 'text-rose-600' : 'text-slate-500' }}">
                                        {{ rupiah($item->total_kerugian) }}
                                    </td>

                                    <!-- Dicatat Oleh -->
                                    <td class="px-5 py-3.5 text-slate-600">
                                        {{ $item->user->name ?? 'Admin' }}
                                    </td>

                                    <!-- Aksi -->
                                    <td class="px-5 py-3.5 text-right">
                                        <a href="{{ route('admin.penyesuaian.show', $item) }}" 
                                           class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-50 font-bold text-[11px] transition">
                                            <span>Detail</span>
                                            <span>&rarr;</span>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($penyesuaianList->hasPages())
                    <div class="px-5 py-4 border-t border-slate-100 bg-slate-50/50">
                        {{ $penyesuaianList->links() }}
                    </div>
                @endif
            @else
                <div class="p-8 sm:p-12 text-center">
                    <x-empty-state 
                        title="Belum ada riwayat penyesuaian stok"
                        description="Catat barang rusak, hilang, kedaluwarsa, atau lakukan stok opname berkala untuk menyelaraskan stok fisik."
                        actionLabel="Catat Penyesuaian"
                        :actionUrl="route('admin.penyesuaian.create')" />
                </div>
            @endif
        </div>

        <!-- Daftar Kartu untuk Tampilan Mobile (375px) -->
        <div class="block md:hidden space-y-3">
            @forelse ($penyesuaianList as $item)
                <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="font-mono text-xs font-bold text-slate-900 bg-slate-100 px-2 py-0.5 rounded-md">
                            {{ $item->nomor }}
                        </span>
                        @if ($item->mode === 'opname')
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 border border-blue-200">
                                Opname
                            </span>
                        @else
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 border border-amber-200">
                                Manual
                            </span>
                        @endif
                    </div>

                    <div class="text-xs text-slate-500 space-y-1">
                        <div class="flex justify-between">
                            <span>Tanggal:</span>
                            <span class="font-semibold text-slate-800">{{ $item->tanggal->translatedFormat('d M Y') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Jumlah Barang:</span>
                            <span class="font-semibold text-slate-800">{{ $item->detail->count() }} item</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Total Kerugian:</span>
                            <span class="font-bold {{ $item->total_kerugian > 0 ? 'text-rose-600' : 'text-slate-800' }}">
                                {{ rupiah($item->total_kerugian) }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span>Pencatat:</span>
                            <span class="text-slate-700">{{ $item->user->name ?? 'Admin' }}</span>
                        </div>
                    </div>

                    <div class="pt-2 border-t border-slate-100">
                        <a href="{{ route('admin.penyesuaian.show', $item) }}" 
                           class="w-full py-2 px-3 rounded-xl bg-slate-50 hover:bg-slate-100 text-slate-700 font-bold text-xs flex items-center justify-center gap-1 border border-slate-200 transition">
                            <span>Lihat Detail Nota</span>
                            <span>&rarr;</span>
                        </a>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-2xl border border-slate-200/80 p-8 text-center">
                    <x-empty-state 
                        title="Belum ada penyesuaian"
                        description="Belum ada data penyesuaian stok yang dicatat."
                        actionLabel="Catat Penyesuaian"
                        :actionUrl="route('admin.penyesuaian.create')" />
                </div>
            @endforelse

            @if ($penyesuaianList->hasPages())
                <div class="pt-2">
                    {{ $penyesuaianList->links() }}
                </div>
            @endif
        </div>

    </div>
</x-layouts.admin>
