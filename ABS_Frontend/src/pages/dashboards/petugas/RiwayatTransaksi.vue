<script setup>
import { ref, onMounted, computed, inject, watch } from "vue";
import DashboardLayout from "@/layouts/DashboardLayout.vue";
import { Icon } from "@iconify/vue";
import { checkRole } from "@/utils";

// Hak akses Petugas
checkRole("petugas");

const axios = inject("axios");

// State
const activeFilter = ref("penjemputan"); // penjemputan, setor_manual, penarikan
const searchQuery = ref("");
const historyData = ref([]);
const loading = ref(true);
const pagination = ref({
  current_page: 1,
  last_page: 1,
  total: 0,
});

// Modal State
const showModal = ref(false);
const selectedItem = ref(null);
const modalActiveTab = ref("penjemputan"); // penjemputan, penimbangan
const loadingDetail = ref(false);

const filters = [
  { id: "penjemputan", label: "Riwayat Penjemputan", icon: "material-symbols:local-shipping-outline" },
  { id: "setor_manual", label: "Riwayat Setor Manual", icon: "material-symbols:storefront-outline" },
  { id: "penarikan", label: "Riwayat Penarikan", icon: "material-symbols:account-balance-wallet-outline" },
];

const fetchHistory = async (page = 1) => {
  loading.value = true;
  try {
    let endpoint = "";
    if (activeFilter.value === "penjemputan") {
      endpoint = "/api/petugas/riwayat-penjemputan";
    } else if (activeFilter.value === "setor_manual") {
      endpoint = "/api/petugas/riwayat-setor-manual";
    } else if (activeFilter.value === "penarikan") {
      endpoint = "/api/petugas/riwayat-penarikan";
    }

    const response = await axios.get(endpoint, {
      params: {
        page: page,
        search: searchQuery.value,
      },
    });

    if (activeFilter.value === "penarikan") {
        historyData.value = response.data.penarikan.data;
        pagination.value = {
            current_page: response.data.penarikan.current_page,
            last_page: response.data.penarikan.last_page,
            total: response.data.penarikan.total,
        };
    } else {
        historyData.value = response.data.data;
        pagination.value = {
            current_page: response.data.current_page,
            last_page: response.data.last_page,
            total: response.data.total,
        };
    }
  } catch (err) {
    console.error("Failed to fetch history:", err);
  } finally {
    loading.value = false;
  }
};

let searchTimeout = null;

const debouncedSearch = () => {
  if (searchTimeout) clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    pagination.value.current_page = 1;
    fetchHistory();
  }, 500);
};

watch(searchQuery, debouncedSearch);

watch(activeFilter, () => {
  searchQuery.value = "";
  pagination.value.current_page = 1;
  fetchHistory();
});

const openDetail = async (item) => {
  showModal.value = true;
  loadingDetail.value = true;
  modalActiveTab.value = activeFilter.value === 'penarikan' ? 'penarikan' : 'penjemputan';
  
  try {
    let endpoint = "";
    let id = "";
    
    if (activeFilter.value === 'penjemputan') {
        endpoint = `/api/petugas/riwayat-penjemputan/${item.penjemputan_id}`;
    } else if (activeFilter.value === 'setor_manual') {
        // For manual deposit, we might use the transaction detail or similar
        // Based on the image, manual deposit detail also has "Penimbangan" info
        selectedItem.value = item;
        loadingDetail.value = false;
        modalActiveTab.value = 'penimbangan';
        return;
    } else if (activeFilter.value === 'penarikan') {
        endpoint = `/api/petugas/riwayat-penarikan/${item.penarikan_id}`;
    }

    const res = await axios.get(endpoint);
    selectedItem.value = res.data.data;
  } catch (err) {
    console.error("Failed to fetch detail:", err);
  } finally {
    loadingDetail.value = false;
  }
};

const closeModal = () => {
  showModal.value = false;
  selectedItem.value = null;
};

const formatRupiah = (number) => {
  return new Intl.NumberFormat("id-ID", {
    style: "currency",
    currency: "IDR",
    minimumFractionDigits: 0,
  }).format(number);
};

const formatDate = (dateString) => {
  if (!dateString) return "-";
  const options = { year: "numeric", month: "short", day: "numeric" };
  return new Date(dateString).toLocaleDateString("id-ID", options);
};

