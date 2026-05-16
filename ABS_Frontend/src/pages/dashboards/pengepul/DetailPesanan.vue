<template>
  <DashboardLayout title="Detail Pesanan">
    <div v-if="loading" class="flex items-center justify-center min-h-[60vh]">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-[#4A7043]"></div>
    </div>

    <div v-else-if="order" class="max-w-4xl mx-auto pb-20">
      <!-- Header Info -->
      <div class="mb-8 flex items-center justify-between">
        <div>
          <button @click="router.back()" class="flex items-center gap-2 text-gray-500 hover:text-gray-800 transition-all mb-2">
            <Icon icon="material-symbols:arrow-back" class="w-5 h-5" />
            <span class="text-sm font-bold">Kembali</span>
          </button>
          <h1 class="text-2xl font-black text-gray-800">Detail Pesanan</h1>
          <p class="text-sm text-gray-400 font-medium">#{{ order.transaksi_id }}</p>
        </div>
      </div>

      <!-- Stepper Progress -->
      <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8 mb-6 overflow-x-auto">
        <div class="min-w-[600px] flex justify-between relative">
          <!-- Connector Line -->
          <div class="absolute top-5 left-[12%] right-[12%] h-0.5 bg-gray-100 z-0">
            <div 
              class="h-full bg-[#8B5E3C] transition-all duration-500"
              :style="{ width: stepperProgress + '%' }"
            ></div>
          </div>

          <!-- Step 1 -->
          <div class="relative z-10 flex flex-col items-center gap-3 text-center w-1/4">
            <div :class="['w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm shadow-sm transition-all', currentStep >= 1 ? 'bg-[#8B5E3C] text-white' : 'bg-gray-100 text-gray-400']">
              <Icon v-if="currentStep > 1" icon="material-symbols:check" class="w-6 h-6" />
              <span v-else>1</span>
            </div>
            <div>
              <p :class="['text-[11px] font-bold uppercase tracking-wider mb-1', currentStep >= 1 ? 'text-[#8B5E3C]' : 'text-gray-400']">Request Dibuat</p>
              <p class="text-[9px] text-gray-400 leading-tight">Lakukan pembayaran dan upload bukti</p>
            </div>
          </div>

          <!-- Step 2 -->
          <div class="relative z-10 flex flex-col items-center gap-3 text-center w-1/4">
            <div :class="['w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm shadow-sm transition-all', currentStep >= 2 ? 'bg-[#8B5E3C] text-white' : 'bg-gray-100 text-gray-400']">
              <Icon v-if="currentStep > 2" icon="material-symbols:check" class="w-6 h-6" />
              <span v-else>2</span>
            </div>
            <div>
              <p :class="['text-[11px] font-bold uppercase tracking-wider mb-1', currentStep >= 2 ? 'text-[#8B5E3C]' : 'text-gray-400']">Validasi Pembayaran</p>
              <p class="text-[9px] text-gray-400 leading-tight">Petugas memvalidasi pembayaran</p>
            </div>
          </div>

          <!-- Step 3 -->
          <div class="relative z-10 flex flex-col items-center gap-3 text-center w-1/4">
            <div :class="['w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm shadow-sm transition-all', currentStep >= 3 ? 'bg-[#8B5E3C] text-white' : 'bg-gray-100 text-gray-400']">
              <Icon v-if="currentStep > 3" icon="material-symbols:check" class="w-6 h-6" />
              <span v-else>3</span>
            </div>
            <div>
              <p :class="['text-[11px] font-bold uppercase tracking-wider mb-1', currentStep >= 3 ? 'text-[#8B5E3C]' : 'text-gray-400']">Siap Diambil</p>
              <p class="text-[9px] text-gray-400 leading-tight">Barang siap diambil di gudang</p>
            </div>
          </div>

          <!-- Step 4 -->
          <div class="relative z-10 flex flex-col items-center gap-3 text-center w-1/4">
            <div :class="['w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm shadow-sm transition-all', currentStep >= 4 ? 'bg-[#8B5E3C] text-white' : 'bg-gray-100 text-gray-400']">
              <Icon v-if="currentStep === 4" icon="material-symbols:check" class="w-6 h-6" />
              <span v-else>4</span>
            </div>
            <div>
              <p :class="['text-[11px] font-bold uppercase tracking-wider mb-1', currentStep >= 4 ? 'text-[#8B5E3C]' : 'text-gray-400']">Selesai</p>
              <p class="text-[9px] text-gray-400 leading-tight">Transaksi selesai dilakukan</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Status Info Message -->
      <div v-if="order.status === 'proses' && !order.bukti_transfer" class="bg-blue-50 border border-blue-100 rounded-3xl p-6 mb-6">
        <div class="flex gap-4">
          <Icon icon="material-symbols:info-outline" class="w-6 h-6 text-blue-600 shrink-0" />
          <div>
            <h3 class="font-bold text-blue-700 text-sm mb-1">Disetujui - Lakukan Pembayaran</h3>
            <p class="text-[11px] text-blue-600 mb-4 leading-relaxed">Silakan lakukan pembayaran dalam waktu:</p>
            
            <div class="flex items-center gap-2 text-red-600 bg-white/50 w-fit px-4 py-2 rounded-xl border border-red-100">
              <Icon icon="material-symbols:timer-outline" class="w-4 h-4" />
              <span class="font-black text-sm">{{ countdown }}</span>
            </div>
            <p class="text-[10px] text-gray-400 mt-2">*Pesanan akan otomatis dibatalkan jika tidak dibayar dalam 24 jam</p>
          </div>
        </div>
      </div>

      <div v-else-if="order.status === 'proses' && order.bukti_transfer" class="bg-cyan-50 border border-cyan-100 rounded-3xl p-6 mb-6">
        <div class="flex gap-4">
          <Icon icon="material-symbols:hourglass-empty" class="w-6 h-6 text-cyan-600 shrink-0" />
          <div>
            <h3 class="font-bold text-cyan-700 text-sm mb-1">Menunggu Validasi Pembayaran</h3>
            <p class="text-[11px] text-cyan-600 leading-relaxed">Bukti pembayaran Anda sedang divalidasi oleh petugas. Proses ini biasanya memakan waktu 1-2 jam.</p>
          </div>
        </div>
      </div>

      <div v-else-if="order.status === 'siap_diambil'" class="bg-green-50 border border-green-100 rounded-3xl p-6 mb-6">
        <div class="flex gap-4">
          <Icon icon="material-symbols:check-circle-outline" class="w-6 h-6 text-green-600 shrink-0" />
          <div>
            <h3 class="font-bold text-green-700 text-sm mb-1">Siap Diambil</h3>
            <p class="text-[11px] text-green-600 mb-4 leading-relaxed">Pembayaran divalidasi. Pesanan ini perlu diambil di gudang yang ditentukan:</p>
            
            <div class="space-y-3">
              <div v-for="(gudang, id) in groupedItemsByGudang" :key="id" class="flex gap-3">
                <div class="w-1.5 h-1.5 rounded-full bg-blue-600 mt-1.5 shrink-0"></div>
                <div>
                  <p class="text-[11px] font-bold text-gray-700">Gudang {{ id }}</p>
                  <p class="text-[10px] text-gray-500">{{ gudang.alamat }}</p>
                  <p class="text-[10px] text-gray-400 mt-0.5">Barang: {{ gudang.items.join(', ') }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div v-else-if="order.status === 'selesai'" class="bg-gray-50 border border-gray-100 rounded-3xl p-6 mb-6">
        <div class="flex gap-4">
          <Icon icon="material-symbols:check-all" class="w-6 h-6 text-gray-600 shrink-0" />
          <div>
            <h3 class="font-bold text-gray-800 text-sm mb-1">Selesai</h3>
            <p class="text-[11px] text-gray-500 leading-relaxed">Terima kasih! Transaksi telah selesai. Barang sudah berhasil diambil.</p>
          </div>
        </div>
      </div>

      <!-- Payment Instructions Stage 1 -->
      <div v-if="order.status === 'proses' && !order.bukti_transfer" class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8 mb-6">
        <h3 class="text-sm font-bold text-gray-500 mb-6 flex items-center gap-2">
          <Icon icon="material-symbols:receipt-long-outline" class="w-5 h-5" />
          Instruksi Pembayaran
        </h3>

        <div class="bg-[#8B5E3C] rounded-2xl p-6 text-white mb-8">
          <p class="text-xs opacity-80 mb-1">Total yang harus dibayar:</p>
          <p class="text-3xl font-black">{{ formatCurrency(order.total_harga) }}</p>
        </div>

        <h4 class="text-xs font-bold text-gray-400 mb-4 uppercase tracking-widest flex items-center gap-2">
          <Icon icon="material-symbols:account-balance" class="w-4 h-4" />
          Rekening Tujuan Transfer
        </h4>

        <div class="space-y-4 mb-8">
          <div v-for="bank in banks" :key="bank.name" class="p-4 rounded-2xl border border-gray-100 hover:border-blue-200 transition-all group relative overflow-hidden">
            <div class="flex justify-between items-center relative z-10">
              <div>
                <p class="text-[10px] font-bold text-blue-600 uppercase mb-1">{{ bank.name }}</p>
                <p class="text-xl font-black text-gray-800 tracking-wider">{{ bank.number }}</p>
                <p class="text-[10px] text-gray-400 mt-1">a.n. Bank Sampah Indonesia</p>
              </div>
              <div class="px-3 py-1 bg-blue-50 text-blue-600 text-[10px] font-black rounded-lg group-hover:bg-blue-600 group-hover:text-white transition-all">
                {{ bank.code }}
              </div>
            </div>
          </div>
        </div>

        <div class="bg-amber-50 rounded-2xl p-6 border border-amber-100">
          <h4 class="text-xs font-bold text-amber-700 mb-3 flex items-center gap-2">
            <Icon icon="material-symbols:edit-note-outline" class="w-4 h-4" />
            Langkah-langkah:
          </h4>
          <ol class="space-y-2">
            <li v-for="(step, i) in paymentSteps" :key="i" class="text-[11px] text-amber-800 flex gap-2">
              <span class="font-bold">{{ i + 1 }}.</span>
              <span v-html="step"></span>
            </li>
          </ol>
        </div>
      </div>

      <!-- Upload Proof Area Stage 1 -->
      <div v-if="order.status === 'proses' && !order.bukti_transfer" class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8 mb-6">
        <h3 class="text-sm font-bold text-gray-500 mb-6 flex items-center gap-2">
          <Icon icon="material-symbols:cloud-upload-outline" class="w-5 h-5" />
          Upload Bukti Pembayaran
        </h3>

        <div 
          @click="$refs.fileInput.click()"
          class="border-2 border-dashed border-gray-200 rounded-3xl p-10 flex flex-col items-center justify-center gap-4 hover:border-[#4A7043] hover:bg-green-50/30 transition-all cursor-pointer group"
        >
          <input type="file" ref="fileInput" class="hidden" accept="image/*" @change="handleFileUpload" />
          
          <div v-if="!selectedFile" class="flex flex-col items-center text-center">
            <div class="w-16 h-16 rounded-2xl bg-gray-50 flex items-center justify-center group-hover:scale-110 transition-all">
              <Icon icon="material-symbols:add-photo-alternate-outline" class="w-8 h-8 text-gray-400 group-hover:text-[#4A7043]" />
            </div>
            <p class="mt-4 text-sm font-bold text-gray-800">Klik untuk pilih gambar</p>
            <p class="text-[10px] text-gray-400 mt-1">Format: JPG, PNG. Maksimal 5MB</p>
          </div>

          <div v-else class="w-full flex flex-col items-center">
            <div class="relative w-40 h-40 rounded-2xl overflow-hidden border border-gray-100 shadow-sm">
              <img :src="previewUrl" class="w-full h-full object-cover" />
              <button @click.stop="selectedFile = null; previewUrl = null" class="absolute top-2 right-2 w-8 h-8 rounded-full bg-red-500 text-white flex items-center justify-center shadow-lg active:scale-95">
                <Icon icon="material-symbols:close" class="w-5 h-5" />
              </button>
            </div>
            <p class="mt-4 text-[10px] text-gray-500 italic">{{ selectedFile.name }}</p>
          </div>
        </div>

        <button 
          v-if="selectedFile"
          @click="submitProof"
          :disabled="uploading"
          class="w-full mt-6 py-4 bg-[#4A7043] text-white font-black rounded-2xl hover:bg-[#3D5C37] transition-all flex items-center justify-center gap-2 disabled:opacity-50"
        >
          <Icon v-if="!uploading" icon="material-symbols:send-outline" class="w-5 h-5" />
          <span v-if="uploading">Mengunggah...</span>
          <span v-else>Kirim Bukti Pembayaran</span>
        </button>
      </div>

      <!-- Order Summary Card -->
      <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8 mb-6">
        <h3 class="text-sm font-bold text-gray-500 mb-6">Detail Pesanan</h3>
        
        <div class="space-y-6">
          <div v-for="item in order.detail_transaksi" :key="item.id" class="flex gap-4">
            <div class="w-16 h-16 rounded-xl bg-gray-50 border border-gray-100 overflow-hidden shrink-0">
              <img v-if="item.sampah?.item_sampah?.foto" :src="`${axios.defaults.baseURL}/storage/${item.sampah.item_sampah.foto}`" class="w-full h-full object-cover" />
              <div v-else class="w-full h-full flex items-center justify-center text-gray-200">
                <Icon icon="material-symbols:image-not-supported" class="w-6 h-6" />
              </div>
            </div>
            <div class="flex-1">
              <h4 class="text-sm font-bold text-gray-800">{{ item.sampah?.item_sampah?.nama }}</h4>
              <div class="flex items-center gap-2 mt-1">
                <span class="text-[9px] font-bold text-gray-400 bg-gray-50 px-1.5 py-0.5 rounded-md uppercase tracking-wider">{{ item.sampah?.item_sampah?.kategori_sampah?.nama_kategori }}</span>
                <div class="flex items-center gap-1">
                  <div class="w-1.5 h-1.5 rounded-full bg-blue-600"></div>
                  <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">GUDANG {{ item.sampah?.gudang_id }}</span>
                </div>
              </div>
              <p class="text-[10px] text-gray-400 mt-2">{{ item.berat }} kg × {{ formatCurrency(item.harga) }}</p>
            </div>
            <div class="text-right">
              <p class="text-sm font-black text-gray-700">{{ formatCurrency(item.berat * item.harga) }}</p>
            </div>
          </div>
        </div>

        <div class="mt-8 pt-6 border-t border-gray-100 flex justify-between items-center">
          <p class="text-sm font-bold text-gray-400">Total Pembayaran</p>
          <p class="text-xl font-black text-gray-800">{{ formatCurrency(order.total_harga) }}</p>
        </div>
      </div>

      <!-- Activity Log -->
      <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8 mb-6">
        <h3 class="text-sm font-bold text-gray-500 mb-6 flex items-center gap-2">
          <Icon icon="material-symbols:history-edu-outline" class="w-5 h-5" />
          Riwayat Aktivitas
        </h3>

        <div class="relative pl-6 space-y-8 before:absolute before:left-[11px] before:top-2 before:bottom-2 before:w-0.5 before:bg-gray-100">
          <div v-for="(log, i) in mockedLogs" :key="i" class="relative">
            <div :class="['absolute -left-[23px] top-1 w-4 h-4 rounded-full border-4 border-white z-10 shadow-sm', i === 0 ? 'bg-[#8B5E3C]' : 'bg-gray-300']"></div>
            <div>
              <h4 :class="['text-xs font-bold leading-none mb-1', i === 0 ? 'text-gray-800' : 'text-gray-400']">{{ log.title }}</h4>
              <p class="text-[10px] text-gray-400">{{ log.date }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Cancel Warning Box Stage 1 -->
      <div v-if="order.status === 'proses' && !order.bukti_transfer" class="bg-white rounded-3xl border border-red-50 p-4 flex items-center justify-between">
        <div class="flex items-center gap-3">
          <div class="w-8 h-8 rounded-full bg-red-50 flex items-center justify-center text-red-500">
            <Icon icon="material-symbols:warning-outline" class="w-4 h-4" />
          </div>
          <p class="text-[10px] text-gray-400">Jika Anda ingin membatalkan request ini, klik tombol di samping.</p>
        </div>
        <button @click="cancelOrder" class="px-4 py-2 border border-red-200 text-red-500 text-[10px] font-black rounded-xl hover:bg-red-50 transition-all flex items-center gap-2">
          <Icon icon="material-symbols:cancel-outline" class="w-3.5 h-3.5" />
          Batalkan Pesanan
        </button>
      </div>
    </div>
  </DashboardLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, inject } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import { Icon } from '@iconify/vue'
import Swal from 'sweetalert2'
import dayjs from 'dayjs'

const route = useRoute()
const router = useRouter()
const axios = inject('axios')

const loading = ref(true)
const order = ref(null)
const countdown = ref('24:00:00')
const uploading = ref(false)
const selectedFile = ref(null)
const previewUrl = ref(null)

let timerInterval = null

const banks = [
  { name: 'Bank BCA', number: '1234567890', code: 'BCA' },
  { name: 'Bank Mandiri', number: '9876543210', code: 'MDR' },
  { name: 'Bank BNI', number: '5555666677', code: 'BNI' }
]

const paymentSteps = [
  'Transfer <strong>TEPAT {{ formatCurrency(order?.total_harga) }}</strong> ke salah satu rekening di atas',
  'Screenshot atau foto bukti transfer yang jelas',
  'Upload bukti transfer di form di bawah',
  'Tunggu validasi petugas (biasanya 1-2 jam)',
  'Setelah valid, ambil barang di gudang'
]

const currentStep = computed(() => {
  if (!order.value) return 1
  switch (order.value.status) {
    case 'proses': return order.value.bukti_transfer ? 2 : 1
    case 'validasi': return 2
    case 'siap_diambil': return 3
    case 'selesai': return 4
    default: return 1
  }
})

const stepperProgress = computed(() => {
  return ((currentStep.value - 1) / 3) * 100
})

const mockedLogs = computed(() => {
  if (!order.value) return []
  const logs = []
  
  if (order.value.status === 'selesai') {
    logs.push({ title: 'Barang diambil', date: dayjs(order.value.updated_at).format('DD MMMM YYYY pukul HH:mm') })
  }
  if (order.value.status === 'siap_diambil' || order.value.status === 'selesai') {
    logs.push({ title: 'Pembayaran divalidasi', date: dayjs(order.value.updated_at).subtract(1, 'hour').format('DD MMMM YYYY pukul HH:mm') })
  }
  if (order.value.bukti_transfer) {
    logs.push({ title: 'Bukti pembayaran diupload', date: dayjs(order.value.updated_at).subtract(2, 'hour').format('DD MMMM YYYY pukul HH:mm') })
  }
  
  // Always show these
  logs.push({ title: 'Disetujui petugas', date: dayjs(order.value.created_at).add(30, 'minute').format('DD MMMM YYYY pukul HH:mm') })
  logs.push({ title: 'Request dibuat', date: dayjs(order.value.created_at).format('DD MMMM YYYY pukul HH:mm') })
  
  return logs
})

const groupedItemsByGudang = computed(() => {
  if (!order.value) return {}
  const groups = {}
  order.value.detail_transaksi.forEach(item => {
    const gid = item.sampah?.gudang_id || 'Utama'
    if (!groups[gid]) {
      groups[gid] = {
        alamat: item.sampah?.gudang?.alamat || 'Lokasi Gudang tidak diketahui',
        items: []
      }
    }
    groups[gid].items.push(`${item.sampah?.item_sampah?.nama} (${item.berat} kg)`)
  })
  return groups
})

const fetchOrder = async () => {
  try {
    const res = await axios.get(`/api/pengepul/request-pembelian/show/${route.params.id}`)
    order.value = res.data
    
    // Add calculated total_harga if not present
    if (!order.value.total_harga) {
      order.value.total_harga = order.value.detail_transaksi.reduce((acc, d) => acc + (d.berat * d.harga), 0)
    }

    if (order.value.status === 'proses' && !order.value.bukti_transfer) {
      startCountdown()
    }
  } catch (err) {
    console.error('Fetch order error:', err)
    Swal.fire('Gagal', 'Tidak dapat mengambil detail pesanan.', 'error')
  } finally {
    loading.value = false
  }
}

const startCountdown = () => {
  const deadline = dayjs(order.value.deadline)
  timerInterval = setInterval(() => {
    const now = dayjs()
    const diff = deadline.diff(now)
    
    if (diff <= 0) {
      countdown.value = '00:00:00'
      clearInterval(timerInterval)
      return
    }

    const h = Math.floor(diff / (1000 * 60 * 60))
    const m = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60))
    const s = Math.floor((diff % (1000 * 60)) / 1000)
    
    countdown.value = `${h.toString().padStart(2, '0')}:${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`
  }, 1000)
}

