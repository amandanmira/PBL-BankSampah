import { createRouter, createWebHistory } from "vue-router";
import "../style.css";
import LandingPage from "@/pages/public/LandingPage.vue";

// Auth
import ChooseRole from "@/pages/auth/ChooseRole.vue";
import RegisterNasabah from "@/pages/auth/RegisterNasabah.vue";
import RegisterPengepul from "@/pages/auth/RegisterPengepul.vue";
import BuatPetugas from "@/pages/dashboards/admin/BuatPetugas.vue";
import Login from "@/pages/auth/Login.vue";
import VerifikasiEmail from "@/pages/dashboards/nasabah/VerifikasiEmail.vue";

// Dashboards
import DashboardAdmin from "@/pages/dashboards/admin/DashboardAdmin.vue";
import DashboardManager from "@/pages/dashboards/manager/DashboardManager.vue";
import DashboardPetugas from "@/pages/dashboards/petugas/DashboardPetugas.vue";
import DashboardPengepul from "@/pages/dashboards/pengepul/DashboardPengepul.vue";
import DashboardNasabah from "@/pages/dashboards/nasabah/DashboardNasabah.vue";

// Import komponen baru
import VerifikasiPengepul from "@/pages/dashboards/admin/VerifikasiPengepul.vue";
import LandingPageLayout from "@/layouts/LandingPageLayout.vue";
import FAQ from "@/pages/public/FAQ.vue";
import TermsAndPrivacy from "@/pages/public/TermsAndPrivacy.vue";
import AboutFull from "@/pages/public/AboutFull.vue";
import Blog from "@/pages/public/Blog.vue";
import OneBlog from "@/pages/public/OneBlog.vue";
import KelolaUser from "@/pages/dashboards/admin/KelolaUser.vue";

// Berita
import BeritaIndex from "@/pages/dashboards/petugas/berita/index.vue";
import BeritaCreate from "@/pages/dashboards/petugas/berita/Create.vue";
import BeritaEdit from "@/pages/dashboards/petugas/berita/Edit.vue";
import BeritaShow from "@/pages/dashboards/petugas/berita/show.vue";

// Gudang
import GudangIndex from "@/pages/dashboards/admin/managePages/gudang/GudangIndex.vue";
import GudangCreate from "@/pages/dashboards/admin/managePages/gudang/GudangCreate.vue";
import GudangEdit from "@/pages/dashboards/admin/managePages/gudang/GudangEdit.vue";
import GudangShow from "@/pages/dashboards/admin/managePages/gudang/GudangShow.vue";

// Sampah
import SampahIndex from "@/pages/dashboards/admin/managePages/sampah/SampahIndex.vue";
import SampahCreate from "@/pages/dashboards/admin/managePages/sampah/SampahCreate.vue";
import SampahEdit from "@/pages/dashboards/admin/managePages/sampah/SampahEdit.vue";
import SampahShow from "@/pages/dashboards/admin/managePages/sampah/SampahShow.vue";

// Profile
import EditProfilePengepul from "@/pages/dashboards/pengepul/EditProfilePengepul.vue";
import EditProfileNasabah from "@/pages/dashboards/nasabah/EditProfileNasabah.vue";

const routes = [
  {
    path: "/",
    component: LandingPageLayout,
    children: [
      {
        path: "/",
        component: LandingPage,
      },
      {
        path: "/faq",
        component: FAQ,
      },
      {
        path: "/about",
        component: AboutFull,
      },
      {
        path: "/terms-and-privacy",
        component: TermsAndPrivacy,
      },
      {
        path: "/blog",
        component: Blog,
      },
      {
        path: "/blog/:slug",
        name: "SingleBlog",
        component: OneBlog,
      },
    ],
  },

  // Auth Routes
  {
    path: "/choose-role",
    component: ChooseRole,
  },
  {
    path: "/register-nasabah",
    component: RegisterNasabah,
  },
  {
    path: "/register-pengepul",
    component: RegisterPengepul,
  },
  {
    path: "/login",
    component: Login,
  },
  {
    path: "/verify-email/:token",
    name: "VerifyEmail",
    component: VerifikasiEmail,
  },

  // Dashboard pages
  // Admin
  {
    path: "/dashboard-admin",
    component: DashboardAdmin,
  },

  // Tambahkan rute baru untuk kelola petugas
  {
    path: "/dashboard-admin/buat-petugas",
    component: BuatPetugas,
  },

  // Halaman Kelola
  // Akun
  {
    path: "/dashboard-admin/kelola-users",
    component: KelolaUser,
  },

  // Gudang
  {
    path: "/dashboard-admin/kelola-gudang",
    component: GudangIndex,
  },
  {
    path: "/dashboard-admin/kelola-gudang/create",
    component: GudangCreate,
  },
  {
    path: "/dashboard-admin/kelola-gudang/:id/edit",
    component: GudangEdit,
  },
  {
    path: "/dashboard-admin/kelola-gudang/:id",
    component: GudangShow,
  },

  // Sampah
  {
    path: "/dashboard-admin/kelola-sampah",
    component: SampahIndex,
  },
  {
    path: "/dashboard-admin/kelola-sampah/create",
    component: SampahCreate,
  },
  {
    path: "/dashboard-admin/kelola-sampah/:id/edit",
    component: SampahEdit,
  },
  {
    path: "/dashboard-admin/kelola-sampah/:id",
    component: SampahShow,
  },

  {
    path: "/dashboard-admin/verifikasi-pengepul",
    component: VerifikasiPengepul,
  },

  // Manager
  {
    path: "/dashboard-admin/verifikasi-email",
    component: VerifikasiEmail,
  },
  {
    path: "/dashboard-manager",
    component: DashboardManager,
  },

  // Petugas
  {
    path: "/dashboard-petugas",
    component: DashboardPetugas,
  },
  {
    path: "/dashboard-petugas/kelola-berita",
    component: BeritaIndex,
  },

  {
    path: "/dashboard-petugas/kelola-berita/create",
    component: BeritaCreate,
  },

  {
    path: "/dashboard-petugas/kelola-berita/:id/edit",
    name: "berita.edit",
    component: BeritaEdit,
  },
  {
    path: "/dashboard-petugas/berita/:id",
    name: "berita.show",
    component: BeritaShow,
  },

  // Pengepul
  {
    path: "/dashboard-pengepul",
    component: DashboardPengepul,
  },
  {
    path: "/dashboard-pengepul/edit-profile",
    component: EditProfilePengepul,
  },

  // Nasabah
  {
    path: "/dashboard-nasabah",
    component: DashboardNasabah,
  },
  {
    path: "/dashboard-nasabah/edit-profile",
    component: EditProfileNasabah,
  },
];

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
            behavior: "smooth",
            // Kita kasih jarak sedikit dari atas karena Navbar-nya sekarang Sticky (menempel)
            // Namun Vue Router 'el' offset biasanya menggunakan scroll-margin-top di CSS
          });
        }, 300);
      });
    }

    // 3. Pindah halaman standar, kembalikan posisi layar ke pucuk secara halus
    return { top: 0, behavior: "smooth" };
  },
});

export default router;
