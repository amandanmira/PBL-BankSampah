<script setup>
import { ref, onMounted, computed, inject, nextTick } from "vue";
import { Icon } from "@iconify/vue";
import DashboardLayout from "@/layouts/DashboardLayout.vue";
import { checkRole } from "@/utils";
import { useRouter, useRoute } from "vue-router";

// Security check
checkRole("petugas");

const router = useRouter();
const route  = useRoute();          // ← untuk membaca query params dari dashboard
const axios  = inject('axios');

const user = ref(JSON.parse(sessionStorage.getItem("user") || "{}"));

// ─── State ────────────────────────────────────────────────────────────────────
const requests            = ref([]);
const workers             = ref([]);
const loading             = ref(false);
const searchQuery         = ref("");
const activeFilter        = ref("Menunggu");
const isWorkerModalOpen   = ref(false);
const selectedRequest     = ref(null);
const isDetailModalOpen   = ref(false);
const detailRequest       = ref(null);
const isRejectModalOpen   = ref(false);
const rejectRequest       = ref(null);
const rejectReason        = ref("");

// ─── ID yang sedang di-highlight (dikirim dari dashboard via query param) ─────
const highlightedId = ref(null);

// ─── Fetch data ───────────────────────────────────────────────────────────────
const fetchData = async () => {
  loading.value = true;
  try {
    const res = await axios.get("/api/petugas/penjemputan");
    requests.value = res.data.data.map(r => ({
      ...r,
      selectedTukang:    r.tukang || null,
      showScheduleForm:  false,
      scheduleDate:      r.jadwal ? r.jadwal.split(' ')[0] : '',
      scheduleHour:      r.jadwal ? r.jadwal.split(' ')[1]?.split(':')[0] : '',
      scheduleMinute:    r.jadwal ? r.jadwal.split(' ')[1]?.split(':')[1] : '',
    }));
  } catch (err) {
    console.error("Failed to fetch requests:", err);
  } finally {
    loading.value = false;
  }
};

const fetchWorkers = async () => {
  try {
    const res = await axios.get("/api/petugas/tukang");
    workers.value = res.data.data;
  } catch (err) {
    console.error("Failed to fetch workers:", err);
  }
};

// ─── Filter & computed ────────────────────────────────────────────────────────
const filteredRequests = computed(() => {
  let filtered = requests.value;

  if (user.value?.gudang_id) {
    filtered = filtered.filter(r => Number(r.gudang_id) === Number(user.value.gudang_id));
  }

  if (activeFilter.value === "Menunggu") {
    filtered = filtered.filter(r => ['pending', 'menunggu_persetujuan', 'jadwal_ditolak'].includes(r.status));
  } else if (activeFilter.value === "Diproses") {
    filtered = filtered.filter(r => r.status === 'proses');
  } else if (activeFilter.value === "Dijemput") {
    filtered = filtered.filter(r => r.status === 'dijemput');
  } else if (activeFilter.value === "Perlu Input Data") {
    filtered = filtered.filter(r => r.status === 'perlu_input');
  }

  if (searchQuery.value) {
    const q = searchQuery.value.toLowerCase();
    filtered = filtered.filter(r =>
      r.penjemputan_id.toString().includes(q) ||
      (r.nasabah?.nama && r.nasabah.nama.toLowerCase().includes(q))
    );
  }

  return filtered;
});

const filteredWorkers = computed(() => {
  if (!selectedRequest.value) return [];
  return workers.value.filter(w => Number(w.gudang_id) === Number(selectedRequest.value.gudang_id));
});

const getCount = (filter) => {
  let valid = requests.value;
  if (user.value?.gudang_id) valid = valid.filter(r => Number(r.gudang_id) === Number(user.value.gudang_id));
  if (filter === "Menunggu")         return valid.filter(r => ['pending', 'menunggu_persetujuan', 'jadwal_ditolak'].includes(r.status)).length;
  if (filter === "Diproses")         return valid.filter(r => r.status === 'proses').length;
  if (filter === "Dijemput")         return valid.filter(r => r.status === 'dijemput').length;
  if (filter === "Perlu Input Data") return valid.filter(r => r.status === 'perlu_input').length;
  return 0;
};

// ─────────────────────────────────────────────────────────────────────────────
// INTI FITUR BARU: Baca query param dari dashboard, set filter & scroll/highlight
// ─────────────────────────────────────────────────────────────────────────────
const applyDeepLink = async () => {
  const { filter, id } = route.query;

  // 1. Set tab yang benar jika dikirim dari dashboard
  if (filter) {
    const validFilters = ['Menunggu', 'Diproses', 'Dijemput', 'Perlu Input Data'];
    if (validFilters.includes(filter)) {
      activeFilter.value = filter;
    }
  }

  // 2. Simpan ID yang akan di-highlight
  if (id) {
    highlightedId.value = parseInt(id, 10);
  }

  // 3. Tunggu DOM selesai render, lalu scroll ke card yang dimaksud
  if (id) {
    await nextTick();
    // Beri sedikit delay agar list sudah muncul di DOM
    setTimeout(() => {
      scrollToCard(parseInt(id, 10));
    }, 400);
  }
};

