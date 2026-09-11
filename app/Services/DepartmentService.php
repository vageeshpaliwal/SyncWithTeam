<?php

namespace App\Services;

use App\Models\Department;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Services\PlanLimitService;
use Illuminate\Validation\ValidationException;

class DepartmentService
{
    public function __construct(
        protected PlanLimitService $planLimitService
    ) {}

    public function getForOrganization(int $organizationId): Collection
    {
        return Department::where('organization_id', $organizationId)
            ->with(['users.role'])
            ->withCount('users')
            ->orderBy('name')
            ->get();
    }

    public function create(array $data, int $organizationId): Department
    {
        if (!$this->planLimitService->canAddDepartment($organizationId)) {
            throw ValidationException::withMessages([
                'name' => 'Plan limit reached! Upgrade your subscription to create more departments.',
            ]);
        }

        return Department::create([
            'organization_id' => $organizationId,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'status' => filter_var($data['status'] ?? true, FILTER_VALIDATE_BOOLEAN),
        ]);
    }

    public function update(Department $department, array $data): Department
    {
        $department->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'status' => filter_var($data['status'] ?? true, FILTER_VALIDATE_BOOLEAN),
        ]);

        return $department;
    }

    public function delete(Department $department): void
    {
        DB::transaction(function () use ($department) {
            $department->delete();
        });
    }
}
