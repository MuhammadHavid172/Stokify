<x-admin-layout>
    <x-slot name="header">
        <x-page-header 
            title="Log Aktivitas" 
            description="Jejak audit dan rekaman seluruh perubahan data, transaksi, dan aktivitas operasional kios secara transparan.">
        </x-page-header>
    </x-slot>

    <div x-data="{ 
            showModal: false, 
            modalTitle: '', 
            modalUser: '', 
            modalWaktu: '', 
            modalAksi: '',
            dataLama: null, 
            dataBaru: null,

            openDiff(log) {
                this.modalTitle = log.deskripsi;
                this.modalUser = log.user ? log.user.name : 'Sistem';
                this.modalWaktu = log.waktu_formatted;
                this.modalAksi = log.aksi || '';
                this.dataLama = log.data_lama_masked;
                this.dataBaru = log.data_baru_masked;
                this.showModal = true;
            },

            get tipePerubahan() {
                if (!this.dataLama && this.dataBaru) return 'create';
                if (this.dataLama && !this.dataBaru) return 'delete';
                return 'update';
            },

            formatKey(key) {
                const dict = {
                    shift_id: 'ID Shift',
                    modal_awal: 'Modal Awal Kas',
                    waktu_buka: 'Waktu Buka Shift',
                    waktu_tutup: 'Waktu Tutup Shift',
                    uang_fisik: 'Uang Kas Fisik',
                    uang_seharusnya: 'Uang Kas Seharusnya',
                    saldo_akhir_seharusnya: 'Saldo Kas Seharusnya',
                    saldo_akhir_fisik: 'Saldo Kas Fisik',
                    selisih: 'Selisih Kas',
                    ditutup_paksa: 'Status Tutup Shift',
                    total_penjualan: 'Total Penjualan',
                    total_transaksi: 'Jumlah Transaksi',
                    status: 'Status',
                    user_id: 'ID Pengguna',
                    kios_id: 'ID Kios',
                    nama: 'Nama',
                    kode: 'Kode Barang',
                    barcode: 'Barcode',
                    kategori_id: 'Kategori',
                    kategori_nama: 'Kategori',
                    supplier_id: 'Supplier',
                    supplier_nama: 'Supplier',
                    harga_modal: 'Harga Modal',
                    harga_jual: 'Harga Jual',
                    stok: 'Stok Saat Ini',
                    stok_awal: 'Stok Awal',
                    stok_minimum: 'Batas Stok Minimum',
                    satuan: 'Satuan',
                    is_aktif: 'Status Aktif',
                    telepon: 'Nomor Telepon',
                    alamat: 'Alamat',
                    alasan: 'Alasan',
                    catatan: 'Catatan / Keterangan',
                    jumlah: 'Jumlah',
                    arah: 'Arah Penyesuaian',
                    jenis: 'Jenis Penyesuaian',
                    nomor_nota: 'Nomor Nota',
                    total_bayar: 'Total Bayar',
                    uang_diterima: 'Uang Diterima',
                    kembalian: 'Kembalian',
                    metode_pembayaran: 'Metode Pembayaran'
                };
                if (dict[key]) return dict[key];
                return String(key).replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
            },

            formatVal(key, val) {
                if (val === null || val === undefined || val === '') return '-';
                
                // Khusus ditutup paksa
                if (key === 'ditutup_paksa') {
                    return val ? 'Ditutup Paksa (Admin)' : 'Normal (Oleh Kasir)';
                }
                
                if (typeof val === 'boolean') return val ? 'Aktif / Ya' : 'Nonaktif / Tidak';
                
                // Khusus selisih
                if (key === 'selisih' && (typeof val === 'number' || (!isNaN(Number(val)) && typeof val === 'string' && val.trim() !== ''))) {
                    const num = Number(val);
                    if (num > 0) return '+Rp ' + new Intl.NumberFormat('id-ID').format(num) + ' (Lebih)';
                    if (num < 0) return '-Rp ' + new Intl.NumberFormat('id-ID').format(Math.abs(num)) + ' (Kurang)';
                    return 'Rp 0 (Pas / Tepat)';
                }

                const rupiahKeys = [
                    'modal_awal', 'uang_fisik', 'uang_seharusnya', 'harga_modal', 'harga_jual', 
                    'saldo_akhir_seharusnya', 'saldo_akhir_fisik', 'total_penjualan', 
                    'total_bayar', 'uang_diterima', 'kembalian', 'omzet', 'diskon', 'subtotal'
                ];
                if (rupiahKeys.includes(key) && (typeof val === 'number' || (!isNaN(Number(val)) && typeof val === 'string' && val.trim() !== ''))) {
                    return 'Rp ' + new Intl.NumberFormat('id-ID').format(Number(val));
                }

                if (typeof val === 'object') {
                    return Object.entries(val).map(([k, v]) => `${this.formatKey(k)}: ${v}`).join(', ');
                }
                return String(val);
            },

            getAllKeys() {
                const set = new Set();
                if (this.dataLama && typeof this.dataLama === 'object') {
                    Object.keys(this.dataLama).forEach(k => set.add(k));
                }
                if (this.dataBaru && typeof this.dataBaru === 'object') {
                    Object.keys(this.dataBaru).forEach(k => set.add(k));
                }
                return Array.from(set);
            },

            isDiff(key) {
                const v1 = this.dataLama ? this.dataLama[key] : undefined;
                const v2 = this.dataBaru ? this.dataBaru[key] : undefined;
                return JSON.stringify(v1) !== JSON.stringify(v2);
            },

            formatAksi(aksi) {
                if (!aksi) return 'Aktivitas';
                const map = {
                    'shift.buka': 'Buka Shift Kasir',
                    'shift.tutup': 'Tutup Shift Kasir',
                    'shift.paksa_tutup': 'Tutup Paksa Shift',
                    'shift.tutup_paksa': 'Tutup Paksa Shift',
                    'barang.tambah': 'Tambah Barang',
                    'barang.ubah': 'Ubah Barang',
                    'barang.toggle': 'Status Barang',
                    'barang.hapus': 'Hapus Barang',
                    'barang_masuk.catat': 'Catat Barang Masuk',
                    'penyesuaian.catat': 'Penyesuaian Stok',
                    'penjualan.transaksi': 'Transaksi Penjualan',
                    'penjualan.void': 'Pembatalan Nota (Void)',
                    'supplier.tambah': 'Tambah Supplier',
                    'supplier.ubah': 'Ubah Supplier',
                    'supplier.toggle': 'Status Supplier',
                    'kategori.tambah': 'Tambah Kategori',
                    'kategori.ubah': 'Ubah Kategori',
                    'kategori.hapus': 'Hapus Kategori',
                    'kios.ubah': 'Profil Kios'
                };
                if (map[aksi.toLowerCase()]) return map[aksi.toLowerCase()];
                return aksi.replace(/[._]/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
            },

            getBannerInfo() {
                if (this.tipePerubahan === 'delete') {
                    return {
                        title: 'Entitas Telah Dihapus',
                        desc: 'Berikut adalah arsip snapshot data terakhir sebelum data dihapus dari sistem.',
                        color: 'rose'
                    };
                }
                if (this.tipePerubahan === 'update') {
                    return {
                        title: 'Pembaruan Data',
                        desc: 'Perbandingan rincian nilai sebelum dan sesudah perubahan dilakukan.',
                        color: 'amber'
                    };
                }
                // Tipe create
                const aksi = (this.modalAksi || '').toLowerCase();
                if (aksi.startsWith('shift.')) {
                    return {
                        title: 'Rincian Operasional Shift',
                        desc: 'Rekaman rekapitulasi shift kasir telah tersimpan secara resmi di sistem.',
                        color: 'emerald'
                    };
                }
                if (aksi.includes('masuk')) {
                    return {
                        title: 'Rincian Barang Masuk',
                        desc: 'Penerimaan stok barang telah dicatat dan terverifikasi di kartu mutasi stok.',
                        color: 'emerald'
                    };
                }
                if (aksi.includes('penjualan')) {
                    return {
                        title: 'Rincian Transaksi Penjualan',
                        desc: 'Transaksi nota kasir telah selesai dan tercatat resmi di sistem.',
                        color: 'emerald'
                    };
                }
                return {
                    title: 'Rincian Data Baru',
                    desc: 'Aktivitas ini mencatat data baru ke sistem sehingga tidak ada riwayat data sebelumnya.',
                    color: 'emerald'
                };
            }
        }" 
        class="space-y-6">

        <!-- Sub-Navigasi Tab: Pengaturan Kios -->
        <div class="flex items-center gap-2 border-b border-gray-200 pb-3">
            <a href="{{ route('admin.kios.index') }}" 
               class="px-4 py-2 rounded-lg text-xs font-bold transition {{ request()->routeIs('admin.kios.*') ? 'bg-[#166534] text-white shadow-xs' : 'bg-white text-gray-600 hover:text-gray-900 border border-gray-200 hover:bg-gray-50' }}">
                Profil Kios
            </a>
            <a href="{{ route('admin.kasir.index') }}" 
               class="px-4 py-2 rounded-lg text-xs font-bold transition {{ request()->routeIs('admin.kasir.*') ? 'bg-[#166534] text-white shadow-xs' : 'bg-white text-gray-600 hover:text-gray-900 border border-gray-200 hover:bg-gray-50' }}">
                Kelola Kasir
            </a>
            <a href="{{ route('admin.log.index') }}" 
               class="px-4 py-2 rounded-lg text-xs font-bold transition {{ request()->routeIs('admin.log.*') ? 'bg-[#166534] text-white shadow-xs' : 'bg-white text-gray-600 hover:text-gray-900 border border-gray-200 hover:bg-gray-50' }}">
                Log Aktivitas
            </a>
        </div>

        <x-flash-message />

        <!-- Filter Bar -->
        <div class="bg-white border border-gray-200 rounded-xl p-4 sm:p-5 shadow-sm">
            <form method="GET" action="{{ route('admin.log.index') }}" class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3.5">
                    <!-- Tanggal Mulai -->
                    <div>
                        <label for="tanggal_mulai" class="block text-[11px] font-semibold text-gray-700 uppercase tracking-wider mb-1.5">
                            Dari Tanggal
                        </label>
                        <input type="date" 
                               id="tanggal_mulai" 
                               name="tanggal_mulai" 
                               value="{{ $tanggalMulai }}"
                               class="w-full bg-white border border-gray-300 rounded-lg px-3 py-2 text-xs text-gray-900 focus:outline-none focus:border-[#166534] focus:ring-1 focus:ring-[#166534]">
                    </div>

                    <!-- Tanggal Selesai -->
                    <div>
                        <label for="tanggal_selesai" class="block text-[11px] font-semibold text-gray-700 uppercase tracking-wider mb-1.5">
                            Sampai Tanggal
                        </label>
                        <input type="date" 
                               id="tanggal_selesai" 
                               name="tanggal_selesai" 
                               value="{{ $tanggalSelesai }}"
                               class="w-full bg-white border border-gray-300 rounded-lg px-3 py-2 text-xs text-gray-900 focus:outline-none focus:border-[#166534] focus:ring-1 focus:ring-[#166534]">
                    </div>

                    <!-- Pengguna -->
                    <div>
                        <label for="user_id" class="block text-[11px] font-semibold text-gray-700 uppercase tracking-wider mb-1.5">
                            Pengguna
                        </label>
                        <select id="user_id" 
                                name="user_id" 
                                class="w-full bg-white border border-gray-300 rounded-lg px-3 py-2 text-xs text-gray-900 focus:outline-none focus:border-[#166534] focus:ring-1 focus:ring-[#166534]">
                            <option value="">Semua Pengguna</option>
                            @foreach ($users as $u)
                                <option value="{{ $u->id }}" {{ $selectedUserId == $u->id ? 'selected' : '' }}>
                                    {{ $u->name }} ({{ ucfirst($u->role) }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Kategori Aksi -->
                    <div>
                        <label for="kategori_aksi" class="block text-[11px] font-semibold text-gray-700 uppercase tracking-wider mb-1.5">
                            Kelompok Aksi
                        </label>
                        <select id="kategori_aksi" 
                                name="kategori_aksi" 
                                class="w-full bg-white border border-gray-300 rounded-lg px-3 py-2 text-xs text-gray-900 focus:outline-none focus:border-[#166534] focus:ring-1 focus:ring-[#166534]">
                            <option value="">Semua Kelompok</option>
                            @foreach ($kategoriList as $key => $label)
                                <option value="{{ $key }}" {{ $selectedKategori == $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Pencarian Deskripsi -->
                    <div>
                        <label for="q" class="block text-[11px] font-semibold text-gray-700 uppercase tracking-wider mb-1.5">
                            Cari Deskripsi
                        </label>
                        <input type="text" 
                               id="q" 
                               name="q" 
                               value="{{ $searchQuery }}"
                               placeholder="Kata kunci..."
                               class="w-full bg-white border border-gray-300 rounded-lg px-3 py-2 text-xs text-gray-900 placeholder-gray-400 focus:outline-none focus:border-[#166534] focus:ring-1 focus:ring-[#166534]">
                    </div>
                </div>

                <!-- Tombol Filter -->
                <div class="flex items-center justify-end gap-2.5 pt-2 border-t border-gray-100">
                    <a href="{{ route('admin.log.index') }}" 
                       class="px-4 py-2 rounded-lg bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-xs font-semibold transition">
                        Reset Filter
                    </a>
                    <button type="submit" 
                            class="px-4 py-2 rounded-lg bg-[#166534] hover:bg-[#14532D] text-white font-semibold text-xs tracking-wider shadow-sm transition flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                        <span>Terapkan Filter</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Tabel Log Aktivitas -->
        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm">
            @if ($logs->isEmpty())
                <div class="p-8">
                    <x-empty-state 
                        title="Tidak Ada Catatan Log" 
                        description="Belum ada aktivitas yang tercatat untuk kriteria atau rentang waktu yang Anda pilih.">
                    </x-empty-state>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-gray-700">
                        <thead class="bg-gray-50 text-gray-600 uppercase font-bold tracking-wider text-[11px] border-b border-gray-200">
                            <tr>
                                <th scope="col" class="py-3 px-4">Waktu</th>
                                <th scope="col" class="py-3 px-4">Pengguna</th>
                                <th scope="col" class="py-3 px-4">Aksi</th>
                                <th scope="col" class="py-3 px-4">Deskripsi Aktivitas</th>
                                <th scope="col" class="py-3 px-4 text-center">Perubahan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @foreach ($logs as $log)
                                @php
                                    $prefix = explode('.', $log->aksi)[0] ?? 'lainnya';
                                    
                                    // Warna badge berdasarkan kelompok aksi
                                    $badgeColor = match($prefix) {
                                        'barang', 'kategori' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                                        'barang_masuk' => 'bg-blue-50 text-blue-800 border-blue-200',
                                        'penyesuaian' => 'bg-amber-50 text-amber-800 border-amber-200',
                                        'shift' => 'bg-purple-50 text-purple-800 border-purple-200',
                                        'penjualan' => 'bg-lime-50 text-lime-800 border-lime-200',
                                        'laporan' => 'bg-teal-50 text-teal-800 border-teal-200',
                                        'analisis' => 'bg-violet-50 text-violet-800 border-violet-200',
                                        'kios' => 'bg-sky-50 text-sky-800 border-sky-200',
                                        'supplier' => 'bg-indigo-50 text-indigo-800 border-indigo-200',
                                        default => 'bg-gray-100 text-gray-800 border-gray-200'
                                    };

                                    $hasDiff = !empty($log->data_lama_masked) || !empty($log->data_baru_masked);
                                    
                                    $logJson = [
                                        'id' => $log->id,
                                        'aksi' => $log->aksi,
                                        'deskripsi' => $log->deskripsi,
                                        'user' => $log->user ? ['name' => $log->user->name] : null,
                                        'waktu_formatted' => \Carbon\Carbon::parse($log->created_at)->timezone('Asia/Jayapura')->format('d/m/Y H:i:s'),
                                        'data_lama_masked' => $log->data_lama_masked,
                                        'data_baru_masked' => $log->data_baru_masked,
                                    ];
                                @endphp
                                <tr class="hover:bg-gray-50 transition">
                                    <!-- Waktu -->
                                    <td class="py-3 px-4 whitespace-nowrap text-gray-500 font-mono text-[11px]">
                                        {{ \Carbon\Carbon::parse($log->created_at)->timezone('Asia/Jayapura')->format('d/m/Y H:i:s') }}
                                    </td>

                                    <!-- Pengguna -->
                                    <td class="py-3 px-4 whitespace-nowrap">
                                        <div class="flex items-center gap-1.5">
                                            <span class="font-bold text-gray-900">{{ $log->user?->name ?? 'Sistem' }}</span>
                                            @if ($log->user)
                                                <span class="text-[10px] px-1.5 py-0.5 rounded bg-gray-100 text-gray-600 uppercase font-semibold">
                                                    {{ $log->user->role }}
                                                </span>
                                            @endif
                                        </div>
                                    </td>

                                    <!-- Aksi (Badge) -->
                                    <td class="py-3 px-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $badgeColor }}">
                                            {{ $log->aksi }}
                                        </span>
                                    </td>

                                    <!-- Deskripsi -->
                                    <td class="py-3 px-4 text-gray-900 max-w-md">
                                        <p class="truncate" title="{{ $log->deskripsi }}">{{ $log->deskripsi }}</p>
                                    </td>

                                    <!-- Tombol Perubahan -->
                                    <td class="py-3 px-4 text-center whitespace-nowrap">
                                        @if ($hasDiff)
                                            <button type="button" 
                                                    @click="openDiff({{ json_encode($logJson) }})" 
                                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-gray-50 hover:bg-gray-100 text-gray-700 border border-gray-300 text-[11px] font-semibold transition">
                                                <svg class="w-3.5 h-3.5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                                <span>Lihat Perubahan</span>
                                            </button>
                                        @else
                                            <span class="text-gray-400 text-[11px]">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if ($logs->hasPages())
                    <div class="p-4 border-t border-gray-100">
                        {{ $logs->links() }}
                    </div>
                @endif
            @endif
        </div>

        <!-- Modal Diff JSON (Alpine.js) -->
        <div x-show="showModal" 
             x-cloak 
             class="fixed inset-0 z-50 overflow-y-auto" 
             aria-labelledby="modal-title" 
             role="dialog" 
             aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <!-- Overlay Backdrop -->
                <div x-show="showModal" 
                     x-transition:enter="ease-out duration-200"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-150"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     @click="showModal = false" 
                     class="fixed inset-0 bg-gray-900/50 backdrop-blur-xs transition-opacity"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <!-- Modal Panel -->
                <div x-show="showModal" 
                     x-transition:enter="ease-out duration-200"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-150"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="inline-block align-bottom bg-white border border-gray-200 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                    
                    <!-- Modal Header -->
                    <div class="px-6 py-5 border-b border-gray-100 flex items-start justify-between gap-4 bg-white">
                        <div class="space-y-2 flex-1 min-w-0">
                            <!-- Judul Utama (Mulai dari tepi kiri, tidak terdesak badge) -->
                            <h3 class="text-base font-bold text-gray-900 leading-snug break-words" id="modal-title" x-text="modalTitle"></h3>
                            
                            <!-- Metadata: Badge Aksi Bersih & Petugas & Waktu -->
                            <div class="flex flex-wrap items-center gap-2 text-xs text-gray-500">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#DCFCE7] text-[#166534] border border-[#86EFAC]/60" 
                                      x-text="formatAksi(modalAksi)"></span>
                                <span class="text-gray-300">&bull;</span>
                                <span class="text-gray-600">Oleh <strong class="text-gray-900 font-semibold" x-text="modalUser"></strong></span>
                                <span class="text-gray-300">&bull;</span>
                                <span class="text-gray-500 font-mono text-[11px]" x-text="modalWaktu"></span>
                            </div>
                        </div>

                        <!-- Tombol Tutup X -->
                        <button type="button" 
                                @click="showModal = false" 
                                class="p-1.5 rounded-xl text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition shrink-0 mt-0.5"
                                title="Tutup">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Modal Body Content (Murni Tampilan Rapi, Tanpa Kode / JSON Sama Sekali) -->
                    <div class="p-6 space-y-4">
                        
                        <!-- KASUS A: DATA BARU DIBUAT ATAU REKAP SHIFT (Create) -->
                        <template x-if="tipePerubahan === 'create'">
                            <div class="space-y-3">
                                <div class="p-3.5 rounded-xl border flex items-center gap-3"
                                     :class="{
                                         'bg-emerald-50/80 border-emerald-200 text-emerald-950': getBannerInfo().color === 'emerald',
                                         'bg-amber-50/80 border-amber-200 text-amber-950': getBannerInfo().color === 'amber',
                                         'bg-rose-50/80 border-rose-200 text-rose-950': getBannerInfo().color === 'rose'
                                     }">
                                    <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0"
                                         :class="{
                                             'bg-emerald-100 text-[#166534]': getBannerInfo().color === 'emerald',
                                             'bg-amber-100 text-amber-800': getBannerInfo().color === 'amber',
                                             'bg-rose-100 text-rose-700': getBannerInfo().color === 'rose'
                                         }">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold" x-text="getBannerInfo().title"></p>
                                        <p class="text-[11px] opacity-80 mt-0.5" x-text="getBannerInfo().desc"></p>
                                    </div>
                                </div>

                                <div class="bg-gray-50/70 border border-gray-200 rounded-xl overflow-hidden divide-y divide-gray-100">
                                    <template x-for="key in Object.keys(dataBaru || {})" :key="key">
                                        <div class="px-4 py-3 flex items-center justify-between text-xs hover:bg-white transition gap-4">
                                            <span class="font-medium text-gray-600 shrink-0" x-text="formatKey(key)"></span>
                                            <span class="font-bold text-gray-900 text-right font-mono" x-text="formatVal(key, dataBaru[key])"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>

                        <!-- KASUS B: DATA DIHAPUS (Delete) -->
                        <template x-if="tipePerubahan === 'delete'">
                            <div class="space-y-3">
                                <div class="p-3.5 rounded-xl bg-rose-50/80 border border-rose-200 flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-rose-100 text-rose-700 flex items-center justify-center shrink-0">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-rose-900">Entitas Telah Dihapus</p>
                                        <p class="text-[11px] text-rose-700 mt-0.5">Berikut adalah rekaman snapshot data terakhir sebelum data dihapus dari sistem.</p>
                                    </div>
                                </div>

                                <div class="bg-gray-50/70 border border-gray-200 rounded-xl overflow-hidden divide-y divide-gray-200/70">
                                    <template x-for="key in Object.keys(dataLama || {})" :key="key">
                                        <div class="px-4 py-3 flex items-center justify-between text-xs hover:bg-white transition">
                                            <span class="font-medium text-gray-600" x-text="formatKey(key)"></span>
                                            <span class="font-bold text-gray-900 text-right" x-text="formatVal(key, dataLama[key])"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>

                        <!-- KASUS C: PERUBAHAN DATA (Update / Perbandingan Sebelum & Sesudah) -->
                        <template x-if="tipePerubahan === 'update'">
                            <div class="space-y-3">
                                <div class="p-3.5 rounded-xl bg-amber-50/80 border border-amber-200 flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-800 flex items-center justify-center shrink-0">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-amber-900">Pembaruan Data</p>
                                        <p class="text-[11px] text-amber-700 mt-0.5">Perbandingan rincian nilai sebelum dan sesudah perubahan dilakukan.</p>
                                    </div>
                                </div>

                                <div class="border border-gray-200 rounded-xl overflow-hidden shadow-xs">
                                    <table class="w-full text-left text-xs border-collapse">
                                        <thead>
                                            <tr class="bg-gray-100/80 border-b border-gray-200 text-gray-700 font-bold text-[11px] uppercase tracking-wider">
                                                <th class="py-2.5 px-3.5">Kolom</th>
                                                <th class="py-2.5 px-3.5 text-rose-700">Data Sebelumnya</th>
                                                <th class="py-2.5 px-3.5 text-emerald-800">Data Sesudahnya</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100 bg-white text-xs">
                                            <template x-for="key in getAllKeys()" :key="key">
                                                <tr :class="isDiff(key) ? 'bg-amber-50/40' : 'hover:bg-gray-50/50'" class="transition">
                                                    <td class="py-2.5 px-3.5 font-medium text-gray-700">
                                                        <div class="flex items-center gap-1.5">
                                                            <span x-text="formatKey(key)"></span>
                                                            <template x-if="isDiff(key)">
                                                                <span class="text-[9px] font-bold px-1.5 py-0.2 rounded bg-amber-100 text-amber-800 uppercase">Ubah</span>
                                                            </template>
                                                        </div>
                                                    </td>
                                                    <td class="py-2.5 px-3.5 text-gray-500" :class="isDiff(key) ? 'text-rose-700 line-through bg-rose-50/50 font-semibold' : ''">
                                                        <span x-text="formatVal(key, dataLama ? dataLama[key] : null)"></span>
                                                    </td>
                                                    <td class="py-2.5 px-3.5 text-gray-900" :class="isDiff(key) ? 'text-emerald-800 font-bold bg-emerald-50/50' : ''">
                                                        <span x-text="formatVal(key, dataBaru ? dataBaru[key] : null)"></span>
                                                    </td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </template>

                        <!-- Catatan Keamanan Sensor Field Sensitif -->
                        <div class="p-3 rounded-xl bg-gray-50 border border-gray-200/80 flex items-start gap-2.5 text-[11px] text-gray-600">
                            <div class="w-5 h-5 rounded-md bg-emerald-100 text-[#166534] flex items-center justify-center shrink-0 mt-0.5">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                            <span>Field sensitif seperti password, pin, atau token keamanan otomatis disensor secara ketat (<code class="font-mono text-emerald-800 font-bold bg-emerald-50 px-1 rounded">********</code>) demi kepatuhan perlindungan data.</span>
                        </div>

                    </div>

                    <!-- Modal Footer -->
                    <div class="px-6 py-3.5 bg-gray-50/80 border-t border-gray-100 flex justify-end">
                        <button type="button" 
                                @click="showModal = false" 
                                class="px-5 py-2 rounded-xl bg-white border border-gray-300 hover:bg-gray-50 text-xs font-semibold text-gray-700 shadow-xs transition">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-admin-layout>
