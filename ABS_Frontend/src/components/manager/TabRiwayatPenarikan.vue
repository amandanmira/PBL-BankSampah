<script setup>
import { ref, computed, onMounted } from "vue";
import { Icon } from "@iconify/vue";
import Swal from 'sweetalert2';
import dayjs from 'dayjs';
import { getAuditPenarikanData, getAuditPenarikanSummary, exportPenarikanPdf, getListGudang } from '@/lib/api/manager/auditApi';

const isLoadingPenarikan = ref(false);
const currentPagePenarikan = ref(1);
const isLaporanModalOpen = ref(false);
const isDetailModalOpen = ref(false);
const selectedDetailRow = ref(null);

const openDetailModal = (row) => {
  selectedDetailRow.value = row;
  isDetailModalOpen.value = true;
};

const handleExportCsv = () => {
  if (!tableDataPenarikan.value.length) {
    Swal.fire({
      icon: 'info',
      title: 'Tidak ada data',
      text: 'Tidak ada data penarikan untuk diekspor.',
      confirmButtonColor: '#4A7043'
    });
    return;
  }
  
  const headers = ['ID Penarikan', 'Tanggal', 'Nasabah', 'Nominal', 'Status', 'Bank/E-Wallet', 'No Rekening', 'Nama Rekening', 'Petugas', 'Gudang'];
  const rows = tableDataPenarikan.value.map(row => [
    row.id,
    row.tanggal,
    row.nasabah,
    row.nominal.replace(/[^\d]/g, ''),
    row.status,
    row.nama_bank || '-',
    `'${row.no_rekening || ''}`,
    row.nama_rek || '-',
    row.petugas || '-',
    row.gudang || '-'
  ]);
  
  const csvContent = "\uFEFF" + [headers.join(','), ...rows.map(e => e.map(val => `"${val || ''}"`).join(','))].join('\n');
  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
  const url = URL.createObjectURL(blob);
  const link = document.createElement("a");
  link.setAttribute("href", url);
  link.setAttribute("download", `Riwayat_Penarikan_Saldo_${dayjs().format('YYYYMMDD')}.csv`);
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
};
const totalPagesPenarikan = ref(1);
const itemsPerPage = ref(10);
const searchQueryPenarikan = ref('');
const tableDataPenarikan = ref([]);
const isExportingPdf = ref(false);
const isFilterModalOpen = ref(false);
const gudangOptions = ref([]);

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
  end_date: formatDate(today),
  gudang: 'Semua Gudang'
});

const appliedFilter = ref({
  start_date: formatDate(thirtyDaysAgo),
  end_date: formatDate(today),
  gudang: 'Semua Gudang'
});

