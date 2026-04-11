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

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('villas', 'slug')->ignore($villaId)],
            'location' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'capacity' => ['required', 'integer', 'min:0'],
            'is_resort' => ['nullable', 'boolean'],
            'status' => ['required', 'string', Rule::in(['draft', 'active', 'inactive'])],
            'rules' => ['nullable', 'string'],
            'pros' => ['nullable', 'string'],
            'cons' => ['nullable', 'string'],
            'youtube_url' => ['nullable', 'url', 'max:255'],
        ];
    }
}
