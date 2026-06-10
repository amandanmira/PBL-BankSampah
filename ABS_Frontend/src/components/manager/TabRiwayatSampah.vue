<script setup>
import { ref, computed, onMounted } from "vue";
import { Icon } from "@iconify/vue";
import Swal from 'sweetalert2';
import dayjs from 'dayjs';
import { getListGudang, getListSampah, getAuditData, getAuditSummary, exportLaporanExcel, exportLaporanPdf } from '@/lib/api/manager/auditApi';

const isGroupedByGudang = ref(true);
const isFilterModalOpen = ref(false);
const isLaporanModalOpen = ref(false);
const isLoading = ref(false);
const isLoadingSummary = ref(false);
const isExportingExcel = ref(false);
const isExportingPdf = ref(false);
const isDetailModalOpen = ref(false);
const selectedDetailRow = ref(null);

const gudangOptions = ref([]);
const jenisSampahOptions = ref([]);
const tableData = ref([]);

const today = new Date();
const thirtyDaysAgo = new Date();
thirtyDaysAgo.setDate(today.getDate() - 30);

// Format ke YYYY-MM-DD
const formatDate = (date) => date.toISOString().split('T')[0];

const data = ref({
  start_date: formatDate(thirtyDaysAgo),
  end_date: formatDate(today)
});

const filterForm = ref({
  gudang: 'Semua Gudang',
  role: 'Semua Role',
  jenisSampah: [],
  start_date: formatDate(thirtyDaysAgo),
  end_date: formatDate(today)
});
const appliedFilter = ref({
  gudang: 'Semua Gudang',
  role: 'Semua Role',
  jenisSampah: []
});

const currentPage = ref(1);
const totalPages = ref(1);
const itemsPerPage = ref(10);
const searchQuery = ref('');

const laporanStats = ref({
  totalTransaksi: 0,
  totalBerat: 0,
  verifiedCount: 0,
  pendingCount: 0,
  perGudangList: [],
  jenisSampahList: []
});

const toggleJenisSampah = (jenis) => {
  const index = filterForm.value.jenisSampah.indexOf(jenis);
  if (index === -1) filterForm.value.jenisSampah.push(jenis);
  else filterForm.value.jenisSampah.splice(index, 1);
};

const resetFilterForm = () => {
  filterForm.value.gudang = 'Semua Gudang';
  filterForm.value.role = 'Semua Role';
  filterForm.value.jenisSampah = [];
  filterForm.value.start_date = formatDate(thirtyDaysAgo);
  filterForm.value.end_date = formatDate(today);
};

const openFilterModal = () => {
  filterForm.value.gudang = appliedFilter.value.gudang;
  filterForm.value.role = appliedFilter.value.role;
  filterForm.value.jenisSampah = [...appliedFilter.value.jenisSampah];
  filterForm.value.start_date = data.value.start_date;
  filterForm.value.end_date = data.value.end_date;
  isFilterModalOpen.value = true;
};

const terapkanFilter = () => {
  appliedFilter.value.gudang = filterForm.value.gudang;
  appliedFilter.value.role = filterForm.value.role;
  appliedFilter.value.jenisSampah = [...filterForm.value.jenisSampah];
  data.value.start_date = filterForm.value.start_date;
  data.value.end_date = filterForm.value.end_date;
  currentPage.value = 1;
  isFilterModalOpen.value = false;
  fetchData();
};

const removeFilter = (type, value = null) => {
  if (type === 'gudang') appliedFilter.value.gudang = 'Semua Gudang';
  if (type === 'role') appliedFilter.value.role = 'Semua Role';
  if (type === 'durasi') {
    data.value.start_date = '';
    data.value.end_date = '';
  }
  if (type === 'jenisSampah') appliedFilter.value.jenisSampah = appliedFilter.value.jenisSampah.filter(j => j !== value);
  currentPage.value = 1;
  fetchData();
};

const clearAllFilters = () => {
  appliedFilter.value.gudang = 'Semua Gudang';
  appliedFilter.value.role = 'Semua Role';
  appliedFilter.value.jenisSampah = [];
  data.value.start_date = '';
  data.value.end_date = '';
  currentPage.value = 1;
  fetchData();
};

const hasActiveFilters = computed(() => {
  return appliedFilter.value.gudang !== 'Semua Gudang' ||
         appliedFilter.value.role !== 'Semua Role' ||
         appliedFilter.value.jenisSampah.length > 0 ||
         data.value.start_date !== formatDate(thirtyDaysAgo) ||
         data.value.end_date !== formatDate(today);
});

const formatDateRangeText = computed(() => {
  if (data.value.start_date && data.value.end_date) {
    return `${dayjs(data.value.start_date).format('DD MMM YYYY')} - ${dayjs(data.value.end_date).format('DD MMM YYYY')}`;
  } else if (data.value.start_date) {
    return `Mulai ${dayjs(data.value.start_date).format('DD MMM YYYY')}`;
  } else if (data.value.end_date) {
    return `Sampai ${dayjs(data.value.end_date).format('DD MMM YYYY')}`;
  }
  return 'Semua Waktu';
});

let searchTimeout = null;
const handleSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    currentPage.value = 1;
    fetchData();
  }, 500);
};

const fetchData = async () => {
  try {
    isLoading.value = true;
    const params = {
      ...appliedFilter.value,
      start_date: data.value.start_date,
      end_date: data.value.end_date,
      search: searchQuery.value
    };
    const response = await getAuditData(currentPage.value, params, itemsPerPage.value);
    tableData.value = response.data.data;
    totalPages.value = response.data.last_page || 1;
  } catch (error) {
    console.error("Error fetching audit data:", error);
    Swal.fire({ icon: 'error', title: 'Gagal memuat data', text: 'Terjadi kesalahan saat menghubungi server.', confirmButtonColor: '#4A7043' });
  } finally {
    isLoading.value = false;
  }
};

