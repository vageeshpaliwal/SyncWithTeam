<aside class="sidebar" id="app-sidebar">

    <div class="brand">
        <div class="brand-icon">S</div>
        <div>
            <h3 class="brand-title">SyncWithTeam</h3>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill" style="font-size: 0.62rem; font-weight: 700; letter-spacing: 0.06em; padding: 3px 8px;">WORKSPACE</span>
        </div>
    </div>

    @php
        $currentUserService = new \App\Services\CurrentUserService();
        $currentUser = $currentUserService->getUser();
        $roleCode = strtolower($currentUser?->role?->code ?? '');
        $isHrOrAdminOrOwner = in_array($roleCode, ['admin', 'owner', 'hr']);
    @endphp

    <div class="sidebar-nav">
    <div class="sidebar-section">Overview</div>
    <ul>
        <li>
            <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="fa-solid fa-chart-pie"></i>
                <span>Dashboard</span>
            </a>
        </li>

        @if(session()->has('user_id') && $currentUserService->hasPermission('projects.view'))
        <li>
            <a href="{{ route('projects.index') }}" class="{{ request()->routeIs('projects.*') ? 'active' : '' }}">
                <i class="fa-solid fa-diagram-project"></i>
                <span>Projects</span>
            </a>
        </li>
        @endif

        <li>
            <a href="{{ route('chat.index') }}" class="{{ request()->routeIs('chat.*') ? 'active' : '' }}">
                <i class="fa-solid fa-comments"></i>
                <span>Team Chat</span>
            </a>
        </li>

        <li>
            <a href="{{ route('announcements.index') }}" class="{{ request()->routeIs('announcements.*') ? 'active' : '' }}">
                <i class="fa-solid fa-bullhorn"></i>
                <span>Announcements</span>
            </a>
        </li>
    </ul>

    <div class="sidebar-section">People</div>
    <ul>
        @if(session()->has('user_id') && $isHrOrAdminOrOwner && $currentUserService->hasPermission('users.view'))
        <li>
            <a href="{{ route('users.index') }}" class="{{ request()->routeIs('users.*') ? 'active' : '' }}">
                <i class="fa-solid fa-users"></i>
                <span>Users</span>
            </a>
        </li>
        @endif

        @if(session()->has('user_id') && $currentUserService->hasPermission('roles.view'))
        <li>
            <a href="{{ route('roles.index') }}" class="{{ request()->routeIs('roles.*') ? 'active' : '' }}">
                <i class="fa-solid fa-user-shield"></i>
                <span>Roles</span>
            </a>
        </li>
        @endif

        @if(session()->has('user_id') && in_array($roleCode, ['employee', 'manager']))
        <li>
            <a href="{{ route('my-department') }}" class="{{ request()->routeIs('my-department') ? 'active' : '' }}">
                <i class="fa-solid fa-building-user"></i>
                <span>My Department</span>
            </a>
        </li>
        @else
        <li>
            <a href="{{ route('departments.index') }}" class="{{ request()->routeIs('departments.*') ? 'active' : '' }}">
                <i class="fa-solid fa-sitemap"></i>
                <span>Departments</span>
            </a>
        </li>
        @endif
    </ul>

    <div class="sidebar-section">Time & pay</div>
    <ul>
        @if(session()->has('user_id') && (
            $currentUserService->hasPermission('attendance.view') ||
            $currentUserService->hasPermission('attendance.manage')
        ))
        <li>
            <a href="{{ route('attendance.index') }}" class="{{ request()->routeIs('attendance.*') ? 'active' : '' }}">
                <i class="fa-solid fa-stopwatch"></i>
                <span>Time Logs</span>
            </a>
        </li>
        @endif

        @if(session()->has('user_id') && (
            $currentUserService->hasPermission('leaves.request') ||
            $currentUserService->hasPermission('leaves.manage') ||
            $currentUserService->hasPermission('leaves.approve')
        ))
        <li>
            <a href="{{ route('leaves.index') }}" class="{{ request()->routeIs('leaves.*') ? 'active' : '' }}">
                <i class="fa-solid fa-calendar-check"></i>
                <span>{{ in_array($roleCode, ['admin', 'hr']) ? 'Leave & Approvals' : 'Leaves' }}</span>
            </a>
        </li>
        @endif

        @if(session()->has('user_id'))
            @if($currentUserService->hasPermission('payroll.view') || $currentUserService->hasPermission('payroll.manage'))
            <li>
                <a href="{{ route('payrolls.index') }}" class="{{ request()->routeIs('payrolls.index') || request()->routeIs('payrolls.edit') || request()->routeIs('payrolls.slip') ? 'active' : '' }}">
                    <i class="fa-solid fa-file-invoice-dollar"></i>
                    <span>Payroll & Slips</span>
                </a>
            </li>
            @else
            <li>
                <a href="{{ route('payrolls.my-payslips') }}" class="{{ request()->routeIs('payrolls.my-payslips') || request()->routeIs('payrolls.slip') ? 'active' : '' }}">
                    <i class="fa-solid fa-file-invoice-dollar"></i>
                    <span>My Payslips</span>
                </a>
            </li>
            @endif
        @endif

        <li>
            <a href="{{ route('holidays.index') }}" class="{{ request()->routeIs('holidays.*') ? 'active' : '' }}">
                <i class="fa-solid fa-calendar-day"></i>
                <span>Holidays</span>
            </a>
        </li>
    </ul>

    <div class="sidebar-section">Account</div>
    <ul>
        @if(session()->has('user_id') && $currentUserService->hasPermission('settings.view'))
        <li>
            <a href="{{ route('settings.index') }}" class="{{ request()->routeIs('settings.*') ? 'active' : '' }}">
                <i class="fa-solid fa-gear"></i>
                <span>Settings</span>
            </a>
        </li>
        @endif

        @if(session()->has('user_id') && $currentUserService->hasPermission('billing.view'))
        <li>
            <a href="{{ route('billing.index') }}" class="{{ request()->routeIs('billing.*') ? 'active' : '' }}">
                <i class="fa-solid fa-credit-card"></i>
                <span>Billing</span>
            </a>
        </li>
        @endif

        <li>
            <a href="{{ route('profile.edit') }}" class="{{ request()->routeIs('profile.*') ? 'active' : '' }}">
                <i class="fa-solid fa-id-card"></i>
                <span>My Profile</span>
            </a>
        </li>
    </ul>
    </div>

    @if($currentUser)
    <div class="sidebar-footer">
        <a href="{{ route('profile.edit') }}" class="{{ request()->routeIs('profile.*') ? 'active' : '' }}">
            <i class="fa-solid fa-circle-user"></i>
            <span class="text-truncate">{{ $currentUser->full_name }}</span>
        </a>
    </div>
    @endif

</aside>
