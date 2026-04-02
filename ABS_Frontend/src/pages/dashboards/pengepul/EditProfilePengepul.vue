<template>
  <div class="container">
    <h2>Edit Profile</h2>

    <div v-if="loading">Loading...</div>

    <form v-else @submit.prevent="submitForm">
      <div>
        <label>Nama</label><br />
        <input v-model="form.nama"></input>
      </div>
      <div>
        <label>Username</label><br />
        <input v-model="form.username"></input>
      </div>
      <div>
        <label>No Telp</label><br />
        <input v-model="form.no_telp"></input>
      </div>
      <div>
        <label>Nama Lembaga</label><br />
        <input v-model="form.nama_lembaga"></input>
      </div>
      <div>
        <label>Alamat</label><br />
        <textarea v-model="form.alamat"></textarea>
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

checkRole('pengepul')

const router = useRouter()

const form = ref({
  nama: '',
  username: '',
  no_telp: '',
  nama_lembaga: '',
  alamat: '',
})

const errors = ref({})
const loading = ref(true)
const token = sessionStorage.getItem('token')
const user = JSON.parse(sessionStorage.getItem('user'))

if (!token) {
  throw new Error('Otentikasi diperlukan.')
}

const headers = { 'Authorization': `Bearer ${token}` }

// ambil data awal
const fetchProfile = async () => {
  try {
    const id = user.pengepul_id
    const res = await axios.get(`/api/pengepul/profile/${id}`, {headers})
    console.log(res)
    form.value = res.data
  } catch (err) {
    console.error(err)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchProfile()
})

// submit update
const submitForm = async () => {
  errors.value = {}

  try {
    const id = user.pengepul_id
    await axios.put(`/api/pengepul/edit-profile/${id}`, form.value, {headers})

    const res = await axios.get(`/api/pengepul/profile/${id}`, {headers})
    sessionStorage.setItem("user", JSON.stringify(res.data))
    
    router.push('/dashboard-pengepul')
  } catch (err) {
    if (err.response && err.response.status === 422) {
      errors.value = err.response.data.errors
    } else {
      console.error(err)
    }
  }
}

const goBack = () => {
  router.push('/dashboard-pengepul')
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
