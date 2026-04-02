<template>
  <div class="verification-container">
    <h1>Verifikasi Email Anda</h1>
    <div v-if="loading" class="status-box loading">
      <p>Sedang memverifikasi email Anda...</p>
    </div>
    <div v-else-if="error" class="status-box error">
      <p>Gagal melakukan verifikasi.</p>
      <p class="message">{{ error }}</p>
      <router-link to="/login">Kembali ke Login</router-link>
    </div>
    <div v-else-if="success" class="status-box success">
      <p>Verifikasi berhasil!</p>
      <p class="message">Akun Anda telah diaktifkan. Anda akan diarahkan ke halaman login dalam beberapa detik.</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import axios from "axios";

const route = useRoute();
const router = useRouter();

const loading = ref(true);
const error = ref(null);
const success = ref(false);

onMounted(async () => {
  const token = route.params.token;

  if (!token) {
    error.value = "Token verifikasi tidak ditemukan.";
    loading.value = false;
    return;
  }

  try {
    const response = await axios.get(`http://localhost:8000/api/verify-nasabah/${token}`);
    success.value = true;

    // Redirect to login page after 3 seconds
    setTimeout(() => {
      router.push("/login");
    }, 3000);
  } catch (err) {
    error.value = err.response?.data?.message || "Terjadi kesalahan pada server.";
  } finally {
    loading.value = false;
  }
});
</script>

<style scoped>
.verification-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 80vh;
  text-align: center;
  font-family: "Arial", sans-serif;
}

.status-box {
  border: 1px solid #ddd;
  padding: 2rem;
  border-radius: 8px;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  max-width: 400px;
}

.status-box p {
  margin: 0;
  font-size: 1.2rem;
}

.message {
  font-size: 1rem;
  color: #555;
  margin-top: 0.5rem;
}

.loading {
  background-color: #f0f8ff;
  border-color: #add8e6;
}

.error {
  background-color: #fff0f0;
  border-color: #ffc0cb;
  color: #d8000c;
}

.success {
  background-color: #f0fff0;
  border-color: #90ee90;
  color: #2b7d2b;
}

a {
  margin-top: 1rem;
  display: inline-block;
  color: #007bff;
  text-decoration: none;
}
</style>
