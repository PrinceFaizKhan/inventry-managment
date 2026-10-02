<script setup lang="ts">
import { ChevronDown, X } from 'lucide-vue-next'
import { nextTick, ref, watch } from 'vue'
import type { GridBulkAction } from '../../types/grid'

const props = defineProps<{ count: number; actions: GridBulkAction[]; allowed: Record<string, boolean> }>()
const emit = defineEmits<{ action: [key: GridBulkAction['key']]; clear: [] }>()
const open = ref(false)
const trigger = ref<HTMLButtonElement | null>(null)
const options = ref<HTMLButtonElement[]>([])
const available = () => props.actions.filter((action) => props.allowed[action.key])
watch(() => props.count, (count) => { if (!count) open.value = false })
async function openMenu(): Promise<void> { open.value = !open.value; if (open.value) { await nextTick(); options.value[0]?.focus() } }
function keydown(event: KeyboardEvent): void {
  const actions = available()
  if (!actions.length) return
  if (event.key === 'Escape') { open.value = false; trigger.value?.focus(); return }
  if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
    event.preventDefault()
    const index = options.value.indexOf(document.activeElement as HTMLButtonElement)
    const next = event.key === 'ArrowDown' ? (index + 1) % actions.length : (index <= 0 ? actions.length - 1 : index - 1)
    options.value[next]?.focus()
  }
  if (event.key === 'Home') { event.preventDefault(); options.value[0]?.focus() }
  if (event.key === 'End') { event.preventDefault(); options.value[actions.length - 1]?.focus() }
}
function choose(key: GridBulkAction['key']): void { emit('action', key); open.value = false; trigger.value?.focus() }
function focusTrigger(): void { trigger.value?.focus() }
defineExpose({ focusTrigger })
</script>
<template><div v-if="count" class="bulk-toolbar" @keydown="keydown"><strong>{{ count }} selected</strong><div class="bulk-toolbar__right"><div class="bulk-menu-wrap"><button ref="trigger" type="button" class="bulk-menu-trigger" aria-haspopup="menu" :aria-expanded="open" @click="openMenu">Actions <ChevronDown :size="15"/></button><div v-if="open" class="bulk-menu" role="menu"><button v-for="(action,index) in available()" :key="action.key" :ref="(element) => { if(element) options[index]=element as HTMLButtonElement }" type="button" role="menuitem" :class="{ destructive: action.key === 'delete_selected' }" @click="choose(action.key)">{{ action.label }}</button><span v-if="!available().length" class="bulk-menu__empty">No permitted actions</span></div></div><button type="button" class="bulk-clear" aria-label="Clear selection" title="Clear selection" @click="emit('clear')"><X :size="17"/></button></div></div></template>
