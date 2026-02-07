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

    public function scopeNameSearch($query, $keyword)
    {
        return $query->where('name', 'like', "%{$keyword}%");
    }

    public function scopeFilter($query, $request)
    {
        return $query
            ->when($request->has('active'), function ($q) use ($request) {
                $q->byStatus($request->boolean('active'));
            })
            ->nameSearch($request->search)
            ->sort($request, ['id', 'name', 'created_at', 'is_active']);
    }
}
