import { createRouter, createWebHistory } from 'vue-router'

import LandingPage from '@/pages/LandingPage.vue'

// Auth
import RegisterNasabah from '@/pages/auth/RegisterNasabah.vue'
import RegisterPengepul from '@/pages/auth/RegisterPengepul.vue'
import Login from '@/pages/auth/Login.vue'

// Dashboards
import DashboardAdmin from '@/pages/dashboards/admin/DashboardAdmin.vue'
import DashboardManager from '@/pages/dashboards/manager/DashboardManager.vue'
import DashboardPetugas from '@/pages/dashboards/petugas/DashboardPetugas.vue'
import DashboardPengepul from '@/pages/dashboards/pengepul/DashboardPengepul.vue'
import DashboardNasabah from '@/pages/dashboards/nasabah/DashboardNasabah.vue'

const routes = [
  {
    path: '/',
    component: LandingPage,
  },

  // Auth Routes
  {
    path: '/register-nasabah',
    component: RegisterNasabah,
  },
  {
    path: '/register-pengepul',
    component: RegisterPengepul,
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
    path: '/dashboard-pengepul',
    component: DashboardPengepul,
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
