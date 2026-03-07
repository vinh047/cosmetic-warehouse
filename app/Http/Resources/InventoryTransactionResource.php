<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventoryTransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'quantity' => (int) $this->quantity,

            // Thông tin chi tiết lô hàng và sản phẩm
            'product_batch' => [
                'id' => $this->product_batch_id,
                'code' => $this->productBatch?->batch_code,
                'product_name' => $this->productBatch?->product?->name,
                // THÊM: Cho SHOW để biết hàng còn hạn hay không
                'expiry_date' => $this->when($request->routeIs('*.show'), function () {
                    return $this->productBatch?->expiry_date?->format('d/m/Y');
                }),
            ],

            // Thông tin kho hàng
            'warehouse' => [
                'id' => $this->warehouse_id,
                'name' => $this->warehouse?->name,
                // THÊM: Địa chỉ kho để nhân viên dễ tìm
                'location' => $this->when($request->routeIs('*.show'), $this->warehouse?->location),
            ],

            // Người thực hiện giao dịch
            'user' => [
                'id' => $this->user_id,
                'name' => $this->user?->name,
                // THÊM: Vai trò người thực hiện (Admin/Manager/Staff)
                'role' => $this->when($request->routeIs('*.show'), $this->user?->role),
            ],

            'reference' => [
                'type' => $this->reference_type,
                'id' => $this->reference_id,
                // update*
                // THÊM: Nếu là Order thì có thể trả về link/code đơn hàng nếu cần
            ],

            'created_at' => $this->created_at?->format('d-m-Y H:i'),
            'updated_at' => $this->when($request->routeIs('*.show'), $this->updated_at?->format('d/m/Y H:i')),
        ];
    }
}
