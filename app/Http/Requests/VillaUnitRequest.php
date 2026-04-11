<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VillaUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'villa_id' => ['required', 'exists:villas,id'],
            'unit_name' => ['required', 'string', 'max:255'],
            'unit_type' => ['nullable', 'string', 'max:255'],
            'capacity' => ['required', 'integer', 'min:0'],
            'price_weekday' => ['required', 'integer', 'min:0'],
            'price_semi_weekend' => ['required', 'integer', 'min:0'],
            'price_weekend' => ['required', 'integer', 'min:0'],
            'status' => ['required', 'string', Rule::in(['active', 'inactive'])],
        ];
    }
}