// Scroll ke card berdasarkan penjemputan_id
const scrollToCard = (id) => {
  const el = document.getElementById(`card-penjemputan-${id}`);
  if (el) {
    el.scrollIntoView({ behavior: 'smooth', block: 'center' });
    // Hapus highlight setelah 3 detik agar tidak mengganggu
    setTimeout(() => { highlightedId.value = null; }, 3000);
  }
};

// ─── Actions ──────────────────────────────────────────────────────────────────
const openWorkerModal = (request) => {
  selectedRequest.value = request;
  isWorkerModalOpen.value = true;
};

const selectWorker = (worker) => {
  if (selectedRequest.value) selectedRequest.value.selectedTukang = worker;
  isWorkerModalOpen.value = false;
};

const handleTerima = (request) => {
  if (!request.selectedTukang) return;
  request.showScheduleForm = true;
};

const confirmSchedule = async (request) => {
  if (!request.scheduleDate || !request.scheduleHour || !request.scheduleMinute) {
    alert("Mohon lengkapi tanggal dan waktu penjemputan.");
    return;
  }
  try {
    loading.value = true;
    const datetime = `${request.scheduleDate} ${request.scheduleHour}:${request.scheduleMinute}:00`;
    await axios.put(`/api/petugas/penjemputan/${request.penjemputan_id}/terima`, {
      tukang_id: request.selectedTukang.tukang_id,
      jadwal:    datetime,
    });
    request.status           = 'menunggu_persetujuan';
    request.jadwal           = datetime;
    request.showScheduleForm = false;
  } catch (err) {
    console.error("Failed to confirm schedule:", err);
    alert("Gagal mengkonfirmasi jadwal.");
  } finally {
    loading.value = false;
  }
};

const assignTukang = async (request) => {
  try {
    loading.value = true;
    await axios.put(`/api/petugas/penjemputan/${request.penjemputan_id}/dijemput`);
    request.status    = 'dijemput';
    activeFilter.value = "Dijemput";
  } catch (err) {
    console.error("Gagal menugaskan tukang:", err);
    alert("Gagal menugaskan tukang.");
  } finally {
    loading.value = false;
  }
};

const sampaiLokasi = async (request) => {
  try {
    loading.value = true;
    await axios.put(`/api/petugas/penjemputan/${request.penjemputan_id}/sampai-lokasi`);
    request.status    = 'perlu_input';
    activeFilter.value = "Perlu Input Data";
  } catch (err) {
    console.error("Gagal update status sampai lokasi:", err);
    alert("Gagal mengupdate status sampai lokasi.");
  } finally {
    loading.value = false;
  }
};

const inputDataTransaksi = (request) => {
  router.push('/dashboard-petugas/penimbangan/' + request.penjemputan_id);
};

const handleTolak = (request) => {
  rejectRequest.value = request;
  rejectReason.value  = "";
  isRejectModalOpen.value = true;
};

const confirmReject = async () => {
  if (!rejectReason.value.trim()) {
    alert("Silakan isi alasan penolakan.");
    return;
  }
  try {
    loading.value = true;
    await axios.put(`/api/petugas/penjemputan/${rejectRequest.value.penjemputan_id}/tolak`, {
      ket_status: rejectReason.value,
    });
    requests.value      = requests.value.filter(r => r.penjemputan_id !== rejectRequest.value.penjemputan_id);
    isRejectModalOpen.value = false;
  } catch (err) {
    console.error("Gagal menolak request:", err);
    alert("Terjadi kesalahan saat menolak request.");
  } finally {
    loading.value = false;
  }
};

const showDetail = (request) => {
  detailRequest.value   = request;
  isDetailModalOpen.value = true;
};

const formatDate = (dateStr) => {
  if (!dateStr) return "-";
  const date = new Date(dateStr);
  return date.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })
    + ", "
    + date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
};

// ─── Lifecycle ────────────────────────────────────────────────────────────────
onMounted(async () => {
  await fetchData();
  fetchWorkers();
  // Setelah data loaded, terapkan deep-link dari dashboard
  await applyDeepLink();
});
</script>

