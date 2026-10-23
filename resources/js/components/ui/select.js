/**
 * Universal Custom Dropdown / Select Component for Haseen Telecom.
 * Adheres to Strict Zero .vue Policy (Pure JS SFC Object).
 * Usable everywhere as <v-select> or <ui-select>.
 */
export const SelectComponent = {
  name: 'VSelect',
  props: {
    modelValue: {
      type: [String, Number, Array, Boolean],
      default: null,
    },
    options: {
      type: Array,
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
    }
  },
  computed: {
    normalizedOptions() {
      return this.options.map((opt) => {
        if (typeof opt === 'object' && opt !== null) {
          return {
            value: opt.value ?? opt.id ?? opt.key,
            label: String(opt.label ?? opt.name ?? opt.title ?? opt.value),
            badge: opt.badge ?? null,
            disabled: Boolean(opt.disabled),
          }
        }
        return {
          value: opt,
          label: String(opt),
          badge: null,
          disabled: false,
        }
      })
    },
    filteredOptions() {
      if (!this.searchQuery) return this.normalizedOptions
      const q = this.searchQuery.toLowerCase()
      return this.normalizedOptions.filter((o) => o.label.toLowerCase().includes(q))
    },
    selectedOptions() {
      if (this.multiple) {
        const vals = Array.isArray(this.modelValue) ? this.modelValue : []
        return this.normalizedOptions.filter((o) => vals.includes(o.value))
      }
      return this.normalizedOptions.find((o) => o.value === this.modelValue) || null
    },
  },
  mounted() {
    document.addEventListener('click', this.handleClickOutside)
  },
  beforeUnmount() {
    document.removeEventListener('click', this.handleClickOutside)
  },
  methods: {
    toggle() {
      if (this.disabled) return
      this.isOpen = !this.isOpen
      if (this.isOpen) {
        this.searchQuery = ''
        this.$nextTick(() => {
          this.$refs.searchInput?.focus()
        })
      }
    },
    handleClickOutside(e) {
      if (this.$refs.wrapperRef && !this.$refs.wrapperRef.contains(e.target)) {
        this.isOpen = false
      }
    },
    selectOption(opt) {
      if (opt.disabled) return

      if (this.multiple) {
        const currentVals = Array.isArray(this.modelValue) ? [...this.modelValue] : []
        const idx = currentVals.indexOf(opt.value)
        if (idx > -1) {
          currentVals.splice(idx, 1)
        } else {
          currentVals.push(opt.value)
        }
        this.$emit('update:modelValue', currentVals)
        this.$emit('change', currentVals)
      } else {
        this.$emit('update:modelValue', opt.value)
        this.$emit('change', opt.value)
        this.$emit('select', opt)
        this.isOpen = false
      }
    },
    isSelected(opt) {
      if (this.multiple) {
        return Array.isArray(this.modelValue) && this.modelValue.includes(opt.value)
      }
      return this.modelValue === opt.value
    },
    removeTag(val) {
      if (!this.multiple) return
      const currentVals = Array.isArray(this.modelValue) ? [...this.modelValue] : []
      const idx = currentVals.indexOf(val)
      if (idx > -1) {
        currentVals.splice(idx, 1)
        this.$emit('update:modelValue', currentVals)
        this.$emit('change', currentVals)
      }
    },
    clearAll() {
      const next = this.multiple ? [] : null
      this.$emit('update:modelValue', next)
      this.$emit('change', next)
    },
  },
  template: `
    <div ref="wrapperRef" class="select-component-wrapper space-y-1.5 w-full relative">
      <div v-if="label" class="flex items-center justify-between">
        <label :for="id" class="block text-xs font-semibold text-[var(--text)]">
          {{ label }}
        </label>
      </div>

      <!-- Trigger Button -->
      <button
        type="button"
        @click="toggle"
        :disabled="disabled"
        class="w-full flex items-center justify-between gap-2 rounded-xl border text-left transition-all duration-200 outline-none"
        :class="[
          size === 'sm' ? 'min-h-[34px] px-2.5 py-1 text-xs' : 'min-h-[42px] px-3.5 py-1.5 text-xs',
          isOpen ? 'border-[var(--teal)] ring-2 ring-[var(--teal)]/20 bg-[var(--card)]' : 'border-[var(--border)] bg-[var(--card)] hover:border-[var(--teal)]/60',
          error ? 'border-red-500 ring-1 ring-red-500/30' : '',
          disabled ? 'opacity-60 bg-[var(--page)] cursor-not-allowed' : 'cursor-pointer'
        ]"
      >
        <!-- Single Mode Selected Value -->
        <span v-if="!multiple" class="truncate flex-1 font-medium" :class="selectedOptions ? 'text-[var(--text)]' : 'text-[var(--muted)]'">
          {{ selectedOptions ? selectedOptions.label : placeholder }}
        </span>

        <!-- Multiple Mode Tags -->
        <div v-else class="flex flex-wrap items-center gap-1.5 flex-1 py-0.5">
          <span v-if="selectedOptions.length === 0" class="text-[var(--muted)]">
            {{ placeholder }}
          </span>
          <span
            v-for="opt in selectedOptions"
            :key="opt.value"
            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-[var(--soft)] text-[var(--teal)] text-[11px] font-semibold border border-[var(--teal)]/20"
          >
            <span>{{ opt.label }}</span>
            <button type="button" @click.stop="removeTag(opt.value)" class="hover:opacity-70 font-bold">&times;</button>
          </span>
        </div>

        <div class="flex items-center gap-1.5 text-[var(--muted)] shrink-0">
          <!-- Clear Button -->
          <span
            v-if="clearable && !disabled && ((multiple && selectedOptions.length > 0) || (!multiple && selectedOptions))"
            @click.stop="clearAll"
            class="hover:text-red-500 p-0.5 text-sm font-bold leading-none cursor-pointer"
            title="Clear selection"
          >&times;</span>

          <!-- Chevron -->
          <svg
            class="transition-transform duration-200"
            :class="{ 'rotate-180 text-[var(--teal)]': isOpen }"
            width="13"
            height="13"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2.5"
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
          class="absolute z-50 mt-1 w-full rounded-xl border border-[var(--border)] bg-[var(--card)] shadow-2xl overflow-hidden backdrop-blur-xl"
        >
          <!-- Search input inside dropdown -->
          <div v-if="searchable" class="p-2 border-b border-[var(--border)] bg-[var(--page)]">
            <div class="relative">
              <input
                ref="searchInput"
                type="text"
                v-model="searchQuery"
                placeholder="Search..."
                class="w-full h-7 pl-7 pr-3 rounded-lg border border-[var(--border)] bg-[var(--card)] text-xs text-[var(--text)] placeholder-[var(--muted)] focus:outline-none focus:border-[var(--teal)] transition-all"
              />
              <svg class="absolute left-2 top-2 text-[var(--muted)]" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
            </div>
          </div>

          <!-- Options list -->
          <div class="max-h-60 overflow-y-auto p-1 custom-scrollbar text-xs">
            <div
              v-for="opt in filteredOptions"
              :key="opt.value"
              @click="selectOption(opt)"
              class="flex items-center justify-between px-3 py-2 rounded-lg cursor-pointer transition-colors"
              :class="[
                isSelected(opt) ? 'bg-[var(--soft)] text-[var(--teal)] font-semibold' : 'text-[var(--text)] hover:bg-[var(--border)]/30',
                opt.disabled ? 'opacity-40 cursor-not-allowed' : ''
              ]"
            >
              <div class="flex items-center gap-2">
                <span
                  v-if="multiple"
                  class="w-3.5 h-3.5 rounded border flex items-center justify-center text-[10px]"
                  :class="isSelected(opt) ? 'bg-[var(--teal)] border-[var(--teal)] text-white' : 'border-[var(--border)] bg-[var(--card)]'"
                >
                  <svg v-if="isSelected(opt)" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                </span>
                <span>{{ opt.label }}</span>
              </div>

              <div class="flex items-center gap-1.5">
                <span v-if="opt.badge" class="px-1.5 py-0.2 rounded text-[10px] font-mono bg-[var(--border)] text-[var(--muted)]">{{ opt.badge }}</span>
                <svg v-if="!multiple && isSelected(opt)" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" class="text-[var(--teal)]"><polyline points="20 6 9 17 4 12"/></svg>
              </div>
            </div>

            <div v-if="filteredOptions.length === 0" class="py-4 text-center text-[var(--muted)] text-xs">
              No options found
            </div>
          </div>
        </div>
      </transition>

      <span v-if="error" class="block text-[11px] font-medium text-red-500">{{ error }}</span>
    </div>
  `,
}
