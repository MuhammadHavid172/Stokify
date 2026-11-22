<section>
    <header>
        <h2 class="text-base font-bold text-gray-900">
            Informasi Profil
        </h2>

        <p class="mt-1 text-xs sm:text-sm text-gray-500">
            Perbarui nama profil Anda{{ $user->isAdmin() ? ' dan alamat surel (email)' : '' }}.
        </p>
    </header>

    <form method="post" action="{{ route('profile.update') }}" class="mt-5 space-y-4">
        @csrf
        @method('patch')

        <!-- Username (Readonly) -->
        <div>
            <x-input-label for="username" value="Username (ID Masuk)" />
            <input id="username" 
                   type="text" 
                   value="{{ $user->username }}" 
                   disabled 
                   class="mt-1 block w-full bg-gray-50 border border-gray-300 text-gray-500 rounded-lg text-xs sm:text-sm shadow-xs cursor-not-allowed" />
            <p class="mt-1 text-[11px] text-gray-400">Username digunakan untuk login dan tidak dapat diubah.</p>
        </div>

        <!-- Nama Lengkap -->
        <div>
            <x-input-label for="name" value="Nama Lengkap" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-1" :messages="$errors->get('name')" />
        </div>

        @if ($user->isAdmin())
            <!-- Email (Hanya untuk Admin Kios) -->
            <div>
                <x-input-label for="email" value="Alamat Email (Untuk Reset Password)" />
                <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="email" />
                <x-input-error class="mt-1" :messages="$errors->get('email')" />
            </div>
        @else
            <!-- Kasir: Pemberitahuan Email Tersembunyi -->
            <div class="p-3 rounded-lg bg-gray-50 border border-gray-200 text-xs text-gray-600">
                <strong class="text-gray-900">Akun Kasir:</strong> Akun kasir dikelola langsung oleh Admin Kios. Jika Anda lupa kata sandi, silakan hubungi Admin Kios Anda.
            </div>
        @endif

        <div class="flex items-center gap-4 pt-2">
            <button type="submit" class="px-4 py-2 rounded-lg bg-[#166534] hover:bg-[#14532D] text-white text-xs font-semibold transition shadow-xs">
                Simpan Perubahan
            </button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2500)"
                    class="text-xs font-semibold text-[#166534] bg-[#DCFCE7] px-2.5 py-1 rounded-md border border-[#86EFAC]"
                >Berhasil disimpan.</p>
            @endif
        </div>
    </form>
</section>
