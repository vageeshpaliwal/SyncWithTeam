<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Models\Permission;
use App\Models\Role;
use App\Services\CurrentUserService;
use App\Services\RoleService;
use Illuminate\Validation\ValidationException;

class RoleController extends Controller
{
    public function __construct(
        protected CurrentUserService $currentUserService,
        protected RoleService $roleService,
    ) {}

    public function index()
    {
        $roles = $this->roleService->getForOrganization(
            $this->currentUserService->getOrganization()->id
        );

        $canManage = $this->currentUserService->hasPermission('roles.manage');

        return view('roles.index', compact('roles', 'canManage'));
    }

    public function create()
    {
        $permissions = Permission::orderBy('name')->get();

        return view('roles.create', compact('permissions'));
    }

    public function store(StoreRoleRequest $request)
    {
        $this->roleService->create(
            $request->validated(),
            $this->currentUserService->getOrganization()->id
        );

        return redirect()->route('roles.index')->with('success', 'Role created successfully.');
    }

    public function edit(Role $role)
    {
        $this->ensureBelongsToOrganization($role);

        if ($role->is_default) {
            return redirect()->route('roles.index')->with('error', 'System roles cannot be edited.');
        }

        $permissions = Permission::orderBy('name')->get();
        $role->load('permissions');

        return view('roles.edit', compact('role', 'permissions'));
    }

    public function update(UpdateRoleRequest $request, Role $role)
    {
        $this->ensureBelongsToOrganization($role);

        try {
            $this->roleService->update($role, $request->validated());
        } catch (ValidationException $e) {
            return redirect()->route('roles.index')->withErrors($e->errors());
        }

        return redirect()->route('roles.index')->with('success', 'Role updated successfully.');
    }

    public function destroy(Role $role)
    {
        $this->ensureBelongsToOrganization($role);

        try {
            $this->roleService->delete($role);
        } catch (ValidationException $e) {
            return redirect()->route('roles.index')->withErrors($e->errors());
        }

        return redirect()->route('roles.index')->with('success', 'Role deleted successfully.');
    }

    private function ensureBelongsToOrganization(Role $role): void
    {
        if ($role->organization_id !== $this->currentUserService->getOrganization()->id) {
            abort(404);
        }
    }
}
