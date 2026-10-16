<v-filter-drawer></v-filter-drawer>

@pushOnce('scripts')
<script type="text/x-template" id="v-filter-drawer-template">
    <div class="filter-drawer-layer" role="presentation" @click.self="$emit('close')">
        <aside class="filter-drawer" role="dialog" aria-modal="true">
            <header class="filter-drawer__header">
                <h2>Filters</h2>
                <button type="button" class="filter-drawer__close" aria-label="Close filters" @click="$emit('close')">
                    &times;
                </button>
            </header>
            <div class="filter-drawer__body">
                <div v-for="filter in filters" :key="filter.key" class="filter-drawer__field">
                    <label :for="`filter-${filter.key}`">@{{ filter.label }}</label>
                    <select
                        :id="`filter-${filter.key}`"
                        v-model="draft[filter.key]"
                    >
                        <option value="">All</option>
                        <option
                            v-for="opt in (options[filter.key] || [])"
                            :key="opt.value"
                            :value="opt.value"
                        >
                            @{{ opt.label }}
                        </option>
                    </select>
                </div>
            </div>
            <footer class="filter-drawer__footer">
                <button type="button" class="button button--outline" @click="resetDraft">
                    Reset
                </button>
                <button type="button" class="button button--primary" @click="applyDraft">
                    Apply
                </button>
            </footer>
        </aside>
    </div>
</script>
<script type="module">
    const filterDrawerComponent = {
        template: '#v-filter-drawer-template',
        props: {
            filters: { type: Array, default: () => [] },
            values: { type: Object, default: () => ({}) },
            options: { type: Object, default: () => ({}) }
        },
        emits: ['close', 'apply'],
        data() {
            return {
                draft: { ...(this.values || {}) }
            };
        },
        methods: {
            resetDraft() {
                this.draft = {};
                this.$emit('apply', {});
            },
            applyDraft() {
                this.$emit('apply', { ...this.draft });
            }
        }
    };
    app.component('v-filter-drawer', filterDrawerComponent);
    app.component('filter-drawer', filterDrawerComponent);
</script>
@endpushOnce
