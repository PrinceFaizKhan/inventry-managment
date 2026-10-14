@extends('admin::layouts.admin')

@section('title', __('app.dashboard') ?? 'Dashboard')
@section('crumb', __('app.dashboard') ?? 'Dashboard')

@section('content')
<div class="dashboard-page space-y-6">
    <!-- Welcome Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-2 border-b border-[var(--border)]">
        <div>
            <p class="eyebrow">Haseen Telecom · Admin Workspace</p>
            <h1 class="text-2xl font-bold tracking-tight text-[var(--text)]">
                Welcome, {{ auth()->user()?->name ?? 'Admin' }}!
            </h1>
            <p class="page-copy text-sm text-[var(--muted)] mt-1">
                UnoPim Admin workspace overview, catalog metrics, and external pluggable connectors.
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

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Attributes Count -->
        <div class="p-5 rounded-xl border border-[var(--border)] bg-[var(--card)] shadow-sm hover:border-[var(--teal)] transition-colors">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold uppercase tracking-wider text-[var(--muted)]">Catalog Attributes</span>
                <span class="w-8 h-8 rounded-lg bg-[var(--soft)] text-[var(--teal)] grid place-items-center">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 21v-7"/><path d="M4 10V3"/><path d="M12 21v-9"/><path d="M12 8V3"/><path d="M20 21v-5"/><path d="M20 12V3"/><path d="M1 14h6"/><path d="M9 8h6"/><path d="M17 16h6"/></svg>
                </span>
            </div>
            <div class="text-3xl font-bold tracking-tight text-[var(--text)]">{{ $stats['total_attributes'] ?? 0 }}</div>
            <div class="mt-2 flex items-center text-xs text-[var(--muted)] gap-1">
                <span class="font-semibold text-[var(--teal)]">{{ $stats['filterable_attributes'] ?? 0 }}</span> filterable attributes configured
            </div>
        </div>

        <!-- Users Count -->
        <div class="p-5 rounded-xl border border-[var(--border)] bg-[var(--card)] shadow-sm hover:border-[var(--teal)] transition-colors">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold uppercase tracking-wider text-[var(--muted)]">Staff & Administrators</span>
                <span class="w-8 h-8 rounded-lg bg-[var(--soft)] text-[var(--teal)] grid place-items-center">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </span>
            </div>
            <div class="text-3xl font-bold tracking-tight text-[var(--text)]">{{ $stats['total_users'] ?? 0 }}</div>
            <div class="mt-2 flex items-center text-xs text-[var(--muted)] gap-1">
                <span>Active platform operators</span>
            </div>
        </div>

        <!-- Roles Count -->
        <div class="p-5 rounded-xl border border-[var(--border)] bg-[var(--card)] shadow-sm hover:border-[var(--teal)] transition-colors">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold uppercase tracking-wider text-[var(--muted)]">Access Roles</span>
                <span class="w-8 h-8 rounded-lg bg-[var(--soft)] text-[var(--teal)] grid place-items-center">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                </span>
            </div>
            <div class="text-3xl font-bold tracking-tight text-[var(--text)]">{{ $stats['total_roles'] ?? 0 }}</div>
            <div class="mt-2 flex items-center text-xs text-[var(--muted)] gap-1">
                <span>Granular permission profiles</span>
            </div>
        </div>

        <!-- Connectors Count -->
        <div class="p-5 rounded-xl border border-[var(--border)] bg-[var(--card)] shadow-sm hover:border-[var(--teal)] transition-colors">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold uppercase tracking-wider text-[var(--muted)]">External Connectors</span>
                <span class="w-8 h-8 rounded-lg bg-[var(--soft)] text-[var(--teal)] grid place-items-center">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="8" height="8" x="2" y="2" rx="2"/><path d="M14 2c1.1 0 2 .9 2 2v4c0 1.1-.9 2-2 2"/><path d="M20 2c1.1 0 2 .9 2 2v4c0 1.1-.9 2-2 2"/><rect width="8" height="8" x="2" y="14" rx="2"/><path d="M6 10v4"/><path d="M14 14c1.1 0 2 .9 2 2v4c0 1.1-.9 2-2 2"/><path d="M20 14c1.1 0 2 .9 2 2v4c0 1.1-.9 2-2 2"/></svg>
                </span>
            </div>
            <div class="text-3xl font-bold tracking-tight text-[var(--teal)]">2</div>
            <div class="mt-2 flex items-center text-xs text-[var(--muted)] gap-1">
                <span class="inline-block w-2 h-2 rounded-full bg-emerald-500"></span>
                <span>Pluggable Git packages ready</span>
            </div>
        </div>
    </div>

    <!-- External Pluggable Connectors Section -->
    <div class="p-6 rounded-xl border border-[var(--border)] bg-[var(--card)] shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-lg font-bold text-[var(--text)] tracking-tight">External Pluggable Connectors</h2>
                <p class="text-xs text-[var(--muted)] mt-0.5">
                    Independent modular packages decoupled from Admin core, ready to be extracted into standalone Git repositories.
                </p>
            </div>
            <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
                Decoupled Architecture
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
            <!-- Sales Connector Card -->
            <div class="p-4 rounded-lg border border-[var(--border)] bg-[var(--page)] flex items-start justify-between gap-3">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-lg bg-[var(--card)] border border-[var(--border)] grid place-items-center text-[var(--teal)] flex-shrink-0">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="font-semibold text-sm text-[var(--text)]">Sales Connector</h3>
                            <span class="text-[10px] uppercase font-bold tracking-wider px-2 py-0.5 rounded bg-[var(--soft)] text-[var(--teal)]">Pluggable</span>
                        </div>
                        <p class="text-xs text-[var(--muted)] mt-1">Package: <code>inventry/sales</code></p>
                        <p class="text-xs text-[var(--muted)] mt-0.5">Isolated ServiceProvider with independent menu, permissions, and routes.</p>
                    </div>
                </div>
                <div class="text-right">
                    <span class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-600">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active
                    </span>
                </div>
            </div>

            <!-- Inventory Connector Card -->
            <div class="p-4 rounded-lg border border-[var(--border)] bg-[var(--page)] flex items-start justify-between gap-3">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-lg bg-[var(--card)] border border-[var(--border)] grid place-items-center text-[var(--teal)] flex-shrink-0">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="font-semibold text-sm text-[var(--text)]">Inventory Connector</h3>
                            <span class="text-[10px] uppercase font-bold tracking-wider px-2 py-0.5 rounded bg-[var(--soft)] text-[var(--teal)]">Pluggable</span>
                        </div>
                        <p class="text-xs text-[var(--muted)] mt-1">Package: <code>inventry/inventory</code></p>
                        <p class="text-xs text-[var(--muted)] mt-0.5">Isolated ServiceProvider with independent menu, permissions, and routes.</p>
                    </div>
                </div>
                <div class="text-right">
                    <span class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-600">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Navigation Shortcuts -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <a href="{{ url('catalog/attributes') }}" class="p-4 rounded-xl border border-[var(--border)] bg-[var(--card)] hover:border-[var(--teal)] transition-all group flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="w-10 h-10 rounded-lg bg-[var(--soft)] text-[var(--teal)] grid place-items-center group-hover:scale-105 transition-transform">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 21v-7"/><path d="M4 10V3"/><path d="M12 21v-9"/><path d="M12 8V3"/><path d="M20 21v-5"/><path d="M20 12V3"/><path d="M1 14h6"/><path d="M9 8h6"/><path d="M17 16h6"/></svg>
                </span>
                <div>
                    <strong class="block text-sm text-[var(--text)] font-semibold">Manage Attributes</strong>
                    <span class="text-xs text-[var(--muted)]">Catalog attribute definitions & types</span>
                </div>
            </div>
            <svg class="text-[var(--muted)] group-hover:text-[var(--teal)] transition-colors" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
        </a>

        <a href="{{ url('settings/users') }}" class="p-4 rounded-xl border border-[var(--border)] bg-[var(--card)] hover:border-[var(--teal)] transition-all group flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="w-10 h-10 rounded-lg bg-[var(--soft)] text-[var(--teal)] grid place-items-center group-hover:scale-105 transition-transform">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                </span>
                <div>
                    <strong class="block text-sm text-[var(--text)] font-semibold">Manage Users</strong>
                    <span class="text-xs text-[var(--muted)]">User accounts & permissions</span>
                </div>
            </div>
            <svg class="text-[var(--muted)] group-hover:text-[var(--teal)] transition-colors" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
        </a>

        <a href="{{ url('settings/roles/create') }}" class="p-4 rounded-xl border border-[var(--border)] bg-[var(--card)] hover:border-[var(--teal)] transition-all group flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="w-10 h-10 rounded-lg bg-[var(--soft)] text-[var(--teal)] grid place-items-center group-hover:scale-105 transition-transform">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                </span>
                <div>
                    <strong class="block text-sm text-[var(--text)] font-semibold">Configure Roles</strong>
                    <span class="text-xs text-[var(--muted)]">Role-based access control</span>
                </div>
            </div>
            <svg class="text-[var(--muted)] group-hover:text-[var(--teal)] transition-colors" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
        </a>
    </div>
</div>
@endsection
