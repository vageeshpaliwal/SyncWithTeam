@extends('layouts.dashboard')

@section('title', 'Company Holiday Calendar')

@section('content')

@php
    $currentUserService = new \App\Services\CurrentUserService();
    $currentUser = $currentUserService->getUser();
    $roleCode = strtolower($currentUser?->role?->code ?? '');
    $isAdminOrHr = in_array($roleCode, ['admin', 'hr', 'owner']);
@endphp

<div class="container-fluid px-0">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm border-0 mb-4" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Top Header & Action -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <h3 class="mb-1 fw-bold text-dark"><i class="fa-solid fa-calendar-day me-2 text-primary"></i> Company Holidays</h3>
            <p class="text-muted mb-0">Official company holiday schedule and non-working day calendar.</p>
        </div>

        @if($isAdminOrHr)
            <button type="button" class="btn btn-primary d-inline-flex align-items-center gap-2 fw-semibold px-4 py-2.5 rounded-3 text-nowrap shadow-2xs" data-bs-toggle="modal" data-bs-target="#addHolidayModal">
                <i class="fa-solid fa-plus"></i> Add Holiday
            </button>
        @endif
    </div>

    <!-- Holiday List Table -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-4">
        <div class="card-header bg-white py-3 px-4 border-bottom">
            <h6 class="mb-0 fw-bold text-dark fs-6"><i class="fa-solid fa-list-check text-primary me-2"></i> {{ date('Y') }} Holiday Calendar ({{ $holidays->count() }})</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-modern align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">Holiday Name</th>
                            <th>Date</th>
                            <th>Day</th>
                            <th>Type</th>
                            <th>Description</th>
                            @if($isAdminOrHr)
                                <th class="text-end pe-4">Action</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($holidays as $h)
                            <tr>
                                <td class="ps-4 py-3 fw-bold text-dark fs-6">
                                    <i class="fa-solid fa-gift me-2 text-primary"></i>{{ $h->name }}
                                </td>
                                <td class="py-3 font-monospace fw-semibold">
                                    {{ $h->date->format('M d, Y') }}
                                </td>
                                <td class="py-3 font-monospace">
                                    <span class="badge bg-light text-dark border px-2.5 py-1">{{ $h->date->format('l') }}</span>
                                </td>
                                <td class="py-3">
                                    @if($h->is_optional)
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-3 py-1 font-monospace">Optional</span>
                                    @else
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1 font-monospace">Mandatory</span>
                                    @endif
                                </td>
                                <td class="py-3 text-muted small">
                                    {{ $h->description ?: '-' }}
                                </td>
                                @if($isAdminOrHr)
                                    <td class="text-end pe-4 py-3">
                                        <form action="{{ route('holidays.destroy', $h->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this holiday?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3" style="font-size: 0.75rem;">
                                                <i class="fa-solid fa-trash-can me-1"></i> Delete
                                            </button>
                                        </form>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $isAdminOrHr ? '6' : '5' }}" class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-calendar-xmark fs-1 text-secondary opacity-50 mb-2 d-block"></i>
                                    No holidays listed for this year.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- Modal: Add Holiday -->
@if($isAdminOrHr)
<div class="modal fade" id="addHolidayModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form action="{{ route('holidays.store') }}" method="POST">
                @csrf
                <div class="modal-header border-bottom py-3 px-4">
                    <h5 class="modal-title fw-bold text-dark fs-6"><i class="fa-solid fa-plus-circle me-2 text-primary"></i> Add New Holiday</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Holiday Name</label>
                        <input type="text" name="name" class="form-control rounded-3" placeholder="e.g. Diwali, Independence Day" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Date</label>
                        <input type="date" name="date" class="form-control rounded-3" required value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Description (Optional)</label>
                        <textarea name="description" rows="2" class="form-control rounded-3" placeholder="Brief note about the holiday..."></textarea>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="is_optional" id="is_optional" value="1">
                        <label class="form-check-label small text-muted" for="is_optional">
                            Mark as Optional Restricted Holiday
                        </label>
                    </div>
                </div>
                <div class="modal-footer border-top py-3 px-4">
                    <button type="button" class="btn btn-light rounded-3 px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-3 px-4 fw-bold">Save Holiday</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@endsection
