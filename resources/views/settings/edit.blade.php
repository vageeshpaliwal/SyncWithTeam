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

                            <label>Country</label>

                            <input type="text" name="country" class="form-control"
                                value="{{ old('country', $organization->country) }}">

                        </div>
                        <div class="d-flex justify-content-end gap-2">

                            <a href="{{ route('settings.index') }}" class="btn btn-secondary">
                                Cancel
                            </a>

                            <button type="submit" class="btn btn-primary">
                                Save Changes
                            </button>

                        </div>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection