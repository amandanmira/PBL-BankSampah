<template>
  <div class="container">
    <h1>Kelola Akun Petugas</h1>
    <p>Halaman ini hanya bisa diakses oleh admin.</p>
    
    <div v-if="loading" class="loading">
      Memuat data...
    </div>

    <div v-if="error" class="error">
      {{ error }}
    </div>

    <table v-else-if="petugasList.length > 0">
      <thead>
        <tr>
          <th>ID Petugas</th>
          <th>Nama</th>
          <th>Username</th>
          <th>Email</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="petugas in petugasList" :key="petugas.petugas_id">
          <td>{{ petugas.petugas_id }}</td>
          <td>{{ petugas.nama }}</td>
          <td>{{ petugas.username }}</td>
          <td>{{ petugas.email }}</td>
        </tr>
      </tbody>
    </table>
    <div v-else-if="!loading">
      Tidak ada data petugas untuk ditampilkan.
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { checkRole } from '@/utils'
import axios from 'axios' // <-- UBAH INI: Impor axios langsung

// Periksa role saat komponen dimuat
checkRole('admin')

const petugasList = ref([])
const loading = ref(true)
const error = ref(null)

onMounted(async () => {
  try {
    // Ambil token dari local storage atau state management
    const token = sessionStorage.getItem('token');
    if (!token) {
        throw new Error('Otentikasi diperlukan.');
    }

    // UBAH INI: Gunakan axios langsung dengan URL lengkap
    const response = await axios.get('http://localhost:8000/api/admin/petugas', {
        headers: {
            'Authorization': `Bearer ${token}`
        }
    });
    petugasList.value = response.data.data;
  } catch (err) {
    error.value = 'Gagal mengambil data petugas. ' + (err.response ? err.response.data.message : err.message);
    console.error(err);
  } finally {
    loading.value = false;
  }
});
</script>

<style scoped>
.container {
  padding: 2rem;
}
table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 1rem;
}
th, td {
  border: 1px solid #ddd;
  padding: 8px;
  text-align: left;
}
th {
  background-color: #f2f2f2;
}
.loading, .error {
  margin-top: 1rem;
  color: #888;
}
.error {
  color: red;
}
</style>

