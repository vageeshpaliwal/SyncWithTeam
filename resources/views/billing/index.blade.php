@extends('layouts.dashboard')

@section('title', 'Subscription & Billing')

@section('content')

<div class="container-fluid px-0">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Header Section -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold text-dark mb-1">Subscription & Resource Quota</h4>
            <p class="text-muted small mb-0">Manage organization tier limits, seat utilization, and scale plans.</p>
        </div>
        <div>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-2 font-monospace">
                <i class="fa-solid fa-crown me-1.5"></i> Current Plan: {{ ucfirst($org->plan) }}
            </span>
        </div>
    </div>

    <!-- Usage Metrics Grid -->
    <div class="row g-3 mb-5">
        <!-- Users Limit Card -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small text-uppercase fw-bold font-monospace">Users Limit</span>
                    <div class="rounded-3 bg-primary-subtle text-primary p-2">
                        <i class="fa-solid fa-users"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline mb-3">
                    <span class="fs-2 fw-bold text-dark font-monospace">{{ $usage['users']['current'] }}</span>
                    <span class="text-muted ms-1.5 small font-monospace">/ {{ $usage['users']['limit'] > 0 ? $usage['users']['limit'] : 'Unlimited' }}</span>
                </div>
                <div class="progress rounded-pill mb-2" style="height: 6px;">
                    <div class="progress-bar bg-primary" role="progressbar" 
                         style="width: {{ $usage['users']['limit'] > 0 ? $usage['users']['percent'] : 100 }}%" 
                         aria-valuenow="{{ $usage['users']['current'] }}" 
                         aria-valuemin="0" 
                         aria-valuemax="{{ $usage['users']['limit'] }}"></div>
                </div>
                <small class="text-muted font-monospace" style="font-size: 0.75rem;">{{ $usage['users']['limit'] > 0 ? $usage['users']['percent'] . '% utilized' : 'Unlimited utilization' }}</small>
            </div>
        </div>

        <!-- Departments Limit Card -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small text-uppercase fw-bold font-monospace">Departments Limit</span>
                    <div class="rounded-3 bg-success-subtle text-success p-2">
                        <i class="fa-solid fa-sitemap"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline mb-3">
                    <span class="fs-2 fw-bold text-dark font-monospace">{{ $usage['departments']['current'] }}</span>
                    <span class="text-muted ms-1.5 small font-monospace">/ {{ $usage['departments']['limit'] > 0 ? $usage['departments']['limit'] : 'Unlimited' }}</span>
                </div>
                <div class="progress rounded-pill mb-2" style="height: 6px;">
                    <div class="progress-bar bg-success" role="progressbar" 
                         style="width: {{ $usage['departments']['limit'] > 0 ? $usage['departments']['percent'] : 100 }}%" 
                         aria-valuenow="{{ $usage['departments']['current'] }}" 
                         aria-valuemin="0" 
                         aria-valuemax="{{ $usage['departments']['limit'] }}"></div>
                </div>
                <small class="text-muted font-monospace" style="font-size: 0.75rem;">{{ $usage['departments']['limit'] > 0 ? $usage['departments']['percent'] . '% utilized' : 'Unlimited utilization' }}</small>
            </div>
        </div>

        <!-- Projects Limit Card -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small text-uppercase fw-bold font-monospace">Projects Limit</span>
                    <div class="rounded-3 bg-info-subtle text-info p-2">
                        <i class="fa-solid fa-folder-kanban"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline mb-3">
                    <span class="fs-2 fw-bold text-dark font-monospace">{{ $usage['projects']['current'] }}</span>
                    <span class="text-muted ms-1.5 small font-monospace">/ {{ $usage['projects']['limit'] > 0 ? $usage['projects']['limit'] : 'Unlimited' }}</span>
                </div>
                <div class="progress rounded-pill mb-2" style="height: 6px;">
                    <div class="progress-bar bg-info" role="progressbar" 
                         style="width: {{ $usage['projects']['limit'] > 0 ? $usage['projects']['percent'] : 100 }}%" 
                         aria-valuenow="{{ $usage['projects']['current'] }}" 
                         aria-valuemin="0" 
                         aria-valuemax="{{ $usage['projects']['limit'] }}"></div>
                </div>
                <small class="text-muted font-monospace" style="font-size: 0.75rem;">{{ $usage['projects']['limit'] > 0 ? $usage['projects']['percent'] . '% utilized' : 'Unlimited utilization' }}</small>
            </div>
        </div>
    </div>

    <!-- Pricing Grid -->
    <div class="text-center mb-4">
        <h4 class="fw-bold text-dark mb-1">Available Membership Plans</h4>
        <p class="text-muted small mb-0">Choose the plan that best fits your team's expansion and workflow requirements.</p>
    </div>

    <div class="row g-4 align-items-stretch justify-content-center">
        <!-- Free Plan -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 d-flex flex-column text-center bg-white p-4 {{ $org->plan === 'free' ? 'border-2 border-primary' : '' }}">
                <div class="d-flex flex-column justify-content-between h-100">
                    <div>
                        <h5 class="fw-bold text-dark mb-2">Free Plan</h5>
                        <div class="fs-2 fw-bold text-dark mb-4 font-monospace">$0 <span class="text-muted fs-6 fw-normal font-sans">/ month</span></div>
                        <ul class="list-unstyled text-start mb-5 ps-2">
                            <li class="mb-2.5 text-secondary"><i class="fa-solid fa-circle-check text-success me-2"></i> Up to 5 Team Members</li>
                            <li class="mb-2.5 text-secondary"><i class="fa-solid fa-circle-check text-success me-2"></i> Max 2 Departments</li>
                            <li class="mb-2.5 text-secondary"><i class="fa-solid fa-circle-check text-success me-2"></i> Max 2 Active Projects</li>
                            <li class="mb-2.5 text-secondary"><i class="fa-solid fa-circle-check text-success me-2"></i> Core Clock-In Tracker</li>
                        </ul>
                    </div>
                    @if($org->plan === 'free')
                        <button type="button" class="btn btn-light rounded-pill w-100 py-2 fw-semibold border" disabled>
                            <i class="fa-solid fa-check me-1.5 text-success"></i> Active Plan
                        </button>
                    @else
                        <form action="{{ route('billing.upgrade') }}" method="POST">
                            @csrf
                            <input type="hidden" name="plan" value="free">
                            <button type="submit" class="btn btn-outline-primary rounded-pill w-100 py-2 fw-semibold" onclick="return confirm('Are you sure you want to downgrade to Free?');">Downgrade to Free</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <!-- Pro Plan -->
        <div class="col-lg-4">
            <div class="card border-primary border-2 shadow rounded-4 h-100 d-flex flex-column text-center p-4 position-relative" style="background: linear-gradient(180deg, #F8FAFF 0%, #FFFFFF 100%);">
                <span class="position-absolute top-0 start-50 translate-middle badge bg-primary rounded-pill px-3 py-1.5 shadow-sm font-monospace text-uppercase" style="font-size: 0.72rem;">
                    Most Popular
                </span>
                <div class="d-flex flex-column justify-content-between h-100">
                    <div>
                        <h5 class="fw-bold text-dark mb-2 mt-1">Pro Plan</h5>
                        <div class="fs-2 fw-bold text-primary mb-4 font-monospace">$49 <span class="text-muted fs-6 fw-normal font-sans">/ month</span></div>
                        <ul class="list-unstyled text-start mb-5 ps-2">
                            <li class="mb-2.5 text-secondary"><i class="fa-solid fa-circle-check text-success me-2"></i> Up to 50 Team Members</li>
                            <li class="mb-2.5 text-secondary"><i class="fa-solid fa-circle-check text-success me-2"></i> Max 10 Departments</li>
                            <li class="mb-2.5 text-secondary"><i class="fa-solid fa-circle-check text-success me-2"></i> Unlimited Active Projects</li>
                            <li class="mb-2.5 text-secondary"><i class="fa-solid fa-circle-check text-success me-2"></i> Priority Task Kanban Board</li>
                            <li class="mb-2.5 text-secondary"><i class="fa-solid fa-circle-check text-success me-2"></i> Team Attendance Reports</li>
                        </ul>
                    </div>
                    @if($org->plan === 'pro')
                        <button type="button" class="btn btn-light rounded-pill w-100 py-2 fw-semibold border" disabled>
                            <i class="fa-solid fa-check me-1.5 text-success"></i> Active Plan
                        </button>
                    @else
                        <form action="{{ route('billing.upgrade') }}" method="POST">
                            @csrf
                            <input type="hidden" name="plan" value="pro">
                            <button type="submit" class="btn btn-primary rounded-pill w-100 py-2 fw-semibold shadow-sm">Activate Pro Plan</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <!-- Enterprise Plan -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 d-flex flex-column text-center bg-white p-4">
                <div class="d-flex flex-column justify-content-between h-100">
                    <div>
                        <h5 class="fw-bold text-dark mb-2">Enterprise</h5>
                        <div class="fs-2 fw-bold text-dark mb-4 font-monospace">$199 <span class="text-muted fs-6 fw-normal font-sans">/ month</span></div>
                        <ul class="list-unstyled text-start mb-5 ps-2">
                            <li class="mb-2.5 text-secondary"><i class="fa-solid fa-circle-check text-success me-2"></i> Unlimited Members</li>
                            <li class="mb-2.5 text-secondary"><i class="fa-solid fa-circle-check text-success me-2"></i> Unlimited Departments</li>
                            <li class="mb-2.5 text-secondary"><i class="fa-solid fa-circle-check text-success me-2"></i> Unlimited Projects</li>
                            <li class="mb-2.5 text-secondary"><i class="fa-solid fa-circle-check text-success me-2"></i> Dedicated 24/7 Support</li>
                        </ul>
                    </div>
                    @if($org->plan === 'enterprise')
                        <button type="button" class="btn btn-light rounded-pill w-100 py-2 fw-semibold border" disabled>
                            <i class="fa-solid fa-check me-1.5 text-success"></i> Active Plan
                        </button>
                    @else
                        <form action="{{ route('billing.upgrade') }}" method="POST">
                            @csrf
                            <input type="hidden" name="plan" value="enterprise">
                            <button type="submit" class="btn btn-primary rounded-pill w-100 py-2 fw-semibold shadow-sm">Activate Enterprise</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

</div>

@endsection
