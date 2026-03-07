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

    public function scopeLowStock($query, $threshold = null)
    {
        $limit = $threshold ?? 10;
        return $query->where('quantity', '<=', $limit);
    }

    public function scopeExpiringSoon($query, $days = null)
    {
        $limitDays = (int) ($days ?? 30);

        return $query->whereHas('productBatch', function ($q) use ($limitDays) {
            $q->where('expiry_date', '<=', now()->addDays($limitDays))
                ->where('expiry_date', '>=', now());
        });
    }

    public function scopeFilter($query, $request)
    {
        return $query
            ->warehouseId($request->warehouse_id)
            ->productId($request->product_id)
            ->productBatchId($request->product_batch_id)
            ->when($request->filled('low_stock'), function ($q) use ($request) {
                return $q->lowStock($request->get('threshold'));
            })
            ->when($request->filled('expiring_soon'), function ($q) use ($request) {
                return $q->expiringSoon($request->get('days'));
            })
            ->sort($request, ['id', 'quantity', 'updated_at']);
    }
}
