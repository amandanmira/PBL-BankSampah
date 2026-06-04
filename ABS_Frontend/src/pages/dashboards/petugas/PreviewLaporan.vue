<script setup>
import { ref, onMounted, computed } from "vue";
import { useRoute, useRouter } from "vue-router";
import axios from 'axios';
import { Icon } from "@iconify/vue";

const route = useRoute();
const router = useRouter();
const token = localStorage.getItem('token');

if (!token) {
  router.push('/login');
}

const user = ref(JSON.parse(localStorage.getItem("user")) || {});
const headers = { 'Authorization': `Bearer ${token}` };

const startDate = route.query.start_date;
const endDate = route.query.end_date;
const gudangId = route.query.gudang_id || user.value.gudang_id;

const summaryLaporan = ref({});
const detailsLaporan = ref({
  penjemputan: [],
  setor_manual: [],
  penarikan: [],
  pesanan_pengepul: []
});

const webConfig = ref({ logo: null });
const listGudang = ref([]);

const gudangName = computed(() => {
  if (user.value.gudang?.alamat) return user.value.gudang.alamat;
  if (user.value.gudang?.nama) return user.value.gudang.nama;
  const gudang = listGudang.value.find(g => g.id == gudangId || g.gudang_id == gudangId);
  return gudang ? (gudang.alamat || gudang.nama) : '-';
});

const isLoading = ref(true);

const fetchData = async () => {
  isLoading.value = true;
  try {
    const responseSummary = await axios.get(`${axios.defaults.baseURL}/api/petugas/summary-laporan`, {
      headers,
      params: {
        start_date: startDate,
        end_date: endDate,
        gudang_id: gudangId
      }
    });
    summaryLaporan.value = responseSummary.data.data;
    detailsLaporan.value = responseSummary.data.details || {
      penjemputan: [],
      setor_manual: [],
      penarikan: [],
      pesanan_pengepul: []
    };
    
    // Fetch Web Config for Logo
    const resConfig = await axios.get(`${axios.defaults.baseURL}/api/web-config`);
    webConfig.value = resConfig.data;

    // Fetch Gudang to get name
    const resGudang = await axios.get(`${axios.defaults.baseURL}/api/laporan/list-gudang`, { headers });
    listGudang.value = resGudang.data;

  } catch (err) {
    console.error("Gagal mengambil data:", err);
  } finally {
    isLoading.value = false;
  }
};

onMounted(() => {
  fetchData();
});

// Format Utilities
const formatDateDisplay = (dateStr) => {
  if (!dateStr) return '';
  const date = new Date(dateStr);
  return date.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
};

const formatDateTime = (dateStr) => {
  if (!dateStr) return '';
  const date = new Date(dateStr);
  return date.toLocaleString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
};

const formatRupiah = (angka) => {
  return new Intl.NumberFormat("id-ID", {
    style: "currency",
    currency: "IDR",
    minimumFractionDigits: 0,
  }).format(angka || 0);
};

// Computed Properties for the Report
const periodeLaporan = computed(() => {
  if (startDate === endDate) {
    return formatDateDisplay(startDate);
  }
  return `${formatDateDisplay(startDate)} - ${formatDateDisplay(endDate)}`;
});

const totalSampahMasuk = computed(() => {
  return Number(summaryLaporan.value.penjemputan_berat || 0) + Number(summaryLaporan.value.setor_berat || 0);
});

const totalSampahKeluar = computed(() => {
  return Number(summaryLaporan.value.pengepul_berat || 0);
});

const totalTransaksi = computed(() => {
  return Number(summaryLaporan.value.total_transaksi || 0);
});

const uangKeluarPembelian = computed(() => {
  return Number(summaryLaporan.value.penjemputan_harga || 0) + Number(summaryLaporan.value.setor_harga || 0);
});

const uangKeluarPenarikan = computed(() => {
  return Number(summaryLaporan.value.penarikan_harga || 0);
});

const totalUangKeluar = computed(() => {
  return uangKeluarPembelian.value + uangKeluarPenarikan.value;
});

const uangMasukPenjualan = computed(() => {
  return Number(summaryLaporan.value.pengepul_harga || 0);
});

const statusKas = computed(() => {
  return uangMasukPenjualan.value - totalUangKeluar.value;
});

