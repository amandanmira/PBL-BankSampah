<script setup>
import { ref, onMounted, computed, inject } from "vue";
import { Icon } from "@iconify/vue";
import DashboardLayout from "@/layouts/DashboardLayout.vue";
import { checkRole } from "@/utils";
import { cn } from "@/lib/utils";

// Security check
checkRole("admin");

const axios = inject('axios');

// State
const pendingPengepuls = ref([]);
const isLoading = ref(true);
const error = ref(null);
const searchQuery = ref("");
const itemsPerPage = ref(10);
const currentPage = ref(1);

// Modal States
const isRejectModalOpen = ref(false);
const isDocModalOpen = ref(false);
const isImagePreviewOpen = ref(false);
const previewImageSrc = ref("");
const previewImageTitle = ref("");
const isSubmitting = ref(false);
const selectedPengepul = ref(null);
const rejectionReason = ref("");

const openImagePreview = (src, title) => {
  previewImageSrc.value = src;
  previewImageTitle.value = title;
  isImagePreviewOpen.value = true;
};

// Fetch Data
const fetchData = async () => {
  try {
    isLoading.value = true;
    const token = sessionStorage.getItem("token");
    const headers = { Authorization: `Bearer ${token}` };

    const response = await axios.get("/api/admin/pengepul/pending", { headers });
    pendingPengepuls.value = response.data.data;
  } catch (err) {
    error.value = "Gagal memuat data pendaftar: " + (err.response?.data?.message || err.message);
    console.error(err);
  } finally {
    isLoading.value = false;
  }
};

onMounted(fetchData);

// Filtering and Pagination
const filteredPengepuls = computed(() => {
  let result = pendingPengepuls.value;

  // Filter by Search
  if (searchQuery.value) {
    const q = searchQuery.value.toLowerCase();
    result = result.filter(
      (p) =>
        p.nama?.toLowerCase().includes(q) ||
        p.nama_lembaga?.toLowerCase().includes(q) ||
        p.username?.toLowerCase().includes(q) ||
        p.email?.toLowerCase().includes(q)
    );
  }

  return result;
});

const totalPages = computed(() => Math.ceil(filteredPengepuls.value.length / itemsPerPage.value));

const paginatedPengepuls = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value;
  const end = start + itemsPerPage.value;
  return filteredPengepuls.value.slice(start, end);
});

// Actions
const handleApprove = async (pengepul) => {
  if (!confirm(`Apakah Anda yakin ingin menyetujui pendaftaran ${pengepul.nama_lembaga}?`)) return;

  try {
    isSubmitting.value = true;
    const token = sessionStorage.getItem("token");
    const headers = { Authorization: `Bearer ${token}` };

    await axios.put(`/api/admin/pengepul/${pengepul.pengepul_id}/terima`, {}, { headers });
    
    // Update local state
    pendingPengepuls.value = pendingPengepuls.value.filter(p => p.pengepul_id !== pengepul.pengepul_id);
    alert("Pendaftaran berhasil disetujui!");
  } catch (err) {
    alert("Gagal menyetujui pendaftaran: " + (err.response?.data?.message || err.message));
  } finally {
    isSubmitting.value = false;
  }
};

const openRejectModal = (pengepul) => {
  selectedPengepul.value = pengepul;
  rejectionReason.value = "";
  isRejectModalOpen.value = true;
};

const handleReject = async () => {
  if (!rejectionReason.value.trim()) {
    alert("Alasan penolakan wajib diisi.");
    return;
  }

  try {
    isSubmitting.value = true;
    const token = sessionStorage.getItem("token");
    const headers = { Authorization: `Bearer ${token}` };

    await axios.put(`/api/admin/pengepul/${selectedPengepul.value.pengepul_id}/tolak`, {
      ket_status: rejectionReason.value
    }, { headers });
    
    // Update local state
    pendingPengepuls.value = pendingPengepuls.value.filter(p => p.pengepul_id !== selectedPengepul.value.pengepul_id);
    isRejectModalOpen.value = false;
    alert("Pendaftaran berhasil ditolak.");
  } catch (err) {
    alert("Gagal menolak pendaftaran: " + (err.response?.data?.message || err.message));
  } finally {
    isSubmitting.value = false;
  }
};

