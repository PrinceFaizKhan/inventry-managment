<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { Check, ShieldCheck } from 'lucide-vue-next'
import BaseButton from '@/components/ui/BaseButton.vue'
import SelectControl from '@/components/ui/SelectControl.vue'
import PermissionTree from './components/PermissionTree.vue'
import { request } from '@/lib/http'
import { t } from '@/lib/i18n'

const emit = defineEmits<{ notify: [message: string] }>()

const name = ref('')
const access = ref<'administration' | 'custom'>('administration')
const permissions = ref<string[]>([])
const nodes = ref([])
const saving = ref(false)

const accessOptions = [
  { value: 'administration', label: 'Administration' },
  { value: 'custom', label: 'Custom' },
]

onMounted(async () => {
  try {
    const data = await request('/api/permissions')
    nodes.value = data.data
  } catch (error) {
    emit('notify', (error as Error).message)
  }
})

async function save(): Promise<void> {
  saving.value = true
  try {
    await request('/api/roles', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        name: name.value,
        access: access.value,
        permissions: permissions.value,
      }),
    })
    emit('notify', 'Role saved successfully.')
    name.value = ''
    permissions.value = []
  } catch (error) {
    emit('notify', (error as Error).message)
  } finally {
    saving.value = false
  }
}
</script>

<template>
  <div class="role-page">
    <div class="role-title">
      <div>
        <h1>{{ t('settings.roles.title') || 'Create role' }}</h1>
        <p>{{ t('settings.roles.description') || 'Choose what users assigned to this role can access.' }}</p>
      </div>
      <BaseButton type="button" @click="save">
        <Check :size="17" />
        {{ saving ? t('action.saving') || 'Saving…' : t('settings.roles.save') || 'Save role' }}
      </BaseButton>
    </div>

    <form class="role-panel" @submit.prevent="save">
      <h2>Role details</h2>
      <div class="role-fields">
        <label class="field">
          <span>Role name <b>*</b></span>
          <input v-model="name" required placeholder="e.g. Store manager" />
        </label>
        <label class="field">
          <span>Permission access</span>
          <SelectControl
            :model-value="access"
            :options="accessOptions"
            aria-label="Permission access"
            @update:model-value="access = ($event as any) || 'administration'"
          />
        </label>
      </div>

      <div v-if="access === 'administration'" class="all-access">
        <ShieldCheck :size="21" />
        <div>
          <h3>All permissions granted</h3>
          <p>Full access to every registered permission, including Users and Roles. Future registered permissions are included automatically.</p>
        </div>
      </div>
      <PermissionTree v-else v-model="permissions" :nodes="nodes" />
    </form>
  </div>
</template>
