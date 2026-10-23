import { createApp } from 'vue'
import '../css/app.css'
import { request } from '../../Modules/Core/Resources/js/lib/http'

/**
 * Global Vue application instance matching modular architecture.
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

// Process any components registered before app.js was ready
window.registerVueComponent = function(name, component) {
  app.component(name, component)
}

if (window.__pendingComponents && Array.isArray(window.__pendingComponents)) {
  window.__pendingComponents.forEach(([name, component]) => {
    app.component(name, component)
  })
  window.__pendingComponents = []
}

import { Editor } from '@tiptap/core'
import StarterKit from '@tiptap/starter-kit'
import Placeholder from '@tiptap/extension-placeholder'
import Link from '@tiptap/extension-link'
import { useI18n } from '../../Modules/Core/Resources/js/lib/i18n'

// Expose Tiptap and useI18n globally for Blade+Vue x-templates in Modules/Admin
window.Tiptap = { Editor, StarterKit, Placeholder, Link }
window.useI18n = useI18n
app.config.globalProperties.$axios = {
  get: (url, opts) => request(url, { method: 'GET', ...opts }),
  post: (url, data, opts) => request(url, { method: 'POST', body: JSON.stringify(data), headers: { 'Content-Type': 'application/json', ...(opts?.headers || {}) }, ...opts }),
  put: (url, data, opts) => request(url, { method: 'PUT', body: JSON.stringify(data), headers: { 'Content-Type': 'application/json', ...(opts?.headers || {}) }, ...opts }),
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
  const isMobile = window.innerWidth <= 768
  const sidebar = document.getElementById('admin-sidebar')
  const backdrop = document.getElementById('sidebar-backdrop')

  if (isMobile) {
    if (sidebar) sidebar.classList.toggle('open')
    if (backdrop) backdrop.classList.toggle('open')
  } else {
    const isCurrentlyExpanded = document.documentElement.classList.contains('sidebar-expanded') || document.body.classList.contains('sidebar-expanded')
    const nextState = !isCurrentlyExpanded
    document.documentElement.classList.toggle('sidebar-expanded', nextState)
    document.body.classList.toggle('sidebar-expanded', nextState)
    localStorage.setItem('ht-sidebar-expanded', nextState ? 'true' : 'false')
    window.dispatchEvent(new CustomEvent('ht-sidebar-toggled', { detail: { expanded: nextState } }))
  }
}

window.closeSidebar = function () {
  const isMobile = window.innerWidth <= 768
  const sidebar = document.getElementById('admin-sidebar')
  const backdrop = document.getElementById('sidebar-backdrop')
  if (sidebar) sidebar.classList.remove('open')
  if (backdrop) backdrop.classList.remove('open')
  if (!isMobile) {
    document.documentElement.classList.remove('sidebar-expanded')
    document.body.classList.remove('sidebar-expanded')
    localStorage.setItem('ht-sidebar-expanded', 'false')
    window.dispatchEvent(new CustomEvent('ht-sidebar-toggled', { detail: { expanded: false } }))
  }
}

// Restore saved desktop sidebar expanded state on initial load
if (typeof window !== 'undefined' && window.innerWidth > 768) {
  if (localStorage.getItem('ht-sidebar-expanded') === 'false') {
    document.documentElement.classList.remove('sidebar-expanded')
    document.body.classList.remove('sidebar-expanded')
  } else {
    document.documentElement.classList.add('sidebar-expanded')
    document.body.classList.add('sidebar-expanded')
  }
}

document.addEventListener('keydown', function (e) {
  if (e.key === 'Escape') {
    window.closeSidebar()
  }
  if ((e.metaKey || e.ctrlKey) && e.key === '\\') {
    e.preventDefault()
    window.toggleSidebar()
  }
})

/**
 * Mount the application ensuring all inline Vue components defined in Blade
 * views have finished registering.
 */
function mountApp() {
  const appElement = document.getElementById('app')
  if (appElement && !appElement.__vue_app__) {
    // Process any remaining pending components just in case
    if (window.__pendingComponents && Array.isArray(window.__pendingComponents)) {
      window.__pendingComponents.forEach(([name, component]) => {
        app.component(name, component)
      })
      window.__pendingComponents = []
    }
    app.mount('#app')
  }
}

window.mountApp = mountApp

if (document.readyState === 'complete' || document.readyState === 'interactive') {
  setTimeout(mountApp, 0)
} else {
  document.addEventListener('DOMContentLoaded', () => setTimeout(mountApp, 0))
  window.addEventListener('load', mountApp)
}

