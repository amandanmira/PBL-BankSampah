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

// Confirmation Modal states
const showCancelConfirmModal = ref(false);
const targetCancelId = ref(null);

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

const confirmCancel = (id) => {
  targetCancelId.value = id;
  showCancelConfirmModal.value = true;
};

const executeCancel = async () => {
  if (!targetCancelId.value) return;
  try {
    await axios.post(`/api/nasabah/penarikan/${targetCancelId.value}/cancel`);
    Swal.fire({
      title: 'Berhasil!',
      text: 'Penarikan telah dibatalkan.',
      icon: 'success',
      confirmButtonColor: '#4A7043'
    });
    showCancelConfirmModal.value = false;
    targetCancelId.value = null;
    fetchWithdrawals();
    if (showDetailModal.value) {
      closeDetail();
    }
  } catch (error) {
    Swal.fire('Gagal!', error.response?.data?.message || 'Gagal membatalkan penarikan.', 'error');
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
  <DashboardLayout title="Riwayat Penarikan">
    <!-- Main mobile viewport simulator -->
    <div class="max-w-md mx-auto bg-[#F7F7F5] min-h-screen pb-16 font-sans antialiased text-stone-800">
      
      <!-- Spacing and List Content -->
      <div class="p-4 space-y-4">

        <!-- Tabs status filter -->
        <div class="bg-white rounded-2xl p-1 shadow-sm border border-stone-200/50 flex overflow-x-auto no-scrollbar">
          <button
            v-for="filter in statusFilters"
            :key="filter.value"
            @click="activeStatusFilter = filter.value"
            :class="cn(
              'flex-1 min-w-[75px] flex flex-col items-center gap-1 py-3 transition-all relative border-b-2',
              activeStatusFilter === filter.value ? 'border-[#A86444] text-[#A86444] font-bold' : 'border-transparent text-stone-400 hover:text-stone-600'
            )"
          >
            <span class="text-xs">{{ filter.label }}</span>
            <div 
              :class="cn(
                'w-5.5 h-5.5 rounded-full flex items-center justify-center text-[10px] font-bold transition-all',
                activeStatusFilter === filter.value ? 'bg-[#A86444] text-white' : 'bg-stone-100 text-stone-400'
              )"
            >
              {{ counts[filter.value] || 0 }}
            </div>
          </button>
        </div>

        <!-- Search input field -->
        <div class="relative group">
          <Icon icon="material-symbols:search" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-stone-400 focus-within:text-[#A86444]" />
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Cari berdasarkan tracking ID atau metode..."
            class="w-full pl-11 pr-4 py-3 bg-white border border-stone-200 rounded-2xl focus:outline-none focus:border-[#A86444] transition-all text-xs"
          />
        </div>

        <!-- List View -->
        <div class="space-y-4">
          <div v-if="loading" class="flex flex-col items-center justify-center py-20 gap-3">
            <div class="w-10 h-10 border-4 border-[#A86444]/20 border-t-[#A86444] rounded-full animate-spin"></div>
            <p class="text-stone-400 text-xs italic">Menarik data penarikan...</p>
          </div>

          <template v-else>
            <div v-if="withdrawals.length === 0" class="flex flex-col items-center justify-center py-16 text-center space-y-3 bg-white rounded-2xl p-6 border border-stone-100">
              <div class="w-16 h-16 bg-stone-50 rounded-full flex items-center justify-center text-stone-300">
                <Icon icon="material-symbols:info-outline" class="w-8 h-8" />
              </div>
              <div>
                <p class="text-stone-800 font-bold text-sm">Tidak ada data ditemukan</p>
                <p class="text-xs text-stone-400">Coba ubah filter atau kata kunci pencarian Anda.</p>
              </div>
            </div>

            <!-- List card items -->
            <div
              v-for="item in withdrawals"
              :key="item.penarikan_id"
              class="bg-white border border-stone-200/60 rounded-2xl p-4 shadow-sm space-y-4"
            >
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-stone-50 rounded-xl flex items-center justify-center text-stone-600 shrink-0">
                  <Icon icon="material-symbols:account-balance-wallet-outline" class="w-5 h-5 text-stone-500" />
                </div>
                <div>
                  <p class="text-[9px] font-bold text-stone-400 uppercase tracking-wider">Tracking ID</p>
                  <h4 class="text-sm font-bold text-stone-800">
                    #WD-{{ String(item.penarikan_id).padStart(3, '0') }}
                  </h4>
                </div>
              </div>

              <!-- Grey Nested Card for Amount -->
              <div class="bg-[#FDF8F6] p-4 rounded-xl border border-stone-100/50">
                <p class="text-[9px] font-semibold text-stone-400 uppercase tracking-wider mb-0.5">Jumlah Penarikan</p>
                <p class="text-xl font-bold text-[#A86444]">
                  Rp {{ item.jumlah.toLocaleString('id-ID') }}
                </p>
              </div>

              <div class="flex items-center gap-1.5 text-stone-400 text-xs">
                <Icon icon="material-symbols:calendar-today-outline" class="w-4 h-4" />
                <span>{{ formatDate(item.created_at, true) }}</span>
              </div>

              <!-- Buttons -->
              <div class="flex gap-2">
                <button 
                  @click="openDetail(item)"
                  class="flex-1 bg-[#925F3A] hover:bg-[#805030] text-white py-3 rounded-xl font-bold text-xs transition-all flex items-center justify-center gap-1.5 active:scale-[0.98]"
                >
                  <span>Lihat Detail</span>
                  <Icon icon="material-symbols:arrow-right-alt" class="w-4 h-4" />
                </button>
                <button
                  v-if="item.status === 'pending'"
                  @click="confirmCancel(item.penarikan_id)"
                  class="px-5 border border-red-200 text-red-500 hover:bg-red-50 rounded-xl font-bold text-xs transition-all flex items-center justify-center gap-1 active:scale-[0.98]"
                >
                  <Icon icon="material-symbols:cancel-outline" class="w-4.5 h-4.5" />
                  <span>Batalkan</span>
                </button>
              </div>
            </div>
          </template>
        </div>
      </div>
    </div>

    <!-- Detail Modal -->
    <div v-if="showDetailModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-in fade-in duration-300">
      <div class="relative bg-[#F7F7F5] w-full max-w-md max-h-[90vh] overflow-y-auto rounded-[2rem] shadow-2xl animate-in zoom-in-95 duration-300 no-scrollbar">
        
        <!-- Header -->
        <div class="bg-[#925F3A] px-6 py-4 flex items-center justify-between border-b border-white/10 text-white shrink-0 sticky top-0 z-10 shadow-sm">
          <div>
            <h2 class="text-base font-bold">Detail Penarikan</h2>
            <span class="text-[10px] text-white/70">#WD-{{ String(selectedItem.penarikan_id).padStart(3, '0') }}</span>
          </div>
          <button @click="closeDetail" class="w-8 h-8 bg-white/10 text-white hover:bg-white/20 rounded-full flex items-center justify-center transition-all">
            <Icon icon="material-symbols:close" class="w-4.5 h-4.5" />
          </button>
        </div>

        <div class="p-4 space-y-4">
          <!-- Card 1: Jumlah Penarikan Details -->
          <div class="bg-white rounded-2xl p-4 shadow-sm border border-stone-100 space-y-3">
            <div>
              <p class="text-[9px] text-stone-400 font-bold uppercase tracking-wider mb-0.5">Jumlah Penarikan</p>
              <p class="text-2xl font-bold text-[#A86444]">Rp {{ selectedItem.jumlah.toLocaleString('id-ID') }}</p>
            </div>
            
            <!-- Pending/Wait/Cancel details breakdown -->
            <div class="pt-3 border-t border-stone-100 space-y-2 text-xs">
              <div class="flex justify-between items-center text-stone-500">
                <span>Jumlah Penarikan</span>
                <span class="font-bold text-stone-800">Rp {{ selectedItem.jumlah.toLocaleString('id-ID') }}</span>
              </div>
              <div class="flex justify-between items-center text-stone-500">
                <span>Biaya Admin</span>
                <span class="font-bold text-red-500">- Rp {{ (selectedItem.jumlah * 0.01).toLocaleString('id-ID') }}</span>
              </div>
              <div class="flex justify-between items-center font-bold text-stone-800 pt-1 border-t border-stone-100/50">
                <span>Total Diterima</span>
                <span class="text-[#4A7043]">Rp {{ (selectedItem.jumlah - (selectedItem.jumlah * 0.01)).toLocaleString('id-ID') }}</span>
              </div>
            </div>
          </div>

          <!-- Card 2: Rekening Tujuan -->
          <div class="bg-white rounded-2xl p-4 shadow-sm border border-stone-100 space-y-3">
            <div class="flex items-center gap-2 border-b border-stone-100 pb-2">
              <Icon icon="material-symbols:account-balance-outline" class="w-5 h-5 text-[#925F3A]" />
              <h3 class="text-xs font-bold text-stone-800">Rekening Tujuan</h3>
            </div>
            <div class="space-y-3 text-xs pl-7">
              <div>
                <p class="text-[9px] text-stone-400 uppercase tracking-wider font-bold">Bank</p>
                <p class="font-bold text-stone-800">{{ selectedItem.nama_bank || '-' }}</p>
              </div>
              <div>
                <p class="text-[9px] text-stone-400 uppercase tracking-wider font-bold">Nomor Rekening</p>
                <p class="font-mono font-bold text-stone-800">{{ selectedItem.no_rekening || '-' }}</p>
              </div>
              <div>
                <p class="text-[9px] text-stone-400 uppercase tracking-wider font-bold">Atas Nama</p>
                <p class="font-bold text-stone-800">{{ selectedItem.nama_rek || '-' }}</p>
              </div>
            </div>
          </div>

          <!-- Card 3: Timeline -->
          <div class="bg-white rounded-2xl p-4 shadow-sm border border-stone-100 space-y-3">
            <div class="flex items-center gap-2 border-b border-stone-100 pb-2">
              <Icon icon="material-symbols:history" class="w-5 h-5 text-[#925F3A]" />
              <h3 class="text-xs font-bold text-stone-800">Timeline</h3>
            </div>
            
            <div class="space-y-4 pl-3">
              <!-- Created -->
              <div class="flex gap-3 relative">
                <div class="absolute left-3.5 top-8 bottom-0 w-[1.5px] bg-stone-200" v-if="selectedItem.status !== 'pending'"></div>
                <div class="w-7 h-7 rounded-full bg-[#925F3A] text-white flex items-center justify-center shrink-0 z-10">
                  <Icon icon="material-symbols:calendar-today" class="w-4 h-4" />
                </div>
                <div class="text-xs pt-0.5">
                  <p class="font-bold text-stone-800">Request Dibuat</p>
                  <p class="text-[10px] text-stone-400">{{ formatDate(selectedItem.created_at, true) }}</p>
                </div>
              </div>

              <!-- State changes -->
              <div v-if="selectedItem.status !== 'pending'" class="flex gap-3 relative">
                <div 
                  :class="cn(
                    'w-7 h-7 rounded-full text-white flex items-center justify-center shrink-0 z-10',
                    selectedItem.status === 'selesai' ? 'bg-[#4A7043]' : 'bg-red-500'
                  )"
                >
                  <Icon 
                    :icon="selectedItem.status === 'selesai' ? 'material-symbols:check-circle' : 'material-symbols:cancel'" 
                    class="w-4 h-4" 
                  />
                </div>
                <div class="text-xs pt-0.5">
                  <p class="font-bold text-stone-800">
                    {{ selectedItem.status === 'selesai' ? 'Transfer Selesai' : selectedItem.status === 'tolak' ? 'Ditolak' : 'Dibatalkan' }}
                  </p>
                  <p class="text-[10px] text-stone-400">{{ formatDate(selectedItem.updated_at, true) }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Card 4: Bukti Transfer (Selesai status only) -->
          <div v-if="selectedItem.status === 'selesai'" class="bg-white rounded-2xl p-4 shadow-sm border border-stone-100 space-y-3">
            <div class="flex items-center gap-2 border-b border-stone-100 pb-2">
              <Icon icon="material-symbols:verified-user-outline" class="w-5 h-5 text-[#4A7043]" />
              <h3 class="text-xs font-bold text-stone-800">Bukti Transfer</h3>
            </div>
            <div class="bg-stone-50 rounded-xl overflow-hidden border border-stone-100 p-1.5 max-w-full">
              <img 
                v-if="selectedItem.bukti_tf"
                :src="`${axios.defaults.baseURL}/storage/${selectedItem.bukti_tf}`" 
                class="w-full h-auto rounded-lg" 
                alt="Bukti Transfer" 
              />
              <div v-else class="py-6 flex flex-col items-center justify-center text-stone-300 gap-1">
                <Icon icon="material-symbols:image-not-supported-outline" class="w-8 h-8" />
                <p class="text-[9px] font-bold uppercase tracking-wider">Bukti belum diupload</p>
              </div>
            </div>
          </div>

          <!-- Ditolak Alert Card -->
          <div v-if="selectedItem.status === 'tolak'" class="bg-red-50 border border-red-100 rounded-xl p-4 space-y-2">
            <div class="flex items-center gap-2 text-red-600">
              <Icon icon="material-symbols:error-outline" class="w-5 h-5" />
              <span class="font-bold text-xs uppercase tracking-wider">Penarikan Ditolak</span>
            </div>
            <div>
              <p class="text-[9px] text-stone-400 font-bold uppercase tracking-wider">Alasan Penolakan:</p>
              <p class="text-xs font-bold text-red-700 leading-relaxed">{{ selectedItem.ket_status || 'Saldo tidak mencukupi' }}</p>
            </div>
          </div>

          <!-- Dibatalkan Alert Card -->
          <div v-if="selectedItem.status === 'batal'" class="bg-[#FFF8F6] border border-orange-200/60 rounded-xl p-4 space-y-2">
            <div class="flex items-center gap-2 text-orange-600">
              <Icon icon="material-symbols:cancel-outline" class="w-5 h-5" />
              <span class="font-bold text-xs uppercase tracking-wider">Penarikan Dibatalkan</span>
            </div>
            <div class="bg-white rounded-lg p-2.5 border border-orange-100 shadow-sm flex gap-2">
              <Icon icon="material-symbols:info-outline" class="w-4 h-4 text-blue-500 shrink-0 mt-0.5" />
              <p class="text-[9px] text-stone-500 leading-normal">
                <strong>Informasi:</strong> Penarikan ini telah dibatalkan. Saldo Anda tetap utuh dan tidak berkurang. Anda dapat membuat penarikan baru kapan saja.
              </p>
            </div>
          </div>

          <!-- Bottom Close / Cancel trigger inside details -->
          <div class="flex gap-2">
            <button
              v-if="selectedItem.status === 'pending'"
              @click="confirmCancel(selectedItem.penarikan_id)"
              class="flex-1 py-3 border border-red-200 text-red-500 hover:bg-red-50 rounded-xl font-bold text-xs transition-all active:scale-[0.98]"
            >
              Batalkan Penarikan
            </button>
            <button 
              @click="closeDetail"
              class="flex-1 py-3 rounded-xl bg-[#925F3A] hover:bg-[#805030] text-white font-bold text-xs transition-all text-center"
            >
              Tutup
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Confirmation Modal for cancellation -->
    <div v-if="showCancelConfirmModal" class="fixed inset-0 z-[120] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-in fade-in duration-300">
      <div class="bg-white rounded-2xl w-full max-w-xs p-6 flex flex-col items-start gap-4 shadow-2xl animate-in zoom-in-95 duration-300 text-stone-800">
        <div>
          <h3 class="text-base font-bold text-stone-800">Konfirmasi Persetujuan</h3>
          <p class="text-xs text-stone-500 mt-2 leading-relaxed">Apakah Anda yakin untuk membatalkan penarikan ini?</p>
        </div>
        <div class="flex w-full gap-2.5 justify-end">
          <button 
            @click="showCancelConfirmModal = false; targetCancelId = null"
            class="px-4 py-2 border border-stone-200 text-stone-500 rounded-lg text-xs font-bold hover:bg-stone-50 transition-all active:scale-[0.97]"
          >
            Batal
          </button>
          <button 
            @click="executeCancel"
            class="px-4 py-2 bg-[#4A7043] text-white rounded-lg text-xs font-bold hover:bg-[#3D5C37] transition-all active:scale-[0.97] shadow-sm"
          >
            Ya, Setujui
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
