@extends('layouts.dashboard')

@section('title', 'Users')

@section('content')

<div class="container-fluid">

    <!-- Page Header -->

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h3 class="mb-1">Users</h3>

            <p class="text-muted mb-0">
                Manage your organization users and invitations.
            </p>

        </div>

        <a href="{{ route('users.invite') }}" class="btn btn-primary">

            + Add User

        </a>

    </div>

    <!-- Search -->

    <div class="card shadow-sm border-0 mb-4">

        <div class="card-body">

            <form>

                <div class="row">

                    <div class="col-md-4">

                        <input type="text" class="form-control" placeholder="Search user...">

                    </div>

                </div>

            </form>

        </div>

    </div>

    <!-- Tabs -->

    <ul class="nav nav-tabs mb-3">

        <li class="nav-item">

            <button class="nav-link active" type="button">
                Users
            </button>

        </li>

        <li class="nav-item">

            <button class="nav-link" type="button">
                Pending Invitations
            </button>

        </li>

        <li class="nav-item">

            <button class="nav-link" type="button">
                Accepted Invitations
            </button>

        </li>

    </ul>

    <!-- Table -->

    <div class="card shadow-sm border-0">

        <div class="card-body p-0">

            <table class="table table-hover mb-0">

                <thead class="table-light">

                    <tr>

                        <th>Name</th>

                        <th>Email</th>

                        <th>Role</th>

                        <th>Status</th>

                        <th width="120">Action</th>

                    </tr>

                </thead>

                <tbody>

                    <tr>

                        <td colspan="5" class="text-center py-5">

                            No users found.

                        </td>

                    </tr>

                </tbody>

            </table>

        </div>

    </div>

</div>

@endsection