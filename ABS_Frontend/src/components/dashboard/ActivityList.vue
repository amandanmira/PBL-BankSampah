<script setup>
import { Icon } from "@iconify/vue";

const props = defineProps({
  activities: { type: Array, required: true },
});
</script>

<template>
  <div class="bg-white rounded-[2rem] p-5 md:p-8 shadow-sm border border-stone-100/50">
    <h3 class="text-lg md:text-xl font-black text-stone-800 mb-6 flex items-center gap-2">
      <Icon icon="material-symbols:history" class="w-6 h-6 text-[#4A7043]" />
      Aktivitas Terbaru
    </h3>
    
    <div v-if="activities.length > 0" class="space-y-4">
      <div 
        v-for="activity in activities" 
        :key="activity.id" 
        class="bg-[#F5F5F0]/30 hover:bg-[#F5F5F0]/60 rounded-2xl p-4 flex items-center justify-between border border-stone-100/30 transition-all"
      >
        <div class="flex items-center gap-4">
          <!-- Icon Container -->
          <div 
            :class="[
              'w-12 h-12 rounded-xl flex items-center justify-center shrink-0 border shadow-sm', 
              activity.type === 'penarikan' 
                ? 'border-sky-100 bg-[#E8F6F9] text-[#2E8B9A]' 
                : 'border-green-100 bg-[#E8F0E6] text-[#4A7043]'
            ]"
          >
            <Icon 
              :icon="activity.type === 'penarikan' ? 'material-symbols:account-balance-wallet-outline' : 'material-symbols:package-2-outline'" 
              class="w-6 h-6" 
            />
          </div>
          
          <!-- Details -->
          <div class="flex flex-col">
            <h4 class="font-bold text-stone-800 text-sm md:text-base leading-tight">{{ activity.title }}</h4>
            <p class="text-xs text-stone-400 font-medium mt-1">{{ activity.description }}</p>
            <p class="text-[10px] text-stone-300 font-bold mt-0.5 tracking-wider">{{ activity.time }}</p>
          </div>
        </div>

        <!-- Amount (Right Side) -->
        <div 
          :class="[
            'font-black text-sm md:text-base whitespace-nowrap',
            activity.type === 'penarikan' ? 'text-[#2E8B9A]' : 'text-[#4A7043]'
          ]"
        >
          {{ activity.amount }}
        </div>
      </div>
    </div>
    
    <!-- Empty State -->
    <div v-else class="py-12 border border-dashed border-stone-200 rounded-2xl flex flex-col items-center justify-center text-stone-300 gap-2">
      <Icon icon="material-symbols:history" class="w-10 h-10 opacity-30" />
      <p class="font-bold text-xs italic tracking-wide">Belum ada aktivitas hari ini.</p>
    </div>
  </div>
</template>
