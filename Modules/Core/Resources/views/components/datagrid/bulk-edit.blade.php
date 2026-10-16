<v-bulk-edit-dialog></v-bulk-edit-dialog>

@pushOnce('scripts')
<script type="text/x-template" id="v-bulk-edit-dialog-template">
    <div class="confirm-backdrop" role="presentation" @click.self="!submitting && $emit('cancel')">
        <section class="bulk-edit-dialog" role="dialog" aria-modal="true" aria-labelledby="bulk-edit-title">
            <header>
                <div>
                    <h2 id="bulk-edit-title">Edit @{{ count }} selected</h2>
                    <p>Choose the changes to apply to every selected record.</p>
                </div>
                <button type="button" aria-label="Close dialog" :disabled="submitting" @click="$emit('cancel')">
                    &times;
                </button>
            </header>
            <div class="bulk-edit-fields">
                <div v-for="(field, key, index) in fields" :key="key" class="bulk-edit-field">
                    <label class="bulk-edit-apply">
                        <input v-model="applies[key]" type="checkbox"/>
                        Apply this change
                    </label>
                    <div class="bulk-edit-control">
                        <label :for="`bulk-${key}`">@{{ field.label }}</label>
                        <input :id="`bulk-${key}`" v-model="values[key]" :disabled="!applies[key] || clears[key] || submitting" :type="field.type || 'text'"/>
                        <label v-if="field.nullable && applies[key]" class="bulk-edit-clear">
                            <input v-model="clears[key]" type="checkbox"/>
                            Clear this value
                        </label>
                        <small v-if="errors[`changes.${key}`]" class="bulk-edit-error">@{{ errors[`changes.${key}`] }}</small>
                    </div>
                </div>
            </div>
            <small v-if="errors && errors.changes" class="bulk-edit-error">@{{ errors.changes }}</small>
            <footer>
                <button type="button" class="button button--outline" :disabled="submitting" @click="$emit('cancel')">
                    Cancel
                </button>
                <button type="button" class="button button--primary bulk-apply" :disabled="submitting || !canApply()" @click="submit">
                    @{{ submitting ? 'Applying…' : 'Apply changes' }}
                </button>
            </footer>
        </section>
    </div>
</script>
<script type="module">
    const bulkEditDialogComponent = {
        template: '#v-bulk-edit-dialog-template',
        props: {
            count: { type: Number, default: 0 },
            fields: { type: Object, default: () => ({}) },
            submitting: { type: Boolean, default: false },
            errors: { type: Object, default: () => ({}) }
        },
        emits: ['cancel', 'submit'],
        data() {
            return {
                applies: {},
                clears: {},
                values: {}
            };
        },
        methods: {
            canApply() {
                const keys = Object.keys(this.fields || {});
                return keys.some(k => this.applies[k] && (this.clears[k] || (this.values[k] !== undefined && this.values[k] !== '')));
            },
            submit() {
                if (!this.canApply()) return;
                const changes = {};
                const keys = Object.keys(this.fields || {});
                keys.forEach(k => {
                    if (this.applies[k]) {
                        changes[k] = this.clears[k] ? null : this.values[k];
                    }
                });
                if (Object.keys(changes).length) {
                    this.$emit('submit', changes);
                }
            }
        }
    };
    app.component('v-bulk-edit-dialog', bulkEditDialogComponent);
    app.component('bulk-edit-dialog', bulkEditDialogComponent);
</script>
@endpushOnce
