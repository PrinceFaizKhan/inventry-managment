import { createApp } from 'vue'
import '../css/app.css'
import { request } from '../../Modules/Core/Resources/js/lib/http'

/**
 * Global Vue application instance matching UnoPim architecture.
 * Exposing on window.app allows Blade views to register inline components
 * via app.component('v-...', { template: '#...', ... }) before mounting.
 */
const app = createApp({
  data() {
    return {}
  },
  methods: {
    onSubmit() {},
    onInvalidSubmit() {},
  },
})

window.app = app

// Register global theme-toggle component matching Haseen Telecom design
app.component('theme-toggle', {
  template: `
    <div class="theme">
      <button :class="{ active: theme === 'light' }" type="button" @click="setTheme('light')" title="Light Mode">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
        <span>Light</span>
      </button>
      <button :class="{ active: theme === 'dark' }" type="button" @click="setTheme('dark')" title="Dark Mode">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
        <span>Dark</span>
      </button>
    </div>
  `,
  data() {
    return {
      theme: localStorage.getItem('ht-theme') || (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'),
    }
  },
  mounted() {
    this.applyTheme(this.theme)
  },
  methods: {
    setTheme(t) {
      this.theme = t
      localStorage.setItem('ht-theme', t)
      this.applyTheme(t)
    },
    applyTheme(t) {
      if (t === 'dark') {
        document.documentElement.setAttribute('data-theme', 'dark')
        document.documentElement.classList.add('dark')
      } else {
        document.documentElement.removeAttribute('data-theme')
        document.documentElement.classList.remove('dark')
      }
    },
  },
})
app.config.globalProperties.$axios = {
  get: (url, opts) => request(url, { method: 'GET', ...opts }),
  post: (url, data, opts) => request(url, { method: 'POST', body: JSON.stringify(data), ...opts }),
  put: (url, data, opts) => request(url, { method: 'PUT', body: JSON.stringify(data), ...opts }),
  delete: (url, opts) => request(url, { method: 'DELETE', ...opts }),
}
window.request = request
window.axios = app.config.globalProperties.$axios


/**
 * Register all Vue components from independent Modules and shared UI library.
 */
function registerAllComponents(application) {
  const moduleComponents = import.meta.glob('../../Modules/**/Resources/js/**/*.vue', { eager: true })
  for (const [path, mod] of Object.entries(moduleComponents)) {
    if (mod && typeof mod === 'object' && 'default' in mod) {
      const fileName = path.split('/').pop()?.replace(/\.\w+$/, '')
      if (fileName) {
        application.component(fileName, mod.default)
        const kebab = fileName.replace(/([a-z0-9])([A-Z])/g, '$1-$2').toLowerCase()
        if (kebab !== fileName) {
          application.component(kebab, mod.default)
        }
      }
    }
  }
}

registerAllComponents(app)

window.toggleSidebar = function () {
  const sidebar = document.getElementById('admin-sidebar')
  const backdrop = document.getElementById('sidebar-backdrop')
  if (sidebar) sidebar.classList.toggle('open')
  if (backdrop) backdrop.classList.toggle('open')
}

window.closeSidebar = function () {
  const sidebar = document.getElementById('admin-sidebar')
  const backdrop = document.getElementById('sidebar-backdrop')
  if (sidebar) sidebar.classList.remove('open')
  if (backdrop) backdrop.classList.remove('open')
}

document.addEventListener('keydown', function (e) {
  if (e.key === 'Escape') {
    window.closeSidebar()
  }
})

/**
 * Mount the application on window load event so that all inline Vue components
 * defined in Blade views have been registered on window.app first.
 */
window.addEventListener('load', function () {
  const appElement = document.getElementById('app')
  if (appElement) {
    app.mount('#app')
  }
})

