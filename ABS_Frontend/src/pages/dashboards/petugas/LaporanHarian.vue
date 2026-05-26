<script setup>
import { ref, onMounted, computed } from "vue";
import { Icon } from "@iconify/vue";
import DashboardLayout from "@/layouts/DashboardLayout.vue";
import axios from 'axios'
import Swal from 'sweetalert2';

const token = sessionStorage.getItem('token')

if (!token) {
  throw new Error('Otentikasi diperlukan.')
}

const user = ref(JSON.parse(sessionStorage.getItem("user")));

const headers = { 'Authorization': `Bearer ${token}` }

const listSampah = ref([]);
const listGudang = ref([]);

const today = new Date()
const thirtyDaysAgo = new Date()
thirtyDaysAgo.setDate(today.getDate() - 30)

// Format ke YYYY-MM-DD
const formatDate = (date) => date.toISOString().split('T')[0]

const data = ref({
  start_date: formatDate(thirtyDaysAgo),
  end_date: formatDate(today),
  gudang_id: user.value.gudang_id,
  sampah: [],
})

const filterOptions = [
  { label: '7 Hari Terakhir', value: 7 },
  { label: '15 Hari Terakhir', value: 15 },
  { label: '30 Hari Terakhir', value: 30 },
]

const filterLabel = computed(() =>
  filterOptions.find(o => o.value === data.value.start_date)?.label || ''
)

const showPreview = ref(false)

const tanggalCetak = computed(() => {
  const now = new Date()
  return now.toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' })
    + ' pukul '
    + now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })
})

const addSampah = () => {
  data.value.sampah.push({ sampah_id: "" });
};

const removeSampah = (index) => {
  data.value.sampah.splice(index, 1);
};

const downloadExcel = async () => {
  try {
    const res = await axios.get(
      `/api/cetak-laporan/excel`,
      {
        headers,
        params: {
          start_date: data.value.start_date,
          end_date: data.value.end_date,
          gudang_id: data.value.gudang_id,
          sampah: data.value.sampah,
        },
        responseType: 'blob',
      }
    )
    const url = window.URL.createObjectURL(new Blob([res.data]))
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', 'transaksi.xlsx')
    document.body.appendChild(link)
    link.click()
    link.remove()
  } catch (err) {
    console.error(err)
  }
}

const previewPdf = async () => {
  try {
    const response = await axios.get(
      `/api/cetak-laporan/pdf`,
      {
        headers,
        params: {
          start_date: data.value.start_date,
          end_date: data.value.end_date,
          gudang_id: data.value.gudang_id,
          sampah: data.value.sampah,
        }
      }
    )

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
  } catch (err) {
    console.error(err)
  }
}

const fetchData = async () => {
  try {
    const response = await axios.get("http://localhost:8000/api/laporan/list-sampah", { headers });
    listSampah.value = response.data;

    const responseG = await axios.get("http://localhost:8000/api/laporan/list-gudang", { headers });
    listGudang.value = responseG.data;
  } catch (err) {
    console.error("Gagal mengambil data:", err);
  }
};

onMounted(async () => {
  fetchData();
});
</script>

