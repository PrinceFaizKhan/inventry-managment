<v-row-actions></v-row-actions>

@pushOnce('scripts')
<script type="text/x-template" id="v-row-actions-template">
    <div class="grid-row-actions">
        <button
            v-for="action in actions.filter((item) => item.key !== 'create' && (allowed[item.key] !== false))"
            :key="action.key"
            type="button"
            :aria-label="`${action.label} row`"
            @click="$emit('action', action, row)"
        >
            <svg v-if="action.icon === 'pencil'" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
            <svg v-else-if="action.icon === 'trash'" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
            <span v-else>@{{ action.label }}</span>
        </button>
        <span v-if="!actions.some((item) => item.key !== 'create' && (allowed[item.key] !== false))">—</span>
    </div>
</script>
<script type="module">
    const rowActionsComponent = {
        template: '#v-row-actions-template',
        props: {
            row: { type: Object, required: true },
            actions: { type: Array, default: () => [] },
            allowed: { type: Object, default: () => ({}) }
        },
        emits: ['action']
    };
    app.component('v-row-actions', rowActionsComponent);
    app.component('row-actions', rowActionsComponent);
</script>
@endpushOnce
