@extends('layouts.dashboard')

@section('title', $bug->bug_number . ' - ' . $bug->title)

@section('content')

<div class="container-fluid px-0">

    <!-- Navigation Breadcrumb -->
    <div class="mb-3 d-flex align-items-center justify-content-between">
        <a href="{{ route('projects.show', $bug->project_id) }}" class="text-secondary text-decoration-none small fw-semibold">
            <i class="fa-solid fa-arrow-left me-1"></i> Back to Project: {{ $bug->project->name }}
        </a>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-danger text-white rounded-pill px-3 py-1 font-monospace fw-bold fs-7">
                {{ $bug->bug_number }}
            </span>
        </div>
    </div>

    <!-- Main Bug Detail Header Card -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
        <div class="card-body p-4">
            <div class="row align-items-start g-3">
                <div class="col-md-8">
                    <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                        <h3 class="fw-bold text-dark m-0">{{ $bug->title }}</h3>
                    </div>
                    <div class="d-flex align-items-center gap-3 text-muted small flex-wrap">
                        <span><i class="fa-solid fa-user me-1 text-primary"></i> Reported by <strong>{{ $bug->reporter?->full_name ?? 'System' }}</strong></span>
                        <span>&bull;</span>
                        <span><i class="fa-regular fa-clock me-1"></i> {{ $bug->created_at->format('M d, Y \a\t h:i A') }} ({{ $bug->created_at->diffForHumans() }})</span>
                    </div>
                </div>

                <div class="col-md-4 text-md-end">
                    <form action="{{ route('bugs.update-status', $bug->id) }}" method="POST" class="d-inline-block">
                        @csrf
                        @method('PATCH')
                        <label class="form-label text-muted d-block small fw-bold text-uppercase mb-1">Update Status</label>
                        <select name="status" class="form-select form-select-sm rounded-pill font-monospace fw-bold py-1.5 px-3 shadow-2xs" style="font-size: 0.85rem;" onchange="this.form.submit()">
                            <option value="open" {{ $bug->status === 'open' ? 'selected' : '' }}>🔴 Open</option>
                            <option value="in_progress" {{ $bug->status === 'in_progress' ? 'selected' : '' }}>🟡 In Progress / Fixing</option>
                            <option value="resolved" {{ $bug->status === 'resolved' ? 'selected' : '' }}>🟢 Resolved</option>
                            <option value="closed" {{ $bug->status === 'closed' ? 'selected' : '' }}>⚪ Closed</option>
                        </select>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Left: Detailed Info & Screenshot (8 cols) -->
        <div class="col-lg-8">
            <!-- Bug Description -->
            <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
                <div class="card-header bg-white py-3 px-4 border-bottom">
                    <h6 class="fw-bold text-dark mb-0"><i class="fa-solid fa-align-left text-primary me-2"></i>Detailed Issue Description</h6>
                </div>
                <div class="card-body p-4">
                    <div class="p-3 bg-light rounded-3 border text-dark" style="white-space: pre-wrap; font-size: 0.95rem; line-height: 1.6;">{{ $bug->description }}</div>
                </div>
            </div>

            <!-- Screenshot / Visual Proof -->
            @if($bug->screenshot_path)
                <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
                    <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold text-dark mb-0"><i class="fa-solid fa-image text-danger me-2"></i>Attached Screenshot / Visual Proof</h6>
                        <a href="{{ \Illuminate\Support\Facades\Storage::url($bug->screenshot_path) }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 font-monospace fw-semibold" style="font-size: 0.78rem;">
                            <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Full Resolution
                        </a>
                    </div>
                    <div class="card-body p-4 text-start bg-light">
                        <div class="d-inline-block text-center">
                            <a href="#" data-bs-toggle="modal" data-bs-target="#imageLightboxModal">
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($bug->screenshot_path) }}" class="rounded-3 border shadow-sm cursor-pointer hover-shadow transition-all" style="width: 220px; height: 150px; object-fit: cover;" alt="Bug Screenshot">
                            </a>
                            <small class="text-muted d-block mt-1.5 font-monospace" style="font-size: 0.75rem;"><i class="fa-solid fa-magnifying-glass-plus me-1 text-primary"></i> Click thumbnail to enlarge</small>
                        </div>
                    </div>
                </div>

                <!-- Lightbox Modal for Large Image Preview -->
                <div class="modal fade" id="imageLightboxModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-xl">
                        <div class="modal-content border-0 shadow-lg bg-dark text-white rounded-4 overflow-hidden">
                            <div class="modal-header border-bottom border-secondary py-2.5 px-4">
                                <h6 class="modal-title fw-bold font-monospace fs-7"><i class="fa-solid fa-image me-2 text-danger"></i>Screenshot Preview - {{ $bug->bug_number }}</h6>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body p-3 text-center bg-black">
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($bug->screenshot_path) }}" class="img-fluid rounded-3" style="max-height: 80vh; object-fit: contain;" alt="Bug Screenshot Full">
                            </div>
                            <div class="modal-footer border-top border-secondary py-2 px-4 justify-content-between">
                                <small class="text-muted font-monospace">{{ $bug->title }}</small>
                                <a href="{{ \Illuminate\Support\Facades\Storage::url($bug->screenshot_path) }}" target="_blank" class="btn btn-sm btn-light fw-bold rounded-pill px-3">
                                    <i class="fa-solid fa-download me-1"></i> Open Original Image
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
                    <div class="card-header bg-white py-3 px-4 border-bottom">
                        <h6 class="fw-bold text-dark mb-0"><i class="fa-solid fa-image text-muted me-2"></i>Attached Screenshot / Visual Proof</h6>
                    </div>
                    <div class="card-body p-4 text-center bg-light">
                        <div class="py-3 text-muted">
                            <i class="fa-regular fa-image fs-1 text-secondary opacity-50 mb-2 d-block"></i>
                            <p class="mb-0 small fw-semibold">No screenshot was uploaded when reporting this bug.</p>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Right: Bug Properties Sidebar (4 cols) -->
        <div class="col-lg-4 d-flex flex-column gap-3">
            <div class="card border-0 shadow-sm rounded-4 bg-white">
                <div class="card-header bg-white py-3 px-4 border-bottom">
                    <h6 class="fw-bold text-dark mb-0"><i class="fa-solid fa-circle-info text-info me-2"></i>Issue Properties</h6>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3.5">
                        <small class="text-muted d-block text-uppercase fw-bold mb-1" style="font-size: 0.7rem;">Severity Level</small>
                        @if($bug->severity === 'critical')
                            <span class="badge bg-danger text-white rounded-pill px-3 py-1 fw-bold font-monospace fs-7">CRITICAL / BLOCKING</span>
                        @elseif($bug->severity === 'high')
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-1 fw-bold font-monospace fs-7">HIGH</span>
                        @elseif($bug->severity === 'medium')
                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-3 py-1 fw-bold font-monospace fs-7">MEDIUM</span>
                        @else
                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-3 py-1 fw-bold font-monospace fs-7">LOW</span>
                        @endif
                    </div>

                    <div class="mb-3.5">
                        <small class="text-muted d-block text-uppercase fw-bold mb-1" style="font-size: 0.7rem;">Linked Task</small>
                        @if($bug->task)
                            <div class="p-2.5 rounded-3 bg-light border">
                                <span class="fw-bold text-dark d-block" style="font-size: 0.88rem;">#{{ $bug->task->id }} - {{ $bug->task->title }}</span>
                                <small class="text-muted">Task Status: <strong class="text-uppercase font-monospace">{{ $bug->task->status }}</strong></small>
                            </div>
                        @else
                            <span class="text-muted small fst-italic">Project General Bug (No Task)</span>
                        @endif
                    </div>

                    <div class="mb-3.5">
                        <small class="text-muted d-block text-uppercase fw-bold mb-1" style="font-size: 0.7rem;">Project</small>
                        <span class="fw-bold text-dark fs-7"><i class="fa-solid fa-briefcase text-primary me-1"></i>{{ $bug->project->name }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
