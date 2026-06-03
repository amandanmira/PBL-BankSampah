<script setup>
import { defineProps } from 'vue';
import { Icon } from "@iconify/vue";

const props = defineProps({
  topStats: {
    type: Array,
    required: true
  },
  isLoadingStats: {
    type: Boolean,
    default: true
  }
});
const emit = defineEmits(['click-detail']);
</script>

<template>
  <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">
    <template v-if="isLoadingStats">
      <div v-for="i in 5" :key="`stat-skel-${i}`" class="rounded-2xl p-5 h-36 bg-stone-100 animate-pulse border border-stone-200">
        <div class="flex justify-between items-start">
          <div class="w-10 h-10 rounded-full bg-stone-200"></div>
          <div class="w-16 h-5 rounded-full bg-stone-200"></div>
        </div>
        <div class="mt-8">
          <div class="w-20 h-8 rounded bg-stone-200 mb-2"></div>
          <div class="w-24 h-4 rounded bg-stone-200"></div>
        </div>
      </div>
    </template>
    <template v-else>
      <div v-for="(stat, idx) in topStats" :key="idx"
        class="rounded-2xl p-5 text-white shadow-sm flex flex-col justify-between h-36 relative overflow-hidden group transition-all duration-300 hover:shadow-md hover:-translate-y-1"
        :class="stat.bgClass"
      >
        <div class="flex justify-between items-start z-10 w-full">
          <Icon :icon="stat.icon" class="w-7 h-7" />
          <div class="flex items-center gap-2">
            <!-- Lihat Detail Button in Top Right (Only for Transaksi Card, on the left side of badge) -->
            <button 
              v-if="stat.id === 'transaksi'" 
              @click="$emit('click-detail')" 
              class="px-2.5 py-1 rounded-xl text-[10px] font-bold bg-white/20 hover:bg-white/30 text-white flex items-center gap-1 transition-all cursor-pointer shadow-sm border border-white/10 shrink-0"
            >
              Detail <Icon icon="material-symbols:arrow-forward" class="w-3 h-3" />
            </button>
            <div class="px-2.5 py-1 rounded-full text-[10px] font-bold flex items-center gap-1" :class="stat.increaseClass">
              <Icon v-if="stat.increase.includes('+')" icon="material-symbols:trending-up" class="w-3 h-3" />
              <Icon v-else-if="stat.increase.includes('-')" icon="material-symbols:trending-down" class="w-3 h-3" />
              <Icon v-else icon="material-symbols:check-circle-outline" class="w-3 h-3" />
              {{ stat.increase }}
            </div>
          </div>
        </div>
        <div class="z-10 mt-auto">
          <h3 class="text-3xl font-bold mb-0.5 leading-none">{{ stat.value }}</h3>
          <p class="text-xs font-medium text-white/90">{{ stat.title }}</p>
        </div>
      </div>
    </template>
  </div>
</template>
