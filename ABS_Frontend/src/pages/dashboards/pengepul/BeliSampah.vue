<template>
  <DashboardLayout title="Beli Sampah">
    <!-- Header Section -->
    <div class="mb-6">
      <div class="relative max-w-xl mx-auto mb-6">
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

      <div class="mb-4">
        <h2 class="text-lg font-bold text-gray-800 mb-3">Daftar Sampah Tersedia</h2>
        <div class="flex flex-wrap gap-2">
          <button
            @click="selectedGudang = null"
            :class="[
              'px-4 py-1.5 rounded-full text-xs font-semibold transition-all',
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
              'px-4 py-1.5 rounded-full text-xs font-semibold transition-all flex items-center gap-2',
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
        <!-- Image Container 1:1 -->
        <div class="relative aspect-square overflow-hidden bg-gray-50">
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
            <h3 class="font-bold text-gray-800 text-sm leading-tight truncate">{{ item.item_sampah.nama }}</h3>
            <div class="flex items-center gap-1 mt-0.5 text-blue-600">
              <Icon icon="material-symbols:location-on" class="w-2.5 h-2.5" />
              <span class="text-[9px] font-bold">Gudang {{ item.gudang_id }}</span>
            </div>
          </div>

          <div class="bg-gray-50 rounded-xl p-2 mb-3 flex justify-between items-center">
            <div>
              <div class="flex items-baseline gap-0.5">
                <span class="text-sm font-black text-[#A86444]">{{ formatCurrency(item.item_sampah.harga_jual * (1 - parseFloat(item.item_sampah.diskon || 0))) }}</span>
                <span class="text-[8px] font-bold text-gray-400">/kg</span>
              </div>
            </div>
            <div class="flex items-center gap-0.5 text-amber-600">
              <Icon icon="material-symbols:inventory-2-outline" class="w-2.5 h-2.5" />
              <span class="text-[9px] font-bold">{{ item.stok }}kg</span>
            </div>
          </div>

          <!-- Quantity Selector Compact -->
          <div class="space-y-2">
            <div class="flex items-center justify-between bg-white border border-gray-100 rounded-lg overflow-hidden shadow-xs h-8">
              <button
                @click="updateQty(item.sampah_id, -1)"
                class="w-8 h-full flex items-center justify-center hover:bg-gray-50 text-gray-400 transition-colors"
              >
                <Icon icon="material-symbols:remove" class="w-3.5 h-3.5" />
              </button>
              <input
                v-model.number="itemQuantities[item.sampah_id]"
                type="number"
                class="w-full text-center border-none focus:ring-0 font-bold text-xs text-gray-700 bg-transparent p-0"
                min="0"
                :max="item.stok"
              />
              <button
                @click="updateQty(item.sampah_id, 1, item.stok)"
                class="w-8 h-full flex items-center justify-center hover:bg-gray-50 text-gray-400 transition-colors"
              >
                <Icon icon="material-symbols:add" class="w-3.5 h-3.5" />
              </button>
            </div>

            <div class="text-center text-[9px] font-bold text-gray-400">
              Total: <span class="text-[#A86444]">{{ formatCurrency((item.item_sampah.harga_jual * (1 - parseFloat(item.item_sampah.diskon || 0))) * (itemQuantities[item.sampah_id] || 0)) }}</span>
            </div>

            <button
              @click="addToCart(item)"
              :disabled="!itemQuantities[item.sampah_id] || itemQuantities[item.sampah_id] <= 0"
              class="w-full py-1.5 rounded-lg bg-white border border-[#4A7043] text-[#4A7043] font-bold text-[10px] hover:bg-[#4A7043] hover:text-white transition-all disabled:opacity-30 flex items-center justify-center gap-1"
            >
              <Icon icon="material-symbols:shopping-cart-outline" class="w-3.5 h-3.5" />
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
