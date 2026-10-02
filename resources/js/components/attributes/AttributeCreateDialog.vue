<script setup lang="ts">
import { computed, nextTick, onMounted, ref } from 'vue'
import { X } from 'lucide-vue-next'
import { request } from '../../lib/http'
import { isValidAttributeCode, normalizeAttributeCode } from '../../modules/catalog/attributeCode'
import SelectControl from '../ui/SelectControl.vue'

const emit = defineEmits<{ close: []; created: [id: number]; notify: [message: string] }>()
const name = ref('')
const type = ref('')
const types = ref<Record<string, string>>({})
const loadingTypes = ref(true)
const saving = ref(false)
const errors = ref<Record<string, string>>({})
const nameInput = ref<HTMLInputElement | null>(null)
const code = computed(() => normalizeAttributeCode(name.value))
const typeOptions = computed(() => Object.entries(types.value).map(([value, label]) => ({ value, label })))
const codeError = computed(() => name.value.trim() !== '' && !isValidAttributeCode(code.value)
  ? 'This name does not produce a valid attribute code.'
  : errors.value.code)

onMounted(async () => {
  try {
    const response = await request('/api/catalog/attribute-types')
    types.value = response.data
  } catch (error) {
    emit('notify', (error as Error).message)
  } finally {
    loadingTypes.value = false
    await nextTick()
    nameInput.value?.focus()
  }
})

async function createAttribute(): Promise<void> {
  if (saving.value || !name.value.trim() || !type.value || !isValidAttributeCode(code.value)) {
    return
  }

  saving.value = true
  errors.value = {}
  try {
    const response = await request('/api/catalog/attributes', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ name: name.value, type: type.value }),
    })
    emit('created', response.data.id)
  } catch (caught) {
    const error = caught as Error & { errors?: Record<string, string[]> }
    errors.value = Object.fromEntries(Object.entries(error.errors || {}).map(([key, messages]) => [key, messages[0]]))
  } finally {
    saving.value = false
  }
}

function handleKeydown(event: KeyboardEvent): void {
  if (event.key === 'Escape' && !saving.value) {
    emit('close')
  }
}
</script>

<template>
  <div class="attribute-modal-backdrop" @click.self="!saving && emit('close')">
    <section class="attribute-create-modal" role="dialog" aria-modal="true" aria-labelledby="attribute-create-title" @keydown="handleKeydown">
      <header class="attribute-modal__header">
        <h2 id="attribute-create-title">Create attribute</h2>
        <button type="button" class="attribute-modal__close" aria-label="Close" :disabled="saving" @click="emit('close')"><X :size="18"/></button>
      </header>

      <form class="attribute-create-form" @submit.prevent="createAttribute">
        <label class="attribute-form-field">
          <span>Name <b>*</b></span>
          <input ref="nameInput" v-model="name" required maxlength="120" placeholder="Name" :aria-invalid="!!errors.name" @input="errors = {}"/>
          <small v-if="errors.name" class="attribute-error">{{ errors.name }}</small>
        </label>

        <label class="attribute-form-field">
          <span>Type <b>*</b></span>
          <SelectControl
            :model-value="type"
            :options="typeOptions"
            placeholder="Select type"
            :disabled="loadingTypes"
            :invalid="!!errors.type"
            aria-label="Attribute type"
            @update:model-value="type=String($event || '')"
          />
          <small v-if="errors.type" class="attribute-error">{{ errors.type }}</small>
        </label>

        <label class="attribute-form-field">
          <span>Code</span>
          <input :value="code" readonly placeholder="Code" :aria-invalid="!!codeError"/>
          <small v-if="codeError" class="attribute-error">{{ codeError }}</small>
        </label>

        <footer class="attribute-modal__footer">
          <button type="button" class="button button--outline" :disabled="saving" @click="emit('close')">Cancel</button>
          <button type="submit" class="button button--primary" :disabled="saving || loadingTypes || !type">
            {{ saving ? 'Creating…' : 'Create attribute' }}
          </button>
        </footer>
      </form>
    </section>
  </div>
</template>
