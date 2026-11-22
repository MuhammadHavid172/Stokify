<x-layouts.admin title="Kelola Kasir">
    <div class="space-y-6">

        <!-- Sub-Navigasi Tab: Pengaturan Kios -->
        <div class="flex items-center gap-2 border-b border-gray-200 pb-3">
            <a href="{{ route('admin.kios.index') }}" 
               class="px-4 py-2 rounded-lg text-xs font-bold transition {{ request()->routeIs('admin.kios.*') ? 'bg-[#166534] text-white shadow-xs' : 'bg-white text-gray-600 hover:text-gray-900 border border-gray-200 hover:bg-gray-50' }}">
                Profil Kios
            </a>
            <a href="{{ route('admin.kasir.index') }}" 
               class="px-4 py-2 rounded-lg text-xs font-bold transition {{ request()->routeIs('admin.kasir.*') ? 'bg-[#166534] text-white shadow-xs' : 'bg-white text-gray-600 hover:text-gray-900 border border-gray-200 hover:bg-gray-50' }}">
                Kelola Kasir
            </a>
            <a href="{{ route('admin.log.index') }}" 
               class="px-4 py-2 rounded-lg text-xs font-bold transition {{ request()->routeIs('admin.log.*') ? 'bg-[#166534] text-white shadow-xs' : 'bg-white text-gray-600 hover:text-gray-900 border border-gray-200 hover:bg-gray-50' }}">
                Log Aktivitas
            </a>
        </div>

        <!-- Page Header -->
        <x-page-header 
            title="Kelola Kasir" 
            description="Daftar petugas kasir yang memiliki hak akses untuk mengoperasikan layar penjualan kasir di {{ $kios->nama_kios }}.">
            
            <a href="{{ route('admin.kasir.create') }}" 
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#166534] hover:bg-[#EA580C] text-white font-bold text-xs shadow-sm hover:shadow transition transform hover:-translate-y-0.5 active:translate-y-0">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <line x1="12" y1="5" x2="12" y2="19" />
                    <line x1="5" y1="12" x2="19" y2="12" />
                </svg>
                <span>Tambah Kasir</span>
            </a>
        </x-page-header>

        <!-- Informasi Kebijakan Data (Kasir dinonaktifkan, bukan dihapus) -->
        <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-800 text-xs sm:text-sm flex items-start gap-3 shadow-2xs">
            <div class="w-7 h-7 rounded-lg bg-amber-500/20 text-amber-700 flex items-center justify-center shrink-0 mt-0.5">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="leading-relaxed">
                <strong>Kebijakan Akun:</strong> Kasir dinonaktifkan, bukan dihapus, agar riwayat transaksinya tetap utuh dan pembukuan toko tetap akuntabel.
            </div>
        </div>

        <!-- Filter & Pencarian -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs">
            <form method="GET" action="{{ route('admin.kasir.index') }}" class="flex flex-col sm:flex-row items-center justify-between gap-3">
                <div class="w-full sm:w-80 relative">
                    <input type="text" 
                           name="q" 
                           value="{{ request('q') }}" 
                           placeholder="Cari nama atau username..." 
                           class="w-full rounded-xl border border-slate-300 bg-slate-50/50 pl-10 pr-4 py-2 text-xs text-slate-800 placeholder-slate-400 focus:bg-white focus:border-[#166534] focus:ring-1 focus:ring-[#166534] transition" />
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8" />
                            <line x1="21" y1="21" x2="16.65" y2="16.65" />
                        </svg>
                    </div>
                </div>

                <div class="w-full sm:w-auto flex items-center gap-2">
                    <!-- Filter Status -->
                    <select name="status" 
                            onchange="this.form.submit()" 
                            class="w-full sm:w-auto rounded-xl border border-slate-300 bg-slate-50/50 pl-3.5 pr-9 py-2 text-xs text-slate-700 font-medium focus:bg-white focus:border-[#166534] focus:ring-1 focus:ring-[#166534] transition cursor-pointer">
                        <option value="" {{ request('status') === '' || request('status') === null ? 'selected' : '' }}>Semua Status</option>
                        <option value="aktif" {{ request('status') === 'aktif' ? 'selected' : '' }}>Hanya Aktif</option>
                        <option value="nonaktif" {{ request('status') === 'nonaktif' ? 'selected' : '' }}>Hanya Nonaktif</option>
                    </select>

                    <button type="submit" class="px-4 py-2 rounded-xl bg-slate-800 text-white font-bold text-xs hover:bg-slate-700 transition shrink-0">
                        Filter
                    </button>

                    @if (request()->hasAny(['q', 'status']))
                        <a href="{{ route('admin.kasir.index') }}" class="px-3 py-2 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-100 font-bold text-xs transition shrink-0">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Tabel Daftar Kasir -->
        @if ($kasirs->count() > 0)
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                                <th class="py-3.5 px-6">Petugas Kasir</th>
                                <th class="py-3.5 px-6">Username</th>
                                <th class="py-3.5 px-6">Status</th>
                                <th class="py-3.5 px-6">Tanggal Dibuat</th>
                                <th class="py-3.5 px-6 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs">
                            @foreach ($kasirs as $kasir)
                                <tr class="hover:bg-slate-50/60 transition">
                                    <!-- Nama Kasir -->
                                    <td class="py-4 px-6">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-xl bg-[#DCFCE7]/50 text-[#166534] font-bold flex items-center justify-center uppercase shrink-0 text-xs border border-[#6B7280]/30">
                                                {{ substr($kasir->name, 0, 2) }}
                                            </div>
                                            <div>
                                                <p class="font-bold text-[#1E2A4A] text-sm">{{ $kasir->name }}</p>
                                                <p class="text-[11px] text-slate-400">ID Kasir: #{{ $kasir->id }}</p>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Username -->
                                    <td class="py-4 px-6 font-mono font-semibold text-slate-700">
                                        @<span>{{ $kasir->username }}</span>
                                    </td>

                                    <!-- Status Badge -->
                                    <td class="py-4 px-6">
                                        @if ($kasir->is_aktif)
                                            <x-badge variant="aktif" size="sm">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                Aktif
                                            </x-badge>
                                        @else
                                            <x-badge variant="nonaktif" size="sm">
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                                Nonaktif
                                            </x-badge>
                                        @endif
                                    </td>

                                    <!-- Tanggal Dibuat -->
                                    <td class="py-4 px-6 text-slate-500 font-medium">
                                        {{ $kasir->created_at ? $kasir->created_at->translatedFormat('d M Y') : '—' }}
                                    </td>

                                    <!-- Aksi -->
                                    <td class="py-4 px-6 text-right">
                                        <div class="inline-flex items-center gap-1.5" x-data>
                                            <!-- Ubah Kasir -->
                                            <a href="{{ route('admin.kasir.edit', $kasir) }}" 
                                               title="Ubah Data Kasir" 
                                               class="p-2 rounded-xl text-slate-600 hover:text-[#1E2A4A] hover:bg-slate-100 transition">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                                </svg>
                                            </a>

                                            <!-- Reset Password (Buka Modal) -->
                                            <button type="button" 
                                                    @click="$dispatch('open-modal', { name: 'modal-reset-password', data: { id: '{{ $kasir->id }}', name: '{{ addslashes($kasir->name) }}', action: '{{ route('admin.kasir.resetPassword', $kasir) }}' } })" 
                                                    title="Reset Password Kasir" 
                                                    class="p-2 rounded-xl text-amber-600 hover:bg-amber-50 transition">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                                </svg>
                                            </button>

                                            <!-- Toggle Status (Buka Modal) -->
                                            <button type="button" 
                                                    @click="$dispatch('open-modal', { name: 'modal-toggle-kasir', data: { id: '{{ $kasir->id }}', name: '{{ addslashes($kasir->name) }}', is_aktif: {{ $kasir->is_aktif ? 'true' : 'false' }}, action: '{{ route('admin.kasir.toggle', $kasir) }}' } })" 
                                                    title="{{ $kasir->is_aktif ? 'Nonaktifkan Kasir' : 'Aktifkan Kasir' }}" 
                                                    class="p-2 rounded-xl {{ $kasir->is_aktif ? 'text-rose-600 hover:bg-rose-50' : 'text-emerald-600 hover:bg-emerald-50' }} transition">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5.636 18.364a9 9 0 010-12.728m12.728 0a9 9 0 010 12.728m-9.9-2.829a5 5 0 010-7.07m7.072 0a5 5 0 010 7.07M12 4v8" />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if ($kasirs->hasPages())
                    <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                        {{ $kasirs->links() }}
                    </div>
                @endif
            </div>
        @else
            <!-- Empty State jika Belum Ada Kasir -->
            <x-empty-state 
                title="Belum Ada Akun Kasir" 
                description="Kios Anda belum memiliki petugas kasir terdaftar. Tambahkan kasir untuk mulai mengoperasikan penjualan di toko Anda.">
                
                <a href="{{ route('admin.kasir.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#166534] hover:bg-[#EA580C] text-white font-bold text-xs shadow-sm transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <line x1="12" y1="5" x2="12" y2="19" />
                        <line x1="5" y1="12" x2="19" y2="12" />
                    </svg>
                    <span>Tambah Kasir Sekarang</span>
                </a>
            </x-empty-state>
        @endif

    </div>

    <!-- Modal Konfirmasi Toggle Aktif/Nonaktif -->
    <div x-data="{ open: false, data: {} }"
         x-on:open-modal.window="if ($event.detail.name === 'modal-toggle-kasir') { open = true; data = $event.detail.data; }"
         x-on:keydown.escape.window="open = false"
         x-show="open"
         x-cloak
         class="relative z-50">
        
        <div class="fixed inset-0 bg-black/50 backdrop-blur-xs transition-opacity" @click="open = false"></div>

        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4 text-center">
                <div class="relative transform overflow-hidden rounded-3xl bg-white border border-slate-200 p-6 sm:p-7 text-left shadow-2xl transition-all w-full sm:max-w-md">
                    <!-- Header: Ikon Sesuai Konteks & Judul Sejajar Rapi -->
                    <div class="flex items-start gap-3.5 mb-4">
                        <div class="w-11 h-11 rounded-2xl flex items-center justify-center shrink-0 border shadow-xs"
                             :class="data.is_aktif ? 'bg-amber-50 text-amber-600 border-amber-200/70' : 'bg-emerald-50 text-emerald-600 border-emerald-200/70'">
                            <!-- Ikon Peringatan untuk Nonaktifkan -->
                            <template x-if="data.is_aktif">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </template>
                            <!-- Ikon Centang Sukses untuk Aktifkan Kembali -->
                            <template x-if="!data.is_aktif">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </template>
                        </div>

                        <div class="pt-0.5">
                            <h3 class="text-base font-bold text-slate-900" 
                                x-text="data.is_aktif ? 'Nonaktifkan Akun Kasir?' : 'Aktifkan Kembali Kasir?'"></h3>
                            <p class="text-xs text-slate-500 mt-0.5">
                                Konfirmasi perubahan hak akses masuk kasir ke sistem.
                            </p>
                        </div>
                    </div>

                    <!-- Ringkasan Kasir yang Dituju -->
                    <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200/70 flex items-center justify-between gap-3 mb-3">
                        <div class="min-w-0 pr-2">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-0.5">Petugas Kasir</span>
                            <p class="text-xs sm:text-sm font-bold text-slate-900 truncate" x-text="data.name"></p>
                        </div>
                        <div class="text-right shrink-0">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-0.5">Username</span>
                            <span class="inline-block font-mono text-[11px] font-bold px-2 py-0.5 rounded-lg bg-white border border-slate-200 text-slate-700 shadow-2xs" x-text="'@' + (data.username || '')"></span>
                        </div>
                    </div>

                    <!-- Kotak Informasi Catatan Rapi -->
                    <div class="p-3.5 rounded-2xl border text-xs flex items-start gap-2.5 transition mb-5"
                         :class="data.is_aktif ? 'bg-amber-50/60 border-amber-200/70 text-amber-900' : 'bg-emerald-50/60 border-emerald-200/70 text-emerald-900'">
                        <svg class="w-4 h-4 shrink-0 mt-0.5" :class="data.is_aktif ? 'text-amber-600' : 'text-emerald-600'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="12" y1="16" x2="12" y2="12"/>
                            <line x1="12" y1="8" x2="12.01" y2="8"/>
                        </svg>
                        <div class="leading-relaxed text-[11px]">
                            <strong class="font-bold">Catatan:</strong> 
                            <span x-show="data.is_aktif">Kasir tidak akan bisa masuk ke sistem. Sesi aktif kasir ini akan langsung terputus secara otomatis demi keamanan kios.</span>
                            <span x-show="!data.is_aktif">Kasir akan diizinkan kembali untuk masuk dan melayani transaksi penjualan di kasir.</span>
                        </div>
                    </div>

                    <!-- Tombol Aksi Rapi & Seimbang -->
                    <form :action="data.action" method="POST" class="flex items-center justify-end gap-2.5 pt-4 border-t border-slate-100">
                        @csrf
                        <button type="button" @click="open = false" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 font-bold text-xs transition">
                            Batal
                        </button>
                        <button type="submit" 
                                class="px-5 py-2.5 rounded-xl font-bold text-xs text-white shadow-xs transition"
                                :class="data.is_aktif ? 'bg-amber-600 hover:bg-amber-700' : 'bg-[#166534] hover:bg-[#14532D]'"
                                x-text="data.is_aktif ? 'Ya, Nonaktifkan' : 'Ya, Aktifkan Kembali'"></button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Reset Password Kasir -->
    <div x-data="{ open: false, data: {}, showPass: false, showPassConfirm: false }"
         x-on:open-modal.window="if ($event.detail.name === 'modal-reset-password') { open = true; data = $event.detail.data; showPass = false; showPassConfirm = false; }"
         x-on:keydown.escape.window="open = false"
         x-show="open"
         x-cloak
         class="relative z-50">
        
        <div class="fixed inset-0 bg-black/50 backdrop-blur-xs transition-opacity" @click="open = false"></div>

        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4 text-center">
                <div class="relative transform overflow-hidden rounded-3xl bg-white border border-slate-200 p-6 sm:p-8 text-left shadow-2xl transition-all w-full sm:max-w-md">
                    <div class="flex items-start gap-4 mb-4">
                        <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 border border-amber-200 flex items-center justify-center shrink-0">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-[#1E2A4A]">Reset Password Kasir</h3>
                            <p class="text-xs text-slate-500 mt-1">
                                Masukkan password baru untuk <span class="font-bold text-slate-700" x-text="data.name"></span>. Sesi aktif kasir akan langsung dihentikan.
                            </p>
                        </div>
                    </div>

                    <form :action="data.action" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Password Baru (min. 8 karakter)</label>
                            <div class="relative">
                                <input :type="showPass ? 'text' : 'password'" 
                                       name="password" 
                                       required 
                                       placeholder="Password baru"
                                       style="-ms-reveal: none; -ms-clear: none;"
                                       class="w-full rounded-xl border border-slate-300 py-2.5 px-3 pr-10 text-xs text-slate-800 focus:border-[#166534] focus:ring-1 focus:ring-[#166534]" />
                                <button type="button" 
                                        @click="showPass = !showPass" 
                                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-700 transition focus:outline-none"
                                        tabindex="-1"
                                        title="Tampilkan atau sembunyikan password">
                                    <svg x-show="!showPass" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                                    </svg>
                                    <svg x-show="showPass" x-cloak class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Konfirmasi Password Baru</label>
                            <div class="relative">
                                <input :type="showPassConfirm ? 'text' : 'password'" 
                                       name="password_confirmation" 
                                       required 
                                       placeholder="Ulangi password baru"
                                       style="-ms-reveal: none; -ms-clear: none;"
                                       class="w-full rounded-xl border border-slate-300 py-2.5 px-3 pr-10 text-xs text-slate-800 focus:border-[#166534] focus:ring-1 focus:ring-[#166534]" />
                                <button type="button" 
                                        @click="showPassConfirm = !showPassConfirm" 
                                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-700 transition focus:outline-none"
                                        tabindex="-1"
                                        title="Tampilkan atau sembunyikan konfirmasi password">
                                    <svg x-show="!showPassConfirm" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                                    </svg>
                                    <svg x-show="showPassConfirm" x-cloak class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="mt-6 flex flex-col-reverse sm:flex-row sm:justify-end gap-3 pt-4 border-t border-slate-100">
                            <button type="button" @click="open = false" class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50 font-bold text-xs transition">
                                Batal
                            </button>
                            <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#166534] hover:bg-[#EA580C] text-white font-bold text-xs shadow-sm transition">
                                Simpan Password Baru
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layouts.admin>
