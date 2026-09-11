<?php

namespace App\Services;

use App\Models\Role;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class RoleService
{
    public function getForOrganization(int $organizationId): Collection
    {
        return Role::where('organization_id', $organizationId)
            ->with('permissions')
            ->withCount('users')
            ->orderBy('name')
            ->get();
    }

    public function create(array $data, int $organizationId): Role
    {
        return DB::transaction(function () use ($data, $organizationId) {
            $role = Role::create([
                'organization_id' => $organizationId,
                'name' => $data['name'],
                'code' => Str::slug($data['code'] ?? $data['name']),
                'description' => $data['description'] ?? null,
                'is_default' => false,
            ]);

            if (!empty($data['permissions'])) {
                $role->permissions()->sync($data['permissions']);
            }

            return $role;
        });
    }

    public function update(Role $role, array $data): Role
    {
        if ($role->is_default) {
            throw ValidationException::withMessages([
                'role' => 'System roles cannot be modified.',
            ]);
        }

        return DB::transaction(function () use ($role, $data) {
            $role->update([
                'name' => $data['name'],
                'code' => Str::slug($data['code'] ?? $data['name']),
                'description' => $data['description'] ?? null,
            ]);

            $role->permissions()->sync($data['permissions'] ?? []);

            return $role;
        });
    }

    public function delete(Role $role): void
    {
        if ($role->is_default) {
            throw ValidationException::withMessages([
                'role' => 'System roles cannot be deleted.',
            ]);
        }

        if ($role->users()->exists()) {
            throw ValidationException::withMessages([
                'role' => 'Cannot delete a role that is assigned to users.',
            ]);
        }

        DB::transaction(function () use ($role) {
            $role->permissions()->detach();
            $role->delete();
        });
    }
}
