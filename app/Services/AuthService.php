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
    public function __construct(
        protected PermissionService $permissionService,
    ) {}

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
            'is_default'       => true,
        ]);

        $adminRole = Role::create([
            'organization_id' => $organization->id,
            'name'            => 'Admin',
            'code'            => 'admin',
            'description'     => 'Administrator',
            'is_default'       => true,
        ]);

        $hrRole = Role::create([
            'organization_id' => $organization->id,
            'name'            => 'HR',
            'code'            => 'hr',
            'description'     => 'Human Resources Manager',
            'is_default'       => true,
        ]);

        $managerRole = Role::create([
            'organization_id' => $organization->id,
            'name'            => 'Manager',
            'code'            => 'manager',
            'description'     => 'Project Manager',
            'is_default'       => true,
        ]);

        $employeeRole = Role::create([
            'organization_id' => $organization->id,
            'name'            => 'Employee',
            'code'            => 'employee',
            'description'     => 'Employee',
            'is_default'       => true,
        ]);

        $this->permissionService->assignDefaultRolePermissions(
            $ownerRole,
            $adminRole,
            $managerRole,
            $employeeRole,
            $hrRole
        );


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
            throw ValidationException::withMessages([
                'email' => 'User not found.',
            ]);
        }

        if (!Hash::check($data['password'], $user->password)) {
         
            throw ValidationException::withMessages([
                'password' => 'Invalid password.',
            ]);
        }
        if ($user->status !=true) {
          
            throw ValidationException::withMessages([
                'email' => 'Your account is inactive.',
            ]);
        }
        if ($user->organization->status != true) {
         
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