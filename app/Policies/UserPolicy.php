<?php

namespace App\Policies;

use App\Models\User;

/**
 * UserPolicy: Authorization rules for user-related actions
 * Used in controllers with $this->authorize('ability', User::class)
 * Throws 403 Forbidden if policy method returns false
 */
class UserPolicy
{
    /**
     * Can user create staff accounts?
     * Allowed if: user is admin OR has explicit 'staff.create' permission
     *
     * @param User $user The authenticated user requesting the action
     * @return bool
     */
    public function createStaff(User $user): bool
    {
        return $user->isAdmin() || $user->hasPermission('staff.create');
    }

    /**
     * Can user manage content (announcements, services, etc.)?
     * Allowed if: user is admin OR has explicit 'content.manage' permission
     *
     * @param User $user
     * @return bool
     */
    public function manageContent(User $user): bool
    {
        return $user->isAdmin() || $user->hasPermission('content.manage');
    }
}
