<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    protected $fillable = ['order_code', 'user_id', 'channel', 'customer_name', 'total_price', 'status'];

    protected $casts = [
        'total_price' => 'decimal:2',
    ];

    // Quan hệ 1 đơn hàng có nhiều sản phẩm chi tiết
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    // Người tạo đơn
    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
