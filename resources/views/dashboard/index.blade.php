@extends('layouts.dashboard')

@section('title', 'Dashboard')

@section('content')

@php
    $currentUserService = new \App\Services\CurrentUserService();
    $roleCode = strtolower($currentUser->role?->code ?? '');
    $isOwnerOrAdmin = in_array($roleCode, ['admin', 'owner']);
    $isManager = ($roleCode === 'manager');
    $isHr = ($roleCode === 'hr');
    $isLeadership = $isOwnerOrAdmin || $isManager || $isHr || 
                     $currentUserService->hasPermission('users.view') || 
                     $currentUserService->hasPermission('leaves.approve');
@endphp

<div class="container-fluid px-0">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
            <i class="fa-solid fa-triangle-exclamation me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Top Compact Bar: Company Name & Date -->
    <div class="page-hero d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <p class="text-muted small mb-1 fw-semibold text-uppercase" style="letter-spacing: 0.08em;">
                {{ now()->format('l, M d') }}
            </p>
            <h3 class="fw-bold mb-1">Good {{ now()->hour < 12 ? 'morning' : (now()->hour < 17 ? 'afternoon' : 'evening') }}, {{ $currentUser->first_name }}</h3>
            <div class="d-flex align-items-center gap-2 mt-2">
                <div class="bg-white border rounded-pill px-3 py-1.5 shadow-2xs d-inline-flex align-items-center gap-2">
                    <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width: 24px; height: 24px; font-size: 0.75rem;">
                        <i class="fa-solid fa-building"></i>
                    </div>
                    <span class="fw-bold text-dark small">{{ $currentUser->organization?->name ?? 'SyncWithTeam Workspace' }}</span>
                </div>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 py-2 fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#createTaskModal">
                <i class="fa-solid fa-plus me-1"></i> Add Task
            </button>
            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-2 fw-semibold bg-white" data-bs-toggle="modal" data-bs-target="#applyLeaveModal">
                <i class="fa-solid fa-plane-departure me-1"></i> Apply Leave
            </button>
        </div>
    </div>

    <!-- Metric Cards Row -->
    <div class="row g-3 mb-4">
        @if($isOwnerOrAdmin || $isHr)
            <!-- Active Members -->
            <div class="col-xl-3 col-md-6">
                <a href="{{ route('users.index') }}" class="text-decoration-none">
                    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white card-hover stat-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="text-muted small fw-bold text-uppercase d-block mb-1">Active Members</span>
                                <h3 class="mb-0 fw-bold text-dark font-monospace">{{ $stats['active_users'] }}</h3>
                            </div>
                            <div class="rounded-3 bg-primary-subtle text-primary p-3">
                                <i class="fa-solid fa-users fs-4"></i>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Total Projects -->
            <div class="col-xl-3 col-md-6">
                <a href="{{ route('projects.index') }}" class="text-decoration-none">
                    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white card-hover stat-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="text-muted small fw-bold text-uppercase d-block mb-1">Active Projects</span>
                                <h3 class="mb-0 fw-bold text-dark font-monospace">{{ $myProjects->count() }}</h3>
                            </div>
                            <div class="rounded-3 bg-success-subtle text-success p-3">
                                <i class="fa-solid fa-folder-kanban fs-4"></i>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Pending Leaves -->
            <div class="col-xl-3 col-md-6">
                <a href="{{ route('leaves.index') }}" class="text-decoration-none">
                    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white card-hover stat-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="text-muted small fw-bold text-uppercase d-block mb-1">Pending Leaves</span>
                                <h3 class="mb-0 fw-bold text-dark font-monospace">
                                    {{ \App\Models\Leave::where('organization_id', $currentUser->organization_id)->where('status', 'pending')->count() }}
                                </h3>
                            </div>
                            <div class="rounded-3 bg-warning-subtle text-warning p-3">
                                <i class="fa-solid fa-plane-departure fs-4"></i>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Departments -->
            <div class="col-xl-3 col-md-6">
                <a href="{{ route('departments.index') }}" class="text-decoration-none">
                    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white card-hover stat-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="text-muted small fw-bold text-uppercase d-block mb-1">Departments</span>
                                <h3 class="mb-0 fw-bold text-dark font-monospace">{{ $stats['departments'] }}</h3>
                            </div>
                            <div class="rounded-3 bg-purple-subtle text-purple p-3" style="background-color: #F3E8FF; color: #8B5CF6;">
                                <i class="fa-solid fa-sitemap fs-4"></i>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

        @elseif($isManager)
            <!-- Team Members -->
            <div class="col-xl-3 col-md-6">
                <a href="{{ route('users.index') }}" class="text-decoration-none">
                    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white card-hover stat-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="text-muted small fw-bold text-uppercase d-block mb-1">Team Members</span>
                                <h3 class="mb-0 fw-bold text-dark font-monospace">{{ count($teamMembers ?? []) }}</h3>
                            </div>
                            <div class="rounded-3 bg-primary-subtle text-primary p-3">
                                <i class="fa-solid fa-users fs-4"></i>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Projects -->
            <div class="col-xl-3 col-md-6">
                <a href="{{ route('projects.index') }}" class="text-decoration-none">
                    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white card-hover stat-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="text-muted small fw-bold text-uppercase d-block mb-1">Department Projects</span>
                                <h3 class="mb-0 fw-bold text-dark font-monospace">{{ $myProjects->count() }}</h3>
                            </div>
                            <div class="rounded-3 bg-success-subtle text-success p-3">
                                <i class="fa-solid fa-folder-kanban fs-4"></i>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Pending Leaves Queue -->
            <div class="col-xl-3 col-md-6">
                <a href="{{ route('leaves.index') }}" class="text-decoration-none">
                    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white card-hover stat-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="text-muted small fw-bold text-uppercase d-block mb-1">Leave Requests</span>
                                <h3 class="mb-0 fw-bold text-dark font-monospace">
                                    {{ \App\Models\Leave::where('organization_id', $currentUser->organization_id)->where('status', 'pending')->count() }}
                                </h3>
                            </div>
                            <div class="rounded-3 bg-warning-subtle text-warning p-3">
                                <i class="fa-solid fa-plane-departure fs-4"></i>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <!-- My Active Tasks -->
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white card-hover stat-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small fw-bold text-uppercase d-block mb-1">My Tasks</span>
                            <h3 class="mb-0 fw-bold text-dark font-monospace">{{ $myTasks->count() }}</h3>
                        </div>
                        <div class="rounded-3 bg-purple-subtle text-purple p-3" style="background-color: #F3E8FF; color: #8B5CF6;">
                            <i class="fa-solid fa-list-check fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

        @else
            <!-- Employee Metric Cards -->
            <!-- Leave Balance -->
            <div class="col-xl-4 col-md-6">
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white card-hover stat-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small fw-bold text-uppercase d-block mb-1">Leave Balance</span>
                            @php $curQ = $currentUser->current_quarter_leave_stats; @endphp
                            <h3 class="mb-0 fw-bold text-dark font-monospace">{{ $curQ['remaining'] ?? 0 }}</h3>
                        </div>
                        <div class="rounded-3 bg-primary-subtle text-primary p-2.5">
                            <i class="fa-solid fa-calendar-check fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Assigned Projects -->
            <div class="col-xl-4 col-md-6">
                <a href="{{ route('projects.index') }}" class="text-decoration-none">
                    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white card-hover stat-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="text-muted small fw-bold text-uppercase d-block mb-1">Active Projects</span>
                                <h3 class="mb-0 fw-bold text-dark font-monospace">{{ $myProjects->count() }}</h3>
                            </div>
                            <div class="rounded-3 bg-success-subtle text-success p-2.5">
                                <i class="fa-solid fa-folder-kanban fs-4"></i>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <!-- My Assigned Tasks -->
            <div class="col-xl-4 col-md-6">
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white card-hover stat-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small fw-bold text-uppercase d-block mb-1">My Active Tasks</span>
                            <h3 class="mb-0 fw-bold text-dark font-monospace">{{ $myTasks->count() }}</h3>
                        </div>
                        <div class="rounded-3 bg-warning-subtle text-warning p-2.5">
                            <i class="fa-solid fa-list-check fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    @if($isLeadership)
        <!-- Leadership Overview: Pending Leaves Queue & Department Staffing -->
        <div class="row g-4 mb-4">
            <!-- Pending Leave Requests Queue -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 bg-white">
                    <div class="card-header bg-white py-3.5 px-4 border-bottom d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fa-solid fa-clock text-warning fs-5 me-1"></i>
                            <h6 class="fw-bold text-dark mb-0 fs-6">Recent Leave Applications</h6>
                        </div>
                        <a href="{{ route('leaves.index') }}" class="btn btn-sm btn-link text-decoration-none fw-semibold">View All</a>
                    </div>
                    <div class="card-body p-0">
                        @php
                            $recentLeaves = \App\Models\Leave::where('organization_id', $currentUser->organization_id)
                                ->where('status', 'pending')
                                ->with(['user.role', 'user.department'])
                                ->orderByDesc('created_at')
                                ->limit(4)
                                ->get();
                        @endphp
                        <div class="list-group list-group-flush">
                            @forelse($recentLeaves as $lReq)
                                <div class="list-group-item p-3.5 hover-bg-light transition-all border-bottom">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-0 fw-bold text-dark fs-6">{{ $lReq->user->full_name }}</h6>
                                            <small class="text-muted d-block mb-1">{{ $lReq->user->department?->name ?? 'No Department' }} &bull; {{ $lReq->user->display_designation }}</small>
                                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill font-monospace" style="font-size: 0.72rem;">
                                                {{ $lReq->start_date->format('M d') }} - {{ $lReq->end_date->format('M d') }} ({{ ucfirst($lReq->type) }})
                                            </span>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill d-block mb-2 font-monospace" style="font-size: 0.68rem;">
                                                {{ $lReq->user->remaining_leaves_days }} Days Left
                                            </span>
                                            <a href="{{ route('leaves.index') }}" class="btn btn-xs btn-outline-primary rounded-pill px-2.5 py-1 fw-bold" style="font-size: 0.72rem;">
                                                Review Request
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-circle-check fs-2 text-success opacity-50 mb-2"></i>
                                    <p class="mb-0 fw-medium small">No pending leave applications.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- Department Staffing Overview -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 bg-white">
                    <div class="card-header bg-white py-3.5 px-4 border-bottom d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fa-solid fa-sitemap text-primary fs-5 me-1"></i>
                            <h6 class="fw-bold text-dark mb-0 fs-6">Department Overview</h6>
                        </div>
                        <a href="{{ route('departments.index') }}" class="btn btn-sm btn-link text-decoration-none fw-semibold">View All</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">Department</th>
                                        <th>Manager</th>
                                        <th class="pe-4 text-end">Staff Members</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentDepartments as $dept)
                                        @php
                                            $deptManager = $dept->users->first(fn($u) => strtolower($u->role?->code ?? '') === 'manager');
                                        @endphp
                                        <tr>
                                            <td class="ps-4">
                                                <span class="fw-bold text-dark">{{ $dept->name }}</span>
                                            </td>
                                            <td>
                                                @if($deptManager)
                                                    <span class="badge bg-light text-dark border rounded-pill"><i class="fa-solid fa-user-tie text-primary me-1"></i> {{ $deptManager->full_name }}</span>
                                                @else
                                                    <span class="text-muted small fst-italic">Unassigned</span>
                                                @endif
                                            </td>
                                            <td class="pe-4 text-end">
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill font-monospace px-2.5">{{ $dept->users->count() }} Members</span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center py-4 text-muted">No departments created.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Projects & Active Tasks Row (For All Roles) -->
    <div class="row g-4 mb-4">
        <!-- Projects List -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden h-100">
                <div class="card-header bg-white py-3.5 px-4 border-bottom d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fa-solid fa-folder-kanban text-primary fs-5 me-1"></i>
                        <h6 class="fw-bold text-dark mb-0 fs-6">Projects I'm Working On</h6>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-light text-secondary border rounded-pill px-3 py-1 font-monospace fw-bold">{{ $myProjects->count() }}</span>
                        <a href="{{ route('projects.index') }}" class="btn btn-sm btn-link text-decoration-none fw-semibold p-0" style="font-size: 0.8rem;">View All</a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($myProjects as $proj)
                            <div class="list-group-item px-4 py-3.5 hover-bg-light transition-all border-bottom">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <div class="d-flex align-items-center gap-2">
                                        <h6 class="mb-0 fw-bold text-dark fs-6">
                                            <a href="{{ route('projects.show', $proj->id) }}" class="text-decoration-none text-dark hover-primary">
                                                {{ $proj->name }}
                                            </a>
                                        </h6>
                                        @if($proj->status === 'planning')
                                            <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill font-monospace" style="font-size: 0.65rem;">PLANNING</span>
                                        @elseif($proj->status === 'active')
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill font-monospace" style="font-size: 0.65rem;">ACTIVE</span>
                                        @endif
                                        @if($proj->team_lead_id === $currentUser->id)
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill font-monospace" style="font-size: 0.65rem;">TEAM LEAD</span>
                                        @endif
                                    </div>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill font-monospace px-2.5 py-1" style="font-size: 0.72rem;">
                                        {{ $proj->tasks_count }} Tasks
                                    </span>
                                </div>
                                @if($proj->description)
                                    <p class="text-muted mb-0 text-truncate" style="max-width: 380px; font-size: 0.82rem;">
                                        {{ $proj->description }}
                                    </p>
                                @endif
                            </div>
                        @empty
                            <div class="text-center py-5 text-muted">
                                <i class="fa-solid fa-folder-open fs-2 text-secondary opacity-50 mb-2"></i>
                                <p class="mb-0 fw-medium small">No active projects assigned currently.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Tasks List -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden h-100">
                <div class="card-header bg-white py-3.5 px-4 border-bottom d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fa-solid fa-list-check text-warning fs-5 me-1"></i>
                        <h6 class="fw-bold text-dark mb-0 fs-6">My Active Tasks</h6>
                    </div>
                    <span class="badge bg-light text-secondary border rounded-pill px-3 py-1 font-monospace fw-bold">{{ $myTasks->count() }}</span>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($myTasks as $task)
                            <div class="list-group-item px-4 py-3.5 hover-bg-light transition-all border-bottom">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1 fw-bold text-dark fs-6">{{ $task->title }}</h6>
                                        <small class="text-muted d-flex align-items-center gap-1" style="font-size: 0.78rem;">
                                            <i class="fa-regular fa-folder text-primary me-0.5"></i> {{ $task->project?->name ?? 'General Work' }}
                                        </small>
                                    </div>
                                    <div>
                                        @if($task->status === 'done')
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 text-uppercase fw-bold" style="font-size: 0.7rem;">Done</span>
                                        @elseif($task->status === 'review')
                                            <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-2.5 py-1 text-uppercase fw-bold" style="font-size: 0.7rem;">In Review</span>
                                        @elseif($task->status === 'in_progress')
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1 text-uppercase fw-bold" style="font-size: 0.7rem;">In Progress</span>
                                        @else
                                            <span class="badge bg-light text-dark border rounded-pill px-2.5 py-1 text-uppercase fw-bold" style="font-size: 0.7rem;">To Do</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-5 text-muted">
                                <i class="fa-solid fa-clipboard-check fs-2 text-success opacity-50 mb-2"></i>
                                <p class="mb-0 fw-medium small">No active tasks assigned to you right now.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Live Team Status & Office Highlights Section (For All Users) -->
    <div class="row g-4 mb-4">
        <!-- Live Team Presence Board -->
        <div class="col-lg-7 col-xl-8">
            <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden h-100">
                <div class="card-header bg-white py-3.5 px-4 border-bottom d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <span class="pulse-dot bg-success rounded-circle d-inline-block" style="width: 10px; height: 10px;"></span>
                        <h6 class="fw-bold text-dark mb-0 fs-6">Live Team Presence Board</h6>
                    </div>
                    <span class="badge bg-light text-muted border rounded-pill font-monospace" style="font-size: 0.72rem;">
                        {{ count($teamMembers ?? []) }} Members
                    </span>
                </div>
                <div class="card-body p-3">
                    <div class="row g-3">
                        @forelse($teamMembers ?? [] as $member)
                            <div class="col-md-6 col-xl-4">
                                <div class="p-3 rounded-3 border bg-light-subtle h-100 d-flex flex-column justify-content-between">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="avatar-circle rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold" style="width: 34px; height: 34px; font-size: 0.85rem;">
                                                {{ strtoupper(substr($member->first_name, 0, 1) . substr($member->last_name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <h6 class="mb-0 fw-bold text-dark text-truncate" style="max-width: 120px; font-size: 0.88rem;">{{ $member->full_name }}</h6>
                                                <small class="text-muted d-block text-truncate" style="max-width: 120px; font-size: 0.75rem;">{{ $member->department?->name ?? 'Team' }}</small>
                                            </div>
                                        </div>
                                        <div>
                                            @if($member->live_status === 'working')
                                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill font-monospace" style="font-size: 0.68rem;">
                                                    🟢 Working
                                                </span>
                                            @elseif($member->live_status === 'on_leave')
                                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill font-monospace" style="font-size: 0.68rem;">
                                                    🟡 On Leave
                                                </span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill font-monospace" style="font-size: 0.68rem;">
                                                    ⚪ Offline
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    @if($member->live_status === 'working' && $member->active_log)
                                        <div class="bg-white p-2 rounded border-0 shadow-2xs mt-1" style="font-size: 0.75rem;">
                                            <span class="text-muted d-block text-truncate"><i class="fa-solid fa-clock text-success me-1"></i> Clocked in {{ $member->active_log->clock_in->format('h:i A') }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="col-12 text-center py-4 text-muted">
                                <p class="mb-0">No active team members found.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Announcements & Upcoming Holidays Widgets -->
        <div class="col-lg-5 col-xl-4 d-flex flex-column gap-3">
            <!-- Notice / Announcements Card -->
            <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden flex-fill">
                <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fa-solid fa-bullhorn text-danger me-1"></i>
                        <h6 class="fw-bold text-dark mb-0 fs-6">Announcements</h6>
                    </div>
                    <a href="{{ route('announcements.index') }}" class="btn btn-sm btn-link text-decoration-none p-0 fw-semibold" style="font-size: 0.78rem;">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($announcements ?? [] as $announce)
                            <div class="list-group-item px-3.5 py-2.5 border-bottom">
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <h6 class="mb-0 fw-bold text-dark" style="font-size: 0.85rem;">
                                        @if($announce->is_pinned)
                                            <i class="fa-solid fa-thumbtack text-warning me-1" style="font-size: 0.75rem;"></i>
                                        @endif
                                        {{ $announce->title }}
                                    </h6>
                                    <span class="badge bg-light text-muted border font-monospace ms-2" style="font-size: 0.65rem;">
                                        {{ $announce->created_at->format('M d') }}
                                    </span>
                                </div>
                                <p class="text-muted mb-0 small text-truncate-2" style="font-size: 0.78rem; line-height: 1.35;">
                                    {{ $announce->content }}
                                </p>
                            </div>
                        @empty
                            <div class="text-center py-4 text-muted">
                                <small>No announcements posted yet.</small>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Upcoming Holidays Card -->
            <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden flex-fill">
                <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fa-solid fa-calendar-day text-primary me-1"></i>
                        <h6 class="fw-bold text-dark mb-0 fs-6">Upcoming Holidays</h6>
                    </div>
                    <a href="{{ route('holidays.index') }}" class="btn btn-sm btn-link text-decoration-none p-0 fw-semibold" style="font-size: 0.78rem;">View Calendar</a>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($upcomingHolidays ?? [] as $holiday)
                            <div class="list-group-item px-3.5 py-2.5 d-flex justify-content-between align-items-center border-bottom">
                                <div>
                                    <h6 class="mb-0 fw-bold text-dark" style="font-size: 0.85rem;">{{ $holiday->name }}</h6>
                                    <span class="text-muted d-block" style="font-size: 0.75rem;">{{ $holiday->date->format('l, M d, Y') }}</span>
                                </div>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill font-monospace" style="font-size: 0.7rem;">
                                    {{ $holiday->date->isToday() ? 'Today!' : $holiday->date->diffForHumans() }}
                                </span>
                            </div>
                        @empty
                            <div class="text-center py-4 text-muted">
                                <small>No upcoming holidays listed.</small>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Apply Leave Modal -->
<div class="modal fade" id="applyLeaveModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4 overflow-hidden">
            <div class="modal-header bg-light py-3 px-4 border-bottom">
                <h5 class="modal-title fw-bold text-dark fs-6"><i class="fa-solid fa-plane-departure text-primary me-2"></i> Apply for Leave</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('leaves.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary small text-uppercase">Leave Type</label>
                        <select name="type" class="form-select rounded-3" required>
                            <option value="full_day">Full Day</option>
                            <option value="half_day">Half Day (0.5 Day)</option>
                            <option value="short_leave">Short Leave (0.5 Day)</option>
                        </select>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-bold text-secondary small text-uppercase">Start Date</label>
                            <input type="date" name="start_date" class="form-control rounded-3" value="{{ now()->toDateString() }}" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold text-secondary small text-uppercase">End Date</label>
                            <input type="date" name="end_date" class="form-control rounded-3" value="{{ now()->toDateString() }}" required>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-bold text-secondary small text-uppercase">Reason</label>
                        <textarea name="reason" class="form-control rounded-3" rows="3" placeholder="State reason for leave request..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2.5 px-4 border-top">
                    <button type="button" class="btn btn-light border rounded-pill px-3.5" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold">Submit Request</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Create Task Modal -->
<div class="modal fade" id="createTaskModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4 overflow-hidden">
            <div class="modal-header bg-light py-3 px-4 border-bottom">
                <h5 class="modal-title fw-bold text-dark fs-6"><i class="fa-solid fa-square-plus text-primary me-2"></i> Create New Task</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            @if($myProjects->count() > 0)
                <form id="dashCreateTaskForm" method="POST" action="{{ route('tasks.store', $myProjects->first()->id) }}">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold text-secondary small text-uppercase">Select Project</label>
                            <select id="dashProjectSelect" class="form-select rounded-3" onchange="updateDashTaskAction(this.value)" required>
                                @foreach($myProjects as $proj)
                                    <option value="{{ $proj->id }}">{{ $proj->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold text-secondary small text-uppercase">Task Title</label>
                            <input type="text" name="title" class="form-control rounded-3" placeholder="Enter task title..." required>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <label class="form-label fw-bold text-secondary small text-uppercase">Priority</label>
                                <select name="priority" class="form-select rounded-3" required>
                                    <option value="low">Low</option>
                                    <option value="medium" selected>Medium</option>
                                    <option value="high">High</option>
                                    <option value="urgent">Urgent</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-bold text-secondary small text-uppercase">Status</label>
                                <select name="status" class="form-select rounded-3" required>
                                    <option value="todo" selected>To Do</option>
                                    <option value="in_progress">In Progress</option>
                                    <option value="review">In Review</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold text-secondary small text-uppercase">Due Date (Optional)</label>
                            <input type="date" name="due_date" class="form-control rounded-3">
                        </div>
                        <input type="hidden" name="user_id" value="{{ $currentUser->id }}">
                    </div>
                    <div class="modal-footer bg-light py-2.5 px-4 border-top">
                        <button type="button" class="btn btn-light border rounded-pill px-3.5" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold">Create Task</button>
                    </div>
                </form>
            @else
                <div class="modal-body p-4 text-center text-muted py-5">
                    <i class="fa-solid fa-folder-open fs-2 mb-2 opacity-50"></i>
                    <p class="mb-0">You need to be assigned to at least one active project to create a task.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
    function updateDashTaskAction(projectId) {
        const form = document.getElementById('dashCreateTaskForm');
        if (form) {
            form.action = `/projects/${projectId}/tasks`;
        }
    }
</script>

@endsection