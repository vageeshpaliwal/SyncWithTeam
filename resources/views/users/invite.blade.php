@extends('layouts.dashboard')

@section('title','Add User')

@section('content')

<div class="container-fluid">

    <div class="row justify-content-center">

        <div class="col-lg-6">

            <div class="card shadow-sm border-0">

                <div class="card-header bg-white">

                    <h3 class="mb-1">

                        Add User

                    </h3>

                    <small class="text-muted">

                        Send an invitation to join your organization.

                    </small>

                </div>

                <div class="card-body">

                    <form action="#" method="POST">

                        @csrf
                        <div class="mb-3">

                            <label class="form-label">

                                Email Address

                            </label>

                            <input type="email" name="email" class="form-control" value="{{ old('email') }}">

                            @error('email')

                            <small class="text-danger">

                                {{ $message }}

                            </small>

                            @enderror

                        </div>
                        <div class="mb-4">

                            <label class="form-label">

                                Role

                            </label>

                            <select name="role_id" class="form-select">

                                <option value="">

                                    Select Role

                                </option>

                            </select>

                            @error('role_id')

                            <small class="text-danger">

                                {{ $message }}

                            </small>

                            @enderror

                        </div>
                        <div class="d-flex justify-content-end gap-2">

                            <a href="{{ route('users.index') }}" class="btn btn-secondary">
                                Cancel
                            </a>

                            <button type="submit" class="btn btn-primary">
                              Add User
                            </button>

                        </div>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection