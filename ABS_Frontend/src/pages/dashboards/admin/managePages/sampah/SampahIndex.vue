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
          <th>Kategori</th>
          <th>Aksi</th>
        </tr>
      </thead>

      <tbody>
        <tr v-for="item in jenisList" :key="item.kategori_id">
          <td>{{ item.kategori_id }}</td>
          <td>{{ item.nama }}</td>

          <!-- tampilkan kategori -->
          <td>
            <ul>
              <li v-for="k in item.item_sampah" :key="k.item_id">
                {{ k.nama }}
              </li>
            </ul>
          </td>

          <td>
            <button v-if="item.active == 1" @click="$router.push(`/dashboard-admin/kelola-sampah/${item.kategori_id}`)">
              Show
            </button>

            <button v-if="item.active == 1"
              @click="$router.push(`/dashboard-admin/kelola-sampah/${item.kategori_id}/edit`)">
              Edit
            </button>

            <button @click="deleteJenis(item.kategori_id)">
              {{ item.active === 0 ? 'Aktifkan' : 'Nonaktifkan' }}
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
    const res = await axios.get("/api/admin/kategori-sampah", { headers });
    jenisList.value = res.data;
  } catch (err) {
    error.value = err.response?.data || err.message;
  } finally {
    loading.value = false;
  }
};

// delete jenis
const deleteJenis = async (id) => {
  try {
    const dataSampah = jenisList.value[id - 1]
    const statusData = ref({
      active: !(dataSampah.active === 1) ? 1 : 0
    })

    const headers = { 'Authorization': `Bearer ${token}` }

    await axios.put(`/api/admin/kategori-sampah/${id}/status`, statusData.value, { headers });
    fetchData();
  } catch (err) {
    console.error(err)
    alert("Gagal hapus");
  }
};

onMounted(() => {
  fetchData();
});
</script>