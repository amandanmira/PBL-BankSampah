<script setup>
import { Icon } from "@iconify/vue";
import dayjs from "dayjs";
import numeral from "numeral";

const props = defineProps({
  activities: { type: Array, required: true },
});

const getActivityConfig = (type) => {
  if (type === 'setor') {
    return {
      icon: "material-symbols:package-2-outline",
      iconBg: "bg-stone-100 text-[#4A7043]",
      amountPrefix: "+",
      amountClass: "text-[#4A7043]"
    };
  }
  return {
    icon: "material-symbols:account-balance-wallet-outline",
    iconBg: "bg-stone-100 text-stone-600",
    amountPrefix: "-",
    amountClass: "text-stone-800"
  };
};
</script>

<template>
  <div class="space-y-4">
    <h3 class="text-lg font-bold text-stone-800 mb-4">Aktivitas Terbaru</h3>
    
    <div v-if="activities.length > 0" class="space-y-4">
      <div v-for="activity in activities" :key="activity.id" class="bg-white rounded-3xl p-5 shadow-sm border border-stone-100 flex items-center justify-between transition-all hover:shadow-md">
        <div class="flex items-center gap-5">
          <!-- Icon -->
          <div :class="['w-14 h-14 rounded-2xl flex items-center justify-center shrink-0', getActivityConfig(activity.type).iconBg]">
            <Icon :icon="getActivityConfig(activity.type).icon" class="w-7 h-7" />
          </div>
          
          <!-- Details -->
          <div>
            <h4 class="font-bold text-stone-800 leading-tight">{{ activity.title }}</h4>
            <p class="text-xs text-stone-400 mt-0.5">{{ activity.description }}</p>
            <p class="text-[10px] text-stone-300 font-medium mt-1">{{ dayjs(activity.date).format('YYYY-MM-DD') }}</p>
          </div>
        </div>
        
        <!-- Amount -->
        <div :class="['font-black text-lg', getActivityConfig(activity.type).amountClass]">
          {{ getActivityConfig(activity.type).amountPrefix }} Rp {{ numeral(activity.amount).format('0,0') }}
        </div>
      </div>
    </div>
    
    <!-- Empty State -->
    <div v-else class="bg-white rounded-3xl p-10 border border-dashed border-stone-200 flex flex-col items-center justify-center text-stone-400 gap-2">
      <Icon icon="material-symbols:history" class="w-10 h-10 opacity-20" />
      <p class="font-medium italic">Belum ada aktivitas terkini.</p>
    </div>
  </div>
</template>
