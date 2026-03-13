<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Auth\Access\Response;

class WarehousePolicy
{
    public function before(User $user)
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Warehouse $warehouse): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isManager();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Warehouse $warehouse): bool
    {
        return $user->isManager();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Warehouse $warehouse): bool
    {
        return $user->isManager();
    }

    public function viewTrash(User $user): bool
    {
        return $user->isManager();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Warehouse $warehouse): bool
    {
        return $user->isManager();
    }
}
