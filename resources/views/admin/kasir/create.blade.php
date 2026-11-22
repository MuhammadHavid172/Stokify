<x-layouts.admin title="Tambah Kasir Baru">
    <div class="max-w-2xl mx-auto space-y-6">

        <!-- Page Header -->
        <x-page-header 
            title="Tambah Kasir Baru" 
            description="Buat akun login petugas kasir untuk melayani penjualan di kios Anda.">
            
            <a href="{{ route('admin.kasir.index') }}" 
               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs transition">
                <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
                <span>Kembali ke Daftar</span>
            </a>
        </x-page-header>

        <!-- Form Card -->
        <div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-8 shadow-xs">
            <form method="POST" 
                  action="{{ route('admin.kasir.store') }}" 
                  class="space-y-5" 
                  autocomplete="off"
                  x-data="{ showPass: false, showPassConfirm: false }"
                  x-init="
                      setTimeout(() => {
                          @if(!$errors->any() && !old('username'))
                              const u = document.getElementById('username');
                              const p = document.getElementById('password');
                              const pc = document.getElementById('password_confirmation');
                              if (u && (!u.defaultValue || u.value.toLowerCase() === 'admin')) { u.value = ''; }
                              if (p) { p.value = ''; }
                              if (pc) { pc.value = ''; }
                          @endif
                      }, 50);
                  ">
                @csrf

                <!-- Jebakan Autofill Browser (Mencegah browser mengisi otomatis username/password admin) -->
                <div style="opacity: 0; position: absolute; top: -9999px; left: -9999px; width: 0; height: 0; overflow: hidden;" aria-hidden="true" tabindex="-1">
                    <input type="text" name="_fake_username_trap" tabindex="-1" autocomplete="username" />
                    <input type="password" name="_fake_password_trap" tabindex="-1" autocomplete="current-password" />
                </div>

                <!-- Nama Kasir -->
                <div>
                    <label for="name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Nama Lengkap Kasir <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" 
                           id="name" 
                           name="name" 
                           value="{{ old('name') }}" 
                           placeholder="Nama lengkap kasir" 
                           required 
                           autofocus 
                           autocomplete="off"
                           class="w-full rounded-xl border border-slate-300 py-2.5 px-3.5 text-xs sm:text-sm text-slate-800 focus:border-[#166534] focus:ring-1 focus:ring-[#166534] transition" />
                    <p class="text-[11px] text-slate-400 mt-1">Nama lengkap yang akan tercetak pada struk kasir.</p>
                    <x-input-error :messages="$errors->get('name')" class="mt-1 text-xs" />
                </div>

                <!-- Username Kasir -->
                <div>
                    <label for="username" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Username Login <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400 font-mono text-xs pointer-events-none">@</span>
                        <input type="text" 
                               id="username" 
                               name="username" 
                               value="{{ old('username') }}" 
                               placeholder="username_kasir" 
                               required 
                               autocomplete="off"
                               class="w-full rounded-xl border border-slate-300 py-2.5 pl-8 pr-3.5 text-xs sm:text-sm text-slate-800 font-mono focus:border-[#166534] focus:ring-1 focus:ring-[#166534] transition" />
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1">Minimal 4 karakter (huruf kecil, angka, titik, atau underscore). Dipakai untuk login.</p>
                    <x-input-error :messages="$errors->get('username')" class="mt-1 text-xs" />
                </div>

                <!-- Catatan Email -->
                <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200 text-[11px] text-slate-500 flex items-start gap-2.5">
                    <svg class="w-4 h-4 text-[#166534] shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="16" x2="12" y2="12"/>
                        <line x1="12" y1="8" x2="12.01" y2="8"/>
                    </svg>
                    <span>
                        <strong>Informasi:</strong> Akun kasir tidak memerlukan email. Kasir masuk menggunakan <strong>Username</strong> dan kata sandi di atas.
                    </span>
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Password Sementara <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <input :type="showPass ? 'text' : 'password'" 
                               id="password" 
                               name="password" 
                               placeholder="Minimal 8 karakter" 
                               required 
                               autocomplete="new-password"
                               style="-ms-reveal: none; -ms-clear: none;"
                               class="w-full rounded-xl border border-slate-300 py-2.5 px-3.5 pr-11 text-xs sm:text-sm text-slate-800 focus:border-[#166534] focus:ring-1 focus:ring-[#166534] transition" />
                        
                        <!-- Tombol Ikon Mata -->
                        <button type="button" 
                                @click="showPass = !showPass" 
                                class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-700 transition focus:outline-none"
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
                    <x-input-error :messages="$errors->get('password')" class="mt-1 text-xs" />
                </div>

                <!-- Konfirmasi Password -->
                <div>
                    <label for="password_confirmation" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Konfirmasi Password <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <input :type="showPassConfirm ? 'text' : 'password'" 
                               id="password_confirmation" 
                               name="password_confirmation" 
                               placeholder="Ulangi password di atas" 
                               required 
                               autocomplete="new-password"
                               style="-ms-reveal: none; -ms-clear: none;"
                               class="w-full rounded-xl border border-slate-300 py-2.5 px-3.5 pr-11 text-xs sm:text-sm text-slate-800 focus:border-[#166534] focus:ring-1 focus:ring-[#166534] transition" />
                        
                        <!-- Tombol Ikon Mata -->
                        <button type="button" 
                                @click="showPassConfirm = !showPassConfirm" 
                                class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-700 transition focus:outline-none"
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
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1 text-xs" />
                </div>

                <!-- Tombol Aksi -->
                <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                    <a href="{{ route('admin.kasir.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-300 hover:bg-slate-50 text-slate-700 font-bold text-xs transition">
                        Batal
                    </a>
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-[#166534] hover:bg-[#EA580C] text-white font-bold text-xs shadow-sm hover:shadow transition transform hover:-translate-y-0.5 active:translate-y-0 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <polyline points="20 6 9 17 4 12" />
                        </svg>
                        <span>Simpan Akun Kasir</span>
                    </button>
                </div>
            </form>
        </div>

    </div>
</x-layouts.admin>
