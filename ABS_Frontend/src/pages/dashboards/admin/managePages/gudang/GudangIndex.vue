<template>
  <div class="container">
    <h2>Data Gudang</h2>

    <!-- tombol create -->
    <button @click="goCreate">+ Tambah Gudang</button>

    <table border="1" cellpadding="8" cellspacing="0">
      <thead>
        <tr>
          <th>ID</th>
          <th>Alamat</th>
          <th>Kapasitas</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="gudang in gudangList" :key="gudang.gudang_id">
          <td>{{ gudang.gudang_id }}</td>
          <td>{{ gudang.alamat }}</td>
          <td>{{ gudang.kapasitas }}</td>
          <td>
            <button @click="showGudang(gudang.gudang_id)">Show</button>
            <button @click="editGudang(gudang.gudang_id)">Edit</button>
            <button @click="deleteGudang(gudang.gudang_id)">Delete</button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'
import { checkRole } from '@/utils'

checkRole('admin')

const router = useRouter()
const gudangList = ref([])

// ambil data
const fetchGudang = async () => {
  try {
    const token = sessionStorage.getItem('token')

    if (!token) {
      throw new Error('Otentikasi diperlukan.')
    }

    const headers = { 'Authorization': `Bearer ${token}` }

    const res = await axios.get('/api/admin/gudang', { headers })
    gudangList.value = res.data.data
  } catch (err) {
    console.error(err)
  }
}

// lifecycle
onMounted(() => {
  fetchGudang()
})

// navigasi
const goCreate = () => {
  router.push('/dashboard-admin/kelola-gudang/create')
}

const showGudang = (id) => {
  router.push(`/dashboard-admin/kelola-gudang/${id}`)
}

const editGudang = (id) => {
  router.push(`/dashboard-admin/kelola-gudang/${id}/edit`)
}

// delete
const deleteGudang = async (id) => {
  if (!confirm('Yakin mau hapus?')) return

  try {
		const token = sessionStorage.getItem('token')

    if (!token) {
      throw new Error('Otentikasi diperlukan.')
    }

    const headers = { 'Authorization': `Bearer ${token}` }

    await axios.delete(`/api/admin/gudang/${id}`, {headers})
    fetchGudang() // refresh
  } catch (err) {
    console.error(err)
  }
}
</script>

<style scoped>
.container {
  padding: 20px;
}

button {
  margin: 2px;
  cursor: pointer;
}
</style>
