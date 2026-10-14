<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { ArrowLeft, Check } from 'lucide-vue-next'
import AttributeInput from './components/AttributeInput.vue'
import AttributeOptionsEditor from './components/AttributeOptionsEditor.vue'
import AttributeSettingsEditor from './components/AttributeSettingsEditor.vue'
import BaseButton from '@/components/ui/BaseButton.vue'
import ExtensionSlot from '@/components/ui/ExtensionSlot.vue'
import { request } from '@/lib/http'
import { t } from '@/lib/i18n'
import type { AttributeData, AttributeOptionData, AttributeTypeDefinition } from './types/attributes'

const props = withDefaults(
  defineProps<{
    initialAttribute?: AttributeData | null
    initialTypes?: Record<string, AttributeTypeDefinition>
    attributeId?: number | string
    backUrl?: string
    csrfToken?: string
  }>(),
  {
    initialAttribute: null,
    initialTypes: () => ({}),
    attributeId: undefined,
    backUrl: '/catalog/attributes',
    csrfToken: '',
  }
)

const emit = defineEmits<{ notify: [message: string] }>()

const attribute = ref<AttributeData | null>(props.initialAttribute)
const types = ref<Record<string, AttributeTypeDefinition>>(props.initialTypes || {})
const loading = ref(!props.initialAttribute)
const saving = ref(false)
const saved = ref(false)
const errors = ref<Record<string, string>>({})

const name = ref(props.initialAttribute?.name || '')
const required = ref(Boolean(props.initialAttribute?.required))
const isFilterable = ref(Boolean(props.initialAttribute?.is_filterable))
const settings = ref<Record<string, unknown>>({ ...(props.initialAttribute?.settings || {}) })
const options = ref<AttributeOptionData[]>([...(props.initialAttribute?.options || [])])

const currentType = computed(() => (attribute.value ? types.value[attribute.value.type] : undefined))
const configurableOptions = computed(() =>
  ['select', 'multiselect', 'radio', 'checkbox'].includes(attribute.value?.type || '')
)

onMounted(async () => {
  if (!attribute.value && props.attributeId) {
    await fetchAttributeData(props.attributeId)
  }
})

async function fetchAttributeData(id: string | number): Promise<void> {
  loading.value = true
  try {
    const res = await request(`/api/catalog/attributes/${id}`)
    attribute.value = res.data
    types.value = res.types
    name.value = res.data.name
    required.value = Boolean(res.data.required)
    isFilterable.value = Boolean(res.data.is_filterable)
    settings.value = { ...res.data.settings }
    options.value = res.data.options.map((opt: AttributeOptionData) => ({ ...opt }))
  } catch (e) {
    emit('notify', (e as Error).message)
  } finally {
    loading.value = false
  }
}

async function save(): Promise<void> {
  if (!attribute.value || saving.value) return
  saving.value = true
  saved.value = false
  errors.value = {}

  try {
    await request(`/api/catalog/attributes/${attribute.value.id}`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        ...(props.csrfToken ? { 'X-CSRF-TOKEN': props.csrfToken } : {}),
      },
      body: JSON.stringify({
        name: name.value,
        required: required.value,
        is_filterable: isFilterable.value,
        settings: settings.value,
        ...(configurableOptions.value
          ? { options: options.value.map((opt, sort_order) => ({ ...opt, sort_order })) }
          : {}),
      }),
    })
    saved.value = true
    emit('notify', t('catalog.attributes.saved') || 'Attribute saved successfully.')
  } catch (caught) {
    const error = caught as Error & { errors?: Record<string, string[]> }
    errors.value = Object.fromEntries(
      Object.entries(error.errors || {}).map(([key, msgs]) => [key, msgs[0]])
    )
    emit('notify', error.message)
  } finally {
    saving.value = false
  }
}
</script>

