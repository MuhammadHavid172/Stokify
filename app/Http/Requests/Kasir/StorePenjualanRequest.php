<?php

namespace App\Http\Requests\Kasir;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePenjualanRequest extends FormRequest
{
    /**
     * Tentukan apakah pengguna berhak membuat permintaan ini.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Aturan validasi untuk request simpan penjualan kasir.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $kiosId = $this->user()?->kios_id;

        return [
            'token' => ['required', 'string'],
            'bayar' => ['required', 'integer', 'min:1'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.barang_id' => [
                'required',
                'integer',
                Rule::exists('barang', 'id')
                    ->where('kios_id', $kiosId)
                    ->where('is_aktif', true),
            ],
            'items.*.jumlah' => ['required', 'integer', 'min:1'],
        ];
    }

    /**
     * Pesan validasi kustom dalam Bahasa Indonesia.
     */
    public function messages(): array
    {
        return [
            'token.required' => 'Token transaksi wajib disertakan.',
            'bayar.required' => 'Nominal pembayaran wajib diisi.',
            'bayar.integer' => 'Nominal pembayaran harus berupa angka bulat.',
            'bayar.min' => 'Nominal pembayaran minimal Rp 1.',
            'items.required' => 'Keranjang belanja tidak boleh kosong.',
            'items.array' => 'Format daftar barang belanja tidak sah.',
            'items.min' => 'Pilih minimal satu barang untuk bertransaksi.',
            'items.*.barang_id.required' => 'ID barang wajib diisi.',
            'items.*.barang_id.exists' => 'Barang tidak ditemukan atau sudah tidak aktif di kios ini.',
            'items.*.jumlah.required' => 'Jumlah barang belanja wajib diisi.',
            'items.*.jumlah.integer' => 'Jumlah barang harus berupa angka bulat.',
            'items.*.jumlah.min' => 'Jumlah barang minimal 1.',
        ];
    }
}
