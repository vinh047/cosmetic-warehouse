<?php

namespace App\Models;

use App\Traits\CommonScopes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes, CommonScopes;

    protected $fillable = ['name', 'sku', 'category_id', 'brand_id', 'price', 'description', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function batches()
    {
        return $this->hasMany(ProductBatch::class);
    }

    public function isActive()
    {
        return $this->is_active;
    }

    public function scopeBrand($query, $brandId)
    {
        return $query->when($brandId, fn($q) => $q->where('brand_id', $brandId));
    }

    public function scopeCategory($query, $categoryId)
    {
        return $query->when($categoryId, fn($q) => $q->where('category_id', $categoryId));
    }

    // Name or sku
    public function scopeSearch($query, $keyword)
    {
        return $query->when($keyword, function ($q) use ($keyword) {
            $q->where(function ($subQ) use ($keyword) {
                $subQ->where('name', 'like', "%{$keyword}%")
                    ->orWhere('sku', 'like', "%{$keyword}%");
            });
        });
    }

    public function scopeFilter($query, array $filters)
    {
        return $query
            ->filterActive($filters['active'] ?? null)
            ->brand($filters['brand_id'] ?? null)
            ->category($filters['category_id'] ?? null)
            ->search($filters['search'] ?? null)
            ->filterTrashed($filters['trashed'] ?? null)
            ->sort(
                $filters['sort'] ?? null,
                $filters['order'] ?? 'desc',
                ['id', 'name', 'sku', 'price', 'is_active']
            );
    }

    public function alert()
    {
        return $this->hasOne(ProductAlert::class);
    }
}
