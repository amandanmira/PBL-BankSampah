<template>
  <div class="container">
    <h1>List Penarikan Saldo</h1>
    <p>Halaman ini berisi daftar permintaan penarikan saldo dari nasabah.</p>

    <div v-if="loading" class="loading">Memuat data...</div>
    <div v-if="error" class="error">{{ error }}</div>

    <table v-else-if="filteredPenarikan.length > 0">
      <thead>
        <tr>
          <th>ID</th>
          <th>Tanggal</th>
          <th>Nama Nasabah</th>
          <th>Bank / No. Rek</th>
          <th>Saldo Awal</th>
          <th>Jumlah Tarik</th>
          <th>Sisa Saldo</th>
          <th>Status</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="penarikan in filteredPenarikan" :key="penarikan.penarikan_id">
          <td>{{ penarikan.penarikan_id }}</td>
          <td>{{ formatDate(penarikan.created_at) }}</td>
          <td>{{ penarikan.nasabah?.nama || 'Tidak diketahui' }}</td>
          <td>
            <strong>{{ penarikan.nasabah?.nama_bank || '-' }}</strong><br>
            <small>{{ penarikan.nasabah?.no_rekening || '-' }}</small>
          </td>
          
          <td class="text-green">{{ formatRupiah(penarikan.nasabah?.saldo || 0) }}</td>
          
          <td class="text-red font-bold">{{ formatRupiah(penarikan.jumlah) }}</td>
          
          <td class="text-blue font-bold">
            {{ formatRupiah((penarikan.nasabah?.saldo || 0) - penarikan.jumlah) }}
          </td>

          <td>
            <span :class="`status-${penarikan.status}`">{{ penarikan.status }}</span>
          </td>
          <td class="aksi-buttons">
            <template v-if="penarikan.status === 'pending'">
              <button @click="openModalTerima(penarikan)" class="button-terima">Terima & Upload</button>
              <button @click="handleTolak(penarikan.penarikan_id)" class="button-tolak">Tolak</button>
            </template>
          </td>
        </tr>
      </tbody>
    </table>
    
    <div v-else-if="!loading" class="empty-state">
      Tidak ada permintaan penarikan yang perlu diproses.
    </div>

    <div v-if="isModalOpen" class="modal-overlay" @click.self="closeModal">
      <div class="modal-content">
        <div class="modal-header">
          <h2>Upload Bukti Transfer</h2>
          <button class="close-btn" @click="closeModal">&times;</button>
        </div>
        
        <form @submit.prevent="submitTerima" class="modal-body">
          <p>Memproses penarikan untuk <strong>{{ selectedData?.nasabah?.nama }}</strong> sebesar <strong class="text-red">{{ formatRupiah(selectedData?.jumlah) }}</strong>.</p>
          
          <div class="info-bank">
            <p>Silakan transfer ke:</p>
            <h3>{{ selectedData?.nasabah?.nama_bank }} - {{ selectedData?.nasabah?.no_rekening }}</h3>
          </div>

          <div class="form-group upload-section">
            <label for="bukti_tf">Upload Bukti Transfer (Wajib) *</label>
            <input 
              type="file" 
              id="bukti_tf" 
              accept="image/*" 
              @change="handleFileUpload" 
              required
              class="file-input"
            />
          </div>

          <button type="submit" class="button-submit" :disabled="isSubmitting">
            {{ isSubmitting ? 'Memproses...' : 'Kirim Bukti & Selesaikan' }}
          </button>
        </form>
      </div>
    </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
import { checkRole } from "@/utils";
import axios from "axios";

checkRole("petugas");

const allPenarikans = ref([]);
const loading = ref(true);
const error = ref(null);

// --- STATE UNTUK MODAL UPLOAD ---
const isModalOpen = ref(false);
const selectedData = ref(null);
const buktiFile = ref(null);
const isSubmitting = ref(false);

// Utils: Format Uang ke Rupiah
const formatRupiah = (angka) => {
  if (angka === null || angka === undefined) return "Rp 0";
  return new Intl.NumberFormat("id-ID", { style: "currency", currency: "IDR", minimumFractionDigits: 0 }).format(angka);
};

