@props([
    'name' => null,
    'options' => [],
    'modelValue' => [],
    'placeholder' => 'Select options',
    'searchable' => null,
    'size' => 'sm',
    'disabled' => false,
    'maxDisplay' => 2,
])

<v-dropdown-multiselect
    @if($name) name="{{ $name }}" @endif
    :options='@json($options)'
    :model-value='@json($modelValue)'
    placeholder="{{ $placeholder }}"
    :searchable="{{ is_null($searchable) ? 'null' : ($searchable ? 'true' : 'false') }}"
    size="{{ $size }}"
    :disabled="{{ $disabled ? 'true' : 'false' }}"
    :max-display="{{ $maxDisplay }}"
    {{ $attributes }}
></v-dropdown-multiselect>

@pushOnce('scripts')
<script type="text/x-template" id="v-dropdown-multiselect-template">
    <div
        ref="rootRef"
        class="relative inline-block text-left w-full select-none"
        :class="[disabled ? 'opacity-60 cursor-not-allowed' : '']"
    >
        <!-- Optional hidden inputs for standard form submission -->
        <template v-if="name">
            <input
                v-for="(val, idx) in selectedValues"
                :key="'hidden-' + idx"
                type="hidden"
                :name="name.includes('[]') ? name : name + '[]'"
                :value="val"
            />
        </template>

        <!-- Trigger Button -->
        <button
            ref="triggerRef"
            type="button"
            :disabled="disabled"
            @click="toggle"
            class="w-full flex items-center justify-between gap-1.5 rounded-lg border transition-all text-left cursor-pointer focus:outline-none focus:ring-1 focus:ring-[var(--teal)] focus:border-[var(--teal)]"
            :class="[
                size === 'sm' ? 'min-h-[32px] h-8 px-2.5 text-xs' : 'min-h-[36px] h-9 px-3 text-sm',
                isOpen ? 'border-[var(--teal)] ring-1 ring-[var(--teal)] bg-[var(--card)]' : 'border-[var(--border)] bg-[var(--card)] text-[var(--text)] hover:border-[var(--teal)]',
                disabled ? 'cursor-not-allowed bg-[var(--page)]' : ''
            ]"
            :aria-expanded="isOpen"
            aria-haspopup="listbox"
        >
            <!-- Selected Values Summary -->
            <div class="truncate flex-1 flex items-center gap-1.5 min-w-0">
                <template v-if="selectedValues.length === 0">
                    <span class="text-[var(--muted)]">@{{ placeholder }}</span>
                </template>
                <template v-else-if="selectedValues.length <= maxDisplay">
                    <span
                        v-for="opt in selectedOptionObjects"
                        :key="String(opt.value)"
                        class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-semibold bg-[var(--soft)] text-[var(--teal)] border border-[var(--teal)]/20 truncate"
                    >
                        @{{ opt.label }}
                    </span>
                </template>
                <template v-else>
                    <span class="font-semibold text-[var(--text)]">
                        @{{ selectedValues.length }} selected
                    </span>
                    <span class="text-[10px] text-[var(--muted)]">
                        (@{{ selectedOptionObjects.slice(0, 1).map(o => o.label).join('') }} +@{{ selectedValues.length - 1 }})
                    </span>
                </template>
            </div>

            <div class="flex items-center gap-1 shrink-0 text-[var(--muted)]">
                <!-- Clear Button -->
                <button
                    v-if="selectedValues.length > 0 && !disabled"
                    type="button"
                    @click.stop="clearAll"
                    class="p-0.5 hover:text-red-500 rounded text-xs leading-none"
                    title="Clear all"
                >&times;</button>

                <!-- Chevron -->
                <svg
                    class="transition-transform duration-200"
                    :class="{ 'rotate-180 text-[var(--teal)]': isOpen }"
                    width="12"
                    height="12"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2.5"
                >
                    <polyline points="6 9 12 15 18 9"/>
                </svg>
            </div>
        </button>

        <!-- Floating Multi-Select Menu -->
        <teleport to="body" :disabled="!teleportMenu">
            <transition
                enter-active-class="transition ease-out duration-100"
                enter-from-class="transform opacity-0 scale-95"
                enter-to-class="transform opacity-100 scale-100"
                leave-active-class="transition ease-in duration-75"
                leave-from-class="transform opacity-100 scale-100"
                leave-to-class="transform opacity-0 scale-95"
            >
                <div
                    v-if="isOpen"
                    ref="popupRef"
                    :style="teleportMenu ? popupStyle : {}"
                    class="rounded-lg border border-[var(--border)] bg-[var(--card)] shadow-xl overflow-hidden text-[var(--text)]"
                    :class="[
                        teleportMenu ? 'fixed z-[9999]' : 'absolute z-50 mt-1 w-full left-0',
                        size === 'sm' ? 'text-xs' : 'text-sm'
                    ]"
                    @click.stop
                >
                    <!-- Header Actions: Quick Select / Counter -->
                    <div class="p-2 border-b border-[var(--border)] bg-[var(--page)] flex items-center justify-between gap-2 text-[11px]">
                        <span class="font-medium text-[var(--muted)]">
                            @{{ selectedValues.length }} of @{{ normalizedOptions.length }} selected
                        </span>
                        <div class="flex items-center gap-2">
                            <button
                                type="button"
                                @click="selectAll"
                                class="text-[var(--teal)] hover:underline font-semibold cursor-pointer"
                            >
                                Select All
                            </button>
                            <span class="text-[var(--border)]">|</span>
                            <button
                                type="button"
                                @click="clearAll"
                                class="text-[var(--muted)] hover:text-red-500 hover:underline cursor-pointer"
                            >
                                Clear
                            </button>
                        </div>
                    </div>

                    <!-- Search Filter -->
                    <div v-if="isSearchable" class="p-1.5 border-b border-[var(--border)] bg-[var(--card)]">
                        <div class="relative">
                            <input
                                ref="searchInputRef"
                                v-model="searchQuery"
                                type="text"
                                placeholder="Search options..."
                                class="w-full h-7 pl-7 pr-2 rounded border border-[var(--border)] bg-[var(--card)] text-xs text-[var(--text)] placeholder-[var(--muted)] focus:outline-none focus:border-[var(--teal)]"
                                @keydown.esc.prevent="close"
                            />
                            <svg class="absolute left-2 top-2 text-[var(--muted)]" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                        </div>
                    </div>

                    <!-- Checkboxes List -->
                    <ul class="max-h-56 overflow-y-auto p-1 space-y-0.5 focus:outline-none" role="listbox">
                        <li
                            v-for="option in filteredOptions"
                            :key="String(option.value)"
                            @click="toggleOption(option)"
                            class="px-2.5 py-1.5 rounded cursor-pointer flex items-center gap-2 transition-colors select-none"
                            :class="[
                                isSelected(option) ? 'bg-[var(--soft)]/50 text-[var(--teal)] font-medium' : 'text-[var(--text)] hover:bg-[var(--page)]',
                                option.disabled ? 'opacity-40 cursor-not-allowed pointer-events-none' : ''
                            ]"
                        >
                            <!-- Custom checkbox visual -->
                            <div
                                class="w-3.5 h-3.5 rounded border flex items-center justify-center shrink-0 transition-all"
                                :class="isSelected(option) ? 'border-[var(--teal)] bg-[var(--teal)] text-white' : 'border-[var(--border)] bg-[var(--card)]'"
                            >
                                <svg v-if="isSelected(option)" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                    <polyline points="20 6 9 17 4 12"/>
                                </svg>
                            </div>

                            <span class="truncate flex-1">@{{ option.label }}</span>
                        </li>

                        <!-- Empty State -->
                        <li
                            v-if="filteredOptions.length === 0"
                            class="px-3 py-3 text-center text-xs text-[var(--muted)] italic"
                        >
                            No matching options
                        </li>
                    </ul>

                    <!-- Done / Close Footer -->
                    <div class="p-1.5 border-t border-[var(--border)] bg-[var(--page)] flex justify-end">
                        <button
                            type="button"
                            @click="close"
                            class="px-2.5 py-1 rounded bg-[var(--card)] border border-[var(--border)] hover:border-[var(--teal)] text-[var(--text)] text-[11px] font-semibold cursor-pointer shadow-xs"
                        >
                            Done
                        </button>
                    </div>
                </div>
            </transition>
        </teleport>
    </div>
