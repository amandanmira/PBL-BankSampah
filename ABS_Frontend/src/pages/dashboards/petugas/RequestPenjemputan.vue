<script setup>
import { ref, onMounted, computed, inject } from "vue";
import { Icon } from "@iconify/vue";
import DashboardLayout from "@/layouts/DashboardLayout.vue";
import { checkRole } from "@/utils";
import { useRouter } from "vue-router";

// Security check
checkRole("petugas");

const router = useRouter();
const axios = inject('axios');

// State
const requests = ref([]);
const workers = ref([]);
const loading = ref(false);
const searchQuery = ref("");
const activeFilter = ref("Menunggu"); // Menunggu, Diproses, Perlu Input Data
const isWorkerModalOpen = ref(false);
const selectedRequest = ref(null);

// Fetch data
const fetchData = async () => {
  loading.value = true;
  try {
    const res = await axios.get("/api/petugas/penjemputan");
    // Ensure nested nasabah is handled
    requests.value = res.data.data.map(r => ({
      ...r,
      selectedTukang: null,
      showScheduleForm: false
    }));
  } catch (err) {
    console.error("Failed to fetch requests:", err);
  } finally {
    loading.value = false;
  }
};

const fetchWorkers = async () => {
  try {
    const res = await axios.get("/api/petugas/tukang");
    workers.value = res.data.data;
  } catch (err) {
    console.error("Failed to fetch workers:", err);
  }
};

// Computed
const filteredRequests = computed(() => {
  let filtered = requests.value;

  // Filter by status tab
  if (activeFilter.value === "Menunggu") {
    filtered = filtered.filter(r => r.status === 'pending');
  } else if (activeFilter.value === "Diproses") {
    filtered = filtered.filter(r => r.status === 'proses');
  } else if (activeFilter.value === "Perlu Input Data") {
    // Assuming 'Perlu Input Data' is for requests in 'proses' status that haven't been weighed yet
    // For now, let's keep it empty or mock a condition
    filtered = []; 
  }

  // Search filter
  if (searchQuery.value) {
    const q = searchQuery.value.toLowerCase();
    filtered = filtered.filter(r => 
      r.penjemputan_id.toString().includes(q) || 
      (r.nasabah?.nama && r.nasabah.nama.toLowerCase().includes(q))
    );
  }

  return filtered;
});

const getCount = (filter) => {
  if (filter === "Menunggu") return requests.value.filter(r => r.status === 'pending').length;
  if (filter === "Diproses") return requests.value.filter(r => r.status === 'proses').length;
  if (filter === "Perlu Input Data") return 0;
  return 0;
};

// Actions
const openWorkerModal = (request) => {
  selectedRequest.value = request;
  isWorkerModalOpen.value = true;
};

const selectWorker = (worker) => {
  if (selectedRequest.value) {
    selectedRequest.value.selectedTukang = worker;
  }
  isWorkerModalOpen.value = false;
};

const handleTerima = (request) => {
  if (!request.selectedTukang) return;
  // Set flag to show schedule form (static simulation)
  request.showScheduleForm = true;
  console.log("Terima request:", request.penjemputan_id);
};

const handleTolak = (request) => {
  // Currently static as requested
  console.log("Tolak request:", request.penjemputan_id);
};

const showDetail = (id) => {
  router.push(`/dashboard-petugas/penimbangan/${id}`);
};

const formatDate = (dateStr) => {
  if (!dateStr) return "-";
  const date = new Date(dateStr);
  return date.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) + ", " + 
         date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
};

onMounted(() => {
  fetchData();
  fetchWorkers();
});
</script>

