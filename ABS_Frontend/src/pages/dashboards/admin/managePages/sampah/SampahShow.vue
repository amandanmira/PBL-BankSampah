<template>
  <div>
    <h2>Detail Jenis Sampah</h2>

    <div v-if="loading">Loading...</div>
    <div v-if="error">{{ error }}</div>

    <div v-if="data">
      <p><strong>ID:</strong> {{ data.kategori_id }}</p>
      <p><strong>Nama:</strong> {{ data.nama }}</p>

      <hr />

      <h3>Kategori</h3>

      <div v-if="data.item_sampah.length === 0">
        Tidak ada kategori
      </div>

      <table
        v-else
        border="1"
        cellpadding="8"
        cellspacing="0"
      >
        <thead>
          <tr>
            <th>ID</th>
            <th>Foto</th>
            <th>Nama</th>
            <th>Harga Beli</th>
            <th>Harga Jual</th>
            <th>Diskon</th>
          </tr>
        </thead>

        <tbody>
          <tr v-for="k in data.item_sampah" :key="k.item_id">
            <td>{{ k.item_id }}</td>
            <td>
              <img v-if="k.foto" :src="getFoto(k.foto)" width="100" />
              <span v-else>-</span>
            </td>
            <td>{{ k.nama }}</td>
            <td>{{ k.harga_beli }}</td>
            <td>{{ k.harga_jual }}</td>
            <td>{{ k.diskon }}</td>
          </tr>
        </tbody>
      </table>

      <br />

      <button @click="router.push(`/dashboard-admin/kelola-sampah/${data.kategori_id}/edit`)">
        Edit
      </button>

      <button @click="router.push('/dashboard-admin/kelola-sampah')">
        Kembali
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";
import { useRoute, useRouter } from "vue-router";
import { checkRole } from '@/utils'

checkRole('admin')

const route = useRoute();
const router = useRouter();

const id = route.params.id;

const data = ref(null);
const loading = ref(false);
const error = ref(null);

const token = sessionStorage.getItem("token");

if (!token) {
  throw new Error('Otentikasi diperlukan.');
}

const headers = { 'Authorization': `Bearer ${token}` }

const getFoto = (path) => {
  return `http://localhost:8000/storage/${path}`;
};

const fetchData = async () => {
  loading.value = true;
  try {
    const res = await axios.get(`/api/admin/kategori-sampah/${id}`, { headers });
    data.value = res.data;
  } catch (err) {
    error.value = err.response?.data || err.message;
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchData();
});
</script>