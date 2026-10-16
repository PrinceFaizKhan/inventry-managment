<script setup lang="ts">
import { ref } from 'vue'
import {
  LayoutDashboard,
  Package,
  SlidersHorizontal,
  Tags,
  Boxes,
  ClipboardList,
  Settings,
  Users,
  ShieldCheck,
  ChevronDown,
  Circle,
} from 'lucide-vue-next'

export interface MenuItem {
  key: string
  label: string
  icon?: string
  route?: string
  coming_soon?: boolean
  enabled?: boolean
  children?: MenuItem[]
}

const props = defineProps<{
  items: MenuItem[]
  currentPath: string
}>()

const iconMap: Record<string, any> = {
  'layout-dashboard': LayoutDashboard,
  'package': Package,
  'sliders-horizontal': SlidersHorizontal,
  'tags': Tags,
  'boxes': Boxes,
  'clipboard-list': ClipboardList,
  'settings': Settings,
  'users': Users,
  'shield-check': ShieldCheck,
}

// Track accordion toggle state for parent items
const openGroups = ref<Record<string, boolean>>({})

function normalize(path: string): string {
  return (path || '').replace(/^\/+|\/+$/g, '')
}

function isItemActive(key: string, route?: string): boolean {
  const current = normalize(props.currentPath)
  const target = normalize(route || '')

  if (key === 'dashboard') {
    return current === 'dashboard' || current === 'admin/dashboard'
  }
  if (key === 'catalog' || key === 'catalog.attributes') {
    return current.startsWith('catalog/attributes') || current.startsWith('attributes')
  }
  if (key === 'catalog.product-types') {
    return current.startsWith('catalog/product-types')
  }
  if (key === 'catalog.products') {
    return current.startsWith('catalog/products')
  }
  if (key === 'settings.users') {
    return current.startsWith('settings/users')
  }
  if (key === 'settings.roles') {
    return current.startsWith('settings/roles')
  }
  if (target !== '') {
    return current.startsWith(target)
  }
  return false
}

function isGroupActive(item: MenuItem): boolean {
  if (isItemActive(item.key, item.route)) {
    return true
  }
  if (item.children && item.children.length > 0) {
    return item.children.some(child => isItemActive(child.key, child.route))
  }
  return false
}

// Initialize: groups with active children or groups that are active start expanded
props.items.forEach(item => {
  if (item.children && item.children.length > 0) {
    openGroups.value[item.key] = isGroupActive(item)
  }
})

function toggleGroup(key: string): void {
  openGroups.value[key] = !openGroups.value[key]
}

function getUrl(route?: string): string {
  if (!route) return '#'
  return '/' + normalize(route)
}
</script>

<template>
  <nav class="sidebar-nav-container">
    <template v-for="item in items" :key="item.key">
      <!-- Top-Level Item Without Children -->
      <template v-if="!item.children || item.children.length === 0">
        <a
          v-if="item.coming_soon"
          href="javascript:void(0)"
          class="nav-item disabled"
          title="Coming Soon"
        >
          <component :is="iconMap[item.icon || ''] || Circle" :size="18" class="flex-shrink-0" />
          <span>{{ item.label }}</span>
          <small class="badge badge-subtle">Soon</small>
        </a>

        <a
          v-else
          :href="getUrl(item.route)"
          class="nav-item"
          :class="{ active: isItemActive(item.key, item.route) }"
        >
          <component :is="iconMap[item.icon || ''] || Circle" :size="18" class="flex-shrink-0" />
          <span>{{ item.label }}</span>
        </a>
      </template>

      <!-- Parent Item With Submenus (Accordion) -->
      <div v-else class="nav-group">
        <button
          type="button"
          class="nav-parent-btn"
          :class="{
            'is-active': isGroupActive(item),
            'is-open': openGroups[item.key],
          }"
          :aria-expanded="openGroups[item.key] ? 'true' : 'false'"
          @click="toggleGroup(item.key)"
        >
          <component :is="iconMap[item.icon || ''] || Circle" :size="18" class="flex-shrink-0" />
          <span>{{ item.label }}</span>
          <ChevronDown
            :size="14"
            class="chevron"
          />
        </button>

        <transition
          enter-active-class="transition-all duration-200 ease-out overflow-hidden"
          enter-from-class="max-h-0 opacity-0"
          enter-to-class="max-h-96 opacity-100"
          leave-active-class="transition-all duration-150 ease-in overflow-hidden"
          leave-from-class="max-h-96 opacity-100"
          leave-to-class="max-h-0 opacity-0"
        >
          <div
            v-show="openGroups[item.key]"
            class="nav-children"
          >
            <template v-for="child in item.children" :key="child.key">
              <a
                v-if="child.coming_soon"
                href="javascript:void(0)"
                class="nav-sub-item disabled"
                title="Coming Soon"
              >
                <span>{{ child.label }}</span>
                <small class="badge badge-subtle">Soon</small>
              </a>

              <a
                v-else
                :href="getUrl(child.route)"
                class="nav-sub-item"
                :class="{ active: isItemActive(child.key, child.route) }"
              >
                <span>{{ child.label }}</span>
              </a>
            </template>
          </div>
        </transition>
      </div>
    </template>
  </nav>
</template>
