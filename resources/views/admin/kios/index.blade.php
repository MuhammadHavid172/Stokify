<x-admin-layout>
    <x-slot name="header">
        <x-page-header 
            title="Profil Kios" 
            description="Kelola identitas kios Anda yang tercetak pada struk belanja, rekap shift, dan laporan resmi.">
        </x-page-header>
    </x-slot>

    <div class="space-y-6 w-full">
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

        <x-flash-message />

        <!-- 1. Ringkasan Info Read-Only -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <!-- Tanggal Terdaftar -->
            <div class="bg-white border border-gray-200 rounded-xl p-4 flex items-center gap-3.5 shadow-sm">
                <div class="w-10 h-10 rounded-lg bg-[#DCFCE7] flex items-center justify-center text-[#166534] shrink-0 border border-[#86EFAC]/50">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <div>
                    <p class="text-[11px] font-semibold tracking-wider text-gray-500 uppercase">Tanggal Terdaftar</p>
                    <p class="text-sm font-bold text-gray-900 mt-0.5">
                        {{ \Carbon\Carbon::parse($kios->created_at)->timezone('Asia/Jayapura')->locale('id')->isoFormat('D MMMM Y') }}
                    </p>
                </div>
            </div>

            <!-- Jumlah Kasir Aktif -->
            <div class="bg-white border border-gray-200 rounded-xl p-4 flex items-center gap-3.5 shadow-sm">
                <div class="w-10 h-10 rounded-lg bg-[#DCFCE7] flex items-center justify-center text-[#166534] shrink-0 border border-[#86EFAC]/50">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-[11px] font-semibold tracking-wider text-gray-500 uppercase">Kasir Aktif</p>
                    <p class="text-sm font-bold text-gray-900 mt-0.5">
                        {{ $kasirAktifCount }} <span class="text-xs font-normal text-gray-500">Petugas</span>
                    </p>
                </div>
            </div>

            <!-- Jumlah Barang Terdaftar -->
            <div class="bg-white border border-gray-200 rounded-xl p-4 flex items-center gap-3.5 shadow-sm">
                <div class="w-10 h-10 rounded-lg bg-[#DCFCE7] flex items-center justify-center text-[#166534] shrink-0 border border-[#86EFAC]/50">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                </div>
                <div>
                    <p class="text-[11px] font-semibold tracking-wider text-gray-500 uppercase">Total Master Barang</p>
                    <p class="text-sm font-bold text-gray-900 mt-0.5">
                        {{ $barangCount }} <span class="text-xs font-normal text-gray-500">Barang</span>
                    </p>
                </div>
            </div>
        </div>

        <!-- 2. Formulir Pengaturan Profil Kios -->
        <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm w-full">
            <div class="border-b border-gray-100 pb-4 mb-6">
                <h2 class="text-base font-bold text-gray-900">Identitas Kios</h2>
                <p class="text-xs text-gray-500 mt-1 teks-penjelasan">
                    Nama, alamat, dan kontak ini otomatis tercetak pada struk kasir, rekap penutupan shift, serta kop laporan dokumen resmi.
                </p>
            </div>

            <form action="{{ route('admin.kios.update') }}" method="POST" class="space-y-5">
                @csrf
                @method('PUT')

                <!-- Baris Nama Kios & Nomor Telepon (2 Kolom di Layar Lebar) -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <!-- Nama Kios -->
                    <div>
                        <label for="nama_kios" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">
                            Nama Kios <span class="text-rose-600">*</span>
                        </label>
                        <input type="text" 
                               id="nama_kios" 
                               name="nama_kios" 
                               value="{{ old('nama_kios', $kios->nama_kios) }}" 
                               required 
                               maxlength="100"
                               class="w-full bg-white border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:border-[#166534] focus:ring-1 focus:ring-[#166534] transition @error('nama_kios') border-rose-500 @enderror"
                               placeholder="Nama usaha atau kios">
                        @error('nama_kios')
                            <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Nomor Telepon Kios -->
                    <div>
                        <label for="no_telepon" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">
                            Nomor Telepon / WhatsApp
                        </label>
                        <input type="text" 
                               id="no_telepon" 
                               name="no_telepon" 
                               value="{{ old('no_telepon', $kios->no_telepon) }}" 
                               maxlength="20"
                               class="w-full bg-white border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:border-[#166534] focus:ring-1 focus:ring-[#166534] transition @error('no_telepon') border-rose-500 @enderror"
                               placeholder="Nomor telepon aktif / WhatsApp">
                        @error('no_telepon')
                            <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Alamat Kios -->
                <div>
                    <label for="alamat" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">
                        Alamat Lengkap Kios
                    </label>
                    <textarea id="alamat" 
                              name="alamat" 
                              rows="3" 
                              maxlength="255"
                              class="w-full bg-white border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:border-[#166534] focus:ring-1 focus:ring-[#166534] transition @error('alamat') border-rose-500 @enderror"
                              placeholder="Alamat lengkap lokasi kios">{{ old('alamat', $kios->alamat) }}</textarea>
                    @error('alamat')
                        <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Info Banner -->
                <div class="bg-[#DCFCE7]/40 border border-[#86EFAC]/60 rounded-lg p-3.5 flex items-start gap-3">
                    <svg class="w-4 h-4 text-[#166534] shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-xs text-gray-700 leading-relaxed">
                        Perubahan informasi profil langsung berlaku seketika pada seluruh nota penjualan baru dan dokumen ekspor PDF tanpa memerlukan restart aplikasi.
                    </p>
                </div>

                <div class="flex items-center justify-end pt-2">
                    <button type="submit" 
                            class="px-4 py-2.5 rounded-lg bg-[#166534] hover:bg-[#14532D] text-white font-semibold text-xs tracking-wider shadow-sm transition flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Simpan Profil Kios</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
