<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductBatchRequest extends FormRequest
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

        // Lấy ID batch khi update (route model binding)
        $batchId = $this->route('product_batch')
            ? $this->route('product_batch')->id
            : null;

        return [

            // Product ID
            'product_id' => [
                $batchId ? 'sometimes' : 'required',
                'integer',
                Rule::exists('products', 'id'),
            ],

            // Batch code
            'batch_code' => [
                $batchId ? 'sometimes' : 'required',
                'string',
                'max:100',

                // Unique theo product_id (không trùng batch trong cùng product)
                Rule::unique('product_batches', 'batch_code')
                    ->ignore($batchId)
                    ->where(function ($query) {
                        return $query->where('product_id', $this->product_id);
                    }),

                // Format: chữ hoa, số, gạch ngang (optional)
                'regex:/^[A-Z0-9-]+$/',
            ],

            // Ngày sản xuất
            'manufacture_date' => [
                $batchId ? 'sometimes' : 'required',
                'date',
                'before_or_equal:today',
            ],

            // Ngày hết hạn
            'expiry_date' => [
                $batchId ? 'sometimes' : 'required',
                'date',
                'after:manufacture_date',
            ],

            // Trạng thái
            'is_active' => [
                'sometimes',
                'boolean',
            ],
        ];
    }
}
