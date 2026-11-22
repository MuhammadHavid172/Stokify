<x-layouts.admin title="Kelola Kategori">
    <div class="space-y-6" x-data="{ 
        createModalOpen: false, 
        editModalOpen: false, 
        deleteModalOpen: false,
        editData: { id: '', nama: '', action: '' },
        deleteData: { id: '', nama: '', count: 0, action: '' }
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
                    <a href="{{ route('admin.barang.index') }}" class="hover:text-slate-800 transition">Barang</a>
                    <span>/</span>
                    <span class="text-slate-800">Kategori</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Kategori Barang</h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">
                    Kelola pengelompokan barang untuk memudahkan pencarian dan penataan inventaris kios.
                </p>
            </div>

            <div class="flex items-center gap-2.5 shrink-0">
                <a href="{{ route('admin.barang.index') }}" 
                   class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs shadow-xs transition">
                    <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    <span>Daftar Barang</span>
                </a>

                <button type="button" 
                        @click="createModalOpen = true"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-[#166534] to-[#14532D] hover:from-[#14532D] hover:to-[#14532D] text-white font-bold text-xs shadow-md shadow-[#166534]/20 transition transform hover:-translate-y-0.5 active:translate-y-0">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <line x1="12" y1="5" x2="12" y2="19" />
                        <line x1="5" y1="12" x2="19" y2="12" />
                    </svg>
                    <span>Tambah Kategori</span>
                </button>
            </div>
        </div>

        <!-- Filter Pencarian Sederhana -->
        <div class="bg-slate-50/70 border border-slate-200/80 rounded-2xl p-4 flex flex-col sm:flex-row items-center justify-between gap-3">
            <form method="GET" action="{{ route('admin.kategori.index') }}" class="w-full sm:w-80 relative">
                <input type="text" 
                       name="q" 
                       value="{{ request('q') }}" 
                       placeholder="Cari nama kategori..." 
                       class="w-full rounded-xl border border-slate-200 bg-white pl-9 pr-4 py-2 text-xs text-slate-800 placeholder-slate-400 focus:border-[#166534] focus:ring-1 focus:ring-[#166534] transition" />
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8" />
                        <line x1="21" y1="21" x2="16.65" y2="16.65" />
                    </svg>
                </div>
            </form>

            <span class="text-xs font-semibold text-slate-500">
                Total: <span class="font-bold text-slate-800">{{ $kategoriList->total() }}</span> kategori
            </span>
        </div>

        <!-- Tabel Daftar Kategori -->
        @if ($kategoriList->count() > 0)
            <div class="bg-white border border-slate-200/80 rounded-2xl shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-600">
                        <thead class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200/80">
                            <tr>
                                <th class="px-5 py-3.5">Nama Kategori</th>
                                <th class="px-5 py-3.5 text-center">Jumlah Barang</th>
                                <th class="px-5 py-3.5">Tanggal Dibuat</th>
                                <th class="px-5 py-3.5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($kategoriList as $kategori)
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="px-5 py-3.5 font-bold text-slate-900">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-8 h-8 rounded-lg bg-[#DCFCE7]/40 text-[#166534] flex items-center justify-center shrink-0 border border-[#6B7280]/30/50">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                                </svg>
                                            </div>
                                            <span>{{ $kategori->nama }}</span>
                                        </div>
                                    </td>
                                    <td class="px-5 py-3.5 text-center font-semibold">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold {{ $kategori->barang_count > 0 ? 'bg-slate-100 text-slate-800' : 'bg-slate-50 text-slate-400' }}">
                                            {{ $kategori->barang_count }} barang
                                        </span>
                                    </td>
                                    <td class="px-5 py-3.5 text-slate-500">
                                        {{ $kategori->created_at->translatedFormat('d M Y, H:i') }}
                                    </td>
                                    <td class="px-5 py-3.5 text-right">
                                        <div class="inline-flex items-center gap-1.5">
                                            <!-- Tombol Ubah -->
                                            <button type="button" 
                                                    @click="editData = { id: {{ $kategori->id }}, nama: '{{ addslashes($kategori->nama) }}', action: '{{ route('admin.kategori.update', $kategori) }}' }; editModalOpen = true"
                                                    class="p-1.5 rounded-lg border border-slate-200 text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition" 
                                                    title="Ubah Kategori">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </button>

                                            <!-- Tombol Hapus -->
                                            <button type="button" 
                                                    @click="deleteData = { id: {{ $kategori->id }}, nama: '{{ addslashes($kategori->nama) }}', count: {{ $kategori->barang_count }}, action: '{{ route('admin.kategori.destroy', $kategori) }}' }; deleteModalOpen = true"
                                                    class="p-1.5 rounded-lg border border-rose-200 text-rose-600 hover:text-rose-800 hover:bg-rose-50 transition" 
                                                    title="Hapus Kategori">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if ($kategoriList->hasPages())
                    <div class="px-5 py-4 border-t border-slate-100 bg-slate-50/50">
                        {{ $kategoriList->links() }}
                    </div>
                @endif
            </div>
        @else
            <x-empty-state 
                title="Belum Ada Kategori" 
                description="{{ request('q') ? 'Tidak ditemukan kategori yang cocok dengan pencarian Anda.' : 'Buat kategori pertama untuk mulai mengelompokkan barang dagangan kios Anda.' }}"
                buttonText="{{ request('q') ? 'Reset Pencarian' : 'Tambah Kategori' }}"
                buttonAction="{{ request('q') ? route('admin.kategori.index') : '#' }}"
                @click="!{{ request('q') ? 'true' : 'false' }} && (createModalOpen = true)"
            />
        @endif

        <!-- Modal Tambah Kategori -->
        <div x-show="createModalOpen" 
             x-cloak 
             class="fixed inset-0 z-50 overflow-y-auto" 
             role="dialog" 
             aria-modal="true">
            <div class="fixed inset-0 bg-black/60 backdrop-blur-xs transition-opacity" @click="createModalOpen = false"></div>
            <div class="flex min-h-full items-center justify-center p-4">
                <div x-show="createModalOpen" 
                     x-transition 
                     class="relative w-full max-w-md rounded-3xl bg-white p-6 sm:p-7 shadow-2xl border border-slate-200">
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                        <h3 class="text-lg font-bold text-slate-900">Tambah Kategori Baru</h3>
                        <button @click="createModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('admin.kategori.store') }}" class="mt-4 space-y-4">
                        @csrf
                        <div>
                            <label for="nama_tambah" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Nama Kategori <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" 
                                   id="nama_tambah" 
                                   name="nama" 
                                   required 
                                   autofocus 
                                   placeholder="Nama kategori barang baru" 
                                   class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs sm:text-sm text-slate-800 focus:border-[#166534] focus:ring-1 focus:ring-[#166534] transition" />
                            <p class="text-[11px] text-slate-400 mt-1">Nama kategori harus unik di kios Anda.</p>
                        </div>

                        <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100">
                            <button type="button" 
                                    @click="createModalOpen = false" 
                                    class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition">
                                Batal
                            </button>
                            <button type="submit" 
                                    class="px-5 py-2 rounded-xl bg-gradient-to-r from-[#166534] to-[#14532D] text-xs font-bold text-white shadow-sm hover:shadow transition">
                                Simpan Kategori
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Ubah Kategori -->
        <div x-show="editModalOpen" 
             x-cloak 
             class="fixed inset-0 z-50 overflow-y-auto" 
             role="dialog" 
             aria-modal="true">
            <div class="fixed inset-0 bg-black/60 backdrop-blur-xs transition-opacity" @click="editModalOpen = false"></div>
            <div class="flex min-h-full items-center justify-center p-4">
                <div x-show="editModalOpen" 
                     x-transition 
                     class="relative w-full max-w-md rounded-3xl bg-white p-6 sm:p-7 shadow-2xl border border-slate-200">
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                        <h3 class="text-lg font-bold text-slate-900">Ubah Kategori</h3>
                        <button @click="editModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <form method="POST" :action="editData.action" class="mt-4 space-y-4">
                        @csrf
                        @method('PUT')
                        <div>
                            <label for="nama_ubah" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Nama Kategori <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" 
                                   id="nama_ubah" 
                                   name="nama" 
                                   x-model="editData.nama" 
                                   required 
                                   class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs sm:text-sm text-slate-800 focus:border-[#166534] focus:ring-1 focus:ring-[#166534] transition" />
                        </div>

                        <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100">
                            <button type="button" 
                                    @click="editModalOpen = false" 
                                    class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition">
                                Batal
                            </button>
                            <button type="submit" 
                                    class="px-5 py-2 rounded-xl bg-gradient-to-r from-[#166534] to-[#14532D] text-xs font-bold text-white shadow-sm hover:shadow transition">
                                Perbarui Kategori
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Konfirmasi Hapus Kategori -->
        <div x-show="deleteModalOpen" 
             x-cloak 
             class="fixed inset-0 z-50 overflow-y-auto" 
             role="dialog" 
             aria-modal="true">
            <div class="fixed inset-0 bg-black/60 backdrop-blur-xs transition-opacity" @click="deleteModalOpen = false"></div>
            <div class="flex min-h-full items-center justify-center p-4">
                <div x-show="deleteModalOpen" 
                     x-transition 
                     class="relative w-full max-w-md rounded-3xl bg-white p-6 sm:p-7 shadow-2xl border border-slate-200">
                    <!-- Header: Ikon & Judul Sejajar Rapi -->
                    <div class="flex items-start gap-3.5 mb-4">
                        <div class="w-11 h-11 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center shrink-0 border border-rose-200/70 shadow-xs">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </div>
                        <div class="pt-0.5">
                            <h3 class="text-base font-bold text-slate-900">Hapus Kategori?</h3>
                            <p class="text-xs text-slate-500 mt-0.5">
                                Konfirmasi penghapusan master kategori barang.
                            </p>
                        </div>
                    </div>

                    <!-- Ringkasan Kategori yang Dituju -->
                    <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200/70 flex items-center justify-between gap-3 mb-3">
                        <div class="min-w-0 pr-2">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-0.5">Nama Kategori</span>
                            <p class="text-xs sm:text-sm font-bold text-slate-900 truncate" x-text="deleteData.nama"></p>
                        </div>
                        <div class="text-right shrink-0">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-0.5">Jumlah Barang</span>
                            <span class="inline-block text-[11px] font-bold px-2 py-0.5 rounded-lg border shadow-2xs"
                                  :class="deleteData.count > 0 ? 'bg-amber-50 text-amber-700 border-amber-200' : 'bg-slate-100 text-slate-700 border-slate-200'"
                                  x-text="deleteData.count + ' Barang'"></span>
                        </div>
                    </div>

                    <!-- Keterangan Kondisional -->
                    <template x-if="deleteData.count > 0">
                        <div class="p-3.5 rounded-2xl border text-xs flex items-start gap-2.5 transition mb-5 bg-amber-50/60 border-amber-200/70 text-amber-900">
                            <svg class="w-4 h-4 shrink-0 mt-0.5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/>
                                <line x1="12" y1="16" x2="12" y2="12"/>
                                <line x1="12" y1="8" x2="12.01" y2="8"/>
                            </svg>
                            <div class="leading-relaxed text-[11px]">
                                <strong class="font-bold">Kategori Tidak Dapat Dihapus:</strong> 
                                Kategori ini masih menaungi <span class="font-bold" x-text="deleteData.count"></span> barang. Pindahkan atau hapus tautan kategori pada barang tersebut terlebih dahulu.
                            </div>
                        </div>
                    </template>

                    <template x-if="deleteData.count === 0">
                        <div class="p-3.5 rounded-2xl border text-xs flex items-start gap-2.5 transition mb-5 bg-rose-50/50 border-rose-200/70 text-rose-900">
                            <svg class="w-4 h-4 shrink-0 mt-0.5 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/>
                                <line x1="12" y1="16" x2="12" y2="12"/>
                                <line x1="12" y1="8" x2="12.01" y2="8"/>
                            </svg>
                            <div class="leading-relaxed text-[11px]">
                                <strong class="font-bold">Perhatian:</strong> 
                                Kategori ini kosong dan akan dihapus permanen. Tindakan ini tidak dapat dibatalkan.
                            </div>
                        </div>
                    </template>

                    <!-- Tombol Aksi Rapi & Seimbang -->
                    <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-slate-100">
                        <button type="button" 
                                @click="deleteModalOpen = false" 
                                class="px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-700 hover:bg-slate-50 transition text-center">
                            <span x-text="deleteData.count > 0 ? 'Tutup' : 'Batal'"></span>
                        </button>
                        
                        <form method="POST" :action="deleteData.action" x-show="deleteData.count === 0">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="px-5 py-2.5 rounded-xl text-xs font-bold text-white shadow-xs transition text-center bg-rose-600 hover:bg-rose-700">
                                Ya, Hapus Kategori
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-layouts.admin>
