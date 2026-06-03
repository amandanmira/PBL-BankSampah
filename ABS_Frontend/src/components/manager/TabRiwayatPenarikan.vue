<script setup>
import { ref, computed, onMounted } from "vue";
import { Icon } from "@iconify/vue";
import Swal from 'sweetalert2';
import dayjs from 'dayjs';
import { getAuditPenarikanData, getAuditPenarikanSummary, exportPenarikanPdf } from '@/lib/api/manager/auditApi';

const isLoadingPenarikan = ref(false);
const currentPagePenarikan = ref(1);
const totalPagesPenarikan = ref(1);
const itemsPerPage = ref(10);
const searchQueryPenarikan = ref('');
const tableDataPenarikan = ref([]);
const isExportingPdf = ref(false);
const isFilterModalOpen = ref(false);

const penarikanSummary = ref({
  totalNominal: 'Rp 0',
  selesai: 0,
  diproses: 0,
  ditolak: 0
});

const today = new Date();
const thirtyDaysAgo = new Date();
thirtyDaysAgo.setDate(today.getDate() - 30);

const formatDate = (date) => date.toISOString().split('T')[0];

const filterForm = ref({
  start_date: formatDate(thirtyDaysAgo),
  end_date: formatDate(today)
});

const appliedFilter = ref({
  start_date: formatDate(thirtyDaysAgo),
  end_date: formatDate(today)
});

const fetchPenarikanData = async () => {
  try {
    isLoadingPenarikan.value = true;
    const params = {
      search: searchQueryPenarikan.value,
      start_date: appliedFilter.value.start_date,
      end_date: appliedFilter.value.end_date
    };
    const response = await getAuditPenarikanData(currentPagePenarikan.value, params, itemsPerPage.value);
    tableDataPenarikan.value = response.data.data;
    totalPagesPenarikan.value = response.data.last_page || 1;
  } catch (error) {
    console.error("Error fetching audit penarikan data:", error);
    Swal.fire({
      icon: 'error',
      title: 'Gagal memuat data penarikan',
      text: 'Terjadi kesalahan saat menghubungi server.',
      confirmButtonColor: '#4A7043'
    });
  } finally {
    isLoadingPenarikan.value = false;
  }
};

const fetchPenarikanSummary = async () => {
  try {
    const params = {
      search: searchQueryPenarikan.value,
      start_date: appliedFilter.value.start_date,
      end_date: appliedFilter.value.end_date
    };
    const response = await getAuditPenarikanSummary(params);
    penarikanSummary.value = response.data;
  } catch (error) {
    console.error("Error fetching audit penarikan summary:", error);
  }
};

let searchTimeoutPenarikan = null;
const handleSearchPenarikan = () => {
  clearTimeout(searchTimeoutPenarikan);
  searchTimeoutPenarikan = setTimeout(() => {
    currentPagePenarikan.value = 1;
    fetchPenarikanData();
    fetchPenarikanSummary();
  }, 500);
};

const openFilterModal = () => {
  filterForm.value.start_date = appliedFilter.value.start_date;
  filterForm.value.end_date = appliedFilter.value.end_date;
  isFilterModalOpen.value = true;
};

const terapkanFilter = () => {
  appliedFilter.value.start_date = filterForm.value.start_date;
  appliedFilter.value.end_date = filterForm.value.end_date;
  currentPagePenarikan.value = 1;
  isFilterModalOpen.value = false;
  fetchPenarikanData();
  fetchPenarikanSummary();
};

const resetFilterForm = () => {
  filterForm.value.start_date = '';
  filterForm.value.end_date = '';
};

const removeFilter = (type) => {
  if (type === 'date') {
    appliedFilter.value.start_date = '';
    appliedFilter.value.end_date = '';
    filterForm.value.start_date = '';
    filterForm.value.end_date = '';
  }
  currentPagePenarikan.value = 1;
  fetchPenarikanData();
  fetchPenarikanSummary();
};

const clearAllFilters = () => {
  appliedFilter.value.start_date = '';
  appliedFilter.value.end_date = '';
  filterForm.value.start_date = '';
  filterForm.value.end_date = '';
  currentPagePenarikan.value = 1;
  fetchPenarikanData();
  fetchPenarikanSummary();
};

const hasActiveFilters = computed(() => {
  return appliedFilter.value.start_date !== '' || appliedFilter.value.end_date !== '';
});

const formatDateRangeText = computed(() => {
  if (appliedFilter.value.start_date && appliedFilter.value.end_date) {
    return `${dayjs(appliedFilter.value.start_date).format('DD MMM YYYY')} - ${dayjs(appliedFilter.value.end_date).format('DD MMM YYYY')}`;
  } else if (appliedFilter.value.start_date) {
    return `Mulai ${dayjs(appliedFilter.value.start_date).format('DD MMM YYYY')}`;
  } else if (appliedFilter.value.end_date) {
    return `Sampai ${dayjs(appliedFilter.value.end_date).format('DD MMM YYYY')}`;
  }
  return '';
});

