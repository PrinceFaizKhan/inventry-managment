import { createApp } from 'vue'
import { createRouter, createWebHistory } from 'vue-router'
import App from './App.vue'
import DashboardPage from './pages/DashboardPage.vue'
import AdminsPage from './pages/AdminsPage.vue'
import RolesPage from './pages/RolesPage.vue'
import AttributesPage from './pages/AttributesPage.vue'
import AttributeEditPage from './pages/AttributeEditPage.vue'
import '../css/app.css'

const router = createRouter({ history: createWebHistory(), routes: [
    { path: '/', name: 'dashboard', component: DashboardPage, meta: { title: 'Dashboard' } },
    { path: '/settings/users', name: 'users', component: AdminsPage, meta: { title: 'Users & access' } },
    { path: '/settings/roles/create', name: 'roles', component: RolesPage, meta: { title: 'Roles' } },
    { path: '/catalog/attributes', name: 'catalog.attributes', component: AttributesPage, meta: { title: 'Attributes', activeMenu: 'catalog.attributes', breadcrumbs: ['Catalog', 'Attributes'] } },
    { path: '/catalog/attributes/:id/edit', name: 'catalog.attributes.edit', component: AttributeEditPage, meta: { title: 'Edit attribute', activeMenu: 'catalog.attributes', breadcrumbs: ['Catalog', 'Attributes', 'Edit'] } },
] })
createApp(App).use(router).mount('#app')
