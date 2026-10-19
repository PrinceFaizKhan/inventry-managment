@props([
    'name' => null,
    'options' => [],
    'modelValue' => null,
    'placeholder' => 'Select an option',
    'searchable' => null,
    'clearable' => false,
    'size' => 'sm',
    'disabled' => false,
])

<v-dropdown-select
    @if($name) name="{{ $name }}" @endif
    :options='@json($options)'
    :model-value='@json($modelValue)'
    placeholder="{{ $placeholder }}"
    :searchable="{{ is_null($searchable) ? 'null' : ($searchable ? 'true' : 'false') }}"
    :clearable="{{ $clearable ? 'true' : 'false' }}"
    size="{{ $size }}"
    :disabled="{{ $disabled ? 'true' : 'false' }}"
    {{ $attributes }}
></v-dropdown-select>

@pushOnce('scripts')
<script type="text/x-template" id="v-dropdown-select-template">
    <div
        ref="rootRef"
        class="relative inline-block text-left w-full select-none"
        :class="[disabled ? 'opacity-60 cursor-not-allowed' : '']"
    >
        <!-- Optional hidden input for standard HTML form submission -->
        <input v-if="name" type="hidden" :name="name" :value="currentValue ?? ''" />

        <!-- Trigger Button -->
        <button
            ref="triggerRef"
            type="button"
            :disabled="disabled"
            @click="toggle"
            class="w-full flex items-center justify-between gap-1.5 rounded-lg border transition-all text-left cursor-pointer focus:outline-none focus:ring-1 focus:ring-[var(--teal)] focus:border-[var(--teal)]"
            :class="[
                size === 'sm' ? 'h-8 px-2.5 text-xs' : 'h-9 px-3 text-sm',
                isOpen ? 'border-[var(--teal)] ring-1 ring-[var(--teal)] bg-[var(--card)]' : 'border-[var(--border)] bg-[var(--card)] text-[var(--text)] hover:border-[var(--teal)]',
                disabled ? 'cursor-not-allowed bg-[var(--page)]' : ''
            ]"
            :aria-expanded="isOpen"
            aria-haspopup="listbox"
        >
            <span
                class="truncate flex-1 font-medium"
                :class="selectedOption ? 'text-[var(--text)]' : 'text-[var(--muted)]'"
            >
                @{{ selectedOption ? selectedOption.label : placeholder }}
            </span>

            <div class="flex items-center gap-1 shrink-0 text-[var(--muted)]">
                <!-- Clear Button -->
                <button
                    v-if="clearable && selectedOption && !disabled"
                    type="button"
                    @click.stop="clear"
                    class="p-0.5 hover:text-red-500 rounded text-xs leading-none"
                    title="Clear"
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

        <!-- Floating Dropdown Menu -->
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
                    class="rounded-lg border border-[var(--border)] bg-[var(--card)] shadow-lg overflow-hidden text-[var(--text)]"
                    :class="[
                        teleportMenu ? 'fixed z-[9999]' : 'absolute z-50 mt-1 w-full left-0',
                        size === 'sm' ? 'text-xs' : 'text-sm'
                    ]"
                    @click.stop
                >
                    <!-- Search Input (Auto if > 6 options or explicitly searchable) -->
                    <div v-if="isSearchable" class="p-1.5 border-b border-[var(--border)] bg-[var(--page)]">
                        <div class="relative">
                            <input
                                ref="searchInputRef"
                                v-model="searchQuery"
                                type="text"
                                placeholder="Search..."
                                class="w-full h-7 pl-7 pr-2 rounded border border-[var(--border)] bg-[var(--card)] text-xs text-[var(--text)] placeholder-[var(--muted)] focus:outline-none focus:border-[var(--teal)]"
                                @keydown.down.prevent="highlightNext"
                                @keydown.up.prevent="highlightPrev"
                                @keydown.enter.prevent="selectHighlighted"
                                @keydown.esc.prevent="close"
                            />
                            <svg class="absolute left-2 top-2 text-[var(--muted)]" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                        </div>
                    </div>

                    <!-- Options List -->
                    <ul
                        ref="listRef"
                        class="max-h-56 overflow-y-auto p-1 space-y-0.5 focus:outline-none"
                        role="listbox"
                    >
                        <li
                            v-for="(option, index) in filteredOptions"
                            :key="String(option.value)"
                            role="option"
                            :aria-selected="isSelected(option)"
                            @click="select(option)"
                            @mouseenter="highlightedIndex = index"
                            class="px-2.5 py-1.5 rounded cursor-pointer flex items-center justify-between gap-2 transition-colors select-none"
                            :class="[
                                isSelected(option) ? 'bg-[var(--soft)] text-[var(--teal)] font-semibold' : 'text-[var(--text)]',
                                highlightedIndex === index && !isSelected(option) ? 'bg-[var(--page)]' : '',
                                option.disabled ? 'opacity-40 cursor-not-allowed pointer-events-none' : ''
                            ]"
                        >
                            <span class="truncate">@{{ option.label }}</span>
                            <svg
                                v-if="isSelected(option)"
                                class="text-[var(--teal)] shrink-0"
                                width="14"
                                height="14"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2.5"
                            >
                                <polyline points="20 6 9 17 4 12"/>
                            </svg>
                        </li>

                        <!-- Empty State -->
                        <li
                            v-if="filteredOptions.length === 0"
                            class="px-3 py-3 text-center text-xs text-[var(--muted)] italic"
                        >
                            No options found
                        </li>
                    </ul>
                </div>
            </transition>
        </teleport>
    </div>
