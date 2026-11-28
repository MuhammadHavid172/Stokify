<x-layouts.admin title="Tambah Barang Baru">
    <div class="max-w-4xl mx-auto space-y-6" x-data="{
        kode: '{{ old('kode', $nextKode) }}',
        kategoriId: '{{ old('kategori_id', '') }}',
        kategoriMap: {{ json_encode($kategoriKodeMap ?? []) }},
        defaultKode: '{{ $nextKode }}',
        updateKodeOtomatis() {
            if (this.kategoriId && this.kategoriMap[this.kategoriId]) {
                this.kode = this.kategoriMap[this.kategoriId];
            } else if (!this.kategoriId) {
                this.kode = this.defaultKode;
            }
        },
        hargaModal: '{{ old('harga_modal', '') }}',
        hargaJual: '{{ old('harga_jual', '') }}',
        cameraScannerOpen: false,
        html5QrCodeInstance: null,
        playBeep() {
            try {
                const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                const osc = audioCtx.createOscillator();
                const gain = audioCtx.createGain();
                osc.connect(gain);
                gain.connect(audioCtx.destination);
                osc.type = 'sine';
                osc.frequency.setValueAtTime(1400, audioCtx.currentTime);
                gain.gain.setValueAtTime(0.25, audioCtx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.12);
                osc.start(audioCtx.currentTime);
                osc.stop(audioCtx.currentTime + 0.12);
            } catch(e) {}
        },
        async openCameraScanner() {
            this.cameraScannerOpen = true;
            this.$nextTick(async () => {
                try {
                    if (typeof Html5Qrcode === 'undefined') {
                        alert('Pustaka scanner kamera belum siap. Coba periksa koneksi atau muat ulang.');
                        this.cameraScannerOpen = false;
                        return;
                    }
                    if (this.html5QrCodeInstance) {
                        try { await this.html5QrCodeInstance.stop(); } catch(e) {}
                    }
                    this.html5QrCodeInstance = new Html5Qrcode('create-camera-reader');
                    await this.html5QrCodeInstance.start(
                        { facingMode: 'environment' },
                        { fps: 12, qrbox: { width: 250, height: 180 }, aspectRatio: 1.333333 },
                        (decodedText) => {
                            if (decodedText) {
                                this.playBeep();
                                this.kode = decodedText.trim();
                                this.closeCameraScanner();
                            }
                        }
                    );
                } catch(err) {
                    alert('Tidak dapat mengakses kamera: ' + (err.message || 'Izin kamera ditolak.'));
                    this.cameraScannerOpen = false;
                }
            });
        },
        async closeCameraScanner() {
            if (this.html5QrCodeInstance) {
                try {
                    await this.html5QrCodeInstance.stop();
                    await this.html5QrCodeInstance.clear();
                } catch(e) {}
                this.html5QrCodeInstance = null;
            }
            this.cameraScannerOpen = false;
        },
        get isRugi() {
            return Number(this.hargaJual) > 0 && Number(this.hargaModal) > 0 && Number(this.hargaJual) < Number(this.hargaModal);
        }
    }">
        
        <!-- Header Halaman -->
        <div class="flex items-center justify-between pb-2 border-b border-slate-100">
            <div>
                <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 mb-1">
                    <a href="{{ route('admin.dashboard') }}" class="hover:text-slate-800 transition">Dashboard</a>
                    <span>/</span>
                    <a href="{{ route('admin.barang.index') }}" class="hover:text-slate-800 transition">Master Barang</a>
                    <span>/</span>
                    <span class="text-slate-800">Tambah Barang</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Tambah Barang Baru</h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">
                    Daftarkan barang baru ke katalog kios Anda.
                </p>
            </div>

            <a href="{{ route('admin.barang.index') }}" 
               class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs shadow-xs transition">
                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Kembali</span>
            </a>
        </div>

        <!-- Form Tambah Barang -->
        <div class="bg-white border border-slate-200/80 rounded-3xl p-6 sm:p-8 shadow-xs">
            <form method="POST" action="{{ route('admin.barang.store') }}" class="space-y-6">
                @csrf

                <!-- Section 1: Identitas Barang -->
                <div>
                    <h3 class="text-sm font-bold text-slate-900 pb-2 border-b border-slate-100 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-[#DCFCE7] text-[#166534] flex items-center justify-center text-xs font-bold">1</span>
                        <span>Identitas Barang</span>
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5 mt-4">
                        <!-- Kode Barang -->
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label for="kode" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                                    Kode Barang <span class="text-rose-500">*</span>
                                </label>
                                <div class="flex items-center gap-2">
                                    <button type="button" 
                                            @click="openCameraScanner()" 
                                            class="inline-flex items-center gap-1 text-[11px] font-bold text-[#166534] hover:text-[#14532D] bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 px-2 py-0.5 rounded transition">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        <span>Scan Kamera</span>
                                    </button>
                                    <button type="button" 
                                            @click="updateKodeOtomatis()" 
                                            class="text-[11px] font-semibold text-slate-500 hover:text-slate-800 underline transition">
                                        Buat otomatis
                                    </button>
                                </div>
                            </div>
                            <input type="text" 
                                   id="kode" 
                                   name="kode" 
                                   x-model="kode" 
                                   required 
                                   placeholder="Kode barang atau scan barcode" 
                                   class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 font-mono text-xs sm:text-sm text-slate-900 focus:border-[#166534] focus:ring-1 focus:ring-[#166534] transition" />
                            <p class="text-[11px] text-slate-400 mt-1">Scan kemasan dengan kamera/scanner fisik atau klik otomatis.</p>
                            @error('kode')
                                <p class="text-[11px] font-semibold text-rose-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Kategori Barang -->
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label for="kategori_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                                    Kategori
                                </label>
                                <a href="{{ route('admin.kategori.index') }}" target="_blank" class="text-[11px] font-semibold text-slate-500 hover:text-slate-800 underline">
                                    + Kelola Kategori
                                </a>
                            </div>
                            <select id="kategori_id" 
                                    name="kategori_id" 
                                    x-model="kategoriId"
                                    @change="updateKodeOtomatis()"
                                    class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs sm:text-sm text-slate-800 focus:border-[#166534] focus:ring-1 focus:ring-[#166534] transition">
                                <option value="">-- Tanpa Kategori (BRG) --</option>
                                @foreach ($kategoriList as $kategori)
                                    <option value="{{ $kategori->id }}" {{ old('kategori_id') == $kategori->id ? 'selected' : '' }}>
                                        {{ $kategori->nama }} ({{ $kategori->getPrefixKode() }})
                                    </option>
                                @endforeach
                            </select>
                            @error('kategori_id')
                                <p class="text-[11px] font-semibold text-rose-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Nama Barang -->
                        <div class="sm:col-span-2">
                            <label for="nama" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Nama Barang Dagangan <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" 
                                   id="nama" 
                                   name="nama" 
                                   value="{{ old('nama') }}" 
                                   required 
                                   placeholder="Nama barang" 
                                   class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs sm:text-sm text-slate-900 focus:border-[#166534] focus:ring-1 focus:ring-[#166534] transition" />
                            @error('nama')
                                <p class="text-[11px] font-semibold text-rose-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Satuan Barang -->
                        <div>
                            <label for="satuan" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Satuan <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" 
                                   id="satuan" 
                                   name="satuan" 
                                   list="satuan-list"
                                   value="{{ old('satuan', 'pcs') }}" 
                                   required 
                                   placeholder="pcs, bungkus, botol, kg, dll" 
                                   class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs sm:text-sm text-slate-900 focus:border-[#166534] focus:ring-1 focus:ring-[#166534] transition" />
                            <datalist id="satuan-list">
                                @foreach ($satuanOptions as $sat)
                                    <option value="{{ $sat }}"></option>
                                @endforeach
                            </datalist>
                            <p class="text-[11px] text-slate-400 mt-1">Pilih dari daftar atau ketik bebas.</p>
                            @error('satuan')
                                <p class="text-[11px] font-semibold text-rose-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Section 2: Penetapan Harga -->
                <div>
                    <h3 class="text-sm font-bold text-slate-900 pb-2 border-b border-slate-100 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-[#DCFCE7]/40 text-[#166534] flex items-center justify-center text-xs font-black">2</span>
                        <span>Harga & Margin</span>
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5 mt-4">
                        <!-- Harga Modal -->
                        <div>
                            <label for="harga_modal" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Harga Modal / Beli (Rp) <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-xs font-bold text-slate-400">Rp</span>
                                <input type="number" 
                                       id="harga_modal" 
                                       name="harga_modal" 
                                       x-model="hargaModal"
                                       required 
                                       min="0" 
                                       placeholder="0" 
                                       class="no-spinner w-full rounded-xl border border-slate-200 pl-10 pr-3.5 py-2.5 text-xs sm:text-sm font-semibold text-slate-900 focus:border-[#166534] focus:ring-1 focus:ring-[#166534] transition" />
                            </div>
                            <p class="text-[11px] text-slate-400 mt-1">Rupiah tanpa titik/desimal.</p>
                            @error('harga_modal')
                                <p class="text-[11px] font-semibold text-rose-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Harga Jual -->
                        <div>
                            <label for="harga_jual" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Harga Jual ke Pembeli (Rp) <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-xs font-bold text-slate-400">Rp</span>
                                <input type="number" 
                                       id="harga_jual" 
                                       name="harga_jual" 
                                       x-model="hargaJual"
                                       required 
                                       min="0" 
                                       placeholder="0" 
                                       class="no-spinner w-full rounded-xl border border-slate-200 pl-10 pr-3.5 py-2.5 text-xs sm:text-sm font-bold text-slate-900 focus:border-[#166534] focus:ring-1 focus:ring-[#166534] transition" />
                            </div>
                            @error('harga_jual')
                                <p class="text-[11px] font-semibold text-rose-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Peringatan Harga Jual < Harga Modal -->
                    <div x-show="isRugi" x-cloak class="mt-3 p-3.5 rounded-2xl bg-amber-50 border border-amber-200 text-amber-800 text-xs flex items-center gap-2.5">
                        <svg class="w-5 h-5 text-amber-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <span>
                            <strong>Peringatan:</strong> Harga jual berada di bawah harga modal. Anda tetap diperbolehkan menyimpan, namun berpotensi rugi saat transaksi.
                        </span>
                    </div>
                </div>

                <!-- Section 3: Persediaan & Stok Awal -->
                <div>
                    <h3 class="text-sm font-bold text-slate-900 pb-2 border-b border-slate-100 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-[#DCFCE7]/40 text-[#166534] flex items-center justify-center text-xs font-black">3</span>
                        <span>Stok Awal & Pengingat Minimum</span>
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5 mt-4">
                        <!-- Stok Awal -->
                        <div>
                            <label for="stok_awal" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Stok Awal Sekarang
                            </label>
                            <input type="number" 
                                   id="stok_awal" 
                                   name="stok_awal" 
                                   value="{{ old('stok_awal', '') }}" 
                                   min="0" 
                                   placeholder="0 (opsional)"
                                   class="no-spinner w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs sm:text-sm font-semibold text-slate-900 focus:border-[#166534] focus:ring-1 focus:ring-[#166534] transition" />
                            <p class="text-[11px] text-slate-400 mt-1">
                                Jika diisi &gt; 0, otomatis tercatat di mutasi stok sebagai 'stok awal'.
                            </p>
                            @error('stok_awal')
                                <p class="text-[11px] font-semibold text-rose-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Stok Minimum -->
                        <div>
                            <label for="stok_minimum" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Batas Stok Minimum
                            </label>
                            <input type="number" 
                                   id="stok_minimum" 
                                   name="stok_minimum" 
                                   value="{{ old('stok_minimum', '') }}" 
                                   min="0" 
                                   placeholder="0 (opsional)"
                                   class="no-spinner w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs sm:text-sm font-semibold text-slate-900 focus:border-[#166534] focus:ring-1 focus:ring-[#166534] transition" />
                            <p class="text-[11px] text-slate-400 mt-1">
                                Muncul sebagai peringatan 'Stok Menipis' jika stok saat ini &le; angka ini.
                            </p>
                            @error('stok_minimum')
                                <p class="text-[11px] font-semibold text-rose-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Tombol Aksi Form -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <a href="{{ route('admin.barang.index') }}" 
                       class="px-5 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition">
                        Batal
                    </a>
                    <button type="submit" 
                            class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-[#166534] to-[#14532D] hover:from-[#14532D] hover:to-[#14532D] text-xs font-bold text-white shadow-md shadow-[#166534]/20 transition transform hover:-translate-y-0.5 active:translate-y-0">
                        Simpan Barang
                    </button>
                </div>
            </form>
        </div>

        <!-- Modal Pemindai Barcode Kamera -->
        <div x-show="cameraScannerOpen" 
             x-cloak 
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-xs"
             @keydown.escape.window="closeCameraScanner()">
            <div class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden border border-slate-200 animate-in fade-in zoom-in duration-150">
                <div class="px-5 py-3.5 bg-slate-900 text-white flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
                        <h3 class="text-sm font-bold">Pindai Barcode Kemasan Barang</h3>
                    </div>
                    <button type="button" @click="closeCameraScanner()" class="p-1 rounded-lg text-slate-400 hover:text-white transition">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                </div>
                <div class="p-4 bg-slate-950 flex flex-col items-center justify-center min-h-[300px]">
                    <div id="create-camera-reader" class="w-full max-w-sm rounded-xl overflow-hidden bg-black shadow-inner"></div>
                    <p class="text-xs text-slate-300 mt-3 text-center">Arahkan kamera ke barcode kemasan barang. Kode barcode akan terisi otomatis ke formulir.</p>
                </div>
                <div class="p-3 bg-slate-100 border-t border-slate-200 flex justify-end">
                    <button type="button" @click="closeCameraScanner()" class="px-4 py-2 rounded-lg bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold transition">
                        Batal & Tutup
                    </button>
                </div>
            </div>
        </div>

    </div>
</x-layouts.admin>
