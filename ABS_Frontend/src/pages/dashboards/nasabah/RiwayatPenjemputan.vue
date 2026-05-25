<template>
  <div class="container">
    <h1>Riwayat Penjemputan</h1>
    <p>Halaman ini berisi riwayat penjemputan Anda.</p>

    <div v-if="loading" class="loading">Memuat data...</div>

    <div v-if="error" class="error">
      {{ error }}
    </div>

    <table v-else-if="allPenjemputans.length > 0">
      <thead>
        <tr>
          <th>ID</th>
          <th>Deskripsi</th>
          <th>Alamat</th>
          <th>Foto</th>
          <th>Status</th>
          <th>Ket Status</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="penjemputan in allPenjemputans" :key="penjemputan.penjemputan_id">
          <td>{{ penjemputan.penjemputan_id }}</td>
          <td>{{ penjemputan.deskripsi }}</td>
          <td>{{ penjemputan.alamat }}</td>
          <td>
            <div
              v-if="Array.isArray(penjemputan.foto) && penjemputan.foto.length > 0"
              class="flex gap-2 flex-wrap"
            >
              <img
                v-for="(f, i) in penjemputan.foto"
                :key="i"
                :src="`http://localhost:8000/storage/${f}`"
                alt="Foto Penjemputan"
                style="width: 100px; height: 100px; object-fit: cover; border-radius: 4px"
              />
            </div>
            <img
              v-else-if="penjemputan.foto && !Array.isArray(penjemputan.foto)"
              :src="`http://localhost:8000/storage/${penjemputan.foto}`"
              alt="Foto Penjemputan"
              style="width: 100px; height: 100px; object-fit: cover; border-radius: 4px"
            />
            <span v-else>Tidak ada foto</span>
          </td>
          <td>
            <span :class="`status-${penjemputan.status}`">{{ penjemputan.status }}</span>
          </td>
          <td>{{ penjemputan.ket_status }}</td>
        </tr>
      </tbody>
    </table>
    <div v-else-if="!loading">Tidak ada riwayat penjemputan.</div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { checkRole } from '@/utils'
import axios from 'axios'

// Pastikan hanya nasabah yang bisa mengakses
checkRole('nasabah')

const allPenjemputans = ref([])
const loading = ref(true)
const error = ref(null)

// Fungsi untuk mengambil data dari API
const fetchData = async () => {
  loading.value = true
  try {
    const token = sessionStorage.getItem('token')
    if (!token) {
      throw new Error('Otentikasi diperlukan.')
    }
    const user = JSON.parse(sessionStorage.getItem('user'))
    if (!user || !user.nasabah_id) {
      throw new Error('Data pengguna tidak ditemukan.')
    }

    const headers = { Authorization: `Bearer ${token}` }

    // Asumsi ada endpoint untuk mengambil riwayat berdasarkan nasabah_id
    const response = await axios.get('http://localhost:8000/api/nasabah/penjemputan-nasabah', {
      headers,
    })
    allPenjemputans.value = response.data.data
  } catch (err) {
    error.value =
      'Gagal mengambil data riwayat penjemputan. ' +
      (err.response ? err.response.data.message : err.message)
    console.error(err)
  } finally {
    loading.value = false
  }
}

// Ambil data saat komponen dimuat
onMounted(fetchData)
</script>

<style scoped>
.container {
  padding: 2rem;
  font-family: 'Arial', sans-serif;
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
.status-proses {
  background-color: #3498db;
  color: white;
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 0.9em;
  text-transform: capitalize;
}
.status-selesai {
  background-color: #2ecc71;
  color: white;
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 0.9em;
  text-transform: capitalize;
}
.status-ditolak {
  background-color: #e74c3c;
  color: white;
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 0.9em;
  text-transform: capitalize;
}
</style>
