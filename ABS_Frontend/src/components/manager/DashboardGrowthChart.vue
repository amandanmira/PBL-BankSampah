<script setup>
import { defineProps } from 'vue';
import { Icon } from "@iconify/vue";
import VueApexCharts from "vue3-apexcharts";

const apexchart = VueApexCharts;

const props = defineProps({
  isLoadingCharts: {
    type: Boolean,
    default: true
  },
  growthChartOptions: {
    type: Object,
    required: true
  },
  growthChartSeries: {
    type: Array,
    required: true
  }
});
</script>

<template>
  <div class="lg:col-span-2 bg-white rounded-[1.5rem] p-6 shadow-sm border border-stone-100 min-h-[380px]">
    <template v-if="isLoadingCharts">
      <div class="mb-6 space-y-2">
        <div class="h-6 w-48 bg-stone-200 rounded animate-pulse"></div>
        <div class="h-4 w-56 bg-stone-100 rounded animate-pulse"></div>
      </div>
      <div class="h-[280px] w-full bg-stone-50 rounded-xl animate-pulse"></div>
    </template>
    <template v-else>
      <div class="mb-2">
        <h3 class="text-lg font-bold text-stone-800">Pertumbuhan Total Sampah</h3>
        <p class="text-xs text-stone-400">Akumulasi bulanan seluruh gudang</p>
      </div>
      <div class="mt-4 -ml-2 h-[280px] flex items-center justify-center border border-dashed border-stone-200 rounded-2xl bg-stone-50/30" v-if="!growthChartSeries || growthChartSeries.length === 0 || growthChartSeries[0]?.data?.every(v => v === 0)">
        <div class="text-center space-y-2">
          <Icon icon="material-symbols:show-chart-rounded" class="w-12 h-12 text-stone-300 mx-auto" />
          <p class="text-sm font-medium text-stone-500">Belum ada pertumbuhan total sampah</p>
          <p class="text-xs text-stone-400">Data akumulasi bulanan belum tersedia.</p>
        </div>
      </div>
      <div class="mt-4 -ml-2" v-else>
        <apexchart type="line" height="280" :options="growthChartOptions" :series="growthChartSeries" />
      </div>
    </template>
  </div>
</template>
