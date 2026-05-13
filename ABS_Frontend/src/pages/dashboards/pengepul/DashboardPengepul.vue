<template>
  <DashboardLayout title="Dashboard">
    <div class="space-y-6">
      <!-- Info Header -->
      <div class="flex items-center justify-between">
        <div class="bg-amber-100 border-l-4 border-amber-500 text-amber-700 p-4 rounded-r-lg w-full">
          <p class="font-bold">*Statis</p>
          <p class="text-sm">Halaman ini saat ini menggunakan data statis untuk keperluan demo tampilan.</p>
        </div>
      </div>

      <!-- Top Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Total Pengeluaran -->
        <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-gray-100 flex flex-col gap-4">
          <div class="flex justify-between items-start">
            <div class="w-12 h-12 rounded-full bg-[#E8F0E5] flex items-center justify-center">
              <Icon icon="material-symbols:attach-money" class="w-6 h-6 text-[#4A7043]" />
            </div>
            <Icon icon="material-symbols:trending-up" class="w-6 h-6 text-[#4CAF50]" />
          </div>
          <div>
            <p class="text-gray-500 text-sm font-medium">Total Pengeluaran</p>
            <p class="text-2xl font-bold text-gray-800">Rp 21.600</p>
          </div>
        </div>

        <!-- Total Sampah Terkumpul -->
        <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-gray-100 flex flex-col gap-4">
          <div class="flex justify-between items-start">
            <div class="w-12 h-12 rounded-full bg-[#E8F0E5] flex items-center justify-center">
              <Icon icon="material-symbols:inventory-2" class="w-6 h-6 text-[#4A7043]" />
            </div>
            <Icon icon="material-symbols:trending-up" class="w-6 h-6 text-[#4CAF50]" />
          </div>
          <div>
            <p class="text-gray-500 text-sm font-medium">Total Sampah Terkumpul</p>
            <p class="text-2xl font-bold text-gray-800">8.00 kg</p>
          </div>
        </div>

        <!-- Pesanan Aktif -->
        <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-gray-100 flex flex-col gap-4">
          <div class="flex justify-between items-start">
            <div class="w-12 h-12 rounded-full bg-[#F5E6E0] flex items-center justify-center">
              <Icon icon="material-symbols:shopping-cart" class="w-6 h-6 text-[#A86444]" />
            </div>
          </div>
          <div>
            <p class="text-gray-500 text-sm font-medium">Pesanan Aktif</p>
            <p class="text-2xl font-bold text-gray-800">2 Pesanan</p>
          </div>
        </div>
      </div>

      <!-- Chart and Status Active Orders -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Bar Chart -->
        <div class="lg:col-span-2 bg-white p-8 rounded-[2rem] shadow-sm border border-gray-100">
          <h3 class="text-lg font-bold text-gray-800 mb-6">Tren Pembelian (Bulan Ini) dalam kg</h3>
          <div class="h-64">
            <apexchart
              type="bar"
              :options="chartOptions"
              :series="chartSeries"
              height="100%"
            />
          </div>
        </div>

        <!-- Status Pesanan Aktif -->
        <div class="bg-white p-8 rounded-[2rem] shadow-sm border border-gray-100 flex flex-col">
          <h3 class="text-lg font-bold text-gray-800 mb-6">Status Pesanan Aktif</h3>
          <div class="space-y-4 flex-1">
            <div class="p-4 rounded-2xl border border-gray-100 space-y-2">
              <div class="flex justify-between items-center">
                <span class="font-bold text-sm text-gray-700">#1713456789012</span>
                <span class="px-3 py-1 bg-green-100 text-green-700 text-[10px] font-bold rounded-full">Diproses</span>
              </div>
              <div class="text-xs text-gray-500">
                <p>Plastik PET</p>
                <p>35 kg</p>
              </div>
            </div>
            <div class="p-4 rounded-2xl border border-gray-100 space-y-2">
              <div class="flex justify-between items-center">
                <span class="font-bold text-sm text-gray-700">#1713256789014</span>
                <span class="px-3 py-1 bg-amber-100 text-amber-700 text-[10px] font-bold rounded-full">Siap Diambil</span>
              </div>
              <div class="text-xs text-gray-500">
                <p>Botol Kaca</p>
                <p>80 kg</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Market Insights -->
      <div class="bg-white p-8 rounded-[2rem] shadow-sm border border-gray-100">
        <h3 class="text-lg font-bold text-gray-800 mb-6">Peluang Pembelian & Market Insights</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <!-- Card 1 -->
          <div class="bg-[#F2F6F1] p-6 rounded-2xl border-l-4 border-[#4A7043] flex flex-col gap-4">
            <div class="flex items-center gap-2">
              <Icon icon="material-symbols:trending-up" class="w-4 h-4 text-[#4A7043]" />
              <span class="text-[10px] font-bold text-[#4A7043] uppercase">Ketersediaan Tinggi</span>
            </div>
            <div>
              <h4 class="font-bold text-gray-800">Kertas HVS</h4>
              <p class="text-xs text-gray-500 mt-1">Gudang 1 - 800kg</p>
            </div>
            <button class="text-xs font-bold text-[#4A7043] flex items-center gap-1 hover:gap-2 transition-all cursor-pointer">
              Beli Sekarang <Icon icon="material-symbols:arrow-forward" />
            </button>
          </div>

          <!-- Card 2 -->
          <div class="bg-[#FFF5F5] p-6 rounded-2xl border-l-4 border-red-500 flex flex-col gap-4">
            <div class="flex items-center gap-2">
              <Icon icon="material-symbols:warning-outline" class="w-4 h-4 text-red-500" />
              <span class="text-[10px] font-bold text-red-500 uppercase">Stok Menipis</span>
            </div>
            <div>
              <h4 class="font-bold text-gray-800">Kaleng Aluminium</h4>
              <p class="text-xs text-gray-500 mt-1">Sisa: 50kg</p>
            </div>
            <button class="text-xs font-bold text-red-500 flex items-center gap-1 hover:gap-2 transition-all cursor-pointer">
              Beli Sekarang <Icon icon="material-symbols:arrow-forward" />
            </button>
          </div>

          <!-- Card 3 -->
          <div class="bg-[#F1F6F7] p-6 rounded-2xl border-l-4 border-cyan-500 flex flex-col gap-4">
            <div class="flex items-center gap-2">
              <Icon icon="material-symbols:trending-down" class="w-4 h-4 text-cyan-500" />
              <span class="text-[10px] font-bold text-cyan-500 uppercase">Harga Turun</span>
            </div>
            <div>
              <h4 class="font-bold text-gray-800">Botol Kaca</h4>
              <p class="text-xs text-gray-500 mt-1">Turun Rp 200/kg</p>
            </div>
            <button class="text-xs font-bold text-cyan-500 flex items-center gap-1 hover:gap-2 transition-all cursor-pointer">
              Beli Sekarang <Icon icon="material-symbols:arrow-forward" />
            </button>
          </div>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>

