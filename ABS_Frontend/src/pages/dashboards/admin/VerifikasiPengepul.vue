<template>
  <div class="container">
    <h1>Verifikasi Pendaftar Pengepul</h1>
    <p>Halaman ini untuk menerima atau menolak pendaftar pengepul baru.</p>

    <div v-if="loading" class="loading">
      Memuat data...
    </div>

    <div v-if="error" class="error">
      {{ error }}
    </div>

    <table v-else-if="pendingPengepuls.length > 0">
      <thead>
        <tr>
          <th>ID</th>
          <th>Nama</th>
          <th>Email</th>
          <th>No. Telepon</th>
          <th>Nama Lembaga</th>
          <th>Alamat</th>
          <th>Status</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="pengepul in pendingPengepuls" :key="pengepul.pengepul_id">
          <td>{{ pengepul.pengepul_id }}</td>
          <td>{{ pengepul.nama }}</td>
          <td>{{ pengepul.email }}</td>
          <td>{{ pengepul.no_telp }}</td>
          <td>{{ pengepul.nama_lembaga }}</td>
          <td>{{ pengepul.alamat }}</td>
          <td>
            <span class="status-pending">{{ pengepul.status }}</span>
          </td>
          <td class="aksi-buttons">
            <button @click="handleAction(pengepul.pengepul_id, 'terima')" class="button-terima">Terima</button>
            <button @click="handleAction(pengepul.pengepul_id, 'tolak')" class="button-tolak">Tolak</button>
          </td>
        </tr>
      </tbody>
    </table>
    <div v-else-if="!loading">
      Tidak ada pendaftar pengepul yang perlu diverifikasi.
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
import { checkRole } from "@/utils";
import axios from "axios";

// Pastikan hanya admin yang bisa mengakses
checkRole("admin");

const allPengepuls = ref([]);
const loading = ref(true);
const error = ref(null);

// Computed property untuk memfilter pengepul yang statusnya 'pending'
const pendingPengepuls = computed(() => {
  return allPengepuls.value.filter((p) => p.status === "pending");
});

// Fungsi untuk mengambil data dari API
const fetchData = async () => {
  loading.value = true;
  try {
    const token = sessionStorage.getItem("token");
    if (!token) {
      throw new Error("Otentikasi diperlukan.");
    }
    const headers = { Authorization: `Bearer ${token}` };

    const response = await axios.get("http://localhost:8000/api/admin/pengepul", { headers });
    allPengepuls.value = response.data.data;
  } catch (err) {
    error.value = "Gagal mengambil data pengepul. " + (err.response ? err.response.data.message : err.message);
    console.error(err);
  } finally {
    loading.value = false;
  }
};

// Fungsi untuk menangani aksi terima atau tolak
const handleAction = async (id, action) => {
  const confirmationMessage = action === "terima" ? "Apakah Anda yakin ingin menerima pendaftar ini?" : "Apakah Anda yakin ingin menolak dan menghapus pendaftar ini?";

  if (!confirm(confirmationMessage)) {
    return;
  }

  try {
    const token = sessionStorage.getItem("token");
    const headers = { Authorization: `Bearer ${token}` };

    if (action === "terima") {
      await axios.put(`http://localhost:8000/api/admin/pengepul/${id}/terima`, {}, { headers });
    } else if (action === "tolak") {
      await axios.delete(`http://localhost:8000/api/admin/pengepul/${id}/tolak`, { headers });
    }

    // Hapus item dari list di frontend untuk update UI secara instan
    allPengepuls.value = allPengepuls.value.filter((p) => p.pengepul_id !== id);
  } catch (err) {
    error.value = `Gagal ${action} pendaftar. ` + (err.response ? err.response.data.message : err.message);
    console.error(err);
  }
};

// Ambil data saat komponen dimuat
onMounted(fetchData);
</script>

<style scoped>
.container {
  padding: 2rem;
  font-family: "Arial", sans-serif;
}
table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 1rem;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}
th,
td {
  border: 1px solid #ddd;
  padding: 12px;
  text-align: left;
  vertical-align: middle;
}
th {
  background-color: #f8f8f8;
  font-weight: bold;
}
.loading,
.error {
  margin-top: 1rem;
  color: #888;
  text-align: center;
}
.error {
  color: #e74c3c;
  font-weight: bold;
}
.status-pending {
  background-color: #f39c12;
  color: white;
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 0.9em;
  text-transform: capitalize;
}
.aksi-buttons {
  display: flex;
  gap: 8px;
}
button {
  padding: 8px 12px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  color: white;
  font-weight: bold;
  transition: background-color 0.2s;
}
.button-terima {
  background-color: #2ecc71;
}
.button-terima:hover {
  background-color: #27ae60;
}
.button-tolak {
  background-color: #e74c3c;
}
.button-tolak:hover {
  background-color: #c0392b;
}
</style>
