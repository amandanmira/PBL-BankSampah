<script setup>
import { ref, onMounted, onUnmounted, computed, inject, watch } from "vue";
import DashboardLayout from "@/layouts/DashboardLayout.vue";
import { Icon } from "@iconify/vue";
import { checkRole } from "@/utils";

// Hak akses Petugas
checkRole("petugas");

const axios = inject("axios");
const user = ref(JSON.parse(sessionStorage.getItem("user") || "{}"));

// URL Server Production untuk Gambar
const STORAGE_URL = "https://api.tabungansampah.online/storage";

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

    const perPage = window.innerWidth < 768 ? 3 : 10;
    const params = {
      page: page,
      search: searchQuery.value,
      per_page: perPage,
    };

    if (activeFilter.value === "setor_manual") {
      params.gudang_id = user.value.gudang_id;
    }

    const response = await axios.get(endpoint, {
      params: params,
    });

    // Array status yang diperbolehkan tampil di Riwayat
    const allowedStatuses = ['selesai', 'tolak', 'batal', 'jadwal_ditolak'];

    if (activeFilter.value === "penarikan") {
        const rawPenarikan = response.data.penarikan.data || [];
        historyData.value = rawPenarikan.filter(item => 
            allowedStatuses.includes(item.status?.toLowerCase())
        );
        
        pagination.value = {
            current_page: response.data.penarikan.current_page,
            last_page: response.data.penarikan.last_page,
            total: response.data.penarikan.total,
        };
    } else {
        const rawData = response.data.data || [];
        historyData.value = rawData.filter(item => {
            return allowedStatuses.includes(item.status?.toLowerCase());
        });
        console.log(historyData.value)
        
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
  modalActiveTab.value = activeFilter.value === 'penarikan' ? 'penarikan' : (activeFilter.value === 'setor_manual' ? 'penimbangan' : 'penjemputan');
  
  try {
    let endpoint = "";
    
    if (activeFilter.value === 'penjemputan') {
        endpoint = `/api/petugas/riwayat-penjemputan/${item.penjemputan_id}`;
    } else if (activeFilter.value === 'setor_manual') {
        endpoint = `/api/petugas/riwayat-setor-manual/${item.transaksi_id}`;
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

// Edit Modal State
const showEditModal = ref(false);
const loadingEdit = ref(false);
const isSubmitting = ref(false);
const editItems = ref([]);
const masterSampah = ref([]);

const openEditModal = async () => {
  showModal.value = false; // Tutup modal detail dulu
  showEditModal.value = true;
  loadingEdit.value = true;

  // Ambil data master sampah jika belum ada
  if (masterSampah.value.length === 0) {
    try {
      const response = await axios.get('/api/petugas/list-sampah');
      masterSampah.value = response.data.data;
    } catch (err) {
      console.error("Gagal mengambil master sampah:", err);
    }
  }

  // Salin data item untuk diedit
  let itemsToEdit = [];
  if (activeFilter.value === 'penjemputan' || activeFilter.value === 'setor_manual') {
      itemsToEdit = selectedItem.value?.penimbangan || [];
  }
  
  editItems.value = JSON.parse(JSON.stringify(itemsToEdit));
  
  loadingEdit.value = false;
};

const closeEditModal = () => {
  showEditModal.value = false;
  editItems.value = [];
};

const addItem = () => {
  if (masterSampah.value.length > 0) {
    editItems.value.push({
      sampah_id: masterSampah.value[0].sampah_id,
      berat_timbang: 0.1,
      sampah: {
          item_sampah: {
              nama: masterSampah.value[0].item_sampah.nama
          }
      }
    });
  }
};

const removeItem = (index) => {
  editItems.value.splice(index, 1);
};

const submitEdit = async () => {
    isSubmitting.value = true;
    try {
        const payload = {
            items: editItems.value.map(item => ({
                sampah_id: item.sampah_id,
                berat_timbang: item.berat_timbang,
            })),
        };

        let transaksi_id;
        if (activeFilter.value === 'penjemputan') {
            transaksi_id = selectedItem.value?.penimbangan?.[0]?.transaksi_id;
        } else if (activeFilter.value === 'setor_manual') {
            transaksi_id = selectedItem.value?.transaksi_id;
        }

        if (!transaksi_id) {
            throw new Error("ID Transaksi tidak ditemukan.");
        }

        await axios.put(`/api/petugas/penimbangan/${transaksi_id}`, payload);

        closeEditModal();
        fetchHistory(pagination.value.current_page); // Refresh list
        
    } catch (error) {
        console.error("Gagal update penimbangan:", error);
        if(error.response && error.response.data && error.response.data.message){
             alert(error.response.data.message);
        }
    } finally {
        isSubmitting.value = false;
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

// --- LOGIKA TIMER DINAMIS ---
const currentTime = ref(new Date());
let timerInterval = null;
const batasWaktuEdit = ref(12);

const fetchConfigWeb = async () => {
  try {
    const res = await axios.get('/api/web-config');
    if (res.data && res.data.batas_waktu_edit) {
      batasWaktuEdit.value = res.data.batas_waktu_edit;
    }
  } catch (err) {
    console.error("Gagal mengambil konfigurasi web:", err);
  }
};

onMounted(() => {
  fetchHistory();
  fetchConfigWeb();
  
  timerInterval = setInterval(() => {
    currentTime.value = new Date();
  }, 1000); 
});

onUnmounted(() => {
  if (timerInterval) clearInterval(timerInterval);
});

const isEditable = (item) => {
    if (!item) return false;
    const dateString = item.updated_at || item.created_at;
    if (!dateString) return false;

    const transactionDate = new Date(dateString);
    const diffInHours = (currentTime.value - transactionDate) / (1000 * 60 * 60);
    
    return diffInHours <= batasWaktuEdit.value; 
};

const remainingTimeText = computed(() => {
    if (!selectedItem.value) return "";
    
    const dateString = selectedItem.value.updated_at || selectedItem.value.created_at;
    if (!dateString) return "";

    const transactionDate = new Date(dateString);
    const deadline = new Date(transactionDate.getTime() + (batasWaktuEdit.value * 60 * 60 * 1000)); 
    const diffMs = deadline - currentTime.value;

    if (diffMs <= 0) return "Waktu Habis";

    const hours = Math.floor(diffMs / (1000 * 60 * 60));
    const minutes = Math.floor((diffMs % (1000 * 60 * 60)) / (1000 * 60));
    
    return `${hours} jam ${minutes} menit`;
});
</script>

<template>
  <DashboardLayout title="Riwayat Transaksi">
    <div class="space-y-6">
      <div>
        <p class="text-gray-500 text-sm">Lihat semua riwayat transaksi penjemputan, setor manual, dan penarikan yang sudah selesai</p>
      </div>

      <div class="bg-white p-6 rounded-3xl shadow-sm space-y-4 border border-gray-100">
        <div class="flex flex-col md:flex-row gap-4">
          <div class="flex-1 relative">
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Cari ID Transaksi atau Nama Nasabah"
              class="w-full pl-5 pr-11 py-2.5 sm:py-4 bg-gray-50 border-none rounded-xl sm:rounded-2xl focus:ring-2 focus:ring-[#4A7043] transition-all text-xs sm:text-sm"
            />
            <div class="absolute right-3 sm:right-4 top-1/2 -translate-y-1/2 bg-[#4A7043] p-1.5 sm:p-2 rounded-lg sm:rounded-xl text-white">
              <Icon icon="material-symbols:search" class="w-4 h-4 sm:w-5 sm:h-5" />
            </div>
          </div>
        </div>

        <div class="flex flex-wrap gap-1.5 sm:gap-2">
          <button
            v-for="filter in filters"
            :key="filter.id"
            @click="activeFilter = filter.id"
            :class="[
              'px-3 py-2 sm:px-6 sm:py-3 rounded-xl sm:rounded-2xl flex items-center gap-2 sm:gap-3 transition-all text-xs sm:text-sm font-semibold border cursor-pointer',
              activeFilter === filter.id
                ? 'bg-[#4A7043] text-white border-[#4A7043] shadow-lg shadow-[#4A7043]/20'
                : 'bg-white text-gray-500 border-gray-200 hover:border-[#4A7043] hover:text-[#4A7043]'
            ]"
          >
            <Icon :icon="filter.icon" class="w-4.5 h-4.5 sm:w-5 sm:h-5 shrink-0" />
            <span>
              <span class="hidden sm:inline">{{ filter.label }}</span>
              <span class="inline sm:hidden">{{ filter.label.replace('Riwayat ', '') }}</span>
            </span>
          </button>
        </div>
      </div>

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
          class="bg-white rounded-2xl sm:rounded-3xl p-4 sm:p-6 border border-gray-100 hover:shadow-xl transition-all group relative overflow-hidden"
          :class="{'border-l-4 border-l-[#4A7043]': activeFilter === 'penjemputan' && item.status === 'selesai'}"
        >
          <div class="flex flex-col md:flex-row justify-between gap-4 md:gap-6">
            <div class="space-y-2.5 sm:space-y-3 flex-1">
              <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                <span class="text-sm sm:text-lg font-bold text-gray-800">
                    {{ (activeFilter === 'penjemputan' ? 'REQ-' : '') + (activeFilter === 'penjemputan' ? item.penjemputan_id : (activeFilter === 'setor_manual' ? item.transaksi_id : item.penarikan_id)) }}
                </span>
                <span :class="['px-2.5 py-0.5 sm:px-3 sm:py-1 rounded-full text-[9px] sm:text-[10px] font-bold uppercase tracking-wider', getStatusColor(item.status)]">
                  {{ getStatusLabel(item.status) }}
                </span>
                <span class="px-2.5 py-0.5 sm:px-3 sm:py-1 rounded-full bg-gray-100 text-gray-500 text-[9px] sm:text-[10px] font-bold uppercase tracking-wider">
                  {{ activeFilter === 'penjemputan' ? 'Penjemputan' : (activeFilter === 'setor_manual' ? 'Setor Manual' : 'Penarikan') }}
                </span>
              </div>
              
              <div>
                <h3 class="text-sm sm:text-xl font-black text-gray-800 leading-tight">
                    {{ item.nasabah?.nama || item.penimbangan?.[0]?.nasabah?.nama || "N/A" }} 
                    <span class="text-gray-400 font-medium text-xs">(NSB-{{ item.nasabah?.nasabah_id || item.penimbangan?.[0]?.nasabah?.nasabah_id || "-" }})</span>
                </h3>
                <p class="text-gray-400 text-[10px] sm:text-xs font-medium mt-0.5">{{ formatDate(item.created_at) }}</p>
              </div>

              <div v-if="activeFilter !== 'penarikan'" class="grid grid-cols-2 md:grid-cols-3 gap-2.5 sm:gap-4 bg-gray-50 p-3 sm:p-4 rounded-xl sm:rounded-2xl mt-2 sm:mt-4">
                <div>
                  <p class="text-[9px] sm:text-[10px] text-gray-400 font-bold uppercase">Berat Actual</p>
                  <p class="text-xs sm:text-sm font-bold text-gray-700">
                    {{ activeFilter === 'penjemputan' ? (calculateTotalWeight(item.penimbangan) || "-") : calculateTotalWeight(item.penimbangan) }} kg
                  </p>
                </div>
                <div>
                  <p class="text-[9px] sm:text-[10px] text-gray-400 font-bold uppercase">Jenis Sampah</p>
                  <p class="text-xs sm:text-sm font-bold text-gray-700 truncate max-w-[120px] sm:max-w-[150px]">{{ getWasteTypes(item) }}</p>
                </div>
                <div v-if="activeFilter === 'penjemputan'">
                  <p class="text-[9px] sm:text-[10px] text-gray-400 font-bold uppercase">Tukang</p>
                  <p class="text-xs sm:text-sm font-bold text-gray-700">{{ item.tukang?.nama || "-" }}</p>
                </div>
                <div v-if="activeFilter === 'setor_manual'">
                  <p class="text-[9px] sm:text-[10px] text-gray-400 font-bold uppercase">Petugas</p>
                  <p class="text-xs sm:text-sm font-bold text-gray-700">{{ item.petugas?.nama || "-" }}</p>
                </div>
              </div>

              <div v-if="(item.status === 'tolak' || item.status === 'batal') && item.ket_status" class="bg-red-50 p-3 sm:p-4 rounded-xl sm:rounded-2xl mt-2 sm:mt-4 border border-red-100">
                <p class="text-[9px] sm:text-[10px] text-red-400 font-bold uppercase">{{ item.status === 'tolak' ? 'Alasan Ditolak' : 'Alasan Dibatalkan' }}</p>
                <p class="text-xs sm:text-sm font-bold text-red-600">{{ item.ket_status }}</p>
              </div>
            </div>

            <div class="flex flex-col items-start md:items-end justify-between mt-2 md:mt-0">
              <div class="text-left md:text-right">
                <p class="text-[9px] sm:text-[10px] text-gray-400 font-bold uppercase">{{ activeFilter === 'penarikan' ? 'Jumlah Penarikan' : 'Total Nilai' }}</p>
                <p class="text-lg sm:text-2xl font-black text-[#4A7043]">
                    {{ activeFilter === 'penarikan' ? formatRupiah(item.jumlah) : (item.penimbangan?.length > 0 ? formatRupiah(calculateTotalValue(item.penimbangan)) : "-") }}
                </p>
              </div>
            </div>
          </div>

          <button
            @click="openDetail(item)"
            class="w-full mt-4 sm:mt-6 py-2.5 sm:py-3 bg-[#4A7043] text-white rounded-xl sm:rounded-2xl font-bold flex items-center justify-center gap-2 hover:bg-[#3d5c37] transition-all cursor-pointer text-xs sm:text-sm"
          >
            <Icon icon="material-symbols:visibility-outline" class="w-4 h-4 sm:w-5 sm:h-5" />
            Lihat Detail
          </button>
        </div>
      </div>

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

    <div v-if="showModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
      <div class="bg-white w-full max-w-2xl rounded-[2.5rem] overflow-hidden shadow-2xl animate-in zoom-in duration-300 flex flex-col max-h-[90vh]">
        <div class="bg-[#4A7043] p-6 text-white flex justify-between items-center relative">
          <div>
            <h2 class="text-xl font-bold">Detail Transaksi</h2>
            <p class="text-white/60 text-xs font-medium">{{ (activeFilter === 'penjemputan' ? 'REQ-' : '') + (selectedItem?.penjemputan_id || selectedItem?.transaksi_id || selectedItem?.penarikan_id) }}</p>
          </div>
          <button @click="closeModal" class="p-2 hover:bg-white/10 rounded-full transition-all">
            <Icon icon="material-symbols:close" class="w-6 h-6" />
          </button>
        </div>

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

        <div class="flex-1 overflow-y-auto p-6 space-y-6 custom-scrollbar">
          <div v-if="loadingDetail" class="flex justify-center py-10">
             <div class="animate-spin rounded-full h-8 w-8 border-3 border-[#4A7043] border-t-transparent"></div>
          </div>
          
          <div v-else-if="selectedItem">
            <div v-if="activeFilter === 'penarikan'" class="space-y-6">
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
                        <div v-if="selectedItem.petugas">
                            <p class="text-gray-400 text-[10px] font-bold uppercase">Petugas Pemroses</p>
                            <p class="font-bold text-gray-700">{{ selectedItem.petugas?.nama }}</p>
                        </div>
                        <div v-if="selectedItem.petugas?.gudang">
                            <p class="text-gray-400 text-[10px] font-bold uppercase">Asal Gudang</p>
                            <p class="font-bold text-gray-700">{{ selectedItem.petugas?.gudang?.alamat }}</p>
                        </div>
                    </div>
                </div>

                <div v-if="selectedItem.bukti_tf" class="space-y-4">
                    <div class="flex items-center gap-3">
                        <Icon icon="material-symbols:image-outline" class="w-5 h-5 text-[#4A7043]" />
                        <span class="font-bold text-gray-700">Bukti Transfer</span>
                    </div>
                    <div class="aspect-video w-full rounded-3xl overflow-hidden border border-gray-200">
                        <img :src="`${STORAGE_URL}/${selectedItem.bukti_tf}`" class="w-full h-full object-cover" />
                    </div>
                </div>

                <div v-if="selectedItem.status === 'tolak' && selectedItem.ket_status" class="bg-red-50 p-6 rounded-3xl border border-red-100">
                    <p class="text-red-500 font-bold text-sm mb-1">Alasan Penolakan:</p>
                    <p class="text-red-700 font-medium text-sm">{{ selectedItem.ket_status }}</p>
                </div>
            </div>

            <div v-else-if="modalActiveTab === 'penjemputan'" class="space-y-6">
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
                                    {{ (selectedItem.nasabah?.no_telp || selectedItem.penimbangan?.[0]?.nasabah?.no_telp) || "Nasabah belum mengatur nomor telepon" }}
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

                <div v-if="selectedItem.foto && selectedItem.foto.length > 0" class="space-y-4">
                    <p class="text-sm font-bold text-gray-700">Foto Sampah dari Nasabah</p>
                    <div class="grid grid-cols-3 gap-2">
                        <div v-for="(img, idx) in selectedItem.foto" :key="idx" class="aspect-square rounded-2xl overflow-hidden border border-gray-100">
                            <img :src="`${STORAGE_URL}/${img}`" class="w-full h-full object-cover" />
                        </div>
                    </div>
                </div>

                <div class="bg-yellow-50 p-6 rounded-3xl border-l-4 border-yellow-400">
                    <p class="text-[10px] text-yellow-600 font-bold uppercase">Catatan Nasabah</p>
                    <p class="text-sm font-medium text-gray-700 italic">{{ selectedItem.deskripsi?.includes('|') ? (selectedItem.deskripsi.split('|')[2] || "Tidak ada catatan") : (selectedItem.deskripsi || "Tidak ada catatan") }}</p>
                </div>

                <div v-if="selectedItem.status === 'tolak' && selectedItem.ket_status" class="bg-red-50 p-6 rounded-3xl border border-red-100">
                    <p class="text-[10px] text-red-500 font-bold uppercase">Alasan Ditolak</p>
                    <p class="text-sm font-bold text-red-600">{{ selectedItem.ket_status }}</p>
                </div>
                <div v-if="selectedItem.status === 'batal' && selectedItem.ket_status" class="bg-gray-50 p-6 rounded-3xl border border-gray-200">
                    <div class="flex items-center gap-3 mb-2">
                         <Icon icon="material-symbols:info-outline" class="w-5 h-5 text-gray-400" />
                         <span class="font-bold text-gray-700">Request Dibatalkan</span>
                    </div>
                    <p class="text-[10px] text-gray-400 font-bold uppercase">Alasan Pembatalan</p>
                    <p class="text-sm font-bold text-gray-600">{{ selectedItem.ket_status }}</p>
                </div>

                <div v-if="selectedItem.tukang" class="bg-gray-50 p-6 rounded-3xl space-y-4 border border-gray-100">
                    <div class="flex items-center gap-3 mb-2">
                        <Icon icon="material-symbols:local-shipping-outline" class="w-5 h-5 text-[#4A7043]" />
                        <span class="font-bold text-gray-700">Informasi Penjemputan</span>
                    </div>
                    <div class="flex flex-col md:flex-row gap-6">
                        <div class="flex items-center gap-4 bg-white p-4 rounded-2xl flex-1">
                            <div class="w-12 h-12 rounded-full bg-gray-200 overflow-hidden shrink-0">
                                <img v-if="selectedItem.tukang.foto" :src="`${STORAGE_URL}/${selectedItem.tukang.foto}`" class="w-full h-full object-cover" />
                                <Icon v-else icon="material-symbols:person" class="w-full h-full p-2 text-gray-400" />
                            </div>
                            <div class="text-xs">
                                <p class="text-gray-400">Tukang</p>
                                <p class="font-bold text-gray-700">{{ selectedItem.tukang.nama }}</p>
                                <p class="text-gray-400 mt-1">No. HP: <span class="font-bold text-gray-600">{{ selectedItem.tukang.no_telp || "-" }}</span></p>
                            </div>
                        </div>
                        <div class="text-sm text-right flex flex-col justify-center">
                            <p class="text-gray-400 text-[10px] font-bold uppercase">Jadwal Penjemputan:</p>
                            <p class="font-bold text-gray-700">{{ formatDate(selectedItem.jadwal) }}, {{ formatTime(selectedItem.jadwal) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div v-else-if="modalActiveTab === 'penimbangan'" class="space-y-6">
                <div class="bg-gray-50 p-6 rounded-3xl space-y-4 border border-gray-100">
                    <div class="flex items-center gap-3 mb-2">
                        <Icon icon="material-symbols:inventory-2-outline" class="w-5 h-5 text-[#4A7043]" />
                        <span class="font-bold text-gray-700">Informasi Transaksi</span>
                    </div>
                    <div class="flex flex-col gap-y-4 text-sm">
                        <div class="flex justify-between border-b border-gray-100 pb-2">
                             <span class="text-gray-400">ID Transaksi</span>
                             <span class="font-bold text-gray-700">{{ activeFilter === 'setor_manual' ? 'TR-' + String(selectedItem.transaksi_id).padStart(3, '0') : (selectedItem.penimbangan?.[0]?.transaksi_id ? 'TR-' + String(selectedItem.penimbangan[0].transaksi_id).padStart(3, '0') : "-") }}</span>
                        </div>
                        <div v-if="activeFilter === 'penjemputan'" class="flex justify-between border-b border-gray-100 pb-2">
                             <span class="text-gray-400">ID Penjemputan</span>
                             <span class="font-bold text-gray-700">REQ-{{ selectedItem.penjemputan_id }}</span>
                        </div>
                        <div class="flex justify-between border-b border-gray-100 pb-2">
                             <span class="text-gray-400">Gudang</span>
                             <span class="font-bold text-gray-700">{{ selectedItem.gudang?.alamat || selectedItem.penimbangan?.[0]?.tukang?.gudang?.alamat || "-" }}</span>
                        </div>
                        <div class="flex justify-between border-b border-gray-100 pb-2">
                             <span class="text-gray-400">Tanggal Selesai</span>
                             <span class="font-bold text-gray-700">{{ formatDate(selectedItem.updated_at) }}, {{ formatTime(selectedItem.updated_at) }}</span>
                        </div>
                        <div class="flex justify-between border-b border-gray-100 pb-2">
                             <span class="text-gray-400">Petugas Input</span>
                             <span class="font-bold text-gray-700">{{ selectedItem.penimbangan?.[0]?.transaksi?.petugas?.nama || selectedItem.petugas?.nama || "-" }}</span>
                        </div>
                        <div v-if="selectedItem.tukang || selectedItem.penimbangan?.[0]?.tukang" class="flex justify-between border-b border-gray-100 pb-2">
                             <span class="text-gray-400">Tukang</span>
                             <span class="font-bold text-gray-700">{{ selectedItem.tukang?.nama || selectedItem.penimbangan?.[0]?.tukang?.nama || "-" }}</span>
                        </div>
                    </div>
                </div>

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

                <div v-if="selectedItem.penimbangan?.[0]?.foto" class="space-y-4">
                     <div class="flex items-center gap-3">
                        <Icon icon="material-symbols:camera-alt-outline" class="w-5 h-5 text-[#4A7043]" />
                        <span class="font-bold text-gray-700">Foto Sampah</span>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                         <div v-for="(p, idx) in selectedItem.penimbangan" :key="idx" class="aspect-square rounded-2xl overflow-hidden border border-gray-100">
                             <img :src="`${STORAGE_URL}/${p.foto}`" class="w-full h-full object-cover" />
                         </div>
                    </div>
                </div>

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

        <div class="p-6 bg-gray-50 flex justify-center gap-4">
          <button
            @click="closeModal"
            class="w-full py-4 bg-gray-200 text-gray-700 rounded-2xl font-bold hover:bg-gray-300 transition-all"
          >
            Tutup
          </button>
          
          <button
            v-if="(activeFilter === 'penjemputan' || activeFilter === 'setor_manual') && modalActiveTab === 'penimbangan' && selectedItem?.status === 'selesai' && isEditable(selectedItem)"
            @click="openEditModal"
            class="w-full py-2 bg-[#4A7043] text-white rounded-2xl hover:bg-[#3d5c37] transition-all flex flex-col items-center justify-center gap-0.5"
          >
            <span class="font-bold">Edit Penimbangan</span>
            <span class="text-[10px] font-medium bg-white/20 px-2.5 py-0.5 rounded-full flex items-center gap-1 shadow-sm mt-0.5">
              <Icon icon="material-symbols:timer-outline" class="w-3 h-3" />
              Sisa Waktu: {{ remainingTimeText }}
            </span>
          </button>
        </div>
      </div>
    </div>

    <div v-if="showEditModal" class="fixed inset-0 z-[101] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
        <div class="bg-white w-full max-w-2xl rounded-[2.5rem] overflow-hidden shadow-2xl animate-in zoom-in duration-300 flex flex-col max-h-[90vh]">
            <div class="bg-[#4A7043] p-6 text-white flex justify-between items-center">
                <div>
                    <h2 class="text-xl font-bold">Edit Penimbangan</h2>
                    <p class="text-white/60 text-xs font-medium">TR-{{ String(selectedItem?.transaksi_id || selectedItem?.penimbangan?.[0]?.transaksi_id || '0').padStart(3, '0') }}</p>
                </div>
                <button @click="closeEditModal" class="p-2 hover:bg-white/10 rounded-full transition-all">
                    <Icon icon="material-symbols:close" class="w-6 h-6" />
                </button>
            </div>

            <div class="flex-1 overflow-y-auto p-6 space-y-6 custom-scrollbar">
                <div v-if="loadingEdit" class="flex justify-center py-10">
                    <div class="animate-spin rounded-full h-8 w-8 border-3 border-[#4A7043] border-t-transparent"></div>
                </div>
                <div v-else>
                    <div class="space-y-4">
                        <div v-for="(item, index) in editItems" :key="index" class="flex items-center gap-4 bg-gray-50 p-4 rounded-2xl">
                            <div class="flex-1 grid grid-cols-2 gap-4">
                                <div>
                                    <label class="text-xs text-gray-500">Sampah</label>
                                    <select v-model="item.sampah_id" class="w-full p-2 border rounded-lg text-sm">
                                        <option v-for="sampah in masterSampah" :key="sampah.sampah_id" :value="sampah.sampah_id">
                                            {{ sampah.item_sampah.nama }}
                                        </option>
                                    </select>
                                </div>
                                <div>
                                    <label class="text-xs text-gray-500">Berat (kg)</label>
                                    <input type="number" v-model="item.berat_timbang" class="w-full p-2 border rounded-lg text-sm" />
                                </div>
                            </div>
                            <button @click="removeItem(index)" class="p-2 text-red-500 hover:bg-red-100 rounded-full">
                                <Icon icon="material-symbols:delete-outline" class="w-5 h-5" />
                            </button>
                        </div>
                    </div>
                    <button @click="addItem" class="mt-4 w-full py-3 bg-green-100 text-[#4A7043] rounded-2xl font-bold flex items-center justify-center gap-2 hover:bg-green-200 transition-all">
                        <Icon icon="material-symbols:add" class="w-5 h-5" />
                        Tambah Item
                    </button>
                </div>
            </div>

            <div class="p-6 bg-gray-50 flex justify-center gap-4">
                <button @click="closeEditModal" class="w-full py-4 bg-gray-200 text-gray-700 rounded-2xl font-bold hover:bg-gray-300 transition-all">
                    Batal
                </button>
                <button @click="submitEdit" class="w-full py-4 bg-[#4A7043] text-white rounded-2xl font-bold hover:bg-[#3D5C37] transition-all" :disabled="isSubmitting">
                    <span v-if="isSubmitting" class="flex items-center justify-center">
                        <div class="animate-spin rounded-full h-5 w-5 border-2 border-white border-t-transparent mr-2"></div>
                        Menyimpan...
                    </span>
                    <span v-else>Simpan Perubahan</span>
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