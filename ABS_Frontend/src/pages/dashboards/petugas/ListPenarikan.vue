<script setup>
import { ref, onMounted, computed, inject } from "vue";
import { Icon } from "@iconify/vue";
import DashboardLayout from "@/layouts/DashboardLayout.vue";
import { checkRole } from "@/utils";
import Swal from "sweetalert2";

// Security check
checkRole("petugas");

const axios = inject('axios');

// State
const penarikans = ref([]);
const pagination = ref({});
const loading = ref(false);
const searchQuery = ref("");
const activeFilter = ref("semua"); // semua, menunggu, selesai, ditolak
const isSubmitting = ref(false);

const todayTotal = ref(0);
const dailyLimit = ref(5000000);

// Computed for remaining limit
const remainingLimit = computed(() => dailyLimit.value - todayTotal.value);

// Modal States
const isDetailModalOpen = ref(false);
const isApproveModalOpen = ref(false);
const isRejectModalOpen = ref(false);
const selectedPenarikan = ref(null);
const rejectReason = ref("");
const buktiFile = ref(null);

// Utils
const formatRupiah = (angka) => {
  return new Intl.NumberFormat("id-ID", {
    style: "currency",
    currency: "IDR",
    minimumFractionDigits: 0,
  }).format(angka || 0);
};

const formatDate = (dateStr) => {
  if (!dateStr) return "-";
  return new Date(dateStr).toLocaleDateString("id-ID", {
    day: "numeric",
    month: "short",
    year: "numeric",
  });
};

const formatFullDate = (dateStr) => {
  if (!dateStr) return "-";
  return new Date(dateStr).toLocaleDateString("id-ID", {
    day: "numeric",
    month: "short",
    year: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  });
};

// Fetch data
const fetchData = async (page = 1) => {
  loading.value = true;
  try {
    const res = await axios.get("/api/petugas/penarikan", {
      params: {
        page,
        status: activeFilter.value,
        search: searchQuery.value,
      },
    });
    const data = res.data.penarikan;
    penarikans.value = data.data;
    pagination.value = {
      current_page: data.current_page,
      last_page: data.last_page,
      total: data.total,
      per_page: data.per_page,
    };
    todayTotal.value = res.data.today_total || 0;
    dailyLimit.value = res.data.daily_limit || 5000000;
  } catch (err) {
    console.error("Failed to fetch penarikan:", err);
  } finally {
    loading.value = false;
  }
};

// Actions
const setFilter = (filter) => {
  activeFilter.value = filter;
  fetchData(1);
};

const handleSearch = () => {
  fetchData(1);
};

const openDetail = (item) => {
  selectedPenarikan.value = item;
  isDetailModalOpen.value = true;
};

const openApprove = (item) => {
  selectedPenarikan.value = item;
  isApproveModalOpen.value = true;
};

const openReject = (item) => {
  selectedPenarikan.value = item;
  isRejectModalOpen.value = true;
};

const handleFileUpload = (event) => {
  buktiFile.value = event.target.files[0];
};

const closeAllModals = () => {
  isDetailModalOpen.value = false;
  isApproveModalOpen.value = false;
  isRejectModalOpen.value = false;
  selectedPenarikan.value = null;
  rejectReason.value = "";
  buktiFile.value = null;
};

const confirmApprove = async () => {
  if (!buktiFile.value) {
    Swal.fire("Peringatan", "Bukti transfer wajib diunggah", "warning");
    return;
  }

  isSubmitting.value = true;
  const formData = new FormData();
  formData.append("bukti_tf", buktiFile.value);
  formData.append("_method", "PUT");

  try {
    await axios.post(`/api/petugas/penarikan/${selectedPenarikan.value.penarikan_id}/terima`, formData, {
      headers: { "Content-Type": "multipart/form-data" },
    });

    Swal.fire("Berhasil", "Request penarikan telah disetujui", "success");
    closeAllModals();
    fetchData(pagination.value.current_page);
  } catch (err) {
    console.error(err);
    Swal.fire("Gagal", err.response?.data?.message || "Terjadi kesalahan", "error");
  } finally {
    isSubmitting.value = false;
  }
};

