<?php

namespace App\Models;

use App\Traits\CommonScopes;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, CommonScopes;

    /**
     * Các field được phép fill (Mass Assignment)
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
    ];

    /**
     * Các field bị ẩn khi trả về JSON
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Ép kiểu dữ liệu
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
        'password' => 'hashed',
    ];

    /* =========================
       RELATIONSHIPS
    ========================= */

    /**
     * User có nhiều đơn hàng
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * User có nhiều giao dịch kho
     */
    public function inventoryTransactions()
    {
        return $this->hasMany(InventoryTransaction::class);
    }

    /**
     * User có nhiều log
     */
    // public function auditLogs()
    // {
    //     return $this->hasMany(AuditLog::class);
    // }

    /* =========================
       HELPER FUNCTIONS
    ========================= */

    /**
     * Check role
     */
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isManager()
    {
        return $this->role === 'manager';
    }

    public function isStaff()
    {
        return $this->role === 'staff';
    }

    /**
     * Check active
     */
    public function isActive()
    {
        return $this->is_active;
    }

    public function scopeSearch($query, $keyword)
    {
        return $query->when($keyword, function ($q) use ($keyword) {
            $q->where(function ($subQ) use ($keyword) {
                $subQ->where('name', 'like', "%{$keyword}%")
                    ->orWhere('email', 'like', "%{$keyword}%");
            });
        });
    }

    public function scopeRole($query, $role)
    {
        return $query->when($role, fn($q) => $q->where('role', $role));
    }

    public function scopeFilter($query, array $filters)
    {
        return $query
            ->search($filters['search'] ?? null)

            ->role($filters['role'] ?? null)

            ->filterActive($filters['active'] ?? null)
            ->filterTrashed($filters['trashed'] ?? null)
            ->sort(
                $filters['sort'] ?? null,
                $filters['order'] ?? 'desc',
                ['id', 'name', 'email', 'role', 'is_active', 'created_at']
            );
    }
}
