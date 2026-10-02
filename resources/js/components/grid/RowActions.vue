<script setup lang="ts">
import { Pencil, Trash2 } from 'lucide-vue-next'
import type { GridAction, GridRow } from '../../types/grid'
defineProps<{ row: GridRow; actions: GridAction[]; allowed: Record<string, boolean> }>()
const emit = defineEmits<{ action: [action: GridAction, row: GridRow] }>()
</script>
<template><div class="grid-row-actions"><button v-for="action in actions.filter((item) => item.key !== 'create' && allowed[item.key])" :key="action.key" type="button" :aria-label="`${action.label} row`" @click="emit('action', action, row)"><Pencil v-if="action.icon === 'pencil'" :size="16"/><Trash2 v-else-if="action.icon === 'trash'" :size="16"/></button><span v-if="!actions.some((item) => item.key !== 'create' && allowed[item.key])">—</span></div></template>
