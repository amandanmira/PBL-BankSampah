<template>
  <DashboardLayout title="Ringkasan Pembelian">
    <div class="max-w-4xl mx-auto pb-20">
      <!-- Detail Pembelian Section -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 mb-6">
        <h3 class="text-base font-bold text-gray-500 mb-6 flex items-center gap-2">
          Detail Pembelian ({{ cartStore.totalItems }} item)
        </h3>
        
        <div class="space-y-8">
          <div v-for="item in cartStore.items" :key="item.sampah_id" class="flex gap-6 pb-6 border-b border-gray-50 last:border-0 last:pb-0">
            <!-- Image -->
            <div class="w-24 h-24 rounded-xl overflow-hidden bg-gray-50 shrink-0 border border-gray-100 relative">
              <img
                v-if="item.item_sampah.foto"
                :src="`${axios.defaults.baseURL}/storage/${item.item_sampah.foto}`"
                class="w-full h-full object-cover"
              />
              <div v-else class="w-full h-full flex items-center justify-center text-gray-300">
                <Icon icon="material-symbols:image-not-supported-outline" class="w-8 h-8" />
              </div>
            </div>

            <!-- Info -->
            <div class="flex-1">
              <div class="flex items-start justify-between mb-1">
                <div>
                  <h4 class="font-bold text-gray-800 text-xl leading-tight">{{ item.item_sampah.nama }}</h4>
                  <div class="flex items-center gap-3 mt-1.5">
                    <span class="bg-gray-100 text-gray-500 text-xs px-2 py-0.5 rounded-md font-bold uppercase tracking-wider">{{ item.item_sampah.kategori_sampah.nama_kategori }}</span>
                    <div class="flex items-center gap-2">
                      <div class="w-2 h-2 rounded-full bg-blue-600 shadow-sm"></div>
                      <span class="text-xs font-bold text-gray-600 uppercase tracking-widest">GUDANG {{ item.gudang_id }}</span>
                    </div>
                  </div>
                </div>
              </div>

              <div class="mt-2 text-sm text-gray-400 font-medium">
                <span class="text-[#4A7043] font-bold">{{ formatCurrency(item.item_sampah.harga_jual * (1 - (parseFloat(item.item_sampah.diskon) || 0))) }}</span>
                <span v-if="item.item_sampah.diskon && parseFloat(item.item_sampah.diskon) > 0" class="line-through ml-1">{{ formatCurrency(item.item_sampah.harga_jual) }}</span>
                <span> /kg × {{ item.quantity }} kg</span>
              </div>

              <div v-if="item.item_sampah.diskon && parseFloat(item.item_sampah.diskon) > 0" class="mt-1 flex items-center gap-1 text-xs font-bold text-red-500">
                <Icon icon="material-symbols:sell-outline" class="w-3 h-3" />
                Diskon {{ parseFloat(item.item_sampah.diskon) * 100 }}% - Hemat {{ formatCurrency(item.item_sampah.harga_jual * parseFloat(item.item_sampah.diskon) * item.quantity) }}
              </div>

              <div class="mt-4 flex justify-between items-center text-base">
                <span class="text-gray-400 font-medium">Subtotal:</span>
                <span class="font-black text-gray-700">{{ formatCurrency((item.item_sampah.harga_jual * (1 - (parseFloat(item.item_sampah.diskon) || 0))) * item.quantity) }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Ringkasan Pembayaran Section -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 mb-6">
        <h3 class="text-base font-bold text-gray-500 mb-6">Ringkasan Pembayaran</h3>
        
        <div class="space-y-4">
          <div class="flex justify-between text-base text-gray-500">
            <span>Total Harga</span>
            <span class="font-bold text-gray-700">{{ formatCurrency(totalBeforeDiscount) }}</span>
          </div>
          <div class="flex justify-between text-base text-red-500">
            <span>Total Diskon</span>
            <span class="font-bold">- {{ formatCurrency(totalSavings) }}</span>
          </div>
          <div class="flex justify-between text-base text-gray-500">
            <span>Total Berat</span>
            <span class="font-bold text-gray-700">{{ cartStore.totalWeight }} kg</span>
          </div>
          
          <div class="pt-6 mt-2 border-t border-gray-100 flex justify-between items-center">
            <h4 class="text-xl font-black text-gray-800">Total Pembayaran</h4>
            <span class="text-3xl font-black text-[#A86444]">{{ formatCurrency(cartStore.totalPrice) }}</span>
          </div>
        </div>
      </div>

      <!-- Alur Pembelian Info Box -->
      <div class="bg-blue-50 rounded-3xl p-8 mb-10 border border-blue-100/50">
        <div class="flex items-center gap-3 mb-8 text-[#1E40AF]">
          <Icon icon="material-symbols:info-outline" class="w-6 h-6" />
          <h2 class="text-2xl font-black">Alur Pembelian</h2>
        </div>

        <div class="space-y-6">
          <div v-for="(alur, index) in alurPembelian" :key="index" class="flex gap-4">
            <div class="w-8 h-8 rounded-full bg-[#2563EB] flex items-center justify-center text-white font-bold shrink-0 text-base shadow-sm">
              {{ index + 1 }}
            </div>
            <div>
              <h4 class="font-bold text-gray-800 text-lg mb-1">{{ alur.title }}</h4>
              <p class="text-base text-[#1E40AF]/70 leading-relaxed">{{ alur.desc }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <button
          @click="router.back()"
          class="py-4 rounded-xl border-2 border-red-500 text-red-500 font-black text-base hover:bg-red-50 transition-all flex items-center justify-center cursor-pointer"
        >
          Batal
        </button>
        <button
          @click="processFinalPayment"
          :disabled="processing"
          class="py-4 rounded-xl bg-[#4A7043] text-white font-black text-base hover:bg-[#3D5C37] transition-all flex items-center justify-center gap-2 shadow-lg shadow-[#4A7043]/20 disabled:opacity-50 cursor-pointer"
        >
          <Icon icon="material-symbols:account-balance-wallet-outline" class="w-5 h-5" />
          <span v-if="processing">Memproses...</span>
          <span v-else>Konfirmasi & Proses Pembayaran</span>
        </button>
      </div>
    </div>
  </DashboardLayout>
</template>

<script setup>
import { ref, computed, inject } from 'vue'
import { useRouter } from 'vue-router'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import { Icon } from '@iconify/vue'
import { useCartStore } from '@/stores/cart'
import { checkRole } from '@/utils'
import Swal from 'sweetalert2'

checkRole('pengepul')

const router = useRouter()
const axios = inject('axios')
const cartStore = useCartStore()
const processing = ref(false)

const user = JSON.parse(sessionStorage.getItem('user') || '{}')
const pengepul_id = user.pengepul_id || user.pengepul?.pengepul_id

const alurPembelian = [
  {
    title: 'Pesanan Dibuat',
    desc: 'Dengan menekan tombol "Proses Pembayaran" Anda akan diarahkan ke halaman pembayaran'
  },
  {
    title: 'Pembayaran (24 Jam)',
    desc: 'Lakukan pembayaran di luar sistem, lalu upload bukti pembayaran'
  },
  {
    title: 'Validasi Pembayaran',
    desc: 'Petugas akan validasi bukti pembayaran Anda'
  },
  {
    title: 'Ambil Barang',
    desc: 'Setelah validasi, ambil barang di gudang sampah'
  }
]

const totalBeforeDiscount = computed(() => {
  return cartStore.items.reduce((acc, item) => {
    return acc + (item.item_sampah.harga_jual * item.quantity)
  }, 0)
})

const totalSavings = computed(() => {
  return cartStore.items.reduce((acc, item) => {
    const savingsPerKg = item.item_sampah.harga_jual * (parseFloat(item.item_sampah.diskon) || 0)
    return acc + (savingsPerKg * item.quantity)
  }, 0)
})

const formatCurrency = (val) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0
  }).format(val)
}

