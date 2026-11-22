<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class BatalBarangMasukRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->role === 'admin';
    }

    /**
     * Normalize input for flexibility.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('alasan_batal') && ! $this->has('alasan')) {
            $this->merge(['alasan' => $this->input('alasan_batal')]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'alasan' => [
                'required',
                'string',
                'min:5',
                'max:255',
            ],
        ];
    }

    /**
     * Custom message validation in Indonesian.
     */
    public function messages(): array
    {
        return [
            'alasan.required' => 'Alasan pembatalan nota wajib diisi.',
            'alasan.min' => 'Alasan pembatalan minimal 5 karakter.',
            'alasan.max' => 'Alasan pembatalan maksimal 255 karakter.',
        ];
    }
}
