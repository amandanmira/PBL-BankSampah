<template>
  <DashboardLayout :title="step === 1 ? 'Dashboard Penimbangan' : step === 2 ? 'Form Detail Penimbangan' : 'Simpan Penimbangan Nasabah'">
    
    <!-- STEP 1: PILIH NASABAH -->
    <div v-if="step === 1" class="max-w-3xl mx-auto pb-20 animate-in fade-in duration-500">
      <div class="mb-6">
        <h1 class="text-3xl font-black text-stone-800">Dashboard Penimbangan</h1>
        <p class="text-stone-500 font-medium mt-1">Pilih Nasabah Untuk Mengisi Form Penimbangan</p>
      </div>

      <div class="bg-white rounded-[2rem] p-8 shadow-sm border border-stone-100">
        <h2 class="text-lg font-black text-stone-800 mb-4">Cari Nasabah</h2>
        
        <div class="relative flex mb-6">
          <input 
            v-model="searchQuery" 
            type="text" 
            placeholder="Nama Nasabah atau ID..." 
            class="flex-1 bg-[#F9F9F7] border border-stone-200 rounded-l-[1rem] py-3.5 px-5 text-sm font-medium text-stone-700 focus:outline-none focus:border-[#4A7043] transition-colors"
          />
          <button class="bg-[#4A7043] text-white px-6 rounded-r-[1rem] hover:bg-[#3D5C37] transition-colors flex items-center justify-center shadow-sm">
            <Icon icon="material-symbols:search" class="w-6 h-6" />
          </button>
        </div>

        <div class="space-y-4">
          <div 
            v-for="n in filteredNasabahList" 
            :key="n.nasabah_id"
            @click="selectNasabahAndProceed(n)"
            class="bg-[#F9F9F7] border border-stone-200 rounded-[1.5rem] p-5 flex justify-between items-center cursor-pointer hover:border-[#4A7043] transition-all group"
          >
            <div>
              <div class="flex items-center gap-2 mb-1.5">
                <h3 class="text-lg font-black text-stone-800 group-hover:text-[#4A7043] transition-colors">{{ n.nama }}</h3>
                <span class="bg-[#5C8554] text-white text-[10px] font-bold px-2 py-0.5 rounded-full tracking-wider">NSB-{{ String(n.nasabah_id).padStart(3, '0') }}</span>
              </div>
              <p class="text-sm font-medium text-stone-500">{{ n.alamat || '-' }}</p>
              <p class="text-sm font-medium text-stone-500">{{ n.no_telp || '-' }}</p>
            </div>
            <div class="text-right">
              <p class="text-[11px] font-bold text-stone-400">Saldo Saat Ini</p>
              <p class="text-lg font-black text-[#4A7043]">{{ formatRupiah(Number(n.saldo)) }}</p>
            </div>
          </div>
          
          <div v-if="filteredNasabahList.length === 0" class="text-center py-10">
            <p class="text-stone-500 font-medium">Tidak ada nasabah yang ditemukan.</p>
          </div>
        </div>
      </div>
    </div>

    <!-- STEP 2: FORM PENIMBANGAN -->
    <div v-else-if="step === 2" class="max-w-3xl mx-auto space-y-6 pb-20 animate-in fade-in duration-500">
      <div class="flex items-center gap-4">
        <button @click="step = 1" class="w-10 h-10 rounded-full bg-white shadow-sm border border-stone-100 flex items-center justify-center text-[#4A7043] hover:bg-[#4A7043] hover:text-white transition-all">
          <Icon icon="material-symbols:arrow-back" class="w-6 h-6" />
        </button>
        <div>
          <h2 class="text-2xl font-black text-stone-800">Form Detail Penimbangan</h2>
          <p class="text-stone-500 font-medium">Input Detail Penimbangan Dari Transaksi Nasabah</p>
        </div>
      </div>

      <!-- Nasabah Card Summary -->
      <div class="bg-[#4A7043] rounded-[1.5rem] p-6 text-white shadow-lg flex items-center gap-4">
        <div class="w-12 h-12 rounded-full bg-white/20 flex items-center justify-center shrink-0">
          <Icon icon="material-symbols:person-outline" class="w-6 h-6" />
        </div>
        <div class="flex-1">
          <p class="text-white/70 text-xs font-bold mb-1">Nama Nasabah:</p>
          <h3 class="text-xl font-black">{{ selectedNasabahData?.nama }} <span class="text-white/60 text-base font-bold">(NSB-{{ String(selectedNasabahData?.nasabah_id).padStart(3, '0') }})</span></h3>
          <p class="text-white/60 text-xs font-medium">{{ selectedNasabahData?.alamat || '-' }}</p>
        </div>
        <div class="text-right">
          <p class="text-white/70 text-xs font-bold mb-1">Saldo Saat Ini:</p>
          <p class="text-lg font-black">{{ formatRupiah(Number(selectedNasabahData?.saldo)) }}</p>
        </div>
      </div>

      <!-- Tukang Picker -->
      <div class="bg-white rounded-[1.5rem] p-6 shadow-sm border border-stone-100 space-y-3">
        <p class="text-sm font-black text-[#4A7043]">Pilih Tukang / Petugas Timbang: <span class="text-red-500">*</span></p>
        <div class="relative">
          <select v-model="selectedTukang" class="w-full bg-[#F5F7F5] border border-stone-200 rounded-xl py-3.5 px-4 text-sm font-bold text-stone-700 focus:outline-none appearance-none">
            <option value="" disabled>-- Pilih Tukang --</option>
            <option v-for="t in listTukang" :key="t.tukang_id" :value="t.tukang_id">{{ t.nama }}</option>
          </select>
          <Icon icon="material-symbols:arrow-drop-down" class="absolute right-3 top-1/2 -translate-y-1/2 w-6 h-6 text-stone-400 pointer-events-none" />
        </div>
      </div>

      <!-- Detail Sampah Form -->
      <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-stone-100">
        <div class="flex justify-between items-center mb-6">
          <h3 class="text-lg font-black text-stone-800">Detail Sampah</h3>
          <button @click="addRow" class="bg-[#4A7043] text-white px-4 py-2 rounded-xl text-xs font-bold hover:bg-[#3D5C37] transition-all flex items-center gap-1.5 shadow-sm">
            <Icon icon="material-symbols:add" class="w-4 h-4" /> Tambah Jenis
          </button>
        </div>

        <div class="space-y-6">
          <div v-for="(row, index) in formRows" :key="index" class="bg-[#F9F9F7] rounded-[1.5rem] p-5 border border-stone-100 relative">
            <button v-if="formRows.length > 1" @click="removeRow(index)" class="absolute -top-3 -right-3 w-8 h-8 bg-red-100 text-red-600 rounded-full flex items-center justify-center hover:bg-red-200 transition-all shadow-sm">
              <Icon icon="material-symbols:close" class="w-5 h-5 font-bold" />
            </button>

            <div class="flex flex-col md:flex-row gap-6">
              <div class="w-full md:w-1/3">
                <div @click="triggerFileInput(index)" class="w-full aspect-square border-2 border-dashed border-[#8CA68D] rounded-3xl flex flex-col items-center justify-center cursor-pointer hover:bg-green-50 transition-all relative overflow-hidden">
                  <img v-if="row.fotoPreview" :src="row.fotoPreview" class="absolute inset-0 w-full h-full object-cover" />
                  <Icon v-else icon="material-symbols:upload-2" class="w-10 h-10 text-[#4A7043]" />
                  <input :id="'file-input-'+index" type="file" class="hidden" accept="image/*" @change="handleFileUpload($event, index)" />
                </div>
              </div>

              <div class="w-full md:w-2/3 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                  <div class="space-y-1.5">
                    <label class="text-xs font-black text-stone-800">Kategori <span class="text-red-500">*</span></label>
                    <select v-model="row.kategori_id" @change="row.sampah_id = ''" class="w-full bg-white border border-stone-200 rounded-xl py-2.5 px-4 text-sm appearance-none">
                      <option value="" disabled>Pilih</option>
                      <option v-for="kat in listKategori" :key="kat.kategori_id" :value="kat.kategori_id">{{ kat.nama }}</option>
                    </select>
                  </div>

                  <div class="space-y-1.5">
                    <label class="text-xs font-black text-stone-800">Jenis <span class="text-red-500">*</span></label>
                    <select v-model="row.sampah_id" :disabled="!row.kategori_id" class="w-full bg-white border border-stone-200 rounded-xl py-2.5 px-4 text-sm appearance-none disabled:bg-stone-100">
                      <option value="" disabled>Pilih</option>
                      <option v-for="item in filteredSampah(row.kategori_id)" :key="item.sampah_id" :value="item.sampah_id">{{ item.item_sampah?.nama }}</option>
                    </select>
                  </div>

                  <div class="space-y-1.5">
                    <label class="text-xs font-black text-stone-800">Berat (kg) <span class="text-red-500">*</span></label>
                    <input type="number" v-model="row.berat_timbang" step="0.1" class="w-full bg-white border border-stone-200 rounded-xl py-2.5 px-4 text-sm" placeholder="0" />
                  </div>

                  <div class="space-y-1.5">
                    <label class="text-xs font-black text-stone-800">Harga/kg</label>
                    <div class="w-full bg-[#F5F7F5] border border-stone-200 rounded-xl py-2.5 px-4 text-sm font-bold text-stone-500">
                      {{ formatRupiah(getHargaPerKg(row.sampah_id)) }}
                    </div>
                  </div>
                </div>

                <div class="pt-4 flex justify-end items-center gap-4 border-t border-stone-200">
                  <p class="text-sm font-black text-stone-800">Total:</p>
                  <p class="text-lg font-black text-[#4A7043]">{{ formatRupiah(getRowTotal(row)) }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="mt-6 bg-[#4A7043] rounded-2xl p-6 flex justify-between items-center text-white">
          <div>
            <p class="text-white/70 text-xs font-bold">Total Berat</p>
            <p class="text-2xl font-black">{{ totalBerat.toFixed(1) }} kg</p>
          </div>
          <div class="text-right">
            <p class="text-white/70 text-xs font-bold">Total Nilai</p>
            <p class="text-2xl font-black">{{ formatRupiah(totalNilai) }}</p>
          </div>
        </div>
      </div>

      <div class="flex gap-4">
        <button @click="step = 1" class="flex-1 py-4 rounded-2xl bg-stone-200 text-stone-600 font-bold hover:bg-stone-300">Batal</button>
        <button @click="submitPenimbangan" :disabled="isSubmitting || !isFormValid" class="flex-[2] py-4 rounded-2xl bg-[#4A7043] text-white font-bold hover:bg-[#3D5C37] shadow-lg disabled:opacity-50">
          {{ isSubmitting ? 'Memproses...' : 'Simpan Transaksi' }}
        </button>
      </div>
    </div>

    <!-- STEP 3: SUCCESS -->
    <div v-else-if="step === 3" class="max-w-3xl mx-auto flex flex-col items-center pb-20 text-center space-y-4">
      <Icon icon="material-symbols:check-circle-outline" class="w-24 h-24 text-green-500" />
      <h3 class="text-2xl font-black text-stone-800">Penimbangan Berhasil!</h3>
      <p class="text-stone-500">Data telah disimpan ke database.</p>
      
      <div class="w-full bg-white rounded-3xl p-6 shadow-lg border border-stone-200 text-left space-y-4">
        <div class="border-b border-dashed border-stone-300 pb-4 text-center">
          <p class="text-xs font-bold text-stone-400">ID TRANSAKSI: TR-{{ String(receiptData?.transaksi_id || 0).padStart(3, '0') }}</p>
        </div>
        <div class="space-y-2">
          <div class="flex justify-between text-sm font-bold"><span>Nasabah:</span> <span>{{ selectedNasabahData?.nama }}</span></div>
          <div class="flex justify-between text-sm font-bold"><span>Total Nilai:</span> <span class="text-[#4A7043]">{{ formatRupiah(totalNilai) }}</span></div>
        </div>
      </div>

      <button @click="resetToStart" class="w-full py-4 rounded-2xl bg-[#4A7043] text-white font-bold shadow-lg">Kembali</button>
    </div>

  </DashboardLayout>
</template>

<script setup>
import { ref, computed, onMounted, inject } from "vue";
import DashboardLayout from "@/layouts/DashboardLayout.vue";
import { Icon } from "@iconify/vue";

const axios = inject('axios'); // Gunakan axios global

const step = ref(1);
const listNasabah = ref([]);
const selectedNasabah = ref(""); 
const selectedNasabahData = ref(null);
const searchQuery = ref("");

const listKategori = ref([]);
const listSampah = ref([]);
const listTukang = ref([]); 
const selectedTukang = ref(""); 

const isSubmitting = ref(false);
const receiptData = ref(null);

const formRows = ref([{ kategori_id: "", sampah_id: "", berat_timbang: "", foto: null, fotoPreview: null }]);

const handleFileUpload = (event, index) => {
  const file = event.target.files[0];
  if (file) {
    formRows.value[index].foto = file;
    formRows.value[index].fotoPreview = URL.createObjectURL(file);
  }
};

const triggerFileInput = (index) => {
  const input = document.getElementById(`file-input-${index}`);
  if (input) input.click();
};

const addRow = () => {
  formRows.value.push({ kategori_id: "", sampah_id: "", berat_timbang: "", foto: null, fotoPreview: null });
};

const removeRow = (index) => {
  if (formRows.value.length > 1) formRows.value.splice(index, 1);
};

const filteredSampah = (kategoriId) => {
  if (!kategoriId) return [];
  return listSampah.value.filter(item => item.item_sampah && item.item_sampah.kategori_id === kategoriId);
};

const getHargaPerKg = (sampah_id) => {
  if (!sampah_id) return 0;
  const selectedItem = listSampah.value.find(item => item.sampah_id === sampah_id);
  return selectedItem && selectedItem.item_sampah ? Number(selectedItem.item_sampah.harga_beli) : 0;
};

const getRowTotal = (row) => {
  return (Number(row.berat_timbang) || 0) * getHargaPerKg(row.sampah_id);
};

const totalBerat = computed(() => formRows.value.reduce((sum, row) => sum + (Number(row.berat_timbang) || 0), 0));
const totalNilai = computed(() => formRows.value.reduce((sum, row) => sum + getRowTotal(row), 0));

const formatRupiah = (angka) => {
  return new Intl.NumberFormat("id-ID", { style: "currency", currency: "IDR", minimumFractionDigits: 0 }).format(angka);
};

const filteredNasabahList = computed(() => {
  if (!searchQuery.value) return listNasabah.value;
  const lowerQuery = searchQuery.value.toLowerCase();
  return listNasabah.value.filter(n => 
    (n.nama && n.nama.toLowerCase().includes(lowerQuery)) || 
    `NSB-${String(n.nasabah_id).padStart(3, '0')}`.toLowerCase().includes(lowerQuery)
  );
});

const selectNasabahAndProceed = (nasabah) => {
  selectedNasabah.value = nasabah.nasabah_id;
  selectedNasabahData.value = nasabah;
  step.value = 2;
  window.scrollTo({ top: 0, behavior: 'smooth' });
};

const isFormValid = computed(() => {
  const validItems = formRows.value.filter(row => row.sampah_id !== "" && row.berat_timbang > 0);
  return validItems.length === formRows.value.length && validItems.length > 0 && selectedTukang.value !== "";
});

// API CALLS (Menggunakan URL Relatif)
const fetchListNasabah = async () => {
  try {
    const response = await axios.get("/api/petugas/list-nasabah");
    listNasabah.value = response.data.data;
  } catch (err) { console.error(err); }
};

const fetchListKategori = async () => {
  try {
    const response = await axios.get("/api/petugas/list-kategori");
    listKategori.value = response.data.data;
  } catch (err) { console.error(err); }
};

const fetchListSampah = async () => {
  try {
    const response = await axios.get("/api/petugas/list-sampah");
    listSampah.value = response.data.data;
  } catch (err) { console.error(err); }
};

const fetchListTukang = async () => {
  try {
    // Sesuaikan endpoint tukang dengan api.php
    const response = await axios.get("/api/petugas/list-tukang");
    listTukang.value = response.data.data;
  } catch (err) { console.error(err); }
};

const submitPenimbangan = async () => {
  isSubmitting.value = true;
  try {
    const formData = new FormData();
    formData.append("nasabah_id", selectedNasabah.value);
    formData.append("tukang_id", selectedTukang.value);
    
    formRows.value.forEach((item, index) => {
      formData.append(`items[${index}][sampah_id]`, item.sampah_id);
      formData.append(`items[${index}][berat_timbang]`, item.berat_timbang);
      if (item.foto) formData.append(`items[${index}][foto]`, item.foto);
    });

    const response = await axios.post("/api/petugas/penimbangan-antar-sendiri", formData);
    receiptData.value = response.data;
    step.value = 3;
    window.scrollTo({ top: 0, behavior: 'smooth' });
  } catch (err) {
    alert("Gagal menyimpan: " + (err.response ? err.response.data.message : err.message));
  } finally {
    isSubmitting.value = false;
  }
};

const resetToStart = () => {
  step.value = 1;
  selectedNasabah.value = "";
  selectedNasabahData.value = null;
  selectedTukang.value = "";
  formRows.value = [{ kategori_id: "", sampah_id: "", berat_timbang: "", foto: null, fotoPreview: null }];
  fetchListNasabah();
};

onMounted(() => {
  fetchListNasabah(); 
  fetchListKategori(); 
  fetchListSampah();
  fetchListTukang();
});
</script>
