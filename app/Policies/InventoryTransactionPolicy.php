<?php

namespace App\Policies;

use App\Models\InventoryTransaction;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class InventoryTransactionPolicy
{
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
    public function view(User $user, InventoryTransaction $inventoryTransaction): bool
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
}
