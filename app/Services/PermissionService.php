<?php

namespace App\Services;

use App\Models\Permission;
use App\Models\Role;

class PermissionService
{
    public function ensurePermissionsExist(): void
    {
        if (Permission::count() > 0) {
            return;
        }

        (new \Database\Seeders\PermissionSeeder())->run();
    }

    public function assignDefaultRolePermissions(
        Role $owner,
        Role $admin,
        Role $manager,
        Role $employee,
        ?Role $hr = null,
    ): void {
        $this->ensurePermissionsExist();

        $permissions = Permission::pluck('id', 'name');

        $owner->permissions()->sync($permissions->values());

        // Admin: Full HR & Operations administration (NO Projects/Tasks)
        $adminPermissions = [
            $permissions['dashboard.view'],
            $permissions['users.view'],
            $permissions['users.invite'],
            $permissions['users.manage'],
            $permissions['roles.view'],
            $permissions['departments.view'],
            $permissions['departments.manage'],
            $permissions['settings.view'],
            $permissions['attendance.manage'],
            $permissions['attendance.view'],
            $permissions['leaves.request'],
            $permissions['leaves.approve'],
            $permissions['leaves.manage'],
            $permissions['payroll.view'],
            $permissions['payroll.manage'],
        ];
        if (isset($permissions['billing.view'])) {
            $adminPermissions[] = $permissions['billing.view'];
        }
        if (isset($permissions['roles.manage'])) {
            $adminPermissions[] = $permissions['roles.manage'];
        }
        if (isset($permissions['settings.edit'])) {
            $adminPermissions[] = $permissions['settings.edit'];
        }
        $admin->permissions()->sync($adminPermissions);

        // HR Role (if present): Employee & HR operations only
        if ($hr) {
            $hr->permissions()->sync([
                $permissions['dashboard.view'],
                $permissions['users.view'],
                $permissions['users.invite'],
                $permissions['users.manage'],
                $permissions['roles.view'],
                $permissions['departments.view'],
                $permissions['departments.manage'],
                $permissions['attendance.manage'],
                $permissions['attendance.view'],
                $permissions['leaves.request'],
                $permissions['leaves.approve'],
                $permissions['leaves.manage'],
                $permissions['payroll.view'],
                $permissions['payroll.manage'],
            ]);
        }

        // Manager: Projects, Tasks, and Team Leaves
        $manager->permissions()->sync([
            $permissions['dashboard.view'],
            $permissions['users.view'],
            $permissions['departments.view'],
            $permissions['departments.manage'],
            $permissions['attendance.manage'],
            $permissions['attendance.view'],
            $permissions['projects.manage'],
            $permissions['projects.view'],
            $permissions['tasks.manage'],
            $permissions['leaves.request'],
            $permissions['leaves.approve'],
        ]);

        // Employee: Assigned Projects, Tasks, Personal Attendance & Leaves
        $employee->permissions()->sync([
            $permissions['dashboard.view'],
            $permissions['attendance.manage'],
            $permissions['projects.view'],
            $permissions['tasks.manage'],
            $permissions['leaves.request'],
        ]);
    }

}
