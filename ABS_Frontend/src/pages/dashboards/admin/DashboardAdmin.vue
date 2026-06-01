<script setup>
import { ref, onMounted, computed } from "vue";
import { Icon } from "@iconify/vue";
import axios from "axios";
import DashboardLayout from "@/layouts/DashboardLayout.vue";
import { checkRole } from "@/utils";

// Security check
checkRole("admin");

const stats = ref({
  total_petugas: 0,
  nasabah_aktif: 0,
  total_pengepul: 0,
  total_gudang: 0,
});

const activities = ref([]);
const isLoading = ref(true);

const fetchDashboardData = async () => {
  try {
    isLoading.value = true;
    const token = sessionStorage.getItem("token");
    const headers = { Authorization: `Bearer ${token}` };
    const response = await axios.get("/api/admin/dashboard-stats", { headers });
    stats.value = response.data.stats;
    activities.value = response.data.activities;
  } catch (error) {
    console.error("Failed to fetch dashboard stats:", error);
  } finally {
    isLoading.value = false;
  }
};

const totalUsers = computed(() => {
  return stats.value.total_petugas + stats.value.nasabah_aktif + stats.value.total_pengepul;
});

const getPercentage = (val) => {
  if (!totalUsers.value) return 0;
  return Math.round((val / totalUsers.value) * 100);
};

const getActivityIcon = (type) => {
  const icons = {
    petugas: "material-symbols:group-outline",
    nasabah: "material-symbols:person-outline",
    gudang: "material-symbols:home-work-outline",
    sampah: "material-symbols:delete-outline",
    transaksi: "material-symbols:account-balance-wallet-outline",
    laporan: "material-symbols:payments-outline"
  };
  return icons[type] || "material-symbols:notifications-outline";
};

const getActivityBg = (type) => {
  const bgs = {
    petugas: "bg-[#4A7043]/10 text-[#4A7043]",
    nasabah: "bg-[#5FA09B]/10 text-[#5FA09B]",
    gudang: "bg-[#A86444]/10 text-[#A86444]",
    sampah: "bg-[#707070]/10 text-[#707070]",
    transaksi: "bg-emerald-500/10 text-emerald-600",
    laporan: "bg-orange-500/10 text-orange-600"
  };
  return bgs[type] || "bg-stone-100 text-stone-600";
};

onMounted(() => {
  fetchDashboardData();
});
</script>

