<script setup lang="ts">
import { nextTick, onBeforeUnmount, onMounted, ref } from 'vue'
import { X } from 'lucide-vue-next'
import type { GridFilter } from '../../types/grid'
import SelectControl from '../ui/SelectControl.vue'
import type { SelectControlValue } from '../ui/SelectControl.vue'

const props = defineProps<{
  filters: GridFilter[]
  values: Record<string, string | number>
  options: Record<string, { value: string | number; label: string }[]>
}>()

const emit = defineEmits<{
  close: []
  apply: [values: Record<string, string | number>]
}>()

const drawer = ref<HTMLElement | null>(null)
const firstFocus = ref<HTMLButtonElement | null>(null)
const draft = ref<Record<string, string | number>>({ ...props.values })
let previousBodyOverflow = ''

onMounted(async () => {
  previousBodyOverflow = document.body.style.overflow
  document.body.style.overflow = 'hidden'
  await nextTick()
  firstFocus.value?.focus()
})

onBeforeUnmount(() => {
  document.body.style.overflow = previousBodyOverflow
})

function updateFilter(key: string, value: SelectControlValue): void {
  if (value === '' || value === null) {
    delete draft.value[key]
    return
  }

  if (Array.isArray(value)) return
  draft.value[key] = value
}

function resetDraft(): void {
  draft.value = {}
}

function applyDraft(): void {
  emit('apply', { ...draft.value })
}

function handleKeydown(event: KeyboardEvent): void {
  if (event.key === 'Escape') {
    event.preventDefault()
    emit('close')
    return
  }

  if (event.key !== 'Tab' || !drawer.value) {
    return
  }

  const focusable = Array.from(drawer.value.querySelectorAll<HTMLElement>(
    'button:not(:disabled), select:not(:disabled), input:not(:disabled), [tabindex]:not([tabindex="-1"])',
  ))
  const first = focusable[0]
  const last = focusable.at(-1)

  if (event.shiftKey && document.activeElement === first) {
    event.preventDefault()
    last?.focus()
  } else if (!event.shiftKey && document.activeElement === last) {
    event.preventDefault()
    first?.focus()
  }
}
</script>

<template>
  <Teleport to="body">
    <div class="filter-drawer-layer" @click.self="emit('close')">
      <aside
        ref="drawer"
        class="filter-drawer"
        role="dialog"
        aria-modal="true"
        aria-labelledby="filter-drawer-title"
        tabindex="-1"
        @keydown="handleKeydown"
      >
        <header class="filter-drawer__header">
          <h2 id="filter-drawer-title">Filters</h2>
          <button
            ref="firstFocus"
            type="button"
            class="filter-drawer__close"
            aria-label="Close filters"
            @click="emit('close')"
          >
            <X :size="18" />
          </button>
        </header>

        <div class="filter-drawer__body">
          <label v-for="filter in filters" :key="filter.key" class="filter-drawer__field">
            <span>{{ filter.label }}</span>
            <SelectControl
              :model-value="draft[filter.key] ?? ''"
              :options="options[filter.key] || []"
              :placeholder="`All ${filter.label.toLowerCase()}`"
              :aria-label="filter.label"
              @update:model-value="updateFilter(filter.key,$event)"
            />
          </label>
        </div>

        <footer class="filter-drawer__footer">
          <button type="button" class="button button--outline" @click="resetDraft">Reset</button>
          <button type="button" class="button button--primary" @click="applyDraft">Apply filters</button>
        </footer>
      </aside>
    </div>
  </Teleport>
</template>
