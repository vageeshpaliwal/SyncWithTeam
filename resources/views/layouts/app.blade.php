<!DOCTYPE html>
<html>

<head>

    <title>@yield('title')</title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])

    @stack('styles')

</head>

<body>

    @yield('content')

</body>

</html>