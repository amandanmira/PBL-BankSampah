<script setup>
import { ref, computed, onMounted, watch } from "vue";
import { inject } from "vue";
import DashboardLayout from "@/layouts/DashboardLayout.vue";
import { Icon } from "@iconify/vue";
import { cn } from "@/lib/utils";
import Swal from "sweetalert2";

const axios = inject('axios');
const loading = ref(false);
const withdrawals = ref([]);
const counts = ref({
  menunggu: 0,
  selesai: 0,
  ditolak: 0,
  dibatalkan: 0
});
const activeStatusFilter = ref("menunggu"); // menunggu, selesai, ditolak, dibatalkan
const searchQuery = ref("");
const showDetailModal = ref(false);
const selectedItem = ref(null);

const statusFilters = [
  { label: "Menunggu", value: "menunggu", icon: "material-symbols:schedule" },
  { label: "Selesai", value: "selesai", icon: "material-symbols:check-circle" },
  { label: "Ditolak", value: "ditolak", icon: "material-symbols:cancel" },
  { label: "Dibatalkan", value: "dibatalkan", icon: "material-symbols:block" },
];

const fetchWithdrawals = async () => {
  loading.value = true;
  try {
    const res = await axios.get("/api/nasabah/penarikan-saya", {
      params: {
        status: activeStatusFilter.value,
        search: searchQuery.value
      }
    });
    withdrawals.value = res.data.penarikan.data || [];
    counts.value = res.data.counts;
  } catch (error) {
    console.error("Failed to fetch withdrawals:", error);
  } finally {
    loading.value = false;
  }
};

watch([activeStatusFilter], () => {
  fetchWithdrawals();
});

// Debounce search
let searchTimeout;
watch(searchQuery, () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    fetchWithdrawals();
  }, 500);
});

const openDetail = (item) => {
  selectedItem.value = item;
  showDetailModal.value = true;
};

const closeDetail = () => {
  showDetailModal.value = false;
  selectedItem.value = null;
};

const cancelWithdrawal = async (id) => {
  const result = await Swal.fire({
    title: 'Apakah Anda yakin?',
    text: "Penarikan ini akan dibatalkan!",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Ya, Batalkan!',
    cancelButtonText: 'Kembali'
  });

  if (result.isConfirmed) {
    try {
      await axios.post(`/api/nasabah/penarikan/${id}/cancel`);
      Swal.fire('Berhasil!', 'Penarikan telah dibatalkan.', 'success');
      fetchWithdrawals();
    } catch (error) {
      Swal.fire('Gagal!', error.response?.data?.message || 'Gagal membatalkan penarikan.', 'error');
    }
  }
};

const formatDate = (dateString, includeTime = false) => {
  if (!dateString) return "-";
  const date = new Date(dateString);
  const options = { day: 'numeric', month: 'long', year: 'numeric' };
  if (includeTime) {
    return `${date.toLocaleDateString('id-ID', options)} pukul ${date.getHours().toString().padStart(2, '0')}.${date.getMinutes().toString().padStart(2, '0')}`;
  }
  return date.toLocaleDateString('id-ID', options);
};

onMounted(() => {
  fetchWithdrawals();
});
</script>

