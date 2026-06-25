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
            class="w-full pl-11 pr-4 py-3 bg-white rounded-xl border-none shadow-sm focus:ring-2 focus:ring-[#4A7043] transition-all text-base text-gray-700"
          />
        </div>
        <button
          @click="showTataCara = true"
          class="flex items-center gap-2 px-6 py-3 bg-[#4A7043] hover:bg-[#3D5C37] text-white font-bold text-base rounded-xl shadow-md transition-all active:scale-95 shrink-0 cursor-pointer"
        >
          <Icon icon="material-symbols:menu-book-outline" class="w-5 h-5" />
          Tata Cara
        </button>
      </div>

      <div class="mb-4 relative z-20">
        <h2 class="text-xl font-bold text-gray-800 mb-3">Daftar Sampah Tersedia</h2>
        <div class="relative w-full sm:max-w-md">
          <!-- Trigger Button -->
          <button 
            @click="isDropdownOpen = !isDropdownOpen" 
            class="w-full flex items-center justify-between bg-white border border-gray-200 hover:border-[#4A7043] rounded-xl px-4 py-3 shadow-sm transition-all text-left cursor-pointer focus:outline-none focus:ring-2 focus:ring-[#4A7043]/50"
          >
             <div class="flex items-center gap-3 overflow-hidden">
               <Icon icon="material-symbols:warehouse-outline" class="text-gray-500 w-5 h-5 shrink-0" />
               <span class="truncate text-sm font-semibold text-gray-700">
                 {{ selectedGudang ? getGudangAddress(selectedGudang) : 'Semua Gudang' }}
               </span>
             </div>
             <Icon icon="material-symbols:expand-more" class="text-gray-500 w-5 h-5 shrink-0 transition-transform duration-300" :class="{'rotate-180': isDropdownOpen}" />
          </button>

          <!-- Dropdown Menu -->
          <transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="transform scale-95 opacity-0"
            enter-to-class="transform scale-100 opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="transform scale-100 opacity-100"
            leave-to-class="transform scale-95 opacity-0"
          >
            <div 
              v-if="isDropdownOpen" 
              class="absolute z-50 w-full mt-2 bg-white border border-gray-100 rounded-xl shadow-xl max-h-[60vh] overflow-y-auto custom-scrollbar"
            >
              <button 
                @click="selectedGudang = null; isDropdownOpen = false" 
                :class="['w-full text-left px-5 py-3.5 transition-colors border-b border-gray-50 text-sm font-semibold flex items-center gap-3', selectedGudang === null ? 'bg-[#F5F8F5] text-[#4A7043]' : 'text-gray-700 hover:bg-gray-50']"
              >
                <div class="w-2.5 h-2.5 rounded-full bg-gray-300 shadow-sm shrink-0"></div>
                Semua Gudang
              </button>
              <button 
                v-for="g in warehouses" 
                :key="g.gudang_id" 
                @click="selectedGudang = g.gudang_id; isDropdownOpen = false" 
                :class="['w-full text-left px-5 py-3.5 transition-colors border-b border-gray-50 text-sm flex items-start gap-3', selectedGudang === g.gudang_id ? 'bg-[#F5F8F5] text-[#4A7043] font-semibold' : 'text-gray-600 hover:bg-gray-50 font-medium']"
              >
                <div :class="['w-2.5 h-2.5 rounded-full shadow-sm shrink-0 mt-1', getGudangColor(g.gudang_id)]"></div>
                <span class="leading-relaxed">{{ g.alamat }}</span>
              </button>
            </div>
          </transition>
          
          <!-- Invisible overlay to close dropdown when clicking outside -->
          <div v-if="isDropdownOpen" @click="isDropdownOpen = false" class="fixed inset-0 z-40"></div>
        </div>
      </div>
    </div>

    <!-- Grid Section -->
    <div v-if="loading" class="flex justify-center py-20">
      <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-[#4A7043]"></div>
    </div>

    <div v-else-if="filteredSampah.length === 0" class="flex flex-col items-center justify-center py-20 text-gray-500 bg-white rounded-[2rem] shadow-sm border border-gray-100">
      <Icon icon="material-symbols:inventory-2-outline" class="w-12 h-12 mb-3 opacity-20" />
      <p class="text-base">Tidak ada sampah yang tersedia.</p>
    </div>

    <div v-else class="pb-36">
      <div :class="['grid gap-4 sm:gap-6 xl:gap-8 mb-8 transition-all duration-300', isSidebarCollapsed ? 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4' : 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3']">
        <div
          v-for="item in paginatedSampah"
          :key="item.sampah_id"
          class="bg-white rounded-3xl overflow-hidden shadow-sm border border-gray-100 hover:shadow-lg transition-all duration-300 group flex flex-col"
        >
          <div class="relative aspect-square overflow-hidden bg-gray-50 cursor-zoom-in shrink-0" @click="previewImage(`${axios.defaults.baseURL}/storage/${item.item_sampah.foto}`, item.item_sampah.nama)">
            <img
              v-if="item.item_sampah.foto"
              :src="`${axios.defaults.baseURL}/storage/${item.item_sampah.foto}`"
              :alt="item.item_sampah.nama"
              class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
              @error="item.item_sampah.foto = null"
            />
            <div v-else class="w-full h-full flex flex-col items-center justify-center text-gray-300 scale-75">
              <Icon icon="material-symbols:image-not-supported-outline" class="w-10 h-10 mb-1" />
              <span class="text-xs">No Image</span>
            </div>

            <!-- Discount Badge (Top Right) -->
            <div v-if="item.item_sampah.diskon && parseFloat(item.item_sampah.diskon) > 0" class="absolute top-0 right-0 bg-red-600 text-white px-4 py-2 rounded-bl-2xl text-sm font-black shadow-lg z-10">
              {{ parseFloat(item.item_sampah.diskon) * 100 }}% OFF
            </div>
            <div class="absolute bottom-2 left-2">
              <span class="bg-white/95 backdrop-blur-sm text-[#A86444] text-[11px] font-bold px-2 py-0.5 rounded-full border border-[#A86444]/10">
                {{ item.item_sampah.kategori_sampah.nama_kategori }}
              </span>
            </div>
          </div>

          <!-- Content -->
          <div class="p-5 flex flex-col flex-1 justify-between">
            <div class="mb-4">
              <h3 class="font-bold text-gray-800 text-xl leading-tight truncate">{{ item.item_sampah.nama }}</h3>
              <div class="flex items-start gap-2 mt-2">
                <div :class="['w-2.5 h-2.5 rounded-full shadow-sm shrink-0 mt-1', getGudangColor(item.gudang_id)]"></div>
                <span class="text-[10px] font-bold text-gray-500 uppercase tracking-widest leading-relaxed">{{ getGudangAddress(item.gudang_id) }}</span>
              </div>
            </div>

            <div class="bg-gray-50 rounded-2xl p-3 mb-4 flex justify-between items-center flex-wrap gap-2">
              <div>
                <div class="flex items-baseline gap-0.5">
                  <span class="text-lg font-black text-[#A86444]">{{ formatCurrency(item.item_sampah.harga_jual * (1 - parseFloat(item.item_sampah.diskon || 0))) }}</span>
                  <span class="text-xs font-bold text-gray-400">/kg</span>
                </div>
              </div>
              <div class="flex items-center gap-1 text-[#4A7043] bg-[#E8F0E6] px-2.5 py-1 rounded-full text-xs font-bold">
                <Icon icon="material-symbols:inventory-2-outline" class="w-3.5 h-3.5" />
                <span>Stok: {{ item.stok }}kg</span>
              </div>
            </div>

            <!-- Quantity Selector Compact -->
            <div class="space-y-3">
              <div class="flex items-center justify-between bg-gray-50 border border-gray-100 rounded-xl overflow-hidden shadow-inner h-11">
                <button
                  @click="updateQty(item.sampah_id, -1)"
                  class="w-11 h-full flex items-center justify-center bg-white text-red-500 hover:bg-red-50 transition-colors cursor-pointer border-r border-gray-100"
                >
                  <Icon icon="material-symbols:remove" class="w-5 h-5" />
                </button>
                <input
                  v-model.number="itemQuantities[item.sampah_id]"
                  type="number"
                  class="w-full text-center border-none focus:ring-0 font-bold text-base text-gray-800 bg-transparent p-0"
                  min="0"
                  :max="item.stok"
                />
                <button
                  @click="updateQty(item.sampah_id, 1, item.stok)"
                  class="w-11 h-full flex items-center justify-center bg-white text-[#4A7043] hover:bg-green-50 transition-colors cursor-pointer border-l border-gray-100"
                >
                  <Icon icon="material-symbols:add" class="w-5 h-5" />
                </button>
              </div>

              <div class="text-center text-sm font-semibold text-gray-500">
                Total: <span class="text-[#A86444] font-bold">{{ formatCurrency((item.item_sampah.harga_jual * (1 - parseFloat(item.item_sampah.diskon || 0))) * (itemQuantities[item.sampah_id] || 0)) }}</span>
              </div>

              <button
                @click="addToCart(item)"
                :disabled="!itemQuantities[item.sampah_id] || itemQuantities[item.sampah_id] <= 0"
                class="w-full py-3 rounded-xl bg-[#4A7043] hover:bg-[#3D5C37] text-white font-bold text-sm transition-all disabled:opacity-40 disabled:hover:bg-[#4A7043] flex items-center justify-center gap-2 shadow-sm cursor-pointer active:scale-95"
              >
                <Icon icon="material-symbols:shopping-cart-outline" class="w-4 h-4" />
                + Keranjang
              </button>
            </div>
          </div>
        </div>
      </div>
      <!-- Pagination Controls -->
      <div v-if="totalPages > 1" class="flex justify-center items-center gap-2 mt-8">
        <button
          @click="currentPage > 1 && (currentPage--)"
          :disabled="currentPage === 1"
          class="p-2 rounded-xl bg-white border border-gray-100 text-gray-400 disabled:opacity-30 hover:bg-gray-50 transition-all cursor-pointer shadow-sm"
        >
          <Icon icon="material-symbols:chevron-left" class="w-6 h-6" />
        </button>
        
        <div class="flex items-center gap-1">
          <template v-for="(page, index) in visiblePages" :key="index">
            <span v-if="page === '...'" class="px-2 text-gray-400 font-bold">...</span>
            <button
              v-else
              @click="currentPage = page"
              :class="[
                'w-10 h-10 rounded-xl font-bold text-base transition-all cursor-pointer shadow-sm flex items-center justify-center',
                currentPage === page
                  ? 'bg-[#4A7043] text-white'
                  : 'bg-white text-gray-500 border border-gray-100 hover:bg-gray-50'
              ]"
            >
              {{ page }}
            </button>
          </template>
        </div>

        <button
          @click="currentPage < totalPages && (currentPage++)"
          :disabled="currentPage === totalPages"
          class="p-2 rounded-xl bg-white border border-gray-100 text-gray-400 disabled:opacity-30 hover:bg-gray-50 transition-all cursor-pointer shadow-sm"
        >
          <Icon icon="material-symbols:chevron-right" class="w-6 h-6" />
        </button>
      </div>
    </div>

    <!-- Floating Help Button (FAB) -->
    <button
      @click="showTataCara = true"
      :class="[
        'fixed right-4 sm:right-6 w-12 sm:w-14 h-12 sm:h-14 bg-[#4A7043] hover:bg-[#3D5C37] text-white rounded-full flex items-center justify-center shadow-2xl transition-all duration-500 active:scale-95 z-50 cursor-pointer',
        cartStore.totalItems > 0 ? 'bottom-44 sm:bottom-32' : 'bottom-6 sm:bottom-8'
      ]"
      title="Tata Cara Pembelian"
    >
      <Icon icon="material-symbols:help" class="w-6 h-6 sm:w-7 sm:h-7" />
    </button>

    <!-- Floating Summary Bar Compact -->
    <div
      v-if="cartStore.totalItems > 0"
      :class="[
        'fixed bottom-4 sm:bottom-6 z-40 transition-all duration-500 animate-in slide-in-from-bottom-10 -translate-x-1/2 max-w-3xl',
        isSidebarCollapsed 
          ? 'left-1/2 lg:left-[calc(50%+3rem)] w-[calc(100vw-2rem)] sm:w-[calc(100vw-4rem)] lg:w-[calc(100vw-6rem-4rem)]' 
          : 'left-1/2 lg:left-[calc(50%+10rem)] w-[calc(100vw-2rem)] sm:w-[calc(100vw-4rem)] lg:w-[calc(100vw-20rem-4rem)]'
      ]"
    >
      <div class="bg-[#4A7043]/95 backdrop-blur-md rounded-2xl p-3 sm:p-4 shadow-2xl flex flex-col sm:flex-row items-center justify-between gap-3 sm:gap-4 text-white border border-white/10">
        <div class="flex items-center justify-between w-full sm:w-auto gap-4">
          <div class="flex flex-col">
            <span class="text-[10px] sm:text-xs font-bold uppercase tracking-wider text-white/60">Total</span>
            <span class="text-xl sm:text-2xl font-black">{{ formatCurrency(cartStore.totalPrice) }}</span>
          </div>
          <div class="h-8 w-[1px] bg-white/20"></div>
          <div class="flex flex-col text-right sm:text-left">
            <span class="text-[10px] sm:text-xs font-bold uppercase tracking-wider text-white/60">Berat</span>
            <span class="text-sm sm:text-base font-bold">{{ cartStore.totalWeight }} kg</span>
          </div>
        </div>
        <div class="flex items-center justify-between sm:justify-end w-full sm:w-auto gap-2 sm:gap-3">
          <button
            @click="showAlurPembelian = true"
            class="flex-1 sm:flex-none justify-center bg-white/10 hover:bg-white/20 text-white px-3 sm:px-4 py-2 sm:py-2.5 rounded-xl font-bold text-xs sm:text-sm flex items-center gap-1 sm:gap-2 transition-all active:scale-95 border border-white/10"
          >
            <Icon icon="material-symbols:info-outline" class="w-4 h-4" />
            <span class="hidden sm:inline">Alur Pembelian</span>
            <span class="inline sm:hidden">Alur</span>
          </button>
          <button
            @click="router.push('/dashboard-pengepul/keranjang')"
            class="flex-1 sm:flex-none justify-center bg-white text-[#4A7043] px-4 sm:px-6 py-2 sm:py-2.5 rounded-xl font-black text-xs sm:text-sm flex items-center gap-2 hover:bg-[#F5F5F0] transition-all hover:scale-105 active:scale-95 shadow-lg"
          >
            KERANJANG
            <div class="bg-[#4A7043] text-white w-5 h-5 rounded-full flex items-center justify-center text-[10px] sm:text-xs">
              {{ cartStore.totalItems }}
            </div>
          </button>
        </div>
      </div>
    </div>

    <!-- Tata Cara Modal -->
    <div v-if="showTataCara" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
      <!-- Backdrop -->
      <div @click="showTataCara = false" class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>
      
      <!-- Modal Content -->
      <div class="relative bg-[#F9F9F5] w-full max-w-xl max-h-[90vh] overflow-y-auto rounded-[2rem] shadow-2xl animate-in zoom-in-95 duration-300 border border-gray-100">
        <div class="p-8">
          <h2 class="text-3xl font-black text-gray-800 mb-2">Tata Cara Pengambilan Sampah</h2>
          <p class="text-base text-gray-500 mb-8">Ikuti langkah-langkah berikut untuk melakukan transaksi pembelian sampah dengan lancar.</p>

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
          <div class="bg-[#E6F0EE] rounded-2xl p-6 mb-8">
            <div class="flex items-center gap-2 mb-4 text-[#3B7A77]">
              <Icon icon="material-symbols:info-outline" class="w-5 h-5 animate-pulse" />
              <h4 class="font-bold">Catatan Penting</h4>
            </div>
            <ul class="space-y-3">
              <li v-for="(note, index) in notes" :key="index" class="flex gap-2.5 text-sm text-[#3B7A77]/90 leading-relaxed">
                <span class="mt-2 w-1.5 h-1.5 rounded-full bg-[#3B7A77] shrink-0"></span>
                <span>{{ note }}</span>
              </li>
            </ul>
          </div>

          <!-- Contact Center Box -->
          <div class="border border-dashed border-gray-200 rounded-2xl p-5 mb-8 flex items-center gap-4 bg-white shadow-xs">
            <div class="w-12 h-12 rounded-xl bg-amber-50 flex items-center justify-center text-amber-600 shrink-0">
              <Icon icon="material-symbols:support-agent" class="w-7 h-7" />
            </div>
            <div>
              <h5 class="text-sm font-bold text-gray-800">Butuh Bantuan Lebih Lanjut?</h5>
              <p class="text-xs text-gray-500 mt-0.5">Hubungi customer service kami jika Anda mengalami kendala saat bertransaksi.</p>
            </div>
          </div>

          <!-- Close Button -->
          <div class="flex justify-end gap-3">
            <button
              @click="showTataCara = false"
              class="w-full sm:w-auto px-8 py-3.5 bg-[#4A7043] text-white font-bold text-base rounded-xl hover:bg-[#3D5C37] transition-all shadow-md active:scale-95 cursor-pointer text-center"
            >
              Mengerti
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Alur Pembelian Modal -->
    <div v-if="showAlurPembelian" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
      <div @click="showAlurPembelian = false" class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>
      <div class="relative bg-white w-full max-w-lg rounded-[2rem] shadow-2xl animate-in zoom-in-95 duration-300 overflow-hidden">
        <div class="p-8">
          <div class="flex items-center gap-3 mb-8 text-[#1E40AF]">
            <Icon icon="material-symbols:info-outline" class="w-6 h-6" />
            <h2 class="text-2xl font-black">Alur Pembelian</h2>
          </div>

          <div class="space-y-8">
            <div v-for="(alur, index) in alurPembelian" :key="index" class="flex gap-4">
              <div class="w-8 h-8 rounded-full bg-[#2563EB] flex items-center justify-center text-white font-bold shrink-0 text-base shadow-sm">
                {{ index + 1 }}
              </div>
              <div>
                <h4 class="font-bold text-gray-800 text-base mb-1">{{ alur.title }}</h4>
                <p class="text-sm text-gray-500 leading-relaxed">{{ alur.desc }}</p>
              </div>
            </div>
          </div>

          <div class="mt-10 flex justify-end">
            <button
              @click="showAlurPembelian = false"
              class="px-8 py-2.5 bg-[#2563EB] text-white font-bold text-base rounded-xl hover:bg-[#1E40AF] transition-all shadow-lg active:scale-95 cursor-pointer"
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
import { ref, computed, onMounted, inject, watch } from 'vue'
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
const isSidebarCollapsed = inject('isSidebarCollapsed', ref(false))

