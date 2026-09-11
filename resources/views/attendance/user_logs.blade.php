@extends('layouts.dashboard')

@section('title', $user->full_name . ' - Time Logs')

@section('content')
<div class="container-fluid px-0">

    <!-- Top Breadcrumb Bar -->
    <div class="mb-3">
        <a href="{{ route('attendance.index') }}" class="text-secondary text-decoration-none small hover-primary fw-medium">
            <i class="fa-solid fa-arrow-left me-1"></i> Time Logs
        </a>
    </div>

    <!-- SECTION 1: EMPLOYEE OVERVIEW -->
    <div class="mb-4">
        <div class="card shadow-sm border-0 rounded-4 bg-white p-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-4">
                <div>
                    <h4 class="fw-bold text-dark mb-1">{{ $user->full_name }}</h4>
                    <div class="text-muted small">
                        <span class="fw-medium text-dark">{{ $user->display_designation }}</span>
                        <span class="mx-1.5">&bull;</span>
                        <span>{{ $user->department?->name ?? 'General' }}</span>
                        <span class="mx-1.5">&bull;</span>
                        <span>{{ $user->email }}</span>
                    </div>
                </div>

                @php
                    $cwDaysWorked = isset($weeklyStats[0]) ? count($weeklyStats[0]['logs']->groupBy(fn($l) => $l->clock_in_at ? $l->clock_in_at->toDateString() : $l->date->toDateString()) ?? []) : 0;
                @endphp
                <div class="d-flex align-items-center gap-3">
                    <div class="border rounded-3 px-3 py-2 bg-light text-center">
                        <div class="text-muted small text-uppercase font-monospace" style="font-size: 0.7rem;">This Week</div>
                        <div class="fw-bold text-dark fs-6">{{ $currentWeekHours }} / 40.0 <span class="text-muted small fw-normal">Hrs</span></div>
                    </div>
                    <div class="border rounded-3 px-3 py-2 bg-light text-center">
                        <div class="text-muted small text-uppercase font-monospace" style="font-size: 0.7rem;">Active Days</div>
                        <div class="fw-bold text-dark fs-6">{{ $cwDaysWorked }} / 5 <span class="text-muted small fw-normal">Days</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SECTION 2: ATTENDANCE TIME LOGS BREAKDOWN -->
    <div>
        <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
            
            <!-- Tab Header -->
            <div class="card-header bg-white py-3 px-4 border-bottom">
                <ul class="nav nav-pills card-header-pills gap-2" id="userLogsTabs" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active fw-bold rounded-pill px-4 py-2" id="week-view-tab" data-bs-toggle="tab" data-bs-target="#week-view" type="button" role="tab">
                            <i class="fa-solid fa-calendar-week me-2"></i>Week Wise Logs ({{ count($weeklyStats) }})
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link fw-bold rounded-pill px-4 py-2" id="month-view-tab" data-bs-toggle="tab" data-bs-target="#month-view" type="button" role="tab">
                            <i class="fa-solid fa-calendar-days me-2"></i>Month Wise Logs ({{ count($monthlyStats) }})
                        </button>
                    </li>
                </ul>
            </div>

            <div class="card-body p-4 bg-light-subtle">
                <div class="tab-content" id="userLogsTabContent">

                    <!-- 1. WEEK WISE LOGS TAB -->
                    <div class="tab-pane fade show active" id="week-view" role="tabpanel">
                        @forelse($weeklyStats as $wStat)
                            <div class="card border shadow-2xs rounded-3 mb-4 overflow-hidden bg-white">
                                <!-- Week Header -->
                                <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
                                    <div>
                                        <h6 class="fw-bold text-dark mb-0 fs-6">{{ $wStat['label'] }}</h6>
                                        <small class="text-muted">{{ $wStat['logs_count'] }} Session{{ $wStat['logs_count'] == 1 ? '' : 's' }} &bull; {{ $wStat['days_worked'] }} / 5 Days Worked</small>
                                    </div>
                                    <div>
                                        <span class="badge {{ $wStat['percent'] >= 100 ? 'bg-success text-white' : 'bg-primary text-white' }} px-3 py-2 rounded-pill font-monospace fw-bold">
                                            Total: {{ $wStat['hours'] }} / 40.0 Hrs
                                        </span>
                                    </div>
                                </div>

                                <!-- Week Sessions Table -->
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light text-uppercase text-muted fw-bold small" style="letter-spacing: 0.5px;">
                                            <tr>
                                                <th class="ps-4 py-3">Date & Day</th>
                                                <th class="py-3">Task Description</th>
                                                <th class="py-3">Project</th>
                                                <th class="py-3 text-center">Clock In</th>
                                                <th class="py-3 text-center">Clock Out</th>
                                                <th class="pe-4 py-3 text-end">Duration</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($wStat['logs'] as $logItem)
                                                <tr>
                                                    <td class="ps-4 py-3">
                                                        <span class="fw-bold text-dark font-monospace me-1.5">{{ $logItem->clock_in_at->format('M d, Y') }}</span>
                                                        <span class="badge bg-light text-muted border font-monospace">{{ $logItem->clock_in_at->format('D') }}</span>
                                                    </td>
                                                    <td class="py-3">
                                                        <span class="fw-semibold text-dark">{{ $logItem->task?->title ?: 'General Work' }}</span>
                                                    </td>
                                                    <td class="py-3">
                                                        @if($logItem->project)
                                                            @php
                                                                $cSeed = md5($logItem->project->name);
                                                                $hue = hexdec(substr($cSeed, 0, 2)) % 360;
                                                            @endphp
                                                            <span class="badge rounded-pill font-monospace border px-3 py-1.5" style="background-color: hsl({{ $hue }}, 70%, 95%); color: hsl({{ $hue }}, 70%, 35%); border-color: hsl({{ $hue }}, 70%, 85%) !important;">
                                                                📁 {{ $logItem->project->name }}
                                                            </span>
                                                        @else
                                                            <span class="text-muted small">-</span>
                                                        @endif
                                                    </td>
                                                    <td class="py-3 text-center font-monospace">
                                                        <span class="badge bg-light text-secondary border px-3 py-1.5 fw-semibold">
                                                            <i class="fa-regular fa-clock me-1 text-primary"></i>{{ $logItem->clock_in_at->format('h:i:s A') }}
                                                        </span>
                                                    </td>
                                                    <td class="py-3 text-center font-monospace">
                                                        <span class="badge bg-light text-dark border px-3 py-1.5 fw-semibold">
                                                            <i class="fa-solid fa-flag-checkered me-1 text-danger"></i>{{ $logItem->clock_out_at ? $logItem->clock_out_at->format('h:i:s A') : 'Active' }}
                                                        </span>
                                                    </td>
                                                    <td class="pe-4 py-3 text-end fw-bold font-monospace">
                                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1.5 rounded-pill font-monospace fw-bold">
                                                            <i class="fa-solid fa-stopwatch me-1"></i>{{ $logItem->clock_in_at->diff($logItem->clock_out_at)->format('%H:%I:%S') }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-5 bg-white rounded-4 border p-4 shadow-2xs">
                                <i class="fa-solid fa-calendar-xmark fs-1 text-secondary opacity-50 mb-3 d-block"></i>
                                <h6 class="fw-bold text-dark mb-1">No Weekly Logs Recorded</h6>
                                <p class="text-muted small mb-0">No time tracking sessions recorded for this employee yet.</p>
                            </div>
                        @endforelse
                    </div>

                    <!-- 2. MONTH WISE LOGS TAB -->
                    <div class="tab-pane fade" id="month-view" role="tabpanel">
                        @forelse($monthlyStats as $mStat)
                            <div class="card border shadow-2xs rounded-3 mb-4 overflow-hidden bg-white">
                                <!-- Month Header -->
                                <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
                                    <div>
                                        <h6 class="fw-bold text-dark mb-0 fs-6">{{ $mStat['month'] }}</h6>
                                        <small class="text-muted">{{ $mStat['days_worked'] }} Active Days &bull; {{ $mStat['logs_count'] }} Total Sessions</small>
                                    </div>
                                    <div>
                                        <span class="badge bg-success text-white px-3 py-2 rounded-pill font-monospace fw-bold">
                                            Total: {{ $mStat['hours'] }} / 160.0 Hrs
                                        </span>
                                    </div>
                                </div>

                                <!-- Month Sessions Table -->
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light text-uppercase text-muted fw-bold small" style="letter-spacing: 0.5px;">
                                            <tr>
                                                <th class="ps-4 py-3">Date & Day</th>
                                                <th class="py-3">Task Description</th>
                                                <th class="py-3">Project</th>
                                                <th class="py-3 text-center">Clock In</th>
                                                <th class="py-3 text-center">Clock Out</th>
                                                <th class="pe-4 py-3 text-end">Duration</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($mStat['logs'] as $logItem)
                                                <tr>
                                                    <td class="ps-4 py-3">
                                                        <span class="fw-bold text-dark font-monospace me-1.5">{{ $logItem->clock_in_at->format('M d, Y') }}</span>
                                                        <span class="badge bg-light text-muted border font-monospace">{{ $logItem->clock_in_at->format('D') }}</span>
                                                    </td>
                                                    <td class="py-3">
                                                        <span class="fw-semibold text-dark">{{ $logItem->task?->title ?: 'General Work' }}</span>
                                                    </td>
                                                    <td class="py-3">
                                                        @if($logItem->project)
                                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill font-monospace px-3 py-1.5">
                                                                📁 {{ $logItem->project->name }}
                                                            </span>
                                                        @else
                                                            <span class="text-muted small">-</span>
                                                        @endif
                                                    </td>
                                                    <td class="py-3 text-center font-monospace">
                                                        <span class="badge bg-light text-secondary border px-3 py-1.5 fw-semibold">
                                                            <i class="fa-regular fa-clock me-1 text-success"></i>{{ $logItem->clock_in_at->format('h:i:s A') }}
                                                        </span>
                                                    </td>
                                                    <td class="py-3 text-center font-monospace">
                                                        <span class="badge bg-light text-dark border px-3 py-1.5 fw-semibold">
                                                            <i class="fa-solid fa-flag-checkered me-1 text-danger"></i>{{ $logItem->clock_out_at ? $logItem->clock_out_at->format('h:i:s A') : 'Active' }}
                                                        </span>
                                                    </td>
                                                    <td class="pe-4 py-3 text-end fw-bold font-monospace">
                                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1.5 rounded-pill font-monospace fw-bold">
                                                            <i class="fa-solid fa-stopwatch me-1"></i>{{ $logItem->clock_out_at ? $logItem->clock_in_at->diff($logItem->clock_out_at)->format('%H:%I:%S') : '-' }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-5 bg-white rounded-4 border p-4 shadow-2xs">
                                <i class="fa-solid fa-calendar-xmark fs-1 text-secondary opacity-50 mb-3 d-block"></i>
                                <h6 class="fw-bold text-dark mb-1">No Monthly Logs Recorded</h6>
                                <p class="text-muted small mb-0">No time tracking sessions recorded for this employee yet.</p>
                            </div>
                        @endforelse
                    </div>

                </div>
            </div>
        </div>
    </div>

</div>
@endsection