<template>
  <DashboardLayout title="Penarikan Saya">
    <div class="space-y-6">
      <!-- Filter and Content Area -->
      <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <!-- Status Filter Tabs -->
        <div class="flex border-b border-gray-50 overflow-x-auto no-scrollbar">
          <button
            v-for="filter in statusFilters"
            :key="filter.value"
            @click="activeStatusFilter = filter.value"
            :class="cn(
              'flex-1 min-w-[120px] flex flex-col items-center gap-1 py-4 transition-all relative',
              activeStatusFilter === filter.value ? 'text-[#A86444]' : 'text-gray-400 hover:text-gray-600'
            )"
          >
            <span class="text-xs font-bold">{{ filter.label }}</span>
            <div 
              :class="cn(
                'w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-black transition-all',
                activeStatusFilter === filter.value ? 'bg-[#A86444] text-white' : 'bg-gray-100 text-gray-500'
              )"
            >
              {{ counts[filter.value] || 0 }}
            </div>
            <!-- Active Indicator -->
            <div 
              v-if="activeStatusFilter === filter.value" 
              class="absolute bottom-0 left-4 right-4 h-1 bg-[#A86444] rounded-t-full"
            ></div>
          </button>
        </div>

        <!-- Search Bar -->
        <div class="p-6">
          <div class="relative group">
            <Icon icon="material-symbols:search" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400 group-focus-within:text-[#A86444] transition-colors" />
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Cari berdasarkan tracking ID atau metode..."
              class="w-full pl-12 pr-4 py-3 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-[#A86444]/20 transition-all text-sm outline-none"
            />
          </div>
        </div>

        <!-- List View -->
        <div class="px-6 pb-6 space-y-4">
          <div v-if="loading" class="flex flex-col items-center justify-center py-20 gap-4">
            <div class="w-12 h-12 border-4 border-[#A86444]/20 border-t-[#A86444] rounded-full animate-spin"></div>
            <p class="text-gray-500 font-medium italic">Menarik data penarikan...</p>
          </div>

          <template v-else>
            <div v-if="withdrawals.length === 0" class="flex flex-col items-center justify-center py-20 text-center space-y-4">
              <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center">
                <Icon icon="material-symbols:info-outline" class="w-10 h-10 text-gray-300" />
              </div>
              <div>
                <p class="text-gray-800 font-bold">Tidak ada data ditemukan</p>
                <p class="text-sm text-gray-500">Coba ubah filter atau kata kunci pencarian Anda.</p>
              </div>
            </div>

            <div
              v-for="item in withdrawals"
              :key="item.penarikan_id"
              class="group bg-white border border-gray-100 rounded-3xl p-5 hover:border-[#A86444]/30 hover:shadow-xl hover:shadow-[#A86444]/5 transition-all duration-500"
            >
              <div class="flex flex-col md:flex-row gap-6">
                <!-- Status Icon -->
                <div class="w-14 h-14 bg-[#FDF8F6] rounded-2xl flex items-center justify-center shrink-0">
                  <Icon icon="material-symbols:account-balance-wallet-outline" class="w-7 h-7 text-[#A86444]" />
                </div>

                <!-- Content -->
                <div class="flex-1 space-y-3">
                  <div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">
                      Tracking ID
                    </p>
                    <h3 class="text-lg font-black text-gray-800">
                      #WD-{{ String(item.penarikan_id).padStart(3, '0') }}
                    </h3>
                  </div>

                  <div class="bg-[#FDF8F6] px-6 py-4 rounded-2xl border border-[#A86444]/5">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Jumlah Penarikan</p>
                    <p class="text-2xl font-black text-[#A86444]">Rp {{ item.jumlah.toLocaleString('id-ID') }}</p>
                  </div>

                  <div class="flex items-center gap-2 text-gray-500">
                    <Icon icon="material-symbols:calendar-today-outline" class="w-4 h-4" />
                    <span class="text-xs font-bold">{{ formatDate(item.created_at, true) }}</span>
                  </div>

                  <!-- Buttons -->
                  <div class="flex gap-3 pt-2">
                    <button 
                      @click="openDetail(item)"
                      class="flex-1 bg-[#A86444] hover:bg-[#8B5238] text-white py-3 rounded-2xl font-bold text-sm transition-all flex items-center justify-center gap-2 shadow-lg shadow-[#A86444]/20 active:scale-[0.98]"
                    >
                      <span>Lihat Detail</span>
                      <Icon icon="material-symbols:arrow-right-alt" class="w-5 h-5" />
                    </button>
                    <button
                      v-if="item.status === 'pending'"
                      @click="cancelWithdrawal(item.penarikan_id)"
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
    <div v-if="showDetailModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6">
      <!-- Backdrop -->
      <div class="absolute inset-0 bg-black/60 backdrop-blur-sm animate-in fade-in duration-300" @click="closeDetail"></div>
      
      <!-- Modal Content -->
      <div class="relative bg-[#FDFDFB] w-full max-w-xl max-h-[90vh] overflow-y-auto rounded-[2.5rem] shadow-2xl animate-in zoom-in-95 duration-300 no-scrollbar">
        <!-- Header -->
        <div class="sticky top-0 z-10 bg-[#A86444] px-8 py-6 flex items-center justify-between border-b border-white/10 shadow-lg">
          <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-white/10 rounded-2xl flex items-center justify-center text-white shrink-0">
              <Icon icon="material-symbols:account-balance-wallet-outline" class="w-7 h-7" />
            </div>
            <div>
              <h2 class="text-xl font-black text-white">Detail Penarikan</h2>
              <p class="text-[10px] font-black text-white/60 uppercase tracking-widest mt-0.5">#WD-{{ String(selectedItem.penarikan_id).padStart(3, '0') }}</p>
            </div>
          </div>
          <button @click="closeDetail" class="w-10 h-10 bg-white/10 text-white hover:bg-white/20 rounded-full flex items-center justify-center transition-all">
            <Icon icon="material-symbols:close" class="w-6 h-6" />
          </button>
        </div>

        <div class="p-8 space-y-6">
          <!-- Amount Section -->
          <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-gray-100">
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Jumlah Penarikan</p>
            <p class="text-3xl font-black text-[#A86444]">Rp {{ selectedItem.jumlah.toLocaleString('id-ID') }}</p>
          </div>

          <!-- Destination Account -->
          <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-gray-100 space-y-4">
            <div class="flex items-center gap-3 mb-2">
              <Icon icon="material-symbols:account-balance-outline" class="w-6 h-6 text-[#A86444]" />
              <h3 class="font-black text-gray-800">Rekening Tujuan</h3>
            </div>
            <div class="space-y-4 pl-9">
              <div>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Bank</p>
                <p class="font-black text-gray-700">{{ selectedItem.nama_bank }}</p>
              </div>
              <div>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Nomor Rekening</p>
                <p class="font-black text-gray-700 tracking-wider">{{ selectedItem.no_rekening }}</p>
              </div>
              <div>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Atas Nama</p>
                <p class="font-black text-gray-700">{{ selectedItem.nama_rek }}</p>
              </div>
            </div>
          </div>

          <!-- Timeline -->
          <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-gray-100">
            <div class="flex items-center gap-3 mb-6">
              <Icon icon="material-symbols:history" class="w-6 h-6 text-[#A86444]" />
              <h3 class="font-black text-gray-800">Timeline</h3>
            </div>
            
            <div class="space-y-6 pl-2">
              <!-- Created -->
              <div class="flex gap-4 relative">
                <div class="absolute left-5 top-10 bottom-0 w-[2px] bg-gray-100" v-if="selectedItem.status !== 'pending'"></div>
                <div class="w-10 h-10 rounded-full bg-[#A86444] flex items-center justify-center text-white shrink-0 z-10">
                  <Icon icon="material-symbols:calendar-today" class="w-5 h-5" />
                </div>
                <div class="pt-1">
                  <p class="font-black text-sm text-gray-800">Request Dibuat</p>
                  <p class="text-[10px] font-bold text-gray-400">{{ formatDate(selectedItem.created_at, true) }}</p>
                </div>
              </div>

              <!-- Finished/Rejected/Cancelled -->
              <div v-if="selectedItem.status !== 'pending'" class="flex gap-4 relative">
                <div 
                  :class="cn(
                    'w-10 h-10 rounded-full flex items-center justify-center text-white shrink-0 z-10',
                    selectedItem.status === 'selesai' ? 'bg-[#4A7043]' : 
                    selectedItem.status === 'tolak' ? 'bg-red-500' : 'bg-orange-500'
                  )"
                >
                  <Icon 
                    :icon="
                      selectedItem.status === 'selesai' ? 'material-symbols:check-circle' : 
                      selectedItem.status === 'tolak' ? 'material-symbols:cancel' : 'material-symbols:block'
                    " 
                    class="w-5 h-5" 
                  />
                </div>
                <div class="pt-1">
                  <p class="font-black text-sm text-gray-800">
                    {{ 
                      selectedItem.status === 'selesai' ? 'Transfer Selesai' : 
                      selectedItem.status === 'tolak' ? 'Request Ditolak' : 'Dibatalkan'
                    }}
                  </p>
                  <p class="text-[10px] font-bold text-gray-400">{{ formatDate(selectedItem.updated_at, true) }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Proof of Transfer (Only for Selesai) -->
          <div v-if="selectedItem.status === 'selesai'" class="bg-white rounded-[2rem] p-6 shadow-sm border border-gray-100">
            <div class="flex items-center gap-3 mb-6">
              <Icon icon="material-symbols:verified-user-outline" class="w-6 h-6 text-[#4A7043]" />
              <h3 class="font-black text-gray-800">Bukti Transfer</h3>
            </div>
            <div class="bg-gray-50 rounded-3xl overflow-hidden border border-gray-100 p-2">
              <img 
                v-if="selectedItem.bukti_tf"
                :src="`${axios.defaults.baseURL}/storage/${selectedItem.bukti_tf}`" 
                class="w-full h-auto rounded-2xl" 
                alt="Bukti Transfer" 
              />
              <div v-else class="py-10 flex flex-col items-center justify-center text-gray-300 gap-2">
                <Icon icon="material-symbols:image-not-supported-outline" class="w-12 h-12" />
                <p class="text-xs font-bold uppercase tracking-widest">Bukti transfer belum tersedia</p>
              </div>
            </div>
          </div>

          <!-- Rejection Reason (Only for Ditolak) -->
          <div v-if="selectedItem.status === 'tolak'" class="bg-red-50 rounded-[2rem] p-6 border border-red-100 space-y-3">
            <div class="flex items-center gap-3 text-red-600">
              <Icon icon="material-symbols:error-outline" class="w-6 h-6" />
              <span class="font-black text-sm uppercase tracking-wider">Informasi Penolakan</span>
            </div>
            <div>
              <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Alasan Petugas:</p>
              <p class="text-sm font-bold text-red-700 leading-relaxed">{{ selectedItem.ket_status || 'Alasan tidak diberikan.' }}</p>
            </div>
          </div>

          <!-- Cancellation Info (Only for Dibatalkan) -->
          <div v-if="selectedItem.status === 'batal'" class="bg-orange-50 rounded-[2rem] p-6 border border-orange-100 space-y-4">
            <div class="flex items-center gap-3 text-orange-600">
              <Icon icon="material-symbols:cancel-outline" class="w-6 h-6" />
              <span class="font-black text-sm uppercase tracking-wider">Anda Membatalkan Penarikan Ini</span>
            </div>
            <div class="bg-white rounded-2xl p-4 border border-orange-100 shadow-sm flex gap-3">
              <div class="w-8 h-8 bg-blue-50 rounded-lg flex items-center justify-center text-blue-500 shrink-0">
                <Icon icon="material-symbols:info-outline" class="w-5 h-5" />
              </div>
              <div>
                <p class="text-[10px] font-black text-gray-800 uppercase tracking-widest mb-1">Informasi:</p>
                <p class="text-[10px] font-bold text-gray-500 leading-relaxed">Penarikan ini telah dibatalkan. Saldo Anda tetap utuh dan tidak berkurang. Anda dapat membuat penarikan baru kapan saja.</p>
              </div>
            </div>
          </div>

          <!-- Close Button -->
          <button 
            @click="closeDetail"
            class="w-full py-4 rounded-2xl bg-[#A86444] hover:bg-[#8B5238] text-white font-black text-sm transition-all shadow-lg active:scale-[0.98]"
          >
            Tutup
          </button>
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
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}
.animate-spin {
  animation: spin 1s linear infinite;
}
</style>
