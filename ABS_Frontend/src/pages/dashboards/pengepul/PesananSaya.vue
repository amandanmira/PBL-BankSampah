<template>
  <DashboardLayout title="Pesanan Saya">
    <div class="max-w-6xl mx-auto pb-20">
      <div class="mb-8">
        <h1 class="text-3xl font-black text-gray-800">Riwayat Pesanan</h1>
        <p class="text-gray-500 mt-1">Pantau status dan riwayat pembelian sampah Anda.</p>
      </div>

      <!-- Tabs -->
      <div class="flex gap-2 bg-[#F8F7F3] p-2 rounded-full mb-6 overflow-x-auto whitespace-nowrap border border-gray-100 max-w-fit shadow-sm">
        <button 
          v-for="tab in tabs" :key="tab.id"
          @click="changeTab(tab.id)"
          :class="['px-6 py-3 rounded-full font-bold text-sm flex items-center gap-2 transition-all justify-center min-w-[120px]', activeTab === tab.id ? 'bg-white text-gray-800 shadow-sm' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100/50']"
        >
          <Icon :icon="tab.icon" class="w-4 h-4" />
          {{ tab.label }}
          <span :class="['w-5 h-5 flex items-center justify-center rounded-full text-[10px] ml-1', activeTab === tab.id ? 'bg-[#8B5E3C] text-white' : 'bg-[#8B5E3C] text-white']">
            {{ stats[tab.id] || 0 }}
          </span>
        </button>
      </div>

      <!-- Search Bar -->
      <div class="relative mb-8 shadow-sm rounded-xl">
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

      <div v-else-if="orders.length === 0" class="bg-white rounded-3xl border border-dashed border-gray-200 p-20 text-center">
        <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-6">
          <Icon icon="material-symbols:shopping-bag-outline" class="w-10 h-10 text-gray-300" />
        </div>
        <h3 class="text-lg font-bold text-gray-800">Belum Ada Pesanan</h3>
        <p class="text-gray-400 text-sm mt-2 mb-8">Anda belum pernah melakukan pembelian sampah.</p>
        <router-link to="/dashboard-pengepul/beli-sampah" class="px-8 py-3 bg-[#4A7043] text-white font-black rounded-xl hover:bg-[#3D5C37] transition-all">
          Mulai Beli Sampah
        </router-link>
      </div>

      <div v-else class="space-y-4">
        <div v-for="order in orders" :key="order.transaksi_id" class="bg-white rounded-3xl border border-gray-100 p-6 shadow-sm hover:shadow-md transition-all group">
          <div class="flex flex-wrap items-center justify-between gap-6">
            <!-- Basic Info -->
            <div class="flex items-center gap-6 flex-1 min-w-[250px]">
              <div class="w-14 h-14 rounded-2xl bg-gray-50 flex items-center justify-center text-gray-400 group-hover:bg-[#4A7043]/10 group-hover:text-[#4A7043] transition-all">
                <Icon icon="material-symbols:receipt-long-outline" class="w-8 h-8" />
              </div>
              <div>
                <div class="flex items-center gap-3 mb-1">
                  <span class="text-xs font-black text-gray-800">#{{ order.transaksi_id }}</span>
                  <span :class="['text-[9px] font-bold px-3 py-1 rounded-md tracking-wider flex items-center gap-1', getStatusClass(order.status, order)]">
                    <Icon v-if="order.status === 'selesai' || order.status === 'siap_diambil' || (order.status === 'proses' && !order.bukti_transfer)" icon="material-symbols:check-circle-outline" class="w-3 h-3" />
                    <Icon v-else-if="order.status === 'tolak' || order.status === 'batal'" icon="material-symbols:cancel-outline" class="w-3 h-3" />
                    {{ getStatusText(order.status, order) }}
                  </span>
                </div>
                <p class="text-[10px] text-gray-400 font-medium">
                  {{ formatDate(order.created_at) }} • {{ order.detail_transaksi?.length || 0 }} Jenis Sampah
                </p>
              </div>
            </div>

            <!-- Total Price -->
            <div class="text-right px-6 border-x border-gray-50 min-w-[150px]">
              <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest mb-1">Total Pembayaran</p>
              <p class="text-lg font-black text-gray-800">{{ formatCurrency(calculateTotal(order)) }}</p>
            </div>

            <!-- Action -->
            <div class="flex gap-3 w-full">
              <router-link 
                :to="`/dashboard-pengepul/pesanan/${order.transaksi_id}`"
                class="flex-1 py-3 bg-[#F8F7F3] text-gray-700 text-xs font-bold rounded-xl hover:bg-gray-200 transition-all flex items-center justify-center gap-2"
              >
                <Icon icon="material-symbols:visibility-outline" class="w-4 h-4" />
                Lihat Detail
              </router-link>
              
              <button
                v-if="order.status === 'selesai'"
                @click="router.push(`/dashboard-pengepul/pesanan/${order.transaksi_id}/cetak`)"
                class="flex-1 py-3 bg-[#8B5E3C] text-white text-xs font-bold rounded-xl hover:bg-[#724C30] transition-all flex items-center justify-center gap-2 shadow-sm"
              >
                <Icon icon="material-symbols:print-outline" class="w-4 h-4" />
                Cetak Riwayat
              </button>
            </div>
          </div>
          
          <!-- Rejected Box -->
          <div v-if="order.status === 'tolak' || order.status === 'batal'" class="mt-4 bg-red-50 border border-red-100 rounded-xl p-4 flex gap-3">
            <Icon icon="material-symbols:error-outline" class="w-5 h-5 text-red-500 shrink-0 mt-0.5" />
            <div>
              <p class="text-xs font-bold text-red-600 mb-1">Alasan {{ order.status === 'tolak' ? 'Ditolak' : 'Dibatalkan' }}:</p>
              <p class="text-xs text-red-500/80">{{ order.ket_status || 'Tidak ada keterangan.' }}</p>
            </div>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="pagination.last_page > 1" class="flex justify-center gap-2 mt-10">
          <button 
            v-for="page in pagination.last_page" 
            :key="page"
            @click="fetchOrders(page)"
            :class="['w-10 h-10 rounded-xl font-bold text-sm transition-all', pagination.current_page === page ? 'bg-[#4A7043] text-white shadow-lg shadow-[#4A7043]/20' : 'bg-white border border-gray-100 text-gray-500 hover:border-[#4A7043] hover:text-[#4A7043]']"
          >
            {{ page }}
          </button>
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
    case 'pending': return 'bg-amber-100 text-amber-700'
    case 'proses': return 'bg-blue-100 text-blue-700'
    case 'selesai': return 'bg-[#4A7043] text-white'
    case 'tolak': return 'bg-red-600 text-white'
    case 'batal': return 'bg-red-600 text-white'
    default: return 'bg-gray-100 text-gray-500'
  }
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

onMounted(() => {
  fetchOrders()
})
</script>
