<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceScreenshot;
use App\Models\User;
use App\Services\CurrentUserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AttendanceScreenshotController extends Controller
{
    public function __construct(
        protected CurrentUserService $currentUserService
    ) {}

    /**
     * Store a newly captured screenshot from the live tracker or desktop companion
     */
    public function store(Request $request)
    {
        $user = $this->currentUserService->getUser();
        if (!$user && ($request->filled('user_id') || $request->hasHeader('X-User-Id'))) {
            $userId = $request->input('user_id') ?: $request->header('X-User-Id');
            $user = User::with('organization')->find($userId);
        }

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $org = $user->organization;
        if (!$org) {
            return response()->json(['success' => false, 'message' => 'Organization not found for user'], 404);
        }

        // Find active attendance session if any
        $activeAttendance = Attendance::where('user_id', $user->id)
            ->whereNull('clock_out_at')
            ->first();

        $projectId = $request->input('project_id') ?: ($activeAttendance ? $activeAttendance->project_id : null);
        $taskId = $request->input('task_id') ?: ($activeAttendance ? $activeAttendance->task_id : null);

        $screenshotRelativePath = null;

        if ($request->hasFile('file') || $request->hasFile('screenshot')) {
            $file = $request->file('file') ?: $request->file('screenshot');
            $directory = "screenshots/org_{$org->id}/user_{$user->id}";
            $fileName = now()->format('Y-m-d_His') . '_' . Str::random(8) . '.jpg';
            $screenshotRelativePath = $file->storeAs($directory, $fileName, 'public');
        } elseif ($request->filled('image_data') || $request->filled('screenshot')) {
            $base64String = $request->input('image_data') ?: $request->input('screenshot');
            if (preg_match('/^data:image\/(\w+);base64,/', $base64String)) {
                $base64String = substr($base64String, strpos($base64String, ',') + 1);
            }

            $decodedImage = base64_decode($base64String);
            if (!$decodedImage) {
                return response()->json(['success' => false, 'message' => 'Invalid image data'], 422);
            }

            $directory = "screenshots/org_{$org->id}/user_{$user->id}";
            $fileName = now()->format('Y-m-d_His') . '_' . Str::random(8) . '.jpg';
            $screenshotRelativePath = "{$directory}/{$fileName}";

            Storage::disk('public')->put($screenshotRelativePath, $decodedImage);
        } else {
            return response()->json(['success' => false, 'message' => 'No screenshot data provided'], 422);
        }

        $screenshot = AttendanceScreenshot::create([
            'organization_id' => $org->id,
            'user_id' => $user->id,
            'attendance_id' => $activeAttendance?->id,
            'project_id' => $projectId,
            'task_id' => $taskId,
            'screenshot_path' => $screenshotRelativePath,
            'captured_at' => now(),
        ]);

        // Opportunistic lazy pruning (auto-delete screenshots older than 1 month / 30 days)
        if (mt_rand(1, 10) === 1) {
            $this->pruneOldScreenshots();
        }

        return response()->json([
            'success' => true,
            'message' => 'Screenshot captured successfully',
            'data' => [
                'id' => $screenshot->id,
                'url' => $screenshot->url,
                'captured_at' => $screenshot->captured_at->format('Y-m-d H:i:s'),
            ]
        ]);
    }

    /**
     * Get screenshots for a specific day and user
     */
    public function getScreenshots(Request $request)
    {
        $user = $this->currentUserService->getUser();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $org = $user->organization;
        $canViewTeam = $this->currentUserService->hasPermission('attendance.view');

        $targetUserId = $request->input('user_id');
        if (!$canViewTeam || !$targetUserId) {
            $targetUserId = $user->id;
        }

        $date = $request->input('date', now()->toDateString());

        $screenshots = AttendanceScreenshot::where('organization_id', $org->id)
            ->where('user_id', $targetUserId)
            ->whereDate('captured_at', $date)
            ->with(['project', 'task'])
            ->orderBy('captured_at', 'asc')
            ->get()
            ->map(function ($s) {
                return [
                    'id' => $s->id,
                    'url' => $s->url,
                    'captured_at_formatted' => $s->captured_at ? $s->captured_at->format('h:i A') : '-',
                    'captured_at_full' => $s->captured_at ? $s->captured_at->format('d M Y, h:i A') : '-',
                    'project_name' => $s->project?->name ?: 'Miscellaneous',
                    'task_title' => $s->task?->title ?: 'Active Work',
                ];
            });

        return response()->json([
            'success' => true,
            'date' => $date,
            'user_id' => (int)$targetUserId,
            'screenshots' => $screenshots,
        ]);
    }

    /**
     * Delete a screenshot
     */
    public function destroy(AttendanceScreenshot $screenshot)
    {
        $user = $this->currentUserService->getUser();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Invalid'], 401);
        }

        $canDelete = ($screenshot->user_id === $user->id) || $this->currentUserService->hasPermission('attendance.manage');

        if (!$canDelete) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        if ($screenshot->screenshot_path && Storage::disk('public')->exists($screenshot->screenshot_path)) {
            Storage::disk('public')->delete($screenshot->screenshot_path);
        }

        $screenshot->delete();

        return response()->json(['success' => true, 'message' => 'Screenshot deleted successfully']);
    }

    /**
     * Prune screenshots older than 7 days (previous week auto-delete)
     */
    protected function pruneOldScreenshots(int $days = 7): int
    {
        $cutoff = now()->subDays($days);
        $oldScreenshots = AttendanceScreenshot::where('captured_at', '<', $cutoff)->get();
        $deleted = 0;

        foreach ($oldScreenshots as $s) {
            if ($s->screenshot_path && Storage::disk('public')->exists($s->screenshot_path)) {
                Storage::disk('public')->delete($s->screenshot_path);
            }
            $s->delete();
            $deleted++;
        }

        return $deleted;
    }

    /**
     * Check if a user has an active timer running (for Desktop Companion Tracker polling)
     */
    public function getActiveSession(Request $request)
    {
        $user = $this->currentUserService->getUser();
        if (!$user && ($request->filled('user_id') || $request->hasHeader('X-User-Id'))) {
            $userId = $request->input('user_id') ?: $request->header('X-User-Id');
            $user = User::with('organization')->find($userId);
        }

        if (!$user) {
            return response()->json(['active' => false, 'message' => 'User not identified'], 401);
        }

        $activeAttendance = Attendance::where('user_id', $user->id)
            ->whereNull('clock_out_at')
            ->with(['project', 'task'])
            ->first();

        if (!$activeAttendance) {
            return response()->json([
                'active' => false,
                'user_id' => $user->id,
                'user_name' => $user->full_name,
            ]);
        }

        return response()->json([
            'active' => true,
            'attendance_id' => $activeAttendance->id,
            'user_id' => $user->id,
            'user_name' => $user->full_name,
            'project_id' => $activeAttendance->project_id,
            'project_name' => $activeAttendance->project?->name,
            'task_id' => $activeAttendance->task_id,
            'task_title' => $activeAttendance->task?->title,
            'clock_in_at' => $activeAttendance->clock_in_at?->toIso8601String(),
            'interval_seconds' => 120, // 2 minutes screenshot interval
        ]);
    }

    /**
     * Download the Desktop Tracker ZIP package
     */
    public function downloadTrackerPackage()
    {
        $zipPath = public_path('downloads/SyncWithTeam-Tracker.zip');
        if (!file_exists($zipPath)) {
            // If not found, return back with error
            return response()->json(['error' => 'Tracker package not available'], 404);
        }

        return response()->download($zipPath, 'SyncWithTeam-Tracker.zip', [
            'Content-Type' => 'application/zip',
        ]);
    }
}