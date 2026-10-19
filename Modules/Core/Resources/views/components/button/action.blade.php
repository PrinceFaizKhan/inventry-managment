@props([
    'type' => 'edit', // 'edit', 'delete', 'activate', 'deactivate', 'toggle_status', 'view'
    'href' => null,
    'title' => null,
    'active' => null,
    'size' => 'sm',
    'disabled' => false,
])

<v-action-button
    type="{{ $type }}"
    @if($href) href="{{ $href }}" @endif
    @if($title) title="{{ $title }}" @endif
    :active="{{ is_null($active) ? 'null' : ($active ? 'true' : 'false') }}"
    size="{{ $size }}"
    :disabled="{{ $disabled ? 'true' : 'false' }}"
    {{ $attributes }}
></v-action-button>

@pushOnce('scripts')
<script type="text/x-template" id="v-action-button-template">
    <component
        :is="href ? 'a' : 'button'"
        :href="href"
        :type="href ? undefined : 'button'"
        :disabled="disabled"
        @click="handleClick"
        class="rounded border grid place-items-center transition-colors select-none"
        :class="[
            size === 'sm' ? 'w-7 h-7' : 'w-8 h-8',
            colorClasses,
            disabled ? 'opacity-40 cursor-not-allowed pointer-events-none' : 'cursor-pointer'
        ]"
        :title="computedTitle"
        :aria-label="computedTitle"
    >
        <!-- Edit / Pencil Icon -->
        <svg
            v-if="resolvedType === 'edit'"
            :width="iconSize"
            :height="iconSize"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
        >
            <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/>
            <path d="m15 5 4 4"/>
        </svg>

        <!-- Delete / Trash Icon -->
        <svg
            v-else-if="resolvedType === 'delete'"
            :width="iconSize"
            :height="iconSize"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
        >
            <path d="M3 6h18"/>
            <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/>
            <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>
            <line x1="10" y1="11" x2="10" y2="17"/>
            <line x1="14" y1="11" x2="14" y2="17"/>
        </svg>

        <!-- Activate / Active Icon (Check-Circle) -->
        <svg
            v-else-if="resolvedType === 'activate' || resolvedType === 'active'"
            :width="iconSize"
            :height="iconSize"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2.2"
            stroke-linecap="round"
            stroke-linejoin="round"
        >
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
            <polyline points="22 4 12 14.01 9 11.01"/>
        </svg>

        <!-- Deactivate / Deactive Icon (Slash / Circle-Off) -->
        <svg
            v-else-if="resolvedType === 'deactivate' || resolvedType === 'deactive'"
            :width="iconSize"
            :height="iconSize"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2.2"
            stroke-linecap="round"
            stroke-linejoin="round"
        >
            <circle cx="12" cy="12" r="10"/>
            <line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/>
        </svg>

        <!-- View / Eye Icon -->
        <svg
            v-else-if="resolvedType === 'view'"
            :width="iconSize"
            :height="iconSize"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
        >
            <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/>
            <circle cx="12" cy="12" r="3"/>
        </svg>

        <!-- Fallback Slot -->
        <slot v-else></slot>
    </component>
</script>

<script type="module">
    const ActionButtonComponent = {
        template: '#v-action-button-template',
        props: {
            type: {
                type: String,
                default: 'edit'
            },
            href: {
                type: String,
                default: null
            },
            title: {
                type: String,
                default: null
            },
            active: {
                type: Boolean,
                default: null
            },
            size: {
                type: String,
                default: 'sm'
            },
            disabled: {
                type: Boolean,
                default: false
            }
        },
        emits: ['click'],
        computed: {
            resolvedType() {
                if (this.type === 'toggle_status') {
                    return this.active ? 'activate' : 'deactivate';
                }
                return this.type;
            },
            iconSize() {
                return this.size === 'sm' ? 13 : 15;
            },
            computedTitle() {
                if (this.title) return this.title;
                switch (this.resolvedType) {
                    case 'edit': return 'Edit record';
                    case 'delete': return 'Delete record';
                    case 'activate':
                    case 'active':
                        return this.type === 'toggle_status' ? 'Active (Click to deactivate)' : 'Activate';
                    case 'deactivate':
                    case 'deactive':
                        return this.type === 'toggle_status' ? 'Inactive (Click to activate)' : 'Deactivate';
                    case 'view': return 'View record';
                    default: return 'Action';
                }
            },
            colorClasses() {
                switch (this.resolvedType) {
                    case 'edit':
                        return 'border-[var(--border)] bg-[var(--card)] hover:border-[var(--teal)] hover:bg-[var(--soft)] text-[var(--muted)] hover:text-[var(--teal)]';
                    case 'delete':
                        return 'border-red-200 bg-red-50 text-red-600 hover:bg-red-100 hover:border-red-300 dark:border-red-900/60 dark:bg-red-950/40 dark:text-red-300';
                    case 'activate':
                    case 'active':
                        return 'border-emerald-200 bg-emerald-50 text-emerald-600 hover:bg-emerald-100 hover:border-emerald-300 dark:border-emerald-900/60 dark:bg-emerald-950/40 dark:text-emerald-300';
                    case 'deactivate':
                    case 'deactive':
                        return 'border-zinc-200 bg-zinc-100 text-zinc-500 hover:bg-zinc-200 hover:border-zinc-300 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-400';
                    case 'view':
                        return 'border-[var(--border)] bg-[var(--card)] hover:border-sky-400 hover:bg-sky-50 text-[var(--muted)] hover:text-sky-600 dark:hover:bg-sky-950/40';
                    default:
                        return 'border-[var(--border)] bg-[var(--card)] hover:border-[var(--teal)] text-[var(--text)]';
                }
            }
        },
        methods: {
            handleClick(e) {
                if (this.disabled) return;
                this.$emit('click', e);
            }
        }
    };

    app.component('v-action-button', ActionButtonComponent);
    app.component('action-button', ActionButtonComponent);
</script>
@endpushOnce
