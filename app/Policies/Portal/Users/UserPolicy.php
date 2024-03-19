<?php

namespace App\Policies\Portal\Users;

use App\Models\Portal\User\User;

class UserPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
    }

    public function viewAnyUsers(User $user) : bool
    {
        return $user->privilegio_id === 1 || $user->privilegio_id === 2;
    }
}
