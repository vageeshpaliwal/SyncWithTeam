<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Department;
use App\Models\Project;
use App\Models\Invitation;
use App\Services\CurrentUserService;
use App\Services\PlanLimitService;
use Illuminate\Http\Request;

class BillingController extends Controller
{
    public function __construct(
        protected CurrentUserService $currentUserService,
        protected PlanLimitService $planLimitService
    ) {}

    public function index()
    {
        $currentUser = $this->currentUserService->getUser();
        if ($currentUser->role->code !== 'owner') {
            abort(403, 'Only the organization Owner can view billing details.');
        }

        $org = $this->currentUserService->getOrganization();
        $limits = $this->planLimitService->getLimits($org->plan);

        $usersCount = User::where('organization_id', $org->id)->where('status', true)->count();
        $pendingInvitesCount = Invitation::where('organization_id', $org->id)->where('status', 'PENDING')->count();
        $totalUsers = $usersCount + $pendingInvitesCount;

        $departmentsCount = Department::where('organization_id', $org->id)->count();
        $projectsCount = Project::where('organization_id', $org->id)->count();

        $usage = [
            'users' => [
                'current' => $totalUsers,
                'limit' => $limits['users'],
                'percent' => $limits['users'] > 0 ? min(100, round(($totalUsers / $limits['users']) * 100)) : 0,
            ],
            'departments' => [
                'current' => $departmentsCount,
                'limit' => $limits['departments'],
                'percent' => $limits['departments'] > 0 ? min(100, round(($departmentsCount / $limits['departments']) * 100)) : 0,
            ],
            'projects' => [
                'current' => $projectsCount,
                'limit' => $limits['projects'],
                'percent' => $limits['projects'] > 0 ? min(100, round(($projectsCount / $limits['projects']) * 100)) : 0,
            ],
        ];

        return view('billing.index', compact('org', 'limits', 'usage'));
    }

    public function upgrade(Request $request)
    {
        $currentUser = $this->currentUserService->getUser();
        if ($currentUser->role->code !== 'owner') {
            abort(403, 'Only the organization Owner can modify subscription plans.');
        }

        $request->validate([
            'plan' => 'required|in:free,pro,enterprise',
        ]);

        $org = $this->currentUserService->getOrganization();
        $org->update([
            'plan' => $request->plan,
            'plan_expires_at' => $request->plan !== 'free' ? now()->addYear() : null,
        ]);

        return redirect()->route('billing.index')->with('success', 'Plan updated successfully to ' . ucfirst($request->plan) . '.');
    }
}
