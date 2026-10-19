@props([
    'value' => null,
    'type' => 'auto',
    'size' => 'sm',
])

<v-status-badge
    :value='@json($value)'
    type="{{ $type }}"
    size="{{ $size }}"
    {{ $attributes }}
></v-status-badge>

@pushOnce('scripts')
<script type="text/x-template" id="v-status-badge-template">
    <span
        class="inline-flex items-center font-semibold border capitalize select-none"
        :class="[
            badgeClasses,
            size === 'sm' ? 'gap-1 px-1.5 py-0.5 text-[10px] rounded-full' : 'gap-1.5 px-2 py-0.5 text-xs rounded-full'
        ]"
    >
        <!-- Active / Enabled Status Icon -->
        <svg
            v-if="resolvedStatus === 'active'"
            :width="size === 'sm' ? 10 : 12"
            :height="size === 'sm' ? 10 : 12"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2.5"
            stroke-linecap="round"
            stroke-linejoin="round"
        >
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
            <polyline points="22 4 12 14.01 9 11.01"/>
        </svg>

        <!-- Inactive / Deactive Status Icon -->
        <svg
            v-else-if="resolvedStatus === 'inactive'"
            :width="size === 'sm' ? 10 : 12"
            :height="size === 'sm' ? 10 : 12"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2.5"
            stroke-linecap="round"
            stroke-linejoin="round"
        >
            <circle cx="12" cy="12" r="10"/>
            <line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/>
        </svg>

        <!-- Boolean True Icon -->
        <svg
            v-else-if="resolvedStatus === 'true'"
            :width="size === 'sm' ? 9 : 11"
            :height="size === 'sm' ? 9 : 11"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="3"
            stroke-linecap="round"
            stroke-linejoin="round"
        >
            <polyline points="20 6 9 17 4 12"/>
        </svg>

        <!-- Boolean False Icon -->
        <svg
            v-else-if="resolvedStatus === 'false'"
            :width="size === 'sm' ? 9 : 11"
            :height="size === 'sm' ? 9 : 11"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="3"
            stroke-linecap="round"
            stroke-linejoin="round"
        >
            <line x1="18" y1="6" x2="6" y2="18"/>
            <line x1="6" y1="6" x2="18" y2="18"/>
        </svg>

        <!-- Pending / In Progress Icon -->
        <svg
            v-else-if="resolvedStatus === 'pending'"
            :width="size === 'sm' ? 10 : 12"
            :height="size === 'sm' ? 10 : 12"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2.5"
            stroke-linecap="round"
            stroke-linejoin="round"
        >
            <circle cx="12" cy="12" r="10"/>
            <polyline points="12 6 12 12 16 14"/>
        </svg>

        <!-- Label text -->
        <span>@{{ displayLabel }}</span>
    </span>
</script>

<script type="module">
    const StatusBadgeComponent = {
        template: '#v-status-badge-template',
        props: {
            value: {
                type: [String, Number, Boolean, null],
                default: null
            },
            type: {
                type: String,
                default: 'auto' // 'auto', 'status', 'boolean'
            },
            size: {
                type: String,
                default: 'sm'
            },
            label: {
                type: String,
                default: null
            }
        },
        computed: {
            isBoolean() {
                if (this.type === 'boolean') return true;
                if (this.type === 'status') return false;
                return typeof this.value === 'boolean';
            },
            resolvedStatus() {
                if (this.isBoolean) {
                    return this.value ? 'true' : 'false';
                }
                const str = String(this.value ?? '').toLowerCase().trim();
                if (str === 'active' || str === 'enabled' || str === 'published' || str === '1' || str === 'true') {
                    return 'active';
                }
                if (str === 'inactive' || str === 'deactive' || str === 'disabled' || str === '0' || str === 'false') {
                    return 'inactive';
                }
                if (str === 'pending' || str === 'processing' || str === 'draft') {
                    return 'pending';
                }
                return 'generic';
            },
            displayLabel() {
                if (this.label) return this.label;
                if (this.isBoolean) {
                    return this.value ? 'Yes' : 'No';
                }
                if (this.value === null || this.value === undefined || this.value === '') {
                    return '—';
                }
                return String(this.value).replace(/_/g, ' ');
            },
            badgeClasses() {
                switch (this.resolvedStatus) {
                    case 'active':
                    case 'true':
                        return 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/50 dark:text-emerald-300 dark:border-emerald-900';
                    case 'inactive':
                    case 'false':
                        return 'bg-zinc-100 text-zinc-600 border-zinc-200 dark:bg-zinc-900 dark:text-zinc-400 dark:border-zinc-800';
                    case 'pending':
                        return 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/50 dark:text-amber-300 dark:border-amber-900';
                    default:
                        return 'bg-[var(--soft)] text-[var(--teal)] border-[var(--teal)]/20';
                }
            }
        }
    };

    app.component('v-status-badge', StatusBadgeComponent);
    app.component('v-badge', StatusBadgeComponent);
    app.component('status-badge', StatusBadgeComponent);
</script>
@endpushOnce
