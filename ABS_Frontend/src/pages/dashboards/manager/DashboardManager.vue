<script setup>
import { ref, onMounted, watch } from "vue";
import DashboardLayout from "@/layouts/DashboardLayout.vue";
import DashboardTopStats from "@/components/manager/DashboardTopStats.vue";
import DashboardTrendChart from "@/components/manager/DashboardTrendChart.vue";
import DashboardGrowthChart from "@/components/manager/DashboardGrowthChart.vue";
import DashboardGudangStatus from "@/components/manager/DashboardGudangStatus.vue";
import DashboardDistribusi from "@/components/manager/DashboardDistribusi.vue";
import axios from "axios";

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
            id: g.gudang_id,
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
  } finally {
    isLoadingCharts.value = false;
  }
};

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
const detailSampah = ref([]);
const isDistribusiModalOpen = ref(false);

// Transaksi Bulan Ini Modal State
const isModalOpen = ref(false);
const modalLoading = ref(false);
const modalTransactions = ref([]);
const currentPage = ref(1);
const totalPages = ref(1);
const totalTransactionsCount = ref(0);

const fetchModalData = async (page = 1) => {
  modalLoading.value = true;
  try {
    const token = sessionStorage.getItem('token');
    const response = await axios.get("/api/manager/transaksi-bulan-ini", {
      params: { page, per_page: 5 },
      headers: {
        'Authorization': `Bearer ${token}`
      }
    });
    modalTransactions.value = response.data.data;
    currentPage.value = response.data.current_page;
    totalPages.value = response.data.last_page;
    totalTransactionsCount.value = response.data.total;
  } catch (err) {
    console.error("Gagal memuat transaksi detail:", err);
  } finally {
    modalLoading.value = false;
  }
};

const openModal = () => {
  isModalOpen.value = true;
  currentPage.value = 1;
  fetchModalData(1);
};

const closeModal = () => {
  isModalOpen.value = false;
};

const nextPage = () => {
  if (currentPage.value < totalPages.value) {
    fetchModalData(currentPage.value + 1);
  }
};

const prevPage = () => {
  if (currentPage.value > 1) {
    fetchModalData(currentPage.value - 1);
  }
};
</script>

