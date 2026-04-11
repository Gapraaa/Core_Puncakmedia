<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BookingAdjustmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'extend_check_out' => ['nullable', 'date'],
            'selected_addons' => ['nullable', 'array'],
            'selected_addons.*' => ['exists:addons,id'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $extendDate = $this->input('extend_check_out');
            $booking = $this->route('booking');

            if ($extendDate !== null && $booking !== null && $extendDate <= $booking->check_out->format('Y-m-d')) {
                $validator->errors()->add('extend_check_out', 'Tanggal check-out baru harus lebih besar dari check-out saat ini.');
            }

            if (blank($extendDate) && empty($this->input('selected_addons', []))) {
                $validator->errors()->add('selected_addons', 'Pilih add-on atau isi extend booking terlebih dahulu.');
            }
        });
    }
}
