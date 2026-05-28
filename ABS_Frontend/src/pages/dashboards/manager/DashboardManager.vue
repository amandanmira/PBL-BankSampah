<script setup>
import { ref, onMounted } from "vue";
import { Icon } from "@iconify/vue";
import DashboardLayout from "@/layouts/DashboardLayout.vue";
import VueApexCharts from "vue3-apexcharts";
import axios from "axios";

const apexchart = VueApexCharts;

// Reactive data based on the image and API
const topStats = ref([
  {
    id: "petugas",
    title: "Total Petugas Aktif",
    value: "12",
    increase: "+2 bulan ini",
    icon: "material-symbols:group-outline",
    bgClass: "bg-[#4A7043]",
    iconBgClass: "bg-white/20",
    increaseClass: "bg-white/20 text-white",
  },
  {
    id: "sampah",
    title: "Total Sampah (Kg)",
    value: "1,280",
    increase: "+12.4%",
    icon: "material-symbols:recycling",
    bgClass: "bg-[#5FA09B]",
    iconBgClass: "bg-white/20",
    increaseClass: "bg-white/20 text-white",
  },
  {
    id: "nasabah",
    title: "Nasabah Terverifikasi",
    value: "156",
    increase: "+8 minggu ini",
    icon: "material-symbols:person-outline",
    bgClass: "bg-[#A86444]",
    iconBgClass: "bg-white/20",
    increaseClass: "bg-white/20 text-white",
  },
  {
    id: "transaksi",
    title: "Transaksi Bulan Ini",
    value: "70",
    increase: "-3.1%",
    icon: "material-symbols:receipt-long-outline",
    bgClass: "bg-[#7A7A7A]",
    iconBgClass: "bg-white/20",
    increaseClass: "bg-white/20 text-white",
  },
  {
    id: "gudang",
    title: "Total Gudang Aktif",
    value: "4",
    increase: "4 dari 4 online",
    icon: "material-symbols:warehouse-outline",
    bgClass: "bg-[#3D5A35]",
    iconBgClass: "bg-white/20",
    increaseClass: "bg-white/20 text-white",
  },
]);

const fetchDashboardData = async () => {
  isLoadingStats.value = true;
  try {
    const token = sessionStorage.getItem('token');
    const response = await axios.get("http://localhost:8000/api/manager/dashboard-stats", {
      headers: {
        'Authorization': `Bearer ${token}`
      }
    });

    const data = response.data.stats;

    // Fungsi untuk update nilai stat spesifik jika nilainya tidak "X" dan terdefinisi
    const updateStat = (id, newValue, increaseValue) => {
      const stat = topStats.value.find(s => s.id === id);
      if (stat && newValue !== "X" && newValue !== undefined) {
        stat.value = typeof newValue === 'number' ? newValue.toLocaleString('id-ID') : newValue;
        if (increaseValue !== undefined) {
          stat.increase = increaseValue;
        }
      }
    };

    updateStat('petugas', data.total_petugas, data.petugas_increase);
    updateStat('sampah', data.total_sampah, data.sampah_increase);
    updateStat('nasabah', data.nasabah_verifikasi, data.nasabah_increase);
    updateStat('transaksi', data.transaksi_bulan_ini, data.transaksi_increase);
    updateStat('gudang', data.total_gudang, data.gudang_increase);

  } catch (error) {
    console.error("Error fetching dashboard data:", error);
  } finally {
    isLoadingStats.value = false;
  }
};

const selectedPeriod = ref('6 Bulan');
const periods = ['1 Bulan', '3 Bulan', '6 Bulan'];
const totalSampahPeriode = ref(0);
const isLoadingStats = ref(true);
const isLoadingCharts = ref(true);

