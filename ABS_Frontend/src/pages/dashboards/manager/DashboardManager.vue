<script setup>
import { ref, onMounted, computed } from "vue";
import { Icon } from "@iconify/vue";
import DashboardLayout from "@/layouts/DashboardLayout.vue";
import { checkRole } from "@/utils";
import axios from "axios";
import VueApexCharts from "vue3-apexcharts";

const apexchart = VueApexCharts;

// Security check
checkRole("manager");

const user = computed(() => {
  try {
    return JSON.parse(sessionStorage.getItem("user") || "{}");
  } catch (e) {
    return {};
  }
});

const stats = ref({
  total_petugas: 0,
  total_sampah: 0,
  nasabah_verifikasi: 0,
  transaksi_bulan_ini: 0,
});

const activities = ref([]);
const loading = ref(true);

const fetchDashboardData = async () => {
  try {
    const token = sessionStorage.getItem("token");
    const response = await axios.get("http://localhost:8000/api/manager/dashboard-stats", {
      headers: {
        Authorization: `Bearer ${token}`,
      },
    });
    stats.value = response.data.stats;
    activities.value = response.data.activities;
  } catch (error) {
    console.error("Error fetching dashboard data:", error);
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchDashboardData();
});

// Chart Options
const trashChartOptions = {
  chart: {
    type: 'line',
    toolbar: { show: false },
    zoom: { enabled: false }
  },
  colors: ['#5FA09B', '#8C5230', '#3D5A35'],
  stroke: { curve: 'smooth', width: 3 },
  xaxis: {
    categories: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'],
    labels: { style: { colors: '#A8A29E', fontWeight: 600 } }
  },
  yaxis: {
    labels: { style: { colors: '#A8A29E', fontWeight: 600 } }
  },
  grid: { borderColor: '#F5F5F4', strokeDashArray: 4 },
  legend: { position: 'bottom', fontWeight: 600 },
  markers: { size: 4 }
};

const trashChartSeries = [
  { name: 'Organik', data: [350, 420, 500, 480, 550, 600] },
  { name: 'Anorganik', data: [200, 250, 300, 280, 320, 350] },
  { name: 'Total', data: [550, 670, 800, 760, 870, 950] }
];

const transactionChartOptions = {
  chart: {
    type: 'bar',
    toolbar: { show: false }
  },
  colors: ['#5FA09B'],
  plotOptions: {
    bar: {
      borderRadius: 8,
      columnWidth: '50%',
    }
  },
  dataLabels: { enabled: false },
  xaxis: {
    categories: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'],
    labels: { style: { colors: '#A8A29E', fontWeight: 600 } }
  },
  yaxis: {
    labels: { style: { colors: '#A8A29E', fontWeight: 600 } }
  },
  grid: { borderColor: '#F5F5F4', strokeDashArray: 4 }
};

const transactionChartSeries = [
  { name: 'Transaksi', data: [45, 52, 60, 55, 65, 70] }
];

const getIconBg = (type) => {
  switch (type) {
    case 'nasabah': return 'bg-green-100 text-green-600';
    case 'transaksi': return 'bg-blue-100 text-blue-600';
    case 'gudang': return 'bg-orange-100 text-orange-600';
    case 'laporan': return 'bg-purple-100 text-purple-600';
    default: return 'bg-stone-100 text-stone-600';
  }
};

const getIcon = (type) => {
  switch (type) {
    case 'nasabah': return 'material-symbols:person-check-outline';
    case 'transaksi': return 'material-symbols:receipt-long-outline';
    case 'gudang': return 'material-symbols:warehouse-outline';
    case 'laporan': return 'material-symbols:description-outline';
    default: return 'material-symbols:notifications-outline';
  }
};

</script>

