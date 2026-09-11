<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskComment;
use App\Models\Notification;
use App\Services\CurrentUserService;
use Illuminate\Http\Request;

class TaskCommentController extends Controller
{
    public function __construct(
        protected CurrentUserService $currentUserService
    ) {}

    public function store(Request $request, Task $task)
    {
        $commentText = $request->input('comment') ?? $request->input('content');
        
        if (!$commentText || trim($commentText) === '') {
            return redirect()->back()->with('error', 'Comment cannot be empty.');
        }

        $currentUser = $this->currentUserService->getUser();
        $org = $this->currentUserService->getOrganization();

        if ($task->project->organization_id !== $org->id) {
            abort(404);
        }

        $comment = TaskComment::create([
            'organization_id' => $org->id,
            'task_id' => $task->id,
            'user_id' => $currentUser->id,
            'comment' => trim($commentText),
        ]);

        // Send notification to task owner/assignee if different from commenter
        if ($task->user_id && $task->user_id !== $currentUser->id) {
            Notification::create([
                'organization_id' => $org->id,
                'user_id' => $task->user_id,
                'title' => 'New Task Comment',
                'message' => "{$currentUser->full_name} commented on task '{$task->title}'",
                'type' => 'task_comment',
                'link' => route('projects.show', $task->project_id),
            ]);
        }

        return redirect()->back()->with('success', 'Comment posted successfully.');
    }

    public function destroy(Task $task, TaskComment $comment)
    {
        $currentUser = $this->currentUserService->getUser();
        $org = $this->currentUserService->getOrganization();

        if ($comment->task_id !== $task->id || $comment->organization_id !== $org->id) {
            abort(404);
        }

        $roleCode = strtolower($currentUser->role?->code ?? '');
        if ($comment->user_id !== $currentUser->id && !in_array($roleCode, ['admin', 'owner', 'hr'])) {
            abort(403, 'Unauthorized to delete this comment.');
        }

        $comment->delete();

        return redirect()->back()->with('success', 'Comment deleted successfully.');
    }
}
