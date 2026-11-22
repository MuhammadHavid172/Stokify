<x-layouts.admin title="Catat Penyesuaian Stok Manual">
    <div class="space-y-6" x-data="penyesuaianForm()">
        
        <!-- Header Halaman -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-slate-100">
            <div>
                <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 mb-1">
                    <a href="{{ route('admin.dashboard') }}" class="hover:text-slate-800 transition">Dashboard</a>
                    <span>/</span>
                    <a href="{{ route('admin.penyesuaian.index') }}" class="hover:text-slate-800 transition">Penyesuaian Stok</a>
                    <span>/</span>
                    <span class="text-slate-800">Catat Manual</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Catat Penyesuaian Stok</h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">
                    Catat barang rusak, hilang, kedaluwarsa, atau koreksi selisih stok fisik manual.
                </p>
            </div>

            <div class="flex items-center gap-2.5 shrink-0">
                <a href="{{ route('admin.penyesuaian.index') }}" 
                   class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs shadow-xs transition">
                    &larr; Kembali
                </a>
            </div>
        </div>

        <!-- Form Penyesuaian -->
        <form method="POST" action="{{ route('admin.penyesuaian.store') }}" @submit="handleSubmit($event)">
            @csrf

            <div class="space-y-6">
                <!-- Bagian 1: Header Nota (Tanggal & Catatan) -->
                <div class="bg-white rounded-2xl border border-slate-200/80 p-5 sm:p-6 shadow-xs">
                    <h3 class="text-sm font-bold text-slate-900 mb-4 pb-2 border-b border-slate-100 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-[#166534]"></span>
                        <span>Informasi Penyesuaian</span>
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Tanggal -->
                        <div>
                            <label for="tanggal" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Tanggal Penyesuaian <span class="text-rose-500">*</span>
                            </label>
                            <input type="date" 
                                   id="tanggal" 
                                   name="tanggal" 
                                   value="{{ old('tanggal', date('Y-m-d')) }}" 
                                   max="{{ date('Y-m-d') }}"
                                   required 
                                   class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3.5 py-2 text-xs sm:text-sm text-slate-800 focus:bg-white focus:border-[#166534] focus:ring-1 focus:ring-[#166534] transition" />
                            @error('tanggal')
                                <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Catatan -->
                        <div>
                            <label for="catatan" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Catatan / Keterangan Tambahan <span class="text-slate-400 font-normal">(Opsional)</span>
                            </label>
                            <input type="text" 
                                   id="catatan" 
                                   name="catatan" 
                                   value="{{ old('catatan') }}" 
                                   placeholder="Tuliskan catatan atau keterangan penyesuaian..." 
                                   class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3.5 py-2 text-xs sm:text-sm text-slate-800 focus:bg-white focus:border-[#166534] focus:ring-1 focus:ring-[#166534] transition" />
                            @error('catatan')
                                <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Bagian 2: Daftar Barang Penyesuaian Dinamis -->
                <div class="bg-white rounded-2xl border border-slate-200/80 p-5 sm:p-6 shadow-xs space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 pb-3 border-b border-slate-100">
                        <div>
                            <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-[#166534]"></span>
                                <span>Rincian Barang yang Disesuaikan</span>
                            </h3>
                            <p class="text-[11px] text-slate-500 mt-0.5">
                                Barang aktif maupun nonaktif dapat disesuaikan. Barang rusak/hilang/kedaluwarsa otomatis mengurangi stok.
                            </p>
                        </div>

                        <button type="button" 
                                @click="tambahBaris()" 
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-[#DCFCE7]/40 text-[#166534] hover:bg-[#DCFCE7]/50 font-bold text-xs transition shrink-0">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <line x1="12" y1="5" x2="12" y2="19" />
                                <line x1="5" y1="12" x2="19" y2="12" />
                            </svg>
                            <span>Tambah Baris Barang</span>
                        </button>
                    </div>

                    @error('items')
                        <div class="p-3 rounded-xl bg-rose-50 text-rose-700 text-xs font-semibold">
                            {{ $message }}
                        </div>
                    @enderror

                    <!-- Daftar Baris Barang -->
                    <div class="space-y-3">
                        <template x-for="(row, index) in items" :key="row.uid">
                            <div class="p-4 rounded-xl border transition space-y-3"
                                 :class="row.stokSesudah < 0 ? 'bg-rose-50/50 border-rose-300' : 'bg-slate-50/60 border-slate-200/80'">
                                
                                <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-start">
                                    <!-- 1. Pilih Barang (md:col-span-4) -->
                                    <div class="md:col-span-4">
                                        <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1">
                                            Pilih Barang <span class="text-rose-500">*</span>
                                        </label>
                                        <select :name="'items[' + index + '][barang_id]'" 
                                                x-model="row.barang_id" 
                                                @change="onBarangChange(row)"
                                                required
                                                class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-[#166534] focus:ring-1 focus:ring-[#166534] transition">
                                            <option value="">-- Pilih Barang Toko --</option>
                                            <template x-for="b in daftarBarang" :key="b.id">
                                                <option :value="b.id" 
                                                        :disabled="isBarangSelectedInOtherRow(b.id, row.uid)"
                                                        x-text="b.kode + ' - ' + b.nama + (b.is_aktif ? '' : ' (Nonaktif)') + ' [Stok: ' + b.stok + ' ' + b.satuan + ']'">
                                                </option>
                                            </template>
                                        </select>

                                        <!-- Info Stok & Modal Terkini -->
                                        <div x-show="row.barang" class="mt-1 flex items-center gap-2 text-[11px] text-slate-500">
                                            <span>Stok: <strong class="text-slate-800" x-text="row.barang ? (row.barang.stok + ' ' + row.barang.satuan) : '-'"></strong></span>
                                            <span>&bull;</span>
                                            <span>Modal: <strong class="text-slate-800" x-text="formatRupiah(row.barang ? row.barang.harga_modal : 0)"></strong></span>
                                        </div>
                                    </div>

                                    <!-- 2. Jenis Penyesuaian (md:col-span-2) -->
                                    <div class="md:col-span-2">
                                        <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1">
                                            Jenis <span class="text-rose-500">*</span>
                                        </label>
                                        <select :name="'items[' + index + '][jenis]'" 
                                                x-model="row.jenis" 
                                                @change="onJenisChange(row)"
                                                required
                                                class="w-full rounded-xl border border-slate-200 bg-white px-2.5 py-2 text-xs text-slate-800 focus:border-[#166534] focus:ring-1 focus:ring-[#166534] transition font-medium">
                                            <option value="rusak">Barang Rusak</option>
                                            <option value="hilang">Barang Hilang</option>
                                            <option value="kedaluwarsa">Kedaluwarsa</option>
                                            <option value="koreksi_lain">Koreksi Lainnya</option>
                                        </select>
                                    </div>

                                    <!-- 3. Arah Penyesuaian (md:col-span-2) -->
                                    <div class="md:col-span-2">
                                        <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1">
                                            Arah Penyesuaian
                                        </label>
                                        <template x-if="row.jenis !== 'koreksi_lain'">
                                            <div class="py-2 px-2.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 font-bold text-[11px] flex items-center justify-between">
                                                <span>Kurang (-)</span>
                                                <input type="hidden" :name="'items[' + index + '][arah]'" value="kurang">
                                            </div>
                                        </template>
                                        <template x-if="row.jenis === 'koreksi_lain'">
                                            <select :name="'items[' + index + '][arah]'" 
                                                    x-model="row.arah" 
                                                    @change="hitungBaris(row)"
                                                    class="w-full rounded-xl border border-slate-200 bg-white px-2.5 py-2 text-xs text-slate-800 focus:border-[#166534] focus:ring-1 focus:ring-[#166534] transition font-bold"
                                                    :class="row.arah === 'tambah' ? 'text-emerald-700' : 'text-rose-700'">
                                                <option value="kurang">Kurangi (-)</option>
                                                <option value="tambah">Tambah (+)</option>
                                            </select>
                                        </template>
                                    </div>

                                    <!-- 4. Jumlah (md:col-span-2) -->
                                    <div class="md:col-span-2">
                                        <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1">
                                            Jumlah <span class="text-rose-500">*</span>
                                        </label>
                                        <input type="number" 
                                               :name="'items[' + index + '][jumlah]'" 
                                               x-model.number="row.jumlah" 
                                               @input="hitungBaris(row)"
                                               min="1" 
                                               required 
                                               class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 font-bold focus:border-[#166534] focus:ring-1 focus:ring-[#166534] transition" />
                                    </div>

                                    <!-- 5. Estimasi Stok Sesudah & Tombol Hapus (md:col-span-2) -->
                                    <div class="md:col-span-2 flex items-center justify-between gap-2 pt-1 sm:pt-0">
                                        <div>
                                            <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                                Stok Sesudah:
                                            </span>
                                            <span class="text-sm font-black"
                                                  :class="row.stokSesudah < 0 ? 'text-rose-600' : 'text-slate-900'"
                                                  x-text="row.barang ? row.stokSesudah : '-'">
                                            </span>
                                        </div>

                                        <button type="button" 
                                                @click="hapusBaris(index)" 
                                                x-show="items.length > 1"
                                                class="p-2 rounded-xl text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition"
                                                title="Hapus baris">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                <!-- Baris Kedua: Alasan Wajib & Nilai Rupiah -->
                                <div class="grid grid-cols-1 md:grid-cols-12 gap-3 pt-2 border-t border-slate-200/60 items-center">
                                    <div class="md:col-span-8">
                                        <input type="text" 
                                               :name="'items[' + index + '][alasan]'" 
                                               x-model="row.alasan" 
                                               required
                                               placeholder="Tuliskan alasan penyesuaian stok barang ini..." 
                                               class="w-full rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-800 placeholder-slate-400 focus:border-[#166534] focus:ring-1 focus:ring-[#166534] transition" />
                                    </div>

                                    <div class="md:col-span-4 text-right text-xs">
                                        <span class="text-slate-500 text-[11px]">Nilai:</span>
                                        <span class="font-bold ml-1" 
                                              :class="row.arah === 'tambah' ? 'text-emerald-600' : 'text-rose-600'" 
                                              x-text="(row.arah === 'tambah' ? '+' : '-') + ' ' + formatRupiah(row.nilai)">
                                        </span>
                                    </div>
                                </div>

                                <!-- Peringatan jika stok sesudah negatif -->
                                <div x-show="row.stokSesudah < 0" class="p-2 rounded-lg bg-rose-100 text-rose-800 text-[11px] font-bold flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><circle cx="12" cy="12" r="10" stroke-width="2"/><line x1="12" y1="8" x2="12" y2="12" stroke-width="2"/><circle cx="12" cy="16" r="1" fill="currentColor"/></svg>
                                    <span>Peringatan: Stok sesudah penyesuaian tidak boleh bernilai negatif! Kurangi jumlah atau periksa kembali.</span>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Footer Ringkasan & Total Kerugian -->
                    <div class="pt-4 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div>
                            <span class="text-xs text-slate-500">Estimasi Total Kerugian Toko:</span>
                            <div class="text-xl sm:text-2xl font-black text-rose-600" x-text="formatRupiah(totalKerugian)"></div>
                            <p class="text-[10px] text-slate-400">Total nilai modal dari barang yang berkurang</p>
                        </div>

                        <div class="flex items-center gap-3">
                            <a href="{{ route('admin.penyesuaian.index') }}" 
                               class="py-2.5 px-4 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 font-bold text-xs transition">
                                Batal
                            </a>
                            <button type="submit" 
                                    :disabled="isSubmitting || hasNegativeStock() || items.length === 0"
                                    class="py-2.5 px-6 rounded-xl bg-gradient-to-r from-[#166534] to-[#14532D] hover:from-[#14532D] hover:to-[#14532D] text-white font-bold text-xs shadow-md shadow-[#166534]/20 transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                                <span x-show="!isSubmitting">Simpan Penyesuaian Stok</span>
                                <span x-show="isSubmitting">Menyimpan...</span>
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </form>

    </div>

    <script>
        window.penyesuaianForm = function penyesuaianForm() {
            return {
                daftarBarang: @js($barangList),
                items: [
                    {
                        uid: Date.now(),
                        barang_id: '',
                        barang: null,
                        jenis: 'rusak',
                        arah: 'kurang',
                        jumlah: 1,
                        alasan: '',
                        stokSesudah: 0,
                        nilai: 0
                    }
                ],
                isSubmitting: false,

                tambahBaris() {
                    this.items.push({
                        uid: Date.now() + Math.random(),
                        barang_id: '',
                        barang: null,
                        jenis: 'rusak',
                        arah: 'kurang',
                        jumlah: 1,
                        alasan: '',
                        stokSesudah: 0,
                        nilai: 0
                    });
                },

                hapusBaris(index) {
                    if (this.items.length > 1) {
                        this.items.splice(index, 1);
                    }
                },

                onBarangChange(row) {
                    const b = this.daftarBarang.find(item => item.id == row.barang_id);
                    row.barang = b || null;
                    this.hitungBaris(row);
                },

                onJenisChange(row) {
                    if (row.jenis !== 'koreksi_lain') {
                        row.arah = 'kurang';
                    }
                    this.hitungBaris(row);
                },

                hitungBaris(row) {
                    if (!row.barang) {
                        row.stokSesudah = 0;
                        row.nilai = 0;
                        return;
                    }
                    const stokAwal = Number(row.barang.stok) || 0;
                    const modal = Number(row.barang.harga_modal) || 0;
                    const qty = Number(row.jumlah) || 0;

                    if (row.arah === 'tambah') {
                        row.stokSesudah = stokAwal + qty;
                    } else {
                        row.stokSesudah = stokAwal - qty;
                    }

                    row.nilai = qty * modal;
                },

                get totalKerugian() {
                    let total = 0;
                    for (const row of this.items) {
                        if (row.arah === 'kurang' && row.nilai > 0) {
                            total += row.nilai;
                        }
                    }
                    return total;
                },

                isBarangSelectedInOtherRow(barangId, currentUid) {
                    return this.items.some(row => row.uid !== currentUid && row.barang_id == barangId);
                },

                hasNegativeStock() {
                    return this.items.some(row => row.barang && row.stokSesudah < 0);
                },

                handleSubmit(event) {
                    if (this.hasNegativeStock()) {
                        event.preventDefault();
                        alert('Terdapat barang dengan stok sesudah penyesuaian yang bernilai negatif. Mohon perbaiki.');
                        return;
                    }
                    this.isSubmitting = true;
                },

                formatRupiah(number) {
                    return 'Rp ' + Number(number || 0).toLocaleString('id-ID');
                }
            };
        };
    </script>
</x-layouts.admin>
