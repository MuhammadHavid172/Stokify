<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBarangMasukRequest extends FormRequest
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
            'tanggal' => [
                'required',
                'date',
                'before_or_equal:today',
            ],
            'supplier_id' => [
                'nullable',
                'integer',
                Rule::exists('supplier', 'id')->where('kios_id', $kiosId),
            ],
            'catatan' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'items' => [
                'required',
                'array',
                'min:1',
            ],
            'items.*.barang_id' => [
                'required',
                'integer',
                Rule::exists('barang', 'id')->where('kios_id', $kiosId)->where('is_aktif', true),
            ],
            'items.*.jumlah' => [
                'required',
                'integer',
                'min:1',
            ],
            'items.*.harga_beli' => [
                'required',
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
            'tanggal.required' => 'Tanggal barang masuk wajib diisi.',
            'tanggal.date' => 'Format tanggal tidak valid.',
            'tanggal.before_or_equal' => 'Tanggal barang masuk tidak boleh lebih dari hari ini.',
            'supplier_id.exists' => 'Supplier yang dipilih tidak valid atau bukan milik kios Anda.',
            'items.required' => 'Daftar barang masuk minimal harus memiliki 1 item.',
            'items.min' => 'Daftar barang masuk minimal harus memiliki 1 item.',
            'items.*.barang_id.required' => 'Barang pada setiap baris wajib dipilih.',
            'items.*.barang_id.exists' => 'Barang yang dipilih tidak valid, berstatus nonaktif, atau bukan milik kios Anda.',
            'items.*.jumlah.required' => 'Jumlah barang wajib diisi.',
            'items.*.jumlah.min' => 'Jumlah barang masuk minimal 1.',
            'items.*.harga_beli.required' => 'Harga beli wajib diisi.',
            'items.*.harga_beli.min' => 'Harga beli tidak boleh negatif.',
        ];
    }
}
