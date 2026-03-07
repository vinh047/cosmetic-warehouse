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
        if ($type)
            return $query->where('type', $type);
    }

    public function scopeWarehouseId($query, $warehouseId)
    {
        if ($warehouseId)
            return $query->where('warehouse_id', $warehouseId);
    }

    public function scopeProductBatchId($query, $productBatchId)
    {
        if ($productBatchId)
            return $query->where('product_batch_id', $productBatchId);
    }

    public function scopeReferenceType($query, $referenceType)
    {
        if ($referenceType)
            return $query->where('reference_type', $referenceType);
    }

    public function scopeFromDate($query, $date)
    {
        if ($date) {
            return $query->whereDate('created_at', '>=', $date);
        }
    }

    public function scopeToDate($query, $date)
    {
        if ($date) {
            return $query->whereDate('created_at', '<=', $date);
        }
    }

    public function scopeUserId($query, $userId)
    {
        if ($userId)
            return $query->where('user_id', $userId);
    }

    // Trong Model InventoryTransaction

    public function scopeFilter($query, $request)
    {
        return $query
            ->type($request->type)
            ->warehouseId($request->warehouse_id)
            ->productBatchId($request->product_batch_id)
            ->referenceType($request->reference_type)
            ->userId($request->user_id)
            ->fromDate($request->from_date)
            ->toDate($request->to_date)
            ->sort($request, ['id', 'type', 'quantity', 'created_at']);
    }
}
