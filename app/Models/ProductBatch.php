<?php

namespace App\Models;

use App\Traits\CommonScopes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductBatch extends Model
{
    use SoftDeletes, CommonScopes;

    protected $fillable = ['product_id', 'batch_code', 'manufacture_date', 'expiry_date', 'is_active'];

    protected $casts = ['manufacture_date' => 'date', 'expiry_date' => 'date', 'is_active' => 'boolean'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function stocks()
    {
        return $this->hasMany(Stock::class);
    }

    public function inventoryTransactions()
    {
        return $this->hasMany(InventoryTransaction::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function isActive()
    {
        return $this->is_active;
    }

    public function isExpired()
    {
        return $this->expiry_date->isPast();
    }

    public function scopeProduct($query, $productId)
    {
        return $query->when($productId, fn($q) => $q->where('product_id', $productId));
    }

    public function scopeExpiryFrom($query, $expiryFrom)
    {
        return $query->when($expiryFrom, fn($q) => $q->whereDate('expiry_date', '>=', $expiryFrom));
    }

    public function scopeExpiryTo($query, $expiryTo)
    {
        return $query->when($expiryTo, fn($q) => $q->whereDate('expiry_date', '<=', $expiryTo));
    }

    public function scopeExpired($query, $isExpired)
    {
        return $query->when(!is_null($isExpired), function ($q) use ($isExpired) {
            $expiredBool = filter_var($isExpired, FILTER_VALIDATE_BOOLEAN);
            return $q->whereDate('expiry_date', $expiredBool ? '<' : '>=', now());
        });
    }

    public function scopeStock($query, $hasStock)
    {
        return $query->when(!is_null($hasStock), function ($q) use ($hasStock) {
            $hasStockBool = filter_var($hasStock, FILTER_VALIDATE_BOOLEAN);

            return $q->whereHas('stocks', function ($q2) use ($hasStockBool) {
                return $hasStockBool
                    ? $q2->where('quantity', '>', 0)
                    : $q2->where('quantity', '<=', 0);
            });
        });
    }

    public function scopeSearch($query, $keyword)
    {
        return $query->when($keyword, fn($q) => $q->where('batch_code', 'like', "%{$keyword}%"));
    }

    public function scopeFilter($query, array $filters)
    {
        return $query
            ->filterActive($filters['active'] ?? null)
            ->search($filters['search'] ?? null)
            ->product($filters['product_id'] ?? null)
            ->expiryFrom($filters['expiry_from'] ?? null)
            ->expiryTo($filters['expiry_to'] ?? null)
            ->expired($filters['is_expired'] ?? null)
            ->stock($filters['has_stock'] ?? null)
            ->sort(
                $filters['sort'] ?? null,
                $filters['order'] ?? 'desc',
                ['id', 'batch_code', 'manufacture_date', 'expiry_date', 'is_active']
            );
    }
}
