@extends('layouts.admin')

@section('title', 'Pusat Laporan Kios - Stokify')

@section('content')
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

    <!-- Header Halaman -->
    <x-page-header 
        title="Pusat Laporan Kios" 
        description="Pantau performa keuangan, audit transaksi kasir, dan rekonsiliasi mutasi stok kios Anda secara transparan.">
    </x-page-header>

    <!-- Tiga Kartu Pilihan Laporan -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-stretch">
        <!-- Kartu 1: Laporan Ringkasan Keuangan -->
        <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm flex flex-col justify-between hover:border-gray-300 transition group">
            <div class="space-y-4">
                <div class="w-12 h-12 rounded-xl bg-[#DCFCE7] text-[#166534] flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-base font-bold text-gray-900 group-hover:text-[#166534] transition">Ringkasan Keuangan</h2>
                    <p class="text-xs text-gray-500 mt-2 leading-relaxed teks-penjelasan">
                        Analisis omzet penjualan, HPP (modal barang terjual), margin laba kotor, tren grafik harian, rekap performa kasir, serta 10 barang paling laris dan menguntungkan.
                    </p>
                </div>
                <div class="space-y-1.5 pt-3 border-t border-gray-100 text-xs text-gray-600">
                    <div class="flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#166534]"></span>
                        <span>Omzet, HPP, dan Laba Kotor</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#166534]"></span>
                        <span>Grafik Tren Harian dan 10 Barang Terlaris</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#166534]"></span>
                        <span>Unduh Dokumen PDF Format Potret</span>
                    </div>
                </div>
            </div>

            <div class="pt-6">
                <a href="{{ route('admin.laporan.keuangan') }}" 
                   class="inline-flex items-center justify-center gap-2 w-full py-2.5 px-4 rounded-lg bg-[#166534] hover:bg-[#14532D] text-white font-semibold text-xs shadow-xs transition">
                    <span>Buka Laporan Keuangan</span>
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                    </svg>
                </a>
            </div>
        </div>

        <!-- Kartu 2: Laporan Audit Transaksi dan Shift -->
        <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm flex flex-col justify-between hover:border-gray-300 transition group">
            <div class="space-y-4">
                <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-700 border border-amber-200 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-base font-bold text-gray-900 group-hover:text-amber-800 transition">Audit Transaksi & Shift</h2>
                    <p class="text-xs text-gray-500 mt-2 leading-relaxed teks-penjelasan">
                        Rekonsiliasi uang kas di laci kasir, audit selisih fisik vs sistem, riwayat transaksi nota penjualan per jam, dan audit investigasi pembatalan nota.
                    </p>
                </div>
                <div class="space-y-1.5 pt-3 border-t border-gray-100 text-xs text-gray-600">
                    <div class="flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-600"></span>
                        <span>Rekap Selisih Kas Seluruh Shift</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-600"></span>
                        <span>Daftar Lengkap Nota Selesai dan Batal</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-600"></span>
                        <span>Unduh Dokumen PDF Format Lanskap</span>
                    </div>
                </div>
            </div>

            <div class="pt-6">
                <a href="{{ route('admin.laporan.audit') }}" 
                   class="inline-flex items-center justify-center gap-2 w-full py-2.5 px-4 rounded-lg bg-white hover:bg-gray-50 border border-gray-200 text-gray-700 font-semibold text-xs shadow-xs transition">
                    <span>Buka Laporan Audit</span>
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                    </svg>
                </a>
            </div>
        </div>

        <!-- Kartu 3: Laporan Mutasi dan Sisa Stok -->
        <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm flex flex-col justify-between hover:border-gray-300 transition group">
            <div class="space-y-4">
                <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-700 border border-blue-200 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-base font-bold text-gray-900 group-hover:text-blue-800 transition">Mutasi & Sisa Stok</h2>
                    <p class="text-xs text-gray-500 mt-2 leading-relaxed teks-penjelasan">
                        Perhitungan stok awal, barang masuk, penjualan, retur pembatalan nota, dan penyesuaian stok. Dilengkapi nilai aset persediaan akhir dan daftar belanja stok.
                    </p>
                </div>
                <div class="space-y-1.5 pt-3 border-t border-gray-100 text-xs text-gray-600">
                    <div class="flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-blue-600"></span>
                        <span>Stok Awal, Mutasi, dan Sisa Stok Akhir</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-blue-600"></span>
                        <span>Nilai Persediaan dan Panduan Belanja Stok</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-blue-600"></span>
                        <span>Unduh Dokumen PDF Format Lanskap</span>
                    </div>
                </div>
            </div>

            <div class="pt-6">
                <a href="{{ route('admin.laporan.stok') }}" 
                   class="inline-flex items-center justify-center gap-2 w-full py-2.5 px-4 rounded-lg bg-white hover:bg-gray-50 border border-gray-200 text-gray-700 font-semibold text-xs shadow-xs transition">
                    <span>Buka Laporan Stok</span>
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                    </svg>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
