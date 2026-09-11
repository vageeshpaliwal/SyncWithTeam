<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Services\CurrentUserService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(
        protected CurrentUserService $currentUserService
    ) {}

    public function markAsRead(Notification $notification)
    {
        $user = $this->currentUserService->getUser();

        if ($notification->user_id === $user->id) {
            $notification->update(['is_read' => true]);
        }

        if ($notification->link) {
            return redirect($notification->link);
        }

        return redirect()->back();
    }

    public function markAllAsRead()
    {
        $user = $this->currentUserService->getUser();

        Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return redirect()->back()->with('success', 'All notifications marked as read.');
    }
}
