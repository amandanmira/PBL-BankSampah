<template>
  <div class="container">
    <h2>Tambah Gudang</h2>

    <form @submit.prevent="submitForm">
      <div>
        <label>Jumlah</label><br />
        <input type="number" v-model="form.jumlah"></input>
      </div>
      <div>
        <label>No Rekening</label><br />
        <input v-model="form.no_rekening"></input>
      </div>
      <div>
        <label>Nama Bank</label><br />
        <input v-model="form.nama_bank"></input>
      </div>
      <div>
        <label>Nama Rekening</label><br />
        <input v-model="form.nama_rek"></input>
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

const token = sessionStorage.getItem('token')
const user = JSON.parse(sessionStorage.getItem('user'))

if (!token) {
  throw new Error('Otentikasi diperlukan.')
}

const headers = { 'Authorization': `Bearer ${token}` }

const form = ref({
  jumlah: '',
  nasabah_id: user.nasabah_id,
  no_rekening: user.no_rekening,
  nama_bank: user.nama_bank,
  nama_rek: user.nama_rek,
})

const errors = ref({})

// submit
const submitForm = async () => {
  errors.value = {}

  try {
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
