<template>
  <DashboardLayout title="Dashboard">
    <div class="space-y-6">
      
      <!-- Loading State -->
      <div v-if="loading" class="flex flex-col items-center justify-center py-32 bg-white rounded-[2rem] border border-stone-100 shadow-xs">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-[#4A7043]"></div>
        <p class="text-sm font-semibold text-stone-500 mt-4">Memuat data dashboard...</p>
      </div>

      <div v-else class="space-y-6 animate-in fade-in duration-500">
        <!-- Top Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <!-- Total Pengeluaran -->
          <div class="bg-white p-6 rounded-[2rem] shadow-xs border border-stone-100 flex flex-col gap-4">
            <div class="flex justify-between items-start">
              <div class="w-12 h-12 rounded-full bg-[#3CA3A3] flex items-center justify-center shadow-sm">
                <Icon icon="material-symbols:attach-money" class="w-6 h-6 text-white" />
              </div>
              <Icon icon="material-symbols:trending-up" class="w-6 h-6 text-[#3CA3A3]" />
            </div>
            <div>
              <p class="text-stone-400 text-sm font-semibold">Total Pengeluaran</p>
              <p class="text-3xl font-extrabold text-stone-800 mt-1">{{ formatCurrency(dashboardData.total_pengeluaran || 0) }}</p>
            </div>
          </div>

          <!-- Total Volume Sampah Dibeli -->
          <div class="bg-white p-6 rounded-[2rem] shadow-xs border border-stone-100 flex flex-col gap-4">
            <div class="flex justify-between items-start">
              <div class="w-12 h-12 rounded-full bg-[#4A7043] flex items-center justify-center shadow-sm">
                <Icon icon="material-symbols:inventory-2" class="w-6 h-6 text-white" />
              </div>
              <Icon icon="material-symbols:trending-up" class="w-6 h-6 text-[#4CAF50]" />
            </div>
            <div>
              <p class="text-stone-400 text-sm font-semibold">Total Volume Sampah Dibeli</p>
              <p class="text-3xl font-extrabold text-stone-800 mt-1">{{ (dashboardData.total_sampah || 0).toFixed(2) }} kg</p>
            </div>
          </div>

          <!-- Pesanan Aktif -->
          <div class="bg-white p-6 rounded-[2rem] shadow-xs border border-stone-100 flex flex-col gap-4">
            <div class="flex justify-between items-start">
              <div class="w-12 h-12 rounded-full bg-[#A86444] flex items-center justify-center shadow-sm">
                <Icon icon="material-symbols:inventory-2" class="w-6 h-6 text-white" />
              </div>
            </div>
            <div>
              <p class="text-stone-400 text-sm font-semibold">Pesanan Aktif</p>
              <p class="text-3xl font-extrabold text-stone-800 mt-1">{{ dashboardData.pesanan_aktif_count || 0 }} Pesanan</p>
            </div>
          </div>
        </div>

        <!-- Chart and Status Active Orders -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Bar Chart -->
          <div class="lg:col-span-2 bg-white p-8 rounded-[2rem] shadow-xs border border-stone-100">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-3">
              <h3 class="text-lg font-bold text-stone-800">
                Tren Pembelian (Bulan Ini) <span class="text-sm font-normal text-stone-400">dalam {{ activeFilter === 'berat' ? 'kg' : 'item' }}</span>
              </h3>
              
              <!-- Weight/Item Filter Toggle Buttons -->
              <div class="flex bg-stone-100 p-1 rounded-xl shrink-0 self-start">
                <button
                  @click="activeFilter = 'berat'"
                  :class="[
                    'px-4 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer',
                    activeFilter === 'berat'
                      ? 'bg-white text-[#4A7043] shadow-xs'
                      : 'text-stone-500 hover:text-stone-800 bg-transparent'
                  ]"
                >
                  Berat (kg)
                </button>
                <button
                  @click="activeFilter = 'item'"
                  :class="[
                    'px-4 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer',
                    activeFilter === 'item'
                      ? 'bg-white text-[#4A7043] shadow-xs'
                      : 'text-stone-500 hover:text-stone-800 bg-transparent'
                  ]"
                >
                  Item (Qty)
                </button>
              </div>
            </div>

            <div class="h-64">
              <apexchart
                :key="activeFilter"
                type="bar"
                :options="chartOptions"
                :series="chartSeries"
                height="100%"
              />
            </div>
          </div>

          <!-- Status Pesanan Aktif -->
          <div class="bg-white p-8 rounded-[2rem] shadow-xs border border-stone-100 flex flex-col">
            <h3 class="text-lg font-bold text-stone-800 mb-6">Status Pesanan Aktif</h3>
            <div class="space-y-4 flex-1 overflow-y-auto max-h-[300px] pr-1">
              <div 
                v-for="order in dashboardData.pesanan_aktif" 
                :key="order.transaksi_id"
                @click="router.push(`/dashboard-pengepul/request-pembelian/show/${order.transaksi_id}`)"
                class="p-5 rounded-2xl border border-stone-100 space-y-3 hover:border-[#4A7043]/30 hover:shadow-xs transition-all cursor-pointer group"
              >
                <div class="flex justify-between items-center">
                  <span class="font-extrabold text-base text-stone-800 group-hover:text-[#4A7043] transition-colors">
                    #{{ order.kode_transaksi || order.transaksi_id }}
                  </span>
                  <span :class="[
                    'px-3 py-1 text-[11px] font-bold rounded-full border tracking-wide',
                    order.status === 'pending' ? 'bg-amber-50 text-amber-700 border-amber-200' :
                    order.status === 'proses' ? (order.bukti_transfer ? 'bg-sky-50 text-sky-700 border-sky-200' : 'bg-orange-50 text-orange-700 border-orange-200') :
                    'bg-[#E8F0E5] text-[#4A7043] border-[#C4D1C0]/30'
                  ]">
                    {{ order.status === 'siap_diambil' ? 'Siap Diambil' : formatStatus(order.status, order.bukti_transfer) }}
                  </span>
                </div>
                <div class="text-sm text-stone-500 space-y-1">
                  <p class="truncate font-semibold text-stone-600">{{ order.items_summary || 'Tidak ada deskripsi item' }}</p>
                  <p class="font-medium text-stone-400 text-xs">{{ order.total_berat }} kg</p>
                </div>
              </div>

              <!-- Empty State -->
              <div v-if="!dashboardData.pesanan_aktif || dashboardData.pesanan_aktif.length === 0" class="flex flex-col items-center justify-center py-16 text-stone-400">
                <Icon icon="material-symbols:shopping-cart-outline" class="w-12 h-12 opacity-20 mb-2" />
                <p class="text-xs font-semibold">Tidak ada pesanan aktif</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Market Insights (Dynamic Section) -->
        <div class="bg-white p-8 rounded-[2rem] shadow-xs border border-stone-100">
          <div class="flex items-center mb-6">
            <h3 class="text-lg font-bold text-stone-800">Peluang Pembelian & Market Insights</h3>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div 
              v-for="insight in dashboardData.market_insights" 
              :key="insight.tipe"
              :class="[
                'p-6 rounded-2xl border-l-4 flex flex-col gap-4',
                insight.tipe === 'ketersediaan_tinggi' ? 'bg-[#F2F6F1] border-[#4A7043]' :
                insight.tipe === 'stok_menipis' ? 'bg-[#FFF5F5] border-red-500' :
                'bg-[#F1F6F7] border-cyan-500'
              ]"
            >
              <div class="flex items-center gap-2">
                <Icon 
                  :icon="
                    insight.tipe === 'ketersediaan_tinggi' ? 'material-symbols:trending-up' :
                    insight.tipe === 'stok_menipis' ? 'material-symbols:warning-outline' :
                    'material-symbols:trending-down'
                  " 
                  :class="[
                    'w-4 h-4',
                    insight.tipe === 'ketersediaan_tinggi' ? 'text-[#4A7043]' :
                    insight.tipe === 'stok_menipis' ? 'text-red-500' :
                    'text-cyan-500'
                  ]"
                />
                <span 
                  :class="[
                    'text-[10px] font-bold uppercase',
                    insight.tipe === 'ketersediaan_tinggi' ? 'text-[#4A7043]' :
                    insight.tipe === 'stok_menipis' ? 'text-red-500' :
                    'text-cyan-500'
                  ]"
                >
                  {{ insight.label }}
                </span>
              </div>
              <div>
                <h4 class="font-bold text-stone-800">{{ insight.nama }}</h4>
                <p class="text-xs text-stone-500 mt-1">{{ insight.info }}</p>
              </div>
              <button 
                @click="router.push('/dashboard-pengepul/beli-sampah')" 
                :class="[
                  'text-xs font-bold flex items-center gap-1 hover:gap-2 transition-all cursor-pointer mt-auto self-start',
                  insight.tipe === 'ketersediaan_tinggi' ? 'text-[#4A7043]' :
                  insight.tipe === 'stok_menipis' ? 'text-red-500' :
                  'text-cyan-500'
                ]"
              >
                Beli Sekarang <Icon icon="material-symbols:arrow-forward" />
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>

