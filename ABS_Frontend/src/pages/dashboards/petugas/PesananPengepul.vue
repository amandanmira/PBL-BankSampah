<script setup>
import { ref, onMounted, computed, inject } from "vue";
import { Icon } from "@iconify/vue";
import DashboardLayout from "@/layouts/DashboardLayout.vue";
import { checkRole } from "@/utils";
import Swal from "sweetalert2";

// Security check
checkRole("petugas");

const axios = inject('axios');

// State
const orders = ref([]);
const counts = ref({
  perlu_validasi: 0,
  siap_diambil: 0,
  selesai: 0,
  ditolak: 0
});
const pagination = ref({});
const loading = ref(false);
const searchQuery = ref("");
const activeFilter = ref("perlu_validasi"); // perlu_validasi, siap_diambil, selesai, ditolak
const isSubmitting = ref(false);

const user = ref(JSON.parse(sessionStorage.getItem('user') || '{}'));
const currentGudangId = computed(() => user.value.gudang_id);
const currentGudangNama = computed(() => user.value.gudang?.nama || `Gudang ${user.value.gudang_id}`);

// Modal States
const isDetailModalOpen = ref(false);
const isConfirmApproveOpen = ref(false);
const isConfirmRejectOpen = ref(false);
const isProofModalOpen = ref(false);
const isApprovePaymentModalOpen = ref(false);
const isRejectPaymentModalOpen = ref(false);
const isCompleteCollectionModalOpen = ref(false);
const selectedOrder = ref(null);
const rejectReason = ref("");

// Utils
const formatRupiah = (angka) => {
  return new Intl.NumberFormat("id-ID", {
    style: "currency",
    currency: "IDR",
    minimumFractionDigits: 0,
  }).format(angka || 0);
};

const formatDate = (dateStr) => {
  if (!dateStr) return "-";
  return dateStr.split("T")[0];
};

