@extends('admin::layouts.admin')

@section('title', 'Users')
@section('crumb', 'Users')

@section('content')
<div class="users-page space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-[var(--border)]">
        <div>
            <p class="eyebrow">Settings / Users</p>
            <h1 class="text-2xl font-bold tracking-tight text-[var(--text)]">Users</h1>
            <p class="page-copy text-sm text-[var(--muted)] mt-1">Manage administrators and staff members with access to your workspace.</p>
        </div>
    </div>

    {!! view_event_render('settings.users.before') !!}

    <x-admin::datagrid
        src="/api/grids/users"
        search-placeholder="Search by name or email..."
    >
        <template #cell-name="{ row }">
            <div class="flex items-center gap-2.5">
                <span class="w-7 h-7 rounded-full bg-[var(--soft)] text-[var(--teal)] font-bold text-[11px] grid place-items-center flex-shrink-0">
                    @{{ (row.name || 'U').charAt(0).toUpperCase() }}
                </span>
                <span class="font-semibold text-[var(--text)]">@{{ row.name }}</span>
            </div>
        </template>
        <template #cell-email="{ row }">
            <span class="font-mono text-xs text-[var(--muted)]">@{{ row.email }}</span>
        </template>
        <template #cell-role="{ row }">
            <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-[var(--soft)] text-[var(--teal)]">
                @{{ row.role || 'Unassigned' }}
            </span>
        </template>
        <template #cell-status="{ row }">
            <span
                class="px-2 py-0.5 rounded text-[10px] font-semibold border capitalize"
                :class="row.status === 'active' ? 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/50 dark:text-emerald-300' : 'bg-zinc-50 text-zinc-600 border-zinc-200 dark:bg-zinc-900 dark:text-zinc-400'"
            >
                @{{ row.status }}
            </span>
        </template>
    </x-admin::datagrid>

    <div class="hidden" aria-hidden="true"><users-list></users-list></div>

    {!! view_event_render('settings.users.after') !!}
</div>
@endsection
