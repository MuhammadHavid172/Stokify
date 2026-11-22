<x-layouts.admin title="Kelola Supplier">
    <div class="space-y-6" x-data="{ 
        createModalOpen: false, 
        editModalOpen: false, 
        toggleModalOpen: false,
        editData: { id: '', nama: '', no_telepon: '', alamat: '', action: '' },
        toggleData: { id: '', nama: '', is_aktif: true, count: 0, action: '' }
    }">
        
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
                    <a href="{{ route('admin.barang-masuk.index') }}" class="hover:text-slate-800 transition">Barang Masuk</a>
                    <span>/</span>
                    <span class="text-slate-800">Supplier</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Data Supplier</h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">
                    Kelola data agen, grosir, atau pemasok barang dagangan kios Anda.
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

                <button type="button" 
                        @click="createModalOpen = true"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-[#166534] to-[#14532D] hover:from-[#14532D] hover:to-[#14532D] text-white font-bold text-xs shadow-md shadow-[#166534]/20 transition transform hover:-translate-y-0.5 active:translate-y-0">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <line x1="12" y1="5" x2="12" y2="19" />
                        <line x1="5" y1="12" x2="19" y2="12" />
                    </svg>
                    <span>Tambah Supplier</span>
                </button>
            </div>
        </div>

        <!-- Filter & Search Bar -->
        <div class="bg-slate-50/80 border border-slate-200/80 rounded-2xl p-4 flex flex-col sm:flex-row items-center justify-between gap-3 shadow-xs">
            <form method="GET" action="{{ route('admin.supplier.index') }}" class="w-full sm:w-80 relative">
                <input type="text" 
                       name="q" 
                       value="{{ $search }}" 
                       placeholder="Cari nama atau telepon supplier..." 
                       class="w-full rounded-xl border border-slate-200 bg-white pl-9 pr-4 py-2 text-xs text-slate-800 placeholder-slate-400 focus:border-[#166534] focus:ring-1 focus:ring-[#166534] transition" />
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8" />
                        <line x1="21" y1="21" x2="16.65" y2="16.65" />
                    </svg>
                </div>
            </form>

            <div class="flex items-center gap-3 w-full sm:w-auto justify-end">
                <select name="status" 
                        onchange="location.href='{{ route('admin.supplier.index') }}?q={{ urlencode($search) }}&status=' + this.value"
                        class="rounded-xl border border-slate-200 bg-white pl-3.5 pr-9 py-2 text-xs text-slate-700 focus:border-[#166534] focus:ring-1 focus:ring-[#166534] shadow-2xs cursor-pointer">
                    <option value="">Semua Status</option>
                    <option value="aktif" {{ $status === 'aktif' ? 'selected' : '' }}>Aktif Saja</option>
                    <option value="nonaktif" {{ $status === 'nonaktif' ? 'selected' : '' }}>Nonaktif Saja</option>
                </select>

                <span class="text-xs font-semibold text-slate-500 shrink-0">
                    Total: <strong class="text-slate-800">{{ $supplierList->total() }}</strong> supplier
                </span>
            </div>
        </div>

        <!-- Tabel Daftar Supplier -->
        @if ($supplierList->count() > 0)
            <div class="bg-white border border-slate-200/80 rounded-2xl shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-600">
                        <thead class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200/80">
                            <tr>
                                <th class="px-5 py-3.5">Nama Supplier</th>
                                <th class="px-5 py-3.5">No. Telepon / WA</th>
                                <th class="px-5 py-3.5">Alamat</th>
                                <th class="px-5 py-3.5 text-center">Riwayat Barang Masuk</th>
                                <th class="px-5 py-3.5 text-center">Status</th>
                                <th class="px-5 py-3.5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($supplierList as $supplier)
                                <tr class="hover:bg-slate-50/80 transition {{ ! $supplier->is_aktif ? 'bg-slate-50/40 opacity-75' : '' }}">
                                    <!-- Nama Supplier -->
                                    <td class="px-5 py-3.5 font-bold text-slate-900">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-8 h-8 rounded-lg bg-[#DCFCE7]/40 text-[#166534] flex items-center justify-center shrink-0 border border-[#6B7280]/30/50">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                                </svg>
                                            </div>
                                            <span>{{ $supplier->nama }}</span>
                                        </div>
                                    </td>

                                    <!-- Nomor Telepon -->
                                    <td class="px-5 py-3.5 font-mono text-slate-600">
                                        {{ $supplier->no_telepon ?: '—' }}
                                    </td>

                                    <!-- Alamat -->
                                    <td class="px-5 py-3.5 text-slate-500 max-w-xs truncate" title="{{ $supplier->alamat }}">
                                        {{ $supplier->alamat ?: '—' }}
                                    </td>

                                    <!-- Riwayat Barang Masuk -->
                                    <td class="px-5 py-3.5 text-center font-semibold">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold {{ $supplier->barang_masuk_count > 0 ? 'bg-slate-100 text-slate-800' : 'bg-slate-50 text-slate-400' }}">
                                            {{ $supplier->barang_masuk_count }} nota
                                        </span>
                                    </td>

                                    <!-- Status -->
                                    <td class="px-5 py-3.5 text-center">
                                        <x-badge :variant="$supplier->is_aktif ? 'green' : 'red'" size="sm">
                                            {{ $supplier->is_aktif ? 'Aktif' : 'Nonaktif' }}
                                        </x-badge>
                                    </td>

                                    <!-- Aksi -->
                                    <td class="px-5 py-3.5 text-right">
                                        <div class="inline-flex items-center gap-1.5">
                                            <!-- Tombol Ubah -->
                                            <button type="button" 
                                                    @click="editData = { 
                                                        id: {{ $supplier->id }}, 
                                                        nama: '{{ addslashes($supplier->nama) }}', 
                                                        no_telepon: '{{ addslashes($supplier->no_telepon ?? '') }}', 
                                                        alamat: '{{ addslashes($supplier->alamat ?? '') }}', 
                                                        action: '{{ route('admin.supplier.update', $supplier) }}' 
                                                    }; editModalOpen = true"
                                                    class="p-1.5 rounded-lg border border-slate-200 text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition" 
                                                    title="Ubah Supplier">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </button>

                                            <!-- Tombol Toggle Aktif/Nonaktif -->
                                            <button type="button" 
                                                    @click="toggleData = { 
                                                        id: {{ $supplier->id }}, 
                                                        nama: '{{ addslashes($supplier->nama) }}', 
                                                        is_aktif: {{ $supplier->is_aktif ? 'true' : 'false' }}, 
                                                        count: {{ $supplier->barang_masuk_count }}, 
                                                        action: '{{ route('admin.supplier.toggle', $supplier) }}' 
                                                    }; toggleModalOpen = true"
                                                    class="p-1.5 rounded-lg border border-slate-200 text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition" 
                                                    title="{{ $supplier->is_aktif ? 'Nonaktifkan Supplier' : 'Aktifkan Supplier' }}">
                                                <svg class="w-4 h-4 {{ $supplier->is_aktif ? 'text-amber-600' : 'text-emerald-600' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    @if ($supplier->is_aktif)
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

                <!-- Pagination -->
                @if ($supplierList->hasPages())
                    <div class="px-5 py-4 border-t border-slate-100 bg-slate-50/50">
                        {{ $supplierList->links() }}
                    </div>
                @endif
            </div>
        @else
            <x-empty-state 
                title="Belum Ada Supplier" 
                description="{{ $search || $status ? 'Tidak ditemukan supplier yang sesuai dengan pencarian Anda.' : 'Daftarkan agen atau grosir tempat Anda biasa belanja stok barang dagangan.' }}"
                buttonText="{{ $search || $status ? 'Reset Filter' : 'Tambah Supplier Baru' }}"
                buttonAction="{{ $search || $status ? route('admin.supplier.index') : '#' }}"
                @click="!{{ $search || $status ? 'true' : 'false' }} && (createModalOpen = true)"
            />
        @endif

        <!-- Modal Tambah Supplier -->
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
                        <h3 class="text-lg font-bold text-slate-900">Tambah Supplier Baru</h3>
                        <button @click="createModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('admin.supplier.store') }}" class="mt-4 space-y-4">
                        @csrf
                        <div>
                            <label for="nama_tambah" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Nama Supplier / Toko Grosir <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" 
                                   id="nama_tambah" 
                                   name="nama" 
                                   required 
                                   autofocus 
                                   placeholder="Nama perusahaan atau agen supplier" 
                                   class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs sm:text-sm text-slate-800 focus:border-[#166534] focus:ring-1 focus:ring-[#166534] transition" />
                        </div>

                        <div>
                            <label for="no_telepon_tambah" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Nomor Telepon / WA (Opsional)
                            </label>
                            <input type="text" 
                                   id="no_telepon_tambah" 
                                   name="no_telepon" 
                                   placeholder="Nomor telepon atau WhatsApp supplier" 
                                   class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs sm:text-sm text-slate-800 focus:border-[#166534] focus:ring-1 focus:ring-[#166534] transition" />
                        </div>

                        <div>
                            <label for="alamat_tambah" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Alamat Gudang / Toko (Opsional)
                            </label>
                            <textarea id="alamat_tambah" 
                                      name="alamat" 
                                      rows="2" 
                                      placeholder="Alamat kantor atau gudang supplier" 
                                      class="w-full rounded-xl border border-slate-200 px-3.5 py-2 text-xs sm:text-sm text-slate-800 focus:border-[#166534] focus:ring-1 focus:ring-[#166534] transition"></textarea>
                        </div>

                        <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100">
                            <button type="button" 
                                    @click="createModalOpen = false" 
                                    class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition">
                                Batal
                            </button>
                            <button type="submit" 
                                    class="px-5 py-2 rounded-xl bg-gradient-to-r from-[#166534] to-[#14532D] text-xs font-bold text-white shadow-sm hover:shadow transition">
                                Simpan Supplier
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Ubah Supplier -->
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
                        <h3 class="text-lg font-bold text-slate-900">Ubah Data Supplier</h3>
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
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Nama Supplier <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" 
                                   name="nama" 
                                   x-model="editData.nama" 
                                   required 
                                   class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs sm:text-sm text-slate-800 focus:border-[#166534] focus:ring-1 focus:ring-[#166534] transition" />
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Nomor Telepon / WA (Opsional)
                            </label>
                            <input type="text" 
                                   name="no_telepon" 
                                   x-model="editData.no_telepon" 
                                   class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs sm:text-sm text-slate-800 focus:border-[#166534] focus:ring-1 focus:ring-[#166534] transition" />
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Alamat (Opsional)
                            </label>
                            <textarea name="alamat" 
                                      x-model="editData.alamat" 
                                      rows="2" 
                                      class="w-full rounded-xl border border-slate-200 px-3.5 py-2 text-xs sm:text-sm text-slate-800 focus:border-[#166534] focus:ring-1 focus:ring-[#166534] transition"></textarea>
                        </div>

                        <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100">
                            <button type="button" 
                                    @click="editModalOpen = false" 
                                    class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition">
                                Batal
                            </button>
                            <button type="submit" 
                                    class="px-5 py-2 rounded-xl bg-gradient-to-r from-[#166534] to-[#14532D] text-xs font-bold text-white shadow-sm hover:shadow transition">
                                Perbarui Supplier
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Konfirmasi Toggle Supplier (Aktif / Nonaktifkan) -->
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
                                x-text="toggleData.is_aktif ? 'Nonaktifkan Supplier?' : 'Aktifkan Kembali Supplier?'">
                            </h3>
                            <p class="text-xs text-slate-500 mt-0.5">
                                Konfirmasi perubahan status rekanan supplier kios.
                            </p>
                        </div>
                    </div>

                    <!-- Ringkasan Supplier yang Dituju -->
                    <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200/70 flex items-center justify-between gap-3 mb-3">
                        <div class="min-w-0 pr-2">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-0.5">Nama Supplier</span>
                            <p class="text-xs sm:text-sm font-bold text-slate-900 truncate" x-text="toggleData.nama"></p>
                        </div>
                        <div class="text-right shrink-0">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-0.5">Status Target</span>
                            <span class="inline-block text-[11px] font-bold px-2 py-0.5 rounded-lg border shadow-2xs"
                                  :class="toggleData.is_aktif ? 'bg-amber-50 text-amber-700 border-amber-200' : 'bg-emerald-50 text-emerald-700 border-emerald-200'"
                                  x-text="toggleData.is_aktif ? 'Akan Dinonaktifkan' : 'Akan Diaktifkan'"></span>
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
                            <span x-show="toggleData.is_aktif">Supplier dinonaktifkan, bukan dihapus, agar riwayat barang masuk tetap utuh. Supplier nonaktif tidak akan muncul di form barang masuk baru.</span>
                            <span x-show="!toggleData.is_aktif">Supplier akan kembali aktif dan dapat dipilih kembali saat mencatat barang masuk baru.</span>
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