const formatDateTime = (dateStr) => {
  if (!dateStr) return "-";
  const date = new Date(dateStr);
  return date.toLocaleString('id-ID', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

const getStatusLabel = (order) => {
  if (order.status === 'pending') return 'Menunggu Konfirmasi';
  if (order.status === 'proses') {
    return order.bukti_transfer ? 'Menunggu Validasi Pembayaran' : 'Menunggu Pembayaran';
  }
  if (order.status === 'siap_diambil') return 'Siap Diambil';
  if (order.status === 'selesai') return 'Selesai';
  if (order.status === 'tolak') return 'Ditolak';
  if (order.status === 'batal') return 'Dibatalkan';
  return order.status;
};

const getStatusClass = (order) => {
  if (order.status === 'pending') return 'bg-amber-50 border-amber-200 text-amber-700';
  if (order.status === 'proses') {
    return order.bukti_transfer ? 'bg-sky-50 border-sky-200 text-sky-700' : 'bg-orange-50 border-orange-200 text-orange-700';
  }
  if (order.status === 'siap_diambil') return 'bg-green-50 border-green-200 text-green-700';
  if (order.status === 'selesai') return 'bg-stone-50 border-stone-200 text-stone-500';
  return 'bg-red-50 border-red-200 text-red-700';
};

// Fetch data
const fetchData = async (page = 1) => {
  loading.value = true;
  try {
    const res = await axios.get("/api/petugas/pesanan-pengepul", {
      params: {
        page,
        status: activeFilter.value,
        search: searchQuery.value,
      },
    });
    
    orders.value = res.data.orders.data;
    pagination.value = {
      current_page: res.data.orders.current_page,
      last_page: res.data.orders.last_page,
      total: res.data.orders.total,
      per_page: res.data.orders.per_page,
    };
    counts.value = res.data.counts;
  } catch (err) {
    console.error("Failed to fetch pesanan pengepul:", err);
    Swal.fire("Gagal", "Gagal memuat data pesanan.", "error");
  } finally {
    loading.value = false;
  }
};

// Filter detail items helper
const getTugasAndaItems = (detailTransaksi) => {
  if (!detailTransaksi) return [];
  return detailTransaksi.filter(d => d.sampah?.gudang_id === currentGudangId.value);
};

const getGudangLainItems = (detailTransaksi) => {
  if (!detailTransaksi) return [];
  return detailTransaksi.filter(d => d.sampah?.gudang_id !== currentGudangId.value);
};

// Check if other warehouses are still pending stock approval
const getPendingOtherWarehouses = (order) => {
  if (!order.detail_transaksi) return [];
  const pendingWarehouses = [];
  
  order.detail_transaksi.forEach(d => {
    const gid = d.sampah?.gudang_id;
    if (gid !== currentGudangId.value && d.status === 'pending') {
      const gName = d.sampah?.gudang?.nama || `Gudang ${gid}`;
      if (!pendingWarehouses.includes(gName)) {
        pendingWarehouses.push(gName);
      }
    }
  });
  
  return pendingWarehouses;
};

// Fungsi pengecekan ACC Stok Gudang saat ini
const isCurrentWarehouseApproved = (order) => {
  const tugasAnda = getTugasAndaItems(order.detail_transaksi);
  if (tugasAnda.length === 0) return false;
  return tugasAnda.every(item => item.status !== 'pending');
};

// Fungsi pengecekan ACC Pembayaran Gudang saat ini
const isPaymentVerifiedByCurrentWarehouse = (order) => {
  const tugasAnda = getTugasAndaItems(order.detail_transaksi);
  if (tugasAnda.length === 0) return false;
  return tugasAnda.every(item => item.status_pembayaran === 'terverifikasi');
};

// Fungsi pengecekan penyerahan barang gudang saat ini
const isCollectionCompletedByCurrentWarehouse = (order) => {
  const tugasAnda = getTugasAndaItems(order.detail_transaksi);
  if (tugasAnda.length === 0) return false;
  
  return tugasAnda.every(item => {
    const s = String(item.status || '').toLowerCase();
    const sp = String(item.status_pengambilan || '').toLowerCase();
    
    return ['selesai', 'diambil', 'diserahkan', 'success'].includes(s) || 
           ['selesai', 'diambil', 'diserahkan', 'terverifikasi'].includes(sp) ||
           s === 'approved' && order.status === 'selesai';
  });
};

// Actions
const setFilter = (filter) => {
  activeFilter.value = filter;
  fetchData(1);
};

const handleSearch = () => {
  fetchData(1);
};

const openApproveStock = (order) => {
  selectedOrder.value = order;
  isConfirmApproveOpen.value = true;
};

const openRejectOrder = (order) => {
  selectedOrder.value = order;
  isConfirmRejectOpen.value = true;
};

const openProofModal = (order) => {
  selectedOrder.value = order;
  isProofModalOpen.value = true;
};

const openApprovePaymentModal = (order) => {
  selectedOrder.value = order;
  isApprovePaymentModalOpen.value = true;
};

const openRejectPaymentModal = (order) => {
  selectedOrder.value = order;
  isRejectPaymentModalOpen.value = true;
};

const openCompleteCollectionModal = (order) => {
  selectedOrder.value = order;
  isCompleteCollectionModalOpen.value = true;
};

const openDetailModal = (order) => {
  selectedOrder.value = order;
  isDetailModalOpen.value = true;
};

const closeAllModals = () => {
  isDetailModalOpen.value = false;
  isConfirmApproveOpen.value = false;
  isConfirmRejectOpen.value = false;
  isProofModalOpen.value = false;
  isApprovePaymentModalOpen.value = false;
  isRejectPaymentModalOpen.value = false;
  isCompleteCollectionModalOpen.value = false;
  selectedOrder.value = null;
  rejectReason.value = "";
};

const confirmApproveStock = async () => {
  isSubmitting.value = true;
  try {
    await axios.put(`/api/petugas/pesanan-pengepul/${selectedOrder.value.transaksi_id}/approve-stok`);
    Swal.fire("Berhasil", "Stok pesanan berhasil disetujui.", "success");
    closeAllModals();
    fetchData(pagination.value.current_page);
  } catch (err) {
    console.error(err);
    Swal.fire("Gagal", err.response?.data?.message || "Terjadi kesalahan saat menyetujui stok.", "error");
  } finally {
    isSubmitting.value = false;
  }
};

const confirmRejectOrder = async () => {
  if (!rejectReason.value.trim()) {
    Swal.fire("Peringatan", "Alasan penolakan wajib diisi", "warning");
    return;
  }

  isSubmitting.value = true;
  try {
    await axios.put(`/api/petugas/pesanan-pengepul/${selectedOrder.value.transaksi_id}/tolak`, {
      alasan_penolakan: rejectReason.value
    });
    Swal.fire("Berhasil", "Pesanan berhasil ditolak.", "success");
    closeAllModals();
    fetchData(pagination.value.current_page);
  } catch (err) {
    console.error(err);
    Swal.fire("Gagal", err.response?.data?.message || "Terjadi kesalahan saat menolak pesanan.", "error");
  } finally {
    isSubmitting.value = false;
  }
};

const confirmApprovePayment = async () => {
  if (!selectedOrder.value) return;
  isSubmitting.value = true;
  try {
    await axios.put(`/api/petugas/pesanan-pengepul/${selectedOrder.value.transaksi_id}/verifikasi-pembayaran`);
    Swal.fire("Berhasil", "Pembayaran pesanan telah diverifikasi.", "success");
    closeAllModals();
    fetchData(pagination.value.current_page);
  } catch (err) {
    console.error(err);
    Swal.fire("Gagal", err.response?.data?.message || "Gagal memverifikasi pembayaran.", "error");
  } finally {
    isSubmitting.value = false;
  }
};

const confirmRejectPayment = async () => {
  if (!selectedOrder.value) return;
  if (!rejectReason.value.trim()) {
    Swal.fire("Peringatan", "Alasan penolakan bukti transfer wajib diisi", "warning");
    return;
  }
  isSubmitting.value = true;
  try {
    await axios.put(`/api/petugas/pesanan-pengepul/${selectedOrder.value.transaksi_id}/tolak`, {
      alasan_penolakan: rejectReason.value
    });
    Swal.fire("Berhasil", "Bukti transfer telah ditolak.", "success");
    closeAllModals();
    fetchData(pagination.value.current_page);
  } catch (err) {
    console.error(err);
    Swal.fire("Gagal", err.response?.data?.message || "Terjadi kesalahan saat menolak bukti.", "error");
  } finally {
    isSubmitting.value = false;
  }
};

const confirmCompleteCollection = async () => {
  if (!selectedOrder.value) return;
  isSubmitting.value = true;
  try {
    await axios.put(`/api/petugas/pesanan-pengepul/${selectedOrder.value.transaksi_id}/selesai`);
    Swal.fire("Berhasil", "Transaksi pesanan pengepul telah diselesaikan.", "success");
    closeAllModals();
    fetchData(pagination.value.current_page);
  } catch (err) {
    console.error(err);
    Swal.fire("Gagal", err.response?.data?.message || "Gagal menyelesaikan pesanan.", "error");
  } finally {
    isSubmitting.value = false;
  }
};

onMounted(() => {
  fetchData();
});
</script>

<template>
  <DashboardLayout title="Pesanan Pengepul">
    <div class="space-y-6 animate-in fade-in duration-700 pb-10">
      <div class="flex justify-between items-start">
        <div>
          <h2 class="text-2xl font-black text-stone-800">Pesanan Pengepul</h2>
          <p class="text-stone-500 font-medium">Validasi stok, pembayaran, dan pantau pengambilan sampah oleh Pengepul.</p>
        </div>
      </div>

      <div class="bg-white rounded-2xl p-2 shadow-sm border border-stone-100 flex overflow-x-auto no-scrollbar">
        <button 
          v-for="tab in [
            { id: 'perlu_validasi', label: 'Perlu Validasi', count: counts.perlu_validasi },
            { id: 'siap_diambil', label: 'Siap Diambil', count: counts.siap_diambil },
            { id: 'selesai', label: 'Selesai', count: counts.selesai },
            { id: 'ditolak', label: 'Ditolak/Batal', count: counts.ditolak }
          ]" 
          :key="tab.id"
          @click="setFilter(tab.id)"
          :class="[
            'flex-1 min-w-[150px] py-3.5 rounded-xl text-sm font-bold transition-all whitespace-nowrap flex items-center justify-center gap-2',
            activeFilter === tab.id ? 'bg-[#4A7043] text-white shadow-md' : 'text-stone-400 hover:bg-stone-50'
          ]"
        >
          <span>{{ tab.label }}</span>
          <span :class="[
            'px-2 py-0.5 rounded-full text-xs font-black',
            activeFilter === tab.id ? 'bg-white/20 text-white' : 'bg-stone-100 text-stone-500'
          ]">
            {{ tab.count }}
          </span>
        </button>
      </div>

      <div class="bg-white rounded-2xl p-4 shadow-sm border border-stone-100 relative">
        <div class="relative group">
          <Icon icon="material-symbols:search" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-stone-300 group-focus-within:text-[#4A7043] transition-colors" />
          <input 
            v-model="searchQuery"
            @keyup.enter="handleSearch"
            type="text" 
            placeholder="Cari berdasarkan tracking ID atau jenis sampah..." 
            class="w-full bg-stone-50 border border-stone-100 rounded-xl py-3.5 pl-12 pr-4 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-[#4A7043]/10 focus:border-[#4A7043] transition-all"
          />
        </div>
      </div>

      <div class="space-y-4">
        <div v-if="loading && orders.length === 0" class="space-y-4">
          <div v-for="i in 3" :key="i" class="bg-white rounded-3xl p-6 border border-stone-100 animate-pulse h-60"></div>
        </div>

        <div v-else-if="orders.length === 0" class="bg-white rounded-3xl p-20 shadow-sm border border-stone-100 text-center">
          <div class="flex flex-col items-center opacity-40">
            <Icon icon="material-symbols:inbox-outline" class="w-16 h-16 mb-4" />
            <h3 class="text-lg font-black text-stone-800">Tidak Ada Pesanan</h3>
            <p class="text-sm text-stone-500 mt-1">Tidak ada pesanan pengepul yang terdaftar pada filter ini.</p>
          </div>
        </div>

        <div 
          v-else 
          v-for="order in orders" 
          :key="order.transaksi_id" 
          class="bg-white rounded-3xl p-6 shadow-sm border border-stone-200/80 space-y-6 hover:shadow-md transition-all"
        >
          <div class="flex justify-between items-center pb-2">
            <div class="flex items-center gap-2">
              <span class="text-base font-bold text-stone-800">PSN-{{ String(order.transaksi_id).padStart(3, '0') }}</span>
              <span class="text-xs text-stone-400 font-semibold">{{ formatDate(order.created_at) }}</span>
            </div>
            <span :class="[
              'px-3 py-1 rounded-full text-xs font-semibold border',
              getStatusClass(order)
            ]">
              {{ getStatusLabel(order) }}
            </span>
          </div>

          <div class="space-y-4">
            <div class="bg-[#F5F8F5] border border-[#4A7043]/20 rounded-2xl p-6 space-y-4">
              <h4 class="text-sm font-bold text-[#4A7043]">
                Tempat Anda ({{ currentGudangNama }})
              </h4>
              <div class="space-y-3">
                <div v-if="getTugasAndaItems(order.detail_transaksi).length === 0" class="text-[#4A7043]/60 text-xs font-semibold py-1">
                  Tidak ada item dari {{ currentGudangNama }}
                </div>
                <div 
                  v-else
                  v-for="item in getTugasAndaItems(order.detail_transaksi)" 
                  :key="item.detail_id" 
                  class="flex justify-between items-center"
                >
                  <div class="flex-1 text-sm font-bold text-stone-800 flex items-baseline">
                    <span>{{ item.sampah?.item_sampah?.nama }}</span>
                    <span class="text-xs text-stone-400 font-medium ml-1">× {{ item.berat * 2 }}</span>
                  </div>
                  <div class="w-24 text-right text-sm font-medium text-stone-500">
                    {{ item.berat }} kg
                  </div>
                  <div class="w-32 text-right text-sm font-bold text-stone-800">
                    {{ formatRupiah(item.berat * item.harga) }}
                  </div>
                </div>
              </div>
            </div>

            <div v-if="getGudangLainItems(order.detail_transaksi).length > 0" class="bg-white border border-stone-200 rounded-2xl p-6 space-y-4">
              <h4 class="text-sm font-bold text-stone-400">
                Item Gudang Lain
              </h4>
              <div class="space-y-3">
                <div 
                  v-for="item in getGudangLainItems(order.detail_transaksi)" 
                  :key="item.detail_id" 
                  class="flex justify-between items-center"
                >
                  <div class="flex-1 text-sm font-medium text-stone-400 flex items-baseline">
                    <span>{{ item.sampah?.item_sampah?.nama }}</span>
                    <span class="text-xs text-stone-300 font-normal ml-1">× {{ item.berat * 2 }}</span>
                    <span class="text-xs text-stone-300 font-normal ml-1">({{ item.sampah?.gudang?.nama || `Gudang ${item.sampah?.gudang_id}` }})</span>
                  </div>
                  <div class="w-24 text-right text-sm font-medium text-stone-300">
                    {{ item.berat }} kg
                  </div>
                  <div class="w-32 text-right text-sm font-medium text-stone-300">
                    {{ formatRupiah(item.berat * item.harga) }}
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 pt-4 border-t border-stone-100">
            <div class="text-sm text-stone-500 font-medium">
              Total Keseluruhan: <span class="text-base font-bold text-stone-800">{{ formatRupiah(order.total_harga || order.detail_transaksi.reduce((a, d) => a + (d.berat * d.harga), 0)) }}</span>
            </div>

            <div class="w-full md:w-auto flex flex-wrap items-center justify-end gap-3 self-stretch md:self-auto">
              <button 
                @click="openDetailModal(order)"
                class="flex-1 md:flex-none px-4 py-2 border border-stone-200 text-stone-600 rounded-xl text-xs font-semibold hover:bg-stone-50 transition-all flex items-center justify-center gap-1"
              >
                Lihat Detail
              </button>

              <template v-if="activeFilter === 'perlu_validasi'">
                <template v-if="order.status === 'pending'">
                  <div v-if="isCurrentWarehouseApproved(order)" class="flex items-center gap-2 text-stone-500 text-xs font-semibold py-2">
                    <Icon icon="line-md:loading-twotone-loop" class="w-4 h-4 text-orange-500" />
                    <span>Menunggu konfirmasi {{ getPendingOtherWarehouses(order).join(', ') }}...</span>
                  </div>
                  
                  <template v-else>
                    <button 
                      @click="openRejectOrder(order)"
                      class="flex-1 md:flex-none px-4 py-2 border border-red-200 text-red-600 rounded-xl text-xs font-semibold hover:bg-red-50 transition-all flex items-center justify-center gap-1"
                    >
                      Tolak ({{ currentGudangNama }})
                    </button>
                    <button 
                      @click="openApproveStock(order)"
                      class="flex-1 md:flex-none px-4 py-2 bg-[#4A7043] text-white rounded-xl text-xs font-semibold hover:bg-[#3D5C37] transition-all flex items-center justify-center gap-1 shadow-sm"
                    >
                      Setujui Stok ({{ currentGudangNama }})
                    </button>
                  </template>
                </template>

                <template v-else-if="order.status === 'proses' && order.bukti_transfer">
                  <div v-if="isPaymentVerifiedByCurrentWarehouse(order)" class="flex items-center gap-2 text-stone-500 text-xs font-semibold py-2">
                    <Icon icon="line-md:loading-twotone-loop" class="w-4 h-4 text-orange-500" />
                    <span>Pembayaran terverifikasi ({{ currentGudangNama }}). Menunggu gudang lain...</span>
                  </div>
                  
                  <template v-else>
                    <button 
                      @click="openProofModal(order)"
                      class="flex-1 md:flex-none px-4 py-2 bg-sky-50 border border-sky-100 text-sky-700 rounded-xl text-xs font-semibold hover:bg-sky-100 transition-all flex items-center justify-center gap-1"
                    >
                      <Icon icon="material-symbols:visibility-outline" class="w-4 h-4" />
                      Lihat Bukti Transfer
                    </button>
                    <button 
                      @click="openRejectPaymentModal(order)"
                      class="flex-1 md:flex-none px-4 py-2 border border-red-200 text-red-600 rounded-xl text-xs font-semibold hover:bg-red-50 transition-all flex items-center justify-center gap-1"
                    >
                      Tolak Pembayaran
                    </button>
                    <button 
                      @click="openApprovePaymentModal(order)"
                      class="flex-1 md:flex-none px-4 py-2 bg-[#4A7043] text-white rounded-xl text-xs font-semibold hover:bg-[#3D5C37] transition-all flex items-center justify-center gap-1 shadow-sm"
                    >
                      Setujui Pembayaran
                    </button>
                  </template>
                </template>
              </template>

              <template v-else-if="activeFilter === 'siap_diambil'">
                <div v-if="isCollectionCompletedByCurrentWarehouse(order)" class="flex items-center gap-2 text-stone-500 text-xs font-semibold py-2">
                  <Icon icon="line-md:loading-twotone-loop" class="w-4 h-4 text-orange-500" />
                  <span>Barang telah diserahkan ({{ currentGudangNama }}). Menunggu gudang lain...</span>
                </div>
                
                <button 
                  v-else
                  @click="openCompleteCollectionModal(order)"
                  class="w-full md:w-auto px-4 py-2 bg-[#7A4A28] hover:bg-[#683E20] text-white rounded-xl text-xs font-semibold transition-all flex items-center justify-center shadow-sm"
                >
                  Barang Diserahkan ({{ currentGudangNama }})
                </button>
              </template>

            </div>
          </div>
        </div>
      </div>

      <div v-if="orders.length > 0" class="bg-white rounded-2xl shadow-sm border border-stone-100 p-4 flex flex-col md:flex-row justify-between items-center gap-4">
        <div class="text-xs font-bold text-stone-500">
          Menampilkan {{ orders.length }} dari {{ pagination.total }} pesanan
        </div>
        <div class="flex items-center gap-1">
          <button 
            @click="fetchData(pagination.current_page - 1)"
            :disabled="pagination.current_page === 1"
            class="px-4 py-2 rounded-xl text-xs font-bold bg-white border border-stone-200 text-stone-400 disabled:opacity-50 transition-all"
          >
            Sebelumnya
          </button>
          <div class="px-4 py-2 rounded-xl bg-[#4A7043] text-white text-xs font-bold shadow-md">
            {{ pagination.current_page }} / {{ pagination.last_page }}
          </div>
          <button 
            @click="fetchData(pagination.current_page + 1)"
            :disabled="pagination.current_page === pagination.last_page"
            class="px-4 py-2 rounded-xl text-xs font-bold bg-white border border-stone-200 text-stone-400 disabled:opacity-50 transition-all"
          >
            Selanjutnya
          </button>
        </div>
      </div>
    </div>

    <Transition name="fade">
      <div v-if="selectedOrder" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-stone-900/60 backdrop-blur-sm" @click="closeAllModals"></div>
        
        <div v-if="isConfirmApproveOpen" class="relative bg-white w-full max-w-md rounded-[2rem] shadow-2xl overflow-hidden animate-in zoom-in-95 duration-300 p-6 space-y-6">
          <div class="flex justify-between items-start">
            <h3 class="text-lg font-black text-stone-800">Konfirmasi Persetujuan</h3>
            <button @click="closeAllModals" class="p-2 hover:bg-stone-50 rounded-xl transition-colors text-stone-400">
              <Icon icon="material-symbols:close" class="w-6 h-6" />
            </button>
          </div>
          
          <div class="space-y-4">
            <div class="w-16 h-16 rounded-full bg-green-50 text-[#4A7043] flex items-center justify-center mx-auto shadow-sm">
              <Icon icon="material-symbols:playlist-add-check" class="w-8 h-8" />
            </div>
            <p class="text-sm font-medium text-stone-600 text-center leading-relaxed">
              Apakah Anda yakin stok untuk pesanan <span class="font-extrabold text-stone-800">PSN-{{ String(selectedOrder.transaksi_id).padStart(3, '0') }}</span> di <span class="font-extrabold text-stone-800">{{ currentGudangNama }}</span> sudah tersedia dan siap disisihkan?
            </p>
          </div>
          
          <div class="flex gap-3">
            <button @click="closeAllModals" class="flex-1 py-3.5 rounded-xl bg-stone-50 text-stone-600 font-black text-sm hover:bg-stone-100 transition-colors">Batal</button>
            <button 
              @click="confirmApproveStock" 
              :disabled="isSubmitting"
              class="flex-1 py-3.5 rounded-xl bg-[#2D4A27] text-white font-black text-sm hover:bg-[#1D321A] transition-all flex items-center justify-center gap-1.5 shadow-md active:scale-95"
            >
              <Icon v-if="isSubmitting" icon="material-symbols:hourglass-empty" class="w-4 h-4 animate-spin" />
              <span>Ya, Setujui</span>
            </button>
          </div>
        </div>

        <div v-if="isConfirmRejectOpen" class="relative bg-white w-full max-w-md rounded-[2rem] shadow-2xl overflow-hidden animate-in zoom-in-95 duration-300 p-6 space-y-6">
          <div class="flex justify-between items-start">
            <h3 class="text-lg font-black text-stone-800">Tolak Pesanan PSN-{{ String(selectedOrder.transaksi_id).padStart(3, '0') }}</h3>
            <button @click="closeAllModals" class="p-2 hover:bg-stone-50 rounded-xl transition-colors text-stone-400">
              <Icon icon="material-symbols:close" class="w-6 h-6" />
            </button>
          </div>
          
          <div class="space-y-4">
            <div class="space-y-2">
              <label class="text-xs font-black text-stone-800 flex items-center gap-1">Alasan Penolakan <span class="text-red-500">*</span></label>
              <textarea v-model="rejectReason" placeholder="Masukkan alasan penolakan..." class="w-full bg-stone-50 border border-stone-100 rounded-2xl py-4 px-5 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition-all min-h-[120px] text-stone-700"></textarea>
            </div>
          </div>
          
          <div class="flex gap-3">
            <button @click="closeAllModals" class="flex-1 py-3.5 rounded-xl bg-stone-50 text-stone-600 font-black text-sm hover:bg-stone-100 transition-colors">Batal</button>
            <button 
              @click="confirmRejectOrder" 
              :disabled="isSubmitting || !rejectReason.trim()"
              class="flex-1 py-3.5 rounded-xl bg-red-600 text-white font-black text-sm hover:bg-red-700 transition-all flex items-center justify-center gap-1.5 shadow-md active:scale-95 disabled:opacity-50"
            >
              <Icon v-if="isSubmitting" icon="material-symbols:hourglass-empty" class="w-4 h-4 animate-spin" />
              <span>Tolak Pesanan</span>
            </button>
          </div>
        </div>

        <div v-if="isProofModalOpen" class="relative bg-white w-full max-w-lg rounded-[2rem] shadow-2xl overflow-hidden animate-in zoom-in-95 duration-300 p-6 space-y-6">
          <div class="flex justify-between items-start border-b border-stone-50 pb-4">
            <div>
              <h3 class="text-lg font-black text-stone-800">Bukti Transfer Pembayaran</h3>
              <p class="text-xs text-stone-400 font-bold">PSN-{{ String(selectedOrder.transaksi_id).padStart(3, '0') }} • Pengepul: {{ selectedOrder.pengepul?.nama }}</p>
            </div>
            <button @click="closeAllModals" class="p-2 hover:bg-stone-50 rounded-xl transition-colors text-stone-400">
              <Icon icon="material-symbols:close" class="w-6 h-6" />
            </button>
          </div>
          
          <div class="flex justify-center bg-stone-50 rounded-2xl p-4 overflow-hidden border border-stone-100">
            <img :src="`${axios.defaults.baseURL}/storage/${selectedOrder.bukti_transfer}`" class="max-h-[50vh] max-w-full object-contain rounded-xl shadow-sm" alt="Bukti Transfer Pengepul" />
          </div>
          
          <div class="flex gap-4">
            <button @click="isProofModalOpen = false; openRejectPaymentModal(selectedOrder)" class="flex-1 py-3.5 rounded-2xl border border-red-200 text-red-600 font-semibold text-sm hover:bg-red-50 transition-all flex items-center justify-center gap-2">
              <Icon icon="material-symbols:close-rounded" class="w-4 h-4 shrink-0" />
              <span>Tolak Pembayaran</span>
            </button>
            <button @click="isProofModalOpen = false; openApprovePaymentModal(selectedOrder)" class="flex-1 py-3.5 rounded-2xl bg-[#4A7043] text-white font-semibold text-sm hover:bg-[#3D5C37] transition-all flex items-center justify-center gap-2 shadow-sm">
              <Icon icon="material-symbols:check-rounded" class="w-4 h-4 shrink-0" />
              <span>Setujui Pembayaran</span>
            </button>
          </div>
        </div>

        <div v-if="isApprovePaymentModalOpen" class="relative bg-white w-full max-w-md rounded-3xl shadow-2xl overflow-hidden animate-in zoom-in-95 duration-300 p-6 space-y-6">
          <div class="space-y-3">
            <h3 class="text-lg font-bold text-slate-900">Konfirmasi Persetujuan</h3>
            <p class="text-sm font-medium text-slate-600 leading-relaxed">
              Apakah Anda yakin dana sebesar <span class="font-bold text-slate-900">{{ formatRupiah(selectedOrder.total_harga || selectedOrder.detail_transaksi.reduce((a, d) => a + (d.berat * d.harga), 0)) }}</span> sudah masuk ke rekening?
            </p>
          </div>
          
          <div class="flex justify-end gap-3 pt-2">
            <button @click="closeAllModals" class="px-5 py-2 rounded-xl border border-stone-200 text-stone-600 font-medium text-sm hover:bg-stone-50 transition-colors">Batal</button>
            <button 
              @click="confirmApprovePayment" 
              :disabled="isSubmitting"
              class="px-5 py-2 rounded-xl bg-[#4A7043] text-white font-medium text-sm hover:bg-[#3D5C37] transition-all flex items-center gap-1.5 shadow-sm"
            >
              <Icon v-if="isSubmitting" icon="material-symbols:hourglass-empty" class="w-4 h-4 animate-spin" />
              <span>Ya, Setujui</span>
            </button>
          </div>
        </div>

        <div v-if="isRejectPaymentModalOpen" class="relative bg-white w-full max-w-md rounded-3xl shadow-2xl overflow-hidden animate-in zoom-in-95 duration-300 p-6 space-y-6">
          <div class="space-y-4">
            <h3 class="text-lg font-bold text-slate-900">Tolak Bukti Transfer</h3>
            <div class="space-y-2">
              <label class="text-sm font-semibold text-slate-700">Alasan Bukti Tidak Valid <span class="text-red-500">*</span></label>
              <p class="text-xs text-stone-400">Contoh: Saldo belum masuk, foto buram</p>
              <textarea v-model="rejectReason" placeholder="Masukkan alasan penolakan bukti..." class="w-full bg-white border border-stone-200 rounded-xl py-3 px-4 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition-all min-h-[100px] text-stone-700"></textarea>
            </div>
          </div>
          
          <div class="flex justify-end gap-3 pt-2">
            <button @click="closeAllModals" class="px-5 py-2 rounded-xl border border-stone-200 text-stone-600 font-medium text-sm hover:bg-stone-50 transition-colors">Batal</button>
            <button 
              @click="confirmRejectPayment" 
              :disabled="isSubmitting || !rejectReason.trim()"
              class="px-5 py-2 rounded-xl bg-[#EF4444] text-white font-medium text-sm hover:bg-red-700 transition-all flex items-center gap-1.5 shadow-sm disabled:opacity-50"
            >
              <Icon v-if="isSubmitting" icon="material-symbols:hourglass-empty" class="w-4 h-4 animate-spin" />
              <span>Tolak Bukti</span>
            </button>
          </div>
        </div>

        <div v-if="isDetailModalOpen && selectedOrder" class="relative bg-white w-full max-w-2xl rounded-3xl shadow-2xl overflow-hidden animate-in zoom-in-95 duration-300 p-0 flex flex-col max-h-[90vh]">
          <div class="flex justify-between items-center p-6 border-b border-stone-100">
            <h3 class="text-lg font-black text-stone-800">Detail Pesanan PSN-{{ String(selectedOrder.transaksi_id).padStart(3, '0') }}</h3>
            <button @click="closeAllModals" class="p-2 hover:bg-stone-50 rounded-xl transition-colors text-stone-400">
              <Icon icon="material-symbols:close" class="w-6 h-6" />
            </button>
          </div>
          
          <div class="p-6 overflow-y-auto no-scrollbar space-y-6">
            <div class="bg-stone-50 rounded-2xl p-4 border border-stone-100 space-y-3">
              <h4 class="text-xs font-bold text-stone-400 uppercase tracking-wider">Informasi Pengepul</h4>
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <p class="text-xs text-stone-500 mb-1">Nama Lengkap</p>
                  <p class="text-sm font-bold text-stone-800">{{ selectedOrder.pengepul?.nama || '-' }}</p>
                </div>
                <div>
                  <p class="text-xs text-stone-500 mb-1">Nama Lembaga</p>
                  <p class="text-sm font-bold text-stone-800">{{ selectedOrder.pengepul?.nama_lembaga || '-' }}</p>
                </div>
              </div>
            </div>

            <div v-if="['tolak', 'batal'].includes(selectedOrder.status)" class="bg-red-50 rounded-2xl p-4 border border-red-100 space-y-2">
              <h4 class="text-xs font-bold text-red-500 uppercase tracking-wider">Alasan Pembatalan / Penolakan</h4>
              <p class="text-sm font-semibold text-red-700 leading-relaxed">{{ selectedOrder.ket_status || 'Tidak ada keterangan yang dicantumkan.' }}</p>
            </div>

            <div class="space-y-3">
              <h4 class="text-xs font-bold text-stone-400 uppercase tracking-wider">Detail Barang</h4>
              <div class="bg-white border border-stone-100 rounded-2xl overflow-hidden shadow-sm">
                <div v-for="(item, index) in selectedOrder.detail_transaksi" :key="item.detail_id" class="p-4 flex justify-between items-center" :class="{ 'border-t border-stone-50': index > 0 }">
                  <div class="flex-1">
                    <p class="text-sm font-bold text-stone-800">{{ item.sampah?.item_sampah?.nama }}</p>
                    <p class="text-xs text-stone-500 mt-0.5">{{ item.sampah?.gudang?.nama || `Gudang ${item.sampah?.gudang_id}` }}</p>
                  </div>
                  <div class="text-right">
                    <p class="text-sm font-bold text-stone-800">{{ item.berat }} kg <span class="text-xs text-stone-400 font-normal ml-1">× {{ formatRupiah(item.harga) }}</span></p>
                    <p class="text-sm font-bold text-[#4A7043] mt-0.5">{{ formatRupiah(item.berat * item.harga) }}</p>
                  </div>
                </div>
                <div class="bg-stone-50 p-4 border-t border-stone-100 flex justify-between items-center">
                  <span class="text-sm font-bold text-stone-600">Total Harga</span>
                  <span class="text-base font-black text-stone-800">{{ formatRupiah(selectedOrder.total_harga || selectedOrder.detail_transaksi.reduce((a, d) => a + (d.berat * d.harga), 0)) }}</span>
                </div>
              </div>
            </div>

            <div v-if="selectedOrder.bukti_transfer" class="space-y-3">
              <h4 class="text-xs font-bold text-stone-400 uppercase tracking-wider">Bukti Transfer</h4>
              <div class="flex justify-center bg-stone-50 rounded-2xl p-4 overflow-hidden border border-stone-100">
                <img :src="`${axios.defaults.baseURL}/storage/${selectedOrder.bukti_transfer}`" class="max-h-60 max-w-full object-contain rounded-xl shadow-sm" alt="Bukti Transfer Pengepul" />
              </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div class="bg-stone-50 rounded-2xl p-4 border border-stone-100">
                <p class="text-xs text-stone-500 mb-1">Pesanan Dibuat</p>
                <p class="text-sm font-bold text-stone-800">{{ formatDateTime(selectedOrder.created_at) }}</p>
              </div>
              <div v-if="selectedOrder.status === 'selesai'" class="bg-green-50 rounded-2xl p-4 border border-green-100">
                <p class="text-xs text-green-600 mb-1">Pesanan Selesai</p>
                <p class="text-sm font-bold text-green-800">{{ formatDateTime(selectedOrder.updated_at) }}</p>
              </div>
            </div>
          </div>
        </div>

        <div v-if="isCompleteCollectionModalOpen" class="relative bg-white w-full max-w-md rounded-3xl shadow-2xl overflow-hidden animate-in zoom-in-95 duration-300 p-6 space-y-6">
          <div class="space-y-3">
            <h3 class="text-lg font-bold text-slate-900">Konfirmasi Persetujuan</h3>
            <p class="text-sm font-medium text-slate-600 leading-relaxed">Apakah barang sudah diserahkan ke Pengepul?</p>
          </div>
          
          <div class="flex justify-end gap-3 pt-2">
            <button @click="closeAllModals" class="px-5 py-2 rounded-xl border border-stone-200 text-stone-600 font-medium text-sm hover:bg-stone-50 transition-colors">Batal</button>
            <button 
              @click="confirmCompleteCollection" 
              :disabled="isSubmitting"
              class="px-5 py-2 rounded-xl bg-[#4A7043] text-white font-medium text-sm hover:bg-[#3D5C37] transition-all flex items-center gap-1.5 shadow-sm"
            >
              <Icon v-if="isSubmitting" icon="material-symbols:hourglass-empty" class="w-4 h-4 animate-spin" />
              <span>Ya, Setujui</span>
            </button>
          </div>
        </div>

      </div>
    </Transition>
  </DashboardLayout>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
.animate-in { animation: fadeIn 0.5s ease-out both; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
.zoom-in-95 { animation: zoomIn 0.3s ease-out both; }
@keyframes zoomIn { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }
</style>