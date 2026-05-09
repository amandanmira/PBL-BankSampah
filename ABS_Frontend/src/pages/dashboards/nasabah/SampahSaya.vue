<script setup>
import { ref, computed, onMounted, watch } from "vue";
import { inject } from "vue";
import DashboardLayout from "@/layouts/DashboardLayout.vue";
import { Icon } from "@iconify/vue";
import { cn } from "@/lib/utils";
import Swal from "sweetalert2";

const axios = inject('axios');
const loading = ref(false);
const transactions = ref([]);
const activeMainTab = ref("jemput"); // 'jemput' or 'setor'
const activeStatusFilter = ref("pending"); // pending, proses, dijemput, selesai, tolak, batal
const searchQuery = ref("");

const statusFilters = [
  { label: "Menunggu", value: "pending", icon: "material-symbols:schedule" },
  { label: "Diproses", value: "proses", icon: "material-symbols:sync" },
  { label: "Dijemput", value: "dijemput", icon: "material-symbols:local-shipping" },
  { label: "Selesai", value: "selesai", icon: "material-symbols:check-circle" },
  { label: "Ditolak", value: "tolak", icon: "material-symbols:cancel" },
  { label: "Dibatalkan", value: "batal", icon: "material-symbols:block" },
];

const fetchTransactions = async () => {
  loading.value = true;
  try {
    const response = await axios.get("/api/nasabah/penjemputan-nasabah");
    transactions.value = response.data.data || [];
  } catch (error) {
    console.error("Failed to fetch transactions:", error);
  } finally {
    loading.value = false;
  }
};

const counts = computed(() => {
  const result = {
    pending: 0,
    proses: 0,
    dijemput: 0,
    selesai: 0,
    tolak: 0,
    batal: 0,
  };
  transactions.value.forEach(t => {
    if (result[t.status] !== undefined) {
      result[t.status]++;
    }
  });
  return result;
});

const filteredTransactions = computed(() => {
  return transactions.value.filter(t => {
    const matchesStatus = t.status === activeStatusFilter.value;
    const matchesSearch = searchQuery.value === "" || 
      t.penjemputan_id.toString().includes(searchQuery.value) ||
      (t.deskripsi && t.deskripsi.toLowerCase().includes(searchQuery.value.toLowerCase())) ||
      (t.detail_penjemputan && t.detail_penjemputan.some(d => d.sampah?.item_sampah?.nama.toLowerCase().includes(searchQuery.value.toLowerCase())));
    
    return matchesStatus && matchesSearch;
  });
});

const cancelTransaction = async (id) => {
  const result = await Swal.fire({
    title: 'Apakah Anda yakin?',
    text: "Penjemputan ini akan dibatalkan!",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Ya, Batalkan!',
    cancelButtonText: 'Kembali'
  });

  if (result.isConfirmed) {
    try {
      await axios.post(`/api/nasabah/penjemputan/${id}/cancel`);
      Swal.fire('Berhasil!', 'Penjemputan telah dibatalkan.', 'success');
      fetchTransactions();
    } catch (error) {
      Swal.fire('Gagal!', error.response?.data?.message || 'Gagal membatalkan penjemputan.', 'error');
    }
  }
};

const formatDate = (dateString) => {
  if (!dateString) return "-";
  const date = new Date(dateString);
  return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
};

onMounted(() => {
  fetchTransactions();
});
</script>

