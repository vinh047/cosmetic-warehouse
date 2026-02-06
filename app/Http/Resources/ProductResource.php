<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
            'sku' => $this->sku, // SKU là định danh quan trọng nhất, nên để lên đầu
            'name' => $this->name,

            // 1. Giá trị thô (Raw) để JS tính toán (cộng trừ nhân chia)
            'price' => (float) $this->price,

            // 2. Giá trị Format sẵn để hiển thị (View) -> Frontend đỡ khổ
            'price_formatted' => number_format($this->price, 0, ',', '.') . ' đ',

            'description' => $this->description,
            'is_active' => $this->is_active,

            // 3. Quan hệ (Relationships)
            // Sử dụng whenLoaded để tránh lỗi N+1 Query.
            // Data chỉ xuất hiện khi Controller có dùng ->with('brand')
            'brand' => $this->whenLoaded('brand'),
            'category' => $this->whenLoaded('category'),

            // Field này dành cho tương lai khi bạn làm bảng Stocks
            // 'total_stock' => $this->whenLoaded('stocks', function () {
            //     return $this->stocks->sum('quantity');
            // }),

            'created_at' => $this->created_at->format('d-m-Y H:i'),
            'updated_at' => $this->updated_at->format('d-m-Y H:i'),
        ];
    }
}
