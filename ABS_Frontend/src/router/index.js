import { createRouter, createWebHistory } from 'vue-router'

import LandingPage from '@/pages/LandingPage.vue'

// Auth
import RegisterNasabah from '@/pages/auth/RegisterNasabah.vue'
import RegisterPengepul from '@/pages/auth/RegisterPengepul.vue'
import BuatPetugas from '@/pages/dashboards/admin/BuatPetugas.vue'
import Login from '@/pages/auth/Login.vue'

// Dashboards
import DashboardAdmin from '@/pages/dashboards/admin/DashboardAdmin.vue'
import DashboardManager from '@/pages/dashboards/manager/DashboardManager.vue'
import DashboardPetugas from '@/pages/dashboards/petugas/DashboardPetugas.vue'
import DashboardPengepul from '@/pages/dashboards/pengepul/DashboardPengepul.vue'
import DashboardNasabah from '@/pages/dashboards/nasabah/DashboardNasabah.vue'

// Halaman Kelola
// Akun
import KelolaPetugas from '@/pages/dashboards/admin/KelolaPetugas.vue'

// Gudang
import GudangIndex from '@/pages/dashboards/admin/managePages/gudang/GudangIndex.vue'
import GudangCreate from '@/pages/dashboards/admin/managePages/gudang/GudangCreate.vue'
import GudangEdit from '@/pages/dashboards/admin/managePages/gudang/GudangEdit.vue'
import GudangShow from '@/pages/dashboards/admin/managePages/gudang/GudangShow.vue'

// Sampah
import SampahIndex from '@/pages/dashboards/admin/managePages/sampah/SampahIndex.vue'
import SampahCreate from '@/pages/dashboards/admin/managePages/sampah/SampahCreate.vue'
import SampahEdit from '@/pages/dashboards/admin/managePages/sampah/SampahEdit.vue'
import SampahShow from '@/pages/dashboards/admin/managePages/sampah/SampahShow.vue'

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
  // Admin
  {
    path: '/dashboard-admin',
    component: DashboardAdmin,
  },

  // Tambahkan rute baru untuk kelola petugas
  {
    path: '/dashboard-admin/buat-petugas',
    component: BuatPetugas,
  },

  // Halaman Kelola
  // Akun
  {
    path: '/dashboard-admin/kelola-petugas',
    component: KelolaPetugas,
  },

  // Gudang
  {
    path: '/dashboard-admin/kelola-gudang',
    component: GudangIndex,
  },
  {
    path: '/dashboard-admin/kelola-gudang/create',
    component: GudangCreate,
  },
  {
    path: '/dashboard-admin/kelola-gudang/:id/edit',
    component: GudangEdit,
  },
  {
    path: '/dashboard-admin/kelola-gudang/:id',
    component: GudangShow,
  },

  // Sampah
  {
    path: '/dashboard-admin/kelola-sampah',
    component: SampahIndex,
  },
  {
    path: '/dashboard-admin/kelola-sampah/create',
    component: SampahCreate,
  },
  {
    path: '/dashboard-admin/kelola-sampah/:id/edit',
    component: SampahEdit,
  },
  {
    path: '/dashboard-admin/kelola-sampah/:id',
    component: SampahShow,
  },

  // Manager
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