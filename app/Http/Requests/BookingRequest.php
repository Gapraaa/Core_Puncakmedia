<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'brand_id' => ['required', 'exists:brands,id'],
            'villa_id' => ['required', 'exists:villas,id'],
            'villa_unit_id' => ['required', 'exists:villa_units,id'],
            'guest_name' => ['required', 'string', 'max:255'],
            'guest_phone' => ['required', 'string', 'max:255'],
            'check_in' => ['required', 'date'],
            'check_out' => ['required', 'date', 'after:check_in'],
            'voucher_id' => ['nullable', 'exists:vouchers,id'],
            'manual_discount_amount' => ['nullable', 'integer', 'min:0'],
            'manual_discount_reason' => ['nullable', 'string'],
            'selected_addons' => ['nullable', 'array'],
            'selected_addons.*' => ['exists:addons,id'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $manualDiscount = (int) $this->input('manual_discount_amount', 0);
            $reason = trim((string) $this->input('manual_discount_reason', ''));

            if ($manualDiscount > 0 && $reason === '') {
                $validator->errors()->add('manual_discount_reason', 'Alasan diskon manual wajib diisi saat nilai diskon manual lebih dari 0.');
            }
        });
    }
}
