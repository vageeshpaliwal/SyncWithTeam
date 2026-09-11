<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['name' => 'dashboard.view', 'description' => 'View dashboard'],
            ['name' => 'users.view', 'description' => 'View users list'],
            ['name' => 'users.invite', 'description' => 'Invite new users'],
            ['name' => 'users.manage', 'description' => 'Edit, delete, and toggle status of users'],
            ['name' => 'roles.view', 'description' => 'View roles'],
            ['name' => 'roles.manage', 'description' => 'Create and edit roles'],
            ['name' => 'departments.view', 'description' => 'View departments'],
            ['name' => 'departments.manage', 'description' => 'Create and edit departments'],
            ['name' => 'settings.view', 'description' => 'View organization settings'],
            ['name' => 'settings.edit', 'description' => 'Edit organization settings'],
            ['name' => 'attendance.manage', 'description' => 'Clock in and out'],
            ['name' => 'attendance.view', 'description' => 'View organization attendance logs'],
            ['name' => 'projects.manage', 'description' => 'Create, edit, delete, and assign projects'],
            ['name' => 'projects.view', 'description' => 'View assigned projects'],
            ['name' => 'tasks.manage', 'description' => 'Create, edit, and delete tasks under projects'],
            ['name' => 'billing.view', 'description' => 'View and manage subscription plans'],
            ['name' => 'leaves.request', 'description' => 'Request leaves and view own request logs'],
            ['name' => 'leaves.approve', 'description' => 'Approve or reject leave requests'],
            ['name' => 'leaves.manage', 'description' => 'Administer leave requests globally'],
            ['name' => 'payroll.view', 'description' => 'View payroll and salary logs'],
            ['name' => 'payroll.manage', 'description' => 'Generate and process monthly payrolls and payslips'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name']],
                ['description' => $permission['description']]
            );
        }
    }
}
