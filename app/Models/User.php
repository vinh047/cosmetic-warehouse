<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

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
}
