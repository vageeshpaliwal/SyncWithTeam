@extends('layouts.dashboard')

@section('title', 'Users & Team Directory')

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

    <!-- Header Section -->
    <div class="page-hero d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h4 class="fw-bold text-dark mb-1">Users & Team Directory</h4>
            <p class="text-muted small mb-0">
                Manage organization employees, managers, departments, and pending invitations.
            </p>
        </div>

        <a href="{{ route('users.invite') }}" class="btn btn-primary rounded-pill px-3.5 py-2 fw-semibold d-flex align-items-center gap-2 shadow-sm">
            <i class="fa-solid fa-user-plus"></i> Add User
        </a>
    </div>

    <!-- Navigation Tabs -->
    <ul class="nav nav-pills mb-4 gap-2 border-bottom pb-3">
        <li class="nav-item">
            <a class="nav-link rounded-pill px-3.5 py-2 fw-semibold {{ $tab === 'users' ? 'active bg-primary' : 'bg-white text-dark border' }}"
               href="{{ route('users.index', ['tab' => 'users']) }}">
                <i class="fa-solid fa-users me-1.5"></i> All Members ({{ $users->count() }})
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link rounded-pill px-3.5 py-2 fw-semibold {{ $tab === 'pending' ? 'active bg-primary' : 'bg-white text-dark border' }}"
               href="{{ route('users.index', ['tab' => 'pending']) }}">
                <i class="fa-solid fa-clock-rotate-left me-1.5"></i> Pending Invitations ({{ $pendingInvitations->count() }})
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link rounded-pill px-3.5 py-2 fw-semibold {{ $tab === 'accepted' ? 'active bg-primary' : 'bg-white text-dark border' }}"
               href="{{ route('users.index', ['tab' => 'accepted']) }}">
                <i class="fa-solid fa-user-check me-1.5"></i> Accepted ({{ $acceptedInvitations->count() }})
            </a>
        </li>
    </ul>

    @if($tab === 'users')
        <!-- Search & Department Filter Bar -->
        <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
            <div class="card-body p-3">
                <form action="{{ route('users.index') }}" method="GET" class="row g-2 align-items-center">
                    <input type="hidden" name="tab" value="users">
                    
                    <!-- Search Input -->
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                            <input type="text" name="search" class="form-control bg-light border-0" placeholder="Search by name, email, designation..." value="{{ request('search') }}">
                        </div>
                    </div>

                    <!-- Department Filter -->
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

                    <!-- Role Filter -->
                    <div class="col-md-3">
                        <select name="role_id" class="form-select bg-light border-0" onchange="this.form.submit()">
                            <option value="">All Roles (Employees & Managers)</option>
                            @foreach($roles as $r)
                                <option value="{{ $r->id }}" {{ request('role_id') == $r->id ? 'selected' : '' }}>
                                    {{ $r->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filter Action Buttons -->
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100 rounded-3 fw-semibold">
                            Filter
                        </button>
                        @if(request('search') || request('department_id') || request('role_id'))
                            <a href="{{ route('users.index', ['tab' => 'users']) }}" class="btn btn-light border rounded-3" title="Clear Filters">
                                <i class="fa-solid fa-xmark"></i>
                            </a>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Team Members Table / Listing View -->
    <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
        <div class="card-body p-0">

            @if($tab === 'users')
                <div class="table-responsive">
                    <table class="table table-modern align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4">Team Member</th>
                                <th>Role</th>
                                <th>Designation</th>
                                <th>Department</th>
                                <th>Tenure & Probation</th>
                                <th>Status</th>
                                <th class="pe-4 text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                                @php
                                    $roleCode = strtolower($user->role?->code ?? '');
                                    $roleBadge = match($roleCode) {
                                        'owner' => 'bg-danger text-white',
                                        'admin' => 'bg-purple text-white',
                                        'manager' => 'bg-info text-white',
                                        default => 'bg-secondary text-white',
                                    };
                                    $prob = $user->probation_status;
                                    $userPayload = [
                                        'name' => $user->full_name,
                                        'username' => $user->username,
                                        'email' => $user->email,
                                        'phone' => $user->phone ?? 'Not provided',
                                        'designation' => $user->display_designation,
                                        'department' => $user->department?->name ?? 'Unassigned',
                                        'role' => $user->role?->name ?? 'User',
                                        'status' => $user->status ? 'Active' : 'Inactive',
                                        'joined' => $user->joining_date ? $user->joining_date->format('M d, Y') : $user->created_at->format('M d, Y'),
                                        'tenure' => $user->tenure,
                                        'probation' => $prob,
                                        'dob' => $user->dob ? $user->dob->format('M d, Y') : 'Not provided',
                                        'father_name' => $user->father_name ?? 'Not provided',
                                        'mother_name' => $user->mother_name ?? 'Not provided',
                                        'aadhaar_number' => $user->aadhaar_number ?? 'Not provided',
                                        'pan_number' => $user->pan_number ?? 'Not provided',
                                        'remaining_leaves' => $user->remaining_leaves_days . ' Days',
                                        'projects' => $user->projects->pluck('name')->toArray(),
                                        'assigned_tasks' => $user->assignedTasks()->whereIn('status', ['todo', 'in_progress', 'review'])->pluck('title')->toArray(),
                                    ];
                                @endphp
                                <tr>
                                    <!-- Name & Avatar -->
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold flex-shrink-0"
                                                 style="width: 38px; height: 38px; font-size: 0.95rem; background: linear-gradient(135deg, #4F46E5 0%, #7C3AED 100%);">
                                                {{ strtoupper(substr($user->first_name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <h6 class="mb-0 fw-bold text-dark fs-6">{{ $user->full_name }}</h6>
                                                <small class="text-muted font-monospace" style="font-size: 0.75rem;">{{ $user->email }}</small>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Role -->
                                    <td>
                                        <span class="badge {{ $roleBadge }} rounded-pill px-2.5 py-1 text-uppercase font-monospace" style="font-size: 0.68rem;">
                                            {{ $user->role?->name ?? 'User' }}
                                        </span>
                                    </td>

                                    <!-- Designation & Salary -->
                                    <td>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1 fw-semibold mb-1 d-inline-block" style="font-size: 0.75rem;">
                                            {{ $user->display_designation }}
                                        </span>
                                        @if($user->basic_salary)
                                            <div class="text-success fw-bold font-monospace" style="font-size: 0.72rem;">
                                                <i class="fa-solid fa-indian-rupee-sign" style="font-size: 0.65rem;"></i>{{ number_format($user->basic_salary, 2) }} <span class="text-muted fw-normal" style="font-size: 0.65rem;">/ mo</span>
                                            </div>
                                        @endif
                                    </td>

                                    <!-- Department -->
                                    <td>
                                        @if($user->department)
                                            <span class="badge bg-light text-dark border rounded-pill font-monospace px-2.5" style="font-size: 0.75rem;">
                                                <i class="fa-solid fa-sitemap text-primary me-1"></i> {{ $user->department->name }}
                                            </span>
                                        @else
                                            <span class="text-muted small fst-italic">Unassigned</span>
                                        @endif
                                    </td>

                                    <!-- Tenure & Probation -->
                                    <td>
                                        <div class="lh-1">
                                            <span class="badge {{ $prob['badge'] }} rounded-pill font-monospace mb-1" style="font-size: 0.68rem;">
                                                {{ $prob['label'] }}
                                            </span>
                                            <small class="text-muted d-block font-monospace" style="font-size: 0.72rem;">{{ $user->tenure }}</small>
                                        </div>
                                    </td>

                                    <!-- Status -->
                                    <td>
                                        @if($user->status)
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 font-monospace" style="font-size: 0.7rem;">Active</span>
                                        @else
                                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-2.5 py-1 font-monospace" style="font-size: 0.7rem;">Inactive</span>
                                        @endif
                                    </td>

                                    <!-- Actions -->
                                    <td class="pe-4 text-end">
                                        <div class="d-flex justify-content-end gap-1.5">
                                            <!-- View Profile Link -->
                                            <a href="{{ route('users.show', $user->id) }}" class="btn btn-sm btn-outline-info rounded-pill px-2.5 py-1" style="font-size: 0.78rem;">
                                                <i class="fa-solid fa-eye me-1"></i> View
                                            </a>

                                            <!-- Edit User Button -->
                                            <a href="{{ route('users.edit', $user->id) }}" class="btn btn-sm btn-outline-primary rounded-pill px-2.5 py-1" style="font-size: 0.78rem;">
                                                <i class="fa-solid fa-pen me-1"></i> Edit
                                            </a>

                                            @if($user->id !== session('user_id'))
                                                <form action="{{ route('users.toggle-status', $user->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-sm {{ $user->status ? 'btn-outline-warning' : 'btn-outline-success' }} rounded-pill px-2.5 py-1" style="font-size: 0.78rem;">
                                                        {{ $user->status ? 'Deactivate' : 'Activate' }}
                                                    </button>
                                                </form>

                                                <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-2.5 py-1" style="font-size: 0.78rem;">
                                                        <i class="fa-solid fa-trash-can"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="fa-solid fa-users-slash fs-2 opacity-50 mb-2"></i>
                                        <p class="mb-0 fw-medium">No team members match your filter criteria.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            @elseif($tab === 'pending')
                <div class="table-responsive">
                    <table class="table table-modern align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4">Email Address</th>
                                <th>Role</th>
                                <th>Invited By</th>
                                <th>Expires At</th>
                                <th>Status</th>
                                <th class="pe-4 text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pendingInvitations as $invitation)
                                <tr>
                                    <td class="ps-4"><span class="fw-bold text-dark font-monospace">{{ $invitation->email }}</span></td>
                                    <td><span class="badge bg-secondary rounded-pill font-monospace px-2.5 py-1">{{ $invitation->role?->name ?? '-' }}</span></td>
                                    <td><span class="small text-muted">{{ $invitation->invitedBy?->full_name ?? '-' }}</span></td>
                                    <td><span class="small font-monospace">{{ $invitation->expires_at->format('M d, Y') }}</span></td>
                                    <td><span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill font-monospace">Pending</span></td>
                                    <td class="pe-4 text-end">
                                        <div class="d-flex justify-content-end gap-2">
                                            <form action="{{ route('invitations.resend', $invitation->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1" style="font-size: 0.78rem;">
                                                    Resend
                                                </button>
                                            </form>

                                            <form action="{{ route('invitations.cancel', $invitation->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to cancel this invitation?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1" style="font-size: 0.78rem;">
                                                    Cancel
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="fa-solid fa-envelope-open fs-2 opacity-50 mb-2"></i>
                                        <p class="mb-0 fw-medium">No pending invitations.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            @else
                <div class="table-responsive">
                    <table class="table table-modern align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4">Email Address</th>
                                <th>Role</th>
                                <th>Invited By</th>
                                <th>Accepted Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($acceptedInvitations as $invitation)
                                <tr>
                                    <td class="ps-4"><span class="fw-bold text-dark font-monospace">{{ $invitation->email }}</span></td>
                                    <td><span class="badge bg-secondary rounded-pill font-monospace px-2.5 py-1">{{ $invitation->role?->name ?? '-' }}</span></td>
                                    <td><span class="small text-muted">{{ $invitation->invitedBy?->full_name ?? '-' }}</span></td>
                                    <td><span class="small font-monospace">{{ $invitation->updated_at->format('M d, Y') }}</span></td>
                                    <td><span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill font-monospace">Accepted</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">No accepted invitations recorded.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif

        </div>
    </div>

</div>

@endsection
