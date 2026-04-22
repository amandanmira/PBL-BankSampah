import { createRouter, createWebHistory } from "vue-router";
import "../style.css";
import LandingPage from "@/pages/public/LandingPage.vue";

// Auth
import ChooseRole from "@/pages/auth/ChooseRole.vue";
import RegisterNasabah from "@/pages/auth/RegisterNasabah.vue";
import RegisterPengepul from "@/pages/auth/RegisterPengepul.vue";
import BuatPetugas from "@/pages/dashboards/admin/BuatPetugas.vue";
import BuatManager from "@/pages/dashboards/admin/BuatManager.vue";
import Login from "@/pages/auth/Login.vue";
import ForgotPassword from "@/pages/auth/ForgotPassword.vue";
import ResetPassword from "@/pages/auth/ResetPassword.vue";
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
import ConfigWeb from "@/pages/dashboards/admin/managePages/ConfigWeb.vue";

//Penarikan
import ListPenarikan from "@/pages/dashboards/petugas/ListPenarikan.vue";
import RiwayatPenarikan from "@/pages/dashboards/petugas/RiwayatPenarikan.vue";

//Penjmeputan
import ListPenjemputan from "@/pages/dashboards/petugas/ListPenjemputan.vue";
import RiwayatPenjemputanPetugas from "@/pages/dashboards/petugas/RiwayatPenjemputan.vue";
import Penimbangan from "@/pages/dashboards/petugas/Penimbangan.vue";
import PenimbanganAntarSendiri from "@/pages/dashboards/petugas/PenimbanganAntarSendiri.vue";
import NotifikasiPetugas from "@/pages/dashboards/petugas/Notifikasi.vue";
import LaporanHarianPetugas from "@/pages/dashboards/petugas/LaporanHarian.vue";

// Gudang
import GudangIndex from "@/pages/dashboards/admin/managePages/gudang/GudangIndex.vue";
import GudangCreate from "@/pages/dashboards/admin/managePages/gudang/GudangCreate.vue";
import GudangEdit from "@/pages/dashboards/admin/managePages/gudang/GudangEdit.vue";
import GudangShow from "@/pages/dashboards/admin/managePages/gudang/GudangShow.vue";
import ManageSampahGudang from "@/pages/dashboards/admin/managePages/gudang/ManageSampahGudang.vue";
import ManageTukang from "@/pages/dashboards/admin/managePages/gudang/ManageTukang.vue";

// Sampah
import SampahIndex from "@/pages/dashboards/admin/managePages/sampah/SampahIndex.vue";
import SampahCreate from "@/pages/dashboards/admin/managePages/sampah/SampahCreate.vue";
import SampahEdit from "@/pages/dashboards/admin/managePages/sampah/SampahEdit.vue";
import SampahShow from "@/pages/dashboards/admin/managePages/sampah/SampahShow.vue";

// Profile
import EditProfilePengepul from "@/pages/dashboards/pengepul/EditProfilePengepul.vue";
import EditProfileNasabah from "@/pages/dashboards/nasabah/EditProfileNasabah.vue";
import EditPetugas from "@/pages/dashboards/admin/EditPetugas.vue";
import EditManager from "@/pages/dashboards/admin/EditManager.vue";

// Nasabah
import RequestPenjemputan from "@/pages/dashboards/nasabah/RequestPenjemputan.vue";
import RequestPenarikan from "@/pages/dashboards/nasabah/RequestPenarikan.vue";
import RiwayatPenjemputan from "@/pages/dashboards/nasabah/RiwayatPenjemputan.vue";

//Pengepul
import RequestPembelianCreate from "@/pages/dashboards/pengepul/requestPembelian/RequestPembelianCreate.vue";
import RequestPembelianIndex from "@/pages/dashboards/pengepul/requestPembelian/RequestPembelianIndex.vue";
import RequestPembelianEdit from "@/pages/dashboards/pengepul/requestPembelian/RequestPembelianEdit.vue";

//Manager
import RiwayatPenarikanMan from "@/pages/dashboards/manager/RiwayatPenarikanMan.vue";
import RiwayatPenjemputanMan from "@/pages/dashboards/manager/RiwayatPenjemputanMan.vue";

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
    path: "/forgot-password",
    component: ForgotPassword,
  },
  {
    path: "/reset-password/:token",
    name: "ResetPassword",
    component: ResetPassword,
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
  {
    path: "/dashboard-admin/edit-petugas/:id",
    component: EditPetugas,
  },
  {
    path: "/dashboard-admin/edit-manager/:id",
    component: EditManager,
  },

  // Tambahkan rute baru untuk kelola petugas
  {
    path: "/dashboard-admin/buat-petugas",
    component: BuatPetugas,
  },
  {
    path: "/dashboard-admin/buat-manager",
    component: BuatManager,
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
		children: [
			{
        path: "sampah",
        component: ManageSampahGudang,
      },
			{
        path: "tukang",
        component: ManageTukang,
      },
		]
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

  // Other
  {
    path: "/dashboard-admin/verifikasi-pengepul",
    component: VerifikasiPengepul,
  },
  {
    path: "/dashboard-admin/konfigurasi-web",
    component: ConfigWeb,
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
  {
    path: "/dashboard-manager/riwayat-penjemputan",
    component: RiwayatPenjemputanMan,
  },
  {
    path: "/dashboard-manager/riwayat-penarikan",
    component: RiwayatPenarikanMan,
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
  {
    path: "/dashboard-petugas/listpenjemputan",
    component: ListPenjemputan,
  },
  {
    path: "/dashboard-petugas/listpenarikan",
    component: ListPenarikan,
  },
  {
    path: "/dashboard-petugas/riwayat-penjemputan",
    component: RiwayatPenjemputanPetugas,
  },
  {
    path: '/dashboard-petugas/riwayat-penarikan',
    component: RiwayatPenarikan,
  },
  {
    path: "/dashboard-petugas/penimbangan/:id",
    component: Penimbangan,
  },
  {
    path: "/dashboard-petugas/penimbangan",
    component: PenimbanganAntarSendiri,
  },
  {
    path: "/dashboard-petugas/notifikasi",
    component: NotifikasiPetugas,
  },
  {
    path: "/dashboard-petugas/laporan-harian",
    component: LaporanHarianPetugas,
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
  {
    path: "/dashboard-pengepul/request-pembelian",
    component: RequestPembelianIndex,
  },
  {
    path: "/dashboard-pengepul/request-pembelian/:id",
    component: RequestPembelianEdit,
  },
  {
    path: "/dashboard-pengepul/request-pembelian/create",
    component: RequestPembelianCreate,
  },

  // Nasabah
  {
    path: "/dashboard-nasabah",
    component: DashboardNasabah,
  },
  {
    path: "/dashboard-nasabah/riwayatpenjemputan",
    component: RiwayatPenjemputan,
  },
  {
    path: "/dashboard-nasabah/edit-profile",
    component: EditProfileNasabah,
  },
  {
    path: "/dashboard-nasabah/request-penjemputan",
    component: RequestPenjemputan,
  },
  {
    path: "/dashboard-nasabah/request-penarikan",
    component: RequestPenarikan,
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