<template>
  <DashboardLayout title="Laporan Harian">

    <!-- Filter Bar -->
    <div class="flex items-center justify-between mb-5">
      <div class="flex items-center gap-2 bg-white rounded-xl px-3 py-2 border border-stone-200 shadow-sm">
        <Icon icon="material-symbols:calendar-month-outline" class="w-4 h-4 text-stone-500" />

        <div>
          <label>Dari Tanggal: </label>
          <input type="date" v-model="data.start_date" />
        </div>
        
        <div>
          <label>Sampai Tanggal: </label>
          <input type="date" v-model="data.end_date" />
        </div>
        <!-- <button
          v-for="opt in filterOptions"
          :key="opt.value"
          :class="[
            'px-3 py-1.5 rounded-lg text-sm font-medium transition-all',
            data.start_date === opt.value
              ? 'bg-[#2d4a3e] text-white'
              : 'text-stone-500 hover:bg-stone-100'
          ]"
        >
          {{ opt.label }}
        </button> -->

      </div>

      <button
        @click="showPreview = true"
        class="flex items-center gap-2 bg-[#2d4a3e] text-white text-sm font-medium px-4 py-2.5 rounded-xl hover:bg-[#1f3529] transition-all"
      >
        <Icon icon="material-symbols:description-outline" class="w-4 h-4" />
        Lihat &amp; Cetak Laporan
      </button>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-4 gap-4 mb-5">
      <div class="relative rounded-2xl p-5 bg-[#2d6a4f] text-white">
        <div class="flex items-start justify-between mb-6">
          <div class="w-9 h-9 rounded-xl bg-white/15 flex items-center justify-center">
            <Icon icon="material-symbols:package-2-outline" class="w-5 h-5" />
          </div>
          <Icon icon="material-symbols:trending-up" class="w-5 h-5 opacity-70" />
        </div>
        <p class="text-3xl font-bold leading-none mb-1">4</p>
        <p class="text-xs text-white/70 mb-4">Penjemputan Selesai</p>
        <div class="border-t border-white/20 pt-3">
          <p class="text-xs text-white/60 mb-1">Total Nilai</p>
          <p class="text-base font-semibold">Rp 123.100</p>
        </div>
      </div>

      <div class="relative rounded-2xl p-5 bg-[#7a4419] text-white">
        <div class="flex items-start justify-between mb-6">
          <div class="w-9 h-9 rounded-xl bg-white/15 flex items-center justify-center">
            <Icon icon="material-symbols:store-outline" class="w-5 h-5" />
          </div>
          <Icon icon="material-symbols:trending-up" class="w-5 h-5 opacity-70" />
        </div>
        <p class="text-3xl font-bold leading-none mb-1">3</p>
        <p class="text-xs text-white/70 mb-4">Setor Manual</p>
        <div class="border-t border-white/20 pt-3">
          <p class="text-xs text-white/60 mb-1">Total Nilai</p>
          <p class="text-base font-semibold">Rp 103.500</p>
        </div>
      </div>

      <div class="relative rounded-2xl p-5 bg-[#1a7a6e] text-white">
        <div class="flex items-start justify-between mb-6">
          <div class="w-9 h-9 rounded-xl bg-white/15 flex items-center justify-center">
            <Icon icon="material-symbols:credit-card-outline" class="w-5 h-5" />
          </div>
          <Icon icon="material-symbols:trending-up" class="w-5 h-5 opacity-70" />
        </div>
        <p class="text-3xl font-bold leading-none mb-1">2</p>
        <p class="text-xs text-white/70 mb-4">Penarikan Disetujui</p>
        <div class="border-t border-white/20 pt-3">
          <p class="text-xs text-white/60 mb-1">Total Nominal</p>
          <p class="text-base font-semibold">Rp 350.000</p>
        </div>
      </div>

      <div class="relative rounded-2xl p-5 bg-[#4a4a4a] text-white">
        <div class="flex items-start justify-between mb-6">
          <div class="w-9 h-9 rounded-xl bg-white/15 flex items-center justify-center">
            <Icon icon="material-symbols:receipt-outline" class="w-5 h-5" />
          </div>
          <Icon icon="material-symbols:trending-up" class="w-5 h-5 opacity-70" />
        </div>
        <p class="text-3xl font-bold leading-none mb-1">9</p>
        <p class="text-xs text-white/70 mb-4">Pesanan Pengepul</p>
        <div class="border-t border-white/20 pt-3">
          <p class="text-xs text-white/60 mb-1">Total Nilai</p>
          <p class="text-base font-semibold">Rp 23.500</p>
        </div>
      </div>
    </div>

    <!-- Info Bar -->
    <div class="flex items-center justify-between bg-[#eaf4ee] rounded-2xl px-5 py-4">
      <div>
        <p class="text-sm font-semibold text-[#2d4a3e] mb-0.5">Butuh detail transaksi per item?</p>
        <p class="text-xs text-[#5f8a72]">Riwayat lengkap, foto bukti, dan filter tersedia di halaman Riwayat.</p>
      </div>
      <button class="flex items-center gap-2 bg-white border border-[#c8dfd2] text-[#2d4a3e] text-sm font-medium px-4 py-2 rounded-xl hover:bg-stone-50 transition-all">
        Buka Riwayat
        <Icon icon="material-symbols:open-in-new" class="w-4 h-4" />
      </button>
    </div>

    <!-- Modal Preview -->
    <Teleport to="body">
      <Transition name="fade">
        <div
          v-if="showPreview"
          class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-6"
          @click.self="showPreview = false"
        >
          <div class="bg-white rounded-3xl shadow-2xl w-full max-w-2xl max-h-[90vh] flex flex-col overflow-hidden">

            <!-- Header -->
            <div class="flex items-center justify-between px-8 pt-7 pb-6 border-b border-[#eaf4ee]">
              <div>
                <h2 class="text-lg font-semibold text-stone-800 mb-1">Preview laporan harian</h2>
                <p class="text-xs text-[#7aab8e]">{{ filterLabel }}</p>
              </div>
              <div class="flex items-center gap-3">
                <button
                  @click="downloadExcel"
                  class="flex items-center gap-2 border border-[#2d4a3e] text-[#2d4a3e] text-sm font-medium px-4 py-2 rounded-xl hover:bg-[#eaf4ee] transition-all"
                >
                  <Icon icon="material-symbols:table-view-outline" class="w-4 h-4" />
                  Excel
                </button>
                <button
                  @click="previewPdf"
                  class="flex items-center gap-2 bg-[#2d4a3e] text-white text-sm font-medium px-4 py-2 rounded-xl hover:bg-[#1f3529] transition-all"
                >
                  <Icon icon="material-symbols:print-outline" class="w-4 h-4" />
                  Cetak PDF
                </button>
                <button
                  @click="showPreview = false"
                  class="w-9 h-9 flex items-center justify-center rounded-xl border border-[#c8dfd2] text-[#7aab8e] hover:bg-[#eaf4ee] transition-all"
                >
                  <Icon icon="material-symbols:close" class="w-4 h-4" />
                </button>
              </div>
            </div>

            <!-- Body -->
            <div class="overflow-y-auto flex-1 px-8 py-6">

              <!-- Petugas & Gudang -->
              <div class="grid grid-cols-2 gap-6 mb-7 pb-6 border-b border-[#eaf4ee]">
                <div>
                  <p class="text-xs text-[#7aab8e] mb-1.5">Nama petugas</p>
                  <p class="text-sm font-semibold text-stone-700">Budi Santoso</p>
                </div>
                <div>
                  <p class="text-xs text-[#7aab8e] mb-1.5">Lokasi gudang</p>
                  <p class="text-sm font-semibold text-stone-700">Gudang 001</p>
                </div>
              </div>

              <!-- Summary Cards -->
              <div class="grid grid-cols-4 gap-3 mb-8">
                <div class="bg-[#eaf4ee] rounded-2xl p-4">
                  <p class="text-xs text-[#4a7a60] mb-2">Total transaksi</p>
                  <p class="text-3xl font-semibold text-[#1f3529] leading-none">11</p>
                </div>
                <div class="bg-[#eaf4ee] rounded-2xl p-4">
                  <p class="text-xs text-[#4a7a60] mb-2">Total berat</p>
                  <p class="text-3xl font-semibold text-[#1f3529] leading-none">224<span class="text-sm font-normal text-[#4a7a60]">.3 kg</span></p>
                </div>
                <div class="bg-[#eaf4ee] rounded-2xl p-4">
                  <p class="text-xs text-[#4a7a60] mb-2">Total nilai</p>
                  <p class="text-lg font-semibold text-[#1f3529] leading-snug">Rp<br>602.100</p>
                </div>
                <div class="bg-[#eaf4ee] rounded-2xl p-4">
                  <p class="text-xs text-[#4a7a60] mb-2">Kategori aktif</p>
                  <p class="text-3xl font-semibold text-[#1f3529] leading-none">4</p>
                </div>
              </div>

              <!-- Ringkasan -->
              <p class="text-sm font-semibold text-stone-700 mb-4">Ringkasan per kategori</p>
              <div class="grid grid-cols-2 gap-3 mb-8">
                <div class="border border-[#c8dfd2] rounded-2xl p-5">
                  <p class="text-xs text-[#4a7a60] mb-3">Penjemputan selesai</p>
                  <p class="mb-1.5 leading-none">
                    <span class="text-3xl font-semibold text-[#2d4a3e]">4</span>
                    <span class="text-xs text-[#7aab8e] ml-2">transaksi</span>
                  </p>
                  <p class="text-xs text-[#7aab8e]">38.7 kg · Rp 125.100</p>
                </div>
                <div class="border border-[#c8dfd2] rounded-2xl p-5">
                  <p class="text-xs text-[#4a7a60] mb-3">Setor manual</p>
                  <p class="mb-1.5 leading-none">
                    <span class="text-3xl font-semibold text-[#2d4a3e]">3</span>
                    <span class="text-xs text-[#7aab8e] ml-2">transaksi</span>
                  </p>
                  <p class="text-xs text-[#7aab8e]">22.6 kg · Rp 103.500</p>
                </div>
                <div class="border border-[#c8dfd2] rounded-2xl p-5">
                  <p class="text-xs text-[#4a7a60] mb-3">Penarikan distribusi</p>
                  <p class="mb-1.5 leading-none">
                    <span class="text-3xl font-semibold text-[#2d4a3e]">2</span>
                    <span class="text-xs text-[#7aab8e] ml-2">transaksi</span>
                  </p>
                  <p class="text-xs text-[#7aab8e]">Rp 350.000</p>
                </div>
                <div class="border border-[#c8dfd2] rounded-2xl p-5">
                  <p class="text-xs text-[#4a7a60] mb-3">Pesanan pengepul</p>
                  <p class="mb-1.5 leading-none">
                    <span class="text-3xl font-semibold text-[#2d4a3e]">2</span>
                    <span class="text-xs text-[#7aab8e] ml-2">transaksi</span>
                  </p>
                  <p class="text-xs text-[#7aab8e]">165.0 kg · Rp 23.500</p>
                </div>
              </div>

              <!-- Tabel -->
              <p class="text-sm font-semibold text-stone-700 mb-4">Data per kategori</p>
              <table class="w-full text-sm mb-7" style="border-collapse:collapse;table-layout:fixed;">
                <thead>
                  <tr class="border-b border-[#c8dfd2]">
                    <th class="text-left pb-3 text-xs font-medium text-[#4a7a60]" style="width:38%;">Kategori</th>
                    <th class="text-center pb-3 text-xs font-medium text-[#4a7a60]" style="width:18%;">Transaksi</th>
                    <th class="text-right pb-3 text-xs font-medium text-[#4a7a60]" style="width:22%;">Total berat</th>
                    <th class="text-right pb-3 text-xs font-medium text-[#4a7a60]" style="width:22%;">Total nilai</th>
                  </tr>
                </thead>
                <tbody>
                  <tr class="border-b border-[#eaf4ee]">
                    <td class="py-3.5 text-stone-700 font-medium">Penjemputan selesai</td>
                    <td class="py-3.5 text-center">
                      <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-[#2d4a3e] text-white text-xs font-semibold">4</span>
                    </td>
                    <td class="py-3.5 text-right text-[#4a7a60]">36.7 kg</td>
                    <td class="py-3.5 text-right text-[#4a7a60]">Rp 125.100</td>
                  </tr>
                  <tr class="border-b border-[#eaf4ee]">
                    <td class="py-3.5 text-stone-700 font-medium">Setor manual</td>
                    <td class="py-3.5 text-center">
                      <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-[#3d6b54] text-white text-xs font-semibold">3</span>
                    </td>
                    <td class="py-3.5 text-right text-[#4a7a60]">22.6 kg</td>
                    <td class="py-3.5 text-right text-[#4a7a60]">Rp 103.500</td>
                  </tr>
                  <tr class="border-b border-[#eaf4ee]">
                    <td class="py-3.5 text-stone-700 font-medium">Penarikan distribusi</td>
                    <td class="py-3.5 text-center">
                      <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-[#4f8a6a] text-white text-xs font-semibold">2</span>
                    </td>
                    <td class="py-3.5 text-right text-[#7aab8e]">—</td>
                    <td class="py-3.5 text-right text-[#4a7a60]">Rp 350.000</td>
                  </tr>
                  <tr>
                    <td class="py-3.5 text-stone-700 font-medium">Pesanan pengepul</td>
                    <td class="py-3.5 text-center">
                      <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-[#6aaa85] text-white text-xs font-semibold">2</span>
                    </td>
                    <td class="py-3.5 text-right text-[#4a7a60]">165.0 kg</td>
                    <td class="py-3.5 text-right text-[#4a7a60]">Rp 23.500</td>
                  </tr>
                </tbody>
              </table>

              <!-- Catatan -->
              <div class="bg-[#eaf4ee] border border-[#c8dfd2] rounded-2xl px-5 py-4 flex gap-3 items-start">
                <Icon icon="material-symbols:info-outline" class="w-4 h-4 text-[#2d4a3e] mt-0.5 shrink-0" />
                <p class="text-xs text-[#2d4a3e] leading-relaxed">
                  Laporan ini merupakan ringkasan transaksi pada periode {{ data.start_date }} hari terakhir.
                  Detail tiap transaksi (foto bukti, alamat, catatan nasabah) dapat dilihat pada halaman Riwayat.
                </p>
              </div>
            </div>

            <!-- Footer -->
            <div class="px-8 py-4 border-t border-[#eaf4ee] flex items-center justify-between">
              <p class="text-xs text-[#7aab8e]">Digenerate pada: {{ tanggalCetak }}</p>
              <p class="text-xs text-[#7aab8e]">Bank Sampah Management System</p>
            </div>

          </div>
        </div>
      </Transition>
    </Teleport>

  </DashboardLayout>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>