const prevPagePenarikan = () => { 
  if (currentPagePenarikan.value > 1) {
    currentPagePenarikan.value--;
    fetchPenarikanData();
  }
};
const nextPagePenarikan = () => { 
  if (currentPagePenarikan.value < totalPagesPenarikan.value) {
    currentPagePenarikan.value++;
    fetchPenarikanData();
  }
};

const handlePrintPdf = async () => {
  try {
    isExportingPdf.value = true;
    const params = {
      durasi: appliedFilter.value.durasi,
      start_date: appliedFilter.value.start_date,
      end_date: appliedFilter.value.end_date
    };
    const response = await exportPenarikanPdf(params);
    
    // Convert Base64 back to Blob
    const base64Data = response.data.data.pdf_base64;
    const byteCharacters = atob(base64Data);
    const byteNumbers = new Array(byteCharacters.length);
    for (let i = 0; i < byteCharacters.length; i++) {
        byteNumbers[i] = byteCharacters.charCodeAt(i);
    }
    const byteArray = new Uint8Array(byteNumbers);
    const blobData = new Blob([byteArray], { type: 'application/pdf' });
    
    const url = window.URL.createObjectURL(blobData);
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', `Laporan_Penarikan_Saldo_Nasabah_${dayjs().format('YYYYMMDD')}.pdf`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    window.URL.revokeObjectURL(url);
    Swal.fire({
      icon: 'success',
      title: 'Berhasil Print',
      text: 'File PDF berhasil diunduh.',
      timer: 2000,
      showConfirmButton: false
    });
  } catch (error) {
    console.error("PDF Export error:", error);
    Swal.fire({
      icon: 'error',
      title: 'Gagal Print PDF',
      text: 'Error: ' + (error.message || JSON.stringify(error)),
      confirmButtonColor: '#4A7043'
    });
  } finally {
    isExportingPdf.value = false;
  }
};

onMounted(() => {
  fetchPenarikanData();
  fetchPenarikanSummary();
});
</script>

