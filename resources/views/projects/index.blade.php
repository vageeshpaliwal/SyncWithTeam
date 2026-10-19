@extends('layouts.dashboard')

@section('title', 'Projects')

@section('content')

<div class="container-fluid px-0">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="page-hero d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h4 class="fw-bold text-dark mb-1">Projects & Initiatives</h4>
            <p class="text-muted small mb-0">Manage organization projects, track progress, and assign team members.</p>
        </div>
        @if(session()->has('user_id') && (new \App\Services\CurrentUserService())->hasPermission('projects.manage'))
            <a href="{{ route('projects.create') }}" class="btn btn-primary rounded-pill px-3.5 py-2 fw-semibold d-flex align-items-center gap-2 shadow-sm">
                <i class="fa-solid fa-plus"></i>
                <span>Create Project</span>
            </a>
        @endif
    </div>

    <!-- Search & Filter Controls -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
        <div class="card-body p-3">
            <form action="{{ route('projects.index') }}" method="GET" class="row g-2 align-items-center">
                <!-- Search Input -->
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-0 text-muted ps-3">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </span>
                        <input type="text" name="search" class="form-control bg-light border-0 ps-2" 
                               placeholder="Search by project name..." 
                               value="{{ request('search') }}">
                    </div>
                </div>

                <!-- Filter by Department -->
                <div class="col-md-3">
                    <select name="department_id" class="form-select bg-light border-0" onchange="this.form.submit()">
                        <option value="">All Departments</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                                {{ $dept->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Filter by Status -->
                <div class="col-md-3">
                    <select name="status" class="form-select bg-light border-0" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="planning" {{ request('status') === 'planning' ? 'selected' : '' }}>Planning</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                    </select>
                </div>

                <!-- Buttons -->
                <div class="col-md-2 d-flex gap-2 justify-content-end">
                    <button type="submit" class="btn btn-primary rounded-3 fw-semibold px-3 w-100">
                        Filter
                    </button>
                    @if(request()->hasAny(['search', 'department_id', 'status']))
                        <a href="{{ route('projects.index') }}" class="btn btn-light border rounded-3 px-3" title="Clear Filters">
                            <i class="fa-solid fa-rotate-left"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0 rounded-4 overflow-hidden bg-white">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-modern align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">Project & Overview</th>
                            <th>Status</th>
                            <th>Duration</th>
                            <th>Team Members</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($projects as $project)
                            <tr>
                                <td class="ps-4 py-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="rounded-3 bg-primary-subtle text-primary d-flex align-items-center justify-content-center shadow-2xs" style="width: 42px; height: 42px; min-width: 42px;">
                                            <i class="fa-solid fa-diagram-project fs-5"></i>
                                        </div>
                                        <div class="overflow-hidden">
                                            <a href="{{ route('projects.show', $project->id) }}" class="fw-bold text-dark text-decoration-none hover-text-primary fs-6 d-block text-truncate mb-0.5">
                                                {{ $project->name }}
                                            </a>
                                            @if($project->client_name)
                                                <div class="mb-1">
                                                    <span class="badge bg-light text-dark border px-2 py-0.5" style="font-size: 0.72rem;">
                                                        <i class="fa-solid fa-user-tie text-secondary me-1"></i> {{ $project->client_name }}
                                                        @if($project->client_country)
                                                            <span class="text-muted">({{ $project->client_country }})</span>
                                                        @endif
                                                    </span>
                                                </div>
                                            @endif
                                            <small class="text-muted text-truncate d-block" style="max-width: 320px; font-size: 0.78rem;">
                                                {{ $project->description ?: 'No detailed description.' }}
                                            </small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($project->status === 'active')
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1">🟢 Active</span>
                                    @elseif($project->status === 'planning')
                                        <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-2.5 py-1">🔵 Planning</span>
                                    @elseif($project->status === 'completed')
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2.5 py-1">⚪ Completed</span>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2.5 py-1">🟡 Suspended</span>
                                    @endif
                                </td>
                                <td>
                                    <div>
                                        <div class="small fw-semibold text-dark font-monospace"><i class="fa-regular fa-calendar-check me-1 text-primary"></i>{{ $project->start_date ? $project->start_date->format('M d, Y') : '-' }}</div>
                                        <small class="text-muted font-monospace"><i class="fa-regular fa-calendar-xmark me-1 text-secondary"></i>{{ $project->end_date ? $project->end_date->format('M d, Y') : '-' }}</small>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border rounded-pill px-2.5 py-1 font-monospace">
                                        <i class="fa-solid fa-users text-primary me-1"></i> {{ $project->users_count }} Members
                                    </span>
                                    @if($project->teamLead)
                                        <div class="mt-1">
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2 py-0.5" style="font-size: 0.72rem;" title="Team Lead">
                                                <i class="fa-solid fa-crown text-warning me-1"></i> {{ $project->teamLead->full_name }}
                                            </span>
                                        </div>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-flex justify-content-end gap-1.5">
                                        <a href="{{ route('projects.show', $project->id) }}" class="btn btn-sm btn-primary rounded-pill px-3 py-1 fw-semibold" style="font-size: 0.78rem;">
                                            <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Open
                                        </a>
                                        @if((new \App\Services\CurrentUserService())->hasPermission('projects.manage'))
                                            <a href="{{ route('projects.edit', $project->id) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-2.5 py-1" style="font-size: 0.78rem;" title="Edit Project">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </a>
                                            <form action="{{ route('projects.destroy', $project->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this project?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-2.5 py-1" style="font-size: 0.78rem;" title="Delete Project">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">No projects found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

@endsection