const formatTime = (dateString) => {
    if (!dateString) return "-";
    const date = new Date(dateString);
    return date.toLocaleTimeString("id-ID", { hour: '2-digit', minute: '2-digit' });
};

const getStatusColor = (status) => {
  switch (status?.toLowerCase()) {
    case "selesai": return "bg-green-100 text-green-600";
    case "tolak": return "bg-red-100 text-red-600";
    case "batal": return "bg-gray-100 text-gray-600";
    case "proses": return "bg-blue-100 text-blue-600";
    case "pending": return "bg-yellow-100 text-yellow-600";
    default: return "bg-gray-100 text-gray-600";
  }
};

const getStatusLabel = (status) => {
    if (status === 'tolak') return 'Ditolak';
    if (status === 'batal') return 'Dibatalkan';
    return status?.charAt(0).toUpperCase() + status?.slice(1);
};

const calculateTotalWeight = (penimbangan) => {
    if (!penimbangan) return 0;
    return penimbangan.reduce((acc, item) => acc + parseFloat(item.berat_timbang || 0), 0);
};

const calculateTotalValue = (penimbangan) => {
    if (!penimbangan || penimbangan.length === 0) return 0;
    return penimbangan.reduce((acc, item) => {
        const harga = parseFloat(item.berat_timbang || 0) * (item.sampah?.item_sampah?.harga_beli || 0);
        return acc + harga;
    }, 0);
};

const getWasteTypes = (item) => {
    const details = item.detail_penjemputan || item.detailPenjemputan;
    if (activeFilter.value === 'penjemputan' && details) {
        return details.map(d => d.sampah?.item_sampah?.nama).filter(Boolean).join(", ");
    }
    const penimbangans = item.penimbangan;
    if (penimbangans && penimbangans.length > 0) {
        return penimbangans.map(p => p.sampah?.item_sampah?.nama).filter(Boolean).join(", ");
    }
    return "-";
};

onMounted(() => {
  fetchHistory();
});
</script>

