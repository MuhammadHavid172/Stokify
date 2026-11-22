<x-layouts.admin title="Catat Barang Masuk">
    <div class="space-y-6" x-data="{
        tanggal: '{{ old('tanggal', date('Y-m-d')) }}',
        supplier_id: '{{ old('supplier_id', '') }}',
        catatan: '{{ old('catatan', '') }}',
        submitting: false,
        barangCatalog: {{ Js::from($barangList) }},
        items: [],
        
        init() {
            // Jika ada old items dari validasi gagal
            const oldItems = {{ Js::from(old('items', [])) }};
            const prefillItems = {{ Js::from(session('prefill_barang_masuk', [])) }};
            if (oldItems && oldItems.length > 0) {
                this.items = oldItems.map(item => {
                    const found = this.barangCatalog.find(b => b.id == item.barang_id);
                    return {
                        barang_id: item.barang_id,
                        nama: found ? found.nama : '',
                        kode: found ? found.kode : '',
                        satuan: found ? found.satuan : 'pcs',
                        stok_sekarang: found ? found.stok : 0,
                        modal_sekarang: found ? found.harga_modal : 0,
                        jumlah: Number(item.jumlah) || 1,
                        harga_beli: Number(item.harga_beli) || 0,
                    };
                });
            } else if (prefillItems && prefillItems.length > 0) {
                this.items = prefillItems.map(item => {
                    const found = this.barangCatalog.find(b => b.id == item.barang_id);
                    return {
                        barang_id: item.barang_id,
                        nama: found ? found.nama : item.nama,
                        kode: found ? found.kode : item.kode,
                        satuan: found ? found.satuan : item.satuan,
                        stok_sekarang: found ? found.stok : item.stok_sekarang,
                        modal_sekarang: found ? found.harga_modal : item.modal_sekarang,
                        jumlah: Number(item.jumlah) || 1,
                        harga_beli: Number(item.harga_beli ?? (found ? found.harga_modal : 0)),
                    };
                });
            } else {
                // Tambahkan 1 baris kosong awal
                this.addRow();
            }
        },

        addRow() {
            this.items.push({
                barang_id: '',
                nama: '',
                kode: '',
                satuan: 'pcs',
                stok_sekarang: 0,
                modal_sekarang: 0,
                jumlah: 1,
                harga_beli: 0,
            });
        },

        removeRow(index) {
            if (this.items.length > 1) {
                this.items.splice(index, 1);
            } else {
                alert('Transaksi barang masuk minimal harus memiliki 1 barang.');
            }
        },

        onBarangChange(index) {
            const row = this.items[index];
            if (!row.barang_id) {
                row.nama = '';
                row.kode = '';
                row.satuan = 'pcs';
                row.stok_sekarang = 0;
                row.modal_sekarang = 0;
                row.harga_beli = 0;
                return;
            }

            // Periksa apakah barang sudah ada di baris lain
            const duplicateIndex = this.items.findIndex((item, i) => i !== index && item.barang_id == row.barang_id);
            if (duplicateIndex !== -1) {
                alert('Barang ini sudah ada pada daftar di atas. Silakan ubah kuantitas pada baris tersebut.');
                row.barang_id = '';
                return;
            }

            const found = this.barangCatalog.find(b => b.id == row.barang_id);
            if (found) {
                row.nama = found.nama;
                row.kode = found.kode;
                row.satuan = found.satuan;
                row.stok_sekarang = found.stok;
                row.modal_sekarang = found.harga_modal;
                row.harga_beli = found.harga_modal; // Otomatis terisi harga modal terkini
            }
        },

        calculateSubtotal(row) {
            return (Number(row.jumlah) || 0) * (Number(row.harga_beli) || 0);
        },

        get grandTotal() {
            return this.items.reduce((total, row) => total + this.calculateSubtotal(row), 0);
        },

        formatRupiah(num) {
            return 'Rp ' + Number(num || 0).toLocaleString('id-ID');
        },

        submitForm() {
            // Validasi client-side
            const validItems = this.items.filter(item => item.barang_id && item.jumlah > 0);
            if (validItems.length === 0) {
                alert('Pilih minimal satu barang dengan jumlah lebih dari 0.');
                return;
            }
            this.submitting = true;
            this.$refs.form.submit();
        }
    }">
        
        <!-- Header Halaman -->
        <div class="flex items-center justify-between pb-2 border-b border-slate-100">
            <div>
                <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 mb-1">
                    <a href="{{ route('admin.dashboard') }}" class="hover:text-slate-800 transition">Dashboard</a>
                    <span>/</span>
                    <a href="{{ route('admin.barang-masuk.index') }}" class="hover:text-slate-800 transition">Barang Masuk</a>
                    <span>/</span>
                    <span class="text-slate-800">Catat Barang Masuk</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Catat Barang Masuk</h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">
                    Input faktur pembelian barang masuk. Stok barang akan bertambah dan harga modal disesuaikan otomatis dengan metode rata-rata tertimbang.
                </p>
            </div>

            <a href="{{ route('admin.barang-masuk.index') }}" 
               class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs shadow-xs transition">
                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Batal &amp; Kembali</span>
            </a>
        </div>

        <!-- Form Nota Barang Masuk -->
        <form x-ref="form" method="POST" action="{{ route('admin.barang-masuk.store') }}" @submit.prevent="submitForm" class="space-y-6">
            @csrf

            <!-- Section 1: Informasi Header Nota -->
            <div class="bg-white border border-slate-200/80 rounded-3xl p-5 sm:p-7 shadow-xs">
                <h3 class="text-sm font-bold text-slate-900 pb-3 border-b border-slate-100 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-[#DCFCE7]/40 text-[#166534] flex items-center justify-center text-xs font-black">1</span>
                    <span>Informasi Faktur &amp; Asal Barang</span>
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-5 mt-4">
                    <!-- Tanggal Barang Masuk -->
                    <div>
                        <label for="tanggal" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Tanggal Barang Masuk <span class="text-rose-500">*</span>
                        </label>
                        <input type="date" 
                               id="tanggal" 
                               name="tanggal" 
                               x-model="tanggal"
                               max="{{ date('Y-m-d') }}"
                               required 
                               class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs sm:text-sm text-slate-800 focus:border-[#166534] focus:ring-1 focus:ring-[#166534] transition" />
                        <p class="text-[11px] text-slate-400 mt-1">Tidak boleh lebih dari hari ini.</p>
                        @error('tanggal')
                            <p class="text-[11px] font-semibold text-rose-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Supplier (Opsional) -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="supplier_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                                Supplier (Opsional)
                            </label>
                            <a href="{{ route('admin.supplier.index') }}" target="_blank" class="text-[11px] font-semibold text-[#166534] hover:underline">
                                + Kelola Supplier
                            </a>
                        </div>
                        <select id="supplier_id" 
                                name="supplier_id" 
                                x-model="supplier_id"
                                class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs sm:text-sm text-slate-800 focus:border-[#166534] focus:ring-1 focus:ring-[#166534] transition">
                            <option value="">-- Beli Langsung / Pasar (Tanpa Supplier) --</option>
                            @foreach ($supplierList as $sup)
                                <option value="{{ $sup->id }}">{{ $sup->nama }}</option>
                            @endforeach
                        </select>
                        <p class="text-[11px] text-slate-400 mt-1">Pilih supplier agen atau kosongkan jika beli di pasar bebas.</p>
                        @error('supplier_id')
                            <p class="text-[11px] font-semibold text-rose-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Catatan Tambahan -->
                    <div>
                        <label for="catatan" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Catatan / No. Bukti Fisik (Opsional)
                        </label>
                        <input type="text" 
                               id="catatan" 
                               name="catatan" 
                               x-model="catatan"
                               placeholder="Nomor faktur atau nota dari supplier" 
                               class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs sm:text-sm text-slate-800 focus:border-[#166534] focus:ring-1 focus:ring-[#166534] transition" />
                        <p class="text-[11px] text-slate-400 mt-1">Catatan referensi tambahan untuk audit.</p>
                        @error('catatan')
                            <p class="text-[11px] font-semibold text-rose-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Section 2: Daftar Rincian Barang Masuk -->
            <div class="bg-white border border-slate-200/80 rounded-3xl p-5 sm:p-7 shadow-xs">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-[#DCFCE7]/40 text-[#166534] flex items-center justify-center text-xs font-black">2</span>
                        <span>Daftar Barang yang Dibeli / Masuk</span>
                    </h3>

                    <button type="button" 
                            @click="addRow" 
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-200 bg-slate-50 hover:bg-slate-100 text-slate-700 font-bold text-xs transition">
                        <svg class="w-3.5 h-3.5 text-[#166534]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <line x1="12" y1="5" x2="12" y2="19" />
                            <line x1="5" y1="12" x2="19" y2="12" />
                        </svg>
                        <span>Tambah Baris</span>
                    </button>
                </div>

                <!-- Tabel Item Dinamis -->
                <div class="mt-4 space-y-3">
                    <template x-for="(row, index) in items" :key="index">
                        <div class="p-4 rounded-2xl border border-slate-200 bg-slate-50/50 space-y-3 transition hover:border-slate-300">
                            <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
                                <!-- Pilih Barang (Col 5) -->
                                <div class="sm:col-span-5">
                                    <div class="flex items-center justify-between mb-1">
                                        <label class="block text-[11px] font-bold text-slate-600 uppercase">
                                            Pilih Barang Dagangan <span class="text-rose-500">*</span>
                                        </label>
                                        <template x-if="row.barang_id">
                                            <span class="text-[10px] text-slate-500">
                                                Stok saat ini: <strong class="text-slate-800" x-text="row.stok_sekarang + ' ' + row.satuan"></strong>
                                            </span>
                                        </template>
                                    </div>

                                    <select :name="'items[' + index + '][barang_id]'" 
                                            x-model="row.barang_id" 
                                            @change="onBarangChange(index)"
                                            required 
                                            class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs sm:text-sm text-slate-800 focus:border-[#166534] focus:ring-1 focus:ring-[#166534]">
                                        <option value="">-- Pilih Barang --</option>
                                        <template x-for="b in barangCatalog" :key="b.id">
                                            <option :value="b.id" x-text="b.kode + ' - ' + b.nama + ' (' + b.satuan + ')'"></option>
                                        </template>
                                    </select>
                                </div>

                                <!-- Jumlah Masuk (Col 2) -->
                                <div class="sm:col-span-2">
                                    <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">
                                        Jumlah <span class="text-rose-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <input type="number" 
                                               :name="'items[' + index + '][jumlah]'" 
                                               x-model.number="row.jumlah" 
                                               min="1" 
                                               required 
                                               class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 pr-10 text-xs sm:text-sm font-black text-slate-900 focus:border-[#166534] focus:ring-1 focus:ring-[#166534]" />
                                        <span class="absolute inset-y-0 right-0 pr-3 flex items-center text-[11px] font-semibold text-slate-400 pointer-events-none" x-text="row.satuan"></span>
                                    </div>
                                </div>

                                <!-- Harga Beli Satuan (Col 2) -->
                                <div class="sm:col-span-2">
                                    <div class="flex items-center justify-between mb-1">
                                        <label class="block text-[11px] font-bold text-slate-600 uppercase">
                                            Harga Beli (Rp) <span class="text-rose-500">*</span>
                                        </label>
                                    </div>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-xs font-bold text-slate-400">Rp</span>
                                        <input type="number" 
                                               :name="'items[' + index + '][harga_beli]'" 
                                               x-model.number="row.harga_beli" 
                                               min="0" 
                                               required 
                                               class="w-full rounded-xl border border-slate-200 bg-white pl-8 pr-3 py-2 text-xs sm:text-sm font-semibold text-slate-900 focus:border-[#166534] focus:ring-1 focus:ring-[#166534]" />
                                    </div>
                                </div>

                                <!-- Subtotal (Col 2) -->
                                <div class="sm:col-span-2">
                                    <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">
                                        Subtotal
                                    </label>
                                    <div class="rounded-xl border border-slate-200 bg-slate-100/70 px-3 py-2 text-xs sm:text-sm font-black text-slate-900 truncate">
                                        <span x-text="formatRupiah(calculateSubtotal(row))"></span>
                                    </div>
                                </div>

                                <!-- Tombol Hapus Baris (Col 1) -->
                                <div class="sm:col-span-1 flex justify-end pb-1">
                                    <button type="button" 
                                            @click="removeRow(index)" 
                                            class="p-2 rounded-xl text-rose-500 hover:text-rose-700 hover:bg-rose-50 transition border border-rose-200/50" 
                                            title="Hapus baris ini">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Keterangan Estimasi Harga Modal Rata-Rata -->
                            <template x-if="row.barang_id && row.jumlah > 0">
                                <div class="pt-2 border-t border-slate-200/60 flex items-center justify-between text-[11px] text-slate-500">
                                    <span>
                                        Modal Saat Ini: <strong class="text-slate-700" x-text="formatRupiah(row.modal_sekarang)"></strong> / <span x-text="row.satuan"></span>
                                    </span>
                                    <span class="text-slate-600">
                                        Perkiraan modal baru rata-rata tertimbang: 
                                        <strong class="text-[#166534]" 
                                                x-text="formatRupiah(Math.round(((Math.max(0, row.stok_sekarang) * row.modal_sekarang) + (row.jumlah * row.harga_beli)) / (Math.max(0, row.stok_sekarang) + row.jumlah)))"></strong>
                                    </span>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>

                <!-- Total & Tombol Submit -->
                <div class="mt-6 pt-5 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="text-left w-full sm:w-auto">
                        <span class="text-xs font-semibold text-slate-500 block">Total Nilai Pembelian</span>
                        <span class="text-2xl sm:text-3xl font-black text-slate-900" x-text="formatRupiah(grandTotal)"></span>
                    </div>

                    <div class="flex items-center gap-3 w-full sm:w-auto justify-end">
                        <a href="{{ route('admin.barang-masuk.index') }}" 
                           class="px-5 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition">
                            Batal
                        </a>

                        <button type="submit" 
                                :disabled="submitting || grandTotal <= 0"
                                class="px-7 py-3 rounded-xl bg-gradient-to-r from-[#166534] to-[#14532D] hover:from-[#14532D] hover:to-[#14532D] text-xs font-bold text-white shadow-md shadow-[#166534]/20 transition transform hover:-translate-y-0.5 active:translate-y-0 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                            <svg x-show="submitting" x-cloak class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            <span x-text="submitting ? 'Menyimpan &amp; Memperbarui Stok...' : 'Simpan Barang Masuk'"></span>
                        </button>
                    </div>
                </div>
            </div>
        </form>

    </div>
</x-layouts.admin>
