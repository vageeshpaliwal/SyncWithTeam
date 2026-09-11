<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Project;
use App\Services\CurrentUserService;
use App\Services\UserService;
use App\Services\PlanLimitService;

class ProjectController extends Controller
{
    public function __construct(
        protected CurrentUserService $currentUserService,
        protected UserService $userService,
        protected PlanLimitService $planLimitService
    ) {}

    public function index(\Illuminate\Http\Request $request)
    {
        $org = $this->currentUserService->getOrganization();
        $user = $this->currentUserService->getUser();

        $departments = \App\Models\Department::where('organization_id', $org->id)->orderBy('name')->get();

        if ($this->currentUserService->hasPermission('projects.manage')) {
            $query = Project::where('organization_id', $org->id);
        } else {
            if (!$this->currentUserService->hasPermission('projects.view')) {
                abort(403);
            }
            $query = Project::where('organization_id', $org->id)
                ->where(function ($q) use ($user) {
                    $q->whereHas('users', function ($uQ) use ($user) {
                        $uQ->where('users.id', $user->id);
                    })->orWhere('team_lead_id', $user->id);
                });
        }

        // Search by Project Name or Description
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by Department
        if ($request->filled('department_id')) {
            $query->whereHas('users', function ($q) use ($request) {
                $q->where('department_id', $request->department_id);
            });
        }

        // Filter by Status (Active / Inactive / specific)
        if ($request->filled('status')) {
            if ($request->status === 'inactive') {
                $query->where('status', '!=', 'active');
            } else {
                $query->where('status', $request->status);
            }
        }

        $projects = $query->with(['teamLead'])->withCount(['users', 'tasks'])
            ->orderBy('name')
            ->get();

        return view('projects.index', compact('projects', 'departments'));
    }

    public function create()
    {
        if (!$this->currentUserService->hasPermission('projects.manage')) {
            abort(403);
        }

        $org = $this->currentUserService->getOrganization();
        $currentUser = $this->currentUserService->getUser();

        // Only list Employees (exclude Owner, Admin, HR, and Manager)
        $usersQuery = \App\Models\User::with(['role', 'department'])
            ->where('organization_id', $org->id)
            ->whereHas('role', function ($q) {
                $q->where('code', 'employee');
            });

        // If the current user is a Department Manager, only show employees of their department
        if ($currentUser->role?->code === 'manager' && $currentUser->department_id) {
            $usersQuery->where('department_id', $currentUser->department_id);
        }

        $users = $usersQuery->orderBy('first_name')->get();

        return view('projects.create', compact('users'));
    }

    public function store(StoreProjectRequest $request)
    {
        if (!$this->currentUserService->hasPermission('projects.manage')) {
            abort(403);
        }

        $org = $this->currentUserService->getOrganization();

        if (!$this->planLimitService->canAddProject($org->id)) {
            return redirect()->back()->withInput()->with('error', 'Plan limit reached! Upgrade your subscription to create more projects.');
        }

        $validated = $request->validated();
        $teamLeadId = !empty($validated['team_lead_id']) ? (int)$validated['team_lead_id'] : null;

        $project = Project::create([
            'organization_id' => $org->id,
            'team_lead_id' => $teamLeadId,
            'name' => $validated['name'],
            'client_name' => $validated['client_name'] ?? null,
            'client_country' => $validated['client_country'] ?? null,
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'],
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
        ]);

        // Handle uploaded project documents
        if ($request->hasFile('documents')) {
            $currentUser = $this->currentUserService->getUser();
            foreach ($request->file('documents') as $uploadedFile) {
                if ($uploadedFile && $uploadedFile->isValid()) {
                    $originalName = $uploadedFile->getClientOriginalName();
                    $fileSize = $uploadedFile->getSize();
                    $extension = strtolower($uploadedFile->getClientOriginalExtension());
                    $fileType = 'other';
                    if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'])) {
                        $fileType = 'image';
                    } elseif (in_array($extension, ['pdf', 'doc', 'docx', 'txt', 'rtf', 'csv', 'xlsx', 'xls'])) {
                        $fileType = 'document';
                    } elseif (in_array($extension, ['zip', 'rar', '7z', 'tar', 'gz'])) {
                        $fileType = 'archive';
                    } elseif (in_array($extension, ['js', 'php', 'py', 'html', 'css', 'json', 'sql'])) {
                        $fileType = 'code';
                    }
                    $path = $uploadedFile->store("projects/{$project->id}/files", 'public');
                    \App\Models\ProjectFile::create([
                        'organization_id' => $org->id,
                        'project_id' => $project->id,
                        'user_id' => $currentUser->id,
                        'name' => $originalName,
                        'file_path' => $path,
                        'file_type' => $fileType,
                        'file_size' => $fileSize,
                    ]);
                }
            }
        }

