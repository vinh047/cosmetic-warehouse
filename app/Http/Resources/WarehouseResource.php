<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WarehouseResource extends JsonResource
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
            'name' => $this->name,
            'location' => $this->location,
            'is_active' => (bool) $this->is_active,


            'total_batches' => $this->stocks_count ?? 0,

            // Laravel tự động đặt tên là {relation}_{function}_{column}
            'total_quantity' => (int) $this->stocks_sum_quantity,

            'stocks' => $this->whenLoaded('stocks'),

            'created_at' => $this->created_at?->format('d-m-Y H:i'),
            'updated_at' => $this->updated_at?->format('d-m-Y H:i'),
        ];
    }
}