<template>
  <div class="space-y-6 relative animate-in fade-in duration-500">
    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
      <div class="bg-white p-5 rounded-2xl shadow-sm border border-stone-100 flex flex-col justify-center">
        <p class="text-xs font-bold text-stone-500 mb-2">Total Penarikan</p>
        <p class="text-2xl font-black text-[#4A7043]">{{ penarikanSummary.totalNominal }}</p>
      </div>
      <div class="bg-white p-5 rounded-2xl shadow-sm border border-stone-100 flex flex-col justify-center">
        <p class="text-xs font-bold text-stone-500 mb-2">Selesai</p>
        <p class="text-2xl font-black text-[#4A7043]">{{ penarikanSummary.selesai }}</p>
      </div>
      <div class="bg-white p-5 rounded-2xl shadow-sm border border-stone-100 flex flex-col justify-center">
        <p class="text-xs font-bold text-stone-500 mb-2">Diproses</p>
        <p class="text-2xl font-black text-[#F59E0B]">{{ penarikanSummary.diproses }}</p>
      </div>
      <div class="bg-white p-5 rounded-2xl shadow-sm border border-stone-100 flex flex-col justify-center">
        <p class="text-xs font-bold text-stone-500 mb-2">Ditolak</p>
        <p class="text-2xl font-black text-[#EF4444]">{{ penarikanSummary.ditolak }}</p>
      </div>
    </div>

    <!-- Toolbar -->
    <div class="bg-white p-4 rounded-2xl shadow-sm border border-stone-100 flex flex-row gap-2 justify-between items-center overflow-x-auto">
      <div class="flex flex-row gap-2 w-auto shrink-0">
        <div class="relative w-48 sm:w-64 flex items-center shrink-0">
          <Icon icon="material-symbols:search" class="absolute left-3 top-1/2 -translate-y-1/2 text-stone-400 w-4 h-4" />
          <input type="text" v-model="searchQueryPenarikan" @input="handleSearchPenarikan" placeholder="Cari ID, nasabah..." class="w-full pl-9 pr-3 py-1.5 bg-white border border-stone-200 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-[#4A7043]/20 focus:border-[#4A7043] transition-colors text-stone-700" />
        </div>
        <!-- Filter Button -->
        <button @click="openFilterModal" class="relative px-4 py-1.5 bg-stone-50 border border-stone-200 rounded-lg text-xs font-bold text-stone-600 hover:bg-stone-100 transition-colors flex items-center gap-1.5 justify-center shadow-sm shrink-0 cursor-pointer">
          <Icon icon="material-symbols:filter-alt-outline" class="w-3.5 h-3.5" />
          Filter
          <!-- Active Indicator Dot -->
          <span v-if="hasActiveFilters" class="absolute -top-1 -right-1 flex h-3 w-3">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#4A7043] opacity-75"></span>
            <span class="relative inline-flex rounded-full h-3 w-3 bg-[#4A7043] border border-white"></span>
          </span>
        </button>
      </div>
      <div class="flex flex-row gap-2 w-auto shrink-0">
        <button @click="handlePrintPdf" :disabled="isExportingPdf || isLoadingPenarikan" class="px-4 py-1.5 bg-[#4A7043] hover:bg-[#3D5A35] text-white rounded-lg text-xs font-bold transition-colors flex items-center gap-1.5 shadow-sm shrink-0 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
          <Icon v-if="isExportingPdf" icon="line-md:loading-twotone-loop" class="w-3.5 h-3.5" />
          <Icon v-else icon="material-symbols:print-outline" class="w-3.5 h-3.5" />
          {{ isExportingPdf ? 'Mengekspor...' : 'Cetak PDF' }}
        </button>
      </div>
    </div>

    <!-- Active Filter Chips Section -->
    <div v-if="hasActiveFilters" class="flex flex-wrap items-center gap-2 px-1">
      <span class="text-[11px] font-bold text-stone-500 mr-1">Filter Aktif:</span>
      <div class="inline-flex items-center gap-1.5 px-3 py-1 bg-[#F5F9F5] text-[#3D5A35] rounded-full text-[11px] font-bold border border-[#4A7043]/20 shadow-sm transition-all hover:bg-[#E9F5E9]">
        Waktu: {{ formatDateRangeText }}
        <button @click="removeFilter('date')" class="text-[#4A7043]/50 hover:text-red-500 transition-colors ml-1 focus:outline-none">
          <Icon icon="material-symbols:close" class="w-3.5 h-3.5" />
        </button>
      </div>
      <button @click="clearAllFilters" class="text-[11px] font-bold text-stone-400 hover:text-red-500 transition-colors ml-2 underline cursor-pointer">
        Hapus Semua
      </button>
    </div>

    <!-- Skeleton Loader for Penarikan -->
    <div v-if="isLoadingPenarikan" class="p-6 bg-white rounded-2xl shadow-sm border border-stone-100">
      <div class="grid grid-cols-5 gap-4 mb-4 pb-4 border-b border-stone-100">
        <div v-for="i in 5" :key="`th-${i}`" class="h-3 bg-stone-200 rounded animate-pulse"></div>
      </div>
      <div class="space-y-4">
        <div v-for="r in 10" :key="`tr-${r}`" class="grid grid-cols-5 gap-4 items-center">
          <div v-for="i in 5" :key="`td-${r}-${i}`" class="h-3 bg-stone-100 rounded animate-pulse"></div>
        </div>
      </div>
    </div>

    <!-- Table Container -->
    <div v-else class="bg-white rounded-2xl shadow-sm border border-stone-100 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse min-w-[700px]">
          <thead>
            <tr class="bg-[#F5F5F0] border-b border-stone-200">
              <th class="py-4 px-6 text-xs font-bold text-stone-600 tracking-wider">ID Penarikan</th>
              <th class="py-4 px-6 text-xs font-bold text-stone-600 tracking-wider">Tanggal</th>
              <th class="py-4 px-6 text-xs font-bold text-stone-600 tracking-wider">Nasabah</th>
              <th class="py-4 px-6 text-xs font-bold text-stone-600 tracking-wider">Nominal</th>
              <th class="py-4 px-6 text-xs font-bold text-stone-600 tracking-wider text-center">Status</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-stone-100">
            <tr v-for="(row, index) in tableDataPenarikan" :key="index" class="hover:bg-stone-50 transition-colors group">
              <td class="py-4 px-6 text-sm text-[#4A7043] font-medium whitespace-nowrap">{{ row.id }}</td>
              <td class="py-4 px-6 text-sm text-stone-600 font-medium whitespace-nowrap">{{ row.tanggal }}</td>
              <td class="py-4 px-6 text-sm text-stone-600 font-medium whitespace-nowrap">{{ row.nasabah }}</td>
              <td class="py-4 px-6 text-sm text-stone-800 font-medium whitespace-nowrap">{{ row.nominal }}</td>
              <td class="py-4 px-6 whitespace-nowrap text-center">
                <div v-if="row.status === 'Selesai'" class="inline-flex items-center justify-center px-4 py-1.5 rounded-full bg-[#3D5A35] text-white text-[10px] font-bold tracking-wider shadow-sm min-w-[70px]">
                  Selesai
                </div>
                <div v-else-if="row.status === 'Diproses'" class="inline-flex items-center justify-center px-4 py-1.5 rounded-full bg-[#F59E0B] text-white text-[10px] font-bold tracking-wider shadow-sm min-w-[70px]">
                  Diproses
                </div>
                <div v-else-if="row.status === 'Ditolak'" class="inline-flex items-center justify-center px-4 py-1.5 rounded-full bg-[#EF4444] text-white text-[10px] font-bold tracking-wider shadow-sm min-w-[70px]">
                  Ditolak
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="!isLoadingPenarikan" class="bg-white p-4 rounded-2xl shadow-sm border border-stone-100 flex flex-col sm:flex-row justify-between items-center gap-4">
      <div class="flex items-center gap-3">
        <span class="text-sm font-medium text-stone-500">Tampilkan</span>
        <div class="bg-stone-50 border border-stone-200 rounded-lg px-3 py-1.5 text-sm font-bold text-stone-600 flex items-center justify-between w-16 shadow-sm">
          10
        </div>
      </div>
      <div class="flex items-center gap-2">
        <button @click="prevPagePenarikan" :disabled="currentPagePenarikan === 1" class="px-4 py-2 rounded-lg text-sm font-bold text-stone-500 hover:bg-stone-100 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed transition-colors">Sebelumnya</button>
        <button class="w-auto px-3 h-8 flex items-center justify-center rounded-lg bg-[#4A7043] text-white text-sm font-bold shadow-sm">{{ currentPagePenarikan }}/{{ totalPagesPenarikan }}</button>
        <button @click="nextPagePenarikan" :disabled="currentPagePenarikan === totalPagesPenarikan" class="px-4 py-2 rounded-lg text-sm font-bold text-stone-500 hover:bg-stone-100 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed transition-colors">Selanjutnya</button>
      </div>
    </div>

    <!-- Filter Modal Teleport -->
    <Teleport to="body">
      <div v-if="isFilterModalOpen" class="fixed inset-0 z-[999] flex items-center justify-center p-4 bg-stone-900/60 backdrop-blur-sm transition-all duration-300">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden transform transition-all border border-stone-100 flex flex-col">
          <!-- Modal Header -->
          <div class="px-5 py-4 border-b border-stone-100 flex justify-between items-center bg-[#4A7043] text-white">
            <h3 class="text-sm font-bold flex items-center gap-2">
              <Icon icon="material-symbols:filter-alt" class="w-4 h-4" /> Filter Riwayat Penarikan
            </h3>
            <button @click="isFilterModalOpen = false" class="text-white/80 hover:text-white transition-colors cursor-pointer">
              <Icon icon="material-symbols:close" class="w-5 h-5" />
            </button>
          </div>

          <!-- Modal Body -->
          <div class="p-6 space-y-6 flex-1">
            <!-- Custom Rentang Tanggal -->
            <div class="grid grid-cols-2 gap-4">
              <div class="space-y-2">
                <label class="block text-xs font-bold text-stone-600">Dari Tanggal</label>
                <input type="date" v-model="filterForm.start_date" class="w-full px-3 py-2 bg-white border border-stone-200 rounded-lg text-xs text-stone-700 focus:outline-none focus:ring-2 focus:ring-[#4A7043]/20 focus:border-[#4A7043] transition-colors cursor-pointer" />
              </div>
              <div class="space-y-2">
                <label class="block text-xs font-bold text-stone-600">Sampai Tanggal</label>
                <input type="date" v-model="filterForm.end_date" class="w-full px-3 py-2 bg-white border border-stone-200 rounded-lg text-xs text-stone-700 focus:outline-none focus:ring-2 focus:ring-[#4A7043]/20 focus:border-[#4A7043] transition-colors cursor-pointer" />
              </div>
            </div>
          </div>

          <!-- Modal Footer -->
          <div class="px-6 py-4 bg-stone-50 border-t border-stone-100 flex justify-between gap-3">
            <button @click="resetFilterForm" class="px-4 py-2 border border-stone-200 rounded-lg text-xs font-bold text-stone-600 hover:bg-stone-100 transition-colors cursor-pointer">
              Reset
            </button>
            <div class="flex gap-2">
              <button @click="isFilterModalOpen = false" class="px-4 py-2 border border-stone-200 rounded-lg text-xs font-bold text-stone-600 hover:bg-stone-100 transition-colors cursor-pointer">
                Batal
              </button>
              <button @click="terapkanFilter" class="px-4 py-2 bg-[#4A7043] hover:bg-[#3D5A35] text-white rounded-lg text-xs font-bold transition-colors cursor-pointer">
                Terapkan
              </button>
            </div>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<style scoped>
.animate-in {
  animation: fadeIn 0.7s ease-out both;
}
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(15px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
