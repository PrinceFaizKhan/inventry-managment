import { createApp } from 'vue'
import '../css/app.css'
import LoginView from '../../Modules/Admin/Resources/js/components/LoginView.vue'

/**
 * Register all Vue components from independent Modules and shared UI library.
 */
function registerAllComponents(app) {
  // Auto-discover components from all independent Modules
  const moduleComponents = import.meta.glob('../../Modules/**/Resources/js/**/*.vue', { eager: true })
  for (const [path, mod] of Object.entries(moduleComponents)) {
    if (mod && typeof mod === 'object' && 'default' in mod) {
      const fileName = path.split('/').pop()?.replace(/\.\w+$/, '')
      if (fileName) {
        app.component(fileName, mod.default)
        const kebab = fileName.replace(/([a-z0-9])([A-Z])/g, '$1-$2').toLowerCase()
        if (kebab !== fileName) {
          app.component(kebab, mod.default)
        }
      }
    }
  }
}

const appElement = typeof document !== 'undefined' ? document.getElementById('app') : null

if (appElement) {
  const hasContent = appElement.children.length > 0 || appElement.innerHTML.trim().length > 0
  const app = hasContent ? createApp({}) : createApp(LoginView)
  registerAllComponents(app)
  app.mount('#app')
}
