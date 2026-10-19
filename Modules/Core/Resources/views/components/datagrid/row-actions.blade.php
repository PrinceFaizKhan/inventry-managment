@props([
    'row' => null,
    'actions' => null,
])

<v-row-actions
    :row='@json($row)'
    {{ $attributes }}
></v-row-actions>

@pushOnce('scripts')
<!-- Ensure action-button scripts are registered -->
<div style="display:none">
    <x-core::button.action />
</div>

<script type="text/x-template" id="v-row-actions-template">
    <div class="inline-flex items-center gap-1 justify-end">
        <!-- Active / Deactive Status Toggle Button -->
        <v-action-button
            v-if="hasStatusToggle"
            type="toggle_status"
            :active="isCurrentlyActive"
            @click="$emit('toggle-status', row)"
        ></v-action-button>

        <!-- Edit Action Button -->
        <v-action-button
            v-if="hasEditAction"
            type="edit"
            :href="editUrl"
            @click="!editUrl && $emit('edit', row)"
        ></v-action-button>

        <!-- Delete Action Button -->
        <v-action-button
            v-if="hasDeleteAction"
            type="delete"
            @click="$emit('delete', row)"
        ></v-action-button>

        <!-- Additional Custom Dynamic Actions -->
        <template v-for="action in customActions" :key="action.key">
            <v-action-button
                :type="action.icon || action.key"
                :title="action.label"
                :href="action.url"
                @click="!action.url && $emit('action', action, row)"
            ></v-action-button>
        </template>
    </div>
</script>

<script type="module">
    const RowActionsComponent = {
        template: '#v-row-actions-template',
        props: {
            row: {
                type: Object,
                required: true
            },
            actions: {
                type: [Array, Object],
                default: () => ({})
            },
            editUrl: {
                type: String,
                default: null
            },
            showStatusToggle: {
                type: Boolean,
                default: true
            },
            rowKey: {
                type: String,
                default: 'id'
            }
        },
        emits: ['edit', 'delete', 'toggle-status', 'action'],
        computed: {
            isCurrentlyActive() {
                if (!this.row) return false;
                const val = this.row.status !== undefined ? this.row.status : this.row.is_active;
                return val === true || val === 1 || val === '1' || val === 'active' || val === 'enabled';
            },
            hasStatusToggle() {
                if (!this.showStatusToggle || !this.row) return false;
                const hasStatusField = this.row.status !== undefined || this.row.is_active !== undefined;
                if (!hasStatusField) return false;
                if (this.actions?.toggle_status !== undefined) return Boolean(this.actions.toggle_status);
                if (this.actions?.activate !== undefined || this.actions?.deactivate !== undefined) return true;
                return true;
            },
            hasEditAction() {
                if (this.row?.edit_url || this.editUrl) return true;
                if (this.actions?.edit !== undefined) return Boolean(this.actions.edit);
                return true;
            },
            hasDeleteAction() {
                if (this.row?.delete_url) return true;
                if (this.actions?.delete !== undefined) return Boolean(this.actions.delete);
                return true;
            },
            customActions() {
                if (Array.isArray(this.actions)) {
                    return this.actions.filter(a => !['edit', 'delete', 'activate', 'deactivate', 'toggle_status'].includes(a.key));
                }
                return [];
            }
        }
    };

    app.component('v-row-actions', RowActionsComponent);
    app.component('row-actions', RowActionsComponent);
</script>
@endpushOnce
