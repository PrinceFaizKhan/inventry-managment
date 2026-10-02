<script setup>
import { onMounted, ref } from 'vue'
import { Check, ShieldCheck } from 'lucide-vue-next'
import BaseButton from '../components/ui/BaseButton.vue'
import SelectControl from '../components/ui/SelectControl.vue'
import PermissionTree from '../components/PermissionTree.vue'
import { request } from '../lib/http'
const emit = defineEmits(['notify'])
const name = ref(''), access = ref('administration'), permissions = ref([]), nodes = ref([]), saving = ref(false)
const accessOptions = [{ value: 'administration', label: 'Administration' }, { value: 'custom', label: 'Custom' }]
onMounted(async () => { try { const data = await request('/api/permissions'); nodes.value = data.data } catch (error) { emit('notify', error.message) } })
async function save() { saving.value = true; try { await request('/api/roles', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ name: name.value, access: access.value, permissions: permissions.value }) }); emit('notify', 'Role saved successfully.'); name.value = ''; permissions.value = [] } catch (error) { emit('notify', error.message) } finally { saving.value = false } }
</script>
<template><div class="role-page"><div class="role-title"><div><h1>Create role</h1><p>Choose what users assigned to this role can access.</p></div><BaseButton type="button" @click="save"><Check :size="17"/>{{saving?'Saving…':'Save role'}}</BaseButton></div><form class="role-panel" @submit.prevent="save"><h2>Role details</h2><div class="role-fields"><label class="field"><span>Role name <b>*</b></span><input v-model="name" required placeholder="e.g. Store manager"/></label><label class="field"><span>Permission access</span><SelectControl :model-value="access" :options="accessOptions" aria-label="Permission access" @update:model-value="access=String($event || 'administration')"/></label></div><div v-if="access==='administration'" class="all-access"><ShieldCheck :size="21"/><div><h3>All permissions granted</h3><p>Full access to every registered permission, including Users and Roles. Future registered permissions are included automatically.</p></div></div><PermissionTree v-else v-model="permissions" :nodes="nodes"/></form></div></template>
