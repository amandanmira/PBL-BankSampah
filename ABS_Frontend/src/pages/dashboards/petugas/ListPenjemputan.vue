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

    <table v-else-if="pendingPenjemputans.length > 0">
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
        <tr v-for="penjemputan in pendingPenjemputans" :key="penjemputan.penjemputan_id">
          <td>{{ penjemputan.penjemputan_id }}</td>
          <td>{{ penjemputan.deskripsi }}</td>
          <td>{{ penjemputan.alamat }}</td>
          <td>foto</td>
          
          <td>
            <span class="status-pending">{{ penjemputan.status }}</span>
          </td>
          <td class="aksi-buttons">
            <button @click="handleAction(penjemputan.penjemputan_id, 'terima')" class="button-terima">Terima</button>
            <button @click="handleAction(penjemputan.penjemputan_id, 'tolak')" class="button-tolak">Tolak</button>
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

// Pastikan hanya admin yang bisa mengakses
checkRole("petugas");

const allPenjemputans = ref([]);
const loading = ref(true);
const error = ref(null);

// Computed property untuk memfilter pengepul yang statusnya 'pending'
const pendingPenjemputans = computed(() => {
  return allPenjemputans.value.filter((p) => p.status === "pending");
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

    const response = await axios.get("http://localhost:8000/api/petugas/penjemputan", { headers });
    allPenjemputans.value = response.data.data;
  } catch (err) {
    error.value = "Gagal mengambil data penjemputan. " + (err.response ? err.response.data.message : err.message);
    console.error(err);
  } finally {
    loading.value = false;
  }
};

// Fungsi untuk menangani aksi terima atau tolak
const handleAction = async (id, action) => {
  if (action === "terima") {
    if (!confirm("Apakah Anda yakin ingin menerima penjemputan ini?")) {
      return;
    }
  } else if (action === "tolak") {
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

      // Hapus item dari list di frontend untuk update UI secara instan
      allPenjemputans.value = allPenjemputans.value.filter((p) => p.penjemputan_id !== id);
    } catch (err) {
      error.value = `Gagal menolak penjemputan. ` + (err.response ? err.response.data.message : err.message);
      console.error(err);
    }
    return; // Hentikan eksekusi setelah menangani 'tolak'
  }

  // Bagian ini hanya untuk 'terima'
  try {
    const token = sessionStorage.getItem("token");
    const headers = { Authorization: `Bearer ${token}` };

    await axios.put(`http://localhost:8000/api/petugas/penjemputan/${id}/terima`, {}, { headers });

    // Hapus item dari list di frontend untuk update UI secara instan
    allPenjemputans.value = allPenjemputans.value.filter((p) => p.penjemputan_id !== id);
  } catch (err) {
    error.value = `Gagal menerima penjemputan. ` + (err.response ? err.response.data.message : err.message);
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
