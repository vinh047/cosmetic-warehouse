<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'name'              => $this->name,
            'email'             => $this->email,
            'role'              => $this->role,
            'is_active'         => (bool) $this->is_active,

            // Trả về số lượng đơn hàng hoặc giao dịch nếu có load quan hệ
            'orders_count'      => $this->whenCounted('orders'),
            'transactions_count' => $this->whenCounted('inventoryTransactions'),

            'email_verified'    => $this->email_verified_at ? true : false,

            // Thông tin thời gian
            'created_at'        => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at'        => $this->updated_at->format('Y-m-d H:i:s'),

            'deleted_at'        => $this->deleted_at ? $this->deleted_at->format('Y-m-d H:i:s') : null,
        ];
    }
}
