<script setup lang="ts">
import { computed, onBeforeUnmount, ref } from 'vue'
import { Bold, Italic } from 'lucide-vue-next'
import SelectControl from '@/components/ui/SelectControl.vue'
import type { SelectControlValue } from '@/components/ui/SelectControl.vue'
import type { AttributeOptionData } from '../types/attributes'
import { hasCustomAttributeComponent, resolveAttributeComponent } from '../attributeComponents'

const props = defineProps<{
  name: string
  code: string
  type: string
  required: boolean
  settings: Record<string, unknown>
  options: AttributeOptionData[]
}>()

const textValue = ref('')
const selectedValues = ref<string[]>([])
const booleanValue = ref('')
const files = ref<Array<{ name: string; url: string }>>([])
const fileError = ref('')
const editor = ref<HTMLElement | null>(null)
const fileAccept = computed(() => ((props.settings.allowed_formats as string[]) || []).join(','))
const fileLimit = computed(() => Number(props.settings.max_file_size_kb || 5120) * 1024)
const maxImages = computed(() => Number(props.settings.max_images || 10))
const numberStep = computed(() => {
  const precision = Number(props.settings.decimal_precision ?? 2)
  return precision === 0 ? '1' : `0.${'0'.repeat(precision - 1)}1`
})
const plainTextLength = computed(() => textValue.value.length)
const attributeOptions = computed(() => props.options.map((option) => ({ value: option.code, label: option.label })))
const customComponent = computed(() => resolveAttributeComponent(props.type))
const isCustom = computed(() => hasCustomAttributeComponent(props.type))

onBeforeUnmount(() => releaseFiles())

function updateText(event: Event): void {
  textValue.value = (event.currentTarget as HTMLElement).textContent || ''
}

function formatEditor(command: 'bold' | 'italic'): void {
  editor.value?.focus()
  document.execCommand(command)
  updateText({ currentTarget: editor.value } as unknown as Event)
}

function setSingleOption(value: SelectControlValue): void {
  selectedValues.value = typeof value === 'string' || typeof value === 'number' ? [String(value)] : []
}

function setMultipleOptions(value: SelectControlValue): void {
  selectedValues.value = Array.isArray(value) ? value.map(String) : []
}

function setBooleanOption(value: SelectControlValue): void {
  booleanValue.value = typeof value === 'string' ? value : ''
}

function releaseFiles(): void {
  files.value.forEach((file) => URL.revokeObjectURL(file.url))
  files.value = []
}

function previewFiles(event: Event): void {
  releaseFiles()
  fileError.value = ''
  const inputFiles = Array.from((event.target as HTMLInputElement).files || [])
  const maximum = props.type === 'gallery' ? maxImages.value : 1
  if (inputFiles.length > maximum) {
    fileError.value = `Choose no more than ${maximum} ${maximum === 1 ? 'image' : 'images'}.`
    return
  }
  const invalid = inputFiles.find((file) => !((props.settings.allowed_formats as string[]) || []).includes(file.type) || file.size > fileLimit.value)
  if (invalid) {
    fileError.value = invalid.size > fileLimit.value ? 'This image exceeds the configured maximum file size.' : 'This image format is not allowed.'
    return
  }
  files.value = inputFiles.map((file) => ({ name: file.name, url: URL.createObjectURL(file) }))
}
</script>

