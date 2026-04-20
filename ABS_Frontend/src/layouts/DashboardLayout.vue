<script setup>
import { ref, computed } from "vue";
import { useRouter, useRoute } from "vue-router";
import { Icon } from "@iconify/vue";
import { cn } from "@/lib/utils";
import DashboardFooter from "@/components/dashboard/DashboardFooter.vue";

const props = defineProps({
  title: { type: String, default: "Dashboard" }
});

const router = useRouter();
const route = useRoute();

const isSidebarCollapsed = ref(false);

const toggleSidebar = () => {
  isSidebarCollapsed.value = !isSidebarCollapsed.value;
};

const menuItems = [
  { name: "Dashboard", path: "/dashboard-nasabah", icon: "material-symbols:dashboard-outline" },
  { name: "Katalog Sampah", path: "/dashboard-nasabah/katalog", icon: "material-symbols:book-outline" },
  { name: "Request Jemput/Setor", path: "/dashboard-nasabah/request-penjemputan", icon: "material-symbols:local-shipping-outline" },
  { name: "Penarikan Uang", path: "/dashboard-nasabah/request-penarikan", icon: "material-symbols:account-balance-wallet-outline" },
  { name: "Sampah Saya", path: "/dashboard-nasabah/sampah-saya", icon: "material-symbols:delete-outline" },
  { name: "Riwayat Penarikan", path: "/dashboard-nasabah/riwayatpenjemputan", icon: "material-symbols:history" },
];

const handleLogout = () => {
  sessionStorage.clear();
  router.push("/login");
};

const user = computed(() => {
  try {
    return JSON.parse(sessionStorage.getItem("user") || "{}");
  } catch (e) {
    return {};
  }
});
</script>

<template>
  <div class="h-screen bg-[#F5F5F0] flex font-['Inter'] overflow-hidden">
    <!-- Sidebar -->
    <aside
      :class="
        cn(
          'h-full bg-[#4A7043] text-white flex flex-col relative z-50 shrink-0 transition-all duration-300 ease-in-out',
          isSidebarCollapsed ? 'w-20' : 'w-72'
        )
      "
    >
      <!-- Logo Section with Toggle -->
      <div :class="cn('p-6 flex items-center transition-all duration-300', isSidebarCollapsed ? 'justify-center px-0' : 'justify-start gap-4')">
        <button 
          @click="toggleSidebar" 
          class="p-2 hover:bg-white/10 rounded-lg transition-colors shrink-0"
        >
          <Icon icon="material-symbols:menu" class="w-7 h-7" />
        </button>
        
        <!-- Logo Text Reveal -->
        <div 
          :class="cn('flex items-center gap-3 overflow-hidden transition-all duration-300', isSidebarCollapsed ? 'max-w-0 opacity-0' : 'max-w-xs opacity-100')"
        >
          <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center font-bold text-sm shrink-0">LOGO</div>
          <div class="flex flex-col whitespace-nowrap">
            <h1 class="font-bold text-sm leading-none uppercase tracking-widest">LOGO</h1>
            <p class="text-[8px] text-white/60">Aplikasi Bank Sampah</p>
          </div>
        </div>
      </div>

      <!-- User Profile (Sidebar) -->
      <div :class="cn('px-4 mb-8 transition-all duration-300', isSidebarCollapsed ? 'flex justify-center px-0' : '')">
        <button 
          @click="() => {}"
          :class="
            cn(
              'bg-white/10 hover:bg-white/15 rounded-3xl p-3 flex items-center transition-all duration-300 overflow-hidden',
              isSidebarCollapsed ? 'w-12 h-12 justify-center rounded-full p-0' : 'w-full gap-3 px-4'
            )
          "
        >
          <div :class="cn('rounded-full bg-[#8BA783] flex items-center justify-center font-bold border-2 border-white/20 shrink-0 transition-all duration-300', isSidebarCollapsed ? 'w-10 h-10 text-sm' : 'w-12 h-12 text-base')">
            {{ user.name?.charAt(0) || 'U' }}
          </div>
          
          <!-- Profile Text Reveal -->
          <div 
            :class="cn('overflow-hidden transition-all duration-300 whitespace-nowrap', isSidebarCollapsed ? 'max-w-0 opacity-0' : 'max-w-xs opacity-100')"
          >
            <p class="font-bold truncate text-sm leading-tight">{{ user.name || 'Nama User' }}</p>
            <p class="text-[10px] text-white/60 truncate">{{ user.username || 'Username' }}</p>
          </div>
        </button>
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
              route.path === item.path
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
            <span class="font-semibold text-xs">Keluar</span>
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
        <div class="flex-1 flex justify-center py-10 px-6 lg:px-10">
          <div class="w-full max-w-5xl">
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
