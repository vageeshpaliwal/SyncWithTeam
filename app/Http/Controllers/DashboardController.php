<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CurrentUserService;
use App\Models\User;
use App\Models\Department;
use App\Models\Role;
use App\Models\Invitation;
use App\Models\Attendance;
use App\Models\Leave;
use App\Models\Holiday;
use App\Models\Announcement;

class DashboardController extends Controller
{
    protected CurrentUserService $currentUserService;

    public function __construct(CurrentUserService $currentUserService)
    {
        $this->currentUserService = $currentUserService;
    }

    public function index()
    {
        $currentUser = $this->currentUserService->getUser();
        $orgId = $currentUser->organization_id;

        $stats = [
            'active_users' => User::where('organization_id', $orgId)->where('status', true)->count(),
            'pending_invitations' => Invitation::where('organization_id', $orgId)->where('status', 'PENDING')->count(),
            'departments' => Department::where('organization_id', $orgId)->count(),
            'roles' => Role::where('organization_id', $orgId)->count(),
        ];

        $recentUsers = User::where('organization_id', $orgId)
            ->with(['role', 'department'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $recentDepartments = Department::where('organization_id', $orgId)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $activeAttendance = Attendance::where('user_id', $currentUser->id)
            ->whereNull('clock_out_at')
            ->with(['project', 'task'])
            ->orderByDesc('clock_in_at')
            ->first();

        if ($currentUser->role?->code === 'owner' || $currentUser->role?->code === 'admin') {
            $myProjects = \App\Models\Project::where('organization_id', $orgId)
                ->where('status', '!=', 'completed')
                ->withCount('tasks')
                ->get();
        } elseif ($currentUser->role?->code === 'manager') {
            $managerDeptId = $currentUser->department_id;
            $myProjectsQuery = \App\Models\Project::where('organization_id', $orgId)
                ->where('status', '!=', 'completed');

            if ($managerDeptId) {
                $myProjectsQuery->where(function ($q) use ($currentUser, $managerDeptId) {
                    $q->whereHas('users', function ($uQ) use ($currentUser, $managerDeptId) {
                        $uQ->where('users.id', $currentUser->id)
                           ->orWhere('users.department_id', $managerDeptId);
                    })->orWhere('team_lead_id', $currentUser->id);
                });
            }
            $myProjects = $myProjectsQuery->withCount('tasks')->get();
        } else {
            $myProjects = \App\Models\Project::where('organization_id', $orgId)
                ->where('status', '!=', 'completed')
                ->where(function ($q) use ($currentUser) {
                    $q->whereHas('users', function ($uQ) use ($currentUser) {
                        $uQ->where('users.id', $currentUser->id);
                    })->orWhere('team_lead_id', $currentUser->id);
                })
                ->withCount('tasks')
                ->get();
        }

        $myTasks = \App\Models\Task::where('user_id', $currentUser->id)
            ->whereIn('status', ['todo', 'in_progress', 'review'])
            ->with('project')
            ->get();

        $weeklyAttendance = Attendance::where('user_id', $currentUser->id)
            ->where('date', '>=', now()->subDays(7)->toDateString())
            ->whereNotNull('clock_out_at')
            ->get();

        $totalSeconds = 0;
        foreach ($weeklyAttendance as $att) {
            $totalSeconds += $att->clock_in_at->diffInSeconds($att->clock_out_at);
        }
        $myWeeklyHours = round($totalSeconds / 3600, 1);

        // Fetch Live Team Status
        $todayStr = now()->toDateString();
        $onLeaveUserIds = Leave::where('organization_id', $orgId)
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $todayStr)
            ->whereDate('end_date', '>=', $todayStr)
            ->pluck('user_id')
            ->toArray();

        $activeClockedInUsers = Attendance::where('organization_id', $orgId)
            ->whereNull('clock_out_at')
            ->with(['user.role', 'user.department', 'project', 'task'])
            ->get();

        $teamMembersQuery = User::where('organization_id', $orgId)
            ->where('status', true)
            ->where('id', '!=', $currentUser->id);

        $isOwnerOrAdmin = in_array(strtolower($currentUser->role?->code ?? ''), ['owner', 'admin']);
        if (!$isOwnerOrAdmin) {
            if ($currentUser->department_id) {
                $teamMembersQuery->where('department_id', $currentUser->department_id);
            } else {
                $teamMembersQuery->whereNull('department_id');
            }
        }

        $teamMembers = $teamMembersQuery->with(['role', 'department'])
            ->orderBy('first_name')
            ->get()
            ->map(function ($u) use ($activeClockedInUsers, $onLeaveUserIds) {
                $clockedInAtt = $activeClockedInUsers->firstWhere('user_id', $u->id);
                if ($clockedInAtt) {
                    $u->live_status = 'working';
                    $u->active_attendance = $clockedInAtt;
                } elseif (in_array($u->id, $onLeaveUserIds)) {
                    $u->live_status = 'on_leave';
                } else {
                    $u->live_status = 'offline';
                }
                return $u;
            });

        // Announcements & Holidays
        $announcements = Announcement::where('organization_id', $orgId)
            ->with('author')
            ->orderByDesc('created_at')
            ->take(3)
            ->get();

        $upcomingHolidays = Holiday::where('organization_id', $orgId)
            ->whereDate('date', '>=', $todayStr)
            ->orderBy('date')
            ->take(4)
            ->get();

        return view('dashboard.index', compact(
            'currentUser',
            'stats',
            'recentUsers',
            'recentDepartments',
            'activeAttendance',
            'myProjects',
            'myTasks',
            'myWeeklyHours',
            'teamMembers',
            'announcements',
            'upcomingHolidays'
        ));
    }
}