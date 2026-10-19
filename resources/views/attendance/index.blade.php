@extends('layouts.dashboard')

@section('title', 'Time Logs & Tracker')

@section('content')

@php
    $currentUser = (new \App\Services\CurrentUserService())->getUser();
    $canViewTeam = (new \App\Services\CurrentUserService())->hasPermission('attendance.view');
@endphp

@push('styles')
<style>
    .time-tracker-card {
        border: 1px solid var(--bs-border-color, #E2E8F0);
        border-radius: 12px;
        background: var(--bs-card-bg, #FFFFFF);
        box-shadow: 0 2px 10px -2px rgba(15, 23, 42, 0.04);
    }
    .tracker-pill-input {
        background-color: var(--bs-tertiary-bg, #F8FAFC);
        border: 1px solid var(--bs-border-color, #E2E8F0);
        border-radius: 50rem;
        transition: all 0.15s ease-in-out;
        overflow: visible !important;
        position: relative;
    }
    .tracker-pill-input:focus-within {
        background-color: var(--bs-body-bg, #FFFFFF);
        border-color: #3B82F6 !important;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.12) !important;
    }
    .tracker-pill-input .form-control,
    .tracker-pill-input .form-select,
    .tracker-pill-input .input-group-text {
        border: none !important;
        background-color: transparent !important;
        box-shadow: none !important;
        outline: none !important;
        border-radius: 0 !important;
    }
    .tracker-pill-input .form-control:focus,
    .tracker-pill-input .form-select:focus {
        border: none !important;
        background-color: transparent !important;
        box-shadow: none !important;
        outline: none !important;
    }
    .timesheet-week-header {
        background: var(--bs-body-bg, #FFFFFF);
        border: 1px solid var(--bs-border-color, #E2E8F0);
        border-left: 4px solid #3B82F6 !important;
        border-radius: 8px;
        padding: 0.75rem 1.25rem !important;
    }
    .timesheet-table th {
        font-weight: 700;
        letter-spacing: 0.4px;
        color: var(--bs-secondary-color, #64748B);
        border-bottom: 2px solid var(--bs-border-color-subtle, #E2E8F0);
        background-color: var(--bs-tertiary-bg, #F8FAFC);
    }
    .timesheet-table td {
        vertical-align: middle;
        border-bottom: 1px solid var(--bs-border-color-subtle, #F1F5F9);
    }
    .timesheet-table tbody tr:hover {
        background-color: var(--bs-tertiary-bg, #F8FAFC) !important;
    }
    .table-responsive {
        overflow: visible !important;
    }
    .timesheet-table .dropdown-menu {
        z-index: 9999 !important;
    }
    .action-btn-circle {
        width: 30px;
        height: 30px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        border-radius: 50%;
        transition: all 0.15s ease;
    }
    .action-btn-circle:hover {
        transform: scale(1.08);
        background-color: #E2E8F0;
    }
    .live-pulse {
        animation: pulseAnimation 2s infinite;
    }
    @keyframes pulseAnimation {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.4; transform: scale(0.85); }
    }
</style>
@endpush

<div class="container-fluid px-0">

    <!-- Time Tracker Bar (Clean SaaS Aesthetic) -->
    <div class="card time-tracker-card mb-4 border-0">
        <div class="card-body py-3 px-4">
            <div id="tracker-container">
                @if($activeAttendance)
                    <!-- Active State Form -->
                    <form id="active-tracker-form" onsubmit="handleTrackerSubmit(event, 'switch')" class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                        @csrf
                        <div class="d-flex align-items-center flex-grow-1 gap-3 min-w-0" style="min-width: 280px;">
                            <div class="input-group tracker-pill-input px-3 py-1 flex-grow-1">
                                <span class="input-group-text bg-transparent border-0 text-primary ps-0 pe-2">
                                    <i class="fa-solid fa-bolt fs-6"></i>
                                </span>
                                <input type="text" id="task_title_input" name="task_title" class="form-control border-0 shadow-none bg-transparent py-1 fw-semibold text-body" style="font-size: 0.9rem;" 
                                       placeholder="What are you working on?"
                                       value="{{ $activeAttendance->task?->title }}">
                            </div>
                            <div class="input-group tracker-pill-input px-3 py-1 flex-shrink-0 position-relative dropdown" style="width: 200px;">
                                <select id="project_id_select" name="project_id" class="d-none" onchange="handleProjectChange(this)">
                                    <option value="">+ Project</option>
                                    @foreach($projects as $project)
                                        <option value="{{ $project->id }}" {{ $activeAttendance->project_id === $project->id ? 'selected' : '' }}>
                                             {{ $project->name }}
                                        </option>
                                    @endforeach
                                </select>

                                <button type="button" 
                                        class="btn btn-transparent border-0 shadow-none fw-semibold text-body py-1 px-0 text-start text-truncate flex-grow-1 d-flex align-items-center justify-content-between" 
                                        id="custom_project_btn"
                                        data-bs-toggle="dropdown" 
                                        data-bs-display="static" 
                                        aria-expanded="false" 
                                        style="font-size: 0.85rem;">
                                    <span class="text-truncate me-1" id="custom_project_label">
                                        {{ $activeAttendance->project?->name ?? '+ Project' }}
                                    </span>
                                    <i class="fa-solid fa-chevron-down text-muted" style="font-size: 0.65rem;"></i>
                                </button>

                                <ul class="dropdown-menu dropdown-menu-end shadow-lg border rounded-3 p-1 mt-1 font-sans" style="min-width: 220px; z-index: 9999 !important; border-color: var(--bs-border-color) !important;" id="custom_project_menu">
                                    <li class="px-2 py-1 mb-1" onclick="event.stopPropagation();">
                                        <div class="input-group input-group-sm bg-body-tertiary rounded-2 border px-2 py-1">
                                            <span class="input-group-text bg-transparent border-0 text-muted p-0 me-1.5" style="font-size: 0.75rem;">
                                                <i class="fa-solid fa-magnifying-glass"></i>
                                            </span>
                                            <input type="text" class="form-control bg-transparent border-0 shadow-none p-0 small text-body" placeholder="Search project..." onkeyup="filterProjectDropdown(this)" onclick="event.stopPropagation();">
                                        </div>
                                    </li>
                                    <li onclick="event.stopPropagation();"><hr class="dropdown-divider my-1 opacity-10"></li>
                                    <div class="project-items-list" style="max-height: 200px; overflow-y: auto;">
                                        <li>
                                            <button class="dropdown-item project-item-btn py-2 px-3 rounded-2 fw-medium d-flex align-items-center justify-content-between text-body {{ !$activeAttendance?->project_id ? 'active bg-primary text-white' : '' }}" 
                                                    type="button" 
                                                    onclick="selectProjectCustom('', '+ Project', this)">
                                                <span>+ Project</span>
                                                <i class="fa-solid fa-check small check-mark {{ !$activeAttendance?->project_id ? '' : 'd-none' }}"></i>
                                            </button>
                                        </li>
                                        <li><hr class="dropdown-divider my-1 opacity-10"></li>
                                        @foreach($projects as $project)
                                            @php $isSel = ($activeAttendance && $activeAttendance->project_id === $project->id); @endphp
                                            <li>
                                                <button class="dropdown-item project-item-btn py-2 px-3 rounded-2 fw-medium d-flex align-items-center justify-content-between text-body {{ $isSel ? 'active bg-primary text-white' : '' }}" 
                                                        type="button" 
                                                        onclick="selectProjectCustom('{{ $project->id }}', '{{ addslashes($project->name) }}', this)">
                                                    <span class="text-truncate me-2">{{ $project->name }}</span>
                                                    <i class="fa-solid fa-check small check-mark {{ $isSel ? '' : 'd-none' }}"></i>
                                                </button>
                                            </li>
                                        @endforeach
                                    </div>
                                </ul>
                            </div>
                        </div>

                        <div class="d-flex align-items-center gap-3 ms-auto">
                            <div class="d-flex align-items-center gap-2 text-success rounded-pill border border-success-subtle shadow-sm" style="background-color: #ECFDF5; padding: 0.45rem 1.1rem !important;">
                                <span class="spinner-grow spinner-grow-sm text-success live-pulse flex-shrink-0" style="width: 8px; height: 8px;"></span>
                                <div id="logs-running-timer" class="fs-5 fw-bold font-monospace text-success lh-1" data-start="{{ $activeAttendance->clock_in_at->toIso8601String() }}">
                                    00:00:00
                                </div>
                            </div>
                            <button type="button" onclick="handleStopTimer()" class="btn btn-danger btn-sm px-4 py-2 fw-bold text-uppercase rounded-pill shadow-sm d-flex align-items-center gap-2" style="font-size: 0.8rem; letter-spacing: 0.4px;">
                                <i class="fa-solid fa-square" style="font-size: 0.75rem;"></i> Stop
                            </button>
                        </div>
                    </form>
                @elseif($activeLeave)
                    @php
                        $leaveLabel = ucwords(str_replace('_', ' ', $activeLeave->type));
                        $leaveNote = 'Time tracking is disabled during your approved leave.';
                        if ($activeLeave->type === 'first_half') {
                            $leaveNote = 'First half leave is active until 2:00 PM. You can track time in the second half (after 2:00 PM).';
                        } elseif ($activeLeave->type === 'second_half') {
                            $leaveNote = 'Second half leave is active from 1:30 PM onwards. Tracking is blocked for the rest of the day.';
                        }
                    @endphp
                    <!-- On Approved Leave State -->
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 py-1">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle bg-warning-subtle text-warning-emphasis d-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px;">
                                <i class="fa-solid fa-umbrella-beach fs-6 text-warning"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold text-body mb-0.5 fs-6">Approved Leave Active ({{ $leaveLabel }})</h6>
                                <p class="text-secondary small mb-0 font-monospace" style="font-size: 0.78rem;">
                                    Period: {{ $activeLeave->start_date->format('d M Y') }} &ndash; {{ $activeLeave->end_date->format('d M Y') }} &bull; 
                                    <span class="text-danger fw-semibold">{{ $leaveNote }}</span>
                                </p>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-3 py-1.5 fw-semibold" style="font-size: 0.78rem;">
                                <i class="fa-solid fa-ban me-1 text-danger"></i> Tracking Disabled
                            </span>
                        </div>
                    </div>
                @else
                    <!-- Idle State Form -->
                    <form id="idle-tracker-form" onsubmit="handleTrackerSubmit(event, 'start')" class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                        @csrf
                        <div class="d-flex align-items-center flex-grow-1 gap-3 min-w-0" style="min-width: 280px;">
                            <div class="input-group tracker-pill-input px-3 py-1 flex-grow-1">
                                <span class="input-group-text bg-transparent border-0 text-muted ps-0 pe-2">
                                    <i class="fa-solid fa-pen-to-square" style="font-size: 0.85rem;"></i>
                                </span>
                                <input type="text" id="task_title_input" name="task_title" class="form-control border-0 shadow-none bg-transparent py-1 fw-semibold text-body" style="font-size: 0.9rem;" 
                                       placeholder="What are you working on?">
                            </div>
                            <div class="input-group tracker-pill-input px-3 py-1 flex-shrink-0 position-relative dropdown" style="width: 200px;">
                                <select id="project_id_select" name="project_id" class="d-none">
                                    <option value="">+ Project</option>
                                    @foreach($projects as $project)
                                        <option value="{{ $project->id }}">
                                            {{ $project->name }}
                                        </option>
                                    @endforeach
                                </select>

                                <button type="button" 
                                        class="btn btn-transparent border-0 shadow-none fw-semibold text-body py-1 px-0 text-start text-truncate flex-grow-1 d-flex align-items-center justify-content-between" 
                                        id="custom_project_btn"
                                        data-bs-toggle="dropdown" 
                                        data-bs-display="static" 
                                        aria-expanded="false" 
                                        style="font-size: 0.85rem;">
                                    <span class="text-truncate me-1" id="custom_project_label">+ Project</span>
                                    <i class="fa-solid fa-chevron-down text-muted" style="font-size: 0.65rem;"></i>
                                </button>

                                <ul class="dropdown-menu dropdown-menu-end shadow-lg border rounded-3 p-1 mt-1 font-sans" style="min-width: 220px; z-index: 9999 !important; border-color: var(--bs-border-color) !important;" id="custom_project_menu">
                                    <li class="px-2 py-1 mb-1" onclick="event.stopPropagation();">
                                        <div class="input-group input-group-sm bg-body-tertiary rounded-2 border px-2 py-1">
                                            <span class="input-group-text bg-transparent border-0 text-muted p-0 me-1.5" style="font-size: 0.75rem;">
                                                <i class="fa-solid fa-magnifying-glass"></i>
                                            </span>
                                            <input type="text" class="form-control bg-transparent border-0 shadow-none p-0 small text-body" placeholder="Search project..." onkeyup="filterProjectDropdown(this)" onclick="event.stopPropagation();">
                                        </div>
                                    </li>
                                    <li onclick="event.stopPropagation();"><hr class="dropdown-divider my-1 opacity-10"></li>
                                    <div class="project-items-list" style="max-height: 200px; overflow-y: auto;">
                                        <li>
                                            <button class="dropdown-item project-item-btn py-2 px-3 rounded-2 fw-medium d-flex align-items-center justify-content-between text-body active bg-primary text-white" 
                                                    type="button" 
                                                    onclick="selectProjectCustom('', '+ Project', this)">
                                                <span>+ Project</span>
                                                <i class="fa-solid fa-check small check-mark"></i>
                                            </button>
                                        </li>
                                        <li><hr class="dropdown-divider my-1 opacity-10"></li>
                                        @foreach($projects as $project)
                                            <li>
                                                <button class="dropdown-item project-item-btn py-2 px-3 rounded-2 fw-medium d-flex align-items-center justify-content-between text-body" 
                                                        type="button" 
                                                        onclick="selectProjectCustom('{{ $project->id }}', '{{ addslashes($project->name) }}', this)">
                                                    <span class="text-truncate me-2">{{ $project->name }}</span>
                                                    <i class="fa-solid fa-check small check-mark d-none"></i>
                                                </button>
                                            </li>
                                        @endforeach
                                    </div>
                                </ul>
                            </div>
                        </div>

                        <div class="d-flex align-items-center gap-3 ms-auto">
                            <div class="fs-5 fw-bold text-secondary opacity-75 font-monospace pe-2 lh-1" style="letter-spacing: 0.3px;">
                                00:00:00
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm px-4 py-2 fw-bold text-uppercase rounded-pill shadow-sm d-flex align-items-center gap-2" style="font-size: 0.8rem; letter-spacing: 0.4px;">
                                <i class="fa-solid fa-play" style="font-size: 0.75rem;"></i> Start
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <!-- Logs Container -->
    <div class="card border-0 shadow-sm bg-body rounded-4 mb-3.5 overflow-hidden">
        <div class="card-header bg-body py-2.5 px-4 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2.5">
            @php
                $isTeamFiltered = request()->has('employee_id') || request()->has('log_date');
                $wSecs = $myPersonalStats['weekly_seconds'] ?? 0;
                $wHrs = floor($wSecs / 3600);
                $wMins = floor(($wSecs % 3600) / 60);

                $tSecs = $myPersonalStats['today_seconds'] ?? 0;
                $tHrs = floor($tSecs / 3600);
                $tMins = floor(($tSecs % 3600) / 60);
            @endphp
            
            <ul class="nav nav-pills card-header-pills gap-2" id="timeLogsTabs" role="tablist">
                <li class="nav-item">
                    <button class="nav-link {{ !$isTeamFiltered ? 'active' : '' }} fw-semibold d-flex align-items-center gap-2 rounded-pill px-3.5 py-1.5 small" id="my-logs-tab" data-bs-toggle="tab" data-bs-target="#my-logs" type="button" role="tab">
                        <i class="fa-solid fa-user-clock"></i> My Time Logs
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link {{ request('tab') === 'screenshots' ? 'active' : '' }} fw-semibold d-flex align-items-center gap-2 rounded-pill px-3.5 py-1.5 small" id="screenshots-tab" data-bs-toggle="tab" data-bs-target="#screenshots-pane" type="button" role="tab">
                        <i class="fa-solid fa-camera text-primary"></i> Screenshots & Activity
                        @php $totalWeekScreenshots = array_sum($weekScreenshotsCount ?? []); @endphp
                        @if($totalWeekScreenshots > 0)
                            <span class="badge bg-primary text-white rounded-pill font-monospace" style="font-size: 0.7rem; padding: 2px 7px;">{{ $totalWeekScreenshots }}</span>
                        @endif
                    </button>
                </li>
                @if($canViewTeam)
                    <li class="nav-item">
                        <button class="nav-link {{ $isTeamFiltered && request('tab') !== 'screenshots' ? 'active' : '' }} fw-semibold d-flex align-items-center gap-2 rounded-pill px-3.5 py-1.5 small" id="team-logs-tab" data-bs-toggle="tab" data-bs-target="#team-logs" type="button" role="tab">
                            <i class="fa-solid fa-users-rectangle"></i> Team Logs & Audits
                        </button>
                    </li>
                @endif
            </ul>

            <div class="d-flex align-items-center gap-2.5">
                @php
                    $wSecsRem = $wSecs % 60;
                @endphp


                @if($canViewTeam)
                    <a href="{{ route('attendance.export-csv', ['employee_id' => request('employee_id'), 'log_date' => request('log_date'), 'start_date' => request('start_date'), 'end_date' => request('end_date')]) }}" 
                       class="btn btn-sm btn-success rounded-pill px-3 py-1.5 fw-bold shadow-2xs" style="font-size: 0.8rem;">
                        <i class="fa-solid fa-file-excel me-1"></i> Export CSV
                    </a>
                @endif
            </div>
        </div>
        
        <div class="card-body p-4 bg-body-tertiary">
            <div class="tab-content" id="timeLogsTabContent">
                
                <!-- My Grouped Logs Tab -->
                <div class="tab-pane fade {{ !$isTeamFiltered && request('tab') !== 'screenshots' ? 'show active' : '' }}" id="my-logs" role="tabpanel">
                    
                    <div id="logs-groups-wrapper">
                        @forelse($myWeeklyGroupedLogs as $weekGroup)
                            <!-- Single Main Card Per Week -->
                            <div class="card border shadow-sm rounded-4 mb-4 bg-body overflow-visible">
                                <!-- Week Group Header (Card Top) -->
                                <div class="card-header bg-body py-3 px-4 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <i class="fa-solid fa-calendar-week text-primary fs-5 me-1"></i>
                                        <h6 class="fw-bold text-body mb-0 fs-6 me-2">{{ $weekGroup['title'] }}</h6>
                                        @if($weekGroup['is_this_week'])
                                            <span class="badge bg-primary text-white rounded-pill px-3 py-1 fw-bold" style="font-size: 0.65rem;">CURRENT WEEK</span>
                                        @endif
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="text-secondary small font-monospace">WEEKLY TOTAL:</span>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill font-monospace px-3 py-1 fw-bold fs-6">
                                            {{ $weekGroup['total_formatted'] }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Card Body: Spacious Container for All Days -->
                                <div class="card-body p-4 overflow-visible">
                                    @foreach($weekGroup['days'] as $dayInfo)
                                        @php
                                            $dateKey = $dayInfo['date_key'];
                                            $logs = $dayInfo['logs'];
                                            $cDate = $dateKey !== 'other' ? \Carbon\Carbon::parse($dateKey) : now();
                                            $isToday = $cDate->isToday();
                                            $isYesterday = $cDate->isYesterday();
                                            $seconds = $dayInfo['total_seconds'] ?? 0;
                                            $hrs = floor($seconds / 3600);
                                            $mins = floor(($seconds % 3600) / 60);
                                            $secs = $seconds % 60;
                                        @endphp
                                        
                                        <!-- Day Section Sub-Card -->
                                        <div class="card border rounded-3 shadow-2xs mb-4 bg-white overflow-visible">
                                            <!-- Day Header (Sub-Card Header) -->
                                            <div class="card-header bg-light-subtle py-2.5 px-3 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
                                                <div class="d-flex align-items-center gap-2">
                                                    <i class="fa-regular fa-calendar-days text-primary fs-6 me-0.5"></i>
                                                    <h6 class="fw-bold text-body mb-0 fs-6">{{ $cDate->format('D, d M') }}</h6>
                                                    @if($isToday)
                                                        <span class="badge bg-primary text-white rounded-pill px-2 py-0.5 fw-bold" style="font-size: 0.62rem;">TODAY</span>
                                                    @elseif($isYesterday)
                                                        <span class="badge bg-secondary text-white rounded-pill px-2 py-0.5 fw-bold" style="font-size: 0.62rem;">YESTERDAY</span>
                                                    @endif
                                                </div>
                                                <div class="d-flex align-items-center gap-2 font-monospace text-secondary small" style="font-size: 0.82rem;">
                                                    <span>DAY TOTAL:</span>
                                                    <span class="badge bg-body-tertiary text-body border font-monospace px-2.5 py-1 fw-bold" style="font-size: 0.82rem;">
                                                        {{ sprintf('%02d:%02d:%02d', $hrs, $mins, $secs) }}
                                                    </span>
                                                </div>
                                            </div>

                                            <!-- Day Timesheet Table Container -->
                                            <div class="table-responsive overflow-visible">
                                                <table class="table table-hover align-middle mb-0 timesheet-table">
                                                    <thead class="table-light text-uppercase text-muted fw-bold" style="font-size: 0.7rem; letter-spacing: 0.4px;">
                                                        <tr>
                                                            <th class="ps-3 py-3" style="min-width: 250px;">Task Description</th>
                                                            <th class="py-3" style="min-width: 150px;">Project</th>
                                                            <th class="py-3 text-center" style="min-width: 140px;">Time Range</th>
                                                            <th class="py-3 text-center" style="min-width: 110px;">Duration</th>
                                                            <th class="pe-3 py-3 text-end" style="min-width: 110px;">Actions</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($logs as $log)
                                                            @php
                                                                $startTime = $log->clock_in_at;
                                                                $endTime = $log->clock_out_at;
                                                                if ($endTime && $startTime > $endTime) {
                                                                    $temp = $startTime;
                                                                    $startTime = $endTime;
                                                                    $endTime = $temp;
                                                                }
                                                                $durSeconds = ($startTime && $endTime) ? $startTime->diffInSeconds($endTime) : 0;
                                                                $durHrs = floor($durSeconds / 3600);
                                                                $durMins = floor(($durSeconds % 3600) / 60);
                                                                $durSecs = $durSeconds % 60;
                                                                $durFormatted = sprintf('%02d:%02d:%02d', $durHrs, $durMins, $durSecs);

                                                                $projectName = $log->project?->name ?? 'Miscellaneous';
                                                                $colorSeed = md5($projectName);
                                                                $hue = hexdec(substr($colorSeed, 0, 2)) % 360;
                                                                $dotColor = "hsl({$hue}, 75%, 40%)";
                                                                $bgColor = "hsl({$hue}, 85%, 96%)";
                                                                $borderColor = "hsl({$hue}, 60%, 88%)";
                                                            @endphp
                                                            <tr>
                                                                <!-- Task Description -->
                                                                <td class="ps-3 py-3">
                                                                    <div class="fw-semibold text-body text-truncate" style="font-size: 0.88rem; max-width: 380px;" title="{{ $log->task->title ?? 'No description' }}">
                                                                        {{ ($log->task && trim($log->task->title) !== '') ? $log->task->title : 'No description' }}
                                                                    </div>
                                                                </td>

                                                                <!-- Project -->
                                                                <td class="py-3">
                                                                    <span class="badge rounded-pill px-3 py-1 d-inline-flex align-items-center gap-2 border font-sans text-truncate" 
                                                                          style="background-color: {{ $bgColor }}; color: {{ $dotColor }}; border-color: {{ $borderColor }} !important; font-size: 0.74rem; font-weight: 600; max-width: 180px;">
                                                                        <span class="rounded-circle d-inline-block flex-shrink-0" style="width: 6px; height: 6px; background-color: {{ $dotColor }};"></span>
                                                                        <span class="text-truncate">{{ $projectName }}</span>
                                                                    </span>
                                                                </td>

                                                                <!-- Time Range -->
                                                                <td class="py-3 text-center">
                                                                    <span class="badge bg-body-tertiary text-secondary border px-3 py-1 font-monospace fw-normal" style="font-size: 0.76rem;">
                                                                        <i class="fa-regular fa-clock me-1 text-muted"></i>
                                                                        {{ $startTime ? $startTime->format('H:i') : '--:--' }} &ndash; {{ $endTime ? $endTime->format('H:i') : '--:--' }}
                                                                    </span>
                                                                </td>

                                                                <!-- Duration -->
                                                                <td class="py-3 text-center font-monospace fw-bold text-body" style="font-size: 0.88rem;">
                                                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1 rounded-pill font-monospace fw-bold" style="font-size: 0.8rem;">
                                                                        {{ $durFormatted }}
                                                                    </span>
                                                                </td>

                                                                <!-- Actions -->
                                                                <td class="pe-3 py-3 text-end">
                                                                    <div class="d-inline-flex align-items-center gap-2 justify-content-end">
                                                                        @if($log->clock_in_latitude && $log->clock_in_longitude)
                                                                            <button type="button" 
                                                                                    onclick="openLocationMap({{ $log->clock_in_latitude }}, {{ $log->clock_in_longitude }}, '{{ addslashes($currentUser->full_name ?? 'My Log') }}', '{{ $log->clock_in_at ? $log->clock_in_at->format('d M Y, h:i A') : '' }}', '{{ $log->clock_in_ip ?: 'N/A' }}')" 
                                                                                    class="btn btn-sm btn-light border rounded-pill px-2 py-1 text-danger d-inline-flex align-items-center gap-1 shadow-sm" 
                                                                                    style="font-size: 0.68rem;" 
                                                                                    title="View GPS Clock-in Location">
                                                                                <i class="fa-solid fa-location-dot"></i>
                                                                            </button>
                                                                        @endif

                                                                        @if($weekGroup['is_this_week'])
                                                                            <button type="button" 
                                                                                    onclick="handleQuickRestart('{{ $log->project_id }}', '{{ addslashes($log->task?->title ?? '') }}', '{{ $log->task_id }}')" 
                                                                                    class="btn btn-light border rounded-circle text-primary action-btn-circle shadow-sm" 
                                                                                    title="Continue this timer"
                                                                                    style="width: 28px; height: 28px;">
                                                                                <i class="fa-solid fa-play" style="font-size: 0.68rem;"></i>
                                                                            </button>
                                                                        @endif

                                                                        <div class="dropdown position-relative">
                                                                            <button class="btn btn-light border rounded-circle text-muted action-btn-circle shadow-sm" 
                                                                                    type="button" 
                                                                                    data-bs-toggle="dropdown"
                                                                                    data-bs-display="static" 
                                                                                    aria-expanded="false"
                                                                                    title="More actions"
                                                                                    style="width: 28px; height: 28px;">
                                                                                <i class="fa-solid fa-ellipsis-vertical" style="font-size: 0.75rem;"></i>
                                                                            </button>
                                                                            <ul class="dropdown-menu dropdown-menu-end shadow-lg border rounded-3 py-1 px-1 mt-1" style="min-width: 160px; font-size: 0.82rem; z-index: 9999 !important; border-color: var(--bs-border-color) !important;">
                                                                                @if($log->clock_in_latitude && $log->clock_in_longitude)
                                                                                    <li>
                                                                                        <button class="dropdown-item py-2 px-3 rounded-2 fw-medium d-flex align-items-center gap-2 text-danger" type="button" 
                                                                                                onclick="openLocationMap({{ $log->clock_in_latitude }}, {{ $log->clock_in_longitude }}, '{{ addslashes($currentUser->full_name ?? 'My Log') }}', '{{ $log->clock_in_at ? $log->clock_in_at->format('d M Y, h:i A') : '' }}', '{{ $log->clock_in_ip ?: 'N/A' }}')">
                                                                                            <i class="fa-solid fa-location-dot text-danger" style="font-size: 0.8rem;"></i> View GPS Location
                                                                                        </button>
                                                                                    </li>
                                                                                    <li><hr class="dropdown-divider my-1 opacity-10"></li>
                                                                                @endif
                                                                                @if($weekGroup['is_this_week'])
                                                                                    <li>
                                                                                        <button class="dropdown-item py-2 px-3 rounded-2 fw-medium d-flex align-items-center gap-2" type="button" onclick="openEditLogModal('{{ $log->id }}', '{{ addslashes($log->task?->title ?? '') }}', '{{ $log->project_id }}')">
                                                                                            <i class="fa-solid fa-pen-to-square text-primary" style="font-size: 0.8rem;"></i> Edit entry
                                                                                        </button>
                                                                                    </li>
                                                                                    <li><hr class="dropdown-divider my-1 opacity-10"></li>
                                                                                @endif
                                                                                <li>
                                                                                    <form action="{{ route('attendance.destroy', $log->id) }}" method="POST" onsubmit="return confirm('Delete this time entry?');" class="m-0">
                                                                                        @csrf
                                                                                        @method('DELETE')
                                                                                        <button type="submit" class="dropdown-item py-2 px-3 rounded-2 text-danger fw-medium d-flex align-items-center gap-2">
                                                                                            <i class="fa-solid fa-trash-can text-danger" style="font-size: 0.8rem;"></i> Delete entry
                                                                                        </button>
                                                                                    </form>
                                                                                </li>
                                                                            </ul>
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-5 text-muted bg-body rounded-4 border shadow-sm p-4">
                                <i class="fa-solid fa-stopwatch fs-1 opacity-40 mb-3 d-block text-primary"></i>
                                <h6 class="fw-bold text-body mb-1">No time entries recorded yet</h6>
                                <p class="text-secondary small mb-0">Use the live timer bar at the top to start tracking your activities.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Screenshots & Activity Tab -->
                <div class="tab-pane fade {{ request('tab') === 'screenshots' ? 'show active' : '' }}" id="screenshots-pane" role="tabpanel">
                    
                    <!-- Top Filter & Controls Card -->
                    <div class="card border shadow-2xs bg-white rounded-3 mb-2.5">
                        <div class="card-body py-2 px-3">
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2.5">
                                <div class="d-flex align-items-center gap-2.5">
                                    <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center flex-shrink-0" style="width: 34px; height: 34px;">
                                        <i class="fa-solid fa-camera fs-6"></i>
                                    </div>
                                    <div>
                                        <div class="d-flex align-items-center gap-2">
                                            <h6 class="fw-bold text-dark mb-0 fs-6">Current Week Timeline & Screenshots</h6>
                                            <span class="badge bg-light text-muted border rounded-pill px-2 py-0.5 font-monospace" style="font-size: 0.68rem;">
                                                <i class="fa-solid fa-arrows-rotate me-1 text-primary"></i> 2-Min Intervals
                                            </span>
                                        </div>
                                        <small class="text-muted font-monospace" style="font-size: 0.72rem;">
                                            Week: {{ $currentWeekDays[0]['formatted'] }} &ndash; {{ $currentWeekDays[6]['formatted'] }} {{ now()->format('Y') }} &bull; 
                                            <span class="text-secondary"><i class="fa-regular fa-clock-rotate-left me-0.5"></i> Auto-retains last 30 days</span>
                                        </small>
                                    </div>
                                </div>

                                <div class="d-flex align-items-center gap-2 ms-auto flex-wrap">
                                    @if($canViewTeam)
                                        <div class="d-flex align-items-center gap-1.5 bg-light px-2.5 py-1 rounded-pill border">
                                            <label for="screenshot_user_select" class="small fw-semibold text-muted mb-0 text-nowrap" style="font-size: 0.78rem;">
                                                <i class="fa-solid fa-user-gear text-primary me-1"></i> Member:
                                            </label>
                                            <select id="screenshot_user_select" class="form-select form-select-sm border-0 bg-transparent shadow-none fw-semibold text-dark py-0" style="font-size: 0.8rem; min-width: 170px;" onchange="handleScreenshotUserChange(this.value)">
                                                @foreach($organizationUsers as $emp)
                                                    <option value="{{ $emp->id }}" {{ $selectedScreenshotUser->id == $emp->id ? 'selected' : '' }}>
                                                        {{ $emp->full_name }} ({{ $emp->display_designation }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endif

                                    <div class="d-flex align-items-center gap-1.5">
                                        <a href="{{ route('attendance.download-tracker') }}" class="btn btn-sm btn-primary rounded-pill px-3 py-1 fw-bold d-flex align-items-center gap-1 shadow-2xs" style="font-size: 0.78rem;" title="Download 1-Click Desktop Tracker">
                                            <i class="fa-solid fa-cloud-arrow-down"></i>
                                            <span>Download Tracker</span>
                                        </a>

                                        <button type="button" class="btn btn-sm btn-light border rounded-pill px-2.5 py-1 text-dark fw-semibold d-flex align-items-center gap-1" style="font-size: 0.78rem;" data-bs-toggle="modal" data-bs-target="#desktopTrackerHelpModal" title="Setup & Installation Guide">
                                            <i class="fa-regular fa-circle-question text-primary"></i>
                                            <span>Guide</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Day Selector Bar (Mon - Sun of Current Week) -->
                    <div class="card border shadow-2xs bg-white rounded-3 mb-2.5">
                        <div class="card-header bg-white py-1.5 px-3 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div class="fw-semibold text-dark small text-uppercase font-monospace" style="letter-spacing: 0.3px; font-size: 0.75rem;">
                                <i class="fa-regular fa-calendar-days text-primary me-1"></i> Select Week Day
                            </div>
                            <div class="text-muted small" style="font-size: 0.75rem;">
                                Showing data for: <strong class="text-dark" id="current-selected-user-name">{{ $selectedScreenshotUser->full_name }}</strong>
                            </div>
                        </div>
                        <div class="card-body p-2">
                            <div class="d-flex gap-1.5 overflow-x-auto pb-0.5" id="week-days-selector-bar">
                                @foreach($currentWeekDays as $day)
                                    @php
                                        $isSelected = ($selectedScreenshotDate === $day['date']);
                                        $count = $weekScreenshotsCount[$day['date']] ?? 0;
                                    @endphp
                                    <button type="button" 
                                            class="btn flex-fill text-center py-1.5 px-2 rounded-2 border transition-all day-selector-btn {{ $isSelected ? 'btn-primary text-white shadow-xs border-primary active-day-btn' : 'btn-light text-dark bg-light hover-bg-light-subtle' }}"
                                            data-date="{{ $day['date'] }}"
                                            data-day-name="{{ $day['day_name'] }}"
                                            data-formatted="{{ $day['formatted'] }}"
                                            onclick="handleScreenshotDayClick('{{ $day['date'] }}', this)">
                                        <div class="d-flex align-items-center justify-content-between gap-1 mb-0.5">
                                            <span class="fw-bold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.3px;">{{ $day['short_day'] }}</span>
                                            @if($day['is_today'])
                                                <span class="badge {{ $isSelected ? 'bg-white text-primary' : 'bg-primary text-white' }} rounded-pill" style="font-size: 0.6rem; padding: 1px 4px;">Today</span>
                                            @endif
                                        </div>
                                        <div class="fw-bold mb-0.5 font-monospace {{ $isSelected ? 'text-white' : 'text-dark' }}" style="font-size: 0.85rem;">{{ $day['formatted'] }}</div>
                                        <div>
                                            <span class="badge {{ $isSelected ? 'bg-white bg-opacity-25 text-white' : ($count > 0 ? 'bg-primary-subtle text-primary border border-primary-subtle' : 'bg-secondary-subtle text-secondary') }} rounded-pill font-monospace day-count-badge" style="font-size: 0.68rem; padding: 2px 6px;">
                                                {{ $count }} shot{{ $count == 1 ? '' : 's' }}
                                            </span>
                                        </div>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Active Day Screenshot Gallery Grid -->
                    <div class="card border shadow-2xs bg-white rounded-3 mb-3">
                        <div class="card-header bg-white py-1.5 px-3 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-primary text-white rounded-pill px-2.5 py-0.5 font-monospace fw-bold" style="font-size: 0.78rem;" id="gallery-active-day-badge">
                                    {{ \Carbon\Carbon::parse($selectedScreenshotDate)->format('l, d M Y') }}
                                </span>
                                <span class="text-muted small" style="font-size: 0.75rem;" id="gallery-shots-count-label">
                                    {{ count($initialScreenshots) }} screenshot{{ count($initialScreenshots) == 1 ? '' : 's' }} recorded
                                </span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <button type="button" onclick="refreshCurrentDayScreenshots()" class="btn btn-sm btn-light border rounded-pill px-2.5 py-0.5 text-secondary fw-semibold d-flex align-items-center gap-1 shadow-2xs hover-scale" style="font-size: 0.75rem;" title="Refresh Screenshots">
                                    <i class="fa-solid fa-rotate text-primary"></i> <span>Refresh</span>
                                </button>
                            </div>
                        </div>

                        <div class="card-body p-3">
                            <!-- Screenshots Grid -->
                            <div id="screenshots-grid-container" class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-5 row-cols-xl-6 g-2.5">
                                @forelse($initialScreenshots as $idx => $shot)
                                    <div class="col screenshot-item-col" id="shot-card-{{ $shot->id }}">
                                        <div class="card h-100 border rounded-2 overflow-hidden shadow-2xs bg-white hover-shadow transition-all position-relative group">
                                            <!-- Thumbnail with zoom overlay -->
                                            <div class="position-relative overflow-hidden bg-dark ratio ratio-16x9" 
                                                 onclick="openScreenshotLightbox({{ $idx }})" 
                                                 style="cursor: pointer;">
                                                <img src="{{ $shot->url }}" 
                                                     alt="Screenshot at {{ $shot->captured_at ? $shot->captured_at->format('h:i A') : '' }}" 
                                                     class="card-img-top object-fit-cover transition-all screenshot-thumb" 
                                                     loading="lazy"
                                                     style="transition: transform 0.25s ease;">
                                                <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center bg-dark bg-opacity-40 opacity-0 hover-opacity-100 transition-all text-white">
                                                    <div class="rounded-circle bg-white bg-opacity-25 p-1.5 d-flex align-items-center justify-content-center shadow-sm">
                                                        <i class="fa-solid fa-magnifying-glass-plus fs-6"></i>
                                                    </div>
                                                </div>
                                                <div class="position-absolute top-1 start-1">
                                                    <span class="badge bg-dark bg-opacity-80 text-white font-monospace rounded-pill px-2 py-0.5" style="font-size: 0.65rem; backdrop-filter: blur(4px);">
                                                        <i class="fa-regular fa-clock me-0.5 text-warning"></i>{{ $shot->captured_at ? $shot->captured_at->format('h:i A') : '--:--' }}
                                                    </span>
                                                </div>
                                            </div>

                                            <!-- Card Body -->
                                            <div class="card-body p-2 d-flex flex-column justify-content-between">
                                                <div class="mb-1.5">
                                                    <div class="d-flex align-items-center justify-content-between gap-1 mb-0.5">
                                                        <span class="badge bg-light text-dark border rounded-pill px-1.5 py-0.5 text-truncate" style="max-width: 110px; font-size: 0.68rem;">
                                                            📁 {{ $shot->project?->name ?? 'Misc' }}
                                                        </span>
                                                        @if($shot->user_id == $currentUser->id || $canViewTeam)
                                                            <button type="button" 
                                                                    onclick="deleteScreenshot({{ $shot->id }}, event)" 
                                                                    class="btn btn-link text-muted hover-danger p-0 border-0 text-decoration-none" 
                                                                    style="font-size: 0.7rem;" 
                                                                    title="Delete screenshot">
                                                                <i class="fa-regular fa-trash-can"></i>
                                                            </button>
                                                        @endif
                                                    </div>
                                                    <div class="fw-semibold text-dark text-truncate small" style="font-size: 0.78rem;" title="{{ $shot->task?->title ?? 'Active Work' }}">
                                                        {{ $shot->task?->title ?: 'Active Work' }}
                                                    </div>
                                                </div>
                                                <div class="text-muted font-monospace" style="font-size: 0.66rem;">
                                                    {{ $shot->captured_at ? $shot->captured_at->format('d M, h:i:s A') : '' }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-12 py-5 text-center text-muted" id="gallery-empty-state">
                                        <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center p-4 mb-3 shadow-2xs border">
                                            <i class="fa-solid fa-camera-rotate fs-1 text-primary opacity-50"></i>
                                        </div>
                                        <h6 class="fw-bold text-dark mb-1">No Screenshots Recorded for This Day</h6>
                                        <p class="text-secondary small mb-0 max-w-md mx-auto" style="max-width: 420px;">
                                            Screenshots are captured automatically every 2 minutes while the time tracker is active. Start the timer to begin tracking screen activity.
                                        </p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Team Logs Tab -->
                @if($canViewTeam)
                    <div class="tab-pane fade p-3 {{ request()->has('employee_id') || request()->has('log_date') ? 'show active' : '' }}" id="team-logs" role="tabpanel">
                        <!-- Admin / HR Filter Bar -->
                        <form method="GET" action="{{ route('attendance.index') }}" class="row g-2 mb-3 bg-white p-2.5 rounded-3 shadow-2xs border align-items-center">
                            <div class="col-md-5">
                                <label class="form-label mb-1 fw-semibold text-dark small" style="font-size: 0.78rem;">
                                    <i class="fa-solid fa-user-gear me-1 text-primary"></i> Filter by Team Member
                                </label>
                                <select name="employee_id" class="form-select form-select-sm border-gray rounded-2" style="font-size: 0.82rem;" onchange="this.form.submit()">
                                    <option value="">All Team Members (Whole Organization)</option>
                                    @foreach($organizationUsers as $emp)
                                        <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>
                                            {{ $emp->full_name }} ({{ $emp->display_designation }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label mb-1 fw-semibold text-dark small" style="font-size: 0.78rem;">
                                    <i class="fa-solid fa-calendar me-1 text-primary"></i> Filter by Date
                                </label>
                                <input type="date" name="log_date" class="form-control form-control-sm border-gray rounded-2" style="font-size: 0.82rem;" value="{{ request('log_date') }}" onchange="this.form.submit()">
                            </div>
                            <div class="col-md-3 d-flex align-items-end gap-1.5 pt-md-3">
                                <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3 py-1 fw-bold shadow-2xs" style="font-size: 0.78rem;">
                                    <i class="fa-solid fa-filter me-1"></i> Filter
                                </button>
                                <a href="{{ route('attendance.export-csv', ['employee_id' => request('employee_id'), 'log_date' => request('log_date'), 'start_date' => request('start_date'), 'end_date' => request('end_date')]) }}" 
                                   class="btn btn-sm btn-success rounded-pill px-2.5 py-1 fw-bold shadow-2xs" style="font-size: 0.78rem;">
                                    <i class="fa-solid fa-file-csv me-1"></i> CSV
                                </a>
                                @if(request()->filled('employee_id') || request()->filled('log_date'))
                                    <a href="{{ route('attendance.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-2.5 py-1 fw-semibold" style="font-size: 0.78rem;">
                                        Reset
                                    </a>
                                @endif
                            </div>
                        </form>

                        <!-- Team Members List Table -->
                        <div class="card border-0 shadow-2xs rounded-3 overflow-hidden border">
                            <div class="card-header bg-white py-2 px-3.5 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
                                <div>
                                    <h6 class="fw-bold text-dark mb-0 fs-6">
                                        <i class="fa-solid fa-users text-primary me-1.5"></i> Team Members & Weekly Time Summary
                                    </h6>
                                    <small class="text-muted font-monospace" style="font-size: 0.72rem;">Standard 5-Day Work Week (Mon - Fri &bull; 40.0 Hours Target)</small>
                                </div>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill font-monospace px-2.5 py-1 fw-bold" style="font-size: 0.72rem;">
                                    <i class="fa-solid fa-briefcase me-1 text-primary"></i> 5-Day (40.0 Hrs)
                                </span>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0" style="font-size: 0.84rem;">
                                    <thead class="table-light text-uppercase text-muted fw-bold" style="font-size: 0.7rem; letter-spacing: 0.3px;">
                                        <tr>
                                            <th class="ps-3.5 py-2">Team Member</th>
                                            <th class="py-2">Role / Department</th>
                                            <th class="py-2 text-center">This Week Worked</th>
                                            <th class="py-2 text-center">Active Days</th>
                                            <th class="py-2" style="width: 22%;">40-Hour Quota Progress</th>
                                            <th class="pe-3.5 py-2 text-end">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($teamWeeklyStats as $stat)
                                            @php
                                                $emp = $stat['user'];
                                            @endphp
                                            <tr>
                                                <td class="ps-3.5 py-2.5">
                                                    <div class="d-flex align-items-center gap-2.5">
                                                        @if($emp->avatar)
                                                            <img src="{{ asset('storage/' . $emp->avatar) }}" 
                                                                 alt="{{ $emp->full_name }}" 
                                                                 class="rounded-circle img-thumbnail shadow-2xs" 
                                                                 style="width: 34px; height: 34px; object-fit: cover;">
                                                        @else
                                                            <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold shadow-2xs flex-shrink-0"
                                                                 style="width: 34px; height: 34px; font-size: 0.85rem; background: linear-gradient(135deg, #4F46E5 0%, #7C3AED 100%);">
                                                                {{ strtoupper(substr($emp->first_name, 0, 1)) }}
                                                            </div>
                                                        @endif
                                                        <div>
                                                            <div class="fw-semibold text-dark mb-0" style="font-size: 0.85rem;">
                                                                <a href="{{ route('users.show', $emp->id) }}" class="text-dark text-decoration-none hover-primary">
                                                                    {{ $emp->full_name }}
                                                                </a>
                                                            </div>
                                                            <div class="text-muted font-monospace" style="font-size: 0.72rem;">{{ $emp->email }}</div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="py-2.5">
                                                    <div class="fw-semibold text-dark small mb-0" style="font-size: 0.82rem;">{{ $emp->display_designation }}</div>
                                                    <div class="text-muted" style="font-size: 0.72rem;">{{ $emp->department?->name ?? 'General' }}</div>
                                                </td>
                                                <td class="py-2.5 text-center">
                                                    <span class="badge {{ $stat['percent'] >= 100 ? 'bg-success-subtle text-success border-success-subtle' : ($stat['percent'] > 0 ? 'bg-primary-subtle text-primary border-primary-subtle' : 'bg-light text-muted border') }} border px-2.5 py-1 rounded-pill font-monospace fw-bold shadow-2xs" style="font-size: 0.75rem;">
                                                        <i class="fa-solid fa-clock me-1"></i>{{ $stat['hours'] >= 1.0 ? $stat['hours'] . ' Hrs' : ($stat['formatted_total'] ?? '00:00:00') }} / 40.0 Hrs
                                                    </span>
                                                </td>
                                                <td class="py-2.5 text-center font-monospace">
                                                    <span class="badge bg-light text-dark border px-2 py-1 rounded-pill fw-semibold" style="font-size: 0.72rem;">
                                                        <i class="fa-regular fa-calendar-check me-1 text-success"></i>{{ $stat['days_worked'] }} / 5 Days
                                                    </span>
                                                </td>
                                                <td class="py-2.5">
                                                    <div class="d-flex align-items-center justify-content-between mb-1" style="font-size: 0.72rem;">
                                                        <span class="fw-bold font-monospace text-muted">{{ $stat['percent'] }}%</span>
                                                        <span class="text-muted font-monospace">{{ $stat['hours'] }} hrs</span>
                                                    </div>
                                                    <div class="progress rounded-pill" style="height: 6px; background-color: #E2E8F0;">
                                                        <div class="progress-bar rounded-pill {{ $stat['percent'] >= 100 ? 'bg-success' : 'bg-primary' }}" 
                                                             style="width: {{ $stat['percent'] }}%;"></div>
                                                    </div>
                                                </td>
                                                <td class="pe-3.5 py-2.5 text-end">
                                                    <a href="{{ route('attendance.user-logs', $emp->id) }}" class="btn btn-sm btn-primary rounded-pill px-3 py-1 fw-bold shadow-2xs d-inline-flex align-items-center gap-1 hover-scale" style="font-size: 0.75rem;">
                                                        <i class="fa-solid fa-eye"></i>
                                                        <span>View Logs</span>
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center py-5 text-muted">
                                                    <i class="fa-solid fa-users-slash fs-2 text-secondary opacity-50 mb-3 d-block"></i>
                                                    No team members found.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>

</div>

<!-- Edit Log Modal -->
<div class="modal fade" id="editLogModal" tabindex="-1" aria-labelledby="editLogModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header border-bottom px-4 py-3 bg-light">
                <h5 class="modal-title fw-bold text-dark fs-5" id="editLogModalLabel">
                    <i class="fa-solid fa-pen-to-square text-primary me-2"></i>Edit Time Log
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="edit-log-form" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="edit_task_title" class="form-label fw-bold text-dark small">Task Title / Description</label>
                        <input type="text" class="form-control form-control-lg fs-6 rounded-3" id="edit_task_title" name="task_title" placeholder="What were you working on?">
                    </div>
                    <div class="mb-3">
                        <label for="edit_project_id" class="form-label fw-bold text-dark small">Project</label>
                        <select class="form-select form-select-lg fs-6 rounded-3" id="edit_project_id" name="project_id">
                            <option value="">Miscellaneous / General Work</option>
                            @foreach($projects as $project)
                                <option value="{{ $project->id }}">{{ $project->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-top px-4 py-3 bg-light d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-danger btn-sm rounded-3 fw-bold" onclick="handleDeleteLog()">
                        <i class="fa-solid fa-trash me-1"></i> Delete Log
                    </button>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-light fw-semibold rounded-3 px-3" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary fw-bold rounded-3 px-4">Save Changes</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- GPS Location Map Modal -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<div class="modal fade" id="locationMapModal" tabindex="-1" aria-labelledby="locationMapModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header border-bottom px-4 py-3 bg-light d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle bg-danger-subtle text-danger d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                        <i class="fa-solid fa-location-dot fs-6"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark fs-6 mb-0" id="locationMapModalLabel">Clock-in GPS Verification</h5>
                        <small class="text-muted" id="map-modal-subtitle">Verified attendance coordinates</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div id="attendance-leaflet-map" style="height: 380px; width: 100%;"></div>
            </div>
            <div class="modal-footer px-4 py-2.5 bg-light d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="d-flex align-items-center gap-3 font-monospace small">
                    <span><i class="fa-solid fa-crosshairs text-primary me-1"></i> <strong id="map-modal-coords">--</strong></span>
                    <span><i class="fa-solid fa-network-wired text-secondary me-1"></i> IP: <strong id="map-modal-ip">--</strong></span>
                </div>
                <a id="map-modal-gmaps-link" href="#" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 fw-semibold">
                    <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Open Google Maps
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Screenshot Lightbox Fullscreen Modal -->
<div class="modal fade" id="screenshotLightboxModal" tabindex="-1" aria-labelledby="screenshotLightboxModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 shadow-lg bg-dark text-white rounded-4 overflow-hidden">
            <div class="modal-header border-secondary border-opacity-25 py-2.5 px-4 bg-black bg-opacity-50 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-primary text-white font-monospace fs-6" id="lightbox-time-badge">--:--</span>
                    <span class="text-white fw-bold small ms-1" id="lightbox-project-badge">Project</span>
                    <span class="text-muted small mx-1">&bull;</span>
                    <span class="text-white-50 small" id="lightbox-task-title">Task</span>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <span class="badge bg-light bg-opacity-10 text-light font-monospace small px-3 py-1.5 rounded-pill" id="lightbox-counter-badge">1 / 1</span>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            <div class="modal-body p-0 position-relative d-flex align-items-center justify-content-center bg-black" style="min-height: 480px; max-height: 78vh; user-select: none;">
                <!-- Prev Button -->
                <button type="button" onclick="navigateLightbox(-1)" id="lightbox-prev-btn" class="btn btn-dark bg-opacity-75 text-white rounded-circle position-absolute start-0 ms-3 d-flex align-items-center justify-content-center shadow-lg hover-scale z-3 border border-secondary" style="width: 48px; height: 48px;">
                    <i class="fa-solid fa-chevron-left fs-5"></i>
                </button>
                <!-- Image Container -->
                <div class="w-100 h-100 d-flex align-items-center justify-content-center p-2">
                    <img id="lightbox-main-img" src="" alt="Screenshot" class="img-fluid rounded-2 shadow" style="max-height: 74vh; max-width: 100%; object-fit: contain;">
                </div>
                <!-- Next Button -->
                <button type="button" onclick="navigateLightbox(1)" id="lightbox-next-btn" class="btn btn-dark bg-opacity-75 text-white rounded-circle position-absolute end-0 me-3 d-flex align-items-center justify-content-center shadow-lg hover-scale z-3 border border-secondary" style="width: 48px; height: 48px;">
                    <i class="fa-solid fa-chevron-right fs-5"></i>
                </button>
            </div>
            <div class="modal-footer border-secondary border-opacity-25 py-2.5 px-4 bg-black bg-opacity-50 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="text-white-50 small font-monospace" id="lightbox-timestamp-full">
                    <i class="fa-regular fa-clock me-1 text-warning"></i> Captured at: --
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a id="lightbox-download-link" href="#" target="_blank" download class="btn btn-sm btn-outline-light rounded-pill px-3 py-1 font-monospace" style="font-size: 0.78rem;">
                        <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Open Original
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Desktop Tracker Help & Setup Modal -->
<div class="modal fade" id="desktopTrackerHelpModal" tabindex="-1" aria-labelledby="desktopTrackerHelpModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header border-bottom py-3 px-4 bg-light">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                        <i class="fa-solid fa-desktop"></i>
                    </div>
                    <div>
                        <h6 class="modal-title fw-bold text-dark mb-0" id="desktopTrackerHelpModalLabel">SyncWithTeam Desktop Companion Tracker</h6>
                        <span class="text-muted font-monospace small" style="font-size: 0.75rem;">100% Silent &bull; Zero Popups &bull; Full Laptop Desktop Capture</span>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-info border-0 rounded-3 py-2.5 px-3.5 mb-4 d-flex align-items-center gap-3">
                    <i class="fa-solid fa-shield-check fs-4 text-primary flex-shrink-0"></i>
                    <div class="small">
                        <strong>Zero Browser Popups:</strong> Unlike standard browser popups that constantly ask for screen sharing permissions and only capture single tabs, the SyncWithTeam Desktop Companion runs silently in the background on your PC and captures your entire active workspace (VS Code, Chrome, Slack, Excel, etc.) every 2 minutes.
                    </div>
                </div>

                <div class="card border-0 bg-primary-subtle rounded-4 p-3.5 mb-4">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                        <div>
                            <h6 class="fw-bold text-dark mb-1">Get the SyncWithTeam Desktop Agent</h6>
                            <p class="text-secondary small mb-0">Lightweight, portable companion for 100% silent background laptop screen capture.</p>
                        </div>
                        <a href="{{ route('attendance.download-tracker') }}" class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm d-flex align-items-center gap-2">
                            <i class="fa-solid fa-download"></i>
                            <span>Download Tracker ZIP</span>
                        </a>
                    </div>
                </div>

                <h6 class="fw-bold text-dark mb-3">Simple 3-Step Setup (Only Once):</h6>

                <div class="d-flex flex-column gap-3 mb-4">
                    <div class="d-flex align-items-start gap-3 p-3 bg-light rounded-3 border">
                        <span class="badge bg-primary text-white rounded-circle p-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 32px; height: 32px;">1</span>
                        <div>
                            <div class="fw-bold text-dark small mb-1">Download & Extract the ZIP</div>
                            <p class="text-muted small mb-0">Download <code class="text-dark bg-white px-1.5 py-0.5 rounded border">SyncWithTeam-Tracker.zip</code> and extract it anywhere on your laptop (e.g. Downloads or Documents).</p>
                        </div>
                    </div>

                    <div class="d-flex align-items-start gap-3 p-3 bg-light rounded-3 border">
                        <span class="badge bg-primary text-white rounded-circle p-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 32px; height: 32px;">2</span>
                        <div>
                            <div class="fw-bold text-dark small mb-1">Run `Install-AutoStart.bat` (1-Click)</div>
                            <p class="text-muted small mb-0">Double-click <strong class="text-dark font-monospace">Install-AutoStart.bat</strong>. It configures the tracker to run silently in the background on laptop boot (no black console window will stay open).</p>
                        </div>
                    </div>

                    <div class="d-flex align-items-start gap-3 p-3 bg-light rounded-3 border">
                        <span class="badge bg-success text-white rounded-circle p-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 32px; height: 32px;"><i class="fa-solid fa-check"></i></span>
                        <div>
                            <div class="fw-bold text-dark small mb-1">Start Timer on Web Portal</div>
                            <p class="text-muted small mb-0">Whenever you click <strong class="text-primary">"Start"</strong> timer on the SyncWithTeam website, your laptop activities (VS Code, Chrome, Slack, etc.) are silently captured every 2 minutes with <strong>zero popups</strong>!</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top py-2.5 px-4 bg-light d-flex justify-content-end">
                <button type="button" class="btn btn-secondary rounded-pill px-4 fw-semibold" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let currentEditingLogId = null;
    let leafletMap = null;
    let leafletMarker = null;

    // --- SCREENSHOTS & 2-MINUTE AUTOMATIC SCREEN TRACKER (DESKTOP COMPANION AGENT) ---
    let currentScreenshotsList = @json($initialScreenshotsFormatted ?? []);

    let currentSelectedScreenshotDate = "{{ $selectedScreenshotDate }}";
    let currentSelectedScreenshotUserId = "{{ $selectedScreenshotUser->id }}";
    let activeLightboxIndex = 0;

    const DESKTOP_TRACKER_BRIDGE_URL = 'http://127.0.0.1:49152';
    window.desktopTrackerStatusInterval = null;

    // Check status of background companion agent
    async function checkDesktopTrackerStatus() {
        const badge = document.getElementById('screen-capture-status-badge');
        const textEl = document.getElementById('screen-capture-status-text');
        
        try {
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 1800);
            
            const res = await fetch(`${DESKTOP_TRACKER_BRIDGE_URL}/status`, { 
                signal: controller.signal 
            });
            clearTimeout(timeoutId);
            const data = await res.json();
            
            if (data.ok) {
                if (data.state && data.state.running) {
                    updateCaptureBadgeStatus(true, 'Desktop Tracker Active (2m)');
                } else {
                    updateCaptureBadgeStatus(false, 'Desktop Companion Ready');
                }
                return true;
            }
        } catch (e) {
            updateCaptureBadgeStatus(false, 'Companion Standby');
            return false;
        }
        return false;
    }

    // Start Silent Screen Capture via Desktop Companion (Zero Popups)
    async function startScreenCaptureTracker() {
        const userId = {{ $currentUser->id }};
        const projSelect = document.getElementById('project_id_select');
        const taskInput = document.getElementById('task_title_input');
        const projectId = projSelect ? projSelect.value : null;
        const taskTitle = taskInput ? taskInput.value : '';

        const payload = {
            userId: userId,
            projectId: projectId,
            taskTitle: taskTitle,
            apiUrl: "{{ route('api.attendance.screenshot.store') }}",
            activeSessionUrl: "{{ route('api.attendance.active-session') }}",
            intervalSeconds: 120
        };

        try {
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 2500);

            const res = await fetch(`${DESKTOP_TRACKER_BRIDGE_URL}/start`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload),
                signal: controller.signal
            });
            clearTimeout(timeoutId);

            const data = await res.json();
            if (data.success) {
                updateCaptureBadgeStatus(true, 'Desktop Tracker Active');
                console.log('✅ Desktop Tracker connected and capturing silently.');
                
                // Schedule automatic gallery refresh at 2.5s and 5s to show immediate initial screenshot
                setTimeout(() => {
                    const todayStr = new Date().toISOString().split('T')[0];
                    if (currentSelectedScreenshotDate === todayStr && currentSelectedScreenshotUserId == userId) {
                        refreshCurrentDayScreenshots(false);
                    }
                }, 2500);

                setTimeout(() => {
                    const todayStr = new Date().toISOString().split('T')[0];
                    if (currentSelectedScreenshotDate === todayStr && currentSelectedScreenshotUserId == userId) {
                        refreshCurrentDayScreenshots(false);
                    }
                }, 5000);

                // Set auto-refresh of today's gallery every 2 minutes
                if (window.desktopTrackerStatusInterval) clearInterval(window.desktopTrackerStatusInterval);
                window.desktopTrackerStatusInterval = setInterval(() => {
                    const todayStr = new Date().toISOString().split('T')[0];
                    if (currentSelectedScreenshotDate === todayStr && currentSelectedScreenshotUserId == userId) {
                        refreshCurrentDayScreenshots(false);
                    }
                }, 125000);
            }
        } catch (err) {
            updateCaptureBadgeStatus(false, 'Companion Offline');
            showToast('warning', 'Desktop Tracker Companion is offline. Please launch Start-Tracker.bat to enable background screenshots.');
            console.info('Desktop companion offline. Run desktop-tracker/Start-Tracker.bat to enable screen capture.');
        }
    }

    // Helper to wake up desktop tracker on system
    function launchDesktopTrackerProtocol() {
        try {
            let iframe = document.getElementById('syncwithteam-protocol-frame');
            if (!iframe) {
                iframe = document.createElement('iframe');
                iframe.id = 'syncwithteam-protocol-frame';
                iframe.style.display = 'none';
                document.body.appendChild(iframe);
            }
            iframe.src = 'syncwithteam://start';
        } catch (e) {}
    }

    // Stop Silent Screen Capture
    async function stopScreenCaptureTracker() {
        if (window.desktopTrackerStatusInterval) {
            clearInterval(window.desktopTrackerStatusInterval);
            window.desktopTrackerStatusInterval = null;
        }

        try {
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 2000);

            await fetch(`${DESKTOP_TRACKER_BRIDGE_URL}/stop`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                signal: controller.signal
            });
            clearTimeout(timeoutId);
            updateCaptureBadgeStatus(false, 'Capture Inactive');
        } catch (err) {
            updateCaptureBadgeStatus(false, 'Capture Inactive');
        }
    }

    // Trigger instant screenshot capture manually
    async function triggerInstantScreenshot() {
        try {
            showToast('info', 'Triggering instant full laptop screen capture...');
            const res = await fetch(`${DESKTOP_TRACKER_BRIDGE_URL}/capture-now`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' }
            });
            const data = await res.json();
            if (data.success) {
                showToast('success', 'Screenshot captured and uploaded!');
                setTimeout(() => refreshCurrentDayScreenshots(false), 1500);
            } else {
                showToast('error', data.message || 'Capture failed');
            }
        } catch (e) {
            showToast('error', 'Desktop Companion is offline. Please launch desktop-tracker/Start-Tracker.bat');
        }
    }

    function updateCaptureBadgeStatus(isActive, text) {
        const badge = document.getElementById('screen-capture-status-badge');
        const textEl = document.getElementById('screen-capture-status-text');
        if (!badge || !textEl) return;

        if (isActive) {
            badge.className = 'badge bg-emerald-50 text-success border border-success-subtle rounded-pill px-3 py-2 fw-bold d-flex align-items-center gap-1.5 shadow-2xs';
            badge.style.backgroundColor = '#ECFDF5';
            textEl.textContent = text || 'Capture Active (2m)';
            badge.querySelector('.spinner-grow')?.classList.remove('d-none');
        } else {
            badge.className = 'badge bg-light text-secondary border rounded-pill px-3 py-2 fw-semibold d-flex align-items-center gap-1.5 shadow-2xs';
            badge.style.backgroundColor = '';
            textEl.textContent = text || 'Companion Standby';
            badge.querySelector('.spinner-grow')?.classList.add('d-none');
        }
    }

    // Day & User Switcher Handlers
    function handleScreenshotDayClick(dateStr, btnEl) {
        currentSelectedScreenshotDate = dateStr;

        // Update button active styles
        document.querySelectorAll('.day-selector-btn').forEach(b => {
            b.className = 'btn flex-fill text-center py-2.5 px-3 rounded-3 border transition-all day-selector-btn btn-light text-dark bg-light hover-bg-light-subtle';
            const f = b.querySelector('.fs-6');
            if (f) f.className = 'fw-bold fs-6 mb-1 font-monospace text-dark';
        });

        if (btnEl) {
            btnEl.className = 'btn flex-fill text-center py-2.5 px-3 rounded-3 border transition-all day-selector-btn btn-primary text-white shadow-sm border-primary active-day-btn';
            const f = btnEl.querySelector('.fs-6');
            if (f) f.className = 'fw-bold fs-6 mb-1 font-monospace text-white';
            
            const dayName = btnEl.getAttribute('data-day-name') || '';
            const dayFmt = btnEl.getAttribute('data-formatted') || '';
            const dayBadge = document.getElementById('gallery-active-day-badge');
            if (dayBadge) dayBadge.textContent = `${dayName}, ${dayFmt} {{ now()->format('Y') }}`;
        }

        fetchAndRenderScreenshots(currentSelectedScreenshotUserId, currentSelectedScreenshotDate);
    }

    function handleScreenshotUserChange(userId) {
        currentSelectedScreenshotUserId = userId;
        const select = document.getElementById('screenshot_user_select');
        const nameEl = document.getElementById('current-selected-user-name');
        if (select && nameEl) {
            nameEl.textContent = select.options[select.selectedIndex].text.split('(')[0].trim();
        }
        fetchAndRenderScreenshots(currentSelectedScreenshotUserId, currentSelectedScreenshotDate);
    }

    function refreshCurrentDayScreenshots(showLoader = true) {
        fetchAndRenderScreenshots(currentSelectedScreenshotUserId, currentSelectedScreenshotDate, showLoader);
    }

    function fetchAndRenderScreenshots(userId, dateStr, showLoader = true) {
        const container = document.getElementById('screenshots-grid-container');
        if (showLoader && container) {
            container.innerHTML = `
                <div class="col-12 text-center py-5">
                    <div class="spinner-border text-primary mb-2" role="status"></div>
                    <div class="text-muted small">Loading screenshots...</div>
                </div>
            `;
        }

        fetch(`{{ route('attendance.screenshots.get') }}?user_id=${userId}&date=${dateStr}`, {
            headers: {
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                currentScreenshotsList = data.screenshots || [];
                renderScreenshotsGrid(currentScreenshotsList);
                const countLabel = document.getElementById('gallery-shots-count-label');
                if (countLabel) {
                    countLabel.textContent = `${currentScreenshotsList.length} screenshot${currentScreenshotsList.length === 1 ? '' : 's'} recorded`;
                }
            }
        })
        .catch(err => {
            console.error('Error fetching screenshots:', err);
            if (container) {
                container.innerHTML = `
                    <div class="col-12 text-center py-5 text-danger">
                        <i class="fa-solid fa-triangle-exclamation fs-3 mb-2"></i>
                        <p class="small mb-0">Failed to load screenshots.</p>
                    </div>
                `;
            }
        });
    }

    function renderScreenshotsGrid(shots) {
        const container = document.getElementById('screenshots-grid-container');
        if (!container) return;

        if (!shots || shots.length === 0) {
            container.innerHTML = `
                <div class="col-12 py-5 text-center text-muted" id="gallery-empty-state">
                    <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center p-4 mb-3 shadow-2xs border">
                        <i class="fa-solid fa-camera-rotate fs-1 text-primary opacity-50"></i>
                    </div>
                    <h6 class="fw-bold text-dark mb-1">No Screenshots Recorded for This Day</h6>
                    <p class="text-secondary small mb-0 max-w-md mx-auto" style="max-width: 420px;">
                        Screenshots are captured automatically every 2 minutes while the time tracker is active. Start the timer to begin tracking screen activity.
                    </p>
                </div>
            `;
            return;
        }

        let html = '';
        shots.forEach((shot, idx) => {
            const currentUserId = {{ $currentUser->id }};
            const canViewTeam = {{ $canViewTeam ? 'true' : 'false' }};
            const showDelete = (currentSelectedScreenshotUserId == currentUserId || canViewTeam);

            html += `
                <div class="col screenshot-item-col" id="shot-card-${shot.id}">
                    <div class="card h-100 border rounded-3 overflow-hidden shadow-2xs bg-white hover-shadow transition-all position-relative group">
                        <div class="position-relative overflow-hidden bg-dark ratio ratio-16x9" 
                             onclick="openScreenshotLightbox(${idx})" 
                             style="cursor: pointer;">
                            <img src="${shot.url}" 
                                 alt="Screenshot at ${shot.captured_at_formatted}" 
                                 class="card-img-top object-fit-cover transition-all screenshot-thumb" 
                                 loading="lazy"
                                 style="transition: transform 0.3s ease;">
                            <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center bg-dark bg-opacity-40 opacity-0 hover-opacity-100 transition-all text-white">
                                <div class="rounded-circle bg-white bg-opacity-25 p-2 d-flex align-items-center justify-content-center shadow-sm">
                                    <i class="fa-solid fa-magnifying-glass-plus fs-5"></i>
                                </div>
                            </div>
                            <div class="position-absolute top-2 start-2">
                                <span class="badge bg-dark bg-opacity-80 text-white font-monospace rounded-pill px-2.5 py-1" style="font-size: 0.72rem; backdrop-filter: blur(4px);">
                                    <i class="fa-regular fa-clock me-1 text-warning"></i>${shot.captured_at_formatted}
                                </span>
                            </div>
                        </div>

                        <div class="card-body p-2.5 d-flex flex-column justify-content-between">
                            <div class="mb-2">
                                <div class="d-flex align-items-center justify-content-between gap-1 mb-1">
                                    <span class="badge bg-light text-dark border rounded-pill px-2 py-0.5 text-truncate" style="max-width: 140px; font-size: 0.72rem;">
                                        📁 ${shot.project_name}
                                    </span>
                                    ${showDelete ? `
                                        <button type="button" 
                                                onclick="deleteScreenshot(${shot.id}, event)" 
                                                class="btn btn-link text-muted hover-danger p-0 border-0 text-decoration-none" 
                                                style="font-size: 0.75rem;" 
                                                title="Delete screenshot">
                                            <i class="fa-regular fa-trash-can"></i>
                                        </button>
                                    ` : ''}
                                </div>
                                <div class="fw-semibold text-dark text-truncate small" title="${shot.task_title}">
                                    ${shot.task_title}
                                </div>
                            </div>
                            <div class="text-muted font-monospace" style="font-size: 0.7rem;">
                                ${shot.captured_at_full}
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });

        container.innerHTML = html;
    }

    function deleteScreenshot(shotId, e) {
        if (e) e.stopPropagation();
        if (!confirm('Are you sure you want to delete this screenshot?')) return;

        fetch(`/attendance/screenshots/${shotId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}",
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showToast('success', data.message);
                const el = document.getElementById(`shot-card-${shotId}`);
                if (el) el.remove();
                currentScreenshotsList = currentScreenshotsList.filter(s => s.id != shotId);
                const countLabel = document.getElementById('gallery-shots-count-label');
                if (countLabel) {
                    countLabel.textContent = `${currentScreenshotsList.length} screenshot${currentScreenshotsList.length === 1 ? '' : 's'} recorded`;
                }
                if (currentScreenshotsList.length === 0) {
                    renderScreenshotsGrid([]);
                }
            } else {
                showToast('error', data.message || 'Could not delete screenshot');
            }
        })
        .catch(err => {
            console.error(err);
            showToast('error', 'Request failed.');
        });
    }

    // Lightbox Modal Functions
    function openScreenshotLightbox(index) {
        if (!currentScreenshotsList || currentScreenshotsList.length === 0) return;
        activeLightboxIndex = Math.max(0, Math.min(index, currentScreenshotsList.length - 1));
        updateLightboxView();

        const modalEl = document.getElementById('screenshotLightboxModal');
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    }

    function navigateLightbox(step) {
        if (!currentScreenshotsList || currentScreenshotsList.length === 0) return;
        activeLightboxIndex = (activeLightboxIndex + step + currentScreenshotsList.length) % currentScreenshotsList.length;
        updateLightboxView();
    }

    function updateLightboxView() {
        const item = currentScreenshotsList[activeLightboxIndex];
        if (!item) return;

        const mainImg = document.getElementById('lightbox-main-img');
        const timeBadge = document.getElementById('lightbox-time-badge');
        const projBadge = document.getElementById('lightbox-project-badge');
        const taskTitle = document.getElementById('lightbox-task-title');
        const counter = document.getElementById('lightbox-counter-badge');
        const timestampFull = document.getElementById('lightbox-timestamp-full');
        const downloadLink = document.getElementById('lightbox-download-link');

        if (mainImg) mainImg.src = item.url;
        if (timeBadge) timeBadge.textContent = item.captured_at_formatted;
        if (projBadge) projBadge.textContent = '📁 ' + item.project_name;
        if (taskTitle) taskTitle.textContent = item.task_title;
        if (counter) counter.textContent = `${activeLightboxIndex + 1} / ${currentScreenshotsList.length}`;
        if (timestampFull) timestampFull.innerHTML = `<i class="fa-regular fa-clock me-1 text-warning"></i> Captured at: ${item.captured_at_full}`;
        if (downloadLink) downloadLink.href = item.url;
    }

    document.addEventListener('keydown', function(e) {
        const modalEl = document.getElementById('screenshotLightboxModal');
        if (modalEl && modalEl.classList.contains('show')) {
            if (e.key === 'ArrowLeft') navigateLightbox(-1);
            if (e.key === 'ArrowRight') navigateLightbox(1);
        }
    });

    function openLocationMap(lat, lng, name, time, ip) {
        document.getElementById('map-modal-coords').textContent = `${parseFloat(lat).toFixed(5)}, ${parseFloat(lng).toFixed(5)}`;
        document.getElementById('map-modal-ip').textContent = ip || 'N/A';
        document.getElementById('map-modal-subtitle').textContent = `Logged by ${name} at ${time}`;
        document.getElementById('map-modal-gmaps-link').href = `https://www.google.com/maps?q=${lat},${lng}`;

        const modalEl = document.getElementById('locationMapModal');
        const bsModal = new bootstrap.Modal(modalEl);
        bsModal.show();

        modalEl.addEventListener('shown.bs.modal', function onModalShow() {
            modalEl.removeEventListener('shown.bs.modal', onModalShow);
            if (!leafletMap) {
                leafletMap = L.map('attendance-leaflet-map').setView([lat, lng], 16);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; OpenStreetMap'
                }).addTo(leafletMap);

                leafletMarker = L.marker([lat, lng]).addTo(leafletMap)
                    .bindPopup(`<b>${name}</b><br>Time: ${time}<br>IP: ${ip}`)
                    .openPopup();
            } else {
                leafletMap.setView([lat, lng], 16);
                leafletMarker.setLatLng([lat, lng])
                    .setPopupContent(`<b>${name}</b><br>Time: ${time}<br>IP: ${ip}`)
                    .openPopup();
                leafletMap.invalidateSize();
            }
        });
    }

    function openEditLogModal(logId, taskTitle, projectId) {
        currentEditingLogId = logId;
        const modalEl = document.getElementById('editLogModal');
        const form = document.getElementById('edit-log-form');
        const taskInput = document.getElementById('edit_task_title');
        const projectSelect = document.getElementById('edit_project_id');

        form.action = `/attendance/${logId}`;
        taskInput.value = taskTitle;
        projectSelect.value = projectId || '';

        const bsModal = new bootstrap.Modal(modalEl);
        bsModal.show();
    }

    function handleDeleteLog() {
        if (!currentEditingLogId) return;
        if (confirm('Are you sure you want to delete this time log record?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/attendance/${currentEditingLogId}`;
            
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = "{{ csrf_token() }}";

            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';

            form.appendChild(csrfInput);
            form.appendChild(methodInput);
            document.body.appendChild(form);
            form.submit();
        }
    }

    let timerInterval = null;

    document.addEventListener('DOMContentLoaded', function () {
        initLiveTimer();
        initPopovers();
        checkDesktopTrackerStatus();

        @if($activeAttendance)
            // Active tracking session is in progress, attempt screen capture tracker start
            setTimeout(() => {
                startScreenCaptureTracker();
            }, 800);
        @endif
    });

    function initPopovers() {
        const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
        popoverTriggerList.map(function (popoverTriggerEl) {
            return new bootstrap.Popover(popoverTriggerEl);
        });
    }

    function initLiveTimer() {
        if (timerInterval) clearInterval(timerInterval);

        function updateAllTimers() {
            const now = new Date();

            // 1. Top card timer
            const logsTimer = document.getElementById('logs-running-timer');
            if (logsTimer) {
                const startTime = new Date(logsTimer.getAttribute('data-start'));
                const diffMs = now - startTime;
                if (diffMs >= 0) {
                    const totalSecs = Math.floor(diffMs / 1000);
                    const hrs = String(Math.floor(totalSecs / 3600)).padStart(2, '0');
                    const mins = String(Math.floor((totalSecs % 3600) / 60)).padStart(2, '0');
                    const secs = String(totalSecs % 60).padStart(2, '0');
                    logsTimer.textContent = `${hrs}:${mins}:${secs}`;
                }
            }

            // 2. Listing row active timers
            const rowTimers = document.querySelectorAll('.live-row-timer');
            rowTimers.forEach(el => {
                const startTime = new Date(el.getAttribute('data-start'));
                const diffMs = now - startTime;
                if (diffMs >= 0) {
                    const totalSecs = Math.floor(diffMs / 1000);
                    const hrs = String(Math.floor(totalSecs / 3600)).padStart(2, '0');
                    const mins = String(Math.floor((totalSecs % 3600) / 60)).padStart(2, '0');
                    const secs = String(totalSecs % 60).padStart(2, '0');
                    el.innerHTML = `<i class="fa-solid fa-stopwatch me-1 text-success"></i>${hrs}:${mins}:${secs}`;
                }
            });
        }

        updateAllTimers();
        timerInterval = setInterval(updateAllTimers, 1000);
    }

    // Floating Toast Helper
    function showToast(type, message) {
        const container = document.getElementById('toast-container');
        if (!container) return;
        const toastId = 'toast-' + Date.now();
        const bgClass = type === 'success' ? 'bg-success text-white' : 'bg-danger text-white';
        const icon = type === 'success' ? 'fa-circle-check' : 'fa-triangle-exclamation';
        
        const toastHTML = `
            <div id="${toastId}" class="toast align-items-center ${bgClass} border-0 custom-toast show" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body d-flex align-items-center gap-2 fw-semibold">
                        <i class="fa-solid ${icon} fs-5"></i>
                        <span>${message}</span>
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" onclick="document.getElementById('${toastId}').remove()"></button>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', toastHTML);
        setTimeout(() => {
            const el = document.getElementById(toastId);
            if (el) el.remove();
        }, 4000);
    }

    // Asynchronous Tracker Form Submit with GPS Capture
    function handleTrackerSubmit(event, mode) {
        event.preventDefault();
        const form = event.target;
        const formData = new FormData(form);
        const url = mode === 'start' ? "{{ route('attendance.clock-in') }}" : "{{ route('attendance.switch') }}";

        function sendPayload(lat, lon) {
            if (lat && lon) {
                formData.set('latitude', lat);
                formData.set('longitude', lon);
            }

            fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}",
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast('success', data.message);
                    renderActiveState(data.attendance);
                    refreshNavbarTimer(data.attendance);
                    if (mode === 'start') {
                        startScreenCaptureTracker();
                    }
                } else {
                    showToast('error', data.message || 'Error occurred');
                }
            })
            .catch(err => {
                console.error(err);
                showToast('error', 'Request failed.');
            });
        }

        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function(pos) {
                    sendPayload(pos.coords.latitude, pos.coords.longitude);
                },
                function(err) {
                    sendPayload(null, null);
                },
                { timeout: 5000, enableHighAccuracy: true }
            );
        } else {
            sendPayload(null, null);
        }
    }


    function filterProjectDropdown(inputEl) {
        const term = inputEl.value.toLowerCase().trim();
        const menu = inputEl.closest('.dropdown-menu');
        if (menu) {
            const items = menu.querySelectorAll('.project-item-btn');
            items.forEach(btn => {
                const text = btn.textContent.toLowerCase();
                const parentLi = btn.closest('li');
                if (parentLi) {
                    if (text.includes(term)) {
                        parentLi.style.display = '';
                    } else {
                        parentLi.style.display = 'none';
                    }
                }
            });
        }
    }

    function selectProjectCustom(id, name, btnEl) {
        const hiddenSelect = document.getElementById('project_id_select');
        const label = document.getElementById('custom_project_label');
        
        if (hiddenSelect) {
            hiddenSelect.value = id;
        }
        if (label) {
            label.textContent = name;
        }

        if (btnEl) {
            const menu = btnEl.closest('.dropdown-menu');
            if (menu) {
                menu.querySelectorAll('.dropdown-item').forEach(item => {
                    item.classList.remove('active', 'bg-primary', 'text-white');
                    const chk = item.querySelector('.check-mark');
                    if (chk) chk.classList.add('d-none');
                });
                btnEl.classList.add('active', 'bg-primary', 'text-white');
                const chk = btnEl.querySelector('.check-mark');
                if (chk) chk.classList.remove('d-none');
            }
        }

        if (hiddenSelect && document.getElementById('active-tracker-form')) {
            handleProjectChange(hiddenSelect);
        }
    }

    function handleProjectChange(selectEl) {
        const form = document.getElementById('active-tracker-form');
        if (form) {
            form.dispatchEvent(new Event('submit', { cancelable: true, bubbles: true }));
        }
    }

    function handleStopTimer() {
        const taskInput = document.getElementById('task_title_input');
        const projectSelect = document.getElementById('project_id_select');

        const taskTitle = taskInput ? taskInput.value.trim() : '';
        const projectId = projectSelect ? projectSelect.value.trim() : '';

        // Reset previous validation error styling
        if (taskInput) taskInput.closest('.input-group')?.classList.remove('border-danger', 'shadow-sm');
        if (projectSelect) projectSelect.closest('.input-group')?.classList.remove('border-danger', 'shadow-sm');

        let hasError = false;

        if (!taskTitle) {
            if (taskInput) {
                taskInput.closest('.input-group')?.classList.add('border-danger', 'shadow-sm');
                taskInput.focus();
            }
            hasError = true;
        }

        if (!projectId) {
            if (projectSelect) {
                projectSelect.closest('.input-group')?.classList.add('border-danger', 'shadow-sm');
                if (!hasError) projectSelect.focus();
            }
            hasError = true;
        }

        if (hasError) {
            showToast('error', 'Please add a task description and select a project before stopping the tracker.');
            return;
        }

        const formData = new FormData();
        formData.append('task_title', taskTitle);
        formData.append('project_id', projectId);

        fetch("{{ route('attendance.clock-out') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}",
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                stopScreenCaptureTracker();
                window.location.reload();
            } else {
                showToast('error', data.message || 'Could not stop timer');
            }
        })
        .catch(err => {
            console.error(err);
            showToast('error', 'Request failed.');
        });
    }

    document.addEventListener('input', function(e) {
        if (e.target && e.target.id === 'task_title_input') {
            e.target.closest('.input-group')?.classList.remove('border-danger', 'shadow-sm');
        }
    });

    document.addEventListener('change', function(e) {
        if (e.target && e.target.id === 'project_id_select') {
            e.target.closest('.input-group')?.classList.remove('border-danger', 'shadow-sm');
        }
    });

    function handleQuickRestart(projectId, taskTitle, taskId) {
        const formData = new FormData();
        if (projectId) formData.append('project_id', projectId);
        if (taskTitle) formData.append('task_title', taskTitle);
        if (taskId) formData.append('task_id', taskId);

        fetch("{{ route('attendance.clock-in') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}",
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showToast('success', data.message);
                renderActiveState(data.attendance);
                refreshNavbarTimer(data.attendance);
                startScreenCaptureTracker();
            } else {
                showToast('error', data.message || 'Failed to start task');
            }
        })
        .catch(err => console.error(err));
    }

    function renderActiveState(attendance) {
        const container = document.getElementById('tracker-container');
        const taskTitle = attendance.task ? attendance.task.title : '';
        const clockInAt = attendance.clock_in_at;
        const currentProjId = attendance.project_id;
        
        let activeProjName = '+ Project';
        let customItemsHtml = `
            <li>
                <button class="dropdown-item py-2 px-3 rounded-2 fw-medium d-flex align-items-center justify-content-between text-body ${!currentProjId ? 'active bg-primary text-white' : ''}" 
                        type="button" 
                        onclick="selectProjectCustom('', '+ Project', this)">
                    <span>+ Project</span>
                    <i class="fa-solid fa-check small check-mark ${!currentProjId ? '' : 'd-none'}"></i>
                </button>
            </li>
            <li><hr class="dropdown-divider my-1 opacity-10"></li>
        `;

        let projectOptions = '<option value="">+ Project</option>';
        @foreach($projects as $project)
            if (currentProjId == {{ $project->id }}) {
                activeProjName = "{{ addslashes($project->name) }}";
            }
            projectOptions += '<option value="{{ $project->id }}" ' + (currentProjId == {{ $project->id }} ? 'selected' : '') + '>{{ addslashes($project->name) }}</option>';
            
            const isSel{{ $project->id }} = (currentProjId == {{ $project->id }});
            customItemsHtml += `
                <li>
                    <button class="dropdown-item py-2 px-3 rounded-2 fw-medium d-flex align-items-center justify-content-between text-body ${isSel{{ $project->id }} ? 'active bg-primary text-white' : ''}" 
                            type="button" 
                            onclick="selectProjectCustom('{{ $project->id }}', '{{ addslashes($project->name) }}', this)">
                        <span class="text-truncate me-2">{{ addslashes($project->name) }}</span>
                        <i class="fa-solid fa-check small check-mark ${isSel{{ $project->id }} ? '' : 'd-none'}"></i>
                    </button>
                </li>
            `;
        @endforeach

        container.innerHTML = `
            <form id="active-tracker-form" onsubmit="handleTrackerSubmit(event, 'switch')" class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                @csrf
                <div class="d-flex align-items-center flex-grow-1 gap-3 min-w-0" style="min-width: 280px;">
                    <div class="input-group tracker-pill-input px-3 py-1 flex-grow-1">
                        <span class="input-group-text bg-transparent border-0 text-primary ps-0 pe-2">
                            <i class="fa-solid fa-bolt fs-6"></i>
                        </span>
                        <input type="text" id="task_title_input" name="task_title" class="form-control border-0 shadow-none bg-transparent py-1 fw-semibold text-body" style="font-size: 0.9rem;" 
                               placeholder="What are you working on?"
                               value="${taskTitle}">
                    </div>
                    <div class="input-group tracker-pill-input px-3 py-1 flex-shrink-0 position-relative dropdown" style="width: 200px;">
                        <select id="project_id_select" name="project_id" class="d-none" onchange="handleProjectChange(this)">
                            ${projectOptions}
                        </select>
                        <button type="button" 
                                class="btn btn-transparent border-0 shadow-none fw-semibold text-body py-1 px-0 text-start text-truncate flex-grow-1 d-flex align-items-center justify-content-between" 
                                id="custom_project_btn"
                                data-bs-toggle="dropdown" 
                                data-bs-display="static" 
                                aria-expanded="false" 
                                style="font-size: 0.85rem;">
                            <span class="text-truncate me-1" id="custom_project_label">${activeProjName}</span>
                            <i class="fa-solid fa-chevron-down text-muted" style="font-size: 0.65rem;"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-lg border rounded-3 p-1 mt-1 font-sans" style="min-width: 220px; z-index: 9999 !important; border-color: var(--bs-border-color) !important;" id="custom_project_menu">
                            <li class="px-2 py-1 mb-1" onclick="event.stopPropagation();">
                                <div class="input-group input-group-sm bg-body-tertiary rounded-2 border px-2 py-1">
                                    <span class="input-group-text bg-transparent border-0 text-muted p-0 me-1.5" style="font-size: 0.75rem;">
                                        <i class="fa-solid fa-magnifying-glass"></i>
                                    </span>
                                    <input type="text" class="form-control bg-transparent border-0 shadow-none p-0 small text-body" placeholder="Search project..." onkeyup="filterProjectDropdown(this)" onclick="event.stopPropagation();">
                                </div>
                            </li>
                            <li onclick="event.stopPropagation();"><hr class="dropdown-divider my-1 opacity-10"></li>
                            <div class="project-items-list" style="max-height: 200px; overflow-y: auto;">
                                ${customItemsHtml}
                            </div>
                        </ul>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-3 ms-auto">
                    <div class="d-flex align-items-center gap-2 text-success rounded-pill border border-success-subtle shadow-sm" style="background-color: #ECFDF5; padding: 0.45rem 1.1rem !important;">
                        <span class="spinner-grow spinner-grow-sm text-success live-pulse flex-shrink-0" style="width: 8px; height: 8px;"></span>
                        <div id="logs-running-timer" class="fs-5 fw-bold font-monospace text-success lh-1" data-start="${clockInAt}">
                            00:00:00
                        </div>
                    </div>
                    <button type="button" onclick="handleStopTimer()" class="btn btn-danger btn-sm px-4 py-2 fw-bold text-uppercase rounded-pill shadow-sm d-flex align-items-center gap-2" style="font-size: 0.8rem; letter-spacing: 0.4px;">
                        <i class="fa-solid fa-square" style="font-size: 0.75rem;"></i> Stop
                    </button>
                </div>
            </form>
        `;
        initLiveTimer();
        initPopovers();
    }

    function renderIdleState() {
        const container = document.getElementById('tracker-container');
        if (timerInterval) clearInterval(timerInterval);
        
        container.innerHTML = `
            <form id="idle-tracker-form" onsubmit="handleTrackerSubmit(event, 'start')" class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                @csrf
                <div class="d-flex align-items-center flex-grow-1 gap-3 min-w-0" style="min-width: 280px;">
                    <div class="input-group tracker-pill-input px-3 py-1 flex-grow-1">
                        <span class="input-group-text bg-transparent border-0 text-muted ps-0 pe-2">
                            <i class="fa-solid fa-pen-to-square" style="font-size: 0.85rem;"></i>
                        </span>
                        <input type="text" id="task_title_input" name="task_title" class="form-control border-0 shadow-none bg-transparent py-1 fw-semibold text-body" style="font-size: 0.9rem;" 
                               placeholder="What are you working on?">
                    </div>
                    <div class="input-group tracker-pill-input px-3 py-1 flex-shrink-0 position-relative dropdown" style="width: 200px;">
                        <select id="project_id_select" name="project_id" class="d-none">
                            <option value="">+ Project</option>
                            @foreach($projects as $project)
                                <option value="{{ $project->id }}">
                                    {{ $project->name }}
                                </option>
                            @endforeach
                        </select>
                        <button type="button" 
                                class="btn btn-transparent border-0 shadow-none fw-semibold text-body py-1 px-0 text-start text-truncate flex-grow-1 d-flex align-items-center justify-content-between" 
                                id="custom_project_btn"
                                data-bs-toggle="dropdown" 
                                data-bs-display="static" 
                                aria-expanded="false" 
                                style="font-size: 0.85rem;">
                            <span class="text-truncate me-1" id="custom_project_label">+ Project</span>
                            <i class="fa-solid fa-chevron-down text-muted" style="font-size: 0.65rem;"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-lg border rounded-3 p-1 mt-1 font-sans" style="min-width: 220px; z-index: 9999 !important; border-color: var(--bs-border-color) !important;" id="custom_project_menu">
                            <li class="px-2 py-1 mb-1" onclick="event.stopPropagation();">
                                <div class="input-group input-group-sm bg-body-tertiary rounded-2 border px-2 py-1">
                                    <span class="input-group-text bg-transparent border-0 text-muted p-0 me-1.5" style="font-size: 0.75rem;">
                                        <i class="fa-solid fa-magnifying-glass"></i>
                                    </span>
                                    <input type="text" class="form-control bg-transparent border-0 shadow-none p-0 small text-body" placeholder="Search project..." onkeyup="filterProjectDropdown(this)" onclick="event.stopPropagation();">
                                </div>
                            </li>
                            <li onclick="event.stopPropagation();"><hr class="dropdown-divider my-1 opacity-10"></li>
                            <div class="project-items-list" style="max-height: 200px; overflow-y: auto;">
                                <li>
                                    <button class="dropdown-item project-item-btn py-2 px-3 rounded-2 fw-medium d-flex align-items-center justify-content-between text-body active bg-primary text-white" 
                                            type="button" 
                                            onclick="selectProjectCustom('', '+ Project', this)">
                                        <span>+ Project</span>
                                        <i class="fa-solid fa-check small check-mark"></i>
                                    </button>
                                </li>
                                <li><hr class="dropdown-divider my-1 opacity-10"></li>
                                @foreach($projects as $project)
                                    <li>
                                        <button class="dropdown-item project-item-btn py-2 px-3 rounded-2 fw-medium d-flex align-items-center justify-content-between text-body" 
                                                type="button" 
                                                onclick="selectProjectCustom('{{ $project->id }}', '{{ addslashes($project->name) }}', this)">
                                            <span class="text-truncate me-2">{{ $project->name }}</span>
                                            <i class="fa-solid fa-check small check-mark d-none"></i>
                                        </button>
                                    </li>
                                @endforeach
                            </div>
                        </ul>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-3 ms-auto">
                    <div class="fs-5 fw-bold text-secondary opacity-75 font-monospace pe-2 lh-1" style="letter-spacing: 0.3px;">
                        00:00:00
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm px-4 py-2 fw-bold text-uppercase rounded-pill shadow-sm d-flex align-items-center gap-2" style="font-size: 0.8rem; letter-spacing: 0.4px;">
                        <i class="fa-solid fa-play" style="font-size: 0.75rem;"></i> Start
                    </button>
                </div>
            </form>
        `;
    }

    function refreshNavbarTimer(attendance) {
        const container = document.getElementById('navbar-active-tracker-container');
        if (container) {
            const projName = attendance.project ? attendance.project.name : '';
            const taskTitle = attendance.task ? attendance.task.title : '';
            const displayTitle = taskTitle || projName || 'No Task / Project';
            const hasProj = attendance.project_id ? '1' : '0';
            const hasTsk = (attendance.task_id && taskTitle) ? '1' : '0';

            container.innerHTML = `
                <div class="d-flex align-items-center bg-emerald-50 border border-success-subtle rounded-pill px-3 py-1 gap-2 shadow-sm navbar-active-timer-badge" 
                     style="background-color: #ECFDF5;"
                     data-has-project="${hasProj}"
                     data-has-task="${hasTsk}"
                     data-project-id="${attendance.project_id || ''}"
                     data-task-title="${taskTitle}">
                    <span class="spinner-grow spinner-grow-sm text-success live-pulse" role="status" style="width: 8px; height: 8px;"></span>
                    <span class="fw-bold text-success small font-monospace" id="navbar-running-timer" data-start="${attendance.clock_in_at}">00:00:00</span>
                    <span class="text-secondary text-truncate px-2 border-start border-end" style="max-width: 180px; font-size: 0.8rem;" title="${displayTitle}">
                        <i class="fa-solid fa-play me-1 text-success"></i> ${displayTitle}
                    </span>
                    <button type="button" onclick="stopActiveTimerFromNavbar()" class="btn btn-link text-danger p-0 ms-1 text-decoration-none fw-bold" style="font-size: 0.75rem;" title="Stop Timer">
                        <i class="fa-solid fa-circle-stop fs-6"></i>
                    </button>
                </div>
            `;
            const navbarTimer = document.getElementById('navbar-running-timer');
            if (navbarTimer) {
                if (window.navbarInterval) clearInterval(window.navbarInterval);
                const startTime = new Date(attendance.clock_in_at);
                function updateNav() {
                    const now = new Date();
                    const diffMs = now - startTime;
                    if (diffMs < 0) return;
                    const totalSecs = Math.floor(diffMs / 1000);
                    const hrs = String(Math.floor(totalSecs / 3600)).padStart(2, '0');
                    const mins = String(Math.floor((totalSecs % 3600) / 60)).padStart(2, '0');
                    const secs = String(totalSecs % 60).padStart(2, '0');
                    navbarTimer.textContent = `${hrs}:${mins}:${secs}`;
                }
                updateNav();
                window.navbarInterval = setInterval(updateNav, 1000);
            }
        }
    }

    function removeNavbarTimer() {
        const container = document.getElementById('navbar-active-tracker-container');
        if (container) container.innerHTML = '';
        if (window.navbarInterval) clearInterval(window.navbarInterval);
    }
</script>
@endpush

@endsection
