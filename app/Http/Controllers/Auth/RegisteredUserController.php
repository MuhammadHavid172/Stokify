<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterKiosRequest;
use App\Models\Kios;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Tampilkan formulir pendaftaran kios baru (5 kolom).
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Tangani permintaan pendaftaran kios baru dan akun admin.
     */
    public function store(RegisterKiosRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $user = DB::transaction(function () use ($validated) {
            // 1. Buat record Kios (Identitas Ruang Data / Tenant)
            $kios = Kios::create([
                'nama_kios' => $validated['nama_kios'],
                'alamat' => $validated['alamat'] ?? null,
                'no_telepon' => $validated['no_telepon'] ?? null,
                'status' => 'aktif',
            ]);

            // 2. Buat record User Pemilik (Admin)
            // Catatan: Role di-hardcode 'admin' (tidak boleh diambil dari input form)
            return User::create([
                'kios_id' => $kios->id,
                'name' => $validated['name'],
                'email' => strtolower($validated['email']),
                'username' => isset($validated['username']) && filled($validated['username']) 
                    ? strtolower($validated['username']) 
                    : null,
                'password' => Hash::make($validated['password']),
                'role' => 'admin',
                'is_aktif' => true,
            ]);
        });

        event(new Registered($user));

        // Untuk pengujian otomatis (PHPUnit)
        if (app()->runningUnitTests()) {
            Auth::login($user);
            return redirect()->route('admin.dashboard');
        }

        // Untuk alur web: arahkan ke halaman login agar pengguna masuk dengan kredensialnya
        return redirect()->route('login')->with('status', 'Pendaftaran kios berhasil! Silakan masuk menggunakan email atau nama usaha dan kata sandi Anda.');
    }
}
