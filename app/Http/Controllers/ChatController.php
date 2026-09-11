<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use App\Models\Project;
use App\Models\User;
use App\Services\CurrentUserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ChatController extends Controller
{
    protected CurrentUserService $currentUserService;

    public function __construct(CurrentUserService $currentUserService)
    {
        $this->currentUserService = $currentUserService;
    }

    public function index(Request $request)
    {
        $user = $this->currentUserService->getUser();
        if (!$user) {
            return redirect()->route('login');
        }

        $orgId = $user->organization_id;

        // Fetch Projects
        $projects = Project::where('organization_id', $orgId)
            ->orderBy('name')
            ->get();

        // Fetch Team Members
        $teamMembers = User::where('organization_id', $orgId)
            ->where('id', '!=', $user->id)
            ->where('status', true)
            ->with('role')
            ->orderBy('first_name')
            ->get();


        // Unread counts per member
        $unreadCounts = ChatMessage::where('organization_id', $orgId)
            ->where('channel_type', 'direct')
            ->where('receiver_id', $user->id)
            ->where('is_read', false)
            ->selectRaw('user_id, count(*) as count')
            ->groupBy('user_id')
            ->pluck('count', 'user_id');

        // Active thread selection
        $activeType = $request->query('type', 'general');
        $activeId = $request->query('id', null);

        $activeTarget = null;
        if ($activeType === 'project' && $activeId) {
            $activeTarget = Project::where('organization_id', $orgId)->find($activeId);
            if (!$activeTarget) {
                $activeType = 'general';
                $activeId = null;
            }
        } elseif ($activeType === 'direct' && $activeId) {
            $activeTarget = User::where('organization_id', $orgId)->find($activeId);
            if (!$activeTarget) {
                $activeType = 'general';
                $activeId = null;
            } else {
                // Mark messages from this user as read
                ChatMessage::where('organization_id', $orgId)
                    ->where('channel_type', 'direct')
                    ->where('user_id', $activeTarget->id)
                    ->where('receiver_id', $user->id)
                    ->where('is_read', false)
                    ->update(['is_read' => true]);
            }
        }

        return view('chat.index', compact(
            'projects',
            'teamMembers',
            'unreadCounts',
            'activeType',
            'activeId',
            'activeTarget',
            'user'
        ));
    }

    public function getMessages(Request $request)
    {
        $user = $this->currentUserService->getUser();
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        $orgId = $user->organization_id;
        $type = $request->query('channel_type', 'general');
        $targetId = $request->query('target_id', null);
        $lastId = $request->query('last_id', 0);

        $query = ChatMessage::with('user')
            ->where('organization_id', $orgId);

        if ($type === 'general') {
            $query->where('channel_type', 'general');
        } elseif ($type === 'project' && $targetId) {
            $query->where('channel_type', 'project')
                ->where('project_id', $targetId);
        } elseif ($type === 'direct' && $targetId) {
            $query->where('channel_type', 'direct')
                ->where(function ($q) use ($user, $targetId) {
                    $q->where(function ($sq) use ($user, $targetId) {
                        $sq->where('user_id', $user->id)->where('receiver_id', $targetId);
                    })->orWhere(function ($sq) use ($user, $targetId) {
                        $sq->where('user_id', $targetId)->where('receiver_id', $user->id);
                    });
                });

            // Mark as read
            ChatMessage::where('organization_id', $orgId)
                ->where('channel_type', 'direct')
                ->where('user_id', $targetId)
                ->where('receiver_id', $user->id)
                ->where('is_read', false)
                ->update(['is_read' => true]);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Invalid parameters'], 400);
        }

        if ($lastId > 0) {
            $query->where('id', '>', $lastId);
        }

        $messages = $query->orderBy('created_at', 'asc')->take(100)->get();

        $formatted = $messages->map(function ($msg) use ($user) {
            return [
                'id' => $msg->id,
                'user_id' => $msg->user_id,
                'user_name' => $msg->user ? $msg->user->full_name : 'Unknown',
                'user_initial' => $msg->user ? strtoupper(substr($msg->user->first_name, 0, 1)) : '?',
                'user_avatar' => $msg->user && $msg->user->avatar ? asset('storage/' . $msg->user->avatar) : null,
                'message' => $msg->message,
                'attachment_url' => $msg->attachment_path ? asset('storage/' . $msg->attachment_path) : null,
                'attachment_name' => $msg->attachment_name,
                'is_image' => $msg->attachment_path ? (bool) preg_match('/\.(jpg|jpeg|png|gif|webp|svg)$/i', $msg->attachment_path) : false,
                'created_at_human' => $msg->created_at->diffForHumans(),
                'created_at_time' => $msg->created_at->format('h:i A'),
                'created_at_date' => $msg->created_at->format('d M Y'),
                'is_me' => $msg->user_id === $user->id,
            ];
        });

        return response()->json([
            'status' => 'success',
            'messages' => $formatted,
        ]);
    }

    public function sendMessage(Request $request)
    {
        $user = $this->currentUserService->getUser();
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        $request->validate([
            'channel_type' => 'required|in:general,project,direct',
            'project_id' => 'nullable|required_if:channel_type,project|exists:projects,id',
            'receiver_id' => 'nullable|required_if:channel_type,direct|exists:users,id',
            'message' => 'nullable|string|max:5000',
            'attachment' => 'nullable|file|max:10240', // max 10MB
        ]);

        if (empty($request->message) && !$request->hasFile('attachment')) {
            return response()->json(['status' => 'error', 'message' => 'Message or attachment is required'], 422);
        }

        $attachmentPath = null;
        $attachmentName = null;

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $attachmentName = $file->getClientOriginalName();
            $attachmentPath = $file->store('chat_attachments', 'public');
        }

        $chatMessage = ChatMessage::create([
            'organization_id' => $user->organization_id,
            'user_id' => $user->id,
            'channel_type' => $request->channel_type,
            'project_id' => $request->channel_type === 'project' ? $request->project_id : null,
            'receiver_id' => $request->channel_type === 'direct' ? $request->receiver_id : null,
            'message' => $request->message,
            'attachment_path' => $attachmentPath,
            'attachment_name' => $attachmentName,
            'is_read' => false,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => [
                'id' => $chatMessage->id,
                'user_id' => $user->id,
                'user_name' => $user->full_name,
                'user_initial' => strtoupper(substr($user->first_name, 0, 1)),
                'user_avatar' => $user->avatar ? asset('storage/' . $user->avatar) : null,
                'message' => $chatMessage->message,
                'attachment_url' => $chatMessage->attachment_path ? asset('storage/' . $chatMessage->attachment_path) : null,
                'attachment_name' => $chatMessage->attachment_name,
                'is_image' => $chatMessage->attachment_path ? (bool) preg_match('/\.(jpg|jpeg|png|gif|webp|svg)$/i', $chatMessage->attachment_path) : false,
                'created_at_human' => $chatMessage->created_at->diffForHumans(),
                'created_at_time' => $chatMessage->created_at->format('h:i A'),
                'created_at_date' => $chatMessage->created_at->format('d M Y'),
                'is_me' => true,
            ]
        ]);
    }
}
