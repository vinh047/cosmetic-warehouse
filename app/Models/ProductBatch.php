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
        if ($productId) {
            $query->where('product_id', $productId);
        }
        return $query;
    }

    public function scopeExpiryFrom($query, $expiryFrom)
    {
        if ($expiryFrom) {
            $query->whereDate('expiry_date', '>=', $expiryFrom);
        }
        return $query;
    }

    public function scopeExpiryTo($query, $expiryTo)
    {
        if ($expiryTo) {
            $query->whereDate('expiry_date', '<=', $expiryTo);
        }
        return $query;
    }

    public function scopeExpired($query, $isExpired)
    {
        if (!is_null($isExpired)) {
            return $query->where('expiry_date', $isExpired ? '<' : '>=', now());
        }
        return $query;
    }

    public function scopeStock($query, $hasStock)
    {
        if (!is_null($hasStock)) {
            $query->whereHas('stocks', function ($q) use ($hasStock) {
                return $hasStock
                    ? $q->where('quantity', '>', 0)
                    : $q->where('quantity', '<=', 0);
            });
        }
        return $query;
    }

    public function scopeSearch($query, $keyword)
    {
        if ($keyword) {
            $query->where('batch_code', 'like', "%{$keyword}%");
        }
        return $query;
    }

    public function scopeFilter($query, $request)
    {
        return $query
            ->when($request->has('active'), function ($q) use ($request) {
                $q->byStatus($request->boolean('active'));
            })
            ->sort($request, ['batch_code', 'manufacture_date', 'expiry_date', 'is_active'])
            ->search($request->search)
            ->product($request->product_id)
            ->expiryFrom($request->expiry_from)
            ->expiryTo($request->expiry_to)
            ->when($request->has('is_expired'), function ($q) use ($request) {
                $q->expired($request->boolean('is_expired'));
            })
            ->when($request->has('has_stock'), function ($q) use ($request) {
                $q->stock($request->boolean('has_stock'));
            });
    }
}
