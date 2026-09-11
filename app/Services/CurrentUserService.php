<?php

namespace App\Services;

use App\Models\User;

class CurrentUserService
{
    protected ?User $cachedUser = null;

    /**
     * Get Current Logged In User (Memoized per request)
     */
    public function getUser(): ?User
    {
        $userId = session('user_id');

        if (!$userId) {
            return null;
        }

        if ($this->cachedUser && $this->cachedUser->id === $userId) {
            return $this->cachedUser;
        }

        $this->cachedUser = User::with([
            'organization',
            'role'
        ])->find($userId);

        return $this->cachedUser;
    }

    public function getOrganization()
    {
        $user = $this->getUser();
        return $user ? $user->organization : null;
    }

    public function getRoles()
    {
        $user = $this->getUser();
        return $user?->organization
            ? $user->organization
                ->roles()
                ->where('name', '!=', 'owner')
                ->orderBy('name')
                ->get()
            : collect();
    }

    public function hasPermission(string $permission): bool
    {
        $user = $this->getUser();

        if (!$user?->role) {
            return false;
        }

        if ($user->role->code === 'owner') {
            return true;
        }

        return $user->role->permissions()->where('name', $permission)->exists();
    }
}