<script setup>
import { ref, computed, onMounted, inject, watch } from "vue";
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
const isMobileMenuOpen = ref(false);

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
    { name: "Dashboard", path: "/dashboard-nasabah", icon: "material-symbols:home-outline" },
    { name: "Katalog Sampah", path: "/dashboard-nasabah/katalog", icon: "material-symbols:menu-book-outline" },
    { name: "Request Jemput/Setor", path: "/dashboard-nasabah/request-penjemputan", icon: "material-symbols:local-shipping-outline" },
    { name: "Penarikan Uang", path: "/dashboard-nasabah/request-penarikan", icon: "material-symbols:credit-card-outline" },
    { name: "Sampah Saya", path: "/dashboard-nasabah/sampah-saya", icon: "material-symbols:package-2-outline" },
    { name: "Riwayat Penarikan", path: "/dashboard-nasabah/penarikan-saya", icon: "material-symbols:account-balance-wallet-outline" },
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

// Notification Red Dot Logic
const menuUpdates = ref({});
const lastVisitedMenus = ref({});

const getUserId = () => {
  return user.value.admin_id || user.value.petugas_id || user.value.manager_id || user.value.pengepul_id || user.value.nasabah_id || user.value.id || 'guest';
};

const initLastVisited = () => {
  try {
    const stored = localStorage.getItem(`lastVisitedMenus_${role.value}_${getUserId()}`);
    if (stored) {
      lastVisitedMenus.value = JSON.parse(stored);
    }
  } catch (e) {
    console.error(e);
  }
};

// Initialize synchronously so it's ready before watch immediate
initLastVisited();

const saveLastVisited = () => {
  try {
    localStorage.setItem(`lastVisitedMenus_${role.value}_${getUserId()}`, JSON.stringify(lastVisitedMenus.value));
  } catch (e) {
    console.error(e);
  }
};

const fetchMenuUpdates = async () => {
  try {
    const token = sessionStorage.getItem('token');
    if (!token) return;
    const res = await axios.get(`${axios.defaults.baseURL}/api/menu-updates`, {
      headers: { Authorization: `Bearer ${token}` }
    });
    menuUpdates.value = res.data;
    
    // Automatically mark the current page as visited to catch updates while active
    const currentMenu = menuItems.value.find(item => item.path === route.path || route.path.startsWith(item.path + '/'));
    if (currentMenu) {
      markAsVisited(currentMenu.path);
    }
  } catch (e) {
    console.error('Failed to fetch menu updates', e);
  }
};

const hasUpdate = (path) => {
  // Never show red dot if the user is currently on this page
  if (route.path === path || route.path.startsWith(path + '/')) {
    return false;
  }

  const updateTimestamp = menuUpdates.value[path];
  const lastVisitedTimestamp = lastVisitedMenus.value[path];
  
  if (!updateTimestamp) return false;
  if (!lastVisitedTimestamp) return true;
  
  return updateTimestamp > lastVisitedTimestamp;
};

const markAsVisited = (path) => {
  const serverTime = menuUpdates.value[path] || 0;
  const localTime = Math.floor(Date.now() / 1000);
  const currentSaved = lastVisitedMenus.value[path] || 0;
  
  // Guarantee we never downgrade the timestamp
  lastVisitedMenus.value = {
    ...lastVisitedMenus.value,
    [path]: Math.max(serverTime, localTime, currentSaved)
  };
  saveLastVisited();
};

watch(() => route.path, (newPath) => {
  isMobileMenuOpen.value = false;
  const matchingMenu = menuItems.value.find(item => item.path === newPath || newPath.startsWith(item.path + '/'));
  if (matchingMenu) {
    markAsVisited(matchingMenu.path);
  }
}, { immediate: true });

onMounted(() => {
  fetchWebConfig();
  fetchMenuUpdates();
  setInterval(fetchMenuUpdates, 3 * 60 * 1000); // Poll every 3 minutes
});
</script>

