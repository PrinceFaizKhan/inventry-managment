import { computed, ref } from 'vue'

export type LocaleCode = 'en-IN'

export interface LocaleDefinition {
  code: LocaleCode
  name: string
  nativeName: string
  direction: 'ltr'
  currencySymbol: string
  currencyCode: string
}

export const SUPPORTED_LOCALES: LocaleDefinition[] = [
  {
    code: 'en-IN',
    name: 'English',
    nativeName: 'English',
    direction: 'ltr',
    currencySymbol: '₹',
    currencyCode: 'INR',
  },
]

if (typeof window !== 'undefined') {
  localStorage.removeItem('ht-locale')
}

const initialLocale: LocaleCode = 'en-IN'

const currentLocale = ref<LocaleCode>(initialLocale)

// Translation tables - partitioned for extensibility
const translations: Record<LocaleCode, Record<string, string>> = {
  'en-IN': {
    // App & Layout
    'brand.title': 'Khan Group & Company',
    'brand.portal': 'Admin Portal',
    'brand.tagline': 'Your business. Connected & in control.',
    'brand.tagline_desc': 'Manage your catalog, track inventory and keep every sale in one place.',
    'brand.workspace': 'Admin workspace',

    // Navigation
    'nav.dashboard': 'Dashboard',
    'nav.catalog': 'Catalog',
    'nav.catalog.attributes': 'Attributes',
    'nav.catalog.product_types': 'Product Types',
    'nav.catalog.products': 'Products',
    'nav.inventory': 'Inventory',
    'nav.sales': 'Sales',
    'nav.settings': 'Settings',
    'nav.settings.users': 'Users',
    'nav.settings.roles': 'Roles',

    // Actions & Common
    'action.sign_in': 'Sign in',
    'action.signing_in': 'Signing in…',
    'action.save': 'Save',
    'action.saving': 'Saving…',
    'action.saved': 'Saved successfully.',
    'action.create': 'Create',
    'action.edit': 'Edit',
    'action.delete': 'Delete',
    'action.cancel': 'Cancel',
    'action.back': 'Back',
    'action.logout': 'Logout',
    'action.filter': 'Filter',
    'action.filters': 'Filters',
    'action.search': 'Search',
    'action.fill': 'Fill',
    'common.yes': 'Yes',
    'common.no': 'No',
    'common.on': 'On',
    'common.off': 'Off',
    'common.loading': 'Loading…',

    // Attributes Domain
    'attributes.title': 'Attributes',
    'attributes.desc': 'Define reusable fields for your catalog.',
    'attributes.name': 'Name',
    'attributes.code': 'Code',
    'attributes.type': 'Type',
    'attributes.required': 'Required',
    'attributes.required_desc': 'Users must provide a value for this attribute.',
    'attributes.filterable': 'Filterable',
    'attributes.filterable_title': 'Filterable (Is Filter)',
    'attributes.filterable_desc': 'Allow filtering products by this attribute.',
    'attributes.general_settings': 'General settings',
    'attributes.live_preview': 'Live preview',
    'attributes.live_preview_desc': 'Preview values are temporary and will not be saved.',
    'attributes.options': 'Options',
    'attributes.options_desc': 'Option codes stay stable after creation. Labels and order can be changed.',
    'attributes.validation_settings': 'Validation and settings',

    // Auth
    'auth.welcome_back': 'Welcome back',
    'auth.welcome_sub': 'Sign in to Khan Group & Company admin.',
    'auth.email': 'Email address',
    'auth.password': 'Password',
    'auth.remember_me': 'Remember me',
    'auth.admin_credentials': 'Admin Credentials',
  },
}

/**
 * Apply direction and lang attributes to document HTML.
 */
function applyDocumentAttributes(locale: LocaleCode): void {
  if (typeof document === 'undefined') return
  const def = SUPPORTED_LOCALES.find((item) => item.code === locale) || SUPPORTED_LOCALES[0]
  document.documentElement.lang = def.code
  document.documentElement.dir = def.direction
}

// Initial application
if (typeof document !== 'undefined') {
  applyDocumentAttributes(initialLocale)
}

/**
 * Register external module translations at runtime.
 */
export function registerTranslations(locale: LocaleCode, messages: Record<string, string>): void {
  if (!translations[locale]) {
    translations[locale] = {}
  }
  Object.assign(translations[locale], messages)
}

/**
 * Fetch remote translations from backend API.
 */
export async function loadTranslations(locale: LocaleCode): Promise<void> {
  try {
    const res = await fetch(`/api/translations?locale=${locale}`, {
      headers: { Accept: 'application/json' },
    })
    if (res.ok) {
      const data = await res.json()
      if (data && data.messages) {
        registerTranslations(locale, data.messages)
      }
    }
  } catch {
    // Graceful fallback to local dictionary
  }
}

/**
 * Set current application locale.
 */
export function setLocale(locale: LocaleCode): void {
  if (!translations[locale]) {
    translations[locale] = {}
  }
  currentLocale.value = locale
  if (typeof window !== 'undefined') {
    localStorage.setItem('ht-locale', locale)
    applyDocumentAttributes(locale)
    void loadTranslations(locale)
  }
}

// Initial remote fetch
if (typeof window !== 'undefined') {
  void loadTranslations(initialLocale)
}

/**
 * Translate a key with optional fallback and parameter interpolation.
 */
export function t(key: string, fallback?: string, params?: Record<string, string | number>): string {
  const active = currentLocale.value
  let message = translations[active]?.[key] ?? translations['en-IN']?.[key] ?? fallback ?? key

  if (params) {
    for (const [paramKey, paramVal] of Object.entries(params)) {
      message = message.replace(new RegExp(`{${paramKey}}`, 'g'), String(paramVal))
    }
  }

  return message
}

/**
 * Indian numbering and currency formatter (₹ Lakhs & Crores).
 */
export function formatIndianCurrency(amount: number): string {
  return new Intl.NumberFormat('en-IN', {
    style: 'currency',
    currency: 'INR',
    maximumFractionDigits: 2,
  }).format(amount)
}

/**
 * Composable for Vue components.
 */
export function useI18n() {
  const locale = computed(() => currentLocale.value)
  const isRtl = computed(() => false)
  const currentDefinition = computed(
    () => SUPPORTED_LOCALES.find((l) => l.code === currentLocale.value) || SUPPORTED_LOCALES[0]
  )

  return {
    locale,
    isRtl,
    currentDefinition,
    supportedLocales: SUPPORTED_LOCALES,
    setLocale,
    t,
    formatIndianCurrency,
    registerTranslations,
    loadTranslations,
  }
}
