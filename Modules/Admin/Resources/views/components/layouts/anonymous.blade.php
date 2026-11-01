<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Sign In · Khan Group & Company' }}</title>

    {!! view_event_render('layout.head.before') !!}

    <script>
        if (localStorage.getItem('ht-theme') === 'dark' || (!localStorage.getItem('ht-theme') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.setAttribute('data-theme', 'dark');
            document.documentElement.classList.add('dark');
        }

        // Global component registration queue to prevent race conditions
        window.__pendingComponents = window.__pendingComponents || [];
        window.registerVueComponent = function(name, component) {
            if (window.app && typeof window.app.component === 'function' && window.app._context) {
                window.app.component(name, component);
            } else {
                window.__pendingComponents.push([name, component]);
            }
        };

        var app = {
            component: function(name, component) {
                window.registerVueComponent(name, component);
                return app;
            }
        };
        window.app = app;
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    {!! view_event_render('layout.head') !!}
</head>
<body class="min-h-screen bg-[var(--page)] text-[var(--text)] font-sans antialiased">
    {!! view_event_render('layout.body.before') !!}

    <div id="app">
        {{ $slot ?? '' }}
        @yield('content')
    </div>

    {!! view_event_render('layout.body.after') !!}
    @stack('scripts')
    <script>
        if (typeof window.mountApp === 'function') {
            window.mountApp();
        } else {
            window.addEventListener('load', function() {
                if (typeof window.mountApp === 'function') {
                    window.mountApp();
                }
            });
        }
    </script>
</body>
</html>