const fetchChartsData = async () => {
  isLoadingCharts.value = true;
  try {
    const token = sessionStorage.getItem('token');
    const response = await axios.get(`http://localhost:8000/api/manager/dashboard-charts?period=${selectedPeriod.value}`, {
      headers: {
        'Authorization': `Bearer ${token}`
      }
    });

    const data = response.data;

    trendChartSeries.value = data.trendSeries;
    trendChartOptions.value = {
        ...trendChartOptions.value,
        xaxis: {
            ...trendChartOptions.value.xaxis,
            categories: data.trendCategories
        }
    };

    growthChartSeries.value = data.growthSeries;
    growthChartOptions.value = {
        ...growthChartOptions.value,
        xaxis: {
            ...growthChartOptions.value.xaxis,
            categories: data.trendCategories
        }
    };

    let totalTrend = 0;
    data.trendSeries.forEach(s => {
        s.data.forEach(v => totalTrend += v);
    });
    totalSampahPeriode.value = totalTrend;

    // Calculate colors and percentages for Gudang Status
    gudangStatus.value = data.gudangStatus.map((g) => {
        const stok = parseFloat(g.total_stok);
        const kap = parseFloat(g.kapasitas) || 1000; // Gunakan kapasitas asli, fallback ke 1000 jika 0/null
        const pct = kap > 0 ? Math.min(Math.round((stok / kap) * 100), 100) : 0;
        
        let text = 'Normal';
        let colorClass = 'bg-[#4A7043]';
        let textClass = 'text-[#4A7043]';

        if (pct >= 100) {
            text = 'Penuh';
            colorClass = 'bg-red-500';
            textClass = 'text-red-500';
        } else if (pct >= 90) {
            text = 'Hampir Penuh';
            colorClass = 'bg-orange-500';
            textClass = 'text-orange-500';
        } else if (pct >= 70) {
            text = 'Tinggi';
            colorClass = 'bg-yellow-400';
            textClass = 'text-yellow-600';
        }

        return {
            name: g.nama,
            percentage: pct,
            text: text,
            value: `${stok.toLocaleString('id-ID')} kg tersimpan`,
            colorClass: colorClass,
            textClass: textClass
        };
    });

    // Calculate percentages for Distribusi Saat Ini
    const distColors = ['bg-[#4A7043]', 'bg-[#5FA09B]', 'bg-[#A86444]', 'bg-[#F59E0B]', 'bg-[#7A7A7A]', 'bg-[#3D5A35]', 'bg-[#2E4A27]'];
    const totalDist = data.distribusiSaatIni.reduce((acc, curr) => acc + parseFloat(curr.total_stok), 0);
    distribusiSaatIni.value = data.distribusiSaatIni.map((d, idx) => {
        const stok = parseFloat(d.total_stok);
        const pct = totalDist > 0 ? Math.round((stok / totalDist) * 100) : 0;
        const color = distColors[idx % distColors.length];
        return {
            name: d.nama,
            value: `${stok.toLocaleString('id-ID')} kg`,
            percentage: pct,
            colorClass: color,
            dotClass: color
        };
    });

  } catch (error) {
    console.error("Error fetching charts data:", error);
  } finally {
    isLoadingCharts.value = false;
  }
};

import { watch } from "vue";
watch(selectedPeriod, () => {
    fetchChartsData();
});

onMounted(() => {
  fetchDashboardData();
  fetchChartsData();
});

// Area chart (Trend Sampah)
const trendChartOptions = ref({
  chart: {
    type: 'area',
    toolbar: { show: false },
    zoom: { enabled: false },
    fontFamily: 'Inter, sans-serif'
  },
  colors: ['#4A7043', '#5FA09B', '#A86444', '#F59E0B', '#7A7A7A', '#3D5A35', '#2E4A27'],
  dataLabels: { enabled: false },
  stroke: { curve: 'smooth', width: 2 },
  fill: {
    type: 'gradient',
    gradient: {
      shadeIntensity: 1,
      opacityFrom: 0.4,
      opacityTo: 0.05,
      stops: [0, 90, 100]
    }
  },
  xaxis: {
    categories: [],
    labels: { style: { colors: '#A8A29E', fontSize: '12px' } },
    axisBorder: { show: false },
    axisTicks: { show: false },
  },
  yaxis: {
    min: 0,
    tickAmount: 4,
    labels: { style: { colors: '#A8A29E', fontSize: '12px' } }
  },
  grid: {
    borderColor: '#f3f4f6',
    strokeDashArray: 4,
    yaxis: { lines: { show: true } }
  },
  legend: {
    position: 'bottom',
    horizontalAlign: 'center',
    markers: { radius: 12, size: 6 },
    itemMargin: { horizontal: 10, vertical: 0 }
  }
});

const trendChartSeries = ref([]);

// Line chart (Pertumbuhan Total Sampah)
const growthChartOptions = ref({
  chart: {
    type: 'line',
    toolbar: { show: false },
    zoom: { enabled: false },
    fontFamily: 'Inter, sans-serif'
  },
  colors: ['#4A7043'],
  dataLabels: { enabled: false },
  stroke: { curve: 'straight', width: 2 },
  markers: {
    size: 5,
    colors: ['#4A7043'],
    strokeColors: '#fff',
    strokeWidth: 2,
    hover: { size: 7 }
  },
  xaxis: {
    categories: [],
    labels: { style: { colors: '#A8A29E', fontSize: '12px' } },
    axisBorder: { show: false },
    axisTicks: { show: false },
  },
  yaxis: {
    min: 0,
    tickAmount: 4,
    labels: { style: { colors: '#A8A29E', fontSize: '12px' } }
  },
  grid: {
    borderColor: '#f3f4f6',
    strokeDashArray: 4,
  }
});

const growthChartSeries = ref([]);

