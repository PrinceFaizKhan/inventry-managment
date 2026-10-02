<script setup lang="ts">
import { ChevronLeft, ChevronRight } from 'lucide-vue-next'
import { ref, watch } from 'vue'
const props = defineProps<{ currentPage: number; lastPage: number; perPage: number; pageSizes: number[]; total: number; from: number | null; to: number | null }>()
const emit = defineEmits<{ page: [value: number]; pageSize: [value: number]; invalidPage: [message: string] }>()
const pageInput = ref(String(props.currentPage))
watch(() => props.currentPage, (value) => { pageInput.value = String(value) })
function submitPage(): void { const page = Number(pageInput.value); if (!Number.isInteger(page) || page < 1 || page > props.lastPage) { emit('invalidPage', `Enter a whole number from 1 to ${props.lastPage}.`); pageInput.value = String(props.currentPage); return } emit('page', page) }
function updatePageSize(event: Event): void { emit('pageSize', Number((event.target as HTMLSelectElement).value)) }
</script>
<template><footer class="grid-pagination"><p v-if="total">Showing {{ from }}–{{ to }} of {{ total }}</p><p v-else>Showing 0 results</p><div class="grid-page-controls"><label>Per page <select :value="perPage" @change="updatePageSize"><option v-for="size in pageSizes" :key="size" :value="size">{{ size }}</option></select></label><button type="button" aria-label="Previous page" :disabled="currentPage <= 1 || total === 0" @click="emit('page', currentPage - 1)"><ChevronLeft :size="17"/></button><form @submit.prevent="submitPage"><input v-model="pageInput" aria-label="Page number" inputmode="numeric"/><span>/ {{ lastPage }}</span></form><button type="button" aria-label="Next page" :disabled="currentPage >= lastPage || total === 0" @click="emit('page', currentPage + 1)"><ChevronRight :size="17"/></button></div></footer></template>
