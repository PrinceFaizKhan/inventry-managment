<v-grid-toolbar></v-grid-toolbar>

@pushOnce('scripts')
<script type="text/x-template" id="v-grid-toolbar-template">
    <div class="grid-toolbar">
        <label class="grid-search">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
            <input :value="search" :placeholder="placeholder" @input="$emit('search', $event.target.value)"/>
        </label>
        <button
            class="grid-filter-button"
            :class="{ active: showFilters }"
            type="button"
            :aria-expanded="showFilters"
            @click="$emit('toggleFilters')"
        >
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="21" x2="14" y1="4" y2="4"/><line x1="10" x2="3" y1="4" y2="4"/><line x1="21" x2="12" y1="12" y2="12"/><line x1="8" x2="3" y1="12" y2="12"/><line x1="21" x2="16" y1="20" y2="20"/><line x1="12" x2="3" y1="20" y2="20"/><line x1="14" x2="14" y1="2" y2="6"/><line x1="8" x2="8" y1="10" y2="14"/><line x1="16" x2="16" y1="18" y2="22"/></svg>
            Filter
            <span v-if="filterCount">@{{ filterCount }}</span>
        </button>
        <span class="grid-result-count">@{{ total }} @{{ total === 1 ? 'result' : 'results' }}</span>
    </div>
</script>
<script type="module">
    const gridToolbarComponent = {
        template: '#v-grid-toolbar-template',
        props: {
            search: { type: String, default: '' },
            placeholder: { type: String, default: 'Search records...' },
            filterCount: { type: Number, default: 0 },
            showFilters: { type: Boolean, default: false },
            total: { type: Number, default: 0 }
        },
        emits: ['search', 'toggleFilters']
    };
    app.component('v-grid-toolbar', gridToolbarComponent);
    app.component('grid-toolbar', gridToolbarComponent);
</script>
@endpushOnce
