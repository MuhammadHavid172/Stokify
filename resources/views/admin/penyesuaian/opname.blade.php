<x-layouts.admin title="Stok Opname Sederhana">
    <div class="space-y-6" x-data="opnameApp()">
        
        <!-- Header Halaman -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-slate-100">
            <div>
                <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 mb-1">
                    <a href="{{ route('admin.dashboard') }}" class="hover:text-slate-800 transition">Dashboard</a>
                    <span>/</span>
                    <a href="{{ route('admin.penyesuaian.index') }}" class="hover:text-slate-800 transition">Penyesuaian Stok</a>
                    <span>/</span>
                    <span class="text-slate-800">Stok Opname</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Stok Opname</h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">
                    Cocokkan stok fisik di rak/etalase dengan data sistem. Hanya barang dengan selisih yang akan disesuaikan.
                </p>
            </div>

            <div class="flex items-center gap-2.5 shrink-0">
                <a href="{{ route('admin.penyesuaian.index') }}" 
                   class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs shadow-xs transition">
                    &larr; Riwayat Penyesuaian
                </a>
            </div>
        </div>

        <!-- Filter Pencarian & Kategori -->
        <div class="bg-slate-50/80 border border-slate-200/80 rounded-2xl p-4 sm:p-5 shadow-xs">
            <form method="GET" action="{{ route('admin.penyesuaian.opname') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-center">
                <!-- 1. Pencarian Kode / Nama -->
                <div class="lg:col-span-4 relative">
                    <input type="text" 
                           name="q" 
                           value="{{ request('q') }}" 
                           placeholder="Cari kode atau nama barang..." 
                           class="w-full rounded-xl border border-slate-200 bg-white pl-9 pr-3 py-2 text-xs text-slate-800 placeholder-slate-400 focus:border-[#166534] focus:ring-1 focus:ring-[#166534] transition" />
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8" />
                            <line x1="21" y1="21" x2="16.65" y2="16.65" />
                        </svg>
                    </div>
                </div>

                <!-- 2. Filter Kategori -->
                <div class="lg:col-span-3">
                    <select name="kategori_id" 
                            onchange="this.form.submit()"
                            class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs text-slate-700 focus:border-[#166534] focus:ring-1 focus:ring-[#166534] transition">
                        <option value="">Semua Kategori</option>
                        @foreach ($kategoriList as $kat)
                            <option value="{{ $kat->id }}" {{ request('kategori_id') == $kat->id ? 'selected' : '' }}>
                                {{ $kat->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- 3. Checkbox Sertakan Nonaktif -->
                <div class="lg:col-span-3 flex items-center gap-2">
                    <label class="flex items-center gap-2 text-xs text-slate-700 cursor-pointer select-none">
                        <input type="checkbox" 
                               name="sertakan_nonaktif" 
                               value="1" 
                               {{ request('sertakan_nonaktif') ? 'checked' : '' }}
                               onchange="this.form.submit()"
                               class="rounded border-slate-300 text-[#166534] focus:ring-[#166534]" />
                        <span>Sertakan barang nonaktif</span>
                    </label>
                </div>

                <!-- 4. Tombol Filter & Reset -->
                <div class="lg:col-span-2 flex items-center gap-2">
                    <button type="submit" 
                            class="flex-1 py-2 px-3 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs shadow-xs transition">
                        Terapkan
                    </button>
                    @if (request()->hasAny(['q', 'kategori_id', 'sertakan_nonaktif']))
                        <a href="{{ route('admin.penyesuaian.opname') }}" 
                           class="py-2 px-3 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 font-semibold text-xs transition">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Formulir Eksekusi Opname -->
        <form method="POST" action="{{ route('admin.penyesuaian.storeOpname') }}" @submit="handleSubmit($event)">
            @csrf

            <!-- Header Info Opname -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 sm:p-6 shadow-xs mb-6 space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="tanggal" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Tanggal Opname <span class="text-rose-500">*</span>
                        </label>
                        <input type="date" 
                               id="tanggal" 
                               name="tanggal" 
                               value="{{ old('tanggal', date('Y-m-d')) }}" 
                               max="{{ date('Y-m-d') }}"
                               required 
                               class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3.5 py-2 text-xs sm:text-sm text-slate-800 focus:bg-white focus:border-[#166534] focus:ring-1 focus:ring-[#166534] transition" />
                    </div>

                    <div>
                        <label for="catatan" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Catatan Opname <span class="text-slate-400 font-normal">(Opsional)</span>
                        </label>
                        <input type="text" 
                               id="catatan" 
                               name="catatan" 
                               value="{{ old('catatan') }}" 
                               placeholder="Tuliskan keterangan pelaksanaan stok opname..." 
                               class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3.5 py-2 text-xs sm:text-sm text-slate-800 focus:bg-white focus:border-[#166534] focus:ring-1 focus:ring-[#166534] transition" />
                    </div>
                </div>

                <div class="p-3 rounded-xl bg-blue-50 border border-blue-200 text-blue-800 text-xs flex items-start gap-2">
                    <svg class="w-4 h-4 text-blue-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><circle cx="12" cy="12" r="10" stroke-width="2"/><line x1="12" y1="16" x2="12" y2="12" stroke-width="2"/><line x1="12" y1="8" x2="12.01" y2="8" stroke-width="2"/></svg>
                    <div>
                        <strong>Petunjuk Pengisian:</strong> Kosongkan kolom <em>Stok Fisik</em> untuk barang yang tidak dihitung. Hanya baris yang stok fisiknya diisi dan berselisih dengan stok sistem yang akan dicatat ke dalam penyesuaian.
                    </div>
                </div>
            </div>

            <!-- Tabel Barang Opname -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden mb-6">
                @if ($barangList->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="border-b border-slate-100 bg-slate-50/70 font-bold text-slate-500 uppercase tracking-wider text-[11px]">
                                    <th class="px-4 py-3.5 w-12 text-center">No</th>
                                    <th class="px-4 py-3.5">Barang</th>
                                    <th class="px-4 py-3.5 text-center">Stok Sistem</th>
                                    <th class="px-4 py-3.5 text-center w-36">Stok Fisik Rak</th>
                                    <th class="px-4 py-3.5 text-center w-28">Selisih</th>
                                    <th class="px-4 py-3.5">Alasan Selisih</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 font-medium">
                                @foreach ($barangList as $index => $b)
                                    <tr class="hover:bg-slate-50/80 transition"
                                        :class="items[{{ $index }}].isDiff ? 'bg-amber-50/40' : ''">
                                        <!-- No -->
                                        <td class="px-4 py-3 text-center text-slate-400">
                                            {{ $index + 1 }}
                                            <input type="hidden" name="items[{{ $index }}][barang_id]" value="{{ $b->id }}">
                                            <input type="hidden" name="items[{{ $index }}][stok_sistem]" value="{{ $b->stok }}">
                                        </td>

                                        <!-- Barang -->
                                        <td class="px-4 py-3">
                                            <div class="font-bold text-slate-900">{{ $b->nama }}</div>
                                            <div class="text-[10px] text-slate-400 font-mono">
                                                {{ $b->kode }} &bull; {{ $b->kategori->nama ?? 'Tanpa Kategori' }}
                                                @if (! $b->is_aktif)
                                                    <span class="text-rose-500 font-semibold">[Nonaktif]</span>
                                                @endif
                                            </div>
                                        </td>

                                        <!-- Stok Sistem -->
                                        <td class="px-4 py-3 text-center font-bold text-slate-700">
                                            {{ $b->stok }} <span class="text-[10px] font-normal text-slate-400">{{ $b->satuan }}</span>
                                        </td>

                                        <!-- Input Stok Fisik -->
                                        <td class="px-4 py-3 text-center">
                                            <input type="number" 
                                                   name="items[{{ $index }}][stok_fisik]" 
                                                   x-model="items[{{ $index }}].fisik"
                                                   @input="calculateDiff({{ $index }})"
                                                   placeholder="—"
                                                   min="0"
                                                   class="w-24 text-center rounded-xl border border-slate-200 px-2 py-1.5 text-xs text-slate-900 font-bold focus:border-[#166534] focus:ring-1 focus:ring-[#166534] transition" />
                                        </td>

                                        <!-- Selisih -->
                                        <td class="px-4 py-3 text-center">
                                            <template x-if="items[{{ $index }}].fisik === '' || items[{{ $index }}].fisik === null">
                                                <span class="text-slate-300 font-mono text-[11px]">—</span>
                                            </template>
                                            <template x-if="items[{{ $index }}].fisik !== '' && items[{{ $index }}].fisik !== null">
                                                <div>
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold"
                                                          :class="{
                                                              'bg-emerald-50 text-emerald-700 border border-emerald-200': items[{{ $index }}].diff > 0,
                                                              'bg-rose-50 text-rose-700 border border-rose-200': items[{{ $index }}].diff < 0,
                                                              'bg-slate-100 text-slate-500 border border-slate-200': items[{{ $index }}].diff === 0
                                                          }"
                                                          x-text="(items[{{ $index }}].diff > 0 ? '+' : '') + items[{{ $index }}].diff">
                                                    </span>
                                                </div>
                                            </template>
                                        </td>

                                        <!-- Alasan Selisih -->
                                        <td class="px-4 py-3">
                                            <input type="text" 
                                                   name="items[{{ $index }}][alasan]" 
                                                   x-model="items[{{ $index }}].alasan"
                                                   :disabled="!items[{{ $index }}].isDiff"
                                                   placeholder="Selisih stok opname"
                                                   class="w-full rounded-xl border border-slate-200 px-3 py-1 text-xs text-slate-800 placeholder-slate-400 focus:border-[#166534] focus:ring-1 focus:ring-[#166534] transition disabled:bg-slate-50 disabled:text-slate-400 disabled:border-slate-100" />
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="p-8 text-center text-slate-400 text-xs">
                        Tidak ada barang yang cocok dengan filter pencarian.
                    </div>
                @endif
            </div>

            <!-- Sticky Summary & Submit Bar -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-lg flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center gap-6">
                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Barang Berselisih:</span>
                        <div class="text-xl font-black text-slate-900" x-text="countDiff + ' item'"></div>
                    </div>
                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Selisih Unit:</span>
                        <div class="text-xl font-black"
                             :class="totalUnitDiff >= 0 ? 'text-emerald-600' : 'text-rose-600'"
                             x-text="(totalUnitDiff > 0 ? '+' : '') + totalUnitDiff + ' unit'"></div>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.penyesuaian.index') }}" 
                       class="py-2.5 px-4 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 font-bold text-xs transition">
                        Batal
                    </a>
                    <button type="submit" 
                            :disabled="countDiff === 0 || isSubmitting"
                            class="py-2.5 px-6 rounded-xl bg-gradient-to-r from-[#166534] to-[#14532D] hover:from-[#14532D] hover:to-[#14532D] text-white font-bold text-xs shadow-md shadow-[#166534]/20 transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                        <span x-show="!isSubmitting">Simpan Penyesuaian Opname (<span x-text="countDiff"></span>)</span>
                        <span x-show="isSubmitting">Menyimpan...</span>
                    </button>
                </div>
            </div>
        </form>

    </div>

    <script>
        window.opnameApp = function opnameApp() {
            const rawBarang = @js($barangList);
            const initialItems = rawBarang.map(b => ({
                id: b.id,
                stokSistem: Number(b.stok),
                fisik: '',
                diff: 0,
                isDiff: false,
                alasan: 'Selisih stok opname'
            }));

            return {
                items: initialItems,
                isSubmitting: false,

                calculateDiff(index) {
                    const row = this.items[index];
                    if (row.fisik === '' || row.fisik === null) {
                        row.diff = 0;
                        row.isDiff = false;
                        return;
                    }
                    const f = Number(row.fisik);
                    row.diff = f - row.stokSistem;
                    row.isDiff = (row.diff !== 0);
                },

                get countDiff() {
                    return this.items.filter(item => item.isDiff).length;
                },

                get totalUnitDiff() {
                    return this.items.reduce((sum, item) => sum + (item.isDiff ? item.diff : 0), 0);
                },

                handleSubmit(event) {
                    if (this.countDiff === 0) {
                        event.preventDefault();
                        alert('Tidak ada selisih stok fisik yang diisi untuk disesuaikan.');
                        return;
                    }

                    if (!confirm('Anda akan menyimpan penyesuaian stok opname untuk ' + this.countDiff + ' barang. Lanjutkan?')) {
                        event.preventDefault();
                        return;
                    }

                    this.isSubmitting = true;
                }
            };
        };
    </script>
</x-layouts.admin>
