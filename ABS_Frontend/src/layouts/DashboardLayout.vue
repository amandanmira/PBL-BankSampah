<script setup>
import { ref, computed, onMounted, inject } from "vue";
import { useRouter, useRoute } from "vue-router";
import { Icon } from "@iconify/vue";
import { cn } from "@/lib/utils";
import DashboardFooter from "@/components/dashboard/DashboardFooter.vue";

const props = defineProps({
  title: { type: String, default: "Dashboard" }
});

const router = useRouter();
const route = useRoute();
const axios = inject('axios');

const isSidebarCollapsed = ref(false);
const isProfileDropdownOpen = ref(false);

const toggleSidebar = () => {
  isSidebarCollapsed.value = !isSidebarCollapsed.value;
  if (isSidebarCollapsed.value) {
    isProfileDropdownOpen.value = false;
  }
};

const role = computed(() => sessionStorage.getItem("role")?.toLowerCase());

const menuItems = computed(() => {
  console.log("Current role in sidebar:", role.value);

  if (role.value === "admin") {
    return [
      { name: "Dashboard", path: "/dashboard-admin", icon: "material-symbols:grid-view-outline" },
      { name: "Kelola Akun", path: "/dashboard-admin/kelola-users", icon: "material-symbols:person-outline" },
      { name: "Konfirmasi Pengepul", path: "/dashboard-admin/verifikasi-pengepul", icon: "material-symbols:verified-user-outline" },
      { name: "Konfigurasi Web", path: "/dashboard-admin/konfigurasi-web", icon: "material-symbols:settings-outline" },
      { name: "Kelola Sampah", path: "/dashboard-admin/kelola-sampah", icon: "material-symbols:delete-outline" },
      { name: "Kelola Gudang", path: "/dashboard-admin/kelola-gudang", icon: "material-symbols:home-work-outline" },
      { name: "Deadline Pembayaran", path: "/dashboard-admin/deadline-pembayaran", icon: "material-symbols:calendar-today-outline" },
    ];
  }
  if (role.value === "petugas") {
    return [
      { name: "Dashboard", path: "/dashboard-petugas", icon: "material-symbols:grid-view-outline" },
      { name: "Request Penjemputan", path: "/dashboard-petugas/listpenjemputan", icon: "material-symbols:local-shipping-outline" },
      { name: "Setor Manual", path: "/dashboard-petugas/penimbangan", icon: "material-symbols:storefront-outline" },
      { name: "Request Penarikan", path: "/dashboard-petugas/listpenarikan", icon: "material-symbols:account-balance-wallet-outline" },
      { name: "Pesanan Pengepul", path: "/dashboard-petugas/pesanan-pengepul", icon: "material-symbols:shopping-bag-outline" },
      { name: "Riwayat", path: "/dashboard-petugas/riwayat", icon: "material-symbols:history" },
      { name: "Laporan Harian", path: "/dashboard-petugas/laporan-harian", icon: "material-symbols:description-outline" },
      { name: "Berita", path: "/dashboard-petugas/kelola-berita", icon: "material-symbols:article-outline" },
    ];
  }
  if (role.value === "manager") {
    return [
      { name: "Dashboard", path: "/dashboard-manager", icon: "material-symbols:grid-view-outline" },
      { name: "Audit Data", path: "/dashboard-manager/audit-data", icon: "material-symbols:description-outline" },
    ];
  }
  if (role.value === "pengepul") {
    return [
      { name: "Dashboard", path: "/dashboard-pengepul", icon: "material-symbols:grid-view-outline" },
      { name: "Beli Sampah", path: "/dashboard-pengepul/beli-sampah", icon: "material-symbols:shopping-bag-outline" },
      { name: "Keranjang", path: "/dashboard-pengepul/keranjang", icon: "material-symbols:shopping-cart-outline" },
      { name: "Pesanan Saya", path: "/dashboard-pengepul/pesanan-saya", icon: "material-symbols:article-outline" },
    ];
  }
  // Default to nasabah
  return [
    { name: "Dashboard", path: "/dashboard-nasabah", icon: "material-symbols:grid-view-outline" },
    { name: "Katalog Sampah", path: "/dashboard-nasabah/katalog", icon: "material-symbols:book-outline" },
    { name: "Request Jemput/Setor", path: "/dashboard-nasabah/request-penjemputan", icon: "material-symbols:local-shipping-outline" },
    { name: "Penarikan Uang", path: "/dashboard-nasabah/request-penarikan", icon: "material-symbols:account-balance-wallet-outline" },
    { name: "Riwayat Penarikan", path: "/dashboard-nasabah/penarikan-saya", icon: "material-symbols:payments-outline" },
    { name: "Riwayat Setor", path: "/dashboard-nasabah/sampah-saya", icon: "material-symbols:delete-outline" },
  ];
});

