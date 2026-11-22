<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateKasirRequest extends FormRequest
{
    /**
     * Tentukan apakah pengguna diizinkan untuk membuat permintaan ini.
     */
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    /**
     * Dapatkan aturan validasi untuk pembaruan profil kasir.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $kasirParam = $this->route('kasir');
        $kasirId = $kasirParam instanceof User ? $kasirParam->id : $kasirParam;

        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => [
                'required',
                'string',
                'min:4',
                'max:50',
                'regex:/^[a-z0-9._]+$/',
                Rule::unique('users', 'username')->ignore($kasirId),
            ],
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
            'name.required' => 'Nama lengkap kasir wajib diisi.',
            'username.required' => 'Username kasir wajib diisi.',
            'username.min' => 'Username minimal terdiri dari 4 karakter.',
            'username.regex' => 'Username hanya boleh menggunakan huruf kecil, angka, titik, dan garis bawah (_).',
            'username.unique' => 'Username ini sudah digunakan. Silakan pilih username lain.',
        ];
    }
}