// Mapping Details
const rincianSampahMasuk = computed(() => {
  const p = detailsLaporan.value.penjemputan.map(item => ({
    tanggal: item.created_at,
    tipe: 'Penjemputan',
    nasabah: item.penimbangan?.[0]?.nasabah?.nama || '-',
    berat: item.penimbangan?.reduce((acc, x) => acc + (x.berat_timbang || 0), 0) || 0,
    total_harga: item.total_harga || 0
  }));
  
  const s = detailsLaporan.value.setor_manual.map(item => ({
    tanggal: item.created_at,
    tipe: 'Setor Manual',
    nasabah: item.penimbangan?.[0]?.nasabah?.nama || '-',
    berat: item.penimbangan?.reduce((acc, x) => acc + (x.berat_timbang || 0), 0) || 0,
    total_harga: item.total_harga || 0
  }));

  const combined = [...p, ...s];
  // Sort by date descending
  return combined.sort((a, b) => new Date(b.tanggal) - new Date(a.tanggal));
});

const rincianPenjualan = computed(() => {
  return detailsLaporan.value.pesanan_pengepul.map(item => ({
    tanggal: item.created_at,
    pengepul: item.pengepul?.nama || item.pengepul?.nama_lembaga || '-',
    jenis_sampah: item.detail_transaksi?.map(d => d.sampah?.item_sampah?.nama).join(', ') || '-',
    berat: item.detail_transaksi?.reduce((acc, d) => acc + (d.berat || 0), 0) || 0,
    total_pendapatan: item.detail_transaksi?.reduce((acc, d) => acc + (d.berat * d.harga || 0), 0) || item.total_harga || 0
  })).sort((a, b) => new Date(b.tanggal) - new Date(a.tanggal));
});

const rincianPenarikan = computed(() => {
  return detailsLaporan.value.penarikan.map(item => ({
    tanggal: item.created_at,
    nasabah: item.nasabah?.nama || '-',
    nominal: item.jumlah || 0
  })).sort((a, b) => new Date(b.tanggal) - new Date(a.tanggal));
});

const handlePrint = () => {
  window.print();
};

const goBack = () => {
  router.back();
};

</script>

