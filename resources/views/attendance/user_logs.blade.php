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
    <div class="mb-3">
        <div class="card shadow-2xs border bg-white rounded-3 p-3">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <h5 class="fw-bold text-dark mb-0.5">{{ $user->full_name }}</h5>
                    <div class="text-muted small" style="font-size: 0.8rem;">
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
                <div class="d-flex align-items-center gap-2">
                    <div class="border rounded-2 px-2.5 py-1 bg-light text-center">
                        <div class="text-muted small text-uppercase font-monospace" style="font-size: 0.65rem;">This Week</div>
                        <div class="fw-bold text-dark fs-6 font-monospace">{{ $currentWeekHours >= 1.0 ? $currentWeekHours . ' Hrs' : ($currentWeekFormatted ?? '00:00:00') }} <span class="text-muted small fw-normal" style="font-size: 0.72rem;">/ 40.0 Hrs</span></div>
                    </div>
                    <div class="border rounded-2 px-2.5 py-1 bg-light text-center">
                        <div class="text-muted small text-uppercase font-monospace" style="font-size: 0.65rem;">Active Days</div>
                        <div class="fw-bold text-dark fs-6">{{ $cwDaysWorked }} / 5 <span class="text-muted small fw-normal" style="font-size: 0.72rem;">Days</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SECTION 2: ATTENDANCE TIME LOGS BREAKDOWN -->
    <div>
        <div class="card border-0 shadow-2xs rounded-3 bg-white overflow-hidden border">
            
            <!-- Tab Header -->
            <div class="card-header bg-white py-2 px-3 border-bottom">
                <ul class="nav nav-pills card-header-pills gap-1.5" id="userLogsTabs" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active fw-semibold rounded-pill px-3 py-1 small" id="week-view-tab" data-bs-toggle="tab" data-bs-target="#week-view" type="button" role="tab">
                            <i class="fa-solid fa-calendar-week me-1.5"></i>Week Wise Logs ({{ count($weeklyStats) }})
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link fw-semibold rounded-pill px-3 py-1 small" id="month-view-tab" data-bs-toggle="tab" data-bs-target="#month-view" type="button" role="tab">
                            <i class="fa-solid fa-calendar-days me-1.5"></i>Month Wise Logs ({{ count($monthlyStats) }})
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link fw-semibold rounded-pill px-3 py-1 small" id="user-screenshots-tab" data-bs-toggle="tab" data-bs-target="#user-screenshots-view" type="button" role="tab">
                            <i class="fa-solid fa-camera me-1.5"></i>Screenshots & Activity ({{ array_sum($weekScreenshotsCount ?? []) }})
                        </button>
                    </li>
                </ul>
            </div>

            <div class="card-body p-3 bg-light-subtle">
                <div class="tab-content" id="userLogsTabContent">

                    <!-- 1. WEEK WISE LOGS TAB -->
                    <div class="tab-pane fade show active" id="week-view" role="tabpanel">
                        @forelse($weeklyStats as $wStat)
                            <div class="card border shadow-2xs rounded-3 mb-3 overflow-hidden bg-white">
                                <!-- Week Header -->
                                <div class="card-header bg-white py-2 px-3 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
                                    <div>
                                        <h6 class="fw-bold text-dark mb-0 fs-6">{{ $wStat['label'] }}</h6>
                                        <small class="text-muted" style="font-size: 0.72rem;">{{ $wStat['logs_count'] }} Session{{ $wStat['logs_count'] == 1 ? '' : 's' }} &bull; {{ $wStat['days_worked'] }} / 5 Days Worked</small>
                                    </div>
                                    <div>
                                        <span class="badge {{ $wStat['percent'] >= 100 ? 'bg-success text-white' : 'bg-primary text-white' }} px-2.5 py-1 rounded-pill font-monospace fw-bold" style="font-size: 0.78rem;">
                                            Total: {{ $wStat['hours'] >= 1.0 ? $wStat['hours'] . ' Hrs' : $wStat['formatted_total'] }} / 40.0 Hrs
                                        </span>
                                    </div>
                                </div>

                                <!-- Week Sessions Table -->
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0" style="font-size: 0.84rem;">
                                        <thead class="table-light text-uppercase text-muted fw-bold" style="font-size: 0.72rem; letter-spacing: 0.4px;">
                                            <tr>
                                                <th class="ps-4 py-2.5">Date & Day</th>
                                                <th class="py-2.5">Task Description</th>
                                                <th class="py-2.5">Project</th>
                                                <th class="py-2.5 text-center">Clock In</th>
                                                <th class="py-2.5 text-center">Clock Out</th>
                                                <th class="pe-4 py-2.5 text-end">Duration</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($wStat['logs'] as $logItem)
                                                <tr>
                                                    <td class="ps-4 py-3">
                                                        <span class="fw-semibold text-body font-monospace me-1.5" style="font-size: 0.88rem;">{{ $logItem->clock_in_at->format('M d, Y') }}</span>
                                                        <span class="badge bg-body-tertiary text-secondary border font-monospace" style="font-size: 0.7rem;">{{ $logItem->clock_in_at->format('D') }}</span>
                                                    </td>
                                                    <td class="py-3">
                                                        <span class="fw-semibold text-body" style="font-size: 0.88rem;">{{ $logItem->task?->title ?: 'General Work' }}</span>
                                                    </td>
                                                    <td class="py-3">
                                                        @if($logItem->project)
                                                            @php
                                                                $cSeed = md5($logItem->project->name);
                                                                $hue = hexdec(substr($cSeed, 0, 2)) % 360;
                                                            @endphp
                                                            <span class="badge rounded-pill font-sans border px-2.5 py-1" style="background-color: hsl({{ $hue }}, 70%, 95%); color: hsl({{ $hue }}, 70%, 35%); border-color: hsl({{ $hue }}, 70%, 85%) !important; font-size: 0.75rem; font-weight: 600;">
                                                                📁 {{ $logItem->project->name }}
                                                            </span>
                                                        @else
                                                            <span class="text-muted small">&ndash;</span>
                                                        @endif
                                                    </td>
                                                    <td class="py-3 text-center font-monospace">
                                                        <span class="badge bg-body-tertiary text-secondary border px-2.5 py-1 fw-semibold" style="font-size: 0.78rem;">
                                                            <i class="fa-regular fa-clock me-1.5 text-primary"></i>{{ $logItem->clock_in_at->format('h:i:s A') }}
                                                        </span>
                                                    </td>
                                                    <td class="py-3 text-center font-monospace">
                                                        <span class="badge bg-body-tertiary text-body border px-2.5 py-1 fw-semibold" style="font-size: 0.78rem;">
                                                            <i class="fa-solid fa-flag-checkered me-1.5 text-danger"></i>{{ $logItem->clock_out_at ? $logItem->clock_out_at->format('h:i:s A') : 'Active' }}
                                                        </span>
                                                    </td>
                                                    <td class="pe-4 py-3 text-end fw-bold font-monospace">
                                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1 rounded-pill font-monospace fw-bold" style="font-size: 0.8rem;">
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
                            <div class="card border shadow-2xs rounded-3 mb-3 overflow-hidden bg-white">
                                <!-- Month Header -->
                                <div class="card-header bg-white py-2 px-3 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
                                    <div>
                                        <h6 class="fw-bold text-dark mb-0 fs-6">{{ $mStat['month'] }}</h6>
                                        <small class="text-muted" style="font-size: 0.72rem;">{{ $mStat['days_worked'] }} Active Days &bull; {{ $mStat['logs_count'] }} Total Sessions</small>
                                    </div>
                                    <div>
                                        <span class="badge bg-success text-white px-2.5 py-1 rounded-pill font-monospace fw-bold" style="font-size: 0.78rem;">
                                            Total: {{ $mStat['hours'] >= 1.0 ? $mStat['hours'] . ' Hrs' : $mStat['formatted_total'] }} / 160.0 Hrs
                                        </span>
                                    </div>
                                </div>

                                <!-- Month Sessions Table -->
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0" style="font-size: 0.84rem;">
                                        <thead class="table-light text-uppercase text-muted fw-bold" style="font-size: 0.7rem; letter-spacing: 0.3px;">
                                            <tr>
                                                <th class="ps-3.5 py-2">Date & Day</th>
                                                <th class="py-2">Task Description</th>
                                                <th class="py-2">Project</th>
                                                <th class="py-2 text-center">Clock In</th>
                                                <th class="py-2 text-center">Clock Out</th>
                                                <th class="pe-3.5 py-2 text-end">Duration</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($mStat['logs'] as $logItem)
                                                <tr>
                                                    <td class="ps-3.5 py-2">
                                                        <span class="fw-semibold text-dark font-monospace me-1">{{ $logItem->clock_in_at->format('M d, Y') }}</span>
                                                        <span class="badge bg-light text-muted border font-monospace" style="font-size: 0.68rem;">{{ $logItem->clock_in_at->format('D') }}</span>
                                                    </td>
                                                    <td class="py-2">
                                                        <span class="fw-semibold text-dark">{{ $logItem->task?->title ?: 'General Work' }}</span>
                                                    </td>
                                                    <td class="py-2">
                                                        @if($logItem->project)
                                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill font-monospace px-2.5 py-0.5" style="font-size: 0.72rem;">
                                                                📁 {{ $logItem->project->name }}
                                                            </span>
                                                        @else
                                                            <span class="text-muted small">-</span>
                                                        @endif
                                                    </td>
                                                    <td class="py-2 text-center font-monospace">
                                                        <span class="badge bg-light text-secondary border px-2 py-0.5 fw-semibold" style="font-size: 0.72rem;">
                                                            <i class="fa-regular fa-clock me-1 text-success"></i>{{ $logItem->clock_in_at->format('h:i:s A') }}
                                                        </span>
                                                    </td>
                                                    <td class="py-2 text-center font-monospace">
                                                        <span class="badge bg-light text-dark border px-2 py-0.5 fw-semibold" style="font-size: 0.72rem;">
                                                            <i class="fa-solid fa-flag-checkered me-1 text-danger"></i>{{ $logItem->clock_out_at ? $logItem->clock_out_at->format('h:i:s A') : 'Active' }}
                                                        </span>
                                                    </td>
                                                    <td class="pe-3.5 py-2 text-end fw-bold font-monospace">
                                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-0.5 rounded-pill font-monospace fw-bold" style="font-size: 0.75rem;">
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
                            <div class="text-center py-4 bg-white rounded-3 border p-3 shadow-2xs">
                                <i class="fa-solid fa-calendar-xmark fs-2 text-secondary opacity-50 mb-2 d-block"></i>
                                <h6 class="fw-bold text-dark mb-1">No Monthly Logs Recorded</h6>
                                <p class="text-muted small mb-0">No time tracking sessions recorded for this employee yet.</p>
                            </div>
                        @endforelse
                    </div>

                    <!-- 3. SCREENSHOTS & TIMELINE TAB -->
                    <div class="tab-pane fade" id="user-screenshots-view" role="tabpanel">
                        
                        <!-- Day Selector Bar (Mon - Sun of Current Week) -->
                        <div class="card border shadow-2xs rounded-3 mb-2.5 bg-white overflow-hidden">
                            <div class="card-header bg-white py-1.5 px-3 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
                                <div>
                                    <h6 class="fw-bold text-dark mb-0 fs-6">
                                        <i class="fa-regular fa-calendar-days text-primary me-1.5"></i> Current Week Daily Screenshots
                                    </h6>
                                    <small class="text-muted font-monospace" style="font-size: 0.72rem;">
                                        Week: {{ $currentWeekDays[0]['formatted'] }} &ndash; {{ $currentWeekDays[6]['formatted'] }} {{ now()->format('Y') }} &bull; 
                                        <span>Auto-deletes after 30 days</span>
                                    </small>
                                </div>
                                <span class="badge bg-light text-muted border rounded-pill font-monospace px-2.5 py-0.5" style="font-size: 0.68rem;">
                                    <i class="fa-solid fa-arrows-rotate text-primary me-1"></i> 2-Minute Intervals
                                </span>
                            </div>

                            <div class="card-body p-2">
                                <div class="d-flex gap-1.5 overflow-x-auto pb-0.5" id="user-week-days-bar">
                                    @foreach($currentWeekDays as $day)
                                        @php
                                            $isSelected = ($selectedScreenshotDate === $day['date']);
                                            $count = $weekScreenshotsCount[$day['date']] ?? 0;
                                        @endphp
                                        <button type="button" 
                                                class="btn flex-fill text-center py-1.5 px-2 rounded-2 border transition-all user-day-btn {{ $isSelected ? 'btn-primary text-white shadow-xs border-primary active-day' : 'btn-light text-dark bg-light' }}"
                                                data-date="{{ $day['date'] }}"
                                                data-day-name="{{ $day['day_name'] }}"
                                                data-formatted="{{ $day['formatted'] }}"
                                                onclick="handleUserScreenshotDayClick('{{ $day['date'] }}', this)">
                                            <div class="d-flex align-items-center justify-content-between gap-1 mb-0.5">
                                                <span class="fw-bold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.3px;">{{ $day['short_day'] }}</span>
                                                @if($day['is_today'])
                                                    <span class="badge {{ $isSelected ? 'bg-white text-primary' : 'bg-primary text-white' }} rounded-pill" style="font-size: 0.6rem; padding: 1px 4px;">Today</span>
                                                @endif
                                            </div>
                                            <div class="fw-bold mb-0.5 font-monospace {{ $isSelected ? 'text-white' : 'text-dark' }}" style="font-size: 0.85rem;">{{ $day['formatted'] }}</div>
                                            <div>
                                                <span class="badge {{ $isSelected ? 'bg-white bg-opacity-25 text-white' : ($count > 0 ? 'bg-primary-subtle text-primary border border-primary-subtle' : 'bg-secondary-subtle text-secondary') }} rounded-pill font-monospace" style="font-size: 0.68rem; padding: 2px 6px;">
                                                    {{ $count }} shot{{ $count == 1 ? '' : 's' }}
                                                </span>
                                            </div>
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- Gallery Card -->
                        <div class="card border shadow-2xs rounded-3 mb-3 bg-white overflow-hidden">
                            <div class="card-header bg-white py-1.5 px-3 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-primary text-white rounded-pill px-2.5 py-0.5 font-monospace fw-bold" style="font-size: 0.78rem;" id="user-gallery-active-day-badge">
                                        {{ \Carbon\Carbon::parse($selectedScreenshotDate)->format('l, d M Y') }}
                                    </span>
                                    <span class="text-muted small" style="font-size: 0.75rem;" id="user-gallery-shots-count-label">
                                        {{ count($initialScreenshots) }} screenshot{{ count($initialScreenshots) == 1 ? '' : 's' }} recorded
                                    </span>
                                </div>
                                <button type="button" onclick="refreshUserScreenshots()" class="btn btn-sm btn-light border rounded-pill px-2.5 py-0.5 text-secondary fw-semibold d-flex align-items-center gap-1 shadow-2xs hover-scale" style="font-size: 0.75rem;">
                                    <i class="fa-solid fa-rotate text-primary"></i> <span>Refresh</span>
                                </button>
                            </div>

                            <div class="card-body p-3">
                                <div id="user-screenshots-grid" class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-5 row-cols-xl-6 g-2.5">
                                    @forelse($initialScreenshots as $idx => $shot)
                                        <div class="col" id="user-shot-card-{{ $shot->id }}">
                                            <div class="card h-100 border rounded-2 overflow-hidden shadow-2xs bg-white hover-shadow transition-all position-relative">
                                                <div class="position-relative overflow-hidden bg-dark ratio ratio-16x9 cursor-pointer" 
                                                     onclick="openUserScreenshotLightbox({{ $idx }})" 
                                                     style="cursor: pointer;">
                                                    <img src="{{ $shot->url }}" 
                                                         alt="Screenshot at {{ $shot->captured_at ? $shot->captured_at->format('h:i A') : '' }}" 
                                                         class="card-img-top object-fit-cover transition-all" 
                                                         loading="lazy">
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

                                                <div class="card-body p-2 d-flex flex-column justify-content-between">
                                                    <div class="mb-1.5">
                                                        <div class="d-flex align-items-center justify-content-between gap-1 mb-0.5">
                                                            <span class="badge bg-light text-dark border rounded-pill px-1.5 py-0.5 text-truncate" style="max-width: 110px; font-size: 0.68rem;">
                                                                📁 {{ $shot->project?->name ?? 'Misc' }}
                                                            </span>
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
                                        <div class="col-12 py-5 text-center text-muted" id="user-gallery-empty-state">
                                            <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center p-4 mb-3 shadow-2xs border">
                                                <i class="fa-solid fa-camera-rotate fs-1 text-primary opacity-50"></i>
                                            </div>
                                            <h6 class="fw-bold text-dark mb-1">No Screenshots Recorded for This Day</h6>
                                            <p class="text-secondary small mb-0 max-w-md mx-auto" style="max-width: 420px;">
                                                Screenshots are captured automatically every 2 minutes while this employee is tracking time.
                                            </p>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                    </div>

                </div>
            </div>
        </div>
    </div>

