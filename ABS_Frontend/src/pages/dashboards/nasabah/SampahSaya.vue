<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { inject } from 'vue'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import { Icon } from '@iconify/vue'
import { cn } from '@/lib/utils'
import Swal from 'sweetalert2'

const axios = inject('axios')
const loading = ref(false)
const transactions = ref([])
const manualTransactions = ref([])
const activeMainTab = ref('jemput') // 'jemput' or 'setor'
const activeStatusFilter = ref('pending') // pending, proses, dijemput, selesai, tolak, batal
const searchQuery = ref('')
const showDetailModal = ref(false)
const selectedItem = ref(null)
const currentPhotoIndex = ref(0)
const activeDetailTab = ref('jemput') // 'jemput' or 'timbang'

const openDetail = (item) => {
  selectedItem.value = item
  currentPhotoIndex.value = 0
  if (activeMainTab.value === 'jemput') {
    activeDetailTab.value = 'jemput'
  } else {
    activeDetailTab.value = 'timbang'
  }
  showDetailModal.value = true
}

const closeDetail = () => {
  showDetailModal.value = false
  selectedItem.value = null
}

const statusFilters = [
  { label: 'Menunggu', value: 'pending', icon: 'material-symbols:schedule' },
  { label: 'Diproses', value: 'proses', icon: 'material-symbols:sync' },
  { label: 'Dijemput', value: 'dijemput', icon: 'material-symbols:local-shipping' },
  { label: 'Selesai', value: 'selesai', icon: 'material-symbols:check-circle' },
  { label: 'Ditolak', value: 'tolak', icon: 'material-symbols:cancel' },
  { label: 'Dibatalkan', value: 'batal', icon: 'material-symbols:block' },
]

const visibleStatusFilters = computed(() => {
  if (activeMainTab.value === 'setor') {
    return statusFilters.filter((f) => f.value === 'selesai')
  }
  return statusFilters
})

watch(activeMainTab, (newTab) => {
  if (newTab === 'setor') {
    activeStatusFilter.value = 'selesai'
  } else {
    activeStatusFilter.value = 'pending'
  }
})

const fetchTransactions = async () => {
  loading.value = true
  try {
    const [penjemputanRes, manualRes] = await Promise.all([
      axios.get('/api/nasabah/penjemputan-nasabah'),
      axios.get('/api/nasabah/setor-manual-nasabah'),
    ])
    console.log(penjemputanRes.data.data)
    console.log(manualRes.data.data)
    transactions.value = penjemputanRes.data.data || []
    manualTransactions.value = manualRes.data.data || []
  } catch (error) {
    console.error('Failed to fetch transactions:', error)
  } finally {
    loading.value = false
  }
}

const counts = computed(() => {
  const result = {
    pending: 0,
    proses: 0,
    dijemput: 0,
    selesai: 0,
    tolak: 0,
    batal: 0,
  }

  if (activeMainTab.value === 'jemput') {
    transactions.value.forEach((t) => {
      if (t.status === 'dijemput' || t.status === 'perlu_input') {
        result.dijemput++
      } else if (result[t.status] !== undefined) {
        result[t.status]++
      }
    })
  } else {
    manualTransactions.value.forEach((t) => {
      if (result[t.status] !== undefined) {
        result[t.status]++
      }
    })
  }
  return result
})

const filteredTransactions = computed(() => {
  const data = activeMainTab.value === 'jemput' ? transactions.value : manualTransactions.value

  return data.filter((t) => {
    let matchesStatus = t.status === activeStatusFilter.value
    if (activeStatusFilter.value === 'dijemput') {
      matchesStatus = t.status === 'dijemput' || t.status === 'perlu_input'
    }
    const query = searchQuery.value.toLowerCase()

    if (activeMainTab.value === 'jemput') {
      const matchesSearch =
        searchQuery.value === '' ||
        t.penjemputan_id.toString().includes(query) ||
        (t.deskripsi && t.deskripsi.toLowerCase().includes(query)) ||
        (t.detail_penjemputan &&
          t.detail_penjemputan.some((d) =>
            d.sampah?.item_sampah?.nama.toLowerCase().includes(query),
          ))
      return matchesStatus && matchesSearch
    } else {
      const matchesSearch =
        searchQuery.value === '' ||
        t.transaksi_id.toString().includes(query) ||
        (t.penimbangan &&
          t.penimbangan.some((p) => p.sampah?.item_sampah?.nama.toLowerCase().includes(query)))
      return matchesStatus && matchesSearch
    }
  })
})

const cancelTransaction = async (id) => {
  const result = await Swal.fire({
    title: 'Apakah Anda yakin?',
    text: 'Penjemputan ini akan dibatalkan!',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Ya, Batalkan!',
    cancelButtonText: 'Kembali',
  })

  if (result.isConfirmed) {
    try {
      await axios.post(`/api/nasabah/penjemputan/${id}/cancel`)
      Swal.fire('Berhasil!', 'Penjemputan telah dibatalkan.', 'success')
      fetchTransactions()
    } catch (error) {
      Swal.fire(
        'Gagal!',
        error.response?.data?.message || 'Gagal membatalkan penjemputan.',
        'error',
      )
    }
  }
}

