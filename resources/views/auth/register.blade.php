<x-guest-layout maxWidth="sm:max-w-[460px]">
    <!-- Header Centered -->
    <div class="mb-5 text-center">
        <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Daftar Kios Baru</h2>
        <p class="text-xs text-gray-500 mt-1 leading-relaxed">
            Mulai kelola kasir dan stok barang kios Anda secara terpusat dan aman.
        </p>
    </div>

    <!-- Form Registrasi -->
    <form method="POST" action="{{ route('register') }}" class="space-y-3.5">
        @csrf

        <!-- 1. Nama Kios / Usaha -->
        <div>
            <label for="nama_kios" class="block text-xs font-semibold text-gray-700 mb-1">
                Nama Kios / Usaha <span class="text-rose-600">*</span>
            </label>
            <div class="relative">
                <input id="nama_kios" 
                       type="text" 
                       name="nama_kios" 
                       value="{{ old('nama_kios') }}" 
                       placeholder="Nama usaha atau kios Anda" 
                       required 
                       autofocus 
                       class="w-full bg-white border border-gray-300 rounded-lg px-3.5 py-2.5 pr-10 text-gray-900 placeholder-gray-400 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-[#166534] focus:border-[#166534] transition shadow-xs" />
                
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                </div>
            </div>
            @if ($errors->has('nama_kios'))
                <p class="text-xs font-medium text-rose-600 mt-1 pl-1">{{ $errors->first('nama_kios') }}</p>
            @endif
        </div>

        <!-- 2. Nama Pemilik -->
        <div>
            <label for="name" class="block text-xs font-semibold text-gray-700 mb-1">
                Nama Pemilik (Admin) <span class="text-rose-600">*</span>
            </label>
            <div class="relative">
                <input id="name" 
                       type="text" 
                       name="name" 
                       value="{{ old('name') }}" 
                       placeholder="Nama lengkap pemilik usaha" 
                       required 
                       class="w-full bg-white border border-gray-300 rounded-lg px-3.5 py-2.5 pr-10 text-gray-900 placeholder-gray-400 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-[#166534] focus:border-[#166534] transition shadow-xs" />
                
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
            </div>
            @if ($errors->has('name'))
                <p class="text-xs font-medium text-rose-600 mt-1 pl-1">{{ $errors->first('name') }}</p>
            @endif
        </div>

        <!-- 3. Email Pemilik -->
        <div>
            <label for="email" class="block text-xs font-semibold text-gray-700 mb-1">
                Email Pemilik <span class="text-rose-600">*</span>
            </label>
            <div class="relative">
                <input id="email" 
                       type="email" 
                       name="email" 
                       value="{{ old('email') }}" 
                       placeholder="nama@email.com" 
                       required 
                       autocomplete="email" 
                       class="w-full bg-white border border-gray-300 rounded-lg px-3.5 py-2.5 pr-10 text-gray-900 placeholder-gray-400 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-[#166534] focus:border-[#166534] transition shadow-xs" />
                
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>
            </div>
            @if ($errors->has('email'))
                <p class="text-xs font-medium text-rose-600 mt-1 pl-1">{{ $errors->first('email') }}</p>
            @endif
        </div>

        <!-- 4. Kata Sandi -->
        <div x-data="{ show: false }">
            <label for="password" class="block text-xs font-semibold text-gray-700 mb-1">
                Kata Sandi <span class="text-rose-600">*</span>
            </label>
            <div class="relative">
                <input :type="show ? 'text' : 'password'" 
                       id="password" 
                       name="password" 
                       placeholder="Minimal 8 karakter" 
                       required 
                       autocomplete="new-password" 
                       style="-ms-reveal: none; -ms-clear: none;" 
                       class="w-full bg-white border border-gray-300 rounded-lg px-3.5 py-2.5 pr-10 text-gray-900 placeholder-gray-400 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-[#166534] focus:border-[#166534] transition shadow-xs" />

                <!-- Single Eye Toggle Icon -->
                <button type="button" 
                        @click="show = !show" 
                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 transition focus:outline-none" 
                        tabindex="-1" 
                        aria-label="Tampilkan atau sembunyikan kata sandi">
                    <svg x-show="!show" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                    </svg>
                    <svg x-show="show" x-cloak class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                </button>
            </div>
            @if ($errors->has('password'))
                <p class="text-xs font-medium text-rose-600 mt-1 pl-1">{{ $errors->first('password') }}</p>
            @endif
        </div>

        <!-- 5. Konfirmasi Kata Sandi -->
        <div x-data="{ show: false }">
            <label for="password_confirmation" class="block text-xs font-semibold text-gray-700 mb-1">
                Konfirmasi Kata Sandi <span class="text-rose-600">*</span>
            </label>
            <div class="relative">
                <input :type="show ? 'text' : 'password'" 
                       id="password_confirmation" 
                       name="password_confirmation" 
                       placeholder="Ulangi kata sandi" 
                       required 
                       autocomplete="new-password" 
                       style="-ms-reveal: none; -ms-clear: none;" 
                       class="w-full bg-white border border-gray-300 rounded-lg px-3.5 py-2.5 pr-10 text-gray-900 placeholder-gray-400 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-[#166534] focus:border-[#166534] transition shadow-xs" />

                <!-- Single Eye Toggle Icon -->
                <button type="button" 
                        @click="show = !show" 
                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 transition focus:outline-none" 
                        tabindex="-1" 
                        aria-label="Tampilkan atau sembunyikan konfirmasi kata sandi">
                    <svg x-show="!show" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                    </svg>
                    <svg x-show="show" x-cloak class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                </button>
            </div>
            @if ($errors->has('password_confirmation'))
                <p class="text-xs font-medium text-rose-600 mt-1 pl-1">{{ $errors->first('password_confirmation') }}</p>
            @endif
        </div>

        <!-- Tombol Daftar Sekarang -->
        <div class="pt-2">
            <button type="submit" 
                    class="w-full min-h-[44px] py-2.5 px-4 bg-[#166534] hover:bg-[#14532D] text-white font-bold text-xs uppercase tracking-wider rounded-lg shadow-sm hover:shadow transition flex items-center justify-center gap-2 focus:outline-none focus:ring-2 focus:ring-[#166534] focus:ring-offset-2">
                <span>Daftar Kios Sekarang</span>
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                    <polyline points="12 5 19 12 12 19"></polyline>
                </svg>
            </button>
        </div>

        <!-- Tautan ke Halaman Login -->
        <div class="text-center pt-2 border-t border-gray-100">
            <p class="text-xs text-gray-600">
                Sudah memiliki akun kios? 
                <a href="{{ route('login') }}" class="font-bold text-[#166534] hover:underline">
                    Masuk di sini
                </a>
            </p>
        </div>
    </form>
</x-guest-layout>
