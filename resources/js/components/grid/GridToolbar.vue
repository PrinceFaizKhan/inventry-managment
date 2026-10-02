<script setup lang="ts">
import { ref } from 'vue'
import { Search, SlidersHorizontal } from 'lucide-vue-next'
defineProps<{ search: string; placeholder: string; filterCount: number; showFilters: boolean; total: number }>()
const emit = defineEmits<{ search: [value: string]; toggleFilters: [] }>()
const filterButton = ref<HTMLButtonElement | null>(null)
function updateSearch(event: Event): void { emit('search', (event.target as HTMLInputElement).value) }
function focusFilterButton(): void { filterButton.value?.focus() }
defineExpose({ focusFilterButton })
</script>
<template><div class="grid-toolbar"><label class="grid-search"><Search :size="17"/><input :value="search" :placeholder="placeholder" @input="updateSearch"/></label><button ref="filterButton" class="grid-filter-button" :class="{ active: showFilters }" type="button" :aria-expanded="showFilters" aria-haspopup="dialog" @click="emit('toggleFilters')"><SlidersHorizontal :size="16"/>Filter <span v-if="filterCount">{{ filterCount }}</span></button><span class="grid-result-count">{{ total }} {{ total === 1 ? 'result' : 'results' }}</span></div></template>
