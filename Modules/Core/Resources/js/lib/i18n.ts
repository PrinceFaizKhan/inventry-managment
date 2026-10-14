import { computed, ref } from 'vue'

export type LocaleCode = 'en-IN' | 'ur-IN'

export interface LocaleDefinition {
  code: LocaleCode
  name: string
  nativeName: string
  direction: 'ltr' | 'rtl'
  currencySymbol: string
  currencyCode: string
}

export const SUPPORTED_LOCALES: LocaleDefinition[] = [
  {
    code: 'en-IN',
    name: 'English (India)',
    nativeName: 'English (India)',
    direction: 'ltr',
    currencySymbol: '₹',
    currencyCode: 'INR',
  },
  {
    code: 'ur-IN',
    name: 'Urdu (India)',
    nativeName: 'اردو (ہندوستان)',
    direction: 'rtl',
    currencySymbol: '₹',
    currencyCode: 'INR',
  },
]

const initialLocale: LocaleCode =
  (typeof window !== 'undefined' && (localStorage.getItem('ht-locale') as LocaleCode)) || 'en-IN'

const currentLocale = ref<LocaleCode>(initialLocale)

// Translation tables - partitioned for extensibility
const translations: Record<LocaleCode, Record<string, string>> = {
  'en-IN': {
    // App & Layout
    'brand.title': 'Haseen Telecom',
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
    'auth.welcome_sub': 'Sign in to Haseen Telecom admin.',
    'auth.email': 'Email address',
    'auth.password': 'Password',
    'auth.remember_me': 'Remember me',
    'auth.admin_credentials': 'Admin Credentials',
  },
  'ur-IN': {
    // App & Layout
    'brand.title': 'حسین ٹیلی کام',
    'brand.portal': 'ایڈمن پورٹل',
    'brand.tagline': 'آپ کا کاروبار۔ بااختیار اور مربوط۔',
    'brand.tagline_desc': 'کیٹلاگ کا انتظام کریں، انوینٹری پر نظر رکھیں اور ہر فروخت کا ریکارڈ رکھیں۔',
    'brand.workspace': 'ایڈمن ورک اسپیس',

    // Navigation
    'nav.dashboard': 'ڈیش بورڈ',
    'nav.catalog': 'کیٹلاگ',
    'nav.catalog.attributes': 'اوصاف و خصوصیات',
    'nav.catalog.product_types': 'مصنوعات کی اقسام',
    'nav.catalog.products': 'مصنوعات',
    'nav.inventory': 'انوینٹری',
    'nav.sales': 'فروخت و سیلز',
    'nav.settings': 'ترتیبات',
    'nav.settings.users': 'صارفین',
    'nav.settings.roles': 'کردار اور اختیارات',

    // Actions & Common
    'action.sign_in': 'لاگ ان کریں',
    'action.signing_in': 'لاگ ان ہو رہا ہے…',
    'action.save': 'محفوظ کریں',
    'action.saving': 'محفوظ ہو رہا ہے…',
    'action.saved': 'کامیابی سے محفوظ ہو گیا۔',
    'action.create': 'نیا بنائیں',
    'action.edit': 'ترمیم',
    'action.delete': 'حذف کریں',
    'action.cancel': 'منسوخ کریں',
    'action.back': 'واپس',
    'action.logout': 'لاگ آؤٹ',
    'action.filter': 'فلٹر',
    'action.filters': 'فلٹرز',
    'action.search': 'تلاش کریں',
    'action.fill': 'خودکار بھریں',
    'common.yes': 'ہاں',
    'common.no': 'نہیں',
    'common.on': 'آن',
    'common.off': 'آف',
    'common.loading': 'لوڈ ہو رہا ہے…',

    // Attributes Domain
    'attributes.title': 'اوصاف (Attributes)',
    'attributes.desc': 'اپنے کیٹلاگ کے لیے قابلِ تجدید فیلڈز متعین کریں۔',
    'attributes.name': 'نام',
    'attributes.code': 'کوڈ',
    'attributes.type': 'قسم',
    'attributes.required': 'لازمی',
    'attributes.required_desc': 'صارفین کو اس وصف کی قیمت درج کرنا لازمی ہوگا۔',
    'attributes.filterable': 'قابلِ فلٹر',
    'attributes.filterable_title': 'فلٹر میں شامل کریں (Is Filter)',
    'attributes.filterable_desc': 'مصنوعات تلاش کرتے وقت اس وصف سے فلٹر کرنے کی اجازت دیں۔',
    'attributes.general_settings': 'عمومی ترتیبات',
    'attributes.live_preview': 'لائیو پیش نظارہ',
    'attributes.live_preview_desc': 'پیش نظارہ عارضی ہے اور محفوظ نہیں کیا جائے گا۔',
    'attributes.options': 'اختیارات / آپشنز',
    'attributes.options_desc': 'اختیاری کوڈز تبدیل نہیں ہوں گے۔ لیبلز اور ترتیب بدلی جا سکتی ہے۔',
    'attributes.validation_settings': 'توثیق اور ترتیبات',

    // Auth
    'auth.welcome_back': 'خوش آمدید',
    'auth.welcome_sub': 'حسین ٹیلی کام ایڈمن میں سائن ان کریں۔',
    'auth.email': 'ای میل پتہ',
    'auth.password': 'پاس ورڈ',
    'auth.remember_me': 'مجھے یاد رکھیں',
    'auth.admin_credentials': 'ایڈمن اسناد',
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
  const isRtl = computed(() => currentLocale.value === 'ur-IN')
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
