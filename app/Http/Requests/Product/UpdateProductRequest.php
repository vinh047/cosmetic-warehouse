<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Lấy model product đang được sửa từ Route
        $product = $this->route('product');

        return [
            'name' => [
                'sometimes',
                'string',
                'max:255'
            ],
            'sku' => [
                'sometimes',
                'string',
                'max:100',
                Rule::unique('products', 'sku')->ignore($product->id),
                'regex:/^[A-Z0-9-]+$/'
            ],
            'category_id' => [
                'sometimes',
                'integer',
                Rule::exists('categories', 'id'),
            ],
            'brand_id' => [
                'sometimes',
                'integer',
                Rule::exists('brands', 'id'),
            ],
            'price' => [
                'sometimes',
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