<script setup>
import { ref, computed, onMounted } from "vue";
import { Icon } from "@iconify/vue";
import DashboardLayout from "@/layouts/DashboardLayout.vue";
import Swal from 'sweetalert2';
import dayjs from 'dayjs';
import { getListGudang, getListSampah, fetchAllRiwayatPenjemputan, fetchAllRiwayatPenarikan, exportLaporanExcel, exportLaporanPdf } from '@/lib/api/manager/auditApi';

const activeTab = ref('Riwayat Sampah');
const isGroupedByGudang = ref(true);

const isFilterModalOpen = ref(false);
const isLaporanModalOpen = ref(false);

const isLoading = ref(false);
const loadingProgress = ref(0);
const isExportingExcel = ref(false);
const isExportingPdf = ref(false);

const gudangOptions = ref([]);
const jenisSampahOptions = ref([]);
const rawRiwayatData = ref([]);


// --- Penarikan State ---
const rawRiwayatPenarikanData = ref([]);
const searchPenarikan = ref('');
const currentPenarikanPage = ref(1);
const penarikanItemsPerPage = ref(10);

const filterPenarikanForm = ref({
  gudang: 'Semua Gudang',
  start_date: '',
  end_date: '',
  status: []
});
const appliedPenarikanFilter = ref({
  gudang: 'Semua Gudang',
  start_date: '',
  end_date: '',
  status: []
});

const isFilterPenarikanModalOpen = ref(false);
const isDetailPenarikanModalOpen = ref(false);
const selectedPenarikan = ref(null);

const statusPenarikanOptions = ['selesai', 'pending', 'proses', 'tolak'];

const formatRupiah = (value) => {
  return new Intl.NumberFormat("id-ID", {
    style: "currency",
    currency: "IDR",
    minimumFractionDigits: 0,
  }).format(value);
};

const formatStatusPenarikan = (status) => {
  if (status === 'selesai') return 'Selesai';
  if (status === 'pending' || status === 'proses') return 'Diproses';
  if (status === 'tolak') return 'Ditolak';
  return status;
};

const getStatusColorPenarikan = (status) => {
  if (status === 'selesai') return 'bg-[#4A7043] text-white';
  if (status === 'pending' || status === 'proses') return 'bg-[#F59E0B] text-white';
  if (status === 'tolak') return 'bg-[#EF4444] text-white';
  return 'bg-stone-500 text-white';
};

const openFilterPenarikanModal = () => {
  filterPenarikanForm.value.gudang = appliedPenarikanFilter.value.gudang;
  filterPenarikanForm.value.start_date = appliedPenarikanFilter.value.start_date;
  filterPenarikanForm.value.end_date = appliedPenarikanFilter.value.end_date;
  filterPenarikanForm.value.status = [...appliedPenarikanFilter.value.status];
  isFilterPenarikanModalOpen.value = true;
};

const terapkanFilterPenarikan = () => {
  appliedPenarikanFilter.value.gudang = filterPenarikanForm.value.gudang;
  appliedPenarikanFilter.value.start_date = filterPenarikanForm.value.start_date;
  appliedPenarikanFilter.value.end_date = filterPenarikanForm.value.end_date;
  appliedPenarikanFilter.value.status = [...filterPenarikanForm.value.status];
  currentPenarikanPage.value = 1;
  isFilterPenarikanModalOpen.value = false;
};

const resetFilterPenarikanForm = () => {
  filterPenarikanForm.value.gudang = 'Semua Gudang';
  filterPenarikanForm.value.start_date = '';
  filterPenarikanForm.value.end_date = '';
  filterPenarikanForm.value.status = [];
};

const toggleStatusPenarikan = (status) => {
  const index = filterPenarikanForm.value.status.indexOf(status);
  if (index === -1) filterPenarikanForm.value.status.push(status);
  else filterPenarikanForm.value.status.splice(index, 1);
};

const openDetailPenarikan = (row) => {
  selectedPenarikan.value = row;
  isDetailPenarikanModalOpen.value = true;
};

const filteredPenarikanData = computed(() => {
  let result = rawRiwayatPenarikanData.value;
  
  if (searchPenarikan.value) {
    const s = searchPenarikan.value.toLowerCase();
    result = result.filter(r => 
      (r.penarikan_id && r.penarikan_id.toLowerCase().includes(s)) ||
      (r.nasabah?.nama && r.nasabah.nama.toLowerCase().includes(s)) ||
      (r.nama_bank && r.nama_bank.toLowerCase().includes(s))
    );
  }

  if (appliedPenarikanFilter.value.gudang !== 'Semua Gudang') {
    // Note: If penarikan doesn't have gudang directly, we might filter by petugas's gudang if available.
    // For now we check if we can. Since riwayat doesn't eager load gudang, this might not work perfectly unless added to backend.
    result = result.filter(r => r.petugas?.gudang?.alamat === appliedPenarikanFilter.value.gudang || r.petugas?.gudang?.nama_gudang === appliedPenarikanFilter.value.gudang);
  }

  if (appliedPenarikanFilter.value.status.length > 0) {
    result = result.filter(r => {
      const mappedStatus = formatStatusPenarikan(r.status).toLowerCase();
      // map to generic status strings
      const formStatuses = appliedPenarikanFilter.value.status.map(s => {
          if(s === 'selesai') return 'selesai';
          if(s === 'pending' || s === 'proses') return 'diproses';
          if(s === 'tolak') return 'ditolak';
          return s;
      });
      return formStatuses.includes(mappedStatus);
    });
  }

  if (appliedPenarikanFilter.value.start_date) {
    result = result.filter(r => dayjs(r.created_at).format('YYYY-MM-DD') >= appliedPenarikanFilter.value.start_date);
  }
  if (appliedPenarikanFilter.value.end_date) {
    result = result.filter(r => dayjs(r.created_at).format('YYYY-MM-DD') <= appliedPenarikanFilter.value.end_date);
  }

  return result.sort((a,b) => new Date(b.created_at) - new Date(a.created_at));
});

const totalPenarikanPages = computed(() => Math.ceil(filteredPenarikanData.value.length / penarikanItemsPerPage.value) || 1);

const paginatedPenarikanData = computed(() => {
  const start = (currentPenarikanPage.value - 1) * penarikanItemsPerPage.value;
  return filteredPenarikanData.value.slice(start, start + penarikanItemsPerPage.value);
});

const prevPenarikanPage = () => { if (currentPenarikanPage.value > 1) currentPenarikanPage.value--; };
const nextPenarikanPage = () => { if (currentPenarikanPage.value < totalPenarikanPages.value) currentPenarikanPage.value++; };

