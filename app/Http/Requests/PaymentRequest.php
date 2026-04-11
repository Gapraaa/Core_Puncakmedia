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
            'booking_id' => ['required', 'exists:bookings,id'],
            'amount' => ['required', 'integer', 'min:1'],
            'payment_method' => ['required', 'string', Rule::in(['cash', 'transfer'])],
            'received_by' => ['required', 'string', Rule::in(['finance', 'office', 'field_staff'])],
            'note' => ['nullable', 'string'],
            'proof_image' => ['nullable', 'string', 'max:255'],
            'paid_at' => ['required', 'date'],
        ];
    }
}
