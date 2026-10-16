@props([
    'open' => false,
    'title' => 'Confirm Action',
    'description' => 'Are you sure you want to perform this action?',
    'confirmText' => 'Delete',
    'submitting' => false,
])

<v-confirm-dialog></v-confirm-dialog>

@pushOnce('scripts')
<script type="text/x-template" id="v-confirm-dialog-template">
    <div v-if="open" class="confirm-backdrop" role="presentation" @click.self="!submitting && $emit('cancel')">
        <section class="confirm-dialog" role="alertdialog" aria-modal="true" :aria-label="title">
            <h2>@{{ title }}</h2>
            <p>@{{ description }}</p>
            <div>
                <button type="button" class="button button--outline" :disabled="submitting" @click="$emit('cancel')">
                    Cancel
                </button>
                <button type="button" class="button button--danger" :disabled="submitting" @click="$emit('confirm')">
                    @{{ submitting ? 'Working…' : confirmText }}
                </button>
            </div>
        </section>
    </div>
</script>
<script type="module">
    const confirmDialogComponent = {
        template: '#v-confirm-dialog-template',
        props: {
            open: { type: Boolean, default: false },
            title: { type: String, default: 'Confirm Action' },
            description: { type: String, default: '' },
            confirmText: { type: String, default: 'Delete' },
            submitting: { type: Boolean, default: false }
        },
        emits: ['cancel', 'confirm']
    };
    app.component('v-confirm-dialog', confirmDialogComponent);
    app.component('confirm-dialog', confirmDialogComponent);
</script>
@endpushOnce
