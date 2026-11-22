<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePenyesuaianOpnameRequest extends FormRequest
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
                Rule::exists('barang', 'id')->where('kios_id', $kiosId),
            ],
            'items.*.stok_sistem' => [
                'required',
                'integer',
            ],
            'items.*.stok_fisik' => [
                'nullable',
                'integer',
                'min:0',
            ],
            'items.*.alasan' => [
                'nullable',
                'string',
                'max:255',
            ],
        ];
    }

    /**
     * Custom validation messages in Indonesian.
     */
    public function messages(): array
    {
        return [
            'tanggal.required' => 'Tanggal opname wajib diisi.',
            'tanggal.before_or_equal' => 'Tanggal opname tidak boleh lebih dari hari ini.',
            'items.required' => 'Daftar barang opname tidak boleh kosong.',
            'items.*.barang_id.required' => 'Barang wajib dipilih.',
            'items.*.barang_id.exists' => 'Barang yang dipilih tidak valid atau bukan milik kios Anda.',
            'items.*.stok_sistem.required' => 'Stok sistem tidak valid.',
            'items.*.stok_fisik.min' => 'Stok fisik tidak boleh bernilai negatif.',
        ];
    }
}
