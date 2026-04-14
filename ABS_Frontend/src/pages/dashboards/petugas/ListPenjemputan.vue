<template>
  <div class="container">
    <h1>List Penjemputan</h1>
    <p>Halaman ini berisi list penjemputan</p>

    <div v-if="loading" class="loading">
      Memuat data...
    </div>

    <div v-if="error" class="error">
      {{ error }}
    </div>

    <table v-else-if="filteredPenjemputans.length > 0">
      <thead>
        <tr>
          <th>ID</th>
          <th>Deskripsi</th>
          <th>Alamat</th>
          <th>Foto</th>
          <th>Status</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="penjemputan in filteredPenjemputans" :key="penjemputan.penjemputan_id">
          <td>{{ penjemputan.penjemputan_id }}</td>
          <td>{{ penjemputan.deskripsi }}</td>
          <td>{{ penjemputan.alamat }}</td>
          <td>
            <img v-if="penjemputan.foto" :src="`http://localhost:8000/storage/${penjemputan.foto}`" alt="Foto Penjemputan" style="width: 100px; height: auto; object-fit: cover; border-radius: 4px;" />
            <span v-else>Tidak ada foto</span>
          </td>

          <td>
            <span :class="`status-${penjemputan.status}`">{{ penjemputan.status }}</span>
          </td>
          <td class="aksi-buttons">
            <template v-if="penjemputan.status === 'pending'">
              <select v-model="penjemputan.selectedTukang" class="select-tukang">
                <option value="" disabled selected>-- Pilih Tukang --</option>
                <option v-for="tukang in listTukang" :key="tukang.tukang_id" :value="tukang.tukang_id">
                  {{ tukang.nama }}
                </option>
              </select>

              <button @click="handleTerima(penjemputan)" class="button-terima">Terima</button>
              <button @click="handleTolak(penjemputan.penjemputan_id)" class="button-tolak">Tolak</button>
            </template>

            <template v-else-if="penjemputan.status === 'proses'">
              <button @click="showDetail(penjemputan.penjemputan_id)" class="button-show">Show</button>
            </template>
          </td>
        </tr>
      </tbody>
    </table>
    <div v-else-if="!loading">
      Tidak ada penjemputan.
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
import { checkRole } from "@/utils";
import axios from "axios";
import { useRouter } from "vue-router";

// Pastikan hanya admin yang bisa mengakses
checkRole("petugas");

const router = useRouter();
const allPenjemputans = ref([]);
const listTukang = ref([]); // State untuk menyimpan daftar tukang
const loading = ref(true);
const error = ref(null);

const filteredPenjemputans = computed(() => {
  return allPenjemputans.value.filter((p) => p.status === "pending" || p.status === "proses");
});

// Fungsi untuk mengambil data Tukang
const fetchTukang = async () => {
  try {
    const token = sessionStorage.getItem("token");
    const headers = { Authorization: `Bearer ${token}` };
    const response = await axios.get("http://localhost:8000/api/petugas/tukang", { headers });
    listTukang.value = response.data.data;
  } catch (err) {
    console.error("Gagal mengambil data tukang:", err);
  }
};

const fetchData = async () => {
  loading.value = true;
  try {
    const token = sessionStorage.getItem("token");
    if (!token) throw new Error("Otentikasi diperlukan.");
    const headers = { Authorization: `Bearer ${token}` };

    const response = await axios.get("http://localhost:8000/api/petugas/penjemputan", { headers });
    
    // Inisialisasi properti selectedTukang kosong ('') untuk setiap baris penjemputan
    const dataPenjemputan = response.data.data.map(p => ({
        ...p,
        selectedTukang: "" 
    }));
    
    allPenjemputans.value = dataPenjemputan;
  } catch (err) {
    error.value = "Gagal mengambil data penjemputan. " + (err.response ? err.response.data.message : err.message);
    console.error(err);
  } finally {
    loading.value = false;
  }
};

// Fungsi khusus untuk menangani Terima dengan payload tukang_id
const handleTerima = async (penjemputan) => {
  // Validasi jika petugas belum memilih tukang
  if (!penjemputan.selectedTukang) {
    alert("Silakan pilih tukang terlebih dahulu!");
    return;
  }

  if (!confirm("Apakah Anda yakin ingin menerima penjemputan ini?")) {
    return;
  }

  try {
    const token = sessionStorage.getItem("token");
    const headers = { Authorization: `Bearer ${token}` };
    
    // Kirim tukang_id ke backend
    const payload = { tukang_id: penjemputan.selectedTukang };

    await axios.put(`http://localhost:8000/api/petugas/penjemputan/${penjemputan.penjemputan_id}/terima`, payload, { headers });

    // Update UI
    penjemputan.status = "proses";
  } catch (err) {
    error.value = `Gagal menerima penjemputan. ` + (err.response ? err.response.data.message : err.message);
    console.error(err);
  }
};

// Fungsi khusus untuk menangani Tolak
const handleTolak = async (id) => {
  const alasan = prompt("Masukkan alasan penolakan:");
  if (!alasan) {
    alert("Aksi dibatalkan. Alasan penolakan harus diisi.");
    return;
  }

  if (!confirm(`Apakah Anda yakin ingin menolak penjemputan ini dengan alasan: "${alasan}"?`)) {
    return;
  }

  try {
    const token = sessionStorage.getItem("token");
    const headers = { Authorization: `Bearer ${token}` };
    const data = { ket_status: alasan };

    await axios.put(`http://localhost:8000/api/petugas/penjemputan/${id}/tolak`, data, { headers });

    // Hapus dari UI
    allPenjemputans.value = allPenjemputans.value.filter((p) => p.penjemputan_id !== id);
  } catch (err) {
    error.value = `Gagal menolak penjemputan. ` + (err.response ? err.response.data.message : err.message);
    console.error(err);
  }
};

const showDetail = (id) => {
  router.push(`/dashboard-petugas/penimbangan/${id}`);
};

onMounted(() => {
  fetchTukang(); // Ambil list tukang dulu
  fetchData();   // Lalu ambil list penjemputan
});
</script>

<style scoped>
/* Tambahkan sedikit style untuk dropdown select */
.select-tukang {
  padding: 6px;
  border-radius: 4px;
  border: 1px solid #ccc;
  outline: none;
  background-color: white;
}

/* Style lainnya tetap sama */
.container { padding: 2rem; font-family: "Arial", sans-serif; }
table { width: 100%; border-collapse: collapse; margin-top: 1rem; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); }
th, td { border: 1px solid #ddd; padding: 12px; text-align: left; vertical-align: middle; }
th { background-color: #f8f8f8; font-weight: bold; }
.loading, .error { margin-top: 1rem; color: #888; text-align: center; }
.error { color: #e74c3c; font-weight: bold; }
.status-pending { background-color: #f39c12; color: white; padding: 4px 8px; border-radius: 4px; font-size: 0.9em; text-transform: capitalize; }
.status-proses { background-color: #3498db; color: white; padding: 4px 8px; border-radius: 4px; font-size: 0.9em; text-transform: capitalize; }
.aksi-buttons { display: flex; gap: 8px; align-items: center; }
button { padding: 8px 12px; border: none; border-radius: 4px; cursor: pointer; color: white; font-weight: bold; transition: background-color 0.2s; }
.button-terima { background-color: #2ecc71; }
.button-terima:hover { background-color: #27ae60; }
.button-tolak { background-color: #e74c3c; }
.button-tolak:hover { background-color: #c0392b; }
.button-show { background-color: #3498db; }
.button-show:hover { background-color: #2980b9; }
</style>