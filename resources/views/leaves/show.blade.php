@extends('layouts.dashboard')

@section('title', $user->full_name . ' - Leave Audit & Details')

@section('content')

<div class="container-fluid px-0">

    <!-- Back Button & Page Header -->
    <div class="mb-4">
        <a href="{{ route('leaves.index') }}" class="text-secondary text-decoration-none small hover-primary fw-medium mb-3 d-inline-block">
            <i class="fa-solid fa-arrow-left me-1"></i> Back to Leaves Workspace
        </a>
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold fs-4" style="width: 48px; height: 48px;">
                    {{ strtoupper(substr($user->first_name, 0, 1)) }}
                </div>
                <div>
                    <h4 class="mb-1 fw-bold text-dark">{{ $user->full_name }}</h4>
                    <div class="d-flex align-items-center gap-2 text-muted small">
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1 fw-semibold">{{ $user->display_designation }}</span>
                        <span>&bull; {{ $user->department?->name ?? 'No Department' }}</span>
                        <span>&bull; {{ $user->email }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Personal Overview Stat Cards (Compact & Borderless) -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold text-uppercase font-monospace" style="font-size: 0.68rem; letter-spacing: 0.5px;">Yearly Quota</span>
                    <div class="rounded-3 bg-primary-subtle text-primary p-2">
                        <i class="fa-solid fa-wallet fs-6"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline gap-1.5">
                    <h4 class="mb-0 fw-bold text-dark font-monospace fs-4">14</h4>
                    <span class="text-muted small font-monospace" style="font-size: 0.75rem;">Days / Year</span>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold text-uppercase font-monospace" style="font-size: 0.68rem; letter-spacing: 0.5px;">Approved Taken</span>
                    <div class="rounded-3 bg-warning-subtle text-warning p-2">
                        <i class="fa-solid fa-plane-departure fs-6"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline gap-1.5">
                    <h4 class="mb-0 fw-bold text-warning font-monospace fs-4">{{ $user->approved_leaves_days }}</h4>
                    <span class="text-muted small font-monospace" style="font-size: 0.75rem;">Days</span>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold text-uppercase font-monospace" style="font-size: 0.68rem; letter-spacing: 0.5px;">Remaining Balance</span>
                    <div class="rounded-3 bg-success-subtle text-success p-2">
                        <i class="fa-solid fa-clock fs-6"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline gap-1.5">
                    <h4 class="mb-0 fw-bold text-success font-monospace fs-4">{{ $user->remaining_leaves_days }}</h4>
                    <span class="text-muted small font-monospace" style="font-size: 0.75rem;">Days Left</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Quarterly Breakdown (3.5 Days / Qtr) -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 bg-white">
        <div class="card-header bg-white py-3 px-4 border-bottom">
            <h6 class="mb-0 fw-bold text-dark fs-6"><i class="fa-solid fa-chart-pie me-2 text-info"></i> Quarterly Division Breakdown (3.5 Days per Quarter)</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-center">
                    <thead class="table-light text-uppercase text-muted fw-bold small">
                        <tr>
                            <th class="text-start ps-4 py-3">Quarter Period</th>
                            <th class="py-3">Quarter Allowed Quota</th>
                            <th class="py-3">Leaves Taken</th>
                            <th class="py-3">Remaining Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($user->quarterly_leave_stats as $qName => $qData)
                            <tr>
                                <td class="fw-bold text-start ps-4 py-3">{{ $qName }}</td>
                                <td class="font-monospace fw-semibold py-3">{{ $qData['quota'] }} Days</td>
                                <td class="font-monospace text-warning fw-bold py-3">{{ $qData['taken'] }} Days</td>
                                <td class="font-monospace text-success fw-bold py-3">{{ $qData['remaining'] }} Days</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Complete Leave Request History -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-4">
        <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold text-dark fs-6"><i class="fa-solid fa-clock-rotate-left me-2 text-primary"></i> Complete Applied Leaves History</h6>
            <span class="badge bg-primary text-white rounded-pill px-3 py-1 font-monospace">{{ $user->leaves->count() }} Total Applications</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-uppercase text-muted fw-bold small">
                        <tr>
                            <th class="ps-4 py-3">Type</th>
                            <th class="py-3">Leave Duration</th>
                            <th class="py-3">Reason / Remarks</th>
                            <th class="py-3">Applied On</th>
                            <th class="text-end pe-4 py-3">Status / Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($user->leaves as $lHist)
                            <tr>
                                <td class="ps-4 py-3">
                                    @if($lHist->type === 'full_day')
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill">Full Day</span>
                                    @elseif($lHist->type === 'half_day')
                                        <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill">Half Day</span>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill">Short Leave</span>
                                    @endif
                                </td>
                                <td class="py-3">
                                    <div class="fw-bold text-dark font-monospace">{{ $lHist->start_date->format('d M Y (D)') }}</div>
                                    @if($lHist->start_date->ne($lHist->end_date))
                                        <small class="text-muted font-monospace">to {{ $lHist->end_date->format('d M Y (D)') }}</small>
                                    @endif
                                </td>
                                <td class="py-3">
                                    <span class="text-muted small d-inline-block" style="max-width: 300px;">
                                        {{ $lHist->reason }}
                                    </span>
                                </td>
                                <td class="small text-muted font-monospace py-3">
                                    {{ $lHist->created_at->format('d M Y, h:i A') }}
                                </td>
                                <td class="text-end pe-4 py-3">
                                    @if($lHist->status === 'approved')
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1.5">Approved</span>
                                    @elseif($lHist->status === 'rejected')
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-1.5">Rejected</span>
                                    @elseif($lHist->status === 'cancelled')
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-3 py-1.5">Cancelled</span>
                                    @else
                                        <div class="d-flex justify-content-end gap-2">
                                            <form action="{{ route('leaves.approve', $lHist->id) }}" method="POST" class="m-0">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-success rounded-pill px-3">Approve</button>
                                            </form>
                                            <form action="{{ route('leaves.reject', $lHist->id) }}" method="POST" class="m-0">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3">Reject</button>
                                            </form>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">No active leave applications recorded for this employee.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

@endsection
