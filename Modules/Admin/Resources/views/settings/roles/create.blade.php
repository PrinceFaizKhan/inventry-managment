@extends('admin::layouts.admin')

@section('title', 'Create Role')
@section('crumb', 'Create Role')

@section('content')
<div class="roles-page">
    {!! view_event_render('settings.roles.create.before') !!}

    <roles-form></roles-form>

    {!! view_event_render('settings.roles.create.after') !!}
</div>
@endsection
