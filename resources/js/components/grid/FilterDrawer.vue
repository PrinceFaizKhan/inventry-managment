<script setup lang="ts">
import { nextTick, onBeforeUnmount, onMounted, ref } from 'vue'
import { X } from 'lucide-vue-next'
import type { GridFilter } from '../../types/grid'

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

function updateFilter(key: string, event: Event): void {
  const value = (event.target as HTMLSelectElement).value
  if (value === '') {
    delete draft.value[key]
    return
  }

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
            <select :value="draft[filter.key] ?? ''" @change="updateFilter(filter.key, $event)">
              <option value="">All {{ filter.label.toLowerCase() }}</option>
              <option
                v-for="option in options[filter.key] || []"
                :key="option.value"
                :value="option.value"
              >
                {{ option.label }}
              </option>
            </select>
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
