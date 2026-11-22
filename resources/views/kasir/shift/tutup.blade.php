<!DOCTYPE html>
<html lang="id" class="h-full bg-[#F6F7F5]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Tutup Shift Kasir - {{ $user->kios->nama_kios }} - Stokify</title>

    <!-- Favicon Stokify -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

    <!-- Fonts: Inter & Poppins -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|poppins:500,600,700,800&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-[#F6F7F5] text-gray-800 antialiased font-sans flex flex-col selection:bg-[#DCFCE7] selection:text-[#166534]">

    <!-- Topbar Kasir Sederhana -->
    <header class="bg-white border-b border-gray-200 px-4 py-3 sticky top-0 z-30 shadow-xs">
        <div class="max-w-4xl mx-auto flex items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-amber-50 border border-amber-200 text-amber-700 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-sm font-bold text-gray-900">Penutupan Shift Kasir</h1>
                    <p class="text-[11px] text-gray-500">{{ $user->kios->nama_kios }} &bull; {{ $user->name }}</p>
                </div>
            </div>

            <a href="{{ route('kasir.index') }}" 
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white hover:bg-gray-50 border border-gray-200 text-xs font-semibold text-gray-700 transition">
                <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
                <span>Kembali ke Kasir</span>
            </a>
        </div>
    </header>

    <!-- Konten Form Tutup Shift -->
    <main class="flex-1 flex items-center justify-center p-4">
        <div class="w-full max-w-xl bg-white border border-gray-200 rounded-2xl p-6 sm:p-8 shadow-sm"
             x-data="{
                 uangFisikRaw: '{{ old('uang_fisik', '') }}',
                 submitting: false,
                 get formattedUangFisik() {
                     let clean = (this.uangFisikRaw + '').replace(/\D/g, '');
                     if (!clean) return '';
                     return new Intl.NumberFormat('id-ID').format(clean);
                 },
                 onInput(e) {
                     let clean = e.target.value.replace(/\D/g, '');
                     this.uangFisikRaw = clean;
                 }
             }">

            <!-- Header Form -->
            <div class="text-center mb-6">
                <div class="w-14 h-14 rounded-2xl bg-amber-50 border border-amber-200 text-amber-700 mx-auto flex items-center justify-center mb-3">
                    <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                    </svg>
                </div>
                <h2 class="text-xl font-bold text-gray-900 text-balance">Hitung Fisik Uang di Laci</h2>
                <p class="text-xs text-gray-500 mt-1 max-w-md mx-auto text-pretty">
                    Terapkan metode hitung buta: hitung dan masukkan seluruh uang tunai fisik yang ada di laci kasir saat ini.
                </p>
            </div>

            <!-- Ringkasan Info Shift Berjalan -->
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 p-4 rounded-xl bg-gray-50 border border-gray-200 mb-6 text-xs">
                <div>
                    <span class="text-gray-500 block text-[11px]">Waktu Buka Shift</span>
                    <strong class="text-gray-900 font-semibold">
                        {{ \Carbon\Carbon::parse($shift->waktu_buka)->timezone('Asia/Jayapura')->format('H:i') }} WIT
                    </strong>
                </div>
                <div>
                    <span class="text-gray-500 block text-[11px]">Durasi Shift</span>
                    <strong class="text-gray-900 font-semibold">{{ $shift->durasi }}</strong>
                </div>
                <div class="col-span-2 sm:col-span-1">
                    <span class="text-gray-500 block text-[11px]">Modal Awal</span>
                    <strong class="text-[#166534] font-semibold tabular-nums">Rp {{ number_format($shift->modal_awal, 0, ',', '.') }}</strong>
                </div>
            </div>

            <!-- Banner Peringatan Selisih / Error -->
            @if ($errors->any())
                <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs shadow-xs">
                    <div class="flex items-start gap-2.5">
                        <svg class="w-5 h-5 text-rose-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10" />
                            <line x1="12" y1="8" x2="12" y2="12" />
                            <line x1="12" y1="16" x2="12.01" y2="16" />
                        </svg>
                        <div class="space-y-1">
                            <span class="font-bold text-rose-800">Penutupan shift belum dapat diproses:</span>
                            @foreach ($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Info Box Hitung Buta -->
            <div class="p-3.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs mb-6 flex items-start gap-2.5">
                <svg class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="16" x2="12" y2="12"/>
                    <line x1="12" y1="8" x2="12.01" y2="8"/>
                </svg>
                <div class="leading-relaxed teks-penjelasan">
                    Sistem akan membandingkan uang fisik Anda dengan hasil pencatatan penjualan secara otomatis. Jika terdapat selisih uang, catatan pertanggungjawaban wajib diisi.
                </div>
            </div>

            <form method="POST" action="{{ route('kasir.shift.tutup.post') }}" @submit="submitting = true" class="space-y-5">
                @csrf

                <!-- Input Uang Fisik -->
                <div>
                    <label for="uang_fisik_display" class="block text-xs font-semibold text-gray-700 mb-1.5">
                        Total Uang Fisik di Laci <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-base font-bold text-gray-400">
                            Rp
                        </div>
                        <input type="text"
                               id="uang_fisik_display"
                               :value="formattedUangFisik"
                               @input="onInput"
                               placeholder="0"
                               autofocus
                               required
                               class="w-full pl-11 pr-4 py-3 bg-white border {{ $errors->has('uang_fisik') ? 'border-rose-500' : 'border-gray-200' }} rounded-xl text-xl font-bold text-gray-900 focus:outline-none focus:border-[#166534] focus:ring-1 focus:ring-[#166534] transition tabular-nums" />
                        <input type="hidden" name="uang_fisik" :value="uangFisikRaw">
                    </div>
                </div>

                <!-- Input Catatan Pertanggungjawaban -->
                <div>
                    <label for="catatan" class="block text-xs font-semibold text-gray-700 mb-1.5">
                        Catatan Pertanggungjawaban
                        <span class="text-[11px] font-normal text-gray-400 lowercase">(wajib jika terdapat selisih kas, minimal 5 karakter)</span>
                    </label>
                    <textarea id="catatan"
                              name="catatan"
                              rows="3"
                              placeholder="Tuliskan alasan jika uang fisik di laci kurang atau lebih dari seharusnya..."
                              class="w-full px-3.5 py-2.5 bg-white border {{ $errors->has('catatan') ? 'border-rose-500' : 'border-gray-200' }} rounded-xl text-xs text-gray-900 placeholder-gray-400 focus:outline-none focus:border-[#166534] focus:ring-1 focus:ring-[#166534] transition leading-relaxed">{{ old('catatan') }}</textarea>
                </div>

                <!-- Tombol Aksi -->
                <div class="pt-3 flex flex-col sm:flex-row items-center gap-3">
                    <a href="{{ route('kasir.index') }}" 
                       class="w-full sm:w-1/3 py-2.5 px-4 rounded-lg bg-white hover:bg-gray-50 border border-gray-200 text-xs font-semibold text-gray-700 text-center transition">
                        Batal
                    </a>
                    <button type="submit"
                            :disabled="submitting || !uangFisikRaw"
                            class="w-full sm:w-2/3 py-2.5 px-6 rounded-lg bg-amber-600 hover:bg-amber-700 text-white font-semibold text-xs tracking-wide shadow-xs transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                        </svg>
                        <span x-text="submitting ? 'Memproses Penutupan...' : 'Tutup Shift & Rekonsiliasi'"></span>
                    </button>
                </div>
            </form>

        </div>
    </main>

</body>
</html>
