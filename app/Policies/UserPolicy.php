<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function createStaff(User $user): bool
    {
        return $user->isAdmin() || $user->hasPermission('staff.create');
    }

    public function manageContent(User $user): bool
    {
        return $user->isAdmin() || $user->hasPermission('content.manage');
    }
}
