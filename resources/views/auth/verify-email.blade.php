<x-guest-layout maxWidth="sm:max-w-[440px]">
    <div class="mb-5 text-center">
        <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Verifikasi Email</h2>
        <p class="text-xs text-gray-500 mt-2 leading-relaxed">
            Terima kasih telah mendaftar! Sebelum memulai, silakan verifikasi alamat email Anda dengan mengeklik tautan yang baru saja kami kirimkan.
        </p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 text-xs font-semibold text-[#166534] bg-[#DCFCE7] p-3 rounded-lg border border-[#86EFAC] text-center">
            Tautan verifikasi baru telah dikirimkan ke alamat email yang Anda daftarkan.
        </div>
    @endif

    <div class="pt-2 flex flex-col sm:flex-row items-center justify-between gap-3">
        <form method="POST" action="{{ route('verification.send') }}" class="w-full sm:w-auto">
            @csrf
            <button type="submit" 
                    class="w-full sm:w-auto min-h-[44px] py-2.5 px-4 bg-[#166534] hover:bg-[#14532D] text-white font-bold text-xs uppercase tracking-wider rounded-lg shadow-sm transition flex items-center justify-center">
                <span>Kirim Ulang Email Verifikasi</span>
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}" class="w-full sm:w-auto text-center">
            @csrf
            <button type="submit" class="text-xs font-semibold text-gray-600 hover:text-rose-600 hover:underline">
                Keluar
            </button>
        </form>
    </div>
</x-guest-layout>