<template>
  <DashboardLayout title="Dashboard Admin">
    <div class="space-y-6 animate-in fade-in duration-700 font-['Inter'] pb-10">
      
      <!-- Top Row: Compact Stats Cards (Full Width) -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        
        <!-- Total Petugas -->
        <div class="bg-white rounded-2xl p-5 border border-stone-100 shadow-sm flex items-center gap-4 transition-all hover:shadow-md">
          <div class="w-12 h-12 rounded-xl bg-[#4A7043]/10 text-[#4A7043] flex items-center justify-center shrink-0">
            <Icon icon="material-symbols:group-outline" class="w-6.5 h-6.5" />
          </div>
          <div>
            <p class="text-xs font-bold text-stone-400 uppercase tracking-wider leading-none">Total Petugas</p>
            <h4 class="text-xl font-extrabold text-stone-800 mt-1.5 leading-none">{{ stats.total_petugas }}</h4>
          </div>
        </div>

        <!-- Nasabah Aktif -->
        <div class="bg-white rounded-2xl p-5 border border-stone-100 shadow-sm flex items-center gap-4 transition-all hover:shadow-md">
          <div class="w-12 h-12 rounded-xl bg-[#5FA09B]/10 text-[#5FA09B] flex items-center justify-center shrink-0">
            <Icon icon="material-symbols:person-outline" class="w-6.5 h-6.5" />
          </div>
          <div>
            <p class="text-xs font-bold text-stone-400 uppercase tracking-wider leading-none">Nasabah Aktif</p>
            <h4 class="text-xl font-extrabold text-stone-800 mt-1.5 leading-none">{{ stats.nasabah_aktif }}</h4>
          </div>
        </div>

        <!-- Total Pengepul -->
        <div class="bg-white rounded-2xl p-5 border border-stone-100 shadow-sm flex items-center gap-4 transition-all hover:shadow-md">
          <div class="w-12 h-12 rounded-xl bg-[#A86444]/10 text-[#A86444] flex items-center justify-center shrink-0">
            <Icon icon="material-symbols:group-outline" class="w-6.5 h-6.5" />
          </div>
          <div>
            <p class="text-xs font-bold text-stone-400 uppercase tracking-wider leading-none">Total Pengepul</p>
            <h4 class="text-xl font-extrabold text-stone-800 mt-1.5 leading-none">{{ stats.total_pengepul }}</h4>
          </div>
        </div>

        <!-- Total Gudang -->
        <div class="bg-white rounded-2xl p-5 border border-stone-100 shadow-sm flex items-center gap-4 transition-all hover:shadow-md">
          <div class="w-12 h-12 rounded-xl bg-[#707070]/10 text-[#707070] flex items-center justify-center shrink-0">
            <Icon icon="material-symbols:home-work-outline" class="w-6.5 h-6.5" />
          </div>
          <div>
            <p class="text-xs font-bold text-stone-400 uppercase tracking-wider leading-none">Total Gudang</p>
            <h4 class="text-xl font-extrabold text-stone-800 mt-1.5 leading-none">{{ stats.total_gudang }}</h4>
          </div>
        </div>
        
      </div>

      <!-- Bottom Row: Recent Activities (Left, Spans 2 cols) & Sidebar (Right, Spans 1 col) -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left Side: Recent Activity (lg:col-span-2) -->
        <div class="lg:col-span-2 bg-white rounded-[1.5rem] p-6 border border-stone-100 shadow-sm flex flex-col justify-between">
          <div>
            <div class="flex justify-between items-center mb-5 pb-2.5 border-b border-stone-50">
              <div>
                <h3 class="text-base font-extrabold text-stone-800">Aktivitas Terkini</h3>
                <p class="text-xs text-stone-400 mt-0.5">Log aktivitas terintegrasi sistem terbaru</p>
              </div>
              <Icon icon="material-symbols:history" class="w-6 h-6 text-stone-400" />
            </div>

            <div v-if="activities.length > 0" class="space-y-3">
              <div v-for="activity in activities" :key="activity.id" 
                class="flex items-center justify-between p-4 rounded-xl border border-stone-50 bg-[#F5F5F0]/30 hover:bg-[#F5F5F0]/60 transition-all">
                <div class="flex items-center gap-4">
                  <!-- Compact Icon Badge -->
                  <div class="w-9 h-9 rounded-lg flex items-center justify-center shrink-0 shadow-sm" :class="getActivityBg(activity.type)">
                    <Icon :icon="getActivityIcon(activity.type)" class="w-5.5 h-5.5" />
                  </div>
                  
                  <!-- Compact Info -->
                  <div>
                    <h4 class="text-sm font-bold text-stone-800 leading-tight">{{ activity.title }}</h4>
                    <p class="text-xs text-stone-400 mt-1">{{ activity.description }}</p>
                  </div>
                </div>
                <!-- Time tag -->
                <span class="text-[11px] font-bold text-stone-500 px-2.5 py-0.5 bg-stone-100 rounded-md shrink-0">{{ activity.time }}</span>
              </div>
            </div>

            <!-- Empty State -->
            <div v-else class="py-10 border border-dashed border-stone-100 rounded-2xl flex flex-col items-center justify-center text-stone-300 gap-2">
              <Icon icon="material-symbols:history" class="w-8 h-8 opacity-20" />
              <p class="font-bold text-xs italic tracking-wide">Belum ada aktivitas hari ini.</p>
            </div>
          </div>
        </div>

        <!-- Right Side: Sidebar Quick Actions & User Composition -->
        <div class="space-y-6">
          
          <!-- Quick Action Panel -->
          <div class="bg-white rounded-[1.5rem] p-6 border border-stone-100 shadow-sm flex flex-col">
            <div class="mb-5 pb-2.5 border-b border-stone-50">
              <h3 class="text-base font-extrabold text-stone-800">Akses Cepat Modul</h3>
              <p class="text-xs text-stone-400 mt-0.5">Pintasan navigasi kelola aplikasi</p>
            </div>
            
            <div class="grid grid-cols-2 gap-3">
              <router-link to="/dashboard-admin/kelola-users" 
                class="p-3 bg-stone-50 hover:bg-[#4A7043]/10 border border-stone-100 hover:border-[#4A7043]/30 rounded-xl flex flex-col items-center justify-center text-center gap-1.5 transition-all text-stone-700 hover:text-[#4A7043] group cursor-pointer">
                <Icon icon="material-symbols:person-outline" class="w-6 h-6 text-stone-400 group-hover:text-[#4A7043] transition-colors" />
                <span class="text-xs font-bold">Kelola Akun</span>
              </router-link>

              <router-link to="/dashboard-admin/kelola-gudang" 
                class="p-3 bg-stone-50 hover:bg-[#5FA09B]/10 border border-stone-100 hover:border-[#5FA09B]/30 rounded-xl flex flex-col items-center justify-center text-center gap-1.5 transition-all text-stone-700 hover:text-[#5FA09B] group cursor-pointer">
                <Icon icon="material-symbols:home-work-outline" class="w-6 h-6 text-stone-400 group-hover:text-[#5FA09B] transition-colors" />
                <span class="text-xs font-bold">Kelola Gudang</span>
              </router-link>

              <router-link to="/dashboard-admin/kelola-sampah" 
                class="p-3 bg-stone-50 hover:bg-[#A86444]/10 border border-stone-100 hover:border-[#A86444]/30 rounded-xl flex flex-col items-center justify-center text-center gap-1.5 transition-all text-stone-700 hover:text-[#A86444] group cursor-pointer">
                <Icon icon="material-symbols:delete-outline" class="w-6 h-6 text-stone-400 group-hover:text-[#A86444] transition-colors" />
                <span class="text-xs font-bold">Kelola Sampah</span>
              </router-link>

              <router-link to="/dashboard-admin/verifikasi-pengepul" 
                class="p-3 bg-stone-50 hover:bg-orange-500/10 border border-stone-100 hover:border-orange-500/30 rounded-xl flex flex-col items-center justify-center text-center gap-1.5 transition-all text-stone-700 hover:text-orange-600 group cursor-pointer">
                <Icon icon="material-symbols:verified-user-outline-rounded" class="w-6 h-6 text-stone-400 group-hover:text-orange-500 transition-colors" />
                <span class="text-xs font-bold">Verif Pengepul</span>
              </router-link>

              <router-link to="/dashboard-admin/konfigurasi-web" 
                class="p-3 bg-stone-50 hover:bg-[#707070]/10 border border-stone-100 hover:border-[#707070]/30 rounded-xl flex flex-col items-center justify-center text-center gap-1.5 transition-all text-stone-700 hover:text-stone-900 group cursor-pointer">
                <Icon icon="material-symbols:settings-outline" class="w-6 h-6 text-stone-400 group-hover:text-stone-800 transition-colors" />
                <span class="text-xs font-bold">Konfig Web</span>
              </router-link>

              <router-link to="/dashboard-admin/deadline-pembayaran" 
                class="p-3 bg-stone-50 hover:bg-red-500/10 border border-stone-100 hover:border-red-500/30 rounded-xl flex flex-col items-center justify-center text-center gap-1.5 transition-all text-stone-700 hover:text-red-600 group cursor-pointer">
                <Icon icon="material-symbols:calendar-clock-outline" class="w-6 h-6 text-stone-400 group-hover:text-red-500 transition-colors" />
                <span class="text-xs font-bold">Set Deadline</span>
              </router-link>
            </div>
          </div>

          <!-- User Distribution panel -->
          <div class="bg-white rounded-[1.5rem] p-6 border border-stone-100 shadow-sm flex flex-col">
            <div class="mb-5 pb-2.5 border-b border-stone-50">
              <h3 class="text-base font-extrabold text-stone-800">Komposisi Pengguna</h3>
              <p class="text-xs text-stone-400 mt-0.5">Proporsi pembagian peran dalam sistem</p>
            </div>

            <div class="space-y-4">
              <!-- Nasabah -->
              <div class="space-y-1.5">
                <div class="flex justify-between items-center text-sm">
                  <span class="text-stone-600 font-bold">Nasabah</span>
                  <span class="font-extrabold text-stone-700">{{ stats.nasabah_aktif }} ({{ getPercentage(stats.nasabah_aktif) }}%)</span>
                </div>
                <div class="w-full bg-stone-100 rounded-full h-1.5 overflow-hidden">
                  <div class="h-1.5 rounded-full bg-[#5FA09B]" :style="{ width: `${getPercentage(stats.nasabah_aktif)}%` }"></div>
                </div>
              </div>

              <!-- Petugas -->
              <div class="space-y-1.5">
                <div class="flex justify-between items-center text-sm">
                  <span class="text-stone-600 font-bold">Petugas</span>
                  <span class="font-extrabold text-stone-700">{{ stats.total_petugas }} ({{ getPercentage(stats.total_petugas) }}%)</span>
                </div>
                <div class="w-full bg-stone-100 rounded-full h-1.5 overflow-hidden">
                  <div class="h-1.5 rounded-full bg-[#4A7043]" :style="{ width: `${getPercentage(stats.total_petugas)}%` }"></div>
                </div>
              </div>

              <!-- Pengepul -->
              <div class="space-y-1.5">
                <div class="flex justify-between items-center text-sm">
                  <span class="text-stone-600 font-bold">Pengepul</span>
                  <span class="font-extrabold text-stone-700">{{ stats.total_pengepul }} ({{ getPercentage(stats.total_pengepul) }}%)</span>
                </div>
                <div class="w-full bg-stone-100 rounded-full h-1.5 overflow-hidden">
                  <div class="h-1.5 rounded-full bg-[#A86444]" :style="{ width: `${getPercentage(stats.total_pengepul)}%` }"></div>
                </div>
              </div>
            </div>
          </div>
          
        </div>
        
      </div>
    </div>
  </DashboardLayout>
</template>

<style scoped>
.animate-in {
  animation-duration: 0.7s;
  animation-fill-mode: both;
}
.fade-in {
  animation-name: fadeIn;
}
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
