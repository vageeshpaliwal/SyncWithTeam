<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Project;
use App\Models\Task;
use App\Services\CurrentUserService;

class TaskController extends Controller
{
    public function __construct(
        protected CurrentUserService $currentUserService
    ) {}

    public function store(StoreTaskRequest $request, Project $project)
    {
        $this->ensureProjectBelongsToOrganization($project);
        $user = $this->currentUserService->getUser();

        // Check if user is assigned to project (or has projects.manage)
        if (!$this->currentUserService->hasPermission('projects.manage')) {
            if (!$project->users()->where('users.id', $user->id)->exists()) {
                abort(403, 'You are not assigned to this project.');
            }
        }

        $validated = $request->validated();

        if (!empty($validated['user_id'])) {
            if (!$project->users()->where('users.id', $validated['user_id'])->exists()) {
                return redirect()->back()->with('error', 'The assigned user is not part of this project.');
            }
        }

        $task = Task::create([
            'project_id' => $project->id,
            'user_id' => $validated['user_id'] ?? null,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'],
            'priority' => $validated['priority'],
            'due_date' => $validated['due_date'] ?? null,
        ]);

        if (!empty($validated['user_id'])) {
            \App\Models\Notification::create([
                'organization_id' => $project->organization_id,
                'user_id' => $validated['user_id'],
                'title' => 'New Task Assigned',
                'message' => "You have been assigned task '{$task->title}' in project '{$project->name}'.",
                'type' => 'task_assigned',
                'link' => route('projects.show', $project->id),
            ]);
        }

        return redirect()->route('projects.show', $project->id)->with('success', 'Task added successfully.');
    }

    public function update(UpdateTaskRequest $request, Task $task)
    {
        $this->ensureProjectBelongsToOrganization($task->project);
        $user = $this->currentUserService->getUser();

        if (!$this->currentUserService->hasPermission('projects.manage')) {
            if (!$task->project->users()->where('users.id', $user->id)->exists()) {
                abort(403, 'You are not assigned to this project.');
            }
        }

        if ($user->role?->code === 'employee' && $task->user_id !== $user->id) {
            abort(403, 'Employees can only modify tasks assigned to themselves.');
        }

        $validated = $request->validated();

        if (!empty($validated['user_id'])) {
            if (!$task->project->users()->where('users.id', $validated['user_id'])->exists()) {
                return redirect()->back()->with('error', 'The assigned user is not part of this project.');
            }
        }

        $oldAssignedId = $task->user_id;

        $task->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'user_id' => $validated['user_id'] ?? null,
            'status' => $validated['status'],
            'priority' => $validated['priority'],
            'due_date' => $validated['due_date'] ?? null,
        ]);

        $newAssignedId = $validated['user_id'] ?? null;
        if ($newAssignedId && $newAssignedId != $oldAssignedId) {
            \App\Models\Notification::create([
                'organization_id' => $task->project->organization_id,
                'user_id' => $newAssignedId,
                'title' => 'Task Assigned',
                'message' => "You have been assigned task '{$task->title}' in project '{$task->project->name}'.",
                'type' => 'task_assigned',
                'link' => route('projects.show', $task->project_id),
            ]);
        }

        return redirect()->route('projects.show', $task->project_id)->with('success', 'Task updated successfully.');
    }

    public function destroy(Task $task)
    {
        $this->ensureProjectBelongsToOrganization($task->project);
        $user = $this->currentUserService->getUser();

        if (!$this->currentUserService->hasPermission('projects.manage')) {
            if (!$task->project->users()->where('users.id', $user->id)->exists()) {
                abort(403, 'You are not assigned to this project.');
            }
        }

        if ($user->role?->code === 'employee' && $task->user_id !== $user->id) {
            abort(403, 'Employees can only delete tasks assigned to themselves.');
        }

        $projectId = $task->project_id;
        $task->delete();

        return redirect()->route('projects.show', $projectId)->with('success', 'Task deleted successfully.');
    }

    public function updateStatus(\Illuminate\Http\Request $request, Task $task)
    {
        $this->ensureProjectBelongsToOrganization($task->project);
        $user = $this->currentUserService->getUser();

        if (!$this->currentUserService->hasPermission('projects.manage')) {
            if (!$task->project->users()->where('users.id', $user->id)->exists()) {
                return response()->json(['success' => false, 'message' => 'You are not assigned to this project.'], 403);
            }
        }

        if ($user->role?->code === 'employee' && $task->user_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Employees can only modify tasks assigned to themselves.'], 403);
        }

        $request->validate([
            'status' => 'required|in:todo,in_progress,review,done,reopened',
        ]);

        $task->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => 'Task status updated to ' . ucfirst(str_replace('_', ' ', $task->status)),
            'task_id' => $task->id,
            'status' => $task->status
        ]);
    }

    public function storeChecklist(\Illuminate\Http\Request $request, Task $task)
    {
        $this->ensureProjectBelongsToOrganization($task->project);
        $user = $this->currentUserService->getUser();

        if (!$this->currentUserService->hasPermission('projects.manage')) {
            if (!$task->project->users()->where('users.id', $user->id)->exists()) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
                }
                abort(403);
            }
        }

        $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $item = \App\Models\TaskChecklist::create([
            'task_id' => $task->id,
            'title' => $request->title,
            'is_completed' => false,
            'created_by' => $user->id,
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Subtask added successfully.',
                'checklist' => $item,
            ]);
        }

        return redirect()->back()->with('success', 'Subtask added successfully.');
    }

    public function toggleChecklist(\Illuminate\Http\Request $request, \App\Models\TaskChecklist $checklist)
    {
        $this->ensureProjectBelongsToOrganization($checklist->task->project);
        $user = $this->currentUserService->getUser();

        if (!$this->currentUserService->hasPermission('projects.manage')) {
            if (!$checklist->task->project->users()->where('users.id', $user->id)->exists()) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
                }
                abort(403);
            }
        }

        $checklist->update([
            'is_completed' => !$checklist->is_completed,
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Subtask updated.',
                'is_completed' => $checklist->is_completed,
            ]);
        }

        return redirect()->back()->with('success', 'Subtask updated.');
    }

    public function destroyChecklist(\Illuminate\Http\Request $request, \App\Models\TaskChecklist $checklist)
    {
        $this->ensureProjectBelongsToOrganization($checklist->task->project);
        $user = $this->currentUserService->getUser();

        if (!$this->currentUserService->hasPermission('projects.manage')) {
            if (!$checklist->task->project->users()->where('users.id', $user->id)->exists()) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
                }
                abort(403);
            }
        }

        $checklist->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Subtask deleted.',
            ]);
        }

        return redirect()->back()->with('success', 'Subtask deleted.');
    }

    protected function ensureProjectBelongsToOrganization(Project $project): void
    {
        if ($project->organization_id !== $this->currentUserService->getOrganization()->id) {
            abort(404);
        }
    }
}
