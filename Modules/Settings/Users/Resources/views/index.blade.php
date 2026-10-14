@extends('admin::layouts.admin')

@section('title', __('settings.users::app.title') ?? 'Users')
@section('crumb', __('settings.users::app.title') ?? 'Users')

@section('content')
<div class="users-page">
    {!! view_event_render('settings.users.before') !!}

    <users-list></users-list>

    {!! view_event_render('settings.users.after') !!}
</div>
@endsection
