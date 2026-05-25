<template>
  <DashboardLayout title="Cetak Riwayat">
    <div v-if="loading" class="flex items-center justify-center min-h-[60vh]">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-[#4A7043]"></div>
    </div>

    <div v-else-if="order" class="max-w-4xl mx-auto pb-20">
      <!-- Header Info & Actions -->
      <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <button 
            @click="router.push('/dashboard-pengepul/pesanan-saya')" 
            class="flex items-center gap-2 text-gray-500 hover:text-gray-800 transition-all mb-2"
          >
            <Icon icon="material-symbols:arrow-back" class="w-5 h-5" />
            <span class="text-base font-bold">Kembali</span>
          </button>
          <h1 class="text-3xl font-black text-gray-800">Riwayat Pesanan</h1>
          <p class="text-base text-gray-400 font-medium">Tracking ID: #{{ order.transaksi_id }}</p>
        </div>
        
        <button 
          @click="downloadPdf"
          :disabled="downloading"
          class="px-6 py-3 bg-[#4A7043] text-white text-sm font-black rounded-xl hover:bg-[#3D5C37] transition-all flex items-center justify-center gap-2 shadow-sm disabled:opacity-50"
        >
          <Icon v-if="!downloading" icon="material-symbols:download" class="w-5 h-5" />
          <Icon v-else icon="material-symbols:sync" class="w-5 h-5 animate-spin" />
          {{ downloading ? 'Mengunduh...' : 'Unduh PDF' }}
        </button>
      </div>

      <!-- Invoice Content -->
      <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-10 print-section">
        
        <!-- Header Invoice -->
        <div class="flex justify-between items-start border-b border-gray-100 pb-8 mb-8">
          <div>
            <h2 class="text-2xl font-black text-[#4A7043] mb-1">INVOICE PEMBELIAN</h2>
            <p class="text-gray-500 text-sm">Status: <span class="font-bold text-gray-800 uppercase">{{ order.status }}</span></p>
          </div>
          <div class="text-right">
            <p class="text-sm text-gray-400 mb-1">Tanggal Pesanan</p>
            <p class="text-base font-bold text-gray-800">{{ formatDate(order.created_at) }}</p>
          </div>
        </div>

        <!-- Detail Pesanan -->
        <div class="mb-10">
          <h3 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-4">Item Pembelian</h3>
          <div class="space-y-4">
            <div v-for="item in order.detail_transaksi" :key="item.id" class="flex justify-between items-center bg-gray-50 p-4 rounded-2xl border border-gray-100">
              <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl overflow-hidden bg-white shrink-0">
                  <img v-if="item.sampah?.item_sampah?.foto" :src="`${axios.defaults.baseURL}/storage/${item.sampah.item_sampah.foto}`" class="w-full h-full object-cover" />
                  <div v-else class="w-full h-full flex items-center justify-center text-gray-300">
                    <Icon icon="material-symbols:image-not-supported" class="w-6 h-6" />
                  </div>
                </div>
                <div>
                  <h4 class="font-bold text-gray-800">{{ item.sampah?.item_sampah?.nama }}</h4>
                  <p class="text-xs text-gray-500">{{ item.berat }} kg &times; {{ formatCurrency(item.harga) }}</p>
                </div>
              </div>
              <p class="font-black text-gray-800">{{ formatCurrency(item.berat * item.harga) }}</p>
            </div>
          </div>
        </div>

        <!-- Total Pembayaran -->
        <div class="border-t border-gray-100 pt-8 flex justify-between items-end">
          <div>
            <p class="text-sm text-gray-500 mb-1">Metode Pembayaran</p>
            <p class="font-bold text-gray-800">Transfer Bank</p>
          </div>
          <div class="text-right">
            <p class="text-sm text-gray-500 mb-1">Total Keseluruhan</p>
            <p class="text-3xl font-black text-[#8B5E3C]">{{ formatCurrency(totalHarga) }}</p>
          </div>
        </div>

      </div>
    </div>
  </DashboardLayout>
</template>

<script setup>
import { ref, computed, onMounted, inject } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import { Icon } from '@iconify/vue'
import dayjs from 'dayjs'
import Swal from 'sweetalert2'

const route = useRoute()
const router = useRouter()
const axios = inject('axios')

const loading = ref(true)
const order = ref(null)

const fetchOrder = async () => {
  try {
    const res = await axios.get(`/api/pengepul/request-pembelian/show/${route.params.id}`)
    order.value = res.data
  } catch (err) {
    console.error('Fetch order error:', err)
    Swal.fire('Gagal', 'Tidak dapat mengambil detail pesanan.', 'error')
  } finally {
    loading.value = false
  }
}

const totalHarga = computed(() => {
  if (!order.value) return 0
  return order.value.detail_transaksi?.reduce((acc, d) => acc + (d.berat * d.harga), 0) || 0
})

const formatCurrency = (val) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0
  }).format(val || 0)
}

const formatDate = (date) => {
  return dayjs(date).format('DD MMM YYYY, HH:mm')
}

const downloading = ref(false)

const downloadPdf = async () => {
  if (!order.value || downloading.value) return
  downloading.value = true
  
  try {
    const response = await axios.get(`/api/pengepul/export-pembelian/pdf/${order.value.transaksi_id}`, {
      responseType: 'blob'
    })
    
    const url = window.URL.createObjectURL(new Blob([response.data]))
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', `Invoice_Pesanan_${order.value.transaksi_id}.pdf`)
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    window.URL.revokeObjectURL(url)
  } catch (error) {
    console.error('Download error:', error)
    Swal.fire('Gagal', 'Tidak dapat mengunduh PDF.', 'error')
  } finally {
    downloading.value = false
  }
}

onMounted(() => {
  fetchOrder()
})
</script>
