<script setup>
import { ref, onMounted, computed, inject } from "vue";
import { Icon } from "@iconify/vue";
import DashboardLayout from "@/layouts/DashboardLayout.vue";
import axios from 'axios'
import Swal from 'sweetalert2';
import { useRouter } from 'vue-router';

const router = useRouter();
const token = sessionStorage.getItem('token')

if (!token) {
  throw new Error('Otentikasi diperlukan.')
}

const user = ref(JSON.parse(sessionStorage.getItem("user")));
const headers = { 'Authorization': `Bearer ${token}` }

const listSampah = ref([]);
const listGudang = ref([]);
const summaryLaporan = ref({});
const detailsLaporan = ref({
  penjemputan: [],
  setor_manual: [],
  penarikan: [],
  pesanan_pengepul: []
});

const showDateModal = ref(false);
const activeTab = ref('penjemputan'); // penjemputan, setor_manual, penarikan, pesanan_pengepul
const showImageModal = ref(false);
const selectedImage = ref('');

const today = new Date()
const thirtyDaysAgo = new Date()
thirtyDaysAgo.setDate(today.getDate() - 30)

// Format ke YYYY-MM-DD
const formatDate = (date) => date.toISOString().split('T')[0]

const data = ref({
  start_date: formatDate(thirtyDaysAgo),
  end_date: formatDate(today),
  gudang_id: user.value.gudang_id,
  sampah: [],
})

const tempStartDate = ref(data.value.start_date);
const tempEndDate = ref(data.value.end_date);

const setPresetDate = (preset) => {
  const d = new Date();
  tempEndDate.value = formatDate(d);
  if (preset === 'hari_ini') {
    tempStartDate.value = formatDate(d);
  } else if (preset === 'minggu_ini') {
    d.setDate(d.getDate() - 7);
    tempStartDate.value = formatDate(d);
  } else if (preset === 'bulan_ini') {
    d.setMonth(d.getMonth() - 1);
    tempStartDate.value = formatDate(d);
  }
};

const applyDateFilter = () => {
  data.value.start_date = tempStartDate.value;
  data.value.end_date = tempEndDate.value;
  showDateModal.value = false;
  fetchData();
};

const dateRangeLabel = computed(() => {
  if (data.value.start_date === data.value.end_date) {
    return formatDateDisplay(data.value.start_date);
  }
  return `${formatDateDisplay(data.value.start_date)} - ${formatDateDisplay(data.value.end_date)}`;
});

const formatDateDisplay = (dateStr) => {
  if (!dateStr) return '';
  const date = new Date(dateStr);
  return date.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
};

const formatTimeDisplay = (dateStr) => {
  if (!dateStr) return '';
  const date = new Date(dateStr);
  return date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
};

const formatDateTime = (dateStr) => {
  if (!dateStr) return '';
  const date = new Date(dateStr);
  return date.toLocaleString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
};

const formatRupiah = (angka) => {
  return new Intl.NumberFormat("id-ID", {
    style: "currency",
    currency: "IDR",
    minimumFractionDigits: 0,
  }).format(angka || 0);
};

const viewImage = (imagePath) => {
  if (!imagePath) return;
  selectedImage.value = imagePath;
  showImageModal.value = true;
};

const navigateToPreview = () => {
  router.push({
    path: '/dashboard-petugas/preview-laporan',
    query: {
      start_date: data.value.start_date,
      end_date: data.value.end_date,
      gudang_id: data.value.gudang_id
    }
  });
};

const fetchData = async () => {
  try {
    const response = await axios.get("https://api.tabungansampah.online/api/laporan/list-sampah", { headers });
    listSampah.value = response.data;

    const responseG = await axios.get(`${axios.defaults.baseURL}/api/laporan/list-gudang`, { headers });
    listGudang.value = responseG.data;
    
    const responseSummary = await axios.get(`${axios.defaults.baseURL}/api/petugas/summary-laporan`, {
      headers,
      params: {
        start_date: data.value.start_date,
        end_date: data.value.end_date,
        gudang_id: data.value.gudang_id
      }
    });
    
    summaryLaporan.value = responseSummary.data.data;
    const rawDetails = responseSummary.data.details || {
      penjemputan: [],
      setor_manual: [],
      penarikan: [],
      pesanan_pengepul: []
    };

    // FILTER LOGIC: Pastikan kita MENGHAPUS data yang bukan 'selesai' dari array
    // Ini akan otomatis memperbaiki angka di Stats Grid maupun Tabel
    detailsLaporan.value = {
      ...rawDetails,
      penarikan: rawDetails.penarikan.filter(item => item.status === 'selesai')
    };

  } catch (err) {
    console.error("Gagal mengambil data:", err);
  }
};

onMounted(async () => {
  fetchData();
});
</script>

