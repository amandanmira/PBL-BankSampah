<template>
  <div style="padding: 50px;">
    <h2>Login Bank Sampah</h2>
    
    <div v-if="user">
      <p>Selamat datang, <strong>{{ user.name }}</strong>!</p>
      <button @click="handleLogout">Logout</button>
    </div>

    <form v-else @submit.prevent="handleLogin">
      <div style="margin-bottom: 10px;">
        <label>Email:</label><br>
        <input type="email" v-model="form.email" required>
      </div>
      <div style="margin-bottom: 10px;">
        <label>Password:</label><br>
        <input type="password" v-model="form.password" required>
      </div>
      <button type="submit">Login</button>
      <p style="color: red;" v-if="errorMessage">{{ errorMessage }}</p>
    </form>
  </div>

  <router-view></router-view>
</template>

<script setup>
import { ref, inject } from 'vue';

// Mengambil axios yang sudah kita atur di main.js sebelumnya
const axios = inject('axios');

const form = ref({ email: '', password: '' });
const user = ref(null);
const errorMessage = ref('');

// Fungsi Login
const handleLogin = async () => {
  try {
    errorMessage.value = '';
    
    // LANGKAH 1 WAJIB: Minta tiket / cookie keamanan ke Laravel
    await axios.get('/sanctum/csrf-cookie');
    
    // LANGKAH 2: Kirim data form login
    const response = await axios.post('/login', form.value);
    
    // LANGKAH 3: Jika sukses, ambil data user yang sedang login dari API
    const userResponse = await axios.get('/api/user');
    user.value = userResponse.data;
    
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Terjadi kesalahan sistem.';
  }
};

// Fungsi Logout
const handleLogout = async () => {
  try {
    await axios.post('/logout');
    user.value = null; // Hapus data user dari layar
    form.value.email = '';
    form.value.password = '';
  } catch (error) {
    console.error('Gagal logout', error);
  }
};
</script>