const totalNominalPenarikan = computed(() => {
  return rawRiwayatPenarikanData.value.filter(r => r.status === 'selesai').reduce((sum, r) => sum + parseFloat(r.jumlah || 0), 0);
});
const countPenarikanSelesai = computed(() => rawRiwayatPenarikanData.value.filter(r => r.status === 'selesai').length);
const countPenarikanDiproses = computed(() => rawRiwayatPenarikanData.value.filter(r => r.status === 'pending' || r.status === 'proses').length);
const countPenarikanDitolak = computed(() => rawRiwayatPenarikanData.value.filter(r => r.status === 'tolak').length);

const hasActivePenarikanFilters = computed(() => {
  return appliedPenarikanFilter.value.gudang !== 'Semua Gudang' ||
         appliedPenarikanFilter.value.status.length > 0 ||
         appliedPenarikanFilter.value.start_date !== '' ||
         appliedPenarikanFilter.value.end_date !== '';
});
// --- End Penarikan State ---

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
  jenisSampah: [],
  start_date: formatDate(thirtyDaysAgo),
  end_date: formatDate(today)
});
const appliedFilter = ref({
  gudang: 'Semua Gudang',
  jenisSampah: []
});

const currentPage = ref(1);
const itemsPerPage = ref(10);

const toggleJenisSampah = (jenis) => {
  const index = filterForm.value.jenisSampah.indexOf(jenis);
  if (index === -1) filterForm.value.jenisSampah.push(jenis);
  else filterForm.value.jenisSampah.splice(index, 1);
};

const resetFilterForm = () => {
  filterForm.value.gudang = 'Semua Gudang';
  filterForm.value.jenisSampah = [];
  filterForm.value.start_date = formatDate(thirtyDaysAgo);
  filterForm.value.end_date = formatDate(today);
};

const openFilterModal = () => {
  filterForm.value.gudang = appliedFilter.value.gudang;
  filterForm.value.jenisSampah = [...appliedFilter.value.jenisSampah];
  filterForm.value.start_date = data.value.start_date;
  filterForm.value.end_date = data.value.end_date;
  isFilterModalOpen.value = true;
};

const terapkanFilter = () => {
  appliedFilter.value.gudang = filterForm.value.gudang;
  appliedFilter.value.jenisSampah = [...filterForm.value.jenisSampah];
  data.value.start_date = filterForm.value.start_date;
  data.value.end_date = filterForm.value.end_date;
  currentPage.value = 1;
  isFilterModalOpen.value = false;
};

const removeFilter = (type, value = null) => {
  if (type === 'gudang') appliedFilter.value.gudang = 'Semua Gudang';
  if (type === 'durasi') {
    data.value.start_date = '';
    data.value.end_date = '';
  }
  if (type === 'jenisSampah') appliedFilter.value.jenisSampah = appliedFilter.value.jenisSampah.filter(j => j !== value);
  currentPage.value = 1;
};

const clearAllFilters = () => {
  appliedFilter.value.gudang = 'Semua Gudang';
  appliedFilter.value.jenisSampah = [];
  data.value.start_date = '';
  data.value.end_date = '';
  currentPage.value = 1;
};

