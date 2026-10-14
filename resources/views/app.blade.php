<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ in_array(app()->getLocale(), ['ur', 'ur-IN']) ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Haseen Telecom · Admin</title>
    {!! view_event_render('layout.head') !!}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    {!! view_event_render('layout.body.before') !!}
    <div id="app"></div>
    {!! view_event_render('layout.body.after') !!}
</body>
</html>