</div>

<!-- Screenshot Lightbox Modal for User Logs -->
<div class="modal fade" id="userScreenshotLightboxModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 shadow-lg bg-dark text-white rounded-4 overflow-hidden">
            <div class="modal-header border-secondary border-opacity-25 py-2.5 px-4 bg-black bg-opacity-50 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-primary text-white font-monospace fs-6" id="user-lightbox-time-badge">--:--</span>
                    <span class="text-white fw-bold small ms-1" id="user-lightbox-project-badge">Project</span>
                    <span class="text-muted small mx-1">&bull;</span>
                    <span class="text-white-50 small" id="user-lightbox-task-title">Task</span>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <span class="badge bg-light bg-opacity-10 text-light font-monospace small px-3 py-1.5 rounded-pill" id="user-lightbox-counter-badge">1 / 1</span>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            <div class="modal-body p-0 position-relative d-flex align-items-center justify-content-center bg-black" style="min-height: 480px; max-height: 78vh;">
                <button type="button" onclick="navigateUserLightbox(-1)" class="btn btn-dark bg-opacity-75 text-white rounded-circle position-absolute start-0 ms-3 d-flex align-items-center justify-content-center shadow-lg z-3 border border-secondary" style="width: 48px; height: 48px;">
                    <i class="fa-solid fa-chevron-left fs-5"></i>
                </button>
                <div class="w-100 h-100 d-flex align-items-center justify-content-center p-2">
                    <img id="user-lightbox-main-img" src="" alt="Screenshot" class="img-fluid rounded-2 shadow" style="max-height: 74vh; max-width: 100%; object-fit: contain;">
                </div>
                <button type="button" onclick="navigateUserLightbox(1)" class="btn btn-dark bg-opacity-75 text-white rounded-circle position-absolute end-0 me-3 d-flex align-items-center justify-content-center shadow-lg z-3 border border-secondary" style="width: 48px; height: 48px;">
                    <i class="fa-solid fa-chevron-right fs-5"></i>
                </button>
            </div>
            <div class="modal-footer border-secondary border-opacity-25 py-2.5 px-4 bg-black bg-opacity-50 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="text-white-50 small font-monospace" id="user-lightbox-timestamp-full">
                    <i class="fa-regular fa-clock me-1 text-warning"></i> Captured at: --
                </div>
                <a id="user-lightbox-download-link" href="#" target="_blank" download class="btn btn-sm btn-outline-light rounded-pill px-3 py-1 font-monospace" style="font-size: 0.78rem;">
                    <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Open Original
                </a>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let userScreenshotsList = @json($initialScreenshotsFormatted ?? []);

    let userActiveDate = "{{ $selectedScreenshotDate }}";
    let userActiveLightboxIdx = 0;
    const targetUserId = "{{ $user->id }}";

    function handleUserScreenshotDayClick(dateStr, btnEl) {
        userActiveDate = dateStr;

        document.querySelectorAll('.user-day-btn').forEach(b => {
            b.className = 'btn flex-fill text-center py-2.5 px-3 rounded-3 border transition-all user-day-btn btn-light text-dark bg-light';
            const f = b.querySelector('.fs-6');
            if (f) f.className = 'fw-bold fs-6 mb-1 font-monospace text-dark';
        });

        if (btnEl) {
            btnEl.className = 'btn flex-fill text-center py-2.5 px-3 rounded-3 border transition-all user-day-btn btn-primary text-white shadow-sm border-primary active-day';
            const f = btnEl.querySelector('.fs-6');
            if (f) f.className = 'fw-bold fs-6 mb-1 font-monospace text-white';

            const dayName = btnEl.getAttribute('data-day-name') || '';
            const dayFmt = btnEl.getAttribute('data-formatted') || '';
            const dayBadge = document.getElementById('user-gallery-active-day-badge');
            if (dayBadge) dayBadge.textContent = `${dayName}, ${dayFmt} {{ now()->format('Y') }}`;
        }

        fetchUserScreenshots(dateStr);
    }

    function refreshUserScreenshots() {
        fetchUserScreenshots(userActiveDate);
    }

    function fetchUserScreenshots(dateStr) {
        const container = document.getElementById('user-screenshots-grid');
        if (container) {
            container.innerHTML = `
                <div class="col-12 text-center py-5">
                    <div class="spinner-border text-primary mb-2" role="status"></div>
                    <div class="text-muted small">Loading screenshots...</div>
                </div>
            `;
        }

        fetch(`{{ route('attendance.screenshots.get') }}?user_id=${targetUserId}&date=${dateStr}`, {
            headers: { 'Accept': 'application/json' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                userScreenshotsList = data.screenshots || [];
                renderUserGrid(userScreenshotsList);
                const countLabel = document.getElementById('user-gallery-shots-count-label');
                if (countLabel) {
                    countLabel.textContent = `${userScreenshotsList.length} screenshot${userScreenshotsList.length === 1 ? '' : 's'} recorded`;
                }
            }
        })
        .catch(err => {
            console.error(err);
            if (container) {
                container.innerHTML = `<div class="col-12 text-center py-5 text-danger"><p class="small mb-0">Failed to load screenshots.</p></div>`;
            }
        });
    }

    function renderUserGrid(shots) {
        const container = document.getElementById('user-screenshots-grid');
        if (!container) return;

        if (!shots || shots.length === 0) {
            container.innerHTML = `
                <div class="col-12 py-5 text-center text-muted" id="user-gallery-empty-state">
                    <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center p-4 mb-3 shadow-2xs border">
                        <i class="fa-solid fa-camera-rotate fs-1 text-primary opacity-50"></i>
                    </div>
                    <h6 class="fw-bold text-dark mb-1">No Screenshots Recorded for This Day</h6>
                    <p class="text-secondary small mb-0 max-w-md mx-auto" style="max-width: 420px;">
                        Screenshots are captured automatically every 2 minutes while this employee is tracking time.
                    </p>
                </div>
            `;
            return;
        }

        let html = '';
        shots.forEach((shot, idx) => {
            html += `
                <div class="col" id="user-shot-card-${shot.id}">
                    <div class="card h-100 border rounded-3 overflow-hidden shadow-2xs bg-white hover-shadow transition-all position-relative">
                        <div class="position-relative overflow-hidden bg-dark ratio ratio-16x9 cursor-pointer" 
                             onclick="openUserScreenshotLightbox(${idx})" 
                             style="cursor: pointer;">
                            <img src="${shot.url}" 
                                 alt="Screenshot at ${shot.captured_at_formatted}" 
                                 class="card-img-top object-fit-cover transition-all" 
                                 loading="lazy">
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

    function openUserScreenshotLightbox(idx) {
        if (!userScreenshotsList || userScreenshotsList.length === 0) return;
        userActiveLightboxIdx = Math.max(0, Math.min(idx, userScreenshotsList.length - 1));
        updateUserLightboxView();

        const modalEl = document.getElementById('userScreenshotLightboxModal');
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    }

    function navigateUserLightbox(step) {
        if (!userScreenshotsList || userScreenshotsList.length === 0) return;
        userActiveLightboxIdx = (userActiveLightboxIdx + step + userScreenshotsList.length) % userScreenshotsList.length;
        updateUserLightboxView();
    }

    function updateUserLightboxView() {
        const item = userScreenshotsList[userActiveLightboxIdx];
        if (!item) return;

        document.getElementById('user-lightbox-main-img').src = item.url;
        document.getElementById('user-lightbox-time-badge').textContent = item.captured_at_formatted;
        document.getElementById('user-lightbox-project-badge').textContent = '📁 ' + item.project_name;
        document.getElementById('user-lightbox-task-title').textContent = item.task_title;
        document.getElementById('user-lightbox-counter-badge').textContent = `${userActiveLightboxIdx + 1} / ${userScreenshotsList.length}`;
        document.getElementById('user-lightbox-timestamp-full').innerHTML = `<i class="fa-regular fa-clock me-1 text-warning"></i> Captured at: ${item.captured_at_full}`;
        document.getElementById('user-lightbox-download-link').href = item.url;
    }
</script>
@endpush
@endsection

