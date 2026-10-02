<script setup lang="ts">
import { ArrowDown, ArrowUp, Plus, Trash2 } from 'lucide-vue-next'
import type { AttributeOptionData } from '../../types/attributes'

const props = defineProps<{ options: AttributeOptionData[]; errors: Record<string, string> }>()
const emit = defineEmits<{ 'update:options': [value: AttributeOptionData[]] }>()

function update(index: number, key: 'code' | 'label', value: string): void {
  const options = props.options.map((option, optionIndex) => optionIndex === index ? { ...option, [key]: value } : option)
  emit('update:options', options)
}

function add(): void {
  emit('update:options', [...props.options, { code: '', label: '', sort_order: props.options.length }])
}

function remove(index: number): void {
  emit('update:options', props.options.filter((_, optionIndex) => optionIndex !== index).map((option, sort_order) => ({ ...option, sort_order })))
}

function move(index: number, offset: -1 | 1): void {
  const target = index + offset
  if (target < 0 || target >= props.options.length) return
  const options = [...props.options]
  ;[options[index], options[target]] = [options[target], options[index]]
  emit('update:options', options.map((option, sort_order) => ({ ...option, sort_order })))
}
</script>

<template>
  <div class="attribute-options-editor">
    <div v-for="(option, index) in options" :key="option.id ?? `new-${index}`" class="attribute-option-row">
      <div class="attribute-option-fields">
        <label>
          <span>Option label</span>
          <input :value="option.label" maxlength="120" placeholder="Option label" @input="update(index, 'label', ($event.target as HTMLInputElement).value)"/>
        </label>
        <label>
          <span>Option code</span>
          <input :value="option.code" maxlength="120" placeholder="option_code" :readonly="!!option.id" @input="update(index, 'code', ($event.target as HTMLInputElement).value)"/>
        </label>
      </div>
      <div class="attribute-option-actions">
        <button type="button" :disabled="index===0" :aria-label="`Move ${option.label || 'option'} up`" @click="move(index,-1)"><ArrowUp :size="15"/></button>
        <button type="button" :disabled="index===options.length-1" :aria-label="`Move ${option.label || 'option'} down`" @click="move(index,1)"><ArrowDown :size="15"/></button>
        <button type="button" class="attribute-option-remove" :aria-label="`Remove ${option.label || 'option'}`" @click="remove(index)"><Trash2 :size="15"/></button>
      </div>
      <small v-if="errors[`options.${index}.code`]" class="attribute-error">{{ errors[`options.${index}.code`] }}</small>
      <small v-if="errors[`options.${index}.label`]" class="attribute-error">{{ errors[`options.${index}.label`] }}</small>
    </div>
    <small v-if="errors.options" class="attribute-error">{{ errors.options }}</small>
    <button type="button" class="attribute-add-option" @click="add"><Plus :size="15"/>Add option</button>
  </div>
</template>
