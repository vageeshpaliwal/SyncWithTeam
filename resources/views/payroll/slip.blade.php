@extends('layouts.dashboard')

@section('title', 'Payslip - ' . $payroll->payslip_number)

@section('content')
<div class="container-fluid px-0">

    <!-- Top Action Bar (Hidden on Print) -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3 d-print-none">
        <div>
            <a href="{{ url()->previous() == url()->current() ? route('payrolls.my-payslips') : url()->previous() }}" class="btn btn-sm btn-light border rounded-pill px-3 py-1.5 fw-semibold text-secondary shadow-2xs">
                <i class="fa-solid fa-arrow-left me-1.5"></i> Back
            </a>
        </div>

        <div class="d-flex align-items-center gap-2">
            <button onclick="window.print()" class="btn btn-sm btn-outline-dark rounded-pill px-3 py-1.5 fw-semibold shadow-2xs">
                <i class="fa-solid fa-print me-1.5"></i> Print Slip
            </button>
            <a href="{{ route('payrolls.pdf', $payroll->id) }}" class="btn btn-sm btn-primary rounded-pill px-3 py-1.5 fw-semibold shadow-2xs">
                <i class="fa-solid fa-download me-1.5"></i> Download PDF
            </a>
        </div>
    </div>

    <!-- Formal Compact Salary Slip -->
    <div class="card border shadow-sm rounded-4 mx-auto payslip-document" style="max-width: 820px; background: #ffffff;">
        <div class="card-body p-3 p-md-4">

            <!-- Company & Payslip Header -->
            <div class="d-flex flex-wrap justify-content-between align-items-center border-bottom pb-2.5 mb-2.5">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="rounded-3 bg-primary text-white d-flex align-items-center justify-content-center fw-bold shadow-2xs flex-shrink-0" style="width: 40px; height: 40px; font-size: 1.15rem;">
                        {{ substr($payroll->organization->name ?? 'S', 0, 1) }}
                    </div>
                    <div>
                        <h5 class="fw-bold text-dark mb-0 tracking-tight leading-tight">{{ $payroll->organization->name ?? 'SyncWithTeam' }}</h5>
                        <p class="text-muted mb-0 font-monospace" style="font-size: 0.72rem; line-height: 1.2;">
                            {{ $payroll->organization->address ?? 'Corporate Office' }}
                            @if($payroll->organization->email) &bull; {{ $payroll->organization->email }} @endif
                            @if($payroll->organization->phone) &bull; {{ $payroll->organization->phone }} @endif
                        </p>
                    </div>
                </div>

                <div class="text-end mt-2 mt-sm-0">
                    <div class="badge bg-primary-subtle text-primary border border-primary-subtle fw-bold text-uppercase px-2.5 py-1 mb-1 font-monospace" style="font-size: 0.75rem;">
                        SALARY SLIP &bull; {{ strtoupper($payroll->formatted_month_year) }}
                    </div>
                    <div class="text-muted font-monospace" style="font-size: 0.72rem;">
                        Ref: <strong class="text-dark">{{ $payroll->payslip_number }}</strong> | Issued: {{ $payroll->payment_date ? $payroll->payment_date->format('d M Y') : $payroll->created_at->format('d M Y') }}
                    </div>
                </div>
            </div>

            <!-- Employee & Bank Info & Attendance Grid (Condensed Single Box) -->
            <div class="bg-light rounded-3 p-2.5 mb-2.5 border">
                <div class="row g-2" style="font-size: 0.78rem;">
                    <!-- Employee Details -->
                    <div class="col-6 col-md-3">
                        <span class="text-muted d-block font-monospace" style="font-size: 0.65rem;">EMPLOYEE NAME</span>
                        <strong class="text-dark">{{ $payroll->user->full_name }}</strong>
                    </div>
                    <div class="col-6 col-md-3">
                        <span class="text-muted d-block font-monospace" style="font-size: 0.65rem;">EMPLOYEE ID</span>
                        <strong class="text-dark font-monospace">EMP-{{ str_pad($payroll->user->id, 4, '0', STR_PAD_LEFT) }}</strong>
                    </div>
                    <div class="col-6 col-md-3">
                        <span class="text-muted d-block font-monospace" style="font-size: 0.65rem;">DESIGNATION</span>
                        <strong class="text-dark">{{ $payroll->user->display_designation }}</strong>
                    </div>
                    <div class="col-6 col-md-3">
                        <span class="text-muted d-block font-monospace" style="font-size: 0.65rem;">DEPARTMENT</span>
                        <strong class="text-dark">{{ $payroll->user->department?->name ?? 'General' }}</strong>
                    </div>

                    <!-- Bank & Statutory -->
                    <div class="col-6 col-md-3 border-top pt-1.5">
                        <span class="text-muted d-block font-monospace" style="font-size: 0.65rem;">BANK NAME</span>
                        <span class="text-dark fw-medium">{{ $payroll->user->bank_name ?? 'N/A' }}</span>
                    </div>
                    <div class="col-6 col-md-3 border-top pt-1.5">
                        <span class="text-muted d-block font-monospace" style="font-size: 0.65rem;">ACCOUNT NUMBER</span>
                        <span class="text-dark font-monospace fw-medium">{{ $payroll->user->account_number ?? 'N/A' }}</span>
                    </div>
                    <div class="col-6 col-md-3 border-top pt-1.5">
                        <span class="text-muted d-block font-monospace" style="font-size: 0.65rem;">IFSC CODE</span>
                        <span class="text-dark font-monospace fw-medium">{{ $payroll->user->ifsc_code ?? 'N/A' }}</span>
                    </div>
                    <div class="col-6 col-md-3 border-top pt-1.5">
                        <span class="text-muted d-block font-monospace" style="font-size: 0.65rem;">PAN / TAX ID</span>
                        <span class="text-dark font-monospace fw-medium">{{ $payroll->user->pan_number ?? 'N/A' }}</span>
                    </div>

                    <!-- Attendance Metrics -->
                    <div class="col-3 border-top pt-1.5">
                        <span class="text-muted d-block font-monospace" style="font-size: 0.65rem;">CALENDAR DAYS</span>
                        <strong class="text-dark font-monospace">{{ $payroll->working_days }}</strong>
                    </div>
                    <div class="col-3 border-top pt-1.5">
                        <span class="text-muted d-block font-monospace" style="font-size: 0.65rem;">PRESENT DAYS</span>
                        <strong class="text-success font-monospace">{{ $payroll->present_days }}</strong>
                    </div>
                    <div class="col-3 border-top pt-1.5">
                        <span class="text-muted d-block font-monospace" style="font-size: 0.65rem;">PAID LEAVES</span>
                        <strong class="text-primary font-monospace">{{ $payroll->paid_leaves }}</strong>
                    </div>
                    <div class="col-3 border-top pt-1.5">
                        <span class="text-muted d-block font-monospace" style="font-size: 0.65rem;">LOSS OF PAY (LOP)</span>
                        <strong class="text-danger font-monospace">{{ $payroll->unpaid_leaves }}</strong>
                    </div>
                </div>
            </div>

            <!-- Two-Column Earnings & Deductions Table (Condensed) -->
            <div class="table-responsive mb-2.5">
                <table class="table table-bordered align-middle mb-0" style="font-size: 0.8rem;">
                    <thead class="table-light text-uppercase font-monospace" style="font-size: 0.7rem; letter-spacing: 0.05em;">
                        <tr>
                            <th style="width: 50%;" class="ps-2.5 py-1 text-success"><i class="fa-solid fa-circle-plus me-1"></i> Earnings (INR)</th>
                            <th style="width: 50%;" class="ps-2.5 py-1 text-danger"><i class="fa-solid fa-circle-minus me-1"></i> Deductions (INR)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <!-- Earnings Column -->
                            <td class="p-0 align-top">
                                <table class="table table-sm table-borderless mb-0" style="font-size: 0.8rem;">
                                    <tbody>
                                        <tr class="border-bottom">
                                            <td class="ps-2.5 py-1 text-muted">Basic Salary</td>
                                            <td class="pe-2.5 py-1 text-end fw-semibold font-monospace">₹{{ number_format($payroll->basic_salary, 2) }}</td>
                                        </tr>
                                        @if(is_array($payroll->allowances))
                                            @foreach($payroll->allowances as $allowance)
                                                <tr class="border-bottom">
                                                    <td class="ps-2.5 py-1 text-muted">{{ $allowance['name'] ?? 'Allowance' }}</td>
                                                    <td class="pe-2.5 py-1 text-end fw-semibold font-monospace">₹{{ number_format((float)($allowance['amount'] ?? 0), 2) }}</td>
                                                </tr>
                                            @endforeach
                                        @endif
                                    </tbody>
                                </table>
                            </td>

                            <!-- Deductions Column -->
                            <td class="p-0 align-top">
                                <table class="table table-sm table-borderless mb-0" style="font-size: 0.8rem;">
                                    <tbody>
                                        @if(is_array($payroll->deductions) && count($payroll->deductions) > 0)
                                            @foreach($payroll->deductions as $deduction)
                                                <tr class="border-bottom">
                                                    <td class="ps-2.5 py-1 text-muted">{{ $deduction['name'] ?? 'Deduction' }}</td>
                                                    <td class="pe-2.5 py-1 text-end text-danger fw-semibold font-monospace">₹{{ number_format((float)($deduction['amount'] ?? 0), 2) }}</td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr class="border-bottom">
                                                <td class="ps-2.5 py-1 text-muted fst-italic">No deductions</td>
                                                <td class="pe-2.5 py-1 text-end font-monospace">₹0.00</td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </td>
                        </tr>

                        <!-- Totals Row -->
                        <tr class="table-light fw-bold" style="font-size: 0.82rem;">
                            <td class="px-2.5 py-1.5">
                                <div class="d-flex justify-content-between">
                                    <span>Total Gross Earnings (A):</span>
                                    <span class="font-monospace text-dark">₹{{ number_format($payroll->gross_salary, 2) }}</span>
                                </div>
                            </td>
                            <td class="px-2.5 py-1.5">
                                <div class="d-flex justify-content-between">
                                    <span>Total Deductions (B):</span>
                                    <span class="font-monospace text-danger">₹{{ number_format($payroll->total_deductions, 2) }}</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Net Salary Payable Callout (Compact Banner) -->
            <div class="rounded-3 p-2.5 px-3 mb-2.5 border" style="background: linear-gradient(135deg, #EFF6FF 0%, #DBEAFE 100%);">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <div class="d-flex align-items-baseline gap-2">
                            <span class="text-muted fw-bold text-uppercase font-monospace" style="font-size: 0.7rem; letter-spacing: 0.05em;">NET SALARY PAYABLE (A - B):</span>
                            <span class="fs-5 fw-bold text-primary font-monospace">₹{{ number_format($payroll->net_salary, 2) }}</span>
                        </div>
                        <div class="text-secondary small fst-italic" style="font-size: 0.73rem;">{{ $payroll->net_salary_in_words }}</div>
                    </div>

                    @if($payroll->payment_date)
                        <div class="text-sm-end bg-white px-2.5 py-1 rounded-2 border shadow-2xs">
                            <span class="fw-bold text-success font-monospace" style="font-size: 0.72rem;"><i class="fa-solid fa-circle-check me-1"></i> Disbursed</span>
                            <span class="text-muted font-monospace d-inline-block ms-1" style="font-size: 0.68rem;">({{ $payroll->payment_date->format('d M Y') }})</span>
                            @if($payroll->transaction_reference)
                                <div class="text-muted font-monospace" style="font-size: 0.65rem;">Txn: {{ $payroll->transaction_reference }}</div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <!-- Signatures Section (Condensed) -->
            <div class="row pt-3 mt-3 border-top">
                <div class="col-6 text-center">
                    <div class="border-top border-dark mx-auto" style="width: 140px; margin-top: 25px;"></div>
                    <span class="text-muted font-monospace mt-1 d-block" style="font-size: 0.7rem;">Employee Signature</span>
                </div>
                <div class="col-6 text-center">
                    <div class="border-top border-dark mx-auto" style="width: 140px; margin-top: 25px;"></div>
                    <span class="text-muted font-monospace mt-1 d-block" style="font-size: 0.7rem;">Authorized Signatory</span>
                </div>
            </div>

            <!-- Computer Generated Notice -->
            <div class="text-center mt-2.5 pt-1">
                <small class="text-muted font-monospace" style="font-size: 0.65rem;">
                    This is a computer-generated salary document from {{ config('app.name', 'SyncWithTeam') }} and does not require a physical seal.
                </small>
            </div>
        </div>
    </div>
</div>

<style>
@page {
    size: A4 portrait;
    margin: 8mm;
}
@media print {
    body {
        background: #ffffff !important;
        color: #000000 !important;
        font-size: 11px !important;
    }
    .sidebar, .navbar, .d-print-none, .app-header {
        display: none !important;
    }
    .main-content, .content-wrapper, .container-fluid {
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
    }
    .payslip-document {
        border: none !important;
        box-shadow: none !important;
        max-width: 100% !important;
        page-break-inside: avoid;
    }
}
</style>
@endsection