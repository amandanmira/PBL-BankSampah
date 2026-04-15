<template>
  <div class="container">
    <h1>Detail Penjemputan</h1>
    
    <div v-if="loading" class="loading">Memuat data...</div>
    <div v-if="error" class="error">{{ error }}</div>
    
    <div v-if="penjemputan" class="penjemputan-detail">
      <div class="detail-item">
        <strong>ID Penjemputan:</strong>
        <span>{{ penjemputan.penjemputan_id }}</span>
      </div>
      <div class="detail-item">
        <strong>Nama Nasabah:</strong>
        <span>{{ penjemputan.nasabah ? penjemputan.nasabah.nama : "Tidak diketahui" }}</span>
      </div>

      <div class="form-section-wrapper">
        <form @submit.prevent="submitPenimbangan">
          
          <div class="form-section top-section">
            <div class="form-group">
              <label for="tukang">Petugas / Tukang Timbang *</label>
              <select id="tukang" v-model="selectedTukang" required class="select-dropdown">
                <option value="" disabled>-- Pilih Tukang --</option>
                <option 
                  v-for="t in listTukang" 
                  :key="t.tukang_id" 
                  :value="t.tukang_id"
                >
                  {{ t.nama }}
                </option>
              </select>
            </div>
          </div>

          <div class="form-header mt-2">
            <h2>Detail Sampah</h2>
            <button type="button" @click="addRow" class="btn-add">
              + Tambah Jenis
            </button>
          </div>
          
          <div v-for="(row, index) in formRows" :key="index" class="form-section">
            <button v-if="formRows.length > 1" type="button" @click="removeRow(index)" class="btn-remove" title="Hapus Baris ini">
              &times;
            </button>

            <div class="timbang-row">
              
              <div class="form-group">
                <label>Kategori *</label>
                <select v-model="row.kategori_id" @change="row.sampah_id = ''" required class="select-dropdown">
                  <option value="" disabled>Pilih Kategori</option>
                  <option 
                    v-for="kat in listKategori" 
                    :key="kat.kategori_id" 
                    :value="kat.kategori_id"
                  >
                    {{ kat.nama }}
                  </option>
                </select>
              </div>

              <div class="form-group">
                <label>Jenis Sampah *</label>
                <select v-model="row.sampah_id" required class="select-dropdown" :disabled="!row.kategori_id">
                  <option value="" disabled>Pilih Jenis Sampah</option>
                  <option 
                    v-for="item in filteredSampah(row.kategori_id)" 
                    :key="item.sampah_id" 
                    :value="item.sampah_id"
                  >
                    {{ item.item_sampah ? item.item_sampah.nama : 'Nama tidak ditemukan' }}
                  </option>
                </select>
              </div>

              <div class="form-group">
                <label>Berat (kg) *</label>
                <input 
                  type="number" 
                  v-model="row.berat_timbang" 
                  step="0.1" 
                  min="0.1" 
                  placeholder="Contoh: 7" 
                  required 
                />
              </div>

              <div class="form-group">
                <label>Harga/kg</label>
                <div class="readonly-box">
                  {{ formatRupiah(getHargaPerKg(row.sampah_id)) }}
                </div>
              </div>

              <div class="form-group">
                <label>Total</label>
                <div class="readonly-box total-box">
                  {{ formatRupiah(getRowTotal(row)) }}
                </div>
              </div>
            </div>

            <div class="form-group mt-3">
              <label>Upload Bukti Foto (Opsional)</label>
              <input 
                type="file" 
                accept="image/*" 
                @change="handleFileUpload($event, index)" 
                class="file-input"
              />
            </div>
          </div>

          <div class="summary-card">
            <div class="summary-item">
              <span class="summary-label">Total Berat</span>
              <span class="summary-value">{{ totalBerat.toFixed(1) }} kg</span>
            </div>
            <div class="summary-item right-align">
              <span class="summary-label">Total Nilai</span>
              <span class="summary-value">{{ formatRupiah(totalNilai) }}</span>
            </div>
          </div>

          <button type="submit" class="btn-submit" :disabled="isSubmitting || totalNilai <= 0 || !selectedTukang">
            {{ isSubmitting ? 'Memproses...' : 'Simpan dan Selesai' }}
          </button>
        </form>

        <div v-if="submitMessage" :class="['alert', isSuccess ? 'alert-success' : 'alert-error']">
          {{ submitMessage }}
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import axios from "axios";

const route = useRoute();
const router = useRouter(); 
const penjemputan = ref(null);
const loading = ref(true);
const error = ref(null);

const listKategori = ref([]);
const listSampah = ref([]);
const listTukang = ref([]); 
const selectedTukang = ref(""); 

const isSubmitting = ref(false);
const submitMessage = ref("");
const isSuccess = ref(false);

const formRows = ref([{ kategori_id: "", sampah_id: "", berat_timbang: "", foto: null }]);

const handleFileUpload = (event, index) => {
  formRows.value[index].foto = event.target.files[0];
};

const addRow = () => {
  formRows.value.push({ kategori_id: "", sampah_id: "", berat_timbang: "", foto: null });
};

