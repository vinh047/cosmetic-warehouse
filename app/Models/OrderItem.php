<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = ['order_id', 'product_batch_id', 'quantity', 'price'];

    public $timestamps = false;

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // Liên kết tới lô hàng để lấy thông tin sản phẩm, ngày sản xuất
    public function productBatch()
    {
        return $this->belongsTo(ProductBatch::class);
    }
}
