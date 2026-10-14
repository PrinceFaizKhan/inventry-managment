<script setup lang="ts">
import { Languages } from 'lucide-vue-next'
import { useI18n } from '@/lib/i18n'

const { locale, setLocale, supportedLocales } = useI18n()
</script>

<template>
  <div class="theme language-toggle" :title="locale === 'en-IN' ? 'Switch to Urdu (اردو)' : 'انگریزی میں تبدیل کریں'">
    <button
      v-for="item in supportedLocales"
      :key="item.code"
      :class="{ active: locale === item.code }"
      type="button"
      @click="setLocale(item.code)"
    >
      <Languages v-if="item.code === 'en-IN'" :size="13" />
      <span>{{ item.code === 'en-IN' ? 'EN' : 'اردو' }}</span>
    </button>
  </div>
</template>