<template>
  <DashboardLayout title="Dashboard">
    <div class="space-y-6 animate-in fade-in duration-700 pb-10 font-['Inter']">

      <!-- Top Stats Row -->
      <DashboardTopStats :topStats="topStats" :isLoadingStats="isLoadingStats" @click-detail="openModal" />

      <!-- Middle Row -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Trend Sampah Chart -->
        <DashboardTrendChart 
          :isLoadingCharts="isLoadingCharts" 
          :totalSampahPeriode="totalSampahPeriode" 
          :selectedPeriod="selectedPeriod" 
          @update:selectedPeriod="selectedPeriod = $event"
          :periods="periods" 
          :trendChartOptions="trendChartOptions" 
          :trendChartSeries="trendChartSeries" 
        />

        <!-- Status Gudang -->
        <DashboardGudangStatus 
          :isLoadingCharts="isLoadingCharts" 
          :gudangStatus="gudangStatus" 
        />
      </div>

      <!-- Bottom Row -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Pertumbuhan Total Sampah Chart -->
        <DashboardGrowthChart 
          :isLoadingCharts="isLoadingCharts" 
          :growthChartOptions="growthChartOptions" 
          :growthChartSeries="growthChartSeries" 
        />

        <!-- Distribusi Saat Ini -->
        <DashboardDistribusi 
          :isLoadingCharts="isLoadingCharts" 
          :distribusiSaatIni="distribusiSaatIni" 
          :detailSampah="detailSampah" 
        />
      </div>

      <!-- Modal Transaksi Bulan Ini -->
      <div v-if="isModalOpen" class="fixed inset-0 z-55 flex items-center justify-center p-4 bg-transparent font-['Inter']">
        <!-- Transparent Click-outside Overlay -->
        <div class="fixed inset-0 bg-transparent" @click="closeModal"></div>
        
        <!-- Modal Content Container -->
        <div class="bg-white rounded-3xl w-full max-w-2xl max-h-[85vh] flex flex-col border border-stone-200/80 shadow-[0_25px_60px_-15px_rgba(0,0,0,0.2)] overflow-hidden relative z-10 animate-in fade-in zoom-in duration-200">
          <!-- Header -->
          <div class="p-6 border-b border-stone-100 flex justify-between items-center bg-[#4A7043] text-white">
            <div>
              <h3 class="text-lg font-bold text-white">Detail Transaksi Bulan Ini</h3>
              <p class="text-xs text-white/80 mt-0.5">Gabungan transaksi nasabah dan pengepul</p>
            </div>
            <button @click="closeModal" class="p-1.5 hover:bg-white/10 rounded-full transition-colors cursor-pointer text-white">
              <Icon icon="material-symbols:close" class="w-6 h-6 text-white" />
            </button>
          </div>

          <!-- Content -->
          <div class="p-6 overflow-y-auto flex-1 space-y-4 text-stone-700">
            <div v-if="modalLoading" class="flex flex-col items-center justify-center py-16">
              <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-[#4A7043]"></div>
              <p class="text-xs font-semibold text-stone-500 mt-3">Memuat riwayat transaksi...</p>
            </div>
            
            <div v-else-if="modalTransactions.length === 0" class="flex flex-col items-center justify-center py-16 text-stone-400">
              <Icon icon="material-symbols:history" class="w-12 h-12 opacity-30 mb-2" />
              <p class="text-sm font-semibold text-stone-500">Tidak ada transaksi di bulan ini</p>
            </div>

            <div v-else class="space-y-3">
              <div 
                v-for="tx in modalTransactions" 
                :key="tx.kode"
                class="p-4 rounded-2xl border border-stone-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:bg-stone-50/50 transition-colors"
              >
                <div class="flex items-start gap-3">
                  <div 
                    :class="[
                      'p-2.5 rounded-xl shrink-0 flex items-center justify-center',
                      tx.tipe === 'Nasabah' ? 'bg-[#E8F0E6] text-[#4A7043]' : 'bg-cyan-50 text-cyan-600'
                    ]"
                  >
                    <Icon 
                      :icon="tx.tipe === 'Nasabah' ? 'material-symbols:person-outline' : 'material-symbols:local-shipping-outline'" 
                      class="w-5 h-5" 
                    />
                  </div>
                  <div>
                    <div class="flex items-center gap-2 flex-wrap">
                      <span class="font-bold text-sm text-stone-800">{{ tx.kode }}</span>
                      <span 
                        :class="[
                          'px-2 py-0.5 text-[9.5px] font-extrabold rounded-full border uppercase tracking-wider',
                          tx.tipe === 'Nasabah' ? 'bg-green-50 text-green-700 border-green-100' : 'bg-cyan-50 text-cyan-700 border-cyan-100'
                        ]"
                      >
                        {{ tx.tipe }}
                      </span>
                      <span 
                        :class="[
                          'px-2 py-0.5 text-[9.5px] font-bold rounded-full border uppercase tracking-wide',
                          tx.status === 'selesai' ? 'bg-green-50 text-green-700 border-green-200' :
                          tx.status === 'pending' ? 'bg-amber-50 text-amber-700 border-amber-200' :
                          'bg-stone-50 text-stone-600 border-stone-200'
                        ]"
                      >
                        {{ tx.status }}
                      </span>
                    </div>
                    <p class="text-xs font-semibold text-stone-600 mt-1">Pelaku: {{ tx.pelaku }}</p>
                    <p class="text-[11px] text-stone-400 mt-0.5">{{ tx.keterangan }} • {{ tx.tanggal }}</p>
                  </div>
                </div>
                <div class="sm:text-right shrink-0">
                  <span class="font-extrabold text-base text-[#4A7043]">{{ tx.total }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Footer / Pagination -->
          <div v-if="!modalLoading && totalPages > 1" class="p-6 border-t border-stone-100 flex items-center justify-between bg-stone-50/50">
            <p class="text-xs text-stone-500 font-semibold">
              Menampilkan {{ modalTransactions.length }} dari {{ totalTransactionsCount }} transaksi
            </p>
            <div class="flex gap-2">
              <button 
                @click="prevPage" 
                :disabled="currentPage === 1"
                class="px-3 py-1.5 rounded-xl border border-stone-200 text-xs font-bold bg-white text-stone-700 hover:bg-stone-50 disabled:opacity-50 disabled:cursor-not-allowed transition-all cursor-pointer"
              >
                Sebelumnya
              </button>
              <button 
                @click="nextPage" 
                :disabled="currentPage === totalPages"
                class="px-3 py-1.5 rounded-xl border border-stone-200 text-xs font-bold bg-white text-stone-700 hover:bg-stone-50 disabled:opacity-50 disabled:cursor-not-allowed transition-all cursor-pointer"
              >
                Berikutnya
              </button>
            </div>
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