const fetchSummary = async () => {
  try {
    isLoadingSummary.value = true;
    const params = {
      ...appliedFilter.value,
      start_date: data.value.start_date,
      end_date: data.value.end_date,
      search: searchQuery.value
    };
    const response = await getAuditSummary(params);
    laporanStats.value = response.data;
  } catch (error) {
    console.error("Error fetching audit summary:", error);
  } finally {
    isLoadingSummary.value = false;
  }
};

const openLaporanModal = async () => {
  isLaporanModalOpen.value = true;
  await fetchSummary();
};

const openDetailModal = (row) => {
  selectedDetailRow.value = row;
  isDetailModalOpen.value = true;
};

onMounted(() => {
  fetchData();
  Promise.all([
    getListGudang(),
    getListSampah()
  ]).then(([gudangRes, sampahRes]) => {
    gudangOptions.value = gudangRes.data;
    const sampahList = sampahRes.data.data ? sampahRes.data.data : sampahRes.data; 
    jenisSampahOptions.value = [...new Set(sampahList.map(s => s.item_sampah?.nama || s.nama).filter(Boolean))];
    if (jenisSampahOptions.value.length === 0) jenisSampahOptions.value = ['Organik', 'Plastik PET', 'Kertas', 'Logam']; 
  }).catch(e => {
    console.error("Gagal memuat filter opsi:", e);
  });
});

const filteredFlatData = computed(() => tableData.value);

const filteredGroupedData = computed(() => {
  const groups = {};
  tableData.value.forEach(row => {
    if (!groups[row.gudang]) {
      groups[row.gudang] = { gudangName: row.gudang, rows: [], totalBerat: 0 };
    }
    groups[row.gudang].rows.push(row);
    groups[row.gudang].totalBerat += row.berat;
  });

  return Object.values(groups).map(g => ({
    ...g,
    summary: `${g.rows.length} transaksi - ${g.totalBerat.toFixed(1)} kg`
  })).sort((a, b) => a.gudangName.localeCompare(b.gudangName));
});

const prevPage = () => { 
  if (currentPage.value > 1) {
    currentPage.value--;
    fetchData();
  }
};
const nextPage = () => { 
  if (currentPage.value < totalPages.value) {
    currentPage.value++;
    fetchData();
  }
};

const generateTimeText = computed(() => dayjs().format('DD MMM YYYY pukul HH.mm'));

