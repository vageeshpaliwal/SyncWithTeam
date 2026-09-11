<?php

namespace App\Http\Controllers;

use App\Models\Bug;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskComment;
use App\Services\CurrentUserService;
use Illuminate\Http\Request;

class BugController extends Controller
{
    public function __construct(
        protected CurrentUserService $currentUserService
    ) {}

    public function store(Request $request, Project $project)
    {
        $this->ensureBelongsToOrganization($project);
        $currentUser = $this->currentUserService->getUser();
        $org = $this->currentUserService->getOrganization();

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'severity' => 'required|in:low,medium,high,critical',
            'task_id' => 'nullable|exists:tasks,id',
            'screenshot' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:5120',
        ]);

        $screenshotPath = null;
        if ($request->hasFile('screenshot')) {
            $screenshotPath = $request->file('screenshot')->store('bugs/screenshots', 'public');
        }

        $lastBug = Bug::where('organization_id', $org->id)->latest('id')->first();
        $nextNum = $lastBug ? ($lastBug->id + 101) : 101;
        $bugNumber = 'BUG-' . $nextNum;

        $bugData = [
            'organization_id' => $org->id,
            'project_id' => $project->id,
            'task_id' => $request->task_id,
            'reported_by' => $currentUser->id,
            'bug_number' => $bugNumber,
            'title' => $request->title,
            'description' => $request->description,
            'severity' => $request->severity,
            'status' => 'open',
        ];

        if (\Illuminate\Support\Facades\Schema::hasColumn('bugs', 'screenshot_path')) {
            $bugData['screenshot_path'] = $screenshotPath;
        }

        $bug = Bug::create($bugData);

        // If bug is reported on a specific task, reopen task & add comment
        if ($request->task_id) {
            $task = Task::find($request->task_id);
            if ($task) {
                $task->update(['status' => 'reopened']);

                TaskComment::create([
                    'organization_id' => $org->id,
                    'task_id' => $task->id,
                    'user_id' => $currentUser->id,
                    'comment' => "🔴 Task Reopened & Bug Reported [{$bugNumber}]: {$request->title}\nDetails: {$request->description}"
                ]);

                // Notify assigned developer
                if ($task->user_id && $task->user_id !== $currentUser->id) {
                    \App\Models\Notification::create([
                        'organization_id' => $org->id,
                        'user_id' => $task->user_id,
                        'title' => "Task Reopened [{$bugNumber}]",
                        'message' => "{$currentUser->full_name} reported bug {$bugNumber} on task '{$task->title}'.",
                        'type' => 'bug_reported',
                        'link' => route('projects.show', $project->id),
                    ]);
                }
            }
        }

        return redirect()->route('projects.show', $project->id)->with('success', "Bug {$bugNumber} reported successfully.");
    }

    public function show(Bug $bug)
    {
        $this->ensureBelongsToOrganization($bug->project);
        $bug->load(['project', 'task', 'reporter']);

        return view('bugs.show', compact('bug'));
    }

    public function updateStatus(Request $request, Bug $bug)
    {
        $this->ensureBelongsToOrganization($bug->project);

        $request->validate([
            'status' => 'required|in:open,in_progress,resolved,closed',
        ]);

        $bug->update(['status' => $request->status]);

        // If bug resolved and linked to task, set task to done if requested
        if (in_array($request->status, ['resolved', 'closed']) && $bug->task) {
            if ($request->boolean('resolve_task')) {
                $bug->task->update(['status' => 'done']);
            }
        }

        return redirect()->route('projects.show', $bug->project_id)->with('success', "Bug {$bug->bug_number} status updated to " . ucfirst(str_replace('_', ' ', $bug->status)) . '.');
    }

    public function destroy(Bug $bug)
    {
        $this->ensureBelongsToOrganization($bug->project);
        $projectId = $bug->project_id;
        $bug->delete();

        return redirect()->route('projects.show', $projectId)->with('success', 'Bug entry deleted successfully.');
    }

    protected function ensureBelongsToOrganization(Project $project): void
    {
        if ($project->organization_id !== $this->currentUserService->getOrganization()->id) {
            abort(404);
        }
    }
}
