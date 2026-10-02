<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import DataGrid from '../components/grid/DataGrid.vue'
import AttributeCreateDialog from '../components/attributes/AttributeCreateDialog.vue'
import { request } from '../lib/http'
import { attributesGrid } from '../modules/catalog/attributesGrid'
import type { GridRow } from '../types/grid'

const emit = defineEmits<{ notify: [message: string] }>()
const router = useRouter()
const grid = ref<{ refresh: () => Promise<void> } | null>(null)
const createOpen = ref(false)

function editAttribute(row: GridRow): void {
  void router.push({ name: 'catalog.attributes.edit', params: { id: row.id } })
}

function createdAttribute(id: number): void {
  createOpen.value = false
  void router.push({ name: 'catalog.attributes.edit', params: { id } })
}

async function deleteAttribute(row: GridRow): Promise<void> {
  try {
    await request(`/api/catalog/attributes/${row.id}`, { method: 'DELETE' })
    emit('notify', 'Attribute deleted.')
    await grid.value?.refresh()
  } catch (error) {
    emit('notify', (error as Error).message)
  }
}
</script>

<template>
  <section class="attributes-page">
    <DataGrid
      ref="grid"
      :config="attributesGrid"
      @notify="emit('notify', $event)"
      @create="createOpen = true"
      @edit="editAttribute"
      @delete="deleteAttribute"
    >
      <template #title>
        <div class="attributes-page__heading">
          <div>
            <p class="eyebrow">Catalog</p>
            <h1>Attributes</h1>
            <p class="page-copy">Define reusable fields for your catalog.</p>
          </div>
        </div>
      </template>
      <template #cell-required="{ row }: { row: GridRow }">
        <span class="attribute-required-badge" :class="row.required ? 'is-required' : 'is-optional'">{{ row.required ? 'Yes' : 'No' }}</span>
      </template>
    </DataGrid>

    <AttributeCreateDialog v-if="createOpen" @close="createOpen = false" @created="createdAttribute" @notify="emit('notify', $event)"/>
  </section>
</template>
