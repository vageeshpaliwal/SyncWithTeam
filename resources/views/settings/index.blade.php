@extends('layouts.dashboard')

@section('title', 'Organization Settings')

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
            <h4 class="fw-bold text-dark mb-1">Organization Settings</h4>
            <p class="text-muted small mb-0">Manage company branding, timezone, currency, and general profile.</p>
        </div>

        <a href="{{ route('organization.edit') }}" class="btn btn-primary rounded-pill px-3.5 py-2 fw-semibold d-flex align-items-center gap-2 shadow-sm">
            <i class="fa-solid fa-pen-to-square"></i> Edit Organization
        </a>
    </div>

    <div class="row g-4">
        <!-- Logo Card -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-4 text-center h-100">
                <div class="mb-3">
                    @if($organization->logo)
                        <img src="{{ asset('storage/'.$organization->logo) }}" class="img-fluid rounded-4 shadow-sm border p-2 bg-white" style="max-height: 160px; object-fit: contain;">
                    @else
                        <div class="rounded-4 bg-primary-subtle text-primary d-flex align-items-center justify-content-center mx-auto shadow-2xs" style="width: 120px; height: 120px;">
                            <i class="fa-solid fa-building fs-1"></i>
                        </div>
                    @endif
                </div>
                <h5 class="fw-bold text-dark mb-1">{{ $organization->name }}</h5>
                <span class="badge bg-light text-secondary border rounded-pill font-monospace px-3 py-1 mb-3" style="font-size: 0.75rem;">
                    {{ $organization->slug ?: 'organization-workspace' }}
                </span>
                <div>
                    @if($organization->status)
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1.5 font-monospace">
                            <i class="fa-solid fa-circle-check me-1"></i> Active Organization
                        </span>
                    @else
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-1.5 font-monospace">
                            <i class="fa-solid fa-circle-xmark me-1"></i> Inactive
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Details Grid Card -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
                <div class="card-header bg-white py-3 px-4 border-bottom">
                    <h6 class="mb-0 fw-bold text-dark fs-6"><i class="fa-solid fa-circle-info text-primary me-2"></i> Company Profile</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-modern mb-0">
                            <tbody>
                                <tr>
                                    <th class="ps-4 text-muted small text-uppercase" width="35%">Organization Name</th>
                                    <td class="fw-bold text-dark">{{ $organization->name }}</td>
                                </tr>
                                <tr>
                                    <th class="ps-4 text-muted small text-uppercase">Email Address</th>
                                    <td class="font-monospace text-dark">{{ $organization->email }}</td>
                                </tr>
                                <tr>
                                    <th class="ps-4 text-muted small text-uppercase">Phone Number</th>
                                    <td class="font-monospace text-dark">{{ $organization->phone ?: 'Not configured' }}</td>
                                </tr>
                                <tr>
                                    <th class="ps-4 text-muted small text-uppercase">Website</th>
                                    <td>
                                        @if($organization->website)
                                            <a href="{{ $organization->website }}" target="_blank" class="text-primary text-decoration-none fw-semibold">
                                                {{ $organization->website }} <i class="fa-solid fa-arrow-up-right-from-square small ms-1"></i>
                                            </a>
                                        @else
                                            <span class="text-muted fst-italic">Not configured</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th class="ps-4 text-muted small text-uppercase">Timezone</th>
                                    <td>
                                        <span class="badge bg-light text-dark border rounded-pill font-monospace px-2.5 py-1">
                                            <i class="fa-regular fa-clock me-1 text-primary"></i> {{ $organization->timezone }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="ps-4 text-muted small text-uppercase">Default Currency</th>
                                    <td>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill font-monospace px-2.5 py-1">
                                            {{ $organization->currency }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="ps-4 text-muted small text-uppercase">Country</th>
                                    <td class="text-dark">{{ $organization->country }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Geofencing & IP Security Card -->
            <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mt-4">
                <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold text-dark fs-6"><i class="fa-solid fa-location-dot text-danger me-2"></i> Attendance Geofencing & Network Security</h6>
                    <a href="{{ route('organization.edit') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1">
                        <i class="fa-solid fa-gear me-1"></i> Configure
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-modern mb-0">
                            <tbody>
                                <tr>
                                    <th class="ps-4 text-muted small text-uppercase" width="35%">GPS Geofence Enforcement</th>
                                    <td>
                                        @if($organization->enforce_geofence)
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill font-monospace px-2.5 py-1">
                                                <i class="fa-solid fa-shield-check me-1"></i> Enabled & Enforced
                                            </span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary border rounded-pill font-monospace px-2.5 py-1">
                                                <i class="fa-solid fa-shield-slash me-1"></i> Disabled (Log Only)
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th class="ps-4 text-muted small text-uppercase">Office GPS Location</th>
                                    <td>
                                        @if($organization->geofence_latitude && $organization->geofence_longitude)
                                            <span class="font-monospace text-dark fw-semibold">
                                                {{ $organization->geofence_latitude }}, {{ $organization->geofence_longitude }}
                                            </span>
                                            <span class="badge bg-primary-subtle text-primary border rounded-pill ms-2">
                                                Radius: {{ $organization->geofence_radius_meters ?: 200 }}m
                                            </span>
                                        @else
                                            <span class="text-muted fst-italic">Office coordinates not set</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th class="ps-4 text-muted small text-uppercase">IP Network Restriction</th>
                                    <td>
                                        @if($organization->enforce_ip_restriction)
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill font-monospace px-2.5 py-1">
                                                <i class="fa-solid fa-wifi me-1"></i> Enabled & Enforced
                                            </span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary border rounded-pill font-monospace px-2.5 py-1">
                                                <i class="fa-solid fa-wifi-slash me-1"></i> Disabled
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th class="ps-4 text-muted small text-uppercase">Allowed Office IPs</th>
                                    <td>
                                        @if($organization->allowed_ips)
                                            <div class="d-flex flex-wrap gap-1.5">
                                                @foreach(explode(',', $organization->allowed_ips) as $ip)
                                                    <span class="badge bg-light text-dark border font-monospace px-2 py-1">{{ trim($ip) }}</span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-muted fst-italic">No specific IPs restricted (All networks allowed)</span>
                                        @endif
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>

</div>

@endsection