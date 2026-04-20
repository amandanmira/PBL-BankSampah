<script setup>
import { ref } from "vue";
import DashboardLayout from "@/layouts/DashboardLayout.vue";
import GreetingCard from "@/components/dashboard/GreetingCard.vue";
import StatCard from "@/components/dashboard/StatCard.vue";
import TransactionChart from "@/components/dashboard/TransactionChart.vue";
import LeaderboardTable from "@/components/dashboard/LeaderboardTable.vue";
import ActivityList from "@/components/dashboard/ActivityList.vue";
import { checkRole } from "@/utils";

// Middleware
checkRole("nasabah");

const user = ref(JSON.parse(sessionStorage.getItem("user") || "{}"));

const stats = ref([
  {
    title: "Saldo Tersedia",
    value: "Rp 450.000",
    icon: "material-symbols:account-balance-wallet-outline",
    iconClass: "text-[#4A7043] bg-[#E8F0E6]",
  },
  {
    title: "Total Sampah",
    value: "125.5 kg",
    icon: "material-symbols:package-2-outline",
    iconClass: "text-cyan-600 bg-cyan-50",
  },
  {
    title: "Total Transaksi",
    value: "28",
    icon: "material-symbols:trending-up",
    iconClass: "text-orange-600 bg-orange-50",
  },
]);

// Top Nasabah - Dikosongkan sementara
const topNasabah = ref([]);

// Aktivitas Terbaru - Dikosongkan sementara
const recentActivities = ref([]);
</script>

<template>
  <DashboardLayout title="Dashboard">
    <div class="space-y-10">
      <!-- Greeting -->
      <GreetingCard :name="user.name || 'Nasabah'" />

      <!-- Summary Section -->
      <section>
        <h2 class="text-xl font-bold text-stone-800 mb-6">Ringkasan</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <StatCard
            v-for="stat in stats"
            :key="stat.title"
            v-bind="stat"
          />
        </div>
      </section>

      <!-- Chart Section -->
      <section>
        <TransactionChart />
      </section>

      <!-- Leaderboard Section -->
      <section>
        <LeaderboardTable :data="topNasabah" />
      </section>

      <!-- Activities Section -->
      <section>
        <ActivityList :activities="recentActivities" />
      </section>
    </div>
  </DashboardLayout>
</template>
