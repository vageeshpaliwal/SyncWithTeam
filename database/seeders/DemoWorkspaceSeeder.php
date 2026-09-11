<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\User;
use App\Models\Role;
use App\Models\Department;
use App\Models\Project;
use App\Services\PermissionService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoWorkspaceSeeder extends Seeder
{
    public function run(): void
    {
        $org = Organization::firstOrCreate(
            ['slug' => 'demo-corp'],
            [
                'name' => 'Demo Corp',
                'email' => 'contact@democorp.com',
                'plan' => 'free',
            ]
        );

        $ownerRole = Role::firstOrCreate(
            ['organization_id' => $org->id, 'code' => 'owner'],
            ['name' => 'Owner', 'description' => 'Organization Owner', 'is_default' => true]
        );

        $adminRole = Role::firstOrCreate(
            ['organization_id' => $org->id, 'code' => 'admin'],
            ['name' => 'Admin', 'description' => 'Administrator', 'is_default' => true]
        );

        $managerRole = Role::firstOrCreate(
            ['organization_id' => $org->id, 'code' => 'manager'],
            ['name' => 'Manager', 'description' => 'Manager', 'is_default' => true]
        );

        $employeeRole = Role::firstOrCreate(
            ['organization_id' => $org->id, 'code' => 'employee'],
            ['name' => 'Employee', 'description' => 'Employee', 'is_default' => true]
        );

        $permissionService = new PermissionService();
        $permissionService->assignDefaultRolePermissions($ownerRole, $adminRole, $managerRole, $employeeRole);

        $dept = Department::firstOrCreate(
            ['organization_id' => $org->id, 'name' => 'Engineering'],
            ['description' => 'Product development and engineering team.', 'status' => true]
        );

        $owner = User::firstOrCreate(
            ['email' => 'owner@example.com'],
            [
                'first_name' => 'John',
                'last_name' => 'Owner',
                'username' => 'johnowner',
                'password' => Hash::make('password'),
                'role_id' => $ownerRole->id,
                'organization_id' => $org->id,
                'status' => true,
            ]
        );

        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'first_name' => 'Sarah',
                'last_name' => 'Admin',
                'username' => 'sarahadmin',
                'password' => Hash::make('password'),
                'role_id' => $adminRole->id,
                'organization_id' => $org->id,
                'status' => true,
            ]
        );

        $manager = User::firstOrCreate(
            ['email' => 'manager@example.com'],
            [
                'first_name' => 'Mike',
                'last_name' => 'Manager',
                'username' => 'mikemanager',
                'password' => Hash::make('password'),
                'role_id' => $managerRole->id,
                'organization_id' => $org->id,
                'department_id' => $dept->id,
                'status' => true,
            ]
        );

        $employee = User::firstOrCreate(
            ['email' => 'employee@example.com'],
            [
                'first_name' => 'David',
                'last_name' => 'Employee',
                'username' => 'davidemployee',
                'password' => Hash::make('password'),
                'role_id' => $employeeRole->id,
                'organization_id' => $org->id,
                'department_id' => $dept->id,
                'status' => true,
            ]
        );

        $project = Project::firstOrCreate(
            ['organization_id' => $org->id, 'name' => 'Apollo Lander'],
            [
                'description' => 'Lunar landing modules planning and telemetry development project.',
                'status' => 'active',
                'start_date' => now()->subDays(5),
                'end_date' => now()->addDays(30),
            ]
        );

        $project->users()->syncWithoutDetaching([$manager->id, $employee->id]);
    }
}
