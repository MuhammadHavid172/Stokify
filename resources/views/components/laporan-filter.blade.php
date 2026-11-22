@props([
    'periode',
    'routeUrl',
    'pdfUrl',
    'showAdditionalFilters' => false,
    'kategoriOptions' => null,
    'filters' => [],
])

@php
    $today = \Carbon\Carbon::today('Asia/Jayapura')->toDateString();
    $currentYear = (int) \Carbon\Carbon::today('Asia/Jayapura')->year;
@endphp

<div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm mb-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-gray-200">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-emerald-50 border border-emerald-100 flex items-center justify-center text-[#166534]">
                <svg class="w-5 h-5 text-[#166534]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <div>
                <h3 class="text-xs font-semibold text-gray-700 uppercase tracking-wider">Periode Laporan</h3>
                <div class="flex items-center gap-2 mt-0.5">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        <span class="w-1.5 h-1.5 rounded-full {{ $periode->isBerjalan ? 'bg-emerald-600 animate-pulse' : 'bg-gray-400' }}"></span>
                        {{ $periode->label }}
                    </span>
                    <span class="text-xs text-gray-500">({{ $periode->rentangHari }} hari)</span>
                </div>
            </div>
        </div>

        @if(isset($pdfUrl))
            <div class="shrink-0">
                <a href="{{ $pdfUrl }}" 
                   target="_blank"
                   class="inline-flex items-center justify-center gap-2 w-full sm:w-auto px-4 py-2 rounded-lg bg-[#166534] hover:bg-[#14532D] text-white text-xs font-semibold shadow-sm transition active:scale-95">
                    <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>Unduh PDF</span>
                </a>
            </div>
        @endif
    </div>

    <!-- Formulir Pemilih Periode -->
    <form method="GET" action="{{ $routeUrl }}" x-data="{ jenis: '{{ $periode->jenis }}' }" class="mt-4 space-y-4">
        <!-- Opsi Jenis Periode -->
        <div class="flex flex-wrap gap-2">
            <label class="cursor-pointer">
                <input type="radio" name="jenis" value="harian" x-model="jenis" class="sr-only">
                <span :class="jenis === 'harian' ? 'bg-[#166534] text-white border-[#166534] shadow-sm font-semibold' : 'bg-gray-100 text-gray-700 border-gray-200 hover:bg-gray-200'"
                      class="inline-block px-3.5 py-1.5 rounded-lg border text-xs transition">
                    Harian
                </span>
            </label>

            <label class="cursor-pointer">
                <input type="radio" name="jenis" value="mingguan" x-model="jenis" class="sr-only">
                <span :class="jenis === 'mingguan' ? 'bg-[#166534] text-white border-[#166534] shadow-sm font-semibold' : 'bg-gray-100 text-gray-700 border-gray-200 hover:bg-gray-200'"
                      class="inline-block px-3.5 py-1.5 rounded-lg border text-xs transition">
                    Mingguan
                </span>
            </label>

            <label class="cursor-pointer">
                <input type="radio" name="jenis" value="bulanan" x-model="jenis" class="sr-only">
                <span :class="jenis === 'bulanan' ? 'bg-[#166534] text-white border-[#166534] shadow-sm font-semibold' : 'bg-gray-100 text-gray-700 border-gray-200 hover:bg-gray-200'"
                      class="inline-block px-3.5 py-1.5 rounded-lg border text-xs transition">
                    Bulanan
                </span>
            </label>

            <label class="cursor-pointer">
                <input type="radio" name="jenis" value="kustom" x-model="jenis" class="sr-only">
                <span :class="jenis === 'kustom' ? 'bg-[#166534] text-white border-[#166534] shadow-sm font-semibold' : 'bg-gray-100 text-gray-700 border-gray-200 hover:bg-gray-200'"
                      class="inline-block px-3.5 py-1.5 rounded-lg border text-xs transition">
                    Kustom (Rentang)
                </span>
            </label>
        </div>

        <!-- Input Dinamis Sesuai Jenis Periode -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 pt-1">
            <!-- Harian -->
            <div x-show="jenis === 'harian'" class="space-y-1">
                <label class="block text-xs font-medium text-gray-700">Tanggal Harian</label>
                <input type="date" 
                       name="tanggal_harian" 
                       value="{{ $periode->tanggalHarian ?? $today }}" 
                       max="{{ $today }}"
                       class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#166534] focus:border-[#166534]">
            </div>

            <!-- Mingguan -->
            <div x-show="jenis === 'mingguan'" class="space-y-1">
                <label class="block text-xs font-medium text-gray-700">Tanggal Acuan Minggu</label>
                <input type="date" 
                       name="tanggal_mingguan" 
                       value="{{ $periode->tanggalMingguan ?? $today }}" 
                       max="{{ $today }}"
                       class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#166534] focus:border-[#166534]">
                <span class="text-[11px] text-gray-500 block">Periode: Senin s/d Minggu</span>
            </div>

            <!-- Bulanan -->
            <div x-show="jenis === 'bulanan'" class="contents">
                <div class="space-y-1">
                    <label class="block text-xs font-medium text-gray-700">Bulan</label>
                    <select name="bulan" class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#166534] focus:border-[#166534]">
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ ($periode->bulan ?? \Carbon\Carbon::today('Asia/Jayapura')->month) == $m ? 'selected' : '' }}>
                                {{ \App\ValueObjects\PeriodeLaporan::getNamaBulanIndonesia($m) }}
                            </option>
                        @endfor
                    </select>
                </div>

                <div class="space-y-1">
                    <label class="block text-xs font-medium text-gray-700">Tahun</label>
                    <select name="tahun" class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#166534] focus:border-[#166534]">
                        @for($y = $currentYear; $y >= $currentYear - 4; $y--)
                            <option value="{{ $y }}" {{ ($periode->tahun ?? $currentYear) == $y ? 'selected' : '' }}>
                                {{ $y }}
                            </option>
                        @endfor
                    </select>
                </div>
            </div>

            <!-- Kustom -->
            <div x-show="jenis === 'kustom'" class="contents">
                <div class="space-y-1">
                    <label class="block text-xs font-medium text-gray-700">Tanggal Mulai</label>
                    <input type="date" 
                           name="tanggal_kustom_mulai" 
                           value="{{ $periode->tanggalKustomMulai ?? \Carbon\Carbon::today('Asia/Jayapura')->subDays(7)->toDateString() }}" 
                           max="{{ $today }}"
                           class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#166534] focus:border-[#166534]">
                </div>
                <div class="space-y-1">
                    <label class="block text-xs font-medium text-gray-700">Tanggal Akhir</label>
                    <input type="date" 
                           name="tanggal_kustom_akhir" 
                           value="{{ $periode->tanggalKustomAkhir ?? $today }}" 
                           max="{{ $today }}"
                           class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#166534] focus:border-[#166534]">
                </div>
            </div>

            <!-- Filter Tambahan untuk Laporan Stok (jika diaktifkan) -->
            @if($showAdditionalFilters)
                <div class="space-y-1">
                    <label class="block text-xs font-medium text-gray-700">Cari Barang</label>
                    <input type="text" 
                           name="q" 
                           value="{{ $filters['q'] ?? '' }}" 
                           placeholder="Nama atau kode..."
                           class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-xs text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#166534] focus:border-[#166534]">
                </div>

                <div class="space-y-1">
                    <label class="block text-xs font-medium text-gray-700">Kategori</label>
                    <select name="kategori_id" class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#166534] focus:border-[#166534]">
                        <option value="">Semua Kategori</option>
                        @if($kategoriOptions)
                            @foreach($kategoriOptions as $kat)
                                <option value="{{ $kat->id }}" {{ ($filters['kategori_id'] ?? '') == $kat->id ? 'selected' : '' }}>
                                    {{ $kat->nama }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>
            @endif

            <!-- Tombol Submit Filter -->
            <div class="flex items-end pt-1">
                <button type="submit" 
                        class="w-full sm:w-auto px-4 py-2 rounded-lg bg-[#166534] hover:bg-[#14532D] text-xs font-semibold text-white transition flex items-center justify-center gap-1.5 shadow-sm active:scale-95">
                    <svg class="w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    <span>Terapkan Filter</span>
                </button>
            </div>
        </div>

        @if($showAdditionalFilters)
            <!-- Checkbox Filter Khusus Stok -->
            <div class="flex flex-wrap items-center gap-4 pt-3 border-t border-gray-200 text-xs text-gray-700">
                <label class="inline-flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="hanya_bergerak" value="1" {{ !empty($filters['hanya_bergerak']) ? 'checked' : '' }} class="rounded border-gray-300 text-[#166534] focus:ring-[#166534]">
                    <span>Hanya yang memiliki pergerakan</span>
                </label>
                <label class="inline-flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="hanya_perlu_kulakan" value="1" {{ !empty($filters['hanya_perlu_kulakan']) ? 'checked' : '' }} class="rounded border-gray-300 text-[#166534] focus:ring-[#166534]">
                    <span class="text-red-600 font-semibold">Hanya yang perlu restok</span>
                </label>
            </div>
        @endif
    </form>
</div>