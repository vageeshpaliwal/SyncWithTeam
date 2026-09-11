@extends('layouts.dashboard')

@section('title', 'Payroll & Salary Slips')

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
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-4 mb-4 d-flex align-items-center gap-2 p-3 bg-danger-subtle text-danger" role="alert">
            <i class="fa-solid fa-triangle-exclamation fs-5"></i>
            <div class="fw-medium">{{ session('error') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Top Hero Header -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden position-relative" style="background: linear-gradient(135deg, #1E1B4B 0%, #312E81 50%, #0F172A 100%); color: #ffffff;">
        <div class="card-body p-4 p-md-4.5 position-relative z-1">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div>
                    <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill mb-2" style="background: rgba(255, 255, 255, 0.12); backdrop-filter: blur(8px); border: 1px solid rgba(255, 255, 255, 0.15);">
                        <i class="fa-solid fa-file-invoice-dollar text-warning"></i>
                        <span class="small fw-semibold text-white font-monospace" style="font-size: 0.72rem; letter-spacing: 0.05em;">HR & PAYROLL DISBURSEMENT</span>
                    </div>
                    <h3 class="fw-bold text-white mb-1 tracking-tight">Payroll & Salary Slips</h3>
                    <p class="text-white-50 small mb-0">Generate monthly employee salary slips and track disbursement status.</p>
                </div>

                <div class="d-flex align-items-center gap-2.5">
                    <button type="button" class="btn btn-light rounded-pill px-3.5 py-2 fw-bold text-primary shadow-sm d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#singlePayrollModal">
                        <i class="fa-solid fa-user-plus"></i>
                        <span>Add / Generate Slip</span>
                    </button>
                    <button type="button" class="btn btn-warning rounded-pill px-4 py-2 fw-bold text-dark shadow-sm d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#batchPayrollModal">
                        <i class="fa-solid fa-bolt text-dark"></i>
                        <span>Generate for All</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Toolbar Card -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
        <div class="card-body p-3 p-md-3.5">
            <form action="{{ route('payrolls.index') }}" method="GET" class="row g-2.5 align-items-center">
                <div class="col-lg-3 col-sm-6">
                    <label class="form-label small fw-bold text-muted mb-1 font-monospace" style="font-size: 0.7rem;">PAY MONTH</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0 rounded-start-pill text-muted"><i class="fa-regular fa-calendar"></i></span>
                        <select name="month" class="form-select border-start-0 rounded-end-pill">
                            @for($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ $selectedMonth == $m ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::createFromDate(null, $m, 1)->format('F') }}
                                </option>
                            @endfor
                        </select>
                    </div>
                </div>

                <div class="col-lg-2 col-sm-6">
                    <label class="form-label small fw-bold text-muted mb-1 font-monospace" style="font-size: 0.7rem;">YEAR</label>
                    <select name="year" class="form-select form-select-sm rounded-pill">
                        @for($y = now()->year - 2; $y <= now()->year + 1; $y++)
                            <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>

                <div class="col-lg-3 col-sm-6">
                    <label class="form-label small fw-bold text-muted mb-1 font-monospace" style="font-size: 0.7rem;">PAYMENT STATUS</label>
                    <select name="status" class="form-select form-select-sm rounded-pill">
                        <option value="">All Statuses</option>
                        <option value="pending" {{ $selectedStatus === 'pending' ? 'selected' : '' }}>⏳ Pending</option>
                        <option value="paid" {{ $selectedStatus === 'paid' ? 'selected' : '' }}>✅ Paid / Disbursed</option>
                    </select>
                </div>

                <div class="col-lg-3 col-sm-6">
                    <label class="form-label small fw-bold text-muted mb-1 font-monospace" style="font-size: 0.7rem;">SEARCH EMPLOYEE</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0 rounded-start-pill text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                        <input type="text" name="search" class="form-control border-start-0 rounded-end-pill" placeholder="Name or email..." value="{{ $search }}">
                    </div>
                </div>

                <div class="col-lg-1 col-12 d-flex align-items-end">
                    <button type="submit" class="btn btn-sm btn-dark w-100 rounded-pill py-1.5 fw-bold">
                        Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Salary Slips Table Card -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
        <div class="card-header bg-white py-3.5 px-4 border-bottom d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 0.85rem;">
                    <i class="fa-solid fa-receipt"></i>
                </div>
                <h6 class="mb-0 fw-bold text-dark">
                    Salary Slips for <span class="text-primary">{{ \Carbon\Carbon::createFromDate($selectedYear, $selectedMonth, 1)->format('F Y') }}</span>
                </h6>
            </div>
            <span class="badge bg-light text-secondary border rounded-pill px-3 py-1.5 font-monospace" style="font-size: 0.75rem;">
                {{ $payrolls->count() }} Records
            </span>
        </div>

        <div class="table-responsive">
            <table class="table align-middle mb-0 table-hover">
                <thead class="bg-light">
                    <tr class="text-uppercase text-muted" style="font-size: 0.7rem; letter-spacing: 0.06em;">
                        <th class="ps-4 py-3">Employee</th>
                        <th>Slip #</th>
                        <th>Present Days</th>
                        <th>Base Salary</th>
                        <th>Net Take-Home</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payrolls as $p)
                        <tr class="transition-all">
                            <!-- Employee Details -->
                            <td class="ps-4 py-3.5">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold shadow-2xs" style="width: 40px; height: 40px; font-size: 0.85rem; background: linear-gradient(135deg, #4F46E5 0%, #7C3AED 100%);">
                                        {{ substr($p->user->first_name, 0, 1) }}{{ substr($p->user->last_name, 0, 1) }}
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-bold text-dark">{{ $p->user->full_name }}</h6>
                                        <div class="text-muted small d-flex align-items-center gap-1.5" style="font-size: 0.73rem;">
                                            <span>{{ $p->user->department?->name ?? 'General' }}</span>
                                            <span>&bull;</span>
                                            <span class="text-primary fw-medium">{{ $p->user->display_designation }}</span>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Slip # -->
                            <td>
                                <span class="badge bg-light text-dark border font-monospace px-2.5 py-1 rounded-3" style="font-size: 0.75rem;">
                                    {{ $p->payslip_number }}
                                </span>
                            </td>

                            <!-- Attendance Days -->
                            <td>
                                <div>
                                    <span class="text-success fw-bold font-monospace">{{ $p->present_days }}</span> <span class="text-muted small">/ {{ $p->working_days }} Days</span>
                                    @if($p->unpaid_leaves > 0)
                                        <span class="badge bg-danger-subtle text-danger rounded-pill px-2 py-0.5 ms-1" style="font-size: 0.65rem;">{{ $p->unpaid_leaves }} LOP</span>
                                    @endif
                                </div>
                                <small class="text-muted font-monospace" style="font-size: 0.68rem;">
                                    <i class="fa-regular fa-clock me-1 text-secondary"></i>{{ $p->total_hours_worked }} hrs logged
                                </small>
                            </td>

                            <!-- Base Salary -->
                            <td>
                                <span class="font-monospace text-dark fw-medium small">₹{{ number_format($p->basic_salary, 2) }}</span>
                            </td>

                            <!-- Net Salary -->
                            <td>
                                <span class="fw-bold text-primary font-monospace fs-6">₹{{ number_format($p->net_salary, 2) }}</span>
                            </td>

                            <!-- Payment Status Badge -->
                            <td>
                                @if($p->payment_status === 'paid')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1 font-monospace d-inline-flex align-items-center gap-1.5" style="font-size: 0.72rem;">
                                        <i class="fa-solid fa-circle-check"></i> PAID
                                    </span>
                                    @if($p->payment_date)
                                        <small class="text-muted d-block font-monospace mt-0.5" style="font-size: 0.68rem;">
                                            {{ $p->payment_date->format('d M Y') }}
                                        </small>
                                    @endif
                                @else
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-3 py-1 font-monospace d-inline-flex align-items-center gap-1.5" style="font-size: 0.72rem;">
                                        <i class="fa-solid fa-clock"></i> PENDING
                                    </span>
                                @endif
                            </td>

                            <!-- Action Buttons -->
                            <td class="text-end pe-4">
                                <div class="d-inline-flex gap-1.5 align-items-center">
                                    <!-- View Slip -->
                                    <a href="{{ route('payrolls.slip', $p->id) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 fw-semibold d-inline-flex align-items-center gap-1" title="View Payslip">
                                        <i class="fa-solid fa-eye"></i> View
                                    </a>

                                    <!-- Download PDF -->
                                    <a href="{{ route('payrolls.pdf', $p->id) }}" class="btn btn-sm btn-light border rounded-pill px-2.5 py-1 text-danger" title="Download PDF Slip">
                                        <i class="fa-solid fa-file-pdf"></i>
                                    </a>

                                    <!-- Mark Paid -->
                                    @if($p->payment_status !== 'paid')
                                        <button type="button" class="btn btn-sm btn-success rounded-pill px-2.5 py-1" data-bs-toggle="modal" data-bs-target="#markPaidModal{{ $p->id }}" title="Mark as Paid">
                                            <i class="fa-solid fa-check"></i>
                                        </button>
                                    @endif

                                    <!-- Edit line items -->
                                    <a href="{{ route('payrolls.edit', $p->id) }}" class="btn btn-sm btn-light border rounded-pill px-2.5 py-1 text-secondary" title="Edit line items">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>

                                    <!-- Delete -->
                                    <form action="{{ route('payrolls.destroy', $p->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this salary slip record?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-light border text-danger rounded-pill px-2.5 py-1" title="Delete">
                                            <i class="fa-regular fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>

                        <!-- Mark Paid Modal for each row -->
                        <div class="modal fade" id="markPaidModal{{ $p->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content rounded-4 border-0 shadow-lg">
                                    <form action="{{ route('payrolls.mark-paid', $p->id) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <div class="modal-header border-bottom py-3 px-4">
                                            <h6 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                                                <i class="fa-solid fa-circle-check text-success fs-5"></i> Mark Salary Disbursed / Paid
                                            </h6>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body p-4">
                                            <div class="p-3 rounded-3 mb-3 border bg-light">
                                                <div class="d-flex justify-content-between mb-1">
                                                    <span class="text-muted small">Employee:</span>
                                                    <strong class="text-dark">{{ $p->user->full_name }}</strong>
                                                </div>
                                                <div class="d-flex justify-content-between mb-1">
                                                    <span class="text-muted small">Month:</span>
                                                    <strong class="text-dark">{{ $p->formatted_month_year }}</strong>
                                                </div>
                                                <div class="d-flex justify-content-between">
                                                    <span class="text-muted small">Net Amount:</span>
                                                    <strong class="text-success font-monospace fs-6">₹{{ number_format($p->net_salary, 2) }}</strong>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label small fw-bold text-muted">Payment Date</label>
                                                <input type="date" name="payment_date" class="form-control rounded-3" value="{{ now()->toDateString() }}" required>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label small fw-bold text-muted">Payment Method</label>
                                                <select name="payment_method" class="form-select rounded-3" required>
                                                    <option value="bank_transfer" selected>Direct Bank Transfer (NEFT/RTGS/IMPS)</option>
                                                    <option value="upi">UPI</option>
                                                    <option value="cheque">Cheque</option>
                                                    <option value="cash">Cash</option>
                                                </select>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label small fw-bold text-muted">Transaction Reference / UTR #</label>
                                                <input type="text" name="transaction_reference" class="form-control rounded-3" placeholder="e.g. UTR12345678">
                                            </div>

                                            <div class="mb-0">
                                                <label class="form-label small fw-bold text-muted">Notes / Remarks</label>
                                                <textarea name="remarks" class="form-control rounded-3" rows="2" placeholder="Optional disbursement notes..."></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer border-top py-2.5 px-4">
                                            <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold shadow-sm">Confirm Paid</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <div class="rounded-circle bg-light d-inline-flex p-3 mb-2 text-muted opacity-50">
                                    <i class="fa-solid fa-receipt fs-2"></i>
                                </div>
                                <h6 class="fw-bold text-dark">No Salary Slips Generated Yet</h6>
                                <p class="small text-muted mb-3">No payslips found for {{ \Carbon\Carbon::createFromDate($selectedYear, $selectedMonth, 1)->format('F Y') }}.</p>
                                <button type="button" class="btn btn-sm btn-primary rounded-pill px-4 py-2 fw-semibold" data-bs-toggle="modal" data-bs-target="#batchPayrollModal">
                                    <i class="fa-solid fa-bolt me-1.5"></i> Generate Slips for All Employees
                                </button>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Batch Payroll Generator -->
<div class="modal fade" id="batchPayrollModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <form action="{{ route('payrolls.generate') }}" method="POST">
                @csrf
                <div class="modal-header border-bottom py-3 px-4">
                    <h6 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                        <i class="fa-solid fa-bolt text-warning fs-5"></i> Generate Salary Slips for All
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted small mb-3">
                        This tool auto-calculates attendance logs, working days, and salary structure for all active team members.
                    </p>

                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold text-muted">Month</label>
                            <select name="month" class="form-select rounded-3" required>
                                @for($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ $selectedMonth == $m ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::createFromDate(null, $m, 1)->format('F') }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-muted">Year</label>
                            <select name="year" class="form-select rounded-3" required>
                                @for($y = now()->year - 1; $y <= now()->year + 1; $y++)
                                    <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top py-2.5 px-4">
                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Generate All Slips</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Single Employee -->
<div class="modal fade" id="singlePayrollModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <form action="{{ route('payrolls.generate') }}" method="POST">
                @csrf
                <div class="modal-header border-bottom py-3 px-4">
                    <h6 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                        <i class="fa-solid fa-user-plus text-primary fs-5"></i> Add / Generate Slip for Employee
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted font-monospace" style="font-size: 0.72rem;">SELECT EMPLOYEE</label>
                        <select name="user_id" id="modalEmployeeSelect" class="form-select rounded-3" required onchange="handleModalEmployeeChange(this)">
                            <option value="">Choose Employee...</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}" data-salary="{{ $emp->basic_salary ?? 35000 }}">
                                    {{ $emp->full_name }} ({{ $emp->display_designation }}) - CTC: ₹{{ number_format($emp->basic_salary ?? 35000, 2) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted font-monospace" style="font-size: 0.72rem;">MONTHLY BASE SALARY (₹)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-dark fw-bold">₹</span>
                            <input type="number" step="0.01" name="basic_salary" id="modalSalaryInput" class="form-control" placeholder="e.g. 50000" required>
                        </div>
                        <small class="text-muted" style="font-size: 0.7rem;">Auto-filled from employee profile. You can modify it here.</small>
                    </div>

                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold text-muted font-monospace" style="font-size: 0.72rem;">PAY MONTH</label>
                            <select name="month" class="form-select rounded-3" required>
                                @for($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ $selectedMonth == $m ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::createFromDate(null, $m, 1)->format('F') }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-muted font-monospace" style="font-size: 0.72rem;">YEAR</label>
                            <select name="year" class="form-select rounded-3" required>
                                @for($y = now()->year - 1; $y <= now()->year + 1; $y++)
                                    <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top py-2.5 px-4">
                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">Generate Slip</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function handleModalEmployeeChange(select) {
    const selectedOption = select.options[select.selectedIndex];
    const salary = selectedOption.getAttribute('data-salary');
    const salaryInput = document.getElementById('modalSalaryInput');
    if (salary && salaryInput) {
        salaryInput.value = salary;
    }
}
</script>
@endsection