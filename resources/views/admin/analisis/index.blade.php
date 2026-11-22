<x-layouts.admin title="Analisis Performa Barang - Stokify">
<div class="space-y-6">

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
                <span class="text-gray-900 font-semibold">Ringkasan Performa</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight text-balance">Analisis Performa Barang</h1>
            <p class="text-xs text-gray-500 mt-1 text-pretty">
                Klasifikasi perputaran barang (Fast, Normal, Slow, Tidak Laku) berdasarkan frekuensi penjualan riil di kios {{ $kios->nama_kios }}.
            </p>
        </div>

        <!-- Tombol Tab Navigasi -->
        <div class="flex items-center bg-gray-100 p-1 rounded-lg border border-gray-200 self-start sm:self-auto">
            <a href="{{ route('admin.analisis.index', ['periode' => $periode]) }}" 
               class="px-3.5 py-1.5 rounded-md text-xs font-semibold transition bg-white text-gray-900 shadow-sm">
                Ringkasan Performa
            </a>
            <a href="{{ route('admin.analisis.rekomendasi', ['periode' => $periode]) }}" 
               class="px-3.5 py-1.5 rounded-md text-xs font-medium transition text-gray-600 hover:text-gray-900 hover:bg-white/60">
                Rekomendasi Restok
            </a>
            <a href="{{ route('admin.analisis.pengaturan') }}" 
               class="px-3.5 py-1.5 rounded-md text-xs font-medium transition text-gray-600 hover:text-gray-900 hover:bg-white/60">
                Pengaturan
            </a>
        </div>
    </div>

    <!-- Pemilih Periode & Penjelasan Rumus (Alpine.js) -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white border border-gray-200 p-4 rounded-xl shadow-sm"
         x-data="{ showPanduan: false }">
        <div class="flex flex-wrap items-center gap-2">
            <span class="text-xs font-semibold text-gray-700 uppercase tracking-wider mr-2">Rentang Waktu:</span>
            @foreach([7, 14, 30, 60, 90] as $hari)
                <a href="{{ route('admin.analisis.index', ['periode' => $hari]) }}"
                   class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ $periode === $hari ? 'bg-[#166534] text-white shadow-sm' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                    {{ $hari }} Hari Terakhir
                </a>
            @endforeach
        </div>

        <button @click="showPanduan = !showPanduan" 
                type="button"
                class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#166534] hover:underline transition">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span x-text="showPanduan ? 'Tutup Cara Perhitungan' : 'Cara Perhitungan & Rumus'"></span>
        </button>

        <!-- Kotak Penjelasan Rumus (Collapsible) -->
        <div x-show="showPanduan" 
             x-cloak 
             class="w-full mt-3 pt-3 border-t border-gray-200 text-xs text-gray-600 space-y-2 leading-relaxed teks-penjelasan">
            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 space-y-2">
                <p class="font-semibold text-gray-900">Aturan & Dasar Perhitungan Analisis:</p>
                <ul class="list-disc pl-5 space-y-1">
                    <li><strong>Sumber Data:</strong> Dihitung murni dari snapshot <code>detail_penjualan</code> pada nota berstatus <strong>selesai</strong> (nota dibatalkan otomatis tidak dihitung).</li>
                    <li><strong>Prinsip Pareto:</strong> Klasifikasi menggunakan akumulasi frekuensi nota (bukan volume kuantitas) agar satuan seperti kg, liter, dus, dan pcs dapat dibandingkan secara adil dan objektif.</li>
                    <li><strong>Batas Pareto:</strong> Kumulatif frekuensi sebelum barang &lt; 80% = <span class="text-emerald-700 font-bold">Fast Moving</span>; &lt; 95% = <span class="text-blue-700 font-bold">Normal</span>; sisanya = <span class="text-amber-700 font-bold">Slow Moving</span>.</li>
                    <li><strong>Tidak Laku (Dead Stock):</strong> Barang aktif dengan data cukup (&ge; 7 hari), frekuensi 0 nota, dan memiliki sisa stok &gt; 0.</li>
                    <li><strong>Data Belum Cukup:</strong> Barang yang baru dibuat kurang dari 7 hari lalu ditandai agar tidak salah diklasifikasikan sebagai barang lambat.</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- 5 KARTU METRIK ANGKA PERFORMA BARANG -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3.5 sm:gap-4">
        <!-- Fast Moving -->
        <div class="bg-white border border-gray-200 rounded-xl p-4 sm:p-5 flex flex-col justify-between shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-gray-500">Fast Moving</span>
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
            </div>
            <div class="mt-3">
                <div class="text-2xl sm:text-3xl font-bold text-gray-900 font-mono tabular-nums">{{ $ringkasan['jumlah_fast'] }}</div>
                <span class="text-xs text-gray-500 block mt-0.5">Kumulatif &lt; 80%</span>
            </div>
        </div>

        <!-- Normal -->
        <div class="bg-white border border-gray-200 rounded-xl p-4 sm:p-5 flex flex-col justify-between shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-gray-500">Normal</span>
                <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
            </div>
            <div class="mt-3">
                <div class="text-2xl sm:text-3xl font-bold text-gray-900 font-mono tabular-nums">{{ $ringkasan['jumlah_normal'] }}</div>
                <span class="text-xs text-gray-500 block mt-0.5">Kumulatif 80% - 95%</span>
            </div>
        </div>

        <!-- Slow Moving -->
        <div class="bg-white border border-gray-200 rounded-xl p-4 sm:p-5 flex flex-col justify-between shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-gray-500">Slow Moving</span>
                <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
            </div>
            <div class="mt-3">
                <div class="text-2xl sm:text-3xl font-bold text-gray-900 font-mono tabular-nums">{{ $ringkasan['jumlah_slow'] }}</div>
                <span class="text-xs text-gray-500 block mt-0.5">Kumulatif &ge; 95%</span>
            </div>
        </div>

        <!-- Tidak Laku -->
        <div class="bg-white border border-gray-200 rounded-xl p-4 sm:p-5 flex flex-col justify-between shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-gray-500">Tidak Laku</span>
                <span class="w-2.5 h-2.5 rounded-full bg-red-500"></span>
            </div>
            <div class="mt-3">
                <div class="text-2xl sm:text-3xl font-bold text-gray-900 font-mono tabular-nums">{{ $ringkasan['jumlah_tidak_laku'] }}</div>
                <span class="text-xs text-gray-500 block mt-0.5">0 penjualan, stok &gt; 0</span>
            </div>
        </div>

        <!-- Modal Tertahan Masalah -->
        <div class="col-span-2 lg:col-span-1 bg-white border border-gray-200 rounded-xl p-4 sm:p-5 flex flex-col justify-between shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-gray-500">Modal Tertahan</span>
                <div class="w-5 h-5 rounded bg-amber-50 text-amber-700 flex items-center justify-center text-[10px] font-bold">
                    Rp
                </div>
            </div>
            <div class="mt-3">
                <div class="text-lg sm:text-xl font-bold text-gray-900 font-mono truncate tabular-nums">
                    {{ rupiah($ringkasan['total_modal_tertahan_masalah']) }}
                </div>
                <span class="text-xs text-gray-500 block mt-0.5">Slow, Tidak Laku, &amp; Berlebih</span>
            </div>
        </div>
    </div>

    @if ($sepuluhTerlaris->isEmpty())
        <!-- EMPTY STATE BILA BELUM ADA PENJUALAN SAMA SEKALI PADA PERIODE -->
        <div class="bg-white border border-gray-200 rounded-xl p-12 text-center shadow-sm space-y-4">
            <div class="w-16 h-16 rounded-xl bg-gray-50 border border-gray-200 flex items-center justify-center mx-auto text-gray-400">
                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                </svg>
            </div>
            <div class="max-w-md mx-auto space-y-1">
                <h3 class="text-base font-semibold text-gray-900">Belum Ada Penjualan Pada Periode Ini</h3>
                <p class="text-xs text-gray-500">
                    Tidak ditemukan transaksi penjualan berstatus selesai dalam {{ $periode }} hari terakhir di kios {{ $kios->nama_kios }}.
                </p>
            </div>
            <div class="pt-2">
                <a href="{{ route('kasir.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-[#166534] text-white text-xs font-semibold hover:bg-[#14532D] transition shadow-sm">
                    Buka Kasir Penjualan
                </a>
            </div>
        </div>
    @else

        <!-- DAFTAR 10 BARANG TERLARIS DENGAN BATANG PROPORSI SEDERHANA -->
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <div class="p-4 sm:p-5 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <div>
                    <h2 class="text-sm font-semibold text-gray-900">10 Barang Terlaris ({{ $periode }} Hari Terakhir)</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Daftar barang dengan frekuensi pembelian tertinggi pada nota kasir selesai.</p>
                </div>
                <span class="text-xs font-mono text-gray-600 bg-gray-100 px-3 py-1 rounded-full">
                    Total Frekuensi Terbanyak: {{ $sepuluhTerlaris->first()['frekuensi'] ?? 0 }} nota
                </span>
            </div>

            @php
                $maxFrekuensi = max(1, (int) ($sepuluhTerlaris->first()['frekuensi'] ?? 1));
            @endphp

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse min-w-[700px]">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50 text-gray-600 font-semibold">
                            <th class="py-3 px-4">Nama Barang</th>
                            <th class="py-3 px-4">Klasifikasi</th>
                            <th class="py-3 px-4 text-center">Frekuensi Nota</th>
                            <th class="py-3 px-4 text-center">Terjual Netto</th>
                            <th class="py-3 px-4 text-right">Omzet Penjualan</th>
                            <th class="py-3 px-4 text-right">Laba Kotor</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($sepuluhTerlaris as $b)
                            @php
                                $barPercent = min(100, max(5, round(($b['frekuensi'] / $maxFrekuensi) * 100)));
                            @endphp
                            <tr class="hover:bg-gray-50/50 transition">
                                <td class="py-3 px-4 text-gray-900">
                                    <div class="font-medium flex items-center gap-2">
                                        <span>{{ $b['nama'] }}</span>
                                    </div>
                                    <span class="text-[11px] text-gray-400 font-mono">{{ $b['kode'] }} • {{ $b['kategori_nama'] }}</span>
                                </td>
                                <td class="py-3 px-4">
                                    @if ($b['kelas'] === 'fast')
                                        <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            Fast Moving
                                        </span>
                                    @elseif ($b['kelas'] === 'normal')
                                        <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                            Normal
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-amber-50 text-amber-800 border border-amber-200">
                                            Slow Moving
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex items-center justify-center gap-2">
                                        <div class="w-24 bg-gray-100 rounded-full h-2 overflow-hidden">
                                            <div class="bg-[#166534] h-2 rounded-full" 
                                                 style="width: {{ $barPercent }}%"></div>
                                        </div>
                                        <span class="font-mono text-gray-900 font-semibold tabular-nums">{{ $b['frekuensi'] }}</span>
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-center font-mono text-gray-700 tabular-nums">
                                    {{ $b['terjual_netto'] }} <span class="text-xs text-gray-500">{{ $b['satuan'] }}</span>
                                </td>
                                <td class="py-3 px-4 text-right font-mono text-gray-900 font-semibold tabular-nums">
                                    {{ rupiah($b['omzet']) }}
                                </td>
                                <td class="py-3 px-4 text-right font-mono text-[#166534] font-semibold tabular-nums">
                                    {{ rupiah($b['laba_kotor']) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 2 KOLOM: BARANG KURANG LAKU & MARGIN TIPIS -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <!-- DAFTAR BARANG KURANG LAKU (SLOW MOVING & TIDAK LAKU) -->
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden flex flex-col justify-between">
                <div>
                    <div class="p-4 sm:p-5 border-b border-gray-200 flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-semibold text-gray-900">Barang Kurang Laku (Perlu Evaluasi)</h3>
                            <p class="text-xs text-gray-500 mt-0.5">Klasifikasi Slow Moving dan Tidak Laku dengan modal tertahan tinggi.</p>
                        </div>
                        <span class="text-xs font-mono text-amber-800 bg-amber-50 px-2.5 py-0.5 rounded-full border border-amber-200 font-semibold">
                            {{ $barangKurangLaku->count() }} barang
                        </span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs border-collapse min-w-[450px]">
                            <thead>
                                <tr class="border-b border-gray-200 bg-gray-50 text-gray-600 font-semibold">
                                    <th class="py-2.5 px-4">Nama Barang</th>
                                    <th class="py-2.5 px-4 text-center">Sisa Stok</th>
                                    <th class="py-2.5 px-4 text-center">Cakupan</th>
                                    <th class="py-2.5 px-4 text-right">Modal Tertahan</th>
                                    <th class="py-2.5 px-4 text-right">Saran</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse ($barangKurangLaku->take(10) as $b)
                                    <tr class="hover:bg-gray-50/50 transition">
                                        <td class="py-2.5 px-4 text-gray-900">
                                            <div class="font-medium">{{ $b['nama'] }}</div>
                                            <span class="text-[11px] text-gray-400 font-mono">{{ $b['kode'] }}</span>
                                        </td>
                                        <td class="py-2.5 px-4 text-center font-mono text-gray-900 tabular-nums">
                                            {{ $b['stok'] }} {{ $b['satuan'] }}
                                        </td>
                                        <td class="py-2.5 px-4 text-center font-mono text-gray-600 tabular-nums">
                                            {{ $b['cakupan_hari'] !== null ? $b['cakupan_hari'] . ' hari' : '-' }}
                                        </td>
                                        <td class="py-2.5 px-4 text-right font-mono text-gray-900 font-semibold tabular-nums">
                                            {{ rupiah($b['modal_tertahan']) }}
                                        </td>
                                        <td class="py-2.5 px-4 text-right">
                                            <span class="text-[11px] font-semibold text-red-700 bg-red-50 px-2 py-0.5 rounded border border-red-200">
                                                Kurangi / Tahan Restok
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-8 text-center text-xs text-gray-500">
                                            Bagus! Tidak ada barang lambat atau tidak laku yang menahan modal persediaan.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- DAFTAR BARANG MARGIN TIPIS -->
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden flex flex-col justify-between">
                <div>
                    <div class="p-4 sm:p-5 border-b border-gray-200 flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-semibold text-gray-900">Barang Margin Tipis (&lt; {{ $kios->margin_tipis_persen ?? 10 }}%)</h3>
                            <p class="text-xs text-gray-500 mt-0.5">Barang berputar namun persentase margin keuntungan terhadap harga jual sangat rendah.</p>
                        </div>
                        <span class="text-xs font-mono text-red-700 bg-red-50 px-2.5 py-0.5 rounded-full border border-red-200 font-semibold">
                            {{ $barangMarginTipis->count() }} barang
                        </span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs border-collapse min-w-[450px]">
                            <thead>
                                <tr class="border-b border-gray-200 bg-gray-50 text-gray-600 font-semibold">
                                    <th class="py-2.5 px-4">Nama Barang</th>
                                    <th class="py-2.5 px-4 text-center">Margin %</th>
                                    <th class="py-2.5 px-4 text-right">Harga Modal</th>
                                    <th class="py-2.5 px-4 text-right">Harga Jual</th>
                                    <th class="py-2.5 px-4 text-center">Frekuensi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse ($barangMarginTipis->take(10) as $b)
                                    <tr class="hover:bg-gray-50/50 transition">
                                        <td class="py-2.5 px-4 text-gray-900">
                                            <div class="font-medium">{{ $b['nama'] }}</div>
                                            <span class="text-[11px] text-gray-400 font-mono">{{ $b['kode'] }}</span>
                                        </td>
                                        <td class="py-2.5 px-4 text-center font-mono font-bold text-red-600 tabular-nums">
                                            {{ $b['margin_persen'] !== null ? $b['margin_persen'] . '%' : '-' }}
                                        </td>
                                        <td class="py-2.5 px-4 text-right font-mono text-gray-600 tabular-nums">
                                            {{ rupiah($b['harga_modal']) }}
                                        </td>
                                        <td class="py-2.5 px-4 text-right font-mono text-gray-900 tabular-nums">
                                            {{ rupiah($b['harga_jual']) }}
                                        </td>
                                        <td class="py-2.5 px-4 text-center font-mono text-gray-700 tabular-nums">
                                            {{ $b['frekuensi'] }} nota
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-8 text-center text-xs text-gray-500">
                                            Tidak ada barang dengan margin di bawah {{ $kios->margin_tipis_persen ?? 10 }}%. Seluruh margin barang berada pada tingkat wajar.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>

    @endif

</div>
</x-layouts.admin>