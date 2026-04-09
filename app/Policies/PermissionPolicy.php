<?php

namespace App\Policies;

use App\Models\User;

class PermissionPolicy
{
    public function create(User $user): bool
    {
        return $user->hasPermission('manage-platform-permissions');
    }

    public function assignToRole(User $user): bool
    {
        return $user->hasPermission('manage-platform-permissions');
    }
}
