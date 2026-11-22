<x-layouts.admin title="Detail Barang & Riwayat Mutasi Stok">
    <div class="space-y-6">
        
        <!-- Header Halaman -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-slate-100">
            <div>
                <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 mb-1">
                    <a href="{{ route('admin.dashboard') }}" class="hover:text-slate-800 transition">Dashboard</a>
                    <span>/</span>
                    <a href="{{ route('admin.barang.index') }}" class="hover:text-slate-800 transition">Master Barang</a>
                    <span>/</span>
                    <span class="text-slate-800">{{ $barang->kode }}</span>
                </div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                        {{ $barang->nama }}
                    </h1>
                    <x-badge :variant="$barang->is_aktif ? 'green' : 'red'" size="sm">
                        {{ $barang->is_aktif ? 'Aktif' : 'Nonaktif' }}
                    </x-badge>
                </div>
                <p class="text-xs sm:text-sm text-slate-500 mt-1 font-mono">
                    Kode: <strong class="text-slate-800">{{ $barang->kode }}</strong> • Kategori: <strong class="text-slate-800">{{ $barang->kategori->nama ?? 'Tanpa Kategori' }}</strong>
                </p>
            </div>

            <div class="flex items-center gap-2.5 shrink-0">
                <a href="{{ route('admin.barang.index') }}" 
                   class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs shadow-xs transition">
                    <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    <span>Daftar Barang</span>
                </a>

                <a href="{{ route('admin.barang.edit', $barang) }}" 
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-[#166534] hover:bg-[#14532D] text-white font-semibold text-xs shadow-xs transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    <span>Ubah Barang</span>
                </a>
            </div>
        </div>

        <!-- 4 Kotak Ringkasan Info Barang -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3.5 sm:gap-4">
            <!-- Stok Saat Ini -->
            <div class="p-4 sm:p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
                <span class="text-xs font-semibold text-slate-500 block">Stok Saat Ini</span>
                <div class="flex items-baseline gap-1.5 mt-1">
                    <span class="text-2xl sm:text-3xl font-black text-slate-900">{{ $barang->stok }}</span>
                    <span class="text-xs text-slate-500 font-semibold">{{ $barang->satuan }}</span>
                </div>
                <div class="mt-2">
                    @if ($barang->stok == 0)
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-rose-600 text-white">Stok Habis</span>
                    @elseif ($barang->stok <= $barang->stok_minimum)
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-rose-100 text-rose-700">Stok Menipis (Min. {{ $barang->stok_minimum }})</span>
                    @else
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">Aman (&gt; Min. {{ $barang->stok_minimum }})</span>
                    @endif
                </div>
            </div>

            <!-- Harga Modal -->
            <div class="p-4 sm:p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
                <span class="text-xs font-semibold text-slate-500 block">Harga Modal</span>
                <span class="text-xl sm:text-2xl font-bold text-slate-800 block mt-1">
                    {{ rupiah($barang->harga_modal) }}
                </span>
                <span class="text-[11px] text-slate-400 mt-1 block">Biaya per {{ $barang->satuan }}</span>
            </div>

            <!-- Harga Jual -->
            <div class="p-4 sm:p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
                <span class="text-xs font-semibold text-slate-500 block">Harga Jual</span>
                <span class="text-xl sm:text-2xl font-black text-[#166534] block mt-1">
                    {{ rupiah($barang->harga_jual) }}
                </span>
                <span class="text-[11px] text-slate-400 mt-1 block">Harga kasir ke pelanggan</span>
            </div>

            <!-- Margin Keuntungan -->
            @php
                $margin = $barang->harga_jual - $barang->harga_modal;
                $persenMargin = $barang->harga_modal > 0 ? round(($margin / $barang->harga_modal) * 100, 1) : 0;
            @endphp
            <div class="p-4 sm:p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
                <span class="text-xs font-semibold text-slate-500 block">Estimasi Margin</span>
                <span class="text-xl sm:text-2xl font-bold {{ $margin >= 0 ? 'text-emerald-700' : 'text-rose-600' }} block mt-1">
                    {{ rupiah($margin) }}
                </span>
                <span class="text-[11px] font-semibold {{ $margin >= 0 ? 'text-emerald-600' : 'text-rose-500' }} mt-1 block">
                    {{ $margin >= 0 ? "+{$persenMargin}% keuntungan" : "{$persenMargin}% potensi rugi" }}
                </span>
            </div>
        </div>

        <!-- Kotak Performa 30 Hari (Tahap 10) -->
        @if (isset($performa30Hari))
            <div class="p-4 sm:p-5 rounded-xl bg-white border border-gray-200 shadow-sm">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-3 border-b border-gray-100">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#166534]"></span>
                        <h3 class="text-sm font-bold text-gray-900 tracking-tight">Performa 30 Hari Terakhir</h3>
                    </div>
                    <a href="{{ route('admin.analisis.rekomendasi', ['q' => $barang->kode]) }}" class="text-xs text-[#166534] hover:text-[#14532D] font-medium transition flex items-center gap-1">
                        <span>Lihat di Analisis Rekomendasi</span>
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-3.5">
                    <!-- Kelas Barang -->
                    <div>
                        <span class="text-[11px] text-gray-500 block font-medium">Klasifikasi Barang</span>
                        <div class="mt-1">
                            @if ($performa30Hari['kelas'] === 'fast')
                                <span class="px-2.5 py-0.5 rounded-full bg-[#DCFCE7] text-[#166534] border border-[#86EFAC]/60 font-bold text-xs">
                                    Fast Moving
                                </span>
                            @elseif ($performa30Hari['kelas'] === 'normal')
                                <span class="px-2.5 py-0.5 rounded-full bg-blue-50 text-blue-700 border border-blue-200 font-bold text-xs">
                                    Normal
                                </span>
                            @elseif ($performa30Hari['kelas'] === 'slow')
                                <span class="px-2.5 py-0.5 rounded-full bg-amber-50 text-amber-700 border border-amber-200 font-bold text-xs">
                                    Slow Moving
                                </span>
                            @elseif ($performa30Hari['kelas'] === 'tidak_laku')
                                <span class="px-2.5 py-0.5 rounded-full bg-rose-50 text-rose-700 border border-rose-200 font-bold text-xs">
                                    Tidak Laku
                                </span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full bg-gray-100 text-gray-700 border border-gray-200 font-bold text-xs">
                                    {{ $performa30Hari['kelas_label'] }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Rata-rata Jual per Hari -->
                    <div>
                        <span class="text-[11px] text-gray-500 block font-medium">Rata-rata Terjual</span>
                        <div class="flex items-baseline gap-1 mt-1 font-mono">
                            <span class="text-lg font-bold text-gray-900 tabular-nums">{{ number_format($performa30Hari['rata_harian'], 1, ',', '.') }}</span>
                            <span class="text-xs text-gray-500">{{ $barang->satuan }}/hari</span>
                        </div>
                    </div>

                    <!-- Estimasi Habis (Cakupan Hari) -->
                    <div>
                        <span class="text-[11px] text-gray-500 block font-medium">Estimasi Habis</span>
                        <div class="mt-1 font-mono">
                            @if ($performa30Hari['cakupan_hari'] !== null)
                                <span class="text-lg font-bold tabular-nums {{ $performa30Hari['cakupan_hari'] <= 7 ? 'text-amber-600' : 'text-gray-900' }}">
                                    {{ $performa30Hari['cakupan_hari'] }}
                                </span>
                                <span class="text-xs text-gray-500">hari</span>
                            @else
                                <span class="text-xs text-gray-400 italic">Tidak ada penjualan</span>
                            @endif
                        </div>
                    </div>

                    <!-- Rekomendasi Restok -->
                    <div>
                        <span class="text-[11px] text-gray-500 block font-medium">Rekomendasi Restok</span>
                        <div class="mt-1">
                            @if ($performa30Hari['jumlah_rekomendasi'] > 0)
                                <div class="flex items-baseline gap-1 font-mono">
                                    <span class="text-lg font-bold text-[#166534] tabular-nums">+{{ $performa30Hari['jumlah_rekomendasi'] }}</span>
                                    <span class="text-xs text-[#166534]">{{ $barang->satuan }}</span>
                                </div>
                                <span class="text-[10px] text-gray-500 block font-mono">({{ $performa30Hari['status_rekomendasi_label'] }})</span>
                            @else
                                <span class="text-xs font-semibold text-gray-600 block">
                                    {{ $performa30Hari['status_rekomendasi_label'] }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Tabel Riwayat Mutasi Stok -->
        <div class="bg-white border border-slate-200/80 rounded-3xl shadow-xs overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Riwayat Mutasi & Pergerakan Stok</h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Log siklus stok tertutup yang mencatat setiap penambahan dan pengurangan persediaan secara auditabel.
                    </p>
                </div>
                <span class="text-xs font-semibold text-slate-500 bg-slate-50 px-3 py-1 rounded-full border border-slate-200">
                    Total: <strong class="text-slate-800">{{ $mutasiList->total() }}</strong> catatan
                </span>
            </div>

            @if ($mutasiList->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-600">
                        <thead class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200/80">
                            <tr>
                                <th class="px-5 py-3.5">Waktu</th>
                                <th class="px-5 py-3.5">Tipe Mutasi</th>
                                <th class="px-5 py-3.5 text-center">Jumlah (+/-)</th>
                                <th class="px-5 py-3.5 text-center">Stok Sebelum</th>
                                <th class="px-5 py-3.5 text-center">Stok Sesudah</th>
                                <th class="px-5 py-3.5">Keterangan</th>
                                <th class="px-5 py-3.5">Petugas</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($mutasiList as $mutasi)
                                @php
                                    $tipeMap = [
                                        'stok_awal' => ['label' => 'Stok Awal', 'class' => 'bg-blue-50 text-blue-700 border-blue-200'],
                                        'barang_masuk' => ['label' => 'Barang Masuk', 'class' => 'bg-emerald-50 text-emerald-700 border-emerald-200'],
                                        'penjualan' => ['label' => 'Penjualan', 'class' => 'bg-purple-50 text-purple-700 border-purple-200'],
                                        'void_penjualan' => ['label' => 'Batal Jual', 'class' => 'bg-amber-50 text-amber-700 border-amber-200'],
                                        'penyesuaian' => ['label' => 'Penyesuaian', 'class' => 'bg-slate-100 text-slate-700 border-slate-200'],
                                    ];
                                    $tipeInfo = $tipeMap[$mutasi->tipe] ?? ['label' => $mutasi->tipe, 'class' => 'bg-slate-100 text-slate-700 border-slate-200'];
                                @endphp
                                <tr class="hover:bg-slate-50/80 transition">
                                    <!-- Tanggal / Waktu -->
                                    <td class="px-5 py-3.5 text-slate-500 font-mono text-[11px]">
                                        {{ $mutasi->created_at->translatedFormat('d M Y, H:i:s') }}
                                    </td>

                                    <!-- Tipe Mutasi -->
                                    <td class="px-5 py-3.5">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider border {{ $tipeInfo['class'] }}">
                                            {{ $tipeInfo['label'] }}
                                        </span>
                                    </td>

                                    <!-- Jumlah Bertanda (+/-) -->
                                    <td class="px-5 py-3.5 text-center font-bold">
                                        @if ($mutasi->jumlah > 0)
                                            <span class="text-emerald-600 font-black">+{{ $mutasi->jumlah }}</span>
                                        @elseif ($mutasi->jumlah < 0)
                                            <span class="text-rose-600 font-black">{{ $mutasi->jumlah }}</span>
                                        @else
                                            <span class="text-slate-400">0</span>
                                        @endif
                                        <span class="text-[10px] font-normal text-slate-400">{{ $barang->satuan }}</span>
                                    </td>

                                    <!-- Stok Sebelum -->
                                    <td class="px-5 py-3.5 text-center font-semibold text-slate-500">
                                        {{ $mutasi->stok_sebelum }}
                                    </td>

                                    <!-- Stok Sesudah -->
                                    <td class="px-5 py-3.5 text-center font-black text-slate-900">
                                        {{ $mutasi->stok_sesudah }}
                                    </td>

                                    <!-- Keterangan -->
                                    <td class="px-5 py-3.5 text-slate-600">
                                        <div>{{ $mutasi->keterangan ?? '—' }}</div>
                                        @if ($mutasi->tipe === 'barang_masuk' && $mutasi->referensi_type === \App\Models\BarangMasuk::class && $mutasi->referensi_id)
                                            <a href="{{ route('admin.barang-masuk.show', $mutasi->referensi_id) }}" 
                                               class="inline-flex items-center gap-1 text-[11px] font-bold text-[#166534] hover:underline mt-0.5">
                                                <span>Lihat Nota Masuk</span>
                                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                            </a>
                                        @elseif ($mutasi->tipe === 'penyesuaian' && $mutasi->referensi_type === \App\Models\PenyesuaianStok::class && $mutasi->referensi_id)
                                            <a href="{{ route('admin.penyesuaian.show', $mutasi->referensi_id) }}" 
                                               class="inline-flex items-center gap-1 text-[11px] font-bold text-amber-600 hover:underline mt-0.5">
                                                <span>Lihat Dokumen Penyesuaian</span>
                                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                            </a>
                                        @elseif (in_array($mutasi->tipe, ['penjualan', 'void_penjualan'], true) && $mutasi->referensi_type === \App\Models\Penjualan::class && $mutasi->referensi_id)
                                            <a href="{{ route('admin.penjualan.show', $mutasi->referensi_id) }}" 
                                               class="inline-flex items-center gap-1 text-[11px] font-bold text-[#166534] hover:text-[#14532D] hover:underline mt-0.5">
                                                <span>{{ $mutasi->tipe === 'void_penjualan' ? 'Lihat Nota Batal' : 'Lihat Nota Penjualan' }}</span>
                                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                            </a>
                                        @endif
                                    </td>

                                    <!-- Dicatat Oleh Siapa -->
                                    <td class="px-5 py-3.5 text-slate-700 font-medium">
                                        {{ $mutasi->user->name ?? 'Sistem' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if ($mutasiList->hasPages())
                    <div class="px-5 py-4 border-t border-slate-100 bg-slate-50/50">
                        {{ $mutasiList->links() }}
                    </div>
                @endif
            @else
                <div class="p-8 text-center text-slate-400 text-xs">
                    Belum ada riwayat pergerakan stok untuk barang ini.
                </div>
            @endif
        </div>

    </div>
</x-layouts.admin>
