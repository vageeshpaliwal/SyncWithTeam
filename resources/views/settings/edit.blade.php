@extends('layouts.dashboard')

@section('title', 'Edit Organization')

@section('content')

<div class="container-fluid">

    <div class="row justify-content-center">

        <div class="col-lg-10">

            <div class="card shadow-sm border-0">

                <div class="card-header bg-white">

                    <h3>Edit Organization</h3>

                    <small class="text-muted">
                        Update your organization details.
                    </small>

                </div>

                <div class="card-body">

                    <form action="{{ route('organization.update') }}" method="POST" enctype="multipart/form-data">

                        @csrf

                        @method('PUT')
                        <div class="mb-4">

                            <label class="form-label">

                                Organization Logo

                            </label>

                            <input type="file" name="logo" class="form-control">

                            @error('logo')

                            <small class="text-danger">

                                {{ $message }}

                            </small>

                            @enderror

                        </div>
                        <div class="mb-3">

                            <label class="form-label">

                                Organization Name

                            </label>

                            <input type="text" name="name" class="form-control"
                                value="{{ old('name', $organization->name) }}">

                            @error('name')

                            <small class="text-danger">

                                {{ $message }}

                            </small>

                            @enderror

                        </div>
                        <div class="mb-3">

                            <label>Website</label>

                            <input type="text" name="website" class="form-control"
                                value="{{ old('website', $organization->website) }}">

                        </div>
                        <div class="mb-3">

                            <label>Email</label>

                            <input type="email" name="email" class="form-control"
                                value="{{ old('email', $organization->email) }}">

                        </div>
                        <div class="mb-3">

                            <label>Slug</label>

                            <input type="text" name="slug" class="form-control"
                                value="{{ old('slug', $organization->slug) }}">
                            </div>
                        <div class="mb-3">

                            <label>Phone</label>

                            <input type="text" name="phone" class="form-control"
                                value="{{ old('phone', $organization->phone) }}">

                        </div>
                        <div class="mb-4">
                            <label class="form-label">Country</label>
                            <input type="text" name="country" class="form-control"
                                value="{{ old('country', $organization->country) }}">
                        </div>

                        <!-- Geofencing & IP Restriction Settings Section -->
                        <div class="p-4 rounded-4 bg-light border mb-4">
                            <h5 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                                <i class="fa-solid fa-location-dot text-danger"></i> Attendance Geofence & Office Security
                            </h5>
                            <p class="text-muted small mb-4">Set office GPS coordinates and network boundaries to prevent remote proxy attendance.</p>

                            <!-- GPS Geofence Settings -->
                            <div class="row g-3 mb-3">
                                <div class="col-md-5">
                                    <label class="form-label fw-semibold text-dark small">Office Latitude</label>
                                    <input type="number" step="any" id="geofence_latitude" name="geofence_latitude" class="form-control font-monospace" 
                                           placeholder="e.g. 28.613939" value="{{ old('geofence_latitude', $organization->geofence_latitude) }}">
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label fw-semibold text-dark small">Office Longitude</label>
                                    <input type="number" step="any" id="geofence_longitude" name="geofence_longitude" class="form-control font-monospace" 
                                           placeholder="e.g. 77.209021" value="{{ old('geofence_longitude', $organization->geofence_longitude) }}">
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="button" onclick="detectCurrentLocation()" class="btn btn-outline-primary w-100 rounded-3 text-nowrap small py-2 fw-semibold" title="Use current device GPS">
                                        <i class="fa-solid fa-crosshairs me-1"></i> Pick GPS
                                    </button>
                                </div>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-dark small">Geofence Allowed Radius (Meters)</label>
                                    <input type="number" name="geofence_radius_meters" class="form-control" 
                                           placeholder="200" min="10" max="50000" value="{{ old('geofence_radius_meters', $organization->geofence_radius_meters ?: 200) }}">
                                    <small class="text-muted">Standard office radius is typically 100m - 300m.</small>
                                </div>
                                <div class="col-md-6 d-flex align-items-center pt-3">
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" role="switch" id="enforce_geofence" name="enforce_geofence" value="1" {{ old('enforce_geofence', $organization->enforce_geofence) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold text-dark small" for="enforce_geofence">
                                            Enforce Geofencing Check
                                            <span class="d-block text-muted fw-normal" style="font-size: 0.75rem;">Reject clock-ins if employee is outside the designated radius.</span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">

                            <!-- IP Restriction Settings -->
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="form-label fw-semibold text-dark small mb-0">Allowed Office IP Addresses (Comma-separated)</label>
                                    <button type="button" onclick="addCurrentIP('{{ request()->ip() }}')" class="btn btn-link text-primary p-0 small text-decoration-none fw-semibold">
                                        + Add Current IP ({{ request()->ip() }})
                                    </button>
                                </div>
                                <input type="text" id="allowed_ips" name="allowed_ips" class="form-control font-monospace" 
                                       placeholder="e.g. 192.168.1.1, 103.21.244.0" value="{{ old('allowed_ips', $organization->allowed_ips) }}">
                                <small class="text-muted">Leave empty to allow clock-in from any network.</small>
                            </div>

                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" role="switch" id="enforce_ip_restriction" name="enforce_ip_restriction" value="1" {{ old('enforce_ip_restriction', $organization->enforce_ip_restriction) ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold text-dark small" for="enforce_ip_restriction">
                                    Enforce Office IP Restriction
                                    <span class="d-block text-muted fw-normal" style="font-size: 0.75rem;">Only allow attendance clock-ins from the listed Wi-Fi / IP networks.</span>
                                </label>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('settings.index') }}" class="btn btn-secondary rounded-pill px-4">
                                Cancel
                            </a>
                            <button type="submit" class="btn btn-primary rounded-pill px-4">
                                Save Changes
                            </button>
                        </div>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>

<script>
    function detectCurrentLocation() {
        if (!navigator.geolocation) {
            alert('Geolocation is not supported by your browser.');
            return;
        }
        navigator.geolocation.getCurrentPosition(
            function(position) {
                document.getElementById('geofence_latitude').value = position.coords.latitude.toFixed(6);
                document.getElementById('geofence_longitude').value = position.coords.longitude.toFixed(6);
            },
            function(error) {
                alert('Could not retrieve location: ' + error.message);
            },
            { enableHighAccuracy: true, timeout: 10000 }
        );
    }

    function addCurrentIP(ip) {
        const input = document.getElementById('allowed_ips');
        const current = input.value.trim();
        if (!current) {
            input.value = ip;
        } else if (!current.includes(ip)) {
            input.value = current + ', ' + ip;
        }
    }
</script>

@endsection