@extends('layouts.app')
@section('title','Welcome')

@push('styles')
    @vite('resources/css/pages/welcome.css')
@endpush

@section('content')

<div class="welcome-page">
<nav class="navbar navbar-expand-lg welcome-nav border-bottom">
    <div class="container py-2">
        <a class="welcome-brand navbar-brand mb-0" href="/">
            <span class="welcome-brand-mark">S</span>
            SyncWithTeam
        </a>
        <div class="ms-auto d-flex gap-2">
            <a href="{{ route('login') }}" class="btn btn-outline-primary rounded-pill px-3">Login</a>
            <a href="{{ route('register.organization') }}" class="btn btn-primary rounded-pill px-3">Register Organization</a>
        </div>
    </div>
</nav>

<section class="hero">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <span class="badge rounded-pill text-primary bg-primary-subtle border border-primary-subtle mb-3 px-3 py-2">Team operations, simplified</span>
                <h1 class="hero-title">Manage your organization, projects &amp; teams</h1>
                <p class="lead mt-4 text-secondary">
                    SyncWithTeam helps organizations manage users, time, leaves, payroll, and tasks from one calm workspace.
                </p>
                <div class="mt-4 d-flex flex-wrap gap-2">
                    <a href="{{ route('register.organization') }}" class="btn btn-primary btn-lg rounded-pill px-4">Get started</a>
                    <a href="{{ route('login') }}" class="btn btn-outline-dark btn-lg rounded-pill px-4">Login</a>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card preview-card border-0">
                    <div class="preview-bar"></div>
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-4">Dashboard preview</h5>
                        <div class="row g-3 text-center">
                            <div class="col-4">
                                <div class="preview-stat">
                                    <h3 class="fw-bold mb-0">12</h3>
                                    <small class="text-muted">Projects</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="preview-stat">
                                    <h3 class="fw-bold mb-0">48</h3>
                                    <small class="text-muted">Users</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="preview-stat">
                                    <h3 class="fw-bold mb-0">156</h3>
                                    <small class="text-muted">Tasks</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mt-5 pb-5">
            <div class="col-md-4">
                <div class="feature-card">
                    <i class="fa-solid fa-users text-primary mb-3"></i>
                    <h5 class="fw-bold">People & roles</h5>
                    <p class="text-muted mb-0">Invite teammates, assign departments, and keep permissions tidy.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card">
                    <i class="fa-solid fa-stopwatch text-primary mb-3"></i>
                    <h5 class="fw-bold">Time that makes sense</h5>
                    <p class="text-muted mb-0">Track work against projects and tasks without extra admin noise.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card">
                    <i class="fa-solid fa-file-invoice-dollar text-primary mb-3"></i>
                    <h5 class="fw-bold">Payroll & leaves</h5>
                    <p class="text-muted mb-0">Approvals, holidays, and slips stay connected to the same team data.</p>
                </div>
            </div>
        </div>
    </div>
</section>
</div>

@endsection
