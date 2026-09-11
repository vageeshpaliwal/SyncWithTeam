@extends('layouts.dashboard')

@section('title', $user->full_name . ' - Profile Details')

@section('content')
<div class="container-fluid">

    <!-- Top Action Navigation Bar -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('users.index') }}" class="btn btn-outline-secondary rounded-pill px-3 py-1.5 fs-7 font-monospace fw-semibold">
                <i class="fa-solid fa-arrow-left me-1.5"></i> Back to Directory
            </a>
            <span class="text-muted">/</span>
            <span class="fw-bold text-dark font-monospace fs-7">{{ $user->full_name }}</span>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('users.edit', $user->id) }}" class="btn btn-primary rounded-pill px-3.5 py-1.5 fs-7 font-monospace fw-semibold shadow-sm">
                <i class="fa-solid fa-pen me-1.5"></i> Edit Profile
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Left Side: Avatar & Summary Profile -->
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 rounded-4 p-3.5 bg-white">
                <div class="card-body p-0 text-center">
                    <div class="mb-2.5 d-inline-block position-relative">
                        @if($user->avatar)
                            <img src="{{ asset('storage/' . $user->avatar) }}" 
                                 alt="{{ $user->full_name }}" 
                                 class="rounded-circle img-thumbnail shadow-sm" 
                                 style="width: 84px; height: 84px; object-fit: cover;">
                        @else
                            <div class="rounded-circle text-white d-flex align-items-center justify-content-center mx-auto shadow-sm fw-bold"
                                 style="width: 84px; height: 84px; font-size: 2.2rem; background: linear-gradient(135deg, #4F46E5 0%, #7C3AED 100%);">
                                {{ strtoupper(substr($user->first_name, 0, 1)) }}
                            </div>
                        @endif
                    </div>

                    <h5 class="fw-bold text-dark mb-0.5 fs-5">{{ $user->full_name }}</h5>
                    <p class="text-muted font-monospace small mb-2" style="font-size: 0.8rem;">@ {{ $user->username }}</p>

                    <div class="mb-3">
                        @if(!empty($user->designation) && strtolower(trim($user->designation)) !== strtolower(trim($user->role?->name ?? '')))
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1 fw-semibold" style="font-size: 0.72rem;">
                                {{ $user->designation }}
                            </span>
                        @endif
                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2.5 py-1 font-monospace" style="font-size: 0.72rem;">
                            {{ $user->role?->name ?? 'User' }}
                        </span>
                    </div>

                    <div class="w-100 border-top pt-3 text-start">
                        <div class="mb-2.5 bg-light p-2.5 rounded-3 border">
                            <span class="text-muted small fw-bold text-uppercase d-block" style="font-size: 0.68rem;">Email Address</span>
                            <span class="fw-semibold text-dark font-monospace small" style="word-break: break-all;">
                                <i class="fa-regular fa-envelope me-1.5 text-primary"></i> {{ $user->email }}
                            </span>
                        </div>
                        <div class="mb-2.5 bg-light p-2.5 rounded-3 border">
                            <span class="text-muted small fw-bold text-uppercase d-block" style="font-size: 0.68rem;">Phone Number</span>
                            <span class="fw-semibold text-dark font-monospace small">
                                <i class="fa-solid fa-phone me-1.5 text-success"></i> {{ $user->phone ?? 'Not provided' }}
                            </span>
                        </div>
                        <div class="mb-2.5 bg-light p-2.5 rounded-3 border">
                            <span class="text-muted small fw-bold text-uppercase d-block" style="font-size: 0.68rem;">Department</span>
                            <span class="fw-semibold text-dark font-monospace small">
                                <i class="fa-solid fa-sitemap me-1.5 text-info"></i> {{ $user->department?->name ?? 'Unassigned' }}
                            </span>
                        </div>
                        <div class="bg-light p-2.5 rounded-3 border d-flex align-items-center justify-content-between">
                            <span class="text-muted small fw-bold text-uppercase" style="font-size: 0.68rem;">Account Status</span>
                            @if($user->status)
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-0.5 font-monospace" style="font-size: 0.7rem;">Active</span>
                            @else
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-2.5 py-0.5 font-monospace" style="font-size: 0.7rem;">Inactive</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side: Employment HR & Personal Details -->
        <div class="col-lg-8">
            
            <!-- HR Employment & Probation Status Card -->
            <div class="card shadow-sm border-0 rounded-4 mb-4 bg-white">
                <div class="card-header bg-white py-3 border-0 d-flex align-items-center justify-content-between">
                    <h6 class="fw-bold text-dark mb-0">
                        <i class="fa-solid fa-briefcase text-primary me-2"></i> HR Employment & Probation Details
                    </h6>
                    <span class="badge bg-light text-muted border rounded-pill font-monospace" style="font-size: 0.7rem;">Set by HR / Admin</span>
                </div>
                <div class="card-body pt-0">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3 border">
                                <span class="text-muted small fw-bold text-uppercase d-block mb-1" style="font-size: 0.7rem;">
                                    <i class="fa-regular fa-calendar-check text-primary me-1"></i> Joining Date
                                </span>
                                <span class="fw-bold text-dark font-monospace fs-6">
                                    {{ $user->joining_date ? $user->joining_date->format('d F Y') : ($user->created_at ? $user->created_at->format('d F Y') : 'N/A') }}
                                </span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3 border">
                                <span class="text-muted small fw-bold text-uppercase d-block mb-1" style="font-size: 0.7rem;">
                                    <i class="fa-solid fa-hourglass-half text-info me-1"></i> Total Tenure in Company
                                </span>
                                <span class="fw-bold text-dark font-monospace fs-6">
                                    {{ $user->tenure }}
                                </span>
                            </div>
                        </div>

                        @php
                            $prob = $user->probation_status;
                        @endphp

                        <div class="col-md-12">
                            <div class="p-3 rounded-3 border bg-light d-flex align-items-center justify-content-between flex-wrap gap-2">
                                <div>
                                    <span class="text-muted small fw-bold text-uppercase d-block mb-1" style="font-size: 0.7rem;">
                                        <i class="fa-solid fa-shield-halved text-warning me-1"></i> Probation Status (3-Month Countdown)
                                    </span>
                                    <div class="fw-semibold text-dark font-monospace small">
                                        {{ $prob['detail'] }}
                                    </div>
                                </div>
                                <div>
                                    <span class="badge {{ $prob['badge'] }} rounded-pill font-monospace px-3 py-1.5 fs-7 fw-bold">
                                        {{ $prob['label'] }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Employee Personal & KYE Information Card -->
            <div class="card shadow-sm border-0 rounded-4 mb-4 bg-white">
                <div class="card-header bg-white py-3 border-0 d-flex align-items-center justify-content-between">
                    <h6 class="fw-bold text-dark mb-0">
                        <i class="fa-solid fa-address-card text-success me-2"></i> Personal & Identity (KYE) Details
                    </h6>
                    <span class="badge bg-light text-muted border rounded-pill font-monospace" style="font-size: 0.7rem;">Self Managed by Employee</span>
                </div>
                <div class="card-body pt-0">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded-3 border">
                                <span class="text-muted small fw-bold text-uppercase d-block mb-1" style="font-size: 0.7rem;">Date of Birth</span>
                                <span class="fw-semibold text-dark font-monospace small">
                                    {{ $user->dob ? $user->dob->format('d M Y') : 'Not provided' }}
                                </span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded-3 border">
                                <span class="text-muted small fw-bold text-uppercase d-block mb-1" style="font-size: 0.7rem;">Father's Name</span>
                                <span class="fw-semibold text-dark font-monospace small">
                                    {{ $user->father_name ?? 'Not provided' }}
                                </span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded-3 border">
                                <span class="text-muted small fw-bold text-uppercase d-block mb-1" style="font-size: 0.7rem;">Mother's Name</span>
                                <span class="fw-semibold text-dark font-monospace small">
                                    {{ $user->mother_name ?? 'Not provided' }}
                                </span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3 border">
                                <span class="text-muted small fw-bold text-uppercase d-block mb-1" style="font-size: 0.7rem;">Aadhaar Card Number</span>
                                <span class="fw-semibold text-dark font-monospace small">
                                    {{ $user->aadhaar_number ?? 'Not provided' }}
                                </span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3 border">
                                <span class="text-muted small fw-bold text-uppercase d-block mb-1" style="font-size: 0.7rem;">PAN Card Number</span>
                                <span class="fw-semibold text-dark font-monospace small">
                                    {{ $user->pan_number ?? 'Not provided' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            <!-- Salary & Compensation Details Card -->
            <div class="card shadow-sm border-0 rounded-4 mb-4 bg-white">
                <div class="card-header bg-white py-3 border-0 d-flex align-items-center justify-content-between">
                    <h6 class="fw-bold text-dark mb-0">
                        <i class="fa-solid fa-indian-rupee-sign text-success me-2"></i> Salary & Bank Account Details
                    </h6>
                    <span class="badge bg-light text-muted border rounded-pill font-monospace" style="font-size: 0.7rem;">Confidential</span>
                </div>
                <div class="card-body pt-0">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3 border">
                                <span class="text-muted small fw-bold text-uppercase d-block mb-1" style="font-size: 0.7rem;">Basic Salary / Compensation</span>
                                <span class="fw-bold text-dark font-monospace fs-6">
                                    @if($user->basic_salary)
                                        ₹{{ number_format($user->basic_salary, 2) }}
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill font-monospace text-uppercase ms-1" style="font-size: 0.65rem;">
                                            {{ $user->pay_frequency ?? 'monthly' }}
                                        </span>
                                    @else
                                        <span class="text-muted fw-normal fs-7">Not set</span>
                                    @endif
                                </span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3 border">
                                <span class="text-muted small fw-bold text-uppercase d-block mb-1" style="font-size: 0.7rem;">Bank Name</span>
                                <span class="fw-semibold text-dark font-monospace small">
                                    {{ $user->bank_name ?? 'Not provided' }}
                                </span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3 border">
                                <span class="text-muted small fw-bold text-uppercase d-block mb-1" style="font-size: 0.7rem;">Account Number</span>
                                <span class="fw-semibold text-dark font-monospace small">
                                    {{ $user->account_number ?? 'Not provided' }}
                                </span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3 border">
                                <span class="text-muted small fw-bold text-uppercase d-block mb-1" style="font-size: 0.7rem;">IFSC Code</span>
                                <span class="fw-semibold text-dark font-monospace small">
                                    {{ $user->ifsc_code ?? 'Not provided' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    @php
                        $latestSlip = \App\Models\Payroll::where('user_id', $user->id)->orderByDesc('id')->first();
                    @endphp
                    @if($latestSlip)
                        <div class="mt-3 pt-3 border-top d-flex align-items-center justify-content-between">
                            <span class="small text-muted font-monospace">
                                <i class="fa-solid fa-file-invoice-dollar text-primary me-1"></i> Latest Slip: <strong>{{ $latestSlip->formatted_month_year }}</strong> ({{ strtoupper($latestSlip->payment_status) }})
                            </span>
                            <a href="{{ route('payrolls.slip', $latestSlip->id) }}" class="btn btn-xs btn-outline-primary rounded-pill px-3 py-1 font-monospace" style="font-size: 0.75rem;">
                                <i class="fa-solid fa-eye me-1"></i> View Payslip
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Weekly Time Logs & 40-Hour Quota Card -->
            <div class="card shadow-sm border-0 rounded-4 mb-4 bg-white">
                <div class="card-header bg-white py-3 border-0 d-flex align-items-center justify-content-between">
                    <h6 class="fw-bold text-dark mb-0">
                        <i class="fa-solid fa-clock-rotate-left text-primary me-2"></i> Weekly Time Logs & Work Quota (40 Hrs / Week)
                    </h6>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill font-monospace" style="font-size: 0.7rem;">
                        Standard: 40 Hrs / Week
                    </span>
                </div>
                <div class="card-body pt-0">
                    
                    <!-- Current Week Progress Bar -->
                    <div class="p-3 bg-light rounded-3 border mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-bold text-dark small">
                                <i class="fa-solid fa-calendar-week me-1.5 text-primary"></i> Current Week Progress
                            </span>
                            <span class="fw-bold font-monospace text-primary fs-6">
                                {{ $currentWeekHours }} / 40.0 Hrs ({{ $currentWeekPercent }}%)
                            </span>
                        </div>
                        <div class="progress rounded-pill style-progress" style="height: 10px; background-color: #E2E8F0;">
                            <div class="progress-bar rounded-pill {{ $currentWeekPercent >= 100 ? 'bg-success' : ($currentWeekPercent >= 75 ? 'bg-primary' : 'bg-info') }}" 
                                 role="progressbar" 
                                 style="width: {{ $currentWeekPercent }}%;" 
                                 aria-valuenow="{{ $currentWeekPercent }}" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                            </div>
                        </div>
                    </div>

                    <!-- Past 4 Weeks Breakdown Table -->
                    <h6 class="fw-bold text-dark mb-2.5 fs-7 text-uppercase text-muted" style="letter-spacing: 0.03em;">
                        Week-by-Week Worked Summary
                    </h6>
                    <div class="table-responsive border rounded-3">
                        <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Week Period</th>
                                    <th class="text-center">Worked Hours</th>
                                    <th class="text-center">Weekly Quota</th>
                                    <th class="text-center">Completion</th>
                                    <th class="pe-3 text-end">Logs Count</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentWeeklyStats as $wStat)
                                    <tr>
                                        <td class="ps-3">
                                            <span class="fw-bold text-dark font-monospace">{{ $wStat['label'] }}</span>
                                            <small class="text-muted d-block font-monospace" style="font-size: 0.72rem;">{{ $wStat['start'] }} - {{ $wStat['end'] }}</small>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill font-monospace px-2.5 py-1 fw-bold fs-7">
                                                {{ $wStat['hours'] }} Hrs
                                            </span>
                                        </td>
                                        <td class="text-center font-monospace text-muted">
                                            40.0 Hrs
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex align-items-center justify-content-center gap-2">
                                                <div class="progress flex-grow-1 rounded-pill" style="height: 6px; max-width: 80px; background-color: #E2E8F0;">
                                                    <div class="progress-bar rounded-pill {{ $wStat['percent'] >= 100 ? 'bg-success' : 'bg-primary' }}" 
                                                         style="width: {{ $wStat['percent'] }}%;"></div>
                                                </div>
                                                <span class="font-monospace fw-bold small text-dark">{{ $wStat['percent'] }}%</span>
                                            </div>
                                        </td>
                                        <td class="pe-3 text-end font-monospace text-muted">
                                            {{ $wStat['logs_count'] }} Session{{ $wStat['logs_count'] == 1 ? '' : 's' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>

            <!-- Leave Quota Summary Card -->
            <div class="card shadow-sm border-0 rounded-4 bg-white">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="fw-bold text-dark mb-0">
                        <i class="fa-solid fa-umbrella-beach text-info me-2"></i> Annual Leave Balance Summary
                    </h6>
                </div>
                <div class="card-body pt-0">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3 border text-center">
                                <span class="text-muted small fw-bold text-uppercase d-block mb-1" style="font-size: 0.7rem;">Leaves Taken (Current Year)</span>
                                <span class="fw-bold text-dark font-monospace fs-5">{{ $user->approved_leaves_days }} Days</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3 border text-center">
                                <span class="text-muted small fw-bold text-uppercase d-block mb-1" style="font-size: 0.7rem;">Remaining Quota (Out of 14 Days)</span>
                                <span class="fw-bold text-success font-monospace fs-5">{{ $user->remaining_leaves_days }} Days</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

</div>
@endsection
