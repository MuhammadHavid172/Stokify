<x-layouts.admin title="Pengaturan Parameter Restok - Stokify">
<div class="space-y-6">

    <!-- Header Halaman & Tab Navigasi -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-gray-200">
        <div>
            <div class="flex items-center gap-2 text-xs text-gray-500 mb-1">
                <span>Analisis Barang</span>
                <span>/</span>
                <span class="text-gray-900 font-semibold">Pengaturan Restok</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight text-balance">Pengaturan Parameter Restok</h1>
            <p class="text-xs text-gray-500 mt-1 text-pretty">
                Sesuaikan parameter model Titik Pesan Ulang (Reorder Point) dan toleransi stok untuk kios {{ $kios->nama_kios }}.
            </p>
        </div>

        <!-- Tombol Tab Navigasi -->
        <div class="flex items-center bg-gray-100 p-1 rounded-lg border border-gray-200 self-start sm:self-auto">
            <a href="{{ route('admin.analisis.index') }}" 
               class="px-3.5 py-1.5 rounded-md text-xs font-medium transition text-gray-600 hover:text-gray-900 hover:bg-white/60">
                Ringkasan Performa
            </a>
            <a href="{{ route('admin.analisis.rekomendasi') }}" 
               class="px-3.5 py-1.5 rounded-md text-xs font-medium transition text-gray-600 hover:text-gray-900 hover:bg-white/60">
                Rekomendasi Restok
            </a>
            <a href="{{ route('admin.analisis.pengaturan') }}" 
               class="px-3.5 py-1.5 rounded-md text-xs font-semibold transition bg-white text-gray-900 shadow-sm">
                Pengaturan
            </a>
        </div>
    </div>

    <!-- Kotak Form Pengaturan -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Formulir Parameter (2 Kolom) -->
        <div class="lg:col-span-2 bg-white border border-gray-200 rounded-xl p-6 sm:p-7 shadow-sm space-y-6">
            <div class="border-b border-gray-200 pb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h2 class="text-base font-semibold text-gray-900 tracking-tight">Konfigurasi Reorder Point (ROP)</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Nilai parameter di bawah berlaku untuk seluruh perhitungan rekomendasi restok barang.</p>
                </div>

                <!-- Tombol Reset Nilai Bawaan -->
                <form method="POST" action="{{ route('admin.analisis.pengaturan.reset') }}" onsubmit="return confirm('Apakah Anda yakin ingin mengembalikan seluruh parameter restok ke nilai bawaan sistem?');">
                    @csrf
                    <button type="submit" class="px-3 py-1.5 rounded-lg border border-gray-300 bg-white hover:bg-gray-50 text-gray-700 text-xs font-semibold transition shadow-sm">
                        Kembalikan ke Bawaan
                    </button>
                </form>
            </div>

            <form method="POST" action="{{ route('admin.analisis.pengaturan.update') }}" class="space-y-5">
                @csrf
                @method('PUT')

                <!-- 1. Lead Time (Hari) -->
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between">
                        <label for="lead_time_hari" class="text-xs font-semibold text-gray-700 uppercase tracking-wider">
                            1. Lead Time Pengiriman (Hari)
                        </label>
                        <span class="text-[11px] text-gray-400 font-mono">Batas: 0 - 30 hari</span>
                    </div>
                    <div class="relative">
                        <input type="number" 
                               id="lead_time_hari" 
                               name="lead_time_hari" 
                               value="{{ old('lead_time_hari', $kios->lead_time_hari ?? 2) }}" 
                               min="0" 
                               max="30" 
                               required
                               class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2 text-xs text-gray-900 font-mono focus:border-[#166534] focus:ring-1 focus:ring-[#166534] transition" />
                        <span class="absolute right-3.5 top-2 text-xs text-gray-400">hari</span>
                    </div>
                    <p class="text-xs text-gray-500 leading-relaxed">
                        Lama waktu sejak pesanan restok dikirimkan ke supplier / distributor hingga barang sampai di kios Anda (contoh: 2 hari).
                    </p>
                    @error('lead_time_hari')
                        <p class="text-xs text-red-600 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <!-- 2. Hari Pengaman (Safety Stock) -->
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between">
                        <label for="hari_pengaman" class="text-xs font-semibold text-gray-700 uppercase tracking-wider">
                            2. Hari Pengaman / Safety Stock (Hari)
                        </label>
                        <span class="text-[11px] text-gray-400 font-mono">Batas: 0 - 30 hari</span>
                    </div>
                    <div class="relative">
                        <input type="number" 
                               id="hari_pengaman" 
                               name="hari_pengaman" 
                               value="{{ old('hari_pengaman', $kios->hari_pengaman ?? 3) }}" 
                               min="0" 
                               max="30" 
                               required
                               class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2 text-xs text-gray-900 font-mono focus:border-[#166534] focus:ring-1 focus:ring-[#166534] transition" />
                        <span class="absolute right-3.5 top-2 text-xs text-gray-400">hari</span>
                    </div>
                    <p class="text-xs text-gray-500 leading-relaxed">
                        Cadangan ekstra untuk mengantisipasi lonjakan pembeli mendadak atau keterlambatan pengiriman supplier (contoh: 3 hari).
                    </p>
                    @error('hari_pengaman')
                        <p class="text-xs text-red-600 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <!-- 3. Target Stok (hari penjualan) -->
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between">
                        <label for="cakupan_kulakan_hari" class="text-xs font-semibold text-gray-700 uppercase tracking-wider">
                            3. Target Stok (hari penjualan)
                        </label>
                        <span class="text-[11px] text-gray-400 font-mono">Batas: 1 - 60 hari</span>
                    </div>
                    <div class="relative">
                        <input type="number" 
                               id="cakupan_kulakan_hari" 
                               name="cakupan_kulakan_hari" 
                               value="{{ old('cakupan_kulakan_hari', $kios->cakupan_kulakan_hari ?? 7) }}" 
                               min="1" 
                               max="60" 
                               required
                               class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2 text-xs text-gray-900 font-mono focus:border-[#166534] focus:ring-1 focus:ring-[#166534] transition" />
                        <span class="absolute right-3.5 top-2 text-xs text-gray-400">hari</span>
                    </div>
                    <p class="text-xs text-gray-500 leading-relaxed">
                        Perkiraan jumlah hari penjualan yang ingin Anda penuhi dalam satu kali transaksi belanja barang masuk (contoh: 7 hari = belanja mingguan).
                    </p>
                    @error('cakupan_kulakan_hari')
                        <p class="text-xs text-red-600 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <!-- 4. Batas Stok Berlebih (Hari) -->
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between">
                        <label for="batas_stok_berlebih_hari" class="text-xs font-semibold text-gray-700 uppercase tracking-wider">
                            4. Ambang Batas Stok Berlebih (Hari)
                        </label>
                        <span class="text-[11px] text-gray-400 font-mono">Batas: 14 - 365 hari</span>
                    </div>
                    <div class="relative">
                        <input type="number" 
                               id="batas_stok_berlebih_hari" 
                               name="batas_stok_berlebih_hari" 
                               value="{{ old('batas_stok_berlebih_hari', $kios->batas_stok_berlebih_hari ?? 60) }}" 
                               min="14" 
                               max="365" 
                               required
                               class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2 text-xs text-gray-900 font-mono focus:border-[#166534] focus:ring-1 focus:ring-[#166534] transition" />
                        <span class="absolute right-3.5 top-2 text-xs text-gray-400">hari</span>
                    </div>
                    <p class="text-xs text-gray-500 leading-relaxed">
                        Jika stok saat ini mencukupi penjualan melebihi jumlah hari ini, barang akan diberi tanda "Stok Berlebih" dan restoknya ditahan otomatis (contoh: 60 hari).
                    </p>
                    @error('batas_stok_berlebih_hari')
                        <p class="text-xs text-red-600 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <!-- 5. Ambang Margin Tipis (%) -->
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between">
                        <label for="margin_tipis_persen" class="text-xs font-semibold text-gray-700 uppercase tracking-wider">
                            5. Ambang Batas Margin Tipis (%)
                        </label>
                        <span class="text-[11px] text-gray-400 font-mono">Batas: 0 - 100%</span>
                    </div>
                    <div class="relative">
                        <input type="number" 
                               id="margin_tipis_persen" 
                               name="margin_tipis_persen" 
                               value="{{ old('margin_tipis_persen', $kios->margin_tipis_persen ?? 10) }}" 
                               min="0" 
                               max="100" 
                               required
                               class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2 text-xs text-gray-900 font-mono focus:border-[#166534] focus:ring-1 focus:ring-[#166534] transition" />
                        <span class="absolute right-3.5 top-2 text-xs text-gray-400">%</span>
                    </div>
                    <p class="text-xs text-gray-500 leading-relaxed">
                        Barang dengan persentase margin (Harga Jual - Harga Modal) / Harga Jual di bawah nilai ini akan diberi penanda "Margin Tipis" (contoh: 10%).
                    </p>
                    @error('margin_tipis_persen')
                        <p class="text-xs text-red-600 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Tombol Simpan -->
                <div class="pt-4 border-t border-gray-200 flex items-center justify-end">
                    <button type="submit" class="px-5 py-2 rounded-lg bg-[#166534] hover:bg-[#14532D] text-white font-semibold text-xs shadow-sm transition">
                        Simpan Pengaturan
                    </button>
                </div>
            </form>
        </div>

        <!-- Kolom Contoh Kasus & Simulasi Rumus (1 Kolom) -->
        <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm space-y-4">
            <h3 class="text-xs font-semibold text-gray-700 uppercase tracking-wider pb-3 border-b border-gray-200">
                Contoh Hitungan Satu Barang
            </h3>

            <div class="text-xs text-gray-600 space-y-3 leading-relaxed">
                <p>
                    Misalkan <strong>Beras Ramos 5kg</strong> memiliki riwayat:
                </p>
                <div class="bg-gray-50 p-3 rounded-lg border border-gray-200 space-y-1 font-mono text-xs text-gray-800">
                    <div>• Rata-rata Terjual: <strong>4,2 karung / hari</strong></div>
                    <div>• Stok Saat Ini: <strong>12 karung</strong></div>
                    <div>• Stok Minimum: <strong>10 karung</strong></div>
                    <div>• Harga Modal: <strong>Rp 50.000</strong></div>
                </div>

                <p class="font-semibold text-gray-900 pt-2">Langkah 1: Titik Pesan Ulang (ROP)</p>
                <div class="bg-gray-50 p-3 rounded-lg border border-gray-200 font-mono text-xs text-gray-800">
                    <div>= Max(4,2 x (2 + 3), 10)</div>
                    <div>= Max(21, 10) = <strong>21 karung</strong></div>
                </div>
                <p class="text-xs text-gray-500">
                    Karena stok saat ini (12) &le; Titik Pesan (21), sistem menandai barang ini <strong>harus segera direstok</strong>.
                </p>

                <p class="font-semibold text-gray-900 pt-2">Langkah 2: Target Stok &amp; Kuantitas Beli</p>
                <div class="bg-gray-50 p-3 rounded-lg border border-gray-200 font-mono text-xs text-gray-800">
                    <div>Target = Max(4,2 x (2+3+7), 10)</div>
                    <div>= Max(50,4, 10) = <strong>50,4 karung</strong></div>
                    <div class="pt-1 text-[#166534] font-bold">Beli = CEIL(50,4 - 12) = 39 karung</div>
                </div>

                <p class="font-semibold text-gray-900 pt-2">Langkah 3: Estimasi Biaya</p>
                <div class="bg-gray-50 p-3 rounded-lg border border-gray-200 font-mono text-xs text-gray-900 font-bold tabular-nums">
                    39 karung x Rp 50.000 = Rp 1.950.000
                </div>
            </div>
        </div>

    </div>

</div>
</x-layouts.admin>