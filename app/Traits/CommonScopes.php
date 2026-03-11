<?php

namespace App\Traits;

trait CommonScopes
{
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
        return $isActive ? $query->active() : $query->inactive();
    }

    public function scopeFilterActive($query, $activeValue = null)
    {
        if (is_null($activeValue)) {
            return $query;
        }

        $isActive = filter_var($activeValue, FILTER_VALIDATE_BOOLEAN);

        return $this->scopeByStatus($query, $isActive);
    }

    public function scopeSort($query, $sortColumn = null, $sortOrder = 'desc', array $allowedColumns = [])
    {
        if (empty($sortColumn)) {
            return $query->latest();
        }

        if (empty($allowedColumns)) {
            // Mặc định các cột này bảng nào cũng có
            $allowedColumns = ['id', 'created_at', 'is_active'];
        }

        if (in_array($sortColumn, $allowedColumns)) {
            $direction = in_array(strtolower($sortOrder), ['asc', 'desc']) ? $sortOrder : 'desc';
            return $query->orderBy($sortColumn, $direction);
        }

        return $query->latest();
    }

    /**
     * Scope dùng chung để lọc dữ liệu xóa mềm (Soft Deletes)
     */
    public function scopeFilterTrashed($query, $trashed = null)
    {
        return $query->when($trashed, function ($q, $trashed) {
            if ($trashed === 'with') {
                $q->withTrashed();
            } elseif ($trashed === 'only') {
                $q->onlyTrashed();
            }
        });
    }
}
