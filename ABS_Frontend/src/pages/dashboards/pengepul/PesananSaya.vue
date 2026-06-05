<template>
  <DashboardLayout title="Pesanan Saya">
    <div class="max-w-6xl mx-auto pb-20">
      <div class="mb-8 hidden lg:block">
        <h1 class="text-3xl font-black text-gray-800">Riwayat Pesanan</h1>
        <p class="text-gray-500 mt-1">Pantau status dan riwayat pembelian sampah Anda.</p>
      </div>

      <!-- Tabs -->
      <div class="grid grid-cols-2 gap-1.5 bg-[#EAE8E2]/60 p-1.5 rounded-2xl mb-6 lg:flex lg:rounded-full lg:max-w-fit">
        <button 
          v-for="tab in tabs" :key="tab.id"
          @click="changeTab(tab.id)"
          :class="['px-5 py-2.5 rounded-xl font-bold text-xs flex items-center justify-center gap-1.5 transition-all lg:rounded-full', activeTab === tab.id ? 'bg-white text-gray-800 shadow-sm' : 'text-gray-500 hover:text-gray-700']"
        >
          {{ tab.label }}
          <span class="w-5 h-5 flex items-center justify-center rounded-full text-[10px] bg-[#8B5E3C] text-white shrink-0">
            {{ stats[tab.id] || 0 }}
          </span>
        </button>
      </div>

      <!-- Search Bar -->
      <div class="relative mb-8 shadow-sm rounded-xl hidden lg:block">
        <Icon icon="material-symbols:search" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
        <input 
          v-model="searchQuery" 
          @input="handleSearch"
          type="text" 
          placeholder="Cari berdasarkan tracking ID atau jenis sampah..." 
          class="w-full bg-white border border-gray-100 rounded-xl pl-12 pr-4 py-3 focus:outline-none focus:ring-2 focus:ring-[#4A7043]/20 focus:border-[#4A7043] transition-all text-sm"
        >
      </div>

      <!-- Orders List -->
      <div v-if="loading" class="flex justify-center py-20">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-[#4A7043]"></div>
      </div>

      <div v-else-if="orders.length === 0" class="bg-white rounded-[24px] border border-dashed border-gray-200 p-8 md:p-20 text-center flex flex-col items-center justify-center">
        <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-6">
          <Icon icon="material-symbols:shopping-bag-outline" class="w-10 h-10 text-gray-300" />
        </div>
        <h3 class="text-lg font-bold text-gray-800">Belum Ada Pesanan</h3>
        <p class="text-gray-400 text-sm mt-2 mb-8 max-w-xs mx-auto">Anda belum pernah melakukan pembelian sampah.</p>
        <router-link to="/dashboard-pengepul/beli-sampah" class="inline-flex items-center justify-center px-6 py-3 bg-[#4A7043] text-white font-black text-sm rounded-xl hover:bg-[#3D5C37] transition-all whitespace-nowrap">
          Mulai Beli Sampah
        </router-link>
      </div>

      <div v-else class="space-y-4">
        <div v-for="order in orders" :key="order.transaksi_id" class="bg-white rounded-[24px] border border-gray-100 p-5 shadow-sm hover:shadow-md transition-all">
          
          <!-- Card Header: ID Pesanan & Status Tag -->
          <div class="flex items-center justify-between mb-4">
            <div>
              <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">ID Pesanan</p>
              <h3 class="text-sm lg:text-base font-black text-gray-850">#{{ order.transaksi_id }}</h3>
            </div>
            
            <span :class="['text-[10px] font-bold px-3 py-1.5 rounded-full flex items-center gap-1.5', getStatusClass(order.status, order)]">
              <Icon :icon="getStatusIcon(order.status, order)" class="w-3.5 h-3.5" />
              {{ getStatusText(order.status, order) }}
            </span>
          </div>

          <hr class="border-gray-100 my-4" />

          <!-- Rejected Box -->
          <div v-if="order.status === 'tolak' || order.status === 'batal'" class="bg-[#FFF1F2] border border-[#FECDD3] rounded-2xl p-4 flex gap-3 mb-4">
            <Icon icon="material-symbols:error-outline" class="w-5 h-5 text-[#F43F5E] shrink-0 mt-0.5" />
            <div>
              <p class="text-xs font-bold text-[#E11D48] mb-1">Alasan {{ order.status === 'tolak' ? 'Ditolak' : 'Dibatalkan' }}:</p>
              <p class="text-xs text-[#E11D48]/80">{{ order.ket_status || 'Tidak ada keterangan.' }}</p>
            </div>
          </div>

          <!-- Product List (Items) -->
          <div class="space-y-4 mb-4">
            <div v-for="item in order.detail_transaksi" :key="item.id" class="flex gap-4">
              <div v-if="item.sampah?.item_sampah?.foto" class="w-14 h-14 rounded-xl bg-gray-50 border border-gray-100 overflow-hidden shrink-0">
                <img :src="`${axios.defaults.baseURL}/storage/${item.sampah.item_sampah.foto}`" class="w-full h-full object-cover" />
              </div>
              <div v-else class="w-14 h-14 rounded-xl bg-gray-50 border border-gray-100 flex items-center justify-center text-gray-300 shrink-0">
                <Icon icon="material-symbols:image" class="w-6 h-6" />
              </div>
              <div class="flex-1 min-w-0">
                <h4 class="text-sm font-bold text-gray-800 truncate">{{ item.sampah?.item_sampah?.nama }}</h4>
                <p class="text-xs text-gray-400 mt-1">{{ item.berat }} kg</p>
              </div>
            </div>
          </div>

          <hr class="border-gray-100 my-4" />

          <!-- Info: Total & Tanggal -->
          <div class="space-y-2 mb-4">
            <div class="flex justify-between items-center text-xs">
              <span class="text-gray-400 font-bold">Total Pembayaran</span>
              <span class="text-sm font-bold text-[#4A7043]">{{ formatCurrency(calculateTotal(order)) }}</span>
            </div>
            <div class="flex justify-between items-center text-xs">
              <span class="text-gray-400 font-bold">Tanggal</span>
              <span class="text-gray-500 font-semibold">{{ formatDateSimple(order.created_at) }}</span>
            </div>
          </div>

          <hr class="border-gray-100 my-4" />

          <!-- Actions -->
          <div class="flex gap-3">
            <router-link 
              :to="`/dashboard-pengepul/pesanan/${order.transaksi_id}`"
              class="flex-1 py-3 bg-[#F8F7F3] border border-gray-150 text-gray-700 text-xs font-bold rounded-xl hover:bg-gray-200 transition-all flex items-center justify-center gap-1.5"
            >
              <Icon icon="material-symbols:visibility-outline" class="w-4 h-4" />
              Lihat Detail
            </router-link>
            
            <button
              v-if="order.status === 'selesai'"
              @click="router.push(`/dashboard-pengepul/pesanan/${order.transaksi_id}/cetak`)"
              class="flex-1 py-3 bg-[#8B5E3C] text-white text-xs font-bold rounded-xl hover:bg-[#724C30] transition-all flex items-center justify-center gap-1.5 shadow-sm"
            >
              <Icon icon="material-symbols:print-outline" class="w-4 h-4" />
              Cetak Riwayat
            </button>
          </div>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>

<script setup>
import { ref, onMounted, inject } from 'vue'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import { Icon } from '@iconify/vue'
import { checkRole } from '@/utils'
import dayjs from 'dayjs'
import { useRouter } from 'vue-router'

checkRole('pengepul')

const router = useRouter()
const axios = inject('axios')
const loading = ref(true)
const orders = ref([])
const stats = ref({ menunggu: 0, diproses: 0, selesai: 0, ditolak: 0 })
const pagination = ref({ current_page: 1, last_page: 1, total: 0 })
const activeTab = ref('menunggu')
const searchQuery = ref('')
let searchTimeout = null

const tabs = [
  { id: 'menunggu', label: 'Menunggu', icon: 'material-symbols:schedule-outline' },
  { id: 'diproses', label: 'Diproses', icon: 'material-symbols:sync' },
  { id: 'selesai', label: 'Selesai', icon: 'material-symbols:check-circle-outline' },
  { id: 'ditolak', label: 'Ditolak', icon: 'material-symbols:cancel-outline' }
]

const user = JSON.parse(sessionStorage.getItem('user') || '{}')
const pengepul_id = user.pengepul_id || user.pengepul?.pengepul_id

const fetchOrders = async (page = 1) => {
  if (!pengepul_id) return
  loading.value = true
  try {
    const res = await axios.get(`/api/pengepul/request-pembelian/${pengepul_id}?page=${page}&status=${activeTab.value}&search=${searchQuery.value}`)
    orders.value = res.data.data
    pagination.value = {
      current_page: res.data.current_page,
      last_page: res.data.last_page,
      total: res.data.total
    }
    stats.value = res.data.counts || { menunggu: 0, diproses: 0, selesai: 0, ditolak: 0 }
  } catch (err) {
    console.error('Fetch orders error:', err)
  } finally {
    loading.value = false
  }
}

const changeTab = (tabId) => {
  activeTab.value = tabId
  fetchOrders(1)
}

const handleSearch = () => {
  if (searchTimeout) clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    fetchOrders(1)
  }, 500)
}

