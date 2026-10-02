<script setup lang="ts">
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import type { CSSProperties } from 'vue'
import { Check, ChevronDown, Search } from 'lucide-vue-next'

export interface SelectControlOption {
  value: string | number
  label: string
  disabled?: boolean
}

export type SelectControlValue = string | number | Array<string | number> | null

const props = withDefaults(defineProps<{
  modelValue: SelectControlValue
  options: SelectControlOption[] | Record<string, string>
  mode?: 'single' | 'multiple' | 'radio'
  placeholder?: string
  disabled?: boolean
  required?: boolean
  searchable?: boolean
  invalid?: boolean
  ariaLabel?: string
  id?: string
  name?: string
}>(), {
  mode: 'single',
  placeholder: 'Select an option',
  disabled: false,
  required: false,
  searchable: false,
  invalid: false,
  ariaLabel: undefined,
  id: undefined,
  name: undefined,
})

const emit = defineEmits<{
  'update:modelValue': [value: SelectControlValue]
  change: [value: SelectControlValue]
}>()

const root = ref<HTMLElement | null>(null)
const trigger = ref<HTMLButtonElement | null>(null)
const popup = ref<HTMLElement | null>(null)
const searchInput = ref<HTMLInputElement | null>(null)
const popupStyle = ref<CSSProperties>({})
const isOpen = ref(false)
const searchText = ref('')
const activeIndex = ref(-1)
const optionNodes = ref<HTMLElement[]>([])
const instanceId = `select-control-${Math.random().toString(36).slice(2, 10)}`

const normalizedOptions = computed<SelectControlOption[]>(() => {
  if (Array.isArray(props.options)) {
    return props.options
  }

  return Object.entries(props.options).map(([value, label]) => ({ value, label }))
})

const selectedValues = computed<Array<string | number>>(() => Array.isArray(props.modelValue)
  ? props.modelValue
  : props.modelValue === null || props.modelValue === ''
    ? []
    : [props.modelValue])

const selectedOptions = computed(() => normalizedOptions.value.filter((option) => selectedValues.value.some((value) => String(value) === String(option.value))))
const visibleOptions = computed(() => {
  const query = searchText.value.trim().toLocaleLowerCase()
  return query === ''
    ? normalizedOptions.value
    : normalizedOptions.value.filter((option) => option.label.toLocaleLowerCase().includes(query))
})
const displayValue = computed(() => selectedOptions.value.length > 0
  ? selectedOptions.value.map((option) => option.label).join(', ')
  : props.placeholder)
const listboxId = computed(() => `${props.id || instanceId}-listbox`)
const activeOptionId = computed(() => activeIndex.value >= 0 && visibleOptions.value[activeIndex.value]
  ? `${props.id || instanceId}-option-${activeIndex.value}`
  : undefined)

watch([visibleOptions, isOpen], async ([options, open]) => {
  if (open) {
    activeIndex.value = Math.min(Math.max(activeIndex.value, 0), options.length - 1)
    await nextTick()
    updatePopupPosition()
    scrollActiveOptionIntoView()
  }
})

onMounted(() => {
  document.addEventListener('pointerdown', handleOutsideClick)
  window.addEventListener('resize', updatePopupPosition)
  window.addEventListener('scroll', updatePopupPosition, true)
})
onBeforeUnmount(() => {
  document.removeEventListener('pointerdown', handleOutsideClick)
  window.removeEventListener('resize', updatePopupPosition)
  window.removeEventListener('scroll', updatePopupPosition, true)
})

function setValue(value: SelectControlValue): void {
  emit('update:modelValue', value)
  emit('change', value)
}

function isSelected(option: SelectControlOption): boolean {
  return selectedValues.value.some((value) => String(value) === String(option.value))
}

function choose(option: SelectControlOption): void {
  if (option.disabled || props.disabled) {
    return
  }

  if (props.mode === 'multiple') {
    const next = isSelected(option)
      ? selectedValues.value.filter((value) => String(value) !== String(option.value))
      : [...selectedValues.value, option.value]
    setValue(next)
    return
  }

  setValue(option.value)
  close()
}

function open(): void {
  if (props.disabled || props.mode === 'radio') {
    return
  }

  isOpen.value = true
  searchText.value = ''
  const currentIndex = visibleOptions.value.findIndex(isSelected)
  activeIndex.value = currentIndex >= 0 ? currentIndex : (visibleOptions.value.length > 0 ? 0 : -1)
  if (props.searchable) {
    nextTick(() => searchInput.value?.focus())
  }
}

function close(restoreFocus = false): void {
  isOpen.value = false
  searchText.value = ''
  if (restoreFocus) {
    nextTick(() => trigger.value?.focus())
  }
}

function handleOutsideClick(event: PointerEvent): void {
  const target = event.target as Node
  if (isOpen.value && !root.value?.contains(target) && !popup.value?.contains(target)) {
    close()
  }
}

function updatePopupPosition(): void {
  const bounds = trigger.value?.getBoundingClientRect()
  if (!bounds || !isOpen.value) return

  const below = window.innerHeight - bounds.bottom - 8
  const above = bounds.top - 8
  const openAbove = below < 180 && above > below
  const availableHeight = Math.max(100, Math.min(440, openAbove ? above : below))
  popupStyle.value = {
    position: 'fixed',
    left: `${Math.max(8, Math.min(bounds.left, window.innerWidth - bounds.width - 8))}px`,
    width: `${Math.min(bounds.width, window.innerWidth - 16)}px`,
    maxHeight: `${availableHeight}px`,
    top: openAbove ? 'auto' : `${bounds.bottom + 4}px`,
    bottom: openAbove ? `${window.innerHeight - bounds.top + 4}px` : 'auto',
  }
}

