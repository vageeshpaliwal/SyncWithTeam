@extends('layouts.dashboard')

@section('title', $project->name)

@section('content')

@push('styles')
<style>
    .task-action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 26px;
        height: 26px;
        border-radius: 6px;
        border: 1px solid transparent;
        background: transparent;
        color: #94a3b8;
        font-size: 0.75rem;
        cursor: pointer;
        padding: 0;
        transition: all 0.18s cubic-bezier(0.16, 1, 0.3, 1);
        text-decoration: none;
        line-height: 1;
    }
    .task-action-btn:hover {
        background: #f1f5f9;
        color: #334155;
        border-color: #e2e8f0;
        transform: translateY(-1px);
    }
    .task-action-btn.btn-action-bug {
        color: #ef4444;
    }
    .task-action-btn.btn-action-bug:hover {
        background: #fef2f2;
        color: #dc2626;
        border-color: #fecaca;
    }
    .task-action-btn.btn-action-edit {
        color: #6366f1;
    }
    .task-action-btn.btn-action-edit:hover {
        background: #eef2ff;
        color: #4f46e5;
        border-color: #c7d2fe;
    }
    .task-action-btn.btn-action-delete {
        color: #94a3b8;
    }
    .task-action-btn.btn-action-delete:hover {
        background: #fff1f2;
        color: #e11d48;
        border-color: #fecdd3;
    }
    .task-action-btn.btn-action-open {
        color: #64748b;
    }
    .task-action-btn.btn-action-open:hover {
        background: #f0fdf4;
        color: #16a34a;
        border-color: #bbf7d0;
    }
    .kanban-drag-grip {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 18px;
        height: 26px;
        color: #cbd5e1;
        cursor: grab;
        font-size: 0.75rem;
        transition: color 0.15s ease;
    }
    .kanban-drag-grip:hover {
        color: #64748b;
    }
    .card-comment-btn {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 2px 7px;
        border-radius: 12px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        color: #64748b;
        font-size: 0.72rem;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        transition: all 0.18s ease;
        line-height: 1.2;
    }
    .card-comment-btn:hover {
        background: #eef2ff;
        border-color: #c7d2fe;
        color: #4f46e5;
        transform: translateY(-1px);
    }
    .kanban-card {
        transition: transform 0.18s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.18s ease, border-color 0.18s ease;
        border-color: #e2e8f0 !important;
    }
    .kanban-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px -4px rgba(0, 0, 0, 0.08) !important;
        border-color: #cbd5e1 !important;
    }
    .task-title-link {
        color: #1e293b;
        transition: color 0.15s ease;
    }
    .task-title-link:hover {
        color: #4f46e5 !important;
    }

    [data-bs-theme="dark"] .task-action-btn:hover {
        background: rgba(255, 255, 255, 0.08);
        color: #f1f5f9;
        border-color: rgba(255, 255, 255, 0.15);
    }
    [data-bs-theme="dark"] .task-action-btn.btn-action-bug:hover {
        background: rgba(239, 68, 68, 0.2);
        color: #f87171;
        border-color: rgba(239, 68, 68, 0.3);
    }
    [data-bs-theme="dark"] .task-action-btn.btn-action-edit:hover {
        background: rgba(99, 102, 241, 0.2);
        color: #818cf8;
        border-color: rgba(99, 102, 241, 0.3);
    }
    [data-bs-theme="dark"] .task-action-btn.btn-action-delete:hover {
        background: rgba(244, 63, 94, 0.2);
        color: #fb7185;
        border-color: rgba(244, 63, 94, 0.3);
    }
    [data-bs-theme="dark"] .task-action-btn.btn-action-open:hover {
        background: rgba(34, 197, 94, 0.2);
        color: #4ade80;
        border-color: rgba(34, 197, 94, 0.3);
    }
    [data-bs-theme="dark"] .card-comment-btn {
        background: rgba(255, 255, 255, 0.05);
        border-color: rgba(255, 255, 255, 0.1);
        color: #cbd5e1;
    }
    [data-bs-theme="dark"] .card-comment-btn:hover {
        background: rgba(99, 102, 241, 0.2);
        border-color: rgba(99, 102, 241, 0.4);
        color: #a5b4fc;
    }
    [data-bs-theme="dark"] .kanban-card {
        border-color: rgba(255, 255, 255, 0.08) !important;
    }
    [data-bs-theme="dark"] .kanban-card:hover {
        border-color: rgba(255, 255, 255, 0.18) !important;
        box-shadow: 0 8px 20px -4px rgba(0, 0, 0, 0.4) !important;
    }
    [data-bs-theme="dark"] .task-title-link {
        color: #f1f5f9;
    }
    [data-bs-theme="dark"] .task-title-link:hover {
        color: #818cf8 !important;
    }
</style>
@endpush

<div class="container-fluid px-0">

@php
    $currentUserService = new \App\Services\CurrentUserService();
    $currentUser = $currentUserService->getUser();
    $canManageTasks = $currentUserService->hasPermission('tasks.manage');
    
    $totalTasks = $project->tasks->count();
    $completedTasks = $project->tasks->where('status', 'done')->count();
    $progressPercent = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;
