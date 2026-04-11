<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VoucherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $voucherId = $this->route('voucher')?->id;

        return [
            'code' => ['required', 'string', 'max:255', Rule::unique('vouchers', 'code')->ignore($voucherId)],
            'discount_type' => ['required', 'string', Rule::in(['fixed', 'percentage'])],
            'amount' => ['required', 'integer', 'min:0'],
            'valid_until' => ['nullable', 'date'],
            'minimum_transaction' => ['required', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
