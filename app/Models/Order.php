<?php

namespace App\Models;

use App\Enums\OrderChannel;
use App\Enums\OrderStatus;
use App\Traits\CommonScopes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Order extends Model
{
    use CommonScopes;

    protected $fillable = [
        'order_code',
        'user_id',
        'customer_name',
        'total_price',
        'channel',
        'status',
    ];

    protected $casts = [
        'channel' => OrderChannel::class,
        'status' => OrderStatus::class,
        'total_price' => 'decimal:2',
    ];

    protected static function booted()
    {
        static::creating(function ($order) {
            // Logic: ORD + Năm Tháng Ngày + Chuỗi ngẫu nhiên 4 ký tự (VD: ORD260308ABCD)
            $order->order_code = 'ORD' . now()->format('ymd') . strtoupper(Str::random(4));

            // Đảm bảo không trùng
            while (self::where('order_code', $order->order_code)->exists()) {
                $order->order_code = 'ORD' . now()->format('ymd') . strtoupper(Str::random(4));
            }
        });
    }

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

    public function inventoryTransactions()
    {
        return $this->morphMany(InventoryTransaction::class, 'reference');
    }

    public function scopeOrderCode($query, $orderCode)
    {
        return $query->when($orderCode, fn($q) => $q->where('order_code', 'like', "%{$orderCode}%"));
    }

    public function scopeChannel($query, $channel)
    {
        return $query->when($channel, fn($q) => $q->where('channel', $channel));
    }

    public function scopeCustomerName($query, $customerName)
    {
        return $query->when($customerName, fn($q) => $q->where('customer_name', 'like',  "%{$customerName}%"));
    }

    public function scopeStatus($query, $status)
    {
        return $query->when($status, fn($q) => $q->where('status', $status));
    }

    public function scopeDateFilter($query, $startDate = null, $endDate = null)
    {
        return $query
            ->when($startDate, fn($q) => $q->where('created_at', '>=', $startDate . ' 00:00:00'))

            ->when($endDate, fn($q) => $q->where('created_at', '<=', $endDate . ' 23:59:59'));
    }

    public function scopeFilter($query, array $filters)
    {
        return $query
            ->orderCode($filters['order_code'] ?? null)
            ->customerName($filters['customer_name'] ?? null)
            ->channel($filters['channel'] ?? null)
            ->status($filters['status'] ?? null)
            ->dateFilter($filters['start_date'] ?? null, $filters['end_date'] ?? null)
            ->sort(
                $filters['sort'] ?? null,
                $filters['order'] ?? 'desc',
                ['id', 'order_code', 'total_price', 'created_at']
            );;
    }
}
