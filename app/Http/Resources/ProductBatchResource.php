<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductBatchResource extends JsonResource
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

            'product' => $this->whenLoaded('product', function () {
                return [
                    'id' => $this->product->id,
                    'name' => $this->product->name,
                    'sku' => $this->product->sku,
                ];
            }),
            
            'batch_code' => $this->batch_code,
            'manufacture_date' => $this->manufacture_date->format('Y-m-d H:i:s'),
            'expiry_date' => $this->expiry_date->format('Y-m-d H:i:s'),
            'is_active' => (bool) $this->is_active,

            'current_stock' => $this->stocks_sum_quantity ?? 0,

            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
