<script setup>
import { ref } from 'vue';
import { ArrowRight, Box, Eye, EyeOff, LockKeyhole, PackageSearch, ReceiptText } from 'lucide-vue-next';
import BrandMark from './BrandMark.vue';
import ThemeToggle from './ThemeToggle.vue';
import LanguageToggle from './LanguageToggle.vue';
import BaseButton from '@/components/ui/BaseButton.vue';
import FormField from '@/components/ui/FormField.vue';
import { request } from '@/lib/http';
import { t } from '@/lib/i18n';

const props = defineProps({ theme: String });
const emit = defineEmits(['update:theme', 'logged-in', 'notify']);
const email = ref('');
const password = ref('');
const visible = ref(false);
const loading = ref(false);
const errorMessage = ref('');

function fillAdmin() {
  email.value = 'admin@haseentelecom.test';
  password.value = 'password';
  errorMessage.value = '';
}

async function login() {
  loading.value = true;
  errorMessage.value = '';
  try {
    const data = await request('/login', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ email: email.value, password: password.value }),
    });
    emit('logged-in', data?.user);
    const redirectUrl = new URLSearchParams(window.location.search).get('redirect') || '/dashboard';
    window.location.href = redirectUrl;
  } catch (e) {
    errorMessage.value = e.message || 'The provided credentials are incorrect.';
    emit('notify', errorMessage.value);
  } finally {
    loading.value = false;
  }
}
</script>
<template>
  <div class="login">
    <header>
      <BrandMark />
      <div style="display:flex;gap:8px;align-items:center;">
        <LanguageToggle />
        <ThemeToggle :model-value="theme" @update:model-value="emit('update:theme', $event)" />
      </div>
    </header>
    <main>
      <section class="intro">
        <p class="eyebrow">{{ t('brand.title') }} · {{ t('brand.portal') }}</p>
        <h1>{{ t('brand.tagline') }}</h1>
        <p>{{ t('brand.tagline_desc') }}</p>
        <div class="tags">
          <span><PackageSearch :size="15" />{{ t('nav.catalog') }}</span>
          <span><Box :size="15" />{{ t('nav.inventory') }}</span>
          <span><ReceiptText :size="15" />{{ t('nav.sales') }}</span>
        </div>
      </section>
      <section class="login-card">
        <span class="lock"><LockKeyhole :size="20" /></span>
        <h2>{{ t('auth.welcome_back') }}</h2>
        <p>{{ t('auth.welcome_sub') }}</p>
        <div v-if="errorMessage" style="margin-bottom:14px;padding:10px 12px;background:#fef2f2;border:1px solid #fecaca;color:#991b1b;border-radius:8px;font-size:12px;font-weight:500;">
          {{ errorMessage }}
        </div>
        <form @submit.prevent="login">
          <FormField v-model="email" :label="t('auth.email')" type="email" placeholder="you@company.com" />
          <label class="field">
            <span>{{ t('auth.password') }}</span>
            <div class="password">
              <input v-model="password" :type="visible ? 'text' : 'password'" required placeholder="••••••••" />
              <button type="button" @click="visible = !visible">
                <EyeOff v-if="visible" :size="18" />
                <Eye v-else :size="18" />
              </button>
            </div>
          </label>
          <label class="remember"><input type="checkbox" />{{ t('auth.remember_me') }}</label>
          <BaseButton type="submit">
            <span>{{ loading ? t('action.signing_in') : t('action.sign_in') }}</span>
            <ArrowRight :size="18" />
          </BaseButton>
          <div style="margin-top:16px;padding:10px 14px;background:var(--soft);border-radius:8px;font-size:12px;display:flex;justify-content:space-between;align-items:center;">
            <div>
              <strong style="display:block;color:var(--teal);font-size:11px;">{{ t('auth.admin_credentials') }}</strong>
              <span style="color:var(--muted);font-size:11px;">admin@haseentelecom.test</span>
            </div>
            <button type="button" @click="fillAdmin" style="border:1px solid var(--border);background:var(--card);color:var(--teal);padding:4px 10px;border-radius:6px;font-size:11px;font-weight:600;">{{ t('action.fill') }}</button>
          </div>
        </form>
      </section>
    </main>
    <footer>{{ t('brand.title') }} <i>·</i> {{ t('brand.workspace') }}</footer>
  </div>
</template>
