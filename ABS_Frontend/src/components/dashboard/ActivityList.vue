<script setup>
import { Icon } from "@iconify/vue";
import dayjs from "dayjs";
import numeral from "numeral";

const props = defineProps({
  activities: { type: Array, required: true },
});

const getActivityConfig = (type) => {
  const configs = {
    petugas: {
      icon: "material-symbols:group-outline",
      iconBg: "bg-[#4A7043] text-white",
    },
    nasabah: {
      icon: "material-symbols:person-outline",
      iconBg: "bg-[#5FA09B] text-white",
    },
    gudang: {
      icon: "material-symbols:home-work-outline",
      iconBg: "bg-[#A86444] text-white",
    },
    sampah: {
      icon: "material-symbols:delete-outline",
      iconBg: "bg-[#707070] text-white",
    },
    transaksi: {
      icon: "material-symbols:account-balance-wallet-outline",
      iconBg: "bg-[#4CAF50] text-white",
    },
    laporan: {
      icon: "material-symbols:payments-outline",
      iconBg: "bg-[#FF9800] text-white",
    },
    default: {
      icon: "material-symbols:notifications-outline",
      iconBg: "bg-stone-100 text-stone-600",
    }
  };
  return configs[type] || configs.default;
};
</script>

<template>
  <div class="bg-white rounded-[2.5rem] p-8 shadow-sm border border-stone-50">
    <h3 class="text-xl font-black text-stone-800 mb-8">Aktivitas Terkini</h3>
    
    <div v-if="activities.length > 0" class="space-y-4">
      <div v-for="activity in activities" :key="activity.id" class="bg-[#F5F5F0]/50 rounded-3xl p-5 flex items-center justify-between transition-all hover:bg-[#F5F5F0]">
        <div class="flex items-center gap-5">
          <!-- Icon -->
          <div :class="['w-14 h-14 rounded-2xl flex items-center justify-center shrink-0 shadow-sm', getActivityConfig(activity.type).iconBg]">
            <Icon :icon="getActivityConfig(activity.type).icon" class="w-7 h-7" />
          </div>
          
          <!-- Details -->
          <div class="flex flex-col">
            <h4 class="font-bold text-stone-800 text-base leading-tight">{{ activity.title }}</h4>
            <p class="text-sm text-stone-400 font-medium mt-1">{{ activity.description }}</p>
            <p class="text-[10px] text-stone-300 font-bold mt-1 tracking-wider">{{ activity.time }}</p>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Empty State -->
    <div v-else class="py-12 border-2 border-dashed border-stone-100 rounded-3xl flex flex-col items-center justify-center text-stone-300 gap-3">
      <Icon icon="material-symbols:history" class="w-12 h-12 opacity-20" />
      <p class="font-bold text-sm italic tracking-wide">Belum ada aktivitas hari ini.</p>
    </div>
  </div>
</template>
