<script setup>
import { computed } from "vue";
import VueApexCharts from "vue3-apexcharts";

const props = defineProps({
  series: {
    type: Array,
    default: () => [
      { name: "Volume (kg)", data: [0, 0, 0, 0, 0, 0] },
      { name: "Pendapatan (Rp)", data: [0, 0, 0, 0, 0, 0] },
    ],
  },
  categories: {
    type: Array,
    default: () => ["-", "-", "-", "-", "-", "-"],
  },
});

const apexchart = VueApexCharts;

const chartOptions = computed(() => ({
  chart: {
    type: "line",
    toolbar: { show: false },
    zoom: { enabled: false },
  },
  colors: ["#4A7043", "#8BA783"],
  dataLabels: { enabled: false },
  stroke: { curve: "smooth", width: 3 },
  xaxis: {
    categories: props.categories,
    axisBorder: { show: false },
    axisTicks: { show: false },
  },
  yaxis: {
    labels: {
      formatter: (val) => val >= 1000 ? `${(val / 1000).toFixed(0)}k` : val,
    },
  },
  grid: { borderColor: "#f1f1f1" },
  legend: { position: "bottom", horizontalAlign: "center" },
  tooltip: { theme: "light" },
}));
</script>

<template>
  <div class="bg-white rounded-3xl p-6 shadow-sm border border-stone-100">
    <div class="flex items-center justify-between mb-6">
      <h3 class="text-lg font-bold text-stone-800">Statistik Transaksi (6 Bulan Terakhir)</h3>
      <div class="flex gap-2">
        <!-- Optional filter/tabs could go here -->
      </div>
    </div>
    <div class="w-full">
      <apexchart
        type="line"
        height="300"
        :options="chartOptions"
        :series="series"
      ></apexchart>
    </div>
  </div>
</template>
