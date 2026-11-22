<x-layouts.admin title="Ubah Data Kasir">
    <div class="max-w-2xl mx-auto space-y-6">

        <!-- Page Header -->
        <x-page-header 
            title="Ubah Data Kasir" 
            description="Perbarui profil nama dan username login untuk kasir {{ $kasir->name }}.">
            
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
            <form method="POST" action="{{ route('admin.kasir.update', $kasir) }}" class="space-y-5">
                @csrf
                @method('PUT')

                <!-- Nama Kasir -->
                <div>
                    <label for="name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Nama Lengkap Kasir <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" 
                           id="name" 
                           name="name" 
                           value="{{ old('name', $kasir->name) }}" 
                           placeholder="Nama lengkap kasir" 
                           required 
                           autofocus 
                           class="w-full rounded-xl border border-slate-300 py-2.5 px-3.5 text-xs sm:text-sm text-slate-800 focus:border-[#166534] focus:ring-1 focus:ring-[#166534] transition" />
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
                               value="{{ old('username', $kasir->username) }}" 
                               placeholder="username_kasir" 
                               required 
                               class="w-full rounded-xl border border-slate-300 py-2.5 pl-8 pr-3.5 text-xs sm:text-sm text-slate-800 font-mono focus:border-[#166534] focus:ring-1 focus:ring-[#166534] transition" />
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1">Username harus unik dan minimal 4 karakter.</p>
                    <x-input-error :messages="$errors->get('username')" class="mt-1 text-xs" />
                </div>

                <!-- Informasi Password Terpisah -->
                <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-xs text-amber-800 flex items-start gap-3">
                    <svg class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                    </svg>
                    <div class="leading-relaxed">
                        <strong>Ingin mengganti kata sandi kasir?</strong> Untuk alasan keamanan dan pemutusan sesi lama, pergantian kata sandi kasir dilakukan melalui fitur <strong>Reset Password</strong> pada tombol aksi di halaman daftar kasir.
                    </div>
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
                        <span>Simpan Perubahan</span>
                    </button>
                </div>
            </form>
        </div>

    </div>
</x-layouts.admin>
