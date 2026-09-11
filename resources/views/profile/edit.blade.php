@extends('layouts.dashboard')

@section('title', 'My Profile')

@section('content')

<div class="container-fluid px-0">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
            <i class="fa-solid fa-triangle-exclamation me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Header Section -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold text-dark mb-1">My Account & Profile</h4>
            <p class="text-muted small mb-0">Update personal credentials, identity documents, bank details, and security.</p>
        </div>
    </div>

    <div class="row g-4">
        <!-- Left Side: Profile Avatar / Info Card -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 bg-white text-center p-4 h-100">
                <div class="mb-3 position-relative d-inline-block">
                    @if($currentUser->avatar)
                        <img src="{{ asset('storage/' . $currentUser->avatar) }}" 
                             alt="Avatar" 
                             class="rounded-circle img-thumbnail shadow-sm border-2 border-white"
                             style="width: 140px; height: 140px; object-fit: cover;">
                    @else
                        <div class="rounded-circle text-white d-flex align-items-center justify-content-center mx-auto shadow-sm fw-bold"
                             style="width: 140px; height: 140px; font-size: 3.5rem; background: linear-gradient(135deg, #4F46E5 0%, #7C3AED 100%);">
                            {{ strtoupper(substr($currentUser->first_name, 0, 1)) }}
                        </div>
                    @endif
                </div>
                <h5 class="fw-bold text-dark mb-1">{{ $currentUser->full_name }}</h5>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill font-monospace px-3 py-1 mb-3" style="font-size: 0.75rem;">
                    {{ $currentUser->display_designation }} &bull; {{ $currentUser->role?->name ?? 'User' }}
                </span>
                
                <div class="p-3 bg-light rounded-3 text-start mb-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Organization:</span>
                        <span class="fw-bold text-dark small">{{ $organization->name }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Department:</span>
                        <span class="fw-bold text-dark small">{{ $currentUser->department?->name ?? 'Unassigned' }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted small">Tenure:</span>
                        <span class="font-monospace text-dark small">{{ $currentUser->tenure }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side: Edit Form -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
                <div class="card-header bg-white py-3 px-4 border-bottom">
                    <h6 class="mb-0 fw-bold text-dark fs-6"><i class="fa-solid fa-user-pen text-primary me-2"></i> Update Profile Information</h6>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <!-- Basic Information -->
                        <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-id-badge text-secondary me-2"></i> Basic Details</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-secondary small text-uppercase">First Name</label>
                                <input type="text" name="first_name" class="form-control" value="{{ old('first_name', $currentUser->first_name) }}" required>
                                @error('first_name')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-secondary small text-uppercase">Last Name</label>
                                <input type="text" name="last_name" class="form-control" value="{{ old('last_name', $currentUser->last_name) }}">
                                @error('last_name')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-secondary small text-uppercase">Email Address</label>
                                <input type="email" name="email" class="form-control" value="{{ old('email', $currentUser->email) }}" required>
                                @error('email')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-secondary small text-uppercase">Phone Number</label>
                                <input type="text" name="phone" class="form-control" value="{{ old('phone', $currentUser->phone) }}" placeholder="+91 9876543210">
                                @error('phone')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold text-secondary small text-uppercase">Upload New Avatar</label>
                                <input type="file" name="avatar" class="form-control">
                                <small class="text-muted">Allowed types: png, jpg, jpeg, webp. Max size: 2MB.</small>
                                @error('avatar')
                                    <br><small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- Personal & KYE Details -->
                        <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-address-card text-secondary me-2"></i> Personal & Identity Details (KYE)</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label fw-bold text-secondary small text-uppercase">Date of Birth</label>
                                <input type="date" name="dob" class="form-control" value="{{ old('dob', $currentUser->dob?->format('Y-m-d')) }}">
                                @error('dob')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold text-secondary small text-uppercase">Father's Name</label>
                                <input type="text" name="father_name" class="form-control" placeholder="e.g. Ramesh Kumar" value="{{ old('father_name', $currentUser->father_name) }}">
                                @error('father_name')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold text-secondary small text-uppercase">Mother's Name</label>
                                <input type="text" name="mother_name" class="form-control" placeholder="e.g. Sunita Devi" value="{{ old('mother_name', $currentUser->mother_name) }}">
                                @error('mother_name')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-secondary small text-uppercase">Aadhaar Card Number</label>
                                <input type="text" name="aadhaar_number" class="form-control font-monospace" placeholder="XXXX XXXX XXXX" value="{{ old('aadhaar_number', $currentUser->aadhaar_number) }}">
                                @error('aadhaar_number')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-secondary small text-uppercase">PAN Card Number</label>
                                <input type="text" name="pan_number" class="form-control font-monospace" placeholder="ABCDE1234F" value="{{ old('pan_number', $currentUser->pan_number) }}">
                                @error('pan_number')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold text-secondary small text-uppercase">Bank Name</label>
                                <input type="text" name="bank_name" class="form-control" placeholder="e.g. HDFC Bank" value="{{ old('bank_name', $currentUser->bank_name) }}">
                                @error('bank_name')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold text-secondary small text-uppercase">Account Number</label>
                                <input type="text" name="account_number" class="form-control font-monospace" placeholder="Account No." value="{{ old('account_number', $currentUser->account_number) }}">
                                @error('account_number')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold text-secondary small text-uppercase">IFSC Code</label>
                                <input type="text" name="ifsc_code" class="form-control font-monospace" placeholder="HDFC0001234" value="{{ old('ifsc_code', $currentUser->ifsc_code) }}">
                                @error('ifsc_code')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- Password Change -->
                        <h6 class="fw-bold text-dark mb-1"><i class="fa-solid fa-lock text-secondary me-2"></i> Change Password</h6>
                        <p class="text-muted small mb-3">Leave blank if you do not wish to modify your password.</p>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-secondary small text-uppercase">New Password</label>
                                <input type="password" name="password" class="form-control" placeholder="••••••••">
                                @error('password')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-secondary small text-uppercase">Confirm New Password</label>
                                <input type="password" name="password_confirmation" class="form-control" placeholder="••••••••">
                            </div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold shadow-sm">
                                <i class="fa-solid fa-floppy-disk me-1.5"></i> Save Profile Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>

@endsection
