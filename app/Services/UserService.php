<?php

namespace App\Services;

use App\Models\User;
use App\Models\Invitation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class UserService
{
    public function addUser(array $data, $organizationId, $invitedById)
    {
        return DB::transaction(function () use ($data,$organizationId, $invitedById) {
     
            $user = User::create([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'role_id' => $data['role_id'],
                'username' => $data['username'],
                'organization_id' => $organizationId,
                'status' => 0,
            
            ]);

           return $invitaiton =  Invitation::create([
                'user_id' => $user->id,
                'organization_id' => $organizationId,
                'role_id' => $data['role_id'],
                'invited_by' => $invitedById,   
                'email' => $data['email'],
                'token' => bin2hex(random_bytes(32)), // Generate a random token
                'expires_at' => now()->addDays(7), // Set expiration date (e.g., 7 days from now)
                'status' => 'PENDING',
            
                
            ]);

         });
    }
    
}