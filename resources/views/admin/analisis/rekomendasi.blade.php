<x-layouts.admin title="Rekomendasi Restok - Stokify">
<div class="space-y-6" x-data="{
    selectedIds: [],
    selectAll: false,
    toggleAll(checked, allIds) {
        this.selectAll = checked;
        this.selectedIds = checked ? [...allIds] : [];
    }
}">

    <!-- Sub-Navigasi Tab: Laporan & Analisis -->
    <div class="flex items-center gap-2 border-b border-gray-200 pb-3">
        <a href="{{ route('admin.laporan.index') }}" 
           class="px-4 py-2 rounded-lg text-xs font-bold transition {{ request()->routeIs('admin.laporan.*') ? 'bg-[#166534] text-white shadow-xs' : 'bg-white text-gray-600 hover:text-gray-900 border border-gray-200 hover:bg-gray-50' }}">
            Dokumen Laporan
        </a>
        <a href="{{ route('admin.analisis.index') }}" 
           class="px-4 py-2 rounded-lg text-xs font-bold transition {{ request()->routeIs('admin.analisis.index') ? 'bg-[#166534] text-white shadow-xs' : 'bg-white text-gray-600 hover:text-gray-900 border border-gray-200 hover:bg-gray-50' }}">
            Analisis Performa (Pareto)
        </a>
        <a href="{{ route('admin.analisis.rekomendasi') }}" 
           class="px-4 py-2 rounded-lg text-xs font-bold transition {{ request()->routeIs('admin.analisis.rekomendasi') ? 'bg-[#166534] text-white shadow-xs' : 'bg-white text-gray-600 hover:text-gray-900 border border-gray-200 hover:bg-gray-50' }}">
            Rekomendasi Restok
        </a>
    </div>

    <!-- Header Halaman & Tab Navigasi -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-gray-200">
        <div>
            <div class="flex items-center gap-2 text-xs text-gray-500 mb-1">
                <span>Analisis Barang</span>
                <span>/</span>
                <span class="text-gray-900 font-semibold">Rekomendasi Restok</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight text-balance">Rekomendasi Restok Barang</h1>
            <p class="text-xs text-gray-500 mt-1 text-pretty">
                Estimasi kuantitas belanja ulang otomatis menggunakan model Titik Pesan Ulang (Reorder Point) berbasis laju penjualan harian.
            </p>
        </div>

        <!-- Tombol Tab Navigasi -->
        <div class="flex items-center bg-gray-100 p-1 rounded-lg border border-gray-200 self-start sm:self-auto">
            <a href="{{ route('admin.analisis.index', ['periode' => $periode]) }}" 
               class="px-3.5 py-1.5 rounded-md text-xs font-medium transition text-gray-600 hover:text-gray-900 hover:bg-white/60">
                Ringkasan Performa
            </a>
            <a href="{{ route('admin.analisis.rekomendasi', ['periode' => $periode]) }}" 
               class="px-3.5 py-1.5 rounded-md text-xs font-semibold transition bg-white text-gray-900 shadow-sm">
                Rekomendasi Restok
            </a>
            <a href="{{ route('admin.analisis.pengaturan') }}" 
               class="px-3.5 py-1.5 rounded-md text-xs font-medium transition text-gray-600 hover:text-gray-900 hover:bg-white/60">
                Pengaturan
            </a>
        </div>
    </div>

    <!-- Ringkasan Angka Restok & Tombol Aksi Cetak PDF -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <!-- Kartu 1: Jumlah Barang Perlu Restok -->
        <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs text-gray-500 font-medium block">Barang Perlu Restok</span>
                <div class="flex items-baseline gap-2 mt-1">
                    <span class="text-2xl sm:text-3xl font-bold text-gray-900 font-mono tabular-nums">{{ $ringkasan['jumlah_perlu_kulakan'] }}</span>
                    <span class="text-xs text-gray-500">barang</span>
                </div>
                <span class="text-xs text-[#166534] block mt-1 font-mono font-medium">Stok &le; Titik Pesan</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 border border-emerald-100 text-[#166534] flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
            </div>
        </div>

        <!-- Kartu 2: Total Estimasi Biaya Restok -->
        <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs text-gray-500 font-medium block">Total Estimasi Modal</span>
                <div class="flex items-baseline gap-1 mt-1">
                    <span class="text-xl sm:text-2xl font-bold text-gray-900 font-mono tabular-nums">{{ rupiah($ringkasan['total_estimasi_biaya']) }}</span>
                </div>
                <span class="text-xs text-gray-500 block mt-1 font-mono">Berdasarkan harga modal saat ini</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-50 border border-amber-100 text-amber-700 flex items-center justify-center font-bold font-mono">
                Rp
            </div>
        </div>

        <!-- Kartu 3: Tombol Aksi Cepat Unduh PDF -->
        <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm flex flex-col justify-between">
            <div>
                <span class="text-xs text-gray-500 font-medium block">Ekspor Rekomendasi</span>
                <p class="text-xs text-gray-500 mt-1">Unduh daftar belanja stok siap cetak format dokumen PDF A4.</p>
            </div>
            <div class="mt-3">
                <a href="{{ route('admin.analisis.pdf', ['periode' => $periode]) }}" 
                   target="_blank"
                   class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg bg-[#166534] hover:bg-[#14532D] text-white font-semibold text-xs shadow-sm transition">
                    <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>Unduh PDF Daftar Belanja Stok</span>
                </a>
            </div>
        </div>
    </div>

    <!-- FILTER BAR INTERAKTIF -->
    <div class="bg-white border border-gray-200 p-4 sm:p-5 rounded-xl shadow-sm space-y-4">
        <!-- Baris Rentang Waktu Periode -->
        <div class="flex flex-wrap items-center justify-between gap-3 pb-3 border-b border-gray-200">
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-xs font-semibold text-gray-700 uppercase tracking-wider mr-2">Rentang Laju Penjualan:</span>
                @foreach([7, 14, 30, 60, 90] as $hari)
                    <a href="{{ route('admin.analisis.rekomendasi', array_merge(request()->query(), ['periode' => $hari, 'page' => 1])) }}"
                       class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ $periode === $hari ? 'bg-[#166534] text-white shadow-sm' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        {{ $hari }} Hari
                    </a>
                @endforeach
            </div>

            <!-- Rumus Singkat Kios -->
            <div class="text-xs text-gray-500 font-mono bg-gray-50 px-3 py-1 rounded-lg border border-gray-200">
                Lead Time: <strong>{{ $kios->lead_time_hari ?? 2 }}hr</strong> • Pengaman: <strong>{{ $kios->hari_pengaman ?? 3 }}hr</strong> • Target Beli: <strong>{{ $kios->cakupan_kulakan_hari ?? 7 }}hr</strong>
            </div>
        </div>

        <!-- Form Filter Kategori, Status & Pencarian -->
        <form method="GET" action="{{ route('admin.analisis.rekomendasi') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <input type="hidden" name="periode" value="{{ $periode }}">

            <!-- Pencarian Kode / Nama -->
            <div class="sm:col-span-2 relative">
                <input type="text" 
                       name="q" 
                       value="{{ $keyword }}" 
                       placeholder="Cari kode atau nama barang..." 
                       class="w-full rounded-lg border border-gray-300 bg-white pl-10 pr-4 py-2 text-xs text-gray-900 placeholder-gray-400 focus:border-[#166534] focus:ring-1 focus:ring-[#166534] transition" />
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <circle cx="11" cy="11" r="8" stroke-width="2"/>
                        <line x1="21" y1="21" x2="16.65" y2="16.65" stroke-width="2"/>
                    </svg>
                </div>
            </div>

            <!-- Filter Status Rekomendasi -->
            <div>
                <select name="status" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs text-gray-900 focus:border-[#166534] focus:ring-1 focus:ring-[#166534] transition">
                    <option value="">Semua Status Rekomendasi</option>
                    <option value="perlu_kulakan" {{ $filterStatus === 'perlu_kulakan' ? 'selected' : '' }}>Hanya Perlu Restok (&gt; 0)</option>
                    <option value="kulakan_banyak" {{ $filterStatus === 'kulakan_banyak' ? 'selected' : '' }}>Restok Banyak (Fast)</option>
                    <option value="kulakan_secukupnya" {{ $filterStatus === 'kulakan_secukupnya' ? 'selected' : '' }}>Restok Secukupnya</option>
                    <option value="tahan_kulakan" {{ $filterStatus === 'tahan_kulakan' ? 'selected' : '' }}>Tahan Restok</option>
                    <option value="stok_cukup" {{ $filterStatus === 'stok_cukup' ? 'selected' : '' }}>Stok Cukup</option>
                </select>
            </div>

            <!-- Filter Kategori & Tombol Terapkan -->
            <div class="flex items-center gap-2">
                <select name="kategori_id" class="flex-1 rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs text-gray-900 focus:border-[#166534] focus:ring-1 focus:ring-[#166534] transition">
                    <option value="">Semua Kategori</option>
                    @foreach($kategoriList as $kat)
                        <option value="{{ $kat->id }}" {{ (string)$filterKategoriId === (string)$kat->id ? 'selected' : '' }}>
                            {{ $kat->nama }}
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="px-4 py-2 rounded-lg bg-[#166534] hover:bg-[#14532D] text-white text-xs font-semibold transition shrink-0 shadow-sm">
                    Cari
                </button>
            </div>
        </form>
    </div>

    <!-- FORM PEMBUATAN DRAF BARANG MASUK DARI PILIHAN CHECKBOX -->
    <form method="POST" action="{{ route('admin.analisis.prefill') }}">
        @csrf
        <input type="hidden" name="periode" value="{{ $periode }}">

        <!-- Action Bar Saat Ada Barang Terpilih -->
        <div class="bg-gray-50 border border-gray-200 p-4 rounded-xl shadow-sm flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                <span class="text-xs text-gray-700">
                    Terpilih: <strong class="font-mono text-gray-900 text-sm tabular-nums" x-text="selectedIds.length">0</strong> barang untuk restok
                </span>
            </div>

            <button type="submit" 
                    :disabled="selectedIds.length === 0"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg font-semibold text-xs transition shadow-sm disabled:opacity-40 disabled:cursor-not-allowed"
                    :class="selectedIds.length > 0 ? 'bg-[#166534] hover:bg-[#14532D] text-white' : 'bg-gray-200 text-gray-500'">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Buat Barang Masuk dari Pilihan</span>
            </button>
        </div>

        <!-- TABEL REKOMENDASI RESTOK -->
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            @php
                $eligibleCheckIds = $items->getCollection()->where('jumlah_rekomendasi', '>', 0)->pluck('barang_id')->all();
            @endphp

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse min-w-[950px]">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50 text-gray-600 font-semibold">
                            <th class="py-3 px-3 text-center w-10">
                                @if (count($eligibleCheckIds) > 0)
                                    <input type="checkbox" 
                                           @change="toggleAll($event.target.checked, {{ Js::from($eligibleCheckIds) }})"
                                           :checked="selectAll"
                                           class="rounded border-gray-300 text-[#166534] focus:ring-[#166534] cursor-pointer" />
                                @endif
                            </th>
                            <th class="py-3 px-4">Kode &amp; Nama Barang</th>
                            <th class="py-3 px-3 text-center">Kelas</th>
                            <th class="py-3 px-3 text-center">Sisa Stok</th>
                            <th class="py-3 px-3 text-center">Jual / Hari</th>
                            <th class="py-3 px-3 text-center">Cakupan</th>
                            <th class="py-3 px-3 text-center">Titik Pesan</th>
                            <th class="py-3 px-4 text-center">Rekomendasi</th>
                            <th class="py-3 px-4 text-right">Estimasi Biaya</th>
                            <th class="py-3 px-4 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($items as $b)
                            <tr class="hover:bg-gray-50/50 transition {{ $b['jumlah_rekomendasi'] > 0 ? 'bg-emerald-50/20' : '' }}">
                                <!-- Checkbox Barang -->
                                <td class="py-3 px-3 text-center">
                                    @if ($b['jumlah_rekomendasi'] > 0)
                                        <input type="checkbox" 
                                               name="barang_ids[]" 
                                               value="{{ $b['barang_id'] }}"
                                               x-model="selectedIds"
                                               class="rounded border-gray-300 text-[#166534] focus:ring-[#166534] cursor-pointer" />
                                    @else
                                        <span class="text-gray-300">-</span>
                                    @endif
                                </td>

                                <!-- Nama & Kode Barang -->
                                <td class="py-3 px-4 text-gray-900">
                                    <div class="font-medium flex items-center gap-2">
                                        <a href="{{ route('admin.barang.show', $b['barang_id']) }}" class="hover:text-[#166534] transition hover:underline">
                                            {{ $b['nama'] }}
                                        </a>
                                        @if ($b['is_margin_tipis'])
                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-red-50 text-red-700 border border-red-200">
                                                Margin Tipis
                                            </span>
                                        @endif
                                        @if ($b['is_stok_berlebih'])
                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-amber-50 text-amber-800 border border-amber-200">
                                                Stok Berlebih
                                            </span>
                                        @endif
                                    </div>
                                    <div class="text-[11px] text-gray-400 font-mono mt-0.5">
                                        {{ $b['kode'] }} • {{ $b['kategori_nama'] }}
                                    </div>
                                    <!-- Catatan Rumus Transparan -->
                                    <p class="text-[11px] text-gray-500 mt-1 italic leading-tight">
                                        {{ $b['catatan_rumus'] }}
                                    </p>
                                </td>

                                <!-- Kelas Pareto -->
                                <td class="py-3 px-3 text-center">
                                    @if ($b['kelas'] === 'fast')
                                        <span class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-[11px] font-semibold">
                                            Fast
                                        </span>
                                    @elseif ($b['kelas'] === 'normal')
                                        <span class="px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 border border-blue-200 text-[11px] font-semibold">
                                            Normal
                                        </span>
                                    @elseif ($b['kelas'] === 'slow')
                                        <span class="px-2 py-0.5 rounded-full bg-amber-50 text-amber-800 border border-amber-200 text-[11px] font-semibold">
                                            Slow
                                        </span>
                                    @elseif ($b['kelas'] === 'tidak_laku')
                                        <span class="px-2 py-0.5 rounded-full bg-red-50 text-red-700 border border-red-200 text-[11px] font-semibold">
                                            Tidak Laku
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full bg-gray-100 text-gray-600 border border-gray-200 text-[11px] font-semibold">
                                            Data &lt; 7hr
                                        </span>
                                    @endif
                                </td>

                                <!-- Sisa Stok -->
                                <td class="py-3 px-3 text-center font-mono font-semibold text-gray-900 tabular-nums">
                                    {{ $b['stok'] }} <span class="text-xs text-gray-500 font-normal">{{ $b['satuan'] }}</span>
                                </td>

                                <!-- Rata-rata Terjual per Hari -->
                                <td class="py-3 px-3 text-center font-mono text-gray-700 tabular-nums">
                                    {{ number_format($b['rata_harian'], 1, ',', '.') }}
                                </td>

                                <!-- Estimasi Habis (Cakupan Hari) -->
                                <td class="py-3 px-3 text-center font-mono tabular-nums">
                                    @if ($b['cakupan_hari'] !== null)
                                        <span class="{{ $b['cakupan_hari'] <= 7 ? 'text-amber-700 font-semibold' : 'text-gray-900' }}">
                                            {{ $b['cakupan_hari'] }} hr
                                        </span>
                                    @else
                                        <span class="text-gray-400 text-xs italic">0 jual</span>
                                    @endif
                                </td>

                                <!-- Titik Pesan -->
                                <td class="py-3 px-3 text-center font-mono text-gray-500 tabular-nums">
                                    {{ $b['titik_pesan'] }}
                                </td>

                                <!-- Rekomendasi Restok -->
                                <td class="py-3 px-4 text-center font-mono tabular-nums">
                                    @if ($b['jumlah_rekomendasi'] > 0)
                                        <span class="text-sm font-bold text-[#166534]">
                                            +{{ $b['jumlah_rekomendasi'] }}
                                        </span>
                                        <span class="text-xs text-[#166534]">{{ $b['satuan'] }}</span>
                                    @else
                                        <span class="text-xs text-gray-400">0</span>
                                    @endif
                                </td>

                                <!-- Estimasi Biaya -->
                                <td class="py-3 px-4 text-right font-mono font-semibold tabular-nums {{ $b['estimasi_biaya'] > 0 ? 'text-gray-900' : 'text-gray-400' }}">
                                    {{ rupiah($b['estimasi_biaya']) }}
                                </td>

                                <!-- Status Rekomendasi -->
                                <td class="py-3 px-4 text-center">
                                    @if ($b['status_rekomendasi'] === 'kulakan_banyak')
                                        <span class="px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-[11px] font-semibold">
                                            Restok Banyak
                                        </span>
                                    @elseif ($b['status_rekomendasi'] === 'kulakan_secukupnya')
                                        <span class="px-2.5 py-1 rounded-full bg-teal-50 text-teal-700 border border-teal-200 text-[11px] font-semibold">
                                            Restok Secukupnya
                                        </span>
                                    @elseif ($b['status_rekomendasi'] === 'tahan_kulakan')
                                        <span class="px-2.5 py-1 rounded-full bg-amber-50 text-amber-800 border border-amber-200 text-[11px] font-semibold">
                                            Tahan Restok
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full bg-gray-100 text-gray-600 border border-gray-200 text-[11px] font-semibold">
                                            Stok Cukup
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="py-12 text-center text-xs text-gray-500">
                                    Tidak ada data barang yang sesuai dengan filter pencarian Anda.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginasi 25 per Halaman -->
            @if ($items->hasPages())
                <div class="p-4 border-t border-gray-200 bg-gray-50">
                    {{ $items->links() }}
                </div>
            @endif
        </div>
    </form>

</div>
</x-layouts.admin>