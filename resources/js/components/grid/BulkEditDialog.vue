<script setup lang="ts">
import { nextTick, onMounted, ref } from 'vue'
import { X } from 'lucide-vue-next'
import type { BulkEditField } from '../../types/grid'

const props = defineProps<{ count: number; fields: Record<string, BulkEditField>; submitting: boolean; errors: Record<string, string> }>()
const emit = defineEmits<{ cancel: []; submit: [changes: Record<string, string | number | null>] }>()
const applies = ref<Record<string, boolean>>({})
const clears = ref<Record<string, boolean>>({})
const values = ref<Record<string, string | number>>({})
const firstApply = ref<HTMLInputElement | null>(null)
const fieldKeys = Object.keys(props.fields)
const canApply = () => fieldKeys.some((key) => applies.value[key] && (clears.value[key] || (values.value[key] !== undefined && values.value[key] !== '')))
onMounted(async () => { await nextTick(); firstApply.value?.focus() })
function submit(): void {
  if (!canApply()) return
  const changes: Record<string, string | number | null> = {}
  fieldKeys.forEach((key) => { if (applies.value[key]) changes[key] = clears.value[key] ? null : values.value[key] })
  if (Object.keys(changes).length) emit('submit', changes)
}
function keydown(event: KeyboardEvent): void {
  if (event.key === 'Escape' && !props.submitting) emit('cancel')
  if (event.key === 'Tab') {
    const dialog = event.currentTarget as HTMLElement
    const focusable = Array.from(dialog.querySelectorAll<HTMLElement>('button:not(:disabled), input:not(:disabled), select:not(:disabled)'))
    const first = focusable[0], last = focusable.at(-1)
    if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last?.focus() }
    else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first?.focus() }
  }
}
</script>
<template><div class="confirm-backdrop" role="presentation" @click.self="!submitting && emit('cancel')"><section class="bulk-edit-dialog" role="dialog" aria-modal="true" aria-labelledby="bulk-edit-title" @keydown="keydown"><header><div><h2 id="bulk-edit-title">Edit {{ count }} selected</h2><p>Choose the changes to apply to every selected record.</p></div><button type="button" aria-label="Close dialog" :disabled="submitting" @click="emit('cancel')"><X :size="18"/></button></header><div class="bulk-edit-fields"><div v-for="(field,key,index) in fields" :key="key" class="bulk-edit-field"><label class="bulk-edit-apply"><input :ref="index===0 ? (element) => { firstApply=element as HTMLInputElement } : undefined" v-model="applies[key]" type="checkbox"/>Apply this change</label><div class="bulk-edit-control"><label :for="`bulk-${key}`">{{ field.label }}</label><select v-if="field.type==='select'" :id="`bulk-${key}`" v-model="values[key]" :disabled="!applies[key] || clears[key] || submitting"><option value="">Choose {{ field.label.toLowerCase() }}</option><option v-for="(label,value) in field.options" :key="value" :value="value">{{ label }}</option></select><input v-else :id="`bulk-${key}`" v-model="values[key]" :disabled="!applies[key] || clears[key] || submitting" :type="field.type"/><label v-if="field.nullable && applies[key]" class="bulk-edit-clear"><input v-model="clears[key]" type="checkbox"/>Clear this value</label><small v-if="errors[`changes.${key}`]" class="bulk-edit-error">{{ errors[`changes.${key}`] }}</small></div></div></div><small v-if="errors.changes" class="bulk-edit-error">{{ errors.changes }}</small><footer><button type="button" class="button button--outline" :disabled="submitting" @click="emit('cancel')">Cancel</button><button type="button" class="button button--primary bulk-apply" :disabled="submitting || !canApply()" @click="submit">{{ submitting ? 'Applying…' : 'Apply changes' }}</button></footer></section></div></template>
