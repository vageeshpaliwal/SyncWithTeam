@extends('layouts.dashboard')

@section('title', 'Departments')

@section('content')

@php
    $canManage = app(\App\Services\CurrentUserService::class)->hasPermission('departments.manage');
@endphp

<div class="container-fluid px-0">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-triangle-exclamation me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-1 fw-bold text-dark">Organization Departments</h3>
            <p class="text-muted mb-0">Overview of departments, assigned managers, employee counts, and active projects.</p>
        </div>

        @if($canManage)
            <a href="{{ route('departments.create') }}" class="btn btn-primary d-flex align-items-center gap-2 fw-semibold px-3.5 py-2 shadow-sm">
                <i class="fa-solid fa-plus"></i> Add Department
            </a>
        @endif
    </div>

    <!-- Department Table Listing -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-modern align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4 py-3">Department Name</th>
                            <th class="py-3">Department Manager</th>
                            <th class="py-3 text-center">No. of Employees</th>
                            <th class="py-3 text-center">No. of Projects</th>
                            <th class="py-3">Status</th>
                            <th class="py-3 text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($departments as $department)
                            @php
                                $manager = $department->users->first(fn($u) => strtolower($u->role?->code ?? '') === 'manager' || strtolower($u->role?->code ?? '') === 'owner');
                            @endphp
                            <tr class="transition-all">
                                <td class="ps-4 py-3.5">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="rounded-3 bg-primary-subtle text-primary p-2.5 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                            <i class="fa-solid fa-sitemap fs-5"></i>
                                        </div>
                                        <div>
                                            <a href="{{ route('departments.show', $department) }}" class="fw-bold text-dark text-decoration-none fs-6 hover-primary">
                                                {{ $department->name }}
                                            </a>
                                            <small class="text-muted d-block text-truncate" style="max-width: 280px;">
                                                {{ $department->description ?: 'No description provided.' }}
                                            </small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($manager)
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                                {{ strtoupper(substr($manager->first_name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <span class="fw-bold text-dark d-block lh-1 small">{{ $manager->full_name }}</span>
                                                <small class="text-muted" style="font-size: 0.75rem;">{{ $manager->email }}</small>
                                            </div>
                                        </div>
                                    @else
                                        <span class="badge bg-light text-muted border font-italic"><i class="fa-solid fa-user-xmark me-1"></i> Unassigned</span>
                                    @endif
                                </td>
                                <td class="text-center font-monospace">
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1.5 fs-7">
                                        <i class="fa-solid fa-users me-1"></i> {{ $department->users_count }} Employees
                                    </span>
                                </td>
                                <td class="text-center font-monospace">
                                    <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-3 py-1.5 fs-7">
                                        <i class="fa-solid fa-folder-kanban me-1"></i> {{ $department->projects_count }} Projects
                                    </span>
                                </td>
                                <td>
                                    @if($department->status)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">Active</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-flex justify-content-end align-items-center gap-2">
                                        <a href="{{ route('departments.show', $department) }}" class="btn btn-primary btn-sm px-3 rounded-pill fw-semibold d-flex align-items-center gap-1.5" style="font-size: 0.8rem;">
                                            <i class="fa-solid fa-eye"></i> View Department
                                        </a>
                                        @if($canManage)
                                            <a href="{{ route('departments.edit', $department) }}" class="btn btn-outline-secondary btn-sm px-2.5 rounded-circle" style="width: 32px; height: 32px;" title="Edit">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </a>
                                            <form action="{{ route('departments.destroy', $department) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this department?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-sm px-2.5 rounded-circle" style="width: 32px; height: 32px;" title="Delete">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-sitemap fs-1 text-secondary opacity-50 mb-3"></i>
                                    <p class="mb-0 fw-medium">No departments found for your organization.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

@endsection
