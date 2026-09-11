@extends('layouts.dashboard')

@section('title', 'Leaves & Approvals Workspace')

@section('content')

@php
    $currentUserService = new \App\Services\CurrentUserService();
    $currentUser = $currentUserService->getUser();
    $roleCode = strtolower($currentUser?->role?->code ?? '');
    $canApprove = $currentUserService->hasPermission('leaves.approve') || $currentUserService->hasPermission('leaves.manage');
@endphp

<div class="container-fluid px-0">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm border-0 mb-4" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm border-0 mb-4" role="alert">
            <i class="fa-solid fa-triangle-exclamation me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Top Navigation Bar & Action Buttons -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <h3 class="mb-1 fw-bold text-dark"><i class="fa-solid fa-calendar-check me-2 text-primary"></i> Leaves Workspace</h3>
            <p class="text-muted mb-0">
                @if($canApprove)
                    Manage team leave requests, review pending applications, and audit employee leave quotas.
                @else
                    Apply for leave and track your yearly 14-leaves quota breakdown.
                @endif
            </p>
        </div>

        <div class="d-flex align-items-center gap-3 flex-wrap">
            <button type="button" class="btn btn-outline-primary d-inline-flex align-items-center gap-2 fw-semibold px-3.5 py-2.5 rounded-3 text-nowrap" data-bs-toggle="modal" data-bs-target="#myLeaveQuotaModal">
                <i class="fa-solid fa-chart-pie"></i> Quota Details (14 Leaves/Yr)
            </button>
            <button type="button" class="btn btn-primary d-inline-flex align-items-center gap-2 fw-semibold px-4 py-2.5 rounded-3 text-nowrap" data-bs-toggle="modal" data-bs-target="#applyLeaveModal">
                <i class="fa-solid fa-plus"></i> Apply for Leave
            </button>
        </div>
    </div>

    <!-- Personal Overview Stat Cards (Always visible to everyone) -->
    <div class="row g-3 mb-4">
        <div class="col-md-{{ $canApprove ? '3' : '4' }}">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold text-uppercase font-monospace" style="font-size: 0.68rem; letter-spacing: 0.5px;">Remaining Balance</span>
                    <div class="rounded-2 bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                        <i class="fa-solid fa-wallet" style="font-size: 0.85rem;"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline gap-1.5">
                    <h3 class="mb-0 fw-bold text-dark font-monospace fs-4">{{ $currentUser->remaining_leaves_days }}</h3>
                    <span class="text-muted small font-monospace" style="font-size: 0.78rem;">/ 14 Days</span>
                </div>
            </div>
        </div>

        <div class="col-md-{{ $canApprove ? '3' : '4' }}">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold text-uppercase font-monospace" style="font-size: 0.68rem; letter-spacing: 0.5px;">Leaves Taken (This Year)</span>
                    <div class="rounded-2 bg-warning-subtle text-warning d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                        <i class="fa-solid fa-plane-departure" style="font-size: 0.85rem;"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline gap-1.5">
                    <h3 class="mb-0 fw-bold text-warning font-monospace fs-4">{{ $currentUser->approved_leaves_days }}</h3>
                    <span class="text-muted small font-monospace" style="font-size: 0.78rem;">Days</span>
                </div>
            </div>
        </div>

        <div class="col-md-{{ $canApprove ? '3' : '4' }}">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold text-uppercase font-monospace" style="font-size: 0.68rem; letter-spacing: 0.5px;">Quarterly Allowance</span>
                    <div class="rounded-2 bg-info-subtle text-info d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                        <i class="fa-solid fa-calendar-days" style="font-size: 0.85rem;"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline gap-1.5">
                    <h3 class="mb-0 fw-bold text-info font-monospace fs-4">3.5</h3>
                    <span class="text-muted small font-monospace" style="font-size: 0.78rem;">Days / Qtr</span>
                </div>
            </div>
        </div>

        @if($canApprove)
            <div class="col-md-3">
                <div class="card border-0 shadow-sm rounded-3 p-3 bg-white h-100">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small fw-semibold text-uppercase font-monospace" style="font-size: 0.68rem; letter-spacing: 0.5px;">Pending Approvals</span>
                        <div class="rounded-2 bg-success-subtle text-success d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                            <i class="fa-solid fa-clock" style="font-size: 0.85rem;"></i>
                        </div>
                    </div>
                    <div class="d-flex align-items-baseline gap-1.5">
                        <h3 class="mb-0 fw-bold text-success font-monospace fs-4">{{ $pendingApprovals->count() }}</h3>
                        <span class="text-muted small font-monospace" style="font-size: 0.78rem;">Requests</span>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Separate Personal Section for Regular Employees -->
    @if(!$canApprove)
        <!-- REGULAR EMPLOYEE VIEW: Only Personal Leave Log & Quota -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 bg-white">
            <div class="card-header bg-white py-3 px-4 border-bottom">
                <h5 class="mb-0 fw-bold text-dark fs-6"><i class="fa-solid fa-user-clock text-primary me-2"></i> My Personal Leave History</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Leave Type</th>
                                <th>Duration (Kab se Kab tak)</th>
                                <th>Reason / Remarks</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($myLeaves as $leave)
                                <tr>
                                    <td class="ps-4">
                                        @if($leave->type === 'full_day')
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill">Full Day</span>
                                        @elseif($leave->type === 'half_day')
                                            <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill">Half Day</span>
                                        @else
                                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill">Short Leave</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-block text-dark fw-bold font-monospace">{{ $leave->start_date->format('d M Y (D)') }}</div>
                                        @if($leave->start_date->ne($leave->end_date))
                                            <small class="text-muted d-block font-monospace">to {{ $leave->end_date->format('d M Y (D)') }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="text-muted small text-truncate d-inline-block" style="max-width: 250px;" title="{{ $leave->reason }}">
                                            {{ $leave->reason }}
                                        </span>
                                        @if($leave->manager_remarks)
                                            <div class="mt-1 small text-primary fw-semibold" style="font-size: 0.72rem;">
                                                <i class="fa-solid fa-comment-dots me-1"></i>Remarks: {{ $leave->manager_remarks }}
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        @if($leave->status === 'approved')
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">Approved</span>
                                        @elseif($leave->status === 'rejected')
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill">Rejected</span>
                                        @elseif($leave->status === 'cancelled')
                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill">Cancelled</span>
                                        @else
                                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill">Pending Approval</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-4">
                                        @if($leave->status === 'pending')
                                            <form action="{{ route('leaves.cancel', $leave->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Cancel this leave request?');">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3" style="font-size: 0.75rem;">
                                                    Cancel Request
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">You have not submitted any leave requests yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    @else
        <!-- WORKSPACE TABS FOR MANAGERS & ADMIN/HR -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 bg-white">
            <div class="card-header bg-white border-bottom px-4 py-3">
                <ul class="nav nav-pills card-header-pills gap-2" id="leaveTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active rounded-pill px-4 py-2 fw-semibold d-flex align-items-center gap-2" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending" type="button" role="tab">
                            <i class="fa-solid fa-clipboard-check text-warning"></i> Pending Applications
                            @if($pendingApprovals->count() > 0)
                                <span class="badge bg-warning text-dark rounded-pill">{{ $pendingApprovals->count() }}</span>
                            @endif
                        </button>
                    </li>

                    <li class="nav-item" role="presentation">
                        <button class="nav-link rounded-pill px-4 py-2 fw-semibold d-flex align-items-center gap-2" id="onleave-tab" data-bs-toggle="tab" data-bs-target="#onleave" type="button" role="tab">
                            <i class="fa-solid fa-user-slash text-danger"></i> Currently On Leave
                            @if($todayLeaves->count() > 0)
                                <span class="badge bg-danger text-white rounded-pill">{{ $todayLeaves->count() }}</span>
                            @endif
                        </button>
                    </li>

                    @if(in_array($roleCode, ['admin', 'hr', 'owner']))
                    <li class="nav-item" role="presentation">
                        <button class="nav-link rounded-pill px-4 py-2 fw-semibold d-flex align-items-center gap-2" id="balances-tab" data-bs-toggle="tab" data-bs-target="#balances" type="button" role="tab">
                            <i class="fa-solid fa-users-viewfinder text-primary"></i> Employee Leave Balances
                        </button>
                    </li>
                    @endif

                    <li class="nav-item" role="presentation">
                        <button class="nav-link rounded-pill px-4 py-2 fw-semibold d-flex align-items-center gap-2" id="myleaves-tab" data-bs-toggle="tab" data-bs-target="#myleaves" type="button" role="tab">
                            <i class="fa-solid fa-user-clock text-info"></i> My Personal Leave Log
                        </button>
                    </li>
                </ul>
            </div>

            <div class="card-body p-0">
                <div class="tab-content" id="leaveTabsContent">

                    <!-- TAB 1: Pending Approvals Queue -->
                    <div class="tab-pane fade show active p-4" id="pending" role="tabpanel">
                        <div class="row g-3">
                            @forelse($pendingApprovals as $approval)
                                <div class="col-lg-6">
                                    <div class="card border-0 rounded-4 shadow-sm h-100 hover-shadow transition-all bg-white p-3.5">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold fs-5" style="width: 44px; height: 44px;">
                                                    {{ strtoupper(substr($approval->user->first_name, 0, 1)) }}
                                                </div>
                                                <div>
                                                    <h6 class="mb-0 fw-bold text-dark fs-6">{{ $approval->user->full_name }}</h6>
                                                    <small class="text-muted">{{ $approval->user->display_designation }} &bull; {{ $approval->user->department?->name ?? 'No Dept' }}</small>
                                                </div>
                                            </div>
                                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-3 py-1 font-monospace">Pending</span>
                                        </div>

                                        <div class="bg-light rounded-3 p-3 mb-3">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill text-capitalize">
                                                    {{ str_replace('_', ' ', $approval->type) }}
                                                </span>
                                                <small class="fw-bold font-monospace text-dark">
                                                    <i class="fa-regular fa-calendar me-1 text-primary"></i>
                                                    {{ $approval->start_date->format('M d, Y') }}
                                                    @if($approval->start_date->ne($approval->end_date))
                                                        to {{ $approval->end_date->format('M d, Y') }}
                                                    @endif
                                                </small>
                                            </div>
                                            <p class="text-secondary small mb-0 font-italic">
                                                "{{ $approval->reason }}"
                                            </p>
                                        </div>

                                        <div class="d-flex justify-content-between align-items-center pt-1">
                                            <a href="{{ route('leaves.show', $approval->user_id) }}" class="btn btn-sm btn-outline-info rounded-pill px-3 fw-semibold">
                                                <i class="fa-solid fa-eye me-1"></i> View Details
                                            </a>

                                            <div class="d-flex gap-2">
                                                <form action="{{ route('leaves.approve', $approval->id) }}" method="POST" class="m-0">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-sm btn-success rounded-pill px-3 fw-semibold">
                                                        <i class="fa-solid fa-check me-1"></i> Approve
                                                    </button>
                                                </form>
                                                <form action="{{ route('leaves.reject', $approval->id) }}" method="POST" class="m-0">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-semibold">
                                                        <i class="fa-solid fa-xmark me-1"></i> Reject
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center py-5 text-muted col-12">
                                        <i class="fa-solid fa-circle-check fs-1 text-success opacity-50 mb-2"></i>
                                        <h6 class="fw-bold mb-1">No Pending Applications!</h6>
                                        <p class="mb-0 text-muted small">All employee & manager leave requests are reviewed.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>

                    <!-- TAB: Currently On Leave Employees -->
                    <div class="tab-pane fade p-4" id="onleave" role="tabpanel">
                        <div class="row g-3">
                            @forelse($todayLeaves as $activeLeave)
                                <div class="col-lg-6">
                                    <div class="card border-0 rounded-4 shadow-sm h-100 bg-white p-3.5">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="rounded-circle bg-danger text-white d-flex align-items-center justify-content-center fw-bold fs-5" style="width: 44px; height: 44px;">
                                                    {{ strtoupper(substr($activeLeave->user->first_name, 0, 1)) }}
                                                </div>
                                                <div>
                                                    <h6 class="mb-0 fw-bold text-dark fs-6">{{ $activeLeave->user->full_name }}</h6>
                                                    <small class="text-muted">{{ $activeLeave->user->display_designation }} &bull; {{ $activeLeave->user->department?->name ?? 'General' }}</small>
                                                </div>
                                            </div>
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-1 font-monospace">On Leave Today</span>
                                        </div>

                                        <div class="bg-light rounded-3 p-3">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill text-capitalize">
                                                    {{ str_replace('_', ' ', $activeLeave->type) }}
                                                </span>
                                                <small class="fw-bold font-monospace text-dark">
                                                    <i class="fa-regular fa-calendar me-1 text-danger"></i>
                                                    {{ $activeLeave->start_date->format('M d, Y') }}
                                                    @if($activeLeave->start_date->ne($activeLeave->end_date))
                                                        to {{ $activeLeave->end_date->format('M d, Y') }}
                                                    @endif
                                                </small>
                                            </div>
                                            <p class="text-secondary small mb-0 font-italic">
                                                "{{ $activeLeave->reason }}"
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-5 text-muted col-12">
                                    <i class="fa-solid fa-users-slash fs-1 text-secondary opacity-50 mb-2"></i>
                                    <h6 class="fw-bold mb-1">No Employees Currently On Leave</h6>
                                    <p class="mb-0 text-muted small">All team members are active today.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- TAB 2: Manager & Employee Leave Balances Ledger with Search -->
                    <div class="tab-pane fade p-4" id="balances" role="tabpanel">
                        
                        <!-- Real-time Search Input Bar -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0 text-muted rounded-start-pill ps-3">
                                        <i class="fa-solid fa-magnifying-glass"></i>
                                    </span>
                                    <input type="text" id="employeeLeaveSearch" class="form-control border-start-0 rounded-end-pill ps-0" placeholder="Search by employee name or designation...">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0 text-muted rounded-start-pill ps-3">
                                        <i class="fa-solid fa-filter"></i>
                                    </span>
                                    <input type="text" id="departmentLeaveFilter" class="form-control border-start-0 rounded-end-pill ps-0" placeholder="Filter by department name (e.g. Engineering, Sales)...">
                                </div>
                            </div>
                        </div>

                        <!-- Balances Directory Table -->
                        <div class="table-responsive border rounded-4 bg-white">
                            <table class="table table-hover align-middle mb-0" id="leaveBalancesTable">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">Member Name</th>
                                        <th>Designation</th>
                                        <th>Department</th>
                                        <th>Yearly Quota</th>
                                        <th>Leaves Taken</th>
                                        <th>Remaining Quota</th>
                                        <th class="text-end pe-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($employeeBalances as $emp)
                                        <tr class="emp-row" data-name="{{ strtolower($emp->full_name) }} {{ strtolower($emp->display_designation) }}" data-dept="{{ strtolower($emp->department?->name ?? 'no department') }}">
                                            <td class="ps-4 py-3">
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold" style="width: 38px; height: 38px; font-size: 0.85rem;">
                                                        {{ strtoupper(substr($emp->first_name, 0, 1)) }}
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold text-dark fs-6">{{ $emp->full_name }}</div>
                                                        <small class="text-muted">{{ $emp->email }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1.5 fw-semibold fs-7">{{ $emp->display_designation }}</span></td>
                                            <td><span class="badge bg-light text-dark border px-2.5 py-1">{{ $emp->department?->name ?? 'Unassigned' }}</span></td>
                                            <td class="font-monospace fw-semibold">14 Days</td>
                                            <td class="font-monospace text-warning fw-bold">{{ $emp->approved_leaves_days }} Days</td>
                                            <td class="font-monospace text-success fw-bold">
                                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1">
                                                    {{ $emp->remaining_leaves_days }} Days Left
                                                </span>
                                            </td>
                                            <td class="text-end pe-4">
                                                <a href="{{ route('leaves.show', $emp->id) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold d-inline-flex align-items-center gap-1.5">
                                                    <i class="fa-solid fa-eye"></i> View Details
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr id="noResultsRow">
                                            <td colspan="7" class="text-center py-5 text-muted">No managers or employees found in organization.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- TAB 3: My Personal Leave Log for Admin/Manager -->
                    <div class="tab-pane fade p-0" id="myleaves" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">Type</th>
                                        <th>Duration</th>
                                        <th>Reason</th>
                                        <th>Status</th>
                                        <th class="text-end pe-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($myLeaves as $leave)
                                        <tr>
                                            <td class="ps-4">
                                                @if($leave->type === 'full_day')
                                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill">Full Day</span>
                                                @elseif($leave->type === 'half_day')
                                                    <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill">Half Day</span>
                                                @else
                                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill">Short Leave</span>
                                                @endif
                                            </td>
                                            <td>
                                                <small class="d-block text-dark fw-bold font-monospace">{{ $leave->start_date->format('M d, Y') }}</small>
                                                @if($leave->start_date->ne($leave->end_date))
                                                    <small class="text-muted d-block font-monospace">to {{ $leave->end_date->format('M d, Y') }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="text-muted small text-truncate d-inline-block" style="max-width: 250px;" title="{{ $leave->reason }}">
                                                    {{ $leave->reason }}
                                                </span>
                                                @if($leave->manager_remarks)
                                                    <div class="mt-1 small text-primary fw-semibold" style="font-size: 0.72rem;">
                                                        <i class="fa-solid fa-comment-dots me-1"></i>Remarks: {{ $leave->manager_remarks }}
                                                    </div>
                                                @endif
                                            </td>
                                            <td>
                                                @if($leave->status === 'approved')
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">Approved</span>
                                                @elseif($leave->status === 'rejected')
                                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill">Rejected</span>
                                                @elseif($leave->status === 'cancelled')
                                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill">Cancelled</span>
                                                @else
                                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill">Pending</span>
                                                @endif
                                            </td>
                                            <td class="text-end pe-4">
                                                @if($leave->status === 'pending')
                                                    <form action="{{ route('leaves.cancel', $leave->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Cancel this leave request?');">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3" style="font-size: 0.75rem;">
                                                            Cancel
                                                        </button>
                                                    </form>
                                                @else
                                                    <span class="text-muted small">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-5 text-muted">You have not submitted any leave requests yet.</td>
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

</div>

<!-- MODAL: Personal 14-Leaves Yearly Quota Breakdown Details -->
<div class="modal fade" id="myLeaveQuotaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom py-3 px-4 bg-primary text-white">
                <h5 class="modal-title fw-bold fs-6"><i class="fa-solid fa-chart-pie me-2"></i> My Personal Yearly Quota & Quarterly Breakdown</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="p-3 bg-light rounded-3 text-center border">
                            <span class="text-muted small fw-bold text-uppercase d-block mb-1">Yearly Quota</span>
                            <h3 class="mb-0 fw-bold text-dark font-monospace">14 <span class="fs-6 text-muted">Days</span></h3>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 bg-light rounded-3 text-center border">
                            <span class="text-muted small fw-bold text-uppercase d-block mb-1">Approved Taken</span>
                            <h3 class="mb-0 fw-bold text-warning font-monospace">{{ $currentUser->approved_leaves_days }} <span class="fs-6 text-muted">Days</span></h3>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 bg-light rounded-3 text-center border">
                            <span class="text-muted small fw-bold text-uppercase d-block mb-1">Remaining Balance</span>
                            <h3 class="mb-0 fw-bold text-success font-monospace">{{ $currentUser->remaining_leaves_days }} <span class="fs-6 text-muted">Days</span></h3>
                        </div>
                    </div>
                </div>

                <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-list-check me-2 text-primary"></i> Quarterly Division (3.5 Days per Quarter)</h6>
                <div class="table-responsive border rounded-3">
                    <table class="table table-hover align-middle mb-0 text-center">
                        <thead class="table-light">
                            <tr>
                                <th class="text-start ps-3">Quarter</th>
                                <th>Quarter Quota</th>
                                <th>Leaves Taken</th>
                                <th>Remaining Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($currentUser->quarterly_leave_stats as $qName => $qData)
                                <tr>
                                    <td class="fw-bold text-start ps-3">{{ $qName }}</td>
                                    <td>{{ $qData['quota'] }} Days</td>
                                    <td class="text-warning fw-bold">{{ $qData['taken'] }} Days</td>
                                    <td class="text-success fw-bold">{{ $qData['remaining'] }} Days</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer border-top py-3 px-4">
                <button type="button" class="btn btn-secondary rounded-3 px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Apply Leave -->
<div class="modal fade" id="applyLeaveModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form action="{{ route('leaves.store') }}" method="POST">
                @csrf
                <div class="modal-header border-bottom py-3 px-4">
                    <h5 class="modal-title fw-bold text-dark fs-6"><i class="fa-solid fa-plane-departure me-2 text-primary"></i> Apply for Leave</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Leave Type</label>
                        <select name="type" class="form-select rounded-3" required>
                            <option value="full_day">Full Day Leave</option>
                            <option value="first_half">First Half Leave (Morning - until 2:00 PM)</option>
                            <option value="second_half">Second Half Leave (Afternoon - from 1:30 PM)</option>
                            <option value="half_day">Half Day Leave</option>
                            <option value="short_leave">Short Leave (Few Hours)</option>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Start Date</label>
                            <input type="date" name="start_date" class="form-control rounded-3" required value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">End Date</label>
                            <input type="date" name="end_date" class="form-control rounded-3" required value="{{ date('Y-m-d') }}">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Reason / Remarks</label>
                        <textarea name="reason" rows="3" class="form-control rounded-3" placeholder="State reason for your leave request..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top py-3 px-4">
                    <button type="button" class="btn btn-light rounded-3 px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-3 px-4 fw-bold">Submit Request</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('employeeLeaveSearch');
    const filterInput = document.getElementById('departmentLeaveFilter');
    const tableRows = document.querySelectorAll('#leaveBalancesTable .emp-row');

    function filterLeaveTable() {
        const queryName = searchInput ? searchInput.value.toLowerCase().trim() : '';
        const queryDept = filterInput ? filterInput.value.toLowerCase().trim() : '';

        tableRows.forEach(row => {
            const nameData = row.getAttribute('data-name') || '';
            const deptData = row.getAttribute('data-dept') || '';

            const matchesName = nameData.includes(queryName);
            const matchesDept = deptData.includes(queryDept);

            if (matchesName && matchesDept) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    if (searchInput) searchInput.addEventListener('keyup', filterLeaveTable);
    if (filterInput) filterInput.addEventListener('keyup', filterLeaveTable);
});
</script>

@endsection
