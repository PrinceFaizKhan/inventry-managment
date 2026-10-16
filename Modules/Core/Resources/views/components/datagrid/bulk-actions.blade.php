<v-bulk-actions-menu></v-bulk-actions-menu>

@pushOnce('scripts')
<script type="text/x-template" id="v-bulk-actions-menu-template">
    <div v-if="count" class="bulk-toolbar">
        <strong>@{{ count }} selected</strong>
        <div class="bulk-toolbar__right">
            <div class="bulk-menu-wrap">
                <button
                    ref="trigger"
                    type="button"
                    class="bulk-menu-trigger"
                    aria-haspopup="menu"
                    :aria-expanded="open"
                    @click="openMenu"
                >
                    Actions
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                </button>
                <div v-if="open" class="bulk-menu" role="menu">
                    <button
                        v-for="action in availableActions"
                        :key="action.key"
                        type="button"
                        role="menuitem"
                        :class="{ destructive: action.key === 'delete_selected' }"
                        @click="choose(action.key)"
                    >
                        @{{ action.label }}
                    </button>
                    <span v-if="!availableActions.length" class="bulk-menu__empty">No permitted actions</span>
                </div>
            </div>
            <button type="button" class="bulk-clear" aria-label="Clear selection" title="Clear selection" @click="$emit('clear')">
                &times;
            </button>
        </div>
    </div>
</script>
<script type="module">
    const bulkActionsMenuComponent = {
        template: '#v-bulk-actions-menu-template',
        props: {
            count: { type: Number, default: 0 },
            actions: { type: Array, default: () => [] },
            allowed: { type: Object, default: () => ({}) }
        },
        emits: ['action', 'clear'],
        data() {
            return {
                open: false
            };
        },
        computed: {
            availableActions() {
                return (this.actions || []).filter(a => this.allowed[a.key] !== false);
            }
        },
        watch: {
            count(newVal) {
                if (!newVal) this.open = false;
            }
        },
        methods: {
            openMenu() {
                this.open = !this.open;
            },
            choose(key) {
                this.$emit('action', key);
                this.open = false;
            }
        }
    };
    app.component('v-bulk-actions-menu', bulkActionsMenuComponent);
    app.component('bulk-actions-menu', bulkActionsMenuComponent);
</script>
@endpushOnce
