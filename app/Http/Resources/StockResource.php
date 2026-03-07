<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'quantity' => (int) $this->quantity,

            'warehouse' => [
                'id' => $this->warehouse_id,
                'name' => $this->warehouse?->name,
                // Chỉ hiện địa chỉ khi xem chi tiết (Show)
                'location' => $this->when($request->routeIs('*.show'), $this->warehouse?->location),
            ],

            // Thông tin sản phẩm và lô hàng (Quan trọng nhất)
            'product' => [
                'id' => $this->productBatch?->product?->id,
                'name' => $this->productBatch?->product?->name,
                'sku' => $this->productBatch?->product?->sku,
                'category' => $this->productBatch?->product?->category?->name,
            ],

            'product_batch' => [
                'id' => $this->product_batch_id,
                'code' => $this->productBatch?->batch_code,
                'expiry_date' => $this->productBatch?->expiry_date?->format('d-m-Y H:i'),
                // Trạng thái lô hàng: Cảnh báo nếu sắp hết hạn
                'is_expired' => $this->productBatch?->expiry_date?->isPast(),
                'days_until_expiry' => now()->diffInDays($this->productBatch?->expiry_date, false),
            ],
            
            'created_at' => $this->created_at?->format('d-m-Y H:i'),

            // Thời gian cập nhật cuối cùng (Lần nhập/xuất gần nhất)
            'updated_at' => $this->updated_at?->format('d-m-Y H:i'),
            
        ];
    }
}
