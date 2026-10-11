---
name: vue-development
description: "Develops Vue 3 Single File Components (SFCs) and client-side logic for the Laravel application. Activates when creating or editing Vue components, pages, composables, or router configuration; working with reactive state (ref, reactive, computed, watch), props, emits, or lifecycle hooks; integrating with Lucide icons (lucide-vue-next), Tailwind CSS classes in templates, or API calls via resources/js/lib/http.js."
license: MIT
metadata:
  author: project
---

# Vue 3 Development Guidelines

## Project Context & Architecture

This application uses **Vue 3** as a Single Page Application (SPA) alongside Laravel, bundled via **Vite** and **Tailwind CSS v4**:
- Vue 3 with Composition API and `<script setup>`
- Routing with **Vue Router 4** (`resources/js/app.js` and `resources/js/pages/`)
- Types in `resources/js/types/` and composables in `resources/js/composables/`
- Component library in `resources/js/components/ui/`
- Icons from `lucide-vue-next`
- HTTP communication via `resources/js/lib/http.js`

## Component Conventions

### 1. Structure & `<script setup>`
- Always use `<script setup>` for Vue Single File Components (`.vue`).
- Keep component imports organized: Vue core (`ref`, `computed`, `watch`), router, third-party libraries (e.g. `lucide-vue-next`), local components, and local utilities/composables.
- Use explicit `defineProps` and `defineEmits`.

```vue
<script setup lang="ts">
import { computed, ref } from 'vue'
import { Check, AlertCircle } from 'lucide-vue-next'
import BaseButton from '@/components/ui/BaseButton.vue'

interface Props {
    title: string
    disabled?: boolean
}

const props = withDefaults(defineProps<Props>(), {
    disabled: false,
})

const emit = defineEmits<{
    (e: 'save', payload: { title: string }): void
}>()

const isSaving = ref(false)

function handleSave() {
    emit('save', { title: props.title })
}
</script>

<template>
    <div class="flex items-center justify-between p-4 bg-white dark:bg-gray-800 rounded-lg shadow-sm">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ title }}</h2>
        <BaseButton :disabled="disabled || isSaving" @click="handleSave">
            <Check class="w-4 h-4 mr-1.5" />
            <span>Save</span>
        </BaseButton>
    </div>
</template>
```

### 2. State Management & Reactivity
- Prefer `ref()` for primitive and object state. Reserve `reactive()` for strictly grouping form or filter states where destructuring is not needed.
- Use `computed()` for derived state rather than updating reactive properties manually inside watchers or methods.
- Clean up side effects in `onBeforeUnmount()` or `onUnmounted()` if timers, event listeners, or observers are registered.

### 3. API Communication
- Always use the shared HTTP client `request` from `resources/js/lib/http.js`:

```javascript
import { request } from '../lib/http'

async function fetchData() {
    try {
        const response = await request('/api/resource')
        items.value = response.data
    } catch (error) {
        emit('notify', error.message)
    }
}
```

### 4. Reusable UI Components
Before writing custom buttons, form inputs, or dropdowns, check and reuse existing components in `resources/js/components/ui/`:
- `BaseButton.vue`: Standard button with variant styling
- `FormField.vue`: Label, description, and error wrappers
- `SelectControl.vue`: Form select dropdowns
- `AppDropdown.vue`: Floating action menus
- `Notifications.vue`: Toast / banner notification display

### 5. Routing
- Page components reside in `resources/js/pages/`.
- Routes are registered in `resources/js/app.js` with metadata (`title`, `breadcrumbs`, `activeMenu`).
- Use `useRouter()` and `useRoute()` from `vue-router` inside components.

### 6. Verification
After making changes to Vue components or TypeScript files:
1. Run `npm run typecheck` (`vue-tsc --noEmit`) to verify type safety.
2. Run `npm run build` (`vite build`) to verify that templates and assets compile cleanly.
