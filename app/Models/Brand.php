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

    public function scopeByStatus($query, bool $isActive)
    {
        // Nếu true gọi active(), nếu false gọi inactive()
        return $isActive ? $query->active() : $query->inactive();
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

    public function scopeSort($query, $request, array $allowedColumns = [])
    {
        // 1. Lấy tham số, gán mặc định
        $sortColumn = $request->get('sort');
        $sortOrder = $request->get('order', 'desc');

        // 2. Nếu không truyền sort -> Mặc định dùng latest()
        if (empty($sortColumn)) {
            return $query->latest();
        }

        // 3. Nếu danh sách allowedColumns rỗng -> Tự set mặc định
        if (empty($allowedColumns)) {
            $allowedColumns = ['id', 'name', 'created_at', 'is_active'];
        }

        // 4. Kiểm tra bảo mật & Apply sort
        if (in_array($sortColumn, $allowedColumns)) {
            // Đảm bảo sortOrder chỉ là asc hoặc desc
            $direction = in_array(strtolower($sortOrder), ['asc', 'desc']) ? $sortOrder : 'desc';
            return $query->orderBy($sortColumn, $direction);
        }

        // 5. Fallback: Nếu truyền cột linh tinh -> vẫn về latest() cho an toàn
        return $query->latest();
    }

    public function scopeFilter($query, $request)
    {
        return $query
            ->when($request->filled('active'), function ($q) use ($request) {
                $q->byStatus($request->boolean('active'));
            })
            ->search($request->search)
            ->sort($request, ['id', 'name', 'country', 'created_at', 'is_active']);
    }
}
