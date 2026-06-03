<script setup>
import { defineProps, defineEmits } from 'vue';
import { Icon } from "@iconify/vue";
import VueApexCharts from "vue3-apexcharts";

const apexchart = VueApexCharts;

const props = defineProps({
  isLoadingCharts: {
    type: Boolean,
    default: true
  },
  totalSampahPeriode: {
    type: Number,
    default: 0
  },
  selectedPeriod: {
    type: String,
    required: true
  },
  periods: {
    type: Array,
    required: true
  },
  trendChartOptions: {
    type: Object,
    required: true
  },
  trendChartSeries: {
    type: Array,
    required: true
  }
});

const emit = defineEmits(['update:selectedPeriod']);

const selectPeriod = (period) => {
  emit('update:selectedPeriod', period);
};
</script>

<template>
  <div class="lg:col-span-2 bg-white rounded-[1.5rem] p-6 shadow-sm border border-stone-100 min-h-[400px]">
    <template v-if="isLoadingCharts">
      <div class="flex justify-between items-start mb-6">
        <div class="space-y-2">
          <div class="h-6 w-32 bg-stone-200 rounded animate-pulse"></div>
          <div class="h-4 w-48 bg-stone-100 rounded animate-pulse"></div>
        </div>
        <div class="h-8 w-40 bg-stone-100 rounded-lg animate-pulse"></div>
      </div>
      <div class="h-[300px] w-full bg-stone-50 rounded-xl animate-pulse"></div>
    </template>
    <template v-else>
      <div class="flex justify-between items-start mb-2">
        <div>
          <h3 class="text-lg font-bold text-stone-800">Trend Sampah</h3>
          <p class="text-xs text-stone-400">Total {{ totalSampahPeriode.toLocaleString('id-ID') }} kg pada periode terpilih</p>
        </div>
        <div class="flex bg-stone-100 rounded-lg p-1">
          <button v-for="period in periods" :key="period"
            @click="selectPeriod(period)"
            class="px-3 py-1.5 text-xs font-bold rounded-md transition-colors cursor-pointer"
            :class="selectedPeriod === period ? 'bg-[#4A7043] text-white shadow-sm' : 'text-stone-500 hover:text-stone-700'"
          >
            {{ period }}
          </button>
        </div>
      </div>
      <div class="mt-4 -ml-2 h-[300px] flex items-center justify-center border border-dashed border-stone-200 rounded-2xl bg-stone-50/30" v-if="totalSampahPeriode === 0">
        <div class="text-center space-y-2">
          <Icon icon="material-symbols:insert-chart-outline" class="w-12 h-12 text-stone-300 mx-auto" />
          <p class="text-sm font-medium text-stone-500">Belum ada trend sampah pada periode ini</p>
          <p class="text-xs text-stone-400">Data transaksi penimbangan sampah belum tercatat.</p>
        </div>
      </div>
      <div class="mt-4 -ml-2" v-else>
        <apexchart type="area" height="300" :options="trendChartOptions" :series="trendChartSeries" />
      </div>
    </template>
  </div>
</template>
