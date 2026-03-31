<template>
  <div class="container">
    <h2>Tambah Gudang</h2>

    <form @submit.prevent="submitForm">
      <div>
        <label>Jumlah</label><br />
        <input type="number" v-model="form.jumlah"></input>
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

checkRole('nasabah')

const router = useRouter()

const form = ref({
  jumlah: '',
  nasabah_id: '',
})

const errors = ref({})

// submit
const submitForm = async () => {
  errors.value = {}

  try {
    const token = sessionStorage.getItem('token')
    const user = JSON.parse(sessionStorage.getItem('user'))

    if (!token) {
      throw new Error('Otentikasi diperlukan.')
    }

    form.value.nasabah_id = user.nasabah_id

    const headers = { 'Authorization': `Bearer ${token}` }

    await axios.post('/api/nasabah/request-penarikan', form.value, { headers })
    router.push('/dashboard-nasabah')
  } catch (err) {
    if (err.response && err.response.status === 422) {
      errors.value = err.response.data.errors
    } else {
      console.error(err)
    }
  }
}

const goBack = () => {
  router.push('/dashboard-nasabah')
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
