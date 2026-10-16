<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount } from 'vue'
import {
  ChevronDown,
  User,
  Users,
  SlidersHorizontal,
  LogOut,
} from 'lucide-vue-next'

const props = withDefaults(
  defineProps<{
    user: {
      name: string
      email: string
      role?: string
    }
    accountUrl?: string
    usersUrl?: string
    attributesUrl?: string
    logoutUrl?: string
    csrfToken?: string
  }>(),
  {
    accountUrl: '/account',
    usersUrl: '/settings/users',
    attributesUrl: '/catalog/attributes',
    logoutUrl: '/logout',
    csrfToken: '',
  }
)

const isOpen = ref(false)
const dropdownRef = ref<HTMLElement | null>(null)

function toggle(): void {
  isOpen.value = !isOpen.value
}

function close(): void {
  isOpen.value = false
}

function handleOutsideClick(event: MouseEvent | PointerEvent): void {
  if (dropdownRef.value && !dropdownRef.value.contains(event.target as Node)) {
    close()
  }
}

function handleKeydown(event: KeyboardEvent): void {
  if (event.key === 'Escape') {
    close()
  }
}

onMounted(() => {
  document.addEventListener('pointerdown', handleOutsideClick)
  document.addEventListener('keydown', handleKeydown)
})

onBeforeUnmount(() => {
  document.removeEventListener('pointerdown', handleOutsideClick)
  document.removeEventListener('keydown', handleKeydown)
})
</script>

<template>
  <div ref="dropdownRef" class="relative">
    <!-- Trigger Button -->
    <button
      type="button"
      class="flex items-center gap-2.5 h-9 px-2 rounded-lg hover:bg-[var(--soft)] text-[var(--text)] transition-colors focus:outline-none cursor-pointer"
      :class="{ 'bg-[var(--soft)] text-[var(--teal)]': isOpen }"
      :aria-expanded="isOpen"
      aria-haspopup="true"
      @click="toggle"
    >
      <span class="w-7 h-7 rounded-full bg-[var(--soft)] text-[var(--teal)] font-bold text-xs grid place-items-center">
        {{ (user.name || 'A').charAt(0).toUpperCase() }}
      </span>

      <span class="text-xs font-semibold max-w-[120px] truncate hidden sm:inline-block">
        {{ user.name }}
      </span>

      <ChevronDown
        :size="14"
        class="text-[var(--muted)] transition-transform duration-200"
        :class="{ 'rotate-180 text-[var(--teal)]': isOpen }"
      />
    </button>

    <!-- Floating Dropdown Popup -->
    <transition
      enter-active-class="transition ease-out duration-100"
      enter-from-class="transform opacity-0 scale-95"
      enter-to-class="transform opacity-100 scale-100"
      leave-active-class="transition ease-in duration-75"
      leave-from-class="transform opacity-100 scale-100"
      leave-to-class="transform opacity-0 scale-95"
    >
      <div
        v-if="isOpen"
        class="absolute right-0 top-11 w-64 rounded-xl border border-[var(--border)] bg-[var(--card)] shadow-xl p-2 z-50 text-[var(--text)] rtl:right-auto rtl:left-0"
        role="menu"
      >
        <!-- User Info Header -->
        <div class="p-2.5 rounded-lg bg-[var(--page)] border border-[var(--border)] mb-1.5">
          <div class="flex items-center gap-2.5">
            <span class="w-9 h-9 rounded-full bg-[var(--soft)] text-[var(--teal)] font-bold text-sm grid place-items-center flex-shrink-0">
              {{ (user.name || 'A').charAt(0).toUpperCase() }}
            </span>
            <div class="min-w-0 flex-1">
              <div class="font-bold text-xs text-[var(--text)] truncate">
                {{ user.name }}
              </div>
              <div class="text-[11px] text-[var(--muted)] truncate">
                {{ user.email }}
              </div>
            </div>
          </div>
          <div class="mt-2 flex items-center justify-between text-[10px]">
            <span class="text-[var(--muted)]">Role:</span>
            <span class="px-2 py-0.5 rounded-full bg-[var(--soft)] text-[var(--teal)] font-semibold">
              {{ user.role || 'Administrator' }}
            </span>
          </div>
        </div>

        <!-- Navigation Links -->
        <div class="space-y-0.5 py-1">
          <a
            :href="accountUrl"
            class="flex items-center gap-2.5 px-2.5 py-2 rounded-lg text-xs font-medium text-[var(--text)] hover:bg-[var(--soft)] hover:text-[var(--teal)] transition-colors"
            role="menuitem"
            @click="close"
          >
            <User :size="15" class="text-[var(--muted)]" />
            <span>My Profile</span>
          </a>

          <a
            :href="usersUrl"
            class="flex items-center gap-2.5 px-2.5 py-2 rounded-lg text-xs font-medium text-[var(--text)] hover:bg-[var(--soft)] hover:text-[var(--teal)] transition-colors"
            role="menuitem"
            @click="close"
          >
            <Users :size="15" class="text-[var(--muted)]" />
            <span>Manage Users</span>
          </a>

          <a
            :href="attributesUrl"
            class="flex items-center gap-2.5 px-2.5 py-2 rounded-lg text-xs font-medium text-[var(--text)] hover:bg-[var(--soft)] hover:text-[var(--teal)] transition-colors"
            role="menuitem"
            @click="close"
          >
            <SlidersHorizontal :size="15" class="text-[var(--muted)]" />
            <span>Catalog Attributes</span>
          </a>
        </div>

        <div class="border-t border-[var(--border)] my-1"></div>

        <!-- Logout Action Form -->
        <form method="POST" :action="logoutUrl">
          <input type="hidden" name="_token" :value="csrfToken" />
          <button
            type="submit"
            class="w-full flex items-center gap-2.5 px-2.5 py-2 rounded-lg text-xs font-semibold text-red-600 hover:bg-red-50 dark:hover:bg-red-950/30 transition-colors ltr:text-left rtl:text-right cursor-pointer"
            role="menuitem"
          >
            <LogOut :size="15" class="text-red-500" />
            <span>Sign Out</span>
          </button>
        </form>
      </div>
    </transition>
  </div>
</template>
