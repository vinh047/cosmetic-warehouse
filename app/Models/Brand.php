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
        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                    ->orWhere('country', 'like', "%{$keyword}%");
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
            ->search($request->search)
            ->sort($request, ['id', 'name', 'country', 'created_at', 'is_active']);
    }
}
