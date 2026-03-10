<?php

namespace App\Models;

use App\Traits\CommonScopes;
use Illuminate\Database\Eloquent\Model;

class InventoryTransaction extends Model
{
    use CommonScopes;

    protected $fillable = ['user_id', 'product_batch_id', 'warehouse_id', 'type', 'quantity', 'reference_type', 'reference_id'];

    // Quan hệ với User thực hiện
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Quan hệ với lô hàng
    public function productBatch()
    {
        return $this->belongsTo(ProductBatch::class);
    }

    // Quan hệ với kho
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function reference()
    {
        return $this->morphTo();
    }

    public function scopeType($query, $type)
    {
        return $query->when($type, fn($q) => $q->where('type', $type));
    }

    public function scopeWarehouseId($query, $warehouseId)
    {
        return $query->when($warehouseId, fn($q) => $q->where('warehouse_id', $warehouseId));
    }

    public function scopeProductBatchId($query, $productBatchId)
    {
        return $query->when($productBatchId, fn($q) => $q->where('product_batch_id', $productBatchId));
    }

    public function scopeReferenceType($query, $referenceType)
    {
        return $query->when($referenceType, fn($q) => $q->where('reference_type', $referenceType));
    }

    public function scopeFromDate($query, $date)
    {
        return $query->when($date, fn($q) => $q->whereDate('created_at', '>=', $date));
    }

    public function scopeToDate($query, $date)
    {
        return $query->when($date, fn($q) => $q->whereDate('created_at', '<=', $date));
    }

    public function scopeUserId($query, $userId)
    {
        return $query->when($userId, fn($q) => $q->where('user_id', $userId));
    }

    // Trong Model InventoryTransaction

    public function scopeFilter($query, array $filters)
    {
        return $query
            ->type($filters['type'] ?? null)
            ->warehouseId($filters['warehouse_id'] ?? null)
            ->productBatchId($filters['product_batch_id'] ?? null)
            ->referenceType($filters['reference_type'] ?? null)
            ->userId($filters['user_id'] ?? null)
            ->fromDate($filters['from_date'] ?? null)
            ->toDate($filters['to_date'] ?? null)
            ->sort(
                $filters['sort'] ?? null,
                $filters['order'] ?? 'desc',
                ['id', 'type', 'quantity', 'created_at']
            );
    }
}
