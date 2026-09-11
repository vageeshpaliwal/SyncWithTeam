@extends('layouts.dashboard')

@section('title','Add User')

@section('content')

<div class="container-fluid">

    <div class="row justify-content-center">

        <div class="col-lg-6">

            <div class="card shadow-sm border-0">

                <div class="card-header bg-white">

                    <h3 class="mb-1">

                        Add User

                    </h3>

                    <small class="text-muted">

                        Send an invitation to join your organization.

                    </small>

                </div>

                <div class="card-body">

                    <form action="{{ route('users.store') }}" method="POST">

                        @csrf
                        <div class="mb-3">

                            <label class="form-label">

                                Email Address

                            </label>

                            <input type="email" name="email" class="form-control" value="{{ old('email') }}">

                            @error('email')

                            <small class="text-danger">

                                {{ $message }}

                            </small>

                            @enderror

                        </div>
                        <div class="mb-3">

                            <label class="form-label">

                                First Name

                            </label>

                            <input type="text" name="first_name" class="form-control" value="{{ old('first_name') }}">

                            @error('first_name')

                            <small class="text-danger">

                                {{ $message }}

                            </small>

                            @enderror

                        </div>
                        <div class="mb-3">

                            <label class="form-label">

                                Last Name

                            </label>

                            <input type="text" name="last_name" class="form-control" value="{{ old('last_name') }}">

                            @error('last_name')

                            <small class="text-danger">

                                {{ $message }}

                            </small>

                            @enderror
                        <div class="mb-3">
                            <label class="form-label">
                                Designation / Job Title
                            </label>
                            <input type="text" name="designation" class="form-control" placeholder="e.g. Senior Software Engineer" value="{{ old('designation') }}">
                            @error('designation')
                            <small class="text-danger">
                                {{ $message }}
                            </small>
                            @enderror
                        </div>
                        <div class="mb-3">

                            <label class="form-label">

                                Username

                            </label>

                            <input type="text" name="username" class="form-control" value="{{ old('username') }}">

                            @error('username')

                            <small class="text-danger">

                                {{ $message }}

                            </small>

                            @enderror

                        </div>

                        <div class="mb-3">

                            <label class="form-label">

                                Department

                            </label>

                            <select name="department_id" class="form-select">

                                <option value="">
                                    Select Department (Optional)
                                </option>

                                @foreach($departments as $department)

                                    <option
                                        value="{{ $department->id }}"
                                        {{ old('department_id') == $department->id ? 'selected' : '' }}
                                    >
                                        {{ $department->name }}
                                    </option>

                                @endforeach

                            </select>

                            @error('department_id')

                            <small class="text-danger">

                                {{ $message }}

                            </small>

                            @enderror

                        </div>

                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <select name="role_id" class="form-select">
                                <option value="">Select Role</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                        {{ $role->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('role_id')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Joining Date (Set by HR)</label>
                                <input type="date" name="joining_date" class="form-control" value="{{ old('joining_date', date('Y-m-d')) }}">
                                @error('joining_date')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Probation Period (Months)</label>
                                <select name="probation_months" class="form-select">
                                    <option value="1" {{ old('probation_months', 3) == 1 ? 'selected' : '' }}>1 Month</option>
                                    <option value="2" {{ old('probation_months', 3) == 2 ? 'selected' : '' }}>2 Months</option>
                                    <option value="3" {{ old('probation_months', 3) == 3 ? 'selected' : '' }}>3 Months (Standard)</option>
                                    <option value="6" {{ old('probation_months', 3) == 6 ? 'selected' : '' }}>6 Months</option>
                                    <option value="12" {{ old('probation_months', 3) == 12 ? 'selected' : '' }}>1 Year</option>
                                </select>
                                @error('probation_months')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <hr class="my-4">
                        <h5 class="fs-6 fw-bold text-dark mb-3"><i class="fa-solid fa-indian-rupee-sign me-1.5 text-success"></i> Salary & Compensation Details</h5>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Basic Salary / CTC (₹)</label>
                                <input type="number" step="0.01" name="basic_salary" class="form-control" placeholder="e.g. 50000" value="{{ old('basic_salary') }}">
                                @error('basic_salary')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Pay Frequency</label>
                                <select name="pay_frequency" class="form-select">
                                    <option value="monthly" {{ old('pay_frequency') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                    <option value="annually" {{ old('pay_frequency') == 'annually' ? 'selected' : '' }}>Annual CTC</option>
                                    <option value="hourly" {{ old('pay_frequency') == 'hourly' ? 'selected' : '' }}>Hourly Rate</option>
                                </select>
                                @error('pay_frequency')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label class="form-label">Bank Name</label>
                                <input type="text" name="bank_name" class="form-control" placeholder="e.g. HDFC Bank" value="{{ old('bank_name') }}">
                                @error('bank_name')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Account Number</label>
                                <input type="text" name="account_number" class="form-control" placeholder="Account No." value="{{ old('account_number') }}">
                                @error('account_number')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">IFSC Code</label>
                                <input type="text" name="ifsc_code" class="form-control" placeholder="HDFC0001234" value="{{ old('ifsc_code') }}">
                                @error('ifsc_code')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                        <div class="d-flex justify-content-end gap-2">

                            <a href="{{ route('users.index') }}" class="btn btn-secondary">
                                Cancel
                            </a>

                            <button type="submit" class="btn btn-primary">
                              Add User
                            </button>

                        </div>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection