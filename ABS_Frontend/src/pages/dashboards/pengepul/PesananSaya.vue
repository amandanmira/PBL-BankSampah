<template>
  <DashboardLayout title="Pesanan Saya">
    <div class="max-w-6xl mx-auto pb-20">
      <div class="mb-8">
        <h1 class="text-3xl font-black text-gray-800">Riwayat Pesanan</h1>
        <p class="text-gray-500 mt-1">Pantau status dan riwayat pembelian sampah Anda.</p>
      </div>

      <!-- Filters & Stats -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm">
          <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Total Pesanan</p>
          <p class="text-2xl font-black text-gray-800">{{ pagination.total || 0 }}</p>
        </div>
        <div class="bg-amber-50 p-6 rounded-3xl border border-amber-100 shadow-sm">
          <p class="text-[10px] font-bold text-amber-500 uppercase tracking-widest mb-1">Perlu Bayar</p>
          <p class="text-2xl font-black text-amber-700">{{ stats.pending || 0 }}</p>
        </div>
        <div class="bg-blue-50 p-6 rounded-3xl border border-blue-100 shadow-sm">
          <p class="text-[10px] font-bold text-blue-500 uppercase tracking-widest mb-1">Diproses</p>
          <p class="text-2xl font-black text-blue-700">{{ stats.proses || 0 }}</p>
        </div>
        <div class="bg-green-50 p-6 rounded-3xl border border-green-100 shadow-sm">
          <p class="text-[10px] font-bold text-green-500 uppercase tracking-widest mb-1">Selesai</p>
          <p class="text-2xl font-black text-green-700">{{ stats.selesai || 0 }}</p>
        </div>
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
                  <span :class="['text-[9px] font-black uppercase px-2 py-0.5 rounded-full tracking-wider', getStatusClass(order.status)]">
                    {{ order.status }}
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
            <div class="flex gap-2">
              <router-link 
                :to="`/dashboard-pengepul/pesanan/${order.transaksi_id}`"
                class="px-6 py-3 bg-gray-800 text-white text-[11px] font-black rounded-xl hover:bg-black transition-all flex items-center gap-2"
              >
                Lihat Detail
                <Icon icon="material-symbols:arrow-forward" class="w-4 h-4" />
              </router-link>
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

checkRole('pengepul')

const axios = inject('axios')
const loading = ref(true)
const orders = ref([])
const stats = ref({ pending: 0, proses: 0, selesai: 0 })
const pagination = ref({ current_page: 1, last_page: 1, total: 0 })

const user = JSON.parse(sessionStorage.getItem('user') || '{}')
const pengepul_id = user.pengepul_id || user.pengepul?.pengepul_id

const fetchOrders = async (page = 1) => {
  if (!pengepul_id) return
  loading.value = true
  try {
    const res = await axios.get(`/api/pengepul/request-pembelian/${pengepul_id}?page=${page}`)
    orders.value = res.data.data
    pagination.value = {
      current_page: res.data.current_page,
      last_page: res.data.last_page,
      total: res.data.total
    }

    // Simple stats from current page (usually backend should provide this)
    stats.value.pending = orders.value.filter(o => o.status === 'pending').length
    stats.value.proses = orders.value.filter(o => o.status === 'proses').length
    stats.value.selesai = orders.value.filter(o => o.status === 'selesai').length
  } catch (err) {
    console.error('Fetch orders error:', err)
  } finally {
    loading.value = false
  }
}

const calculateTotal = (order) => {
  return order.detail_transaksi?.reduce((acc, item) => acc + (item.berat * item.harga), 0) || 0
}

const getStatusClass = (status) => {
  switch (status) {
    case 'pending': return 'bg-amber-100 text-amber-700'
    case 'proses': return 'bg-blue-100 text-blue-700'
    case 'siap_diambil': return 'bg-green-100 text-green-700'
    case 'selesai': return 'bg-gray-100 text-gray-500'
    case 'tolak': return 'bg-red-100 text-red-700'
    default: return 'bg-gray-100 text-gray-500'
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
