<template>
  <div class="container">
    <h2>Edit Gudang</h2>

    <div v-if="loading">Loading...</div>

    <form v-else @submit.prevent="submitForm">
      <div>
        <label>Alamat</label><br />
        <textarea v-model="form.alamat"></textarea>
      </div>
      <div>
        <label>Kapasitas</label><br />
        <input type="number" v-model="form.kapasitas"></input>
      </div>

      <br />

      <button type="submit">Update</button>
      <button type="button" @click="goBack">Batal</button>
    </form>
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

const form = ref({
  alamat: '',
  kapasitas: 0,
})

const errors = ref({})
const loading = ref(true)
const token = sessionStorage.getItem('token')

if (!token) {
  throw new Error('Otentikasi diperlukan.')
}

const headers = { 'Authorization': `Bearer ${token}` }

// ambil data awal
const fetchGudang = async () => {
  try {
    const id = route.params.id
    const res = await axios.get(`/api/admin/gudang/${id}`, {headers})
    form.value = res.data
  } catch (err) {
    console.error(err)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchGudang()
})

// submit update
const submitForm = async () => {
  errors.value = {}

  try {
    const id = route.params.id
    await axios.put(`/api/admin/gudang/${id}`, form.value, {headers})
    router.push('/dashboard-admin/kelola-gudang')
  } catch (err) {
    if (err.response && err.response.status === 422) {
      errors.value = err.response.data.errors
    } else {
      console.error(err)
    }
  }
}

const goBack = () => {
  router.push('/dashboard-admin/kelola-gudang')
}
</script>

<style scoped>
.container {
  padding: 20px;
}

textarea {
  width: 300px;
  height: 100px;
}

button {
  margin: 5px;
  cursor: pointer;
}

.error {
  color: red;
  font-size: 12px;
}
</style>
