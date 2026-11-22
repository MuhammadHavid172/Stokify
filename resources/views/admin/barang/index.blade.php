<x-layouts.admin title="Master Data Barang">
    <div class="space-y-6" x-data="{ 
        toggleModalOpen: false, 
        toggleData: { id: '', nama: '', kode: '', is_aktif: true, action: '' }
    }">
        
        <!-- Sub-Navigasi Tab: Data Barang & Kategori -->
        <div class="flex items-center gap-2 border-b border-gray-200 pb-3">
            <a href="{{ route('admin.barang.index') }}" 
               class="px-4 py-2 rounded-lg text-xs font-bold transition {{ request()->routeIs('admin.barang.*') ? 'bg-[#166534] text-white shadow-xs' : 'bg-white text-gray-600 hover:text-gray-900 border border-gray-200 hover:bg-gray-50' }}">
                Katalog Barang
            </a>
            <a href="{{ route('admin.kategori.index') }}" 
               class="px-4 py-2 rounded-lg text-xs font-bold transition {{ request()->routeIs('admin.kategori.*') ? 'bg-[#166534] text-white shadow-xs' : 'bg-white text-gray-600 hover:text-gray-900 border border-gray-200 hover:bg-gray-50' }}">
                Kategori Barang
            </a>
        </div>

        <!-- Header Halaman -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-slate-100">
            <div>
                <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 mb-1">
                    <a href="{{ route('admin.dashboard') }}" class="hover:text-slate-800 transition">Dashboard</a>
                    <span>/</span>
                    <span class="text-slate-800">Master Barang</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Master Data Barang</h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">
                    Kelola katalog barang, harga modal, harga jual, dan pantau persediaan stok kios.
                </p>
            </div>

            <div class="flex items-center gap-2.5 shrink-0">
                <a href="{{ route('admin.kategori.index') }}" 
                   class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs shadow-xs transition">
                    <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                    </svg>
                    <span>Kelola Kategori</span>
                </a>

                <a href="{{ route('admin.barang.create') }}" 
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-[#166534] hover:bg-[#14532D] text-white font-semibold text-xs shadow-xs transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <line x1="12" y1="5" x2="12" y2="19" />
                        <line x1="5" y1="12" x2="19" y2="12" />
                    </svg>
                    <span>Tambah Barang</span>
                </a>
            </div>
        </div>

        <!-- Filter & Search Bar Panel -->
        <div class="bg-slate-50/80 border border-slate-200/80 rounded-2xl p-4 sm:p-5 shadow-xs">
            <form method="GET" action="{{ route('admin.barang.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                <!-- 1. Pencarian Kode / Nama -->
                <div class="lg:col-span-2 relative">
                    <input type="text" 
                           name="q" 
                           value="{{ $search }}" 
                           placeholder="Cari nama atau kode barang..." 
                           class="w-full rounded-xl border border-slate-200 bg-white pl-9 pr-4 py-2 text-xs text-slate-800 placeholder-slate-400 focus:border-[#166534] focus:ring-1 focus:ring-[#166534] transition" />
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8" />
                            <line x1="21" y1="21" x2="16.65" y2="16.65" />
                        </svg>
                    </div>
                </div>

                <!-- 2. Filter Kategori -->
                <div>
                    <select name="kategori_id" 
                            onchange="this.form.submit()"
                            class="w-full rounded-xl border border-slate-200 bg-white pl-3.5 pr-9 py-2 text-xs text-slate-700 focus:border-[#166534] focus:ring-1 focus:ring-[#166534] transition cursor-pointer">
                        <option value="">Semua Kategori</option>
                        @foreach ($kategoriList as $kat)
                            <option value="{{ $kat->id }}" {{ (string) $kategoriId === (string) $kat->id ? 'selected' : '' }}>
                                {{ $kat->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- 3. Filter Status -->
                <div>
                    <select name="status" 
                            onchange="this.form.submit()"
                            class="w-full rounded-xl border border-slate-200 bg-white pl-3.5 pr-9 py-2 text-xs text-slate-700 focus:border-[#166534] focus:ring-1 focus:ring-[#166534] transition cursor-pointer">
                        <option value="">Semua Status</option>
                        <option value="aktif" {{ $status === 'aktif' ? 'selected' : '' }}>Aktif Saja</option>
                        <option value="nonaktif" {{ $status === 'nonaktif' ? 'selected' : '' }}>Nonaktif Saja</option>
                    </select>
                </div>

                <!-- 4. Tombol Aksi & Checkbox Stok Menipis -->
                <div class="flex items-center justify-between sm:justify-end gap-3">
                    <label class="inline-flex items-center gap-1.5 cursor-pointer text-xs font-semibold text-slate-700 select-none">
                        <input type="checkbox" 
                               name="menipis" 
                               value="1" 
                               onchange="this.form.submit()"
                               {{ $menipis === '1' || $menipis === 'true' ? 'checked' : '' }} 
                               class="rounded border-slate-300 text-rose-600 focus:ring-rose-500 w-3.5 h-3.5" />
                        <span class="text-rose-700">Stok Menipis Saja</span>
                    </label>

                    @if ($search || $kategoriId || $status || $menipis)
                        <a href="{{ route('admin.barang.index') }}" 
                           class="text-xs text-slate-500 hover:text-slate-800 underline transition shrink-0" 
                           title="Bersihkan Semua Filter">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Tabel / Daftar Barang -->
        @if ($barangList->count() > 0)
            <div class="bg-white border border-slate-200/80 rounded-2xl shadow-xs overflow-hidden">
                <!-- Tampilan Desktop & Tablet: Tabel Lengkap -->
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-600">
                        <thead class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200/80">
                            <tr>
                                <th class="px-5 py-3.5">Kode</th>
                                <th class="px-5 py-3.5">Nama Barang</th>
                                <th class="px-5 py-3.5">Kategori</th>
                                <th class="px-5 py-3.5 text-right">Harga Modal</th>
                                <th class="px-5 py-3.5 text-right">Harga Jual</th>
                                <th class="px-5 py-3.5 text-center">Stok</th>
                                <th class="px-5 py-3.5 text-center">Stok Min.</th>
                                <th class="px-5 py-3.5 text-center">Status</th>
                                <th class="px-5 py-3.5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($barangList as $barang)
                                <tr class="hover:bg-slate-50/80 transition {{ ! $barang->is_aktif ? 'bg-slate-50/40 opacity-75' : '' }}">
                                    <!-- Kode -->
                                    <td class="px-5 py-3.5 font-mono font-bold text-slate-900">
                                        <a href="{{ route('admin.barang.show', $barang) }}" class="hover:text-[#166534] transition">
                                            {{ $barang->kode }}
                                        </a>
                                    </td>

                                    <!-- Nama -->
                                    <td class="px-5 py-3.5">
                                        <a href="{{ route('admin.barang.show', $barang) }}" class="font-bold text-slate-900 hover:text-[#166534] transition block">
                                            {{ $barang->nama }}
                                        </a>
                                        <span class="text-[10px] text-slate-400">Satuan: {{ $barang->satuan }}</span>
                                    </td>

                                    <!-- Kategori -->
                                    <td class="px-5 py-3.5 text-slate-600 font-medium">
                                        {{ $barang->kategori->nama ?? '—' }}
                                    </td>

                                    <!-- Harga Modal -->
                                    <td class="px-5 py-3.5 text-right font-medium text-slate-600">
                                        {{ rupiah($barang->harga_modal) }}
                                    </td>

                                    <!-- Harga Jual -->
                                    <td class="px-5 py-3.5 text-right font-bold text-slate-900">
                                        {{ rupiah($barang->harga_jual) }}
                                        @if ($barang->harga_jual < $barang->harga_modal)
                                            <span class="block text-[10px] text-amber-600 font-semibold" title="Harga jual di bawah modal">⚠️ Rugi</span>
                                        @endif
                                    </td>

                                    <!-- Stok + Badges -->
                                    <td class="px-5 py-3.5 text-center">
                                        <div class="inline-flex flex-col items-center gap-1">
                                            <span class="font-bold text-xs text-slate-900">
                                                {{ $barang->stok }} <span class="text-[10px] font-normal text-slate-500">{{ $barang->satuan }}</span>
                                            </span>

                                            @if ($barang->stok == 0)
                                                <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-bold bg-rose-600 text-white">
                                                    Habis
                                                </span>
                                            @elseif ($barang->stok <= $barang->stok_minimum)
                                                <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-bold bg-rose-100 text-rose-700">
                                                    Menipis
                                                </span>
                                            @endif
                                        </div>
                                    </td>

                                    <!-- Stok Minimum -->
                                    <td class="px-5 py-3.5 text-center font-medium text-slate-500">
                                        {{ $barang->stok_minimum }}
                                    </td>

                                    <!-- Status Badge -->
                                    <td class="px-5 py-3.5 text-center">
                                        <x-badge :variant="$barang->is_aktif ? 'green' : 'red'" size="sm">
                                            {{ $barang->is_aktif ? 'Aktif' : 'Nonaktif' }}
                                        </x-badge>
                                    </td>

                                    <!-- Aksi -->
                                    <td class="px-5 py-3.5 text-right">
                                        <div class="inline-flex items-center gap-1.5">
                                            <!-- Detail / Riwayat Stok -->
                                            <a href="{{ route('admin.barang.show', $barang) }}" 
                                               class="p-1.5 rounded-lg border border-slate-200 text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition" 
                                               title="Detail & Riwayat Mutasi">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                            </a>

                                            <!-- Edit -->
                                            <a href="{{ route('admin.barang.edit', $barang) }}" 
                                               class="p-1.5 rounded-lg border border-slate-200 text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition" 
                                               title="Ubah Barang">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </a>

                                            <!-- Toggle Aktif / Nonaktif -->
                                            <button type="button" 
                                                    @click="toggleData = { id: {{ $barang->id }}, nama: '{{ addslashes($barang->nama) }}', kode: '{{ $barang->kode }}', is_aktif: {{ $barang->is_aktif ? 'true' : 'false' }}, action: '{{ route('admin.barang.toggle', $barang) }}' }; toggleModalOpen = true"
                                                    class="p-1.5 rounded-lg border border-slate-200 text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition" 
                                                    title="{{ $barang->is_aktif ? 'Nonaktifkan Barang' : 'Aktifkan Barang' }}">
                                                <svg class="w-4 h-4 {{ $barang->is_aktif ? 'text-amber-600' : 'text-emerald-600' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    @if ($barang->is_aktif)
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                                    @else
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    @endif
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Tampilan Mobile (375px): Kartu Bertumpuk Rapi -->
                <div class="md:hidden divide-y divide-slate-100">
                    @foreach ($barangList as $barang)
                        <div class="p-4 space-y-3 {{ ! $barang->is_aktif ? 'bg-slate-50/50 opacity-80' : '' }}">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <span class="text-[10px] font-mono font-bold text-slate-400 bg-slate-100 px-2 py-0.5 rounded">
                                        {{ $barang->kode }}
                                    </span>
                                    <a href="{{ route('admin.barang.show', $barang) }}" class="text-sm font-bold text-slate-900 block mt-1 hover:text-[#166534]">
                                        {{ $barang->nama }}
                                    </a>
                                    <p class="text-[11px] text-slate-500">
                                        Kategori: <span class="font-medium text-slate-700">{{ $barang->kategori->nama ?? '—' }}</span>
                                    </p>
                                </div>
                                <x-badge :variant="$barang->is_aktif ? 'green' : 'red'" size="sm">
                                    {{ $barang->is_aktif ? 'Aktif' : 'Nonaktif' }}
                                </x-badge>
                            </div>

                            <div class="grid grid-cols-2 gap-2 text-xs bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                                <div>
                                    <span class="text-[10px] text-slate-400 block">Harga Jual</span>
                                    <span class="font-bold text-slate-900">{{ rupiah($barang->harga_jual) }}</span>
                                </div>
                                <div>
                                    <span class="text-[10px] text-slate-400 block">Stok Saat Ini</span>
                                    <div class="flex items-center gap-1.5">
                                        <span class="font-black text-slate-900">{{ $barang->stok }} {{ $barang->satuan }}</span>
                                        @if ($barang->stok == 0)
                                            <span class="text-[9px] font-bold px-1.5 py-0.2 rounded bg-rose-600 text-white">Habis</span>
                                        @elseif ($barang->stok <= $barang->stok_minimum)
                                            <span class="text-[9px] font-bold px-1.5 py-0.2 rounded bg-rose-100 text-rose-700">Menipis</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-end gap-2 pt-1">
                                <a href="{{ route('admin.barang.show', $barang) }}" 
                                   class="px-3 py-1.5 rounded-lg border border-slate-200 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                                    Riwayat
                                </a>
                                <a href="{{ route('admin.barang.edit', $barang) }}" 
                                   class="px-3 py-1.5 rounded-lg border border-slate-200 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                                    Ubah
                                </a>
                                <button type="button" 
                                        @click="toggleData = { id: {{ $barang->id }}, nama: '{{ addslashes($barang->nama) }}', kode: '{{ $barang->kode }}', is_aktif: {{ $barang->is_aktif ? 'true' : 'false' }}, action: '{{ route('admin.barang.toggle', $barang) }}' }; toggleModalOpen = true"
                                        class="px-3 py-1.5 rounded-lg border text-xs font-semibold {{ $barang->is_aktif ? 'border-amber-200 text-amber-700 hover:bg-amber-50' : 'border-emerald-200 text-emerald-700 hover:bg-emerald-50' }}">
                                    {{ $barang->is_aktif ? 'Nonaktifkan' : 'Aktifkan' }}
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                @if ($barangList->hasPages())
                    <div class="px-5 py-4 border-t border-slate-100 bg-slate-50/50">
                        {{ $barangList->links() }}
                    </div>
                @endif
            </div>
        @else
            <x-empty-state 
                title="Belum Ada Barang" 
                description="{{ $search || $kategoriId || $status || $menipis ? 'Tidak ditemukan barang yang sesuai dengan kriteria filter Anda.' : 'Katalog inventaris kios Anda masih kosong. Tambahkan barang pertama untuk mulai mengelola stok.' }}"
                buttonText="{{ $search || $kategoriId || $status || $menipis ? 'Reset Filter' : 'Tambah Barang Baru' }}"
                buttonAction="{{ $search || $kategoriId || $status || $menipis ? route('admin.barang.index') : route('admin.barang.create') }}"
            />
        @endif

        <!-- Modal Konfirmasi Toggle Status Barang (Aktif / Nonaktif) -->
        <div x-show="toggleModalOpen" 
             x-cloak 
             class="fixed inset-0 z-50 overflow-y-auto" 
             role="dialog" 
             aria-modal="true">
            <div class="fixed inset-0 bg-black/60 backdrop-blur-xs transition-opacity" @click="toggleModalOpen = false"></div>
            <div class="flex min-h-full items-center justify-center p-4">
                <div x-show="toggleModalOpen" 
                     x-transition:enter="ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="ease-in duration-150"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="relative w-full max-w-md rounded-3xl bg-white p-6 sm:p-7 shadow-2xl border border-slate-200">
                    
                    <!-- Header: Ikon Sesuai Konteks & Judul Sejajar Rapi -->
                    <div class="flex items-start gap-3.5 mb-4">
                        <div class="w-11 h-11 rounded-2xl flex items-center justify-center shrink-0 border shadow-xs"
                             :class="toggleData.is_aktif ? 'bg-amber-50 text-amber-600 border-amber-200/70' : 'bg-emerald-50 text-emerald-600 border-emerald-200/70'">
                            <!-- Ikon Peringatan untuk Nonaktifkan -->
                            <template x-if="toggleData.is_aktif">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </template>
                            <!-- Ikon Centang Sukses untuk Aktifkan Kembali -->
                            <template x-if="!toggleData.is_aktif">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </template>
                        </div>
                        <div class="pt-0.5">
                            <h3 class="text-base font-bold text-slate-900" 
                                x-text="toggleData.is_aktif ? 'Nonaktifkan Barang?' : 'Aktifkan Kembali Barang?'">
                            </h3>
                            <p class="text-xs text-slate-500 mt-0.5">
                                Konfirmasi perubahan status ketersediaan barang di kios.
                            </p>
                        </div>
                    </div>

                    <!-- Ringkasan Barang yang Dituju -->
                    <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200/70 flex items-center justify-between gap-3 mb-3">
                        <div class="min-w-0 pr-2">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-0.5">Barang</span>
                            <p class="text-xs sm:text-sm font-bold text-slate-900 truncate" x-text="toggleData.nama"></p>
                        </div>
                        <div class="text-right shrink-0">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-0.5">Kode Barang</span>
                            <span class="inline-block font-mono text-[11px] font-bold px-2 py-0.5 rounded-lg bg-white border border-slate-200 text-slate-700 shadow-2xs" x-text="toggleData.kode"></span>
                        </div>
                    </div>

                    <!-- Kotak Informasi Catatan Rapi -->
                    <div class="p-3.5 rounded-2xl border text-xs flex items-start gap-2.5 transition mb-5"
                         :class="toggleData.is_aktif ? 'bg-amber-50/60 border-amber-200/70 text-amber-900' : 'bg-emerald-50/60 border-emerald-200/70 text-emerald-900'">
                        <svg class="w-4 h-4 shrink-0 mt-0.5" :class="toggleData.is_aktif ? 'text-amber-600' : 'text-emerald-600'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="12" y1="16" x2="12" y2="12"/>
                            <line x1="12" y1="8" x2="12.01" y2="8"/>
                        </svg>
                        <div class="leading-relaxed text-[11px]">
                            <strong class="font-bold">Catatan:</strong> 
                            <span x-show="toggleData.is_aktif">Barang dinonaktifkan, bukan dihapus, agar riwayat penjualan dan mutasi stok tetap utuh. Barang ini tidak akan muncul di kasir penjualan.</span>
                            <span x-show="!toggleData.is_aktif">Barang akan kembali aktif dan dapat dicari serta dijual kembali di halaman kasir.</span>
                        </div>
                    </div>

                    <!-- Tombol Aksi Rapi & Seimbang -->
                    <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-slate-100">
                        <button type="button" 
                                @click="toggleModalOpen = false" 
                                class="px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-700 hover:bg-slate-50 transition text-center">
                            Batal
                        </button>
                        
                        <form method="POST" :action="toggleData.action">
                            @csrf
                            <button type="submit" 
                                    class="px-5 py-2.5 rounded-xl text-xs font-bold text-white shadow-xs transition text-center flex items-center justify-center gap-1.5"
                                    :class="toggleData.is_aktif ? 'bg-amber-600 hover:bg-amber-700' : 'bg-[#166534] hover:bg-[#14532D]'">
                                <span x-text="toggleData.is_aktif ? 'Ya, Nonaktifkan' : 'Ya, Aktifkan Kembali'"></span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-layouts.admin>
