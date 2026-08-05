<?php

namespace App\Services;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\Request;
use App\Mail\OrganizationWelcomeMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Role;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function register(array $data)
    {

        $user = null;
        $organization = null;
        DB::transaction(function () use ($data, &$user, &$organization) {
         
            $organization = Organization::create([
                'name' => $data['name'],
                'website' => $data['website'] ?? null,
                'slug'      => Str::slug($data['name']),
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
            ]);
        $ownerRole = Role::create([
            'organization_id' => $organization->id,
            'name'            => 'Owner',
            'code'            => 'owner',
            'description'     => 'Organization Owner',
            'is_system'       => true,
        ]);

        Role::create([
            'organization_id' => $organization->id,
            'name'            => 'Admin',
            'code'            => 'admin',
            'description'     => 'Administrator',
            'is_system'       => true,
        ]);

        Role::create([
            'organization_id' => $organization->id,
            'name'            => 'Manager',
            'code'            => 'manager',
            'description'     => 'Manager',
            'is_system'       => true,
        ]);

        Role::create([
            'organization_id' => $organization->id,
            'name'            => 'Employee',
            'code'            => 'employee',
            'description'     => 'Employee',
            'is_system'       => true,
        ]);

        // Create the owner user
        $user = User::create([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'] ?? null,
                'username' => $data['username'],
                'email' => $data['owner_email'],
                'password' => Hash::make($data['password']),
                'role_id' => $ownerRole->id,
                'organization_id' => $organization->id,
            ]);
        });

        Mail::to($organization->email)->send(new OrganizationWelcomeMail($organization, $user));

    }

    public function login(array $data)
    {
       
        $user = User::where('email', $data['email'])->first();
        if(!$user)
        {
            dd('User not found');
            throw ValidationException::withMessages([
                'email' => 'User not found.',
            ]);
        }

        if (!Hash::check($data['password'], $user->password)) {
            dd('Invalid password');
            throw ValidationException::withMessages([
                'password' => 'Invalid password.',
            ]);
        }
        if ($user->status !=true) {
            dd('User account is inactive');
            throw ValidationException::withMessages([
                'email' => 'Your account is inactive.',
            ]);
        }
        if ($user->organization->status != true) {
            dd('Organization is inactive');
            throw ValidationException::withMessages([
                'email' => 'Your organization is inactive.',
            ]);
        }
        session()->regenerate();
        session()->put([
            'organization_id' => $user->organization_id,
            'user_id' => $user->id,
        ]);
          return true;

     
    }

    public function logout(Request $request)
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }
}