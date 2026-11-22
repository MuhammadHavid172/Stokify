@extends('layouts.admin')

@section('title', 'Laporan Ringkasan Keuangan - Stokify')

@section('content')
<div class="space-y-6">
    <!-- Header Halaman -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-gray-200">
        <div>
            <div class="flex items-center gap-2 text-xs text-gray-500 mb-1">
                <a href="{{ route('admin.laporan.index') }}" class="hover:text-[#166534] transition">Pusat Laporan</a>
                <span>/</span>
                <span class="text-gray-900 font-semibold">Ringkasan Keuangan</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight text-balance">Laporan Ringkasan Keuangan</h1>
            <p class="text-xs text-gray-500 mt-1 text-pretty">
                Kios: <strong class="text-gray-900">{{ $kepala['nama_kios'] }}</strong> &bull; Dicetak oleh <span class="text-gray-700">{{ $kepala['nama_pencetak'] }}</span> pada {{ $kepala['waktu_cetak'] }}
            </p>
        </div>
    </div>

    <!-- Pemilih Periode Komponen -->
    <x-laporan-filter 
        :periode="$periode" 
        :routeUrl="route('admin.laporan.keuangan')" 
        :pdfUrl="route('admin.laporan.keuangan.pdf', $periode->toQueryParams())" 
    />

    <!-- Kartu Ringkasan Angka Utama -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <!-- 1. Omzet Penjualan -->
        <div class="p-4 sm:p-5 rounded-xl bg-white border border-gray-200 shadow-sm space-y-1">
            <span class="text-xs font-medium text-gray-500 block">Omzet Penjualan</span>
            <div class="text-xl sm:text-2xl font-bold text-gray-900 font-mono tracking-tight truncate tabular-nums">
                {{ rupiah($ringkasan['omzet']) }}
            </div>
            <div class="text-xs text-gray-500">
                Dari <strong class="text-gray-900 font-mono">{{ $ringkasan['jumlah_transaksi'] }}</strong> transaksi selesai
            </div>
        </div>

        <!-- 2. HPP (Modal Barang) -->
        <div class="p-4 sm:p-5 rounded-xl bg-white border border-gray-200 shadow-sm space-y-1">
            <span class="text-xs font-medium text-gray-500 block">HPP (Modal Terjual)</span>
            <div class="text-xl sm:text-2xl font-bold text-gray-900 font-mono tracking-tight truncate tabular-nums">
                {{ rupiah($ringkasan['hpp']) }}
            </div>
            <div class="text-xs text-gray-500">
                Snapshot modal saat penjualan
            </div>
        </div>

        <!-- 3. Laba Kotor -->
        <div class="p-4 sm:p-5 rounded-xl bg-white border border-gray-200 shadow-sm space-y-1">
            <span class="text-xs font-medium text-gray-500 block">Laba Kotor *</span>
            <div class="text-xl sm:text-2xl font-bold text-[#166534] font-mono tracking-tight truncate tabular-nums">
                {{ rupiah($ringkasan['laba_kotor']) }}
            </div>
            <div class="text-xs text-gray-500">
                Omzet dikurangi HPP
            </div>
        </div>

        <!-- 4. Margin Laba Kotor -->
        <div class="p-4 sm:p-5 rounded-xl bg-white border border-gray-200 shadow-sm space-y-1">
            <span class="text-xs font-medium text-gray-500 block">Margin Laba Kotor</span>
            <div class="text-xl sm:text-2xl font-bold text-gray-900 font-mono tracking-tight truncate tabular-nums">
                {{ $ringkasan['margin_laba_kotor'] }}
            </div>
            <div class="text-xs text-gray-500">
                Rata-rata/nota: <strong class="text-gray-900 font-mono tabular-nums">{{ rupiah($ringkasan['rata_rata_transaksi']) }}</strong>
            </div>
        </div>
    </div>

    <!-- Informasi Pendukung & Penyesuaian Kerugian Stok -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Total Barang Masuk -->
        <div class="p-4 rounded-xl bg-white border border-gray-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs text-gray-500 font-medium block">Total Barang Masuk</span>
                <span class="text-base font-bold text-gray-900 font-mono tabular-nums">{{ rupiah($ringkasan['total_kulakan']) }}</span>
            </div>
            <span class="text-xs text-gray-500 bg-gray-100 px-2.5 py-1 rounded-lg">Status selesai</span>
        </div>

        <!-- Kerugian Stok -->
        <div class="p-4 rounded-xl bg-white border border-gray-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs text-gray-500 font-medium block">Kerugian Penyesuaian Stok</span>
                <span class="text-base font-bold text-red-600 font-mono tabular-nums">{{ rupiah($ringkasan['kerugian_stok']) }}</span>
            </div>
            <span class="text-xs text-gray-500 bg-gray-100 px-2.5 py-1 rounded-lg">Rusak/hilang/opname</span>
        </div>

        <!-- Laba Kotor Setelah Kerugian Stok -->
        <div class="p-4 rounded-xl bg-emerald-50/50 border border-emerald-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs text-emerald-800 font-medium block">Laba Kotor Setelah Kerugian Stok **</span>
                <span class="text-base font-bold text-[#166534] font-mono tabular-nums">{{ rupiah($ringkasan['laba_kotor_setelah_kerugian_stok']) }}</span>
            </div>
            <span class="text-xs text-emerald-700 font-semibold bg-emerald-100 px-2 py-0.5 rounded">Riil Stok</span>
        </div>
    </div>

    <!-- Banner Nota Dibatalkan jika ada -->
    @if($ringkasan['jumlah_nota_batal'] > 0)
        <div class="p-3.5 rounded-xl bg-gray-50 border border-gray-200 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs text-gray-600">
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-gray-400"></span>
                <span>Terdapat <strong>{{ $ringkasan['jumlah_nota_batal'] }}</strong> nota dibatalkan senilai <strong class="tabular-nums">{{ rupiah($ringkasan['nilai_nota_batal']) }}</strong> pada periode ini (tidak dihitung dalam omzet).</span>
            </div>
            <a href="{{ route('admin.laporan.audit', $periode->toQueryParams()) }}" class="text-[#166534] hover:underline font-semibold text-xs shrink-0">
                Lihat di Laporan Audit &rarr;
            </a>
        </div>
    @endif

    <!-- Grafik Omzet Harian (Jika periode <= 31 hari) -->
    @if(!empty($grafikHarian) && count($grafikHarian) > 1)
        <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm space-y-3">
            <div class="flex items-center justify-between pb-2 border-b border-gray-200">
                <h3 class="text-xs font-semibold text-gray-700 uppercase tracking-wider">Tren Omzet Harian</h3>
                <span class="text-xs text-gray-500">Visualisasi harian dalam periode</span>
            </div>
            
            <div class="h-32 flex items-end gap-1.5 sm:gap-2 pt-4 px-1 overflow-x-auto">
                @foreach($grafikHarian as $gh)
                    <div class="flex-1 min-w-[20px] sm:min-w-[28px] flex flex-col items-center gap-1.5 group relative">
                        <!-- Tooltip saat hover -->
                        <div class="absolute bottom-full mb-1 hidden group-hover:block z-20 bg-gray-900 text-white rounded-lg p-1.5 text-xs whitespace-nowrap shadow-xl pointer-events-none text-center">
                            <div class="text-gray-300">{{ $gh['tanggal'] }}</div>
                            <div class="text-emerald-300 font-bold font-mono tabular-nums">{{ rupiah($gh['omzet']) }}</div>
                        </div>

                        <!-- Batang Grafik -->
                        <div class="w-full bg-gray-100 rounded-t-lg relative flex items-end justify-center overflow-hidden h-24">
                            <div class="w-full bg-[#166534] rounded-t-lg transition-all duration-300 group-hover:bg-[#14532D]"
                                 style="height: {{ max(4, $gh['persen']) }}%;"></div>
                        </div>
                        <span class="text-[10px] text-gray-500 truncate w-full text-center">{{ $gh['label'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Tabel Rincian per Hari -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-gray-200 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-semibold text-gray-900">Rincian Penjualan per Hari</h3>
                <p class="text-xs text-gray-500 mt-0.5">Penjualan berstatus selesai yang tercatat pada rentang periode</p>
            </div>
            <span class="text-xs text-gray-500 font-mono">{{ count($rincian_harian) }} hari</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse min-w-[600px]">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50 text-gray-600 font-semibold">
                        <th class="py-3 px-4">Tanggal</th>
                        <th class="py-3 px-4 text-center">Transaksi</th>
                        <th class="py-3 px-4 text-right">Omzet</th>
                        <th class="py-3 px-4 text-right">HPP</th>
                        <th class="py-3 px-4 text-right">Laba Kotor</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($rincian_harian as $row)
                        <tr class="hover:bg-gray-50/50 transition {{ $row['jumlah_transaksi'] > 0 ? '' : 'opacity-60' }}">
                            <td class="py-2.5 px-4 text-gray-900 font-medium">
                                {{ $row['tanggal_label'] }}
                                <span class="text-[11px] text-gray-400 block font-mono">{{ $row['tanggal'] }}</span>
                            </td>
                            <td class="py-2.5 px-4 text-center font-mono text-gray-700 tabular-nums">
                                {{ $row['jumlah_transaksi'] }}
                            </td>
                            <td class="py-2.5 px-4 text-right font-mono text-gray-900 font-semibold tabular-nums">
                                {{ rupiah($row['omzet']) }}
                            </td>
                            <td class="py-2.5 px-4 text-right font-mono text-gray-600 tabular-nums">
                                {{ rupiah($row['hpp']) }}
                            </td>
                            <td class="py-2.5 px-4 text-right font-mono text-[#166534] font-bold tabular-nums">
                                {{ rupiah($row['laba_kotor']) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-xs text-gray-500">
                                Tidak ada transaksi penjualan pada periode ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <!-- Baris Total -->
                @if(count($rincian_harian) > 0)
                    <tfoot>
                        <tr class="border-t-2 border-gray-200 bg-gray-50 font-bold text-xs text-gray-900">
                            <td class="py-3 px-4">TOTAL PERIODE</td>
                            <td class="py-3 px-4 text-center font-mono tabular-nums">{{ $total_rincian['jumlah_transaksi'] }}</td>
                            <td class="py-3 px-4 text-right font-mono text-sm tabular-nums">{{ rupiah($total_rincian['omzet']) }}</td>
                            <td class="py-3 px-4 text-right font-mono text-gray-700 tabular-nums">{{ rupiah($total_rincian['hpp']) }}</td>
                            <td class="py-3 px-4 text-right font-mono text-[#166534] text-sm tabular-nums">{{ rupiah($total_rincian['laba_kotor']) }}</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

    <!-- Rekap Kasir & Top 10 Barang -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Rekap Performa Kasir (1 Kolom) -->
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden flex flex-col justify-between">
            <div>
                <div class="p-4 sm:p-5 border-b border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-900">Rekap Kasir</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Kontribusi transaksi per kasir</p>
                </div>

                <div class="divide-y divide-gray-100">
                    @forelse($rekap_kasir as $rk)
                        <div class="p-4 flex items-center justify-between text-xs hover:bg-gray-50/50 transition">
                            <div class="space-y-0.5">
                                <strong class="text-gray-900 block font-semibold">{{ $rk['nama_kasir'] }}</strong>
                                <span class="text-xs text-gray-500 font-mono">{{ $rk['jumlah_transaksi'] }} transaksi</span>
                            </div>
                            <div class="text-right">
                                <span class="font-mono text-gray-900 font-semibold block tabular-nums">{{ rupiah($rk['omzet']) }}</span>
                                @if($ringkasan['omzet'] > 0)
                                    <span class="text-[11px] text-[#166534] font-mono tabular-nums">
                                        {{ round(($rk['omzet'] / $ringkasan['omzet']) * 100, 1) }}% omzet
                                    </span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="p-6 text-center text-xs text-gray-500">
                            Tidak ada transaksi kasir pada periode ini.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- 10 Barang Terlaris & 10 Laba Terbesar (2 Kolom) -->
        <div class="lg:col-span-2 space-y-6">
            <!-- 10 Paling Laku -->
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                <div class="p-4 sm:p-5 border-b border-gray-200 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900">10 Barang Paling Banyak Terjual</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Berdasarkan volume kuantitas penjualan selesai</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse min-w-[450px]">
                        <thead>
                            <tr class="border-b border-gray-200 bg-gray-50 text-gray-600 font-semibold">
                                <th class="py-2.5 px-4">Nama Barang</th>
                                <th class="py-2.5 px-4 text-center">Terjual</th>
                                <th class="py-2.5 px-4 text-right">Omzet</th>
                                <th class="py-2.5 px-4 text-right">Laba Kotor</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($sepuluh_paling_laku as $b)
                                <tr class="hover:bg-gray-50/50 transition">
                                    <td class="py-2 px-4 text-gray-900">
                                        <div class="font-medium">{{ $b['nama'] }}</div>
                                        <span class="text-[11px] text-gray-400 font-mono">{{ $b['kode'] }}</span>
                                    </td>
                                    <td class="py-2 px-4 text-center font-mono text-[#166534] font-semibold tabular-nums">
                                        {{ $b['jumlah'] }} <span class="text-xs text-gray-500 font-normal">{{ $b['satuan'] }}</span>
                                    </td>
                                    <td class="py-2 px-4 text-right font-mono text-gray-900 tabular-nums">
                                        {{ rupiah($b['omzet']) }}
                                    </td>
                                    <td class="py-2 px-4 text-right font-mono text-[#166534] font-semibold tabular-nums">
                                        {{ rupiah($b['laba_kotor']) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-6 text-center text-xs text-gray-500">
                                        Tidak ada penjualan pada periode ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 10 Laba Terbesar -->
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                <div class="p-4 sm:p-5 border-b border-gray-200 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900">10 Barang dengan Laba Kotor Terbesar</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Barang paling menguntungkan (omzet dikurangi modal snapshot)</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse min-w-[450px]">
                        <thead>
                            <tr class="border-b border-gray-200 bg-gray-50 text-gray-600 font-semibold">
                                <th class="py-2.5 px-4">Nama Barang</th>
                                <th class="py-2.5 px-4 text-center">Terjual</th>
                                <th class="py-2.5 px-4 text-right">Omzet</th>
                                <th class="py-2.5 px-4 text-right">Laba Kotor</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($sepuluh_laba_terbesar as $b)
                                <tr class="hover:bg-gray-50/50 transition">
                                    <td class="py-2 px-4 text-gray-900">
                                        <div class="font-medium">{{ $b['nama'] }}</div>
                                        <span class="text-[11px] text-gray-400 font-mono">{{ $b['kode'] }}</span>
                                    </td>
                                    <td class="py-2 px-4 text-center font-mono text-gray-700 tabular-nums">
                                        {{ $b['jumlah'] }} <span class="text-xs text-gray-500 font-normal">{{ $b['satuan'] }}</span>
                                    </td>
                                    <td class="py-2 px-4 text-right font-mono text-gray-900 tabular-nums">
                                        {{ rupiah($b['omzet']) }}
                                    </td>
                                    <td class="py-2 px-4 text-right font-mono text-[#166534] font-bold tabular-nums">
                                        {{ rupiah($b['laba_kotor']) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-6 text-center text-xs text-gray-500">
                                        Tidak ada penjualan pada periode ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Catatan Penjelasan Resmi Keuangan -->
    <div class="bg-white border border-gray-200 rounded-2xl p-4 sm:p-5 shadow-xs space-y-3 teks-penjelasan">
        <div class="flex items-center gap-2 pb-2.5 border-b border-gray-100">
            <div class="w-6 h-6 rounded-lg bg-[#DCFCE7] text-[#166534] flex items-center justify-center shrink-0">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h4 class="text-xs font-bold text-gray-900 uppercase tracking-wider">Catatan Penjelasan Perhitungan Finansial</h4>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
            <!-- Penjelasan * -->
            <div class="p-3.5 rounded-xl bg-gray-50/80 border border-gray-200/70 flex items-start gap-3">
                <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-emerald-100 text-[#166534] font-bold text-xs shrink-0 font-mono mt-0.5">
                    *
                </span>
                <div class="text-xs text-gray-600 leading-relaxed">
                    <strong class="text-gray-900 block font-semibold mb-0.5">Laba Kotor Penjualan</strong>
                    <p>{{ $catatan_kaki['laba_kotor'] }}</p>
                </div>
            </div>

            <!-- Penjelasan ** -->
            <div class="p-3.5 rounded-xl bg-gray-50/80 border border-gray-200/70 flex items-start gap-3">
                <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-emerald-100 text-[#166534] font-bold text-xs shrink-0 font-mono mt-0.5">
                    **
                </span>
                <div class="text-xs text-gray-600 leading-relaxed">
                    <strong class="text-gray-900 block font-semibold mb-0.5">Penyesuaian Kerugian Fisik</strong>
                    <p>{{ $catatan_kaki['laba_setelah_kerugian'] }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection