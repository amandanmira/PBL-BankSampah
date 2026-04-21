<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";
import DashboardLayout from "@/layouts/DashboardLayout.vue";
import StatCard from "@/components/dashboard/StatCard.vue";
import ActivityList from "@/components/dashboard/ActivityList.vue";
import { checkRole } from "@/utils";

// Security check
checkRole("admin");

const stats = ref({
  total_petugas: 0,
  nasabah_aktif: 0,
  pengepul_terverifikasi: 0,
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

onMounted(() => {
  fetchDashboardData();
});
</script>

<template>
  <DashboardLayout title="Dashboard Admin">
    <div class="space-y-10 animate-in fade-in duration-700">
      <!-- Stats Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <StatCard
          title="Total Petugas"
          :value="stats.total_petugas"
          icon="material-symbols:group-outline"
          icon-class="bg-[#4A7043] text-white"
        />
        <StatCard
          title="Nasabah Aktif"
          :value="stats.nasabah_aktif"
          icon="material-symbols:person-outline"
          icon-class="bg-[#5FA09B] text-white"
        />
        <StatCard
          title="Pengepul Terverifikasi"
          :value="stats.pengepul_terverifikasi"
          icon="material-symbols:verified-user-outline"
          icon-class="bg-[#A86444] text-white"
        />
        <StatCard
          title="Total Gudang"
          :value="stats.total_gudang"
          sub-value="(18 Tukang Bekerja)"
          icon="material-symbols:home-work-outline"
          icon-class="bg-[#707070] text-white"
        />
      </div>

      <!-- Recent Activity -->
      <div class="max-w-4xl">
        <ActivityList :activities="activities" />
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
