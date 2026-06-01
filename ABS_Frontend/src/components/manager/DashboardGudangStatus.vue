<script setup>
import { ref, defineProps } from 'vue';
import { Icon } from "@iconify/vue";

const props = defineProps({
  isLoadingCharts: {
    type: Boolean,
    default: true
  },
  gudangStatus: {
    type: Array,
    required: true
  }
});

const isGudangModalOpen = ref(false);
</script>

<template>
  <div class="bg-white rounded-[1.5rem] p-6 shadow-sm border border-stone-100 flex flex-col min-h-[400px]">
    <template v-if="isLoadingCharts">
      <div class="flex justify-between items-center mb-6">
        <div class="h-6 w-32 bg-stone-200 rounded animate-pulse"></div>
        <div class="h-4 w-12 bg-stone-100 rounded animate-pulse"></div>
      </div>
      <div class="space-y-6 flex-1 flex flex-col justify-center">
        <div v-for="i in 4" :key="`gudang-skel-${i}`" class="space-y-2">
          <div class="flex justify-between">
            <div class="h-4 w-24 bg-stone-200 rounded animate-pulse"></div>
            <div class="h-4 w-8 bg-stone-200 rounded animate-pulse"></div>
          </div>
          <div class="w-full bg-stone-100 rounded-full h-2"></div>
          <div class="flex justify-between">
            <div class="h-3 w-20 bg-stone-100 rounded animate-pulse"></div>
            <div class="h-3 w-16 bg-stone-100 rounded animate-pulse"></div>
          </div>
        </div>
      </div>
    </template>
    <template v-else>
      <div class="flex justify-between items-center mb-6">
        <h3 class="text-lg font-bold text-stone-800">Status Gudang</h3>
        <button @click="isGudangModalOpen = true"
          class="text-xs flex items-center gap-1 font-bold transition-all cursor-pointer px-2.5 py-1.5 rounded-lg"
          :class="isGudangModalOpen ? 'bg-[#4A7043] text-white shadow-sm' : 'text-[#4A7043] hover:text-[#3D5A35] hover:bg-stone-50'">
          Lihat Detail <Icon icon="material-symbols:arrow-outward" class="w-3.5 h-3.5" />
        </button>
      </div>
      <div class="space-y-6 flex-1 flex flex-col justify-center">
        <div v-for="(gudang, index) in gudangStatus" :key="index" class="space-y-2">
          <div class="flex justify-between items-end">
            <span class="text-sm font-bold text-stone-700">{{ gudang.name }}</span>
            <span class="text-xs font-bold" :class="gudang.textClass">{{ gudang.percentage }}%</span>
          </div>
          <div class="w-full bg-stone-100 rounded-full h-2 overflow-hidden">
            <div class="h-2 rounded-full transition-all duration-1000" :class="gudang.colorClass" :style="{ width: `${gudang.percentage}%` }"></div>
          </div>
          <div class="flex justify-between items-center">
            <span class="text-xs font-medium text-stone-400">{{ gudang.value }}</span>
            <span class="text-[10px] font-bold" :class="gudang.textClass">{{ gudang.text }}</span>
          </div>
        </div>
      </div>
    </template>

    <!-- Modal Detail Gudang (Teleported) -->
    <Teleport to="body">
      <div v-if="isGudangModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center p-4 transition-opacity duration-300">
        <!-- Backdrop Blur -->
        <div class="absolute inset-0 bg-stone-900/40 backdrop-blur-sm" @click="isGudangModalOpen = false"></div>
        
        <div class="bg-white rounded-[2rem] shadow-[0_20px_50px_rgba(0,0,0,0.2)] border border-stone-200 max-w-2xl w-full overflow-hidden transform transition-all duration-300 scale-100 flex flex-col max-h-[85vh] relative z-10">
          <!-- Modal Header -->
          <div class="px-6 py-5 border-b border-stone-100 flex justify-between items-center bg-stone-50">
            <div>
              <h3 class="text-xl font-bold text-stone-800">Detail Status Gudang</h3>
              <p class="text-xs text-stone-500 mt-1">Daftar informasi lengkap kapasitas dan penyimpanan seluruh gudang aktif</p>
            </div>
            <button @click="isGudangModalOpen = false" class="p-2 text-stone-400 hover:text-stone-700 hover:bg-stone-100 rounded-full transition-colors cursor-pointer">
              <Icon icon="material-symbols:close-rounded" class="w-6 h-6" />
            </button>
          </div>

          <!-- Modal Body -->
          <div class="p-6 overflow-y-auto space-y-4 flex-1">
            <div v-for="gudang in gudangStatus" :key="gudang.id" 
              class="p-5 rounded-2xl border border-stone-100 bg-stone-50/50 hover:bg-stone-50 transition-all duration-200 flex flex-col md:flex-row md:items-center justify-between gap-4">
              <div class="space-y-1.5 flex-1">
                <div class="flex items-center gap-2">
                  <span class="px-2 py-0.5 text-[10px] font-bold bg-stone-200 text-stone-700 rounded-md">ID: {{ gudang.id }}</span>
                  <h4 class="text-sm font-bold text-stone-800">{{ gudang.name }}</h4>
                </div>
                <div class="text-xs text-stone-500 flex items-center gap-1.5">
                  <Icon icon="material-symbols:location-on-outline" class="w-4 h-4 text-stone-400" />
                  <span>Alamat: {{ gudang.name }}</span>
                </div>
              </div>
              <div class="flex items-center gap-4 border-t md:border-t-0 pt-3 md:pt-0 border-stone-100">
                <div class="text-right">
                  <p class="text-xs text-stone-400">Total Penyimpanan</p>
                  <p class="text-sm font-bold text-stone-800">{{ gudang.value }}</p>
                </div>
                <div class="flex items-center justify-center w-12 h-12 rounded-full border-2 transition-all duration-300" 
                  :class="gudang.percentage > 80 ? 'border-red-500 bg-red-50' : gudang.percentage > 60 ? 'border-orange-500 bg-orange-50' : 'border-[#4A7043] bg-emerald-50'">
                  <span class="text-xs font-bold" :class="gudang.percentage > 80 ? 'text-red-700' : gudang.percentage > 60 ? 'text-orange-700' : 'text-[#4A7043]'">{{ gudang.percentage }}%</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Modal Footer -->
          <div class="px-6 py-4 border-t border-stone-100 bg-stone-50 flex justify-end">
            <button @click="isGudangModalOpen = false" class="px-5 py-2.5 text-sm font-bold text-stone-600 hover:text-stone-800 hover:bg-stone-100 rounded-xl transition-all cursor-pointer">
              Tutup
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>