const processFinalPayment = async () => {
  if (!pengepul_id) {
    Swal.fire('Error', 'Data pengepul tidak ditemukan. Silakan login kembali.', 'error')
    return
  }

  const result = await Swal.fire({
    title: 'Konfirmasi Pembelian',
    text: 'Apakah Anda yakin ingin memproses pembelian ini?',
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#4A7043',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Ya, Proses!',
    cancelButtonText: 'Batal'
  })

  if (result.isConfirmed) {
    processing.value = true
    try {
      const payload = {
        pengepul_id: pengepul_id,
        detail: cartStore.items.map(item => ({
          sampah_id: item.sampah_id,
          berat: item.quantity,
          harga: item.item_sampah.harga_jual * (1 - (parseFloat(item.item_sampah.diskon) || 0))
        }))
      }

      const res = await axios.post('/api/pengepul/request-pembelian', payload)
      const orderId = res.data.transaksi_id
      
      cartStore.clearCart()
      
      await Swal.fire({
        title: 'Berhasil!',
        text: 'Permintaan pembelian Anda telah berhasil dibuat. Silakan lakukan pembayaran.',
        icon: 'success',
        confirmButtonColor: '#4A7043'
      })

      router.push(`/dashboard-pengepul/pesanan/${orderId}`)
    } catch (err) {
      console.error('Checkout error:', err)
      Swal.fire({
        title: 'Gagal',
        text: err.response?.data?.message || 'Terjadi kesalahan saat memproses pembelian.',
        icon: 'error'
      })
    } finally {
      processing.value = false
    }
  }
}
</script>
