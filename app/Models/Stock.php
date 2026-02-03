<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
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

}