const loading = ref(true)
const sampahList = ref([])
const warehouses = ref([])
const searchQuery = ref('')
const selectedGudang = ref(null)
const itemQuantities = ref({})
const showTataCara = ref(false)
const showAlurPembelian = ref(false)
const currentPage = ref(1)
const itemsPerPage = 8
const isDropdownOpen = ref(false)

const alurPembelian = [
  {
    title: 'Pesanan Dibuat',
    desc: 'Dengan menekan tombol "Proses Pembayaran" Anda akan diarahkan ke halaman pembayaran'
  },
  {
    title: 'Pembayaran',
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

// Reset page to 1 when filters change
watch([searchQuery, selectedGudang], () => {
  currentPage.value = 1
})

const steps = [
  {
    title: 'Pilih Sampah yang Ingin Dibeli',
    desc: 'Telusuri daftar sampah yang tersedia pada sistem dan pilih jenis sampah yang sesuai dengan kebutuhan Anda. Pilih sampah dari gudang mana yang ingin Anda beli, kemudian tambahkan ke keranjang belanja untuk melanjutkan proses pembelian.'
  },
  {
    title: 'Tinjau Pesanan di Keranjang',
    desc: 'Setelah itu, pergi ke menu Keranjang dan tinjau kembali rincian pesanan beserta total pembayaran yang harus dibayarkan. Klik tombol "Proses Pembayaran" untuk melanjutkan ke tahap pembayaran.'
  },
  {
    title: 'Lakukan Pembayaran',
    desc: 'Lakukan pembayaran melalui transfer bank ke salah satu rekening yang tertera pada halaman pembayaran. Setelah transfer berhasil, ambil screenshot atau foto bukti pembayaran, lalu unggah bukti tersebut ke dalam sistem dan kirim untuk diverifikasi.'
  },
  {
    title: 'Verifikasi Pembayaran',
    desc: 'Petugas akan melakukan verifikasi terhadap bukti pembayaran yang telah Anda kirimkan. Setelah pembayaran dinyatakan valid, Anda akan menerima notifikasi berisi informasi detail mengenai lokasi dan jadwal pengambilan sampah.'
  },
  {
    title: 'Ambil Sampah di Gudang',
    desc: 'Datang ke lokasi gudang sesuai dengan jenis sampah yang telah Anda beli. Tunjukkan ID Pesanan kepada petugas gudang, dan sampah yang telah Anda beli akan diserahkan kepada Anda.'
  }
]

const notes = [
  'Pastikan nominal transfer sesuai dengan total pembayaran',
  'Unggah bukti pembayaran yang jelas dan dapat terbaca dengan baik',
  'Verifikasi pembayaran dilakukan oleh petugas dalam waktu 1-2 jam kerja',
  'Simpan ID Pesanan untuk ditunjukkan saat pengambilan sampah',
  'Siapkan kendaraan dan wadah yang sesuai untuk mengangkut sampah',
  'Periksa kondisi dan kualitas sampah sebelum membawa pulang'
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
  const filtered = sampahList.value.filter(item => {
    const matchesSearch = item.item_sampah.nama.toLowerCase().includes(searchQuery.value.toLowerCase())
    const matchesGudang = selectedGudang.value === null || Number(item.gudang_id) === Number(selectedGudang.value)
    return matchesSearch && matchesGudang
  })

  // Sort by discount (descending)
  return filtered.sort((a, b) => {
    const diskonA = parseFloat(a.item_sampah.diskon || 0)
    const diskonB = parseFloat(b.item_sampah.diskon || 0)
    return diskonB - diskonA
  })
})

const paginatedSampah = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage
  const end = start + itemsPerPage
  return filteredSampah.value.slice(start, end)
})

const totalPages = computed(() => Math.ceil(filteredSampah.value.length / itemsPerPage))

const visiblePages = computed(() => {
  const pages = []
  const current = currentPage.value
  const total = totalPages.value

  if (total <= 7) {
    for (let i = 1; i <= total; i++) pages.push(i)
  } else {
    if (current <= 4) {
      pages.push(1, 2, 3, 4, 5, '...', total)
    } else if (current >= total - 3) {
      pages.push(1, '...', total - 4, total - 3, total - 2, total - 1, total)
    } else {
      pages.push(1, '...', current - 1, current, current + 1, '...', total)
    }
  }
  return pages
})

const getGudangAddress = (id) => {
  const gudang = warehouses.value.find(g => Number(g.gudang_id) === Number(id))
  return gudang ? gudang.alamat : `Gudang ${id}`
}

const getGudangColor = (id) => {
  const colors = [
    'bg-blue-500', 'bg-red-500', 'bg-emerald-500', 'bg-amber-500', 
    'bg-purple-500', 'bg-pink-500', 'bg-indigo-500', 'bg-teal-500', 
    'bg-orange-500', 'bg-cyan-500'
  ]
  return colors[Number(id) % colors.length]
}

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
