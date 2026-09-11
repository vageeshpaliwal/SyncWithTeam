@extends('layouts.dashboard')

@section('title', 'Company Announcements & Notices')

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
            <h3 class="mb-1 fw-bold text-dark"><i class="fa-solid fa-bullhorn me-2 text-primary"></i> Company Announcements</h3>
            <p class="text-muted mb-0">Official company broadcasts, policy notices, and news feed.</p>
        </div>

        @if($isAdminOrHr)
            <button type="button" class="btn btn-primary d-inline-flex align-items-center gap-2 fw-semibold px-4 py-2.5 rounded-3 text-nowrap shadow-2xs" data-bs-toggle="modal" data-bs-target="#addAnnouncementModal">
                <i class="fa-solid fa-plus"></i> Post Announcement
            </button>
        @endif
    </div>

    <!-- Announcement Cards Feed -->
    <div class="row g-3">
        @forelse($announcements as $anc)
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4 bg-white p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold fs-5" style="width: 44px; height: 44px;">
                                <i class="fa-solid fa-bullhorn"></i>
                            </div>
                            <div>
                                <h5 class="mb-1 fw-bold text-dark fs-5">{{ $anc->title }}</h5>
                                <div class="text-muted small font-monospace">
                                    Posted by <strong>{{ $anc->author?->full_name ?? 'HR' }}</strong> &bull; {{ $anc->created_at->diffForHumans() }}
                                </div>
                            </div>
                        </div>

                        <div class="d-flex align-items-center gap-2">
                            @if($anc->priority === 'high')
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-1 font-monospace">High Priority</span>
                            @elseif($anc->priority === 'medium')
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-3 py-1 font-monospace">Medium Priority</span>
                            @else
                                <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-3 py-1 font-monospace">General Info</span>
                            @endif

                            @if($isAdminOrHr)
                                <form action="{{ route('announcements.destroy', $anc->id) }}" method="POST" class="m-0" onsubmit="return confirm('Delete this announcement?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle p-0 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>

                    <div class="p-3 bg-light rounded-3 text-secondary lh-base fs-6">
                        {!! nl2br(e($anc->content)) !!}
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="text-center py-5 bg-white rounded-4 border-0 shadow-sm p-4">
                    <i class="fa-solid fa-bullhorn fs-1 text-secondary opacity-50 mb-3 d-block"></i>
                    <h6 class="fw-bold text-dark mb-1">No Announcements Posted Yet</h6>
                    <p class="text-muted small mb-0">Check back later for company updates and notices.</p>
                </div>
            </div>
        @endforelse
    </div>

</div>

<!-- Modal: Post Announcement -->
@if($isAdminOrHr)
<div class="modal fade" id="addAnnouncementModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow">
            <form action="{{ route('announcements.store') }}" method="POST">
                @csrf
                <div class="modal-header border-bottom py-3 px-4">
                    <h5 class="modal-title fw-bold text-dark fs-6"><i class="fa-solid fa-bullhorn me-2 text-primary"></i> Post New Announcement</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Title</label>
                        <input type="text" name="title" class="form-control rounded-3" placeholder="e.g. Office Closed for Festival, Quarterly All-Hands Meeting" required>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Priority</label>
                            <select name="priority" class="form-select rounded-3" required>
                                <option value="medium">Medium Priority</option>
                                <option value="high">High Priority (Urgent)</option>
                                <option value="low">Low Priority (General Info)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Expiration Date (Optional)</label>
                            <input type="date" name="expires_at" class="form-control rounded-3">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Content / Announcement Details</label>
                        <textarea name="content" rows="5" class="form-control rounded-3" placeholder="Write full announcement content here..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top py-3 px-4">
                    <button type="button" class="btn btn-light rounded-3 px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-3 px-4 fw-bold">Broadcast Announcement</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@endsection