// Progress bar data
const gudangStatus = ref([]);

const distribusiSaatIni = ref([]);
</script>

<template>
  <DashboardLayout title="Dashboard">
    <div class="space-y-6 animate-in fade-in duration-700 pb-10 font-['Inter']">

      <!-- Top Stats Row -->
      <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">
        <template v-if="isLoadingStats">
          <div v-for="i in 5" :key="`stat-skel-${i}`" class="rounded-2xl p-5 h-36 bg-stone-100 animate-pulse border border-stone-200">
            <div class="flex justify-between items-start">
              <div class="w-10 h-10 rounded-full bg-stone-200"></div>
              <div class="w-16 h-5 rounded-full bg-stone-200"></div>
            </div>
            <div class="mt-8">
              <div class="w-20 h-8 rounded bg-stone-200 mb-2"></div>
              <div class="w-24 h-4 rounded bg-stone-200"></div>
            </div>
          </div>
        </template>
        <template v-else>
          <div v-for="(stat, idx) in topStats" :key="idx"
            class="rounded-2xl p-5 text-white shadow-sm flex flex-col justify-between h-36 relative overflow-hidden group transition-all duration-300 hover:shadow-md hover:-translate-y-1"
            :class="stat.bgClass"
          >
            <div class="flex justify-between items-start z-10">
              <Icon :icon="stat.icon" class="w-7 h-7" />
              <div class="px-2.5 py-1 rounded-full text-[10px] font-bold flex items-center gap-1" :class="stat.increaseClass">
                <Icon v-if="stat.increase.includes('+')" icon="material-symbols:trending-up" class="w-3 h-3" />
                <Icon v-else-if="stat.increase.includes('-')" icon="material-symbols:trending-down" class="w-3 h-3" />
                <Icon v-else icon="material-symbols:check-circle-outline" class="w-3 h-3" />
                {{ stat.increase }}
              </div>
            </div>
            <div class="z-10 mt-auto">
              <h3 class="text-3xl font-bold mb-0.5 leading-none">{{ stat.value }}</h3>
              <p class="text-xs font-medium text-white/90">{{ stat.title }}</p>
            </div>
          </div>
        </template>
      </div>

      <!-- Middle Row -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Trend Sampah Chart -->
        <div class="lg:col-span-2 bg-white rounded-[1.5rem] p-6 shadow-sm border border-stone-100 min-h-[400px]">
          <template v-if="isLoadingCharts">
            <div class="flex justify-between items-start mb-6">
              <div class="space-y-2">
                <div class="h-6 w-32 bg-stone-200 rounded animate-pulse"></div>
                <div class="h-4 w-48 bg-stone-100 rounded animate-pulse"></div>
              </div>
              <div class="h-8 w-40 bg-stone-100 rounded-lg animate-pulse"></div>
            </div>
            <div class="h-[300px] w-full bg-stone-50 rounded-xl animate-pulse"></div>
          </template>
          <template v-else>
            <div class="flex justify-between items-start mb-2">
              <div>
                <h3 class="text-lg font-bold text-stone-800">Trend Sampah</h3>
                <p class="text-xs text-stone-400">Total {{ totalSampahPeriode.toLocaleString('id-ID') }} kg pada periode terpilih</p>
              </div>
              <div class="flex bg-stone-100 rounded-lg p-1">
                <button v-for="period in periods" :key="period"
                  @click="selectedPeriod = period"
                  class="px-3 py-1.5 text-xs font-bold rounded-md transition-colors cursor-pointer"
                  :class="selectedPeriod === period ? 'bg-[#4A7043] text-white shadow-sm' : 'text-stone-500 hover:text-stone-700'"
                >
                  {{ period }}
                </button>
              </div>
            </div>
            <div class="mt-4 -ml-2">
              <apexchart type="area" height="300" :options="trendChartOptions" :series="trendChartSeries" />
            </div>
          </template>
        </div>

        <!-- Status Gudang -->
        <div class="bg-white rounded-[1.5rem] p-6 shadow-sm border border-stone-100 flex flex-col min-h-[400px]">
          <template v-if="isLoadingCharts">
            <div class="flex justify-between items-center mb-6">
              <div class="h-6 w-32 bg-stone-200 rounded animate-pulse"></div>
              <div class="h-4 w-12 bg-stone-100 rounded animate-pulse"></div>
            </div>
            <div class="space-y-6 flex-1 flex flex-col justify-center">
              <div v-for="i in 4" :key="`gudang-skel-${i}`" class="space-y-2">
                <div class="flex justify-between">
                  <div class="h-4 w-24 bg-stone-200 rounded animate-pulse"></div>
                  <div class="h-4 w-8 bg-stone-200 rounded animate-pulse"></div>
                </div>
                <div class="w-full bg-stone-100 rounded-full h-2"></div>
                <div class="flex justify-between">
                  <div class="h-3 w-20 bg-stone-100 rounded animate-pulse"></div>
                  <div class="h-3 w-16 bg-stone-100 rounded animate-pulse"></div>
                </div>
              </div>
            </div>
          </template>
          <template v-else>
            <div class="flex justify-between items-center mb-6">
              <h3 class="text-lg font-bold text-stone-800">Status Gudang</h3>
              <!-- <a href="#" class="text-xs text-stone-500 hover:text-stone-800 flex items-center gap-1 font-bold transition-colors">
                Detail <Icon icon="material-symbols:arrow-outward" class="w-3.5 h-3.5" />
              </a> -->
            </div>
            <div class="space-y-6 flex-1 flex flex-col justify-center">
              <div v-for="(gudang, index) in gudangStatus" :key="index" class="space-y-2">
                <div class="flex justify-between items-end">
                  <span class="text-sm font-bold text-stone-700">{{ gudang.name }}</span>
                  <span class="text-xs font-bold" :class="gudang.textClass">{{ gudang.percentage }}%</span>
                </div>
                <div class="w-full bg-stone-100 rounded-full h-2 overflow-hidden">
                  <div class="h-2 rounded-full transition-all duration-1000" :class="gudang.colorClass" :style="{ width: `${gudang.percentage}%` }"></div>
                </div>
                <div class="flex justify-between items-center">
                  <span class="text-xs font-medium text-stone-400">{{ gudang.value }}</span>
                  <span class="text-[10px] font-bold" :class="gudang.textClass">{{ gudang.text }}</span>
                </div>
              </div>
            </div>
          </template>
        </div>
      </div>

      <!-- Bottom Row -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Pertumbuhan Total Sampah Chart -->
        <div class="lg:col-span-2 bg-white rounded-[1.5rem] p-6 shadow-sm border border-stone-100 min-h-[380px]">
          <template v-if="isLoadingCharts">
            <div class="mb-6 space-y-2">
              <div class="h-6 w-48 bg-stone-200 rounded animate-pulse"></div>
              <div class="h-4 w-56 bg-stone-100 rounded animate-pulse"></div>
            </div>
            <div class="h-[280px] w-full bg-stone-50 rounded-xl animate-pulse"></div>
          </template>
          <template v-else>
            <div class="mb-2">
              <h3 class="text-lg font-bold text-stone-800">Pertumbuhan Total Sampah</h3>
              <p class="text-xs text-stone-400">Akumulasi bulanan seluruh gudang</p>
            </div>
            <div class="mt-4 -ml-2">
              <apexchart type="line" height="280" :options="growthChartOptions" :series="growthChartSeries" />
            </div>
          </template>
        </div>

        <!-- Distribusi Saat Ini -->
        <div class="bg-white rounded-[1.5rem] p-6 shadow-sm border border-stone-100 flex flex-col min-h-[380px]">
          <template v-if="isLoadingCharts">
            <div class="h-6 w-40 bg-stone-200 rounded animate-pulse mb-6"></div>
            <div class="space-y-6 flex-1 flex flex-col justify-center">
              <div v-for="i in 4" :key="`dist-skel-${i}`" class="space-y-2">
                <div class="flex justify-between items-center">
                  <div class="flex items-center gap-2">
                    <div class="w-2.5 h-2.5 rounded-full bg-stone-200 animate-pulse"></div>
                    <div class="h-4 w-16 bg-stone-200 rounded animate-pulse"></div>
                  </div>
                  <div class="h-4 w-12 bg-stone-200 rounded animate-pulse"></div>
                </div>
                <div class="w-full bg-stone-100 rounded-full h-2"></div>
              </div>
            </div>
          </template>
          <template v-else>
            <h3 class="text-lg font-bold text-stone-800 mb-6">Distribusi Saat Ini</h3>
            <div class="space-y-6 flex-1 flex flex-col justify-center">
              <div v-for="(item, index) in distribusiSaatIni" :key="index" class="space-y-2 group">
                <div class="flex justify-between items-center">
                  <div class="flex items-center gap-2">
                    <div class="w-2.5 h-2.5 rounded-full" :class="item.dotClass"></div>
                    <span class="text-sm font-bold text-stone-600">{{ item.name }}</span>
                  </div>
                  <span class="text-sm font-bold text-stone-800">{{ item.value }}</span>
                </div>
                <div class="w-full bg-stone-100 rounded-full h-2 overflow-hidden">
                  <div class="h-2 rounded-full transition-all duration-1000 group-hover:opacity-80" :class="item.colorClass" :style="{ width: `${item.percentage}%` }"></div>
                </div>
              </div>
            </div>
          </template>
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
