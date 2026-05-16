<script setup>
import { ref, onMounted, inject } from "vue";
import DashboardLayout from "@/layouts/DashboardLayout.vue";
import GreetingCard from "@/components/dashboard/GreetingCard.vue";
import StatCard from "@/components/dashboard/StatCard.vue";
import TransactionChart from "@/components/dashboard/TransactionChart.vue";
import LeaderboardTable from "@/components/dashboard/LeaderboardTable.vue";
import ActivityList from "@/components/dashboard/ActivityList.vue";
import { checkRole } from "@/utils";

// Middleware
checkRole("nasabah");

const axios = inject("axios");
const user = ref(JSON.parse(sessionStorage.getItem("user") || "{}"));
const loading = ref(true);

// Data Statistik Ringkasan
const stats = ref([
  {
    title: "Saldo Tersedia",
    value: "Rp 0",
    icon: "material-symbols:account-balance-wallet-outline",
    iconClass: "text-[#4A7043] bg-[#E8F0E6]",
  },
  {
    title: "Total Sampah",
    value: "0 kg",
    icon: "material-symbols:package-2-outline",
    iconClass: "text-cyan-600 bg-cyan-50",
  },
  {
    title: "Total Transaksi",
    value: "0",
    icon: "material-symbols:trending-up",
    iconClass: "text-orange-600 bg-orange-50",
  },
]);

// Top Nasabah
const topNasabah = ref([]);

// Chart Data
const chartSeries = ref([
  { name: "Volume (kg)", data: [0, 0, 0, 0, 0, 0] },
  { name: "Pendapatan (Rp)", data: [0, 0, 0, 0, 0, 0] },
]);
const chartCategories = ref(["-", "-", "-", "-", "-", "-"]);

// Aktivitas Terbaru
const recentActivities = ref([]);

const formatRupiah = (number) => {
  return new Intl.NumberFormat("id-ID", {
    style: "currency",
    currency: "IDR",
    minimumFractionDigits: 0,
  }).format(number);
};

const fetchData = async () => {
  loading.value = true;
  try {
    const response = await axios.get("/api/nasabah/dashboard-stats");
    const data = response.data;

    if (data.user) {
      user.value = { ...user.value, ...data.user };
    }

    stats.value[0].value = formatRupiah(data.stats.saldo_tersedia);
    stats.value[1].value = `${data.stats.total_sampah} kg`;
    stats.value[2].value = data.stats.total_transaksi.toString();

    // Chart Data
    if (data.chart_data) {
      chartCategories.value = data.chart_data.map(d => d.month);
      chartSeries.value = [
        { name: "Volume (kg)", data: data.chart_data.map(d => d.volume) },
        { name: "Pendapatan (Rp)", data: data.chart_data.map(d => d.income) },
      ];
    }

    // Top Nasabah
    topNasabah.value = data.top_nasabah || [];

    recentActivities.value = data.activities;
  } catch (err) {
    console.error("Failed to fetch dashboard stats:", err);
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchData();
});
</script>

<template>
  <DashboardLayout title="Dashboard">
    <div class="space-y-10">
      <!-- Greeting Section -->
      <GreetingCard :name="user.nama || user.name || 'Nasabah'" />

      <!-- Summary Section -->
      <section>
        <h2 class="text-xl font-bold text-stone-800 mb-6">Ringkasan</h2>
        <div v-if="loading" class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div v-for="i in 3" :key="i" class="h-32 bg-stone-200 animate-pulse rounded-3xl"></div>
        </div>
        <div v-else class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <StatCard
            v-for="stat in stats"
            :key="stat.title"
            v-bind="stat"
          />
        </div>
      </section>

      <!-- Chart Section -->
      <section>
        <div v-if="loading" class="h-80 bg-stone-200 animate-pulse rounded-3xl"></div>
        <TransactionChart v-else :series="chartSeries" :categories="chartCategories" />
      </section>

      <!-- Leaderboard Section -->
      <section>
        <div v-if="loading" class="h-80 bg-stone-200 animate-pulse rounded-3xl"></div>
        <LeaderboardTable v-else :data="topNasabah" />
      </section>

      <!-- Activities Section -->
      <section>
        <div v-if="loading" class="h-64 bg-stone-200 animate-pulse rounded-3xl"></div>
        <ActivityList v-else :activities="recentActivities" />
      </section>
    </div>
  </DashboardLayout>
</template>
