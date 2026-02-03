<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

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
}
