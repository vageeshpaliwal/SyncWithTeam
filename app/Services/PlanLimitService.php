<?php

namespace App\Services;

use App\Models\User;
use App\Models\Department;
use App\Models\Project;
use App\Models\Invitation;
use App\Models\Organization;

class PlanLimitService
{
    public function getLimits(string $plan): array
    {
        $plan = strtolower($plan);

        switch ($plan) {
            case 'pro':
                return [
                    'users' => 50,
                    'departments' => 10,
                    'projects' => -1, // Unlimited
                ];
            case 'enterprise':
                return [
                    'users' => -1, // Unlimited
                    'departments' => -1,
                    'projects' => -1,
                ];
            case 'free':
            default:
                return [
                    'users' => 5,
                    'departments' => 2,
                    'projects' => 2,
                ];
        }
    }

    public function canAddUser(int $orgId): bool
    {
        $org = Organization::findOrFail($orgId);
        $limits = $this->getLimits($org->plan);

        if ($limits['users'] === -1) {
            return true;
        }

        $usersCount = User::where('organization_id', $orgId)->where('status', true)->count();
        $pendingInvitesCount = Invitation::where('organization_id', $orgId)->where('status', 'PENDING')->count();

        return ($usersCount + $pendingInvitesCount) < $limits['users'];
    }

    public function canAddDepartment(int $orgId): bool
    {
        $org = Organization::findOrFail($orgId);
        $limits = $this->getLimits($org->plan);

        if ($limits['departments'] === -1) {
            return true;
        }

        $departmentsCount = Department::where('organization_id', $orgId)->count();

        return $departmentsCount < $limits['departments'];
    }

    public function canAddProject(int $orgId): bool
    {
        $org = Organization::findOrFail($orgId);
        $limits = $this->getLimits($org->plan);

        if ($limits['projects'] === -1) {
            return true;
        }

        $projectsCount = Project::where('organization_id', $orgId)->count();

        return $projectsCount < $limits['projects'];
    }
}
