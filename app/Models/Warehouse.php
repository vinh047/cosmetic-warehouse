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
        return $query->when($keyword, function ($q) use ($keyword) {
            $q->where(function ($subQ) use ($keyword) {
                $subQ->where('name', 'like', "%{$keyword}%")
                    ->orWhere('location', 'like', "%{$keyword}%");
            });
        });
    }

    public function scopeFilter($query, array $filters)
    {
        return $query
            ->filterActive($filters['active'] ?? null)
            ->search($filters['search'] ?? null)
            ->sort(
                $filters['sort'] ?? null,
                $filters['order'] ?? 'desc',
                ['id', 'name', 'location', 'created_at', 'is_active']
            );
    }
}
