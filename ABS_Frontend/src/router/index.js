import { createRouter, createWebHistory } from 'vue-router'
import '../style.css';
import LandingPage from '@/pages/public/LandingPage.vue'

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

// Import komponen baru
import KelolaPetugas from '@/pages/dashboards/admin/KelolaPetugas.vue'
import LandingPageLayout from '@/layouts/LandingPageLayout.vue';
import FAQ from '@/pages/public/FAQ.vue';
import TermsAndPrivacy from '@/pages/public/TermsAndPrivacy.vue';
import AboutFull from '@/pages/public/AboutFull.vue';
import Blog from '@/pages/public/Blog.vue';
import OneBlog from '@/pages/public/OneBlog.vue';

const routes = [
  {
    path: '/',
    component: LandingPageLayout,
    children:[
      {
        path:'/',
        component: LandingPage
      },
      {
        path:'/faq',
        component: FAQ
      },
      {
        path:'/about',
        component: AboutFull
      },
      {
        path:'/terms-and-privacy',
        component: TermsAndPrivacy
      },
      {
        path:'/blog',
        component: Blog
      },
      {
        path:'/blog/:slug',
        name: 'SingleBlog',
        component: OneBlog
      },
    ]
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
    path: '/dashboard-admin/buat-petugas',
    component: BuatPetugas,
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
  // Tambahkan rute baru untuk kelola petugas
  {
    path: '/dashboard-admin/kelola-petugas',
    component: KelolaPetugas,
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
  scrollBehavior(to, from, savedPosition) {
    // 1. Jika tombol back dipencet
    if (savedPosition) {
      return savedPosition;
    }
    
    // 2. Jika tujuan kita adalah sebuah section (hashtag)
    if (to.hash) {
      return new Promise((resolve) => {
        // Berikan sedikit penundaan agar halaman terlempar/ter-render dulu sebelum scroll
        setTimeout(() => {
          resolve({
            el: to.hash,
            behavior: 'smooth',
            // Kita kasih jarak sedikit dari atas karena Navbar-nya sekarang Sticky (menempel)
            // Namun Vue Router 'el' offset biasanya menggunakan scroll-margin-top di CSS
          });
        }, 300);
      });
    }
    
    // 3. Pindah halaman standar, kembalikan posisi layar ke pucuk secara halus
    return { top: 0, behavior: 'smooth' };
  }
})

export default router