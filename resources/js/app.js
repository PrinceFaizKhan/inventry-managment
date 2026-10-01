import { createApp } from 'vue'
import { createRouter, createWebHistory } from 'vue-router'
import App from './App.vue'
import DashboardPage from './pages/DashboardPage.vue'
import AdminsPage from './pages/AdminsPage.vue'
import RolesPage from './pages/RolesPage.vue'
import '../css/app.css'

const router = createRouter({ history: createWebHistory(), routes: [
    { path: '/', name: 'dashboard', component: DashboardPage, meta: { title: 'Dashboard' } },
    { path: '/settings/users', name: 'users', component: AdminsPage, meta: { title: 'Users & access' } },
    { path: '/settings/roles/create', name: 'roles', component: RolesPage, meta: { title: 'Roles' } },
] })
createApp(App).use(router).mount('#app')
