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
  try {
    const token = sessionStorage.getItem('token');
    const response = await axios.get("http://localhost:8000/api/manager/dashboard-stats", {
      headers: {
        'Authorization': `Bearer ${token}`
      }
    });
    
    const data = response.data.stats;
    
    // Fungsi untuk update nilai stat spesifik jika nilainya tidak "X" dan terdefinisi
    const updateStat = (id, newValue) => {
      const stat = topStats.value.find(s => s.id === id);
      if (stat && newValue !== "X" && newValue !== undefined) {
        stat.value = typeof newValue === 'number' ? newValue.toLocaleString('id-ID') : newValue;
      }
    };

    updateStat('petugas', data.total_petugas);
    updateStat('sampah', data.total_sampah);
    updateStat('nasabah', data.nasabah_verifikasi);
    updateStat('transaksi', data.transaksi_bulan_ini);
    
    // Update gudang stat dynamically
    const gudangStat = topStats.value.find(s => s.id === 'gudang');
    if (gudangStat && data.total_gudang !== undefined) {
      gudangStat.value = data.total_gudang.toLocaleString('id-ID');
      gudangStat.increase = `${data.active_gudang} dari ${data.total_gudang} online`;
    }
    
  } catch (error) {
    console.error("Error fetching dashboard data:", error);
  }
};

const selectedPeriod = ref('6 Bulan');
const periods = ['1 Bulan', '3 Bulan', '6 Bulan'];
const totalSampahPeriode = ref(0);

