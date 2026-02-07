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

    public function scopeSort($query, $request, array $allowedColumns = [])
    {
        $sortColumn = $request->get('sort');
        $sortOrder = $request->get('order', 'desc');

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
}