<template>
  <div class="attribute-edit-form-wrap">
    <div v-if="loading" class="attribute-loading">
      {{ t('common.loading') }}
    </div>

    <template v-else-if="attribute && currentType">
      <div class="attribute-edit-heading">
        <div>
          <p class="eyebrow">{{ t('nav.catalog') }} / {{ t('attributes.title') }}</p>
          <h1>{{ name || attribute.name }}</h1>
        </div>
        <div class="attribute-edit-actions">
          <a :href="backUrl" class="button button--outline flex items-center gap-1.5">
            <ArrowLeft :size="16" />
            <span>{{ t('action.back') }}</span>
          </a>
          <BaseButton :disabled="saving" @click="save">
            <Check :size="16" />
            <span>{{ saving ? t('action.saving') : saved ? t('action.saved') : t('action.save') }}</span>
          </BaseButton>
        </div>
      </div>

      <ExtensionSlot name="catalog.attribute.edit.form.before" :context="{ attribute, name, settings }" />

      <form class="attribute-edit-layout" @submit.prevent="save">
        <div class="attribute-edit-main">
          <section class="attribute-panel">
            <h2>{{ t('attributes.general_settings') }}</h2>
            <div class="attribute-general-grid">
              <label class="attribute-form-field">
                <span>{{ t('attributes.name') }} <b>*</b></span>
                <input v-model="name" maxlength="120" required />
                <small v-if="errors.name" class="attribute-error">{{ errors.name }}</small>
              </label>

              <label class="attribute-form-field">
                <span>{{ t('attributes.code') }}</span>
                <input :value="attribute.code" readonly />
                <small class="attribute-help">Unique identifier. Cannot be changed.</small>
              </label>

              <label class="attribute-form-field attribute-type-field">
                <span>{{ t('attributes.type') }}</span>
                <input :value="currentType.label" readonly />
              </label>
            </div>
          </section>

          <section v-if="configurableOptions" class="attribute-panel attribute-options-panel">
            <h2>{{ t('attributes.options') }}</h2>
            <p class="attribute-panel-copy">{{ t('attributes.options_desc') }}</p>
            <AttributeOptionsEditor
              :options="options"
              :errors="errors"
              @update:options="options = $event"
            />
          </section>
        </div>

        <div class="attribute-edit-side">
          <ExtensionSlot name="catalog.attribute.edit.sidebar.before" :context="{ attribute, name, settings }" />

          <section class="attribute-panel attribute-live-preview">
            <h2>{{ t('attributes.live_preview') }}</h2>
            <p class="attribute-panel-copy">{{ t('attributes.live_preview_desc') }}</p>
            <AttributeInput
              :name="name"
              :code="attribute.code"
              :type="attribute.type"
              :required="required"
              :settings="settings"
              :options="options"
            />
          </section>

          <section class="attribute-panel attribute-required-panel">
            <label class="attribute-required-toggle">
              <span>
                <b>{{ t('attributes.required') }}</b>
                <small>{{ t('attributes.required_desc') }}</small>
              </span>
              <input v-model="required" type="checkbox" role="switch" :aria-checked="required" />
              <span class="attribute-switch-track" aria-hidden="true"><span /></span>
              <span class="attribute-switch-state">{{ required ? t('common.on') : t('common.off') }}</span>
            </label>
          </section>

          <!-- Filterable Setting -->
          <section class="attribute-panel attribute-required-panel">
            <label class="attribute-required-toggle">
              <span>
                <b>{{ t('attributes.filterable_title') }}</b>
                <small>{{ t('attributes.filterable_desc') }}</small>
              </span>
              <input v-model="isFilterable" type="checkbox" role="switch" :aria-checked="isFilterable" />
              <span class="attribute-switch-track" aria-hidden="true"><span /></span>
              <span class="attribute-switch-state">{{ isFilterable ? t('common.on') : t('common.off') }}</span>
            </label>
          </section>

          <section v-if="Object.keys(currentType.settings).length" class="attribute-panel">
            <h2>{{ t('attributes.validation_settings') }}</h2>
            <AttributeSettingsEditor
              :type="attribute.type"
              :fields="currentType.settings"
              :settings="settings"
              :errors="errors"
              @update:settings="settings = $event"
            />
            <p v-if="errors.settings" class="attribute-error">{{ errors.settings }}</p>
          </section>

          <ExtensionSlot name="catalog.attribute.edit.sidebar.after" :context="{ attribute, name, settings }" />
        </div>
      </form>

      <ExtensionSlot name="catalog.attribute.edit.form.after" :context="{ attribute, name, settings }" />
    </template>
  </div>
</template>
