@extends('layouts.dashboard')

@section('title','Dashboard')

@section('content')

<div class="card">

    <div class="card-body">

        <h2>

            Welcome

            {{ $currentUser->first_name }}

        </h2>

        <p>

            Organization :

            {{ $currentUser->organization->name }}

        </p>

    </div>

</div>

@endsection