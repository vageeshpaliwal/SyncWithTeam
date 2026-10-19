@inject('currentUserService', 'App\Services\CurrentUserService')
@php
    $currentUser = $currentUserService->getUser();
    $activeAttendance = null;
    $unreadNotifications = collect();
    $unreadCount = 0;
    $navbarProjects = collect();
    $navbarLeaveBlock = null;
    if ($currentUser) {
        $org = $currentUserService->getOrganization();

        $activeAttendance = \App\Models\Attendance::where('user_id', $currentUser->id)
            ->whereNull('clock_out_at')
            ->with(['project', 'task'])
            ->first();

        $navbarLeaveBlock = \App\Models\Leave::getActiveLeaveForUser($currentUser->id);

        if ($currentUser->role?->code === 'owner' || $currentUserService->hasPermission('projects.manage')) {
            $navbarProjects = \App\Models\Project::where('organization_id', $org->id)->where('status', 'active')->orderBy('name')->get();
        } else {
            $navbarProjects = $currentUser->projects()->where('status', 'active')->orderBy('name')->get();
        }

        $unreadNotifications = \App\Models\Notification::where('user_id', $currentUser->id)
            ->orderByDesc('created_at')
            ->take(10)
            ->get();
            
        $unreadCount = \App\Models\Notification::where('user_id', $currentUser->id)
            ->where('is_read', false)
            ->count();
    }
@endphp
<nav class="navbar py-0 px-4 shadow-sm border-bottom">

    <div class="d-flex align-items-center">

        <button type="button" class="btn btn-light d-lg-none me-2 rounded-circle border-0 shadow-2xs" data-sidebar-toggle aria-label="Open menu" style="width: 40px; height: 40px;">
            <i class="fa-solid fa-bars text-secondary"></i>
        </button>

        <h4 class="m-0 fw-bold text-dark fs-5">
            @yield('title', 'Dashboard')
        </h4>

        <!-- Quick Time Tracker Button / Active Badge in Navbar -->
        <div id="navbar-active-tracker-container" class="d-none d-md-flex align-items-center ms-3">
            @if($activeAttendance)
                @php
                    $hasProj = !empty($activeAttendance->project_id);
                    $hasTsk = !empty($activeAttendance->task_id) || !empty(trim((string)($activeAttendance->task?->title ?? '')));
                    $badgeTitle = $activeAttendance->task?->title ?: ($activeAttendance->project?->name ?? 'No Task / Project');
                @endphp
                <div class="d-flex align-items-center bg-emerald-50 border border-success-subtle rounded-pill px-3 py-1 gap-2 shadow-sm navbar-active-timer-badge"
                     data-has-project="{{ $hasProj ? '1' : '0' }}"
                     data-has-task="{{ $hasTsk ? '1' : '0' }}"
                     data-project-id="{{ $activeAttendance->project_id ?? '' }}"
                     data-task-title="{{ $activeAttendance->task?->title ?? '' }}">
                    <span class="spinner-grow spinner-grow-sm text-success live-pulse" role="status" style="width: 8px; height: 8px;"></span>
                    <span class="fw-bold text-success small font-monospace" id="navbar-running-timer" data-start="{{ $activeAttendance->clock_in_at->toIso8601String() }}">00:00:00</span>
                    <span class="text-secondary text-truncate px-2 border-start border-end" style="max-width: 180px; font-size: 0.8rem;" title="{{ $badgeTitle }}">
                        <i class="fa-solid fa-play me-1 text-success"></i> {{ $badgeTitle }}
                    </span>
                    <button type="button" onclick="stopActiveTimerFromNavbar()" class="btn btn-link text-danger p-0 ms-1 text-decoration-none fw-bold" style="font-size: 0.75rem;" title="Stop Timer">
                        <i class="fa-solid fa-circle-stop fs-6"></i>
                    </button>
                </div>
            @elseif($navbarLeaveBlock && !empty($navbarLeaveBlock['blocked']))
                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-3 py-1.5 fw-semibold d-flex align-items-center gap-1.5 shadow-2xs" style="font-size: 0.78rem;" title="{{ $navbarLeaveBlock['reason'] }}">
                    <i class="fa-solid fa-umbrella-beach text-warning"></i>
                    <span>{{ $navbarLeaveBlock['label'] }} (Blocked)</span>
                </span>
            @else
                <form id="navbar-clock-in-form" action="{{ route('attendance.clock-in') }}" method="POST" onsubmit="handleNavbarClockIn(event)" class="m-0">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 fw-semibold d-flex align-items-center gap-2 shadow-sm">
                        <i class="fa-solid fa-circle-play text-primary fs-6"></i>
                        <span>Start Timer</span>
                    </button>
                </form>
            @endif

        </div>

    </div>

    <div class="nav-right d-flex align-items-center gap-3">

        <!-- Theme Mode Toggle (Light/Dark) -->
        <button class="btn btn-light rounded-circle border-0 shadow-2xs d-flex align-items-center justify-content-center" 
                id="theme-toggle-btn"
                type="button" 
                title="Toggle Dark / Light Theme"
                aria-label="Toggle Dark / Light Theme"
                style="width: 40px; height: 40px; cursor: pointer;">
            <i class="fa-solid fa-moon text-secondary fs-6" id="theme-icon-moon"></i>
            <i class="fa-solid fa-sun text-warning fs-6 d-none" id="theme-icon-sun"></i>
        </button>

        <!-- Notification Bell Dropdown -->
        @if($currentUser)
            <div class="dropdown">
                <button class="btn btn-light rounded-circle position-relative border-0 shadow-2xs d-flex align-items-center justify-content-center" 
                        type="button" data-bs-toggle="dropdown" aria-expanded="false" 
                        style="width: 40px; height: 40px;">
                    <i class="fa-solid fa-bell text-secondary fs-6"></i>
                    @if($unreadCount > 0)
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger font-monospace" style="font-size: 0.65rem;">
                            {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                        </span>
                    @endif
                </button>
                <div class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4 p-0 mt-2 overflow-hidden" style="width: 340px; max-height: 420px;">
                    <div class="p-3 border-bottom d-flex align-items-center justify-content-between bg-light">
                        <h6 class="mb-0 fw-bold text-dark fs-6"><i class="fa-solid fa-bell text-primary me-2"></i> Notifications</h6>

                        @if($unreadCount > 0)
                            <form action="{{ route('notifications.read-all') }}" method="POST" class="m-0">
                                @csrf
                                <button type="submit" class="btn btn-link text-primary p-0 small text-decoration-none fw-semibold" style="font-size: 0.75rem;">Mark all as read</button>
                            </form>
                        @endif
                    </div>
                    <div class="list-group list-group-flush" style="max-height: 350px; overflow-y: auto;">
                        @forelse($unreadNotifications as $notif)
                            <form action="{{ route('notifications.read', $notif->id) }}" method="POST" class="m-0">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="list-group-item list-group-item-action p-3 border-bottom text-start border-0 {{ !$notif->is_read ? 'bg-primary-subtle bg-opacity-25' : '' }}">
                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                        <span class="fw-bold text-dark small">{{ $notif->title }}</span>
                                        <small class="text-muted font-monospace ms-2 text-nowrap" style="font-size: 0.68rem;">{{ $notif->created_at->diffForHumans() }}</small>
                                    </div>
                                    <p class="text-secondary small mb-0 lh-sm">{{ $notif->message }}</p>
                                </button>
                            </form>
                        @empty
                            <div class="p-4 text-center text-muted">
                                <i class="fa-regular fa-bell-slash fs-3 opacity-50 mb-2 d-block"></i>
                                <small class="fw-medium">No notifications right now.</small>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        @endif

        @if($currentUser)
            <a href="{{ route('profile.edit') }}" class="d-flex align-items-center text-dark text-decoration-none gap-2 px-2 py-1 rounded-pill hover-bg-light">
                @if($currentUser->avatar)
                    <img src="{{ asset('storage/' . $currentUser->avatar) }}" 
                         alt="Avatar" 
                         class="rounded-circle shadow-sm border border-2 border-white"
                         style="width: 38px; height: 38px; object-fit: cover;">
                @else
                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center shadow-sm fw-bold"
                         style="width: 38px; height: 38px; font-size: 0.95rem; background: linear-gradient(135deg, #4F46E5 0%, #7C3AED 100%);">
                        {{ strtoupper(substr($currentUser->first_name, 0, 1)) }}
                    </div>
                @endif
                <div class="d-none d-md-flex flex-column text-start">
                    <span class="fw-bold text-dark lh-1" style="font-size: 0.875rem;">{{ $currentUser->full_name }}</span>
                    <span class="text-muted lh-1 mt-1" style="font-size: 0.75rem;">{{ $currentUser->display_designation }}</span>
                </div>
            </a>
        @endif

        <form action="{{ route('logout') }}" method="POST" class="m-0">
            @csrf
            <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-3 d-flex align-items-center gap-1.5 fw-semibold" style="font-size: 0.8rem;">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span>Logout</span>
            </button>
        </form>

    </div>

</nav>

<!-- Stop Tracker Required Details Modal (Navbar Popup) -->
<div class="modal fade" id="navbarStopTrackerModal" tabindex="-1" aria-labelledby="navbarStopTrackerModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header border-bottom py-3 px-4 bg-light">
                <h6 class="modal-title fw-bold text-dark d-flex align-items-center gap-2" id="navbarStopTrackerModalLabel">
                    <i class="fa-solid fa-circle-exclamation text-warning"></i> Add Details to Stop Tracker
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div id="navbar-modal-error" class="alert alert-danger border-0 rounded-3 py-2 px-3 small d-none mb-3">
                    Please add a task description and select a project before stopping the tracker.
                </div>
                
                <p class="text-muted small mb-3">
                    To record your time log accurately, please specify which project and task description you worked on.
                </p>

                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted font-monospace" style="font-size: 0.72rem;">PROJECT <span class="text-danger">*</span></label>
                    <select id="navbar_modal_project_id" class="form-select rounded-3">
                        <option value="">-- Select Project --</option>
                        @foreach($navbarProjects as $proj)
                            <option value="{{ $proj->id }}" {{ ($activeAttendance && $activeAttendance->project_id == $proj->id) ? 'selected' : '' }}>
                                {{ $proj->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-2">
                    <label class="form-label small fw-bold text-muted font-monospace" style="font-size: 0.72rem;">TASK DESCRIPTION <span class="text-danger">*</span></label>
                    <input type="text" id="navbar_modal_task_title" class="form-control rounded-3" 
                           placeholder="What were you working on?" 
                           value="{{ $activeAttendance->task?->title ?? '' }}">
                </div>
            </div>
            <div class="modal-footer border-top py-2.5 px-4 d-flex justify-content-between bg-light">
                <button type="button" class="btn btn-light rounded-pill px-3.5" data-bs-dismiss="modal">Keep Tracking</button>
                <button type="button" id="navbar-modal-submit-btn" onclick="submitNavbarStopModal()" class="btn btn-danger rounded-pill px-4 fw-bold shadow-sm d-flex align-items-center gap-2">
                    <i class="fa-solid fa-square"></i>
                    <span>Save & Stop</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Theme Toggle Logic
        const themeBtn = document.getElementById('theme-toggle-btn');
        const moonIcon = document.getElementById('theme-icon-moon');
        const sunIcon = document.getElementById('theme-icon-sun');

        function updateThemeUI(theme) {
            if (theme === 'dark') {
                moonIcon?.classList.add('d-none');
                sunIcon?.classList.remove('d-none');
            } else {
                moonIcon?.classList.remove('d-none');
                sunIcon?.classList.add('d-none');
            }
        }

        const currentTheme = document.documentElement.getAttribute('data-bs-theme') || 'light';
        updateThemeUI(currentTheme);

        if (themeBtn) {
            themeBtn.addEventListener('click', function () {
                const active = document.documentElement.getAttribute('data-bs-theme') || 'light';
                const next = active === 'dark' ? 'light' : 'dark';
                document.documentElement.setAttribute('data-bs-theme', next);
                localStorage.setItem('theme', next);
                updateThemeUI(next);
            });
        }

        // Live Timer in Navbar
        const navbarTimer = document.getElementById('navbar-running-timer');
        if (navbarTimer) {
            const startTime = new Date(navbarTimer.getAttribute('data-start'));
            function updateNavbarTimer() {
                const now = new Date();
                const diffMs = now - startTime;
                if (diffMs < 0) return;
                const totalSecs = Math.floor(diffMs / 1000);
                const hrs = String(Math.floor(totalSecs / 3600)).padStart(2, '0');
                const mins = String(Math.floor((totalSecs % 3600) / 60)).padStart(2, '0');
                const secs = String(totalSecs % 60).padStart(2, '0');
                navbarTimer.textContent = `${hrs}:${mins}:${secs}`;
            }
            updateNavbarTimer();
            window.navbarInterval = setInterval(updateNavbarTimer, 1000);
        }
    });

    function handleNavbarClockIn(e) {
        e.preventDefault();
        const form = document.getElementById('navbar-clock-in-form');
        
        function submitForm(lat, lon) {
            if (lat && lon) {
                const latInput = document.createElement('input');
                latInput.type = 'hidden';
                latInput.name = 'latitude';
                latInput.value = lat;
                form.appendChild(latInput);

                const lonInput = document.createElement('input');
                lonInput.type = 'hidden';
                lonInput.name = 'longitude';
                lonInput.value = lon;
                form.appendChild(lonInput);
            }
            form.submit();
        }

        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function(pos) {
                    submitForm(pos.coords.latitude, pos.coords.longitude);
                },
                function(err) {
                    submitForm(null, null);
                },
                { timeout: 4000, enableHighAccuracy: true }
            );
        } else {
            submitForm(null, null);
        }
    }

    function stopActiveTimerFromNavbar() {
        // If on attendance page, delegate to handleStopTimer on the page
        if (typeof handleStopTimer === 'function') {
            handleStopTimer();
            return;
        }

        const badge = document.querySelector('.navbar-active-timer-badge');
        const hasProject = badge ? badge.getAttribute('data-has-project') === '1' : false;
        const hasTask = badge ? badge.getAttribute('data-has-task') === '1' : false;

        if (!hasProject || !hasTask) {
            const modalEl = document.getElementById('navbarStopTrackerModal');
            if (modalEl) {
                const bsModal = bootstrap.Modal.getOrCreateInstance(modalEl);
                bsModal.show();
                return;
            }
        }

        // Direct fetch request to clock-out
        fetch("{{ route('attendance.clock-out') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}",
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                if (typeof stopScreenCaptureTracker === 'function') stopScreenCaptureTracker();
                window.location.reload();
            } else if (data.missing_details) {
                const modalEl = document.getElementById('navbarStopTrackerModal');
                if (modalEl) {
                    const bsModal = bootstrap.Modal.getOrCreateInstance(modalEl);
                    bsModal.show();
                    const errorAlert = document.getElementById('navbar-modal-error');
                    if (errorAlert) {
                        errorAlert.textContent = data.message;
                        errorAlert.classList.remove('d-none');
                    }
                } else {
                    alert(data.message);
                }
            } else {
                alert(data.message || 'Could not stop timer.');
            }
        })
        .catch(err => {
            console.error(err);
        });
    }

    function submitNavbarStopModal() {
        const projectSelect = document.getElementById('navbar_modal_project_id');
        const taskInput = document.getElementById('navbar_modal_task_title');
        const errorAlert = document.getElementById('navbar-modal-error');

        const projectId = projectSelect ? projectSelect.value.trim() : '';
        const taskTitle = taskInput ? taskInput.value.trim() : '';

        if (errorAlert) errorAlert.classList.add('d-none');
        if (projectSelect) projectSelect.classList.remove('is-invalid');
        if (taskInput) taskInput.classList.remove('is-invalid');

        let hasError = false;
        if (!projectId) {
            if (projectSelect) projectSelect.classList.add('is-invalid');
            hasError = true;
        }
        if (!taskTitle) {
            if (taskInput) taskInput.classList.add('is-invalid');
            hasError = true;
        }

        if (hasError) {
            if (errorAlert) {
                errorAlert.textContent = 'Please add a task description and select a project before stopping the tracker.';
                errorAlert.classList.remove('d-none');
            }
            return;
        }

        const formData = new FormData();
        formData.append('project_id', projectId);
        formData.append('task_title', taskTitle);

        const submitBtn = document.getElementById('navbar-modal-submit-btn');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Stopping...';
        }

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
                if (typeof stopScreenCaptureTracker === 'function') stopScreenCaptureTracker();
                window.location.reload();
            } else {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fa-solid fa-square me-1"></i> Save & Stop';
                }
                if (errorAlert) {
                    errorAlert.textContent = data.message || 'Could not stop tracker.';
                    errorAlert.classList.remove('d-none');
                }
            }
        })
        .catch(err => {
            console.error(err);
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fa-solid fa-square me-1"></i> Save & Stop';
            }
            if (errorAlert) {
                errorAlert.textContent = 'Network error. Please try again.';
                errorAlert.classList.remove('d-none');
            }
        });
    }
</script>