const formatDate = (dateString, includeTime = false) => {
  if (!dateString) return '-'
  const date = new Date(dateString)
  const options = { day: 'numeric', month: 'long', year: 'numeric' }
  if (includeTime) {
    return `${date.toLocaleDateString('id-ID', options)} pukul ${date.getHours().toString().padStart(2, '0')}.${date.getMinutes().toString().padStart(2, '0')}`
  }
  return date.toLocaleDateString('id-ID', options)
}

const getStatusLabel = (status) => {
  const s = statusFilters.find((f) => f.value === status)
  return s ? s.label : status
}

onMounted(() => {
  fetchTransactions()
})
</script>

<template>
  <DashboardLayout title="Sampah Saya">
    <div class="space-y-6">
      <!-- Main Tab Switcher -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-1 flex">
        <button
          @click="activeMainTab = 'jemput'"
          :class="
            cn(
              'flex-1 flex items-center justify-center gap-2 py-4 rounded-xl transition-all duration-300 font-bold',
              activeMainTab === 'jemput'
                ? 'bg-[#4A7043] text-white shadow-md'
                : 'text-gray-500 hover:bg-gray-50',
            )
          "
        >
          <Icon icon="material-symbols:local-shipping-outline" class="w-6 h-6" />
          <span>Jemput Sampah</span>
        </button>
        <button
          @click="activeMainTab = 'setor'"
          :class="
            cn(
              'flex-1 flex items-center justify-center gap-2 py-4 rounded-xl transition-all duration-300 font-bold',
              activeMainTab === 'setor'
                ? 'bg-[#4A7043] text-white shadow-md'
                : 'text-gray-500 hover:bg-gray-50',
            )
          "
        >
          <Icon icon="material-symbols:storefront-outline" class="w-6 h-6" />
          <span>Setor Manual</span>
        </button>
      </div>

      <!-- Filter and Content Area -->
      <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <!-- Status Filter Tabs -->
        <div class="flex border-b border-gray-50 overflow-x-auto no-scrollbar">
          <button
            v-for="filter in visibleStatusFilters"
            :key="filter.value"
            @click="activeStatusFilter = filter.value"
            :class="
              cn(
                'flex-1 min-w-[120px] flex flex-col items-center gap-1 py-4 transition-all relative',
                activeStatusFilter === filter.value
                  ? 'text-[#4A7043]'
                  : 'text-gray-400 hover:text-gray-600',
              )
            "
          >
            <span class="text-xs font-bold">{{ filter.label }}</span>
            <div
              :class="
                cn(
                  'w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-black transition-all',
                  activeStatusFilter === filter.value
                    ? 'bg-[#4A7043] text-white'
                    : 'bg-gray-100 text-gray-500',
                )
              "
            >
              {{ counts[filter.value] || 0 }}
            </div>
            <!-- Active Indicator -->
            <div
              v-if="activeStatusFilter === filter.value"
              class="absolute bottom-0 left-4 right-4 h-1 bg-[#4A7043] rounded-t-full"
            ></div>
          </button>
        </div>

        <!-- Search Bar -->
        <div class="p-6">
          <div class="relative group">
            <Icon
              icon="material-symbols:search"
              class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400 group-focus-within:text-[#4A7043] transition-colors"
            />
            <input
              v-model="searchQuery"
              type="text"
              :placeholder="
                activeMainTab === 'jemput'
                  ? 'Cari berdasarkan tracking ID atau jenis sampah...'
                  : 'Cari berdasarkan ID transaksi atau jenis sampah...'
              "
              class="w-full pl-12 pr-4 py-3 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-[#4A7043]/20 transition-all text-sm outline-none"
            />
          </div>
        </div>

        <!-- List View -->
        <div class="px-6 pb-6 space-y-4">
          <div v-if="loading" class="flex flex-col items-center justify-center py-20 gap-4">
            <div
              class="w-12 h-12 border-4 border-[#4A7043]/20 border-t-[#4A7043] rounded-full animate-spin"
            ></div>
            <p class="text-gray-500 font-medium italic">Menarik data penjemputan...</p>
          </div>

          <template v-else>
            <div
              v-if="filteredTransactions.length === 0"
              class="flex flex-col items-center justify-center py-20 text-center space-y-4"
            >
              <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center">
                <Icon icon="material-symbols:info-outline" class="w-10 h-10 text-gray-300" />
              </div>
              <div>
                <p class="text-gray-800 font-bold">Tidak ada data ditemukan</p>
                <p class="text-sm text-gray-500">
                  Coba ubah filter atau kata kunci pencarian Anda.
                </p>
              </div>
            </div>

            <div
              v-for="item in filteredTransactions"
              :key="activeMainTab === 'jemput' ? item.penjemputan_id : item.transaksi_id"
              class="group bg-white border border-gray-100 rounded-3xl p-5 hover:border-[#4A7043]/30 hover:shadow-xl hover:shadow-[#4A7043]/5 transition-all duration-500"
            >
              <div class="flex flex-col md:flex-row gap-6">
                <!-- Status Icon & Image -->
                <div class="flex gap-4">
                  <div
                    class="w-14 h-14 bg-gray-50 rounded-2xl flex items-center justify-center shrink-0"
                  >
                    <Icon
                      :icon="
                        activeMainTab === 'jemput'
                          ? 'material-symbols:local-shipping-outline'
                          : 'material-symbols:storefront-outline'
                      "
                      class="w-7 h-7 text-gray-400"
                    />
                  </div>
                  <div
                    class="w-24 h-24 rounded-2xl overflow-hidden bg-gray-100 shrink-0 border border-gray-50"
                  >
                    <img
                      v-if="activeMainTab === 'jemput' && item.foto && item.foto.length > 0"
                      :src="`${axios.defaults.baseURL}/storage/${item.foto[0]}`"
                      class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700"
                      alt="Sampah"
                    />
                    <img
                      v-else-if="activeMainTab === 'setor' && item.penimbangan?.[0]?.foto"
                      :src="`${axios.defaults.baseURL}/storage/${item.penimbangan[0].foto}`"
                      class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700"
                      alt="Sampah"
                    />
                    <div
                      v-else
                      class="w-full h-full flex items-center justify-center text-gray-300"
                    >
                      <Icon icon="material-symbols:image-outline" class="w-8 h-8" />
                    </div>
                  </div>
                </div>

                <!-- Content -->
                <div class="flex-1 space-y-3">
                  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div>
                      <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">
                        {{
                          activeMainTab === 'jemput'
                            ? `#JMP-${String(item.penjemputan_id).padStart(3, '0')}`
                            : `#TR-${String(item.transaksi_id).padStart(3, '0')}`
                        }}
                      </p>
                      <h3 class="text-lg font-black text-gray-800 line-clamp-1">
                        <template v-if="activeMainTab === 'jemput'">
                          {{
                            item.detail_penjemputan
                              ?.map((d) => d.sampah?.item_sampah?.nama)
                              .join(', ') || 'Jenis Sampah Tidak Diketahui'
                          }}
                        </template>
                        <template v-else>
                          {{
                            item.penimbangan?.map((p) => p.sampah?.item_sampah?.nama).join(', ') ||
                            'Jenis Sampah Tidak Diketahui'
                          }}
                        </template>
                      </h3>
                    </div>
                  </div>

                  <div class="flex flex-wrap gap-y-2 gap-x-6">
                    <div class="flex items-center gap-2 text-gray-500">
                      <Icon icon="material-symbols:calendar-today-outline" class="w-4 h-4" />
                      <span class="text-xs font-bold">{{
                        formatDate(activeMainTab === 'jemput' ? item.created_at : item.tanggal)
                      }}</span>
                    </div>
                    <div class="flex items-center gap-2 text-gray-500">
                      <Icon icon="material-symbols:weight-outline" class="w-4 h-4" />
                      <span class="text-xs font-bold">
                        <template v-if="activeMainTab === 'jemput'"
                          >{{ item.estimasi_berat || '-' }} kg</template
                        >
                        <template v-else
                          >{{
                            item.penimbangan?.reduce(
                              (acc, curr) => acc + parseFloat(curr.berat_timbang),
                              0,
                            )
                          }}
                          kg</template
                        >
                      </span>
                    </div>
                    <div
                      v-if="activeMainTab === 'setor'"
                      class="flex items-center gap-2 text-[#4A7043]"
                    >
                      <Icon icon="material-symbols:payments-outline" class="w-4 h-4" />
                      <span class="text-xs font-black"
                        >+ Rp
                        {{
                          item.penimbangan
                            ?.reduce(
                              (acc, curr) =>
                                acc +
                                curr.berat_timbang * (curr.sampah?.item_sampah?.harga_beli || 0),
                              0,
                            )
                            .toLocaleString('id-ID')
                        }}</span
                      >
                    </div>
                    <div class="flex items-center gap-2 text-[#4A7043]">
                      <Icon icon="material-symbols:location-on-outline" class="w-4 h-4" />
                      <span class="text-xs font-black">
                        <template v-if="activeMainTab === 'jemput'">{{
                          item.gudang?.nama || 'Lokasi tidak diset'
                        }}</template>
                        <template v-else>{{
                          item.penimbangan?.[0]?.tukang?.gudang?.nama || 'Lokasi tidak diset'
                        }}</template>
                      </span>
                    </div>
                  </div>

                  <!-- Buttons -->
                  <div class="flex gap-3 pt-2">
                    <button
                      @click="openDetail(item)"
                      class="flex-1 bg-[#4A7043] hover:bg-[#3D5C37] text-white py-3 rounded-2xl font-bold text-sm transition-all flex items-center justify-center gap-2 shadow-lg shadow-[#4A7043]/20 active:scale-[0.98]"
                    >
                      <span>Lihat Detail</span>
                      <Icon icon="material-symbols:arrow-right-alt" class="w-5 h-5" />
                    </button>
                    <button
                      v-if="item.status === 'pending'"
                      @click="cancelTransaction(item.penjemputan_id)"
                      class="px-6 border-2 border-red-100 text-red-500 hover:bg-red-50 rounded-2xl font-bold text-sm transition-all flex items-center justify-center gap-2 active:scale-[0.98]"
                    >
                      <Icon icon="material-symbols:cancel-outline" class="w-5 h-5" />
                      <span>Batalkan</span>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </template>
        </div>
      </div>
    </div>
    <!-- Detail Modal -->
    <div
      v-if="showDetailModal"
      class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6"
    >
      <!-- Backdrop -->
      <div
        class="absolute inset-0 bg-black/60 backdrop-blur-sm animate-in fade-in duration-300"
        @click="closeDetail"
      ></div>

      <!-- Modal Content -->
      <div
        class="relative bg-[#F8F9F8] w-full max-w-2xl max-h-[90vh] overflow-y-auto rounded-[2.5rem] shadow-2xl animate-in zoom-in-95 duration-300 no-scrollbar"
      >
        <!-- Header -->
        <div
          class="sticky top-0 z-10 bg-[#4A7043] px-8 py-6 flex items-center justify-between border-b border-white/10 shadow-lg"
        >
          <div class="flex items-center gap-4">
            <div
              class="w-12 h-12 bg-white/10 rounded-2xl flex items-center justify-center text-white shrink-0"
            >
              <Icon
                :icon="
                  selectedItem.penjemputan_id
                    ? 'material-symbols:local-shipping-outline'
                    : 'material-symbols:storefront-outline'
                "
                class="w-7 h-7"
              />
            </div>
            <div>
              <h2 class="text-xl font-black text-white">Detail Transaksi</h2>
              <div class="flex gap-3 mt-0.5">
                <span
                  v-if="selectedItem.penjemputan_id"
                  class="text-[10px] font-black text-white/60 uppercase tracking-widest"
                  >#JMP-{{ String(selectedItem.penjemputan_id).padStart(3, '0') }}</span
                >
                <span
                  v-if="selectedItem.transaksi_id || selectedItem.penimbangan?.[0]?.transaksi_id"
                  class="text-[10px] font-black text-white/60 uppercase tracking-widest"
                  >#TR-{{
                    String(
                      selectedItem.transaksi_id || selectedItem.penimbangan[0].transaksi_id,
                    ).padStart(3, '0')
                  }}</span
                >
              </div>
            </div>
          </div>
          <button
            @click="closeDetail"
            class="w-10 h-10 bg-white/10 text-white hover:bg-white/20 rounded-full flex items-center justify-center transition-all"
          >
            <Icon icon="material-symbols:close" class="w-6 h-6" />
          </button>
        </div>

        <div class="p-8 space-y-6">
          <!-- Status & Tab Switcher (Only for Selesai) -->
          <div v-if="selectedItem.status === 'selesai'" class="flex flex-col items-center gap-6">
            <div class="flex flex-col items-center gap-3">
              <div
                class="bg-[#4A7043] px-6 py-2.5 rounded-full flex items-center gap-2 shadow-lg shadow-green-900/20"
              >
                <Icon icon="material-symbols:check-circle" class="w-5 h-5 text-white" />
                <span class="text-xs font-black text-white uppercase tracking-widest"
                  >Selesai Diproses</span
                >
              </div>
              <div
                v-if="!selectedItem.penjemputan_id"
                class="bg-green-100 px-4 py-1.5 rounded-full flex items-center gap-2"
              >
                <Icon icon="material-symbols:check" class="w-4 h-4 text-green-700" />
                <span class="text-[10px] font-black text-green-700 uppercase tracking-widest"
                  >Penimbangan Setor Manual</span
                >
              </div>
            </div>

            <!-- Modern Compact Tabs -->
            <div
              v-if="selectedItem.penjemputan_id"
              class="bg-white p-1.5 rounded-2xl shadow-sm border border-gray-100 flex gap-2 w-full max-w-md"
            >
              <button
                @click="activeDetailTab = 'jemput'"
                :class="
                  cn(
                    'flex-1 flex items-center justify-center gap-2 py-3 rounded-xl font-black text-xs transition-all duration-300',
                    activeDetailTab === 'jemput'
                      ? 'bg-[#DCFCE7] text-[#166534]'
                      : 'text-gray-400 hover:bg-gray-50',
                  )
                "
              >
                <Icon
                  icon="material-symbols:check"
                  v-if="activeDetailTab === 'jemput'"
                  class="w-4 h-4"
                />
                Penjemputan
              </button>
              <button
                @click="activeDetailTab = 'timbang'"
                :disabled="!selectedItem.penimbangan?.length"
                :class="
                  cn(
                    'flex-1 flex items-center justify-center gap-2 py-3 rounded-xl font-black text-xs transition-all duration-300',
                    !selectedItem.penimbangan?.length ? 'opacity-50 cursor-not-allowed' : '',
                    activeDetailTab === 'timbang'
                      ? 'bg-[#DCFCE7] text-[#166534]'
                      : 'text-gray-400 hover:bg-gray-50',
                  )
                "
              >
                <Icon
                  icon="material-symbols:check"
                  v-if="activeDetailTab === 'timbang'"
                  class="w-4 h-4"
                />
                Penimbangan
              </button>
            </div>
          </div>

          <!-- Carousel (For non-Selesai statuses or Penjemputan Tab) -->
          <div
            v-if="selectedItem.status !== 'selesai' || activeDetailTab === 'jemput'"
            class="bg-white rounded-[2rem] p-6 shadow-sm border border-gray-100 space-y-4"
          >
            <div class="flex items-center gap-2 mb-2">
              <p class="text-xs font-black text-gray-400 uppercase tracking-widest">Foto Sampah</p>
            </div>

            <div class="relative aspect-video rounded-3xl overflow-hidden bg-gray-100 group">
              <img
                v-if="selectedItem.foto && selectedItem.foto.length > 0"
                :src="`${axios.defaults.baseURL}/storage/${selectedItem.foto[currentPhotoIndex]}`"
                class="w-full h-full object-cover transition-all duration-500"
              />
              <div
                v-else
                class="w-full h-full flex flex-col items-center justify-center text-gray-300 gap-2"
              >
                <Icon icon="material-symbols:image-outline" class="w-12 h-12" />
                <p class="text-xs font-bold uppercase tracking-widest">Tidak ada foto</p>
              </div>

              <template v-if="selectedItem.foto && selectedItem.foto.length > 1">
                <button
                  @click="
                    currentPhotoIndex =
                      (currentPhotoIndex - 1 + selectedItem.foto.length) % selectedItem.foto.length
                  "
                  class="absolute left-4 top-1/2 -translate-y-1/2 w-10 h-10 bg-white/20 backdrop-blur-md text-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 hover:bg-white/40 transition-all"
                >
                  <Icon icon="material-symbols:chevron-left" class="w-6 h-6" />
                </button>
                <button
                  @click="currentPhotoIndex = (currentPhotoIndex + 1) % selectedItem.foto.length"
                  class="absolute right-4 top-1/2 -translate-y-1/2 w-10 h-10 bg-white/20 backdrop-blur-md text-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 hover:bg-white/40 transition-all"
                >
                  <Icon icon="material-symbols:chevron-right" class="w-6 h-6" />
                </button>
              </template>
            </div>
            <div
              v-if="selectedItem.foto && selectedItem.foto.length > 1"
              class="flex gap-3 overflow-x-auto no-scrollbar pt-2"
            >
              <button
                v-for="(photo, idx) in selectedItem.foto"
                :key="idx"
                @click="currentPhotoIndex = idx"
                :class="
                  cn(
                    'w-16 h-16 rounded-xl overflow-hidden shrink-0 border-2 transition-all',
                    currentPhotoIndex === idx
                      ? 'border-[#4A7043] scale-105'
                      : 'border-transparent opacity-60',
                  )
                "
              >
                <img
                  :src="`${axios.defaults.baseURL}/storage/${photo}`"
                  class="w-full h-full object-cover"
                />
              </button>
            </div>
          </div>

          <!-- Tracking ID Card (Visible for all except Penimbangan tab) -->
          <div
            v-if="activeDetailTab === 'jemput' || selectedItem.status !== 'selesai'"
            class="bg-[#4A7043] px-8 py-5 flex items-center justify-between rounded-[2.5rem] shadow-sm"
          >
            <div class="flex items-center gap-4">
              <div
                class="w-10 h-10 bg-white/10 rounded-xl flex items-center justify-center text-white"
              >
                <Icon icon="material-symbols:local-shipping-outline" class="w-6 h-6" />
              </div>
              <div>
                <p class="text-[10px] font-black text-white/60 uppercase tracking-widest">
                  Tracking ID
                </p>
                <h4 class="text-lg font-black text-white">
                  #JMP-{{ String(selectedItem.penjemputan_id).padStart(3, '0') }}
                </h4>
              </div>
            </div>
            <div
              :class="
                cn(
                  'px-4 py-2 rounded-full text-[10px] font-black text-white uppercase tracking-widest',
                  selectedItem.status === 'ditolak'
                    ? 'bg-red-600'
                    : selectedItem.status === 'batal'
                      ? 'bg-orange-600'
                      : 'bg-[#D97706]',
                )
              "
            >
              {{ getStatusLabel(selectedItem.status) }}
            </div>
          </div>

          <!-- Tab Content: Penjemputan -->
          <div
            v-if="activeDetailTab === 'jemput' || selectedItem.status !== 'selesai'"
            class="space-y-6 animate-in slide-in-from-left-4 duration-500"
          >
            <!-- Details Grid -->
            <div class="space-y-6 px-4">
              <div class="flex gap-4">
                <div
                  class="w-10 h-10 bg-gray-50 rounded-xl flex items-center justify-center text-gray-400 shrink-0"
                >
                  <Icon icon="material-symbols:storefront-outline" class="w-5 h-5" />
                </div>
                <div>
                  <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">
                    Nama Gudang
                  </p>
                  <p class="font-black text-[#4A7043]">
                    {{ selectedItem.gudang?.nama || 'Surakarta' }}
                  </p>
                </div>
              </div>

              <div class="flex gap-4">
                <div
                  class="w-10 h-10 bg-gray-50 rounded-xl flex items-center justify-center text-gray-400 shrink-0"
                >
                  <Icon icon="material-symbols:recycling" class="w-5 h-5" />
                </div>
                <div>
                  <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">
                    Jenis Sampah
                  </p>
                  <p class="font-black text-gray-700">
                    {{
                      selectedItem.detail_penjemputan
                        ?.map((d) => d.sampah?.item_sampah?.nama)
                        .join(', ') || '-'
                    }}
                  </p>
                  <p class="text-[10px] font-bold text-gray-400 mt-1">
                    Estimasi: {{ selectedItem.estimasi_berat || '-' }} kg
                  </p>
                </div>
              </div>

              <div class="flex gap-4">
                <div
                  class="w-10 h-10 bg-gray-50 rounded-xl flex items-center justify-center text-gray-400 shrink-0"
                >
                  <Icon icon="material-symbols:location-on-outline" class="w-5 h-5" />
                </div>
                <div>
                  <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">
                    Alamat Penjemputan
                  </p>
                  <p class="font-bold text-gray-600 text-sm leading-relaxed">
                    {{ selectedItem.alamat || '-' }}
                  </p>
                </div>
              </div>

              <div class="flex gap-4">
                <div
                  class="w-10 h-10 bg-gray-50 rounded-xl flex items-center justify-center text-gray-400 shrink-0"
                >
                  <Icon icon="material-symbols:calendar-today-outline" class="w-5 h-5" />
                </div>
                <div>
                  <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">
                    Tanggal Request
                  </p>
                  <p class="font-bold text-gray-700 text-sm">
                    {{ formatDate(selectedItem.created_at, true) }}
                  </p>
                </div>
              </div>

              <div class="flex gap-4">
                <div
                  class="w-10 h-10 bg-gray-50 rounded-xl flex items-center justify-center text-gray-400 shrink-0"
                >
                  <Icon icon="material-symbols:notes" class="w-5 h-5" />
                </div>
                <div>
                  <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">
                    Catatan
                  </p>
                  <p class="font-bold text-gray-500 text-sm italic">
                    "{{ selectedItem.deskripsi || 'Tidak ada catatan' }}"
                  </p>
                </div>
              </div>

              <!-- Ditolak Info Box -->
              <div
                v-if="selectedItem.status === 'ditolak'"
                class="bg-red-50 rounded-3xl p-6 border border-red-100 space-y-3 animate-in zoom-in-95"
              >
                <div class="flex items-center gap-3 text-red-600">
                  <Icon icon="material-symbols:error-outline" class="w-6 h-6" />
                  <span class="font-black text-sm uppercase tracking-wider">Request Ditolak</span>
                </div>
                <div>
                  <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">
                    Alasan Penolakan:
                  </p>
                  <p class="text-sm font-bold text-red-700 leading-relaxed">
                    {{ selectedItem.ket_status || 'Alasan tidak diberikan.' }}
                  </p>
                </div>
                <div class="flex items-center gap-2 pt-2 border-t border-red-100/50">
                  <Icon icon="material-symbols:history" class="w-3.5 h-3.5 text-gray-400" />
                  <p class="text-[10px] font-bold text-gray-400">
                    Ditolak pada: {{ formatDate(selectedItem.updated_at, true) }}
                  </p>
                </div>
              </div>

              <!-- Dibatalkan Info Box -->
              <div
                v-if="selectedItem.status === 'batal'"
                class="bg-orange-50 rounded-3xl p-6 border border-orange-100 space-y-4 animate-in zoom-in-95"
              >
                <div class="flex items-center gap-3 text-orange-600">
                  <Icon icon="material-symbols:cancel-outline" class="w-6 h-6" />
                  <span class="font-black text-sm uppercase tracking-wider"
                    >Anda membatalkan request</span
                  >
                </div>
                <div class="flex items-center gap-2">
                  <Icon icon="material-symbols:history" class="w-3.5 h-3.5 text-gray-400" />
                  <p class="text-[10px] font-bold text-gray-400">
                    Dibatalkan pada: {{ formatDate(selectedItem.updated_at, true) }}
                  </p>
                </div>
                <div class="bg-white rounded-2xl p-4 border border-orange-100 shadow-sm flex gap-3">
                  <div
                    class="w-8 h-8 bg-blue-50 rounded-lg flex items-center justify-center text-blue-500 shrink-0"
                  >
                    <Icon icon="material-symbols:info-outline" class="w-5 h-5" />
                  </div>
                  <div>
                    <p class="text-[10px] font-black text-gray-800 uppercase tracking-widest mb-1">
                      Informasi:
                    </p>
                    <p class="text-[10px] font-bold text-gray-500 leading-relaxed">
                      Request ini telah dibatalkan. Anda dapat membuat request baru kapan saja
                      melalui menu Request Jemput/Setor Manual.
                    </p>
                  </div>
                </div>
              </div>

              <!-- Jadwal Penjemputan Section -->
              <div
                v-if="
                  selectedItem.jadwal &&
                  selectedItem.status !== 'ditolak' &&
                  selectedItem.status !== 'batal'
                "
                class="bg-[#EAF0F0] rounded-3xl p-5 border border-[#D1E5E5] flex items-center gap-4"
              >
                <div
                  class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center text-[#4A7043] shadow-sm"
                >
                  <Icon icon="material-symbols:calendar-month-outline" class="w-6 h-6" />
                </div>
                <div>
                  <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">
                    Jadwal Penjemputan
                  </p>
                  <h5 class="text-base font-black text-[#4A7043]">
                    {{ formatDate(selectedItem.jadwal, true) }}
                  </h5>
                </div>
              </div>

              <!-- Tukang Section -->
              <div
                v-if="
                  selectedItem.tukang &&
                  selectedItem.status !== 'ditolak' &&
                  selectedItem.status !== 'batal'
                "
                class="bg-[#F0F2EF] rounded-3xl p-5 border border-[#E2E6E1] flex items-center gap-4"
              >
                <div
                  class="w-14 h-14 rounded-2xl overflow-hidden bg-white border border-gray-100 shrink-0 shadow-sm"
                >
                  <img
                    v-if="selectedItem.tukang.foto"
                    :src="`${axios.defaults.baseURL}/storage/${selectedItem.tukang.foto}`"
                    class="w-full h-full object-cover"
                  />
                  <div v-else class="w-full h-full flex items-center justify-center text-gray-300">
                    <Icon icon="material-symbols:person-outline" class="w-8 h-8" />
                  </div>
                </div>
                <div class="flex-1 min-w-0">
                  <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">
                    Tukang
                  </p>
                  <h5 class="text-base font-black text-[#4A7043] truncate">
                    {{ selectedItem.tukang.nama }}
                  </h5>
                  <div class="flex items-center gap-2 mt-1">
                    <Icon
                      icon="material-symbols:phone-android-outline-rounded"
                      class="w-3.5 h-3.5 text-gray-400"
                    />
                    <span class="text-xs font-bold text-gray-500 tracking-tight">{{
                      selectedItem.tukang.no_telp
                    }}</span>
                  </div>
                </div>
                <a
                  :href="`https://wa.me/${selectedItem.tukang.no_telp.replace(/^0/, '62')}`"
                  target="_blank"
                  class="w-10 h-10 bg-[#25D366] text-white rounded-full flex items-center justify-center shadow-lg shadow-green-500/10 hover:scale-110 active:scale-95 transition-all"
                >
                  <Icon icon="logos:whatsapp-icon" class="w-5 h-5" />
                </a>
              </div>
            </div>
          </div>

          <!-- Tab Content: Penimbangan -->
          <div
            v-if="activeDetailTab === 'timbang' && selectedItem.penimbangan?.length"
            class="space-y-6 animate-in slide-in-from-right-4 duration-500"
          >
            <!-- Informasi Transaksi -->
            <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-gray-100">
              <h3 class="text-sm font-black text-gray-800 mb-6 flex items-center gap-2">
                <Icon icon="material-symbols:receipt-long-outline" class="w-5 h-5 text-[#4A7043]" />
                Informasi Transaksi
              </h3>
              <div class="space-y-4">
                <div class="flex justify-between items-center text-xs">
                  <p class="font-bold text-gray-400 uppercase tracking-widest">ID Transaksi</p>
                  <p class="font-black text-gray-800">
                    TR-{{ String(selectedItem.penimbangan[0].transaksi_id).padStart(3, '0') }}
                  </p>
                </div>
                <div class="flex justify-between items-center text-xs">
                  <p class="font-bold text-gray-400 uppercase tracking-widest">ID Penjemputan</p>
                  <p class="font-black text-gray-800">
                    JMP-{{ String(selectedItem.penjemputan_id).padStart(3, '0') }}
                  </p>
                </div>
                <div class="flex justify-between items-center text-xs">
                  <p class="font-bold text-gray-400 uppercase tracking-widest">Gudang</p>
                  <p class="font-black text-gray-800">
                    {{
                      selectedItem.gudang?.nama ||
                      selectedItem.penimbangan?.[0]?.tukang?.gudang?.nama ||
                      'Surakarta'
                    }}
                  </p>
                </div>
                <div class="flex justify-between items-center text-xs">
                  <p class="font-bold text-gray-400 uppercase tracking-widest">Tanggal</p>
                  <p class="font-black text-gray-800">
                    {{
                      formatDate(
                        selectedItem.penimbangan[0].created_at || selectedItem.tanggal,
                        true,
                      )
                    }}
                  </p>
                </div>
                <div class="flex justify-between items-center text-xs">
                  <p class="font-bold text-gray-400 uppercase tracking-widest">Petugas Input</p>
                  <p class="font-black text-gray-800">
                    {{
                      selectedItem.penimbangan[0].transaksi?.petugas?.nama ||
                      selectedItem.petugas?.nama ||
                      '-'
                    }}
                  </p>
                </div>
                <div class="flex justify-between items-center text-xs">
                  <p class="font-bold text-gray-400 uppercase tracking-widest">Tukang</p>
                  <p class="font-black text-gray-800">
                    {{
                      selectedItem.tukang?.nama ||
                      selectedItem.penimbangan?.[0]?.tukang?.nama ||
                      '-'
                    }}
                  </p>
                </div>
              </div>
            </div>

            <!-- Informasi Nasabah -->
            <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-gray-100">
              <h3 class="text-sm font-black text-gray-800 mb-6 flex items-center gap-2">
                <Icon icon="material-symbols:person-outline" class="w-5 h-5 text-[#4A7043]" />
                Informasi Nasabah
              </h3>
              <div class="space-y-1">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">
                  Nama Nasabah
                </p>
                <p class="font-black text-gray-800 text-base">
                  {{ selectedItem.nasabah?.nama || 'Anda' }}
                </p>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mt-2">
                  ID Nasabah
                </p>
                <p class="font-black text-gray-800">
                  NSB-{{
                    String(
                      selectedItem.nasabah_id || selectedItem.penimbangan?.[0]?.nasabah_id,
                    ).padStart(3, '0')
                  }}
                </p>
              </div>
            </div>

            <!-- Foto Sampah (Penimbangan) -->
            <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-gray-100">
              <h3 class="text-sm font-black text-gray-800 mb-6 flex items-center gap-2">
                <Icon
                  icon="material-symbols:photo-library-outline"
                  class="w-5 h-5 text-[#4A7043]"
                />
                Foto Sampah
              </h3>
              <div class="flex gap-4 overflow-x-auto no-scrollbar pb-2">
                <div
                  v-for="(p, i) in selectedItem.penimbangan"
                  :key="i"
                  class="w-32 h-32 rounded-2xl overflow-hidden bg-gray-100 shrink-0 border border-gray-50"
                >
                  <img
                    v-if="p.foto"
                    :src="`${axios.defaults.baseURL}/storage/${p.foto}`"
                    class="w-full h-full object-cover"
                  />
                  <div v-else class="w-full h-full flex items-center justify-center text-gray-300">
                    <Icon icon="material-symbols:image-outline" class="w-10 h-10" />
                  </div>
                </div>
              </div>
            </div>

            <!-- Detail Sampah -->
            <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-gray-100">
              <h3 class="text-sm font-black text-gray-800 mb-6">Detail Sampah</h3>
              <div class="space-y-6">
                <div
                  v-for="(p, i) in selectedItem.penimbangan"
                  :key="i"
                  class="flex justify-between items-start"
                >
                  <div>
                    <p class="font-black text-gray-800 text-sm">
                      {{ p.sampah?.item_sampah?.nama }}
                    </p>
                    <p class="text-[10px] font-bold text-gray-400">
                      {{ p.berat_timbang }} kg × Rp
                      {{ (p.sampah?.item_sampah?.harga_beli || 0).toLocaleString('id-ID') }}
                    </p>
                  </div>
                  <p class="font-black text-gray-800 text-sm">
                    Rp
                    {{
                      (p.berat_timbang * (p.sampah?.item_sampah?.harga_beli || 0)).toLocaleString(
                        'id-ID',
                      )
                    }}
                  </p>
                </div>
              </div>
            </div>

            <!-- Total Berat & Nilai -->
            <div class="bg-stone-50 rounded-[2rem] p-6 space-y-4">
              <div class="flex justify-between items-center">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Total Berat</p>
                <p class="text-sm font-black text-gray-800">
                  {{
                    selectedItem.penimbangan?.reduce(
                      (acc, curr) => acc + parseFloat(curr.berat_timbang),
                      0,
                    )
                  }}
                  kg
                </p>
              </div>
              <div class="flex justify-between items-center">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Total Nilai</p>
                <p class="text-lg font-black text-[#4A7043]">
                  Rp
                  {{
                    selectedItem.penimbangan
                      ?.reduce(
                        (acc, curr) =>
                          acc + curr.berat_timbang * (curr.sampah?.item_sampah?.harga_beli || 0),
                        0,
                      )
                      .toLocaleString('id-ID')
                  }}
                </p>
              </div>
            </div>

            <!-- Informasi Saldo -->
            <div class="bg-[#F0FAF4] rounded-[2rem] p-6 border border-[#DCF2E7] space-y-4">
              <h3 class="text-sm font-black text-[#166534] mb-2">Informasi Saldo</h3>
              <div class="flex justify-between items-center text-xs">
                <p class="font-bold text-gray-500">Saldo Sebelum</p>
                <p class="font-black text-gray-700">
                  Rp {{ (selectedItem.nasabah?.saldo || 0).toLocaleString('id-ID') }}
                </p>
              </div>
              <div class="flex justify-between items-center text-xs">
                <p class="font-bold text-[#166534]">Nilai Tambahan</p>
                <p class="font-black text-[#166534]">
                  + Rp
                  {{
                    selectedItem.penimbangan
                      ?.reduce(
                        (acc, curr) =>
                          acc + curr.berat_timbang * (curr.sampah?.item_sampah?.harga_beli || 0),
                        0,
                      )
                      .toLocaleString('id-ID')
                  }}
                </p>
              </div>
              <div class="pt-4 border-t border-[#DCF2E7] flex justify-between items-center">
                <p class="font-black text-[#166534] text-sm">Saldo Sesudah</p>
                <p class="font-black text-[#166534] text-lg">
                  Rp
                  {{
                    (
                      parseFloat(selectedItem.nasabah?.saldo || 0) +
                      selectedItem.penimbangan?.reduce(
                        (acc, curr) =>
                          acc + curr.berat_timbang * (curr.sampah?.item_sampah?.harga_beli || 0),
                        0,
                      )
                    ).toLocaleString('id-ID')
                  }}
                </p>
              </div>
            </div>
          </div>

          <!-- Global Actions -->
          <div class="pt-4">
            <button
              @click="closeDetail"
              class="w-full py-4 rounded-2xl bg-[#4A7043] hover:bg-[#3D5C37] text-white font-black text-sm transition-all shadow-lg active:scale-[0.98]"
            >
              Tutup
            </button>
          </div>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
  display: none;
}
.no-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}
.animate-spin {
  animation: spin 1s linear infinite;
}
</style>
