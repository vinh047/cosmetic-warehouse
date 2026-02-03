<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductBatch extends Model
{
    use SoftDeletes;

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
}
