<script setup>
import { Icon } from "@iconify/vue";
import Avatar from "@/components/ui/avatar/Avatar.vue";
import numeral from "numeral";

const props = defineProps({
  data: { type: Array, required: true },
});

const getRankIcon = (rank) => {
  if (rank === 1) return { icon: "twemoji:trophy", color: "text-yellow-500" };
  if (rank === 2) return { icon: "twemoji:medal-silver", color: "text-stone-400" };
  if (rank === 3) return { icon: "twemoji:medal-bronze", color: "text-orange-600" };
  return { icon: "material-symbols:star-outline", color: "text-yellow-400" };
};
</script>

<template>
  <div class="bg-white rounded-3xl overflow-hidden shadow-sm border border-stone-100">
    <div class="p-6 border-b border-stone-50 flex items-center gap-3">
       <Icon icon="twemoji:trophy" class="w-6 h-6" />
       <h3 class="text-lg font-bold text-stone-800">Top Nasabah Bulan Ini</h3>
    </div>
    
    <div v-if="data.length > 0" class="overflow-x-auto">
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
          <tr v-for="item in data" :key="item.rank" class="hover:bg-stone-50/30 transition-colors">
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
    
    <!-- Empty State -->
    <div v-else class="py-16 flex flex-col items-center justify-center text-stone-400 gap-2">
      <Icon icon="material-symbols:leaderboard-outline" class="w-12 h-12 opacity-20" />
      <p class="font-medium italic">Belum ada top nasabah saat ini.</p>
    </div>
  </div>
</template>
