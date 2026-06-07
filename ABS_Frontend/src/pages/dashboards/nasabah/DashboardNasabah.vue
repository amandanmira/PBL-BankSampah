<script setup>
import { ref, onMounted, onUnmounted, inject } from "vue";
import { useRouter } from "vue-router";
import DashboardLayout from "@/layouts/DashboardLayout.vue";
import GreetingCard from "@/components/dashboard/GreetingCard.vue";
import StatCard from "@/components/dashboard/StatCard.vue";
import TransactionChart from "@/components/dashboard/TransactionChart.vue";
import LeaderboardTable from "@/components/dashboard/LeaderboardTable.vue";
import ActivityList from "@/components/dashboard/ActivityList.vue";
import { Icon } from "@iconify/vue";
import { checkRole } from "@/utils";

// Middleware
checkRole("nasabah");

const router = useRouter();
const axios = inject("axios");
const user = ref(JSON.parse(sessionStorage.getItem("user") || "{}"));
const loading = ref(true);
let refreshInterval = null; 

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

const topNasabah = ref([]);
const chartSeries = ref([
  { name: "Volume (kg)", data: [0, 0, 0, 0, 0, 0] },
  { name: "Pendapatan (Rp)", data: [0, 0, 0, 0, 0, 0] },
]);
const chartCategories = ref(["-", "-", "-", "-", "-", "-"]);
const recentActivities = ref([]);
const penjemputanPending = ref([]);

const formatRupiah = (number) => {
  return new Intl.NumberFormat("id-ID", {
    style: "currency",
    currency: "IDR",
    minimumFractionDigits: 0,
  }).format(number);
};

const formatTanggal = (dateStr) => {
  if (!dateStr) return "-";
  return new Date(dateStr).toLocaleDateString("id-ID", {
    day: "numeric",
    month: "long",
    year: "numeric",
  });
};

const fetchData = async (isBackgroundRefresh = false) => {
  if (!isBackgroundRefresh) {
    loading.value = true;
  }
  
  try {
    const response = await axios.get("/api/nasabah/dashboard-stats");
    const data = response.data;

    if (data.user) {
      user.value = { ...user.value, ...data.user };
      sessionStorage.setItem("user", JSON.stringify(user.value));
    }

    stats.value[0].value = formatRupiah(data.stats.saldo_tersedia);
    stats.value[1].value = `${data.stats.total_sampah} kg`;
    stats.value[2].value = data.stats.total_transaksi.toString();

    if (data.chart_data) {
      chartCategories.value = data.chart_data.map(d => d.month);
      chartSeries.value = [
        { name: "Volume (kg)", data: data.chart_data.map(d => d.volume) },
        { name: "Pendapatan (Rp)", data: data.chart_data.map(d => d.income) },
      ];
    }

    topNasabah.value = data.top_nasabah || [];
    recentActivities.value = data.activities || [];
    penjemputanPending.value = data.penjemputan_pending || [];
  } catch (err) {
    console.error("Failed to fetch dashboard stats:", err);
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchData();
  refreshInterval = setInterval(() => {
    fetchData(true);
  }, 30000); 
});

onUnmounted(() => {
  if (refreshInterval) clearInterval(refreshInterval);
});
</script>

