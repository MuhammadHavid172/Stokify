<x-guest-layout maxWidth="sm:max-w-[420px]">
    <!-- Header Rata Tengah -->
    <div class="mb-6 text-center">
        <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Masuk ke Akun</h2>
        <p class="text-xs sm:text-sm text-gray-500 mt-1 leading-relaxed">
            Selamat datang kembali, silakan masuk ke akun kios Anda.
        </p>
    </div>

    <!-- Session Status / Alert Message -->
    <x-auth-session-status class="mb-4 text-xs font-semibold text-[#166534] bg-[#DCFCE7] p-3 rounded-lg border border-[#86EFAC] text-center" :status="session('status')" />

    @if (session('error'))
        <div class="mb-4 text-xs font-semibold text-rose-700 bg-rose-50 p-3 rounded-lg border border-rose-200 text-center">
            {{ session('error') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-4" autocomplete="off">
        @csrf

        <!-- Dummy trap inputs untuk menyerap autofill bawaan browser -->
        <input type="text" name="_fake_username" style="display:none !important;" tabindex="-1" autocomplete="off" />
        <input type="password" name="_fake_password" style="display:none !important;" tabindex="-1" autocomplete="new-password" />

        <!-- Input 1: Email/Username -->
        <div>
            <label for="login" class="block text-xs font-semibold text-gray-700 mb-1">
                Email/Username
            </label>
            <div class="relative">
                <input id="login" 
                       type="text" 
                       name="login" 
                       value="{{ old('login') ?? old('username') ?? old('email') }}" 
                       placeholder="nama@email.com / username kasir" 
                       required 
                       autocomplete="off"
                       autocorrect="off"
                       autocapitalize="off"
                       spellcheck="false"
                       class="w-full bg-white border border-gray-300 rounded-lg px-3.5 py-2.5 pr-10 text-gray-900 placeholder-gray-400 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-[#166534] focus:border-[#166534] transition shadow-xs" />
                
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
            </div>
            @if ($errors->has('login') || $errors->has('username') || $errors->has('email'))
                <p class="text-xs font-medium text-rose-600 mt-1 pl-1">
                    {{ $errors->first('login') ?: ($errors->first('username') ?: $errors->first('email')) }}
                </p>
            @endif
        </div>

        <!-- Input 2: Kata Sandi with Single Eye Toggle -->
        <div x-data="{ show: false }">
            <label for="password" class="block text-xs font-semibold text-gray-700 mb-1">
                Kata Sandi
            </label>
            <div class="relative">
                <input :type="show ? 'text' : 'password'" 
                       id="password" 
                       name="password" 
                       placeholder="Masukkan kata sandi" 
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
                <p class="text-xs font-medium text-rose-600 mt-1 pl-1">
                    {{ $errors->first('password') }}
                </p>
            @endif
        </div>

        <!-- Checkbox: Ingat Saya & Lupa Kata Sandi -->
        <div class="flex items-center justify-between pt-1">
            <label for="remember_me" class="inline-flex items-center cursor-pointer select-none">
                <input id="remember_me" 
                       type="checkbox" 
                       name="remember" 
                       class="rounded border-gray-300 text-[#166534] focus:ring-[#166534] w-4 h-4 transition cursor-pointer" />
                <span class="ms-2 text-xs text-gray-600 font-medium">Ingat saya</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-xs text-gray-500 hover:text-[#166534] hover:underline transition" href="{{ route('password.request') }}">
                    Lupa kata sandi?
                </a>
            @endif
        </div>

        <!-- Tombol Masuk Sekarang -->
        <div class="pt-2">
            <button type="submit" 
                    class="w-full min-h-[44px] py-2.5 px-4 bg-[#166534] hover:bg-[#14532D] text-white font-bold text-xs uppercase tracking-wider rounded-lg shadow-sm hover:shadow transition flex items-center justify-center gap-2 focus:outline-none focus:ring-2 focus:ring-[#166534] focus:ring-offset-2">
                <span>Masuk Sekarang</span>
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                    <polyline points="12 5 19 12 12 19"></polyline>
                </svg>
            </button>
        </div>

        <!-- Tautan ke Halaman Registrasi Kios -->
        <div class="text-center pt-2 border-t border-gray-100">
            <p class="text-xs text-gray-600">
                Belum punya kios? 
                <a href="{{ route('register') }}" class="font-bold text-[#166534] hover:underline">
                    Daftar Kios Baru
                </a>
            </p>
        </div>
    </form>

    @if (!$errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                function resetLoginFields() {
                    const loginInput = document.getElementById('login');
                    const passInput = document.getElementById('password');
                    if (loginInput) loginInput.value = '';
                    if (passInput) passInput.value = '';
                }
                resetLoginFields();
                setTimeout(resetLoginFields, 50);
                setTimeout(resetLoginFields, 150);
                setTimeout(resetLoginFields, 400);
            });
        </script>
    @endif

    <style>
        /* Hilangkan warna biru autofill bawaan browser agar selalu putih bersih */
        input:-webkit-autofill,
        input:-webkit-autofill:hover, 
        input:-webkit-autofill:focus, 
        input:-webkit-autofill:active {
            -webkit-box-shadow: 0 0 0 1000px white inset !important;
            -webkit-text-fill-color: #111827 !important;
            transition: background-color 5000s ease-in-out 0s;
        }
    </style>
</x-guest-layout>
