<x-guest-layout maxWidth="sm:max-w-[420px]">
    <div class="mb-5 text-center">
        <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Lupa Kata Sandi</h2>
        <p class="text-xs text-gray-500 mt-1 leading-relaxed">
            Masukkan email terdaftar untuk menerima tautan pemulihan kata sandi.
        </p>
    </div>

    <!-- Catatan untuk Kasir -->
    <div class="mb-4 p-3 rounded-lg bg-amber-50 border border-amber-200 text-xs text-amber-800 flex items-start gap-2.5">
        <svg class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/>
        </svg>
        <span>
            <strong>Catatan untuk Kasir:</strong> Fitur pemulihan email khusus untuk akun Admin Pemilik. Jika Anda adalah Kasir, silakan hubungi Admin Kios Anda untuk reset kata sandi.
        </span>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4 text-xs font-semibold text-[#166534] bg-[#DCFCE7] p-3 rounded-lg border border-[#86EFAC] text-center" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf

        <!-- Email Address -->
        <div>
            <label for="email" class="block text-xs font-semibold text-gray-700 mb-1">
                Email Terdaftar <span class="text-rose-600">*</span>
            </label>
            <div class="relative">
                <input id="email" 
                       type="email" 
                       name="email" 
                       value="{{ old('email') }}" 
                       placeholder="nama@email.com" 
                       required 
                       autofocus 
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

        <div class="pt-2 space-y-3">
            <button type="submit" 
                    class="w-full min-h-[44px] py-2.5 px-4 bg-[#166534] hover:bg-[#14532D] text-white font-bold text-xs uppercase tracking-wider rounded-lg shadow-sm hover:shadow transition flex items-center justify-center gap-2 focus:outline-none focus:ring-2 focus:ring-[#166534] focus:ring-offset-2">
                <span>Kirim Tautan Reset Kata Sandi</span>
            </button>

            <div class="text-center pt-2">
                <a href="{{ route('login') }}" class="text-xs font-bold text-[#166534] hover:underline">
                    &larr; Kembali ke Halaman Masuk
                </a>
            </div>
        </div>
    </form>
</x-guest-layout>