const fetchPenarikanData = async () => {
  try {
    isLoadingPenarikan.value = true;
    const selectedGudang = gudangOptions.value.find(g => g.alamat === appliedFilter.value.gudang);
    const params = {
      search: searchQueryPenarikan.value,
      start_date: appliedFilter.value.start_date,
      end_date: appliedFilter.value.end_date,
      gudang_id: selectedGudang ? selectedGudang.gudang_id : null
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
    const selectedGudang = gudangOptions.value.find(g => g.alamat === appliedFilter.value.gudang);
    const params = {
      search: searchQueryPenarikan.value,
      start_date: appliedFilter.value.start_date,
      end_date: appliedFilter.value.end_date,
      gudang_id: selectedGudang ? selectedGudang.gudang_id : null
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
  filterForm.value.gudang = appliedFilter.value.gudang;
  isFilterModalOpen.value = true;
};

const terapkanFilter = () => {
  appliedFilter.value.start_date = filterForm.value.start_date;
  appliedFilter.value.end_date = filterForm.value.end_date;
  appliedFilter.value.gudang = filterForm.value.gudang;
  currentPagePenarikan.value = 1;
  isFilterModalOpen.value = false;
  fetchPenarikanData();
  fetchPenarikanSummary();
};

const resetFilterForm = () => {
  filterForm.value.start_date = '';
  filterForm.value.end_date = '';
  filterForm.value.gudang = 'Semua Gudang';
};

const removeFilter = (type) => {
  if (type === 'date') {
    appliedFilter.value.start_date = '';
    appliedFilter.value.end_date = '';
    filterForm.value.start_date = '';
    filterForm.value.end_date = '';
  } else if (type === 'gudang') {
    appliedFilter.value.gudang = 'Semua Gudang';
    filterForm.value.gudang = 'Semua Gudang';
  }
  currentPagePenarikan.value = 1;
  fetchPenarikanData();
  fetchPenarikanSummary();
};

const clearAllFilters = () => {
  appliedFilter.value.start_date = '';
  appliedFilter.value.end_date = '';
  appliedFilter.value.gudang = 'Semua Gudang';
  filterForm.value.start_date = '';
  filterForm.value.end_date = '';
  filterForm.value.gudang = 'Semua Gudang';
  currentPagePenarikan.value = 1;
  fetchPenarikanData();
  fetchPenarikanSummary();
};

const hasActiveFilters = computed(() => {
  return appliedFilter.value.start_date !== '' || appliedFilter.value.end_date !== '' || appliedFilter.value.gudang !== 'Semua Gudang';
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
    const selectedGudang = gudangOptions.value.find(g => g.alamat === appliedFilter.value.gudang);
    const params = {
      start_date: appliedFilter.value.start_date,
      end_date: appliedFilter.value.end_date,
      gudang_id: selectedGudang ? selectedGudang.gudang_id : null
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

onMounted(async () => {
  fetchPenarikanData();
  fetchPenarikanSummary();
  try {
    const res = await getListGudang();
    gudangOptions.value = res.data;
  } catch (error) {
    console.error("Error loading gudang list:", error);
  }
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
        <p class="text-xs font-bold text-stone-500 mb-2">Menunggu</p>
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
          <input type="text" v-model="searchQueryPenarikan" @input="handleSearchPenarikan" placeholder="Cari ID, nasabah, metode..." class="w-full pl-9 pr-3 py-1.5 bg-white border border-stone-200 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-[#4A7043]/20 focus:border-[#4A7043] transition-colors text-stone-700" />
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
        <button @click="isLaporanModalOpen = true" :disabled="isLoadingPenarikan" class="px-4 py-1.5 bg-[#4A7043] hover:bg-[#3D5A35] text-white rounded-lg text-xs font-bold transition-colors flex items-center gap-1.5 shadow-sm shrink-0 cursor-pointer disabled:opacity-50">
          <Icon icon="material-symbols:visibility-outline" class="w-3.5 h-3.5" />
          Lihat Laporan
        </button>
        <button @click="handleExportCsv" :disabled="isLoadingPenarikan" class="px-4 py-1.5 bg-stone-50 border border-stone-200 text-stone-600 hover:bg-stone-100 rounded-lg text-xs font-bold transition-colors flex items-center gap-1.5 shadow-sm shrink-0 cursor-pointer disabled:opacity-50">
          <Icon icon="material-symbols:download-outline" class="w-3.5 h-3.5" />
          Export CSV
        </button>
      </div>
    </div>

    <!-- Active Filter Chips Section -->
    <div v-if="hasActiveFilters" class="flex flex-wrap items-center gap-2 px-1">
      <span class="text-[11px] font-bold text-stone-500 mr-1">Filter Aktif:</span>
      <div v-if="appliedFilter.start_date || appliedFilter.end_date" class="inline-flex items-center gap-1.5 px-3 py-1 bg-[#F5F9F5] text-[#3D5A35] rounded-full text-[11px] font-bold border border-[#4A7043]/20 shadow-sm transition-all hover:bg-[#E9F5E9]">
        Waktu: {{ formatDateRangeText }}
        <button @click="removeFilter('date')" class="text-[#4A7043]/50 hover:text-red-500 transition-colors ml-1 focus:outline-none">
          <Icon icon="material-symbols:close" class="w-3.5 h-3.5" />
        </button>
      </div>
      <div v-if="appliedFilter.gudang && appliedFilter.gudang !== 'Semua Gudang'" class="inline-flex items-center gap-1.5 px-3 py-1 bg-[#F5F9F5] text-[#3D5A35] rounded-full text-[11px] font-bold border border-[#4A7043]/20 shadow-sm transition-all hover:bg-[#E9F5E9]">
        Gudang: {{ appliedFilter.gudang }}
        <button @click="removeFilter('gudang')" class="text-[#4A7043]/50 hover:text-red-500 transition-colors ml-1 focus:outline-none">
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
              <th class="py-4 px-6 text-xs font-bold text-stone-600 tracking-wider">Nominal(Rp)</th>
              <th class="py-4 px-6 text-xs font-bold text-stone-600 tracking-wider text-center">Status</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-stone-100">
            <tr v-for="(row, index) in tableDataPenarikan" :key="index" class="hover:bg-stone-50 transition-colors group cursor-pointer" @click="openDetailModal(row)">
              <td class="py-4 px-6 text-sm text-[#4A7043] font-bold whitespace-nowrap">{{ row.id }}</td>
              <td class="py-4 px-6 text-sm text-stone-600 font-medium whitespace-nowrap">{{ row.tanggal }}</td>
              <td class="py-4 px-6 text-sm text-stone-600 font-medium whitespace-nowrap">{{ row.nasabah }}</td>
              <td class="py-4 px-6 text-sm text-stone-800 font-bold whitespace-nowrap">{{ row.nominal.replace('Rp ', '') }}</td>
              <td class="py-4 px-6 whitespace-nowrap text-center">
                <div v-if="row.status === 'Selesai'" class="inline-flex items-center justify-center px-4 py-1.5 rounded-full bg-[#3D5A35] text-white text-[10px] font-bold tracking-wider shadow-sm min-w-[70px]">
                  Selesai
                </div>
                <div v-else-if="row.status === 'Menunggu'" class="inline-flex items-center justify-center px-4 py-1.5 rounded-full bg-[#F59E0B] text-white text-[10px] font-bold tracking-wider shadow-sm min-w-[70px]">
                  Menunggu
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
            <!-- Gudang Dropdown -->
            <div class="space-y-2">
              <label class="block text-xs font-bold text-stone-600">Gudang</label>
              <div class="relative">
                <select v-model="filterForm.gudang" class="w-full appearance-none px-4 py-2.5 bg-white border border-stone-200 rounded-lg text-xs text-stone-700 focus:outline-none focus:ring-2 focus:ring-[#4A7043]/20 focus:border-[#4A7043] transition-colors cursor-pointer">
                  <option value="Semua Gudang">Semua Gudang</option>
                  <option v-for="g in gudangOptions" :key="g.gudang_id" :value="g.alamat">{{ g.alamat }}</option>
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-stone-400">
                  <Icon icon="material-symbols:keyboard-arrow-down-rounded" class="w-4 h-4" />
                </div>
              </div>
            </div>

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

    <!-- Laporan Modal -->
    <Teleport to="body">
      <div v-if="isLaporanModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="isLaporanModalOpen = false"></div>
        <div class="relative bg-stone-50 rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] flex flex-col overflow-hidden animate-in zoom-in-95 duration-200">
          <div class="p-6 bg-white border-b border-stone-100 flex justify-between items-center shrink-0 z-10">
            <div>
              <h3 class="text-xl font-black text-stone-800">Preview Laporan Penarikan Saldo</h3>
              <p class="text-xs text-stone-500 mt-1">Periode: {{ formatDateRangeText || 'Semua Waktu' }}</p>
            </div>
            <div class="flex items-center gap-3">
              <button @click="handlePrintPdf" :disabled="isExportingPdf" class="px-4 py-2 bg-[#4A7043] hover:bg-[#3D5A35] text-white rounded-lg text-sm font-bold transition-colors flex items-center gap-2 shadow-sm cursor-pointer disabled:opacity-50">
                <Icon v-if="isExportingPdf" icon="line-md:loading-twotone-loop" class="w-4 h-4" />
                <Icon v-else icon="material-symbols:print-outline" class="w-4 h-4" />
                {{ isExportingPdf ? 'Mencetak...' : 'Print' }}
              </button>
              <button @click="isLaporanModalOpen = false" class="text-stone-400 hover:text-stone-600 transition-colors cursor-pointer p-1">
                <Icon icon="material-symbols:close" class="w-6 h-6" />
              </button>
            </div>
          </div>
          
          <div class="p-6 overflow-y-auto custom-scrollbar flex-1 space-y-6 relative z-0">
            <!-- Summary stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div class="bg-white p-5 rounded-xl border border-stone-100 shadow-sm flex flex-col justify-center">
                <p class="text-xs font-bold text-stone-400 mb-1 uppercase tracking-wider">Total Transaksi Final</p>
                <p class="text-2xl font-black text-[#3D5A35]">{{ penarikanSummary.totalTransaksiFinal || 0 }}</p>
                <p class="text-[10px] font-medium text-stone-500 mt-1">transaksi tercatat</p>
              </div>
              <div class="bg-white p-5 rounded-xl border border-stone-100 shadow-sm flex flex-col justify-center">
                <p class="text-xs font-bold text-stone-400 mb-1 uppercase tracking-wider">Total Nominal Selesai</p>
                <p class="text-2xl font-black text-[#4A7043]">{{ penarikanSummary.totalNominalSelesaiFormatted || 'Rp 0' }}</p>
                <p class="text-[10px] font-medium text-stone-500 mt-1">sudah dicairkan</p>
              </div>
              <div class="bg-white p-5 rounded-xl border border-stone-100 shadow-sm flex flex-col justify-center bg-[#FFFDF8] border-amber-100">
                <p class="text-xs font-bold text-stone-400 mb-1 uppercase tracking-wider">Total Ditolak</p>
                <p class="text-2xl font-black text-amber-600">{{ penarikanSummary.totalDitolak || 0 }}</p>
                <p class="text-[10px] font-medium text-stone-500 mt-1">transaksi gagal</p>
              </div>
            </div>

            <!-- Bank Distribution -->
            <div class="bg-white rounded-xl border border-stone-100 shadow-sm overflow-hidden">
              <div class="p-5 border-b border-stone-100">
                <h4 class="text-sm font-bold text-stone-800 uppercase tracking-wider">Distribusi Bank Tujuan</h4>
              </div>
              <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                  <thead>
                    <tr class="text-stone-500 bg-stone-50/50 border-b border-stone-100">
                      <th class="py-3.5 px-6 font-bold text-xs">Nama Bank / E-Wallet</th>
                      <th class="py-3.5 px-6 font-bold text-xs text-center">Jml Transaksi</th>
                      <th class="py-3.5 px-6 font-bold text-xs text-right">Total Nominal</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-stone-100">
                    <tr v-for="bank in penarikanSummary.bankDistribution" :key="bank.nama_bank" class="hover:bg-stone-50/50 transition-colors">
                      <td class="py-3.5 px-6 font-medium text-stone-700 text-xs flex items-center gap-2">
                        <span class="inline-flex items-center justify-center w-7 h-7 rounded bg-[#4A7043] text-white text-[10px] font-bold tracking-wider uppercase shrink-0">
                          {{ bank.nama_bank ? bank.nama_bank.slice(0, 2) : '??' }}
                        </span>
                        <span class="font-bold text-stone-800">{{ bank.nama_bank }}</span>
                      </td>
                      <td class="py-3.5 px-6 text-stone-600 text-xs text-center font-semibold">{{ bank.jumlah_transaksi }}</td>
                      <td class="py-3.5 px-6 text-stone-800 text-xs text-right font-bold">{{ bank.total_nominal_formatted }}</td>
                    </tr>
                    <tr v-if="!penarikanSummary.bankDistribution || !penarikanSummary.bankDistribution.length">
                      <td colspan="3" class="py-8 text-center text-stone-400 text-xs">Tidak ada data distribusi bank</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <!-- Ratio Progress Bar -->
            <div class="bg-white rounded-xl border border-stone-100 shadow-sm overflow-hidden p-6 space-y-4">
              <h4 class="text-sm font-bold text-stone-800 uppercase tracking-wider">Rasio Keberhasilan Transfer</h4>
              <div class="h-3 w-full bg-stone-100 rounded-full overflow-hidden flex">
                <div class="bg-[#4A7043] h-full transition-all duration-500" :style="`width: ${penarikanSummary.selesaiPercent || 0}%`"></div>
                <div class="bg-amber-500 h-full transition-all duration-500" :style="`width: ${penarikanSummary.ditolakPercent || 0}%`"></div>
              </div>
              <div class="grid grid-cols-2 gap-6 pt-2">
                <div class="space-y-1">
                  <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#4A7043]"></span>
                    <span class="text-xs font-bold text-stone-400 uppercase tracking-wider">SELESAI</span>
                  </div>
                  <p class="text-2xl font-black text-stone-800">{{ penarikanSummary.selesaiPercent || 0 }}%</p>
                  <p class="text-xs text-stone-500 font-semibold">{{ penarikanSummary.selesai || 0 }} transaksi - {{ penarikanSummary.totalNominalSelesaiFormatted || 'Rp 0' }}</p>
                </div>
                <div class="space-y-1">
                  <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                    <span class="text-xs font-bold text-stone-400 uppercase tracking-wider">DITOLAK</span>
                  </div>
                  <p class="text-2xl font-black text-stone-800">{{ penarikanSummary.ditolakPercent || 0 }}%</p>
                  <p class="text-xs text-stone-500 font-semibold">{{ penarikanSummary.totalDitolak || 0 }} transaksi - {{ penarikanSummary.totalDitolakNominalFormatted || 'Rp 0' }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Detail Modal -->
    <Teleport to="body">
      <div v-if="isDetailModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="isDetailModalOpen = false"></div>
        <div class="relative bg-stone-50 rounded-2xl shadow-2xl w-full max-w-lg flex flex-col overflow-hidden animate-in zoom-in-95 duration-200">
          <div class="bg-[#4A7043] p-5 flex justify-between items-start shrink-0">
            <div>
              <h3 class="text-lg font-bold text-white">Detail Penarikan</h3>
              <p class="text-xs text-green-100 mt-1">{{ selectedDetailRow?.tanggal }} - {{ selectedDetailRow?.id }}</p>
            </div>
            <button @click="isDetailModalOpen = false" class="text-white/80 hover:text-white transition-colors p-1">
              <Icon icon="material-symbols:close" class="w-5 h-5" />
            </button>
          </div>
          <div class="p-6 space-y-6">
            <div class="bg-white rounded-xl p-5 flex justify-between items-start border border-stone-100 shadow-sm">
              <div>
                <p class="text-[10px] font-bold text-stone-400 uppercase tracking-wider mb-1">Nominal Penarikan</p>
                <p class="text-3xl font-black text-[#4A7043] mb-1">{{ selectedDetailRow?.nominal }}</p>
                <p class="text-xs font-semibold text-stone-500">Transfer ke {{ selectedDetailRow?.nama_bank }}</p>
              </div>
              <div class="flex flex-col gap-2 items-end mt-1">
                <span class="inline-flex items-center justify-center px-4 py-1.5 rounded-full text-[10px] font-bold shadow-sm"
                      :class="selectedDetailRow?.status === 'Selesai' ? 'bg-[#3D5A35] text-white' : selectedDetailRow?.status === 'Ditolak' ? 'bg-[#DC2626] text-white' : 'bg-[#F59E0B] text-white'">
                  {{ selectedDetailRow?.status }}
                </span>
              </div>
            </div>
            <div class="grid grid-cols-2 gap-y-6 gap-x-4 bg-white p-5 rounded-xl border border-stone-100 shadow-sm text-xs">
              <div>
                <p class="text-[10px] font-bold text-stone-400 uppercase tracking-wider mb-1">Tanggal Request</p>
                <p class="text-sm font-bold text-stone-800">{{ selectedDetailRow?.tanggal }}</p>
              </div>
              <div>
                <p class="text-[10px] font-bold text-stone-400 uppercase tracking-wider mb-1">Nama Nasabah</p>
                <p class="text-sm font-bold text-stone-800">{{ selectedDetailRow?.nasabah }}</p>
              </div>
              <div>
                <p class="text-[10px] font-bold text-stone-400 uppercase tracking-wider mb-1">Metode Transfer</p>
                <p class="text-sm font-bold text-stone-800">{{ selectedDetailRow?.nama_bank || '-' }}</p>
              </div>
              <div>
                <p class="text-[10px] font-bold text-stone-400 uppercase tracking-wider mb-1">No Rekening / E-Wallet</p>
                <p class="text-sm font-bold text-stone-800">{{ selectedDetailRow?.no_rekening || '-' }}</p>
              </div>
              <div>
                <p class="text-[10px] font-bold text-stone-400 uppercase tracking-wider mb-1">Atas Nama Rekening</p>
                <p class="text-sm font-bold text-stone-800">{{ selectedDetailRow?.nama_rek || '-' }}</p>
              </div>
              <div>
                <p class="text-[10px] font-bold text-stone-400 uppercase tracking-wider mb-1">Petugas Verifikator</p>
                <p class="text-sm font-bold text-stone-800">{{ selectedDetailRow?.petugas || '-' }}</p>
              </div>
              <div class="col-span-2">
                <p class="text-[10px] font-bold text-stone-400 uppercase tracking-wider mb-1">Gudang / Lokasi</p>
                <p class="text-sm font-bold text-stone-800">{{ selectedDetailRow?.gudang || '-' }}</p>
              </div>
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
.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: #f1f1f1; 
  border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #c1c1c1; 
  border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #4A7043; 
}
</style>