<template>
  <DashboardLayout title="Request Penjemputan">
    <div class="space-y-6 animate-in fade-in duration-700 pb-10">
      <div>
        <h2 class="text-2xl font-black text-stone-800">Request Penjemputan</h2>
        <p class="text-stone-500 font-medium">Kelola request penjemputan dari nasabah yang masih aktif</p>
        <p class="text-stone-400 text-[10px] font-bold mt-1 italic">* Request yang sudah selesai/ditolak ada di menu Riwayat Transaksi</p>
      </div>

      <!-- Search -->
      <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-stone-100">
        <label class="block text-[10px] font-black text-stone-400 uppercase tracking-widest mb-2">Cari Request</label>
        <div class="flex gap-3">
          <div class="flex-1 relative">
            <input
              v-model="searchQuery"
              type="text"
              placeholder="ID Request atau Nama Nasabah"
              class="w-full bg-stone-50 border border-stone-100 rounded-2xl py-3.5 px-5 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-[#4A7043]/20 transition-all"
            />
          </div>
          <button class="bg-[#4A7043] text-white p-3.5 rounded-2xl shadow-lg shadow-green-900/10 hover:scale-105 active:scale-95 transition-all">
            <Icon icon="material-symbols:search" class="w-6 h-6" />
          </button>
        </div>
      </div>

      <!-- Filter tabs -->
      <div class="bg-white rounded-[2rem] p-2 shadow-sm border border-stone-100 flex">
        <button
          v-for="filter in ['Menunggu', 'Diproses', 'Dijemput', 'Perlu Input Data']"
          :key="filter"
          @click="activeFilter = filter"
          :class="[
            'flex-1 py-3.5 rounded-[1.5rem] text-xs font-black transition-all flex items-center justify-center gap-2',
            activeFilter === filter ? 'bg-[#4A7043] text-white shadow-md' : 'text-stone-400 hover:bg-stone-50'
          ]"
        >
          {{ filter }}
          <span
            v-if="getCount(filter) > 0"
            :class="[
              'px-2 py-0.5 rounded-full text-[10px]',
              activeFilter === filter ? 'bg-white/20 text-white' : 'bg-stone-100 text-stone-500'
            ]"
          >
            {{ getCount(filter) }}
          </span>
        </button>
      </div>

      <!-- Card list -->
      <div class="space-y-4">
        <div v-if="loading" class="flex justify-center py-20">
          <Icon icon="line-md:loading-twotone-loop" class="w-12 h-12 text-[#4A7043]" />
        </div>

        <template v-else-if="filteredRequests.length > 0">
          <div
            v-for="request in filteredRequests"
            :key="request.penjemputan_id"
            :id="`card-penjemputan-${request.penjemputan_id}`"
            :class="[
              'rounded-[2rem] p-6 shadow-sm border space-y-6 transition-all duration-500',
              // ── HIGHLIGHT: ring hijau + bg sedikit lebih terang jika ini item dari dashboard ──
              highlightedId === request.penjemputan_id
                ? 'bg-[#F0F7EF] border-[#4A7043] ring-2 ring-[#4A7043]/30 shadow-lg'
                : 'bg-white border-stone-100'
            ]"
          >
            <!-- Header card -->
            <div class="flex justify-between items-start">
              <div class="flex items-center gap-3 flex-wrap">
                <h3 class="text-xl font-black text-stone-800">REQ-{{ String(request.penjemputan_id).padStart(3, '0') }}</h3>

                <!-- Badge highlight "Dari Dashboard" -->
                <span
                  v-if="highlightedId === request.penjemputan_id"
                  class="px-3 py-1 rounded-full text-[10px] font-black uppercase bg-[#4A7043] text-white animate-pulse"
                >
                  ← Dari Dashboard
                </span>

                <span :class="[
                  'px-3 py-1 rounded-full text-[10px] font-black uppercase',
                  request.status === 'pending'                ? 'bg-orange-100 text-orange-600' :
                  request.status === 'menunggu_persetujuan'   ? 'bg-indigo-100 text-indigo-600' :
                  request.status === 'jadwal_ditolak'         ? 'bg-red-100 text-red-600' :
                  request.status === 'proses'                 ? 'bg-blue-100 text-blue-600' :
                  request.status === 'dijemput'               ? 'bg-purple-100 text-purple-600' :
                  request.status === 'perlu_input'            ? 'bg-orange-100 text-orange-500' :
                                                                'bg-stone-100 text-stone-500'
                ]">
                  {{
                    request.status === 'pending'              ? 'Menunggu' :
                    request.status === 'menunggu_persetujuan' ? 'Menunggu Nasabah' :
                    request.status === 'jadwal_ditolak'       ? 'Jadwal Ditolak' :
                    request.status === 'perlu_input'          ? 'Perlu Input Data' : request.status
                  }}
                </span>
                <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase bg-stone-100 text-stone-500">
                  Penjemputan
                </span>
              </div>

              <!-- Tombol Detail -->
              <button
                @click="showDetail(request)"
                class="flex items-center gap-1.5 px-4 py-2 rounded-xl bg-stone-50 text-stone-400 text-[10px] font-black hover:bg-stone-100 transition-colors uppercase tracking-widest"
              >
                <Icon icon="material-symbols:visibility-outline" class="w-4 h-4" />
                Detail
              </button>
            </div>

            <!-- Info nasabah -->
            <div class="space-y-3">
              <p class="font-black text-stone-800">
                {{ request.nasabah?.nama || 'Unknown' }}
                <span class="text-stone-400 font-bold">(NSB-{{ String(request.nasabah_id).padStart(3, '0') }})</span>
              </p>
              <div class="flex flex-wrap gap-y-2 gap-x-6">
                <div class="flex items-center gap-2 text-stone-400">
                  <Icon icon="material-symbols:location-on-outline" class="w-4 h-4" />
                  <p class="text-xs font-bold">{{ request.alamat }}</p>
                </div>
                <div class="flex items-center gap-2 text-stone-400">
                  <Icon icon="material-symbols:calendar-today-outline" class="w-4 h-4" />
                  <p class="text-xs font-bold">{{ formatDate(request.created_at) }}</p>
                </div>
                <div class="flex items-center gap-2 text-stone-400">
                  <Icon icon="material-symbols:call-outline" class="w-4 h-4" />
                  <p class="text-xs font-bold">{{ request.nasabah?.no_telp || '-' }}</p>
                </div>
              </div>
            </div>

            <!-- Info sampah -->
            <div class="bg-stone-50 rounded-2xl p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <p class="text-[10px] font-black text-stone-400 uppercase tracking-widest mb-1">Estimasi Berat</p>
                <p class="font-black text-stone-800 text-lg">{{ request.estimasi_berat ? request.estimasi_berat + ' kg' : '-' }}</p>
              </div>
              <div>
                <p class="text-[10px] font-black text-stone-400 uppercase tracking-widest mb-1">Jenis Sampah</p>
                <p class="font-black text-stone-800 text-lg">{{ request.detail_penjemputan?.map(d => d.sampah?.item_sampah?.nama).join(', ') || 'Tidak ada' }}</p>
              </div>
              <div class="md:col-span-2 pt-2 border-t border-stone-100">
                <p class="text-[10px] font-black text-stone-400 uppercase tracking-widest mb-1">Preferensi Waktu Jemput</p>
                <p class="font-bold text-stone-800 text-sm">{{ request.rentang_hari || 'Belum diatur' }} &bull; {{ request.rentan_waktu || 'Belum diatur' }}</p>
              </div>
              <div class="md:col-span-2">
                <p class="text-[10px] font-black text-stone-400 uppercase tracking-widest mb-1">Tukang Ditugaskan</p>
                <p class="font-black text-stone-800 text-lg">{{ request.tukang?.nama || request.selectedTukang?.nama || '-' }}</p>
              </div>
              <div v-if="request.deskripsi?.split('|')[2]" class="md:col-span-2 pt-2 border-t border-stone-100">
                <p class="text-[10px] font-black text-stone-400 uppercase tracking-widest mb-1">Keterangan Tambahan</p>
                <p class="text-xs font-bold text-stone-600">{{ request.deskripsi?.split('|')[2] }}</p>
              </div>
            </div>

            <!-- ── Aksi: PENDING / JADWAL DITOLAK ── -->
            <template v-if="['pending', 'jadwal_ditolak'].includes(request.status)">

              <div v-if="request.status === 'jadwal_ditolak' && !request.showScheduleForm"
                class="bg-red-50 border border-red-200 rounded-2xl p-4 flex flex-col gap-2">
                <div class="flex items-center gap-2 text-red-700">
                  <Icon icon="material-symbols:warning-outline" class="w-5 h-5" />
                  <p class="font-black text-sm">Jadwal Sebelumnya Ditolak Nasabah</p>
                </div>
                <p class="text-xs font-bold text-red-600">Alasan: "{{ request.ket_status }}"</p>
                <p class="text-[10px] text-red-500 font-medium">Silakan atur jadwal penjemputan ulang.</p>
              </div>

              <button
                @click="openWorkerModal(request)"
                :class="[
                  'w-full py-4 rounded-2xl text-sm font-black transition-all border-2 flex items-center justify-center gap-2',
                  request.selectedTukang
                    ? 'bg-stone-50 border-stone-200 text-stone-800'
                    : 'bg-red-50 border-red-200 text-red-600 border-dashed'
                ]"
              >
                <span v-if="!request.selectedTukang">Pilih tukang *</span>
                <template v-else>
                  <span>{{ request.selectedTukang.nama }}</span>
                  <Icon icon="material-symbols:keyboard-arrow-down" class="w-5 h-5" />
                </template>
              </button>

              <div v-if="request.showScheduleForm" class="bg-green-50 border border-green-100 rounded-2xl p-4 flex items-center gap-3">
                <div class="w-8 h-8 bg-white rounded-full flex items-center justify-center text-green-600 shadow-sm">
                  <Icon icon="material-symbols:person-check-outline" class="w-5 h-5" />
                </div>
                <div>
                  <p class="text-green-800 font-black text-xs leading-none">Request telah Anda ambil</p>
                  <p class="text-green-600 font-bold text-[10px] mt-1">Lanjutkan dengan menjadwalkan penjemputan</p>
                </div>
              </div>

              <div v-if="request.showScheduleForm"
                class="bg-[#EAF0EB] border border-[#C2D6C5] rounded-[1.5rem] p-6 space-y-5 relative shadow-sm mt-4">
                <button @click="request.showScheduleForm = false" class="absolute top-6 right-6 text-stone-500 hover:text-stone-800 transition-colors">
                  <Icon icon="material-symbols:close" class="w-5 h-5" />
                </button>
                <h4 class="font-bold text-stone-700 text-sm">Form Jadwal Penjemputan</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div class="space-y-1.5">
                    <label class="text-xs font-semibold text-stone-600">Tanggal</label>
                    <input type="date" v-model="request.scheduleDate"
                      class="w-full bg-white border border-[#A8C4AC] rounded-xl py-2.5 px-4 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-[#84A087]/30 transition-all text-stone-700" />
                  </div>
                  <div class="space-y-1.5">
                    <label class="text-xs font-semibold text-stone-600">Waktu</label>
                    <div class="flex items-center bg-white border border-[#A8C4AC] rounded-xl focus-within:ring-2 focus-within:ring-[#84A087]/30 transition-all overflow-hidden pr-2">
                      <select v-model="request.scheduleHour"
                        class="flex-1 bg-transparent py-2.5 px-4 text-sm font-medium focus:outline-none text-stone-700 appearance-none text-center cursor-pointer hover:bg-stone-50 transition-colors">
                        <option value="" disabled selected>Jam</option>
                        <option v-for="h in 24" :key="h" :value="String(h-1).padStart(2, '0')">{{ String(h-1).padStart(2, '0') }}</option>
                      </select>
                      <span class="text-stone-400 font-black text-sm select-none">:</span>
                      <select v-model="request.scheduleMinute"
                        class="flex-1 bg-transparent py-2.5 px-4 text-sm font-medium focus:outline-none text-stone-700 appearance-none text-center cursor-pointer hover:bg-stone-50 transition-colors">
                        <option value="" disabled selected>Mnt</option>
                        <option v-for="m in 60" :key="m" :value="String(m-1).padStart(2, '0')">{{ String(m-1).padStart(2, '0') }}</option>
                      </select>
                      <Icon icon="material-symbols:schedule-outline" class="w-4 h-4 text-stone-400 pointer-events-none shrink-0 ml-1" />
                    </div>
                  </div>
                </div>
                <button
                  @click="confirmSchedule(request)"
                  :disabled="!request.scheduleDate || !request.scheduleHour || !request.scheduleMinute"
                  :class="[
                    'w-full py-3 mt-2 rounded-xl text-white text-sm font-bold transition-all shadow-sm',
                    request.scheduleDate && request.scheduleHour && request.scheduleMinute
                      ? 'bg-[#4A7043] hover:bg-[#3D5C37] active:scale-[0.98]'
                      : 'bg-stone-300 text-stone-100 cursor-not-allowed shadow-none'
                  ]"
                >
                  Konfirmasi Jadwal
                </button>
              </div>

              <div v-if="!request.showScheduleForm" class="flex flex-col md:flex-row gap-4">
                <button
                  @click="handleTerima(request)"
                  :disabled="!request.selectedTukang"
                  :class="[
                    'flex-[2] py-4 rounded-2xl text-sm font-black uppercase tracking-widest transition-all shadow-lg',
                    request.selectedTukang
                      ? 'bg-[#4A7043] text-white hover:bg-[#3D5C37]'
                      : 'bg-stone-200 text-stone-400 cursor-not-allowed shadow-none'
                  ]"
                >
                  {{ request.status === 'jadwal_ditolak' ? 'Jadwalkan Ulang Penjemputan' : 'Terima & Atur Jadwal Penjemputan' }}
                </button>
                <button
                  @click="handleTolak(request)"
                  class="flex-1 py-4 rounded-2xl bg-[#C62828] text-white text-sm font-black uppercase tracking-widest hover:bg-[#B71C1C] transition-all shadow-lg"
                >
                  Tolak
                </button>
              </div>
            </template>

            <!-- ── Aksi: MENUNGGU PERSETUJUAN ── -->
            <template v-if="request.status === 'menunggu_persetujuan'">
              <div class="bg-indigo-50 border border-indigo-200 rounded-2xl p-4 flex flex-col gap-2 mt-4 shadow-sm">
                <div class="flex items-center gap-2 text-indigo-700">
                  <Icon icon="material-symbols:hourglass-empty" class="w-5 h-5 animate-pulse" />
                  <p class="font-black text-sm uppercase tracking-wider">Menunggu Persetujuan Nasabah</p>
                </div>
                <p class="text-xs font-bold text-indigo-600/80 leading-relaxed">
                  Jadwal penjemputan telah Anda buat dan sedang menunggu konfirmasi atau persetujuan dari pihak nasabah melalui aplikasi mereka.
                </p>
              </div>
            </template>

            <!-- ── Aksi: PROSES ── -->
            <template v-if="request.status === 'proses'">
              <button
                @click="assignTukang(request)"
                class="w-full py-4 rounded-2xl bg-[#4A7043] text-white text-sm font-black uppercase tracking-widest hover:bg-[#3D5C37] transition-all shadow-lg mt-2"
              >
                Tugaskan Tukang
              </button>
            </template>

            <!-- ── Aksi: DIJEMPUT ── -->
            <template v-if="request.status === 'dijemput'">
              <div class="bg-[#F9F6FC] border border-[#EBDDF6] rounded-[1.5rem] p-5 mt-2">
                <div class="flex items-center gap-4 mb-4">
                  <div class="w-10 h-10 bg-[#EFE3F8] rounded-xl flex items-center justify-center text-purple-600 shrink-0">
                    <Icon icon="material-symbols:local-shipping-outline" class="w-5 h-5" />
                  </div>
                  <div>
                    <h4 class="text-stone-800 font-black text-[13px]">Tukang Penjemputan Sedang OTW</h4>
                    <p class="text-stone-500 font-medium text-[10px] mt-0.5">Tracking sudah terkirim ke nasabah</p>
                  </div>
                </div>
                <button
                  @click="sampaiLokasi(request)"
                  class="w-full py-3.5 rounded-xl bg-[#4A7043] text-white text-[13px] font-bold hover:bg-[#3D5C37] transition-all flex items-center justify-center gap-1.5 active:scale-[0.99]"
                >
                  <Icon icon="material-symbols:check" class="w-4 h-4 font-bold" />
                  Sampai Lokasi - Input Data Transaksi
                </button>
              </div>
            </template>

            <!-- ── Aksi: PERLU INPUT ── -->
            <template v-if="request.status === 'perlu_input'">
              <button
                @click="inputDataTransaksi(request)"
                class="w-full py-4 rounded-2xl bg-[#4A7043] text-white text-sm font-bold hover:bg-[#3D5C37] transition-all shadow-lg mt-2"
              >
                Input Data Transaksi
              </button>
            </template>
          </div>
        </template>

        <div v-else class="flex flex-col items-center justify-center py-20 opacity-60">
          <div class="w-20 h-20 bg-stone-100 rounded-full flex items-center justify-center mb-6">
            <Icon icon="material-symbols:inbox-outline" class="w-10 h-10 text-stone-300" />
          </div>
          <p class="text-stone-400 font-black text-lg">Belum Ada Request Penjemputan Yang Tersedia</p>
        </div>
      </div>
    </div>

    <!-- ══════════════════════════════════════════════════════════════════════ -->
    <!-- Modal: Pilih Tukang                                                   -->
    <!-- ══════════════════════════════════════════════════════════════════════ -->
    <div v-if="isWorkerModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center p-6">
      <div class="absolute inset-0 bg-stone-900/60 backdrop-blur-sm" @click="isWorkerModalOpen = false"></div>
      <div class="relative bg-[#F5F5F0] w-full max-w-2xl rounded-[2.5rem] shadow-2xl flex flex-col max-h-[80vh] overflow-hidden">
        <div class="p-8 pb-4 flex justify-between items-center">
          <h3 class="text-xl font-black text-stone-800">Pilih Tukang Untuk Penjemputan</h3>
          <button @click="isWorkerModalOpen = false" class="p-2 hover:bg-stone-200 rounded-xl transition-colors">
            <Icon icon="material-symbols:close" class="w-6 h-6 text-stone-400" />
          </button>
        </div>
        <div class="flex-1 overflow-y-auto p-8 pt-4 space-y-4">
          <div
            v-for="worker in filteredWorkers"
            :key="worker.tukang_id"
            @click="selectWorker(worker)"
            class="bg-[#4A7043] rounded-2xl p-5 text-white flex items-center gap-6 cursor-pointer hover:scale-[1.02] active:scale-[0.98] transition-all group"
          >
            <div class="w-20 h-20 bg-white/10 rounded-2xl flex items-center justify-center overflow-hidden border border-white/10 shrink-0">
              <img v-if="worker.foto" :src="`https://api.tabungansampah.online/storage/${worker.foto}`" alt="Foto Tukang" class="w-full h-full object-cover" />
              <Icon v-else icon="material-symbols:image-outline" class="w-8 h-8 text-white/30" />
            </div>
            <div class="flex-1 space-y-1">
              <div class="flex justify-between items-start">
                <div>
                  <p class="text-[10px] font-black text-white/40 uppercase tracking-widest">Nama:</p>
                  <p class="font-black text-lg">{{ worker.nama }}</p>
                </div>
                <button class="bg-white text-[#4A7043] px-5 py-2 rounded-xl text-xs font-black shadow-lg">
                  Pilih Tukang
                </button>
              </div>
              <div class="flex gap-6 pt-2">
                <div>
                  <p class="text-[10px] font-black text-white/40 uppercase tracking-widest">No. Telp:</p>
                  <p class="text-sm font-bold">{{ worker.no_telp }}</p>
                </div>
              </div>
            </div>
          </div>
          <div v-if="filteredWorkers.length === 0" class="text-center py-10">
            <p class="text-stone-400 font-bold">Tidak ada tukang yang tersedia di gudang ini.</p>
          </div>
        </div>
      </div>
    </div>

    <!-- ══════════════════════════════════════════════════════════════════════ -->
    <!-- Modal: Detail Request                                                  -->
    <!-- ══════════════════════════════════════════════════════════════════════ -->
    <div v-if="isDetailModalOpen && detailRequest" class="fixed inset-0 z-[100] flex items-center justify-center p-6">
      <div class="absolute inset-0 bg-stone-900/60 backdrop-blur-sm" @click="isDetailModalOpen = false"></div>
      <div class="relative bg-[#F5F5F0] w-full max-w-3xl rounded-[2.5rem] shadow-2xl flex flex-col max-h-[90vh] overflow-hidden animate-in fade-in zoom-in-95 duration-300">
        <div class="p-8 pb-6 flex justify-between items-start bg-white border-b border-stone-100">
          <div>
            <h3 class="text-2xl font-black text-stone-800">Detail Request Penjemputan</h3>
            <p class="text-stone-500 font-medium text-sm mt-1">Informasi lengkap request dari nasabah</p>
          </div>
          <button @click="isDetailModalOpen = false" class="p-2.5 bg-stone-50 hover:bg-stone-200 rounded-xl transition-colors">
            <Icon icon="material-symbols:close" class="w-5 h-5 text-stone-500 font-bold" />
          </button>
        </div>
        <div class="flex-1 overflow-y-auto p-8 space-y-6">
          <div class="flex items-center gap-3 mb-2">
            <h4 class="text-xl font-black text-stone-800">REQ-{{ String(detailRequest.penjemputan_id).padStart(3, '0') }}</h4>
            <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase bg-orange-100 text-orange-600">
              {{ detailRequest.status === 'pending' ? 'Menunggu' : detailRequest.status === 'menunggu_persetujuan' ? 'Menunggu Nasabah' : detailRequest.status }}
            </span>
          </div>

          <div class="bg-white rounded-2xl p-6 shadow-sm border border-stone-100 space-y-4">
            <h5 class="text-sm font-black text-stone-800 mb-2">Informasi Nasabah</h5>
            <div class="space-y-4">
              <div class="flex items-start gap-3">
                <Icon icon="material-symbols:person-outline" class="w-5 h-5 text-stone-400 mt-0.5" />
                <div>
                  <p class="text-[10px] font-black text-stone-400 uppercase tracking-widest">Nama Nasabah</p>
                  <p class="font-black text-stone-800">{{ detailRequest.nasabah?.nama || 'Unknown' }} <span class="text-stone-400 font-bold">(NSB-{{ String(detailRequest.nasabah_id).padStart(3, '0') }})</span></p>
                </div>
              </div>
              <div class="flex items-start gap-3">
                <Icon icon="material-symbols:location-on-outline" class="w-5 h-5 text-stone-400 mt-0.5" />
                <div>
                  <p class="text-[10px] font-black text-stone-400 uppercase tracking-widest">Alamat Penjemputan</p>
                  <p class="font-bold text-stone-800 text-sm leading-snug">{{ detailRequest.alamat }}</p>
                </div>
              </div>
              <div class="flex items-start gap-3">
                <Icon icon="material-symbols:call-outline" class="w-5 h-5 text-stone-400 mt-0.5" />
                <div>
                  <p class="text-[10px] font-black text-stone-400 uppercase tracking-widest">Nomor Telepon</p>
                  <p class="font-bold text-stone-800 text-sm">{{ detailRequest.nasabah?.no_telp || '-' }}</p>
                </div>
              </div>
              <div class="flex items-start gap-3">
                <Icon icon="material-symbols:calendar-today-outline" class="w-5 h-5 text-stone-400 mt-0.5" />
                <div>
                  <p class="text-[10px] font-black text-stone-400 uppercase tracking-widest">Waktu Request</p>
                  <p class="font-bold text-stone-800 text-sm">{{ formatDate(detailRequest.created_at) }}</p>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-2xl p-6 shadow-sm border border-stone-100 space-y-4">
            <h5 class="text-sm font-black text-stone-800 mb-2">Informasi Sampah</h5>
            <div class="space-y-4">
              <div class="flex items-start gap-3">
                <Icon icon="material-symbols:monitor-weight-outline" class="w-5 h-5 text-stone-400 mt-0.5" />
                <div>
                  <p class="text-[10px] font-black text-stone-400 uppercase tracking-widest">Estimasi Berat</p>
                  <p class="font-black text-stone-800">{{ detailRequest.deskripsi?.split('|')[0] || detailRequest.estimasi_berat || '-' }}</p>
                </div>
              </div>
              <div class="flex items-start gap-3">
                <Icon icon="material-symbols:inventory-2-outline" class="w-5 h-5 text-stone-400 mt-0.5" />
                <div>
                  <p class="text-[10px] font-black text-stone-400 uppercase tracking-widest mb-1">Jenis Sampah</p>
                  <div class="flex flex-wrap gap-2">
                    <span
                      v-for="(jenis, idx) in (detailRequest.deskripsi?.split('|')[1] ? detailRequest.deskripsi.split('|')[1].split(', ') : [])"
                      :key="idx"
                      class="px-3 py-1 bg-stone-50 border border-stone-200 text-stone-600 rounded-full text-xs font-bold"
                    >
                      {{ jenis.trim() }}
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-2xl p-6 shadow-sm border border-stone-100 space-y-4">
            <h5 class="text-sm font-black text-stone-800 mb-2">Preferensi Waktu Penjemputan</h5>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="flex items-start gap-3">
                <Icon icon="material-symbols:date-range-outline" class="w-5 h-5 text-stone-400 mt-0.5" />
                <div>
                  <p class="text-[10px] font-black text-stone-400 uppercase tracking-widest">Preferensi Hari</p>
                  <p class="font-bold text-stone-800">{{ detailRequest.rentang_hari || 'Belum diatur oleh nasabah' }}</p>
                </div>
              </div>
              <div class="flex items-start gap-3">
                <Icon icon="material-symbols:schedule-outline" class="w-5 h-5 text-stone-400 mt-0.5" />
                <div>
                  <p class="text-[10px] font-black text-stone-400 uppercase tracking-widest">Preferensi Waktu</p>
                  <p class="font-bold text-stone-800">{{ detailRequest.rentan_waktu || 'Tidak diatur oleh nasabah' }}</p>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-2xl p-6 shadow-sm border border-stone-100 space-y-4">
            <h5 class="text-sm font-black text-stone-800 mb-2">Foto Sampah</h5>
            <div v-if="detailRequest.foto && (Array.isArray(detailRequest.foto) ? detailRequest.foto.length > 0 : true)" class="flex gap-4 overflow-x-auto pb-2">
              <template v-if="Array.isArray(detailRequest.foto)">
                <img v-for="(f, i) in detailRequest.foto" :key="i" :src="`https://api.tabungansampah.online/storage/${f}`" alt="Foto Sampah" class="w-32 h-32 rounded-xl object-cover shadow-sm border border-stone-100 flex-shrink-0" />
              </template>
              <template v-else>
                <img :src="`https://api.tabungansampah.online/storage/${detailRequest.foto}`" alt="Foto Sampah" class="w-32 h-32 rounded-xl object-cover shadow-sm border border-stone-100 flex-shrink-0" />
              </template>
            </div>
            <div v-else class="text-stone-400 text-sm font-bold italic">Tidak ada foto terlampir.</div>
          </div>

          <div class="bg-stone-50 rounded-2xl p-6 border border-stone-100 space-y-3">
            <div class="flex items-center gap-2 mb-1">
              <Icon icon="material-symbols:description-outline" class="w-4 h-4 text-stone-400" />
              <p class="text-[10px] font-black text-stone-400 uppercase tracking-widest">Catatan Nasabah</p>
            </div>
            <p class="text-sm font-medium text-stone-600 leading-relaxed">
              {{ detailRequest.deskripsi?.includes('|') ? (detailRequest.deskripsi.split('|')[2] || 'Tidak ada catatan tambahan.') : (detailRequest.deskripsi || 'Tidak ada catatan tambahan.') }}
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- ══════════════════════════════════════════════════════════════════════ -->
    <!-- Modal: Tolak Request                                                   -->
    <!-- ══════════════════════════════════════════════════════════════════════ -->
    <div v-if="isRejectModalOpen && rejectRequest" class="fixed inset-0 z-[100] flex items-center justify-center p-6">
      <div class="absolute inset-0 bg-stone-900/60 backdrop-blur-sm" @click="isRejectModalOpen = false"></div>
      <div class="relative bg-white w-full max-w-2xl rounded-[2.5rem] shadow-2xl flex flex-col max-h-[90vh] overflow-hidden animate-in fade-in zoom-in-95 duration-300">
        <div class="p-8 pb-6 flex justify-between items-start border-b border-stone-100">
          <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-red-50 flex items-center justify-center border border-red-100 shrink-0">
              <Icon icon="material-symbols:error-outline" class="w-6 h-6 text-red-500" />
            </div>
            <div>
              <h3 class="text-xl font-black text-stone-800">Tolak Request Penjemputan</h3>
              <p class="text-stone-500 font-medium text-sm mt-1">REQ-{{ String(rejectRequest.penjemputan_id).padStart(3, '0') }} - {{ rejectRequest.nasabah?.nama || 'Unknown' }}</p>
            </div>
          </div>
          <button @click="isRejectModalOpen = false" class="p-2.5 bg-stone-50 hover:bg-stone-200 rounded-xl transition-colors">
            <Icon icon="material-symbols:close" class="w-5 h-5 text-stone-500 font-bold" />
          </button>
        </div>
        <div class="flex-1 overflow-y-auto p-8 space-y-6 bg-[#FAFAFA]">
          <div class="bg-orange-50 border border-orange-200 rounded-2xl p-4 flex gap-3">
            <Icon icon="material-symbols:info-outline" class="w-5 h-5 text-orange-500 shrink-0 mt-0.5" />
            <p class="text-sm font-medium text-orange-800 leading-snug">
              Request yang ditolak akan masuk ke riwayat transaksi dan nasabah akan menerima notifikasi penolakan beserta alasannya.
            </p>
          </div>
          <div class="space-y-3">
            <label class="text-xs font-black text-stone-800">Alasan Penolakan <span class="text-red-500">*</span></label>
            <textarea
              v-model="rejectReason"
              placeholder="Jelaskan alasan penolakan request ini secara detail..."
              class="w-full bg-white border border-stone-200 rounded-2xl py-4 px-5 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition-all min-h-[120px] text-stone-700"
            ></textarea>
            <div class="flex items-center gap-2 mt-2">
              <Icon icon="material-symbols:lightbulb-outline" class="w-4 h-4 text-orange-400" />
              <p class="text-[10px] font-bold text-stone-500">Alasan yang jelas akan membantu nasabah memahami mengapa request ditolak</p>
            </div>
          </div>
        </div>
        <div class="p-6 border-t border-stone-100 bg-white flex gap-4">
          <button @click="isRejectModalOpen = false" class="flex-1 py-3.5 rounded-xl bg-[#F5F5F0] text-stone-600 font-black text-sm hover:bg-stone-200 transition-colors">
            Batal
          </button>
          <button @click="confirmReject" class="flex-1 py-3.5 rounded-xl bg-[#E57373] text-white font-black text-sm hover:bg-[#EF5350] transition-all shadow-md active:scale-[0.98]">
            Konfirmasi Penolakan
          </button>
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

::-webkit-scrollbar { width: 6px; }
::-webkit-scrollbar-track { background: transparent; }
::-webkit-scrollbar-thumb { background: #e2e2e2; border-radius: 10px; }
::-webkit-scrollbar-thumb:hover { background: #d1d1d1; }
</style>