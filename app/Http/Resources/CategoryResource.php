<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
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
            'description' => $this->description,
            'is_active' => (bool) $this->is_active,

            'created_at' => $this->created_at->format('d-m-Y H:i'),

            'products_count' => $this->whenCounted('products'),

            // update
            // 'products' => ProductResource::collection($this->whenLoaded('products')),
            'products' => $this->whenLoaded('products'),
        ];
    }
}