const fetchChartsData = async () => {
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
    const gudangColors = ['bg-[#4A7043]', 'bg-[#5FA09B]', 'bg-[#A86444]', 'bg-orange-500', 'bg-red-500'];
    gudangStatus.value = data.gudangStatus.map((g, idx) => {
        const stok = parseFloat(g.total_stok);
        const kap = 1000; // Asumsi kapasitas 1000kg
        const pct = Math.min(Math.round((stok / kap) * 100), 100);
        let text = 'Normal';
        if(pct > 80) text = 'Hampir Penuh';
        else if(pct > 60) text = 'Tinggi';
        const color = gudangColors[idx % gudangColors.length];
        
        return {
            id: g.gudang_id,
            name: g.nama,
            percentage: pct,
            text: text,
            value: `${stok.toLocaleString('id-ID')} kg tersimpan`,
            colorClass: color,
            textClass: color.replace('bg-', 'text-').replace('[', '').replace(']', '')
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
            id: d.kategori_id,
            name: d.nama,
            value: `${stok.toLocaleString('id-ID')} kg`,
            percentage: pct,
            colorClass: color,
            dotClass: color
        };
    });
    detailSampah.value = data.detailSampah;
    
  } catch (error) {
    console.error("Error fetching charts data:", error);
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
const isGudangModalOpen = ref(false);

const distribusiSaatIni = ref([]);
const detailSampah = ref([]);
const isDistribusiModalOpen = ref(false);
</script>

<template>
  <DashboardLayout title="Dashboard">
    <div class="space-y-6 animate-in fade-in duration-700 pb-10 font-['Inter']">

      <!-- Top Stats Row -->
      <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">
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
      </div>

      <!-- Middle Row -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Trend Sampah Chart -->
        <div class="lg:col-span-2 bg-white rounded-[1.5rem] p-6 shadow-sm border border-stone-100">
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
          <div class="mt-4 -ml-2 h-[300px] flex items-center justify-center border border-dashed border-stone-200 rounded-2xl bg-stone-50/30" v-if="totalSampahPeriode === 0">
            <div class="text-center space-y-2">
              <Icon icon="material-symbols:insert-chart-outline" class="w-12 h-12 text-stone-300 mx-auto" />
              <p class="text-sm font-medium text-stone-500">Belum ada trend sampah pada periode ini</p>
              <p class="text-xs text-stone-400">Data transaksi penimbangan sampah belum tercatat.</p>
            </div>
          </div>
          <div class="mt-4 -ml-2" v-else>
            <apexchart type="area" height="300" :options="trendChartOptions" :series="trendChartSeries" />
          </div>
        </div>

        <!-- Status Gudang -->
        <div class="bg-white rounded-[1.5rem] p-6 shadow-sm border border-stone-100 flex flex-col">
          <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-bold text-stone-800">Status Gudang</h3>
            <button @click="isGudangModalOpen = true" 
              class="text-xs flex items-center gap-1 font-bold transition-all cursor-pointer px-2.5 py-1.5 rounded-lg"
              :class="isGudangModalOpen ? 'bg-[#4A7043] text-white shadow-sm' : 'text-[#4A7043] hover:text-[#3D5A35] hover:bg-stone-50'">
              Lihat Detail <Icon icon="material-symbols:arrow-outward" class="w-3.5 h-3.5" />
            </button>
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
        </div>
      </div>

      <!-- Bottom Row -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Pertumbuhan Total Sampah Chart -->
        <div class="lg:col-span-2 bg-white rounded-[1.5rem] p-6 shadow-sm border border-stone-100">
          <div class="mb-2">
            <h3 class="text-lg font-bold text-stone-800">Pertumbuhan Total Sampah</h3>
            <p class="text-xs text-stone-400">Akumulasi bulanan seluruh gudang</p>
          </div>
          <div class="mt-4 -ml-2 h-[280px] flex items-center justify-center border border-dashed border-stone-200 rounded-2xl bg-stone-50/30" v-if="!growthChartSeries || growthChartSeries.length === 0 || growthChartSeries[0]?.data?.every(v => v === 0)">
            <div class="text-center space-y-2">
              <Icon icon="material-symbols:show-chart-rounded" class="w-12 h-12 text-stone-300 mx-auto" />
              <p class="text-sm font-medium text-stone-500">Belum ada pertumbuhan total sampah</p>
              <p class="text-xs text-stone-400">Data akumulasi bulanan belum tersedia.</p>
            </div>
          </div>
          <div class="mt-4 -ml-2" v-else>
            <apexchart type="line" height="280" :options="growthChartOptions" :series="growthChartSeries" />
          </div>
        </div>

        <!-- Distribusi Saat Ini -->
        <div class="bg-white rounded-[1.5rem] p-6 shadow-sm border border-stone-100 flex flex-col">
          <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-bold text-stone-800">Distribusi Saat Ini</h3>
            <button @click="isDistribusiModalOpen = true" 
              class="text-xs flex items-center gap-1 font-bold transition-all cursor-pointer px-2.5 py-1.5 rounded-lg"
              :class="isDistribusiModalOpen ? 'bg-[#4A7043] text-white shadow-sm' : 'text-[#4A7043] hover:text-[#3D5A35] hover:bg-stone-50'">
              Lihat Detail <Icon icon="material-symbols:arrow-outward" class="w-3.5 h-3.5" />
            </button>
          </div>
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
        </div>
      </div>

      <!-- Modal Detail Gudang -->
      <div v-if="isGudangModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 transition-opacity duration-300">
        <div class="bg-white rounded-[2rem] shadow-[0_20px_50px_rgba(0,0,0,0.2)] border border-stone-200 max-w-2xl w-full overflow-hidden transform transition-all duration-300 scale-100 flex flex-col max-h-[85vh]">
          <!-- Modal Header -->
          <div class="px-6 py-5 border-b border-stone-100 flex justify-between items-center bg-stone-50">
            <div>
              <h3 class="text-xl font-bold text-stone-800">Detail Status Gudang</h3>
              <p class="text-xs text-stone-500 mt-1">Daftar informasi lengkap kapasitas dan penyimpanan seluruh gudang aktif</p>
            </div>
            <button @click="isGudangModalOpen = false" class="p-2 text-stone-400 hover:text-stone-700 hover:bg-stone-100 rounded-full transition-colors cursor-pointer">
              <Icon icon="material-symbols:close-rounded" class="w-6 h-6" />
            </button>
          </div>

          <!-- Modal Body -->
          <div class="p-6 overflow-y-auto space-y-4 flex-1">
            <div v-for="gudang in gudangStatus" :key="gudang.id" 
              class="p-5 rounded-2xl border border-stone-100 bg-stone-50/50 hover:bg-stone-50 transition-all duration-200 flex flex-col md:flex-row md:items-center justify-between gap-4">
              <div class="space-y-1.5 flex-1">
                <div class="flex items-center gap-2">
                  <span class="px-2 py-0.5 text-[10px] font-bold bg-stone-200 text-stone-700 rounded-md">ID: {{ gudang.id }}</span>
                  <h4 class="text-sm font-bold text-stone-800">{{ gudang.name }}</h4>
                </div>
                <div class="text-xs text-stone-500 flex items-center gap-1.5">
                  <Icon icon="material-symbols:location-on-outline" class="w-4 h-4 text-stone-400" />
                  <span>Alamat: {{ gudang.name }}</span>
                </div>
              </div>
              <div class="flex items-center gap-4 border-t md:border-t-0 pt-3 md:pt-0 border-stone-100">
                <div class="text-right">
                  <p class="text-xs text-stone-400">Total Penyimpanan</p>
                  <p class="text-sm font-bold text-stone-800">{{ gudang.value }}</p>
                </div>
                <div class="flex items-center justify-center w-12 h-12 rounded-full border-2 transition-all duration-300" 
                  :class="gudang.percentage > 80 ? 'border-red-500 bg-red-50' : gudang.percentage > 60 ? 'border-orange-500 bg-orange-50' : 'border-[#4A7043] bg-emerald-50'">
                  <span class="text-xs font-bold" :class="gudang.percentage > 80 ? 'text-red-700' : gudang.percentage > 60 ? 'text-orange-700' : 'text-[#4A7043]'">{{ gudang.percentage }}%</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Modal Footer -->
          <div class="px-6 py-4 border-t border-stone-100 bg-stone-50 flex justify-end">
            <button @click="isGudangModalOpen = false" class="px-5 py-2.5 text-sm font-bold text-stone-600 hover:text-stone-800 hover:bg-stone-100 rounded-xl transition-all cursor-pointer">
              Tutup
            </button>
          </div>
        </div>
      </div>

      <!-- Modal Detail Distribusi Sampah -->
      <div v-if="isDistribusiModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 transition-opacity duration-300">
        <div class="bg-white rounded-[2rem] shadow-[0_20px_50px_rgba(0,0,0,0.2)] border border-stone-200 max-w-3xl w-full overflow-hidden transform transition-all duration-300 scale-100 flex flex-col max-h-[85vh]">
          <!-- Modal Header -->
          <div class="px-6 py-5 border-b border-stone-100 flex justify-between items-center bg-stone-50">
            <div>
              <h3 class="text-xl font-bold text-stone-800">Detail Distribusi Sampah</h3>
              <p class="text-xs text-stone-500 mt-1">Daftar rincian item sampah, lokasi gudang penyimpanan, dan stok masing-masing kategori</p>
            </div>
            <button @click="isDistribusiModalOpen = false" class="p-2 text-stone-400 hover:text-stone-700 hover:bg-stone-100 rounded-full transition-colors cursor-pointer">
              <Icon icon="material-symbols:close-rounded" class="w-6 h-6" />
            </button>
          </div>

          <!-- Modal Body -->
          <div class="p-6 overflow-y-auto space-y-6 flex-1 bg-stone-50/20">
            <div v-for="category in distribusiSaatIni" :key="category.id" class="space-y-3">
              <!-- Category Header -->
              <div class="flex justify-between items-center bg-stone-100/70 p-3.5 rounded-xl border border-stone-200/50">
                <div class="flex items-center gap-2">
                  <div class="w-3 h-3 rounded-full" :class="category.dotClass"></div>
                  <h4 class="text-sm font-extrabold text-stone-700 uppercase tracking-wider">{{ category.name }}</h4>
                </div>
                <span class="text-xs font-extrabold text-stone-800 bg-white px-2.5 py-1 rounded-md border border-stone-200">Total: {{ category.value }}</span>
              </div>
              
              <!-- Items List -->
              <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pl-2">
                <div v-for="(item, idx) in detailSampah.filter(i => i.kategori_id === category.id)" :key="idx"
                  class="p-4 rounded-xl border border-stone-100 bg-white flex flex-col justify-between gap-3 hover:bg-stone-50 transition-all duration-200 shadow-sm">
                  <div class="space-y-1">
                    <h5 class="text-xs font-bold text-stone-800">{{ item.nama_item }}</h5>
                    <div class="text-[10px] text-stone-500 flex items-center gap-1">
                      <Icon icon="material-symbols:warehouse-outline" class="w-3.5 h-3.5 text-stone-400" />
                      <span>{{ item.nama_gudang }}</span>
                    </div>
                  </div>
                  <div class="flex justify-between items-end border-t border-stone-100 pt-2">
                    <div class="text-[10px] text-stone-400">
                      <div>Beli: <span class="font-semibold text-stone-600">Rp{{ item.harga_beli.toLocaleString('id-ID') }}/kg</span></div>
                      <div>Jual: <span class="font-semibold text-stone-600">Rp{{ item.harga_jual.toLocaleString('id-ID') }}/kg</span></div>
                    </div>
                    <span class="text-xs font-bold text-[#4A7043] bg-emerald-50 px-2 py-0.5 rounded-md">{{ item.stok.toLocaleString('id-ID') }} kg</span>
                  </div>
                </div>
                <!-- Empty Item Handler -->
                <div v-if="detailSampah.filter(i => i.kategori_id === category.id).length === 0" 
                  class="col-span-full py-4 text-center text-xs text-stone-400 italic">
                  Belum ada rincian item untuk kategori ini.
                </div>
              </div>
            </div>
          </div>

          <!-- Modal Footer -->
          <div class="px-6 py-4 border-t border-stone-100 bg-stone-50 flex justify-end">
            <button @click="isDistribusiModalOpen = false" class="px-5 py-2.5 text-sm font-bold text-stone-600 hover:text-stone-800 hover:bg-stone-100 rounded-xl transition-all cursor-pointer">
              Tutup
            </button>
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
