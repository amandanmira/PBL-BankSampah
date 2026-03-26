<template>
  <div>
    <h2>Data Jenis Sampah</h2>

    <button @click="$router.push('/dashboard-admin/kelola-sampah/create')">
      + Tambah
    </button>

    <div v-if="loading">Loading...</div>
    <div v-if="error">{{ error }}</div>

    <table border="1" cellpadding="8" cellspacing="0">
      <thead>
        <tr>
          <th>ID</th>
          <th>Nama</th>
          <th>Stok</th>
          <th>Kategori</th>
          <th>Aksi</th>
        </tr>
      </thead>

      <tbody>
        <tr v-for="item in jenisList" :key="item.jenis_id">
          <td>{{ item.jenis_id }}</td>
          <td>{{ item.nama }}</td>
          <td>{{ item.stok_jenis }}</td>

          <!-- tampilkan kategori -->
          <td>
            <ul>
              <li v-for="k in item.kategori_sampah" :key="k.kategori_id">
                {{ k.nama }} (stok: {{ k.stok }})
              </li>
            </ul>
          </td>

          <td>
            <button @click="$router.push(`/dashboard-admin/kelola-sampah/${item.jenis_id}`)">
              Show
            </button>

            <button @click="$router.push(`/dashboard-admin/kelola-sampah/${item.jenis_id}/edit`)">
              Edit
            </button>

            <button @click="deleteJenis(item.jenis_id)">
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
import { checkRole } from '@/utils'

checkRole('admin')

const jenisList = ref([]);
const loading = ref(false);
const error = ref(null);
const token = sessionStorage.getItem("token");

if (!token) {
  throw new Error('Otentikasi diperlukan.')
}

// fetch data
const fetchData = async () => {
  loading.value = true;
  try {
    const headers = { 'Authorization': `Bearer ${token}` }
    const res = await axios.get("/api/admin/jenis-sampah", {headers});
    jenisList.value = res.data;
  } catch (err) {
    error.value = err.response?.data || err.message;
  } finally {
    loading.value = false;
  }
};

// delete jenis
const deleteJenis = async (id) => {
  if (!confirm("Yakin hapus data ini?")) return;

  try {
    const headers = { 'Authorization': `Bearer ${token}` }

    await axios.delete(`/api/admin/jenis-sampah/${id}`, {headers});
    fetchData();
  } catch (err) {
    alert("Gagal hapus");
  }
};

onMounted(() => {
  fetchData();
});
</script>