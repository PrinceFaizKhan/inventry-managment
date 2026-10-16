<v-grid-pagination></v-grid-pagination>

@pushOnce('scripts')
<script type="text/x-template" id="v-grid-pagination-template">
    <footer class="grid-pagination">
        <p v-if="total">Showing @{{ from }}–@{{ to }} of @{{ total }}</p>
        <p v-else>Showing 0 results</p>
        <div class="grid-page-controls">
            <div class="grid-page-size">
                <span>Per page</span>
                <select :value="perPage" @change="$emit('pageSize', Number($event.target.value))">
                    <option v-for="size in pageSizes" :key="size" :value="size">@{{ size }}</option>
                </select>
            </div>
            <div class="grid-page-navigation">
                <button type="button" aria-label="Previous page" :disabled="currentPage <= 1 || total === 0" @click="$emit('page', currentPage - 1)">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
                </button>
                <form @submit.prevent="submitPage">
                    <input v-model="pageInput" aria-label="Page number" inputmode="numeric" autocomplete="off"/>
                    <span>/ @{{ lastPage }}</span>
                </form>
                <button type="button" aria-label="Next page" :disabled="currentPage >= lastPage || total === 0" @click="$emit('page', currentPage + 1)">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                </button>
            </div>
        </div>
    </footer>
</script>
<script type="module">
    const gridPaginationComponent = {
        template: '#v-grid-pagination-template',
        props: {
            currentPage: { type: Number, default: 1 },
            lastPage: { type: Number, default: 1 },
            perPage: { type: Number, default: 10 },
            pageSizes: { type: Array, default: () => [10, 25, 50] },
            total: { type: Number, default: 0 },
            from: { type: [Number, null], default: null },
            to: { type: [Number, null], default: null }
        },
        emits: ['page', 'pageSize', 'invalidPage'],
        data() {
            return {
                pageInput: String(this.currentPage)
            };
        },
        watch: {
            currentPage(val) {
                this.pageInput = String(val);
            }
        },
        methods: {
            submitPage() {
                const page = Number(this.pageInput);
                if (!Number.isInteger(page) || page < 1 || page > this.lastPage) {
                    this.$emit('invalidPage', `Enter a whole number from 1 to ${this.lastPage}.`);
                    this.pageInput = String(this.currentPage);
                    return;
                }
                this.$emit('page', page);
            }
        }
    };
    app.component('v-grid-pagination', gridPaginationComponent);
    app.component('grid-pagination', gridPaginationComponent);
</script>
@endpushOnce
