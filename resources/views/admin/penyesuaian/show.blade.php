<x-layouts.admin title="Detail Penyesuaian Stok">
    <div class="space-y-6">
        
        <!-- Header Halaman -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-slate-100">
            <div>
                <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 mb-1">
                    <a href="{{ route('admin.dashboard') }}" class="hover:text-slate-800 transition">Dashboard</a>
                    <span>/</span>
                    <a href="{{ route('admin.penyesuaian.index') }}" class="hover:text-slate-800 transition">Penyesuaian Stok</a>
                    <span>/</span>
                    <span class="text-slate-800">{{ $penyesuaian->nomor }}</span>
                </div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight font-mono">
                        {{ $penyesuaian->nomor }}
                    </h1>
                    @if ($penyesuaian->mode === 'opname')
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                            Stok Opname
                        </span>
                    @else
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                            Manual
                        </span>
                    @endif
                </div>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">
                    Tanggal: <strong class="text-slate-800">{{ $penyesuaian->tanggal->translatedFormat('d F Y') }}</strong> &bull; Dicatat oleh: <strong class="text-slate-800">{{ $penyesuaian->user->name ?? 'Admin' }}</strong>
                </p>
            </div>

            <div class="flex items-center gap-2.5 shrink-0">
                <a href="{{ route('admin.penyesuaian.index') }}" 
                   class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs shadow-xs transition">
                    &larr; Riwayat Penyesuaian
                </a>
            </div>
        </div>

        <!-- Banner Pemberitahuan Immutable / Tidak Dapat Diubah -->
        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 text-slate-600 text-xs flex items-start gap-3">
            <svg class="w-5 h-5 text-slate-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div>
                <strong class="text-slate-800">Dokumen Penyesuaian Permanen:</strong>
                Penyesuaian stok tidak dapat diubah atau dihapus demi menjamin audit dan keaslian pembukuan pergerakan barang. Jika terdapat kesalahan input, buat penyesuaian koreksi baru agar riwayat tetap utuh.
            </div>
        </div>

        <!-- Ringkasan Informasi Header -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <!-- Mode -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Mode Penyesuaian</span>
                <span class="text-base font-black text-slate-800 capitalize mt-1 block">
                    {{ $penyesuaian->mode === 'opname' ? 'Penyelarasan Stok Opname' : 'Penyesuaian Manual' }}
                </span>
            </div>

            <!-- Jumlah Barang -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Total Barang Disesuaikan</span>
                <span class="text-base font-black text-slate-800 mt-1 block">
                    {{ $penyesuaian->detail->count() }} barang
                </span>
            </div>

            <!-- Total Kerugian -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Total Kerugian Toko</span>
                <span class="text-base font-black {{ $penyesuaian->total_kerugian > 0 ? 'text-rose-600' : 'text-slate-800' }} mt-1 block">
                    {{ rupiah($penyesuaian->total_kerugian) }}
                </span>
            </div>
        </div>

        @if ($penyesuaian->catatan)
            <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs text-xs">
                <span class="font-bold text-slate-700">Catatan Tambahan:</span>
                <p class="text-slate-600 mt-1">{{ $penyesuaian->catatan }}</p>
            </div>
        @endif

        <!-- Rincian Item Penyesuaian -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-sm font-bold text-slate-900">Rincian Item yang Disesuaikan</h3>
                <span class="text-xs text-slate-400">{{ $penyesuaian->detail->count() }} barang</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/70 font-bold text-slate-500 uppercase tracking-wider text-[11px]">
                            <th class="px-5 py-3.5 w-12 text-center">No</th>
                            <th class="px-5 py-3.5">Barang</th>
                            <th class="px-5 py-3.5 text-center">Jenis</th>
                            <th class="px-5 py-3.5 text-center">Perubahan</th>
                            <th class="px-5 py-3.5 text-center">Stok Sebelum</th>
                            <th class="px-5 py-3.5 text-center">Stok Sesudah</th>
                            <th class="px-5 py-3.5 text-right">Harga Modal</th>
                            <th class="px-5 py-3.5 text-right">Nilai</th>
                            <th class="px-5 py-3.5">Alasan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @foreach ($penyesuaian->detail as $index => $detail)
                            @php
                                $jenisMap = [
                                    'rusak' => ['label' => 'Barang Rusak', 'class' => 'bg-rose-50 text-rose-700 border-rose-200'],
                                    'hilang' => ['label' => 'Barang Hilang', 'class' => 'bg-purple-50 text-purple-700 border-purple-200'],
                                    'kedaluwarsa' => ['label' => 'Kedaluwarsa', 'class' => 'bg-amber-50 text-amber-700 border-amber-200'],
                                    'selisih_opname' => ['label' => 'Selisih Opname', 'class' => 'bg-blue-50 text-blue-700 border-blue-200'],
                                    'koreksi_lain' => ['label' => 'Koreksi Lain', 'class' => 'bg-slate-100 text-slate-700 border-slate-200'],
                                ];
                                $jenisInfo = $jenisMap[$detail->jenis] ?? ['label' => $detail->jenis, 'class' => 'bg-slate-100 text-slate-700 border-slate-200'];
                            @endphp
                            <tr class="hover:bg-slate-50/80 transition">
                                <!-- No -->
                                <td class="px-5 py-3.5 text-center text-slate-400">
                                    {{ $index + 1 }}
                                </td>

                                <!-- Barang -->
                                <td class="px-5 py-3.5">
                                    <div class="font-bold text-slate-900">
                                        <a href="{{ route('admin.barang.show', $detail->barang_id) }}" class="hover:text-[#166534] transition">
                                            {{ $detail->barang->nama ?? 'Barang #' . $detail->barang_id }}
                                        </a>
                                    </div>
                                    <div class="text-[10px] text-slate-400 font-mono">
                                        {{ $detail->barang->kode ?? '-' }} &bull; {{ $detail->barang->satuan ?? 'pcs' }}
                                    </div>
                                </td>

                                <!-- Jenis -->
                                <td class="px-5 py-3.5 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $jenisInfo['class'] }}">
                                        {{ $jenisInfo['label'] }}
                                    </span>
                                </td>

                                <!-- Perubahan (+/-) -->
                                <td class="px-5 py-3.5 text-center font-bold">
                                    @if ($detail->jumlah > 0)
                                        <span class="text-emerald-600 font-black">+{{ $detail->jumlah }}</span>
                                    @else
                                        <span class="text-rose-600 font-black">{{ $detail->jumlah }}</span>
                                    @endif
                                </td>

                                <!-- Stok Sebelum -->
                                <td class="px-5 py-3.5 text-center text-slate-500">
                                    {{ $detail->stok_sebelum }}
                                </td>

                                <!-- Stok Sesudah -->
                                <td class="px-5 py-3.5 text-center font-black text-slate-900">
                                    {{ $detail->stok_sesudah }}
                                </td>

                                <!-- Harga Modal Snapshot -->
                                <td class="px-5 py-3.5 text-right font-mono text-slate-600">
                                    {{ rupiah($detail->harga_modal) }}
                                </td>

                                <!-- Nilai -->
                                <td class="px-5 py-3.5 text-right font-bold {{ $detail->nilai < 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                                    {{ $detail->nilai < 0 ? '-' : '+' }} {{ rupiah(abs($detail->nilai)) }}
                                </td>

                                <!-- Alasan -->
                                <td class="px-5 py-3.5 text-slate-600">
                                    {{ $detail->alasan }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-layouts.admin>
