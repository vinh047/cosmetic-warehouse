<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryTransaction extends Model
{
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
}