<template>
  <DashboardLayout title="Request Penjemputan">
    <div class="space-y-6 animate-in fade-in duration-700 pb-10">
      <!-- Header Section -->
      <div>
        <h2 class="text-2xl font-black text-stone-800">Request Penjemputan</h2>
        <p class="text-stone-500 font-medium">Kelola request penjemputan dari nasabah yang masih aktif</p>
        <p class="text-stone-400 text-[10px] font-bold mt-1 italic">* Request yang sudah selesai/ditolak ada di menu Riwayat Transaksi</p>
      </div>

      <!-- Search Bar -->
      <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-stone-100">
        <label class="block text-[10px] font-black text-stone-400 uppercase tracking-widest mb-2">Cari Request</label>
        <div class="flex gap-3">
          <div class="flex-1 relative">
            <input 
              v-model="searchQuery"
              type="text" 
              placeholder="ID Request atau Nama Nasabah" 
              class="w-full bg-stone-50 border border-stone-100 rounded-2xl py-3.5 px-5 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-[#4A7043]/20 transition-all"
            />
          </div>
          <button class="bg-[#4A7043] text-white p-3.5 rounded-2xl shadow-lg shadow-green-900/10 hover:scale-105 active:scale-95 transition-all">
            <Icon icon="material-symbols:search" class="w-6 h-6" />
          </button>
        </div>
      </div>

      <!-- Filter Tabs -->
      <div class="bg-white rounded-[2rem] p-2 shadow-sm border border-stone-100 flex">
        <button 
          v-for="filter in ['Menunggu', 'Diproses', 'Perlu Input Data']" 
          :key="filter"
          @click="activeFilter = filter"
          :class="[
            'flex-1 py-3.5 rounded-[1.5rem] text-xs font-black transition-all flex items-center justify-center gap-2',
            activeFilter === filter ? 'bg-[#4A7043] text-white shadow-md' : 'text-stone-400 hover:bg-stone-50'
          ]"
        >
          {{ filter }}
          <span 
            v-if="getCount(filter) > 0"
            :class="[
              'px-2 py-0.5 rounded-full text-[10px]',
              activeFilter === filter ? 'bg-white/20 text-white' : 'bg-stone-100 text-stone-500'
            ]"
          >
            {{ getCount(filter) }}
          </span>
        </button>
      </div>

      <!-- Request List -->
      <div class="space-y-4">
        <div v-if="loading" class="flex justify-center py-20">
          <Icon icon="line-md:loading-twotone-loop" class="w-12 h-12 text-[#4A7043]" />
        </div>

        <template v-else-if="filteredRequests.length > 0">
          <div 
            v-for="request in filteredRequests" 
            :key="request.penjemputan_id"
            class="bg-white rounded-[2rem] p-6 shadow-sm border border-stone-100 space-y-6"
          >
            <!-- Card Header -->
            <div class="flex justify-between items-start">
              <div class="flex items-center gap-3">
                <h3 class="text-xl font-black text-stone-800">REQ-{{ String(request.penjemputan_id).padStart(3, '0') }}</h3>
                <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase bg-orange-100 text-orange-600">
                  {{ request.status === 'pending' ? 'Menunggu' : request.status }}
                </span>
                <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase bg-stone-100 text-stone-500">
                  Penjemputan
                </span>
              </div>
              <button 
                @click="showDetail(request.penjemputan_id)"
                class="flex items-center gap-1.5 px-4 py-2 rounded-xl bg-stone-50 text-stone-400 text-[10px] font-black hover:bg-stone-100 transition-colors uppercase tracking-widest"
              >
                <Icon icon="material-symbols:visibility-outline" class="w-4 h-4" />
                Detail
              </button>
            </div>

            <!-- Nasabah Info -->
            <div class="space-y-3">
              <p class="font-black text-stone-800">{{ request.nasabah?.nama || 'Unknown' }} <span class="text-stone-400 font-bold">(NSB-{{ String(request.nasabah_id).padStart(3, '0') }})</span></p>
              <div class="flex flex-wrap gap-y-2 gap-x-6">
                <div class="flex items-center gap-2 text-stone-400">
                  <Icon icon="material-symbols:location-on-outline" class="w-4 h-4" />
                  <p class="text-xs font-bold">{{ request.alamat }}</p>
                </div>
                <div class="flex items-center gap-2 text-stone-400">
                  <Icon icon="material-symbols:calendar-today-outline" class="w-4 h-4" />
                  <p class="text-xs font-bold">{{ formatDate(request.created_at) }}</p>
                </div>
                <div class="flex items-center gap-2 text-stone-400">
                  <Icon icon="material-symbols:call-outline" class="w-4 h-4" />
                  <p class="text-xs font-bold">{{ request.nasabah?.no_telp || '-' }}</p>
                </div>
              </div>
            </div>

            <!-- Weight & Waste Info -->
            <div class="bg-stone-50 rounded-2xl p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <p class="text-[10px] font-black text-stone-400 uppercase tracking-widest mb-1">Estimasi Berat</p>
                <p class="font-black text-stone-800 text-lg">{{ request.deskripsi?.split('|')[0] || '8-10 kg' }}</p>
              </div>
              <div>
                <p class="text-[10px] font-black text-stone-400 uppercase tracking-widest mb-1">Jenis Sampah</p>
                <p class="font-black text-stone-800 text-lg">{{ request.deskripsi?.split('|')[1] || 'Plastik PET, Botol Kaca' }}</p>
              </div>
              <div v-if="request.deskripsi?.split('|')[2]" class="md:col-span-2 pt-2 border-t border-stone-100">
                <p class="text-[10px] font-black text-stone-400 uppercase tracking-widest mb-1">Keterangan Tambahan</p>
                <p class="text-xs font-bold text-stone-600">{{ request.deskripsi?.split('|')[2] }}</p>
              </div>
            </div>

            <!-- Worker Selection -->
            <button 
              @click="openWorkerModal(request)"
              :class="[
                'w-full py-4 rounded-2xl text-sm font-black transition-all border-2 flex items-center justify-center gap-2',
                request.selectedTukang 
                  ? 'bg-stone-50 border-stone-200 text-stone-800' 
                  : 'bg-red-50 border-red-200 text-red-600 border-dashed'
              ]"
            >
              <span v-if="!request.selectedTukang">Pilih tukang *</span>
              <template v-else>
                <span>{{ request.selectedTukang.nama }}</span>
                <Icon icon="material-symbols:keyboard-arrow-down" class="w-5 h-5" />
              </template>
            </button>

            <!-- Success Banner (Conditional) -->
            <div v-if="request.showScheduleForm" class="bg-green-50 border border-green-100 rounded-2xl p-4 flex items-center gap-3">
              <div class="w-8 h-8 bg-white rounded-full flex items-center justify-center text-green-600 shadow-sm">
                <Icon icon="material-symbols:person-check-outline" class="w-5 h-5" />
              </div>
              <div>
                <p class="text-green-800 font-black text-xs leading-none">Request telah Anda ambil</p>
                <p class="text-green-600 font-bold text-[10px] mt-1">Lanjutkan dengan menjadwalkan penjemputan</p>
              </div>
            </div>

            <!-- Schedule Form (Conditional) -->
            <div v-if="request.showScheduleForm" class="bg-blue-50/50 border border-blue-100 rounded-[2rem] p-6 space-y-6 relative shadow-sm">
              <button @click="request.showScheduleForm = false" class="absolute top-6 right-6 text-stone-400 hover:text-stone-600 transition-colors">
                <Icon icon="material-symbols:close" class="w-5 h-5" />
              </button>
              <h4 class="font-black text-stone-800">Form Jadwal Penjemputan</h4>
              
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-2">
                  <label class="text-[10px] font-black text-stone-400 uppercase tracking-widest">Tanggal</label>
                  <input type="date" class="w-full bg-white border border-blue-100 rounded-xl py-3 px-4 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-500/20 transition-all" />
                </div>
                <div class="space-y-2">
                  <label class="text-[10px] font-black text-stone-400 uppercase tracking-widest">Waktu</label>
                  <input type="time" class="w-full bg-white border border-blue-100 rounded-xl py-3 px-4 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-500/20 transition-all" />
                </div>
              </div>

              <div class="space-y-2">
                <label class="text-[10px] font-black text-stone-400 uppercase tracking-widest">Tukang</label>
                <div class="relative">
                  <select class="w-full bg-white border border-blue-100 rounded-xl py-3 px-4 text-sm font-medium focus:outline-none appearance-none pr-10">
                    <option :value="request.selectedTukang?.tukang_id">{{ request.selectedTukang?.nama }}</option>
                  </select>
                  <Icon icon="material-symbols:keyboard-arrow-down" class="absolute right-4 top-1/2 -translate-y-1/2 w-5 h-5 text-stone-400 pointer-events-none" />
                </div>
              </div>

              <button class="w-full py-4 rounded-2xl bg-[#5FA09B] text-white text-xs font-black uppercase tracking-widest hover:bg-[#4E8B86] transition-all shadow-lg active:scale-95">
                Konfirmasi Jadwal
              </button>
            </div>

            <!-- Action Buttons -->
            <div v-if="!request.showScheduleForm" class="flex flex-col md:flex-row gap-4">
              <button 
                @click="handleTerima(request)"
                :disabled="!request.selectedTukang"
                :class="[
                  'flex-[2] py-4 rounded-2xl text-sm font-black uppercase tracking-widest transition-all shadow-lg',
                  request.selectedTukang 
                    ? 'bg-[#4A7043]/60 text-white hover:bg-[#4A7043]' 
                    : 'bg-stone-200 text-stone-400 cursor-not-allowed shadow-none'
                ]"
              >
                Terima & Atur Jadwal Penjemputan
              </button>
              <button 
                @click="handleTolak(request)"
                class="flex-1 py-4 rounded-2xl bg-[#C62828] text-white text-sm font-black uppercase tracking-widest hover:bg-[#B71C1C] transition-all shadow-lg"
              >
                Tolak
              </button>
            </div>
          </div>
        </template>

        <div v-else class="flex flex-col items-center justify-center py-20 opacity-60">
          <div class="w-20 h-20 bg-stone-100 rounded-full flex items-center justify-center mb-6">
            <Icon icon="material-symbols:inbox-outline" class="w-10 h-10 text-stone-300" />
          </div>
          <p class="text-stone-400 font-black text-lg">Belum Ada Request Penjemputan Yang Tersedia</p>
        </div>
      </div>
    </div>

    <!-- Worker Selection Modal -->
    <div v-if="isWorkerModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center p-6">
      <div class="absolute inset-0 bg-stone-900/60 backdrop-blur-sm" @click="isWorkerModalOpen = false"></div>
      <div class="relative bg-[#F5F5F0] w-full max-w-2xl rounded-[2.5rem] shadow-2xl flex flex-col max-h-[80vh] overflow-hidden">
        <div class="p-8 pb-4 flex justify-between items-center">
          <h3 class="text-xl font-black text-stone-800">Pilih Tukang Untuk Penjemputan</h3>
          <button @click="isWorkerModalOpen = false" class="p-2 hover:bg-stone-200 rounded-xl transition-colors">
            <Icon icon="material-symbols:close" class="w-6 h-6 text-stone-400" />
          </button>
        </div>
        
        <div class="flex-1 overflow-y-auto p-8 pt-4 space-y-4">
          <div 
            v-for="worker in workers" 
            :key="worker.tukang_id"
            @click="selectWorker(worker)"
            class="bg-[#4A7043] rounded-2xl p-5 text-white flex items-center gap-6 cursor-pointer hover:scale-[1.02] active:scale-[0.98] transition-all group"
          >
            <div class="w-20 h-20 bg-white/10 rounded-2xl flex items-center justify-center overflow-hidden border border-white/10 shrink-0">
              <Icon icon="material-symbols:image-outline" class="w-8 h-8 text-white/30" />
            </div>
            <div class="flex-1 space-y-1">
              <div class="flex justify-between items-start">
                <div>
                  <p class="text-[10px] font-black text-white/40 uppercase tracking-widest">Nama:</p>
                  <p class="font-black text-lg">{{ worker.nama }}</p>
                </div>
                <button class="bg-white text-[#4A7043] px-5 py-2 rounded-xl text-xs font-black shadow-lg">
                  Pilih Tukang
                </button>
              </div>
              <div class="flex gap-6 pt-2">
                <div>
                  <p class="text-[10px] font-black text-white/40 uppercase tracking-widest">No. Telp:</p>
                  <p class="text-sm font-bold">{{ worker.no_telp }}</p>
                </div>
                <div>
                  <p class="text-[10px] font-black text-white/40 uppercase tracking-widest">Email:</p>
                  <p class="text-sm font-bold opacity-80">{{ worker.email || '-' }}</p>
                </div>
              </div>
            </div>
          </div>

          <div v-if="workers.length === 0" class="text-center py-10">
            <p class="text-stone-400 font-bold">Tidak ada tukang yang tersedia.</p>
          </div>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>

<style scoped>
.animate-in {
  animation: fadeIn 0.7s ease-out both;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(15px); }
  to { opacity: 1; transform: translateY(0); }
}

::-webkit-scrollbar {
  width: 6px;
}
::-webkit-scrollbar-track {
  background: transparent;
}
::-webkit-scrollbar-thumb {
  background: #e2e2e2;
  border-radius: 10px;
}
::-webkit-scrollbar-thumb:hover {
  background: #d1d1d1;
}
</style>