const removeRow = (index) => {
  if (formRows.value.length > 1) {
    formRows.value.splice(index, 1);
  }
};

const filteredSampah = (kategoriId) => {
  if (!kategoriId) return [];
  return listSampah.value.filter(item => item.item_sampah && item.item_sampah.kategori_id === kategoriId);
};

const getHargaPerKg = (sampah_id) => {
  if (!sampah_id) return 0;
  const selectedItem = listSampah.value.find(item => item.sampah_id === sampah_id);
  if (selectedItem && selectedItem.item_sampah) {
    return Number(selectedItem.item_sampah.harga_beli);
  }
  return 0;
};

const getRowTotal = (row) => {
  const berat = Number(row.berat_timbang) || 0;
  return berat * getHargaPerKg(row.sampah_id);
};

const totalBerat = computed(() => {
  return formRows.value.reduce((sum, row) => sum + (Number(row.berat_timbang) || 0), 0);
});

const totalNilai = computed(() => {
  return formRows.value.reduce((sum, row) => sum + getRowTotal(row), 0);
});

const formatRupiah = (angka) => {
  return new Intl.NumberFormat("id-ID", {
    style: "currency", currency: "IDR", minimumFractionDigits: 0, maximumFractionDigits: 0
  }).format(angka);
};

const fetchData = async () => {
  try {
    const token = sessionStorage.getItem("token");
    if (!token) throw new Error("Otentikasi diperlukan.");
    const penjemputanId = route.params.id;
    const headers = { Authorization: `Bearer ${token}` };
    const response = await axios.get(`http://localhost:8000/api/petugas/showpenjemputan/${penjemputanId}`, { headers });
    penjemputan.value = response.data.data;
  } catch (err) {
    error.value = "Gagal mengambil data detail penjemputan. " + (err.response ? err.response.data.message : err.message);
  } finally {
    loading.value = false;
  }
};

const fetchListKategori = async () => {
  try {
    const token = sessionStorage.getItem("token");
    const headers = { Authorization: `Bearer ${token}` };
    const response = await axios.get("http://localhost:8000/api/petugas/list-kategori", { headers });
    listKategori.value = response.data.data;
  } catch (err) {
    console.error("Gagal mengambil daftar kategori:", err);
  }
};

const fetchListSampah = async () => {
  try {
    const token = sessionStorage.getItem("token");
    const headers = { Authorization: `Bearer ${token}` };
    const response = await axios.get("http://localhost:8000/api/petugas/list-sampah", { headers });
    listSampah.value = response.data.data;
  } catch (err) {
    console.error("Gagal mengambil daftar sampah:", err);
  }
};

const fetchListTukang = async () => {
  try {
    const token = sessionStorage.getItem("token");
    const headers = { Authorization: `Bearer ${token}` };
    const response = await axios.get("http://localhost:8000/api/petugas/list-tukang", { headers });
    listTukang.value = response.data.data;
  } catch (err) {
    console.error("Gagal mengambil daftar tukang:", err);
  }
};

const submitPenimbangan = async () => {
  isSubmitting.value = true;
  submitMessage.value = "";
  
  try {
    const token = sessionStorage.getItem("token");
    const headers = { 
      Authorization: `Bearer ${token}`,
      "Content-Type": "multipart/form-data" 
    };

    const validItems = formRows.value.filter(row => row.sampah_id !== "" && row.berat_timbang > 0);

    const formData = new FormData();
    formData.append("nasabah_id", penjemputan.value.nasabah_id);
    formData.append("penjemputan_id", penjemputan.value.penjemputan_id);
    formData.append("tukang_id", selectedTukang.value);
    // tipeTransaksi dihapus dari formData
    
    validItems.forEach((item, index) => {
      formData.append(`items[${index}][sampah_id]`, item.sampah_id);
      formData.append(`items[${index}][berat_timbang]`, item.berat_timbang);
      if (item.foto) {
        formData.append(`items[${index}][foto]`, item.foto);
      }
    });

    const response = await axios.post("http://localhost:8000/api/petugas/penimbangan", formData, { headers });
    
    isSuccess.value = true;
    submitMessage.value = response.data.message;
    
    setTimeout(() => {
      router.push('/dashboard-petugas/listpenjemputan');
    }, 1500); 
    
  } catch (err) {
    isSuccess.value = false;
    submitMessage.value = "Gagal menyimpan: " + (err.response ? err.response.data.message : err.message);
  } finally {
    isSubmitting.value = false;
  }
};

onMounted(() => {
  fetchData();
  fetchListKategori(); 
  fetchListSampah();
  fetchListTukang();
});
</script>

<style scoped>
.mt-3 { margin-top: 1rem; }
.mb-3 { margin-bottom: 1rem; }
.select-dropdown { width: 100%; font-size: 1em; padding: 0.8rem; border: 1px solid #ccc; border-radius: 6px; background-color: #fcfcfc; }
.select-dropdown:focus { border-color: #4A6B46; outline: none; }
.file-input { padding: 0.5rem; background-color: #fff; cursor: pointer; border: 1px dashed #4A6B46; width: 100%; border-radius: 6px; }

</style>