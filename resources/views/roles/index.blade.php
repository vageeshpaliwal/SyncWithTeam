@extends('layouts.dashboard')

@section('title', 'Roles & Permissions')

@section('content')

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

    @if(isset($errors) && $errors->any())
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">

            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Header Section -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold text-dark mb-1">Roles & Access Control</h4>
            <p class="text-muted small mb-0">Configure role levels, manage permissions, and control team privileges.</p>
        </div>

        @if($canManage)
            <a href="{{ route('roles.create') }}" class="btn btn-primary rounded-pill px-3.5 py-2 fw-semibold d-flex align-items-center gap-2 shadow-sm">
                <i class="fa-solid fa-plus"></i> Create Role
            </a>
        @endif
    </div>

    <!-- Roles Table Card -->
    <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-modern align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">Role Name</th>
                            <th>Code</th>
                            <th>Description</th>
                            <th class="text-center">Active Users</th>
                            <th>Classification</th>
                            @if($canManage)
                                <th class="pe-4 text-end" width="160">Actions</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($roles as $role)
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-2.5">
                                        <div class="rounded-3 bg-primary-subtle text-primary p-2 d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;">
                                            <i class="fa-solid fa-shield-halved"></i>
                                        </div>
                                        <span class="fw-bold text-dark fs-6">{{ $role->name }}</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-secondary border rounded-pill font-monospace px-2.5 py-1" style="font-size: 0.75rem;">
                                        {{ $role->code }}
                                    </span>
                                </td>
                                <td>
                                    <span class="text-muted small">{{ $role->description ?? 'No description provided.' }}</span>
                                </td>
                                <td class="text-center font-monospace">
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1">
                                        <i class="fa-solid fa-users me-1"></i> {{ $role->users_count }}
                                    </span>
                                </td>
                                <td>
                                    @if($role->is_default)
                                        <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-2.5 py-1 font-monospace" style="font-size: 0.72rem;">
                                            <i class="fa-solid fa-lock me-1"></i> System Default
                                        </span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2.5 py-1 font-monospace" style="font-size: 0.72rem;">
                                            <i class="fa-solid fa-sliders me-1"></i> Custom Role
                                        </span>
                                    @endif
                                </td>
                                @if($canManage)
                                    <td class="pe-4 text-end">
                                        @if(!$role->is_default)
                                            <div class="d-flex justify-content-end gap-1.5">
                                                <a href="{{ route('roles.edit', $role) }}" class="btn btn-sm btn-outline-primary rounded-pill px-2.5 py-1" style="font-size: 0.78rem;">
                                                    <i class="fa-solid fa-pen me-1"></i> Edit
                                                </a>
                                                <form action="{{ route('roles.destroy', $role) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this role?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-2.5 py-1" style="font-size: 0.78rem;">
                                                        <i class="fa-solid fa-trash-can"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        @else
                                            <span class="text-muted small fst-italic">Protected</span>
                                        @endif
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $canManage ? '6' : '5' }}" class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-user-shield fs-2 opacity-50 mb-2"></i>
                                    <p class="mb-0 fw-medium">No roles found.</p>
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
