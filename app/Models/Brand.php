<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Brand extends Model
{
    use SoftDeletes;

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

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    // public function scopeHasProducts($query)
    // {
    //     return $query->has('products');
    // }

    public function scopeSearch($query, $keyword)
    {
        return $query->where(function ($q) use ($keyword) {
            $q->where('name', 'like', "%{$keyword}%")
                ->orWhere('country', 'like', "%{$keyword}%");
        });
    }
}
