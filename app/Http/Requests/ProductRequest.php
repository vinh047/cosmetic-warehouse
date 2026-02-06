<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // update
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $productId = $this->route('product') ? $this->route('product')->id : null;

        return [
            'name' => [
                $productId ? 'sometimes' : 'required',
                'string',
                'max:255'
            ],

            // SKU bắt buộc duy nhất
            'sku' => [
                $productId ? 'sometimes' : 'required',
                'string',
                'max:100',
                // Kiểm tra trùng SKU, bỏ qua chính nó nếu update
                Rule::unique('products', 'sku')->ignore($productId),
                // Quy tắc đặt tên SKU: Chữ in hoa, số, dấu gạch ngang (Regex)
                'regex:/^[A-Z0-9-]+$/'
            ],

            // Kiểm tra Category có tồn tại không
            'category_id' => [
                $productId ? 'sometimes' : 'required',
                'integer',
                // Rule exists: bảng categories, cột id
                Rule::exists('categories', 'id'),
            ],

            // Kiểm tra Brand có tồn tại không
            'brand_id' => [
                $productId ? 'sometimes' : 'required',
                'integer',
                Rule::exists('brands', 'id'),
            ],

            'price' => [
                $productId ? 'sometimes' : 'required',
                'numeric',
                'min:0'
            ],
            'description' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ];
    }

    public function messages()
    {
        return [
            'category_id.exists' => 'The selected category is invalid.',
            'brand_id.exists' => 'The selected brand is invalid.',
            'sku.regex' => 'The SKU may only contain uppercase letters, numbers, and hyphens.',
        ];
    }
}