<template>
  <div class="attribute-preview-field">
    <label class="attribute-preview-label" :for="`preview-${code}`">{{ name || 'Attribute name' }}<b v-if="required">*</b></label>

    <!-- Custom dynamic component registered by another module -->
    <component
      v-if="isCustom && customComponent"
      :is="customComponent"
      v-bind="props"
    />

    <input
      v-else-if="type === 'text'"
      :id="`preview-${code}`"
      :required="required"
      :minlength="settings.min_length as number | undefined"
      :maxlength="settings.max_length as number | undefined"
      :min="settings.min_value as string | undefined"
      :max="settings.max_value as string | undefined"
      :step="settings.validation === 'decimal' ? numberStep : settings.validation === 'numeric' ? '1' : undefined"
      :type="settings.validation === 'email' ? 'email' : settings.validation === 'url' ? 'url' : settings.validation === 'numeric' || settings.validation === 'decimal' ? 'number' : 'text'"
      :pattern="settings.validation === 'letters_only' ? '[A-Za-z ]+' : undefined"
      placeholder="Enter a value"
      @input="textValue=($event.target as HTMLInputElement).value"
    />

    <template v-else-if="type === 'textarea'">
      <div v-if="settings.rich_text" class="attribute-rich-preview">
        <div class="attribute-rich-toolbar">
          <button type="button" aria-label="Bold" @click="formatEditor('bold')"><Bold :size="15"/></button>
          <button type="button" aria-label="Italic" @click="formatEditor('italic')"><Italic :size="15"/></button>
        </div>
        <div :id="`preview-${code}`" ref="editor" class="attribute-rich-editor" contenteditable="true" role="textbox" aria-multiline="true" :aria-required="required" @input="updateText"/>
      </div>
      <textarea v-else :id="`preview-${code}`" v-model="textValue" :required="required" :minlength="settings.min_length as number | undefined" :maxlength="settings.max_length as number | undefined" placeholder="Enter a value"/>
      <small v-if="settings.rich_text && (settings.min_length || settings.max_length)" class="attribute-preview-help">Plain text length: {{ plainTextLength }}{{ settings.max_length ? ` / ${settings.max_length}` : '' }}</small>
    </template>

    <SelectControl
      v-else-if="type === 'select' || type === 'radio'"
      :mode="type === 'radio' ? 'radio' : 'single'"
      :id="`preview-${code}`"
      :model-value="selectedValues[0] || ''"
      :options="attributeOptions"
      placeholder="Select an option"
      :name="`preview-${code}`"
      :aria-label="name"
      :required="required"
      @update:model-value="setSingleOption"
    />

    <SelectControl
      v-else-if="type === 'checkbox' || type === 'multiselect'"
      :id="`preview-${code}`"
      mode="multiple"
      :model-value="selectedValues"
      :options="attributeOptions"
      placeholder="Select one or more options"
      :required="required"
      @update:model-value="setMultipleOptions"
    />

    <SelectControl
      v-else-if="type === 'boolean'"
      :id="`preview-${code}`"
      :model-value="booleanValue"
      :options="[{ value: 'yes', label: 'Yes' }, { value: 'no', label: 'No' }]"
      mode="radio"
      :name="`preview-${code}`"
      :aria-label="name"
      :required="required"
      @update:model-value="setBooleanOption"
    />

    <div v-else-if="type === 'money'" class="attribute-money-preview">
      <span>{{ settings.currency || 'Currency' }}</span>
      <input :id="`preview-${code}`" v-model="textValue" type="number" :step="numberStep" :min="settings.min_value as string | undefined" :max="settings.max_value as string | undefined" :required="required" placeholder="0.00"/>
    </div>

    <div v-else-if="type === 'image' || type === 'gallery'" class="attribute-file-preview">
      <input :id="`preview-${code}`" type="file" :accept="fileAccept" :multiple="type === 'gallery'" :required="required" @change="previewFiles"/>
      <small class="attribute-preview-help">{{ fileAccept ? fileAccept.split(',').map((format) => format.replace('image/','').toUpperCase()).join(', ') : 'Choose allowed formats in settings.' }} · Max {{ settings.max_file_size_kb || 5120 }} KB{{ type === 'gallery' ? ` · Up to ${maxImages} images` : '' }}</small>
      <small v-if="fileError" class="attribute-error">{{ fileError }}</small>
      <div v-if="files.length" class="attribute-file-previews">
        <figure v-for="file in files" :key="file.url"><img :src="file.url" :alt="file.name"/><figcaption>{{ file.name }}</figcaption></figure>
      </div>
    </div>

    <small v-if="required" class="attribute-preview-help">Required</small>
  </div>
</template>