<template>
  <DashboardLayout title="Dashboard">
    <div class="space-y-8 animate-in fade-in duration-700 pb-10">
      
      <!-- Header Section -->
      <div class="flex justify-between items-center">
        <h2 class="text-2xl font-black text-stone-800">Ringkasan Dashboard</h2>
        <div class="flex items-center gap-2 bg-white px-4 py-2 rounded-2xl shadow-sm border border-stone-100">
          <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
          <span class="text-xs font-bold text-stone-500 uppercase tracking-wider">Live System</span>
        </div>
      </div>

      <!-- Stats Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-6">
        <!-- Total Petugas -->
        <div class="bg-[#3D5A35] rounded-[2.5rem] p-8 text-white shadow-xl shadow-green-900/10 flex flex-col justify-between h-56 relative overflow-hidden group">
          <div class="absolute top-0 right-0 w-32 h-32 bg-white/5 rounded-full -mr-10 -mt-10 blur-2xl group-hover:scale-150 transition-transform duration-700"></div>
          <div class="flex justify-between items-start relative z-10">
            <div class="p-3 bg-white/10 rounded-2xl backdrop-blur-md">
              <Icon icon="material-symbols:group-outline" class="w-8 h-8" />
            </div>
          </div>
          <div class="relative z-10">
            <h3 class="text-5xl font-black mb-1">{{ stats.total_petugas }}</h3>
            <p class="text-sm font-bold text-white/70 uppercase tracking-widest">Total Petugas Aktif</p>
          </div>
        </div>

        <!-- Total Sampah -->
        <div class="bg-[#5FA09B] rounded-[2.5rem] p-8 text-white shadow-xl shadow-teal-900/10 flex flex-col justify-between h-56 relative overflow-hidden group">
          <div class="absolute top-0 right-0 w-32 h-32 bg-white/5 rounded-full -mr-10 -mt-10 blur-2xl group-hover:scale-150 transition-transform duration-700"></div>
          <div class="flex justify-between items-start relative z-10">
            <div class="p-3 bg-white/10 rounded-2xl backdrop-blur-md">
              <Icon icon="material-symbols:inventory-2-outline" class="w-8 h-8" />
            </div>
          </div>
          <div class="relative z-10">
            <div class="flex items-baseline gap-2">
              <h3 class="text-5xl font-black mb-1">{{ typeof stats.total_sampah === 'number' ? stats.total_sampah.toLocaleString('id-ID') : stats.total_sampah }}</h3>
              <span class="text-2xl font-bold opacity-60">Kg</span>
            </div>
            <p class="text-sm font-bold text-white/70 uppercase tracking-widest">Total Sampah (Kg)</p>
          </div>
        </div>

        <!-- Nasabah Verifikasi -->
        <div class="bg-[#8C5230] rounded-[2.5rem] p-8 text-white shadow-xl shadow-orange-900/10 flex flex-col justify-between h-56 relative overflow-hidden group">
          <div class="absolute top-0 right-0 w-32 h-32 bg-white/5 rounded-full -mr-10 -mt-10 blur-2xl group-hover:scale-150 transition-transform duration-700"></div>
          <div class="flex justify-between items-start relative z-10">
            <div class="p-3 bg-white/10 rounded-2xl backdrop-blur-md">
              <Icon icon="material-symbols:trending-up" class="w-8 h-8" />
            </div>
          </div>
          <div class="relative z-10">
            <h3 class="text-5xl font-black mb-1">{{ stats.nasabah_verifikasi }}</h3>
            <p class="text-sm font-bold text-white/70 uppercase tracking-widest">Nasabah Terverifikasi</p>
          </div>
        </div>

        <!-- Transaksi Bulan Ini -->
        <div class="bg-[#6B6B6B] rounded-[2.5rem] p-8 text-white shadow-xl shadow-stone-900/10 flex flex-col justify-between h-56 relative overflow-hidden group">
          <div class="absolute top-0 right-0 w-32 h-32 bg-white/5 rounded-full -mr-10 -mt-10 blur-2xl group-hover:scale-150 transition-transform duration-700"></div>
          <div class="flex justify-between items-start relative z-10">
            <div class="p-3 bg-white/10 rounded-2xl backdrop-blur-md">
              <Icon icon="material-symbols:attach-money" class="w-8 h-8" />
            </div>
          </div>
          <div class="relative z-10">
            <h3 class="text-5xl font-black mb-1">{{ stats.transaksi_bulan_ini }}</h3>
            <p class="text-sm font-bold text-white/70 uppercase tracking-widest">Transaksi Bulan Ini</p>
          </div>
        </div>
      </div>

      <!-- Charts Section -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Trash Statistics -->
        <div class="bg-white rounded-[2.5rem] p-8 shadow-sm border border-stone-100">
          <div class="flex items-center gap-3 mb-8">
            <div class="w-1.5 h-6 bg-[#5FA09B] rounded-full"></div>
            <h3 class="text-xl font-black text-stone-800">Statistik Sampah (6 Bulan)</h3>
          </div>
          <apexchart 
            type="line" 
            height="300" 
            :options="trashChartOptions" 
            :series="trashChartSeries" 
          />
          <p class="text-[10px] text-stone-400 mt-4 font-medium italic">* Data statistik di atas masih bersifat statis</p>
        </div>

        <!-- Transaction Statistics -->
        <div class="bg-white rounded-[2.5rem] p-8 shadow-sm border border-stone-100">
          <div class="flex items-center gap-3 mb-8">
            <div class="w-1.5 h-6 bg-[#3D5A35] rounded-full"></div>
            <h3 class="text-xl font-black text-stone-800">Statistik Transaksi</h3>
          </div>
          <apexchart 
            type="bar" 
            height="300" 
            :options="transactionChartOptions" 
            :series="transactionChartSeries" 
          />
          <p class="text-[10px] text-stone-400 mt-4 font-medium italic">* Data statistik di atas masih bersifat statis</p>
        </div>
      </div>

      <!-- Recent Activity -->
      <div class="bg-white rounded-[2.5rem] p-8 shadow-sm border border-stone-100">
        <div class="flex justify-between items-center mb-8">
          <div class="flex items-center gap-3">
            <div class="w-1.5 h-6 bg-stone-800 rounded-full"></div>
            <h3 class="text-xl font-black text-stone-800">Aktivitas Terkini</h3>
          </div>
          <button class="text-xs font-bold text-[#4A7043] uppercase tracking-widest hover:underline transition-all">Lihat Semua</button>
        </div>

        <div class="space-y-4">
          <div v-for="activity in activities" :key="activity.id" 
               class="bg-stone-50 rounded-2xl p-5 flex items-center justify-between group hover:bg-stone-100 transition-colors border border-transparent hover:border-stone-200">
            <div class="flex items-center gap-5">
              <div :class="['w-12 h-12 rounded-xl flex items-center justify-center transition-transform group-hover:scale-110 shadow-sm', getIconBg(activity.type)]">
                <Icon :icon="getIcon(activity.type)" class="w-6 h-6" />
              </div>
              <div>
                <h4 class="font-black text-stone-800 text-sm leading-tight">{{ activity.title }}</h4>
                <p class="text-xs font-bold text-stone-400 mt-0.5">{{ activity.description }}</p>
              </div>
            </div>
            <div class="text-right">
              <p class="text-xs font-black text-stone-800">{{ activity.time }}</p>
              <p class="text-[10px] font-bold text-stone-400 uppercase mt-0.5">{{ activity.date }}</p>
            </div>
          </div>

          <div v-if="activities.length === 0" class="py-12 flex flex-col items-center justify-center text-center opacity-40">
            <Icon icon="material-symbols:history" class="w-12 h-12 text-stone-300 mb-2" />
            <p class="text-stone-500 font-bold text-sm">Belum ada aktivitas terbaru.</p>
          </div>
        </div>
      </div>

    </div>
  </DashboardLayout>
</template>

<style scoped>
.animate-in {
  animation: fadeIn 0.7s ease-out both;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(15px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
