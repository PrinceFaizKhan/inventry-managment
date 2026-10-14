<script setup lang="ts">
import { computed } from 'vue'
import { getExtensionComponents } from '../../lib/extensionSlots'

const props = defineProps<{
  name: string
  context?: Record<string, unknown>
}>()

const componentsRef = getExtensionComponents(props.name)
const components = computed(() => componentsRef.value)
</script>

<template>
  <template v-for="(component, index) in components" :key="index">
    <component :is="component" v-bind="context" />
  </template>
</template>
