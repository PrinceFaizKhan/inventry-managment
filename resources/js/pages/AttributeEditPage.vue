<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { ArrowLeft, Check } from 'lucide-vue-next'
import { useRoute, useRouter } from 'vue-router'
import AttributeInput from '../components/attributes/AttributeInput.vue'
import AttributeOptionsEditor from '../components/attributes/AttributeOptionsEditor.vue'
import AttributeSettingsEditor from '../components/attributes/AttributeSettingsEditor.vue'
import BaseButton from '../components/ui/BaseButton.vue'
import { request } from '../lib/http'
import type { AttributeData, AttributeOptionData, AttributeTypeDefinition } from '../types/attributes'

const route = useRoute()
const router = useRouter()
const emit = defineEmits<{ notify: [message: string] }>()
const attribute = ref<AttributeData | null>(null)
const types = ref<Record<string, AttributeTypeDefinition>>({})
const loading = ref(true)
const saving = ref(false)
const errors = ref<Record<string, string>>({})
const name = ref('')
const required = ref(false)
const settings = ref<Record<string, unknown>>({})
const options = ref<AttributeOptionData[]>([])
const currentType = computed(() => attribute.value ? types.value[attribute.value.type] : undefined)
const configurableOptions = computed(() => ['select', 'multiselect', 'radio', 'checkbox'].includes(attribute.value?.type || ''))

onMounted(loadAttribute)

async function loadAttribute(): Promise<void> {
  loading.value = true
  try {
    const response = await request(`/api/catalog/attributes/${route.params.id}`)
    attribute.value = response.data
    types.value = response.types
    name.value = response.data.name
    required.value = response.data.required
    settings.value = { ...response.data.settings }
    options.value = response.data.options.map((option: AttributeOptionData) => ({ ...option }))
  } catch (error) {
    emit('notify', (error as Error).message)
    void router.replace({ name: 'catalog.attributes' })
  } finally {
    loading.value = false
  }
}

async function save(): Promise<void> {
  if (!attribute.value || saving.value) return
  saving.value = true
  errors.value = {}
  try {
    await request(`/api/catalog/attributes/${attribute.value.id}`, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        name: name.value,
        required: required.value,
        settings: settings.value,
        ...(configurableOptions.value ? { options: options.value.map((option, sort_order) => ({ ...option, sort_order })) } : {}),
      }),
    })
    emit('notify', 'Attribute saved.')
    await loadAttribute()
  } catch (caught) {
    const error = caught as Error & { errors?: Record<string, string[]> }
    errors.value = Object.fromEntries(Object.entries(error.errors || {}).map(([key, messages]) => [key, messages[0]]))
    emit('notify', error.message)
  } finally {
    saving.value = false
  }
}
</script>

<template>
  <section class="attribute-edit-page">
    <div v-if="loading" class="attribute-loading">Loading attribute…</div>
    <template v-else-if="attribute && currentType">
      <div class="attribute-edit-heading">
        <div>
          <p class="eyebrow">Catalog / Attributes</p>
          <h1>{{ name || attribute.name }}</h1>
        </div>
        <div class="attribute-edit-actions">
          <BaseButton variant="outline" @click="router.push({ name: 'catalog.attributes' })"><ArrowLeft :size="16"/>Back</BaseButton>
          <BaseButton :disabled="saving" @click="save"><Check :size="16"/>{{ saving ? 'Saving…' : 'Save' }}</BaseButton>
        </div>
      </div>

      <form class="attribute-edit-layout" @submit.prevent="save">
        <div class="attribute-edit-main">
          <section class="attribute-panel">
            <h2>General settings</h2>
            <div class="attribute-general-grid">
              <label class="attribute-form-field"><span>Name <b>*</b></span><input v-model="name" maxlength="120" required/><small v-if="errors.name" class="attribute-error">{{ errors.name }}</small></label>
              <label class="attribute-form-field"><span>Code</span><input :value="attribute.code" readonly/><small class="attribute-help">Unique, permanent identifier. It cannot be changed after creation.</small></label>
              <label class="attribute-form-field attribute-type-field"><span>Type</span><input :value="currentType.label" readonly/></label>
            </div>
          </section>
          <section v-if="configurableOptions" class="attribute-panel attribute-options-panel">
            <h2>Options</h2>
            <p class="attribute-panel-copy">Option codes stay stable after creation. Labels and order can be changed.</p>
            <AttributeOptionsEditor :options="options" :errors="errors" @update:options="options=$event"/>
          </section>
        </div>

        <div class="attribute-edit-side">
          <section class="attribute-panel attribute-live-preview">
            <h2>Live preview</h2>
            <p class="attribute-panel-copy">Preview values are temporary and will not be saved.</p>
            <AttributeInput :name="name" :code="attribute.code" :type="attribute.type" :required="required" :settings="settings" :options="options"/>
          </section>

          <section class="attribute-panel attribute-required-panel">
            <label class="attribute-required-toggle">
              <span><b>Required</b><small>Users must provide a value for this attribute.</small></span>
              <input v-model="required" type="checkbox" role="switch" :aria-checked="required"/>
              <span class="attribute-switch-track" aria-hidden="true"><span/></span>
              <span class="attribute-switch-state">{{ required ? 'On' : 'Off' }}</span>
            </label>
          </section>

          <section v-if="Object.keys(currentType.settings).length" class="attribute-panel">
            <h2>Validation and settings</h2>
            <p class="attribute-panel-copy">Configure only the options supported by {{ currentType.label }}.</p>
            <AttributeSettingsEditor :type="attribute.type" :fields="currentType.settings" :settings="settings" :errors="errors" @update:settings="settings=$event"/>
            <p v-if="errors.settings" class="attribute-error">{{ errors.settings }}</p>
          </section>

        </div>
      </form>
    </template>
  </section>
</template>
