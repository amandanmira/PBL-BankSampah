<template>
  <DashboardLayout title="Keranjang">
    <div class="max-w-4xl mx-auto pb-20">
      <div v-if="cartStore.totalItems === 0" class="flex flex-col items-center justify-center py-20 text-gray-500 bg-white rounded-[2rem] shadow-sm border border-gray-100">
        <Icon icon="material-symbols:shopping-cart-outline" class="w-16 h-16 mb-4 opacity-20" />
        <p class="text-lg font-medium">Keranjang Anda kosong</p>
        <button
          @click="router.push('/dashboard-pengepul/beli-sampah')"
          class="mt-6 px-8 py-3 bg-[#4A7043] text-white rounded-xl font-bold hover:bg-[#3D5C37] transition-all"
        >
          Mulai Belanja
        </button>
      </div>

      <div v-else class="space-y-4">
        <!-- Cart Items (Individual Cards) -->
        <div 
          v-for="item in cartStore.items" 
          :key="item.sampah_id" 
          class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex flex-col gap-4 relative"
        >
          <!-- Delete Button (Top Right) -->
          <button 
            @click="cartStore.removeItem(item.sampah_id)"
            class="absolute top-4 right-4 text-red-500 hover:bg-red-50 p-2 rounded-full transition-all"
          >
            <Icon icon="material-symbols:delete-outline" class="w-5 h-5" />
          </button>

          <div class="flex flex-col sm:flex-row gap-4 sm:gap-6">
            <!-- Image with Discount Badge -->
            <div class="w-24 h-24 sm:w-32 sm:h-32 rounded-xl overflow-hidden bg-gray-50 shrink-0 relative border border-gray-100">
              <img
                v-if="item.item_sampah.foto"
                :src="`${axios.defaults.baseURL}/storage/${item.item_sampah.foto}`"
                class="w-full h-full object-cover"
              />
              <div v-else class="w-full h-full flex items-center justify-center text-gray-300">
                <Icon icon="material-symbols:image-not-supported-outline" class="w-8 h-8" />
              </div>
              
              <div v-if="item.item_sampah.diskon && parseFloat(item.item_sampah.diskon) > 0" class="absolute top-0 right-0 bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-bl-lg">
                -{{ parseFloat(item.item_sampah.diskon) * 100 }}%
              </div>
            </div>

            <!-- Details & Quantity Wrapper -->
            <div class="flex flex-1 flex-col sm:flex-row gap-4 sm:gap-6">
              <!-- Details -->
              <div class="flex-1">
                <h3 class="text-lg font-bold text-gray-800">{{ item.item_sampah.nama }}</h3>
                <div class="flex items-center gap-3 mt-1.5">
                  <span class="bg-gray-100 text-gray-500 text-[9px] px-2 py-0.5 rounded-md font-bold uppercase tracking-wider">{{ item.item_sampah.kategori_sampah.nama_kategori }}</span>
                  <div class="flex items-center gap-2">
                    <div class="w-2.5 h-2.5 rounded-full bg-blue-600 shadow-sm"></div>
                    <span class="text-[10px] font-bold text-gray-600 uppercase tracking-widest">GUDANG {{ item.gudang_id }}</span>
                  </div>
                </div>

                <div class="mt-3 flex items-baseline gap-2">
                  <span class="text-lg font-black text-[#4A7043]">
                    {{ formatCurrency(item.item_sampah.harga_jual * (1 - (parseFloat(item.item_sampah.diskon) || 0))) }}<span class="text-xs font-bold text-gray-400">/kg</span>
                  </span>
                  <span v-if="item.item_sampah.diskon && parseFloat(item.item_sampah.diskon) > 0" class="text-xs text-gray-400 line-through">
                    {{ formatCurrency(item.item_sampah.harga_jual) }}
                  </span>
                </div>
                
                <p class="text-[10px] font-bold text-gray-400 mt-1">Stok: {{ item.stok }} kg</p>
              </div>

              <!-- Quantity Selector -->
              <div class="flex items-center justify-between sm:justify-end self-stretch sm:self-center border-t sm:border-t-0 border-gray-100 pt-3 sm:pt-0">
                <span class="sm:hidden text-xs font-bold text-gray-400">Atur Jumlah:</span>
                <div class="flex items-center bg-gray-50 rounded-lg p-0.5 border border-gray-100">
                  <button
                    @click="updateQty(item.sampah_id, -1)"
                    class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-white transition-colors text-gray-400"
                  >
                    <Icon icon="material-symbols:remove" class="w-4 h-4" />
                  </button>
                  <input
                    type="number"
                    :value="item.quantity"
                    @input="(e) => {
                      const val = parseInt(e.target.value) || 1;
                      cartStore.updateQuantity(item.sampah_id, Math.max(1, Math.min(item.stok, val)));
                      e.target.value = cartStore.items.find(i => i.sampah_id === item.sampah_id).quantity;
                    }"
                    class="w-10 text-center border-none bg-transparent font-bold text-sm text-gray-700 focus:ring-0 p-0"
                  />
                  <button
                    @click="updateQty(item.sampah_id, 1, item.stok)"
                    class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-white transition-colors text-gray-400"
                  >
                    <Icon icon="material-symbols:add" class="w-4 h-4" />
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Detailed Breakdown -->
          <div class="border-t border-gray-50 pt-4 mt-2 space-y-2">
            <div class="flex justify-between text-[11px] text-gray-400 font-medium">
              <span>{{ formatCurrency(item.item_sampah.harga_jual) }} x {{ item.quantity }} kg</span>
              <span>{{ formatCurrency(item.item_sampah.harga_jual * item.quantity) }}</span>
            </div>
            <div v-if="item.item_sampah.diskon && parseFloat(item.item_sampah.diskon) > 0" class="flex justify-between text-[11px] text-red-500 font-bold">
              <span>Diskon {{ parseFloat(item.item_sampah.diskon) * 100 }}%</span>
              <span>-{{ formatCurrency(item.item_sampah.harga_jual * parseFloat(item.item_sampah.diskon) * item.quantity) }}</span>
            </div>
            <div class="pt-2 flex justify-between items-center border-t border-gray-50/50">
              <span class="text-xs font-bold text-gray-500 uppercase tracking-widest">Subtotal:</span>
              <span class="text-xl font-black text-[#4A7043]">
                {{ formatCurrency((item.item_sampah.harga_jual * (1 - (parseFloat(item.item_sampah.diskon) || 0))) * item.quantity) }}
              </span>
            </div>
          </div>
        </div>

        <!-- Summary Card -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 space-y-6">
          <div class="space-y-3">
            <div class="flex justify-between text-sm">
              <span class="text-gray-400 font-bold">Total Item:</span>
              <span class="text-gray-800 font-bold">{{ cartStore.totalItems }} produk</span>
            </div>
            <div class="flex justify-between text-sm">
              <span class="text-gray-400 font-bold">Total Berat:</span>
              <span class="text-gray-800 font-bold">{{ cartStore.totalWeight }} kg</span>
            </div>
            <div class="pt-2 flex justify-between text-sm border-t border-gray-50">
              <span class="text-gray-400 font-medium">Harga Sebelum Diskon:</span>
              <span class="text-gray-600 font-bold">{{ formatCurrency(totalBeforeDiscount) }}</span>
            </div>
            <div class="flex justify-between text-sm">
              <span class="text-red-400 font-medium">Total Potongan Diskon:</span>
              <span class="text-red-500 font-bold">-{{ formatCurrency(totalSavings) }}</span>
            </div>
          </div>
          
          <div class="pt-4 border-t border-gray-100 flex justify-between items-center">
            <h4 class="text-xl font-black text-gray-800">Total Pembayaran:</h4>
            <span class="text-3xl font-black text-[#4A7043]">{{ formatCurrency(cartStore.totalPrice) }}</span>
          </div>
          
          <div class="pt-4">
            <button
              @click="processCheckout"
              :disabled="processing"
              class="w-full py-4 rounded-xl bg-[#4A7043] text-white font-black hover:bg-[#3D5C37] transition-all flex items-center justify-center gap-3 shadow-lg shadow-[#4A7043]/20 disabled:opacity-50"
            >
              <span v-if="processing">Memproses...</span>
              <template v-else>
                Lanjut ke Ringkasan
                <Icon icon="material-symbols:arrow-forward-rounded" class="w-5 h-5" />
              </template>
            </button>
            <p class="text-center text-[10px] text-gray-400 mt-4 font-medium">
              Dengan melanjutkan, Anda menyetujui syarat dan ketentuan yang berlaku
            </p>
          </div>
        </div>
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

// Extra calculations for summary
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

const updateQty = (id, delta, max = 9999) => {
  const item = cartStore.items.find(i => i.sampah_id === id)
  if (item) {
    const newVal = Math.max(1, Math.min(max, item.quantity + delta))
    cartStore.updateQuantity(id, newVal)
  }
}

const formatCurrency = (val) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0
  }).format(val)
}

const processCheckout = () => {
  router.push('/dashboard-pengepul/ringkasan-pembelian')
}
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
