<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255'
            ],
            'sku' => [
                'required',
                'string',
                'max:100',
                'unique:products,sku',
                'regex:/^[A-Z0-9-]+$/'
            ],
            'category_id' => [
                'required',
                'integer',
                Rule::exists('categories', 'id'),
            ],
            'brand_id' => [
                'required',
                'integer',
                Rule::exists('brands', 'id'),
            ],
            'price' => [
                'required',
                'numeric',
                'min:0'
            ],
            'description' => 'nullable|string',
            'is_active'   => 'sometimes|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.exists' => 'The selected category is invalid.',
            'brand_id.exists'    => 'The selected brand is invalid.',
            'sku.regex'          => 'The SKU may only contain uppercase letters, numbers, and hyphens.',
        ];
    }
}