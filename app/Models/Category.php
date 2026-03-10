<?php

namespace App\Models;

use App\Traits\CommonScopes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use SoftDeletes, CommonScopes;

    protected $fillable = ['name', 'description', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function isActive()
    {
        return $this->is_active;
    }

    public function scopeSearch($query, $keyword)
    {
        return $query->when($keyword, fn($q) => $q->where('name', 'like', "%{$keyword}%"));
    }

    public function scopeFilter($query, array $filters)
    {
        return $query
            ->filterActive($filters['active'] ?? null)

            ->search($filters['search'] ?? null)

            ->sort(
                $filters['sort'] ?? null,
                $filters['order'] ?? 'desc',
                ['id', 'name', 'created_at', 'is_active']
            );
    }
}
