@extends('layouts.app')

@section('title', 'Login')

@push('styles')
    @vite('resources/css/pages/login.css')
@endpush

@section('content')

<div class="login-page">
    <div class="container-fluid p-0">
        <div class="row g-0 login-split">
            <div class="col-lg-6 login-brand-pane">
                <div>
                    <div class="login-brand-logo">S</div>
                    <h1 class="fw-bold display-6 mb-3">Work together without losing the plot.</h1>
                    <p class="lead mb-4" style="opacity: 0.88;">Time, projects, leaves, and payroll — one workspace for your whole team.</p>
                </div>
                <div class="d-grid gap-3" style="max-width: 420px;">
                    <div class="login-feature d-flex align-items-center gap-3">
                        <i class="fa-solid fa-stopwatch"></i>
                        <span>Live time tracking with task context</span>
                    </div>
                    <div class="login-feature d-flex align-items-center gap-3">
                        <i class="fa-solid fa-diagram-project"></i>
                        <span>Projects, roles, and team chat in one place</span>
                    </div>
                    <div class="login-feature d-flex align-items-center gap-3">
                        <i class="fa-solid fa-file-invoice-dollar"></i>
                        <span>Leaves, holidays, and payslips that stay in sync</span>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 login-form-pane">
                <div class="login-card">
                    <div class="mb-4">
                        <p class="text-uppercase text-muted small fw-bold mb-2" style="letter-spacing: 0.08em;">Welcome back</p>
                        <h2>Sign in to your workspace</h2>
                        <p class="text-muted">Use your organization email to continue.</p>
                    </div>

                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    @error('token')
                        <div class="alert alert-danger">{{ $message }}</div>
                    @enderror

                    <form action="{{ route('user.login') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="you@company.com">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Enter your password">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember">
                            <label class="form-check-label" for="remember">Remember Me</label>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">Login</button>
                        </div>
                    </form>

                    <hr class="my-4 opacity-25">

                    <div class="text-center">
                        Don't have an organization?
                        <a href="{{ route('register.organization') }}">Create Organization</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