<template>
  <div class="h-screen bg-[#F5F5F0] flex font-['Inter'] overflow-hidden">
    <!-- Sidebar (Desktop) -->
    <aside
      :class="
        cn(
          'h-full bg-[#4A7043] text-white flex flex-col relative z-50 shrink-0 transition-all duration-300 ease-in-out hidden lg:flex',
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
          @click="markAsVisited(item.path)"
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
          <div class="relative shrink-0">
            <Icon :icon="item.icon" class="w-6 h-6" />
            <div v-if="hasUpdate(item.path)" class="absolute -top-1 -right-1 w-2.5 h-2.5 bg-red-500 rounded-full shadow-sm animate-pulse border-2 border-[#4A7043] group-hover:border-white/10" :class="(route.path === item.path || (item.path === '/dashboard-pengepul/keranjang' && route.path === '/dashboard-pengepul/ringkasan-pembelian') || (item.path === '/dashboard-pengepul/pesanan-saya' && route.path.startsWith('/dashboard-pengepul/pesanan/'))) ? 'border-[#A86444]' : ''"></div>
          </div>

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

    <!-- Mobile Sidebar Drawer Overlay -->
    <div
      v-if="isMobileMenuOpen"
      @click="isMobileMenuOpen = false"
      class="fixed inset-0 bg-black/40 backdrop-blur-sm z-50 lg:hidden"
    ></div>

    <!-- Mobile Sidebar Drawer -->
    <aside
      :class="
        cn(
          'fixed inset-y-0 left-0 w-[80vw] max-w-[320px] bg-[#4A7043] text-white flex flex-col z-55 transition-transform duration-300 ease-in-out lg:hidden shadow-2xl',
          isMobileMenuOpen ? 'translate-x-0' : '-translate-x-full'
        )
      "
    >
      <!-- Mobile Close & Logo -->
      <div class="p-6 flex items-center justify-between">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 bg-white/10 rounded-xl flex items-center justify-center overflow-hidden shrink-0">
            <img v-if="webConfig.logo" :src="`${axios.defaults.baseURL}/storage/${webConfig.logo}`" class="w-full h-full object-contain p-1" alt="Logo" />
            <Icon v-else icon="material-symbols:recycling" class="w-7 h-7 text-[#4CAF50]" />
          </div>
          <div>
            <h2 class="text-sm font-black tracking-wide leading-none text-white">Bank Sampah</h2>
            <p class="text-[9px] text-white/60 font-bold uppercase tracking-wider mt-1">Aplikasi Bank Sampah</p>
          </div>
        </div>
        <button
          @click="isMobileMenuOpen = false"
          class="p-1.5 hover:bg-white/10 rounded-lg transition-colors cursor-pointer"
        >
          <Icon icon="material-symbols:close" class="w-6 h-6 text-white" />
        </button>
      </div>

      <!-- User Profile (Mobile) -->
      <div class="px-4 mb-6">
        <div class="bg-[#5C8555] rounded-[1.5rem] p-4 flex items-center gap-4 border border-white/5 shadow-md">
          <!-- User Avatar Icon with Name Initial -->
          <div class="w-12 h-12 rounded-full bg-[#A86444] flex items-center justify-center font-black text-white text-lg shrink-0 shadow-md">
            {{ user.nama?.charAt(0).toUpperCase() || user.username?.charAt(0).toUpperCase() || 'B' }}
          </div>
          <!-- User Details -->
          <div class="flex-1 min-w-0">
            <h3 class="font-bold text-sm text-white truncate leading-tight">
              {{ (role === 'nasabah' || role === 'pengepul') ? (user.nama || 'Budi Santoso') : (user.nama || user.name || 'Nama Lengkap') }}
            </h3>
            <p class="text-[10px] font-semibold text-white/70 mt-0.5 truncate">
              @{{ user.username || 'username' }}
            </p>
          </div>
        </div>
      </div>

      <!-- Navigation (Mobile) -->
      <nav class="flex-1 px-3 space-y-1.5 overflow-y-auto custom-scrollbar overflow-x-hidden">
        <template v-for="(item, index) in menuItems" :key="item.path">
          <!-- Divider line between Penarikan Uang (index 3) and Sampah Saya (index 4) -->
          <hr v-if="index === 4" class="border-white/10 my-3 mx-3" />
          
          <router-link
            :to="item.path"
            @click="isMobileMenuOpen = false"
            :class="
              cn(
                'flex items-center rounded-2xl px-4 py-3 gap-4 transition-all duration-200 group overflow-hidden',
                (route.path === item.path || (item.path === '/dashboard-pengepul/keranjang' && route.path === '/dashboard-pengepul/ringkasan-pembelian') || (item.path === '/dashboard-pengepul/pesanan-saya' && route.path.startsWith('/dashboard-pengepul/pesanan/')))
                  ? 'bg-[#A86444] text-white shadow-lg font-bold'
                  : 'text-white/80 hover:bg-white/10 hover:text-white'
              )
            "
          >
            <Icon :icon="item.icon" class="w-5.5 h-5.5 shrink-0" />
            <span class="font-bold text-xs">{{ item.name }}</span>
          </router-link>
        </template>
      </nav>

      <!-- Logout (Mobile) -->
      <div class="p-4 border-t border-white/10">
        <button
          @click="handleLogout"
          class="flex items-center px-4 py-3 gap-4 w-full rounded-2xl text-white/80 hover:bg-red-500/25 hover:text-white transition-all duration-200 font-bold"
        >
          <Icon icon="material-symbols:logout" class="w-5.5 h-5.5 rotate-180 shrink-0" />
          <span class="font-bold text-xs">Keluar</span>
        </button>
      </div>
    </aside>

    <!-- Content Area -->
    <div class="flex-1 flex flex-col min-w-0 h-full">
      <!-- Header with Page Title (Responsive) -->
      <header class="bg-[#F5F5F0] lg:bg-[#4A7043] text-[#4A7043] lg:text-white flex items-center sticky top-0 z-30 h-16 lg:h-20 shrink-0 border-b border-stone-200/50 lg:border-none shadow-sm lg:shadow-md">
        <div class="w-full flex items-center justify-between px-4 lg:px-16">
          <div class="flex items-center gap-3">
            <!-- Hamburger Menu Button (Mobile Only) -->
            <button
              @click="isMobileMenuOpen = true"
              class="lg:hidden p-2 hover:bg-stone-200/50 rounded-lg transition-colors shrink-0 cursor-pointer"
            >
              <Icon icon="material-symbols:menu" class="w-6 h-6 text-[#4A7043]" />
            </button>
            <h1 class="text-lg lg:text-2xl font-bold tracking-wide uppercase lg:normal-case">{{ title }}</h1>
          </div>

          <!-- Profile Button (Top Right Header) -->
          <div class="flex items-center">
            <button
              @click="handleProfileClick"
              class="p-2 hover:bg-stone-200/50 lg:hover:bg-white/10 rounded-full transition-all shrink-0 cursor-pointer text-[#4A7043] lg:text-white flex items-center justify-center"
              title="Profil Saya"
            >
              <Icon icon="material-symbols:account-circle-outline" class="w-6 h-6 lg:w-7 h-7" />
            </button>
          </div>
        </div>
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
