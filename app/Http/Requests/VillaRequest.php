<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VillaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $villaId = $this->route('villa')?->id;

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('villas', 'slug')->ignore($villaId)],
            'location' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'facilities' => ['nullable', 'array'],
            'facilities.*' => ['nullable', 'string', 'max:255'],
            'additional_facilities' => ['nullable', 'array'],
            'additional_facilities.*' => ['nullable', 'string', 'max:255'],
            'is_resort' => ['nullable', 'boolean'],
            'status' => ['required', 'string', Rule::in(['draft', 'active', 'inactive'])],
            'rules' => ['nullable', 'string'],
            'pros' => ['nullable', 'string'],
            'cons' => ['nullable', 'string'],
            'youtube_url' => ['nullable', 'url', 'max:255'],
            'brand_ids' => ['nullable', 'array'],
            'brand_ids.*' => ['integer', Rule::exists('brands', 'id')],
        ];

        // Villa biasa (non-resort): wajib isi data unit langsung
        if (! $this->boolean('is_resort')) {
            $rules['unit_capacity'] = ['required', 'integer', 'min:1'];
            $rules['price_weekday'] = ['required', 'integer', 'min:0'];
            $rules['price_semi_weekend'] = ['required', 'integer', 'min:0'];
            $rules['price_weekend'] = ['required', 'integer', 'min:0'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'unit_capacity.required' => 'Kapasitas wajib diisi untuk villa biasa.',
            'price_weekday.required' => 'Harga weekday wajib diisi untuk villa biasa.',
            'price_semi_weekend.required' => 'Harga semi weekend wajib diisi untuk villa biasa.',
            'price_weekend.required' => 'Harga weekend wajib diisi untuk villa biasa.',
        ];
    }
}