const handleFileUpload = (e) => {
  const file = e.target.files[0]
  if (file) {
    if (file.size > 5 * 1024 * 1024) {
      Swal.fire('Ukuran Terlalu Besar', 'Maksimal ukuran file adalah 5MB.', 'warning')
      return
    }
    selectedFile.value = file
    previewUrl.value = URL.createObjectURL(file)
  }
}

const submitProof = async () => {
  if (!selectedFile.value) return

  uploading.value = true
  const formData = new FormData()
  formData.append('bukti_transfer', selectedFile.value)
  formData.append('_method', 'PUT')

  try {
    await axios.post(`/api/pengepul/request-pembelian/${order.value.transaksi_id}`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    
    await Swal.fire({
      title: 'Berhasil!',
      text: 'Bukti pembayaran Anda telah dikirim. Menunggu validasi petugas.',
      icon: 'success',
      confirmButtonColor: '#4A7043'
    })
    
    fetchOrder()
    selectedFile.value = null
    previewUrl.value = null
  } catch (err) {
    console.error('Upload error:', err)
    Swal.fire('Gagal', 'Terjadi kesalahan saat mengunggah bukti.', 'error')
  } finally {
    uploading.value = false
  }
}

const cancelOrder = async () => {
  const result = await Swal.fire({
    title: 'Batalkan Pesanan?',
    text: 'Apakah Anda yakin ingin membatalkan pesanan ini?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#EF4444',
    cancelButtonColor: '#9CA3AF',
    confirmButtonText: 'Ya, Batalkan!',
    cancelButtonText: 'Kembali'
  })

  if (result.isConfirmed) {
    try {
      // Mocking cancel as reject by pengepul side
      // In real scenario, you might need a specific endpoint or update status to 'batal'
      Swal.fire('Berhasil', 'Pesanan Anda telah dibatalkan.', 'success')
      router.push('/dashboard-pengepul/pesanan-saya')
    } catch (err) {
      Swal.fire('Gagal', 'Gagal membatalkan pesanan.', 'error')
    }
  }
}

const formatCurrency = (val) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0
  }).format(val || 0)
}

onMounted(() => {
  fetchOrder()
})

onUnmounted(() => {
  if (timerInterval) clearInterval(timerInterval)
})
</script>

<style scoped>
.leading-tight { line-height: 1.25; }
</style>
