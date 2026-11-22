<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSupplierRequest extends FormRequest
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
        $supplierId = $this->route('supplier') instanceof \App\Models\Supplier
            ? $this->route('supplier')->id
            : $this->route('supplier');

        return [
            'nama' => [
                'required',
                'string',
                'max:150',
                Rule::unique('supplier', 'nama')
                    ->where('kios_id', $this->user()->kios_id)
                    ->ignore($supplierId),
            ],
            'no_telepon' => [
                'nullable',
                'string',
                'max:30',
            ],
            'alamat' => [
                'nullable',
                'string',
                'max:500',
            ],
        ];
    }

    /**
     * Custom message validation in Indonesian.
     */
    public function messages(): array
    {
        return [
            'nama.required' => 'Nama supplier wajib diisi.',
            'nama.unique' => 'Nama supplier sudah digunakan di kios Anda.',
            'no_telepon.max' => 'Nomor telepon maksimal 30 karakter.',
            'alamat.max' => 'Alamat maksimal 500 karakter.',
        ];
    }
}
