@extends('layouts.app')
@section('title','Welcome')

@push('styles')

    @vite('resources/css/pages/welcome.css')

@endpush

@section('content')

<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
    <div class="container">

        <a class="navbar-brand fw-bold" href="/">
            crm
        </a>

        <div class="ms-auto">

            <a href="{{ route('login') }}" class="btn btn-outline-primary me-2">
                Login
            </a>

            <a href="{{ route('register.organization') }}" class="btn btn-primary">
                Register Organization
            </a>

        </div>

    </div>
</nav>

<section class="py-5">

    <div class="container">

        <div class="row align-items-center">

            <div class="col-lg-6">

                <h1 class="display-4 fw-bold">
                    Manage your Organization,
                    Projects & Teams
                </h1>

                <p class="lead mt-4">
                    FlowCRM helps organizations manage users,
                    projects, roles, permissions and tasks
                    from one place.
                </p>

                <div class="mt-4">

                    <a href="{{ route('register.organization') }}" class="btn btn-primary btn-lg me-3">
                        Register Organization
                    </a>

                    <a href="{{ route('login') }}" class="btn btn-outline-dark btn-lg">
                        Login
                    </a>

                </div>

            </div>

            <div class="col-lg-6">

                <div class="card shadow-lg">

                    <div class="card-body">

                        <h4>Dashboard Preview</h4>

                        <hr>

                        <div class="row text-center">

                            <div class="col">
                                <h3>12</h3>
                                <small>Projects</small>
                            </div>

                            <div class="col">
                                <h3>48</h3>
                                <small>Users</small>
                            </div>

                            <div class="col">
                                <h3>156</h3>
                                <small>Tasks</small>
                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</section>

@endsection