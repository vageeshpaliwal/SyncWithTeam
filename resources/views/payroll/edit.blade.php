@extends('layouts.dashboard')

@section('title', 'Edit Salary Slip - ' . $payroll->payslip_number)

@section('content')
<div class="container-fluid px-0">

    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">
                <i class="fa-solid fa-pen-to-square text-primary me-2"></i> Adjust Salary Details
            </h4>
            <p class="text-muted small mb-0">Modify allowances, deductions, and payment status for {{ $payroll->user->full_name }} ({{ $payroll->formatted_month_year }}).</p>
        </div>
        <a href="{{ route('payrolls.index', ['month' => $payroll->month, 'year' => $payroll->year]) }}" class="btn btn-sm btn-light border rounded-pill px-3 py-1.5 fw-semibold text-secondary">
            <i class="fa-solid fa-arrow-left me-1.5"></i> Back to Payroll
        </a>
    </div>

    <form action="{{ route('payrolls.update', $payroll->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row g-4">
            <!-- Left Column: Attendance & Basic Salary -->
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white py-3 px-4 border-bottom">
                        <h6 class="mb-0 fw-bold text-dark"><i class="fa-solid fa-user me-2 text-primary"></i> Employee & Basic Details</h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Employee</label>
                            <input type="text" class="form-control bg-light rounded-3" value="{{ $payroll->user->full_name }} ({{ $payroll->user->display_designation }})" disabled>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Base Salary (₹)</label>
                            <input type="number" step="0.01" name="basic_salary" class="form-control rounded-3" value="{{ old('basic_salary', $payroll->basic_salary) }}" required>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-bold text-muted">Working Days</label>
                                <input type="number" name="working_days" class="form-control rounded-3" value="{{ old('working_days', $payroll->working_days) }}" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold text-muted">Present Days</label>
                                <input type="number" step="0.5" name="present_days" class="form-control rounded-3" value="{{ old('present_days', $payroll->present_days) }}" required>
                            </div>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-bold text-muted">Paid Leaves</label>
                                <input type="number" step="0.5" name="paid_leaves" class="form-control rounded-3" value="{{ old('paid_leaves', $payroll->paid_leaves) }}" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold text-muted">Unpaid (LOP)</label>
                                <input type="number" step="0.5" name="unpaid_leaves" class="form-control rounded-3" value="{{ old('unpaid_leaves', $payroll->unpaid_leaves) }}" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Payment Status</label>
                            <select name="payment_status" class="form-select rounded-3">
                                <option value="pending" {{ $payroll->payment_status === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="processing" {{ $payroll->payment_status === 'processing' ? 'selected' : '' }}>Processing</option>
                                <option value="paid" {{ $payroll->payment_status === 'paid' ? 'selected' : '' }}>Paid</option>
                                <option value="cancelled" {{ $payroll->payment_status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>

                        <div class="mb-0">
                            <label class="form-label small fw-bold text-muted">Remarks</label>
                            <textarea name="remarks" class="form-control rounded-3" rows="2">{{ old('remarks', $payroll->remarks) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Allowances & Deductions Customization -->
            <div class="col-lg-7">
                <!-- Allowances Card -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold text-dark"><i class="fa-solid fa-plus-circle me-2 text-success"></i> Allowances / Earnings</h6>
                        <button type="button" class="btn btn-xs btn-outline-success rounded-pill px-2.5 py-1" onclick="addAllowanceRow()">
                            <i class="fa-solid fa-plus me-1"></i> Add Item
                        </button>
                    </div>
                    <div class="card-body p-4" id="allowanceContainer">
                        @if(is_array($payroll->allowances))
                            @foreach($payroll->allowances as $a)
                                <div class="row g-2 mb-2 allowance-row">
                                    <div class="col-7">
                                        <input type="text" name="allowance_names[]" class="form-control rounded-3" value="{{ $a['name'] ?? '' }}" placeholder="Allowance Name">
                                    </div>
                                    <div class="col-4">
                                        <input type="number" step="0.01" name="allowance_amounts[]" class="form-control rounded-3" value="{{ $a['amount'] ?? 0 }}" placeholder="Amount (₹)">
                                    </div>
                                    <div class="col-1 d-flex align-items-center">
                                        <button type="button" class="btn btn-sm text-danger p-0" onclick="this.closest('.allowance-row').remove()">
                                            <i class="fa-solid fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>

                <!-- Deductions Card -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold text-dark"><i class="fa-solid fa-minus-circle me-2 text-danger"></i> Deductions</h6>
                        <button type="button" class="btn btn-xs btn-outline-danger rounded-pill px-2.5 py-1" onclick="addDeductionRow()">
                            <i class="fa-solid fa-plus me-1"></i> Add Deduction
                        </button>
                    </div>
                    <div class="card-body p-4" id="deductionContainer">
                        @if(is_array($payroll->deductions))
                            @foreach($payroll->deductions as $d)
                                <div class="row g-2 mb-2 deduction-row">
                                    <div class="col-7">
                                        <input type="text" name="deduction_names[]" class="form-control rounded-3" value="{{ $d['name'] ?? '' }}" placeholder="Deduction Name">
                                    </div>
                                    <div class="col-4">
                                        <input type="number" step="0.01" name="deduction_amounts[]" class="form-control rounded-3" value="{{ $d['amount'] ?? 0 }}" placeholder="Amount (₹)">
                                    </div>
                                    <div class="col-1 d-flex align-items-center">
                                        <button type="button" class="btn btn-sm text-danger p-0" onclick="this.closest('.deduction-row').remove()">
                                            <i class="fa-solid fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm">
                        <i class="fa-solid fa-save me-1.5"></i> Save & Recalculate Payslip
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
function addAllowanceRow() {
    const container = document.getElementById('allowanceContainer');
    const div = document.createElement('div');
    div.className = 'row g-2 mb-2 allowance-row';
    div.innerHTML = `
        <div class="col-7">
            <input type="text" name="allowance_names[]" class="form-control rounded-3" placeholder="Allowance Name">
        </div>
        <div class="col-4">
            <input type="number" step="0.01" name="allowance_amounts[]" class="form-control rounded-3" value="0" placeholder="Amount (₹)">
        </div>
        <div class="col-1 d-flex align-items-center">
            <button type="button" class="btn btn-sm text-danger p-0" onclick="this.closest('.allowance-row').remove()">
                <i class="fa-solid fa-times"></i>
            </button>
        </div>
    `;
    container.appendChild(div);
}

function addDeductionRow() {
    const container = document.getElementById('deductionContainer');
    const div = document.createElement('div');
    div.className = 'row g-2 mb-2 deduction-row';
    div.innerHTML = `
        <div class="col-7">
            <input type="text" name="deduction_names[]" class="form-control rounded-3" placeholder="Deduction Name">
        </div>
        <div class="col-4">
            <input type="number" step="0.01" name="deduction_amounts[]" class="form-control rounded-3" value="0" placeholder="Amount (₹)">
        </div>
        <div class="col-1 d-flex align-items-center">
            <button type="button" class="btn btn-sm text-danger p-0" onclick="this.closest('.deduction-row').remove()">
                <i class="fa-solid fa-times"></i>
            </button>
        </div>
    `;
    container.appendChild(div);
}
</script>
@endsection