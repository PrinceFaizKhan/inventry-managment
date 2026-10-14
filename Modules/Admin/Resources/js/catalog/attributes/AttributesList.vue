<script setup lang="ts">
import { ref } from 'vue'
import DataGrid from '@/components/grid/DataGrid.vue'
import AttributeCreateDialog from './components/AttributeCreateDialog.vue'
import { request } from '@/lib/http'
import { attributesGrid } from './attributesGrid'
import type { GridRow } from '@/types/grid'

const grid = ref<{ refresh: () => Promise<void> } | null>(null)
const createOpen = ref(false)
const notification = ref<string | null>(null)

function showNotification(msg: string): void {
  notification.value = msg
  setTimeout(() => {
    notification.value = null
  }, 3000)
}

function editAttribute(row: GridRow): void {
  window.location.href = `/catalog/attributes/${row.id}/edit`
}

function createdAttribute(id: number): void {
  createOpen.value = false
  window.location.href = `/catalog/attributes/${id}/edit`
}

async function deleteAttribute(row: GridRow): Promise<void> {
  try {
    await request(`/api/catalog/attributes/${row.id}`, { method: 'DELETE' })
    showNotification('Attribute deleted successfully.')
    await grid.value?.refresh()
  } catch (error) {
    showNotification((error as Error).message)
  }
}
</script>

<template>
  <div class="attributes-list-wrap">
    <div v-if="notification" class="notification">
      {{ notification }}
    </div>

    <DataGrid
      ref="grid"
      :config="attributesGrid"
      @notify="showNotification"
      @create="createOpen = true"
      @edit="editAttribute"
      @delete="deleteAttribute"
    >
      <template #cell-required="{ row }: { row: GridRow }">
        <span class="attribute-required-badge" :class="row.required ? 'is-required' : 'is-optional'">
          {{ row.required ? 'Yes' : 'No' }}
        </span>
      </template>

      <template #cell-is_filterable="{ row }: { row: GridRow }">
        <span class="attribute-required-badge" :class="row.is_filterable ? 'is-required' : 'is-optional'">
          {{ row.is_filterable ? 'Yes' : 'No' }}
        </span>
      </template>
    </DataGrid>

    <AttributeCreateDialog
      :open="createOpen"
      @close="createOpen = false"
      @created="createdAttribute"
      @notify="showNotification"
    />
  </div>
</template>
