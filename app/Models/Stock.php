<?php

namespace App\Models;

use App\Traits\CommonScopes;
use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    use CommonScopes;

    protected $fillable = ['warehouse_id', 'product_batch_id', 'quantity'];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function productBatch()
    {
        return $this->belongsTo(ProductBatch::class);
    }

    // Trong Stock.php
    public function scopeInStock($query)
    {
        return $query->where('quantity', '>', 0);
    }

    public function scopeWarehouseId($query, $warehouseId)
    {
        return $query->when($warehouseId, fn($q) => $q->where('warehouse_id', $warehouseId));
    }

    public function scopeProductId($query, $productId)
    {
        return $query->when($productId, function ($q) use ($productId) {
            return $q->whereHas('productBatch', fn($b) => $b->where('product_id', $productId));
        });
    }

    public function scopeProductBatchId($query, $productBatchId)
    {
        return $query->when($productBatchId, fn($q) => $q->where('product_batch_id', $productBatchId));
    }

    public function scopeLowStock($query, $isLowStock = null, $threshold = null)
    {
        return $query->when(!is_null($isLowStock), function ($q) use ($isLowStock, $threshold) {
            if (filter_var($isLowStock, FILTER_VALIDATE_BOOLEAN)) {
                $limit = $threshold ?? 10;
                return $q->where('quantity', '<=', $limit);
            }
        });
    }

    public function scopeExpiringSoon($query, $isExpiringSoon = null, $days = null)
    {
        return $query->when(!is_null($isExpiringSoon), function ($q) use ($isExpiringSoon, $days) {
            if (filter_var($isExpiringSoon, FILTER_VALIDATE_BOOLEAN)) {
                $limitDays = (int) ($days ?? 30);
                return $q->whereHas('productBatch', function ($subQ) use ($limitDays) {
                    $subQ->where('expiry_date', '<=', now()->addDays($limitDays))
                        ->where('expiry_date', '>=', now());
                });
            }
        });
    }

    public function scopeFilter($query, array $filters)
    {
        return $query
            ->warehouseId($filters['warehouse_id'] ?? null)
            ->productId($filters['product_id'] ?? null)
            ->productBatchId($filters['product_batch_id'] ?? null)
            ->lowStock($filters['low_stock'] ?? null, $filters['threshold'] ?? null)
            ->expiringSoon($filters['expiring_soon'] ?? null, $filters['days'] ?? null)
            ->sort(
                $filters['sort'] ?? null,
                $filters['order'] ?? 'desc',
                ['id', 'quantity', 'updated_at']
            );
    }
}