<script setup>
import { ref, computed, onMounted, inject } from 'vue'
import { useRouter } from 'vue-router'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import { Icon } from '@iconify/vue'
import VueApexCharts from 'vue3-apexcharts'
import { checkRole } from '@/utils'

// Security check
checkRole('pengepul')

// Register component locally
const apexchart = VueApexCharts
const router = useRouter()
const axios = inject('axios')

const loading = ref(true)
const dashboardData = ref({})
const activeFilter = ref('berat') // 'berat' or 'item'

// User info
const user = computed(() => {
  try {
    return JSON.parse(sessionStorage.getItem("user") || "{}")
  } catch (e) {
    return {}
  }
})

const fetchDashboardStats = async () => {
  try {
    const id = user.value.pengepul_id
    if (!id) return
    const res = await axios.get(`/api/pengepul/dashboard/${id}`)
    dashboardData.value = res.data
  } catch (err) {
    console.error("Gagal memuat statistik dashboard:", err)
  } finally {
    loading.value = false
  }
}

const formatCurrency = (val) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0
  }).format(val)
}

const formatStatus = (status, buktiTransfer) => {
  if (status === 'pending') return 'Menunggu Konfirmasi'
  if (status === 'proses') {
    return buktiTransfer ? 'Menunggu Validasi' : 'Menunggu Pembayaran'
  }
  if (status === 'siap_diambil') return 'Siap Diambil'
  if (status === 'selesai') return 'Selesai'
  if (status === 'tolak') return 'Ditolak'
  if (status === 'batal') return 'Batal'
  return status
}