const handleExportExcel = async () => {
  try {
    isExportingExcel.value = true;
    const selectedGudang = gudangOptions.value.find(g => g.alamat === appliedFilter.value.gudang);
    const params = {
      gudang_id: selectedGudang ? selectedGudang.gudang_id : null,
      start_date: data.value.start_date,
      end_date: data.value.end_date,
    };
    const response = await exportLaporanExcel(params);
    const blobData = response.data || response;
    const url = window.URL.createObjectURL(new Blob([blobData]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', 'Laporan_Audit_Bank_Sampah.xlsx');
    document.body.appendChild(link);
    link.click();
    link.remove();
    Swal.fire({ icon: 'success', title: 'Berhasil Export', text: 'File Excel berhasil diunduh.', timer: 2000, showConfirmButton: false });
  } catch (error) {
    console.error(error);
    Swal.fire({ icon: 'error', title: 'Gagal Export Excel', text: 'Terjadi kesalahan. Pastikan backend tidak mengalami error.'});
  } finally {
    isExportingExcel.value = false;
  }
};

const handlePrintPdf = async () => {
  try {
    isExportingPdf.value = true;
    const selectedGudang = gudangOptions.value.find(g => g.alamat === appliedFilter.value.gudang);
    const params = {
      gudang_id: selectedGudang ? selectedGudang.gudang_id : null,
      start_date: data.value.start_date,
      end_date: data.value.end_date,
    };
    const response = await exportLaporanPdf(params);
    
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
    link.setAttribute('download', 'Laporan_Audit_Bank_Sampah.pdf');
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    window.URL.revokeObjectURL(url);
    Swal.fire({ icon: 'success', title: 'Berhasil Print', text: 'File PDF berhasil diunduh.', timer: 2000, showConfirmButton: false });
  } catch (error) {
    console.error("PDF Export error:", error);
    Swal.fire({ icon: 'error', title: 'Gagal Print PDF', text: 'Error: ' + (error.message || JSON.stringify(error))});
  } finally {
    isExportingPdf.value = false;
  }
};
</script>

<template>
  <div class="space-y-6 relative animate-in fade-in duration-500">
    <!-- Toolbar -->
    <div class="bg-white p-4 rounded-2xl shadow-sm border border-stone-100 flex flex-row gap-2 justify-between items-center overflow-x-auto">
      <!-- Left Controls -->
      <div class="flex flex-row gap-2 w-auto shrink-0">
        <!-- Search -->
        <div class="relative w-48 sm:w-56 flex items-center shrink-0">
          <Icon icon="material-symbols:search" class="absolute left-3 top-1/2 -translate-y-1/2 text-stone-400 w-4 h-4" />
          <input
            type="text"
            v-model="searchQuery"
            @input="handleSearch"
            placeholder="Cari data..."
            class="w-full pl-9 pr-3 py-1.5 bg-white border border-stone-200 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-[#4A7043]/20 focus:border-[#4A7043] transition-colors text-stone-700"
          />
        </div>

        <!-- Dropdown Group By (Toggleable) -->
        <button
          @click="isGroupedByGudang = !isGroupedByGudang"
          :class="[
            'px-3 py-1.5 border rounded-lg text-xs font-bold transition-colors flex items-center gap-1.5 justify-center shadow-sm shrink-0 cursor-pointer',
            isGroupedByGudang ? 'bg-[#4A7043] text-white border-transparent' : 'bg-stone-50 border-stone-200 text-stone-600 hover:bg-stone-100'
          ]"
        >
          <Icon icon="material-symbols:menu" class="w-3.5 h-3.5" />
          Group by Gudang
        </button>

        <!-- Filter Button -->
        <button @click="openFilterModal" class="relative px-3 py-1.5 bg-stone-50 border border-stone-200 rounded-lg text-xs font-bold text-stone-600 hover:bg-stone-100 transition-colors flex items-center gap-1.5 justify-center shadow-sm shrink-0 cursor-pointer">
          <Icon icon="material-symbols:filter-alt-outline" class="w-3.5 h-3.5" />
          Filter
          <!-- Active Indicator Dot -->
          <span v-if="hasActiveFilters" class="absolute -top-1 -right-1 flex h-3 w-3">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#4A7043] opacity-75"></span>
            <span class="relative inline-flex rounded-full h-3 w-3 bg-[#4A7043] border border-white"></span>
          </span>
        </button>
      </div>

      <!-- Right Controls -->
      <div class="flex flex-row gap-2 w-auto shrink-0">
        <button @click="openLaporanModal" :disabled="isLoading" class="px-3 py-1.5 bg-[#4A7043] hover:bg-[#3D5A35] text-white rounded-lg text-xs font-bold transition-colors flex items-center gap-1.5 shadow-sm shrink-0 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
          <Icon icon="material-symbols:description-outline" class="w-3.5 h-3.5" />
          Lihat Laporan
        </button>
        <button @click="handleExportExcel" :disabled="isExportingExcel || isLoading" class="px-3 py-1.5 bg-[#4A7043] hover:bg-[#3D5A35] text-white rounded-lg text-xs font-bold transition-colors flex items-center gap-1.5 shadow-sm shrink-0 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
          <Icon v-if="isExportingExcel" icon="line-md:loading-twotone-loop" class="w-3.5 h-3.5" />
          <Icon v-else icon="material-symbols:download" class="w-3.5 h-3.5" />
          {{ isExportingExcel ? 'Mengekspor...' : 'Export Excel' }}
        </button>
      </div>
    </div>

    <!-- Active Filter Chips Indicator Section -->
    <div v-if="hasActiveFilters" class="flex flex-wrap items-center gap-2 px-1">
      <span class="text-[11px] font-bold text-stone-500 mr-1">Filter Aktif:</span>
      <div v-if="appliedFilter.gudang && appliedFilter.gudang !== 'Semua Gudang'" class="inline-flex items-center gap-1.5 px-3 py-1 bg-[#F5F9F5] text-[#3D5A35] rounded-full text-[11px] font-bold border border-[#4A7043]/20 shadow-sm transition-all hover:bg-[#E9F5E9]">
        Gudang: {{ appliedFilter.gudang }}
        <button @click="removeFilter('gudang')" class="text-[#4A7043]/50 hover:text-red-500 transition-colors ml-1 focus:outline-none">
          <Icon icon="material-symbols:close" class="w-3.5 h-3.5" />
        </button>
      </div>
      <div v-if="appliedFilter.role && appliedFilter.role !== 'Semua Role'" class="inline-flex items-center gap-1.5 px-3 py-1 bg-[#F5F9F5] text-[#3D5A35] rounded-full text-[11px] font-bold border border-[#4A7043]/20 shadow-sm transition-all hover:bg-[#E9F5E9]">
        Role: {{ appliedFilter.role }}
        <button @click="removeFilter('role')" class="text-[#4A7043]/50 hover:text-red-500 transition-colors ml-1 focus:outline-none">
          <Icon icon="material-symbols:close" class="w-3.5 h-3.5" />
        </button>
      </div>
      <div v-if="data.start_date || data.end_date" class="inline-flex items-center gap-1.5 px-3 py-1 bg-[#F5F9F5] text-[#3D5A35] rounded-full text-[11px] font-bold border border-[#4A7043]/20 shadow-sm transition-all hover:bg-[#E9F5E9]">
        Waktu: {{ formatDateRangeText }}
        <button @click="removeFilter('durasi')" class="text-[#4A7043]/50 hover:text-red-500 transition-colors ml-1 focus:outline-none">
          <Icon icon="material-symbols:close" class="w-3.5 h-3.5" />
        </button>
      </div>
      <div v-for="jenis in appliedFilter.jenisSampah" :key="jenis" class="inline-flex items-center gap-1.5 px-3 py-1 bg-[#F5F9F5] text-[#3D5A35] rounded-full text-[11px] font-bold border border-[#4A7043]/20 shadow-sm transition-all hover:bg-[#E9F5E9]">
        {{ jenis }}
        <button @click="removeFilter('jenisSampah', jenis)" class="text-[#4A7043]/50 hover:text-red-500 transition-colors ml-1 focus:outline-none">
          <Icon icon="material-symbols:close" class="w-3.5 h-3.5" />
        </button>
      </div>
      <button @click="clearAllFilters" class="text-[11px] font-bold text-stone-400 hover:text-red-500 transition-colors ml-2 underline">
        Hapus Semua
      </button>
    </div>

    <!-- Table Container -->
    <div class="bg-white rounded-2xl shadow-sm border border-stone-100 overflow-hidden">
      <!-- Skeleton Loader -->
      <div v-if="isLoading" class="p-6">
        <div class="grid grid-cols-8 gap-4 mb-4 pb-4 border-b border-stone-100">
          <div v-for="i in 8" :key="`th-${i}`" class="h-3 bg-stone-200 rounded animate-pulse"></div>
        </div>
        <div class="space-y-4">
          <div v-for="r in 10" :key="`tr-${r}`" class="grid grid-cols-8 gap-4 items-center">
            <div v-for="i in 8" :key="`td-${r}-${i}`" class="h-3 bg-stone-100 rounded animate-pulse"></div>
          </div>
        </div>
      </div>

      <div v-else class="overflow-x-auto">
        <table class="w-full text-left border-collapse min-w-[900px]">
          <thead>
            <tr class="bg-[#F5F5F0] border-b border-stone-200">
              <th class="py-4 px-6 text-xs font-bold text-stone-600 uppercase tracking-wider">Tanggal</th>
              <th class="py-4 px-6 text-xs font-bold text-stone-600 uppercase tracking-wider">Role</th>
              <th class="py-4 px-6 text-xs font-bold text-stone-600 uppercase tracking-wider">Jenis Sampah</th>
              <th class="py-4 px-6 text-xs font-bold text-stone-600 uppercase tracking-wider">Berat</th>
              <th class="py-4 px-6 text-xs font-bold text-stone-600 uppercase tracking-wider text-center">Sumber</th>
              <th class="py-4 px-6 text-xs font-bold text-stone-600 uppercase tracking-wider text-center">Status</th>
              <th v-if="isGroupedByGudang" class="py-4 px-6 text-xs font-bold text-stone-600 uppercase tracking-wider text-center">Aksi</th>
            </tr>
          </thead>

          <!-- GROUPED VIEW -->
          <tbody v-if="isGroupedByGudang" class="divide-y divide-stone-100">
            <template v-if="filteredGroupedData.length === 0">
              <tr>
                <td colspan="8" class="py-8 text-center text-sm font-bold text-stone-400">Tidak ada data yang sesuai filter</td>
              </tr>
            </template>
            <template v-else v-for="(group, idx) in filteredGroupedData" :key="idx">
              <!-- Group Header Row -->
              <tr class="bg-[#E9F5E9] border-b border-stone-100">
                <td :colspan="isGroupedByGudang ? 7 : 6" class="py-3 px-6">
                  <div class="flex justify-between items-center w-full">
                    <span class="text-sm font-black text-[#3D5A35]">{{ group.gudangName }}</span>
                    <span class="text-xs font-bold text-[#3D5A35]">{{ group.summary }}</span>
                  </div>
                </td>
              </tr>
              <!-- Group Rows -->
              <tr v-for="(row, rowIdx) in group.rows" :key="`${idx}-${rowIdx}`" class="hover:bg-stone-50 transition-colors group">
                <td class="py-4 px-6 text-sm text-stone-600 font-medium whitespace-nowrap">{{ row.tanggal }}</td>
                <td class="py-4 px-6 text-sm text-stone-800 font-medium whitespace-nowrap">{{ row.role }}</td>
                <td class="py-4 px-6 text-sm text-stone-600 font-medium whitespace-nowrap">{{ row.jenis }}</td>
                <td class="py-4 px-6 text-sm text-stone-800 font-bold whitespace-nowrap">{{ row.berat }} kg</td>
                <td class="py-4 px-6 whitespace-nowrap text-center">
                  <div v-if="row.sumber === 'Jemput'" class="inline-flex items-center justify-center px-4 py-1.5 rounded-full bg-[#E9F5E9] text-[#3D5A35] text-[10px] font-bold tracking-wider shadow-sm min-w-[70px] border border-[#3D5A35]/20">
                    Jemput
                  </div>
                  <div v-else-if="row.sumber === 'Pengepul'" class="inline-flex items-center justify-center px-4 py-1.5 rounded-full bg-[#E9F0FA] text-[#2563EB] text-[10px] font-bold tracking-wider shadow-sm min-w-[70px] border border-[#2563EB]/20">
                    Pengepul
                  </div>
                  <div v-else class="inline-flex items-center justify-center px-4 py-1.5 rounded-full bg-stone-100 text-stone-600 text-[10px] font-bold tracking-wider shadow-sm min-w-[70px] border border-stone-200">
                    Setor Manual
                  </div>
                </td>
                <td class="py-4 px-6 whitespace-nowrap text-center">
                  <div v-if="row.status === 'Selesai'" class="inline-flex items-center justify-center px-4 py-1.5 rounded-full bg-[#3D5A35] text-white text-[10px] font-bold tracking-wider shadow-sm min-w-[70px]">
                    Selesai
                  </div>
                  <div v-else class="inline-flex items-center justify-center px-4 py-1.5 rounded-full bg-[#DC2626] text-white text-[10px] font-bold tracking-wider shadow-sm min-w-[70px]">
                    Tidak Terlaksana
                  </div>
                </td>
                <td class="py-4 px-6 text-xs font-bold text-[#4A7043] cursor-pointer hover:underline whitespace-nowrap text-center transition-colors" @click="openDetailModal(row)">
                  Detail
                </td>
              </tr>
            </template>
          </tbody>

          <!-- FLAT VIEW -->
          <tbody v-else class="divide-y divide-stone-100">
            <template v-if="filteredFlatData.length === 0">
              <tr>
                <td colspan="8" class="py-8 text-center text-sm font-bold text-stone-400">Tidak ada data yang sesuai filter</td>
              </tr>
            </template>
            <template v-else>
              <tr v-for="(row, index) in filteredFlatData" :key="index" class="hover:bg-stone-50 transition-colors group">
                <td class="py-4 px-6 text-sm text-stone-600 font-medium whitespace-nowrap">{{ row.tanggal }}</td>
                <td class="py-4 px-6 text-sm text-stone-800 font-medium whitespace-nowrap">{{ row.role }}</td>
                <td class="py-4 px-6 text-sm text-stone-600 font-medium whitespace-nowrap">{{ row.jenis }}</td>
                <td class="py-4 px-6 text-sm text-stone-800 font-bold whitespace-nowrap">{{ row.berat }} kg</td>
                <td class="py-4 px-6 whitespace-nowrap text-center">
                  <div v-if="row.sumber === 'Jemput'" class="inline-flex items-center justify-center px-4 py-1.5 rounded-full bg-[#E9F5E9] text-[#3D5A35] text-[10px] font-bold tracking-wider shadow-sm min-w-[70px] border border-[#3D5A35]/20">
                    Jemput
                  </div>
                  <div v-else-if="row.sumber === 'Pengepul'" class="inline-flex items-center justify-center px-4 py-1.5 rounded-full bg-[#E9F0FA] text-[#2563EB] text-[10px] font-bold tracking-wider shadow-sm min-w-[70px] border border-[#2563EB]/20">
                    Pengepul
                  </div>
                  <div v-else class="inline-flex items-center justify-center px-4 py-1.5 rounded-full bg-stone-100 text-stone-600 text-[10px] font-bold tracking-wider shadow-sm min-w-[70px] border border-stone-200">
                    Setor Manual
                  </div>
                </td>
                <td class="py-4 px-6 whitespace-nowrap text-center">
                  <div v-if="row.status === 'Selesai'" class="inline-flex items-center justify-center px-4 py-1.5 rounded-full bg-[#3D5A35] text-white text-[10px] font-bold tracking-wider shadow-sm min-w-[70px]">
                    Selesai
                  </div>
                  <div v-else class="inline-flex items-center justify-center px-4 py-1.5 rounded-full bg-[#DC2626] text-white text-[10px] font-bold tracking-wider shadow-sm min-w-[70px]">
                    Tidak Terlaksana
                  </div>
                </td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="!isLoading" class="bg-white p-4 rounded-2xl shadow-sm border border-stone-100 flex flex-col sm:flex-row justify-between items-center gap-4">
      <div class="flex items-center gap-3">
        <span class="text-sm font-medium text-stone-500">Tampilkan</span>
        <select v-model="itemsPerPage" @change="fetchData" class="bg-stone-50 border border-stone-200 rounded-lg px-3 py-1.5 text-sm font-bold text-stone-600 focus:outline-none focus:ring-2 focus:ring-[#4A7043]/20 focus:border-[#4A7043] cursor-pointer shadow-sm">
          <option :value="10">10</option>
          <option :value="20">20</option>
          <option :value="50">50</option>
        </select>
      </div>
      <div class="flex items-center gap-2">
        <button @click="prevPage" :disabled="currentPage === 1" class="px-4 py-2 rounded-lg text-sm font-bold text-stone-500 hover:bg-stone-100 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed transition-colors">Sebelumnya</button>
        <button class="w-8 h-8 flex items-center justify-center rounded-lg bg-[#4A7043] text-white text-sm font-bold shadow-sm">{{ currentPage }}</button>
        <button @click="nextPage" :disabled="currentPage === totalPages" class="px-4 py-2 rounded-lg text-sm font-bold text-stone-500 hover:bg-stone-100 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed transition-colors">Selanjutnya</button>
      </div>
    </div>
  </div>

  <!-- Filter Modal -->
  <Teleport to="body">
  <div v-if="isFilterModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="isFilterModalOpen = false"></div>
    <div class="relative bg-white rounded-2xl w-full max-w-md shadow-2xl animate-in zoom-in-95 duration-200">
      <div class="flex items-center justify-between p-6 border-b border-stone-100">
        <h3 class="text-lg font-bold text-stone-800">Filter Data</h3>
        <button @click="isFilterModalOpen = false" class="text-stone-400 hover:text-stone-600 transition-colors cursor-pointer">
          <Icon icon="material-symbols:close" class="w-6 h-6" />
        </button>
      </div>
      <div class="p-6 space-y-6">
        <div class="space-y-2">
          <label class="block text-sm font-bold text-stone-600">Gudang</label>
          <div class="relative">
            <select v-model="filterForm.gudang" class="w-full appearance-none px-4 py-2.5 bg-white border border-stone-200 rounded-lg text-sm text-stone-700 focus:outline-none focus:ring-2 focus:ring-[#4A7043]/20 focus:border-[#4A7043] transition-colors cursor-pointer">
              <option value="" disabled>Pilih Gudang...</option>
              <option value="Semua Gudang">Semua Gudang</option>
              <option v-for="g in gudangOptions" :key="g.gudang_id" :value="g.alamat">{{ g.alamat }}</option>
            </select>
            <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-stone-400">
              <Icon icon="material-symbols:keyboard-arrow-down" class="w-5 h-5" />
            </div>
          </div>
        </div>
        <div class="space-y-2">
          <label class="block text-sm font-bold text-stone-600">Role</label>
          <div class="relative">
            <select v-model="filterForm.role" class="w-full appearance-none px-4 py-2.5 bg-white border border-stone-200 rounded-lg text-sm text-stone-700 focus:outline-none focus:ring-2 focus:ring-[#4A7043]/20 focus:border-[#4A7043] transition-colors cursor-pointer">
              <option value="Semua Role">Semua Role</option>
              <option value="Nasabah">Nasabah</option>
              <option value="Pengepul">Pengepul</option>
            </select>
            <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-stone-400">
              <Icon icon="material-symbols:keyboard-arrow-down" class="w-5 h-5" />
            </div>
          </div>
        </div>
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
        <div class="space-y-3">
          <label class="block text-sm font-bold text-stone-600">Jenis Sampah</label>
          <div class="flex flex-wrap gap-2 max-h-40 overflow-y-auto pr-2 custom-scrollbar">
            <button v-for="jenis in jenisSampahOptions" :key="jenis" @click="toggleJenisSampah(jenis)"
              :class="['px-4 py-1.5 rounded-full text-xs font-bold transition-all border cursor-pointer', filterForm.jenisSampah.includes(jenis) ? 'bg-[#4A7043] text-white border-[#4A7043] shadow-sm' : 'bg-white text-stone-600 border-stone-200 hover:border-[#4A7043] hover:text-[#4A7043]']">
              {{ jenis }}
            </button>
          </div>
          <p class="text-[10px] text-stone-400 mt-2">Kosongkan untuk menampilkan semua jenis</p>
        </div>
      </div>
      <div class="p-6 border-t border-stone-100 flex gap-3">
        <button @click="resetFilterForm" class="flex-1 px-4 py-2.5 bg-white border border-stone-200 text-stone-600 rounded-lg text-sm font-bold hover:bg-stone-50 transition-colors cursor-pointer">Reset Filter</button>
        <button @click="terapkanFilter" class="flex-1 px-4 py-2.5 bg-[#4A7043] text-white rounded-lg text-sm font-bold hover:bg-[#3D5A35] transition-colors shadow-sm cursor-pointer">Terapkan Filter</button>
      </div>
    </div>
  </div>
  </Teleport>

  <!-- Laporan Modal -->
  <Teleport to="body">
  <div v-if="isLaporanModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="isLaporanModalOpen = false"></div>
    <div id="printable-laporan" class="relative bg-stone-50 rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] flex flex-col overflow-hidden animate-in zoom-in-95 duration-200">
      <div class="p-6 bg-white border-b border-stone-100 flex justify-between items-center shrink-0 z-10">
        <div>
          <h3 class="text-xl font-black text-stone-800">Preview Laporan Audit</h3>
          <p class="text-xs text-stone-500 mt-1">Periode: {{ formatDateRangeText }}</p>
        </div>
        <div id="laporan-actions" class="flex items-center gap-3">
          <button @click="handlePrintPdf" :disabled="isExportingPdf || isLoadingSummary" class="px-4 py-2 bg-[#4A7043] hover:bg-[#3D5A35] text-white rounded-lg text-sm font-bold transition-colors flex items-center gap-2 shadow-sm cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
            <Icon v-if="isExportingPdf" icon="line-md:loading-twotone-loop" class="w-4 h-4" />
            <Icon v-else icon="material-symbols:print-outline" class="w-4 h-4" />
            {{ isExportingPdf ? 'Mencetak...' : 'Print' }}
          </button>
          <button @click="isLaporanModalOpen = false" class="text-stone-400 hover:text-stone-600 transition-colors cursor-pointer p-1">
            <Icon icon="material-symbols:close" class="w-6 h-6" />
          </button>
        </div>
      </div>
      <div id="laporan-scroll-area" class="p-6 overflow-y-auto custom-scrollbar flex-1 space-y-6 relative z-0">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <div class="bg-[#F9FAF8] p-5 rounded-xl border border-stone-100 shadow-sm">
            <p class="text-xs font-bold text-stone-500 mb-1">Total Transaksi</p>
            <div v-if="isLoadingSummary" class="h-8 bg-stone-200 rounded animate-pulse w-16 mt-1"></div>
            <p v-else class="text-2xl font-black text-[#3D5A35]">{{ laporanStats?.totalTransaksi || 0 }}</p>
          </div>
          <div class="bg-[#F9FAF8] p-5 rounded-xl border border-stone-100 shadow-sm">
            <p class="text-xs font-bold text-stone-500 mb-1">Total Berat</p>
            <div v-if="isLoadingSummary" class="h-8 bg-stone-200 rounded animate-pulse w-24 mt-1"></div>
            <p v-else class="text-2xl font-black text-[#3D5A35]">{{ laporanStats?.totalBerat?.toFixed(1) || '0.0' }} <span class="text-sm font-bold text-stone-500">kg</span></p>
          </div>
          <div class="bg-[#F9FAF8] p-5 rounded-xl border border-stone-100 shadow-sm">
            <p class="text-xs font-bold text-stone-500 mb-1">Selesai</p>
            <div v-if="isLoadingSummary" class="h-8 bg-stone-200 rounded animate-pulse w-16 mt-1"></div>
            <p v-else class="text-2xl font-black text-[#3D5A35]">{{ laporanStats?.verifiedCount || 0 }}</p>
          </div>
          <div class="bg-[#F9FAF8] p-5 rounded-xl border border-stone-100 shadow-sm">
            <p class="text-xs font-bold text-stone-500 mb-1">Tidak Terlaksana</p>
            <div v-if="isLoadingSummary" class="h-8 bg-stone-200 rounded animate-pulse w-16 mt-1"></div>
            <p v-else class="text-2xl font-black text-[#B45309]">{{ laporanStats?.pendingCount || 0 }}</p>
          </div>
        </div>
        <div class="bg-white rounded-xl border border-stone-100 shadow-sm overflow-hidden p-6">
          <h4 class="text-sm font-bold text-stone-800 mb-4">Distribusi Sumber Transaksi</h4>
          <div v-if="isLoadingSummary" class="flex gap-4">
            <div class="h-20 bg-stone-100 rounded animate-pulse flex-1"></div>
            <div class="h-20 bg-stone-100 rounded animate-pulse flex-1"></div>
          </div>
          <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-[#F9FAF8] p-4 rounded-lg">
              <p class="text-xs font-medium text-stone-600 mb-2">Request Jemput</p>
              <div class="flex items-baseline gap-1 mb-1">
                <span class="text-xl font-bold text-[#3D5A35]">{{ laporanStats?.jemputCount || 0 }}</span>
                <span class="text-xs text-stone-500 font-medium">transaksi</span>
              </div>
              <p class="text-xs text-stone-400 font-medium">{{ laporanStats?.jemputWeight?.toFixed(1) || '0.0' }} kg total</p>
            </div>
            <div class="bg-[#F9FAF8] p-4 rounded-lg">
              <p class="text-xs font-medium text-stone-600 mb-2">Setor Manual</p>
              <div class="flex items-baseline gap-1 mb-1">
                <span class="text-xl font-bold text-[#3D5A35]">{{ laporanStats?.setorManualCount || 0 }}</span>
                <span class="text-xs text-stone-500 font-medium">transaksi</span>
              </div>
              <p class="text-xs text-stone-400 font-medium">{{ laporanStats?.setorManualWeight?.toFixed(1) || '0.0' }} kg total</p>
            </div>
          </div>
        </div>
        <div class="bg-white rounded-xl border border-stone-100 shadow-sm overflow-hidden">
          <div class="p-5 border-b border-stone-100">
            <h4 class="text-sm font-bold text-stone-800">Data Per Gudang</h4>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
              <thead>
                <tr class="text-stone-500 bg-white border-b border-stone-50">
                  <th class="py-4 px-6 font-bold text-xs">Gudang</th>
                  <th class="py-4 px-6 font-bold text-xs">Transaksi</th>
                  <th class="py-4 px-6 font-bold text-xs">Total Berat</th>
                  <th class="py-4 px-6 font-bold text-xs text-center">Selesai</th>
                  <th class="py-4 px-6 font-bold text-xs text-center">Tidak Terlaksana</th>
                </tr>
              </thead>
              <tbody v-if="isLoadingSummary" class="divide-y divide-stone-100">
                <tr v-for="i in 3" :key="`skeleton-${i}`" class="animate-pulse">
                  <td class="py-4 px-6"><div class="h-4 bg-stone-200 rounded w-24"></div></td>
                  <td class="py-4 px-6"><div class="h-4 bg-stone-200 rounded w-8"></div></td>
                  <td class="py-4 px-6"><div class="h-4 bg-stone-200 rounded w-16"></div></td>
                  <td class="py-4 px-6"><div class="h-6 w-8 bg-stone-200 rounded-full mx-auto"></div></td>
                  <td class="py-4 px-6"><div class="h-6 w-8 bg-stone-200 rounded-full mx-auto"></div></td>
                </tr>
              </tbody>
              <tbody v-else class="divide-y divide-stone-100">
                <tr v-for="gudang in laporanStats?.perGudangList || []" :key="gudang.gudang" class="hover:bg-stone-50 transition-colors">
                  <td class="py-4 px-6 font-medium text-stone-600 text-xs">{{ gudang.gudang }}</td>
                  <td class="py-4 px-6 text-stone-600 text-xs">{{ gudang.transaksi }}</td>
                  <td class="py-4 px-6 text-stone-600 text-xs">{{ gudang.berat.toFixed(1) }} kg</td>
                  <td class="py-4 px-6 text-center">
                    <span class="inline-flex items-center justify-center w-8 h-6 rounded-full bg-[#3D5A35] text-white text-[10px] font-bold shadow-sm">{{ gudang.verified }}</span>
                  </td>
                  <td class="py-4 px-6 text-center">
                    <span class="inline-flex items-center justify-center w-8 h-6 rounded-full bg-[#F5F5F0] text-stone-500 border border-stone-200 text-[10px] font-bold shadow-sm">{{ gudang.pending }}</span>
                  </td>
                </tr>
                <tr v-if="!laporanStats?.perGudangList?.length">
                  <td colspan="5" class="py-8 text-center text-stone-400 text-xs">Tidak ada data gudang</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        <div class="bg-white rounded-xl border border-stone-100 shadow-sm overflow-hidden p-6">
          <h4 class="text-sm font-bold text-stone-800 mb-6">Distribusi Jenis Sampah</h4>
          <div v-if="isLoadingSummary" class="space-y-4">
            <div v-for="i in 4" :key="`skel-jenis-${i}`" class="flex items-center gap-4 animate-pulse">
              <div class="w-24 h-4 bg-stone-200 rounded shrink-0"></div>
              <div class="flex-1 h-7 bg-stone-100 rounded-full"></div>
              <div class="w-12 h-4 bg-stone-200 rounded shrink-0"></div>
            </div>
          </div>
          <div v-else class="space-y-4">
            <div v-for="sampah in laporanStats?.jenisSampahList || []" :key="sampah.name" class="flex items-center gap-4">
              <div class="w-24 shrink-0 text-xs font-medium text-stone-600 truncate" :title="sampah.name">{{ sampah.name }}</div>
              <div class="flex-1 h-7 bg-[#F5F5F0] rounded-full overflow-hidden relative">
                <div class="absolute top-0 left-0 h-full rounded-full transition-all duration-500"
                     :class="sampah.name === 'Organik' ? 'bg-[#3D5A35]' : sampah.name === 'Plastik PET' ? 'bg-[#4A7043]' : sampah.name === 'Kertas' ? 'bg-[#3D5A35] opacity-80' : 'bg-[#4A7043] opacity-80'"
                     :style="`width: ${sampah.percentage}%`"></div>
                <div class="absolute inset-0 flex items-center px-4 text-[10px] font-bold text-white justify-end" :style="`width: ${sampah.percentage}%`">
                  <span v-if="sampah.percentage > 5">{{ sampah.berat.toFixed(1) }} kg</span>
                </div>
              </div>
              <div class="w-12 shrink-0 text-right text-xs font-medium text-stone-500">{{ sampah.percentage.toFixed(1) }}%</div>
            </div>
            <div v-if="!laporanStats?.jenisSampahList?.length" class="text-center text-stone-400 text-xs py-4">Tidak ada data jenis sampah</div>
          </div>
        </div>
        <div class="bg-white rounded-xl border border-stone-100 shadow-sm overflow-hidden p-6">
          <h4 class="text-sm font-bold text-stone-800 mb-6">Ringkasan Penjualan Ke Pengepul</h4>
          <div v-if="isLoadingSummary" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="h-20 bg-stone-100 rounded animate-pulse"></div>
            <div class="h-20 bg-stone-100 rounded animate-pulse"></div>
            <div class="h-20 bg-stone-100 rounded animate-pulse"></div>
          </div>
          <div v-else class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-[#F9FAF8] p-4 rounded-lg">
              <p class="text-xs font-medium text-stone-600 mb-2">Total Terjual</p>
              <div class="flex items-baseline gap-1 mb-1">
                <span class="text-xl font-bold text-[#3D5A35]">{{ laporanStats?.totalPengepulBerat?.toFixed(1) || '0.0' }}</span>
                <span class="text-xs text-stone-500 font-medium">kg</span>
              </div>
              <p class="text-xs text-stone-400 font-medium">{{ laporanStats?.jumlahPengepul || 0 }} pengepul</p>
            </div>
            <div class="bg-[#F9FAF8] p-4 rounded-lg">
              <p class="text-xs font-medium text-stone-600 mb-2">Diterima Dari Pengepul</p>
              <div class="flex items-baseline gap-1 mb-1">
                <span class="text-xl font-bold text-[#3D5A35]">Rp {{ new Intl.NumberFormat("id-ID").format(laporanStats?.totalPengepulDiterima || 0) }}</span>
              </div>
              <p class="text-xs text-stone-400 font-medium">Dibayar ke Nasabah: Rp {{ new Intl.NumberFormat("id-ID").format(laporanStats?.totalDibayarNasabah || 0) }}</p>
            </div>
            <div class="bg-[#F9FAF8] p-4 rounded-lg bg-[#FDFBF7] border border-amber-100">
              <p class="text-xs font-medium text-stone-600 mb-2">Keuntungan Bersih</p>
              <div class="flex items-baseline gap-1 mb-1">
                <span class="text-xl font-bold text-[#B45309]">Rp {{ new Intl.NumberFormat("id-ID").format(laporanStats?.totalPengepulKeuntungan || 0) }}</span>
              </div>
              <p class="text-xs text-amber-700/80 font-medium">Margin: {{ laporanStats?.marginNasabah?.toFixed(1) || '0.0' }}%</p>
            </div>
          </div>
        </div>

      </div>
      <div class="p-4 border-t border-stone-100 flex justify-between items-center shrink-0 bg-white z-10">
        <span class="text-[10px] font-bold text-stone-400">Digenerate pada: {{ generateTimeText }}</span>
        <span class="text-[10px] font-bold text-stone-400">Bank Sampah Management System</span>
      </div>
    </div>
  </div>
  </Teleport>

  <!-- Detail Modal -->
  <Teleport to="body">
  <div v-if="isDetailModalOpen" class="fixed inset-0 z-[60] flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="isDetailModalOpen = false"></div>
    <div class="relative bg-stone-50 rounded-2xl shadow-2xl w-full max-w-lg flex flex-col overflow-hidden animate-in zoom-in-95 duration-200">
      <div class="bg-[#4A7043] p-5 flex justify-between items-start shrink-0">
        <div>
          <h3 class="text-lg font-bold text-white">Detail Transaksi Sampah</h3>
          <p class="text-xs text-green-100 mt-1">{{ selectedDetailRow?.tanggal }} - {{ selectedDetailRow?.gudang }}</p>
        </div>
        <button @click="isDetailModalOpen = false" class="text-white/80 hover:text-white transition-colors p-1">
          <Icon icon="material-symbols:close" class="w-5 h-5" />
        </button>
      </div>
      <div class="p-6 overflow-y-auto custom-scrollbar max-h-[75vh]">
        <div class="bg-white rounded-xl p-5 mb-6 flex justify-between items-start border border-stone-100 shadow-sm">
          <div>
            <p class="text-[10px] font-bold text-stone-400 uppercase tracking-wider mb-1">Berat Sampah</p>
            <p class="text-3xl font-black text-[#4A7043] mb-1">{{ selectedDetailRow?.berat }} <span class="text-sm font-bold text-[#4A7043]">kg</span></p>
            <p class="text-xs font-medium text-stone-500">{{ selectedDetailRow?.jenis }}</p>
          </div>
          <div class="flex flex-col gap-2 items-end mt-1">
            <span class="inline-flex items-center justify-center px-4 py-1.5 rounded-full text-[10px] font-bold shadow-sm"
                  :class="selectedDetailRow?.status === 'Selesai' ? 'bg-[#3D5A35] text-white' : 'bg-[#DC2626] text-white'">
              {{ selectedDetailRow?.status }}
            </span>
            <span class="inline-flex items-center justify-center px-4 py-1.5 rounded-full text-[10px] font-bold shadow-sm"
                  :class="selectedDetailRow?.sumber === 'Jemput' ? 'bg-[#E9F5E9] border border-[#3D5A35]/20 text-[#3D5A35]' : (selectedDetailRow?.sumber === 'Pengepul' ? 'bg-[#E9F0FA] border border-[#2563EB]/20 text-[#2563EB]' : 'bg-stone-100 border border-stone-200 text-stone-600')">
              {{ selectedDetailRow?.sumber }}
            </span>
          </div>
        </div>
        <div class="grid grid-cols-2 gap-y-6 gap-x-4 bg-white p-5 rounded-xl border border-stone-100 shadow-sm">
          <div>
            <p class="text-[10px] font-bold text-stone-400 uppercase tracking-wider mb-1">Tanggal</p>
            <p class="text-sm font-bold text-stone-800">{{ selectedDetailRow?.tanggal }}</p>
          </div>
          <div>
            <p class="text-[10px] font-bold text-stone-400 uppercase tracking-wider mb-1">{{ selectedDetailRow?.role === 'Pengepul' ? 'Pengepul' : 'Nasabah' }}</p>
            <p class="text-sm font-bold text-stone-800">{{ selectedDetailRow?.nasabah }}</p>
          </div>
          <div>
            <p class="text-[10px] font-bold text-stone-400 uppercase tracking-wider mb-1">Gudang</p>
            <p class="text-sm font-bold text-stone-800">{{ selectedDetailRow?.gudang }}</p>
          </div>
          <div>
            <p class="text-[10px] font-bold text-stone-400 uppercase tracking-wider mb-1">Petugas</p>
            <p class="text-sm font-bold text-stone-800">{{ selectedDetailRow?.petugas }}</p>
          </div>
          <div>
            <p class="text-[10px] font-bold text-stone-400 uppercase tracking-wider mb-1">Tukang Penjemput</p>
            <p class="text-sm font-bold text-stone-800">{{ selectedDetailRow?.tukang }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
  </Teleport>
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
