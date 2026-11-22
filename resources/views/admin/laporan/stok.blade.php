@extends('layouts.admin')

@section('title', 'Laporan Mutasi & Sisa Stok - Stokify')

@section('content')
<div class="space-y-6">
    <!-- Header Halaman -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-gray-200">
        <div>
            <div class="flex items-center gap-2 text-xs text-gray-500 mb-1">
                <a href="{{ route('admin.laporan.index') }}" class="hover:text-[#166534] transition">Pusat Laporan</a>
                <span>/</span>
                <span class="text-gray-900 font-semibold">Mutasi & Sisa Stok</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight text-balance">Laporan Mutasi & Sisa Stok</h1>
            <p class="text-xs text-gray-500 mt-1 text-pretty">
                Kios: <strong class="text-gray-900">{{ $kepala['nama_kios'] }}</strong> &bull; Dicetak oleh <span class="text-gray-700">{{ $kepala['nama_pencetak'] }}</span> pada {{ $kepala['waktu_cetak'] }}
            </p>
        </div>
    </div>

    <!-- Pemilih Periode & Filter Komponen -->
    <x-laporan-filter 
        :periode="$periode" 
        :routeUrl="route('admin.laporan.stok')" 
        :pdfUrl="route('admin.laporan.stok.pdf', array_merge($periode->toQueryParams(), $filters))" 
        :showAdditionalFilters="true"
        :kategoriOptions="$kategoriOptions"
        :filters="$filters"
    />

    <!-- Kartu Ringkasan Stok -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <!-- 1. Jumlah Jenis Barang -->
        <div class="p-4 sm:p-5 rounded-xl bg-white border border-gray-200 shadow-sm space-y-1">
            <span class="text-xs font-medium text-gray-500 block">Jenis Barang Terdaftar</span>
            <div class="text-xl sm:text-2xl font-bold text-gray-900 font-mono tabular-nums">
                {{ $ringkasan['jumlah_jenis_barang'] }} <span class="text-xs text-gray-500 font-normal">barang</span>
            </div>
            <div class="text-xs text-gray-500">
                Sesuai filter terpilih
            </div>
        </div>

        <!-- 2. Total Unit Masuk -->
        <div class="p-4 sm:p-5 rounded-xl bg-white border border-gray-200 shadow-sm space-y-1">
            <span class="text-xs font-medium text-gray-500 block">Total Unit Masuk **</span>
            <div class="text-xl sm:text-2xl font-bold text-gray-900 font-mono tabular-nums">
                +{{ number_format($ringkasan['total_unit_masuk'], 0, ',', '.') }}
            </div>
            <div class="text-xs text-gray-500">
                Barang masuk & saldo awal baru
            </div>
        </div>

        <!-- 3. Total Unit Terjual -->
        <div class="p-4 sm:p-5 rounded-xl bg-white border border-gray-200 shadow-sm space-y-1">
            <span class="text-xs font-medium text-gray-500 block">Total Unit Terjual</span>
            <div class="text-xl sm:text-2xl font-bold text-[#166534] font-mono tabular-nums">
                {{ number_format($ringkasan['total_unit_terjual'], 0, ',', '.') }}
            </div>
            <div class="text-xs text-gray-500">
                Volume penjualan kasir
            </div>
        </div>

        <!-- 4. Nilai Persediaan Akhir -->
        <div class="p-4 sm:p-5 rounded-xl bg-white border border-gray-200 shadow-sm space-y-1">
            <span class="text-xs font-medium text-gray-500 block">Nilai Persediaan Akhir *</span>
            <div class="text-xl sm:text-2xl font-bold text-gray-900 font-mono truncate tabular-nums">
                {{ rupiah($ringkasan['nilai_persediaan_akhir']) }}
            </div>
            <div class="text-xs text-gray-500">
                Stok akhir &times; modal terkini
            </div>
        </div>
    </div>

    <!-- Tabel Mutasi dan Sisa Stok -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-gray-200 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-semibold text-gray-900">Tabel Mutasi & Sisa Stok Barang</h3>
                <p class="text-xs text-gray-500 mt-0.5">Dihitung murni dari kronologi mutasi stok sistem</p>
            </div>
            <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-gray-100 text-gray-600 font-mono">
                Menampilkan {{ $paginated_items->count() }} dari {{ $paginated_items->total() }} barang
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse min-w-[950px]">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50 text-gray-600 font-semibold">
                        <th class="py-3 px-4">Barang</th>
                        <th class="py-3 px-4">Kategori</th>
                        <th class="py-3 px-3 text-center">Stok Awal</th>
                        <th class="py-3 px-3 text-center">Masuk</th>
                        <th class="py-3 px-3 text-center text-[#166534]">Terjual</th>
                        <th class="py-3 px-3 text-center">Retur</th>
                        <th class="py-3 px-3 text-center">Penyesuaian</th>
                        <th class="py-3 px-3 text-center">Stok Akhir</th>
                        <th class="py-3 px-3 text-center">Min.</th>
                        <th class="py-3 px-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($paginated_items as $b)
                        <tr class="hover:bg-gray-50/50 transition {{ !$b['is_aktif'] ? 'opacity-60' : '' }}">
                            <td class="py-2.5 px-4 text-gray-900">
                                <div class="font-semibold">{{ $b['nama'] }}</div>
                                <div class="flex items-center gap-2 mt-0.5">
                                    <span class="text-[11px] text-gray-400 font-mono">{{ $b['kode'] }}</span>
                                    <span class="text-[11px] text-gray-500">({{ $b['satuan'] }})</span>
                                    @if(!$b['is_aktif'])
                                        <span class="text-[10px] text-gray-500 bg-gray-100 px-1.5 py-0.5 rounded">Nonaktif</span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-2.5 px-4 text-gray-700">
                                {{ $b['kategori'] }}
                            </td>
                            <td class="py-2.5 px-3 text-center font-mono text-gray-700 tabular-nums">
                                {{ $b['stok_awal'] }}
                            </td>
                            <td class="py-2.5 px-3 text-center font-mono font-medium text-gray-900 tabular-nums">
                                {{ $b['masuk'] > 0 ? '+' . $b['masuk'] : '0' }}
                            </td>
                            <td class="py-2.5 px-3 text-center font-mono font-semibold text-[#166534] tabular-nums">
                                {{ $b['terjual'] }}
                            </td>
                            <td class="py-2.5 px-3 text-center font-mono text-gray-700 tabular-nums">
                                {{ $b['retur_void'] > 0 ? '+' . $b['retur_void'] : '0' }}
                            </td>
                            <td class="py-2.5 px-3 text-center font-mono tabular-nums {{ $b['penyesuaian'] < 0 ? 'text-red-600' : ($b['penyesuaian'] > 0 ? 'text-[#166534]' : 'text-gray-400') }}">
                                {{ $b['penyesuaian'] > 0 ? '+' . $b['penyesuaian'] : $b['penyesuaian'] }}
                            </td>
                            <td class="py-2.5 px-3 text-center font-mono font-bold text-sm tabular-nums {{ $b['stok_akhir'] <= 0 ? 'text-red-600' : 'text-gray-900' }}">
                                {{ $b['stok_akhir'] }}
                            </td>
                            <td class="py-2.5 px-3 text-center font-mono text-gray-500 tabular-nums">
                                {{ $b['stok_minimum'] }}
                            </td>
                            <td class="py-2.5 px-4 text-center">
                                <div class="flex items-center justify-center gap-1 flex-wrap">
                                    @if($b['perlu_kulakan'])
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-semibold bg-red-50 text-red-700 border border-red-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-red-600 animate-pulse"></span>
                                            Perlu Restok
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded text-[11px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            Aman
                                        </span>
                                    @endif

                                    @if(!$b['is_sinkron'])
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-amber-50 text-amber-800 border border-amber-200" title="Stok akhir periode ({{ $b['stok_akhir'] }}) berbeda dengan stok saat ini ({{ $b['stok_sistem_saat_ini'] }})">
                                            Tidak Sinkron
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="py-8 text-center text-xs text-gray-500">
                                Tidak ada data stok yang sesuai dengan filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginasi Layar -->
        @if($paginated_items->hasPages())
            <div class="p-4 border-t border-gray-200 bg-gray-50">
                {{ $paginated_items->links() }}
            </div>
        @endif
    </div>

    <!-- Bagian Akhir: Daftar Belanja Stok (Panduan Pengadaan Berikutnya) -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-gray-200 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-lg bg-red-50 border border-red-100 text-red-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-900">Daftar Belanja Stok (Stok Menipis / Habis)</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Panduan belanja stok barang yang stok akhirnya berada di bawah atau sama dengan batas minimum</p>
                </div>
            </div>
            <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-red-50 text-red-700 font-mono">
                {{ count($daftar_kulakan) }} barang
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse min-w-[500px]">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50 text-gray-600 font-semibold">
                        <th class="py-2.5 px-4">Kode</th>
                        <th class="py-2.5 px-4">Nama Barang</th>
                        <th class="py-2.5 px-4 text-center">Stok Akhir</th>
                        <th class="py-2.5 px-4 text-center">Batas Minimum</th>
                        <th class="py-2.5 px-4 text-center text-red-600">Minimal Tambahan Restok</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($daftar_kulakan as $dk)
                        <tr class="hover:bg-gray-50/50 transition">
                            <td class="py-2 px-4 font-mono font-semibold text-gray-900">
                                {{ $dk['kode'] }}
                            </td>
                            <td class="py-2 px-4 text-gray-900 font-medium">
                                {{ $dk['nama'] }}
                            </td>
                            <td class="py-2 px-4 text-center font-mono font-bold text-red-600 tabular-nums">
                                {{ $dk['stok_akhir'] }} {{ $dk['satuan'] }}
                            </td>
                            <td class="py-2 px-4 text-center font-mono text-gray-500 tabular-nums">
                                {{ $dk['stok_minimum'] }} {{ $dk['satuan'] }}
                            </td>
                            <td class="py-2 px-4 text-center font-mono font-bold text-red-600 tabular-nums">
                                +{{ $dk['kekurangan'] }} {{ $dk['satuan'] }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-6 text-center text-xs text-[#166534]">
                                Seluruh stok barang dalam kondisi aman di atas batas minimum.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Catatan Kaki Stok -->
    <div class="p-4 rounded-xl bg-gray-50 border border-gray-200 text-xs text-gray-600 space-y-1 teks-penjelasan">
        <p>* {{ $catatan_kaki['persediaan'] }}</p>
        <p>** {{ $catatan_kaki['masuk'] }}</p>
    </div>
</div>
@endsection