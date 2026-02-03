<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Warehouse extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'location', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function stocks()
    {
        return $this->hasMany(Stock::class);
    }

    public function inventoryTransactions()
    {
        return $this->hasMany(InventoryTransaction::class);
    }

    public function isActive()
    {
        return $this->is_active;
    }
}
