<script setup>
import { ref, onMounted, computed } from "vue";
import { Icon } from "@iconify/vue";
import DashboardLayout from "@/layouts/DashboardLayout.vue";
import { checkRole } from "@/utils";

// Security check
checkRole("petugas");

const user = computed(() => {
  try {
    return JSON.parse(sessionStorage.getItem("user") || "{}");
  } catch (e) {
    return {};
  }
});

const stats = ref({
  pickup_requests: "X",
  withdrawal_requests: "X",
  today_transactions: "X",
  today_waste: "X",
});

const reportSummary = ref({
  pickup: { count: "X", value: "X" },
  manual_deposit: { count: "X", value: "X" },
  withdrawal: { count: "X", value: "X" },
});

const activities = ref([]);

const attentionItems = ref([]);

const today = computed(() => {
  const options = { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' };
  return new Date().toLocaleDateString('id-ID', options);
});

</script>

<template>
  <DashboardLayout title="Dashboard Petugas">
    <div class="space-y-8 animate-in fade-in duration-700 pb-10">
      
      <!-- Welcome Card -->
      <div class="bg-[#4A7043] rounded-[2rem] p-8 text-white relative overflow-hidden shadow-xl shadow-green-900/10">
        <div class="relative z-10">
          <h2 class="text-3xl font-black mb-2">Selamat Datang, {{ user.name?.split(' ')[0] || 'Petugas' }}!</h2>
          <p class="text-white/80 font-medium">Kelola semua permintaan nasabah dan pengepul</p>
        </div>
        <!-- Decorative background elements -->
        <div class="absolute top-0 right-0 w-64 h-64 bg-white/5 rounded-full -mr-20 -mt-20 blur-3xl"></div>
        <div class="absolute bottom-0 left-0 w-40 h-40 bg-black/10 rounded-full -ml-10 -mb-10 blur-2xl"></div>
      </div>

      <!-- Stats Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- Pickup Request -->
        <div class="bg-[#3D5A35] rounded-[2rem] p-6 text-white shadow-lg flex flex-col justify-between aspect-square lg:aspect-auto h-48">
          <div class="flex justify-between items-start">
            <div class="p-2.5 bg-white/10 rounded-xl">
              <Icon icon="material-symbols:local-shipping-outline" class="w-6 h-6" />
            </div>
            <Icon icon="material-symbols:trending-up" class="w-5 h-5 text-white/40" />
          </div>
          <div>
            <h3 class="text-4xl font-black mb-1">{{ stats.pickup_requests }}</h3>
            <p class="text-xs font-bold text-white/70 uppercase tracking-wider">Request Penjemputan</p>
          </div>
        </div>

        <!-- Withdrawal Request -->
        <div class="bg-[#5FA09B] rounded-[2rem] p-6 text-white shadow-lg flex flex-col justify-between h-48">
          <div class="flex justify-between items-start">
            <div class="p-2.5 bg-white/10 rounded-xl">
              <Icon icon="material-symbols:account-balance-wallet-outline" class="w-6 h-6" />
            </div>
            <Icon icon="material-symbols:trending-up" class="w-5 h-5 text-white/40" />
          </div>
          <div>
            <h3 class="text-4xl font-black mb-1">{{ stats.withdrawal_requests }}</h3>
            <p class="text-xs font-bold text-white/70 uppercase tracking-wider">Request Penarikan</p>
          </div>
        </div>

        <!-- Transactions Today -->
        <div class="bg-[#8C5230] rounded-[2rem] p-6 text-white shadow-lg flex flex-col justify-between h-48">
          <div class="flex justify-between items-start">
            <div class="p-2.5 bg-white/10 rounded-xl">
              <Icon icon="material-symbols:receipt-long-outline" class="w-6 h-6" />
            </div>
            <Icon icon="material-symbols:trending-up" class="w-5 h-5 text-white/40" />
          </div>
          <div>
            <h3 class="text-4xl font-black mb-1">{{ stats.today_transactions }}</h3>
            <p class="text-xs font-bold text-white/70 uppercase tracking-wider">Transaksi Hari Ini</p>
          </div>
        </div>

        <!-- Total Waste Today -->
        <div class="bg-[#6B6B6B] rounded-[2rem] p-6 text-white shadow-lg flex flex-col justify-between h-48">
          <div class="flex justify-between items-start">
            <div class="p-2.5 bg-white/10 rounded-xl">
              <Icon icon="material-symbols:delete-outline" class="w-6 h-6" />
            </div>
            <Icon icon="material-symbols:trending-up" class="w-5 h-5 text-white/40" />
          </div>
          <div>
            <div class="flex items-baseline gap-2">
              <h3 class="text-4xl font-black mb-1">{{ stats.today_waste }}</h3>
              <span class="text-xl font-bold opacity-60">kg</span>
            </div>
            <p class="text-xs font-bold text-white/70 uppercase tracking-wider">Total Sampah Hari Ini</p>
          </div>
        </div>
      </div>

      <!-- Main Section: Attention & Activities -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        <!-- Left Column: Yang Perlu Perhatian -->
        <div class="lg:col-span-7 space-y-6">
          <div class="flex justify-between items-center">
            <h3 class="text-xl font-black text-stone-800">Yang Perlu Perhatian</h3>
            <span class="text-xs font-bold text-stone-400 uppercase tracking-widest">{{ attentionItems.length }} items</span>
          </div>

          <div class="space-y-4">
            <div v-for="item in attentionItems" :key="item.id" class="bg-white rounded-[2rem] p-6 shadow-sm border border-stone-100 flex flex-col gap-4">
              <div class="flex justify-between items-start">
                <div class="flex items-center gap-3">
                  <div class="p-2 bg-stone-50 rounded-xl text-stone-400">
                    <Icon v-if="item.type.includes('pickup')" icon="material-symbols:local-shipping-outline" class="w-5 h-5" />
                    <Icon v-else icon="material-symbols:account-balance-wallet-outline" class="w-5 h-5" />
                  </div>
                  <div>
                    <h4 class="font-black text-stone-800 leading-none">{{ item.id }}</h4>
                    <p class="text-[10px] font-bold text-stone-400 mt-1 uppercase">{{ item.time }}</p>
                  </div>
                </div>
                <span :class="['text-[10px] font-black px-3 py-1 rounded-full uppercase tracking-wider', 
                  item.status === 'Menunggu' ? 'bg-orange-50 text-orange-600' : 'bg-red-50 text-red-600']">
                  {{ item.status }}
                </span>
              </div>

              <div>
                <p class="font-bold text-stone-800 text-sm">{{ item.name }}</p>
                <div class="flex items-center gap-1.5 mt-1 text-stone-400">
                  <Icon icon="material-symbols:location-on-outline" class="w-3.5 h-3.5" />
                  <p class="text-[11px] font-medium truncate">{{ item.address }}</p>
                </div>
              </div>

              <div class="flex gap-2">
                <button :class="['flex-1 py-3 rounded-2xl text-white text-xs font-black transition-transform active:scale-95 shadow-md', item.color]">
                  {{ item.action }}
                </button>
                <button v-if="item.type === 'withdrawal'" class="px-5 py-3 rounded-2xl bg-stone-50 text-stone-400 text-xs font-black border border-stone-200">
                  Detail
                </button>
              </div>
            </div>
            
            <div v-if="attentionItems.length === 0" class="bg-white rounded-[2rem] p-12 shadow-sm border border-stone-100 flex flex-col items-center justify-center text-center opacity-60">
              <div class="w-16 h-16 bg-stone-50 rounded-full flex items-center justify-center mb-4">
                <Icon icon="material-symbols:check-circle-outline" class="w-8 h-8 text-stone-300" />
              </div>
              <p class="text-stone-400 font-bold text-sm">Tidak ada yang memerlukan perhatian saat ini.</p>
            </div>
          </div>
        </div>

        <!-- Right Column: Aktivitas Terkini -->
        <div class="lg:col-span-5 bg-white rounded-[2.5rem] p-8 shadow-sm border border-stone-100 flex flex-col h-fit">
          <div class="flex items-center gap-3 mb-8">
            <div class="w-1.5 h-6 bg-[#4A7043] rounded-full"></div>
            <h3 class="text-xl font-black text-stone-800">Aktivitas Terkini</h3>
          </div>

          <div class="space-y-8 relative">
            <!-- Timeline Line -->
            <div v-if="activities.length > 0" class="absolute left-[22px] top-2 bottom-2 w-0.5 bg-stone-100"></div>

            <div v-for="activity in activities" :key="activity.id" class="relative flex gap-5 group">
              <div :class="['w-11 h-11 rounded-2xl flex items-center justify-center shrink-0 z-10 transition-transform group-hover:scale-110 shadow-sm', activity.iconBg]">
                <Icon :icon="activity.icon" class="w-6 h-6" />
              </div>
              <div class="flex-1 pt-1">
                <div class="flex justify-between items-start mb-1">
                  <h4 class="font-black text-stone-800 text-sm leading-tight group-hover:text-[#4A7043] transition-colors">{{ activity.title }}</h4>
                  <span class="text-[10px] font-bold text-stone-400 uppercase tracking-tighter">{{ activity.time }}</span>
                </div>
                <p class="text-[11px] font-bold text-stone-400 uppercase tracking-widest leading-none">
                  {{ activity.user }} <span class="mx-1">•</span> {{ activity.ref }}
                </p>
              </div>
            </div>

            <div v-if="activities.length === 0" class="py-10 flex flex-col items-center justify-center text-center opacity-60">
              <div class="w-16 h-16 bg-stone-50 rounded-full flex items-center justify-center mb-4">
                <Icon icon="material-symbols:history" class="w-8 h-8 text-stone-300" />
              </div>
              <p class="text-stone-400 font-bold text-sm">Belum ada aktivitas hari ini.</p>
            </div>
          </div>

          <button class="mt-10 w-full py-4 rounded-2xl bg-stone-50 text-stone-400 text-xs font-black border border-stone-100 hover:bg-stone-100 transition-colors uppercase tracking-widest">
            Lihat Semua Aktivitas
          </button>
        </div>
      </div>

      <!-- Bottom Section: Ringkasan Laporan -->
      <div class="bg-[#4A7043] rounded-[2.5rem] p-8 text-white shadow-2xl shadow-green-900/20">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-10">
          <div class="flex items-center gap-4">
            <div class="p-3 bg-white/10 rounded-2xl">
              <Icon icon="material-symbols:description-outline" class="w-7 h-7" />
            </div>
            <div>
              <h3 class="text-xl font-black leading-none">Ringkasan Laporan Hari Ini</h3>
              <p class="text-[11px] font-bold text-white/60 mt-1 uppercase tracking-widest flex items-center gap-1.5">
                <Icon icon="material-symbols:calendar-today-outline" class="w-3.5 h-3.5" />
                {{ today }}
              </p>
            </div>
          </div>
          <button class="px-6 py-3.5 bg-white text-[#4A7043] rounded-2xl text-xs font-black flex items-center gap-2 shadow-xl hover:-translate-y-1 transition-all">
            Lihat Laporan Lengkap
            <Icon icon="material-symbols:arrow-forward-rounded" class="w-4 h-4" />
          </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <!-- Pickup Summary -->
          <div class="bg-white/10 rounded-[2rem] p-7 border border-white/10 hover:bg-white/15 transition-colors">
            <div class="flex items-center gap-3 mb-6">
              <Icon icon="material-symbols:local-shipping-outline" class="w-5 h-5 text-white/40" />
              <p class="text-[10px] font-black uppercase tracking-widest text-white/40">Penjemputan</p>
            </div>
            <div class="mb-6">
              <h4 class="text-4xl font-black mb-1">{{ reportSummary.pickup.count }}</h4>
              <p class="text-[10px] font-bold text-white/40 uppercase">transaksi selesai</p>
            </div>
            <div class="pt-6 border-t border-white/10">
              <p class="text-[10px] font-black uppercase tracking-widest text-white/40 mb-1">Total Nilai</p>
              <h5 class="text-xl font-black">Rp {{ reportSummary.pickup.value }}</h5>
            </div>
          </div>

          <!-- Manual Deposit Summary -->
          <div class="bg-white/10 rounded-[2rem] p-7 border border-white/10 hover:bg-white/15 transition-colors">
            <div class="flex items-center gap-3 mb-6">
              <Icon icon="material-symbols:delete-outline" class="w-5 h-5 text-white/40" />
              <p class="text-[10px] font-black uppercase tracking-widest text-white/40">Setor Manual</p>
            </div>
            <div class="mb-6">
              <h4 class="text-4xl font-black mb-1">{{ reportSummary.manual_deposit.count }}</h4>
              <p class="text-[10px] font-bold text-white/40 uppercase">transaksi selesai</p>
            </div>
            <div class="pt-6 border-t border-white/10">
              <p class="text-[10px] font-black uppercase tracking-widest text-white/40 mb-1">Total Nilai</p>
              <h5 class="text-xl font-black">Rp {{ reportSummary.manual_deposit.value }}</h5>
            </div>
          </div>

          <!-- Withdrawal Summary -->
          <div class="bg-white/10 rounded-[2rem] p-7 border border-white/10 hover:bg-white/15 transition-colors">
            <div class="flex items-center gap-3 mb-6">
              <Icon icon="material-symbols:account-balance-wallet-outline" class="w-5 h-5 text-white/40" />
              <p class="text-[10px] font-black uppercase tracking-widest text-white/40">Penarikan</p>
            </div>
            <div class="mb-6">
              <h4 class="text-4xl font-black mb-1">{{ reportSummary.withdrawal.count }}</h4>
              <p class="text-[10px] font-bold text-white/40 uppercase">request disetujui</p>
            </div>
            <div class="pt-6 border-t border-white/10">
              <p class="text-[10px] font-black uppercase tracking-widest text-white/40 mb-1">Total Nilai</p>
              <h5 class="text-xl font-black">Rp {{ reportSummary.withdrawal.value }}</h5>
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