<script setup>
import { ref } from 'vue'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import { Icon } from '@iconify/vue'
import VueApexCharts from 'vue3-apexcharts'
import { checkRole } from '@/utils'

// Security check
checkRole('pengepul')

// Register component locally
const apexchart = VueApexCharts

const chartOptions = {
  chart: {
    toolbar: { show: false },
    fontFamily: 'Inter, sans-serif'
  },
  plotOptions: {
    bar: {
      borderRadius: 10,
      columnWidth: '60%',
      distributed: true
    }
  },
  dataLabels: { enabled: false },
  legend: { show: false },
  colors: ['#BFCAB8', '#4A7043', '#BFCAB8', '#BFCAB8'],
  xaxis: {
    categories: ['Minggu 1', 'Minggu 2', 'Minggu 3', 'Minggu 4'],
    axisBorder: { show: false },
    axisTicks: { show: false }
  },
  yaxis: {
    labels: {
      style: { colors: '#9CA3AF' }
    }
  },
  grid: {
    borderColor: '#F3F4F6',
    strokeDashArray: 4,
    xaxis: { lines: { show: false } }
  },
  tooltip: {
    theme: 'light'
  }
}

const chartSeries = [{
  name: 'Berat (kg)',
  data: [25, 58, 38, 48]
}]
</script>
