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
      } else if (['pending', 'menunggu_persetujuan', 'jadwal_ditolak'].includes(t.status)) {
        result.pending++
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
    } else if (activeStatusFilter.value === 'pending') {
      matchesStatus = ['pending', 'menunggu_persetujuan', 'jadwal_ditolak'].includes(t.status)
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

const acceptSchedule = async (id) => {
  const result = await Swal.fire({
    title: 'Setujui Jadwal?',
    text: 'Anda menyetujui jadwal penjemputan ini.',
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#4A7043',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Ya, Setujui!',
    cancelButtonText: 'Batal',
  })

  if (result.isConfirmed) {
    try {
      await axios.put(`/api/nasabah/penjemputan/${id}/setuju`)
      Swal.fire('Berhasil!', 'Jadwal telah disetujui. Petugas akan segera memproses.', 'success')
      fetchTransactions()
      closeDetail()
    } catch (error) {
      Swal.fire('Gagal!', error.response?.data?.message || 'Gagal menyetujui jadwal.', 'error')
    }
  }
}

const rejectSchedule = async (id) => {
  const { value: reason } = await Swal.fire({
    title: 'Tolak Jadwal',
    input: 'textarea',
    inputLabel: 'Alasan penolakan',
    inputPlaceholder: 'Tulis alasan Anda menolak jadwal ini...',
    inputAttributes: {
      'aria-label': 'Tulis alasan Anda menolak jadwal ini'
    },
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Tolak Jadwal',
    cancelButtonText: 'Batal',
    inputValidator: (value) => {
      if (!value) {
        return 'Anda wajib mengisi alasan penolakan!'
      }
    }
  })

  if (reason) {
    try {
      await axios.put(`/api/nasabah/penjemputan/${id}/tolak`, {
        alasan_tolak: reason
      })
      Swal.fire('Ditolak!', 'Jadwal penjemputan telah ditolak.', 'success')
      fetchTransactions()
      closeDetail()
    } catch (error) {
      Swal.fire('Gagal!', error.response?.data?.message || 'Gagal menolak jadwal.', 'error')
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

                  <div v-if="item.status === 'menunggu_persetujuan'" class="bg-indigo-50 rounded-xl p-3 border border-indigo-100 flex items-start gap-2">
                    <Icon icon="material-symbols:info" class="w-5 h-5 text-indigo-500 shrink-0" />
                    <p class="text-xs font-bold text-indigo-700">Silahkan setujui jadwal penjemputan Anda untuk diproses lebih lanjut.</p>
                  </div>
                  
                  <div v-if="item.status === 'jadwal_ditolak'" class="bg-red-50 rounded-xl p-3 border border-red-100 flex items-start gap-2">
                    <Icon icon="material-symbols:warning" class="w-5 h-5 text-red-500 shrink-0" />
                    <div>
                       <p class="text-xs font-bold text-red-700">Jadwal Anda tolak. Menunggu petugas mengatur ulang jadwal.</p>
                       <p class="text-[10px] font-medium text-red-600 mt-0.5">Alasan: {{ item.ket_status }}</p>
                    </div>
                  </div>

                  <!-- Buttons -->
                  <div class="flex gap-3 pt-2">
                    <button
                      @click="openDetail(item)"
                      :class="cn(
                        'flex-1 py-3 rounded-2xl font-bold text-sm transition-all flex items-center justify-center gap-2 shadow-lg active:scale-[0.98]',
                        item.status === 'menunggu_persetujuan' ? 'bg-indigo-600 hover:bg-indigo-700 text-white shadow-indigo-600/20' : 'bg-[#4A7043] hover:bg-[#3D5C37] text-white shadow-[#4A7043]/20'
                      )"
                    >
                      <span>{{ item.status === 'menunggu_persetujuan' ? 'Lihat Konfirmasi Jadwal' : 'Lihat Detail' }}</span>
                      <Icon icon="material-symbols:arrow-right-alt" class="w-5 h-5" />
                    </button>
                    <button
                      v-if="['pending', 'menunggu_persetujuan', 'jadwal_ditolak'].includes(item.status)"
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

      <!-- Modal Content Wrapper (Mobile App Mockup styling) -->
      <div
        class="relative bg-[#F7F7F5] w-full max-w-md max-h-[90vh] overflow-y-auto rounded-[2rem] shadow-2xl animate-in zoom-in-95 duration-300 no-scrollbar flex flex-col"
      >
        
        <!-- Header (Selesai status has green header, others have white header) -->
        <div
          :class="cn(
            'px-6 py-4 flex items-center justify-between sticky top-0 z-10 shadow-sm border-b shrink-0',
            selectedItem.status === 'selesai' 
              ? 'bg-[#4A7043] border-white/10 text-white' 
              : 'bg-white border-stone-100 text-stone-800'
          )"
        >
          <div class="flex items-center gap-3">
            <h2 class="text-base font-bold">
              {{ selectedItem.status === 'selesai' ? 'Detail Transaksi' : 'Detail Request' }}
            </h2>
            <span v-if="selectedItem.status === 'selesai'" class="text-xs text-white/70">
              #{{ selectedItem.penjemputan_id ? 'JMP' : 'TR' }}-{{ String(selectedItem.penjemputan_id || selectedItem.transaksi_id).padStart(3, '0') }}
            </span>
          </div>
          
          <div class="flex items-center gap-2">
            <!-- Selesai check icon -->
            <div v-if="selectedItem.status === 'selesai'" class="w-7 h-7 bg-white/10 rounded-full flex items-center justify-center">
              <Icon icon="material-symbols:check" class="w-4 h-4 text-white" />
            </div>
            <!-- Close button -->
            <button
              @click="closeDetail"
              :class="cn(
                'w-8 h-8 rounded-full flex items-center justify-center transition-all',
                selectedItem.status === 'selesai' 
                  ? 'bg-white/10 text-white hover:bg-white/20' 
                  : 'bg-stone-100 text-stone-600 hover:bg-stone-200'
              )"
            >
              <Icon icon="material-symbols:close" class="w-4 h-4" />
            </button>
          </div>
        </div>

        <!-- Scrollable Modal Body -->
        <div class="p-4 space-y-4 flex-1 overflow-y-auto no-scrollbar">

          <!-- TAB SWITCHER (For Completed pick-ups) -->
          <div
            v-if="selectedItem.status === 'selesai' && selectedItem.penjemputan_id"
            class="bg-white p-1 rounded-xl border border-stone-200/60 flex w-full"
          >
            <button
              @click="activeDetailTab = 'jemput'"
              :class="cn(
                'flex-1 text-center py-2.5 rounded-lg text-xs font-bold transition-all duration-300 border-b-2',
                activeDetailTab === 'jemput'
                  ? 'border-[#4A7043] text-[#4A7043] font-extrabold'
                  : 'border-transparent text-stone-400 hover:text-stone-600'
              )"
            >
              Penjemputan
            </button>
            <button
              @click="activeDetailTab = 'timbang'"
              :disabled="!selectedItem.penimbangan?.length"
              :class="cn(
                'flex-1 text-center py-2.5 rounded-lg text-xs font-bold transition-all duration-300 border-b-2',
                !selectedItem.penimbangan?.length ? 'opacity-50 cursor-not-allowed' : '',
                activeDetailTab === 'timbang'
                  ? 'border-[#4A7043] text-[#4A7043] font-extrabold'
                  : 'border-transparent text-stone-400 hover:text-stone-600'
              )"
            >
              Penimbangan
            </button>
          </div>

          <!-- SECTION 1: FOTO SAMPAH CARD (For non-Selesai, or Penjemputan Tab) -->
          <div
            v-if="selectedItem.status !== 'selesai' || activeDetailTab === 'jemput'"
            class="bg-white rounded-2xl p-4 shadow-sm border border-stone-100 space-y-3"
          >
            <div class="flex items-center justify-between">
              <span class="text-xs font-bold text-stone-800">Foto Sampah</span>
            </div>

            <!-- Image Carousel Container -->
            <div class="relative aspect-video rounded-xl overflow-hidden bg-stone-100 group">
              <img
                v-if="selectedItem.foto && selectedItem.foto.length > 0"
                :src="`${axios.defaults.baseURL}/storage/${selectedItem.foto[currentPhotoIndex]}`"
                class="w-full h-full object-cover transition-all duration-500"
              />
              <div
                v-else
                class="w-full h-full flex flex-col items-center justify-center text-stone-300 gap-1.5"
              >
                <Icon icon="material-symbols:image-outline" class="w-10 h-10" />
                <p class="text-[10px] font-bold uppercase tracking-widest">Tidak ada foto</p>
              </div>

              <!-- Indicators -->
              <div 
                v-if="selectedItem.foto && selectedItem.foto.length > 0"
                class="absolute bottom-3 left-1/2 -translate-x-1/2 bg-black/60 text-white text-[10px] px-2.5 py-0.5 rounded-full font-bold"
              >
                {{ currentPhotoIndex + 1 }} / {{ selectedItem.foto.length }}
              </div>

              <!-- Left/Right Controls -->
              <template v-if="selectedItem.foto && selectedItem.foto.length > 1">
                <button
                  @click="
                    currentPhotoIndex =
                      (currentPhotoIndex - 1 + selectedItem.foto.length) % selectedItem.foto.length
                  "
                  class="absolute left-2 top-1/2 -translate-y-1/2 w-8 h-8 bg-black/30 hover:bg-black/50 text-white rounded-full flex items-center justify-center transition-all"
                >
                  <Icon icon="material-symbols:chevron-left" class="w-5 h-5" />
                </button>
                <button
                  @click="currentPhotoIndex = (currentPhotoIndex + 1) % selectedItem.foto.length"
                  class="absolute right-2 top-1/2 -translate-y-1/2 w-8 h-8 bg-black/30 hover:bg-black/50 text-white rounded-full flex items-center justify-center transition-all"
                >
                  <Icon icon="material-symbols:chevron-right" class="w-5 h-5" />
                </button>
              </template>
            </div>

            <!-- Image Thumbnails Grid -->
            <div
              v-if="selectedItem.foto && selectedItem.foto.length > 1"
              class="flex gap-2 overflow-x-auto no-scrollbar pt-1"
            >
              <button
                v-for="(photo, idx) in selectedItem.foto"
                :key="idx"
                @click="currentPhotoIndex = idx"
                :class="cn(
                  'w-12 h-12 rounded-lg overflow-hidden shrink-0 border-2 transition-all',
                  currentPhotoIndex === idx ? 'border-[#4A7043] scale-105' : 'border-transparent opacity-60'
                )"
              >
                <img
                  :src="`${axios.defaults.baseURL}/storage/${photo}`"
                  class="w-full h-full object-cover"
                />
              </button>
            </div>
          </div>

          <!-- SECTION 2: TRACKING ID BANNER (For non-Selesai, or Penjemputan Tab) -->
          <div
            v-if="activeDetailTab === 'jemput' || selectedItem.status !== 'selesai'"
            class="bg-[#4A7043]/90 text-white px-5 py-4 flex items-center justify-between rounded-2xl shadow-sm"
          >
            <div class="flex items-center gap-3">
              <div class="w-9 h-9 bg-white/20 rounded-xl flex items-center justify-center text-white shrink-0">
                <Icon icon="material-symbols:local-shipping-outline" class="w-5 h-5" />
              </div>
              <div>
                <p class="text-[9px] text-white/70 uppercase tracking-widest">Tracking ID</p>
                <h4 class="text-base font-bold">
                  #JMP-{{ String(selectedItem.penjemputan_id).padStart(3, '0') }}
                </h4>
              </div>
            </div>
            <div
              :class="cn(
                'px-3 py-1.5 rounded-full text-[9px] font-bold text-white uppercase tracking-wider shadow-sm',
                selectedItem.status === 'tolak'
                  ? 'bg-red-600'
                  : selectedItem.status === 'batal'
                    ? 'bg-[#EA580C]'
                    : selectedItem.status === 'menunggu_persetujuan'
                      ? 'bg-indigo-600'
                      : selectedItem.status === 'proses'
                        ? 'bg-[#C27E3A]'
                        : 'bg-emerald-600'
              )"
            >
              {{ selectedItem.status === 'batal' ? 'Dibatalkan' : selectedItem.status === 'tolak' ? 'Ditolak' : selectedItem.status === 'proses' ? 'Diproses' : selectedItem.status === 'pending' ? 'Menunggu' : getStatusLabel(selectedItem.status) }}
            </div>
          </div>

          <!-- SECTION 3: DETAILED LABELS AND INFORMATION (When in Pickup tab or non-Selesai) -->
          <div
            v-if="activeDetailTab === 'jemput' || selectedItem.status !== 'selesai'"
            class="bg-white rounded-2xl p-5 shadow-sm border border-stone-100 space-y-4"
          >
            <!-- Gudang -->
            <div class="flex items-start gap-3">
              <div class="w-8 h-8 rounded-xl bg-stone-100 text-stone-500 flex items-center justify-center shrink-0">
                <Icon icon="material-symbols:domain-outline" class="w-4.5 h-4.5 text-stone-400" />
              </div>
              <div class="space-y-0.5">
                <p class="text-[10px] text-stone-400 font-semibold uppercase tracking-wider">Nama Gudang</p>
                <p class="font-bold text-[#4A7043] text-sm">
                  {{ selectedItem.gudang?.nama || 'Gudang Pusat Surakarta' }}
                </p>
              </div>
            </div>

            <!-- Jenis Sampah -->
            <div class="flex items-start gap-3">
              <div class="w-8 h-8 rounded-xl bg-stone-100 text-stone-500 flex items-center justify-center shrink-0">
                <Icon icon="material-symbols:recycling" class="w-4.5 h-4.5 text-stone-400" />
              </div>
              <div class="space-y-0.5">
                <p class="text-[10px] text-stone-400 font-semibold uppercase tracking-wider">Jenis Sampah</p>
                <p class="font-bold text-stone-700 text-sm">
                  {{ selectedItem.detail_penjemputan?.map((d) => d.sampah?.item_sampah?.nama).join(', ') || 'Botol Plastik, Kaleng' }}
                </p>
                <p class="text-[10px] font-medium text-stone-400">
                  Estimasi: ~{{ selectedItem.estimasi_berat || '10' }} kg
                </p>
              </div>
            </div>

            <!-- Alamat -->
            <div class="flex items-start gap-3">
              <div class="w-8 h-8 rounded-xl bg-stone-100 text-stone-500 flex items-center justify-center shrink-0">
                <Icon icon="material-symbols:location-on-outline" class="w-4.5 h-4.5 text-stone-400" />
              </div>
              <div class="space-y-0.5">
                <p class="text-[10px] text-stone-400 font-semibold uppercase tracking-wider">Alamat Penjemputan</p>
                <p class="font-bold text-stone-600 text-xs leading-relaxed">
                  {{ selectedItem.alamat || 'Jl. Merdeka No. 123, Jakarta Selatan' }}
                </p>
              </div>
            </div>

            <!-- Tanggal Request -->
            <div class="flex items-start gap-3">
              <div class="w-8 h-8 rounded-xl bg-stone-100 text-stone-500 flex items-center justify-center shrink-0">
                <Icon icon="material-symbols:calendar-today-outline" class="w-4.5 h-4.5 text-stone-400" />
              </div>
              <div class="space-y-0.5">
                <p class="text-[10px] text-stone-400 font-semibold uppercase tracking-wider">Tanggal Request</p>
                <p class="font-bold text-stone-700 text-xs">
                  {{ formatDate(selectedItem.created_at, true) }}
                </p>
              </div>
            </div>

            <!-- Jadwal Penjemputan Banner (if scheduled) -->
            <div 
              v-if="selectedItem.jadwal && selectedItem.status !== 'ditolak' && selectedItem.status !== 'batal'"
              class="flex items-start gap-3 bg-[#E8F0E6] border border-[#D5E5D1] rounded-xl p-3"
            >
              <div class="w-8 h-8 rounded-lg bg-[#4A7043] text-white flex items-center justify-center shrink-0">
                <Icon icon="material-symbols:calendar-month-outline" class="w-4.5 h-4.5" />
              </div>
              <div class="space-y-0.5">
                <p class="text-[9px] text-[#4A7043]/80 font-bold uppercase tracking-wider">Jadwal Penjemputan</p>
                <p class="font-extrabold text-[#4A7043] text-xs">
                  {{ formatDate(selectedItem.jadwal, true) }}
                </p>
              </div>
            </div>

            <!-- Tukang / Driver Card -->
            <div 
              v-if="selectedItem.tukang && selectedItem.status !== 'ditolak' && selectedItem.status !== 'batal'"
              class="bg-stone-50 rounded-xl p-3 border border-stone-200/50 flex items-center gap-3"
            >
              <div class="w-10 h-10 rounded-full overflow-hidden bg-white border border-stone-100 shrink-0 shadow-sm">
                <img
                  v-if="selectedItem.tukang.foto"
                  :src="`${axios.defaults.baseURL}/storage/${selectedItem.tukang.foto}`"
                  class="w-full h-full object-cover"
                />
                <div v-else class="w-full h-full flex items-center justify-center text-stone-300">
                  <Icon icon="material-symbols:person-outline" class="w-6 h-6" />
                </div>
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-[9px] text-stone-400 font-bold uppercase tracking-wider">Tukang</p>
                <p class="font-bold text-[#4A7043] text-xs truncate">{{ selectedItem.tukang.nama }}</p>
                <p class="text-[10px] text-stone-500">{{ selectedItem.tukang.no_telp }}</p>
              </div>
              <a
                :href="`https://wa.me/${selectedItem.tukang.no_telp.replace(/^0/, '62')}`"
                target="_blank"
                class="w-8 h-8 bg-[#25D366] text-white rounded-full flex items-center justify-center hover:scale-105 active:scale-95 transition-all shadow-sm"
              >
                <Icon icon="logos:whatsapp-icon" class="w-4 h-4" />
              </a>
            </div>

            <!-- Catatan -->
            <div class="flex items-start gap-3">
              <div class="w-8 h-8 rounded-xl bg-stone-100 text-stone-500 flex items-center justify-center shrink-0">
                <Icon icon="material-symbols:notes" class="w-4.5 h-4.5 text-stone-400" />
              </div>
              <div class="space-y-0.5 flex-1">
                <p class="text-[10px] text-stone-400 font-semibold uppercase tracking-wider">Catatan</p>
                <p class="font-medium text-stone-500 text-xs italic">
                  "{{ selectedItem.deskripsi || 'Tidak ada catatan' }}"
                </p>
              </div>
            </div>

            <!-- Dibatalkan Info Box -->
            <div
              v-if="selectedItem.status === 'batal'"
              class="bg-[#FFF8F6] rounded-xl p-4 border border-orange-200/60 space-y-3"
            >
              <div class="flex items-center gap-2 text-[#EA580C]">
                <Icon icon="material-symbols:error-outline" class="w-5 h-5" />
                <span class="font-bold text-xs uppercase tracking-wider">Request Dibatalkan</span>
              </div>
              <div class="space-y-2 text-xs">
                <div>
                  <p class="text-[9px] text-stone-400 font-bold uppercase tracking-wider">Alasan Pembatalan:</p>
                  <p class="font-bold text-[#EA580C] leading-snug">
                    {{ selectedItem.ket_status || 'Dibatalkan oleh nasabah - Berubah pikiran, mau dibuang sendiri' }}
                  </p>
                </div>
                <p class="text-[10px] text-stone-400">
                  Dibatalkan pada: {{ formatDate(selectedItem.updated_at, true) }}
                </p>
                <!-- Inner info card -->
                <div class="bg-white rounded-lg p-2.5 border border-orange-100 shadow-sm flex gap-2">
                  <Icon icon="material-symbols:info-outline" class="w-4 h-4 text-blue-500 shrink-0 mt-0.5" />
                  <p class="text-[9px] text-stone-500 leading-normal">
                    <strong>Informasi:</strong> Request ini telah dibatalkan. Anda dapat membuat request baru kapan saja melalui menu Request Jemput atau Setor Manual.
                  </p>
                </div>
              </div>
            </div>

            <!-- Ditolak Info Box -->
            <div
              v-if="selectedItem.status === 'tolak'"
              class="bg-[#FFF5F5] rounded-xl p-4 border border-red-200/60 space-y-3"
            >
              <div class="flex items-center gap-2 text-red-600">
                <Icon icon="material-symbols:cancel-outline" class="w-5 h-5" />
                <span class="font-bold text-xs uppercase tracking-wider">Request Ditolak</span>
              </div>
              <div class="space-y-2 text-xs">
                <div>
                  <p class="text-[9px] text-stone-400 font-bold uppercase tracking-wider">Alasan Penolakan:</p>
                  <p class="font-bold text-red-600 leading-snug">
                    {{ selectedItem.ket_status || 'Sampah organik tidak dapat diterima. Hanya menerima sampah anorganik (plastik, kertas, kardus, kaleng, dll)' }}
                  </p>
                </div>
                <p class="text-[10px] text-stone-400">
                  Ditolak pada: {{ formatDate(selectedItem.updated_at, true) }}
                </p>
                <!-- Inner saran card -->
                <div class="bg-white rounded-lg p-2.5 border border-red-100 shadow-sm flex gap-2">
                  <Icon icon="material-symbols:lightbulb-outline" class="w-4 h-4 text-amber-500 shrink-0 mt-0.5" />
                  <p class="text-[9px] text-stone-500 leading-normal">
                    <strong>Saran:</strong> Silakan buat request baru dengan memperhatikan alasan penolakan di atas. Pastikan sampah sudah sesuai ketentuan yang berlaku.
                  </p>
                </div>
              </div>
            </div>

          </div>

          <!-- SECTION 4: TAB CONTENT - PENIMBANGAN (Only for completed Selesai status) -->
          <div
            v-if="selectedItem.status === 'selesai' && activeDetailTab === 'timbang'"
            class="space-y-4 animate-in slide-in-from-right-4 duration-500"
          >
            <!-- Informasi Transaksi -->
            <div class="bg-white rounded-2xl p-4 shadow-sm border border-stone-100 space-y-3">
              <h3 class="text-xs font-bold text-stone-800 flex items-center gap-2 border-b border-stone-100 pb-2">
                <Icon icon="material-symbols:receipt-long-outline" class="w-4 h-4 text-[#4A7043]" />
                Informasi Transaksi
              </h3>
              <div class="space-y-2 text-xs">
                <div class="flex justify-between items-center">
                  <p class="text-stone-400 uppercase tracking-wider font-semibold text-[9px]">ID Transaksi</p>
                  <p class="font-bold text-stone-800">
                    JMP-{{ String(selectedItem.penjemputan_id).padStart(3, '0') }}
                  </p>
                </div>
                <div class="flex justify-between items-center">
                  <p class="text-stone-400 uppercase tracking-wider font-semibold text-[9px]">ID Penjemputan</p>
                  <p class="font-bold text-stone-800">
                    JMP-{{ String(selectedItem.penjemputan_id).padStart(3, '0') }}
                  </p>
                </div>
                <div class="flex justify-between items-center">
                  <p class="text-stone-400 uppercase tracking-wider font-semibold text-[9px]">Gudang</p>
                  <p class="font-bold text-stone-800">
                    {{ selectedItem.gudang?.nama || 'Gudang Pusat Surabaya' }}
                  </p>
                </div>
                <div class="flex justify-between items-center">
                  <p class="text-stone-400 uppercase tracking-wider font-semibold text-[9px]">Tanggal</p>
                  <p class="font-bold text-stone-800">
                    {{ formatDate(selectedItem.updated_at, false) }}
                  </p>
                </div>
                <div class="flex justify-between items-center">
                  <p class="text-stone-400 uppercase tracking-wider font-semibold text-[9px]">Petugas Input</p>
                  <p class="font-bold text-stone-800">
                    {{ selectedItem.petugas?.nama || 'Budi Santoso' }}
                  </p>
                </div>
                <div class="flex justify-between items-center">
                  <p class="text-stone-400 uppercase tracking-wider font-semibold text-[9px]">Tukang</p>
                  <p class="font-bold text-stone-800">
                    {{ selectedItem.tukang?.nama || 'Tatan Sukarman' }}
                  </p>
                </div>
              </div>
            </div>

            <!-- Informasi Nasabah -->
            <div class="bg-white rounded-2xl p-4 shadow-sm border border-stone-100 space-y-2">
              <h3 class="text-xs font-bold text-stone-800 flex items-center gap-2 border-b border-stone-100 pb-2">
                <Icon icon="material-symbols:person-outline" class="w-4 h-4 text-[#4A7043]" />
                Informasi Nasabah
              </h3>
              <div class="space-y-1.5 text-xs">
                <div class="flex justify-between items-center">
                  <p class="text-stone-400 uppercase tracking-wider font-semibold text-[9px]">Nama Nasabah</p>
                  <p class="font-bold text-stone-800">{{ selectedItem.nasabah?.nama || 'Dewi Lestari' }}</p>
                </div>
                <div class="flex justify-between items-center">
                  <p class="text-stone-400 uppercase tracking-wider font-semibold text-[9px]">ID Nasabah</p>
                  <p class="font-bold text-stone-800">
                    NSB-{{ String(selectedItem.nasabah_id || 4).padStart(3, '0') }}
                  </p>
                </div>
              </div>
            </div>

            <!-- Foto Sampah -->
            <div class="bg-white rounded-2xl p-4 shadow-sm border border-stone-100 space-y-2">
              <p class="text-xs font-bold text-stone-800">Foto Sampah</p>
              <div class="relative aspect-video rounded-xl overflow-hidden bg-stone-50 border border-stone-100">
                <img
                  v-if="selectedItem.foto && selectedItem.foto.length > 0"
                  :src="`${axios.defaults.baseURL}/storage/${selectedItem.foto[0]}`"
                  class="w-full h-full object-cover"
                />
                <div v-else class="w-full h-full flex items-center justify-center text-stone-300">
                  <Icon icon="material-symbols:image-outline" class="w-10 h-10" />
                </div>
              </div>
              <p class="text-[10px] text-center text-stone-400">Klik foto untuk memperbesar</p>
            </div>

            <!-- Detail Sampah Weighing Breakdown -->
            <div class="bg-white rounded-2xl p-4 shadow-sm border border-stone-100 space-y-3">
              <p class="text-xs font-bold text-stone-800 border-b border-stone-100 pb-2">Detail Sampah</p>
              <div class="space-y-3">
                <div
                  v-for="(p, i) in selectedItem.penimbangan"
                  :key="i"
                  class="flex justify-between items-start text-xs"
                >
                  <div>
                    <p class="font-bold text-stone-800">
                      {{ p.sampah?.item_sampah?.nama }}
                    </p>
                    <p class="text-[10px] text-stone-400 mt-0.5">
                      {{ p.berat_timbang }} kg × Rp {{ (p.sampah?.item_sampah?.harga_beli || 0).toLocaleString('id-ID') }}
                    </p>
                  </div>
                  <p class="font-bold text-stone-800">
                    Rp {{ (p.berat_timbang * (p.sampah?.item_sampah?.harga_beli || 0)).toLocaleString('id-ID') }}
                  </p>
                </div>

                <!-- Default Mock elements to represent screens when empty -->
                <template v-if="!selectedItem.penimbangan || selectedItem.penimbangan.length === 0">
                  <div class="flex justify-between items-start text-xs">
                    <div>
                      <p class="font-bold text-stone-800">Botol Plastik</p>
                      <p class="text-[10px] text-stone-400 mt-0.5">5 kg × Rp 3.000</p>
                    </div>
                    <p class="font-bold text-stone-800">Rp 15.000</p>
                  </div>
                  <div class="flex justify-between items-start text-xs">
                    <div>
                      <p class="font-bold text-stone-800">Kardus</p>
                      <p class="text-[10px] text-stone-400 mt-0.5">3 kg × Rp 2.500</p>
                    </div>
                    <p class="font-bold text-stone-800">Rp 7.500</p>
                  </div>
                  <div class="flex justify-between items-start text-xs">
                    <div>
                      <p class="font-bold text-stone-800">Kertas</p>
                      <p class="text-[10px] text-stone-400 mt-0.5">2 kg × Rp 2.000</p>
                    </div>
                    <p class="font-bold text-stone-800">Rp 4.000</p>
                  </div>
                </template>
              </div>

              <div class="pt-3 border-t border-stone-100 space-y-2 text-xs">
                <div class="flex justify-between items-center text-stone-500">
                  <span class="font-semibold">Total Berat</span>
                  <span class="font-bold text-stone-800">
                    {{ selectedItem.penimbangan?.reduce((acc, curr) => acc + parseFloat(curr.berat_timbang), 0) || 10 }} kg
                  </span>
                </div>
                <div class="flex justify-between items-center text-stone-500">
                  <span class="font-bold">Total Nilai</span>
                  <span class="font-bold text-[#4A7043] text-sm">
                    Rp {{ (selectedItem.penimbangan?.reduce((acc, curr) => acc + curr.berat_timbang * (curr.sampah?.item_sampah?.harga_beli || 0), 0) || 26500).toLocaleString('id-ID') }}
                  </span>
                </div>
              </div>
            </div>

            <!-- Informasi Saldo -->
            <div class="bg-[#F0FAF4] rounded-2xl p-4 border border-[#DCF2E7] space-y-2 text-xs">
              <h3 class="font-bold text-[#166534] border-b border-[#DCF2E7] pb-1.5">Informasi Saldo</h3>
              <div class="flex justify-between items-center text-stone-600">
                <span>Saldo Sebelumnya</span>
                <span class="font-semibold">Rp {{ (selectedItem.nasabah?.saldo || 320000).toLocaleString('id-ID') }}</span>
              </div>
              <div class="flex justify-between items-center text-[#166534]">
                <span>Nilai Transaksi</span>
                <span class="font-bold">+ Rp {{ (selectedItem.penimbangan?.reduce((acc, curr) => acc + curr.berat_timbang * (curr.sampah?.item_sampah?.harga_beli || 0), 0) || 26500).toLocaleString('id-ID') }}</span>
              </div>
              <div class="pt-2.5 border-t border-[#DCF2E7] flex justify-between items-center font-bold text-[#166534]">
                <span>Saldo Sesudah</span>
                <span class="text-sm">Rp {{ (parseFloat(selectedItem.nasabah?.saldo || 320000) + (selectedItem.penimbangan?.reduce((acc, curr) => acc + curr.berat_timbang * (curr.sampah?.item_sampah?.harga_beli || 0), 0) || 26500)).toLocaleString('id-ID') }}</span>
              </div>
            </div>
          </div>

        </div>

        <!-- Sticky Footer Action Button (Tutup) -->
        <div class="p-4 bg-white border-t border-stone-100 shrink-0">
          <template v-if="selectedItem.status === 'menunggu_persetujuan'">
            <div class="flex gap-2">
              <button
                @click="rejectSchedule(selectedItem.penjemputan_id)"
                class="flex-1 py-3 rounded-xl bg-red-50 hover:bg-red-100 text-red-600 font-bold text-xs transition-all active:scale-[0.98]"
              >
                Tolak Jadwal
              </button>
              <button
                @click="acceptSchedule(selectedItem.penjemputan_id)"
                class="flex-[2] py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs transition-all shadow-md active:scale-[0.98]"
              >
                Setujui Jadwal
              </button>
            </div>
          </template>
          <template v-else>
            <button
              @click="closeDetail"
              class="w-full py-3 bg-[#4A7043] hover:bg-[#3D5C37] text-white rounded-xl font-bold text-xs transition-all active:scale-[0.98]"
            >
              Tutup
            </button>
          </template>
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
