import { createRouter, createWebHistory } from 'vue-router'

import LandingPage from '@/pages/LandingPage.vue'

// Auth
import Register from "@/pages/RegisterNasabah.vue"
import Login from '@/pages/Login.vue'

// Dashboards
import DashboardAdmin from '@/pages/dashboards/admin/DashboardAdmin.vue'
import DashboardManager from '@/pages/dashboards/manager/DashboardManager.vue'
import DashboardPetugas from '@/pages/dashboards/petugas/DashboardPetugas.vue'
import DashboardNasabah from '@/pages/dashboards/nasabah/DashboardNasabah.vue'

const routes = [
  {
    path: '/',
    component: LandingPage,
  },

  // Auth Routes
  {
    path: '/register-nasabah',
    component: Register,
  },
  {
    path: '/login',
    component: Login,
  },

  // Dashboard pages
  {
    path: '/dashboard-admin',
    component: DashboardAdmin,
  },
  {
    path: '/dashboard-manager',
    component: DashboardManager,
  },
  {
    path: '/dashboard-petugas',
    component: DashboardPetugas,
  },
  {
    path: '/dashboard-nasabah',
    component: DashboardNasabah,
  },
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: routes,
})

export default router
