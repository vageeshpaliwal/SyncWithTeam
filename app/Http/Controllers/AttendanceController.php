<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Leave;
use App\Models\Project;
use App\Models\Task;
use App\Services\CurrentUserService;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function __construct(
        protected CurrentUserService $currentUserService
    ) {}

    public function index()
    {
        $org = $this->currentUserService->getOrganization();
        $user = $this->currentUserService->getUser();

        // Check if user is on active approved leave right now (Full Day, First Half, Second Half)
        $leaveBlockInfo = Leave::getActiveLeaveForUser($user->id);
        $activeLeave = $leaveBlockInfo ? $leaveBlockInfo['leave'] : null;

        // 1. Load active projects for tracking selection
        if ($user->role?->code === 'owner' || $this->currentUserService->hasPermission('projects.manage')) {
            $projects = Project::where('organization_id', $org->id)->where('status', '!=', 'completed')->get();
        } elseif ($this->currentUserService->hasPermission('projects.view')) {
            $projects = Project::where('organization_id', $org->id)
                ->where('status', '!=', 'completed')
                ->where(function ($q) use ($user) {
                    $q->whereHas('users', function ($uQ) use ($user) {
                        $uQ->where('users.id', $user->id);
                    })->orWhere('team_lead_id', $user->id);
                })->get();
        } else {
            $projects = collect();
        }


        // 2. Load active tracking session
        $activeAttendance = Attendance::where('user_id', $user->id)
            ->whereNull('clock_out_at')
            ->with(['project', 'task'])
            ->first();

        // 3. Load personal logs grouped by date (only completed logs after stop)
        $myLogs = Attendance::where('user_id', $user->id)
            ->whereNotNull('clock_out_at')
            ->with(['project', 'task'])
            ->orderByDesc('clock_in_at')
            ->get();

        $groupedLogs = $myLogs->groupBy(function ($log) {
            $date = $log->clock_in_at ?: $log->date;
            return $date ? $date->format('Y-m-d') : 'other';
        });

        $dailyTotals = [];
        foreach ($groupedLogs as $dateKey => $logs) {
            $totalSeconds = 0;
            foreach ($logs as $log) {
                if ($log->clock_out_at && $log->clock_in_at) {
                    $start = $log->clock_in_at;
                    $end = $log->clock_out_at;
                    if ($start > $end) {
                        $temp = $start;
                        $start = $end;
                        $end = $temp;
                    }
                    $totalSeconds += $start->diffInSeconds($end);
                }
            }
            $dailyTotals[$dateKey] = $totalSeconds;
        }

        // Group personal logs by Week (Clockify/Toggl multi-week timeline)
        $myWeeklyGroupedLogs = [];
        $weeklyGroups = $myLogs->groupBy(function ($log) {
            $date = $log->clock_in_at ?: $log->date;
            $startOfWeek = $date ? $date->copy()->startOfWeek() : now()->startOfWeek();
            $endOfWeek = $date ? $date->copy()->endOfWeek() : now()->endOfWeek();
            return $startOfWeek->format('Y-m-d') . '|' . $startOfWeek->format('d M') . ' - ' . $endOfWeek->format('d M, Y');
        });

        foreach ($weeklyGroups as $wKey => $wLogs) {
            [$wStartIso, $wDateRange] = explode('|', $wKey, 2);
            $wStartDate = \Carbon\Carbon::parse($wStartIso);
            $isThisWeek = $wStartDate->isCurrentWeek();
            $isLastWeek = $wStartDate->isLastWeek();

            $wTotalSeconds = 0;
            $daysInWeek = $wLogs->groupBy(function ($log) {
                $date = $log->clock_in_at ?: $log->date;
                return $date ? $date->format('Y-m-d') : 'other';
            });

            $daysFormatted = [];
            foreach ($daysInWeek as $dKey => $dLogs) {
                $dTotalSecs = 0;
                foreach ($dLogs as $l) {
                    if ($l->clock_out_at && $l->clock_in_at) {
                        $start = $l->clock_in_at;
                        $end = $l->clock_out_at;
                        if ($start > $end) {
                            $temp = $start;
                            $start = $end;
                            $end = $temp;
                        }
                        $dTotalSecs += $start->diffInSeconds($end);
                    }
                }
                $wTotalSeconds += $dTotalSecs;
                $daysFormatted[$dKey] = [
                    'date_key' => $dKey,
                    'total_seconds' => $dTotalSecs,
                    'logs' => $dLogs,
                ];
            }

            $wHrs = floor($wTotalSeconds / 3600);
            $wMins = floor(($wTotalSeconds % 3600) / 60);
            $wSecs = $wTotalSeconds % 60;

            $weekTitle = $isThisWeek ? 'This Week' : ($isLastWeek ? 'Last Week' : 'Week of ' . $wStartDate->format('d M, Y'));

            $myWeeklyGroupedLogs[] = [
                'start_date' => $wStartIso,
                'title' => $weekTitle,
                'date_range' => $wDateRange,
                'is_this_week' => $isThisWeek,
                'is_last_week' => $isLastWeek,
                'total_seconds' => $wTotalSeconds,
                'total_formatted' => sprintf('%02d:%02d:%02d', $wHrs, $wMins, $wSecs),
                'days' => $daysFormatted,
            ];
        }

        // Calculate Personal Weekly & Daily Aggregate Stats
        $wStart = now()->startOfWeek();
        $wEnd = now()->endOfWeek();
        $todayStr = now()->toDateString();

        $myWeeklyLogs = Attendance::where('user_id', $user->id)
            ->where(function ($q) use ($wStart, $wEnd) {
                $q->whereBetween('date', [$wStart->toDateString(), $wEnd->toDateString()])
                  ->orWhereBetween('clock_in_at', [$wStart, $wEnd]);
            })
            ->whereNotNull('clock_out_at')
            ->get();

        $myWeeklySeconds = 0;
        $myDaysWorked = [];
        foreach ($myWeeklyLogs as $l) {
            if ($l->clock_out_at && $l->clock_in_at) {
                $start = $l->clock_in_at;
                $end = $l->clock_out_at;
                if ($start > $end) {
                    $temp = $start;
                    $start = $end;
                    $end = $temp;
                }
                $myWeeklySeconds += $start->diffInSeconds($end);
            }
            $d = $l->clock_in_at ? $l->clock_in_at->toDateString() : ($l->date ? $l->date->toDateString() : null);
            if ($d) {
                $myDaysWorked[$d] = true;
            }
        }

        $myTodaySeconds = 0;
        $myTodayLogs = Attendance::where('user_id', $user->id)
            ->where(function ($q) use ($todayStr) {
                $q->whereDate('date', $todayStr)
                  ->orWhereDate('clock_in_at', $todayStr);
            })
            ->whereNotNull('clock_out_at')
            ->get();

        foreach ($myTodayLogs as $l) {
            if ($l->clock_out_at && $l->clock_in_at) {
                $start = $l->clock_in_at;
                $end = $l->clock_out_at;
                if ($start > $end) {
                    $temp = $start;
                    $start = $end;
                    $end = $temp;
                }
                $myTodaySeconds += $start->diffInSeconds($end);
            }
        }

        $myWeeklyHours = round($myWeeklySeconds / 3600, 1);
        $myTodayHours = round($myTodaySeconds / 3600, 1);
        $myDaysCount = count($myDaysWorked);
        $myWeeklyPercent = min(100, round(($myWeeklySeconds / (40 * 3600)) * 100));

        $myPersonalStats = [
            'today_seconds' => $myTodaySeconds,
            'today_hours' => $myTodayHours,
            'weekly_seconds' => $myWeeklySeconds,
            'weekly_hours' => $myWeeklyHours,
            'days_worked' => $myDaysCount,
            'weekly_percent' => $myWeeklyPercent,
            'total_logs' => $myLogs->count(),
        ];

        // 4. Load team logs & weekly 40-hour quota stats for administrators/managers
        $teamLogs = collect();
        $organizationUsers = collect();
        $teamWeeklyStats = [];

        if ($this->currentUserService->hasPermission('attendance.view')) {
            $organizationUsers = \App\Models\User::where('organization_id', $org->id)
                ->orderBy('first_name')
                ->get();

            $teamQuery = Attendance::where('organization_id', $org->id)
                ->with(['user', 'project', 'task']);

            if (request()->filled('employee_id')) {
                $teamQuery->where('user_id', request('employee_id'));
            }

            if (request()->filled('log_date')) {
                $teamQuery->whereDate('date', request('log_date'));
            }

            $teamLogs = $teamQuery->orderByDesc('clock_in_at')->paginate(20)->withQueryString();

            // Weekly 40-Hour Quota calculation for Team Members (Current Week)
            $wStart = now()->startOfWeek();
            $wEnd = now()->endOfWeek();

            $empQuery = \App\Models\User::where('organization_id', $org->id);
            if (request()->filled('employee_id')) {
                $empQuery->where('id', request('employee_id'));
            }
            $targetEmployees = $empQuery->orderBy('first_name')->get();

            foreach ($targetEmployees as $emp) {
                $empLogs = Attendance::where('user_id', $emp->id)
                    ->whereBetween('date', [$wStart->toDateString(), $wEnd->toDateString()])
                    ->whereNotNull('clock_out_at')
                    ->get();

                $empSecs = 0;
                $empDates = [];
                foreach ($empLogs as $l) {
                    if ($l->clock_out_at && $l->clock_in_at) {
                        $start = $l->clock_in_at;
                        $end = $l->clock_out_at;
                        if ($start > $end) {
                            $temp = $start;
                            $start = $end;
                            $end = $temp;
                        }
                        $empSecs += $start->diffInSeconds($end);
                    }
                    $d = $l->clock_in_at ? $l->clock_in_at->toDateString() : $l->date->toDateString();
                    $empDates[$d] = true;
                }

                $eHrs = floor($empSecs / 3600);
                $eMins = floor(($empSecs % 3600) / 60);
                $eSecsRem = $empSecs % 60;
                $eFormatted = sprintf('%02d:%02d:%02d', $eHrs, $eMins, $eSecsRem);
                $empHours = round($empSecs / 3600, 2);
                $percent = min(100, round(($empHours / 40.0) * 100));

                $teamWeeklyStats[] = [
                    'user' => $emp,
                    'hours' => $empHours,
                    'formatted_total' => $eFormatted,
                    'quota' => 40.0,
                    'percent' => $percent,
                    'logs_count' => $empLogs->count(),
                    'days_worked' => count($empDates),
                ];
            }
        }

        // 5. Screenshots Day-Wise (Current Week Mon-Sun) Logic
        $weekStart = now()->startOfWeek();
        $weekEnd = now()->endOfWeek();

        $currentWeekDays = [];
        $tempDay = $weekStart->copy();
        while ($tempDay->lte($weekEnd)) {
            $currentWeekDays[] = [
                'date' => $tempDay->toDateString(),
                'day_name' => $tempDay->format('l'),
                'short_day' => $tempDay->format('D'),
                'formatted' => $tempDay->format('d M'),
                'is_today' => $tempDay->isToday(),
                'is_future' => $tempDay->isFuture() && !$tempDay->isToday(),
            ];
            $tempDay->addDay();
        }

        $canViewTeam = $this->currentUserService->hasPermission('attendance.view');
        $targetScreenshotUserId = request('screenshot_user_id') ?: (request('employee_id') ?: $user->id);
        if (!$canViewTeam) {
            $targetScreenshotUserId = $user->id;
        }

        $selectedScreenshotDate = request('screenshot_date', now()->toDateString());
        $selectedScreenshotUser = \App\Models\User::find($targetScreenshotUserId) ?? $user;

        $weekScreenshotsCount = \App\Models\AttendanceScreenshot::where('organization_id', $org->id)
            ->where('user_id', $targetScreenshotUserId)
            ->whereBetween('captured_at', [$weekStart->copy()->startOfDay(), $weekEnd->copy()->endOfDay()])
            ->selectRaw('DATE(captured_at) as log_date, count(*) as total')
            ->groupBy('log_date')
            ->pluck('total', 'log_date')
            ->toArray();

        $initialScreenshots = \App\Models\AttendanceScreenshot::where('organization_id', $org->id)
            ->where('user_id', $targetScreenshotUserId)
            ->whereDate('captured_at', $selectedScreenshotDate)
            ->with(['project', 'task'])
            ->orderBy('captured_at', 'asc')
            ->get();

        $initialScreenshotsFormatted = $initialScreenshots->map(function ($s) {
            return [
                'id' => $s->id,
                'url' => $s->url,
                'captured_at_formatted' => $s->captured_at ? $s->captured_at->format('h:i A') : '-',
                'captured_at_full' => $s->captured_at ? $s->captured_at->format('d M Y, h:i A') : '-',
                'project_name' => $s->project?->name ?: 'Miscellaneous',
                'task_title' => $s->task?->title ?: 'Active Work',
                'user_id' => $s->user_id,
            ];
        })->values();

        return view('attendance.index', compact(
            'projects',
            'activeAttendance',
            'activeLeave',
            'groupedLogs',
            'dailyTotals',
            'myWeeklyGroupedLogs',
            'teamLogs',
            'organizationUsers',
            'teamWeeklyStats',
            'myPersonalStats',
            'currentWeekDays',
            'selectedScreenshotUser',
            'selectedScreenshotDate',
            'weekScreenshotsCount',
            'initialScreenshots',
            'initialScreenshotsFormatted'
        ));
    }

    public static function calculateDistanceInMeters($lat1, $lon1, $lat2, $lon2): float
    {
        $earthRadius = 6371000; // in meters
        $latFrom = deg2rad((float)$lat1);
        $lonFrom = deg2rad((float)$lon1);
        $latTo = deg2rad((float)$lat2);
        $lonTo = deg2rad((float)$lon2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
        return $angle * $earthRadius;
    }

    public function clockIn(Request $request)
    {
        $request->validate([
            'project_id' => 'nullable|exists:projects,id',
            'task_title' => 'nullable|string|max:255',
            'task_id' => 'nullable|exists:tasks,id',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        $user = $this->currentUserService->getUser();
        $org = $this->currentUserService->getOrganization();

        // 0. Approved Leave Check (Full Day, First Half, Second Half)
        $leaveBlockInfo = Leave::getActiveLeaveForUser($user->id);
        if ($leaveBlockInfo && !empty($leaveBlockInfo['blocked'])) {
            $errorMsg = "Clock-in blocked: " . $leaveBlockInfo['reason'] . " Time tracking is not permitted during your approved leave.";
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $errorMsg, 'on_leave' => true], 403);
            }
            return redirect()->back()->with('error', $errorMsg);
        }

        // 1. IP Network Restriction Validation
        if ($org->enforce_ip_restriction && !empty($org->allowed_ips)) {
            $clientIp = $request->ip();
            $allowedList = array_filter(array_map('trim', explode(',', $org->allowed_ips)));
            if (!in_array($clientIp, $allowedList) && !in_array('127.0.0.1', $allowedList) && $clientIp !== '127.0.0.1' && $clientIp !== '::1') {
                $errorMsg = "Clock-in blocked: You are not connected to the designated office network. (Your IP: {$clientIp})";
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json(['success' => false, 'message' => $errorMsg], 422);
                }
                return redirect()->back()->with('error', $errorMsg);
            }
        }

        // 2. GPS Geofencing Validation
        $lat = $request->input('latitude');
        $lon = $request->input('longitude');

        if ($org->enforce_geofence && $org->geofence_latitude && $org->geofence_longitude) {
            if (!$lat || !$lon) {
                $errorMsg = "Clock-in blocked: GPS Location access is mandatory to verify you are at the office.";
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json(['success' => false, 'message' => $errorMsg], 422);
                }
                return redirect()->back()->with('error', $errorMsg);
            }

            $distance = self::calculateDistanceInMeters(
                $lat, $lon,
                $org->geofence_latitude, $org->geofence_longitude
            );

            $allowedRadius = $org->geofence_radius_meters ?: 200;
            if ($distance > $allowedRadius) {
                $distRounded = round($distance);
                $errorMsg = "Clock-in blocked: You are outside the office boundary ({$distRounded}m away, allowed radius: {$allowedRadius}m).";
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json(['success' => false, 'message' => $errorMsg], 422);
                }
                return redirect()->back()->with('error', $errorMsg);
            }
        }

        $projectId = $request->project_id;
        $taskId = $request->task_id;
        $taskTitle = $request->task_title;

        if (!$projectId && $taskTitle) {
            $miscProject = Project::firstOrCreate(
                ['organization_id' => $org->id, 'name' => 'Miscellaneous'],
                [
                    'description' => 'General and miscellaneous tracking.',
                    'status' => 'active',
                    'start_date' => now(),
                ]
            );
            $projectId = $miscProject->id;
            $miscProject->users()->syncWithoutDetaching([$user->id]);
        }

        if ($projectId) {
            $project = Project::where('organization_id', $org->id)
                ->where('id', $projectId)
                ->first();

            if (!$project) {
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json(['success' => false, 'message' => 'Invalid project selected.'], 422);
                }
                return redirect()->back()->with('error', 'Invalid project selected.');
            }

            if ($taskTitle && !$taskId) {
                $task = Task::firstOrCreate(
                    ['project_id' => $projectId, 'title' => $taskTitle],
                    [
                        'status' => 'in_progress',
                        'priority' => 'medium',
                        'user_id' => $user->id,
                    ]
                );
                $taskId = $task->id;
            }
        }

        // Auto-close any active timer safely
        $activeSession = Attendance::where('user_id', $user->id)
            ->whereNull('clock_out_at')
            ->first();

        if ($activeSession) {
            $now = now();
            $closeTime = $now < $activeSession->clock_in_at ? $activeSession->clock_in_at->copy()->addSeconds(1) : $now;
            $activeSession->update(['clock_out_at' => $closeTime]);
        }

        $newAttendance = Attendance::create([
            'user_id' => $user->id,
            'organization_id' => $org->id,
            'project_id' => $projectId,
            'task_id' => $taskId,
            'date' => now()->toDateString(),
            'clock_in_at' => now(),
            'clock_in_ip' => $request->ip(),
            'clock_in_latitude' => $lat,
            'clock_in_longitude' => $lon,
        ]);

        $newAttendance->load(['project', 'task']);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Timer started.',
                'attendance' => $newAttendance,
            ]);
        }

        return redirect()->back()->with('success', 'Timer started.');
    }

    public function clockOut(Request $request)
    {
        $user = $this->currentUserService->getUser();
        $org = $this->currentUserService->getOrganization();

        $activeAttendance = Attendance::where('user_id', $user->id)
            ->whereNull('clock_out_at')
            ->with(['project', 'task'])
            ->first();

        if (!$activeAttendance) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'No active timer found.'], 400);
            }
            return redirect()->back()->with('error', 'No active timer found.');
        }

        // If request provides project_id or task_title, update the session before validation
        $projectId = $request->input('project_id') ?: $activeAttendance->project_id;
        $taskTitle = trim((string)($request->input('task_title') ?: ($activeAttendance->task?->title ?? '')));
        $taskId = $request->input('task_id') ?: $activeAttendance->task_id;

        if ($projectId && $taskTitle && !$taskId) {
            $task = Task::firstOrCreate(
                ['project_id' => $projectId, 'title' => $taskTitle],
                [
                    'status' => 'in_progress',
                    'priority' => 'medium',
                    'user_id' => $user->id,
                ]
            );
            $taskId = $task->id;
        }

        if ($projectId != $activeAttendance->project_id || $taskId != $activeAttendance->task_id) {
            $activeAttendance->update([
                'project_id' => $projectId,
                'task_id' => $taskId,
            ]);
            $activeAttendance->load(['project', 'task']);
        }

        // VALIDATION: Must have both project and task/description to stop tracker
        $hasProject = !empty($activeAttendance->project_id);
        $hasTask = !empty($activeAttendance->task_id) || !empty(trim((string)($activeAttendance->task?->title ?? '')));

        if (!$hasProject || !$hasTask) {
            $errorMsg = 'Please add a task description and select a project before stopping the tracker.';
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMsg,
                    'missing_details' => true,
                    'missing_project' => !$hasProject,
                    'missing_task' => !$hasTask,
                ], 422);
            }
            return redirect()->back()->with('error', $errorMsg);
        }

        $clockOutTime = now();
        if ($clockOutTime->diffInSeconds($activeAttendance->clock_in_at) < 1) {
            $clockOutTime = $activeAttendance->clock_in_at->copy()->addSeconds(1);
        }

        $activeAttendance->update([
            'clock_out_at' => $clockOutTime,
            'clock_out_ip' => $request->ip(),
            'clock_out_latitude' => $request->input('latitude'),
            'clock_out_longitude' => $request->input('longitude'),
        ]);

        $activeAttendance->load(['project', 'task']);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Timer stopped successfully.',
                'attendance' => $activeAttendance,
            ]);
        }

        return redirect()->back()->with('success', 'Timer stopped successfully.');
    }

    public function switchProject(Request $request)
    {
        $request->validate([
            'project_id' => 'nullable|exists:projects,id',
            'task_title' => 'nullable|string|max:255',
            'task_id' => 'nullable|exists:tasks,id',
        ]);

        $user = $this->currentUserService->getUser();
        $org = $this->currentUserService->getOrganization();

        $projectId = $request->project_id;
        $taskId = $request->task_id;
        $taskTitle = $request->task_title;

        if (!$projectId && $taskTitle) {
            $miscProject = Project::firstOrCreate(
                ['organization_id' => $org->id, 'name' => 'Miscellaneous'],
                [
                    'description' => 'General and miscellaneous tracking.',
                    'status' => 'active',
                    'start_date' => now(),
                ]
            );
            $projectId = $miscProject->id;
            $miscProject->users()->syncWithoutDetaching([$user->id]);
        }

        if ($projectId) {
            $project = Project::where('organization_id', $org->id)
                ->where('id', $projectId)
                ->first();

            if (!$project) {
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json(['success' => false, 'message' => 'Invalid project selected.'], 422);
                }
                return redirect()->back()->with('error', 'Invalid project selected.');
            }

            if ($taskTitle && !$taskId) {
                $task = Task::firstOrCreate(
                    ['project_id' => $projectId, 'title' => $taskTitle],
                    [
                        'status' => 'in_progress',
                        'priority' => 'medium',
                        'user_id' => $user->id,
                    ]
                );
                $taskId = $task->id;
            }
        }

        $activeSession = Attendance::where('user_id', $user->id)
            ->whereNull('clock_out_at')
            ->first();

        if ($activeSession) {
            $activeSession->update([
                'project_id' => $projectId,
                'task_id' => $taskId,
            ]);
            $activeSession->load(['project', 'task']);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Timer updated.',
                    'attendance' => $activeSession,
                ]);
            }

            return redirect()->back()->with('success', 'Timer updated.');
        }

        // Approved Leave Check (Full Day, First Half, Second Half)
        $leaveBlockInfo = Leave::getActiveLeaveForUser($user->id);
        if ($leaveBlockInfo && !empty($leaveBlockInfo['blocked'])) {
            $errorMsg = "Clock-in blocked: " . $leaveBlockInfo['reason'] . " Time tracking is not permitted during your approved leave.";
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $errorMsg, 'on_leave' => true], 403);
            }
            return redirect()->back()->with('error', $errorMsg);
        }

        $newAttendance = Attendance::create([
            'user_id' => $user->id,
            'organization_id' => $org->id,
            'project_id' => $projectId,
            'task_id' => $taskId,
            'date' => now()->toDateString(),
            'clock_in_at' => now(),
        ]);

        $newAttendance->load(['project', 'task']);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Timer started.',
                'attendance' => $newAttendance,
            ]);
        }

        return redirect()->back()->with('success', 'Timer started.');
    }

    public function updateLog(Request $request, Attendance $attendance)
    {
        $user = $this->currentUserService->getUser();
        if ($attendance->user_id !== $user->id && !$this->currentUserService->hasPermission('attendance.edit')) {
            abort(403);
        }

        // Restrict edits to current week logs only
        $logDate = $attendance->clock_in_at ?? ($attendance->date ? \Carbon\Carbon::parse($attendance->date) : null);
        if ($logDate && !$logDate->between(now()->startOfWeek(), now()->endOfWeek())) {
            return redirect()->back()->with('error', 'Only time entries from the current week can be edited.');
        }

        $request->validate([
            'project_id' => 'nullable|exists:projects,id',
            'task_title' => 'nullable|string|max:255',
        ]);

        $org = $this->currentUserService->getOrganization();
        $projectId = $request->project_id;
        $taskId = null;

        if ($request->filled('task_title')) {
            if (!$projectId) {
                $miscProject = Project::firstOrCreate(
                    ['organization_id' => $org->id, 'name' => 'Miscellaneous'],
                    [
                        'description' => 'General and miscellaneous tracking.',
                        'status' => 'active',
                        'start_date' => now(),
                    ]
                );
                $projectId = $miscProject->id;
                $miscProject->users()->syncWithoutDetaching([$attendance->user_id]);
            }

            $task = Task::firstOrCreate(
                ['project_id' => $projectId, 'title' => $request->task_title],
                [
                    'status' => 'in_progress',
                    'priority' => 'medium',
                    'user_id' => $attendance->user_id,
                ]
            );
            $taskId = $task->id;
        }

        $attendance->update([
            'project_id' => $projectId,
            'task_id' => $taskId,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Time log updated successfully.',
                'attendance' => $attendance->fresh(['project', 'task']),
            ]);
        }

        return redirect()->back()->with('success', 'Time log updated successfully.');
    }

    public function destroyLog(Attendance $attendance)
    {
        $user = $this->currentUserService->getUser();
        if ($attendance->user_id !== $user->id && !$this->currentUserService->hasPermission('attendance.edit')) {
            abort(403);
        }

        $attendance->delete();

        return redirect()->back()->with('success', 'Time log deleted successfully.');
    }

    public function showUserLogs(\App\Models\User $user)
    {
        $org = $this->currentUserService->getOrganization();
        if ($user->organization_id !== $org->id) {
            abort(403);
        }

        if (!$this->currentUserService->hasPermission('attendance.view')) {
            abort(403);
        }

        $user->load(['role', 'department']);

        $logs = Attendance::where('user_id', $user->id)
            ->whereNotNull('clock_out_at')
            ->with(['project', 'task'])
            ->orderByDesc('clock_in_at')
            ->get();

        $weeklyQuotaHours = 40.0;

        // 1. WEEKLY STATS (Grouped by Week - 5 Working Days Mon-Fri)
        $weeklyGroups = $logs->groupBy(function ($log) {
            $date = $log->clock_in_at ?: $log->date;
            $startOfWeek = $date->copy()->startOfWeek();
            $endOfWeek = $date->copy()->endOfWeek();
            return $startOfWeek->format('Y-m-d') . '|' . $startOfWeek->format('d M, Y') . ' - ' . $endOfWeek->format('d M, Y');
        });

        $weeklyStats = [];
        foreach ($weeklyGroups as $key => $wLogs) {
            [$sortKey, $label] = explode('|', $key, 2);
            $wSecs = 0;
            $wDates = [];
            foreach ($wLogs as $log) {
                if ($log->clock_out_at && $log->clock_out_at >= $log->clock_in_at) {
                    $wSecs += $log->clock_in_at->diffInSeconds($log->clock_out_at);
                }
                $d = $log->clock_in_at ? $log->clock_in_at->toDateString() : $log->date->toDateString();
                $wDates[$d] = true;
            }
            $wHrs = floor($wSecs / 3600);
            $wMins = floor(($wSecs % 3600) / 60);
            $wSecsRem = $wSecs % 60;
            $wFormatted = sprintf('%02d:%02d:%02d', $wHrs, $wMins, $wSecsRem);
            $wHours = round($wSecs / 3600, 2);
            $wPercent = min(100, round(($wHours / $weeklyQuotaHours) * 100));

            $weeklyStats[] = [
                'sort_key' => $sortKey,
                'label' => 'Week (' . $label . ')',
                'hours' => $wHours,
                'formatted_total' => $wFormatted,
                'quota' => $weeklyQuotaHours,
                'percent' => $wPercent,
                'logs_count' => $wLogs->count(),
                'days_worked' => count($wDates),
                'logs' => $wLogs,
            ];
        }

        // 2. MONTHLY STATS (Grouped by Month)
        $monthlyGroups = $logs->groupBy(function ($log) {
            $date = $log->clock_in_at ?: $log->date;
            return $date->format('F Y');
        });

        $monthlyStats = [];
        foreach ($monthlyGroups as $monthLabel => $mLogs) {
            $mSecs = 0;
            $uniqueDates = [];
            foreach ($mLogs as $log) {
                if ($log->clock_out_at && $log->clock_out_at >= $log->clock_in_at) {
                    $mSecs += $log->clock_in_at->diffInSeconds($log->clock_out_at);
                }
                $d = $log->clock_in_at ? $log->clock_in_at->toDateString() : $log->date->toDateString();
                $uniqueDates[$d] = true;
            }
            $mHrs = floor($mSecs / 3600);
            $mMins = floor(($mSecs % 3600) / 60);
            $mSecsRem = $mSecs % 60;
            $mFormatted = sprintf('%02d:%02d:%02d', $mHrs, $mMins, $mSecsRem);
            $mHours = round($mSecs / 3600, 2);
            $monthlyQuota = 160.0; // standard 4 weeks = 160 hours target
            $mPercent = min(100, round(($mHours / $monthlyQuota) * 100));

            $monthlyStats[] = [
                'month' => $monthLabel,
                'hours' => $mHours,
                'formatted_total' => $mFormatted,
                'quota' => $monthlyQuota,
                'percent' => $mPercent,
                'logs_count' => $mLogs->count(),
                'days_worked' => count($uniqueDates),
                'logs' => $mLogs,
            ];
        }

        // Current week calculation for header card
        $wStart = now()->startOfWeek();
        $wEnd = now()->endOfWeek();
        $currentWeekLogs = $logs->filter(function($l) use ($wStart, $wEnd) {
            $d = $l->clock_in_at ?: $l->date;
            return $d->between($wStart, $wEnd);
        });

        $cwSecs = 0;
        foreach ($currentWeekLogs as $l) {
            if ($l->clock_out_at && $l->clock_out_at >= $l->clock_in_at) {
                $cwSecs += $l->clock_in_at->diffInSeconds($l->clock_out_at);
            }
        }
        $cwHrs = floor($cwSecs / 3600);
        $cwMins = floor(($cwSecs % 3600) / 60);
        $cwSecsRem = $cwSecs % 60;
        $currentWeekFormatted = sprintf('%02d:%02d:%02d', $cwHrs, $cwMins, $cwSecsRem);
        $currentWeekHours = round($cwSecs / 3600, 2);
        $currentWeekPercent = min(100, round(($currentWeekHours / $weeklyQuotaHours) * 100));

        // Screenshots Day-Wise (Current Week Mon-Sun) for this user
        $currentWeekDays = [];
        $tempDay = $wStart->copy();
        while ($tempDay->lte($wEnd)) {
            $currentWeekDays[] = [
                'date' => $tempDay->toDateString(),
                'day_name' => $tempDay->format('l'),
                'short_day' => $tempDay->format('D'),
                'formatted' => $tempDay->format('d M'),
                'is_today' => $tempDay->isToday(),
                'is_future' => $tempDay->isFuture() && !$tempDay->isToday(),
            ];
            $tempDay->addDay();
        }

        $selectedScreenshotDate = request('screenshot_date', now()->toDateString());

        $weekScreenshotsCount = \App\Models\AttendanceScreenshot::where('organization_id', $org->id)
            ->where('user_id', $user->id)
            ->whereBetween('captured_at', [$wStart->copy()->startOfDay(), $wEnd->copy()->endOfDay()])
            ->selectRaw('DATE(captured_at) as log_date, count(*) as total')
            ->groupBy('log_date')
            ->pluck('total', 'log_date')
            ->toArray();

        $initialScreenshots = \App\Models\AttendanceScreenshot::where('organization_id', $org->id)
            ->where('user_id', $user->id)
            ->whereDate('captured_at', $selectedScreenshotDate)
            ->with(['project', 'task'])
            ->orderBy('captured_at', 'asc')
            ->get();

        $initialScreenshotsFormatted = $initialScreenshots->map(function ($s) {
            return [
                'id' => $s->id,
                'url' => $s->url,
                'captured_at_formatted' => $s->captured_at ? $s->captured_at->format('h:i A') : '-',
                'captured_at_full' => $s->captured_at ? $s->captured_at->format('d M Y, h:i A') : '-',
                'project_name' => $s->project?->name ?: 'Miscellaneous',
                'task_title' => $s->task?->title ?: 'Active Work',
                'user_id' => $s->user_id,
            ];
        })->values();

        return view('attendance.user_logs', compact(
            'user',
            'weeklyStats',
            'monthlyStats',
            'currentWeekHours',
            'currentWeekFormatted',
            'currentWeekPercent',
            'weeklyQuotaHours',
            'currentWeekDays',
            'selectedScreenshotDate',
            'weekScreenshotsCount',
            'initialScreenshots',
            'initialScreenshotsFormatted'
        ));
    }

    public function exportCsv(Request $request)
    {
        if (!$this->currentUserService->hasPermission('attendance.view')) {
            abort(403, 'Unauthorized action.');
        }

        $org = $this->currentUserService->getOrganization();
        $currentUser = $this->currentUserService->getUser();

        $query = Attendance::where('organization_id', $org->id)
            ->with(['user.department', 'project', 'task']);

        if ($request->filled('employee_id')) {
            $query->where('user_id', $request->employee_id);
        }

        // Date filters
        if ($request->filled('log_date')) {
            $query->where(function($q) use ($request) {
                $q->whereDate('date', $request->log_date)
                  ->orWhereDate('clock_in_at', $request->log_date);
            });
        }
        if ($request->filled('start_date')) {
            $query->where(function($q) use ($request) {
                $q->whereDate('date', '>=', $request->start_date)
                  ->orWhereDate('clock_in_at', '>=', $request->start_date);
            });
        }
        if ($request->filled('end_date')) {
            $query->where(function($q) use ($request) {
                $q->whereDate('date', '<=', $request->end_date)
                  ->orWhereDate('clock_in_at', '<=', $request->end_date);
            });
        }

        $logs = $query->orderByDesc('clock_in_at')->get();

        $filename = "attendance_report_" . date('Y-m-d_H-i-s') . ".csv";

        $headers = [
            "Content-type" => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename={$filename}",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function () use ($logs) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, [
                'Log ID',
                'Employee Name',
                'Email',
                'Department',
                'Date',
                'Clock In Time',
                'Clock Out Time',
                'Duration (HH:MM:SS)',
                'Duration (Hours)',
                'Project',
                'Task',
                'Status'
            ]);

            foreach ($logs as $log) {
                $logDate = $log->clock_in_at ? $log->clock_in_at->format('Y-m-d') : ($log->date ? $log->date->format('Y-m-d') : '-');
                $clockInStr = $log->clock_in_at ? $log->clock_in_at->format('Y-m-d h:i:s A') : '-';
                $clockOutStr = $log->clock_out_at ? $log->clock_out_at->format('Y-m-d h:i:s A') : 'Still Active';
                
                if ($log->clock_out_at && $log->clock_in_at && $log->clock_out_at >= $log->clock_in_at) {
                    $diffInSecs = $log->clock_in_at->diffInSeconds($log->clock_out_at);
                    $hrs = floor($diffInSecs / 3600);
                    $mins = floor(($diffInSecs % 3600) / 60);
                    $secs = $diffInSecs % 60;
                    $durationFormatted = sprintf('%02d:%02d:%02d', $hrs, $mins, $secs);
                    $durationHours = round($diffInSecs / 3600, 2);
                    $status = 'Completed';
                } else {
                    $durationFormatted = 'In Progress';
                    $durationHours = '0.00';
                    $status = 'Active (Clocked In)';
                }

                fputcsv($file, [
                    $log->id,
                    $log->user ? $log->user->full_name : 'N/A',
                    $log->user ? $log->user->email : 'N/A',
                    $log->user?->department ? $log->user->department->name : 'N/A',
                    $logDate,
                    $clockInStr,
                    $clockOutStr,
                    $durationFormatted,
                    $durationHours,
                    $log->project ? $log->project->name : 'General',
                    $log->task ? $log->task->title : ($log->task_title ?: '-'),
                    $status
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
