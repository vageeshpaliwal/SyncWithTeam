<?php

namespace App\Services;

use App\Models\User;

class CurrentUserService
{
    /**
     * Get Current Logged In User
     */
    public function getUser()
    {
        return User::with([
            'organization',
            'role'
        ])->find(session('user_id'));
    }
    public function getOrganization()
    {
        $user = $this->getUser();
        return $user ? $user->organization : null;
    }
    public function getRoles()
    {
        return $this->getUser()
            ->organization
            ->roles()
            ->where('name', '!=', 'owner')
            ->orderBy('name')
            ->get();
    }
}