const openDocModal = (pengepul) => {
  selectedPengepul.value = pengepul;
  isDocModalOpen.value = true;
};

// Helpers
const formatDate = (dateStr) => {
  if (!dateStr) return "-";
  const date = new Date(dateStr);
  return new Intl.DateTimeFormat("id-ID", {
    day: "2-digit",
    month: "short",
    year: "numeric",
  }).format(date);
};

const isImage = (filename) => {
  if (!filename) return false;
  const ext = filename.split('.').pop().toLowerCase();
  return ['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext);
};

const getFileUrl = (path) => {
  if (!path) return '#';
  // Assuming storage is linked and accessible via /storage
  return `http://localhost:8000/storage/${path}`;
};
</script>

<template>
  <DashboardLayout title="Konfirmasi Pengepul">
    <div class="space-y-6 animate-in fade-in duration-500">
      
      <!-- Header with Badge -->
      <div class="flex items-center gap-4">
        <h1 class="text-2xl font-bold text-gray-800">Pengepul Menunggu Konfirmasi</h1>
        <div class="bg-[#7A3E3E] text-white px-4 py-1.5 rounded-xl text-sm font-bold shadow-sm">
          {{ pendingPengepuls.length }} Request
        </div>
      </div>

      <!-- Search Bar -->
      <div class="relative group max-w-lg">
        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
          <Icon icon="material-symbols:search" class="w-5 h-5 text-gray-400 group-focus-within:text-[#4A7043] transition-colors" />
        </div>
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Cari nama perusahaan, pemilik, atau username..."
          class="w-full bg-white border-none rounded-2xl py-4 pl-12 pr-4 shadow-sm focus:ring-2 focus:ring-[#4A7043] outline-none transition-all placeholder:text-gray-400"
        />
      </div>

      <!-- Table Container -->
      <div class="bg-white rounded-[32px] shadow-sm overflow-hidden border border-gray-100">
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="bg-[#4A7043] text-white">
                <th class="px-6 py-5 font-bold uppercase text-xs tracking-wider">Identitas</th>
                <th class="px-6 py-5 font-bold uppercase text-xs tracking-wider">Kontak</th>
                <th class="px-6 py-5 font-bold uppercase text-xs tracking-wider">Alamat</th>
                <th class="px-6 py-5 font-bold uppercase text-xs tracking-wider">Tanggal Daftar</th>
                <th class="px-6 py-5 font-bold uppercase text-xs tracking-wider text-center">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
              <tr v-if="isLoading">
                <td colspan="5" class="px-6 py-20 text-center">
                  <div class="flex flex-col items-center gap-3">
                    <Icon icon="line-md:loading-twotone-loop" class="w-10 h-10 text-[#4A7043]" />
                    <span class="text-gray-500 font-medium italic">Memuat pendaftar...</span>
                  </div>
                </td>
              </tr>
              <tr v-else-if="paginatedPengepuls.length === 0">
                <td colspan="5" class="px-6 py-20 text-center text-gray-500 italic">
                  Tidak ada pendaftar pengepul yang perlu dikonfirmasi.
                </td>
              </tr>
              <tr 
                v-for="pengepul in paginatedPengepuls" 
                :key="pengepul.pengepul_id"
                class="hover:bg-gray-50/50 transition-colors group"
              >
                <!-- Identitas -->
                <td class="px-6 py-6">
                  <div class="flex items-start gap-3">
                    <div class="flex flex-col">
                      <span class="font-bold text-gray-800 text-base">{{ pengepul.nama_lembaga }}</span>
                      <span class="text-xs text-[#4A7043] font-medium">@{{ pengepul.username }}</span>
                      <span class="text-xs text-gray-500 mt-1 italic">Pemilik: {{ pengepul.nama }}</span>
                    </div>
                  </div>
                </td>
                
                <!-- Kontak -->
                <td class="px-6 py-6">
                  <div class="flex flex-col gap-1 text-sm text-gray-600">
                    <div class="flex items-center gap-2">
                      <Icon icon="material-symbols:mail-outline" class="w-4 h-4 text-gray-400" />
                      <span>{{ pengepul.email }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                      <Icon icon="material-symbols:call-outline" class="w-4 h-4 text-gray-400" />
                      <span>{{ pengepul.no_telp }}</span>
                    </div>
                  </div>
                </td>
                
                <!-- Alamat -->
                <td class="px-6 py-6">
                  <div class="flex items-start gap-2 text-sm text-gray-600 max-w-[250px]">
                    <Icon icon="material-symbols:location-on-outline" class="w-4 h-4 text-gray-400 mt-0.5 shrink-0" />
                    <span class="line-clamp-2">{{ pengepul.alamat }}</span>
                  </div>
                </td>
                
                <!-- Tanggal Daftar -->
                <td class="px-6 py-6">
                  <div class="flex items-center gap-2 text-sm text-gray-600 font-medium">
                    <Icon icon="material-symbols:calendar-today-outline" class="w-4 h-4 text-gray-400" />
                    <span>{{ formatDate(pengepul.created_at) }}</span>
                  </div>
                </td>
                
                <!-- Aksi -->
                <td class="px-6 py-6">
                  <div class="flex items-center justify-center gap-2">
                    <button 
                      @click="handleApprove(pengepul)"
                      class="p-2 bg-[#4A7043] text-white rounded-lg hover:bg-[#3d5d37] transition-all shadow-sm group/btn"
                      title="Setujui"
                    >
                      <Icon icon="material-symbols:check-circle-outline" class="w-5 h-5" />
                    </button>
                    <button 
                      @click="openRejectModal(pengepul)"
                      class="p-2 bg-[#7A3E3E] text-white rounded-lg hover:bg-[#633232] transition-all shadow-sm"
                      title="Tolak"
                    >
                      <Icon icon="material-symbols:cancel-outline" class="w-5 h-5" />
                    </button>
                    <button 
                      @click="openDocModal(pengepul)"
                      class="p-2 bg-[#526D4E] text-white rounded-lg hover:bg-[#435940] transition-all shadow-sm"
                      title="Lihat Dokumen"
                    >
                      <Icon icon="material-symbols:description-outline" class="w-5 h-5" />
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Pagination -->
      <div class="bg-white rounded-2xl p-4 flex flex-col md:flex-row items-center justify-between gap-4 shadow-sm border border-gray-100">
        <div class="flex items-center gap-3">
          <span class="text-sm text-gray-500 font-medium">Tampilkan</span>
          <div class="relative">
            <select 
              v-model="itemsPerPage"
              class="appearance-none bg-gray-50 border border-gray-200 rounded-xl px-4 py-2 pr-10 text-sm font-semibold outline-none focus:ring-2 focus:ring-[#4A7043] transition-all"
            >
              <option :value="5">5</option>
              <option :value="10">10</option>
              <option :value="20">20</option>
              <option :value="50">50</option>
            </select>
            <Icon icon="material-symbols:keyboard-arrow-down" class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" />
          </div>
        </div>

        <div class="flex items-center gap-2">
          <button 
            @click="currentPage--"
            :disabled="currentPage === 1"
            class="px-4 py-2 rounded-xl text-sm font-semibold transition-all disabled:opacity-30 disabled:cursor-not-allowed hover:bg-gray-100 text-gray-600"
          >
            Sebelumnya
          </button>
          
          <div class="bg-[#4A7043] text-white px-4 py-2 rounded-xl text-sm font-bold shadow-sm">
            {{ currentPage }} / {{ totalPages || 1 }}
          </div>
          
          <button 
            @click="currentPage++"
            :disabled="currentPage === totalPages || totalPages === 0"
            class="px-4 py-2 rounded-xl text-sm font-semibold transition-all disabled:opacity-30 disabled:cursor-not-allowed hover:bg-gray-100 text-gray-600"
          >
            Selanjutnya
          </button>
        </div>
      </div>
    </div>

    <!-- Rejection Modal -->
    <div 
      v-if="isRejectModalOpen"
      class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-in fade-in duration-300"
    >
      <div class="bg-white w-full max-w-xl rounded-[32px] overflow-hidden shadow-2xl animate-in zoom-in-95 duration-300">
        <div class="p-8 space-y-6">
          <h2 class="text-2xl font-black text-gray-800">Tolak Pendaftaran</h2>
          <p class="text-base text-gray-500 font-medium leading-relaxed">
            Anda akan menolak pendaftaran <span class="font-extrabold text-gray-800">{{ selectedPengepul?.nama_lembaga }}</span>
          </p>
          
          <div class="space-y-2">
            <label class="text-xs font-black text-gray-400 uppercase tracking-widest ml-1">Alasan Penolakan *</label>
            <textarea 
              v-model="rejectionReason"
              placeholder="Jelaskan alasan penolakan (wajib diisi)"
              rows="4"
              class="w-full bg-gray-50 border border-gray-100 rounded-3xl p-5 focus:ring-2 focus:ring-[#7A3E3E] outline-none transition-all placeholder:text-gray-400 text-base text-gray-700 resize-none shadow-inner"
            ></textarea>
          </div>

          <div class="flex gap-4">
            <button 
              @click="isRejectModalOpen = false"
              class="flex-1 bg-gray-500 text-white py-4 rounded-2xl font-black text-base hover:bg-gray-600 transition-colors shadow-sm"
            >
              Batal
            </button>
            <button 
              @click="handleReject"
              :disabled="isSubmitting || !rejectionReason.trim()"
              class="flex-1 bg-[#7A3E3E] text-white py-4 rounded-2xl font-black text-base hover:bg-[#633232] transition-all shadow-md disabled:opacity-50"
            >
              {{ isSubmitting ? 'Memproses...' : 'Tolak Pendaftaran' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Document Modal -->
    <div 
      v-if="isDocModalOpen"
      class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-in fade-in duration-300"
    >
      <div class="bg-white w-full max-w-3xl rounded-[32px] overflow-hidden shadow-2xl animate-in zoom-in-95 duration-300">
        <div class="p-8 space-y-6">
          <div>
            <h2 class="text-2xl font-black text-gray-800">Dokumen Pendukung</h2>
            <p class="text-gray-500 font-medium">
              Dokumen untuk <span class="font-bold text-gray-800">{{ selectedPengepul?.nama_lembaga }}</span>
            </p>
          </div>
          
          <div class="space-y-4">
            <!-- KTP -->
            <div class="bg-gray-50 rounded-2xl p-6 flex items-center justify-between group hover:bg-gray-100 transition-colors border border-gray-100">
              <div class="flex items-center gap-4 min-w-0 flex-1 mr-4">
                <div class="p-3 bg-white rounded-xl shadow-sm text-[#4A7043] shrink-0">
                  <Icon icon="material-symbols:badge-outline" class="w-8 h-8" />
                </div>
                <div class="flex flex-col min-w-0">
                  <span class="font-extrabold text-gray-800 text-base uppercase">KTP (Kartu Tanda Penduduk)</span>
                  <span class="text-xs text-gray-400 mt-1 truncate" :title="selectedPengepul?.ktp">
                    {{ selectedPengepul?.ktp || 'Tidak ada file' }}
                  </span>
                </div>
              </div>
              <button 
                v-if="selectedPengepul?.ktp"
                @click="openImagePreview(getFileUrl(selectedPengepul.ktp), 'KTP')"
                class="bg-[#4A7043] text-white px-6 py-3 rounded-xl font-bold hover:bg-[#3d5d37] transition-all shadow-sm shrink-0 whitespace-nowrap"
              >
                Lihat Dokumen
              </button>
            </div>

            <!-- NPWP -->
            <div class="bg-gray-50 rounded-2xl p-6 flex items-center justify-between group hover:bg-gray-100 transition-colors border border-gray-100">
              <div class="flex items-center gap-4 min-w-0 flex-1 mr-4">
                <div class="p-3 bg-white rounded-xl shadow-sm text-[#4A7043] shrink-0">
                  <Icon icon="material-symbols:description-outline" class="w-8 h-8" />
                </div>
                <div class="flex flex-col min-w-0">
                  <span class="font-extrabold text-gray-800 text-base uppercase">NPWP</span>
                  <span class="text-xs text-gray-400 mt-1 truncate" :title="selectedPengepul?.npwp">
                    {{ selectedPengepul?.npwp || 'Tidak ada file' }}
                  </span>
                </div>
              </div>
              <button 
                v-if="selectedPengepul?.npwp"
                @click="openImagePreview(getFileUrl(selectedPengepul.npwp), 'NPWP')"
                class="bg-[#4A7043] text-white px-6 py-3 rounded-xl font-bold hover:bg-[#3d5d37] transition-all shadow-sm shrink-0 whitespace-nowrap"
              >
                Lihat Dokumen
              </button>
            </div>
          </div>

          <button 
            @click="isDocModalOpen = false"
            class="w-full bg-gray-500 text-white py-4 rounded-2xl font-black hover:bg-gray-600 transition-colors shadow-sm mt-4"
          >
            Tutup
          </button>
        </div>
      </div>
    </div>

    <!-- Image Preview Modal -->
    <div 
      v-if="isImagePreviewOpen"
      class="fixed inset-0 z-[110] flex items-center justify-center p-4 bg-black/80 backdrop-blur-md animate-in fade-in duration-300"
      @click="isImagePreviewOpen = false"
    >
      <div 
        class="relative bg-white max-w-4xl w-full rounded-[32px] overflow-hidden shadow-2xl p-6 flex flex-col items-center justify-center gap-4 animate-in zoom-in-95 duration-300"
        @click.stop
      >
        <!-- Header -->
        <div class="w-full flex items-center justify-between border-b border-gray-100 pb-4">
          <h3 class="text-xl font-black text-gray-800">Pratinjau Dokumen - {{ previewImageTitle }}</h3>
          <button 
            @click="isImagePreviewOpen = false"
            class="p-2 hover:bg-gray-100 rounded-full transition-colors text-gray-500 hover:text-gray-800"
          >
            <Icon icon="material-symbols:close" class="w-6 h-6" />
          </button>
        </div>

        <!-- Image Content -->
        <div class="w-full max-h-[70vh] flex items-center justify-center bg-gray-50 rounded-2xl overflow-hidden border border-gray-100 p-2">
          <!-- If image -->
          <img 
            v-if="isImage(previewImageSrc.split('/').pop())"
            :src="previewImageSrc" 
            class="max-w-full max-h-[65vh] object-contain rounded-xl shadow-sm" 
            alt="Preview Dokumen" 
          />
          <!-- If not image (e.g. PDF) -->
          <div v-else class="flex flex-col items-center gap-4 py-20 text-gray-500">
            <Icon icon="material-symbols:picture-as-pdf-outline" class="w-20 h-20 text-red-500" />
            <p class="font-bold text-lg">Format dokumen tidak mendukung pratinjau langsung.</p>
            <a 
              :href="previewImageSrc" 
              target="_blank" 
              class="bg-[#4A7043] text-white px-6 py-3 rounded-xl font-bold hover:bg-[#3d5d37] transition-all shadow-sm"
            >
              Unduh / Buka di Tab Baru
            </a>
          </div>
        </div>
      </div>
    </div>

  </DashboardLayout>
</template>

<style scoped>
.animate-in {
  animation-duration: 0.5s;
  animation-fill-mode: both;
}
.fade-in {
  animation-name: fadeIn;
}
.zoom-in-95 {
  animation-name: zoomIn95;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

@keyframes zoomIn95 {
  from { opacity: 0; transform: scale(0.95); }
  to { opacity: 1; transform: scale(1); }
}

/* Custom Select Styling */
select {
  background-image: none;
}

::-webkit-scrollbar {
  height: 6px;
  width: 6px;
}
::-webkit-scrollbar-track {
  background: transparent;
}
::-webkit-scrollbar-thumb {
  background: #e2e8f0;
  border-radius: 10px;
}
::-webkit-scrollbar-thumb:hover {
  background: #cbd5e0;
}
</style>