        $assignedUserIds = array_map('intval', $validated['user_ids'] ?? []);
        if ($teamLeadId && !in_array($teamLeadId, $assignedUserIds)) {
            $assignedUserIds[] = $teamLeadId;
        }
        $project->users()->sync($assignedUserIds);

        // Notify assigned users
        foreach ($assignedUserIds as $uId) {
            $isLead = ($teamLeadId && $uId === $teamLeadId);
            \App\Models\Notification::create([
                'organization_id' => $org->id,
                'user_id' => $uId,
                'title' => $isLead ? 'Assigned as Team Lead' : 'Assigned to Project',
                'message' => $isLead 
                    ? "You have been assigned as Team Lead for project '{$project->name}'."
                    : "You have been assigned to project '{$project->name}'.",
                'type' => $isLead ? 'team_lead_assigned' : 'project_assigned',
                'link' => route('projects.show', $project->id),
            ]);
        }

        return redirect()->route('projects.index')->with('success', 'Project created successfully.');
    }

    public function show(Project $project)
    {
        $this->ensureBelongsToOrganization($project);
        $user = $this->currentUserService->getUser();

        if (!$this->currentUserService->hasPermission('projects.manage')) {
            $this->checkProjectAccess($project);
        }

        // Eager load users, tasks, task assignees, task comments, checklists, and files in 1 query batch
        $project->load([
            'teamLead',
            'users.role',
            'users.department',
            'tasks.assignee',
            'tasks.comments.user',
            'tasks.checklists',
            'tasks.bugs.reporter',
            'files.uploader',
            'bugs.reporter',
            'bugs.task'
        ]);

        // Ensure Team Lead is always first in the users list
        $leadId = $project->team_lead_id;
        $sortedUsers = $project->users->sort(function ($a, $b) use ($leadId) {
            if ($leadId) {
                if ($a->id === $leadId) return -1;
                if ($b->id === $leadId) return 1;
            }
            return strcasecmp($a->first_name ?? '', $b->first_name ?? '');
        })->values();
        $project->setRelation('users', $sortedUsers);

        return view('projects.show', compact('project'));
    }

    public function edit(Project $project)
    {
        if (!$this->currentUserService->hasPermission('projects.manage')) {
            abort(403);
        }

        $this->ensureBelongsToOrganization($project);

        $org = $this->currentUserService->getOrganization();
        $currentUser = $this->currentUserService->getUser();

        // Only list Employees (exclude Owner, Admin, HR, and Manager)
        $usersQuery = \App\Models\User::with(['role', 'department'])
            ->where('organization_id', $org->id)
            ->whereHas('role', function ($q) {
                $q->where('code', 'employee');
            });

        // If the current user is a Department Manager, only show employees of their department
        if ($currentUser->role?->code === 'manager' && $currentUser->department_id) {
            $usersQuery->where('department_id', $currentUser->department_id);
        }

        $users = $usersQuery->orderBy('first_name')->get();
        $assignedUserIds = $project->users()->pluck('users.id')->toArray();

        return view('projects.edit', compact('project', 'users', 'assignedUserIds'));
    }

    public function update(UpdateProjectRequest $request, Project $project)
    {
        if (!$this->currentUserService->hasPermission('projects.manage')) {
            abort(403);
        }

        $this->ensureBelongsToOrganization($project);
        $validated = $request->validated();
        $teamLeadId = !empty($validated['team_lead_id']) ? (int)$validated['team_lead_id'] : null;
        $oldLeadId = $project->team_lead_id;

        $oldUserIds = $project->users()->pluck('users.id')->toArray();

        $project->update([
            'team_lead_id' => $teamLeadId,
            'name' => $validated['name'],
            'client_name' => $validated['client_name'] ?? null,
            'client_country' => $validated['client_country'] ?? null,
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'],
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
        ]);

        // Handle uploaded project documents
        if ($request->hasFile('documents')) {
            $currentUser = $this->currentUserService->getUser();
            foreach ($request->file('documents') as $uploadedFile) {
                if ($uploadedFile && $uploadedFile->isValid()) {
                    $originalName = $uploadedFile->getClientOriginalName();
                    $fileSize = $uploadedFile->getSize();
                    $extension = strtolower($uploadedFile->getClientOriginalExtension());
                    $fileType = 'other';
                    if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'])) {
                        $fileType = 'image';
                    } elseif (in_array($extension, ['pdf', 'doc', 'docx', 'txt', 'rtf', 'csv', 'xlsx', 'xls'])) {
                        $fileType = 'document';
                    } elseif (in_array($extension, ['zip', 'rar', '7z', 'tar', 'gz'])) {
                        $fileType = 'archive';
                    } elseif (in_array($extension, ['js', 'php', 'py', 'html', 'css', 'json', 'sql'])) {
                        $fileType = 'code';
                    }
                    $path = $uploadedFile->store("projects/{$project->id}/files", 'public');
                    \App\Models\ProjectFile::create([
                        'organization_id' => $project->organization_id,
                        'project_id' => $project->id,
                        'user_id' => $currentUser->id,
                        'name' => $originalName,
                        'file_path' => $path,
                        'file_type' => $fileType,
                        'file_size' => $fileSize,
                    ]);
                }
            }
        }

        $newUserIds = array_map('intval', $validated['user_ids'] ?? []);
        if ($teamLeadId && !in_array($teamLeadId, $newUserIds)) {
            $newUserIds[] = $teamLeadId;
        }
        $project->users()->sync($newUserIds);

        // Notify freshly assigned users
        $freshlyAssigned = array_diff($newUserIds, $oldUserIds);
        foreach ($freshlyAssigned as $uId) {
            $isLead = ($teamLeadId && $uId === $teamLeadId);
            \App\Models\Notification::create([
                'organization_id' => $project->organization_id,
                'user_id' => $uId,
                'title' => $isLead ? 'Assigned as Team Lead' : 'Assigned to Project',
                'message' => $isLead 
                    ? "You have been assigned as Team Lead for project '{$project->name}'."
                    : "You have been assigned to project '{$project->name}'.",
                'type' => $isLead ? 'team_lead_assigned' : 'project_assigned',
                'link' => route('projects.show', $project->id),
            ]);
        }

        if ($teamLeadId && $teamLeadId != $oldLeadId && !in_array($teamLeadId, $freshlyAssigned)) {
            \App\Models\Notification::create([
                'organization_id' => $project->organization_id,
                'user_id' => $teamLeadId,
                'title' => 'Assigned as Team Lead',
                'message' => "You have been assigned as Team Lead for project '{$project->name}'.",
                'type' => 'team_lead_assigned',
                'link' => route('projects.show', $project->id),
            ]);
        }

        return redirect()->route('projects.index')->with('success', 'Project updated successfully.');
    }

    public function destroy(Project $project)
    {
        if (!$this->currentUserService->hasPermission('projects.manage')) {
            abort(403);
        }

        $this->ensureBelongsToOrganization($project);

        $project->delete();

        return redirect()->route('projects.index')->with('success', 'Project deleted successfully.');
    }

    protected function ensureBelongsToOrganization(Project $project): void
    {
        $org = $this->currentUserService->getOrganization();
        if (!$org || $project->organization_id !== $org->id) {
            abort(404);
        }
    }

    protected function checkProjectAccess(Project $project): void
    {
        if (!$this->currentUserService->hasPermission('projects.view')) {
            abort(403, 'Unauthorized access.');
        }

        $user = $this->currentUserService->getUser();
        if (!$user) {
            abort(401);
        }

        $hasAccess = $project->users()->where('users.id', $user->id)->exists()
            || ($project->team_lead_id && (int)$project->team_lead_id === (int)$user->id);

        if (!$hasAccess) {
            abort(403, 'You are not assigned to this project.');
        }
    }
}
