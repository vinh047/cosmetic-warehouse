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
        if ($brandId) {
            $query->where('brand_id', $brandId);
        }
    }

    public function scopeCategory($query, $categoryId)
    {
        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }
    }

    // Name or sku
    public function scopeSearch($query, $keyword)
    {
        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%$keyword%")
                    ->orWhere('sku', 'like', "%$keyword%");
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
            ->brand($request->brand_id)
            ->category($request->category_id)
            ->search($request->search)
            ->sort($request, ['id', 'name', 'sku', 'price', 'is_active']);
    }
}