<template>
  <DashboardLayout title="Dashboard">
    <div class="space-y-10">
      <GreetingCard :name="user.nama || user.name || 'Nasabah'" />

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

      <section>
        <div v-if="loading" class="h-48 bg-stone-200 animate-pulse rounded-3xl"></div>
        <div v-else class="bg-white rounded-[2rem] p-6 shadow-sm border border-stone-100/80">
          <div class="flex items-center justify-between mb-5">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-[14px] bg-[#E8F0E6] flex items-center justify-center">
                <Icon icon="material-symbols:local-shipping-outline" class="text-[#4A7043] w-5 h-5" />
              </div>
              <div>
                <h2 class="text-lg font-bold text-stone-800">Penjemputan Aktif</h2>
                <p class="text-xs text-stone-400 mt-0.5">Sedang menunggu & dalam proses</p>
              </div>
            </div>
            <button
              @click="router.push('/dashboard-nasabah/sampah-saya')"
              class="inline-flex items-center gap-1.5 text-[11px] font-bold text-white bg-[#4A7043] hover:bg-[#3d5e38] px-4 py-2 rounded-full transition-colors duration-200"
            >
              <Icon icon="material-symbols:open-in-new" class="text-sm" />
              Lihat Semua
            </button>
          </div>

          <div
            v-if="penjemputanPending.length === 0"
            class="flex flex-col items-center justify-center py-10 text-center"
          >
            <div class="p-4 rounded-full bg-stone-50 mb-3">
              <Icon icon="material-symbols:check-circle-outline" class="text-stone-300 text-4xl" />
            </div>
            <p class="text-stone-500 font-bold text-sm">Tidak ada penjemputan aktif</p>
            <p class="text-stone-400 text-xs mt-1">Semua penjemputan sudah selesai</p>
          </div>

          <div v-else class="space-y-4 mt-6">
            <button
              v-for="item in penjemputanPending"
              :key="item.penjemputan_id"
              @click="router.push({ path: '/dashboard-nasabah/sampah-saya', query: { highlight_id: item.penjemputan_id } })"
              class="w-full bg-[#FCFCFC] hover:bg-stone-50 rounded-[20px] p-5 flex items-center justify-between border border-stone-100/80 hover:border-stone-200 transition-all text-left group"
            >
              <div class="flex items-center gap-5">
                
                <div 
                  class="w-14 h-14 rounded-[16px] flex items-center justify-center shrink-0 transition-colors"
                  :class="{
                    'bg-[#F4F6F8] text-[#637381]': item.status === 'menunggu_persetujuan',
                    'bg-[#FFF4E5] text-[#B76E00]': item.status === 'proses',
                    'bg-[#E8F0E6] text-[#4A7043]': item.status !== 'menunggu_persetujuan' && item.status !== 'proses'
                  }"
                >
                  <Icon v-if="item.status === 'menunggu_persetujuan'" icon="material-symbols:schedule-outline" class="w-6 h-6" />
                  <Icon v-else-if="item.status === 'proses'" icon="material-symbols:sync" class="w-6 h-6" />
                  <Icon v-else icon="material-symbols:local-shipping-outline" class="w-6 h-6" />
                </div>
                
                <div class="flex flex-col py-1">
                  <h4 class="font-bold text-stone-800 text-[15px] leading-tight mb-1">Request Penjemputan</h4>
                  <p class="text-[11px] text-stone-400 font-medium">
                    Status: <span class="capitalize font-bold text-stone-600">{{ item.status.replace(/_/g, ' ') }}</span>
                  </p>
                  <p class="text-[11px] text-stone-400 mt-1">{{ formatTanggal(item.created_at) }}</p>
                </div>
              </div>

              <div class="flex flex-col items-end justify-between self-stretch py-1">
                <span class="text-[11px] font-bold text-stone-400">#JMP-{{ String(item.penjemputan_id).padStart(3, '0') }}</span>
                <div class="text-stone-300 group-hover:text-[#4A7043] group-hover:translate-x-1 transition-transform mt-auto">
                  <Icon icon="material-symbols:chevron-right" class="w-5 h-5" />
                </div>
              </div>
            </button>
          </div>
        </div>
      </section>

      <section>
        <div v-if="loading" class="h-80 bg-stone-200 animate-pulse rounded-3xl"></div>
        <TransactionChart v-else :series="chartSeries" :categories="chartCategories" />
      </section>

      <section>
        <div v-if="loading" class="h-80 bg-stone-200 animate-pulse rounded-3xl"></div>
        <LeaderboardTable v-else :data="topNasabah" />
      </section>

      <section>
        <div v-if="loading" class="h-64 bg-stone-200 animate-pulse rounded-3xl"></div>
        <ActivityList v-else :activities="recentActivities" />
      </section>
    </div>
  </DashboardLayout>
</template>