const hasActiveFilters = computed(() => {
  return appliedFilter.value.gudang !== 'Semua Gudang' ||
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

const fetchData = async () => {
  try {
    isLoading.value = true;
    loadingProgress.value = 0;

    // Fake progress interval to make it feel alive while waiting for the first request
    const fakeProgressInterval = setInterval(() => {
      if (loadingProgress.value < 45) {
        loadingProgress.value += Math.floor(Math.random() * 5) + 1;
      }
    }, 400);

    const [gudangRes, sampahRes] = await Promise.all([
      getListGudang(),
      getListSampah()
    ]);
    gudangOptions.value = gudangRes.data;
    const sampahList = sampahRes.data.data ? sampahRes.data.data : sampahRes.data; 
    jenisSampahOptions.value = [...new Set(sampahList.map(s => s.item_sampah?.nama || s.nama).filter(Boolean))];
    if(jenisSampahOptions.value.length === 0) jenisSampahOptions.value = ['Organik', 'Plastik PET', 'Kertas', 'Logam']; 


    const riwayat = await fetchAllRiwayatPenjemputan((progress) => {
      // Real progress overrides fake progress
      loadingProgress.value = Math.max(loadingProgress.value, progress / 2);
    });

    const riwayatPenarikan = await fetchAllRiwayatPenarikan((progress) => {
      loadingProgress.value = Math.max(loadingProgress.value, 50 + (progress / 2));
    });
    
    clearInterval(fakeProgressInterval);
    loadingProgress.value = 100;

    rawRiwayatData.value = riwayat;
    rawRiwayatPenarikanData.value = riwayatPenarikan;

  } catch (error) {
    console.error("Error fetching audit data:", error);
    Swal.fire({
      icon: 'error',
      title: 'Gagal memuat data',
      text: 'Terjadi kesalahan saat menghubungi server.',
      confirmButtonColor: '#4A7043'
    });
  } finally {
    // Add small delay so user can see 100%
    setTimeout(() => {
      isLoading.value = false;
    }, 300);
  }
};

onMounted(() => {
  fetchData();
});

const flatTableData = computed(() => {
  const flat = [];
  rawRiwayatData.value.forEach(penjemputan => {
    const hasPenimbangan = penjemputan.penimbangan && penjemputan.penimbangan.length > 0;
    const details = penjemputan.detail_penjemputan || penjemputan.detailPenjemputan || [];
    const hasDetails = details.length > 0;

    if (hasPenimbangan) {
      penjemputan.penimbangan.forEach(p => {
        flat.push({
          tanggal: dayjs(penjemputan.created_at).format('DD MMM YYYY'),
          nasabah: penjemputan.nasabah?.nama || 'Unknown',
          gudang: penjemputan.gudang?.alamat || penjemputan.gudang?.nama_gudang || 'Unknown Gudang',
          jenis: p.sampah?.item_sampah?.nama || 'Unknown',
          berat: parseFloat(p.berat_timbang) || 0,
          petugas: penjemputan.tukang?.nama || 'Unknown',
          status: p.transaksi?.status === 'selesai' ? 'Verified' : 'Pending',
          rawDate: dayjs(penjemputan.created_at)
        });
      });
    } else if (hasDetails) {
      details.forEach(d => {
        flat.push({
          tanggal: dayjs(penjemputan.created_at).format('DD MMM YYYY'),
          nasabah: penjemputan.nasabah?.nama || 'Unknown',
          gudang: penjemputan.gudang?.alamat || penjemputan.gudang?.nama_gudang || 'Unknown Gudang',
          jenis: d.sampah?.item_sampah?.nama || 'Unknown',
          berat: parseFloat(penjemputan.estimasi_berat) || 0,
          petugas: penjemputan.tukang?.nama || 'Unknown',
          status: penjemputan.status === 'selesai' ? 'Verified' : 'Pending',
          rawDate: dayjs(penjemputan.created_at)
        });
      });
    } else {
      flat.push({
        tanggal: dayjs(penjemputan.created_at).format('DD MMM YYYY'),
        nasabah: penjemputan.nasabah?.nama || 'Unknown',
        gudang: penjemputan.gudang?.alamat || penjemputan.gudang?.nama_gudang || 'Unknown Gudang',
        jenis: 'Unknown',
        berat: parseFloat(penjemputan.estimasi_berat) || 0,
        petugas: penjemputan.tukang?.nama || 'Unknown',
        status: penjemputan.status === 'selesai' ? 'Verified' : 'Pending',
        rawDate: dayjs(penjemputan.created_at)
      });
    }
  });
  return flat;
});

const filteredFlatDataAll = computed(() => {
  return flatTableData.value.filter(row => {
    if (appliedFilter.value.gudang !== 'Semua Gudang' && row.gudang !== appliedFilter.value.gudang) return false;
    if (appliedFilter.value.jenisSampah.length > 0 && !appliedFilter.value.jenisSampah.includes(row.jenis)) return false;

    const rowDateStr = row.rawDate.format('YYYY-MM-DD');
    if (data.value.start_date && rowDateStr < data.value.start_date) return false;
    if (data.value.end_date && rowDateStr > data.value.end_date) return false;

    return true;
  }).sort((a, b) => b.rawDate.valueOf() - a.rawDate.valueOf());
});

const filteredGroupedDataAll = computed(() => {
  const groups = {};
  filteredFlatDataAll.value.forEach(row => {
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

const totalPages = computed(() => Math.ceil((isGroupedByGudang.value ? filteredGroupedDataAll.value.length : filteredFlatDataAll.value.length) / itemsPerPage.value) || 1);

const filteredFlatData = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value;
  return filteredFlatDataAll.value.slice(start, start + itemsPerPage.value);
});

const filteredGroupedData = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value;
  return filteredGroupedDataAll.value.slice(start, start + itemsPerPage.value);
});

const prevPage = () => { if (currentPage.value > 1) currentPage.value--; };
const nextPage = () => { if (currentPage.value < totalPages.value) currentPage.value++; };

const laporanStats = computed(() => {
  let totalTransaksi = 0;
  let totalBerat = 0;
  let verifiedCount = 0;
  let pendingCount = 0;

  const perGudangMap = {};
  const jenisSampahMap = {
    'Organik': { berat: 0 },
    'Plastik PET': { berat: 0 },
    'Kertas': { berat: 0 },
    'Logam': { berat: 0 }
  };

  filteredFlatDataAll.value.forEach(row => {
    totalTransaksi++;
    totalBerat += row.berat;
    if (row.status === 'Verified') verifiedCount++; else pendingCount++;

    if (!perGudangMap[row.gudang]) {
      perGudangMap[row.gudang] = { gudang: row.gudang, transaksi: 0, berat: 0, verified: 0, pending: 0 };
    }
    perGudangMap[row.gudang].transaksi++;
    perGudangMap[row.gudang].berat += row.berat;
    if (row.status === 'Verified') perGudangMap[row.gudang].verified++; else perGudangMap[row.gudang].pending++;

    if (jenisSampahMap[row.jenis]) jenisSampahMap[row.jenis].berat += row.berat;
    else jenisSampahMap[row.jenis] = { berat: row.berat };
  });

  const perGudangList = Object.values(perGudangMap).sort((a,b) => b.berat - a.berat);

  const jenisSampahList = Object.keys(jenisSampahMap).map(key => {
    const berat = jenisSampahMap[key].berat;
    const percentage = totalBerat > 0 ? (berat / totalBerat) * 100 : 0;
    return { name: key, berat, percentage };
  }).sort((a,b) => b.berat - a.berat).filter(j => j.berat > 0 || ['Organik','Plastik PET','Kertas','Logam'].includes(j.name));

  return { totalTransaksi, totalBerat, verifiedCount, pendingCount, perGudangList, jenisSampahList };
});

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
    
    // Kita kembali gunakan Axios (yang akan terkena CORS)
    // karena window.open tidak bisa mengirimkan Bearer Token.
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
  <DashboardLayout title="Audit Data">
    <div class="space-y-6 animate-in fade-in duration-700 pb-10 font-['Inter']">

      <!-- Header Description -->
      <div>
        <p class="text-sm font-medium text-stone-500">Riwayat sampah dan transaksi untuk audit</p>
      </div>

      <!-- Tabs -->
      <div class="flex gap-2 mb-4">
        <button
          @click="activeTab = 'Riwayat Sampah'"
          :class="[
            'px-6 py-2.5 rounded-lg text-sm font-bold transition-all shadow-sm border border-transparent cursor-pointer',
            activeTab === 'Riwayat Sampah' ? 'bg-[#4A7043] text-white shadow-md' : 'bg-white text-stone-600 hover:bg-stone-50 border-stone-200'
          ]"
        >
          Riwayat Sampah
        </button>
        <button
          @click="activeTab = 'Riwayat Penarikan'"
          :class="[
            'px-6 py-2.5 rounded-lg text-sm font-bold transition-all shadow-sm border border-transparent cursor-pointer',
            activeTab === 'Riwayat Penarikan' ? 'bg-[#4A7043] text-white shadow-md' : 'bg-white text-stone-600 hover:bg-stone-50 border-stone-200'
          ]"
        >
          Riwayat Penarikan
        </button>
      </div>

      <!-- TAB: RIWAYAT SAMPAH -->
      <div v-if="activeTab === 'Riwayat Sampah'" class="space-y-6 relative">
        <!-- Loading Overlay -->
        <div v-if="isLoading" class="absolute inset-0 z-50 bg-white/70 backdrop-blur-sm flex flex-col items-center justify-center rounded-2xl">
          <Icon icon="line-md:loading-twotone-loop" class="w-10 h-10 text-[#4A7043] mb-2" />
          <p class="text-sm font-bold text-stone-600">Memuat Data...</p>
          <p class="text-xs font-medium text-stone-500 mt-1">{{ loadingProgress }}%</p>
        </div>
        <!-- Toolbar -->
        <div class="bg-white p-4 rounded-2xl shadow-sm border border-stone-100 flex flex-row gap-2 justify-between items-center overflow-x-auto">
          <!-- Left Controls -->
          <div class="flex flex-row gap-2 w-auto shrink-0">
            <!-- Search -->
            <div class="relative w-48 sm:w-56 flex items-center shrink-0">
              <Icon icon="material-symbols:search" class="absolute left-3 top-1/2 -translate-y-1/2 text-stone-400 w-4 h-4" />
              <input
                type="text"
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
            <button @click="isLaporanModalOpen = true" class="px-3 py-1.5 bg-[#4A7043] hover:bg-[#3D5A35] text-white rounded-lg text-xs font-bold transition-colors flex items-center gap-1.5 shadow-sm shrink-0 cursor-pointer">
              <Icon icon="material-symbols:description-outline" class="w-3.5 h-3.5" />
              Lihat Laporan
            </button>
            <button @click="handleExportExcel" :disabled="isExportingExcel" class="px-3 py-1.5 bg-[#4A7043] hover:bg-[#3D5A35] text-white rounded-lg text-xs font-bold transition-colors flex items-center gap-1.5 shadow-sm shrink-0 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
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
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse min-w-[900px]">
            <thead>
              <tr class="bg-[#F5F5F0] border-b border-stone-200">
                <th class="py-4 px-6 text-xs font-bold text-stone-600 uppercase tracking-wider">Tanggal</th>
                <th class="py-4 px-6 text-xs font-bold text-stone-600 uppercase tracking-wider">Nasabah</th>
                <th class="py-4 px-6 text-xs font-bold text-stone-600 uppercase tracking-wider">Gudang</th>
                <th class="py-4 px-6 text-xs font-bold text-stone-600 uppercase tracking-wider">Jenis Sampah</th>
                <th class="py-4 px-6 text-xs font-bold text-stone-600 uppercase tracking-wider">Berat</th>
                <th class="py-4 px-6 text-xs font-bold text-stone-600 uppercase tracking-wider">Petugas</th>
                <th class="py-4 px-6 text-xs font-bold text-stone-600 uppercase tracking-wider text-center">Status</th>
                <th class="py-4 px-6 text-xs font-bold text-stone-600 uppercase tracking-wider text-center">Aksi</th>
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
                  <td colspan="8" class="py-3 px-6">
                    <div class="flex justify-between items-center w-full">
                      <span class="text-sm font-black text-[#3D5A35]">{{ group.gudangName }}</span>
                      <span class="text-xs font-bold text-[#3D5A35]">{{ group.summary }}</span>
                    </div>
                  </td>
                </tr>
                <!-- Group Rows -->
                <tr v-for="(row, rowIdx) in group.rows" :key="`${idx}-${rowIdx}`" class="hover:bg-stone-50 transition-colors group">
                  <td class="py-4 px-6 text-sm text-stone-600 font-medium whitespace-nowrap">{{ row.tanggal }}</td>
                  <td class="py-4 px-6 text-sm text-stone-800 font-medium whitespace-nowrap">{{ row.nasabah }}</td>
                  <td class="py-4 px-6">
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-green-50 text-[#4A7043] text-xs font-bold border border-green-100 whitespace-nowrap">
                      <div class="w-1.5 h-1.5 rounded-full bg-[#4A7043]"></div>
                      {{ row.gudang }}
                    </div>
                  </td>
                  <td class="py-4 px-6 text-sm text-stone-600 font-medium whitespace-nowrap">{{ row.jenis }}</td>
                  <td class="py-4 px-6 text-sm text-stone-800 font-bold whitespace-nowrap">{{ row.berat }}</td>
                  <td class="py-4 px-6 text-sm text-stone-600 font-medium whitespace-nowrap">{{ row.petugas }}</td>
                  <td class="py-4 px-6 whitespace-nowrap text-center">
                    <div v-if="row.status === 'Verified'" class="inline-flex items-center justify-center px-4 py-1.5 rounded-full bg-[#3D5A35] text-white text-[10px] font-bold tracking-wider shadow-sm min-w-[70px]">
                      Verified
                    </div>
                    <div v-else-if="row.status === 'Pending'" class="inline-flex items-center justify-center px-4 py-1.5 rounded-full bg-[#F59E0B] text-white text-[10px] font-bold tracking-wider shadow-sm min-w-[70px]">
                      Pending
                    </div>
                  </td>
                  <td class="py-4 px-6 text-sm font-bold text-[#4A7043] cursor-pointer hover:underline whitespace-nowrap text-center">Detail</td>
                </tr>
              </template>
            </tbody>

            <!-- FLAT VIEW (When Group By is Off) -->
            <tbody v-else class="divide-y divide-stone-100">
              <template v-if="filteredFlatData.length === 0">
                <tr>
                  <td colspan="8" class="py-8 text-center text-sm font-bold text-stone-400">Tidak ada data yang sesuai filter</td>
                </tr>
              </template>
              <template v-else>
                <tr v-for="(row, index) in filteredFlatData" :key="index" class="hover:bg-stone-50 transition-colors group">
                  <td class="py-4 px-6 text-sm text-stone-600 font-medium whitespace-nowrap">{{ row.tanggal }}</td>
                  <td class="py-4 px-6 text-sm text-stone-800 font-medium whitespace-nowrap">{{ row.nasabah }}</td>
                  <td class="py-4 px-6">
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-green-50 text-[#4A7043] text-xs font-bold border border-green-100 whitespace-nowrap">
                      <div class="w-1.5 h-1.5 rounded-full bg-[#4A7043]"></div>
                      {{ row.gudang }}
                    </div>
                  </td>
                  <td class="py-4 px-6 text-sm text-stone-600 font-medium whitespace-nowrap">{{ row.jenis }}</td>
                  <td class="py-4 px-6 text-sm text-stone-800 font-bold whitespace-nowrap">{{ row.berat }}</td>
                  <td class="py-4 px-6 text-sm text-stone-600 font-medium whitespace-nowrap">{{ row.petugas }}</td>
                  <td class="py-4 px-6 whitespace-nowrap text-center">
                    <div v-if="row.status === 'Verified'" class="inline-flex items-center justify-center px-4 py-1.5 rounded-full bg-[#3D5A35] text-white text-[10px] font-bold tracking-wider shadow-sm min-w-[70px]">
                      Verified
                    </div>
                    <div v-else-if="row.status === 'Pending'" class="inline-flex items-center justify-center px-4 py-1.5 rounded-full bg-[#F59E0B] text-white text-[10px] font-bold tracking-wider shadow-sm min-w-[70px]">
                      Pending
                    </div>
                  </td>
                  <td class="py-4 px-6 text-sm font-bold text-[#4A7043] cursor-pointer hover:underline whitespace-nowrap text-center">Detail</td>
                </tr>
              </template>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Pagination -->
      <div class="bg-white p-4 rounded-2xl shadow-sm border border-stone-100 flex flex-col sm:flex-row justify-between items-center gap-4">
        <div class="flex items-center gap-3">
          <span class="text-sm font-medium text-stone-500">Tampilkan</span>
          <div class="bg-stone-50 border border-stone-200 rounded-lg px-3 py-1.5 text-sm font-bold text-stone-600 flex items-center justify-between w-16 shadow-sm">
            10
          </div>
        </div>

        <div class="flex items-center gap-2">
          <button @click="prevPage" :disabled="currentPage === 1" class="px-4 py-2 rounded-lg text-sm font-bold text-stone-500 hover:bg-stone-100 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
            Sebelumnya
          </button>
          <button class="w-8 h-8 flex items-center justify-center rounded-lg bg-[#4A7043] text-white text-sm font-bold shadow-sm">
            {{ currentPage }}
          </button>
          <button @click="nextPage" :disabled="currentPage === totalPages" class="px-4 py-2 rounded-lg text-sm font-bold text-stone-500 hover:bg-stone-100 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
            Selanjutnya
          </button>
        </div>
      </div>
      </div> <!-- Close v-if activeTab === 'Riwayat Sampah' -->

      <!-- TAB: RIWAYAT PENARIKAN -->
      <div v-else class="space-y-6 relative animate-in fade-in duration-500">
        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div class="bg-white p-5 rounded-2xl shadow-sm border border-stone-100 flex flex-col justify-center relative overflow-hidden">
            <p class="text-[11px] font-bold text-stone-500 mb-1 z-10">Total Penarikan</p>
            <p class="text-2xl font-black text-[#4A7043] z-10">{{ formatRupiah(totalNominalPenarikan) }}</p>
          </div>
          <div class="bg-white p-5 rounded-2xl shadow-sm border border-stone-100 flex flex-col justify-center">
            <p class="text-[11px] font-bold text-stone-500 mb-1">Selesai</p>
            <p class="text-2xl font-black text-[#4A7043]">{{ countPenarikanSelesai }}</p>
          </div>
          <div class="bg-white p-5 rounded-2xl shadow-sm border border-stone-100 flex flex-col justify-center relative group cursor-pointer">
            <p class="text-[11px] font-bold text-stone-500 mb-1">Diproses</p>
            <p class="text-2xl font-black text-[#F59E0B]">{{ countPenarikanDiproses }}</p>
            <Icon icon="bi:cursor-fill" class="absolute bottom-4 right-4 text-black w-4 h-4 opacity-70 group-hover:opacity-100 transition-opacity" />
          </div>
          <div class="bg-white p-5 rounded-2xl shadow-sm border border-stone-100 flex flex-col justify-center">
            <p class="text-[11px] font-bold text-stone-500 mb-1">Ditolak</p>
            <p class="text-2xl font-black text-[#EF4444]">{{ countPenarikanDitolak }}</p>
          </div>
        </div>

        <!-- Toolbar -->
        <div class="bg-white p-4 rounded-2xl shadow-sm border border-stone-100 flex flex-col md:flex-row gap-4 justify-between items-center">
          <div class="flex flex-row gap-2 w-full md:w-auto shrink-0 items-center">
            <!-- Search -->
            <div class="relative w-full md:w-64 flex items-center">
              <Icon icon="material-symbols:search" class="absolute left-3 top-1/2 -translate-y-1/2 text-stone-400 w-4 h-4" />
              <input
                type="text"
                v-model="searchPenarikan"
                placeholder="Cari ID, nasabah, metode..."
                class="w-full pl-9 pr-3 py-2 bg-white border border-stone-200 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-[#4A7043]/20 focus:border-[#4A7043] transition-colors text-stone-700"
              />
            </div>
            <!-- Filter Button -->
            <button @click="openFilterPenarikanModal" class="relative px-4 py-2 bg-stone-50 border border-stone-200 rounded-lg text-xs font-bold text-stone-600 hover:bg-stone-100 transition-colors flex items-center gap-1.5 shadow-sm shrink-0 cursor-pointer">
              <Icon icon="material-symbols:filter-alt-outline" class="w-4 h-4" />
              Filter
              <span v-if="hasActivePenarikanFilters" class="absolute -top-1 -right-1 flex h-3 w-3">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#4A7043] opacity-75"></span>
                <span class="relative inline-flex rounded-full h-3 w-3 bg-[#4A7043] border border-white"></span>
              </span>
            </button>
          </div>
          <div class="flex flex-row gap-2 w-full md:w-auto shrink-0">
            <button class="px-4 py-2 bg-[#4A7043] hover:bg-[#3D5A35] text-white rounded-lg text-xs font-bold transition-colors flex items-center gap-1.5 shadow-sm shrink-0 cursor-pointer">
              Lihat Laporan
            </button>
            <button class="px-4 py-2 bg-[#4A7043] hover:bg-[#3D5A35] text-white rounded-lg text-xs font-bold transition-colors flex items-center gap-1.5 shadow-sm shrink-0 cursor-pointer">
              Export CSV
            </button>
          </div>
        </div>

        <!-- Table Container -->
        <div class="bg-white rounded-2xl shadow-sm border border-stone-100 overflow-hidden">
          <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[800px]">
              <thead>
                <tr class="bg-[#F5F5F0] border-b border-stone-200">
                  <th class="py-4 px-6 text-xs font-bold text-stone-600 tracking-wider w-[15%]">ID Penarikan</th>
                  <th class="py-4 px-6 text-xs font-bold text-stone-600 tracking-wider w-[20%]">Tanggal</th>
                  <th class="py-4 px-6 text-xs font-bold text-stone-600 tracking-wider w-[25%]">Nasabah</th>
                  <th class="py-4 px-6 text-xs font-bold text-stone-600 tracking-wider w-[25%]">Nominal</th>
                  <th class="py-4 px-6 text-xs font-bold text-stone-600 tracking-wider w-[15%]">Status</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-stone-100">
                <template v-if="paginatedPenarikanData.length === 0">
                  <tr>
                    <td colspan="5" class="py-8 text-center text-sm font-bold text-stone-400">Tidak ada data penarikan</td>
                  </tr>
                </template>
                <template v-else>
                  <tr v-for="row in paginatedPenarikanData" :key="row.penarikan_id" @click="openDetailPenarikan(row)" class="hover:bg-stone-50 transition-colors cursor-pointer group">
                    <td class="py-4 px-6 text-xs font-bold text-[#4A7043]">{{ row.penarikan_id }}</td>
                    <td class="py-4 px-6 text-sm text-stone-600 font-medium whitespace-nowrap">{{ dayjs(row.created_at).format('DD MMM YYYY') }}</td>
                    <td class="py-4 px-6 text-sm text-stone-600 font-medium">{{ row.nasabah?.nama || '-' }}</td>
                    <td class="py-4 px-6 text-sm text-stone-800 font-bold whitespace-nowrap">{{ formatRupiah(row.jumlah) }}</td>
                    <td class="py-4 px-6 whitespace-nowrap">
                      <div :class="['inline-flex items-center justify-center px-4 py-1.5 rounded-full text-[10px] font-bold tracking-wider shadow-sm min-w-[70px]', getStatusColorPenarikan(row.status)]">
                        {{ formatStatusPenarikan(row.status) }}
                      </div>
                    </td>
                  </tr>
                </template>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Pagination -->
        <div class="bg-white p-4 rounded-2xl shadow-sm border border-stone-100 flex flex-col sm:flex-row justify-between items-center gap-4">
          <div class="flex items-center gap-3">
            <span class="text-sm font-medium text-stone-500">Tampilkan</span>
            <div class="bg-stone-50 border border-stone-200 rounded-lg px-3 py-1.5 text-sm font-bold text-stone-600 flex items-center justify-between w-16 shadow-sm">
              10
            </div>
          </div>
          <div class="flex items-center gap-2">
            <button @click="prevPenarikanPage" :disabled="currentPenarikanPage === 1" class="px-4 py-2 rounded-lg text-sm font-bold text-stone-500 hover:bg-stone-100 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
              Sebelumnya
            </button>
            <div class="px-4 py-2 rounded-lg bg-[#4A7043] text-white text-sm font-bold shadow-sm flex items-center gap-1">
              <span>{{ currentPenarikanPage }}</span>
              <span>/</span>
              <span>{{ totalPenarikanPages }}</span>
            </div>
            <button @click="nextPenarikanPage" :disabled="currentPenarikanPage === totalPenarikanPages" class="px-4 py-2 rounded-lg text-sm font-bold text-stone-500 hover:bg-stone-100 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
              Selanjutnya
            </button>
          </div>
        </div>
      </div>

    </div>

    <!-- Filter Modal -->
    <div v-if="isFilterModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
      <!-- Backdrop -->
      <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="isFilterModalOpen = false"></div>

      <!-- Modal Panel -->
      <div class="relative bg-white rounded-2xl w-full max-w-md shadow-2xl animate-in zoom-in-95 duration-200">
        <!-- Header -->
        <div class="flex items-center justify-between p-6 border-b border-stone-100">
          <h3 class="text-lg font-bold text-stone-800">Filter Data</h3>
          <button @click="isFilterModalOpen = false" class="text-stone-400 hover:text-stone-600 transition-colors cursor-pointer">
            <Icon icon="material-symbols:close" class="w-6 h-6" />
          </button>
        </div>

        <!-- Body -->
        <div class="p-6 space-y-6">
          <!-- Gudang -->
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

          <!-- Durasi Waktu -->
          <div class="grid grid-cols-2 gap-4">
            <div class="space-y-2">
              <label class="block text-xs font-bold text-stone-600">Dari Tanggal</label>
              <input 
                type="date" 
                v-model="filterForm.start_date" 
                class="w-full px-3 py-2 bg-white border border-stone-200 rounded-lg text-xs text-stone-700 focus:outline-none focus:ring-2 focus:ring-[#4A7043]/20 focus:border-[#4A7043] transition-colors cursor-pointer"
              />
            </div>
            <div class="space-y-2">
              <label class="block text-xs font-bold text-stone-600">Sampai Tanggal</label>
              <input 
                type="date" 
                v-model="filterForm.end_date" 
                class="w-full px-3 py-2 bg-white border border-stone-200 rounded-lg text-xs text-stone-700 focus:outline-none focus:ring-2 focus:ring-[#4A7043]/20 focus:border-[#4A7043] transition-colors cursor-pointer"
              />
            </div>
          </div>

          <!-- Jenis Sampah -->
          <div class="space-y-3">
            <label class="block text-sm font-bold text-stone-600">Jenis Sampah</label>
            <div class="flex flex-wrap gap-2 max-h-40 overflow-y-auto pr-2 custom-scrollbar">
              <button
                v-for="jenis in jenisSampahOptions"
                :key="jenis"
                @click="toggleJenisSampah(jenis)"
                :class="[
                  'px-4 py-1.5 rounded-full text-xs font-bold transition-all border cursor-pointer',
                  filterForm.jenisSampah.includes(jenis)
                    ? 'bg-[#4A7043] text-white border-[#4A7043] shadow-sm'
                    : 'bg-white text-stone-600 border-stone-200 hover:border-[#4A7043] hover:text-[#4A7043]'
                ]"
              >
                {{ jenis }}
              </button>
            </div>
            <p class="text-[10px] text-stone-400 mt-2">Kosongkan untuk menampilkan semua jenis</p>
          </div>
        </div>

        <!-- Footer -->
        <div class="p-6 border-t border-stone-100 flex gap-3">
          <button @click="resetFilterForm" class="flex-1 px-4 py-2.5 bg-white border border-stone-200 text-stone-600 rounded-lg text-sm font-bold hover:bg-stone-50 transition-colors cursor-pointer">
            Reset Filter
          </button>
          <button @click="terapkanFilter" class="flex-1 px-4 py-2.5 bg-[#4A7043] text-white rounded-lg text-sm font-bold hover:bg-[#3D5A35] transition-colors shadow-sm cursor-pointer">
            Terapkan Filter
          </button>
        </div>
      </div>
    </div>

    <!-- Laporan Modal -->
    <div v-if="isLaporanModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
      <!-- Backdrop -->
      <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="isLaporanModalOpen = false"></div>

      <!-- Modal Panel -->
      <div id="printable-laporan" class="relative bg-stone-50 rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] flex flex-col overflow-hidden animate-in zoom-in-95 duration-200">
        <!-- Header -->
        <div class="p-6 bg-white border-b border-stone-100 flex justify-between items-center shrink-0 z-10">
          <div>
            <h3 class="text-xl font-black text-stone-800">Preview Laporan Audit</h3>
            <p class="text-xs text-stone-500 mt-1">Periode: {{ formatDateRangeText }}</p>
          </div>
          <div id="laporan-actions" class="flex items-center gap-3">
            <button @click="handlePrintPdf" :disabled="isExportingPdf" class="px-4 py-2 bg-[#4A7043] hover:bg-[#3D5A35] text-white rounded-lg text-sm font-bold transition-colors flex items-center gap-2 shadow-sm cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
              <Icon v-if="isExportingPdf" icon="line-md:loading-twotone-loop" class="w-4 h-4" />
              <Icon v-else icon="material-symbols:print-outline" class="w-4 h-4" />
              {{ isExportingPdf ? 'Mencetak...' : 'Print' }}
            </button>
            <button @click="isLaporanModalOpen = false" class="text-stone-400 hover:text-stone-600 transition-colors cursor-pointer p-1">
              <Icon icon="material-symbols:close" class="w-6 h-6" />
            </button>
          </div>
        </div>

        <!-- Scrollable Body -->
        <div id="laporan-scroll-area" class="p-6 overflow-y-auto custom-scrollbar flex-1 space-y-6 relative z-0">

          <!-- Top 4 Cards -->
          <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white p-4 rounded-xl border border-stone-100 shadow-sm">
              <p class="text-xs font-bold text-stone-500 mb-1">Total Transaksi</p>
              <p class="text-2xl font-black text-[#4A7043]">{{ laporanStats.totalTransaksi }}</p>
            </div>
            <div class="bg-white p-4 rounded-xl border border-stone-100 shadow-sm">
              <p class="text-xs font-bold text-stone-500 mb-1">Total Berat</p>
              <p class="text-2xl font-black text-[#4A7043]">{{ laporanStats.totalBerat.toFixed(1) }} <span class="text-sm font-bold text-stone-500">kg</span></p>
            </div>
            <div class="bg-white p-4 rounded-xl border border-stone-100 shadow-sm">
              <p class="text-xs font-bold text-stone-500 mb-1">Tervalidasi</p>
              <p class="text-2xl font-black text-[#4A7043]">{{ laporanStats.verifiedCount }}</p>
            </div>
            <div class="bg-white p-4 rounded-xl border border-stone-100 shadow-sm">
              <p class="text-xs font-bold text-stone-500 mb-1">Pending</p>
              <p class="text-2xl font-black text-[#F59E0B]">{{ laporanStats.pendingCount }}</p>
            </div>
          </div>

          <!-- Data Per Gudang Table -->
          <div class="bg-white rounded-xl border border-stone-100 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-stone-100">
              <h4 class="text-sm font-bold text-stone-800">Data Per Gudang</h4>
            </div>
            <div class="overflow-x-auto">
              <table class="w-full text-left text-sm">
                <thead>
                  <tr class="text-stone-500 bg-white">
                    <th class="py-3 px-5 font-bold text-xs">Gudang</th>
                    <th class="py-3 px-5 font-bold text-xs">Transaksi</th>
                    <th class="py-3 px-5 font-bold text-xs">Total Berat</th>
                    <th class="py-3 px-5 font-bold text-xs text-center">Verified</th>
                    <th class="py-3 px-5 font-bold text-xs text-center">Pending</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                  <tr v-for="gudang in laporanStats.perGudangList" :key="gudang.gudang" class="hover:bg-stone-50 transition-colors">
                    <td class="py-3 px-5 font-medium text-stone-600 text-xs">{{ gudang.gudang }}</td>
                    <td class="py-3 px-5 text-stone-600 text-xs">{{ gudang.transaksi }}</td>
                    <td class="py-3 px-5 text-stone-600 text-xs">{{ gudang.berat.toFixed(1) }} kg</td>
                    <td class="py-3 px-5 text-center"><span class="inline-flex items-center justify-center w-8 h-5 rounded-full bg-[#3D5A35] text-white text-[10px] font-bold shadow-sm">{{ gudang.verified }}</span></td>
                    <td class="py-3 px-5 text-center"><span class="inline-flex items-center justify-center w-8 h-5 rounded-full bg-[#F59E0B] text-white text-[10px] font-bold shadow-sm">{{ gudang.pending }}</span></td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Distribusi Jenis Sampah -->
          <div class="bg-white rounded-xl border border-stone-100 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-stone-100">
              <h4 class="text-sm font-bold text-stone-800">Distribusi Jenis Sampah</h4>
            </div>
            <div class="p-5 space-y-5">
              <!-- Iterate through dynamic list -->
              <div v-for="jenis in laporanStats.jenisSampahList" :key="jenis.name" class="flex items-center gap-4">
                <div class="w-24 shrink-0 text-xs font-medium text-stone-600 truncate">{{ jenis.name }}</div>
                <div class="flex-1 h-6 bg-[#F5F9F5] rounded-full overflow-hidden relative">
                  <div class="absolute top-0 left-0 h-full bg-[#4A7043] rounded-full transition-all duration-500" :style="{ width: jenis.percentage + '%' }"></div>
                  <div class="absolute inset-0 flex items-center px-3 text-[10px] font-bold text-white justify-start" :style="{ paddingLeft: 'max(0.5rem, calc(' + jenis.percentage + '% - 3.5rem))' }">
                    <span v-if="jenis.percentage > 15">{{ jenis.berat.toFixed(1) }} kg</span>
                  </div>
                </div>
                <div class="w-12 shrink-0 text-right text-xs font-bold text-stone-500">{{ jenis.percentage.toFixed(1) }}%</div>
              </div>
            </div>
          </div>

          <!-- Alert Note -->
          <div v-if="laporanStats.pendingCount > 0" class="bg-[#FFF8E6] border border-[#FDE68A] rounded-xl p-4 flex gap-3 items-start shadow-sm">
            <Icon icon="material-symbols:info-outline" class="w-5 h-5 text-[#F59E0B] shrink-0 mt-0.5" />
            <div>
              <h5 class="text-sm font-bold text-[#D97706] mb-1">Catatan</h5>
              <p class="text-xs text-[#B45309] leading-relaxed">Terdapat {{ laporanStats.pendingCount }} transaksi yang masih menunggu verifikasi. Harap segera ditindaklanjuti untuk memastikan akurasi data audit.</p>
            </div>
          </div>

        </div>

        <!-- Footer -->
        <div class="p-4 border-t border-stone-100 flex justify-between items-center shrink-0 bg-white z-10">
          <span class="text-[10px] font-bold text-stone-400">Digenerate pada: {{ generateTimeText }}</span>
          <span class="text-[10px] font-bold text-stone-400">Bank Sampah Management System</span>
        </div>
      </div>
    </div>



    <!-- Modal Filter Penarikan -->
    <div v-if="isFilterPenarikanModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="isFilterPenarikanModalOpen = false"></div>
      <div class="relative bg-white rounded-2xl w-full max-w-md shadow-2xl animate-in zoom-in-95 duration-200">
        <div class="flex items-center justify-between p-6 border-b border-stone-100">
          <h3 class="text-lg font-bold text-stone-800">Filter Data</h3>
          <button @click="isFilterPenarikanModalOpen = false" class="text-stone-400 hover:text-stone-600 transition-colors cursor-pointer">
            <Icon icon="material-symbols:close" class="w-6 h-6" />
          </button>
        </div>
        <div class="p-6 space-y-6">
          <div class="space-y-2">
            <label class="block text-sm font-bold text-stone-600">Gudang</label>
            <input type="text" v-model="filterPenarikanForm.gudang" class="w-full px-4 py-2.5 bg-white border border-stone-200 rounded-lg text-sm focus:outline-none focus:border-[#4A7043]" placeholder="Semua Gudang" />
          </div>
          <div class="space-y-2">
            <label class="block text-sm font-bold text-stone-600">Durasi Waktu</label>
            <div class="flex gap-2">
              <input type="date" v-model="filterPenarikanForm.start_date" class="w-full px-3 py-2 bg-white border border-stone-200 rounded-lg text-xs" />
              <input type="date" v-model="filterPenarikanForm.end_date" class="w-full px-3 py-2 bg-white border border-stone-200 rounded-lg text-xs" />
            </div>
          </div>
          <div class="space-y-3">
            <label class="block text-sm font-bold text-stone-600">Status Penarikan</label>
            <div class="flex flex-wrap gap-2">
              <button @click="toggleStatusPenarikan('selesai')" :class="['px-4 py-1.5 rounded-full text-xs font-bold transition-all border cursor-pointer', filterPenarikanForm.status.includes('selesai') ? 'bg-white text-stone-800 border-stone-300 shadow-sm' : 'bg-white text-stone-400 border-stone-200']">Selesai</button>
              <button @click="toggleStatusPenarikan('pending')" :class="['px-4 py-1.5 rounded-full text-xs font-bold transition-all border cursor-pointer', filterPenarikanForm.status.includes('pending') ? 'bg-white text-stone-800 border-stone-300 shadow-sm' : 'bg-white text-stone-400 border-stone-200']">Diproses</button>
              <button @click="toggleStatusPenarikan('tolak')" :class="['px-4 py-1.5 rounded-full text-xs font-bold transition-all border cursor-pointer', filterPenarikanForm.status.includes('tolak') ? 'bg-white text-stone-800 border-stone-300 shadow-sm' : 'bg-white text-stone-400 border-stone-200']">Ditolak</button>
            </div>
          </div>
        </div>
        <div class="p-6 border-t border-stone-100 flex gap-3">
          <button @click="resetFilterPenarikanForm" class="flex-1 px-4 py-2.5 bg-white border border-stone-200 text-stone-600 rounded-lg text-sm font-bold hover:bg-stone-50 transition-colors cursor-pointer">Reset Filter</button>
          <button @click="terapkanFilterPenarikan" class="flex-1 px-4 py-2.5 bg-[#4A7043] text-white rounded-lg text-sm font-bold hover:bg-[#3D5A35] transition-colors shadow-sm cursor-pointer">Terapkan Filter</button>
        </div>
      </div>
    </div>

    <!-- Modal Detail Penarikan -->
    <div v-if="isDetailPenarikanModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="isDetailPenarikanModalOpen = false"></div>
      <div class="relative bg-white rounded-2xl w-full max-w-md shadow-2xl animate-in zoom-in-95 duration-200 overflow-hidden">
        <div class="bg-[#4A7043] p-6 text-white flex justify-between items-start">
          <div>
            <h3 class="text-lg font-black">Detail Penarikan</h3>
            <p class="text-sm text-green-100 opacity-90">{{ selectedPenarikan?.penarikan_id }}</p>
          </div>
          <button @click="isDetailPenarikanModalOpen = false" class="text-white hover:text-stone-200 transition-colors">
            <Icon icon="material-symbols:close" class="w-6 h-6" />
          </button>
        </div>
        <div class="p-6 space-y-6">
          <div class="bg-[#F5F9F5] p-4 rounded-xl flex justify-between items-center border border-[#4A7043]/10">
            <div>
              <p class="text-[10px] font-bold text-stone-500 mb-0.5">Nominal Penarikan</p>
              <p class="text-2xl font-black text-[#4A7043]">{{ formatRupiah(selectedPenarikan?.jumlah) }}</p>
            </div>
            <div :class="['px-3 py-1 rounded-full text-[10px] font-bold shadow-sm', getStatusColorPenarikan(selectedPenarikan?.status)]">
              {{ formatStatusPenarikan(selectedPenarikan?.status) }}
            </div>
          </div>
          
          <div class="grid grid-cols-2 gap-y-5 gap-x-4">
            <div>
              <p class="text-[10px] font-bold text-stone-400 mb-0.5">Tanggal</p>
              <p class="text-sm font-bold text-stone-700">{{ selectedPenarikan?.created_at ? dayjs(selectedPenarikan.created_at).format('DD MMM YYYY') : '-' }}</p>
            </div>
            <div>
              <p class="text-[10px] font-bold text-stone-400 mb-0.5">Nasabah</p>
              <p class="text-sm font-bold text-stone-700">{{ selectedPenarikan?.nasabah?.nama || '-' }}</p>
            </div>
            <div>
              <p class="text-[10px] font-bold text-stone-400 mb-0.5">Gudang</p>
              <p class="text-sm font-bold text-stone-700">{{ selectedPenarikan?.petugas?.gudang?.nama_gudang || selectedPenarikan?.petugas?.gudang?.alamat || '-' }}</p>
            </div>
            <div>
              <p class="text-[10px] font-bold text-stone-400 mb-0.5">Petugas</p>
              <p class="text-sm font-bold text-stone-700">{{ selectedPenarikan?.petugas?.nama || '-' }}</p>
            </div>
            <div>
              <p class="text-[10px] font-bold text-stone-400 mb-0.5">Metode</p>
              <p class="text-sm font-bold text-stone-700">{{ selectedPenarikan?.nama_bank || 'Transfer Bank' }}</p>
            </div>
            <div>
              <p class="text-[10px] font-bold text-stone-400 mb-0.5">Rekening / Akun</p>
              <p class="text-sm font-bold text-stone-700">{{ selectedPenarikan?.nama_bank || 'Bank' }} &bull;&bull;&bull;&bull; {{ selectedPenarikan?.no_rekening ? selectedPenarikan.no_rekening.slice(-4) : '0000' }}</p>
            </div>
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
