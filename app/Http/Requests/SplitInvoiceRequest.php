<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SplitInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'label' => ['required', 'string', 'max:255'],
            'item_ids' => ['required', 'array', 'min:1'],
            'item_ids.*' => ['integer', 'exists:booking_items,id'],
        ];
    }
}
