<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    public function before(User $user)
    {
        return $user->isAdmin();
    }

    public function update(User $user, User $model)
    {
        return $user->id === $model->id;
    }
}
