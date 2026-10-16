@props([
    'name' => null,
    'options' => [],
    'modelValue' => null,
    'placeholder' => 'Select an option',
    'searchable' => false,
])

<v-select-control
    @if($name) name="{{ $name }}" id="{{ $name }}" @endif
    :options='@json($options)'
    :model-value='@json($modelValue)'
    placeholder="{{ $placeholder }}"
    :searchable="{{ $searchable ? 'true' : 'false' }}"
></v-select-control>

@pushOnce('scripts')
<script type="text/x-template" id="v-select-control-template">
    <div ref="root" class="select-control" :class="{ 'is-open': isOpen, 'has-error': invalid }">
        <button
            ref="trigger"
            type="button"
            class="select-control__trigger"
            :aria-expanded="isOpen"
            :disabled="disabled"
            @click="toggle"
        >
            <span class="select-control__value" :class="{ 'is-placeholder': selectedOptions.length === 0 }">
                @{{ displayValue }}
            </span>
            <svg class="select-control__chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
        </button>

        <div
            v-if="isOpen"
            ref="popup"
            class="select-control__popup"
            :style="popupStyle"
        >
            <div v-if="searchable" class="select-control__search">
                <input
                    ref="searchInput"
                    v-model="searchText"
                    type="text"
                    placeholder="Search..."
                />
            </div>
            <div class="select-control__options">
                <button
                    v-for="option in visibleOptions"
                    :key="option.value"
                    type="button"
                    class="select-control__option"
                    :class="{ 'is-selected': isSelected(option) }"
                    @click="choose(option)"
                >
                    <span class="select-control__option-label">@{{ option.label }}</span>
                </button>
                <div v-if="visibleOptions.length === 0" class="select-control__empty">
                    No options found
                </div>
            </div>
        </div>
    </div>
</script>

<script type="module">
    const selectControlComponent = {
        template: '#v-select-control-template',
        props: {
            options: { type: [Array, Object], default: () => [] },
            modelValue: { type: [String, Number, Array], default: null },
            placeholder: { type: String, default: 'Select an option' },
            disabled: { type: Boolean, default: false },
            searchable: { type: Boolean, default: false },
            invalid: { type: Boolean, default: false }
        },
        emits: ['update:modelValue', 'change'],
        data() {
            return {
                isOpen: false,
                searchText: '',
                popupStyle: {}
            };
        },
        computed: {
            normalizedOptions() {
                if (Array.isArray(this.options)) return this.options;
                return Object.entries(this.options || {}).map(([value, label]) => ({ value, label }));
            },
            selectedOptions() {
                const val = Array.isArray(this.modelValue) ? this.modelValue : (this.modelValue !== null && this.modelValue !== '' ? [this.modelValue] : []);
                return this.normalizedOptions.filter(o => val.some(v => String(v) === String(o.value)));
            },
            visibleOptions() {
                const q = this.searchText.trim().toLowerCase();
                if (!q) return this.normalizedOptions;
                return this.normalizedOptions.filter(o => o.label.toLowerCase().includes(q));
            },
            displayValue() {
                if (this.selectedOptions.length > 0) {
                    return this.selectedOptions.map(o => o.label).join(', ');
                }
                return this.placeholder;
            }
        },
        mounted() {
            document.addEventListener('pointerdown', this.handleOutsideClick);
        },
        beforeUnmount() {
            document.removeEventListener('pointerdown', this.handleOutsideClick);
        },
        methods: {
            toggle() {
                if (this.disabled) return;
                this.isOpen = !this.isOpen;
                if (this.isOpen) {
                    this.updatePopupPosition();
                }
            },
            close() {
                this.isOpen = false;
                this.searchText = '';
            },
            choose(option) {
                this.$emit('update:modelValue', option.value);
                this.$emit('change', option.value);
                this.close();
            },
            isSelected(option) {
                return this.selectedOptions.some(o => String(o.value) === String(option.value));
            },
            handleOutsideClick(event) {
                if (this.$el && !this.$el.contains(event.target)) {
                    this.close();
                }
            },
            updatePopupPosition() {
                const bounds = this.$refs.trigger?.getBoundingClientRect();
                if (!bounds) return;
                this.popupStyle = {
                    position: 'fixed',
                    left: `${bounds.left}px`,
                    width: `${bounds.width}px`,
                    top: `${bounds.bottom + 4}px`,
                    zIndex: 100
                };
            }
        }
    };
    app.component('v-select-control', selectControlComponent);
    app.component('select-control', selectControlComponent);
</script>
@endpushOnce