const confirmReject = async () => {
  if (!rejectReason.value.trim()) {
    Swal.fire("Peringatan", "Alasan penolakan wajib diisi", "warning");
    return;
  }

  isSubmitting.value = true;
  try {
    await axios.put(`/api/petugas/penarikan/${selectedPenarikan.value.penarikan_id}/tolak`, {
      ket_status: rejectReason.value,
    });

    Swal.fire("Berhasil", "Request penarikan telah ditolak", "success");
    closeAllModals();
    fetchData(pagination.value.current_page);
  } catch (err) {
    console.error(err);
    Swal.fire("Gagal", "Terjadi kesalahan saat menolak request", "error");
  } finally {
    isSubmitting.value = false;
  }
};

onMounted(() => {
  fetchData();
});
</script>

<template>
  <DashboardLayout title="Request Penarikan">
    <div class="space-y-6 animate-in fade-in duration-700 pb-10">
      <!-- Header Section -->
      <div class="flex justify-between items-start">
        <div>
          <h2 class="text-2xl font-black text-stone-800">Request Penarikan</h2>
          <p class="text-stone-500 font-medium">Kelola request penarikan saldo dari nasabah secara terpusat dan efisien.</p>
        </div>
      </div>

      <!-- Filter Category Tabs -->
      <div class="bg-white rounded-2xl p-2 shadow-sm border border-stone-100 flex overflow-x-auto no-scrollbar">
        <button 
          v-for="tab in [
            { id: 'semua', label: 'Semua Request' },
            { id: 'menunggu', label: 'Menunggu' },
            { id: 'selesai', label: 'Selesai' },
            { id: 'ditolak', label: 'Ditolak/Batal' }
          ]" 
          :key="tab.id"
          @click="setFilter(tab.id)"
          :class="[
            'flex-1 min-w-[150px] py-3.5 rounded-xl text-sm font-bold transition-all whitespace-nowrap',
            activeFilter === tab.id ? 'bg-[#4A7043] text-white shadow-md' : 'text-stone-400 hover:bg-stone-50'
          ]"
        >
          {{ tab.label }}
        </button>
      </div>

      <!-- Search Bar -->
      <div class="bg-white rounded-2xl p-4 shadow-sm border border-stone-100 relative">
        <div class="relative group">
          <Icon icon="material-symbols:search" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-stone-300 group-focus-within:text-[#4A7043] transition-colors" />
          <input 
            v-model="searchQuery"
            @keyup.enter="handleSearch"
            type="text" 
            placeholder="Cari berdasarkan ID atau Nama..." 
            class="w-full bg-stone-50 border border-stone-100 rounded-xl py-3.5 pl-12 pr-4 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-[#4A7043]/10 focus:border-[#4A7043] transition-all"
          />
        </div>
      </div>

      <!-- Table Section -->
      <div class="bg-white rounded-2xl shadow-sm border border-stone-100 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="bg-[#4A7043] text-white">
                <th class="py-4 px-6 text-xs font-black uppercase tracking-widest text-center">Id Request</th>
                <th class="py-4 px-6 text-xs font-black uppercase tracking-widest">Nasabah</th>
                <th class="py-4 px-6 text-xs font-black uppercase tracking-widest">Rekening</th>
                <th class="py-4 px-6 text-xs font-black uppercase tracking-widest">Nominal</th>
                <th class="py-4 px-6 text-xs font-black uppercase tracking-widest text-center">Status</th>
                <th class="py-4 px-6 text-xs font-black uppercase tracking-widest text-center">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
              <tr v-if="loading" v-for="i in 5" :key="i" class="animate-pulse">
                <td colspan="6" class="py-8 px-6 text-center text-stone-300">Memuat data...</td>
              </tr>
              <tr v-else-if="penarikans.length === 0">
                <td colspan="6" class="py-20 px-6 text-center">
                  <div class="flex flex-col items-center opacity-40">
                    <Icon icon="material-symbols:inbox-outline" class="w-12 h-12 mb-2" />
                    <p class="font-black">Tidak ada request penarikan</p>
                  </div>
                </td>
              </tr>
              <tr v-for="item in penarikans" :key="item.penarikan_id" class="hover:bg-stone-50/50 transition-colors group">
                <td class="py-4 px-6 text-center">
                  <span class="text-xs font-bold text-stone-500">WD-{{ item.created_at ? new Date(item.created_at).toISOString().slice(0, 10).replace(/-/g, '') : '0000' }}-{{ String(item.penarikan_id).padStart(3, '0') }}</span>
                </td>
                <td class="py-4 px-6">
                  <div class="flex flex-col">
                    <span class="font-black text-stone-800">{{ item.nasabah?.nama || '-' }}</span>
                    <span class="text-[10px] font-bold text-stone-400">ID: NSB-{{ String(item.nasabah_id).padStart(5, '0') }}</span>
                  </div>
                </td>
                <td class="py-4 px-6">
                  <div class="flex flex-col">
                    <span class="font-black text-stone-800">{{ item.nama_bank || item.nasabah?.nama_bank || '-' }}</span>
                    <span class="text-[10px] font-bold text-stone-400">{{ item.no_rekening || item.nasabah?.no_rekening || '-' }}</span>
                  </div>
                </td>
                <td class="py-4 px-6">
                  <span class="font-black text-stone-800">{{ formatRupiah(item.jumlah) }}</span>
                </td>
                <td class="py-4 px-6 text-center">
                  <span :class="[
                    'px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider',
                    item.status === 'pending' || item.status === 'proses' ? 'bg-orange-100 text-orange-600' :
                    item.status === 'selesai' ? 'bg-green-100 text-green-600' :
                    'bg-red-100 text-red-600'
                  ]">
                    {{ item.status === 'pending' || item.status === 'proses' ? 'Menunggu' : item.status }}
                  </span>
                </td>
                <td class="py-4 px-6">
                  <div class="flex items-center justify-center gap-2">
                    <button 
                      @click="openDetail(item)"
                      class="w-8 h-8 rounded-lg bg-cyan-500 text-white flex items-center justify-center hover:bg-cyan-600 transition-colors shadow-sm"
                      title="Lihat Detail"
                    >
                      <Icon icon="material-symbols:visibility-outline" class="w-5 h-5" />
                    </button>
                    <template v-if="item.status === 'pending' || item.status === 'proses'">
                      <button 
                        @click="openApprove(item)"
                        class="w-8 h-8 rounded-lg bg-green-600 text-white flex items-center justify-center hover:bg-green-700 transition-colors shadow-sm"
                        title="Setujui"
                      >
                        <Icon icon="material-symbols:check" class="w-5 h-5" />
                      </button>
                      <button 
                        @click="openReject(item)"
                        class="w-8 h-8 rounded-lg bg-red-600 text-white flex items-center justify-center hover:bg-red-700 transition-colors shadow-sm"
                        title="Tolak"
                      >
                        <Icon icon="material-symbols:close" class="w-5 h-5" />
                      </button>
                    </template>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Footer Pagination -->
        <div class="p-4 bg-stone-50/50 border-t border-stone-100 flex flex-col md:flex-row justify-between items-center gap-4">
          <div class="flex items-center gap-2 text-xs font-bold text-stone-500">
            Tampilkan
            <select class="bg-white border border-stone-200 rounded-lg py-1 px-2 focus:outline-none">
              <option>10</option>
            </select>
          </div>
          <div class="flex items-center gap-1">
            <button 
              @click="fetchData(pagination.current_page - 1)"
              :disabled="pagination.current_page === 1"
              class="px-4 py-2 rounded-xl text-xs font-bold bg-white border border-stone-200 text-stone-400 disabled:opacity-50 transition-all"
            >
              Sebelumnya
            </button>
            <div class="px-4 py-2 rounded-xl bg-[#4A7043] text-white text-xs font-bold shadow-md">
              {{ pagination.current_page }} / {{ pagination.last_page }}
            </div>
            <button 
              @click="fetchData(pagination.current_page + 1)"
              :disabled="pagination.current_page === pagination.last_page"
              class="px-4 py-2 rounded-xl text-xs font-bold bg-white border border-stone-200 text-stone-400 disabled:opacity-50 transition-all"
            >
              Selanjutnya
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modals -->
    <Transition name="fade">
      <div v-if="selectedPenarikan" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-stone-900/60 backdrop-blur-sm" @click="closeAllModals"></div>
        
        <!-- Detail Modal -->
        <div v-if="isDetailModalOpen" class="relative bg-white w-full max-w-lg rounded-[2rem] shadow-2xl overflow-hidden animate-in zoom-in-95 duration-300">
          <div class="p-6 pb-4 flex justify-between items-center border-b border-stone-50">
            <h3 class="text-xl font-black text-stone-800">Detail Request Penarikan</h3>
            <button @click="closeAllModals" class="p-2 hover:bg-stone-50 rounded-xl transition-colors text-stone-400">
              <Icon icon="material-symbols:close" class="w-6 h-6" />
            </button>
          </div>
          
          <div class="p-6 space-y-6 max-h-[75vh] overflow-y-auto no-scrollbar">
            <!-- Header Card -->
            <div class="bg-stone-50 rounded-2xl p-5 relative overflow-hidden">
              <div class="flex justify-between items-start relative z-10">
                <div class="space-y-1">
                  <p class="text-[10px] font-black text-stone-400 uppercase tracking-widest">ID Request</p>
                  <p class="font-black text-stone-800">WD-{{ new Date(selectedPenarikan.created_at).toISOString().slice(0, 10).replace(/-/g, '') }}-{{ String(selectedPenarikan.penarikan_id).padStart(3, '0') }}</p>
                  <p class="text-[11px] font-bold text-stone-500 mt-2">Tanggal: {{ formatFullDate(selectedPenarikan.created_at) }}</p>
                </div>
                <div class="flex flex-col items-end gap-2">
                   <p class="text-[10px] font-black text-stone-400 uppercase tracking-widest">Status</p>
                   <span :class="[
                    'px-4 py-1.5 rounded-full text-[10px] font-black uppercase flex items-center gap-1.5 shadow-sm',
                    selectedPenarikan.status === 'pending' || selectedPenarikan.status === 'proses' ? 'bg-orange-400 text-white' :
                    selectedPenarikan.status === 'selesai' ? 'bg-green-600 text-white' : 'bg-red-600 text-white'
                   ]">
                    <Icon :icon="selectedPenarikan.status === 'pending' || selectedPenarikan.status === 'proses' ? 'material-symbols:schedule' : selectedPenarikan.status === 'selesai' ? 'material-symbols:check-circle' : 'material-symbols:cancel'" class="w-4 h-4" />
                    {{ selectedPenarikan.status === 'pending' || selectedPenarikan.status === 'proses' ? 'Menunggu' : selectedPenarikan.status }}
                   </span>
                   <p v-if="selectedPenarikan.status === 'selesai' && selectedPenarikan.updated_at" class="text-[10px] font-bold text-stone-500 mt-1">
                    Diproses: {{ formatFullDate(selectedPenarikan.updated_at) }}
                   </p>
                </div>
              </div>
            </div>

            <!-- Data Nasabah -->
            <div class="space-y-4">
              <h4 class="text-sm font-black text-stone-800 flex items-center gap-2">
                <div class="w-1.5 h-4 bg-[#4A7043] rounded-full"></div>
                Data Nasabah
              </h4>
              <div class="grid grid-cols-2 gap-y-4">
                <div>
                  <p class="text-[10px] font-black text-stone-400 uppercase tracking-widest">Nama</p>
                  <p class="font-bold text-stone-800">{{ selectedPenarikan.nasabah?.nama || '-' }}</p>
                </div>
                <div class="text-right">
                  <p class="text-[10px] font-black text-stone-400 uppercase tracking-widest">Siti Aminah</p> <!-- Mock text from image -->
                  <p class="font-bold text-stone-800">{{ selectedPenarikan.nasabah?.nama }}</p>
                </div>
                <div>
                  <p class="text-[10px] font-black text-stone-400 uppercase tracking-widest">ID Nasabah</p>
                  <p class="font-bold text-stone-800">NSB-{{ String(selectedPenarikan.nasabah_id).padStart(3, '0') }}</p>
                </div>
                <div class="text-right">
                  <p class="text-[10px] font-black text-stone-400 uppercase tracking-widest">Saldo Tersedia</p>
                  <p class="font-black text-green-600">{{ formatRupiah(selectedPenarikan.nasabah?.saldo) }}</p>
                </div>
                <div>
                  <p class="text-[10px] font-black text-stone-400 uppercase tracking-widest">Saldo Petugas (Limit Harian)</p>
                  <p class="font-bold text-stone-800">{{ formatRupiah(dailyLimit) }}</p>
                </div>
                <div class="text-right">
                  <p class="text-[10px] font-black text-stone-400 uppercase tracking-widest">Sisa Saldo Petugas</p>
                  <p class="font-black text-green-600">{{ formatRupiah(remainingLimit) }}</p>
                </div>
              </div>
            </div>

            <!-- Detail Penarikan -->
            <div class="space-y-4">
              <h4 class="text-sm font-black text-stone-800 flex items-center gap-2">
                <div class="w-1.5 h-4 bg-[#4A7043] rounded-full"></div>
                Detail Penarikan
              </h4>
              <div class="space-y-3">
                <div class="flex justify-between items-center">
                  <p class="text-[10px] font-black text-stone-400 uppercase tracking-widest">Jumlah Penarikan</p>
                  <p class="text-lg font-black text-stone-800">{{ formatRupiah(selectedPenarikan.jumlah) }}</p>
                </div>
                <div class="flex justify-between items-center">
                  <p class="text-[10px] font-black text-stone-400 uppercase tracking-widest">Sisa Saldo Setelah Penarikan</p>
                  <p class="font-black text-green-600">{{ formatRupiah(selectedPenarikan.nasabah?.saldo - selectedPenarikan.jumlah) }}</p>
                </div>
                <div class="bg-orange-50 border border-orange-100 rounded-xl p-4">
                  <p class="text-[10px] font-black text-orange-800 uppercase tracking-widest mb-1">Rekening Tujuan</p>
                  <p class="font-black text-orange-900">{{ selectedPenarikan.nama_bank || selectedPenarikan.nasabah?.nama_bank }} - {{ selectedPenarikan.no_rekening || selectedPenarikan.nasabah?.no_rekening }}</p>
                </div>
              </div>
            </div>

            <!-- Bukti Transfer (If Selesai) -->
            <div v-if="selectedPenarikan.status === 'selesai' && selectedPenarikan.bukti_tf" class="space-y-3">
              <div class="bg-green-50 border border-green-100 rounded-xl p-4">
                 <p class="text-[10px] font-black text-green-800 uppercase tracking-widest mb-1">Bukti Transfer</p>
                 <a :href="`http://localhost:8000/storage/${selectedPenarikan.bukti_tf}`" target="_blank" class="text-xs font-bold text-green-600 hover:underline flex items-center gap-1">
                    <Icon icon="material-symbols:image" class="w-4 h-4" />
                    {{ selectedPenarikan.bukti_tf.split('/').pop() }}
                 </a>
              </div>
            </div>

            <!-- Keterangan Penolakan (If Ditolak) -->
            <div v-if="selectedPenarikan.status === 'tolak'" class="bg-red-50 border border-red-100 rounded-xl p-4">
                <p class="text-[10px] font-black text-red-800 uppercase tracking-widest mb-1">Alasan Penolakan</p>
                <p class="text-xs font-bold text-red-700 leading-relaxed">{{ selectedPenarikan.ket_status || 'Tidak ada alasan diberikan.' }}</p>
            </div>
          </div>

          <div class="p-6 border-t border-stone-50 flex gap-3">
            <button @click="closeAllModals" class="flex-1 py-3.5 rounded-xl bg-stone-50 text-stone-600 font-black text-sm hover:bg-stone-100 transition-colors">Tutup</button>
            <template v-if="selectedPenarikan.status === 'pending' || selectedPenarikan.status === 'proses'">
               <button @click="openApprove(selectedPenarikan)" class="flex-1 py-3.5 rounded-xl bg-[#4A7043] text-white font-black text-sm hover:bg-[#3D5C37] transition-all flex items-center justify-center gap-2">
                 <Icon icon="material-symbols:check" class="w-5 h-5" />
                 Terima
               </button>
               <button @click="openReject(selectedPenarikan)" class="flex-1 py-3.5 rounded-xl bg-red-600 text-white font-black text-sm hover:bg-red-700 transition-all flex items-center justify-center gap-2">
                 <Icon icon="material-symbols:close" class="w-5 h-5" />
                 Tolak
               </button>
            </template>
          </div>
        </div>

        <!-- Approve Modal -->
        <div v-if="isApproveModalOpen" class="relative bg-white w-full max-w-lg rounded-[2rem] shadow-2xl overflow-hidden animate-in zoom-in-95 duration-300">
           <div class="p-6 pb-4 flex justify-between items-center border-b border-stone-50">
            <h3 class="text-xl font-black text-stone-800">Terima Request Penarikan</h3>
            <button @click="closeAllModals" class="p-2 hover:bg-stone-50 rounded-xl transition-colors text-stone-400">
              <Icon icon="material-symbols:close" class="w-6 h-6" />
            </button>
          </div>

          <div class="p-6 space-y-6">
            <div class="bg-green-50 border border-green-100 rounded-2xl p-4 flex gap-3">
              <Icon icon="material-symbols:info-outline" class="w-5 h-5 text-green-600 shrink-0 mt-0.5" />
              <p class="text-sm font-medium text-green-800 leading-snug">
                Anda akan menerima request penarikan dari <span class="font-black">{{ selectedPenarikan.nasabah?.nama }}</span> sebesar <span class="font-black">{{ formatRupiah(selectedPenarikan.jumlah) }}</span>
              </p>
            </div>

            <div class="bg-orange-50 border border-orange-100 rounded-2xl p-5 space-y-1">
              <p class="text-[10px] font-black text-orange-800 uppercase tracking-widest">Informasi Rekening Tujuan</p>
              <p class="font-black text-orange-900">{{ selectedPenarikan.nasabah?.nama_rek || selectedPenarikan.nasabah?.nama }}</p>
              <p class="font-black text-orange-900 text-lg uppercase">{{ selectedPenarikan.nama_bank || selectedPenarikan.nasabah?.nama_bank }} - {{ selectedPenarikan.no_rekening || selectedPenarikan.nasabah?.no_rekening }}</p>
            </div>

            <div class="space-y-3">
              <label class="text-xs font-black text-stone-800">Upload Bukti Transfer <span class="text-red-500">*</span></label>
              <div 
                class="border-2 border-dashed border-stone-200 rounded-2xl p-8 flex flex-col items-center justify-center gap-3 bg-stone-50 hover:border-[#4A7043] hover:bg-green-50 transition-all cursor-pointer relative overflow-hidden"
                @click="$refs.fileInput.click()"
              >
                <input type="file" ref="fileInput" class="hidden" accept="image/*" @change="handleFileUpload" />
                
                <template v-if="!buktiFile">
                  <div class="w-12 h-12 rounded-full bg-white flex items-center justify-center text-stone-400 shadow-sm">
                    <Icon icon="material-symbols:upload" class="w-6 h-6" />
                  </div>
                  <div class="text-center">
                    <p class="text-sm font-black text-stone-800">Klik untuk upload bukti transfer</p>
                    <p class="text-[10px] font-bold text-stone-400 mt-1 uppercase tracking-widest">Format: JPG, PNG, PDF (Max 5MB)</p>
                  </div>
                </template>
                <template v-else>
                  <Icon icon="material-symbols:image" class="w-12 h-12 text-green-600 mb-1" />
                  <p class="text-sm font-black text-green-800">{{ buktiFile.name }}</p>
                  <p class="text-[10px] font-bold text-green-600 underline">Klik untuk ganti file</p>
                </template>
              </div>
            </div>
          </div>

          <div class="p-6 border-t border-stone-50 flex gap-4">
             <button @click="closeAllModals" class="flex-1 py-3.5 rounded-xl bg-stone-50 text-stone-600 font-black text-sm hover:bg-stone-100 transition-colors">Batal</button>
             <button 
              @click="confirmApprove" 
              :disabled="isSubmitting || !buktiFile"
              class="flex-[2] py-3.5 rounded-xl bg-[#4A7043] text-white font-black text-sm hover:bg-[#3D5C37] transition-all shadow-md disabled:opacity-50 active:scale-[0.98]"
            >
              {{ isSubmitting ? 'Memproses...' : 'Setujui Request' }}
            </button>
          </div>
        </div>

        <!-- Reject Modal -->
        <div v-if="isRejectModalOpen" class="relative bg-white w-full max-w-lg rounded-[2rem] shadow-2xl overflow-hidden animate-in zoom-in-95 duration-300">
           <div class="p-6 pb-4 flex justify-between items-center border-b border-stone-50">
            <h3 class="text-xl font-black text-stone-800">Tolak Request Penarikan</h3>
            <button @click="closeAllModals" class="p-2 hover:bg-stone-50 rounded-xl transition-colors text-stone-400">
              <Icon icon="material-symbols:close" class="w-6 h-6" />
            </button>
          </div>

          <div class="p-6 space-y-6">
            <div class="bg-red-50 border border-red-100 rounded-2xl p-4 flex gap-3">
              <Icon icon="material-symbols:warning-outline" class="w-5 h-5 text-red-600 shrink-0 mt-0.5" />
              <p class="text-sm font-medium text-red-800 leading-snug">
                Anda akan menolak request penarikan dari <span class="font-black">{{ selectedPenarikan.nasabah?.nama }}</span> sebesar <span class="font-black">{{ formatRupiah(selectedPenarikan.jumlah) }}</span>
              </p>
            </div>

            <div class="space-y-3">
              <label class="text-xs font-black text-stone-800">Alasan Penolakan <span class="text-red-500">*</span></label>
              <textarea 
                v-model="rejectReason"
                placeholder="Masukkan alasan penolakan request penarikan..." 
                class="w-full bg-stone-50 border border-stone-100 rounded-2xl py-4 px-5 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition-all min-h-[120px] text-stone-700"
              ></textarea>
            </div>
          </div>

          <div class="p-6 border-t border-stone-50 flex gap-4">
             <button @click="closeAllModals" class="flex-1 py-3.5 rounded-xl bg-stone-50 text-stone-600 font-black text-sm hover:bg-stone-100 transition-colors">Batal</button>
             <button 
              @click="confirmReject" 
              :disabled="isSubmitting || !rejectReason.trim()"
              class="flex-[2] py-3.5 rounded-xl bg-red-600 text-white font-black text-sm hover:bg-red-700 transition-all shadow-md disabled:opacity-50 active:scale-[0.98]"
            >
              {{ isSubmitting ? 'Memproses...' : 'Tolak Request' }}
            </button>
          </div>
        </div>
      </div>
    </Transition>
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

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.animate-in {
  animation: fadeIn 0.5s ease-out both;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

.zoom-in-95 {
  animation: zoomIn 0.3s ease-out both;
}

@keyframes zoomIn {
  from { opacity: 0; transform: scale(0.95); }
  to { opacity: 1; transform: scale(1); }
}
</style>
