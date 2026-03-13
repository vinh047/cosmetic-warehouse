<?php

namespace App\Policies;

use App\Models\ProductBatch;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProductBatchPolicy
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
    public function view(User $user, ProductBatch $productBatch): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ProductBatch $productBatch): bool
    {
        return $user->isManager();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ProductBatch $productBatch): bool
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
    public function restore(User $user, ProductBatch $productBatch): bool
    {
        return $user->isManager();
    }
}
