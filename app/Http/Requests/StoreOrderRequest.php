<?php

namespace App\Http\Requests;

use App\Enums\OrderChannel;
use App\Models\Stock;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // update*
        return true;
    }

    /**
     * Chuẩn bị dữ liệu TRƯỚC KHI chạy vào bộ rules kiểm tra
     */
    protected function prepareForValidation(): void
    {
        // Nếu là đơn offline và không có tên khách hàng -> Tự động gán là "Khách lẻ"
        if ($this->input('channel') === OrderChannel::OFFLINE->value && empty($this->input('customer_name'))) {
            $this->merge([
                'customer_name' => 'Khách lẻ'
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Thông tin chung đơn hàng
            'customer_name' => ['required', 'string', 'max:255'],
            'channel'       => ['required', Rule::enum(OrderChannel::class)],
            'warehouse_id'  => ['required_if:channel,' . OrderChannel::OFFLINE->value, 'nullable', 'exists:warehouses,id'],

            // Validate mảng items
            'items'                    => ['required', 'array', 'min:1'],
            'items.*.quantity'         => ['required', 'integer', 'min:1'],

            // Nếu Offline -> Bắt buộc gửi product_batch_id
            'items.*.product_batch_id' => [
                'required_if:channel,' . OrderChannel::OFFLINE->value,
                'nullable',
                'exists:product_batches,id'
            ],
            // Nếu Online -> Bắt buộc gửi product_id
            'items.*.product_id' => [
                'required_if:channel,' . OrderChannel::ONLINE->value,
                'nullable',
                'exists:products,id'
            ],
        ];
    }
}
