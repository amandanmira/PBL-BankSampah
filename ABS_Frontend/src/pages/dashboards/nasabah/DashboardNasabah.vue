<script setup>
import { ref, onMounted, inject } from "vue";
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

// Penjemputan Menunggu Persetujuan
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

const getCatatan = (deskripsi) => {
  if (!deskripsi) return "Tidak ada catatan";
  if (deskripsi.includes("|")) {
    return deskripsi.split("|")[2] || "Tidak ada catatan";
  }
  return deskripsi;
};

const fetchData = async () => {
  loading.value = true;
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

    // Aktivitas Terbaru
    recentActivities.value = data.activities;

    // Penjemputan Pending
    penjemputanPending.value = data.penjemputan_pending || [];
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

      <!-- Penjemputan Aktif -->
      <section>
        <div v-if="loading" class="h-48 bg-stone-200 animate-pulse rounded-3xl"></div>
        <div v-else class="bg-white rounded-3xl p-6 shadow-sm border border-stone-100">
          <!-- Header -->
          <div class="flex items-center justify-between mb-5">
            <div class="flex items-center gap-3">
              <div class="p-2.5 rounded-2xl bg-[#E8F0E6]">
                <Icon icon="material-symbols:local-shipping-outline" class="text-[#4A7043] text-xl" />
              </div>
              <div>
                <h2 class="text-lg font-bold text-stone-800">Penjemputan Aktif</h2>
                <p class="text-xs text-stone-400 mt-0.5">Sedang menunggu & dalam proses</p>
              </div>
            </div>
            <button
              @click="router.push('/dashboard-nasabah/sampah-saya')"
              class="inline-flex items-center gap-1.5 text-xs font-semibold text-white bg-[#4A7043] hover:bg-[#3d5e38] px-3 py-1.5 rounded-full transition-colors duration-200"
            >
              <Icon icon="material-symbols:open-in-new" class="text-sm" />
              Lihat Semua
            </button>
          </div>

          <!-- Empty State -->
          <div
            v-if="penjemputanPending.length === 0"
            class="flex flex-col items-center justify-center py-10 text-center"
          >
            <div class="p-4 rounded-full bg-stone-100 mb-3">
              <Icon icon="material-symbols:check-circle-outline" class="text-stone-400 text-3xl" />
            </div>
            <p class="text-stone-500 font-medium text-sm">Tidak ada penjemputan aktif</p>
            <p class="text-stone-400 text-xs mt-1">Semua penjemputan sudah selesai</p>
          </div>

          <!-- List -->
          <div v-else class="space-y-3">
            <div
              v-for="item in penjemputanPending"
              :key="item.penjemputan_id"
              class="rounded-2xl border overflow-hidden transition-all duration-200"
              :class="{
                'border-indigo-100 bg-indigo-50/40': item.status === 'menunggu_persetujuan',
                'border-amber-100 bg-amber-50/40': item.status === 'proses',
                'border-[#4A7043]/20 bg-[#E8F0E6]/40': item.status === 'dijemput' || item.status === 'perlu_input',
              }"
            >
              <!-- Status Banner -->
              <div
                class="px-4 py-2 flex items-center gap-2"
                :class="{
                  'bg-indigo-100/60': item.status === 'menunggu_persetujuan',
                  'bg-amber-100/60': item.status === 'proses',
                  'bg-[#4A7043]/10': item.status === 'dijemput' || item.status === 'perlu_input',
                }"
              >
                <Icon
                  :icon="
                    item.status === 'menunggu_persetujuan' ? 'material-symbols:pending-actions-outline'
                    : item.status === 'proses' ? 'material-symbols:sync'
                    : 'material-symbols:local-shipping'
                  "
                  class="text-sm shrink-0"
                  :class="{
                    'text-indigo-600': item.status === 'menunggu_persetujuan',
                    'text-amber-600': item.status === 'proses',
                    'text-[#4A7043]': item.status === 'dijemput' || item.status === 'perlu_input',
                  }"
                />
                <span
                  class="text-xs font-bold"
                  :class="{
                    'text-indigo-700': item.status === 'menunggu_persetujuan',
                    'text-amber-700': item.status === 'proses',
                    'text-[#4A7043]': item.status === 'dijemput' || item.status === 'perlu_input',
                  }"
                >
                  {{
                    item.status === 'menunggu_persetujuan' ? 'Menunggu Persetujuan Jadwal'
                    : item.status === 'proses' ? 'Sedang Diproses'
                    : 'Petugas Sedang Menuju Alamatmu'
                  }}
                </span>
                <span class="ml-auto text-[10px] text-stone-400 font-medium">#JMP-{{ String(item.penjemputan_id).padStart(3, '0') }}</span>
              </div>

              <!-- Body -->
              <div class="p-4 space-y-3">
                <!-- Info baris: lokasi & tanggal -->
                <div class="flex flex-wrap gap-x-4 gap-y-1">
                  <p class="text-xs text-stone-500 flex items-center gap-1">
                    <Icon icon="material-symbols:location-on-outline" class="text-stone-400 text-sm shrink-0" />
                    <span class="truncate max-w-[220px]">{{ item.alamat || "-" }}</span>
                  </p>
                  <p class="text-xs text-stone-400 flex items-center gap-1">
                    <Icon icon="material-symbols:calendar-today-outline" class="text-stone-400 text-sm" />
                    {{ formatTanggal(item.created_at) }}
                  </p>
                </div>

                <!-- Info menunggu persetujuan -->
                <div
                  v-if="item.status === 'menunggu_persetujuan'"
                  class="flex items-start gap-2 bg-indigo-50 rounded-xl p-3 border border-indigo-100"
                >
                  <Icon icon="material-symbols:info" class="text-indigo-500 text-base shrink-0 mt-0.5" />
                  <p class="text-xs font-semibold text-indigo-700">
                    Silakan setujui jadwal penjemputan Anda untuk diproses lebih lanjut.
                  </p>
                </div>

                <!-- Jadwal penjemputan (proses/dijemput) -->
                <div
                  v-if="item.jadwal && item.status !== 'menunggu_persetujuan'"
                  class="flex items-center gap-2 bg-white rounded-xl p-3 border border-stone-100"
                >
                  <Icon icon="material-symbols:calendar-month-outline" class="text-[#4A7043] text-base shrink-0" />
                  <div>
                    <p class="text-[10px] text-stone-400 font-semibold uppercase tracking-wide">Jadwal Penjemputan</p>
                    <p class="text-xs font-bold text-[#4A7043]">{{ formatTanggal(item.jadwal) }}</p>
                  </div>
                </div>

                <!-- Info petugas (dijemput) -->
                <div
                  v-if="(item.status === 'dijemput' || item.status === 'perlu_input') && item.tukang"
                  class="flex items-center gap-3 bg-white rounded-xl p-3 border border-stone-100"
                >
                  <div class="w-9 h-9 rounded-full overflow-hidden bg-stone-100 border border-stone-200 shrink-0">
                    <img
                      v-if="item.tukang.foto"
                      :src="`http://localhost:8000/storage/${item.tukang.foto}`"
                      class="w-full h-full object-cover"
                    />
                    <div v-else class="w-full h-full flex items-center justify-center">
                      <Icon icon="material-symbols:person-outline" class="text-stone-400 text-lg" />
                    </div>
                  </div>
                  <div class="flex-1 min-w-0">
                    <p class="text-[10px] text-stone-400 font-semibold uppercase tracking-wide">Tukang</p>
                    <p class="text-xs font-bold text-stone-800 truncate">{{ item.tukang.nama }}</p>
                    <p class="text-[10px] text-stone-500">{{ item.tukang.no_telp }}</p>
                  </div>
                  <a
                    :href="`https://wa.me/${item.tukang.no_telp.replace(/^0/, '62')}`"
                    target="_blank"
                    class="w-8 h-8 bg-[#25D366] text-white rounded-full flex items-center justify-center hover:scale-105 active:scale-95 transition-all shadow-sm shrink-0"
                  >
                    <Icon icon="logos:whatsapp-icon" class="text-base" />
                  </a>
                </div>

                <!-- Tombol aksi -->
                <button
                  @click="router.push({ path: '/dashboard-nasabah/sampah-saya', query: { highlight_id: item.penjemputan_id } })"
                  class="w-full py-2.5 rounded-xl text-xs font-bold flex items-center justify-center gap-2 transition-all active:scale-[0.98]"
                  :class="{
                    'bg-indigo-600 hover:bg-indigo-700 text-white': item.status === 'menunggu_persetujuan',
                    'bg-amber-500 hover:bg-amber-600 text-white': item.status === 'proses',
                    'bg-[#4A7043] hover:bg-[#3d5e38] text-white': item.status === 'dijemput' || item.status === 'perlu_input',
                  }"
                >
                  <span>{{
                    item.status === 'menunggu_persetujuan' ? 'Lihat & Setujui Jadwal'
                    : item.status === 'proses' ? 'Lihat Detail'
                    : 'Lihat Detail'
                  }}</span>
                  <Icon icon="material-symbols:arrow-right-alt" class="text-base" />
                </button>
              </div>
            </div>
          </div>
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