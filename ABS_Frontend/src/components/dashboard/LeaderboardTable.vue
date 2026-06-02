<script setup>
import { Icon } from "@iconify/vue";
import numeral from "numeral";

const props = defineProps({
  data: { type: Array, required: true },
});

const currentUser = JSON.parse(sessionStorage.getItem("user") || "{}");

const isCurrentUser = (item) => {
  if (!item || !currentUser) return false;
  return item.nama === currentUser.nama || item.username === currentUser.username;
};

const getRankIcon = (rank) => {
  if (rank === 1) return { icon: "twemoji:trophy", color: "text-yellow-500" };
  if (rank === 2) return { icon: "twemoji:medal-silver", color: "text-stone-400" };
  if (rank === 3) return { icon: "twemoji:medal-bronze", color: "text-orange-600" };
  return { icon: "material-symbols:star-outline", color: "text-yellow-400" };
};
</script>

<template>
  <div class="bg-white rounded-[2rem] overflow-hidden shadow-sm border border-stone-100/50">
    <div class="p-6 border-b border-stone-50 flex items-center gap-3">
       <Icon icon="twemoji:trophy" class="w-6 h-6" />
       <h3 class="text-lg font-bold text-stone-800">Top Nasabah Bulan Ini</h3>
    </div>
    
    <div v-if="data.length > 0">
      <!-- Desktop Table View -->
      <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-left">
          <thead>
            <tr class="bg-stone-50/50 text-stone-500 text-xs font-bold uppercase tracking-wider">
              <th class="px-6 py-4">Peringkat</th>
              <th class="px-6 py-4">Nasabah</th>
              <th class="px-6 py-4 text-center">Total Sampah</th>
              <th class="px-6 py-4 text-center">Transaksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-stone-50">
            <tr v-for="item in data" :key="item.rank" :class="['hover:bg-stone-50/30 transition-colors', isCurrentUser(item) ? 'bg-green-50/40' : '']">
              <td class="px-6 py-4">
                <div class="flex items-center gap-3">
                  <Icon :icon="getRankIcon(item.rank).icon" class="w-6 h-6" />
                  <span class="font-bold text-stone-600">#{{ item.rank }}</span>
                </div>
              </td>
              <td class="px-6 py-4">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 rounded-full bg-stone-100 flex items-center justify-center text-stone-400">
                    <Icon icon="material-symbols:person" class="w-6 h-6" />
                  </div>
                  <div class="flex items-center gap-2">
                    <span class="font-bold text-stone-700">{{ item.nama }}</span>
                    <span v-if="isCurrentUser(item)" class="bg-[#4D8C57] text-white text-[10px] px-2 py-0.5 rounded-full font-bold">Anda</span>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 text-center font-bold text-[#4A7043]">
                {{ numeral(item.total_sampah).format('0,0.[0]') }} kg
              </td>
              <td class="px-6 py-4 text-center font-medium text-stone-600">
                {{ item.total_transaksi }}x
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Mobile List View (Matching nasabah - dashboard.png) -->
      <div class="block md:hidden divide-y divide-stone-100">
        <div 
          v-for="item in data" 
          :key="item.rank" 
          :class="['p-4 flex items-center justify-between hover:bg-stone-50/50 transition-colors', isCurrentUser(item) ? 'bg-stone-50/70' : '']"
        >
          <div class="flex items-center gap-3">
            <!-- Rank Flag Badge -->
            <div class="relative flex items-center justify-center">
              <div
                v-if="item.rank <= 3"
                :class="[
                  'w-8 h-10 flex flex-col items-center justify-center text-white font-black text-[11px] relative',
                  item.rank === 1 ? 'bg-[#4A7043]' : '',
                  item.rank === 2 ? 'bg-[#2E8B9A]' : '',
                  item.rank === 3 ? 'bg-[#B5653C]' : ''
                ]"
                style="clip-path: polygon(0% 0%, 100% 0%, 100% 85%, 50% 100%, 0% 85%)"
              >
                #{{ item.rank }}
              </div>
              <div
                v-else
                class="w-8 h-8 rounded-lg bg-[#C49F16] flex items-center justify-center text-white font-black text-[11px]"
              >
                #{{ item.rank }}
              </div>
            </div>

            <!-- Avatar (Initial matching the user's name or placeholder) -->
            <div class="w-10 h-10 rounded-full overflow-hidden bg-stone-100 border border-stone-200/50 flex items-center justify-center shrink-0">
              <div class="w-full h-full bg-[#D4E2D4] text-[#4A7043] font-black text-sm flex items-center justify-center">
                {{ item.nama?.charAt(0).toUpperCase() || 'N' }}
              </div>
            </div>

            <!-- Name and Anda Badge -->
            <div class="flex flex-col">
              <div class="flex items-center gap-1.5">
                <span class="font-bold text-stone-700 text-sm">{{ item.nama }}</span>
                <span v-if="isCurrentUser(item)" class="bg-[#4D8C57] text-white text-[9px] px-1.5 py-0.5 rounded-full font-bold">Anda</span>
              </div>
            </div>
          </div>

          <!-- Total Stats on the Right -->
          <div class="text-right text-[11px]">
            <p class="text-stone-500">Total Sampah : <span class="font-bold text-[#4A7043]">{{ numeral(item.total_sampah).format('0,0.[0]') }} kg</span></p>
            <p class="text-stone-400 mt-0.5">Transaksi : <span class="font-semibold text-stone-600">{{ item.total_transaksi }}x</span></p>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Empty State -->
    <div v-else class="py-16 flex flex-col items-center justify-center text-stone-400 gap-2">
      <Icon icon="material-symbols:leaderboard-outline" class="w-12 h-12 opacity-20" />
      <p class="font-medium italic">Belum ada top nasabah saat ini.</p>
    </div>
  </div>
</template>
