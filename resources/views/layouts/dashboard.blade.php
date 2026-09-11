<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title') - SyncWithTeam</title>

    <!-- Instant Anti-Flicker Dark Mode Init -->
    <script>
        (function() {
            try {
                const savedTheme = localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
                document.documentElement.setAttribute('data-bs-theme', savedTheme);
            } catch (e) {}
        })();
    </script>

    <!-- PWA & Mobile Optimization -->
    <meta name="theme-color" content="#4F46E5">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="SyncWithTeam">
    <link rel="manifest" href="/manifest.json">
    <link rel="icon" type="image/svg+xml" href="/icons/icon.svg">
    <link rel="apple-touch-icon" href="/icons/icon.svg">

    <!-- Google Fonts Plus Jakarta Sans & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @vite([
        'resources/css/app.css',
        'resources/css/pages/dashboard.css',
        'resources/css/dark-mode.css',
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

<!-- Toast Container for Instant AJAX Feedback -->
<div id="toast-container" class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1090;"></div>

<!-- Service Worker Registration for PWA -->
<script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function() {
            navigator.serviceWorker.register('/sw.js')
                .then(function(registration) {
                    console.log('SyncWithTeam SW registered: ', registration.scope);
                })
                .catch(function(err) {
                    console.warn('SyncWithTeam SW registration failed: ', err);
                });
        });
    }
</script>

@stack('scripts')

</body>

</html>