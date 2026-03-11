<?php

namespace App\Http\Requests\ProductBatch;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductBatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => [
                'required',
                'integer',
                Rule::exists('products', 'id'),
            ],
            'batch_code' => [
                'required',
                'string',
                'max:100',
                // Unique theo product_id hiện tại đang gửi lên
                Rule::unique('product_batches', 'batch_code')
                    ->where(function ($query) {
                        return $query->where('product_id', $this->product_id);
                    }),
                'regex:/^[A-Z0-9-]+$/',
            ],
            'manufacture_date' => [
                'required',
                'date',
                'before_or_equal:today',
            ],
            'expiry_date' => [
                'required',
                'date',
                'after:manufacture_date',
            ],
            'is_active' => [
                'sometimes',
                'boolean',
            ],
        ];
    }
}
