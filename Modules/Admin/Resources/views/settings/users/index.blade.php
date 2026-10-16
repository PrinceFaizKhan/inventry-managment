@extends('admin::layouts.admin')

@section('title', 'Users')
@section('crumb', 'Users')

@section('content')
<div class="users-page space-y-6">
    {!! view_event_render('settings.users.before') !!}

    <v-users></v-users>
    <div class="hidden" aria-hidden="true"><users-list></users-list></div>

    {!! view_event_render('settings.users.after') !!}
</div>

@pushOnce('scripts')
<script type="text/x-template" id="v-users-template">
    <div class="space-y-6">
        <!-- Page Heading -->
        <div class="flex items-center justify-between gap-4 pb-2 border-b border-[var(--border)]">
            <div>
                <p class="eyebrow">Settings / Users</p>
                <h1 class="text-2xl font-bold tracking-tight text-[var(--text)]">Users</h1>
                <p class="page-copy text-sm text-[var(--muted)] mt-1">Manage administrators and staff members with access to your workspace.</p>
            </div>
        </div>

        <!-- Notification Banner -->
        <div v-if="notification" class="p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs rounded-lg font-medium flex items-center justify-between">
            <span>@{{ notification }}</span>
            <button type="button" @click="notification = null" class="text-emerald-600 hover:text-emerald-900">&times;</button>
        </div>

        <!-- Users Table Card -->
        <div class="bg-[var(--card)] border border-[var(--border)] rounded-xl shadow-sm overflow-hidden">
            <!-- Toolbar -->
            <div class="p-4 border-b border-[var(--border)] flex flex-wrap items-center justify-between gap-3 bg-[var(--page)]">
                <div class="flex items-center gap-2 flex-1 min-w-[240px] max-w-md">
                    <div class="relative w-full">
                        <input
                            type="text"
                            v-model="search"
                            @input="debounceFetch"
                            placeholder="Search by name or email..."
                            class="w-full h-9 pl-9 pr-3 rounded-lg border border-[var(--border)] bg-[var(--card)] text-xs text-[var(--text)] focus:outline-none focus:border-[var(--teal)] focus:ring-1 focus:ring-[var(--teal)]"
                        />
                        <svg class="absolute left-3 top-2.5 text-[var(--muted)] pointer-events-none" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    </div>
                </div>

                <div class="flex items-center gap-2 flex-wrap">
                    <!-- Status Filter -->
                    <select
                        v-model="filterStatus"
                        @change="fetchData(1)"
                        class="h-9 px-3 rounded-lg border border-[var(--border)] bg-[var(--card)] text-xs text-[var(--text)] focus:outline-none focus:border-[var(--teal)]"
                    >
                        <option value="">All Statuses</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>

                    <button
                        type="button"
                        @click="resetFilters"
                        class="h-9 px-3 rounded-lg border border-[var(--border)] bg-[var(--card)] text-xs text-[var(--muted)] hover:text-[var(--text)]"
                    >
                        Reset
                    </button>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-[var(--border)] bg-[var(--page)] text-[var(--muted)] font-semibold uppercase tracking-wider">
                            <th class="py-3 px-4">User</th>
                            <th class="py-3 px-4">Email</th>
                            <th class="py-3 px-4">Role</th>
                            <th class="py-3 px-4 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--border)]">
                        <tr v-if="loading" class="animate-pulse">
                            <td colspan="4" class="py-8 text-center text-[var(--muted)]">Loading users...</td>
                        </tr>
                        <tr v-else-if="users.length === 0">
                            <td colspan="4" class="py-12 text-center text-[var(--muted)]">
                                No users found matching your search.
                            </td>
                        </tr>
                        <tr v-else v-for="user in users" :key="user.id" class="hover:bg-[var(--soft)] transition-colors">
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-3">
                                    <span class="w-8 h-8 rounded-full bg-[var(--soft)] text-[var(--teal)] font-bold text-xs grid place-items-center flex-shrink-0">
                                        @{{ initials(user.name) }}
                                    </span>
                                    <span class="font-semibold text-[var(--text)]">@{{ user.name }}</span>
                                </div>
                            </td>
                            <td class="py-3 px-4 text-[var(--muted)] font-mono">@{{ user.email }}</td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-[var(--soft)] text-[var(--teal)]">
                                    @{{ user.role || 'Unassigned' }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span :class="user.status === 'active' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-zinc-50 text-zinc-600 border-zinc-200'" class="px-2 py-0.5 rounded text-[10px] font-semibold border capitalize">
                                    @{{ user.status }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="meta.total > 0" class="p-3 border-t border-[var(--border)] flex items-center justify-between text-xs text-[var(--muted)] bg-[var(--page)]">
                <div>
                    Showing @{{ ((meta.current_page - 1) * meta.per_page) + 1 }} to @{{ Math.min(meta.current_page * meta.per_page, meta.total) }} of @{{ meta.total }} users
                </div>
                <div class="flex items-center gap-1">
                    <button
                        type="button"
                        :disabled="meta.current_page <= 1"
                        @click="fetchData(meta.current_page - 1)"
                        class="px-2.5 py-1 rounded border border-[var(--border)] bg-[var(--card)] disabled:opacity-40 disabled:cursor-not-allowed hover:bg-[var(--soft)]"
                    >
                        Prev
                    </button>
                    <span class="px-2 font-medium text-[var(--text)]">@{{ meta.current_page }} / @{{ meta.last_page }}</span>
                    <button
                        type="button"
                        :disabled="meta.current_page >= meta.last_page"
                        @click="fetchData(meta.current_page + 1)"
                        class="px-2.5 py-1 rounded border border-[var(--border)] bg-[var(--card)] disabled:opacity-40 disabled:cursor-not-allowed hover:bg-[var(--soft)]"
                    >
                        Next
                    </button>
                </div>
            </div>
        </div>
    </div>
</script>

<script type="module">
    const UsersComponent = {
        template: '#v-users-template',
        data() {
            return {
                users: [],
                search: '',
                filterStatus: '',
                loading: false,
                notification: null,
                debounceTimer: null,
                meta: {
                    total: 0,
                    current_page: 1,
                    per_page: 10,
                    last_page: 1,
                },
            };
        },
        mounted() {
            this.fetchData(1);
        },
        methods: {
            initials(name) {
                if (!name) return 'U';
                return name.split(' ').filter(Boolean).map(p => p[0]).slice(0, 2).join('').toUpperCase();
            },
            debounceFetch() {
                clearTimeout(this.debounceTimer);
                this.debounceTimer = setTimeout(() => {
                    this.fetchData(1);
                }, 300);
            },
            resetFilters() {
                this.search = '';
                this.filterStatus = '';
                this.fetchData(1);
            },
            async fetchData(page = 1) {
                this.loading = true;
                try {
                    const params = new URLSearchParams();
                    params.append('page', page);
                    if (this.search.trim()) params.append('search', this.search.trim());
                    if (this.filterStatus) params.append('filter[status]', this.filterStatus);

                    const res = await this.$axios.get('/api/grids/users?' + params.toString());
                    this.users = res.data || [];
                    this.meta = {
                        total: res.meta?.total || 0,
                        current_page: res.meta?.current_page || 1,
                        per_page: res.meta?.per_page || 10,
                        last_page: res.meta?.last_page || 1,
                    };
                } catch (e) {
                    console.error(e);
                } finally {
                    this.loading = false;
                }
            },
        },
    };

    app.component('v-users', UsersComponent);
    app.component('users-list', UsersComponent);
</script>
@endPushOnce
@endsection
