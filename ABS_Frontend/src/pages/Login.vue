<template>
  <div style="padding: 50px">
    <h2>Login Bank Sampah</h2>

    <form @submit.prevent="handleLogin">
      <div style="margin-bottom: 10px">
        <label>Email:</label><br />
        <input type="email" v-model="form.email" required />
      </div>
      <div style="margin-bottom: 10px">
        <label>Password:</label><br />
        <input type="password" v-model="form.password" required />
      </div>
      <button type="submit">Login</button>
      <p style="color: red" v-if="errorMessage">{{ errorMessage }}</p>
    </form>
  </div>
</template>

<script setup>
import { ref, inject } from 'vue'
import { useRouter } from "vue-router";

const router = useRouter()

// Mengambil axios yang sudah kita atur di main.js sebelumnya
const axios = inject('axios')

const form = ref({ email: '', password: '' })
const errorMessage = ref('')

// Fungsi Login
const handleLogin = async () => {
  try {
    errorMessage.value = ''

    // LANGKAH 1 WAJIB: Minta tiket / cookie keamanan ke Laravel
    await axios.get('/sanctum/csrf-cookie')

    // LANGKAH 2: Kirim data form login
    const response = await axios.post('/api/login', form.value)

    console.log(response.data)
    const user = response.data.user
    const role = response.data.role

    sessionStorage.setItem("user", JSON.stringify(user))
    sessionStorage.setItem("role", role)

    // Redirect berdasarkan role
    if (role === "admin") {
      router.push("/dashboard-admin")
    }
    else if (role === "manager") {
      router.push("/dashboard-mananger")
    }
    else if (role === "petugas") {
      router.push("/dashboard-petugas")
    }
    else if (role === "nasabah") {
      router.push("/dashboard-nasabah")
    }
  } catch (error) {
    errorMessage.value = error.response?.data?.message || error
  }
}
</script>
