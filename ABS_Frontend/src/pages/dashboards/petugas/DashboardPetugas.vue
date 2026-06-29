<script setup>
import { ref, onMounted, computed, inject } from "vue";
import { Icon } from "@iconify/vue";
import DashboardLayout from "@/layouts/DashboardLayout.vue";
import { checkRole } from "@/utils";
import { useRouter } from "vue-router";

// Security check
checkRole("petugas");

const axios = inject("axios");
const router = useRouter();
const loading = ref(true);

const user = computed(() => {
  try {
    return JSON.parse(sessionStorage.getItem("user") || "{}");
  } catch (e) {
    return {};
  }
});

const stats = ref({
  pickup_requests: 0,
  withdrawal_requests: 0,
  pengepul_requests: 0,
  total_finished: 0,
  today_waste: 0,
});

const reportSummary = ref({
  pickup: { count: 0, value: "0" },
  manual_deposit: { count: 0, value: "0" },
  withdrawal: { count: 0, value: "0" },
});

const activities = ref([]);
const slicedActivities = computed(() => activities.value.slice(0, 5));
const attentionItems = ref([]);

const today = computed(() => {
  const options = { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' };
  return new Date().toLocaleDateString('id-ID', options);
});

// ─────────────────────────────────────────────────────────────────────────────
// PERBAIKAN UTAMA: getRouteTarget sekarang mengirim `id` (integer murni)
// agar halaman tujuan bisa langsung scroll & highlight item tersebut.
// ─────────────────────────────────────────────────────────────────────────────
const getRouteTarget = (item) => {
  try {
    // Ekstrak angka murni dari string ID seperti "REQ-019" → 19
    const rawId = String(item.id || '');
    const numericId = parseInt(rawId.replace(/\D/g, ''), 10) || null;

    const stat = String(item.status || '').toLowerCase();

    if (item.type === 'pickup') {
      // Tentukan tab yang benar berdasarkan status
      let tab = 'Menunggu';
      if (stat.includes('proses') && !stat.includes('menunggu')) tab = 'Diproses';
      if (stat.includes('dijemput') || stat.includes('otw')) tab = 'Dijemput';
      if (stat.includes('perlu_input') || stat.includes('input')) tab = 'Perlu Input Data';
      if (stat.includes('menunggu_persetujuan')) tab = 'Menunggu';

      return {
        path: '/dashboard-petugas/listpenjemputan',
        query: {
          filter: tab,
          id: numericId || '',   // ← ID integer untuk highlight
        }
      };
    } else {
      // Transaksi penarikan
      let tab = 'menunggu';
      if (stat.includes('selesai')) tab = 'selesai';
      if (stat.includes('tolak') || stat.includes('batal')) tab = 'ditolak';

      return {
        path: '/dashboard-petugas/listpenarikan',
        query: {
          filter: tab,
          id: numericId || '',
        }
      };
    }
  } catch (error) {
    console.error("Routing error:", error);
    return { path: '/dashboard-petugas' };
  }
};

const fetchData = async () => {
  loading.value = true;
  try {
    const responseStats = await axios.get("/api/petugas/dashboard-stats");
    const data = responseStats.data;

    const responsePenjemputan = await axios.get("/api/petugas/penjemputan");
    const validPenjemputans = responsePenjemputan.data.data || [];
    const validPickupIds = validPenjemputans.map(p => p.penjemputan_id);

    const filteredAttention = data.attention_items.filter(item => {
      if (item.type === 'pickup') {
        const idMurni = parseInt(item.id.replace(/\D/g, ''), 10);
        return validPickupIds.includes(idMurni);
      }
      return true;
    });

    stats.value = data.stats;
    attentionItems.value = filteredAttention;
    activities.value = data.activities;
    reportSummary.value = data.report_summary;

  } catch (err) {
    console.error("Failed to fetch petugas dashboard stats:", err);
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchData();
});
</script>

<template>
  <DashboardLayout title="Dashboard Petugas">
    <div class="space-y-6 md:space-y-8 animate-in fade-in duration-1000 pb-20 px-4 sm:px-6 lg:px-10">

      <div class="bg-[#4A7043] rounded-[1.5rem] p-6 sm:p-8 text-white relative overflow-hidden shadow-2xl shadow-green-900/20">
        <div class="relative z-10">
          <h2 class="text-2xl sm:text-3xl font-black mb-2 tracking-tight">Selamat Datang, {{ user.name?.split(' ')[0] || 'Petugas' }}!</h2>
          <p class="text-white/70 text-sm sm:text-base font-medium">Kelola semua operasional bank sampah hari ini</p>
        </div>
        <div class="absolute top-0 right-0 w-80 h-80 bg-white/5 rounded-full -mr-32 -mt-32 blur-3xl"></div>
        <div class="absolute bottom-0 left-0 w-64 h-64 bg-black/10 rounded-full -ml-20 -mb-20 blur-2xl"></div>
      </div>

      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">
        <router-link to="/dashboard-petugas/listpenjemputan" class="bg-[#3D5A35] rounded-[1.25rem] sm:rounded-[1.5rem] p-4 sm:p-6 md:p-8 text-white shadow-xl flex flex-col justify-between h-40 sm:h-48 md:h-56 transition-all hover:-translate-y-2 hover:shadow-2xl group">
          <div class="flex justify-between items-start">
            <div class="p-2 sm:p-3 bg-white/10 rounded-xl sm:rounded-2xl backdrop-blur-md group-hover:scale-110 transition-transform">
              <Icon icon="material-symbols:local-shipping-outline" class="w-6 h-6 sm:w-8 sm:h-8" />
            </div>
            <Icon icon="material-symbols:trending-up" class="w-4 h-4 sm:w-6 sm:h-6 text-white/30" />
          </div>
          <div>
            <h3 v-if="loading" class="h-8 sm:h-12 w-16 sm:w-20 bg-white/10 animate-pulse rounded-lg sm:rounded-xl mb-1 sm:mb-2"></h3>
            <h3 v-else class="text-3xl sm:text-4xl md:text-5xl font-black mb-0.5 sm:mb-1 tracking-tighter">{{ stats.pickup_requests }}</h3>
            <p class="text-[10px] sm:text-xs font-bold text-white/60 uppercase tracking-widest leading-none">Penjemputan Hari Ini</p>
          </div>
        </router-link>

        <router-link to="/dashboard-petugas/listpenarikan" class="bg-[#5FA09B] rounded-[1.25rem] sm:rounded-[1.5rem] p-4 sm:p-6 md:p-8 text-white shadow-xl flex flex-col justify-between h-40 sm:h-48 md:h-56 transition-all hover:-translate-y-2 hover:shadow-2xl group">
          <div class="flex justify-between items-start">
            <div class="p-2 sm:p-3 bg-white/10 rounded-xl sm:rounded-2xl backdrop-blur-md group-hover:scale-110 transition-transform">
              <Icon icon="material-symbols:account-balance-wallet-outline" class="w-6 h-6 sm:w-8 sm:h-8" />
            </div>
            <Icon icon="material-symbols:trending-up" class="w-4 h-4 sm:w-6 sm:h-6 text-white/30" />
          </div>
          <div>
            <h3 v-if="loading" class="h-8 sm:h-12 w-16 sm:w-20 bg-white/10 animate-pulse rounded-lg sm:rounded-xl mb-1 sm:mb-2"></h3>
            <h3 v-else class="text-3xl sm:text-4xl md:text-5xl font-black mb-0.5 sm:mb-1 tracking-tighter">{{ stats.withdrawal_requests }}</h3>
            <p class="text-[10px] sm:text-xs font-bold text-white/60 uppercase tracking-widest leading-none">Penarikan Hari Ini</p>
          </div>
        </router-link>

        <router-link to="/dashboard-petugas/penimbangan" class="bg-[#8C5230] rounded-[1.25rem] sm:rounded-[1.5rem] p-4 sm:p-6 md:p-8 text-white shadow-xl flex flex-col justify-between h-40 sm:h-48 md:h-56 transition-all hover:-translate-y-2 hover:shadow-2xl group">
          <div class="flex justify-between items-start">
            <div class="p-2 sm:p-3 bg-white/10 rounded-xl sm:rounded-2xl backdrop-blur-md group-hover:scale-110 transition-transform">
              <Icon icon="material-symbols:package-2-outline" class="w-6 h-6 sm:w-8 sm:h-8" />
            </div>
            <Icon icon="material-symbols:trending-up" class="w-4 h-4 sm:w-6 sm:h-6 text-white/30" />
          </div>
          <div>
            <h3 v-if="loading" class="h-8 sm:h-12 w-16 sm:w-20 bg-white/10 animate-pulse rounded-lg sm:rounded-xl mb-1 sm:mb-2"></h3>
            <h3 v-else class="text-3xl sm:text-4xl md:text-5xl font-black mb-0.5 sm:mb-1 tracking-tighter">{{ stats.pengepul_requests }}</h3>
            <p class="text-[10px] sm:text-xs font-bold text-white/60 uppercase tracking-widest leading-none">Pesanan Pengepul</p>
          </div>
        </router-link>

        <div class="bg-[#6B6B6B] rounded-[1.25rem] sm:rounded-[1.5rem] p-4 sm:p-6 md:p-8 text-white shadow-xl flex flex-col justify-between h-40 sm:h-48 md:h-56 group border border-white/5">
          <div class="flex justify-between items-start">
            <div class="p-2 sm:p-3 bg-white/10 rounded-xl sm:rounded-2xl backdrop-blur-md group-hover:rotate-12 transition-transform">
              <Icon icon="material-symbols:check-circle-outline" class="w-6 h-6 sm:w-8 sm:h-8" />
            </div>
            <Icon icon="material-symbols:verified-outline" class="w-4 h-4 sm:w-6 sm:h-6 text-white/30" />
          </div>
          <div>
            <h3 v-if="loading" class="h-8 sm:h-12 w-16 sm:w-20 bg-white/10 animate-pulse rounded-lg sm:rounded-xl mb-1 sm:mb-2"></h3>
            <h3 v-else class="text-3xl sm:text-4xl md:text-5xl font-black mb-0.5 sm:mb-1 tracking-tighter">{{ stats.total_finished }}</h3>
            <p class="text-[10px] sm:text-xs font-bold text-white/60 uppercase tracking-widest leading-tight">Total Transaksi Selesai</p>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-8">

        <div class="space-y-6 md:space-y-8">
          <div class="flex justify-between items-end px-2 sm:px-4">
            <h3 class="text-xl sm:text-2xl font-black text-stone-800">Yang Perlu Perhatian</h3>
            <span v-if="!loading" class="text-xs sm:text-sm font-bold text-stone-400 uppercase tracking-widest">{{ attentionItems.length }} items</span>
          </div>

          <div class="space-y-4">
            <div v-if="loading" v-for="i in 3" :key="i" class="h-40 sm:h-48 bg-white animate-pulse rounded-[1.25rem] sm:rounded-[1.5rem] border border-stone-100 shadow-sm"></div>

            <template v-else>
              <div
                v-for="item in attentionItems"
                :key="item.id"
                class="bg-white rounded-[1.25rem] sm:rounded-[1.5rem] p-4 sm:p-6 shadow-sm border border-stone-100 flex flex-col gap-4 sm:gap-6 transition-all hover:shadow-xl hover:-translate-y-1"
              >
                <div class="flex justify-between items-start gap-2">
                  <div class="flex items-center gap-3 sm:gap-4">
                    <div class="w-12 h-12 sm:w-14 sm:h-14 bg-stone-50 rounded-xl flex items-center justify-center text-stone-300 shadow-inner shrink-0">
                      <Icon v-if="item.type === 'pickup'" icon="material-symbols:local-shipping-outline" class="w-6 h-6 sm:w-7 sm:h-7" />
                      <Icon v-else icon="material-symbols:account-balance-wallet-outline" class="w-6 h-6 sm:w-7 sm:h-7" />
                    </div>
                    <div>
                      <h4 class="font-black text-stone-800 text-base sm:text-lg leading-none uppercase tracking-tight">{{ item.id }}</h4>
                      <p class="text-[10px] sm:text-xs font-bold text-stone-500 mt-1.5 sm:mt-2">{{ item.time }}</p>
                    </div>
                  </div>
                  <span :class="['text-[9px] sm:text-[10px] font-black px-3 py-1.5 sm:px-4 sm:py-2 rounded-full uppercase tracking-widest shrink-0 text-center',
                    item.status === 'Menunggu' ? 'bg-[#FFF9E6] text-[#D9A300]' : 'bg-[#FFF2EB] text-[#E67E22]']">
                    {{ item.status }}
                  </span>
                </div>

                <div class="px-1">
                  <p class="font-black text-stone-800 text-lg sm:text-xl leading-tight">{{ item.name }}</p>
                  <div class="flex items-center gap-2 mt-2 text-stone-400">
                    <Icon v-if="item.type === 'pickup'" icon="material-symbols:location-on-outline" class="w-4 h-4 shrink-0" />
                    <Icon v-else icon="material-symbols:payments-outline" class="w-4 h-4 shrink-0" />
                    <p class="text-xs sm:text-sm font-medium truncate">{{ item.address }}</p>
                  </div>
                </div>

                <div class="flex gap-2 sm:gap-3 pt-1">
                  <!-- Tombol aksi utama — langsung bawa ke item yang tepat -->
                  <router-link
                    :to="getRouteTarget(item)"
                    :class="['flex-1 py-3 sm:py-4 rounded-xl text-white text-xs sm:text-sm text-center font-black transition-all active:scale-95 shadow-xl shadow-stone-200 block', item.color]"
                  >
                    {{ item.action }} <span class="ml-1 text-white/50">›</span>
                  </router-link>
                  <!-- Tombol "Detail" terpisah untuk withdrawal -->
                  <router-link
                    v-if="item.type === 'withdrawal'"
                    :to="getRouteTarget(item)"
                    class="px-4 sm:px-8 py-3 sm:py-4 rounded-xl bg-white text-stone-400 text-xs sm:text-sm font-black border border-stone-200 text-center hover:bg-stone-50 transition-colors block"
                  >
                    Detail
                  </router-link>
                </div>
              </div>

              <div v-if="attentionItems.length === 0" class="bg-white rounded-[1.25rem] sm:rounded-[1.5rem] p-8 sm:p-16 shadow-sm border border-stone-100 flex flex-col items-center justify-center text-center">
                <div class="w-16 h-16 sm:w-20 sm:h-20 bg-stone-50 rounded-full flex items-center justify-center mb-4 sm:mb-6">
                  <Icon icon="material-symbols:check-circle-outline" class="w-8 h-8 sm:w-10 sm:h-10 text-stone-200" />
                </div>
                <p class="text-stone-400 text-sm sm:text-base font-bold">Semua beres! Tidak ada yang perlu perhatian.</p>
              </div>
            </template>
          </div>
        </div>

        <div class="bg-white rounded-[1.5rem] sm:rounded-[2rem] p-5 sm:p-8 shadow-xl shadow-stone-200/50 border border-stone-50 flex flex-col">
          <div class="flex items-center gap-2 sm:gap-3 mb-6 sm:mb-8 px-1">
            <div class="w-1 sm:w-1.5 h-5 sm:h-6 bg-[#5FA09B] rounded-full"></div>
            <h3 class="text-lg sm:text-xl font-black text-stone-800 uppercase tracking-tighter">Aktivitas Terkini</h3>
          </div>

          <div class="flex-1 space-y-4 sm:space-y-8 relative px-1">
            <div v-if="slicedActivities.length > 0 && !loading" class="absolute left-[16px] sm:left-[24px] top-4 bottom-4 w-[1px] sm:w-[1.5px] bg-stone-50"></div>

            <div v-if="loading" v-for="i in 5" :key="i" class="flex gap-3 sm:gap-6">
              <div class="w-8 h-8 sm:w-12 sm:h-12 bg-stone-50 animate-pulse rounded-full shrink-0"></div>
              <div class="flex-1 space-y-1.5 sm:space-y-3">
                <div class="h-3.5 sm:h-5 w-3/4 bg-stone-50 animate-pulse rounded-lg"></div>
                <div class="h-2 sm:h-3 w-1/2 bg-stone-50 animate-pulse rounded-lg"></div>
              </div>
            </div>

            <template v-else>
              <div v-for="activity in slicedActivities" :key="activity.id" class="relative flex gap-3 sm:gap-8 group">
                <div :class="['w-8 h-8 sm:w-12 sm:h-12 rounded-full flex items-center justify-center shrink-0 z-10 transition-all group-hover:scale-110 shadow-md border-2 sm:border-[4px] border-white',
                  activity.iconBg.replace('rounded-2xl', 'rounded-full')]">
                  <Icon :icon="activity.icon" class="w-4 h-4 sm:w-6 sm:h-6" />
                </div>
                <div class="flex-1 min-w-0">
                  <div class="flex justify-between items-start gap-3">
                    <div class="pt-0.5 min-w-0">
                      <h4 class="font-black text-stone-800 text-sm sm:text-lg leading-tight group-hover:text-[#4A7043] transition-colors truncate">{{ activity.title }}</h4>
                      <p class="text-[9px] sm:text-[10px] font-bold text-stone-400 mt-1 uppercase tracking-[0.1em] truncate">
                        {{ activity.user }} <span class="mx-1.5 text-stone-100">•</span> {{ activity.ref }}
                      </p>
                    </div>
                    <span class="text-[9px] sm:text-xs font-black text-stone-500 pt-1 sm:pt-1.5 shrink-0">{{ activity.time }}</span>
                  </div>
                </div>
              </div>

              <div v-if="slicedActivities.length === 0" class="py-16 flex flex-col items-center justify-center text-center opacity-30">
                <Icon icon="material-symbols:history" class="w-14 h-14 text-stone-100 mb-5" />
                <p class="text-stone-400 text-base font-bold">Belum ada aktivitas hari ini.</p>
              </div>
            </template>
          </div>

          <router-link to="/dashboard-petugas/riwayat" class="mt-8 sm:mt-12 w-full py-4 sm:py-5 rounded-2xl bg-stone-50 text-stone-400 text-[9px] sm:text-[10px] font-black border border-stone-100 hover:bg-stone-100 hover:text-stone-700 transition-all uppercase tracking-[0.2em] sm:tracking-[0.3em] text-center shadow-inner">
            Lihat Semua Aktivitas
          </router-link>
        </div>
      </div>

      <div class="bg-[#4A7043] rounded-[1.5rem] sm:rounded-[2.5rem] p-6 sm:p-10 text-white shadow-2xl shadow-green-900/20">
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6 mb-8 sm:mb-12">
          <div class="flex items-center gap-4 sm:gap-5">
            <div class="p-3 sm:p-4 bg-white/10 rounded-xl sm:rounded-2xl backdrop-blur-md border border-white/5">
              <Icon icon="material-symbols:description-outline" class="w-6 h-6 sm:w-8 sm:h-8 text-white/80" />
            </div>
            <div>
              <h3 class="text-xl sm:text-2xl font-black leading-none">Ringkasan Laporan Hari Ini</h3>
              <p class="text-[10px] sm:text-xs font-bold text-white/40 mt-1.5 sm:mt-2 uppercase tracking-widest flex items-center gap-2">
                <Icon icon="material-symbols:calendar-today-outline" class="w-4 h-4" />
                {{ today }}
              </p>
            </div>
          </div>
          <router-link to="/dashboard-petugas/laporan-harian" class="w-full lg:w-auto px-6 sm:px-8 py-3.5 sm:py-4 bg-white text-[#4A7043] rounded-xl sm:rounded-2xl text-xs sm:text-sm font-black flex items-center justify-center gap-2 shadow-xl hover:-translate-y-1 transition-all">
            Lihat Laporan Lengkap
            <Icon icon="material-symbols:arrow-forward-rounded" class="w-4 h-4 sm:w-5 sm:h-5" />
          </router-link>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 md:gap-6 lg:gap-8">
          <div class="bg-white/5 rounded-[1.5rem] sm:rounded-[2rem] p-5 sm:p-8 border border-white/10 backdrop-blur-md transition-all hover:bg-white/10">
            <div class="flex items-center gap-2.5 sm:gap-3 mb-6 sm:mb-8">
              <div class="p-2 bg-white/10 rounded-lg sm:rounded-xl">
                <Icon icon="material-symbols:local-shipping-outline" class="w-4.5 h-4.5 sm:w-5 sm:h-5 text-white/60" />
              </div>
              <p class="text-[10px] sm:text-xs font-black uppercase tracking-widest text-white/40">Penjemputan</p>
            </div>
            <div class="mb-6 sm:mb-8">
              <h4 v-if="loading" class="h-10 sm:h-12 w-16 sm:w-20 bg-white/10 animate-pulse rounded-lg mb-2"></h4>
              <h4 v-else class="text-4xl sm:text-5xl font-black mb-1 leading-none">{{ reportSummary.pickup.count }}</h4>
              <p class="text-[10px] sm:text-xs font-bold text-white/30 uppercase tracking-tighter">transaksi selesai</p>
            </div>
            <div class="pt-6 sm:pt-8 border-t border-white/10">
              <p class="text-[10px] sm:text-xs font-black uppercase tracking-widest text-white/40 mb-1.5 sm:mb-2">Total Nilai</p>
              <h5 v-if="loading" class="h-6 sm:h-8 w-28 sm:w-32 bg-white/10 animate-pulse rounded-lg"></h5>
              <h5 v-else class="text-xl sm:text-2xl font-black">Rp {{ reportSummary.pickup.value }}</h5>
            </div>
          </div>

          <div class="bg-white/5 rounded-[1.5rem] sm:rounded-[2rem] p-5 sm:p-8 border border-white/10 backdrop-blur-md transition-all hover:bg-white/10">
            <div class="flex items-center gap-2.5 sm:gap-3 mb-6 sm:mb-8">
              <div class="p-2 bg-white/10 rounded-lg sm:rounded-xl">
                <Icon icon="material-symbols:storefront-outline" class="w-4.5 h-4.5 sm:w-5 sm:h-5 text-white/60" />
              </div>
              <p class="text-[10px] sm:text-xs font-black uppercase tracking-widest text-white/40">Setor Manual</p>
            </div>
            <div class="mb-6 sm:mb-8">
              <h4 v-if="loading" class="h-10 sm:h-12 w-16 sm:w-20 bg-white/10 animate-pulse rounded-lg mb-2"></h4>
              <h4 v-else class="text-4xl sm:text-5xl font-black mb-1 leading-none">{{ reportSummary.manual_deposit.count }}</h4>
              <p class="text-[10px] sm:text-xs font-bold text-white/30 uppercase tracking-tighter">transaksi selesai</p>
            </div>
            <div class="pt-6 sm:pt-8 border-t border-white/10">
              <p class="text-[10px] sm:text-xs font-black uppercase tracking-widest text-white/40 mb-1.5 sm:mb-2">Total Nilai</p>
              <h5 v-if="loading" class="h-6 sm:h-8 w-28 sm:w-32 bg-white/10 animate-pulse rounded-lg"></h5>
              <h5 v-else class="text-xl sm:text-2xl font-black">Rp {{ reportSummary.manual_deposit.value }}</h5>
            </div>
          </div>

          <div class="bg-white/5 rounded-[1.5rem] sm:rounded-[2rem] p-5 sm:p-8 border border-white/10 backdrop-blur-md transition-all hover:bg-white/10">
            <div class="flex items-center gap-2.5 sm:gap-3 mb-6 sm:mb-8">
              <div class="p-2 bg-white/10 rounded-lg sm:rounded-xl">
                <Icon icon="material-symbols:account-balance-wallet-outline" class="w-4.5 h-4.5 sm:w-5 sm:h-5 text-white/60" />
              </div>
              <p class="text-[10px] sm:text-xs font-black uppercase tracking-widest text-white/40">Penarikan</p>
            </div>
            <div class="mb-6 sm:mb-8">
              <h4 v-if="loading" class="h-10 sm:h-12 w-16 sm:w-20 bg-white/10 animate-pulse rounded-lg mb-2"></h4>
              <h4 v-else class="text-4xl sm:text-5xl font-black mb-1 leading-none">{{ reportSummary.withdrawal.count }}</h4>
              <p class="text-[10px] sm:text-xs font-bold text-white/30 uppercase tracking-tighter">request disetujui</p>
            </div>
            <div class="pt-6 sm:pt-8 border-t border-white/10">
              <p class="text-[10px] sm:text-xs font-black uppercase tracking-widest text-white/40 mb-1.5 sm:mb-2">Total Nilai</p>
              <h5 v-if="loading" class="h-6 sm:h-8 w-28 sm:w-32 bg-white/10 animate-pulse rounded-lg"></h5>
              <h5 v-else class="text-xl sm:text-2xl font-black">Rp {{ reportSummary.withdrawal.value }}</h5>
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