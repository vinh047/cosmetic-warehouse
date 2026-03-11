<?php

namespace App\Http\Requests\Brand;

use Illuminate\Foundation\Http\FormRequest;

class StoreBrandRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'      => 'required|string|max:255|unique:brands,name',
            'country'   => 'nullable|string|max:100',
            'is_active' => 'sometimes|boolean',
        ];
    }
}