<template>
  <div class="min-h-screen bg-stone-100 py-8 print:py-0 print:bg-white font-sans text-stone-800">
    <!-- Action Bar (Not visible in print) -->
    <div class="max-w-[800px] mx-auto mb-6 flex justify-between items-center px-4 print:hidden">
      <button 
        @click="goBack"
        class="flex items-center gap-2 bg-white border border-stone-200 text-stone-600 px-4 py-2 rounded-xl text-sm font-semibold hover:bg-stone-50 transition-colors shadow-sm"
      >
        <Icon icon="material-symbols:arrow-back" class="w-4 h-4" />
        Kembali
      </button>
      
      <button 
        @click="handlePrint"
        class="flex items-center gap-2 bg-[#4A7043] text-white px-4 py-2 rounded-xl text-sm font-semibold hover:bg-[#3D5C37] transition-colors shadow-sm"
      >
        <Icon icon="material-symbols:print-outline" class="w-4 h-4" />
        Cetak / Simpan PDF
      </button>
    </div>

    <!-- Paper Document -->
    <div class="max-w-[800px] mx-auto bg-white p-10 md:p-12 shadow-xl print:shadow-none print:p-0 print:max-w-full w-full min-h-[1056px] print-document">
      
      <div v-if="isLoading" class="flex justify-center items-center py-20 text-stone-400">
        <Icon icon="mdi:loading" class="w-8 h-8 animate-spin" />
        <span class="ml-2 font-medium">Memuat Laporan...</span>
      </div>
      
      <div v-else>
        <!-- Header -->
        <div class="flex justify-between items-start border-b-2 border-stone-100 pb-6 mb-8">
          <div class="flex items-center gap-4">
            <div class="w-14 h-14 bg-white rounded-lg flex items-center justify-center text-stone-800 shrink-0 overflow-hidden">
              <img v-if="webConfig.logo" :src="`${axios.defaults.baseURL}/storage/${webConfig.logo}`" class="w-full h-full object-contain" alt="Logo" />
              <Icon v-else icon="material-symbols:eco" class="w-8 h-8 text-[#4A7043]" />
            </div>
            <div>
              <h1 class="text-xl font-bold text-stone-800 leading-tight">Aplikasi<br>Bank<br>Sampah</h1>
            </div>
          </div>
          
          <div class="text-right">
            <h2 class="text-lg font-black text-stone-800 uppercase tracking-wide mb-2">LAPORAN OPERASIONAL & KEUANGAN</h2>
            <table class="text-xs text-stone-500 ml-auto text-right">
              <tbody>
                <tr>
                  <td class="pr-2">Periode Laporan:</td>
                  <td class="font-bold text-stone-800">{{ periodeLaporan }}</td>
                </tr>
                <tr>
                  <td class="pr-2">Nama Petugas:</td>
                  <td class="font-bold text-stone-800">{{ user.nama || user.name || 'Admin' }}</td>
                </tr>
                <tr>
                  <td class="pr-2">Lokasi Gudang:</td>
                  <td class="font-bold text-stone-800">{{ gudangName }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-4 gap-4 mb-8">
          <div class="border border-stone-200 rounded-lg p-4">
            <div class="text-[10px] uppercase font-bold text-stone-400 mb-1">Total Sampah Masuk</div>
            <div class="text-xl font-bold text-stone-800">{{ totalSampahMasuk }} Kg</div>
          </div>
          <div class="border border-stone-200 rounded-lg p-4">
            <div class="text-[10px] uppercase font-bold text-stone-400 mb-1">Total Sampah Keluar</div>
            <div class="text-xl font-bold text-stone-800">{{ totalSampahKeluar }} Kg</div>
          </div>
          <div class="border border-stone-200 rounded-lg p-4">
            <div class="text-[10px] uppercase font-bold text-stone-400 mb-1">Total Transaksi</div>
            <div class="text-xl font-bold text-stone-800">{{ totalTransaksi }} <span class="text-xs font-medium">Transaksi</span></div>
          </div>
          <div class="border border-stone-200 rounded-lg p-4">
            <div class="text-[10px] uppercase font-bold text-stone-400 mb-1">Status Kas</div>
            <div class="text-xl font-bold" :class="statusKas >= 0 ? 'text-green-600' : 'text-red-600'">
              {{ statusKas > 0 ? '+' : '' }} {{ formatRupiah(statusKas) }}
            </div>
          </div>
        </div>

        <!-- Ringkasan Arus Kas -->
        <div class="border border-stone-200 rounded-lg overflow-hidden mb-8">
          <div class="bg-stone-50 px-4 py-2 border-b border-stone-200">
            <h3 class="text-xs font-bold text-stone-700 uppercase">RINGKASAN ARUS KAS</h3>
          </div>
          <div class="grid grid-cols-2 divide-x divide-stone-200">
            <!-- UANG KELUAR -->
            <div class="p-4">
              <h4 class="text-[10px] font-bold text-stone-400 uppercase mb-3">UANG KELUAR</h4>
              <div class="space-y-2 mb-4">
                <div class="flex justify-between text-xs">
                  <span class="text-stone-600">Pembelian Sampah (Nasabah)</span>
                  <span class="font-semibold text-stone-800">{{ formatRupiah(uangKeluarPembelian) }}</span>
                </div>
                <div class="flex justify-between text-xs">
                  <span class="text-stone-600">Penarikan Saldo Nasabah</span>
                  <span class="font-semibold text-stone-800">{{ formatRupiah(uangKeluarPenarikan) }}</span>
                </div>
              </div>
              <div class="flex justify-between text-sm font-bold pt-2 border-t border-stone-100">
                <span>Total Keluar</span>
                <span class="text-red-600">{{ formatRupiah(totalUangKeluar) }}</span>
              </div>
            </div>

            <!-- UANG MASUK -->
            <div class="p-4 flex flex-col">
              <h4 class="text-[10px] font-bold text-stone-400 uppercase mb-3">UANG MASUK</h4>
              <div class="space-y-2 flex-grow">
                <div class="flex justify-between text-xs">
                  <span class="text-stone-600">Penjualan ke Pengepul</span>
                  <span class="font-semibold text-stone-800">{{ formatRupiah(uangMasukPenjualan) }}</span>
                </div>
              </div>
              <div class="flex justify-between text-sm font-bold pt-2 border-t border-stone-100 mt-4">
                <span>Total Masuk</span>
                <span class="text-green-600">{{ formatRupiah(uangMasukPenjualan) }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Rincian Sampah Masuk -->
        <div class="mb-8">
          <h3 class="text-sm font-bold text-stone-800 mb-3">Rincian Sampah Masuk (Nasabah)</h3>
          <div class="border border-stone-200 rounded-lg overflow-hidden">
            <table class="w-full text-left text-xs">
              <thead class="bg-stone-50 border-b border-stone-200">
                <tr>
                  <th class="py-2 px-3 font-bold text-stone-700">Tanggal</th>
                  <th class="py-2 px-3 font-bold text-stone-700">Tipe Transaksi</th>
                  <th class="py-2 px-3 font-bold text-stone-700">Nama Nasabah</th>
                  <th class="py-2 px-3 font-bold text-stone-700 text-right">Berat (Kg)</th>
                  <th class="py-2 px-3 font-bold text-stone-700 text-right">Total Harga (Rp)</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-stone-100">
                <tr v-if="!rincianSampahMasuk.length">
                  <td colspan="5" class="py-4 text-center text-stone-400">Tidak ada data</td>
                </tr>
                <tr v-for="(item, index) in rincianSampahMasuk" :key="index">
                  <td class="py-2 px-3 text-stone-600">{{ formatDateDisplay(item.tanggal) }}</td>
                  <td class="py-2 px-3 text-stone-600">{{ item.tipe }}</td>
                  <td class="py-2 px-3 text-stone-600">{{ item.nasabah }}</td>
                  <td class="py-2 px-3 text-stone-800 text-right">{{ item.berat }}</td>
                  <td class="py-2 px-3 text-stone-800 text-right">{{ new Intl.NumberFormat("id-ID").format(item.total_harga) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Rincian Penjualan -->
        <div class="mb-8">
          <h3 class="text-sm font-bold text-stone-800 mb-3">Rincian Penjualan (Pengepul)</h3>
          <div class="border border-stone-200 rounded-lg overflow-hidden">
            <table class="w-full text-left text-xs">
              <thead class="bg-stone-50 border-b border-stone-200">
                <tr>
                  <th class="py-2 px-3 font-bold text-stone-700">Tanggal</th>
                  <th class="py-2 px-3 font-bold text-stone-700">Nama Pengepul</th>
                  <th class="py-2 px-3 font-bold text-stone-700">Jenis Sampah</th>
                  <th class="py-2 px-3 font-bold text-stone-700 text-right">Berat (Kg)</th>
                  <th class="py-2 px-3 font-bold text-stone-700 text-right">Total Pendapatan (Rp)</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-stone-100">
                <tr v-if="!rincianPenjualan.length">
                  <td colspan="5" class="py-4 text-center text-stone-400">Tidak ada data</td>
                </tr>
                <tr v-for="(item, index) in rincianPenjualan" :key="index">
                  <td class="py-2 px-3 text-stone-600">{{ formatDateDisplay(item.tanggal) }}</td>
                  <td class="py-2 px-3 text-stone-600">{{ item.pengepul }}</td>
                  <td class="py-2 px-3 text-stone-600 max-w-[200px] truncate">{{ item.jenis_sampah }}</td>
                  <td class="py-2 px-3 text-stone-800 text-right">{{ item.berat }}</td>
                  <td class="py-2 px-3 text-stone-800 text-right">{{ new Intl.NumberFormat("id-ID").format(item.total_pendapatan) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Rincian Penarikan -->
        <div class="mb-16">
          <h3 class="text-sm font-bold text-stone-800 mb-3">Rincian Penarikan Saldo</h3>
          <div class="border border-stone-200 rounded-lg overflow-hidden">
            <table class="w-full text-left text-xs">
              <thead class="bg-stone-50 border-b border-stone-200">
                <tr>
                  <th class="py-2 px-3 font-bold text-stone-700">Tanggal</th>
                  <th class="py-2 px-3 font-bold text-stone-700">Nama Nasabah</th>
                  <th class="py-2 px-3 font-bold text-stone-700 text-right">Nominal Tarik (Rp)</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-stone-100">
                <tr v-if="!rincianPenarikan.length">
                  <td colspan="3" class="py-4 text-center text-stone-400">Tidak ada data</td>
                </tr>
                <tr v-for="(item, index) in rincianPenarikan" :key="index">
                  <td class="py-2 px-3 text-stone-600">{{ formatDateDisplay(item.tanggal) }}</td>
                  <td class="py-2 px-3 text-stone-600">{{ item.nasabah }}</td>
                  <td class="py-2 px-3 text-stone-800 text-right">{{ new Intl.NumberFormat("id-ID").format(item.nominal) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Signatures -->
        <div class="grid grid-cols-2 text-center text-xs text-stone-700 mt-16 pt-8">
          <div>
            <p class="mb-20">Mengetahui,</p>
            <div class="inline-block border-t border-stone-400 pt-2 font-bold w-48">
              ( Manager Bank Sampah )
            </div>
          </div>
          <div>
            <p class="mb-20">Dibuat Oleh,</p>
            <div class="inline-block border-t border-stone-400 pt-2 font-bold w-48">
              ( Petugas )
            </div>
          </div>
        </div>
        
      </div>
    </div>
  </div>
</template>

<style scoped>
@media print {
  @page {
    margin: 1.5cm;
    size: A4 portrait;
  }
  body {
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
    background-color: white !important;
  }
  /* Ensure no overflow */
  .print-document {
    width: 100% !important;
    max-width: 100% !important;
    overflow: hidden !important;
    box-sizing: border-box !important;
  }
}
</style>
