<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-xl text-gray-900 leading-tight">
                    Pengaturan Profil
                </h2>
                <p class="text-xs text-gray-500 mt-0.5">Kelola informasi akun dan kata sandi Anda</p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <!-- Informasi Akun -->
            <div class="p-6 bg-white border border-gray-200 shadow-sm rounded-xl">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <!-- Ganti Password -->
            <div class="p-6 bg-white border border-gray-200 shadow-sm rounded-xl">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <!-- Hapus Akun (Hanya untuk Admin Kios) -->
            @if (auth()->user()->isAdmin())
                <div class="p-6 bg-white border border-rose-200 shadow-sm rounded-xl">
                    <div class="max-w-xl">
                        @include('profile.partials.delete-user-form')
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
