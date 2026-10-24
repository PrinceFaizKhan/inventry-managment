@extends('admin::layouts.admin')

@section('title', 'Create User')
@section('crumb', 'Create User')

@section('content')
<div class="user-create-page space-y-6">
    {!! view_event_render('settings.users.create.before') !!}

    <v-user-form
        mode="create"
        :roles='@json($roles)'
        back-url="{{ route('settings.users') }}"
    ></v-user-form>

    <div class="hidden" aria-hidden="true">
        <user-create-form></user-create-form>
    </div>

    @include('admin::settings.users.form')

    {!! view_event_render('settings.users.create.after') !!}
</div>
@endsection