function moveActive(offset: number): void {
  if (visibleOptions.value.length === 0) {
    activeIndex.value = -1
    return
  }

  const current = activeIndex.value < 0 ? (offset > 0 ? -1 : 0) : activeIndex.value
  activeIndex.value = (current + offset + visibleOptions.value.length) % visibleOptions.value.length
  nextTick(scrollActiveOptionIntoView)
}

function scrollActiveOptionIntoView(): void {
  optionNodes.value[activeIndex.value]?.scrollIntoView({ block: 'nearest' })
}

function selectActive(): void {
  const option = visibleOptions.value[activeIndex.value]
  if (option) {
    choose(option)
  }
}

function handleKeydown(event: KeyboardEvent): void {
  if (props.mode === 'radio') {
    return
  }

  if (event.target instanceof HTMLInputElement && event.key === ' ') {
    return
  }

  if (!isOpen.value && ['ArrowDown', 'ArrowUp', 'Enter', ' '].includes(event.key)) {
    event.preventDefault()
    open()
    return
  }

  if (!isOpen.value) {
    return
  }

  if (event.key === 'Escape') {
    event.preventDefault()
    close(true)
  } else if (event.key === 'ArrowDown') {
    event.preventDefault()
    moveActive(1)
  } else if (event.key === 'ArrowUp') {
    event.preventDefault()
    moveActive(-1)
  } else if (event.key === 'Home') {
    event.preventDefault()
    activeIndex.value = 0
    nextTick(scrollActiveOptionIntoView)
  } else if (event.key === 'End') {
    event.preventDefault()
    activeIndex.value = visibleOptions.value.length - 1
    nextTick(scrollActiveOptionIntoView)
  } else if (event.key === 'Enter' || event.key === ' ') {
    event.preventDefault()
    selectActive()
  }
}

function setOptionNode(element: Element | null, index: number): void {
  if (element instanceof HTMLElement) {
    optionNodes.value[index] = element
  }
}
</script>

<template>
  <div v-if="mode === 'radio'" class="select-control-radio" role="radiogroup" :aria-label="ariaLabel" :aria-required="required" :aria-disabled="disabled">
    <label v-for="option in normalizedOptions" :key="String(option.value)" class="select-control-radio__option" :class="{ 'is-disabled': disabled || option.disabled }">
      <input
        :id="id ? `${id}-${option.value}` : undefined"
        type="radio"
        :name="name || id || instanceId"
        :value="option.value"
        :checked="isSelected(option)"
        :disabled="disabled || option.disabled"
        :required="required"
        @change="setValue(option.value)"
      />
      <span>{{ option.label }}</span>
    </label>
  </div>

  <div v-else ref="root" class="select-control" :class="{ 'is-open': isOpen, 'is-disabled': disabled, 'has-error': invalid, 'is-multiple': mode === 'multiple' }">
    <button
      :id="id"
      ref="trigger"
      type="button"
      class="select-control__trigger"
      role="combobox"
      aria-haspopup="listbox"
      :aria-controls="listboxId"
      :aria-expanded="isOpen"
      :aria-label="ariaLabel"
      :aria-invalid="invalid"
      :aria-required="required"
      :aria-activedescendant="activeOptionId"
      :disabled="disabled"
      @click="isOpen ? close() : open()"
      @keydown="handleKeydown"
    >
      <span class="select-control__value" :class="{ 'is-placeholder': selectedOptions.length === 0 }">{{ displayValue }}</span>
      <span v-if="mode === 'multiple' && selectedValues.length" class="select-control__count">{{ selectedValues.length }}</span>
      <ChevronDown :size="16" class="select-control__chevron"/>
    </button>

    <Teleport to="body">
      <div v-if="isOpen" ref="popup" class="select-control__popup" :style="popupStyle" @keydown="handleKeydown">
        <label v-if="searchable" class="select-control__search"><Search :size="15"/><input ref="searchInput" v-model="searchText" type="search" role="combobox" aria-autocomplete="list" aria-haspopup="listbox" aria-expanded="true" :aria-controls="listboxId" :aria-activedescendant="activeOptionId" :placeholder="`Search ${placeholder.toLowerCase()}…`"/></label>
        <div :id="listboxId" class="select-control__options" role="listbox" :aria-multiselectable="mode === 'multiple'">
          <button
            v-for="(option,index) in visibleOptions"
            :id="`${id || instanceId}-option-${index}`"
            :key="String(option.value)"
            :ref="(element) => setOptionNode(element as Element | null,index)"
            type="button"
            role="option"
            tabindex="-1"
            class="select-control__option"
            :class="{ 'is-active': activeIndex === index, 'is-selected': isSelected(option), 'is-disabled': option.disabled }"
            :aria-selected="isSelected(option)"
            :aria-disabled="option.disabled"
            :disabled="option.disabled"
            @mousemove="activeIndex=index"
            @mousedown.prevent
            @click="choose(option)"
          >
            <span v-if="mode === 'multiple'" class="select-control__check" :class="{ checked: isSelected(option) }"><Check v-if="isSelected(option)" :size="13"/></span>
            <span class="select-control__option-label">{{ option.label }}</span>
            <Check v-if="mode !== 'multiple' && isSelected(option)" :size="15" class="select-control__selected-icon"/>
          </button>
          <p v-if="visibleOptions.length === 0" class="select-control__empty">No options found.</p>
        </div>
      </div>
    </Teleport>
  </div>
</template>