<template>
  <DashboardLayout title="Sampah Saya">
    <div class="space-y-6">
      <!-- Main Tab Switcher -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-1 flex">
        <button
          @click="activeMainTab = 'jemput'"
          :class="cn(
            'flex-1 flex items-center justify-center gap-2 py-4 rounded-xl transition-all duration-300 font-bold',
            activeMainTab === 'jemput' ? 'bg-[#4A7043] text-white shadow-md' : 'text-gray-500 hover:bg-gray-50'
          )"
        >
          <Icon icon="material-symbols:local-shipping-outline" class="w-6 h-6" />
          <span>Jemput Sampah</span>
        </button>
        <button
          @click="activeMainTab = 'setor'"
          :class="cn(
            'flex-1 flex items-center justify-center gap-2 py-4 rounded-xl transition-all duration-300 font-bold',
            activeMainTab === 'setor' ? 'bg-[#4A7043] text-white shadow-md' : 'text-gray-500 hover:bg-gray-50'
          )"
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
            v-for="filter in statusFilters"
            :key="filter.value"
            @click="activeStatusFilter = filter.value"
            :class="cn(
              'flex-1 min-w-[120px] flex flex-col items-center gap-1 py-4 transition-all relative',
              activeStatusFilter === filter.value ? 'text-[#4A7043]' : 'text-gray-400 hover:text-gray-600'
            )"
          >
            <span class="text-xs font-bold uppercase tracking-wider">{{ filter.label }}</span>
            <div 
              :class="cn(
                'w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-black transition-all',
                activeStatusFilter === filter.value ? 'bg-[#4A7043] text-white' : 'bg-gray-100 text-gray-500'
              )"
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
            <Icon icon="material-symbols:search" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400 group-focus-within:text-[#4A7043] transition-colors" />
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Cari berdasarkan tracking ID atau jenis sampah..."
              class="w-full pl-12 pr-4 py-3 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-[#4A7043]/20 transition-all text-sm outline-none"
            />
          </div>
        </div>

        <!-- List View -->
        <div class="px-6 pb-6 space-y-4">
          <div v-if="loading" class="flex flex-col items-center justify-center py-20 gap-4">
            <div class="w-12 h-12 border-4 border-[#4A7043]/20 border-t-[#4A7043] rounded-full animate-spin"></div>
            <p class="text-gray-500 font-medium italic">Menarik data penjemputan...</p>
          </div>

          <template v-else>
            <div v-if="filteredTransactions.length === 0" class="flex flex-col items-center justify-center py-20 text-center space-y-4">
              <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center">
                <Icon icon="material-symbols:info-outline" class="w-10 h-10 text-gray-300" />
              </div>
              <div>
                <p class="text-gray-800 font-bold">Tidak ada data ditemukan</p>
                <p class="text-sm text-gray-500">Coba ubah filter atau kata kunci pencarian Anda.</p>
              </div>
            </div>

            <div
              v-for="item in filteredTransactions"
              :key="item.penjemputan_id"
              class="group bg-white border border-gray-100 rounded-3xl p-5 hover:border-[#4A7043]/30 hover:shadow-xl hover:shadow-[#4A7043]/5 transition-all duration-500"
            >
              <div class="flex flex-col md:flex-row gap-6">
                <!-- Status Icon & Image -->
                <div class="flex gap-4">
                  <div class="w-14 h-14 bg-gray-50 rounded-2xl flex items-center justify-center shrink-0">
                    <Icon icon="material-symbols:local-shipping-outline" class="w-7 h-7 text-gray-400" />
                  </div>
                  <div class="w-24 h-24 rounded-2xl overflow-hidden bg-gray-100 shrink-0 border border-gray-50">
                    <img 
                      v-if="item.foto && item.foto.length > 0" 
                      :src="`${axios.defaults.baseURL}/storage/${item.foto[0]}`" 
                      class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700" 
                      alt="Sampah" 
                    />
                    <div v-else class="w-full h-full flex items-center justify-center text-gray-300">
                      <Icon icon="material-symbols:image-outline" class="w-8 h-8" />
                    </div>
                  </div>
                </div>

                <!-- Content -->
                <div class="flex-1 space-y-3">
                  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div>
                      <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">#JMP-{{ String(item.penjemputan_id).padStart(3, '0') }}</p>
                      <h3 class="text-lg font-black text-gray-800 line-clamp-1">
                        {{ item.detail_penjemputan?.map(d => d.sampah?.item_sampah?.nama).join(', ') || 'Jenis Sampah Tidak Diketahui' }}
                      </h3>
                    </div>
                  </div>

                  <div class="flex flex-wrap gap-y-2 gap-x-6">
                    <div class="flex items-center gap-2 text-gray-500">
                      <Icon icon="material-symbols:calendar-today-outline" class="w-4 h-4" />
                      <span class="text-xs font-bold">{{ formatDate(item.created_at) }}</span>
                    </div>
                    <div class="flex items-center gap-2 text-gray-500">
                      <Icon icon="material-symbols:weight-outline" class="w-4 h-4" />
                      <span class="text-xs font-bold">{{ item.estimasi_berat || '-' }} kg</span>
                    </div>
                    <div class="flex items-center gap-2 text-[#4A7043]">
                      <Icon icon="material-symbols:location-on-outline" class="w-4 h-4" />
                      <span class="text-xs font-black">{{ item.gudang?.alamat || 'Lokasi tidak diset' }}</span>
                    </div>
                  </div>

                  <!-- Buttons -->
                  <div class="flex gap-3 pt-2">
                    <button class="flex-1 bg-[#4A7043] hover:bg-[#3D5C37] text-white py-3 rounded-2xl font-bold text-sm transition-all flex items-center justify-center gap-2 shadow-lg shadow-[#4A7043]/20 active:scale-[0.98]">
                      <span>Lihat Detail</span>
                      <Icon icon="material-symbols:arrow-right-alt" class="w-5 h-5" />
                    </button>
                    <button
                      v-if="item.status === 'pending'"
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
