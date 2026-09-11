@extends('layouts.dashboard')

@section('title', 'My Salary & Payslip')

@section('content')
<div class="container-fluid px-0">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-4 mb-4 d-flex align-items-center gap-2 p-3 bg-success-subtle text-success" role="alert">
            <i class="fa-solid fa-circle-check fs-5"></i>
            <div class="fw-medium">{{ session('success') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-warning alert-dismissible fade show border-0 shadow-sm rounded-4 mb-4 d-flex align-items-center gap-2 p-3 bg-warning-subtle text-warning-emphasis" role="alert">
            <i class="fa-solid fa-circle-info fs-5 text-warning"></i>
            <div class="fw-medium">{{ session('error') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @php
        $baseSalary = (float)($currentUser->basic_salary > 0 ? $currentUser->basic_salary : 35000);
        $hra = round($baseSalary * 0.40, 2);
        $special = round($baseSalary * 0.15, 2);
        $medical = 1250.00;
        $gross = round($baseSalary + $hra + $special + $medical, 2);
        $pf = round($baseSalary * 0.12, 2);
        $pt = 200.00;
        $totalDeductions = round($pf + $pt, 2);
        $netTakeHome = round($gross - $totalDeductions, 2);
    @endphp

    <!-- Header with Direct "Download Payslip" Popup Button -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">
                <i class="fa-solid fa-wallet text-primary me-2"></i> My Salary & Compensation
            </h4>
            <p class="text-muted small mb-0">Overview of your salary package, allowances, and deductions.</p>
        </div>

        <button type="button" class="btn btn-primary rounded-pill px-4 py-2.5 fw-bold shadow-sm d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#downloadSlipModal">
            <i class="fa-solid fa-download"></i>
            <span>Download Payslip</span>
        </button>
    </div>

    <!-- Clean, Unified Salary & Compensation Details Card -->
    <div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
        <!-- Top Employee Info Banner -->
        <div class="card-header bg-white py-3 px-3.5 px-md-4 border-bottom">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center fw-bold fs-5 flex-shrink-0" style="width: 46px; height: 46px;">
                    {{ substr($currentUser->first_name, 0, 1) }}{{ substr($currentUser->last_name, 0, 1) }}
                </div>
                <div>
                    <h6 class="fw-bold text-dark mb-0.5">{{ $currentUser->full_name }}</h6>
                    <div class="text-muted small font-monospace" style="font-size: 0.75rem;">
                        EMP-{{ str_pad($currentUser->id, 4, '0', STR_PAD_LEFT) }} &bull; {{ $currentUser->department?->name ?? 'General' }} &bull; {{ $currentUser->display_designation }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Salary Breakdown Grid -->
        <div class="card-body p-3 p-md-4">
            <div class="row g-3">
                <!-- Earnings Column -->
                <div class="col-lg-6">
                    <div class="p-3 rounded-3 bg-light border h-100 d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-2.5">
                                <span class="fw-bold text-success text-uppercase small font-monospace" style="font-size: 0.75rem;">
                                    <i class="fa-solid fa-circle-plus me-1"></i> Earnings Component
                                </span>
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill font-monospace" style="font-size: 0.72rem;">
                                    Gross: ₹{{ number_format($gross, 2) }}
                                </span>
                            </div>

                            <div class="d-flex justify-content-between small text-muted mb-1.5" style="font-size: 0.8rem;">
                                <span>Basic Pay</span>
                                <span class="fw-bold text-dark font-monospace">₹{{ number_format($baseSalary, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between small text-muted mb-1.5" style="font-size: 0.8rem;">
                                <span>House Rent Allowance (HRA 40%)</span>
                                <span class="fw-bold text-dark font-monospace">₹{{ number_format($hra, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between small text-muted mb-1.5" style="font-size: 0.8rem;">
                                <span>Special Allowance (15%)</span>
                                <span class="fw-bold text-dark font-monospace">₹{{ number_format($special, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between small text-muted mb-2" style="font-size: 0.8rem;">
                                <span>Medical & Conveyance</span>
                                <span class="fw-bold text-dark font-monospace">₹{{ number_format($medical, 2) }}</span>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between pt-2 border-top fw-bold text-dark" style="font-size: 0.82rem;">
                            <span>Total Gross Earnings:</span>
                            <span class="text-success font-monospace">₹{{ number_format($gross, 2) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Deductions Column -->
                <div class="col-lg-6">
                    <div class="p-3 rounded-3 bg-light border h-100 d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-2.5">
                                <span class="fw-bold text-danger text-uppercase small font-monospace" style="font-size: 0.75rem;">
                                    <i class="fa-solid fa-circle-minus me-1"></i> Standard Deductions
                                </span>
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill font-monospace" style="font-size: 0.72rem;">
                                    Total: -₹{{ number_format($totalDeductions, 2) }}
                                </span>
                            </div>

                            <div class="d-flex justify-content-between small text-muted mb-1.5" style="font-size: 0.8rem;">
                                <span>Provident Fund (PF 12%)</span>
                                <span class="fw-bold text-danger font-monospace">₹{{ number_format($pf, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between small text-muted mb-1.5" style="font-size: 0.8rem;">
                                <span>Professional Tax (PT)</span>
                                <span class="fw-bold text-danger font-monospace">₹{{ number_format($pt, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between small text-muted mb-2" style="font-size: 0.8rem;">
                                <span>Loss of Pay (LOP)</span>
                                <span class="text-muted font-monospace fst-italic" style="font-size: 0.75rem;">Based on attendance</span>
                            </div>
                        </div>

                        <div>
                            <div class="d-flex justify-content-between pt-2 border-top fw-bold text-dark" style="font-size: 0.82rem;">
                                <span>Total Monthly Deductions:</span>
                                <span class="text-danger font-monospace">-₹{{ number_format($totalDeductions, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between pt-1.5 mt-1 border-top fw-bold text-dark" style="font-size: 0.82rem;">
                                <span>Total Net Income:</span>
                                <span class="text-primary font-monospace">₹{{ number_format($netTakeHome, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bank & Statutory Details Row -->
            <div class="mt-3 p-2.5 bg-light rounded-3 border">
                <div class="row g-2" style="font-size: 0.78rem;">
                    <div class="col-sm-6 col-md-3">
                        <span class="text-muted d-block font-monospace" style="font-size: 0.65rem;">SALARY BANK</span>
                        <strong class="text-dark">{{ $currentUser->bank_name ?? 'Not provided' }}</strong>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <span class="text-muted d-block font-monospace" style="font-size: 0.65rem;">ACCOUNT NUMBER</span>
                        <strong class="text-dark font-monospace">{{ $currentUser->account_number ? '•••• ' . substr($currentUser->account_number, -4) : 'Not provided' }}</strong>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <span class="text-muted d-block font-monospace" style="font-size: 0.65rem;">IFSC CODE</span>
                        <strong class="text-dark font-monospace">{{ $currentUser->ifsc_code ?? 'Not provided' }}</strong>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <span class="text-muted d-block font-monospace" style="font-size: 0.65rem;">PAN / TAX ID</span>
                        <strong class="text-dark font-monospace">{{ $currentUser->pan_number ?? 'Not provided' }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Select Month & Download / View Payslip Popup -->
<div class="modal fade" id="downloadSlipModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <form action="{{ route('payrolls.download-by-period') }}" method="POST">
                @csrf
                <div class="modal-header border-bottom py-3 px-4">
                    <h6 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                        <i class="fa-solid fa-file-invoice-dollar text-primary"></i> Download Monthly Salary Slip
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted small mb-3">Select the pay month and year you wish to view or download as PDF.</p>

                    <div class="row g-3 mb-2">
                        <!-- Month -->
                        <div class="col-6">
                            <label class="form-label small fw-bold text-muted font-monospace" style="font-size: 0.72rem;">SELECT MONTH</label>
                            <select name="month" class="form-select rounded-3">
                                @for($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ now()->month == $m ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::createFromDate(null, $m, 1)->format('F') }}
                                    </option>
                                @endfor
                            </select>
                        </div>

                        <!-- Year -->
                        <div class="col-6">
                            <label class="form-label small fw-bold text-muted font-monospace" style="font-size: 0.72rem;">SELECT YEAR</label>
                            <select name="year" class="form-select rounded-3">
                                @for($y = now()->year; $y >= now()->year - 2; $y--)
                                    <option value="{{ $y }}" {{ now()->year == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top py-2.5 px-4 d-flex justify-content-between">
                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                    <div class="d-flex gap-2">
                        <button type="submit" name="action" value="view" class="btn btn-outline-primary rounded-pill px-3.5 py-2 fw-semibold">
                            <i class="fa-solid fa-eye me-1"></i> View Slip
                        </button>
                        <button type="submit" name="action" value="download" class="btn btn-primary rounded-pill px-3.5 py-2 fw-bold shadow-sm">
                            <i class="fa-solid fa-download me-1"></i> Download PDF
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection