@extends('layouts.dashboard')

@section('title', 'Organization')

@section('content')

<div class="container-fluid">

    <div class="row">

        <div class="col-lg-12">

            <div class="card shadow-sm border-0">

                <div class="card-header bg-white d-flex justify-content-between align-items-center">

                    <div>

                        <h3 class="mb-1">
                            Organization Details
                        </h3>

                        <small class="text-muted">
                            View your organization information.
                        </small>

                    </div>

                    <a
                        href="{{ route('organization.edit') }}"
                        class="btn btn-primary"
                    >
                        Edit Organization
                    </a>

                </div>

                <div class="card-body">

                    <div class="row">

                        <!-- Logo -->

                        <div class="col-md-3 text-center">

                            @if($organization->logo)

                                <img
                                    src="{{ asset('storage/'.$organization->logo) }}"
                                    class="img-fluid rounded shadow"
                                    style="max-height:180px"
                                >

                            @else

                                <div
                                    class="border rounded d-flex justify-content-center align-items-center"
                                    style="height:180px;background:#f8f9fa;"
                                >

                                    <span class="text-muted">

                                        No Logo

                                    </span>

                                </div>

                            @endif

                        </div>

                        <!-- Organization Details -->

                        <div class="col-md-9">

                            <table class="table table-bordered table-striped">

                                <tbody>

                                    <tr>

                                        <th width="30%">
                                            Organization Name
                                        </th>

                                        <td>
                                            {{ $organization->name }}
                                        </td>

                                    </tr>

                                    <tr>

                                        <th>
                                            Website
                                        </th>

                                        <td>

                                            {{ $organization->website ?: '-' }}

                                        </td>

                                    </tr>
                                     <tr>

                                        <th>
                                            Slug
                                        </th>

                                        <td>

                                            {{ $organization->slug ?: '-' }}

                                        </td>

                                    </tr>

                                    <tr>

                                        <th>
                                            Email
                                        </th>

                                        <td>

                                            {{ $organization->email }}

                                        </td>

                                    </tr>

                                    <tr>

                                        <th>
                                            Phone
                                        </th>

                                        <td>

                                            {{ $organization->phone ?: '-' }}

                                        </td>

                                    </tr>

                                    <tr>

                                        <th>
                                            Timezone
                                        </th>

                                        <td>

                                            {{ $organization->timezone }}

                                        </td>

                                    </tr>

                                    <tr>

                                        <th>
                                            Currency
                                        </th>

                                        <td>

                                            {{ $organization->currency }}

                                        </td>

                                    </tr>

                                    <tr>

                                        <th>
                                            Country
                                        </th>

                                        <td>

                                            {{ $organization->country }}

                                        </td>

                                    </tr>

                                    <tr>

                                        <th>
                                            Status
                                        </th>

                                        <td>

                                            @if($organization->status == true)

                                                <span class="badge bg-success">

                                                    Active

                                                </span>

                                            @else

                                                <span class="badge bg-danger">

                                                    Inactive

                                                </span>

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

</div>

@endsection