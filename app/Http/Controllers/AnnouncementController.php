<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Services\CurrentUserService;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function __construct(
        protected CurrentUserService $currentUserService
    ) {}

    public function index()
    {
        $org = $this->currentUserService->getOrganization();

        $announcements = Announcement::where('organization_id', $org->id)
            ->with('author')
            ->orderByDesc('created_at')
            ->get();

        return view('announcements.index', compact('announcements'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string|max:2000',
            'priority' => 'required|in:low,medium,high',
            'expires_at' => 'nullable|date|after_or_equal:today',
        ]);

        $user = $this->currentUserService->getUser();
        $org = $this->currentUserService->getOrganization();

        Announcement::create([
            'organization_id' => $org->id,
            'user_id' => $user->id,
            'title' => $request->title,
            'content' => $request->content,
            'priority' => $request->priority,
            'expires_at' => $request->expires_at,
        ]);

        return redirect()->route('announcements.index')->with('success', 'Announcement published successfully.');
    }

    public function destroy(Announcement $announcement)
    {
        $org = $this->currentUserService->getOrganization();

        if ($announcement->organization_id !== $org->id) {
            abort(404);
        }

        $announcement->delete();

        return redirect()->route('announcements.index')->with('success', 'Announcement deleted successfully.');
    }
}
