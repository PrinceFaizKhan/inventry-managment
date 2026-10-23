/**
 * Universal Custom Toggle Switch Component for Haseen Telecom.
 * Adheres to Strict Zero .vue Policy (Pure JS SFC Object).
 * Usable everywhere as <v-toggle>, <ui-toggle>, or <toggle-switch>.
 */
export const ToggleComponent = {
  name: 'VToggle',
  props: {
    modelValue: {
      type: [Boolean, Number, String],
      default: false,
    },
    label: {
      type: String,
      default: '',
    },
    description: {
      type: String,
      default: '',
    },
    disabled: {
      type: Boolean,
      default: false,
    },
    size: {
      type: String,
      default: 'md', // 'sm' | 'md' | 'lg'
    },
    name: {
      type: String,
      default: '',
    },
    id: {
      type: String,
      default: '',
    },
  },
  emits: ['update:modelValue', 'change'],
  computed: {
    isChecked() {
      if (typeof this.modelValue === 'string') {
        return this.modelValue === '1' || this.modelValue === 'true'
      }
      return Boolean(this.modelValue)
    },
    trackClasses() {
      if (this.size === 'sm') return 'h-4 w-7'
      if (this.size === 'lg') return 'h-6 w-11'
      return 'h-5 w-9'
    },
    thumbClasses() {
      if (this.size === 'sm') {
        return this.isChecked ? 'translate-x-3 h-3 w-3' : 'translate-x-0.5 h-3 w-3'
      }
      if (this.size === 'lg') {
        return this.isChecked ? 'translate-x-5 h-5 w-5' : 'translate-x-0.5 h-5 w-5'
      }
      return this.isChecked ? 'translate-x-4 h-4 w-4' : 'translate-x-0.5 h-4 w-4'
    },
  },
  methods: {
    toggle() {
      if (this.disabled) return
      const next = !this.isChecked
      this.$emit('update:modelValue', next)
      this.$emit('change', next)
    },
    onInputChange(e) {
      const val = e.target.checked
      this.$emit('update:modelValue', val)
      this.$emit('change', val)
    },
  },
  template: `
    <div
      class="inline-flex items-center justify-between cursor-pointer select-none group py-1"
      :class="{ 'opacity-50 cursor-not-allowed': disabled }"
      @click="toggle"
    >
      <div v-if="label || description" class="pr-3 text-left">
        <span v-if="label" class="block text-xs font-semibold text-[var(--text)] group-hover:text-[var(--text)] transition-colors">
          {{ label }}
        </span>
        <span v-if="description" class="block text-[11px] text-[var(--muted)]">
          {{ description }}
        </span>
      </div>

      <!-- Hidden standard input for standard form submission -->
      <input
        v-if="name"
        type="checkbox"
        :id="id"
        :name="name"
        :checked="isChecked"
        :disabled="disabled"
        class="sr-only"
        @change="onInputChange"
      />

      <!-- Native Custom Switch Button -->
      <button
        type="button"
        role="switch"
        :aria-checked="isChecked ? 'true' : 'false'"
        :disabled="disabled"
        class="relative inline-flex flex-shrink-0 cursor-pointer rounded-full border border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-[var(--teal)]/40 focus:ring-offset-1"
        :class="[
          trackClasses,
          disabled ? 'cursor-not-allowed opacity-50' : '',
          isChecked ? 'bg-[var(--teal)] shadow-[0_0_12px_rgba(20,184,166,0.35)]' : 'bg-slate-300 dark:bg-slate-800 border-slate-400/20 dark:border-white/10'
        ]"
        @click.stop="toggle"
      >
        <span class="sr-only">{{ label || 'Toggle' }}</span>
        <span
          aria-hidden="true"
          class="pointer-events-none inline-block transform rounded-full bg-white shadow-md ring-0 transition duration-200 ease-in-out"
          :class="thumbClasses"
        />
      </button>
    </div>
  `,
}
