<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ in_array(app()->getLocale(), ['ur', 'ur-IN']) ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Sign In · Haseen Telecom' }}</title>

    {!! view_event_render('unopim.admin.layout.head.before') !!}

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Noto+Sans+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">

    {!! view_event_render('unopim.admin.layout.head') !!}
</head>
<body class="min-h-screen bg-[var(--page)] text-[var(--text)] font-sans antialiased">
    {!! view_event_render('unopim.admin.layout.body.before') !!}

    <div id="app" class="min-h-screen flex flex-col justify-center items-center py-12 px-4 sm:px-6 lg:px-8">
        {{ $slot ?? '' }}
        @yield('content')
    </div>

    {!! view_event_render('unopim.admin.layout.body.after') !!}
    @stack('scripts')
</body>
</html>