@endphp

    <!-- Alerts -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-3 py-2 px-3 small" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm mb-3 py-2 px-3 small" role="alert">
            <i class="fa-solid fa-triangle-exclamation me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Navigation Breadcrumb -->
    <div class="mb-2">
        <a href="{{ route('projects.index') }}" class="text-secondary text-decoration-none small fw-semibold">
            <i class="fa-solid fa-arrow-left me-1"></i> Back to Projects
        </a>
    </div>

    <!-- Condensed Project Header -->
    <div class="card border-0 shadow-sm rounded-4 mb-3 bg-white">
        <div class="card-body p-3 p-md-4">
            <div class="row align-items-center g-3">
                <div class="col-md-7">
                    <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                        <h4 class="fw-bold text-dark m-0">{{ $project->name }}</h4>
                        @if($project->status === 'active')
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 fw-bold" style="font-size: 0.75rem;">
                                🟢 Active
                            </span>
                        @elseif($project->status === 'planning')
                            <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-2.5 py-1 fw-bold" style="font-size: 0.75rem;">
                                🔵 Planning
                            </span>
                        @elseif($project->status === 'completed')
                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2.5 py-1 fw-bold" style="font-size: 0.75rem;">
                                ⚪ Completed
                            </span>
                        @else
                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2.5 py-1 fw-bold" style="font-size: 0.75rem;">
                                🟡 Suspended
                            </span>
                        @endif

                        @if($project->client_name)
                            <span class="badge bg-light text-dark border rounded-pill px-2.5 py-1" style="font-size: 0.75rem;">
                                <i class="fa-solid fa-user-tie text-secondary me-1"></i> {{ $project->client_name }}
                                @if($project->client_country)
                                    <span class="text-muted fw-normal">({{ $project->client_country }})</span>
                                @endif
                            </span>
                        @endif
                    </div>
                    <p class="text-muted small mb-2" style="max-width: 600px;">
                        {{ $project->description ?: 'No detailed description.' }}
                    </p>

                    <!-- Compact Progress Bar -->
                    <div class="d-flex align-items-center gap-3">
                        <div class="progress rounded-pill bg-light border flex-grow-1" style="height: 8px; max-width: 320px;">
                            <div class="progress-bar rounded-pill" role="progressbar" 
                                 style="width: {{ $progressPercent }}%; background: linear-gradient(90deg, #4F46E5 0%, #7C3AED 100%);">
                            </div>
                        </div>
                        <span class="fw-bold text-primary font-monospace small" style="font-size: 0.8rem;">
                            {{ $progressPercent }}% Progress
                        </span>
                    </div>
                </div>

                <!-- Stats Badges -->
                <div class="col-md-5 text-md-end d-flex flex-wrap align-items-center justify-content-md-end gap-2">
                    @if($project->teamLead)
                        <div class="bg-warning-subtle border border-warning-subtle rounded-3 px-3 py-1.5 text-center">
                            <span class="text-warning-emphasis d-block fw-bold" style="font-size: 0.68rem;"><i class="fa-solid fa-crown text-warning me-1"></i>TEAM LEAD</span>
                            <span class="fw-bold text-dark font-monospace small">{{ $project->teamLead->full_name }}</span>
                        </div>
                    @endif
                    <div class="bg-light border rounded-3 px-3 py-1.5 text-center">
                        <span class="text-muted d-block" style="font-size: 0.7rem;">MEMBERS</span>
                        <span class="fw-bold text-dark font-monospace small"><i class="fa-solid fa-users me-1 text-primary"></i>{{ $project->users->count() }}</span>
                    </div>
                    <div class="bg-light border rounded-3 px-3 py-1.5 text-center">
                        <span class="text-muted d-block" style="font-size: 0.7rem;">START</span>
                        <span class="fw-bold text-dark font-monospace small">{{ $project->start_date ? $project->start_date->format('M d, Y') : '-' }}</span>
                    </div>
                    <div class="bg-light border rounded-3 px-3 py-1.5 text-center">
                        <span class="text-muted d-block" style="font-size: 0.7rem;">TARGET END</span>
                        <span class="fw-bold text-dark font-monospace small">{{ $project->end_date ? $project->end_date->format('M d, Y') : '-' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Layout -->
    <div class="row g-3">
        <!-- Main: Tasks & Bugs Section (Full Width 12 cols for spacious workspace) -->
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-3">
                
                <!-- Main 3 Project Tabs Header -->
                <div class="card-header bg-white py-2 px-3 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <ul class="nav nav-tabs card-header-tabs border-bottom-0" id="projectMainTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active fw-bold text-dark border-0 border-bottom border-3 border-primary py-2.5 px-3 d-flex align-items-center gap-2" id="btn-tasks-main-tab" onclick="switchMainTab('tasks')">
                                <i class="fa-solid fa-list-check text-primary"></i> Tasks & Bugs 
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-semibold text-secondary border-0 py-2.5 px-3 d-flex align-items-center gap-2" id="btn-team-main-tab" onclick="switchMainTab('team')">
                                <i class="fa-solid fa-users text-info"></i> Project Team 
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-semibold text-secondary border-0 py-2.5 px-3 d-flex align-items-center gap-2" id="btn-files-main-tab" onclick="switchMainTab('files')">
                                <i class="fa-solid fa-folder-closed text-warning"></i> Project Files 
                            </button>
                        </li>
                    </ul>

                    <div class="d-flex align-items-center gap-2 py-1">
                        <!-- Sub View Switcher (Only active when inside Tasks & Bugs tab) -->
                        <div id="tasks-subview-switcher" class="btn-group btn-group-sm rounded-pill border bg-light p-0.5" role="group">
                            <button type="button" class="btn btn-sm btn-white active rounded-pill px-2.5 py-1 fw-bold shadow-2xs text-dark" id="btn-list-view" onclick="switchTaskSubView('list')">
                                <i class="fa-solid fa-bars me-1"></i> List
                            </button>
                            <button type="button" class="btn btn-sm btn-transparent rounded-pill px-2.5 py-1 fw-semibold text-muted" id="btn-kanban-view" onclick="switchTaskSubView('kanban')">
                                <i class="fa-solid fa-table-columns me-1"></i> Kanban
                            </button>
                            <button type="button" class="btn btn-sm btn-transparent rounded-pill px-2.5 py-1 fw-semibold text-muted" id="btn-bugs-view" onclick="switchTaskSubView('bugs')">
                                <i class="fa-solid fa-bug me-1 text-danger"></i> Bugs
                            </button>
                        </div>

                        <button type="button" class="btn btn-outline-danger btn-sm rounded-3 px-2.5 py-1 fw-bold d-flex align-items-center gap-1" style="font-size: 0.8rem;" data-bs-toggle="modal" data-bs-target="#reportBugModal" onclick="openReportBugModal(null)">
                            <i class="fa-solid fa-bug"></i> Report Bug
                        </button>

                        @if($canManageTasks)
                            <button type="button" class="btn btn-primary btn-sm rounded-3 px-3 py-1 fw-semibold d-flex align-items-center gap-1.5 shadow-sm" style="font-size: 0.8rem;" data-bs-toggle="modal" data-bs-target="#addTaskModal">
                                <i class="fa-solid fa-plus"></i> Add Task
                            </button>
                        @endif
                    </div>
                </div>

                <!-- TASK SEARCH & FILTER TOOLBAR -->
                <div class="px-3 py-2 bg-light-subtle border-bottom d-flex flex-wrap align-items-center justify-content-between gap-2" id="task-filter-bar">
                    <div class="d-flex align-items-center gap-2 flex-grow-1" style="max-width: 420px;">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                            <input type="text" class="form-control form-control-sm border-start-0 ps-0 bg-white" id="task-live-search" placeholder="Search tasks by keyword..." oninput="applyTaskFilters()">
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <select class="form-select form-select-sm bg-white" id="task-assignee-filter" onchange="applyTaskFilters()" style="width: auto; min-width: 135px; font-size: 0.8rem;">
                            <option value="">All Assignees</option>
                            @foreach($project->users as $u)
                                <option value="{{ $u->id }}">{{ $u->first_name }}</option>
                            @endforeach
                            <option value="unassigned">Unassigned</option>
                        </select>

                        <select class="form-select form-select-sm bg-white" id="task-priority-filter" onchange="applyTaskFilters()" style="width: auto; min-width: 120px; font-size: 0.8rem;">
                            <option value="">All Priorities</option>
                            <option value="high">High Priority</option>
                            <option value="medium">Medium Priority</option>
                            <option value="low">Low Priority</option>
                        </select>

                        <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2.5 text-nowrap" onclick="resetTaskFilters()" title="Reset Filters" style="font-size: 0.75rem;">
                            <i class="fa-solid fa-rotate-left me-1"></i> Reset
                        </button>
                    </div>
                </div>

                <!-- LIST VIEW CONTAINER -->
                <div id="tasks-list-container">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light text-uppercase text-muted fw-bold" style="font-size: 0.72rem; letter-spacing: 0.5px;">
                                <tr>
                                    <th scope="col" class="ps-4 py-3" style="width: 35%;">Task & Description</th>
                                    <th scope="col" class="py-3 text-center" style="width: 15%;">Status</th>
                                    <th scope="col" class="py-3 text-center" style="width: 12%;">Priority</th>
                                    <th scope="col" class="py-3" style="width: 18%;">Assignee</th>
                                    <th scope="col" class="pe-4 py-3 text-end" style="width: 20%;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($project->tasks as $task)
                                    <tr id="list-task-{{ $task->id }}" class="task-filter-item" data-task-title="{{ strtolower($task->title . ' ' . ($task->description ?? '')) }}" data-assignee-id="{{ $task->user_id ?? 'unassigned' }}" data-priority="{{ $task->priority }}">
                                        <td class="ps-4 py-3 cursor-pointer" onclick="openTaskDrawer({{ $task->id }})" style="cursor: pointer;" title="Click to open task details">
                                            <div class="fw-bold text-dark fs-7 mb-0.5 hover-text-primary text-decoration-none d-inline-block">
                                                {{ $task->title }} <i class="fa-solid fa-arrow-up-right-from-square ms-1 text-muted opacity-50" style="font-size: 0.65rem;"></i>
                                            </div>
                                            @if($task->description)
                                                <small class="text-muted d-block text-truncate" style="max-width: 340px; font-size: 0.78rem;">
                                                    {{ $task->description }}
                                                </small>
                                            @endif
                                        </td>
                                        <td class="py-3 text-center status-badge-cell">
                                            @if($task->status === 'done')
                                                <span class="badge bg-success text-white rounded-pill px-2.5 py-1 text-uppercase fw-bold" style="font-size: 0.65rem;">Done</span>
                                            @elseif($task->status === 'review')
                                                <span class="badge bg-info text-white rounded-pill px-2.5 py-1 text-uppercase fw-bold" style="font-size: 0.65rem;">Review</span>
                                            @elseif($task->status === 'reopened')
                                                <span class="badge bg-danger text-white rounded-pill px-2.5 py-1 text-uppercase fw-bold" style="font-size: 0.65rem;">🔴 Reopened</span>
                                            @elseif($task->status === 'in_progress')
                                                <span class="badge bg-primary text-white rounded-pill px-2.5 py-1 text-uppercase fw-bold" style="font-size: 0.65rem;">In Progress</span>
                                            @else
                                                <span class="badge bg-light text-dark border rounded-pill px-2.5 py-1 text-uppercase fw-bold" style="font-size: 0.65rem;">To Do</span>
                                            @endif
                                        </td>
                                        <td class="py-3 text-center">
                                            @if($task->priority === 'high')
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2 py-0.5 text-uppercase fw-bold" style="font-size: 0.65rem;">High</span>
                                            @elseif($task->priority === 'medium')
                                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2 py-0.5 text-uppercase fw-bold" style="font-size: 0.65rem;">Medium</span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2 py-0.5 text-uppercase fw-bold" style="font-size: 0.65rem;">Low</span>
                                            @endif
                                        </td>
                                        <td class="py-3">
                                            @if($task->assignee)
                                                <div class="d-inline-flex align-items-center gap-1.5 bg-light border rounded-pill px-2.5 py-1" style="font-size: 0.78rem;" title="{{ $task->assignee->full_name }}">
                                                    <i class="fa-solid fa-user text-primary" style="font-size: 0.7rem;"></i>
                                                    <span class="fw-semibold text-dark">{{ $task->assignee->first_name }}</span>
                                                </div>
                                            @else
                                                <span class="text-muted small fst-italic" style="font-size: 0.75rem;">Unassigned</span>
                                            @endif
                                        </td>
                                        <td class="pe-4 py-3 text-end">
                                            @php
                                                $canEditTask = $canManageTasks && 
                                                               ($currentUser->role?->code !== 'employee' || $task->user_id === $currentUser->id);
                                            @endphp
                                            <div class="d-inline-flex align-items-center gap-1.5">
                                                <button type="button" class="task-action-btn btn-action-open" 
                                                        onclick="openTaskDrawer({{ $task->id }})" 
                                                        title="Open Task Details">
                                                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                                </button>
                                                <button type="button" class="card-comment-btn" 
                                                        onclick="openTaskDrawer({{ $task->id }}, true)" 
                                                        title="Comments & Discussion">
                                                    <i class="fa-regular fa-comment text-primary"></i>
                                                    <span>{{ $task->comments->count() }}</span>
                                                </button>
                                                <button type="button" class="task-action-btn btn-action-bug" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#reportBugModal" 
                                                        onclick="openReportBugModal({{ $task->id }})" 
                                                        title="Report Bug / Reopen Task">
                                                    <i class="fa-solid fa-bug"></i>
                                                </button>
                                                @if($canEditTask)
                                                    <button type="button" 
                                                            class="task-action-btn btn-action-edit edit-task-btn" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#editTaskModal"
                                                            data-id="{{ $task->id }}"
                                                            data-title="{{ $task->title }}"
                                                            data-description="{{ $task->description }}"
                                                            data-user-id="{{ $task->user_id }}"
                                                            data-status="{{ $task->status }}"
                                                            data-priority="{{ $task->priority }}"
                                                            data-due-date="{{ $task->due_date ? $task->due_date->format('Y-m-d') : '' }}"
                                                            title="Edit Task">
                                                        <i class="fa-solid fa-pen"></i>
                                                    </button>
                                                    <form action="{{ route('tasks.destroy', $task->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete task?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="task-action-btn btn-action-delete" title="Delete Task">
                                                            <i class="fa-solid fa-trash"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">No tasks created yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- KANBAN BOARD CONTAINER (Hidden by default) -->
                <div id="tasks-kanban-container" class="p-3 bg-light-subtle d-none">
                    @php
                        $kanbanColumns = [
                            'todo' => ['title' => 'To Do', 'icon' => 'fa-list-check', 'color' => 'secondary'],
                            'in_progress' => ['title' => 'In Progress', 'icon' => 'fa-spinner', 'color' => 'primary'],
                            'review' => ['title' => 'In Review', 'icon' => 'fa-magnifying-glass', 'color' => 'info'],
                            'reopened' => ['title' => 'Reopened', 'icon' => 'fa-circle-exclamation', 'color' => 'danger'],
                            'done' => ['title' => 'Done', 'icon' => 'fa-circle-check', 'color' => 'success'],
                        ];
                    @endphp

                    <div class="row g-2 overflow-x-auto flex-nowrap pb-2">
                        @foreach($kanbanColumns as $statusKey => $col)
                            @php
                                $colTasks = $project->tasks->where('status', $statusKey);
                            @endphp
                            <div class="col-10 col-md-4 col-xl-3" style="min-width: 250px;">
                                <div class="card border border-light-subtle shadow-2xs rounded-3 bg-light h-100 kanban-column" 
                                     data-status="{{ $statusKey }}"
                                     ondragover="handleDragOver(event)" 
                                     ondragleave="handleDragLeave(event)" 
                                     ondrop="handleDrop(event, '{{ $statusKey }}')">
                                    
                                    <div class="card-header bg-white border-bottom py-2.5 px-3 d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge bg-{{ $col['color'] }}-subtle text-{{ $col['color'] }} p-1.5 rounded-circle">
                                                <i class="fa-solid {{ $col['icon'] }}" style="font-size: 0.75rem;"></i>
                                            </span>
                                            <h6 class="fw-bold text-dark mb-0 fs-7">{{ $col['title'] }}</h6>
                                        </div>
                                        <span class="badge bg-white text-muted border rounded-pill font-monospace column-count" id="kanban-count-{{ $statusKey }}">
                                            {{ $colTasks->count() }}
                                        </span>
                                    </div>

                                    <div class="card-body p-2 kanban-cards-wrapper d-flex flex-column gap-2" style="min-height: 250px;" id="kanban-wrapper-{{ $statusKey }}">
                                        @foreach($colTasks as $task)
                                            @php
                                                $canEditTask = $canManageTasks && 
                                                               ($currentUser->role?->code !== 'employee' || $task->user_id === $currentUser->id);
                                            @endphp
                                            <div class="card border rounded-3 bg-white shadow-2xs p-3 kanban-card cursor-grab task-filter-item" 
                                                 id="kanban-task-{{ $task->id }}" 
                                                 data-task-id="{{ $task->id }}"
                                                 data-task-title="{{ strtolower($task->title . ' ' . ($task->description ?? '')) }}"
                                                 data-assignee-id="{{ $task->user_id ?? 'unassigned' }}"
                                                 data-priority="{{ $task->priority }}"
                                                 draggable="{{ $canEditTask ? 'true' : 'false' }}"
                                                 ondragstart="handleDragStart(event, {{ $task->id }})"
                                                 ondragend="handleDragEnd(event)">
                                                
                                                <div class="d-flex align-items-center justify-content-between mb-1.5">
                                                    @if($task->priority === 'high')
                                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill font-monospace" style="font-size: 0.65rem;">HIGH</span>
                                                    @elseif($task->priority === 'medium')
                                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill font-monospace" style="font-size: 0.65rem;">MEDIUM</span>
                                                    @else
                                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill font-monospace" style="font-size: 0.65rem;">LOW</span>
                                                    @endif

                                                    <div class="d-flex align-items-center gap-1">
                                                        <button type="button" class="task-action-btn btn-action-bug" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#reportBugModal" 
                                                                onclick="openReportBugModal({{ $task->id }})" 
                                                                title="Report Bug / Reopen">
                                                            <i class="fa-solid fa-bug"></i>
                                                        </button>
                                                        @if($canEditTask)
                                                            <button type="button" 
                                                                    class="task-action-btn btn-action-edit edit-task-btn" 
                                                                    data-bs-toggle="modal" 
                                                                    data-bs-target="#editTaskModal"
                                                                    data-id="{{ $task->id }}"
                                                                    data-title="{{ $task->title }}"
                                                                    data-description="{{ $task->description }}"
                                                                    data-user-id="{{ $task->user_id }}"
                                                                    data-status="{{ $task->status }}"
                                                                    data-priority="{{ $task->priority }}"
                                                                    data-due-date="{{ $task->due_date ? $task->due_date->format('Y-m-d') : '' }}"
                                                                    title="Edit Task">
                                                                <i class="fa-solid fa-pen"></i>
                                                            </button>
                                                            <form action="{{ route('tasks.destroy', $task->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete task?');">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="task-action-btn btn-action-delete" title="Delete Task">
                                                                    <i class="fa-solid fa-trash"></i>
                                                                </button>
                                                            </form>
                                                            <span class="kanban-drag-grip" title="Drag to reorder">
                                                                <i class="fa-solid fa-grip-vertical"></i>
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>

                                                <h6 class="fw-bold mb-1 task-title-link" style="font-size: 0.85rem;" onclick="openTaskDrawer({{ $task->id }})" title="Click to open task details">
                                                    {{ $task->title }}
                                                </h6>

                                                @if($task->description)
                                                    <p class="text-muted small mb-2 text-truncate" style="font-size: 0.75rem; max-width: 100%; cursor: pointer;" onclick="openTaskDrawer({{ $task->id }})">{{ $task->description }}</p>
                                                @endif

                                                <div class="d-flex align-items-center justify-content-between pt-2 border-top mt-auto" style="font-size: 0.75rem;">
                                                    <div class="d-flex align-items-center gap-1">
                                                        @if($task->assignee)
                                                            <span class="badge bg-light text-dark border rounded-pill font-monospace fw-semibold" style="font-size: 0.68rem;" title="{{ $task->assignee->full_name }}">
                                                                <i class="fa-solid fa-user me-1 text-primary"></i>{{ $task->assignee->first_name }}
                                                            </span>
                                                        @else
                                                            <span class="text-muted fst-italic" style="font-size: 0.7rem;">Unassigned</span>
                                                        @endif
                                                    </div>

                                                    <div class="d-flex align-items-center gap-2 text-muted">
                                                        @if($task->due_date)
                                                            <span class="font-monospace" title="Due Date" style="font-size: 0.7rem;">
                                                                <i class="fa-regular fa-calendar me-0.5"></i>{{ $task->due_date->format('M d') }}
                                                            </span>
                                                        @endif
                                                        <button type="button" class="card-comment-btn" 
                                                                onclick="openTaskDrawer({{ $task->id }}, true)" 
                                                                title="Comments & Discussion">
                                                            <i class="fa-regular fa-comment text-primary"></i>
                                                            <span>{{ $task->comments->count() }}</span>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- BUGS & ISSUES LIST CONTAINER (Hidden by default) -->
                <div id="project-bugs-container" class="d-none">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size: 0.875rem;">
                            <thead class="table-light text-uppercase text-muted fw-bold" style="font-size: 0.7rem; letter-spacing: 0.5px;">
                                <tr>
                                    <th class="ps-3 py-2.5">Bug ID</th>
                                    <th class="py-2.5">Bug Description</th>
                                    <th class="py-2.5">Linked Task</th>
                                    <th class="py-2.5">Reported By</th>
                                    <th class="py-2.5 text-center">Severity</th>
                                    <th class="py-2.5 text-center">Status</th>
                                    <th class="pe-3 py-2.5 text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($project->bugs as $bug)
                                    <tr>
                                        <td class="ps-3 py-2.5 font-monospace fw-bold text-danger">
                                            {{ $bug->bug_number }}
                                        </td>
                                        <td class="py-2.5">
                                            <h6 class="mb-0 fw-bold text-dark fs-7">{{ $bug->title }}</h6>
                                            <small class="text-muted d-block text-truncate" style="max-width: 280px;">{{ $bug->description }}</small>
                                        </td>
                                        <td class="py-2.5">
                                            @if($bug->task)
                                                <span class="badge bg-light text-dark border rounded-pill font-monospace" style="font-size: 0.72rem;">
                                                    <i class="fa-solid fa-list-check me-1 text-primary"></i>#{{ $bug->task->id }} - {{ Str::limit($bug->task->title, 20) }}
                                                </span>
                                            @else
                                                <span class="text-muted small fst-italic">Project General</span>
                                            @endif
                                        </td>
                                        <td class="py-2.5">
                                            <span class="fw-semibold text-dark">{{ $bug->reporter?->first_name ?? 'N/A' }}</span>
                                            <small class="text-muted d-block font-monospace" style="font-size: 0.7rem;">{{ $bug->created_at->format('M d, H:i') }}</small>
                                        </td>
                                        <td class="py-2.5 text-center">
                                            @if($bug->severity === 'critical')
                                                <span class="badge bg-danger text-white rounded-pill px-2.5 py-0.5 fw-bold font-monospace" style="font-size: 0.65rem;">CRITICAL</span>
                                            @elseif($bug->severity === 'high')
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2 py-0.5 fw-bold font-monospace" style="font-size: 0.65rem;">HIGH</span>
                                            @elseif($bug->severity === 'medium')
                                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2 py-0.5 fw-bold font-monospace" style="font-size: 0.65rem;">MEDIUM</span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2 py-0.5 fw-bold font-monospace" style="font-size: 0.65rem;">LOW</span>
                                            @endif
                                        </td>
                                        <td class="py-2.5 text-center">
                                            <form action="{{ route('bugs.update-status', $bug->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <select name="status" class="form-select form-select-sm rounded-pill font-monospace fw-bold py-0 px-2" style="font-size: 0.72rem;" onchange="this.form.submit()">
                                                    <option value="open" {{ $bug->status === 'open' ? 'selected' : '' }}>🔴 Open</option>
                                                    <option value="in_progress" {{ $bug->status === 'in_progress' ? 'selected' : '' }}>🟡 Fixing</option>
                                                    <option value="resolved" {{ $bug->status === 'resolved' ? 'selected' : '' }}>🟢 Resolved</option>
                                                    <option value="closed" {{ $bug->status === 'closed' ? 'selected' : '' }}>⚪ Closed</option>
                                                </select>
                                            </form>
                                        </td>
                                        <td class="pe-3 py-2.5 text-end">
                                            <div class="d-inline-flex align-items-center gap-1.5">
                                                <a href="{{ route('bugs.show', $bug->id) }}" class="btn btn-sm btn-outline-primary border-0 p-1 rounded-2" title="View Bug Details Page">
                                                    <i class="fa-solid fa-eye" style="font-size: 0.85rem;"></i>
                                                </a>
                                                <form action="{{ route('bugs.destroy', $bug->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete bug report?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger border-0 p-1 rounded-2" title="Delete Bug Report">
                                                        <i class="fa-solid fa-trash" style="font-size: 0.85rem;"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">
                                            <i class="fa-solid fa-bug fs-3 text-secondary opacity-50 mb-2 d-block"></i>
                                            <p class="mb-0 small">No bugs reported for this project.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>

                <!-- TEAM MEMBERS LIST CONTAINER (Hidden by default) -->
                <div id="project-team-container" class="d-none">
                    <div class="p-3">
                        <div class="row g-3">
                            @php
                                $sortedMembers = $project->users->sort(function ($a, $b) use ($project) {
                                    if ($project->team_lead_id) {
                                        if ($a->id === $project->team_lead_id) return -1;
                                        if ($b->id === $project->team_lead_id) return 1;
                                    }
                                    return strcasecmp($a->first_name ?? '', $b->first_name ?? '');
                                });
                            @endphp
                            @forelse($sortedMembers as $member)
                                @php
                                    $isLead = ($project->team_lead_id && $member->id === $project->team_lead_id);
                                @endphp
                                <div class="col-md-6 col-lg-4">
                                    <div class="card {{ $isLead ? 'border-warning shadow-xs' : 'border shadow-2xs bg-white' }} rounded-3 p-3 hover-shadow transition-all position-relative" style="{{ $isLead ? 'background-color: #fffdf5;' : '' }}">
                                        @if($isLead)
                                            <span class="position-absolute top-0 end-0 translate-middle-y me-3 badge bg-warning text-dark border border-warning-subtle rounded-pill px-2.5 py-1 shadow-2xs" style="font-size: 0.68rem;">
                                                <i class="fa-solid fa-crown me-1 text-dark"></i> Team Lead
                                            </span>
                                        @endif
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold shadow-2xs flex-shrink-0"
                                                 style="width: 42px; height: 42px; font-size: 1.1rem; background: {{ $isLead ? 'linear-gradient(135deg, #F59E0B 0%, #D97706 100%)' : 'linear-gradient(135deg, #4F46E5 0%, #7C3AED 100%)' }};">
                                                {{ strtoupper(substr($member->first_name, 0, 1)) }}
                                            </div>
                                            <div class="flex-grow-1 text-truncate">
                                                <h6 class="fw-bold text-dark mb-0 text-truncate" style="font-size: 0.9rem;">
                                                    {{ $member->full_name }}
                                                </h6>
                                                <small class="text-muted d-block text-truncate" style="font-size: 0.78rem;">
                                                    {{ $member->display_designation }}
                                                </small>
                                                <div class="d-flex align-items-center gap-1.5 mt-1 flex-wrap">
                                                    @if($member->department)
                                                        <span class="badge bg-light text-secondary border rounded-pill px-2 py-0.5" style="font-size: 0.68rem;">
                                                            {{ $member->department->name }}
                                                        </span>
                                                    @endif
                                                    @if($isLead)
                                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2 py-0.5" style="font-size: 0.68rem;">
                                                            Lead
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12 text-center py-5 text-muted">
                                    <i class="fa-solid fa-users fs-2 text-secondary opacity-50 mb-2 d-block"></i>
                                    <p class="mb-0">No team members assigned to this project.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- PROJECT FILES CONTAINER (Hidden by default) -->
                <div id="project-files-container" class="d-none">
                    <div class="p-3 bg-light-subtle border-bottom d-flex justify-content-between align-items-center">
                        <span class="fw-bold text-dark small"><i class="fa-solid fa-folder-closed text-warning me-1"></i> Attached Documents & Assets</span>
                        <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 py-1 fw-semibold" style="font-size: 0.78rem;" data-bs-toggle="modal" data-bs-target="#uploadFileModal">
                            <i class="fa-solid fa-cloud-arrow-up me-1"></i> Upload File
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light text-uppercase text-muted fw-bold" style="font-size: 0.72rem; letter-spacing: 0.5px;">
                                <tr>
                                    <th scope="col" class="ps-4 py-3">File Name</th>
                                    <th scope="col" class="py-3">Type</th>
                                    <th scope="col" class="py-3">Uploaded By</th>
                                    <th scope="col" class="py-3">Size</th>
                                    <th scope="col" class="pe-4 py-3 text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($project->files as $file)
                                    <tr>
                                        <td class="ps-4 py-3">
                                            <div class="d-flex align-items-center gap-2.5">
                                                @if($file->file_type === 'image')
                                                    <i class="fa-solid fa-file-image text-info fs-5"></i>
                                                @elseif($file->file_type === 'document')
                                                    <i class="fa-solid fa-file-pdf text-danger fs-5"></i>
                                                @elseif($file->file_type === 'archive')
                                                    <i class="fa-solid fa-file-zipper text-warning fs-5"></i>
                                                @elseif($file->file_type === 'code')
                                                    <i class="fa-solid fa-file-code text-primary fs-5"></i>
                                                @else
                                                    <i class="fa-solid fa-file text-secondary fs-5"></i>
                                                @endif
                                                <a href="{{ asset('storage/' . $file->file_path) }}" target="_blank" class="fw-bold text-dark text-decoration-none hover-primary" style="font-size: 0.85rem;" title="{{ $file->name }}">
                                                    {{ $file->name }}
                                                </a>
                                            </div>
                                        </td>
                                        <td class="py-3 text-uppercase font-monospace text-muted" style="font-size: 0.75rem;">
                                            {{ $file->file_type }}
                                        </td>
                                        <td class="py-3">
                                            <span class="fw-semibold text-dark" style="font-size: 0.82rem;">{{ $file->uploader?->full_name ?? 'User' }}</span>
                                            <small class="text-muted d-block font-monospace" style="font-size: 0.7rem;">{{ $file->created_at->format('M d, Y') }}</small>
                                        </td>
                                        <td class="py-3 font-monospace small">
                                            {{ $file->formatted_size }}
                                        </td>
                                        <td class="pe-4 py-3 text-end">
                                            <div class="d-inline-flex align-items-center gap-2">
                                                <a href="{{ asset('storage/' . $file->file_path) }}" download class="btn btn-sm btn-outline-primary rounded-2 px-2 py-1" title="Download">
                                                    <i class="fa-solid fa-download"></i>
                                                </a>
                                                @if($file->user_id === $currentUser->id || in_array(strtolower($currentUser->role?->code ?? ''), ['admin', 'owner', 'hr']))
                                                    <form action="{{ route('projects.files.destroy', [$project->id, $file->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this file?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-2 px-2 py-1" title="Delete">
                                                            <i class="fa-solid fa-trash"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted">
                                            <i class="fa-solid fa-cloud-arrow-up fs-2 text-secondary opacity-50 mb-2 d-block"></i>
                                            <p class="mb-0">No files attached to this project yet.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Right-side Sliding Drawer: Full Task Details & Discussion -->
<div class="offcanvas offcanvas-end border-0 shadow-lg d-flex flex-column" tabindex="-1" id="taskDetailsDrawer" aria-labelledby="taskDetailsDrawerLabel" style="width: 560px; max-width: 95vw;">
    <!-- Fixed Header -->
    <div class="offcanvas-header bg-white border-bottom px-4 py-3 align-items-center flex-shrink-0">
        <div class="d-flex align-items-center gap-2 flex-wrap flex-grow-1 overflow-hidden pe-2">
            <span class="badge bg-light text-secondary border font-monospace small px-2 py-1" id="taskDrawerId">#TASK</span>
            
            <!-- Quick Status Switcher in Drawer -->
            <select class="form-select form-select-sm rounded-pill font-monospace fw-bold py-1 px-2.5 shadow-2xs" id="taskDrawerStatusSelect" style="width: auto; min-width: 125px; font-size: 0.75rem;" onchange="updateDrawerTaskStatus(this.value)">
                <option value="todo">⚪ To Do</option>
                <option value="in_progress">🔵 In Progress</option>
                <option value="review">🟣 In Review</option>
                <option value="reopened">🔴 Reopened</option>
                <option value="done">🟢 Done</option>
            </select>

            <span id="taskDrawerPriorityBadge"></span>
        </div>
        
        <div class="d-flex align-items-center gap-2 flex-shrink-0">
            <button type="button" class="btn btn-sm btn-outline-primary border rounded-2 p-1.5" id="taskDrawerEditBtn" title="Edit Task">
                <i class="fa-solid fa-pen" style="font-size: 0.82rem;"></i>
            </button>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
    </div>
    
    <!-- Middle Scrollable Body (Scrolls cleanly without being cut off by footer) -->
    <div class="offcanvas-body p-4 flex-grow-1 bg-white" style="overflow-y: auto;">
        <!-- Task Title -->
        <h5 class="fw-bold text-dark mb-3" id="taskDrawerTitle" style="line-height: 1.35;"></h5>

        <!-- Meta Information Card -->
        <div class="card border rounded-3 p-3 bg-light-subtle mb-4 shadow-2xs">
            <div class="row g-2">
                <div class="col-sm-6">
                    <small class="text-muted d-block" style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.5px;">Assignee</small>
                    <div class="d-flex align-items-center gap-2 mt-1">
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold shadow-2xs" style="width: 28px; height: 28px; font-size: 0.75rem;" id="taskDrawerAssigneeAvatar">
                            U
                        </div>
                        <div class="overflow-hidden text-truncate">
                            <span class="fw-bold text-dark small d-block text-truncate" id="taskDrawerAssigneeName">Unassigned</span>
                            <small class="text-muted d-block text-truncate" style="font-size: 0.7rem;" id="taskDrawerAssigneeRole">-</small>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <small class="text-muted d-block" style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.5px;">Due Date</small>
                    <div class="d-flex align-items-center gap-1.5 mt-1.5">
                        <i class="fa-regular fa-calendar text-primary"></i>
                        <span class="fw-semibold text-dark small font-monospace" id="taskDrawerDueDate">No deadline</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Description Section -->
        <div class="mb-4">
            <h6 class="fw-bold text-dark small text-uppercase mb-2" style="letter-spacing: 0.5px;">
                <i class="fa-solid fa-align-left text-primary me-1.5"></i> Description
            </h6>
            <div class="bg-light-subtle p-3 rounded-3 border text-secondary small shadow-2xs" id="taskDrawerDescription" style="white-space: pre-wrap; font-size: 0.85rem; line-height: 1.5;">
                No description provided.
            </div>
        </div>

        <!-- Subtasks / Checklists Section -->
        <div class="mb-4">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <h6 class="fw-bold text-dark small text-uppercase mb-0" style="letter-spacing: 0.5px;">
                    <i class="fa-solid fa-list-check text-success me-1.5"></i> Subtasks / Checklist
                </h6>
                <span class="badge bg-light text-secondary border font-monospace small" id="taskDrawerChecklistProgress">0/0</span>
            </div>
            
            <!-- Progress bar for checklists -->
            <div class="progress rounded-pill bg-light border mb-2.5" style="height: 6px;" id="taskDrawerChecklistProgressWrapper">
                <div class="progress-bar bg-success rounded-pill" role="progressbar" id="taskDrawerChecklistProgressBar" style="width: 0%;"></div>
            </div>

            <!-- Checklist items list -->
            <div id="taskDrawerChecklistsContainer" class="d-flex flex-column gap-1.5 mb-2">
                <!-- Dynamic checklists -->
            </div>

            <!-- Add Subtask Input Form -->
            <form id="taskDrawerAddChecklistForm" onsubmit="handleDrawerAddChecklist(event)" class="d-flex gap-2 mt-2">
                <input type="text" id="taskDrawerNewChecklistInput" class="form-control form-control-sm bg-light-subtle" placeholder="+ Add a subtask/step (press Enter)..." style="font-size: 0.82rem;">
                <button type="submit" class="btn btn-sm btn-outline-primary px-3 fw-bold" style="font-size: 0.78rem;">Add</button>
            </form>
        </div>

        <!-- Dedicated Linked Bugs Section (Shows when there are bugs linked to this task) -->
        <div class="mb-4 d-none" id="taskDrawerLinkedBugsSection">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <h6 class="fw-bold text-danger small text-uppercase mb-0" style="letter-spacing: 0.5px;">
                    <i class="fa-solid fa-bug text-danger me-1.5"></i> Linked Bugs & Issues
                </h6>
                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill font-monospace small" id="taskDrawerBugsCount">0 bugs</span>
            </div>
            <div id="taskDrawerBugsList" class="d-flex flex-column gap-2">
                <!-- Dynamic Bug cards -->
            </div>
        </div>

        <hr class="text-muted opacity-20 my-4">

        <!-- Comments & Discussion Section -->
        <div class="mb-2" id="taskDrawerCommentsSection">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h6 class="fw-bold text-dark small text-uppercase mb-0" style="letter-spacing: 0.5px;">
                    <i class="fa-regular fa-comments text-info me-1.5"></i> Activity & Comments
                </h6>
                <span class="badge bg-light text-secondary border font-monospace" id="taskDrawerCommentCount" style="font-size: 0.72rem;">0 comments</span>
            </div>

            <!-- Comments Feed -->
            <div id="taskDrawerCommentsFeed" class="d-flex flex-column gap-3 mb-2">
                <!-- Dynamic comments -->
            </div>
        </div>
    </div>

    <!-- Fixed Bottom Comment Input Box (Outside body, never overlaps content) -->
    <div class="p-3 bg-white border-top flex-shrink-0 shadow-sm">
        <form id="taskDrawerCommentForm" method="POST">
            @csrf
            <div class="mb-2">
                <textarea name="comment" id="taskDrawerCommentInput" rows="2" class="form-control rounded-3 bg-light-subtle" placeholder="Write a comment, reply or update..." required style="font-size: 0.82rem; resize: none;"></textarea>
            </div>
            <div class="d-flex justify-content-between align-items-center">
                <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-3 py-1 fw-bold" style="font-size: 0.75rem;" onclick="openReportBugForCurrentDrawerTask()">
                    <i class="fa-solid fa-bug me-1"></i> Report Bug
                </button>
                <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3.5 py-1 fw-bold" style="font-size: 0.78rem;">
                    <i class="fa-solid fa-paper-plane me-1"></i> Post Comment
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Upload File -->
<div class="modal fade" id="uploadFileModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <form action="{{ route('projects.files.store', $project->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header border-bottom px-4 py-3 bg-light">
                    <h5 class="modal-title fw-bold text-dark fs-6"><i class="fa-solid fa-cloud-arrow-up text-primary me-2"></i>Upload Project Document / File</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small">Select File (Max 20MB)</label>
                        <input type="file" name="file" class="form-control rounded-3" required>
                        <small class="text-muted d-block mt-1">Supports PDF, Images, Word Docs, Zip Archives, Source Code files.</small>
                    </div>
                </div>
                <div class="modal-footer border-top px-4 py-3 bg-light">
                    <button type="button" class="btn btn-light fw-semibold rounded-3 px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold rounded-3 px-4">Upload File</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Add Task -->
@if($canManageTasks)
    <div class="modal fade" id="addTaskModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <form action="{{ route('tasks.store', $project->id) }}" method="POST">
                    @csrf
                    <div class="modal-header border-bottom px-4 py-3 bg-light">
                        <h5 class="modal-title fw-bold text-dark fs-5"><i class="fa-solid fa-plus-circle text-primary me-2"></i>Add Task</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark small">Task Title</label>
                            <input type="text" name="title" class="form-control rounded-3" placeholder="Enter task title..." required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark small">Description</label>
                            <textarea name="description" rows="3" class="form-control rounded-3" placeholder="Add task description..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark small">Assignee</label>
                            <select name="user_id" class="form-select rounded-3">
                                <option value="">Unassigned</option>
                                @foreach($project->users as $user)
                                    <option value="{{ $user->id }}">{{ $user->full_name }} ({{ $user->display_designation }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold text-dark small">Status</label>
                                <select name="status" class="form-select rounded-3">
                                    <option value="todo">To Do</option>
                                    <option value="in_progress">In Progress</option>
                                    <option value="review">Review</option>
                                    <option value="done">Done</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold text-dark small">Priority</label>
                                <select name="priority" class="form-select rounded-3">
                                    <option value="low">Low</option>
                                    <option value="medium" selected>Medium</option>
                                    <option value="high">High</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark small">Due Date</label>
                            <input type="date" name="due_date" class="form-control rounded-3">
                        </div>
                    </div>
                    <div class="modal-footer border-top px-4 py-3 bg-light">
                        <button type="button" class="btn btn-light fw-semibold rounded-3 px-3" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary fw-bold rounded-3 px-4">Create Task</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal: Edit Task -->
    <div class="modal fade" id="editTaskModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <form id="editTaskForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header border-bottom px-4 py-3 bg-light">
                        <h5 class="modal-title fw-bold text-dark fs-5"><i class="fa-solid fa-pen-to-square text-primary me-2"></i>Edit Task</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark small">Task Title</label>
                            <input type="text" name="title" id="edit_title" class="form-control rounded-3" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark small">Description</label>
                            <textarea name="description" id="edit_description" rows="3" class="form-control rounded-3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark small">Assignee</label>
                            <select name="user_id" id="edit_user_id" class="form-select rounded-3">
                                <option value="">Unassigned</option>
                                @foreach($project->users as $user)
                                    <option value="{{ $user->id }}">{{ $user->full_name }} ({{ $user->display_designation }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold text-dark small">Status</label>
                                <select name="status" id="edit_status" class="form-select rounded-3">
                                    <option value="todo">To Do</option>
                                    <option value="in_progress">In Progress</option>
                                    <option value="review">Review</option>
                                    <option value="done">Done</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold text-dark small">Priority</label>
                                <select name="priority" id="edit_priority" class="form-select rounded-3">
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark small">Due Date</label>
                            <input type="date" name="due_date" id="edit_due_date" class="form-control rounded-3">
                        </div>
                    </div>
                    <div class="modal-footer border-top px-4 py-3 bg-light">
                        <button type="button" class="btn btn-light fw-semibold rounded-3 px-3" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary fw-bold rounded-3 px-4">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif
<!-- Modal: Report Bug / Reopen Task -->
<div class="modal fade" id="reportBugModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <form action="{{ route('projects.bugs.store', $project->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="task_id" id="bug_task_id" value="">
                <div class="modal-header border-bottom px-4 py-3 bg-danger text-white">
                    <h5 class="modal-title fw-bold fs-6"><i class="fa-solid fa-bug me-2"></i>Report Bug / Reopen Task</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4" style="max-height: 75vh;">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold text-dark small">Linked Task</label>
                            <select id="bug_task_id_select" class="form-select rounded-3" onchange="document.getElementById('bug_task_id').value = this.value">
                                <option value="">General Project Bug (No Task)</option>
                                @foreach($project->tasks as $t)
                                    <option value="{{ $t->id }}">#{{ $t->id }} - {{ $t->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold text-dark small">Severity Level <span class="text-danger">*</span></label>
                            <select name="severity" class="form-select rounded-3" required>
                                <option value="low">Low Severity</option>
                                <option value="medium" selected>Medium Severity</option>
                                <option value="high">High Severity</option>
                                <option value="critical">Critical / Blocking</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small">Bug / Issue Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control rounded-3" placeholder="Summary of the issue..." required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small">Detailed Description <span class="text-danger">*</span></label>
                        <textarea name="description" rows="3" class="form-control rounded-3" placeholder="Explain what failed or steps to reproduce..." required></textarea>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-bold text-dark small">Upload Screenshot / Image (Optional)</label>
                        <input type="file" name="screenshot" class="form-control rounded-3" accept="image/*">
                        <small class="text-muted d-block mt-1">Attach proof or error screenshot (JPG, PNG, WebP max 5MB).</small>
                    </div>
                </div>
                <div class="modal-footer border-top px-4 py-2.5 bg-light">
                    <button type="button" class="btn btn-light fw-semibold rounded-3 px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger fw-bold rounded-3 px-4">Submit Bug Report</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    let draggedTaskId = null;

    function switchMainTab(tab) {
        const tasksTabBtn = document.getElementById('btn-tasks-main-tab');
        const teamTabBtn = document.getElementById('btn-team-main-tab');
        const filesTabBtn = document.getElementById('btn-files-main-tab');

        const tasksSubSwitcher = document.getElementById('tasks-subview-switcher');

        const listContainer = document.getElementById('tasks-list-container');
        const kanbanContainer = document.getElementById('tasks-kanban-container');
        const bugsContainer = document.getElementById('project-bugs-container');
        const teamContainer = document.getElementById('project-team-container');
        const filesContainer = document.getElementById('project-files-container');

        // Reset main tabs
        [tasksTabBtn, teamTabBtn, filesTabBtn].forEach(btn => {
            if (btn) {
                btn.classList.remove('active', 'fw-bold', 'text-dark', 'border-bottom', 'border-3', 'border-primary');
                btn.classList.add('fw-semibold', 'text-secondary');
            }
        });

        // Hide all main view containers
        [listContainer, kanbanContainer, bugsContainer, teamContainer, filesContainer].forEach(c => {
            if (c) c.classList.add('d-none');
        });

        if (tab === 'team' && teamContainer) {
            teamTabBtn.classList.add('active', 'fw-bold', 'text-dark', 'border-bottom', 'border-3', 'border-primary');
            teamTabBtn.classList.remove('fw-semibold', 'text-secondary');
            teamContainer.classList.remove('d-none');
            if (tasksSubSwitcher) tasksSubSwitcher.classList.add('d-none');
            localStorage.setItem('project_main_tab', 'team');
        } else if (tab === 'files' && filesContainer) {
            filesTabBtn.classList.add('active', 'fw-bold', 'text-dark', 'border-bottom', 'border-3', 'border-primary');
            filesTabBtn.classList.remove('fw-semibold', 'text-secondary');
            filesContainer.classList.remove('d-none');
            if (tasksSubSwitcher) tasksSubSwitcher.classList.add('d-none');
            localStorage.setItem('project_main_tab', 'files');
        } else {
            tasksTabBtn.classList.add('active', 'fw-bold', 'text-dark', 'border-bottom', 'border-3', 'border-primary');
            tasksTabBtn.classList.remove('fw-semibold', 'text-secondary');
            if (tasksSubSwitcher) tasksSubSwitcher.classList.remove('d-none');
            
            // Restore last subview or default to list
            const subView = localStorage.getItem('project_task_subview') || 'list';
            switchTaskSubView(subView);
            localStorage.setItem('project_main_tab', 'tasks');
        }
    }

    function switchTaskSubView(subView) {
        const listBtn = document.getElementById('btn-list-view');
        const kanbanBtn = document.getElementById('btn-kanban-view');
        const bugsBtn = document.getElementById('btn-bugs-view');

        const listContainer = document.getElementById('tasks-list-container');
        const kanbanContainer = document.getElementById('tasks-kanban-container');
        const bugsContainer = document.getElementById('project-bugs-container');

        [listBtn, kanbanBtn, bugsBtn].forEach(btn => {
            if (btn) {
                btn.classList.remove('active', 'btn-white', 'shadow-2xs', 'text-dark');
                btn.classList.add('btn-transparent', 'text-muted');
            }
        });

        [listContainer, kanbanContainer, bugsContainer].forEach(c => {
            if (c) c.classList.add('d-none');
        });

        if (subView === 'bugs' && bugsContainer) {
            bugsBtn.classList.add('active', 'btn-white', 'shadow-2xs', 'text-dark');
            bugsBtn.classList.remove('btn-transparent', 'text-muted');
            bugsContainer.classList.remove('d-none');
            localStorage.setItem('project_task_subview', 'bugs');
        } else if (subView === 'kanban' && kanbanContainer) {
            kanbanBtn.classList.add('active', 'btn-white', 'shadow-2xs', 'text-dark');
            kanbanBtn.classList.remove('btn-transparent', 'text-muted');
            kanbanContainer.classList.remove('d-none');
            localStorage.setItem('project_task_subview', 'kanban');
        } else if (listContainer) {
            listBtn.classList.add('active', 'btn-white', 'shadow-2xs', 'text-dark');
            listBtn.classList.remove('btn-transparent', 'text-muted');
            listContainer.classList.remove('d-none');
            localStorage.setItem('project_task_subview', 'list');
        }
    }

    function openReportBugModal(taskId) {
        document.getElementById('bug_task_id').value = taskId || '';
        const select = document.getElementById('bug_task_id_select');
        if (select) {
            select.value = taskId || '';
        }
    }

    function handleDragStart(e, taskId) {
        draggedTaskId = taskId;
        e.dataTransfer.setData('text/plain', taskId);
        e.currentTarget.classList.add('opacity-50', 'border-primary');
    }

    function handleDragEnd(e) {
        e.currentTarget.classList.remove('opacity-50', 'border-primary');
    }

    function handleDragOver(e) {
        e.preventDefault();
        const col = e.currentTarget;
        col.classList.add('border-primary', 'bg-primary-subtle');
    }

    function handleDragLeave(e) {
        const col = e.currentTarget;
        col.classList.remove('border-primary', 'bg-primary-subtle');
    }

    function handleDrop(e, newStatus) {
        e.preventDefault();
        const col = e.currentTarget;
        col.classList.remove('border-primary', 'bg-primary-subtle');

        if (!draggedTaskId) return;

        const card = document.getElementById(`kanban-task-${draggedTaskId}`);
        const wrapper = document.getElementById(`kanban-wrapper-${newStatus}`);

        if (card && wrapper) {
            wrapper.appendChild(card);
            updateColumnCounts();

            fetch(`/tasks/${draggedTaskId}/status`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ status: newStatus })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const listRow = document.getElementById(`list-task-${draggedTaskId}`);
                    if (listRow) {
                        const badgeCell = listRow.querySelector('.status-badge-cell');
                        if (badgeCell) {
                            let badgeHtml = '';
                            if (newStatus === 'done') badgeHtml = '<span class="badge bg-success text-white rounded-pill px-2.5 py-1 text-uppercase fw-bold" style="font-size: 0.65rem;">Done</span>';
                            else if (newStatus === 'review') badgeHtml = '<span class="badge bg-info text-white rounded-pill px-2.5 py-1 text-uppercase fw-bold" style="font-size: 0.65rem;">Review</span>';
                            else if (newStatus === 'reopened') badgeHtml = '<span class="badge bg-danger text-white rounded-pill px-2.5 py-1 text-uppercase fw-bold" style="font-size: 0.65rem;">🔴 Reopened</span>';
                            else if (newStatus === 'in_progress') badgeHtml = '<span class="badge bg-primary text-white rounded-pill px-2.5 py-1 text-uppercase fw-bold" style="font-size: 0.65rem;">In Progress</span>';
                            else badgeHtml = '<span class="badge bg-light text-dark border rounded-pill px-2.5 py-1 text-uppercase fw-bold" style="font-size: 0.65rem;">To Do</span>';
                            badgeCell.innerHTML = badgeHtml;
                        }
                    }
                } else {
                    alert(data.message || 'Could not update task status.');
                    window.location.reload();
                }
            })
            .catch(err => {
                console.error(err);
                alert('An error occurred while updating task status.');
                window.location.reload();
            });
        }
    }

    function applyTaskFilters() {
        const query = (document.getElementById('task-live-search')?.value || '').toLowerCase().trim();
        const assignee = document.getElementById('task-assignee-filter')?.value || '';
        const priority = document.getElementById('task-priority-filter')?.value || '';

        const items = document.querySelectorAll('.task-filter-item');
        items.forEach(item => {
            const title = item.getAttribute('data-task-title') || '';
            const itemAssignee = item.getAttribute('data-assignee-id') || '';
            const itemPriority = item.getAttribute('data-priority') || '';

            const matchesQuery = !query || title.includes(query);
            const matchesAssignee = !assignee || itemAssignee === assignee;
            const matchesPriority = !priority || itemPriority === priority;

            if (matchesQuery && matchesAssignee && matchesPriority) {
                item.classList.remove('d-none');
            } else {
                item.classList.add('d-none');
            }
        });

        // Update column counters in kanban
        ['todo', 'in_progress', 'review', 'reopened', 'done'].forEach(status => {
            const wrapper = document.getElementById(`kanban-wrapper-${status}`);
            const countBadge = document.getElementById(`kanban-count-${status}`);
            if (wrapper && countBadge) {
                const visibleCount = wrapper.querySelectorAll('.kanban-card:not(.d-none)').length;
                countBadge.innerText = visibleCount;
            }
        });
    }

    function resetTaskFilters() {
        const searchInput = document.getElementById('task-live-search');
        const assigneeSelect = document.getElementById('task-assignee-filter');
        const prioritySelect = document.getElementById('task-priority-filter');
        if (searchInput) searchInput.value = '';
        if (assigneeSelect) assigneeSelect.value = '';
        if (prioritySelect) prioritySelect.value = '';
        applyTaskFilters();
    }

    // Global State for Active Task in Drawer
    let currentDrawerTaskId = null;

    // Project Tasks Data Map for Right-Side Slide-Over Task Details & Discussion Drawer
    const projectTasksData = {
        @foreach($project->tasks as $task)
            "{{ $task->id }}": {
                id: {{ $task->id }},
                title: @json($task->title),
                description: @json($task->description ?? ''),
                status: @json($task->status),
                priority: @json($task->priority),
                userId: @json($task->user_id),
                assigneeName: @json($task->assignee ? $task->assignee->full_name : 'Unassigned'),
                assigneeRole: @json($task->assignee ? $task->assignee->display_designation : 'Team Member'),
                assigneeInitials: @json($task->assignee ? strtoupper(substr($task->assignee->first_name, 0, 1)) : 'U'),
                dueDate: @json($task->due_date ? $task->due_date->format('M d, Y') : 'No deadline'),
                dueDateRaw: @json($task->due_date ? $task->due_date->format('Y-m-d') : ''),
                canEdit: {{ ($canManageTasks && ($currentUser->role?->code !== 'employee' || $task->user_id === $currentUser->id)) ? 'true' : 'false' }},
                deleteUrl: "{{ route('tasks.destroy', $task->id) }}",
                statusUpdateUrl: "/tasks/{{ $task->id }}/status",
                commentsUrl: "{{ route('tasks.comments.store', $task->id) }}",
                checklistStoreUrl: "/tasks/{{ $task->id }}/checklists",
                bugs: [
                    @foreach($task->bugs as $b)
                        {
                            id: {{ $b->id }},
                            bugNumber: @json($b->bug_number),
                            title: @json($b->title),
                            description: @json($b->description ?? ''),
                            severity: @json($b->severity),
                            status: @json($b->status),
                            reporterName: @json($b->reporter ? $b->reporter->full_name : 'User'),
                            timeAgo: @json($b->created_at->diffForHumans()),
                            url: "{{ route('bugs.show', $b->id) }}"
                        },
                    @endforeach
                ],
                checklists: [
                    @foreach($task->checklists as $cl)
                        {
                            id: {{ $cl->id }},
                            title: @json($cl->title),
                            isCompleted: {{ $cl->is_completed ? 'true' : 'false' }},
                            toggleUrl: "/checklists/{{ $cl->id }}/toggle",
                            deleteUrl: "/checklists/{{ $cl->id }}"
                        },
                    @endforeach
                ],
                comments: [
                    @foreach($task->comments as $comment)
                        {
                            id: {{ $comment->id }},
                            userName: @json($comment->user ? $comment->user->full_name : 'User'),
                            userInitials: @json($comment->user ? strtoupper(substr($comment->user->first_name, 0, 1)) : 'U'),
                            content: @json($comment->content ?? $comment->comment ?? ''),
                            timeAgo: @json($comment->created_at->diffForHumans()),
                            canDelete: {{ ($comment->user_id === $currentUser->id || in_array(strtolower($currentUser->role?->code ?? ''), ['admin', 'owner', 'hr'])) ? 'true' : 'false' }},
                            deleteUrl: "{{ route('tasks.comments.destroy', [$task->id, $comment->id]) }}"
                        },
                    @endforeach
                ]
            },
        @endforeach
    };

    function openTaskDrawer(taskId, focusComments = false) {
        const task = projectTasksData[taskId];
        if (!task) return;

        currentDrawerTaskId = taskId;

        // Header Elements
        document.getElementById('taskDrawerId').textContent = `#TASK-${task.id}`;
        document.getElementById('taskDrawerTitle').textContent = task.title;
        document.getElementById('taskDrawerStatusSelect').value = task.status;

        // Priority Badge
        const priorityEl = document.getElementById('taskDrawerPriorityBadge');
        if (task.priority === 'high') {
            priorityEl.innerHTML = '<span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-1 font-monospace fw-bold" style="font-size: 0.72rem;">HIGH</span>';
        } else if (task.priority === 'medium') {
            priorityEl.innerHTML = '<span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2.5 py-1 font-monospace fw-bold" style="font-size: 0.72rem;">MEDIUM</span>';
        } else {
            priorityEl.innerHTML = '<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2.5 py-1 font-monospace fw-bold" style="font-size: 0.72rem;">LOW</span>';
        }

        // Edit Button Wire-up
        const editBtn = document.getElementById('taskDrawerEditBtn');
        if (task.canEdit) {
            editBtn.classList.remove('d-none');
            editBtn.onclick = function() {
                const offcanvasEl = document.getElementById('taskDetailsDrawer');
                const offcanvasInstance = bootstrap.Offcanvas.getInstance(offcanvasEl);
                if (offcanvasInstance) offcanvasInstance.hide();

                // Populate Edit Modal
                document.getElementById('edit_title').value = task.title;
                document.getElementById('edit_description').value = task.description;
                document.getElementById('edit_user_id').value = task.userId || '';
                document.getElementById('edit_status').value = task.status;
                document.getElementById('edit_priority').value = task.priority;
                document.getElementById('edit_due_date').value = task.dueDateRaw;
                document.getElementById('editTaskForm').setAttribute('action', `/tasks/${task.id}`);

                const editModal = new bootstrap.Modal(document.getElementById('editTaskModal'));
                editModal.show();
            };
        } else {
            editBtn.classList.add('d-none');
        }

        // Meta Card: Assignee & Due Date
        document.getElementById('taskDrawerAssigneeAvatar').textContent = task.assigneeInitials;
        document.getElementById('taskDrawerAssigneeName').textContent = task.assigneeName;
        document.getElementById('taskDrawerAssigneeRole').textContent = task.assigneeRole;
        document.getElementById('taskDrawerDueDate').textContent = task.dueDate;

        // Description
        const descEl = document.getElementById('taskDrawerDescription');
        if (task.description && task.description.trim() !== '') {
            descEl.textContent = task.description;
            descEl.classList.remove('fst-italic', 'text-muted');
        } else {
            descEl.textContent = 'No detailed description provided.';
            descEl.classList.add('fst-italic', 'text-muted');
        }

        // Render Checklists / Subtasks
        renderDrawerChecklists(task);

        // Render Linked Bugs
        renderDrawerBugs(task);

        // Form action & Reset Input
        document.getElementById('taskDrawerCommentForm').action = task.commentsUrl;
        document.getElementById('taskDrawerCommentInput').value = '';

        // Comments Count & Feed
        document.getElementById('taskDrawerCommentCount').textContent = task.comments.length + ' comments';
        renderDrawerComments(task);

        // Show Bootstrap Offcanvas
        const offcanvasEl = document.getElementById('taskDetailsDrawer');
        const offcanvasInstance = bootstrap.Offcanvas.getOrCreateInstance(offcanvasEl);
        offcanvasInstance.show();

        if (focusComments) {
            setTimeout(() => {
                const commentInput = document.getElementById('taskDrawerCommentInput');
                commentInput.focus();
            }, 300);
        }
    }

    function renderDrawerChecklists(task) {
        const container = document.getElementById('taskDrawerChecklistsContainer');
        const progressBadge = document.getElementById('taskDrawerChecklistProgress');
        const progressBar = document.getElementById('taskDrawerChecklistProgressBar');

        const total = task.checklists.length;
        const completed = task.checklists.filter(c => c.isCompleted).length;
        const percent = total > 0 ? Math.round((completed / total) * 100) : 0;

        progressBadge.textContent = `${completed}/${total} (${percent}%)`;
        progressBar.style.width = `${percent}%`;

        if (total === 0) {
            container.innerHTML = `<div class="text-muted small fst-italic py-1" style="font-size: 0.78rem;">No subtasks added yet.</div>`;
            return;
        }

        let html = '';
        task.checklists.forEach(cl => {
            html += `
                <div class="d-flex align-items-center justify-content-between p-2 rounded-2 border bg-light-subtle" id="drawer-cl-${cl.id}">
                    <div class="form-check mb-0 d-flex align-items-center gap-2">
                        <input class="form-check-input mt-0 cursor-pointer" type="checkbox" ${cl.isCompleted ? 'checked' : ''} onchange="toggleDrawerChecklist(${cl.id}, '${cl.toggleUrl}')" id="cl-check-${cl.id}">
                        <label class="form-check-label small ${cl.isCompleted ? 'text-decoration-line-through text-muted' : 'text-dark fw-semibold'} cursor-pointer" for="cl-check-${cl.id}" style="font-size: 0.82rem;">
                            ${escapeHtml(cl.title)}
                        </label>
                    </div>
                    <button type="button" class="btn btn-link text-danger p-0 border-0" onclick="deleteDrawerChecklist(${cl.id}, '${cl.deleteUrl}')" title="Delete subtask" style="font-size: 0.75rem;">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            `;
        });
        container.innerHTML = html;
    }

    function handleDrawerAddChecklist(e) {
        e.preventDefault();
        if (!currentDrawerTaskId) return;

        const input = document.getElementById('taskDrawerNewChecklistInput');
        const title = (input.value || '').trim();
        if (!title) return;

        const task = projectTasksData[currentDrawerTaskId];
        if (!task) return;

        fetch(task.checklistStoreUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ title: title })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success && data.checklist) {
                task.checklists.push({
                    id: data.checklist.id,
                    title: data.checklist.title,
                    isCompleted: false,
                    toggleUrl: `/checklists/${data.checklist.id}/toggle`,
                    deleteUrl: `/checklists/${data.checklist.id}`
                });
                renderDrawerChecklists(task);
                input.value = '';
            } else {
                alert(data.message || 'Could not add subtask.');
            }
        })
        .catch(err => {
            console.error(err);
            alert('Error adding subtask.');
        });
    }

    function toggleDrawerChecklist(checklistId, toggleUrl) {
        if (!currentDrawerTaskId) return;
        const task = projectTasksData[currentDrawerTaskId];
        if (!task) return;

        fetch(toggleUrl, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const item = task.checklists.find(c => c.id === checklistId);
                if (item) {
                    item.isCompleted = data.is_completed;
                    renderDrawerChecklists(task);
                }
            }
        })
        .catch(console.error);
    }

    function deleteDrawerChecklist(checklistId, deleteUrl) {
        if (!currentDrawerTaskId) return;
        const task = projectTasksData[currentDrawerTaskId];
        if (!task) return;

        fetch(deleteUrl, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                task.checklists = task.checklists.filter(c => c.id !== checklistId);
                renderDrawerChecklists(task);
            }
        })
        .catch(console.error);
    }

    function renderDrawerBugs(task) {
        const sectionEl = document.getElementById('taskDrawerLinkedBugsSection');
        const listEl = document.getElementById('taskDrawerBugsList');
        const countEl = document.getElementById('taskDrawerBugsCount');

        if (!task.bugs || task.bugs.length === 0) {
            sectionEl.classList.add('d-none');
            return;
        }

        sectionEl.classList.remove('d-none');
        countEl.textContent = `${task.bugs.length} ${task.bugs.length === 1 ? 'bug' : 'bugs'}`;

        let html = '';
        task.bugs.forEach(b => {
            const severityBadges = {
                'critical': '<span class="badge bg-danger text-white rounded-pill px-2 py-0.5 font-monospace fw-bold" style="font-size: 0.65rem;">CRITICAL</span>',
                'high': '<span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2 py-0.5 font-monospace fw-bold" style="font-size: 0.65rem;">HIGH</span>',
                'medium': '<span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2 py-0.5 font-monospace fw-bold" style="font-size: 0.65rem;">MEDIUM</span>',
                'low': '<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2 py-0.5 font-monospace fw-bold" style="font-size: 0.65rem;">LOW</span>'
            };

            const statusBadges = {
                'open': '<span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2 py-0.5 font-monospace fw-bold" style="font-size: 0.65rem;">🔴 Open</span>',
                'in_progress': '<span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2 py-0.5 font-monospace fw-bold" style="font-size: 0.65rem;">🟡 Fixing</span>',
                'resolved': '<span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-0.5 font-monospace fw-bold" style="font-size: 0.65rem;">🟢 Resolved</span>',
                'closed': '<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2 py-0.5 font-monospace fw-bold" style="font-size: 0.65rem;">⚪ Closed</span>'
            };

            html += `
                <div class="card border border-danger-subtle rounded-3 p-3 bg-white shadow-2xs">
                    <div class="d-flex align-items-center justify-content-between mb-1.5">
                        <div class="d-flex align-items-center gap-1.5 flex-wrap">
                            <span class="font-monospace fw-bold text-danger small">${escapeHtml(b.bugNumber)}</span>
                            ${severityBadges[b.severity] || ''}
                            ${statusBadges[b.status] || ''}
                        </div>
                        <a href="${b.url}" class="btn btn-sm btn-link text-primary p-0 text-decoration-none" title="View bug report details">
                            <i class="fa-solid fa-arrow-up-right-from-square" style="font-size: 0.75rem;"></i>
                        </a>
                    </div>
                    <h6 class="fw-bold text-dark mb-1 fs-7">${escapeHtml(b.title)}</h6>
                    ${b.description ? `<p class="text-secondary small mb-2" style="font-size: 0.8rem; line-height: 1.4;">${escapeHtml(b.description)}</p>` : ''}
                    <div class="d-flex align-items-center justify-content-between text-muted pt-1 border-top" style="font-size: 0.7rem;">
                        <span>Reported by <strong>${escapeHtml(b.reporterName)}</strong></span>
                        <span class="font-monospace">${escapeHtml(b.timeAgo)}</span>
                    </div>
                </div>
            `;
        });
        listEl.innerHTML = html;
    }

    function renderDrawerComments(task) {
        const feedEl = document.getElementById('taskDrawerCommentsFeed');
        if (task.comments.length === 0) {
            feedEl.innerHTML = `
                <div class="text-center py-4 text-muted bg-light-subtle rounded-3 border">
                    <i class="fa-regular fa-comments fs-3 text-secondary opacity-40 mb-1.5 d-block"></i>
                    <p class="mb-0 small fw-semibold">No comments yet</p>
                    <small class="text-muted">Start the conversation below.</small>
                </div>
            `;
            return;
        }

        let html = '';
        task.comments.forEach(c => {
            const isBugReport = c.content.includes('Task Reopened & Bug Reported') || c.content.includes('🔴') || c.content.includes('[BUG-');

            if (isBugReport) {
                html += `
                    <div class="card border border-danger-subtle bg-danger-subtle rounded-3 p-3 shadow-2xs">
                        <div class="d-flex justify-content-between align-items-center mb-1.5">
                            <div class="d-flex align-items-center gap-1.5">
                                <span class="badge bg-danger text-white rounded-pill px-2 py-0.5 font-monospace fw-bold" style="font-size: 0.68rem;">
                                    <i class="fa-solid fa-bug me-1"></i> BUG REPORTED
                                </span>
                                <span class="fw-bold text-danger-emphasis small">${escapeHtml(c.userName)}</span>
                            </div>
                            <div class="d-flex align-items-center gap-1.5">
                                <small class="text-muted font-monospace" style="font-size: 0.68rem;">${escapeHtml(c.timeAgo)}</small>
                                ${c.canDelete ? `
                                    <form action="${c.deleteUrl}" method="POST" class="d-inline" onsubmit="return confirm('Delete comment?');">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <input type="hidden" name="_method" value="DELETE">
                                        <button type="submit" class="btn btn-link text-danger p-0 border-0 ms-1" style="font-size: 0.68rem;" title="Delete">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                ` : ''}
                            </div>
                        </div>
                        <div class="p-2.5 rounded-2 bg-white border border-danger-subtle text-dark" style="font-size: 0.82rem; white-space: pre-wrap; line-height: 1.45;">
                            ${escapeHtml(c.content.replace('🔴 Task Reopened & Bug Reported ', ''))}
                        </div>
                    </div>
                `;
            } else {
                html += `
                    <div class="d-flex gap-2.5">
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold shadow-2xs flex-shrink-0" style="width: 34px; height: 34px; font-size: 0.8rem;">
                            ${escapeHtml(c.userInitials)}
                        </div>
                        <div class="flex-grow-1">
                            <div class="card border shadow-2xs rounded-3 bg-white p-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-bold text-dark small" style="font-size: 0.82rem;">${escapeHtml(c.userName)}</span>
                                    <div class="d-flex align-items-center gap-1.5">
                                        <small class="text-muted font-monospace" style="font-size: 0.68rem;">${escapeHtml(c.timeAgo)}</small>
                                        ${c.canDelete ? `
                                            <form action="${c.deleteUrl}" method="POST" class="d-inline" onsubmit="return confirm('Delete comment?');">
                                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                                <input type="hidden" name="_method" value="DELETE">
                                                <button type="submit" class="btn btn-link text-danger p-0 border-0 ms-1" style="font-size: 0.72rem;" title="Delete comment">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>
                                            </form>
                                        ` : ''}
                                    </div>
                                </div>
                                <p class="mb-0 text-secondary" style="white-space: pre-wrap; font-size: 0.83rem; line-height: 1.45;">${escapeHtml(c.content)}</p>
                            </div>
                        </div>
                    </div>
                `;
            }
        });
        feedEl.innerHTML = html;
    }

    function updateDrawerTaskStatus(newStatus) {
        if (!currentDrawerTaskId) return;

        const task = projectTasksData[currentDrawerTaskId];
        if (!task) return;

        fetch(task.statusUpdateUrl, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ status: newStatus })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                task.status = newStatus;

                // Update List Row Badge
                const listRow = document.getElementById(`list-task-${task.id}`);
                if (listRow) {
                    const badgeCell = listRow.querySelector('.status-badge-cell');
                    if (badgeCell) {
                        let badgeHtml = '';
                        if (newStatus === 'done') badgeHtml = '<span class="badge bg-success text-white rounded-pill px-2.5 py-1 text-uppercase fw-bold" style="font-size: 0.65rem;">Done</span>';
                        else if (newStatus === 'review') badgeHtml = '<span class="badge bg-info text-white rounded-pill px-2.5 py-1 text-uppercase fw-bold" style="font-size: 0.65rem;">Review</span>';
                        else if (newStatus === 'reopened') badgeHtml = '<span class="badge bg-danger text-white rounded-pill px-2.5 py-1 text-uppercase fw-bold" style="font-size: 0.65rem;">🔴 Reopened</span>';
                        else if (newStatus === 'in_progress') badgeHtml = '<span class="badge bg-primary text-white rounded-pill px-2.5 py-1 text-uppercase fw-bold" style="font-size: 0.65rem;">In Progress</span>';
                        else badgeHtml = '<span class="badge bg-light text-dark border rounded-pill px-2.5 py-1 text-uppercase fw-bold" style="font-size: 0.65rem;">To Do</span>';
                        badgeCell.innerHTML = badgeHtml;
                    }
                }

                // Update Kanban Card Position & Column Counts
                const card = document.getElementById(`kanban-task-${task.id}`);
                const wrapper = document.getElementById(`kanban-wrapper-${newStatus}`);
                if (card && wrapper) {
                    wrapper.appendChild(card);
                    applyTaskFilters();
                }
            } else {
                alert(data.message || 'Could not update status.');
                document.getElementById('taskDrawerStatusSelect').value = task.status;
            }
        })
        .catch(err => {
            console.error(err);
            alert('Error updating status.');
            document.getElementById('taskDrawerStatusSelect').value = task.status;
        });
    }

    function openReportBugForCurrentDrawerTask() {
        if (!currentDrawerTaskId) return;
        const offcanvasEl = document.getElementById('taskDetailsDrawer');
        const offcanvasInstance = bootstrap.Offcanvas.getInstance(offcanvasEl);
        if (offcanvasInstance) offcanvasInstance.hide();
        openReportBugModal(currentDrawerTaskId);
        const bugModal = new bootstrap.Modal(document.getElementById('reportBugModal'));
        bugModal.show();
    }

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    document.addEventListener('DOMContentLoaded', function () {
        const savedMainTab = localStorage.getItem('project_main_tab') || 'tasks';
        switchMainTab(savedMainTab);

        const editButtons = document.querySelectorAll('.edit-task-btn');
        const editForm = document.getElementById('editTaskForm');
        
        editButtons.forEach(button => {
            button.addEventListener('click', function () {
                const id = this.getAttribute('data-id');
                const title = this.getAttribute('data-title');
                const description = this.getAttribute('data-description');
                const userId = this.getAttribute('data-user-id');
                const status = this.getAttribute('data-status');
                const priority = this.getAttribute('data-priority');
                const dueDate = this.getAttribute('data-due-date');

                // Populate fields
                document.getElementById('edit_title').value = title;
                document.getElementById('edit_description').value = description;
                document.getElementById('edit_user_id').value = userId || '';
                document.getElementById('edit_status').value = status;
                document.getElementById('edit_priority').value = priority;
                document.getElementById('edit_due_date').value = dueDate;

                // Update form action
                editForm.setAttribute('action', `/tasks/${id}`);
            });
        });
    });
</script>
@endpush
