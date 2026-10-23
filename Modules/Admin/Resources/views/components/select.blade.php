@props([
    'name' => null,
    'options' => [],
    'modelValue' => null,
    'placeholder' => 'Select an option...',
    'label' => '',
    'searchable' => true,
    'clearable' => false,
    'size' => 'md',
    'disabled' => false,
    'error' => '',
])

@if(!empty($name) || !empty($options) || !empty($label))
<v-select
    @if($name) name="{{ $name }}" @endif
    :options='@json($options)'
    @if(!is_null($modelValue)) :model-value='@json($modelValue)' @endif
    placeholder="{{ $placeholder }}"
    @if($label) label="{{ $label }}" @endif
    :searchable="{{ $searchable ? 'true' : 'false' }}"
    :clearable="{{ $clearable ? 'true' : 'false' }}"
    size="{{ $size }}"
    :disabled="{{ $disabled ? 'true' : 'false' }}"
    @if($error) error="{{ $error }}" @endif
    {{ $attributes }}
></v-select>
@endif

@pushOnce('scripts')
<script type="text/x-template" id="v-select-template">
    <div ref="wrapperRef" class="select-component-wrapper space-y-1.5 w-full relative" @keydown.esc="isOpen = false">
        <!-- Optional Label -->
        <div v-if="label" class="flex items-center justify-between">
            <label :for="id" class="block text-xs font-semibold text-[var(--text)]">
                @{{ label }}
            </label>
        </div>

        <!-- Hidden input for standard HTML forms -->
        <input v-if="name" type="hidden" :name="name" :value="formValue" />

        <!-- Trigger Button -->
        <button
            type="button"
            @click="toggle"
            :disabled="disabled"
            class="w-full flex items-center justify-between gap-2 rounded-xl border text-left transition-all duration-200 outline-none select-none cursor-pointer"
            :class="[
                size === 'sm' ? 'h-8 px-2.5 text-xs' : 'h-10 px-3.5 text-xs',
                isOpen ? 'border-[var(--teal)] ring-1 ring-[var(--teal)]/40 bg-[var(--page)] text-[var(--text)]' : 'border-[var(--border)] bg-[var(--page)] text-[var(--text)] hover:border-[var(--teal)]/60',
                error ? 'border-red-500 ring-1 ring-red-500/30' : '',
                disabled ? 'opacity-60 cursor-not-allowed bg-[var(--page)]' : ''
            ]"
            :aria-expanded="isOpen"
            aria-haspopup="listbox"
        >
            <!-- Single Mode Selected Value -->
            <span v-if="!multiple" class="truncate flex-1 font-medium" :class="selectedOptions ? 'text-[var(--text)]' : 'text-[var(--muted)]'">
                @{{ selectedOptions ? selectedOptions.label : placeholder }}
            </span>

            <!-- Multiple Mode Tags -->
            <div v-else class="flex flex-wrap items-center gap-1.5 flex-1 py-0.5">
                <span v-if="selectedOptions.length === 0" class="text-[var(--muted)]">
                    @{{ placeholder }}
                </span>
                <span
                    v-for="opt in selectedOptions"
                    :key="opt.value"
                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-[var(--soft)] text-[var(--teal)] text-[11px] font-semibold border border-[var(--teal)]/20"
                >
                    <span>@{{ opt.label }}</span>
                    <button type="button" @click.stop="removeTag(opt.value)" class="hover:opacity-70 font-bold">&times;</button>
                </span>
            </div>

            <!-- Trailing Icons (Clear + Animated Chevron) -->
            <div class="flex items-center gap-1.5 text-[var(--muted)] shrink-0">
                <!-- Clear Button -->
                <span
                    v-if="clearable && !disabled && ((multiple && selectedOptions.length > 0) || (!multiple && selectedOptions))"
                    @click.stop="clearAll"
                    class="hover:text-red-500 p-0.5 text-sm font-bold leading-none cursor-pointer"
                    title="Clear selection"
                >&times;</span>

                <!-- Animated Modern Chevron Arrow -->
                <svg
                    class="transition-transform duration-200 pointer-events-none shrink-0"
                    :class="{ 'rotate-180 text-[var(--teal)]': isOpen }"
                    width="14"
                    height="14"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2.5"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                >
                    <polyline points="6 9 12 15 18 9"/>
                </svg>
            </div>
        </button>

        <!-- Dropdown Popup Menu -->
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
                class="absolute z-[100] mt-1.5 w-full rounded-xl border border-[var(--border)] bg-[var(--card)] shadow-2xl overflow-hidden backdrop-blur-xl"
            >
                <!-- Search input inside dropdown -->
                <div v-if="searchable" class="p-2 border-b border-[var(--border)] bg-[var(--page)]">
                    <div class="relative">
                        <input
                            ref="searchInput"
                            type="text"
                            v-model="searchQuery"
                            placeholder="Search..."
                            class="w-full h-8 pl-8 pr-3 rounded-lg border border-[var(--border)] bg-[var(--card)] text-xs text-[var(--text)] placeholder-[var(--muted)] focus:outline-none focus:border-[var(--teal)] transition-all"
                            @keydown.down.prevent="highlightNext"
                            @keydown.up.prevent="highlightPrev"
                            @keydown.enter.prevent="selectHighlighted"
                        />
                        <svg class="absolute left-2.5 top-2.5 text-[var(--muted)]" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    </div>
                </div>

                <!-- Options list -->
                <div class="max-h-60 overflow-y-auto p-1 custom-scrollbar text-xs" role="listbox">
                    <div
                        v-for="(opt, idx) in filteredOptions"
                        :key="String(opt.value)"
                        @click="selectOption(opt)"
                        @mouseenter="highlightedIndex = idx"
                        class="flex items-center justify-between px-3 py-2 rounded-lg cursor-pointer transition-colors select-none"
                        :class="[
                            isSelected(opt) ? 'bg-[var(--soft)] text-[var(--teal)] font-semibold' : (highlightedIndex === idx ? 'bg-[var(--soft)]/50 text-[var(--text)]' : 'text-[var(--text)] hover:bg-[var(--border)]/30'),
                            opt.disabled ? 'opacity-40 cursor-not-allowed pointer-events-none' : ''
                        ]"
                        role="option"
                        :aria-selected="isSelected(opt)"
                    >
                        <div class="flex items-center gap-2">
                            <span
                                v-if="multiple"
                                class="w-3.5 h-3.5 rounded border flex items-center justify-center text-[10px]"
                                :class="isSelected(opt) ? 'bg-[var(--teal)] border-[var(--teal)] text-white' : 'border-[var(--border)] bg-[var(--card)]'"
                            >
                                <svg v-if="isSelected(opt)" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                            </span>
                            <span>@{{ opt.label }}</span>
                        </div>

                        <div class="flex items-center gap-1.5">
                            <span v-if="opt.badge" class="px-1.5 py-0.2 rounded text-[10px] font-mono bg-[var(--border)] text-[var(--muted)]">@{{ opt.badge }}</span>
                            <svg v-if="!multiple && isSelected(opt)" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" class="text-[var(--teal)]"><polyline points="20 6 9 17 4 12"/></svg>
                        </div>
                    </div>

                    <div v-if="filteredOptions.length === 0" class="py-4 text-center text-[var(--muted)] text-xs">
                        No options found
                    </div>
                </div>
            </div>
        </transition>

        <span v-if="error" class="block text-[11px] font-medium text-red-500">@{{ error }}</span>
    </div>
