<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'       => $this->id,
            'quantity' => $this->quantity,
            'price'    => (float) $this->price,

            // Tự động tính thành tiền của dòng này
            'subtotal' => (float) ($this->quantity * $this->price),

            // Lồng thông tin Lô hàng & Sản phẩm (Chỉ hiển thị khi dùng with('productBatch.product'))
            'product_batch' => $this->whenLoaded('productBatch', function () {
                return [
                    'id'          => $this->productBatch->id,
                    'batch_code'  => $this->productBatch->batch_code,
                    'expiry_date' => $this->productBatch->expiry_date,
                    // Lấy luôn tên sản phẩm từ quan hệ lồng nhau
                    'product_name' => $this->productBatch->product->name ?? null,
                    'sku'          => $this->productBatch->product->sku ?? null,
                ];
            }),
        ];
    }
}
