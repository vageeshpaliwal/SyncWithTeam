@extends('layouts.app')

@section('title', 'Register Organization')

@push('styles')
@vite('resources/css/pages/register-organization.css')
@endpush

@section('content')

<div class="register-page">

    <div class="container py-5">

        <div class="row justify-content-center">

            <div class="col-lg-8">

                <div class="register-header text-center mb-5">

                    <h2>Create Your Organization</h2>

                    <p class="text-muted">
                        Register your organization and create the owner account.
                    </p>

                </div>

                <form action="{{ route('store.organization') }}" method="POST" class="register-form">

                    @csrf

                    <!-- ================================= -->
                    <!-- Organization Information -->
                    <!-- ================================= -->

                    <div class="form-section">

                        <div class="section-title">

                            <h4>Organization Information</h4>

                            <p>
                                Basic information about your organization.
                            </p>

                        </div>

                        <div class="row">

                            <div class="col-md-12 mb-4">

                                <label class="form-label">

                                    Organization Name
                                    <span class="text-danger">*</span>

                                </label>

                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                    value="{{ old('name') }}" placeholder="ABC Technologies">

                                @error('name')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                                @enderror

                            </div>

                        </div>

                        <div class="row">

                            <div class="col-md-6 mb-4">

                                <label class="form-label">
                                    Website
                                </label>

                                <input type="url" name="website"
                                    class="form-control @error('website') is-invalid @enderror"
                                    value="{{ old('website') }}" placeholder="https://example.com">

                                @error('website')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                                @enderror

                            </div>

                            <div class="col-md-6 mb-4">

                                <label class="form-label">

                                    Organization Email

                                </label>

                                <input type="email" name="email"
                                    class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}"
                                    placeholder="info@example.com">

                                @error('email')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                                @enderror

                            </div>

                        </div>

                        <div class="row">

                            <div class="col-md-6 mb-4">

                                <label class="form-label">

                                    Organization Phone

                                </label>

                                <input type="text" name="phone"
                                    class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}"
                                    placeholder="+91 9876543210">

                                @error('phone')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                                @enderror

                            </div>


                        </div>


                    </div>

                    <!-- ================================= -->
                    <!-- Owner Information -->
                    <!-- ================================= -->

                    <div class="form-section mt-5">

                        <div class="section-title">

                            <h4>Owner Information</h4>

                            <p>
                                This account will become the organization owner.
                            </p>

                        </div>

                        <div class="row">

                            <div class="col-md-6 mb-4">

                                <label class="form-label">
                                    First Name
                                    <span class="text-danger">*</span>
                                </label>

                                <input type="text" name="first_name"
                                    class="form-control @error('first_name') is-invalid @enderror"
                                    value="{{ old('first_name') }}" placeholder="John">

                                @error('first_name')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                                @enderror

                            </div>

                            <div class="col-md-6 mb-4">

                                <label class="form-label">
                                    Last Name
                                </label>

                                <input type="text" name="last_name"
                                    class="form-control @error('last_name') is-invalid @enderror"
                                    value="{{ old('last_name') }}" placeholder="Doe">

                                @error('last_name')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                                @enderror

                            </div>

                        </div>



                        <div class="row">

                            <div class="col-md-12 mb-4">

                                <label class="form-label">
                                    Phone
                                </label>

                                <input type="text" name="owner_phone"
                                    class="form-control @error('owner_phone') is-invalid @enderror"
                                    value="{{ old('owner_phone') }}" placeholder="+91 9876543210">

                                @error('owner_phone')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                                @enderror

                            </div>
                            <div class="col-md-6 mb-4">

                                <label class="form-label">
                                    Username
                                    <span class="text-danger">*</span>
                                </label>

                                <input type="text" name="username"
                                    class="form-control @error('username') is-invalid @enderror"
                                    value="{{ old('username') }}" placeholder="johndoe">

                                @error('username')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-4">

                                <label class="form-label">
                                    Email
                                    <span class="text-danger">*</span>
                                </label>

                                <input type="email" name="owner_email"
                                    class="form-control @error('owner_email') is-invalid @enderror"
                                    value="{{ old('owner_email') }}" placeholder="">
                              
                                @error('owner_email')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                    </div>
                                @enderror
                            </div>       

                        </div>
                        

                    </div>

                    <!-- ================================= -->
                    <!-- Security -->
                    <!-- ================================= -->

                    <div class="form-section mt-5">

                        <div class="section-title">

                            <h4>Security</h4>

                            <p>
                                Secure your owner account.
                            </p>

                        </div>

                        <div class="row">

                            <div class="col-md-6 mb-4">

                                <label class="form-label">
                                    Password
                                    <span class="text-danger">*</span>
                                </label>

                                <input type="password" name="password"
                                    class="form-control @error('password') is-invalid @enderror">

                                @error('password')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                                @enderror

                            </div>

                            <div class="col-md-6 mb-4">

                                <label class="form-label">
                                    Confirm Password
                                    <span class="text-danger">*</span>
                                </label>

                                <input type="password" name="password_confirmation" class="form-control">

                            </div>

                        </div>

                    </div>

                    <div class="mt-5">

                        <div class="form-check mb-4">

                            <input class="form-check-input" type="checkbox" id="terms" required>

                            <label class="form-check-label" for="terms">
                                I agree to the
                                <a href="#">Terms & Conditions</a>
                                and
                                <a href="#">Privacy Policy</a>
                            </label>

                        </div>

                        <button type="submit" class="btn btn-primary btn-lg w-100">
                            Create Organization
                        </button>

                    </div>

                    <div class="text-center mt-4">

                        Already have an account?

                        <a href="{{ route('login') }}">
                            Login
                        </a>

                    </div>

                </form>

            </div>

        </div>

    </div>

</div>

@endsection