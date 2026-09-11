@extends('layouts.dashboard')

@section('title', 'Time Logs & Tracker')

@section('content')

@php
    $currentUser = (new \App\Services\CurrentUserService())->getUser();
    $canViewTeam = (new \App\Services\CurrentUserService())->hasPermission('attendance.view');
@endphp

<div class="container-fluid px-0">

    <!-- Time Tracker Bar (Clockify Pro SaaS Aesthetic) -->
    <div class="card border-0 shadow-sm mb-4 bg-white rounded-4 overflow-hidden border">
        <div class="card-body p-3.5">
            <div id="tracker-container">
                @if($activeAttendance)
                    <!-- Active State Form -->
                    <form id="active-tracker-form" onsubmit="handleTrackerSubmit(event, 'switch')" class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                        @csrf
                        <div class="d-flex align-items-center flex-grow-1 gap-3 min-w-0" style="min-width: 280px;">
                            <div class="input-group bg-light rounded-pill px-3 py-1.5 border shadow-2xs flex-grow-1">
                                <span class="input-group-text bg-transparent border-0 text-primary ps-0 pe-2">
                                    <i class="fa-solid fa-bolt"></i>
                                </span>
                                <input type="text" id="task_title_input" name="task_title" class="form-control border-0 shadow-none bg-transparent py-1 fw-semibold text-dark" style="font-size: 0.925rem;" 
                                       placeholder="What are you working on?"
                                       value="{{ $activeAttendance->task?->title }}">
                            </div>
                            <div class="input-group bg-light rounded-pill px-3 py-1.5 border shadow-2xs flex-shrink-0" style="width: 220px;">
                                <span class="input-group-text bg-transparent border-0 text-warning ps-0 pe-2">
                                    <i class="fa-solid fa-folder-closed"></i>
                                </span>
                                <select id="project_id_select" name="project_id" class="form-select border-0 bg-transparent shadow-none fw-semibold text-dark" style="font-size: 0.88rem;" onchange="handleProjectChange(this)">
                                    <option value="">+ Project</option>
                                    @foreach($projects as $project)
                                        <option value="{{ $project->id }}" {{ $activeAttendance->project_id === $project->id ? 'selected' : '' }}>
                                            {{ $project->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="d-flex align-items-center gap-3 ms-auto">
                            <div class="d-flex align-items-center gap-2 text-success px-4 py-1.5 rounded-pill border border-success-subtle shadow-2xs" style="background-color: #ECFDF5;">
                                <span class="spinner-grow spinner-grow-sm text-success live-pulse" style="width: 8px; height: 8px;"></span>
                                <div id="logs-running-timer" class="fs-4 fw-bold font-monospace text-success lh-1" data-start="{{ $activeAttendance->clock_in_at->toIso8601String() }}">
                                    00:00:00
                                </div>
                            </div>
                            <button type="button" onclick="handleStopTimer()" class="btn btn-danger px-4 py-2 fw-bold text-uppercase rounded-pill shadow-sm d-flex align-items-center gap-2" style="font-size: 0.85rem; letter-spacing: 0.5px;">
                                <i class="fa-solid fa-square"></i> Stop
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
                            <div class="rounded-circle bg-warning-subtle text-warning-emphasis d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                                <i class="fa-solid fa-umbrella-beach fs-5 text-warning"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold text-dark mb-0.5">Approved Leave Active ({{ $leaveLabel }})</h6>
                                <p class="text-muted small mb-0 font-monospace" style="font-size: 0.78rem;">
                                    Period: {{ $activeLeave->start_date->format('d M Y') }} &ndash; {{ $activeLeave->end_date->format('d M Y') }} &bull; 
                                    <span class="text-danger fw-semibold">{{ $leaveNote }}</span>
                                </p>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-3 py-2 fw-semibold" style="font-size: 0.8rem;">
                                <i class="fa-solid fa-ban me-1 text-danger"></i> Tracking Disabled
                            </span>
                        </div>
                    </div>
                @else
                    <!-- Idle State Form -->
                    <form id="idle-tracker-form" onsubmit="handleTrackerSubmit(event, 'start')" class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                        @csrf
                        <div class="d-flex align-items-center flex-grow-1 gap-3 min-w-0" style="min-width: 280px;">
                            <div class="input-group bg-light rounded-pill px-3 py-1.5 border shadow-2xs flex-grow-1">
                                <span class="input-group-text bg-transparent border-0 text-muted ps-0 pe-2">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </span>
                                <input type="text" id="task_title_input" name="task_title" class="form-control border-0 shadow-none bg-transparent py-1 fw-semibold text-dark" style="font-size: 0.925rem;" 
                                       placeholder="What are you working on?">
                            </div>
                            <div class="input-group bg-light rounded-pill px-3 py-1.5 border shadow-2xs flex-shrink-0" style="width: 220px;">
                                <span class="input-group-text bg-transparent border-0 text-warning ps-0 pe-2">
                                    <i class="fa-solid fa-folder-closed"></i>
                                </span>
                                <select id="project_id_select" name="project_id" class="form-select border-0 bg-transparent shadow-none fw-semibold text-dark" style="font-size: 0.88rem;">
                                    <option value="">+ Project</option>
                                    @foreach($projects as $project)
                                        <option value="{{ $project->id }}">
                                            {{ $project->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="d-flex align-items-center gap-3 ms-auto">
                            <div class="fs-4 fw-bold text-secondary opacity-75 font-monospace pe-2 lh-1" style="letter-spacing: 0.5px;">
                                00:00:00
                            </div>
                            <button type="submit" class="btn btn-primary px-4 py-2 fw-bold text-uppercase rounded-pill shadow-sm d-flex align-items-center gap-2" style="font-size: 0.85rem; letter-spacing: 0.5px;">
                                <i class="fa-solid fa-play" style="font-size: 0.75rem;"></i> Start
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <!-- Logs Container -->
    <div class="card border-0 shadow-sm bg-white rounded-4 mb-4 border">
        <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-3">
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
                    <button class="nav-link {{ !$isTeamFiltered ? 'active' : '' }} fw-bold d-flex align-items-center gap-2 rounded-pill px-3.5 py-1.5" id="my-logs-tab" data-bs-toggle="tab" data-bs-target="#my-logs" type="button" role="tab">
                        <i class="fa-solid fa-user-clock"></i> My Time Logs
                    </button>
                </li>
                @if($canViewTeam)
                    <li class="nav-item">
                        <button class="nav-link {{ $isTeamFiltered ? 'active' : '' }} fw-bold d-flex align-items-center gap-2 rounded-pill px-3.5 py-1.5" id="team-logs-tab" data-bs-toggle="tab" data-bs-target="#team-logs" type="button" role="tab">
                            <i class="fa-solid fa-users-rectangle"></i> Team Logs & Audits
                        </button>
                    </li>
                @endif
            </ul>

            <div class="d-flex align-items-center gap-3">
                @php
                    $wSecsRem = $wSecs % 60;
                @endphp
                <div class="d-flex align-items-center gap-2 bg-light text-dark px-3 py-1.5 rounded-pill border shadow-2xs font-sans small">
                    <i class="fa-solid fa-calendar-check text-primary"></i>
                    <span class="text-secondary fw-semibold">Week Total:</span>
                    <strong class="font-monospace text-dark fs-6">{{ sprintf('%02d:%02d:%02d', $wHrs, $wMins, $wSecsRem) }}</strong>
                </div>

                @if($canViewTeam)
                    <a href="{{ route('attendance.export-csv', ['employee_id' => request('employee_id'), 'log_date' => request('log_date'), 'start_date' => request('start_date'), 'end_date' => request('end_date')]) }}" 
                       class="btn btn-sm btn-success rounded-pill px-3.5 py-1.5 fw-bold shadow-2xs">
                        <i class="fa-solid fa-file-excel me-1.5"></i> Export CSV
                    </a>
                @endif
            </div>
        </div>
        
        <div class="card-body p-4 bg-light-subtle">
            <div class="tab-content" id="timeLogsTabContent">
                
                <!-- My Grouped Logs Tab -->
                <div class="tab-pane fade {{ !$isTeamFiltered ? 'show active' : '' }}" id="my-logs" role="tabpanel">
                    
                    <div id="logs-groups-wrapper">
                        @forelse($groupedLogs as $dateKey => $logs)
                            @php
                                $cDate = $dateKey !== 'other' ? \Carbon\Carbon::parse($dateKey) : now();
                                $isToday = $cDate->isToday();
                                $isYesterday = $cDate->isYesterday();
                                $seconds = $dailyTotals[$dateKey] ?? 0;
                                $hrs = floor($seconds / 3600);
                                $mins = floor(($seconds % 3600) / 60);
                                $secs = $seconds % 60;
                            @endphp
                            
                            <!-- Standalone Floating Day Card -->
                            <div class="card border shadow-sm bg-white rounded-4 mb-4">
                                <!-- Card Day Header -->
                                <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center rounded-top-4">
                                    <div class="d-flex align-items-center gap-2">
                                        @if($isToday)
                                            <span class="badge bg-primary text-white rounded-pill px-3 py-1 fw-bold text-uppercase shadow-2xs" style="font-size: 0.72rem; letter-spacing: 0.5px;">Today</span>
                                            <span class="fw-bold text-dark fs-6 ms-1">{{ $cDate->format('l, d M Y') }}</span>
                                        @elseif($isYesterday)
                                            <span class="badge bg-secondary text-white rounded-pill px-3 py-1 fw-bold text-uppercase shadow-2xs" style="font-size: 0.72rem; letter-spacing: 0.5px;">Yesterday</span>
                                            <span class="fw-bold text-dark fs-6 ms-1">{{ $cDate->format('l, d M Y') }}</span>
                                        @else
                                            <i class="fa-regular fa-calendar text-primary me-1.5 fs-6"></i>
                                            <span class="fw-bold text-dark fs-6">{{ $cDate->format('l, d M Y') }}</span>
                                        @endif
                                    </div>
                                    <div class="d-flex align-items-center gap-2 text-secondary small">
                                        <span class="text-muted fw-semibold">Day Total:</span>
                                        <span class="badge bg-light text-dark border font-monospace fs-6 px-3 py-1 rounded-pill fw-bold shadow-2xs">
                                            {{ sprintf('%02d:%02d:%02d', $hrs, $mins, $secs) }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Card Rows (Grid Alignment) -->
                                <div class="list-group list-group-flush rounded-bottom-4">
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

                                        <div class="list-group-item py-3 px-4 hover-bg-light transition-all border-0 border-bottom bg-white {{ $loop->last ? 'rounded-bottom-4 border-bottom-0' : '' }}">
                                            <div class="d-flex align-items-center justify-content-between gap-3">
                                                
                                                <!-- Col 1: Task Description -->
                                                <div class="flex-grow-1 min-w-0" style="flex-basis: 35%;">
                                                    @if($log->task && trim($log->task->title) !== '')
                                                        <span class="text-dark fw-bold text-truncate d-block" style="font-size: 0.92rem;" title="{{ $log->task->title }}">
                                                            {{ $log->task->title }}
                                                        </span>
                                                    @else
                                                        <span class="text-muted small d-block" style="font-size: 0.88rem; color: #94A3B8 !important;">
                                                            Add description
                                                        </span>
                                                    @endif
                                                </div>

                                                <!-- Col 2: Project Pill Badge -->
                                                <div class="flex-shrink-0" style="width: 200px;">
                                                    <span class="badge rounded-pill px-3 py-1.5 d-inline-flex align-items-center gap-2 border font-sans text-truncate" 
                                                          style="background-color: {{ $bgColor }}; color: {{ $dotColor }}; border-color: {{ $borderColor }} !important; font-size: 0.8rem; font-weight: 600; max-width: 195px;">
                                                        <span class="rounded-circle d-inline-block flex-shrink-0" style="width: 7px; height: 7px; background-color: {{ $dotColor }};"></span>
                                                        <span class="text-truncate">{{ $projectName }}</span>
                                                    </span>
                                                </div>

                                                <!-- Col 3: Time Range Badge -->
                                                <div class="flex-shrink-0 text-center d-flex align-items-center gap-1.5">
                                                    <span class="text-secondary font-monospace px-3 py-1.5 rounded-pill bg-light border d-inline-flex align-items-center gap-1.5" style="font-size: 0.8rem; letter-spacing: 0.3px;">
                                                        <i class="fa-regular fa-clock text-muted" style="font-size: 0.72rem;"></i>
                                                        {{ $startTime ? $startTime->format('H:i') : '--:--' }} &ndash; {{ $endTime ? $endTime->format('H:i') : '--:--' }}
                                                    </span>
                                                    @if($log->clock_in_latitude && $log->clock_in_longitude)
                                                        <button type="button" 
                                                                onclick="openLocationMap({{ $log->clock_in_latitude }}, {{ $log->clock_in_longitude }}, '{{ addslashes($currentUser->full_name ?? 'My Log') }}', '{{ $log->clock_in_at ? $log->clock_in_at->format('d M Y, h:i A') : '' }}', '{{ $log->clock_in_ip ?: 'N/A' }}')" 
                                                                class="btn btn-sm btn-light border rounded-pill px-2 py-1 text-danger d-inline-flex align-items-center gap-1 shadow-2xs hover-scale" 
                                                                style="font-size: 0.75rem;" 
                                                                title="View GPS Clock-in Location">
                                                            <i class="fa-solid fa-location-dot"></i>
                                                            <span class="d-none d-xl-inline fw-semibold text-secondary">GPS</span>
                                                        </button>
                                                    @endif
                                                </div>

                                                <!-- Col 4: Duration -->
                                                <div class="fw-bold text-dark font-monospace flex-shrink-0 text-end pe-2" style="min-width: 90px; font-size: 0.98rem; letter-spacing: 0.3px;">
                                                    {{ $durFormatted }}
                                                </div>

                                                <!-- Col 5: Controls (Play & Actions Menu) -->
                                                <div class="flex-shrink-0 d-flex align-items-center gap-2">
                                                    <!-- Play Button -->
                                                    <button type="button" 
                                                            onclick="handleQuickRestart('{{ $log->project_id }}', '{{ addslashes($log->task?->title ?? '') }}', '{{ $log->task_id }}')" 
                                                            class="btn btn-sm btn-light border rounded-circle text-primary p-0 d-inline-flex align-items-center justify-content-center shadow-2xs hover-scale" 
                                                            style="width: 34px; height: 34px;"
                                                            title="Continue this timer">
                                                        <i class="fa-solid fa-play" style="font-size: 0.75rem;"></i>
                                                    </button>

                                                    <!-- Actions Menu -->
                                                    <div class="dropdown position-relative">
                                                        <button class="btn btn-sm btn-light border rounded-circle text-muted p-0 d-inline-flex align-items-center justify-content-center shadow-2xs hover-scale" 
                                                                style="width: 34px; height: 34px;"
                                                                type="button" 
                                                                data-bs-toggle="dropdown" 
                                                                data-bs-display="static"
                                                                aria-expanded="false"
                                                                title="More actions">
                                                            <i class="fa-solid fa-ellipsis-vertical" style="font-size: 0.85rem;"></i>
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-end shadow-lg border rounded-3 py-1.5 px-1 mt-1" style="min-width: 170px; font-size: 0.84rem; z-index: 1060; border-color: #E2E8F0 !important;">
                                                            @if($log->clock_in_latitude && $log->clock_in_longitude)
                                                                <li>
                                                                    <button class="dropdown-item py-2 px-3 rounded-2 fw-medium d-flex align-items-center gap-2 text-danger" type="button" 
                                                                            onclick="openLocationMap({{ $log->clock_in_latitude }}, {{ $log->clock_in_longitude }}, '{{ addslashes($currentUser->full_name ?? 'My Log') }}', '{{ $log->clock_in_at ? $log->clock_in_at->format('d M Y, h:i A') : '' }}', '{{ $log->clock_in_ip ?: 'N/A' }}')">
                                                                        <i class="fa-solid fa-location-dot text-danger" style="font-size: 0.85rem;"></i> View GPS Location
                                                                    </button>
                                                                </li>
                                                                <li><hr class="dropdown-divider my-1 opacity-10"></li>
                                                            @endif
                                                            <li>
                                                                <button class="dropdown-item py-2 px-3 rounded-2 fw-medium d-flex align-items-center gap-2" type="button" onclick="openEditLogModal('{{ $log->id }}', '{{ addslashes($log->task?->title ?? '') }}', '{{ $log->project_id }}')">
                                                                    <i class="fa-solid fa-pen-to-square text-primary" style="font-size: 0.85rem;"></i> Edit entry
                                                                </button>
                                                            </li>
                                                            <li><hr class="dropdown-divider my-1 opacity-10"></li>
                                                            <li>
                                                                <form action="{{ route('attendance.destroy', $log->id) }}" method="POST" onsubmit="return confirm('Delete this time entry?');" class="m-0">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="dropdown-item py-2 px-3 rounded-2 text-danger fw-medium d-flex align-items-center gap-2">
                                                                        <i class="fa-solid fa-trash-can text-danger" style="font-size: 0.85rem;"></i> Delete entry
                                                                    </button>
                                                                </form>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>


                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-5 text-muted bg-white rounded-4 border shadow-sm">
                                <i class="fa-solid fa-stopwatch fs-1 opacity-40 mb-3 d-block text-primary"></i>
                                <h6 class="fw-bold text-dark mb-1">No time entries recorded yet this week</h6>
                                <p class="text-secondary small mb-0">Use the live timer bar at the top to start tracking your activities.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Team Logs Tab -->
                @if($canViewTeam)
                    <div class="tab-pane fade p-4 {{ request()->has('employee_id') || request()->has('log_date') ? 'show active' : '' }}" id="team-logs" role="tabpanel">
                        <!-- Admin / HR Filter Bar -->
                        <form method="GET" action="{{ route('attendance.index') }}" class="row g-3 mb-4 bg-white p-3.5 rounded-4 shadow-2xs border align-items-center">
                            <div class="col-md-5">
                                <label class="form-label mb-1.5 fw-bold text-dark small">
                                    <i class="fa-solid fa-user-gear me-1 text-primary"></i> Filter by Team Member
                                </label>
                                <select name="employee_id" class="form-select form-select-sm border-gray rounded-3" onchange="this.form.submit()">
                                    <option value="">All Team Members (Whole Organization)</option>
                                    @foreach($organizationUsers as $emp)
                                        <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>
                                            {{ $emp->full_name }} ({{ $emp->display_designation }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label mb-1.5 fw-bold text-dark small">
                                    <i class="fa-solid fa-calendar me-1 text-primary"></i> Filter by Date
                                </label>
                                <input type="date" name="log_date" class="form-control form-control-sm border-gray rounded-3" value="{{ request('log_date') }}" onchange="this.form.submit()">
                            </div>
                            <div class="col-md-3 d-flex align-items-end gap-2 pt-md-4">
                                <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3.5 py-1.5 fw-bold shadow-2xs">
                                    <i class="fa-solid fa-filter me-1"></i> Filter
                                </button>
                                <a href="{{ route('attendance.export-csv', ['employee_id' => request('employee_id'), 'log_date' => request('log_date'), 'start_date' => request('start_date'), 'end_date' => request('end_date')]) }}" 
                                   class="btn btn-sm btn-success rounded-pill px-3 py-1.5 fw-bold shadow-2xs">
                                    <i class="fa-solid fa-file-csv me-1"></i> CSV
                                </a>
                                @if(request()->filled('employee_id') || request()->filled('log_date'))
                                    <a href="{{ route('attendance.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1.5 fw-semibold">
                                        Reset
                                    </a>
                                @endif
                            </div>
                        </form>

                        <!-- Team Members List Table -->
                        <div class="card border-0 shadow-sm rounded-4 overflow-hidden border">
                            <div class="card-header bg-white py-3.5 px-4 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
                                <div>
                                    <h6 class="fw-bold text-dark mb-0 fs-6">
                                        <i class="fa-solid fa-users text-primary me-2"></i> Team Members & Weekly Time Summary
                                    </h6>
                                    <small class="text-muted font-monospace">Standard 5-Day Work Week (Mon - Fri &bull; 40.0 Hours Target)</small>
                                </div>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill font-monospace px-3 py-1.5 fw-bold">
                                    <i class="fa-solid fa-briefcase me-1 text-primary"></i> 5-Day Work Week (40.0 Hrs)
                                </span>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0" style="font-size: 0.875rem;">
                                    <thead class="table-light text-uppercase text-muted fw-bold" style="font-size: 0.725rem; letter-spacing: 0.5px;">
                                        <tr>
                                            <th class="ps-4 py-3">Team Member</th>
                                            <th class="py-3">Role / Department</th>
                                            <th class="py-3 text-center">This Week Worked</th>
                                            <th class="py-3 text-center">Active Days</th>
                                            <th class="py-3" style="width: 22%;">40-Hour Quota Progress</th>
                                            <th class="pe-4 py-3 text-end">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($teamWeeklyStats as $stat)
                                            @php
                                                $emp = $stat['user'];
                                            @endphp
                                            <tr>
                                                <td class="ps-4 py-3.5">
                                                    <div class="d-flex align-items-center gap-3">
                                                        @if($emp->avatar)
                                                            <img src="{{ asset('storage/' . $emp->avatar) }}" 
                                                                 alt="{{ $emp->full_name }}" 
                                                                 class="rounded-circle img-thumbnail shadow-2xs" 
                                                                 style="width: 42px; height: 42px; object-fit: cover;">
                                                        @else
                                                            <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold shadow-2xs flex-shrink-0"
                                                                 style="width: 42px; height: 42px; font-size: 1rem; background: linear-gradient(135deg, #4F46E5 0%, #7C3AED 100%);">
                                                                {{ strtoupper(substr($emp->first_name, 0, 1)) }}
                                                            </div>
                                                        @endif
                                                        <div>
                                                            <div class="fw-bold text-dark fs-6 mb-0">
                                                                <a href="{{ route('users.show', $emp->id) }}" class="text-dark text-decoration-none hover-primary">
                                                                    {{ $emp->full_name }}
                                                                </a>
                                                            </div>
                                                            <div class="text-muted small font-monospace" style="font-size: 0.76rem;">{{ $emp->email }}</div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="py-3.5">
                                                    <div class="fw-semibold text-dark small mb-0">{{ $emp->display_designation }}</div>
                                                    <div class="text-muted small" style="font-size: 0.76rem;">{{ $emp->department?->name ?? 'General' }}</div>
                                                </td>
                                                <td class="py-3.5 text-center">
                                                    <span class="badge {{ $stat['percent'] >= 100 ? 'bg-success-subtle text-success border-success-subtle' : ($stat['percent'] > 0 ? 'bg-primary-subtle text-primary border-primary-subtle' : 'bg-light text-muted border') }} border px-3 py-1.5 rounded-pill font-monospace fw-bold fs-7 shadow-2xs">
                                                        <i class="fa-solid fa-clock me-1"></i>{{ $stat['hours'] }} / 40.0 Hrs
                                                    </span>
                                                </td>
                                                <td class="py-3.5 text-center font-monospace">
                                                    <span class="badge bg-light text-dark border px-2.5 py-1.5 rounded-pill fw-semibold" style="font-size: 0.75rem;">
                                                        <i class="fa-regular fa-calendar-check me-1 text-success"></i>{{ $stat['days_worked'] }} / 5 Days
                                                    </span>
                                                </td>
                                                <td class="py-3.5">
                                                    <div class="d-flex align-items-center justify-content-between mb-1" style="font-size: 0.75rem;">
                                                        <span class="fw-bold font-monospace text-muted">{{ $stat['percent'] }}%</span>
                                                        <span class="text-muted font-monospace">{{ $stat['hours'] }} hrs</span>
                                                    </div>
                                                    <div class="progress rounded-pill" style="height: 7px; background-color: #E2E8F0;">
                                                        <div class="progress-bar rounded-pill {{ $stat['percent'] >= 100 ? 'bg-success' : 'bg-primary' }}" 
                                                             style="width: {{ $stat['percent'] }}%;"></div>
                                                    </div>
                                                </td>
                                                <td class="pe-4 py-3.5 text-end">
                                                    <a href="{{ route('attendance.user-logs', $emp->id) }}" class="btn btn-sm btn-primary rounded-pill px-3.5 py-1.5 fw-bold shadow-2xs d-inline-flex align-items-center gap-1.5 hover-scale">
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

@push('scripts')
<script>
    let currentEditingLogId = null;
    let leafletMap = null;
    let leafletMarker = null;

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
        
        let projectOptions = '<option value="">+ Project</option>';
        @foreach($projects as $project)
            projectOptions += '<option value="{{ $project->id }}" ' + (currentProjId == {{ $project->id }} ? 'selected' : '') + '>{{ addslashes($project->name) }}</option>';
        @endforeach

        container.innerHTML = `
            <form id="active-tracker-form" onsubmit="handleTrackerSubmit(event, 'switch')" class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                @csrf
                <div class="d-flex align-items-center flex-grow-1 gap-3 min-w-0" style="min-width: 280px;">
                    <div class="input-group bg-light rounded-pill px-3 py-1.5 border shadow-2xs flex-grow-1">
                        <span class="input-group-text bg-transparent border-0 text-primary ps-0 pe-2">
                            <i class="fa-solid fa-bolt"></i>
                        </span>
                        <input type="text" id="task_title_input" name="task_title" class="form-control border-0 shadow-none bg-transparent py-1 fw-semibold text-dark" style="font-size: 0.925rem;" 
                               placeholder="What are you working on?"
                               value="${taskTitle}">
                    </div>
                    <div class="input-group bg-light rounded-pill px-3 py-1.5 border shadow-2xs flex-shrink-0" style="width: 220px;">
                        <span class="input-group-text bg-transparent border-0 text-warning ps-0 pe-2">
                            <i class="fa-solid fa-folder-closed"></i>
                        </span>
                        <select id="project_id_select" name="project_id" class="form-select border-0 bg-transparent shadow-none fw-semibold text-dark" style="font-size: 0.88rem;" onchange="handleProjectChange(this)">
                            ${projectOptions}
                        </select>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-3 ms-auto">
                    <div class="d-flex align-items-center gap-2 text-success px-4 py-1.5 rounded-pill border border-success-subtle shadow-2xs" style="background-color: #ECFDF5;">
                        <span class="spinner-grow spinner-grow-sm text-success live-pulse" style="width: 8px; height: 8px;"></span>
                        <div id="logs-running-timer" class="fs-4 fw-bold font-monospace text-success lh-1" data-start="${clockInAt}">
                            00:00:00
                        </div>
                    </div>
                    <button type="button" onclick="handleStopTimer()" class="btn btn-danger px-4 py-2 fw-bold text-uppercase rounded-pill shadow-sm d-flex align-items-center gap-2" style="font-size: 0.85rem; letter-spacing: 0.5px;">
                        <i class="fa-solid fa-square"></i> Stop
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
                    <div class="input-group bg-light rounded-pill px-3 py-1.5 border shadow-2xs flex-grow-1">
                        <span class="input-group-text bg-transparent border-0 text-muted ps-0 pe-2">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </span>
                        <input type="text" id="task_title_input" name="task_title" class="form-control border-0 shadow-none bg-transparent py-1 fw-semibold text-dark" style="font-size: 0.925rem;" 
                               placeholder="What are you working on?">
                    </div>
                    <div class="input-group bg-light rounded-pill px-3 py-1.5 border shadow-2xs flex-shrink-0" style="width: 220px;">
                        <span class="input-group-text bg-transparent border-0 text-warning ps-0 pe-2">
                            <i class="fa-solid fa-folder-closed"></i>
                        </span>
                        <select id="project_id_select" name="project_id" class="form-select border-0 bg-transparent shadow-none fw-semibold text-dark" style="font-size: 0.88rem;">
                            <option value="">+ Project</option>
                            @foreach($projects as $project)
                                <option value="{{ $project->id }}">
                                    {{ $project->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-3 ms-auto">
                    <div class="fs-4 fw-bold text-secondary opacity-75 font-monospace pe-2 lh-1" style="letter-spacing: 0.5px;">
                        00:00:00
                    </div>
                    <button type="submit" class="btn btn-primary px-4 py-2 fw-bold text-uppercase rounded-pill shadow-sm d-flex align-items-center gap-2" style="font-size: 0.85rem; letter-spacing: 0.5px;">
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
