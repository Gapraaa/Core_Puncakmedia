<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'integer', 'min:1'],
            'payment_method' => ['required', 'string', Rule::in(['cash', 'transfer'])],
            'received_by' => ['required', 'string', Rule::in(['finance', 'office', 'field_staff'])],
            'note' => ['nullable', 'string'],
            'proof_image' => ['nullable', 'string', 'max:255'],
            'paid_at' => ['nullable', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'amount.required' => 'Nominal pembayaran wajib diisi.',
            'amount.min' => 'Nominal pembayaran minimal Rp 1.',
            'payment_method.required' => 'Metode pembayaran wajib dipilih.',
            'received_by.required' => 'Penerima pembayaran wajib dipilih.',
        ];
    }
}
