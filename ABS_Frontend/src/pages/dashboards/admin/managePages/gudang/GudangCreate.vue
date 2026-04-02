<template>
  <div class="container">
    <h2>Tambah Gudang</h2>

    <form @submit.prevent="submitForm">
      <div>
        <label>Alamat</label><br />
        <textarea v-model="form.alamat"></textarea>
        <div v-if="errors.alamat" class="error">
          {{ errors.alamat[0] }}
        </div>
      </div>

      <br />

      <button type="submit">Simpan</button>
      <button type="button" @click="goBack">Batal</button>
    </form>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'
import { checkRole } from '@/utils'

checkRole('admin')

const router = useRouter()

const form = ref({
  alamat: '',
})

const errors = ref({})

// submit
const submitForm = async () => {
  errors.value = {}

  try {
		const token = sessionStorage.getItem('token')

    if (!token) {
      throw new Error('Otentikasi diperlukan.')
    }

    const headers = { 'Authorization': `Bearer ${token}` }

    await axios.post('/api/admin/gudang', form.value, {headers})
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
