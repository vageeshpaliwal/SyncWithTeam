<?php

namespace App\Http\Controllers;

use App\Http\Requests\InviteUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Mail\UserInvitationMail;
use App\Models\User;
use App\Models\Invitation;
use App\Services\CurrentUserService;
use App\Services\UserService;
use App\Services\DepartmentService;
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{
    public function __construct(
        protected CurrentUserService $currentUserService,
        protected UserService $userService,
    ) {}

    public function users()
    {
        $organization = $this->currentUserService->getOrganization();
        $tab = request('tab', 'users');
        $search = request('search');
        $departmentId = request('department_id');
        $roleId = request('role_id');

        $query = User::with(['role', 'department'])->where('organization_id', $organization->id);

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('designation', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%");
            });
        }

        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }

        if ($roleId) {
            $query->where('role_id', $roleId);
        }

        $users = $query->orderBy('first_name')->get();
        $pendingInvitations = $this->userService->getPendingInvitations($organization->id);
        $acceptedInvitations = $this->userService->getAcceptedInvitations($organization->id);

        $departments = \App\Models\Department::where('organization_id', $organization->id)->orderBy('name')->get();
        $roles = \App\Models\Role::where('organization_id', $organization->id)->orderBy('name')->get();

        return view('users.index', compact(
            'users',
            'pendingInvitations',
            'acceptedInvitations',
            'departments',
            'roles',
            'tab'
        ));
    }

    public function invite(DepartmentService $departmentService)
    {
        $roles = $this->currentUserService->getRoles();
        $departments = $departmentService->getForOrganization($this->currentUserService->getOrganization()->id);

        return view('users.invite', compact('roles', 'departments'));
    }

    public function addUser(InviteUserRequest $request)
    {
        $validatedData = $request->validated();
        $org = $this->currentUserService->getOrganization();
        $invitedBy = $this->currentUserService->getUser();

        $invitation = $this->userService->addUser($validatedData, $org->id, $invitedBy->id);

        if (!$invitation) {
            return redirect()->back()->with('error', 'Failed to create invitation.');
        }

        Mail::to($invitation->email)->send(new UserInvitationMail($invitation));

        return redirect()->route('users.index')->with('success', 'Invitation sent successfully.');
    }

    public function show(User $user)
    {
        $this->ensureBelongsToOrganization($user);
        $user->load(['role', 'department']);

        $weeklyQuotaHours = 40.0;
        $recentWeeklyStats = [];

        for ($i = 0; $i < 4; $i++) {
            $wStart = now()->subWeeks($i)->startOfWeek();
            $wEnd = now()->subWeeks($i)->endOfWeek();

            $wLogs = \App\Models\Attendance::where('user_id', $user->id)
                ->whereBetween('date', [$wStart->toDateString(), $wEnd->toDateString()])
                ->whereNotNull('clock_out_at')
                ->with(['project', 'task'])
                ->get();

            $wSecs = 0;
            foreach ($wLogs as $log) {
                if ($log->clock_out_at && $log->clock_out_at >= $log->clock_in_at) {
                    $wSecs += $log->clock_in_at->diffInSeconds($log->clock_out_at);
                }
            }

            $wHours = round($wSecs / 3600, 1);
            $recentWeeklyStats[] = [
                'label' => $i === 0 ? 'Current Week' : ($i === 1 ? 'Last Week' : $wStart->format('d M') . ' - ' . $wEnd->format('d M')),
                'start' => $wStart->format('d M'),
                'end' => $wEnd->format('d M Y'),
                'hours' => $wHours,
                'quota' => $weeklyQuotaHours,
                'percent' => min(100, round(($wHours / $weeklyQuotaHours) * 100)),
                'logs_count' => $wLogs->count(),
                'logs' => $wLogs,
            ];
        }

        $currentWeekHours = $recentWeeklyStats[0]['hours'];
        $currentWeekPercent = $recentWeeklyStats[0]['percent'];

        return view('users.show', compact(
            'user',
            'weeklyQuotaHours',
            'recentWeeklyStats',
            'currentWeekHours',
            'currentWeekPercent'
        ));
    }

    public function edit(User $user, DepartmentService $departmentService)
    {
        $this->ensureBelongsToOrganization($user);
        $this->ensureOwnerProtection($user);

        $roles = $this->currentUserService->getRoles();
        $departments = $departmentService->getForOrganization($this->currentUserService->getOrganization()->id);

        return view('users.edit', compact('user', 'roles', 'departments'));
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $this->ensureBelongsToOrganization($user);
        $this->ensureOwnerProtection($user);

        $this->userService->updateUser($user, $request->validated());

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    public function toggleStatus(User $user)
    {
        $this->ensureBelongsToOrganization($user);
        $this->ensureOwnerProtection($user);

        // Check that they aren't deactivating themselves
        if ($user->id === $this->currentUserService->getUser()->id) {
            return redirect()->back()->with('error', 'You cannot deactivate yourself.');
        }

        $this->userService->toggleUserStatus($user);

        return redirect()->back()->with('success', 'User status updated successfully.');
    }

    public function destroy(User $user)
    {
        $this->ensureBelongsToOrganization($user);
        $this->ensureOwnerProtection($user);

        if ($user->id === $this->currentUserService->getUser()->id) {
            return redirect()->back()->with('error', 'You cannot delete yourself.');
        }

        $this->userService->deleteUser($user);

        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }

    public function resendInvitation(Invitation $invitation)
    {
        $this->ensureInvitationBelongsToOrganization($invitation);

        $this->userService->resendInvitation($invitation);

        Mail::to($invitation->email)->send(new UserInvitationMail($invitation));

        return redirect()->route('users.index', ['tab' => 'pending'])->with('success', 'Invitation resent successfully.');
    }

    public function cancelInvitation(Invitation $invitation)
    {
        $this->ensureInvitationBelongsToOrganization($invitation);

        $this->userService->cancelInvitation($invitation);

        return redirect()->route('users.index', ['tab' => 'pending'])->with('success', 'Invitation cancelled successfully.');
    }

    private function ensureBelongsToOrganization(User $user): void
    {
        if ($user->organization_id !== $this->currentUserService->getOrganization()->id) {
            abort(404);
        }
    }

    private function ensureInvitationBelongsToOrganization(Invitation $invitation): void
    {
        if ($invitation->organization_id !== $this->currentUserService->getOrganization()->id) {
            abort(404);
        }
    }

    private function ensureOwnerProtection(User $user): void
    {
        $currentUser = $this->currentUserService->getUser();
        if ($user->role?->code === 'owner' && $currentUser->role?->code !== 'owner') {
            abort(403, 'Administrators cannot modify the organization Owner.');
        }
    }
}