<template>
  <DashboardLayout title="Riwayat Transaksi">
    <div class="space-y-6">
      <!-- Header Info -->
      <div>
        <p class="text-gray-500 text-sm">Lihat semua riwayat transaksi penjemputan, setor manual, dan penarikan yang sudah selesai</p>
      </div>

      <!-- Search and Filter Section -->
      <div class="bg-white p-6 rounded-3xl shadow-sm space-y-4 border border-gray-100">
        <div class="flex flex-col md:flex-row gap-4">
          <!-- Search Bar -->
          <div class="flex-1 relative">
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Cari ID Transaksi atau Nama Nasabah"
              class="w-full pl-6 pr-12 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-[#4A7043] transition-all text-sm"
            />
            <div class="absolute right-4 top-1/2 -translate-y-1/2 bg-[#4A7043] p-2 rounded-xl text-white">
              <Icon icon="material-symbols:search" class="w-5 h-5" />
            </div>
          </div>
          <!-- Date Filter (Optional Placeholder) -->
          <div class="w-full md:w-48 relative">
             <div class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                <Icon icon="material-symbols:calendar-today" class="w-5 h-5" />
             </div>
             <input type="text" readonly placeholder="Pilih Tanggal" class="w-full pl-12 py-4 bg-gray-50 border-none rounded-2xl text-sm cursor-not-allowed opacity-50" />
          </div>
        </div>

        <!-- Filter Buttons -->
        <div class="flex flex-wrap gap-2">
          <button
            v-for="filter in filters"
            :key="filter.id"
            @click="activeFilter = filter.id"
            :class="[
              'px-6 py-3 rounded-2xl flex items-center gap-3 transition-all text-sm font-medium border',
              activeFilter === filter.id
                ? 'bg-[#4A7043] text-white border-[#4A7043] shadow-lg shadow-[#4A7043]/20'
                : 'bg-white text-gray-500 border-gray-200 hover:border-[#4A7043] hover:text-[#4A7043]'
            ]"
          >
            <Icon :icon="filter.icon" class="w-5 h-5" />
            {{ filter.label }}
          </button>
        </div>
      </div>

      <!-- History List -->
      <div v-if="loading" class="flex justify-center py-20">
        <div class="animate-spin rounded-full h-12 w-12 border-4 border-[#4A7043] border-t-transparent"></div>
      </div>

      <div v-else-if="historyData.length === 0" class="bg-white p-12 rounded-3xl text-center border border-dashed border-gray-300">
        <Icon icon="material-symbols:history" class="w-16 h-16 mx-auto text-gray-300 mb-4" />
        <p class="text-gray-500 font-medium">Belum ada riwayat transaksi ditemukan</p>
      </div>

      <div v-else class="grid grid-cols-1 gap-4">
        <div
          v-for="item in historyData"
          :key="activeFilter === 'penjemputan' ? item.penjemputan_id : (activeFilter === 'setor_manual' ? item.transaksi_id : item.penarikan_id)"
          class="bg-white rounded-3xl p-6 border border-gray-100 hover:shadow-xl transition-all group relative overflow-hidden"
          :class="{'border-l-4 border-l-[#4A7043]': activeFilter === 'penjemputan' && item.status === 'selesai'}"
        >
          <!-- Card Content -->
          <div class="flex flex-col md:flex-row justify-between gap-6">
            <div class="space-y-3 flex-1">
              <div class="flex items-center gap-3">
                <span class="text-lg font-bold text-gray-800">
                    {{ (activeFilter === 'penjemputan' ? 'REQ-' : '') + (activeFilter === 'penjemputan' ? item.penjemputan_id : (activeFilter === 'setor_manual' ? item.transaksi_id : item.penarikan_id)) }}
                </span>
                <span :class="['px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider', getStatusColor(item.status)]">
                  {{ getStatusLabel(item.status) }}
                </span>
                <span class="px-3 py-1 rounded-full bg-gray-100 text-gray-500 text-[10px] font-bold uppercase tracking-wider">
                  {{ activeFilter === 'penjemputan' ? 'Penjemputan' : (activeFilter === 'setor_manual' ? 'Setor Manual' : 'Penarikan') }}
                </span>
              </div>
              
              <div>
                <h3 class="text-xl font-black text-gray-800">
                    {{ item.nasabah?.nama || item.penimbangan?.[0]?.nasabah?.nama || "N/A" }} 
                    <span class="text-gray-400 font-medium text-sm">(NSB-{{ item.nasabah?.nasabah_id || item.penimbangan?.[0]?.nasabah?.nasabah_id || "-" }})</span>
                </h3>
                <p class="text-gray-400 text-xs font-medium">{{ formatDate(item.created_at) }}</p>
              </div>

              <!-- Information Grid -->
              <div v-if="activeFilter !== 'penarikan'" class="grid grid-cols-2 md:grid-cols-3 gap-4 bg-gray-50 p-4 rounded-2xl mt-4">
                <div>
                  <p class="text-[10px] text-gray-400 font-bold uppercase">Berat Actual</p>
                  <p class="text-sm font-bold text-gray-700">
                    {{ activeFilter === 'penjemputan' ? (calculateTotalWeight(item.penimbangan) || "-") : calculateTotalWeight(item.penimbangan) }} kg
                  </p>
                </div>
                <div>
                  <p class="text-[10px] text-gray-400 font-bold uppercase">Jenis Sampah</p>
                  <p class="text-sm font-bold text-gray-700 truncate max-w-[150px]">{{ getWasteTypes(item) }}</p>
                </div>
                <div v-if="activeFilter === 'penjemputan'">
                  <p class="text-[10px] text-gray-400 font-bold uppercase">Tukang</p>
                  <p class="text-sm font-bold text-gray-700">{{ item.tukang?.nama || "-" }}</p>
                </div>
                <div v-if="activeFilter === 'setor_manual'">
                  <p class="text-[10px] text-gray-400 font-bold uppercase">Petugas</p>
                  <p class="text-sm font-bold text-gray-700">{{ item.petugas?.nama || "-" }}</p>
                </div>
              </div>

              <!-- Rejection / Cancellation Reason (If any) -->
              <div v-if="(item.status === 'tolak' || item.status === 'batal') && item.ket_status" class="bg-red-50 p-4 rounded-2xl mt-4 border border-red-100">
                <p class="text-[10px] text-red-400 font-bold uppercase">{{ item.status === 'tolak' ? 'Alasan Ditolak' : 'Alasan Dibatalkan' }}</p>
                <p class="text-sm font-bold text-red-600">{{ item.ket_status }}</p>
              </div>
            </div>

            <!-- Value Display -->
            <div class="flex flex-col items-end justify-between">
              <div class="text-right">
                <p class="text-[10px] text-gray-400 font-bold uppercase">{{ activeFilter === 'penarikan' ? 'Jumlah Penarikan' : 'Total Nilai' }}</p>
                <p class="text-2xl font-black text-[#4A7043]">
                    {{ activeFilter === 'penarikan' ? formatRupiah(item.jumlah) : (item.penimbangan?.length > 0 ? formatRupiah(calculateTotalValue(item.penimbangan)) : "-") }}
                </p>
              </div>
            </div>
          </div>

          <!-- Bottom Action -->
          <button
            @click="openDetail(item)"
            class="w-full mt-6 py-3 bg-[#4A7043] text-white rounded-2xl font-bold flex items-center justify-center gap-2 hover:bg-[#3d5c37] transition-all"
          >
            <Icon icon="material-symbols:visibility-outline" class="w-5 h-5" />
            Lihat Detail
          </button>
        </div>
      </div>

      <!-- Pagination (Simple) -->
      <div v-if="pagination.last_page > 1" class="flex justify-center items-center gap-4 py-6">
        <button
          @click="fetchHistory(pagination.current_page - 1)"
          :disabled="pagination.current_page === 1"
          class="p-3 rounded-xl border border-gray-200 disabled:opacity-50 hover:bg-gray-50 transition-all"
        >
          <Icon icon="material-symbols:chevron-left" class="w-6 h-6" />
        </button>
        <span class="font-bold text-sm text-gray-600">Halaman {{ pagination.current_page }} dari {{ pagination.last_page }}</span>
        <button
          @click="fetchHistory(pagination.current_page + 1)"
          :disabled="pagination.current_page === pagination.last_page"
          class="p-3 rounded-xl border border-gray-200 disabled:opacity-50 hover:bg-gray-50 transition-all"
        >
          <Icon icon="material-symbols:chevron-right" class="w-6 h-6" />
        </button>
      </div>
    </div>

    <!-- Detail Modal -->
    <div v-if="showModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
      <div class="bg-white w-full max-w-2xl rounded-[2.5rem] overflow-hidden shadow-2xl animate-in zoom-in duration-300 flex flex-col max-h-[90vh]">
        <!-- Modal Header -->
        <div class="bg-[#4A7043] p-6 text-white flex justify-between items-center relative">
          <div>
            <h2 class="text-xl font-bold">Detail Transaksi</h2>
            <p class="text-white/60 text-xs font-medium">{{ (activeFilter === 'penjemputan' ? 'REQ-' : '') + (selectedItem?.penjemputan_id || selectedItem?.transaksi_id || selectedItem?.penarikan_id) }}</p>
          </div>
          <button @click="closeModal" class="p-2 hover:bg-white/10 rounded-full transition-all">
            <Icon icon="material-symbols:close" class="w-6 h-6" />
          </button>
        </div>

        <!-- Modal Tabs (Only for Pickup/Manual) -->
        <div v-if="activeFilter !== 'penarikan'" class="p-4 flex gap-2 border-b border-gray-100">
          <button
            @click="modalActiveTab = 'penjemputan'"
            :class="[
              'flex-1 py-3 rounded-2xl font-bold text-sm transition-all flex items-center justify-center gap-2',
              modalActiveTab === 'penjemputan' ? 'bg-green-100 text-green-700' : 'bg-gray-50 text-gray-400 hover:bg-gray-100'
            ]"
          >
            <Icon icon="material-symbols:check-circle-outline" v-if="modalActiveTab === 'penjemputan'" />
            Penjemputan
          </button>
          <button
            @click="modalActiveTab = 'penimbangan'"
            :class="[
              'flex-1 py-3 rounded-2xl font-bold text-sm transition-all flex items-center justify-center gap-2',
              modalActiveTab === 'penimbangan' ? 'bg-green-100 text-green-700' : 'bg-gray-50 text-gray-400 hover:bg-gray-100'
            ]"
          >
            <Icon icon="material-symbols:check-circle-outline" v-if="modalActiveTab === 'penimbangan'" />
            Penimbangan
          </button>
        </div>

        <!-- Modal Content Scroll Area -->
        <div class="flex-1 overflow-y-auto p-6 space-y-6 custom-scrollbar">
          <div v-if="loadingDetail" class="flex justify-center py-10">
             <div class="animate-spin rounded-full h-8 w-8 border-3 border-[#4A7043] border-t-transparent"></div>
          </div>
          
          <div v-else-if="selectedItem">
            <!-- PENARIKAN TAB / CONTENT -->
            <div v-if="activeFilter === 'penarikan'" class="space-y-6">
                <!-- Info Penarikan -->
                <div class="bg-gray-50 p-6 rounded-3xl space-y-4 border border-gray-100">
                    <div class="flex items-center gap-3 mb-2">
                        <Icon icon="material-symbols:account-balance-wallet-outline" class="w-5 h-5 text-[#4A7043]" />
                        <span class="font-bold text-gray-700">Informasi Penarikan</span>
                    </div>
                    <div class="grid grid-cols-2 gap-y-4 text-sm">
                        <div>
                            <p class="text-gray-400 text-[10px] font-bold uppercase">Nama Nasabah</p>
                            <p class="font-bold text-gray-700">{{ selectedItem.nasabah?.nama }} (NSB-{{ selectedItem.nasabah?.nasabah_id }})</p>
                        </div>
                        <div>
                            <p class="text-gray-400 text-[10px] font-bold uppercase">Tanggal Request</p>
                            <p class="font-bold text-gray-700">{{ formatDate(selectedItem.created_at) }}</p>
                        </div>
                        <div>
                            <p class="text-gray-400 text-[10px] font-bold uppercase">Jumlah Penarikan</p>
                            <p class="font-bold text-green-600 text-lg">{{ formatRupiah(selectedItem.jumlah) }}</p>
                        </div>
                        <div>
                            <p class="text-gray-400 text-[10px] font-bold uppercase">Status</p>
                            <span :class="['px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider', getStatusColor(selectedItem.status)]">
                                {{ getStatusLabel(selectedItem.status) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Proof of Transfer -->
                <div v-if="selectedItem.bukti_tf" class="space-y-4">
                    <div class="flex items-center gap-3">
                        <Icon icon="material-symbols:image-outline" class="w-5 h-5 text-[#4A7043]" />
                        <span class="font-bold text-gray-700">Bukti Transfer</span>
                    </div>
                    <div class="aspect-video w-full rounded-3xl overflow-hidden border border-gray-200">
                        <img :src="`${axios.defaults.baseURL}/storage/${selectedItem.bukti_tf}`" class="w-full h-full object-cover" />
                    </div>
                </div>

                <!-- Rejection Reason -->
                <div v-if="selectedItem.status === 'tolak' && selectedItem.ket_status" class="bg-red-50 p-6 rounded-3xl border border-red-100">
                    <p class="text-red-500 font-bold text-sm mb-1">Alasan Penolakan:</p>
                    <p class="text-red-700 font-medium text-sm">{{ selectedItem.ket_status }}</p>
                </div>
            </div>

            <!-- PENJEMPUTAN TAB -->
            <div v-else-if="modalActiveTab === 'penjemputan'" class="space-y-6">
                <!-- Status Badge (For Cancel/Reject) -->
                <div v-if="selectedItem.status === 'tolak'" class="bg-red-50 p-4 rounded-2xl border border-red-100 flex items-center gap-3">
                    <div class="bg-red-100 p-2 rounded-xl text-red-600">
                        <Icon icon="material-symbols:cancel-outline" class="w-5 h-5" />
                    </div>
                    <span class="font-bold text-red-600 text-sm">Ditolak Petugas</span>
                </div>
                <div v-if="selectedItem.status === 'batal'" class="bg-gray-100 p-4 rounded-2xl border border-gray-200 flex items-center gap-3">
                    <div class="bg-gray-200 p-2 rounded-xl text-gray-600">
                        <Icon icon="material-symbols:block-outline" class="w-5 h-5" />
                    </div>
                    <span class="font-bold text-gray-600 text-sm">Dibatalkan Nasabah</span>
                </div>

                <!-- Informasi Nasabah -->
                <div class="bg-gray-50 p-6 rounded-3xl space-y-4 border border-gray-100">
                    <div class="flex items-center gap-3 mb-2">
                        <Icon icon="material-symbols:person-outline" class="w-5 h-5 text-[#4A7043]" />
                        <span class="font-bold text-gray-700">Informasi Nasabah</span>
                    </div>
                    <div class="space-y-4 text-sm">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-gray-400 text-[10px] font-bold uppercase">Nama Nasabah</p>
                                <p class="font-bold text-gray-700">{{ selectedItem.nasabah?.nama || selectedItem.penimbangan?.[0]?.nasabah?.nama }} (NSB-{{ selectedItem.nasabah?.nasabah_id || selectedItem.penimbangan?.[0]?.nasabah?.nasabah_id }})</p>
                            </div>
                            <div>
                                <p class="text-gray-400 text-[10px] font-bold uppercase">Nomor Telepon</p>
                                <p class="font-bold text-gray-700">
                                    {{ (selectedItem.nasabah?.no_hp || selectedItem.penimbangan?.[0]?.nasabah?.no_hp) || "Nasabah belum mengatur nomor telepon" }}
                                </p>
                            </div>
                        </div>
                        <div>
                            <p class="text-gray-400 text-[10px] font-bold uppercase">Alamat Penjemputan</p>
                            <p class="font-bold text-gray-700">{{ selectedItem.alamat }}</p>
                        </div>
                        <div>
                            <p class="text-gray-400 text-[10px] font-bold uppercase">Waktu Request</p>
                            <p class="font-bold text-gray-700">{{ formatDate(selectedItem.created_at) }}, {{ formatTime(selectedItem.created_at) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Detail Request -->
                <div class="bg-gray-50 p-6 rounded-3xl space-y-4 border border-gray-100">
                    <div class="flex items-center gap-3 mb-2">
                        <Icon icon="material-symbols:list-alt-outline" class="w-5 h-5 text-[#4A7043]" />
                        <span class="font-bold text-gray-700">Detail Request dari Nasabah</span>
                    </div>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <p class="text-gray-400 text-[10px] font-bold uppercase">Estimasi Berat</p>
                            <p class="font-bold text-gray-700">{{ selectedItem.estimasi_berat }}</p>
                        </div>
                        <div>
                            <p class="text-gray-400 text-[10px] font-bold uppercase">Jenis Sampah</p>
                            <p class="font-bold text-gray-700">{{ getWasteTypes(selectedItem) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Foto Sampah -->
                <div v-if="selectedItem.foto && selectedItem.foto.length > 0" class="space-y-4">
                    <p class="text-sm font-bold text-gray-700">Foto Sampah dari Nasabah</p>
                    <div class="grid grid-cols-3 gap-2">
                        <div v-for="(img, idx) in selectedItem.foto" :key="idx" class="aspect-square rounded-2xl overflow-hidden border border-gray-100">
                            <img :src="`${axios.defaults.baseURL}/storage/${img}`" class="w-full h-full object-cover" />
                        </div>
                    </div>
                </div>

                <!-- Catatan Nasabah -->
                <div class="bg-yellow-50 p-6 rounded-3xl border-l-4 border-yellow-400">
                    <p class="text-[10px] text-yellow-600 font-bold uppercase">Catatan Nasabah</p>
                    <p class="text-sm font-medium text-gray-700 italic">{{ selectedItem.deskripsi || "Tidak ada catatan" }}</p>
                </div>

                <!-- Rejection Reason Section -->
                <div v-if="selectedItem.status === 'tolak' && selectedItem.ket_status" class="bg-red-50 p-6 rounded-3xl border border-red-100">
                    <p class="text-[10px] text-red-500 font-bold uppercase">Alasan Ditolak</p>
                    <p class="text-sm font-bold text-red-600">{{ selectedItem.ket_status }}</p>
                </div>
                <!-- Cancellation Reason Section -->
                <div v-if="selectedItem.status === 'batal' && selectedItem.ket_status" class="bg-gray-50 p-6 rounded-3xl border border-gray-200">
                    <div class="flex items-center gap-3 mb-2">
                         <Icon icon="material-symbols:info-outline" class="w-5 h-5 text-gray-400" />
                         <span class="font-bold text-gray-700">Request Dibatalkan</span>
                    </div>
                    <p class="text-[10px] text-gray-400 font-bold uppercase">Alasan Pembatalan</p>
                    <p class="text-sm font-bold text-gray-600">{{ selectedItem.ket_status }}</p>
                </div>

                <!-- Informasi Penjemputan (Tukang) -->
                <div v-if="selectedItem.tukang" class="bg-gray-50 p-6 rounded-3xl space-y-4 border border-gray-100">
                    <div class="flex items-center gap-3 mb-2">
                        <Icon icon="material-symbols:local-shipping-outline" class="w-5 h-5 text-[#4A7043]" />
                        <span class="font-bold text-gray-700">Informasi Penjemputan</span>
                    </div>
                    <div class="flex flex-col md:flex-row gap-6">
                        <div class="flex items-center gap-4 bg-white p-4 rounded-2xl flex-1">
                            <div class="w-12 h-12 rounded-full bg-gray-200 overflow-hidden shrink-0">
                                <img v-if="selectedItem.tukang.foto" :src="`${axios.defaults.baseURL}/storage/${selectedItem.tukang.foto}`" class="w-full h-full object-cover" />
                                <Icon v-else icon="material-symbols:person" class="w-full h-full p-2 text-gray-400" />
                            </div>
                            <div class="text-xs">
                                <p class="text-gray-400">Tukang</p>
                                <p class="font-bold text-gray-700">{{ selectedItem.tukang.nama }}</p>
                                <p class="text-gray-400 mt-1">No. HP: <span class="font-bold text-gray-600">{{ selectedItem.tukang.no_hp || "-" }}</span></p>
                            </div>
                        </div>
                        <div class="text-sm text-right flex flex-col justify-center">
                            <p class="text-gray-400 text-[10px] font-bold uppercase">Jadwal Penjemputan:</p>
                            <p class="font-bold text-gray-700">{{ formatDate(selectedItem.jadwal) }}, {{ formatTime(selectedItem.jadwal) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PENIMBANGAN TAB -->
            <div v-else-if="modalActiveTab === 'penimbangan'" class="space-y-6">
                <!-- Data Transaksi -->
                <div class="bg-gray-50 p-6 rounded-3xl space-y-4 border border-gray-100">
                    <div class="flex items-center gap-3 mb-2">
                        <Icon icon="material-symbols:inventory-2-outline" class="w-5 h-5 text-[#4A7043]" />
                        <span class="font-bold text-gray-700">Informasi Transaksi</span>
                    </div>
                    <div class="flex flex-col gap-y-4 text-sm">
                        <div class="flex justify-between border-b border-gray-100 pb-2">
                             <span class="text-gray-400">ID Transaksi</span>
                             <span class="font-bold text-gray-700">{{ activeFilter === 'setor_manual' ? selectedItem.transaksi_id : (selectedItem.penimbangan?.[0]?.transaksi_id || "-") }}</span>
                        </div>
                        <div v-if="activeFilter === 'penjemputan'" class="flex justify-between border-b border-gray-100 pb-2">
                             <span class="text-gray-400">ID Penjemputan</span>
                             <span class="font-bold text-gray-700">REQ-{{ selectedItem.penjemputan_id }}</span>
                        </div>
                        <div class="flex justify-between border-b border-gray-100 pb-2">
                             <span class="text-gray-400">Gudang</span>
                             <span class="font-bold text-gray-700">{{ selectedItem.gudang?.nama || selectedItem.penimbangan?.[0]?.tukang?.gudang?.nama || "-" }}</span>
                        </div>
                        <div class="flex justify-between border-b border-gray-100 pb-2">
                             <span class="text-gray-400">Tanggal Selesai</span>
                             <span class="font-bold text-gray-700">{{ formatDate(selectedItem.updated_at) }}, {{ formatTime(selectedItem.updated_at) }}</span>
                        </div>
                        <div class="flex justify-between border-b border-gray-100 pb-2">
                             <span class="text-gray-400">Petugas Input</span>
                             <span class="font-bold text-gray-700">{{ selectedItem.petugas?.nama || selectedItem.penimbangan?.[0]?.transaksi?.petugas?.nama || "-" }}</span>
                        </div>
                        <div v-if="selectedItem.tukang || selectedItem.penimbangan?.[0]?.tukang" class="flex justify-between border-b border-gray-100 pb-2">
                             <span class="text-gray-400">Tukang</span>
                             <span class="font-bold text-gray-700">{{ selectedItem.tukang?.nama || selectedItem.penimbangan?.[0]?.tukang?.nama || "-" }}</span>
                        </div>
                    </div>
                </div>

                <!-- Informasi Nasabah -->
                <div class="bg-gray-50 p-6 rounded-3xl space-y-2 border border-gray-100">
                    <div class="flex items-center gap-3 mb-2">
                        <Icon icon="material-symbols:person-outline" class="w-5 h-5 text-[#4A7043]" />
                        <span class="font-bold text-gray-700">Informasi Nasabah</span>
                    </div>
                    <p class="text-xs text-gray-400">Nama Nasabah</p>
                    <p class="font-bold text-gray-700 text-sm">{{ selectedItem.nasabah?.nama || selectedItem.penimbangan?.[0]?.nasabah?.nama }}</p>
                    <p class="text-xs text-gray-400 mt-2">ID Nasabah</p>
                    <p class="font-bold text-gray-700 text-sm">NSB-{{ selectedItem.nasabah?.nasabah_id || selectedItem.penimbangan?.[0]?.nasabah?.nasabah_id }}</p>
                </div>

                <!-- Foto Sampah -->
                <div v-if="selectedItem.penimbangan?.[0]?.foto" class="space-y-4">
                     <div class="flex items-center gap-3">
                        <Icon icon="material-symbols:camera-alt-outline" class="w-5 h-5 text-[#4A7043]" />
                        <span class="font-bold text-gray-700">Foto Sampah</span>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                         <div v-for="(p, idx) in selectedItem.penimbangan" :key="idx" class="aspect-square rounded-2xl overflow-hidden border border-gray-100">
                             <img :src="`${axios.defaults.baseURL}/storage/${p.foto}`" class="w-full h-full object-cover" />
                         </div>
                    </div>
                </div>

                <!-- Detail Penimbangan -->
                <div class="bg-gray-50 p-6 rounded-3xl space-y-4 border border-gray-100">
                    <div class="flex items-center gap-3 mb-2">
                        <Icon icon="material-symbols:balance" class="w-5 h-5 text-[#4A7043]" />
                        <span class="font-bold text-gray-700">Detail Sampah</span>
                    </div>
                    <div class="space-y-4">
                        <div v-for="(p, idx) in selectedItem.penimbangan" :key="idx" class="flex justify-between items-center text-sm">
                            <div>
                                <p class="font-bold text-gray-700">{{ p.sampah?.item_sampah?.nama }}</p>
                                <p class="text-[10px] text-gray-400">{{ p.berat_timbang }} kg x {{ formatRupiah(p.sampah?.item_sampah?.harga_beli || 0) }}</p>
                            </div>
                            <span class="font-bold text-gray-700">{{ formatRupiah(parseFloat(p.berat_timbang || 0) * (p.sampah?.item_sampah?.harga_beli || 0)) }}</span>
                        </div>
                        <div class="pt-4 border-t border-gray-200 flex justify-between items-center">
                            <span class="text-gray-400 font-bold">Total Berat</span>
                            <span class="font-black text-gray-700">{{ calculateTotalWeight(selectedItem.penimbangan) }} kg</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-400 font-bold">Total Nilai</span>
                            <span class="font-black text-2xl text-[#4A7043]">{{ formatRupiah(calculateTotalValue(selectedItem.penimbangan)) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Informasi Saldo -->
                <div v-if="selectedItem.penimbangan?.[0]?.transaksi" class="bg-green-50 p-6 rounded-3xl space-y-4 border border-green-100">
                     <div class="flex items-center gap-3 mb-2">
                        <Icon icon="material-symbols:account-balance-wallet-outline" class="w-5 h-5 text-[#4A7043]" />
                        <span class="font-bold text-gray-700">Informasi Saldo</span>
                    </div>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-400 font-medium">Saldo Sebelum</span>
                            <span class="font-bold text-gray-600">{{ formatRupiah(selectedItem.penimbangan[0].transaksi.saldo_sebelum || 0) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-green-600 font-bold">Nilai Tambahan</span>
                            <span class="font-black text-green-600">+ {{ formatRupiah(calculateTotalValue(selectedItem.penimbangan)) }}</span>
                        </div>
                        <div class="pt-3 border-t border-green-200 flex justify-between items-center">
                            <span class="font-black text-gray-700">Saldo Sesudah</span>
                            <span class="font-black text-lg text-gray-800">{{ formatRupiah(parseFloat(selectedItem.penimbangan[0].transaksi.saldo_sebelum || 0) + calculateTotalValue(selectedItem.penimbangan)) }}</span>
                        </div>
                    </div>
                </div>
            </div>
          </div>
        </div>

        <!-- Modal Footer -->
        <div class="p-6 bg-gray-50 flex justify-center">
          <button
            @click="closeModal"
            class="w-full py-4 bg-[#4A7043] text-white rounded-2xl font-bold hover:bg-[#3d5c37] transition-all"
          >
            Tutup
          </button>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #e5e7eb;
  border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #d1d5db;
}
</style>

