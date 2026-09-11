@extends('layouts.dashboard')

@section('title', 'My Department')

@section('content')

<div class="container-fluid px-0">

    <!-- Header Banner with Manager Name as Small Sub-Text -->
    <div class="card border-0 shadow-sm rounded-4 mb-3 bg-white">
        <div class="card-body p-3 p-md-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <h4 class="fw-bold text-dark m-0">{{ $department->name }}</h4>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1 font-monospace fw-bold" style="font-size: 0.75rem;">
                            Department
                        </span>
                    </div>
                    <p class="text-muted small mb-0">
                        @if($manager)
                            <i class="fa-solid fa-user-tie text-primary me-1"></i> Manager: <span class="fw-semibold text-dark me-2">{{ $manager->full_name }}</span>
                            <span class="text-muted me-2">&bull;</span>
                        @endif
                        {{ $department->description ?: 'No department description specified.' }}
                    </p>
                </div>
                
                <div>
                    <span class="badge bg-light text-secondary border rounded-pill px-3 py-1.5 font-monospace fw-bold" style="font-size: 0.78rem;">
                        <i class="fa-solid fa-users text-primary me-1.5"></i>{{ $coworkers->count() + ($manager ? 1 : 0) }} Members
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Team Members Full Width List -->
    <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
        <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
            <h6 class="fw-bold text-dark mb-0"><i class="fa-solid fa-users me-2 text-primary"></i>Department Team Members</h6>
            <span class="badge bg-light text-secondary border rounded-pill px-2.5 py-1 font-monospace fw-bold" style="font-size: 0.75rem;">
                {{ $coworkers->count() }} Members
            </span>
        </div>
        
        <div class="card-body p-0">
            <div class="list-group list-group-flush">
                @forelse($coworkers as $coworker)
                    <div class="list-group-item d-flex align-items-center justify-content-between py-3 px-4 hover-bg-light border-bottom">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold flex-shrink-0"
                                 style="width: 36px; height: 36px; font-size: 0.9rem; background: linear-gradient(135deg, #3B82F6 0%, #1D4ED8 100%);">
                                {{ strtoupper(substr($coworker->first_name, 0, 1)) }}
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold text-dark lh-1" style="font-size: 0.88rem;">{{ $coworker->full_name }}</h6>
                                <small class="text-muted font-monospace lh-1" style="font-size: 0.75rem;">{{ $coworker->email }}</small>
                            </div>
                        </div>
                        <div>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1 fw-semibold" style="font-size: 0.75rem;">
                                {{ $coworker->display_designation }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-4 text-muted small fst-italic">You are the only member in this department.</div>
                @endforelse
            </div>
        </div>
    </div>

</div>

@endsection
