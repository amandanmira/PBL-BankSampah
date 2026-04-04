<template>
  <div>
    <h2>Berita</h2>

    <button @click="$router.push('/dashboard-petugas/kelola-berita/create')">
      + Tambah
    </button>

    <div v-if="loading">Loading...</div>
    <div v-if="error">{{ error }}</div>

    <table border="1" cellpadding="8" cellspacing="0">
      <thead>
        <tr>
          <th>ID</th>
          <th>judul</th>
          <th>kategori</th>
          <th>thumbnail</th>
          <th>isi</th>
          <th>tanggal</th>
          <th>petugas_id</th>
        </tr>
      </thead>

      <tbody>
        <tr v-for="item in beritaList" :key="item.berita_id">
          <td>{{ item.berita_id }}</td>
          <td>{{ item.judul }}</td>
          <td>{{ item.kategori }}</td>
          <td>
            <img v-if="item.thumbnail" :src="`http://localhost:8000/storage/${item.thumbnail}`" alt="thumbnail" width="100" />
          </td>
          <td>{{ item.isi }}</td>
          <td>{{ item.tanggal }}</td>
          <td>{{ item.petugas_id }}</td>

          <td>
            <button @click="$router.push(`/dashboard-petugas/berita/${item.berita_id}`)">
              Show
            </button>

            <button @click="$router.push(`/dashboard-petugas/kelola-berita/${item.berita_id}/edit`)">
              Edit
            </button>

            <button @click="deleteBerita(item.berita_id)">
              Delete
            </button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";
import { checkRole } from "@/utils";

checkRole("petugas");

const beritaList = ref([]);
const loading = ref(false);
const error = ref(null);
const token = sessionStorage.getItem("token");

if (!token) {
  throw new Error("Otentikasi diperlukan.");
}

// fetch data
const fetchData = async () => {
  loading.value = true;
  try {
    const headers = { Authorization: `Bearer ${token}` };
    const res = await axios.get("/api/petugas/berita", { headers }); // Pastikan endpoint ini sesuai dengan route Laravel-mu
    beritaList.value = res.data;
  } catch (err) {
    error.value = err.response?.data || err.message;
  } finally {
    loading.value = false;
  }
};

// PERBAIKAN: Ubah nama fungsi menjadi deleteBerita dan perbaiki URL endpoint-nya
const deleteBerita = async (id) => {
  if (!confirm("Yakin ingin menghapus berita ini?")) return;

  try {
    const headers = { Authorization: `Bearer ${token}` };

    // Sesuaikan endpoint delete dengan route berita kamu
    await axios.delete(`/api/petugas/berita/${id}`, { headers });

    alert("Berita berhasil dihapus!");
    fetchData(); // Refresh data setelah berhasil dihapus
  } catch (err) {
    console.error(err);
    alert("Gagal menghapus berita.");
  }
};

onMounted(() => {
  fetchData();
});
</script>
