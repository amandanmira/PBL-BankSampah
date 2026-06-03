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
</script>

<template>
  <DashboardLayout title="Dashboard">
    <div class="space-y-6 animate-in fade-in duration-700 pb-10 font-['Inter']">

      <!-- Top Stats Row -->
      <DashboardTopStats :topStats="topStats" :isLoadingStats="isLoadingStats" />

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
