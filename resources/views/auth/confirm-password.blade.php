<x-guest-layout maxWidth="sm:max-w-[420px]">
    <div class="mb-5 text-center">
        <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Konfirmasi Kata Sandi</h2>
        <p class="text-xs text-gray-500 mt-1 leading-relaxed">
            Ini adalah area aman. Harap konfirmasi kata sandi Anda sebelum melanjutkan.
        </p>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-4">
        @csrf

        <!-- Password -->
        <div x-data="{ show: false }">
            <label for="password" class="block text-xs font-semibold text-gray-700 mb-1">
                Kata Sandi <span class="text-rose-600">*</span>
            </label>
            <div class="relative">
                <input :type="show ? 'text' : 'password'" 
                       id="password" 
                       name="password" 
                       placeholder="Masukkan kata sandi" 
                       required 
                       autocomplete="current-password"
                       style="-ms-reveal: none; -ms-clear: none;"
                       class="w-full bg-white border border-gray-300 rounded-lg px-3.5 py-2.5 pr-10 text-gray-900 placeholder-gray-400 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-[#166534] focus:border-[#166534] transition shadow-xs" />

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

        <div class="pt-2">
            <button type="submit" 
                    class="w-full min-h-[44px] py-2.5 px-4 bg-[#166534] hover:bg-[#14532D] text-white font-bold text-xs uppercase tracking-wider rounded-lg shadow-sm hover:shadow transition flex items-center justify-center gap-2 focus:outline-none focus:ring-2 focus:ring-[#166534] focus:ring-offset-2">
                <span>Konfirmasi Kata Sandi</span>
            </button>
        </div>
    </form>
</x-guest-layout>
