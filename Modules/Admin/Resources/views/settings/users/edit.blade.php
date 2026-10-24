@extends('admin::layouts.admin')

@section('title', 'Edit User')
@section('crumb', 'Edit User')

@section('content')
<div class="user-edit-page space-y-6">
    {!! view_event_render('settings.users.edit.before') !!}

    <v-user-form
        mode="edit"
        :initial-user='@json($user)'
        :roles='@json($roles)'
        back-url="{{ route('settings.users') }}"
    ></v-user-form>

    <div class="hidden" aria-hidden="true">
        <user-edit-form></user-edit-form>
    </div>

    @include('admin::settings.users.form')

    {!! view_event_render('settings.users.edit.after') !!}
</div>
@endsection
