<script setup>
import { ref, onMounted } from "vue";
import { Icon } from "@iconify/vue";
import Swal from 'sweetalert2';
import dayjs from 'dayjs';
import { getAuditPenarikanData, getAuditPenarikanSummary } from '@/lib/api/manager/auditApi';

const isLoadingPenarikan = ref(false);
const currentPagePenarikan = ref(1);
const totalPagesPenarikan = ref(1);
const itemsPerPage = ref(10);
const searchQueryPenarikan = ref('');
const tableDataPenarikan = ref([]);

const penarikanSummary = ref({
  totalNominal: 'Rp 0',
  selesai: 0,
  diproses: 0,
  ditolak: 0
});

const fetchPenarikanData = async () => {
  try {
    isLoadingPenarikan.value = true;
    const params = {
      search: searchQueryPenarikan.value
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
      search: searchQueryPenarikan.value
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
          <input type="text" v-model="searchQueryPenarikan" @input="handleSearchPenarikan" placeholder="Cari ID, nasabah, metode..." class="w-full pl-9 pr-3 py-1.5 bg-white border border-stone-200 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-[#4A7043]/20 focus:border-[#4A7043] transition-colors text-stone-700" />
        </div>
      </div>
      <div class="flex flex-row gap-2 w-auto shrink-0">
        <button :disabled="isLoadingPenarikan" class="px-4 py-1.5 bg-white border border-stone-200 rounded-lg text-xs font-bold text-stone-600 hover:bg-stone-50 transition-colors flex items-center gap-1.5 shadow-sm shrink-0 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
          <Icon icon="material-symbols:filter-alt-outline" class="w-3.5 h-3.5" /> Filter
        </button>
        <button :disabled="isLoadingPenarikan" class="px-4 py-1.5 bg-[#4A7043] hover:bg-[#3D5A35] text-white rounded-lg text-xs font-bold transition-colors flex items-center gap-1.5 shadow-sm shrink-0 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
          Lihat Laporan
        </button>
        <button :disabled="isLoadingPenarikan" class="px-4 py-1.5 bg-[#4A7043] hover:bg-[#3D5A35] text-white rounded-lg text-xs font-bold transition-colors flex items-center gap-1.5 shadow-sm shrink-0 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
          Export CSV
        </button>
      </div>
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
