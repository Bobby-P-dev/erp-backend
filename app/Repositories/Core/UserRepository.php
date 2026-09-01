<?php

namespace App\Repositories\Core;

use App\Models\User;

class UserRepository
{
    public function find($id)
    {
        return User::findOrFail($id);
    }

    public function syncRoles(User $user, array $roles)
    {
        return $user->syncRoles($roles);
    }
}
