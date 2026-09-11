@extends('layouts.app')

@section('title', 'Login')

@push('styles')
    @vite('resources/css/pages/login.css')
@endpush

@section('content')

<div class="login-page">

    <div class="container">

        <div class="row justify-content-center align-items-center min-vh-100">

            <div class="col-lg-5 col-md-7">

                <div class="login-card">

                    <div class="text-center mb-4">

                        <h2>Welcome Back</h2>

                        <p class="text-muted">
                            Login to your organization account.
                        </p>

                    </div>

                    @if(session('success'))

                        <div class="alert alert-success">

                            {{ session('success') }}

                        </div>

                    @endif

                    @error('token')
                        <div class="alert alert-danger">
                            {{ $message }}
                        </div>
                    @enderror

                    <form action="{{ route('user.login') }}" method="POST">

                        @csrf

                        <!-- Email -->

                        <div class="mb-4">

                            <label class="form-label">

                                Email

                            </label>

                            <input
                                type="email"
                                name="email"
                                class="form-control @error('email') is-invalid @enderror"
                                value="{{ old('email') }}"
                                placeholder="Enter your email"
                            >

                            @error('email')

                                <div class="invalid-feedback">

                                    {{ $message }}

                                </div>

                            @enderror

                        </div>

                        <!-- Password -->

                        <div class="mb-4">

                            <label class="form-label">

                                Password

                            </label>

                            <input
                                type="password"
                                name="password"
                                class="form-control @error('password') is-invalid @enderror"
                                placeholder="Enter your password"
                            >

                            @error('password')

                                <div class="invalid-feedback">

                                    {{ $message }}

                                </div>

                            @enderror

                        </div>

                        <!-- Remember Me -->

                        <div class="form-check mb-4">

                            <input
                                class="form-check-input"
                                type="checkbox"
                                name="remember"
                                id="remember"
                            >

                            <label
                                class="form-check-label"
                                for="remember"
                            >

                                Remember Me

                            </label>

                        </div>

                        <!-- Login -->

                        <div class="d-grid">

                            <button
                                type="submit"
                                class="btn btn-primary btn-lg"
                            >

                                Login

                            </button>

                        </div>

                    </form>

                    <hr class="my-4">

                    <div class="text-center">

                        Don't have an organization?

                        <a href="{{ route('register.organization') }}">

                            Create Organization

                        </a>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection