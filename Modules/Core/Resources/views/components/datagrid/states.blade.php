<v-grid-states></v-grid-states>

@pushOnce('scripts')
<script type="text/x-template" id="v-grid-states-template">
    <div class="grid-state" :class="`grid-state--${type}`">
        <span v-if="type === 'loading'" class="grid-spinner"></span>
        <strong>
            @{{ type === 'loading' ? 'Loading results…' : type === 'empty' ? 'No results found' : 'Unable to load results' }}
        </strong>
        <p v-if="type === 'empty'">Try changing your search or filters.</p>
        <p v-else-if="type === 'error'">@{{ message }}</p>
    </div>
</script>
<script type="module">
    const gridStatesComponent = {
        template: '#v-grid-states-template',
        props: {
            type: { type: String, default: 'empty' },
            message: { type: String, default: '' }
        }
    };
    app.component('v-grid-states', gridStatesComponent);
    app.component('grid-states', gridStatesComponent);
</script>
@endpushOnce
