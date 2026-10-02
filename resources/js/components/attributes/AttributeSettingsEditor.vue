<script setup lang="ts">
import { computed } from 'vue'
import SelectControl from '../ui/SelectControl.vue'
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

function selectOptions(field: AttributeSettingDefinition): { value: string; label: string }[] {
  return Object.entries(field.options || {}).map(([value, label]) => ({ value, label }))
}
</script>

<template>
  <div class="attribute-settings-fields">
    <div v-for="[key, field] in visibleFields" :key="key" class="attribute-setting">
      <label v-if="field.type !== 'boolean'" :for="`attribute-setting-${key}`">{{ field.label }}</label>

      <SelectControl
        v-if="field.type === 'select'"
        :id="`attribute-setting-${key}`"
        :model-value="fieldValue(key, field) as string"
        :options="selectOptions(field)"
        :placeholder="`Select ${field.label.toLowerCase()}`"
        :invalid="!!errors[`settings.${key}`]"
        @update:model-value="update(key, $event)"
      />

      <SelectControl
        v-else-if="field.type === 'radio'"
        :id="`attribute-setting-${key}`"
        mode="radio"
        :model-value="fieldValue(key, field) as string"
        :options="selectOptions(field)"
        :aria-label="field.label"
        @update:model-value="update(key, $event)"
      />

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

      <SelectControl
        v-else-if="field.type === 'multi_select'"
        :id="`attribute-setting-${key}`"
        mode="multiple"
        :model-value="fieldValue(key, field) as string[]"
        :options="selectOptions(field)"
        :placeholder="`Select ${field.label.toLowerCase()}`"
        :invalid="!!errors[`settings.${key}`]"
        searchable
        @update:model-value="update(key, $event)"
      />

      <small v-if="errors[`settings.${key}`]" class="attribute-error">{{ errors[`settings.${key}`] }}</small>
      <small v-if="key === 'rich_text' && Boolean(fieldValue(key, field))" class="attribute-help">Text length checks use the plain text content.</small>
    </div>
  </div>
</template>