</script>

<script>
(function() {
    const DropdownMultiSelectComponent = {
        template: '#v-dropdown-multiselect-template',
        props: {
            name: { type: String, default: null },
            options: { type: [Array, Object], default: () => [] },
            modelValue: { type: Array, default: () => [] },
            placeholder: { type: String, default: 'Select options' },
            searchable: { type: Boolean, default: null },
            size: { type: String, default: 'sm' },
            disabled: { type: Boolean, default: false },
            maxDisplay: { type: Number, default: 2 },
            teleportMenu: { type: Boolean, default: true }
        },
        emits: ['update:modelValue', 'change'],
        data() {
            return {
                isOpen: false,
                selectedValues: Array.isArray(this.modelValue) ? [...this.modelValue] : [],
                searchQuery: '',
                popupStyle: {}
            };
        },
        watch: {
            modelValue(newVal) {
                this.selectedValues = Array.isArray(newVal) ? [...newVal] : [];
            }
        },
        computed: {
            normalizedOptions() {
                if (!this.options) return [];
                if (Array.isArray(this.options)) {
                    return this.options.map(item => {
                        if (item !== null && typeof item === 'object') {
                            return {
                                value: item.value ?? item.id ?? item.key,
                                label: item.label ?? item.name ?? item.title ?? String(item.value ?? item.id ?? item.key),
                                disabled: Boolean(item.disabled)
                            };
                        }
                        return { value: item, label: String(item), disabled: false };
                    });
                }
                if (typeof this.options === 'object') {
                    return Object.entries(this.options).map(([val, lbl]) => ({
                        value: val,
                        label: String(lbl),
                        disabled: false
                    }));
                }
                return [];
            },
            isSearchable() {
                if (this.searchable !== null) return this.searchable;
                return this.normalizedOptions.length > 5;
            },
            filteredOptions() {
                const q = (this.searchQuery || '').trim().toLowerCase();
                if (!q) return this.normalizedOptions;
                return this.normalizedOptions.filter(o => o.label.toLowerCase().includes(q));
            },
            selectedOptionObjects() {
                return this.selectedValues.map(val => {
                    const match = this.normalizedOptions.find(o => String(o.value) === String(val));
                    return match || { value: val, label: String(val) };
                });
            }
        },
        mounted() {
            document.addEventListener('pointerdown', this.onOutsideClick);
            window.addEventListener('resize', this.updatePosition);
            window.addEventListener('scroll', this.updatePosition, true);
        },
        beforeUnmount() {
            document.removeEventListener('pointerdown', this.onOutsideClick);
            window.removeEventListener('resize', this.updatePosition);
            window.removeEventListener('scroll', this.updatePosition, true);
        },
        methods: {
            toggle() {
                if (this.disabled) return;
                this.isOpen ? this.close() : this.open();
            },
            open() {
                this.isOpen = true;
                this.searchQuery = '';
                this.updatePosition();
                this.$nextTick(() => {
                    if (this.isSearchable && this.$refs.searchInputRef) {
                        this.$refs.searchInputRef.focus();
                    }
                });
            },
            close() {
                this.isOpen = false;
                this.searchQuery = '';
            },
            toggleOption(option) {
                if (option.disabled) return;
                const strVal = String(option.value);
                const exists = this.selectedValues.some(v => String(v) === strVal);
                if (exists) {
                    this.selectedValues = this.selectedValues.filter(v => String(v) !== strVal);
                } else {
                    this.selectedValues = [...this.selectedValues, option.value];
                }
                this.$emit('update:modelValue', this.selectedValues);
                this.$emit('change', this.selectedValues);
            },
            isSelected(option) {
                return this.selectedValues.some(v => String(v) === String(option.value));
            },
            selectAll() {
                const allVals = this.normalizedOptions.filter(o => !o.disabled).map(o => o.value);
                this.selectedValues = [...new Set([...this.selectedValues, ...allVals])];
                this.$emit('update:modelValue', this.selectedValues);
                this.$emit('change', this.selectedValues);
            },
            clearAll() {
                this.selectedValues = [];
                this.$emit('update:modelValue', []);
                this.$emit('change', []);
            },
            onOutsideClick(e) {
                if (!this.isOpen) return;
                const root = this.$refs.rootRef;
                const popup = this.$refs.popupRef;
                if ((root && root.contains(e.target)) || (popup && popup.contains(e.target))) {
                    return;
                }
                this.close();
            },
            updatePosition() {
                if (!this.isOpen || !this.teleportMenu) return;
                const trigger = this.$refs.triggerRef;
                if (!trigger) return;
                const rect = trigger.getBoundingClientRect();
                const viewportHeight = window.innerHeight;
                const spaceBelow = viewportHeight - rect.bottom;
                const spaceAbove = rect.top;
                const menuHeight = 260;

                let top = rect.bottom + 4;
                if (spaceBelow < menuHeight && spaceAbove > spaceBelow) {
                    top = Math.max(10, rect.top - menuHeight - 4);
                }

                this.popupStyle = {
                    top: `${top}px`,
                    left: `${rect.left}px`,
                    width: `${Math.max(rect.width, 180)}px`,
                    minWidth: '160px',
                    maxWidth: '92vw'
                };
            }
        }
    };

    if (typeof window.registerVueComponent === 'function') {
        window.registerVueComponent('v-dropdown-multiselect', DropdownMultiSelectComponent);
        window.registerVueComponent('v-multi-select', DropdownMultiSelectComponent);
        window.registerVueComponent('app-multiselect', DropdownMultiSelectComponent);
    } else if (window.app && typeof window.app.component === 'function') {
        window.app.component('v-dropdown-multiselect', DropdownMultiSelectComponent);
        window.app.component('v-multi-select', DropdownMultiSelectComponent);
        window.app.component('app-multiselect', DropdownMultiSelectComponent);
    }
})();
</script>
@endpushOnce
