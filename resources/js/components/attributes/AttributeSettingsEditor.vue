<script setup lang="ts">
import { computed } from 'vue'
import type { AttributeSettingDefinition } from '../../types/attributes'

const props = defineProps<{
  type: string
  fields: Record<string, AttributeSettingDefinition>
  settings: Record<string, unknown>
  errors: Record<string, string>
}>()
const emit = defineEmits<{ 'update:settings': [value: Record<string, unknown>] }>()

const visibleFields = computed(() => Object.entries(props.fields).filter(([, field]) => {
  if (!field.applies_to) return true
  const mode = String(props.settings.validation ?? 'any')
  return field.applies_to.includes(mode)
}))

function fieldValue(key: string, field: AttributeSettingDefinition): unknown {
  return props.settings[key] ?? field.default ?? (field.type === 'multi_select' ? [] : '')
}

function update(key: string, value: unknown): void {
  const next = { ...props.settings, [key]: value }
  if (key === 'validation') {
    Object.entries(props.fields).forEach(([setting, field]) => {
      if (field.applies_to && !field.applies_to.includes(String(value))) {
        delete next[setting]
      }
    })
  }
  emit('update:settings', next)
}

function toggleMultiSelect(key: string, value: string, event: Event): void {
  const selected = new Set((fieldValue(key, props.fields[key]) as string[]) || [])
  if ((event.target as HTMLInputElement).checked) selected.add(value)
  else selected.delete(value)
  update(key, [...selected])
}
</script>

<template>
  <div class="attribute-settings-fields">
    <div v-for="[key, field] in visibleFields" :key="key" class="attribute-setting">
      <label v-if="field.type !== 'boolean' && field.type !== 'multi_select'" :for="`attribute-setting-${key}`">{{ field.label }}</label>

      <select
        v-if="field.type === 'select'"
        :id="`attribute-setting-${key}`"
        :value="fieldValue(key, field)"
        :aria-invalid="!!errors[`settings.${key}`]"
        @change="update(key, ($event.target as HTMLSelectElement).value)"
      >
        <option v-if="field.default === undefined && !fieldValue(key, field)" value="">Select {{ field.label.toLowerCase() }}</option>
        <option v-for="(label, option) in field.options || {}" :key="option" :value="option">{{ label }}</option>
      </select>

      <input
        v-else-if="field.type === 'integer' || field.type === 'decimal'"
        :id="`attribute-setting-${key}`"
        :value="fieldValue(key, field)"
        type="number"
        :step="field.type === 'decimal' ? 'any' : 1"
        :min="field.min"
        :max="field.max"
        :aria-invalid="!!errors[`settings.${key}`]"
        @input="update(key, ($event.target as HTMLInputElement).value)"
      />

      <label v-else-if="field.type === 'boolean'" class="attribute-setting-check">
        <input type="checkbox" :checked="Boolean(fieldValue(key, field))" @change="update(key, ($event.target as HTMLInputElement).checked)"/>
        <span>{{ field.label }}</span>
      </label>

      <fieldset v-else-if="field.type === 'multi_select'" class="attribute-setting-options" :aria-invalid="!!errors[`settings.${key}`]">
        <legend>{{ field.label }}</legend>
        <label v-for="(label, option) in field.options || {}" :key="option" class="attribute-setting-check">
          <input type="checkbox" :checked="((fieldValue(key, field) as string[]) || []).includes(option)" @change="toggleMultiSelect(key, option, $event)"/>
          <span>{{ label }}</span>
        </label>
      </fieldset>

      <small v-if="errors[`settings.${key}`]" class="attribute-error">{{ errors[`settings.${key}`] }}</small>
      <small v-if="key === 'rich_text' && Boolean(fieldValue(key, field))" class="attribute-help">Text length checks use the plain text content.</small>
    </div>
  </div>
</template>
