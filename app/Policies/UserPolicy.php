<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Admins can always perform any action (super-policy bypass).
     */
    public function before(User $currentUser, string $ability): ?bool
    {
        if ($currentUser->isAdmin()) {
            return true;
        }

        return null; // Fall through to individual methods
    }

    /**
     * View a list of users — only admins (handled by before()).
     */
    public function viewAny(User $currentUser): bool
    {
        return false;
    }

    /**
     * View a specific user — admin (before()) or the user themselves.
     */
    public function view(User $currentUser, User $targetUser): bool
    {
        return $currentUser->id === $targetUser->id;
    }

    /**
     * Update a user — admin (before()) or the user updating their own profile.
     */
    public function update(User $currentUser, User $targetUser): bool
    {
        return $currentUser->id === $targetUser->id;
    }

    /**
     * Deactivate a user account — admin only (before()).
     */
    public function deactivate(User $currentUser, User $targetUser): bool
    {
        // Admin cannot deactivate themselves
        return $currentUser->id !== $targetUser->id;
    }

    /**
     * Reactivate a user account — admin only (before()).
     */
    public function activate(User $currentUser, User $targetUser): bool
    {
        return true;
    }

    /**
     * Change another user's role — admin only (before()).
     */
    public function changeRole(User $currentUser, User $targetUser): bool
    {
        return false;
    }
}
