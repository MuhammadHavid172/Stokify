<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBarangRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->role === 'admin';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $kiosId = $this->user()->kios_id;

        return [
            'kode' => [
                'required',
                'string',
                'max:50',
                Rule::unique('barang', 'kode')->where('kios_id', $kiosId),
            ],
            'nama' => [
                'required',
                'string',
                'max:255',
            ],
            'kategori_id' => [
                'nullable',
                'integer',
                Rule::exists('kategori', 'id')->where('kios_id', $kiosId),
            ],
            'satuan' => [
                'required',
                'string',
                'max:50',
            ],
            'harga_modal' => [
                'required',
                'integer',
                'min:0',
            ],
            'harga_jual' => [
                'required',
                'integer',
                'min:0',
            ],
            'stok_awal' => [
                'nullable',
                'integer',
                'min:0',
            ],
            'stok_minimum' => [
                'nullable',
                'integer',
                'min:0',
            ],
        ];
    }

    /**
     * Custom message validation in Indonesian.
     */
    public function messages(): array
    {
        return [
            'kode.required' => 'Kode barang wajib diisi.',
            'kode.unique' => 'Kode barang sudah digunakan di kios Anda.',
            'nama.required' => 'Nama barang wajib diisi.',
            'kategori_id.exists' => 'Kategori yang dipilih tidak valid atau bukan milik kios Anda.',
            'satuan.required' => 'Satuan barang wajib diisi.',
            'harga_modal.required' => 'Harga modal wajib diisi.',
            'harga_modal.min' => 'Harga modal tidak boleh bernilai negatif.',
            'harga_jual.required' => 'Harga jual wajib diisi.',
            'harga_jual.min' => 'Harga jual tidak boleh bernilai negatif.',
            'stok_awal.min' => 'Stok awal tidak boleh bernilai negatif.',
            'stok_minimum.min' => 'Stok minimum tidak boleh bernilai negatif.',
        ];
    }
}
