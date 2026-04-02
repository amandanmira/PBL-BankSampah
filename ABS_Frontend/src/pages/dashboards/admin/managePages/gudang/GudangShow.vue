<template>
  <div class="container">
    <h2>Detail Gudang</h2>

    <div v-if="gudang">
      <p><strong>ID:</strong> {{ gudang.gudang_id }}</p>
      <p><strong>Alamat:</strong> {{ gudang.alamat }}</p>
      <p><strong>Kapasitas:</strong> {{ gudang.kapasitas }}</p>
    </div>

    <div v-else>
      <p>Loading...</p>
    </div>

    <button @click="goBack">Kembali</button>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useRoute, useRouter } from 'vue-router'
import { checkRole } from '@/utils'

checkRole('admin')

const route = useRoute()
const router = useRouter()

const gudang = ref(null)

// ambil data berdasarkan id
const fetchGudang = async () => {
  try {
		const token = sessionStorage.getItem('token')

    if (!token) {
      throw new Error('Otentikasi diperlukan.')
    }

    const headers = { 'Authorization': `Bearer ${token}` }
    const id = route.params.id
    const res = await axios.get(`/api/admin/gudang/${id}`, {headers})
    gudang.value = res.data
  } catch (err) {
    console.error(err)
  }
}

onMounted(() => {
  fetchGudang()
})

const goBack = () => {
  router.push('/dashboard-admin/kelola-gudang')
}
</script>

<style scoped>
.container {
  padding: 20px;
}

button {
  margin-top: 10px;
  cursor: pointer;
}
</style>
