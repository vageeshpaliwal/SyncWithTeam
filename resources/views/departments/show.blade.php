@extends('layouts.dashboard')

@section('title', $department->name . ' Department')

@section('content')

@php
    $canManageUsers = app(\App\Services\CurrentUserService::class)->hasPermission('users.manage') || app(\App\Services\CurrentUserService::class)->hasPermission('users.view');
@endphp

<div class="container-fluid px-0">

    <!-- Back Navigation & Title -->
    <div class="mb-4">
        <a href="{{ route('departments.index') }}" class="btn btn-sm btn-link text-decoration-none ps-0 mb-2 font-semibold">
            <i class="fa-solid fa-arrow-left me-1"></i> Back to Departments
        </a>
        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-4 bg-primary text-white p-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                    <i class="fa-solid fa-sitemap fs-4"></i>
                </div>
                <div>
                    <h3 class="mb-0 fw-bold text-dark">{{ $department->name }}</h3>
                    <p class="text-muted mb-0">{{ $department->description ?: 'No description provided.' }}</p>
                </div>
            </div>
            <div>
                @if($department->status)
                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-2 fs-7">Active</span>
                @else
                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-3 py-2 fs-7">Inactive</span>
                @endif
            </div>
        </div>
    </div>

    <!-- Summary Stats Bar -->
    <div class="row g-3 mb-4">
        <!-- Manager Card -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <span class="text-muted small fw-bold text-uppercase d-block mb-2 font-monospace">Department Manager</span>
                @if($manager)
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold fs-5" style="width: 44px; height: 44px;">
                            {{ strtoupper(substr($manager->first_name, 0, 1)) }}
                        </div>
                        <div>
                            <h6 class="mb-0 fw-bold text-dark">{{ $manager->full_name }}</h6>
                            <small class="text-muted">{{ $manager->email }}</small>
                        </div>
                    </div>
                @else
                    <span class="badge bg-light text-muted border py-2 px-3 font-italic"><i class="fa-solid fa-user-xmark me-1"></i> Manager Unassigned</span>
                @endif
            </div>
        </div>

        <!-- Total Employees -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted small fw-bold text-uppercase d-block mb-1">Total Employees</span>
                        <h3 class="mb-0 fw-bold text-dark font-monospace">{{ $department->users->count() }} <span class="fs-6 text-muted font-normal">Members</span></h3>
                    </div>
                    <div class="rounded-3 bg-primary-subtle text-primary p-3">
                        <i class="fa-solid fa-users fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Projects -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted small fw-bold text-uppercase d-block mb-1">Department Projects</span>
                        <h3 class="mb-0 fw-bold text-dark font-monospace">{{ $projects->count() }} <span class="fs-6 text-muted font-normal">Projects</span></h3>
                    </div>
                    <div class="rounded-3 bg-info-subtle text-info p-3">
                        <i class="fa-solid fa-folder-kanban fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Assigned Staff Members Table -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 bg-white">
        <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold text-dark fs-6"><i class="fa-solid fa-users text-primary me-2"></i> Department Employees</h5>
            <span class="badge bg-primary text-white rounded-pill px-3 py-1 font-monospace">{{ $department->users->count() }} Staff</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Employee Name</th>
                            <th>Email</th>
                            <th>Designation</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($department->users as $staff)
                            <tr>
                                <td class="ps-4 py-3">
                                    <div class="d-flex align-items-center gap-3">
                                        @if($staff->avatar)
                                            <img src="{{ asset('storage/' . $staff->avatar) }}" class="rounded-circle border shadow-sm" style="width: 38px; height: 38px; object-fit: cover;">
                                        @else
                                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold" style="width: 38px; height: 38px; font-size: 0.9rem;">
                                                {{ strtoupper(substr($staff->first_name, 0, 1)) }}
                                            </div>
                                        @endif
                                        <div>
                                            <h6 class="mb-0 fw-bold text-dark">{{ $staff->full_name }}</h6>
                                            <small class="text-muted">{{ $staff->username }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td class="small text-muted">{{ $staff->email }}</td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle fw-semibold px-3 py-1.5 fs-7">{{ $staff->display_designation }}</span>
                                </td>
                                <td>
                                    @if($staff->status)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">Active</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    @if($canManageUsers)
                                        <a href="{{ route('users.edit', $staff) }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-semibold d-inline-flex align-items-center gap-1.5" style="font-size: 0.8rem;">
                                            <i class="fa-solid fa-id-card"></i> View Profile
                                        </a>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">No employees assigned to this department.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Department Projects Table -->
    @if($projects->isNotEmpty())
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-4">
            <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold text-dark fs-6"><i class="fa-solid fa-folder-kanban text-info me-2"></i> Department Active Projects</h5>
                <span class="badge bg-info text-white rounded-pill px-3 py-1 font-monospace">{{ $projects->count() }} Projects</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Project Name</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($projects as $proj)
                                <tr>
                                    <td class="ps-4 py-3 fw-bold text-dark">{{ $proj->name }}</td>
                                    <td class="small text-muted text-truncate" style="max-width: 350px;">{{ $proj->description ?: 'No description.' }}</td>
                                    <td>
                                        @if($proj->status === 'active')
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">Active</span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill">{{ ucfirst($proj->status) }}</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-4">
                                        <a href="{{ route('projects.show', $proj->id) }}" class="btn btn-outline-info btn-sm rounded-pill px-3 fw-semibold" style="font-size: 0.8rem;">
                                            View Board &rarr;
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

</div>

@endsection