</script>

<script type="module">
    const DropdownSelectComponent = {
        template: '#v-dropdown-select-template',
        props: {
            name: { type: String, default: null },
            options: { type: [Array, Object], default: () => [] },
            modelValue: { type: [String, Number, Boolean, null], default: null },
            placeholder: { type: String, default: 'Select an option' },
            searchable: { type: Boolean, default: null },
            clearable: { type: Boolean, default: false },
            size: { type: String, default: 'sm' },
            disabled: { type: Boolean, default: false },
            teleportMenu: { type: Boolean, default: true }
        },
        emits: ['update:modelValue', 'change'],
        data() {
            return {
                isOpen: false,
                currentValue: this.modelValue,
                searchQuery: '',
                highlightedIndex: -1,
                popupStyle: {}
            };
        },
        watch: {
            modelValue(newVal) {
                this.currentValue = newVal;
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
                return this.normalizedOptions.length > 6;
            },
            filteredOptions() {
                const q = (this.searchQuery || '').trim().toLowerCase();
                if (!q) return this.normalizedOptions;
                return this.normalizedOptions.filter(o => o.label.toLowerCase().includes(q));
            },
            selectedOption() {
                if (this.currentValue === null || this.currentValue === undefined || this.currentValue === '') {
                    return null;
                }
                return this.normalizedOptions.find(o => String(o.value) === String(this.currentValue)) || {
                    value: this.currentValue,
                    label: String(this.currentValue)
                };
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
                this.highlightedIndex = -1;
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
            select(option) {
                if (option.disabled) return;
                this.currentValue = option.value;
                this.$emit('update:modelValue', option.value);
                this.$emit('change', option.value);
                this.close();
            },
            clear() {
                this.currentValue = null;
                this.$emit('update:modelValue', null);
                this.$emit('change', null);
            },
            isSelected(option) {
                return this.selectedOption && String(this.selectedOption.value) === String(option.value);
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
                const menuHeight = 240;

                let top = rect.bottom + 4;
                if (spaceBelow < menuHeight && spaceAbove > spaceBelow) {
                    top = Math.max(10, rect.top - menuHeight - 4);
                }

                this.popupStyle = {
                    top: `${top}px`,
                    left: `${rect.left}px`,
                    width: `${Math.max(rect.width, 140)}px`,
                    minWidth: '130px',
                    maxWidth: '92vw'
                };
            },
            highlightNext() {
                if (this.highlightedIndex < this.filteredOptions.length - 1) {
                    this.highlightedIndex++;
                }
            },
            highlightPrev() {
                if (this.highlightedIndex > 0) {
                    this.highlightedIndex--;
                }
            },
            selectHighlighted() {
                if (this.highlightedIndex >= 0 && this.highlightedIndex < this.filteredOptions.length) {
                    this.select(this.filteredOptions[this.highlightedIndex]);
                }
            }
        }
    };

    app.component('v-dropdown-select', DropdownSelectComponent);
    app.component('v-single-select', DropdownSelectComponent);
    app.component('app-select', DropdownSelectComponent);
</script>
@endpushOnce
