<?php

namespace App\Models;

use App\Traits\CommonScopes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Brand extends Model
{
    use SoftDeletes, CommonScopes;

    protected $fillable = ['name', 'country', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function isActive()
    {
        return $this->is_active;
    }

    // public function scopeHasProducts($query)
    // {
    //     return $query->has('products');
    // }

    public function scopeSearch($query, $keyword)
    {
        return $query->when($keyword, function ($q) use ($keyword) {
            $q->where(function ($subQ) use ($keyword) {
                $subQ->where('name', 'like', "%{$keyword}%")
                    ->orWhere('country', 'like', "%{$keyword}%");
            });
        });
    }

    public function scopeFilter($query, array $filters)
    {
        return $query
            ->filterActive($filters['active'] ?? null)

            ->search($filters['search'] ?? null)

            ->filterTrashed($filters['trashed'] ?? null)

            ->sort(
                $filters['sort'] ?? null,
                $filters['order'] ?? 'desc',
                ['id', 'name', 'country', 'created_at', 'is_active']
            );
    }
}
