<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterKiosRequest extends FormRequest
{
    /**
     * Tentukan apakah pengguna diizinkan untuk membuat permintaan ini.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Dapatkan aturan validasi yang berlaku untuk pendaftaran kios baru (5 kolom utama).
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // 1. Nama Kios / Usaha (Wajib — identitas ruang data/tenant)
            'nama_kios' => ['required', 'string', 'max:255'],

            // 2. Nama Pemilik (Wajib — nama profil Admin)
            'name' => ['required', 'string', 'max:255'],

            // 3. Email (Wajib — login dan pemulihan sandi)
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                'unique:users,email',
            ],

            // 4 & 5. Password & Konfirmasi Password (Wajib)
            'password' => [
                'required',
                'confirmed',
                Password::defaults()->min(8),
            ],

            // Kolom opsional (fleksibel jika dikirim sistem lama/test)
            'username' => [
                'nullable',
                'string',
                'min:4',
                'max:50',
                'regex:/^[a-z0-9._]+$/',
                'unique:users,username',
            ],
            'alamat' => ['nullable', 'string'],
            'no_telepon' => ['nullable', 'string', 'max:30'],
        ];
    }

    /**
     * Dapatkan pesan kesalahan khusus Bahasa Indonesia.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nama_kios.required' => 'Nama Kios / Usaha wajib diisi.',
            'name.required' => 'Nama Pemilik wajib diisi.',
            'email.required' => 'Alamat Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah terdaftar. Silakan gunakan email lain atau masuk ke akun Anda.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal terdiri dari 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'username.unique' => 'Username ini sudah terdaftar. Silakan pilih username lain.',
        ];
    }
}
