<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title')</title>

    @vite([
        'resources/css/app.css',
        'resources/css/pages/dashboard.css',
        'resources/js/app.js'
    ])

    @stack('styles')        

</head>

<body>

<div class="dashboard-wrapper">

    @include('partials.sidebar')

    <div class="dashboard-content">

        @include('partials.navbar')

        <main class="main-content">

            @yield('content')

        </main>

    </div>

</div>

@stack('scripts')

</body>

</html>