const handleLogout = () => {
  sessionStorage.clear();
  router.push("/login");
};

const goToProfile = () => {
  if (role.value === 'nasabah') {
    router.push('/dashboard-nasabah/edit-profile');
  } else if (role.value === 'pengepul') {
    router.push('/dashboard-pengepul/edit-profile');
  }
};

const handleProfileClick = () => {
  if (['admin', 'petugas', 'manager'].includes(role.value)) {
    if (isSidebarCollapsed.value) {
      isSidebarCollapsed.value = false;
      isProfileDropdownOpen.value = true;
    } else {
      isProfileDropdownOpen.value = !isProfileDropdownOpen.value;
    }
  } else {
    goToProfile();
  }
};

const user = computed(() => {
  try {
    return JSON.parse(sessionStorage.getItem("user") || "{}");
  } catch (e) {
    return {};
  }
});

const webConfig = ref({
  logo: null,
});

const fetchWebConfig = async () => {
  try {
    const res = await axios.get("/api/web-config");
    webConfig.value = res.data;
  } catch (err) {
    console.error("Failed to fetch web config:", err);
  }
};

onMounted(() => {
  fetchWebConfig();
});
</script>

<template>
  <div class="h-screen bg-[#F5F5F0] flex font-['Inter'] overflow-hidden">
    <!-- Sidebar -->
    <aside
      :class="
        cn(
          'h-full bg-[#4A7043] text-white flex flex-col relative z-50 shrink-0 transition-all duration-300 ease-in-out',
          isSidebarCollapsed ? 'w-24' : 'w-80'
        )
      "
    >
      <!-- Sidebar Toggle (Top Left) -->
      <div :class="cn('p-6 flex transition-all duration-300', isSidebarCollapsed ? 'justify-center' : 'justify-start')">
        <button
          @click="toggleSidebar"
          class="p-2 hover:bg-white/10 rounded-lg transition-colors shrink-0 cursor-pointer"
        >
          <Icon icon="material-symbols:menu" class="w-7 h-7" />
        </button>
      </div>

      <!-- Logo Section -->
      <div :class="cn('px-4 mb-6 transition-all duration-300', isSidebarCollapsed ? 'flex flex-col items-center gap-4' : '')">
        <div
          :class="
            cn(
              'bg-white/10 rounded-[2rem] p-6 flex flex-col items-center text-center transition-all duration-300 overflow-hidden border border-white/5',
              isSidebarCollapsed ? 'bg-transparent border-none p-0' : 'w-full'
            )
          "
        >
          <!-- Logo Icon -->
          <div :class="cn('flex items-center justify-center transition-all duration-300', isSidebarCollapsed ? 'w-14 h-14' : 'w-24 h-24 mb-4')">
            <div v-if="webConfig.logo" class="w-full h-full p-1">
              <img :src="`${axios.defaults.baseURL}/storage/${webConfig.logo}`" class="w-full h-full object-contain" alt="Logo" />
            </div>
            <Icon v-else icon="material-symbols:recycling" :class="cn('text-[#4CAF50] transition-all duration-300', isSidebarCollapsed ? 'w-12 h-12' : 'w-20 h-20')" />
          </div>

          <!-- Logo Text -->
          <div
            :class="cn('overflow-hidden transition-all duration-300 flex flex-col items-center', isSidebarCollapsed ? 'max-h-0 opacity-0' : 'max-h-20 opacity-100')"
          >
            <p class="text-xs text-white/80 font-bold uppercase tracking-[0.2em]">Aplikasi Bank Sampah</p>
          </div>
        </div>

        <!-- Divider for collapsed state -->
        <div v-if="isSidebarCollapsed" class="w-10 h-[1px] bg-white/10"></div>
      </div>

      <!-- User Profile (Sidebar) -->
      <div :class="cn('px-4 mb-8 transition-all duration-300 flex flex-col items-center relative', isSidebarCollapsed ? 'gap-4' : 'gap-2')">
        <button
          @click="handleProfileClick"
          :class="
            cn(
              'bg-white/10 hover:bg-white/20 rounded-[2rem] p-4 flex items-center transition-all duration-300 overflow-hidden border border-white/5 cursor-pointer hover:border-[#A86444]/50 hover:shadow-[0_0_20px_rgba(168,100,68,0.15)] w-full',
              isSidebarCollapsed ? 'bg-transparent border-none p-0 w-14 h-14 justify-center' : 'gap-4 px-6',
              isProfileDropdownOpen ? 'bg-white/20 border-white/20' : ''
            )
          "
        >
          <!-- User Icon -->
          <div :class="cn('w-14 h-14 rounded-full bg-[#A86444] flex items-center justify-center shrink-0 shadow-lg border-2 border-white/10 transition-all duration-300', isSidebarCollapsed ? 'w-14 h-14' : '')">
            <Icon icon="material-symbols:person" class="w-8 h-8" />
          </div>

          <!-- User Details -->
          <div
            :class="cn('flex-1 text-left overflow-hidden transition-all duration-300 whitespace-nowrap', isSidebarCollapsed ? 'max-w-0 opacity-0' : 'max-w-xs opacity-100')"
          >
            <p class="font-black text-sm leading-tight truncate">
              {{ (role === 'nasabah' || role === 'pengepul') ? (user.username || 'Username') : (user.nama || user.name || 'Nama User') }}
            </p>
            <p class="text-[10px] font-bold text-white/60 mt-0.5">
              {{ (role === 'nasabah' || role === 'pengepul') ? (user.nama || 'Nama Lengkap') : (role === 'admin' ? 'Administrator' : (role ? role.charAt(0).toUpperCase() + role.slice(1) : 'Role')) }}
            </p>
          </div>

          <!-- Dropdown Icon -->
          <div v-if="['admin', 'petugas', 'manager'].includes(role) && !isSidebarCollapsed" :class="cn('shrink-0 transition-transform duration-300', isProfileDropdownOpen ? 'rotate-180' : '')">
            <Icon icon="material-symbols:keyboard-arrow-down-rounded" class="w-5 h-5 text-white/50" />
          </div>
        </button>

        <!-- Dropdown Info -->
        <div v-if="['admin', 'petugas', 'manager'].includes(role) && isProfileDropdownOpen && !isSidebarCollapsed" class="w-full bg-white/10 backdrop-blur-md rounded-[1.5rem] p-5 flex flex-col gap-4 border border-white/5 text-white shadow-xl animate-in fade-in slide-in-from-top-2 duration-300 relative z-20">
          <div class="flex items-center gap-4">
            <Icon icon="material-symbols:tag" class="w-5 h-5 text-white/40 shrink-0" />
            <div class="min-w-0 flex-1">
              <p class="text-[9px] font-black uppercase text-white/40 tracking-widest mb-0.5">ID {{ role === 'admin' ? 'Admin' : (role === 'petugas' ? 'Petugas' : 'Manager') }}</p>
              <p class="text-xs font-bold truncate">{{ user.admin_id || user.petugas_id || user.manager_id || user.id || '-' }}</p>
            </div>
          </div>
          <div class="flex items-center gap-4">
            <Icon icon="material-symbols:account-circle-outline" class="w-5 h-5 text-white/40 shrink-0" />
            <div class="min-w-0 flex-1">
              <p class="text-[9px] font-black uppercase text-white/40 tracking-widest mb-0.5">Username</p>
              <p class="text-xs font-bold truncate">{{ user.username || '-' }}</p>
            </div>
          </div>
          <div class="flex items-center gap-4">
            <Icon icon="material-symbols:mail-outline" class="w-5 h-5 text-white/40 shrink-0" />
            <div class="min-w-0 flex-1">
              <p class="text-[9px] font-black uppercase text-white/40 tracking-widest mb-0.5">Email</p>
              <p class="text-xs font-bold truncate">{{ user.email || '-' }}</p>
            </div>
          </div>
        </div>

        <!-- Warehouse Badge (For Petugas) -->
        <div v-if="role === 'petugas' && !isSidebarCollapsed" class="w-full flex justify-center">
          <div class="bg-white/10 text-white text-[10px] px-4 py-1.5 rounded-full font-bold backdrop-blur-sm border border-white/5">
            {{ user.gudang?.alamat || 'Gudang' }}
          </div>
        </div>

        <!-- Divider for collapsed state -->
        <div v-if="isSidebarCollapsed" class="w-10 h-[1px] bg-white/10"></div>
      </div>

      <!-- Navigation -->
      <nav class="flex-1 px-3 space-y-2 overflow-y-auto custom-scrollbar overflow-x-hidden">
        <router-link
          v-for="item in menuItems"
          :key="item.path"
          :to="item.path"
          :class="
            cn(
              'flex items-center rounded-2xl transition-all duration-200 group overflow-hidden',
              isSidebarCollapsed ? 'justify-center w-12 h-12 mx-auto' : 'px-4 py-3.5 gap-4',
              (route.path === item.path || (item.path === '/dashboard-pengepul/keranjang' && route.path === '/dashboard-pengepul/ringkasan-pembelian') || (item.path === '/dashboard-pengepul/pesanan-saya' && route.path.startsWith('/dashboard-pengepul/pesanan/')))
                ? 'bg-[#A86444] text-white shadow-lg'
                : 'text-white/70 hover:bg-white/10 hover:text-white'
            )
          "
        >
          <Icon :icon="item.icon" class="w-6 h-6 shrink-0" />

          <!-- Nav Label Reveal -->
          <div
            :class="cn('overflow-hidden transition-all duration-300 whitespace-nowrap', isSidebarCollapsed ? 'max-w-0 opacity-0' : 'max-w-xs opacity-100')"
          >
            <span class="font-semibold text-xs">{{ item.name }}</span>
          </div>
        </router-link>
      </nav>

      <!-- Logout -->
      <div :class="cn('p-4 border-t border-white/10 transition-all duration-300', isSidebarCollapsed ? 'flex justify-center px-0' : '')">
        <button
          @click="handleLogout"
          :class="
            cn(
              'flex items-center transition-all duration-300 group overflow-hidden',
              isSidebarCollapsed ? 'justify-center w-12 h-12 rounded-full hover:bg-red-500/20' : 'px-4 py-3.5 gap-4 w-full rounded-2xl text-white/70 hover:bg-red-500/20 hover:text-red-300'
            )
          "
        >
          <Icon icon="material-symbols:logout" class="w-6 h-6 rotate-180 shrink-0" />

          <!-- Logout Text Reveal -->
          <div
            :class="cn('overflow-hidden transition-all duration-300 whitespace-nowrap', isSidebarCollapsed ? 'max-w-0 opacity-0' : 'max-w-xs opacity-100')"
          >
            <span class="font-semibold text-xs">Logout</span>
          </div>
        </button>
      </div>
    </aside>

    <!-- Content Area -->
    <div class="flex-1 flex flex-col min-w-0 h-full">
      <!-- Header with Page Title (Green) -->
      <header class="bg-[#4A7043] text-white px-8 flex items-center sticky top-0 z-30 shadow-md h-20 shrink-0">
        <h1 class="text-xl lg:text-2xl font-bold tracking-wide uppercase">{{ title }}</h1>
      </header>

      <!-- Main Scroll Area -->
      <main class="flex-1 overflow-y-auto bg-[#F5F5F0] flex flex-col">
        <!-- Content Wrapper -->
        <div class="flex-1 flex justify-center py-10 px-4 lg:px-16">
          <div class="w-full max-w-[1650px]">
            <slot />
          </div>
        </div>

        <DashboardFooter />
      </main>
    </div>
  </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.1);
  border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: rgba(255, 255, 255, 0.2);
}
</style>
