@extends('admin::layouts.admin')

@section('title', __('app.dashboard') ?? 'Dashboard')
@section('crumb', __('app.dashboard') ?? 'Dashboard')

@section('content')
<div class="dashboard-page flex flex-col gap-8">
    <!-- Welcome Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-[var(--border)]">
        <div>
            <p class="eyebrow">Haseen Telecom · Admin Workspace</p>
            <h1 class="text-2xl font-bold tracking-tight text-[var(--text)]">
                Welcome, {{ auth()->user()?->name ?? 'Admin' }}!
            </h1>
            <p class="page-copy text-sm text-[var(--muted)] mt-1">
                Admin workspace overview, catalog metrics, and system configuration.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ url('catalog/attributes') }}" class="button button--outline text-xs">
                <span>{{ __('nav.catalog') ?? 'Catalog' }}</span>
            </a>
            <a href="{{ url('catalog/attributes/create') }}" class="button button--primary text-xs inline-flex items-center gap-1.5">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                <span>{{ __('action.create') ?? 'Create' }} {{ __('catalog.attributes::app.title') ?? 'Attribute' }}</span>
            </a>
        </div>
    </div>

    <!-- Section: Overview Metrics -->
    <div class="flex flex-col gap-3">
        <div class="flex items-center justify-between">
            <h2 class="text-xs font-semibold uppercase tracking-wider text-[var(--muted)]">Platform Overview</h2>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Attributes Count -->
            <div class="p-6 rounded-xl border border-[var(--border)] bg-[var(--card)] shadow-xs hover:border-[var(--teal)] hover:shadow-sm transition-all flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-semibold uppercase tracking-wider text-[var(--muted)]">Catalog Attributes</span>
                        <span class="w-9 h-9 rounded-lg bg-[var(--soft)] text-[var(--teal)] grid place-items-center flex-shrink-0">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 21v-7"/><path d="M4 10V3"/><path d="M12 21v-9"/><path d="M12 8V3"/><path d="M20 21v-5"/><path d="M20 12V3"/><path d="M1 14h6"/><path d="M9 8h6"/><path d="M17 16h6"/></svg>
                        </span>
                    </div>
                    <div class="text-3xl font-bold tracking-tight text-[var(--text)]">{{ $stats['total_attributes'] ?? 0 }}</div>
                </div>
                <div class="mt-4 pt-3 border-t border-[var(--border)] flex items-center text-xs text-[var(--muted)] gap-1.5">
                    <span class="font-semibold text-[var(--teal)]">{{ $stats['filterable_attributes'] ?? 0 }}</span> filterable attributes configured
                </div>
            </div>

            <!-- Users Count -->
            <div class="p-6 rounded-xl border border-[var(--border)] bg-[var(--card)] shadow-xs hover:border-[var(--teal)] hover:shadow-sm transition-all flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-semibold uppercase tracking-wider text-[var(--muted)]">Staff & Administrators</span>
                        <span class="w-9 h-9 rounded-lg bg-[var(--soft)] text-[var(--teal)] grid place-items-center flex-shrink-0">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        </span>
                    </div>
                    <div class="text-3xl font-bold tracking-tight text-[var(--text)]">{{ $stats['total_users'] ?? 0 }}</div>
                </div>
                <div class="mt-4 pt-3 border-t border-[var(--border)] flex items-center text-xs text-[var(--muted)] gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block"></span>
                    <span>Active platform operators</span>
                </div>
            </div>

            <!-- Roles Count -->
            <div class="p-6 rounded-xl border border-[var(--border)] bg-[var(--card)] shadow-xs hover:border-[var(--teal)] hover:shadow-sm transition-all flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-semibold uppercase tracking-wider text-[var(--muted)]">Access Roles</span>
                        <span class="w-9 h-9 rounded-lg bg-[var(--soft)] text-[var(--teal)] grid place-items-center flex-shrink-0">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        </span>
                    </div>
                    <div class="text-3xl font-bold tracking-tight text-[var(--text)]">{{ $stats['total_roles'] ?? 0 }}</div>
                </div>
                <div class="mt-4 pt-3 border-t border-[var(--border)] flex items-center text-xs text-[var(--muted)] gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-sky-500 inline-block"></span>
                    <span>Granular permission profiles</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Section: Quick Navigation Shortcuts -->
    <div class="flex flex-col gap-3">
        <div class="flex items-center justify-between">
            <h2 class="text-xs font-semibold uppercase tracking-wider text-[var(--muted)]">Quick Shortcuts</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <a href="{{ url('catalog/attributes') }}" class="p-5 rounded-xl border border-[var(--border)] bg-[var(--card)] hover:border-[var(--teal)] hover:shadow-sm transition-all group flex items-center justify-between">
                <div class="flex items-center gap-3.5">
                    <span class="w-10 h-10 rounded-lg bg-[var(--soft)] text-[var(--teal)] grid place-items-center group-hover:scale-105 transition-transform flex-shrink-0">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 21v-7"/><path d="M4 10V3"/><path d="M12 21v-9"/><path d="M12 8V3"/><path d="M20 21v-5"/><path d="M20 12V3"/><path d="M1 14h6"/><path d="M9 8h6"/><path d="M17 16h6"/></svg>
                    </span>
                    <div>
                        <strong class="block text-sm text-[var(--text)] font-semibold group-hover:text-[var(--teal)] transition-colors">Manage Attributes</strong>
                        <span class="text-xs text-[var(--muted)] mt-0.5 block">Catalog attribute definitions & types</span>
                    </div>
                </div>
                <svg class="text-[var(--muted)] group-hover:text-[var(--teal)] group-hover:translate-x-1 transition-all flex-shrink-0" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
            </a>

            <a href="{{ url('settings/users') }}" class="p-5 rounded-xl border border-[var(--border)] bg-[var(--card)] hover:border-[var(--teal)] hover:shadow-sm transition-all group flex items-center justify-between">
                <div class="flex items-center gap-3.5">
                    <span class="w-10 h-10 rounded-lg bg-[var(--soft)] text-[var(--teal)] grid place-items-center group-hover:scale-105 transition-transform flex-shrink-0">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                    </span>
                    <div>
                        <strong class="block text-sm text-[var(--text)] font-semibold group-hover:text-[var(--teal)] transition-colors">Manage Users</strong>
                        <span class="text-xs text-[var(--muted)] mt-0.5 block">User accounts & permissions</span>
                    </div>
                </div>
                <svg class="text-[var(--muted)] group-hover:text-[var(--teal)] group-hover:translate-x-1 transition-all flex-shrink-0" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
            </a>

            <a href="{{ url('settings/roles/create') }}" class="p-5 rounded-xl border border-[var(--border)] bg-[var(--card)] hover:border-[var(--teal)] hover:shadow-sm transition-all group flex items-center justify-between">
                <div class="flex items-center gap-3.5">
                    <span class="w-10 h-10 rounded-lg bg-[var(--soft)] text-[var(--teal)] grid place-items-center group-hover:scale-105 transition-transform flex-shrink-0">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    </span>
                    <div>
                        <strong class="block text-sm text-[var(--text)] font-semibold group-hover:text-[var(--teal)] transition-colors">Configure Roles</strong>
                        <span class="text-xs text-[var(--muted)] mt-0.5 block">Role-based access control</span>
                    </div>
                </div>
                <svg class="text-[var(--muted)] group-hover:text-[var(--teal)] group-hover:translate-x-1 transition-all flex-shrink-0" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
            </a>
        </div>
    </div>
</div>
@endsection
