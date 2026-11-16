<?php

namespace App\Http\Requests;

use App\Models\Loan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLoanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $maxDate = now()->addDays(Loan::MAX_DURATION_DAYS)->toDateString();

        return [
            'asset_id' => [
                'required',
                Rule::exists('assets', 'id')->whereNull('deleted_at'),
            ],
            'user_id'  => ['nullable', 'exists:users,id'],
            'due_date' => ['required', 'date', 'after_or_equal:today', 'before_or_equal:' . $maxDate],
            'notes'    => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'asset_id.required'         => 'Aset wajib dipilih.',
            'asset_id.exists'           => 'Aset tidak ditemukan.',
            'due_date.required'         => 'Tanggal jatuh tempo wajib diisi.',
            'due_date.after_or_equal'   => 'Jatuh tempo tidak boleh sebelum hari ini.',
            'due_date.before_or_equal'  => 'Jatuh tempo maksimal ' . Loan::MAX_DURATION_DAYS . ' hari dari hari ini.',
        ];
    }
}