// Utils: Format Tanggal
const formatDate = (dateString) => {
  if (!dateString) return "-";
  const options = { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' };
  return new Date(dateString).toLocaleDateString('id-ID', options);
};

const filteredPenarikan = computed(() => {
  return allPenarikans.value.filter((p) => p.status === "pending" || p.status === "proses");
});

const fetchData = async () => {
  loading.value = true;
  error.value = null;
  try {
    const token = sessionStorage.getItem("token");
    const headers = { Authorization: `Bearer ${token}` };
    const response = await axios.get("http://localhost:8000/api/petugas/penarikan", { headers });
    allPenarikans.value = response.data.data;
  } catch (err) {
    error.value = "Gagal mengambil data penarikan.";
  } finally {
    loading.value = false;
  }
};

// --- FUNGSI MODAL & UPLOAD BUKTI ---
const openModalTerima = (penarikan) => {
  selectedData.value = penarikan;
  isModalOpen.value = true;
};

const closeModal = () => {
  isModalOpen.value = false;
  selectedData.value = null;
  buktiFile.value = null;
};

const handleFileUpload = (event) => {
  buktiFile.value = event.target.files[0];
};

const submitTerima = async () => {
  if (!buktiFile.value) {
    alert("Bukti transfer wajib diupload!");
    return;
  }

  isSubmitting.value = true;
  try {
    const token = sessionStorage.getItem("token");
    const headers = { 
      Authorization: `Bearer ${token}`,
      "Content-Type": "multipart/form-data" // Wajib karena mengirim file
    };

    const formData = new FormData();
    formData.append("bukti_tf", buktiFile.value);

    // Menggunakan POST dengan method _method=PUT karena PHP kadang kesulitan membaca file via PUT langsung
    formData.append("_method", "PUT"); 

    await axios.post(`http://localhost:8000/api/petugas/penarikan/${selectedData.value.penarikan_id}/terima`, formData, { headers });

    // Hapus dari tampilan tabel karena statusnya sudah 'selesai'
    allPenarikans.value = allPenarikans.value.filter((p) => p.penarikan_id !== selectedData.value.penarikan_id);
    
    closeModal();
    alert("Penarikan berhasil diproses dan saldo telah dipotong!");
    
  } catch (err) {
    alert(`Gagal memproses penarikan: ` + (err.response ? err.response.data.message : err.message));
  } finally {
    isSubmitting.value = false;
  }
};

// --- FUNGSI TOLAK TETAP SAMA ---
const handleTolak = async (id) => {
  const alasan = prompt("Masukkan alasan penolakan:");
  if (!alasan) return;
  if (!confirm(`Tolak penarikan ini dengan alasan: "${alasan}"?`)) return;

  try {
    const token = sessionStorage.getItem("token");
    const headers = { Authorization: `Bearer ${token}` };
    const data = { ket_status: alasan };

    await axios.put(`http://localhost:8000/api/petugas/penarikan/${id}/tolak`, data, { headers });
    allPenarikans.value = allPenarikans.value.filter((p) => p.penarikan_id !== id);
  } catch (err) {
    alert(`Gagal menolak penarikan.`);
  }
};

onMounted(fetchData);
</script>

<style scoped>
.container { padding: 2rem; font-family: "Arial", sans-serif; }
table { width: 100%; border-collapse: collapse; margin-top: 1.5rem; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); background-color: white; }
th, td { border: 1px solid #ddd; padding: 12px; text-align: left; vertical-align: middle; }
th { background-color: #f8f8f8; font-weight: bold; }

.font-bold { font-weight: bold; }
.text-green { color: #27ae60; font-weight: bold; }
.text-red { color: #c0392b; }
.text-blue { color: #2980b9; }

.loading, .error, .empty-state { margin-top: 2rem; text-align: center; font-size: 1.1em; }
.error { color: #e74c3c; font-weight: bold; }

.status-pending { background-color: #f39c12; color: white; padding: 4px 8px; border-radius: 4px; font-size: 0.9em; text-transform: capitalize; }
.status-proses { background-color: #3498db; color: white; padding: 4px 8px; border-radius: 4px; font-size: 0.9em; text-transform: capitalize; }

.aksi-buttons { display: flex; gap: 8px; flex-wrap: wrap; }
button { padding: 8px 12px; border: none; border-radius: 4px; cursor: pointer; color: white; font-weight: bold; font-size: 0.9em; transition: 0.2s; }
.button-terima { background-color: #2ecc71; }
.button-terima:hover { background-color: #27ae60; }
.button-tolak { background-color: #e74c3c; }
.button-tolak:hover { background-color: #c0392b; }

/* CSS MODAL UPLOAD */
.modal-overlay { position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(0, 0, 0, 0.5); display: flex; justify-content: center; align-items: center; z-index: 1000; }
.modal-content { background: white; padding: 2rem; border-radius: 8px; width: 90%; max-width: 450px; box-shadow: 0 4px 15px rgba(0,0,0,0.2); }
.modal-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #eee; padding-bottom: 1rem; margin-bottom: 1rem; }
.modal-header h2 { margin: 0; font-size: 1.3rem; color: #333; }
.close-btn { background: none; border: none; font-size: 1.8rem; cursor: pointer; color: #888; padding: 0; }
.close-btn:hover { color: #e74c3c; }

.info-bank { background-color: #f0f8ff; padding: 1rem; border-radius: 6px; border-left: 4px solid #3498db; margin: 1.5rem 0; }
.info-bank h3 { margin: 5px 0 0 0; color: #2c3e50; }

.upload-section { margin-bottom: 1.5rem; display: flex; flex-direction: column; gap: 8px;}
.file-input { padding: 0.5rem; border: 1px dashed #3498db; border-radius: 4px; cursor: pointer; }

.button-submit { background-color: #3498db; width: 100%; padding: 12px; font-size: 1em; }
.button-submit:hover:not(:disabled) { background-color: #2980b9; }
.button-submit:disabled { background-color: #95a5a6; cursor: not-allowed; }
</style>