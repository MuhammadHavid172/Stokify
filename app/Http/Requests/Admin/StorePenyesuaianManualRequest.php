<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePenyesuaianManualRequest extends FormRequest
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
            'items.*.jenis' => [
                'required',
                'string',
                Rule::in(['rusak', 'hilang', 'kedaluwarsa', 'koreksi_lain']),
            ],
            'items.*.arah' => [
                'nullable',
                'string',
                Rule::in(['tambah', 'kurang']),
            ],
            'items.*.jumlah' => [
                'required',
                'integer',
                'min:1',
            ],
            'items.*.alasan' => [
                'required',
                'string',
                'min:3',
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
            'tanggal.required' => 'Tanggal penyesuaian wajib diisi.',
            'tanggal.before_or_equal' => 'Tanggal penyesuaian tidak boleh lebih dari hari ini.',
            'items.required' => 'Minimal satu barang harus ditambahkan.',
            'items.min' => 'Minimal satu barang harus ditambahkan.',
            'items.*.barang_id.required' => 'Barang wajib dipilih.',
            'items.*.barang_id.exists' => 'Barang yang dipilih tidak valid atau bukan milik kios Anda.',
            'items.*.jenis.required' => 'Jenis penyesuaian wajib dipilih.',
            'items.*.jenis.in' => 'Jenis penyesuaian tidak valid.',
            'items.*.jumlah.required' => 'Jumlah barang wajib diisi.',
            'items.*.jumlah.min' => 'Jumlah penyesuaian minimal 1.',
            'items.*.alasan.required' => 'Alasan penyesuaian wajib diisi.',
            'items.*.alasan.min' => 'Alasan penyesuaian minimal 3 karakter.',
        ];
    }
}