</script>

<script type="module">
    const SelectComponent = {
        template: '#v-select-template',
        props: {
            modelValue: {
                type: [String, Number, Array, Boolean, null],
                default: null,
            },
            options: {
                type: [Array, Object],
                default: () => [],
            },
            placeholder: {
                type: String,
                default: 'Select an option...',
            },
            label: {
                type: String,
                default: '',
            },
            searchable: {
                type: Boolean,
                default: true,
            },
            multiple: {
                type: Boolean,
                default: false,
            },
            clearable: {
                type: Boolean,
                default: false,
            },
            disabled: {
                type: Boolean,
                default: false,
            },
            size: {
                type: String,
                default: 'md', // 'sm' | 'md'
            },
            name: {
                type: String,
                default: '',
            },
            id: {
                type: String,
                default: '',
            },
            error: {
                type: String,
                default: '',
            },
        },
        emits: ['update:modelValue', 'change', 'select'],
        data() {
            return {
                isOpen: false,
                searchQuery: '',
                highlightedIndex: -1,
            };
        },
        computed: {
            normalizedOptions() {
                if (!this.options) return [];
                let opts = this.options;

                // Support Key-Value Objects (e.g. { text: 'Text', price: 'Price' })
                if (!Array.isArray(opts) && typeof opts === 'object') {
                    return Object.entries(opts).map(([key, opt]) => {
                        if (typeof opt === 'object' && opt !== null) {
                            return {
                                value: opt.value ?? opt.id ?? key,
                                label: String(opt.label ?? opt.name ?? opt.title ?? key),
                                badge: opt.badge ?? null,
                                disabled: Boolean(opt.disabled),
                            };
                        }
                        return {
                            value: key,
                            label: String(opt),
                            badge: null,
                            disabled: false,
                        };
                    });
                }

                // Support Standard Array of Objects or Primitives
                if (Array.isArray(opts)) {
                    return opts.map((opt) => {
                        if (typeof opt === 'object' && opt !== null) {
                            return {
                                value: opt.value ?? opt.id ?? opt.key,
                                label: String(opt.label ?? opt.name ?? opt.title ?? opt.value),
                                badge: opt.badge ?? null,
                                disabled: Boolean(opt.disabled),
                            };
                        }
                        return {
                            value: opt,
                            label: String(opt),
                            badge: null,
                            disabled: false,
                        };
                    });
                }

                return [];
            },
            filteredOptions() {
                if (!this.searchQuery) return this.normalizedOptions;
                const q = this.searchQuery.toLowerCase();
                return this.normalizedOptions.filter((o) => o.label.toLowerCase().includes(q));
            },
            selectedOptions() {
                if (this.multiple) {
                    const vals = Array.isArray(this.modelValue) ? this.modelValue : [];
                    return this.normalizedOptions.filter((o) => vals.some(v => String(v) === String(o.value)));
                }
                return this.normalizedOptions.find((o) => String(o.value) === String(this.modelValue)) || null;
            },
            formValue() {
                if (this.multiple) {
                    return Array.isArray(this.modelValue) ? JSON.stringify(this.modelValue) : '';
                }
                return this.modelValue ?? '';
            },
        },
        mounted() {
            document.addEventListener('click', this.handleClickOutside);
        },
        beforeUnmount() {
            document.removeEventListener('click', this.handleClickOutside);
        },
        methods: {
            toggle() {
                if (this.disabled) return;
                this.isOpen = !this.isOpen;
                if (this.isOpen) {
                    this.searchQuery = '';
                    this.highlightedIndex = -1;
                    this.$nextTick(() => {
                        this.$refs.searchInput?.focus();
                    });
                }
            },
            handleClickOutside(e) {
                if (this.$refs.wrapperRef && !this.$refs.wrapperRef.contains(e.target)) {
                    this.isOpen = false;
                }
            },
            selectOption(opt) {
                if (opt.disabled) return;

                if (this.multiple) {
                    const currentVals = Array.isArray(this.modelValue) ? [...this.modelValue] : [];
                    const idx = currentVals.findIndex(v => String(v) === String(opt.value));
                    if (idx > -1) {
                        currentVals.splice(idx, 1);
                    } else {
                        currentVals.push(opt.value);
                    }
                    this.$emit('update:modelValue', currentVals);
                    this.$emit('change', currentVals);
                } else {
                    this.$emit('update:modelValue', opt.value);
                    this.$emit('change', opt.value);
                    this.$emit('select', opt);
                    this.isOpen = false;
                }
            },
            isSelected(opt) {
                if (this.multiple) {
                    return Array.isArray(this.modelValue) && this.modelValue.some(v => String(v) === String(opt.value));
                }
                return String(this.modelValue) === String(opt.value);
            },
            removeTag(val) {
                if (!this.multiple) return;
                const currentVals = Array.isArray(this.modelValue) ? [...this.modelValue] : [];
                const idx = currentVals.findIndex(v => String(v) === String(val));
                if (idx > -1) {
                    currentVals.splice(idx, 1);
                    this.$emit('update:modelValue', currentVals);
                    this.$emit('change', currentVals);
                }
            },
            clearAll() {
                const next = this.multiple ? [] : null;
                this.$emit('update:modelValue', next);
                this.$emit('change', next);
            },
            highlightNext() {
                if (this.filteredOptions.length === 0) return;
                this.highlightedIndex = (this.highlightedIndex + 1) % this.filteredOptions.length;
            },
            highlightPrev() {
                if (this.filteredOptions.length === 0) return;
                this.highlightedIndex = (this.highlightedIndex - 1 + this.filteredOptions.length) % this.filteredOptions.length;
            },
            selectHighlighted() {
                if (this.highlightedIndex >= 0 && this.highlightedIndex < this.filteredOptions.length) {
                    this.selectOption(this.filteredOptions[this.highlightedIndex]);
                }
            },
        },
    };

    if (typeof window.registerVueComponent === 'function') {
        window.registerVueComponent('v-select', SelectComponent);
        window.registerVueComponent('ui-select', SelectComponent);
    } else if (window.app && window.app.component) {
        window.app.component('v-select', SelectComponent);
        window.app.component('ui-select', SelectComponent);
    }
</script>
@endPushOnce
