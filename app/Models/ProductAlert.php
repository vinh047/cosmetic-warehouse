<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'stock_threshold',
        'expiry_threshold_days',
        'last_stock_alert_at',
        'last_expiry_alert_at'
    ];

    protected $casts = [
        'last_stock_alert_at' => 'datetime',
        'last_expiry_alert_at' => 'datetime',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}