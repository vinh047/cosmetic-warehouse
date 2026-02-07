<?php

namespace App\Models;

use App\Traits\CommonScopes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Warehouse extends Model
{
    use SoftDeletes, CommonScopes;

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

    // name or location
    public function scopeSearch($query, $keyword)
    {
        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                return $q->where('name', 'like', "%{$keyword}%")
                    ->orWhere('location', 'like', "%{$keyword}%");
            });
        }
        return $query;
    }

    public function scopeFilter($query, $request)
    {
        return $query
            ->when($request->has('active'), function ($q) use ($request) {
                $q->byStatus($request->boolean('active'));
            })
            ->sort($request, ['name', 'location', 'is_active'])
            ->search($request->search);
    }
}
