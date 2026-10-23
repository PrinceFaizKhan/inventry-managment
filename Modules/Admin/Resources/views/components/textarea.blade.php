@props([
    'name' => null,
    'modelValue' => '',
    'label' => '',
    'placeholder' => '',
    'rows' => 3,
    'maxRows' => 10,
    'maxLength' => null,
    'autoResize' => true,
    'disabled' => false,
    'readonly' => false,
    'id' => null,
    'error' => '',
    'help' => '',
])

@if(!empty($name) || !empty($label) || !empty($placeholder))
<v-textarea
    @if($name) name="{{ $name }}" @endif
    @if(!is_null($modelValue)) :model-value='@json($modelValue)' @endif
    @if($label) label="{{ $label }}" @endif
    placeholder="{{ $placeholder }}"
    :rows="{{ $rows }}"
    :max-rows="{{ $maxRows }}"
    @if(!is_null($maxLength)) :max-length="{{ $maxLength }}" @endif
    :auto-resize="{{ $autoResize ? 'true' : 'false' }}"
    :disabled="{{ $disabled ? 'true' : 'false' }}"
    :readonly="{{ $readonly ? 'true' : 'false' }}"
    @if($id) id="{{ $id }}" @endif
    @if($error) error="{{ $error }}" @endif
    @if($help) help="{{ $help }}" @endif
    {{ $attributes }}
></v-textarea>
@endif

@pushOnce('scripts')
<script type="text/x-template" id="v-textarea-template">
    <div class="textarea-wrapper space-y-1.5 w-full">
        <div v-if="label || hasLimit" class="flex items-center justify-between">
            <label v-if="label" :for="id" class="block text-xs font-semibold text-[var(--text)]">
                @{{ label }}
            </label>
            <span v-if="hasLimit" class="text-[11px] font-mono" :class="characterCount >= Number(maxLength) ? 'text-red-500 font-bold' : 'text-[var(--muted)]'">
                @{{ characterCount }} / @{{ maxLength }}
            </span>
        </div>

        <div class="relative">
            <textarea
                ref="textareaRef"
                :id="id"
                :name="name"
                :rows="rows"
                :value="modelValue"
                :placeholder="placeholder"
                :disabled="disabled"
                :readonly="readonly"
                :maxlength="hasLimit ? Number(maxLength) : undefined"
                @input="onInput"
                @change="onChange"
                @focus="$emit('focus', $event)"
                @blur="$emit('blur', $event)"
                class="w-full px-3 py-2 rounded-xl border text-xs text-[var(--text)] bg-[var(--card)] placeholder-[var(--muted)] transition-all duration-200 outline-none resize-none"
                :class="[
                    error ? 'border-red-500 ring-1 ring-red-500/30' : 'border-[var(--border)] focus:border-[var(--teal)] focus:ring-2 focus:ring-[var(--teal)]/20',
                    disabled ? 'opacity-60 bg-[var(--page)] cursor-not-allowed' : 'hover:border-[var(--teal)]/50'
                ]"
            ></textarea>
        </div>

        <div v-if="error || help" class="flex items-center justify-between text-[11px]">
            <span v-if="error" class="text-red-500 font-medium">@{{ error }}</span>
            <span v-else-if="help" class="text-[var(--muted)]">@{{ help }}</span>
        </div>
    </div>
</script>

<script type="module">
    const TextareaComponent = {
        template: '#v-textarea-template',
        props: {
            modelValue: {
                type: [String, Number],
                default: '',
            },
            label: {
                type: String,
                default: '',
            },
            placeholder: {
                type: String,
                default: '',
            },
            rows: {
                type: [Number, String],
                default: 3,
            },
            maxRows: {
                type: [Number, String],
                default: 10,
            },
            maxLength: {
                type: [Number, String],
                default: null,
            },
            autoResize: {
                type: Boolean,
                default: true,
            },
            disabled: {
                type: Boolean,
                default: false,
            },
            readonly: {
                type: Boolean,
                default: false,
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
            help: {
                type: String,
                default: '',
            },
        },
        emits: ['update:modelValue', 'change', 'input', 'blur', 'focus'],
        computed: {
            characterCount() {
                return String(this.modelValue || '').length;
            },
            hasLimit() {
                return this.maxLength !== null && Number(this.maxLength) > 0;
            },
        },
        mounted() {
            if (this.autoResize) {
                this.resize();
            }
        },
        watch: {
            modelValue() {
                if (this.autoResize) {
                    this.$nextTick(this.resize);
                }
            },
        },
        methods: {
            onInput(e) {
                const val = e.target.value;
                this.$emit('update:modelValue', val);
                this.$emit('input', val);
                if (this.autoResize) {
                    this.resize();
                }
            },
            onChange(e) {
                this.$emit('change', e.target.value);
            },
            resize() {
                const textarea = this.$refs.textareaRef;
                if (!textarea) return;
                textarea.style.height = 'auto';
                const minH = Number(this.rows) * 20 + 20;
                const newHeight = Math.max(minH, textarea.scrollHeight);
                textarea.style.height = `${newHeight}px`;
            },
        },
    };

    if (typeof window.registerVueComponent === 'function') {
        window.registerVueComponent('v-textarea', TextareaComponent);
        window.registerVueComponent('ui-textarea', TextareaComponent);
    } else if (window.app && window.app.component) {
        window.app.component('v-textarea', TextareaComponent);
        window.app.component('ui-textarea', TextareaComponent);
    }
</script>
@endPushOnce
