<script setup lang="ts">
import AttributeCreateDialog from './AttributeCreateDialog.vue'

defineProps<{
  isOpen?: boolean
  types?: Record<string, string>
}>()

defineEmits<{
  close: []
  created: [id: number]
  notify: [message: string]
}>()
</script>

<template>
  <AttributeCreateDialog
    :open="isOpen"
    :types="types"
    @close="$emit('close')"
    @created="$emit('created', $event)"
    @notify="$emit('notify', $event)"
  />
</template>
