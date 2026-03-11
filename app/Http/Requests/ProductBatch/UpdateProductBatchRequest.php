<?php

namespace App\Http\Requests\ProductBatch;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductBatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $batch = $this->route('product_batch');

        $productId = $this->input('product_id', $batch->product_id);

        return [
            'product_id' => [
                'sometimes',
                'integer',
                Rule::exists('products', 'id'),
            ],
            'batch_code' => [
                'sometimes',
                'string',
                'max:100',
                // Bỏ qua ID hiện tại VÀ check theo product_id (kết hợp cả input lẫn DB)
                Rule::unique('product_batches', 'batch_code')
                    ->ignore($batch->id)
                    ->where(function ($query) use ($productId) {
                        return $query->where('product_id', $productId);
                    }),
                'regex:/^[A-Z0-9-]+$/',
            ],
            'manufacture_date' => [
                'sometimes',
                'date',
                'before_or_equal:today',
            ],
            'expiry_date' => [
                'sometimes',
                'date',
                // Lưu ý: Nếu user gửi expiry_date mà không gửi manufacture_date, 
                // rule này có thể cần sửa lại tùy độ khắt khe, nhưng hiện tại Laravel vẫn cover được.
                'after:manufacture_date', 
            ],
            'is_active' => [
                'sometimes',
                'boolean',
            ],
        ];
    }
}