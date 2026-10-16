@extends('admin::layouts.admin')

@section('title', __('catalog.attributes::app.title') ?? 'Attributes')
@section('crumb', __('catalog.attributes::app.title') ?? 'Attributes')

@section('content')
<div class="attributes-page">
    <div class="attributes-page__heading flex justify-between items-center mb-6">
        <div>
            <p class="eyebrow">Catalog / Attributes</p>
            <h1 class="text-2xl font-bold tracking-tight text-[var(--text)]">Attributes</h1>
            <p class="page-copy text-sm text-[var(--muted)] mt-1">Define and manage reusable product attributes for your catalog.</p>
        </div>
        <div class="flex items-center gap-2">
            {!! view_event_render('catalog.attributes.header.actions') !!}
            <a href="{{ url('catalog/attributes/create') }}" class="button button--primary inline-flex items-center gap-2">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                <span>Create Attribute</span>
            </a>
        </div>
    </div>

    {!! view_event_render('catalog.attributes.grid.before') !!}

    <v-attributes-list></v-attributes-list>
    <div class="hidden" aria-hidden="true"><attributes-list></attributes-list></div>

    {!! view_event_render('catalog.attributes.grid.after') !!}
</div>

@pushOnce('scripts')
<script type="text/x-template" id="v-attributes-list-template">
    <div class="bg-[var(--card)] border border-[var(--border)] rounded-xl shadow-sm overflow-hidden">
        <!-- Notification Banner -->
        <div v-if="notification" class="p-3 bg-emerald-50 border-b border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center justify-between">
            <span>@{{ notification }}</span>
            <button type="button" @click="notification = null" class="text-emerald-600 hover:text-emerald-900">&times;</button>
        </div>

        <!-- Toolbar: Search & Filters -->
        <div class="p-4 border-b border-[var(--border)] flex flex-wrap items-center justify-between gap-3 bg-[var(--page)]">
            <div class="flex items-center gap-2 flex-1 min-w-[240px] max-w-md">
                <div class="relative w-full">
                    <input
                        type="text"
                        v-model="search"
                        @input="debounceFetch"
                        placeholder="Search by code or name..."
                        class="w-full h-9 pl-9 pr-3 rounded-lg border border-[var(--border)] bg-[var(--card)] text-xs text-[var(--text)] focus:outline-none focus:border-[var(--teal)] focus:ring-1 focus:ring-[var(--teal)]"
                    />
                    <svg class="absolute left-3 top-2.5 text-[var(--muted)] pointer-events-none" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                </div>
            </div>

            <div class="flex items-center gap-2 flex-wrap">
                <!-- Type Filter -->
                <select
                    v-model="filterType"
                    @change="fetchData(1)"
                    class="h-9 px-3 rounded-lg border border-[var(--border)] bg-[var(--card)] text-xs text-[var(--text)] focus:outline-none focus:border-[var(--teal)]"
                >
                    <option value="">All Types</option>
                    <option value="text">Text</option>
                    <option value="textarea">Textarea</option>
                    <option value="boolean">Boolean</option>
                    <option value="checkbox">Checkbox</option>
                    <option value="select">Select</option>
                    <option value="multiselect">Multiselect</option>
                    <option value="image">Image</option>
                    <option value="gallery">Gallery</option>
                    <option value="radio">Radio</option>
                    <option value="money">Money</option>
                </select>

                <!-- Filterable Filter -->
                <select
                    v-model="filterFilterable"
                    @change="fetchData(1)"
                    class="h-9 px-3 rounded-lg border border-[var(--border)] bg-[var(--card)] text-xs text-[var(--text)] focus:outline-none focus:border-[var(--teal)]"
                >
                    <option value="">All Filterable</option>
                    <option value="1">Filterable: Yes</option>
                    <option value="0">Filterable: No</option>
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

        <!-- Table Data -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-[var(--border)] bg-[var(--page)] text-[var(--muted)] font-semibold uppercase tracking-wider">
                        <th class="py-3 px-4">Code</th>
                        <th class="py-3 px-4">Name</th>
                        <th class="py-3 px-4">Type</th>
                        <th class="py-3 px-4 text-center">Required</th>
                        <th class="py-3 px-4 text-center">Filterable</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[var(--border)]">
                    <tr v-if="loading" class="animate-pulse">
                        <td colspan="6" class="py-8 text-center text-[var(--muted)]">Loading attributes...</td>
                    </tr>
                    <tr v-else-if="attributes.length === 0">
                        <td colspan="6" class="py-12 text-center text-[var(--muted)]">
                            <div class="flex flex-col items-center justify-center gap-2">
                                <p class="text-sm font-semibold text-[var(--text)]">No attributes found</p>
                                <p class="text-xs">Try adjusting your search criteria or create a new attribute.</p>
                                <a href="{{ url('catalog/attributes/create') }}" class="button button--primary text-xs mt-2">
                                    Create Attribute
                                </a>
                            </div>
                        </td>
                    </tr>
                    <tr v-else v-for="item in attributes" :key="item.id" class="hover:bg-[var(--soft)] transition-colors">
                        <td class="py-3 px-4 font-mono font-semibold text-[var(--text)]">
                            <a :href="'/catalog/attributes/' + item.id + '/edit'" class="hover:text-[var(--teal)] hover:underline">
                                @{{ item.code }}
                            </a>
                        </td>
                        <td class="py-3 px-4 text-[var(--text)] font-medium">@{{ item.name }}</td>
                        <td class="py-3 px-4">
                            <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-[var(--soft)] text-[var(--teal)]">
                                @{{ item.type }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-center">
                            <span :class="item.required ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-zinc-50 text-zinc-600 border-zinc-200'" class="px-2 py-0.5 rounded text-[10px] font-semibold border">
                                @{{ item.required ? 'Yes' : 'No' }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-center">
                            <span :class="item.is_filterable ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-zinc-50 text-zinc-600 border-zinc-200'" class="px-2 py-0.5 rounded text-[10px] font-semibold border">
                                @{{ item.is_filterable ? 'Yes' : 'No' }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-right">
                            <div class="inline-flex items-center gap-1.5">
                                <a :href="'/catalog/attributes/' + item.id + '/edit'" class="px-2.5 py-1 rounded border border-[var(--border)] bg-[var(--card)] hover:border-[var(--teal)] text-[var(--text)] hover:text-[var(--teal)] transition-colors">
                                    Edit
                                </a>
                                <button type="button" @click="confirmDelete(item)" class="px-2.5 py-1 rounded border border-red-200 bg-red-50 text-red-600 hover:bg-red-100 transition-colors">
                                    Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination Footer -->
        <div v-if="meta.total > 0" class="p-3 border-t border-[var(--border)] flex items-center justify-between text-xs text-[var(--muted)] bg-[var(--page)]">
            <div>
                Showing @{{ ((meta.current_page - 1) * meta.per_page) + 1 }} to @{{ Math.min(meta.current_page * meta.per_page, meta.total) }} of @{{ meta.total }} attributes
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
</script>

<script type="module">
    const AttributesList = {
        template: '#v-attributes-list-template',
        data() {
            return {
                attributes: [],
                search: '',
                filterType: '',
                filterFilterable: '',
                loading: false,
                notification: null,
                debounceTimer: null,
                meta: {
                    total: 0,
                    current_page: 1,
                    per_page: 15,
                    last_page: 1,
                },
            };
        },
        mounted() {
            this.fetchData(1);
        },
        methods: {
            debounceFetch() {
                clearTimeout(this.debounceTimer);
                this.debounceTimer = setTimeout(() => {
                    this.fetchData(1);
                }, 300);
            },
            resetFilters() {
                this.search = '';
                this.filterType = '';
                this.filterFilterable = '';
                this.fetchData(1);
            },
            async fetchData(page = 1) {
                this.loading = true;
                try {
                    const params = new URLSearchParams();
                    params.append('page', page);
                    if (this.search.trim()) params.append('search', this.search.trim());
                    if (this.filterType) params.append('filter[type]', this.filterType);
                    if (this.filterFilterable !== '') params.append('filter[is_filterable]', this.filterFilterable);

                    const res = await this.$axios.get('/api/grids/attributes?' + params.toString());
                    this.attributes = res.data || [];
                    this.meta = {
                        total: res.meta?.total || 0,
                        current_page: res.meta?.current_page || 1,
                        per_page: res.meta?.per_page || 15,
                        last_page: res.meta?.last_page || 1,
                    };
                } catch (e) {
                    console.error(e);
                } finally {
                    this.loading = false;
                }
            },
            async confirmDelete(item) {
                if (!confirm(`Are you sure you want to delete attribute "${item.name}" (${item.code})?`)) {
                    return;
                }
                try {
                    await this.$axios.delete(`/api/catalog/attributes/${item.id}`);
                    this.notification = `Attribute "${item.name}" was deleted successfully.`;
                    this.fetchData(this.meta.current_page);
                } catch (e) {
                    alert(e.message || 'Failed to delete attribute.');
                }
            },
        },
    };

    app.component('v-attributes-list', AttributesList);
    app.component('attributes-list', AttributesList);
</script>
@endPushOnce
@endsection