onMounted(() => {
  fetchDashboardStats()
})

const chartOptions = computed(() => {
  const isBerat = activeFilter.value === 'berat'

  return {
    chart: {
      toolbar: { show: false },
      fontFamily: 'Outfit, Inter, sans-serif'
    },
    plotOptions: {
      bar: {
        borderRadius: 6,
        borderRadiusApplication: 'end',
        columnWidth: isBerat ? '50%' : '65%',
        distributed: isBerat
      }
    },
    dataLabels: { enabled: false },
    legend: {
      show: !isBerat,
      position: 'top',
      horizontalAlign: 'right',
      fontSize: '11px',
      fontFamily: 'Outfit, Inter, sans-serif',
      fontWeight: 600,
      labels: { colors: '#4B5563' }
    },
    colors: isBerat
      ? ['#BFCAB8', '#4A7043', '#BFCAB8', '#BFCAB8']
      : ['#4A7043', '#83A77C', '#BFCAB8'],
    xaxis: {
      categories: dashboardData.value.chart_data?.categories || ['Minggu 1', 'Minggu 2', 'Minggu 3', 'Minggu 4'],
      axisBorder: { show: false },
      axisTicks: { show: false },
      labels: {
        style: {
          colors: '#6B7280',
          fontWeight: 600
        }
      }
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
      theme: 'light',
      y: {
        formatter: (val) => `${val.toFixed(2)} kg`
      }
    }
  }
})

const chartSeries = computed(() => {
  if (activeFilter.value === 'berat') {
    return [{
      name: 'Berat (kg)',
      data: dashboardData.value.chart_data?.berat || [0, 0, 0, 0]
    }]
  } else {
    return dashboardData.value.chart_data?.top_items_weekly || [
      { name: 'Item 1', data: [0, 0, 0, 0] },
      { name: 'Item 2', data: [0, 0, 0, 0] },
      { name: 'Item 3', data: [0, 0, 0, 0] }
    ]
  }
})
</script>

<style scoped>
/* Smooth transition effects */
.animate-in {
  animation: fadeIn 0.4s ease-out forwards;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
