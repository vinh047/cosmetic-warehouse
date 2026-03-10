<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'order_code'    => $this->order_code,
            'customer_name' => $this->customer_name,

            // Chuyển đổi Enum thành format dễ dùng cho Frontend
            'channel'       => $this->channel->value ?? $this->channel,
            'status'        => $this->status->value ?? $this->status,

            'total_price' => (float) $this->total_price,
            'created_at'  => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null,

            // Hiển thị người tạo đơn (nếu có load)
            'creator' => $this->whenLoaded('creator', function () {
                return $this->creator ? [
                    'id'   => $this->creator->id,
                    'name' => $this->creator->name,
                    'role' => $this->creator->role,
                ] : null;
            }),

            // Gọi OrderItemResource để xử lý mảng items
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