const calculateTotal = (order) => {
  return order.detail_transaksi?.reduce((acc, item) => acc + (item.berat * item.harga), 0) || 0
}

const getStatusClass = (status, order) => {
  if (status === 'proses' && !order?.bukti_transfer) return 'bg-[#4A7043] text-white' 
  if (status === 'siap_diambil') return 'bg-[#4A7043] text-white' 
  
  switch (status) {
    case 'pending': return 'bg-[#4A7043] text-white'
    case 'proses': return 'bg-[#4A7043] text-white'
    case 'selesai': return 'bg-[#4A7043] text-white'
    case 'tolak': return 'bg-[#E11D48] text-white'
    case 'batal': return 'bg-[#8B5E3C] text-white'
    default: return 'bg-gray-100 text-gray-500'
  }
}

const getStatusIcon = (status, order) => {
  if (status === 'proses' && !order?.bukti_transfer) return 'material-symbols:info-outline'
  if (status === 'siap_diambil') return 'material-symbols:check-circle-outline'
  if (status === 'selesai') return 'material-symbols:check-circle-outline'
  if (status === 'tolak' || status === 'batal') return 'material-symbols:cancel-outline'
  if (status === 'pending') return 'material-symbols:schedule-outline'
  return 'material-symbols:sync'
}

const getStatusText = (status, order) => {
  if (status === 'proses' && !order?.bukti_transfer) return 'Lakukan Pembayaran'
  if (status === 'siap_diambil') return 'Siap Diambil'
  
  switch (status) {
    case 'pending': return 'Menunggu Konfirmasi'
    case 'proses': return 'Menunggu Validasi'
    case 'selesai': return 'Selesai'
    case 'tolak': return 'Ditolak'
    case 'batal': return 'Dibatalkan'
    default: return status
  }
}

const formatCurrency = (val) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0
  }).format(val)
}

const formatDate = (date) => {
  return dayjs(date).format('DD MMM YYYY, HH:mm')
}

const formatDateSimple = (date) => {
  return dayjs(date).format('D/M/YYYY')
}

onMounted(() => {
  fetchOrders()
})
</script>
