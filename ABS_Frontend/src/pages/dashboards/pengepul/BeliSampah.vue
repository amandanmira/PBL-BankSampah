<template>
  <DashboardLayout title="Beli Sampah">
    <!-- Header Section -->
    <div class="mb-6">
      <div class="flex flex-col md:flex-row items-center gap-4 mb-8">
        <div class="relative flex-1 w-full">
          <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
            <Icon icon="material-symbols:search" class="text-gray-400 w-5 h-5" />
          </div>
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Cari sampah..."
            class="w-full pl-11 pr-4 py-3 bg-white rounded-xl border-none shadow-sm focus:ring-2 focus:ring-[#4A7043] transition-all text-sm text-gray-700"
          />
        </div>
        <button
          @click="showTataCara = true"
          class="flex items-center gap-2 px-6 py-3 bg-white hover:bg-gray-50 text-[#4A7043] font-bold text-sm rounded-xl shadow-sm border border-gray-100 transition-all active:scale-95 shrink-0"
        >
          <Icon icon="material-symbols:menu-book-outline" class="w-5 h-5" />
          Tata Cara
        </button>
      </div>

      <div class="mb-4">
        <h2 class="text-lg font-bold text-gray-800 mb-3">Daftar Sampah Tersedia</h2>
        <div class="flex flex-wrap gap-2">
          <button
            @click="selectedGudang = null"
            :class="[
              'px-4 py-1.5 rounded-full text-xs font-semibold transition-all cursor-pointer',
              selectedGudang === null
                ? 'bg-[#4A7043] text-white shadow-md'
                : 'bg-white text-gray-600 hover:bg-gray-100'
            ]"
          >
            Semua Gudang
          </button>
          <button
            v-for="g in warehouses"
            :key="g.gudang_id"
            @click="selectedGudang = g.gudang_id"
            :class="[
              'px-4 py-1.5 rounded-full text-xs font-semibold transition-all flex items-center gap-2 cursor-pointer',
              selectedGudang === g.gudang_id
                ? 'bg-[#4A7043] text-white shadow-md'
                : 'bg-white text-gray-600 hover:bg-gray-100'
            ]"
          >
            <span>Gudang {{ g.gudang_id }}</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Grid Section -->
    <div v-if="loading" class="flex justify-center py-20">
      <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-[#4A7043]"></div>
    </div>

    <div v-else-if="filteredSampah.length === 0" class="flex flex-col items-center justify-center py-20 text-gray-500 bg-white rounded-[2rem] shadow-sm border border-gray-100">
      <Icon icon="material-symbols:inventory-2-outline" class="w-12 h-12 mb-3 opacity-20" />
      <p class="text-sm">Tidak ada sampah yang tersedia.</p>
    </div>

    <div v-else class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mb-32">
      <div
        v-for="item in filteredSampah"
        :key="item.sampah_id"
        class="bg-white rounded-[1.5rem] overflow-hidden shadow-sm border border-gray-100 hover:shadow-lg transition-all duration-300 group"
      >
        <div class="relative aspect-square overflow-hidden bg-gray-50 cursor-zoom-in" @click="previewImage(`${axios.defaults.baseURL}/storage/${item.item_sampah.foto}`, item.item_sampah.nama)">
          <img
            v-if="item.item_sampah.foto"
            :src="`${axios.defaults.baseURL}/storage/${item.item_sampah.foto}`"
            :alt="item.item_sampah.nama"
            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
            @error="item.item_sampah.foto = null"
          />
          <div v-else class="w-full h-full flex flex-col items-center justify-center text-gray-300 scale-75">
            <Icon icon="material-symbols:image-not-supported-outline" class="w-10 h-10 mb-1" />
            <span class="text-[9px]">No Image</span>
          </div>

          <!-- Discount Badge (Top Right) -->
          <div v-if="item.item_sampah.diskon && parseFloat(item.item_sampah.diskon) > 0" class="absolute top-0 right-0 bg-red-600 text-white px-4 py-2 rounded-bl-2xl text-xs font-black shadow-lg z-10">
            {{ parseFloat(item.item_sampah.diskon) * 100 }}% OFF
          </div>
          <div class="absolute bottom-2 left-2">
            <span class="bg-white/90 backdrop-blur-sm text-[#A86444] text-[8px] font-bold px-2 py-0.5 rounded-full border border-[#A86444]/10">
              {{ item.item_sampah.kategori_sampah.nama_kategori }}
            </span>
          </div>
        </div>

        <!-- Content -->
        <div class="p-3">
          <div class="mb-2">
            <h3 class="font-bold text-gray-800 text-lg leading-tight truncate">{{ item.item_sampah.nama }}</h3>
            <div class="flex items-center gap-1 mt-2 text-blue-600">
              <Icon icon="material-symbols:location-on" class="w-2.5 h-2.5" />
              <span class="text-[12px] font-bold">Gudang {{ item.gudang_id }}</span>
            </div>
          </div>

          <div class="bg-gray-50 rounded-xl p-2 mb-3 flex justify-between items-center">
            <div>
              <div class="flex items-baseline gap-0.5">
                <span class="text-sm font-black text-[#A86444]">{{ formatCurrency(item.item_sampah.harga_jual * (1 - parseFloat(item.item_sampah.diskon || 0))) }}</span>
                <span class="text-[12px] font-bold text-gray-400">/kg</span>
              </div>
            </div>
            <div class="flex items-center gap-0.5 text-amber-600">
              <Icon icon="material-symbols:inventory-2-outline" class="w-2.5 h-2.5" />
              <span class="text-[12px] font-bold">{{ item.stok }}kg</span>
            </div>
          </div>

          <!-- Quantity Selector Compact -->
          <div class="space-y-2">
            <div class="flex items-center justify-between bg-white border border-gray-100 rounded-lg overflow-hidden shadow-xs h-10">
              <button
                @click="updateQty(item.sampah_id, -1)"
                class="w-10 h-full flex items-center justify-center bg-red-50 text-red-500 hover:bg-red-100 transition-colors cursor-pointer"
              >
                <Icon icon="material-symbols:remove" class="w-4 h-4" />
              </button>
              <input
                v-model.number="itemQuantities[item.sampah_id]"
                type="number"
                class="w-full text-center border-none focus:ring-0 font-bold text-sm text-gray-700 bg-transparent p-0"
                min="0"
                :max="item.stok"
              />
              <button
                @click="updateQty(item.sampah_id, 1, item.stok)"
                class="w-10 h-full flex items-center justify-center bg-green-50 text-green-500 hover:bg-green-100 transition-colors cursor-pointer"
              >
                <Icon icon="material-symbols:add" class="w-4 h-4" />
              </button>
            </div>

            <div class="text-center text-[12px] font-bold text-gray-400">
              Total: <span class="text-[#A86444]">{{ formatCurrency((item.item_sampah.harga_jual * (1 - parseFloat(item.item_sampah.diskon || 0))) * (itemQuantities[item.sampah_id] || 0)) }}</span>
            </div>

            <button
              @click="addToCart(item)"
              :disabled="!itemQuantities[item.sampah_id] || itemQuantities[item.sampah_id] <= 0"
              class="w-full py-2.5 rounded-xl bg-white border-2 border-[#4A7043] text-[#4A7043] font-black text-xs hover:bg-[#4A7043] hover:text-white transition-all disabled:opacity-30 flex items-center justify-center gap-2 shadow-sm cursor-pointer"
            >
              <Icon icon="material-symbols:shopping-cart-outline" class="w-4 h-4" />
              Tambah
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Floating Summary Bar Compact -->
    <div
      v-if="cartStore.totalItems > 0"
      class="fixed bottom-6 left-1/2 -translate-x-1/2 w-full max-w-3xl px-4 z-40 transition-all duration-500 animate-in slide-in-from-bottom-10"
    >
      <div class="bg-[#4A7043]/95 backdrop-blur-md rounded-2xl p-4 shadow-2xl flex items-center justify-between text-white border border-white/10">
        <div class="flex items-center gap-4">
          <div class="flex flex-col">
            <span class="text-[9px] font-bold uppercase tracking-wider text-white/60">Total</span>
            <span class="text-xl font-black">{{ formatCurrency(cartStore.totalPrice) }}</span>
          </div>
          <div class="h-8 w-[1px] bg-white/20"></div>
          <div class="flex flex-col">
            <span class="text-[9px] font-bold uppercase tracking-wider text-white/60">Berat</span>
            <span class="text-sm font-bold">{{ cartStore.totalWeight }} kg</span>
          </div>
        </div>
        <button
          @click="router.push('/dashboard-pengepul/keranjang')"
          class="bg-white text-[#4A7043] px-6 py-2.5 rounded-xl font-black text-xs flex items-center gap-2 hover:bg-[#F5F5F0] transition-all hover:scale-105 active:scale-95 shadow-lg"
        >
          KERANJANG
          <div class="bg-[#4A7043] text-white w-5 h-5 rounded-full flex items-center justify-center text-[9px]">
            {{ cartStore.totalItems }}
          </div>
        </button>
      </div>
    </div>

    <!-- Tata Cara Modal -->
    <div v-if="showTataCara" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
      <!-- Backdrop -->
      <div @click="showTataCara = false" class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>
      
      <!-- Modal Content -->
      <div class="relative bg-[#F9F9F5] w-full max-w-xl max-h-[90vh] overflow-y-auto rounded-[2rem] shadow-2xl animate-in zoom-in-95 duration-300">
        <div class="p-8">
          <h2 class="text-2xl font-black text-gray-800 mb-2">Tata Cara Pengambilan Sampah</h2>
          <p class="text-sm text-gray-500 mb-8">Ikuti langkah-langkah berikut untuk melakukan transaksi pembelian sampah dengan lancar.</p>

          <!-- Steps -->
          <div class="space-y-6 mb-8 relative">
            <!-- Timeline Line -->
            <div class="absolute left-5 top-5 bottom-5 w-[1px] bg-gray-200 z-0"></div>

            <div v-for="(step, index) in steps" :key="index" class="flex gap-4 relative z-10">
              <div class="w-10 h-10 rounded-full bg-[#4A7043] flex items-center justify-center text-white font-bold shrink-0 shadow-md">
                {{ index + 1 }}
              </div>
              <div class="pt-1">
                <h3 class="font-bold text-[#4A7043] mb-1">{{ step.title }}</h3>
                <p class="text-sm text-gray-600 leading-relaxed">{{ step.desc }}</p>
              </div>
            </div>
          </div>

          <!-- Important Notes -->
          <div class="bg-[#E6F0EE] rounded-2xl p-6 mb-6">
            <div class="flex items-center gap-2 mb-3 text-[#3B7A77]">
              <Icon icon="material-symbols:info-outline" class="w-5 h-5" />
              <h4 class="font-bold">Catatan Penting</h4>
            </div>
            <ul class="space-y-2">
              <li v-for="(note, index) in notes" :key="index" class="flex gap-2 text-sm text-[#3B7A77]/80 leading-relaxed">
                <span class="mt-1.5 w-1.5 h-1.5 rounded-full bg-[#3B7A77] shrink-0"></span>
                {{ note }}
              </li>
            </ul>
          </div>



          <!-- Close Button -->
          <div class="flex justify-end">
            <button
              @click="showTataCara = false"
              class="px-8 py-3 bg-[#4A7043] text-white font-black text-sm rounded-xl hover:bg-[#3D5C37] transition-all shadow-lg active:scale-95"
            >
              Mengerti
            </button>
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
import { useCartStore } from '@/stores/cart'
import { checkRole } from '@/utils'
import Swal from 'sweetalert2'

checkRole('pengepul')

const router = useRouter()
const axios = inject('axios')
const cartStore = useCartStore()

const loading = ref(true)
const sampahList = ref([])
const warehouses = ref([])
const searchQuery = ref('')
const selectedGudang = ref(null)
const itemQuantities = ref({})
const showTataCara = ref(false)

const steps = [
  {
    title: 'Pilih Sampah',
    desc: 'Pilih jenis sampah dan lokasi gudang, lalu tambahkan ke Keranjang.'
  },
  {
    title: 'Proses Pesanan',
    desc: 'Cek kembali rincian di Keranjang, lalu klik "Proses Pembayaran".'
  },
  {
    title: 'Bayar & Unggah Bukti',
    desc: 'Transfer ke rekening yang tertera dan segera unggah foto bukti pembayarannya.'
  },
  {
    title: 'Tunggu Verifikasi',
    desc: 'Setelah divalidasi, lokasi dan jadwal pengambilan akan dikirim via notifikasi.'
  },
  {
    title: 'Ambil di Gudang',
    desc: 'Datang ke lokasi sesuai jadwal, tunjukkan ID Pesanan, dan bawa pulang pesananmu.'
  }
]

const notes = [
  'Pastikan nominal transfer sesuai dan foto bukti terlihat jelas.',
  'Proses verifikasi membutuhkan waktu 1-2 jam kerja.',
  'Simpan ID Pesanan untuk ditunjukkan saat pengambilan sampah.',
  'Siapkan kendaraan yang memadai dan cek kondisi sampah sebelum dibawa pulang.'
]

const fetchData = async () => {
  try {
    const res = await axios.get('/api/pengepul/daftar-sampah')
    sampahList.value = res.data.sampah
    warehouses.value = res.data.gudang
    
    // Initialize quantities
    sampahList.value.forEach(item => {
      itemQuantities.value[item.sampah_id] = 0
    })
  } catch (err) {
    console.error('Error fetching data:', err)
  } finally {
    loading.value = false
  }
}

const filteredSampah = computed(() => {
  return sampahList.value.filter(item => {
    const matchesSearch = item.item_sampah.nama.toLowerCase().includes(searchQuery.value.toLowerCase())
    const matchesGudang = selectedGudang.value === null || item.gudang_id === selectedGudang.value
    return matchesSearch && matchesGudang
  })
})

const updateQty = (id, delta, max = 9999) => {
  const current = itemQuantities.value[id] || 0
  const newVal = Math.max(0, Math.min(max, current + delta))
  itemQuantities.value[id] = newVal
}

const addToCart = (item) => {
  const qty = itemQuantities.value[item.sampah_id]
  if (qty > 0) {
    cartStore.addItem(item, qty)
    itemQuantities.value[item.sampah_id] = 0 // Reset after adding
    
    Swal.fire({
      icon: 'success',
      title: 'Berhasil',
      text: `${item.item_sampah.nama} ditambahkan`,
      toast: true,
      position: 'top-end',
      showConfirmButton: false,
      timer: 2000,
      timerProgressBar: true,
    })
  }
}

const formatCurrency = (val) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0
  }).format(val)
}

const previewImage = (url, title) => {
  Swal.fire({
    imageUrl: url,
    imageAlt: title,
    background: 'transparent',
    showConfirmButton: false,
    showCloseButton: true,
    width: 'auto',
    padding: '0',
    customClass: {
      image: 'rounded-2xl max-h-[85vh] shadow-2xl',
      closeButton: 'text-red-600 !bg-white hover:!bg-red-50 rounded-xl transition-all shadow-lg border-none focus:shadow-none'
    }
  })
}

onMounted(() => {
  fetchData()
})
</script>

<style scoped>
/* Chrome, Safari, Edge, Opera */
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

/* Firefox */
input[type=number] {
  -moz-appearance: textfield;
}
</style>