<template>
  <DashboardLayout title="Laporan Harian">

    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between mb-5">
      <div class="flex flex-col sm:flex-row sm:items-center gap-3 w-full lg:w-auto">
        <div class="flex items-center gap-2 text-xs sm:text-sm text-stone-500 font-medium">
          <Icon icon="material-symbols:calendar-today-outline" class="w-4.5 h-4.5 shrink-0" />
          <span>{{ dateRangeLabel }}</span>
        </div>
        <div class="flex gap-2 w-full sm:w-auto">
          <button 
            @click="showDateModal = true"
            class="flex-1 sm:flex-none flex items-center justify-center gap-1.5 bg-white rounded-xl px-3 py-2 border border-stone-200 shadow-sm text-xs font-bold text-stone-700 hover:bg-stone-50 transition-all cursor-pointer"
          >
            <Icon icon="material-symbols:edit-calendar-outline" class="w-4 h-4 text-[#2d4a3e] shrink-0" />
            Pilih Rentang Tanggal
          </button>
          <button
            @click="navigateToPreview"
            class="flex-1 sm:flex-none lg:hidden flex items-center justify-center gap-1.5 bg-[#2d4a3e] text-white text-xs font-bold px-3 py-2.5 rounded-xl hover:bg-[#1f3529] transition-all cursor-pointer"
          >
            <Icon icon="material-symbols:description-outline" class="w-4 h-4 shrink-0" />
            Preview Laporan
          </button>
        </div>
      </div>

      <button
        @click="navigateToPreview"
        class="hidden lg:flex items-center gap-2 bg-[#2d4a3e] text-white text-sm font-medium px-4 py-2.5 rounded-xl hover:bg-[#1f3529] transition-all cursor-pointer"
      >
        <Icon icon="material-symbols:description-outline" class="w-4 h-4" />
        Preview Laporan
      </button>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-5">
      <div class="relative rounded-2xl p-3.5 sm:p-5 bg-[#2d6a4f] text-white shadow-md">
        <div class="flex items-start justify-between mb-4 sm:mb-6">
          <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-white/15 flex items-center justify-center">
            <Icon icon="material-symbols:package-2-outline" class="w-4.5 h-4.5 sm:w-5 sm:h-5" />
          </div>
          <Icon icon="material-symbols:trending-up" class="w-4 h-4 sm:w-5 sm:h-5 opacity-70" />
        </div>
        <p class="text-xl sm:text-3xl font-bold leading-none mb-1">{{ summaryLaporan.penjemputan_count || 0 }}</p>
        <p class="text-[10px] sm:text-xs text-white/70 mb-3 sm:mb-4 uppercase sm:normal-case font-semibold tracking-wider sm:tracking-normal">Penjemputan Selesai</p>
        <div class="border-t border-white/20 pt-2 sm:pt-3">
          <p class="text-[10px] sm:text-xs text-white/60 mb-0.5 sm:mb-1">Total Nilai</p>
          <p class="text-sm sm:text-base font-semibold">{{ formatRupiah(summaryLaporan.penjemputan_harga || 0) }}</p>
        </div>
      </div>

      <div class="relative rounded-2xl p-3.5 sm:p-5 bg-[#7a4419] text-white shadow-md">
        <div class="flex items-start justify-between mb-4 sm:mb-6">
          <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-white/15 flex items-center justify-center">
            <Icon icon="material-symbols:store-outline" class="w-4.5 h-4.5 sm:w-5 sm:h-5" />
          </div>
          <Icon icon="material-symbols:trending-up" class="w-4 h-4 sm:w-5 sm:h-5 opacity-70" />
        </div>
        <p class="text-xl sm:text-3xl font-bold leading-none mb-1">{{ summaryLaporan.setor_count || 0 }}</p>
        <p class="text-[10px] sm:text-xs text-white/70 mb-3 sm:mb-4 uppercase sm:normal-case font-semibold tracking-wider sm:tracking-normal">Setor Manual</p>
        <div class="border-t border-white/20 pt-2 sm:pt-3">
          <p class="text-[10px] sm:text-xs text-white/60 mb-0.5 sm:mb-1">Total Nilai</p>
          <p class="text-sm sm:text-base font-semibold">{{ formatRupiah(summaryLaporan.setor_harga || 0) }}</p>
        </div>
      </div>

      <div class="relative rounded-2xl p-3.5 sm:p-5 bg-[#1a7a6e] text-white shadow-md">
        <div class="flex items-start justify-between mb-4 sm:mb-6">
          <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-white/15 flex items-center justify-center">
            <Icon icon="material-symbols:credit-card-outline" class="w-4.5 h-4.5 sm:w-5 sm:h-5" />
          </div>
          <Icon icon="material-symbols:trending-up" class="w-4 h-4 sm:w-5 sm:h-5 opacity-70" />
        </div>
        <p class="text-xl sm:text-3xl font-bold leading-none mb-1">{{ detailsLaporan.penarikan.length || 0 }}</p>
        <p class="text-[10px] sm:text-xs text-white/70 mb-3 sm:mb-4 uppercase sm:normal-case font-semibold tracking-wider sm:tracking-normal">Penarikan Disetujui</p>
        <div class="border-t border-white/20 pt-2 sm:pt-3">
          <p class="text-[10px] sm:text-xs text-white/60 mb-0.5 sm:mb-1">Total Nominal</p>
          <p class="text-sm sm:text-base font-semibold">
            {{ formatRupiah(detailsLaporan.penarikan.reduce((sum, item) => sum + Number(item.jumlah || 0), 0)) }}
          </p>
        </div>
      </div>

      <div class="relative rounded-2xl p-3.5 sm:p-5 bg-[#4a4a4a] text-white shadow-md">
        <div class="flex items-start justify-between mb-4 sm:mb-6">
          <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-white/15 flex items-center justify-center">
            <Icon icon="material-symbols:receipt-outline" class="w-4.5 h-4.5 sm:w-5 sm:h-5" />
          </div>
          <Icon icon="material-symbols:trending-up" class="w-4 h-4 sm:w-5 sm:h-5 opacity-70" />
        </div>
        <p class="text-xl sm:text-3xl font-bold leading-none mb-1">{{ summaryLaporan.pengepul_count || 0 }}</p>
        <p class="text-[10px] sm:text-xs text-white/70 mb-3 sm:mb-4 uppercase sm:normal-case font-semibold tracking-wider sm:tracking-normal">Pesanan Pengepul</p>
        <div class="border-t border-white/20 pt-2 sm:pt-3">
          <p class="text-[10px] sm:text-xs text-white/60 mb-0.5 sm:mb-1">Total Nilai</p>
          <p class="text-sm sm:text-base font-semibold">{{ formatRupiah(summaryLaporan.pengepul_harga || 0) }}</p>
        </div>
      </div>
    </div>

    <div class="bg-white rounded-3xl p-6 shadow-sm border border-stone-200/80 mb-5">
      <h3 class="text-lg font-bold text-stone-800 mb-4">Detail Transaksi</h3>
      
      <div class="grid grid-cols-2 lg:flex lg:flex-row gap-1.5 lg:gap-0 bg-[#F5F8F5] p-1.5 rounded-2xl mb-6">
        <button 
          @click="activeTab = 'penjemputan'"
          :class="['w-full flex items-center justify-center gap-1.5 py-2.5 lg:py-3 rounded-xl text-xs lg:text-sm font-semibold transition-all cursor-pointer', activeTab === 'penjemputan' ? 'bg-[#4A7043] text-white shadow-md' : 'text-stone-500 hover:text-stone-700 hover:bg-stone-100/50']"
        >
          <Icon icon="material-symbols:package-2-outline" class="w-4.5 h-4.5 lg:w-5 lg:h-5 shrink-0" />
          <span>Penjemputan ({{ detailsLaporan.penjemputan?.length || 0 }})</span>
        </button>
        <button 
          @click="activeTab = 'setor_manual'"
          :class="['w-full flex items-center justify-center gap-1.5 py-2.5 lg:py-3 rounded-xl text-xs lg:text-sm font-semibold transition-all cursor-pointer', activeTab === 'setor_manual' ? 'bg-[#4A7043] text-white shadow-md' : 'text-stone-500 hover:text-stone-700 hover:bg-stone-100/50']"
        >
          <Icon icon="material-symbols:store-outline" class="w-4.5 h-4.5 lg:w-5 lg:h-5 shrink-0" />
          <span>Setor Manual ({{ detailsLaporan.setor_manual?.length || 0 }})</span>
        </button>
        <button 
          @click="activeTab = 'penarikan'"
          :class="['w-full flex items-center justify-center gap-1.5 py-2.5 lg:py-3 rounded-xl text-xs lg:text-sm font-semibold transition-all cursor-pointer', activeTab === 'penarikan' ? 'bg-[#4A7043] text-white shadow-md' : 'text-stone-500 hover:text-stone-700 hover:bg-stone-100/50']"
        >
          <Icon icon="material-symbols:credit-card-outline" class="w-4.5 h-4.5 lg:w-5 lg:h-5 shrink-0" />
          <span>Penarikan ({{ detailsLaporan.penarikan?.length || 0 }})</span>
        </button>
        <button 
          @click="activeTab = 'pesanan_pengepul'"
          :class="['w-full flex items-center justify-center gap-1.5 py-2.5 lg:py-3 rounded-xl text-xs lg:text-sm font-semibold transition-all cursor-pointer', activeTab === 'pesanan_pengepul' ? 'bg-[#4A7043] text-white shadow-md' : 'text-stone-500 hover:text-stone-700 hover:bg-stone-100/50']"
        >
          <Icon icon="material-symbols:receipt-outline" class="w-4.5 h-4.5 lg:w-5 lg:h-5 shrink-0" />
          <span><span class="lg:inline hidden">Pesanan </span>Pengepul ({{ detailsLaporan.pesanan_pengepul?.length || 0 }})</span>
        </button>
      </div>

      <!-- Desktop Table View (Penjemputan) -->
      <div v-if="activeTab === 'penjemputan'" class="hidden lg:block overflow-x-auto">
        <table class="w-full text-left text-sm whitespace-nowrap">
          <thead class="text-xs text-stone-400 font-bold border-b border-stone-100">
            <tr>
              <th class="py-3 px-4 w-32">ID</th>
              <th class="py-3 px-4 w-32">Waktu</th>
              <th class="py-3 px-4">Nasabah</th>
              <th class="py-3 px-4">Jenis Sampah</th>
              <th class="py-3 px-4 text-center">Berat (kg)</th>
              <th class="py-3 px-4 text-right">Nilai</th>
              <th class="py-3 px-4 text-center w-24">Foto</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-stone-100">
            <tr v-if="!detailsLaporan.penjemputan?.length">
              <td colspan="7" class="py-8 text-center text-stone-400 font-medium">Tidak ada data penjemputan</td>
            </tr>
            <tr v-for="item in detailsLaporan.penjemputan" :key="item.transaksi_id" class="hover:bg-stone-50/50 transition-colors">
              <td class="py-4 px-4 font-bold text-stone-700">REQ-{{ String(item.transaksi_id).padStart(3, '0') }}</td>
              <td class="py-4 px-4 text-stone-500">{{ formatTimeDisplay(item.created_at) }}</td>
              <td class="py-4 px-4">
                <div class="font-bold text-stone-800">{{ item.penimbangan?.[0]?.nasabah?.nama || '-' }}</div>
                <div class="text-xs text-stone-400">NSB-{{ String(item.penimbangan?.[0]?.nasabah_id).padStart(3, '0') }}</div>
              </td>
              <td class="py-4 px-4 text-stone-500">{{ item.penimbangan?.map(p => p.sampah?.item_sampah?.nama).join(', ') || '-' }}</td>
              <td class="py-4 px-4 text-center font-bold text-stone-700">{{ item.penimbangan?.reduce((acc, p) => acc + Number(p.berat_timbang || 0), 0) }}</td>
              <td class="py-4 px-4 text-right font-bold text-[#4A7043]">{{ formatRupiah(item.total_harga) }}</td>
              <td class="py-4 px-4 text-center">
                <button v-if="item.penimbangan?.[0]?.foto" @click="viewImage(item.penimbangan[0].foto)" class="inline-flex items-center gap-1 px-3 py-1.5 bg-[#4A7043] text-white text-xs font-semibold rounded-lg hover:bg-[#3D5C37] transition-colors cursor-pointer">
                  <Icon icon="material-symbols:search" class="w-3.5 h-3.5" />
                  Lihat
                </button>
                <span v-else class="text-xs text-stone-400">-</span>
              </td>
            </tr>
          </tbody>
          <tfoot v-if="detailsLaporan.penjemputan?.length" class="bg-[#F5F8F5]">
            <tr>
              <td colspan="4" class="py-4 px-4 text-right font-bold text-stone-600">TOTAL PENJEMPUTAN:</td>
              <td class="py-4 px-4 text-center font-black text-stone-800">{{ detailsLaporan.penjemputan.reduce((sum, item) => sum + item.penimbangan.reduce((acc, p) => acc + Number(p.berat_timbang || 0), 0), 0) }} kg</td>
              <td class="py-4 px-4 text-right font-black text-[#4A7043]">{{ formatRupiah(detailsLaporan.penjemputan.reduce((sum, item) => sum + Number(item.total_harga || 0), 0)) }}</td>
              <td></td>
            </tr>
          </tfoot>
        </table>
      </div>

      <!-- Mobile List View (Penjemputan) -->
      <div v-if="activeTab === 'penjemputan'" class="block lg:hidden space-y-3">
        <div v-if="!detailsLaporan.penjemputan?.length" class="py-8 text-center text-stone-400 font-medium text-xs">
          Tidak ada data penjemputan
        </div>
        <div v-for="item in detailsLaporan.penjemputan" :key="item.transaksi_id" class="bg-stone-50 border border-stone-100 rounded-2xl p-4 space-y-3 shadow-sm">
          <div class="flex justify-between items-center text-xs">
            <span class="font-black text-[#4A7043]">REQ-{{ String(item.transaksi_id).padStart(3, '0') }}</span>
            <span class="text-stone-400 font-medium">{{ formatTimeDisplay(item.created_at) }}</span>
          </div>
          <div class="space-y-1">
            <div class="font-black text-stone-800 text-sm">{{ item.penimbangan?.[0]?.nasabah?.nama || '-' }}</div>
            <div class="text-[10px] font-bold text-stone-400">NSB-{{ String(item.penimbangan?.[0]?.nasabah_id).padStart(3, '0') }}</div>
            <div class="text-xs text-stone-500 font-medium truncate">Sampah: {{ item.penimbangan?.map(p => p.sampah?.item_sampah?.nama).join(', ') || '-' }}</div>
          </div>
          <div class="flex justify-between items-center pt-3 border-t border-stone-200/50 text-xs">
            <div class="flex items-center gap-1.5">
              <span class="text-stone-400 font-bold">Berat:</span>
              <span class="font-black text-stone-700">{{ item.penimbangan?.reduce((acc, p) => acc + Number(p.berat_timbang || 0), 0) }} kg</span>
            </div>
            <div class="flex items-center gap-2">
              <span class="font-black text-[#4A7043]">{{ formatRupiah(item.total_harga) }}</span>
              <button v-if="item.penimbangan?.[0]?.foto" @click="viewImage(item.penimbangan[0].foto)" class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-[#4A7043] text-white text-[10px] font-bold rounded-lg hover:bg-[#3D5C37] transition-colors cursor-pointer">
                <Icon icon="material-symbols:search" class="w-3.5 h-3.5" />
                Foto
              </button>
            </div>
          </div>
        </div>
        <!-- Mobile Total -->
        <div v-if="detailsLaporan.penjemputan?.length" class="bg-[#F5F8F5] rounded-xl p-3.5 flex justify-between items-center text-xs font-black">
          <span class="text-stone-500">TOTAL:</span>
          <div class="flex items-center gap-3">
            <span class="text-stone-700">{{ detailsLaporan.penjemputan.reduce((sum, item) => sum + item.penimbangan.reduce((acc, p) => acc + Number(p.berat_timbang || 0), 0), 0) }} kg</span>
            <span class="text-[#4A7043]">{{ formatRupiah(detailsLaporan.penjemputan.reduce((sum, item) => sum + Number(item.total_harga || 0), 0)) }}</span>
          </div>
        </div>
      </div>

      <!-- Desktop Table View (Setor Manual) -->
      <div v-if="activeTab === 'setor_manual'" class="hidden lg:block overflow-x-auto">
        <table class="w-full text-left text-sm whitespace-nowrap">
          <thead class="text-xs text-stone-400 font-bold border-b border-stone-100">
            <tr>
              <th class="py-3 px-4 w-32">ID</th>
              <th class="py-3 px-4 w-32">Waktu</th>
              <th class="py-3 px-4">Nasabah</th>
              <th class="py-3 px-4">Jenis Sampah</th>
              <th class="py-3 px-4 text-center">Berat (kg)</th>
              <th class="py-3 px-4 text-right">Nilai</th>
              <th class="py-3 px-4 text-center w-24">Foto</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-stone-100">
            <tr v-if="!detailsLaporan.setor_manual?.length">
              <td colspan="7" class="py-8 text-center text-stone-400 font-medium">Tidak ada data setor manual</td>
            </tr>
            <tr v-for="item in detailsLaporan.setor_manual" :key="item.transaksi_id" class="hover:bg-stone-50/50 transition-colors">
              <td class="py-4 px-4 font-bold text-stone-700">TR-{{ String(item.transaksi_id).padStart(3, '0') }}</td>
              <td class="py-4 px-4 text-stone-500">{{ formatTimeDisplay(item.created_at) }}</td>
              <td class="py-4 px-4">
                <div class="font-bold text-stone-800">{{ item.penimbangan?.[0]?.nasabah?.nama || '-' }}</div>
                <div class="text-xs text-stone-400">NSB-{{ String(item.penimbangan?.[0]?.nasabah_id).padStart(3, '0') }}</div>
              </td>
              <td class="py-4 px-4 text-stone-500">{{ item.penimbangan?.map(p => p.sampah?.item_sampah?.nama).join(', ') || '-' }}</td>
              <td class="py-4 px-4 text-center font-bold text-stone-700">{{ item.penimbangan?.reduce((acc, p) => acc + Number(p.berat_timbang || 0), 0) }}</td>
              <td class="py-4 px-4 text-right font-bold text-[#4A7043]">{{ formatRupiah(item.total_harga) }}</td>
              <td class="py-4 px-4 text-center">
                <button v-if="item.penimbangan?.[0]?.foto" @click="viewImage(item.penimbangan[0].foto)" class="inline-flex items-center gap-1 px-3 py-1.5 bg-[#4A7043] text-white text-xs font-semibold rounded-lg hover:bg-[#3D5C37] transition-colors cursor-pointer">
                  <Icon icon="material-symbols:search" class="w-3.5 h-3.5" />
                  Lihat
                </button>
                <span v-else class="text-xs text-stone-400">-</span>
              </td>
            </tr>
          </tbody>
          <tfoot v-if="detailsLaporan.setor_manual?.length" class="bg-[#F5F8F5]">
            <tr>
              <td colspan="4" class="py-4 px-4 text-right font-bold text-stone-600">TOTAL SETOR MANUAL:</td>
              <td class="py-4 px-4 text-center font-black text-stone-800">{{ detailsLaporan.setor_manual.reduce((sum, item) => sum + item.penimbangan.reduce((acc, p) => acc + Number(p.berat_timbang || 0), 0), 0) }} kg</td>
              <td class="py-4 px-4 text-right font-black text-[#4A7043]">{{ formatRupiah(detailsLaporan.setor_manual.reduce((sum, item) => sum + Number(item.total_harga || 0), 0)) }}</td>
              <td></td>
            </tr>
          </tfoot>
        </table>
      </div>

      <!-- Mobile List View (Setor Manual) -->
      <div v-if="activeTab === 'setor_manual'" class="block lg:hidden space-y-3">
        <div v-if="!detailsLaporan.setor_manual?.length" class="py-8 text-center text-stone-400 font-medium text-xs">
          Tidak ada data setor manual
        </div>
        <div v-for="item in detailsLaporan.setor_manual" :key="item.transaksi_id" class="bg-stone-50 border border-stone-100 rounded-2xl p-4 space-y-3 shadow-sm">
          <div class="flex justify-between items-center text-xs">
            <span class="font-black text-[#4A7043]">TR-{{ String(item.transaksi_id).padStart(3, '0') }}</span>
            <span class="text-stone-400 font-medium">{{ formatTimeDisplay(item.created_at) }}</span>
          </div>
          <div class="space-y-1">
            <div class="font-black text-stone-800 text-sm">{{ item.penimbangan?.[0]?.nasabah?.nama || '-' }}</div>
            <div class="text-[10px] font-bold text-stone-400">NSB-{{ String(item.penimbangan?.[0]?.nasabah_id).padStart(3, '0') }}</div>
            <div class="text-xs text-stone-500 font-medium truncate">Sampah: {{ item.penimbangan?.map(p => p.sampah?.item_sampah?.nama).join(', ') || '-' }}</div>
          </div>
          <div class="flex justify-between items-center pt-3 border-t border-stone-200/50 text-xs">
            <div class="flex items-center gap-1.5">
              <span class="text-stone-400 font-bold">Berat:</span>
              <span class="font-black text-stone-700">{{ item.penimbangan?.reduce((acc, p) => acc + Number(p.berat_timbang || 0), 0) }} kg</span>
            </div>
            <div class="flex items-center gap-2">
              <span class="font-black text-[#4A7043]">{{ formatRupiah(item.total_harga) }}</span>
              <button v-if="item.penimbangan?.[0]?.foto" @click="viewImage(item.penimbangan[0].foto)" class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-[#4A7043] text-white text-[10px] font-bold rounded-lg hover:bg-[#3D5C37] transition-colors cursor-pointer">
                <Icon icon="material-symbols:search" class="w-3.5 h-3.5" />
                Foto
              </button>
            </div>
          </div>
        </div>
        <!-- Mobile Total -->
        <div v-if="detailsLaporan.setor_manual?.length" class="bg-[#F5F8F5] rounded-xl p-3.5 flex justify-between items-center text-xs font-black">
          <span class="text-stone-500">TOTAL:</span>
          <div class="flex items-center gap-3">
            <span class="text-stone-700">{{ detailsLaporan.setor_manual.reduce((sum, item) => sum + item.penimbangan.reduce((acc, p) => acc + Number(p.berat_timbang || 0), 0), 0) }} kg</span>
            <span class="text-[#4A7043]">{{ formatRupiah(detailsLaporan.setor_manual.reduce((sum, item) => sum + Number(item.total_harga || 0), 0)) }}</span>
          </div>
        </div>
      </div>

      <!-- Desktop Table View (Penarikan) -->
      <div v-if="activeTab === 'penarikan'" class="hidden lg:block overflow-x-auto">
        <table class="w-full text-left text-sm whitespace-nowrap">
          <thead class="text-xs text-stone-400 font-bold border-b border-stone-100">
            <tr>
              <th class="py-3 px-4 w-32">ID</th>
              <th class="py-3 px-4 w-32">Waktu</th>
              <th class="py-3 px-4">Nasabah</th>
              <th class="py-3 px-4">No. Rekening</th>
              <th class="py-3 px-4 text-center w-24">Status</th>
              <th class="py-3 px-4 text-right">Jumlah</th>
              <th class="py-3 px-4 text-center w-32">Bukti Transfer</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-stone-100">
            <tr v-if="!detailsLaporan.penarikan?.length">
              <td colspan="7" class="py-8 text-center text-stone-400 font-medium">Tidak ada data penarikan disetujui</td>
            </tr>
            <tr v-for="item in detailsLaporan.penarikan" :key="item.penarikan_id" class="hover:bg-stone-50/50 transition-colors">
              <td class="py-4 px-4 font-bold text-stone-700">WD-{{ String(item.penarikan_id).padStart(3, '0') }}</td>
              <td class="py-4 px-4 text-stone-500">{{ formatTimeDisplay(item.created_at) }}</td>
              <td class="py-4 px-4">
                <div class="font-bold text-stone-800">{{ item.nasabah?.nama || '-' }}</div>
                <div class="text-xs text-stone-400">NSB-{{ String(item.nasabah_id).padStart(3, '0') }}</div>
              </td>
              <td class="py-4 px-4 text-stone-500">{{ item.nama_bank }} - {{ item.no_rekening }}</td>
              <td class="py-4 px-4 text-center">
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-green-100 text-green-700 uppercase tracking-wider">
                  Selesai
                </span>
              </td>
              <td class="py-4 px-4 text-right font-bold text-[#4A7043]">{{ formatRupiah(item.jumlah) }}</td>
              <td class="py-4 px-4 text-center">
                <button v-if="item.bukti_tf" @click="viewImage(item.bukti_tf)" class="inline-flex items-center gap-1 px-3 py-1.5 bg-[#4A7043] text-white text-xs font-semibold rounded-lg hover:bg-[#3D5C37] transition-colors cursor-pointer">
                  <Icon icon="material-symbols:search" class="w-3.5 h-3.5" />
                  Lihat
                </button>
                <span v-else class="text-xs text-stone-400">-</span>
              </td>
            </tr>
          </tbody>
          <tfoot v-if="detailsLaporan.penarikan?.length" class="bg-[#F5F8F5]">
            <tr>
              <td colspan="5" class="py-4 px-4 text-right font-bold text-stone-600">TOTAL PENARIKAN DISETUJUI:</td>
              <td class="py-4 px-4 text-right font-black text-[#4A7043]">{{ formatRupiah(detailsLaporan.penarikan.reduce((sum, item) => sum + Number(item.jumlah || 0), 0)) }}</td>
              <td></td>
            </tr>
          </tfoot>
        </table>
      </div>

      <!-- Mobile List View (Penarikan) -->
      <div v-if="activeTab === 'penarikan'" class="block lg:hidden space-y-3">
        <div v-if="!detailsLaporan.penarikan?.length" class="py-8 text-center text-stone-400 font-medium text-xs">
          Tidak ada data penarikan disetujui
        </div>
        <div v-for="item in detailsLaporan.penarikan" :key="item.penarikan_id" class="bg-stone-50 border border-stone-100 rounded-2xl p-4 space-y-3 shadow-sm">
          <div class="flex justify-between items-center text-xs">
            <span class="font-black text-[#4A7043]">WD-{{ String(item.penarikan_id).padStart(3, '0') }}</span>
            <span class="text-stone-400 font-medium">{{ formatTimeDisplay(item.created_at) }}</span>
          </div>
          <div class="space-y-1">
            <div class="font-black text-stone-800 text-sm">{{ item.nasabah?.nama || '-' }}</div>
            <div class="text-[10px] font-bold text-stone-400">NSB-{{ String(item.nasabah_id).padStart(3, '0') }}</div>
            <div class="text-xs text-stone-500 font-medium">Rekening: {{ item.nama_bank }} - {{ item.no_rekening }}</div>
          </div>
          <div class="flex justify-between items-center pt-3 border-t border-stone-200/50 text-xs">
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-bold bg-green-100 text-green-700 uppercase tracking-wider">Selesai</span>
            <div class="flex items-center gap-2">
              <span class="font-black text-[#4A7043]">{{ formatRupiah(item.jumlah) }}</span>
              <button v-if="item.bukti_tf" @click="viewImage(item.bukti_tf)" class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-[#4A7043] text-white text-[10px] font-bold rounded-lg hover:bg-[#3D5C37] transition-colors cursor-pointer">
                <Icon icon="material-symbols:search" class="w-3.5 h-3.5" />
                Bukti
              </button>
            </div>
          </div>
        </div>
        <!-- Mobile Total -->
        <div v-if="detailsLaporan.penarikan?.length" class="bg-[#F5F8F5] rounded-xl p-3.5 flex justify-between items-center text-xs font-black">
          <span class="text-stone-500">TOTAL:</span>
          <span class="text-[#4A7043]">{{ formatRupiah(detailsLaporan.penarikan.reduce((sum, item) => sum + Number(item.jumlah || 0), 0)) }}</span>
        </div>
      </div>

      <!-- Desktop Table View (Pesanan Pengepul) -->
      <div v-if="activeTab === 'pesanan_pengepul'" class="hidden lg:block overflow-x-auto">
        <table class="w-full text-left text-sm whitespace-nowrap">
          <thead class="text-xs text-stone-400 font-bold border-b border-stone-100">
            <tr>
              <th class="py-3 px-4 w-32">ID Pesanan</th>
              <th class="py-3 px-4">Tanggal & Deadline</th>
              <th class="py-3 px-4">Pengepul</th>
              <th class="py-3 px-4 text-center">Total Berat</th>
              <th class="py-3 px-4 text-right">Total Nilai</th>
              <th class="py-3 px-4 text-center w-24">Status</th>
              <th class="py-3 px-4 text-center w-32">Bukti Transfer</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-stone-100">
            <tr v-if="!detailsLaporan.pesanan_pengepul?.length">
              <td colspan="7" class="py-8 text-center text-stone-400 font-medium">Tidak ada data pesanan pengepul</td>
            </tr>
            <tr v-for="item in detailsLaporan.pesanan_pengepul" :key="item.transaksi_id" class="hover:bg-stone-50/50 transition-colors">
              <td class="py-4 px-4 font-bold text-stone-700">PO-{{ String(item.transaksi_id).padStart(3, '0') }}</td>
              <td class="py-4 px-4">
                <div class="font-bold text-stone-800">{{ formatDateTime(item.created_at) }}</div>
                <div class="text-[10px] text-stone-400 mt-0.5">Deadline: {{ formatDateTime(item.deadline) }}</div>
              </td>
              <td class="py-4 px-4">
                <div class="font-bold text-stone-800">{{ item.pengepul?.nama || item.pengepul?.nama_lembaga || '-' }}</div>
                <div class="text-xs text-stone-400">{{ item.pengepul?.no_telp || '-' }}</div>
              </td>
              <td class="py-4 px-4 text-center font-bold text-stone-700">{{ item.detail_transaksi?.reduce((acc, d) => acc + Number(d.berat || 0), 0) }} kg</td>
              <td class="py-4 px-4 text-right font-bold text-[#4A7043]">{{ formatRupiah(item.detail_transaksi?.reduce((acc, d) => acc + (Number(d.berat) * Number(d.harga) || 0), 0) || Number(item.total_harga)) }}</td>
              <td class="py-4 px-4 text-center">
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-green-100 text-green-700 uppercase tracking-wider">
                  Selesai
                </span>
              </td>
              <td class="py-4 px-4 text-center">
                <button v-if="item.bukti_transfer" @click="viewImage(item.bukti_transfer)" class="inline-flex items-center gap-1 px-3 py-1.5 bg-[#4A7043] text-white text-xs font-semibold rounded-lg hover:bg-[#3D5C37] transition-colors cursor-pointer">
                  <Icon icon="material-symbols:search" class="w-3.5 h-3.5" />
                  Lihat
                </button>
                <span v-else class="text-xs text-stone-400">-</span>
              </td>
            </tr>
          </tbody>
          <tfoot v-if="detailsLaporan.pesanan_pengepul?.length" class="bg-[#F5F8F5]">
            <tr>
              <td colspan="3" class="py-4 px-4 text-right font-bold text-stone-600">TOTAL PESANAN PENGEPUL:</td>
              <td class="py-4 px-4 text-center font-black text-stone-800">{{ detailsLaporan.pesanan_pengepul.reduce((sum, item) => sum + item.detail_transaksi.reduce((acc, d) => acc + Number(d.berat || 0), 0), 0) }} kg</td>
              <td class="py-4 px-4 text-right font-black text-[#4A7043]">{{ formatRupiah(detailsLaporan.pesanan_pengepul.reduce((sum, item) => sum + (item.detail_transaksi?.reduce((acc, d) => acc + (Number(d.berat) * Number(d.harga) || 0), 0) || Number(item.total_harga) || 0), 0)) }}</td>
              <td colspan="2"></td>
            </tr>
          </tfoot>
        </table>
      </div>

      <!-- Mobile List View (Pesanan Pengepul) -->
      <div v-if="activeTab === 'pesanan_pengepul'" class="block lg:hidden space-y-3">
        <div v-if="!detailsLaporan.pesanan_pengepul?.length" class="py-8 text-center text-stone-400 font-medium text-xs">
          Tidak ada data pesanan pengepul
        </div>
        <div v-for="item in detailsLaporan.pesanan_pengepul" :key="item.transaksi_id" class="bg-stone-50 border border-stone-100 rounded-2xl p-4 space-y-3 shadow-sm">
          <div class="flex justify-between items-center text-xs">
            <span class="font-black text-[#4A7043]">PO-{{ String(item.transaksi_id).padStart(3, '0') }}</span>
            <span class="text-stone-400 font-medium">{{ formatTimeDisplay(item.created_at) }}</span>
          </div>
          <div class="space-y-1">
            <div class="font-black text-stone-800 text-sm">{{ item.pengepul?.nama || item.pengepul?.nama_lembaga || '-' }}</div>
            <div class="text-[10px] font-bold text-stone-400">{{ item.pengepul?.no_telp || '-' }}</div>
            <div class="text-[10px] text-stone-400 mt-0.5">Deadline: {{ formatDateTime(item.deadline) }}</div>
          </div>
          <div class="flex justify-between items-center pt-3 border-t border-stone-200/50 text-xs">
            <div class="flex items-center gap-1.5">
              <span class="text-stone-400 font-bold">Berat:</span>
              <span class="font-black text-stone-700">{{ item.detail_transaksi?.reduce((acc, d) => acc + Number(d.berat || 0), 0) }} kg</span>
            </div>
            <div class="flex items-center gap-2">
              <span class="font-black text-[#4A7043]">{{ formatRupiah(item.detail_transaksi?.reduce((acc, d) => acc + (Number(d.berat) * Number(d.harga) || 0), 0) || Number(item.total_harga)) }}</span>
              <button v-if="item.bukti_transfer" @click="viewImage(item.bukti_transfer)" class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-[#4A7043] text-white text-[10px] font-bold rounded-lg hover:bg-[#3D5C37] transition-colors cursor-pointer">
                <Icon icon="material-symbols:search" class="w-3.5 h-3.5" />
                Bukti
              </button>
            </div>
          </div>
        </div>
        <!-- Mobile Total -->
        <div v-if="detailsLaporan.pesanan_pengepul?.length" class="bg-[#F5F8F5] rounded-xl p-3.5 flex justify-between items-center text-xs font-black">
          <span class="text-stone-500">TOTAL:</span>
          <div class="flex items-center gap-3">
            <span class="text-stone-700">{{ detailsLaporan.pesanan_pengepul.reduce((sum, item) => sum + item.detail_transaksi.reduce((acc, d) => acc + Number(d.berat || 0), 0), 0) }} kg</span>
            <span class="text-[#4A7043]">{{ formatRupiah(detailsLaporan.pesanan_pengepul.reduce((sum, item) => sum + (item.detail_transaksi?.reduce((acc, d) => acc + (Number(d.berat) * Number(d.harga) || 0), 0) || Number(item.total_harga) || 0), 0)) }}</span>
          </div>
        </div>
      </div>
    </div>

    <Teleport to="body">
      <Transition name="fade">
        <div v-if="showDateModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
          <div class="absolute inset-0 bg-stone-900/60 backdrop-blur-sm" @click="showDateModal = false"></div>
          
          <div class="relative bg-white w-full max-w-md rounded-[2rem] shadow-2xl overflow-hidden p-6 space-y-6">
            <div class="flex justify-between items-start">
              <h3 class="text-lg font-black text-stone-800">Pilih Rentang Tanggal</h3>
              <button @click="showDateModal = false" class="p-2 hover:bg-stone-50 rounded-xl transition-colors text-stone-400">
                <Icon icon="material-symbols:close" class="w-6 h-6" />
              </button>
            </div>
            
            <div class="space-y-4">
              <div class="grid grid-cols-3 gap-2">
                <button @click="setPresetDate('hari_ini')" class="py-2 px-3 rounded-xl border border-stone-200 text-xs font-bold text-stone-600 hover:bg-stone-50 transition-colors focus:ring-2 focus:ring-[#4A7043]/20">Hari Ini</button>
                <button @click="setPresetDate('minggu_ini')" class="py-2 px-3 rounded-xl border border-stone-200 text-xs font-bold text-stone-600 hover:bg-stone-50 transition-colors focus:ring-2 focus:ring-[#4A7043]/20">Minggu Ini</button>
                <button @click="setPresetDate('bulan_ini')" class="py-2 px-3 rounded-xl border border-stone-200 text-xs font-bold text-stone-600 hover:bg-stone-50 transition-colors focus:ring-2 focus:ring-[#4A7043]/20">Bulan Ini</button>
              </div>

              <div class="grid grid-cols-2 gap-4 pt-2">
                <div>
                  <label class="block text-xs font-bold text-stone-500 mb-1.5">Dari Tanggal</label>
                  <input type="date" v-model="tempStartDate" class="w-full bg-stone-50 border border-stone-200 rounded-xl px-3 py-2.5 text-sm font-medium text-stone-700 focus:outline-none focus:ring-2 focus:ring-[#4A7043]/20 focus:border-[#4A7043] transition-all" />
                </div>
                <div>
                  <label class="block text-xs font-bold text-stone-500 mb-1.5">Sampai Tanggal</label>
                  <input type="date" v-model="tempEndDate" class="w-full bg-stone-50 border border-stone-200 rounded-xl px-3 py-2.5 text-sm font-medium text-stone-700 focus:outline-none focus:ring-2 focus:ring-[#4A7043]/20 focus:border-[#4A7043] transition-all" />
                </div>
              </div>
            </div>
            
            <div class="flex gap-3 pt-2">
              <button 
                @click="showDateModal = false" 
                class="flex-1 py-3 rounded-xl border border-stone-200 text-stone-600 font-bold text-sm hover:bg-stone-50 transition-colors"
              >
                Batalkan
              </button>
              <button 
                @click="applyDateFilter" 
                class="flex-1 py-3 rounded-xl bg-[#4A7043] text-white font-bold text-sm hover:bg-[#3D5C37] transition-all flex items-center justify-center gap-1.5 shadow-md active:scale-95"
              >
                Simpan
              </button>
            </div>
          </div>
        </div>
      </Transition>

      <Transition name="fade">
        <div v-if="showImageModal" class="fixed inset-0 z-[60] flex items-center justify-center p-4">
          <div class="absolute inset-0 bg-stone-900/80 backdrop-blur-sm" @click="showImageModal = false"></div>
          
          <div class="relative bg-white w-full max-w-lg rounded-3xl shadow-2xl overflow-hidden p-6 space-y-6">
            <div class="flex justify-between items-start border-b border-stone-100 pb-4">
              <h3 class="text-lg font-black text-stone-800">Preview Foto / Bukti</h3>
              <button @click="showImageModal = false" class="p-2 hover:bg-stone-50 rounded-xl transition-colors text-stone-400">
                <Icon icon="material-symbols:close" class="w-6 h-6" />
              </button>
            </div>
            
            <div class="flex justify-center bg-stone-50 rounded-2xl p-2 overflow-hidden border border-stone-100 min-h-[200px]">
              <img 
                :src="`${axios.defaults.baseURL}/storage/${selectedImage}`" 
                class="max-h-[60vh] max-w-full object-contain rounded-xl"
                alt="Preview"
              />
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
  </DashboardLayout>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>