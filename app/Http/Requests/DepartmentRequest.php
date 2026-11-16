<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DepartmentRequest extends FormRequest
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
                Rule::unique('departments', 'name')->ignore($this->category),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama department wajib diisi.',
            'name.unique' => 'Nama department sudah digunakan.',
        ];
    }
}
