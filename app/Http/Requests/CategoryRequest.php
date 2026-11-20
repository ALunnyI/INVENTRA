<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required', 'string', 'max:100',
                Rule::unique('categories', 'name')->ignore($this->category),
            ],
            'description' => ['nullable', 'string', 'max:500'],
            'code_prefix' => [
                'nullable', 'regex:/^[A-Z0-9]{2,6}$/',
                Rule::unique('categories', 'code_prefix')->ignore($this->category),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama kategori wajib diisi.',
            'name.unique' => 'Nama kategori sudah digunakan.',
            'code_prefix.regex'  => 'Prefix harus 2 sampai 6 karakter berupa huruf atau angka.',
            'code_prefix.unique' => 'Prefix sudah dipakai kategori lain.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('code_prefix')) {
            $this->merge(['code_prefix' => strtoupper(trim($this->code_prefix))]);
        }
    }
}
