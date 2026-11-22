@extends('layouts.admin')

@section('title', 'Laporan Audit Transaksi & Shift - Stokify')

@section('content')
<div class="space-y-6">
    <!-- Header Halaman -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-gray-200">
        <div>
            <div class="flex items-center gap-2 text-xs text-gray-500 mb-1">
                <a href="{{ route('admin.laporan.index') }}" class="hover:text-[#166534] transition">Pusat Laporan</a>
                <span>/</span>
                <span class="text-gray-900 font-semibold">Audit Transaksi & Shift</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight text-balance">Laporan Audit Transaksi & Shift</h1>
            <p class="text-xs text-gray-500 mt-1 text-pretty">
                Kios: <strong class="text-gray-900">{{ $kepala['nama_kios'] }}</strong> &bull; Dicetak oleh <span class="text-gray-700">{{ $kepala['nama_pencetak'] }}</span> pada {{ $kepala['waktu_cetak'] }}
            </p>
        </div>
    </div>

    <!-- Pemilih Periode Komponen -->
    <x-laporan-filter 
        :periode="$periode" 
        :routeUrl="route('admin.laporan.audit')" 
        :pdfUrl="route('admin.laporan.audit.pdf', $periode->toQueryParams())" 
    />

    <!-- Ringkasan Angka Audit -->
    <div class="grid grid-cols-2 lg:grid-cols-6 gap-3 sm:gap-4">
        <!-- 1. Nota Selesai -->
        <div class="p-4 rounded-xl bg-white border border-gray-200 shadow-sm space-y-1">
            <span class="text-xs font-medium text-gray-500 block">Nota Selesai</span>
            <div class="text-xl font-bold text-gray-900 font-mono tabular-nums">
                {{ $ringkasan['jumlah_nota_selesai'] }}
            </div>
            <span class="text-xs text-gray-500">Transaksi sukses</span>
        </div>

        <!-- 2. Nota Batal -->
        <div class="p-4 rounded-xl bg-white border border-gray-200 shadow-sm space-y-1">
            <span class="text-xs font-medium text-gray-500 block">Nota Batal</span>
            <div class="text-xl font-bold text-red-600 font-mono tabular-nums">
                {{ $ringkasan['jumlah_nota_batal'] }}
            </div>
            <span class="text-xs text-gray-500">Dibatalkan</span>
        </div>

        <!-- 3. Total Omzet Selesai -->
        <div class="p-4 rounded-xl bg-white border border-gray-200 shadow-sm space-y-1 col-span-2 sm:col-span-1 lg:col-span-2">
            <span class="text-xs font-medium text-gray-500 block">Total Omzet Nota Selesai</span>
            <div class="text-xl font-bold text-gray-900 font-mono truncate tabular-nums">
                {{ rupiah($ringkasan['total_omzet']) }}
            </div>
            <span class="text-xs text-gray-500">Nota berstatus selesai</span>
        </div>

        <!-- 4. Total Shift Dibuka -->
        <div class="p-4 rounded-xl bg-white border border-gray-200 shadow-sm space-y-1">
            <span class="text-xs font-medium text-gray-500 block">Jumlah Shift</span>
            <div class="text-xl font-bold text-gray-900 font-mono tabular-nums">
                {{ $ringkasan['jumlah_shift'] }}
            </div>
            <span class="text-xs text-gray-500">{{ $ringkasan['jumlah_shift_selisih'] }} shift berselisih</span>
        </div>

        <!-- 5. Total Selisih Kas -->
        <div class="p-4 rounded-xl bg-white border border-gray-200 shadow-sm space-y-1">
            <span class="text-xs font-medium text-gray-500 block">Total Selisih Kas</span>
            <div class="text-xl font-bold font-mono truncate tabular-nums {{ $ringkasan['total_selisih_kas'] < 0 ? 'text-red-600' : ($ringkasan['total_selisih_kas'] > 0 ? 'text-amber-600' : 'text-[#166534]') }}">
                {{ rupiah($ringkasan['total_selisih_kas']) }}
            </div>
            <span class="text-xs text-gray-500">Seluruh shift ditutup</span>
        </div>
    </div>

    <!-- 1. Rekapitulasi Shift Kasir -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-gray-200 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-semibold text-gray-900">Rekapitulasi Shift Kasir</h3>
                <p class="text-xs text-gray-500 mt-0.5">Audit kepatuhan kas laci kasir dan catatan selisih</p>
            </div>
            <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-gray-100 text-gray-600 font-mono">
                {{ count($rekap_shift) }} shift
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse min-w-[850px]">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50 text-gray-600 font-semibold">
                        <th class="py-3 px-4">Kasir</th>
                        <th class="py-3 px-4">Waktu Buka / Tutup</th>
                        <th class="py-3 px-4 text-right">Modal Awal</th>
                        <th class="py-3 px-4 text-right">Penjualan Tunai</th>
                        <th class="py-3 px-4 text-right">Uang Seharusnya</th>
                        <th class="py-3 px-4 text-right">Uang Fisik</th>
                        <th class="py-3 px-4 text-right">Selisih</th>
                        <th class="py-3 px-4">Status & Catatan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($rekap_shift as $s)
                        <tr class="hover:bg-gray-50/50 transition">
                            <td class="py-2.5 px-4 text-gray-900 font-semibold">
                                {{ $s['kasir'] }}
                                <span class="text-[11px] text-gray-400 block font-mono">Shift #{{ $s['id'] }}</span>
                            </td>
                            <td class="py-2.5 px-4 text-gray-700">
                                <div>{{ $s['waktu_buka'] }}</div>
                                <div class="text-[11px] text-gray-400">s/d {{ $s['waktu_tutup'] }}</div>
                            </td>
                            <td class="py-2.5 px-4 text-right font-mono text-gray-900 tabular-nums">
                                {{ rupiah($s['modal_awal']) }}
                            </td>
                            <td class="py-2.5 px-4 text-right font-mono text-[#166534] font-medium tabular-nums">
                                {{ rupiah($s['penjualan_tunai']) }}
                            </td>
                            <td class="py-2.5 px-4 text-right font-mono text-gray-900 tabular-nums">
                                {{ rupiah($s['uang_seharusnya']) }}
                                @if($s['status'] === 'berjalan')
                                    <span class="text-[10px] text-amber-600 block font-normal">(sementara)</span>
                                @endif
                            </td>
                            <td class="py-2.5 px-4 text-right font-mono text-gray-900 tabular-nums">
                                {{ $s['uang_fisik'] !== null ? rupiah($s['uang_fisik']) : '-' }}
                            </td>
                            <td class="py-2.5 px-4 text-right font-mono font-bold tabular-nums">
                                @if($s['selisih'] !== null)
                                    <span class="{{ $s['selisih'] < 0 ? 'text-red-600' : ($s['selisih'] > 0 ? 'text-amber-600' : 'text-[#166534]') }}">
                                        {{ rupiah($s['selisih']) }}
                                    </span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="py-2.5 px-4">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    @if($s['status'] === 'berjalan')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-600 animate-pulse"></span>
                                            Berjalan
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-gray-100 text-gray-700 border border-gray-200">
                                            Ditutup
                                        </span>
                                    @endif

                                    @if($s['ditutup_paksa'])
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-amber-100 text-amber-800 border border-amber-200">
                                            Paksa ({{ $s['ditutup_oleh'] ?? 'Admin' }})
                                        </span>
                                    @endif
                                </div>
                                @if($s['catatan'])
                                    <p class="text-[11px] text-gray-600 italic mt-1 max-w-xs break-words">"{{ $s['catatan'] }}"</p>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-xs text-gray-500">
                                Tidak ada shift kasir yang dibuka pada periode ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if(count($rekap_shift) > 0)
                    <tfoot>
                        <tr class="border-t-2 border-gray-200 bg-gray-50 font-bold text-xs text-gray-900">
                            <td colspan="6" class="py-3 px-4 text-right">TOTAL SELISIH KAS (SHIFT DITUTUP):</td>
                            <td class="py-3 px-4 text-right font-mono font-bold text-sm tabular-nums {{ $total_selisih_kas < 0 ? 'text-red-600' : ($total_selisih_kas > 0 ? 'text-amber-600' : 'text-[#166534]') }}">
                                {{ rupiah($total_selisih_kas) }}
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

    <!-- 2. Daftar Lengkap Nota Penjualan -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-gray-200 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-semibold text-gray-900">Daftar Transaksi Penjualan</h3>
                <p class="text-xs text-gray-500 mt-0.5">Seluruh nota kasir urut kronologis naik (nota batal tidak dihitung di total)</p>
            </div>
            <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-gray-100 text-gray-600 font-mono">
                {{ count($daftar_nota) }} nota
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse min-w-[750px]">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50 text-gray-600 font-semibold">
                        <th class="py-3 px-4">No. Nota</th>
                        <th class="py-3 px-4">Waktu Transaksi</th>
                        <th class="py-3 px-4">Kasir</th>
                        <th class="py-3 px-4 text-center">Barang</th>
                        <th class="py-3 px-4 text-right">Total Transaksi</th>
                        <th class="py-3 px-4 text-right">HPP</th>
                        <th class="py-3 px-4 text-right">Laba Kotor</th>
                        <th class="py-3 px-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($daftar_nota as $p)
                        <tr class="hover:bg-gray-50/50 transition {{ $p['status'] === 'dibatalkan' ? 'opacity-50 line-through bg-red-50/20' : '' }}">
                            <td class="py-2 px-4 font-mono font-semibold text-gray-900">
                                <a href="{{ route('admin.penjualan.show', $p['id']) }}" class="hover:text-[#166534] transition">
                                    {{ $p['nomor'] }}
                                </a>
                            </td>
                            <td class="py-2 px-4 text-gray-600 font-mono text-xs">
                                {{ $p['waktu_transaksi'] }}
                            </td>
                            <td class="py-2 px-4 text-gray-700">
                                {{ $p['kasir'] }}
                            </td>
                            <td class="py-2 px-4 text-center font-mono text-gray-700 tabular-nums">
                                {{ $p['jumlah_item'] }}
                            </td>
                            <td class="py-2 px-4 text-right font-mono text-gray-900 font-semibold tabular-nums">
                                {{ rupiah($p['total']) }}
                            </td>
                            <td class="py-2 px-4 text-right font-mono text-gray-600 tabular-nums">
                                {{ rupiah($p['total_modal']) }}
                            </td>
                            <td class="py-2 px-4 text-right font-mono text-[#166534] font-bold tabular-nums">
                                {{ rupiah($p['laba_kotor']) }}
                            </td>
                            <td class="py-2 px-4 text-center">
                                @if($p['status'] === 'selesai')
                                    <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Selesai
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-red-50 text-red-700 border border-red-200">
                                        DIBATALKAN
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-xs text-gray-500">
                                Tidak ada transaksi penjualan pada periode ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if(count($daftar_nota) > 0)
                    <tfoot>
                        <tr class="border-t-2 border-gray-200 bg-gray-50 font-bold text-xs text-gray-900">
                            <td colspan="4" class="py-3 px-4 text-right">TOTAL NOTA SELESAI:</td>
                            <td class="py-3 px-4 text-right font-mono text-sm tabular-nums">{{ rupiah($total_nota['omzet']) }}</td>
                            <td class="py-3 px-4 text-right font-mono text-gray-700 tabular-nums">{{ rupiah($total_nota['hpp']) }}</td>
                            <td class="py-3 px-4 text-right font-mono text-[#166534] text-sm tabular-nums">{{ rupiah($total_nota['laba_kotor']) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

    <!-- 3. Daftar Pembatalan Nota -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-gray-200 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-red-500"></span>
                <h3 class="text-sm font-semibold text-gray-900">Log Pembatalan Nota</h3>
            </div>
            <span class="text-xs text-red-600 font-mono">{{ count($daftar_void) }} dibatalkan</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse min-w-[700px]">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50 text-gray-600 font-semibold">
                        <th class="py-2.5 px-4">No. Nota</th>
                        <th class="py-2.5 px-4">Waktu Transaksi</th>
                        <th class="py-2.5 px-4">Waktu Dibatalkan</th>
                        <th class="py-2.5 px-4 text-right">Nominal</th>
                        <th class="py-2.5 px-4">Kasir Transaksi</th>
                        <th class="py-2.5 px-4">Aktor Pembatal</th>
                        <th class="py-2.5 px-4">Alasan Pembatalan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($daftar_void as $v)
                        <tr class="hover:bg-gray-50/50 transition">
                            <td class="py-2 px-4 font-mono font-semibold text-red-700">
                                {{ $v['nomor'] }}
                            </td>
                            <td class="py-2 px-4 text-gray-600 font-mono text-xs">
                                {{ $v['waktu_transaksi'] }}
                            </td>
                            <td class="py-2 px-4 text-red-600 font-mono text-xs">
                                {{ $v['waktu_dibatalkan'] }}
                            </td>
                            <td class="py-2 px-4 text-right font-mono text-gray-900 font-semibold tabular-nums">
                                {{ rupiah($v['total']) }}
                            </td>
                            <td class="py-2 px-4 text-gray-700">
                                {{ $v['kasir'] }}
                            </td>
                            <td class="py-2 px-4 text-gray-900 font-medium">
                                {{ $v['pembatal'] }}
                            </td>
                            <td class="py-2 px-4 text-gray-600 italic max-w-xs break-words">
                                "{{ $v['alasan'] }}"
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-6 text-center text-xs text-gray-500">
                                Tidak ada transaksi yang dibatalkan pada periode ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection