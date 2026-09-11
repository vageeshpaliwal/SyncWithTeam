<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDepartmentRequest;
use App\Http\Requests\UpdateDepartmentRequest;
use App\Models\Department;
use App\Services\CurrentUserService;
use App\Services\DepartmentService;
use Illuminate\Validation\ValidationException;

class DepartmentController extends Controller
{
    public function __construct(
        protected CurrentUserService $currentUserService,
        protected DepartmentService $departmentService,
    ) {}

    public function index()
    {
        $departments = $this->departmentService->getForOrganization(
            $this->currentUserService->getOrganization()->id
        );

        return view('departments.index', compact('departments'));
    }

    public function show(Department $department)
    {
        $this->ensureBelongsToOrganization($department);

        $department->load(['users.role']);

        $manager = $department->users->first(fn($u) => strtolower($u->role?->code ?? '') === 'manager' || strtolower($u->role?->code ?? '') === 'owner');

        $projects = $department->projects;

        return view('departments.show', compact('department', 'manager', 'projects'));
    }

    public function myDepartment()
    {
        $currentUser = $this->currentUserService->getUser();
        $department = $currentUser->department;

        if (!$department) {
            return redirect()->route('dashboard')->with('error', 'You are not assigned to any department.');
        }

        $manager = $department->users()
            ->whereHas('role', function($query) {
                $query->where('code', 'manager');
            })
            ->with(['role', 'department'])
            ->first();

        $coworkers = $department->users()
            ->where('id', '!=', $currentUser->id)
            ->with(['role', 'department'])
            ->get();

        return view('departments.my_department', compact('department', 'manager', 'coworkers'));
    }

    public function create()
    {
        return view('departments.create');
    }

    public function store(StoreDepartmentRequest $request)
    {
        $this->departmentService->create(
            $request->validated(),
            $this->currentUserService->getOrganization()->id
        );

        return redirect()->route('departments.index')->with('success', 'Department created successfully.');
    }

    public function edit(Department $department)
    {
        $this->ensureBelongsToOrganization($department);

        return view('departments.edit', compact('department'));
    }

    public function update(UpdateDepartmentRequest $request, Department $department)
    {
        $this->ensureBelongsToOrganization($department);

        $this->departmentService->update($department, $request->validated());

        return redirect()->route('departments.index')->with('success', 'Department updated successfully.');
    }

    public function destroy(Department $department)
    {
        $this->ensureBelongsToOrganization($department);

        $this->departmentService->delete($department);

        return redirect()->route('departments.index')->with('success', 'Department deleted successfully.');
    }

    private function ensureBelongsToOrganization(Department $department): void
    {
        if ($department->organization_id !== $this->currentUserService->getOrganization()->id) {
            abort(404);
        }
    }
}
