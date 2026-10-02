<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { ChevronLeft, ChevronRight } from 'lucide-vue-next'
import SelectControl from '../ui/SelectControl.vue'

const props = defineProps<{ currentPage: number; lastPage: number; perPage: number; pageSizes: number[]; total: number; from: number | null; to: number | null }>()
const emit = defineEmits<{ page: [value: number]; pageSize: [value: number]; invalidPage: [message: string] }>()
const pageInput = ref(String(props.currentPage))
const pageSizeOptions = computed(() => props.pageSizes.map((size) => ({ value: size, label: String(size) })))

watch(() => props.currentPage, (value) => { pageInput.value = String(value) })

function submitPage(): void {
  const page = Number(pageInput.value)
  if (!Number.isInteger(page) || page < 1 || page > props.lastPage) {
    emit('invalidPage', `Enter a whole number from 1 to ${props.lastPage}.`)
    pageInput.value = String(props.currentPage)
    return
  }

  emit('page', page)
}
</script>

<template>
  <footer class="grid-pagination">
    <p v-if="total">Showing {{ from }}–{{ to }} of {{ total }}</p>
    <p v-else>Showing 0 results</p>
    <div class="grid-page-controls">
      <div class="grid-page-size"><span>Per page</span><SelectControl :model-value="perPage" :options="pageSizeOptions" aria-label="Rows per page" @update:model-value="emit('pageSize',Number($event))"/></div>
      <div class="grid-page-navigation">
        <button type="button" aria-label="Previous page" :disabled="currentPage <= 1 || total === 0" @click="emit('page', currentPage - 1)"><ChevronLeft :size="17"/></button>
        <form @submit.prevent="submitPage"><input v-model="pageInput" aria-label="Page number" inputmode="numeric" autocomplete="off"/><span :aria-label="`of ${lastPage} pages`">/ {{ lastPage }}</span></form>
        <button type="button" aria-label="Next page" :disabled="currentPage >= lastPage || total === 0" @click="emit('page', currentPage + 1)"><ChevronRight :size="17"/></button>
      </div>
    </div>
  </footer>
</template>
