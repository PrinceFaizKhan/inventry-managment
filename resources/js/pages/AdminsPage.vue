<script setup lang="ts">
import DataGrid from '../components/grid/DataGrid.vue'
import { usersGrid } from '../modules/settings/usersGrid'
import type { GridRow } from '../types/grid'
const emit = defineEmits<{ notify: [message: string] }>()
function initials(name: unknown): string { return String(name).split(' ').filter(Boolean).map((part) => part[0]).slice(0, 2).join('').toUpperCase() }
function unavailable(): void { emit('notify', 'User create, edit, and delete flows are not registered yet.') }
</script>
<template><section class="users-page"><DataGrid :config="usersGrid" @notify="emit('notify',$event)" @create="unavailable" @edit="unavailable" @delete="unavailable"><template #title><div class="attributes-page__heading"><div><p class="eyebrow">Settings</p><h1>Users</h1><p class="page-copy">Manage people with access to your Haseen Telecom workspace.</p></div></div></template><template #cell-user="{ row }: { row: GridRow }"><div class="grid-user"><span class="avatar">{{ initials(row.name) }}</span><span><b>{{ row.name }}</b><small>{{ row.email }}</small></span></div></template><template #cell-role="{ row }: { row: GridRow }"><span>{{ row.role || 'Unassigned' }}</span></template><template #cell-status="{ row }: { row: GridRow }"><span class="status" :class="`status--${row.status}`">{{ row.status }}</span></template></DataGrid><p class="grid-limitation">User create, edit, and delete flows will appear once their authorized backend endpoints are registered.</p></section></template>
